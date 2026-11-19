{{-- Cagri Kayitlari gorunumu (ck-*): Cagri Kayitlari sayfasi + Musteri detay > Arama Gecmisi ORTAK --}}
<style>
.ck-wrap{ --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC8; }
.ck-hero{ background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%); border-radius:18px; padding:22px 26px; color:#fff; margin-bottom:16px; box-shadow:0 12px 30px -10px rgba(92,0,142,.45); position:relative; overflow:hidden; }
.ck-hero:after{ content:""; position:absolute; right:-40px; top:-40px; width:170px; height:170px; border-radius:50%; background:rgba(255,255,255,.10); }
.ck-hero h4{ margin:0 0 6px; font-weight:700; font-size:22px; color:#fff; }
.ck-hero p{ margin:0; opacity:.92; font-size:13.5px; max-width:680px; }
.ck-geri{ display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:30px; background:#fff; color:#5C008E !important; font-size:14px; font-weight:800; text-decoration:none; margin-right:12px; vertical-align:middle; box-shadow:0 6px 16px -6px rgba(0,0,0,.4); }
.ck-geri:hover{ color:#5C008E; filter:brightness(.97); }

/* Özet kartları */
.ck-ozet{ display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
.ck-oz{ position:relative; background:#fff; border:1px solid #f0eef5; border-radius:14px; padding:18px 18px 16px; box-shadow:0 6px 18px -10px rgba(30,30,60,.15); overflow:hidden; transition:transform .15s, box-shadow .15s; }
.ck-oz:before{ content:""; position:absolute; left:0; top:0; height:100%; width:5px; background:var(--acc,#9097ad); }
.ck-oz:after{ content:""; position:absolute; right:-28px; top:-28px; width:92px; height:92px; border-radius:50%; background:var(--acc,#9097ad); opacity:.08; }
.ck-oz:hover{ transform:translateY(-2px); box-shadow:0 12px 26px -12px rgba(30,30,60,.28); }
.ck-oz .n{ font-size:28px; font-weight:800; line-height:1; color:var(--acc,#241b3a); }
.ck-oz .t{ font-size:12.5px; color:#6f6a82; margin-top:7px; display:flex; align-items:center; gap:6px; font-weight:600; }
.ck-oz .dot{ width:9px; height:9px; border-radius:50%; display:inline-block; background:var(--acc,#9097ad); box-shadow:0 0 0 3px color-mix(in srgb, var(--acc,#9097ad) 22%, transparent); }
.ck-oz.t-mor{ --acc:#7c3aed; background:linear-gradient(135deg,#faf6ff 0%,#fff 60%); border-color:#ece1fb; }
.ck-oz.t-yesil{ --acc:#16a34a; background:linear-gradient(135deg,#f1fdf5 0%,#fff 60%); border-color:#d9f3e2; }
.ck-oz.t-mavi{ --acc:#1565c0; background:linear-gradient(135deg,#f1f7fe 0%,#fff 60%); border-color:#dbe9fa; }
.ck-oz.t-gri{ --acc:#5b6472; background:linear-gradient(135deg,#f6f7f9 0%,#fff 60%); border-color:#e8eaef; }
@media (max-width:680px){ .ck-ozet{ grid-template-columns:repeat(2,1fr); } }

/* İki kolon */
.ck-grid{ display:grid; grid-template-columns:380px 1fr; gap:16px; align-items:start; }
@media (max-width:991px){ .ck-grid{ grid-template-columns:1fr; } }
.ck-panel{ position:relative; background:#fff; border-radius:18px; border:1px solid #eef0f5; box-shadow:0 6px 20px -12px rgba(30,30,60,.16); overflow:hidden; }
.ck-panel:before{ content:""; position:absolute; left:0; right:0; top:0; height:4px; background:linear-gradient(90deg,#5C008E,#7B2FB8,#9D5DC8); }

/* Sol liste */
.ck-ara{ padding:12px 14px; border-bottom:1px solid #f0eef5; position:relative; background:linear-gradient(180deg,#faf6ff,#fff); }
.ck-ara input{ width:100%; border:1px solid #e7e9f0; border-radius:12px; padding:9px 12px 9px 34px; font-size:13px; outline:none; background:#fff; transition:border-color .15s, box-shadow .15s; }
.ck-ara input:focus{ border-color:#9D5DC8; box-shadow:0 0 0 3px rgba(157,93,200,.18); }
.ck-ara .fa{ position:absolute; left:26px; top:50%; transform:translateY(-50%); color:#aab0c0; }
.ck-liste{ max-height:62vh; overflow-y:auto; }
.ck-item{ display:flex; align-items:center; gap:11px; padding:12px 14px; border-bottom:1px solid #f4f3f8; cursor:pointer; transition:background .12s; }
.ck-item:hover{ background:#faf8fe; }
.ck-item.aktif{ background:#f3edfb; border-left:4px solid #6d28d9; }
.ck-av{ width:40px; height:40px; border-radius:50%; flex:0 0 40px; background:linear-gradient(135deg,#6d28d9,#9D5DC8); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; }
.ck-item .bil{ flex:1; min-width:0; }
.ck-item .ad{ font-weight:700; color:#241b3a; font-size:13.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ck-item .alt{ font-size:11.5px; color:#9a95ab; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ck-item .sag{ text-align:right; flex:0 0 auto; }
.ck-item .zmn{ font-size:11px; color:#aab0c0; }
.ck-badge{ font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:20px; display:inline-block; margin-top:3px; }
.b-yesil{ background:#e4f7ec; color:#16a34a; } .b-kirmizi{ background:#fdecec; color:#dc2626; }
.b-mavi{ background:#e7f1fd; color:#1565c0; } .b-turuncu{ background:#fff2e3; color:#e07a1a; }
.b-altin{ background:#fef7e0; color:#b7791f; } .b-gri{ background:#f0eef5; color:#8a85a0; }

/* Sağ detay */
.ck-detay{ padding:22px; min-height:420px; }
.ck-bos{ text-align:center; color:#9097ad; padding:80px 20px; }
.ck-bos i{ font-size:46px; color:#d7d2e6; display:block; margin-bottom:14px; }
.ck-d-bas{ display:flex; align-items:center; gap:14px; margin-bottom:16px; }
.ck-d-av{ width:54px; height:54px; border-radius:50%; flex:0 0 54px; background:linear-gradient(135deg,#6d28d9,#9D5DC8); color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; }
.ck-d-ad{ font-size:20px; font-weight:800; color:#241b3a; }
.ck-d-tel{ font-size:13px; color:#6b7280; margin-top:2px; }
.ck-d-satir{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.ck-d-chip{ background:#faf9fc; border:1px solid #efeaf6; border-radius:10px; padding:8px 12px; font-size:12.5px; color:#574f6b; }
.ck-d-chip b{ color:#241b3a; }
.ck-d-not{ background:#faf9fc; border:1px solid #efeaf6; border-radius:12px; padding:14px; font-size:14px; color:#444; line-height:1.5; margin-bottom:14px; }
.ck-d-not .lbl{ font-size:11.5px; font-weight:700; color:#8a85a0; display:block; margin-bottom:6px; }
.ck-d-audio{ margin-bottom:16px; }
.ck-d-audio audio{ width:100%; }
.ck-d-audio .yok{ font-size:13px; color:#9a95ab; }
.ck-gecmis-bas{ font-size:13px; font-weight:800; color:#5C008E; margin:18px 0 10px; }
.ck-g-row{ border:1px solid #efeaf6; border-left:4px solid #cfc7df; border-radius:12px; padding:11px 13px; margin-bottom:9px; }
.ck-g-row.bl-yesil{ border-left-color:#16a34a; } .ck-g-row.bl-kirmizi{ border-left-color:#dc2626; }
.ck-g-row.bl-mavi{ border-left-color:#1565c0; } .ck-g-row.bl-turuncu{ border-left-color:#e07a1a; }
.ck-g-row.bl-altin{ border-left-color:#b7791f; }
.ck-g-row .ust{ display:flex; align-items:center; gap:8px; }
.ck-g-row .zmn{ margin-left:auto; font-size:12px; color:#9a95ab; }
.ck-g-row .not{ font-size:12.5px; color:#574f6b; margin-top:6px; }
.ck-g-row audio{ width:100%; margin-top:8px; height:34px; }

/* Musteri detay > Arama Gecmisi: arama randevusu kartlari */
.ck-ar-bas{ font-size:13px; font-weight:800; color:#5C008E; margin:0 0 10px; }
.ck-ar-liste{ display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:10px; margin-bottom:16px; }
.ck-ar-kart{ background:#fff; border:1px solid #efeaf6; border-left:5px solid #7c3aed; border-radius:12px; padding:12px 14px; box-shadow:0 6px 18px -12px rgba(30,30,60,.18); }
.ck-ar-kart.d-gecikti{ border-left-color:#dc2626; } .ck-ar-kart.d-tamamlandi{ border-left-color:#16a34a; }
.ck-ar-kart .zm{ font-size:16px; font-weight:800; color:#241b3a; }
.ck-ar-kart .al{ font-size:12px; color:#6f6a82; margin-top:4px; }
.ck-ozet.c5{ grid-template-columns:repeat(5,1fr); }
@media (max-width:991px){ .ck-ozet.c5{ grid-template-columns:repeat(2,1fr); } }
</style>
