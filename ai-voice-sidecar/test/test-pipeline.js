/**
 * Uçtan uca CLI test.
 * Asterisk YOK — kullanıcı klavyeden yazar, sistem hem ekrana basar hem TTS mp3 üretir.
 *
 * Kullanım:
 *   node test/test-pipeline.js
 *
 * Çıkış: "cik" yazınca biter.
 */
import readline from 'readline/promises';
import path from 'path';
import { Conversation } from '../src/dialog/state.js';
import { tts } from '../src/tts/edge-tts.js';
import { config } from '../src/config.js';

const conv = new Conversation({
  salonId: config.testSalonId,
  salonAdi: 'Randevumcepte Test Salonu',
  callerPhone: '+905551234567',
});

console.log('═══════════════════════════════════════════════════════════');
console.log('  AI SESLİ ASİSTAN — CLI Test (Asterisk yok)');
console.log('  Salon: Randevumcepte Test Salonu | Telefon: +905551234567');
console.log('  Çıkmak için "cik" yazın.');
console.log('═══════════════════════════════════════════════════════════\n');

const rl = readline.createInterface({ input: process.stdin, output: process.stdout });

// İlk açılış: AI önce selamlasın
async function doTurn(userText, turnLabel) {
  const t0 = Date.now();
  const r = await conv.turn(userText);
  const totalMs = Date.now() - t0;

  console.log(`\n[ASİSTAN] ${r.reply}`);
  console.log(`           ⏱  toplam ${totalMs}ms  (LLM ${r.durations.llm || 0}ms${r.durations.tool ? `, tool ${r.durations.tool}ms` : ''})`);

  if (r.reply) {
    const outFile = path.join(config.outputDir, `turn-${String(turnLabel).padStart(2, '0')}.mp3`);
    const tt0 = Date.now();
    await tts.toFile(r.reply, outFile);
    console.log(`           🔊 ${outFile} (${Date.now() - tt0}ms TTS)`);
  }

  if (r.action === 'transfer') {
    console.log('\n[SİSTEM] Operatöre aktarıldı — çağrı sonu.');
    return true;
  }
  return false;
}

// Açılış (kullanıcı henüz konuşmadı)
const ended = await doTurn(null, 0);
if (ended) { rl.close(); process.exit(0); }

let turn = 1;
while (true) {
  const user = (await rl.question('\n[MÜŞTERİ] ')).trim();
  if (!user) continue;
  if (user.toLowerCase() === 'cik') break;

  const stop = await doTurn(user, turn++);
  if (stop) break;
}

rl.close();
console.log('\nÇağrı sonlandı.');
