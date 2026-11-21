@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
   $J = function ($v) { return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); };
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet">
<style>
   .wa { --mor:#5b1fa8; --mor2:#7c3aed; --mor3:#a855f7; --lav:#f3eefc; --lav2:#e9dffb; --metin:#1f1638; --gri:#6b6483; --cizgi:#ece6f6;
         --yesil:#16a34a; --yesil-acik:#dcfce7; --golge:0 6px 24px rgba(76,29,149,.07); color:var(--metin); background:#f7f4fc; margin:-6px -6px 0; padding:18px; border-radius:18px; }
   .wa * { box-sizing:border-box; }
   .wa-ust { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
   .wa-sekmeler { display:flex; gap:4px; background:#fff; border-radius:16px; padding:6px; box-shadow:var(--golge); overflow-x:auto; max-width:100%; scrollbar-width:none; }
   .wa-sekmeler::-webkit-scrollbar { display:none; }
   .wa-sekme { border:none; background:transparent; color:#2c2246; padding:10px 16px; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; white-space:nowrap; display:flex; align-items:center; gap:8px; }
   .wa-sekme i { font-size:16px; color:#5b4b80; }
   .wa-sekme:hover { background:var(--lav); }
   .wa-sekme.aktif { background:linear-gradient(135deg,#4c1d95,#7c3aed); color:#fff; box-shadow:0 6px 16px rgba(91,31,168,.35); }
   .wa-sekme.aktif i { color:#fff; }
   .wa-donem { display:flex; align-items:center; gap:8px; background:#fff; border-radius:14px; padding:4px 6px 4px 14px; box-shadow:var(--golge); }
   .wa-donem i { color:var(--mor2); font-size:17px; }
   .wa-donem select { border:none; background:transparent; font-size:14px; font-weight:600; color:var(--metin); padding:8px 6px; cursor:pointer; outline:none; }
   .wa-panel { display:none; } .wa-panel.aktif { display:block; }

   /* hero */
   .wa-hero { position:relative; overflow:hidden; background:linear-gradient(120deg,#3b0f80 0%,#5b1fa8 38%,#7c3aed 72%,#9d5cf0 100%); color:#fff; border-radius:22px; padding:26px 30px; display:flex; align-items:center; gap:22px; margin-bottom:18px; box-shadow:0 14px 34px rgba(76,29,149,.28); }
   .wa-hero::before { content:''; position:absolute; right:-80px; top:-120px; width:420px; height:420px; border-radius:50%; background:radial-gradient(circle,rgba(255,255,255,.16),rgba(255,255,255,0) 65%); }
   .wa-hero::after { content:''; position:absolute; right:120px; bottom:-160px; width:360px; height:260px; border-radius:50%; border:2px solid rgba(255,255,255,.12); }
   .wa-hero-ikon { flex:0 0 auto; width:78px; height:78px; border-radius:50%; background:#25D366; display:flex; align-items:center; justify-content:center; box-shadow:0 0 0 6px rgba(255,255,255,.18); position:relative; z-index:1; }
   .wa-hero-ikon i { font-size:42px; color:#fff; }
   .wa-hero-bolum { position:relative; z-index:1; flex:1 1 0; min-width:0; display:flex; align-items:center; gap:16px; padding:0 22px; }
   /* Bolum ayirici: belirgin, ortasi parlak uclari yumusak dikey cizgi */
   .wa-hero-bolum::before { content:""; position:absolute; left:0; top:-6px; bottom:-6px; width:2px; border-radius:2px;
      background:linear-gradient(180deg,rgba(255,255,255,0),rgba(255,255,255,.75) 25%,rgba(255,255,255,.75) 75%,rgba(255,255,255,0)); box-shadow:0 0 8px rgba(255,255,255,.35); }
   .wa-hero-bolum.ilk::before { display:none; }
   .wa-hero-bolum.ilk { border-left:none; padding-left:4px; }
   .wa-hero-bolum > i { font-size:40px; opacity:.92; }
   .wa-hero-e { font-size:15px; opacity:.92; }
   .wa-hero-d { font-size:34px; font-weight:800; line-height:1.1; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
   .wa-hero-d small { font-size:18px; font-weight:600; }
   .wa-hero-yazi { flex:0 0 auto; margin-left:auto; position:relative; z-index:1; font-family:'Caveat',cursive; font-size:30px; line-height:1.05; transform:rotate(-6deg); text-align:center; padding-right:6px; }
   .wa-hero-yazi svg { display:block; margin:2px auto 0; }
   /* Sag taraf: daha koyu katman + slogan arkasinda yaprak (lotus) deseni */
   .wa-hero-katman { position:absolute; top:0; right:0; bottom:0; width:34%; z-index:0; pointer-events:none; border-radius:60% 0 0 60% / 100% 0 0 100%;
      background:linear-gradient(90deg,rgba(46,16,101,0) 0%,rgba(46,16,101,.38) 35%,rgba(38,10,88,.55) 100%); }
   .wa-hero-yaprak { position:absolute; right:175px; bottom:-14px; width:150px; height:150px; z-index:0; pointer-events:none; opacity:.22; filter:drop-shadow(0 0 14px rgba(255,255,255,.35)); }
   .wa-trend { display:inline-flex; align-items:center; gap:3px; font-size:12.5px; font-weight:700; padding:3px 9px; border-radius:999px; }
   .wa-hero .wa-trend { background:rgba(22,163,74,.85); color:#fff; }
   .wa-hero .wa-trend.dus { background:rgba(220,38,38,.85); }
   .wa-uyari { display:flex; gap:10px; flex-wrap:wrap; margin:-6px 0 16px; }
   .wa-uyari a { display:inline-flex; align-items:center; gap:8px; background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; border-radius:12px; padding:8px 12px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }

   /* kpi */
   .wa-kpiler { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:18px; }
   .wa-kpi { background:#fff; border-radius:20px; padding:18px 18px 10px; box-shadow:var(--golge); position:relative; overflow:hidden; min-height:150px; }
   .wa-kpi-ust { display:flex; align-items:center; gap:12px; }
   .wa-kpi-ikon { flex:0 0 auto; width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; }
   .wa-kpi-d { font-size:28px; font-weight:800; line-height:1.05; letter-spacing:-.5px; }
   .wa-kpi-e { font-size:13.5px; color:#3a3154; margin-top:2px; }
   .wa-kpi .wa-trend { position:absolute; right:16px; top:58px; background:none; padding:0; color:var(--yesil); }
   .wa-kpi .wa-trend.dus { color:#dc2626; }
   .wa-kpi svg.spark { display:block; width:100%; height:46px; margin-top:8px; }
   .wa-k-mor .wa-kpi-ikon { background:#efe6fd; color:#6d28d9; }
   .wa-k-lila .wa-kpi-ikon { background:#ede9fe; color:#7c3aed; }
   .wa-k-mavi .wa-kpi-ikon { background:#e0edff; color:#2563eb; }
   .wa-k-tur .wa-kpi-ikon { background:#ffedd5; color:#f97316; }
   .wa-k-yesil .wa-kpi-ikon { background:#dcfce7; color:#16a34a; }

   .wa-bolum-baslik { display:flex; align-items:center; gap:10px; font-size:18px; font-weight:800; margin:6px 0 12px; }
   .wa-bolum-baslik i { color:var(--mor2); }
   .wa-kazanclar { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; margin-bottom:18px; }
   .wa-kaz { background:#fff; border-radius:18px; padding:16px; box-shadow:var(--golge); }
   .wa-kaz .wa-kpi-ikon { width:40px; height:40px; font-size:19px; border-radius:12px; margin-bottom:10px; }
   .wa-kaz .d { font-size:22px; font-weight:800; letter-spacing:-.3px; }
   .wa-kaz .e { font-size:13px; font-weight:600; color:#3a3154; margin-top:2px; }
   .wa-kaz .a { font-size:12px; color:var(--gri); margin-top:4px; line-height:1.35; }
   .wa-kaz .m { display:inline-flex; align-items:center; gap:4px; margin-top:5px; padding:2px 8px; border-radius:20px; background:rgba(255,255,255,.65); font-size:11px; font-weight:600; color:#5b5570; white-space:nowrap; }
   .wa-kaz .m i { font-size:11px; }
   /* Kazanc kartlari: her biri kendi renginde yumusak gradyan + ince renkli cerceve + beyaz ikon kutusu */
   .wa-kaz { position:relative; overflow:hidden; border:1px solid transparent; transition:transform .15s, box-shadow .15s; }
   .wa-kaz:hover { transform:translateY(-2px); box-shadow:0 12px 28px rgba(91,33,182,.12); }
   .wa-kaz::after { content:''; position:absolute; right:-28px; top:-28px; width:96px; height:96px; border-radius:50%; background:rgba(255,255,255,.45); }
   /* Ikon kutusu: kartin renginde koyu tonlu gradyan (beyaz degil) + dolgulu ikon */
   .wa-kaz .wa-kpi-ikon { position:relative; z-index:1; box-shadow:0 6px 14px rgba(31,22,56,.10); }
   .wa-kaz.wa-k-mor .wa-kpi-ikon { background:linear-gradient(140deg,#e7dafd,#cdb4fb); color:#5b21b6; }
   .wa-kaz.wa-k-lila .wa-kpi-ikon { background:linear-gradient(140deg,#e2e3ff,#c3c4fb); color:#4338ca; }
   .wa-kaz.wa-k-tur .wa-kpi-ikon { background:linear-gradient(140deg,#ffe6cc,#fdc48d); color:#ea580c; }
   .wa-kaz.wa-k-mavi .wa-kpi-ikon { background:linear-gradient(140deg,#dbeaff,#b4d2fd); color:#1d4ed8; }
   .wa-kaz.wa-k-yesil .wa-kpi-ikon { background:linear-gradient(140deg,#d6f7e2,#a8e9c0); color:#15803d; }
   .wa-kaz.wa-k-pembe .wa-kpi-ikon { background:linear-gradient(140deg,#ffdcec,#fbb1d1); color:#be185d; }
   .wa-kaz.wa-k-mor { background:linear-gradient(140deg,#f4ecff 0%,#e6d7ff 100%); border-color:#dccbfd; }
   .wa-kaz.wa-k-lila { background:linear-gradient(140deg,#eef0ff 0%,#dcdcff 100%); border-color:#cfd0fb; }
   .wa-kaz.wa-k-tur { background:linear-gradient(140deg,#fff4e8 0%,#ffe1c2 100%); border-color:#fdd3a6; }
   .wa-kaz.wa-k-mavi { background:linear-gradient(140deg,#ebf4ff 0%,#d6e7ff 100%); border-color:#c3dafd; }
   .wa-kaz.wa-k-yesil { background:linear-gradient(140deg,#eafbf0 0%,#d0f4de 100%); border-color:#b9ebcb; }
   .wa-kaz.wa-k-pembe { background:linear-gradient(140deg,#fff0f7 0%,#ffd9ea 100%); border-color:#fcc6dd; }
   .wa-kaz .a { color:#4b4563; }
   .wa-kaz svg.spark { display:block; width:100%; height:40px; margin-top:10px; position:relative; z-index:1; }
   .wa-kaz .d, .wa-kaz .e, .wa-kaz .a { position:relative; z-index:1; }
   /* Kart ici duzen (tasarim): buyuk ikon | rakam + ad ; altta grafik */
   .wa-kaz { padding:18px 18px 10px; display:flex; flex-direction:column; }
   .wa-kaz-ust { display:flex; align-items:center; gap:14px; position:relative; z-index:1; }
   .wa-kaz-ic { min-width:0; }
   .wa-kaz .wa-kpi-ikon { width:56px; height:56px; font-size:28px; border-radius:16px; margin:0; flex:0 0 auto; }
   .wa-kaz .d { font-size:24px; line-height:1.1; letter-spacing:-.3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
   .wa-kaz .e { font-size:13.5px; font-weight:500; color:#3a3154; margin-top:3px; }
   .wa-kaz svg.spark { margin-top:auto; padding-top:12px; height:52px; }

   /* kartlar */
   .wa-kart { background:#fff; border-radius:22px; padding:20px 22px; box-shadow:var(--golge); min-width:0; }
   .wa-kart-bas { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap; }
   .wa-kart-bas h4 { margin:0; font-size:19px; font-weight:800; display:flex; align-items:center; gap:10px; color:var(--metin); }
   .wa-orta { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(0,1fr); gap:18px; margin-bottom:18px; }
   .wa-alt-sira { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:18px; margin-bottom:18px; }
   .wa-toggle { display:flex; gap:4px; background:#f4f1fa; border-radius:12px; padding:4px; }
   .wa-toggle button { border:none; background:transparent; padding:7px 16px; border-radius:9px; font-size:13.5px; font-weight:600; color:#3a3154; cursor:pointer; }
   .wa-toggle button.aktif { background:linear-gradient(135deg,#4c1d95,#7c3aed); color:#fff; }
   .wa-lejant { display:flex; justify-content:center; gap:18px; font-size:12.5px; color:var(--gri); margin-bottom:4px; }
   .wa-lejant span::before { content:''; display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:6px; vertical-align:middle; }
   .wa-lejant .r::before { background:#6d28d9; } .wa-lejant .m::before { background:#c4b5fd; }
   .wa-grafik { position:relative; height:270px; }
   .wa-grafik svg { width:100%; height:100%; display:block; }
   .wa-ipucu { position:absolute; pointer-events:none; background:#fff; border-radius:12px; box-shadow:0 8px 24px rgba(31,22,56,.16); padding:8px 12px; font-size:12.5px; display:none; white-space:nowrap; z-index:2; }
   .wa-ipucu b { display:block; font-size:13px; margin-bottom:2px; }

   .wa-tumu { border:none; background:var(--lav); color:#4c1d95; font-weight:700; font-size:13px; padding:8px 14px; border-radius:12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
   .wa-konusma { display:flex; align-items:center; gap:14px; padding:12px 4px; border-top:1px solid #f1edf8; cursor:pointer; }
   .wa-konusma:first-child { border-top:none; }
   .wa-konusma:hover { background:#faf7ff; border-radius:12px; }
   .wa-avatar { flex:0 0 auto; width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:#fff; position:relative; overflow:hidden; }
   .wa-avatar img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; background:#fff; }
   .wa-konusma .ic { flex:1; min-width:0; }
   .wa-konusma .ad { font-weight:700; font-size:15px; }
   .wa-konusma .mt { font-size:13.5px; color:var(--gri); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
   .wa-konusma .sa { font-size:12.5px; color:var(--gri); flex:0 0 auto; }
   .wa-konusma .bi-chevron-right { color:#9b92b3; }
   .wa-hap { flex:0 0 auto; font-size:12px; font-weight:700; padding:6px 12px; border-radius:999px; white-space:nowrap; }
   /* Genel Bakis "Son WhatsApp Konusmalari" (tasarimdaki satir) */
   .wa-konusma.ozet { gap:16px; padding:12px 4px; }
   .wa-konusma.ozet .wa-avatar { width:52px; height:52px; font-size:17px; box-shadow:0 3px 8px rgba(31,22,56,.15); }
   .wa-konusma.ozet .ad { font-size:16px; color:#1e1b4b; }
   .wa-konusma.ozet .mt { font-size:14px; color:#1e1b4b; }
   .wa-konusma.ozet .sa { font-size:14px; color:#1e1b4b; }
   .wa-konusma.ozet .wa-hap { font-size:13.5px; font-weight:600; padding:9px 16px; min-width:150px; text-align:center; }
   .wa-konusma.ozet .wa-hap.y { background:#dcfce7; color:#15803d; }
   .wa-konusma.ozet .wa-hap.m { background:#f3e8ff; color:#7e22ce; }
   .wa-konusma.ozet .wa-hap.g { background:#e9e7fb; color:#3730a3; }
   .wa-konusma.ozet .bi-chevron-right { color:#1e1b4b; font-size:18px; -webkit-text-stroke:1px #1e1b4b; }
   .wa-hap.y { background:var(--yesil-acik); color:#15803d; } .wa-hap.m { background:#f1e8ff; color:#7c3aed; }
   .wa-hap.g { background:#eef2f7; color:#475569; } .wa-hap.s { background:#fef3c7; color:#92400e; } .wa-hap.k { background:#fee2e2; color:#b91c1c; }

   .wa-hizli { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
   .wa-hizli a { border:none; border-radius:18px; padding:22px 10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px; font-size:14.5px; font-weight:700; color:var(--metin); text-align:center; text-decoration:none; cursor:pointer; transition:transform .15s; }
   .wa-hizli a:hover { transform:translateY(-2px); color:var(--metin); text-decoration:none; }
   .wa-hizli i { font-size:30px; }
   .wa-h1 { background:#f1e8ff; } .wa-h1 i { color:#7c3aed; }
   .wa-h2 { background:#dff7ef; } .wa-h2 i { color:#0f9d6e; }
   .wa-h3 { background:#ffeedd; } .wa-h3 i { color:#f97316; }
   .wa-h4 { background:#e3edff; } .wa-h4 i { color:#2563eb; }
   .wa-ai { background:linear-gradient(120deg,#f3ecff,#ebe2fd); display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
   .wa-ai .robot { flex:0 0 auto; width:170px; height:auto; align-self:flex-end; margin:-6px 0 -20px -8px; -webkit-mask-image:radial-gradient(ellipse 62% 66% at 50% 58%, #000 58%, transparent 98%); mask-image:radial-gradient(ellipse 62% 66% at 50% 58%, #000 58%, transparent 98%); }
   .wa-ai .orta-ic { flex:1; min-width:200px; }
   .wa-ai h4 { margin:0; font-size:18px; font-weight:800; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
   .wa-aktif { background:var(--yesil); color:#fff; font-size:12px; font-weight:700; padding:4px 10px; border-radius:999px; display:inline-flex; align-items:center; gap:5px; }
   .wa-aktif.kapali { background:#9ca3af; }
   .wa-ai p { margin:8px 0 12px; font-size:14px; color:#3a3154; }
   .wa-btn { border:none; border-radius:12px; padding:10px 18px; font-size:14px; font-weight:700; cursor:pointer; background:linear-gradient(135deg,#3b0f80,#6d28d9); color:#fff; display:inline-flex; align-items:center; gap:8px; }
   .wa-btn.ikincil { background:#fff; color:#5b1fa8; border:1px solid #d8c8f5; }
   .wa-btn:disabled { opacity:.55; cursor:default; }
   .wa-liste-ok { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; font-size:14px; }
   .wa-liste-ok li { display:flex; align-items:center; gap:8px; }
   .wa-liste-ok i { font-size:19px; color:var(--yesil); }
   .wa-liste-ok li.kapali i { color:#cbd5e1; } .wa-liste-ok li.kapali { color:#9ca3af; }
   .wa-liste-ok li.kapali[data-git] { cursor:pointer; }
   .wa-ac { font-size:11.5px; font-weight:700; color:#6d28d9; background:#efe6fd; border-radius:999px; padding:2px 8px; margin-left:4px; }
   .wa-paketbar { margin-top:10px; font-size:12.5px; color:#3a3154; }
   .wa-bar { height:9px; background:#e7defa; border-radius:99px; overflow:hidden; margin-top:5px; }
   .wa-bar > i { display:block; height:100%; background:linear-gradient(90deg,#7c3aed,#4c1d95); border-radius:99px; }
   .wa-bar.uyari > i { background:linear-gradient(90deg,#f59e0b,#d97706); } .wa-bar.dolu > i { background:#dc2626; }
   .wa-huni { display:flex; flex-direction:column; gap:12px; }
   .wa-huni .s { display:flex; align-items:center; gap:12px; font-size:13.5px; }
   .wa-huni .s span:first-child { width:140px; color:#3a3154; font-weight:600; }
   .wa-huni .s .wa-bar { flex:1; height:14px; margin:0; }
   .wa-huni .s b { width:52px; text-align:right; }
   .wa-etiketler { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
   .wa-etiketler span { background:var(--lav); color:#4c1d95; font-size:12.5px; font-weight:600; padding:6px 11px; border-radius:999px; }

   /* diger sekmeler */
   .wa-alt { font-size:13px; color:var(--gri); }
   .wa-tablo { width:100%; border-collapse:collapse; font-size:13.5px; }
   .wa-tablo th { text-align:left; color:var(--gri); font-weight:600; padding:10px 8px; border-bottom:1px solid var(--cizgi); white-space:nowrap; }
   .wa-tablo td { padding:10px 8px; border-bottom:1px solid #f4f0f8; vertical-align:top; }
   .wa-tablo-sar { overflow-x:auto; }
   .wa-iki { display:grid; grid-template-columns:minmax(280px,360px) minmax(0,1fr); gap:18px; }
   .wa-liste { max-height:640px; overflow:auto; display:flex; flex-direction:column; gap:2px; }
   /* Liste sagdaki sohbet kadar uzar (alti bos kalmaz); kendi icinde kayar. Yukseklige katki vermez (flex-basis 0). */
   .wa-liste-kart { display:flex; flex-direction:column; min-height:640px; }
   .wa-liste-kart .wa-liste { flex:1 1 0; min-height:0; max-height:none; }
   .wa-liste .wa-konusma.secili { background:#f3ecff; border-radius:12px; }
   /* Isletmeye sorulan konu: sonuc seridi */
   .ts-kart { border-radius:16px; padding:6px 14px; margin:4px 0 14px; background:#fffbeb; border:1px solid #fde68a; }
   .ts-kart.cevaplandi { background:#f0fdf4; border-color:#bbf7d0; } .ts-kart.cevapsiz { background:#fef2f2; border-color:#fecaca; }
   .ts-adim { display:flex; gap:12px; align-items:flex-start; padding:9px 0; border-top:1px dashed rgba(0,0,0,.08); } .ts-adim:first-child { border-top:none; }
   .ts-ik { flex:0 0 auto; width:30px; height:30px; border-radius:10px; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; }
   .ts-adim > div { flex:1; min-width:0; } .ts-adim b { font-size:14px; display:block; } .ts-adim small { display:block; font-size:12.5px; color:#4b4563; margin-top:2px; line-height:1.45; }
   .ts-z { flex:0 0 auto; font-size:12px; color:var(--gri); white-space:nowrap; }
   .ts-kart.ts-yarim { background:#fef2f2; border-color:#fecaca; }
   .ng-kart { border:1px solid #e9e2f5; border-radius:14px; padding:8px 14px; margin:4px 0 14px; background:#fbfaff; }
   .ng-kart summary { cursor:pointer; font-size:13.5px; font-weight:700; color:#3a3154; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
   .ng-ic { margin-top:8px; display:flex; flex-direction:column; gap:8px; max-height:320px; overflow:auto; }
   .ng-s { background:#fff7ed; border-radius:10px; padding:8px 10px; font-size:13px; } .ng-s.a { background:#f3ecff; } .ng-s.o { background:#eef2f7; }
   .ng-s b { font-size:12.5px; } .ng-s div { margin-top:3px; white-space:pre-wrap; color:#3a3154; } .ng-s .wa-tumu { margin-top:6px; padding:5px 10px; font-size:12px; }
   .ts-adim.bekle b { color:#b91c1c; } .ts-adim.bekle .wa-btn { padding:8px 14px; font-size:13px; flex:0 0 auto; }
   .wa-konusma.liste { align-items:flex-start; gap:12px; padding:12px 10px; border-radius:14px; border-top:none; margin-bottom:2px; }
   .wa-konusma.liste .ust { display:flex; justify-content:space-between; align-items:baseline; gap:8px; }
   .wa-konusma.liste .ad { font-size:14.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; }
   .wa-konusma.liste .sa { font-size:12px; }
   .wa-konusma.liste .mt { font-size:13px; margin-top:2px; }
   .wa-konusma.liste .mt i { color:#db2777; }
   .wa-konusma.liste .wa-hap { display:inline-block; margin-top:7px; font-size:11.5px; font-weight:700; padding:3px 10px; }
   .wa-konusma.liste.secili { background:linear-gradient(135deg,#f3ecff,#ebe2fd); box-shadow:inset 3px 0 0 #7c3aed; }
   .wa-sohbet { display:flex; flex-direction:column; gap:8px; max-height:560px; overflow:auto; padding:6px 2px; }
   .wa-balon { max-width:82%; padding:9px 12px; border-radius:14px; font-size:14px; white-space:pre-wrap; word-wrap:break-word; line-height:1.45; }
   .wa-balon.musteri { align-self:flex-start; background:#f1f5f9; border-top-left-radius:4px; }
   .wa-balon.asistan { align-self:flex-end; background:#efe6fd; border-top-right-radius:4px; }
   .wa-balon.personel { align-self:flex-end; background:#dcfce7; border-top-right-radius:4px; }
   .wa-balon .bz { display:block; text-align:right; font-size:11px; color:#8a8399; margin-top:3px; }
   .wa-bas-btn { display:flex; gap:8px; flex-wrap:wrap; }
   .wa-balon .r { display:block; font-size:11px; font-weight:700; color:var(--gri); margin-bottom:2px; }
   .wa-girdi { border:1px solid #ddd3ea; border-radius:11px; padding:9px 12px; font-size:14px; width:100%; background:#fff; }
   .wa-satir { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:14px; }
   .wa-anahtar { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; padding:14px 0; border-bottom:1px solid #f4f0f8; }
   .wa-anahtar .t b { font-size:15px; } .wa-anahtar .t div { font-size:13px; color:var(--gri); margin-top:3px; }
   .wa-anahtar input[type=checkbox] { width:20px; height:20px; accent-color:#6d28d9; flex:0 0 auto; }
   .wa-bos { text-align:center; color:var(--gri); font-size:13.5px; padding:28px 10px; }
   .wa-gorseller { display:flex; gap:10px; flex-wrap:wrap; margin:8px 0 12px; }
   .wa-gorseller figure { margin:0; width:130px; font-size:12px; color:#3a3154; }
   .wa-gorseller img { width:130px; height:130px; object-fit:cover; border-radius:14px; border:1px solid var(--cizgi); }
   .wa-bilgi { background:#fff; border-left:4px solid #7c3aed; border-radius:14px; padding:14px 16px; font-size:13.5px; color:#3a3154; margin-bottom:16px; box-shadow:var(--golge); }
   .wa-toast { position:fixed; left:50%; bottom:24px; transform:translateX(-50%); background:#1f1638; color:#fff; padding:11px 18px; border-radius:12px; font-size:14px; z-index:99999; display:none; }
   .wa-iskelet { background:linear-gradient(90deg,#f1ecfa,#faf7ff,#f1ecfa); background-size:200% 100%; animation:waIs 1.2s infinite; border-radius:18px; }
   @keyframes waIs { 0% { background-position:200% 0; } 100% { background-position:-200% 0; } }

   @media (max-width: 1400px) { .wa-kazanclar { grid-template-columns:repeat(3,minmax(0,1fr)); } .wa-hero-yazi, .wa-hero-katman, .wa-hero-yaprak { display:none; } }
   @media (max-width: 1280px) { .wa-kpiler { grid-template-columns:repeat(2,minmax(0,1fr)); } }
   @media (max-width: 1100px) { .wa-orta, .wa-alt-sira { grid-template-columns:minmax(0,1fr); } }
   @media (max-width: 900px) {
      .wa-hero { flex-direction:column; align-items:flex-start; padding:22px; gap:16px; }
      .wa-hero-bolum { border-left:none; padding:0; border-top:2px solid rgba(255,255,255,.45); padding-top:14px; width:100%; }
      .wa-hero-bolum::before { display:none; }
      .wa-hero-bolum.ilk { border-top:none; padding-top:0; }
      .wa-hero-ikon { width:60px; height:60px; } .wa-hero-ikon i { font-size:32px; }
      .wa-hero-d { font-size:28px; }
      .wa-iki { grid-template-columns:minmax(0,1fr); }
   }
   @media (max-width: 700px) {
      .wa { padding:12px; margin:0; }
      .wa-kpiler { grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
      .wa-kazanclar { grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
      .wa-hizli { grid-template-columns:repeat(2,minmax(0,1fr)); }
      .wa-kpi-d { font-size:23px; } .wa-kpi { padding:14px 14px 8px; min-height:0; }
      .wa-kpi .wa-trend { position:static; margin-top:6px; }
      .wa-kart { padding:16px; }
      .wa-konusma .wa-hap { display:none; }
      .wa-ust { flex-direction:column; align-items:stretch; }
      .wa-donem { align-self:flex-start; }
      .wa-ai .robot { width:120px; margin-bottom:-16px; }
   }
   @media (max-width: 360px) { .wa-kpiler, .wa-kazanclar { grid-template-columns:minmax(0,1fr); } }
   /* Kart "i" dugmesi (sag ust) */
   .wa-i { position:absolute; top:8px; right:8px; z-index:3; width:24px; height:24px; border-radius:50%; border:none; cursor:pointer;
      display:flex; align-items:center; justify-content:center; font-size:14px; background:rgba(255,255,255,.85); color:#5b21b6;
      box-shadow:0 2px 6px rgba(31,22,56,.12); transition:transform .15s, background .15s; }
   .wa-i:hover, .wa-i:focus-visible { transform:scale(1.12); background:#fff; outline:none; }
   .wa-k-lila .wa-i { color:#4338ca; } .wa-k-tur .wa-i { color:#ea580c; } .wa-k-mavi .wa-i { color:#1d4ed8; }
   .wa-k-yesil .wa-i { color:#15803d; } .wa-k-pembe .wa-i { color:#be185d; }
   .wa-kaz .wa-kaz-ust { padding-right:4px; }

   /* Bilgi penceresi (body'ye eklenir; ekran ortasi) */
   .wa-bm-bg { position:fixed; inset:0; z-index:100100; display:none; align-items:center; justify-content:center; padding:16px;
      background:rgba(20,12,40,.45); backdrop-filter:blur(3px); }
   .wa-bm-bg.acik { display:flex; animation:waBmA .18s ease-out; }
   @keyframes waBmA { from { opacity:0; } to { opacity:1; } }
   .wa-bm { width:100%; max-width:480px; max-height:90vh; overflow:auto; background:#fff; border-radius:22px; color:#1f1638;
      box-shadow:0 30px 70px rgba(20,12,40,.35); animation:waBmK .22s cubic-bezier(.2,.9,.3,1.2); }
   @keyframes waBmK { from { transform:translateY(14px) scale(.97); } to { transform:none; } }
   .wa-bm-bas { display:flex; align-items:center; gap:14px; padding:20px 20px 18px; border-radius:22px 22px 0 0; position:relative; }
   .wa-bm.wa-k-mor .wa-bm-bas { background:linear-gradient(140deg,#f4ecff,#e6d7ff); }
   .wa-bm.wa-k-lila .wa-bm-bas { background:linear-gradient(140deg,#eef0ff,#dcdcff); }
   .wa-bm.wa-k-tur .wa-bm-bas { background:linear-gradient(140deg,#fff4e8,#ffe1c2); }
   .wa-bm.wa-k-mavi .wa-bm-bas { background:linear-gradient(140deg,#ebf4ff,#d6e7ff); }
   .wa-bm.wa-k-yesil .wa-bm-bas { background:linear-gradient(140deg,#eafbf0,#d0f4de); }
   .wa-bm.wa-k-pembe .wa-bm-bas { background:linear-gradient(140deg,#fff0f7,#ffd9ea); }
   .wa-bm .wa-kpi-ikon { width:52px; height:52px; border-radius:16px; font-size:26px; display:flex; align-items:center; justify-content:center; flex:0 0 auto; box-shadow:0 6px 14px rgba(31,22,56,.10); }
   .wa-bm.wa-k-mor .wa-kpi-ikon { background:linear-gradient(140deg,#e7dafd,#cdb4fb); color:#5b21b6; }
   .wa-bm.wa-k-lila .wa-kpi-ikon { background:linear-gradient(140deg,#e2e3ff,#c3c4fb); color:#4338ca; }
   .wa-bm.wa-k-tur .wa-kpi-ikon { background:linear-gradient(140deg,#ffe6cc,#fdc48d); color:#ea580c; }
   .wa-bm.wa-k-mavi .wa-kpi-ikon { background:linear-gradient(140deg,#dbeaff,#b4d2fd); color:#1d4ed8; }
   .wa-bm.wa-k-yesil .wa-kpi-ikon { background:linear-gradient(140deg,#d6f7e2,#a8e9c0); color:#15803d; }
   .wa-bm.wa-k-pembe .wa-kpi-ikon { background:linear-gradient(140deg,#ffdcec,#fbb1d1); color:#be185d; }
   .wa-bm-t { font-size:18px; font-weight:800; line-height:1.2; padding-right:30px; }
   .wa-bm-d { font-size:15px; font-weight:700; color:#3a3154; margin-top:4px; }
   .wa-bm-d span { font-weight:500; color:#5b5574; font-size:13px; }
   .wa-bm-x { position:absolute; top:14px; right:14px; width:32px; height:32px; border-radius:50%; border:none; cursor:pointer; background:rgba(255,255,255,.8); color:#3a3154; font-size:14px; }
   .wa-bm-x:hover { background:#fff; }
   .wa-bm-ic { padding:8px 22px 4px; }
   .wa-bm-b { display:flex; gap:12px; padding:14px 0; border-bottom:1px solid #f1edf8; }
   .wa-bm-b:last-child { border-bottom:none; }
   .wa-bm-bi { flex:0 0 auto; width:34px; height:34px; border-radius:11px; background:#f3ecff; color:#6d28d9; display:flex; align-items:center; justify-content:center; font-size:16px; }
   .wa-bm-b b { font-size:14.5px; font-weight:700; }
   .wa-bm-b p { margin:4px 0 0; font-size:14px; line-height:1.55; color:#3f3a5a; }
   .wa-bm-alt { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:14px 22px 20px; flex-wrap:wrap; }
   .wa-bm-alt span { font-size:12.5px; color:#6b6483; }
   .wa-bm .wa-btn { border:none; border-radius:12px; padding:10px 22px; font-size:14px; font-weight:700; cursor:pointer; background:linear-gradient(135deg,#3b0f80,#6d28d9); color:#fff; }

   /* ===== ASISTAN GELISIMI sekmesi (Gece Denetcisi) ===== */
   .gl-hero { position:relative; overflow:hidden; border-radius:24px; padding:26px 30px; margin-bottom:18px; color:#fff; display:grid; grid-template-columns:auto minmax(0,1fr) auto; gap:26px; align-items:center;
      background:radial-gradient(circle at 85% 0%,rgba(94,234,212,.28),rgba(94,234,212,0) 45%),linear-gradient(125deg,#0f172a 0%,#1e1b4b 45%,#3b0f80 100%); box-shadow:0 18px 40px rgba(30,27,75,.35); }
   .gl-hero::after { content:''; position:absolute; right:-90px; bottom:-160px; width:420px; height:300px; border-radius:50%; border:2px solid rgba(255,255,255,.07); }
   .gl-hero > * { position:relative; z-index:1; }
   .gl-halka svg { width:150px; height:150px; display:block; }
   .gl-halka .y { font-size:30px; font-weight:800; fill:#fff; } .gl-halka .ye { font-size:10.5px; fill:rgba(255,255,255,.75); }
   .gl-rozet { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; font-weight:700; color:#5eead4; background:rgba(94,234,212,.12); border:1px solid rgba(94,234,212,.3); padding:6px 12px; border-radius:999px; }
   .gl-hero h3 { margin:12px 0 6px; font-size:25px; font-weight:800; color:#fff; line-height:1.25; }
   .gl-hero p { margin:0; font-size:14px; color:rgba(255,255,255,.8); line-height:1.5; max-width:620px; }
   .gl-tile { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; margin-top:16px; }
   .gl-tile div { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12); border-radius:16px; padding:12px; }
   .gl-tile b { display:block; font-size:22px; font-weight:800; line-height:1.15; }
   .gl-tile span { font-size:12.5px; color:rgba(255,255,255,.75); }
   .gl-tile em { font-style:normal; font-size:12px; font-weight:700; color:#5eead4; margin-left:4px; } .gl-tile em.k { color:#fca5a5; }
   .gl-hero .wa-btn { background:linear-gradient(135deg,#14b8a6,#0d9488); box-shadow:0 8px 20px rgba(20,184,166,.35); white-space:nowrap; }
   .gl-hero .gl-not { display:block; font-size:12px; color:rgba(255,255,255,.65); margin-top:8px; text-align:center; max-width:190px; }
   .gl-iki { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); gap:18px; margin-bottom:18px; }
   .gl-rapor { border:2px solid #99f6e4; background:linear-gradient(135deg,#f0fdfa,#fff); }
   .gl-rapor-metin { white-space:pre-line; font-size:14.5px; line-height:1.65; color:#1f2937; }
   .gl-bekle { border:2px solid #fde68a; background:linear-gradient(135deg,#fffbeb,#fff); }
   .gl-soru { padding:12px 0; border-top:1px dashed #f3e3a6; } .gl-soru:first-of-type { border-top:none; }
   .gl-soru b { display:block; font-size:14.5px; } .gl-soru small { display:block; font-size:12.5px; color:var(--gri); margin:2px 0 8px; }
   .gl-soru .sat { display:flex; gap:8px; flex-wrap:wrap; } .gl-soru textarea { flex:1 1 260px; font-family:inherit; }
   .gl-soru .wa-btn { padding:8px 14px; font-size:13px; }
   .gl-soru q { font-weight:800; color:#7c2d12; }
   .gl-fiyatlar { display:grid; gap:6px; margin:6px 0 10px; max-width:560px; }
   .gl-fsat { display:flex; align-items:center; gap:8px; margin:0; font-weight:600; font-size:14px; }
   .gl-fsat span { flex:1 1 auto; min-width:0; } .gl-fsat span i { font-style:normal; font-weight:500; font-size:12px; }
   .gl-fsat .gl-fiyat { width:110px; flex:0 0 110px; text-align:right; font-size:15px; font-weight:700; padding:7px 10px; }
   .gl-fsat.yok em { font-style:normal; font-size:12.5px; color:var(--gri); } .gl-fsat.yok span { color:var(--gri); }
   .gl-soru select.gl-hizmet { flex:1 1 260px; max-width:420px; }
   .gl-grafik { display:flex; align-items:flex-end; gap:6px; height:150px; padding-top:20px; }
   .gl-grafik .c { flex:1; min-width:0; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; height:100%; position:relative; }
   .gl-grafik .c i { display:block; width:100%; max-width:26px; border-radius:7px 7px 3px 3px; min-height:3px; background:linear-gradient(180deg,#5eead4,#14b8a6); }
   .gl-grafik .c.dusuk i { background:linear-gradient(180deg,#fdba74,#f97316); } .gl-grafik .c.bos i { background:#eee8f7; }
   .gl-grafik .c em { position:absolute; top:-18px; font-style:normal; font-size:11px; font-weight:700; color:#0f766e; }
   .gl-x { display:flex; gap:6px; margin-top:6px; } .gl-x span { flex:1; min-width:0; text-align:center; font-size:10.5px; color:var(--gri); }
   .gl-tur { display:grid; grid-template-columns:minmax(0,1fr) 140px auto; gap:10px; align-items:center; padding:7px 0; font-size:13.5px; }
   .gl-tur .bar { height:9px; border-radius:99px; background:#f1edf8; overflow:hidden; } .gl-tur .bar i { display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#f97316,#ef4444); }
   .gl-ders { display:flex; gap:14px; align-items:flex-start; padding:14px; border-radius:16px; background:#faf7ff; margin-bottom:10px; border:1px solid transparent; }
   .gl-ders.aktif { background:linear-gradient(135deg,#f0fdfa,#fff); border-color:#99f6e4; }
   .gl-ders .ik { flex:0 0 auto; width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:19px; background:#ede9fe; color:#6d28d9; }
   .gl-ders.aktif .ik { background:#ccfbf1; color:#0f766e; }
   .gl-ders .ic { flex:1; min-width:0; } .gl-ders .ic b { display:block; font-size:14.5px; line-height:1.4; } .gl-ders .ic small { display:block; font-size:12.5px; color:var(--gri); margin-top:4px; line-height:1.45; }
   .gl-etk { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:700; padding:3px 9px; border-radius:999px; margin-top:7px; margin-right:6px; }
   .gl-etk.y { background:#ccfbf1; color:#0f766e; } .gl-etk.s { background:#fef3c7; color:#92400e; } .gl-etk.k { background:#fee2e2; color:#b91c1c; } .gl-etk.g { background:#eef2f7; color:#475569; }
   .gl-ders .islem { display:flex; gap:6px; flex:0 0 auto; }
   .gl-ders .islem button { border:1px solid #e3d8f5; background:#fff; color:#3a3154; border-radius:10px; padding:7px 12px; font-size:12.5px; font-weight:700; cursor:pointer; }
   .gl-ders .islem button.ana { background:linear-gradient(135deg,#14b8a6,#0d9488); color:#fff; border-color:transparent; }
   .gl-sohbet { border-top:1px solid #f1edf8; padding:12px 2px; }
   .gl-sohbet:first-child { border-top:none; }
   .gl-sohbet .ust { display:flex; align-items:center; gap:12px; cursor:pointer; }
   .gl-puan { flex:0 0 auto; width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:800; }
   .gl-puan.i { background:#ccfbf1; color:#0f766e; } .gl-puan.o { background:#fef3c7; color:#92400e; } .gl-puan.k { background:#fee2e2; color:#b91c1c; }
   .gl-sohbet .ic { flex:1; min-width:0; } .gl-sohbet .ic b { font-size:14.5px; } .gl-sohbet .ic div { font-size:13px; color:var(--gri); }
   .gl-sohbet .detay { display:none; margin:10px 0 0 58px; }
   .gl-sohbet.acik .detay { display:block; }
   .gl-sorun { background:#fff7ed; border-radius:12px; padding:10px 12px; margin-bottom:8px; font-size:13.5px; }
   .gl-sorun b { color:#c2410c; font-size:12.5px; display:block; margin-bottom:2px; }
   .gl-sorun .dogru { margin-top:6px; background:#e7fbe9; border-radius:10px; padding:8px 10px; white-space:pre-wrap; }
   .gl-sorun .dogru::before { content:'Doğrusu: '; font-weight:700; color:#15803d; }
   .gl-kod summary { cursor:pointer; font-weight:700; font-size:14px; color:#475569; }
   @media (max-width: 1100px) { .gl-hero { grid-template-columns:minmax(0,1fr); justify-items:start; } .gl-iki { grid-template-columns:minmax(0,1fr); } }
   @media (max-width: 760px) { .gl-hero { padding:22px 18px; } .gl-tile { grid-template-columns:repeat(2,minmax(0,1fr)); } .gl-ders { flex-wrap:wrap; } .gl-sohbet .detay { margin-left:0; } .gl-tur { grid-template-columns:minmax(0,1fr) 90px auto; } }

   /* ===== FOTOGRAF FIYATLARI sekmesi ===== */
   .fg-ust { background:#fff; border-radius:20px; padding:18px 20px; box-shadow:var(--golge); margin-bottom:16px; display:grid; grid-template-columns:auto minmax(0,1fr) auto; gap:16px; align-items:start; }
   .fg-ust-ik { width:48px; height:48px; border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:22px; color:#fff; background:linear-gradient(135deg,#be185d,#7c3aed); }
   .fg-ust h3 { margin:0 0 4px; font-size:19px; font-weight:800; color:var(--metin); }
   .fg-ust p { margin:0; font-size:13.5px; color:#4b4563; line-height:1.5; max-width:820px; }
   .fg-durum-satir { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
   .fg-durum-satir span { font-size:12.5px; font-weight:700; padding:5px 11px; border-radius:999px; background:#f4f1fa; color:#4c1d95; display:inline-flex; align-items:center; gap:6px; }
   .fg-durum-satir .y { background:#dcfce7; color:#15803d; } .fg-durum-satir .s { background:#fef3c7; color:#92400e; }
   .fg-sektor { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
   .fg-sektor > span { font-size:13px; font-weight:700; color:var(--gri); }
   #fgSektorler { display:flex; gap:8px; flex-wrap:wrap; }
   #fgSektorler button { border:1px solid #e3d8f5; background:#fff; color:#3a3154; border-radius:999px; padding:9px 16px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; }
   #fgSektorler button.aktif { background:linear-gradient(135deg,#be185d,#7c3aed); border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(190,24,93,.3); }
   .fg-duzen { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:18px; align-items:start; }
   .fg-yan { display:flex; flex-direction:column; gap:18px; position:sticky; top:84px; }
   .fg-bas { display:flex; align-items:center; gap:10px; font-size:17px; font-weight:800; margin:0 0 4px; }
   .fg-bas small { font-size:13px; font-weight:600; color:var(--gri); }
   .fg-alt { font-size:13px; color:var(--gri); margin-bottom:12px; }
   .fg-ana { display:grid; grid-template-columns:minmax(0,1fr); gap:10px; margin-bottom:22px; }
   .fg-ana-k { background:#fff; border-radius:16px; padding:12px 14px; box-shadow:var(--golge); border:2px solid transparent; display:grid; grid-template-columns:170px minmax(0,1fr) auto; gap:12px; align-items:center; }
   .fg-ana-k.hazir { border-color:#bbf7d0; } .fg-ana-k.eksik { border-color:#fde68a; background:#fffdf5; }
   .fg-ana-ad { display:flex; align-items:center; gap:10px; min-width:0; }
   .fg-ana-ad .ik { flex:0 0 auto; width:34px; height:34px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:16px; background:linear-gradient(140deg,#ffdcec,#fbb1d1); color:#be185d; }
   .fg-ana-ad b { font-size:14.5px; }
   .fg-ana-sec { display:flex; gap:8px; min-width:0; } .fg-ana-sec select { flex:1 1 auto; min-width:0; } .fg-ana-sec .fg-tl { flex:0 0 140px; }
   .fg-durum { font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:999px; background:#fef3c7; color:#92400e; white-space:nowrap; justify-self:end; }
   .fg-ana-k.hazir .fg-durum { background:#dcfce7; color:#15803d; }
   .fg-tl { position:relative; } .fg-tl input { padding-right:38px; } .fg-tl::after { content:'TL'; position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:12.5px; font-weight:700; color:#9b92b3; pointer-events:none; }
   .fg-ekler { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:14px; align-items:start; }
   .fg-yan textarea { font-family:inherit; resize:vertical; }
   .fg-ek-k { background:#fff; border-radius:20px; padding:16px; box-shadow:var(--golge); }
   .fg-ek-k h5 { margin:0 0 10px; font-size:14.5px; font-weight:700; display:flex; align-items:center; gap:8px; }
   .fg-ek-k h5 i { color:#7c3aed; }
   .fg-ek-k h5 small { margin-left:auto; font-size:11.5px; font-weight:600; color:var(--gri); background:#f4f1fa; padding:3px 8px; border-radius:999px; }
   .fg-ek-s { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:7px 0; border-top:1px dashed #eee8f7; }
   .fg-ek-s:first-of-type { border-top:none; }
   .fg-ek-s span { font-size:13.5px; color:#3a3154; }
   .fg-ek-s .fg-tl { width:128px; flex:0 0 auto; } .fg-ek-s input { padding:7px 34px 7px 10px; font-size:13.5px; }
   .fg-ek-s.dolu span { color:#1f1638; font-weight:600; } .fg-ek-s.dolu input { border-color:#c4b5fd; background:#faf7ff; }
   .fg-hesap .fg-h-s { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:6px 0; font-size:13.5px; }
   .fg-hesap select, .fg-hesap input[type=number] { padding:6px 8px; font-size:13px; border-radius:9px; }
   .fg-hesap input[type=number] { width:70px; }
   .fg-hesap label { display:flex; align-items:center; gap:8px; cursor:pointer; }
   .fg-hesap input[type=checkbox] { width:17px; height:17px; accent-color:#db2777; }
   .fg-balon { margin-top:14px; background:#e7fbe9; border-radius:14px 14px 14px 4px; padding:12px 14px; font-size:13.5px; line-height:1.5; white-space:pre-wrap; color:#1f2937; position:relative; box-shadow:0 2px 6px rgba(0,0,0,.05); }
   .fg-balon::before { content:'Asistanın cevabı (örnek)'; display:block; font-size:11px; font-weight:700; color:#15803d; margin-bottom:4px; }
   .fg-balon.uyari { background:#fff7ed; } .fg-balon.uyari::before { content:'Dikkat'; color:#c2410c; }
   .fg-toplam { display:flex; justify-content:space-between; align-items:baseline; margin-top:10px; padding-top:10px; border-top:2px solid #f1edf8; }
   .fg-toplam span { font-size:13px; color:var(--gri); } .fg-toplam b { font-size:26px; font-weight:800; color:#be185d; }
   .fg-kaydet { position:sticky; bottom:12px; z-index:5; margin-top:18px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;
      background:rgba(255,255,255,.92); backdrop-filter:blur(8px); border-radius:18px; padding:12px 16px; box-shadow:0 10px 30px rgba(31,22,56,.14); border:1px solid #eee8f7; }
   .fg-kaydet span { font-size:13.5px; color:var(--gri); display:flex; align-items:center; gap:8px; }
   .fg-kaydet.degisti { border-color:#f9a8d4; } .fg-kaydet.degisti span { color:#be185d; font-weight:700; }
   @media (max-width: 1280px) { .fg-duzen { grid-template-columns:minmax(0,1fr); } .fg-yan { position:static; } }
   @media (max-width: 1100px) { .fg-ust { grid-template-columns:auto minmax(0,1fr); } .fg-ust .fg-sektor { grid-column:1 / -1; justify-self:start; } }
   @media (max-width: 760px) {
      .fg-ust { grid-template-columns:minmax(0,1fr); padding:16px; } .fg-ust-ik { display:none; }
      .fg-ana, .fg-ekler { grid-template-columns:minmax(0,1fr); }
      .fg-ana-k { grid-template-columns:minmax(0,1fr); } .fg-durum { justify-self:start; }
   }

   /* ===== KAZANDIRDIKLARI sekmesi ===== */
   .wk-hero { position:relative; overflow:hidden; border-radius:24px; padding:28px 30px; margin-bottom:18px; color:#fff;
      background:radial-gradient(circle at 12% 0%,#4c1d95 0%,rgba(76,29,149,0) 55%),linear-gradient(125deg,#160a33 0%,#2a0f5e 45%,#3b0f80 100%);
      box-shadow:0 18px 40px rgba(31,10,74,.35); display:grid; grid-template-columns:minmax(0,1.25fr) minmax(0,1fr); gap:26px; align-items:center; }
   .wk-hero::before { content:''; position:absolute; right:-120px; top:-140px; width:440px; height:440px; border-radius:50%; background:radial-gradient(circle,rgba(250,204,21,.18),rgba(250,204,21,0) 62%); }
   .wk-hero::after { content:''; position:absolute; left:38%; bottom:-180px; width:420px; height:300px; border-radius:50%; border:2px solid rgba(255,255,255,.07); }
   .wk-hero > * { position:relative; z-index:1; }
   .wk-ust-e { display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; color:#fde68a; background:rgba(250,204,21,.12); border:1px solid rgba(250,204,21,.3); padding:6px 12px; border-radius:999px; }
   .wk-buyuk { font-size:52px; font-weight:800; line-height:1.05; letter-spacing:-1.2px; margin:14px 0 6px;
      background:linear-gradient(90deg,#fff 0%,#fde68a 60%,#facc15 100%); -webkit-background-clip:text; background-clip:text; color:transparent; }
   .wk-alt-e { font-size:14.5px; color:rgba(255,255,255,.82); }
   .wk-alt-e b { color:#fff; }
   .wk-roi { margin-top:18px; display:flex; flex-direction:column; gap:9px; max-width:520px; }
   .wk-roi-s { display:grid; grid-template-columns:120px minmax(0,1fr) auto; gap:10px; align-items:center; font-size:13px; color:rgba(255,255,255,.85); }
   .wk-roi-s .bar { height:12px; border-radius:99px; background:rgba(255,255,255,.08); overflow:hidden; }
   .wk-roi-s .bar i { display:block; height:100%; border-radius:99px; width:0; transition:width 1s cubic-bezier(.2,.8,.2,1); }
   .wk-roi-s.o .bar i { background:linear-gradient(90deg,#94a3b8,#cbd5e1); }
   .wk-roi-s.k .bar i { background:linear-gradient(90deg,#f59e0b,#facc15); box-shadow:0 0 14px rgba(250,204,21,.6); }
   .wk-roi-s b { font-weight:700; color:#fff; white-space:nowrap; }
   .wk-kat { display:inline-flex; align-items:center; gap:6px; margin-left:8px; background:#facc15; color:#3b0f80; font-weight:800; font-size:14px; padding:4px 12px; border-radius:999px; vertical-align:middle; -webkit-text-fill-color:#3b0f80; }
   .wk-cam { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
   .wk-cam > div { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12); border-radius:18px; padding:16px; backdrop-filter:blur(6px); }
   .wk-cam i { font-size:22px; color:#c4b5fd; }
   .wk-cam .d { font-size:26px; font-weight:800; margin-top:8px; line-height:1.1; }
   .wk-cam .d small { font-size:14px; font-weight:600; opacity:.8; margin-left:3px; }
   .wk-cam .e { font-size:13px; color:rgba(255,255,255,.75); margin-top:3px; line-height:1.35; }

   .wk-yap { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px; margin-bottom:18px; }
   .wk-yap a { display:flex; align-items:center; gap:14px; background:#fff; border-radius:18px; padding:14px 16px; box-shadow:var(--golge); cursor:pointer; text-decoration:none; color:var(--metin); border-left:5px solid #f97316; transition:transform .15s; }
   .wk-yap a:hover { transform:translateY(-2px); color:var(--metin); text-decoration:none; }
   .wk-yap .ik { flex:0 0 auto; width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:21px; background:#ffedd5; color:#ea580c; }
   .wk-yap .t { flex:1; min-width:0; } .wk-yap .t b { display:block; font-size:15px; } .wk-yap .t span { font-size:13px; color:var(--gri); }
   .wk-yap .git { font-size:13px; font-weight:700; color:#ea580c; white-space:nowrap; }
   .wk-yap a.kirmizi { border-left-color:#dc2626; } .wk-yap a.kirmizi .ik { background:#fee2e2; color:#dc2626; } .wk-yap a.kirmizi .git { color:#dc2626; }
   .wk-yap a.mor { border-left-color:#7c3aed; } .wk-yap a.mor .ik { background:#efe6fd; color:#7c3aed; } .wk-yap a.mor .git { color:#7c3aed; }

   .wk-iki { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.25fr); gap:18px; margin-bottom:18px; }
   .wk-uc { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px; margin-bottom:18px; }
   .wk-alt-not { font-size:12.5px; color:var(--gri); margin-top:12px; line-height:1.45; }
   .wk-bos { font-size:13.5px; color:var(--gri); background:#faf7ff; border:1px dashed #ddd0f3; border-radius:14px; padding:16px; line-height:1.5; }
   .wk-bos b { color:#4c1d95; }

   .wk-donut { display:flex; align-items:center; gap:24px; flex-wrap:wrap; }
   .wk-donut svg { width:180px; height:180px; flex:0 0 auto; }
   .wk-donut .orta-d { font-size:19px; font-weight:800; fill:#1f1638; }
   .wk-donut .orta-e { font-size:11px; fill:#6b6483; }
   .wk-lej { flex:1; min-width:180px; display:flex; flex-direction:column; gap:12px; }
   .wk-lej div { display:grid; grid-template-columns:14px minmax(0,1fr) auto; gap:10px; align-items:center; font-size:14px; }
   .wk-lej i { width:14px; height:14px; border-radius:5px; }
   .wk-lej b { font-weight:700; } .wk-lej small { display:block; font-size:12.5px; color:var(--gri); }

   .wk-huni { display:flex; flex-direction:column; align-items:center; gap:0; }
   .wk-huni .k { position:relative; height:50px; border-radius:14px; display:flex; align-items:center; justify-content:space-between; padding:0 18px; color:#fff; min-width:200px; box-shadow:0 8px 18px rgba(76,29,149,.18); transition:width .9s cubic-bezier(.2,.8,.2,1); }
   .wk-huni .k span { font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; white-space:nowrap; }
   .wk-huni .k b { font-size:20px; font-weight:800; }
   .wk-huni .gec { font-size:12px; font-weight:700; color:#6d28d9; background:#f3ecff; border-radius:999px; padding:3px 10px; margin:6px 0; display:inline-flex; align-items:center; gap:4px; }

   .wk-halka { display:flex; align-items:center; gap:18px; }
   .wk-halka svg { width:120px; height:120px; flex:0 0 auto; }
   .wk-halka .y { font-size:24px; font-weight:800; fill:#1f1638; }
   .wk-halka .ye { font-size:10.5px; fill:#6b6483; }
   .wk-satirlar { flex:1; min-width:0; display:flex; flex-direction:column; gap:8px; font-size:13.5px; }
   .wk-satirlar div { display:flex; justify-content:space-between; gap:8px; border-bottom:1px dashed #eee8f7; padding-bottom:6px; }
   .wk-satirlar div:last-child { border-bottom:none; }
   .wk-satirlar span { color:var(--gri); } .wk-satirlar b { font-weight:700; }
   .wk-memnun { display:flex; align-items:baseline; gap:10px; }
   .wk-memnun .d { font-size:44px; font-weight:800; color:#16a34a; letter-spacing:-1px; }
   .wk-memnun .e { font-size:14px; color:var(--gri); }
   .wk-yuz { display:flex; height:14px; border-radius:99px; overflow:hidden; margin:12px 0 10px; background:#f1edf8; }
   .wk-yuz i { display:block; height:100%; }
   .wk-isler { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
   .wk-isler div { background:#faf7ff; border-radius:14px; padding:12px; display:flex; align-items:center; gap:10px; }
   .wk-isler i { font-size:20px; flex:0 0 auto; width:38px; height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
   .wk-isler b { display:block; font-size:19px; font-weight:800; line-height:1.1; }
   .wk-isler span { font-size:12.5px; color:var(--gri); }

   .wk-hizmet { display:flex; flex-direction:column; gap:12px; }
   .wk-hizmet .s { display:grid; grid-template-columns:30px minmax(0,1fr) auto; gap:12px; align-items:center; }
   .wk-hizmet .no { width:30px; height:30px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; background:#f3ecff; color:#6d28d9; }
   .wk-hizmet .s:first-child .no { background:linear-gradient(135deg,#facc15,#f59e0b); color:#fff; }
   .wk-hizmet .ad { font-size:14px; font-weight:600; margin-bottom:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
   .wk-hizmet .bar { height:9px; border-radius:99px; background:#f1edf8; overflow:hidden; }
   .wk-hizmet .bar i { display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#a855f7,#ec4899); }
   .wk-hizmet .n { font-size:13px; font-weight:700; color:#4c1d95; white-space:nowrap; }
   .wk-saat { display:flex; align-items:flex-end; gap:4px; height:150px; padding-top:22px; }
   .wk-saat .c { flex:1; min-width:0; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; position:relative; }
   .wk-saat .c i { display:block; width:100%; max-width:22px; border-radius:6px 6px 3px 3px; background:linear-gradient(180deg,#c4b5fd,#a78bfa); min-height:3px; transition:height .8s cubic-bezier(.2,.8,.2,1); }
   .wk-saat .c.gece i { background:linear-gradient(180deg,#818cf8,#4f46e5); }
   .wk-saat .c.tepe i { background:linear-gradient(180deg,#f472b6,#db2777); box-shadow:0 4px 12px rgba(219,39,119,.35); }
   .wk-saat .c em { position:absolute; top:-20px; font-style:normal; font-size:11px; font-weight:700; color:#db2777; white-space:nowrap; }
   .wk-saat-x { display:flex; gap:4px; margin-top:6px; }
   .wk-saat-x span { flex:1; min-width:0; text-align:center; font-size:10.5px; color:var(--gri); }
   .wk-saat-lej { display:flex; gap:14px; flex-wrap:wrap; font-size:12.5px; color:var(--gri); margin-top:10px; }
   .wk-saat-lej span::before { content:''; display:inline-block; width:10px; height:10px; border-radius:3px; margin-right:6px; vertical-align:-1px; }
   .wk-saat-lej .g::before { background:#a78bfa; } .wk-saat-lej .n::before { background:#4f46e5; } .wk-saat-lej .t::before { background:#db2777; }

   @media (max-width: 1280px) { .wk-uc { grid-template-columns:repeat(2,minmax(0,1fr)); } }
   /* ===== REKLAMLARIM ===== */
   .rk-duzen { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); gap:18px; align-items:start; }
   .rk-satir { display:flex; gap:14px; align-items:flex-start; padding:14px 0; border-top:1px solid #f0eaf7; }
   .rk-satir:first-child { border-top:none; }
   .rk-afis { width:84px; height:84px; flex:0 0 84px; border-radius:14px; background:#f3e8ff center/cover no-repeat; display:flex; align-items:center; justify-content:center; color:#a78bfa; font-size:26px; }
   .rk-ic { flex:1; min-width:0; }
   .rk-ic b { font-size:15px; } .rk-ic .wa-alt { margin-top:3px; }
   .rk-say { display:flex; gap:14px; flex-wrap:wrap; margin-top:8px; font-size:12.5px; color:#475569; }
   .rk-say span b { color:#1f1530; font-size:14px; }
   .rk-btnlar { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
   .rk-btnlar .wa-btn { padding:6px 12px; font-size:12.5px; }
   .rk-hizmetler { max-height:200px; overflow:auto; border:1px solid #ddd3ea; border-radius:11px; padding:6px 10px; }
   .rk-hizmetler label { display:flex; gap:8px; align-items:center; padding:4px 0; font-size:13.5px; cursor:pointer; }
   .rk-on { margin-top:6px; border-radius:12px; max-width:100%; max-height:220px; display:block; }
   @media (max-width: 1100px) { .rk-duzen { grid-template-columns:minmax(0,1fr); } }
   @media (max-width: 1100px) { .wk-hero, .wk-iki { grid-template-columns:minmax(0,1fr); } }
   @media (max-width: 760px) {
      .wk-uc { grid-template-columns:minmax(0,1fr); }
      .wk-hero { padding:22px 18px; } .wk-buyuk { font-size:38px; }
      .wk-roi-s { grid-template-columns:92px minmax(0,1fr) auto; }
      .wk-cam .d { font-size:22px; }
      .wk-donut { justify-content:center; } .wk-donut svg { width:150px; height:150px; }
      .wk-saat-x span:nth-child(odd) { visibility:hidden; }
   }
</style>

<div class="wa">
   <div class="wa-ust">
      <div class="wa-sekmeler" id="waSekmeler" role="tablist">
         <button class="wa-sekme aktif" data-p="kazanc"><i class="bi bi-bar-chart-fill"></i>Genel Bakış</button>
         <button class="wa-sekme" data-p="kaz"><i class="bi bi-graph-up-arrow"></i>Kazandırdıkları</button>
         <button class="wa-sekme" data-p="sohbet"><i class="bi bi-whatsapp"></i>Sohbetler</button>
         <button class="wa-sekme" data-p="gk"><i class="bi bi-arrow-repeat"></i>Kaçan & Geri Kazanılan</button>
         <button class="wa-sekme" data-p="gorsel"><i class="bi bi-camera"></i>Fotoğraftan Fiyat</button>
         @if(session('sysadmin_impersonation_id') || !empty($waAyar['reklam']))
         <button class="wa-sekme" data-p="reklam"><i class="bi bi-megaphone-fill"></i>Reklamlarım</button>
         @endif
         {{-- Asistan Gelisimi / Asistan size soruyor (Gece Denetcisi) sekmesi kaldirildi: denetci gece calismasi kapali --}}
         <button class="wa-sekme" data-p="ogr"><i class="bi bi-lightbulb"></i>Öğrenilen Cevaplar</button>
         <button class="wa-sekme" data-p="ayar"><i class="bi bi-gear"></i>Ayarlar</button>
      </div>
      <label class="wa-donem"><i class="bi bi-calendar3"></i>
         <select id="waDonem" aria-label="Dönem">
            <option value="7g">Son 7 Gün</option>
            <option value="30g" selected>Son 30 Gün</option>
            <option value="90g">Son 90 Gün</option>
            <option value="bu_ay">Bu Ay</option>
            <option value="gecen_ay">Geçen Ay</option>
         </select>
      </label>
   </div>

   @if(!$waAcik)
      <div class="wa-bilgi"><b>WhatsApp asistanı bu işletmede henüz açık değil.</b> Açmak için bizimle iletişime geçin; ayarları şimdiden yapabilirsiniz.</div>
   @endif

   {{-- ================= GENEL BAKIS ================= --}}
   <div class="wa-panel aktif" id="p-kazanc">
      <div id="waGenel">
         <div class="wa-iskelet" style="height:130px;margin-bottom:18px"></div>
         <div class="wa-kpiler">@for($i = 0; $i < 5; $i++)<div class="wa-iskelet" style="height:150px"></div>@endfor</div>
      </div>
   </div>

   {{-- ================= SOHBETLER ================= --}}
   {{-- ================= KAZANDIRDIKLARI ================= --}}
   <div class="wa-panel" id="p-kaz">
      <div id="waKazanc"><div class="wa-iskelet" style="height:160px"></div></div>
   </div>

   <div class="wa-panel" id="p-sohbet">
      <div class="wa-kart" style="margin-bottom:18px">
         <div class="wa-satir" style="margin:0">
            <input type="date" id="waBas" class="wa-girdi" style="width:auto">
            <input type="date" id="waBit" class="wa-girdi" style="width:auto">
            <input type="text" id="waAra" class="wa-girdi" style="width:240px;max-width:100%" placeholder="Ad veya telefon ara (tüm tarihler)">
            <button class="wa-btn ikincil" id="waSohbetYukle"><i class="bi bi-search"></i> Listele</button>
         </div>
      </div>
      <div class="wa-iki">
         <div class="wa-kart wa-liste-kart" style="padding:12px"><div class="wa-liste" id="waSohbetListe"><div class="wa-bos">Yükleniyor…</div></div></div>
         <div class="wa-kart" id="waSohbetDetay"><div class="wa-bos">Soldan bir sohbet seçin.</div></div>
      </div>
   </div>

   {{-- ================= GERI KAZANMA ================= --}}
   <div class="wa-panel" id="p-gk">
      <div class="wa-kart" style="margin-bottom:18px">
         <div class="wa-kart-bas"><h4><i class="bi bi-person-plus" style="color:#f97316"></i>Kaçan müşteriler</h4></div>
         <div class="wa-alt" style="margin-bottom:10px">Asistandan fiyat, bilgi, müsait saat ya da fotoğrafa fiyat alıp randevu oluşturmayan müşteriler. Asistan ayarlardaki süre dolunca bu müşterilere bir kez nazikçe yazar.</div>
         <div class="wa-tablo-sar" id="waKacan"><div class="wa-bos">Yükleniyor…</div></div>
      </div>
      <div class="wa-kart">
         <div class="wa-kart-bas"><h4><i class="bi bi-arrow-repeat" style="color:#7c3aed"></i>Geri kazanma mesajları</h4></div>
         <div class="wa-tablo-sar" id="waGkListe"><div class="wa-bos">Yükleniyor…</div></div>
      </div>
   </div>

   {{-- ================= FOTOGRAF FIYATLARI ================= --}}
   <div class="wa-panel" id="p-gorsel">
      {{-- Ayar sayfasi: bir kez kurulur. Ana islemler hizmet listesinden OTOMATIK eslesir; yalniz eksikler isaretlenir. --}}
      <div class="fg-ust">
         <div class="fg-ust-ik"><i class="bi bi-camera-fill"></i></div>
         <div class="fg-ust-ic"><h3>Fotoğraftan fiyat ayarları</h3>
            <p>Müşteri model fotoğrafı gönderdiğinde asistan uygulamaları tanır ve fiyatı buradaki eşleşmelerden hesaplar. <b>Bir kez ayarlanır</b>; ana işlemler hizmet listenizden otomatik bulunur, sizin yalnızca eksikleri tamamlamanız yeterli. Fiyatı bilinmeyen modelde asistan müşteriyi bekletmez, fiyatı size sorar ve randevuya devam eder.</p>
            <div id="fgDurum" class="fg-durum-satir"></div></div>
         <div class="fg-sektor"><span>Sektör</span><div id="fgSektorler"></div><select id="waSektor" hidden></select></div>
      </div>
      <div class="fg-duzen">
         <div id="waKuralForm"></div>
         <aside class="fg-yan">
            <div class="wa-kart fg-hesap"><div class="wa-kart-bas"><h4><i class="bi bi-calculator-fill" style="color:#db2777"></i>Fiyat deneme</h4></div>
               <div class="wa-alt" style="margin:-6px 0 12px">Bir model seçin, asistanın müşteriye yazacağı fiyatı görün. (Kaydetmeden de çalışır.)</div>
               <div id="fgHesap"></div></div>
            <div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-chat-left-text-fill" style="color:#7c3aed"></i>Fiyatın altına not</h4></div>
               <textarea id="waGorselNot" class="wa-girdi" rows="3" maxlength="500" placeholder="Örn. Mevcut protezin sökümü 150 TL eklenir."></textarea>
               <div class="wa-alt" style="margin-top:6px">İsteğe bağlı; her fotoğraf fiyatının altına eklenir.</div></div>
         </aside>
      </div>
      <div class="fg-kaydet" id="fgKaydetBar"><span id="fgKaydetYazi"><i class="bi bi-info-circle"></i> Boş bıraktığınız ekler fiyata eklenmez.</span><button class="wa-btn" id="waKuralKaydet"><i class="bi bi-check2-circle"></i> Kuralları kaydet</button></div>
   </div>

   {{-- ================= OGRENILEN ================= --}}
   <div class="wa-panel" id="p-ogr">
      <div class="wa-bilgi">Asistan genel bir soruya (ör. "otopark var mı") yapay zekâyla bir kez cevap verince o cevabı öğrenir; aynı soru sonra <b>ücretsiz ve anında</b> cevaplanır. Yanlış ya da eskimiş bir cevabı silebilirsiniz; asistan bir dahaki sefere yeniden düşünür. Öğrenilen cevaplar 30 günde bir yenilenir.</div>
      <div class="wa-kart"><div class="wa-tablo-sar" id="waOgrListe"><div class="wa-bos">Yükleniyor…</div></div></div>
   </div>

   {{-- ================= REKLAMLARIM (WaReklam) ================= --}}
   <div class="wa-panel" id="p-reklam">
      <div class="wa-bilgi">Kampanyanızı bir kez girin; asistan, müşteri <b>ilgili hizmeti sorduğunda</b> ya da "kampanya var mı" dediğinde kampanyanızı afişiyle birlikte <b>aynı sohbette</b> gönderir. Sohbet başına en fazla bir kez gider, ilgisiz hizmette gönderilmez, "kampanya istemiyorum" diyen müşteriye bir daha gitmez.</div>
      <div id="rkKapali" class="wa-bilgi" style="display:none;background:#fef3c7;color:#92400e"><b>Reklam özelliği bu işletmede kapalı.</b> Reklamlarınızı şimdiden hazırlayabilirsiniz; özellik açılınca asistan göndermeye başlar.</div>
      <div class="rk-duzen">
         <div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-megaphone-fill" style="color:#db2777"></i>Kampanyalarım</h4>
            <button class="wa-btn ikincil" id="rkYeni"><i class="bi bi-plus-lg"></i> Yeni kampanya</button></div>
            <div id="rkListe"><div class="wa-bos">Yükleniyor…</div></div></div>
         <div class="wa-kart" id="rkForm" style="display:none"></div>
      </div>
   </div>

   {{-- ================= AYARLAR ================= --}}
   {{-- ================= ASISTAN GELISIMI (Gece Denetcisi) ================= --}}
   <div class="wa-panel" id="p-gel"><div id="waGelisim"><div class="wa-iskelet" style="height:200px"></div></div></div>

   <div class="wa-panel" id="p-ayar">
      <div class="wa-kart">
         <div class="wa-anahtar"><div class="t"><b>Fiyat sorularını cevaplasın</b><div>"Protez tırnak ne kadar?" gibi sorulara hizmet listenizdeki fiyatla cevap verir. Kapalıysa fiyat soran müşteriye fiyatı işletmeye sorduğunu söyler ve size bildirir.</div></div><input type="checkbox" data-ayar="fiyat_soyle" aria-label="Fiyat sorularını cevaplasın"></div>
         <div class="wa-anahtar"><div class="t"><b>Fotoğraftan fiyat versin</b><div>Müşterinin gönderdiği model fotoğrafına, "Fotoğraftan Fiyat" sekmesindeki eşleşmelerle fiyat verir.</div></div><input type="checkbox" data-ayar="gorsel_fiyat" aria-label="Fotoğraftan fiyat versin"></div>
         <div class="wa-anahtar"><div class="t"><b>Randevudan sonra memnuniyet sorsun</b><div>Randevu oluşturulunca tek satırlık 👍 / 👎 sorusu eklenir.</div></div><input type="checkbox" data-ayar="memnuniyet_sor" aria-label="Memnuniyet sorsun"></div>
         @if(session('sysadmin_impersonation_id'))
         <div class="wa-anahtar"><div class="t"><b>Asistan salonun kampanyalarını göndersin (Reklamlarım) <small class="wa-alt">(yalnız sistem yöneticisi görür)</small></b><div>Açıkken "Reklamlarım" sekmesi salona görünür; asistan ilgili hizmet sorulunca salonun kampanyasını sohbette bir kez gönderir. Kapalıyken asistan hiç değişmez.</div></div><input type="checkbox" data-ayar="reklam" aria-label="Kampanya reklamları"></div>
         @endif
         <div class="wa-anahtar"><div class="t"><b>Kaçan müşteriye tekrar yazsın</b><div>Fiyat/bilgi alıp randevu oluşturmayan müşteriye belirlediğiniz süre sonunda, mesai saatinde bir kez yazar ve gerçek boş saatlerinizi önerir. Müşteri "dur" yazarsa bir daha yazılmaz.</div></div><input type="checkbox" data-ayar="geri_kazanim" aria-label="Kaçan müşteriye tekrar yazsın"></div>
         <div class="wa-satir" style="margin-top:14px">
            <label class="wa-alt" for="waBekle">Kaç saat sonra yazsın</label>
            <input type="number" id="waBekle" class="wa-girdi" style="width:100px" min="2" max="168">
         </div>
         <label class="wa-alt" for="waKampanya">Fiyatı yüksek bulan müşteriye eklenecek teklif cümleniz (isteğe bağlı — boşsa teklif yapılmaz; asistan kendisi indirim uydurmaz)</label>
         <textarea id="waKampanya" class="wa-girdi" rows="2" maxlength="500" placeholder="Örn. Bu haftaya özel protez tırnakta %10 indirimimiz var."></textarea>
         <div style="margin-top:14px"><button class="wa-btn" id="waAyarKaydet"><i class="bi bi-check2"></i> Ayarları kaydet</button></div>
      </div>
      <div class="wa-kart" style="margin-top:14px">
         <div class="wa-anahtar"><div class="t"><b>Bildirimler telefonunuza geliyor mu?</b><div>Asistan işletmeye bir şey sorduğunda yöneticilerin telefonuna (Randevumcepte uygulaması) ve paneldeki zile bildirim gider. Deneme bildirimi gönderip kimin telefonuna ulaştığını görün.</div></div>
            <button class="wa-btn ikincil" id="waTestBildirim" style="flex:0 0 auto"><i class="bi bi-bell"></i> Test bildirimi gönder</button></div>
         <div id="waTestSonuc" style="font-size:13.5px;line-height:1.6;margin-top:6px"></div>
      </div>
   </div>
</div>
<div class="wa-toast" id="waToast" role="status"></div>

<script>
(function () {
   var SUBE = {!! $J(isset($_GET['sube']) ? (string) $isletme->id : '') !!};
   var CSRF = {!! $J(csrf_token()) !!};
   var AYAR = {!! $J($waAyar) !!};
   var SEKTORLER = {!! $J($waSektorler) !!};
   var KURALLAR = {!! $J($waKurallar) !!};
   var HIZMETLER = {!! $J($waHizmetler) !!};
   var OTO_KURALLAR = {!! $J($waOtoKurallar ?? []) !!} || {}; // kural yoksa hizmet listesinden otomatik eslesme (sektor => {ana:x => {hizmet_id}})
   var KOK = '/isletmeyonetim/whatsapp-asistan';

   function el(id) { return document.getElementById(id); }
   function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
   function url(yol, p) { p = p || {}; if (SUBE) p.sube = SUBE; var q = Object.keys(p).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]); }).join('&'); return KOK + yol + (q ? '?' + q : ''); }
   // Sunucu hatasi / oturum dusmesi (HTML doner) sessizce kaybolmasin: {ok:0, hata} olarak ekrana yazilir
   function yanit(r) {
      return r.text().then(function (t) {
         try { return JSON.parse(t); } catch (e) {
            return { ok: 0, hata: r.status === 401 || r.status === 419 || /<form|giris|login/i.test(t) ? 'Oturumunuz kapanmış görünüyor; sayfayı yenileyip tekrar giriş yapın.' : 'Sunucu hatası (' + r.status + '). Sayfayı yenileyip tekrar deneyin.' };
         }
      });
   }
   function al(yol, p) { return fetch(url(yol, p), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(yanit); }
   function gonder(yol, veri) {
      return fetch(url(yol), { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(veri || {}) })
         .then(yanit);
   }
   function tl(x) { x = Number(x || 0); return x.toLocaleString('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' TL'; }
   function sayi(x) { return Number(x || 0).toLocaleString('tr-TR'); }
   function tarih(s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? esc(s) : d.toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' }) + ' ' + d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }); }
   function saat(s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T')); if (isNaN(d)) return ''; var b = new Date(); return d.toDateString() === b.toDateString() ? d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }) : d.toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' }); }
   function sure(sn) { if (sn == null) return '—'; return sn < 60 ? sn + ' sn' : (Math.round(sn / 6) / 10).toLocaleString('tr-TR') + ' dk'; }
   function toast(m) { var t = el('waToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._z); t._z = setTimeout(function () { t.style.display = 'none'; }, 2600); }
   function hata() { toast('Bir hata oluştu, lütfen tekrar deneyin.'); }
   function trendHtml(v, tersIyi, puan) {
      if (v == null) return '';
      var iyi = tersIyi ? v <= 0 : v >= 0;
      return '<span class="wa-trend ' + (iyi ? '' : 'dus') + '">' + (v >= 0 ? '↑' : '↓') + ' ' + (puan ? (v >= 0 ? '+' : '') + v + ' puan' : '%' + Math.abs(v)) + '</span>';
   }
   var RENK = ['#7c3aed', '#ec4899', '#f97316', '#0ea5e9', '#16a34a', '#8b5cf6', '#e11d48', '#0891b2'];
   // Profil fotografi: sistemdeki musteri fotografi, yoksa WhatsApp fotografi (/avatar/{sohbet}); yuklenemezse bas harfler kalir
   function avatar(ad, id, foto) { var s = String(ad || '?').trim(), h = 0; for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
      var p = s.split(/\s+/), bas = ((p[0] || '?')[0] + (p.length > 1 ? p[p.length - 1][0] : '')).toLocaleUpperCase('tr-TR');
      var src = foto || (id ? url('/avatar/' + id) : '');
      return '<div class="wa-avatar" style="background:' + RENK[h % RENK.length] + '">' + esc(bas)
         + (src ? '<img src="' + esc(src) + '" alt="" loading="lazy" onerror="this.remove()">' : '') + '</div>'; }
   function hapSinif(s) { if (/Dönüş bekliyor|dönmedi|Yanlış numara/.test(s)) return 'k'; if (/cevapladı/.test(s)) return 'y'; return /Randevu alındı|Randevu Alındı|Oluşturuldu|teyit|değişti/.test(s) ? 'y' : (/Fiyat/.test(s) ? 'm' : (/aktarıldı|Personelde/.test(s) ? 's' : (/iptal|durduruldu|Bot/.test(s) ? 'k' : 'g'))); }

   // ---------- sekmeler
   var yuklendi = {}, SEKME_YUKLE = { sohbet: sohbetListe, gk: gkYukle, ogr: ogrYukle, gel: gelisimYukle, reklam: function () { reklamYukle(); } };
   function sekmeAc(p) {
      document.querySelectorAll('.wa-sekme').forEach(function (x) { x.classList.toggle('aktif', x.dataset.p === p); });
      document.querySelectorAll('.wa-panel').forEach(function (x) { x.classList.toggle('aktif', x.id === 'p-' + p); });
      if (!yuklendi[p] && SEKME_YUKLE[p]) { yuklendi[p] = 1; SEKME_YUKLE[p](); }
      if (p === 'kazanc' && sonGrafik) grafikCiz();
      if (p === 'kaz' && sonOzet) { document.querySelectorAll('#waKazanc [data-w]').forEach(function (x) { x.style.width = x.classList.contains('k') ? '42%' : '0'; }); document.querySelectorAll('#waKazanc [data-h]').forEach(function (x) { x.style.height = '0'; }); kazancCanlandir(); }
   }
   el('waSekmeler').addEventListener('click', function (e) { var b = e.target.closest('.wa-sekme'); if (b) sekmeAc(b.dataset.p); });
   var donemSec = el('waDonem');
   donemSec.addEventListener('change', function () { genelYukle(); yuklendi.gk = 0; if (el('p-gk').classList.contains('aktif')) { yuklendi.gk = 1; gkYukle(); } });

   // ---------- mini grafik (sparkline)
   function spark(degerler, renk) {
      var v = (degerler || []).map(function (x) { return x == null ? null : Number(x); });
      // Uzun seride 3 gunluk hareketli ortalama: kucuk tam sayilarda (0/1/2) testere disi gorunmesin
      if (v.length > 14) v = v.map(function (x, i) { var p = [v[i - 1], x, v[i + 1]].filter(function (y) { return y != null; }); return p.length ? p.reduce(function (a, b) { return a + b; }, 0) / p.length : null; });
      var dolu = v.filter(function (x) { return x != null; });
      if (dolu.length < 2) return '<svg class="spark" viewBox="0 0 200 46" preserveAspectRatio="none"><line x1="0" y1="40" x2="200" y2="40" stroke="' + renk + '" stroke-opacity=".25" stroke-width="2"/></svg>';
      var mx = Math.max.apply(null, dolu), mn = Math.min.apply(null, dolu), ar = (mx - mn) || 1, n = v.length, son = null, nok = [];
      v.forEach(function (x, i) { if (x == null) x = son == null ? mn : son; son = x; nok.push([i / (n - 1) * 200, 40 - (x - mn) / ar * 32]); });
      var yol = nok.map(function (p, i) { return (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join(' ');
      var id = 'g' + Math.random().toString(36).slice(2, 8);
      var noktalar = nok.filter(function (p, i) { return n <= 12 || i % Math.ceil(n / 8) === 0 || i === n - 1; })
         .map(function (p) { return '<circle cx="' + p[0].toFixed(1) + '" cy="' + p[1].toFixed(1) + '" r="2.6" fill="#fff" stroke="' + renk + '" stroke-width="1.6"/>'; }).join('');
      return '<svg class="spark" viewBox="0 0 200 46" preserveAspectRatio="none"><defs><linearGradient id="' + id + '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' + renk + '" stop-opacity=".22"/><stop offset="1" stop-color="' + renk + '" stop-opacity="0"/></linearGradient></defs>'
         + '<path d="' + yol + ' L200 46 L0 46 Z" fill="url(#' + id + ')"/><path d="' + yol + '" fill="none" stroke="' + renk + '" stroke-width="2" vector-effect="non-scaling-stroke"/>' + noktalar + '</svg>';
   }

   // ---------- GENEL BAKIS
   var sonOzet = null, sonGrafik = null, grafikTur = 'ay';
   function kpi(sinif, ikon, d, e, trend, seri, renk) {
      return '<div class="wa-kpi ' + sinif + '"><div class="wa-kpi-ust"><div class="wa-kpi-ikon"><i class="bi ' + ikon + '"></i></div><div><div class="wa-kpi-d">' + d + '</div><div class="wa-kpi-e">' + e + '</div></div></div>'
         + trend + spark(seri, renk) + '</div>';
   }
   // Kart bilgi penceresi metinleri ("i" dugmesi): ne gosterir / nasil hesaplanir / ne ise yarar
   var KART_BILGI = {
      'bi-wallet-fill': { b: 'Asistanın Getirdiği Kazanç',
         ne: 'WhatsApp asistanının sizin yerinize aldığı randevuların toplam hizmet tutarı.',
         nasil: 'Asistanın oluşturduğu ve iptal edilmeyen randevuların hizmet fiyatları toplanır. Randevu panelde "geldi" işaretlenince tutar <b>gerçekleşen kazanç</b> olarak sayılır; işaretlenene kadar "randevu tutarı" olarak görünür.',
         neden: 'Asistanın size ne kadar iş getirdiğini doğrudan gösterir. Pakete ödediğiniz ücretle kıyaslayıp yatırımınızın kaç katını geri aldığınızı görürsünüz.' },
      'bi-moon-stars-fill': { b: 'Mesai Dışı Kazanç',
         ne: 'Salonunuz kapalıyken (akşam, gece, kapalı günler) asistanın aldığı randevuların tutarı. Küçük yazı, asistanın bu saatlerde müşterilerinize kaç mesaj yazdığını gösterir.',
         nasil: 'Randevunun alındığı an, işletme ayarlarınızdaki çalışma saatlerinin dışındaysa tutarı bu karta yazılır. Bu saatlerde gönderilen her cevap da mesaj sayısına eklenir.',
         neden: 'Bu müşteriler normalde size ulaşamayıp başka salona gidecekti. Personel yokken kazanılan, tamamen ekstra gelirdir.' },
      'bi-arrow-repeat': { b: 'Geri Kazanılan Müşteri',
         ne: 'Fiyat ya da bilgi alıp randevu oluşturmadan giden ("kaçan") müşterilere asistan tekrar yazar; geri dönüp randevu alanların tutarı bu kartta görünür. Küçük yazı, bu müşterilerle kaç mesaj yazışıldığını gösterir.',
         nasil: 'Asistan, ayarladığınız süre sonra (varsayılan 24 saat) bu müşterilere mesai saatinde bir kez nazik bir mesaj ve gerçek boş saatlerinizi yollar. Mesajdan sonraki 7 gün içinde alınan randevu "geri kazanılmış" sayılır.',
         neden: 'Kaybolacak müşteriyi hiçbir personel emeği olmadan geri getirir. Müşteri "dur" yazarsa bir daha yazılmaz.' },
      'bi-tag-fill': { b: 'Fiyat Verip Randevu Aldırdığı',
         ne: 'Asistanın fiyat söylediği müşterilerden randevu alanların toplam tutarı. Küçük yazı, bu müşterilere kaç mesaj yazıldığını gösterir.',
         nasil: 'Asistan, sistemde kayıtlı hizmet fiyatlarınızı müşteriye söyler. O sohbette alınan ve iptal edilmeyen randevuların tutarı bu karta yazılır.',
         neden: 'Fiyat soran müşteri çoğu zaman cevap gecikince başka salona yazar. Asistan fiyatı anında verip randevuya bağladığı için bu tutar kaçmadan size kalır.' },
      'bi-shield-fill-check': { b: 'Asistanın Kendi Bitirdiği Sohbetler',
         ne: 'Asistanın <b>baştan sona tek başına</b> ilgilendiği sohbetlerden gelen randevu tutarı. Müşteri ne sorduysa asistan cevapladı; sizin ya da personelinizin araya girmesi gerekmedi. Küçük yazı bu sohbetlerde kaç mesaj yazıldığını, alt satırdaki yüzde ise oranı gösterir: %92, 100 müşteriden 92\'siyle hiç uğraşmadınız demektir.',
         nasil: 'Asistan yalnızca birkaç konuyu size sorar: müşteri "yetkiliyle görüşmek istiyorum" derse, şikâyet / ödeme gibi bir konu açılırsa ya da fiyatı bilinmeyen bir model sorulursa. O zaman size bildirim gelir; asistan bu sırada müşteriyle ilgilenmeye (randevu, bilgi) devam eder. Size hiçbir konu sorulmayan sohbetler bu kartta "kendi bitirdiği" sayılır.',
         neden: 'Oran ne kadar yüksekse personeliniz WhatsApp ile o kadar az uğraşır. Oran düşükse fiyatı eksik hizmetleri tamamlayın ve "Öğrenilen Cevaplar" sekmesine bakın; asistan ne kadar çok şey bilirse size o kadar az sohbet bırakır.' },
      'bi-camera-fill': { b: 'Fotoğrafa Fiyat Verildi',
         ne: 'Müşterinin WhatsApp\'tan gönderdiği model fotoğrafına asistan fiyat verir; bu sohbetlerden alınan randevuların tutarı bu kartta görünür. Küçük yazı, bu sohbetlerde kaç mesaj yazıldığını gösterir.',
         nasil: 'Asistan fotoğraftaki uygulamaları tanır (protez, french, taş sayısı…); fiyatı "Fotoğraf Fiyatları" sekmesindeki kurallarınızdan sistem hesaplar. Asistan fiyat uydurmaz.',
         neden: 'Uzmanınızın fotoğrafa bakıp fiyat yazma işini üstlenir; müşteri anında fiyat alır ve randevuya geçer.' }
   };
   function kaz(sinif, ikon, d, e, a, seri, renk, mesaj) {
      // Tasarimdaki kart: solda buyuk ikon, yaninda TL (buyuk) + kart adi + mesaj sayisi (kucuk), en altta mini grafik; sag ustte "i" (bilgi penceresi)
      var detay = a ? String(a).replace(/<[^>]+>/g, '') : '';
      var m = mesaj != null ? '<div class="m"><i class="bi bi-chat-dots"></i>' + sayi(mesaj) + ' mesaj yazıldı</div>' : '';
      if (mesaj != null) detay = sayi(mesaj) + ' mesaj yazıldı' + (detay ? ' · ' + detay : '');
      return '<div class="wa-kaz ' + sinif + '">'
         + (KART_BILGI[ikon] ? '<button type="button" class="wa-i" data-bilgi="' + ikon + '" data-sinif="' + sinif + '" data-deger="' + esc(String(d).replace(/<[^>]+>/g, '')) + '" data-detay="' + esc(detay) + '" aria-label="' + esc(e) + ' nedir?"><i class="bi bi-info-lg"></i></button>' : '')
         + '<div class="wa-kaz-ust"><div class="wa-kpi-ikon"><i class="bi ' + ikon + '"></i></div>'
         + '<div class="wa-kaz-ic"><div class="d">' + d + '</div><div class="e">' + e + '</div>' + m + '</div></div>' + (seri ? spark(seri, renk) : '') + '</div>';
   }
   // Bilgi penceresi (body'ye eklenir, ekran ortasinda)
   function bilgiAc(btn) {
      var k = KART_BILGI[btn.dataset.bilgi]; if (!k) return;
      var bg = document.getElementById('waBilgiBg');
      if (!bg) {
         bg = document.createElement('div'); bg.id = 'waBilgiBg'; bg.className = 'wa-bm-bg';
         bg.addEventListener('click', function (e) { if (e.target === bg || e.target.closest('[data-kapat]')) bg.classList.remove('acik'); });
         document.addEventListener('keydown', function (e) { if (e.key === 'Escape') bg.classList.remove('acik'); });
         document.body.appendChild(bg);
      }
      var bolum = function (ikon, baslik, metin) { return '<div class="wa-bm-b"><div class="wa-bm-bi"><i class="bi ' + ikon + '"></i></div><div><b>' + baslik + '</b><p>' + metin + '</p></div></div>'; };
      bg.innerHTML = '<div class="wa-bm ' + esc(btn.dataset.sinif) + '" role="dialog" aria-modal="true" aria-label="' + esc(k.b) + '">'
         + '<div class="wa-bm-bas"><div class="wa-kpi-ikon"><i class="bi ' + esc(btn.dataset.bilgi) + '"></i></div><div><div class="wa-bm-t">' + esc(k.b) + '</div>'
         + '<div class="wa-bm-d">' + esc(btn.dataset.deger) + (btn.dataset.detay ? ' <span>· ' + esc(btn.dataset.detay) + '</span>' : '') + '</div></div>'
         + '<button type="button" class="wa-bm-x" data-kapat aria-label="Kapat"><i class="bi bi-x-lg"></i></button></div>'
         + '<div class="wa-bm-ic">' + bolum('bi-eye', 'Ne gösterir?', k.ne) + bolum('bi-calculator', 'Nasıl hesaplanır?', k.nasil) + bolum('bi-lightbulb', 'Size ne kazandırır?', k.neden) + '</div>'
         + '<div class="wa-bm-alt"><span>Seçili dönem: ' + esc(donemSec.options[donemSec.selectedIndex].text) + '</span><button type="button" class="wa-btn" data-kapat>Anladım</button></div></div>';
      bg.classList.add('acik');
      var x = bg.querySelector('.wa-bm-x'); if (x) x.focus();
   }
   // Mor alandaki slogan arkasi yaprak (lotus) deseni: tabandan acilan 5 yaprak + 2 kucuk yan yaprak
   var YAPRAK = '<svg class="wa-hero-yaprak" viewBox="0 0 150 150" aria-hidden="true"><defs><linearGradient id="yp" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#fff" stop-opacity=".25"/><stop offset="1" stop-color="#fff"/></linearGradient></defs><g fill="url(#yp)">'      + [-58, -30, 0, 30, 58].map(function (a, i) { var u = i === 2 ? 118 : (i % 4 === 0 ? 78 : 100); return '<path transform="rotate(' + a + ' 75 145)" d="M75 145 C' + (75 - 22) + ' ' + (145 - u * .45) + ' ' + (75 - 14) + ' ' + (145 - u * .85) + ' 75 ' + (145 - u) + ' C' + (75 + 14) + ' ' + (145 - u * .85) + ' ' + (75 + 22) + ' ' + (145 - u * .45) + ' 75 145Z"/>'; }).join('')      + '</g><g fill="none" stroke="#fff" stroke-opacity=".6" stroke-width="1.2">' + [-30, 0, 30].map(function (a) { return '<path transform="rotate(' + a + ' 75 145)" d="M75 140 L75 ' + (a ? 55 : 35) + '"/>'; }).join('') + '</g></svg>';
   // AI asistan karti robotu: tasarimdaki 3D robot gorseli (public/isletmeyonetim_assets/img/wa-ai-robot.png)
   var ROBOT = '<img class="robot" src="/public/isletmeyonetim_assets/img/wa-ai-robot.png" alt="RandevumCepte AI Asistan" width="186" height="160">';

   // ---------- KAZANDIRDIKLARI sekmesi
   function halka(yuzde, renk, ic, alt, boyut) {
      var r = 50, c = 2 * Math.PI * r, y = Math.max(0, Math.min(100, yuzde || 0));
      return '<svg viewBox="0 0 120 120"><circle cx="60" cy="60" r="' + r + '" fill="none" stroke="#f1edf8" stroke-width="12"/>'
         + '<circle cx="60" cy="60" r="' + r + '" fill="none" stroke="' + renk + '" stroke-width="12" stroke-linecap="round" stroke-dasharray="' + (c * y / 100).toFixed(1) + ' ' + c.toFixed(1) + '" transform="rotate(-90 60 60)"/>'
         + '<text x="60" y="' + (alt ? 62 : 68) + '" text-anchor="middle" class="y">' + ic + '</text>' + (alt ? '<text x="60" y="80" text-anchor="middle" class="ye">' + alt + '</text>' : '') + '</svg>';
   }
   function kazandirdiklariHtml(o, p) {
      var kz = o.kazanc, r = o.randevu, g = o.geri_kazanim, c = o.cevap, k = '';
      var gercek = kz.gerceklesen > 0, toplam = gercek ? kz.gerceklesen : kz.beklenen;
      // 1) Hero: toplam kazanc + pakete gore kat + zaman/mesai kazanclari
      var roi = '';
      if (kz.paket_ucret > 0) {
         var mx = Math.max(kz.paket_ucret, toplam, 1);
         roi = '<div class="wk-roi"><div class="wk-roi-s o"><span>Pakete ödediğiniz</span><div class="bar"><i data-w="' + Math.max(2, kz.paket_ucret / mx * 100) + '"></i></div><b>' + tl(kz.paket_ucret) + '</b></div>'
            + '<div class="wk-roi-s k"><span>Asistanın getirdiği</span><div class="bar"><i data-w="' + Math.max(2, toplam / mx * 100) + '"></i></div><b>' + tl(toplam) + '</b></div></div>';
      }
      k += '<div class="wk-hero"><div><span class="wk-ust-e"><i class="bi bi-trophy-fill"></i>Bu dönem asistanın size kazandırdığı</span>'
         + '<div class="wk-buyuk">' + tl(toplam) + (kz.kat ? '<span class="wk-kat"><i class="bi bi-graph-up-arrow"></i>' + String(kz.kat).replace('.', ',') + ' kat</span>' : '') + '</div>'
         + '<div class="wk-alt-e">' + (toplam > 0
            ? (gercek ? 'Asistanın aldığı randevulardan <b>salona gelen</b> müşterilerin hizmet tutarı.' : 'Asistanın aldığı <b>' + sayi(r.adet + g.randevu) + ' randevunun</b> hizmet tutarı. Müşteri gelip randevu "geldi" işaretlenince kesinleşir.')
            : 'Henüz asistanın aldığı bir randevu yok. Müşteriler WhatsApp\'tan yazıp randevu aldıkça tutar burada birikir.') + '</div>' + roi + '</div>'
         + '<div class="wk-cam">'
         + '<div><i class="bi bi-hourglass-split"></i><div class="d">' + String(c.personel_saat).replace('.', ',') + '<small>saat</small></div><div class="e">personelinizin telefona harcamadığı zaman</div></div>'
         + '<div><i class="bi bi-moon-stars-fill"></i><div class="d">' + sayi(c.mesai_disi) + '<small>mesaj</small></div><div class="e">salon kapalıyken cevaplandı</div></div>'
         + '<div><i class="bi bi-lightning-charge-fill"></i><div class="d">' + sure(c.medyan_sure_sn) + '</div><div class="e">müşteriye ortalama cevap süresi</div></div>'
         + '<div><i class="bi bi-chat-square-heart-fill"></i><div class="d">' + sayi(c.toplam) + '<small>mesaj</small></div><div class="e">asistan yazdı · ' + sayi(c.ucretsiz) + ' tanesi ücretsiz</div></div>'
         + '</div></div>';

      // 2) Sizden beklenenler
      var y = '';
      if (o.aktar_bekleyen.length) y += '<a class="kirmizi" data-git="sohbet"><span class="ik"><i class="bi bi-reply-fill"></i></span><span class="t"><b>' + o.aktar_bekleyen.length + ' müşteri sizden dönüş bekliyor</b><span>Asistan bu konuları size sordu; müşteriyle ilgilenmeye devam ediyor</span></span><span class="git">Sohbetler <i class="bi bi-arrow-right"></i></span></a>';
      if (o.kacan.length) y += '<a data-git="gk"><span class="ik"><i class="bi bi-person-dash-fill"></i></span><span class="t"><b>' + o.kacan.length + ' müşteri bilgi alıp randevu almadı</b><span>Asistan bu müşterilere tekrar yazabilir</span></span><span class="git">Listeyi gör <i class="bi bi-arrow-right"></i></span></a>';
      if (p && p.var && p.seviye) y += '<a class="' + (p.kapali ? 'kirmizi' : 'mor') + '"><span class="ik"><i class="bi bi-box-seam"></i></span><span class="t"><b>' + (p.kapali ? 'Paket doldu' : 'Paketin %' + p.yuzde + '\'i kullanıldı') + '</b><span>' + (p.kapali ? 'Asistan ay sonuna kadar cevap vermiyor' : 'Kalan ' + sayi(p.kalan) + ' yapay zekâ mesajı') + '</span></span></a>';
      if (y) k += '<div class="wa-bolum-baslik"><i class="bi bi-bell-fill"></i>Sizden beklenenler</div><div class="wk-yap">' + y + '</div>';

      // 3) Kazanc nereden geldi (halka grafik) + musteri yolculugu (huni)
      var parca = [['Mesai içinde alınan randevular', Math.max(0, r.tutar - r.mesai_disi_tutar), '#7c3aed', sayi(r.adet - r.mesai_disi) + ' randevu'],
                   ['Salon kapalıyken alınanlar', r.mesai_disi_tutar, '#4f46e5', sayi(r.mesai_disi) + ' randevu · akşam, gece, tatil'],
                   ['Geri kazanılan müşteriler', g.beklenen_tutar, '#f97316', sayi(g.randevu) + ' müşteri geri döndü']];
      var top = parca.reduce(function (a, x) { return a + x[1]; }, 0), donut = '';
      if (top > 0) {
         var rr = 46, cc = 2 * Math.PI * rr, bas = 0, yay = '';
         parca.forEach(function (x) { var u = x[1] / top * cc; if (u > 0) yay += '<circle cx="60" cy="60" r="' + rr + '" fill="none" stroke="' + x[2] + '" stroke-width="16" stroke-dasharray="' + Math.max(0, u - 1.5).toFixed(1) + ' ' + cc.toFixed(1) + '" stroke-dashoffset="' + (-bas).toFixed(1) + '" transform="rotate(-90 60 60)"/>'; bas += u; });
         donut = '<div class="wk-donut"><svg viewBox="0 0 120 120"><circle cx="60" cy="60" r="' + rr + '" fill="none" stroke="#f1edf8" stroke-width="16"/>' + yay
            + '<text x="60" y="59" text-anchor="middle" class="orta-d">' + (top >= 100000 ? sayi(Math.round(top / 1000)) + ' bin' : sayi(Math.round(top))) + '</text><text x="60" y="75" text-anchor="middle" class="orta-e">TL toplam</text></svg>'
            + '<div class="wk-lej">' + parca.map(function (x) { return '<div><i style="background:' + x[2] + '"></i><span><b>' + x[0] + '</b><small>' + x[3] + '</small></span><b>' + tl(x[1]) + ' <small style="display:inline">%' + Math.round(x[1] / top * 100) + '</small></b></div>'; }).join('') + '</div></div>'
            + '<div class="wk-alt-not">Tutarlar asistanın aldığı randevuların hizmet fiyatlarından hesaplanır; iptal edilen randevular sayılmaz.</div>';
      } else donut = '<div class="wk-bos"><b>Henüz tutar yok.</b> Asistan randevu aldıkça kazancın nereden geldiği (mesai içi, salon kapalıyken, geri kazanılan müşteri) burada görünür. Hizmetlerinize fiyat girili değilse tutar 0 görünür; <b>Ayarlar &gt; Hizmetler</b> ekranından fiyatları kontrol edin.</div>';
      var hu = o.huni, hmx = Math.max(1, hu.yazdi), adim = [['bi-chat-dots-fill', 'Yazdı', hu.yazdi, '#6d28d9'], ['bi-info-circle-fill', 'Bilgi / fiyat aldı', hu.bilgi, '#9333ea'], ['bi-calendar-check-fill', 'Randevu aldı', hu.randevu, '#c026d3'], ['bi-shop', 'Salona geldi', hu.geldi, '#16a34a']];
      var huni = '<div class="wk-huni">' + adim.map(function (x, i) {
         var once = i ? adim[i - 1][2] : 0, gec = i ? (once ? Math.round(x[2] / once * 100) : 0) : null;
         return (i ? '<span class="gec"><i class="bi bi-arrow-down"></i>%' + gec + ' devam etti</span>' : '')
            + '<div class="k" data-w="' + Math.max(42, Math.round(x[2] / hmx * 100)) + '" style="width:42%;background:linear-gradient(90deg,' + x[3] + ',' + x[3] + 'cc)"><span><i class="bi ' + x[0] + '"></i>' + x[1] + '</span><b>' + sayi(x[2]) + '</b></div>';
      }).join('') + '</div><div class="wk-alt-not">"Salona geldi", panelde randevu "geldi" işaretlendikçe artar.' + (hu.randevu && !hu.geldi ? ' <b>Randevuları "geldi" işaretlerseniz gerçekleşen kazancınız da görünür.</b>' : '') + '</div>';
      k += '<div class="wk-iki"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-pie-chart-fill" style="color:#7c3aed"></i>Kazanç nereden geldi?</h4></div>' + donut + '</div>'
         + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-funnel-fill" style="color:#c026d3"></i>Müşteri yolculuğu</h4></div>' + (hu.yazdi ? huni : '<div class="wk-bos">Bu dönemde henüz WhatsApp\'tan yazan müşteri yok.</div>') + '</div></div>';

      // 4) Paket + memnuniyet + asistanin yaptigi isler
      var paket;
      if (p && p.var) {
         var pr = p.kapali ? '#dc2626' : (p.seviye ? '#f59e0b' : '#7c3aed');
         paket = '<div class="wk-halka">' + halka(p.yuzde, pr, '%' + p.yuzde, 'kullanıldı') + '<div class="wk-satirlar">'
            + '<div><span>Yapay zekâ mesajı</span><b>' + sayi(p.kullanilan) + ' / ' + sayi(p.dahil) + '</b></div><div><span>Kalan</span><b>' + sayi(p.kalan) + '</b></div>'
            + '<div><span>Ücretsiz cevap</span><b style="color:#16a34a">' + sayi(c.ucretsiz) + '</b></div></div></div>'
            + '<div class="wk-alt-not">Hazır cevaplar ve öğrenilen cevaplar <b>paketten düşmez</b>; yalnızca yapay zekânın yazdığı mesajlar sayılır.' + (p.kapali ? ' <b style="color:#dc2626">Paket doldu, asistan ay sonuna kadar cevap vermiyor.</b>' : '') + '</div>';
      } else {
         var uo = c.toplam ? Math.round(c.ucretsiz / c.toplam * 100) : 0;
         paket = '<div class="wk-halka">' + halka(uo, '#16a34a', '%' + uo, 'ücretsiz') + '<div class="wk-satirlar"><div><span>Toplam cevap</span><b>' + sayi(c.toplam) + '</b></div><div><span>Ücretsiz cevap</span><b style="color:#16a34a">' + sayi(c.ucretsiz) + '</b></div><div><span>Yapay zekâ</span><b>' + sayi(c.ai) + '</b></div></div></div>'
            + '<div class="wk-alt-not">Hazır ve öğrenilen cevaplar ücretsizdir. Bu işletme için mesaj paketi tanımlı değil.</div>';
      }
      var s = o.sohbet, mt = s.memnun + s.memnun_degil, memnun;
      if (mt) {
         memnun = '<div class="wk-memnun"><span class="d">%' + s.memnuniyet_orani + '</span><span class="e">memnun kaldı</span></div>'
            + '<div class="wk-yuz"><i style="width:' + (s.memnun / mt * 100) + '%;background:linear-gradient(90deg,#22c55e,#16a34a)"></i><i style="width:' + (s.memnun_degil / mt * 100) + '%;background:#f87171"></i></div>'
            + '<div class="wk-satirlar"><div><span><i class="bi bi-emoji-smile-fill" style="color:#16a34a"></i> Memnun</span><b>' + sayi(s.memnun) + ' müşteri</b></div><div><span><i class="bi bi-emoji-frown-fill" style="color:#ef4444"></i> Memnun değil</span><b>' + sayi(s.memnun_degil) + ' müşteri</b></div></div>'
            + '<div class="wk-alt-not">Asistan randevudan sonra müşteriye "memnun kaldınız mı?" diye sorar.</div>';
      } else memnun = '<div class="wk-bos"><b>Henüz cevap yok.</b> Asistan randevudan sonra müşteriye "memnun kaldınız mı?" diye sorar; gelen cevaplar burada görünür. <b>Ayarlar</b> sekmesinden memnuniyet sorusunun açık olduğunu kontrol edin.</div>';
      var is = [['bi-calendar-plus-fill', '#7c3aed', '#efe6fd', r.adet + g.randevu, 'randevu oluşturdu'], ['bi-arrow-left-right', '#2563eb', '#e0edff', r.guncelle, 'randevu değiştirdi'],
                ['bi-check2-all', '#16a34a', '#dcfce7', r.teyit, 'randevu teyidi aldı'], ['bi-x-circle-fill', '#dc2626', '#fee2e2', r.iptal_islem, 'iptal isteğini işledi'],
                ['bi-tag-fill', '#f97316', '#ffedd5', s.fiyat_aldi, 'müşteriye fiyat verdi'], ['bi-camera-fill', '#db2777', '#fce7f3', o.gorsel.toplam, 'fotoğraf inceledi']];
      k += '<div class="wk-uc"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-box-seam" style="color:#7c3aed"></i>' + (p && p.var ? 'Paket kullanımı' : 'Cevap dağılımı') + '</h4></div>' + paket + '</div>'
         + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-emoji-heart-eyes-fill" style="color:#16a34a"></i>Müşteri memnuniyeti</h4></div>' + memnun + '</div>'
         + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-robot" style="color:#2563eb"></i>Asistan neler yaptı?</h4></div><div class="wk-isler">'
         + is.map(function (x) { return '<div><i class="bi ' + x[0] + '" style="color:' + x[1] + ';background:' + x[2] + '"></i><span><b>' + sayi(x[3]) + '</b>' + x[4] + '</span></div>'; }).join('') + '</div></div></div>';

      // 5) Musteriler ne soruyor + ne zaman yaziyor
      var hz = o.populer_hizmet || {}, hk = Object.keys(hz), hmax = hk.length ? hz[hk[0]] : 1;
      var hizmet = hk.length ? '<div class="wk-hizmet">' + hk.map(function (x, i) { return '<div class="s"><span class="no">' + (i ? i + 1 : '<i class="bi bi-trophy-fill"></i>') + '</span><div style="min-width:0"><div class="ad">' + esc(x) + '</div><div class="bar"><i data-w="' + Math.round(hz[x] / hmax * 100) + '" style="width:0"></i></div></div><span class="n">' + sayi(hz[x]) + ' sohbet</span></div>'; }).join('') + '</div>'
         : '<div class="wk-bos">Henüz hizmet sorusu yok. Müşteriler bir hizmet hakkında yazdıkça en çok sorulanlar burada sıralanır.</div>';
      var sd = o.saat_dagilim || [], smx = Math.max.apply(null, sd.concat([1])), tepe = Object.keys(o.yogun_saat || {}).map(Number), saat = '';
      if (sd.length && sd.some(function (x) { return x > 0; })) {
         saat = '<div class="wk-saat">' + sd.map(function (n, h) { var gece = h < 9 || h >= 21, tp = tepe.indexOf(h) > -1 && n > 0;
            return '<div class="c' + (tp ? ' tepe' : (gece ? ' gece' : '')) + '" title="' + String(h).padStart(2, '0') + ':00 · ' + n + ' mesaj">' + (tp ? '<em>' + n + '</em>' : '') + '<i data-h="' + Math.round(n / smx * 100) + '" style="height:0"></i></div>'; }).join('') + '</div>'
            + '<div class="wk-saat-x">' + sd.map(function (n, h) { return '<span>' + (h % 3 === 0 ? String(h).padStart(2, '0') : '') + '</span>'; }).join('') + '</div>'
            + '<div class="wk-saat-lej"><span class="g">Gündüz</span><span class="n">Akşam / gece (21:00–09:00)</span><span class="t">En yoğun 3 saat</span></div>';
      } else saat = '<div class="wk-bos">Henüz mesaj yok. Asistan cevap verdikçe müşterilerin günün hangi saatinde yazdığı burada görünür.</div>';
      k += '<div class="wk-iki"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-stars" style="color:#ec4899"></i>Müşteriler en çok neyi soruyor?</h4></div>' + hizmet + '</div>'
         + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-clock-fill" style="color:#4f46e5"></i>Müşteriler ne zaman yazıyor?</h4></div>' + saat + '</div></div>';
      return k;
   }
   function kazancCanlandir() {
      setTimeout(function () {
         document.querySelectorAll('#waKazanc [data-w]').forEach(function (x) { x.style.width = x.dataset.w + '%'; });
         document.querySelectorAll('#waKazanc [data-h]').forEach(function (x) { x.style.height = Math.max(2, x.dataset.h) + '%'; });
      }, 60);
   }

   function genelYukle() {
      al('/ozet', { donem: donemSec.value }).then(function (r) {
         if (!r.ok) { el('waGenel').innerHTML = '<div class="wa-bos">' + esc(r.hata || 'Yüklenemedi') + '</div>'; return; }
         sonOzet = r.ozet; var o = r.ozet, t = r.trend, s = r.seri, p = r.paket, a = r.asistan, kz = o.kazanc, kt = o.kart || {}, h = '';
         var toplamRandevu = o.randevu.adet + o.geri_kazanim.randevu, k = '';
         // ===== GENEL BAKIS (tasarimla birebir): hero, 5 kart, grafik + son konusmalar, hizli islemler + AI kart
         h += '<div class="wa-hero"><div class="wa-hero-ikon"><i class="bi bi-whatsapp"></i></div>'
            + '<div class="wa-hero-bolum ilk"><div><div class="wa-hero-e">WhatsApp üzerinden toplam</div><div class="wa-hero-d">' + sayi(o.sohbet.gelen_mesaj) + ' <small>mesaj aldınız</small>' + trendHtml(t.mesaj) + '</div></div></div>'
            + '<div class="wa-hero-bolum"><i class="bi bi-calendar-check"></i><div><div class="wa-hero-e">Toplam Randevu</div><div class="wa-hero-d">' + sayi(toplamRandevu) + trendHtml(t.randevu) + '</div></div></div>'
            + '<div class="wa-hero-bolum"><i class="bi bi-people-fill"></i><div><div class="wa-hero-e">Yeni Müşteri</div><div class="wa-hero-d">' + sayi(o.randevu.yeni_musteri) + trendHtml(t.yeni) + '</div></div></div>'
            + '<div class="wa-hero-bolum"><i class="bi bi-clock-history"></i><div><div class="wa-hero-e">Ortalama Yanıt Süresi</div><div class="wa-hero-d">' + sure(o.cevap.medyan_sure_sn) + trendHtml(t.sure, true) + '</div></div></div>'
            + '<div class="wa-hero-katman"></div>' + YAPRAK
            + '<div class="wa-hero-yazi">Daha fazla müşteri,<br>daha fazla randevu!<svg width="150" height="12" viewBox="0 0 150 12"><path d="M4 8 Q75 -2 146 6" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg></div></div>';
         // kazandirdiklari kartlari (mor bannerin hemen altinda)
         h += '<div class="wa-bolum-baslik"><i class="bi bi-graph-up-arrow"></i>Asistanın size kazandırdıkları</div><div class="wa-kazanclar">'
            + kaz('wa-k-mor', 'bi-wallet-fill', tl(kz.gerceklesen > 0 ? kz.gerceklesen : kz.beklenen), kz.gerceklesen > 0 ? 'Gerçekleşen kazanç' : 'Randevu tutarı', kz.kat ? 'Pakete ödediğinizin <b>' + String(kz.kat).replace('.', ',') + ' katı</b>' : (kz.gerceklesen > 0 ? 'salona gelen müşterilerden' : 'gelişler "geldi" işaretlenince kesinleşir'), s.tutar, '#7c3aed', o.cevap.toplam)
            + kaz('wa-k-lila', 'bi-moon-stars-fill', tl(kz.mesai_disi), 'Mesai dışı kazanç', sayi(o.randevu.mesai_disi) + ' randevu salon kapalıyken alındı', s.mesai_tutar, '#4f46e5', o.cevap.mesai_disi)
            + kaz('wa-k-tur', 'bi-arrow-repeat', tl(o.geri_kazanim.tutar > 0 ? o.geri_kazanim.tutar : o.geri_kazanim.beklenen_tutar), 'Geri kazanılan müşteri', sayi(o.geri_kazanim.randevu) + ' kaçan müşteri geri döndü · ' + sayi(o.geri_kazanim.yazildi) + ' müşteriye yazıldı', s.geri_kazanim, '#f97316', kt.geri_kazanim ? kt.geri_kazanim.mesaj : o.geri_kazanim.yazildi)
            + kaz('wa-k-mavi', 'bi-tag-fill', tl(kt.fiyat ? kt.fiyat.tutar : 0), 'Fiyat verip randevu aldırdığı', sayi(o.sohbet.fiyat_aldi) + ' müşteriye fiyat verildi, ' + sayi(o.sohbet.fiyattan_randevu) + ' tanesi randevu aldı', s.tutar, '#2563eb', kt.fiyat ? kt.fiyat.mesaj : null)
            + kaz('wa-k-yesil', 'bi-shield-fill-check', tl(kt.cozulen ? kt.cozulen.tutar : 0), 'Kendi bitirdiği sohbetler', o.sohbet.yazisma && o.sohbet.cozulen != null ? ('%' + o.sohbet.cozulen_orani + ' · ' + o.sohbet.yazisma + ' sohbetin ' + o.sohbet.cozulen + ' tanesi personele gerek kalmadan bitti') : 'personele gerek kalmadan biten sohbetler', s.cozulen, '#16a34a', kt.cozulen ? kt.cozulen.mesaj : null)
            + kaz('wa-k-pembe', 'bi-camera-fill', tl(kt.gorsel ? kt.gorsel.tutar : 0), 'Fotoğrafa fiyat verildi', sayi(o.gorsel.fiyat_verildi) + ' fotoğrafa fiyat verildi, ' + sayi(o.gorsel.randevu) + ' tanesi randevuya döndü', s.gorsel, '#db2777', kt.gorsel ? kt.gorsel.mesaj : null)
            + '</div>';
         // grafik + son konusmalar
         h += '<div class="wa-orta"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-bar-chart-fill" style="color:#7c3aed"></i>Performans Grafiği</h4>'
            + '<div class="wa-toggle" id="waGrafikTur"><button data-g="hafta">Hafta</button><button data-g="ay">Ay</button><button data-g="yil">Yıl</button></div></div>'
            + '<div class="wa-lejant"><span class="r">Randevu Sayısı</span><span class="m">Mesaj Sayısı</span></div><div class="wa-grafik" id="waGrafik"><div class="wa-ipucu" id="waIpucu"></div></div></div>'
            + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-whatsapp" style="color:#25D366"></i>Son WhatsApp Konuşmaları</h4><button class="wa-tumu" data-git="sohbet">Tümünü Gör <i class="bi bi-arrow-right"></i></button></div>'
            + '<div id="waSonKonusmalar"><div class="wa-bos">Yükleniyor…</div></div></div></div>';
         // hizli islemler (panelin gercek sayfalari) + AI kart
         // Kapali ozellik soluk: ustune gelince nedeni, tiklayinca acilacagi sekme
         var ok = function (acik, metin, git, neden) { return '<li class="' + (acik ? '' : 'kapali') + '"' + (!acik && git ? ' data-git="' + git + '" title="' + esc(neden) + '"' : '') + '><i class="bi bi-check-circle-fill"></i>' + metin + (!acik && git ? ' <span class="wa-ac">Aç</span>' : '') + '</li>'; };
         var panel = function (yol) { return '/isletmeyonetim/' + yol + (SUBE ? '?sube=' + encodeURIComponent(SUBE) : ''); };
         h += '<div class="wa-alt-sira"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-lightning-charge-fill" style="color:#f59e0b"></i>Hızlı İşlemler</h4></div><div class="wa-hizli">'
            + '<a class="wa-h1" href="' + panel('randevular') + '"><i class="bi bi-calendar-plus"></i>Yeni Randevu Oluştur</a>'
            + '<a class="wa-h2" href="' + panel('musteriler') + '"><i class="bi bi-people-fill"></i>Müşteri Ekle</a>'
            + '<a class="wa-h3" href="' + panel('kampanya_yonetimi') + '"><i class="bi bi-megaphone"></i>Kampanya Yayınla</a>'
            + '<a class="wa-h4" href="' + panel('raporlar') + '"><i class="bi bi-bar-chart-line"></i>Raporları Görüntüle</a></div></div>'
            + '<div class="wa-kart wa-ai">' + ROBOT + '<div class="orta-ic"><h4>RandevumCepte AI Asistan <span class="wa-aktif ' + (a.acik && !(p && p.kapali) ? '' : 'kapali') + '"><i class="bi bi-check-circle-fill"></i>' + (a.acik ? (p && p.kapali ? 'Paket doldu' : 'Aktif') : 'Kapalı') + '</span></h4>'
            + '<p>Tüm müşterilerinize akıllı, hızlı ve kişiselleştirilmiş hizmet sunun.</p><button class="wa-btn" data-git="ayar">Ayarları Düzenle <i class="bi bi-arrow-right"></i></button></div>'
            + '<ul class="wa-liste-ok">' + ok(a.gorsel, 'Görsel Analiz (Fotoğraf)', 'gorsel', 'Kapalı: Fotoğraf Fiyatları sekmesinden fiyat kurallarını girince (ve Ayarlar > Fotoğraftan fiyat versin açıkken) açılır.') + ok(a.fiyat, 'Akıllı Fiyatlandırma', 'ayar', 'Kapalı: Ayarlar > Fiyat sorularını cevaplasın açılınca çalışır.') + ok(true, 'Randevu Yönetimi') + ok(true, 'Müşteri Desteği') + '</ul></div></div>';
         el('waGenel').innerHTML = h;
         el('waKazanc').innerHTML = kazandirdiklariHtml(o, p); kazancCanlandir(); fgYenile();
         grafikYukle(grafikTur);
         sonKonusmalar();
      }).catch(function () { el('waGenel').innerHTML = '<div class="wa-bos">Rapor yüklenemedi.</div>'; });
   }
   el('waKazanc').addEventListener('click', function (e) {
      var g = e.target.closest('[data-git]'); if (g) { sekmeAc(g.dataset.git); window.scrollTo({ top: 0, behavior: 'smooth' }); }
   });
   el('waGenel').addEventListener('click', function (e) {
      var ib = e.target.closest('[data-bilgi]'); if (ib) { bilgiAc(ib); return; }
      var g = e.target.closest('[data-git]'); if (g) { sekmeAc(g.dataset.git); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
      var gt = e.target.closest('#waGrafikTur button'); if (gt) { grafikYukle(gt.dataset.g); return; }
      var k = e.target.closest('[data-sohbet]'); if (k) { sekmeAc('sohbet'); sohbetAc(k.dataset.sohbet); }
   });

   function sonKonusmalar() {
      var b = new Date(), o = new Date(); o.setDate(o.getDate() - 30);
      al('/sohbetler', { baslangic: o.toISOString().slice(0, 10), bitis: b.toISOString().slice(0, 10) }).then(function (r) {
         var hedef = el('waSonKonusmalar'); if (!hedef) return;
         if (!r.ok || !r.liste.length) { hedef.innerHTML = '<div class="wa-bos">Son 30 günde WhatsApp konuşması yok.</div>'; return; }
         // Tasarimdaki durum etiketleri: Randevu Oluşturuldu / Fiyat Verildi / Bilgi Verildi
         // Tasarimdaki 4 etiket: Randevu Oluşturuldu / Randevu Alındı / Fiyat Verildi / Bilgi Verildi
         var etiket4 = function (x) { if (/Dönüş bekliyor|İşletme/.test(x.durum)) return x.durum; if (x.durum === 'Randevu alındı') return 'Randevu Oluşturuldu'; if (/teyit|değişti/.test(x.durum)) return 'Randevu Alındı'; return Number(x.fiyat) ? 'Fiyat Verildi' : 'Bilgi Verildi'; };
         hedef.innerHTML = r.liste.slice(0, 5).map(function (x) { return konusmaSatiri(Object.assign({}, x, { durum: etiket4(x) }), true); }).join('');
      }).catch(function () {});
   }
   function konusmaSatiri(x, ozet) {
      if (!ozet) {
         // Sohbetler listesi (dar sutun): ad + saat ust satirda, mesaj, altta durum etiketi (cakisma olmaz)
         return '<div class="wa-konusma liste" data-sohbet="' + x.id + '" tabindex="0">' + avatar(x.ad || x.telefon, x.id, x.foto)
            + '<div class="ic"><div class="ust"><span class="ad">' + esc(x.ad || x.telefon || 'Müşteri') + '</span><span class="sa">' + saat(x.tarih) + '</span></div>'
            + '<div class="mt">' + (x.gorsel ? '<i class="bi bi-camera-fill"></i> ' : '') + esc(String(x.son_mesaj || '…').replace(/^\[fotoğraf\]\s*/, '')) + '</div>'
            + '<span class="wa-hap ' + hapSinif(x.durum) + '">' + esc(x.durum) + '</span></div></div>';
      }
      return '<div class="wa-konusma' + (ozet ? ' ozet' : '') + '" data-sohbet="' + x.id + '" tabindex="0">' + avatar(x.ad || x.telefon, x.id, x.foto) + '<div class="ic"><div class="ad">' + esc(x.ad || x.telefon || 'Müşteri') + '</div><div class="mt">' + esc(x.son_mesaj || '…') + '</div></div>'
         + '<div class="sa">' + saat(x.tarih) + '</div><span class="wa-hap ' + hapSinif(x.durum) + '">' + esc(x.durum) + '</span><i class="bi bi-chevron-right"></i></div>';
   }

   // ---------- performans grafigi (bar: randevu, cizgi: mesaj)
   var AYLAR = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
   var AYLAR_UZUN = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
   function etiketYaz(e, birim, uzun) { var p = e.split('-'), a = (uzun ? AYLAR_UZUN : AYLAR)[+p[1] - 1]; return birim === 'ay' ? a + (uzun ? ' ' + p[0] : '') : (+p[2]) + ' ' + a; }
   function grafikYukle(g) {
      grafikTur = g;
      document.querySelectorAll('#waGrafikTur button').forEach(function (b) { b.classList.toggle('aktif', b.dataset.g === g); });
      al('/grafik', { g: g }).then(function (r) { if (r.ok) { sonGrafik = r; grafikCiz(); } }).catch(function () {});
   }
   function grafikCiz() {
      var kap = el('waGrafik'); if (!kap || !sonGrafik || !kap.clientWidth) return;
      var r = sonGrafik, W = kap.clientWidth, H = kap.clientHeight, sol = 40, sag = 14, ust = 12, alt = 28;
      var n = r.etiket.length, gw = W - sol - sag, gh = H - ust - alt;
      // Iki eksen: mesaj (cizgi, sol) ve randevu (cubuk, sag) cok farkli buyuklukte -> her biri kendi olceginde
      // 4 esit, yuvarlak adim (1, 2, 2.5, 5 x 10^k; tam sayi): 0-40-80-120-160 gibi
      function olcek(dizi) { var mx = Math.max(1, Math.max.apply(null, dizi)), ham = mx / 4, us = Math.pow(10, Math.floor(Math.log10(ham))), a = us;
         [1, 2, 2.5, 5, 10].some(function (k) { a = k * us; return a >= ham; }); a = Math.max(1, Math.ceil(a)); return { ust: a * 4 }; }
      var om = olcek(r.mesaj), or = olcek(r.randevu), cizgi = 4;
      var bw = gw / n, svg = '<svg viewBox="0 0 ' + W + ' ' + H + '">';
      for (var i = 0; i <= cizgi; i++) { var y = ust + gh - i / cizgi * gh;
         svg += '<line x1="' + sol + '" x2="' + (W - sag) + '" y1="' + y + '" y2="' + y + '" stroke="#efeaf7"/>'
            + '<text x="' + (sol - 8) + '" y="' + (y + 4) + '" text-anchor="end" font-size="11" fill="#8a82a3">' + sayi(Math.round(om.ust * i / cizgi)) + '</text>'
            ; }
      var yv = function (v) { return ust + gh - v / om.ust * gh; }, yr = function (v) { return ust + gh - v / or.ust * gh; };
      r.mesaj.forEach(function (v, i) { var x = sol + i * bw + bw * 0.18, w = Math.max(2, bw * 0.64), y = yv(v);
         svg += '<rect x="' + x.toFixed(1) + '" y="' + y.toFixed(1) + '" width="' + w.toFixed(1) + '" height="' + (ust + gh - y).toFixed(1) + '" rx="' + Math.min(5, w / 2) + '" fill="' + (i === n - 1 ? '#6d28d9' : '#d8c8f7') + '"/>'; });
      var yol = r.randevu.map(function (v, i) { return (i ? 'L' : 'M') + (sol + i * bw + bw / 2).toFixed(1) + ' ' + yr(v).toFixed(1); }).join(' ');
      svg += '<path d="' + yol + '" fill="none" stroke="#6d28d9" stroke-width="2.5" stroke-linejoin="round"/>';
      var her = Math.ceil(n / (W < 520 ? 4 : 8));
      r.randevu.forEach(function (v, i) { var cx = sol + i * bw + bw / 2;
         if (n <= 31) svg += '<circle cx="' + cx.toFixed(1) + '" cy="' + yr(v).toFixed(1) + '" r="' + (i === n - 1 ? 5 : 3) + '" fill="' + (i === n - 1 ? '#fff' : '#6d28d9') + '" stroke="#6d28d9" stroke-width="2"/>';
         // Etiket: her N gunde bir; son gun yalniz bir oncekiyle cakismiyorsa (N/2'den uzaksa)
         if (i % her === 0 || (i === n - 1 && i % her >= her / 2)) svg += '<text x="' + cx.toFixed(1) + '" y="' + (H - 8) + '" text-anchor="middle" font-size="11.5" fill="#6b6483">' + etiketYaz(r.etiket[i], r.birim) + '</text>';
         svg += '<rect class="wa-hit" data-i="' + i + '" x="' + (sol + i * bw).toFixed(1) + '" y="' + ust + '" width="' + bw.toFixed(1) + '" height="' + gh + '" fill="transparent"/>'; });
      // Son noktada sabit etiket (tasarimdaki "30 Kasım · 892 Randevu" kutusu)
      var sx = sol + (n - 1) * bw + bw / 2, sy = yr(r.randevu[n - 1]);
      var sabit = '<div class="wa-ipucu wa-sabit" id="waSabit" style="display:block;right:' + Math.max(0, W - sx - 10) + 'px;top:' + Math.max(0, sy - 62) + 'px"><b>'
         + etiketYaz(r.etiket[n - 1], r.birim, true) + '</b><span style="color:#6d28d9">●</span> ' + sayi(r.randevu[n - 1]) + ' Randevu</div>';
      kap.innerHTML = svg + '</svg>' + sabit + '<div class="wa-ipucu" id="waIpucu"></div>';
      kap.onmousemove = function (e) {
         var h = e.target.closest('.wa-hit'), ip = el('waIpucu'), sb = el('waSabit');
         if (sb) sb.style.display = h ? 'none' : 'block';
         if (!h) { ip.style.display = 'none'; return; }
         var i = +h.dataset.i, b = kap.getBoundingClientRect();
         ip.innerHTML = '<b>' + etiketYaz(r.etiket[i], r.birim) + '</b><span style="color:#6d28d9">●</span> ' + sayi(r.randevu[i]) + ' randevu<br><span style="color:#c4b5fd">●</span> ' + sayi(r.mesaj[i]) + ' mesaj';
         ip.style.display = 'block';
         var x = e.clientX - b.left + 14; if (x + ip.offsetWidth > W) x = e.clientX - b.left - ip.offsetWidth - 14;
         ip.style.left = x + 'px'; ip.style.top = Math.max(0, e.clientY - b.top - 40) + 'px';
      };
      kap.onmouseleave = function () { var ip = el('waIpucu'), sb = el('waSabit'); if (ip) ip.style.display = 'none'; if (sb) sb.style.display = 'block'; };
   }
   var rz; window.addEventListener('resize', function () { clearTimeout(rz); rz = setTimeout(grafikCiz, 150); });
   // Menu acilip sayfa daralinca (pencere boyutu degismeden) grafik yeni genislige gore yeniden cizilsin
   if (window.ResizeObserver) { var sonGen = 0; new ResizeObserver(function () { var g = el('waGrafik'); if (g && g.clientWidth !== sonGen) { sonGen = g.clientWidth; clearTimeout(rz); rz = setTimeout(grafikCiz, 120); } }).observe(document.querySelector('.wa')); }
   genelYukle();

   // ---------- SOHBETLER
   var bugun = new Date(), once = new Date(); once.setDate(once.getDate() - 6);
   el('waBas').value = once.toISOString().slice(0, 10); el('waBit').value = bugun.toISOString().slice(0, 10);
   el('waSohbetYukle').addEventListener('click', sohbetListe);
   el('waAra').addEventListener('keydown', function (e) { if (e.key === 'Enter') sohbetListe(); });
   function sohbetListe() {
      el('waSohbetListe').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/sohbetler', { baslangic: el('waBas').value, bitis: el('waBit').value, q: el('waAra').value }).then(function (r) {
         if (!r.ok) { el('waSohbetListe').innerHTML = '<div class="wa-bos">' + esc(r.hata || '') + '</div>'; return; }
         if (!r.liste.length) {
            var araT = el('waAra').value.replace(/\D/g, '');
            el('waSohbetListe').innerHTML = '<div class="wa-bos">' + (el('waAra').value ? 'Bu aramayla sohbet bulunamadı.' : 'Bu tarihlerde sohbet yok.') + '</div>';
            // Telefonla aranip bulunamadiysa: neden? (hariç numara / mesaj hic gelmedi / islenemedi)
            if (araT.length >= 10) al('/numara-kontrol', { tel: araT }).then(function (k) {
               if (!k.ok) return;
               el('waSohbetListe').innerHTML = '<div class="wa-kart" style="box-shadow:none;border:1px solid #eadff7;padding:12px;font-size:13.5px;line-height:1.55">'
                  + '<b>Numara kontrolü</b><ul style="margin:6px 0 6px 18px;padding:0">' + k.neden.map(function (n) { return '<li>' + esc(n) + '</li>'; }).join('') + '</ul>'
                  + (k.gelen.length ? '<div style="color:#64748b">Son gelen mesajlar:</div>' + k.gelen.map(function (g) { return '<div style="color:#475569">' + tarih(g.tarih) + ' — ' + esc(g.metin) + ' <i>(' + esc(g.durum) + ')</i></div>'; }).join('') : '')
                  + (k.giden_14g ? '<div style="color:#64748b;margin-top:4px">Son 14 günde bu numaraya sistemden ' + k.giden_14g + ' mesaj gitti.</div>' : '')
                  + '</div>';
            }).catch(function () {});
            return;
         }
         el('waSohbetListe').innerHTML = r.liste.map(function (x) { return konusmaSatiri(x); }).join('');
         // Acik sohbet yoksa en sonuncusu acilir (sag taraf bos kalmasin); aciksa listede secili gorunur
         var acik = el('waSohbetDetay').dataset.acik, satir = acik && document.querySelector('#waSohbetListe [data-sohbet="' + acik + '"]');
         if (satir) satir.classList.add('secili');
         else if (!acik) { var ilk = document.querySelector('#waSohbetListe [data-sohbet]'); if (ilk) { ilk.classList.add('secili'); sohbetAc(ilk.dataset.sohbet); } }
      }).catch(function () { el('waSohbetListe').innerHTML = '<div class="wa-bos">Sohbetler yüklenemedi. Sayfayı yenileyip tekrar deneyin.</div>'; });
   }
   el('waSohbetListe').addEventListener('click', function (e) {
      var o = e.target.closest('[data-sohbet]'); if (!o) return;
      document.querySelectorAll('#waSohbetListe .wa-konusma').forEach(function (x) { x.classList.toggle('secili', x === o); });
      sohbetAc(o.dataset.sohbet);
   });
   function sohbetAc(id) {
      el('waSohbetDetay').dataset.acik = id;
      if (!yuklendi.sohbet) { yuklendi.sohbet = 1; sohbetListe(); }
      el('waSohbetDetay').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/sohbet/' + id).then(function (r) {
         if (!r.ok) { el('waSohbetDetay').innerHTML = '<div class="wa-bos">' + esc(r.hata || '') + '</div>'; return; }
         var s = r.sohbet, h = '<div class="wa-kart-bas"><div style="display:flex;align-items:center;gap:12px">' + avatar(s.ad || s.telefon, s.id, null) + '<div><b style="font-size:16px">' + esc(s.ad || 'Müşteri') + '</b><div class="wa-alt">' + esc(s.telefon || 'gizli numara') + '</div></div><span class="wa-hap ' + hapSinif(s.durum) + '">' + esc(s.durum) + '</span></div>'
            + '<div class="wa-bas-btn"><button class="wa-btn ikincil" data-yol="' + s.id + '" title="Salonun adresini ve Google Haritalar bağlantısını bu müşteriye gönderir"><i class="bi bi-geo-alt-fill"></i> Yol tarifi gönder</button>'
            + (s.devralindi ? '<button class="wa-btn" data-geriver="' + s.id + '"><i class="bi bi-robot"></i> Asistana geri ver</button>' : '<button class="wa-btn ikincil" data-devral="' + s.id + '"><i class="bi bi-person"></i> Devral (asistan sussun)</button>') + '</div></div>';
         if (s.yarim) h += '<div class="ts-kart ts-yarim"><div class="ts-adim bekle"><span class="ts-ik" style="background:#dc2626"><i class="bi bi-exclamation-triangle-fill"></i></span><div><b>Bu sohbet yarım kalmış</b><small>'
            + ({ sozde: 'Asistan "bakayım" deyip sonucu yazmamış.', tekrar: 'Asistan aynı cevabı tekrar etmiş; müşterinin isteği karşılanmamış.', sikayet: 'Müşteri bir şeyin olmadığını / gelmediğini yazmış; istek karşılanmamış.' }[s.yarim] || 'Müşterinin son mesajı cevapsız kalmış.') + ' Asistan birkaç dakika içinde kendiliğinden tamamlar; beklemeden hemen tamamlatabilirsiniz.</small></div>'
            + '<button class="wa-btn" data-devam="' + s.id + '"><i class="bi bi-robot"></i> Asistan tamamlasın</button></div></div>';
         if ((s.talepler || []).length) h += talepSeridi(s);
         // "Bu mesaji kim atti?": bu numaraya sistemden giden mesajlar (turuyle) + ayni numaranin onceki sohbetleri
         var sistemGiden = (s.giden || []).filter(function (x) { return !x.asistan; });
         if ((s.giden || []).length || (s.onceki || []).length) {
            h += '<details class="ng-kart"' + (sistemGiden.length ? ' open' : '') + '><summary><i class="bi bi-clock-history"></i> Bu numaraya giden mesajlar ve önceki sohbetler'
               + (sistemGiden.length ? ' <span class="wa-hap s">' + sistemGiden.length + ' sistem mesajı</span>' : '') + '</summary><div class="ng-ic">'
               + (s.giden || []).map(function (x) { return '<div class="ng-s' + (x.asistan ? ' a' : '') + '"><b>' + esc(x.tip) + '</b> <span class="wa-alt">' + tarih(x.tarih) + (x.durum === 2 ? ' · gönderilemedi' : '') + '</span><div>' + esc(x.mesaj) + '</div></div>'; }).join('')
               + (s.onceki || []).map(function (x) { return '<div class="ng-s o"><b>Önceki sohbet</b> <span class="wa-alt">' + tarih(x.tarih) + ' · ' + x.mesaj + ' mesaj</span><div>' + esc(x.ilk) + '</div><button class="wa-tumu" data-onceki="' + x.id + '">Aç <i class="bi bi-arrow-right"></i></button></div>'; }).join('')
               + '</div></details>';
         }
         if (s.gorseller.length) {
            h += '<div class="wa-gorseller">' + s.gorseller.map(function (g) {
               return '<figure>' + (g.dosya ? '<img loading="lazy" alt="Müşterinin gönderdiği model" src="' + url('/gorsel/' + g.id) + '">' : '<div class="wa-bos" style="padding:10px">Fotoğraf silindi</div>')
                  + '<figcaption>' + esc(g.tarif || '') + (g.toplam != null ? '<br><b>' + tl(g.toplam) + '</b>' : ' <span class="wa-hap g">' + esc(({ kural_yok: 'fiyat işletmeye soruldu', hata: 'incelenemedi', belirsiz: 'model soruldu', uygun_degil: 'model görünmedi' })[g.durum] || g.durum) + '</span>') + '</figcaption></figure>'; }).join('') + '</div>';
         }
         h += '<div class="wa-sohbet">' + s.satirlar.map(function (m) {
            var r = m.rol === 'musteri' ? 'Müşteri' : (m.rol === 'personel' ? (m.yazan || 'Personel') : (m.geri_kazanim ? 'Asistan · geri kazanma' : (m.hatirlatma ? 'Hatırlatma' : 'Asistan')));
            return '<div class="wa-balon ' + m.rol + '"><span class="r">' + r + '</span>' + esc(m.metin) + (m.at ? '<span class="bz">' + saatKisa(m.at) + '</span>' : '') + '</div>'; }).join('') + '</div>';
         h += '<div class="wa-alt" style="margin-top:10px">Devraldığınızda asistan bu sohbette 24 saat ya da siz geri verene kadar cevap vermez. Telefonunuzdan müşteriye yazdığınızda da asistan otomatik susar.</div>';
         el('waSohbetDetay').innerHTML = h;
      }).catch(function (e) { el('waSohbetDetay').innerHTML = '<div class="wa-bos">Sohbet gösterilemedi' + (e && e.message ? ' (' + esc(e.message) + ')' : '') + '. Sayfayı yenileyip tekrar deneyin.</div>'; });
   }
   // Isletmeye sorulan konunun sonucu: ne zaman soruldu, kime bildirildi, hatirlatma, asistanin donusu, kim cevapladi
   function saatKisa(s) { if (!s) return ''; var d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? '' : d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }) + (d.toDateString() !== new Date().toDateString() ? ' · ' + d.toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' }) : ''); }
   function talepSeridi(s) {
      // Kime bildirildi: telefonuna/tarayicisina push GERCEKTEN ulasanlar ve ulasmayanlar ayri (eski kayit: yalniz ad)
      var bildirimMetni = function (b) {
         b = b || [];
         var ulasti = b.filter(function (x) { return typeof x === 'object' && x.ulasti; }).map(function (x) { return x.ad; });
         var ulasmadi = b.filter(function (x) { return typeof x === 'object' && !x.ulasti; }).map(function (x) { return x.ad; });
         var eski = b.filter(function (x) { return typeof x !== 'object'; });
         var p = ['Panelde zil bildirimi olarak görünür.'];
         if (ulasti.length) p.unshift('<span style="color:#15803d;font-weight:700">Telefonuna ulaştı:</span> ' + esc(ulasti.join(', ')));
         if (ulasmadi.length) p.push('<span style="color:#b45309;font-weight:700">Ulaşmadı</span> (Randevumcepte uygulamasında oturum yok ya da bildirim izni kapalı): ' + esc(ulasmadi.join(', ')));
         if (eski.length) p.push('Bildirim gönderilenler: ' + esc(eski.join(', ')));
         return p.join('<br>');
      };
      return s.talepler.map(function (t) {
         var adim = function (ik, renk, baslik, alt, zaman) { return '<div class="ts-adim"><span class="ts-ik" style="background:' + renk + '"><i class="bi ' + ik + '"></i></span><div><b>' + baslik + '</b>' + (alt ? '<small>' + alt + '</small>' : '') + '</div><span class="ts-z">' + saatKisa(zaman) + '</span></div>'; };
         var h = adim('bi-send-fill', '#f59e0b', 'İşletmeye soruldu: ' + esc(t.konu_ad), bildirimMetni(t.bildirilen)
            + (t.ozet ? '<br>Müşteri: “' + esc(String(t.ozet).slice(0, 160)) + '”' : ''), t.acildi);
         if (t.hatirlatma_at) h += adim('bi-bell-fill', '#fb923c', 'Hatırlatma gönderildi', 'Henüz kimse dönmediği için yöneticilere tekrar bildirildi.', t.hatirlatma_at);
         if (t.asistan_dondu_at) h += adim('bi-robot', '#7c3aed', 'Asistan müşteriye döndü', 'Müşteri bekletilmesin diye nazikçe bilgi verdi ve randevuya devam etti.', t.asistan_dondu_at);
         if (t.durum === 'cevaplandi') h += adim('bi-check-circle-fill', '#16a34a', 'İşletme cevapladı', esc(t.cevaplayan || '') + ' · ' + esc(t.sure) + ' içinde', t.cevaplandi_at);
         else if (t.durum === 'cevapsiz') h += adim('bi-x-circle-fill', '#dc2626', 'İşletme dönmedi', '24 saat içinde cevap verilmedi.', null);
         else h += '<div class="ts-adim bekle"><span class="ts-ik" style="background:#dc2626"><i class="bi bi-hourglass-split"></i></span><div><b>' + esc(t.sure) + 'dır dönüş bekliyor</b><small>Müşteriye WhatsApp\'tan yazın; telefonunuzdan yazınca burası kendiliğinden "cevaplandı" olur. Asistan bu arada müşteriyle ilgilenmeye devam ediyor.</small></div><button class="wa-btn" data-cevapladim="' + s.id + '"><i class="bi bi-check2"></i> Cevapladım</button></div>';
         return '<div class="ts-kart ' + t.durum + '">' + h + '</div>';
      }).join('');
   }
   el('waSohbetDetay').addEventListener('click', function (e) {
      var on = e.target.closest('[data-onceki]'); if (on) { sohbetAc(on.dataset.onceki); return; }
      var yl = e.target.closest('[data-yol]');
      if (yl) {
         if (!confirm('Salonun adresi ve Google Haritalar bağlantısı bu müşteriye WhatsApp\'tan gönderilsin mi?')) return;
         yl.disabled = true;
         gonder('/sohbet/' + yl.dataset.yol + '/yol-tarifi').then(function (r) { toast(r.ok ? 'Yol tarifi gönderildi.' : (r.hata || 'Gönderilemedi.')); sohbetAc(yl.dataset.yol); })
            .catch(function () { yl.disabled = false; hata(); });
         return;
      }
      var dv = e.target.closest('[data-devam]');
      if (dv) {
         dv.disabled = true; dv.innerHTML = '<i class="bi bi-hourglass-split"></i> Asistan yazıyor…';
         gonder('/sohbet/' + dv.dataset.devam + '/devam').then(function (r) { toast(r.ok ? 'Asistan sohbeti tamamladı ve müşteriye yazdı.' : (r.hata || 'Yapılamadı.')); sohbetAc(dv.dataset.devam); sohbetListe(); })
            .catch(function () { dv.disabled = false; hata(); });
         return;
      }
      var c = e.target.closest('[data-cevapladim]');
      if (c) { c.disabled = true; gonder('/sohbet/' + c.dataset.cevapladim + '/cevaplandi').then(function (r) { toast(r.ok ? 'İşaretlendi: işletme cevapladı.' : 'İşlem yapılamadı.'); sohbetAc(c.dataset.cevapladim); sohbetListe(); }).catch(hata); return; }
      var b = e.target.closest('[data-devral],[data-geriver]'); if (!b) return;
      var id = b.dataset.devral || b.dataset.geriver; b.disabled = true;
      gonder('/sohbet/' + id + (b.dataset.devral ? '/devral' : '/geri-ver')).then(function (r) {
         toast(r.ok ? (b.dataset.devral ? 'Sohbeti devraldınız; asistan bu sohbette susacak.' : 'Sohbet asistana geri verildi.') : 'İşlem yapılamadı.');
         sohbetAc(id); sohbetListe();
      }).catch(hata);
   });

   // ---------- GERI KAZANMA
   var GK_DURUM = { gonderildi: ['Yazıldı', 'm'], cevap: ['Cevap verdi', 'm'], randevu: ['Randevu aldı', 'y'], geldi: ['Salona geldi', 'y'], gelmedi: ['Gelmedi', 'k'], ret: ['İstemedi', 'k'], gonderilemedi: ['Gönderilemedi', 'k'] };
   var GK_NEDEN = { fiyat: 'Fiyat aldı', pahali: 'Fiyatı yüksek buldu', dusunme: 'Düşünecekti', gorsel: 'Fotoğrafa fiyat aldı', saat: 'Saatlere baktı', bilgi: 'Bilgi aldı' };
   function gkYukle() {
      var kc = sonOzet ? sonOzet.kacan : [];
      el('waKacan').innerHTML = kc.length ? '<table class="wa-tablo"><thead><tr><th>Müşteri</th><th>Hizmet</th><th>Durum</th><th>Son mesaj</th></tr></thead><tbody>'
         + kc.map(function (x) { return '<tr><td>' + esc(x.ad || 'Müşteri') + '<div class="wa-alt">' + esc(x.telefon || 'gizli numara') + '</div></td><td>' + esc(x.hizmet || '—') + '</td><td>' + esc(x.neden) + (x.ret ? ' <span class="wa-hap k">yazılmaz</span>' : '') + '</td><td>' + tarih(x.tarih) + '</td></tr>'; }).join('')
         + '</tbody></table>' : '<div class="wa-bos">Bu dönemde kaçan müşteri yok.</div>';
      el('waGkListe').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/geri-kazanim', { donem: donemSec.value }).then(function (r) {
         if (!r.ok || !r.liste.length) { el('waGkListe').innerHTML = '<div class="wa-bos">Bu dönemde geri kazanma mesajı gönderilmedi.</div>'; return; }
         el('waGkListe').innerHTML = '<table class="wa-tablo"><thead><tr><th>Müşteri</th><th>Neden</th><th>Durum</th><th>Gönderildi</th><th>Randevu</th><th>Tutar</th></tr></thead><tbody>'
            + r.liste.map(function (x) { var d = GK_DURUM[x.durum] || [x.durum, 'g'];
               return '<tr title="' + esc(x.mesaj || '') + '"><td>' + esc(x.ad || 'Müşteri') + '<div class="wa-alt">' + esc(x.hizmet_ad || '') + '</div></td><td>' + esc(GK_NEDEN[x.neden] || x.neden || '') + '</td><td><span class="wa-hap ' + d[1] + '">' + d[0] + '</span></td><td>' + tarih(x.gonderildi_at) + '</td><td>' + esc(x.randevu_tarih || '—') + '</td><td>' + (x.tutar ? tl(x.tutar) : '—') + '</td></tr>'; }).join('')
            + '</tbody></table>';
      }).catch(hata);
   }

   // ---------- FOTOGRAF FIYATLARI (ayar sayfasi: bir kez kurulur)
   // Ana islem: kural yoksa hizmet listesinden OTOMATIK eslesme (WaGorselDanisman::otomatikKurallar; asistan da ayni eslesmeyi kullanir).
   // Fiyat deneme: WaGorselDanisman::fiyatHesapla + cevapMetni ile ayni hesap ("-den başlıyor" + eksik ek notu dahil).
   var sektorSec = el('waSektor'), fgDegisti = false;
   Object.keys(SEKTORLER).forEach(function (k) { sektorSec.insertAdjacentHTML('beforeend', '<option value="' + esc(k) + '">' + esc(SEKTORLER[k].ad) + '</option>'); });
   sektorSec.value = AYAR.gorsel_sektor || 'tirnak';
   el('waGorselNot').value = AYAR.gorsel_not || '';
   var FG_IKON = { tirnak: 'bi-hand-index-thumb-fill', sac: 'bi-scissors' };
   function sektorCiz() {
      el('fgSektorler').innerHTML = Object.keys(SEKTORLER).map(function (k) { return '<button type="button" data-sektor="' + esc(k) + '" class="' + (k === sektorSec.value ? 'aktif' : '') + '"><i class="bi ' + (FG_IKON[k] || 'bi-stars') + '"></i>' + esc(SEKTORLER[k].ad) + '</button>'; }).join('');
   }
   el('fgSektorler').addEventListener('click', function (e) {
      var b = e.target.closest('[data-sektor]'); if (!b || b.dataset.sektor === sektorSec.value) return;
      if (fgDegisti && !confirm('Kaydedilmemiş değişiklikleriniz var. Sektörü değiştirirseniz kaybolur. Devam edilsin mi?')) return;
      sektorSec.value = b.dataset.sektor; fgDegisti = false; sektorCiz(); kuralCiz();
   });
   function hizmetSecim(secili, sabit) {
      return '<select class="wa-girdi" data-hizmet><option value="">— eşleşme yok —</option>' + HIZMETLER.map(function (h) {
         return '<option value="' + h.id + '"' + (!sabit && String(h.id) === String(secili || '') ? ' selected' : '') + '>' + esc(h.ad) + (Number(h.fiyat) > 0 ? ' (' + tl(h.fiyat) + ')' : ' (fiyatsız)') + '</option>'; }).join('')
         + '<option value="__sabit"' + (sabit ? ' selected' : '') + '>✎ Listede yok, sabit fiyat yazacağım</option></select>';
   }
   function fiyatGirdi(deger, ph) { return '<div class="fg-tl"><input type="number" min="0" step="0.01" class="wa-girdi" data-fiyat placeholder="' + (ph || '0') + '" value="' + (deger != null ? esc(deger) : '') + '"></div>'; }
   function hizmetFiyat(id) { var h = HIZMETLER.filter(function (x) { return String(x.id) === String(id); })[0]; return h ? Number(h.fiyat) || 0 : 0; }
   // Formdaki (kaydedilmemis dahil) kurallar
   function formKurallari() {
      var kur = {};
      document.querySelectorAll('#waKuralForm [data-anahtar]').forEach(function (k) {
         var hs = k.querySelector('[data-hizmet]'), f = k.querySelector('[data-fiyat]'), sabit = hs && hs.value === '__sabit';
         var hid = hs && !sabit ? hs.value : '', fy = f && (!hs || sabit) ? f.value : '';
         if (hid || fy !== '') kur[k.dataset.anahtar] = { hizmet_id: hid || null, fiyat: fy !== '' ? fy : null };
      });
      return kur;
   }
   function kuralTutar(kur, k) {
      var r = kur[k]; if (!r) return null;
      if (r.hizmet_id && hizmetFiyat(r.hizmet_id) > 0) return hizmetFiyat(r.hizmet_id);
      return r.fiyat != null && r.fiyat !== '' ? Number(r.fiyat) : null;
   }
   function kuralCiz() {
      var sk = sektorSec.value, s = SEKTORLER[sk], k = sk === (AYAR.gorsel_sektor || 'tirnak') ? KURALLAR : {}, oto = OTO_KURALLAR[sk] || {}, h = '';
      h += '<div class="fg-bas"><i class="bi bi-1-circle-fill" style="color:#be185d"></i>Ana işlemler <small>— fiyat hizmet listenizden gelir</small></div><div class="fg-alt">Hizmet listenizden otomatik bulundu. Yanlışsa listeden değiştirin; listenizde yoksa "sabit fiyat" seçin.</div><div class="fg-ana">';
      Object.keys(s.ana).forEach(function (a) {
         var kayitli = k['ana:' + a], r = kayitli || oto['ana:' + a] || {}, sabit = !r.hizmet_id && r.fiyat != null && r.fiyat !== '';
         h += '<div class="fg-ana-k" data-anahtar="ana:' + a + '" data-oto="' + (!kayitli && oto['ana:' + a] ? 1 : 0) + '"><div class="fg-ana-ad"><span class="ik"><i class="bi ' + (FG_IKON[sk] || 'bi-stars') + '"></i></span><b>' + esc(s.ana[a]) + '</b></div>'
            + '<div class="fg-ana-sec">' + hizmetSecim(r.hizmet_id, sabit) + '<div data-alan="sabit">' + fiyatGirdi(sabit ? r.fiyat : null, 'Örn. 800') + '</div></div><span class="fg-durum"></span></div>';
      });
      h += '</div><div class="fg-bas"><i class="bi bi-2-circle-fill" style="color:#7c3aed"></i>Ek ücretler <small>— isteğe bağlı</small></div><div class="fg-alt">French, krom, taş gibi ekler çoğu salonda ayrı hizmet olmadığı için burada yazılır. Boş bırakılan ek fotoğrafta varsa asistan "…\'den başlıyor" der ve ek ücreti size sorar.</div><div class="fg-ekler">';
      // Secimli ozellik (uzunluk) kendi kartinda; var/yok ekler "Efekt ve uygulamalar", adetli ekler "Parmak başı süslemeler" kartinda
      var efekt = '', adet = '';
      Object.keys(s.ozellikler).forEach(function (o) { var oz = s.ozellikler[o], r = k[o] || {}, satir = '';
         if (oz.tip === 'secim') {
            Object.keys(oz.secenek).forEach(function (v) { var rv = k[o + ':' + v] || {};
               satir += '<div class="fg-ek-s" data-anahtar="' + o + ':' + v + '"><span>' + esc(oz.secenek[v]) + '</span>' + fiyatGirdi(rv.fiyat, '+ 0') + '</div>'; });
            h += '<div class="fg-ek-k"><h5><i class="bi bi-arrows-expand"></i>' + esc(oz.ad) + '<small>birini seçer</small></h5>' + satir + '</div>';
         } else if (oz.tip === 'bool') efekt += '<div class="fg-ek-s" data-anahtar="' + o + '"><span>' + esc(oz.ad) + '</span>' + fiyatGirdi(r.fiyat, '+ 0') + '</div>';
         else if (oz.tip === 'adet') adet += '<div class="fg-ek-s" data-anahtar="' + o + '"><span>' + esc(oz.ad.replace(/\s*\([^)]*\)/, '')) + ' <small class="wa-alt">/ ' + esc(oz.birim) + '</small></span>' + fiyatGirdi(r.fiyat, '+ 0') + '</div>';
      });
      if (efekt) h += '<div class="fg-ek-k"><h5><i class="bi bi-magic"></i>Efekt ve uygulamalar<small>varsa eklenir</small></h5>' + efekt + '</div>';
      if (adet) h += '<div class="fg-ek-k"><h5><i class="bi bi-gem"></i>Parmak başı süslemeler<small>adet × fiyat</small></h5>' + adet + '</div>';
      el('waKuralForm').innerHTML = h + '</div>';
      fgYenile(); hesapCiz();
   }
   // Satir durumlari + ust ozet
   function fgYenile() {
      var kur = formKurallari(), anaHazir = 0, anaTop = 0, otoSay = 0, ekDolu = 0, ekTop = 0;
      document.querySelectorAll('#waKuralForm .fg-ana-k').forEach(function (kk) {
         anaTop++;
         var hs = kk.querySelector('[data-hizmet]'), sabit = hs.value === '__sabit', t = kuralTutar(kur, kk.dataset.anahtar), d = kk.querySelector('.fg-durum');
         kk.querySelector('[data-alan="sabit"]').hidden = !sabit;
         kk.classList.toggle('hazir', t != null); kk.classList.toggle('eksik', t == null);
         if (t != null) { anaHazir++; if (kk.dataset.oto === '1') otoSay++; d.textContent = '✓ ' + tl(t) + (kk.dataset.oto === '1' ? ' · otomatik bulundu' : ''); }
         else d.textContent = hs.value && !sabit ? 'Bu hizmetin fiyatı yok' : 'Eşleşme yok → fiyat size sorulur';
      });
      document.querySelectorAll('#waKuralForm .fg-ek-s').forEach(function (x) { var dolu = x.querySelector('[data-fiyat]').value !== ''; x.classList.toggle('dolu', dolu); ekTop++; if (dolu) ekDolu++; });
      var g = (sonOzet && sonOzet.gorsel) || null;
      if (el('fgAyarOzet')) el('fgAyarOzet').innerHTML = (anaHazir === anaTop ? '<span style="color:#15803d;font-weight:700">✓ Hazır</span> · ' : '<span style="color:#b45309;font-weight:700">' + (anaTop - anaHazir) + ' ana işlemin fiyatı bulunamadı</span> · ')
         + anaHazir + '/' + anaTop + ' ana işlem, ' + ekDolu + ' ek ücret tanımlı. Bir kez ayarlanır; fiyatlar hizmet listenizden gelir.';
      el('fgDurum').innerHTML = '<span class="' + (anaHazir === anaTop ? 'y' : 's') + '"><i class="bi ' + (anaHazir === anaTop ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') + '"></i>' + anaHazir + '/' + anaTop + ' ana işlem hazır' + (otoSay ? ' (' + otoSay + '\'i otomatik bulundu)' : '') + '</span>'
         + '<span><i class="bi bi-plus-circle"></i>' + ekDolu + '/' + ekTop + ' ek ücret girildi (isteğe bağlı)</span>'
         + (g ? '<span><i class="bi bi-camera"></i>' + sayi(g.toplam) + ' fotoğraf · ' + sayi(g.fiyat_verildi) + ' fiyat verildi · ' + sayi(g.randevu) + ' randevu</span>' : '');
   }
   function hesapCiz() {
      var s = SEKTORLER[sektorSec.value], h = '<div class="fg-h-s"><span>Model</span><select class="wa-girdi" data-h-ana style="width:auto">'
         + Object.keys(s.ana).map(function (a) { return '<option value="' + a + '">' + esc(s.ana[a]) + '</option>'; }).join('') + '</select></div>';
      Object.keys(s.ozellikler).forEach(function (o) { var oz = s.ozellikler[o];
         if (oz.tip === 'secim') h += '<div class="fg-h-s"><span>' + esc(oz.ad) + '</span><select class="wa-girdi" data-h-oz="' + o + '" style="width:auto"><option value="">—</option>' + Object.keys(oz.secenek).map(function (v) { return '<option value="' + v + '">' + esc(oz.secenek[v]) + '</option>'; }).join('') + '</select></div>';
         else if (oz.tip === 'bool') h += '<div class="fg-h-s"><label><input type="checkbox" data-h-oz="' + o + '">' + esc(oz.ad) + '</label></div>';
         else if (oz.tip === 'adet') h += '<div class="fg-h-s"><span>' + esc(oz.ad) + '</span><input type="number" min="0" max="10" class="wa-girdi" data-h-oz="' + o + '" placeholder="0"></div>';
      });
      el('fgHesap').innerHTML = h + '<div id="fgSonuc"></div>';
      hesapla();
   }
   function hesapla() {
      var s = SEKTORLER[sektorSec.value], kur = formKurallari(), anaSec = document.querySelector('#fgHesap [data-h-ana]'); if (!anaSec) return;
      var anaT = kuralTutar(kur, 'ana:' + anaSec.value), sonuc = el('fgSonuc');
      if (anaT == null) { sonuc.innerHTML = '<div class="fg-balon uyari">"' + esc(s.ana[anaSec.value]) + '" için fiyat bulunamadı. Asistan müşteriye "fiyatı işletmemize sordum, bu arada randevunuzu ayarlayalım" der ve fiyatı size sorar.</div>'; return; }
      var kalem = [[s.ana[anaSec.value], anaT]], top = anaT, eksik = [];
      Object.keys(s.ozellikler).forEach(function (o) { var oz = s.ozellikler[o], g = document.querySelector('#fgHesap [data-h-oz="' + o + '"]'), t; if (!g) return;
         if (oz.tip === 'secim' && g.value) { t = kuralTutar(kur, o + ':' + g.value); if (t) { kalem.push([oz.ad + ' (' + oz.secenek[g.value] + ')', t]); top += t; } }
         else if (oz.tip === 'bool' && g.checked) { t = kuralTutar(kur, o); if (t) { kalem.push([oz.ad, t]); top += t; } else eksik.push(oz.ad); }
         else if (oz.tip === 'adet' && Number(g.value) > 0) { t = kuralTutar(kur, o); if (t) { kalem.push([oz.ad + ' × ' + Number(g.value), t * Number(g.value)]); top += t * Number(g.value); } else eksik.push(oz.ad.replace(/\s*\([^)]*\)/, '')); }
      });
      var liste = eksik.map(function (x, i) { return i ? x.toLocaleLowerCase('tr-TR') : x; });
      var eksikMetin = liste.length ? (liste.length > 1 ? liste.slice(0, -1).join(', ') + ' ve ' + liste[liste.length - 1] : liste[0]) + ' için ek ücret olabilir; işletmemize sordum, netleşince buradan bildireceğiz.' : '';
      var not = el('waGorselNot').value.trim();
      var msj = 'Modelinizi inceledim 😊\n\nBu model için fiyatımız: ' + tl(top) + (eksik.length ? '\'den başlıyor' : '') + (kalem.length > 1 ? '\n(' + kalem.map(function (x) { return x[0] + ' ' + tl(x[1]); }).join(' + ') + ')' : '')
         + (eksikMetin ? '\n' + eksikMetin : '') + (not ? '\n' + not : '') + '\n\nSize uygun bir gün için randevu oluşturalım mı?';
      sonuc.innerHTML = '<div class="fg-toplam"><span>Müşteriye söylenecek fiyat</span><b>' + tl(top) + (eksik.length ? '+' : '') + '</b></div><div class="fg-balon">' + esc(msj) + '</div>';
   }
   function fgIsaretle() { fgDegisti = true; el('fgKaydetBar').classList.add('degisti'); el('fgKaydetYazi').innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Kaydedilmemiş değişiklikler var'; }
   el('waKuralForm').addEventListener('input', function () { fgIsaretle(); fgYenile(); hesapla(); });
   el('waKuralForm').addEventListener('change', function (e) { var a = e.target.closest('.fg-ana-k'); if (a) a.dataset.oto = 0; fgIsaretle(); fgYenile(); hesapla(); });
   el('fgHesap').addEventListener('input', hesapla); el('fgHesap').addEventListener('change', hesapla);
   el('waGorselNot').addEventListener('input', function () { fgIsaretle(); hesapla(); });
   document.querySelectorAll('#p-gorsel [data-git], #p-ayar [data-git]').forEach(function (b) { b.addEventListener('click', function (e) { e.preventDefault(); sekmeAc(b.dataset.git); window.scrollTo({ top: 0, behavior: 'smooth' }); }); });
   sektorCiz(); kuralCiz();
   el('waKuralKaydet').addEventListener('click', function () {
      var kur = formKurallari(), b = this; b.disabled = true;
      Promise.all([gonder('/kurallar', { sektor: sektorSec.value, kurallar: kur }), gonder('/ayar', { gorsel_not: el('waGorselNot').value })]).then(function (r) {
         b.disabled = false;
         if (r[0].ok) {
            KURALLAR = r[0].kurallar; AYAR.gorsel_sektor = sektorSec.value; AYAR.gorsel_not = el('waGorselNot').value; fgDegisti = false;
            el('fgKaydetBar').classList.remove('degisti'); el('fgKaydetYazi').innerHTML = '<i class="bi bi-check-circle-fill" style="color:#16a34a"></i> Kaydedildi. Asistan yeni fiyatları hemen kullanır.';
            toast('Fotoğraf fiyat kuralları kaydedildi.'); kuralCiz();
         } else toast(r[0].hata || 'Kaydedilemedi.');
      }).catch(function () { b.disabled = false; hata(); });
   });

   // ---------- ASISTAN GELISIMI (Gece Denetcisi: inceleme, dersler, bekleyen sorular)
   var sonGelisim = null;
   function gelisimYukle() {
      al('/gelisim').then(function (r) {
         if (!r.ok) { el('waGelisim').innerHTML = '<div class="wa-bos">' + esc(r.hata || 'Yüklenemedi.') + '</div>'; return; }
         sonGelisim = r; el('waGelisim').innerHTML = r.biz ? gelisimHtml(r) : soruHtml(r);
      }).catch(function () { el('waGelisim').innerHTML = '<div class="wa-bos">Yüklenemedi.</div>'; });
   }
   // Salonun gordugu: yalniz asistanin bilmedigi icin salona sordugu bilgiler (egitim kismi sistem yoneticisinde)
   function soruHtml(r) {
      var bilgi = r.dersler || [];
      var h = '<div class="wa-kart gl-bekle"><div class="wa-kart-bas"><h4><i class="bi bi-question-circle-fill" style="color:#d97706"></i>Asistan size soruyor' + (bilgi.length ? ' <small class="wa-alt" style="font-weight:600">' + bilgi.length + '</small>' : '') + '</h4></div>'
         + '<div class="wa-alt" style="margin-bottom:6px">Müşterileriniz bu soruları sordu ama asistan cevabını bilmiyordu. Cevabı bir kez yazın, asistan bundan sonra kendisi cevaplasın.</div>';
      if (!bilgi.length) return h + '<div class="wk-bos"><b>Şu an bekleyen soru yok.</b> Müşteriler asistanın bilmediği bir şey sorarsa (otopark, ödeme yöntemi, bir uzmanın çalıştığı günler gibi) burada size sorulur.</div></div>';
      bilgi.forEach(function (d) { h += soruKart(d); });
      return h + '</div>';
   }
   // Tek soru karti: cevap tipine gore en kisa cevap yolu (fiyat kutusu / evet-hayir / hizmet secimi / kisa metin)
   function soruKart(d) {
      var f = d.form || { tip: 'metin' }, ic = '';
      var yoksay = '<button class="wa-btn ikincil" data-bilgi-yoksay>Yoksay</button>';
      if (f.tip === 'fiyat') {
         ic = '<div class="gl-fiyatlar">' + (f.kalemler || []).map(function (k) {
            if (!k.hizmet_id) return '<div class="gl-fsat yok"><span>' + esc(k.ad) + '</span><em>Hizmet listenizde yok · <a href="/isletmeyonetim/hizmet-yonetimi' + (SUBE ? '?sube=' + encodeURIComponent(SUBE) : '') + '" target="_blank">Hizmet ekle</a></em></div>';
            var deger = k.oneri_fiyat || k.mevcut || '';
            return '<label class="gl-fsat"><span>' + esc(k.ad) + (k.oneri_fiyat ? ' <i class="wa-alt">(personeliniz ' + k.oneri_fiyat + ' TL dedi)</i>' : '') + '</span>'
               + '<input class="wa-girdi gl-fiyat" data-hid="' + k.hizmet_id + '" inputmode="numeric" placeholder="0" value="' + esc(String(deger)) + '"><b>TL</b></label>';
         }).join('') + '</div><div class="sat"><button class="wa-btn" data-fiyat-kaydet>Fiyatları kaydet</button>' + yoksay + '</div>';
      } else if (f.tip === 'evet_hayir') {
         ic = '<div class="sat"><button class="wa-btn" data-eh="' + esc(f.evet) + '"><i class="bi bi-check-lg"></i> Evet</button><button class="wa-btn" data-eh="' + esc(f.hayir) + '"><i class="bi bi-x-lg"></i> Hayır</button>' + yoksay + '</div>'
            + '<small>Evet derseniz müşteriye: "' + esc(f.evet) + '" · Hayır derseniz: "' + esc(f.hayir) + '"</small>';
      } else if (f.tip === 'hizmet_sec') {
         ic = '<div class="sat"><select class="wa-girdi gl-hizmet"><option value="">Hangi hizmetiniz? Seçin…</option>' + ((sonGelisim && sonGelisim.hizmetler) || []).map(function (x) { return '<option value="' + x.id + '">' + esc(x.ad) + '</option>'; }).join('')
            + '</select><button class="wa-btn" data-hizmet-kaydet>Kaydet</button>' + yoksay + '</div>';
      } else {
         ic = '<div class="sat"><textarea class="wa-girdi" rows="2" maxlength="1000" placeholder="Kısa cevabınız (müşteriye aynen gider)"></textarea><button class="wa-btn" data-bilgi-kaydet>Kaydet</button>' + yoksay + '</div>';
      }
      var baslik = f.tip === 'hizmet_sec' ? 'Müşteriler <q>' + esc(f.ifade) + '</q> diyor. Bu hangi hizmetiniz?' : esc(d.metin);
      return '<div class="gl-soru" data-bilgi-id="' + d.id + '"><b>' + baslik + '</b>' + (d.gerekce ? '<small>' + esc(d.gerekce) + '</small>' : '') + ic + '</div>';
   }
   function puanSinif(p) { return p >= 85 ? 'i' : (p >= 70 ? 'o' : 'k'); }
   function fark(simdi, once, tersIyi) {
      if (simdi == null || once == null) return '';
      var d = Math.round((simdi - once) * 10) / 10; if (!d) return '';
      var iyi = tersIyi ? d < 0 : d > 0;
      return '<em class="' + (iyi ? '' : 'k') + '">' + (d > 0 ? '▲ ' : '▼ ') + String(Math.abs(d)).replace('.', ',') + '</em>';
   }
   function gelisimHtml(r) {
      var h = '', dersler = r.dersler || [], aktif = dersler.filter(function (d) { return d.tur === 'davranis' && d.durum === 'aktif'; });
      var bilgi = dersler.filter(function (d) { return d.tur === 'bilgi' && d.durum === 'oneri'; });
      var onay = dersler.filter(function (d) { return d.tur === 'davranis' && d.durum === 'oneri'; });
      var diger = dersler.filter(function (d) { return d.tur === 'davranis' && (d.durum === 'kapali' || d.durum === 'reddedildi'); });
      var kod = dersler.filter(function (d) { return d.tur === 'kod' && d.durum === 'oneri'; });
      // 1) Hero
      h += '<div class="gl-hero"><div class="gl-halka">' + halka(r.puan || 0, '#5eead4', r.puan != null ? Math.round(r.puan) : '–', 'kalite puanı').replace('stroke="#f1edf8"', 'stroke="rgba(255,255,255,.12)"') + '</div>'
         + '<div><span class="gl-rozet"><i class="bi bi-moon-stars-fill"></i>Gece Denetçisi · ' + (r.acik ? (r.oto ? 'Kendini geliştiriyor' : 'Önerileri onayınıza sunuyor') : 'Kapalı') + '</span>'
         + '<h3>Asistan her gece sohbetlerini inceleyip<br>kendini geliştiriyor.</h3>'
         + '<p>Sorunlu cevapları bulur, "doğrusu buydu" cevabını yazar ve dersi geçmiş sohbetlerde yeniden deneyip <b>daha iyi sonuç verirse</b> uygular. Fiyatlarınız ve kurallarınız kendiliğinden değişmez.</p>'
         + '<div class="gl-tile"><div><b>' + sayi(r.incelenen) + '</b><span>sohbet incelendi (7 gün)</span></div>'
         + '<div><b>' + (r.randevu_mesaj != null ? String(r.randevu_mesaj).replace('.', ',') : '–') + fark(r.randevu_mesaj, r.randevu_mesaj_once, true) + '</b><span>mesajda randevu (ortalama)</span></div>'
         + '<div><b>' + aktif.length + '</b><span>ders uygulanıyor</span></div>'
         + '<div><b>' + sayi(r.sorunlu) + '</b><span>sorunlu sohbet (puan &lt; 70)</span></div></div></div>'
         + '<div><button class="wa-btn" id="glBaslat"><i class="bi bi-play-circle-fill"></i> Şimdi incele</button><span class="gl-not">' + (r.bekleyen_is ? sayi(r.bekleyen_is) + ' sohbet incelemede; sonuç genellikle 1 saat içinde gelir.' : 'Otomatik inceleme her gece 03:00\'te.') + '</span></div></div>';
      if (!r.acik) h += '<div class="wa-bilgi">Gece denetçisi kapalı. Açmak için <a href="#" data-git="ayar">Ayarlar</a> sekmesinden "Gece denetçisi sohbetleri incelesin" seçeneğini açın.</div>';
      if (!r.incelenen && !dersler.length) {
         h += '<div class="wa-kart"><div class="wk-bos"><b>Henüz incelenen sohbet yok.</b> Denetçi her gece 03:00\'te, son 3 günde biten sohbetleri inceler. Hemen başlatmak için yukarıdaki <b>Şimdi incele</b> düğmesine basın; sonuçlar genellikle 1 saat içinde burada görünür. (En az bir müşteri mesajı ve bir cevap olan sohbetler incelenir.)</div></div>';
         return h;
      }
      if (r.ifade) {
         var IK = { kutuphane: ['Merkezi kütüphane', '#0d9488'], salon: ['Salonun öğrettiği', '#7c3aed'], tam_ad: ['Hizmetin tam adı', '#2563eb'], kelime: ['Eski kelime eşleştirme', '#64748b'], benzerlik: ['Ses/benzerlik düzeltmesi', '#d97706'], yok: ['Bulunamadı (yapay zekâ seçti)', '#dc2626'] };
         var ikSatir = function (o) {
            var t = o.toplam || 0;
            if (!t) return '<div class="wa-alt">Henüz kayıt yok.</div>';
            return Object.keys(IK).filter(function (k) { return o.katman[k]; }).map(function (k) {
               var y = Math.round(o.katman[k] / t * 100);
               return '<div class="gl-tur"><span>' + IK[k][0] + '</span><div class="bar"><i style="width:' + y + '%;background:' + IK[k][1] + '"></i></div><b>%' + y + '</b></div>';
            }).join('') + '<div class="wa-alt" style="margin-top:6px">' + sayi(t) + ' hizmet arama · bedava çözülen %' + Math.round((t - (o.katman.yok || 0)) / t * 100) + '</div>';
         };
         h += '<div class="gl-iki" style="margin-bottom:18px"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-translate" style="color:#0d9488"></i>Hizmet anlama · bu salon (30 gün)</h4><span class="wa-alt">öğrenilen ifade: ' + sayi(r.ifade.salon.ogrenilen || 0) + '</span></div>' + ikSatir(r.ifade.salon) + '</div>'
            + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-globe2" style="color:#0d9488"></i>Hizmet anlama · tüm salonlar (30 gün)</h4><span class="wa-alt">ortak kütüphaneye giren: ' + sayi(r.ifade.tum.ortak || 0) + '</span></div>' + ikSatir(r.ifade.tum) + '</div></div>';
      }
      if (r.rapor && r.rapor.metin) {
         h += '<div class="wa-kart gl-rapor" style="margin-bottom:18px"><div class="wa-kart-bas"><h4><i class="bi bi-chat-square-text-fill" style="color:#0d9488"></i>Denetçinin raporu</h4><span class="wa-alt">' + tarih(r.rapor.created_at) + '</span></div>'
            + '<div class="gl-rapor-metin">' + esc(r.rapor.metin) + '</div></div>';
      }
      // 2) Sizden beklenenler
      if (bilgi.length || onay.length) {
         h += '<div class="wa-kart gl-bekle" style="margin-bottom:18px"><div class="wa-kart-bas"><h4><i class="bi bi-question-circle-fill" style="color:#d97706"></i>Sizden beklenenler <small class="wa-alt" style="font-weight:600">' + (bilgi.length + onay.length) + '</small></h4></div>';
         bilgi.forEach(function (d) { h += soruKart(d); });
         onay.forEach(function (d) {
            h += '<div class="gl-soru"><b>Önerilen ders: ' + esc(d.metin) + '</b><small>' + esc((d.test && d.test.not) || d.gerekce || '') + '</small><div class="sat"><button class="wa-btn" data-ders="' + d.id + '" data-durum="aktif">Uygula</button><button class="wa-btn ikincil" data-ders="' + d.id + '" data-durum="kapali">Uygulama</button></div></div>';
         });
         h += '</div>';
      }
      // 3) Puan grafigi + sorun turleri
      var g = r.gunluk || [];
      h += '<div class="gl-iki"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-activity" style="color:#14b8a6"></i>Kalite puanı (son 14 gün)</h4>' + (r.puan_once != null && r.puan != null ? '<span class="wa-alt">önceki hafta ' + String(r.puan_once).replace('.', ',') + ' → bu hafta <b>' + String(r.puan).replace('.', ',') + '</b></span>' : '') + '</div>'
         + '<div class="gl-grafik">' + g.map(function (x) { return '<div class="c' + (x.puan == null ? ' bos' : (x.puan < 70 ? ' dusuk' : '')) + '" title="' + esc(x.gun) + ' · ' + (x.puan == null ? 'inceleme yok' : 'puan ' + x.puan + ' · ' + x.adet + ' sohbet') + '">' + (x.puan != null ? '<em>' + x.puan + '</em>' : '') + '<i style="height:' + (x.puan == null ? 3 : Math.max(4, x.puan)) + '%"></i></div>'; }).join('') + '</div>'
         + '<div class="gl-x">' + g.map(function (x, i) { return '<span>' + (i % 2 === 0 ? (+x.gun.slice(8)) + ' ' + AYLAR[+x.gun.slice(5, 7) - 1] : '') + '</span>'; }).join('') + '</div></div>';
      var tmx = Math.max.apply(null, (r.turlar || []).map(function (t) { return t.adet; }).concat([1]));
      h += '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-bug-fill" style="color:#f97316"></i>En sık görülen sorunlar</h4></div>'
         + ((r.turlar || []).length ? r.turlar.slice(0, 7).map(function (t) { return '<div class="gl-tur"><span>' + esc(t.ad) + '</span><div class="bar"><i style="width:' + Math.round(t.adet / tmx * 100) + '%"></i></div><b>' + t.adet + '</b></div>'; }).join('')
            : '<div class="wk-bos"><b>Bu hafta sorun bulunmadı.</b> Asistanın cevapları denetçiden tam not aldı.</div>') + '</div></div>';
      // 4) Asistanin ogrendikleri
      var dersSatir = function (d) {
         var t = d.test || {}, k = t.kanit || null, etk = '';
         if (d.durum === 'aktif') etk += '<span class="gl-etk y"><i class="bi bi-check-circle-fill"></i>Uygulanıyor</span>';
         if (d.durum === 'reddedildi') etk += '<span class="gl-etk k"><i class="bi bi-x-circle-fill"></i>Testi geçemedi, uygulanmadı</span>';
         if (d.durum === 'kapali') etk += '<span class="gl-etk g">Kapatıldı</span>';
         if (k && (k.yeni + k.eski + k.esit)) etk += '<span class="gl-etk s"><i class="bi bi-clipboard2-check-fill"></i>Testte ' + k.yeni + '/' + (k.yeni + k.eski + k.esit) + ' sorunlu sohbette daha iyi</span>';
         return '<div class="gl-ders ' + (d.durum === 'aktif' ? 'aktif' : '') + '"><span class="ik"><i class="bi ' + (d.durum === 'aktif' ? 'bi-patch-check-fill' : 'bi-lightbulb-fill') + '"></i></span><div class="ic"><b>' + esc(d.metin) + '</b>'
            + (d.gerekce ? '<small>' + esc(d.gerekce) + '</small>' : '') + etk + '<small>' + tarih(d.created_at) + '</small></div>'
            + '<div class="islem">' + (d.durum === 'aktif' ? '<button data-ders="' + d.id + '" data-durum="kapali">Geri al</button>' : (d.durum === 'kapali' || d.durum === 'reddedildi' ? '<button class="ana" data-ders="' + d.id + '" data-durum="aktif">Yine de uygula</button>' : '')) + '</div></div>';
      };
      h += '<div class="wa-kart" style="margin-bottom:18px"><div class="wa-kart-bas"><h4><i class="bi bi-mortarboard-fill" style="color:#0d9488"></i>Ortak beyin dersleri <small class="wa-alt" style="font-weight:600">tüm salonlarda geçerli</small></h4><span class="wa-alt">' + aktif.length + ' ders uygulanıyor</span></div>'
         + (aktif.length || diger.length ? aktif.concat(diger).map(dersSatir).join('') : '<div class="wk-bos">Henüz ders yok. Denetçi tekrarlayan bir sorun gördüğünde ders önerir, test eder ve burada gösterir.</div>') + '</div>';
      // 5) Son incelenen sohbetler
      h += '<div class="wa-kart" style="margin-bottom:18px"><div class="wa-kart-bas"><h4><i class="bi bi-chat-quote-fill" style="color:#7c3aed"></i>Son incelenen sohbetler</h4><span class="wa-alt">tıklayınca sorunlar ve doğrusu açılır</span></div>'
         + ((r.son || []).length ? r.son.map(function (s) {
            return '<div class="gl-sohbet"><div class="ust" data-gl-ac><span class="gl-puan ' + puanSinif(s.puan) + '">' + s.puan + '</span><div class="ic"><b>' + esc(s.ad || 'Müşteri') + '</b> <span class="wa-alt">' + tarih(s.tarih) + '</span><div>' + esc(s.ozet || '') + '</div></div>'
               + (s.sorunlar.length ? '<span class="gl-etk s">' + s.sorunlar.length + ' sorun</span>' : '<span class="gl-etk y">Sorunsuz</span>') + '<i class="bi bi-chevron-down"></i></div>'
               + '<div class="detay">' + s.sorunlar.map(function (x) { return '<div class="gl-sorun"><b>' + esc(x.tur) + '</b>' + esc(x.aciklama) + (x.dogrusu ? '<div class="dogru">' + esc(x.dogrusu) + '</div>' : '') + '</div>'; }).join('')
               + '<button class="wa-tumu" data-gl-sohbet="' + s.oturum_id + '">Sohbeti aç <i class="bi bi-arrow-right"></i></button></div></div>';
         }).join('') : '<div class="wk-bos">Bu hafta incelenen sohbet yok.</div>') + '</div>';
      if (kod.length) {
         h += '<div class="wa-kart gl-kod"><details><summary><i class="bi bi-bug-fill"></i> Geliştirici notları (' + kod.length + ')</summary><div style="margin-top:10px">'
            + kod.map(function (d) { return '<div class="gl-sorun"><b>' + esc(d.metin) + '</b>' + esc(d.gerekce || '') + '<div style="margin-top:6px"><button class="wa-btn ikincil" style="padding:5px 10px;font-size:12px" data-bilgi-id-kapat="' + d.id + '">Okundu</button></div></div>'; }).join('')
            + '<div class="wa-alt">Bu notlar sistemsel sorunlardır; destek ekibimiz de görür.</div></div></details></div>';
      }
      return h;
   }
   el('waGelisim').addEventListener('click', function (e) {
      var t = e.target;
      if (t.closest('#glBaslat')) {
         var b = t.closest('#glBaslat'); b.disabled = true;
         gonder('/denetim-baslat', {}).then(function (r) { b.disabled = false; toast(r.ok ? (r.adet ? r.adet + ' sohbet incelemeye gönderildi. Sonuçlar genellikle 1 saat içinde gelir.' : 'İncelenecek yeni sohbet yok.') : (r.hata || 'Başlatılamadı.')); if (r.ok) gelisimYukle(); }).catch(function () { b.disabled = false; hata(); });
         return;
      }
      var d = t.closest('[data-ders]');
      if (d) { gonder('/ders/' + d.dataset.ders + '/durum', { durum: d.dataset.durum }).then(function (r) { toast(r.ok ? (d.dataset.durum === 'aktif' ? 'Ders uygulanıyor.' : 'Ders kapatıldı.') : (r.hata || 'Kaydedilemedi.')); gelisimYukle(); }).catch(hata); return; }
      var bk = t.closest('[data-bilgi-kaydet]'), by = t.closest('[data-bilgi-yoksay]'), kk = t.closest('[data-bilgi-id-kapat]');
      if (bk || by) {
         var s = t.closest('[data-bilgi-id]'), cevap = by ? '' : s.querySelector('textarea').value.trim();
         if (bk && !cevap) { toast('Önce cevabınızı yazın.'); return; }
         gonder('/ders/' + s.dataset.bilgiId + '/cevap', { cevap: cevap }).then(function (r) { toast(r.ok ? (cevap ? 'Kaydedildi. Asistan bu soruyu artık kendisi cevaplar.' : 'Yoksayıldı.') : 'Kaydedilemedi.'); gelisimYukle(); }).catch(hata);
         return;
      }
      if (kk) { gonder('/ders/' + kk.dataset.bilgiIdKapat + '/cevap', { cevap: '' }).then(function () { gelisimYukle(); }).catch(hata); return; }
      var fk = t.closest('[data-fiyat-kaydet]'), eh = t.closest('[data-eh]'), hk = t.closest('[data-hizmet-kaydet]');
      if (fk || eh || hk) {
         var kart = t.closest('[data-bilgi-id]'), veri;
         if (fk) {
            veri = { tip: 'fiyat', fiyatlar: [].map.call(kart.querySelectorAll('.gl-fiyat'), function (i) { return { hizmet_id: i.dataset.hid, fiyat: i.value.replace(/[^\d,.]/g, '') }; }).filter(function (x) { return x.fiyat; }) };
            if (!veri.fiyatlar.length) { toast('En az bir fiyat yazın.'); return; }
         } else if (eh) {
            veri = { cevap: eh.dataset.eh };
         } else {
            var sec = kart.querySelector('.gl-hizmet').value;
            if (!sec) { toast('Önce hizmeti seçin.'); return; }
            veri = { tip: 'hizmet_sec', hizmet_id: sec };
         }
         gonder('/ders/' + kart.dataset.bilgiId + '/cevap', veri).then(function (r) { toast(r.ok ? (r.mesaj || 'Kaydedildi. Asistan bunu artık kendisi cevaplar.') : (r.hata || 'Kaydedilemedi.')); if (r.ok) gelisimYukle(); }).catch(hata);
         return;
      }
      var sh = t.closest('[data-gl-sohbet]');
      if (sh) { sekmeAc('sohbet'); sohbetAc(sh.dataset.glSohbet); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
      var g = t.closest('[data-git]'); if (g) { e.preventDefault(); sekmeAc(g.dataset.git); return; }
      var ac = t.closest('[data-gl-ac]'); if (ac) ac.parentNode.classList.toggle('acik');
   });

   // ---------- OGRENILEN
   function ogrYukle() {
      al('/ogrenilen').then(function (r) {
         if (!r.ok || !r.liste.length) { el('waOgrListe').innerHTML = '<div class="wa-bos">Henüz öğrenilmiş cevap yok. Asistan genel sorulara cevap verdikçe burası dolacak.</div>'; return; }
         el('waOgrListe').innerHTML = '<table class="wa-tablo"><thead><tr><th>Soru</th><th>Cevap</th><th>Ücretsiz kullanım</th><th></th></tr></thead><tbody>'
            + r.liste.map(function (x) { return '<tr><td>' + esc(x.soru) + '</td><td style="white-space:pre-wrap">' + esc(x.cevap) + '</td><td>' + sayi(x.adet) + '</td><td><button class="wa-btn ikincil" data-sil="' + x.id + '">Sil</button></td></tr>'; }).join('')
            + '</tbody></table>';
      }).catch(hata);
   }
   el('waOgrListe').addEventListener('click', function (e) {
      var b = e.target.closest('[data-sil]'); if (!b) return;
      if (!confirm('Bu öğrenilmiş cevap silinsin mi? Asistan bu soruya bir dahaki sefere yeniden cevap üretir.')) return;
      gonder('/ogrenilen/' + b.dataset.sil + '/sil').then(function (r) { toast(r.ok ? 'Silindi.' : 'Silinemedi.'); ogrYukle(); }).catch(hata);
   });

   // ---------- REKLAMLARIM (salonun kendi kampanyalari; asistan ilgili hizmette sohbette bir kez gonderir)
   var REKLAMLAR = [];
   function rkTarih(s) { if (!s) return ''; var p = String(s).slice(0, 10).split('-'); return p.length === 3 ? p[2] + '.' + p[1] + '.' + p[0] : esc(s); }
   function reklamYukle() {
      al('/reklamlar').then(function (r) {
         if (!r.ok) { el('rkListe').innerHTML = '<div class="wa-bos">' + esc(r.hata || 'Yüklenemedi.') + '</div>'; return; }
         el('rkKapali').style.display = r.acik ? 'none' : 'block';
         REKLAMLAR = r.liste || []; reklamCiz();
      }).catch(hata);
   }
   function reklamCiz() {
      if (!REKLAMLAR.length) { el('rkListe').innerHTML = '<div class="wa-bos">Henüz kampanya yok. "Yeni kampanya" ile ilkini ekleyin.</div>'; return; }
      var adlar = {}; HIZMETLER.forEach(function (h) { adlar[h.id] = h.ad; });
      el('rkListe').innerHTML = REKLAMLAR.map(function (x) {
         var bugun = new Date().toISOString().slice(0, 10), bitti = x.bitis && String(x.bitis).slice(0, 10) < bugun;
         var durum = !x.aktif ? '<span class="wa-hap g">Kapalı</span>' : (bitti ? '<span class="wa-hap k">Süresi bitti</span>' : '<span class="wa-hap y">Yayında</span>');
         return '<div class="rk-satir"><div class="rk-afis"' + (x.gorsel ? ' style="background-image:url(\'' + esc(x.gorsel) + '\')"' : '') + '>' + (x.gorsel ? '' : '<i class="bi bi-image"></i>') + '</div>'
            + '<div class="rk-ic"><b>' + esc(x.baslik) + '</b> ' + durum
            + '<div class="wa-alt">' + (x.hizmetler || []).map(function (i) { return esc(adlar[i] || ('#' + i)); }).join(', ')
            + (x.bitis ? ' · son gün ' + rkTarih(x.bitis) : '') + '</div>'
            + '<div class="rk-say"><span><b>' + sayi(x.gosterim) + '</b> kişiye gönderildi</span><span><b>' + sayi(x.tiklama) + '</b> açtı</span><span><b>' + sayi(x.randevu) + '</b> randevu</span><span class="wa-alt">(son 30 gün)</span></div>'
            + '<div class="rk-btnlar"><button class="wa-btn ikincil" data-rk-duzenle="' + x.id + '"><i class="bi bi-pencil"></i> Düzenle</button>'
            + '<button class="wa-btn ikincil" data-rk-ac="' + x.id + '">' + (x.aktif ? '<i class="bi bi-pause"></i> Durdur' : '<i class="bi bi-play"></i> Yayına al') + '</button>'
            + '<a class="wa-btn ikincil" href="' + esc(x.link) + '" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Sayfayı gör</a>'
            + '<button class="wa-btn ikincil" data-rk-sil="' + x.id + '" style="color:#b91c1c"><i class="bi bi-trash"></i></button></div></div></div>';
      }).join('');
   }
   function reklamForm(x) {
      x = x || { aktif: 1, hizmetler: [] };
      var f = el('rkForm'); f.style.display = 'block';
      f.innerHTML = '<div class="wa-kart-bas"><h4><i class="bi bi-pencil-square" style="color:#7c3aed"></i>' + (x.id ? 'Kampanyayı düzenle' : 'Yeni kampanya') + '</h4></div>'
         + '<label class="wa-alt">Başlık</label><input class="wa-girdi" id="rkBaslik" maxlength="120" placeholder="Örn. Kasım\'a özel protez tırnak + manikür 950 TL" value="' + esc(x.baslik || '') + '">'
         + '<label class="wa-alt" style="display:block;margin-top:10px">Açıklama (müşteriye giden metin)</label><textarea class="wa-girdi" id="rkMetin" rows="3" maxlength="700" placeholder="Örn. 1.200 TL yerine 950 TL. Ay sonuna kadar geçerli.">' + esc(x.metin || '') + '</textarea>'
         + '<label class="wa-alt" style="display:block;margin-top:10px">Hangi hizmetler sorulunca gönderilsin? <b id="rkSecSay"></b></label>'
         + '<input class="wa-girdi" id="rkHizAra" placeholder="🔍 Hizmet ara (ör. manikür, protez)" autocomplete="off" style="margin-bottom:6px">'
         + '<div class="rk-hizmetler" id="rkHizListe">'
         // secili hizmetler en ustte; arama ad uzerinden (Turkce buyuk/kucuk harf duyarsiz)
         + HIZMETLER.slice().sort(function (a, b) { var sa = (x.hizmetler || []).indexOf(Number(a.id)) >= 0, sb = (x.hizmetler || []).indexOf(Number(b.id)) >= 0; return sa === sb ? 0 : (sa ? -1 : 1); })
            .map(function (h) { return '<label data-ad="' + esc(String(h.ad).toLocaleLowerCase('tr-TR')) + '"><input type="checkbox" value="' + h.id + '"' + ((x.hizmetler || []).indexOf(Number(h.id)) >= 0 ? ' checked' : '') + '> ' + esc(h.ad) + '</label>'; }).join('')
         + '<div class="wa-alt" id="rkHizYok" style="display:none;padding:6px 0">Bu adla hizmet bulunamadı.</div></div>'
         + '<div class="wa-satir" style="margin-top:10px;gap:10px"><div><label class="wa-alt">Başlangıç</label><input type="date" class="wa-girdi" id="rkBas" value="' + esc(String(x.baslangic || '').slice(0, 10)) + '"></div>'
         + '<div><label class="wa-alt">Bitiş</label><input type="date" class="wa-girdi" id="rkBit" value="' + esc(String(x.bitis || '').slice(0, 10)) + '"></div></div>'
         + '<label class="wa-alt" style="display:block;margin-top:10px">Afiş görseli (JPG/PNG, en fazla 4 MB)</label><input type="file" id="rkGorsel" accept="image/jpeg,image/png,image/webp">'
         + (x.gorsel ? '<img class="rk-on" id="rkOn" src="' + esc(x.gorsel) + '" alt="">' : '<img class="rk-on" id="rkOn" alt="" style="display:none">')
         + '<label style="display:flex;gap:8px;align-items:center;margin-top:12px;font-size:14px"><input type="checkbox" id="rkAktif"' + (x.aktif ? ' checked' : '') + '> Yayında</label>'
         + '<div style="display:flex;gap:8px;margin-top:14px"><button class="wa-btn" id="rkKaydet"><i class="bi bi-check2"></i> Kaydet</button><button class="wa-btn ikincil" id="rkVazgec">Vazgeç</button></div>';
      var secSay = function () { var n = f.querySelectorAll('#rkHizListe input:checked').length; el('rkSecSay').textContent = n ? '(' + n + ' seçili)' : ''; };
      secSay();
      el('rkHizListe').addEventListener('change', secSay);
      el('rkHizAra').addEventListener('input', function () {
         var q = this.value.trim().toLocaleLowerCase('tr-TR'), gorunen = 0;
         f.querySelectorAll('#rkHizListe label').forEach(function (l) {
            var uy = !q || l.dataset.ad.indexOf(q) >= 0 || l.querySelector('input').checked;
            l.style.display = uy ? '' : 'none'; if (uy) gorunen++;
         });
         el('rkHizYok').style.display = gorunen ? 'none' : 'block';
      });
      el('rkHizAra').addEventListener('keydown', function (e) {
         // Enter: aramada tek hizmet kaldiysa onu isaretle
         if (e.key !== 'Enter') return; e.preventDefault();
         var acik = Array.prototype.filter.call(f.querySelectorAll('#rkHizListe label'), function (l) { return l.style.display !== 'none' && !l.querySelector('input').checked; });
         if (acik.length === 1) { acik[0].querySelector('input').checked = true; secSay(); this.value = ''; this.dispatchEvent(new Event('input')); }
      });
      el('rkGorsel').addEventListener('change', function () { var d = this.files && this.files[0]; if (d) { var o = el('rkOn'); o.src = URL.createObjectURL(d); o.style.display = 'block'; } });
      el('rkVazgec').addEventListener('click', function () { f.style.display = 'none'; });
      el('rkKaydet').addEventListener('click', function () {
         var b = this, fd = new FormData();
         if (x.id) fd.append('id', x.id);
         fd.append('baslik', el('rkBaslik').value); fd.append('metin', el('rkMetin').value);
         fd.append('baslangic', el('rkBas').value); fd.append('bitis', el('rkBit').value); fd.append('aktif', el('rkAktif').checked ? 1 : 0);
         f.querySelectorAll('.rk-hizmetler input:checked').forEach(function (c) { fd.append('hizmetler[]', c.value); });
         var g = el('rkGorsel').files[0]; if (g) fd.append('gorsel', g);
         b.disabled = true;
         fetch(url('/reklam'), { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: fd }).then(yanit).then(function (r) {
            b.disabled = false;
            if (!r.ok) { toast(r.hata || 'Kaydedilemedi.'); return; }
            REKLAMLAR = r.liste || []; reklamCiz(); f.style.display = 'none'; toast('Kampanya kaydedildi.');
         }).catch(function () { b.disabled = false; hata(); });
      });
      f.scrollIntoView({ behavior: 'smooth', block: 'start' });
   }
   el('rkYeni').addEventListener('click', function () { reklamForm(null); });
   el('rkListe').addEventListener('click', function (e) {
      var d = e.target.closest('[data-rk-duzenle]'), a = e.target.closest('[data-rk-ac]'), s = e.target.closest('[data-rk-sil]');
      var bul = function (id) { return REKLAMLAR.filter(function (x) { return String(x.id) === String(id); })[0]; };
      if (d) reklamForm(bul(d.dataset.rkDuzenle));
      if (a) {
         var x = bul(a.dataset.rkAc), fd = new FormData();
         fd.append('id', x.id); fd.append('baslik', x.baslik); fd.append('metin', x.metin || ''); fd.append('baslangic', String(x.baslangic || '').slice(0, 10));
         fd.append('bitis', String(x.bitis || '').slice(0, 10)); fd.append('aktif', x.aktif ? 0 : 1); (x.hizmetler || []).forEach(function (h) { fd.append('hizmetler[]', h); });
         fetch(url('/reklam'), { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: fd }).then(yanit)
            .then(function (r) { if (r.ok) { REKLAMLAR = r.liste || []; reklamCiz(); } else toast(r.hata || 'Kaydedilemedi.'); }).catch(hata);
      }
      if (s && confirm('Bu kampanya silinsin mi?')) {
         gonder('/reklam/' + s.dataset.rkSil + '/sil').then(function (r) { if (r.ok) { REKLAMLAR = r.liste || []; reklamCiz(); toast('Silindi.'); } }).catch(hata);
      }
   });

   // ---------- AYARLAR
   document.querySelectorAll('[data-ayar]').forEach(function (c) { c.checked = Number(AYAR[c.dataset.ayar]) === 1; });
   el('waBekle').value = AYAR.gk_bekle_saat || 24;
   el('waKampanya').value = AYAR.gk_kampanya || '';
   el('waTestBildirim').addEventListener('click', function () {
      var b = this, k = el('waTestSonuc'); b.disabled = true; k.innerHTML = 'Gönderiliyor…';
      gonder('/test-bildirim').then(function (d) {
         b.disabled = false;
         if (!d.ok) { k.innerHTML = '<span style="color:#b91c1c">' + esc(d.hata || 'Gönderilemedi.') + '</span>'; return; }
         var ok = [], yok = [];
         (d.liste || []).forEach(function (x) { if (x.ulasti) ok.push(esc(x.ad)); else yok.push('<li><b>' + esc(x.ad) + '</b> — ' + esc(x.neden || 'Ulaşmadı.') + '</li>'); });
         k.innerHTML = (ok.length ? '<div style="color:#15803d"><b>✓ Telefonuna ulaştı:</b> ' + ok.join(', ') + '</div>' : '')
            + (yok.length ? '<div style="color:#b45309"><b>Ulaşmadı:</b><ul style="margin:4px 0 4px 18px;padding:0;color:#475569">' + yok.join('') + '</ul></div>' : '')
            + (!ok.length && !yok.length ? '<div>Yönetici yetkisinde personel bulunamadı.</div>' : '')
            + '<div style="color:#64748b">Panelin sağ üstündeki zilde de görünür.</div>';
      }).catch(function () { b.disabled = false; k.innerHTML = ''; hata(); });
   });

   el('waAyarKaydet').addEventListener('click', function () {
      var v = { gk_bekle_saat: el('waBekle').value, gk_kampanya: el('waKampanya').value }, b = this; b.disabled = true;
      document.querySelectorAll('[data-ayar]').forEach(function (c) { v[c.dataset.ayar] = c.checked ? 1 : 0; });
      gonder('/ayar', v).then(function (r) { b.disabled = false; if (r.ok) { AYAR = r.ayar; toast('Ayarlar kaydedildi.'); genelYukle(); } else toast('Kaydedilemedi.'); }).catch(function () { b.disabled = false; hata(); });
   });

   // Bildirimden gelindi (?sohbet=ID): o sohbet dogrudan acilir
   var acSohbet = new URLSearchParams(location.search).get('sohbet');
   if (acSohbet && /^\d+$/.test(acSohbet)) { sekmeAc('sohbet'); sohbetAc(acSohbet); }
})();
</script>
@endsection
