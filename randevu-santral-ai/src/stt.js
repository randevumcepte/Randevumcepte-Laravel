'use strict';
// Google STREAMING Speech-to-Text sarmalayici.
// ONEMLI: streaming, API anahtari DEGIL servis hesabi (GOOGLE_APPLICATION_CREDENTIALS) ister.
// Her cagri icin bir StreamOturumu; ham ses (ulaw/slin16) yazilir, final cumle geri cagrilir.
const speech = require('@google-cloud/speech');
const cfg = require('./config');
const log = require('./log');

const client = new speech.SpeechClient();

// Google streaming tek akista ~305sn siniri koyar -> uzun cagrida akisi periyodik yenile.
const AKIS_YENILEME_MS = 240000;

// MALIYET OLCUMU: Google STT, akisa GONDERILEN ses suresine gore ucretlendirir; her akis (request) ayri yuvarlanir.
// STT_YUVARLAMA_SN: akis basina yuvarlama birimi (V1 faturasinda gorulen birime gore; varsayilan 1 sn).
const YUVARLAMA_SN = Math.max(0, parseFloat(process.env.STT_YUVARLAMA_SN || '1') || 0);
function saniyeBayt() {
  // ulaw 8 kHz = 8000 B/sn, LINEAR16 = ornek hizi x 2
  return /LINEAR16/i.test(cfg.audio.sttEncoding || '') ? cfg.audio.sampleRate * 2 : cfg.audio.sampleRate;
}

// STT kelime ipuclari (speech context) — SALON/RANDEVU alanina ozgu kelimeler.
// DIKKAT: restorandan kalan 'masa/kişi/kişilik/porsiyon' ipuclari "saç kesimi"ni "kişi" diye duyuruyordu -> kaldirildi.
const KELIME_IPUCLARI = [
  'randevu', 'randevu almak istiyorum', 'saç kesimi', 'saç boyama', 'fön', 'manikür', 'pedikür',
  'cilt bakımı', 'kaş', 'ağda', 'lazer', 'epilasyon', 'masaj', 'sakal', 'tıraş', 'makyaj', 'kalıcı oje',
  'saat', 'sabah', 'öğleden sonra', 'akşam', 'yarın', 'bugün', 'haftaya', 'adres', 'yol tarifi',
  'iptal', 'değiştirmek', 'ertelemek', 'hanım', 'bey', 'telefon numaram', 'istiyorum',
  'adım', 'ismim', 'benim adım',
  'işletmeye bağlar mısınız', 'beni bağlar mısınız', 'yetkiliye bağlayın', 'operatör', 'yetkili', 'biriyle görüşmek istiyorum',
];

// Yaygin Turkce isim/soyadlari -> STT ismi tanisin ("Ferdi"yi "saati" diye duymasin). Ayri context, yuksek boost.
const ISIM_IPUCLARI = [
  'Ferdi', 'Emir', 'Ahmet', 'Mehmet', 'Mustafa', 'Ali', 'Hüseyin', 'Hasan', 'İbrahim', 'Osman', 'Yusuf',
  'Murat', 'Emre', 'Burak', 'Serkan', 'Kerem', 'Kaan', 'Can', 'Deniz', 'Barış', 'Berk', 'Cem', 'Çağrı',
  'Efe', 'Ege', 'Eren', 'Fatih', 'Furkan', 'Gökhan', 'Halil', 'İsmail', 'Kadir', 'Levent', 'Metin',
  'Onur', 'Ozan', 'Salih', 'Selim', 'Serhat', 'Sinan', 'Tolga', 'Uğur', 'Ümit', 'Volkan', 'Yiğit',
  'Yılmaz', 'Zeki', 'Taner', 'Tuncay', 'Hakan', 'Okan', 'Sefa', 'Arda', 'Mert', 'Enes', 'Buğra',
  'Ayşe', 'Fatma', 'Emine', 'Hatice', 'Zeynep', 'Elif', 'Meryem', 'Zehra', 'Sultan', 'Merve', 'Esra',
  'Büşra', 'Melike', 'Gül', 'Gülşah', 'Kübra', 'Selin', 'Ceren', 'Damla', 'Dilara', 'Ebru', 'Ece',
  'Eda', 'Gamze', 'İrem', 'Nur', 'Özge', 'Pınar', 'Sude', 'Tuğçe', 'Yasemin', 'Aslı', 'Aylin', 'Beyza',
  'Cansu', 'Derya', 'Duygu', 'Gizem', 'Hande', 'Nazlı', 'Özlem', 'Sibel', 'Şeyma', 'Betül', 'Nihan',
  'Korkmaz', 'Kabataş', 'Kaya', 'Demir', 'Şahin', 'Çelik', 'Yıldız', 'Yıldırım', 'Öztürk', 'Aydın',
  'Özdemir', 'Arslan', 'Doğan', 'Kılıç', 'Aslan', 'Çetin', 'Kara', 'Koç', 'Kurt', 'Özkan', 'Şimşek',
  'Erdoğan', 'Güneş', 'Aksoy', 'Polat', 'Bulut', 'Erdem', 'Turan', 'Avcı', 'Tekin', 'Acar',
];

class SttOturumu {
  /**
   * @param {(metin:string)=>void} onFinal  Cumle tamamlaninca (final transcript)
   * @param {(metin:string)=>void} onInterim Ara sonuc (barge-in tetigi icin)
   */
  constructor(onFinal, onInterim) {
    this.onFinal = onFinal || (() => {});
    this.onInterim = onInterim || (() => {});
    this.stream = null;
    this.kapali = false;
    this.yenilemeZ = null;
    this.ekIpuclari = [];
    this.faturaSn = 0;   // kapanan akislarin ucretlenen toplami (yuvarlanmis)
    this._akisBayt = 0;  // acik akisa yazilan ses
    this._baslat();
  }

  _istekConfig() {
    const conf = {
      encoding: cfg.audio.sttEncoding,        // MULAW veya LINEAR16
      sampleRateHertz: cfg.audio.sampleRate,  // 8000 / 16000
      languageCode: cfg.stt.language,         // tr-TR
      enableAutomaticPunctuation: true,
      maxAlternatives: 1,
      speechContexts: [
        { phrases: KELIME_IPUCLARI, boost: 15 },   // alan kelimeleri -> dogruluk
        { phrases: ISIM_IPUCLARI, boost: 20 },      // isimler -> 'Ferdi' 'saati' olmasin (daha yuksek boost)
      ],
    };
    // Salonun hizmet adlari ("Tay Masajı" -> 'dayı'/'tay makajı' olmasin). ORTA boost: restoranda urun adlarina
    // yuksek boost her cumleyi urun adina zorlamisti; burada 10 yeterli ve guvenli.
    // Tek kelimeler ("protez", "kirpik") 15: musteri cogu zaman tam adi degil tek kelimeyi soyler ("protez" ->
    // "protest" duyuluyordu, canli 2026-11-18).
    if (this.ekIpuclari && this.ekIpuclari.length) {
      const coklu = this.ekIpuclari.filter((x) => /\s/.test(x));
      const tek = this.ekIpuclari.filter((x) => !/\s/.test(x));
      if (coklu.length) conf.speechContexts.push({ phrases: coklu, boost: 10 });
      if (tek.length) conf.speechContexts.push({ phrases: tek, boost: 15 });
    }
    // Model secimi:
    //  - 'latest_long','latest_short','default','command_and_search' -> COK DILLI (tr-TR dahil), her dilde gonder
    //  - 'phone_call','video' -> enhanced, cogu dilde SADECE en-* destekli; tr-TR'de gonderirsek API hata verir
    //    -> hic sonuc donmez ("AI duymuyor"). Bu yuzden en-* disi dillerde ATLA.
    const m = (cfg.stt.model || '').trim().toLowerCase();
    const cokDilli = ['latest_long', 'latest_short', 'default', 'command_and_search'];
    const sadeceEn = ['phone_call', 'video'];
    if (m && m !== 'auto') {
      if (cokDilli.includes(m)) {
        conf.model = m;
      } else if (sadeceEn.includes(m)) {
        if (/^en/i.test(cfg.stt.language)) { conf.model = m; conf.useEnhanced = true; }
        else if (!this._modelUyari) {
          this._modelUyari = true;
          log.warn(`STT model '${m}' ${cfg.stt.language} icin ATLANDI (enhanced modeller genelde en-* destekli). Oneri: STT_MODEL=latest_long`);
        }
      } else {
        conf.model = m; // bilinmeyen -> oldugu gibi dene
      }
    }
    return { config: conf, interimResults: true };
  }

  _baslat() {
    if (this.kapali) return;
    log.debug('STT akisi aciliyor…');
    this.stream = client
      .streamingRecognize(this._istekConfig())
      .on('error', (err) => {
        // OutOfRange = sure/veri siniri -> hemen yenile (normal)
        if (String(err.message || '').includes('OUT_OF_RANGE') || err.code === 11) {
          log.debug('STT akisi yeniden baslatiliyor (sure siniri)');
          this._yenile(0);
          return;
        }
        // Gercek hata (kimlik/model/kota vb.) -> TAM GORUNUR logla (kod+detay)
        log.warn(`STT HATA: code=${err.code} msg=${err.message}${err.details ? ' details=' + err.details : ''}`);
        this._yenile(1000);
      })
      .on('data', (data) => {
        this._dataGeldi = (this._dataGeldi || 0) + 1;
        if (this._dataGeldi <= 3) log.info(`STT data geldi (#${this._dataGeldi}, results=${(data.results || []).length})`);
        const r = data.results && data.results[0];
        if (!r || !r.alternatives || !r.alternatives[0]) return;
        const metin = (r.alternatives[0].transcript || '').trim();
        if (!metin) return;
        if (r.isFinal) this.onFinal(metin);
        else this.onInterim(metin);
      });

    clearTimeout(this.yenilemeZ);
    this.yenilemeZ = setTimeout(() => this._yenile(0), AKIS_YENILEME_MS);
  }

  /** Acik akisin ses suresini faturaya isle (akis kapanirken). */
  _akisSay() {
    if (!this._akisBayt) return;
    let sn = this._akisBayt / saniyeBayt();
    if (YUVARLAMA_SN > 0) sn = Math.ceil(sn / YUVARLAMA_SN - 1e-9) * YUVARLAMA_SN;
    this.faturaSn += sn;
    this._akisBayt = 0;
  }

  /** Bu cagride STT'ye akan (ucretlenen) toplam saniye — acik akis dahil. */
  ucretliSaniye() {
    this._akisSay();
    return Math.round(this.faturaSn * 10) / 10;
  }

  _yenile(bekle) {
    const eski = this.stream;
    this.stream = null;
    this._akisSay();
    if (eski) { try { eski.end(); } catch (_) {} }
    if (this.kapali) return;
    if (this.duraklatildi) { clearTimeout(this.yenilemeZ); return; } // duraklatilmis: devam() yeni config ile acar
    clearTimeout(this.yenilemeZ);
    if (bekle && bekle > 0) this.yenilemeZ = setTimeout(() => this._baslat(), bekle);
    else this._baslat();
  }

  /** Salona ozel ipuclarini (hizmet adlari) ekle ve akisi yeni config ile yeniden ac (karsilama sirasinda). */
  ipucuEkle(liste) {
    const temiz = (Array.isArray(liste) ? liste : [])
      .map((x) => String(x || '').trim()).filter((x) => x && x.length <= 100).slice(0, 500);
    if (!temiz.length) return;
    this.ekIpuclari = temiz;
    log.info(`STT ipucu: ${temiz.length} hizmet adi eklendi`);
    this._yenile(0);
  }

  /** AI konusurken akisi kapat (ses gonderilmez -> ucretlenmez). */
  durdur() {
    if (this.kapali || this.duraklatildi) return;
    this.duraklatildi = true;
    clearTimeout(this.yenilemeZ);
    const eski = this.stream;
    this.stream = null;
    this._akisSay();
    if (eski) { try { eski.end(); } catch (_) {} }
  }

  /** Akisi yeniden ac ve tampondaki son kareleri ilk olarak yaz (konusmanin basi kaybolmasin). */
  devam(parcalar) {
    if (this.kapali) return;
    this.duraklatildi = false;
    if (!this.stream) this._baslat();
    if (!this.stream) return;
    for (const p of (parcalar || [])) { try { this.stream.write(p); this._akisBayt += p.length; } catch (_) { break; } }
  }

  /** Kor nokta kurtarma: akisi yeniden ac ve tampondaki son konusmayi yeni akisa ver (gercek zamandan hizli yazilir). */
  kurtar(parcalar) {
    if (this.kapali) return;
    this._yenile(0);
    if (!this.stream || !Array.isArray(parcalar) || !parcalar.length) return;
    for (const p of parcalar) { try { this.stream.write(p); this._akisBayt += p.length; } catch (_) { break; } }
  }

  // Asterisk'ten gelen ham ses karesi (RTP payload'i, header'siz)
  yaz(buf) {
    if (this.kapali || !this.stream) { this._yazDus = (this._yazDus || 0) + 1; return; }
    try {
      this.stream.write(buf);
      this._akisBayt += buf.length;
      this._yazSay = (this._yazSay || 0) + 1;
      if (this._yazSay === 1 || this._yazSay === 150) log.info(`STT'ye ${this._yazSay} kare yazildi (ses akiyor)`);
    } catch (e) {
      this._yazHata = (this._yazHata || 0) + 1;
      if (this._yazHata <= 2) log.warn('STT yaz hatasi (ses Google\'a gitmiyor): ' + e.message);
    }
  }

  kapat() {
    this.kapali = true;
    clearTimeout(this.yenilemeZ);
    this._akisSay();
    if (this.stream) { try { this.stream.end(); } catch (_) {} this.stream = null; }
  }
}

module.exports = { SttOturumu };
