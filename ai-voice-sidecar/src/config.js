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
    base: optional('LARAVEL_API_BASE', 'https://apptest.randevumcepte.com.tr/api'),
    token: optional('LARAVEL_API_TOKEN', ''),
  },
  outputDir,
  testSalonId: parseInt(optional('TEST_SALON_ID', '15'), 10),
  projectRoot,
  asterisk: {
    host: optional('ASTERISK_HOST', 'localhost'),
    ariPort: parseInt(optional('ASTERISK_ARI_PORT', '8088'), 10),
    ariUser: optional('ASTERISK_ARI_USER', 'randevu_ai'),
    ariPass: optional('ASTERISK_ARI_PASS', ''),
    stasisApp: optional('ASTERISK_STASIS_APP', 'randevu_ai'),
    rtpPortBase: parseInt(optional('RTP_PORT_BASE', '10000'), 10),
    rtpPortCount: parseInt(optional('RTP_PORT_COUNT', '1000'), 10),
  },
  didSalonMap: parseDidMap(optional('DID_SALON_MAP', '')),
};

function parseDidMap(s) {
  const map = {};
  if (!s) return map;
  s.split(',').forEach((pair) => {
    const [did, salonId] = pair.split(':').map((x) => x.trim());
    if (did && salonId) map[did] = parseInt(salonId, 10);
  });
  return map;
}
