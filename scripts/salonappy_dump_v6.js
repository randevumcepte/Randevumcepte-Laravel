// === Salonappy Full Dump v6 ===
// Console'a yapistir, Enter. Token sorulur (Network tab -> Authorization header).
// Sonuc: salonappy_v6_<ts>.json - clients + clientDetails + visits + bookingDetails + servicesMaster + staffMaster + productsMaster
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  // 1) Token alma
  let TOKEN = '';
  for (const store of [localStorage, sessionStorage]) {
    for (const k of Object.keys(store)) {
      const v = store.getItem(k); if (!v) continue;
      if (v.length > 30 && v.length < 500 && !v.includes(' ') && !v.includes('{')) {
        if (/[a-zA-Z0-9]/.test(v)) { TOKEN = v; console.log('Token aday (raw):', k); break; }
      }
      try {
        const o = JSON.parse(v);
        const t = o?.token || o?.access_token || o?.accessToken || o?.bearer || o?.user?.token;
        if (t) { TOKEN = t; console.log('Token aday (JSON):', k); break; }
      } catch(e) {}
    }
    if (TOKEN) break;
  }
  TOKEN = prompt('Bearer token (otomatik bulundu: ' + (TOKEN ? TOKEN.slice(0,20)+'...' : 'YOK') + ')', TOKEN) || '';
  if (!TOKEN) return console.error('Token yok, iptal.');

  const H = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' };

  const get = async (path, qs) => {
    const url = BASE + path + (qs ? '?' + new URLSearchParams(qs) : '');
    try {
      const r = await fetch(url, { headers: H });
      if (!r.ok) { console.warn('GET FAIL', r.status, path, qs||''); return null; }
      return await r.json();
    } catch(e) { console.warn('GET ERR', path, e.message); return null; }
  };
  const post = async (path, body) => {
    try {
      const r = await fetch(BASE + path, { method: 'POST', headers: H, body: JSON.stringify(body) });
      if (!r.ok) { console.warn('POST FAIL', r.status, path); return null; }
      return await r.json();
    } catch(e) { console.warn('POST ERR', path, e.message); return null; }
  };

  // 2) IndexedDB (buyuk veri icin)
  const DB_NAME = 'sa_v6_' + Date.now();
  const db = await new Promise((res, rej) => {
    const r = indexedDB.open(DB_NAME, 1);
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

  console.log('\n🔹 1) Master listeler cekiliyor...');
  let servicesMaster = await get('/setup/services');
  if (!servicesMaster) servicesMaster = await post('/setup/services', {});
  let staffMaster = await get('/setup/staff');
  if (!staffMaster) staffMaster = await post('/setup/staff', {});
  let productsMaster = await get('/setup/products');
  if (!productsMaster) productsMaster = await post('/setup/products', {});
  console.log('  services:', servicesMaster ? '✓ ' + JSON.stringify(servicesMaster).length + 'B' : '✗');
  console.log('  staff:', staffMaster ? '✓' : '✗');
  console.log('  products:', productsMaster ? '✓' : '✗');
  await dbPut('servicesMaster', servicesMaster);
  await dbPut('staffMaster', staffMaster);
  await dbPut('productsMaster', productsMaster);

  console.log('\n🔹 2) Musteri listesi...');
  let clients = [];
  for (let page = 1; page <= 50; page++) {
    let j = await post('/client/list', { page, limit: 200, search: '' });
    if (!j) j = await get('/client/list', { page, limit: 200 });
    if (!j) break;
    const list = Array.isArray(j) ? j : (j.data || j.clients || j.result || j.list || []);
    if (!list.length) break;
    clients = clients.concat(list);
    console.log(`  sayfa ${page}: +${list.length} (total ${clients.length})`);
    if (list.length < 200) break;
  }
  await dbPut('clients', clients);
  console.log('  TOTAL clients:', clients.length);

  console.log('\n🔹 3) Visit listesi (tum tarihler)...');
  let visits = [];
  let v = await post('/visit/list', { from_date: '2020-01-01', to_date: '2030-12-31', limit: 99999 });
  if (!v) v = await get('/visit/list', { from_date: '2020-01-01', to_date: '2030-12-31' });
  if (v) {
    visits = Array.isArray(v) ? v : (v.data || v.visits || v.list || v.result || []);
    console.log('  visits:', visits.length);
  }
  if (!visits.length) {
    console.log('  Pagination deneniyor...');
    for (let page = 1; page <= 200; page++) {
      const r = await post('/visit/list', { page, limit: 100 });
      const list = r ? (Array.isArray(r) ? r : (r.data || r.visits || [])) : [];
      if (!list.length) break;
      visits = visits.concat(list);
      console.log(`  visit sayfa ${page}: +${list.length} (total ${visits.length})`);
      if (list.length < 100) break;
    }
  }
  await dbPut('visits', visits);

  console.log('\n🔹 4) ClientDetails (her musteri icin)...');
  const clientDetails = {};
  for (let i = 0; i < clients.length; i++) {
    const c = clients[i];
    const cid = c.id || c.client_id;
    if (!cid) continue;
    const d = await get('/client/details/' + cid) || await get('/client/details', { id: cid });
    if (d) clientDetails[cid] = d.data || d;
    if ((i+1) % 50 === 0) {
      console.log(`  client ${i+1}/${clients.length}`);
      await dbPut('clientDetails', clientDetails);
    }
  }
  await dbPut('clientDetails', clientDetails);

  console.log('\n🔹 5) BookingDetails (her visit icin)...');
  const bookingDetails = {};
  let okCount = 0, failCount = 0;
  for (let i = 0; i < visits.length; i++) {
    const v = visits[i];
    const sess = v.session || v.id || v.booking_id;
    if (!sess) { failCount++; continue; }
    const d = await get('/booking/details/' + sess) || await get('/booking/details', { session: sess });
    if (d) { bookingDetails[sess] = d.data || d; okCount++; }
    else { failCount++; }
    if ((i+1) % 100 === 0) {
      console.log(`  booking ${i+1}/${visits.length} (ok=${okCount} fail=${failCount})`);
      await dbPut('bookingDetails', bookingDetails);
    }
  }
  await dbPut('bookingDetails', bookingDetails);
  console.log(`  Toplam booking detayi: ok=${okCount} fail=${failCount}`);

  console.log('\n🔹 6) JSON birlestir + indir...');
  const dump = {
    generated_at: new Date().toISOString(),
    servicesMaster, staffMaster, productsMaster,
    clients, clientDetails, visits, bookingDetails
  };
  const txt = JSON.stringify(dump);
  console.log('  Boyut:', (txt.length/1024/1024).toFixed(2), 'MB');

  const blob = new Blob([txt], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'salonappy_v6_' + Date.now() + '.json';
  document.body.appendChild(a); a.click(); a.remove();
  console.log('✅ Indirildi.');
})();
