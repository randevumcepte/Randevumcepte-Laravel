'use strict';
// KRITIK: Bazi sunucularda IPv6 ADRESI tanimli ama YOLU kirik. Google (STT gRPC + TTS REST)
// hostname'i once AAAA (IPv6) cozup ona baglanmaya calisir -> 'EHOSTUNREACH 2001:4860:...'
// -> ses Google'a gider ama cevap GELMEZ (STT sessiz) ve TTS hata verir (AI sessiz kalir).
// Cozum: tum DNS cozumlemesinde IPv4'u ONCE dene. (Node 18+; dns.lookup kullanan axios dahil.)
require('dns').setDefaultResultOrder('ipv4first');

// ResteOS AI Santral kopru — giris.
// Asterisk (ARI, Asterisk 16/17) -> externalMedia (RTP) <-> bu kopru <-> Google STT/TTS + Laravel beyin.
//
// Akis:
//  1) Dialplan (TEK satir, restoran basina bir kez): exten => s,1,Stasis(restaurant-santral-ai)
//  2) StasisStart -> kanali cevapla, mixing bridge kur
//  3) externalMedia kanali olustur (RTP'yi bu koprunun portuna yollar), bridge'e ekle
//  4) CagriOturumu: RTP<->STT<->beyin<->TTS
//  5) aksiyon 'aktar' -> hedef(ler)i PANELDEN oku, ARI originate ile ARA (hepsi/sirali), ayni bridge'de BIRLESTIR
//     (dialplan'e GEREK YOK -> her restoranda ekstra Asterisk ayari yok = otomasyon)
//  6) hangup -> temizle (tum bacaklar)
const ariClient = require('ari-client');
const cfg = require('./config');
const log = require('./log');
const tts = require('./tts');
const brain = require('./brain');
const { CagriOturumu } = require('./session');

let client = null;

// --- RTP port havuzu (cift portlar; RTP gelenegi) ---
const kullanilan = new Set();
function portAl() {
  for (let i = 0; i < cfg.rtp.portCount; i++) {
    const p = cfg.rtp.portBase + i * 2;
    if (!kullanilan.has(p)) { kullanilan.add(p); return p; }
  }
  return null;
}
function portBirak(p) { if (p) kullanilan.delete(p); }

// --- SES KAYDI (ARI bridge recording) — hata olsa cagri bozulmaz ---
async function sesKayitBasla(kayit, channelId, tur) {
  if (!cfg.recording.aktif || !kayit || !kayit.bridge) return;
  try {
    const safe = String(channelId).replace(/[^a-zA-Z0-9]/g, '_');
    const ad = `santral_${safe}_${tur}`;
    await kayit.bridge.record({ name: ad, format: 'wav', ifExists: 'overwrite', beep: false, terminateOn: 'none', maxDurationSeconds: 3600, maxSilenceSeconds: 0 });
    kayit.rec = { ad, tur };
    log.info(`ses kaydi basladi (${tur}): ${ad}`);
  } catch (e) { log.warn('ses kaydi baslamadi:', e.message); }
}

async function sesKayitBitir(kayit) {
  if (!cfg.recording.aktif) return;
  if (!kayit || !kayit.rec) { log.debug('sesKayitBitir: aktif kayit yok'); return; }
  const { ad, tur } = kayit.rec;
  kayit.rec = null;
  log.info(`ses kaydi bitiriliyor (${tur}): ${ad}`);
  // recordings.stop HANG'a karsi 3sn timeout (bazi durumlarda cozulmeyebilir -> yuklemeyi bloklamasin)
  try {
    await Promise.race([
      client.recordings.stop({ recordingName: ad }),
      new Promise((_, rej) => setTimeout(() => rej(new Error('stop timeout')), 3000)),
    ]);
    log.debug('kayit durduruldu');
  } catch (e) { log.debug('kayit stop atlandi: ' + e.message); }
  await new Promise((r) => setTimeout(r, 1000)); // dosya finalize olsun
  const filePath = `${cfg.recording.dir}/${ad}.wav`;
  const oturumId = kayit.oturum ? kayit.oturum.oturumId : null;
  await brain.sesYukle(oturumId, kayit.subeId, tur, filePath);
}

// callerKanalId -> {oturum,bridge,extChan,port,telefon,subeId,aktarmaModu,hedefAdaylar:Set,hedefBagli,hedefChanId,aktarSira}
const aktif = new Map();
const extMediaKanallari = new Set();
const yerelYari = new Map();        // aktarma kuyruk bacaginin (Local ;2) id'si -> {callerId, hedefId}
const ARA_ANONS = 'Yetkili arkadaşımıza ulaşmaya çalışıyorum, lütfen hatta kalın.';
const geriBasarili = new Set();     // callee acip oturum baslayan geri-arama id'leri (cevapsiz yanlis raporlanmasin)
let _geriDongudeMi = false;

async function main() {
  if (!cfg.laravel.baseUrl) { log.error('LARAVEL_BASE_URL bos — .env ayarlayin'); process.exit(1); }

  const sttKey = process.env.GOOGLE_APPLICATION_CREDENTIALS;
  if (!sttKey) log.warn('GOOGLE_APPLICATION_CREDENTIALS bos — STT (kulak) calismaz, musteri duyulmaz');
  else if (!require('fs').existsSync(sttKey)) log.warn(`STT kimlik dosyasi YOK: ${sttKey} — STT calismaz`);
  if (!cfg.tts.apiKey) log.warn('GOOGLE_TTS_API_KEY bos — TTS (agiz) calismaz, AI sessiz kalir');
  log.info(`SURUM: randevu-fork-1 (restaurant 2026-10-06e tabanli) (terk edilen cagri kurtarma/otomatik geri arama: ${cfg.geriArama.aktif ? 'ACIK' : 'KAPALI'}; ARI keepalive; ambiyans ducking; ofis ambiyansi: ${cfg.ambiyans.aktif ? 'ACIK sev=' + cfg.ambiyans.seviye + ' dinleme=' + cfg.ambiyans.dinleme : 'KAPALI'}; StasisEnd temizle; bargeIn=${cfg.bargeIn ? 'ACIK' : 'KAPALI(!)'} kayit=${cfg.recording.aktif ? 'ACIK' : 'KAPALI'})`);
  log.info(`Ayar: format=${cfg.mediaFormat} bargeIn=${cfg.bargeIn ? 'acik(tam-dupleks)' : 'kapali(yari-dupleks)'} model=${cfg.stt.model} sube=${cfg.laravel.defaultSubeId}`);

  log.info(`ARI baglantisi: ${cfg.ari.url} (app=${cfg.ari.app})`);
  client = await ariClient.connect(cfg.ari.url, cfg.ari.user, cfg.ari.pass);
  ariHandlerKur(client);

  process.on('SIGINT', () => { log.info('kapaniyor…'); process.exit(0); });
  process.on('SIGTERM', () => process.exit(0));

  await client.start(cfg.ari.app);
  log.info('AI Santral kopru hazir. Cagri bekleniyor.');
  // SIK KALIP CUMLELER: acilista bir kez seslendirilip onbellege alinir -> cagrida beklemeden calar
  // (cumle bolme ile birebir ayni metin olmali). Maliyet: acilis basina ~300 karakter.
  (async () => {
    for (const c of ['Başka yardımcı olabileceğim bir konu var mı?', 'Hangisini tercih edersiniz?', 'Hemen bakıyorum.',
      'Tamam, kontrol ediyorum.', 'Randevunuz onaylandığında size bilgi verilecektir.', 'Lütfen hatta kalın.', ARA_ANONS]) {
      // Isletmelerin secebildigi her ses icin (disk onbellegi kalici -> ses basina tek seferlik ~300 karakter)
      for (const ses of cfg.tts.sesler) { try { await tts.seslendir(c, { ses }); } catch (_) {} }
    }
    log.info('kalip cumleler onbellege alindi');
  })();
  await sahipsizSesKanallariniKapat(true);

  // BAGLANTIYI SICAK TUT + koptuysa yeniden baglan: idle'da WS bayatlayip ILK CAGRIYI
  // dusurmesini onler ("ilk arama kapaniyor, ikinci aciliyor" sorunu). 25sn'de bir saglik kontrolu.
  setInterval(ariSaglikKontrol, 25000);

  // TERK EDILEN CAGRI KURTARMA: kuyrugu periyodik yokla -> musteriyi otomatik geri ara.
  if (cfg.geriArama.aktif && cfg.geriArama.dial) {
    setInterval(geriAramaDongu, Math.max(10, cfg.geriArama.pollSn) * 1000);
    log.info(`Geri arama AKTIF (dial=${cfg.geriArama.dial}, poll=${cfg.geriArama.pollSn}sn, timeout=${cfg.geriArama.timeout}sn)`);
  } else {
    log.info('Geri arama KAPALI (GERI_ARAMA=1 + GERI_ARAMA_DIAL ile acilir).');
  }
}

// StasisStart/End + ChannelDestroyed handlerlarini bir client'a bagla (yeniden baglanmada tekrar kullanilir).
function ariHandlerKur(c) {
  c.on('StasisStart', async (event, channel) => {
    if (extMediaKanallari.has(channel.id) || /^UnicastRTP/.test(channel.name || '')) {
      log.debug(`externalMedia bacagi StasisStart, atlaniyor: ${channel.name}`);
      return;
    }
    if (event.args && event.args[0] === 'aktarma') {
      await aktarmaHedefiCevapladi(channel, event.args[1]);
      return;
    }
    // HATIRLATMA ARAMASI (Laravel cron -> AMI Originate -> dialplan exten 8/9 -> Stasis):
    // args: ['hatirlatma', 'randevu:123' | 'alacak:4-5', 'SUBE=20']. Musteri ACTI; konusmayi asistan yurutur.
    if (event.args && event.args[0] === 'hatirlatma') {
      await cagriBasla(channel, event, { hatirlatma: event.args[1] || '' });
      return;
    }
    // GERI ARAMA: disari aradigimiz musteri ACTI -> yarida kalan oturumla devam.
    if (event.args && event.args[0] === 'geri_arama') {
      const geriId = parseInt(event.args[1], 10) || 0;
      const geriAramaOturum = parseInt(event.args[2], 10) || 0;
      const num = event.args[3] || null;
      await cagriBasla(channel, event, { geriAramaId: geriId, geriAramaOturum, telefon: num });
      return;
    }
    await cagriBasla(channel, event);
  });
  c.on('ChannelDestroyed', async (event, channel) => { await kanalDustu(channel.id); });
  // Aktarma: kuyrukta (Local ;2) bir yetkili gercekten acti -> kuyruk bacagi bridge'e girer / Dial ANSWER
  c.on('ChannelEnteredBridge', (event) => { const id = event.channel && event.channel.id; if (id && yerelYari.has(id)) insanCevapladi(id, 'bridge'); });
  c.on('Dial', (event) => { if (event.dialstatus === 'ANSWER' && event.caller && yerelYari.has(event.caller.id)) insanCevapladi(event.caller.id, 'dial'); });
  // Kanal Stasis'ten cikinca (hangup) StasisEnd gelir; ChannelDestroyed ULASMAYABILIR -> ikisini de dinle.
  c.on('StasisEnd', async (event, channel) => { log.debug(`StasisEnd: ${channel.id}`); await kanalDustu(channel.id); });
  c.on('WebSocketError', (err) => log.warn('ARI WS hata:', err && err.message));
  // WS kapandi ve kendi denemeleri bitti -> saglik kontrolunu hemen calistir (25 sn bekleme).
  c.on('WebSocketMaxRetries', () => { log.warn('ARI WS yeniden deneme bitti, yeniden baglaniliyor'); ariSaglikKontrol(); });
}

// SAHIPSIZ SES BACAGI SUPURME: bu koprunun RTP port araligina giden UnicastRTP kanallarindan aktif bir
// cagriya ait olmayanlari kapatir (eski surumden kalan / beklenmedik kopmada asili kalan bacaklar).
// Baslangicta hepsi sahipsizdir; calisirken yalniz 60 sn'den eski olanlar (kurulmakta olan cagriya dokunmaz).
async function sahipsizSesKanallariniKapat(baslangic) {
  try {
    const sahipli = new Set();
    for (const k of aktif.values()) if (k.extChan) sahipli.add(k.extChan.id);
    const ust = cfg.rtp.portBase + cfg.rtp.portCount * 2;
    const kanallar = await client.channels.list();
    let n = 0;
    for (const ch of kanallar) {
      const m = /^UnicastRTP\/([^:]+):(\d+)-/.exec(ch.name || '');
      if (!m || m[1] !== cfg.rtp.host) continue;
      const port = parseInt(m[2], 10);
      if (port < cfg.rtp.portBase || port >= ust) continue;
      if (sahipli.has(ch.id)) continue;
      if (!baslangic) {
        const yas = Date.now() - Date.parse(ch.creationtime || '');
        if (!(yas > 60000)) continue;
      }
      try { await client.Channel(ch.id).hangup(); n++; } catch (_) {}
      extMediaKanallari.delete(ch.id);
    }
    if (n) log.warn(`sahipsiz ses bacagi kapatildi: ${n} UnicastRTP kanali`);
  } catch (e) {
    log.debug('sahipsiz kanal supurme hatasi: ' + (e && e.message));
  }
}

let _ariYenileniyor = false;
async function ariSaglikKontrol() {
  try {
    await client.asterisk.getInfo(); // hafif ping: baglanti canli mi + WS'i sicak tutar
    // Asterisk yeniden baslatilinca (fwconsole stop/start) REST tekrar cevap verir ama WS olmustur
    // ve Stasis app kayitsiz kalir ("Stasis app ... not registered" -> cagrilar eski IVR'a duser).
    // App kayitli degilse applications.get 404 atar -> asagida yeniden baglanilir.
    await client.applications.get({ applicationName: cfg.ari.app });
    await sahipsizSesKanallariniKapat(false);
  } catch (e) {
    if (_ariYenileniyor) return;
    _ariYenileniyor = true;
    log.warn('ARI baglantisi kopuk olabilir, yeniden baglaniliyor:', e && e.message);
    try {
      try { client.stop(); } catch (_) {} // eski (olu) WS'i kapat
      client = await ariClient.connect(cfg.ari.url, cfg.ari.user, cfg.ari.pass);
      ariHandlerKur(client);
      await client.start(cfg.ari.app);
      log.info('ARI yeniden baglandi (kopru tekrar hazir).');
    } catch (e2) {
      log.error('ARI yeniden baglanamadi:', e2 && e2.message);
    }
    _ariYenileniyor = false;
  }
}

// ============================ GERI ARAMA (terk edilen cagri kurtarma) ============================
function geriNumFormat(tel) {
  const d = String(tel || '').replace(/\D/g, '');
  const son10 = d.slice(-10);
  return (cfg.geriArama.prefix || '') + son10;
}
async function geriAramaDongu() {
  if (!cfg.geriArama.aktif || !cfg.geriArama.dial) return;
  if (_geriDongudeMi || _ariYenileniyor) return;
  _geriDongudeMi = true;
  try {
    const r = await brain.geriAramaBekleyen();
    for (const row of ((r && r.liste) || [])) { await originateGeriArama(row); }
  } catch (e) { log.debug('geri arama dongu hatasi:', e.message); }
  _geriDongudeMi = false;
}
async function originateGeriArama(row) {
  const num = geriNumFormat(row.telefon);
  if (!num || num.length < 6) { await brain.geriAramaDurum(row.id, 'cevapsiz'); return; }
  const dial = cfg.geriArama.dial.replace('{num}', num);
  await brain.geriAramaDurum(row.id, 'araniyor');
  log.info(`GERI ARAMA baslatiliyor: ${row.telefon} -> ${dial} (id=${row.id}, oturum=${row.oturum_id})`);
  try {
    const ch = client.Channel();
    await ch.originate({
      endpoint: dial,
      app: cfg.ari.app,
      appArgs: `geri_arama,${row.id},${row.oturum_id},${num}`,
      callerId: cfg.geriArama.callerId || num,
      timeout: cfg.geriArama.timeout,
    });
  } catch (e) {
    log.warn('geri arama originate hatasi:', e.message);
    await brain.geriAramaDurum(row.id, 'cevapsiz');
    return;
  }
  // CEVAPSIZ yakalama: timeout+10sn icinde "basarili" raporlanmadiysa cevapsiz say.
  setTimeout(async () => {
    if (!geriBasarili.has(row.id)) { await brain.geriAramaDurum(row.id, 'cevapsiz'); }
    else { geriBasarili.delete(row.id); }
  }, (cfg.geriArama.timeout + 10) * 1000);
}

async function cagriBasla(channel, event, opts = {}) {
  const telefon = opts.telefon || (channel.caller && channel.caller.number ? channel.caller.number : null);
  let subeId = opts.subeId || cfg.laravel.defaultSubeId;
  const arg = (event.args || []).find((a) => /^SUBE=/i.test(a));
  if (arg) subeId = parseInt(arg.split('=')[1], 10) || subeId;

  const port = portAl();
  if (port === null) {
    log.error('Bos RTP portu yok — cagri reddediliyor');
    try { await channel.hangup(); } catch (_) {}
    return;
  }

  log.info(`${opts.hatirlatma ? 'HATIRLATMA ARAMASI (' + opts.hatirlatma + ')' : opts.geriAramaId ? 'GERI ARAMA (musteriye)' : 'Yeni cagri'}: kanal=${channel.id} tel=${telefon || '-'} sube=${subeId} rtpPort=${port}`);

  let adim = 'answer';
  let yarimBridge = null, yarimExt = null; // kurulum yarida kalirsa temizlenecekler
  try {
    if (aktif.has(channel.id)) { log.warn('cift StasisStart, atlandi: ' + channel.id); portBirak(port); return; }
    // HIZLI KARSILAMA: Laravel baslat + karsilama seslendirmesi hat kurulumuyla AYNI ANDA baslar. Eskiden hepsi
    // sirayla (answer -> bridge -> externalMedia -> kayit -> baslat -> TTS) yapiliyordu: telefon acildiktan sonra
    // ~5 sn yalniz ofis sesi geliyordu (2026-11-19 isletme sikayeti).
    const t0 = Date.now();
    const baslatP = brain.baslat(subeId, telefon, 'santral', opts.geriAramaOturum || 0, opts.hatirlatma || '').then((d) => {
      log.info(`  (baslat ${Date.now() - t0} ms)`);
      const onKaynak = {};
      if (d && d.karsilama) {
        // karsilama parcalarini simdiden seslendir (oturum ayni parcalari istediginde hazir/uretilmekte olur)
        for (const c of CagriOturumu.prototype._cumlelereBol(d.karsilama)) {
          const opt = { ses: d.tts_ses };
          onKaynak[c] = tts.seslendir(c, opt).then(() => opt.kaynak, () => null);
        }
      }
      return { d, onKaynak };
    }, (e) => ({ e }));
    await channel.answer();

    adim = 'bridge.create';
    const bridge = client.Bridge();
    await bridge.create({ type: 'mixing' });
    yarimBridge = bridge;

    // ONEMLI: Arayani HEMEN ekle (o kesin Stasis'te). externalMedia (UnicastRTP) bacagi
    // Stasis'e ASENKRON girer; hemen addChannel yaparsak "Channel not in Stasis application"
    // yarisi olusur -> cagri 1sn'de duser. O yuzden externalMedia bacagini AYRI + retry'li ekle.
    adim = 'addChannel(arayan)';
    await bridge.addChannel({ channel: channel.id });

    adim = 'externalMedia';
    const extChan = client.Channel();
    await extChan.externalMedia({
      app: cfg.ari.app,
      external_host: `${cfg.rtp.host}:${port}`,
      format: cfg.mediaFormat === 'slin16' ? 'slin16' : 'ulaw',
    });
    extMediaKanallari.add(extChan.id);
    yarimExt = extChan;

    adim = 'addChannel(externalMedia)';
    await bridgeKanalEkleRetry(bridge, extChan.id);

    const oturum = new CagriOturumu({
      kanalId: channel.id,
      telefon,
      subeId,
      rtpPort: port,
      geriAramaOturum: opts.geriAramaOturum || 0, // >0: yarida kalan oturumla devam
      hatirlatma: opts.hatirlatma || '',          // giden hatirlatma aramasi hedefi (randevu:ID | alacak:ID-ID)
      baslatP,                                     // hat kurulurken baslamis Laravel baslat (+ karsilama TTS)
      onAktar: (kid) => insanaAktar(kid, subeId),
      // Veda sonrasi kapatma. Hata ESKIDEN sessizce yutuluyordu: hat acik kaliyor, temizle hic calismadigi
      // icin ses kaydi da yuklenmiyordu (canli 2026-11-18 22:59). Simdi loglanir, 1 sn sonra tekrar denenir
      // ve temizle (kayit yukleme) her durumda calisir.
      onBitir: async (kid) => {
        log.info(`kapanis: hat kapatiliyor (${kid})`);
        try {
          await client.Channel(kid).hangup();
        } catch (e) {
          log.warn(`kapanis: hangup hatasi (${e.message}) -> 1 sn sonra tekrar, temizle calisiyor`);
          setTimeout(() => { client.Channel(kid).hangup().catch((e2) => log.error(`kapanis: hangup 2. deneme de basarisiz: ${e2.message}`)); }, 1000);
          if (aktif.has(kid)) await temizle(kid, 'veda');
        }
      },
    });

    const kayit = {
      oturum, bridge, extChan, port, telefon, subeId,
      aktarmaModu: false, hedefAdaylar: null, adayBilgi: null, hedefBagli: false, hedefChanId: null, aktarSira: null,
      rec: null,
    };
    aktif.set(channel.id, kayit);
    yarimBridge = null; yarimExt = null; // artik temizle() sorumlu
    // SES KAYDI (AI fazi): bridge'i kaydet (musteri + AI sesi). Hata olsa cagri bozulmaz.
    await sesKayitBasla(kayit, channel.id, 'ai');
    await oturum.basla();
    // GERI ARAMA basarili: callee acti + oturum basladi -> kuyruga bildir.
    if (opts.geriAramaId) { geriBasarili.add(opts.geriAramaId); brain.geriAramaDurum(opts.geriAramaId, 'basarili'); }
  } catch (e) {
    log.error(`cagriBasla hatasi [adim=${adim}]:`, e.message);
    if (yarimExt) { try { await yarimExt.hangup(); } catch (_) {} extMediaKanallari.delete(yarimExt.id); }
    if (yarimBridge) { try { await yarimBridge.destroy(); } catch (_) {} }
    portBirak(port);
    try { await channel.hangup(); } catch (_) {}
  }
}

// externalMedia (UnicastRTP) bacagi Stasis'e asenkron girer; hemen addChannel yaparsak
// "Channel not in Stasis application" olur -> cagri duser. Kanal Stasis'e girene kadar kisa retry.
async function bridgeKanalEkleRetry(bridge, chanId, deneme = 10, bekleMs = 100) {
  let sonHata = null;
  for (let i = 0; i < deneme; i++) {
    try { await bridge.addChannel({ channel: chanId }); if (i > 0) log.debug(`addChannel ${i + 1}. denemede oldu`); return; }
    catch (e) {
      sonHata = e;
      if (!/not in Stasis/i.test(e.message || '')) throw e; // baska hata -> hemen firlat
      await new Promise((r) => setTimeout(r, bekleMs));
    }
  }
  throw sonHata || new Error('bridge.addChannel basarisiz (retry bitti)');
}

// "insana aktar" (dialplan'SIZ, coklu hedef + strateji)
// YETKILI ACMAZSA: asistan oturumu KAPANMAZ, beklemeye alinir (zil calar, cfg.aktarmaAraAnonsMs'de bir kez
// "ulaşmaya çalışıyorum" der). cfg.aktarmaBekleMs icinde gercek bir kisi acmazsa adaylar kapatilir, asistan geri
// doner: "not aldım, sizi arayacağız" + konuyu sorar (Laravel /aktarma-cevapsiz isaretler + isletmeye bildirir).
// Eskiden kuyruk (Local/...@from-queue) cagriyi hemen "aciyordu": kopru yetkili acti sanip zil suresini birakiyor,
// musteri kimse acmayan hatta sonsuza kadar caliyordu (2026-11-19 isletme sikayeti).
async function insanaAktar(kanalId, subeId) {
  const kayit = aktif.get(kanalId);
  if (!kayit) return;
  // Oturum aktarmayi ayni turda birden fazla yerden tetikleyebiliyor. Ikinci
  // cagri kuyrugu IKINCI kez arar: iki bacak ayni bridge'e girer, birinin
  // calma sesi gorusme boyunca duyulur, temizlikte digeri unutulur. Tek sefer.
  if (kayit.aktarmaBasladi) {
    log.info(`insana aktar: kanal=${kanalId} zaten aktariliyor, tekrar yok sayildi`);
    return;
  }
  kayit.aktarmaBasladi = true;
  log.info(`insana aktar: kanal=${kanalId}`);
  // Asistan geri donebilir mi? (paket yok / baslat hatasi -> oturum yok: eski davranis, kimse acmazsa kapanir)
  const aiDonebilir = cfg.aktarmaBekleMs > 0 && !!(kayit.oturum && kayit.oturum.oturumId);
  kayit.aiDonebilir = aiDonebilir;
  if (aiDonebilir) kayit.oturum.aktarmaBekle();

  const h = await brain.aktarmaHedef(subeId);
  const hedefler = (h && Array.isArray(h.hedefler) && h.hedefler.length) ? h.hedefler : (h && h.dial ? [h.dial] : []);
  if (!h || !h.aktif || !hedefler.length) {
    log.warn(`aktarma hedefi panelde tanimli degil (sube ${subeId}). /santral-ayar'dan ayarlayin.`);
    if (aiDonebilir) { await aktarmaCevapsiz(kanalId, 'hedef_yok'); return; }
    try { await client.Channel(kanalId).hangup(); } catch (_) {}
    await temizle(kanalId, 'aktar');
    return;
  }

  kayit.aktarmaModu = true;
  // Asistan geri donemiyorsa AI fazi simdi kapanir (eski davranis); donebiliyorsa yetkili acinca kapanir
  if (!aiDonebilir) await aiFaziKapat(kayit);

  // Hedef acana kadar arayan sessizlik degil ZIL sesi duysun (cevap verilince durdurulur)
  await zilBasla(kayit);

  kayit.hedefAdaylar = new Set();
  kayit.adayBilgi = new Map();
  kayit.hedefBagli = false;
  kayit.hedefChanId = null;
  const zil = h.zil || 30;
  if (aiDonebilir) {
    kayit.araAnonsZ = setTimeout(() => araAnons(kanalId), cfg.aktarmaAraAnonsMs);
    kayit.aktarZ = setTimeout(() => aktarmaCevapsiz(kanalId, 'cevapsiz'), cfg.aktarmaBekleMs);
  }

  if (h.strateji === 'sirali') {
    kayit.aktarSira = { list: hedefler, idx: 0, zil };
    log.info(`aktarma (sirali): ${hedefler.join(' -> ')} (her biri ${zil}s)`);
    await siraliDenemesi(kanalId);
  } else {
    // Bekleme suresini kopru yonetir: hedef zili ondan once bitmesin
    const z = aiDonebilir ? Math.max(zil, Math.ceil(cfg.aktarmaBekleMs / 1000) + 5) : zil;
    log.info(`aktarma (hepsi ayni anda): ${hedefler.join(', ')} (zil ${z}s${aiDonebilir ? ', asistan ' + Math.round(cfg.aktarmaBekleMs / 1000) + ' sn sonra geri doner' : ''})`);
    for (const dial of hedefler) await originateHedef(kanalId, dial, z);
  }
}

async function zilBasla(kayit) {
  try {
    const pb = client.Playback();
    kayit.zilPb = pb;
    await kayit.bridge.play({ media: 'tone:ring', playbackId: pb.id });
  } catch (e) { log.debug('zil sesi calinamadi: ' + e.message); }
}

async function zilDur(kayit) {
  const pb = kayit.zilPb;
  kayit.zilPb = null;
  if (pb) { try { await pb.stop(); } catch (_) {} }
}

// AI fazini kapat: AI ses bacagi + ses kaydi (yuklenir) + oturum. Bridge + arayan KORUNUR (insan devralir).
async function aiFaziKapat(kayit) {
  if (kayit.aiKapandi) return;
  kayit.aiKapandi = true;
  if (kayit.extChan) {
    try { await kayit.extChan.hangup(); } catch (_) {}
    extMediaKanallari.delete(kayit.extChan.id);
    kayit.extChan = null;
  }
  await sesKayitBitir(kayit);
  try { if (kayit.oturum) await kayit.oturum.kapat('aktar'); } catch (_) {}
  portBirak(kayit.port); kayit.port = null;
}

// Beklerken bir kez: zil durur, asistan "ulaşmaya çalışıyorum" der, zil yeniden baslar
async function araAnons(callerId) {
  const kayit = aktif.get(callerId);
  if (!kayit || kayit.hedefBagli || !kayit.aktarmaModu || kayit.aiKapandi) return;
  await zilDur(kayit);
  log.info('aktarma: ara anons (yetkili henuz acmadi)');
  try { await kayit.oturum.aktarmaAnons(ARA_ANONS); } catch (_) {}
  if (aktif.get(callerId) === kayit && !kayit.hedefBagli && kayit.aktarmaModu && !kayit.aiKapandi) await zilBasla(kayit);
}

// Yetkili acmadi (sure doldu / herkes mesgul / hedef yok): adaylar kapatilir, asistan geri doner ve not alir
async function aktarmaCevapsiz(callerId, neden) {
  const kayit = aktif.get(callerId);
  if (!kayit || kayit.hedefBagli || kayit.aiKapandi || kayit.aiDonuyor) return;
  kayit.aiDonuyor = true;
  clearTimeout(kayit.aktarZ); clearTimeout(kayit.araAnonsZ);
  log.info(`aktarma: ${neden === 'hedef_yok' ? 'hedef tanimli degil' : 'yetkili acmadi'} -> asistan geri donuyor (${callerId})`);
  kayit.aktarmaModu = false;
  kayit.aktarSira = null;
  const adaylar = Array.from(kayit.hedefAdaylar || []);
  kayit.hedefAdaylar = new Set();
  for (const cid of adaylar) { try { await client.Channel(cid).hangup(); } catch (_) {} }
  if (kayit.adayBilgi) { for (const b of kayit.adayBilgi.values()) if (b.digerId) yerelYari.delete(b.digerId); kayit.adayBilgi = null; }
  await zilDur(kayit);
  const r = await brain.aktarmaCevapsiz(kayit.oturum.oturumId, neden);
  if (aktif.get(callerId) !== kayit) return;
  // Musteri yine aktarma isterse Laravel aktarma yerine not alir; kopru hatasi tekrar tetiklerse yeniden denenir
  kayit.aktarmaBasladi = false;
  kayit.aiDonuyor = false;
  await kayit.oturum.aktarmaDon((r && r.cevap) || 'Efendim, yetkili arkadaşlarımız şu an diğer misafirlerimizle ilgileniyor. Aradığınızı not aldım, en kısa sürede sizi geri arayacaklar. İsterseniz konuyu kısaca söyleyin, notuma ekleyeyim.');
}

// Tek bir hedefi ara (aday olarak izle)
async function originateHedef(callerId, dial, zil) {
  const kayit = aktif.get(callerId);
  if (!kayit || kayit.hedefBagli || !kayit.aktarmaModu) return;
  const hedefChan = client.Channel();
  // Kuyruk (Local/...) cagriyi hemen "acabilir" (kuyruk muzigi/zili): Local'in diger yarisi (;2) izlenir; kuyrukta
  // biri gercekten acinca o yari bir bridge'e girer / Dial ANSWER gelir -> o zaman baglanilir.
  const local = /^Local\//i.test(dial);
  const digerId = local ? hedefChan.id + '-q' : null;
  kayit.hedefAdaylar.add(hedefChan.id);
  if (kayit.adayBilgi) kayit.adayBilgi.set(hedefChan.id, { t0: Date.now(), local, digerId, insan: false, chan: null });
  if (digerId) yerelYari.set(digerId, { callerId, hedefId: hedefChan.id });
  try {
    await hedefChan.originate({
      endpoint: dial,                    // or. SIP/101 veya Local/<kuyruk>@from-queue/n (panelden)
      app: cfg.ari.app,
      appArgs: `aktarma,${callerId}`,
      timeout: zil,
      callerId: kayit.telefon || undefined,
      ...(digerId ? { otherChannelId: digerId } : {}),
    });
    if (digerId) {
      client.applications.subscribe({ applicationName: cfg.ari.app, eventSource: 'channel:' + digerId })
        .catch((e) => log.warn('kuyruk bacagi izlenemedi (' + e.message + ') -> kuyruk acisi oto-cevap suresine gore ayirt edilir'));
    }
  } catch (e) {
    log.warn(`originate hatasi (${dial}):`, e.message);
    kayit.hedefAdaylar.delete(hedefChan.id);
    if (digerId) yerelYari.delete(digerId);
    // aday olusmadi; sirali ise sonrakine gecmek icin kanalDustu tetiklenmez -> burada dene
    if (kayit.aktarSira) await siraliDenemesi(callerId);
    else if (kayit.aiDonebilir && kayit.hedefAdaylar.size === 0) await aktarmaCevapsiz(callerId, 'cevapsiz');
  }
}

// Sirali strateji: siradaki hedefi dene; liste biterse asistan geri doner (donemiyorsa arayan kapanir)
async function siraliDenemesi(callerId) {
  const kayit = aktif.get(callerId);
  if (!kayit || kayit.hedefBagli) return;
  const s = kayit.aktarSira;
  if (!s || s.idx >= s.list.length) {
    if (kayit.aiDonebilir) { await aktarmaCevapsiz(callerId, 'cevapsiz'); return; }
    log.info('sirali aktarma: kimse acmadi -> arayan kapaniyor');
    try { await client.Channel(callerId).hangup(); } catch (_) {}
    await temizle(callerId, 'aktar');
    return;
  }
  const dial = s.list[s.idx++];
  log.info(`sirali deneme ${s.idx}/${s.list.length}: ${dial} (${s.zil}s)`);
  await originateHedef(callerId, dial, s.zil);
}

// Hedef bacagi cevapladi (Stasis'e girdi). Kuyruk cok hizli "actiysa" gercek kisi beklenir.
async function aktarmaHedefiCevapladi(hedefChan, callerId) {
  const kayit = aktif.get(callerId);
  if (!kayit || !kayit.bridge) {
    log.warn('aktarma hedefi cevapladi ama arayan kaydi yok -> hedef kapaniyor');
    try { await hedefChan.hangup(); } catch (_) {}
    return;
  }
  // Baska hedef daha once acti / asistan geri dondu (gec kalan aday) -> fazlalik
  if (kayit.hedefBagli || !kayit.aktarmaModu || !(kayit.hedefAdaylar && kayit.hedefAdaylar.has(hedefChan.id))) {
    try { await hedefChan.hangup(); } catch (_) {}
    return;
  }
  const b = kayit.adayBilgi && kayit.adayBilgi.get(hedefChan.id);
  if (b) {
    b.chan = hedefChan;
    const sure = Date.now() - b.t0;
    if (b.local && !b.insan && kayit.aiDonebilir && sure < cfg.aktarmaOtoCevapMs) {
      log.info(`aktarma: kuyruk cagriyi ${sure} ms'de otomatik acti -> yetkilinin gercekten acmasi bekleniyor`);
      return;
    }
    log.info(`aktarma: hedef ${sure} ms'de acti${b.insan ? ' (kuyrukta yetkili acti)' : ''}`);
  }
  await baglan(kayit, hedefChan, callerId);
}

// Kuyruk bacaginin (Local ;2) gercek cevabi: kuyruk bir uyeyi bagladi
async function insanCevapladi(digerId, kaynak) {
  const y = yerelYari.get(digerId);
  if (!y) return;
  const kayit = aktif.get(y.callerId);
  if (!kayit || !kayit.adayBilgi || kayit.hedefBagli) return;
  const b = kayit.adayBilgi.get(y.hedefId);
  if (!b || b.insan) return;
  b.insan = true;
  log.info(`aktarma: kuyrukta yetkili acti (${kaynak}, ${Date.now() - b.t0} ms)`);
  if (b.chan) await baglan(kayit, b.chan, y.callerId);
}

// Arayanla birlestir; hepsi modunda digerlerini iptal et; AI fazini kapat
async function baglan(kayit, hedefChan, callerId) {
  if (kayit.hedefBagli) { try { await hedefChan.hangup(); } catch (_) {} return; }
  kayit.hedefBagli = true;
  kayit.hedefChanId = hedefChan.id;
  clearTimeout(kayit.aktarZ); clearTimeout(kayit.araAnonsZ);
  log.info(`aktarma hedefi cevapladi -> ${callerId} ile birlestiriliyor`);
  await zilDur(kayit);
  // AI sesi hemen kesilsin (ara anons caliyor olabilir); kayit/oturum kapanisi birlestirmeden sonra
  if (kayit.extChan) {
    try { await kayit.extChan.hangup(); } catch (_) {}
    extMediaKanallari.delete(kayit.extChan.id);
    kayit.extChan = null;
  }
  try { await kayit.bridge.addChannel({ channel: hedefChan.id }); }
  catch (e) { log.warn('bridge birlestirme hatasi:', e.message); }

  // Diger ringing adaylari iptal et (ring group kaybedenleri)
  for (const cid of Array.from(kayit.hedefAdaylar)) {
    if (cid !== hedefChan.id) { try { await client.Channel(cid).hangup(); } catch (_) {} }
  }
  kayit.hedefAdaylar = new Set([hedefChan.id]);

  await aiFaziKapat(kayit);
  // AKTARMA fazi ses kaydini baslat (musteri + yetkili)
  await sesKayitBasla(kayit, callerId, 'aktarma');
}

// Herhangi bir kanal yok oldu (cevapsiz/kapandi)
async function kanalDustu(chanId) {
  yerelYari.delete(chanId);
  // 1) Aktarma aday bacagi mi?
  for (const [cid, k] of aktif) {
    if (k.hedefAdaylar && k.hedefAdaylar.has(chanId)) {
      k.hedefAdaylar.delete(chanId);
      if (k.hedefBagli) {
        // Bagli olan hedef mi dustu? -> gorusme bitti, arayani kapat
        if (chanId === k.hedefChanId) {
          log.info(`aktarma hedefi (bagli) kapandi -> arayan ${cid} kapaniyor`);
          try { await client.Channel(cid).hangup(); } catch (_) {}
          await temizle(cid, 'aktar');
        }
        return; // bagli degilse: iptal edilen kaybeden aday -> yok say
      }
      // Henuz kimse baglanmadi (cevapsiz/mesgul)
      if (k.aktarSira) { await siraliDenemesi(cid); }        // sirali -> sonraki
      else if (k.hedefAdaylar.size === 0) {                   // hepsi -> hepsi dustu
        if (k.aiDonebilir) { await aktarmaCevapsiz(cid, 'cevapsiz'); return; }
        log.info(`aktarma: kimse acmadi -> arayan ${cid} kapaniyor`);
        try { await client.Channel(cid).hangup(); } catch (_) {}
        await temizle(cid, 'aktar');
      }
      return;
    }
  }
  // 2) Arayan mi kapandi?
  if (aktif.has(chanId)) await temizle(chanId, 'kapandi');
}

async function temizle(kanalId, sonuc) {
  const kayit = aktif.get(kanalId);
  if (!kayit) { log.debug(`temizle: kayit yok (${kanalId})`); return; }
  log.info(`temizle: kanal=${kanalId} sonuc=${sonuc} rec=${kayit.rec ? kayit.rec.ad : 'YOK'}`);
  aktif.delete(kanalId);
  clearTimeout(kayit.aktarZ); clearTimeout(kayit.araAnonsZ);
  if (kayit.adayBilgi) for (const b of kayit.adayBilgi.values()) if (b.digerId) yerelYari.delete(b.digerId);
  // Aktif ses kaydini kapat+yukle (bridge destroy'dan ONCE; ai veya aktarma fazi)
  await sesKayitBitir(kayit);
  const kapatilacak = new Set(kayit.hedefAdaylar || []);
  if (kayit.hedefChanId) kapatilacak.add(kayit.hedefChanId);
  for (const cid of kapatilacak) { try { await client.Channel(cid).hangup(); } catch (_) {} }
  try { if (kayit.oturum) await kayit.oturum.kapat(sonuc); } catch (_) {}
  // AI ses bacagi (externalMedia/UnicastRTP) da KAPATILMALI: eskiden yalniz bridge yok ediliyordu, bacak
  // Asterisk'te asili kaliyordu (2026-11-17 sunucuda 22 sahipsiz UnicastRTP kanali birikmisti).
  if (kayit.extChan) {
    try { await kayit.extChan.hangup(); } catch (_) {}
    extMediaKanallari.delete(kayit.extChan.id);
  }
  try { if (kayit.bridge) await kayit.bridge.destroy(); } catch (_) {}
  portBirak(kayit.port);
}

main().catch((e) => { log.error('KRITIK:', e.message); process.exit(1); });
