// === Salonappy Upcoming Bookings (bugun -> +12 ay) ===
// Sadece gelecek randevular icin hizli dump (booking/list + booking/detail).
// Cikti: salonappy_visits_upcoming_<ts>.json (ana visits.js dump'i ile ayni schema)
// Kullanim (sunucuda):
//   php artisan salonappy:import --dump-file=... --salon=X --only-visits --from=YYYY-MM-DD --to=YYYY-MM-DD
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';
  const DB_NAME = prompt('IndexedDB cache adi', 'sa_upcoming_v1') || 'sa_upcoming_v1';

  let TOKEN = '75501&xllbghIbb43162455EtvHvW88780133d539433fef4c03826541471';
  let X_DEVICE = 'M3B3Ii2nwZrroB1nyWvOA81pWVKQmeTE';
  const X_VERSION = '2026.06.22.1';
  TOKEN = prompt('Bearer token', TOKEN) || TOKEN;
  X_DEVICE = prompt('x-device', X_DEVICE) || X_DEVICE;
  if (!TOKEN || !X_DEVICE) { console.error('Token/device gerekli'); return; }

  const RATE = parseInt(prompt('Istek arasi gecikme (ms)', '250'), 10) || 250;
  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json, text/plain, */*',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
    'x-product': 'sappy',
    'x-version': X_VERSION,
  });
  const sleep = (ms) => new Promise(r => setTimeout(r, ms));

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
    for (let i = 0; i < 6; i++) {
      try {
        const r = await fetch(url, { headers: H() });
        if (r.status === 429) { const w = 30000 + i*15000; console.warn(`⏸ 429, ${w/1000}s bekle`); await sleep(w); continue; }
        if (!r.ok) { console.warn('FAIL', r.status, path); return null; }
        return await r.json();
      } catch (e) {
        const w = 5000 + i*5000; console.warn(`💥 ${e.message}, ${w/1000}s bekle`); await sleep(w);
      }
    }
    return null;
  };

  // Clients (musteri eslestirme)
  let clients = await dbGet('clients');
  if (!clients) {
    console.log('🔹 Clients...');
    const cj = await get('/client/list');
    clients = cj?.data?.clients || [];
    await dbPut('clients', clients);
    console.log('  clients:', clients.length);
  } else console.log('  resume clients:', clients.length);
  await sleep(RATE);

  // Upcoming bookings — bugunden 12 ay ileri, TUM status
  let bookings = await dbGet('bookings');
  if (!bookings) {
    console.log('🔹 Upcoming bookings...');
    bookings = [];
    const seen = new Set();
    const today = new Date();
    const dStart = today.toISOString().slice(0,10);
    const cutoff = new Date(today.getFullYear() + 1, today.getMonth() + 1, 0);
    const dEnd = cutoff.toISOString().slice(0,10);
    const limit = 100;
    for (const st of [1, 2, 3, 4, 5]) {
      let offset = 0, total = null;
      while (true) {
        const j = await get(`/booking/list?offset=${offset}&limit=${limit}&date_start=${dStart}&date_end=${dEnd}&status=${st}`);
        const arr = j?.data?.bookings || j?.data?.visits || [];
        if (total === null) total = parseInt(j?.data?.total_records || '0', 10);
        for (const b of arr) {
          if (!b.session && b.id) b.session = b.id;
          const sid = String(b.session ?? '');
          if (sid && !seen.has(sid)) { seen.add(sid); bookings.push(b); }
        }
        offset += limit;
        await sleep(RATE);
        if (arr.length === 0) break;
        if (total > 0 && offset >= total) break;
      }
      console.log(`  status=${st}: ${bookings.length} (total_records=${total})`);
    }
    await dbPut('bookings', bookings);
    console.log('✓ Upcoming toplam:', bookings.length);
  } else console.log('  resume bookings:', bookings.length);

  // Booking details (paralel 3'lu batch)
  let bookingDetails = (await dbGet('bookingDetails')) || {};
  const kalanlar = bookings.map(b => String(b.session ?? b.id ?? ''))
    .filter(sid => sid && !bookingDetails[sid]);
  console.log(`🔹 Details ${bookings.length - kalanlar.length}/${bookings.length}, kalan ${kalanlar.length}...`);
  const CONC = 3; let saved = 0; const t0 = Date.now();
  for (let i = 0; i < kalanlar.length; i += CONC) {
    const batch = kalanlar.slice(i, i + CONC);
    const results = await Promise.all(batch.map(sid => get(`/booking/detail?session=${sid}`).then(j => [sid, j])));
    for (const [sid, j] of results) {
      if (j?.data?.booking) { bookingDetails[sid] = j.data.booking; saved++; }
    }
    if (saved % 50 < CONC) {
      await dbPut('bookingDetails', bookingDetails);
      const el = Math.round((Date.now() - t0) / 1000);
      console.log(`  detail ${i + batch.length}/${kalanlar.length} yeni=${saved} gecen=${el}s`);
    }
    await sleep(RATE);
  }
  await dbPut('bookingDetails', bookingDetails);
  console.log('✓ Details:', Object.keys(bookingDetails).length);

  // Ana visits.js schema'sına uygun cikti — payments/visits alanları bos (upcoming'de payment yok)
  const dump = {
    generated_at: new Date().toISOString(),
    clients,
    visits: bookings, // ana importer visits[] bekliyor
    bookingDetails,
    payments: [],
  };
  const txt = JSON.stringify(dump);
  console.log('Boyut:', (txt.length/1024/1024).toFixed(2), 'MB');
  const blob = new Blob([txt], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'salonappy_visits_upcoming_' + Date.now() + '.json';
  document.body.appendChild(a); a.click(); a.remove();
  console.log('✅ Indirildi.');
})();
