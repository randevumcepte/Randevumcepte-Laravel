// === Salonappy Paket Satislari Dump (sadece package_sales) ===
// Cikti: salonappy_package_sales_<ts>.json
// Schema: { packageSales: [...], clients: [...], generated_at }
// Kullanim: php artisan salonappy:import --dump-file=... --salon=X --only-package-sales
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  let TOKEN = '75501&zCVxnWZ8d77af53a846JNoDidc6909016d90b700365d69665815a9';
  let X_DEVICE = 'M3B3Ii2nwZrroB1nyWvOA81pWVKQmeTE';
  let X_VERSION = '2026.05.07.1';
  TOKEN = prompt('Bearer token', TOKEN) || TOKEN;
  X_DEVICE = prompt('x-device', X_DEVICE) || X_DEVICE;
  if (!TOKEN || !X_DEVICE) { console.error('Token/device gerekli'); return; }

  const RATE_DELAY_MS = parseInt(prompt('Istek arasi gecikme (ms)', '250'), 10) || 250;
  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json, text/plain, */*',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
    'x-version': X_VERSION
  });
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));

  const get = async (path) => {
    const url = BASE + path + (path.includes('?') ? '&' : '?') + 'timestamp=' + Math.floor(Date.now()/1000);
    for (let attempt = 0; attempt < 6; attempt++) {
      try {
        const r = await fetch(url, { headers: H() });
        if (r.status === 429) { const wait = 30000 + attempt * 15000; console.warn(`⏸ 429, ${wait/1000}s bekle`); await sleep(wait); continue; }
        if (!r.ok) { console.warn('FAIL', r.status, path); return null; }
        return await r.json();
      } catch(e) {
        const wait = 5000 + attempt * 5000;
        console.warn(`💥 ${e.message}, ${wait/1000}s bekle`); await sleep(wait);
      }
    }
    return null;
  };

  // 1) Tum clients (musteri eslestirme icin)
  console.log('🔹 Clients cekiliyor...');
  const cj = await get('/client/list');
  const clients = cj?.data?.clients || [];
  console.log(`  clients: ${clients.length}`);
  await sleep(RATE_DELAY_MS);

  // 2) Paket satislari pagination
  console.log('🔹 Paket satislari cekiliyor (/package_sale/list_v2)...');
  const dateStart = '2018-01-01';
  const dEnd = new Date(); dEnd.setFullYear(dEnd.getFullYear() + 1);
  const dateEnd = dEnd.toISOString().slice(0,10);
  const packageSales = [];
  let offset = 0; const limit = 100;
  while (true) {
    const j = await get(`/package_sale/list_v2?offset=${offset}&limit=${limit}&date_start=${dateStart}&date_end=${dateEnd}&is_deleted=0`);
    const arr = j?.data?.package_sales || [];
    if (!arr.length) break;
    packageSales.push(...arr);
    offset += arr.length;
    if (offset % 500 === 0) console.log(`  kumule: ${packageSales.length}`);
    await sleep(RATE_DELAY_MS);
    if (arr.length < limit) break;
  }
  console.log(`✓ Toplam paket satisi satir: ${packageSales.length}`);

  // 3) Payments (payment_method_text icin parser eslestirir: client_name+date+amount match)
  console.log('🔹 Payments cekiliyor (/payment/list, payment_method icin)...');
  const payments = [];
  offset = 0;
  while (true) {
    const j = await get(`/payment/list?offset=${offset}&limit=${limit}&date_start=${dateStart}&date_end=${dateEnd}`);
    const arr = j?.data?.payments || [];
    if (!arr.length) break;
    payments.push(...arr);
    offset += arr.length;
    if (offset % 500 === 0) console.log(`  payments kumule: ${payments.length}`);
    await sleep(RATE_DELAY_MS);
    if (arr.length < limit) break;
  }
  console.log(`✓ Toplam payment: ${payments.length}`);

  // 4) Dump indir
  const dump = { generated_at: new Date().toISOString(), clients, packageSales, payments };
  const txt = JSON.stringify(dump);
  console.log(`Boyut: ${(txt.length/1024/1024).toFixed(2)} MB`);
  const blob = new Blob([txt], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'salonappy_package_sales_' + Date.now() + '.json';
  document.body.appendChild(a); a.click(); a.remove();
  console.log('✅ Indirildi.');
})();
