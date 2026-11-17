'use strict';
// Google Cloud TTS (REST + API anahtari — mevcut GOOGLE_TTS_API_KEY kullanilabilir).
// Cikti: telefon native ses (ulaw@8k) veya slin16@16k — RTP'ye dogrudan paketlenir.
const axios = require('axios');
const cfg = require('./config');
const log = require('./log');

// Basit metin -> ses onbellek (ayni cumle tekrar seslendirilmesin; ozellikle karsilama/sabit yanitlar)
const cache = new Map();
const CACHE_MAX = 200;

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

// ElevenLabs: ciktiyi dogrudan telefon bicimindeki ham sesle ister (ulaw_8000 / pcm_16000; WAV basligi yok).
async function elevenlabs(t) {
  const e = cfg.elevenlabs;
  const bicim = cfg.mediaFormat === 'slin16' ? 'pcm_16000' : 'ulaw_8000';
  const url = `https://api.elevenlabs.io/v1/text-to-speech/${encodeURIComponent(e.voiceId)}?output_format=${bicim}`;
  const govde = {
    text: t,
    model_id: e.model,
    voice_settings: { stability: e.stability, similarity_boost: e.similarity, speed: e.speed },
  };
  // Flash/Turbo v2.5 dil zorlamasini destekler (Turkce kelimeyi Ingilizce okumasin)
  if (e.dil && /v2_5|v3/.test(e.model)) govde.language_code = e.dil; // Turkce zorla (ELEVENLABS_DIL=, bos = model kendi secer)
  const { data } = await axios.post(url, govde, {
    timeout: 12000,
    responseType: 'arraybuffer',
    headers: { 'xi-api-key': e.apiKey, 'Content-Type': 'application/json', Accept: 'audio/basic' },
  });
  return Buffer.from(data);
}

async function seslendir(metin) {
  const t = okunur(metin).trim();
  if (!t) return Buffer.alloc(0);
  if (cache.has(t)) return cache.get(t);

  if (cfg.tts.saglayici === 'elevenlabs' && cfg.elevenlabs.apiKey && cfg.elevenlabs.voiceId) {
    try {
      const buf = await elevenlabs(t);
      if (buf.length) {
        if (cache.size >= CACHE_MAX) cache.delete(cache.keys().next().value);
        cache.set(t, buf);
        return buf;
      }
    } catch (e) {
      let m = e.message;
      try { m = Buffer.from(e.response.data).toString('utf8').slice(0, 200); } catch (_) {}
      log.error('ElevenLabs TTS hatasi -> Google ile okunuyor:', m);
    }
  }

  if (!cfg.tts.apiKey) {
    log.error('GOOGLE_TTS_API_KEY yok — seslendirilemiyor');
    return Buffer.alloc(0);
  }

  const url = `https://texttospeech.googleapis.com/v1/text:synthesize?key=${cfg.tts.apiKey}`;
  const body = {
    input: { text: t },
    voice: { languageCode: 'tr-TR', name: cfg.tts.voice },
    audioConfig: audioConfig(),
  };

  try {
    const { data } = await axios.post(url, body, { timeout: 12000 });
    let buf = Buffer.from(data.audioContent, 'base64');
    if (cfg.mediaFormat === 'slin16') buf = stripWavHeader(buf);
    // ulaw ciktisi da WAV header'li gelebilir -> ayikla
    else buf = stripWavHeader(buf);

    if (cache.size >= CACHE_MAX) cache.delete(cache.keys().next().value);
    cache.set(t, buf);
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

async function seslendirAkis(metin, onParca) {
  const t = okunur(metin).trim();
  if (!t) return;
  if (cache.has(t) || !akisli()) { const b = await seslendir(metin); if (b && b.length) onParca(b); return; }

  const e = cfg.elevenlabs;
  const bicim = cfg.mediaFormat === 'slin16' ? 'pcm_16000' : 'ulaw_8000';
  const url = `https://api.elevenlabs.io/v1/text-to-speech/${encodeURIComponent(e.voiceId)}/stream?output_format=${bicim}`;
  const govde = { text: t, model_id: e.model, voice_settings: { stability: e.stability, similarity_boost: e.similarity, speed: e.speed } };
  if (e.dil && /v2_5|v3/.test(e.model)) govde.language_code = e.dil; // Turkce zorla (ELEVENLABS_DIL=, bos = model kendi secer)
  const kare = cfg.audio.bytesPerFrame;
  const parcalar = [];
  let artik = Buffer.alloc(0), geldi = false;
  try {
    const res = await axios.post(url, govde, {
      timeout: 15000, responseType: 'stream',
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
    if (tum.length) {
      if (cache.size >= CACHE_MAX) cache.delete(cache.keys().next().value);
      cache.set(t, tum);
    }
  } catch (err) {
    let m = err.message;
    try { if (err.response && err.response.data && err.response.data.read) m = String(err.response.data.read() || m).slice(0, 200); } catch (_) {}
    if (geldi) { log.error('ElevenLabs akis yarida kesildi:', m); return; }
    log.error('ElevenLabs akis hatasi -> Google ile okunuyor:', m);
    const ds = cfg.tts.saglayici; cfg.tts.saglayici = 'google';
    try { const b = await seslendir(metin); if (b && b.length) onParca(b); } finally { cfg.tts.saglayici = ds; }
  }
}

module.exports = { seslendir, seslendirAkis, akisli };
