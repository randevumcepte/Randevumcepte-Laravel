/* Müşteri dili (online randevu sitesi, pilot salonlar). Sayfa Türkçe üretilir; seçili dilde metinler burada çevrilir.
   Çeviri kuralları sunucu (App\Services\DilSozluk) ve uygulama (lib/dil/ceviri.dart) ile AYNI.
   window.MUSTERI_DIL = {diller:[{kod,ad,bayrak}], surum:{ru:..}} (MusteriDilWeb ara katmanı yazar). */
(function () {
  'use strict';
  var A = window.MUSTERI_DIL;
  if (!A || !A.diller || A.diller.length < 2) return;

  function cerez(ad) {
    var m = document.cookie.match(new RegExp('(?:^|; )' + ad + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  }
  function cerezYaz(ad, v) { document.cookie = ad + '=' + encodeURIComponent(v) + ';path=/;max-age=31536000;samesite=lax'; }
  function listede(k) { return A.diller.some(function (d) { return d.kod === k; }); }

  // İşletme paneli: dil personelin kaydından gelir (uygulamadaki seçimle aynı), A.panel = {dil, sozluk, kaydet}
  var dil = A.panel ? A.panel.dil : cerez('musteri_dil');
  if (!listede(dil)) dil = 'tr';

  // ------------------------------------------------------------------ çeviri motoru
  var AY = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
  var GUN = ['Cumartesi', 'Pazartesi', 'Çarşamba', 'Perşembe', 'Pazar', 'Salı', 'Cuma'];
  var GUNK = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Cts', 'Paz'];
  var AYD = {
    en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    ru: ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
    de: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember']
  };
  var H = 'A-Za-zÇĞİÖŞÜçğıöşü';
  var S = {}, K = {}, P = [], on = {};
  var reH = new RegExp('[' + H + ']');
  function kacis(s) { return s.replace(/[.*+?^${}()|[\]\\\/]/g, '\\$&'); }
  function bosluk(s) { return s.replace(/\s+/g, ' ').trim(); }
  function kucult(s) { return s.replace(/I/g, 'ı').replace(/İ/g, 'i').toLowerCase(); }

  function yukle(sozluk) {
    S = {}; K = {}; P = []; on = {};
    Object.keys(sozluk).forEach(function (tr) {
      var c = sozluk[tr];
      if (tr.indexOf('{}') >= 0 || tr.indexOf('{#}') >= 0) {
        var sabit = '';
        var d = tr.split(/(\{#?\})/).filter(Boolean).map(function (p) {
          if (p === '{}') return '([^\\n]+?)';
          if (p === '{#}') return '([\\d.,:%+\\-/]+)';
          return p.split(/(\s+)/).filter(Boolean).map(function (x) {
            if (!x.trim()) return x.indexOf('\n') >= 0 ? '\\s*\\n\\s*' : '[^\\S\\n]+';
            if (x.length > sabit.length) sabit = x;
            return kacis(x);
          }).join('');
        }).join('');
        P.push([new RegExp('^' + d + '$'), c, sabit, d.length]);
        return;
      }
      S[tr] = c;
      var b = bosluk(tr);
      if (S[b] === undefined) S[b] = c;
      var k = kucult(b);
      if (K[k] === undefined) K[k] = c;
    });
    P.sort(function (a, b) { return b[3] - a[3]; });
  }
  function tam(s) {
    if (Object.prototype.hasOwnProperty.call(S, s)) return S[s];
    var b = bosluk(s);
    if (Object.prototype.hasOwnProperty.call(S, b)) return S[b];
    if (b.length < 3) return null; // "AD" baş harfleri "Ad" sanılmasın
    var k = kucult(b);
    if (!Object.prototype.hasOwnProperty.call(K, k)) return null;
    return s.length > 2 && s === s.toUpperCase() && s !== s.toLowerCase() ? K[k].toUpperCase() : K[k];
  }
  function parcala(liste, ayrac, derin, hepsiSart) {
    var degisti = false, hepsi = true;
    var c = liste.map(function (x) {
      if (!x.trim() || !reH.test(x) || /^\s*https?:\/\/\S+\s*$/.test(x)) return x;
      var y = bul(x.trim(), derin + 1);
      if (y === null) { hepsi = false; return x; }
      degisti = true;
      return y;
    });
    return !degisti || (hepsiSart && !hepsi) ? null : c.join(ayrac);
  }
  function tarih(s) {
    if (!AYD[dil]) return null;
    var r = s.replace(new RegExp('(\\d{1,2}) (' + AY.join('|') + ')(?![' + H + '])', 'g'), function (_, g, a) {
      var ay = AYD[dil][AY.indexOf(a)];
      return dil === 'de' ? g + '. ' + ay : g + ' ' + ay;
    });
    r = r.replace(new RegExp('(^|[^' + H + '])(' + GUN.join('|') + ')(?![' + H + '])', 'g'), function (_, o, g) { return o + (S[g] || g); });
    if (/\d/.test(s)) r = r.replace(new RegExp('(^|[^' + H + '])(' + GUNK.join('|') + ')(?![' + H + '])', 'g'), function (_, o, g) { return o + (S[g] || g); });
    return r === s ? null : r;
  }
  function bul(s, derin) {
    var t = tam(s);
    if (t !== null) return t;
    if (derin > 3) return null;
    for (var i = 0; i < P.length; i++) {
      var k = P[i];
      if (k[2] && s.indexOf(k[2]) < 0) continue;
      var m = k[0].exec(s);
      if (!m) continue;
      var sira = 0;
      return k[1].replace(/\{(\d?)#?\}/g, function (_, n) {
        var parca = m[n === '' ? ++sira : +n] || '';
        if (!reH.test(parca) || AY.indexOf(parca.trim()) >= 0) return parca; // "Sayın Eylül" kişi adı
        var c = bul(parca.trim(), derin + 1);
        return c === null ? parca : c;
      });
    }
    if (s.indexOf('\n') >= 0) return parcala(s.split('\n'), '\n', derin, false);
    var sa = /^(\d{1,2}[:.]\d{2}) +(.+)$/.exec(s);
    if (sa) { var sc = bul(sa[2], derin + 1); if (sc !== null) return sa[1] + ' ' + sc; }
    var tr = tarih(s);
    if (tr !== null && !reH.test(s.replace(new RegExp(AY.concat(GUN, GUNK).join('|'), 'g'), ''))) return tr;
    var ic = new RegExp('^([^' + H + '0-9%]*)([\\s\\S]*?)([^' + H + '0-9%)]*)$').exec(s);
    if (ic && (ic[1] || ic[3]) && ic[2]) { var c2 = bul(ic[2], derin + 1); if (c2 !== null) return ic[1] + c2 + ic[3]; }
    var cumle = s.split(/([.!?])\s+/), birlesik = [];
    for (var q = 0; q < cumle.length; q += 2) birlesik.push(cumle[q] + (cumle[q + 1] || ''));
    if (birlesik.length > 1) { var c3 = parcala(birlesik, ' ', derin, false); if (c3 !== null) return c3; }
    var ay = [' • ', ' · ', '  ·  ', ' | ', ' — ', ' – ', ' / ', ' + ', ', ', ': '];
    for (var j = 0; j < ay.length; j++) {
      if (s.indexOf(ay[j]) < 0) continue;
      var p = parcala(s.split(ay[j]), ay[j], derin, ay[j] === ', ');
      if (p !== null) return p;
    }
    return tr;
  }
  function cevir(metin) {
    if (dil === 'tr' || !metin || !reH.test(metin)) return metin;
    if (on[metin] !== undefined) return on[metin];
    var m = /^(\s*)([\s\S]*?)(\s*)$/.exec(metin);
    var c = m[2] ? bul(bosluk(m[2]), 0) : null;
    return (on[metin] = c === null ? metin : m[1] + c + m[3]);
  }
  window.musteriDilCevir = cevir;

  // ------------------------------------------------------------------ sayfa
  var NIT = ['placeholder', 'title', 'alt', 'aria-label', 'data-original-title'];
  var ATLA = { SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, TEXTAREA: 1, CODE: 1, IFRAME: 1 };
  function nit(e) {
    for (var i = 0; i < NIT.length; i++) {
      var v = e.getAttribute && e.getAttribute(NIT[i]);
      if (v) { var c = cevir(v); if (c !== v) e.setAttribute(NIT[i], c); }
    }
    if (e.tagName === 'INPUT' && /^(submit|button|reset)$/.test(e.type) && e.value) {
      var b = cevir(e.value);
      if (b !== e.value) e.value = b;
    }
  }
  function gez(n) {
    if (n.nodeType === 3) {
      var c = cevir(n.nodeValue);
      if (c !== n.nodeValue) n.nodeValue = c;
      return;
    }
    if (n.nodeType === 1 && n.tagName === 'TEXTAREA') return nit(n);
    if (n.nodeType !== 1 || ATLA[n.tagName] || (n.hasAttribute && n.hasAttribute('data-dil-yok'))) return;
    nit(n);
    for (var x = n.firstChild; x; x = x.nextSibling) gez(x);
  }

  function dugme() {
    var st = document.createElement('style');
    st.textContent = '.md-kutu{position:fixed;top:78px;right:12px;z-index:99990;font-family:inherit}' +
      '.md-dug{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid rgba(15,23,42,.12);border-radius:22px;padding:8px 13px;font-size:14px;font-weight:600;color:#1f2937;box-shadow:0 4px 14px rgba(15,23,42,.14);cursor:pointer;line-height:1}' +
      '.md-liste{display:none;position:absolute;right:0;top:44px;background:#fff;border-radius:14px;box-shadow:0 12px 34px rgba(15,23,42,.22);overflow:hidden;min-width:170px}' +
      '.md-kutu.ac .md-liste{display:block}.md-liste button{display:flex;gap:10px;align-items:center;width:100%;border:0;background:#fff;padding:12px 16px;font-size:15px;color:#1f2937;cursor:pointer;text-align:left}' +
      '.md-liste button:hover,.md-liste button.sec{background:#f1f5f9;font-weight:700}' +
      '.md-oneri{position:fixed;left:12px;right:12px;bottom:86px;margin:auto;max-width:420px;z-index:99991;background:#fff;border-radius:16px;box-shadow:0 12px 40px rgba(15,23,42,.28);padding:14px 16px;display:flex;gap:10px;align-items:center;justify-content:space-between;font-size:15px;color:#1f2937}' +
      '.md-oneri button{border:0;border-radius:12px;padding:10px 16px;font-size:15px;font-weight:700;cursor:pointer}.md-evet{background:#1f2937;color:#fff}.md-hayir{background:#f1f5f9;color:#1f2937}';
    document.head.appendChild(st);
    var k = document.createElement('div');
    k.className = 'md-kutu';
    k.setAttribute('data-dil-yok', '');
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'md-dug';
    b.setAttribute('aria-label', 'Language');
    b.textContent = '🌐 ' + dil.toUpperCase() + ' ▾';
    var l = document.createElement('div');
    l.className = 'md-liste';
    A.diller.forEach(function (d) {
      var o = document.createElement('button');
      o.type = 'button';
      if (d.kod === dil) o.className = 'sec';
      o.textContent = d.bayrak + '  ' + d.ad;
      o.onclick = function () { sec(d.kod); };
      l.appendChild(o);
    });
    b.onclick = function (e) { e.stopPropagation(); k.classList.toggle('ac'); };
    document.addEventListener('click', function () { k.classList.remove('ac'); });
    k.appendChild(b);
    k.appendChild(l);
    document.body.appendChild(k);
    // Panelde seçim profil menüsünde ("Çıkış Yap" üstü); menü yoksa yüzen düğme kalır
    var cikis = A.panel && document.querySelector('a[href*="/isletmeyonetim/cikisyap"]');
    if (cikis) {
      k.classList.add('md-panel');
      st.textContent += '.md-panel{top:64px;right:16px}.md-panel .md-dug{display:none}.md-panel .md-liste{top:0}';
      var a = document.createElement('a');
      a.className = cikis.className;
      a.href = '#';
      a.setAttribute('data-dil-yok', '');
      a.textContent = '🌐 Dil / Language (' + dil.toUpperCase() + ')';
      a.onclick = function (e) { e.preventDefault(); e.stopPropagation(); k.classList.add('ac'); };
      cikis.parentNode.insertBefore(a, cikis);
    }
  }

  function sec(yeni) {
    if (!A.panel) cerezYaz('musteri_dil', yeni);
    var bitti = function () { location.reload(); };
    var t = document.querySelector('meta[name="csrf-token"],meta[name="_token"]');
    var inp = document.querySelector('input[name="_token"]');
    var kod = t ? t.getAttribute('content') : (inp ? inp.value : '');
    if (!kod || !window.fetch) return bitti();
    fetch(A.panel ? A.panel.kaydet : '/musteri-dil', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': kod, 'X-Requested-With': 'XMLHttpRequest' },
      body: '_token=' + encodeURIComponent(kod) + '&dil=' + encodeURIComponent(yeni)
    }).then(bitti, bitti);
    setTimeout(bitti, 2500);
  }

  // Tarayıcısı Türkçe olmayan ziyaretçiye bir kez kendi dilinde önerilir (arama motorlarına Türkçe sayfa kalır)
  function oner() {
    if (cerez('musteri_dil') || cerez('musteri_dil_soruldu') || /bot|crawl|spider|slurp|lighthouse|headless/i.test(navigator.userAgent)) return;
    var t = (navigator.language || '').slice(0, 2).toLowerCase();
    if (t === 'tr' || !listede(t)) return;
    var d = A.diller.filter(function (x) { return x.kod === t; })[0];
    var e = document.createElement('div');
    e.className = 'md-oneri';
    e.setAttribute('data-dil-yok', '');
    var y = document.createElement('span');
    y.textContent = d.bayrak + ' ' + d.ad + '?';
    var g = document.createElement('span');
    var evet = document.createElement('button');
    evet.type = 'button'; evet.className = 'md-evet'; evet.textContent = d.ad;
    evet.onclick = function () { sec(t); };
    var hayir = document.createElement('button');
    hayir.type = 'button'; hayir.className = 'md-hayir'; hayir.textContent = 'Türkçe'; hayir.style.marginLeft = '8px';
    hayir.onclick = function () { cerezYaz('musteri_dil_soruldu', '1'); e.remove(); };
    g.appendChild(evet); g.appendChild(hayir);
    e.appendChild(y); e.appendChild(g);
    document.body.appendChild(e);
  }

  function hazir(f) { document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', f) : f(); }

  if (dil === 'tr') { hazir(function () { dugme(); if (!A.panel) oner(); }); return; }

  // Türkçe metin görünüp değişmesin: çeviri hazır olana (en çok 2,5 sn) kadar gövde gizli
  var gizle = document.createElement('style');
  gizle.textContent = 'body{visibility:hidden!important}';
  (document.head || document.documentElement).appendChild(gizle);
  var ac = function () { if (gizle.parentNode) gizle.parentNode.removeChild(gizle); };
  setTimeout(ac, 2500);

  document.documentElement.lang = dil;
  var uyari = window.alert, onay = window.confirm;
  window.alert = function (m) { uyari.call(window, cevir(String(m))); };
  window.confirm = function (m) { return onay.call(window, cevir(String(m))); };

  fetch((A.panel ? A.panel.sozluk : '/musteri-dil/sozluk/' + dil) + (A.panel ? '&' : '?') + 'v=' + encodeURIComponent((A.surum || {})[dil] || ''), { credentials: 'same-origin' })
    .then(function (r) { return r.json(); })
    .then(function (j) {
      yukle(j.sozluk || {});
      hazir(function () {
        gez(document.body);
        document.title = cevir(document.title);
        new MutationObserver(function (kayit) {
          kayit.forEach(function (m) {
            if (m.type === 'characterData') {
              var p = m.target.parentNode;
              if (p && !ATLA[p.tagName] && !(p.closest && p.closest('[data-dil-yok]'))) gez(m.target);
            } else if (m.type === 'attributes') {
              if (!(m.target.closest && m.target.closest('[data-dil-yok]'))) nit(m.target);
            } else {
              Array.prototype.forEach.call(m.addedNodes, function (n) {
                var p = n.parentNode;
                if (p && p.nodeType === 1 && (ATLA[p.tagName] || (p.closest && p.closest('[data-dil-yok]')))) return;
                gez(n);
              });
            }
          });
        }).observe(document.body, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: NIT });
        dugme();
        ac();
      });
    })
    .catch(function () { hazir(function () { dugme(); ac(); }); });
})();
