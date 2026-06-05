// === Salonappy Kurulum Dump (services + staffs + products + clients + devices) ===
// Sadece master kayıtları çeker — visit/booking/payment YOK. ~10 saniye.
// Kullanım: Salonappy giriş → F12 → Console → bu scripti yapıştır → Enter.
// Çıktı: salonappy_setup_<ts>.json (Downloads klasörüne iner)
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  // Auth (Network tabından)
  let TOKEN = prompt('Bearer token (Authorization header)') || '';
  let X_DEVICE = prompt('x-device header') || '';
  if (!TOKEN || !X_DEVICE) { console.error('Token/x-device zorunlu.'); return; }
  const X_VERSION = '2026.05.07.1';

  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
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

  console.log('  1/5 services...');
  const sj = await get('/service/salon');
  const services = sj?.data?.services || sj?.data || [];
  console.log('     ', services.length, 'hizmet');
  await sleep(RATE);

  console.log('  2/5 staffs...');
  const stj = await get('/staff/list');
  const staffs = stj?.data?.staff || stj?.data?.list || stj?.data || [];
  console.log('     ', staffs.length, 'personel');
  await sleep(RATE);

  console.log('  3/5 products...');
  const pj = await get('/product/list');
  const products = pj?.data?.products || pj?.data?.list || pj?.data || [];
  console.log('     ', products.length, 'ürün');
  await sleep(RATE);

  console.log('  4/5 clients...');
  const cj = await get('/client/list');
  const clients = cj?.data?.clients || cj?.data || [];
  console.log('     ', clients.length, 'müşteri');
  await sleep(RATE);

  // 5) Cihazlar — birkaç olası endpoint dene
  console.log('  5/5 devices (cihazlar)...');
  let devices = [];
  for (const path of ['/device/list', '/devices', '/salon/device', '/salon/devices', '/equipment/list']) {
    const dj = await get(path);
    const arr = dj?.data?.devices || dj?.data?.list || dj?.data || [];
    if (Array.isArray(arr) && arr.length) {
      devices = arr;
      console.log('      endpoint:', path, '→', arr.length, 'cihaz');
      break;
    }
    await sleep(RATE);
  }
  if (!devices.length) console.log('      cihaz endpoint bulunamadı, manuel ekleyin');

  const out = {
    generated_at: new Date().toISOString(),
    services, staffs, products, clients, devices,
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
