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

// KISA TEPKI (algilanan gecikme): musteri sustuktan ~350 ms sonra cevap hala hazir degilse onbellekten aninda
// dogal bir tepki calar (Callivo kalibi). Sorgu gerektiren istek -> "bakiyorum"; kisa cevap/secim -> "Tamam.";
// tesekkur/veda -> hicbir sey. Asistan cevabi ayni kelimeyle basliyorsa o kelime atilir (iki kez "Tamam" olmasin).
const TEPKI_SORGU = ['Tabii, hemen bakıyorum.', 'Bir bakayım.', 'Hemen bakıyorum.'];
// Tek basina "Peki." havada kaliyordu (kayit 166) -> "Tabii, anladım."
const TEPKI_KISA = ['Tabii, anladım.', 'Anladım.', 'Tamam.'];
const TEPKI_GENEL = ['Tabii.'];
// Cevap hazirlanirken gelen BOS TEKRAR ("evet" ikinci kez, "alo", "duyuyor musunuz"): yeni bilgi yok -> hazirlanan
// cevap COPE ATILMAZ (canli 2026-11-18 kayit 176: ikinci "evet" neredeyse hazir cevabi iptal ettirip ayni isi
// bastan yaptirdi, 9 sn sessizlik; musteri sikilip kapatti). "alo" turlerine kisa "buradayim" sozu calar.
const BOS_TEKRAR = /^(evet|evet evet|evet efendim|tamam|tamamdır|tamam tamam|olur|olsun|peki|aynen|he|hı hı|hıhı|ıhı|alo|alo alo|efendim|duyuyor musunuz|beni duyuyor musunuz|orada mısınız|hala orada mısınız|hâlâ orada mısınız|ses gelmiyor|sesiniz gelmiyor)$/;
const BURADA_MI = /(alo|duyuyor|orada|ses gelmiyor|sesiniz gelmiyor)/;
const TEPKI_BURADA = 'Buradayım, hemen bakıyorum.';
// "bakiyorum" yalniz gercekten takvim/kayit sorgusu gerektiren isteklerde (gun/saat/musaitlik/iptal/degisiklik)
const SORGULU = /(müsait|musait|uygun|boş|bos\b|saat|yarın|yarin|bugün|bugun|haftaya|pazartesi|salı|sali|çarşamba|carsamba|perşembe|persembe|cuma|pazar|iptal|ertele|değiştir|degistir|kaydır|kaydir|güncelle|guncelle)/i;
const TEPKI_BAS = /^\s*(tabii,? anladım|harika|çok güzel|süper|tamamdır|tamam|tabii ki|tabii|tabi|peki|anladım|elbette|hemen bakıyorum|bakıyorum)\s*[,.!]?\s+(?=\S)/i;

// Bekleme cumlesi YALNIZ kayit/takvim aranan isteklerde calar (randevu, saat, gun, iptal, paket...).
// "Evet", "fark etmez", isim soyleme gibi kisa cevaplarda "Hemen bakıyorum" anlamsiz kaciyordu.
const DOLGULU = /(randevu|müsait|musait|uygun|boş|bos\b|saat|yarın|yarin|bugün|bugun|haftaya|pazartesi|salı|sali|çarşamba|carsamba|perşembe|persembe|cuma|pazar|iptal|ertele|değiştir|degistir|kaydır|kaydir|paket|seans|kontrol)/i;

// Bu cumlelerde bekleme cumlesi CALMA (vedalasma/tesekkur/ret: "Hemen bakıyorum. Hoşçakalın" sacmaligi, kayit #55)
const DOLGUSUZ = /(teşekkür|tesekkur|sağ ?ol|sag ?ol|iyi günler|iyi akşamlar|iyi aksamlar|hoşça|hosca|görüşürüz|gorusuruz|gerek yok|başka (bir )?(şey|sey|konu)? ?yok|bu kadar|o kadar|kapat)/i;

// u-law bayt -> mutlak genlik (16 bit olcek) tablosu: ses aktivitesi (VAD) icin
const ULAW_ABS = (() => {
  const t = new Int16Array(256);
  for (let i = 0; i < 256; i++) {
    const u = ~i & 0xFF, exp = (u >> 4) & 7, man = u & 0x0F;
    t[i] = (((man << 3) + 0x84) << exp) - 0x84;
  }
  return t;
})();
const VAD_ESIK = 300;          // 20 ms kare ortalama genligi bunun ustu = konusma (kayit #70: sessizlik 15-50, "Fark etmez" 500-4900)
const VAD_MIN_KARE = 6;        // araliksiz olmasa da (400 ms'den kisa bosluklarla) en az 6 konusma karesi = ~120 ms
// Kapali kisa cevaplar (tum ifade bunlardan olusur): evet/hayir/olur/tamam/fark etmez/siralama/saat rakami
const KAPALI_CEVAP = /^\s*(evet|hayır|hayir|olur|olmaz|tamam(dır)?|uygun|aynen|kesinlikle|fark etmez|farketmez|birincisi|ikincisi|üçüncüsü|ilki|ikincisi|sonuncusu|onaylıyorum|onayladım|\d{1,2}([:.]\d{2})?|on( bir| iki| üç| dört| beş| altı| yedi| sekiz| dokuz)?|dokuz|sekiz|yedi)\s*(olsun|lütfen|efendim)?\s*[.!?]?\s*$/i;
const KOR_NOKTA_MS = 1500;     // konusma bitti + bu kadar surede STT'den HIC sonuc yok -> kurtar
const TAMPON_MS = 6000;        // son 6 sn musteri sesi (kurtarmada STT'ye yeniden verilir)

// Tekrar karsilastirmasi icin: kucuk harf + noktalama/bosluk sadelestirme ("Tay makajı." == "tay makajı")
// "Evet" -> "Evet, oluşturalım": eski cumleye yalniz onay/dolgu kelimeleri eklenmis mi? (yeni bilgi yok)
const ONAY_KELIME = new Set(['evet', 'tamam', 'tamamdır', 'olur', 'olsun', 'oluşturalım', 'oluşturun', 'oluştur', 'oluşturabilirsiniz',
  'lütfen', 'kaydet', 'kaydedin', 'kaydedelim', 'onaylıyorum', 'onayla', 'aynen', 'peki', 'tabii', 'tabi', 'efendim', 'yapalım', 'alalım',
  'hı', 'he', 'öyle', 'uygun', 'olabilir', 'ok', 'okey']);
// Onay fiilleri her ekle: oluştur(alım|dum|un|abilirsiniz), kaydet/kayded, onayla, ayarla; soru ekleri
const ONAY_KOK = /^(oluştur|olustur|kaydet|kayded|onayl|ayarla|tamam|evet|alalım|alabilir|yapalım|yapabilir|olabilir|m[ıiuü]s[ıiuü]n[ıiuü]z|m[ıiuü]y[ıiuü]m)/;
function onayUzantisi(eski, yeni) {
  const e = sadeMetin(eski).split(' ').filter(Boolean);
  const y = sadeMetin(yeni).split(' ').filter(Boolean);
  let i = 0;
  while (i < e.length && y[i] === e[i]) i++;
  // eski cumle yeni cumlenin basinda degilse (paralel = eski + yeni ek) yeni kisim eski cumleden sonrasidir
  const ek = (i === e.length ? y.slice(i) : y);
  // Kelime listesi yetmez: ses tanima "oluşturalım"i "oluşturdum" duydu (kayit 180) -> kokten de bak
  return ek.length > 0 && ek.every((w) => ONAY_KELIME.has(w) || ONAY_KOK.test(w));
}

function sadeMetin(s) {
  return String(s || '').toLocaleLowerCase('tr').replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
}

// HAT YANKISI (kayit 2026-11-23 12.52): asistan "...başka bir şey var mı?" dedi, ~0,8 sn sonra kendi sesi hattan
// donup STT'ye "Başka bir şeyiniz var mı?" diye dustu, asistan buna calisma saatleriyle cevap verdi.
// Yanki = asistanin az once soyledigi cumlenin buyuk kismini (kelime koklerine gore) aynen tekrar eden musteri metni.
// "Yarın saat 14 uygun" gibi kisa teyitler (asistan cumlesinin yarisindan azi) yanki SAYILMAZ.
function yankiBenzer(musteri, asistan) {
  // "şey" ~ "şeyiniz": kisa olan uzunun basiysa (en az 3 harf) ayni kelime; kisa kelimeler ("mı", "bir") birebir
  const ayni = (x, y) => x === y || (Math.min(x.length, y.length) >= 3 && (x.startsWith(y) || y.startsWith(x)));
  const m = sadeMetin(musteri).split(' ').filter(Boolean);
  const a = sadeMetin(asistan).split(' ').filter(Boolean);
  if (m.length < 3 || !a.length) return false;
  const ortak = m.filter((w) => a.some((x) => ayni(w, x))).length;
  const kapsanan = a.filter((x) => m.some((w) => ayni(w, x))).length;
  return ortak / m.length >= 0.8 && kapsanan / a.length >= 0.5;
}

// AYNI CUMLENIN IKINCI FINALI (kayit 280/281): "Fiyatı almak istiyorum" islendi, Google birkac yuz ms sonra hafif
// farkli final verdi ("Fiyatını almak istiyorum") -> ayri tur sayilip ayni soru iki kez okunuyordu. Kelimelerin
// (kok eslesmesiyle) en az %75'i iki yonde ortaksa ayni cumledir.
function ayniCumle(a, b) {
  const ayni = (x, y) => x === y || (Math.min(x.length, y.length) >= 3 && (x.startsWith(y) || y.startsWith(x)));
  const x = String(a || '').split(' ').filter(Boolean);
  const y = String(b || '').split(' ').filter(Boolean);
  if (x.length < 2 || y.length < 2) return false;
  if (b.startsWith(a) || a.startsWith(b)) return false;   // uzatma ("saat 3" -> "saat 3 buçuk") yeni bilgidir
  const sayi = (z) => z.filter((w) => /d/.test(w)).join(' ');
  if (sayi(x) !== sayi(y)) return false;                 // rakam farki = farkli cumle
  const xy = x.filter((w) => y.some((v) => ayni(w, v))).length / x.length;
  const yx = y.filter((v) => x.some((w) => ayni(w, v))).length / y.length;
  return xy >= 0.75 && yx >= 0.75;
}

class CagriOturumu {
  constructor({ kanalId, telefon, subeId, rtpPort, onAktar, onBitir, geriAramaOturum, hatirlatma, baslatP, hazirGiris }) {
    this.hazirGiris = hazirGiris || null; // {giris, ses}: salonun sabit ilk cumlesi (index.js saklar)
    this.baslatP = baslatP || null; // index.js hat kurulurken baslatti (karsilama TTS'i de uretiliyor)
    this._onKaynak = null;           // on-seslendirilen karsilama parcalari: metin -> Promise<kaynak> (maliyet dogru sayilsin)
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
    // ISLETMENIN SESI (Laravel baslat -> tts_ses) + NET MALIYET OLCUMU (bitir'de Laravel'e gider)
    this.ttsSes = null;
    this.olcum = { ttsKarakter: 0, elKarakter: 0, soylenen: 0, onbellek: 0 };
    // Her seslendirme islemi isaretlenir: kaynak (y=yeni uretim / o=onbellek) + calindi mi -> bitir'de Laravel'e
    this.ttsDetay = [];
    this._basT = Date.now();

    this.rtp = new RtpOturumu(rtpPort, (payload) => {
      // YARI-DUPLEKS: barge-in kapaliyken AI konusurken mikrofonu STT'ye VERME.
      // Boylece hat yankisi (AI'nin kendi sesi) STT'ye dusup AI'yi kesmez / kendini dinlemez.
      if (!cfg.bargeIn && this.rtp && this.rtp.sesVarMi) return;
      // AKTARMA BEKLEMESI: musteri zil dinliyor; sesi STT'ye verme (ucret + zil sesi yanlis tur acmasin)
      if (this._aktarmada) {
        if (!this._sttDurdu) { this._sttDurdu = true; this.stt.durdur(); }
        return;
      }
      if (!this._sesGeldi) { this._sesGeldi = true; log.info('ilk ses karesi STT ye ulasti (mikrofon calisiyor)'); }
      // ENERJI TESHISI: giden ses gercek mi sessizlik mi? (ulaw sessizlik ~0xFF/0x7F)
      this._ornek = (this._ornek || 0) + 1;
      if (this._ornek <= 250) {
        let dolu = 0;
        for (let i = 0; i < payload.length; i++) { const b = payload[i]; if (b !== 0xFF && b !== 0x7F && b !== 0xFE && b !== 0x7E && b !== 0x00) dolu++; }
        this._enerjiTop = (this._enerjiTop || 0) + (payload.length ? dolu / payload.length : 0);
        if (this._ornek === 250) log.info(`SES ENERJI: ort dolu-oran ${(this._enerjiTop / 250).toFixed(2)} (0'a yakin=SESSIZLIK/sesin gelmiyor, >0.3=gercek ses var)`);
      }
      this._vad(payload);
      // STT MALIYETI: AI konusurken musteri sesi Google'a GONDERILMEZ (akis kapanir; ucret islenen ses suresine gore).
      // Musteri araya girerse (VAD) akis aninda yeniden acilir ve son ~0,8 sn ses de verilir -> barge-in korunur.
      const aiKon = this.rtp && (this.rtp.sesVarMi || this._seslendiriyor);
      if (!aiKon) this._bargeAcik = false;
      else this._aiSesSon = Date.now(); // yanki filtresi: asistanin sesi en son ne zaman hattaydi
      if (cfg.sttDuraklat && cfg.mediaFormat !== 'slin16' && aiKon && !this._bargeAcik) {
        if (!this._sttDurdu) { this._sttDurdu = true; this.stt.durdur(); log.debug('STT duraklatildi (AI konusuyor)'); }
        return;
      }
      if (this._sttDurdu) {
        this._sttDurdu = false;
        const simdi = Date.now();
        this.stt.devam((this._tampon || []).filter((x) => x.t >= simdi - 800).map((x) => x.b)); // bu kare dahil
        return;
      }
      this.stt.yaz(payload);
    });
    this.stt = new SttOturumu(
      (final, guven) => this._finalMetin(final, guven),
      (interim) => this._interim(interim),
    );
  }

  async basla() {
    // HIZLI ACILIS: salonun sabit girisi biliniyorsa Laravel'i BEKLEMEDEN hemen calar; kisiye ozel devami
    // ("Ferdi Bey, ben Ayşe...") Laravel'den gelince arkasindan okunur.
    let girisP = null;
    const giris = this.hazirGiris && this.baslatP ? String(this.hazirGiris.giris || '') : '';
    if (giris) {
      if (this.hazirGiris.ses) this.ttsSes = String(this.hazirGiris.ses);
      log.info(`hizli acilis (Laravel beklenmeden): ${giris}`);
      girisP = this._seslendir(giris, true).catch(() => {});
    }
    try {
      let d;
      if (this.baslatP) {
        const r = await this.baslatP;
        if (r.e) throw r.e;
        d = r.d;
        this._onKaynak = r.onKaynak || null;
      } else {
        d = await brain.baslat(this.subeId, this.telefon, 'santral', this.geriAramaOturum, this.hatirlatma);
      }
      this.oturumId = d.oturum_id;
      if (d.tts_ses) this.ttsSes = String(d.tts_ses);
      // Salonun hizmet adlari -> STT ipucu (karsilama calarken akis yeni config ile yeniden acilir)
      if (Array.isArray(d.stt_ipuclari) && d.stt_ipuclari.length) { try { this.stt.ipucuEkle(d.stt_ipuclari); } catch (e) { log.warn('STT ipucu eklenemedi: ' + e.message); } }
      log.info(`Oturum #${this.oturumId} basladi (kanal ${this.kanalId}, tel ${this.telefon || '-'}, sube ${d.sube_id ?? this.subeId}, menu ${d.menu_adet ?? '?'} urun, musteri ${d.musteri || 'YENI'})`);
      if (d.menu_adet === 0) log.warn('DIKKAT: bu subede aktif urun YOK -> AI menuyu tanitamaz (DEFAULT_SUBE_ID dogru mu? urunler.aktif=1 mi?)');
      if (d.musteri) log.info(`musteri taninID: ${d.musteri}${d.son_siparis ? ' | gecen siparis: ' + d.son_siparis : ' | gecen siparis YOK'}`);
      let karsilama = d.karsilama || 'Merhaba, size nasıl yardımcı olabilirim?';
      if (girisP) {
        await girisP;
        if (karsilama.startsWith(giris)) karsilama = karsilama.slice(giris.length).trim();
        else log.warn('hizli acilis: salonun girisi degismis, karsilama tam okunuyor');
      }
      // Ilk aramada (giris henuz saklanmamis) de karsilamanin sabit kismi TEK PARCA calar (index.js tek parca on-seslendirdi)
      const kGiris = String(d.karsilama_giris || '');
      if (!girisP && kGiris && karsilama.startsWith(kGiris)) {
        await this._seslendir(kGiris, true);
        karsilama = karsilama.slice(kGiris.length).trim();
      }
      if (karsilama) await this._seslendir(karsilama);
      // Bekleme cumlelerini onceden seslendir (onbellege al) -> gerektiginde aninda calsin
      if (cfg.dolguMs > 0) for (const c of DOLGU) this._tts(c).then((r) => this._ttsKaydet(c, r.kaynak, false)).catch(() => {});
      if (cfg.tepkiMs > 0) for (const c of TEPKI_SORGU.concat(TEPKI_KISA, TEPKI_GENEL, [TEPKI_BURADA])) this._tts(c).then((r) => this._ttsKaydet(c, r.kaynak, false)).catch(() => {});
    } catch (e) {
      const neden = e.response && e.response.data && e.response.data.hata;
      if (neden === 'paket_yok' || neden === 'paket_bitti') {
        // Salonun asistan paketi yok/bitti: anons YAPMADAN dogrudan isletmeye (operator_kanali) aktar
        log.info(`Asistan kapali (${neden}, sube ${this.subeId}) -> dogrudan isletmeye aktariliyor`);
        this.onAktar(this.kanalId);
        return;
      }
      log.error('Beyin baslat hatasi:', e.message);
      await this._seslendir('Sizi yetkiliye bağlıyorum, lütfen kapatmayın.');
      this.onAktar(this.kanalId);
    }
    this._sessizlikSifirla();
  }

  // ---- STT KOR NOKTA KURTARMA (kayit #70: musteri "Fark etmez" dedi, Google HIC sonuc dondurmedi -> asistan
  // 15 sn sessiz kalip "Orada mısınız?" dedi). Musteri sesi enerjiyle algilanir; konusma bittikten 1,5 sn sonra
  // hala ara/son sonuc yoksa STT akisi yeniden acilir ve son konusmanin sesi yeni akisa YENIDEN verilir.
  _vad(payload) {
    const simdi = Date.now();
    this._tampon = this._tampon || [];
    this._tampon.push({ t: simdi, b: payload });
    while (this._tampon.length && simdi - this._tampon[0].t > TAMPON_MS) this._tampon.shift();
    if (cfg.mediaFormat === 'slin16' || !payload.length) return; // genlik tablosu u-law icin
    let top = 0;
    for (let i = 0; i < payload.length; i++) top += ULAW_ABS[payload[i]];
    const konusma = top / payload.length > VAD_ESIK;
    const aiKonusuyor = this.rtp && (this.rtp.sesVarMi || this._seslendiriyor);
    // AI konusurken musteri sesi: araya girme adayi -> STT akisini ac (asil barge-in karari STT ara-sonucuyla verilir)
    if (konusma && aiKonusuyor && !this._bargeAcik) {
      if (simdi - (this._bargeSon || 0) > 400) this._bargeKare = 0;
      this._bargeKare = (this._bargeKare || 0) + 1;
      this._bargeSon = simdi;
      if (this._bargeKare >= 5) { this._bargeAcik = true; log.info('musteri AI konusurken konusuyor -> STT acildi'); }
    }
    if (konusma && !aiKonusuyor) {
      if (simdi - (this._vadSon || 0) > 400) { this._vadKare = 0; this._vadIlk = simdi; }
      this._vadKare = (this._vadKare || 0) + 1;
      this._vadSon = simdi;
      if (this._vadKare >= VAD_MIN_KARE && !this._konusmaBas) {
        this._konusmaBas = this._vadIlk;
        tts.isit(); // musteri konusuyor: cevap gelince TTS baglantisi hazir olsun
      }
      if (this._konusmaBas) this._konusmaSon = simdi;
    }
    // HIZLI CUMLE SONU (VAD): ara-metin var, musterinin sesi gercekten kesildi, metin 250 ms sabit -> bitti say.
    // Sesi esigin altinda kalan (kisik) arayanda VAD konusmayi hic gormez -> bu yol devreye GIRMEZ
    // (_vadSon bu cumleden eski), yedek zamanlayici calisir; yani kisik seste cumle ortasinda kesmez.
    if (this._sonInterim && !aiKonusuyor && !this.mesgul && !this.kapali && !this._kapaniyor
        && (this._vadSon || 0) >= (this._ifadeBasT || 0) - 1500) {
      const kelime = this._sonInterim.split(/\s+/).length;
      // Kapali kisa cevap ("evet", "olur", "fark etmez", "birincisi", "13", "on dört") cumlenin devami olasiligi dusuk
      // -> daha erken bitir. Devam ederse paralel tur mekanizmasi tam cumleyle yeniden sorar.
      const esik = (kelime <= 3 && KAPALI_CEVAP.test(this._sonInterim)) ? cfg.vadBitisKapaliMs
        : (kelime <= 3 ? cfg.vadBitisKisaMs : cfg.vadBitisUzunMs);
      const sessiz = simdi - (this._vadSon || 0);
      if (esik > 0 && sessiz >= esik && simdi - (this._interimT || 0) >= 250) {
        log.debug(`endpointing (VAD ${sessiz} ms sessiz): ${this._sonInterim}`);
        clearTimeout(this._interimZ);
        this._interimZorla();
      }
    }
    if (this._konusmaBas && this._konusmaSon && simdi - this._konusmaSon > KOR_NOKTA_MS) {
      const bas = this._konusmaBas;
      this._konusmaBas = null; this._konusmaSon = null;
      if ((this._sttSon || 0) >= bas) return;                      // STT duydu, sorun yok
      if (this.mesgul || this.kapali || this._kapaniyor || aiKonusuyor) return;
      if (simdi - (this._sonKurtarma || 0) < 8000) return;         // ard arda kurtarma yapma
      this._sonKurtarma = simdi;
      const parcalar = this._tampon.filter((x) => x.t >= bas - 700).map((x) => x.b);
      log.warn(`STT KOR NOKTA: musteri ~${Math.round((simdi - bas - KOR_NOKTA_MS) / 100) / 10} sn konustu ama metin gelmedi -> akis yenilenip ses yeniden veriliyor (${parcalar.length} kare)`);
      try { this.stt.kurtar(parcalar); } catch (e) { log.warn('STT kurtarma hatasi: ' + e.message); }
    }
  }

  _interim(metin) {
    this._sttSon = Date.now();
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
    if (!this._sonInterim) this._ifadeBasT = Date.now(); // bu cumlenin ilk ara-metni
    this._sonInterim = (metin || '').trim();
    this._interimT = Date.now();
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

  async _finalMetin(metin, guven) {
    this._sttSon = Date.now();
    this._sttGuven = typeof guven === 'number' && guven > 0 ? guven : null; // 0 = Google vermedi
    if (this.kapali || this._kapaniyor || this._aktarmada) return; // kapanis/aktarma beklemesinde yeni tur baslatma
    clearTimeout(this._interimZ); this._sonInterim = '';
    const n = sadeMetin(metin);
    if (n.length < 1) return;
    if (this._yankiMi(metin)) { log.info(`  (hat yankisi, yok sayildi): ${metin}`); return; }
    // Yineleme onle: zorla-final (interim) ile islenen cumle, Google'in gercek final'iyle ("Tay makajı." gibi
    // noktalamali / biraz uzamis haliyle) ikinci kez TUR olmasin. Eskiden noktalama farki ayri tur yapiyordu ->
    // asistan ayni soruya iki cevap ust uste okuyordu (2026-11-16 kayit #51).
    const simdi = Date.now();
    const onceki = this._sonIslenen;
    // CUMLE DEVAMI CALARKEN (kayit 2026-11-23 12.52): musteri "Bugün" deyip duraksadi, cevap ("saat 12 ... hangisi?")
    // calmaya basladiginda "saat 3" geldi -> eski cevap sonuna kadar okunup ardindan ikinci cevap geliyordu.
    // Musteri bu cumleye cevabi DUYMADAN basladiysa (ilk ara-metin ses baslamadan once / ilk 0,8 sn icinde) devamidir:
    // cevabi kes, tur gecmisten dusulsun, iki parca birlestirilip tek turda sorulsun. Is yapan cevap (randevu/aktar) kesilmez.
    const c = this._caliyor;
    if (this.mesgul && c && !this._bekleyen && !BOS_TEKRAR.test(n)
        && !(onceki && (onceki.n === n || onceki.n.startsWith(n)))
        && (this._ifadeBasT || 0) > c.t0 && (this._ifadeBasT || 0) < c.sesBasT + 800) {
      log.info(`> musteri (cumle devami, cevap kesildi): ${metin}`);
      this._sonIslenen = { n, t: simdi };
      this._caliyor = null;
      this._kesmeNo = (this._kesmeNo || 0) + 1;
      this.rtp.sustur();
      brain.turIptal(this.oturumId, c.tur);
      this._bekleyen = n.startsWith(sadeMetin(c.metin)) ? metin : c.metin + ' ' + metin;
      return;
    }
    if (onceki && (simdi - onceki.t) < 5000) {
      if (onceki.n === n || onceki.n.startsWith(n)) { log.debug('yinelenen final atlandi'); return; }
      if (ayniCumle(onceki.n, n)) { log.info(`  (ayni cumlenin ikinci finali, yok sayildi): ${metin}`); return; }
      if (n.startsWith(onceki.n) && this.mesgul && !this._bekleyen && BOS_TEKRAR.test(n.slice(onceki.n.length).trim())) {
        // "Evet" -> "Evet evet": devami ama yeni bilgi yok -> mevcut tur surer (yeniden sorulmaz)
        log.info(`> musteri (devami, bos tekrar -> yok sayildi): ${metin}`);
        this._sonIslenen = { n, t: simdi };
        return;
      }
      if (c && n.startsWith(onceki.n) && n.startsWith(sadeMetin(c.metin)) && !this._bekleyen) {
        // Kayit 317: "Yarın" (erken final) -> kod cevabi hemen calmaya basladi ("Cuma saat kaçta uygun?"), musteri
        // "...öğleden sonra olsun" diye devam etti -> iki cevap ust uste okunuyordu. Calan cevap yarim cumleye aitti:
        // kes, tur gecmisten dusulsun, tam cumle tek turda sorulsun (is yapan cevap zaten _caliyor'a girmez).
        log.info(`> musteri (devami, calan cevap kesildi): ${metin}`);
        this._sonIslenen = { n, t: simdi };
        this._caliyor = null;
        this._kesmeNo = (this._kesmeNo || 0) + 1;
        this.rtp.sustur();
        brain.turIptal(this.oturumId, c.tur);
        this._bekleyen = metin;
        return;
      }
      if (n.startsWith(onceki.n) && this.mesgul && !this._bekleyen) {
        // Final, islenmekte olan interim'in DEVAMI: onceki cevabi calma, tam cumleyle yeniden sor
        log.info(`> musteri (devami): ${metin}`);
        this._sonIslenen = { n, t: simdi };
        this._eskidi = true;
        if (!this._paralelBaslat(metin)) this._bekleyen = metin;
        return;
      }
    }
    this._sonIslenen = { n, t: simdi };
    log.info(`> musteri: ${metin}`);
    // Beyin dusunurken musteri yeni bir sey soyledi: onceki cevap ESKIDI (calinmayacak, aksiyon yoksa);
    // bekleyen cumleler birlestirilip tek turda sorulur.
    if (this.mesgul && BOS_TEKRAR.test(n)) {
      log.info('  (bos tekrar: hazirlanan cevap korunuyor, yeniden sorulmadi)');
      if (BURADA_MI.test(n)) this._tepkiCal(TEPKI_BURADA);
      return;
    }
    // CUMLE DEVAMI CALARKEN (kayit 2026-11-23 12.52): musteri "Bugün" deyip duraksadi, cevap ("saat 12 ... hangisi?")
    // calmaya basladiginda "saat 3" geldi -> eski cevap sonuna kadar okunup ardindan ikinci cevap geliyordu.
    // Musteri bu cumleye cevabi DUYMADAN basladiysa (ilk ara-metin ses baslamadan once / ilk 0,8 sn icinde) devamidir:
    // cevabi kes, tur gecmisten dusulsun, iki parca birlestirilip tek turda sorulsun. Is yapan cevap (randevu/aktar) kesilmez.
    if (this.mesgul) {
      this._eskidi = true;
      if (!this._bekleyen && this._aktifTur && this._paralelBaslat(this._aktifTur.metin + ' ' + metin)) return;
      this._bekleyen = this._bekleyen ? this._bekleyen + ' ' + metin : metin;
      return;
    }
    await this._isle(metin);
    while (this._bekleyen && !this.kapali) {
      const m = this._bekleyen; this._bekleyen = null;
      // Is yapan turun ("Evet" -> randevu) yalniz onay uzantisi ("Evet, oluşturalım") ayrica sorulmaz (kayit 177)
      if (this._sonIsMetni && onayUzantisi(this._sonIsMetni, m)) { log.info(`  (bekleyen yalniz onay uzantisi, sorulmadi): ${m}`); continue; }
      await this._isle(m);
    }
  }

  // Bu musteri cumlesine hangi kisa tepki uygun? (null = tepki yok)
  _tepkiSec(metin) {
    const m = (metin || '').trim();
    if (!m || DOLGUSUZ.test(m)) return null;
    // Selamlasmaya "Anladım." denmez (kayit 176: "Merhabalar" -> "Anladım. Merhaba...")
    if (/^(merhaba|merhabalar|selam|selamlar|iyi günler|günaydın|iyi akşamlar|alo)[\s.,!?]*$/i.test(m)) return null;
    if (SORGULU.test(m)) { this._tepkiNo = ((this._tepkiNo || 0) + 1) % TEPKI_SORGU.length; return TEPKI_SORGU[this._tepkiNo]; }
    if (DOLGULU.test(m)) return TEPKI_GENEL[0]; // "randevu almak istiyorum" -> "Tabii." (ardindan soru gelecek)
    if (m.split(/\s+/).length <= 3) { this._tepkiNo = ((this._tepkiNo || 0) + 1) % TEPKI_KISA.length; return TEPKI_KISA[this._tepkiNo]; }
    return null;
  }

  _tepkiCal(c) {
    if (this.kapali || this._kapaniyor || !this.mesgul || this.rtp.sesVarMi) return;
    this._tts(c).then(({ ses, kaynak }) => {
      if (this.kapali || !this.mesgul || this.rtp.sesVarMi || !ses || !ses.length) { this._ttsKaydet(c, kaynak, false); return; }
      this._tepkiCaldi = true;
      log.info(`~ tepki: ${c}`);
      this.rtp.oynat(ses);
      this._ttsKaydet(c, kaynak, true);
    }).catch(() => {});
  }

  _dolguCal() {
    if (this.kapali || this._kapaniyor || !this.mesgul) return;
    if (this.rtp.sesVarMi) return; // AI hala onceki cumleyi okuyor
    this._dolguNo = ((this._dolguNo || 0) + 1) % DOLGU.length;
    const c = DOLGU[this._dolguNo];
    this._tts(c).then(({ ses, kaynak }) => {
      if (this.kapali || !this.mesgul || this.rtp.sesVarMi || !ses || !ses.length) { this._ttsKaydet(c, kaynak, false); return; }
      log.info(`~ bekleme: ${c}`);
      this.rtp.oynat(ses);
      this._ttsKaydet(c, kaynak, true);
    }).catch(() => {});
  }

  // PARALEL TUR (gecikme, 2026-11-17 log): musteri "Randevu" deyip duraksayinca tur erken basliyor, "...almak
  // istiyorum" gelince eski cevap calinmiyordu ama yeni tur ESKI TURUN BITMESINI bekliyordu -> her cevapta iki beyin
  // turu arka arkaya (1,6 + 1,5 sn). Artik devami gelir gelmez yeni tur hemen sorulur; eski tur is yapmadiysa
  // cevabi atilir (Laravel gecmisten duser), is yaptiysa (randevu/aktar) o calinir ve yeni cumle sirayla sorulur.
  _paralelBaslat(metin) {
    if (!this._beyinBekliyor || this._spek || !this._aktifTur || this.kapali || this._kapaniyor) return false;
    const tur = (this._turNo = (this._turNo || 0) + 1);
    const p = brain.konus(this.oturumId, metin, { tur, paralel_onceki: this._aktifTur.tur })
      .then((d) => ({ d }), (e) => ({ e }));
    this._spek = { tur, metin, p, t0: Date.now() };
    this._turBas = this._spek.t0;
    log.info(`  (paralel tur: eski cevap beklenmeden soruluyor)`);
    if (this._spekHaber) this._spekHaber(); // _yaris'i uyandir: artik paralel tur da yarista
    return true;
  }

  // Eski tur ile paralel tur yarisir: paralel tur ONCE ve basariyla biterse beklemeden o kullanilir (kayit 106:
  // "beni" -> eski tur 2,5 sn surerken "beni salona bağlar mısınız" aktarmasi kodla hemen hazirdi, 4,6 sn bekledi).
  async _yaris(eskiP) {
    for (;;) {
      const yaris = [eskiP.then((r) => ({ kim: 'eski', r }))];
      const spek = this._spek;
      if (spek) {
        yaris.push(spek.p.then((r) => (r.d && r.d.ok !== false && !r.d.iptal_edildi
          ? { kim: 'spek', r }
          : eskiP.then((r2) => ({ kim: 'eski', r: r2 })))));
      } else {
        yaris.push(new Promise((res) => { this._spekHaber = res; }).then(() => ({ kim: 'yeni' })));
      }
      const s = await Promise.race(yaris);
      this._spekHaber = null;
      if (s.kim !== 'yeni') return s;
    }
  }

  async _isle(metin) {
    this.mesgul = true;
    this._eskidi = false;
    const tur = (this._turNo = (this._turNo || 0) + 1);
    const ek = Object.assign({ tur }, this._oncekiDuyulmadi ? { onceki_duyulmadi: 1 } : {},
      this._sttGuven ? { stt_guven: this._sttGuven.toFixed(2) } : {});
    this._oncekiDuyulmadi = false;
    this._aktifTur = { tur, metin };
    this._tepkiCaldi = false;
    const tepki = cfg.tepkiMs > 0 ? this._tepkiSec(metin) : null;
    const dolguZ = tepki
      ? setTimeout(() => this._tepkiCal(tepki), cfg.tepkiMs)
      : ((cfg.dolguMs > 0 && DOLGULU.test(metin || '') && !DOLGUSUZ.test(metin || '')) ? setTimeout(() => this._dolguCal(), cfg.dolguMs) : null);
    const t0 = Date.now();
    this._turBas = t0;
    try {
      this._beyinBekliyor = true;
      const eskiP = brain.konus(this.oturumId, metin, ek).then((x) => ({ d: x }), (e) => ({ e }));
      const s = await this._yaris(eskiP);
      this._beyinBekliyor = false;
      clearTimeout(dolguZ);
      let d;
      let cal = { tur, metin, t0 }; // calinacak cevabin turu (paralel tur kazanirsa o)
      if (s.kim === 'spek') {
        const spek = this._spek;
        this._spek = null;
        d = s.r.d;
        cal = { tur: spek.tur, metin: spek.metin, t0: spek.t0 };
        log.info(`  (paralel tur ONCE bitti: ${Date.now() - spek.t0} ms, ilk turdan beri ${Date.now() - t0} ms; eski tur beklenmedi)`);
        eskiP.then((r) => {
          // Eski tur is yaptiysa gecmisi silinmez (onay sorularinda Laravel zaten sirali calistirir; burasi nadir)
          if (r.d && (r.d.aksiyon || r.d.bitir)) log.warn(`eski tur paralelden sonra is yapti (${r.d.aksiyon || 'bitir'}): ${r.d.cevap}`);
          else brain.turIptal(this.oturumId, tur);
        });
      } else {
        if (s.r.e) throw s.r.e;
        d = s.r.d;
        log.info(`  (beyin ${Date.now() - t0} ms)`); // gecikme teshisi: musteri sustu -> cevap metni hazir
      }
      if (s.kim === 'eski' && this._spek) {
        const spek = this._spek;
        if (!d || d.ok === false || (!d.aksiyon && !d.bitir)) {
          log.info(`< asistan (CALINMADI, devami paralel soruldu): ${d ? d.cevap : '-'}`);
          brain.turIptal(this.oturumId, tur);
          const r = await spek.p;
          this._spek = null;
          log.info(`  (beyin paralel ${Date.now() - spek.t0} ms, ilk turdan beri ${Date.now() - t0} ms)`);
          if (r.e) throw r.e;
          if (!r.d || r.d.iptal_edildi) { // olmamali (eski tur is yapmadi); guvenli yol: cumleyi sirayla sor
            this._bekleyen = this._bekleyen ? spek.metin + ' ' + this._bekleyen : spek.metin;
            return;
          }
          d = r.d;
          cal = { tur: spek.tur, metin: spek.metin, t0: spek.t0 };
        } else {
          // Eski tur is yapti (randevu/aktar/veda): o calinir; paralel tur bunu bilmeden kuruldu -> iptal,
          // musterinin cumlesi bu isin ardindan sirayla sorulur (eski davranis).
          // Aktarma/kapanista cumle YENIDEN SORULMAZ (canli 2026-11-17: "İşletme ile görüşmek istiyorum" iki kez
          // aktar cumlesi okuttu: "sizi işletmeye bağlıyorum" + "sizi bir yetkiliye aktarıyorum").
          this._spek = null;
          brain.turIptal(this.oturumId, spek.tur);
          // Paralel cumle eski cumlenin yalniz ONAY uzantisiysa ("Evet" -> "Evet, oluşturalım") yeniden SORULMAZ:
          // is zaten yapildi (canli 2026-11-19 kayit 177: randevudan sonra "Randevunuz zaten oluşturuldu..." okundu).
          if (d.aksiyon !== 'aktar' && d.aksiyon !== 'veda' && !d.bitir && !onayUzantisi(metin, spek.metin)) {
            this._bekleyen = this._bekleyen ? spek.metin + ' ' + this._bekleyen : spek.metin;
          } else if (onayUzantisi(metin, spek.metin)) {
            log.info(`  (paralel cumle yalniz onay uzantisi, yeniden sorulmadi): ${spek.metin}`);
          }
        }
      }
      // YALNIZ SELAM (kayit 320): "Merhaba," (virgul duraklamasi = ayri final) -> "Buyurun, sizi dinliyorum" musteri
      // "...randevu alacaktım" derken okunuyordu, ardindan asil cevap geldi. Selam cevabi kisa bekletilir; musteri
      // konusmaya devam ettiyse hic okunmaz, tur gecmisten dusulur, devam cumlesi normal sorulur.
      if (d && d.teshis === 'selam' && !d.aksiyon && !d.bitir) {
        const devamEtti = () => !!(this._bekleyen || this._sonInterim || this._eskidi || this._spek);
        const bas = Date.now();
        while (!this.kapali && !devamEtti() && Date.now() - bas < cfg.selamBekleMs) await new Promise((r) => setTimeout(r, 100));
        if (devamEtti()) {
          log.info(`< asistan (CALINMADI, selamdan sonra musteri devam etti): ${d.cevap}`);
          brain.turIptal(this.oturumId, cal.tur);
          return;
        }
      }
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
      this._sonIsMetni = d.aksiyon ? metin : null;
      if (kapanis) this._kapaniyor = true;
      this._olcum = { beyinHazir: Date.now() };
      // Kisa tepki caldiysa cevabin basindaki ayni turden kelimeyi at ("Tamam. Tamam, ..." olmasin)
      if (this._tepkiCaldi && d.cevap && TEPKI_BAS.test(d.cevap)) {
        const yeni = d.cevap.replace(TEPKI_BAS, '');
        d.cevap = yeni.charAt(0).toLocaleUpperCase('tr') + yeni.slice(1);
      }
      if (!d.aksiyon && !kapanis) this._caliyor = Object.assign(cal, { sesBasT: Date.now() });
      if (d.cevap) await this._seslendir(d.cevap);
      this._caliyor = null;
      this._olcum = null;

      if (d.aksiyon === 'aktar') { this._bekleyen = null; this.onAktar(this.kanalId); return; }
      if (kapanis) { this._bekleyen = null; await this._kapatSirasi(); return; }
    } catch (e) {
      clearTimeout(dolguZ);
      log.error('Beyin konus hatasi:', e.message);
      await this._seslendir('Bir sorun oluştu, sizi yetkiliye bağlıyorum.');
      this.onAktar(this.kanalId);
    } finally {
      this._beyinBekliyor = false;
      this._spek = null;
      this._spekHaber = null;
      this.mesgul = false;
      this._sessizlikSifirla();
    }
  }

  /** Isletmenin sesiyle seslendir -> {ses, kaynak}. Google'a giden karakter bu cagriya yazilir. */
  async _tts(c) {
    const opt = { ses: this.ttsSes, olcum: this.olcum };
    const ses = await tts.seslendir(c, opt);
    // Karsilama parcasi hat kurulurken on-seslendirildiyse ucreti o uretimindir ('yeni'), burada 'onbellek' gorunur
    if (this._onKaynak && this._onKaynak[c]) { const k = await this._onKaynak[c]; delete this._onKaynak[c]; if (k) opt.kaynak = k; }
    return { ses, kaynak: ses && ses.length ? (opt.kaynak || 'yeni') : null };
  }

  /**
   * SESLENDIRME ISARETI: her islem kaynak (y=yeni uretim, ucretli / o=onbellek, ucretsiz) + calindi mi.
   * Maliyet = calinan + uretilen her karakter liste fiyatiyla (onbellek yokmus gibi); onbellek = kazanc (kar).
   * Onbellekten gelip calinmayan islem maliyetsiz -> kaydedilmez.
   */
  _ttsKaydet(c, kaynak, calindi) {
    if (!kaynak || (!calindi && kaynak !== 'yeni')) return;
    const k = Array.from(String(c || '').trim()).length;
    if (!k) return;
    if (calindi) this.olcum.soylenen += k;
    if (calindi && kaynak === 'onbellek') this.olcum.onbellek += k;
    if (this.ttsDetay.length < 400) {
      this.ttsDetay.push({ m: String(c).trim().slice(0, 160), k, src: kaynak === 'yeni' ? 'y' : 'o', c: calindi ? 1 : 0,
        sn: Math.round((Date.now() - this._basT) / 100) / 10 });
    }
  }

  // tekParca: metni cumlelere BOLMEDEN tek seslendirme (karsilama: tek tonlama, cumleler arasi bosluk yok; kayit 243)
  async _seslendir(metin, tekParca) {
    // Cumle-cumle seslendir: ilk cumle hemen calar, sonrakiler o calariken uretilir (algilanan gecikme duser).
    // BARGE-IN: musteri araya girince (_kesmeNo artar) kalan cumleler URETILMEZ/CALINMAZ.
    // (Eskiden sustur() yalniz kuyrugu bosaltiyordu; dongu sonraki cumleyi yine calip AI'yi "devam ettiriyordu".)
    const kesme = this._kesmeNo || 0;
    const cumleler = tekParca ? [String(metin || '').trim()].filter(Boolean) : this._cumlelereBol(metin);
    this._seslendiriyor = true;
    try {
      for (const c of cumleler) {
        if (this.kapali || (this._kesmeNo || 0) !== kesme) break;
        let ilkSes = false;
        if (tts.akisli()) {
          // ElevenLabs: ses geldikce cal (kesildiyse gelen parcalar calinmaz)
          const aOpt = { ses: this.ttsSes, olcum: this.olcum };
          await tts.seslendirAkis(c, (parca) => {
            if (this.kapali || (this._kesmeNo || 0) !== kesme) return;
            this.rtp.oynat(parca);
            if (!ilkSes) { ilkSes = true; this._gecikmeOlcumu(c); }
          }, aOpt);
          this._ttsKaydet(c, aOpt.kaynak || 'yeni', ilkSes);
          if (ilkSes) this._soyledi(c);
          if (this.kapali || (this._kesmeNo || 0) !== kesme) break;
          continue;
        }
        const { ses, kaynak } = await this._tts(c);
        if (this.kapali || (this._kesmeNo || 0) !== kesme) { this._ttsKaydet(c, kaynak, false); break; } // TTS beklerken kesildiyse calma
        if (ses && ses.length) { this.rtp.oynat(ses); this._ttsKaydet(c, kaynak, true); this._soyledi(c); }
        this._gecikmeOlcumu(c);
      }
    } finally {
      this._seslendiriyor = false;
    }
  }

  // Calinan son cumleler (yanki filtresi icin)
  _soyledi(c) {
    this._soylenen = (this._soylenen || []).concat({ c, t: Date.now() }).slice(-4);
  }

  // Musteri metni, asistanin sesi biter bitmez (ya da calarken) baslamis ve az once soylenen cumleyi tekrar ediyorsa yanki
  _yankiMi(metin) {
    const bas = (this._ifadeBasT || 0) > ((this._sonIslenen && this._sonIslenen.t) || 0) ? this._ifadeBasT : Date.now();
    if (!this._aiSesSon || bas > this._aiSesSon + 2000) return false;
    return (this._soylenen || []).some((s) => Date.now() - s.t < 20000 && yankiBenzer(metin, s.c));
  }

  // GECIKME DOKUMU: musterinin sesi kesildi -> asistanin ilk sesi (bitis bekleme + beyin + seslendirme)
  _gecikmeOlcumu(c) {
    if (!this._olcum) return;
    const o = this._olcum; this._olcum = null;
    const simdi = Date.now(), sesSon = Math.min(this._vadSon || this._turBas, this._turBas);
    log.info(`  (GECIKME musteri sustu -> asistan sesi ${simdi - sesSon} ms = bitis ${this._turBas - sesSon}`
      + ` + beyin ${o.beyinHazir - this._turBas} + ses ${simdi - o.beyinHazir} [${c.length} harf])`);
  }

  _cumlelereBol(metin) {
    const t = (metin || '').trim();
    if (!t) return [];
    // Cumle sonlarindan bol (. ! ?), noktalamayi koru; cok kisa parcalari birlestir.
    // Rakam arasindaki nokta (1.200, 15.30) cumle sonu DEGIL: "1." ayri okununca "birinci" deniyordu (kayit 281)
    const parcalar = t.match(/(?:[^.!?]|\.(?=\d))+[.!?]*/g) || [t];
    const out = [];
    let tampon = '';
    for (const p of parcalar) {
      tampon += p;
      if (tampon.trim().length >= 20 || /[.!?]\s*$/.test(tampon)) { out.push(tampon.trim()); tampon = ''; }
    }
    if (tampon.trim()) out.push(tampon.trim());
    // UZUN ILK CUMLE: Google cumlenin TAMAMINI uretmeden calmaz; 9 sn'lik ilk cumle "Anladım."dan sonra 1-1,5 sn
    // sessizlik birakiyordu (kayit 172). Ilk virgulde bol: kisa parca hemen calar, kalani o calarken uretilir.
    if (out.length && out[0].length > 60) {
      const v = out[0].indexOf(', ', 12);
      if (v > 0 && v < 60) out.splice(0, 1, out[0].slice(0, v + 1), out[0].slice(v + 2));
    }
    return out.length ? out : [t];
  }

  _sessizlikSifirla(ikinci) {
    clearTimeout(this.sessizlikZ);
    if (this.kapali || this._kapaniyor || this._aktarmada) return;
    // Sessizlik AI SUSTUKTAN sonra sayilir: AI konusurken / beyin dusunurken / musterinin bitmemis ara-sonucu
    // varken sayac sifirlanir. Eskiden pencere tur donunce basliyordu; 12 sn'lik onay cumlesinden sonra musteriye
    // 3 sn kaliyor, "yok teşekkürler" derken "Orada mısınız?" calıyordu (2026-11-17 kayit).
    let sessiz = 0;
    const tik = async () => {
      if (this.kapali || this._kapaniyor || this._aktarmada) return;
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
    // Kapanis cumlesi TAM calinip bitene kadar bekle (yarida kesme), sonra hatti kapat. Max cfg.kapanisMaxMs
    // (7 sn; 18 sn idi: veda sonrasi hat uzun sure acik kaliyordu, 2026-11-18 isletme istegi).
    let gecen = 700;
    const tik = () => {
      if (this.kapali) return;
      gecen += 300;
      if (this.rtp && this.rtp.sesVarMi && gecen < cfg.kapanisMaxMs) { this._kapatZ = setTimeout(tik, 300); return; }
      this._kapatZ = setTimeout(() => {
        this.onBitir(this.kanalId); // son kareler de gitsin
        // Guvenlik: 3 sn sonra hat hala acik (oturum kapanmadi) ise bir kez daha kapat
        this._kapatZ = setTimeout(() => {
          if (this.kapali) return;
          log.warn('kapanis: hat 3 sn sonra hala acik -> tekrar kapatiliyor');
          this.onBitir(this.kanalId);
        }, 3000);
      }, 600);
    };
    log.info(`kapanis: veda sesi bitince hat kapanacak (en gec ${cfg.kapanisMaxMs} ms)`);
    // once seslendirmenin kuyruga girmesine firsat ver, sonra beklemeye basla
    this._kapatZ = setTimeout(tik, 700);
  }

  // ---- AKTARMA BEKLEMESI: oturum kapanmaz, beklemeye alinir. Yetkili acarsa index.js kapatir; acmazsa
  // aktarmaDon ile asistan geri gelir ("not aldım, sizi arayacağız") ve konusma surer.
  aktarmaBekle() {
    if (this.kapali) return;
    this._aktarmada = true;
    this._bekleyen = null;
    clearTimeout(this.sessizlikZ);
    clearTimeout(this._interimZ);
    this._sonInterim = '';
    if (this.rtp) this.rtp.ambiyansKapali = true;
  }

  /**
   * Calan/kuyruktaki asistan sesi BITENE kadar bekle (en fazla maxMs). Aktarmada "sizi yetkiliye bağlıyorum"
   * anonsu bitmeden zil + yetkili aramasi baslamasin diye (2026-11-19: ikisi ayni anda duyuluyordu).
   */
  async sesBitsin(maxMs = 15000) {
    const son = Date.now() + maxMs;
    while (!this.kapali && Date.now() < son && (this._seslendiriyor || (this.rtp && this.rtp.sesVarMi))) {
      await new Promise((r) => setTimeout(r, 100));
    }
    if (!this.kapali) await new Promise((r) => setTimeout(r, 300)); // son kareler hattan ciksin
  }

  /** Beklerken tek cumlelik anons (zil durdurulmus olmali); ses bitince doner. */
  async aktarmaAnons(metin) {
    if (this.kapali) return;
    await this._seslendir(metin);
    for (let i = 0; i < 100 && !this.kapali && this.rtp && this.rtp.sesVarMi; i++) await new Promise((r) => setTimeout(r, 100));
  }

  /** Yetkili acmadi: asistan geri doner, cumleyi soyler ve musteriyi yeniden dinler. */
  async aktarmaDon(cevap) {
    if (this.kapali) return;
    this._aktarmada = false;
    if (this.rtp) this.rtp.ambiyansKapali = false;
    this._sonIslenen = null;
    this._sonIsMetni = null;
    log.info(`< asistan (yetkili acmadi): ${cevap}`);
    await this._seslendir(cevap);
    this._sessizlikSifirla();
  }

  async kapat(sonuc) {
    if (this.kapali) return;
    this.kapali = true;
    clearTimeout(this.sessizlikZ);
    clearTimeout(this._kapatZ);
    clearTimeout(this._interimZ);
    this.stt.kapat();
    this.rtp.kapat();
    if (this.oturumId) {
      const sttSn = this.stt.ucretliSaniye();
      log.info(`Oturum #${this.oturumId} maliyet olcumu: STT ${sttSn} sn, TTS calinan ${this.olcum.soylenen} karakter`
        + ` (onbellek ${this.olcum.onbellek}, yeni uretim ${this.olcum.ttsKarakter}) ses ${this.ttsSes || cfg.tts.voice}`
        + (this.olcum.elKarakter ? `, ElevenLabs ${this.olcum.elKarakter} karakter` : ''));
      await brain.bitir(this.oturumId, sonuc || 'kapandi', {
        stt_sn: sttSn,
        tts_ucretli_karakter: this.olcum.ttsKarakter,
        tts_onbellek_karakter: this.olcum.onbellek,
        tts_soylenen_karakter: this.olcum.soylenen,
        tts_detay: this.ttsDetay,
        tts_ses: this.ttsSes || cfg.tts.voice,
        kayit_durum: this.kayitDurum || undefined, // ses kaydinin akibeti (index.js)
      });
    }
    log.info(`Oturum #${this.oturumId || '-'} kapandi (kanal ${this.kanalId})`);
  }
}

module.exports = { CagriOturumu };
