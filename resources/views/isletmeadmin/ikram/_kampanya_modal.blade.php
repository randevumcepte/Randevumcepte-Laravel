{{-- Kampanya Ayarlari penceresi (yeni / duzenle) — ayri sayfa yok; ana sayfa ve Kampanya Detayi
     ayni pencereyi kullanir. Alanlar ve kayit akisi mobil kampanya_ayarlari.dart ile ayni.
     Kullanim: IKForm.ac(kural | null, function () { ...kayittan sonra listeyi yenile... })
     Gereken: $ikramHizmetleri = [['id'=>..,'hizmet_adi'=>..], ...] --}}
<style>
.ikf-modal { display:none; position:fixed; inset:0; z-index:100000; background:rgba(46,16,101,.5); overflow-y:auto; padding:24px 12px; }
.ikf-modal.acik { display:block; }
.ikf-kutu { position:relative; max-width:1000px; margin:0 auto; border-radius:28px; padding:18px;
    background:linear-gradient(180deg,#E9DAF8 0%,#F5EDFC 55%,#EFE3FA 100%); box-shadow:0 24px 60px rgba(46,16,101,.35); }
.ikf-ust { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
.ikf-ust h2 { flex:1; margin:0; font-size:20px; font-weight:800; color:#2E1065; }
.ikf-hero { position:relative; width:100%; aspect-ratio:941/362; border-radius:22px; container-type:inline-size;
    background:url('{{ secure_asset('public/img/ikram/kampanya_hero_tam.png') }}') center/100% 100% no-repeat; }
.ikf-hero .ikf-hero-yazi { position:absolute; left:10.1%; top:63%; color:#fff; font-weight:800; font-size:2.1cqw;
    line-height:1.3; white-space:pre-line; text-shadow:0 1px 6px rgba(46,16,101,.35); }
.ikf-izgara { display:grid; grid-template-columns:1fr; gap:14px; }
.ikf-izgara .ik-cam { margin:0; }
.ikf-satirlar { background:rgba(255,255,255,.7); border:1px solid #EDE4FA; border-radius:18px; }
.ikf-satir { display:flex; align-items:center; gap:12px; padding:12px 14px; border-top:1px solid #EDE4FA; flex-wrap:wrap; }
.ikf-satir:first-child { border-top:0; }
.ikf-satir .ikf-ki { width:38px; height:38px; border-radius:50%; background:#F3ECFE; color:#7C3AED; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ikf-satir .ikf-st { flex:1; min-width:170px; } .ikf-satir .ikf-st b { display:block; font-size:14px; color:#2E1065; }
.ikf-satir .ikf-st small { font-size:12px; color:#6B5A8E; }
.ikf-deger { min-width:220px; }
.ikf-deger .select2-container .select2-selection--single { height:40px; border:1px solid #E2D4F8; border-radius:20px; background:#F1E9FD; }
.ikf-deger .select2-container .select2-selection__rendered { line-height:38px; padding-left:14px; color:#2E1065; font-weight:700; }
.ikf-deger .select2-container .select2-selection__arrow { height:38px; right:8px; }
.ikf-deger select, .ikf-girdi { height:40px; border:1px solid #E2D4F8 !important; border-radius:20px !important; background:#F1E9FD !important;
    color:#2E1065; font-weight:700; padding:0 14px; font-size:14px; }
.ikf-sayac { display:flex; align-items:center; gap:10px; }
.ikf-sayac button { width:36px; height:36px; border-radius:50%; border:0; background:#EDE3FD; color:#5B21B6; font-size:16px; cursor:pointer; }
.ikf-sayac button:disabled { background:#F5F0FC; color:#C4B5E0; cursor:default; }
.ikf-sayac b { min-width:32px; text-align:center; font-size:18px; color:#2E1065; }
.ikf-gorsel { display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
.ikf-gorsel .ikf-on { position:relative; width:92px; height:92px; border-radius:16px; overflow:hidden; flex-shrink:0; }
.ikf-gorsel .ikf-on img { width:100%; height:100%; object-fit:cover; }
.ikf-gbtn { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:20px; font-weight:800; font-size:13px; cursor:pointer; border:0; }
.ikf-gbtn.dolu { background:#7C3AED; color:#fff; } .ikf-gbtn.bos { background:#fff; color:#5B21B6; border:1px solid #DCCBF6; }
.ikf-tarihler { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.ikf-tarihler input { width:132px; }
.ikf-anahtar { position:relative; width:52px; height:30px; flex-shrink:0; margin:0; }
.ikf-anahtar input { opacity:0; width:0; height:0; }
.ikf-anahtar span { position:absolute; inset:0; border-radius:20px; background:#D9CCEE; transition:.2s; cursor:pointer; }
.ikf-anahtar span:before { content:''; position:absolute; width:24px; height:24px; left:3px; top:3px; border-radius:50%; background:#fff; transition:.2s; }
.ikf-anahtar input:checked + span { background:#7C3AED; } .ikf-anahtar input:checked + span:before { transform:translateX(22px); }
.ikf-kaydet { max-width:340px; height:56px; border-radius:30px; margin:16px auto 4px; font-size:17px; }
/* Tarih secici ve select2 acilir listesi pencerenin ustunde */
.datepicker, .select2-container--open { z-index:200001 !important; }
@media (min-width:992px) {
    .ikf-modal { padding:40px 24px; }
    .ikf-kutu { padding:24px; }
    .ikf-izgara { grid-template-columns:minmax(0,1fr) minmax(0,1.15fr); gap:18px; align-items:start; }
    .ikf-sol, .ikf-sag { display:flex; flex-direction:column; gap:18px; }
}
@media (max-width:600px) { .ikf-modal { padding:0; } .ikf-kutu { border-radius:0; min-height:100%; padding:12px; } .ikf-hero .ikf-hero-yazi { font-size:3cqw; } }
</style>

<div class="ikf-modal" id="ikf-modal" role="dialog" aria-modal="true">
    <div class="ikf-kutu">
        <div class="ikf-ust">
            <a class="ik-yuv" id="ikf-kapat" title="Kapat"><i class="fa fa-times"></i></a>
            <h2 id="ikf-baslik">Kampanya Ayarları</h2>
            <a class="ik-yuv" id="ikf-bilgi" title="Nasıl çalışır?"><i class="fa fa-info"></i></a>
        </div>
        <div class="ikf-izgara">
            <div class="ikf-sol">
                <div class="ikf-hero"><div class="ikf-hero-yazi" id="ikf-hero-yazi"></div></div>
                <div class="ik-cam">
                    <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-picture-o"></i></div>
                        <div><h3>Kampanya Görseli</h3><p>Kampanya kartında görünecek fotoğraf.</p></div></div>
                    <div class="ikf-gorsel">
                        <div class="ikf-on"><img id="ikf-onizleme" src="" alt=""><span class="ik-rozet" id="ikf-on-rozet"></span></div>
                        <div style="flex:1;min-width:200px">
                            <div id="ikf-gorsel-yazi" style="font-size:13px;color:#6B5A8E;margin-bottom:10px"></div>
                            <button type="button" class="ikf-gbtn dolu" id="ikf-foto-sec"><i class="fa fa-image"></i> <span id="ikf-foto-sec-yazi">Fotoğraf Seç</span></button>
                            <button type="button" class="ikf-gbtn bos" id="ikf-foto-kaldir" style="display:none"><i class="fa fa-times"></i> Kaldır</button>
                            <input type="file" id="ikf-dosya" accept="image/*" style="display:none">
                        </div>
                    </div>
                </div>
                <div class="ik-cam" style="display:flex;align-items:center;gap:14px">
                    <label class="ikf-anahtar"><input type="checkbox" id="ikf-aktif" checked><span></span></label>
                    <div style="flex:1"><b style="font-size:15px;color:#2E1065">Kampanyayı Aktif Et</b>
                        <div id="ikf-aktif-yazi" style="font-size:12.5px;color:#6B5A8E"></div></div>
                    <i class="fa fa-magic" style="color:#7C3AED;font-size:20px"></i>
                </div>
            </div>

            <div class="ikf-sag">
                <div class="ik-cam">
                    <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-sliders"></i></div>
                        <div><h3>Kampanya Detayları</h3><p>Kampanyanın koşullarını belirleyin.</p></div></div>
                    <div class="ikf-satirlar">
                        <div class="ikf-satir">
                            <div class="ikf-ki"><i class="fa fa-leaf"></i></div>
                            <div class="ikf-st"><b>Hizmet</b><small>Kampanyanın geçerli olduğu hizmet.</small></div>
                            <div class="ikf-deger"><select id="ikf-hizmet" style="width:100%">
                                <option value="">Hizmet seçin</option>
                                @foreach($ikramHizmetleri as $h)
                                    <option value="{{ $h['id'] }}">{{ $h['hizmet_adi'] }}</option>
                                @endforeach
                            </select></div>
                        </div>
                        <div class="ikf-satir">
                            <div class="ikf-ki"><i class="fa fa-bar-chart"></i></div>
                            <div class="ikf-st"><b>Hizmet Adedi</b><small>Müşterinin alması gereken hizmet adedini girin.</small></div>
                            <div class="ikf-sayac"><button type="button" id="ikf-eksi">−</button><b id="ikf-adet">5</b><button type="button" id="ikf-arti">+</button></div>
                        </div>
                        <div class="ikf-satir">
                            <div class="ikf-ki"><i class="fa fa-gift"></i></div>
                            <div class="ikf-st"><b>Hediye Hizmet</b><small>Müşteriye verilecek hediye hizmeti seçin.</small></div>
                            <div class="ikf-deger" style="min-width:140px"><select id="ikf-hediye" style="width:100%">
                                @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }} Hizmet</option>@endfor
                            </select></div>
                        </div>
                    </div>
                </div>

                <div class="ik-cam">
                    <div class="ik-baslik"><div class="ik-ikon"><i class="fa fa-calendar"></i></div>
                        <div><h3>Geçerlilik Süresi</h3><p>Kampanyanın ne kadar süreyle geçerli olacağını belirleyin.</p></div></div>
                    <div class="ikf-satirlar">
                        <div class="ikf-satir">
                            <div class="ikf-ki"><i class="fa fa-calendar-check-o"></i></div>
                            <div class="ikf-st"><b>Kampanya tarihleri</b><small>Yalnız bu aralıkta gelinen ziyaretler sayılır. Başlangıç geçmiş bir tarih olabilir; o tarihten beri gelinen ziyaretler de sayılır. Kazanılan hak kampanya bitince de kullanılabilir.</small></div>
                            <div class="ikf-tarihler">
                                <input type="text" class="form-control geriye-yonelik ikf-girdi" id="ikf-bas" placeholder="Bugün · gg.aa.yyyy" title="Takvimden seçin ya da gg.aa.yyyy yazın; geçmiş tarih olabilir" autocomplete="off">
                                –
                                <input type="text" class="form-control geriye-yonelik ikf-girdi" id="ikf-bit" placeholder="Süresiz · gg.aa.yyyy" autocomplete="off">
                                <a href="javascript:void(0)" id="ikf-suresiz" style="font-size:12px;color:#7C3AED;font-weight:700">Bitiş olmasın</a>
                            </div>
                        </div>
                        <div class="ikf-satir">
                            <div class="ikf-ki"><i class="fa fa-hourglass-half"></i></div>
                            <div class="ikf-st"><b>Kazanılan hakkın kullanım süresi</b><small>Süre seçerseniz hak, kazanıldığı günden itibaren o süre içinde kullanılmazsa düşer.</small></div>
                            <div class="ikf-deger" style="min-width:140px"><select id="ikf-sure" style="width:100%"></select></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="ik-btn-ana ikf-kaydet" id="ikf-kaydet"><span class="ik-arti"><i class="fa fa-check"></i></span> <span id="ikf-kaydet-yazi">Kaydet</span></button>
    </div>
</div>

<script>
var IKForm = (function () {
    var $ = function (id) { return document.getElementById(id); };
    var SURELER = [['0|ay', 'Süresiz'], ['15|gun', '15 gün'], ['30|gun', '30 gün'], ['2|ay', '2 ay'], ['3|ay', '3 ay'], ['6|ay', '6 ay'], ['12|ay', '12 ay']];
    var mevcut = null, yeniDosya = null, gorselKaldir = false, sonra = null;

    function select2Var() { return window.jQuery && jQuery.fn.select2; }
    function hizmetDeger(v) {
        var s = $('ikf-hizmet');
        if (v && !Array.prototype.some.call(s.options, function (o) { return o.value === String(v); })) {
            var o = document.createElement('option'); // pasif hizmetteki kural da secili gorunsun
            o.value = v; o.textContent = (mevcut && mevcut.hizmet_adi) || ('Hizmet #' + v);
            s.insertBefore(o, s.options[1] || null);
        }
        s.value = v ? String(v) : '';
        if (select2Var() && jQuery(s).hasClass('select2-hidden-accessible')) jQuery(s).trigger('change.select2');
    }
    function adet() { return parseInt($('ikf-adet').textContent, 10) || 5; }
    function hediye() { return parseInt($('ikf-hediye').value, 10) || 1; }
    function hizmetAdi() { var s = $('ikf-hizmet'); return s.value ? s.options[s.selectedIndex].text : ''; }

    function ciz() {
        $('ikf-eksi').disabled = adet() <= 1;
        $('ikf-arti').disabled = adet() >= 100;
        $('ikf-hero-yazi').textContent = 'Belirlediğin adetlerde\n' + hediye() + ' hizmet hediye et.';
        $('ikf-on-rozet').textContent = adet() + '+' + hediye();
        $('ikf-aktif-yazi').textContent = $('ikf-aktif').checked ? 'Kampanya hemen aktif olsun.'
            : 'Kampanya kapalı; ziyaretler sayılmaya devam eder, hak kullandırılmaz.';
        var kendi = !!yeniDosya || (mevcut && mevcut.gorsel && !gorselKaldir);
        var hazir = IK.hazirGorsel(hizmetAdi());
        var img = $('ikf-onizleme');
        img.onerror = function () { img.onerror = null; img.src = hazir; };
        if (yeniDosya) {
            img.src = URL.createObjectURL(yeniDosya);
            $('ikf-gorsel-yazi').textContent = 'Seçtiğiniz fotoğraf kaydedince yüklenecek.';
        } else if (mevcut && mevcut.gorsel && !gorselKaldir) {
            img.src = mevcut.gorsel;
            $('ikf-gorsel-yazi').textContent = 'Kendi fotoğrafınız kullanılıyor.';
        } else {
            img.src = hazir;
            $('ikf-gorsel-yazi').textContent = 'Otomatik: hizmete uygun hazır görsel. Kendi fotoğrafınızı da yükleyebilirsiniz.';
        }
        $('ikf-foto-sec-yazi').textContent = kendi ? 'Değiştir' : 'Fotoğraf Seç';
        $('ikf-foto-kaldir').style.display = kendi ? '' : 'none';
    }

    function sureDoldur(deger, birim) {
        var liste = SURELER.slice();
        var cur = deger > 0 ? deger + '|' + birim : '0|ay';
        if (!liste.some(function (x) { return x[0] === cur; })) liste.splice(1, 0, [cur, deger + (birim === 'gun' ? ' gün' : ' ay')]);
        $('ikf-sure').innerHTML = liste.map(function (x) {
            return '<option value="' + x[0] + '"' + (x[0] === cur ? ' selected' : '') + '>' + x[1] + '</option>';
        }).join('');
    }

    function ac(kural, kayittanSonra) {
        mevcut = kural || null; yeniDosya = null; gorselKaldir = false; sonra = kayittanSonra || null;
        var k = mevcut || {};
        $('ikf-baslik').textContent = mevcut ? 'Kampanyayı Düzenle' : 'Yeni Kampanya';
        hizmetDeger(k.hizmet_id || '');
        $('ikf-adet').textContent = k.gereken_adet || 5;
        $('ikf-hediye').value = String(k.ucretsiz_adet || 1);
        $('ikf-bas').value = k.baslangic ? String(k.baslangic).slice(0, 10) : '';
        $('ikf-bit').value = k.bitis_tarihi || '';
        sureDoldur(parseInt(k.gecerlilik_deger || 0, 10), k.gecerlilik_birim === 'gun' ? 'gun' : 'ay');
        $('ikf-aktif').checked = mevcut ? Number(k.aktif) === 1 : true;
        $('ikf-kaydet').disabled = false; $('ikf-kaydet-yazi').textContent = 'Kaydet';
        ciz();
        $('ikf-modal').classList.add('acik');
        $('ikf-modal').scrollTop = 0;
        document.body.style.overflow = 'hidden';
    }
    function kapat() {
        $('ikf-modal').classList.remove('acik');
        document.body.style.overflow = '';
    }

    $('ikf-kapat').addEventListener('click', kapat);
    $('ikf-modal').addEventListener('mousedown', function (e) { if (e.target === this) kapat(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && $('ikf-modal').classList.contains('acik')) kapat(); });
    $('ikf-bilgi').addEventListener('click', function () {
        IK.bilgi('Kampanya nasıl çalışır?',
            '• X + 1: Müşteri seçtiğiniz hizmeti belirlediğiniz adet kadar ücretli alınca hediye hizmet kazanır.<br>' +
            '• Yüzde indirim kampanyaları için Kampanyalar bölümünü kullanın.<br><br>' +
            '• Sayım "Geldi" işaretli ücretli randevulara göre yapılır; paket/seanslı satış ve telafi seansları sayılmaz.<br>' +
            '• Yalnız kampanya tarihleri içindeki ziyaretler sayılır. Başlangıcı geçmiş bir tarih seçerseniz o tarihten beri gelinen ziyaretler de sayılır.<br>' +
            '• Müşteri kazandığını uygulamasında görür, ne zaman kullanacağına kendisi karar verir. Kullanmak istediğinde tahsilat ekranından işlenir.<br>' +
            '• Kullanım süresi seçerseniz hak, kazanıldığı günden itibaren o süre içinde kullanılmazsa düşer.<br>' +
            '• Kampanyayı kapatıp açmak müşterilerin ilerlemesini silmez.');
    });
    $('ikf-eksi').addEventListener('click', function () { $('ikf-adet').textContent = Math.max(1, adet() - 1); ciz(); });
    $('ikf-arti').addEventListener('click', function () { $('ikf-adet').textContent = Math.min(100, adet() + 1); ciz(); });
    $('ikf-aktif').addEventListener('change', ciz);
    $('ikf-hizmet').addEventListener('change', ciz);
    $('ikf-hediye').addEventListener('change', ciz);
    $('ikf-foto-sec').addEventListener('click', function () { $('ikf-dosya').click(); });
    $('ikf-dosya').addEventListener('change', function () {
        if (this.files && this.files[0]) { yeniDosya = this.files[0]; gorselKaldir = false; ciz(); }
        this.value = '';
    });
    $('ikf-foto-kaldir').addEventListener('click', function () { yeniDosya = null; gorselKaldir = true; ciz(); });
    $('ikf-suresiz').addEventListener('click', function () { $('ikf-bit').value = ''; });
    document.addEventListener('DOMContentLoaded', function () {
        if (!select2Var()) return;
        jQuery('#ikf-hizmet').select2({ width: '100%', placeholder: 'Hizmet seçin', dropdownParent: jQuery('#ikf-modal'),
            language: { noResults: function () { return 'Hizmet bulunamadı'; } } }).on('change', ciz);
    });

    function gorselIslem(id) {
        var fd;
        if (yeniDosya) { fd = new FormData(); fd.append('id', id); fd.append('dosya', yeniDosya); return IK.postForm(IK.u('ikramhizmet/gorsel'), fd); }
        if (gorselKaldir && mevcut && mevcut.gorsel) { fd = new FormData(); fd.append('id', id); fd.append('sil', '1'); return IK.postForm(IK.u('ikramhizmet/gorsel'), fd); }
        return Promise.resolve({ success: true });
    }

    $('ikf-kaydet').addEventListener('click', function () {
        var hizmetId = parseInt($('ikf-hizmet').value, 10) || 0;
        if (!hizmetId) { IK.toast('Lütfen kampanyanın hizmetini seçin', true); return; }
        var sure = $('ikf-sure').value.split('|');
        // Elle yazilan tarih: gg.aa.yyyy, gg/aa/yyyy ya da yyyy-aa-gg (takvim yyyy-aa-gg yazar)
        var tarihGirdi = function (v) {
            v = String(v || '').trim();
            if (!v) return '';
            var m = v.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/);
            if (m) v = m[3] + '-' + ('0' + m[2]).slice(-2) + '-' + ('0' + m[1]).slice(-2);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(v) || isNaN(new Date(v + 'T00:00:00'))) return null;
            return v;
        };
        var bas = tarihGirdi($('ikf-bas').value), bit = tarihGirdi($('ikf-bit').value);
        if (bas === null || bit === null) { IK.toast('Tarihi gg.aa.yyyy biçiminde yazın (ör. 01.10.2026)', true); return; }
        if (bas && bit && bit < bas) { IK.toast('Bitiş tarihi başlangıçtan önce olamaz', true); return; }
        var veri = {
            hizmet_id: hizmetId, tur: 'ikram', gereken_adet: adet(), ucretsiz_adet: hediye(),
            aktif: $('ikf-aktif').checked ? 1 : 0, gecerlilik_deger: parseInt(sure[0], 10) || 0, gecerlilik_birim: sure[1] === 'gun' ? 'gun' : 'ay',
            baslangic_tarihi: bas, bitis_tarihi: bit,
        };
        if (mevcut) veri.id = mevcut.id;
        var btn = $('ikf-kaydet');
        btn.disabled = true; $('ikf-kaydet-yazi').textContent = 'Kaydediliyor…';
        var bitir = function () { btn.disabled = false; $('ikf-kaydet-yazi').textContent = 'Kaydet'; };

        IK.postJson(IK.u('damgakartikaydet'), veri).then(function (r) {
            if (r && !r.success && r.onay_gerekli) {
                return IK.onay('Müşteriler etkilenecek', r.message, 'Evet, kaydet').then(function (evet) {
                    return evet ? IK.postJson(IK.u('damgakartikaydet'), Object.assign({}, veri, { onay: 1 })) : null;
                });
            }
            return r;
        }).then(function (r) {
            if (r === null) { bitir(); return; }
            if (!r || !r.success) { IK.toast((r && r.message) || 'Kaydedilemedi', true); bitir(); return; }
            var id = r.id || (mevcut && mevcut.id);
            var son = function (g) {
                kapat();
                if (!g || !g.success) IK.toast('Kampanya kaydedildi ama görsel yüklenemedi: ' + ((g && g.message) || 'bağlantı hatası'), true);
                else IK.toast('Kampanya kaydedildi');
                if (sonra) sonra(id);
            };
            return gorselIslem(id).then(son, function () { son(null); });
        }).catch(function () { IK.toast('Kaydedilemedi', true); bitir(); });
    });

    return { ac: ac, kapat: kapat };
})();
</script>
