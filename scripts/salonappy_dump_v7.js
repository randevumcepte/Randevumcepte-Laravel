// === Salonappy Full Dump v7 (TR locale, dogrulanmis endpoint'ler) ===
// Console'a yapistir, Enter. Token+x-device prompt'tan onaylanir.
// Cikti: salonappy_v7_<ts>.json (importer otomatik tanir, ekstra param gerekmez)
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  // Auth degerleri (Network'ten alindi; degisirse promptlarda guncelle)
  let TOKEN = '288401&fJqmdVpa7b01e19c0KUNVnvz4644713c6e717fe0c169b82a60a47e';
  let X_DEVICE = 'deovpplHniEa2s8oE12C7phjNlxsolkP';
  let X_VERSION = '2026.05.07.1';
  TOKEN = prompt('Bearer token', TOKEN) || TOKEN;
  X_DEVICE = prompt('x-device', X_DEVICE) || X_DEVICE;
  if (!TOKEN || !X_DEVICE) return console.error('Token/device yok, iptal.');

  const H = () => ({
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json, text/plain, */*',
    'x-device': X_DEVICE,
    'x-language': 'tr',
    'x-platform': 'web',
    'x-version': X_VERSION
  });
  const ts = () => '?timestamp=' + Math.floor(Date.now()/1000);
  const tsAmp = (path) => path.includes('?') ? '&' : '?';

  const get = async (path) => {
    const url = BASE + path + tsAmp(path) + 'timestamp=' + Math.floor(Date.now()/1000);
    const r = await fetch(url, { headers: H() });
    if (!r.ok) { console.warn('FAIL', r.status, path); return null; }
    return r.json();
  };

  // IndexedDB persist (4567 booking-detail icin guvenli)
  const DB_NAME = 'sa_v7_' + Date.now();
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

  console.log('🔹 1) Master listeler...');
  const sMaster = await get('/service/salon');
  const stMaster = await get('/staff/list');
  const pMaster = await get('/product/list');
  const servicesMaster = sMaster?.data?.services || sMaster?.data || [];
  const staffMaster = stMaster?.data?.staff || stMaster?.data?.list || stMaster?.data || [];
  const productsMaster = pMaster?.data?.products || pMaster?.data?.list || pMaster?.data || [];
  console.log('  services:', servicesMaster?.length || Object.keys(servicesMaster||{}).length,
              'staff:', staffMaster?.length || Object.keys(staffMaster||{}).length,
              'products:', productsMaster?.length || Object.keys(productsMaster||{}).length);
  await dbPut('servicesMaster', servicesMaster);
  await dbPut('staffMaster', staffMaster);
  await dbPut('productsMaster', productsMaster);

  console.log('\n🔹 2) Musteri listesi...');
  const cj = await get('/client/list');
  const clients = cj?.data?.clients || [];
  console.log('  clients:', clients.length, 'total_records:', cj?.data?.total_records);
  await dbPut('clients', clients);

  console.log('\n🔹 3) Visit listesi...');
  const vj = await get('/visit/list');
  const visits = vj?.data?.visits || [];
  console.log('  visits:', visits.length, 'total_records:', vj?.data?.total_records);
  await dbPut('visits', visits);

  console.log('\n🔹 4) Booking detail (her session icin)...');
  const bookingDetails = {};
  let ok = 0, fail = 0, t0 = Date.now();
  for (let i = 0; i < visits.length; i++) {
    const sess = visits[i].session;
    if (!sess) { fail++; continue; }
    const j = await get('/booking/detail?session=' + sess);
    if (j?.data?.booking) {
      bookingDetails[sess] = j.data.booking;
      ok++;
    } else { fail++; }
    if ((i+1) % 100 === 0) {
      const eta = ((Date.now()-t0)/1000/(i+1) * (visits.length - i - 1)).toFixed(0);
      console.log(`  ${i+1}/${visits.length}  ok=${ok} fail=${fail}  ETA ${eta}s`);
      await dbPut('bookingDetails', bookingDetails);
    }
  }
  await dbPut('bookingDetails', bookingDetails);
  console.log(`  TAMAM: ok=${ok} fail=${fail}`);

  console.log('\n🔹 5) JSON birlestir + indir...');
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
  console.log('✅ Indirildi.');
})();
