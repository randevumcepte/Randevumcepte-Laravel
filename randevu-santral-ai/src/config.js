'use strict';
// .env'i HER ZAMAN proje kokunden oku (hangi dizinden calistirilirsa calistirilsin).
// Aksi halde `node /opt/.../src/index.js` /root'tan calisinca .env bulunamaz.
require('dotenv').config({ path: require('path').resolve(__dirname, '..', '.env') });

function bool(v, def) {
  if (v === undefined || v === null || v === '') return def;
  return v === '1' || String(v).toLowerCase() === 'true';
}
function num(v, def) {
  const n = parseInt(v, 10);
  return Number.isFinite(n) ? n : def;
}

const cfg = {
  ari: {
    url: process.env.ARI_URL || 'http://127.0.0.1:8088',
    user: process.env.ARI_USER || 'santral',
    pass: process.env.ARI_PASS || '',
    app: process.env.ARI_APP || 'randevu-santral-ai',
  },
  rtp: {
    host: process.env.RTP_HOST || '127.0.0.1',
    portBase: num(process.env.RTP_PORT_BASE, 40000),
    portCount: num(process.env.RTP_PORT_COUNT, 100),
  },
  // ulaw = PCMU/8000 (telefon native), slin16 = LINEAR16/16000
  mediaFormat: (process.env.MEDIA_FORMAT || 'ulaw').toLowerCase(),
  laravel: {
    baseUrl: (process.env.LARAVEL_BASE_URL || '').replace(/\/+$/, ''),
    defaultSubeId: num(process.env.DEFAULT_SUBE_ID, 1),
    secret: process.env.SANTRAL_SECRET || '',
  },
  stt: {
    language: process.env.STT_LANGUAGE || 'tr-TR',
    model: process.env.STT_MODEL || 'latest_long', // tr-TR icin uygun; phone_call en-* disi dillerde calismaz
  },
  tts: {
    apiKey: process.env.GOOGLE_TTS_API_KEY || '',
    voice: process.env.TTS_VOICE || 'tr-TR-Wavenet-E', // varsayilan; isletmenin sesi Laravel'den (paket ayari) gelir
    // Acilista kalip cumleleri onbellege alinacak sesler (Laravel config santral_ai.tts_sesler ile ayni liste)
    sesler: Array.from(new Set([process.env.TTS_VOICE || 'tr-TR-Wavenet-E'].concat(
      (process.env.TTS_SESLER || 'tr-TR-Wavenet-E,tr-TR-Chirp3-HD-Kore,tr-TR-Chirp3-HD-Achernar').split(',').map((x) => x.trim()).filter(Boolean)))),
    speakingRate: parseFloat(process.env.TTS_SPEAKING_RATE || '1.05'),
    profil: process.env.TTS_PROFIL !== undefined ? process.env.TTS_PROFIL : 'telephony-class-application',
    // Saglayici: 'google' (varsayilan) | 'elevenlabs'. ElevenLabs hata verirse o cumle Google ile okunur.
    saglayici: (process.env.TTS_SAGLAYICI || 'google').toLowerCase(),
  },
  elevenlabs: {
    apiKey: process.env.ELEVENLABS_API_KEY || '',
    voiceId: process.env.ELEVENLABS_VOICE_ID || '',
    model: process.env.ELEVENLABS_MODEL || 'eleven_flash_v2_5', // dusuk gecikme; daha dogal: eleven_multilingual_v2
    stability: parseFloat(process.env.ELEVENLABS_STABILITY || '0.5'),
    similarity: parseFloat(process.env.ELEVENLABS_SIMILARITY || '0.75'),
    speed: parseFloat(process.env.ELEVENLABS_SPEED || '1.0'),
    // "başka=bashka;lazer=lazır" -> [['başka','bashka'],['lazer','lazır']]
    yazim: String(process.env.ELEVENLABS_YAZIM || '').split(';').map((x) => x.split('=').map((y) => y.trim()))
      .filter((x) => x.length === 2 && x[0] && x[1]),
    dil: process.env.ELEVENLABS_DIL !== undefined ? process.env.ELEVENLABS_DIL.trim() : 'tr', // bos = dil kodu gonderilmez
  },
  bargeIn: bool(process.env.BARGE_IN, true),
  sessizlikMs: num(process.env.SESSIZLIK_MS, 15000),
  // Veda cumlesinden sonra hat en gec bu kadar ms'de kapanir (ses bitince daha erken). Eskiden 18 sn idi.
  kapanisMaxMs: num(process.env.KAPANIS_MAX_MS, 7000),
  // Bekleme cumlesi: beyin cevabi bu kadar ms icinde gelmezse "Hemen bakıyorum." gibi kisa bir cumle cal
  // (musteri sessizlikte kalmasin). 0 = kapali.
  dolguMs: num(process.env.DOLGU_MS, 1800),
  // Kisa tepki ('Tamam.', 'Bir bakayım.'): musteri sustuktan sonra cevap bu kadar ms'de gelmezse calar. 0 = kapali.
  tepkiMs: num(process.env.TEPKI_MS, 350),
  // AI konusurken STT akisini kapat (maliyet ~%40-50 duser); 0 = eski davranis (surekli acik)
  sttDuraklat: (process.env.STT_DURAKLAT || '1') !== '0',
  // Musteri sustuktan sonra cumleyi BITMIS sayma suresi (Google final'i gecikirse). Eskiden 1200/2400 ms idi:
  // uzun cumlelerde her turda 2,4 sn bosuna bekleniyordu (kayit #56: kod cevabi bile 2,4 sn'de basliyordu).
  endpointKisaMs: num(process.env.ENDPOINT_KISA_MS, 900),   // <=3 kelime ("evet", "fark etmez")
  endpointUzunMs: num(process.env.ENDPOINT_UZUN_MS, 1400),  // uzun cumle (ad/adres icin biraz sabirli)
  // SES ENERJISIYLE (VAD) bitis: ara-metin Google'dan ~0,3-0,5 sn gec geldiginden yukaridaki sureler fiilen
  // 1,2-1,9 sn bekletiyordu (kayit #78: her turda ~3,5-4,5 sn bosluk). Musterinin sesi GERCEKTEN bu kadar ms
  // kesilince + ara-metin 250 ms sabit kalinca cumle bitmis sayilir. Ustteki sureler yedek olarak kalir. 0 = kapali.
  vadBitisKisaMs: num(process.env.VAD_BITIS_KISA_MS, 550),   // <=3 kelime
  vadBitisKapaliMs: num(process.env.VAD_BITIS_KAPALI_MS, 380), // 'evet', 'olur', 'fark etmez', '13' gibi kapali cevap
  vadBitisUzunMs: num(process.env.VAD_BITIS_UZUN_MS, 850),   // uzun cumle (cumle arasi nefes ~0,5 sn kesmesin)
  logLevel: process.env.LOG_LEVEL || 'info',
  recording: {
    aktif: bool(process.env.KAYIT_AKTIF, true),
    dir: process.env.ASTERISK_RECORDING_DIR || '/var/spool/asterisk/recording',
  },
  // Terk edilen cagri kurtarma (otomatik geri arama). Outbound GEREKIR -> varsayilan KAPALI.
  geriArama: {
    aktif: bool(process.env.GERI_ARAMA, false),
    dial: process.env.GERI_ARAMA_DIAL || '',          // or. "SIP/trunkadi/{num}" veya "PJSIP/{num}@trunkadi"
    callerId: process.env.GERI_ARAMA_CALLERID || '',
    prefix: process.env.GERI_ARAMA_PREFIX || '0',     // 90532... -> son10 basina: 0 (ulusal) / 90 / +90 / bos
    timeout: num(process.env.GERI_ARAMA_TIMEOUT, 30), // callee cevap suresi (sn)
    pollSn: num(process.env.GERI_ARAMA_POLL_SN, 20),  // kuyruk yoklama araligi (sn)
  },
  // Ofis ambiyansi (comfort noise): TTS altinda kisik surekli arka ses (UCRETSIZ).
  ambiyans: {
    aktif: bool(process.env.AMBIYANS, true),
    seviye: parseFloat(process.env.AMBIYANS_SEVIYE || '0.25'),   // AI KONUSURKEN ambiyans seviyesi
    dinleme: parseFloat(process.env.AMBIYANS_DINLEME || '0.3'),  // AI DINLERKEN carpan (0=kapali..1=ayni); dusuk=STT'yi az etkiler
    dosya: process.env.AMBIYANS_DOSYA || require('path').resolve(__dirname, '..', 'assets', 'ofis-ambiyans.sln'),
  },
};

// Ses parametreleri (format'a gore)
cfg.audio = cfg.mediaFormat === 'slin16'
  ? { sampleRate: 16000, sttEncoding: 'LINEAR16', ttsEncoding: 'LINEAR16', rtpPayloadType: 118, bytesPerFrame: 640 } // 20ms @16k @16bit
  : { sampleRate: 8000, sttEncoding: 'MULAW', ttsEncoding: 'MULAW', rtpPayloadType: 0, bytesPerFrame: 160 };        // 20ms @8k ulaw

module.exports = cfg;
