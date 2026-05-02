/**
 * ARI bağlantı testi.
 *
 * 1. Asterisk'e bağlanır
 * 2. randevu_ai Stasis app'ini başlatır
 * 3. Çağrı bekler — gerçek bir telefon araması olduğunda log basar
 *
 * Kullanım:
 *   node test/test-ari.js
 *
 * Test çağrı (başka terminalde):
 *   asterisk -rx 'channel originate Local/9999@from-internal application Stasis randevu_ai'
 */
import { AriService } from '../src/asterisk/ari-client.js';
import { config } from '../src/config.js';

console.log('═══════════════════════════════════════════════════════');
console.log('  ARI Baglanti Testi');
console.log('═══════════════════════════════════════════════════════');
console.log(`  Host:      ${config.asterisk.host}:${config.asterisk.ariPort}`);
console.log(`  User:      ${config.asterisk.ariUser}`);
console.log(`  Stasis:    ${config.asterisk.stasisApp}`);
console.log(`  Salon ID:  ${config.testSalonId} (DID eslesmesi yoksa)`);
console.log('───────────────────────────────────────────────────────');

const ari = new AriService({
  onCall: async (ctx) => {
    console.log(`\n📞 YENI CAGRI`);
    console.log(`   Caller:  ${ctx.callerNum}`);
    console.log(`   DID:     ${ctx.fromDid}`);
    console.log(`   Salon:   ${ctx.salonId}`);
    console.log(`   Channel: ${ctx.channel.id}`);

    // Şimdilik sadece bir test anonsu çal ve 5 saniye sonra kapat
    // (Gerçek AI diyaloğu Faz 2'nin sonraki adımında: External Media + RTP)
    try {
      console.log(`   → Test anonsu çalınıyor...`);
      await ctx.channel.play({ media: 'sound:hello-world' });
      console.log(`   → 5 saniye bekleniyor...`);
      await new Promise((r) => setTimeout(r, 5000));
      console.log(`   → Cagri sonlandiriliyor`);
      await ctx.channel.hangup();
    } catch (e) {
      console.error(`   ✗ Cagri isleme hatasi:`, e.message);
    }
  },
});

try {
  await ari.connect();
  console.log('\n✓ Baglanti OK. Cagri bekleniyor (Ctrl+C ile cik).');
} catch (e) {
  console.error('\n✗ Baglanti basarisiz');
  console.error('   message:', e?.message || '(bos)');
  console.error('   code:   ', e?.code || '(yok)');
  console.error('   name:   ', e?.name || '(yok)');
  console.error('   errno:  ', e?.errno || '(yok)');
  console.error('   syscall:', e?.syscall || '(yok)');
  console.error('   address:', e?.address || '(yok)');
  console.error('   port:   ', e?.port || '(yok)');
  if (e?.response) {
    console.error('   HTTP status:', e.response.statusCode || e.response.status);
    console.error('   HTTP body:  ', String(e.response.body || '').slice(0, 300));
  }
  if (e?.stack) console.error('   stack:\n', e.stack.split('\n').slice(0, 6).join('\n'));
  try {
    const flat = JSON.stringify(e, Object.getOwnPropertyNames(e));
    if (flat && flat !== '{}') console.error('   JSON:  ', flat.slice(0, 500));
  } catch {}
  process.exit(1);
}

// SIGINT/SIGTERM ile temiz kapat
const shutdown = async () => {
  console.log('\n[ARI] Kapatiliyor...');
  await ari.disconnect();
  process.exit(0);
};
process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
