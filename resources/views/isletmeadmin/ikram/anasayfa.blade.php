@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif
@extends($_layout)
@section('content')
{{-- Ikram Hizmet ana sayfa ("Mor Pastel Guzellik Kampanya Paneli" tasarimi). Veri sayfa acildiktan sonra
     /isletmeyonetim/ikramhizmet/veri'den gelir (agir hesap sayfanin acilmasini bekletmez). Secili kampanya
     (listeden tiklanan; yoksa ilk aktif) ust bant, Aktif Kampanyalarim ve Kampanya Detaylari'nda gosterilir. --}}
@include('isletmeadmin.ikram._ortak')
@include('isletmeadmin.ikram._kampanya_modal')
<style>
.ik-sayfa { padding-bottom:36px; background:linear-gradient(180deg,#F3ECFC 0%,#F7F2FD 60%,#F2EAFB 100%); }
.ik-sayfa .ik-ic { max-width:1900px; }
.ik-sayfa a { cursor:pointer; text-decoration:none !important; }
.ikp-kart { background:#fff; border-radius:20px; box-shadow:0 8px 26px rgba(91,33,182,.07); border:1px solid #F0E8FB; }
.ikp-bas { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.ikp-bas h3 { flex:1; margin:0; font-size:17px; font-weight:700; color:#1E1037; }
.ik-sayfa a.ikp-link { font-size:13px; font-weight:700; color:#6D28D9 !important; white-space:nowrap; }
.ik-sayfa a.ikp-link:hover { color:#4C1D95 !important; }

/* 1) Ust bant */
.ikp-hero { position:relative; min-height:clamp(200px,15vw,330px); border-radius:22px; overflow:hidden; margin-bottom:16px; color:#fff;
    background:linear-gradient(100deg,#8A2A99 0%,#6A22A6 30%,#561C9F 55%,#4A1C8C 100%); box-shadow:0 14px 34px rgba(76,29,149,.20); }
.ikp-hero-gorsel { position:absolute; top:0; right:0; bottom:0; width:60%;
    background:url('{{ secure_asset('public/img/ikram/ikram_panel_hero.jpg') }}') right center/cover no-repeat;
    -webkit-mask-image:linear-gradient(90deg,transparent 0,#000 16%); mask-image:linear-gradient(90deg,transparent 0,#000 16%); }
.ikp-hero-yazi { position:relative; z-index:1; padding:clamp(18px,1.6vw,30px) clamp(20px,2.6vw,48px); max-width:58%; }
.ikp-rozet { display:inline-flex; align-items:center; gap:8px; padding:4px 12px 4px 4px; border-radius:16px; background:rgba(255,255,255,.18);
    font-size:12.5px; font-weight:600; }
.ikp-rozet i { width:24px; height:24px; border-radius:50%; background:linear-gradient(135deg,#FCD34D,#F59E0B); display:inline-flex;
    align-items:center; justify-content:center; font-size:12px; color:#fff; }
.ikp-hero h2 { margin:12px 0 8px; font-size:clamp(28px,2.4vw,46px); font-weight:700; line-height:1.08; color:#fff; letter-spacing:-.5px;
    overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
.ikp-hero h2.uzun { font-size:clamp(24px,1.9vw,38px); } .ikp-hero h2.cok-uzun { font-size:clamp(21px,1.6vw,32px); }
.ikp-hero p { margin:0; font-size:clamp(15px,1.1vw,20px); font-weight:500; line-height:1.3; color:#fff; white-space:pre-line; }
.ikp-hero-btn { display:flex; gap:10px; margin-top:16px; }
/* Kampanyalar ust bantta sirayla gecer; noktalar ile secilir */
.ikp-hero-yazi { transition:opacity .25s, transform .25s; } .ikp-hero-yazi.gecis { opacity:0; transform:translateY(8px); }
.ikp-noktalar { display:flex; align-items:center; gap:8px; margin-top:16px; }
.ikp-noktalar:empty { display:none; }
.ik-sayfa .ikp-noktalar a { display:block; flex-shrink:0; width:10px; height:10px; border-radius:5px; background:rgba(255,255,255,.45); transition:width .25s, background .25s; }
.ik-sayfa .ikp-noktalar a:hover { background:rgba(255,255,255,.75); }
.ik-sayfa .ikp-noktalar a.sec { width:28px; background:#fff; }
.ik-sayfa a.ikp-beyaz { display:inline-flex; align-items:center; gap:10px; height:40px; padding:0 22px; border-radius:22px; background:#fff;
    color:#2E1065 !important; font-weight:700; font-size:14px; }
.ik-sayfa a.ikp-beyaz:hover { background:#F5EDFF; }
.ik-sayfa a.ikp-cizgi { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 18px; border-radius:22px;
    border:1.5px solid rgba(255,255,255,.7); color:#fff !important; font-weight:700; font-size:14px; }
.ik-sayfa a.ikp-cizgi:hover { background:rgba(255,255,255,.14); }

/* 2) Ozet kartlari */
.ikp-ozet { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
.ikp-kpi { display:flex; align-items:center; gap:16px; padding:16px 18px; min-height:104px; }
.ikp-kpi-ikon { width:58px; height:58px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:24px; background:linear-gradient(135deg,#9333EA,#6D28D9); box-shadow:0 8px 18px rgba(109,40,217,.28); }
.ikp-kpi-ikon.sari { background:linear-gradient(135deg,#FCD34D,#F59E0B); box-shadow:0 8px 18px rgba(245,158,11,.3); }
.ikp-kpi-govde { flex:1; min-width:0; }
.ikp-kpi-govde small { display:block; font-size:13.5px; font-weight:600; color:#2E1065; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ikp-kpi-satir { display:flex; align-items:center; gap:10px; margin:4px 0 2px; }
.ikp-kpi-satir b { font-size:clamp(22px,1.6vw,30px); font-weight:700; color:#1E1037; line-height:1.1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; }
.ikp-kpi-satir b.ad { font-size:18px; line-height:1.2; white-space:normal; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
.ikp-kpi-alt { font-size:12.5px; color:#6B5A8E; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ikp-art { margin-left:auto; font-size:13px; font-weight:700; white-space:nowrap; }
.ikp-art.art { color:#16A34A; } .ikp-art.az { color:#DC2626; }
.ikp-hediye-cip { width:36px; height:32px; border-radius:10px; background:#F1E8FD; color:#8B5CF6; display:inline-flex; align-items:center;
    justify-content:center; font-size:17px; flex-shrink:0; }
.ikp-yuzde { margin-left:auto; padding:6px 16px; border-radius:16px; background:#EDE4FD; color:#6D28D9; font-size:19px; font-weight:700; white-space:nowrap; flex-shrink:0; }

/* 3) Aktif kampanyam + kampanya listesi */
.ikp-orta { display:grid; grid-template-columns:minmax(0,1.17fr) minmax(0,1fr); gap:16px; margin-bottom:16px; }
.ikp-orta > .ikp-kart, .ikp-alt > .ikp-kart { padding:18px; }
.ikp-nokta { width:10px; height:10px; border-radius:50%; background:#22C55E; flex-shrink:0; }
.ikp-one { display:flex; align-items:center; gap:22px; }
.ikp-karo { position:relative; width:252px; height:180px; flex-shrink:0; border-radius:18px; overflow:hidden; color:#fff; cursor:pointer;
    background:linear-gradient(135deg,#A230ED 0%,#6D1FD0 45%,#400C9D 100%); box-shadow:0 12px 26px rgba(91,33,182,.28); }
.ikp-karo:before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 80% 20%,rgba(255,255,255,.22),transparent 55%); }
.ikp-karo-yazi { position:relative; z-index:1; padding:24px 0 0 20px; font-weight:700; line-height:1.15; font-size:22px; max-width:56%; }
.ikp-karo-yazi span { display:block; font-size:40px; letter-spacing:-.5px; margin-bottom:4px; }
.ikp-karo-yazi em { font-style:normal; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word; }
.ikp-karo-yazi.uzun { font-size:18px; } .ikp-karo-yazi.uzun span { font-size:34px; }
.ikp-karo img { position:absolute; right:6px; bottom:4px; width:112px; -webkit-mask-image:radial-gradient(circle at 50% 55%,#000 58%,transparent 72%);
    mask-image:radial-gradient(circle at 50% 55%,#000 58%,transparent 72%); }
.ikp-karo .ik-cip { position:absolute; top:12px; right:12px; z-index:2; background:#DCFCE7; }
.ikp-one-sag { flex:1; min-width:0; }
.ikp-one-ust { display:flex; align-items:center; gap:16px; }
.ikp-halka { position:relative; width:132px; height:132px; flex-shrink:0; }
.ikp-halka svg { width:100%; height:100%; transform:rotate(-90deg); }
.ikp-halka-ic { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
.ikp-halka-ic b { font-size:26px; font-weight:700; color:#1E1037; line-height:1; }
.ikp-halka-ic small { margin-top:4px; font-size:11.5px; font-weight:600; color:#4C3A6E; line-height:1.2; }
.ikp-hak { display:flex; align-items:center; gap:12px; flex:1; min-width:0; }
.ikp-hak-ikon { width:56px; height:56px; border-radius:50%; flex-shrink:0; background:radial-gradient(circle,#FFF3D6,#FDE7B8); display:flex;
    align-items:center; justify-content:center; font-size:24px; color:#F59E0B; }
.ikp-hak small { display:block; font-size:13px; font-weight:700; color:#1E1037; }
.ikp-hak b { display:block; font-size:18px; font-weight:700; color:#5B21B6; line-height:1.25; }
.ikp-tarihler { display:flex; gap:24px; flex-wrap:wrap; margin-top:18px; font-size:13.5px; color:#2E1065; font-weight:500; }
.ikp-tarihler i { color:#8B5CF6; margin-right:6px; }

.ikp-sekme { display:inline-flex; background:#F5F0FC; border-radius:18px; padding:3px; }
.ik-sayfa .ikp-sekme a { padding:6px 14px; border-radius:15px; font-size:13px; font-weight:600; color:#4C3A6E !important; white-space:nowrap; }
.ik-sayfa .ikp-sekme a.sec { background:#fff; color:#5B21B6 !important; box-shadow:0 2px 8px rgba(91,33,182,.12); }
.ikp-liste { display:flex; flex-direction:column; gap:8px; max-height:262px; overflow-y:auto; margin:0 -4px; padding:0 4px; }
.ikp-satir { display:flex; align-items:center; gap:14px; padding:8px 10px; border-radius:16px; cursor:pointer; border:1.5px solid transparent; transition:.12s; }
.ikp-satir:hover { background:#FAF6FF; }
.ikp-satir.sec { background:#F7F1FF; border-color:#E4D4FB; }
.ikp-satir-foto { width:76px; height:68px; border-radius:12px; overflow:hidden; flex-shrink:0; }
.ikp-satir-foto img { width:100%; height:100%; object-fit:cover; }
.ikp-satir-govde { flex:1; min-width:0; }
.ikp-satir-govde h4 { display:flex; align-items:center; gap:10px; margin:0; font-size:15px; font-weight:700; color:#1E1037; }
.ikp-satir-govde h4 span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ikp-satir-govde h4 .ik-cip { flex-shrink:0; font-size:11px; padding:2px 9px; }
.ikp-satir-govde p { margin:3px 0 0; font-size:13px; color:#3B2A5C; }
.ikp-satir-ilerleme { display:flex; align-items:center; gap:10px; margin-top:4px; font-size:12.5px; color:#3B2A5C; }
.ikp-satir-ilerleme .ikp-cubuk { flex:1; max-width:360px; }
.ikp-cubuk { height:6px; border-radius:3px; background:#EDE4FB; overflow:hidden; }
.ikp-cubuk i { display:block; height:100%; border-radius:3px; background:linear-gradient(90deg,#7C3AED,#6D28D9); }
.ik-sayfa a.ikp-ok { width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center;
    background:#fff; border:1px solid #EDE4FB; color:#6D28D9 !important; box-shadow:0 2px 6px rgba(91,33,182,.08); }
.ik-sayfa a.ikp-ok:hover { background:#6D28D9; color:#fff !important; }

/* 4) Performans + kazananlar + detaylar */
.ikp-alt { display:grid; grid-template-columns:minmax(0,1.28fr) minmax(0,1fr) minmax(0,.98fr); gap:16px; }
.ikp-secim { position:relative; }
.ik-sayfa .ikp-secim > a { display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:16px; background:#F5F0FC;
    font-size:13px; font-weight:700; color:#1E1037 !important; }
.ikp-grafik { display:flex; align-items:flex-end; gap:12px; height:170px; padding-top:30px; border-bottom:1px solid #F0E8FB; }
.ikp-grafik-sutun { flex:1; min-width:0; height:100%; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; position:relative; }
.ikp-grafik-sutun small { font-size:12px; font-weight:600; color:#1E1037; white-space:nowrap; margin-bottom:6px; }
.ikp-grafik-sutun i { display:block; width:72%; max-width:44px; min-height:6px; border-radius:10px 10px 4px 4px; background:linear-gradient(180deg,#C4A5F5,#D8C2FA); }
.ikp-grafik-sutun.son i { background:linear-gradient(180deg,#8B5CF6,#6D28D9); border-radius:12px 12px 4px 4px; position:relative; }
.ikp-grafik-sutun.son i:before { content:''; position:absolute; top:-1px; left:50%; transform:translateX(-50%); border:8px solid transparent; border-top-color:#fff; }
.ikp-grafik-sutun.son small { padding:4px 10px; border-radius:10px; background:#fff; box-shadow:0 4px 14px rgba(91,33,182,.18); font-size:14px; font-weight:700; color:#2E1065; }
.ikp-aylar { display:flex; gap:12px; margin-top:8px; }
.ikp-aylar span { flex:1; text-align:center; font-size:12.5px; font-weight:600; color:#1E1037; }
.ikp-musteri { display:flex; align-items:center; gap:12px; padding:9px 0; }
.ikp-musteri + .ikp-musteri { border-top:1px solid #F5F0FC; }
.ikp-avatar { width:42px; height:42px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700;
    color:#6D28D9; background:linear-gradient(135deg,#F3E8FF,#E4D4FB); border:2px solid #fff; box-shadow:0 2px 8px rgba(91,33,182,.14); }
.ikp-musteri-ad { flex:1; min-width:0; }
.ik-sayfa .ikp-musteri-ad a { display:block; font-size:15px; font-weight:600; color:#1E1037 !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ik-sayfa .ikp-musteri-ad a:hover { color:#6D28D9 !important; }
.ikp-musteri-ad small { display:block; font-size:12px; color:#5B4A7E; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ikp-hediye-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; background:#F1E8FD; color:#7C3AED;
    font-size:13.5px; font-weight:700; white-space:nowrap; }
.ikp-hediye-btn.gri { background:#F3F4F6; color:#6B7280; }
.ikp-detay { width:100%; border-collapse:collapse; font-size:13.5px; }
.ikp-detay td { padding:7px 0; border-bottom:1px solid #F5F0FC; vertical-align:top; }
.ikp-detay td:first-child { color:#4C3A6E; width:44%; padding-right:10px; }
.ikp-detay td:last-child { color:#1E1037; font-weight:600; }
.ik-sayfa a.ikp-duzenle { display:flex; align-items:center; justify-content:center; gap:10px; height:44px; margin-top:16px; border-radius:22px;
    background:linear-gradient(90deg,#7C3AED,#5B21B6); color:#fff !important; font-weight:700; font-size:14px; box-shadow:0 8px 18px rgba(109,40,217,.25); }
.ik-sayfa a.ikp-duzenle:hover { filter:brightness(1.06); }
.ikp-ikincil { display:flex; justify-content:center; gap:18px; margin-top:10px; font-size:12.5px; }
.ik-sayfa .ikp-ikincil a { color:#6B5A8E !important; font-weight:600; }
.ik-sayfa .ikp-ikincil a:hover { color:#6D28D9 !important; } .ik-sayfa .ikp-ikincil a.kirmizi:hover { color:#DC2626 !important; }
.ik-sayfa a.ikp-bilgi { width:24px; height:24px; border-radius:50%; background:#7C3AED; color:#fff !important; display:inline-flex; align-items:center;
    justify-content:center; font-size:12px; flex-shrink:0; }
.ikp-bos { padding:26px 10px; text-align:center; font-size:13px; color:#6B5A8E; line-height:1.55; }
.ikp-iskelet { background:linear-gradient(90deg,#F3ECFC 25%,#FAF6FF 50%,#F3ECFC 75%); background-size:200% 100%; animation:ikpKay 1.2s infinite; border-radius:14px; }
@keyframes ikpKay { to { background-position:-200% 0; } }

@media (max-width:1399px) { .ikp-ozet { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width:1199px) {
    .ikp-orta { grid-template-columns:minmax(0,1fr); }
    .ikp-alt { grid-template-columns:repeat(2,minmax(0,1fr)); } .ikp-alt > :first-child { grid-column:1 / -1; }
    .ikp-hero-yazi { max-width:66%; }
}
@media (max-width:900px) { .ikp-hero-gorsel { width:72%; opacity:.55; } .ikp-hero-yazi { max-width:80%; } }
@media (max-width:700px) {
    .ikp-hero { height:auto; } .ikp-hero-gorsel { width:100%; opacity:.18; }
    .ikp-hero-btn { flex-wrap:wrap; }
    .ikp-alt > :first-child { grid-column:auto; }
    .ikp-sekme { width:100%; } .ik-sayfa .ikp-sekme a { flex:1; text-align:center; padding:6px 6px; font-size:12px; }
    .ikp-satir { gap:10px; padding:8px 6px; } .ikp-satir-foto { width:58px; height:56px; }
    .ikp-satir-govde h4 { flex-wrap:wrap; gap:4px 8px; font-size:14px; }
    .ikp-halka { width:116px; height:116px; }
    .ikp-liste { max-height:none; }
    .ikp-grafik { gap:6px; } .ikp-aylar { gap:6px; } .ikp-grafik-sutun small { font-size:10.5px; }
    .ikp-hero-yazi { max-width:none; padding:20px; } .ikp-hero h2 { font-size:26px; white-space:normal; } .ikp-hero p { font-size:15px; }
    .ikp-ozet, .ikp-alt { grid-template-columns:minmax(0,1fr); }
    .ikp-one { flex-direction:column; align-items:stretch; } .ikp-karo { width:100%; }
    .ikp-one-ust { flex-wrap:wrap; }
    .ikp-orta > .ikp-kart .ikp-bas { flex-wrap:wrap; }
}
</style>

<div class="ik-sayfa">
    <div class="ik-ic">
        {{-- 1) Ust bant: secili kampanya --}}
        <div class="ikp-hero">
            <div class="ikp-hero-gorsel"></div>
            <div class="ikp-hero-yazi" id="ikp-hero-yazi">
                <span class="ikp-rozet"><i class="fa fa-star"></i> Sadakat Kampanyası</span>
                <h2 id="ikp-hero-baslik">İkram Hizmet</h2>
                <p id="ikp-hero-alt">Kampanyalarınız yükleniyor…</p>
                <div class="ikp-hero-btn">
                    <a class="ikp-beyaz" id="ikp-hero-detay" style="display:none">Detayları Gör <i class="fa fa-arrow-right"></i></a>
                    <a class="ikp-cizgi" id="ik-yeni"><i class="fa fa-plus"></i> Yeni Kampanya</a>
                </div>
                <div class="ikp-noktalar" id="ikp-noktalar"></div>
            </div>
        </div>

        {{-- 2) Ozet --}}
        <div class="ikp-ozet" id="ikp-ozet">
            <div class="ikp-kart ikp-kpi ikp-iskelet"></div><div class="ikp-kart ikp-kpi ikp-iskelet"></div>
            <div class="ikp-kart ikp-kpi ikp-iskelet"></div><div class="ikp-kart ikp-kpi ikp-iskelet"></div>
        </div>

        {{-- 3) Aktif kampanyam + liste --}}
        <div class="ikp-orta">
            <div class="ikp-kart">
                <div class="ikp-bas">
                    <span class="ikp-nokta"></span>
                    <h3>Aktif Kampanyalarım</h3>
                    <a class="ikp-link" id="ikp-tumu">Tümünü Gör <i class="fa fa-arrow-right"></i></a>
                </div>
                <div id="ikp-one"><div class="ikp-iskelet" style="height:180px"></div></div>
            </div>
            <div class="ikp-kart">
                <div class="ikp-bas">
                    <h3>Kampanya Listesi</h3>
                    <div class="ikp-sekme" id="ikp-sekme"></div>
                </div>
                <div class="ikp-liste" id="ikp-liste"><div class="ikp-iskelet" style="height:180px"></div></div>
            </div>
        </div>

        {{-- 4) Performans + kazananlar + detaylar --}}
        <div class="ikp-alt">
            <div class="ikp-kart">
                <div class="ikp-bas">
                    <h3>Kampanya Performansı</h3>
                    <div class="ikp-secim ik-menu">
                        <a class="ik-menu-ac"><span id="ikp-ay-yazi">Son 6 Ay</span> <i class="fa fa-chevron-down" style="font-size:10px"></i></a>
                        <div class="ik-menu-liste" style="min-width:140px"><a data-ay="6">Son 6 Ay</a><a data-ay="12">Son 12 Ay</a></div>
                    </div>
                </div>
                <div id="ikp-grafik"><div class="ikp-iskelet" style="height:190px"></div></div>
            </div>
            <div class="ikp-kart">
                <div class="ikp-bas">
                    <i class="fa fa-trophy" style="color:#F59E0B;font-size:20px"></i>
                    <h3>Kazanan Müşteriler</h3>
                    <a class="ikp-link" id="ik-rapor">Tüm Liste <i class="fa fa-arrow-right"></i></a>
                </div>
                <div id="ikp-kazananlar"><div class="ikp-iskelet" style="height:150px"></div></div>
            </div>
            <div class="ikp-kart">
                <div class="ikp-bas">
                    <a class="ikp-bilgi" id="ik-bilgi" title="Nasıl çalışır?"><i class="fa fa-info"></i></a>
                    <h3>Kampanya Detayları</h3>
                </div>
                <div id="ikp-detay"><div class="ikp-iskelet" style="height:150px"></div></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var DONEM = 30, filtre = 'aktif', secId = null, ayAdet = 6;
    var kurallar = [], ozet = null, musteriler = [], yuklendi = false;
    var AYLAR = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
    var $ = function (id) { return document.getElementById(id); };
    var esc = IK.esc;

    $('ik-yeni').addEventListener('click', function () { IKForm.ac(null, yukle); });
    $('ik-rapor').addEventListener('click', function () { location.href = IK.u('ikramhizmet/rapor', { donem: DONEM }); });
    $('ik-bilgi').addEventListener('click', function () {
        IK.bilgi('İkram Hizmet nasıl çalışır?',
            '• Bir hizmeti belirlediğiniz sayıda ücretli alan müşteri o hizmetten ikram kazanır (ör. 5+1).<br>' +
            '• Sayım "Geldi" işaretli ve ödemesi alınmış randevulara göre yapılır; paket seansları sayılmaz.<br>' +
            '• Müşteri ikramlarını uygulamasında görür ve ne zaman kullanacağına kendisi karar verir; kullanmak istediğinde tahsilat ekranından işlenir.<br><br>' +
            '<b>Rakamlar (son 30 gün):</b><br>' +
            '• Toplam Kazanç: kampanyadaki hizmetlerin satış tutarı (kampanya tarihleri içinde).<br>' +
            '• Kampanya Katılımcısı: kampanyalı hizmeti satın alan müşteri sayısı.<br>' +
            '• Toplam Hediye Hakkı: kazanılmış, henüz kullanılmamış ikramlar.<br>' +
            '• En Çok Tercih Edilen: katılımcısı en çok olan kampanya ve katılımcıların yüzde kaçının onda olduğu.');
    });
    $('ikp-hero-detay').addEventListener('click', function () { var k = heroKamp(); if (k && !k.tanitim) detayaGit(k.id); });
    // Tumunu Gor: kartta tum aktif kampanyalar alt alta acilir / kapanir
    var tumuAcik = false;
    $('ikp-tumu').addEventListener('click', function () { tumuAcik = !tumuAcik; oneCiz(); });
    function aktifler() { return kurallar.filter(function (k) { var d = IK.durum(k); return d === 'aktif' || d === 'baslayacak'; }); }
    function detayaGit(id) { location.href = IK.u('ikramhizmet/kampanya/' + id, { donem: DONEM }); }

    function filtreUyar(k, f) {
        var d = IK.durum(k);
        if (f === 'aktif') return d === 'aktif' || d === 'baslayacak';
        if (f === 'pasif') return d === 'pasif' || d === 'bitti';
        return true;
    }
    function secili() { return kurallar.filter(function (k) { return k.id === secId; })[0] || null; }
    function hz(k) { return k.hizmet_adi || 'Hizmet'; }
    function kampAdi(k) { return (k.gereken_adet || 0) + ' + ' + (k.ucretsiz_adet || 1) + ' ' + hz(k) + ' Kampanyası'; }
    function kampOzet(k) { var kk = (ozet && ozet.kampanyalar) || {}; return kk[k.id] || kk[String(k.id)] || {}; }
    function kampMusteri(k) { return musteriler.filter(function (m) { return Number(m.kural_id) === Number(k.id); }); }
    function artis(simdi, once) {
        if (!once) return '';
        var y = (simdi - once) / once * 100, ok = y >= 0;
        return '<span class="ikp-art ' + (ok ? 'art' : 'az') + '"><i class="fa fa-arrow-' + (ok ? 'up' : 'down') + '"></i> %' + Math.abs(Math.round(y)) + '</span>';
    }
    /** Kampanyanin gecen suresi (%) ve kalan gun yazisi; bitisi yoksa null */
    function sure(k) {
        if (!k.baslangic || !k.bitis_tarihi) return null;
        var bas = new Date(String(k.baslangic).slice(0, 10) + 'T00:00:00'), bit = new Date(k.bitis_tarihi + 'T23:59:59'), simdi = new Date();
        var kalan = Math.floor((bit - simdi) / 86400000);
        return { yuzde: Math.max(0, Math.min(100, (simdi - bas) / Math.max(1, bit - bas) * 100)), kalan: kalan };
    }

    /** Ust bantta donen kampanyalar: aktif + baslayacak (hic yoksa hepsi) + sonda "yeni kampanya" tanitimi */
    var TANITIM = { tanitim: true, id: null };
    var heroIdx = 0, heroZaman = null, heroDur = false;
    function heroListe() {
        var l = kurallar.filter(function (k) { var d = IK.durum(k); return d === 'aktif' || d === 'baslayacak'; });
        l = l.length ? l : kurallar;
        return l.length ? l.concat([TANITIM]) : l;
    }
    function heroKamp() { var l = heroListe(); return l.length ? l[heroIdx % l.length] : null; }
    function heroCiz() {
        var k = heroKamp(), l = heroListe();
        $('ikp-hero-baslik').className = '';
        $('ikp-hero-detay').style.display = k && !k.tanitim ? '' : 'none';
        $('ikp-noktalar').innerHTML = l.length > 1 ? l.map(function (x, i) {
            return '<a data-hero="' + i + '" class="' + (i === heroIdx % l.length ? 'sec' : '') + '" title="' + esc(x.tanitim ? 'Yeni kampanya' : kampAdi(x)) + '"></a>';
        }).join('') : '';
        if (!k) {
            $('ikp-hero-baslik').textContent = yuklendi ? 'İlk kampanyanızı oluşturun' : 'İkram Hizmet';
            $('ikp-hero-alt').textContent = yuklendi ? 'Bir hizmeti belirlediğiniz sayıda alan\nmüşterinize hediye hizmet verin.' : 'Kampanyalarınız yükleniyor…';
            return;
        }
        if (k.tanitim) {
            $('ikp-hero-baslik').textContent = 'Yeni bir ikram kampanyası açın';
            $('ikp-hero-baslik').title = '';
            $('ikp-hero-alt').textContent = 'Başka bir hizmet için de "X alana 1 hediye" verin,\nmüşterileriniz daha sık gelsin.';
            return;
        }
        var hb = $('ikp-hero-baslik'), uz = kampAdi(k).length;
        hb.className = uz > 34 ? 'cok-uzun' : uz > 24 ? 'uzun' : '';
        hb.textContent = kampAdi(k);
        hb.title = kampAdi(k);
        $('ikp-hero-alt').textContent = (k.gereken_adet || 0) + ' ' + hz(k) + ' hizmeti alan müşterilerimize\n' + (k.ucretsiz_adet || 1) + ' ' + hz(k) + ' hizmeti tamamen ücretsiz!';
    }
    /** Yazi solup yeni kampanya ile geri gelir */
    function heroGec(i) {
        var l = heroListe();
        if (l.length < 2) return;
        var yazi = $('ikp-hero-yazi');
        yazi.classList.add('gecis');
        setTimeout(function () { heroIdx = (i + l.length) % l.length; heroCiz(); yazi.classList.remove('gecis'); }, 250);
    }
    function heroSayac() {
        clearInterval(heroZaman);
        heroZaman = setInterval(function () { if (!heroDur && !document.hidden) heroGec(heroIdx + 1); }, 6000);
    }
    document.querySelector('.ikp-hero').addEventListener('mouseenter', function () { heroDur = true; });
    document.querySelector('.ikp-hero').addEventListener('mouseleave', function () { heroDur = false; });
    function kpi(ikon, sinif, baslik, satir, alt) {
        return '<div class="ikp-kart ikp-kpi"><span class="ikp-kpi-ikon ' + sinif + '"><i class="fa fa-' + ikon + '"></i></span>' +
            '<div class="ikp-kpi-govde"><small>' + baslik + '</small><div class="ikp-kpi-satir">' + satir + '</div><div class="ikp-kpi-alt">' + alt + '</div></div></div>';
    }
    function ozetCiz() {
        var o = ozet || {};
        var hak = musteriler.reduce(function (t, m) { return t + (Number(m.hak) || 0); }, 0);
        var bekleyen = musteriler.filter(function (m) { return m.hak > 0; }).length;
        var enIyi = null, katToplam = 0;
        kurallar.forEach(function (k) {
            var n = Number(kampOzet(k).katilimci || 0);
            katToplam += n;
            if (n > 0 && (!enIyi || n > enIyi.n)) enIyi = { k: k, n: n };
        });
        $('ikp-ozet').innerHTML =
            kpi('gift', '', 'Toplam Kazanç', '<b class="ik-sayi">' + IK.tl(o.toplam) + ' TL</b>' + artis(Number(o.toplam || 0), Number(o.onceki || 0)),
                'Son 30 gün &nbsp;•&nbsp; Önceki 30 güne göre') +
            kpi('users', '', 'Kampanya Katılımcısı', '<b class="ik-sayi">' + IK.tl(o.katilimci) + '</b>' + artis(Number(o.katilimci || 0), Number(o.onceki_katilimci || 0)),
                'Son 30 günde hizmeti alan müşteri') +
            kpi('tag', '', 'Toplam Hediye Hakkı', '<b class="ik-sayi">' + IK.tl(hak) + '</b><span class="ikp-hediye-cip"><i class="fa fa-gift"></i></span>',
                bekleyen ? bekleyen + ' müşteri hediyesini bekliyor' : 'Bekleyen hediye yok') +
            kpi('star', 'sari', 'En Çok Tercih Edilen Kampanya',
                enIyi ? '<b class="ad" title="' + esc(kampAdi(enIyi.k)) + '">' + (enIyi.k.gereken_adet || 0) + ' + ' + (enIyi.k.ucretsiz_adet || 1) + ' ' + esc(hz(enIyi.k)) + '</b>' +
                    '<span class="ikp-yuzde">%' + Math.round(enIyi.n / Math.max(1, katToplam) * 100) + '</span>'
                    : '<b>—</b>',
                enIyi ? 'Katılımcıların payı, son 30 gün' : 'Son 30 günde katılım yok');
    }

    function oneCiz() {
        var k = secili(), kap = $('ikp-one'), akt = aktifler();
        $('ikp-tumu').style.display = akt.length > 1 ? '' : 'none';
        $('ikp-tumu').innerHTML = tumuAcik && akt.length > 1 ? 'Daha az göster <i class="fa fa-chevron-up"></i>' : 'Tümünü Gör (' + akt.length + ') <i class="fa fa-arrow-right"></i>';
        if (tumuAcik && akt.length > 1) {
            kap.innerHTML = akt.map(oneBlok).join('<div style="height:1px;background:#F1EAFB;margin:16px 0"></div>');
            return;
        }
        if (!k) {
            kap.innerHTML = '<div class="ikp-bos">' + (kurallar.length ? 'Aktif kampanya yok. Listeden bir kampanya seçin ya da yenisini açın.'
                : 'Henüz kampanya yok.<br>Üstteki "Yeni Kampanya" ile ilkini oluşturun.') + '</div>';
            return;
        }
        kap.innerHTML = oneBlok(k);
    }
    function oneBlok(k) {
        var d = IK.durum(k), kul = 0, kaz = 0, hazir = 0;
        kampMusteri(k).forEach(function (m) { kul += Number(m.kullanilan) || 0; kaz += Number(m.kazanilan) || 0; if (m.hak > 0) hazir++; });
        var oran = kaz ? kul / kaz : 0, cevre = 2 * Math.PI * 56;
        return '<div class="ikp-one">' +
            '<div class="ikp-karo" data-detay="' + k.id + '" title="Kampanya detayı">' +
                '<span class="ik-cip ' + d + '">' + IK.DURUM_YAZI[d] + '</span>' +
                '<div class="ikp-karo-yazi' + (hz(k).length > 10 ? ' uzun' : '') + '"><span>' + (k.gereken_adet || 0) + ' + ' + (k.ucretsiz_adet || 1) + '</span><em>' + esc(hz(k)) + '</em>Kampanyası</div>' +
                '<img src="' + IK.GORSEL + 'ikram_panel_kutu.png" alt="">' +
            '</div>' +
            '<div class="ikp-one-sag"><div class="ikp-one-ust">' +
                '<div class="ikp-halka"><svg viewBox="0 0 132 132"><defs><linearGradient id="ikpHg" x1="0" y1="0" x2="1" y2="1">' +
                    '<stop offset="0" stop-color="#22C55E"/><stop offset=".5" stop-color="#8B5CF6"/><stop offset="1" stop-color="#6D28D9"/></linearGradient></defs>' +
                    '<circle cx="66" cy="66" r="56" fill="none" stroke="#F1EAFB" stroke-width="10"/>' +
                    '<circle cx="66" cy="66" r="56" fill="none" stroke="url(#ikpHg)" stroke-width="10" stroke-linecap="round" stroke-dasharray="' +
                        (cevre * oran).toFixed(1) + ' ' + cevre.toFixed(1) + '"/></svg>' +
                    '<div class="ikp-halka-ic"><b class="ik-sayi">' + kul + ' / ' + kaz + '</b><small>Kullanılan<br>Hediye</small></div></div>' +
                '<div class="ikp-hak"><span class="ikp-hak-ikon"><i class="fa fa-gift"></i></span><div><small>Hediye Hakkı</small><b>' +
                    (hazir ? hazir + ' müşteride hazır' : 'Bekleyen yok') + '</b></div></div>' +
            '</div>' +
            '<div class="ikp-tarihler"><span><i class="fa fa-calendar"></i>Başlangıç: ' + (k.baslangic ? IK.tarih(k.baslangic) : 'Açılınca') + '</span>' +
                '<span><i class="fa fa-calendar"></i>Bitiş: ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz') + '</span></div>' +
            '</div></div>';
    }

    function listeCiz() {
        var say = function (f) { return kurallar.filter(function (k) { return filtreUyar(k, f); }).length; };
        $('ikp-sekme').innerHTML = [['aktif', 'Aktif (' + say('aktif') + ')'], ['pasif', 'Pasif (' + say('pasif') + ')'], ['tumu', 'Tüm Kampanyalar']].map(function (f) {
            return '<a data-filtre="' + f[0] + '" class="' + (f[0] === filtre ? 'sec' : '') + '">' + f[1] + '</a>';
        }).join('');
        var liste = kurallar.filter(function (k) { return filtreUyar(k, filtre); });
        if (!liste.length) {
            $('ikp-liste').innerHTML = '<div class="ikp-bos">' + (kurallar.length ? 'Bu sekmede kampanya yok.' : 'Henüz kampanya yok.') + '</div>';
            return;
        }
        $('ikp-liste').innerHTML = liste.map(function (k) {
            var d = IK.durum(k), s = sure(k), ko = kampOzet(k), kat = Number(ko.katilimci || 0);
            var ilerleme = d === 'bitti' ? 'Bitti · ' + IK.tarih(k.bitis_tarihi)
                : d === 'pasif' ? 'Kapalı'
                : s ? (s.kalan < 1 ? 'Son gün' : s.kalan + ' gün kaldı') : 'Süresiz';
            return '<div class="ikp-satir' + (k.id === secId ? ' sec' : '') + '" data-sec="' + k.id + '">' +
                '<div class="ikp-satir-foto">' + IK.gorselImg(k) + '</div>' +
                '<div class="ikp-satir-govde">' +
                    '<h4><span>' + esc(kampAdi(k)) + '</span><span class="ik-cip ' + d + '">' + IK.DURUM_YAZI[d] + '</span></h4>' +
                    '<p>' + (k.gereken_adet || 0) + ' seans al, ' + (k.ucretsiz_adet || 1) + ' seans hediye kazan!</p>' +
                    '<div class="ikp-satir-ilerleme"><span>' + ilerleme + (kat ? ' · ' + kat + ' katılımcı' : '') + '</span>' +
                        (s && d === 'aktif' ? '<div class="ikp-cubuk"><i style="width:' + s.yuzde.toFixed(1) + '%"></i></div>' : '') + '</div>' +
                '</div>' +
                '<a class="ikp-ok" data-detay="' + k.id + '" title="Kampanya detayı"><i class="fa fa-chevron-right"></i></a>' +
            '</div>';
        }).join('');
    }

    function grafikCiz() {
        var aylik = ((ozet && ozet.aylik) || []).slice(-ayAdet);
        $('ikp-ay-yazi').textContent = 'Son ' + ayAdet + ' Ay';
        if (!aylik.some(function (a) { return a.tutar > 0; })) { $('ikp-grafik').innerHTML = '<div class="ikp-bos" style="padding:60px 10px"><i class="fa fa-bar-chart" style="font-size:28px;color:#C4A5F5;display:block;margin-bottom:8px"></i>Son ' + ayAdet + ' ayda kampanyalı hizmet satışı yok.<br>Satış oldukça aylık grafik burada oluşur.</div>'; return; }
        var maks = Math.max.apply(null, aylik.map(function (a) { return a.tutar; }).concat([1]));
        var kisa = ayAdet > 6;
        $('ikp-grafik').innerHTML = '<div class="ikp-grafik">' + aylik.map(function (a, i) {
            var son = i === aylik.length - 1;
            return '<div class="ikp-grafik-sutun' + (son ? ' son' : '') + '"><small class="ik-sayi">' + (kisa && !son ? IK.tl(a.tutar / 1000) + 'B' : IK.tl(a.tutar) + ' TL') + '</small>' +
                '<i style="height:' + Math.max(4, a.tutar / maks * 100) * 0.78 + '%"></i></div>';
        }).join('') + '</div><div class="ikp-aylar">' + aylik.map(function (a) {
            return '<span>' + AYLAR[Number(String(a.ay).slice(5, 7)) - 1] + '</span>';
        }).join('') + '</div>';
    }

    function kazananCiz() {
        var liste = musteriler.slice().sort(function (a, b) { return (b.hak > 0) - (a.hak > 0) || b.kazanilan - a.kazanilan; }).slice(0, 4);
        if (!liste.length) { $('ikp-kazananlar').innerHTML = '<div class="ikp-bos">Henüz ikram kazanan müşteri yok.<br>Kampanya adedini tamamlayanlar burada görünür.</div>'; return; }
        $('ikp-kazananlar').innerHTML = liste.map(function (m) {
            var bas = String(m.ad || '?').trim().split(/\s+/).map(function (p) { return p.charAt(0); }).slice(0, 2).join('').toLocaleUpperCase('tr');
            return '<div class="ikp-musteri"><span class="ikp-avatar">' + esc(bas) + '</span>' +
                '<div class="ikp-musteri-ad"><a href="' + IK.u('musteridetay/' + m.user_id) + '" target="_blank" rel="noopener" title="Müşteri detayı (yeni sekme)">' + esc(m.ad) + '</a>' +
                '<small>' + (Number(m.ucretli) || 0) + '. ' + esc(m.hizmet_adi) + ' hizmetini tamamladı</small></div>' +
                (m.hak > 0 ? '<span class="ikp-hediye-btn" title="Kullanılabilir ikram: ' + m.hak + '"><i class="fa fa-gift"></i> Hediye' + (m.hak > 1 ? ' ×' + m.hak : '') + '</span>'
                    : '<span class="ikp-hediye-btn gri">Kullandı</span>') + '</div>';
        }).join('');
    }

    function detayCiz() {
        var k = secili();
        if (!k) { $('ikp-detay').innerHTML = '<div class="ikp-bos">Listeden bir kampanya seçin.</div>'; return; }
        var acik = Number(k.aktif) === 1, d = IK.durum(k);
        $('ikp-detay').innerHTML = '<table class="ikp-detay">' +
            '<tr><td>Kampanya Türü</td><td>' + (k.gereken_adet || 0) + '+' + (k.ucretsiz_adet || 1) + ' Sadakat Kampanyası</td></tr>' +
            '<tr><td>Geçerlilik Süresi</td><td>' + (k.baslangic ? IK.tarih(k.baslangic) : 'Açılınca') + ' – ' + (k.bitis_tarihi ? IK.tarih(k.bitis_tarihi) : 'Süresiz') + '</td></tr>' +
            '<tr><td>Dahil Hizmetler</td><td>' + esc(hz(k)) + '</td></tr>' +
            '<tr><td>Hediye Hizmet</td><td>' + (k.ucretsiz_adet || 1) + ' Seans ' + esc(hz(k)) + ' (Ücretsiz)</td></tr>' +
            (k.gecerlilik ? '<tr><td>Hediye Kullanım Süresi</td><td>' + esc(k.gecerlilik) + '</td></tr>' : '') +
            '</table>' +
            '<a class="ikp-duzenle" data-islem="duzenle"><i class="fa fa-pencil"></i> Kampanyayı Düzenle</a>' +
            '<div class="ikp-ikincil">' +
                (d === 'bitti' ? '' : '<a data-islem="ackapat"><i class="fa fa-' + (acik ? 'pause' : 'play') + '"></i> ' + (acik ? 'Kampanyayı kapat' : 'Kampanyayı aç') + '</a>') +
                '<a data-islem="sil" class="kirmizi"><i class="fa fa-trash-o"></i> Sil</a></div>';
    }

    function hepsiniCiz() { heroCiz(); ozetCiz(); oneCiz(); listeCiz(); grafikCiz(); kazananCiz(); detayCiz(); }
    function sec(id) {
        secId = id;
        var i = heroListe().map(function (k) { return k.id; }).indexOf(id);
        if (i >= 0 && i !== heroIdx) { heroGec(i); heroSayac(); }
        oneCiz(); listeCiz(); detayCiz();
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        var f = t.closest('[data-filtre]');
        if (f) { filtre = f.getAttribute('data-filtre'); listeCiz(); return; }
        var ay = t.closest('[data-ay]');
        if (ay) { ayAdet = Number(ay.getAttribute('data-ay')); grafikCiz(); return; }
        var h = t.closest('[data-hero]');
        if (h) { heroGec(Number(h.getAttribute('data-hero'))); heroSayac(); return; }
        var dt = t.closest('[data-detay]');
        if (dt) { detayaGit(dt.getAttribute('data-detay')); return; }
        var s = t.closest('[data-sec]');
        if (s) { sec(Number(s.getAttribute('data-sec'))); return; }
    });
    $('ikp-detay').addEventListener('click', function (e) {
        var islem = e.target.closest('[data-islem]'), k = secili();
        if (!islem || !k) return;
        var tur = islem.getAttribute('data-islem');
        if (tur === 'duzenle') { IKForm.ac(k, yukle); return; }
        if (tur === 'ackapat') {
            IK.acKapat(k).then(function (r) {
                if (r && r.success) { IK.toast(Number(k.aktif) === 1 ? 'Kampanya kapatıldı' : 'Kampanya açıldı'); yukle(); }
                else IK.toast((r && r.message) || 'Değiştirilemedi', true);
            }).catch(function () { IK.toast('Değiştirilemedi', true); });
            return;
        }
        if (tur === 'sil') {
            IK.sil(k).then(function (r) {
                if (r === null) return;
                if (r && r.success) { IK.toast('Kampanya silindi'); secId = null; yukle(); }
                else IK.toast((r && r.message) || 'Silinemedi', true);
            }).catch(function () { IK.toast('Silinemedi', true); });
        }
    });

    var istek = 0;
    function yukle() {
        var no = ++istek;
        IK.getJson(IK.u('ikramhizmet/veri', { donem: DONEM, musteriler: 1 })).then(function (r) {
            if (!r || no !== istek) return;
            kurallar = (r.kurallar || []).map(function (k) { k.id = Number(k.id); return k; });
            ozet = r.ozet || null;
            musteriler = r.musteriler || [];
            yuklendi = true;
            // Secim korunur; yoksa ilk aktif, o da yoksa ilk kampanya
            if (!secili()) {
                var aktif = kurallar.filter(function (k) { return IK.durum(k) === 'aktif'; })[0];
                secId = (aktif || kurallar[0] || {}).id || null;
            }
            if (!kurallar.some(function (k) { return filtreUyar(k, filtre); })) filtre = 'tumu';
            hepsiniCiz();
            heroSayac();
        }).catch(function () {
            if (no !== istek) return;
            if (!yuklendi) { yuklendi = true; hepsiniCiz(); }
            IK.toast('Veriler yüklenemedi', true);
        });
    }

    try { if (sessionStorage.getItem('ik_mesaj')) { IK.toast(sessionStorage.getItem('ik_mesaj')); sessionStorage.removeItem('ik_mesaj'); } } catch (e) {}
    yukle();
})();
</script>
@endsection
