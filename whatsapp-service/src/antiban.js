'use strict';

const config = require('./config');

const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

const gaussianJitter = (min, max) => {
  const u1 = Math.random();
  const u2 = Math.random();
  const z = Math.sqrt(-2 * Math.log(u1)) * Math.cos(2 * Math.PI * u2);
  const mid = (min + max) / 2;
  const spread = (max - min) / 4;
  const val = mid + z * spread;
  return Math.min(max, Math.max(min, Math.round(val)));
};

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const daysSince = (iso) => {
  if (!iso) return null;
  const then = new Date(iso).getTime();
  if (isNaN(then)) return null;
  return Math.floor((Date.now() - then) / (1000 * 60 * 60 * 24));
};

const dailyCap = (warmupStart, configuredLimit) => {
  const days = daysSince(warmupStart);
  if (days === null) return configuredLimit;
  const { warmupDays, warmupDayLimits } = config.antiban;
  if (days >= warmupDays) return configuredLimit;
  const warmupCap = warmupDayLimits[Math.min(days, warmupDayLimits.length - 1)] || 15;
  return Math.min(configuredLimit, warmupCap);
};

const withinBusinessHours = () => {
  const hour = new Date().getHours();
  return hour >= config.antiban.businessHourStart && hour < config.antiban.businessHourEnd;
};

class SendQueue {
  constructor(salonId, sender, callbacks = {}) {
    this.salonId = salonId;
    this.sender = sender;
    this.onSent = callbacks.onSent;
    this.onFailed = callbacks.onFailed;
    this.onHealthAlarm = callbacks.onHealthAlarm;
    this.queue = [];
    this.processing = false;
    this.sentInBatch = 0;
    this.dailySent = 0;
    this.dailyDate = new Date().toDateString();
    this.consecutiveFailures = 0;
    this.failureHistory = [];
    this.paused = false;
  }

  pause(reason) {
    this.paused = true;
    this.pauseReason = reason;
  }

  _resetDailyIfNeeded() {
    const today = new Date().toDateString();
    if (today !== this.dailyDate) {
      this.dailyDate = today;
      this.dailySent = 0;
    }
  }

  _recordFailure(err) {
    const permanentErrors = ['invalid-phone', 'not-on-whatsapp', 'outside-business-hours', 'daily-cap-reached'];
    if (permanentErrors.includes(err.message)) return;

    this.consecutiveFailures++;
    const now = Date.now();
    this.failureHistory.push(now);
    const windowMs = config.antiban.failureWindowMinutes * 60 * 1000;
    this.failureHistory = this.failureHistory.filter((t) => now - t <= windowMs);

    const hitConsecutive = this.consecutiveFailures >= config.antiban.consecutiveFailureThreshold;
    const hitWindow = this.failureHistory.length >= config.antiban.failureWindowMax;

    if (hitConsecutive || hitWindow) {
      const reason = hitConsecutive
        ? `consecutive-failures-${this.consecutiveFailures}`
        : `window-failures-${this.failureHistory.length}/${config.antiban.failureWindowMinutes}min`;
      this.pause(reason);
      if (this.onHealthAlarm) {
        this.onHealthAlarm({ salonId: this.salonId, reason, lastError: err.message });
      }
    }
  }

  _recordSuccess() {
    this.consecutiveFailures = 0;
  }

  enqueue(job) {
    return new Promise((resolve, reject) => {
      if (this.paused) {
        return reject(new Error(`queue-paused-${this.pauseReason || 'health'}`));
      }
      this.queue.push({ job, resolve, reject });
      this._process();
    });
  }

  async _process() {
    if (this.processing) return;
    this.processing = true;
    try {
      while (this.queue.length > 0 && !this.paused) {
        this._resetDailyIfNeeded();
        const item = this.queue.shift();
        const { job, resolve, reject } = item;

        const cap = dailyCap(job.warmupStart, job.dailyLimit);
        if (this.dailySent >= cap) {
          reject(new Error(`daily-cap-reached (${this.dailySent}/${cap})`));
          continue;
        }

        if (!withinBusinessHours()) {
          reject(new Error('outside-business-hours'));
          continue;
        }

        if (this.sentInBatch >= config.antiban.batchSize) {
          await sleep(config.antiban.batchPauseMs);
          this.sentInBatch = 0;
        }

        const preDelay = gaussianJitter(config.antiban.msgMinDelayMs, config.antiban.msgMaxDelayMs);
        await sleep(preDelay);

        try {
          const result = await this.sender(job);
          this._recordSuccess();
          this.sentInBatch++;
          this.dailySent++;
          if (this.onSent) this.onSent({ salonId: this.salonId, job, result });
          resolve(result);
        } catch (err) {
          this._recordFailure(err);
          if (this.onFailed) this.onFailed({ salonId: this.salonId, job, error: err });
          reject(err);
          if (this.paused) {
            while (this.queue.length > 0) {
              const pending = this.queue.shift();
              if (this.onFailed) {
                this.onFailed({ salonId: this.salonId, job: pending.job, error: new Error(`queue-paused-${this.pauseReason}`) });
              }
              pending.reject(new Error(`queue-paused-${this.pauseReason}`));
            }
            break;
          }
        }
      }
    } finally {
      this.processing = false;
    }
  }
}

module.exports = {
  randInt,
  gaussianJitter,
  sleep,
  dailyCap,
  withinBusinessHours,
  SendQueue,
};
