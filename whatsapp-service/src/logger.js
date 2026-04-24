'use strict';

const pino = require('pino');
const config = require('./config');

const logger = pino({
  level: config.logLevel,
  transport: process.stdout.isTTY
    ? { target: 'pino-pretty', options: { translateTime: 'SYS:standard' } }
    : undefined,
});

module.exports = logger;
