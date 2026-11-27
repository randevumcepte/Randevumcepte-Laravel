@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Detayli Rapor — mobil ikram_rapor.dart ile ayni. Veri /isletmeyonetim/ikramhizmet/veri?musteriler=1. --}}
@include('isletmeadmin.ikram._ortak')
<style>
.ikr-donem { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
.ikr-donem a { padding:8px 18px; border-radius:18px; background:#fff; border:1px solid #E2D4F8; color:#2E1065; font-weight:800; cursor:pointer; text-decoration:none; }
.ikr-donem a.sec { background:#7C3AED; border-color:#7C3AED; color:#fff; }
.ikr-izgara { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px; }
.ikr-kutu { background:#fff; border-radius:16px; padding:14px; }
.ikr-kutu i { color:#7C3AED; } .ikr-kutu small { color:#6B5A8E; font-weight:700; font-size:12.5px; margin-left:4px; }
.ikr-kutu b { display:block; font-size:19px; font-weight:900; color:#2E1065; margin-top:6px; }
.ikr-kutu em { font-style:normal; font-size:11px; color:#6B5A8E; }
.ikr-bolum { display:flex; align-items:center; gap:8px; margin:18px 2px 10px; }
.ikr-bolum h3 { flex:1; margin:0; font-size:16px; font-weight:800; color:#2E1065; }
.ikr-satir { display:flex; align-items:center; gap:12px; background:#fff; border-radius:16px; padding:12px; margin-bottom:8px; }
.ikr-rozet { background:linear-gradient(135deg,#7C3AED,#5B21B6); color:#fff; font-weight:900; font-size:12.5px; padding:6px 10px; border-radius:12px; }
.ikr-ara { width:100%; height:42px; border-radius:21px; border:1px solid #E2D4F8; padding:0 16px; margin-bottom:10px; background:#fff; }
@media (max-width:700px) { .ikr-izgara { grid-template-columns:repeat(2,1fr); } }
</style>

<div class="ik-sayfa">
    <div class="ik-ic" style="max-width:940px">
        <div class="ik-ust">
            <a class="ik-yuv" id="ik-geri" title="Geri"><i class="fa fa-arrow-left"></i></a>
            <h1>Detaylı Rapor</h1>
            <span style="width:44px"></span>
        </div>
        <div class="ikr-donem" id="ikr-donem"></div>
        <div id="ikr-icerik"><div class="ik-yukleniyor"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
    </div>
</div>

<script>
(function () {
    var params = new URLSearchParams(location.search);
    var donem = parseInt(params.get('donem'), 10) || 30;
    var veri = null, ara = '';
    var DUGME = [[7, '7 Gün'], [30, '30 Gün'], [90, '90 Gün'], [365, '1 Yıl']];
    var $ = function (id) { return document.getElementById(id); };
    $('ik-geri').addEventListener('click', function () { location.href = IK.u('ikramhizmet'); });

    function dugmeler() {
        $('ikr-donem').innerHTML = DUGME.map(function (d) {
            return '<a data-donem="' + d[0] + '" class="' + (d[0] === donem ? 'sec' : '') + '">' + d[1] + '</a>';
        }).join('');
    }
    function kutu(ikon, baslik, deger, alt) {
        return '<div class="ikr-kutu"><i class="fa fa-' + ikon + '"></i><small>' + baslik + '</small><b class="ik-sayi">' + deger + '</b>' +
            (alt ? '<em>' + alt + '</em>' : '') + '</div>';
    }
    function musteriListe() {
        var tum = veri.musteriler || [];
        if (!tum.length) return '<div class="ik-cam ik-bos">Henüz ikram kazanan müşteri yok.</div>';
        var q = ara.toLocaleLowerCase('tr-TR');
        var liste = tum.filter(function (m) {
            if (!q) return true;
            return String(m.ad || '').toLocaleLowerCase('tr-TR').indexOf(q) !== -1 || String(m.telefon || '').indexOf(ara) !== -1 ||
                String(m.hizmet_adi || '').toLocaleLowerCase('tr-TR').indexOf(q) !== -1;
        });
        if (!liste.length) return '<div class="ik-cam ik-bos">Aramaya uyan müşteri yok.</div>';
        return liste.map(function (m) {
            return '<div class="ikr-satir"><div style="flex:1;min-width:0">' +
                '<b style="color:#2E1065">' + IK.esc(m.ad) + '</b>' + (m.telefon ? ' <span style="color:#9ca3af;font-size:12.5px">' + IK.esc(m.telefon) + '</span>' : '') +
                '<div style="font-size:12.5px;color:#374151">' + IK.esc(m.hizmet_adi + ' · ' + m.kural) + '</div>' +
                '<div style="font-size:12px;color:#6B5A8E">Ücretli alım ' + m.ucretli + ' · kazanılan ' + m.kazanilan + ' · kullanılan ' + m.kullanilan + '</div>' +
                (m.hak > 0 && m.son_kullanma ? '<div style="font-size:12px;color:#78350F">En yakın son kullanma: ' + IK.tarih(m.son_kullanma) + '</div>' : '') +
                (m.suresi_dolan > 0 ? '<div style="font-size:12px;color:#dc2626">' + m.suresi_dolan + ' ikramın süresi doldu</div>' : '') +
                '</div>' +
                (m.hak > 0 ? '<span style="background:#EDE9FE;color:#5B21B6;font-weight:900;padding:6px 12px;border-radius:16px"><i class="fa fa-gift"></i> ' + m.hak + '</span>'
                    : '<span style="color:#B9A5DD;font-weight:800">—</span>') +
            '</div>';
        }).join('');
    }
    function ciz() {
        var o = veri.ozet || {}, kurallar = veri.kurallar || [], mus = veri.musteriler || [];
        var artis = o.artis_yuzde === null || o.artis_yuzde === undefined ? 'Önceki dönemle kıyas yok'
            : (Number(o.artis_yuzde) >= 0 ? '↑ ' : '↓ ') + IK.artisYazi(o.artis_yuzde);
        var kamp = o.kampanyalar || {};
        var bekleyen = mus.filter(function (m) { return m.hak > 0; }).length;
        var html = '<div class="ikr-izgara">' +
            kutu('money', 'Toplam Kazanç', IK.tl(o.toplam) + ' TL', artis) +
            kutu('history', 'Önceki Dönem', IK.tl(o.onceki) + ' TL') +
            kutu('database', 'Ek Kazanç', IK.tl(o.ek_kazanc) + ' TL', 'Tekrar gelen müşteriler') +
            kutu('calendar', 'Bu Ay', IK.tl(o.bu_ay) + ' TL') +
            kutu('gift', 'Kampanya Kullanımı', parseInt(o.kullanim || 0, 10), 'Dönemde kullanılan ikram') +
            kutu('star', 'Kullanım Oranı', o.kullanim_orani === null || o.kullanim_orani === undefined ? '—' : '%' + o.kullanim_orani,
                (o.kullanilan_ikram || 0) + ' / ' + (o.kazanilan_ikram || 0) + ' ikram') +
            '</div>';
        html += '<div class="ikr-bolum"><i class="fa fa-bullhorn" style="color:#7C3AED"></i><h3>Kampanyalar</h3></div>';
        html += kurallar.length ? kurallar.map(function (k) {
            var tar = !k.baslangic ? 'Başlamadı' : IK.tarih(k.baslangic) + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz');
            var n = mus.filter(function (m) { return m.hizmet_adi === k.hizmet_adi; }).length;
            var kz = kamp[k.id] || kamp[String(k.id)] || {};
            return '<div class="ikr-satir"><span class="ikr-rozet">' + k.gereken_adet + '+' + k.ucretsiz_adet + '</span>' +
                '<div style="flex:1;min-width:0"><b style="color:#2E1065">' + IK.esc(k.hizmet_adi || 'Hizmet') + '</b>' +
                '<div style="font-size:12px;color:#6B5A8E">' + IK.esc(tar) + ' · ' + (Number(k.aktif) === 1 ? 'Aktif' : 'Pasif') + ' · ' + n + ' müşteri</div></div>' +
                '<div style="text-align:right"><b style="color:#2E1065">' + IK.tl(kz.kazanc) + ' TL</b>' +
                '<div style="font-size:11px;color:#6B5A8E">dönem: ' + IK.tl(kz.donem_kazanc) + ' TL</div></div></div>';
        }).join('') : '<div class="ik-cam ik-bos">Henüz kampanya yok.</div>';
        html += '<div class="ikr-bolum"><i class="fa fa-users" style="color:#7C3AED"></i><h3>İkram Kazanan Müşteriler</h3>' +
            (bekleyen ? '<span style="color:#7C3AED;font-weight:800;font-size:13px">' + bekleyen + ' kişinin bekleyen ikramı var</span>' : '') + '</div>';
        if (mus.length > 6) html += '<input class="ikr-ara" id="ikr-ara" placeholder="Müşteri, telefon veya hizmet ara" value="' + IK.esc(ara) + '">';
        html += '<div id="ikr-musteriler">' + musteriListe() + '</div>';
        $('ikr-icerik').innerHTML = html;
    }

    document.addEventListener('click', function (e) {
        var d = e.target.closest('[data-donem]');
        if (d) { donem = Number(d.getAttribute('data-donem')); dugmeler(); yukle(); }
    });
    document.addEventListener('input', function (e) {
        if (e.target.id === 'ikr-ara') { ara = e.target.value; $('ikr-musteriler').innerHTML = musteriListe(); }
    });

    function yukle() {
        $('ikr-icerik').innerHTML = '<div class="ik-yukleniyor"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';
        IK.getJson(IK.u('ikramhizmet/veri', { donem: donem, musteriler: 1 })).then(function (r) {
            veri = r || { kurallar: [], musteriler: [], ozet: null };
            ciz();
        }).catch(function () {
            $('ikr-icerik').innerHTML = '<div class="ik-cam ik-bos">Rapor yüklenemedi.</div>';
        });
    }
    dugmeler();
    yukle();
})();
</script>
@endsection
