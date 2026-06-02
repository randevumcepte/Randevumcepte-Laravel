// === Salonappy Full Dump v7.1 (throttle + retry + resume) ===
// Tek scriptte: master + clients + visits + booking-details
// Rate limit: 250ms gecikme + 429'da 30s bekle. Resume: IndexedDB'de en son durum.
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  // Auth (Network tabindan)
  let TOKEN = '288401&Oc1lPAy62641ff1camqsiK7919e9107f826b22f39ede49c6ff4eaa';
  let X_DEVICE = '1tevuO7938R1ggFPtQZVerlqY2GfIBJK';
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

  // === Gelecek randevular: /booking/list (visit/list bunlari dondurmuyor) ===
  // status=2 onayli; tarih araligi bugun → 1 yil ileri; pagination ile tamamini cek.
  let upcomingVisits = await dbGet('upcomingVisits');
  if (!upcomingVisits) {
    upcomingVisits = [];
    const today = new Date(); const toDt = new Date(); toDt.setFullYear(toDt.getFullYear() + 1);
    const fmt = d => d.toISOString().slice(0,10);
    const dateStart = fmt(today), dateEnd = fmt(toDt);
    // Tum statuslar: 1=beklemede, 2=onayli, 3=iptal vs. UI'da hangi statuslar gerekiyorsa.
    // Pratik: 1 ve 2'yi cek (iptal ve gecmis durumlar dahil olmasin).
    for (const st of [1, 2]) {
      let offset = 0; const limit = 100;
      while (true) {
        const j = await get(`/booking/list?offset=${offset}&limit=${limit}&date_start=${dateStart}&date_end=${dateEnd}&status=${st}`);
        const arr = j?.data?.bookings || j?.data?.visits || j?.data?.list || j?.data || [];
        if (!arr.length) break;
        // /booking/list response sema'sini visit/list ile uyumlu hale getir
        for (const b of arr) {
          if (!b.session && b.id) b.session = b.id;
          upcomingVisits.push(b);
        }
        offset += arr.length;
        await sleep(RATE_DELAY_MS);
        if (arr.length < limit) break;
      }
      console.log(`  upcoming status=${st}: kumule=${upcomingVisits.length}`);
    }
    await dbPut('upcomingVisits', upcomingVisits);
  } else console.log('  resume: upcomingVisits (cached, ' + upcomingVisits.length + ')');

  // Visit'leri birlestir (session bazli dedup — ayni id 2 kez gelmesin)
  const visitsBySess = {};
  for (const v of visits) if (v?.session) visitsBySess[v.session] = v;
  for (const v of upcomingVisits) if (v?.session && !visitsBySess[v.session]) visitsBySess[v.session] = v;
  visits = Object.values(visitsBySess);

  // === Standalone urun satislari (visit'siz, manuel kasa) ===
  // /api/product_sale/list hem visit-bagli hem standalone hepsini doner.
  // Import tarafinda is_session=false olanlar standalone olarak islenir.
  let productSales = await dbGet('productSales');
  if (!productSales) {
    productSales = [];
    // Genis tarih araligi: 2023 -> +1 yil ileri
    const dateStart = '2023-01-01';
    const dEnd = new Date(); dEnd.setFullYear(dEnd.getFullYear() + 1);
    const dateEnd = dEnd.toISOString().slice(0,10);
    let offset = 0; const limit = 100;
    while (true) {
      const j = await get(`/product_sale/list?offset=${offset}&limit=${limit}&date_start=${dateStart}&date_end=${dateEnd}&is_deleted=0`);
      const arr = j?.data?.product_sales || [];
      if (!arr.length) break;
      productSales.push(...arr);
      offset += arr.length;
      await sleep(RATE_DELAY_MS);
      if (arr.length < limit) break;
      if (offset % 500 === 0) console.log(`  product_sales kumule: ${productSales.length}`);
    }
    await dbPut('productSales', productSales);
    console.log(`  product_sales toplam: ${productSales.length}`);
  } else console.log('  resume: productSales (cached, ' + productSales.length + ')');

  console.log(`  services:${servicesMaster.length||Object.keys(servicesMaster||{}).length} staff:${staffMaster.length||Object.keys(staffMaster||{}).length} products:${productsMaster.length||Object.keys(productsMaster||{}).length} clients:${clients.length} visits:${visits.length} (gecmis+gelecek)`);

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
    visits, bookingDetails,
    productSales
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
