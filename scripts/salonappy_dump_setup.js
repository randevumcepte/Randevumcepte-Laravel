// === Salonappy Kurulum Dump (services + staffs + products + clients) ===
// Sadece master kayıtları çeker — visit/booking/payment YOK. ~10 saniye.
// NOT: Cihazlar Salonappy'de ayrı tablo değil; staff listesinde 'type' alanıyla
// belirleniyor (personel/yonetici/cihaz vb.). Importer staff.type'a göre ayırır.
// Kullanım: Salonappy giriş → F12 → Console → bu scripti yapıştır → Enter.
// Çıktı: salonappy_setup_<ts>.json (Downloads klasörüne iner)
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  // Auth (Network tabından)
  let TOKEN = prompt('Bearer token (Authorization header)') || '';
  let X_DEVICE = prompt('x-device header') || '';
  if (!TOKEN || !X_DEVICE) { console.error('Token/x-device zorunlu.'); return; }
  const X_VERSION = '2026.06.22.1';

  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
    'x-product': 'sappy',
    'x-version': X_VERSION,
  });
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));
  const RATE = 250;

  const get = async (path) => {
    const url = BASE + path + (path.includes('?') ? '&' : '?') + 'timestamp=' + Math.floor(Date.now()/1000);
    for (let i = 0; i < 6; i++) {
      try {
        const r = await fetch(url, { headers: H() });
        if (r.status === 429) { await sleep(30000 + i*15000); continue; }
        if (r.status === 404) { console.warn('  404', path); return null; }
        if (!r.ok) { console.warn('  FAIL', r.status, path); return null; }
        return await r.json();
      } catch(e) { await sleep(5000 + i*5000); }
    }
    return null;
  };

  console.log('🔹 Master listeler çekiliyor...');

  console.log('  1/4 services...');
  const sj = await get('/service/salon');
  const services = sj?.data?.services || sj?.data || [];
  console.log('     ', services.length, 'hizmet');
  await sleep(RATE);

  console.log('  2/4 staffs (personel + cihaz tipler)...');
  const stj = await get('/staff/list');
  const staffs = stj?.data?.staff || stj?.data?.list || stj?.data || [];
  console.log('     ', staffs.length, 'personel');
  await sleep(RATE);

  console.log('  3/4 products...');
  const pj = await get('/product/list');
  const products = pj?.data?.products || pj?.data?.list || pj?.data || [];
  console.log('     ', products.length, 'ürün');
  await sleep(RATE);

  console.log('  4/4 clients...');
  const cj = await get('/client/list');
  const clients = cj?.data?.clients || cj?.data || [];
  console.log('     ', clients.length, 'müşteri');
  await sleep(RATE);

  // Cihazlar Salonappy'de ayrı entity DEĞİL — staff.type='cihaz' (vs.) olarak
  // staffs[] içinde gelir. Importer staff.type bazlı ayrım yapar.
  const out = {
    generated_at: new Date().toISOString(),
    services, staffs, products, clients,
  };
  const blob = new Blob([JSON.stringify(out, null, 2)], { type: 'application/json' });
  const ts = Date.now();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `salonappy_setup_${ts}.json`;
  document.body.appendChild(a); a.click(); a.remove();

  console.log('✅ Bitti. Indirilen dosya: salonappy_setup_' + ts + '.json');
  console.log('   Boyut yaklaşık', Math.round(blob.size/1024), 'KB');
})();
