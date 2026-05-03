/**
 * AI Sesli Asistan — orkestratör.
 *
 * Half-duplex akış (Faz 1-2):
 *   1. Cagri gelir → Stasis app'e dusurulur → AriService.onCall tetiklenir
 *   2. LLM ilk turu calisir → selamla → TTS → WAV → Asterisk sounds → channel.play()
 *   3. Loop: channel.record() (sessizlik VAD'i ile durur) → STT → conversation.turn() → TTS → play
 *   4. transfer aksiyonu → continueInDialplan(fallback context) ile eski IVR'a devret
 *   5. Hangup veya MAX_TURNS limitine kadar devam
 *
 * Faz 4 (yapilmadi): Tam external_media + RTP relay (barge-in destegi).
 */

import path from 'path';
import fs from 'fs';
import { spawn } from 'child_process';
import { webcrypto } from 'crypto';
// Node 18 uyumlulugu — msedge-tts globalThis.crypto bekliyor (Node 19+'da yerlesik)
if (!globalThis.crypto) globalThis.crypto = webcrypto;
import { AriService } from './asterisk/ari-client.js';
import { Conversation } from './dialog/state.js';
import { tts } from './tts/edge-tts.js';
import { stt } from './stt/groq-stt.js';
import { salonBilgiGetir } from './api/laravel.js';
import { config } from './config.js';

const SOUNDS_DIR = process.env.ASTERISK_SOUNDS_DIR || '/var/lib/asterisk/sounds';
const RECORDINGS_DIR = process.env.ASTERISK_RECORDINGS_DIR || '/var/spool/asterisk/recording';
const TTS_SUBDIR = process.env.ASTERISK_TTS_SUBDIR || 'ai_tts';
const TTS_DIR = path.join(SOUNDS_DIR, TTS_SUBDIR);

const MAX_RECORD_SECONDS = parseInt(process.env.MAX_RECORD_SECONDS || '15', 10);
const MAX_SILENCE_SECONDS = parseInt(process.env.MAX_SILENCE_SECONDS || '2', 10);
const MAX_TURNS = parseInt(process.env.MAX_TURNS || '12', 10);
const TRANSFER_CONTEXT = process.env.TRANSFER_CONTEXT || 'from-trunk-custom';
const TRANSFER_EXTEN = process.env.TRANSFER_EXTEN || 's';
const TRANSFER_PRIORITY = parseInt(process.env.TRANSFER_PRIORITY || '1', 10);

fs.mkdirSync(TTS_DIR, { recursive: true });

function resolveSalonAdi(salonId) {
  return process.env.SALON_ADI || `Salon ${salonId}`;
}

// Whisper kotu/sessiz seste cesitli Turkce altyazi/jenerik metinleri uydurur.
// Bunlari sessizlik kabul etmek false-positive STT'yi engeller.
const WHISPER_HALLUCINATIONS = [
  'altyazı m.k.',
  'altyazi m.k.',
  'altyazı m. k.',
  'türkçe altyazı',
  'turkce altyazi',
  'abone olmayı unutmayın',
  'iyi seyirler',
  'iyi izlemeler',
  'teşekkürler',
];
function isWhisperHallucination(text) {
  if (!text) return false;
  const t = text.toLowerCase().trim().replace(/[.,!?]/g, '').trim();
  if (t.length < 4) return true;
  return WHISPER_HALLUCINATIONS.some((h) => t === h || t.includes(h));
}

/**
 * MP3 → WAV (8kHz mono s16le) — Asterisk'in sevdigi format.
 * ffmpeg sistemde olmali (apt install ffmpeg).
 */
function convertMp3ToWav(mp3Path, wavPath) {
  return new Promise((resolve, reject) => {
    const ff = spawn('ffmpeg', [
      '-y', '-loglevel', 'error',
      '-i', mp3Path,
      '-ar', '8000', '-ac', '1', '-sample_fmt', 's16',
      wavPath,
    ]);
    let stderr = '';
    ff.stderr.on('data', (d) => { stderr += d.toString(); });
    ff.on('close', (code) => {
      if (code === 0) resolve(wavPath);
      else reject(new Error(`ffmpeg exit=${code} stderr=${stderr.slice(0, 300)}`));
    });
    ff.on('error', (e) => reject(new Error(`ffmpeg spawn fail: ${e.message}`)));
  });
}

/**
 * Metni seslendir, Asterisk channel'a cal, bitince temizle.
 */
async function speak(client, channel, text, tag) {
  if (!text || !text.trim()) return;

  const baseName = `tts_${tag}_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
  const mp3Path = path.join(TTS_DIR, `${baseName}.mp3`);
  const wavPath = path.join(TTS_DIR, `${baseName}.wav`);

  const t0 = Date.now();
  await tts.toFile(text, mp3Path);
  await convertMp3ToWav(mp3Path, wavPath);
  const ttsMs = Date.now() - t0;

  const media = `sound:${TTS_SUBDIR}/${baseName}`;
  const playback = client.Playback();

  await new Promise((resolve, reject) => {
    let done = false;
    const finish = (err) => {
      if (done) return;
      done = true;
      err ? reject(err) : resolve();
    };
    playback.once('PlaybackFinished', () => finish());
    playback.once('PlaybackFailed', (ev) => finish(new Error(ev?.playback?.cause || 'playback failed')));
    setTimeout(() => finish(), 60_000); // safety: en fazla 60sn

    channel.play({ media }, playback).catch((e) => finish(e));
  });

  console.log(`[TTS ${tag}] ${ttsMs}ms "${text.slice(0, 80)}${text.length > 80 ? '…' : ''}"`);

  // Cleanup — playback bittikten sonra
  for (const p of [mp3Path, wavPath]) {
    try { fs.unlinkSync(p); } catch {}
  }
}

/**
 * Musteriyi dinle (sessizlik tespiti ile durur), STT yap, metni dondur.
 */
async function listen(client, channel, tag) {
  const recName = `rec_${tag}_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`;
  const format = 'wav';

  // ari-client v2: channel.record() Promise olarak LiveRecording dondurur.
  // Asterisk bizim name'imizi degil kendi UUID'sini kullanabilir; gercek
  // ismi liveRecording.name'den oku.
  const liveRecording = await channel.record({
    name: recName,
    format,
    maxDurationSeconds: MAX_RECORD_SECONDS,
    maxSilenceSeconds: MAX_SILENCE_SECONDS,
    ifExists: 'overwrite',
    beep: false,
    terminateOn: '#',
  });

  const actualName = liveRecording?.name || recName;

  const finished = new Promise((resolve, reject) => {
    let done = false;
    const finish = (err) => {
      if (done) return;
      done = true;
      err ? reject(err) : resolve();
    };
    liveRecording.once('RecordingFinished', () => finish());
    liveRecording.once('RecordingFailed', (ev) => finish(new Error(ev?.recording?.cause || 'recording failed')));
    setTimeout(() => finish(), (MAX_RECORD_SECONDS + 5) * 1000);
  });

  await finished;

  // Asterisk dosyayi {RECORDINGS_DIR}/{name}.{format} olarak yazar.
  // Bazen extension yok (UUID isimde) — ikisini de dene.
  const candidates = [
    path.join(RECORDINGS_DIR, `${actualName}.${format}`),
    path.join(RECORDINGS_DIR, actualName),
  ];
  const wavPath = candidates.find((p) => fs.existsSync(p));
  if (!wavPath) {
    throw new Error(`Kayit dosyasi bulunamadi (denenen: ${candidates.join(', ')})`);
  }

  try {
    const t0 = Date.now();
    const result = await stt.transcribeFile(wavPath);
    console.log(`[STT ${tag}] ${Date.now() - t0}ms "${result.text}"`);
    // Whisper sessiz/dusuk kaliteli sese verdigi bilinen halusinasyonlar — sessizlik say
    if (isWhisperHallucination(result.text)) {
      console.log(`[STT ${tag}] halusinasyon tespit edildi, sessizlik kabul ediliyor`);
      return '';
    }
    return result.text;
  } finally {
    try { fs.unlinkSync(wavPath); } catch {}
  }
}

/**
 * Tek bir cagrinin tum yasam dongusu.
 */
async function handleCall(ctx, ari) {
  const { channel, callerNum, fromDid, salonId } = ctx;
  const callId = channel.id.slice(-8);
  const log = (msg) => console.log(`[CALL ${callId}] ${msg}`);

  log(`basladi caller=${callerNum} did=${fromDid} salon=${salonId}`);

  // Hangup bayragi — client seviyesinde dinle (channel objesi tum eventleri yaymaz)
  let hungUp = false;
  const onChannelGone = (_event, ch) => {
    if (ch && ch.id === channel.id) hungUp = true;
  };
  ari.client.on('StasisEnd', onChannelGone);
  ari.client.on('ChannelDestroyed', onChannelGone);
  ari.client.on('ChannelHangupRequest', onChannelGone);

  // Salon adini Laravel'den cek (Mock modunda fallback'e duser)
  let salonAdi = resolveSalonAdi(salonId);
  let hizmetler = [];
  try {
    const info = await salonBilgiGetir({ salonId });
    if (info?.ad) salonAdi = info.ad;
    if (Array.isArray(info?.hizmetler)) hizmetler = info.hizmetler;
    log(`salon="${salonAdi}" hizmet=${hizmetler.length}`);
  } catch (e) {
    log(`salon bilgi cekilemedi (fallback "${salonAdi}"): ${e.message}`);
  }

  const conversation = new Conversation({
    salonId,
    salonAdi,
    callerPhone: callerNum,
    hizmetler,
  });

  let turn = 0;
  let firstTurn = true;
  let consecutiveSilent = 0;

  try {
    while (!hungUp && turn < MAX_TURNS) {
      turn++;
      let userText = null;

      if (!firstTurn) {
        try {
          userText = await listen(ari.client, channel, `${callId}_${turn}`);
        } catch (e) {
          if (hungUp) break;
          log(`listen hatasi: ${e.message}`);
          await speak(ari.client, channel, 'Sizi duyamadım, tekrar söyler misiniz?', `${callId}_${turn}_lerr`);
          continue;
        }
        if (!userText || userText.trim().length < 2) {
          consecutiveSilent++;
          if (consecutiveSilent >= 2) {
            log(`art arda 2 sessizlik, kapatiliyor`);
            await speak(ari.client, channel, 'Sizi duyamadığım için kapatıyorum. İyi günler.', `${callId}_${turn}_bye`);
            break;
          }
          await speak(ari.client, channel, 'Sizi duyamadım, tekrar söyler misiniz?', `${callId}_${turn}_re`);
          continue;
        }
        consecutiveSilent = 0;
      }
      firstTurn = false;

      let result;
      try {
        result = await conversation.turn(userText);
      } catch (e) {
        log(`LLM hatasi: ${e.message}`);
        await speak(ari.client, channel, 'Sistemde bir aksilik oldu. Sizi canlı operatöre bağlıyorum.', `${callId}_${turn}_llmerr`);
        result = { reply: '', action: 'transfer' };
      }

      if (hungUp) break;

      if (result.reply) {
        try {
          await speak(ari.client, channel, result.reply, `${callId}_${turn}`);
        } catch (e) {
          if (hungUp) break;
          log(`speak hatasi: ${e.message}`);
        }
      }

      if (result.action === 'transfer') {
        log(`operatore aktariliyor`);
        try {
          await channel.continueInDialplan({
            context: TRANSFER_CONTEXT,
            extension: TRANSFER_EXTEN,
            priority: TRANSFER_PRIORITY,
          });
        } catch (e) {
          log(`continueInDialplan hatasi: ${e.message} → hangup`);
          try { await channel.hangup(); } catch {}
        }
        break;
      }
    }

    if (turn >= MAX_TURNS && !hungUp) {
      log(`MAX_TURNS asildi, kapatiliyor`);
      try {
        await speak(ari.client, channel, 'Görüşmemiz uzun sürdü, kapatıyorum. İyi günler.', `${callId}_max`);
      } catch {}
    }
  } finally {
    ari.client.removeListener('StasisEnd', onChannelGone);
    ari.client.removeListener('ChannelDestroyed', onChannelGone);
    ari.client.removeListener('ChannelHangupRequest', onChannelGone);

    if (!hungUp) {
      try { await channel.hangup(); } catch {}
    }
    log(`bitti turns=${turn}`);
  }
}

/* ───────── Bootstrap ───────── */

console.log('═══════════════════════════════════════════════════════');
console.log('  AI Sesli Asistan — Sidecar');
console.log('═══════════════════════════════════════════════════════');
console.log(`  ARI:        ${config.asterisk.url} (user=${config.asterisk.ariUser}, app=${config.asterisk.stasisApp})`);
console.log(`  Sounds:     ${TTS_DIR}`);
console.log(`  Recordings: ${RECORDINGS_DIR}`);
console.log(`  TTS voice:  ${config.tts.voice}`);
console.log(`  LLM model:  ${config.groq.llmModel}`);
console.log(`  STT model:  ${config.groq.sttModel}`);
console.log(`  Laravel:    ${config.laravel.base} (${config.laravel.token ? 'token=set' : 'MOCK MODE'})`);
console.log(`  Transfer:   ${TRANSFER_CONTEXT},${TRANSFER_EXTEN},${TRANSFER_PRIORITY}`);
console.log('───────────────────────────────────────────────────────');

let ari;
ari = new AriService({
  onCall: async (ctx) => {
    try {
      await handleCall(ctx, ari);
    } catch (e) {
      console.error(`[CALL ${ctx.channel.id.slice(-8)}] beklenmedik hata:`, e);
      try { await ctx.channel.hangup(); } catch {}
    }
  },
});

try {
  await ari.connect();
  console.log('✓ Hazir, cagri bekleniyor.');
} catch (e) {
  console.error('✗ ARI baglantisi basarisiz:', e?.message || e);
  process.exit(1);
}

const shutdown = async () => {
  console.log('\n[SERVER] Kapatiliyor...');
  try { await ari.disconnect(); } catch {}
  process.exit(0);
};
process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
