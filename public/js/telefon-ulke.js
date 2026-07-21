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
 *
 * NOT: <select> DOM'da kalir (gizlenir) — disaridan .tel-ulke okuyan eski kodlar
 * calismaya devam eder. Ustune aramali bir buton/panel cizilir; 200+ ulke dar bir
 * native select'te bulunamayacagi icin.
 */
(function (w) {
  // one:1 -> listenin en ustundeki "Sik kullanilan" grubunda cikar
  var KODLAR = [
    { ad: 'Türkiye', kod: '+90', bayrak: '🇹🇷', one: 1 },
    { ad: 'Almanya', kod: '+49', bayrak: '🇩🇪', one: 1 },
    { ad: 'Hollanda', kod: '+31', bayrak: '🇳🇱', one: 1 },
    { ad: 'Avusturya', kod: '+43', bayrak: '🇦🇹', one: 1 },
    { ad: 'Belçika', kod: '+32', bayrak: '🇧🇪', one: 1 },
    { ad: 'Fransa', kod: '+33', bayrak: '🇫🇷', one: 1 },
    { ad: 'İngiltere', kod: '+44', bayrak: '🇬🇧', one: 1 },
    { ad: 'İsviçre', kod: '+41', bayrak: '🇨🇭', one: 1 },
    { ad: 'İtalya', kod: '+39', bayrak: '🇮🇹', one: 1 },
    { ad: 'İspanya', kod: '+34', bayrak: '🇪🇸', one: 1 },
    { ad: 'İsveç', kod: '+46', bayrak: '🇸🇪', one: 1 },
    { ad: 'Norveç', kod: '+47', bayrak: '🇳🇴', one: 1 },
    { ad: 'Danimarka', kod: '+45', bayrak: '🇩🇰', one: 1 },
    { ad: 'ABD / Kanada', kod: '+1', bayrak: '🇺🇸', one: 1 },
    { ad: 'Azerbaycan', kod: '+994', bayrak: '🇦🇿', one: 1 },
    { ad: 'Arnavutluk', kod: '+355', bayrak: '🇦🇱', one: 1 },
    { ad: 'Kosova', kod: '+383', bayrak: '🇽🇰', one: 1 },
    { ad: 'K. Makedonya', kod: '+389', bayrak: '🇲🇰', one: 1 },
    { ad: 'Bulgaristan', kod: '+359', bayrak: '🇧🇬', one: 1 },
    { ad: 'K.K.T.C / G.Kıbrıs', kod: '+357', bayrak: '🇨🇾', one: 1 },

    // --- Tum ulkeler (alfabetik) ---
    { ad: 'Afganistan', kod: '+93', bayrak: '🇦🇫' },
    { ad: 'Almanya', kod: '+49', bayrak: '🇩🇪' },
    { ad: 'ABD / Kanada', kod: '+1', bayrak: '🇺🇸' },
    { ad: 'Andorra', kod: '+376', bayrak: '🇦🇩' },
    { ad: 'Angola', kod: '+244', bayrak: '🇦🇴' },
    { ad: 'Anguilla', kod: '+1264', bayrak: '🇦🇮' },
    { ad: 'Antigua ve Barbuda', kod: '+1268', bayrak: '🇦🇬' },
    { ad: 'Arjantin', kod: '+54', bayrak: '🇦🇷' },
    { ad: 'Arnavutluk', kod: '+355', bayrak: '🇦🇱' },
    { ad: 'Aruba', kod: '+297', bayrak: '🇦🇼' },
    { ad: 'Avustralya', kod: '+61', bayrak: '🇦🇺' },
    { ad: 'Avusturya', kod: '+43', bayrak: '🇦🇹' },
    { ad: 'Azerbaycan', kod: '+994', bayrak: '🇦🇿' },
    { ad: 'Bahamalar', kod: '+1242', bayrak: '🇧🇸' },
    { ad: 'Bahreyn', kod: '+973', bayrak: '🇧🇭' },
    { ad: 'Bangladeş', kod: '+880', bayrak: '🇧🇩' },
    { ad: 'Barbados', kod: '+1246', bayrak: '🇧🇧' },
    { ad: 'Belarus', kod: '+375', bayrak: '🇧🇾' },
    { ad: 'Belçika', kod: '+32', bayrak: '🇧🇪' },
    { ad: 'Belize', kod: '+501', bayrak: '🇧🇿' },
    { ad: 'Benin', kod: '+229', bayrak: '🇧🇯' },
    { ad: 'Bermuda', kod: '+1441', bayrak: '🇧🇲' },
    { ad: 'Beyaz Rusya (Belarus)', kod: '+375', bayrak: '🇧🇾' },
    { ad: 'Bhutan', kod: '+975', bayrak: '🇧🇹' },
    { ad: 'Birleşik Arap Emirlikleri', kod: '+971', bayrak: '🇦🇪' },
    { ad: 'Bolivya', kod: '+591', bayrak: '🇧🇴' },
    { ad: 'Bosna-Hersek', kod: '+387', bayrak: '🇧🇦' },
    { ad: 'Botsvana', kod: '+267', bayrak: '🇧🇼' },
    { ad: 'Brezilya', kod: '+55', bayrak: '🇧🇷' },
    { ad: 'Brunei', kod: '+673', bayrak: '🇧🇳' },
    { ad: 'Bulgaristan', kod: '+359', bayrak: '🇧🇬' },
    { ad: 'Burkina Faso', kod: '+226', bayrak: '🇧🇫' },
    { ad: 'Burundi', kod: '+257', bayrak: '🇧🇮' },
    { ad: 'Cebelitarık', kod: '+350', bayrak: '🇬🇮' },
    { ad: 'Cezayir', kod: '+213', bayrak: '🇩🇿' },
    { ad: 'Cibuti', kod: '+253', bayrak: '🇩🇯' },
    { ad: 'Çad', kod: '+235', bayrak: '🇹🇩' },
    { ad: 'Çekya', kod: '+420', bayrak: '🇨🇿' },
    { ad: 'Çin', kod: '+86', bayrak: '🇨🇳' },
    { ad: 'Danimarka', kod: '+45', bayrak: '🇩🇰' },
    { ad: 'Doğu Timor', kod: '+670', bayrak: '🇹🇱' },
    { ad: 'Dominik Cumhuriyeti', kod: '+1809', bayrak: '🇩🇴' },
    { ad: 'Dominika', kod: '+1767', bayrak: '🇩🇲' },
    { ad: 'Ekvador', kod: '+593', bayrak: '🇪🇨' },
    { ad: 'Ekvator Ginesi', kod: '+240', bayrak: '🇬🇶' },
    { ad: 'El Salvador', kod: '+503', bayrak: '🇸🇻' },
    { ad: 'Endonezya', kod: '+62', bayrak: '🇮🇩' },
    { ad: 'Eritre', kod: '+291', bayrak: '🇪🇷' },
    { ad: 'Ermenistan', kod: '+374', bayrak: '🇦🇲' },
    { ad: 'Estonya', kod: '+372', bayrak: '🇪🇪' },
    { ad: 'Esvatini', kod: '+268', bayrak: '🇸🇿' },
    { ad: 'Etiyopya', kod: '+251', bayrak: '🇪🇹' },
    { ad: 'Fas', kod: '+212', bayrak: '🇲🇦' },
    { ad: 'Fiji', kod: '+679', bayrak: '🇫🇯' },
    { ad: 'Fildişi Sahili', kod: '+225', bayrak: '🇨🇮' },
    { ad: 'Filipinler', kod: '+63', bayrak: '🇵🇭' },
    { ad: 'Filistin', kod: '+970', bayrak: '🇵🇸' },
    { ad: 'Finlandiya', kod: '+358', bayrak: '🇫🇮' },
    { ad: 'Fransa', kod: '+33', bayrak: '🇫🇷' },
    { ad: 'Fransız Guyanası', kod: '+594', bayrak: '🇬🇫' },
    { ad: 'Fransız Polinezyası', kod: '+689', bayrak: '🇵🇫' },
    { ad: 'Gabon', kod: '+241', bayrak: '🇬🇦' },
    { ad: 'Gambiya', kod: '+220', bayrak: '🇬🇲' },
    { ad: 'Gana', kod: '+233', bayrak: '🇬🇭' },
    { ad: 'Gine', kod: '+224', bayrak: '🇬🇳' },
    { ad: 'Gine-Bissau', kod: '+245', bayrak: '🇬🇼' },
    { ad: 'Grenada', kod: '+1473', bayrak: '🇬🇩' },
    { ad: 'Grönland', kod: '+299', bayrak: '🇬🇱' },
    { ad: 'Guadeloupe', kod: '+590', bayrak: '🇬🇵' },
    { ad: 'Guam', kod: '+1671', bayrak: '🇬🇺' },
    { ad: 'Guatemala', kod: '+502', bayrak: '🇬🇹' },
    { ad: 'Guyana', kod: '+592', bayrak: '🇬🇾' },
    { ad: 'Güney Afrika', kod: '+27', bayrak: '🇿🇦' },
    { ad: 'Güney Kore', kod: '+82', bayrak: '🇰🇷' },
    { ad: 'Güney Sudan', kod: '+211', bayrak: '🇸🇸' },
    { ad: 'Gürcistan', kod: '+995', bayrak: '🇬🇪' },
    { ad: 'Haiti', kod: '+509', bayrak: '🇭🇹' },
    { ad: 'Hindistan', kod: '+91', bayrak: '🇮🇳' },
    { ad: 'Hollanda', kod: '+31', bayrak: '🇳🇱' },
    { ad: 'Honduras', kod: '+504', bayrak: '🇭🇳' },
    { ad: 'Hong Kong', kod: '+852', bayrak: '🇭🇰' },
    { ad: 'Hırvatistan', kod: '+385', bayrak: '🇭🇷' },
    { ad: 'Irak', kod: '+964', bayrak: '🇮🇶' },
    { ad: 'İngiltere', kod: '+44', bayrak: '🇬🇧' },
    { ad: 'İran', kod: '+98', bayrak: '🇮🇷' },
    { ad: 'İrlanda', kod: '+353', bayrak: '🇮🇪' },
    { ad: 'İspanya', kod: '+34', bayrak: '🇪🇸' },
    { ad: 'İsrail', kod: '+972', bayrak: '🇮🇱' },
    { ad: 'İsveç', kod: '+46', bayrak: '🇸🇪' },
    { ad: 'İsviçre', kod: '+41', bayrak: '🇨🇭' },
    { ad: 'İtalya', kod: '+39', bayrak: '🇮🇹' },
    { ad: 'İzlanda', kod: '+354', bayrak: '🇮🇸' },
    { ad: 'Jamaika', kod: '+1876', bayrak: '🇯🇲' },
    { ad: 'Japonya', kod: '+81', bayrak: '🇯🇵' },
    { ad: 'Kamboçya', kod: '+855', bayrak: '🇰🇭' },
    { ad: 'Kamerun', kod: '+237', bayrak: '🇨🇲' },
    { ad: 'Karadağ', kod: '+382', bayrak: '🇲🇪' },
    { ad: 'Katar', kod: '+974', bayrak: '🇶🇦' },
    { ad: 'Kayman Adaları', kod: '+1345', bayrak: '🇰🇾' },
    { ad: 'Kazakistan', kod: '+7', bayrak: '🇰🇿' },
    { ad: 'Kenya', kod: '+254', bayrak: '🇰🇪' },
    { ad: 'Kırgızistan', kod: '+996', bayrak: '🇰🇬' },
    { ad: 'Kiribati', kod: '+686', bayrak: '🇰🇮' },
    { ad: 'K.K.T.C / G.Kıbrıs', kod: '+357', bayrak: '🇨🇾' },
    { ad: 'Kolombiya', kod: '+57', bayrak: '🇨🇴' },
    { ad: 'Komorlar', kod: '+269', bayrak: '🇰🇲' },
    { ad: 'Kongo Cumhuriyeti', kod: '+242', bayrak: '🇨🇬' },
    { ad: 'Kongo Dem. Cum.', kod: '+243', bayrak: '🇨🇩' },
    { ad: 'Kosova', kod: '+383', bayrak: '🇽🇰' },
    { ad: 'Kosta Rika', kod: '+506', bayrak: '🇨🇷' },
    { ad: 'Kuveyt', kod: '+965', bayrak: '🇰🇼' },
    { ad: 'Kuzey Kore', kod: '+850', bayrak: '🇰🇵' },
    { ad: 'K. Makedonya', kod: '+389', bayrak: '🇲🇰' },
    { ad: 'Küba', kod: '+53', bayrak: '🇨🇺' },
    { ad: 'Laos', kod: '+856', bayrak: '🇱🇦' },
    { ad: 'Lesotho', kod: '+266', bayrak: '🇱🇸' },
    { ad: 'Letonya', kod: '+371', bayrak: '🇱🇻' },
    { ad: 'Liberya', kod: '+231', bayrak: '🇱🇷' },
    { ad: 'Libya', kod: '+218', bayrak: '🇱🇾' },
    { ad: 'Liechtenstein', kod: '+423', bayrak: '🇱🇮' },
    { ad: 'Litvanya', kod: '+370', bayrak: '🇱🇹' },
    { ad: 'Lübnan', kod: '+961', bayrak: '🇱🇧' },
    { ad: 'Lüksemburg', kod: '+352', bayrak: '🇱🇺' },
    { ad: 'Macaristan', kod: '+36', bayrak: '🇭🇺' },
    { ad: 'Madagaskar', kod: '+261', bayrak: '🇲🇬' },
    { ad: 'Makao', kod: '+853', bayrak: '🇲🇴' },
    { ad: 'Malavi', kod: '+265', bayrak: '🇲🇼' },
    { ad: 'Maldivler', kod: '+960', bayrak: '🇲🇻' },
    { ad: 'Malezya', kod: '+60', bayrak: '🇲🇾' },
    { ad: 'Mali', kod: '+223', bayrak: '🇲🇱' },
    { ad: 'Malta', kod: '+356', bayrak: '🇲🇹' },
    { ad: 'Marshall Adaları', kod: '+692', bayrak: '🇲🇭' },
    { ad: 'Martinik', kod: '+596', bayrak: '🇲🇶' },
    { ad: 'Mauritius', kod: '+230', bayrak: '🇲🇺' },
    { ad: 'Meksika', kod: '+52', bayrak: '🇲🇽' },
    { ad: 'Mikronezya', kod: '+691', bayrak: '🇫🇲' },
    { ad: 'Moğolistan', kod: '+976', bayrak: '🇲🇳' },
    { ad: 'Moldova', kod: '+373', bayrak: '🇲🇩' },
    { ad: 'Monako', kod: '+377', bayrak: '🇲🇨' },
    { ad: 'Moritanya', kod: '+222', bayrak: '🇲🇷' },
    { ad: 'Mozambik', kod: '+258', bayrak: '🇲🇿' },
    { ad: 'Myanmar', kod: '+95', bayrak: '🇲🇲' },
    { ad: 'Mısır', kod: '+20', bayrak: '🇪🇬' },
    { ad: 'Namibya', kod: '+264', bayrak: '🇳🇦' },
    { ad: 'Nauru', kod: '+674', bayrak: '🇳🇷' },
    { ad: 'Nepal', kod: '+977', bayrak: '🇳🇵' },
    { ad: 'Nijer', kod: '+227', bayrak: '🇳🇪' },
    { ad: 'Nijerya', kod: '+234', bayrak: '🇳🇬' },
    { ad: 'Nikaragua', kod: '+505', bayrak: '🇳🇮' },
    { ad: 'Norveç', kod: '+47', bayrak: '🇳🇴' },
    { ad: 'Orta Afrika Cum.', kod: '+236', bayrak: '🇨🇫' },
    { ad: 'Özbekistan', kod: '+998', bayrak: '🇺🇿' },
    { ad: 'Pakistan', kod: '+92', bayrak: '🇵🇰' },
    { ad: 'Palau', kod: '+680', bayrak: '🇵🇼' },
    { ad: 'Panama', kod: '+507', bayrak: '🇵🇦' },
    { ad: 'Papua Yeni Gine', kod: '+675', bayrak: '🇵🇬' },
    { ad: 'Paraguay', kod: '+595', bayrak: '🇵🇾' },
    { ad: 'Peru', kod: '+51', bayrak: '🇵🇪' },
    { ad: 'Polonya', kod: '+48', bayrak: '🇵🇱' },
    { ad: 'Portekiz', kod: '+351', bayrak: '🇵🇹' },
    { ad: 'Porto Riko', kod: '+1787', bayrak: '🇵🇷' },
    { ad: 'Réunion', kod: '+262', bayrak: '🇷🇪' },
    { ad: 'Romanya', kod: '+40', bayrak: '🇷🇴' },
    { ad: 'Ruanda', kod: '+250', bayrak: '🇷🇼' },
    { ad: 'Rusya', kod: '+7', bayrak: '🇷🇺' },
    { ad: 'Samoa', kod: '+685', bayrak: '🇼🇸' },
    { ad: 'San Marino', kod: '+378', bayrak: '🇸🇲' },
    { ad: 'Sao Tome ve Principe', kod: '+239', bayrak: '🇸🇹' },
    { ad: 'Senegal', kod: '+221', bayrak: '🇸🇳' },
    { ad: 'Seyşeller', kod: '+248', bayrak: '🇸🇨' },
    { ad: 'Sırbistan', kod: '+381', bayrak: '🇷🇸' },
    { ad: 'Sierra Leone', kod: '+232', bayrak: '🇸🇱' },
    { ad: 'Singapur', kod: '+65', bayrak: '🇸🇬' },
    { ad: 'Slovakya', kod: '+421', bayrak: '🇸🇰' },
    { ad: 'Slovenya', kod: '+386', bayrak: '🇸🇮' },
    { ad: 'Solomon Adaları', kod: '+677', bayrak: '🇸🇧' },
    { ad: 'Somali', kod: '+252', bayrak: '🇸🇴' },
    { ad: 'Sri Lanka', kod: '+94', bayrak: '🇱🇰' },
    { ad: 'Sudan', kod: '+249', bayrak: '🇸🇩' },
    { ad: 'Suriname', kod: '+597', bayrak: '🇸🇷' },
    { ad: 'Suriye', kod: '+963', bayrak: '🇸🇾' },
    { ad: 'Suudi Arabistan', kod: '+966', bayrak: '🇸🇦' },
    { ad: 'Şili', kod: '+56', bayrak: '🇨🇱' },
    { ad: 'Tacikistan', kod: '+992', bayrak: '🇹🇯' },
    { ad: 'Tanzanya', kod: '+255', bayrak: '🇹🇿' },
    { ad: 'Tayland', kod: '+66', bayrak: '🇹🇭' },
    { ad: 'Tayvan', kod: '+886', bayrak: '🇹🇼' },
    { ad: 'Togo', kod: '+228', bayrak: '🇹🇬' },
    { ad: 'Tonga', kod: '+676', bayrak: '🇹🇴' },
    { ad: 'Trinidad ve Tobago', kod: '+1868', bayrak: '🇹🇹' },
    { ad: 'Tunus', kod: '+216', bayrak: '🇹🇳' },
    { ad: 'Türkiye', kod: '+90', bayrak: '🇹🇷' },
    { ad: 'Türkmenistan', kod: '+993', bayrak: '🇹🇲' },
    { ad: 'Uganda', kod: '+256', bayrak: '🇺🇬' },
    { ad: 'Ukrayna', kod: '+380', bayrak: '🇺🇦' },
    { ad: 'Umman', kod: '+968', bayrak: '🇴🇲' },
    { ad: 'Uruguay', kod: '+598', bayrak: '🇺🇾' },
    { ad: 'Ürdün', kod: '+962', bayrak: '🇯🇴' },
    { ad: 'Vanuatu', kod: '+678', bayrak: '🇻🇺' },
    { ad: 'Vatikan', kod: '+379', bayrak: '🇻🇦' },
    { ad: 'Venezuela', kod: '+58', bayrak: '🇻🇪' },
    { ad: 'Vietnam', kod: '+84', bayrak: '🇻🇳' },
    { ad: 'Yemen', kod: '+967', bayrak: '🇾🇪' },
    { ad: 'Yeni Kaledonya', kod: '+687', bayrak: '🇳🇨' },
    { ad: 'Yeni Zelanda', kod: '+64', bayrak: '🇳🇿' },
    { ad: 'Yeşil Burun (Cabo Verde)', kod: '+238', bayrak: '🇨🇻' },
    { ad: 'Yunanistan', kod: '+30', bayrak: '🇬🇷' },
    { ad: 'Zambiya', kod: '+260', bayrak: '🇿🇲' },
    { ad: 'Zimbabve', kod: '+263', bayrak: '🇿🇼' }
  ];

  function digits(s) { return (s || '').replace(/\D/g, ''); }
  function ulusal(s) { return digits(s).replace(/^0+/, ''); } // bastaki 0(lar) at

  // Turkce duyarli arama normalizasyonu ("Arnavut" -> "arnavut", "İngil" -> "ingil")
  function norm(s) {
    s = ('' + (s || '')).replace(/İ/g, 'i').replace(/I/g, 'ı').toLowerCase();
    return s.replace(/ı/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
            .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c')
            .replace(/â/g, 'a').replace(/é/g, 'e');
  }

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

  // kod -> ilk eslesen ulke kaydi (buton etiketi icin)
  function kayitBul(kod) {
    for (var i = 0; i < KODLAR.length; i++) if (KODLAR[i].kod === kod) return KODLAR[i];
    return null;
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
    var html = '', gorulen = {};
    for (var i = 0; i < KODLAR.length; i++) {
      var o = KODLAR[i];
      if (gorulen[o.kod]) continue;     // <select> icin kod basina tek option yeter
      gorulen[o.kod] = 1;
      html += '<option value="' + o.kod + '">' + o.bayrak + ' ' + o.kod + '</option>';
    }
    return html;
  }

  // ---- Aramali secici (native select gizlenir, degeri yine o tutar) ----
  var CSS_ID = 'tel-ulke-css';
  function _cssEkle() {
    if (document.getElementById(CSS_ID)) return;
    var st = document.createElement('style');
    st.id = CSS_ID;
    st.innerHTML =
      '.tel-ulke-box{position:relative;flex:0 0 auto;}' +
      '.tel-ulke-btn{display:flex;align-items:center;justify-content:space-between;gap:4px;width:100%;' +
        'background:#fff;border:1px solid #ced4da;border-radius:4px;padding:6px 8px;font-size:14px;' +
        'line-height:1.2;cursor:pointer;white-space:nowrap;color:#333;height:100%;}' +
      '.tel-ulke-btn:focus{outline:none;border-color:#5C008E;box-shadow:0 0 0 2px rgba(92,0,142,.15);}' +
      '.tel-ulke-btn .ok{font-size:10px;opacity:.6;}' +
      '.tel-ulke-pop{position:absolute;top:calc(100% + 4px);left:0;z-index:99999;width:290px;max-width:80vw;' +
        'background:#fff;border:1px solid #d9d9d9;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.18);' +
        'padding:8px;display:none;}' +
      '.tel-ulke-pop.acik{display:block;}' +
      '.tel-ulke-ara{width:100%;box-sizing:border-box;border:1px solid #ced4da;border-radius:6px;' +
        'padding:7px 9px;font-size:14px;margin-bottom:6px;}' +
      '.tel-ulke-liste{max-height:260px;overflow-y:auto;}' +
      '.tel-ulke-bas{font-size:11px;font-weight:700;color:#8a8a8a;text-transform:uppercase;' +
        'padding:6px 8px 4px;letter-spacing:.4px;}' +
      '.tel-ulke-sat{display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:6px;cursor:pointer;font-size:14px;}' +
      '.tel-ulke-sat:hover,.tel-ulke-sat.vurgu{background:#f2ecf7;}' +
      '.tel-ulke-sat.secili{background:#5C008E;color:#fff;}' +
      '.tel-ulke-sat .ad{flex:1 1 auto;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}' +
      '.tel-ulke-sat .kod{flex:0 0 auto;color:#777;font-variant-numeric:tabular-nums;}' +
      '.tel-ulke-sat.secili .kod{color:#e6d5f0;}' +
      '.tel-ulke-yok{padding:10px 8px;color:#999;font-size:13px;}';
    document.head.appendChild(st);
  }

  function _listeHtml() {
    var html = '', i, o;
    html += '<div class="tel-ulke-bas">Sık kullanılan</div>';
    for (i = 0; i < KODLAR.length; i++) {
      o = KODLAR[i];
      if (!o.one) continue;
      html += _satirHtml(o, i);
    }
    html += '<div class="tel-ulke-bas tumu">Tüm ülkeler</div>';
    for (i = 0; i < KODLAR.length; i++) {
      o = KODLAR[i];
      if (o.one) continue;
      html += _satirHtml(o, i);
    }
    return html;
  }

  function _satirHtml(o, i) {
    return '<div class="tel-ulke-sat" data-kod="' + o.kod + '" data-ara="' +
      norm(o.ad) + ' ' + o.kod.replace('+', '') + '">' +
      '<span class="bayrak">' + o.bayrak + '</span>' +
      '<span class="ad">' + o.ad + '</span>' +
      '<span class="kod">' + o.kod + '</span></div>';
  }

  function _btnEtiket(kod) {
    var k = kayitBul(kod);
    return '<span>' + (k ? k.bayrak + ' ' + k.kod : kod) + '</span><span class="ok">▼</span>';
  }

  var acikPop = null;
  document.addEventListener('click', function (e) {
    if (!acikPop) return;
    if (acikPop.box.contains(e.target)) return;
    _kapat();
  });
  document.addEventListener('keydown', function (e) {
    if (acikPop && e.key === 'Escape') _kapat();
  });
  function _kapat() {
    if (!acikPop) return;
    acikPop.pop.classList.remove('acik');
    acikPop = null;
  }

  // Native select'i gizleyip yerine aramali buton kur
  function _seciciKur(root, sel) {
    if (sel.getAttribute('data-tel-ui') === '1') return;
    _cssEkle();

    var box = document.createElement('div');
    box.className = 'tel-ulke-box';
    box.style.cssText = sel.style.cssText;   // blade'deki flex/max-width korunur
    sel.parentNode.insertBefore(box, sel);
    sel.style.display = 'none';
    sel.setAttribute('data-tel-ui', '1');

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'tel-ulke-btn';
    btn.innerHTML = _btnEtiket(sel.value || '+90');
    box.appendChild(btn);

    var pop = document.createElement('div');
    pop.className = 'tel-ulke-pop';
    pop.innerHTML =
      '<input type="text" class="tel-ulke-ara" placeholder="Ülke veya kod ara..." autocomplete="off">' +
      '<div class="tel-ulke-liste">' + _listeHtml() + '</div>';
    box.appendChild(pop);

    var ara = pop.querySelector('.tel-ulke-ara');
    var liste = pop.querySelector('.tel-ulke-liste');

    function isaretle() {
      var satlar = liste.querySelectorAll('.tel-ulke-sat');
      for (var i = 0; i < satlar.length; i++) {
        satlar[i].classList.toggle('secili', satlar[i].getAttribute('data-kod') === sel.value);
      }
    }

    function filtrele() {
      var q = norm(ara.value.replace('+', '')).trim();
      var satlar = liste.querySelectorAll('.tel-ulke-sat');
      var bulunan = 0;
      for (var i = 0; i < satlar.length; i++) {
        var uyar = !q || satlar[i].getAttribute('data-ara').indexOf(q) !== -1;
        satlar[i].style.display = uyar ? '' : 'none';
        if (uyar) bulunan++;
      }
      // arama varken grup basliklarini gizle
      var bas = liste.querySelectorAll('.tel-ulke-bas');
      for (var j = 0; j < bas.length; j++) bas[j].style.display = q ? 'none' : '';
      var yok = liste.querySelector('.tel-ulke-yok');
      if (!bulunan && !yok) {
        yok = document.createElement('div');
        yok.className = 'tel-ulke-yok';
        yok.textContent = 'Sonuç yok';
        liste.appendChild(yok);
      }
      if (yok) yok.style.display = bulunan ? 'none' : '';
    }

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (acikPop && acikPop.pop === pop) { _kapat(); return; }
      _kapat();
      ara.value = '';
      filtrele();
      isaretle();
      pop.classList.add('acik');
      acikPop = { box: box, pop: pop };
      var s = liste.querySelector('.tel-ulke-sat.secili');
      if (s) liste.scrollTop = Math.max(0, s.offsetTop - 60);
      try { ara.focus(); } catch (err) {}
    });

    ara.addEventListener('input', filtrele);
    ara.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      var satlar = liste.querySelectorAll('.tel-ulke-sat');
      for (var i = 0; i < satlar.length; i++) {
        if (satlar[i].style.display !== 'none') { sec(satlar[i].getAttribute('data-kod')); return; }
      }
    });

    liste.addEventListener('click', function (e) {
      var sat = e.target.closest ? e.target.closest('.tel-ulke-sat') : null;
      if (!sat) return;
      sec(sat.getAttribute('data-kod'));
    });

    function sec(kod) {
      sel.value = kod;
      btn.innerHTML = _btnEtiket(kod);
      _kapat();
      // eski dinleyiciler (numara bicimleme, hidden guncelleme) tetiklensin
      var ev;
      try { ev = new Event('change', { bubbles: true }); }
      catch (err) { ev = document.createEvent('Event'); ev.initEvent('change', true, true); }
      sel.dispatchEvent(ev);
      var num = root.querySelector('.tel-num');
      if (num) try { num.focus(); } catch (err2) {}
    }

    sel._telBtnGuncelle = function () { btn.innerHTML = _btnEtiket(sel.value); };
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

    _seciciKur(root, sel);

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
    if (sel._telBtnGuncelle) sel._telBtnGuncelle();
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
