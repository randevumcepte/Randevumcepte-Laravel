import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import path from 'path';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');

dotenv.config({ path: path.join(projectRoot, '.env') });

function required(key) {
  const v = process.env[key];
  if (!v) throw new Error(`.env eksik: ${key}`);
  return v;
}

function optional(key, fallback) {
  return process.env[key] ?? fallback;
}

/**
 * Birden fazla env adından ilk dolu olanı döndürür (geriye uyumluluk).
 */
function firstOf(keys, fallback) {
  for (const k of keys) {
    if (process.env[k] !== undefined && process.env[k] !== '') return process.env[k];
  }
  return fallback;
}

/**
 * "http://host:port[/path]" -> { host, port, basePath }
 * Geçersiz/eksik ise null döner.
 */
function parseAriUrl(url) {
  if (!url) return null;
  try {
    const u = new URL(url);
    return {
      host: u.hostname,
      port: u.port ? parseInt(u.port, 10) : (u.protocol === 'https:' ? 443 : 80),
      basePath: (u.pathname || '/').replace(/\/$/, ''),
      full: url.replace(/\/$/, ''),
    };
  } catch {
    return null;
  }
}

const outputDir = path.resolve(projectRoot, optional('OUTPUT_DIR', './output'));
if (!fs.existsSync(outputDir)) fs.mkdirSync(outputDir, { recursive: true });

export const config = {
  groq: {
    apiKey: required('GROQ_API_KEY'),
    llmModel: optional('GROQ_LLM_MODEL', 'llama-3.3-70b-versatile'),
    sttModel: optional('GROQ_STT_MODEL', 'whisper-large-v3'),
  },
  tts: {
    voice: optional('TTS_VOICE', 'tr-TR-EmelNeural'),
    rate: optional('TTS_RATE', '+0%'),
    pitch: optional('TTS_PITCH', '+0Hz'),
  },
  laravel: {
    // LARAVEL_API_URL veya LARAVEL_API_BASE (geriye uyumlu)
    base: firstOf(['LARAVEL_API_URL', 'LARAVEL_API_BASE'], 'https://apptest.randevumcepte.com.tr/api'),
    token: optional('LARAVEL_API_TOKEN', ''),
  },
  outputDir,
  testSalonId: parseInt(optional('TEST_SALON_ID', '15'), 10),
  projectRoot,
  asterisk: buildAsterisk(),
  didSalonMap: parseDidMap(optional('DID_SALON_MAP', '')),
  port: parseInt(optional('PORT', '3000'), 10),
};

function buildAsterisk() {
  // ASTERISK_ARI_URL (tek string) veya HOST/PORT (ayri) — ikisi de calisir
  const url = firstOf(['ASTERISK_ARI_URL'], '');
  const parsed = parseAriUrl(url);
  const host = parsed ? parsed.host : optional('ASTERISK_HOST', 'localhost');
  const ariPort = parsed ? parsed.port : parseInt(optional('ASTERISK_ARI_PORT', '8088'), 10);
  // ari-client connect URL'i — basePath /ari ise direkt onu kullan, yoksa http://host:port
  const baseUrl = parsed ? `${url.split('//')[0]}//${parsed.host}:${parsed.port}` : `http://${host}:${ariPort}`;

  return {
    host,
    ariPort,
    // ari-client'in connect() fonksiyonuna verilen URL (path'siz host:port yeterli)
    url: baseUrl,
    ariUser: optional('ASTERISK_ARI_USER', 'randevu_ai'),
    ariPass: optional('ASTERISK_ARI_PASS', ''),
    // Yaygin yazim hatasi: STATIS yerine STASIS — ikisini de kabul et
    stasisApp: firstOf(['ASTERISK_STASIS_APP', 'ASTERISK_STATIS_APP'], 'randevu_ai'),
    rtpPortBase: parseInt(optional('RTP_PORT_BASE', '10000'), 10),
    rtpPortCount: parseInt(optional('RTP_PORT_COUNT', '1000'), 10),
  };
}

function parseDidMap(s) {
  const map = {};
  if (!s) return map;
  s.split(',').forEach((pair) => {
    const [did, salonId] = pair.split(':').map((x) => x.trim());
    if (did && salonId) map[did] = parseInt(salonId, 10);
  });
  return map;
}
