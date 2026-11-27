@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Kampanya Detayi (ana sayfa ile ayni gorunum). Mobil kampanya_detay.dart'taki her sey burada da var: donem, duzenle/ac-kapat/sil,
     katilan musteriler (arama + filtre + musteri penceresi + ara), aylik satis, kullanilan ikramlar, rapor.
     Veri /isletmeyonetim/ikramhizmet/detay-veri. --}}
@include('isletmeadmin.ikram._ortak')
@include('isletmeadmin.ikram._kampanya_modal')
<style>
.ik-sayfa { padding-bottom:36px; background:linear-gradient(180deg,#F3ECFC 0%,#F7F2FD 60%,#F2EAFB 100%); }
.ik-sayfa .ik-ic { max-width:1900px; }
.ik-sayfa a { cursor:pointer; text-decoration:none !important; }
.ikd-kart { background:#fff; border-radius:20px; box-shadow:0 8px 26px rgba(91,33,182,.07); border:1px solid #F0E8FB; padding:18px; min-width:0; }
.ikd-bas { display:flex; align-items:center; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.ikd-bas h3 { flex:1; margin:0; font-size:17px; font-weight:700; color:#1E1037; white-space:nowrap; }
.ikd-bas h3 small { font-size:13px; font-weight:600; color:#6B5A8E; margin-left:4px; }

/* Ust satir */
.ikd-ust { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
.ik-sayfa a.ikd-geri { width:44px; height:44px; border-radius:50%; background:#fff; border:1px solid #E9DDF9; display:flex; align-items:center;
    justify-content:center; color:#2E1065 !important; flex-shrink:0; }
.ik-sayfa a.ikd-geri:hover { color:#6D28D9 !important; border-color:#C4A5F5; }
.ikd-ust-yazi { flex:1; min-width:200px; }
.ikd-ust-yazi h1 { margin:0; font-size:23px; font-weight:700; color:#1E1037; }
.ikd-ust-yazi small { font-size:13px; color:#6B5A8E; }
.ikd-donem { display:inline-flex; background:#fff; border:1px solid #E9DDF9; border-radius:22px; padding:4px; gap:2px; }
.ik-sayfa .ikd-donem a { padding:7px 13px; border-radius:18px; font-size:13px; font-weight:600; color:#6B5A8E !important; white-space:nowrap; }
.ik-sayfa .ikd-donem a.sec { background:#6D28D9; color:#fff !important; }
.ik-sayfa a.ikd-btn { display:inline-flex; align-items:center; gap:8px; height:42px; padding:0 16px; border-radius:21px; background:#fff;
    border:1px solid #E9DDF9; color:#2E1065 !important; font-weight:600; font-size:13.5px; white-space:nowrap; }
.ik-sayfa a.ikd-btn:hover { border-color:#8B5CF6; color:#6D28D9 !important; }
.ik-sayfa a.ikd-btn.kirmizi:hover { border-color:#FCA5A5; color:#DC2626 !important; }
.ik-sayfa a.ikd-btn.ana { background:linear-gradient(90deg,#7C3AED,#5B21B6); border:0; color:#fff !important; }

/* Kampanya bandi */
.ikd-hero { position:relative; min-height:clamp(190px,13vw,280px); border-radius:22px; overflow:hidden; margin-bottom:16px; color:#fff;
    background:linear-gradient(100deg,#8A2A99 0%,#6A22A6 30%,#561C9F 55%,#4A1C8C 100%); box-shadow:0 14px 34px rgba(76,29,149,.20); }
.ikd-hero-foto { position:absolute; top:0; right:0; bottom:0; width:48%; }
.ikd-hero-foto img { width:100%; height:100%; object-fit:cover; -webkit-mask-image:linear-gradient(90deg,transparent 0,#000 38%); mask-image:linear-gradient(90deg,transparent 0,#000 38%); }
.ikd-hero-yazi { position:relative; z-index:1; padding:clamp(18px,1.6vw,28px) clamp(20px,2.6vw,44px); max-width:62%; }
.ikd-durum { display:inline-flex; align-items:center; gap:7px; padding:5px 12px; border-radius:16px; background:rgba(255,255,255,.18); font-size:12.5px; font-weight:600; }
.ikd-durum i { color:#FCD34D; }
.ikd-hero h2 { margin:12px 0 6px; font-size:clamp(26px,2.2vw,42px); font-weight:700; line-height:1.1; color:#fff; }
.ikd-hero p { margin:0; font-size:clamp(14.5px,1.05vw,18px); line-height:1.35; color:rgba(255,255,255,.95); }
.ikd-hero-sure { display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin-top:16px; font-size:13.5px; }
.ikd-hero-sure .ikd-cubuk { width:min(320px,100%); height:7px; border-radius:4px; background:rgba(255,255,255,.25); overflow:hidden; }
.ikd-hero-sure .ikd-cubuk i { display:block; height:100%; background:#fff; border-radius:4px; }

/* Ozet kartlari */
.ikd-ozet { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
.ikd-kpi { display:flex; flex-direction:column; gap:4px; padding:16px; }
.ikd-kpi-ust { display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; color:#4C3A6E; }
.ikd-kpi-ikon { width:38px; height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:16px; color:#fff; flex-shrink:0;
    background:linear-gradient(135deg,#9333EA,#6D28D9); }
.ikd-kpi-ikon.yesil { background:linear-gradient(135deg,#34D399,#059669); } .ikd-kpi-ikon.turuncu { background:linear-gradient(135deg,#FBBF24,#D97706); }
.ikd-kpi-ikon.mavi { background:linear-gradient(135deg,#60A5FA,#2563EB); } .ikd-kpi-ikon.pembe { background:linear-gradient(135deg,#F472B6,#DB2777); }
.ikd-kpi b { margin-top:8px; font-size:clamp(20px,1.5vw,27px); font-weight:700; color:#1E1037; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ikd-kpi small { font-size:12px; color:#6B5A8E; line-height:1.35; }

/* Govde */
.ikd-govde { display:grid; grid-template-columns:minmax(0,1fr) minmax(320px,400px); gap:16px; align-items:start; }
.ikd-yan { display:flex; flex-direction:column; gap:16px; }

/* Musteri listesi */
.ikd-sekme { display:inline-flex; background:#F5F0FC; border-radius:18px; padding:3px; }
.ik-sayfa .ikd-sekme a { padding:7px 14px; border-radius:15px; font-size:13px; font-weight:600; color:#4C3A6E !important; white-space:nowrap; }
.ik-sayfa .ikd-sekme a.sec { background:#fff; color:#5B21B6 !important; box-shadow:0 2px 8px rgba(91,33,182,.12); }
.ikd-sekme a span { display:inline-block; min-width:20px; margin-left:4px; padding:0 6px; border-radius:10px; background:#EDE4FD; font-size:11.5px; text-align:center; }
.ikd-arac { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.ikd-arac input, .ikd-arac select { height:40px; border-radius:20px; border:1px solid #E2D4F8; padding:0 16px; background:#fff; color:#1E1037; font-size:13.5px; }
.ikd-arac input { flex:1; min-width:200px; }
.ikd-arac input:focus, .ikd-arac select:focus { outline:none; border-color:#8B5CF6; }
.ikd-tablo-bas, .ikd-satir { display:grid; grid-template-columns:minmax(180px,1.6fr) minmax(150px,1.3fr) 80px 110px 130px 120px 36px; gap:12px; align-items:center; }
.ikd-tablo-bas { padding:0 12px 8px; font-size:12px; font-weight:600; color:#6B5A8E; border-bottom:1px solid #F1EAFB; }
.ikd-satir { padding:10px 12px; border-radius:14px; cursor:pointer; transition:background .12s; }
.ikd-satir + .ikd-satir { border-top:1px solid #F7F2FD; }
.ikd-satir:hover { background:#FAF6FF; }
.ikd-kisi { display:flex; align-items:center; gap:10px; min-width:0; }
.ikd-av { width:40px; height:40px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;
    font-size:13px; font-weight:700; color:#6D28D9; background:linear-gradient(135deg,#F3E8FF,#E4D4FB); }
.ikd-av img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.ikd-kisi b { display:block; font-size:14px; font-weight:600; color:#1E1037; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ikd-kisi small { display:block; font-size:12px; color:#6B5A8E; }
.ikd-damga { display:flex; flex-wrap:wrap; gap:4px; }
.ikd-damga i { width:16px; height:16px; border-radius:50%; background:#F1E9FD; border:1px solid #DCCBF6; }
.ikd-damga i.dolu { background:#7C3AED; border-color:#7C3AED; }
.ikd-ilerleme small { display:block; font-size:11.5px; color:#6B5A8E; margin-top:3px; }
.ikd-ilerleme .ikd-cubuk { height:7px; border-radius:4px; background:#EDE4FB; overflow:hidden; }
.ikd-ilerleme .ikd-cubuk i { display:block; height:100%; background:linear-gradient(90deg,#A855F7,#6D28D9); }
.ikd-sayi { font-size:14px; font-weight:600; color:#1E1037; }
.ikd-sayi small { display:block; font-size:11.5px; font-weight:500; color:#6B5A8E; }
.ikd-cip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; white-space:nowrap; }
.ikd-cip.hak_var { color:#6D28D9; background:#EDE9FE; } .ikd-cip.kullandi { color:#1D4ED8; background:#DBEAFE; } .ikd-cip.katildi { color:#047857; background:#D1FAE5; }
.ikd-ok { color:#B9A5DD; text-align:right; }
.ikd-bos { padding:34px 10px; text-align:center; font-size:13.5px; color:#6B5A8E; line-height:1.6; white-space:pre-line; }
.ikd-daha { display:block; margin-top:10px; text-align:center; font-size:13px; font-weight:700; color:#6D28D9 !important; }

/* Yan kartlar */
.ikd-halka-satir { display:flex; align-items:center; gap:16px; }
.ikd-halka { position:relative; width:112px; height:112px; flex-shrink:0; }
.ikd-halka svg { width:100%; height:100%; transform:rotate(-90deg); }
.ikd-halka span { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:22px; font-weight:700; color:#1E1037; }
.ikd-halka span small { font-size:11px; font-weight:600; color:#6B5A8E; }
.ikd-liste-mini div { display:flex; justify-content:space-between; gap:10px; padding:6px 0; font-size:13px; color:#4C3A6E; border-bottom:1px solid #F5F0FC; }
.ikd-liste-mini div b { color:#1E1037; font-weight:600; text-align:right; }
.ikd-grafik { display:flex; align-items:flex-end; gap:8px; height:150px; padding-top:24px; border-bottom:1px solid #F0E8FB; }
.ikd-grafik div { flex:1; min-width:0; height:100%; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; }
.ikd-grafik em { font-style:normal; font-size:10.5px; font-weight:600; color:#1E1037; white-space:nowrap; margin-bottom:4px; }
.ikd-grafik i { display:block; width:70%; max-width:34px; min-height:4px; border-radius:8px 8px 3px 3px; background:linear-gradient(180deg,#C4A5F5,#D8C2FA); }
.ikd-grafik div:last-child i { background:linear-gradient(180deg,#8B5CF6,#6D28D9); }
.ikd-aylar { display:flex; gap:8px; margin-top:6px; } .ikd-aylar span { flex:1; text-align:center; font-size:11.5px; color:#4C3A6E; }
.ikd-kul { display:flex; align-items:center; gap:10px; padding:8px 0; font-size:13px; }
.ikd-kul + .ikd-kul { border-top:1px solid #F5F0FC; }
.ikd-kul i.fa { width:30px; height:30px; border-radius:50%; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ikd-kul b { color:#1E1037; font-weight:600; } .ikd-kul small { display:block; color:#6B5A8E; font-size:11.5px; }
.ik-sayfa a.ikd-rapor { display:flex; align-items:center; justify-content:center; gap:10px; height:50px; border-radius:25px; color:#fff !important;
    background:linear-gradient(90deg,#7C3AED,#5B21B6); font-weight:700; font-size:14.5px; box-shadow:0 8px 18px rgba(109,40,217,.25); }

/* Musteri penceresi */
.ikd-modal { display:none; position:fixed; inset:0; background:rgba(30,16,55,.45); z-index:100000; align-items:center; justify-content:center; padding:16px; }
.ikd-modal.acik { display:flex; }
.ikd-modal-ic { background:#fff; width:100%; max-width:540px; border-radius:24px; padding:22px; max-height:90vh; overflow:auto; }
.ikd-mini { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin:14px 0; }
.ikd-mini div { background:#F7F2FD; border-radius:14px; padding:10px 6px; text-align:center; }
.ikd-mini b { display:block; font-size:19px; font-weight:700; color:#1E1037; } .ikd-mini small { font-size:11.5px; color:#6B5A8E; }
.ikd-modal .ikd-damga i { width:24px; height:24px; }

.ikd-iskelet { background:linear-gradient(90deg,#F3ECFC 25%,#FAF6FF 50%,#F3ECFC 75%); background-size:200% 100%; animation:ikdKay 1.2s infinite; border-radius:14px; }
@keyframes ikdKay { to { background-position:-200% 0; } }

@media (max-width:1499px) { .ikd-ozet { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media (max-width:1299px) {
    .ikd-govde { grid-template-columns:minmax(0,1fr); }
    .ikd-yan { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); }
}
@media (max-width:899px) {
    .ikd-tablo-bas { display:none; }
    .ikd-satir { grid-template-columns:minmax(0,1fr) auto; grid-template-areas:"kisi durum" "ilerleme ilerleme" "alim sonz"; row-gap:8px; }
    .ikd-satir > .k1 { grid-area:kisi; } .ikd-satir > .k2 { grid-area:ilerleme; } .ikd-satir > .k3 { grid-area:alim; }
    .ikd-satir > .k4 { display:none; } .ikd-satir > .k5 { grid-area:durum; } .ikd-satir > .k6 { grid-area:sonz; text-align:right; } .ikd-satir > .ikd-ok { display:none; }
    .ikd-hero-foto { width:70%; opacity:.45; } .ikd-hero-yazi { max-width:none; }
}
@media (max-width:600px) {
    .ikd-ozet { grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
    .ikd-yan { grid-template-columns:minmax(0,1fr); }
    .ikd-sekme { width:100%; } .ik-sayfa .ikd-sekme a { flex:1; text-align:center; padding:7px 4px; }
    .ikd-donem { width:100%; } .ik-sayfa .ikd-donem a { flex:1; text-align:center; padding:7px 4px; }
    .ikd-hero-foto { width:100%; opacity:.22; }
    .ikd-mini { grid-template-columns:repeat(2,1fr); }
}
</style>

<div class="ik-sayfa">
    <div class="ik-ic">
        <div class="ikd-ust">
            <a class="ikd-geri" id="ik-geri" title="İkram Hizmet"><i class="fa fa-arrow-left"></i></a>
            <div class="ikd-ust-yazi"><h1>Kampanya Detayı</h1><small id="ikd-ust-alt">Yükleniyor…</small></div>
            <div class="ikd-donem" id="ikd-donem"></div>
            <a class="ikd-btn" data-islem="ackapat" id="ikd-ak"><i class="fa fa-pause"></i> <span>Kampanyayı Kapat</span></a>
            <a class="ikd-btn kirmizi" data-islem="sil" title="Kampanyayı sil"><i class="fa fa-trash-o"></i> Sil</a>
            <a class="ikd-btn ana" data-islem="duzenle"><i class="fa fa-pencil"></i> Düzenle</a>
        </div>

        <div class="ikd-hero">
            <div class="ikd-hero-foto" id="ikd-foto"></div>
            <div class="ikd-hero-yazi">
                <span class="ikd-durum" id="ikd-durum"><i class="fa fa-star"></i> Kampanya</span>
                <h2 id="ikd-baslik">…</h2>
                <p id="ikd-alt"></p>
                <div class="ikd-hero-sure" id="ikd-sure"></div>
            </div>
        </div>

        <div class="ikd-ozet" id="ikd-ozet">
            <div class="ikd-kart ikd-iskelet" style="height:118px"></div><div class="ikd-kart ikd-iskelet"></div><div class="ikd-kart ikd-iskelet"></div>
            <div class="ikd-kart ikd-iskelet"></div><div class="ikd-kart ikd-iskelet"></div><div class="ikd-kart ikd-iskelet"></div>
        </div>

        <div class="ikd-govde">
            <div class="ikd-kart">
                <div class="ikd-bas">
                    <h3>Katılan Müşteriler <small id="ikd-say"></small></h3>
                    <div class="ikd-sekme" id="ikd-sekme"></div>
                </div>
                <div class="ikd-arac">
                    <input type="text" id="ikd-ara" placeholder="Müşteri adı ya da telefon ara…" autocomplete="off">
                    <select id="ikd-sira" title="Sıralama">
                        <option value="son">Son ziyarete göre</option>
                        <option value="ilerleme">Hediyeye en yakın</option>
                        <option value="alim">En çok alan</option>
                        <option value="ad">İsme göre</option>
                    </select>
                    <a class="ikd-btn" id="ikd-indir" title="Listeyi Excel'de açılacak dosya olarak indir"><i class="fa fa-download"></i> Excel</a>
                </div>
                <div class="ikd-tablo-bas"><span>Müşteri</span><span>Hediyeye ilerleme</span><span>Alım</span><span>Kazanılan / Kullanılan</span><span>Durum</span><span>Son ziyaret</span><span></span></div>
                <div id="ikd-liste"><div class="ikd-iskelet" style="height:220px;margin-top:10px"></div></div>
            </div>

            <div class="ikd-yan">
                <div class="ikd-kart">
                    <div class="ikd-bas"><h3>Hediye Kullanımı</h3></div>
                    <div class="ikd-halka-satir">
                        <div class="ikd-halka" id="ikd-halka"></div>
                        <div class="ikd-liste-mini" style="flex:1" id="ikd-hediye-ozet"></div>
                    </div>
                </div>
                <div class="ikd-kart">
                    <div class="ikd-bas"><h3>Aylık Satış</h3></div>
                    <div id="ikd-grafik"><div class="ikd-iskelet" style="height:150px"></div></div>
                </div>
                <div class="ikd-kart">
                    <div class="ikd-bas"><h3>Kullanılan İkramlar</h3></div>
                    <div id="ikd-kullanimlar"><div class="ikd-iskelet" style="height:90px"></div></div>
                </div>
                <div class="ikd-kart">
                    <div class="ikd-bas"><h3>Kampanya Bilgileri</h3></div>
                    <div class="ikd-liste-mini" id="ikd-bilgi"></div>
                </div>
                <a class="ikd-rapor" id="ikd-rapor"><i class="fa fa-bar-chart"></i> Tüm Kampanyaların Raporu <i class="fa fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
    {{-- Musteri penceresi sayfanin icinde: dugme stilleri (.ik-sayfa a.ikd-btn) gecerli olsun --}}
    <div class="ikd-modal" id="ikd-modal"><div class="ikd-modal-ic" id="ikd-modal-ic"></div></div>
</div>

<script>
(function () {
    var ID = {{ (int) $ikramKuralId }};
    var params = new URLSearchParams(location.search);
    var donem = parseInt(params.get('donem'), 10) || 30;
    var veri = null, filtre = 'tumu', ara = '', sira = 'son', gosterilen = 50;
    var FILTRELER = [['tumu', 'Tümü'], ['kazanan', 'Kazananlar'], ['bekleyen', 'Bekleyenler']];
    var DURUM = { aktif: 'Aktif Kampanya', baslayacak: 'Yakında Başlıyor', bitti: 'Kampanya Bitti', pasif: 'Kapalı Kampanya' };
    var MCIP = { hak_var: ['Hediyesi hazır', 'gift'], kullandi: ['Hediyesini kullandı', 'check'], katildi: ['Devam ediyor', 'hourglass-half'] };
    var AYLAR = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
    var $ = function (id) { return document.getElementById(id); };
    var esc = IK.esc;

    $('ik-geri').addEventListener('click', function () { location.href = IK.u('ikramhizmet'); });
    $('ikd-rapor').addEventListener('click', function () { location.href = IK.u('ikramhizmet/rapor', { donem: donem }); });

    /** Kazananlar: en az bir hediye kazanmis; Bekleyenler: hediyeye henuz ulasmamis, alim yapmaya devam eden */
    function filtreUyar(m, f) {
        if (f === 'kazanan') return Number(m.kazanilan) > 0;
        if (f === 'bekleyen') return Number(m.kazanilan) === 0;
        return true;
    }
    function donemCiz() {
        $('ikd-donem').innerHTML = IK.DONEMLER.map(function (d) {
            return '<a data-donem="' + d[0] + '" class="' + (d[0] === donem ? 'sec' : '') + '">' + d[1] + '</a>';
        }).join('');
    }

    function ustCiz() {
        var k = veri.kural, d = IK.durum(k), adet = k.gereken_adet || 0, hediye = k.ucretsiz_adet || 1, hz = k.hizmet_adi || 'Hizmet';
        $('ikd-ust-alt').textContent = adet + ' + ' + hediye + ' ' + hz;
        $('ikd-foto').innerHTML = IK.gorselImg(k);
        $('ikd-durum').innerHTML = '<i class="fa fa-star"></i> ' + DURUM[d];
        $('ikd-baslik').textContent = adet + ' + ' + hediye + ' ' + hz + ' Kampanyası';
        $('ikd-alt').textContent = adet + ' ' + hz + ' alan müşteriye ' + hediye + ' ' + hz + ' hediye.' + (k.gecerlilik ? ' Hediye ' + k.gecerlilik + ' içinde kullanılır.' : '');
        var sure = '<span><i class="fa fa-calendar"></i> ' + (k.baslangic ? IK.tarih(k.baslangic) : 'Açılınca başlar') + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz') + '</span>';
        if (k.baslangic && k.bitis_tarihi && d === 'aktif') {
            var bas = new Date(String(k.baslangic).slice(0, 10) + 'T00:00:00'), bit = new Date(k.bitis_tarihi + 'T23:59:59'), simdi = new Date();
            var kalan = Math.floor((bit - simdi) / 86400000), yuzde = Math.max(0, Math.min(100, (simdi - bas) / Math.max(1, bit - bas) * 100));
            sure += '<span class="ikd-cubuk"><i style="width:' + yuzde.toFixed(1) + '%"></i></span><b>' + (kalan < 1 ? 'Son gün' : kalan + ' gün kaldı') + '</b>';
        }
        $('ikd-sure').innerHTML = sure;
        var acik = Number(k.aktif) === 1;
        $('ikd-ak').style.display = d === 'bitti' ? 'none' : '';
        $('ikd-ak').innerHTML = '<i class="fa fa-' + (acik ? 'pause' : 'play') + '"></i> <span>' + (acik ? 'Kampanyayı Kapat' : 'Kampanyayı Aç') + '</span>';
        document.title = adet + '+' + hediye + ' ' + hz + ' | Kampanya Detayı';
    }

    function kpi(ikon, renk, baslik, deger, alt) {
        return '<div class="ikd-kart ikd-kpi"><div class="ikd-kpi-ust"><span class="ikd-kpi-ikon ' + renk + '"><i class="fa fa-' + ikon + '"></i></span>' + baslik + '</div>' +
            '<b class="ik-sayi">' + deger + '</b><small>' + alt + '</small></div>';
    }
    function ozetCiz() {
        var p = veri.performans || {}, kd = veri.kazanc_detay || {};
        $('ikd-ozet').innerHTML =
            kpi('line-chart', '', 'Toplam Satış', IK.tl(p.kazanc) + ' TL', (kd.satis_adedi || 0) + ' satış · kampanya boyunca') +
            kpi('calendar-check-o', 'mavi', IK.donemYazi(donem) + ' Satış', IK.tl(p.donem_kazanc) + ' TL', 'Seçili dönemde bu hizmetin satışı') +
            kpi('gift', 'turuncu', 'Verilen İkram', IK.tl(kd.ikram_tutari) + ' TL', (p.kullanilan || 0) + ' hediye hizmetin değeri') +
            kpi('money', 'yesil', 'Net Kazanç', IK.tl(kd.net) + ' TL', 'Satış − verilen ikram') +
            kpi('users', 'pembe', 'Katılan Müşteri', IK.tl(p.katilan), (p.donem_katilan || 0) + ' müşteri ' + IK.donemYazi(donem).toLocaleLowerCase('tr') + ' içinde geldi') +
            kpi('star', '', 'Hediyesi Hazır', IK.tl(p.hak_bekleyen), 'Gelince ikram olarak işlenecek müşteri');
    }

    function avatar(m) {
        var r = m.resim ? (/^https?:/.test(m.resim) ? m.resim : '/' + String(m.resim).replace(/^\//, '')) : '';
        var bas = String(m.ad || '').split(/\s+/).filter(Boolean).slice(0, 2).map(function (x) { return x.charAt(0); }).join('').toLocaleUpperCase('tr-TR');
        return '<div class="ikd-av">' + esc(bas) + (r ? '<img src="' + esc(r) + '" alt="" onerror="this.remove()">' : '') + '</div>';
    }
    function cip(dr) { var c = MCIP[dr] || MCIP.katildi; return '<span class="ikd-cip ' + (MCIP[dr] ? dr : 'katildi') + '"><i class="fa fa-' + c[1] + '"></i> ' + c[0] + '</span>'; }
    function damga(dolu, toplam) {
        if (toplam > 12) return '<div class="ikd-cubuk"><i style="width:' + (dolu / toplam * 100) + '%"></i></div>';
        var s = ''; for (var i = 0; i < toplam; i++) s += '<i class="' + (i < dolu ? 'dolu' : '') + '"></i>';
        return '<div class="ikd-damga">' + s + '</div>';
    }

    function listeCiz() {
        var tum = veri.musteriler || [], g = (veri.kural && veri.kural.gereken_adet) || 1;
        $('ikd-say').textContent = '(' + tum.length + ')';
        $('ikd-sekme').innerHTML = FILTRELER.map(function (f) {
            var n = tum.filter(function (m) { return filtreUyar(m, f[0]); }).length;
            return '<a data-filtre="' + f[0] + '" class="' + (f[0] === filtre ? 'sec' : '') + '">' + f[1] + '<span>' + n + '</span></a>';
        }).join('');
        var q = ara.trim().toLocaleLowerCase('tr-TR');
        var liste = tum.filter(function (m) {
            if (!filtreUyar(m, filtre)) return false;
            return !q || String(m.ad || '').toLocaleLowerCase('tr-TR').indexOf(q) !== -1 || String(m.telefon || '').indexOf(q) !== -1;
        });
        liste.sort(function (a, b) {
            if (sira === 'ilerleme') return (b.hak > 0) - (a.hak > 0) || b.ilerleme - a.ilerleme || b.ucretli - a.ucretli;
            if (sira === 'alim') return b.ucretli - a.ucretli;
            if (sira === 'ad') return String(a.ad).localeCompare(String(b.ad), 'tr');
            return String(b.son_ziyaret || '').localeCompare(String(a.son_ziyaret || ''));
        });
        if (!tum.length) {
            $('ikd-liste').innerHTML = '<div class="ikd-bos">Henüz kampanyaya katılan müşteri yok.\nBu hizmeti alan, "Geldi" işaretlenen ve ödemesi alınan müşteriler burada görünür.</div>';
            return;
        }
        if (!liste.length) {
            $('ikd-liste').innerHTML = '<div class="ikd-bos">' + (q ? 'Aramaya uyan müşteri yok.' : filtre === 'kazanan' ? 'Henüz hediye kazanan müşteri yok.' : 'Hediyeye ulaşmayı bekleyen müşteri yok.') + '</div>';
            return;
        }
        $('ikd-liste').innerHTML = liste.slice(0, gosterilen).map(function (m) {
            var kalan = g - m.ilerleme;
            return '<div class="ikd-satir" data-musteri="' + tum.indexOf(m) + '">' +
                '<div class="ikd-kisi k1">' + avatar(m) + '<div style="min-width:0"><b>' + esc(m.ad) + '</b><small>' + esc(m.telefon || '') + '</small></div></div>' +
                '<div class="ikd-ilerleme k2">' + damga(m.ilerleme, g) + '<small>' + m.ilerleme + ' / ' + g + (m.hak > 0 ? ' · hediyesi hazır' : ' · hediyeye ' + kalan + ' kaldı') + '</small></div>' +
                '<div class="ikd-sayi k3">' + m.ucretli + '<small>alım</small></div>' +
                '<div class="ikd-sayi k4">' + m.kazanilan + ' / ' + m.kullanilan + '<small>kazandı / kullandı</small></div>' +
                '<div class="k5">' + cip(m.durum) + '</div>' +
                '<div class="ikd-sayi k6" style="font-weight:500;font-size:13px">' + esc(IK.tarih(m.son_ziyaret)) + '<small>son ziyaret</small></div>' +
                '<div class="ikd-ok"><i class="fa fa-chevron-right"></i></div>' +
            '</div>';
        }).join('') + (liste.length > gosterilen ? '<a class="ikd-daha" data-daha="1">' + (liste.length - gosterilen) + ' müşteri daha göster</a>' : '');
    }

    function yanCiz() {
        var p = veri.performans || {}, kd = veri.kazanc_detay || {}, k = veri.kural;
        var oran = p.kullanim_orani === null || p.kullanim_orani === undefined ? null : Number(p.kullanim_orani), c = 2 * Math.PI * 46;
        $('ikd-halka').innerHTML = '<svg viewBox="0 0 112 112"><defs><linearGradient id="ikdHg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#A855F7"/>' +
            '<stop offset="1" stop-color="#6D28D9"/></linearGradient></defs><circle cx="56" cy="56" r="46" fill="none" stroke="#F1EAFB" stroke-width="10"/>' +
            '<circle cx="56" cy="56" r="46" fill="none" stroke="url(#ikdHg)" stroke-width="10" stroke-linecap="round" stroke-dasharray="' + (c * (oran || 0) / 100).toFixed(1) + ' ' + c.toFixed(1) + '"/></svg>' +
            '<span>' + (oran === null ? '—' : '%' + oran) + '<small>kullanıldı</small></span>';
        $('ikd-hediye-ozet').innerHTML = '<div>Kazanılan hediye<b>' + (p.kazanilan || 0) + '</b></div><div>Kullanılan<b>' + (p.kullanilan || 0) + '</b></div>' +
            '<div>Hediyesi hazır müşteri<b>' + (p.hak_bekleyen || 0) + '</b></div>';

        var aylik = (kd.aylik || []).slice(-8);
        if (!aylik.length) $('ikd-grafik').innerHTML = '<div class="ikd-bos">Kampanya süresince bu hizmetten satış yok.</div>';
        else {
            var max = Math.max.apply(null, aylik.map(function (a) { return Number(a.tutar) || 0; }).concat([1]));
            $('ikd-grafik').innerHTML = '<div class="ikd-grafik">' + aylik.map(function (a) {
                return '<div title="' + a.adet + ' satış"><em>' + IK.tl(a.tutar) + '</em><i style="height:' + Math.max(3, (Number(a.tutar) || 0) / max * 78) + '%"></i></div>';
            }).join('') + '</div><div class="ikd-aylar">' + aylik.map(function (a) { return '<span>' + (AYLAR[parseInt(String(a.ay).slice(5, 7), 10) - 1] || a.ay) + '</span>'; }).join('') + '</div>';
        }

        var kul = kd.kullanimlar || [];
        $('ikd-kullanimlar').innerHTML = kul.length ? kul.slice(0, 8).map(function (x) {
            return '<div class="ikd-kul"><i class="fa fa-gift"></i><div style="flex:1;min-width:0"><b>' + esc(x.ad) + '</b><small>' + esc(IK.tarihSaat(x.tarih)) + '</small></div>' +
                '<b style="color:#B45309">' + IK.tl(x.tutar) + ' TL</b></div>';
        }).join('') + (kul.length > 8 ? '<div class="ikd-bos" style="padding:8px 0 0">+' + (kul.length - 8) + ' ikram daha</div>' : '')
            : '<div class="ikd-bos" style="padding:16px 0">Henüz ikram kullanılmadı.</div>';

        $('ikd-bilgi').innerHTML =
            '<div>Kampanya türü<b>' + (k.gereken_adet || 0) + '+' + (k.ucretsiz_adet || 1) + ' Sadakat Kampanyası</b></div>' +
            '<div>Dahil hizmet<b>' + esc(k.hizmet_adi || 'Hizmet') + '</b></div>' +
            '<div>Hediye<b>' + (k.ucretsiz_adet || 1) + ' seans ' + esc(k.hizmet_adi || '') + ' (ücretsiz)</b></div>' +
            '<div>Tarihler<b>' + (k.baslangic ? IK.tarih(k.baslangic) : 'Açılınca') + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz') + '</b></div>' +
            '<div>Hediye kullanım süresi<b>' + esc(k.gecerlilik || 'Süresiz') + '</b></div>' +
            '<div>Durum<b>' + DURUM[IK.durum(k)] + '</b></div>';
    }

    function musteriAc(m) {
        var g = (veri.kural && veri.kural.gereken_adet) || 1, tel = String(m.telefon || '');
        $('ikd-modal-ic').innerHTML =
            '<div style="display:flex;align-items:center;gap:12px">' + avatar(m) +
                '<div style="flex:1;min-width:0"><b style="font-size:16px;color:#1E1037">' + esc(m.ad) + '</b>' + (tel ? '<div style="font-size:13px;color:#6B5A8E">' + esc(tel) + '</div>' : '') + '</div>' +
                cip(m.durum) + '<a class="ikd-geri" data-kapat style="width:36px;height:36px"><i class="fa fa-times"></i></a></div>' +
            '<div style="margin-top:16px;font-weight:600;color:#1E1037">Hediyeye ilerleme: ' + m.ilerleme + ' / ' + g + '</div>' +
            '<div class="ikd-modal" style="display:block;position:static;background:none;padding:6px 0 0">' + damga(m.ilerleme, g) + '</div>' +
            '<div class="ikd-mini"><div><b>' + m.ucretli + '</b><small>Ücretli alım</small></div><div><b>' + m.kazanilan + '</b><small>Kazanılan</small></div>' +
                '<div><b>' + m.kullanilan + '</b><small>Kullanılan</small></div><div><b>' + m.hak + '</b><small>Hazır hediye</small></div></div>' +
            '<div style="font-size:13px;color:#4C3A6E">İlk ziyaret: ' + IK.tarih(m.ilk_ziyaret) + ' &nbsp;•&nbsp; Son ziyaret: ' + esc(IK.tarihSaat(m.son_ziyaret)) + '</div>' +
            (m.son_kullanma ? '<div style="font-size:13px;color:#B45309;margin-top:6px"><i class="fa fa-clock-o"></i> Hediyenin son kullanma günü: ' + IK.tarih(m.son_kullanma) + '</div>' : '') +
            (m.suresi_dolan > 0 ? '<div style="font-size:13px;color:#DC2626;margin-top:6px">' + m.suresi_dolan + ' hediyenin süresi doldu</div>' : '') +
            '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px">' +
                (tel ? '<a class="ikd-btn" href="tel:' + esc(tel) + '"><i class="fa fa-phone"></i> Müşteriyi Ara</a>' : '') +
                '<a class="ikd-btn ana" href="' + IK.u('musteridetay/' + m.user_id) + '" target="_blank" rel="noopener"><i class="fa fa-user"></i> Müşteri Sayfası</a></div>';
        $('ikd-modal').classList.add('acik');
    }
    $('ikd-modal').addEventListener('click', function (e) {
        if (e.target.id === 'ikd-modal' || e.target.closest('[data-kapat]')) this.classList.remove('acik');
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') $('ikd-modal').classList.remove('acik'); });

    /** Gorunen listeyi Excel'in actigi CSV olarak indirir */
    $('ikd-indir').addEventListener('click', function () {
        if (!veri || !(veri.musteriler || []).length) { IK.toast('İndirilecek müşteri yok', true); return; }
        var g = veri.kural.gereken_adet || 1, d = { hak_var: 'Hediyesi hazır', kullandi: 'Kullandı', katildi: 'Devam ediyor' };
        var satir = function (a) { return a.map(function (x) { return '"' + String(x == null ? '' : x).replace(/"/g, '""') + '"'; }).join(';'); };
        var csv = [satir(['Müşteri', 'Telefon', 'İlerleme', 'Ücretli alım', 'Kazanılan', 'Kullanılan', 'Hazır hediye', 'Durum', 'İlk ziyaret', 'Son ziyaret', 'Son kullanma'])]
            .concat(veri.musteriler.filter(function (m) { return filtreUyar(m, filtre); }).map(function (m) {
                return satir([m.ad, m.telefon, m.ilerleme + '/' + g, m.ucretli, m.kazanilan, m.kullanilan, m.hak, d[m.durum] || '', IK.tarih(m.ilk_ziyaret), IK.tarihSaat(m.son_ziyaret), m.son_kullanma ? IK.tarih(m.son_kullanma) : '']);
            })).join('\r\n');
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }));
        a.download = 'kampanya-' + ID + '-musteriler.csv';
        document.body.appendChild(a); a.click(); a.remove();
    });

    document.addEventListener('click', function (e) {
        var t = e.target;
        var d = t.closest('[data-donem]');
        if (d) { donem = Number(d.getAttribute('data-donem')); donemCiz(); yukle(); return; }
        var f = t.closest('[data-filtre]');
        if (f) { filtre = f.getAttribute('data-filtre'); gosterilen = 50; listeCiz(); return; }
        if (t.closest('[data-daha]')) { gosterilen += 100; listeCiz(); return; }
        var m = t.closest('[data-musteri]');
        if (m && veri) { musteriAc(veri.musteriler[Number(m.getAttribute('data-musteri'))]); return; }
        var is = t.closest('[data-islem]');
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
    $('ikd-ara').addEventListener('input', function () { ara = this.value; gosterilen = 50; if (veri) listeCiz(); });
    $('ikd-sira').addEventListener('change', function () { sira = this.value; if (veri) listeCiz(); });

    var istek = 0;
    function yukle() {
        var no = ++istek;
        IK.getJson(IK.u('ikramhizmet/detay-veri', { id: ID, donem: donem })).then(function (r) {
            if (no !== istek) return;
            if (!r || !r.basarili) {
                if (!veri) $('ikd-liste').innerHTML = '<div class="ikd-bos">' + esc((r && r.mesaj) || 'Kampanya detayı alınamadı.') + '</div>';
                return;
            }
            veri = r;
            ustCiz(); ozetCiz(); listeCiz(); yanCiz();
        }).catch(function () {
            if (no === istek && !veri) $('ikd-liste').innerHTML = '<div class="ikd-bos">Kampanya detayı alınamadı.</div>';
        });
    }

    try { if (sessionStorage.getItem('ik_mesaj')) { IK.toast(sessionStorage.getItem('ik_mesaj')); sessionStorage.removeItem('ik_mesaj'); } } catch (e) {}
    donemCiz();
    yukle();
})();
</script>
@endsection
