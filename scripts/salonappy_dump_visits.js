// === Salonappy Visit Dump (full, IndexedDB resume) ===
// Cikti: salonappy_visits_<ts>.json
// Schema: { clients, visits, bookingDetails: {session:detail}, payments, generated_at }
// Kullanim:
//   1) Tarayicida bu scripti calistir, dump indir
//   2) sunucuda: php artisan salonappy:import --dump-file=... --salon=X --only-visits --from=YYYY-MM-DD --to=YYYY-MM-DD
// Visit'ler full cekilir; importer tarih araligini filtreyle uygular.
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';
  const DB_NAME = prompt('IndexedDB cache adi', 'sa_visits_resume3') || 'sa_visits_resume3';

  let TOKEN = '75501&xllbghIbb43162455EtvHvW88780133d539433fef4c03826541471';
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

  // ---- IndexedDB helpers (resume) ----
  const idb = () => new Promise((res, rej) => {
    const r = indexedDB.open(DB_NAME, 1);
    r.onupgradeneeded = () => r.result.createObjectStore('kv');
    r.onsuccess = () => res(r.result);
    r.onerror = () => rej(r.error);
  });
  const dbGet = async (key) => {
    const db = await idb();
    return new Promise((res) => {
      const tx = db.transaction('kv', 'readonly').objectStore('kv').get(key);
      tx.onsuccess = () => res(tx.result);
      tx.onerror = () => res(null);
    });
  };
  const dbPut = async (key, val) => {
    const db = await idb();
    return new Promise((res) => {
      const tx = db.transaction('kv', 'readwrite').objectStore('kv').put(val, key);
      tx.onsuccess = () => res(true);
      tx.onerror = () => res(false);
    });
  };

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

  // 1) Clients (musteri eslestirme icin)
  let clients = await dbGet('clients');
  if (!clients) {
    console.log('🔹 Clients cekiliyor...');
    const cj = await get('/client/list');
    clients = cj?.data?.clients || [];
    await dbPut('clients', clients);
    console.log(`  clients: ${clients.length}`);
  } else console.log(`  resume: clients (${clients.length})`);
  await sleep(RATE_DELAY_MS);

  // 2) Visit listesi — YIL BAZLI + OFFSET/LIMIT PAGINATED
  // /visit/list parametresiz cagrilirsa Salonappy default limit uyguluyor (~8800).
  // 34K+ visit'li hesaplar icin yil bazli aralik + offset/limit ile paginate lazim.
  let visits = await dbGet('visits');
  if (!visits) {
    console.log('🔹 Visit listesi cekiliyor (/visit/list — yil bazli + offset paginated)...');
    visits = [];
    const seenSess = new Set();
    const yEnd = new Date().getFullYear();
    const limit = 100;
    for (let yr = 2018; yr <= yEnd; yr++) {
      const ds = `${yr}-01-01`;
      const de = `${yr}-12-31`;
      let offset = 0;
      let yearCount = 0;
      while (true) {
        const j = await get(`/visit/list?offset=${offset}&limit=${limit}&date_start=${ds}&date_end=${de}`);
        const arr = j?.data?.visits || [];
        if (!arr.length) break;
        for (const v of arr) {
          const sid = String(v?.session ?? v?.id ?? '');
          if (sid && !seenSess.has(sid)) { seenSess.add(sid); visits.push(v); }
        }
        offset += arr.length;
        yearCount += arr.length;
        await sleep(RATE_DELAY_MS);
        if (arr.length < limit) break;
      }
      console.log(`  yil ${yr}: +${yearCount} (kumule=${visits.length})`);
      // Ara-kaydet — yarim kalirsa devam
      await dbPut('visits', visits);
    }
    console.log(`✓ Visit toplam (unique): ${visits.length}`);
    await sleep(RATE_DELAY_MS);
  } else console.log(`  resume: visits (${visits.length})`);

  // 2b) /booking/list — YIL BAZLI + TUM STATUS (1..5) + OFFSET/LIMIT PAGINATED
  // /visit/list default olarak iptal/no-show/silinmis kayitlari verebilir.
  // /booking/list tum status'leri (1=Bekleyen, 2=Onayli, 3=Iptal, 4=No-show, 5=Kapatildi)
  // paginated cekerek eksik kalanlari toplariz. session dedup ile visits ile birlesir.
  let bookingsAll = await dbGet('bookingsAll');
  if (!bookingsAll) {
    console.log('🔹 /booking/list yil+status bazli paginated cekiliyor...');
    bookingsAll = [];
    const seenB = new Set();
    const yEnd = new Date().getFullYear() + 1; // gelecek yil dahil (upcoming)
    const limit = 100;
    const statuses = [1, 2, 3, 4, 5];
    for (let yr = 2018; yr <= yEnd; yr++) {
      const ds = `${yr}-01-01`;
      const de = `${yr}-12-31`;
      let yrCount = 0;
      for (const st of statuses) {
        let offset = 0;
        while (true) {
          const j = await get(`/booking/list?offset=${offset}&limit=${limit}&date_start=${ds}&date_end=${de}&status=${st}`);
          const arr = j?.data?.bookings || j?.data?.visits || [];
          if (!arr.length) break;
          for (const b of arr) {
            if (!b.session && b.id) b.session = b.id;
            const sid = String(b.session ?? '');
            if (sid && !seenB.has(sid)) { seenB.add(sid); bookingsAll.push(b); }
          }
          offset += arr.length;
          yrCount += arr.length;
          await sleep(RATE_DELAY_MS);
          if (arr.length < limit) break;
        }
      }
      console.log(`  yil ${yr} bookings: +${yrCount} (kumule unique=${bookingsAll.length})`);
      await dbPut('bookingsAll', bookingsAll);
    }
    console.log(`✓ Bookings toplam (unique): ${bookingsAll.length}`);
  } else console.log(`  resume: bookingsAll (${bookingsAll.length})`);

  // Birlestir: visits (/visit/list) + bookingsAll (/booking/list) — session bazli dedup
  const visitsBySess = {};
  for (const v of visits) if (v?.session) visitsBySess[v.session] = v;
  let newFromBookings = 0;
  for (const b of bookingsAll) {
    if (b?.session && !visitsBySess[b.session]) {
      visitsBySess[b.session] = b;
      newFromBookings++;
    }
  }
  visits = Object.values(visitsBySess);
  console.log(`✓ Toplam session (visit+booking dedup): ${visits.length} (booking'ten ek: ${newFromBookings})`);

  // 3) Her visit icin booking detail (resume — cached olan atlanir)
  let bookingDetails = (await dbGet('bookingDetails')) || {};
  let already = Object.keys(bookingDetails).length;
  console.log(`🔹 Booking details: ${already}/${visits.length} hazir, kalanlari cekiliyor...`);
  let saved = 0;
  for (let i = 0; i < visits.length; i++) {
    const v = visits[i];
    const sid = String(v.session ?? v.id ?? v.booking_id ?? '');
    if (!sid || bookingDetails[sid]) continue;
    const j = await get(`/booking/detail?session=${sid}`);
    if (j?.data?.booking) {
      bookingDetails[sid] = j.data.booking;
      saved++;
      if (saved % 50 === 0) {
        await dbPut('bookingDetails', bookingDetails);
        console.log(`  detail ${i+1}/${visits.length} (yeni ${saved})`);
      }
    }
    await sleep(RATE_DELAY_MS);
  }
  await dbPut('bookingDetails', bookingDetails);
  console.log(`✓ Booking details toplam: ${Object.keys(bookingDetails).length}`);

  // 4) Payments (visit detayindaki payments local olabilir; dump'a global liste de eklenir — tahsilat dedup icin yedek)
  let payments = await dbGet('payments');
  if (!payments) {
    console.log('🔹 Payments cekiliyor (/payment/list)...');
    payments = [];
    let offset = 0; const limit = 100;
    while (true) {
      const j = await get(`/payment/list?offset=${offset}&limit=${limit}&date_start=2018-01-01&date_end=2099-12-31`);
      const arr = j?.data?.payments || [];
      if (!arr.length) break;
      payments.push(...arr);
      offset += arr.length;
      if (offset % 500 === 0) console.log(`  payments kumule: ${payments.length}`);
      await sleep(RATE_DELAY_MS);
      if (arr.length < limit) break;
    }
    await dbPut('payments', payments);
    console.log(`✓ Payments: ${payments.length}`);
  } else console.log(`  resume: payments (${payments.length})`);

  // 5) Dump indir
  const dump = { generated_at: new Date().toISOString(), clients, visits, bookingDetails, payments };
  const txt = JSON.stringify(dump);
  console.log(`Boyut: ${(txt.length/1024/1024).toFixed(2)} MB`);
  const blob = new Blob([txt], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'salonappy_visits_' + Date.now() + '.json';
  document.body.appendChild(a); a.click(); a.remove();
  console.log('✅ Indirildi.');
})();
