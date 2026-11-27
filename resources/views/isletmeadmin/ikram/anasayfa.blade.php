@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Ikram Hizmet ana sayfa. Veri sayfa acildiktan sonra /isletmeyonetim/ikramhizmet/veri'den gelir
     (agir hesap sayfanin acilmasini bekletmez). Ust: donem + ozet rakamlar; sol: kampanyalar; sag: ikrami hazir musteriler. --}}
@include('isletmeadmin.ikram._ortak')
@include('isletmeadmin.ikram._kampanya_modal')
<style>
.ik-sayfa { padding-bottom:40px; }
.ik-sayfa .ik-ic { max-width:1320px; }
.ik-sayfa a.ik-btn-ana, .ik-sayfa a.ik-btn-ana:hover { color:#fff !important; cursor:pointer; }

/* Baslik satiri */
.iks-bas { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:22px; }
.iks-bas-yazi { flex:1; min-width:240px; }
.iks-bas-yazi h1 { margin:0; font-size:24px; font-weight:800; color:var(--ik-koyu); }
.iks-bas-yazi p { margin:4px 0 0; font-size:13.5px; color:var(--ik-soluk); }
.iks-bas .ik-btn-ana { width:auto; height:44px; padding:0 20px; font-size:14px; }
.ik-sayfa a.iks-btn-acik { display:inline-flex; align-items:center; gap:8px; height:44px; padding:0 16px; border-radius:22px; background:#fff;
    border:1px solid #E4D7F7; color:var(--ik-koyu) !important; font-weight:700; font-size:13.5px; text-decoration:none; cursor:pointer; }
.ik-sayfa a.iks-btn-acik:hover { border-color:var(--ik-mor); color:var(--ik-mor) !important; }

/* Donem secimi */
.iks-donem { display:inline-flex; background:#fff; border:1px solid #E4D7F7; border-radius:22px; padding:4px; gap:2px; }
.ik-sayfa .iks-donem a { padding:7px 14px; border-radius:18px; font-size:13px; font-weight:700; color:var(--ik-soluk) !important;
    cursor:pointer; text-decoration:none; white-space:nowrap; }
.ik-sayfa .iks-donem a:hover { color:var(--ik-koyu) !important; }
.ik-sayfa .iks-donem a.sec { background:var(--ik-mor); color:#fff !important; }

/* Ozet rakamlar */
.iks-ozet { display:grid; grid-template-columns:1.35fr repeat(3,1fr); gap:14px; margin-bottom:26px; }
.iks-kpi { position:relative; background:#fff; border-radius:20px; padding:18px; box-shadow:0 6px 20px rgba(124,58,237,.07); min-width:0; min-height:132px; }
.iks-kpi .iks-kpi-ust { display:flex; align-items:center; gap:10px; font-size:13px; font-weight:700; color:var(--ik-soluk); }
.iks-kpi .iks-kpi-ikon { width:34px; height:34px; border-radius:11px; display:flex; align-items:center; justify-content:center;
    font-size:15px; flex-shrink:0; background:#F3E8FF; color:var(--ik-mor2); }
.iks-kpi b { display:block; margin-top:12px; font-size:26px; font-weight:900; color:var(--ik-koyu); line-height:1.1; white-space:nowrap;
    overflow:hidden; text-overflow:ellipsis; }
.iks-kpi b small { font-size:14px; font-weight:800; margin-left:3px; }
.iks-kpi .iks-kpi-alt { margin-top:6px; font-size:12px; color:var(--ik-soluk); line-height:1.4; }
.iks-kpi.ana { background:linear-gradient(135deg,#7C3AED,#5B21B6); overflow:hidden; }
.iks-kpi.ana .iks-kpi-ust, .iks-kpi.ana .iks-kpi-alt { position:relative; z-index:1; color:rgba(255,255,255,.88); }
.iks-kpi.ana .iks-kpi-ikon { background:rgba(255,255,255,.18); color:#fff; }
.iks-kpi.ana b { position:relative; z-index:1; color:#fff; font-size:34px; }
.iks-kpi.ana:after { content:''; position:absolute; right:-8px; bottom:-12px; width:130px; height:130px; opacity:.4;
    background:url('{{ secure_asset('public/img/ikram/ikram_m_hediye.png') }}') center/contain no-repeat; }
.iks-trend { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:12px; font-size:12px; font-weight:800; }
.iks-trend.art { background:#D1FAE5; color:#065F46; } .iks-trend.az { background:#FEE2E2; color:#991B1B; }
.iks-bar { height:7px; border-radius:4px; background:#EDE4FB; margin-top:10px; overflow:hidden; }
.iks-bar i { display:block; height:100%; border-radius:4px; background:linear-gradient(90deg,#A855F7,#7C3AED); }

/* Govde: kampanyalar + yan sutun */
.iks-govde { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:22px; align-items:start; }
.iks-bolum-bas { display:flex; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap; }
.iks-bolum-bas h3 { flex:1; margin:0; font-size:17px; font-weight:800; color:var(--ik-koyu); }
.iks-sekme { display:inline-flex; gap:6px; }
.ik-sayfa .iks-sekme a { padding:6px 12px; border-radius:16px; font-size:12.5px; font-weight:700; color:var(--ik-soluk) !important;
    background:rgba(255,255,255,.7); cursor:pointer; text-decoration:none; }
.ik-sayfa .iks-sekme a.sec { background:var(--ik-koyu); color:#fff !important; }
.iks-sekme a span { opacity:.7; margin-left:4px; }

/* Kampanya karti */
.iks-kartlar { display:grid; grid-template-columns:repeat(auto-fill,minmax(330px,1fr)); gap:14px; }
.iks-kart { display:flex; flex-direction:column; background:#fff; border-radius:20px; box-shadow:0 6px 20px rgba(124,58,237,.07);
    transition:transform .15s, box-shadow .15s; }
.iks-kart:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(124,58,237,.13); }
.iks-kart.soluk .iks-kart-ust, .iks-kart.soluk .iks-kart-rakam { opacity:.6; }
.iks-kart-ust { display:flex; gap:14px; padding:16px 16px 12px; cursor:pointer; }
.iks-kart-foto { position:relative; width:76px; height:76px; border-radius:16px; overflow:hidden; flex-shrink:0; }
.iks-kart-foto img { width:100%; height:100%; object-fit:cover; }
.iks-kart-ust h4 { margin:0 0 3px; font-size:15px; font-weight:800; color:var(--ik-koyu); line-height:1.3; }
.iks-kart-ust .iks-kural { font-size:12.5px; font-weight:700; color:var(--ik-mor2); }
.iks-kart-ust .iks-tarih { margin-top:6px; font-size:12px; color:var(--ik-soluk); }
.iks-sure { height:5px; border-radius:3px; background:#EDE4FB; margin-top:7px; overflow:hidden; }
.iks-sure i { display:block; height:100%; background:#A78BFA; }
.iks-kart-rakam { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); margin:0 16px; border-radius:14px; background:#FAF6FF; }
.iks-kart-rakam div { padding:10px 6px; text-align:center; min-width:0; }
.iks-kart-rakam div + div { border-left:1px solid #EDE4FB; }
.iks-kart-rakam b { display:block; font-size:15px; font-weight:900; color:var(--ik-koyu); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.iks-kart-rakam small { font-size:11px; color:var(--ik-soluk); }
.iks-kart-alt { display:flex; align-items:center; gap:8px; padding:12px 16px; margin-top:auto; }
.iks-kart-alt .ik-cip { margin-right:auto; }
.ik-sayfa a.iks-ikon-btn { width:34px; height:34px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
    color:var(--ik-soluk) !important; background:#F5EFFD; text-decoration:none; }
.ik-sayfa a.iks-ikon-btn:hover { color:var(--ik-mor) !important; background:#EDE4FB; }
.ik-sayfa a.iks-ikon-btn.kirmizi:hover { color:#dc2626 !important; background:#FEE2E2; }
/* Ac/kapat anahtari */
.iks-anahtar { display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:700; color:var(--ik-soluk); cursor:pointer; user-select:none; }
.iks-anahtar i { position:relative; width:36px; height:20px; border-radius:10px; background:#D6CCE6; transition:.15s; }
.iks-anahtar i:after { content:''; position:absolute; top:2px; left:2px; width:16px; height:16px; border-radius:50%; background:#fff; transition:.15s;
    box-shadow:0 1px 3px rgba(0,0,0,.2); }
.iks-anahtar.acik i { background:#22C55E; } .iks-anahtar.acik i:after { left:18px; }
.iks-anahtar.bekle { opacity:.5; pointer-events:none; }
.ik-sayfa a.iks-yeni-kart { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; min-height:210px; border-radius:20px;
    border:2px dashed #D8C8F3; color:var(--ik-mor) !important; font-weight:800; cursor:pointer; text-decoration:none; background:rgba(255,255,255,.45); }
.ik-sayfa a.iks-yeni-kart:hover { border-color:var(--ik-mor); background:rgba(255,255,255,.8); }
.iks-yeni-kart span { width:44px; height:44px; border-radius:50%; background:#F3E8FF; display:flex; align-items:center; justify-content:center; font-size:18px; }
.iks-yeni-kart small { font-weight:600; color:var(--ik-soluk); }

/* Yan sutun */
.iks-yan { display:flex; flex-direction:column; gap:16px; }
.iks-panel { background:#fff; border-radius:20px; padding:18px; box-shadow:0 6px 20px rgba(124,58,237,.07); }
.iks-panel h3 { display:flex; align-items:center; gap:8px; margin:0 0 4px; font-size:15.5px; font-weight:800; color:var(--ik-koyu); }
.iks-panel h3 .iks-say { margin-left:auto; min-width:24px; height:24px; padding:0 8px; border-radius:12px; background:var(--ik-mor); color:#fff;
    font-size:12px; display:inline-flex; align-items:center; justify-content:center; }
.iks-panel > p { margin:0 0 12px; font-size:12.5px; color:var(--ik-soluk); line-height:1.45; }
.iks-musteri { display:flex; align-items:center; gap:10px; padding:10px 0; border-top:1px solid #F1EAFB; }
.iks-musteri .iks-avatar { width:38px; height:38px; border-radius:50%; background:#F3E8FF; color:var(--ik-mor2); font-weight:800; font-size:13px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.iks-musteri .iks-m-ad { flex:1; min-width:0; }
.ik-sayfa .iks-musteri .iks-m-ad a { display:block; font-size:13.5px; font-weight:700; color:var(--ik-koyu) !important; white-space:nowrap; overflow:hidden;
    text-overflow:ellipsis; text-decoration:none; }
.ik-sayfa .iks-musteri .iks-m-ad a:hover { color:var(--ik-mor) !important; }
.iks-musteri .iks-m-ad small { display:block; font-size:11.5px; color:var(--ik-soluk); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.iks-musteri .iks-hak { padding:5px 10px; border-radius:14px; background:#EDE9FE; color:#5B21B6; font-size:12px; font-weight:800; white-space:nowrap; }
.ik-sayfa a.iks-panel-alt { display:block; margin-top:6px; padding-top:12px; border-top:1px solid #F1EAFB; text-align:center; font-size:13px; font-weight:800;
    color:var(--ik-mor) !important; cursor:pointer; text-decoration:none; }
.iks-bos-kucuk { padding:12px 0 4px; text-align:center; font-size:12.5px; color:var(--ik-soluk); line-height:1.5; }
.iks-adim { display:flex; gap:12px; padding:8px 0; }
.iks-adim b { width:26px; height:26px; border-radius:50%; background:#F3E8FF; color:var(--ik-mor2); font-size:12.5px; font-weight:900;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.iks-adim div { font-size:12.5px; color:var(--ik-soluk); line-height:1.45; } .iks-adim div strong { display:block; color:var(--ik-koyu); font-size:13px; }

/* Hic kampanya yokken */
.iks-ilk { display:flex; align-items:center; gap:24px; background:#fff; border-radius:24px; padding:28px; box-shadow:0 6px 20px rgba(124,58,237,.07); }
.iks-ilk img { width:170px; flex-shrink:0; border-radius:20px; }
.iks-ilk h3 { margin:0 0 6px; font-size:19px; font-weight:800; color:var(--ik-koyu); }
.iks-ilk p { margin:0 0 16px; font-size:13.5px; color:var(--ik-soluk); line-height:1.55; }
.iks-ilk .ik-btn-ana { width:auto; display:inline-flex; padding:0 22px; height:46px; }

.iks-iskelet { background:linear-gradient(90deg,#F3ECFC 25%,#FAF6FF 50%,#F3ECFC 75%); background-size:200% 100%; animation:iksKay 1.2s infinite;
    border-radius:20px; box-shadow:none; }
@keyframes iksKay { to { background-position:-200% 0; } }

@media (max-width:1199px) { .iks-ozet { grid-template-columns:repeat(2,minmax(0,1fr)); } .iks-govde { grid-template-columns:1fr; } }
@media (max-width:600px) {
    .iks-ozet { gap:10px; } .iks-kpi.ana { grid-column:1 / -1; }
    .iks-kpi { padding:14px; } .iks-kpi b { font-size:21px; } .iks-kpi.ana b { font-size:30px; }
    .iks-kartlar { grid-template-columns:1fr; }
    .iks-bas .ik-btn-ana, .ik-sayfa .iks-bas a.iks-btn-acik { flex:1; justify-content:center; }
    .iks-donem { width:100%; } .ik-sayfa .iks-donem a { flex:1; text-align:center; padding:7px 4px; }
    .iks-ilk { flex-direction:column; text-align:center; padding:22px; } .iks-ilk img { width:130px; }
}
</style>

<div class="ik-sayfa">
    <div class="ik-ic">
        <div class="iks-bas">
            <div class="iks-bas-yazi">
                <h1>İkram Hizmet</h1>
                <p>Bir hizmeti belirlediğiniz sayıda alan müşteriye hediye verin (ör. 5 alana 1 hediye). Müşteri geri gelir, siz kazanırsınız.</p>
            </div>
            <a class="iks-btn-acik" id="ik-bilgi"><i class="fa fa-question-circle"></i> Nasıl çalışır?</a>
            <a class="iks-btn-acik" id="ik-rapor"><i class="fa fa-bar-chart"></i> Detaylı Rapor</a>
            <a class="ik-btn-ana" id="ik-yeni"><span class="ik-arti"><i class="fa fa-plus"></i></span> Yeni Kampanya</a>
        </div>

        <div class="iks-bolum-bas">
            <h3>Özet</h3>
            <div class="iks-donem" id="ik-donem"></div>
        </div>
        <div class="iks-ozet" id="ik-ozet">
            <div class="iks-kpi iks-iskelet"></div><div class="iks-kpi iks-iskelet"></div>
            <div class="iks-kpi iks-iskelet"></div><div class="iks-kpi iks-iskelet"></div>
        </div>

        <div class="iks-govde">
            <div>
                <div class="iks-bolum-bas">
                    <h3>Kampanyalarım</h3>
                    <div class="iks-sekme" id="ik-sekme"></div>
                </div>
                <div id="ik-kartlar"><div class="iks-kartlar"><div class="iks-iskelet" style="height:230px"></div><div class="iks-iskelet" style="height:230px"></div></div></div>
            </div>
            <div class="iks-yan">
                <div class="iks-panel">
                    <h3><i class="fa fa-gift" style="color:var(--ik-mor)"></i> İkramı hazır müşteriler <span class="iks-say" id="ik-hazir-say" style="display:none"></span></h3>
                    <p>Hediyesini kazanmış, henüz kullanmamış müşteriler. Geldiklerinde tahsilat ekranında ikram olarak işleyin.</p>
                    <div id="ik-hazir"><div class="iks-iskelet" style="height:120px"></div></div>
                </div>
                <div class="iks-panel">
                    <h3><i class="fa fa-lightbulb-o" style="color:var(--ik-mor)"></i> 3 adımda ikram</h3>
                    <div class="iks-adim"><b>1</b><div><strong>Kampanya oluşturun</strong>Hizmeti ve kaç alana kaç hediye verileceğini seçin.</div></div>
                    <div class="iks-adim"><b>2</b><div><strong>Müşteri hizmeti alır</strong>"Geldi" işaretli randevular otomatik sayılır, müşteri ilerlemesini uygulamada görür.</div></div>
                    <div class="iks-adim"><b>3</b><div><strong>Hediyeyi işleyin</strong>Müşteri ikramını kullanmak istediğinde tahsilat ekranından ikram olarak düşün.</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var donem = 30, filtre = 'tumu', kurallar = [], ozet = null, musteriler = [], yuklendi = false;
    var FILTRELER = [['tumu', 'Tümü'], ['aktif', 'Aktif'], ['pasif', 'Pasif']];
    var $ = function (id) { return document.getElementById(id); };
    var esc = IK.esc;

    $('ik-yeni').addEventListener('click', function () { IKForm.ac(null, yukle); });
    $('ik-rapor').addEventListener('click', function () { location.href = IK.u('ikramhizmet/rapor', { donem: donem }); });
    $('ik-bilgi').addEventListener('click', function () {
        IK.bilgi('İkram Hizmet nasıl çalışır?',
            '• Bir hizmeti belirlediğiniz sayıda ücretli alan müşteri o hizmetten ikram kazanır (ör. 5+1).<br>' +
            '• Sayım "Geldi" işaretli randevulara göre yapılır; paket seansları sayılmaz.<br>' +
            '• Müşteri ikramlarını uygulamasında görür ve ne zaman kullanacağına kendisi karar verir; kullanmak istediğinde tahsilat ekranından işlenir.<br><br>' +
            '<b>Rakamlar:</b><br>' +
            '• Toplam Kazanç: seçilen dönemde kampanyadaki hizmetlerin satış tutarı (kampanya tarihleri içinde).<br>' +
            '• Ek Kazanç: aynı müşterinin kampanya içindeki 2. ve sonraki alımları — kampanyanın geri getirdiği satış.<br>' +
            '• İkram Kullanımı: kazanılan ikramların kaçının kullanıldığı.');
    });

    function filtreUyar(k, f) {
        var d = IK.durum(k);
        if (f === 'aktif') return d === 'aktif' || d === 'baslayacak';
        if (f === 'pasif') return d === 'pasif' || d === 'bitti';
        return true;
    }
    function ustCiz() {
        $('ik-donem').innerHTML = IK.DONEMLER.map(function (d) {
            return '<a data-donem="' + d[0] + '" class="' + (d[0] === donem ? 'sec' : '') + '">' + d[1] + '</a>';
        }).join('');
        $('ik-sekme').innerHTML = !kurallar.length ? '' : FILTRELER.map(function (f) {
            var n = kurallar.filter(function (k) { return filtreUyar(k, f[0]); }).length;
            return '<a data-filtre="' + f[0] + '" class="' + (f[0] === filtre ? 'sec' : '') + '">' + f[1] + '<span>' + n + '</span></a>';
        }).join('');
    }
    document.addEventListener('click', function (e) {
        var d = e.target.closest('[data-donem]'), f = e.target.closest('[data-filtre]');
        if (d) { donem = Number(d.getAttribute('data-donem')); ustCiz(); yukle(); }
        if (f) { filtre = f.getAttribute('data-filtre'); ustCiz(); kartlariCiz(); }
    });

    function kpi(sinif, ikon, baslik, deger, alt) {
        return '<div class="iks-kpi ' + sinif + '"><div class="iks-kpi-ust"><span class="iks-kpi-ikon"><i class="fa fa-' + ikon + '"></i></span>' + baslik + '</div>' +
            '<b class="ik-sayi">' + deger + '</b><div class="iks-kpi-alt">' + alt + '</div></div>';
    }
    function ozetCiz() {
        var o = ozet || {};
        var trend = (o.artis_yuzde === null || o.artis_yuzde === undefined)
            ? IK.donemYazi(donem) + ' · önceki dönemle kıyas için veri yok'
            : '<span class="iks-trend ' + (Number(o.artis_yuzde) >= 0 ? 'art' : 'az') + '"><i class="fa fa-arrow-' + (Number(o.artis_yuzde) >= 0 ? 'up' : 'down') + '"></i> ' +
              IK.artisYazi(o.artis_yuzde) + '</span> önceki döneme göre';
        var kaz = Number(o.kazanilan_ikram || 0), kul = Number(o.kullanilan_ikram || 0);
        var oran = o.kullanim_orani === null || o.kullanim_orani === undefined ? 0 : parseInt(o.kullanim_orani, 10);
        $('ik-ozet').innerHTML =
            kpi('ana', 'line-chart', 'Toplam Kazanç', IK.tl(o.toplam) + '<small>TL</small>', trend) +
            kpi('', 'calendar-check-o', 'Bu Ay', IK.tl(o.bu_ay) + '<small>TL</small>', 'Kampanyalı hizmetlerden bu ayki satış') +
            kpi('', 'refresh', 'Ek Kazanç', IK.tl(o.ek_kazanc) + '<small>TL</small>', 'Tekrar gelen müşterilerin alımları') +
            kpi('', 'gift', 'İkram Kullanımı', kaz ? kul + ' / ' + kaz : '0',
                (kaz ? 'Kazanılan ikramların %' + oran + '\'i kullanıldı' : 'Henüz kazanılan ikram yok') +
                '<div class="iks-bar"><i style="width:' + Math.min(100, oran) + '%"></i></div>');
    }

    /** Kampanyanin tarih yazisi + aktifse gecen sure yuzdesi */
    function sureBilgi(k, d) {
        if (!k.baslangic) return { yazi: 'Açılınca başlar', yuzde: null };
        var tarih = IK.tarih(k.baslangic) + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz');
        if (!k.bitis_tarihi) return { yazi: tarih, yuzde: null };
        var bas = new Date(String(k.baslangic).slice(0, 10) + 'T00:00:00'), bit = new Date(k.bitis_tarihi + 'T23:59:59'), simdi = new Date();
        var kalan = Math.floor((bit - simdi) / 86400000);
        if (d !== 'aktif' || kalan < 0) return { yazi: tarih, yuzde: null };
        return {
            yazi: tarih + ' · <b style="color:var(--ik-mor)">' + (kalan < 1 ? 'son gün' : kalan + ' gün kaldı') + '</b>',
            yuzde: Math.max(0, Math.min(100, (simdi - bas) / Math.max(1, bit - bas) * 100)),
        };
    }

    function kartlariCiz() {
        var kap = $('ik-kartlar');
        if (!kurallar.length) {
            kap.innerHTML = '<div class="iks-ilk"><img src="' + IK.GORSEL + 'ikram_hediye_kutu.png" alt="">' +
                '<div><h3>İlk ikram kampanyanızı oluşturun</h3>' +
                '<p>Örneğin "5 manikür alana 1 manikür hediye". Müşteriniz ilerlemesini uygulamasında görür, hediyesine yaklaştıkça daha sık gelir.</p>' +
                '<a class="ik-btn-ana" data-yeni="1"><span class="ik-arti"><i class="fa fa-plus"></i></span> Kampanya Oluştur</a></div></div>';
            return;
        }
        var liste = kurallar.filter(function (k) { return filtreUyar(k, filtre); });
        var kamp = (ozet && ozet.kampanyalar) || {};
        var kartlar = liste.map(function (k) {
            var d = IK.durum(k), adet = k.gereken_adet || 0, hediye = k.ucretsiz_adet || 1, hz = k.hizmet_adi || 'Hizmet';
            var s = sureBilgi(k, d), acik = Number(k.aktif) === 1;
            var kaz = (kamp[k.id] || kamp[String(k.id)] || {}).kazanc || 0;
            var mus = musteriler.filter(function (m) { return Number(m.kural_id) === Number(k.id); });
            var hazir = mus.filter(function (m) { return m.hak > 0; }).length;
            return '<div class="iks-kart' + (d === 'pasif' || d === 'bitti' ? ' soluk' : '') + '">' +
                '<div class="iks-kart-ust" data-kart="' + k.id + '" title="Kampanya detayı">' +
                    '<div class="iks-kart-foto">' + IK.gorselImg(k) + '<span class="ik-rozet">' + adet + '+' + hediye + '</span></div>' +
                    '<div style="flex:1;min-width:0">' +
                        '<h4>' + esc(hz) + '</h4>' +
                        '<div class="iks-kural"><i class="fa fa-gift"></i> ' + esc(adet + ' alana ' + hediye + ' hediye') + '</div>' +
                        '<div class="iks-tarih"><i class="fa fa-calendar"></i> ' + s.yazi + '</div>' +
                        (s.yuzde !== null ? '<div class="iks-sure"><i style="width:' + s.yuzde.toFixed(1) + '%"></i></div>' : '') +
                    '</div>' +
                '</div>' +
                '<div class="iks-kart-rakam">' +
                    '<div><b class="ik-sayi">' + IK.tl(kaz) + ' TL</b><small>Kazanç</small></div>' +
                    '<div><b class="ik-sayi">' + mus.length + '</b><small>İkram kazanan</small></div>' +
                    '<div><b class="ik-sayi"' + (hazir ? ' style="color:var(--ik-mor)"' : '') + '>' + hazir + '</b><small>Hediyesi hazır</small></div>' +
                '</div>' +
                '<div class="iks-kart-alt">' +
                    '<span class="ik-cip ' + d + '">' + IK.DURUM_YAZI[d] + '</span>' +
                    (d === 'bitti' ? '' : '<span class="iks-anahtar' + (acik ? ' acik' : '') + '" data-islem="ackapat" data-id="' + k.id + '" title="' +
                        (acik ? 'Kampanyayı kapat' : 'Kampanyayı aç') + '"><i></i>' + (acik ? 'Açık' : 'Kapalı') + '</span>') +
                    '<a class="iks-ikon-btn" data-islem="duzenle" data-id="' + k.id + '" title="Düzenle"><i class="fa fa-pencil"></i></a>' +
                    '<a class="iks-ikon-btn kirmizi" data-islem="sil" data-id="' + k.id + '" title="Sil"><i class="fa fa-trash-o"></i></a>' +
                '</div>' +
            '</div>';
        });
        if (filtre !== 'pasif') kartlar.push('<a class="iks-yeni-kart" data-yeni="1"><span><i class="fa fa-plus"></i></span>Yeni Kampanya<small>Başka bir hizmet için ikram ekleyin</small></a>');
        kap.innerHTML = kartlar.length ? '<div class="iks-kartlar">' + kartlar.join('') + '</div>'
            : '<div class="ik-cam ik-bos">Bu filtrede kampanya yok.</div>';
    }

    function hazirCiz() {
        var hazir = musteriler.filter(function (m) { return m.hak > 0; });
        var say = $('ik-hazir-say');
        say.style.display = hazir.length ? '' : 'none';
        say.textContent = hazir.length;
        var tumu = musteriler.length ? '<a class="iks-panel-alt" data-rapor="1">İkram kazanan tüm müşteriler (' + musteriler.length + ') ›</a>' : '';
        if (!hazir.length) {
            $('ik-hazir').innerHTML = '<div class="iks-bos-kucuk">Şu an hediyesi bekleyen müşteri yok.<br>Müşteriler kampanya adedine ulaştıkça burada görünür.</div>' + tumu;
            return;
        }
        $('ik-hazir').innerHTML = hazir.slice(0, 6).map(function (m) {
            var bas = String(m.ad || '?').trim().split(/\s+/).map(function (p) { return p.charAt(0); }).slice(0, 2).join('').toLocaleUpperCase('tr');
            var son = m.son_kullanma ? ' · son gün ' + IK.tarih(m.son_kullanma) : '';
            return '<div class="iks-musteri"><span class="iks-avatar">' + esc(bas) + '</span>' +
                '<div class="iks-m-ad"><a href="' + IK.u('musteridetay/' + m.user_id) + '" target="_blank" rel="noopener" title="Müşteri detayı (yeni sekme)">' + esc(m.ad) + '</a>' +
                '<small>' + esc(m.hizmet_adi) + son + '</small></div>' +
                '<span class="iks-hak" title="Kullanılabilir ikram"><i class="fa fa-gift"></i> ' + m.hak + '</span></div>';
        }).join('') + tumu;
    }

    $('ik-kartlar').addEventListener('click', function (e) {
        if (e.target.closest('[data-yeni]')) { IKForm.ac(null, yukle); return; }
        var islem = e.target.closest('[data-islem]');
        if (islem) {
            var k = kurallar.filter(function (x) { return String(x.id) === islem.getAttribute('data-id'); })[0];
            if (!k) return;
            var tur = islem.getAttribute('data-islem');
            if (tur === 'duzenle') { IKForm.ac(k, yukle); return; }
            if (tur === 'ackapat') {
                islem.classList.add('bekle');
                IK.acKapat(k).then(function (r) {
                    if (r && r.success) { IK.toast(Number(k.aktif) === 1 ? 'Kampanya kapatıldı' : 'Kampanya açıldı'); yukle(); }
                    else { islem.classList.remove('bekle'); IK.toast((r && r.message) || 'Değiştirilemedi', true); }
                }).catch(function () { islem.classList.remove('bekle'); IK.toast('Değiştirilemedi', true); });
                return;
            }
            if (tur === 'sil') {
                IK.sil(k).then(function (r) {
                    if (r === null) return;
                    if (r && r.success) { IK.toast('Kampanya silindi'); yukle(); }
                    else IK.toast((r && r.message) || 'Silinemedi', true);
                }).catch(function () { IK.toast('Silinemedi', true); });
            }
            return;
        }
        var kart = e.target.closest('[data-kart]');
        if (kart) location.href = IK.u('ikramhizmet/kampanya/' + kart.getAttribute('data-kart'), { donem: donem });
    });
    $('ik-hazir').addEventListener('click', function (e) {
        if (e.target.closest('[data-rapor]')) location.href = IK.u('ikramhizmet/rapor', { donem: donem });
    });

    var istek = 0;
    function yukle() {
        var no = ++istek;
        IK.getJson(IK.u('ikramhizmet/veri', { donem: donem, musteriler: 1 })).then(function (r) {
            if (!r || no !== istek) return;
            kurallar = r.kurallar || [];
            ozet = r.ozet || null;
            musteriler = r.musteriler || [];
            yuklendi = true;
            ustCiz(); ozetCiz(); kartlariCiz(); hazirCiz();
        }).catch(function () {
            if (no !== istek) return;
            if (!yuklendi) { ozetCiz(); kartlariCiz(); hazirCiz(); }
            IK.toast('Veriler yüklenemedi', true);
        });
    }

    try { if (sessionStorage.getItem('ik_mesaj')) { IK.toast(sessionStorage.getItem('ik_mesaj')); sessionStorage.removeItem('ik_mesaj'); } } catch (e) {}
    ustCiz();
    yukle();
})();
</script>
@endsection
