@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Kampanya Ayarlari (yeni / duzenle) — mobil kampanya_ayarlari.dart ile ayni alanlar ve kayit akisi. --}}
@include('isletmeadmin.ikram._ortak')
@php
    $k = $ikramKural;
    $varGereken = $k['gereken_adet'] ?? 5;
    $varHediye = $k['ucretsiz_adet'] ?? 1;
    $varSure = (int) ($k['gecerlilik_deger'] ?? 0);
    $varBirim = ($k['gecerlilik_birim'] ?? 'ay') === 'gun' ? 'gun' : 'ay';
    $sureSecenek = [['0|ay', 'Süresiz'], ['15|gun', '15 gün'], ['30|gun', '30 gün'], ['2|ay', '2 ay'], ['3|ay', '3 ay'], ['6|ay', '6 ay'], ['12|ay', '12 ay']];
    $mevcutSure = $varSure > 0 ? $varSure . '|' . $varBirim : '0|ay';
    if (!in_array($mevcutSure, array_column($sureSecenek, 0), true)) {
        array_splice($sureSecenek, 1, 0, [[$mevcutSure, $varSure . ($varBirim === 'gun' ? ' gün' : ' ay')]]);
    }
@endphp
<style>
.ik-form-hero { position:relative; width:100%; aspect-ratio:941/362; border-radius:24px; margin-bottom:16px;
    background:url('{{ secure_asset('public/img/ikram/kampanya_hero_tam.png') }}') center/100% 100% no-repeat; }
.ik-form-hero .ik-hero-yazi { position:absolute; left:10.1%; top:63%; color:#fff; font-weight:800; font-size:clamp(12px,2vw,19px);
    line-height:1.3; white-space:pre-line; text-shadow:0 1px 6px rgba(46,16,101,.35); }
.ik-satirlar { background:rgba(255,255,255,.7); border:1px solid #EDE4FA; border-radius:18px; }
.ik-satir2 { display:flex; align-items:center; gap:12px; padding:14px; border-top:1px solid #EDE4FA; flex-wrap:wrap; }
.ik-satir2:first-child { border-top:0; }
.ik-satir2 .ik-ki { width:38px; height:38px; border-radius:50%; background:#F3ECFE; color:#7C3AED; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ik-satir2 .ik-st { flex:1; min-width:180px; } .ik-satir2 .ik-st b { display:block; font-size:14px; color:#2E1065; }
.ik-satir2 .ik-st small { font-size:12px; color:#6B5A8E; }
.ik-deger { min-width:220px; }
.ik-deger .select2-container .select2-selection--single { height:40px; border:1px solid #E2D4F8; border-radius:20px; background:#F1E9FD; }
.ik-deger .select2-container .select2-selection__rendered { line-height:38px; padding-left:14px; color:#2E1065; font-weight:700; }
.ik-deger .select2-container .select2-selection__arrow { height:38px; right:8px; }
.ik-deger select, .ik-girdi { height:40px; border:1px solid #E2D4F8; border-radius:20px; background:#F1E9FD; color:#2E1065;
    font-weight:700; padding:0 14px; font-size:14px; }
.ik-sayac { display:flex; align-items:center; gap:10px; }
.ik-sayac button { width:36px; height:36px; border-radius:50%; border:0; background:#EDE3FD; color:#5B21B6; font-size:16px; cursor:pointer; }
.ik-sayac button:disabled { background:#F5F0FC; color:#C4B5E0; cursor:default; }
.ik-sayac b { min-width:32px; text-align:center; font-size:18px; color:#2E1065; }
.ik-gorsel { display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
.ik-gorsel .ik-on { position:relative; width:92px; height:92px; border-radius:16px; overflow:hidden; flex-shrink:0; }
.ik-gorsel .ik-on img { width:100%; height:100%; object-fit:cover; }
.ik-gbtn { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:20px; font-weight:800; font-size:13px; cursor:pointer; border:0; }
.ik-gbtn.dolu { background:#7C3AED; color:#fff; } .ik-gbtn.bos { background:#fff; color:#5B21B6; border:1px solid #DCCBF6; }
.ik-tarihler { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.ik-tarihler input { width:140px; }
.ik-anahtar { position:relative; width:52px; height:30px; flex-shrink:0; }
.ik-anahtar input { opacity:0; width:0; height:0; }
.ik-anahtar span { position:absolute; inset:0; border-radius:20px; background:#D9CCEE; transition:.2s; cursor:pointer; }
.ik-anahtar span:before { content:''; position:absolute; width:24px; height:24px; left:3px; top:3px; border-radius:50%; background:#fff; transition:.2s; }
.ik-anahtar input:checked + span { background:#7C3AED; } .ik-anahtar input:checked + span:before { transform:translateX(22px); }
.ik-kaydet { max-width:340px; height:60px; border-radius:32px; margin:20px auto 0; font-size:17px; }
</style>

<div class="ik-sayfa">
    <img class="ik-yaprak sol" style="width:140px;opacity:.9" src="{{ secure_asset('public/img/ikram/kampanya_yaprak_sol.png') }}" alt="">
    <img class="ik-yaprak sag" style="width:120px;opacity:.9" src="{{ secure_asset('public/img/ikram/kampanya_yaprak_sag.png') }}" alt="">
    <div class="ik-ic" style="max-width:820px">
        <div class="ik-ust">
            <a class="ik-yuv" id="ik-geri" title="Geri"><i class="fa fa-arrow-left"></i></a>
            <h1>Kampanya Ayarları</h1>
            <a class="ik-yuv" id="ik-bilgi" title="Nasıl çalışır?"><i class="fa fa-info"></i></a>
        </div>

        <div class="ik-form-hero"><div class="ik-hero-yazi" id="ik-hero-yazi"></div></div>

        <div class="ik-cam">
            <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-sliders"></i></div>
                <div><h3>Kampanya Detayları</h3><p>Kampanyanın koşullarını belirleyin.</p></div></div>
            <div class="ik-satirlar">
                <div class="ik-satir2">
                    <div class="ik-ki"><i class="fa fa-leaf"></i></div>
                    <div class="ik-st"><b>Hizmet</b><small>Kampanyanın geçerli olduğu hizmet.</small></div>
                    <div class="ik-deger"><select id="ik-hizmet" style="width:100%">
                        <option value="">Hizmet seçin</option>
                        @foreach($ikramHizmetleri as $h)
                            <option value="{{ $h['id'] }}" {{ $k && (int) $k['hizmet_id'] === (int) $h['id'] ? 'selected' : '' }}>{{ $h['hizmet_adi'] }}</option>
                        @endforeach
                    </select></div>
                </div>
                <div class="ik-satir2">
                    <div class="ik-ki"><i class="fa fa-bar-chart"></i></div>
                    <div class="ik-st"><b>Hizmet Adedi</b><small>Müşterinin alması gereken hizmet adedini girin.</small></div>
                    <div class="ik-sayac"><button type="button" id="ik-eksi">−</button><b id="ik-adet">{{ $varGereken }}</b><button type="button" id="ik-arti">+</button></div>
                </div>
                <div class="ik-satir2">
                    <div class="ik-ki"><i class="fa fa-gift"></i></div>
                    <div class="ik-st"><b>Hediye Hizmet</b><small>Müşteriye verilecek hediye hizmeti seçin.</small></div>
                    <div class="ik-deger" style="min-width:140px"><select id="ik-hediye" style="width:100%">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}" {{ (int) $varHediye === $i ? 'selected' : '' }}>{{ $i }} Hizmet</option>@endfor
                    </select></div>
                </div>
            </div>
        </div>

        <div class="ik-cam">
            <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-picture-o"></i></div>
                <div><h3>Kampanya Görseli</h3><p>Kampanya kartında görünecek fotoğraf.</p></div></div>
            <div class="ik-gorsel">
                <div class="ik-on"><img id="ik-onizleme" src="" alt=""><span class="ik-rozet" id="ik-on-rozet"></span></div>
                <div style="flex:1;min-width:200px">
                    <div id="ik-gorsel-yazi" style="font-size:13px;color:#6B5A8E;margin-bottom:10px"></div>
                    <button type="button" class="ik-gbtn dolu" id="ik-foto-sec"><i class="fa fa-image"></i> <span id="ik-foto-sec-yazi">Fotoğraf Seç</span></button>
                    <button type="button" class="ik-gbtn bos" id="ik-foto-kaldir" style="display:none"><i class="fa fa-times"></i> Kaldır</button>
                    <input type="file" id="ik-dosya" accept="image/*" style="display:none">
                </div>
            </div>
        </div>

        <div class="ik-cam">
            <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-calendar"></i></div>
                <div><h3>Geçerlilik Süresi</h3><p>Kampanyanın ne kadar süreyle geçerli olacağını belirleyin.</p></div></div>
            <div class="ik-satirlar">
                <div class="ik-satir2">
                    <div class="ik-ki"><i class="fa fa-calendar-check-o"></i></div>
                    <div class="ik-st"><b>Kampanya tarihleri</b><small>Yalnız bu aralıkta gelinen ziyaretler sayılır. Başlangıç geçmiş bir tarih olabilir; o tarihten beri gelinen ziyaretler de sayılır. Kazanılan hak kampanya bitince de kullanılabilir.</small></div>
                    <div class="ik-tarihler">
                        <input type="text" class="form-control geriye-yonelik ik-girdi" id="ik-bas" placeholder="Bugün" autocomplete="off"
                               value="{{ !empty($k['baslangic']) ? date('Y-m-d', strtotime($k['baslangic'])) : '' }}">
                        –
                        <input type="text" class="form-control geriye-yonelik ik-girdi" id="ik-bit" placeholder="Süresiz" autocomplete="off"
                               value="{{ $k['bitis_tarihi'] ?? '' }}">
                        <a href="javascript:void(0)" id="ik-suresiz" style="font-size:12px;color:#7C3AED;font-weight:700">Bitiş olmasın</a>
                    </div>
                </div>
                <div class="ik-satir2">
                    <div class="ik-ki"><i class="fa fa-hourglass-half"></i></div>
                    <div class="ik-st"><b>Kazanılan hakkın kullanım süresi</b><small>Süre seçerseniz hak, kazanıldığı günden itibaren o süre içinde kullanılmazsa düşer.</small></div>
                    <div class="ik-deger" style="min-width:140px"><select id="ik-sure" style="width:100%">
                        @foreach($sureSecenek as $s)<option value="{{ $s[0] }}" {{ $s[0] === $mevcutSure ? 'selected' : '' }}>{{ $s[1] }}</option>@endforeach
                    </select></div>
                </div>
            </div>
        </div>

        <div class="ik-cam" style="display:flex;align-items:center;gap:14px">
            <label class="ik-anahtar"><input type="checkbox" id="ik-aktif" {{ (int) ($k['aktif'] ?? 1) === 1 ? 'checked' : '' }}><span></span></label>
            <div style="flex:1"><b style="font-size:15px;color:#2E1065">Kampanyayı Aktif Et</b>
                <div id="ik-aktif-yazi" style="font-size:12.5px;color:#6B5A8E"></div></div>
            <i class="fa fa-magic" style="color:#7C3AED;font-size:20px"></i>
        </div>

        <button type="button" class="ik-btn-ana ik-kaydet" id="ik-kaydet"><span class="ik-arti"><i class="fa fa-check"></i></span> <span id="ik-kaydet-yazi">Kaydet</span></button>
    </div>
</div>

<script>
(function () {
    var MEVCUT = {!! json_encode($k) !!};
    var $ = function (id) { return document.getElementById(id); };
    var params = new URLSearchParams(location.search);
    var donusDetay = params.get('don') === 'detay' && MEVCUT;
    var yeniDosya = null, gorselKaldir = false;

    function geriAdres() {
        return donusDetay ? IK.u('ikramhizmet/kampanya/' + MEVCUT.id) : IK.u('ikramhizmet');
    }
    $('ik-geri').addEventListener('click', function () { location.href = geriAdres(); });
    $('ik-bilgi').addEventListener('click', function () {
        IK.bilgi('Kampanya nasıl çalışır?',
            '• X + 1: Müşteri seçtiğiniz hizmeti belirlediğiniz adet kadar ücretli alınca hediye hizmet kazanır.<br>' +
            '• Yüzde indirim kampanyaları için Kampanyalar bölümünü kullanın.<br><br>' +
            '• Sayım "Geldi" işaretli ücretli randevulara göre yapılır; paket/seanslı satış ve telafi seansları sayılmaz.<br>' +
            '• Yalnız kampanya tarihleri içindeki ziyaretler sayılır. Başlangıcı geçmiş bir tarih seçerseniz o tarihten beri gelinen ziyaretler de sayılır.<br>' +
            '• Müşteri kazandığını uygulamasında görür, ne zaman kullanacağına kendisi karar verir. Kullanmak istediğinde tahsilat ekranından işlenir.<br>' +
            '• Kullanım süresi seçerseniz hak, kazanıldığı günden itibaren o süre içinde kullanılmazsa düşer.<br>' +
            '• Kampanyayı kapatıp açmak müşterilerin ilerlemesini silmez.');
    });

    function adet() { return parseInt($('ik-adet').textContent, 10) || 5; }
    function hediye() { return parseInt($('ik-hediye').value, 10) || 1; }
    function hizmetAdi() {
        var s = $('ik-hizmet');
        return s.value ? s.options[s.selectedIndex].text : '';
    }
    function ciz() {
        $('ik-eksi').disabled = adet() <= 1;
        $('ik-arti').disabled = adet() >= 100;
        $('ik-hero-yazi').textContent = 'Belirlediğin adetlerde\n' + hediye() + ' hizmet hediye et.';
        $('ik-on-rozet').textContent = adet() + '+' + hediye();
        $('ik-aktif-yazi').textContent = $('ik-aktif').checked ? 'Kampanya hemen aktif olsun.'
            : 'Kampanya kapalı; ziyaretler sayılmaya devam eder, hak kullandırılmaz.';
        var kendi = !!yeniDosya || (MEVCUT && MEVCUT.gorsel && !gorselKaldir);
        var hazir = IK.hazirGorsel(hizmetAdi());
        var img = $('ik-onizleme');
        img.onerror = function () { img.onerror = null; img.src = hazir; };
        if (yeniDosya) {
            img.src = URL.createObjectURL(yeniDosya);
            $('ik-gorsel-yazi').textContent = 'Seçtiğiniz fotoğraf kaydedince yüklenecek.';
        } else if (MEVCUT && MEVCUT.gorsel && !gorselKaldir) {
            img.src = MEVCUT.gorsel;
            $('ik-gorsel-yazi').textContent = 'Kendi fotoğrafınız kullanılıyor.';
        } else {
            img.src = hazir;
            $('ik-gorsel-yazi').textContent = 'Otomatik: hizmete uygun hazır görsel. Kendi fotoğrafınızı da yükleyebilirsiniz.';
        }
        $('ik-foto-sec-yazi').textContent = kendi ? 'Değiştir' : 'Fotoğraf Seç';
        $('ik-foto-kaldir').style.display = kendi ? '' : 'none';
    }
    $('ik-eksi').addEventListener('click', function () { $('ik-adet').textContent = Math.max(1, adet() - 1); ciz(); });
    $('ik-arti').addEventListener('click', function () { $('ik-adet').textContent = Math.min(100, adet() + 1); ciz(); });
    $('ik-aktif').addEventListener('change', ciz);
    $('ik-foto-sec').addEventListener('click', function () { $('ik-dosya').click(); });
    $('ik-dosya').addEventListener('change', function () {
        if (this.files && this.files[0]) { yeniDosya = this.files[0]; gorselKaldir = false; ciz(); }
        this.value = '';
    });
    $('ik-foto-kaldir').addEventListener('click', function () { yeniDosya = null; gorselKaldir = true; ciz(); });
    $('ik-suresiz').addEventListener('click', function () { $('ik-bit').value = ''; });

    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#ik-hizmet').select2({ width: '100%', placeholder: 'Hizmet seçin',
                language: { noResults: function () { return 'Hizmet bulunamadı'; } } }).on('change', ciz);
        }
    });
    $('ik-hizmet').addEventListener('change', ciz);
    $('ik-hediye').addEventListener('change', ciz);

    function gorselIslem(id) {
        if (yeniDosya) {
            var fd = new FormData(); fd.append('id', id); fd.append('dosya', yeniDosya);
            return IK.postForm(IK.u('ikramhizmet/gorsel'), fd);
        }
        if (gorselKaldir && MEVCUT && MEVCUT.gorsel) {
            var fd2 = new FormData(); fd2.append('id', id); fd2.append('sil', '1');
            return IK.postForm(IK.u('ikramhizmet/gorsel'), fd2);
        }
        return Promise.resolve({ success: true });
    }

    $('ik-kaydet').addEventListener('click', function () {
        var hizmetId = parseInt($('ik-hizmet').value, 10) || 0;
        if (!hizmetId) { IK.toast('Lütfen kampanyanın hizmetini seçin', true); return; }
        var sure = $('ik-sure').value.split('|');
        var bas = $('ik-bas').value.trim(), bit = $('ik-bit').value.trim();
        if (bas && bit && bit < bas) { IK.toast('Bitiş tarihi başlangıçtan önce olamaz', true); return; }
        var veri = {
            hizmet_id: hizmetId, tur: 'ikram', gereken_adet: adet(), ucretsiz_adet: hediye(),
            aktif: $('ik-aktif').checked ? 1 : 0, gecerlilik_deger: parseInt(sure[0], 10) || 0, gecerlilik_birim: sure[1] === 'gun' ? 'gun' : 'ay',
            baslangic_tarihi: bas, bitis_tarihi: bit,
        };
        if (MEVCUT) veri.id = MEVCUT.id;
        var btn = $('ik-kaydet');
        btn.disabled = true; $('ik-kaydet-yazi').textContent = 'Kaydediliyor…';
        var bitir = function () { btn.disabled = false; $('ik-kaydet-yazi').textContent = 'Kaydet'; };

        IK.postJson(IK.u('damgakartikaydet'), veri).then(function (r) {
            if (r && !r.success && r.onay_gerekli) {
                return IK.onay('Müşteriler etkilenecek', r.message, 'Evet, kaydet').then(function (evet) {
                    if (!evet) return null;
                    return IK.postJson(IK.u('damgakartikaydet'), Object.assign({}, veri, { onay: 1 }));
                });
            }
            return r;
        }).then(function (r) {
            if (r === null) { bitir(); return; }
            if (!r || !r.success) { IK.toast((r && r.message) || 'Kaydedilemedi', true); bitir(); return; }
            var id = r.id || (MEVCUT && MEVCUT.id);
            return gorselIslem(id).then(function (g) {
                var mesaj = 'Kampanya kaydedildi';
                if (!g || !g.success) mesaj = 'Kampanya kaydedildi ama görsel yüklenemedi: ' + ((g && g.message) || 'bağlantı hatası');
                try { sessionStorage.setItem('ik_mesaj', mesaj); } catch (e) {}
                location.href = donusDetay ? IK.u('ikramhizmet/kampanya/' + id) : IK.u('ikramhizmet');
            }, function () {
                try { sessionStorage.setItem('ik_mesaj', 'Kampanya kaydedildi ama görsel yüklenemedi: bağlantı hatası'); } catch (e) {}
                location.href = IK.u('ikramhizmet');
            });
        }).catch(function () { IK.toast('Kaydedilemedi', true); bitir(); });
    });

    ciz();
})();
</script>
@endsection
