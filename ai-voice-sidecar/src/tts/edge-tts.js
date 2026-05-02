import { MsEdgeTTS, OUTPUT_FORMAT } from 'msedge-tts';
import fs from 'fs';
import path from 'path';
import { config } from '../config.js';

/**
 * Microsoft Edge TTS wrapper.
 * Türkçe sesler:
 *   - tr-TR-EmelNeural (kadın)
 *   - tr-TR-AhmetNeural (erkek)
 *
 * Maliyet: Ücretsiz. Microsoft'un Edge browser'ının kullandığı
 * Azure Neural TTS'in ters mühendisliği.
 */
export class EdgeTTS {
  constructor({ voice, rate, pitch } = {}) {
    this.voice = voice ?? config.tts.voice;
    this.rate = rate ?? config.tts.rate;
    this.pitch = pitch ?? config.tts.pitch;
  }

  /**
   * Metni MP3 dosyasına dönüştürür.
   * @param {string} text - seslendirilecek Türkçe metin
   * @param {string} outFile - mp3 dosya yolu
   * @returns {Promise<string>} kaydedilen dosya yolu
   */
  async toFile(text, outFile) {
    const tts = new MsEdgeTTS();
    try {
      await tts.setMetadata(this.voice, OUTPUT_FORMAT.AUDIO_24KHZ_48KBITRATE_MONO_MP3);
    } catch (e) {
      throw new Error(`TTS setMetadata fail: ${this._fmtErr(e)}`);
    }
    const dir = path.dirname(outFile);
    const base = path.basename(outFile, path.extname(outFile));
    try {
      const result = await tts.toFile(path.join(dir, base), text, {
        rate: this.rate,
        pitch: this.pitch,
      });
      return result.audioFilePath || (path.join(dir, base) + '.mp3');
    } catch (e) {
      throw new Error(`TTS toFile fail (voice=${this.voice}): ${this._fmtErr(e)}`);
    }
  }

  _fmtErr(e) {
    if (!e) return 'unknown';
    if (typeof e === 'string') return e;
    if (e.message) return e.message;
    try { return JSON.stringify(e); } catch { return String(e); }
  }

  /**
   * Metni Buffer (mp3 bytes) olarak döndürür — Asterisk'e RTP push için.
   */
  async toBuffer(text) {
    const tts = new MsEdgeTTS();
    await tts.setMetadata(this.voice, OUTPUT_FORMAT.AUDIO_24KHZ_48KBITRATE_MONO_MP3);
    return new Promise((resolve, reject) => {
      const chunks = [];
      const stream = tts.toStream(text, { rate: this.rate, pitch: this.pitch });
      stream.audioStream.on('data', (c) => chunks.push(c));
      stream.audioStream.on('end', () => resolve(Buffer.concat(chunks)));
      stream.audioStream.on('error', reject);
    });
  }
}

export const tts = new EdgeTTS();
