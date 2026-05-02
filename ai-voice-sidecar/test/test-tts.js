import path from 'path';
import { tts } from '../src/tts/edge-tts.js';
import { config } from '../src/config.js';

const text = process.argv.slice(2).join(' ') || 'Merhaba, ben Randevumcepte sesli asistanı. Size nasıl yardımcı olabilirim?';

console.log(`[TTS] Ses: ${config.tts.voice}`);
console.log(`[TTS] Metin: ${text}`);

const outFile = path.join(config.outputDir, 'test-tts.mp3');
const t0 = Date.now();
const saved = await tts.toFile(text, outFile);
const ms = Date.now() - t0;

console.log(`[TTS] OK ${ms}ms → ${saved}`);
console.log(`[TTS] Dinlemek için: start ${saved}    (Windows)`);
