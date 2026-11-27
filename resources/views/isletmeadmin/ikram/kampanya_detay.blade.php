@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Kampanya Detayi — mobil kampanya_detay.dart ile ayni bolumler. Veri /isletmeyonetim/ikramhizmet/detay-veri. --}}
@include('isletmeadmin.ikram._ortak')
@include('isletmeadmin.ikram._kampanya_modal')
<style>
.ikd-hero { position:relative; width:100%; aspect-ratio:941/404; border-radius:24px; overflow:hidden; margin-bottom:16px; background:#EDE3FD; container-type:inline-size; }
.ikd-hero .ikd-foto { position:absolute; left:37.2%; top:0; right:0; bottom:0; }
.ikd-hero .ikd-foto img { width:100%; height:100%; object-fit:cover;
    -webkit-mask-image:linear-gradient(90deg,transparent 0,#000 32%), linear-gradient(180deg,#000 86%,transparent 100%);
    -webkit-mask-composite:source-in; mask-image:linear-gradient(90deg,transparent 0,#000 32%); }
.ikd-panel { position:absolute; left:3%; top:0; bottom:0; width:66%; border-radius:24px 0 0 24px; padding:5% 5% 5% 6%; color:#fff;
    background:linear-gradient(90deg,#4C1D95 0%,rgba(91,33,182,.98) 52%,rgba(124,58,237,.62) 70%,rgba(167,139,250,.22) 86%,rgba(196,181,253,0) 100%); }
.ikd-panel:after { content:''; position:absolute; right:12%; top:0; width:40%; height:45%; background:radial-gradient(circle at 70% 20%,rgba(255,255,255,.25),transparent 60%); pointer-events:none; }
.ikd-durum { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:14px; background:rgba(255,255,255,.18);
    font-size:max(10px,1.4cqw); font-weight:800; }
.ikd-buyuk { font-size:6.8cqw; font-weight:900; line-height:1; margin:4% 0 2%; }
.ikd-ad { font-size:2.5cqw; font-weight:800; line-height:1.2; white-space:pre-line; }
.ikd-alt { font-size:max(11px,1.6cqw); opacity:.95; margin-top:2%; white-space:pre-line; }
.ikd-uc { display:grid; grid-template-columns:repeat(3,1fr); background:rgba(255,255,255,.75); border:1px solid #E6DAF8; border-radius:18px; }
.ikd-uc > div { padding:14px 10px; text-align:center; border-left:1px solid #E4D7F7; }
.ikd-uc > div:first-child { border-left:0; }
.ikd-uc i { color:#7C3AED; font-size:18px; } .ikd-uc small { display:block; color:#5B21B6; font-weight:700; font-size:12px; margin:4px 0 2px; }
.ikd-uc b { font-size:17px; font-weight:800; color:#2E1065; }
.ikd-halka { width:86px; height:86px; margin:0 auto; position:relative; }
.ikd-halka span { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:17px; color:#2E1065; }
.ikd-sekmeler { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.ikd-sekme { flex:1; min-width:180px; display:flex; align-items:center; justify-content:center; gap:8px; padding:11px; border-radius:16px;
    background:rgba(255,255,255,.7); border:1px solid #E6DAF8; color:#2E1065; font-weight:800; cursor:pointer; }
.ikd-sekme.sec { background:linear-gradient(90deg,#7C3AED,#5B21B6); color:#fff; border-color:transparent; }
.ikd-ara { display:flex; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.ikd-ara input { flex:1; min-width:180px; height:42px; border-radius:21px; border:1px solid #E2D4F8; padding:0 16px; background:#fff; }
.ikd-musteri { display:flex; align-items:center; gap:12px; padding:10px; border-radius:16px; background:#fff; margin-bottom:8px; cursor:pointer; }
.ikd-musteri:hover { box-shadow:0 4px 14px rgba(124,58,237,.12); }
.ikd-av { width:42px; height:42px; border-radius:50%; background:#EDE3FD; color:#5B21B6; font-weight:800; display:flex; align-items:center;
    justify-content:center; overflow:hidden; flex-shrink:0; }
.ikd-av img { width:100%; height:100%; object-fit:cover; }
.ikd-cip { padding:4px 10px; border-radius:12px; font-size:11.5px; font-weight:800; white-space:nowrap; }
.ikd-cip.hak_var { color:#5B21B6; background:#EDE9FE; } .ikd-cip.kullandi { color:#1D4ED8; background:#DBEAFE; }
.ikd-cip.katildi { color:#15803D; background:#DCFCE7; }
.ikd-kutular { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
.ikd-kutular > div { background:#fff; border-radius:16px; padding:12px; } .ikd-kutular small { color:#6B5A8E; font-size:12px; white-space:pre-line; display:block; }
.ikd-kutular b { font-size:18px; font-weight:900; }
.ikd-grafik { display:flex; align-items:flex-end; gap:8px; height:210px; padding:10px 4px 0; overflow-x:auto; }
.ikd-cubuk { flex:1; min-width:34px; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; }
.ikd-cubuk i { display:block; width:70%; border-radius:6px; background:linear-gradient(180deg,#A855F7,#5B21B6); min-height:4px; }
.ikd-cubuk small { font-size:10px; color:#6B5A8E; margin-top:4px; } .ikd-cubuk em { font-style:normal; font-size:10px; color:#2E1065; font-weight:700; margin-bottom:3px; white-space:nowrap; }
.ikd-kul { display:flex; align-items:center; gap:10px; padding:9px 0; border-bottom:1px dashed #EDE4FA; font-size:13.5px; }
.ikd-modal { display:none; position:fixed; inset:0; background:rgba(46,16,101,.45); z-index:100000; align-items:flex-end; justify-content:center; }
.ikd-modal.acik { display:flex; }
.ikd-modal-ic { background:#fff; width:100%; max-width:560px; border-radius:24px 24px 0 0; padding:22px; max-height:88vh; overflow:auto; }
@media (min-width:700px) { .ikd-modal { align-items:center; } .ikd-modal-ic { border-radius:24px; } }
.ikd-damga { display:flex; flex-wrap:wrap; gap:6px; margin:8px 0 12px; }
.ikd-damga span { width:26px; height:26px; border-radius:50%; background:#F1E9FD; border:1px solid #DCCBF6; display:flex; align-items:center; justify-content:center; font-size:12px; }
.ikd-damga span.dolu { background:#7C3AED; border-color:#7C3AED; color:#fff; }
.ikd-mini { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:12px; }
.ikd-mini div { background:#F6F0FF; border-radius:12px; padding:8px; text-align:center; } .ikd-mini b { display:block; font-size:17px; color:#2E1065; }
.ikd-mini small { font-size:11px; color:#6B5A8E; }
@media (max-width:600px) { .ikd-kutular { grid-template-columns:1fr; } .ikd-uc b { font-size:15px; } }
.ikd-ust-izgara > * { margin-bottom:16px; }
.ikd-yan > .ik-cam { margin-bottom:16px; }
/* Masaustu: kampanya gorseli solda, Bilgiler + Performans sagda */
@media (min-width:992px) {
    .ik-sayfa .ik-ic.ikd-ic { max-width:1320px; }
    .ik-ust h1 { text-align:left; font-size:24px; }
    .ikd-ust-izgara { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(360px,1fr); gap:20px; align-items:stretch; margin-bottom:20px; }
    .ikd-ust-izgara > * { margin-bottom:0; }
    .ikd-hero { margin-bottom:0; align-self:start; }
    .ikd-yan { display:flex; flex-direction:column; gap:20px; }
    .ikd-yan > .ik-cam { margin:0; flex:1; }
    .ikd-alt-izgara { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:20px; align-items:start; }
    .ikd-alt-izgara > .ikd-sag { position:sticky; top:100px; }
    .ikd-sag .ik-cam { flex-direction:column; text-align:center; }
}
</style>

<div class="ik-sayfa">
    <img class="ik-yaprak sol" src="{{ secure_asset('public/img/ikram/kampanya_yaprak_sol.png') }}" alt="">
    <img class="ik-yaprak sag" src="{{ secure_asset('public/img/ikram/kampanya_yaprak_sag.png') }}" alt="">
    <div class="ik-ic ikd-ic" style="max-width:940px">
        <div class="ik-ust">
            <a class="ik-yuv" id="ik-geri" title="Geri"><i class="fa fa-arrow-left"></i></a>
            <h1>Kampanya Detayı</h1>
            <div class="ik-menu"><a class="ik-yuv ik-menu-ac" title="İşlemler"><i class="fa fa-ellipsis-v ik-menu-ac"></i></a>
                <div class="ik-menu-liste">
                    <a data-islem="duzenle"><i class="fa fa-pencil"></i> Düzenle</a>
                    <a data-islem="ackapat"><i class="fa fa-pause" id="ikd-ak-ikon"></i> <span id="ikd-ak-yazi">Kampanyayı Kapat</span></a>
                    <a data-islem="sil" class="kirmizi"><i class="fa fa-trash-o"></i> Sil</a>
                </div>
            </div>
        </div>

        <div class="ikd-ust-izgara">
        <div class="ikd-hero">
            <div class="ikd-foto" id="ikd-foto"></div>
            <div class="ikd-panel">
                <span class="ikd-durum" id="ikd-durum"><i class="fa fa-star"></i> …</span>
                <div class="ikd-buyuk" id="ikd-buyuk">…</div>
                <div class="ikd-ad" id="ikd-ad"></div>
                <div class="ikd-alt" id="ikd-alt"></div>
            </div>
        </div>

        <div class="ikd-yan">
        <div class="ik-cam">
            <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-gift"></i></div><div><h3>Kampanya Bilgileri</h3></div></div>
            <div class="ikd-uc">
                <div><i class="fa fa-tag"></i><small>Hizmet Adedi</small><b id="ikd-adet">…</b></div>
                <div><i class="fa fa-gift"></i><small>Hediye Hizmet</small><b id="ikd-hediye">…</b></div>
                <div><i class="fa fa-calendar"></i><small>Geçerlilik Tarihi</small><b id="ikd-tarih" style="font-size:14px">…</b>
                    <div id="ikd-hak-sure" style="font-size:11px;color:#6B5A8E;margin-top:2px"></div></div>
            </div>
        </div>

        <div class="ik-cam">
            <div class="ik-baslik" style="flex-wrap:wrap">
                <div class="ik-ikon"><i class="fa fa-bar-chart"></i></div><div style="flex:1"><h3>Kampanya Performansı</h3></div>
                <div class="ik-menu"><span class="ik-hap ik-menu-ac"><span id="ikd-donem-yazi" class="ik-menu-ac">Son 30 Gün</span> <i class="fa fa-chevron-down ik-menu-ac" style="font-size:10px"></i></span>
                    <div class="ik-menu-liste" id="ikd-donem-liste"></div></div>
            </div>
            <div class="ikd-uc">
                <div><i class="fa fa-users"></i><small>Katılan Müşteri</small><b class="ik-sayi" id="ikd-katilan">…</b></div>
                <div><i class="fa fa-handshake-o"></i><small>Elde Edilen Kazanç</small><b class="ik-sayi" id="ikd-kazanc">…</b></div>
                <div><small style="margin-top:0">Kullanım Oranı</small><div class="ikd-halka" id="ikd-halka"></div></div>
            </div>
        </div>

        </div>
        </div>

        <div class="ikd-alt-izgara">
        <div class="ik-cam">
            <div class="ikd-sekmeler">
                <div class="ikd-sekme sec" data-sekme="0"><i class="fa fa-users"></i> <span id="ikd-sekme0">Katılan Müşteriler</span></div>
                <div class="ikd-sekme" data-sekme="1"><i class="fa fa-bar-chart"></i> Kazanç Detayları</div>
            </div>
            <div id="ikd-icerik"><div class="ik-yukleniyor"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
        </div>

        <div class="ikd-sag">
        <div class="ik-cam" style="display:flex;align-items:center;gap:14px">
            <i class="fa fa-bullhorn" style="font-size:24px;color:#7C3AED"></i>
            <div style="flex:1;color:#5B21B6;font-size:14px" id="ikd-ozet"></div>
            <i class="fa fa-line-chart" style="font-size:22px;color:#7C3AED"></i>
        </div>

        <a class="ik-btn-ana" id="ikd-rapor" style="height:58px;background:linear-gradient(90deg,#7C3AED,#5B21B6,#4C1D95)"><i class="fa fa-bar-chart"></i> Raporu Görüntüle <i class="fa fa-chevron-right"></i></a>
        </div>
        </div>
    </div>
</div>

<div class="ikd-modal" id="ikd-modal"><div class="ikd-modal-ic" id="ikd-modal-ic"></div></div>

<script>
(function () {
    var ID = {{ (int) $ikramKuralId }};
    var params = new URLSearchParams(location.search);
    var donem = parseInt(params.get('donem'), 10) || 30;
    var veri = null, sekme = 0, ara = '', filtre = 'tumu';
    var FILTRELER = [['tumu', 'Tümünü Gör'], ['hak_var', 'Hakkı Olanlar'], ['kullandi', 'Kullananlar'], ['katildi', 'Devam Edenler']];
    var DURUM = { aktif: ['Aktif Kampanya', '#fff'], baslayacak: ['Yakında Başlıyor', '#BFDBFE'], bitti: ['Kampanya Bitti', '#E5E7EB'], pasif: ['Pasif Kampanya', '#E5E7EB'] };
    var MCIP = { hak_var: 'İkram Hakkı Var', kullandi: 'İkram Kullandı', katildi: 'Kampanyaya Katıldı' };
    var AYLAR = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
    var $ = function (id) { return document.getElementById(id); };

    $('ik-geri').addEventListener('click', function () { location.href = IK.u('ikramhizmet'); });
    $('ikd-rapor').addEventListener('click', function () { location.href = IK.u('ikramhizmet/rapor', { donem: donem }); });

    function donemMenu() {
        $('ikd-donem-yazi').textContent = IK.donemYazi(donem);
        $('ikd-donem-liste').innerHTML = IK.DONEMLER.map(function (d) {
            return '<a data-donem="' + d[0] + '" class="' + (d[0] === donem ? 'sec' : '') + '">' + d[1] + '</a>';
        }).join('');
    }

    function halka(oran) {
        var r = 36, c = 2 * Math.PI * r, p = oran === null || oran === undefined ? 0 : Math.max(0, Math.min(100, oran));
        return '<svg width="86" height="86" viewBox="0 0 86 86"><defs><linearGradient id="ikdg" x1="0" x2="1" y1="0" y2="1">' +
            '<stop offset="0" stop-color="#A855F7"/><stop offset="1" stop-color="#5B21B6"/></linearGradient></defs>' +
            '<circle cx="43" cy="43" r="' + r + '" fill="none" stroke="#EDE3FD" stroke-width="9"/>' +
            '<circle cx="43" cy="43" r="' + r + '" fill="none" stroke="url(#ikdg)" stroke-width="9" stroke-linecap="round" ' +
            'stroke-dasharray="' + (c * p / 100) + ' ' + c + '" transform="rotate(-90 43 43)"/></svg>' +
            '<span>' + (oran === null || oran === undefined ? '—' : '%' + oran) + '</span>';
    }

    function ustCiz() {
        var k = veri.kural, p = veri.performans || {};
        var d = IK.durum(k), adet = k.gereken_adet || 0, hediye = k.ucretsiz_adet || 1, hz = k.hizmet_adi || 'Hizmet';
        $('ikd-foto').innerHTML = IK.gorselImg(k);
        $('ikd-durum').innerHTML = '<i class="fa fa-star"></i> ' + DURUM[d][0];
        $('ikd-durum').style.color = DURUM[d][1];
        $('ikd-buyuk').textContent = adet + ' + ' + hediye;
        $('ikd-ad').textContent = hz + '\nKampanyası';
        $('ikd-alt').textContent = adet + ' hizmet alan müşterilerimize\n' + hediye + ' hizmet hediye!';
        $('ikd-adet').textContent = adet;
        $('ikd-hediye').textContent = hediye;
        $('ikd-tarih').textContent = !k.baslangic ? 'Açılınca başlar'
            : IK.tarih(k.baslangic) + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz');
        $('ikd-hak-sure').textContent = k.gecerlilik ? 'Hak ' + k.gecerlilik + ' geçerli' : '';
        $('ikd-katilan').textContent = parseInt(p.donem_katilan || 0, 10);
        $('ikd-kazanc').textContent = IK.tl(p.donem_kazanc) + ' TL';
        $('ikd-halka').innerHTML = halka(p.kullanim_orani);
        $('ikd-sekme0').textContent = 'Katılan Müşteriler (' + (veri.musteriler || []).length + ')';
        $('ikd-ozet').innerHTML = 'Bu kampanya sayesinde toplam <b style="color:#2E1065">' + parseInt(p.katilan || 0, 10) +
            ' müşteri</b> kampanyadan yararlandı ve <b style="color:#2E1065">' + IK.tl(p.kazanc) + ' TL</b> kazanç sağlandı.';
        var aktif = Number(k.aktif) === 1;
        $('ikd-ak-yazi').textContent = aktif ? 'Kampanyayı Kapat' : 'Kampanyayı Aç';
        $('ikd-ak-ikon').className = 'fa fa-' + (aktif ? 'pause' : 'play');
    }

    function avatar(m) {
        var r = m.resim ? (/^https?:/.test(m.resim) ? m.resim : '/' + String(m.resim).replace(/^\//, '')) : '';
        var bas = String(m.ad || '').split(/\s+/).filter(Boolean).slice(0, 2).map(function (x) { return x.charAt(0); }).join('').toLocaleUpperCase('tr-TR');
        return '<div class="ikd-av">' + (r ? '<img src="' + IK.esc(r) + '" alt="" onerror="this.remove()">' : '') + IK.esc(bas) + '</div>';
    }
    function cip(dr) { var d = MCIP[dr] ? dr : 'katildi'; return '<span class="ikd-cip ' + d + '">' + MCIP[d] + '</span>'; }

    function icerikCiz() {
        var kap = $('ikd-icerik');
        document.querySelectorAll('.ikd-sekme').forEach(function (s) { s.classList.toggle('sec', Number(s.getAttribute('data-sekme')) === sekme); });
        if (!veri) { kap.innerHTML = '<div class="ik-yukleniyor"><i class="fa fa-spinner fa-spin fa-2x"></i></div>'; return; }
        if (sekme === 0) {
            var tum = veri.musteriler || [];
            var q = ara.toLocaleLowerCase('tr-TR');
            var liste = tum.filter(function (m) {
                if (filtre !== 'tumu' && m.durum !== filtre) return false;
                if (!q) return true;
                return String(m.ad || '').toLocaleLowerCase('tr-TR').indexOf(q) !== -1 || String(m.telefon || '').indexOf(ara) !== -1;
            });
            var ust = '<div class="ikd-ara"><input type="text" id="ikd-ara" placeholder="Müşteri ara..." value="' + IK.esc(ara) + '">' +
                '<div class="ik-menu"><span class="ik-hap ik-menu-ac">' + FILTRELER.filter(function (f) { return f[0] === filtre; })[0][1] +
                ' <i class="fa fa-chevron-down" style="font-size:10px"></i></span><div class="ik-menu-liste">' +
                FILTRELER.map(function (f) { return '<a data-mfiltre="' + f[0] + '" class="' + (f[0] === filtre ? 'sec' : '') + '">' + f[1] + '</a>'; }).join('') +
                '</div></div></div>';
            var govde;
            if (!tum.length) govde = '<div class="ik-bos">Henüz kampanyaya katılan müşteri yok.\nBu hizmeti alan ve "Geldi" işaretlenen müşteriler burada görünür.</div>';
            else if (!liste.length) govde = '<div class="ik-bos">Bu filtrede müşteri yok.</div>';
            else govde = liste.map(function (m) {
                return '<div class="ikd-musteri" data-musteri="' + tum.indexOf(m) + '">' + avatar(m) +
                    '<div style="flex:1;min-width:0"><b style="color:#2E1065">' + IK.esc(m.ad) + '</b>' +
                    '<div style="font-size:12px;color:#6B5A8E"><i class="fa fa-calendar"></i> ' + IK.esc(IK.tarihSaat(m.son_ziyaret)) + '</div></div>' +
                    cip(m.durum) + ' <i class="fa fa-chevron-right" style="color:#B9A5DD"></i></div>';
            }).join('');
            var odak = document.activeElement && document.activeElement.id === 'ikd-ara';
            kap.innerHTML = ust + govde;
            if (odak) { var i = $('ikd-ara'); i.focus(); i.setSelectionRange(i.value.length, i.value.length); }
            return;
        }
        var p = veri.performans || {}, kd = veri.kazanc_detay || {};
        var aylik = kd.aylik || [], kul = kd.kullanimlar || [];
        var max = Math.max.apply(null, aylik.map(function (a) { return Number(a.tutar) || 0; }).concat([1]));
        kap.innerHTML =
            '<div class="ikd-kutular">' +
                '<div><small>Toplam Satış\n(kampanya boyunca)</small><b style="color:#2E1065">' + IK.tl(p.kazanc) + ' TL</b></div>' +
                '<div><small>Verilen İkram\n(' + parseInt(p.kullanilan || 0, 10) + ' adet)</small><b style="color:#B45309">' + IK.tl(kd.ikram_tutari) + ' TL</b></div>' +
                '<div><small>Net Kazanç</small><b style="color:#15803D">' + IK.tl(kd.net) + ' TL</b></div>' +
            '</div>' +
            '<b style="color:#2E1065">Aylık Satış</b>' +
            (aylik.length ? '<div class="ikd-grafik">' + aylik.map(function (a) {
                var h = Math.max(4, Math.round(150 * (Number(a.tutar) || 0) / max));
                return '<div class="ikd-cubuk"><em>' + IK.tl(a.tutar) + '</em><i style="height:' + h + 'px"></i><small>' +
                    (AYLAR[parseInt(String(a.ay).slice(5, 7), 10) - 1] || a.ay) + '</small></div>';
            }).join('') + '</div>' : '<div class="ik-bos">Kampanya süresince bu hizmetten satış yok.</div>') +
            '<div style="margin-top:16px"><b style="color:#2E1065">Kullanılan İkramlar</b></div>' +
            (kul.length ? kul.map(function (x) {
                return '<div class="ikd-kul"><i class="fa fa-gift" style="color:#7C3AED"></i><div style="flex:1"><b style="color:#2E1065">' + IK.esc(x.ad) +
                    '</b><div style="font-size:12px;color:#6B5A8E">' + IK.esc(IK.tarihSaat(x.tarih)) + '</div></div>' +
                    '<b style="color:#B45309">' + IK.tl(x.tutar) + ' TL</b></div>';
            }).join('') : '<div class="ik-bos">Henüz ikram kullanılmadı.</div>');
    }

    function musteriAc(m) {
        var g = (veri.kural && veri.kural.gereken_adet) || 1;
        var damga = '';
        for (var i = 0; i < Math.min(g, 20); i++) damga += '<span class="' + (i < m.ilerleme ? 'dolu' : '') + '">' + (i < m.ilerleme ? '<i class="fa fa-check"></i>' : '') + '</span>';
        var tel = String(m.telefon || '');
        $('ikd-modal-ic').innerHTML =
            '<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">' + avatar(m) +
                '<div style="flex:1"><b style="font-size:16px;color:#2E1065">' + IK.esc(m.ad) + '</b>' +
                (tel ? '<div style="font-size:13px;color:#6B5A8E">' + IK.esc(tel) + '</div>' : '') + '</div>' + cip(m.durum) +
                '<a class="ik-yuv" data-kapat style="width:34px;height:34px"><i class="fa fa-times"></i></a></div>' +
            '<b style="color:#2E1065">İlerleme: ' + m.ilerleme + ' / ' + g + '</b><div class="ikd-damga">' + damga + '</div>' +
            '<div class="ikd-mini"><div><b>' + m.ucretli + '</b><small>Ücretli alım</small></div><div><b>' + m.kazanilan + '</b><small>Kazanılan</small></div>' +
                '<div><b>' + m.kullanilan + '</b><small>Kullanılan</small></div><div><b>' + m.hak + '</b><small>Bekleyen</small></div></div>' +
            '<div style="font-size:13px;color:#6B5A8E">İlk ziyaret: ' + IK.tarih(m.ilk_ziyaret) + '   •   Son: ' + IK.esc(IK.tarihSaat(m.son_ziyaret)) + '</div>' +
            (m.son_kullanma ? '<div style="font-size:13px;color:#78350F;margin-top:6px">En yakın son kullanma: ' + IK.tarih(m.son_kullanma) + '</div>' : '') +
            (m.suresi_dolan > 0 ? '<div style="font-size:13px;color:#dc2626;margin-top:6px">' + m.suresi_dolan + ' ikramın süresi doldu</div>' : '') +
            (tel ? '<a class="ik-btn-ana" style="margin-top:16px" href="tel:' + IK.esc(tel) + '"><i class="fa fa-phone"></i> Müşteriyi Ara</a>' : '');
        $('ikd-modal').classList.add('acik');
    }
    $('ikd-modal').addEventListener('click', function (e) {
        if (e.target.id === 'ikd-modal' || e.target.closest('[data-kapat]')) this.classList.remove('acik');
    });

    document.addEventListener('click', function (e) {
        var d = e.target.closest('[data-donem]');
        if (d) { donem = Number(d.getAttribute('data-donem')); donemMenu(); yukle(); return; }
        var s = e.target.closest('[data-sekme]');
        if (s) { sekme = Number(s.getAttribute('data-sekme')); icerikCiz(); return; }
        var f = e.target.closest('[data-mfiltre]');
        if (f) { filtre = f.getAttribute('data-mfiltre'); icerikCiz(); return; }
        var m = e.target.closest('[data-musteri]');
        if (m && veri) { musteriAc(veri.musteriler[Number(m.getAttribute('data-musteri'))]); return; }
        var is = e.target.closest('[data-islem]');
        if (!is || !veri) return;
        var k = veri.kural, tur = is.getAttribute('data-islem');
        if (tur === 'duzenle') { IKForm.ac(k, yukle); return; }
        if (tur === 'ackapat') {
            IK.acKapat(k).then(function (r) {
                if (r && r.success) { IK.toast(Number(k.aktif) === 1 ? 'Kampanya kapatıldı' : 'Kampanya açıldı'); yukle(); }
                else IK.toast((r && r.message) || 'Değiştirilemedi', true);
            }).catch(function () { IK.toast('Değiştirilemedi', true); });
            return;
        }
        if (tur === 'sil') {
            IK.sil(k).then(function (r) {
                if (r === null) return;
                if (r && r.success) {
                    try { sessionStorage.setItem('ik_mesaj', 'Kampanya silindi'); } catch (x) {}
                    location.href = IK.u('ikramhizmet');
                } else IK.toast((r && r.message) || 'Silinemedi', true);
            }).catch(function () { IK.toast('Silinemedi', true); });
        }
    });
    document.addEventListener('input', function (e) { if (e.target.id === 'ikd-ara') { ara = e.target.value; icerikCiz(); } });

    function yukle() {
        if (veri) { $('ikd-katilan').textContent = '…'; $('ikd-kazanc').textContent = '…'; }
        IK.getJson(IK.u('ikramhizmet/detay-veri', { id: ID, donem: donem })).then(function (r) {
            if (!r || !r.basarili) {
                if (!veri) $('ikd-icerik').innerHTML = '<div class="ik-bos">' + IK.esc((r && r.mesaj) || 'Kampanya detayı alınamadı.') + '</div>';
                return;
            }
            veri = r;
            ustCiz();
            icerikCiz();
        }).catch(function () {
            if (!veri) $('ikd-icerik').innerHTML = '<div class="ik-bos">Kampanya detayı alınamadı.</div>';
        });
    }

    try { if (sessionStorage.getItem('ik_mesaj')) { IK.toast(sessionStorage.getItem('ik_mesaj')); sessionStorage.removeItem('ik_mesaj'); } } catch (e) {}
    donemMenu();
    yukle();
})();
</script>
@endsection
