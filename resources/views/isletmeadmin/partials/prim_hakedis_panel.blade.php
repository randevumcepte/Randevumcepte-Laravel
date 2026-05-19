@php
  // Maas tutar gor yetki kontrolu — kapaliysa rakamlar **** ile maskele
  $_maasGor = true;
  $_odemeYap = true;
  try {
    $_uid = \Auth::guard('isletmeyonetim')->user()->id ?? null;
    if ($_uid) {
      $_maasGor = \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_uid, $isletme->id, 'personel.maas_tutar_gor');
      $_odemeYap = \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_uid, $isletme->id, 'personel.odeme_yap');
    }
  } catch (\Throwable $e) { $_maasGor = true; $_odemeYap = true; }
  $_fmt = function($v) use ($_maasGor){ return $_maasGor ? number_format($v,2,',','.') : '****'; };
  $aylar = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
  $toplamMaas = array_sum(array_column($rapor,'maas'));
  $toplamPrim = array_sum(array_column($rapor,'prim_toplam'));
  $toplamBonus = array_sum(array_column($rapor,'bonus'));
  $toplamKesinti = array_sum(array_column($rapor,'kesinti'));
  $toplamNet = array_sum(array_column($rapor,'net_hakedis'));
  $toplamOdenen = array_sum(array_column($rapor,'odenen_toplam'));
  $toplamBekleyen = array_sum(array_column($rapor,'kalan'));
  // Tip bazli odenen toplamlari (Maas / Prim / Avans-Diger)
  $toplamOdenenMaas  = array_sum(array_column($rapor,'odenen_maas'));
  $toplamOdenenPrim  = array_sum(array_column($rapor,'odenen_prim'));
  $toplamOdenenDiger = array_sum(array_column($rapor,'odenen_diger'));
  // Kart bazinda kalan (toplam - odenen, asagida 0'dan kucukse 0)
  $toplamKalanMaas = max(0, $toplamMaas - $toplamOdenenMaas);
  $toplamKalanPrim = max(0, $toplamPrim - $toplamOdenenPrim);
  $odemeYuzde = $toplamNet > 0 ? min(100, round(($toplamOdenen / $toplamNet) * 100, 1)) : 0;
  $bekleyenSayi = count(array_filter($rapor, fn($r) => $r['durum']==='bekliyor' || $r['durum']==='kismi'));
  $tamSayi = count(array_filter($rapor, fn($r) => $r['durum']==='tam' || $r['durum']==='fazla'));
@endphp
<style>
  /* ============ Marka Renk Degiskenleri ============ */
  :root{
    --rmc-purple-1:#5C008E;
    --rmc-purple-2:#7B2FB8;
    --rmc-purple-3:#9D5DC8;
    --rmc-purple-soft:#B88ED8;
    --rmc-purple-bg:#f7f1fb;
    --rmc-grad: linear-gradient(135deg,#5C008E 0%,#7B2FB8 50%,#9D5DC8 100%);
    --rmc-grad-soft: linear-gradient(135deg,#9D5DC8 0%,#B88ED8 100%);
    --rmc-success:#10b981;
    --rmc-success-bg:#ecfdf5;
    --rmc-danger:#ef4444;
    --rmc-danger-bg:#fef2f2;
    --rmc-text:#2d1b3f;
    --rmc-muted:#8a8295;
    --rmc-border:#ece6f2;
    --rmc-shadow-sm: 0 2px 8px rgba(92,0,142,.08);
    --rmc-shadow-md: 0 8px 24px rgba(92,0,142,.12);
    --rmc-shadow-lg: 0 18px 50px rgba(92,0,142,.18);
  }

  /* ============ Sayfa Arka Plan ============ */
  body{ background:#fbfafc; }
  .min-height-200px{ background: transparent; }

  /* ============ Hero / Page Header ============ */
  .pr-hero{
    background: var(--rmc-grad);
    border-radius: 24px;
    padding: 28px 32px;
    color:#fff;
    position:relative;
    overflow:hidden;
    margin-bottom:22px;
    box-shadow: var(--rmc-shadow-md);
  }
  .pr-hero::before{
    content:''; position:absolute; top:-80px; right:-80px; width:280px; height:280px;
    background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
    border-radius:50%;
  }
  .pr-hero::after{
    content:''; position:absolute; bottom:-60px; left:-60px; width:220px; height:220px;
    background: radial-gradient(circle, rgba(184,142,216,.25) 0%, transparent 70%);
    border-radius:50%;
  }
  .pr-hero__inner{ position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; }
  .pr-hero__title{ display:flex; align-items:center; gap:14px; }
  .pr-hero__icon{
    width:54px; height:54px; border-radius:14px; background:rgba(255,255,255,.18);
    display:flex; align-items:center; justify-content:center; font-size:22px; backdrop-filter: blur(6px);
  }
  .pr-hero__title h1{ font-size:24px; font-weight:700; margin:0; color:#fff; letter-spacing:-.3px; }
  .pr-hero__title p{ margin:4px 0 0; color:rgba(255,255,255,.82); font-size:13px; }
  .pr-hero__period{
    background:rgba(255,255,255,.18); padding:10px 18px; border-radius:30px;
    font-size:13px; font-weight:600; backdrop-filter: blur(8px); display:inline-flex; align-items:center; gap:8px;
  }

  /* ============ Filtre Bar ============ */
  .pr-filter{
    background:#fff; border-radius:18px; padding:18px 22px; margin-bottom:22px;
    box-shadow: var(--rmc-shadow-sm); border:1px solid var(--rmc-border);
    display:flex; align-items:end; gap:18px; flex-wrap:wrap;
  }
  .pr-filter label{ font-size:11px; font-weight:700; color:var(--rmc-muted); letter-spacing:.5px; text-transform:uppercase; margin-bottom:6px; display:block; }
  .pr-filter select{
    border:2px solid var(--rmc-border); border-radius:12px; padding:10px 14px; font-weight:600;
    color:var(--rmc-text); background:#fafbfc; font-size:14px; min-width:160px; transition:all .15s;
  }
  .pr-filter select:focus{ outline:none; border-color:var(--rmc-purple-2); background:#fff; box-shadow: 0 0 0 4px rgba(123,47,184,.08); }
  .pr-filter__group{ flex:0 0 auto; }
  .pr-filter__spacer{ flex:1; }

  /* ============ Ozet Widget'lari ============ */
  .pr-stats{
    display:grid; grid-template-columns: repeat(6, 1fr); gap:14px; margin-bottom:22px;
  }
  .pr-stat--net{ grid-column: span 2; }
  @media(max-width:1200px){
    .pr-stats{ grid-template-columns: repeat(3, 1fr); }
    .pr-stat--net{ grid-column: span 3; }
  }
  @media(max-width:700px){
    .pr-stats{ grid-template-columns: repeat(2, 1fr); }
    .pr-stat--net{ grid-column: span 2; }
  }
  @media(max-width:480px){
    .pr-stats{ grid-template-columns: 1fr; }
    .pr-stat--net{ grid-column: span 1; }
  }
  .pr-stat{
    background:#fff; border-radius:18px; padding:18px 20px;
    box-shadow: var(--rmc-shadow-sm); border:1px solid var(--rmc-border);
    transition: all .25s cubic-bezier(.2,.8,.2,1); position:relative; overflow:hidden;
  }
  .pr-stat:hover{ transform:translateY(-3px); box-shadow: var(--rmc-shadow-md); }
  .pr-stat__icon{
    width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center;
    font-size:16px; font-weight:700; margin-bottom:12px; color:#fff;
  }
  .pr-stat__lbl{ font-size:11px; color:var(--rmc-muted); font-weight:700; letter-spacing:.5px; text-transform:uppercase; margin-bottom:4px; }
  .pr-stat__val{ font-size:20px; font-weight:700; color:var(--rmc-text); letter-spacing:-.3px; white-space:nowrap; }
  .pr-stat__val small{ font-size:12px; color:var(--rmc-muted); margin-left:3px; font-weight:600; }
  .pr-stat__brut{ font-size:11px; color:var(--rmc-muted); margin-top:3px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .pr-stat--net .pr-stat__brut{ color:rgba(255,255,255,.85); }

  /* Mini ozet bandi (Odenen / Kalan / Tip kirilimi) */
  .pr-stat__sub{
    display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; padding-top:10px;
    border-top:1px dashed #efe7f3;
  }
  .pr-stat__sub .pill{
    display:inline-flex; align-items:center; gap:5px; flex:1 1 auto;
    background:#f8f6fb; border:1px solid #efe7f3; border-radius:8px;
    padding:5px 8px; font-size:10.5px; color:var(--rmc-muted);
    font-weight:700; letter-spacing:.2px; white-space:nowrap;
    min-width:0;
  }
  .pr-stat__sub .pill b{
    font-size:11.5px; font-weight:800; color:var(--rmc-text);
    margin-left:auto; white-space:nowrap;
  }
  .pr-stat__sub .pill i{ font-size:10px; flex-shrink:0; }
  .pr-stat__sub .pill.odenen{ background:#ecfdf5; border-color:#bbf7d0; color:#047857; }
  .pr-stat__sub .pill.odenen b{ color:#059669; }
  .pr-stat__sub .pill.kalan { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
  .pr-stat__sub .pill.kalan b{ color:#dc2626; }

  .pr-stat--net .pr-stat__sub{ border-top-color:rgba(255,255,255,.3); }
  .pr-stat--net .pr-stat__sub .pill{
    background:rgba(255,255,255,.16); border-color:rgba(255,255,255,.22);
    color:rgba(255,255,255,.92);
  }
  .pr-stat--net .pr-stat__sub .pill b{ color:#fff; }
  .pr-stat--maas .pr-stat__icon{ background: linear-gradient(135deg,#9D5DC8,#B88ED8); }
  .pr-stat--prim .pr-stat__icon{ background: linear-gradient(135deg,#7B2FB8,#9D5DC8); }
  .pr-stat--bonus .pr-stat__icon{ background: linear-gradient(135deg,#10b981,#34d399); }
  .pr-stat--bonus .pr-stat__val{ color:#059669; }
  .pr-stat--kesinti .pr-stat__icon{ background: linear-gradient(135deg,#ef4444,#f87171); }
  .pr-stat--kesinti .pr-stat__val{ color:#dc2626; }
  .pr-stat--net{
    background: var(--rmc-grad); color:#fff; border:0; position:relative; padding:18px 22px;
  }
  .pr-stat--net::after{
    content:''; position:absolute; top:0; right:0; width:160px; height:160px;
    background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
    border-radius:50%; transform:translate(40%,-40%);
  }
  .pr-stat--net .pr-stat__icon{ background:rgba(255,255,255,.22); backdrop-filter: blur(6px); position:relative; z-index:2; }
  .pr-stat--net .pr-stat__lbl{ color:rgba(255,255,255,.85); position:relative; z-index:2; }
  .pr-stat--net .pr-stat__val{ color:#fff; font-size:22px; position:relative; z-index:2; }
  .pr-net-progress{
    margin-top:14px; position:relative; z-index:2;
  }
  .pr-net-progress__bar{
    height:8px; background:rgba(255,255,255,.22); border-radius:4px; overflow:hidden;
  }
  .pr-net-progress__fill{
    height:100%; background:linear-gradient(90deg,#34d399,#10b981);
    border-radius:4px; transition: width .4s ease;
  }
  .pr-net-progress__meta{
    display:flex; justify-content:space-between; align-items:center;
    margin-top:8px; font-size:12px; font-weight:600;
  }
  .pr-net-progress__meta .lbl{ color:rgba(255,255,255,.75); font-weight:500; }
  .pr-net-progress__meta .val{ color:#fff; font-weight:700; }
  .pr-net-progress__meta .val--bekleyen{ color:#fef3c7; }
  .pr-net-progress__meta .val--odenen{ color:#bbf7d0; }
  .pr-net-progress__yuzde{
    display:inline-block; padding:3px 10px; border-radius:12px;
    background:rgba(255,255,255,.22); font-size:11px; font-weight:700;
    margin-left:8px; backdrop-filter: blur(6px);
  }

  /* ============ Tablo Karti ============ */
  .pr-table-card{
    background:#fff; border-radius:18px; padding:8px 8px 14px;
    box-shadow: var(--rmc-shadow-sm); border:1px solid var(--rmc-border); margin-bottom:22px;
  }
  .pr-table-toolbar{
    padding:14px 18px 12px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
  }
  .pr-table-toolbar h3{ margin:0; font-size:16px; font-weight:700; color:var(--rmc-text); display:flex; align-items:center; gap:8px; }
  .pr-table-toolbar h3 i{ color:var(--rmc-purple-2); }

  /* Tablo wrapper: yatay scroll mobilde */
  .pr-table-card .dataTables_wrapper{ overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .pr-table-scroll{ overflow-x: auto; -webkit-overflow-scrolling: touch; }
  #primrapor_tablo{ width:100% !important; border-collapse: separate; border-spacing:0; }
  #primrapor_tablo thead th{
    background:#fafbfc !important; color:var(--rmc-muted); font-size:11px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase; border:0 !important;
    padding:14px 12px !important; vertical-align:middle; white-space:nowrap;
  }
  #primrapor_tablo tbody td{
    border-bottom:1px solid #f4f0f8 !important; border-top:0 !important; padding:12px 10px !important;
    vertical-align:middle; font-size:13.5px; color:var(--rmc-text); white-space:nowrap;
  }
  #primrapor_tablo tbody tr:hover td{ background:#fbfaff !important; }
  #primrapor_tablo tbody tr:last-child td{ border-bottom:0 !important; }
  .pr-cell-personel{ font-weight:700; font-size:14px; }
  .pr-cell-personel-inner{
    display:inline-flex; align-items:center; gap:10px; min-width:0;
  }
  .pr-cell-personel .pr-avatar{
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:50%; background:var(--rmc-purple-bg); color:var(--rmc-purple-1);
    font-weight:700; font-size:13px; flex-shrink:0;
  }
  .pr-cell-bonus{ color:#059669; font-weight:600; }
  .pr-cell-kesinti{ color:#dc2626; font-weight:600; }
  .pr-cell-net{
    background: linear-gradient(90deg, rgba(123,47,184,.06), rgba(157,93,200,.04));
  }
  .pr-cell-net strong{ color:var(--rmc-purple-1) !important; font-size:15px; }

  /* Aksiyon: tek 'Ode' butonu (modal icinde alt islemler) */
  .pr-ode-btn{
    border:0; cursor:pointer; padding:0 16px; display:inline-flex; align-items:center; gap:7px;
    background: linear-gradient(135deg,#7B2FB8,#9D5DC8); color:#fff; font-weight:600; font-size:13px;
    height:36px; border-radius:10px; transition:all .15s;
    box-shadow: 0 2px 6px rgba(123,47,184,.15);
  }
  .pr-ode-btn:hover{ background: linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; transform:translateY(-1px); box-shadow: 0 4px 10px rgba(123,47,184,.25); }
  .pr-musteri-btn{
    border:1px solid #bfdbfe; cursor:pointer; padding:0;
    display:inline-flex; align-items:center; justify-content:center;
    background:#dbeafe; color:#1e40af;
    width:36px; height:36px; border-radius:10px; transition:all .15s;
  }
  .pr-musteri-btn:hover{ background:#1e40af; color:#fff; transform:translateY(-1px); box-shadow: 0 4px 10px rgba(30,64,175,.25); }
  .pr-musteri-btn i{ font-size:14px; }

  /* Modal icindeki quick-action butonlari */
  .pm-quick-actions{
    display:grid; grid-template-columns: repeat(5, 1fr); gap:8px;
    margin-bottom:18px; padding-bottom:18px; border-bottom:1px solid #e2e8f0;
  }
  @media(max-width:780px){ .pm-quick-actions{ grid-template-columns: repeat(3, 1fr); } }
  .pm-quick-btn--detay{ color:#1e40af; border-color:#bfdbfe; background:#dbeafe; }
  .pm-quick-btn--detay:hover{ background:#bfdbfe; color:#1e3a8a; }
  .pm-quick-btn{
    border:1px solid #e2e8f0; background:#fff; cursor:pointer;
    padding:10px 8px; border-radius:10px; font-weight:600; font-size:12px;
    display:inline-flex; align-items:center; justify-content:center; gap:6px;
    transition:.15s; color:#334155; line-height:1.2;
  }
  .pm-quick-btn i{ font-size:13px; }
  .pm-quick-btn:hover{ border-color:#cbd5e1; background:#f8fafc; transform: translateY(-1px); }
  .pm-quick-btn--ode{ color:#fff; border:0; background: linear-gradient(135deg,#7B2FB8,#9D5DC8); }
  .pm-quick-btn--ode:hover{ background: linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(123,47,184,.25); }
  .pm-quick-btn--bonus{ color:#15803d; border-color:#bbf7d0; background:#f0fdf4; }
  .pm-quick-btn--bonus:hover{ background:#dcfce7; border-color:#86efac; color:#15803d; }
  .pm-quick-btn--kesinti{ color:#b91c1c; border-color:#fecaca; background:#fef2f2; }
  .pm-quick-btn--kesinti:hover{ background:#fee2e2; border-color:#fca5a5; color:#b91c1c; }
  .pm-quick-btn--liste{ color:var(--rmc-purple-1); border-color:#e0d4ec; background:var(--rmc-purple-bg); }
  .pm-quick-btn--liste:hover{ background:#ede0f5; border-color:#cdb1e0; color:var(--rmc-purple-1); }
  .pm-quick-btn--aktif{ box-shadow: inset 0 0 0 2px currentColor; opacity:.55; cursor:default; }
  .pm-quick-btn--aktif:hover{ transform:none; }

  /* ====== Ode Tipi Tabs (Maas/Prim/Diger) ====== */
  .pmtip-tabs{
    display:grid; grid-template-columns: repeat(3, 1fr); gap:8px;
    margin-bottom:14px;
  }
  @media(max-width:600px){ .pmtip-tabs{ grid-template-columns: 1fr; } }
  .pmtip-tab{
    background:#fff; border:2px solid #e2e8f0; border-radius:14px;
    padding:12px 14px; cursor:pointer; transition:all .15s;
    display:flex; flex-direction:column; align-items:center; gap:4px;
    text-align:center; line-height:1;
  }
  .pmtip-tab:hover{ border-color:#cbd5e1; transform: translateY(-1px); }
  .pmtip-tab__icon{
    width:34px; height:34px; border-radius:50%; background:#f1f5f9; color:#64748b;
    display:inline-flex; align-items:center; justify-content:center; font-size:14px;
    margin-bottom:2px; transition:all .15s;
  }
  .pmtip-tab__lbl{ font-size:13px; font-weight:700; color:#1e293b; }
  .pmtip-tab__sub{ font-size:11.5px; font-weight:600; color:#94a3b8; }
  .pmtip-tab.is-active{ border-color:#5C008E; background:#faf5fd; box-shadow: 0 4px 14px rgba(92,0,142,.15); }
  .pmtip-tab.is-active .pmtip-tab__icon{ background: linear-gradient(135deg,#5C008E,#9D5DC8); color:#fff; }
  .pmtip-tab.is-active .pmtip-tab__sub{ color:#7B2FB8; }

  .pmtip-summary{
    display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px;
    margin-bottom:16px;
  }
  @media(max-width:600px){ .pmtip-summary{ grid-template-columns: 1fr; } }
  .pmtip-summary__col{
    background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:13px 16px;
  }
  .pmtip-summary__col--kalan{ background:#fef3c7; border-color:#fde68a; }
  .pmtip-summary__lbl{
    font-size:10.5px; color:#64748b; font-weight:700;
    letter-spacing:.4px; text-transform:uppercase; margin-bottom:4px;
  }
  .pmtip-summary__col--kalan .pmtip-summary__lbl{ color:#92400e; }
  .pmtip-summary__val{ font-size:18px; font-weight:800; }
  .pmtip-summary__val--purple{ color:#7B2FB8; }
  .pmtip-summary__val--green{ color:#16a34a; }
  .pmtip-summary__val--orange{ color:#92400e; }

  /* ====== Prim Detay Modal ====== */
  #primDetayModal .modal-dialog{ max-width: 980px !important; width: 95vw; }
  .pdm-ozet{
    display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; margin-bottom:18px;
  }
  @media(max-width:780px){ .pdm-ozet{ grid-template-columns: repeat(2, 1fr); } }
  .pdm-ozet__card{
    background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:13px 16px;
  }
  .pdm-ozet__lbl{ font-size:10.5px; color:#64748b; font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
  .pdm-ozet__val{ font-size:20px; font-weight:800; color:#1e293b; margin-top:3px; }
  .pdm-ozet__sub{ font-size:11.5px; color:#94a3b8; margin-top:2px; }

  .pdm-tabs{
    display:flex; gap:6px; padding:5px; background:#f1f5f9; border-radius:12px;
    margin-bottom:14px; flex-wrap:wrap;
  }
  .pdm-tabs button{
    flex:1; min-width:120px; border:0; background:transparent;
    padding:9px 14px; border-radius:8px; font-weight:600; font-size:13px;
    color:#64748b; cursor:pointer; transition:.15s;
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
  }
  .pdm-tabs button:hover{ color:#1e293b; }
  .pdm-tabs button.is-active{
    background:#fff; color:#5C008E; box-shadow: 0 1px 3px rgba(0,0,0,.05);
  }
  .pdm-tabs .pdm-count{
    background:#e2e8f0; color:#475569; font-size:10.5px; font-weight:700;
    padding:1px 7px; border-radius:10px;
  }
  .pdm-tabs button.is-active .pdm-count{ background:#5C008E; color:#fff; }

  .pdm-table{ width:100%; border-collapse:separate; border-spacing:0; }
  .pdm-table thead th{
    background:#fafbfc; color:#64748b; font-size:11px; font-weight:700;
    letter-spacing:.4px; text-transform:uppercase;
    padding:10px 12px; text-align:left; border-bottom:1px solid #e2e8f0;
  }
  .pdm-table tbody td{
    padding:10px 12px; border-bottom:1px solid #f1f5f9; font-size:13px; color:#334155;
  }
  .pdm-table tbody tr:last-child td{ border-bottom:0; }
  .pdm-table tbody tr:hover td{ background:#fbfaff; }
  .pdm-table .text-right{ text-align:right; }
  .pdm-prim-tutar{ color:#16a34a; font-weight:700; }
  .pdm-empty{
    text-align:center; padding:40px 20px; color:#94a3b8;
    font-size:13px;
  }
  .pdm-empty i{ font-size:32px; margin-bottom:10px; opacity:.5; display:block; }
  @media (max-width: 600px){
    .pm-quick-actions{ grid-template-columns: 1fr 1fr; }
  }

  /* Mobil/tablet duzenleme */
  @media (max-width: 992px){
    #primrapor_tablo tbody td{ padding:10px 8px !important; font-size:13px; }
    #primrapor_tablo thead th{ padding:12px 8px !important; }
    .pr-split__main{ font-size:12px; padding:0 10px; }
    .pr-durum-badge{ font-size:11px; padding:5px 9px; }
    .pr-durum-badge .alt{ font-size:10px; }
  }
  @media (max-width: 600px){
    .pr-stats{ grid-template-columns: 1fr 1fr !important; }
    .pr-hero{ padding:20px 22px; border-radius:18px; }
    .pr-hero__title h1{ font-size:18px; }
    .pr-hero__title p{ font-size:11.5px; }
    .pr-hero__icon{ width:42px; height:42px; font-size:18px; }
    .pr-hero__period{ font-size:11.5px; padding:7px 12px; }
    .pr-table-card{ border-radius:14px; }
    .pr-filter{ padding:14px 16px; gap:12px; }
    .pr-filter select{ min-width:auto; width:100%; }
    .pr-filter__group{ flex:1 1 calc(50% - 6px); }
  }
  .pr-durum-badge{
    display:inline-flex; flex-direction:column; gap:2px;
    padding:6px 12px; border-radius:10px;
    font-size:12px; font-weight:700; cursor:pointer; transition:.15s;
    border:1px solid transparent; line-height:1.2;
  }
  .pr-durum-badge .lbl{ display:inline-flex; align-items:center; gap:5px; }
  .pr-durum-badge .alt{ font-size:10.5px; font-weight:600; opacity:.85; letter-spacing:.2px; }
  .pr-durum--bekliyor{ background:#f1f5f9; color:#64748b; border-color:#e2e8f0; cursor:default; }
  .pr-durum--kismi{ background:#fef3c7; color:#92400e; border-color:#fde68a; }
  .pr-durum--kismi:hover{ background:#fde68a; }
  .pr-durum--tam{ background:#dcfce7; color:#15803d; border-color:#bbf7d0; }
  .pr-durum--tam:hover{ background:#bbf7d0; }
  .pr-durum--fazla{ background:#dbeafe; color:#1e40af; border-color:#bfdbfe; }
  .pr-durum--fazla:hover{ background:#bfdbfe; }
  tr.pr-row-tam td.pr-cell-net{
    background: linear-gradient(90deg, rgba(22,163,74,.08), rgba(22,163,74,.04)) !important;
  }
  tr.pr-row-tam td.pr-cell-net strong{ color:#15803d !important; }
  tr.pr-row-kismi td.pr-cell-net{
    background: linear-gradient(90deg, rgba(245,158,11,.08), rgba(245,158,11,.04)) !important;
  }

  /* DataTable controls override */
  .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter{ padding: 6px 14px; }
  .dataTables_wrapper .dataTables_length select, .dataTables_wrapper .dataTables_filter input{
    border:2px solid var(--rmc-border); border-radius:10px; padding:6px 10px; background:#fafbfc;
  }
  .dataTables_wrapper .dataTables_filter input:focus, .dataTables_wrapper .dataTables_length select:focus{
    outline:none; border-color:var(--rmc-purple-2); background:#fff;
  }
  .dt-buttons .btn{ border-radius:10px !important; font-weight:600; padding:6px 12px; }
  .dt-buttons .btn-success{ background:var(--rmc-grad-soft) !important; border:0 !important; color:#fff; }
  .dt-buttons .btn-danger{ background: linear-gradient(135deg,#ef4444,#dc2626) !important; border:0 !important; }
  .dt-buttons .btn-secondary{ background: linear-gradient(135deg,#64748b,#475569) !important; border:0 !important; }
  .dataTables_paginate .page-link{
    color:var(--rmc-purple-2) !important; border-radius:8px !important; margin: 0 2px;
    border-color:var(--rmc-border) !important;
  }
  .dataTables_paginate .page-item.active .page-link{
    background: var(--rmc-grad) !important; border-color:transparent !important; color:#fff !important;
  }
  .dataTables_info{ color:var(--rmc-muted) !important; font-size:13px; padding:14px 18px !important; }

  /* ============ MODAL GECIS — backdrop sabit, animasyon yok ============ */
  body.prim-switching .modal-backdrop{
    opacity: .5 !important;
    transition: none !important;
  }
  body.prim-switching .modal,
  body.prim-switching .modal.fade,
  body.prim-switching .modal.show{
    transition: none !important;
  }
  body.prim-switching .modal.fade .modal-dialog{
    transition: none !important;
    transform: none !important;
  }
  body.prim-switching .modal-content{
    animation: none !important;
  }

  /* ============ MODAL: Slate/Indigo Pattern (raporlar.blade.php referansi) ============ */
  #primHareketModal, #primHareketListeModal{
    position: fixed !important;
    top:0 !important; left:0 !important; right:0 !important; bottom:0 !important;
    width:100vw !important; height:100vh !important;
    z-index: 10550 !important;
    overflow-x: hidden; overflow-y: auto;
  }
  #primHareketModal .modal-dialog, #primHareketListeModal .modal-dialog{
    max-width: 720px; width: 95%;
    margin: 1.5rem auto !important;
    min-height: calc(100% - 3rem);
    display: flex; align-items: center; justify-content: center;
    pointer-events: none;
  }
  #primHareketListeModal .modal-dialog{ max-width: 860px; }
  #primHareketModal .modal-content, #primHareketListeModal .modal-content{
    width: 100%; pointer-events: auto;
    border: 0; border-radius: 16px; overflow: hidden;
    box-shadow: 0 20px 50px rgba(15,23,42,.18);
  }
  .pm-accent-bar{
    height: 4px;
    background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
  }
  .pm-header{
    background: #ffffff; color: #1e293b;
    padding: 22px 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    border-bottom: 1px solid #f1f5f9;
  }
  .pm-header__left{ display:flex; align-items:center; gap:16px; flex:1; min-width:0; }
  .pm-icon{
    width:48px; height:48px; border-radius:12px;
    background:#eef2ff; color:#6366f1;
    display:flex; align-items:center; justify-content:center;
    font-size:22px; flex-shrink:0;
  }
  .pm-header h4{ margin:0; font-size:19px; font-weight:700; color:#0f172a; }
  .pm-sub{ font-size:13px; color:#64748b; margin-top:3px; font-weight:500; }
  .pm-sub .donem{ color:#475569; }
  .pm-close{
    color:#94a3b8; background:#f1f5f9;
    border:none; border-radius:10px; width:38px; height:38px;
    font-size:22px; line-height:1; cursor:pointer; transition:.15s;
  }
  .pm-close:hover{ background:#e2e8f0; color:#475569; }

  .pm-body{
    padding: 22px 28px; background: #f8fafc;
    max-height: 65vh; overflow-y: auto;
  }
  .pm-body::-webkit-scrollbar{ width:8px; }
  .pm-body::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:4px; }

  .pm-footer{
    padding:16px 28px; background:#fff;
    display:flex; justify-content:flex-end; gap:10px;
    border-top:1px solid #f1f5f9;
  }
  .pm-btn-primary{
    background:#6366f1; color:#fff; border:none;
    padding:10px 26px; border-radius:10px;
    font-weight:600; font-size:14px; cursor:pointer; transition:.15s;
  }
  .pm-btn-primary:hover{ background:#4f46e5; color:#fff; }
  .pm-btn-secondary{
    background:#f1f5f9; color:#475569; border:none;
    padding:10px 22px; border-radius:10px;
    font-weight:600; font-size:14px; cursor:pointer; transition:.15s;
  }
  .pm-btn-secondary:hover{ background:#e2e8f0; color:#1e293b; }

  /* Liste modal - ozet chip'leri */
  .pm-summary{ display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
  .pm-chip{
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    padding:12px 18px; font-size:13px; color:#64748b; font-weight:500;
    display:flex; align-items:baseline; gap:8px;
  }
  .pm-chip strong{ color:#0f172a; font-size:16px; font-weight:700; }
  .pm-chip-success strong{ color:#16a34a; }
  .pm-chip-danger strong{ color:#dc2626; }
  .pm-chip-net strong{ color:#6366f1; }

  /* Hareket karti */
  .pm-list{ display:flex; flex-direction:column; gap:10px; }
  .pm-item{
    display:flex; align-items:center; gap:14px;
    background:#fff; border-radius:12px; padding:14px 16px;
    border:1px solid #e2e8f0; transition: all .15s;
  }
  .pm-item:hover{ border-color:#cbd5e1; box-shadow: 0 4px 12px rgba(15,23,42,.06); }
  .pm-item--bonus{ border-left: 4px solid #16a34a; }
  .pm-item--kesinti{ border-left: 4px solid #dc2626; }

  .pm-item__icon{
    width:42px; height:42px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; font-size:16px;
  }
  .pm-item--bonus .pm-item__icon{ background:#dcfce7; color:#16a34a; }
  .pm-item--kesinti .pm-item__icon{ background:#fee2e2; color:#dc2626; }
  .pm-item__body{ flex:1; min-width:0; }
  .pm-item__row1{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
  .pm-item__badge{
    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
    letter-spacing:.4px;
  }
  .pm-item--bonus .pm-item__badge{ background:#dcfce7; color:#15803d; }
  .pm-item--kesinti .pm-item__badge{ background:#fee2e2; color:#991b1b; }
  .pm-item__tutar{ font-size:17px; font-weight:700; }
  .pm-item--bonus .pm-item__tutar{ color:#16a34a; }
  .pm-item--kesinti .pm-item__tutar{ color:#dc2626; }
  .pm-item__tarih{ font-size:12px; color:#94a3b8; font-weight:500; display:inline-flex; align-items:center; gap:5px; }
  .pm-item__aciklama{ font-size:13px; color:#475569; margin-top:5px; line-height:1.5; }
  .pm-item__sil{
    width:34px; height:34px; border-radius:10px;
    background:transparent; color:#94a3b8; border:0;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:.15s; flex-shrink:0;
  }
  .pm-item__sil:hover{ background:#fee2e2; color:#dc2626; }

  /* Empty / Loading */
  .pm-empty{ text-align:center; padding:50px 20px; color:#94a3b8; }
  .pm-empty__icon{
    width:72px; height:72px; border-radius:50%; background:#eef2ff;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:32px; color:#6366f1; margin-bottom:14px;
  }
  .pm-empty__baslik{ font-size:15px; font-weight:600; color:#475569; margin-bottom:4px; }
  .pm-empty__alt{ font-size:13px; color:#94a3b8; }
  .pm-loading{ text-align:center; padding:60px 20px; color:#64748b; }
  .pm-spinner{
    display:inline-block; width:40px; height:40px;
    border:3px solid #e2e8f0; border-top-color:#6366f1;
    border-radius:50%; animation: pmSpin .8s linear infinite;
  }
  @keyframes pmSpin{ to { transform: rotate(360deg); } }

  /* Form (ekleme modal) */
  .pm-form-group{ margin-bottom:16px; }
  .pm-form-group label{
    font-weight:700; color:#475569; font-size:12px;
    margin-bottom:6px; display:block;
    letter-spacing:.4px; text-transform:uppercase;
  }
  .pm-form-group .form-control, .pm-form-group input, .pm-form-group textarea{
    border-radius:10px; border:1px solid #e2e8f0; padding:10px 14px;
    font-size:14px; background:#fff; color:#0f172a; transition:.15s; width:100%;
  }
  .pm-form-group .form-control:focus, .pm-form-group input:focus, .pm-form-group textarea:focus{
    outline:none; border-color:#6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1);
  }
  .pm-tutar-input{ position:relative; }
  .pm-tutar-input .form-control{ padding-left:36px; font-size:18px; font-weight:700; }
  .pm-tutar-input::before{
    content:'₺'; position:absolute; left:14px; top:50%; transform:translateY(-50%);
    color:#6366f1; font-size:16px; font-weight:700; z-index:2;
  }
  .pm-tip-radio{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .pm-tip-radio input[type=radio]{ display:none; }
  .pm-tip-radio label{
    cursor:pointer; padding:14px 16px; border-radius:12px;
    border:2px solid #e2e8f0; background:#fff;
    text-align:center; font-weight:600; transition:.15s; margin:0;
    display:flex; flex-direction:column; align-items:center; gap:6px;
    color:#475569;
  }
  .pm-tip-radio label .ic{ font-size:22px; }
  .pm-tip-radio input[value=bonus]:checked + label{ border-color:#16a34a; background:#f0fdf4; color:#15803d; }
  .pm-tip-radio input[value=kesinti]:checked + label{ border-color:#dc2626; background:#fef2f2; color:#991b1b; }
  .pm-tip-radio input[value=bonus] + label .ic{ color:#16a34a; }
  .pm-tip-radio input[value=kesinti] + label .ic{ color:#dc2626; }

  @media(max-width:600px){
    .pm-summary{ flex-direction:column; }
    .pm-chip{ width:100%; }
    .pm-header{ padding:18px; }
    .pm-body{ padding:16px; }
  }
</style>

<form method="get" id="primRaporFiltre" class="pr-filter">
  <input type="hidden" name="sube" value="{{$isletme->id}}">
  <input type="hidden" name="_tab" value="prim">
  <div class="pr-filter__group">
    <label>Ay</label>
    <select name="ay" onchange="document.getElementById('primRaporFiltre').submit()">
      @foreach($aylar as $ayNo => $ayAdi)
        <option value="{{$ayNo}}" {{$ayNo==$ay?'selected':''}}>{{$ayAdi}}</option>
      @endforeach
    </select>
  </div>
  <div class="pr-filter__group">
    <label>Yıl</label>
    <select name="yil" onchange="document.getElementById('primRaporFiltre').submit()">
      @for($y=date('Y'); $y>=date('Y')-4; $y--)
        <option value="{{$y}}" {{$y==$yil?'selected':''}}>{{$y}}</option>
      @endfor
    </select>
  </div>
  <div class="pr-filter__spacer"></div>
  <div class="pr-filter__group" style="font-size:12px; color:var(--rmc-muted); display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
    <span style="display:inline-flex; align-items:center; gap:6px; background:var(--rmc-purple-bg); color:var(--rmc-purple-1); padding:7px 12px; border-radius:30px; font-weight:700;">
      <i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($tarih1))}} — {{date('d.m.Y', strtotime($tarih2))}}
    </span>
    <span><i class="fa fa-info-circle"></i> Ay/Yıl değiştirince rapor otomatik yenilenir.</span>
  </div>
</form>

<div class="pr-stats">
  <div class="pr-stat pr-stat--maas">
    <div class="pr-stat__icon">₺</div>
    <div class="pr-stat__lbl">Kalan Maaş</div>
    <div class="pr-stat__val">{{$_fmt($toplamKalanMaas)}} <small>₺</small></div>
    <div class="pr-stat__brut">Toplam: {{$_fmt($toplamMaas)}} ₺</div>
    <div class="pr-stat__sub">
      <span class="pill odenen"><i class="fa fa-check-circle"></i> Ödenen <b>{{$_fmt($toplamOdenenMaas)}} ₺</b></span>
      <span class="pill kalan"><i class="fa fa-clock-o"></i> Kalan <b>{{$_fmt($toplamKalanMaas)}} ₺</b></span>
    </div>
  </div>
  <div class="pr-stat pr-stat--prim">
    <div class="pr-stat__icon">%</div>
    <div class="pr-stat__lbl">Kalan Prim</div>
    <div class="pr-stat__val">{{$_fmt($toplamKalanPrim)}} <small>₺</small></div>
    <div class="pr-stat__brut">Toplam: {{$_fmt($toplamPrim)}} ₺</div>
    <div class="pr-stat__sub">
      <span class="pill odenen"><i class="fa fa-check-circle"></i> Ödenen <b>{{$_fmt($toplamOdenenPrim)}} ₺</b></span>
      <span class="pill kalan"><i class="fa fa-clock-o"></i> Kalan <b>{{$_fmt($toplamKalanPrim)}} ₺</b></span>
    </div>
  </div>
  <div class="pr-stat pr-stat--bonus">
    <div class="pr-stat__icon">＋</div>
    <div class="pr-stat__lbl">Toplam Bonus</div>
    <div class="pr-stat__val">{{$_fmt($toplamBonus)}} <small>₺</small></div>
    <div class="pr-stat__brut" style="opacity:.65">Net hak edişe ekleniyor</div>
  </div>
  <div class="pr-stat pr-stat--kesinti">
    <div class="pr-stat__icon">−</div>
    <div class="pr-stat__lbl">Toplam Kesinti</div>
    <div class="pr-stat__val">{{$_fmt($toplamKesinti)}} <small>₺</small></div>
    <div class="pr-stat__brut" style="opacity:.65">Net hak edişten düşülüyor</div>
  </div>
  <div class="pr-stat pr-stat--net">
    <div class="pr-stat__icon"><i class="fa fa-credit-card"></i></div>
    <div class="pr-stat__lbl">
      Bekleyen Ödeme
      <span class="pr-net-progress__yuzde">%{{$odemeYuzde}}</span>
    </div>
    <div class="pr-stat__val">{{$_fmt($toplamBekleyen)}} <small>₺</small></div>
    <div class="pr-stat__brut">Net Hak Ediş: {{$_fmt($toplamNet)}} ₺ &nbsp;·&nbsp; Ödenen: {{$_fmt($toplamOdenen)}} ₺</div>
    <div class="pr-net-progress">
      <div class="pr-net-progress__bar">
        <div class="pr-net-progress__fill" style="width: {{$odemeYuzde}}%"></div>
      </div>
    </div>
    <div class="pr-stat__sub">
      <span class="pill"><i class="fa fa-money"></i> Maaş <b>{{$_fmt($toplamOdenenMaas)}} ₺</b></span>
      <span class="pill"><i class="fa fa-percent"></i> Prim <b>{{$_fmt($toplamOdenenPrim)}} ₺</b></span>
      <span class="pill"><i class="fa fa-clock-o"></i> Avans <b>{{$_fmt($toplamOdenenDiger)}} ₺</b></span>
    </div>
  </div>
</div>

<div class="pr-table-card">
  <div class="pr-table-toolbar">
    <h3><i class="fa fa-users"></i> Personel Bazında Hak Ediş</h3>
  </div>
  <div class="pr-table-scroll" style="padding: 0 14px 6px">
    <table class="data-table table hover nowrap" id="primrapor_tablo" style="width:100%">
      <thead>
        <tr>
          <th>Personel</th>
          <th>Maaş</th>
          <th>Hizmet Primi</th>
          <th>Ürün Primi</th>
          <th>Paket Primi</th>
          <th>Prim Toplam</th>
          <th>Bonus</th>
          <th>Kesinti</th>
          <th>NET Ödenecek</th>
          <th>Durum</th>
          <th class="datatable-nosort">İşlemler</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rapor as $r)
          @php
            $bas = mb_strtoupper(mb_substr($r['personel_adi'],0,1,'UTF-8'),'UTF-8');
            $rowCls = '';
            if($r['durum']==='tam' || $r['durum']==='fazla') $rowCls = 'pr-row-tam';
            elseif($r['durum']==='kismi') $rowCls = 'pr-row-kismi';
          @endphp
          <tr class="{{ $rowCls }}">
            <td class="pr-cell-personel"><span class="pr-cell-personel-inner"><span class="pr-avatar">{{$bas}}</span><span>{{$r['personel_adi']}}</span></span></td>
            <td>{{$_fmt($r['maas'])}} ₺</td>
            <td>{{$_fmt($r['hizmet_primi'])}} ₺</td>
            <td>{{$_fmt($r['urun_primi'])}} ₺</td>
            <td>{{$_fmt($r['paket_primi'])}} ₺</td>
            <td><strong>{{$_fmt($r['prim_toplam'])}} ₺</strong></td>
            <td class="pr-cell-bonus">+{{$_fmt($r['bonus'])}}@if($r['hareket_sayisi']>0) <small style="color:var(--rmc-muted); font-weight:500">({{$r['hareket_sayisi']}})</small>@endif</td>
            <td class="pr-cell-kesinti">−{{$_fmt($r['kesinti'])}}</td>
            <td class="pr-cell-net"><strong>{{$_fmt($r['net_hakedis'])}} ₺</strong></td>
            <td>
              @if($r['durum']==='bekliyor')
                <span class="pr-durum-badge pr-durum--bekliyor"><span class="lbl"><i class="fa fa-clock-o"></i> Bekliyor</span></span>
              @elseif($r['durum']==='kismi')
                <span class="pr-durum-badge pr-durum--kismi prim-odeme-detay" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Ödeme detayı">
                  <span class="lbl"><i class="fa fa-hourglass-half"></i> Kısmi Ödeme</span>
                  <span class="alt">{{$_fmt($r['odenen_toplam'])}} / {{$_fmt($r['net_hakedis'])}} ₺</span>
                </span>
              @elseif($r['durum']==='tam')
                <span class="pr-durum-badge pr-durum--tam prim-odeme-detay" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Ödeme detayı">
                  <span class="lbl"><i class="fa fa-check-circle"></i> Tam Ödendi</span>
                  <span class="alt">{{$_fmt($r['odenen_toplam'])}} ₺ ({{$r['odeme_sayisi']}} ödeme)</span>
                </span>
              @else
                <span class="pr-durum-badge pr-durum--fazla prim-odeme-detay" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Ödeme detayı">
                  <span class="lbl"><i class="fa fa-arrow-up"></i> Fazla Ödeme</span>
                  <span class="alt">{{$_fmt($r['odenen_toplam'])}} / {{$_fmt($r['net_hakedis'])}} ₺</span>
                </span>
              @endif
            </td>
            <td>
              <div style="display:inline-flex; gap:6px; align-items:center">
                @yetki('personel.odeme_yap')
                <button class="pr-ode-btn prim-ode"
                  data-value="{{$r['personel_id']}}"
                  data-adi="{{$r['personel_adi']}}"
                  data-net="{{$r['net_hakedis']}}"
                  data-odenen="{{$r['odenen_toplam']}}"
                  data-kalan="{{$r['kalan']}}"
                  title="Prim & Hak Ediş İşlemleri">
                  <i class="fa fa-credit-card"></i>
                  <span>Öde</span>
                </button>
                @endyetki
                <button class="pr-musteri-btn prim-musteri-detay"
                  data-value="{{$r['personel_id']}}"
                  data-adi="{{$r['personel_adi']}}"
                  title="Bu ay satış yaptığı müşteriler">
                  <i class="fa fa-users"></i>
                </button>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ========== Bonus/Kesinti Ekleme Modal ========== --}}
<div class="modal fade" id="primHareketModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="pm-accent-bar"></div>
      <form id="primHareketForm">
        {!!csrf_field()!!}
        <input type="hidden" name="sube" value="{{$isletme->id}}">
        <input type="hidden" name="personel_id" id="primHareket_personelId">
        <div class="pm-header">
          <div class="pm-header__left">
            <div class="pm-icon"><i class="fa fa-plus"></i></div>
            <div style="flex:1; min-width:0;">
              <h4>Prim Hareketi Ekle</h4>
              <div class="pm-sub" id="primHareket_personelAdi"></div>
            </div>
          </div>
          <button type="button" class="pm-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
        </div>
        <div class="pm-body">
          <div class="pm-quick-actions">
            <button type="button" class="pm-quick-btn pm-quick-btn--ode pm-quick-ode">
              <i class="fa fa-credit-card"></i> Öde
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--bonus pm-quick-bonus">
              <i class="fa fa-plus-circle"></i> Bonus
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--kesinti pm-quick-kesinti">
              <i class="fa fa-minus-circle"></i> Kesinti
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--liste pm-quick-hareketler">
              <i class="fa fa-history"></i> Hareketler
            </button>
          </div>
          <div class="pm-form-group">
            <label>Hareket Tipi</label>
            <div class="pm-tip-radio">
              <input type="radio" id="prtip_bonus" name="tip" value="bonus" checked>
              <label for="prtip_bonus"><span class="ic">＋</span>Bonus / Ek Ödeme</label>
              <input type="radio" id="prtip_kesinti" name="tip" value="kesinti">
              <label for="prtip_kesinti"><span class="ic">−</span>Kesinti</label>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="pm-form-group">
                <label>Tutar</label>
                <div class="pm-tutar-input">
                  <input type="number" step="0.01" min="0.01" class="form-control" name="tutar" placeholder="0,00" required>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="pm-form-group">
                <label>Tarih</label>
                <input type="date" class="form-control" name="tarih" value="{{date('Y-m-d')}}" required>
              </div>
            </div>
          </div>
          <div class="pm-form-group">
            <label>Açıklama <small style="color:#94a3b8; font-weight:500; text-transform:none; letter-spacing:0">(opsiyonel)</small></label>
            <textarea class="form-control" name="aciklama" rows="2" maxlength="300" placeholder="Ör: Ay sonu performans bonusu / Geç gelme kesintisi"></textarea>
          </div>
        </div>
        <div class="pm-footer">
          <button type="button" class="pm-btn-secondary" data-dismiss="modal">İptal</button>
          <button type="submit" class="pm-btn-primary"><i class="fa fa-check"></i> Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ========== Prim Öde Modal ========== --}}
<div class="modal fade" id="primOdeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="pm-accent-bar"></div>
      <form id="primOdeForm">
        {!!csrf_field()!!}
        <input type="hidden" name="sube" value="{{$isletme->id}}">
        <input type="hidden" name="personel_id" id="primOde_personelId">
        <input type="hidden" name="donem" value="{{ sprintf('%04d-%02d', $yil, $ay) }}">
        <input type="hidden" name="odeme_tipi" id="primOde_tipi" value="maas">
        <div class="pm-header">
          <div class="pm-header__left">
            <div class="pm-icon" style="background:#dcfce7; color:#16a34a"><i class="fa fa-credit-card"></i></div>
            <div style="flex:1; min-width:0;">
              <h4>Prim / Maaş Öde</h4>
              <div class="pm-sub">
                <span id="primOde_personelAdi" style="font-weight:600; color:#0f172a"></span>
                <span class="donem"> &nbsp;·&nbsp; {{$aylar[$ay]}} {{$yil}} dönemi</span>
              </div>
            </div>
          </div>
          <button type="button" class="pm-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
        </div>
        <div class="pm-body">
          <div class="pm-quick-actions">
            <button type="button" class="pm-quick-btn pm-quick-btn--ode pm-quick-btn--aktif" disabled>
              <i class="fa fa-credit-card"></i> Öde
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--bonus pm-quick-bonus">
              <i class="fa fa-plus-circle"></i> Bonus
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--kesinti pm-quick-kesinti">
              <i class="fa fa-minus-circle"></i> Kesinti
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--liste pm-quick-hareketler">
              <i class="fa fa-history"></i> Hareketler
            </button>
            <button type="button" class="pm-quick-btn pm-quick-btn--detay pm-quick-detay">
              <i class="fa fa-bar-chart"></i> Prim Detay
            </button>
          </div>

          <div style="font-size:12px; color:#64748b; font-weight:700; letter-spacing:.4px; text-transform:uppercase; margin-bottom:8px">Ödeme Tipi</div>
          <div class="pmtip-tabs">
            <button type="button" class="pmtip-tab is-active" data-tip="maas">
              <span class="pmtip-tab__icon"><i class="fa fa-money"></i></span>
              <span class="pmtip-tab__lbl">Maaş Öde</span>
              <span class="pmtip-tab__sub" id="primOde_maasKalan">0,00 ₺</span>
            </button>
            <button type="button" class="pmtip-tab" data-tip="prim">
              <span class="pmtip-tab__icon"><i class="fa fa-percent"></i></span>
              <span class="pmtip-tab__lbl">Prim Öde</span>
              <span class="pmtip-tab__sub" id="primOde_primKalan">0,00 ₺</span>
            </button>
            <button type="button" class="pmtip-tab" data-tip="diger">
              <span class="pmtip-tab__icon"><i class="fa fa-asterisk"></i></span>
              <span class="pmtip-tab__lbl">Diğer / Avans</span>
              <span class="pmtip-tab__sub" id="primOde_digerKalan">0,00 ₺</span>
            </button>
          </div>

          <div class="pmtip-summary">
            <div class="pmtip-summary__col">
              <div class="pmtip-summary__lbl">Hak Ediş</div>
              <div class="pmtip-summary__val pmtip-summary__val--purple" id="primOde_netLabel">0,00 ₺</div>
            </div>
            <div class="pmtip-summary__col">
              <div class="pmtip-summary__lbl">Ödenen</div>
              <div class="pmtip-summary__val pmtip-summary__val--green" id="primOde_odenenLabel">0,00 ₺</div>
            </div>
            <div class="pmtip-summary__col pmtip-summary__col--kalan">
              <div class="pmtip-summary__lbl">Kalan</div>
              <div class="pmtip-summary__val pmtip-summary__val--orange" id="primOde_kalanLabel">0,00 ₺</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="pm-form-group">
                <label>Ödenecek Tutar</label>
                <div class="pm-tutar-input">
                  <input type="number" step="0.01" min="0.01" class="form-control" name="tutar" id="primOde_tutar" required>
                </div>
                <div style="font-size:11px; color:#94a3b8; margin-top:4px">Varsayılan, seçili tipin kalan tutarıdır.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="pm-form-group">
                <label>Ödeme Tarihi</label>
                <input type="date" class="form-control" name="odeme_tarihi" value="{{date('Y-m-d')}}" required>
              </div>
            </div>
          </div>
          <div class="pm-form-group">
            <label>Ödeme Yöntemi</label>
            <select class="form-control" name="odeme_yontemi">
              <option value="">Seçiniz...</option>
              <option value="Nakit">Nakit</option>
              <option value="Banka Havalesi">Banka Havalesi</option>
              <option value="Kredi Kartı">Kredi Kartı</option>
              <option value="Diğer">Diğer</option>
            </select>
          </div>
          <div class="pm-form-group">
            <label>Açıklama <small style="color:#94a3b8; font-weight:500; text-transform:none; letter-spacing:0">(opsiyonel)</small></label>
            <textarea class="form-control" name="aciklama" rows="2" maxlength="300" placeholder="Ör: Nisan 2026 prim ödemesi"></textarea>
          </div>
        </div>
        <div class="pm-footer">
          <button type="button" class="pm-btn-secondary" data-dismiss="modal">İptal</button>
          <button type="submit" class="pm-btn-primary" style="background:#16a34a"><i class="fa fa-check"></i> Ödemeyi Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ========== Prim Detay Modal (satislar + musteriler) ========== --}}
<div class="modal fade" id="primDetayModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="pm-accent-bar"></div>
      <div class="pm-header">
        <div class="pm-header__left">
          <div class="pm-icon" style="background:#dbeafe; color:#1e40af"><i class="fa fa-bar-chart"></i></div>
          <div style="flex:1; min-width:0;">
            <h4>Prim Detayı</h4>
            <div class="pm-sub">
              <span id="primDetay_personelAdi" style="font-weight:600; color:#0f172a"></span>
              <span class="donem"> &nbsp;·&nbsp; {{$aylar[$ay]}} {{$yil}} dönemi</span>
            </div>
          </div>
        </div>
        <button type="button" class="pm-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
      </div>
      <div class="pm-body">
        <div class="pm-quick-actions">
          @yetki('personel.odeme_yap')
          <button type="button" class="pm-quick-btn pm-quick-btn--ode pm-quick-ode">
            <i class="fa fa-credit-card"></i> Öde
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--bonus pm-quick-bonus">
            <i class="fa fa-plus-circle"></i> Bonus
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--kesinti pm-quick-kesinti">
            <i class="fa fa-minus-circle"></i> Kesinti
          </button>
          @endyetki
          <button type="button" class="pm-quick-btn pm-quick-btn--liste pm-quick-hareketler">
            <i class="fa fa-history"></i> Hareketler
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--detay pm-quick-btn--aktif" disabled>
            <i class="fa fa-bar-chart"></i> Prim Detay
          </button>
        </div>

        <div id="primDetay_loading">
          <div class="pm-loading"><div class="pm-spinner"></div><div style="margin-top:14px; font-weight:500">Yükleniyor...</div></div>
        </div>

        <div id="primDetay_icerik" style="display:none">
          <div class="pdm-ozet">
            <div class="pdm-ozet__card">
              <div class="pdm-ozet__lbl">Hizmet Primi</div>
              <div class="pdm-ozet__val" style="color:#7B2FB8" id="pdmHizmetPrim">0,00 ₺</div>
              <div class="pdm-ozet__sub"><span id="pdmHizmetCount">0</span> satış · %<span id="pdmHizmetYuzde">0</span></div>
            </div>
            <div class="pdm-ozet__card">
              <div class="pdm-ozet__lbl">Ürün Primi</div>
              <div class="pdm-ozet__val" style="color:#7B2FB8" id="pdmUrunPrim">0,00 ₺</div>
              <div class="pdm-ozet__sub"><span id="pdmUrunCount">0</span> satış · %<span id="pdmUrunYuzde">0</span></div>
            </div>
            <div class="pdm-ozet__card">
              <div class="pdm-ozet__lbl">Paket Primi</div>
              <div class="pdm-ozet__val" style="color:#7B2FB8" id="pdmPaketPrim">0,00 ₺</div>
              <div class="pdm-ozet__sub"><span id="pdmPaketCount">0</span> satış · %<span id="pdmPaketYuzde">0</span></div>
            </div>
            <div class="pdm-ozet__card" style="background:#f7f1fb; border-color:#e0d4ec">
              <div class="pdm-ozet__lbl" style="color:#5C008E">Müşteri Sayısı</div>
              <div class="pdm-ozet__val" style="color:#5C008E" id="pdmMusteriCount">0</div>
              <div class="pdm-ozet__sub" style="color:#7B2FB8">tekil müşteri</div>
            </div>
          </div>

          <div class="pdm-tabs">
            <button type="button" data-pdm-tab="hizmet" class="is-active"><i class="fa fa-cut"></i> Hizmetler <span class="pdm-count" id="pdmTabHizmetCount">0</span></button>
            <button type="button" data-pdm-tab="urun"><i class="fa fa-tag"></i> Ürünler <span class="pdm-count" id="pdmTabUrunCount">0</span></button>
            <button type="button" data-pdm-tab="paket"><i class="fa fa-archive"></i> Paketler <span class="pdm-count" id="pdmTabPaketCount">0</span></button>
            <button type="button" data-pdm-tab="musteri"><i class="fa fa-users"></i> Müşteriler <span class="pdm-count" id="pdmTabMusteriCount">0</span></button>
          </div>

          <div class="pdm-pane" id="pdmPane_hizmet"><div id="pdmTbl_hizmet"></div></div>
          <div class="pdm-pane" id="pdmPane_urun" style="display:none"><div id="pdmTbl_urun"></div></div>
          <div class="pdm-pane" id="pdmPane_paket" style="display:none"><div id="pdmTbl_paket"></div></div>
          <div class="pdm-pane" id="pdmPane_musteri" style="display:none"><div id="pdmTbl_musteri"></div></div>
        </div>
      </div>
      <div class="pm-footer">
        <button type="button" class="pm-btn-primary" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

{{-- ========== Ödeme Detay (Liste) Modal ========== --}}
<div class="modal fade" id="primOdemeDetayModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="pm-accent-bar"></div>
      <div class="pm-header">
        <div class="pm-header__left">
          <div class="pm-icon" style="background:#dcfce7; color:#16a34a"><i class="fa fa-check-circle"></i></div>
          <div style="flex:1; min-width:0;">
            <h4>Ödeme Geçmişi</h4>
            <div class="pm-sub">
              <span id="primOdemeDetay_personelAdi" style="font-weight:600; color:#0f172a"></span>
              <span class="donem"> &nbsp;·&nbsp; {{$aylar[$ay]}} {{$yil}}</span>
            </div>
          </div>
        </div>
        <button type="button" class="pm-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
      </div>
      <div class="pm-body">
        <div class="pm-quick-actions">
          @yetki('personel.odeme_yap')
          <button type="button" class="pm-quick-btn pm-quick-btn--ode pm-quick-ode">
            <i class="fa fa-credit-card"></i> Öde
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--bonus pm-quick-bonus">
            <i class="fa fa-plus-circle"></i> Bonus
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--kesinti pm-quick-kesinti">
            <i class="fa fa-minus-circle"></i> Kesinti
          </button>
          @endyetki
          <button type="button" class="pm-quick-btn pm-quick-btn--liste pm-quick-hareketler">
            <i class="fa fa-history"></i> Hareketler
          </button>
        </div>
        <div class="pm-summary" id="primOdemeDetay_ozet" style="display:none">
          <div class="pm-chip pm-chip-success">Toplam Ödenen <strong id="primOdemeDetay_toplam">0,00 ₺</strong></div>
          <div class="pm-chip">Net Hak Ediş <strong id="primOdemeDetay_net">0,00 ₺</strong></div>
          <div class="pm-chip" id="primOdemeDetay_kalanWrap">Kalan <strong id="primOdemeDetay_kalan">0,00 ₺</strong></div>
        </div>
        <div class="pm-summary pm-summary--breakdown" id="primOdemeDetay_kirilim" style="display:none; margin-top:8px;">
          <div class="pm-chip" style="background:#eef2ff;color:#4338ca;border-color:#c7d2fe">Maaş <strong id="primOdemeDetay_maas">0,00 ₺</strong></div>
          <div class="pm-chip" style="background:#dbeafe;color:#1e40af;border-color:#bfdbfe">Prim <strong id="primOdemeDetay_prim">0,00 ₺</strong></div>
          <div class="pm-chip" style="background:#fef3c7;color:#92400e;border-color:#fde68a">Avans <strong id="primOdemeDetay_diger">0,00 ₺</strong></div>
          <div class="pm-chip pm-chip-success">Bonus <strong id="primOdemeDetay_bonus">0,00 ₺</strong></div>
          <div class="pm-chip pm-chip-danger">Kesinti <strong id="primOdemeDetay_kesinti">0,00 ₺</strong></div>
        </div>
        <div id="primOdemeDetay_liste">
          <div class="pm-loading"><div class="pm-spinner"></div><div style="margin-top:14px; font-weight:500">Yükleniyor...</div></div>
        </div>
      </div>
      <div class="pm-footer">
        <button type="button" class="pm-btn-secondary" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

{{-- ========== Hareket Geçmişi Modal ========== --}}
<div class="modal fade" id="primHareketListeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="pm-accent-bar"></div>
      <div class="pm-header">
        <div class="pm-header__left">
          <div class="pm-icon"><i class="fa fa-history"></i></div>
          <div style="flex:1; min-width:0;">
            <h4>Prim Hareketleri</h4>
            <div class="pm-sub">
              <span id="primListe_personelAdi" style="font-weight:600; color:#0f172a"></span>
              <span class="donem"> &nbsp;·&nbsp; <i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($tarih1))}} — {{date('d.m.Y', strtotime($tarih2))}}</span>
            </div>
          </div>
        </div>
        <button type="button" class="pm-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
      </div>
      <div class="pm-body">
        <div class="pm-quick-actions">
          @yetki('personel.odeme_yap')
          <button type="button" class="pm-quick-btn pm-quick-btn--ode pm-quick-ode">
            <i class="fa fa-credit-card"></i> Öde
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--bonus pm-quick-bonus">
            <i class="fa fa-plus-circle"></i> Bonus
          </button>
          <button type="button" class="pm-quick-btn pm-quick-btn--kesinti pm-quick-kesinti">
            <i class="fa fa-minus-circle"></i> Kesinti
          </button>
          @endyetki
          <button type="button" class="pm-quick-btn pm-quick-btn--liste pm-quick-btn--aktif" disabled>
            <i class="fa fa-history"></i> Hareketler
          </button>
        </div>
        <div class="pm-summary" id="primListe_ozet" style="display:none">
          <div class="pm-chip pm-chip-success">Toplam Bonus <strong id="primListe_toplamBonus">0,00 ₺</strong></div>
          <div class="pm-chip pm-chip-danger">Toplam Kesinti <strong id="primListe_toplamKesinti">0,00 ₺</strong></div>
          <div class="pm-chip pm-chip-net">Net Etki <strong id="primListe_netEtki">0,00 ₺</strong></div>
        </div>
        <div id="primHareketListesi">
          <div class="pm-loading"><div class="pm-spinner"></div><div style="margin-top:14px; font-weight:500">Yükleniyor...</div></div>
        </div>
      </div>
      <div class="pm-footer">
        <button type="button" class="pm-btn-primary" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  var _csrf = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
  var _sube = {{$isletme->id}};
  var _tarih1 = '{{$tarih1}}';
  var _tarih2 = '{{$tarih2}}';

  var _ayAdi = $('#primRaporFiltre select[name="ay"] option:selected').text();
  var _yilAdi = $('#primRaporFiltre select[name="yil"] option:selected').text();
  var _isletmeAdi = @json($isletme->salon_adi);
  var _dosyaAdi = 'Prim_Hakedis_'+_ayAdi+'_'+_yilAdi;

  $('#primrapor_tablo').DataTable({
    pageLength: 50,
    order: [[8,'desc']],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json' },
    dom: '<"d-flex justify-content-between align-items-center mb-2"<"d-flex"l><"d-flex"B>>frtip',
    buttons: [
      { extend: 'excelHtml5',  text: '<i class="fa fa-file-excel-o"></i> Excel',  className: 'btn btn-success btn-sm', title: _dosyaAdi, exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
      { extend: 'pdfHtml5',    text: '<i class="fa fa-file-pdf-o"></i> PDF',      className: 'btn btn-danger btn-sm',  title: _isletmeAdi+' - Prim & Hak Ediş ('+_ayAdi+' '+_yilAdi+')', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
      { extend: 'print',       text: '<i class="fa fa-print"></i> Yazdır',         className: 'btn btn-secondary btn-sm', title: _isletmeAdi+' - Prim & Hak Ediş ('+_ayAdi+' '+_yilAdi+')', exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } }
    ]
  });

  var _donem = '{{ sprintf("%04d-%02d", $yil, $ay) }}';
  var _raporData = @json($rapor);
  var _raporIndex = {};
  _raporData.forEach(function(r){ _raporIndex[r.personel_id] = r; });

  function openBonusKesintiModal(pid, adi, tip){
    setAktifPersonel(pid);
    var $m = $('#primHareketModal');
    if($m.parent()[0] !== document.body) $m.appendTo('body');
    $('#primHareket_personelId').val(pid);
    $('#primHareket_personelAdi').text(adi);
    $('#primHareketForm')[0].reset();
    $('#primHareket_personelId').val(pid);
    $('#primHareketForm input[name="tarih"]').val('{{date("Y-m-d")}}');
    var t = tip === 'kesinti' ? 'kesinti' : 'bonus';
    $('#prtip_'+t).prop('checked', true);
    $m.modal('show');
  }

  // ============ Quick-action butonlari (tum modallarda ortak) ============
  var _PRIM_MODAL_IDS = '#primOdeModal,#primHareketModal,#primHareketListeModal,#primOdemeDetayModal';

  function _isModalOpen(sel){
    var el = document.querySelector(sel);
    if(!el) return false;
    return el.classList.contains('show') || el.classList.contains('in')
        || (el.style && el.style.display === 'block');
  }

  function _switchModal(openFn){
    // Acik modal var mi?
    var $active = $(_PRIM_MODAL_IDS).filter(function(){
      return $(this).hasClass('show') || $(this).hasClass('in')
          || $(this).css('display') === 'block';
    });
    if($active.length === 0){
      openFn();
      return;
    }

    // Gecis modunu aktif et: backdrop sabit, animasyonlar yok
    $('body').addClass('prim-switching');

    $active.one('hidden.bs.modal', function(){
      // Hedefi hemen ac (animasyonlar suppress edili)
      openFn();
      // Bir tick sonra gecis modunu kapat ki ileriki acma/kapama normal animasyon yapsin
      setTimeout(function(){
        $('body').removeClass('prim-switching');
        // Safety net: orphan backdrop kalmasin
        if($('.modal.show, .modal.in').length === 1){
          // Tek bir modal acik — fazla backdrop varsa temizle
          var $bds = $('.modal-backdrop');
          if($bds.length > 1){ $bds.slice(1).remove(); }
        }
      }, 80);
    });
    $active.modal('hide');
  }

  $(document).on('click','.pm-quick-ode', function(){
    if(!_aktifPersonel) return;
    var p = _aktifPersonel;
    if(_isModalOpen('#primOdeModal')) return; // zaten Ode modalindayiz
    _switchModal(function(){ openOdeModal(p.id, p.adi); });
  });
  $(document).on('click','.pm-quick-bonus', function(){
    if(!_aktifPersonel) return;
    var p = _aktifPersonel;
    // Zaten Hareket modalindaysak sadece radio'yu degistir
    if(_isModalOpen('#primHareketModal')){
      $('#prtip_bonus').prop('checked', true);
      return;
    }
    _switchModal(function(){ openBonusKesintiModal(p.id, p.adi, 'bonus'); });
  });
  $(document).on('click','.pm-quick-kesinti', function(){
    if(!_aktifPersonel) return;
    var p = _aktifPersonel;
    if(_isModalOpen('#primHareketModal')){
      $('#prtip_kesinti').prop('checked', true);
      return;
    }
    _switchModal(function(){ openBonusKesintiModal(p.id, p.adi, 'kesinti'); });
  });
  $(document).on('click','.pm-quick-detay', function(){
    if(!_aktifPersonel) return;
    var p = _aktifPersonel;
    if(_isModalOpen('#primDetayModal')) return;
    _switchModal(function(){ openPrimDetayModal(p.id, p.adi); });
  });
  $(document).on('click','.pm-quick-hareketler', function(){
    if(!_aktifPersonel) return;
    var p = _aktifPersonel;
    if(_isModalOpen('#primHareketListeModal')) return;
    _switchModal(function(){ openHareketListeModal(p.id, p.adi); });
  });

  // ============ Aktif personel state ============
  var _aktifPersonel = null; // {id, adi, net, odenen, kalan}

  function setAktifPersonel(pid){
    var r = _raporIndex[pid];
    if(!r) return;
    _aktifPersonel = {
      id: pid,
      adi: r.personel_adi,
      net: parseFloat(r.net_hakedis) || 0,
      odenen: parseFloat(r.odenen_toplam) || 0,
      kalan: parseFloat(r.kalan) || 0
    };
  }

  function openOdeModal(pid, adi){
    setAktifPersonel(pid);
    var r = _raporIndex[pid];
    var maas = r ? (parseFloat(r.maas)||0) : 0;
    var primToplam = r ? (parseFloat(r.prim_toplam)||0) : 0;
    var bonus = r ? (parseFloat(r.bonus)||0) : 0;
    var kesinti = r ? (parseFloat(r.kesinti)||0) : 0;
    var bonusKesintiNet = bonus - kesinti;

    var odenenMaas = r ? (parseFloat(r.odenen_maas)||0) : 0;
    var odenenPrim = r ? (parseFloat(r.odenen_prim)||0) : 0;
    var odenenDiger = r ? (parseFloat(r.odenen_diger)||0) : 0;

    var kalanMaas = Math.max(0, maas - odenenMaas);
    var kalanPrim = Math.max(0, primToplam - odenenPrim);
    var kalanDiger = bonusKesintiNet - odenenDiger; // negatif olabilir

    var $m = $('#primOdeModal');
    if($m.parent()[0] !== document.body) $m.appendTo('body');

    $('#primOde_personelId').val(pid);
    $('#primOde_personelAdi').text(adi || (r ? r.personel_adi : ''));

    // Tab altyazilari
    $('#primOde_maasKalan').text(_formatTL(kalanMaas) + ' ₺');
    $('#primOde_primKalan').text(_formatTL(kalanPrim) + ' ₺');
    // Diger/Avans icin: bu ay ne kadar avans verildi gosterilir
    if(odenenDiger > 0){
       $('#primOde_digerKalan').text(_formatTL(odenenDiger)+' ₺ verildi');
    } else {
       $('#primOde_digerKalan').text('Avans Ekle');
    }

    // Cache hak edis verileri (tab degisince ozet update edilir)
    $m.data('hakedis', {
      maas: maas, primToplam: primToplam,
      bonus: bonus, kesinti: kesinti, bonusKesintiNet: bonusKesintiNet,
      odenenMaas: odenenMaas, odenenPrim: odenenPrim, odenenDiger: odenenDiger,
      kalanMaas: kalanMaas, kalanPrim: kalanPrim, kalanDiger: kalanDiger
    });

    $('#primOdeForm')[0].reset();
    $('#primOde_personelId').val(pid);
    $('#primOdeForm input[name="donem"]').val(_donem);
    $('#primOdeForm input[name="odeme_tarihi"]').val('{{date("Y-m-d")}}');

    // Default: Maas tab aktif
    _selectOdeTip('maas');
    $m.modal('show');
  }

  function _selectOdeTip(tip){
    $('#primOde_tipi').val(tip);
    $('.pmtip-tab').removeClass('is-active');
    $('.pmtip-tab[data-tip="'+tip+'"]').addClass('is-active');

    var data = $('#primOdeModal').data('hakedis') || {};
    var $tutar = $('#primOde_tutar');

    if(tip === 'maas'){
      var maas = data.maas || 0;
      var odenenMaas = data.odenenMaas || 0;
      var kalanMaas = data.kalanMaas || 0;
      $('#primOde_netLabel').text(_formatTL(maas) + ' ₺');
      $('#primOde_odenenLabel').text(_formatTL(odenenMaas) + ' ₺');
      $('#primOde_kalanLabel').text(_formatTL(kalanMaas) + ' ₺');
      // Once kalan, kalan yoksa toplam maas
      var dt = kalanMaas > 0 ? kalanMaas : maas;
      $tutar.val(dt > 0 ? dt.toFixed(2) : '');
      $tutar.attr('placeholder', 'Maaş tutarı');
    } else if(tip === 'prim'){
      var prim = data.primToplam || 0;
      var odenenPrim = data.odenenPrim || 0;
      var kalanPrim = data.kalanPrim || 0;
      $('#primOde_netLabel').text(_formatTL(prim) + ' ₺');
      $('#primOde_odenenLabel').text(_formatTL(odenenPrim) + ' ₺');
      $('#primOde_kalanLabel').text(_formatTL(kalanPrim) + ' ₺');
      // Once kalan, kalan yoksa toplam prim
      var dt2 = kalanPrim > 0 ? kalanPrim : prim;
      $tutar.val(dt2 > 0 ? dt2.toFixed(2) : '');
      $tutar.attr('placeholder', 'Prim tutarı');
    } else { // diger / avans
      var odenenDiger = data.odenenDiger || 0;
      // Avans icin 'kalan' kavrami yok — patron istedigi kadar nakit verir
      $('#primOde_netLabel').text('—');
      $('#primOde_odenenLabel').text(_formatTL(odenenDiger) + ' ₺');
      $('#primOde_kalanLabel').text('Manuel');
      $tutar.val('');
      $tutar.attr('placeholder', 'Avans / ek ödeme tutarı');
      // Tutar alanina otomatik focus
      setTimeout(function(){ $tutar.focus(); }, 100);
    }
  }
  $(document).off('click.pmtip').on('click.pmtip', '.pmtip-tab', function(){
    _selectOdeTip($(this).data('tip'));
  });

  function openOdemeDetayModal(pid, adi){
    setAktifPersonel(pid);
    var $m = $('#primOdemeDetayModal');
    if($m.parent()[0] !== document.body) $m.appendTo('body');
    $('#primOdemeDetay_personelAdi').text(adi);
    $('#primOdemeDetay_ozet').hide();
    $('#primOdemeDetay_liste').html('<div class="pm-loading"><div class="pm-spinner"></div><div style="margin-top:14px; font-weight:500">Yükleniyor...</div></div>');
    $m.modal('show');
    primOdemeDetayDoldur(pid);
  }

  // ============ PRIM ODE (tabloda click) ============
  $(document).on('click','.prim-ode', function(){
    var pid = $(this).data('value');
    var adi = $(this).data('adi');
    openOdeModal(pid, adi);
  });

  // ============ Musteri Detay (tabloda kisayol) ============
  $(document).on('click','.prim-musteri-detay', function(){
    var pid = $(this).data('value');
    var adi = $(this).data('adi');
    openPrimDetayModal(pid, adi);
    // Modal acilir acilmaz Musteriler sekmesini aktif et (loading bittikten sonra)
    var tryActivate = function(tries){
      if(tries <= 0) return;
      var $btn = $('#primDetayModal .pdm-tabs button[data-pdm-tab="musteri"]');
      if($('#primDetay_icerik').is(':visible') && $btn.length){
        $btn.trigger('click');
      } else {
        setTimeout(function(){ tryActivate(tries-1); }, 200);
      }
    };
    tryActivate(15); // 3 saniyeye kadar bekle
  });

  // Sayfayi yenilerken aktif tab'i (Prim & Hak Edis ya da Personeller) koru
  function _reloadKeepTab(){
    try {
      var $active = $('.pyo-tabs .nav-link.active');
      var href = $active.attr('href') || '#primHakedis';
      // Bu partial sadece Prim & Hak Edis tabinda yuklendigi icin default 'prim'
      var tabParam = (href === '#personeller') ? 'personeller' : 'prim';
      var url = new URL(window.location.href);
      url.searchParams.set('_tab', tabParam);
      window.location.href = url.toString();
    } catch(err){
      window.location.reload();
    }
  }

  $('#primOdeForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: '/isletmeyonetim/primode',
      method: 'POST',
      data: $(this).serialize(),
      headers: {'X-CSRF-TOKEN': _csrf},
      success: function(res){
        if(res.basarili){
          $('#primOdeModal').modal('hide');
          swal({title:'Ödeme kaydedildi', type:'success', timer:1300, showConfirmButton:false})
            .then(_reloadKeepTab).catch(_reloadKeepTab);
        } else {
          swal({title:'Hata', text: res.mesaj || 'Kaydedilemedi', type:'error'});
        }
      },
      error: function(){ swal({title:'Hata', text:'Sunucu hatası', type:'error'}); }
    });
  });

  // Ödeme tipi -> badge + ikon + renk bilgisi
  function _odemeTipMeta(tip){
    switch((tip||'diger').toLowerCase()){
      case 'maas':  return { etiket:'MAAŞ',  bg:'#eef2ff', fg:'#4338ca', icon:'fa-university'    };
      case 'prim':  return { etiket:'PRİM',  bg:'#dbeafe', fg:'#1e40af', icon:'fa-percent'       };
      case 'diger': return { etiket:'AVANS', bg:'#fef3c7', fg:'#92400e', icon:'fa-clock-o'       };
      default:      return { etiket:'ÖDEME', bg:'#dbeafe', fg:'#1e40af', icon:'fa-credit-card'   };
    }
  }

  // Ödeme geçmişini göster (çoklu ödeme + bonus/kesinti) + tek tek sil
  function primOdemeDetayDoldur(pid){
    var r = _raporIndex[pid];
    $.ajax({
      url: '/isletmeyonetim/primodemelistesi',
      method: 'GET',
      data: { personel_id: pid, sube: _sube, donem: _donem },
      success: function(res){
        var odemeler = (res && res.odemeler) ? res.odemeler : [];
        var hareketler = (res && res.hareketler) ? res.hareketler : [];
        var t = (res && res.toplamlar) ? res.toplamlar : {maas:0,prim:0,diger:0,bonus:0,kesinti:0};

        // Üst özet (toplam/net/kalan)
        var net = r ? parseFloat(r.net_hakedis||0) : 0;
        var odenen = parseFloat((res && res.odenen_toplam) || 0);
        var kalan = Math.max(0, net - odenen);
        $('#primOdemeDetay_toplam').text(_formatTL(odenen)+' ₺');
        $('#primOdemeDetay_net').text(_formatTL(net)+' ₺');
        $('#primOdemeDetay_kalan').text(_formatTL(kalan)+' ₺');
        $('#primOdemeDetay_ozet').css('display','flex');

        // Tip kırılımı
        $('#primOdemeDetay_maas').text(_formatTL(t.maas||0)+' ₺');
        $('#primOdemeDetay_prim').text(_formatTL(t.prim||0)+' ₺');
        $('#primOdemeDetay_diger').text(_formatTL(t.diger||0)+' ₺');
        $('#primOdemeDetay_bonus').text('+'+_formatTL(t.bonus||0)+' ₺');
        $('#primOdemeDetay_kesinti').text('-'+_formatTL(t.kesinti||0)+' ₺');
        $('#primOdemeDetay_kirilim').css('display','flex');

        if(!res || !res.basarili || (odemeler.length===0 && hareketler.length===0)){
          $('#primOdemeDetay_liste').html(
            '<div class="pm-empty">'+
              '<div class="pm-empty__icon"><i class="fa fa-inbox"></i></div>'+
              '<div class="pm-empty__baslik">Bu dönemde kayıt yok</div>'+
            '</div>'
          );
          return;
        }

        // Birleşik liste: ödemeler + bonus/kesinti hareketleri (tarihe göre desc)
        var birlesik = [];
        odemeler.forEach(function(o){
          birlesik.push({
            kind:'odeme',
            id:o.id,
            tarih:o.odeme_tarihi,
            tutar:o.tutar,
            tip:o.odeme_tipi,
            odeme_yontemi:o.odeme_yontemi,
            aciklama:o.aciklama
          });
        });
        hareketler.forEach(function(h){
          birlesik.push({
            kind:h.tip,    // 'bonus' | 'kesinti'
            id:h.id,
            tarih:h.tarih,
            tutar:h.tutar,
            aciklama:h.aciklama
          });
        });
        birlesik.sort(function(a,b){
          var ta = a.tarih || ''; var tb = b.tarih || '';
          if(ta < tb) return 1; if(ta > tb) return -1; return 0;
        });

        var html = '<div class="pm-list">';
        birlesik.forEach(function(it){
          var tarihStr = it.tarih ? (new Date(it.tarih)).toLocaleDateString('tr-TR') : '';
          var tutarStr = _formatTL(it.tutar);

          if(it.kind === 'odeme'){
            var meta = _odemeTipMeta(it.tip);
            html += '<div class="pm-item pm-item--bonus">';
            html += '  <div class="pm-item__icon" style="background:'+meta.bg+'; color:'+meta.fg+'"><i class="fa '+meta.icon+'"></i></div>';
            html += '  <div class="pm-item__body">';
            html += '    <div class="pm-item__row1">';
            html += '      <span class="pm-item__badge" style="background:'+meta.bg+'; color:'+meta.fg+'">'+meta.etiket+'</span>';
            html += '      <span class="pm-item__tutar" style="color:'+meta.fg+'">'+tutarStr+' ₺</span>';
            html += '      <span class="pm-item__tarih"><i class="fa fa-calendar"></i> '+tarihStr+'</span>';
            if(it.odeme_yontemi){ html += '      <span class="pm-item__tarih" style="color:#64748b"><i class="fa fa-money"></i> '+_escHtml(it.odeme_yontemi)+'</span>'; }
            html += '    </div>';
            if(it.aciklama){ html += '    <div class="pm-item__aciklama">'+_escHtml(it.aciklama)+'</div>'; }
            html += '  </div>';
            html += '  <button class="pm-item__sil prim-odeme-sil-tek" data-id="'+it.id+'" title="Bu ödemeyi sil"><i class="fa fa-trash"></i></button>';
            html += '</div>';
          } else if(it.kind === 'bonus'){
            html += '<div class="pm-item pm-item--bonus">';
            html += '  <div class="pm-item__icon" style="background:#dcfce7; color:#16a34a"><i class="fa fa-plus-circle"></i></div>';
            html += '  <div class="pm-item__body">';
            html += '    <div class="pm-item__row1">';
            html += '      <span class="pm-item__badge" style="background:#dcfce7; color:#16a34a">BONUS</span>';
            html += '      <span class="pm-item__tutar" style="color:#16a34a">+'+tutarStr+' ₺</span>';
            html += '      <span class="pm-item__tarih"><i class="fa fa-calendar"></i> '+tarihStr+'</span>';
            html += '    </div>';
            if(it.aciklama){ html += '    <div class="pm-item__aciklama">'+_escHtml(it.aciklama)+'</div>'; }
            html += '  </div>';
            if(_ODEME_YAP){ html += '  <button class="pm-item__sil prim-hareket-sil-tek" data-id="'+it.id+'" title="Bu bonusu sil"><i class="fa fa-trash"></i></button>'; }
            html += '</div>';
          } else if(it.kind === 'kesinti'){
            html += '<div class="pm-item pm-item--kesinti">';
            html += '  <div class="pm-item__icon" style="background:#fee2e2; color:#dc2626"><i class="fa fa-minus-circle"></i></div>';
            html += '  <div class="pm-item__body">';
            html += '    <div class="pm-item__row1">';
            html += '      <span class="pm-item__badge" style="background:#fee2e2; color:#dc2626">KESİNTİ</span>';
            html += '      <span class="pm-item__tutar" style="color:#dc2626">-'+tutarStr+' ₺</span>';
            html += '      <span class="pm-item__tarih"><i class="fa fa-calendar"></i> '+tarihStr+'</span>';
            html += '    </div>';
            if(it.aciklama){ html += '    <div class="pm-item__aciklama">'+_escHtml(it.aciklama)+'</div>'; }
            html += '  </div>';
            if(_ODEME_YAP){ html += '  <button class="pm-item__sil prim-hareket-sil-tek" data-id="'+it.id+'" title="Bu kesintiyi sil"><i class="fa fa-trash"></i></button>'; }
            html += '</div>';
          }
        });
        html += '</div>';
        $('#primOdemeDetay_liste').html(html);
      }
    });
  }

  $(document).on('click','.prim-odeme-detay', function(){
    var pid = $(this).data('value');
    var adi = $(this).data('adi');
    openOdemeDetayModal(pid, adi);
  });

  // Tek bir odemeyi sil
  $(document).on('click','.prim-odeme-sil-tek', function(){
    var odemeId = $(this).data('id');
    if(!odemeId) return;
    swal({
      title: 'Ödeme silinsin mi?',
      text: 'Bu ödeme kaydı silinecek. Diğer ödemeler etkilenmez.',
      type: 'warning', showCancelButton: true,
      confirmButtonText: 'Sil', cancelButtonText: 'Vazgeç',
      confirmButtonClass: 'btn btn-danger'
    }).then(function(r){
      if(!r.value) return;
      $.ajax({
        url: '/isletmeyonetim/primodemesil',
        method: 'POST',
        data: { id: odemeId, sube: _sube, _token: _csrf },
        headers: {'X-CSRF-TOKEN': _csrf},
        success: function(res){
          if(res.basarili){ _reloadKeepTab(); }
          else { swal({title:'Hata', text: res.mesaj || 'Silinemedi', type:'error'}); }
        }
      });
    });
  });

  $('#primHareketForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: '/isletmeyonetim/primhareketekle',
      method: 'POST',
      data: $(this).serialize(),
      headers: {'X-CSRF-TOKEN': _csrf},
      success: function(res){
        if(res.basarili){
          $('#primHareketModal').modal('hide');
          swal({title:'Kaydedildi', type:'success', timer:1200, showConfirmButton:false})
            .then(_reloadKeepTab)
            .catch(_reloadKeepTab);
        } else {
          swal({title:'Hata', text: res.mesaj || 'Kaydedilemedi', type:'error'});
        }
      },
      error: function(){
        swal({title:'Hata', text:'Sunucu hatası', type:'error'});
      }
    });
  });

  var _MAAS_GOR = {!! $_maasGor ? 'true' : 'false' !!};
  var _ODEME_YAP = {!! $_odemeYap ? 'true' : 'false' !!};
  function _formatTL(v){
    if(!_MAAS_GOR) return '****';
    return parseFloat(v||0).toLocaleString('tr-TR',{minimumFractionDigits:2, maximumFractionDigits:2});
  }
  function _escHtml(s){ return $('<div>').text(s||'').html(); }

  function openPrimDetayModal(pid, adi){
    setAktifPersonel(pid);
    var $m = $('#primDetayModal');
    if($m.parent()[0] !== document.body) $m.appendTo('body');
    $('#primDetay_personelAdi').text(adi);
    $('#primDetay_loading').show();
    $('#primDetay_icerik').hide();
    $m.modal('show');

    $.ajax({
      url: '/isletmeyonetim/personelprimdetayi',
      method: 'GET',
      data: { personel_id: pid, sube: _sube, donem: _donem },
      success: function(res){
        if(!res || !res.basarili){
          $('#primDetay_loading').html('<div class="pm-empty"><div class="pm-empty__icon"><i class="fa fa-warning"></i></div><div class="pm-empty__baslik">Veri alınamadı</div></div>');
          return;
        }
        // Ozet
        $('#pdmHizmetPrim').text(_formatTL(res.ozet.hizmet_prim)+' ₺');
        $('#pdmUrunPrim').text(_formatTL(res.ozet.urun_prim)+' ₺');
        $('#pdmPaketPrim').text(_formatTL(res.ozet.paket_prim)+' ₺');
        $('#pdmHizmetCount').text(res.ozet.hizmet_count);
        $('#pdmUrunCount').text(res.ozet.urun_count);
        $('#pdmPaketCount').text(res.ozet.paket_count);
        $('#pdmHizmetYuzde').text(parseFloat(res.oranlar.hizmet||0));
        $('#pdmUrunYuzde').text(parseFloat(res.oranlar.urun||0));
        $('#pdmPaketYuzde').text(parseFloat(res.oranlar.paket||0));
        $('#pdmMusteriCount').text(res.ozet.musteri_sayisi);
        $('#pdmTabHizmetCount').text(res.ozet.hizmet_count);
        $('#pdmTabUrunCount').text(res.ozet.urun_count);
        $('#pdmTabPaketCount').text(res.ozet.paket_count);
        $('#pdmTabMusteriCount').text(res.ozet.musteri_sayisi);

        $('#pdmTbl_hizmet').html(_pdmRenderSatisTablo(res.hizmet_satislari, 'Hizmet'));
        $('#pdmTbl_urun').html(_pdmRenderSatisTablo(res.urun_satislari, 'Ürün'));
        $('#pdmTbl_paket').html(_pdmRenderSatisTablo(res.paket_satislari, 'Paket'));
        $('#pdmTbl_musteri').html(_pdmRenderMusteriTablo(res.musteriler));

        $('#primDetay_loading').hide();
        $('#primDetay_icerik').show();
      },
      error: function(){
        $('#primDetay_loading').html('<div class="pm-empty"><div class="pm-empty__icon"><i class="fa fa-warning"></i></div><div class="pm-empty__baslik">Sunucu hatası</div></div>');
      }
    });
  }

  function _pdmRenderSatisTablo(satislar, baslik){
    if(!satislar || satislar.length===0){
      return '<div class="pdm-empty"><i class="fa fa-inbox"></i>Bu dönemde '+baslik.toLowerCase()+' satışı yok.</div>';
    }
    var html = '<div style="overflow-x:auto"><table class="pdm-table"><thead><tr>'+
      '<th style="width:90px">Tarih</th>'+
      '<th>Müşteri</th>'+
      '<th>'+baslik+'</th>'+
      '<th class="text-right">Fiyat</th>'+
      '<th class="text-right">Tahsil Edilen</th>'+
      '<th class="text-right">Prim %</th>'+
      '<th class="text-right">Prim Tutarı</th>'+
      '</tr></thead><tbody>';
    satislar.forEach(function(s){
      var t = s.tarih ? (new Date(s.tarih)).toLocaleDateString('tr-TR') : '';
      html += '<tr>';
      html += '<td>'+t+'</td>';
      html += '<td>'+_escHtml(s.musteri_adi)+'</td>';
      html += '<td>'+_escHtml(s.urun)+'</td>';
      html += '<td class="text-right">'+_formatTL(s.fiyat)+' ₺</td>';
      html += '<td class="text-right">'+_formatTL(s.tahsil_edilen)+' ₺</td>';
      html += '<td class="text-right">%'+parseFloat(s.prim_yuzdesi||0)+'</td>';
      html += '<td class="text-right pdm-prim-tutar">'+_formatTL(s.prim_tutari)+' ₺</td>';
      html += '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
  }

  function _pdmRenderMusteriTablo(musteriler){
    if(!musteriler || musteriler.length===0){
      return '<div class="pdm-empty"><i class="fa fa-users"></i>Bu dönemde müşteri yok.</div>';
    }
    var html = '<div style="overflow-x:auto"><table class="pdm-table"><thead><tr>'+
      '<th>Müşteri</th>'+
      '<th class="text-right">İşlem Sayısı</th>'+
      '<th class="text-right">Toplam Tahsilat</th>'+
      '<th class="text-right">Personele Düşen Prim</th>'+
      '</tr></thead><tbody>';
    musteriler.forEach(function(m){
      html += '<tr>';
      html += '<td><strong>'+_escHtml(m.musteri_adi)+'</strong></td>';
      html += '<td class="text-right">'+m.islem_sayisi+'</td>';
      html += '<td class="text-right">'+_formatTL(m.toplam_tutar)+' ₺</td>';
      html += '<td class="text-right pdm-prim-tutar">'+_formatTL(m.toplam_prim)+' ₺</td>';
      html += '</tr>';
    });
    html += '</tbody></table></div>';
    return html;
  }

  // Detay modali tab gecisleri
  $(document).off('click.pdm').on('click.pdm', '.pdm-tabs button', function(){
    var t = $(this).data('pdm-tab');
    $('.pdm-tabs button').removeClass('is-active');
    $(this).addClass('is-active');
    $('.pdm-pane').hide();
    $('#pdmPane_'+t).show();
  });

  function openHareketListeModal(pid, adi){
    setAktifPersonel(pid);
    var $m = $('#primHareketListeModal');
    if($m.parent()[0] !== document.body) $m.appendTo('body');
    $('#primListe_personelAdi').text(adi);
    $('#primListe_ozet').hide();
    $('#primHareketListesi').html('<div class="pm-loading"><div class="pm-spinner"></div><div style="margin-top:14px; font-weight:500">Yükleniyor...</div></div>');
    $m.modal('show');

    $.ajax({
      url: '/isletmeyonetim/primhareketlistesi',
      method: 'GET',
      data: { personel_id: pid, sube: _sube, tarih1: _tarih1, tarih2: _tarih2 },
      success: function(res){
        var hareketler = (res && res.hareketler) ? res.hareketler : [];
        var odemeler = (res && res.odemeler) ? res.odemeler : [];

        if(hareketler.length===0 && odemeler.length===0){
          $('#primHareketListesi').html(
            '<div class="pm-empty">'+
              '<div class="pm-empty__icon"><i class="fa fa-inbox"></i></div>'+
              '<div class="pm-empty__baslik">Bu dönemde kayıt yok</div>'+
              '<div class="pm-empty__alt">Tabloda + butonuyla bonus/kesinti, "Öde" butonuyla ödeme ekleyebilirsiniz.</div>'+
            '</div>'
          );
          return;
        }

        // Tum kayitlari tek listede birlestir, tarihe gore sirala (yeni en ustte)
        var birlesik = [];
        hareketler.forEach(function(h){
          birlesik.push({ kind: h.tip, tarih: h.tarih, tutar: h.tutar, id: h.id, aciklama: h.aciklama });
        });
        odemeler.forEach(function(o){
          birlesik.push({ kind: 'odeme', tarih: o.tarih, tutar: o.tutar, id: o.id, aciklama: o.aciklama, odeme_yontemi: o.odeme_yontemi });
        });
        birlesik.sort(function(a,b){
          var ta = a.tarih || '0000-00-00';
          var tb = b.tarih || '0000-00-00';
          if(ta < tb) return 1; if(ta > tb) return -1; return 0;
        });

        var toplamBonus = 0, toplamKesinti = 0, toplamOdeme = 0;
        var html = '<div class="pm-list">';
        birlesik.forEach(function(h){
          var tutarNum = parseFloat(h.tutar||0);
          var tutarStr = _formatTL(h.tutar);
          var tarihStr = h.tarih ? (new Date(h.tarih)).toLocaleDateString('tr-TR') : '';
          var modCls, icon, tipKisaltma, tutarSign, silClass, tutarColor, badgeStyle;

          if(h.kind === 'bonus'){
            toplamBonus += tutarNum;
            modCls = 'pm-item--bonus'; icon = '<i class="fa fa-arrow-up"></i>';
            tipKisaltma = 'BONUS'; tutarSign = '+'; silClass = 'prim-hareket-sil'; badgeStyle='';
          } else if(h.kind === 'kesinti'){
            toplamKesinti += tutarNum;
            modCls = 'pm-item--kesinti'; icon = '<i class="fa fa-arrow-down"></i>';
            tipKisaltma = 'KESİNTİ'; tutarSign = '−'; silClass = 'prim-hareket-sil'; badgeStyle='';
          } else { // odeme
            toplamOdeme += tutarNum;
            modCls = ''; icon = '<i class="fa fa-credit-card"></i>';
            tipKisaltma = 'ÖDEME'; tutarSign = ''; silClass = 'prim-odeme-sil-tek';
            badgeStyle = 'background:#dbeafe; color:#1e40af';
            tutarColor = '#1e40af';
          }

          html += '<div class="pm-item '+modCls+'"' + (h.kind==='odeme' ? ' style="border-left:4px solid #3b82f6"' : '') + '>';
          html += '  <div class="pm-item__icon"' + (h.kind==='odeme' ? ' style="background:#dbeafe; color:#1e40af"' : '') + '>'+icon+'</div>';
          html += '  <div class="pm-item__body">';
          html += '    <div class="pm-item__row1">';
          html += '      <span class="pm-item__badge"' + (badgeStyle ? ' style="'+badgeStyle+'"' : '') + '>'+tipKisaltma+'</span>';
          html += '      <span class="pm-item__tutar"' + (h.kind==='odeme' ? ' style="color:#1e40af"' : '') + '>'+tutarSign+tutarStr+' ₺</span>';
          html += '      <span class="pm-item__tarih"><i class="fa fa-calendar"></i> '+tarihStr+'</span>';
          if(h.odeme_yontemi){ html += '      <span class="pm-item__tarih" style="color:#64748b"><i class="fa fa-money"></i> '+_escHtml(h.odeme_yontemi)+'</span>'; }
          html += '    </div>';
          if(h.aciklama){ html += '    <div class="pm-item__aciklama">'+_escHtml(h.aciklama)+'</div>'; }
          html += '  </div>';
          if(_ODEME_YAP){ html += '  <button class="pm-item__sil '+silClass+'" data-id="'+h.id+'" title="Sil"><i class="fa fa-trash"></i></button>'; }
          html += '</div>';
        });
        html += '</div>';

        $('#primListe_toplamBonus').text(_formatTL(toplamBonus)+' ₺');
        $('#primListe_toplamKesinti').text(_formatTL(toplamKesinti)+' ₺');
        var net = toplamBonus - toplamKesinti;
        $('#primListe_netEtki').text((net>=0?'+':'')+_formatTL(net)+' ₺');
        $('#primListe_ozet').css('display','flex');

        $('#primHareketListesi').html(html);
      }
    });
  }

  $(document).on('click','.prim-hareket-sil, .prim-hareket-sil-tek', function(){
    var id = $(this).data('id');
    swal({
      title: 'Silinsin mi?',
      text: 'Bu prim hareketi silinecek.',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sil',
      cancelButtonText: 'Vazgeç',
      confirmButtonClass: 'btn btn-danger'
    }).then(function(r){
      if(!r.value) return;
      $.ajax({
        url: '/isletmeyonetim/primhareketsil',
        method: 'POST',
        data: { id: id, sube: _sube, _token: _csrf },
        headers: {'X-CSRF-TOKEN': _csrf},
        success: function(res){
          if(res.basarili){
            _reloadKeepTab();
          } else {
            swal({title:'Hata', text: res.mesaj || 'Silinemedi', type:'error'});
          }
        }
      });
    });
  });
});
</script>
