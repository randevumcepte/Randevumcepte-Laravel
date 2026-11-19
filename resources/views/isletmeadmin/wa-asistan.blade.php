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
   .wa-hero-bolum { position:relative; z-index:1; flex:1 1 0; min-width:0; display:flex; align-items:center; gap:16px; padding:0 22px; border-left:1px solid rgba(255,255,255,.22); }
   .wa-hero-bolum.ilk { border-left:none; padding-left:4px; }
   .wa-hero-bolum > i { font-size:40px; opacity:.92; }
   .wa-hero-e { font-size:15px; opacity:.92; }
   .wa-hero-d { font-size:34px; font-weight:800; line-height:1.1; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
   .wa-hero-d small { font-size:18px; font-weight:600; }
   .wa-hero-yazi { flex:0 0 auto; margin-left:auto; position:relative; z-index:1; font-family:'Caveat',cursive; font-size:30px; line-height:1.05; transform:rotate(-6deg); text-align:center; padding-right:6px; }
   .wa-hero-yazi svg { display:block; margin:2px auto 0; }
   .wa-trend { display:inline-flex; align-items:center; gap:3px; font-size:12.5px; font-weight:700; padding:3px 9px; border-radius:999px; }
   .wa-hero .wa-trend { background:rgba(22,163,74,.85); color:#fff; }
   .wa-hero .wa-trend.dus { background:rgba(220,38,38,.85); }
   .wa-uyari { display:flex; gap:10px; flex-wrap:wrap; margin:-6px 0 16px; }
   .wa-uyari a { display:inline-flex; align-items:center; gap:8px; background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; border-radius:12px; padding:8px 12px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }

   /* kpi */
   .wa-kpiler { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:16px; margin-bottom:18px; }
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
   .wa-avatar { flex:0 0 auto; width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; color:#fff; }
   .wa-konusma .ic { flex:1; min-width:0; }
   .wa-konusma .ad { font-weight:700; font-size:15px; }
   .wa-konusma .mt { font-size:13.5px; color:var(--gri); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
   .wa-konusma .sa { font-size:12.5px; color:var(--gri); flex:0 0 auto; }
   .wa-konusma .bi-chevron-right { color:#9b92b3; }
   .wa-hap { flex:0 0 auto; font-size:12px; font-weight:700; padding:6px 12px; border-radius:999px; white-space:nowrap; }
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
   .wa-ai .robot { flex:0 0 auto; width:120px; }
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
   .wa-iki { display:grid; grid-template-columns:minmax(260px,380px) minmax(0,1fr); gap:18px; }
   .wa-liste { max-height:640px; overflow:auto; display:flex; flex-direction:column; gap:2px; }
   .wa-liste .wa-konusma.secili { background:#f3ecff; border-radius:12px; }
   .wa-sohbet { display:flex; flex-direction:column; gap:8px; max-height:560px; overflow:auto; padding:6px 2px; }
   .wa-balon { max-width:82%; padding:9px 12px; border-radius:14px; font-size:14px; white-space:pre-wrap; word-wrap:break-word; line-height:1.45; }
   .wa-balon.musteri { align-self:flex-start; background:#f1f5f9; border-top-left-radius:4px; }
   .wa-balon.asistan { align-self:flex-end; background:#efe6fd; border-top-right-radius:4px; }
   .wa-balon.personel { align-self:flex-end; background:#dcfce7; border-top-right-radius:4px; }
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

   @media (max-width: 1400px) { .wa-kazanclar { grid-template-columns:repeat(3,minmax(0,1fr)); } .wa-hero-yazi { display:none; } }
   @media (max-width: 1280px) { .wa-kpiler { grid-template-columns:repeat(3,minmax(0,1fr)); } }
   @media (max-width: 1100px) { .wa-orta, .wa-alt-sira { grid-template-columns:minmax(0,1fr); } }
   @media (max-width: 900px) {
      .wa-hero { flex-direction:column; align-items:flex-start; padding:22px; gap:16px; }
      .wa-hero-bolum { border-left:none; padding:0; border-top:1px solid rgba(255,255,255,.2); padding-top:14px; width:100%; }
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
      .wa-ai .robot { width:84px; }
   }
   @media (max-width: 360px) { .wa-kpiler, .wa-kazanclar { grid-template-columns:minmax(0,1fr); } }
</style>

<div class="wa">
   <div class="wa-ust">
      <div class="wa-sekmeler" id="waSekmeler" role="tablist">
         <button class="wa-sekme aktif" data-p="kazanc"><i class="bi bi-bar-chart-fill"></i>Genel Bakış</button>
         <button class="wa-sekme" data-p="kaz"><i class="bi bi-graph-up-arrow"></i>Kazandırdıkları</button>
         <button class="wa-sekme" data-p="sohbet"><i class="bi bi-whatsapp"></i>Sohbetler</button>
         <button class="wa-sekme" data-p="gk"><i class="bi bi-arrow-repeat"></i>Kaçan & Geri Kazanılan</button>
         <button class="wa-sekme" data-p="gorsel"><i class="bi bi-camera"></i>Fotoğraf Fiyatları</button>
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
            <input type="text" id="waAra" class="wa-girdi" style="width:240px;max-width:100%" placeholder="Ad veya telefon ara">
            <button class="wa-btn ikincil" id="waSohbetYukle"><i class="bi bi-search"></i> Listele</button>
         </div>
      </div>
      <div class="wa-iki">
         <div class="wa-kart" style="padding:12px"><div class="wa-liste" id="waSohbetListe"><div class="wa-bos">Yükleniyor…</div></div></div>
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
      <div class="wa-bilgi">Müşteri WhatsApp'tan model fotoğrafı gönderdiğinde asistan fotoğraftaki uygulamaları tanır; <b>fiyatı aşağıdaki kurallarınızdan sistem hesaplar</b> (asistan fiyat uydurmaz). Ana işlem için bir hizmetinizi seçin ya da sabit fiyat yazın; ekler için fiyat yazın. Boş bıraktığınız ekler fiyata eklenmez. Ana işlemin kuralı yoksa müşteri size aktarılır.</div>
      <div class="wa-kart">
         <div class="wa-satir">
            <label class="wa-alt" for="waSektor">Sektör</label>
            <select id="waSektor" class="wa-girdi" style="width:auto"></select>
         </div>
         <div class="wa-tablo-sar" id="waKuralForm"></div>
         <div style="margin-top:14px"><label class="wa-alt" for="waGorselNot">Fiyatın altına eklenecek not (isteğe bağlı)</label>
            <textarea id="waGorselNot" class="wa-girdi" rows="2" maxlength="500" placeholder="Örn. Mevcut protezin sökümü 150 TL eklenir."></textarea></div>
         <div style="margin-top:14px"><button class="wa-btn" id="waKuralKaydet"><i class="bi bi-check2"></i> Kuralları kaydet</button></div>
      </div>
   </div>

   {{-- ================= OGRENILEN ================= --}}
   <div class="wa-panel" id="p-ogr">
      <div class="wa-bilgi">Asistan genel bir soruya (ör. "otopark var mı") yapay zekâyla bir kez cevap verince o cevabı öğrenir; aynı soru sonra <b>ücretsiz ve anında</b> cevaplanır. Yanlış ya da eskimiş bir cevabı silebilirsiniz; asistan bir dahaki sefere yeniden düşünür. Öğrenilen cevaplar 30 günde bir yenilenir.</div>
      <div class="wa-kart"><div class="wa-tablo-sar" id="waOgrListe"><div class="wa-bos">Yükleniyor…</div></div></div>
   </div>

   {{-- ================= AYARLAR ================= --}}
   <div class="wa-panel" id="p-ayar">
      <div class="wa-kart">
         <div class="wa-anahtar"><div class="t"><b>Fiyat sorularını cevaplasın</b><div>"Protez tırnak ne kadar?" gibi sorulara hizmet listenizdeki fiyatla cevap verir. Kapalıysa fiyat soran müşteri size aktarılır.</div></div><input type="checkbox" data-ayar="fiyat_soyle" aria-label="Fiyat sorularını cevaplasın"></div>
         <div class="wa-anahtar"><div class="t"><b>Fotoğraftan fiyat versin</b><div>Model fotoğrafına "Fotoğraf Fiyatları" kurallarınızla fiyat verir.</div></div><input type="checkbox" data-ayar="gorsel_fiyat" aria-label="Fotoğraftan fiyat versin"></div>
         <div class="wa-anahtar"><div class="t"><b>Randevudan sonra memnuniyet sorsun</b><div>Randevu oluşturulunca tek satırlık 👍 / 👎 sorusu eklenir.</div></div><input type="checkbox" data-ayar="memnuniyet_sor" aria-label="Memnuniyet sorsun"></div>
         <div class="wa-anahtar"><div class="t"><b>Kaçan müşteriye tekrar yazsın</b><div>Fiyat/bilgi alıp randevu oluşturmayan müşteriye belirlediğiniz süre sonunda, mesai saatinde bir kez yazar ve gerçek boş saatlerinizi önerir. Müşteri "dur" yazarsa bir daha yazılmaz.</div></div><input type="checkbox" data-ayar="geri_kazanim" aria-label="Kaçan müşteriye tekrar yazsın"></div>
         <div class="wa-satir" style="margin-top:14px">
            <label class="wa-alt" for="waBekle">Kaç saat sonra yazsın</label>
            <input type="number" id="waBekle" class="wa-girdi" style="width:100px" min="2" max="168">
         </div>
         <label class="wa-alt" for="waKampanya">Fiyatı yüksek bulan müşteriye eklenecek teklif cümleniz (isteğe bağlı — boşsa teklif yapılmaz; asistan kendisi indirim uydurmaz)</label>
         <textarea id="waKampanya" class="wa-girdi" rows="2" maxlength="500" placeholder="Örn. Bu haftaya özel protez tırnakta %10 indirimimiz var."></textarea>
         <div style="margin-top:14px"><button class="wa-btn" id="waAyarKaydet"><i class="bi bi-check2"></i> Ayarları kaydet</button></div>
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
   var KOK = '/isletmeyonetim/whatsapp-asistan';

   function el(id) { return document.getElementById(id); }
   function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
   function url(yol, p) { p = p || {}; if (SUBE) p.sube = SUBE; var q = Object.keys(p).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]); }).join('&'); return KOK + yol + (q ? '?' + q : ''); }
   function al(yol, p) { return fetch(url(yol, p), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); }); }
   function gonder(yol, veri) {
      return fetch(url(yol), { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(veri || {}) })
         .then(function (r) { return r.json(); });
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
   function avatar(ad) { var s = String(ad || '?').trim(), h = 0; for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
      var p = s.split(/\s+/), bas = ((p[0] || '?')[0] + (p.length > 1 ? p[p.length - 1][0] : '')).toLocaleUpperCase('tr-TR');
      return '<div class="wa-avatar" style="background:' + RENK[h % RENK.length] + '">' + esc(bas) + '</div>'; }
   function hapSinif(s) { return /Randevu alındı|Oluşturuldu|teyit|değişti/.test(s) ? 'y' : (/Fiyat/.test(s) ? 'm' : (/aktarıldı|Personelde/.test(s) ? 's' : (/iptal|durduruldu|Bot/.test(s) ? 'k' : 'g'))); }

   // ---------- sekmeler
   var yuklendi = {}, SEKME_YUKLE = { sohbet: sohbetListe, gk: gkYukle, ogr: ogrYukle };
   function sekmeAc(p) {
      document.querySelectorAll('.wa-sekme').forEach(function (x) { x.classList.toggle('aktif', x.dataset.p === p); });
      document.querySelectorAll('.wa-panel').forEach(function (x) { x.classList.toggle('aktif', x.id === 'p-' + p); });
      if (!yuklendi[p] && SEKME_YUKLE[p]) { yuklendi[p] = 1; SEKME_YUKLE[p](); }
      if (p === 'kazanc' && sonGrafik) grafikCiz();
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
   function kaz(sinif, ikon, d, e, a) {
      return '<div class="wa-kaz ' + sinif + '"><div class="wa-kpi-ikon"><i class="bi ' + ikon + '"></i></div><div class="d">' + d + '</div><div class="e">' + e + '</div>' + (a ? '<div class="a">' + a + '</div>' : '') + '</div>';
   }
   var ROBOT = '<svg class="robot" viewBox="0 0 120 120" aria-hidden="true"><defs><linearGradient id="rb" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#e9ddff"/></linearGradient></defs>'
      + '<circle cx="60" cy="16" r="6" fill="#7c3aed"/><rect x="57" y="18" width="6" height="12" rx="3" fill="#a78bfa"/>'
      + '<rect x="14" y="44" width="14" height="30" rx="7" fill="#6d28d9"/><rect x="92" y="44" width="14" height="30" rx="7" fill="#6d28d9"/>'
      + '<rect x="22" y="28" width="76" height="64" rx="30" fill="url(#rb)" stroke="#c4b5fd" stroke-width="2"/>'
      + '<rect x="32" y="42" width="56" height="34" rx="17" fill="#1f1638"/><ellipse cx="48" cy="58" rx="6" ry="7" fill="#7dd3fc"/><ellipse cx="72" cy="58" rx="6" ry="7" fill="#7dd3fc"/>'
      + '<path d="M50 68 Q60 74 70 68" stroke="#7dd3fc" stroke-width="3" fill="none" stroke-linecap="round"/>'
      + '<path d="M40 100 Q60 88 80 100 L84 118 L36 118 Z" fill="#7c3aed"/><circle cx="92" cy="98" r="14" fill="#16a34a"/><path d="M85 98 l5 5 l9 -10" stroke="#fff" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';

   function genelYukle() {
      al('/ozet', { donem: donemSec.value }).then(function (r) {
         if (!r.ok) { el('waGenel').innerHTML = '<div class="wa-bos">' + esc(r.hata || 'Yüklenemedi') + '</div>'; return; }
         sonOzet = r.ozet; var o = r.ozet, t = r.trend, s = r.seri, p = r.paket, a = r.asistan, kz = o.kazanc, h = '';
         var toplamRandevu = o.randevu.adet + o.geri_kazanim.randevu, k = '';
         // ===== GENEL BAKIS (tasarimla birebir): hero, 5 kart, grafik + son konusmalar, hizli islemler + AI kart
         h += '<div class="wa-hero"><div class="wa-hero-ikon"><i class="bi bi-whatsapp"></i></div>'
            + '<div class="wa-hero-bolum ilk"><div><div class="wa-hero-e">WhatsApp üzerinden toplam</div><div class="wa-hero-d">' + sayi(o.sohbet.gelen_mesaj) + ' <small>mesaj aldınız</small>' + trendHtml(t.mesaj) + '</div></div></div>'
            + '<div class="wa-hero-bolum"><i class="bi bi-calendar-check"></i><div><div class="wa-hero-e">Toplam Randevu</div><div class="wa-hero-d">' + sayi(toplamRandevu) + trendHtml(t.randevu) + '</div></div></div>'
            + '<div class="wa-hero-bolum"><i class="bi bi-people-fill"></i><div><div class="wa-hero-e">Yeni Müşteri</div><div class="wa-hero-d">' + sayi(o.randevu.yeni_musteri) + trendHtml(t.yeni) + '</div></div></div>'
            + '<div class="wa-hero-yazi">Daha fazla müşteri,<br>daha fazla randevu!<svg width="150" height="12" viewBox="0 0 150 12"><path d="M4 8 Q75 -2 146 6" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg></div></div>';
         // ===== KAZANDIRDIKLARI sekmesi: uyari seridi + kazanc kartlari + paket + yolculuk + sorular
         var u = '';
         if (o.aktar_bekleyen.length) u += '<a data-git="sohbet"><i class="bi bi-hourglass-split"></i>' + o.aktar_bekleyen.length + ' müşteri sizden dönüş bekliyor</a>';
         if (o.kacan.length) u += '<a data-git="gk"><i class="bi bi-person-plus"></i>' + o.kacan.length + ' müşteri bilgi alıp randevu almadı</a>';
         if (p && p.var && p.seviye) u += '<a data-git="kaz"><i class="bi bi-exclamation-triangle"></i>' + (p.kapali ? 'Paket doldu: asistan cevap vermiyor' : 'Paketin %' + p.yuzde + '\'i kullanıldı') + '</a>';
         if (u) k += '<div class="wa-uyari" style="margin-top:0">' + u + '</div>';
         // kpi
         var memSeri = s.memnun.map(function (m, i) { var tt = m + s.memnun_degil[i]; return tt ? m / tt * 100 : null; });
         h += '<div class="wa-kpiler">'
            + kpi('wa-k-mor', 'bi-calendar-check', sayi(toplamRandevu), 'Toplam Randevu', trendHtml(t.randevu), s.randevu, '#7c3aed')
            + kpi('wa-k-lila', 'bi-people', sayi(o.randevu.yeni_musteri), 'Yeni Müşteri', trendHtml(t.yeni), s.yeni, '#16a34a')
            + kpi('wa-k-mavi', 'bi-chat-square-text', sayi(o.sohbet.gelen_mesaj), 'WhatsApp Mesajı', trendHtml(t.mesaj), s.mesaj, '#2563eb')
            + kpi('wa-k-tur', 'bi-star-fill', o.sohbet.memnuniyet_orani == null ? '—' : '%' + o.sohbet.memnuniyet_orani, 'Müşteri Memnuniyeti', trendHtml(t.memnuniyet, false, true), memSeri, '#ec4899')
            + kpi('wa-k-yesil', 'bi-clock-history', sure(o.cevap.medyan_sure_sn), 'Ortalama Yanıt Süresi', trendHtml(t.sure, true), s.sure, '#16a34a')
            + '</div>';
         // kazandirdiklari kartlari (Genel Bakis'ta, 5 kartin altinda)
         h += '<div class="wa-bolum-baslik"><i class="bi bi-graph-up-arrow"></i>Asistanın size kazandırdıkları</div><div class="wa-kazanclar">'
            + kaz('wa-k-mor', 'bi-cash-coin', tl(kz.gerceklesen > 0 ? kz.gerceklesen : kz.beklenen), kz.gerceklesen > 0 ? 'Gerçekleşen kazanç' : 'Randevu tutarı', kz.kat ? 'Pakete ödediğinizin <b>' + String(kz.kat).replace('.', ',') + ' katı</b>' : (kz.gerceklesen > 0 ? 'salona gelen müşterilerden' : 'gelişler "geldi" işaretlenince kesinleşir'))
            + kaz('wa-k-lila', 'bi-moon-stars', tl(kz.mesai_disi), 'Mesai dışı kazanç', sayi(o.randevu.mesai_disi) + ' randevu personel yokken alındı')
            + kaz('wa-k-tur', 'bi-arrow-repeat', sayi(o.geri_kazanim.randevu), 'Geri kazanılan müşteri', sayi(o.geri_kazanim.yazildi) + ' kaçan müşteriye yazıldı · ' + tl(o.geri_kazanim.tutar))
            + kaz('wa-k-mavi', 'bi-hourglass-split', String(o.cevap.personel_saat).replace('.', ',') + ' saat', 'Personele kazandırılan zaman', sayi(o.cevap.toplam) + ' mesaj cevaplandı · ' + sayi(o.cevap.mesai_disi) + ' mesai dışı')
            + kaz('wa-k-yesil', 'bi-shield-check', o.sohbet.cozulen_orani == null ? '—' : '%' + o.sohbet.cozulen_orani, 'Tek başına çözdüğü', 'personele aktarılmadan biten yazışmalar')
            + kaz('wa-k-mor', 'bi-camera', sayi(o.gorsel.fiyat_verildi), 'Fotoğrafa fiyat verildi', sayi(o.gorsel.randevu) + ' tanesi randevuya döndü')
            + '</div>';
         // grafik + son konusmalar
         h += '<div class="wa-orta"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-bar-chart-fill" style="color:#7c3aed"></i>Performans Grafiği</h4>'
            + '<div class="wa-toggle" id="waGrafikTur"><button data-g="hafta">Hafta</button><button data-g="ay">Ay</button><button data-g="yil">Yıl</button></div></div>'
            + '<div class="wa-lejant"><span class="r">Randevu Sayısı</span><span class="m">Mesaj Sayısı</span></div><div class="wa-grafik" id="waGrafik"><div class="wa-ipucu" id="waIpucu"></div></div></div>'
            + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-whatsapp" style="color:#25D366"></i>Son WhatsApp Konuşmaları</h4><button class="wa-tumu" data-git="sohbet">Tümünü Gör <i class="bi bi-arrow-right"></i></button></div>'
            + '<div id="waSonKonusmalar"><div class="wa-bos">Yükleniyor…</div></div></div></div>';
         // hizli islemler (panelin gercek sayfalari) + AI kart
         var ok = function (acik, metin) { return '<li class="' + (acik ? '' : 'kapali') + '"><i class="bi bi-check-circle-fill"></i>' + metin + '</li>'; };
         var panel = function (yol) { return '/isletmeyonetim/' + yol + (SUBE ? '?sube=' + encodeURIComponent(SUBE) : ''); };
         h += '<div class="wa-alt-sira"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-lightning-charge-fill" style="color:#f59e0b"></i>Hızlı İşlemler</h4></div><div class="wa-hizli">'
            + '<a class="wa-h1" href="' + panel('randevular') + '"><i class="bi bi-calendar-plus"></i>Yeni Randevu Oluştur</a>'
            + '<a class="wa-h2" href="' + panel('musteriler') + '"><i class="bi bi-people-fill"></i>Müşteri Ekle</a>'
            + '<a class="wa-h3" href="' + panel('kampanya_yonetimi') + '"><i class="bi bi-megaphone"></i>Kampanya Yayınla</a>'
            + '<a class="wa-h4" href="' + panel('raporlar') + '"><i class="bi bi-bar-chart-line"></i>Raporları Görüntüle</a></div></div>'
            + '<div class="wa-kart wa-ai">' + ROBOT + '<div class="orta-ic"><h4>RandevumCepte AI Asistan <span class="wa-aktif ' + (a.acik && !(p && p.kapali) ? '' : 'kapali') + '"><i class="bi bi-check-circle-fill"></i>' + (a.acik ? (p && p.kapali ? 'Paket doldu' : 'Aktif') : 'Kapalı') + '</span></h4>'
            + '<p>Tüm müşterilerinize akıllı, hızlı ve kişiselleştirilmiş hizmet sunun.</p><button class="wa-btn" data-git="ayar">Ayarları Düzenle <i class="bi bi-arrow-right"></i></button></div>'
            + '<ul class="wa-liste-ok">' + ok(a.gorsel, 'Görsel Analiz (Fotoğraf)') + ok(a.fiyat, 'Akıllı Fiyatlandırma') + ok(true, 'Randevu Yönetimi') + ok(true, 'Müşteri Desteği') + '</ul></div></div>';
         // paket (Kazandirdiklari sekmesi)
         if (p && p.var) { var cls = p.kapali ? 'dolu' : (p.seviye ? 'uyari' : '');
            k += '<div class="wa-kart" style="margin-bottom:18px"><div class="wa-kart-bas"><h4><i class="bi bi-box-seam" style="color:#7c3aed"></i>Paket Kullanımı</h4></div>'
               + '<div class="wa-paketbar" style="margin:0">Bu ay <b>' + sayi(p.kullanilan) + ' / ' + sayi(p.dahil) + '</b> yapay zekâ mesajı kullanıldı · kalan ' + sayi(p.kalan) + ' · ' + sayi(o.cevap.ucretsiz) + ' ücretsiz cevap (paketten düşmez)'
               + '<div class="wa-bar ' + cls + '"><i style="width:' + p.yuzde + '%"></i></div></div>'
               + (p.kapali ? '<div class="wa-alt" style="color:#dc2626;margin-top:8px"><b>Paket doldu:</b> asistan ay sonuna kadar cevap vermiyor.</div>' : '') + '</div>'; }
         // yolculuk + ilgi alanlari (Kazandirdiklari sekmesi)
         var hu = o.huni, mx = Math.max(1, hu.yazdi);
         var etiket = function (obj, f) { var ks = Object.keys(obj || {}); return ks.length ? '<div class="wa-etiketler">' + ks.map(function (x) { return '<span>' + f(x, obj[x]) + '</span>'; }).join('') + '</div>' : '<div class="wa-alt">Henüz veri yok.</div>'; };
         k += '<div class="wa-alt-sira"><div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-funnel" style="color:#7c3aed"></i>Müşteri Yolculuğu</h4></div><div class="wa-huni">'
            + [['Yazdı', hu.yazdi], ['Bilgi / fiyat aldı', hu.bilgi], ['Randevu aldı', hu.randevu], ['Salona geldi', hu.geldi]].map(function (x) {
               return '<div class="s"><span>' + x[0] + '</span><div class="wa-bar"><i style="width:' + Math.round(x[1] / mx * 100) + '%"></i></div><b>' + sayi(x[1]) + '</b></div>'; }).join('')
            + '</div><div class="wa-alt" style="margin-top:10px">"Salona geldi" panelde randevuya "geldi" işaretlendikçe güncellenir.</div></div>'
            + '<div class="wa-kart"><div class="wa-kart-bas"><h4><i class="bi bi-stars" style="color:#ec4899"></i>Müşterileriniz neyi soruyor?</h4></div>'
            + '<b style="font-size:14px">En çok sorulan hizmetler</b>' + etiket(o.populer_hizmet, function (k, v) { return esc(k) + ' · ' + v; })
            + '<div style="height:14px"></div><b style="font-size:14px">En yoğun saatler</b>' + etiket(o.yogun_saat, function (k, v) { return String(k).padStart(2, '0') + ':00 · ' + v + ' mesaj'; }) + '</div></div>';
         el('waGenel').innerHTML = h;
         el('waKazanc').innerHTML = k;
         grafikYukle(grafikTur);
         sonKonusmalar();
      }).catch(function () { el('waGenel').innerHTML = '<div class="wa-bos">Rapor yüklenemedi.</div>'; });
   }
   el('waKazanc').addEventListener('click', function (e) {
      var g = e.target.closest('[data-git]'); if (g) { sekmeAc(g.dataset.git); window.scrollTo({ top: 0, behavior: 'smooth' }); }
   });
   el('waGenel').addEventListener('click', function (e) {
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
         var etk = { 'Randevu alındı': 'Randevu Oluşturuldu', 'Fiyat aldı': 'Fiyat Verildi', 'Tamamlandı': 'Bilgi Verildi', 'Devam ediyor': 'Yazışıyor' };
         hedef.innerHTML = r.liste.slice(0, 5).map(function (x) { return konusmaSatiri(Object.assign({}, x, { durum: etk[x.durum] || x.durum })); }).join('');
      }).catch(function () {});
   }
   function konusmaSatiri(x) {
      return '<div class="wa-konusma" data-sohbet="' + x.id + '" tabindex="0">' + avatar(x.ad || x.telefon) + '<div class="ic"><div class="ad">' + esc(x.ad || x.telefon || 'Müşteri') + '</div><div class="mt">' + esc(x.son_mesaj || '…') + '</div></div>'
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
         if (!r.liste.length) { el('waSohbetListe').innerHTML = '<div class="wa-bos">Bu tarihlerde sohbet yok.</div>'; return; }
         el('waSohbetListe').innerHTML = r.liste.map(konusmaSatiri).join('');
      }).catch(hata);
   }
   el('waSohbetListe').addEventListener('click', function (e) {
      var o = e.target.closest('[data-sohbet]'); if (!o) return;
      document.querySelectorAll('#waSohbetListe .wa-konusma').forEach(function (x) { x.classList.toggle('secili', x === o); });
      sohbetAc(o.dataset.sohbet);
   });
   function sohbetAc(id) {
      if (!yuklendi.sohbet) { yuklendi.sohbet = 1; sohbetListe(); }
      el('waSohbetDetay').innerHTML = '<div class="wa-bos">Yükleniyor…</div>';
      al('/sohbet/' + id).then(function (r) {
         if (!r.ok) { el('waSohbetDetay').innerHTML = '<div class="wa-bos">' + esc(r.hata || '') + '</div>'; return; }
         var s = r.sohbet, h = '<div class="wa-kart-bas"><div style="display:flex;align-items:center;gap:12px">' + avatar(s.ad || s.telefon) + '<div><b style="font-size:16px">' + esc(s.ad || 'Müşteri') + '</b><div class="wa-alt">' + esc(s.telefon || 'gizli numara') + '</div></div><span class="wa-hap ' + hapSinif(s.durum) + '">' + esc(s.durum) + '</span></div>'
            + (s.devralindi ? '<button class="wa-btn" data-geriver="' + s.id + '"><i class="bi bi-robot"></i> Asistana geri ver</button>' : '<button class="wa-btn ikincil" data-devral="' + s.id + '"><i class="bi bi-person"></i> Devral (asistan sussun)</button>') + '</div>';
         if (s.gorseller.length) {
            h += '<div class="wa-gorseller">' + s.gorseller.map(function (g) {
               return '<figure>' + (g.dosya ? '<img loading="lazy" alt="Müşterinin gönderdiği model" src="' + url('/gorsel/' + g.id) + '">' : '<div class="wa-bos" style="padding:10px">Fotoğraf silindi</div>')
                  + '<figcaption>' + esc(g.tarif || '') + (g.toplam != null ? '<br><b>' + tl(g.toplam) + '</b>' : ' <span class="wa-hap g">' + esc(g.durum) + '</span>') + '</figcaption></figure>'; }).join('') + '</div>';
         }
         h += '<div class="wa-sohbet">' + s.satirlar.map(function (m) {
            var r = m.rol === 'musteri' ? 'Müşteri' : (m.rol === 'personel' ? 'Personel' : (m.geri_kazanim ? 'Asistan · geri kazanma' : (m.hatirlatma ? 'Hatırlatma' : 'Asistan')));
            return '<div class="wa-balon ' + m.rol + '"><span class="r">' + r + '</span>' + esc(m.metin) + '</div>'; }).join('') + '</div>';
         h += '<div class="wa-alt" style="margin-top:10px">Devraldığınızda asistan bu sohbette 24 saat ya da siz geri verene kadar cevap vermez. Telefonunuzdan müşteriye yazdığınızda da asistan otomatik susar.</div>';
         el('waSohbetDetay').innerHTML = h;
      }).catch(hata);
   }
   el('waSohbetDetay').addEventListener('click', function (e) {
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

   // ---------- FOTOGRAF FIYATLARI
   var sektorSec = el('waSektor');
   Object.keys(SEKTORLER).forEach(function (k) { sektorSec.insertAdjacentHTML('beforeend', '<option value="' + esc(k) + '">' + esc(SEKTORLER[k].ad) + '</option>'); });
   sektorSec.value = AYAR.gorsel_sektor || 'tirnak';
   el('waGorselNot').value = AYAR.gorsel_not || '';
   function hizmetSecim(secili) {
      return '<select class="wa-girdi" data-hizmet style="max-width:260px"><option value="">— hizmet seçin —</option>' + HIZMETLER.map(function (h) {
         return '<option value="' + h.id + '"' + (String(h.id) === String(secili || '') ? ' selected' : '') + '>' + esc(h.ad) + (Number(h.fiyat) > 0 ? ' (' + tl(h.fiyat) + ')' : '') + '</option>'; }).join('') + '</select>';
   }
   function fiyatGirdi(deger, ph) { return '<input type="number" min="0" step="0.01" class="wa-girdi" data-fiyat style="width:130px" placeholder="' + (ph || 'TL') + '" value="' + (deger != null ? esc(deger) : '') + '">'; }
   function kuralCiz() {
      var s = SEKTORLER[sektorSec.value], k = sektorSec.value === (AYAR.gorsel_sektor || 'tirnak') ? KURALLAR : {}, h = '';
      h += '<b style="font-size:15px">Ana işlem</b><table class="wa-tablo" style="margin:6px 0 18px"><thead><tr><th>İşlem</th><th>Hizmetiniz (fiyatı listeden)</th><th>ya da sabit fiyat</th></tr></thead><tbody>';
      Object.keys(s.ana).forEach(function (a) { var r = k['ana:' + a] || {};
         h += '<tr data-anahtar="ana:' + a + '"><td>' + esc(s.ana[a]) + '</td><td>' + hizmetSecim(r.hizmet_id) + '</td><td>' + fiyatGirdi(r.fiyat) + '</td></tr>'; });
      h += '</tbody></table><b style="font-size:15px">Ekler</b><table class="wa-tablo" style="margin-top:6px"><thead><tr><th>Ek</th><th>Ek fiyat</th></tr></thead><tbody>';
      Object.keys(s.ozellikler).forEach(function (o) { var oz = s.ozellikler[o];
         if (oz.tip === 'bilgi') return;
         if (oz.tip === 'secim') Object.keys(oz.secenek).forEach(function (v) { var r = k[o + ':' + v] || {};
            h += '<tr data-anahtar="' + o + ':' + v + '"><td>' + esc(oz.ad) + ': ' + esc(oz.secenek[v]) + '</td><td>' + fiyatGirdi(r.fiyat, '+ TL') + '</td></tr>'; });
         else { var r = k[o] || {};
            h += '<tr data-anahtar="' + o + '"><td>' + esc(oz.ad) + (oz.tip === 'adet' ? ' <span class="wa-alt">(' + esc(oz.birim) + ' başı)</span>' : '') + '</td><td>' + fiyatGirdi(r.fiyat, oz.tip === 'adet' ? 'TL / ' + oz.birim : '+ TL') + '</td></tr>'; }
      });
      el('waKuralForm').innerHTML = h + '</tbody></table>';
   }
   sektorSec.addEventListener('change', kuralCiz); kuralCiz();
   el('waKuralKaydet').addEventListener('click', function () {
      var kur = {}, b = this; b.disabled = true;
      document.querySelectorAll('#waKuralForm tr[data-anahtar]').forEach(function (tr) {
         var hs = tr.querySelector('[data-hizmet]'), f = tr.querySelector('[data-fiyat]');
         var hid = hs ? hs.value : '', fy = f ? f.value : '';
         if (hid || fy !== '') kur[tr.dataset.anahtar] = { hizmet_id: hid || null, fiyat: fy !== '' ? fy : null };
      });
      Promise.all([gonder('/kurallar', { sektor: sektorSec.value, kurallar: kur }), gonder('/ayar', { gorsel_not: el('waGorselNot').value })]).then(function (r) {
         b.disabled = false;
         if (r[0].ok) { KURALLAR = r[0].kurallar; AYAR.gorsel_sektor = sektorSec.value; AYAR.gorsel_not = el('waGorselNot').value; toast('Fotoğraf fiyat kuralları kaydedildi.'); kuralCiz(); }
         else toast(r[0].hata || 'Kaydedilemedi.');
      }).catch(function () { b.disabled = false; hata(); });
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

   // ---------- AYARLAR
   document.querySelectorAll('[data-ayar]').forEach(function (c) { c.checked = Number(AYAR[c.dataset.ayar]) === 1; });
   el('waBekle').value = AYAR.gk_bekle_saat || 24;
   el('waKampanya').value = AYAR.gk_kampanya || '';
   el('waAyarKaydet').addEventListener('click', function () {
      var v = { gk_bekle_saat: el('waBekle').value, gk_kampanya: el('waKampanya').value }, b = this; b.disabled = true;
      document.querySelectorAll('[data-ayar]').forEach(function (c) { v[c.dataset.ayar] = c.checked ? 1 : 0; });
      gonder('/ayar', v).then(function (r) { b.disabled = false; if (r.ok) { AYAR = r.ayar; toast('Ayarlar kaydedildi.'); genelYukle(); } else toast('Kaydedilemedi.'); }).catch(function () { b.disabled = false; hata(); });
   });
})();
</script>
@endsection
