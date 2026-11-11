@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')

<style>
.po-wrap { padding: 24px; }
.po-wrap * { box-sizing: border-box; }

.po-hero {
    background: linear-gradient(135deg,#10b981 0%,#3b82f6 50%,#8b5cf6 100%);
    color:#fff; border-radius:16px; padding:24px 28px; margin-bottom:22px;
    box-shadow: 0 12px 34px rgba(59,130,246,.22);
}
.po-hero h1 { margin:0 0 5px; font-size:22px; font-weight:800; letter-spacing:-.3px; }
.po-hero p  { margin:0; opacity:.95; font-size:13px; max-width: 760px; }

.po-actions { display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.btn-ekle {
    padding:11px 22px; background:#10b981; color:#fff; border:none; border-radius:10px;
    font-weight:700; cursor:pointer; font-size:14px; transition:.2s;
}
.btn-ekle:hover { background:#059669; transform:translateY(-2px); box-shadow:0 8px 20px rgba(16,185,129,.35); }

.po-list { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:14px; }
.po-card {
    background:#fff; border-radius:14px; padding:18px;
    box-shadow: 0 4px 14px rgba(0,0,0,.06); border:1px solid #e5e7eb;
    position:relative; transition:.25s;
}
.po-card.pasif { opacity:.5; filter:grayscale(.6); }
.po-card:hover { transform:translateY(-3px); box-shadow:0 10px 28px rgba(59,130,246,.15); }

.po-card .rozet-puan {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 14px; background: linear-gradient(135deg,#fde047,#f59e0b);
    color:#78350f; border-radius:20px; font-weight:800; font-size:13px;
    box-shadow:0 2px 8px rgba(245,158,11,.3);
    margin-bottom:10px;
}
.po-card h4  { margin:0 0 4px; font-size:17px; font-weight:700; color:#111827; }
.po-card .ack { font-size:13px; color:#6b7280; line-height:1.5; min-height:36px; }
.po-card .tip-ln { display:flex; gap:6px; align-items:center; margin-top:12px; padding-top:12px; border-top:1px solid #f3f4f6; font-size:12px; }
.po-card .tip-pill { padding:3px 10px; border-radius:16px; font-size:11px; font-weight:700; }
.tp-hizmet { background:#dbeafe; color:#1e40af; }
.tp-urun   { background:#fce7f3; color:#9f1239; }
.tp-hediye { background:#fef3c7; color:#92400e; }

.po-card .btn-row { display:flex; gap:6px; margin-top:10px; }
.po-card .btn-row button {
    flex:1; padding:7px 10px; border-radius:7px; border:none;
    font-size:12px; font-weight:700; cursor:pointer; transition:.15s;
}
.btn-dz { background:#eef2ff; color:#4338ca; }
.btn-dz:hover { background:#c7d2fe; }
.btn-sl { background:#fef2f2; color:#dc2626; }
.btn-sl:hover { background:#fee2e2; }
.btn-akt { background:#d1fae5; color:#065f46; }
.btn-pas { background:#f3f4f6; color:#6b7280; }

.po-empty { background:#fff; border-radius:14px; padding:60px 20px; text-align:center; border:2px dashed #e5e7eb; }
.po-empty .ic { font-size:58px; margin-bottom:10px; }
.po-empty p { color:#6b7280; margin:0; font-size:14px; }

/* Modal */
.pm-ov { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(6px); z-index:10000; align-items:center; justify-content:center; }
.pm-ov.show { display:flex; }
.pm-box { background:#fff; border-radius:18px; padding:28px 26px; max-width:520px; width:92%; box-shadow:0 20px 60px rgba(0,0,0,.25); }
.pm-box h3 { margin:0 0 16px; font-size:18px; font-weight:800; color:#111827; }
.pm-row { margin-bottom:14px; }
.pm-row label { display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:5px; text-transform:uppercase; letter-spacing:.3px; }
.pm-row input, .pm-row textarea, .pm-row select {
    width:100%; padding:11px 14px; border:2px solid #e5e7eb; border-radius:9px;
    font-size:14px; background:#fff; transition:.2s;
}
.pm-row input:focus, .pm-row textarea:focus, .pm-row select:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 4px rgba(59,130,246,.15); }
.pm-row textarea { resize:vertical; min-height:60px; }
.pm-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.pm-act { display:flex; gap:10px; margin-top:8px; }
.pm-btn { flex:1; padding:12px; border:none; border-radius:9px; font-size:14px; font-weight:700; cursor:pointer; transition:.2s; }
.pm-kaydet { background:#10b981; color:#fff; }
.pm-kaydet:hover { background:#059669; }
.pm-vaz { background:#f3f4f6; color:#374151; }
.pm-vaz:hover { background:#e5e7eb; }

.pk-kart { background:#fff; border-radius:14px; padding:18px 20px; margin-bottom:22px; border:1px solid #e5e7eb; box-shadow:0 4px 14px rgba(0,0,0,.06); }
.pk-bas { display:flex; align-items:center; gap:12px; margin-bottom:6px; }
.pk-bas h3 { margin:0; font-size:16px; font-weight:800; color:#111827; flex:1; }
.pk-bas label { display:flex; align-items:center; gap:6px; font-size:13px; font-weight:700; color:#374151; cursor:pointer; margin:0; }
.pk-ack { font-size:13px; color:#6b7280; margin:0 0 14px; }
.pk-satir { display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:14px; color:#374151; margin-bottom:10px; }
.pk-satir input { width:110px; padding:8px 10px; border:2px solid #e5e7eb; border-radius:8px; font-size:14px; }
.pk-satir input:focus { outline:none; border-color:#3b82f6; }
.pk-ornek { font-size:12px; color:#065f46; background:#ecfdf5; border-radius:8px; padding:8px 12px; margin:4px 0 12px; }
.pk-alt { display:flex; flex-wrap:wrap; align-items:center; gap:12px; }
.pk-alt small { color:#9ca3af; }
.pk-kapali .pk-satir, .pk-kapali .pk-ornek { opacity:.5; }
.dk-tablo { width:100%; border-collapse:collapse; margin:0 0 14px; font-size:13px; }
.dk-tablo th { text-align:left; font-size:11px; text-transform:uppercase; color:#6b7280; padding:6px 8px; border-bottom:1px solid #e5e7eb; }
.dk-tablo td { padding:8px; border-bottom:1px solid #f3f4f6; color:#374151; }
.dk-tablo tr.dk-pasif td { opacity:.55; }
.dk-acik { background:#d1fae5; color:#065f46; padding:2px 10px; border-radius:12px; font-weight:700; font-size:11px; }
.dk-kapali { background:#f3f4f6; color:#6b7280; padding:2px 10px; border-radius:12px; font-weight:700; font-size:11px; }
.dk-btn { padding:6px 10px; border-radius:7px; border:none; font-size:12px; font-weight:700; cursor:pointer; }
.dk-select { min-width:200px; padding:8px 10px; border:2px solid #e5e7eb; border-radius:8px; font-size:14px; background:#fff; }
.pk-satir input#dk-gereken, .pk-satir input#dk-ucretsiz { width:80px; }
@media (max-width: 600px) { .dk-tablo th:nth-child(4), .dk-tablo td:nth-child(4) { display:none; } .dk-select { min-width:0; width:100%; } }

#toast { position:fixed; top:20px; right:20px; z-index:11000; padding:14px 20px; background:#10b981; color:#fff; border-radius:10px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none; }
#toast.show { display:block; }
#toast.err { background:#ef4444; }
</style>

<div class="po-wrap">
    @include('partials.carkifelek_tabs')
    <div class="po-hero">
        <h1>🎖️ Puan Ödülleri Merdiveni</h1>
        <p>Müşterileriniz puan biriktirir, belirli eşiklere ulaştığında buradaki ödülleri talep eder. Talep edilen ödül kupon olarak oluşur — "Çark Kazananlar" sayfasındaki doğrulama kutusuyla onaylayabilirsiniz.</p>
    </div>

    <div class="pk-kart {{ $sadakatAyar['aktif'] ? '' : 'pk-kapali' }}" id="pk-kart">
        <div class="pk-bas">
            <h3>⭐ Puan Kazanma Kuralı</h3>
            <label><input type="checkbox" id="pk-aktif" {{ $sadakatAyar['aktif'] ? 'checked' : '' }} onchange="pkGuncelle()"> Açık</label>
        </div>
        <p class="pk-ack">Müşteriler ödeme yaptıkça ve randevusuna geldikçe otomatik puan kazanır; biriken puanla aşağıdaki ödülleri alır. Puanlar birkaç dakika içinde hesaba geçer, silinen tahsilatın puanı geri alınır.</p>
        <div class="pk-satir">
            Her <input type="number" id="pk-tutar" min="1" step="1" value="{{ (int) $sadakatAyar['harcama_tutari'] }}" oninput="pkGuncelle()"> TL ödemeye
            <input type="number" id="pk-puan" min="0" step="1" value="{{ $sadakatAyar['harcama_puani'] }}" oninput="pkGuncelle()"> puan
        </div>
        <div class="pk-satir">
            Gelinen her randevuya <input type="number" id="pk-ziyaret" min="0" step="1" value="{{ $sadakatAyar['ziyaret_puani'] }}" oninput="pkGuncelle()"> puan
        </div>
        <div class="pk-ornek" id="pk-ornek"></div>
        <div class="pk-alt">
            <button class="btn-ekle" id="pk-kaydet" onclick="pkKaydet()">💾 Kuralı Kaydet</button>
            @if($sadakatAyar['aktif'] && $sadakatAyar['baslangic'])
                <small>{{ $sadakatAyar['baslangic'] }} tarihinden sonraki ödeme ve randevular puan kazanır.</small>
            @else
                <small>Açtığınız andan sonraki ödeme ve randevular puan kazanır; geçmiş kayıtlara puan dağıtılmaz.</small>
            @endif
        </div>
    </div>

    <div class="pk-kart" id="dk-kart">
        <div class="pk-bas">
            <h3>🎟️ Damga Kartı (Al-Kazan)</h3>
        </div>
        <p class="pk-ack">Bir hizmeti belirlediğiniz sayıda alan müşteriye ücretsiz seans hakkı verin (ör. 4 Cilt Bakımı alana 1 seans ücretsiz). Sayım "Geldi" işaretli randevulara göre yapılır; paket seansları sayılmaz. Hak doğduğunda tahsilat ekranında uyarı çıkar, personel "Ücretsiz Uygula" ile seansı %100 indirimle kapatır ve 0 TL tahsilat olarak kaydeder.</p>

        @if($damgaKurallari->count())
        <table class="dk-tablo">
            <thead><tr><th>Hizmet</th><th>Kural</th><th>Durum</th><th>Başlangıç</th><th></th></tr></thead>
            <tbody>
            @foreach($damgaKurallari as $k)
                <tr class="{{ $k->aktif ? '' : 'dk-pasif' }}">
                    <td><b>{{ $k->hizmet_adi ?? ('Hizmet #'.$k->hizmet_id) }}</b></td>
                    <td>{{ $k->gereken_adet }} alana {{ $k->ucretsiz_adet }} seans ücretsiz</td>
                    <td>{!! $k->aktif ? '<span class="dk-acik">Açık</span>' : '<span class="dk-kapali">Kapalı</span>' !!}</td>
                    <td><small>{{ $k->aktif && $k->baslangic ? date('d.m.Y', strtotime($k->baslangic)) : '—' }}</small></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button class="dk-btn btn-dz" onclick='dkDuzenle({!! json_encode(['id'=>(int)$k->id,'hizmet_id'=>(int)$k->hizmet_id,'gereken_adet'=>(int)$k->gereken_adet,'ucretsiz_adet'=>(int)$k->ucretsiz_adet,'aktif'=>(int)$k->aktif]) !!})'>✏️ Düzenle</button>
                        <button class="dk-btn btn-sl" onclick="dkSil({{ $k->id }})">🗑️</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif

        <input type="hidden" id="dk-id" value="">
        <div class="pk-satir">
            <select id="dk-hizmet" class="dk-select">
                <option value="">Hizmet seçin…</option>
                @foreach($damgaHizmetleri as $h)
                    <option value="{{ $h->id }}">{{ $h->hizmet_adi }}</option>
                @endforeach
            </select>
            hizmetini <input type="number" id="dk-gereken" min="1" max="100" step="1" value="4"> kez alana
            <input type="number" id="dk-ucretsiz" min="1" max="10" step="1" value="1"> seans ücretsiz
            <label style="display:flex;align-items:center;gap:6px;font-weight:700;margin:0 0 0 6px;"><input type="checkbox" id="dk-aktif" checked> Açık</label>
        </div>
        <div class="pk-alt">
            <button class="btn-ekle" id="dk-kaydet" onclick="dkKaydet()">💾 <span id="dk-kaydet-yazi">Kuralı Ekle</span></button>
            <button class="dk-btn btn-pas" id="dk-vazgec" style="display:none" onclick="dkTemizle()">Vazgeç</button>
            <small>Kuralı açtığınız andan sonraki ziyaretler sayılır; geçmiş randevulara hak dağıtılmaz.</small>
        </div>
    </div>

    <div class="po-actions">
        <button class="btn-ekle" onclick="modalAc()">+ Yeni Ödül Ekle</button>
        <span style="color:#6b7280; font-size:13px;">Toplam: <b>{{ $odulSeviyeleri->count() }}</b> ödül seviyesi</span>
    </div>

    @if($odulSeviyeleri->isEmpty())
        <div class="po-empty">
            <div class="ic">🎖️</div>
            <p>Henüz puan ödülü tanımlanmamış. Yukarıdan "+ Yeni Ödül Ekle" ile başlayın.<br><br>
               <button onclick="ornekleriEkle()" style="margin-top:14px; padding:11px 22px; background:#3b82f6; color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-size:14px;">📋 Örnekleri Yükle (6 adet)</button>
            </p>
        </div>
    @else
        <div class="po-list" id="po-liste">
            @foreach($odulSeviyeleri as $o)
                @php
                    $tipCls = ['hizmet_indirimi'=>'tp-hizmet','urun_indirimi'=>'tp-urun','hediye'=>'tp-hediye'][$o->tip] ?? 'tp-hediye';
                    $tipLbl = ['hizmet_indirimi'=>'Hizmet İnd.','urun_indirimi'=>'Ürün İnd.','hediye'=>'Hediye'][$o->tip] ?? 'Ödül';
                    $degerMetin = $o->deger && in_array($o->tip, ['hizmet_indirimi','urun_indirimi']) ? '%'.((int)$o->deger) : '';
                @endphp
                <div class="po-card {{ $o->aktif ? '' : 'pasif' }}" data-id="{{ $o->id }}">
                    <span class="rozet-puan">⭐ {{ $o->puan_esigi }} Puan</span>
                    <h4>{{ $o->baslik }}</h4>
                    <div class="ack">{{ $o->aciklama ?: '—' }}</div>
                    <div class="tip-ln">
                        <span class="tip-pill {{ $tipCls }}">{{ $tipLbl }}</span>
                        @if($degerMetin)<span style="color:#111827;font-weight:700;">{{ $degerMetin }}</span>@endif
                        <span style="margin-left:auto;color:#9ca3af;font-size:11px;">Sıra: {{ $o->sira }}</span>
                    </div>
                    <div class="btn-row">
                        <button class="btn-dz" onclick='duzenle(@json($o))'>✏️ Düzenle</button>
                        <button class="btn-sl" onclick="sil({{ $o->id }})">🗑 Sil</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal -->
<div class="pm-ov" id="pm-modal">
    <div class="pm-box">
        <h3 id="pm-title">Yeni Puan Ödülü</h3>
        <form id="pm-form" onsubmit="event.preventDefault(); kaydet();">
            <input type="hidden" id="pm-id" value="0">
            <div class="pm-row">
                <label>Başlık <span style="color:#ef4444;">*</span></label>
                <input type="text" id="pm-baslik" maxlength="150" placeholder="Örn: %20 Hizmet İndirimi" required>
            </div>
            <div class="pm-row">
                <label>Açıklama</label>
                <textarea id="pm-aciklama" maxlength="300" placeholder="Müşterinin göreceği kısa açıklama"></textarea>
            </div>
            <div class="pm-2">
                <div class="pm-row">
                    <label>Gerekli Puan <span style="color:#ef4444;">*</span></label>
                    <input type="number" id="pm-puan" min="1" value="100" required>
                </div>
                <div class="pm-row">
                    <label>Sıra</label>
                    <input type="number" id="pm-sira" value="0">
                </div>
            </div>
            <div class="pm-2">
                <div class="pm-row">
                    <label>Tip</label>
                    <select id="pm-tip" onchange="tipDegisti()">
                        <option value="hizmet_indirimi">Hizmet İndirimi</option>
                        <option value="urun_indirimi">Ürün İndirimi</option>
                        <option value="hediye">Hediye</option>
                    </select>
                </div>
                <div class="pm-row" id="pm-deger-wrap">
                    <label>Değer (%)</label>
                    <input type="number" id="pm-deger" min="0" step="0.1" placeholder="örn: 20">
                </div>
            </div>
            <div class="pm-row">
                <label>
                    <input type="checkbox" id="pm-aktif" checked style="width:auto;margin-right:6px;">
                    Aktif (müşteriler görebilsin)
                </label>
            </div>
            @include('isletmeadmin.partials.sube_secici')
            <div class="pm-act">
                <button type="button" class="pm-btn pm-vaz" onclick="modalKapat()">Vazgeç</button>
                <button type="submit" class="pm-btn pm-kaydet" id="pm-btn-kaydet">💾 Kaydet</button>
            </div>
        </form>
    </div>
</div>

<div id="toast"></div>

<script>
const KAYDET_URL = '{{ url("/isletmeyonetim/puanodulkaydet") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const SIL_URL    = '{{ url("/isletmeyonetim/puanodulsil") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const ORNEK_URL  = '{{ url("/isletmeyonetim/puanorneklerekle-admin") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const CSRF = '{{ csrf_token() }}';
const KAZANIM_URL = '{{ url("/isletmeyonetim/puankazanimkaydet") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';

function pkGuncelle() {
    const aktif   = document.getElementById('pk-aktif').checked;
    const tutar   = parseInt(document.getElementById('pk-tutar').value) || 0;
    const puan    = parseInt(document.getElementById('pk-puan').value) || 0;
    const ziyaret = parseInt(document.getElementById('pk-ziyaret').value) || 0;
    document.getElementById('pk-kart').classList.toggle('pk-kapali', !aktif);
    const ornek = document.getElementById('pk-ornek');
    if (!puan && !ziyaret) { ornek.textContent = 'Puan vermek için en az bir değer girin.'; return; }
    const odeme = 1000;
    const kazanc = (tutar > 0 ? Math.floor(odeme / tutar) * puan : 0) + ziyaret;
    ornek.textContent = 'Örnek: Randevusuna gelip ' + odeme.toLocaleString('tr-TR') + ' TL ödeyen müşteri ' + kazanc + ' puan kazanır.';
}

async function pkKaydet() {
    const btn = document.getElementById('pk-kaydet');
    const veri = {
        aktif:          document.getElementById('pk-aktif').checked ? 1 : 0,
        harcama_tutari: parseInt(document.getElementById('pk-tutar').value) || 0,
        harcama_puani:  parseInt(document.getElementById('pk-puan').value) || 0,
        ziyaret_puani:  parseInt(document.getElementById('pk-ziyaret').value) || 0,
    };
    if (veri.aktif && veri.harcama_tutari < 1) { showToast('TL tutarı en az 1 olmalı', true); return; }
    if (veri.aktif && !veri.harcama_puani && !veri.ziyaret_puani) { showToast('Puan değeri girin', true); return; }
    btn.disabled = true;
    try {
        const resp = await fetch(KAZANIM_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(veri),
        });
        const data = await resp.json();
        if (!data.success) { showToast(data.message || 'Hata', true); btn.disabled = false; return; }
        showToast('✓ Kural kaydedildi');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showToast('Bağlantı hatası', true); btn.disabled = false; }
}
pkGuncelle();

const DAMGA_KAYDET_URL = '{{ url("/isletmeyonetim/damgakartikaydet") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';
const DAMGA_SIL_URL    = '{{ url("/isletmeyonetim/damgakartisil") }}{{ isset($_GET["sube"]) ? "?sube=".$isletme->id : "" }}';

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

async function dkIstek(url, veri) {
    const resp = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(veri),
    });
    return resp.json();
}

function dkDuzenle(k) {
    document.getElementById('dk-id').value = k.id;
    document.getElementById('dk-hizmet').value = k.hizmet_id;
    document.getElementById('dk-gereken').value = k.gereken_adet;
    document.getElementById('dk-ucretsiz').value = k.ucretsiz_adet;
    document.getElementById('dk-aktif').checked = !!k.aktif;
    document.getElementById('dk-kaydet-yazi').textContent = 'Değişikliği Kaydet';
    document.getElementById('dk-vazgec').style.display = '';
    document.getElementById('dk-kart').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function dkTemizle() {
    document.getElementById('dk-id').value = '';
    document.getElementById('dk-hizmet').value = '';
    document.getElementById('dk-gereken').value = 4;
    document.getElementById('dk-ucretsiz').value = 1;
    document.getElementById('dk-aktif').checked = true;
    document.getElementById('dk-kaydet-yazi').textContent = 'Kuralı Ekle';
    document.getElementById('dk-vazgec').style.display = 'none';
}

async function dkKaydet() {
    const veri = {
        id:            parseInt(document.getElementById('dk-id').value) || 0,
        hizmet_id:     parseInt(document.getElementById('dk-hizmet').value) || 0,
        gereken_adet:  parseInt(document.getElementById('dk-gereken').value) || 0,
        ucretsiz_adet: parseInt(document.getElementById('dk-ucretsiz').value) || 0,
        aktif:         document.getElementById('dk-aktif').checked ? 1 : 0,
    };
    if (!veri.hizmet_id) { showToast('Hizmet seçin', true); return; }
    if (veri.gereken_adet < 1) { showToast('Alım adedi en az 1 olmalı', true); return; }
    if (veri.ucretsiz_adet < 1) { showToast('Ücretsiz seans en az 1 olmalı', true); return; }
    const btn = document.getElementById('dk-kaydet');
    btn.disabled = true;
    try {
        const data = await dkIstek(DAMGA_KAYDET_URL, veri);
        if (!data.success) { showToast(data.message || 'Hata', true); btn.disabled = false; return; }
        showToast('✓ Damga kartı kuralı kaydedildi');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showToast('Bağlantı hatası', true); btn.disabled = false; }
}

async function dkSil(id) {
    if (!(await onayla('Kural silinsin mi?', 'Bu damga kartı kuralı silinecek.'))) return;
    try {
        const data = await dkIstek(DAMGA_SIL_URL, { id: id });
        if (!data.success) { showToast(data.message || 'Hata', true); return; }
        showToast('✓ Kural silindi');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showToast('Bağlantı hatası', true); }
}

async function ornekleriEkle() {
    try {
        const resp = await fetch(ORNEK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({}),
        });
        const data = await resp.json();
        if (!data.success) { showToast(data.message || 'Hata', true); return; }
        showToast('✓ ' + data.sayac + ' örnek eklendi');
        setTimeout(() => location.reload(), 700);
    } catch (e) { showToast('Bağlantı hatası', true); }
}

function showToast(msg, err) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = err ? 'err show' : 'show';
    setTimeout(() => t.classList.remove('show'), 2500);
}

function tipDegisti() {
    const tip = document.getElementById('pm-tip').value;
    const wrap = document.getElementById('pm-deger-wrap');
    const inp  = document.getElementById('pm-deger');
    if (tip === 'hediye') {
        wrap.style.display = 'none'; inp.value = '';
    } else {
        wrap.style.display = 'block';
        wrap.querySelector('label').textContent = 'Değer (%)';
    }
}

function modalAc() {
    document.getElementById('pm-title').textContent = 'Yeni Puan Ödülü';
    document.getElementById('pm-id').value      = 0;
    document.getElementById('pm-baslik').value   = '';
    document.getElementById('pm-aciklama').value = '';
    document.getElementById('pm-puan').value     = 100;
    document.getElementById('pm-sira').value     = 0;
    document.getElementById('pm-tip').value      = 'hizmet_indirimi';
    document.getElementById('pm-deger').value    = '';
    document.getElementById('pm-aktif').checked  = true;
    tipDegisti();
    var _ss = document.querySelector('.sube-secici'); if (_ss) _ss.style.display = '';
    document.getElementById('pm-modal').classList.add('show');
}

function duzenle(o) {
    document.getElementById('pm-title').textContent = 'Puan Ödülü Düzenle';
    document.getElementById('pm-id').value       = o.id;
    document.getElementById('pm-baslik').value   = o.baslik || '';
    document.getElementById('pm-aciklama').value = o.aciklama || '';
    document.getElementById('pm-puan').value     = o.puan_esigi || 100;
    document.getElementById('pm-sira').value     = o.sira || 0;
    document.getElementById('pm-tip').value      = o.tip || 'hizmet_indirimi';
    document.getElementById('pm-deger').value    = o.deger != null ? o.deger : '';
    document.getElementById('pm-aktif').checked  = !!parseInt(o.aktif);
    tipDegisti();
    var _ss = document.querySelector('.sube-secici'); if (_ss) _ss.style.display = 'none';
    document.getElementById('pm-modal').classList.add('show');
}

function modalKapat() {
    document.getElementById('pm-modal').classList.remove('show');
}

async function kaydet() {
    const btn = document.getElementById('pm-btn-kaydet');
    btn.disabled = true; btn.textContent = '⏳ Kaydediliyor...';

    const veri = {
        id:         parseInt(document.getElementById('pm-id').value) || 0,
        baslik:     document.getElementById('pm-baslik').value.trim(),
        aciklama:   document.getElementById('pm-aciklama').value.trim(),
        puan_esigi: parseInt(document.getElementById('pm-puan').value) || 0,
        sira:       parseInt(document.getElementById('pm-sira').value) || 0,
        tip:        document.getElementById('pm-tip').value,
        deger:      document.getElementById('pm-deger').value !== '' ? parseFloat(document.getElementById('pm-deger').value) : null,
        aktif:      document.getElementById('pm-aktif').checked ? 1 : 0,
    };
    // Çoklu şube: yeni ödül seçili şubelere eklenir (düzenlemede grup korunur).
    if (!veri.id && window.ssSeciliIdler) veri.salon_ids = window.ssSeciliIdler();

    try {
        const resp = await fetch(KAYDET_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(veri),
        });
        const data = await resp.json();
        if (!data.success) {
            showToast(data.message || 'Hata', true);
            btn.disabled = false; btn.textContent = '💾 Kaydet';
            return;
        }
        showToast('✓ Kaydedildi');
        setTimeout(() => location.reload(), 600);
    } catch (e) {
        showToast('Bağlantı hatası', true);
        btn.disabled = false; btn.textContent = '💾 Kaydet';
    }
}

async function sil(id) {
    if (!(await onayla('Ödül silinsin mi?', 'Bu puan ödülü merdivenden kaldırılacak.'))) return;
    try {
        const resp = await fetch(SIL_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ id: id }),
        });
        const data = await resp.json();
        if (!data.success) { showToast(data.message || 'Hata', true); return; }
        showToast('✓ Silindi');
        setTimeout(() => location.reload(), 500);
    } catch (e) { showToast('Bağlantı hatası', true); }
}

document.getElementById('pm-modal').addEventListener('click', e => {
    if (e.target.id === 'pm-modal') modalKapat();
});
</script>
@endsection
