@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')

<style>
:root {
    --purple:   #6c5ce7;
    --purple-l: #a29bfe;
    --pink:     #fd79a8;
    --gold:     #fdcb6e;
    --green:    #00b894;
    --teal:     #00cec9;
    --red:      #d63031;
    --orange:   #e17055;
    --dark:     #1a1a2e;
    --mid:      #636e72;
    --bg:       #f0f2f8;
    --white:    #ffffff;
    --card-r:   20px;
    --sh:       0 4px 24px rgba(0,0,0,.08);
    --sh-lg:    0 16px 56px rgba(0,0,0,.14);
}

.ck-page * { box-sizing: border-box; margin: 0; padding: 0; }

.ck-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    background: var(--bg);
    padding: 24px;
    min-height: calc(100vh - 60px);
}

/* ── Header ─────────────────────────────────── */
.ck-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--white);
    border-radius: var(--card-r);
    padding: 20px 28px;
    box-shadow: var(--sh);
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.ck-header-left { display: flex; align-items: center; gap: 16px; }
.ck-header-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--purple), var(--purple-l));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    box-shadow: 0 6px 16px rgba(108,92,231,.35);
    flex-shrink: 0;
}
.ck-header h1 { font-size: 20px; font-weight: 800; color: var(--dark); margin-bottom: 3px; }
.ck-header p  { font-size: 13px; color: var(--mid); }

.status-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 18px; border-radius: 50px;
    font-size: 14px; font-weight: 600;
    transition: all .3s;
}
.status-pill.on  { background: rgba(0,184,148,.12); color: var(--green); }
.status-pill.off { background: rgba(214,48,49,.12);  color: var(--red); }
.status-dot {
    width: 9px; height: 9px; border-radius: 50%; background: currentColor;
    animation: blink 2s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }

/* ── Grid ────────────────────────────────────── */
.ck-grid {
    display: grid;
    grid-template-columns: 460px 1fr;
    gap: 24px;
    align-items: start;
}
@media(max-width:1100px){ .ck-grid{ grid-template-columns:1fr; } }

/* ── Wheel Panel ─────────────────────────────── */
.wheel-panel {
    background: linear-gradient(145deg, #160630 0%, #2b1a68 55%, #0e1b33 100%);
    border-radius: var(--card-r);
    padding: 28px 24px 24px;
    text-align: center;
    box-shadow: 0 24px 64px rgba(43,26,104,.45);
    position: relative;
    overflow: hidden;
}
.wheel-panel::before {
    content: '';
    position: absolute;
    top: -60%; left: -50%;
    width: 200%; height: 200%;
    background: radial-gradient(circle at 50% 28%, rgba(108,92,231,.18) 0%, transparent 60%);
    pointer-events: none;
}

.wp-label {
    font-size: 11px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: rgba(255,255,255,.45);
    margin-bottom: 20px;
}

/* Pointer */
.pointer-wrap { display: flex; justify-content: center; margin-bottom: -4px; position: relative; z-index: 10; }
.pointer {
    width: 0; height: 0;
    border-left: 13px solid transparent;
    border-right: 13px solid transparent;
    border-top: 30px solid #FFD700;
    filter: drop-shadow(0 4px 10px rgba(255,215,0,.55));
}

/* Outer glow ring */
.wheel-glow {
    display: inline-block;
    padding: 7px;
    background: conic-gradient(from 0deg, var(--purple), var(--purple-l), var(--pink), var(--gold), var(--green), var(--purple));
    border-radius: 50%;
    box-shadow: 0 0 32px rgba(108,92,231,.5), 0 0 72px rgba(108,92,231,.25);
    animation: spinRing 10s linear infinite;
}
@keyframes spinRing { to { filter: hue-rotate(360deg); } }

.wheel-wrap {
    position: relative;
    width: 360px; height: 360px;
    border-radius: 50%;
    overflow: hidden;
    background: #160630;
    display: inline-block;
    vertical-align: top;
}
@media(max-width:520px){
    .wheel-wrap { width: 280px; height: 280px; }
    .ck-grid { gap: 16px; }
}

#wheel {
    width: 100%; height: 100%;
    transform-origin: 50% 50%;
    transition: transform 5.5s cubic-bezier(0.17, 0.67, 0.12, 0.99);
}

.wheel-center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 66px; height: 66px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,.35), 0 0 0 4px rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    cursor: pointer;
    z-index: 5;
    transition: transform .2s;
}
.wheel-center:hover { transform: translate(-50%, -50%) scale(1.05); }
.wheel-center img { max-width: 54px; max-height: 54px; object-fit: contain; }

/* Spin button */
.spin-btn {
    margin-top: 22px;
    padding: 14px 44px;
    background: linear-gradient(135deg, var(--gold), var(--orange));
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 16px; font-weight: 700;
    cursor: pointer;
    box-shadow: 0 8px 28px rgba(225,112,85,.45);
    transition: all .25s;
    letter-spacing: .5px;
}
.spin-btn:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 14px 36px rgba(225,112,85,.55); }
.spin-btn:active:not(:disabled) { transform: translateY(0); }
.spin-btn:disabled { opacity: .6; cursor: not-allowed; }

/* Toggle row */
.wp-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    background: rgba(255,255,255,.06);
    border-radius: 12px;
    margin-top: 14px;
}
.wp-row-label { color: rgba(255,255,255,.7); font-size: 14px; font-weight: 500; }
.toggle { position: relative; width: 52px; height: 28px; cursor: pointer; }
.toggle input { opacity: 0; width: 0; height: 0; }
.tslider {
    position: absolute; inset: 0;
    background: rgba(255,255,255,.18);
    border-radius: 28px; transition: .3s;
}
.tslider::before {
    content: '';
    position: absolute;
    width: 22px; height: 22px;
    left: 3px; top: 3px;
    background: white; border-radius: 50%;
    transition: .3s;
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
}
.toggle input:checked + .tslider { background: var(--green); }
.toggle input:checked + .tslider::before { transform: translateX(24px); }

/* Logo upload row */
.logo-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px;
    background: rgba(255,255,255,.06);
    border-radius: 12px;
    margin-top: 10px;
}
.logo-row-label { color: rgba(255,255,255,.65); font-size: 13px; }
.logo-btn {
    padding: 7px 18px;
    background: rgba(255,255,255,.14);
    color: white;
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 8px;
    font-size: 13px; font-weight: 500;
    cursor: pointer;
    transition: .2s;
}
.logo-btn:hover { background: rgba(255,255,255,.24); }

/* ── Management Panel ────────────────────────── */
.mgmt-panel {
    background: var(--white);
    border-radius: var(--card-r);
    box-shadow: var(--sh);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.mgmt-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 22px 28px 18px;
    border-bottom: 1px solid #f0f2f8;
}
.mgmt-head h2 { font-size: 17px; font-weight: 800; color: var(--dark); }

.count-ctrl {
    display: flex; align-items: center;
    background: #f0f2f8;
    border-radius: 10px; overflow: hidden;
}
.cnt-btn {
    width: 36px; height: 36px;
    background: none; border: none;
    font-size: 20px; font-weight: 700;
    color: var(--purple); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.cnt-btn:hover { background: rgba(108,92,231,.12); }
.cnt-val { min-width: 38px; text-align: center; font-size: 15px; font-weight: 700; color: var(--dark); }

.mgmt-hint {
    padding: 10px 28px 6px;
    font-size: 12px; color: var(--mid);
}

.slices-body {
    padding: 10px 28px 4px;
    max-height: 440px;
    overflow-y: auto;
    flex: 1;
}
.slices-body::-webkit-scrollbar { width: 4px; }
.slices-body::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

.slice-item {
    display: flex; flex-direction: column; align-items: stretch;
    padding: 9px 12px;
    background: #f8f9ff;
    border: 1.5px solid #ececff;
    border-radius: 12px;
    margin-bottom: 7px;
    transition: all .2s;
}
.slice-item:hover { border-color: var(--purple-l); box-shadow: 0 2px 14px rgba(108,92,231,.1); }
.slice-top { display: flex; align-items: center; gap: 10px; }
.slice-bot { display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.tip-pills { display: flex; gap: 5px; flex-wrap: wrap; flex: 1; }
.tip-pill {
    padding: 4px 10px;
    border-radius: 20px;
    border: 1.5px solid #e0e0e0;
    background: white;
    color: var(--mid);
    font-size: 11px; font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.tip-pill:hover { border-color: var(--purple-l); color: var(--purple); }
.tip-pill.active {
    background: linear-gradient(135deg, var(--purple), var(--purple-l));
    border-color: transparent;
    color: white;
}
.deger-wrap { display: none; align-items: center; gap: 4px; }
.deger-wrap.show { display: flex; }
.s-deger {
    width: 90px;
    padding: 5px 8px;
    background: white;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 12px; font-weight: 600; color: var(--dark);
    transition: border-color .2s;
}
.s-deger:focus { outline: none; border-color: var(--purple); }
.deger-unit { font-size: 12px; font-weight: 700; color: var(--mid); }

.s-num {
    width: 24px; height: 24px;
    background: linear-gradient(135deg, var(--purple), var(--purple-l));
    color: white; border-radius: 7px;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.s-color {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 2.5px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
    cursor: pointer; flex-shrink: 0;
    position: relative; overflow: hidden;
}
.s-color input[type="color"] {
    position: absolute; inset: -6px;
    width: calc(100% + 12px); height: calc(100% + 12px);
    opacity: 0; cursor: pointer; border: none; padding: 0;
}
.s-name {
    flex: 1; min-width: 0;
    padding: 8px 12px;
    background: white;
    border: 1.5px solid #e9ecef;
    border-radius: 8px;
    font-size: 13px; font-weight: 500; color: var(--dark);
    transition: border-color .2s;
}
.s-name:focus { outline: none; border-color: var(--purple); }

/* Kazanan seç butonu */
.pick-btn {
    flex-shrink: 0;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px; font-weight: 600;
    cursor: pointer;
    border: 1.5px solid #e0e0e0;
    background: white;
    color: var(--mid);
    transition: all .2s;
    white-space: nowrap;
}
.pick-btn:hover { border-color: var(--green); color: var(--green); }
.pick-btn.winner {
    background: linear-gradient(135deg, var(--green), var(--teal));
    border-color: transparent;
    color: white;
    box-shadow: 0 3px 10px rgba(0,184,148,.35);
}

/* Kazanan dilim satır vurgusu */
.slice-item.winner-row {
    border-color: var(--green);
    background: rgba(0,184,148,.06);
    box-shadow: 0 2px 14px rgba(0,184,148,.12);
}

/* Footer */
.mgmt-foot {
    padding: 16px 28px 24px;
    border-top: 1px solid #f0f2f8;
}
.winner-info {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px;
    background: rgba(0,184,148,.08);
    border: 1.5px solid rgba(0,184,148,.25);
    border-radius: 12px;
    margin-bottom: 14px;
}
.winner-icon { font-size: 20px; }
.winner-label { font-size: 12px; color: var(--mid); font-weight: 500; }
.winner-name  { font-size: 15px; font-weight: 800; color: var(--green); }

.save-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--purple), var(--purple-l));
    color: white; border: none;
    border-radius: 12px;
    font-size: 15px; font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 22px rgba(108,92,231,.35);
    transition: all .25s;
    letter-spacing: .3px;
}
.save-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(108,92,231,.45); }
.save-btn:active:not(:disabled) { transform: translateY(0); }
.save-btn:disabled { opacity: .6; cursor: not-allowed; }

/* ── Modal ───────────────────────────────────── */
.modal-ov {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center; justify-content: center;
}
.modal-ov.show { display: flex; }
.modal-box {
    background: white;
    border-radius: 24px;
    padding: 40px 32px;
    text-align: center;
    max-width: 360px; width: 90%;
    box-shadow: 0 32px 80px rgba(0,0,0,.25);
    animation: popIn .4s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn { from{transform:scale(.7);opacity:0} to{transform:scale(1);opacity:1} }
.modal-emoji { font-size: 58px; margin-bottom: 14px; display: block; }
.modal-box h2 { font-size: 22px; font-weight: 800; color: var(--dark); margin-bottom: 6px; }
.modal-sub { font-size: 14px; color: var(--mid); margin-bottom: 10px; }
.modal-result {
    font-size: 28px; font-weight: 800; color: var(--purple);
    padding: 12px 24px;
    background: rgba(108,92,231,.08);
    border-radius: 12px;
    margin: 10px 0 22px;
    word-break: break-word;
}
.modal-close {
    padding: 12px 36px;
    background: linear-gradient(135deg, var(--purple), var(--purple-l));
    color: white; border: none; border-radius: 50px;
    font-size: 15px; font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(108,92,231,.3);
    transition: .2s;
}
.modal-close:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(108,92,231,.4); }

/* ── Toast ───────────────────────────────────── */
.toast {
    position: fixed; top: 24px; right: 24px;
    padding: 14px 22px;
    border-radius: 12px;
    font-size: 14px; font-weight: 600;
    color: white; z-index: 99999;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
    transform: translateX(140%);
    transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    display: flex; align-items: center; gap: 10px;
    max-width: 340px;
}
.toast.show { transform: translateX(0); }
.toast.success { background: linear-gradient(135deg, var(--green), var(--teal)); }
.toast.error   { background: linear-gradient(135deg, var(--red), var(--orange)); }
</style>

<div class="ck-page">

    {{-- Header --}}
    <div class="ck-header">
        <div class="ck-header-left">
            <div class="ck-header-icon">🎡</div>
            <div>
                <h1>Çarkıfelek Sistemi</h1>
                <p>Dilimlerinizi düzenleyin, müşterilerinize özel ödüller sunun</p>
            </div>
        </div>
        <div class="status-pill on" id="status-pill">
            <span class="status-dot"></span>
            <span id="status-text">Aktif</span>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="ck-grid">

        {{-- Wheel Panel --}}
        <div class="wheel-panel">
            <p class="wp-label">Önizleme</p>

            <div class="pointer-wrap">
                <div class="pointer"></div>
            </div>

            <div class="wheel-glow">
                <div class="wheel-wrap">
                    <svg id="wheel" viewBox="0 0 300 300"></svg>
                    <div class="wheel-center" onclick="document.getElementById('ck-logo-input').click()" title="Logo değiştir">
                        <img id="wheel-logo"
                             src="{{ $isletme->logo !== null ? '/'.$isletme->logo : '/public/isletmeyonetim_assets/img/avatar.png' }}"
                             alt="Logo">
                    </div>
                </div>
            </div>

            <div>
                <button class="spin-btn" id="spin-btn" onclick="testSpin()">🎲 Test Et</button>
            </div>

            <div class="wp-row" style="margin-top:20px">
                <span class="wp-row-label">Çark Durumu</span>
                <label class="toggle">
                    <input type="checkbox" id="status-toggle" checked onchange="toggleStatus()">
                    <span class="tslider"></span>
                </label>
            </div>

            <div class="logo-row">
                <span class="logo-row-label">📷 Logo Değiştir</span>
                <input type="file" id="ck-logo-input" accept="image/*" style="display:none">
                <button class="logo-btn" onclick="document.getElementById('ck-logo-input').click()">Seç</button>
            </div>
        </div>

        {{-- Management Panel --}}
        <div class="mgmt-panel">
            <div class="mgmt-head">
                <h2>Dilim Yönetimi</h2>
                <div class="count-ctrl">
                    <button class="cnt-btn" onclick="changeCount(-1)">−</button>
                    <span class="cnt-val" id="cnt-val">6</span>
                    <button class="cnt-btn" onclick="changeCount(1)">+</button>
                </div>
            </div>

            <p class="mgmt-hint">Kazandırmak istediğiniz dilimi seçin — müşteri çarkı ne kadar çevirirse çevirsin o dilim çıkar. (Min 6 · Maks 12 dilim)</p>

            <div class="slices-body" id="slices-list"></div>

            <div class="mgmt-foot">
                <div class="winner-info" id="winner-info">
                    <span class="winner-icon">🏆</span>
                    <div>
                        <div class="winner-label">Çıkacak dilim:</div>
                        <div class="winner-name" id="winner-name">Seçilmedi</div>
                    </div>
                </div>
                <button class="save-btn" id="save-btn" onclick="saveSlices()">💾 Ayarı Kaydet</button>
            </div>
        </div>

    </div>
</div>

{{-- Result Modal --}}
<div class="modal-ov" id="result-modal">
    <div class="modal-box">
        <span class="modal-emoji">🎉</span>
        <h2>Sonuç!</h2>
        <p class="modal-sub">Test çevirme sonucu:</p>
        <div class="modal-result" id="modal-result-text"></div>
        <button class="modal-close" onclick="closeModal()">Kapat</button>
    </div>
</div>

{{-- Toast --}}
<div class="toast" id="toast"></div>

<script>
(function () {
    /* ── Constants ── */
    const CX = 150, CY = 150, R = 130;
    const COLORS = [
        '#FF6B6B','#FF8E53','#FFC107','#51CF66','#339AF0',
        '#CC5DE8','#F06595','#74C0FC','#63E6BE','#FFD43B',
        '#FF922B','#20C997','#4DABF7','#DA77F2','#F783AC',
        '#E64980','#7950F2','#4C6EF5','#228BE6','#099268'
    ];
    const TIPS = [
        { id: 'puan',            label: '💰 Puan',     hasDeger: true,  unit: 'puan' },
        { id: 'hizmet_indirimi', label: '✂️ Hizmet %', hasDeger: true,  unit: '%'    },
        { id: 'urun_indirimi',   label: '📦 Ürün %',   hasDeger: true,  unit: '%'    },
        { id: 'tekrar_dene',     label: '🔄 Tekrar',   hasDeger: false, unit: ''     },
        { id: 'bos',             label: '— Boş',        hasDeger: false, unit: ''     },
    ];

    const HEADERS = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
        'Accept': 'application/json'
    };

    /* ── State ── */
    let slices = [
        { name: '100 Puan',    color: COLORS[0], tip: 'puan',           deger: 100  },
        { name: '%20 Hizmet',  color: COLORS[1], tip: 'hizmet_indirimi',deger: 20   },
        { name: '%10 Ürün',    color: COLORS[2], tip: 'urun_indirimi',  deger: 10   },
        { name: '50 Puan',     color: COLORS[3], tip: 'puan',           deger: 50   },
        { name: 'Tekrar Dene', color: COLORS[4], tip: 'tekrar_dene',    deger: null },
        { name: 'Boş',         color: COLORS[5], tip: 'bos',            deger: null }
    ];
    let selectedIdx = 0;   // admin'in seçtiği kazanan dilim
    let isActive    = true;
    let currentRot  = 0;
    let spinning    = false;

    /* ── Elements ── */
    const wheelEl     = document.getElementById('wheel');
    const slicesList  = document.getElementById('slices-list');
    const cntVal      = document.getElementById('cnt-val');
    const winnerName  = document.getElementById('winner-name');
    const spinBtn     = document.getElementById('spin-btn');
    const statusToggle= document.getElementById('status-toggle');
    const statusPill  = document.getElementById('status-pill');
    const statusText  = document.getElementById('status-text');
    const toast       = document.getElementById('toast');
    const wheelLogo   = document.getElementById('wheel-logo');
    const logoInput   = document.getElementById('ck-logo-input');
    const resultModal = document.getElementById('result-modal');
    const resultText  = document.getElementById('modal-result-text');

    /* ── Init ── */
    document.addEventListener('DOMContentLoaded', () => {
        loadData();
        logoInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const fr = new FileReader();
                fr.onload = e => { wheelLogo.src = e.target.result; };
                fr.readAsDataURL(this.files[0]);
            }
        });
        resultModal.addEventListener('click', e => {
            if (e.target === resultModal) closeModal();
        });
    });

    /* ── Load from server ── */
    async function loadData() {
        try {
            const res  = await fetch('{{ route("isletmeadmin.carkverilerigetir") }}', { headers: HEADERS });
            const data = await res.json();
            if (data.success && data.data) {
                isActive = data.data.aktifmi == 1;
                if (data.data.dilimler && data.data.dilimler.length > 0) {
                    slices = data.data.dilimler.map(d => ({
                        name:        d.name,
                        color:       d.color || COLORS[0],
                        probability: parseInt(d.probability) || 0,
                        tip:         d.tip   || 'bos',
                        deger:       d.deger != null ? parseFloat(d.deger) : null,
                    }));
                    const found = slices.findIndex(s => s.probability === 100);
                    selectedIdx = found >= 0 ? found : 0;
                    cntVal.textContent = slices.length;
                }
            }
        } catch (e) { console.error('Veri yüklenemedi:', e); }
        render();
    }

    /* ── Render ── */
    function render() {
        renderWheel();
        renderList();
        updateWinnerUI();
        updateStatusUI();
    }

    function renderWheel() {
        wheelEl.innerHTML = '';
        if (!slices.length) return;

        const n   = slices.length;
        const ang = 360 / n;

        slices.forEach((sl, i) => {
            const sa = i * ang, ea = (i + 1) * ang;
            const sr = (sa - 90) * Math.PI / 180;
            const er = (ea - 90) * Math.PI / 180;
            const x1 = CX + R * Math.cos(sr), y1 = CY + R * Math.sin(sr);
            const x2 = CX + R * Math.cos(er), y2 = CY + R * Math.sin(er);
            const lg = ang > 180 ? 1 : 0;

            /* Dilim */
            const path = svgEl('path');
            path.setAttribute('d', `M ${CX} ${CY} L ${x1} ${y1} A ${R} ${R} 0 ${lg} 1 ${x2} ${y2} Z`);
            path.setAttribute('fill', sl.color);
            path.setAttribute('stroke', 'rgba(255,255,255,.6)');
            path.setAttribute('stroke-width', '1.5');
            wheelEl.appendChild(path);

            /* Kazanan: dış kenar altın bandı */
            if (i === selectedIdx) {
                const rInner = R - 14;
                const xi1 = CX + rInner * Math.cos(sr), yi1 = CY + rInner * Math.sin(sr);
                const xi2 = CX + rInner * Math.cos(er), yi2 = CY + rInner * Math.sin(er);
                const band = svgEl('path');
                band.setAttribute('d', `M ${x1} ${y1} A ${R} ${R} 0 ${lg} 1 ${x2} ${y2} L ${xi2} ${yi2} A ${rInner} ${rInner} 0 ${lg} 0 ${xi1} ${yi1} Z`);
                band.setAttribute('fill', 'rgba(255,215,0,.5)');
                band.setAttribute('stroke', 'rgba(255,215,0,.8)');
                band.setAttribute('stroke-width', '1');
                wheelEl.appendChild(band);
            }

            /* Radyal yazı — merkez→dış, harfler hiç ters değil */
            const tAng = sa + ang / 2;
            const tRad = (tAng - 90) * Math.PI / 180;

            // Hub kenarı (36) ile kazanan bandı (R-14=116) arasındaki merkez
            const hubR  = 36, bandR = R - 14;
            const dist  = Math.round((hubR + bandR) / 2);   // ≈ 76
            const radLen = bandR - hubR;                     // ≈ 80px, yazı boyunca radyal alan

            const tx = CX + dist * Math.cos(tRad);
            const ty = CY + dist * Math.sin(tRad);

            // Radyal formül: üst yarı tAng-90, alt yarı tAng-270 (flip → harfler dik kalır)
            const textRot = tAng <= 180 ? tAng - 90 : tAng - 270;

            // Yay genişliği (yazı yüksekliğini kısıtlar): 2·dist·sin(π/n)
            const arcW = 2 * dist * Math.sin(Math.PI / n);
            // Font: 2 satır için toplam yükseklik yay genişliğinin %85'ine sığmalı
            // n≤8 → 2 satır (yay genişliği taşır), n>8 → 1 satır (dar dilimlerde taşma önler)
            const maxLn = n <= 8 ? 2 : 1;
            const fs    = Math.min(16, Math.max(11, Math.floor(arcW * 0.85 / (maxLn * 1.25))));
            const lh    = fs + 3;
            const maxCh = Math.max(6, Math.floor(radLen / (fs * 0.60)));

            const label = buildAutoName(slices[i]);   // tip+deger'den oluşan etiket
            let lines = wrapText(label, maxCh);
            if (lines.length > maxLn) {
                lines = [label.length > maxCh ? label.slice(0, maxCh - 1) + '…' : label];
            }
            const sy = ty - ((lines.length - 1) * lh / 2);

            const g = svgEl('g');
            g.setAttribute('transform', `rotate(${textRot}, ${tx}, ${ty})`);

            lines.forEach((ln, li) => {
                const t = svgEl('text');
                t.setAttribute('x', tx);
                t.setAttribute('y', sy + li * lh);
                t.setAttribute('text-anchor', 'middle');
                t.setAttribute('dominant-baseline', 'middle');
                t.setAttribute('font-size', fs);
                t.setAttribute('font-weight', '600');
                t.setAttribute('fill', 'white');
                t.setAttribute('paint-order', 'stroke');
                t.setAttribute('stroke', 'rgba(0,0,0,.55)');
                t.setAttribute('stroke-width', '2.5');
                t.setAttribute('stroke-linejoin', 'round');
                t.textContent = ln;
                g.appendChild(t);
            });

            wheelEl.appendChild(g);
        });

        /* Center mask */
        const cc = svgEl('circle');
        cc.setAttribute('cx', CX); cc.setAttribute('cy', CY); cc.setAttribute('r', 33);
        cc.setAttribute('fill', 'white');
        cc.setAttribute('stroke', 'rgba(255,255,255,.8)');
        cc.setAttribute('stroke-width', '3');
        wheelEl.appendChild(cc);
    }

    function renderList() {
        slicesList.innerHTML = '';
        slices.forEach((sl, i) => {
            const isWinner = i === selectedIdx;
            const tipObj   = TIPS.find(t => t.id === (sl.tip || 'bos')) || TIPS[4];
            const degerVal = sl.deger != null ? sl.deger : '';
            const placeholder = tipObj.id === 'puan' ? 'Puan miktarı' : 'Yüzde (%)';

            const div = document.createElement('div');
            div.className = 'slice-item' + (isWinner ? ' winner-row' : '');
            div.innerHTML = `
                <div class="slice-top">
                    <div class="s-num">${i + 1}</div>
                    <div class="s-color" style="background:${esc(sl.color)}" title="Renk seç">
                        <input type="color" value="${esc(sl.color)}" data-i="${i}"
                            oninput="window.CK.color(this)" onchange="window.CK.color(this)">
                    </div>
                    <input type="text" class="s-name" value="${esc(sl.name)}"
                        data-i="${i}" placeholder="Dilim adı"
                        oninput="window.CK.name(this)" onblur="window.CK.name(this)">
                    <button class="pick-btn${isWinner ? ' winner' : ''}" onclick="window.CK.pick(${i})">
                        ${isWinner ? '★ Kazanan' : 'Seç'}
                    </button>
                </div>
                <div class="slice-bot">
                    <div class="tip-pills">
                        ${TIPS.map(t => `<button class="tip-pill${sl.tip === t.id ? ' active' : ''}" onclick="window.CK.setTip(${i},'${t.id}')">${t.label}</button>`).join('')}
                    </div>
                    <div class="deger-wrap${tipObj.hasDeger ? ' show' : ''}" data-i="${i}">
                        <input type="number" class="s-deger" data-i="${i}" min="0" step="any"
                               value="${degerVal}" placeholder="${placeholder}"
                               oninput="window.CK.setDeger(this)">
                        <span class="deger-unit">${tipObj.unit}</span>
                    </div>
                </div>
            `;
            slicesList.appendChild(div);
        });
    }

    /* tip + deger'den otomatik etiket üretir — çark ve modal aynı değeri gösterir */
    function buildAutoName(sl) {
        const d = sl.deger;
        switch (sl.tip) {
            case 'puan':            return d != null ? d + ' Puan'       : (sl.name || 'Puan');
            case 'hizmet_indirimi': return d != null ? '%' + d + ' Hizmet' : (sl.name || 'Hizmet İnd.');
            case 'urun_indirimi':   return d != null ? '%' + d + ' Ürün'   : (sl.name || 'Ürün İnd.');
            case 'tekrar_dene':     return 'Tekrar Dene';
            case 'bos':             return 'Boş';
            default:                return sl.name || 'Ödül';
        }
    }

    function updateWinnerUI() {
        const sl = slices[selectedIdx];
        if (!sl) { winnerName.textContent = 'Seçilmedi'; return; }
        winnerName.textContent = buildAutoName(sl);
    }

    function updateStatusUI() {
        statusToggle.checked = isActive;
        if (isActive) {
            statusPill.className = 'status-pill on';
            statusText.textContent = 'Aktif';
        } else {
            statusPill.className = 'status-pill off';
            statusText.textContent = 'Pasif';
        }
    }

    /* ── Input handlers (exposed globally) ── */
    window.CK = {
        color(input) {
            const i = +input.dataset.i;
            slices[i].color = input.value;
            input.closest('.s-color').style.background = input.value;
            renderWheel();
        },
        name(input) {
            const i = +input.dataset.i;
            slices[i].name = input.value || `Ödül ${i + 1}`;
            renderWheel();
            updateWinnerUI();
        },
        pick(i) {
            selectedIdx = i;
            render();
        },
        setTip(i, tip) {
            slices[i].tip = tip;
            const tipObj = TIPS.find(t => t.id === tip) || TIPS[4];
            if (!tipObj.hasDeger) slices[i].deger = null;
            // İsim alanını otomatik güncelle
            slices[i].name = buildAutoName(slices[i]);
            renderList();
            renderWheel();
            updateWinnerUI();
        },
        setDeger(input) {
            const i   = +input.dataset.i;
            const val = parseFloat(input.value);
            slices[i].deger = isNaN(val) ? null : val;
            // İsim alanını otomatik güncelle (DOM'da da yansısın)
            const newName = buildAutoName(slices[i]);
            slices[i].name = newName;
            const nameEl = document.querySelector(`.s-name[data-i="${i}"]`);
            if (nameEl) nameEl.value = newName;
            renderWheel();
            updateWinnerUI();
        }
    };

    /* ── Count control ── */
    window.changeCount = function (delta) {
        const next = slices.length + delta;
        if (next < 6)  { showToast('En az 6 dilim olmalıdır.', 'error');  return; }
        if (next > 12) { showToast('En fazla 12 dilim eklenebilir.', 'error'); return; }
        if (delta < 0) {
            slices = slices.slice(0, next);
            if (selectedIdx >= slices.length) selectedIdx = slices.length - 1;
        } else {
            const idx = slices.length % COLORS.length;
            slices.push({ name: `Ödül ${slices.length + 1}`, color: COLORS[idx], tip: 'bos', deger: null });
        }
        cntVal.textContent = slices.length;
        render();
    };

    /* ── Spin — her zaman selectedIdx'e döner ── */
    window.testSpin = function () {
        if (spinning || slices.length < 2) return;
        spinning = true;
        spinBtn.disabled = true;
        spinBtn.textContent = '⏳ Çevriliyor...';

        const ang    = 360 / slices.length;
        // Kazanan dilim içinde küçük rastgele sapma — görünüm doğal
        const jitter = (Math.random() - 0.5) * ang * 0.6;
        const stopAt = (selectedIdx + 0.5) * ang + jitter;
        const offset = ((360 - stopAt) % 360 + 360) % 360;

        const nSpins = (6 + Math.floor(Math.random() * 4)) * 360;
        const curMod = ((currentRot % 360) + 360) % 360;
        let   diff   = offset - curMod;
        if (diff < 0) diff += 360;
        currentRot  += nSpins + diff;

        wheelEl.style.transition = 'transform 5.5s cubic-bezier(0.17, 0.67, 0.12, 0.99)';
        wheelEl.style.transform  = `rotate(${currentRot}deg)`;

        setTimeout(() => {
            spinning = false;
            spinBtn.disabled = false;
            spinBtn.textContent = '🎲 Test Et';
            showResultModal(selectedIdx);
        }, 5700);
    };

    /* ── Modal ── */
    function showResultModal(idx) {
        resultText.textContent = buildAutoName(slices[idx]);
        resultModal.classList.add('show');
    }
    window.closeModal = function () { resultModal.classList.remove('show'); };

    /* ── Toggle status ── */
    window.toggleStatus = function () {
        isActive = statusToggle.checked;
        updateStatusUI();
        saveToServer(true);
    };

    /* ── Save ── */
    window.saveSlices = async function () {
        // DOM → state senkronizasyonu (oninput yetersiz kaldığında güvence)
        document.querySelectorAll('.s-name').forEach(el => {
            const i = +el.dataset.i;
            if (slices[i]) slices[i].name = el.value || buildAutoName(slices[i]);
        });
        document.querySelectorAll('.s-deger').forEach(el => {
            const i = +el.dataset.i;
            if (!slices[i]) return;
            const v = parseFloat(el.value);
            slices[i].deger = isNaN(v) ? null : v;
        });

        if (selectedIdx < 0 || selectedIdx >= slices.length) {
            showToast('Lütfen önce bir kazanan dilim seçin.', 'error');
            return;
        }
        await saveToServer(false);
    };

    async function saveToServer(statusOnly) {
        const saveBtn = document.getElementById('save-btn');
        if (!statusOnly) { saveBtn.disabled = true; saveBtn.textContent = '⏳ Kaydediliyor...'; }

        const payload = slices.map((sl, i) => ({
            name:        buildAutoName(sl),   // her zaman tip+deger'den üretilen isim
            color:       sl.color,
            probability: i === selectedIdx ? 100 : 0,
            tip:         sl.tip   || 'bos',
            deger:       sl.deger != null ? sl.deger : null,
        }));

        try {
            const res  = await fetch('{{ route("isletmeadmin.carkdilimekle") }}', {
                method: 'POST',
                headers: HEADERS,
                body: JSON.stringify({ dilimler: payload, aktifmi: isActive ? 1 : 0 })
            });
            const data = await res.json();
            console.log('[Çark kaydet] yanıt:', data);
            if (data.success) {
                if (!statusOnly) {
                    showToast('Ayar kaydedildi! 🎉', 'success');
                    // Sunucu yanıtıyla slices'i ASLA güncelleme.
                    // Kayıt başarılıysa mevcut client state doğrudur; üzerine yazmak
                    // migration çalışmamışsa tip/deger kaybına yol açar.
                    render();
                }
            } else {
                showToast(data.message || 'Bir hata oluştu!', 'error');
            }
        } catch (e) {
            showToast('Bağlantı hatası!', 'error');
        }

        if (!statusOnly) { saveBtn.disabled = false; saveBtn.textContent = '💾 Ayarı Kaydet'; }
    }

    /* ── Toast ── */
    let toastTimer;
    function showToast(msg, type) {
        clearTimeout(toastTimer);
        toast.className = `toast ${type} show`;
        toast.textContent = msg;
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ── Helpers ── */
    function svgEl(tag) { return document.createElementNS('http://www.w3.org/2000/svg', tag); }

    function wrapText(str, max) {
        const words = str.split(' ');
        const lines = [];
        let line = '';
        for (const w of words) {
            if (w.length > max) {
                if (line) { lines.push(line); line = ''; }
                for (let j = 0; j < w.length; j += max) lines.push(w.slice(j, j + max));
            } else if ((line ? line + ' ' + w : w).length <= max) {
                line = line ? line + ' ' + w : w;
            } else {
                if (line) lines.push(line);
                line = w;
            }
        }
        if (line) lines.push(line);
        if (lines.length > 3) lines = [lines[0], lines[1], lines[2].slice(0, max - 1) + '…'];
        return lines;
    }

    function esc(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

@endsection
