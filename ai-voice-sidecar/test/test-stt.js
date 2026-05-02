import path from 'path';
import { stt } from '../src/stt/groq-stt.js';
import { config } from '../src/config.js';

const file = process.argv[2] || path.join(config.outputDir, 'test-tts.mp3');
console.log(`[STT] Dosya: ${file}`);

const result = await stt.transcribeFile(file);
console.log(`[STT] OK ${result.durationMs}ms`);
console.log(`[STT] Metin: ${result.text}`);
