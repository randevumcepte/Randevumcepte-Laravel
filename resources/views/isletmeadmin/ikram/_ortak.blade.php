{{-- Ikram Hizmet web ekranlarinin ortak gorunumu + yardimcilari (mobil uygulama tasarimiyla ayni palet).
     Her ekran @include('isletmeadmin.ikram._ortak') ile alir; tekrar eden CSS/JS yalniz burada. --}}
<style>
.ik-sayfa { --ik-koyu:#2E1065; --ik-mor:#7C3AED; --ik-mor2:#5B21B6; --ik-soluk:#6B5A8E;
    position:relative; min-height:calc(100vh - 90px); padding:18px 18px 120px; margin:-20px -20px 0;
    background:linear-gradient(180deg,#E9DAF8 0%,#F5EDFC 55%,#EFE3FA 100%); color:var(--ik-koyu); overflow:hidden; }
.ik-sayfa * { box-sizing:border-box; }
.ik-ic { position:relative; z-index:1; max-width:1080px; margin:0 auto; }
.ik-yaprak { position:absolute; bottom:0; opacity:.85; pointer-events:none; z-index:0; }
.ik-yaprak.sol { left:0; width:110px; } .ik-yaprak.sag { right:0; width:100px; }
.ik-ust { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
.ik-ust h1 { flex:1; margin:0; text-align:center; font-size:21px; font-weight:800; color:var(--ik-koyu); }
.ik-yuv { width:44px; height:44px; border-radius:50%; border:1px solid #E4D7F7; background:rgba(255,255,255,.75);
    display:flex; align-items:center; justify-content:center; color:var(--ik-koyu); cursor:pointer; text-decoration:none; flex-shrink:0; }
.ik-yuv:hover { background:#fff; color:var(--ik-mor); text-decoration:none; }
.ik-cam { background:rgba(255,255,255,.62); border:1.2px solid #fff; border-radius:24px; padding:18px;
    box-shadow:0 8px 24px rgba(124,58,237,.08); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); margin-bottom:16px; }
.ik-baslik { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.ik-baslik .ik-ikon { width:44px; height:44px; border-radius:14px; background:linear-gradient(135deg,#F3E8FF,#DDD0FB);
    display:flex; align-items:center; justify-content:center; color:var(--ik-mor2); font-size:20px; flex-shrink:0; }
.ik-baslik h3 { margin:0; font-size:16px; font-weight:800; color:var(--ik-koyu); }
.ik-baslik p { margin:2px 0 0; font-size:12.5px; color:var(--ik-soluk); }
.ik-btn-ana { display:flex; align-items:center; justify-content:center; gap:10px; width:100%; height:50px; border:0; border-radius:26px;
    background:linear-gradient(90deg,#A855F7,#7C3AED,#5B21B6); color:#fff; font-weight:800; font-size:15px; cursor:pointer; text-decoration:none; }
.ik-btn-ana:hover { color:#fff; text-decoration:none; filter:brightness(1.05); }
.ik-btn-ana:disabled { opacity:.6; cursor:default; }
.ik-btn-ana .ik-arti { width:24px; height:24px; border-radius:50%; background:#fff; color:var(--ik-mor); display:flex; align-items:center; justify-content:center; font-size:13px; }
.ik-rozet { position:absolute; top:0; left:0; background:linear-gradient(135deg,#7C3AED,#5B21B6); color:#fff; font-weight:900;
    font-size:12px; padding:3px 8px; border-radius:10px 0 10px 0; }
.ik-cip { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:800; }
.ik-cip:before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; }
.ik-cip.aktif { color:#15803D; background:#DCFCE7; } .ik-cip.baslayacak { color:#1D4ED8; background:#DBEAFE; }
.ik-cip.bitti, .ik-cip.pasif { color:#6B7280; background:#F3F4F6; }
.ik-hap { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:20px; background:rgba(255,255,255,.7);
    border:1px solid #D8C8F3; color:var(--ik-koyu); font-weight:700; font-size:13px; cursor:pointer; }
.ik-menu { position:relative; display:inline-block; }
.ik-menu-liste { display:none; position:absolute; right:0; top:calc(100% + 6px); min-width:190px; background:#fff; border-radius:14px;
    box-shadow:0 12px 32px rgba(46,16,101,.18); padding:6px; z-index:30; }
.ik-menu.acik .ik-menu-liste { display:block; }
.ik-menu-liste a { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:10px; color:var(--ik-koyu);
    font-size:13.5px; font-weight:600; cursor:pointer; text-decoration:none; }
.ik-menu-liste a:hover { background:#F5EDFC; text-decoration:none; }
.ik-menu-liste a.sec { font-weight:900; color:var(--ik-mor); }
.ik-menu-liste a.kirmizi { color:#dc2626; }
.ik-bos { text-align:center; color:var(--ik-soluk); padding:30px 12px; font-size:13.5px; line-height:1.6; white-space:pre-line; }
.ik-yukleniyor { text-align:center; padding:40px 0; color:var(--ik-mor); }
.ik-sayi { font-variant-numeric:tabular-nums; }
#ik-toast { position:fixed; left:50%; bottom:28px; transform:translateX(-50%); z-index:200000; padding:12px 20px; border-radius:12px;
    background:#5B21B6; color:#fff; font-weight:700; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none; max-width:90vw; text-align:center; }
#ik-toast.goster { display:block; } #ik-toast.hata { background:#dc2626; }
@media (max-width:600px) { .ik-sayfa { padding:12px 10px 120px; margin:-15px -15px 0; } .ik-ust h1 { font-size:18px; } }
</style>
<div id="ik-toast"></div>
<script>
/* ---- Ikram ortak yardimcilari (mobil ikram_hizmet.dart ile ayni kurallar) ---- */
var IK = (function () {
    var SUBE = {!! json_encode(isset($_GET['sube']) ? (string) $isletme->id : '') !!};
    var CSRF = '{{ csrf_token() }}';
    var GORSEL = '{{ secure_asset('public/img/ikram') }}/';

    /** /isletmeyonetim/... adresi + sube parametresi */
    function u(yol, params) {
        var q = [];
        if (SUBE) q.push('sube=' + encodeURIComponent(SUBE));
        Object.keys(params || {}).forEach(function (k) { q.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k])); });
        return '/isletmeyonetim/' + yol + (q.length ? '?' + q.join('&') : '');
    }
    /** 24750.5 -> "24.751" (tam sayi, nokta binlik) */
    function tl(v) {
        var n = Math.round(Number(v) || 0);
        return (n < 0 ? '-' : '') + String(Math.abs(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function tarihParse(t) {
        if (!t) return null;
        var m = String(t).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
        return m ? { y: m[1], a: m[2], g: m[3], s: m[4], d: m[5] } : null;
    }
    function tarih(t) { var p = tarihParse(t); return p ? p.g + '.' + p.a + '.' + p.y : ''; }
    function tarihSaat(t) {
        var p = tarihParse(t);
        if (!p) return '';
        return p.g + '.' + p.a + '.' + p.y + (p.s && !(p.s === '00' && p.d === '00') ? ' • ' + p.s + ':' + p.d : '');
    }
    function bugun() { var d = new Date(); return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); }
    /** aktif | baslayacak | bitti | pasif */
    function durum(k) {
        var b = bugun();
        if (k.bitis_tarihi && String(k.bitis_tarihi).slice(0, 10) < b) return 'bitti';
        if (Number(k.aktif) !== 1) return 'pasif';
        if (k.baslangic && String(k.baslangic).slice(0, 10) > b) return 'baslayacak';
        return 'aktif';
    }
    var DURUM_YAZI = { aktif: 'Aktif', baslayacak: 'Başlayacak', bitti: 'Bitti', pasif: 'Pasif' };
    /** % artis/azalis metni */
    function artisYazi(a) {
        var x = Math.abs(Number(a));
        var s = (x < 10 && x % 1 !== 0) ? x.toFixed(1).replace('.', ',') : String(Math.round(x));
        return '%' + s + ' ' + (Number(a) >= 0 ? 'artış' : 'azalış');
    }
    /** Hizmet adina uygun hazir kampanya gorseli (ilk eslesen kazanir; sira onemli) */
    var KURALLAR = [
        [['pedikür', 'pedikur'], 'ikram_k_pedikur.jpg'],
        [['ayak', 'topuk', 'nasır', 'nasir', 'batık', 'batik', 'mantar'], 'ikram_foto_ayak.png'],
        [['kalıcı makyaj', 'kalici makyaj', 'dudak', 'liner', 'microblading', 'pudra kaş', 'pudra kas'], 'ikram_k_dudak.jpg'],
        [['kirpik', 'lash'], 'ikram_k_kirpik.jpg'],
        [['kaş', 'brow'], 'ikram_k_kas.jpg'],
        [['lazer', 'epilasyon', 'ağda', 'agda', 'iğneli', 'igneli', 'bıyık', 'biyik', 'tüy alma'], 'ikram_k_lazer.jpg'],
        [['tırnak', 'tirnak', 'oje', 'manikür', 'manikur', 'nail', 'jel', 'protez', 'french', 'inci tozu', 'kedi gözü', 'kedi gozu', 'şablon', 'sablon'], 'ikram_k_tirnak.jpg'],
        [['saç', 'sac ', 'fön', 'boya', 'kesim', 'keratin', 'perma', 'röfle', 'rofle', 'balyaj', 'ombre', 'topuz', 'maşa', 'brezilya', 'gelin başı'], 'ikram_k_sac.jpg'],
        [['cilt', 'yüz', 'yuz', 'hydra', 'facial', 'peeling', 'leke', 'akne', 'bakım', 'bakim', 'dermapen', 'mezoterapi'], 'ikram_k_cilt.jpg'],
        [['masaj', 'spa', 'taş', 'aroma', 'refleks', 'sauna', 'hamam', 'kese'], 'ikram_k_masaj.jpg'],
    ];
    function hazirGorsel(hizmetAdi) {
        // Mobil ile ayni: dilden bagimsiz kucultme ('I'->'i', 'İ'->'i'+U+0307, sonra nokta silinir)
        var ad = String(hizmetAdi || '').toLowerCase().replace(/\u0307/g, '');
        if (ad) for (var i = 0; i < KURALLAR.length; i++) {
            for (var j = 0; j < KURALLAR[i][0].length; j++) if (ad.indexOf(KURALLAR[i][0][j]) !== -1) return GORSEL + KURALLAR[i][1];
        }
        return GORSEL + 'ikram_foto_spa.png';
    }
    /** Kampanyanin gorseli: yuklenen foto, yoksa (ya da yuklenemezse) hazir gorsel */
    function gorselImg(k, sinif) {
        var hazir = hazirGorsel(k.hizmet_adi);
        var src = k.gorsel ? k.gorsel : hazir;
        return '<img class="' + (sinif || '') + '" src="' + esc(src) + '" alt="" onerror="this.onerror=null;this.src=\'' + hazir + '\'">';
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function toast(msg, hata) {
        var t = document.getElementById('ik-toast');
        t.textContent = msg;
        t.className = 'goster' + (hata ? ' hata' : '');
        clearTimeout(t._z);
        t._z = setTimeout(function () { t.className = ''; }, 2800);
    }
    /** SweetAlert2 v7 onay; yoksa confirm */
    function onay(baslik, metin, evet, renk) {
        if (!window.swal) return Promise.resolve(confirm(baslik + '\n\n' + metin));
        return swal({ title: baslik, text: metin, type: 'warning', showCancelButton: true, reverseButtons: true, focusCancel: true,
            confirmButtonText: evet || 'Evet', confirmButtonColor: renk || '#7C3AED', cancelButtonText: 'Vazgeç', cancelButtonColor: '#9ca3af',
        }).then(function (r) { return !!(r && r.value); }, function () { return false; });
    }
    function bilgi(baslik, html) {
        if (!window.swal) { alert(baslik); return; }
        swal({ title: baslik, html: '<div style="text-align:left;font-size:14px;line-height:1.6;color:#374151;">' + html + '</div>',
            confirmButtonText: 'Tamam', confirmButtonColor: '#7C3AED' });
    }
    function getJson(url) {
        return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); });
    }
    function postJson(url, veri) {
        return fetch(url, { method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(veri) }).then(function (r) { return r.json(); });
    }
    function postForm(url, fd) {
        return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd })
            .then(function (r) { return r.json(); });
    }
    /** Ac/Kapat: kuralin kendi degerleriyle yalniz aktif degisir (mobil ile ayni yuk) */
    function acKapat(k) {
        return postJson(u('damgakartikaydet'), {
            id: k.id, hizmet_id: k.hizmet_id, gereken_adet: k.gereken_adet, ucretsiz_adet: k.ucretsiz_adet,
            aktif: Number(k.aktif) === 1 ? 0 : 1, gecerlilik_deger: k.gecerlilik_deger || 0, gecerlilik_birim: k.gecerlilik_birim || 'ay',
            baslangic_tarihi: k.baslangic ? String(k.baslangic).slice(0, 10) : '', bitis_tarihi: k.bitis_tarihi || '',
        });
    }
    function sil(k) {
        return onay('Kampanyayı Sil', '"' + (k.hizmet_adi || 'Hizmet') + '" kampanyası silinsin mi?', 'Sil', '#dc2626').then(function (e) {
            return e ? postJson(u('damgakartisil'), { id: k.id }) : null;
        });
    }
    /** Ac-kapat menu: .ik-menu tiklaninca acilir, disari tiklaninca kapanir */
    document.addEventListener('click', function (e) {
        var m = e.target.closest ? e.target.closest('.ik-menu') : null;
        document.querySelectorAll('.ik-menu.acik').forEach(function (x) { if (x !== m) x.classList.remove('acik'); });
        if (m && e.target.closest('.ik-menu-ac')) { m.classList.toggle('acik'); e.preventDefault(); }
    });
    var DONEMLER = [[7, 'Son 7 Gün'], [30, 'Son 30 Gün'], [90, 'Son 90 Gün'], [365, 'Son 1 Yıl']];
    function donemYazi(d) { for (var i = 0; i < DONEMLER.length; i++) if (DONEMLER[i][0] === d) return DONEMLER[i][1]; return 'Son ' + d + ' Gün'; }

    return { u: u, tl: tl, tarih: tarih, tarihSaat: tarihSaat, bugun: bugun, durum: durum, DURUM_YAZI: DURUM_YAZI, artisYazi: artisYazi,
        hazirGorsel: hazirGorsel, gorselImg: gorselImg, esc: esc, toast: toast, onay: onay, bilgi: bilgi, getJson: getJson,
        postJson: postJson, postForm: postForm, acKapat: acKapat, sil: sil, DONEMLER: DONEMLER, donemYazi: donemYazi, GORSEL: GORSEL };
})();
</script>
