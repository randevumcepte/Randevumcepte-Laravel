// Müşteri dili sözlük derleyici: resources/dil/kaynak/*.txt -> resources/dil/{dil}.json
// Kullanım: node scripts/dil-derle.js [flutter klasörü]   (verilirse assets/dil'e de kopyalar)
// Kaynak satırı: Türkçe¦İngilizce¦Rusça¦Almanca  ({#}=sayı/saat, {}=herhangi, çeviride {1}{2} sıra; \n = satır)
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const kok = path.join(__dirname, '..', 'resources', 'dil');
const DILLER = ['en', 'ru', 'de'];
const sozluk = {};
DILLER.forEach(d => { sozluk[d] = {}; });
const uyari = [];

const ac = s => s.replace(/\\n/g, '\n');
const yerSayisi = s => (s.match(/\{[#\d]?\}/g) || []).length;

for (const dosya of fs.readdirSync(path.join(kok, 'kaynak')).filter(f => f.endsWith('.txt')).sort()) {
  fs.readFileSync(path.join(kok, 'kaynak', dosya), 'utf8').replace(/\r/g, '').split('\n').forEach((satir, i) => {
    if (!satir.trim() || satir.startsWith('#')) return;
    const p = satir.split('¦');
    if (p.length !== 1 + DILLER.length) { uyari.push(`${dosya}:${i + 1} sütun sayısı ${p.length}`); return; }
    const tr = ac(p[0]);
    DILLER.forEach((d, k) => {
      const v = ac(p[k + 1]);
      if (!v.trim()) uyari.push(`${dosya}:${i + 1} ${d} boş`);
      if (yerSayisi(v) !== yerSayisi(tr)) uyari.push(`${dosya}:${i + 1} ${d} yer tutucu sayısı farklı: ${tr.slice(0, 50)}`);
      if (sozluk[d][tr] !== undefined && sozluk[d][tr] !== v && d === 'en') uyari.push(`${dosya}:${i + 1} tekrar (farklı çeviri): ${tr.slice(0, 50)}`);
      if (sozluk[d][tr] === undefined) sozluk[d][tr] = v;
    });
  });
}

// Ülke adları (telefon alanı): Flutter listesindeki Türkçe ad -> Intl karşılığı
const flutter = process.argv[2];
if (flutter) {
  const t = fs.readFileSync(path.join(flutter, 'lib/Frontend/telefon_ulke_alani.dart'), 'utf8');
  const ulkeler = [...t.matchAll(/UlkeKodu\('((?:\\'|[^'])+)',\s*'[^']+',\s*'([^']+)'/g)];
  const iso = b => [...b].map(c => c.codePointAt(0) - 0x1F1E6 + 65).filter(c => c >= 65 && c <= 90).map(c => String.fromCharCode(c)).join('');
  let n = 0;
  for (const [, ad, bayrak] of ulkeler) {
    const kod = iso(bayrak);
    if (kod.length !== 2) continue;
    DILLER.forEach(d => { if (sozluk[d][ad] === undefined) sozluk[d][ad] = new Intl.DisplayNames([d], { type: 'region' }).of(kod); });
    n++;
  }
  fs.writeFileSync(path.join(kok, 'ulkeler.json'), JSON.stringify(Object.fromEntries(DILLER.map(d => [d, Object.fromEntries(ulkeler.map(u => [u[1], sozluk[d][u[1]]]).filter(x => x[1]))]))));
  console.log('ülke:', n);
} else if (fs.existsSync(path.join(kok, 'ulkeler.json'))) {
  const u = JSON.parse(fs.readFileSync(path.join(kok, 'ulkeler.json'), 'utf8'));
  DILLER.forEach(d => { for (const k in u[d]) if (sozluk[d][k] === undefined) sozluk[d][k] = u[d][k]; });
}

DILLER.forEach(d => {
  const govde = JSON.stringify(sozluk[d]);
  const surum = crypto.createHash('md5').update(govde).digest('hex').slice(0, 10);
  const cikti = JSON.stringify({ dil: d, surum, sozluk: sozluk[d] });
  fs.writeFileSync(path.join(kok, d + '.json'), cikti);
  if (flutter) {
    fs.mkdirSync(path.join(flutter, 'assets/dil'), { recursive: true });
    fs.writeFileSync(path.join(flutter, 'assets/dil', d + '.json'), cikti);
  }
  console.log(d, Object.keys(sozluk[d]).length, 'metin', Math.round(cikti.length / 1024) + ' KB', surum);
});
uyari.forEach(u => console.log('UYARI', u));
