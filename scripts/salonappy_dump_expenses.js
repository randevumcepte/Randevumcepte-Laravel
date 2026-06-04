// === Salonappy Gider/Masraf Dump ===
// Cikti: salonappy_expenses_<ts>.json
// Schema: { expenses: [...], generated_at }
// Kullanim: php artisan salonappy:import --dump-file=... --salon=X --only-expenses
(async () => {
  const BASE = 'https://web-api.salonappy.com/api';

  let TOKEN = '288401&Oc1lPAy62641ff1camqsiK7919e9107f826b22f39ede49c6ff4eaa';
  let X_DEVICE = '1tevuO7938R1ggFPtQZVerlqY2GfIBJK';
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

  console.log('🔹 Giderler cekiliyor (/expense/list)...');
  const dateStart = '2018-01-01';
  const dEnd = new Date(); dEnd.setFullYear(dEnd.getFullYear() + 1);
  const dateEnd = dEnd.toISOString().slice(0,10);
  const expenses = [];
  let offset = 0; const limit = 100;
  while (true) {
    const j = await get(`/expense/list?offset=${offset}&limit=${limit}&date_start=${dateStart}&date_end=${dateEnd}&is_deleted=0`);
    const arr = j?.data?.expenses || [];
    if (!arr.length) break;
    expenses.push(...arr);
    offset += arr.length;
    if (offset % 500 === 0) console.log(`  kumule: ${expenses.length}`);
    await sleep(RATE_DELAY_MS);
    if (arr.length < limit) break;
  }
  console.log(`✓ Toplam gider: ${expenses.length}`);

  const dump = { generated_at: new Date().toISOString(), expenses };
  const txt = JSON.stringify(dump);
  console.log(`Boyut: ${(txt.length/1024/1024).toFixed(2)} MB`);
  const blob = new Blob([txt], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'salonappy_expenses_' + Date.now() + '.json';
  document.body.appendChild(a); a.click(); a.remove();
  console.log('✅ Indirildi.');
})();
