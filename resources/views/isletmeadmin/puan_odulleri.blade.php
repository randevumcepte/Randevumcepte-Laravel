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

#toast { position:fixed; top:20px; right:20px; z-index:11000; padding:14px 20px; background:#10b981; color:#fff; border-radius:10px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none; }
#toast.show { display:block; }
#toast.err { background:#ef4444; }
</style>

<div class="po-wrap">
    <div class="po-hero">
        <h1>🎖️ Puan Ödülleri Merdiveni</h1>
        <p>Müşterileriniz puan biriktirir, belirli eşiklere ulaştığında buradaki ödülleri talep eder. Talep edilen ödül kupon olarak oluşur — "Çark Kazananlar" sayfasındaki doğrulama kutusuyla onaylayabilirsiniz.</p>
    </div>

    <div class="po-actions">
        <button class="btn-ekle" onclick="modalAc()">+ Yeni Ödül Ekle</button>
        <span style="color:#6b7280; font-size:13px;">Toplam: <b>{{ $odulSeviyeleri->count() }}</b> ödül seviyesi</span>
    </div>

    @if($odulSeviyeleri->isEmpty())
        <div class="po-empty">
            <div class="ic">🎖️</div>
            <p>Henüz puan ödülü tanımlanmamış. Yukarıdan "+ Yeni Ödül Ekle" ile başlayın.</p>
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
const CSRF = '{{ csrf_token() }}';

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
    if (!confirm('Bu puan ödülünü silmek istediğinize emin misiniz?')) return;
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
