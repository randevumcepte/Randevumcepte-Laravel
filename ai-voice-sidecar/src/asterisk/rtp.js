/**
 * Çift yönlü RTP taşıma katmanı — Asterisk external_media (format=slin16) ile.
 *
 * MİMARİ (neden bu):
 *   Sidecar Laravel sunucusunda, Asterisk santral sunucusunda. Dosya tabanlı
 *   record/play iki ayrı diskte çalışmaz. Çözüm: sesi AĞ üzerinden RTP ile taşımak.
 *
 * GELEN (Asterisk → sidecar):
 *   external_media kanalı, kanalın sesini bu UDP portuna RTP paketleri olarak yollar.
 *   Her paket: 12 bayt RTP başlığı + slin16 payload (16-bit LE PCM, 16kHz mono).
 *   'pcm' event'i ile ham PCM parçaları yayınlanır (VAD + STT'ye beslenir).
 *
 * GİDEN (sidecar → Asterisk):
 *   TTS sesini (slin16 16kHz PCM) 20ms'lik çerçevelere böler, doğru RTP başlığıyla
 *   (seq/timestamp/SSRC) Asterisk'in RTP adresine gönderir. Asterisk'in adresi,
 *   GELEN ilk paketin kaynağından öğrenilir (symmetric RTP) — kanal değişkeni gerekmez.
 *
 * Barge-in: müşteri konuşmaya başlayınca stopSending() ile giden kuyruk anında boşaltılır.
 */
import dgram from 'dgram';
import { EventEmitter } from 'events';

export const SAMPLE_RATE = 16000;
export const FRAME_MS = 20;
export const SAMPLES_PER_FRAME = (SAMPLE_RATE * FRAME_MS) / 1000; // 320
export const BYTES_PER_FRAME = SAMPLES_PER_FRAME * 2; // 640 (16-bit)

export class RtpSession extends EventEmitter {
  constructor({ host = '0.0.0.0', port } = {}) {
    super();
    this.host = host;
    this.port = port;
    this.sock = dgram.createSocket('udp4');

    this.remote = null;      // {address, port} — GELEN ilk paketten öğrenilir
    this.payloadType = 118;  // slin16 dinamik PT; gelenden güncellenir
    this.seq = Math.floor(Math.random() * 0xffff);
    this.timestamp = Math.floor(Math.random() * 0xffffffff) >>> 0;
    this.ssrc = Math.floor(Math.random() * 0xffffffff) >>> 0;

    this.sendQueue = [];
    this.sendTimer = null;
    this._speaking = false;
    this._lastInboundAt = 0;

    this.sock.on('message', (msg, rinfo) => {
      if (msg.length <= 12) return; // RTP başlığından küçük = geçersiz
      // Asterisk'in kaynak adresini öğren (giden RTP buraya gidecek)
      if (!this.remote) {
        this.remote = { address: rinfo.address, port: rinfo.port };
        this.emit('remote', this.remote);
      }
      this.payloadType = msg[1] & 0x7f;
      this._lastInboundAt = Date.now();
      this.emit('pcm', msg.subarray(12)); // 12 bayt başlığı atla → ham PCM
    });

    this.sock.on('error', (e) => this.emit('error', e));
  }

  listen() {
    return new Promise((resolve, reject) => {
      this.sock.once('error', reject);
      this.sock.bind(this.port, this.host, () => {
        const addr = this.sock.address();
        this.port = addr.port; // port 0 verildiyse gerçek portu al
        resolve(addr);
      });
    });
  }

  /**
   * TTS PCM'ini (slin16 16kHz mono) 20ms çerçevelere bölüp paced gönderime al.
   * Birden çok kez çağrılabilir (cümle cümle streaming) — kuyruğa eklenir.
   */
  sendPcm(pcm) {
    if (!pcm || !pcm.length) return;
    for (let off = 0; off < pcm.length; off += BYTES_PER_FRAME) {
      let frame = pcm.subarray(off, off + BYTES_PER_FRAME);
      if (frame.length < BYTES_PER_FRAME) {
        const padded = Buffer.alloc(BYTES_PER_FRAME); // son çerçeveyi sessizlikle doldur
        frame.copy(padded);
        frame = padded;
      }
      this.sendQueue.push(frame);
    }
    this._speaking = true;
    this._ensurePump();
  }

  _ensurePump() {
    if (this.sendTimer) return;
    // 20ms'de bir bir çerçeve gönder (gerçek zaman temposunda)
    this.sendTimer = setInterval(() => this._tick(), FRAME_MS);
  }

  _tick() {
    if (this.sendQueue.length === 0) {
      clearInterval(this.sendTimer);
      this.sendTimer = null;
      if (this._speaking) {
        this._speaking = false;
        this.emit('drain'); // tüm TTS çalındı
      }
      return;
    }
    if (!this.remote) return; // henüz Asterisk adresi öğrenilmedi (gelen paket yok)

    const frame = this.sendQueue.shift();
    const header = Buffer.alloc(12);
    header[0] = 0x80;                       // version=2, padding=0, ext=0, cc=0
    header[1] = this.payloadType & 0x7f;    // marker=0 + PT
    header.writeUInt16BE(this.seq & 0xffff, 2);
    header.writeUInt32BE(this.timestamp >>> 0, 4);
    header.writeUInt32BE(this.ssrc >>> 0, 8);

    this.seq = (this.seq + 1) & 0xffff;
    this.timestamp = (this.timestamp + SAMPLES_PER_FRAME) >>> 0;

    const pkt = Buffer.concat([header, frame]);
    this.sock.send(pkt, this.remote.port, this.remote.address, (err) => {
      if (err) this.emit('error', err);
    });
  }

  /** Barge-in / iptal: bekleyen giden sesi anında at. */
  stopSending() {
    this.sendQueue.length = 0;
    // timer bir sonraki tick'te kendini kapatır ve 'drain' yayınlar
  }

  get speaking() {
    return this._speaking;
  }

  close() {
    if (this.sendTimer) {
      clearInterval(this.sendTimer);
      this.sendTimer = null;
    }
    try { this.sock.close(); } catch {}
  }
}
