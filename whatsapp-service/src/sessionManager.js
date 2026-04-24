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

  await session.sock.sendPresenceUpdate('composing', jid);
  await sleep(gaussianJitter(config.antiban.typingMinMs, config.antiban.typingMaxMs));
  await session.sock.sendPresenceUpdate('paused', jid);

  const result = await session.sock.sendMessage(jid, { text: job.message });
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

  const sock = makeWASocket({
    version,
    auth: state,
    browser: Browsers.appropriate('Chrome'),
    printQRInTerminal: false,
    syncFullHistory: false,
    markOnlineOnConnect: false,
    logger: logger.child({ salonId }),
    generateHighQualityLinkPreview: false,
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
      logger.info({ salonId: key, phone: session.phone }, 'connected');
      notifyLaravel('connected', { salonId: key, phone: session.phone });
    }

    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const reason = statusCode ? DisconnectReason[statusCode] || statusCode : 'unknown';
      session.lastError = reason;
      logger.warn({ salonId: key, reason, statusCode }, 'connection closed');

      // Ban / rate-limit / kalıcı askı sinyalleri
      const BAN_CODES = new Set([
        DisconnectReason.loggedOut,     // 401
        DisconnectReason.forbidden,      // 403
        DisconnectReason.badSession,     // 500
        DisconnectReason.multideviceMismatch,
        401, 403, 406, 410, 411,
      ]);
      const RATE_LIMIT_CODES = new Set([
        DisconnectReason.connectionReplaced, // 440
        DisconnectReason.timedOut,           // 408
        408, 428, 429,
      ]);

      const banLikely = BAN_CODES.has(statusCode);
      const rateLimitLikely = RATE_LIMIT_CODES.has(statusCode);

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
        // Rate-limit → ban-warning gönder ama session'ı sil, re-login gerekli
        session.status = 'rate-limited';
        notifyLaravel('ban.warning', {
          salonId: key,
          reason: `rate-limited-${reason}`,
          lastError: `statusCode-${statusCode}`,
          phone: session.phone,
        });
        sessions.delete(key);
      } else {
        session.status = 'disconnected';
        notifyLaravel('disconnected', { salonId: key, reason, statusCode, banLikely: false });
        setTimeout(() => {
          if (sessions.get(key) === session) {
            sessions.delete(key);
            createSession(salonId).catch((err) =>
              logger.error({ err: err.message }, 'reconnect failed'),
            );
          }
        }, 5000);
      }
    }
  });

  return session;
};

const logoutSession = async (salonId) => {
  const key = String(salonId);
  const session = sessions.get(key);
  if (session && session.sock) {
    try {
      await session.sock.logout();
    } catch (_) {}
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

module.exports = {
  createSession,
  logoutSession,
  queueMessage,
  statusOf,
  qrOf,
  getSession,
};
