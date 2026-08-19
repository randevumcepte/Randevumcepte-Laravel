@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

@php
    $subeQuery = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
    $subeParam = isset($_GET['sube']) ? '&sube='.$isletme->id : '';
    $apiSubeParam = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/countUp.js/2.8.0/countUp.umd.min.js" defer></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
  --dash-bg:#f6f7fb;
  --dash-card-bg:#ffffff;
  --dash-border:#ecedf4;
  --dash-text:#181b2c;
  --dash-text-soft:#5c6486;
  --dash-text-muted:#9097ad;
  --dash-accent:#6e4bff;
  --dash-accent-soft:#efe9ff;
  --dash-accent-strong:#4c2dd6;
  --dash-green:#1fbf6f;
  --dash-orange:#ff8a3d;
  --dash-pink:#ff5c8a;
  --dash-blue:#1e9fff;
  --dash-violet:#a16bff;
  --dash-cyan:#22c4cb;
  --dash-yellow:#f7c948;
  --dash-radius:20px;
  --dash-shadow:0 4px 24px rgba(28,32,72,0.06);
  --dash-shadow-hover:0 10px 36px rgba(28,32,72,0.12);
}

.rmc-dash-wrap{
  padding:8px 4px 40px 4px;
  background:radial-gradient(ellipse 900px 360px at 0% 0%, rgba(110,75,255,.05), transparent 55%), radial-gradient(ellipse 800px 380px at 100% 0%, rgba(255,92,138,.04), transparent 50%);
  min-height:600px;
}

/* ===== Hero Greeting ===== */
.rmc-hero{
  position:relative;overflow:hidden;
  background:linear-gradient(120deg,#6e4bff 0%, #a16bff 55%, #ff5c8a 100%);
  border-radius:24px;
  padding:22px 28px;
  color:#fff;
  margin-bottom:22px;
  display:grid;
  grid-template-columns:1fr auto;
  align-items:center;gap:18px;
  box-shadow:0 14px 40px rgba(110,75,255,.20);
}
.rmc-hero::before{
  content:'';position:absolute;right:-60px;top:-60px;width:240px;height:240px;border-radius:50%;
  background:radial-gradient(circle,rgba(255,255,255,.18),transparent 70%);
}
.rmc-hero::after{
  content:'';position:absolute;left:-30px;bottom:-60px;width:160px;height:160px;border-radius:50%;
  background:radial-gradient(circle,rgba(255,255,255,.10),transparent 70%);
}
.rmc-hero-text{position:relative;z-index:1;}
.rmc-hero-date{font-size:12px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;opacity:.82;}
.rmc-hero-greet{font-size:22px;font-weight:800;margin-top:4px;line-height:1.2;}
.rmc-hero-sub{font-size:13.5px;opacity:.88;margin-top:6px;font-weight:500;}
.rmc-hero-stats{position:relative;z-index:1;display:flex;gap:18px;}
.rmc-hero-stat{
  text-align:center;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);
  border-radius:14px;padding:10px 16px;min-width:84px;border:1px solid rgba(255,255,255,.18);
}
.rmc-hero-stat .v{font-size:22px;font-weight:800;line-height:1;}
.rmc-hero-stat .l{font-size:10.5px;letter-spacing:.6px;text-transform:uppercase;opacity:.85;margin-top:4px;}
@media (max-width:767px){
  .rmc-hero{grid-template-columns:1fr;}
  .rmc-hero-stats{margin-top:6px;}
}

/* ===== Hızlı Eylem Şeridi ===== */
.rmc-quick-actions{
  display:grid;
  grid-template-columns:repeat(7,minmax(0,1fr));
  gap:14px;
  margin-bottom:22px;
}
@media (max-width:1399px){.rmc-quick-actions{grid-template-columns:repeat(4,1fr);}}
@media (max-width:767px){.rmc-quick-actions{grid-template-columns:repeat(2,1fr);}}

.rmc-qa{
  background:var(--dash-card-bg);
  border:1px solid var(--dash-border);
  border-radius:16px;
  padding:14px 16px;
  display:flex;align-items:center;gap:12px;
  cursor:pointer;
  text-decoration:none !important;
  color:var(--dash-text) !important;
  transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease;
  position:relative;overflow:hidden;
  opacity:0;animation:rmcRise .55s ease forwards;
}
.rmc-qa::after{
  content:'';position:absolute;left:0;top:0;height:3px;width:0;
  background:linear-gradient(90deg, var(--dash-accent), var(--dash-pink));
  transition:width .35s cubic-bezier(.16,1,.3,1);border-radius:0 3px 3px 0;
}
.rmc-qa:hover{transform:translateY(-4px);box-shadow:var(--dash-shadow-hover);border-color:transparent;}
.rmc-qa:hover::after{width:100%;}
.rmc-qa .rmc-qa-icon{
  width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;
  font-size:18px;flex-shrink:0;color:#fff;
  box-shadow:0 6px 14px rgba(0,0,0,.12);
}
.rmc-qa .rmc-qa-text{font-weight:600;font-size:13.5px;line-height:1.25;}
.rmc-qa.var-1 .rmc-qa-icon{background:linear-gradient(135deg,#1fbf6f,#46d68b);}
.rmc-qa.var-2 .rmc-qa-icon{background:linear-gradient(135deg,#1e9fff,#5cc0ff);}
.rmc-qa.var-3 .rmc-qa-icon{background:linear-gradient(135deg,#a16bff,#d199ff);}
.rmc-qa.var-4 .rmc-qa-icon{background:linear-gradient(135deg,#6e4bff,#9a7cff);}
.rmc-qa.var-5 .rmc-qa-icon{background:linear-gradient(135deg,#ff8a3d,#ffac6e);}
.rmc-qa.var-6 .rmc-qa-icon{background:linear-gradient(135deg,#ff5c8a,#ff8aae);}
.rmc-qa.var-7 .rmc-qa-icon{background:linear-gradient(135deg,#22c4cb,#5dd6dc);}
.rmc-qa:nth-child(1){animation-delay:.00s}
.rmc-qa:nth-child(2){animation-delay:.05s}
.rmc-qa:nth-child(3){animation-delay:.10s}
.rmc-qa:nth-child(4){animation-delay:.15s}
.rmc-qa:nth-child(5){animation-delay:.20s}
.rmc-qa:nth-child(6){animation-delay:.25s}
.rmc-qa:nth-child(7){animation-delay:.30s}

/* ===== Card Genel ===== */
.rmc-card{
  background:var(--dash-card-bg);
  border:1px solid var(--dash-border);
  border-radius:var(--dash-radius);
  padding:22px 24px;
  box-shadow:var(--dash-shadow);
  opacity:0;animation:rmcRise .55s ease forwards;
  position:relative;overflow:hidden;
}
.rmc-card::before{
  content:'';position:absolute;left:0;top:0;width:100%;height:4px;
  background:linear-gradient(90deg, var(--c-from,#6e4bff), var(--c-to,#a16bff));
  opacity:.95;
}
.rmc-card.tone-green{--c-from:#1fbf6f;--c-to:#46d68b;}
.rmc-card.tone-violet{--c-from:#6e4bff;--c-to:#a16bff;}
.rmc-card.tone-orange{--c-from:#ff8a3d;--c-to:#ff5c8a;}
.rmc-card.tone-blue{--c-from:#1e9fff;--c-to:#22c4cb;}
.rmc-card.tone-pink{--c-from:#ff5c8a;--c-to:#ff8aae;}
.rmc-card.tone-mix{--c-from:#6e4bff;--c-to:#22c4cb;}
.rmc-card .rmc-card-head{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:16px;gap:10px;flex-wrap:wrap;
}
.rmc-card-title{font-size:15px;font-weight:700;color:var(--dash-text);display:flex;align-items:center;gap:10px;}
.rmc-card-title .rmc-title-icon{
  width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg, var(--c-from,#6e4bff), var(--c-to,#a16bff));
  color:#fff;font-size:15px;box-shadow:0 4px 10px rgba(110,75,255,.18);
}

/* periyot sekmesi */
.rmc-period{display:flex;background:#f3f4f8;border-radius:10px;padding:3px;gap:0;}
.rmc-period button{
  background:transparent;border:0;font-size:12px;font-weight:600;padding:7px 14px;
  border-radius:8px;color:var(--dash-text-soft);cursor:pointer;transition:all .2s;
}
.rmc-period button.is-active{background:#fff;color:var(--dash-accent);box-shadow:0 1px 3px rgba(0,0,0,.07);}

.rmc-grid-top{
  display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:22px;
}
@media (max-width:1599px){.rmc-grid-top{grid-template-columns:repeat(2,1fr);}}
@media (max-width:767px){.rmc-grid-top{grid-template-columns:1fr;}}

.rmc-card.delay-1{animation-delay:.08s}
.rmc-card.delay-2{animation-delay:.14s}
.rmc-card.delay-3{animation-delay:.20s}
.rmc-card.delay-4{animation-delay:.26s}
.rmc-card.delay-5{animation-delay:.34s}
.rmc-card.delay-6{animation-delay:.42s}
.rmc-card.delay-7{animation-delay:.50s}

/* ===== Kasa İstatistikleri ===== */
.rmc-kasa-body{display:grid;grid-template-columns:160px 1fr;gap:18px;align-items:center;}
.rmc-kasa-chart-box{position:relative;width:160px;height:160px;}
.rmc-kasa-chart-box canvas{width:100%!important;height:100%!important;}
.rmc-kasa-legend{display:flex;flex-direction:column;gap:10px;}
.rmc-kasa-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--dash-text-soft);}
.rmc-kasa-row .rmc-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.rmc-kasa-row b{margin-left:auto;color:var(--dash-text);font-weight:700;}
.rmc-kasa-total{margin-top:16px;padding-top:14px;border-top:1px dashed var(--dash-border);text-align:center;}
.rmc-kasa-total .lbl{font-size:12px;color:var(--dash-text-muted);font-weight:600;letter-spacing:.4px;text-transform:uppercase;}
.rmc-kasa-total .val{font-size:26px;font-weight:800;color:var(--dash-text);margin-top:4px;}
.rmc-kasa-spark{margin-top:10px;height:36px;}

/* ===== Memnuniyet Anketi ===== */
.rmc-anket-body{display:grid;grid-template-columns:1fr;gap:18px;margin-bottom:12px;}
.rmc-nps-block{display:flex;flex-direction:column;gap:10px;}
.rmc-nps-skor{
  display:flex;align-items:baseline;gap:8px;
}
.rmc-nps-skor #rmc-anket-nps{
  font-size:38px;font-weight:800;line-height:1;
  background:linear-gradient(135deg,#6e4bff,#ff5c8a);
  -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;
}
.rmc-nps-lbl{font-size:11px;color:var(--dash-text-muted);font-weight:700;letter-spacing:.6px;text-transform:uppercase;}
.rmc-nps-bar{
  display:flex;height:14px;border-radius:8px;overflow:hidden;background:#eef0f6;
  box-shadow:inset 0 0 0 1px rgba(0,0,0,.04);
}
.rmc-nps-bar .seg{height:100%;width:0;transition:width 1s cubic-bezier(.16,1,.3,1);}
.rmc-nps-bar .seg-prom{background:linear-gradient(90deg,#1fbf6f,#46d68b);}
.rmc-nps-bar .seg-pas{background:linear-gradient(90deg,#ffac6e,#ff8a3d);}
.rmc-nps-bar .seg-det{background:linear-gradient(90deg,#ff5c8a,#ff85aa);}
.rmc-nps-legend{display:flex;justify-content:space-between;gap:8px;font-size:11px;color:var(--dash-text-soft);}
.rmc-nps-legend span{display:flex;align-items:center;gap:5px;}
.rmc-nps-legend i.dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
.rmc-nps-legend i.dot.prom{background:#1fbf6f;}
.rmc-nps-legend i.dot.pas{background:#ff8a3d;}
.rmc-nps-legend i.dot.det{background:#ff5c8a;}
.rmc-nps-legend b{color:var(--dash-text);font-weight:700;}

.rmc-anket-mini{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.mini-stat{
  background:#fafbff;border:1px solid var(--dash-border);border-radius:14px;padding:10px 12px;
  text-align:center;
}
.mini-stat .mini-val{font-size:22px;font-weight:800;color:var(--dash-text);line-height:1;}
.mini-stat .mini-lbl{font-size:10.5px;color:var(--dash-text-muted);font-weight:700;letter-spacing:.4px;text-transform:uppercase;margin-top:4px;}
.mini-stat .mini-sub{font-size:11px;color:var(--dash-text-soft);font-weight:600;margin-top:3px;}

.rmc-anket-yorumlar{border-top:1px dashed var(--dash-border);padding-top:10px;display:flex;flex-direction:column;gap:8px;max-height:130px;overflow-y:auto;}
.rmc-yorum{
  display:flex;align-items:flex-start;gap:8px;
  padding:6px 9px;background:#fafbff;border-radius:9px;border-left:3px solid var(--dash-accent);
}
.rmc-yorum.lo{border-left-color:#ff5c8a;}
.rmc-yorum.hi{border-left-color:#1fbf6f;}
.rmc-yorum .y-txt{font-size:12px;color:var(--dash-text-soft);line-height:1.35;flex:1;}
.rmc-yorum .y-meta{font-size:10px;color:var(--dash-text-muted);margin-top:2px;font-weight:600;}
.rmc-yorum-bos{font-size:12px;color:var(--dash-text-muted);text-align:center;padding:14px 0;}

/* ===== Çarkıfelek ===== */
.rmc-cark-body{display:grid;grid-template-columns:120px 1fr;gap:16px;align-items:center;margin-bottom:12px;}
.rmc-cark-wheel{display:flex;align-items:center;justify-content:center;}
.rmc-cark-wheel svg{filter:drop-shadow(0 6px 14px rgba(110,75,255,.16));}
.rmc-cark-stats{display:flex;flex-direction:column;gap:7px;font-size:12.5px;}
.rmc-cark-stats .cark-row{display:flex;align-items:center;justify-content:space-between;gap:6px;color:var(--dash-text-soft);}
.rmc-cark-stats .cark-row .lbl{display:flex;align-items:center;gap:6px;}
.rmc-cark-stats .cark-row .dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
.rmc-cark-stats .cark-row b{color:var(--dash-text);font-weight:700;}
.rmc-cark-stats .cark-row-sep{border-top:1px dashed var(--dash-border);padding-top:7px;margin-top:3px;}
.rmc-cark-yorumlar{border-top:1px dashed var(--dash-border);padding-top:10px;display:flex;flex-direction:column;gap:6px;max-height:110px;overflow-y:auto;}
.rmc-cark-kazanan-row{
  display:flex;align-items:center;gap:8px;padding:5px 8px;background:#fafbff;border-radius:8px;
  border-left:3px solid var(--dash-cyan);font-size:12px;
}
.rmc-cark-kazanan-row .ad{font-weight:600;color:var(--dash-text);}
.rmc-cark-kazanan-row .od{color:var(--dash-text-soft);margin-left:auto;font-weight:600;}

/* ===== Randevu Ayrıntıları ===== */
.rmc-randevu-body{display:grid;grid-template-columns:1fr 130px;gap:16px;align-items:center;}
.rmc-randevu-list{display:flex;flex-direction:column;gap:12px;}
.rmc-randevu-item{display:flex;align-items:center;gap:12px;}
.rmc-randevu-item .ico{
  width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;
}
.rmc-randevu-item .txt .num{font-size:18px;font-weight:800;color:var(--dash-text);line-height:1;}
.rmc-randevu-item .txt .lbl{font-size:12px;color:var(--dash-text-soft);}

.rmc-doluluk-ring{
  width:148px;height:148px;border-radius:50%;
  background:conic-gradient(var(--dash-accent) 0deg, #eef0f6 0deg);
  display:flex;align-items:center;justify-content:center;
  transition:background 1.2s cubic-bezier(.16,1,.3,1);
  box-shadow:0 8px 24px rgba(110,75,255,.16), inset 0 0 0 2px rgba(255,255,255,.6);
  position:relative;
}
.rmc-doluluk-ring::before{
  content:'';position:absolute;inset:-4px;border-radius:50%;
  background:conic-gradient(from 0deg, rgba(110,75,255,.12), transparent 40%);
  filter:blur(8px);z-index:-1;
}
.rmc-doluluk-ring-inner{
  width:116px;height:116px;border-radius:50%;background:#fff;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.rmc-doluluk-pct{font-size:28px;font-weight:800;color:var(--dash-text);line-height:1;}
.rmc-doluluk-lbl{font-size:10.5px;color:var(--dash-text-muted);font-weight:600;text-align:center;letter-spacing:.3px;margin-top:6px;}

/* ===== Tabbed Alt Panel ===== */
.rmc-tabs-card{margin-bottom:22px;}
.rmc-tabs-nav{display:flex;gap:6px;flex-wrap:wrap;border-bottom:1px solid var(--dash-border);margin-bottom:14px;padding-bottom:0;}
.rmc-tab{
  background:transparent;border:0;padding:10px 16px;font-size:13px;font-weight:600;color:var(--dash-text-soft);
  cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;display:flex;align-items:center;gap:6px;
  transition:color .15s, border-color .15s;
}
.rmc-tab:hover{color:var(--dash-text);}
.rmc-tab.is-active{color:var(--dash-accent);border-bottom-color:var(--dash-accent);}
.rmc-tab .badge-new{background:var(--dash-pink);color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:6px;letter-spacing:.3px;}
.rmc-tab-content{min-height:160px;}
.rmc-tab-table{width:100%;border-collapse:collapse;}
.rmc-tab-table th{font-size:11px;color:var(--dash-text-muted);font-weight:700;letter-spacing:.4px;text-transform:uppercase;text-align:left;padding:8px 12px;border-bottom:1px solid var(--dash-border);}
.rmc-tab-table td{padding:11px 12px;font-size:13px;color:var(--dash-text);border-bottom:1px solid var(--dash-border);}
.rmc-tab-table tbody tr:last-child td{border-bottom:0;}
.rmc-tab-table tbody tr:hover{background:#fafbfd;}
.rmc-tab-empty{padding:36px 16px;text-align:center;color:var(--dash-text-muted);font-size:13px;}

/* ===== Bottom Row (Timeline + Personel) ===== */
.rmc-grid-bottom{display:grid;grid-template-columns:1.3fr 1fr;gap:18px;}
@media (max-width:1199px){.rmc-grid-bottom{grid-template-columns:1fr;}}

.rmc-timeline{max-height:380px;overflow-y:auto;padding-right:6px;}
.rmc-timeline-item{
  display:flex;align-items:flex-start;gap:14px;padding:10px 0;border-left:2px solid var(--dash-border);
  margin-left:8px;padding-left:18px;position:relative;
}
.rmc-timeline-item::before{
  content:'';position:absolute;left:-7px;top:14px;width:12px;height:12px;border-radius:50%;
  background:var(--dash-accent);border:2px solid #fff;box-shadow:0 0 0 2px var(--dash-accent);
}
.rmc-timeline-item.tahsilat::before{background:var(--dash-green);box-shadow:0 0 0 2px var(--dash-green);}
.rmc-timeline-item .t-saat{font-size:12px;color:var(--dash-text-muted);font-weight:700;width:46px;flex-shrink:0;}
.rmc-timeline-item .t-baslik{font-size:13px;color:var(--dash-text);font-weight:500;}
.rmc-timeline-now{
  display:flex;align-items:center;gap:10px;padding:8px 0;margin-left:8px;padding-left:18px;
  position:relative;border-left:2px solid var(--dash-pink);
}
.rmc-timeline-now::before{
  content:'';position:absolute;left:-7px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;
  background:var(--dash-pink);box-shadow:0 0 0 4px rgba(255,111,145,.2);
  animation:rmcPulse 1.6s infinite;
}
.rmc-timeline-now span{font-size:11px;font-weight:800;color:var(--dash-pink);letter-spacing:.5px;text-transform:uppercase;}

.rmc-personel-list{display:flex;flex-direction:column;gap:14px;max-height:380px;overflow-y:auto;}
.rmc-personel-row{display:grid;grid-template-columns:90px 1fr 50px;gap:10px;align-items:center;}
.rmc-personel-row .ad{font-size:13px;color:var(--dash-text);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rmc-personel-row .bar{height:8px;background:#f3f4f8;border-radius:6px;overflow:hidden;}
.rmc-personel-row .bar-fill{
  height:100%;background:linear-gradient(90deg,var(--dash-accent),var(--dash-violet));border-radius:6px;
  width:0%;transition:width 1.2s cubic-bezier(.16,1,.3,1);
}
.rmc-personel-row .pct{font-size:12px;color:var(--dash-text-soft);font-weight:700;text-align:right;}

/* ===== Skeleton Shimmer ===== */
.rmc-skel{
  background:linear-gradient(90deg,#f3f4f8 0%,#eaecf4 50%,#f3f4f8 100%);
  background-size:200% 100%;
  animation:rmcShimmer 1.4s infinite;
  border-radius:8px;
}
.rmc-skel-line{height:14px;margin:6px 0;}
.rmc-skel-circle{border-radius:50%;}

@keyframes rmcShimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
@keyframes rmcRise{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
@keyframes rmcPulse{0%,100%{box-shadow:0 0 0 4px rgba(255,111,145,.2);}50%{box-shadow:0 0 0 8px rgba(255,111,145,.05);}}

/* renkler */
.bg-soft-blue{background:#e3f2ff;color:#1e9fff;}
.bg-soft-green{background:#defaeb;color:#1fbf6f;}
.bg-soft-orange{background:#fff0e3;color:#ff8a3d;}
.bg-soft-pink{background:#ffe3eb;color:#ff5c8a;}
.bg-soft-violet{background:#efe9ff;color:#a16bff;}
.bg-soft-accent{background:#efe9ff;color:#6e4bff;}
.bg-soft-gray{background:#f3f4f8;color:#5c6486;}
.bg-soft-cyan{background:#deffff;color:#22c4cb;}

/* Eylem badge */
.rmc-yeni{position:absolute;top:6px;right:8px;background:var(--dash-pink);color:#fff;font-size:9px;font-weight:800;padding:2px 6px;border-radius:5px;letter-spacing:.4px;}

/* Üyelik bilgi şeridi */
.rmc-lic-bar{
  display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#5b6bf7,#9b5cff);color:#fff;
  padding:10px 16px;border-radius:12px;margin-bottom:16px;font-size:13px;font-weight:500;
}
.rmc-lic-bar i{font-size:16px;}
</style>

<div class="rmc-dash-wrap">

  {{-- HERO GREETING --}}
  @php
    $hourNow = (int) date('H');
    $greet = $hourNow < 6 ? 'İyi geceler' : ($hourNow < 12 ? 'Günaydın' : ($hourNow < 18 ? 'İyi günler' : 'İyi akşamlar'));
    $userName = '';
    if(Auth::guard('isletmeyonetim')->check()){
        $userName = Auth::guard('isletmeyonetim')->user()->name ?? '';
    }
    $aylarArr = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
    $gunlerArr = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
    $bugunStr = date('j').' '.$aylarArr[date('n')-1].' '.date('Y').' · '.$gunlerArr[(int)date('w')];
  @endphp
  <div class="rmc-hero">
    <div class="rmc-hero-text">
      <div class="rmc-hero-date">{{$bugunStr}}</div>
      <div class="rmc-hero-greet">{{$greet}}{{ $userName ? ', '.explode(' ',$userName)[0] : '' }} 👋</div>
      <div class="rmc-hero-sub">{{$isletme->salon_adi}} — Bugünün özetine göz at, aşağıdaki kartlardan her şeyi tek tıkla yönet.</div>
    </div>
    <div class="rmc-hero-stats">
      @yetki('randevu.takvim_gor')
      <div class="rmc-hero-stat"><div class="v" id="rmc-hero-bugun">—</div><div class="l">Bugün Randevu</div></div>
      @endyetki
      @yetki('rapor.kasa')
      <div class="rmc-hero-stat"><div class="v" id="rmc-hero-gelir">—</div><div class="l">Bugün Gelir ₺</div></div>
      @endyetki
      @if(isset($kalan_uyelik_suresi) && is_numeric($kalan_uyelik_suresi) && $kalan_uyelik_suresi >= 0)
      <div class="rmc-hero-stat"><div class="v">{{$kalan_uyelik_suresi}}</div><div class="l">Üyelik Gün</div></div>
      @endif
    </div>
  </div>

  {{-- HIZLI EYLEM ŞERIDI --}}
  <div class="rmc-quick-actions">
    @yetki('randevu.olustur')
    <a class="rmc-qa var-1" href="/isletmeyonetim/randevular{{$subeQuery}}">
      <div class="rmc-qa-icon"><i class="bi bi-calendar-plus-fill"></i></div>
      <div class="rmc-qa-text">Yeni Randevu Oluştur</div>
    </a>
    @endyetki
    @yetki('satis.adisyon_olustur')
    <a class="rmc-qa var-2" href="/isletmeyonetim/adisyonlar{{$subeQuery}}">
      <div class="rmc-qa-icon"><i class="bi bi-receipt"></i></div>
      <div class="rmc-qa-text">Yeni Satış Oluştur</div>
    </a>
    @endyetki
    @yetki('paket.tanim_olustur')
    <a class="rmc-qa var-3" href="/isletmeyonetim/paketsatislari{{$subeQuery}}">
      <div class="rmc-qa-icon"><i class="bi bi-gift-fill"></i></div>
      <div class="rmc-qa-text">Yeni Paket Oluştur</div>
    </a>
    @endyetki
    @yetki('hizmet.tanim_olustur')
    <a class="rmc-qa var-4" href="/isletmeyonetim/ayarlar?p=hizmetler{{ isset($_GET['sube']) ? '&sube='.$isletme->id : '' }}">
      <div class="rmc-qa-icon"><i class="bi bi-stars"></i></div>
      <div class="rmc-qa-text">Yeni Hizmet Oluştur</div>
    </a>
    @endyetki
    @yetki('rapor.satis')
    <a class="rmc-qa var-5" href="/isletmeyonetim/isletmeraporlari{{$subeQuery}}">
      <div class="rmc-qa-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
      <div class="rmc-qa-text">İşletme Raporları</div>
    </a>
    @endyetki
    @yetki('musteri.liste_gor')
    <a class="rmc-qa var-6" href="/isletmeyonetim/musteriler{{$subeQuery}}">
      <div class="rmc-qa-icon"><i class="bi bi-people-fill"></i></div>
      <div class="rmc-qa-text">Müşteri Listesi</div>
    </a>
    @endyetki
    @yetki('paket.seans_takip')
    <a class="rmc-qa var-7" href="/isletmeyonetim/seanstakip{{$subeQuery}}">
      <div class="rmc-qa-icon"><i class="bi bi-list-check"></i></div>
      <div class="rmc-qa-text">Seans Takibi</div>
    </a>
    @endyetki
  </div>

  {{-- ÜST GRID: Kasa / Takvim / Randevu Ayrıntıları --}}
  <div class="rmc-grid-top">

    {{-- KASA İSTATISTIKLERI — rapor.kasa VEYA personel.kendi_ciro_gor açıksa görünür.
         Personel rolünde kendi_ciro_gor açıksa dashboardKasa endpoint'i personel_id ile filtreler. --}}
    @if(
        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'rapor.kasa') ||
        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'personel.kendi_ciro_gor')
    )
    <div class="rmc-card tone-green delay-1" id="rmc-kasa-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><span class="rmc-title-icon"><i class="bi bi-wallet2"></i></span> Kasa İstatistikleri</div>
        <div class="rmc-period" data-target="kasa">
          <button data-period="daily">Günlük</button>
          <button data-period="7d">Son 7 gün</button>
          <button data-period="30d" class="is-active">Son 30 gün</button>
        </div>
      </div>
      <div class="rmc-kasa-body">
        <div class="rmc-kasa-chart-box"><canvas id="rmc-kasa-chart"></canvas></div>
        <div class="rmc-kasa-legend">
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#1fbf6f"></span> Nakit <span id="rmc-kasa-nakit-pct" style="margin-left:auto;color:#9097ad;font-weight:600;">— %</span></div>
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#6e4bff"></span> Kart <span id="rmc-kasa-kart-pct" style="margin-left:auto;color:#9097ad;font-weight:600;">— %</span></div>
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#ff8a3d"></span> Havale/EFT <span id="rmc-kasa-havale-pct" style="margin-left:auto;color:#9097ad;font-weight:600;">— %</span></div>
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#9097ad"></span> Diğer <span id="rmc-kasa-diger-pct" style="margin-left:auto;color:#9097ad;font-weight:600;">— %</span></div>
        </div>
      </div>
      <div class="rmc-kasa-total">
        <div class="lbl">Toplam Gelir</div>
        <div class="val"><span id="rmc-kasa-total">0</span> ₺</div>
        <svg class="rmc-kasa-spark" id="rmc-kasa-spark" viewBox="0 0 200 36" preserveAspectRatio="none"></svg>
      </div>
    </div>

    @endif

    {{-- MEMNUNIYET ANKETI --}}
    @yetki('pazarlama.anket_yonet')
    <div class="rmc-card tone-violet delay-2" id="rmc-anket-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><span class="rmc-title-icon"><i class="bi bi-emoji-heart-eyes"></i></span> Memnuniyet Anketi</div>
        <div class="rmc-period" data-target="anket">
          <button data-period="7d">7g</button>
          <button data-period="30d" class="is-active">30g</button>
          <button data-period="90d">90g</button>
        </div>
      </div>
      <div class="rmc-anket-body">
        <div class="rmc-nps-block">
          <div class="rmc-nps-skor"><span id="rmc-anket-nps">—</span><span class="rmc-nps-lbl">NPS</span></div>
          <div class="rmc-nps-bar" id="rmc-nps-bar" title="Promoter / Pasif / Detractor">
            <div class="seg seg-prom" data-w="0"></div><div class="seg seg-pas" data-w="0"></div><div class="seg seg-det" data-w="0"></div>
          </div>
          <div class="rmc-nps-legend">
            <span><i class="dot prom"></i> Promoter <b id="rmc-anket-prom">0</b></span>
            <span><i class="dot pas"></i> Pasif <b id="rmc-anket-pas">0</b></span>
            <span><i class="dot det"></i> Detractor <b id="rmc-anket-det">0</b></span>
          </div>
        </div>
        <div class="rmc-anket-mini">
          <div class="mini-stat">
            <div class="mini-val" id="rmc-anket-csat">—</div>
            <div class="mini-lbl">CSAT / 5</div>
          </div>
          <div class="mini-stat">
            <div class="mini-val"><span id="rmc-anket-orani">—</span>%</div>
            <div class="mini-lbl">Cevap Oranı</div>
            <div class="mini-sub" id="rmc-anket-sayilar">0 / 0</div>
          </div>
        </div>
      </div>
      <div class="rmc-anket-yorumlar" id="rmc-anket-yorumlar">
        @for($i=0;$i<2;$i++)<div class="rmc-skel rmc-skel-line" style="width:{{90-$i*10}}%;margin:8px 0;"></div>@endfor
      </div>
    </div>

    @endyetki

    {{-- RANDEVU AYRINTILARI --}}
    @yetki('randevu.takvim_gor')
    <div class="rmc-card tone-orange delay-3" id="rmc-randevu-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><span class="rmc-title-icon"><i class="bi bi-clock-history"></i></span> Randevu Ayrıntıları</div>
        <div class="rmc-period" data-target="randevu">
          <button data-period="daily" class="is-active">Günlük</button>
          <button data-period="7d">7g</button>
          <button data-period="30d">30g</button>
        </div>
      </div>
      <div class="rmc-randevu-body">
        <div class="rmc-randevu-list">
          <div class="rmc-randevu-item">
            <div class="ico bg-soft-violet"><i class="bi bi-alarm"></i></div>
            <div class="txt"><div class="num" id="rmc-rnd-olusturulan">0</div><div class="lbl">Oluşturulanlar</div></div>
          </div>
          <div class="rmc-randevu-item">
            <div class="ico bg-soft-green"><i class="bi bi-check-circle-fill"></i></div>
            <div class="txt"><div class="num" id="rmc-rnd-sonuclanan">0</div><div class="lbl">Sonuçlananlar</div></div>
          </div>
          <div class="rmc-randevu-item">
            <div class="ico bg-soft-pink"><i class="bi bi-x-circle-fill"></i></div>
            <div class="txt"><div class="num" id="rmc-rnd-sonuclanmayan">0</div><div class="lbl">Sonuçlanmayanlar</div></div>
          </div>
        </div>
        <div>
          <div class="rmc-doluluk-ring" id="rmc-doluluk-ring">
            <div class="rmc-doluluk-ring-inner">
              <div class="rmc-doluluk-pct" id="rmc-doluluk-pct">0%</div>
              <div class="rmc-doluluk-lbl">Anlık Doluluk<br>Oranı</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @endyetki

    {{-- ÇARKIFELEK --}}
    @yetki('pazarlama.cark_yonet')
    <div class="rmc-card tone-mix delay-4" id="rmc-cark-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><span class="rmc-title-icon"><i class="bi bi-stars"></i></span> Çarkıfelek</div>
        <div class="rmc-period" data-target="cark">
          <button data-period="7d">7g</button>
          <button data-period="30d" class="is-active">30g</button>
          <button data-period="90d">90g</button>
        </div>
      </div>
      <div class="rmc-cark-body">
        <div class="rmc-cark-wheel" id="rmc-cark-wheel">
          <svg viewBox="0 0 100 100" width="120" height="120">
            <circle cx="50" cy="50" r="44" fill="none" stroke="#eef0f6" stroke-width="10"/>
            <circle id="rmc-cark-ring" cx="50" cy="50" r="44" fill="none" stroke="url(#rmcCarkGrad)" stroke-width="10"
              stroke-linecap="round" stroke-dasharray="276.46" stroke-dashoffset="276.46"
              transform="rotate(-90 50 50)" style="transition:stroke-dashoffset 1.2s cubic-bezier(.16,1,.3,1);"/>
            <defs><linearGradient id="rmcCarkGrad" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="#22c4cb"/><stop offset="100%" stop-color="#6e4bff"/>
            </linearGradient></defs>
            <text x="50" y="52" text-anchor="middle" font-size="18" font-weight="800" fill="#181b2c" id="rmc-cark-rate">0%</text>
            <text x="50" y="66" text-anchor="middle" font-size="6.5" font-weight="700" fill="#9097ad">KAZANMA</text>
          </svg>
        </div>
        <div class="rmc-cark-stats">
          <div class="cark-row"><span class="lbl">Toplam Çevrim</span><b id="rmc-cark-toplam">0</b></div>
          <div class="cark-row"><span class="lbl"><i class="dot" style="background:#1fbf6f"></i> Kazanan</span><b id="rmc-cark-kazanan">0</b></div>
          <div class="cark-row"><span class="lbl"><i class="dot" style="background:#9097ad"></i> Boş</span><b id="rmc-cark-bos">0</b></div>
          <div class="cark-row cark-row-sep"><span class="lbl">Kupon (verilen/kullanılan)</span><b><span id="rmc-cark-kupon-v">0</span> / <span id="rmc-cark-kupon-k">0</span></b></div>
          <div class="cark-row"><span class="lbl">Kupon Kullanım</span><b id="rmc-cark-kupon-pct">0%</b></div>
        </div>
      </div>
      <div class="rmc-cark-yorumlar" id="rmc-cark-son">
        @for($i=0;$i<2;$i++)<div class="rmc-skel rmc-skel-line" style="width:{{90-$i*10}}%;margin:8px 0;"></div>@endfor
      </div>
    </div>
    @endyetki

  </div>

  {{-- ALT PANEL: SEKMELER — her sekme kendi yetkisine bagli; sadece izinli
       sekmeler render edilir ve ilk izinli sekme aktif olur. --}}
  @php
    $_sekmeTanim = [
      'online-talep'    => ['yetki'=>'randevu.takvim_gor',  'ikon'=>'bi-globe2',         'ad'=>'Online Randevu Talepleri', 'yeni'=>false],
      'bugunku-randevu' => ['yetki'=>'randevu.takvim_gor',  'ikon'=>'bi-calendar-day',   'ad'=>'Bugünkü Randevular',       'yeni'=>true],
      'acik-adisyon'    => ['yetki'=>'satis.tum_satis_gor', 'ikon'=>'bi-receipt-cutoff', 'ad'=>'Açık Adisyonlar',          'yeni'=>false],
      'alacak'          => ['yetki'=>'finans.alacak_yonet', 'ikon'=>'bi-cash-coin',      'ad'=>'Alacaklılar',              'yeni'=>false],
      'dogum-gunu'      => ['yetki'=>'musteri.liste_gor',   'ikon'=>'bi-gift',           'ad'=>'Yaklaşan Doğum Günleri',   'yeni'=>false],
    ];
    $_dashUid = Auth::guard('isletmeyonetim')->user()->id ?? null;
    $_izinliSekmeler = [];
    foreach($_sekmeTanim as $_sk => $_sv){
      if(!$_dashUid || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, $_sv['yetki'])){
        $_izinliSekmeler[$_sk] = $_sv;
      }
    }
    $_ilkSekme = count($_izinliSekmeler) ? array_keys($_izinliSekmeler)[0] : null;
  @endphp
  @if($_ilkSekme)
  <div class="rmc-card tone-mix rmc-tabs-card delay-5">
    <div class="rmc-tabs-nav">
      @foreach($_izinliSekmeler as $_sk => $_sv)
      <button class="rmc-tab{{ $_sk === $_ilkSekme ? ' is-active' : '' }}" data-tab="{{$_sk}}"><i class="bi {{$_sv['ikon']}}"></i> {{$_sv['ad']}}@if($_sv['yeni']) <span class="badge-new">YENİ</span>@endif</button>
      @endforeach
    </div>
    <div class="rmc-tab-content" id="rmc-tab-content">
      <div style="padding:20px 12px;">
        @for($i=0;$i<4;$i++)
          <div class="rmc-skel rmc-skel-line" style="width: {{ 100 - ($i*8) }}%;"></div>
        @endfor
      </div>
    </div>
  </div>
  @endif

  {{-- ALT GRID: Timeline + Personel Anlık Durum --}}
  <div class="rmc-grid-bottom">
    {{-- Bugünkü Akış: tahsilat tutarlarini gosterir → rapor.kasa --}}
    @yetki('rapor.kasa')
    <div class="rmc-card tone-pink delay-6">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><span class="rmc-title-icon"><i class="bi bi-activity"></i></span> Bugünkü Akış</div>
        <span style="font-size:11px;color:var(--dash-text-muted);font-weight:600;">Randevu + Tahsilat</span>
      </div>
      <div class="rmc-timeline" id="rmc-timeline">
        @for($i=0;$i<5;$i++)
          <div class="rmc-skel rmc-skel-line" style="width:{{90-$i*8}}%;margin:14px 0;"></div>
        @endfor
      </div>
    </div>
    @endyetki
    @if(!$_dashUid || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, 'personel.liste_gor') || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, 'rapor.personel_performans'))
    <div class="rmc-card tone-blue delay-7">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><span class="rmc-title-icon"><i class="bi bi-person-workspace"></i></span> Personel Anlık Durum</div>
        <span style="font-size:11px;color:var(--dash-text-muted);font-weight:600;">Bugün</span>
      </div>
      <div class="rmc-personel-list" id="rmc-personel-list">
        @for($i=0;$i<4;$i++)
          <div class="rmc-personel-row">
            <div class="rmc-skel" style="height:14px;"></div>
            <div class="rmc-skel" style="height:8px;"></div>
            <div class="rmc-skel" style="height:14px;"></div>
          </div>
        @endfor
      </div>
    </div>
    @endif
  </div>

</div>

<script>
function rmcDashInit(){
  var apiBase = '/isletmeyonetim/api/dashboard';
  var subeParam = @json($apiSubeParam);

  function api(path, extra){
    var url = apiBase + path + subeParam;
    if(extra){
      url += (subeParam ? '&' : '?') + extra;
    }
    return fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); });
  }

  function trFmt(n){
    n = Number(n)||0;
    return n.toLocaleString('tr-TR',{minimumFractionDigits:0,maximumFractionDigits:0});
  }
  function pct(v,t){ return t>0 ? Math.round((v/t)*1000)/10 : 0; }
  function animateCount(el, to, opts){
    if(!el) return;
    opts = opts||{};
    try{
      var c = new countUp.CountUp(el, to, Object.assign({duration:1.2,separator:'.',decimal:',',useEasing:true},opts));
      if(!c.error){ c.start(); return; }
    }catch(e){}
    el.textContent = trFmt(to);
  }

  // ===== KASA =====
  var kasaChart = null;
  function renderKasa(period){
    api('/kasa', 'period='+period).then(function(d){
      var ctx = document.getElementById('rmc-kasa-chart').getContext('2d');
      if(kasaChart) kasaChart.destroy();
      var values = [d.nakit, d.kart, d.havale, d.diger];
      var colors = ['#1fbf6f','#6e4bff','#ff8a3d','#9097ad'];
      if(d.toplam <= 0){ values = [0,0,0,1]; colors = ['#1fbf6f','#6e4bff','#ff8a3d','#eef0f6']; }
      kasaChart = new Chart(ctx, {
        type:'doughnut',
        data:{labels:['Nakit','Kart','Havale','Diğer'],datasets:[{data:values,backgroundColor:colors,borderWidth:0,hoverOffset:6}]},
        options:{cutout:'70%',plugins:{legend:{display:false},tooltip:{enabled:d.toplam>0}},animation:{duration:900,easing:'easeOutQuart'}}
      });
      var t = d.toplam || 0;
      document.getElementById('rmc-kasa-nakit-pct').textContent = pct(d.nakit,t)+' %';
      document.getElementById('rmc-kasa-kart-pct').textContent = pct(d.kart,t)+' %';
      document.getElementById('rmc-kasa-havale-pct').textContent = pct(d.havale,t)+' %';
      document.getElementById('rmc-kasa-diger-pct').textContent = pct(d.diger,t)+' %';
      animateCount(document.getElementById('rmc-kasa-total'), t);

      // hero: bugun gelir (period=daily ise)
      if(period === 'daily'){
        var heroGelir = document.getElementById('rmc-hero-gelir');
        if(heroGelir) animateCount(heroGelir, t);
      }

      // sparkline (son 7 gün)
      var svg = document.getElementById('rmc-kasa-spark');
      var pts = (d.sparkline||[]).map(function(p){return Number(p.tutar)||0;});
      if(pts.length>0){
        var max = Math.max.apply(null,pts.concat([1]));
        var w = 200, h = 36, step = pts.length>1 ? w/(pts.length-1) : w;
        var path = pts.map(function(v,i){
          var x = i*step, y = h - (v/max)*(h-4) - 2;
          return (i===0?'M':'L')+x.toFixed(1)+','+y.toFixed(1);
        }).join(' ');
        var area = path + ' L'+w+','+h+' L0,'+h+' Z';
        svg.innerHTML =
          '<defs><linearGradient id="rmcSparkG" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#1fbf6f" stop-opacity=".40"/><stop offset="100%" stop-color="#1fbf6f" stop-opacity="0"/></linearGradient></defs>'
        + '<path d="'+area+'" fill="url(#rmcSparkG)"/>'
        + '<path d="'+path+'" fill="none" stroke="#1fbf6f" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>';
      }
    }).catch(function(e){console.warn('kasa err',e);});
  }

  // ===== RANDEVU ÖZET =====
  function renderRandevu(period){
    api('/randevu-ozet', 'period='+period).then(function(d){
      animateCount(document.getElementById('rmc-rnd-olusturulan'), d.olusturulan);
      animateCount(document.getElementById('rmc-rnd-sonuclanan'), d.sonuclanan);
      animateCount(document.getElementById('rmc-rnd-sonuclanmayan'), d.sonuclanmayan);
      // hero: bugun randevu (period=daily ise)
      if(period === 'daily'){
        var heroBugun = document.getElementById('rmc-hero-bugun');
        if(heroBugun) animateCount(heroBugun, d.olusturulan);
      }
      var ring = document.getElementById('rmc-doluluk-ring');
      var pctVal = Number(d.doluluk)||0;
      var deg = Math.round(pctVal*3.6);
      // doluluk renk skalasi — yeni paleti kullan, gradient ring icin iki renk
      var c1 = pctVal>=70 ? '#1fbf6f' : pctVal>=40 ? '#ff8a3d' : '#ff5c8a';
      var c2 = pctVal>=70 ? '#46d68b' : pctVal>=40 ? '#ffac6e' : '#ff8aae';
      ring.style.background = 'conic-gradient(from -90deg, '+c1+' 0deg, '+c2+' '+deg+'deg, #eef0f6 '+deg+'deg)';
      var pctEl = document.getElementById('rmc-doluluk-pct');
      var startTs = performance.now(); var dur = 1100;
      function step(t){
        var k = Math.min(1,(t-startTs)/dur);
        var eased = 1 - Math.pow(1-k, 4);
        pctEl.textContent = Math.round(pctVal*eased)+'%';
        if(k<1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }).catch(function(e){console.warn('randevu err',e);});
  }

  // ===== MEMNUNIYET ANKETI =====
  function renderAnket(period){
    api('/anket', 'period='+period).then(function(d){
      var nps = (d.nps && d.nps.skor !== null && d.nps.skor !== undefined) ? d.nps.skor : null;
      var npsEl = document.getElementById('rmc-anket-nps');
      if(nps === null){
        npsEl.textContent = '—';
      } else {
        // animate from 0
        var startTs = performance.now(); var dur = 1100;
        function step(t){
          var k = Math.min(1,(t-startTs)/dur);
          var eased = 1 - Math.pow(1-k, 4);
          npsEl.textContent = (nps>=0?'+':'') + Math.round(nps*eased);
          if(k<1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }
      var nt = (d.nps && d.nps.toplam) ? d.nps.toplam : 0;
      var pW = nt>0 ? (d.nps.promoter/nt)*100 : 0;
      var paW = nt>0 ? (d.nps.passive/nt)*100 : 0;
      var dW = nt>0 ? (d.nps.detractor/nt)*100 : 0;
      var bar = document.getElementById('rmc-nps-bar');
      bar.querySelector('.seg-prom').style.width = pW+'%';
      bar.querySelector('.seg-pas').style.width = paW+'%';
      bar.querySelector('.seg-det').style.width = dW+'%';
      document.getElementById('rmc-anket-prom').textContent = d.nps ? d.nps.promoter : 0;
      document.getElementById('rmc-anket-pas').textContent = d.nps ? d.nps.passive : 0;
      document.getElementById('rmc-anket-det').textContent = d.nps ? d.nps.detractor : 0;

      var csat = d.csat_ort;
      document.getElementById('rmc-anket-csat').textContent = csat !== null && csat !== undefined ? csat.toFixed(2).replace('.', ',') : '—';
      document.getElementById('rmc-anket-orani').textContent = (d.cevap_orani || 0).toString().replace('.', ',');
      document.getElementById('rmc-anket-sayilar').textContent = (d.toplam_cevap||0)+' / '+(d.toplam_gonderim||0);

      var box = document.getElementById('rmc-anket-yorumlar');
      var yorumlar = d.son_yorumlar || [];
      if(yorumlar.length === 0){
        box.innerHTML = '<div class="rmc-yorum-bos"><i class="bi bi-chat-quote" style="font-size:20px;color:#dde1ec;display:block;margin-bottom:4px;"></i>Henüz yorum yok.</div>';
      } else {
        var html = '';
        yorumlar.forEach(function(y){
          var cls = '';
          if(y.nps_skoru !== null && y.nps_skoru !== undefined){
            if(y.nps_skoru >= 9) cls = 'hi';
            else if(y.nps_skoru <= 6) cls = 'lo';
          }
          var ad = y.ad_soyad ? y.ad_soyad.split(' ')[0] : 'Müşteri';
          var tarih = y.cevap_zamani ? new Date(y.cevap_zamani.replace(' ','T')).toLocaleDateString('tr-TR') : '';
          var skor = y.nps_skoru !== null && y.nps_skoru !== undefined ? ('NPS '+y.nps_skoru) : (y.csat_skoru !== null && y.csat_skoru !== undefined ? ('CSAT '+y.csat_skoru) : '');
          var yorum = (y.genel_yorum || '').toString();
          if(yorum.length > 110) yorum = yorum.substring(0,110)+'…';
          html += '<div class="rmc-yorum '+cls+'">'
                + '<div style="flex:1;"><div class="y-txt">"'+yorum.replace(/</g,'&lt;')+'"</div>'
                + '<div class="y-meta">'+ad+(skor?' · '+skor:'')+(tarih?' · '+tarih:'')+'</div></div></div>';
        });
        box.innerHTML = html;
      }
    }).catch(function(e){console.warn('anket err',e);});
  }

  // ===== SEKME =====
  function renderTab(tab){
    var box = document.getElementById('rmc-tab-content');
    box.innerHTML = '<div style="padding:20px 12px;">'
      + '<div class="rmc-skel rmc-skel-line" style="width:95%"></div>'
      + '<div class="rmc-skel rmc-skel-line" style="width:88%"></div>'
      + '<div class="rmc-skel rmc-skel-line" style="width:80%"></div></div>';
    api('/sekme', 'tab='+tab).then(function(d){
      var rows = d.liste||[];
      if(rows.length === 0){
        var msg = tab==='online-talep' ? 'Bekleyen randevu talebiniz bulunmamakta.'
          : tab==='bugunku-randevu' ? 'Bugün için randevu bulunmuyor.'
          : tab==='acik-adisyon' ? 'Açık adisyon bulunmuyor.'
          : tab==='alacak' ? 'Bekleyen alacak yok.'
          : 'Yaklaşan doğum günü bulunmuyor.';
        box.innerHTML = '<div class="rmc-tab-empty"><i class="bi bi-emoji-smile" style="font-size:32px;color:#dde1ec;display:block;margin-bottom:8px;"></i>'+msg+'</div>';
        return;
      }
      var headers, rowFn;
      switch(tab){
        case 'online-talep':
          headers = ['Tarih','Saat','Müşteri','Telefon','Hizmet'];
          rowFn = function(r){ return '<td>'+(r.tarih||'')+'</td><td>'+(r.saat||'').substr(0,5)+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td>'+(r.hizmet||'-')+'</td>'; };
          break;
        case 'bugunku-randevu':
          headers = ['Saat','Müşteri','Telefon','Hizmet','Personel'];
          rowFn = function(r){ return '<td>'+(r.saat||'').substr(0,5)+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td>'+(r.hizmet||'-')+'</td><td>'+(r.personel||'-')+'</td>'; };
          break;
        case 'acik-adisyon':
          headers = ['Adisyon No','Tarih','Müşteri','Telefon','Kalan'];
          rowFn = function(r){
            var t = r.created_at ? new Date(r.created_at).toLocaleDateString('tr-TR') : '-';
            var kalan = r.kalan !== undefined ? r.kalan : (Number(r.toplam_tutar||0) - Number(r.odenen_tutar||0));
            return '<td>'+(r.adisyon_no||r.id||'-')+'</td><td>'+t+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td style="color:#ff6f91;font-weight:600">'+trFmt(kalan)+' ₺</td>';
          };
          break;
        case 'alacak':
          headers = ['Planlanan Ödeme','Müşteri','Telefon','Tutar'];
          rowFn = function(r){
            var t = r.planlanan_odeme_tarihi ? new Date(r.planlanan_odeme_tarihi).toLocaleDateString('tr-TR') : '-';
            return '<td>'+t+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td>'+trFmt(r.tutar||0)+' ₺</td>';
          };
          break;
        case 'dogum-gunu':
          headers = ['Doğum Tarihi','Müşteri','Telefon'];
          rowFn = function(r){
            var t = r.dogum_tarihi ? new Date(r.dogum_tarihi).toLocaleDateString('tr-TR') : '-';
            return '<td>'+t+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td>';
          };
          break;
      }
      var html = '<table class="rmc-tab-table"><thead><tr>';
      headers.forEach(function(h){ html += '<th>'+h+'</th>'; });
      html += '</tr></thead><tbody>';
      rows.forEach(function(r){ html += '<tr>'+rowFn(r)+'</tr>'; });
      html += '</tbody></table>';
      box.innerHTML = html;
    }).catch(function(e){ box.innerHTML = '<div class="rmc-tab-empty">Veri yüklenemedi.</div>'; console.warn(e); });
  }

  // ===== TIMELINE =====
  function renderTimeline(){
    api('/timeline').then(function(d){
      var box = document.getElementById('rmc-timeline');
      var rows = d.liste||[];
      if(rows.length === 0){ box.innerHTML = '<div class="rmc-tab-empty">Bugün için kayıt yok.</div>'; return; }
      var simdi = d.simdi || '00:00';
      var html = '';
      var nowInserted = false;
      rows.forEach(function(r){
        if(!nowInserted && r.saat > simdi){
          html += '<div class="rmc-timeline-now"><span>Şimdi · '+simdi+'</span></div>';
          nowInserted = true;
        }
        html += '<div class="rmc-timeline-item '+(r.tip==='tahsilat'?'tahsilat':'')+'">'
              + '<div class="t-saat">'+r.saat+'</div>'
              + '<div class="t-baslik">'+(r.baslik||'-')+'</div></div>';
      });
      if(!nowInserted){ html += '<div class="rmc-timeline-now"><span>Şimdi · '+simdi+'</span></div>'; }
      box.innerHTML = html;
    }).catch(function(e){console.warn('timeline err',e);});
  }

  // ===== PERSONEL =====
  function renderPersonel(){
    api('/personel-durum').then(function(d){
      var box = document.getElementById('rmc-personel-list');
      var rows = d.personeller||[];
      if(rows.length === 0){ box.innerHTML = '<div class="rmc-tab-empty">Aktif personel yok.</div>'; return; }
      var html = '';
      rows.forEach(function(p){
        html += '<div class="rmc-personel-row">'
              + '<div class="ad" title="'+(p.ad||'')+'">'+(p.ad||'-')+'</div>'
              + '<div class="bar"><div class="bar-fill" data-pct="'+p.doluluk+'"></div></div>'
              + '<div class="pct">'+p.doluluk+'%</div></div>';
      });
      box.innerHTML = html;
      setTimeout(function(){
        box.querySelectorAll('.bar-fill').forEach(function(el){ el.style.width = el.getAttribute('data-pct')+'%'; });
      }, 50);
    }).catch(function(e){console.warn('personel err',e);});
  }

  // ===== ÇARKIFELEK =====
  function renderCark(period){
    api('/cark', 'period='+period).then(function(d){
      var t = Number(d.toplam_cevrim)||0;
      animateCount(document.getElementById('rmc-cark-toplam'), t);
      animateCount(document.getElementById('rmc-cark-kazanan'), Number(d.kazanan)||0);
      animateCount(document.getElementById('rmc-cark-bos'), Number(d.bos)||0);
      var kupon = d.kupon || {verilen:0,kullanilan:0,orani:0};
      document.getElementById('rmc-cark-kupon-v').textContent = kupon.verilen;
      document.getElementById('rmc-cark-kupon-k').textContent = kupon.kullanilan;
      document.getElementById('rmc-cark-kupon-pct').textContent = (kupon.orani || 0)+'%';

      // ring + center text
      var rate = Number(d.kazanma_orani)||0;
      var dash = 276.46, off = dash * (1 - rate/100);
      var ring = document.getElementById('rmc-cark-ring');
      if(ring) ring.setAttribute('stroke-dashoffset', off);
      var rateEl = document.getElementById('rmc-cark-rate');
      if(rateEl){
        var s = performance.now(), dur = 1100;
        function step(ts){
          var k = Math.min(1,(ts-s)/dur);
          var eased = 1 - Math.pow(1-k, 4);
          rateEl.textContent = Math.round(rate*eased)+'%';
          if(k<1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }

      // son kazananlar
      var box = document.getElementById('rmc-cark-son');
      var rows = d.son_kazananlar || [];
      if(rows.length === 0){
        box.innerHTML = '<div class="rmc-yorum-bos"><i class="bi bi-trophy" style="font-size:20px;color:#dde1ec;display:block;margin-bottom:4px;"></i>Henüz kazanan yok.</div>';
      } else {
        var html = '';
        rows.forEach(function(r){
          var ad = r.musteri ? r.musteri.split(' ')[0] : 'Müşteri';
          var od = (r.dilim_ismi || r.tip || 'Ödül');
          html += '<div class="rmc-cark-kazanan-row"><i class="bi bi-trophy-fill" style="color:#f7c948"></i>'
                + '<span class="ad">'+ad+'</span>'
                + '<span class="od">'+od+'</span></div>';
        });
        box.innerHTML = html;
      }
    }).catch(function(e){console.warn('cark err',e);});
  }

  // ===== Event bağlantıları =====
  document.querySelectorAll('.rmc-period').forEach(function(grp){
    var target = grp.getAttribute('data-target');
    grp.querySelectorAll('button').forEach(function(btn){
      btn.addEventListener('click', function(){
        grp.querySelectorAll('button').forEach(function(b){ b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        var p = btn.getAttribute('data-period');
        if(target === 'kasa') renderKasa(p);
        else if(target === 'randevu') renderRandevu(p);
        else if(target === 'anket') renderAnket(p);
        else if(target === 'cark') renderCark(p);
      });
    });
  });

  document.querySelectorAll('.rmc-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.rmc-tab').forEach(function(b){ b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      renderTab(btn.getAttribute('data-tab'));
    });
  });

  // ===== İlk yükleme — paralel (sadece yetkili bilesenler icin) =====
  @yetki('rapor.kasa')
  renderKasa('30d');
  // Kart 30 gunluk acilsa da hero "Bugun Gelir" bugunku toplami gostersin
  api('/kasa','period=daily').then(function(d){
    var hg = document.getElementById('rmc-hero-gelir');
    if(hg) animateCount(hg, Number(d.toplam)||0);
  }).catch(function(){});
  @endyetki
  @yetki('randevu.takvim_gor')
  renderRandevu('daily');
  @endyetki
  @yetki('pazarlama.anket_yonet')
  renderAnket('30d');
  @endyetki
  @yetki('pazarlama.cark_yonet')
  renderCark('30d');
  @endyetki
  @if(!empty($_ilkSekme))
  renderTab('{{ $_ilkSekme }}');
  @endif
  @yetki('rapor.kasa')
  renderTimeline();
  @endyetki
  @if(!$_dashUid || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, 'personel.liste_gor') || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, 'rapor.personel_performans'))
  renderPersonel();
  @endif
}

(function rmcDashBoot(){
  function ready(){
    if(typeof Chart === 'undefined'){
      // chart.js henuz yuklenmedi, kucuk bir gecikme
      return setTimeout(ready, 50);
    }
    rmcDashInit();
  }
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }
})();
</script>

{{-- ====== DOĞUM GÜNÜ POPUP (Vanilla — Chart/Swal bağımsız) ====== --}}
<style>
.rmc-bday-overlay{position:fixed;inset:0;background:rgba(20,20,40,.55);backdrop-filter:blur(4px);z-index:99999;display:flex;align-items:center;justify-content:center;animation:rmcBdayFade .2s ease-out;}
@keyframes rmcBdayFade{from{opacity:0}to{opacity:1}}
.rmc-bday-box{background:#fff;border-radius:16px;padding:28px 24px;width:92%;max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,.25);text-align:center;animation:rmcBdayPop .25s cubic-bezier(.34,1.56,.64,1);}
@keyframes rmcBdayPop{from{transform:scale(.85);opacity:0}to{transform:scale(1);opacity:1}}
.rmc-bday-emoji{font-size:56px;line-height:1;margin-bottom:10px;}
.rmc-bday-lbl{font-size:14px;color:#666;margin:0 0 4px;}
.rmc-bday-name{font-size:22px;font-weight:700;color:#e63b6e;margin:0 0 8px;}
.rmc-bday-q{font-size:14px;color:#444;margin:0 0 6px;}
.rmc-bday-info{font-size:12px;color:#999;margin:0 0 18px;}
.rmc-bday-btns{display:flex;flex-direction:column;gap:8px;}
.rmc-bday-btn{padding:12px 16px;border:0;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:transform .1s,filter .15s;}
.rmc-bday-btn:hover{filter:brightness(.95);}
.rmc-bday-btn:active{transform:scale(.97);}
.rmc-bday-btn.ok{background:#1fbf6f;color:#fff;}
.rmc-bday-btn.deny{background:#9097ad;color:#fff;}
.rmc-bday-btn.no{background:#ff5c8a;color:#fff;}
.rmc-bday-msg{margin-top:14px;padding:10px;border-radius:8px;font-size:13px;}
.rmc-bday-msg.ok{background:#e8f8ee;color:#1a7f47;}
.rmc-bday-msg.err{background:#fde8ec;color:#a01035;}
</style>
{{-- Dogum gunu popup'i musteriye WhatsApp/SMS gonderir → en az birinin yetkisi gerekli --}}
@if(!$_dashUid || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, 'pazarlama.whatsapp_gonder') || \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_dashUid, $isletme->id, 'pazarlama.sms_gonder'))
<script>
(function rmcBdayPopupInit(){
  var subeParam = @json($apiSubeParam);
  var lsKey = 'rmc_dogumgunu_kapatildi_' + new Date().toISOString().slice(0,10);

  function fetchJSON(url, opts){
    return fetch(url, Object.assign({credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}, opts||{}))
      .then(function(r){ return r.json(); });
  }

  function gosterModal(m, onResult){
    var ov = document.createElement('div');
    ov.className = 'rmc-bday-overlay';
    ov.innerHTML =
      '<div class="rmc-bday-box">'
    + '  <div class="rmc-bday-emoji">🎂</div>'
    + '  <p class="rmc-bday-lbl">Bugün</p>'
    + '  <h3 class="rmc-bday-name"></h3>'
    + '  <p class="rmc-bday-q">Müşterinize doğum günü mesajı göndermek ister misiniz?</p>'
    + '  <p class="rmc-bday-info">Önce WhatsApp denenecek, başarısız olursa SMS gönderilecek.</p>'
    + '  <div class="rmc-bday-btns">'
    + '    <button class="rmc-bday-btn ok" data-r="ok">✉️ Evet, Gönder</button>'
    + '    <button class="rmc-bday-btn no" data-r="no">Hayır</button>'
    + '  </div>'
    + '  <div class="rmc-bday-result"></div>'
    + '</div>';
    ov.querySelector('.rmc-bday-name').textContent = m.name || 'Müşteri';
    document.body.appendChild(ov);

    var sonucBox = ov.querySelector('.rmc-bday-result');
    var btnsBox = ov.querySelector('.rmc-bday-btns');

    ov.querySelectorAll('.rmc-bday-btn').forEach(function(b){
      b.addEventListener('click', function(){
        var r = b.getAttribute('data-r');
        if(r === 'ok'){
          btnsBox.style.opacity = '.4';
          btnsBox.style.pointerEvents = 'none';
          sonucBox.innerHTML = '<div class="rmc-bday-msg ok">Gönderiliyor…</div>';
          gonderim(m, function(out){
            if(out && out.ok){
              sonucBox.innerHTML = '<div class="rmc-bday-msg ok">✅ '+(out.mesaj || 'Gönderildi.')+'</div>';
            } else {
              sonucBox.innerHTML = '<div class="rmc-bday-msg err">⚠️ '+((out && out.mesaj) ? out.mesaj : 'Hata oluştu.')+'</div>';
            }
            setTimeout(function(){ ov.remove(); onResult && onResult(); }, 1800);
          });
        } else {
          // Hayir: o gun bu musteri icin bir daha sorma
          try {
            var arr = JSON.parse(localStorage.getItem(lsKey) || '[]');
            if(arr.indexOf(m.id) === -1) arr.push(m.id);
            localStorage.setItem(lsKey, JSON.stringify(arr));
          } catch(e){}
          ov.remove(); onResult && onResult();
        }
      });
    });
  }

  function gonderim(m, cb){
    var csrf = '';
    var meta = document.querySelector('meta[name="csrf-token"]');
    if(meta) csrf = meta.getAttribute('content');
    if(!csrf){
      var inp = document.querySelector('input[name="_token"]');
      if(inp) csrf = inp.value;
    }
    var fd = new FormData();
    fd.append('musteri_id', m.id);
    fd.append('_token', csrf);
    fetch('/isletmeyonetim/dogum-gunu-mesaj-gonder' + (subeParam || ''), {
      method:'POST',
      credentials:'same-origin',
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN': csrf},
      body: fd,
    }).then(function(r){ return r.json(); }).then(cb).catch(function(){ cb({ok:false, mesaj:'Sunucuya bağlanılamadı.'}); });
  }

  function basla(){
    fetchJSON('/isletmeyonetim/api/dashboard/bugun-dogum-gunu' + (subeParam || ''))
      .then(function(d){
        var liste = (d && d.liste ? d.liste : []).filter(function(m){ return !m.gonderildi; });
        try {
          var kapatilanlar = JSON.parse(localStorage.getItem(lsKey) || '[]');
          liste = liste.filter(function(m){ return kapatilanlar.indexOf(m.id) === -1; });
        } catch(e){}
        if(liste.length === 0){ console.log('[BDAY] bugun gosterilecek dogum gunu yok'); return; }
        console.log('[BDAY] popup acilacak, musteri sayisi:', liste.length);

        var idx = 0;
        function sira(){
          if(idx >= liste.length) return;
          gosterModal(liste[idx], function(){ idx++; setTimeout(sira, 300); });
        }
        sira();
      })
      .catch(function(e){ console.warn('[BDAY] api hatasi', e); });
  }

  // Window load eventinde calistir — Chart/Swal/diger bagimli scriptlerden bagimsiz
  if(document.readyState === 'complete'){
    setTimeout(basla, 800);
  } else {
    window.addEventListener('load', function(){ setTimeout(basla, 800); });
  }

  // Manuel test icin global tetikleyici
  window.rmcBdayTrigger = basla;
})();
</script>
@endif

@include('isletmeadmin.partials.patron_asistan')

@endsection
