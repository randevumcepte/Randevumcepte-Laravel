'use strict';
// Cagri basina orkestrasyon: RTP(ses) <-> STT(metin) <-> Laravel beyin <-> TTS(ses) <-> RTP
// Barge-in, sira yonetimi ve sessizlik zamanlayicisi burada.
const cfg = require('./config');
const log = require('./log');
const brain = require('./brain');
const tts = require('./tts');
const { SttOturumu } = require('./stt');
const { RtpOturumu } = require('./rtp');

// Bekleme cumleleri (beyin gec cevap verince, sirayla; ayni cumle ust uste gelmez). Ilk cagride seslendirilip
// tts onbellegine girer -> sonraki turlarda aninda calar.
// "Bir saniye lütfen" cikarildi: konusma arasinda anlamsiz geliyordu (2026-11-17 geri bildirim).
const DOLGU = ['Hemen bakıyorum.', 'Tamam, kontrol ediyorum.'];

// Bekleme cumlesi YALNIZ kayit/takvim aranan isteklerde calar (randevu, saat, gun, iptal, paket...).
// "Evet", "fark etmez", isim soyleme gibi kisa cevaplarda "Hemen bakıyorum" anlamsiz kaciyordu.
const DOLGULU = /(randevu|müsait|musait|uygun|boş|bos\b|saat|yarın|yarin|bugün|bugun|haftaya|pazartesi|salı|sali|çarşamba|carsamba|perşembe|persembe|cuma|pazar|iptal|ertele|değiştir|degistir|kaydır|kaydir|paket|seans|kontrol)/i;

// Bu cumlelerde bekleme cumlesi CALMA (vedalasma/tesekkur/ret: "Hemen bakıyorum. Hoşçakalın" sacmaligi, kayit #55)
const DOLGUSUZ = /(teşekkür|tesekkur|sağ ?ol|sag ?ol|iyi günler|iyi akşamlar|iyi aksamlar|hoşça|hosca|görüşürüz|gorusuruz|gerek yok|başka (bir )?(şey|sey|konu)? ?yok|bu kadar|o kadar|kapat)/i;

// Tekrar karsilastirmasi icin: kucuk harf + noktalama/bosluk sadelestirme ("Tay makajı." == "tay makajı")
function sadeMetin(s) {
  return String(s || '').toLocaleLowerCase('tr').replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
}

class CagriOturumu {
  constructor({ kanalId, telefon, subeId, rtpPort, onAktar, onBitir, geriAramaOturum, hatirlatma }) {
    this.kanalId = kanalId;
    this.telefon = telefon || null;
    this.subeId = subeId || cfg.laravel.defaultSubeId;
    this.geriAramaOturum = geriAramaOturum || 0; // >0 ise: yarida kalan oturumla devam (geri arama)
    this.hatirlatma = hatirlatma || '';            // giden hatirlatma aramasi (karsilama = hatirlatma metni)
    this.onAktar = onAktar || (() => {});   // "insana aktar" -> index.js Asterisk transferi yapar
    this.onBitir = onBitir || (() => {});   // gorusme bitti -> index.js kanali kapatir
    this.oturumId = null;
    this.mesgul = false;         // beyin/tts turu devam ediyor mu
    this.kapali = false;
    this.sonSes = Date.now ? 0 : 0; // Date.now scriptte var; node runtime'da normal calisir
    this.sessizlikZ = null;

    this._sesGeldi = false; // ilk ses karesi STT'ye ulasti mi (tani icin)

    this.rtp = new RtpOturumu(rtpPort, (payload) => {
      // YARI-DUPLEKS: barge-in kapaliyken AI konusurken mikrofonu STT'ye VERME.
      // Boylece hat yankisi (AI'nin kendi sesi) STT'ye dusup AI'yi kesmez / kendini dinlemez.
      if (!cfg.bargeIn && this.rtp && this.rtp.sesVarMi) return;
      if (!this._sesGeldi) { this._sesGeldi = true; log.info('ilk ses karesi STT ye ulasti (mikrofon calisiyor)'); }
      // ENERJI TESHISI: giden ses gercek mi sessizlik mi? (ulaw sessizlik ~0xFF/0x7F)
      this._ornek = (this._ornek || 0) + 1;
      if (this._ornek <= 250) {
        let dolu = 0;
        for (let i = 0; i < payload.length; i++) { const b = payload[i]; if (b !== 0xFF && b !== 0x7F && b !== 0xFE && b !== 0x7E && b !== 0x00) dolu++; }
        this._enerjiTop = (this._enerjiTop || 0) + (payload.length ? dolu / payload.length : 0);
        if (this._ornek === 250) log.info(`SES ENERJI: ort dolu-oran ${(this._enerjiTop / 250).toFixed(2)} (0'a yakin=SESSIZLIK/sesin gelmiyor, >0.3=gercek ses var)`);
      }
      this.stt.yaz(payload);
    });
    this.stt = new SttOturumu(
      (final) => this._finalMetin(final),
      (interim) => this._interim(interim),
    );
  }

  async basla() {
    try {
      const d = await brain.baslat(this.subeId, this.telefon, 'santral', this.geriAramaOturum, this.hatirlatma);
      this.oturumId = d.oturum_id;
      // Salonun hizmet adlari -> STT ipucu (karsilama calarken akis yeni config ile yeniden acilir)
      if (Array.isArray(d.stt_ipuclari) && d.stt_ipuclari.length) { try { this.stt.ipucuEkle(d.stt_ipuclari); } catch (e) { log.warn('STT ipucu eklenemedi: ' + e.message); } }
      log.info(`Oturum #${this.oturumId} basladi (kanal ${this.kanalId}, tel ${this.telefon || '-'}, sube ${d.sube_id ?? this.subeId}, menu ${d.menu_adet ?? '?'} urun, musteri ${d.musteri || 'YENI'})`);
      if (d.menu_adet === 0) log.warn('DIKKAT: bu subede aktif urun YOK -> AI menuyu tanitamaz (DEFAULT_SUBE_ID dogru mu? urunler.aktif=1 mi?)');
      if (d.musteri) log.info(`musteri taninID: ${d.musteri}${d.son_siparis ? ' | gecen siparis: ' + d.son_siparis : ' | gecen siparis YOK'}`);
      await this._seslendir(d.karsilama || 'Merhaba, size nasıl yardımcı olabilirim?');
      // Bekleme cumlelerini onceden seslendir (onbellege al) -> gerektiginde aninda calsin
      if (cfg.dolguMs > 0) for (const c of DOLGU) tts.seslendir(c).catch(() => {});
    } catch (e) {
      const neden = e.response && e.response.data && e.response.data.hata;
      if (neden === 'paket_yok' || neden === 'paket_bitti') {
        // Salonun asistan paketi yok/bitti: anons YAPMADAN dogrudan isletmeye (operator_kanali) aktar
        log.info(`Asistan kapali (${neden}, sube ${this.subeId}) -> dogrudan isletmeye aktariliyor`);
        this.onAktar(this.kanalId);
        return;
      }
      log.error('Beyin baslat hatasi:', e.message);
      await this._seslendir('Sizi yetkiliye bağlıyorum, lütfen hatta kalın.');
      this.onAktar(this.kanalId);
    }
    this._sessizlikSifirla();
  }

  _interim(metin) {
    if (this.kapali || this._kapaniyor) return; // kapanis sirasinda dinleme/barge-in yok
    log.debug(`ara-sonuc: ${metin}`);
    // BARGE-IN: musteri GERCEKTEN konusunca (>=2 kelime) AI'nin sesini kes.
    // >=2 kelime sarti: tek kelimelik gurultu/eko blip'i AI'yi bosuna kesmesin.
    const kelime = (metin || '').trim().split(/\s+/).filter(Boolean).length;
    if (cfg.bargeIn && (this.rtp.sesVarMi || this._seslendiriyor) && kelime >= 2) {
      log.info(`barge-in: musteri araya girdi, AI susturuluyor ("${metin}")`);
      this._kesmeNo = (this._kesmeNo || 0) + 1; // devam eden _seslendir dongusu kalan cumleleri CALMASIN
      this.rtp.sustur();
    }
    this._sessizlikSifirla();
    // ISTEMCI-TARAFI ENDPOINTING (AKILLI): interim sabit kalinca zorla finalize et.
    //  - KISA cevap (<=3 kelime: 'kartla olsun','evet','margarita') -> 1.2sn (snappy)
    //  - UZUN cumle (adres/isim, cok kelime) -> 2.4sn SABIRLI (duraklamada kesme, musteri tamamlasin)
    // Her yeni interim timer'i sifirlar; yani sure, musteri SUSTUKTAN sonra sayilir.
    this._sonInterim = (metin || '').trim();
    const kelimeSayisi = this._sonInterim ? this._sonInterim.split(/\s+/).length : 0;
    const bekle = kelimeSayisi <= 3 ? cfg.endpointKisaMs : cfg.endpointUzunMs;
    clearTimeout(this._interimZ);
    this._interimZ = setTimeout(() => this._interimZorla(), bekle);
  }

  _interimZorla() {
    if (this.kapali || this.mesgul || this._kapaniyor) return;
    const m = (this._sonInterim || '').trim();
    this._sonInterim = '';
    if (m.length >= 2) { log.debug(`endpointing: interim->final: ${m}`); this._finalMetin(m); }
  }

  async _finalMetin(metin) {
    if (this.kapali || this._kapaniyor) return; // kapanis sirasinda yeni tur baslatma
    clearTimeout(this._interimZ); this._sonInterim = '';
    const n = sadeMetin(metin);
    if (n.length < 1) return;
    // Yineleme onle: zorla-final (interim) ile islenen cumle, Google'in gercek final'iyle ("Tay makajı." gibi
    // noktalamali / biraz uzamis haliyle) ikinci kez TUR olmasin. Eskiden noktalama farki ayri tur yapiyordu ->
    // asistan ayni soruya iki cevap ust uste okuyordu (2026-11-16 kayit #51).
    const simdi = Date.now();
    const onceki = this._sonIslenen;
    if (onceki && (simdi - onceki.t) < 5000) {
      if (onceki.n === n || onceki.n.startsWith(n)) { log.debug('yinelenen final atlandi'); return; }
      if (n.startsWith(onceki.n) && this.mesgul && !this._bekleyen) {
        // Final, islenmekte olan interim'in DEVAMI: onceki cevabi calma, tam cumleyle yeniden sor
        log.info(`> musteri (devami): ${metin}`);
        this._sonIslenen = { n, t: simdi };
        this._eskidi = true; this._bekleyen = metin;
        return;
      }
    }
    this._sonIslenen = { n, t: simdi };
    log.info(`> musteri: ${metin}`);
    // Beyin dusunurken musteri yeni bir sey soyledi: onceki cevap ESKIDI (calinmayacak, aksiyon yoksa);
    // bekleyen cumleler birlestirilip tek turda sorulur.
    if (this.mesgul) { this._bekleyen = this._bekleyen ? this._bekleyen + ' ' + metin : metin; this._eskidi = true; return; }
    await this._isle(metin);
    while (this._bekleyen && !this.kapali) {
      const m = this._bekleyen; this._bekleyen = null;
      await this._isle(m);
    }
  }

  _dolguCal() {
    if (this.kapali || this._kapaniyor || !this.mesgul) return;
    if (this.rtp.sesVarMi) return; // AI hala onceki cumleyi okuyor
    this._dolguNo = ((this._dolguNo || 0) + 1) % DOLGU.length;
    const c = DOLGU[this._dolguNo];
    tts.seslendir(c).then((ses) => {
      if (this.kapali || !this.mesgul || this.rtp.sesVarMi) return;
      if (ses && ses.length) { log.info(`~ bekleme: ${c}`); this.rtp.oynat(ses); }
    }).catch(() => {});
  }

  async _isle(metin) {
    this.mesgul = true;
    this._eskidi = false;
    const ek = this._oncekiDuyulmadi ? { onceki_duyulmadi: 1 } : {};
    this._oncekiDuyulmadi = false;
    const dolguZ = (cfg.dolguMs > 0 && DOLGULU.test(metin || '') && !DOLGUSUZ.test(metin || '')) ? setTimeout(() => this._dolguCal(), cfg.dolguMs) : null;
    const t0 = Date.now();
    try {
      const d = await brain.konus(this.oturumId, metin, ek);
      clearTimeout(dolguZ);
      log.info(`  (beyin ${Date.now() - t0} ms)`); // gecikme teshisi: musteri sustu -> cevap metni hazir
      // Musteri bu arada yeni bir sey soyledi ve bu cevap bir is yapmadiysa (randevu/iptal/aktarma/kapanis yok):
      // CALMA -> bekleyen cumleyle yeni tur sorulur; Laravel bu calinmayan cevabi gecmisten duser.
      if (this._eskidi && this._bekleyen && d && d.ok !== false && !d.aksiyon && !d.bitir) {
        log.info(`< asistan (CALINMADI, musteri yeni sey soyledi): ${d.cevap}`);
        this._oncekiDuyulmadi = true;
        return;
      }
      if (!d || d.ok === false) {
        await this._seslendir('Kusura bakmayın, sizi yetkiliye aktarıyorum.');
        this.onAktar(this.kanalId);
        return;
      }
      log.info(`< asistan: ${d.cevap}${d.aksiyon ? '  [aksiyon: ' + d.aksiyon + ']' : ''}`);
      // KAPANIS ise: kapanis cumlesi barge-in ile KESILMESIN + yeni tur baslamasin (askida kalmasin)
      const kapanis = (d.aksiyon === 'veda' || d.bitir);
      if (kapanis) this._kapaniyor = true;
      if (d.cevap) await this._seslendir(d.cevap);

      if (d.aksiyon === 'aktar') { this.onAktar(this.kanalId); return; }
      if (kapanis) { await this._kapatSirasi(); return; }
    } catch (e) {
      clearTimeout(dolguZ);
      log.error('Beyin konus hatasi:', e.message);
      await this._seslendir('Bir sorun oluştu, sizi yetkiliye bağlıyorum.');
      this.onAktar(this.kanalId);
    } finally {
      this.mesgul = false;
      this._sessizlikSifirla();
    }
  }

  async _seslendir(metin) {
    // Cumle-cumle seslendir: ilk cumle hemen calar, sonrakiler o calariken uretilir (algilanan gecikme duser).
    // BARGE-IN: musteri araya girince (_kesmeNo artar) kalan cumleler URETILMEZ/CALINMAZ.
    // (Eskiden sustur() yalniz kuyrugu bosaltiyordu; dongu sonraki cumleyi yine calip AI'yi "devam ettiriyordu".)
    const kesme = this._kesmeNo || 0;
    const cumleler = this._cumlelereBol(metin);
    this._seslendiriyor = true;
    try {
      for (const c of cumleler) {
        if (this.kapali || (this._kesmeNo || 0) !== kesme) break;
        const ses = await tts.seslendir(c);
        if (this.kapali || (this._kesmeNo || 0) !== kesme) break; // TTS beklerken kesildiyse calma
        if (ses && ses.length) this.rtp.oynat(ses);
      }
    } finally {
      this._seslendiriyor = false;
    }
  }

  _cumlelereBol(metin) {
    const t = (metin || '').trim();
    if (!t) return [];
    // Cumle sonlarindan bol (. ! ?), noktalamayi koru; cok kisa parcalari birlestir.
    const parcalar = t.match(/[^.!?]+[.!?]*/g) || [t];
    const out = [];
    let tampon = '';
    for (const p of parcalar) {
      tampon += p;
      if (tampon.trim().length >= 20 || /[.!?]\s*$/.test(tampon)) { out.push(tampon.trim()); tampon = ''; }
    }
    if (tampon.trim()) out.push(tampon.trim());
    return out.length ? out : [t];
  }

  _sessizlikSifirla(ikinci) {
    clearTimeout(this.sessizlikZ);
    if (this.kapali || this._kapaniyor) return;
    // Sessizlik AI SUSTUKTAN sonra sayilir: AI konusurken / beyin dusunurken / musterinin bitmemis ara-sonucu
    // varken sayac sifirlanir. Eskiden pencere tur donunce basliyordu; 12 sn'lik onay cumlesinden sonra musteriye
    // 3 sn kaliyor, "yok teşekkürler" derken "Orada mısınız?" calıyordu (2026-11-17 kayit).
    let sessiz = 0;
    const tik = async () => {
      if (this.kapali || this._kapaniyor) return;
      if (this.mesgul || this.rtp.sesVarMi || this._seslendiriyor || this._sonInterim) sessiz = 0;
      else sessiz += 500;
      if (sessiz < cfg.sessizlikMs) { this.sessizlikZ = setTimeout(tik, 500); return; }
      if (ikinci) { log.debug('ikinci sessizlik -> kapat'); this._kapatSirasi(); return; }
      log.debug('sessizlik zaman asimi -> orada misiniz');
      await this._seslendir('Orada mısınız? Yardımcı olabileceğim başka bir şey var mı?');
      this._sessizlikSifirla(true); // ikinci pencere; yine sessizse kapat
    };
    this.sessizlikZ = setTimeout(tik, 500);
  }

  async _kapatSirasi() {
    // Kapanis cumlesi TAM calinip bitene kadar bekle (yarida kesme), sonra hatti kapat. Max ~18sn guvenlik.
    let gecen = 0;
    const tik = () => {
      if (this.kapali) return;
      gecen += 300;
      if (this.rtp && this.rtp.sesVarMi && gecen < 18000) { this._kapatZ = setTimeout(tik, 300); return; }
      this._kapatZ = setTimeout(() => this.onBitir(this.kanalId), 600); // son kareler de gitsin
    };
    // once seslendirmenin kuyruga girmesine firsat ver, sonra beklemeye basla
    this._kapatZ = setTimeout(tik, 700);
  }

  async kapat(sonuc) {
    if (this.kapali) return;
    this.kapali = true;
    clearTimeout(this.sessizlikZ);
    clearTimeout(this._kapatZ);
    clearTimeout(this._interimZ);
    this.stt.kapat();
    this.rtp.kapat();
    if (this.oturumId) await brain.bitir(this.oturumId, sonuc || 'kapandi');
    log.info(`Oturum #${this.oturumId || '-'} kapandi (kanal ${this.kanalId})`);
  }
}

module.exports = { CagriOturumu };
