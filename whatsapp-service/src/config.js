'use strict';

require('dotenv').config?.();

const parseIntList = (v, fallback) =>
  (v || fallback).split(',').map((x) => parseInt(x.trim(), 10)).filter((n) => !isNaN(n));

module.exports = {
  port: parseInt(process.env.PORT || '3001', 10),
  host: process.env.HOST || '127.0.0.1',
  sharedSecret: process.env.SHARED_SECRET || '',
  laravel: {
    webhookUrl: process.env.LARAVEL_WEBHOOK_URL || '',
    webhookSecret: process.env.LARAVEL_WEBHOOK_SECRET || '',
  },
  logLevel: process.env.LOG_LEVEL || 'info',
  antiban: {
    msgMinDelayMs: parseInt(process.env.MSG_MIN_DELAY_MS || '60000', 10),
    msgMaxDelayMs: parseInt(process.env.MSG_MAX_DELAY_MS || '120000', 10),
    typingMinMs: parseInt(process.env.TYPING_MIN_MS || '2000', 10),
    typingMaxMs: parseInt(process.env.TYPING_MAX_MS || '4000', 10),
    batchSize: parseInt(process.env.BATCH_SIZE || '50', 10),
    batchPauseMs: parseInt(process.env.BATCH_PAUSE_MS || '600000', 10),
    warmupDays: parseInt(process.env.WARMUP_DAYS || '7', 10),
    warmupDayLimits: parseIntList(process.env.WARMUP_DAY_LIMITS, '15,30,50,80,110,140,180'),
    businessHourStart: parseInt(process.env.BUSINESS_HOUR_START || '9', 10),
    businessHourEnd: parseInt(process.env.BUSINESS_HOUR_END || '21', 10),
    consecutiveFailureThreshold: parseInt(process.env.CONSECUTIVE_FAILURE_THRESHOLD || '3', 10),
    failureWindowMinutes: parseInt(process.env.FAILURE_WINDOW_MINUTES || '30', 10),
    failureWindowMax: parseInt(process.env.FAILURE_WINDOW_MAX || '5', 10),
  },
  sessionsDir: require('path').resolve(__dirname, '..', 'sessions'),
};
