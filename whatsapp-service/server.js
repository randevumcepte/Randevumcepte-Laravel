'use strict';

const express = require('express');
const config = require('./src/config');
const logger = require('./src/logger');
const sessionMgr = require('./src/sessionManager');

const app = express();
app.use(express.json({ limit: '256kb' }));

app.use((req, res, next) => {
  if (req.path === '/health') return next();
  const token = req.headers['x-service-token'];
  if (!config.sharedSecret || token !== config.sharedSecret) {
    return res.status(401).json({ error: 'unauthorized' });
  }
  next();
});

app.get('/health', (req, res) => res.json({ ok: true, uptime: process.uptime() }));

app.post('/session/:salonId/start', async (req, res) => {
  try {
    const session = await sessionMgr.createSession(req.params.salonId);
    res.json({
      status: session.status,
      phone: session.phone,
      hasQr: !!session.qrDataUrl,
    });
  } catch (err) {
    logger.error({ err: err.message }, 'start failed');
    res.status(500).json({ error: err.message });
  }
});

app.get('/session/:salonId/status', (req, res) => {
  res.json(sessionMgr.statusOf(req.params.salonId));
});

app.get('/session/:salonId/qr', (req, res) => {
  const qr = sessionMgr.qrOf(req.params.salonId);
  if (!qr) return res.status(404).json({ error: 'no-qr' });
  res.json({ qr });
});

app.post('/session/:salonId/logout', async (req, res) => {
  await sessionMgr.logoutSession(req.params.salonId);
  res.json({ ok: true });
});

app.post('/session/:salonId/send', (req, res) => {
  const { to, message, warmupStart, dailyLimit, logId } = req.body || {};
  if (!to || !message) {
    return res.status(400).json({ error: 'to-and-message-required' });
  }
  try {
    const result = sessionMgr.queueMessage(req.params.salonId, {
      to,
      message,
      warmupStart,
      dailyLimit,
      logId,
    });
    res.status(202).json({ accepted: true, ...result });
  } catch (err) {
    logger.warn({ err: err.message, salonId: req.params.salonId }, 'queue-message failed');
    const msg = err.message || '';
    const status =
      msg === 'session-not-found' ? 409
      : msg.startsWith('session-') ? 409
      : msg.startsWith('queue-paused-') ? 423
      : msg === 'invalid-phone' ? 400
      : 500;
    res.status(status).json({ error: msg });
  }
});

app.listen(config.port, config.host, () => {
  logger.info({ port: config.port, host: config.host }, 'whatsapp-service listening');
});

process.on('unhandledRejection', (err) => logger.error({ err }, 'unhandledRejection'));
process.on('uncaughtException', (err) => logger.error({ err }, 'uncaughtException'));
