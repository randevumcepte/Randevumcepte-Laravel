@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Ikram Hizmet ana sayfa — mobil ikram_hizmet.dart ile ayni akis. Veri sayfa acildiktan sonra
     /isletmeyonetim/ikramhizmet/veri'den gelir (agir hesap sayfanin acilmasini bekletmez). --}}
@include('isletmeadmin.ikram._ortak')
<style>
.ik-hero { position:relative; width:100%; aspect-ratio:941/382; border-radius:24px; margin-bottom:16px;
    background:url('{{ secure_asset('public/img/ikram/ikram_hero_tam.png') }}') center/100% 100% no-repeat; color:#fff; }
.ik-hero .ik-p { position:absolute; }
.ik-hero .ik-donem { left:43%; top:2.6%; height:11.3%; display:flex; align-items:center; gap:6px; font-weight:700;
    font-size:clamp(11px,1.6vw,15px); cursor:pointer; color:#fff; }
.ik-hero .ik-donem .ik-menu-liste { left:0; right:auto; }
.ik-hero .ik-toplam { left:9.1%; top:23%; font-weight:900; font-size:clamp(26px,5.6vw,58px); line-height:1; white-space:nowrap; }
.ik-hero .ik-toplam small { font-size:.42em; font-weight:800; margin-left:6px; }
.ik-hero .ik-artis { left:9.1%; top:49.5%; font-size:clamp(10px,1.4vw,13px); font-weight:800; }
.ik-hero .ik-artis span { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:14px; }
.ik-hero .ik-sutun { top:66%; height:32%; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
.ik-hero .ik-sutun i { font-size:clamp(14px,2vw,20px); opacity:.95; }
.ik-hero .ik-sutun b { font-size:clamp(14px,2.3vw,22px); font-weight:900; line-height:1.15; margin-top:2px; }
.ik-hero .ik-sutun small { font-size:clamp(9px,1.15vw,12px); opacity:.9; line-height:1.2; white-space:pre-line; }
.ik-liste-bas { display:flex; align-items:center; gap:10px; margin:6px 2px 12px; }
.ik-liste-bas h3 { flex:1; margin:0; font-size:16px; font-weight:800; color:#2E1065; }
.ik-kartlar { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:14px; }
.ik-kk { position:relative; display:flex; gap:14px; background:#FDFBFF; border-radius:22px; border-left:4px solid #7C3AED; padding:14px;
    box-shadow:0 6px 18px rgba(124,58,237,.08); cursor:pointer; transition:.15s; }
.ik-kk:hover { transform:translateY(-2px); box-shadow:0 10px 26px rgba(124,58,237,.14); }
.ik-kk.soluk { opacity:.6; }
.ik-kk .ik-foto { position:relative; width:84px; height:84px; border-radius:16px; overflow:hidden; flex-shrink:0; }
.ik-kk .ik-foto img { width:100%; height:100%; object-fit:cover; }
.ik-kk .ik-govde { flex:1; min-width:0; }
.ik-kk h4 { margin:0 26px 4px 0; font-size:14.5px; font-weight:800; color:#2E1065; line-height:1.3; }
.ik-kk .ik-alt { font-size:12.5px; color:#6B5A8E; white-space:pre-line; line-height:1.35; }
.ik-kk .ik-tarih { font-size:12px; color:#6B5A8E; margin-top:6px; }
.ik-kk .ik-kaz { position:absolute; right:12px; bottom:12px; background:#F1E9FD; border-radius:14px; padding:6px 10px; text-align:right; }
.ik-kk .ik-kaz b { display:block; font-size:13px; color:#2E1065; } .ik-kk .ik-kaz small { font-size:10px; color:#6B5A8E; }
.ik-kk .ik-menu { position:absolute; top:8px; right:8px; }
.ik-kk .ik-menu-ac { width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:50%; color:#6B5A8E; }
.ik-kk .ik-menu-ac:hover { background:#F1E9FD; }
.ik-alt-cubuk { position:sticky; bottom:12px; z-index:5; display:flex; align-items:center; gap:12px; margin-top:18px; }
.ik-alt-cubuk .ik-ikon { width:46px; height:46px; border-radius:50%; background:linear-gradient(135deg,#F3E8FF,#DDD0FB); display:flex;
    align-items:center; justify-content:center; color:#5B21B6; font-size:20px; flex-shrink:0; }
.ik-alt-cubuk .ik-buay { flex:1; } .ik-alt-cubuk .ik-buay small { color:#6B5A8E; font-weight:700; }
.ik-alt-cubuk .ik-buay b { display:block; font-size:22px; font-weight:900; color:#2E1065; }
.ik-rapor-btn { padding:12px 18px; border-radius:22px; background:linear-gradient(90deg,#6D28D9,#4C1D95); color:#fff; font-weight:800; text-decoration:none; white-space:nowrap; }
.ik-rapor-btn:hover { color:#fff; text-decoration:none; }
@media (max-width:600px) { .ik-kartlar { grid-template-columns:1fr; } }
</style>

<div class="ik-sayfa">
    <img class="ik-yaprak sol" src="{{ secure_asset('public/img/ikram/kampanya_yaprak_sol.png') }}" alt="">
    <img class="ik-yaprak sag" src="{{ secure_asset('public/img/ikram/kampanya_yaprak_sag.png') }}" alt="">
    <div class="ik-ic">
        <div class="ik-ust">
            <span style="width:44px"></span>
            <h1>İkram Hizmet</h1>
            <a class="ik-yuv" id="ik-bilgi" title="Nasıl çalışır?"><i class="fa fa-info"></i></a>
        </div>

        <div class="ik-hero">
            <div class="ik-p ik-donem ik-menu">
                <span class="ik-menu-ac" id="ik-donem-yazi">Son 30 Gün</span> <i class="fa fa-chevron-down ik-menu-ac" style="font-size:11px"></i>
                <div class="ik-menu-liste" id="ik-donem-liste"></div>
            </div>
            <div class="ik-p ik-toplam"><span class="ik-sayi" id="ik-toplam">…</span><small>TL</small></div>
            <div class="ik-p ik-artis" id="ik-artis"></div>
            <div class="ik-p ik-sutun" style="left:11%;width:17.6%"><i class="fa fa-users"></i><b class="ik-sayi" id="ik-kullanim">…</b><small>Kampanya
Kullanımı</small></div>
            <div class="ik-p ik-sutun" style="left:31%;width:16.4%"><i class="fa fa-money"></i><b class="ik-sayi" id="ik-ek">…</b><small>Ek
Kazanç</small></div>
            <div class="ik-p ik-sutun" style="left:49.5%;width:14.9%"><i class="fa fa-star"></i><b class="ik-sayi" id="ik-oran">…</b><small>Kullanım
Oranı</small></div>
        </div>

        <div class="ik-cam">
            <div class="ik-baslik">
                <div class="ik-ikon"><i class="fa fa-gift"></i></div>
                <div><h3>Kampanya Oluştur</h3><p>Hizmeti belirlediğiniz adette alan müşteriye hediye hizmet verin.</p></div>
            </div>
            <a class="ik-btn-ana" id="ik-yeni"><span class="ik-arti"><i class="fa fa-plus"></i></span> Yeni Kampanya</a>
        </div>

        <div class="ik-liste-bas">
            <i class="fa fa-list-ul" style="color:#7C3AED"></i>
            <h3>Aktif Kampanyalarım</h3>
            <div class="ik-menu">
                <span class="ik-hap ik-menu-ac"><span id="ik-filtre-yazi">Tümü</span> <i class="fa fa-chevron-down" style="font-size:10px"></i></span>
                <div class="ik-menu-liste" id="ik-filtre-liste"></div>
            </div>
        </div>
        <div id="ik-kartlar"><div class="ik-yukleniyor"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>

        <div class="ik-cam ik-alt-cubuk">
            <div class="ik-ikon"><i class="fa fa-line-chart"></i></div>
            <div class="ik-buay"><small>Bu Ay Toplam Kazanç</small><b class="ik-sayi"><span id="ik-buay">…</span> TL</b></div>
            <a class="ik-rapor-btn" id="ik-rapor">Detaylı Rapor ›</a>
        </div>
    </div>
</div>

<script>
(function () {
    var donem = 30, filtre = 'tumu', kurallar = [], ozet = null;
    var FILTRELER = [['tumu', 'Tümü'], ['aktif', 'Aktif'], ['pasif', 'Pasif']];
    var $ = function (id) { return document.getElementById(id); };

    $('ik-yeni').href = IK.u('ikramhizmet/kampanya-ayarlari');
    $('ik-rapor').addEventListener('click', function () { location.href = IK.u('ikramhizmet/rapor', { donem: donem }); });
    $('ik-bilgi').addEventListener('click', function () {
        IK.bilgi('İkram Hizmet nasıl çalışır?',
            '• Bir hizmeti belirlediğiniz sayıda ücretli alan müşteri o hizmetten ikram kazanır (ör. 5+1).<br>' +
            '• Sayım "Geldi" işaretli randevulara göre yapılır; paket seansları sayılmaz.<br>' +
            '• Müşteri ikramlarını uygulamasında görür ve ne zaman kullanacağına kendisi karar verir; kullanmak istediğinde tahsilat ekranından işlenir.<br><br>' +
            '<b>Rakamlar:</b><br>' +
            '• Toplam Kazanç: seçilen dönemde kampanyadaki hizmetlerin satış tutarı (kampanya tarihleri içinde).<br>' +
            '• Ek Kazanç: aynı müşterinin kampanya içindeki 2. ve sonraki alımları — kampanyanın geri getirdiği satış.<br>' +
            '• Kampanya Kullanımı: dönemde kullanılan ikram sayısı.<br>' +
            '• Kullanım Oranı: kazanılan ikramların ne kadarının kullanıldığı.');
    });

    function menuDoldur() {
        $('ik-donem-liste').innerHTML = IK.DONEMLER.map(function (d) {
            return '<a data-donem="' + d[0] + '" class="' + (d[0] === donem ? 'sec' : '') + '">' + d[1] + '</a>';
        }).join('');
        $('ik-filtre-liste').innerHTML = FILTRELER.map(function (f) {
            return '<a data-filtre="' + f[0] + '" class="' + (f[0] === filtre ? 'sec' : '') + '">' + f[1] + '</a>';
        }).join('');
        $('ik-donem-yazi').textContent = IK.donemYazi(donem);
        $('ik-filtre-yazi').textContent = FILTRELER.filter(function (f) { return f[0] === filtre; })[0][1];
    }
    document.addEventListener('click', function (e) {
        var d = e.target.closest('[data-donem]'), f = e.target.closest('[data-filtre]');
        if (d) { donem = Number(d.getAttribute('data-donem')); menuDoldur(); yukle(); }
        if (f) { filtre = f.getAttribute('data-filtre'); menuDoldur(); kartlariCiz(); }
    });

    function ozetCiz() {
        var o = ozet || {};
        $('ik-toplam').textContent = IK.tl(o.toplam);
        var a = $('ik-artis');
        if (o.artis_yuzde === null || o.artis_yuzde === undefined) {
            a.innerHTML = '<span style="padding:0;color:rgba(255,255,255,.75)">Önceki dönemle kıyas yok</span>';
        } else {
            var art = Number(o.artis_yuzde) >= 0;
            a.innerHTML = '<span style="background:' + (art ? '#6EE7B7' : '#FCA5A5') + ';color:' + (art ? '#064E3B' : '#7F1D1D') + '">' +
                '<i class="fa fa-arrow-' + (art ? 'up' : 'down') + '"></i> ' + IK.artisYazi(o.artis_yuzde) + '</span>';
        }
        $('ik-kullanim').textContent = parseInt(o.kullanim || 0, 10);
        $('ik-ek').textContent = IK.tl(o.ek_kazanc) + ' TL';
        $('ik-oran').textContent = o.kullanim_orani === null || o.kullanim_orani === undefined ? '—' : '%' + parseInt(o.kullanim_orani, 10);
        $('ik-buay').textContent = IK.tl(o.bu_ay);
    }

    function kartlariCiz() {
        var kap = $('ik-kartlar');
        if (!kurallar.length) {
            kap.innerHTML = '<div class="ik-cam ik-bos">Henüz kampanya yok.\n"Yeni Kampanya" ile ilk kampanyanızı oluşturun.</div>';
            return;
        }
        var liste = kurallar.filter(function (k) {
            var d = IK.durum(k);
            if (filtre === 'aktif') return d === 'aktif' || d === 'baslayacak';
            if (filtre === 'pasif') return d === 'pasif' || d === 'bitti';
            return true;
        });
        if (!liste.length) { kap.innerHTML = '<div class="ik-cam ik-bos">Bu filtrede kampanya yok.</div>'; return; }
        var kamp = (ozet && ozet.kampanyalar) || {};
        kap.innerHTML = '<div class="ik-kartlar">' + liste.map(function (k) {
            var d = IK.durum(k), adet = k.gereken_adet || 0, hediye = k.ucretsiz_adet || 1, hz = k.hizmet_adi || 'Hizmet';
            var tarih = !k.baslangic ? 'Açılınca başlar' : IK.tarih(k.baslangic) + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz');
            var kaz = (kamp[k.id] || kamp[String(k.id)] || {}).kazanc || 0;
            return '<div class="ik-kk' + (d === 'pasif' || d === 'bitti' ? ' soluk' : '') + '" data-kart="' + k.id + '">' +
                '<div class="ik-foto">' + IK.gorselImg(k) + '<span class="ik-rozet">' + adet + '+' + hediye + '</span></div>' +
                '<div class="ik-govde">' +
                    '<h4>' + IK.esc(adet + '+' + hediye + ' ' + hz + ' Kampanyası') + '</h4>' +
                    '<div class="ik-alt">' + IK.esc(adet + ' ' + hz + ' alanlara\n' + hediye + ' ' + hz + ' hediye!') + '</div>' +
                    '<div class="ik-tarih"><i class="fa fa-calendar"></i> ' + IK.esc(tarih) + '</div>' +
                    '<div style="margin-top:8px"><span class="ik-cip ' + d + '">' + IK.DURUM_YAZI[d] + '</span></div>' +
                '</div>' +
                '<div class="ik-kaz"><b><i class="fa fa-bar-chart" style="color:#7C3AED"></i> ' + IK.tl(kaz) + ' TL</b><small>Kazanç</small></div>' +
                '<div class="ik-menu"><span class="ik-menu-ac"><i class="fa fa-ellipsis-v"></i></span><div class="ik-menu-liste">' +
                    '<a data-islem="duzenle" data-id="' + k.id + '"><i class="fa fa-pencil"></i> Düzenle</a>' +
                    '<a data-islem="ackapat" data-id="' + k.id + '"><i class="fa fa-' + (Number(k.aktif) === 1 ? 'pause' : 'play') + '"></i> ' + (Number(k.aktif) === 1 ? 'Kapat' : 'Aç') + '</a>' +
                    '<a data-islem="sil" data-id="' + k.id + '" class="kirmizi"><i class="fa fa-trash-o"></i> Sil</a>' +
                '</div></div>' +
            '</div>';
        }).join('') + '</div>';
    }

    $('ik-kartlar').addEventListener('click', function (e) {
        var islem = e.target.closest('[data-islem]');
        if (islem) {
            e.stopPropagation();
            var k = kurallar.filter(function (x) { return String(x.id) === islem.getAttribute('data-id'); })[0];
            if (!k) return;
            var tur = islem.getAttribute('data-islem');
            if (tur === 'duzenle') { location.href = IK.u('ikramhizmet/kampanya-ayarlari', { id: k.id }); return; }
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
                    if (r && r.success) { IK.toast('Kampanya silindi'); yukle(); }
                    else IK.toast((r && r.message) || 'Silinemedi', true);
                }).catch(function () { IK.toast('Silinemedi', true); });
            }
            return;
        }
        if (e.target.closest('.ik-menu')) return;
        var kart = e.target.closest('[data-kart]');
        if (kart) location.href = IK.u('ikramhizmet/kampanya/' + kart.getAttribute('data-kart'), { donem: donem });
    });

    function yukle() {
        IK.getJson(IK.u('ikramhizmet/veri', { donem: donem })).then(function (r) {
            if (!r) return;
            kurallar = r.kurallar || [];
            ozet = r.ozet || null;
            ozetCiz();
            kartlariCiz();
        }).catch(function () {
            kartlariCiz();
            IK.toast('Veriler yüklenemedi', true);
        });
    }

    try { if (sessionStorage.getItem('ik_mesaj')) { IK.toast(sessionStorage.getItem('ik_mesaj')); sessionStorage.removeItem('ik_mesaj'); } } catch (e) {}
    menuDoldur();
    yukle();
})();
</script>
@endsection
