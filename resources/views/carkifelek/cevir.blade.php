@extends('layout.layout')

@section('content')
<style>
:root {
    --ck-purple: #6c5ce7;
    --ck-purple-l: #a29bfe;
    --ck-pink: #fd79a8;
    --ck-gold: #fdcb6e;
    --ck-green: #00b894;
    --ck-dark: #2d3436;
}
.ck-page { max-width:860px; margin:40px auto; padding:30px 20px; }
.ck-hero {
    text-align:center; color:#fff;
    background: linear-gradient(135deg,#6c5ce7 0%,#a29bfe 55%,#fd79a8 100%);
    padding: 30px 20px 22px; border-radius: 24px 24px 0 0;
}
.ck-hero h1 { font-size:28px; font-weight:800; margin:0 0 6px; letter-spacing:-.5px; }
.ck-hero p  { font-size:14px; opacity:.92; margin:0; }
.ck-body {
    background:#fff; border-radius:0 0 24px 24px;
    padding: 32px 22px 36px;
    box-shadow: 0 14px 40px rgba(108,92,231,.12);
    display:flex; flex-direction:column; align-items:center;
}
.hak-rozeti {
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 16px; background: rgba(255,255,255,.18);
    backdrop-filter: blur(6px);
    border:1px solid rgba(255,255,255,.25);
    border-radius:50px; margin-top:10px;
    font-size:13px; font-weight:600;
}
.hak-rozeti b { font-size:15px; }

/* Pointer pin */
.pointer-wrap { display:flex; justify-content:center; margin-bottom:-16px; position:relative; z-index:10; }
.pointer { display:block; transform-origin:50% 20%; filter: drop-shadow(0 4px 10px rgba(0,0,0,.55)); }
@keyframes pinBounce {
    0%  { transform: rotate(0); }
    25% { transform: rotate(24deg); }
    55% { transform: rotate(-7deg); }
    75% { transform: rotate(10deg); }
    100% { transform: rotate(0); }
}
.pointer.tick { animation: pinBounce .28s cubic-bezier(.36,.07,.19,.97); }

.wheel-glow {
    display:inline-block; padding:7px;
    background: conic-gradient(from 0deg, var(--ck-purple), var(--ck-purple-l), var(--ck-pink), var(--ck-gold), var(--ck-green), var(--ck-purple));
    border-radius:50%;
    box-shadow: 0 0 32px rgba(108,92,231,.5), 0 0 72px rgba(108,92,231,.25);
    animation: spinRing 10s linear infinite;
}
@keyframes spinRing { to { filter: hue-rotate(360deg); } }
.wheel-wrap {
    position:relative; width:360px; height:360px;
    border-radius:50%; overflow:hidden; background:#160630;
    display:inline-block;
}
@media(max-width:520px){ .wheel-wrap { width:280px; height:280px; } }
#wheel { width:100%; height:100%; display:block; transform-origin:50% 50%; }

.cevir-btn {
    margin-top:26px; padding:15px 48px;
    background: linear-gradient(135deg,#fd79a8,#e17055);
    color:#fff; border:none; border-radius:50px;
    font-size:17px; font-weight:800; letter-spacing:.3px;
    cursor:pointer; transition:.2s;
    box-shadow: 0 10px 26px rgba(225,112,85,.45);
    text-transform: uppercase;
}
.cevir-btn:hover:not(:disabled) { transform:translateY(-3px); box-shadow:0 14px 36px rgba(225,112,85,.55); }
.cevir-btn:disabled { opacity:.55; cursor:not-allowed; }

.hak-info {
    margin-top:18px; font-size:14px; color:#636e72; text-align:center;
}
.hak-info b { color: var(--ck-purple); font-weight:700; }

/* Modal (kutlama sonrası sonuç) */
.modal-ov {
    display:none; position:fixed; inset:0;
    background: rgba(0,0,0,.55); backdrop-filter: blur(6px);
    z-index:10000; align-items:center; justify-content:center;
}
.modal-ov.show { display:flex; }
.modal-box {
    background:#fff; border-radius:24px; padding:36px 28px;
    max-width:440px; width:92%; text-align:center;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
    position:relative; z-index:10002;
    animation: popIn .45s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn { from{ transform:scale(.6); opacity:0 } to { transform:scale(1); opacity:1 } }
.modal-emoji { font-size:58px; display:block; margin-bottom:14px; }
.modal-box h2 { font-size:22px; font-weight:800; color:var(--ck-dark); margin-bottom:6px; }
.modal-sub { font-size:14px; color:#636e72; margin-bottom:12px; }
.modal-result {
    background: linear-gradient(135deg,#eef2ff,#fef3c7);
    border-radius:14px; padding:18px 14px;
    color: var(--ck-purple); font-size:22px; font-weight:800;
    margin-bottom:12px;
}
.modal-code {
    display:inline-block; margin-bottom:10px;
    padding:8px 18px; background:#fef3c7; color:#92400e;
    border-radius:10px; font-family: monospace;
    font-size:18px; font-weight:800; letter-spacing:3px;
    border: 2px dashed #f59e0b;
}
.modal-code-label { display:block; font-size:12px; color:#636e72; margin-bottom:4px; font-family: inherit; letter-spacing: normal; font-weight:500; }
.modal-close {
    margin-top:16px; padding:12px 38px;
    background: var(--ck-purple); color:#fff; border:none; border-radius:50px;
    font-weight:700; cursor:pointer; transition:.2s;
}
.modal-close:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(108,92,231,.4); }

.odullerim-link {
    display:inline-block; margin-top:8px; font-size:13px;
    color: var(--ck-purple); text-decoration:none; font-weight:600;
}

#toast {
    position:fixed; top:20px; right:20px; z-index:10003;
    padding:14px 20px; background: #e17055; color:#fff;
    border-radius:12px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2);
    display:none;
}
#toast.show { display:block; animation: slideIn .3s ease-out; }
@keyframes slideIn { from{ transform:translateX(120%); opacity:0 } to { transform:translateX(0); opacity:1 } }
</style>

<div class="ck-page">
    <div class="ck-hero">
        <h1>🎡 Çarkıfelek</h1>
        <p>{{ $salon->salon_adi }} size özel ödüller bekliyor!</p>
        <div class="hak-rozeti">Çevirme hakkınız: <b id="hak-sayisi">{{ $kalanHak }}</b></div>
    </div>

    <div class="ck-body">
        <div class="pointer-wrap">
            <svg id="pointer-pin" class="pointer" viewBox="0 0 32 54" width="32" height="54">
                <defs>
                    <linearGradient id="pg" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%"   stop-color="#7f1d1d"/>
                        <stop offset="40%"  stop-color="#ef4444"/>
                        <stop offset="70%"  stop-color="#fca5a5"/>
                        <stop offset="100%" stop-color="#7f1d1d"/>
                    </linearGradient>
                    <linearGradient id="pg2" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%"   stop-color="#92400e"/>
                        <stop offset="50%"  stop-color="#fbbf24"/>
                        <stop offset="100%" stop-color="#92400e"/>
                    </linearGradient>
                </defs>
                <polygon points="16,54 2,10 30,10" fill="url(#pg)" stroke="#7f1d1d" stroke-width="1"/>
                <rect x="2" y="0" width="28" height="12" rx="5" fill="url(#pg)" stroke="#7f1d1d" stroke-width="1"/>
                <polygon points="16,48 8,14 13,14" fill="rgba(255,255,255,0.18)"/>
                <circle cx="16" cy="54" r="4" fill="url(#pg2)" stroke="#78350f" stroke-width="1"/>
            </svg>
        </div>

        <div class="wheel-glow">
            <div class="wheel-wrap">
                <svg id="wheel" viewBox="0 0 300 300"></svg>
            </div>
        </div>

        <button class="cevir-btn" id="cevir-btn" onclick="cevirCarki()" {{ $kalanHak < 1 ? 'disabled' : '' }}>
            🎲 Çarkı Çevir
        </button>

        <div class="hak-info">
            @if($kalanHak > 0)
                <b>{{ $kalanHak }}</b> çevirme hakkınız var. Her onaylı randevunuz size 1 hak kazandırır.
            @else
                Çevirme hakkınız bulunmuyor. Salonumuzda randevu alıp onaylatırsanız hak kazanırsınız.
            @endif
            <br><a href="{{ route('cark.odullerim') }}" class="odullerim-link">🎁 Kazandığım Ödüller</a>
        </div>
    </div>
</div>

<div class="modal-ov" id="result-modal">
    <div class="modal-box">
        <span class="modal-emoji">🎉</span>
        <h2>Tebrikler!</h2>
        <p class="modal-sub">Kazandığınız ödül:</p>
        <div class="modal-result" id="result-text">—</div>
        <div id="code-wrap" style="display:none;">
            <div class="modal-code">
                <span class="modal-code-label">Kupon Kodunuz</span>
                <span id="coupon-code">—</span>
            </div>
            <p style="font-size:12px;color:#636e72;margin-bottom:8px;">Bu kodu 30 gün içinde salonda kullanabilirsiniz.</p>
        </div>
        <button class="modal-close" onclick="closeResult()">Tamam</button>
    </div>
</div>

<div id="toast"></div>

<script>
(function() {
    const DILIMLER = @json($dilimler->map(function($d){
        return [
            'id'     => $d->id,
            'ismi'   => $d->dilim_ismi,
            'renk'   => $d->renk_kodu,
            'tip'    => $d->tip ?? 'bos',
            'deger'  => $d->deger !== null ? (float) $d->deger : null,
        ];
    }));
    const SALON_ID = {{ $salon->id }};
    const CSRF     = '{{ csrf_token() }}';
    const CEVIR_URL= '{{ route("cark.cevir") }}';

    const CX = 150, CY = 150, R = 130;
    const wheelEl   = document.getElementById('wheel');
    const cevirBtn  = document.getElementById('cevir-btn');
    const hakEl     = document.getElementById('hak-sayisi');
    const toastEl   = document.getElementById('toast');
    const resModal  = document.getElementById('result-modal');
    const resText   = document.getElementById('result-text');
    const codeWrap  = document.getElementById('code-wrap');
    const codeEl    = document.getElementById('coupon-code');
    let spinning    = false;
    let currentRot  = 0;

    /* SVG oluştur */
    function svgEl(n){ return document.createElementNS('http://www.w3.org/2000/svg', n); }

    function buildLabel(d) {
        switch (d.tip) {
            case 'puan':            return d.deger != null ? 'Puan' : (d.ismi || 'Puan');
            case 'hizmet_indirimi': return d.deger != null ? 'Hizmet İnd.' : (d.ismi || 'Hizmet İnd.');
            case 'urun_indirimi':   return d.deger != null ? 'Ürün İnd.'   : (d.ismi || 'Ürün İnd.');
            case 'tekrar_dene':     return 'Tekrar Dene';
            case 'bos':             return 'Boş';
            default:                return d.ismi || 'Ödül';
        }
    }
    function buildFullLabel(d) {
        if ((d.tip === 'puan' || d.tip === 'hizmet_indirimi' || d.tip === 'urun_indirimi') && d.deger != null) {
            const numStr = d.tip.includes('indirimi') ? '%' + d.deger : d.deger;
            return numStr + ' ' + buildLabel(d);
        }
        return buildLabel(d);
    }

    function wrapText(text, maxCh) {
        const words = (text || '').split(/\s+/);
        const lines = []; let cur = '';
        words.forEach(w => {
            if ((cur + ' ' + w).trim().length <= maxCh) cur = (cur + ' ' + w).trim();
            else { if (cur) lines.push(cur); cur = w; }
        });
        if (cur) lines.push(cur);
        return lines.length ? lines : [''];
    }

    function renderWheel() {
        wheelEl.innerHTML = '';
        const n = DILIMLER.length;
        const ang = 360 / n;

        DILIMLER.forEach((sl, i) => {
            const sa = i * ang, ea = (i+1) * ang;
            const sr = (sa - 90) * Math.PI / 180;
            const er = (ea - 90) * Math.PI / 180;
            const x1 = CX + R * Math.cos(sr), y1 = CY + R * Math.sin(sr);
            const x2 = CX + R * Math.cos(er), y2 = CY + R * Math.sin(er);
            const lg = ang > 180 ? 1 : 0;

            const path = svgEl('path');
            path.setAttribute('d', `M ${CX} ${CY} L ${x1} ${y1} A ${R} ${R} 0 ${lg} 1 ${x2} ${y2} Z`);
            path.setAttribute('fill', sl.renk);
            path.setAttribute('stroke', 'rgba(255,255,255,.7)');
            path.setAttribute('stroke-width', '2');
            wheelEl.appendChild(path);

            const tAng = sa + ang / 2;
            const tRad = (tAng - 90) * Math.PI / 180;
            const textRot = tAng <= 180 ? tAng - 90 : tAng - 270;
            const hasDeger = ['puan','hizmet_indirimi','urun_indirimi'].includes(sl.tip) && sl.deger != null;

            function addText(x, y, content, fs, fw, fill, strokeC, strokeW, rot) {
                const r = (rot !== undefined) ? rot : textRot;
                const g = svgEl('g');
                g.setAttribute('transform', `rotate(${r}, ${x}, ${y})`);
                const t = svgEl('text');
                t.setAttribute('x', x); t.setAttribute('y', y);
                t.setAttribute('text-anchor', 'middle');
                t.setAttribute('dominant-baseline', 'middle');
                t.setAttribute('font-size', fs); t.setAttribute('font-weight', fw);
                t.setAttribute('fill', fill);
                t.setAttribute('paint-order', 'stroke');
                t.setAttribute('stroke', strokeC); t.setAttribute('stroke-width', strokeW);
                t.setAttribute('stroke-linejoin','round');
                t.textContent = content;
                g.appendChild(t); wheelEl.appendChild(g);
            }

            if (hasDeger) {
                const numFs  = n <= 8 ? 17 : 14;
                const catFs  = n <= 8 ? 11 : 9;

                // Rakam — dış kenara sabit, teğet, beyaz
                const numStr = sl.tip.includes('indirimi') ? '%' + sl.deger : String(sl.deger);
                const numDist = R - (n <= 8 ? 16 : 13);
                const nx = CX + numDist * Math.cos(tRad);
                const ny = CY + numDist * Math.sin(tRad);
                const ng = svgEl('g');
                ng.setAttribute('transform', `rotate(${tAng}, ${nx}, ${ny})`);
                const nt = svgEl('text');
                nt.setAttribute('x', nx); nt.setAttribute('y', ny);
                nt.setAttribute('text-anchor', 'middle');
                nt.setAttribute('dominant-baseline', 'middle');
                nt.setAttribute('font-size', numFs); nt.setAttribute('font-weight', '900');
                nt.setAttribute('fill', 'white');
                nt.setAttribute('paint-order', 'stroke');
                nt.setAttribute('stroke', 'rgba(0,0,0,.75)');
                nt.setAttribute('stroke-width', '3.5');
                nt.setAttribute('stroke-linejoin','round');
                nt.textContent = numStr;
                ng.appendChild(nt); wheelEl.appendChild(ng);

                // Kategori — iç bölgede radyal, sarmalı
                const innerLabel = buildLabel(sl);
                const catDist = n <= 8 ? 68 : 60;
                const cx2 = CX + catDist * Math.cos(tRad);
                const cy2 = CY + catDist * Math.sin(tRad);
                const catMaxCh = Math.max(4, Math.floor(55 / (catFs * 0.62)));
                let lines = wrapText(innerLabel, catMaxCh);
                if (lines.length > 2) lines = [innerLabel.slice(0, catMaxCh - 1) + '…'];
                const catLH = catFs + 2;
                const catSY = cy2 - ((lines.length - 1) * catLH / 2);
                const catG = svgEl('g');
                catG.setAttribute('transform', `rotate(${textRot}, ${cx2}, ${cy2})`);
                lines.forEach((ln, li) => {
                    const t = svgEl('text');
                    t.setAttribute('x', cx2); t.setAttribute('y', catSY + li * catLH);
                    t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle');
                    t.setAttribute('font-size', catFs); t.setAttribute('font-weight','700');
                    t.setAttribute('fill','rgba(255,255,255,.92)');
                    t.setAttribute('paint-order','stroke');
                    t.setAttribute('stroke','rgba(0,0,0,.5)'); t.setAttribute('stroke-width','2');
                    t.setAttribute('stroke-linejoin','round');
                    t.textContent = ln; catG.appendChild(t);
                });
                wheelEl.appendChild(catG);
            } else {
                // Metin ödülü
                const dist  = n <= 8 ? 76 : 68;
                const fs    = n <= 8 ? 12 : 10;
                const maxCh = Math.max(6, Math.floor(80 / (fs * 0.60)));
                const lh    = fs + 3;
                const label = buildLabel(sl);
                let lines   = wrapText(label, maxCh);
                if (lines.length > 2) lines = [label.slice(0, maxCh - 1) + '…'];
                const tx = CX + dist * Math.cos(tRad);
                const ty = CY + dist * Math.sin(tRad);
                const sy = ty - ((lines.length - 1) * lh / 2);
                const g = svgEl('g');
                g.setAttribute('transform', `rotate(${textRot}, ${tx}, ${ty})`);
                lines.forEach((ln, li) => {
                    const t = svgEl('text');
                    t.setAttribute('x', tx); t.setAttribute('y', sy + li * lh);
                    t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle');
                    t.setAttribute('font-size', fs); t.setAttribute('font-weight','700');
                    t.setAttribute('fill','white');
                    t.setAttribute('paint-order','stroke');
                    t.setAttribute('stroke','rgba(0,0,0,.55)'); t.setAttribute('stroke-width','2.5');
                    t.setAttribute('stroke-linejoin','round');
                    t.textContent = ln; g.appendChild(t);
                });
                wheelEl.appendChild(g);
            }
        });
    }

    /* Web Audio — tık + kutlama */
    let _ctx = null;
    function ac() { if (!_ctx) _ctx = new (window.AudioContext || window.webkitAudioContext)(); return _ctx; }
    function playTick(vol) {
        try {
            const c = ac(); if (c.state === 'suspended') c.resume();
            const now = c.currentTime;
            const len = Math.floor(c.sampleRate * 0.055);
            const buf = c.createBuffer(1, len, c.sampleRate);
            const d = buf.getChannelData(0);
            for (let i = 0; i < len; i++) {
                const t = i / len;
                d[i] = (Math.random() * 2 - 1) * Math.pow(1 - t, 4) * (vol || 0.45);
            }
            const src = c.createBufferSource(); src.buffer = buf;
            const g = c.createGain(); g.gain.setValueAtTime(1, now);
            src.connect(g); g.connect(c.destination); src.start(now);
        } catch(e){}
    }
    function startTickLoop() {
        const pin = document.getElementById('pointer-pin');
        const n = DILIMLER.length; const sliceAng = 360 / n; let last = -1;
        function frame() {
            if (!spinning) return;
            const mat = new DOMMatrix(window.getComputedStyle(wheelEl).transform);
            const a = (Math.atan2(mat.b, mat.a) * 180 / Math.PI + 360) % 360;
            const idx = Math.floor(((360 - a) % 360) / sliceAng) % n;
            if (idx !== last) {
                last = idx; playTick(0.45);
                if (pin) { pin.classList.remove('tick'); void pin.offsetWidth; pin.classList.add('tick'); }
            }
            requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    }

    /* Kutlama (konfeti + balon + fişek) */
    let cCan = null, cCtx = null, cRAF = null, cParts = [];
    function startCeleb() {
        if (!cCan) {
            cCan = document.createElement('canvas');
            cCan.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:10001;pointer-events:none';
            document.body.appendChild(cCan);
            cCtx = cCan.getContext('2d');
        }
        cCan.width = innerWidth; cCan.height = innerHeight; cCan.style.display='block';
        cParts = [];
        const cols = ['#FF6B6B','#FFE66D','#4ECDC4','#A29BFE','#FD79A8','#6C5CE7','#00B894','#FDCB6E','#E17055','#74B9FF'];
        for (let i=0;i<120;i++) cParts.push({type:'c',x:Math.random()*cCan.width,y:-20-Math.random()*300,vx:(Math.random()-.5)*3,vy:3+Math.random()*5,rot:Math.random()*360,rs:(Math.random()-.5)*9,w:6+Math.random()*8,h:3+Math.random()*5,color:cols[Math.floor(Math.random()*cols.length)],a:1});
        for (let i=0;i<14;i++) cParts.push({type:'b',x:40+Math.random()*(cCan.width-80),y:cCan.height+30+Math.random()*120,vx:(Math.random()-.5)*1.4,vy:-(2+Math.random()*2.8),sw:Math.random()*Math.PI*2,ss:.02+Math.random()*.02,r:18+Math.random()*14,color:cols[Math.floor(Math.random()*cols.length)],a:1});
        for (let b=0;b<5;b++) {
            const bx=80+Math.random()*(cCan.width-160), by=60+Math.random()*(cCan.height*.45), bc=cols[Math.floor(Math.random()*cols.length)];
            setTimeout(() => { for (let p=0;p<32;p++){const ang=(p/32)*Math.PI*2, sp=3+Math.random()*6; cParts.push({type:'s',x:bx,y:by,vx:Math.cos(ang)*sp,vy:Math.sin(ang)*sp,r:3+Math.random()*3,color:bc,a:1,life:1});} }, b*500);
        }
        if (cRAF) cancelAnimationFrame(cRAF);
        animCeleb(); playCheer();
    }
    function animCeleb() {
        if (!cCtx) return;
        const W=cCan.width, H=cCan.height;
        cCtx.clearRect(0,0,W,H);
        cParts.forEach(p => {
            if (p.a <= .01) return;
            cCtx.save(); cCtx.globalAlpha = p.a;
            if (p.type === 'c') {
                p.x+=p.vx; p.y+=p.vy; p.vy+=.09; p.rot+=p.rs;
                if (p.y > H+20) { p.a=0; cCtx.restore(); return; }
                cCtx.translate(p.x,p.y); cCtx.rotate(p.rot*Math.PI/180);
                cCtx.fillStyle=p.color; cCtx.fillRect(-p.w/2,-p.h/2,p.w,p.h);
            } else if (p.type === 'b') {
                p.sw+=p.ss; p.x+=p.vx+Math.sin(p.sw)*.6; p.y+=p.vy;
                if (p.y < -70) { p.a=0; cCtx.restore(); return; }
                cCtx.translate(p.x,p.y);
                cCtx.beginPath(); cCtx.arc(0,0,p.r,0,Math.PI*2); cCtx.fillStyle=p.color; cCtx.fill();
                cCtx.beginPath(); cCtx.arc(-p.r*.3,-p.r*.3,p.r*.27,0,Math.PI*2); cCtx.fillStyle='rgba(255,255,255,.38)'; cCtx.fill();
                cCtx.beginPath(); cCtx.moveTo(0,p.r); cCtx.quadraticCurveTo(5,p.r+14,-3,p.r+28);
                cCtx.strokeStyle='rgba(255,255,255,.55)'; cCtx.lineWidth=1.2; cCtx.stroke();
            } else if (p.type === 's') {
                p.x+=p.vx; p.y+=p.vy; p.vy+=.18; p.life-=.022; p.a=Math.max(0,p.life);
                cCtx.beginPath(); cCtx.arc(p.x,p.y,Math.max(.5,p.r*p.life),0,Math.PI*2);
                cCtx.fillStyle=p.color; cCtx.fill();
            }
            cCtx.restore();
        });
        cRAF = requestAnimationFrame(animCeleb);
    }
    function stopCeleb() {
        if (cRAF) { cancelAnimationFrame(cRAF); cRAF = null; }
        if (cCan) cCan.style.display='none';
        cParts = [];
    }
    function playCheer() {
        try {
            const c = ac(); if (c.state === 'suspended') c.resume();
            const now = c.currentTime;
            const dur=4, sr=c.sampleRate;
            const buf = c.createBuffer(2, sr*dur, sr);
            for (let ch=0; ch<2; ch++) {
                const d = buf.getChannelData(ch);
                for (let i=0; i<d.length; i++) {
                    const t=i/sr, env = t<.5 ? t/.5 : t<3 ? 1 : Math.pow(1-(t-3)/1, 1.5);
                    const mod = .5 + .5 * Math.abs(Math.sin(t*7.5 + ch*.4 + Math.random()*.05));
                    d[i] = (Math.random()*2-1) * mod * env * .3;
                }
            }
            const src = c.createBufferSource(); src.buffer = buf;
            const bp = c.createBiquadFilter(); bp.type='bandpass'; bp.frequency.value=1600; bp.Q.value=.7;
            const gv = c.createGain(); gv.gain.value=2.2;
            src.connect(bp); bp.connect(gv); gv.connect(c.destination);
            src.start(now);
            [[0,523],[280,659],[520,784],[720,880],[880,1047]].forEach(([ms,f]) => {
                const o = c.createOscillator(), g = c.createGain();
                o.type='sine'; o.frequency.value=f;
                const t0 = now + ms/1000;
                g.gain.setValueAtTime(0, t0);
                g.gain.linearRampToValueAtTime(.2, t0+.06);
                g.gain.exponentialRampToValueAtTime(.001, t0+.5);
                o.connect(g); g.connect(c.destination);
                o.start(t0); o.stop(t0+.6);
            });
        } catch(e){}
    }

    /* Toast */
    function showToast(msg) {
        toastEl.textContent = msg;
        toastEl.classList.add('show');
        setTimeout(() => toastEl.classList.remove('show'), 3500);
    }

    /* Sonucu göster */
    function showResult(d, kod) {
        const fullLabel = buildFullLabel(d);
        resText.textContent = fullLabel;
        if (kod) {
            codeEl.textContent = kod;
            codeWrap.style.display = 'block';
        } else {
            codeWrap.style.display = 'none';
        }
        resModal.classList.add('show');
        startCeleb();
    }

    window.closeResult = function() {
        resModal.classList.remove('show');
        stopCeleb();
    };

    /* Çevir — AJAX */
    window.cevirCarki = async function() {
        if (spinning) return;
        spinning = true;
        cevirBtn.disabled = true;
        cevirBtn.textContent = '⏳ Çevriliyor...';
        try { ac().resume(); } catch(e){}

        let data;
        try {
            const resp = await fetch(CEVIR_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ salon_id: SALON_ID }),
            });
            data = await resp.json();
        } catch(e) {
            showToast('Bağlantı hatası');
            spinning = false; cevirBtn.disabled = false; cevirBtn.textContent = '🎲 Çarkı Çevir';
            return;
        }

        if (!data.success) {
            showToast(data.message || 'Çevirme başarısız');
            spinning = false; cevirBtn.disabled = false; cevirBtn.textContent = '🎲 Çarkı Çevir';
            return;
        }

        const n = DILIMLER.length;
        const ang = 360 / n;
        const idx = data.dilimIndex;
        const jitter = (Math.random() - 0.5) * ang * 0.6;
        const stopAt = (idx + 0.5) * ang + jitter;
        const offset = ((360 - stopAt) % 360 + 360) % 360;
        const nSpins = (12 + Math.floor(Math.random() * 5)) * 360;
        const curMod = ((currentRot % 360) + 360) % 360;
        let diff = offset - curMod;
        if (diff < 0) diff += 360;
        currentRot += nSpins + diff;

        wheelEl.style.transition = 'transform 9s cubic-bezier(0.17, 0.67, 0.12, 0.99)';
        wheelEl.style.transform = `rotate(${currentRot}deg)`;
        startTickLoop();

        setTimeout(() => {
            spinning = false;
            hakEl.textContent = data.kalanHak;
            if (data.kalanHak > 0) {
                cevirBtn.disabled = false;
                cevirBtn.textContent = '🎲 Çarkı Çevir';
            } else {
                cevirBtn.textContent = 'Hakkınız Bitti';
            }
            showResult(data.dilim, data.odulKodu);
        }, 9200);
    };

    renderWheel();

    resModal.addEventListener('click', e => { if (e.target === resModal) closeResult(); });
})();
</script>
@endsection
