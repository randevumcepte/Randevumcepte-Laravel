@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Ikram Hizmet ("N kez alana M ikram"): Carkifelek'ten bagimsiz sayfa.
     Ic adlar (tablo/servis/url) "damga"; ekranda her yerde "Ikram Hizmet". --}}
<style>
.ik-wrap { padding: 24px; }
.ik-wrap * { box-sizing: border-box; }
.ik-hero { background: linear-gradient(135deg,#7c3aed 0%,#a855f7 55%,#10b981 100%); color:#fff; border-radius:16px; padding:24px 28px; margin-bottom:22px; box-shadow:0 12px 34px rgba(124,58,237,.22); }
.ik-hero h1 { margin:0 0 6px; font-size:22px; font-weight:800; letter-spacing:-.3px; color:#fff; }
.ik-hero p  { margin:0; opacity:.95; font-size:13px; max-width:820px; line-height:1.55; }
.ik-kart { background:#fff; border-radius:14px; padding:18px 20px; margin-bottom:22px; border:1px solid #e5e7eb; box-shadow:0 4px 14px rgba(0,0,0,.06); }
.ik-kart h3 { margin:0 0 6px; font-size:16px; font-weight:800; color:#111827; }
.ik-ack { font-size:13px; color:#6b7280; margin:0 0 14px; line-height:1.5; }
.ik-tablo { width:100%; border-collapse:collapse; margin:0 0 14px; font-size:13px; }
.ik-tablo th { text-align:left; font-size:11px; text-transform:uppercase; color:#6b7280; padding:6px 8px; border-bottom:1px solid #e5e7eb; }
.ik-tablo td { padding:8px; border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
.ik-tablo tr.ik-pasif td { opacity:.55; }
.ik-acik { background:#d1fae5; color:#065f46; padding:2px 10px; border-radius:12px; font-weight:700; font-size:11px; }
.ik-kapali { background:#f3f4f6; color:#6b7280; padding:2px 10px; border-radius:12px; font-weight:700; font-size:11px; }
.ik-hak { background:#ede9fe; color:#5b21b6; padding:2px 10px; border-radius:12px; font-weight:800; font-size:12px; }
.ik-btn { padding:6px 10px; border-radius:7px; border:none; font-size:12px; font-weight:700; cursor:pointer; }
.ik-dz { background:#eef2ff; color:#4338ca; } .ik-dz:hover { background:#c7d2fe; }
.ik-sl { background:#fef2f2; color:#dc2626; } .ik-sl:hover { background:#fee2e2; }
.ik-pas { background:#f3f4f6; color:#6b7280; }
.ik-satir { display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:14px; color:#374151; margin-bottom:10px; }
.ik-satir input[type=number] { width:80px; padding:8px 10px; border:2px solid #e5e7eb; border-radius:8px; font-size:14px; }
.ik-satir input:focus { outline:none; border-color:#7c3aed; }
.ik-select-kap { display:inline-block; min-width:260px; }
.ik-select-kap .select2-container .select2-selection--single { height:40px; border:2px solid #e5e7eb; border-radius:8px; }
.ik-select-kap .select2-container .select2-selection__rendered { line-height:36px; padding-left:10px; }
.ik-select-kap .select2-container .select2-selection__arrow { height:38px; }
.ik-select { min-width:200px; padding:8px 10px; border:2px solid #e5e7eb; border-radius:8px; font-size:14px; background:#fff; }
.ik-alt { display:flex; flex-wrap:wrap; align-items:center; gap:12px; }
.ik-alt small { color:#9ca3af; }
.ik-kaydet { padding:10px 20px; background:#7c3aed; color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-size:14px; }
.ik-kaydet:hover { background:#6d28d9; }
.ik-bos { text-align:center; color:#9ca3af; padding:24px 10px; font-size:13px; }
@media (max-width: 700px) {
    .ik-tablo .ik-gizle { display:none; }
    .ik-select, .ik-select-kap { min-width:0; width:100%; }
}
#toast { position:fixed; top:20px; right:20px; z-index:11000; padding:14px 20px; background:#10b981; color:#fff; border-radius:10px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none; }
#toast.show { display:block; }
#toast.err { background:#ef4444; }
</style>

<div class="ik-wrap">
    <div class="ik-hero">
        <h1>🎁 İkram Hizmet</h1>
        <p>Bir hizmeti belirlediğiniz sayıda ücretli alan müşteriniz o hizmetten ikram kazanır (ör. 4 Cilt Bakımı alana 1 ikram). Sayım "Geldi" işaretli randevulara göre yapılır; paket seansları sayılmaz. Müşteri kazandığı ikramları uygulamasında görür ve <b>ne zaman kullanacağına kendisi karar verir</b>; isterse saklar. Müşteri kullanmak istediğinde tahsilat ekranında "İkramı Kullan" ile seans %100 indirimle kapanır ve 0 TL tahsilat olarak kaydedilir.</p>
    </div>

    <div class="ik-kart" id="ik-kart">
        <h3>İkram Kuralları</h3>
        <p class="ik-ack">Her hizmet için bir kural tanımlayabilirsiniz. Kuralı ilk açtığınız andan sonraki ziyaretler sayılır; geçmiş randevulara ikram dağıtılmaz. Kuralı kapatıp yeniden açmak müşterilerin ilerlemesini ve kazandığı ikramları silmez (kapalıyken ikram kullandırılamaz). Geçerlilik süresi boş bırakılırsa ikram süresizdir; süre girilirse ikram, kazanıldığı günden itibaren o süre içinde kullanılmazsa düşer.</p>

        @if($ikramKurallari->count())
        <table class="ik-tablo">
            <thead><tr><th>Hizmet</th><th>Kampanya</th><th>Kullanım süresi</th><th>Durum</th><th class="ik-gizle">Kampanya tarihleri</th><th></th></tr></thead>
            <tbody>
            @foreach($ikramKurallari as $k)
                <tr class="{{ $k->aktif ? '' : 'ik-pasif' }}">
                    <td><b>{{ $k->hizmet_adi ?? ('Hizmet #'.$k->hizmet_id) }}</b></td>
                    <td>{{ \App\Services\DamgaKartiServisi::tur($k) === 'indirim' ? '🏷️' : '🎁' }} {{ \App\Services\DamgaKartiServisi::kuralYazi($k) }}</td>
                    <td>{{ \App\Services\DamgaKartiServisi::gecerlilikYazi($k) ?: 'Süresiz' }}</td>
                    <td>{!! $k->aktif ? '<span class="ik-acik">Açık</span>' : '<span class="ik-kapali">Kapalı</span>' !!}</td>
                    <td class="ik-gizle"><small>{{ \App\Services\DamgaKartiServisi::tarihAraligiYazi($k) ?: '—' }}</small></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button class="ik-btn ik-dz" onclick='ikDuzenle({!! json_encode(['id'=>(int)$k->id,'hizmet_id'=>(int)$k->hizmet_id,'gereken_adet'=>(int)$k->gereken_adet,'ucretsiz_adet'=>(int)$k->ucretsiz_adet,'aktif'=>(int)$k->aktif,'gecerlilik_deger'=>isset($k->gecerlilik_deger) ? (int)$k->gecerlilik_deger : 0,'gecerlilik_birim'=>$k->gecerlilik_birim ?? 'ay','tur'=>\App\Services\DamgaKartiServisi::tur($k),'indirim_orani'=>(int)($k->indirim_orani ?? 0),'baslangic_tarihi'=>$k->baslangic ? date('Y-m-d', strtotime($k->baslangic)) : '','bitis_tarihi'=>!empty($k->bitis_tarihi) ? date('Y-m-d', strtotime($k->bitis_tarihi)) : '']) !!})'>✏️ Düzenle</button>
                        <button class="ik-btn ik-sl" onclick="ikSil({{ $k->id }})">🗑️</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif

        <input type="hidden" id="ik-id" value="">
        <div class="ik-satir">
            <b>Kampanya türü:</b>
            <label style="display:flex;align-items:center;gap:5px;margin:0;font-weight:600;"><input type="radio" name="ik-tur" value="ikram" checked onchange="ikTurDegisti()"> 🎁 X + 1 (hediye hizmet)</label>
            <label style="display:flex;align-items:center;gap:5px;margin:0 0 0 10px;font-weight:600;"><input type="radio" name="ik-tur" value="indirim" onchange="ikTurDegisti()"> 🏷️ İndirim</label>
        </div>
        <div class="ik-satir">
            <span class="ik-select-kap"><select id="ik-hizmet" class="ik-select">
                <option value="">Hizmet seçin…</option>
                @foreach($ikramHizmetleri as $h)
                    <option value="{{ $h->id }}">{{ $h->hizmet_adi }}</option>
                @endforeach
            </select></span>
            hizmetini <input type="number" id="ik-gereken" min="1" max="100" step="1" value="4"> kez alana
            <input type="number" id="ik-ucretsiz" min="1" max="10" step="1" value="1"> <span id="ik-ucretsiz-yazi">ikram</span><span id="ik-oran-kap" style="display:none;"> seansta %<input type="number" id="ik-oran" min="1" max="100" step="1" value="20" style="width:70px;"> indirim</span>,
            kazanıldığı günden itibaren <input type="number" id="ik-gecerlilik" min="0" step="1" value="" placeholder="süresiz">
            <select id="ik-birim" class="ik-select" style="min-width:0;width:auto;"><option value="ay">ay</option><option value="gun">gün</option></select> geçerli
            <label style="display:flex;align-items:center;gap:6px;font-weight:700;margin:0 0 0 6px;"><input type="checkbox" id="ik-aktif" checked> Açık</label>
        </div>
        <div class="ik-satir">
            <b>Kampanya tarihleri:</b>
            <input type="date" id="ik-bas" class="ik-select" style="min-width:0;width:auto;" min="{{ date('Y-m-d') }}"> –
            <input type="date" id="ik-bit" class="ik-select" style="min-width:0;width:auto;" min="{{ date('Y-m-d') }}">
            <small style="color:#9ca3af;">Başlangıç boşsa kural açıldığı gün başlar; bitiş boşsa süresiz. Yalnız bu aralıktaki ziyaretler sayılır; kazanılan hak bitişten sonra da kullanılır.</small>
        </div>
        <div class="ik-alt">
            <button class="ik-kaydet" id="ik-kaydet" onclick="ikKaydet()">💾 <span id="ik-kaydet-yazi">Kuralı Ekle</span></button>
            <button class="ik-btn ik-pas" id="ik-vazgec" style="display:none" onclick="ikTemizle()">Vazgeç</button>
        </div>
    </div>

    <div class="ik-kart">
        <h3>İkram Kazanan Müşteriler</h3>
        <p class="ik-ack">Kuralı dolduran müşteriler ve bekleyen ikram hakları. Müşteri hakkını istediği zaman kullanır.</p>
        @if(count($ikramMusterileri))
        <table class="ik-tablo">
            <thead><tr><th>Müşteri</th><th>Hizmet</th><th class="ik-gizle">Ücretli alım</th><th class="ik-gizle">Kazanılan</th><th class="ik-gizle">Kullanılan</th><th>Bekleyen</th></tr></thead>
            <tbody>
            @foreach($ikramMusterileri as $m)
                <tr>
                    <td><b>{{ $m['ad'] }}</b>@if($m['telefon'])<br><small style="color:#9ca3af;">{{ $m['telefon'] }}</small>@endif</td>
                    <td>{{ $m['hizmet_adi'] }}<br><small style="color:#9ca3af;">{{ $m['kural'] }}</small></td>
                    <td class="ik-gizle">{{ $m['ucretli'] }}</td>
                    <td class="ik-gizle">{{ $m['kazanilan'] }}</td>
                    <td class="ik-gizle">{{ $m['kullanilan'] }}</td>
                    <td>@if($m['hak'] > 0)<span class="ik-hak">🎁 {{ $m['hak'] }}</span>@if(!empty($m['son_kullanma']))<br><small style="color:#9ca3af;">son: {{ date('d.m.Y', strtotime($m['son_kullanma'])) }}</small>@endif @else<span style="color:#9ca3af;">—</span>@endif
                        @if(!empty($m['suresi_dolan']))<br><small style="color:#dc2626;">{{ $m['suresi_dolan'] }} süresi doldu</small>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <div class="ik-bos">Henüz ikram kazanan müşteri yok.</div>
        @endif
    </div>
</div>

<div id="toast"></div>

<script>
const IK_KAYDET_URL = '{{ url("/isletmeyonetim/damgakartikaydet") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const IK_SIL_URL    = '{{ url("/isletmeyonetim/damgakartisil") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const IK_CSRF = '{{ csrf_token() }}';

function showToast(msg, err) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show' + (err ? ' err' : '');
    clearTimeout(t._z);
    t._z = setTimeout(function () { t.className = ''; }, 2600);
}

/** Modern onay penceresi (SweetAlert2 v7); yoksa tarayici confirm'ine duser. */
function onayla(baslik, metin) {
    if (!window.swal) return Promise.resolve(confirm(baslik + ' ' + metin));
    return swal({
        title: baslik, text: metin, type: 'warning',
        showCancelButton: true, reverseButtons: true, focusCancel: true,
        confirmButtonText: 'Evet, sil', confirmButtonColor: '#dc2626',
        cancelButtonText: 'Vazgeç', cancelButtonColor: '#9ca3af',
    }).then(function (r) { return !!(r && r.value); }, function () { return false; });
}

/* Hizmet secimi aranabilir (select2, panelde yuklu). Yoksa duz select kalir. */
function ikHizmetSec(deger) {
    const el = document.getElementById('ik-hizmet');
    el.value = deger;
    if (window.jQuery && jQuery.fn.select2 && jQuery(el).hasClass('select2-hidden-accessible')) jQuery(el).trigger('change');
}
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery || !jQuery.fn.select2) return;
    jQuery('#ik-hizmet').select2({
        width: '100%',
        placeholder: 'Hizmet ara veya seçin…',
        language: { noResults: function () { return 'Hizmet bulunamadı'; } },
    });
});

async function ikIstek(url, veri) {
    const resp = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': IK_CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(veri),
    });
    return resp.json();
}

function ikTurSec(tur) {
    document.querySelectorAll('input[name="ik-tur"]').forEach(function (r) { r.checked = r.value === tur; });
    ikTurDegisti();
}
function ikTurDegisti() {
    const ind = (document.querySelector('input[name="ik-tur"]:checked') || {}).value === 'indirim';
    document.getElementById('ik-oran-kap').style.display = ind ? '' : 'none';
    document.getElementById('ik-ucretsiz-yazi').style.display = ind ? 'none' : '';
}

function ikDuzenle(k) {
    document.getElementById('ik-id').value = k.id;
    ikTurSec(k.tur === 'indirim' ? 'indirim' : 'ikram');
    document.getElementById('ik-oran').value = k.indirim_orani > 0 ? k.indirim_orani : 20;
    document.getElementById('ik-bas').value = k.baslangic_tarihi || '';
    document.getElementById('ik-bas').min = k.baslangic_tarihi && k.baslangic_tarihi < '{{ date('Y-m-d') }}' ? k.baslangic_tarihi : '{{ date('Y-m-d') }}';
    document.getElementById('ik-bit').value = k.bitis_tarihi || '';
    ikHizmetSec(k.hizmet_id);
    document.getElementById('ik-gereken').value = k.gereken_adet;
    document.getElementById('ik-ucretsiz').value = k.ucretsiz_adet;
    document.getElementById('ik-aktif').checked = !!k.aktif;
    document.getElementById('ik-gecerlilik').value = k.gecerlilik_deger > 0 ? k.gecerlilik_deger : '';
    document.getElementById('ik-birim').value = k.gecerlilik_birim === 'gun' ? 'gun' : 'ay';
    document.getElementById('ik-kaydet-yazi').textContent = 'Değişikliği Kaydet';
    document.getElementById('ik-vazgec').style.display = '';
    document.getElementById('ik-kart').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function ikTemizle() {
    document.getElementById('ik-id').value = '';
    ikTurSec('ikram');
    document.getElementById('ik-oran').value = 20;
    document.getElementById('ik-bas').value = '';
    document.getElementById('ik-bas').min = '{{ date('Y-m-d') }}';
    document.getElementById('ik-bit').value = '';
    ikHizmetSec('');
    document.getElementById('ik-gereken').value = 4;
    document.getElementById('ik-ucretsiz').value = 1;
    document.getElementById('ik-aktif').checked = true;
    document.getElementById('ik-gecerlilik').value = '';
    document.getElementById('ik-birim').value = 'ay';
    document.getElementById('ik-kaydet-yazi').textContent = 'Kuralı Ekle';
    document.getElementById('ik-vazgec').style.display = 'none';
}

async function ikKaydet() {
    const veri = {
        id:            parseInt(document.getElementById('ik-id').value) || 0,
        hizmet_id:     parseInt(document.getElementById('ik-hizmet').value) || 0,
        gereken_adet:  parseInt(document.getElementById('ik-gereken').value) || 0,
        ucretsiz_adet: parseInt(document.getElementById('ik-ucretsiz').value) || 0,
        aktif:         document.getElementById('ik-aktif').checked ? 1 : 0,
        gecerlilik_deger: parseInt(document.getElementById('ik-gecerlilik').value) || 0,
        gecerlilik_birim: document.getElementById('ik-birim').value,
        tur:              (document.querySelector('input[name="ik-tur"]:checked') || {}).value || 'ikram',
        indirim_orani:    parseInt(document.getElementById('ik-oran').value) || 0,
        baslangic_tarihi: document.getElementById('ik-bas').value || '',
        bitis_tarihi:     document.getElementById('ik-bit').value || '',
    };
    if (veri.tur === 'indirim' && (veri.indirim_orani < 1 || veri.indirim_orani > 100)) { showToast('İndirim oranı %1-100 olmalı', true); return; }
    if (veri.bitis_tarihi && veri.baslangic_tarihi && veri.bitis_tarihi < veri.baslangic_tarihi) { showToast('Bitiş başlangıçtan önce olamaz', true); return; }
    if (!veri.hizmet_id) { showToast('Hizmet seçin', true); return; }
    if (veri.gereken_adet < 1) { showToast('Alım adedi en az 1 olmalı', true); return; }
    if (veri.ucretsiz_adet < 1) { showToast('İkram adedi en az 1 olmalı', true); return; }
    const btn = document.getElementById('ik-kaydet');
    btn.disabled = true;
    try {
        let data = await ikIstek(IK_KAYDET_URL, veri);
        if (!data.success && data.onay_gerekli) {
            // Sayilmis ziyareti olan kuralda adet/hizmet degisiyor: etkisini gosterip onay al
            const evet = !window.swal ? confirm(data.message + '\n\nYine de kaydedilsin mi?') : await swal({
                title: 'Müşteriler etkilenecek', text: data.message, type: 'warning',
                showCancelButton: true, reverseButtons: true, focusCancel: true,
                confirmButtonText: 'Evet, kaydet', confirmButtonColor: '#5C008E',
                cancelButtonText: 'Vazgeç', cancelButtonColor: '#9ca3af',
            }).then(function (r) { return !!(r && r.value); }, function () { return false; });
            if (!evet) { btn.disabled = false; return; }
            data = await ikIstek(IK_KAYDET_URL, Object.assign({}, veri, { onay: 1 }));
        }
        if (!data.success) { showToast(data.message || 'Hata', true); btn.disabled = false; return; }
        showToast('✓ İkram kuralı kaydedildi');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showToast('Bağlantı hatası', true); btn.disabled = false; }
}

async function ikSil(id) {
    if (!(await onayla('Kural silinsin mi?', 'Bu ikram kuralı silinecek.'))) return;
    try {
        const data = await ikIstek(IK_SIL_URL, { id: id });
        if (!data.success) { showToast(data.message || 'Hata', true); return; }
        showToast('✓ Kural silindi');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showToast('Bağlantı hatası', true); }
}
</script>
@endsection
