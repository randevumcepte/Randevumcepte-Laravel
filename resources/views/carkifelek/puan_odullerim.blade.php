@extends('layout.layout')

@section('content')
<style>
.pm-page { max-width: 860px; margin: 40px auto; padding: 0 16px 80px; }

.pm-hero {
    background: linear-gradient(135deg,#10b981 0%,#3b82f6 50%,#8b5cf6 100%);
    color:#fff; border-radius: 22px; padding: 28px 26px 30px;
    box-shadow: 0 14px 36px rgba(59,130,246,.22);
    position:relative; overflow:hidden;
}
.pm-hero::before { content:''; position:absolute; top:-50px; right:-50px; width:180px; height:180px; border-radius:50%; background:rgba(255,255,255,.08); }
.pm-hero::after  { content:''; position:absolute; bottom:-70px; right:80px; width:140px; height:140px; border-radius:50%; background:rgba(255,255,255,.06); }
.pm-hero h1 { margin:0 0 5px; font-size:22px; font-weight:800; letter-spacing:-.3px; position:relative; z-index:1; }
.pm-hero .salon { font-size:14px; opacity:.92; position:relative; z-index:1; }
.pm-puan-big {
    display:flex; align-items:center; gap:18px; margin-top:18px; position:relative; z-index:1;
}
.pm-puan-big .rakam {
    font-size:64px; font-weight:800; letter-spacing:-2px; line-height:1;
    text-shadow: 0 3px 18px rgba(0,0,0,.2);
}
.pm-puan-big .alt { font-size:13px; opacity:.9; margin-top:5px; }
.pm-puan-big .alt b { font-size:15px; letter-spacing:.3px; display:block; margin-top:2px; }

.pm-sec {
    display:flex; gap:10px; overflow-x:auto; padding:12px 0;
    margin-bottom:12px;
    scrollbar-width: none;
}
.pm-sec::-webkit-scrollbar { display:none; }
.pm-sec a {
    padding: 8px 16px; background:#fff; border-radius:50px;
    font-size:13px; font-weight:600; color:#6b7280;
    white-space:nowrap; text-decoration:none;
    border: 1.5px solid #e5e7eb; transition:.15s;
}
.pm-sec a.active { background:#6c5ce7; color:#fff; border-color:#6c5ce7; }
.pm-sec a:hover  { color:#6c5ce7; text-decoration:none; }
.pm-sec a.active:hover { color:#fff; }

.pm-ladder { display:flex; flex-direction:column; gap:14px; margin-top:14px; }
.pm-step {
    background:#fff; border-radius:16px; padding:18px 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,.06);
    border: 2px solid transparent;
    position:relative; transition:.25s;
}
.pm-step.talep-edilebilir { border-color: #10b981; background: linear-gradient(135deg,#fff 0%,#ecfdf5 100%); }
.pm-step.talep-edilebilir::before {
    content:'✓ Hazır'; position:absolute; top:14px; right:16px;
    background:#10b981; color:#fff; padding:3px 12px; border-radius:20px;
    font-size:11px; font-weight:800; letter-spacing:.3px;
}
.pm-step.kilitli { opacity:.75; }

.pm-step-top { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; }
.pm-step h3 { margin:0 0 6px; font-size:18px; font-weight:800; color:#111827; }
.pm-step .ack { font-size:13px; color:#6b7280; line-height:1.5; margin:0; }

.pm-esik {
    min-width: 110px;
    text-align:center; padding:10px 14px;
    background: linear-gradient(135deg,#fde047,#f59e0b);
    color:#78350f; border-radius:14px; font-weight:800;
    box-shadow: 0 4px 12px rgba(245,158,11,.25);
}
.pm-esik .rk { font-size:20px; line-height:1.1; }
.pm-esik .lb { font-size:11px; font-weight:600; margin-top:2px; letter-spacing:.5px; }

.pm-pg { margin-top:14px; }
.pm-pg-bar { height:8px; background:#f3f4f6; border-radius:4px; overflow:hidden; }
.pm-pg-fill { height:100%; background: linear-gradient(90deg,#10b981,#3b82f6); border-radius:4px; transition:width .6s; }
.pm-pg-txt { font-size:12px; color:#6b7280; margin-top:6px; display:flex; justify-content:space-between; }

.pm-act { margin-top:14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.btn-talep {
    padding:11px 26px; background: linear-gradient(135deg,#10b981,#059669); color:#fff;
    border:none; border-radius:10px; font-weight:800; font-size:14px;
    cursor:pointer; transition:.2s;
    box-shadow: 0 6px 18px rgba(16,185,129,.35);
}
.btn-talep:hover { transform:translateY(-2px); box-shadow: 0 10px 24px rgba(16,185,129,.45); }
.btn-talep:disabled { opacity:.6; cursor:not-allowed; transform:none; }
.pm-eksik { font-size:13px; color:#dc2626; font-weight:700; }

.pm-tipbadge { display:inline-block; padding:3px 10px; border-radius:16px; font-size:11px; font-weight:700; margin-right:6px; }
.tp-hizmet { background:#dbeafe; color:#1e40af; }
.tp-urun   { background:#fce7f3; color:#9f1239; }
.tp-hediye { background:#fef3c7; color:#92400e; }

.pm-empty { background:#fff; border-radius:14px; padding:60px 20px; text-align:center; }
.pm-empty .ic { font-size:58px; margin-bottom:10px; }
.pm-empty p { color:#6b7280; margin:0; }

/* Talep sonrası modal */
.ts-ov { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center; }
.ts-ov.show { display:flex; }
.ts-box { background:#fff; border-radius:24px; padding:36px 28px; max-width:440px; width:92%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.25); animation: popIn .45s cubic-bezier(.34,1.56,.64,1); }
@keyframes popIn { from{ transform:scale(.6); opacity:0 } to { transform:scale(1); opacity:1 } }
.ts-box .emj { font-size:56px; display:block; margin-bottom:12px; }
.ts-box h2 { font-size:20px; font-weight:800; color:#111827; margin:0 0 6px; }
.ts-box p  { font-size:13px; color:#6b7280; margin:0 0 12px; }
.ts-box .kupon {
    display:inline-block; margin:6px 0 10px;
    padding:10px 20px; background:#fef3c7; color:#92400e;
    border-radius:10px; font-family:monospace;
    font-size:20px; font-weight:800; letter-spacing:3px;
    border: 2px dashed #f59e0b;
}
.ts-box .kupon-lbl { display:block; font-size:12px; color:#6b7280; margin-bottom:4px; font-family: inherit; letter-spacing: normal; font-weight:500; }
.ts-kap { margin-top:14px; padding:12px 32px; background:#6c5ce7; color:#fff; border:none; border-radius:50px; font-weight:700; cursor:pointer; }

#toast { position:fixed; top:20px; right:20px; z-index:10000; padding:14px 20px; background:#10b981; color:#fff; border-radius:10px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none; }
#toast.show { display:block; }
#toast.err { background:#ef4444; }
</style>

<div class="pm-page">
    <div class="pm-hero">
        <h1>⭐ Puan Ödüllerim</h1>
        <div class="salon">{{ $salon->salon_adi }}</div>
        <div class="pm-puan-big">
            <div>
                <div class="rakam" id="puan-rakam">{{ (int) $puanBakiyesi }}</div>
                <div class="alt">Toplam<b>Puan</b></div>
            </div>
        </div>
    </div>

    @if($puanKayitlari->count() > 1)
        <div class="pm-sec">
            @foreach($puanKayitlari as $pk)
                @php $s = $tumSalonlar->get($pk->salon_id); @endphp
                @if($s)
                    <a href="{{ url('/puanodullerim/'.$pk->salon_id) }}" class="{{ $salonId == $pk->salon_id ? 'active' : '' }}">
                        {{ $s->salon_adi }} ({{ (int) $pk->puan }})
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @if($odulSeviyeleri->isEmpty())
        <div class="pm-empty">
            <div class="ic">🎖️</div>
            <p>Bu salonda henüz tanımlı puan ödülü yok.<br>Salon yakında ödülleri tanıtacak.</p>
        </div>
    @else
        <div class="pm-ladder">
            @foreach($odulSeviyeleri as $o)
                @php
                    $talepEdilebilir = $puanBakiyesi >= $o->puan_esigi;
                    $yuzde = min(100, round(($puanBakiyesi / max(1, $o->puan_esigi)) * 100));
                    $eksik = max(0, $o->puan_esigi - $puanBakiyesi);
                    $tipCls = ['hizmet_indirimi'=>'tp-hizmet','urun_indirimi'=>'tp-urun','hediye'=>'tp-hediye'][$o->tip] ?? 'tp-hediye';
                    $tipLbl = ['hizmet_indirimi'=>'Hizmet İndirimi','urun_indirimi'=>'Ürün İndirimi','hediye'=>'Hediye'][$o->tip] ?? 'Ödül';
                    $degerLbl = $o->deger && in_array($o->tip, ['hizmet_indirimi','urun_indirimi']) ? '%'.((int)$o->deger) : '';
                @endphp
                <div class="pm-step {{ $talepEdilebilir ? 'talep-edilebilir' : 'kilitli' }}" data-id="{{ $o->id }}" data-esik="{{ $o->puan_esigi }}">
                    <div class="pm-step-top">
                        <div style="flex:1;">
                            <h3>{{ $o->baslik }}</h3>
                            <p class="ack">{{ $o->aciklama ?: '—' }}</p>
                            <div style="margin-top:8px;">
                                <span class="pm-tipbadge {{ $tipCls }}">{{ $tipLbl }}</span>
                                @if($degerLbl)<b style="color:#111827; font-size:13px;">{{ $degerLbl }}</b>@endif
                            </div>
                        </div>
                        <div class="pm-esik">
                            <div class="rk">{{ $o->puan_esigi }}</div>
                            <div class="lb">PUAN</div>
                        </div>
                    </div>
                    <div class="pm-pg">
                        <div class="pm-pg-bar"><div class="pm-pg-fill" style="width:{{ $yuzde }}%;"></div></div>
                        <div class="pm-pg-txt">
                            <span>{{ (int) $puanBakiyesi }} / {{ $o->puan_esigi }}</span>
                            <span>{{ $yuzde }}%</span>
                        </div>
                    </div>
                    <div class="pm-act">
                        @if($talepEdilebilir)
                            <button class="btn-talep" onclick="talepEt({{ $o->id }}, {{ $o->puan_esigi }}, this)">🎁 Ödülü Talep Et</button>
                        @else
                            <span class="pm-eksik">🔒 {{ (int) $eksik }} puan daha gerekiyor</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Talep sonucu modal -->
<div class="ts-ov" id="ts-modal">
    <div class="ts-box">
        <span class="emj">🎉</span>
        <h2>Ödülünüz Hazır!</h2>
        <p id="ts-baslik">—</p>
        <div class="kupon">
            <span class="kupon-lbl">Kupon Kodunuz</span>
            <span id="ts-kod">—</span>
        </div>
        <p style="font-size:12px;color:#9ca3af;">Bu kodu 60 gün içinde salonda ibraz edin.</p>
        <button class="ts-kap" onclick="kapatModal()">Tamam</button>
    </div>
</div>

<div id="toast"></div>

<script>
const TALEP_URL = '{{ route("cark.puanodul.talep") }}';
const SALON_ID  = {{ $salonId }};
const CSRF = '{{ csrf_token() }}';

function showToast(msg, err) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = err ? 'err show' : 'show';
    setTimeout(() => t.classList.remove('show'), 3000);
}

async function talepEt(odulId, puanEsigi, btn) {
    if (!confirm('Bu ödülü talep etmek istediğinize emin misiniz?\n' + puanEsigi + ' puan düşecek ve kupon kodunuz oluşacak.')) return;
    btn.disabled = true; btn.textContent = '⏳ İşleniyor...';
    try {
        const resp = await fetch(TALEP_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ salon_id: SALON_ID, odul_id: odulId }),
        });
        const data = await resp.json();
        if (!data.success) {
            showToast(data.message || 'Hata', true);
            btn.disabled = false; btn.textContent = '🎁 Ödülü Talep Et';
            return;
        }
        document.getElementById('ts-baslik').textContent = data.baslik;
        document.getElementById('ts-kod').textContent    = data.kod;
        document.getElementById('ts-modal').classList.add('show');
        document.getElementById('puan-rakam').textContent = data.kalanPuan;
        setTimeout(() => location.reload(), 3500);
    } catch (e) {
        showToast('Bağlantı hatası', true);
        btn.disabled = false; btn.textContent = '🎁 Ödülü Talep Et';
    }
}
function kapatModal() { document.getElementById('ts-modal').classList.remove('show'); }

document.getElementById('ts-modal').addEventListener('click', e => {
    if (e.target.id === 'ts-modal') kapatModal();
});
</script>
@endsection
