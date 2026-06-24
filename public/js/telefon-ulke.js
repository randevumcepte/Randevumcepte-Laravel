/*
 * telefon-ulke.js — Alan kodu (ulke) secici + numara bileseni (web)
 *
 * Backend'e gonderilen deger:
 *   - Turkiye (+90): DUZ ulusal numara, bastaki 0 atilmis -> "5321234567"
 *     (Web kayitlari boyle saklaniyor; StoreAdminController normalize etmeden
 *      oldugu gibi kaydediyor, register/login ise zaten ayni 10 haneye indiriyor.)
 *   - Yabanci: "+KOD" + ulusal numara -> "+491761234567"
 *     (Backend telefon_no_format_duzenle yalniz +90/90/0'i soyar, +49..'a dokunmaz.)
 *
 * Kullanim: HTML'de su yapi olsun:
 *   <div class="tel-grup" data-tel-initial="...">
 *     <select class="tel-ulke"></select>
 *     <input class="tel-num" type="tel">
 *     <input class="tel-val" type="hidden" name="telefon">   // backend'e giden
 *   </div>
 * Sonra: TelefonUlke.kur(rootEl)  (data-tel-initial otomatik okunur)
 */
(function (w) {
  var KODLAR = [
    { ad: 'Türkiye', kod: '+90', bayrak: '🇹🇷' },
    { ad: 'Almanya', kod: '+49', bayrak: '🇩🇪' },
    { ad: 'Hollanda', kod: '+31', bayrak: '🇳🇱' },
    { ad: 'Avusturya', kod: '+43', bayrak: '🇦🇹' },
    { ad: 'Belçika', kod: '+32', bayrak: '🇧🇪' },
    { ad: 'Fransa', kod: '+33', bayrak: '🇫🇷' },
    { ad: 'İngiltere', kod: '+44', bayrak: '🇬🇧' },
    { ad: 'İsviçre', kod: '+41', bayrak: '🇨🇭' },
    { ad: 'İtalya', kod: '+39', bayrak: '🇮🇹' },
    { ad: 'İspanya', kod: '+34', bayrak: '🇪🇸' },
    { ad: 'İsveç', kod: '+46', bayrak: '🇸🇪' },
    { ad: 'Norveç', kod: '+47', bayrak: '🇳🇴' },
    { ad: 'Danimarka', kod: '+45', bayrak: '🇩🇰' },
    { ad: 'ABD / Kanada', kod: '+1', bayrak: '🇺🇸' },
    { ad: 'Rusya', kod: '+7', bayrak: '🇷🇺' },
    { ad: 'Azerbaycan', kod: '+994', bayrak: '🇦🇿' },
    { ad: 'Gürcistan', kod: '+995', bayrak: '🇬🇪' },
    { ad: 'Bulgaristan', kod: '+359', bayrak: '🇧🇬' },
    { ad: 'Yunanistan', kod: '+30', bayrak: '🇬🇷' },
    { ad: 'Romanya', kod: '+40', bayrak: '🇷🇴' },
    { ad: 'K. Makedonya', kod: '+389', bayrak: '🇲🇰' },
    { ad: 'Kosova', kod: '+383', bayrak: '🇽🇰' },
    { ad: 'K.K.T.C / G.Kıbrıs', kod: '+357', bayrak: '🇨🇾' },
    { ad: 'S. Arabistan', kod: '+966', bayrak: '🇸🇦' },
    { ad: 'B.A.E.', kod: '+971', bayrak: '🇦🇪' },
    { ad: 'Katar', kod: '+974', bayrak: '🇶🇦' },
    { ad: 'İran', kod: '+98', bayrak: '🇮🇷' },
    { ad: 'Irak', kod: '+964', bayrak: '🇮🇶' }
  ];

  function digits(s) { return (s || '').replace(/\D/g, ''); }
  function ulusal(s) { return digits(s).replace(/^0+/, ''); } // bastaki 0(lar) at

  function trBicim(s) {
    var d = ulusal(s);
    if (d.length > 10) d = d.substr(0, 10);
    var out = '';
    for (var i = 0; i < d.length; i++) {
      if (i === 3 || i === 6 || i === 8) out += ' ';
      out += d[i];
    }
    return out;
  }

  // Saklanan degeri {kod, numara} olarak coz
  function parse(stored) {
    var s = (stored == null ? '' : ('' + stored)).trim();
    if (!s || s === 'null') return { kod: '+90', numara: '' };
    if (s.charAt(0) === '+') {
      var best = null;
      for (var i = 0; i < KODLAR.length; i++) {
        var k = KODLAR[i].kod;
        if (s.indexOf(k) === 0 && (!best || k.length > best.length)) best = k;
      }
      if (best) {
        var kalan = digits(s.substr(best.length));
        return { kod: best, numara: best === '+90' ? trBicim(kalan) : kalan };
      }
      return { kod: '+90', numara: trBicim(digits(s)) };
    }
    return { kod: '+90', numara: trBicim(digits(s)) };
  }

  // Backend'e gidecek deger
  function compose(kod, numText) {
    var d = ulusal(numText);
    if (!d) return '';
    if (kod === '+90') return d;        // TR: duz 10 hane
    return kod + d;                     // yabanci: +KOD + numara
  }

  function bos(numText) { return ulusal(numText) === ''; }

  function _optionsHtml() {
    var html = '';
    for (var i = 0; i < KODLAR.length; i++) {
      var o = KODLAR[i];
      html += '<option value="' + o.kod + '">' + o.bayrak + ' ' + o.kod + '</option>';
    }
    return html;
  }

  function _sync(sel, num, hid) {
    if (sel.value === '+90') {
      num.value = trBicim(num.value);
    } else {
      num.value = num.value.replace(/[^0-9 ]/g, '').replace(/^0+/, '');
    }
    if (hid) hid.value = compose(sel.value, num.value);
  }

  // Bir .tel-grup kokunu kur
  function kur(root, opts) {
    if (!root) return null;
    opts = opts || {};
    if (root.getAttribute('data-tel-kuruldu') === '1') {
      // zaten kurulu — sadece deger guncelle
      if (opts.initial != null) setDeger(root, opts.initial);
      return root._telApi || null;
    }
    var sel = root.querySelector('.tel-ulke');
    var num = root.querySelector('.tel-num');
    var hid = root.querySelector('.tel-val');
    if (!sel || !num || !hid) return null;

    sel.innerHTML = _optionsHtml();

    var initial = opts.initial != null
      ? opts.initial
      : (root.getAttribute('data-tel-initial') || '');
    var p = parse(initial);
    sel.value = p.kod;
    num.value = p.numara;
    hid.value = compose(p.kod, num.value);

    sel.addEventListener('change', function () {
      // mod degisince numarayi uyarla
      var d = ulusal(num.value);
      num.value = sel.value === '+90' ? trBicim(d) : d;
      if (hid) hid.value = compose(sel.value, num.value);
    });
    num.addEventListener('input', function () { _sync(sel, num, hid); });
    num.addEventListener('blur', function () { _sync(sel, num, hid); });

    root.setAttribute('data-tel-kuruldu', '1');
    var api = {
      root: root, sel: sel, num: num, hid: hid,
      bos: function () { return bos(num.value); },
      value: function () { return compose(sel.value, num.value); },
      kod: function () { return sel.value; }
    };
    root._telApi = api;
    return api;
  }

  // Saklanan degeri bir koke uygula (AJAX ile gelen veri icin)
  function setDeger(root, stored) {
    if (!root) return;
    var sel = root.querySelector('.tel-ulke');
    var num = root.querySelector('.tel-num');
    var hid = root.querySelector('.tel-val');
    if (!sel || !num) return;
    if (!sel.options || !sel.options.length) sel.innerHTML = _optionsHtml();
    var p = parse(stored);
    sel.value = p.kod;
    num.value = p.numara;
    if (hid) hid.value = compose(p.kod, num.value);
  }

  // Sayfadaki tum .tel-grup'lari kur
  function kurHepsi() {
    var list = document.querySelectorAll('.tel-grup');
    for (var i = 0; i < list.length; i++) kur(list[i]);
  }

  w.TelefonUlke = {
    KODLAR: KODLAR,
    digits: digits,
    ulusal: ulusal,
    trBicim: trBicim,
    parse: parse,
    compose: compose,
    bos: bos,
    kur: kur,
    setDeger: setDeger,
    kurHepsi: kurHepsi
  };
})(window);
