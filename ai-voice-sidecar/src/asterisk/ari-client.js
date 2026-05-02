import ariClient from 'ari-client';
import { config } from '../config.js';

/**
 * Asterisk ARI bağlantısı — Stasis app olarak "randevu_ai"i dinler.
 *
 * Asterisk → Sidecar event'leri (WebSocket):
 *   - StasisStart:  yeni çağrı geldi (bu app'e devredildi)
 *   - StasisEnd:    çağrı bitti
 *   - ChannelDtmfReceived:  müşteri tuşa bastı (örn. operatöre geçmek için)
 *
 * Sidecar → Asterisk komutları (HTTP POST):
 *   - channel.answer()
 *   - channel.play(...)
 *   - channel.hangup()
 *   - client.channels.externalMedia({...})  // RTP audio kanalı oluştur
 */
export class AriService {
  constructor({ onCall } = {}) {
    this.client = null;
    this.onCall = onCall || (() => {});
    this.activeCalls = new Map(); // channelId → { conversation, rtpPort }
  }

  /**
   * Asterisk'e bağlan ve Stasis app'i başlat.
   */
  async connect() {
    const { host, ariPort, ariUser, ariPass, stasisApp } = config.asterisk;
    if (!ariPass) {
      throw new Error('ASTERISK_ARI_PASS .env\'de bos. Asterisk ari.conf parolasini ekle.');
    }
    const url = `http://${host}:${ariPort}`;
    console.log(`[ARI] Baglaniyor: ${url} user=${ariUser} app=${stasisApp}`);

    this.client = await ariClient.connect(url, ariUser, ariPass);

    // Yeni çağrı — Asterisk dialplan'da Stasis(randevu_ai,...) çalıştığında tetiklenir
    this.client.on('StasisStart', (event, channel) => {
      this._onStasisStart(event, channel).catch((e) => {
        console.error(`[ARI] StasisStart handler hatasi:`, e);
        try { channel.hangup(); } catch {}
      });
    });

    // Çağrı bitti
    this.client.on('StasisEnd', (event, channel) => {
      const ctx = this.activeCalls.get(channel.id);
      console.log(`[ARI] StasisEnd channel=${channel.id} sure=${ctx ? Math.round((Date.now() - ctx.startedAt) / 1000) + 's' : '?'}`);
      this.activeCalls.delete(channel.id);
    });

    // WebSocket bağlantı durumu
    this.client.on('WebSocketReconnecting', () => console.warn('[ARI] WebSocket yeniden baglaniyor...'));
    this.client.on('WebSocketConnected', () => console.log('[ARI] WebSocket bagli'));
    this.client.on('APILoadError', (err) => console.error('[ARI] API yuklenemedi:', err));

    // Stasis app'i başlat (Asterisk'e "ben dinliyorum" demek)
    await this.client.start(stasisApp);
    console.log(`[ARI] Hazir. Stasis app="${stasisApp}" calisiyor, cagri bekleniyor...`);
  }

  /**
   * Yeni çağrı geldiğinde:
   *   1. Cevapla
   *   2. Salon kimliğini DID'den çöz
   *   3. Conversation başlat
   *   4. (Faz 2 sonraki adım) External Media kanalı kur
   *   5. Audio I/O döngüsünü başlat
   */
  async _onStasisStart(event, channel) {
    const args = event.args || [];
    const callerNum = args[0] || channel.caller?.number || '';
    const fromDid = args[1] || '';

    console.log(`[ARI] StasisStart channel=${channel.id} caller=${callerNum} did=${fromDid}`);

    // Salon kimliğini DID'den çöz
    const salonId = config.didSalonMap[fromDid] || config.testSalonId;

    // Cevapla
    await channel.answer();

    // İletişimi başlat (state machine + STT/LLM/TTS)
    const ctx = {
      channel,
      callerNum,
      fromDid,
      salonId,
      startedAt: Date.now(),
    };
    this.activeCalls.set(channel.id, ctx);

    // Faz 2 sonraki adim: external media + RTP audio I/O
    // Şimdilik sadece "merhaba" anonsu çal ve kapat
    try {
      await this.onCall(ctx);
    } catch (e) {
      console.error(`[ARI] onCall hatasi:`, e);
      try { await channel.hangup(); } catch {}
    }
  }

  async disconnect() {
    if (this.client) {
      try {
        await this.client.stop();
      } catch (e) {
        console.warn('[ARI] stop hatasi:', e.message);
      }
    }
  }
}
