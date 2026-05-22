'use strict';

const path = require('path');
const fs = require('fs');
const QRCode = require('qrcode');
const axios = require('axios');

// Baileys v6 artik ESM-only; CommonJS'ten dinamik import ile yuklüyoruz.
let _baileys = null;
const loadBaileys = async () => {
  if (_baileys) return _baileys;
  const mod = await import('@whiskeysockets/baileys');
  _baileys = {
    makeWASocket: mod.default || mod.makeWASocket,
    useMultiFileAuthState: mod.useMultiFileAuthState,
    DisconnectReason: mod.DisconnectReason,
    fetchLatestBaileysVersion: mod.fetchLatestBaileysVersion,
    Browsers: mod.Browsers,
  };
  return _baileys;
};

const config = require('./config');
const logger = require('./logger');
const { SendQueue, gaussianJitter, sleep } = require('./antiban');

const sessions = new Map();

const sessionDir = (salonId) => path.join(config.sessionsDir, `salon_${salonId}`);

const normalizePhone = (raw) => {
  if (!raw) return null;
  let n = String(raw).replace(/\D/g, '');
  if (n.startsWith('00')) n = n.slice(2);
  if (n.length === 10 && n.startsWith('5')) n = '90' + n;
  if (n.length === 11 && n.startsWith('0')) n = '90' + n.slice(1);
  return n.length >= 11 ? n : null;
};

const toJid = (phone) => `${phone}@s.whatsapp.net`;

const notifyLaravel = async (event, payload) => {
  if (!config.laravel.webhookUrl) return;
  try {
    await axios.post(
      config.laravel.webhookUrl,
      { event, ...payload },
      {
        timeout: 5000,
        headers: { 'X-Webhook-Secret': config.laravel.webhookSecret },
      },
    );
  } catch (err) {
    logger.warn({ err: err.message, event }, 'laravel webhook failed');
  }
};

const getSession = (salonId) => sessions.get(String(salonId));

const buildSender = (salonId) => async (job) => {
  const session = getSession(salonId);
  if (!session || !session.sock || session.status !== 'connected') {
    throw new Error('session-not-connected');
  }
  const phone = normalizePhone(job.to);
  if (!phone) throw new Error('invalid-phone');

  const jid = toJid(phone);

  try {
    const [onWhatsApp] = await session.sock.onWhatsApp(jid);
    if (!onWhatsApp || !onWhatsApp.exists) {
      throw new Error('not-on-whatsapp');
    }
  } catch (err) {
    if (err.message === 'not-on-whatsapp') throw err;
    logger.warn({ err: err.message }, 'onWhatsApp check failed, continuing');
  }

  if (!job.urgent) {
    // Normal mesajlar: 2-4 sn typing simulation (antiban)
    await session.sock.sendPresenceUpdate('composing', jid);
    await sleep(gaussianJitter(config.antiban.typingMinMs, config.antiban.typingMaxMs));
    await session.sock.sendPresenceUpdate('paused', jid);
  } else {
    // Urgent (sifre/OTP): typing simulation yok — anlik gonderim.
    // Yine de kisa bir 'available' presence atilir (~50ms): E2E sifreleme
    // oturumunu canlandirir, alicida 'mesaj bekleniyor' takilmasini azaltir.
    try { await session.sock.sendPresenceUpdate('available', jid); } catch (_) {}
  }

  const result = await session.sock.sendMessage(jid, { text: job.message });

  // Mesaji getMessage cache'ine yaz — retry receipt gelirse yeniden gonderilebilsin.
  // Son ~1000 mesaj tutulur (FIFO), bellek sismez.
  if (result?.key?.id && result?.message && session.sentMessages) {
    session.sentMessages.set(result.key.id, result.message);
    if (session.sentMessages.size > 1000) {
      const eskiKey = session.sentMessages.keys().next().value;
      session.sentMessages.delete(eskiKey);
    }
  }

  return { messageId: result?.key?.id || null, phone };
};

const createSession = async (salonId) => {
  const key = String(salonId);
  if (sessions.has(key)) {
    const existing = sessions.get(key);
    if (existing.status === 'connected' || existing.status === 'connecting') {
      return existing;
    }
  }

  const dir = sessionDir(salonId);
  fs.mkdirSync(dir, { recursive: true });

  const {
    makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    Browsers,
  } = await loadBaileys();

  const { state, saveCreds } = await useMultiFileAuthState(dir);
  const { version } = await fetchLatestBaileysVersion();

  // Gonderilen mesajlarin icerigi — alici 'cozulemedi, tekrar gonder' (retry receipt)
  // istedignde Baileys getMessage ile buradan okuyup mesaji yeniden sifreleyip yollar.
  // Bu callback olmazsa retry'lara cevap verilemez ve mesaj alicida 'mesaj bekleniyor'
  // (Waiting for this message) olarak kalici takilir.
  const sentMessages = new Map();

  const sock = makeWASocket({
    version,
    auth: state,
    browser: Browsers.appropriate('Chrome'),
    printQRInTerminal: false,
    syncFullHistory: false,
    markOnlineOnConnect: false,
    logger: logger.child({ salonId }),
    generateHighQualityLinkPreview: false,
    keepAliveIntervalMs: 15000,        // 15s - NAT/conntrack idle timeout'un (genelde 180s) cok altinda
    connectTimeoutMs: 60000,           // ilk bagli olma timeout'unu uzat (default 20s yetersiz)
    defaultQueryTimeoutMs: 120000,     // 120s — buyuk hesaplarda 'init queries' 60s'e sigmiyordu (408 timeout)
    retryRequestDelayMs: 2000,         // istek tekrarinda 2s bekle
    // Buyuk hesaplarda history sync bagilanti sonrasi yuku agirlastirip 'init queries'
    // timeout'una yol aciyor. Gelen mesaj gecmisini zaten kullanmiyoruz — tamamen kapatiyoruz.
    shouldSyncHistoryMessage: () => false,
    getMessage: async (msgKey) => {
      const m = sentMessages.get(msgKey?.id);
      return m || undefined;
    },
  });

  const session = {
    salonId: key,
    sock,
    status: 'connecting',
    qrDataUrl: null,
    lastError: null,
    connectedAt: null,
    phone: null,
    queue: null,
    sentMessages,
  };

  session.queue = new SendQueue(key, buildSender(key), {
    onSent: ({ job, result }) => {
      notifyLaravel('message.sent', {
        salonId: key,
        logId: job.logId || null,
        messageId: result?.messageId,
        phone: result?.phone,
      });
    },
    onFailed: ({ job, error }) => {
      notifyLaravel('message.failed', {
        salonId: key,
        logId: job.logId || null,
        phone: job.to,
        error: error.message,
      });
    },
    onHealthAlarm: async ({ reason, lastError }) => {
      logger.warn({ salonId: key, reason, lastError }, 'health alarm — auto-pausing session');
      session.status = 'auto-paused-ban-risk';
      session.lastError = reason;
      await notifyLaravel('ban.warning', {
        salonId: key,
        reason,
        lastError,
        phone: session.phone,
      });
      try {
        await session.sock.logout();
      } catch (_) {}
      try {
        fs.rmSync(dir, { recursive: true, force: true });
      } catch (_) {}
      sessions.delete(key);
    },
  });

  sessions.set(key, session);

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      try {
        session.qrDataUrl = await QRCode.toDataURL(qr, { width: 320, margin: 1 });
        session.status = 'qr-pending';
        logger.info({ salonId: key }, 'QR issued');
        notifyLaravel('qr.ready', { salonId: key });
      } catch (err) {
        logger.error({ err: err.message }, 'qr encode failed');
      }
    }

    if (connection === 'open') {
      session.status = 'connected';
      session.qrDataUrl = null;
      session.connectedAt = new Date().toISOString();
      session.phone = sock.user?.id?.split(':')[0] || null;
      session.reconnectAttempts = 0;
      logger.info({ salonId: key, phone: session.phone }, 'connected');
      notifyLaravel('connected', { salonId: key, phone: session.phone });

      // Keep-alive: NAT/conntrack idle timeout (genelde 180s) altinda interval lazim,
      // yoksa TCP bagi koparilir ve 408 timedOut alinir. 30s tampon. Onceki deger
      // 240000ms (4dk) idi — NAT zaten 3dk'da dusurdugu icin etkisizdi.
      if (session.keepAliveTimer) clearInterval(session.keepAliveTimer);
      const keepMs = config.antiban.keepAliveIntervalMs || 30000;
      session.keepAliveTimer = setInterval(async () => {
        try {
          if (session.status === 'connected' && session.sock) {
            // sendPresenceUpdate query'si idle timer'ı resetler — antiban açısından
            // 'unavailable' güvenli: "online" göstermez ama bağlantıyı canlı tutar
            await session.sock.sendPresenceUpdate('unavailable');
          }
        } catch (err) {
          logger.debug({ salonId: key, err: err.message }, 'keep-alive presence failed');
        }
      }, keepMs);
    }

    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const reason = statusCode ? DisconnectReason[statusCode] || statusCode : 'unknown';
      session.lastError = reason;
      logger.warn({ salonId: key, reason, statusCode }, 'connection closed');

      // Keep-alive timer'ı temizle — yeni session ile birlikte yeniden kurulacak
      if (session.keepAliveTimer) {
        clearInterval(session.keepAliveTimer);
        session.keepAliveTimer = null;
      }

      // 401/403 = gercek logout/ban — auth state silinmeli, kullanici yeniden QR
      // Not: 500 (badSession) ve multideviceMismatch onceden BAN_CODES'taydi ama
      // bunlar genelde GECICI sorunlar — Baileys'in normal davranisi auth'u silmeden
      // session re-init etmek. Auth silersek 5 saatte bir 'kendi kendine logout'
      // gibi gorunuyor. TEMPORARY'ye tasidik, kullanici yeniden QR yapmadan reconnect olur.
      const BAN_CODES = new Set([
        DisconnectReason.loggedOut,     // 401 — gercek logout
        DisconnectReason.forbidden,      // 403 — gercek ban
        401, 403, 406, 410, 411,
      ]);
      // 429 = gercek rate-limit, 440 = baska cihazdan bagli (kullanici niyeti)
      const RATE_LIMIT_CODES = new Set([
        DisconnectReason.connectionReplaced, // 440
        429,
      ]);
      // 408/428/500/515 + multideviceMismatch = gecici kopma — session korunur, reconnect.
      // 500 (badSession) ve multideviceMismatch Baileys'te genelde "protocol refresh"
      // sinyalidir; auth'u silmeden reconnect ile cozulebilir.
      const TEMPORARY_DISCONNECT_CODES = new Set([
        DisconnectReason.timedOut,           // 408
        DisconnectReason.connectionLost,     // 408 (alias)
        DisconnectReason.connectionClosed,   // 428
        DisconnectReason.badSession,         // 500 — auth bozulmadi, sadece session refresh
        DisconnectReason.multideviceMismatch,// genelde protocol mismatch, reconnect cozer
        DisconnectReason.restartRequired,    // 515
        408, 428, 500, 515,
      ]);

      const banLikely = BAN_CODES.has(statusCode);
      const temporary = TEMPORARY_DISCONNECT_CODES.has(statusCode);
      const rateLimitLikely = !temporary && RATE_LIMIT_CODES.has(statusCode);

      if (banLikely) {
        session.status = 'banned-or-loggedout';
        notifyLaravel('disconnected', { salonId: key, reason, statusCode, banLikely: true });
        notifyLaravel('ban.warning', {
          salonId: key,
          reason: `disconnect-${reason}`,
          lastError: `statusCode-${statusCode}`,
          phone: session.phone,
        });
        try {
          fs.rmSync(dir, { recursive: true, force: true });
        } catch (_) {}
        sessions.delete(key);
      } else if (rateLimitLikely) {
        // Gercek rate-limit veya baska cihaza gectik — session sil, ban.warning gonder
        session.status = 'rate-limited';
        notifyLaravel('ban.warning', {
          salonId: key,
          reason: `rate-limited-${reason}`,
          lastError: `statusCode-${statusCode}`,
          phone: session.phone,
        });
        sessions.delete(key);
      } else {
        // Gecici kopma veya bilinmeyen statusCode — auth korunur, otomatik reconnect
        // Backoff: 5s, 15s, 30s, 60s, 120s (max 300s) — Baileys yeniden bagli olunca status='connected' olur
        session.status = 'reconnecting';
        const attempts = (session.reconnectAttempts || 0) + 1;
        session.reconnectAttempts = attempts;
        const backoffMs = Math.min(300000, 5000 * Math.pow(2, Math.min(attempts - 1, 6)));
        logger.info({ salonId: key, statusCode, reason, attempts, backoffMs, temporary }, 'reconnect schedule');
        setTimeout(() => {
          if (sessions.get(key) === session) {
            sessions.delete(key);
            createSession(salonId).catch((err) => {
              logger.error({ err: err.message, salonId: key, attempts }, 'reconnect failed');
              // Reconnect attempt başarısız oldu — yeniden dene (max 8 deneme)
              if (attempts < 8) {
                const retryBackoff = Math.min(600000, 10000 * Math.pow(2, attempts - 1));
                setTimeout(() => {
                  createSession(salonId).catch((err2) =>
                    logger.error({ err: err2.message, salonId: key }, 'reconnect retry failed'),
                  );
                }, retryBackoff);
              } else {
                // 8 başarısız deneme — Laravel'e bildir, kullanıcı manuel müdahale etsin
                notifyLaravel('disconnected', {
                  salonId: key,
                  reason: 'reconnect-exhausted',
                  statusCode: 0,
                  banLikely: false,
                });
              }
            });
          }
        }, backoffMs);
      }
    }
  });

  return session;
};

const logoutSession = async (salonId) => {
  const key = String(salonId);
  const session = sessions.get(key);
  if (session) {
    if (session.keepAliveTimer) {
      clearInterval(session.keepAliveTimer);
      session.keepAliveTimer = null;
    }
    if (session.sock) {
      try {
        await session.sock.logout();
      } catch (_) {}
    }
  }
  sessions.delete(key);
  try {
    fs.rmSync(sessionDir(salonId), { recursive: true, force: true });
  } catch (_) {}
  return true;
};

const queueMessage = (salonId, payload) => {
  const session = getSession(salonId);
  if (!session) throw new Error('session-not-found');
  if (session.status !== 'connected') throw new Error(`session-${session.status}`);
  if (session.queue.paused) throw new Error(`queue-paused-${session.queue.pauseReason || 'health'}`);

  const normalized = payload.to ? payload.to : null;
  if (!normalized) throw new Error('invalid-phone');

  session.queue.enqueue({
    to: payload.to,
    message: payload.message,
    warmupStart: payload.warmupStart || session.connectedAt,
    dailyLimit: payload.dailyLimit || 150,
    logId: payload.logId || null,
    urgent: !!payload.urgent, // anlik gonderim: pre-delay + typing + business hours bypass
  }).catch(() => {});

  return {
    accepted: true,
    queueLength: session.queue.queue.length,
  };
};

const statusOf = (salonId) => {
  const session = getSession(salonId);
  if (!session) return { status: 'not-initialized' };
  return {
    status: session.status,
    phone: session.phone,
    connectedAt: session.connectedAt,
    lastError: session.lastError,
    hasQr: !!session.qrDataUrl,
  };
};

const qrOf = (salonId) => {
  const session = getSession(salonId);
  if (!session) return null;
  return session.qrDataUrl;
};

/**
 * Service başlangıcında sessions/ klasörünü tarayıp daha önce QR taranmış
 * salonların oturumlarını otomatik yeniden başlatır. PM2 restart veya server
 * reboot sonrası kullanıcının yeniden QR taraması gerekmez.
 */
const restoreAllSessions = async () => {
  try {
    if (!fs.existsSync(config.sessionsDir)) {
      logger.info('sessions/ klasörü yok, restore atlanıyor');
      return { restored: 0 };
    }
    const entries = fs.readdirSync(config.sessionsDir, { withFileTypes: true });
    const salonIds = entries
      .filter((e) => e.isDirectory() && e.name.startsWith('salon_'))
      .map((e) => e.name.slice('salon_'.length))
      .filter((id) => /^\d+$/.test(id));

    if (salonIds.length === 0) {
      logger.info('Restore edilecek session yok');
      return { restored: 0 };
    }

    logger.info({ count: salonIds.length, salonIds }, 'Mevcut session\'lar otomatik açılıyor');
    let ok = 0;
    let failed = 0;
    // Paralel değil, sıralı — Baileys auth dosyalarına çakışma olmasın
    for (const salonId of salonIds) {
      try {
        await createSession(salonId);
        ok++;
      } catch (err) {
        failed++;
        logger.warn({ salonId, err: err.message }, 'auto-restore failed');
      }
      // Her session arası 2 saniye bekle — burst connect WhatsApp'ı tetikler
      await new Promise((r) => setTimeout(r, 2000));
    }
    logger.info({ ok, failed }, 'auto-restore tamamlandı');
    return { restored: ok, failed };
  } catch (err) {
    logger.error({ err: err.message }, 'restoreAllSessions hatası');
    return { restored: 0, error: err.message };
  }
};

module.exports = {
  createSession,
  logoutSession,
  queueMessage,
  statusOf,
  qrOf,
  getSession,
  restoreAllSessions,
};
