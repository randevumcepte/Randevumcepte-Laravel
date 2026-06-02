@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

@php
    $subeQuery = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
    $apiSubeParam = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/countUp.js/2.8.0/countUp.umd.min.js" defer></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
  --rx-bg:#eef1f7;
  --rx-surface:#ffffff;
  --rx-ink:#101426;
  --rx-ink-2:#3a4262;
  --rx-mute:#7a8299;
  --rx-faint:#b6bccd;
  --rx-line:#e6e9f1;
  --rx-line-2:#dfe3ed;
  --rx-primary:#5C008E;
  --rx-primary-soft:#f3eafa;
  --rx-emerald:#069471;
  --rx-emerald-soft:#dcf6ec;
  --rx-amber:#c47214;
  --rx-amber-soft:#fdeed4;
  --rx-rose:#c83361;
  --rx-rose-soft:#ffe3ec;
  --rx-violet:#9D5DC8;
  --rx-violet-soft:#ede0f7;
  --rx-fuchsia:#d946ef;
  --rx-cyan:#0f7e96;
  --rx-cyan-soft:#d9f1f6;
  --rx-r-sm:10px;
  --rx-r-md:14px;
  --rx-r-lg:20px;
}

.rxw{padding:4px 2px 50px 2px;color:var(--rx-ink);}

/* ============== HERO BAR ============== */
.rxhero{
  position:relative;
  background:linear-gradient(135deg,#3a0061 0%,#5C008E 55%,#9D5DC8 100%);
  color:#fff;border-radius:var(--rx-r-lg);
  padding:22px 26px 18px 26px;margin-bottom:18px;
  box-shadow:0 14px 40px -18px rgba(92,0,142,.5);
  overflow:hidden;
}
.rxhero::before{
  content:'';position:absolute;right:-80px;top:-60px;width:280px;height:280px;border-radius:50%;
  background:radial-gradient(circle,rgba(255,255,255,.08) 0%,rgba(255,255,255,0) 70%);
  pointer-events:none;
}
.rxhero::after{
  content:'';position:absolute;left:40%;bottom:-100px;width:240px;height:240px;border-radius:50%;
  background:radial-gradient(circle,rgba(217,70,239,.42) 0%,rgba(217,70,239,0) 70%);
  pointer-events:none;
}
.rxhero-row{position:relative;display:flex;align-items:flex-start;justify-content:space-between;gap:24px;flex-wrap:wrap;}
.rxhero-lead .crumb{font-size:11.5px;letter-spacing:1.6px;text-transform:uppercase;color:#e0c5f3;font-weight:700;margin-bottom:6px;}
.rxhero-lead h1{font-size:24px;font-weight:800;letter-spacing:-.4px;margin:0 0 4px 0;}
.rxhero-lead p{font-size:13px;color:#e6cef5;margin:0;max-width:520px;}

.rxhero-kpi{
  display:flex;align-items:stretch;background:rgba(255,255,255,.07);
  border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:14px 20px;
  backdrop-filter:blur(6px);min-width:280px;
}
.rxhero-kpi .label{font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:#e0c5f3;font-weight:700;}
.rxhero-kpi .value{font-size:30px;font-weight:800;letter-spacing:-.4px;line-height:1.1;margin-top:4px;}
.rxhero-kpi .value small{font-size:14px;color:#e0c5f3;font-weight:700;margin-left:6px;}
.rxhero-kpi .pulse{width:8px;height:8px;border-radius:50%;background:#27e0a4;box-shadow:0 0 0 0 rgba(39,224,164,.7);animation:rxPulse 1.8s infinite;margin:0 12px 0 0;align-self:flex-start;margin-top:8px;}
@keyframes rxPulse{0%{box-shadow:0 0 0 0 rgba(39,224,164,.7);}70%{box-shadow:0 0 0 12px rgba(39,224,164,0);}100%{box-shadow:0 0 0 0 rgba(39,224,164,0);}}

/* ============== TOOLBAR ============== */
.rxbar{
  display:flex;align-items:center;gap:14px;flex-wrap:wrap;
  background:var(--rx-surface);border:1px solid var(--rx-line);
  border-radius:var(--rx-r-md);padding:10px 14px;margin-bottom:18px;
  box-shadow:0 1px 0 rgba(16,20,38,.02);
}
.rxseg{display:inline-flex;background:#f3f5fb;border:1px solid var(--rx-line);border-radius:10px;padding:3px;}
.rxseg button{
  border:0;background:transparent;font-weight:600;font-size:12.5px;color:var(--rx-ink-2);
  padding:7px 14px;border-radius:7px;cursor:pointer;transition:all .15s;
  display:inline-flex;align-items:center;gap:6px;
}
.rxseg button:hover{color:var(--rx-ink);}
.rxseg button.on{background:var(--rx-primary);color:#fff;box-shadow:0 2px 6px rgba(92,0,142,.28);}
.rxdate{display:inline-flex;align-items:center;gap:6px;background:#fafbff;border:1px solid var(--rx-line);border-radius:10px;padding:5px 10px;font-size:12.5px;color:var(--rx-ink-2);}
.rxdate i{color:var(--rx-mute);}
.rxdate input{border:0;background:transparent;outline:none;font-size:12.5px;color:var(--rx-ink);font-weight:600;width:108px;font-family:inherit;}
.rxdate .sep{color:var(--rx-faint);font-weight:700;}
.rxbar-spacer{flex:1;}
.rxbar-meta{font-size:11.5px;color:var(--rx-mute);font-weight:600;letter-spacing:.4px;}
.rxbar-meta b{color:var(--rx-ink);font-weight:800;}

/* ============== NAV TABS ============== */
.rxnav{
  display:flex;gap:6px;background:var(--rx-surface);border:1px solid var(--rx-line);
  border-radius:var(--rx-r-md);padding:6px;margin-bottom:18px;overflow-x:auto;flex-wrap:nowrap;
}
.rxnav button{
  flex:1;min-width:120px;background:transparent;border:0;cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:11px 14px;border-radius:10px;
  font-size:13px;font-weight:600;color:var(--rx-ink-2);
  transition:all .18s;white-space:nowrap;
}
.rxnav button i{font-size:15px;color:var(--rx-mute);transition:color .18s;}
.rxnav button:hover{background:#f3f5fb;color:var(--rx-ink);}
.rxnav button.on{background:linear-gradient(135deg,var(--rx-primary),var(--rx-violet));color:#fff;box-shadow:0 4px 12px -4px rgba(92,0,142,.45);}
.rxnav button.on i{color:#fff;}

/* ============== STAT TILES ============== */
.rxgrid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
@media (max-width:1199px){.rxgrid-4{grid-template-columns:repeat(2,1fr);}}
@media (max-width:599px){.rxgrid-4{grid-template-columns:1fr;}}

.rxtile{
  background:var(--rx-surface);border:1px solid var(--rx-line);
  border-radius:var(--rx-r-md);padding:16px 18px;position:relative;
  opacity:0;animation:rxIn .5s ease forwards;
  transition:transform .2s,box-shadow .2s,border-color .2s;
}
.rxtile.d1{animation-delay:.04s}.rxtile.d2{animation-delay:.10s}
.rxtile.d3{animation-delay:.16s}.rxtile.d4{animation-delay:.22s}
.rxtile:hover{transform:translateY(-2px);box-shadow:0 10px 24px -16px rgba(16,20,38,.18);border-color:var(--rx-line-2);}
.rxtile .head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.rxtile .label{font-size:11.5px;font-weight:700;color:var(--rx-mute);letter-spacing:.6px;text-transform:uppercase;}
.rxtile .ico{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;}
.rxtile .num{font-size:26px;font-weight:800;color:var(--rx-ink);letter-spacing:-.4px;line-height:1;}
.rxtile .sub{font-size:11.5px;color:var(--rx-mute);font-weight:600;margin-top:6px;display:flex;align-items:center;gap:5px;}
.rxtile .bar-mini{height:3px;background:#f0f2f8;border-radius:2px;overflow:hidden;margin-top:12px;}
.rxtile .bar-mini span{display:block;height:100%;border-radius:2px;width:0%;transition:width 1s cubic-bezier(.16,1,.3,1);}

/* Renk tonları (kart kenarında değil ikonunda, alt çubukta) */
.rxtile.t-pri .ico{background:var(--rx-primary-soft);color:var(--rx-primary);}
.rxtile.t-pri .bar-mini span{background:var(--rx-primary);}
.rxtile.t-em .ico{background:var(--rx-emerald-soft);color:var(--rx-emerald);}
.rxtile.t-em .bar-mini span{background:var(--rx-emerald);}
.rxtile.t-am .ico{background:var(--rx-amber-soft);color:var(--rx-amber);}
.rxtile.t-am .bar-mini span{background:var(--rx-amber);}
.rxtile.t-ro .ico{background:var(--rx-rose-soft);color:var(--rx-rose);}
.rxtile.t-ro .bar-mini span{background:var(--rx-rose);}
.rxtile.t-vi .ico{background:var(--rx-violet-soft);color:var(--rx-violet);}
.rxtile.t-vi .bar-mini span{background:var(--rx-violet);}
.rxtile.t-cy .ico{background:var(--rx-cyan-soft);color:var(--rx-cyan);}
.rxtile.t-cy .bar-mini span{background:var(--rx-cyan);}

/* ============== PANEL ============== */
.rxpanel{
  background:var(--rx-surface);border:1px solid var(--rx-line);
  border-radius:var(--rx-r-md);padding:18px 20px;margin-bottom:18px;
  opacity:0;animation:rxIn .5s ease forwards;animation-delay:.28s;
}
.rxpanel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:10px;}
.rxpanel-title{font-size:14px;font-weight:700;color:var(--rx-ink);display:flex;align-items:center;gap:10px;letter-spacing:-.1px;}
.rxpanel-title .dot{width:6px;height:6px;border-radius:50%;background:var(--rx-primary);box-shadow:0 0 0 4px var(--rx-primary-soft);}
.rxpanel-title.em .dot{background:var(--rx-emerald);box-shadow:0 0 0 4px var(--rx-emerald-soft);}
.rxpanel-title.am .dot{background:var(--rx-amber);box-shadow:0 0 0 4px var(--rx-amber-soft);}
.rxpanel-title.ro .dot{background:var(--rx-rose);box-shadow:0 0 0 4px var(--rx-rose-soft);}
.rxpanel-title.vi .dot{background:var(--rx-violet);box-shadow:0 0 0 4px var(--rx-violet-soft);}
.rxpanel-title.cy .dot{background:var(--rx-cyan);box-shadow:0 0 0 4px var(--rx-cyan-soft);}
.rxpanel-sub{font-size:12px;color:var(--rx-mute);font-weight:600;}

/* Gelir paneli body */
.rxrev{display:grid;grid-template-columns:280px 1fr;gap:30px;align-items:center;min-height:240px;}
@media (max-width:991px){.rxrev{grid-template-columns:1fr;}}
.rxrev-chart{position:relative;height:240px;}
.rxrev-empty{text-align:center;color:var(--rx-faint);font-size:13px;padding:60px 0;}
.rxrev-empty i{font-size:36px;display:block;margin-bottom:8px;color:#dfe3ed;}
.rxrev-split{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
@media (max-width:599px){.rxrev-split{grid-template-columns:1fr;}}
.rxrev-cell{
  border:1px solid var(--rx-line);border-radius:12px;padding:14px 16px;background:#fbfcfe;
  display:flex;align-items:center;gap:12px;
}
.rxrev-cell .swatch{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.rxrev-cell .lbl{font-size:11.5px;color:var(--rx-mute);font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
.rxrev-cell .val{font-size:16px;font-weight:800;color:var(--rx-ink);margin-top:2px;}
.rxrev-cell.c1 .swatch{background:var(--rx-primary-soft);color:var(--rx-primary);}
.rxrev-cell.c2 .swatch{background:var(--rx-emerald-soft);color:var(--rx-emerald);}
.rxrev-cell.c3 .swatch{background:var(--rx-rose-soft);color:var(--rx-rose);}
.rxrev-cell.c4 .swatch{background:var(--rx-amber-soft);color:var(--rx-amber);}
.rxrev-cta{display:inline-flex;align-items:center;gap:6px;background:transparent;border:1px solid var(--rx-primary);
  color:var(--rx-primary);font-weight:700;font-size:12.5px;padding:8px 14px;border-radius:9px;text-decoration:none;transition:all .18s;}
.rxrev-cta:hover{background:var(--rx-primary);color:#fff;text-decoration:none;border-color:var(--rx-primary);}

/* Tablolar */
.rxtbl{width:100%;border-collapse:collapse;}
.rxtbl th{font-size:10.5px;color:var(--rx-mute);font-weight:700;letter-spacing:.7px;text-transform:uppercase;
  text-align:left;padding:10px 12px;border-bottom:1px solid var(--rx-line);background:#fafbfe;}
.rxtbl th:first-child{border-top-left-radius:8px;}
.rxtbl th:last-child{border-top-right-radius:8px;}
.rxtbl td{padding:11px 12px;font-size:13px;color:var(--rx-ink);border-bottom:1px solid var(--rx-line);}
.rxtbl tbody tr{transition:background .12s;}
.rxtbl tbody tr:hover{background:#fafbfe;}
.rxtbl tbody tr:last-child td{border-bottom:0;}
.rxtbl td.num,.rxtbl th.num{text-align:right;font-variant-numeric:tabular-nums;}
.rxtbl .rank{width:36px;}
.rxtbl .rank .rb{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:7px;
  background:#f0f2f8;color:var(--rx-ink-2);font-size:11px;font-weight:800;}
.rxtbl tbody tr:nth-child(1) .rb{background:var(--rx-amber-soft);color:var(--rx-amber);}
.rxtbl tbody tr:nth-child(2) .rb{background:#e8eaf0;color:#65719a;}
.rxtbl tbody tr:nth-child(3) .rb{background:#fce7d4;color:#a04e0b;}

/* Personel Leaderboard */
.rxleader{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;}
@media (max-width:767px){.rxleader{grid-template-columns:1fr;}}
.rxleader-card{
  background:var(--rx-surface);border:1px solid var(--rx-line);border-radius:var(--rx-r-md);
  padding:18px;display:flex;align-items:center;gap:14px;position:relative;overflow:hidden;
  opacity:0;animation:rxIn .5s ease forwards;
}
.rxleader-card.r2{animation-delay:.06s}.rxleader-card.r3{animation-delay:.12s}
.rxleader-card .badge{
  width:48px;height:48px;border-radius:14px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;
  background:#f0f2f8;color:var(--rx-ink-2);
}
.rxleader-card.r1 .badge{background:linear-gradient(135deg,#fdeed4,#f7d59b);color:#a06317;
  box-shadow:0 6px 18px -8px rgba(196,114,20,.5);}
.rxleader-card.r1{border-color:#f3d8a6;background:linear-gradient(180deg,#fffaf0 0%,#ffffff 60%);}
.rxleader-card.r2 .badge{background:linear-gradient(135deg,#e8eaf0,#c8cee0);color:#414b6e;}
.rxleader-card.r3 .badge{background:linear-gradient(135deg,#fce7d4,#f4c189);color:#7a3e09;}
.rxleader-card .info{flex:1;min-width:0;}
.rxleader-card .ad{font-size:15px;font-weight:700;color:var(--rx-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rxleader-card .ciro{font-size:20px;font-weight:800;color:var(--rx-emerald);margin-top:4px;letter-spacing:-.3px;}
.rxleader-card .stats{font-size:11px;color:var(--rx-mute);margin-top:4px;font-weight:600;}
.rxleader-card .stats b{color:var(--rx-ink-2);font-weight:700;}

/* Grid */
.rxgrid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
@media (max-width:991px){.rxgrid-2{grid-template-columns:1fr;}}

/* Chart kutuları */
.rxchart{position:relative;height:300px;}
.rxchart.tall{height:340px;}

/* Cinsiyet bar (yatay segment) */
.rxgender{display:flex;flex-direction:column;gap:14px;}
.rxgender-bar{display:flex;height:14px;border-radius:7px;overflow:hidden;background:#f0f2f8;margin-bottom:6px;}
.rxgender-bar span{height:100%;}
.rxgender-bar .k{background:var(--rx-rose);}
.rxgender-bar .e{background:var(--rx-primary);}
.rxgender-bar .b{background:#9aa3bd;}
.rxgender-leg{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.rxgender-leg div{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--rx-ink-2);font-weight:600;}
.rxgender-leg .sw{width:10px;height:10px;border-radius:3px;}
.rxgender-leg .v{margin-left:auto;font-weight:800;color:var(--rx-ink);}

/* Kaynak liste */
.rxsource{display:flex;flex-direction:column;gap:10px;}
.rxsource-row{
  display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;
  background:#fbfcfe;border:1px solid var(--rx-line);
}
.rxsource-row .ico{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.rxsource-row .lbl{font-size:13px;font-weight:700;color:var(--rx-ink);}
.rxsource-row .pct{font-size:11px;color:var(--rx-mute);font-weight:600;margin-top:2px;}
.rxsource-row .v{margin-left:auto;font-size:18px;font-weight:800;color:var(--rx-ink);letter-spacing:-.3px;}
.rxsource-row.s1 .ico{background:var(--rx-primary-soft);color:var(--rx-primary);}
.rxsource-row.s2 .ico{background:var(--rx-emerald-soft);color:var(--rx-emerald);}
.rxsource-row.s3 .ico{background:var(--rx-violet-soft);color:var(--rx-violet);}

/* Empty */
.rxempty{padding:42px 16px;text-align:center;color:var(--rx-faint);font-size:13px;}
.rxempty i{font-size:38px;display:block;margin-bottom:10px;color:#dfe3ed;}

@keyframes rxIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
</style>

<div class="rxw">

  {{-- HERO --}}
  <div class="rxhero">
    <div class="rxhero-row">
      <div class="rxhero-lead">
        <div class="crumb">İşletme Yönetim · Analiz</div>
        <h1>Performans Panosu</h1>
        <p>Dönemsel ciro, hizmet–ürün satışları, personel ve müşteri davranışlarınızı tek panoda inceleyin.</p>
      </div>
      <div class="rxhero-kpi">
        <div class="pulse"></div>
        <div>
          <div class="label">Seçili Dönem Cirosu</div>
          <div class="value"><span id="rep-toplam-gelir">0,00</span><small>₺</small></div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOOLBAR --}}
  <div class="rxbar">
    <div class="rxseg" id="rx-period">
      <button data-period="bugun" class="on"><i class="bi bi-sun"></i>Bugün</button>
      <button data-period="hafta"><i class="bi bi-calendar3"></i>Bu Hafta</button>
      <button data-period="ay"><i class="bi bi-calendar3-range"></i>Bu Ay</button>
    </div>
    <div class="rxdate">
      <i class="bi bi-calendar-event"></i>
      <input type="date" id="rep-bas" value="{{ date('Y-m-d') }}">
      <span class="sep">→</span>
      <input type="date" id="rep-bit" value="{{ date('Y-m-d') }}">
    </div>
    <div class="rxbar-spacer"></div>
    <div class="rxbar-meta">Aktif rapor · <b id="rx-current-label">İşletme</b></div>
  </div>

  {{-- NAV --}}
  <div class="rxnav" id="rx-nav">
    <button class="on" data-section="isletme" data-label="İşletme"><i class="bi bi-building"></i>İşletme</button>
    <button data-section="hizmet" data-label="Hizmet"><i class="bi bi-scissors"></i>Hizmet</button>
    <button data-section="urun" data-label="Ürün"><i class="bi bi-box-seam"></i>Ürün</button>
    <button data-section="personel" data-label="Personel"><i class="bi bi-people"></i>Personel</button>
    <button data-section="musteri" data-label="Müşteri"><i class="bi bi-person-heart"></i>Müşteri</button>
    <button data-section="randevu" data-label="Randevu"><i class="bi bi-calendar-check"></i>Randevu</button>
  </div>

  <div id="rep-content"></div>

</div>

<script>
function repInit(){
  var apiBase = '/isletmeyonetim/api/isletmeraporlari';
  var subeParam = @json($apiSubeParam);
  var subeQuery = @json($subeQuery);

  function api(path, extra){
    var url = apiBase + path + subeParam;
    if(extra) url += (subeParam ? '&' : '?') + extra;
    return fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); });
  }
  function trMoney(n){ n = Number(n)||0; return n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}); }
  function trInt(n){ n = Number(n)||0; return n.toLocaleString('tr-TR'); }
  function animCount(el, to, decimals){
    if(!el) return;
    try{
      var c = new countUp.CountUp(el, to, {duration:1.0, separator:'.', decimal:',', decimalPlaces: decimals || 0});
      if(!c.error){ c.start(); return; }
    }catch(e){}
    el.textContent = (decimals ? Number(to).toFixed(decimals).replace('.', ',') : trInt(to));
  }
  function escape(s){ return (s==null?'':String(s)).replace(/[<>&"]/g,function(c){return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c];}); }
  function currentPeriodQS(){
    var bas = document.getElementById('rep-bas').value;
    var bit = document.getElementById('rep-bit').value;
    var activeChip = document.querySelector('#rx-period button.on');
    var period = activeChip ? activeChip.getAttribute('data-period') : 'custom';
    if(period === 'custom' || (!activeChip && bas && bit)){
      return 'period=custom&bas='+bas+'&bit='+bit;
    }
    return 'period='+period;
  }

  var charts = {};
  function killCharts(){
    Object.keys(charts).forEach(function(k){ try{ charts[k].destroy(); }catch(e){} });
    charts = {};
  }

  // ======================== İŞLETME ========================
  function renderIsletme(){
    var html = ''
      + '<div class="rxgrid-4">'
      +   '<div class="rxtile t-pri d1"><div class="head"><div class="label">Randevu</div><div class="ico"><i class="bi bi-calendar2-check"></i></div></div>'
      +     '<div class="num" id="r-i-rnd">0</div><div class="sub">Toplam randevu sayısı</div><div class="bar-mini"><span style="width:90%"></span></div></div>'
      +   '<div class="rxtile t-em d2"><div class="head"><div class="label">Adisyon</div><div class="ico"><i class="bi bi-receipt"></i></div></div>'
      +     '<div class="num" id="r-i-adi">0</div><div class="sub">Açılan adisyon</div><div class="bar-mini"><span style="width:75%"></span></div></div>'
      +   '<div class="rxtile t-ro d3"><div class="head"><div class="label">Ürün</div><div class="ico"><i class="bi bi-bag-check"></i></div></div>'
      +     '<div class="num" id="r-i-urn">0</div><div class="sub">Satılan ürün adedi</div><div class="bar-mini"><span style="width:60%"></span></div></div>'
      +   '<div class="rxtile t-am d4"><div class="head"><div class="label">Hizmet</div><div class="ico"><i class="bi bi-scissors"></i></div></div>'
      +     '<div class="num" id="r-i-hzm">0</div><div class="sub">Uygulanan hizmet</div><div class="bar-mini"><span style="width:80%"></span></div></div>'
      + '</div>'
      + '<div class="rxpanel">'
      +   '<div class="rxpanel-head">'
      +     '<div class="rxpanel-title em"><span class="dot"></span>Ciro Dağılımı</div>'
      +     '<a class="rxrev-cta" href="/isletmeyonetim/raporlar'+subeQuery+'">Detay raporlar <i class="bi bi-arrow-right"></i></a>'
      +   '</div>'
      +   '<div class="rxrev">'
      +     '<div class="rxrev-chart" id="r-i-chartbox"><canvas id="r-i-chart"></canvas></div>'
      +     '<div class="rxrev-split">'
      +       '<div class="rxrev-cell c1"><div class="swatch"><i class="bi bi-cash"></i></div><div><div class="lbl">Nakit</div><div class="val"><span id="r-i-nakit">0,00</span> ₺</div></div></div>'
      +       '<div class="rxrev-cell c2"><div class="swatch"><i class="bi bi-credit-card"></i></div><div><div class="lbl">Kart</div><div class="val"><span id="r-i-kart">0,00</span> ₺</div></div></div>'
      +       '<div class="rxrev-cell c3"><div class="swatch"><i class="bi bi-bank"></i></div><div><div class="lbl">Havale/EFT</div><div class="val"><span id="r-i-havale">0,00</span> ₺</div></div></div>'
      +       '<div class="rxrev-cell c4"><div class="swatch"><i class="bi bi-wallet2"></i></div><div><div class="lbl">Ödenen Gelir</div><div class="val"><span id="r-i-odenen">0,00</span> ₺</div></div></div>'
      +     '</div>'
      +   '</div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/ozet', currentPeriodQS()).then(function(d){
      animCount(document.getElementById('r-i-rnd'), d.toplam_randevu||0);
      animCount(document.getElementById('r-i-adi'), d.toplam_adisyon||0);
      animCount(document.getElementById('r-i-urn'), d.satilan_urun||0);
      animCount(document.getElementById('r-i-hzm'), d.uygulanan_hizmet||0);
      animCount(document.getElementById('rep-toplam-gelir'), d.toplam_gelir||0, 2);
      animCount(document.getElementById('r-i-nakit'), d.nakit||0, 2);
      animCount(document.getElementById('r-i-kart'), d.kart||0, 2);
      animCount(document.getElementById('r-i-havale'), d.havale||0, 2);
      animCount(document.getElementById('r-i-odenen'), d.odenen||0, 2);
      if(d.tarih_bas) document.getElementById('rep-bas').value = d.tarih_bas;
      if(d.tarih_bit) document.getElementById('rep-bit').value = d.tarih_bit;

      var box = document.getElementById('r-i-chartbox');
      if((d.toplam_gelir||0) <= 0){
        box.innerHTML = '<div class="rxrev-empty"><i class="bi bi-pie-chart"></i>Veri yok, grafik gizlendi.</div>';
        return;
      }
      var ctx = document.getElementById('r-i-chart').getContext('2d');
      charts.gelir = new Chart(ctx, {
        type:'doughnut',
        data:{labels:['Nakit','Kart','Havale/EFT','Diğer'],
              datasets:[{data:[d.nakit||0,d.kart||0,d.havale||0,d.diger||0],
                         backgroundColor:['#5C008E','#069471','#c83361','#9D5DC8'],borderWidth:0,hoverOffset:6}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
          plugins:{legend:{position:'bottom',labels:{font:{size:11,weight:'600'},color:'#3a4262',padding:8,usePointStyle:true,pointStyle:'circle',boxWidth:8}},
            tooltip:{callbacks:{label:function(ctx){return ctx.label+': '+trMoney(ctx.parsed)+' ₺';}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });
    });
  }

  // ======================== HİZMET ========================
  function renderHizmet(){
    var html = '<div class="rxgrid-4">'
      + '<div class="rxtile t-em d1"><div class="head"><div class="label">Uygulanan</div><div class="ico"><i class="bi bi-scissors"></i></div></div><div class="num" id="r-h-adet">0</div><div class="sub">Toplam adet</div><div class="bar-mini"><span style="width:85%"></span></div></div>'
      + '<div class="rxtile t-pri d2"><div class="head"><div class="label">Ciro</div><div class="ico"><i class="bi bi-currency-exchange"></i></div></div><div class="num"><span id="r-h-ciro">0,00</span> ₺</div><div class="sub">Hizmet cirosu</div><div class="bar-mini"><span style="width:78%"></span></div></div>'
      + '<div class="rxtile t-vi d3"><div class="head"><div class="label">Ortalama</div><div class="ico"><i class="bi bi-tag"></i></div></div><div class="num"><span id="r-h-ort">0,00</span> ₺</div><div class="sub">Birim fiyat</div><div class="bar-mini"><span style="width:55%"></span></div></div>'
      + '<div class="rxtile t-am d4"><div class="head"><div class="label">Çeşit</div><div class="ico"><i class="bi bi-grid"></i></div></div><div class="num" id="r-h-cesit">0</div><div class="sub">Farklı hizmet</div><div class="bar-mini"><span style="width:65%"></span></div></div>'
      + '</div>'
      + '<div class="rxgrid-2">'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title em"><span class="dot"></span>En Çok Yapılanlar</div><div class="rxpanel-sub">İlk 10</div></div><div class="rxchart" id="r-h-chartbox"><canvas id="r-h-chart"></canvas></div></div>'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title vi"><span class="dot"></span>Detaylı Liste</div></div><div id="r-h-tbl"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/hizmet', currentPeriodQS()).then(function(d){
      var list = d.hizmetler || [];
      animCount(document.getElementById('r-h-adet'), d.toplam_adet||0);
      animCount(document.getElementById('r-h-ciro'), d.toplam_ciro||0, 2);
      animCount(document.getElementById('r-h-ort'), d.ortalama_fiyat||0, 2);
      animCount(document.getElementById('r-h-cesit'), list.length);

      if(list.length === 0){
        document.getElementById('r-h-chartbox').innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Bu dönemde hizmet uygulanmamış.</div>';
        document.getElementById('r-h-tbl').innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        return;
      }
      var top10 = list.slice(0, 10);
      var ctx = document.getElementById('r-h-chart').getContext('2d');
      charts.hizmet = new Chart(ctx, {
        type:'bar',
        data:{labels:top10.map(function(h){return h.hizmet_adi;}),
              datasets:[{label:'Adet',data:top10.map(function(h){return h.adet;}),
                         backgroundColor:'rgba(6,148,113,.85)',borderRadius:6,maxBarThickness:22}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{
            label:function(c){return c.parsed.x+' adet';},
            afterLabel:function(c){return 'Ciro: '+trMoney(top10[c.dataIndex].ciro)+' ₺';}
          }}},
          scales:{x:{beginAtZero:true,grid:{color:'#f0f2f8'},ticks:{font:{size:10}}},y:{grid:{display:false},ticks:{font:{size:11}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var thtml = '<table class="rxtbl"><thead><tr><th class="rank">#</th><th>Hizmet</th><th class="num">Adet</th><th class="num">Ciro</th></tr></thead><tbody>';
      list.forEach(function(h,i){
        thtml += '<tr><td class="rank"><span class="rb">'+(i+1)+'</span></td><td>'+escape(h.hizmet_adi)+'</td><td class="num">'+trInt(h.adet)+'</td><td class="num"><b>'+trMoney(h.ciro)+' ₺</b></td></tr>';
      });
      thtml += '</tbody></table>';
      document.getElementById('r-h-tbl').innerHTML = thtml;
    });
  }

  // ======================== ÜRÜN ========================
  function renderUrun(){
    var html = '<div class="rxgrid-4">'
      + '<div class="rxtile t-ro d1"><div class="head"><div class="label">Satış</div><div class="ico"><i class="bi bi-bag-check"></i></div></div><div class="num" id="r-u-adet">0</div><div class="sub">Satılan adet</div><div class="bar-mini"><span style="width:70%"></span></div></div>'
      + '<div class="rxtile t-pri d2"><div class="head"><div class="label">Ciro</div><div class="ico"><i class="bi bi-currency-exchange"></i></div></div><div class="num"><span id="r-u-ciro">0,00</span> ₺</div><div class="sub">Ürün cirosu</div><div class="bar-mini"><span style="width:72%"></span></div></div>'
      + '<div class="rxtile t-vi d3"><div class="head"><div class="label">Ortalama</div><div class="ico"><i class="bi bi-tag"></i></div></div><div class="num"><span id="r-u-ort">0,00</span> ₺</div><div class="sub">Birim fiyat</div><div class="bar-mini"><span style="width:50%"></span></div></div>'
      + '<div class="rxtile t-em d4"><div class="head"><div class="label">Çeşit</div><div class="ico"><i class="bi bi-box-seam"></i></div></div><div class="num" id="r-u-cesit">0</div><div class="sub">Farklı ürün</div><div class="bar-mini"><span style="width:62%"></span></div></div>'
      + '</div>'
      + '<div class="rxgrid-2">'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title ro"><span class="dot"></span>En Çok Satanlar</div><div class="rxpanel-sub">İlk 10</div></div><div class="rxchart" id="r-u-chartbox"><canvas id="r-u-chart"></canvas></div></div>'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title vi"><span class="dot"></span>Detaylı Liste</div></div><div id="r-u-tbl"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/urun', currentPeriodQS()).then(function(d){
      var list = d.urunler || [];
      animCount(document.getElementById('r-u-adet'), d.toplam_adet||0);
      animCount(document.getElementById('r-u-ciro'), d.toplam_ciro||0, 2);
      animCount(document.getElementById('r-u-ort'), d.ortalama_fiyat||0, 2);
      animCount(document.getElementById('r-u-cesit'), list.length);

      if(list.length === 0){
        document.getElementById('r-u-chartbox').innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Bu dönemde ürün satışı yok.</div>';
        document.getElementById('r-u-tbl').innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        return;
      }
      var top10 = list.slice(0,10);
      var ctx = document.getElementById('r-u-chart').getContext('2d');
      charts.urun = new Chart(ctx, {
        type:'bar',
        data:{labels:top10.map(function(u){return u.urun_adi;}),
              datasets:[{label:'Adet',data:top10.map(function(u){return u.adet;}),
                         backgroundColor:'rgba(200,51,97,.85)',borderRadius:6,maxBarThickness:22}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{
            label:function(c){return c.parsed.x+' adet';},
            afterLabel:function(c){return 'Ciro: '+trMoney(top10[c.dataIndex].ciro)+' ₺';}
          }}},
          scales:{x:{beginAtZero:true,grid:{color:'#f0f2f8'}},y:{grid:{display:false},ticks:{font:{size:11}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var thtml = '<table class="rxtbl"><thead><tr><th class="rank">#</th><th>Ürün</th><th class="num">Adet</th><th class="num">Ciro</th></tr></thead><tbody>';
      list.forEach(function(u,i){
        thtml += '<tr><td class="rank"><span class="rb">'+(i+1)+'</span></td><td>'+escape(u.urun_adi)+'</td><td class="num">'+trInt(u.adet)+'</td><td class="num"><b>'+trMoney(u.ciro)+' ₺</b></td></tr>';
      });
      thtml += '</tbody></table>';
      document.getElementById('r-u-tbl').innerHTML = thtml;
    });
  }

  // ======================== PERSONEL ========================
  function renderPersonel(){
    var html = '<div id="r-p-leader"></div>'
      + '<div class="rxgrid-2">'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title vi"><span class="dot"></span>Personel Cirosu</div></div><div class="rxchart" id="r-p-chartbox"><canvas id="r-p-chart"></canvas></div></div>'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title em"><span class="dot"></span>Detaylı Liste</div></div><div id="r-p-tbl"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/personel', currentPeriodQS()).then(function(d){
      var list = (d.personeller || []).filter(function(p){ return p.randevu_say>0 || p.hizmet_say>0 || p.ciro>0; });
      animCount(document.getElementById('rep-toplam-gelir'), list.reduce(function(s,p){return s+(p.ciro||0);},0), 2);

      var leaderHtml = '';
      if(list.length === 0){
        leaderHtml = '<div class="rxpanel"><div class="rxempty"><i class="bi bi-people"></i>Bu dönemde personel aktivitesi yok.</div></div>';
      } else {
        var top3 = list.slice(0,3);
        var classes = ['r1','r2','r3'];
        leaderHtml = '<div class="rxleader">';
        top3.forEach(function(p,i){
          leaderHtml += '<div class="rxleader-card '+classes[i]+'">'
            + '<div class="badge">'+(i+1)+'</div>'
            + '<div class="info">'
            +   '<div class="ad">'+escape(p.personel_adi)+'</div>'
            +   '<div class="ciro">'+trMoney(p.ciro)+' ₺</div>'
            +   '<div class="stats"><b>'+trInt(p.randevu_say)+'</b> randevu · <b>'+trInt(p.hizmet_say)+'</b> hizmet</div>'
            + '</div></div>';
        });
        leaderHtml += '</div>';
      }
      document.getElementById('r-p-leader').innerHTML = leaderHtml;

      if(list.length === 0){
        document.getElementById('r-p-chartbox').innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        document.getElementById('r-p-tbl').innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        return;
      }
      var ctx = document.getElementById('r-p-chart').getContext('2d');
      var labels = list.map(function(p){return p.personel_adi;});
      var ciros = list.map(function(p){return p.ciro;});
      charts.personel = new Chart(ctx, {
        type:'bar',
        data:{labels:labels,datasets:[{label:'Ciro (₺)',data:ciros,backgroundColor:'rgba(92,0,142,.85)',borderRadius:6,maxBarThickness:32}]},
        options:{responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return trMoney(c.parsed.y)+' ₺';}}}},
          scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#f0f2f8'},ticks:{callback:function(v){return trMoney(v)+' ₺';}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var thtml = '<table class="rxtbl"><thead><tr><th class="rank">#</th><th>Personel</th><th class="num">Randevu</th><th class="num">Hizmet</th><th class="num">Ciro</th></tr></thead><tbody>';
      list.forEach(function(p,i){
        thtml += '<tr><td class="rank"><span class="rb">'+(i+1)+'</span></td><td>'+escape(p.personel_adi)+'</td><td class="num">'+trInt(p.randevu_say)+'</td><td class="num">'+trInt(p.hizmet_say)+'</td><td class="num"><b>'+trMoney(p.ciro)+' ₺</b></td></tr>';
      });
      thtml += '</tbody></table>';
      document.getElementById('r-p-tbl').innerHTML = thtml;
    });
  }

  // ======================== MÜŞTERİ ========================
  function renderMusteri(){
    var html = '<div class="rxgrid-4">'
      + '<div class="rxtile t-pri d1"><div class="head"><div class="label">Aktif</div><div class="ico"><i class="bi bi-people-fill"></i></div></div><div class="num" id="r-m-aktif">0</div><div class="sub">Aktif müşteri</div><div class="bar-mini"><span style="width:80%"></span></div></div>'
      + '<div class="rxtile t-em d2"><div class="head"><div class="label">Yeni</div><div class="ico"><i class="bi bi-person-plus"></i></div></div><div class="num" id="r-m-yeni">0</div><div class="sub">Yeni müşteri</div><div class="bar-mini"><span style="width:60%"></span></div></div>'
      + '<div class="rxtile t-vi d3"><div class="head"><div class="label">Tekrar</div><div class="ico"><i class="bi bi-arrow-repeat"></i></div></div><div class="num" id="r-m-tekrar">0</div><div class="sub">Tekrar gelen</div><div class="bar-mini"><span style="width:55%"></span></div></div>'
      + '<div class="rxtile t-ro d4"><div class="head"><div class="label">Sadakat</div><div class="ico"><i class="bi bi-heart-fill"></i></div></div><div class="num" id="r-m-orn">0%</div><div class="sub">Geri dönüş oranı</div><div class="bar-mini"><span id="r-m-loyalty-bar" style="width:0%"></span></div></div>'
      + '</div>'
      + '<div class="rxgrid-2">'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title ro"><span class="dot"></span>Cinsiyet Dağılımı</div></div><div id="r-m-cinsiyet"></div></div>'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title am"><span class="dot"></span>En Çok Harcayan 10 Müşteri</div></div><div id="r-m-top"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/musteri', currentPeriodQS()).then(function(d){
      animCount(document.getElementById('r-m-aktif'), d.toplam_aktif||0);
      animCount(document.getElementById('r-m-yeni'), d.yeni_musteri||0);
      animCount(document.getElementById('r-m-tekrar'), d.tekrar_gelen||0);
      var sadakat = (d.toplam_aktif||0) > 0 ? Math.round(((d.tekrar_gelen||0)/(d.toplam_aktif))*100) : 0;
      var sadEl = document.getElementById('r-m-orn');
      var sadBar = document.getElementById('r-m-loyalty-bar');
      var s = performance.now();
      function st(t){ var k=Math.min(1,(t-s)/1100); var e=1-Math.pow(1-k,4); sadEl.textContent=Math.round(sadakat*e)+'%'; if(sadBar) sadBar.style.width=(sadakat*e)+'%'; if(k<1) requestAnimationFrame(st); }
      requestAnimationFrame(st);

      var c = d.cinsiyet || {kadin:0,erkek:0,belirsiz:0};
      var tot = (c.kadin||0)+(c.erkek||0)+(c.belirsiz||0);
      var pk = tot>0 ? (c.kadin/tot*100) : 0;
      var pe = tot>0 ? (c.erkek/tot*100) : 0;
      var pb = tot>0 ? (c.belirsiz/tot*100) : 0;
      var cinsBox = document.getElementById('r-m-cinsiyet');
      cinsBox.innerHTML = ''
        + '<div class="rxgender">'
        +   '<div>'
        +     '<div class="rxgender-bar">'
        +       '<span class="k" style="width:'+pk+'%"></span>'
        +       '<span class="e" style="width:'+pe+'%"></span>'
        +       '<span class="b" style="width:'+pb+'%"></span>'
        +     '</div>'
        +     '<div class="rxgender-leg">'
        +       '<div><span class="sw" style="background:var(--rx-rose)"></span>Kadın <span class="v">'+trInt(c.kadin)+'</span></div>'
        +       '<div><span class="sw" style="background:var(--rx-primary)"></span>Erkek <span class="v">'+trInt(c.erkek)+'</span></div>'
        +       '<div><span class="sw" style="background:#9aa3bd"></span>Belirsiz <span class="v">'+trInt(c.belirsiz)+'</span></div>'
        +     '</div>'
        +   '</div>'
        + '</div>';

      var top = d.top_musteriler || [];
      var topBox = document.getElementById('r-m-top');
      if(top.length === 0){
        topBox.innerHTML = '<div class="rxempty"><i class="bi bi-inbox"></i>Bu dönemde tahsilat yok.</div>';
      } else {
        var thtml = '<table class="rxtbl"><thead><tr><th class="rank">#</th><th>Müşteri</th><th>Telefon</th><th class="num">İşlem</th><th class="num">Harcama</th></tr></thead><tbody>';
        top.forEach(function(m,i){
          thtml += '<tr><td class="rank"><span class="rb">'+(i+1)+'</span></td><td>'+escape(m.name)+'</td><td>'+escape(m.cep_telefon||'-')+'</td><td class="num">'+trInt(m.islem_say||0)+'</td><td class="num"><b>'+trMoney(m.toplam||0)+' ₺</b></td></tr>';
        });
        thtml += '</tbody></table>';
        topBox.innerHTML = thtml;
      }
    });
  }

  // ======================== RANDEVU ========================
  function renderRandevu(){
    var html = '<div class="rxgrid-4">'
      + '<div class="rxtile t-pri d1"><div class="head"><div class="label">Toplam</div><div class="ico"><i class="bi bi-calendar2-check"></i></div></div><div class="num" id="r-r-toplam">0</div><div class="sub">Tüm randevular</div><div class="bar-mini"><span style="width:88%"></span></div></div>'
      + '<div class="rxtile t-em d2"><div class="head"><div class="label">Sonuçlanan</div><div class="ico"><i class="bi bi-check2-circle"></i></div></div><div class="num" id="r-r-son">0</div><div class="sub">Gelen randevu</div><div class="bar-mini"><span style="width:75%"></span></div></div>'
      + '<div class="rxtile t-ro d3"><div class="head"><div class="label">Gelmedi</div><div class="ico"><i class="bi bi-x-octagon"></i></div></div><div class="num" id="r-r-gel">0</div><div class="sub">No-show</div><div class="bar-mini"><span style="width:30%"></span></div></div>'
      + '<div class="rxtile t-am d4"><div class="head"><div class="label">İptal</div><div class="ico"><i class="bi bi-slash-circle"></i></div></div><div class="num" id="r-r-ipt">0</div><div class="sub">İptal edilen</div><div class="bar-mini"><span style="width:20%"></span></div></div>'
      + '</div>'
      + '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title am"><span class="dot"></span>Randevu Kaynağı</div><div class="rxpanel-sub">Kanal dağılımı</div></div><div class="rxsource" id="r-r-kaynak"></div></div>'
      + '<div class="rxgrid-2">'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title vi"><span class="dot"></span>Saatlik Yoğunluk</div></div><div class="rxchart tall" id="r-r-saatbox"><canvas id="r-r-saat"></canvas></div></div>'
      +   '<div class="rxpanel"><div class="rxpanel-head"><div class="rxpanel-title em"><span class="dot"></span>Haftanın Günleri</div></div><div class="rxchart tall" id="r-r-gunbox"><canvas id="r-r-gun"></canvas></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/randevu', currentPeriodQS()).then(function(d){
      animCount(document.getElementById('r-r-toplam'), d.toplam||0);
      animCount(document.getElementById('r-r-son'), d.sonuclanan||0);
      animCount(document.getElementById('r-r-gel'), d.gelmedi||0);
      animCount(document.getElementById('r-r-ipt'), d.iptal||0);

      var k = d.kaynak || {isletme:0,web:0,uygulama:0};
      var ktot = (k.isletme||0)+(k.web||0)+(k.uygulama||0);
      function pct(v){ return ktot>0 ? Math.round(v/ktot*100)+'%' : '0%'; }
      document.getElementById('r-r-kaynak').innerHTML =
         '<div class="rxsource-row s1"><div class="ico"><i class="bi bi-shop"></i></div><div><div class="lbl">İşletmeden</div><div class="pct">'+pct(k.isletme)+' pay</div></div><div class="v">'+trInt(k.isletme)+'</div></div>'
        +'<div class="rxsource-row s2"><div class="ico"><i class="bi bi-globe2"></i></div><div><div class="lbl">Website</div><div class="pct">'+pct(k.web)+' pay</div></div><div class="v">'+trInt(k.web)+'</div></div>'
        +'<div class="rxsource-row s3"><div class="ico"><i class="bi bi-phone"></i></div><div><div class="lbl">Mobil Uygulama</div><div class="pct">'+pct(k.uygulama)+' pay</div></div><div class="v">'+trInt(k.uygulama)+'</div></div>';

      var saatData = d.saat_dagilim || [];
      var saatFiltered = saatData.filter(function(s){ var h = parseInt(s.saat,10); return h>=6 && h<=23; });
      var ctx1 = document.getElementById('r-r-saat').getContext('2d');
      charts.saat = new Chart(ctx1, {
        type:'line',
        data:{labels:saatFiltered.map(function(s){return s.saat+':00';}),
              datasets:[{data:saatFiltered.map(function(s){return s.adet;}),
                         borderColor:'#9D5DC8',backgroundColor:'rgba(157,93,200,.18)',
                         fill:true,tension:.4,pointRadius:3,pointBackgroundColor:'#9D5DC8',borderWidth:2}]},
        options:{responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.parsed.y+' randevu';}}}},
          scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#f0f2f8'},ticks:{precision:0}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var gunData = d.gun_dagilim || [];
      var ctx2 = document.getElementById('r-r-gun').getContext('2d');
      charts.gun = new Chart(ctx2, {
        type:'bar',
        data:{labels:gunData.map(function(g){return g.gun;}),
              datasets:[{data:gunData.map(function(g){return g.adet;}),
                         backgroundColor:'rgba(6,148,113,.85)',
                         borderRadius:6,maxBarThickness:40}]},
        options:{responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.parsed.y+' randevu';}}}},
          scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#f0f2f8'},ticks:{precision:0}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });
    });
  }

  // ======================== ROUTER ========================
  var activeSection = 'isletme';
  function loadSection(section, label){
    activeSection = section;
    killCharts();
    var lblEl = document.getElementById('rx-current-label');
    if(lblEl && label) lblEl.textContent = label;
    if(section === 'isletme') renderIsletme();
    else if(section === 'hizmet') renderHizmet();
    else if(section === 'urun') renderUrun();
    else if(section === 'personel') renderPersonel();
    else if(section === 'musteri') renderMusteri();
    else if(section === 'randevu') renderRandevu();
  }
  function reload(){
    var btn = document.querySelector('#rx-nav button.on');
    loadSection(activeSection, btn ? btn.getAttribute('data-label') : '');
  }

  // Nav
  document.querySelectorAll('#rx-nav button').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('#rx-nav button').forEach(function(b){ b.classList.remove('on'); });
      btn.classList.add('on');
      loadSection(btn.getAttribute('data-section'), btn.getAttribute('data-label'));
    });
  });
  // Period segment
  document.querySelectorAll('#rx-period button').forEach(function(chip){
    chip.addEventListener('click', function(){
      document.querySelectorAll('#rx-period button').forEach(function(c){ c.classList.remove('on'); });
      chip.classList.add('on');
      reload();
    });
  });
  // Date inputs
  ['rep-bas','rep-bit'].forEach(function(id){
    document.getElementById(id).addEventListener('change', function(){
      document.querySelectorAll('#rx-period button').forEach(function(c){ c.classList.remove('on'); });
      reload();
    });
  });

  loadSection('isletme', 'İşletme');
}

(function repBoot(){
  function ready(){
    if(typeof Chart === 'undefined'){ return setTimeout(ready, 50); }
    repInit();
  }
  if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', ready); }
  else { ready(); }
})();
</script>

@endsection
