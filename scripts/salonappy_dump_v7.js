// === Salonappy Full Dump v7.1 (throttle + retry + resume) ===
// Tek scriptte: master + clients + visits + booking-details
// Rate limit: 250ms gecikme + 429'da 30s bekle. Resume: IndexedDB'de en son durum.
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  // Auth (Network tabindan)
  let TOKEN = '288401&fJqmdVpa7b01e19c0KUNVnvz4644713c6e717fe0c169b82a60a47e';
  let X_DEVICE = 'deovpplHniEa2s8oE12C7phjNlxsolkP';
  let X_VERSION = '2026.05.07.1';
  TOKEN = prompt('Bearer token', TOKEN) || TOKEN;
  X_DEVICE = prompt('x-device', X_DEVICE) || X_DEVICE;

  // Rate limit ayari (varsayilan 4 req/s)
  let RATE_DELAY_MS = parseInt(prompt('Istek arasi gecikme (ms, 250 onerilir)', '250'), 10) || 250;

  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json, text/plain, */*',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
    'x-version': X_VERSION
  });
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));

  // 429-aware fetch
  const get = async (path) => {
    const url = BASE + path + (path.includes('?') ? '&' : '?') + 'timestamp=' + Math.floor(Date.now()/1000);
    for (let attempt = 0; attempt < 6; attempt++) {
      try {
        const r = await fetch(url, { headers: H() });
        if (r.status === 429) {
          const wait = 30000 + attempt * 15000;
          console.warn(`⏸ 429 rate limit, ${wait/1000}s bekle (attempt ${attempt+1}/6)`);
          await sleep(wait);
          continue;
        }
        if (!r.ok) { console.warn('FAIL', r.status, path); return null; }
        return await r.json();
      } catch(e) {
        const wait = 5000 + attempt * 5000;
        console.warn(`💥 Network err: ${e.message}, ${wait/1000}s bekle (attempt ${attempt+1}/6)`);
        await sleep(wait);
      }
    }
    console.error('🛑 6 deneme basarisiz:', path);
    return null;
  };

  // IndexedDB resume
  const RESUME_KEY = prompt('Resume DB adi ("yok" ise yeni baslat)',
    'sa_v7_resume') || 'sa_v7_resume';
  const db = await new Promise((res, rej) => {
    const r = indexedDB.open(RESUME_KEY, 1);
    r.onupgradeneeded = () => r.result.createObjectStore('kv');
    r.onsuccess = () => res(r.result);
    r.onerror = () => rej(r.error);
  });
  const dbPut = (k, v) => new Promise((res) => {
    const tx = db.transaction('kv', 'readwrite');
    tx.objectStore('kv').put(v, k);
    tx.oncomplete = res;
  });
  const dbGet = (k) => new Promise((res) => {
    const tx = db.transaction('kv', 'readonly');
    const req = tx.objectStore('kv').get(k);
    req.onsuccess = () => res(req.result);
  });

  // === Master + listeler (eger DB'de yoksa cek) ===
  console.log('🔹 1) Master + listeler...');
  let servicesMaster = await dbGet('servicesMaster');
  if (!servicesMaster) {
    const j = await get('/service/salon');
    servicesMaster = j?.data?.services || j?.data || [];
    await dbPut('servicesMaster', servicesMaster);
    await sleep(RATE_DELAY_MS);
  } else console.log('  resume: servicesMaster (cached)');

  let staffMaster = await dbGet('staffMaster');
  if (!staffMaster) {
    const j = await get('/staff/list');
    staffMaster = j?.data?.staff || j?.data?.list || j?.data || [];
    await dbPut('staffMaster', staffMaster);
    await sleep(RATE_DELAY_MS);
  } else console.log('  resume: staffMaster (cached)');

  let productsMaster = await dbGet('productsMaster');
  if (!productsMaster) {
    const j = await get('/product/list');
    productsMaster = j?.data?.products || j?.data?.list || j?.data || [];
    await dbPut('productsMaster', productsMaster);
    await sleep(RATE_DELAY_MS);
  } else console.log('  resume: productsMaster (cached)');

  let clients = await dbGet('clients');
  if (!clients) {
    const j = await get('/client/list');
    clients = j?.data?.clients || [];
    await dbPut('clients', clients);
    await sleep(RATE_DELAY_MS);
  } else console.log('  resume: clients (cached)');

  let visits = await dbGet('visits');
  if (!visits) {
    const j = await get('/visit/list');
    visits = j?.data?.visits || [];
    await dbPut('visits', visits);
    await sleep(RATE_DELAY_MS);
  } else console.log('  resume: visits (cached)');

  console.log(`  services:${servicesMaster.length||Object.keys(servicesMaster||{}).length} staff:${staffMaster.length||Object.keys(staffMaster||{}).length} products:${productsMaster.length||Object.keys(productsMaster||{}).length} clients:${clients.length} visits:${visits.length}`);

  // === Booking detayları (resume destekli) ===
  console.log('\n🔹 2) Booking detail (resume + throttle)...');
  let bookingDetails = (await dbGet('bookingDetails')) || {};
  const initialOk = Object.keys(bookingDetails).length;
  console.log(`  resume basladi: ${initialOk}/${visits.length} hazir`);

  let ok = initialOk, fail = 0, t0 = Date.now(), processed = 0;
  for (let i = 0; i < visits.length; i++) {
    const sess = visits[i].session;
    if (!sess) { fail++; continue; }
    if (bookingDetails[sess]) continue; // resume: zaten var

    const j = await get('/booking/detail?session=' + sess);
    if (j?.data?.booking) {
      bookingDetails[sess] = j.data.booking;
      ok++;
    } else { fail++; }
    processed++;

    // Throttle
    await sleep(RATE_DELAY_MS);

    if (processed % 100 === 0) {
      await dbPut('bookingDetails', bookingDetails);
      const elapsed = (Date.now() - t0) / 1000;
      const remaining = visits.length - ok;
      const eta = (remaining / (processed/elapsed)).toFixed(0);
      console.log(`  ${ok}/${visits.length}  ok=${ok} fail=${fail} bu_run=${processed}  ETA ${eta}s`);
    }
  }
  await dbPut('bookingDetails', bookingDetails);
  console.log(`✓ TAMAM: ok=${ok} fail=${fail}`);

  // === Birlestir + indir ===
  console.log('\n🔹 3) JSON birlestir + indir...');
  const dump = {
    generated_at: new Date().toISOString(),
    servicesMaster, staffMaster, productsMaster,
    clients, clientDetails: {},
    visits, bookingDetails
  };
  const txt = JSON.stringify(dump);
  console.log('  Boyut:', (txt.length/1024/1024).toFixed(2), 'MB');

  const blob = new Blob([txt], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'salonappy_v7_' + Date.now() + '.json';
  document.body.appendChild(a); a.click(); a.remove();
  console.log('✅ Indirildi: salonappy_v7_*.json');
  console.log('Not: IndexedDB "' + RESUME_KEY + '" silebilirsiniz (devTools > Application > IndexedDB)');
})();
