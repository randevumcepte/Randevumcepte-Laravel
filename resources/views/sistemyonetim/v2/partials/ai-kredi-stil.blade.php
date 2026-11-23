{{-- AI Kredi genel + isletme detay ortak stil --}}
<style>
    .ai-baslik { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
    .ai-baslik h1 { font-size:20px; font-weight:700; margin:0; color:#2a2340; }
    .ai-filtre { display:flex; gap:8px; flex-wrap:wrap; }
    .ai-filtre a { padding:7px 14px; border-radius:20px; font-size:12.5px; font-weight:600; text-decoration:none;
        border:1px solid #e3daf3; color:#6b7280; background:#fff; }
    .ai-filtre a.act { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border-color:transparent; }

    .ai-ozet { background:linear-gradient(135deg,#5C008E,#7B2FB8 60%,#9D5DC9); color:#fff; border-radius:16px; padding:18px 20px; margin-bottom:18px;
        font-size:15px; line-height:1.6; box-shadow:0 6px 20px rgba(92,0,142,.18); }
    .ai-ozet b { font-weight:700; }
    .ai-ozet .k { font-size:12px; opacity:.8; margin-top:6px; }

    .ai-kartlar { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:18px; }
    .ai-kart { position:relative; overflow:hidden; border-radius:16px; padding:16px 18px; color:#fff; box-shadow:0 6px 18px rgba(30,10,60,.12); }
    .ai-kart::after { content:""; position:absolute; right:-28px; top:-28px; width:110px; height:110px; border-radius:50%; background:rgba(255,255,255,.14); }
    .ai-kart .mdi.buyuk { position:absolute; right:14px; bottom:10px; font-size:38px; opacity:.25; }
    .ai-kart .e { font-size:12.5px; font-weight:600; opacity:.9; }
    .ai-kart .d { font-size:26px; font-weight:700; margin-top:5px; }
    .ai-kart .alt { font-size:12.5px; margin-top:4px; line-height:1.45; opacity:.92; position:relative; z-index:1; }
    .ai-kart.iyi     { background:linear-gradient(135deg,#15803d,#22c55e); }
    .ai-kart.kotu    { background:linear-gradient(135deg,#b91c1c,#f87171); }
    .ai-kart.bos     { background:linear-gradient(135deg,#b45309,#f59e0b); }
    .ai-kart.mor     { background:linear-gradient(135deg,#5C008E,#9D5DC9); }
    .ai-kart.mavi    { background:linear-gradient(135deg,#1d4ed8,#38bdf8); }
    .ai-kart.turuncu { background:linear-gradient(135deg,#c2410c,#fb923c); }

    .ai-panel { background:linear-gradient(180deg,#faf7ff,#fff 120px); border:1px solid #e9e0f8; border-radius:16px; padding:18px; margin-bottom:18px; box-shadow:0 4px 14px rgba(92,0,142,.07); }
    .ai-panel h2 { font-size:16px; color:#2a2340; font-weight:700; margin:0 0 4px; display:flex; align-items:center; gap:10px; }
    .rozet { width:30px; height:30px; flex:0 0 30px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:17px; }
    .ai-panel .acik { font-size:13px; color:#8b7ba8; margin:0 0 14px; line-height:1.5; }

    .ai-kanallar { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; }
    .ai-kanal { border:1px solid var(--cizgi); border-top:4px solid var(--renk); border-radius:14px; padding:16px; background:linear-gradient(160deg,var(--zemin),#fff 75%); box-shadow:0 4px 14px var(--golge);
        display:flex; flex-direction:column; gap:10px; }
    .ai-kanal .ust { display:flex; align-items:center; gap:12px; }
    .ai-ikon { width:42px; height:42px; flex:0 0 42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:21px; color:#fff; }
    .ai-kanal .ad { font-size:15px; font-weight:700; color:#2a2340; }
    .ai-kanal .ne { font-size:12px; color:#8b7ba8; margin-top:2px; line-height:1.4; }
    .ai-kanal .tutar .tl { font-size:26px; font-weight:700; color:var(--koyu); line-height:1.1; }
    .ai-kanal .tutar .us { font-size:12px; color:#94a3b8; margin-left:6px; }
    .ai-kanal .bilgi { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .ai-kanal .bilgi div { background:rgba(255,255,255,.85); border:1px solid var(--cizgi); border-radius:9px; padding:7px 10px; font-size:11.5px; color:#6b5b8a; }
    .ai-kanal .bilgi div b { display:block; font-size:14px; color:#2a2340; margin-top:1px; }
    .ai-kanal .model { font-size:11.5px; font-weight:600; color:#fff; background:var(--renk); border-radius:20px; padding:3px 10px; align-self:flex-start; }
    .ai-pay { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .ai-pay .bg { flex:1; background:#f0ebf8; border-radius:6px; height:8px; overflow:hidden; max-width:420px; }
    .ai-pay .bar { height:8px; border-radius:6px; }
    .ai-pay .yuzde { font-size:12px; font-weight:700; color:#6b5b8a; min-width:38px; }
    .ai-cip { display:inline-block; font-size:11.5px; padding:3px 9px; border-radius:20px; background:#f5f2fb; color:#5b4a7a; margin:3px 4px 0 0; }
    .ai-cip.yesil { background:#e8f8ef; color:#15803d; }

    .ai-tablo { width:100%; border-collapse:collapse; font-size:13px; }
    .ai-tablo th { text-align:left; font-size:11px; color:#5C008E; font-weight:600; padding:10px; background:#f3ecfc; }
    .ai-tablo th:first-child { border-radius:9px 0 0 9px; } .ai-tablo th:last-child { border-radius:0 9px 9px 0; }
    .ai-tablo tbody tr:hover td { background:#faf6ff; }
    .ai-tablo td { padding:11px 10px; border-bottom:1px solid #f4f1fa; color:#3a2a5c; font-size:13.5px; vertical-align:top; }
    .ai-tablo td.sag, .ai-tablo th.sag { text-align:right; }
    .ai-tablo td.sag { font-weight:700; color:#2a2340; white-space:nowrap; }
    .ai-tablo-sar { overflow-x:auto; }

    .ai-form { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .ai-form label { font-size:11.5px; color:#8b7ba8; font-weight:600; display:block; margin-bottom:4px; }
    .ai-form input { background:#f7f6fb; border:1px solid #e3daf3; border-radius:9px; padding:9px 12px; color:#2a2340; font-size:14px; width:160px; }
    .ai-form button { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border:none; border-radius:9px; padding:10px 18px; font-weight:700; cursor:pointer; }
    .ai-bar-bg { background:#f0ebf8; border-radius:6px; height:8px; overflow:hidden; }
    .ai-bar { height:8px; background:linear-gradient(90deg,#7B2FB8,#38bdf8); border-radius:6px; }
    .ai-uyari { background:#fef8e7; border:1px solid #f3d98a; color:#8a6100; border-radius:10px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
    .ai-ok { background:#e8f8ef; border:1px solid #a7e0c0; color:#15803d; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }

    details.ai-detay > summary { cursor:pointer; font-size:16px; font-weight:700; color:#2a2340; list-style:none; display:flex; align-items:center; gap:8px; }
    details.ai-detay > summary::-webkit-details-marker { display:none; }
    details.ai-detay > summary .ok { transition:transform .2s; color:#8b7ba8; }
    details.ai-detay[open] > summary .ok { transform:rotate(90deg); }
    details.ai-detay > summary .not { font-size:12.5px; font-weight:400; color:#8b7ba8; }
    .ai-sozluk { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:10px; margin:14px 0; }
    .ai-sozluk div { background:#f9f7fd; border-radius:10px; padding:10px 12px; font-size:12.5px; color:#4b3d6b; line-height:1.5; }
    .ai-sozluk b { color:#2a2340; display:block; margin-bottom:2px; }

    /* Isletme listesi: tiklanabilir satir kartlari */
    .ai-isletmeler { display:flex; flex-direction:column; gap:12px; }
    .ai-isletme { display:grid; grid-template-columns:28px 52px minmax(180px,1.1fr) minmax(220px,2fr) 130px 84px; gap:16px; align-items:center;
        padding:16px 18px; border:1px solid #ece4f8; border-left:5px solid var(--renk); border-radius:14px; text-decoration:none !important; color:inherit;
        background:linear-gradient(90deg,var(--zemin),#fff 45%); box-shadow:0 3px 10px rgba(92,0,142,.06); transition:transform .15s, box-shadow .15s; }
    a.ai-isletme[href]:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(92,0,142,.14); }
    .ai-isletme .sira { width:28px; height:28px; border-radius:50%; background:#f3ecfc; color:#5C008E; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; }
    .ai-isletme .avatar { width:52px; height:52px; border-radius:14px; color:#fff; font-size:22px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,.12); }
    .ai-isletme .kimlik .ad { font-size:17px; font-weight:700; color:#2a2340; }
    .ai-isletme .kimlik .alt { font-size:12.5px; color:#8b7ba8; margin-top:2px; }
    .ai-isletme .kimlik .ai-pay .bg { max-width:200px; }
    .ai-isletme .yigin { display:flex; height:12px; border-radius:7px; overflow:hidden; background:#f0ebf8; }
    .ai-isletme .yigin span { height:100%; }
    .ai-isletme .lejant { display:flex; flex-wrap:wrap; gap:4px 14px; margin-top:8px; font-size:12.5px; color:#4b3d6b; }
    .ai-isletme .lejant i { display:inline-block; width:9px; height:9px; border-radius:3px; margin-right:5px; }
    .ai-isletme .lejant b { color:#2a2340; }
    .ai-isletme .tutar { text-align:right; }
    .ai-isletme .tutar .tl { font-size:22px; font-weight:700; color:var(--renk); }
    .ai-isletme .tutar .us { font-size:12px; color:#94a3b8; }
    .ai-isletme .git { justify-self:end; background:var(--renk); color:#fff; font-size:12.5px; font-weight:700; padding:8px 12px; border-radius:10px; white-space:nowrap; }

    /* Detay sayfasi */
    .ai-geri { display:inline-flex; align-items:center; gap:4px; font-size:13px; font-weight:600; color:#7B2FB8; text-decoration:none; margin-bottom:10px; }
    .ai-kimlik-bas { display:flex; align-items:center; gap:14px; }
    .ai-kimlik-bas .avatar { width:56px; height:56px; border-radius:16px; color:#fff; font-size:24px; font-weight:700; display:flex; align-items:center; justify-content:center; }
    .ai-kimlik-bas h1 { font-size:22px; }
    .ai-kimlik-bas .alt { font-size:13px; color:#8b7ba8; margin-top:2px; }
    .ai-kimlik-bas .alt a { color:#7B2FB8; }
    .ai-iki { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:18px; }
    .ai-iki > .ai-panel { margin-bottom:18px; }
    .ai-etiket { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; background:#f3ecfc; color:#5C008E; }
    .ai-etiket.randevu { background:#e8f8ef; color:#15803d; }
    .ai-etiket.iptal { background:#fdecec; color:#b91c1c; }
    .ai-etiket.aktar { background:#fef3c7; color:#92400e; }

    @media (max-width:1100px) {
        .ai-kanallar { grid-template-columns:repeat(2, minmax(0,1fr)); }
        .ai-isletme { grid-template-columns:52px 1fr auto; }
        .ai-isletme .sira { display:none; }
        .ai-isletme .dagilim { grid-column:1 / -1; }
        .ai-isletme .git { grid-column:1 / -1; justify-self:stretch; text-align:center; }
        .ai-iki { grid-template-columns:1fr; }
    }
    @media (max-width:640px)  { .ai-kanallar { grid-template-columns:1fr; } }
</style>
