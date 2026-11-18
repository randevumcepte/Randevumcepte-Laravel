'use strict';
// Google Cloud TTS (REST + API anahtari — mevcut GOOGLE_TTS_API_KEY kullanilabilir).
// Cikti: telefon native ses (ulaw@8k) veya slin16@16k — RTP'ye dogrudan paketlenir.
const axios = require('axios');
const https = require('https');
const cfg = require('./config');
const log = require('./log');

// BAGLANTI KORUMA: eskiden her cumlede yeni TLS baglantisi aciliyordu (+100-300 ms). Tek ajan, acik soketler.
const ajan = new https.Agent({ keepAlive: true, keepAliveMsecs: 10000, maxSockets: 20 });

// ISINDIRMA: musteri konusmaya baslayinca TTS sunucusuna bos bir istek -> cevap hazir oldugunda TLS zaten acik.
// (Bekleme sirasinda sunucu bosta kalan baglantiyi kapatmis olabilir.) 8 sn'de en fazla bir kez.
let _sonIsitma = 0;
function isit() {
  const simdi = Date.now();
  if (simdi - _sonIsitma < 8000) return;
  _sonIsitma = simdi;
  const url = akisli() ? 'https://api.elevenlabs.io/' : 'https://texttospeech.googleapis.com/';
  axios.get(url, { httpsAgent: ajan, timeout: 4000, validateStatus: () => true }).catch(() => {});
}

// Basit metin -> ses onbellek (ayni cumle tekrar seslendirilmesin; ozellikle karsilama/sabit yanitlar)
const cache = new Map();
const CACHE_MAX = 200;

// DISK ONBELLEGI: bellek onbellegi her yeniden baslatmada (cron guncellemesi) siliniyordu -> kalip cumleler
// ("Başka yardımcı olabileceğim...") her seferinde yeniden ucretlendiriliyordu (ElevenLabs kredisi, 2026-11-17).
// Anahtar = saglayici + ses + model + ayarlar + metin; ses/ayar degisince eski dosya kullanilmaz.
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const DISK_DIZIN = process.env.TTS_ONBELLEK_DIZIN || path.resolve(__dirname, '..', 'tts-onbellek');
try { fs.mkdirSync(DISK_DIZIN, { recursive: true }); } catch (_) {}
// ses: Google ses adi (isletmeye gore Laravel baslat'tan gelir; yoksa TTS_VOICE). Anahtar sese gore ayrilir.
function diskAnahtar(t, ses) {
  const e = cfg.elevenlabs;
  const ayar = akisli()
    ? ['el', e.voiceId, e.model, e.stability, e.similarity, e.speed, e.dil, JSON.stringify(e.yazim), cfg.mediaFormat]
    : ['g', ses || cfg.tts.voice, cfg.tts.speakingRate, cfg.tts.profil, cfg.mediaFormat];
  return path.join(DISK_DIZIN, crypto.createHash('md5').update(JSON.stringify(ayar) + '|' + t).digest('hex') + '.ses');
}
function bellekAnahtar(t, ses) {
  return t + '|' + (akisli() ? 'el' : 'g:' + (ses || cfg.tts.voice));
}
function onbellekAl(t, ses) {
  const k = bellekAnahtar(t, ses);
  if (cache.has(k)) return cache.get(k);
  try {
    const b = fs.readFileSync(diskAnahtar(t, ses));
    if (b && b.length) { onbellekBellek(t, b, ses); return b; }
  } catch (_) {}
  return null;
}
function onbellekBellek(t, buf, ses) {
  if (cache.size >= CACHE_MAX) cache.delete(cache.keys().next().value);
  cache.set(bellekAnahtar(t, ses), buf);
}
function onbellekKoy(t, buf, ses) {
  if (!buf || !buf.length) return;
  onbellekBellek(t, buf, ses);
  fs.writeFile(diskAnahtar(t, ses), buf, () => {});
}

// MALIYET OLCUMU: Google ayni anda gelen ayni cumleyi iki kez ucretlendirmesin (tepki/dolgu on-seslendirme yarisi)
const _suren = new Map();
// Google karakter sayimi: bosluk/noktalama dahil her karakter (kod noktasi)
function karakterSay(t) { return Array.from(t).length; }

// Google TTS ulaw -> MULAW/8000, LINEAR16 -> LINEAR16/16000 (sampleRateHertz zorunlu degil ama netlik icin)
// effectsProfileId 'telephony-class-application': Google sesi telefon hattina (dar bant) gore optimize eder
// (8 kHz'de daha net/anlasilir). TTS_PROFIL= (bos) ile kapatilir.
function audioConfig() {
  const ac = cfg.mediaFormat === 'slin16'
    ? { audioEncoding: 'LINEAR16', sampleRateHertz: 16000, speakingRate: cfg.tts.speakingRate }
    : { audioEncoding: 'MULAW', sampleRateHertz: 8000, speakingRate: cfg.tts.speakingRate };
  if (cfg.tts.profil) ac.effectsProfileId = [cfg.tts.profil];
  return ac;
}

// LINEAR16 cikisinda 44 baytlik WAV header'i atlanmali (RTP ham PCM ister)
function stripWavHeader(buf) {
  if (buf.length > 44 && buf.slice(0, 4).toString('ascii') === 'RIFF') {
    // "data" chunk'ini bul
    const idx = buf.indexOf('data');
    if (idx > 0) return buf.slice(idx + 8);
    return buf.slice(44);
  }
  return buf;
}

// Ek ayiran kesme isareti seste duraklama/harf okumaya donuyor (kayit 106: "Gülşah Hanım'ı" -> "Gülşah Hanım, I").
// Yalniz harf'harf arasi kaldirilir ("Hanımı"); "15.20'de" gibi rakamli olanlar dogru okunur, dokunulmaz.
function okunur(metin) {
  return String(metin || '').replace(/(\p{L})['’](\p{L})/gu, '$1$2');
}

// ElevenLabs'e ozel YAZIM DUZELTME: Ingilizce kokenli ses bazi Turkce kelimeleri kaydiriyor ("başka" -> "beşka",
// "lazer" -> "leyzer"). ELEVENLABS_YAZIM="başka=bashka;lazer=lazır" -> kelime basinda tam eslesme, buyuk harf
// korunur, ekler kalir ("Başka" -> "Bashka", "lazerle" -> "lazırle"). Google'a giden metin degismez.
function elYazim(t) {
  const liste = cfg.elevenlabs.yazim;
  if (!liste.length) return t;
  let out = t;
  for (const [kaynak, hedef] of liste) {
    const re = new RegExp(`(^|[^\\p{L}])(${kaynak.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'giu');
    out = out.replace(re, (m, on, kelime) => {
      const buyuk = kelime[0] !== kelime[0].toLocaleLowerCase('tr');
      return on + (buyuk ? hedef[0].toLocaleUpperCase('tr') + hedef.slice(1) : hedef);
    });
  }
  return out;
}

// ElevenLabs: ciktiyi dogrudan telefon bicimindeki ham sesle ister (ulaw_8000 / pcm_16000; WAV basligi yok).
async function elevenlabs(t) {
  const e = cfg.elevenlabs;
  const bicim = cfg.mediaFormat === 'slin16' ? 'pcm_16000' : 'ulaw_8000';
  const url = `https://api.elevenlabs.io/v1/text-to-speech/${encodeURIComponent(e.voiceId)}?output_format=${bicim}`;
  const govde = {
    text: elYazim(t),
    model_id: e.model,
    voice_settings: { stability: e.stability, similarity_boost: e.similarity, speed: e.speed },
  };
  // Flash/Turbo v2.5 dil zorlamasini destekler (Turkce kelimeyi Ingilizce okumasin)
  if (e.dil && /v2_5|v3|v4/.test(e.model)) govde.language_code = e.dil; // Turkce zorla (ELEVENLABS_DIL=, bos = model kendi secer)
  const { data } = await axios.post(url, govde, {
    httpsAgent: ajan,
    timeout: 12000,
    responseType: 'arraybuffer',
    headers: { 'xi-api-key': e.apiKey, 'Content-Type': 'application/json', Accept: 'audio/basic' },
  });
  return Buffer.from(data);
}

// opt.ses: Google ses adi (isletmeye gore); opt.olcum: cagri sayaci {ttsKarakter, elKarakter} — yalniz GERCEKTEN
// saglayiciya giden (onbellekte olmayan) metin sayilir -> net maliyet.
// opt.kaynak (CIKTI): 'onbellek' (bellek/disk ya da ayni anda uretilen ayni cumle) | 'yeni' (saglayicidan uretildi, ucretli)
async function seslendir(metin, opt) {
  const t = okunur(metin).trim();
  if (!t) return Buffer.alloc(0);
  const ses = (opt && opt.ses) || cfg.tts.voice;
  const olcum = opt && opt.olcum;
  const onb = onbellekAl(t, ses);
  if (onb) { if (opt) opt.kaynak = 'onbellek'; return onb; }
  const anahtar = bellekAnahtar(t, ses);
  if (_suren.has(anahtar)) { if (opt) opt.kaynak = 'onbellek'; return _suren.get(anahtar); } // ayni cumle zaten uretiliyor -> ikinci ucret yok
  if (opt) opt.kaynak = 'yeni';
  const is = _seslendirGercek(t, ses, olcum);
  _suren.set(anahtar, is);
  try { return await is; } finally { _suren.delete(anahtar); }
}

async function _seslendirGercek(t, ses, olcum) {
  let elHata = false; // ElevenLabs basarisiz -> Google sesi ElevenLabs anahtariyla ONBELLEGE YAZILMAZ (kalici yanlis ses olmasin)
  if (cfg.tts.saglayici === 'elevenlabs' && cfg.elevenlabs.apiKey && cfg.elevenlabs.voiceId) {
    try {
      const buf = await elevenlabs(t);
      if (buf.length) {
        if (olcum) olcum.elKarakter = (olcum.elKarakter || 0) + karakterSay(t);
        onbellekKoy(t, buf, ses);
        return buf;
      }
    } catch (e) {
      let m = e.message;
      try { m = Buffer.from(e.response.data).toString('utf8').slice(0, 200); } catch (_) {}
      log.error('ElevenLabs TTS hatasi -> Google ile okunuyor:', m);
      elHata = true;
    }
  }

  if (!cfg.tts.apiKey) {
    log.error('GOOGLE_TTS_API_KEY yok — seslendirilemiyor');
    return Buffer.alloc(0);
  }

  const url = `https://texttospeech.googleapis.com/v1/text:synthesize?key=${cfg.tts.apiKey}`;
  const body = {
    input: { text: t },
    voice: { languageCode: 'tr-TR', name: ses },
    audioConfig: audioConfig(),
  };

  try {
    const { data } = await axios.post(url, body, { timeout: 12000, httpsAgent: ajan });
    let buf = Buffer.from(data.audioContent, 'base64');
    if (cfg.mediaFormat === 'slin16') buf = stripWavHeader(buf);
    // ulaw ciktisi da WAV header'li gelebilir -> ayikla
    else buf = stripWavHeader(buf);

    if (olcum) olcum.ttsKarakter = (olcum.ttsKarakter || 0) + karakterSay(t);
    if (!elHata) onbellekKoy(t, buf, ses);
    return buf;
  } catch (e) {
    log.error('TTS hatasi:', e.response?.data?.error?.message || e.message);
    return Buffer.alloc(0);
  }
}

// ---- AKISLI SESLENDIRME (ElevenLabs /stream): ses geldikce calinir; v3 gibi yavas modelde cumlenin tamamini
// beklemek 1-2 sn ekliyordu, akisla ilk ses ~0,8 sn'de baslar. Parcalar 20 ms kareye hizalanir (yarim kare
// sessizlikle doldurulursa parca aralarinda citirti olur). Onbellekteki / Google'a dusen cumle tek parca gelir.
function akisli() {
  return cfg.tts.saglayici === 'elevenlabs' && !!cfg.elevenlabs.apiKey && !!cfg.elevenlabs.voiceId;
}

async function seslendirAkis(metin, onParca, opt) {
  const t = okunur(metin).trim();
  if (!t) return;
  if (!akisli() || onbellekAl(t)) { const b = await seslendir(metin, opt); if (b && b.length) onParca(b); return; }
  const olcum = opt && opt.olcum;
  if (opt) opt.kaynak = 'yeni';

  const e = cfg.elevenlabs;
  const bicim = cfg.mediaFormat === 'slin16' ? 'pcm_16000' : 'ulaw_8000';
  const url = `https://api.elevenlabs.io/v1/text-to-speech/${encodeURIComponent(e.voiceId)}/stream?output_format=${bicim}`;
  const govde = { text: elYazim(t), model_id: e.model, voice_settings: { stability: e.stability, similarity_boost: e.similarity, speed: e.speed } };
  if (e.dil && /v2_5|v3|v4/.test(e.model)) govde.language_code = e.dil; // Turkce zorla (ELEVENLABS_DIL=, bos = model kendi secer)
  const kare = cfg.audio.bytesPerFrame;
  const parcalar = [];
  let artik = Buffer.alloc(0), geldi = false;
  try {
    if (olcum) olcum.elKarakter = (olcum.elKarakter || 0) + karakterSay(t);
    const res = await axios.post(url, govde, {
      httpsAgent: ajan, timeout: 15000, responseType: 'stream',
      headers: { 'xi-api-key': e.apiKey, 'Content-Type': 'application/json' },
    });
    await new Promise((coz, red) => {
      res.data.on('data', (c) => {
        geldi = true;
        parcalar.push(c);
        artik = artik.length ? Buffer.concat([artik, c]) : c;
        const tam = artik.length - (artik.length % kare);
        if (tam > 0) { onParca(artik.slice(0, tam)); artik = artik.slice(tam); }
      });
      res.data.on('end', coz);
      res.data.on('error', red);
    });
    if (artik.length) onParca(artik);
    const tum = Buffer.concat(parcalar);
    if (tum.length) onbellekKoy(t, tum);
  } catch (err) {
    let m = err.message;
    try { if (err.response && err.response.data && err.response.data.read) m = String(err.response.data.read() || m).slice(0, 200); } catch (_) {}
    if (geldi) { log.error('ElevenLabs akis yarida kesildi:', m); return; }
    log.error('ElevenLabs akis hatasi -> Google ile okunuyor:', m);
    const ds = cfg.tts.saglayici; cfg.tts.saglayici = 'google';
    try { const b = await seslendir(metin, opt); if (b && b.length) onParca(b); } finally { cfg.tts.saglayici = ds; }
  }
}

module.exports = { seslendir, seslendirAkis, akisli, elYazim, isit };
