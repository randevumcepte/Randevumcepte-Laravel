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
import { salonBilgiGetir, musteriBilgiGetir, cagriLogGonder } from './api/laravel.js';
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
// Kısa ama GEÇERLİ yanıtlar — bunları "çok kısa" diye sessizlik sayma!
// (Eski kod t.length < 4 ile "ok", "he", "hı" gibi onayları atıyordu — müşteri
//  "onaylıyor musunuz?" sorusuna "ok" deyince sistem duymuyordu. Kritik bug.)
const GECERLI_KISA = new Set([
  'ok', 'okey', 'oki', 'he', 'hı', 'hi', 'hıhı', 'hı hı', 'ha', 'yo',
  'evet', 'olur', 'yok', 'yok yok', 'peki', 'tabi', 'hayır', 'hayir', 'iptal', 'aynen', 'tamam',
]);
function isWhisperHallucination(text) {
  if (!text) return false;
  const t = text.toLowerCase().trim().replace(/[.,!?]/g, '').trim();
  if (GECERLI_KISA.has(t)) return false;          // geçerli kısa yanıt → sessizlik DEĞİL
  if (t.length < 2) return true;                   // gerçekten boş/tek harf → sessizlik
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
  return ttsMs;
}

/**
 * Musteriyi dinle (sessizlik tespiti ile durur), STT yap, metni dondur.
 */
async function listen(client, channel, tag, sttPrompt) {
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
  console.log(`[REC ${tag}] dinleniyor (max ${MAX_RECORD_SECONDS}s, sessizlik ${MAX_SILENCE_SECONDS}s)...`);

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
  console.log(`[REC ${tag}] kayit bitti, dosya araniyor...`);

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
    const result = await stt.transcribeFile(wavPath, { prompt: sttPrompt });
    const ms = Date.now() - t0;
    console.log(`[STT ${tag}] ${ms}ms "${result.text}"`);
    // Whisper sessiz/dusuk kaliteli sese verdigi bilinen halusinasyonlar — sessizlik say
    if (isWhisperHallucination(result.text)) {
      console.log(`[STT ${tag}] halusinasyon tespit edildi, sessizlik kabul ediliyor`);
      return { text: '', ms };
    }
    return { text: result.text, ms };
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

  const startedAt = Date.now();
  log(`basladi caller=${callerNum} did=${fromDid} salon=${salonId}`);

  // Hangup bayragi — client seviyesinde dinle (channel objesi tum eventleri yaymaz)
  let hungUp = false;
  const onChannelGone = (_event, ch) => {
    if (ch && ch.id === channel.id) hungUp = true;
  };
  ari.client.on('StasisEnd', onChannelGone);
  ari.client.on('ChannelDestroyed', onChannelGone);
  ari.client.on('ChannelHangupRequest', onChannelGone);

  // ── Çağrı logu biriktirici (Faz 0: görünürlük) ──
  const turnLoglari = [];
  let sttToplam = 0, llmToplam = 0, ttsToplam = 0;
  let cagriDurum = 'tamamlandi';
  let cagriSonuc = '';
  let randevuId = null;

  // Salon adini Laravel'den cek (Mock modunda fallback'e duser)
  let salonAdi = resolveSalonAdi(salonId);
  let karsilamaTelaffuz = null;
  let hizmetler = [];
  try {
    const info = await salonBilgiGetir({ salonId });
    if (info?.ad) salonAdi = info.ad;
    if (info?.karsilama_telaffuz && String(info.karsilama_telaffuz).trim()) {
      karsilamaTelaffuz = String(info.karsilama_telaffuz).trim();
    }
    if (Array.isArray(info?.hizmetler)) hizmetler = info.hizmetler;
    log(`salon="${salonAdi}" hizmet=${hizmetler.length} karsilama=${karsilamaTelaffuz ? 'var' : 'yok'}`);
  } catch (e) {
    log(`salon bilgi cekilemedi (fallback "${salonAdi}"): ${e.message}`);
  }

  // Arayani tani (kisisellestirme + paket). Basarisiz olsa cagri devam eder.
  let musteriAdi = null;
  let paketler = [];
  try {
    const m = await musteriBilgiGetir({ salonId, telefon: callerNum });
    if (m?.ad) musteriAdi = m.ad;
    if (Array.isArray(m?.paketler)) paketler = m.paketler;
    log(`musteri="${musteriAdi || 'taninmiyor'}" paket=${paketler.length}`);
  } catch (e) {
    log(`musteri bilgi cekilemedi: ${e.message}`);
  }

  const conversation = new Conversation({
    salonId,
    salonAdi,
    callerPhone: callerNum,
    hizmetler,
    karsilamaTelaffuz,
    musteriAdi,
    paketler,
  });
  const sttPrompt = conversation.sttPrompt();

  let turn = 0;
  let firstTurn = true;
  let consecutiveSilent = 0;

  // Anlık log gönderici — çağrı boyunca defalarca çağrılır (upsert). Donmuş
  // çağrıda bile o ana kadarki döküm DB'ye yazılır, böylece nerede takıldığı görülür.
  const flushLog = (durum, sonuc) => {
    try {
      cagriLogGonder({
        salon_id: salonId,
        caller_telefon: callerNum,
        did: fromDid,
        channel_id: channel.id,
        durum: durum || cagriDurum,
        sonuc: (sonuc != null ? sonuc : cagriSonuc),
        randevu_id: randevuId,
        tur_sayisi: turn,
        toplam_sure_sn: Math.round((Date.now() - startedAt) / 1000),
        stt_ms_toplam: sttToplam,
        llm_ms_toplam: llmToplam,
        tts_ms_toplam: ttsToplam,
        turlar: turnLoglari,
      }).catch(() => {});
    } catch {}
  };

  try {
    // Karsilama telaffuzu DB'de tanimliysa LLM'i bypass et, direkt cal.
    // Sonra normal akis (listen + LLM turn) devam eder.
    if (karsilamaTelaffuz && !hungUp) {
      try {
        const ttsMs = await speak(ari.client, channel, karsilamaTelaffuz, `${callId}_greet`);
        ttsToplam += ttsMs || 0;
        turnLoglari.push({
          tur_no: 0,
          kullanici_metni: null,
          asistan_metni: karsilamaTelaffuz,
          tool_cagrilari: null,
          stt_ms: 0, llm_ms: 0, tts_ms: ttsMs || 0,
        });
        flushLog('devam', 'Karşılama çalındı, müşteri bekleniyor');
        firstTurn = false; // greeting yapildi, sonraki tur listen ile basliyor
      } catch (e) {
        if (hungUp) {
          // call dropped during greeting playback — exit gracefully
        } else {
          log(`karsilama TTS hatasi: ${e.message}`);
        }
      }
    }

    while (!hungUp && turn < MAX_TURNS) {
      turn++;
      let userText = null;
      let sttMs = 0;

      if (!firstTurn) {
        // "dinliyor" durumunu ANLIK yaz — burada donarsa (kayıt/STT takılırsa)
        // DB'de durum=dinliyor tur=N görünür, takıldığı yer belli olur.
        flushLog('dinliyor', `Tur ${turn}: müşteri dinleniyor`);
        try {
          const heard = await listen(ari.client, channel, `${callId}_${turn}`, sttPrompt);
          userText = heard.text;
          sttMs = heard.ms || 0;
          sttToplam += sttMs;
        } catch (e) {
          if (hungUp) break;
          log(`listen hatasi: ${e.message}`);
          ttsToplam += (await speak(ari.client, channel, 'Sizi duyamadım, tekrar söyler misiniz?', `${callId}_${turn}_lerr`)) || 0;
          continue;
        }
        if (!userText || userText.trim().length < 2) {
          consecutiveSilent++;
          if (consecutiveSilent >= 2) {
            log(`art arda 2 sessizlik, kapatiliyor`);
            cagriDurum = 'sessizlik';
            cagriSonuc = 'Müşteri duyulamadı';
            ttsToplam += (await speak(ari.client, channel, 'Sizi duyamadığım için kapatıyorum. İyi günler.', `${callId}_${turn}_bye`)) || 0;
            break;
          }
          ttsToplam += (await speak(ari.client, channel, 'Sizi duyamadım, tekrar söyler misiniz?', `${callId}_${turn}_re`)) || 0;
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
        ttsToplam += (await speak(ari.client, channel, 'Sistemde bir aksilik oldu. Sizi canlı operatöre bağlıyorum.', `${callId}_${turn}_llmerr`)) || 0;
        result = { reply: '', action: 'transfer', durations: {}, tools: [] };
      }
      llmToplam += result.durations?.llm || 0;
      if (conversation.sonRandevuId) randevuId = conversation.sonRandevuId;

      let replyTtsMs = 0;
      if (!hungUp && result.reply) {
        try {
          replyTtsMs = (await speak(ari.client, channel, result.reply, `${callId}_${turn}`)) || 0;
          ttsToplam += replyTtsMs;
        } catch (e) {
          if (!hungUp) log(`speak hatasi: ${e.message}`);
        }
      }

      // Tur logu
      turnLoglari.push({
        tur_no: turn,
        kullanici_metni: userText,
        asistan_metni: result.reply || '',
        tool_cagrilari: result.tools && result.tools.length ? result.tools : null,
        stt_ms: sttMs,
        llm_ms: result.durations?.llm || 0,
        tts_ms: replyTtsMs,
      });
      flushLog('devam');

      if (hungUp) break;

      if (result.action === 'transfer') {
        log(`operatore aktariliyor`);
        cagriDurum = 'transfer';
        cagriSonuc = cagriSonuc || 'Canlı operatöre aktarıldı';
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
      cagriDurum = 'max_tur';
      cagriSonuc = cagriSonuc || 'Görüşme uzadı, kapatıldı';
      try {
        ttsToplam += (await speak(ari.client, channel, 'Görüşmemiz uzun sürdü, kapatıyorum. İyi günler.', `${callId}_max`)) || 0;
      } catch {}
    }
  } finally {
    ari.client.removeListener('StasisEnd', onChannelGone);
    ari.client.removeListener('ChannelDestroyed', onChannelGone);
    ari.client.removeListener('ChannelHangupRequest', onChannelGone);

    if (!hungUp) {
      try { await channel.hangup(); } catch {}
    }

    // Sonuç durumunu sonlandır: randevu oluştuysa öncelik onda
    if (randevuId) {
      cagriDurum = 'randevu';
      cagriSonuc = cagriSonuc || `Randevu oluşturuldu (#${randevuId})`;
    }

    // Çağrı dökümünü son durumuyla yaz (fire-and-forget, akışı bloklamaz)
    flushLog(cagriDurum, cagriSonuc);

    log(`bitti turns=${turn} durum=${cagriDurum}`);
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
