// === Salonappy Visit Dump (full, IndexedDB resume) ===
// Cikti: salonappy_visits_<ts>.json
// Schema: { clients, visits, bookingDetails: {session:detail}, payments, generated_at }
// Kullanim:
//   1) Tarayicida bu scripti calistir, dump indir
//   2) sunucuda: php artisan salonappy:import --dump-file=... --salon=X --only-visits --from=YYYY-MM-DD --to=YYYY-MM-DD
// Visit'ler full cekilir; importer tarih araligini filtreyle uygular.
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';
  const DB_NAME = prompt('IndexedDB cache adi', 'sa_visits_monthly_v1') || 'sa_visits_monthly_v1';

  let TOKEN = '75501&nnFltNE201e7c624c7qCrVC032551e7cb98409cd57ae5f3b1e47e9';
  let X_DEVICE = 'M3B3Ii2nwZrroB1nyWvOA81pWVKQmeTE';
  let X_VERSION = '2026.06.22.1';
  TOKEN = prompt('Bearer token', TOKEN) || TOKEN;
  X_DEVICE = prompt('x-device', X_DEVICE) || X_DEVICE;
  if (!TOKEN || !X_DEVICE) { console.error('Token/device gerekli'); return; }

  const RATE_DELAY_MS = parseInt(prompt('Istek arasi gecikme (ms)', '250'), 10) || 250;

  // KRITIK: x-product: "sappy" header'i olmadan Salonappy API sadece ~30 kayit
  // donderiyor. Bu header ile total_records=34126 gibi gercek toplama ulasilir.
  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json, text/plain, */*',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
    'x-product': 'sappy',
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

  // 2) Visit listesi — AYLIK CHUNK + STATUS + OFFSET/LIMIT PAGINATED
  // Onceki "TEK GENIS ARALIK" stratejisi Salonappy'de erken kesiliyordu
  // (34K visit iddiasi, dump 15K getiriyor). AYLIK chunk garantili kapsama saglar.
  // Her ay × her status icin ayri /visit/list cagirisi.
  // Tarih araligi opsiyonel: gun bazli (YYYY-MM-DD) veya sadece yil verilebilir.
  const today = new Date();
  const defaultCutoff = new Date(today.getFullYear(), today.getMonth() + 2, 1);
  const defaultEnd = defaultCutoff.toISOString().slice(0,10);
  const startInput = prompt('Baslangic tarih (YYYY-MM-DD) veya yil', '2018-01-01') || '2018-01-01';
  const endInput = prompt('Bitis tarih (YYYY-MM-DD) veya yil', defaultEnd) || defaultEnd;
  const parseDate = (s) => {
    s = String(s).trim();
    if (/^\d{4}$/.test(s)) return new Date(parseInt(s,10), 0, 1);       // sadece yil -> Ocak 1
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return new Date(s + 'T00:00:00');
    if (/^\d{4}-\d{2}$/.test(s)) return new Date(s + '-01T00:00:00');
    return null;
  };
  const startDate = parseDate(startInput);
  const endDate = parseDate(endInput);
  if (!startDate || !endDate) { console.error('Tarih formati gecersiz'); return; }
  console.log(`🔹 Visit cekim araligi: ${startDate.toISOString().slice(0,10)} .. ${endDate.toISOString().slice(0,10)}`);
  let visits = await dbGet('visits');
  if (!visits) {
    console.log('🔹 Visit listesi cekiliyor AYLIK+STATUS...');
    visits = [];
    const seenSess = new Set();
    const limit = 100;
    const statuses = [1, 2, 3, 4, 5];
    let toplamCagri = 0;
    // Aylik iterate — hem yil hem gun bazli calisir (start ayindan end ayina kadar)
    const iter = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
    const stop = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
    while (iter <= stop) {
      const yr = iter.getFullYear(), mo = iter.getMonth() + 1;
      const monthFirst = new Date(yr, mo - 1, 1);
      const monthLast = new Date(yr, mo, 0);
      // Baslangic ayi ise startDate'e clamp; bitis ayi ise endDate'e clamp — dar aralik desteklenir
      const rangeStart = (monthFirst < startDate) ? startDate : monthFirst;
      const rangeEnd = (monthLast > endDate) ? endDate : monthLast;
      const dStart = rangeStart.toISOString().slice(0,10);
      const dEnd = rangeEnd.toISOString().slice(0,10);
      {
        let ayCount = 0;
        for (const st of statuses) {
          let offset = 0;
          let totalRecords = null;
          let ardisikBos = 0;
          while (true) {
            const j = await get(`/visit/list?offset=${offset}&limit=${limit}&date_start=${dStart}&date_end=${dEnd}&status=${st}`);
            toplamCagri++;
            const arr = j?.data?.visits || [];
            if (totalRecords === null) totalRecords = parseInt(j?.data?.total_records || '0', 10);
            for (const v of arr) {
              const sid = String(v?.session ?? v?.id ?? '');
              if (sid && !seenSess.has(sid)) { seenSess.add(sid); visits.push(v); ayCount++; }
            }
            offset += limit;
            if (arr.length === 0) ardisikBos++; else ardisikBos = 0;
            await sleep(RATE_DELAY_MS);
            if (totalRecords > 0 && offset >= totalRecords) break;
            if (ardisikBos >= 3) break; // aylik dar aralikta 3 bos sayfa yeter
            if (offset >= 5000) break;  // safety cap (bir ayda 5000 visit anormal)
          }
        }
        if (ayCount > 0 || mo % 3 === 0) {
          console.log(`  ${yr}-${String(mo).padStart(2,'0')}: +${ayCount} (kumule unique=${visits.length}, cagri=${toplamCagri})`);
          await dbPut('visits', visits);
        }
      }
      iter.setMonth(iter.getMonth() + 1);
    }
    console.log(`✓✓ Visit toplam (unique tum ay×status): ${visits.length} / ${toplamCagri} istek`);
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

  // 3) Her visit icin booking detail (PARALEL BATCH — 8'li)
  //    Onceki seri+250ms yaklasimi 16K visit icin 3+ saat aliyordu.
  //    8 concurrent + 50ms per-batch cooldown ile ~5x hizli. 429 gelirse get() zaten backoff yapiyor.
  let bookingDetails = (await dbGet('bookingDetails')) || {};
  let already = Object.keys(bookingDetails).length;
  const kalanlar = [];
  for (let i = 0; i < visits.length; i++) {
    const v = visits[i];
    const sid = String(v.session ?? v.id ?? v.booking_id ?? '');
    if (sid && !bookingDetails[sid]) kalanlar.push(sid);
  }
  const CONCURRENCY = parseInt(prompt('Booking detail concurrency (default 3; 8 429 veriyor)', '3'), 10) || 3;
  console.log(`🔹 Booking details: ${already}/${visits.length} hazir, ${kalanlar.length} kalan (paralel ${CONCURRENCY}'lu batch)...`);
  let saved = 0;
  const tStart = Date.now();
  for (let i = 0; i < kalanlar.length; i += CONCURRENCY) {
    const batch = kalanlar.slice(i, i + CONCURRENCY);
    const results = await Promise.all(batch.map(sid => get(`/booking/detail?session=${sid}`).then(j => [sid, j])));
    for (const [sid, j] of results) {
      if (j?.data?.booking) {
        bookingDetails[sid] = j.data.booking;
        saved++;
      }
    }
    if (saved % 200 < CONCURRENCY) {
      await dbPut('bookingDetails', bookingDetails);
      const elapsed = Math.round((Date.now() - tStart) / 1000);
      const remaining = kalanlar.length - (i + batch.length);
      const rate = saved / Math.max(elapsed, 1);
      const eta = Math.round(remaining / Math.max(rate, 0.1));
      console.log(`  detail ${i + batch.length}/${kalanlar.length} | yeni=${saved} gecen=${elapsed}s hiz=${rate.toFixed(1)}/sn ETA=${eta}s`);
    }
    await sleep(RATE_DELAY_MS); // batch arasi RATE_DELAY_MS (default 250; 429 alirsan 500+ dene)
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
