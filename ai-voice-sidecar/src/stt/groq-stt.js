import Groq from 'groq-sdk';
import fs from 'fs';
import { config } from '../config.js';

/**
 * Groq Whisper API wrapper (Türkçe ses → metin).
 *
 * Free tier: dakikada belirli sayıda istek (yeterli).
 * Ücretli: ~$0.111/saat ses (~$0.00185/dakika).
 *
 * Model: whisper-large-v3 — Türkçe için en iyi açık model.
 */
export class GroqSTT {
  constructor() {
    this.client = new Groq({ apiKey: config.groq.apiKey });
    this.model = config.groq.sttModel;
  }

  /**
   * Ses dosyasını Türkçe metne çevir.
   * @param {string} filePath - mp3/wav/m4a/ogg/flac/webm
   * @returns {Promise<{text: string, durationMs: number}>}
   */
  async transcribeFile(filePath) {
    if (!fs.existsSync(filePath)) {
      throw new Error(`Ses dosyası yok: ${filePath}`);
    }
    const t0 = Date.now();
    const result = await this.client.audio.transcriptions.create({
      file: fs.createReadStream(filePath),
      model: this.model,
      language: 'tr',
      // temperature: 0 → deterministic, randevu için doğru tercih
      temperature: 0,
      response_format: 'verbose_json',
    });
    return {
      text: (result.text || '').trim(),
      durationMs: Date.now() - t0,
      raw: result,
    };
  }

  /**
   * Asterisk'ten gelen audio buffer'ı transcribe et.
   * @param {Buffer} buffer
   * @param {string} mimeExt - 'wav' | 'mp3' | 'ogg'
   */
  async transcribeBuffer(buffer, mimeExt = 'wav') {
    const t0 = Date.now();
    const file = await import('groq-sdk/uploads.js')
      .then((m) => m.toFile(buffer, `audio.${mimeExt}`))
      .catch(async () => {
        // Fallback: temp dosya
        const tmpDir = config.outputDir;
        const tmp = `${tmpDir}/_stt-tmp-${Date.now()}.${mimeExt}`;
        fs.writeFileSync(tmp, buffer);
        return fs.createReadStream(tmp);
      });
    const result = await this.client.audio.transcriptions.create({
      file,
      model: this.model,
      language: 'tr',
      temperature: 0,
      response_format: 'verbose_json',
    });
    return {
      text: (result.text || '').trim(),
      durationMs: Date.now() - t0,
      raw: result,
    };
  }
}

export const stt = new GroqSTT();
