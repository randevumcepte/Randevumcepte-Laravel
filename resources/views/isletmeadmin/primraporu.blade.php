@extends('layout.layout_isletmeadmin')
@section('content')
@php
  $aylar = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
  $toplamMaas = array_sum(array_column($rapor,'maas'));
  $toplamPrim = array_sum(array_column($rapor,'prim_toplam'));
  $toplamBonus = array_sum(array_column($rapor,'bonus'));
  $toplamKesinti = array_sum(array_column($rapor,'kesinti'));
  $toplamNet = array_sum(array_column($rapor,'net_hakedis'));
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
    display:grid; grid-template-columns: repeat(5, 1fr); gap:14px; margin-bottom:22px;
  }
  @media(max-width:1100px){ .pr-stats{ grid-template-columns: repeat(2, 1fr); } }
  @media(max-width:600px){ .pr-stats{ grid-template-columns: 1fr; } }
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
  .pr-stat__val{ font-size:22px; font-weight:700; color:var(--rmc-text); letter-spacing:-.3px; }
  .pr-stat__val small{ font-size:13px; color:var(--rmc-muted); margin-left:4px; font-weight:600; }
  .pr-stat--maas .pr-stat__icon{ background: linear-gradient(135deg,#9D5DC8,#B88ED8); }
  .pr-stat--prim .pr-stat__icon{ background: linear-gradient(135deg,#7B2FB8,#9D5DC8); }
  .pr-stat--bonus .pr-stat__icon{ background: linear-gradient(135deg,#10b981,#34d399); }
  .pr-stat--bonus .pr-stat__val{ color:#059669; }
  .pr-stat--kesinti .pr-stat__icon{ background: linear-gradient(135deg,#ef4444,#f87171); }
  .pr-stat--kesinti .pr-stat__val{ color:#dc2626; }
  .pr-stat--net{
    grid-column: span 1; background: var(--rmc-grad); color:#fff; border:0; position:relative;
  }
  .pr-stat--net::after{
    content:''; position:absolute; top:0; right:0; width:120px; height:120px;
    background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
    border-radius:50%; transform:translate(40%,-40%);
  }
  .pr-stat--net .pr-stat__icon{ background:rgba(255,255,255,.22); backdrop-filter: blur(6px); }
  .pr-stat--net .pr-stat__lbl{ color:rgba(255,255,255,.85); }
  .pr-stat--net .pr-stat__val{ color:#fff; font-size:24px; }

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

  #primrapor_tablo{ width:100% !important; border-collapse: separate; border-spacing:0; }
  #primrapor_tablo thead th{
    background:#fafbfc !important; color:var(--rmc-muted); font-size:11px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase; border:0 !important;
    padding:14px 12px !important; vertical-align:middle; white-space:nowrap;
  }
  #primrapor_tablo tbody td{
    border-bottom:1px solid #f4f0f8 !important; border-top:0 !important; padding:14px 12px !important;
    vertical-align:middle; font-size:13.5px; color:var(--rmc-text);
  }
  #primrapor_tablo tbody tr:hover td{ background:#fbfaff !important; }
  #primrapor_tablo tbody tr:last-child td{ border-bottom:0 !important; }
  .pr-cell-personel{ font-weight:700; font-size:14px; }
  .pr-cell-personel .pr-avatar{
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px; border-radius:50%; background:var(--rmc-purple-bg); color:var(--rmc-purple-1);
    font-weight:700; font-size:13px; margin-right:10px; vertical-align:middle;
  }
  .pr-cell-bonus{ color:#059669; font-weight:600; }
  .pr-cell-kesinti{ color:#dc2626; font-weight:600; }
  .pr-cell-net{
    background: linear-gradient(90deg, rgba(123,47,184,.06), rgba(157,93,200,.04));
    border-radius:8px;
  }
  .pr-cell-net strong{ color:var(--rmc-purple-1) !important; font-size:15px; }
  .pr-action-btn{
    width:34px; height:34px; border-radius:10px; border:0; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center; transition:all .15s;
    margin-right:4px;
  }
  .pr-action-btn--ekle{ background:var(--rmc-success-bg); color:var(--rmc-success); }
  .pr-action-btn--ekle:hover{ background:var(--rmc-success); color:#fff; transform:translateY(-1px); }
  .pr-action-btn--liste{ background:var(--rmc-purple-bg); color:var(--rmc-purple-1); }
  .pr-action-btn--liste:hover{ background:var(--rmc-purple-2); color:#fff; transform:translateY(-1px); }

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

  /* ============ Prim Hareket Modal — Modern Tasarim ============ */
  #primHareketListeModal .modal-dialog,
  #primHareketModal .modal-dialog{
    max-width: 720px !important;
    width: 92vw;
    margin: 1.75rem auto !important;
    display: flex; align-items: center; min-height: calc(100vh - 3.5rem);
  }
  #primHareketListeModal .modal-content,
  #primHareketModal .modal-content{
    border: 0;
    border-radius: 22px;
    box-shadow: var(--rmc-shadow-lg);
    overflow: hidden;
    width: 100%;
    animation: primModalIn .35s cubic-bezier(.2,.8,.2,1);
  }
  @keyframes primModalIn{ from{ opacity:0; transform: translateY(20px) scale(.96);} to{ opacity:1; transform: translateY(0) scale(1);} }

  #primHareketListeModal .modal-header,
  #primHareketModal .modal-header{
    background: var(--rmc-grad);
    color: #fff; border: 0; padding: 22px 28px;
    position: relative;
  }
  #primHareketListeModal .modal-header::before,
  #primHareketModal .modal-header::before{
    content:''; position:absolute; top:-40px; right:-40px; width:160px; height:160px;
    background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
    border-radius:50%;
  }
  #primHareketListeModal .modal-header > div,
  #primHareketModal .modal-header > div{ position:relative; z-index:2; }
  #primHareketListeModal .modal-header .modal-title,
  #primHareketModal .modal-header .modal-title{
    color: #fff; font-weight: 700; font-size: 18px; display: flex; align-items: center; gap: 10px; margin:0;
  }
  #primHareketListeModal .modal-header .close,
  #primHareketModal .modal-header .close{
    color: #fff; opacity: .85; font-size: 28px; font-weight: 300; text-shadow: none;
    position: absolute; right: 22px; top: 18px; z-index:3;
  }
  #primHareketListeModal .modal-header .close:hover,
  #primHareketModal .modal-header .close:hover{ opacity: 1; }
  .prim-modal-personel{
    display:inline-block; margin-top:8px; padding:4px 14px; background:rgba(255,255,255,.22);
    border-radius:20px; font-size:12px; font-weight:600; backdrop-filter: blur(6px);
  }
  .prim-modal-donem{
    color: rgba(255,255,255,.92); font-size:12px; margin-left:8px; font-weight:500;
  }

  #primHareketListeModal .modal-body{ padding: 24px 28px; background:#fbfafc; }
  #primHareketModal .modal-body{ padding: 24px 28px; }
  #primHareketListeModal .modal-footer,
  #primHareketModal .modal-footer{
    border-top: 1px solid var(--rmc-border); padding: 14px 24px; background:#fff;
  }

  /* Ozet kartlari (liste modal usten) */
  .prim-ozet-row{ display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:20px; }
  .prim-ozet-card{
    padding:14px 16px; border-radius:14px; background:#fff;
    box-shadow: var(--rmc-shadow-sm); border:1px solid var(--rmc-border);
  }
  .prim-ozet-card .lbl{ font-size:11px; color:var(--rmc-muted); font-weight:700; letter-spacing:.5px; text-transform:uppercase; }
  .prim-ozet-card .val{ font-size:18px; font-weight:700; margin-top:4px; }
  .prim-ozet-card.bonus .val{ color:var(--rmc-success); }
  .prim-ozet-card.kesinti .val{ color:var(--rmc-danger); }
  .prim-ozet-card.net .val{ color:var(--rmc-purple-1); }

  /* Hareket karti */
  .hareketler-listesi{
    max-height: 360px; overflow-y: auto;
    background: transparent; border: 0; padding: 0;
    display: flex; flex-direction: column; gap: 10px;
  }
  .hareketler-listesi::-webkit-scrollbar{ width:6px; }
  .hareketler-listesi::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:3px; }

  .hareket-item{
    display: flex !important; align-items:center; gap: 14px;
    background: #fff; border-radius: 12px; padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04); border-left: 4px solid transparent;
    transition: all .2s; border-bottom: 0;
  }
  .hareket-item:hover{ box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateX(2px); }
  .hareket-item.tip-bonus{ border-left-color:var(--rmc-success); }
  .hareket-item.tip-kesinti{ border-left-color:var(--rmc-danger); }

  .hareket-icon{
    width: 42px; height: 42px; border-radius: 50%; display:flex; align-items:center; justify-content:center;
    flex-shrink: 0; font-size: 18px;
  }
  .hareket-icon.bonus{ background:var(--rmc-success-bg); color:var(--rmc-success); }
  .hareket-icon.kesinti{ background:var(--rmc-danger-bg); color:var(--rmc-danger); }

  .hareket-info{ flex:1; min-width:0; }
  .hareket-info .row1{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
  .hareket-info .tutar{ font-size: 17px; font-weight: 700; }
  .hareket-info .tutar.bonus{ color:var(--rmc-success); }
  .hareket-info .tutar.kesinti{ color:var(--rmc-danger); }
  .hareket-info .tarih{ font-size: 12px; color:#9ca3af; font-weight:500; display:inline-flex; align-items:center; gap:4px; }
  .hareket-info .aciklama{ font-size: 13px; color:#4b5563; margin-top: 4px; line-height: 1.45; }

  .prim-hareket-sil{
    cursor:pointer; width: 34px; height:34px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    color:#94a3b8; background:transparent; border:0; transition:all .2s; flex-shrink:0;
  }
  .prim-hareket-sil:hover{ color:#ef4444; background:#fee2e2; }

  /* Empty state */
  .hareket-empty{
    text-align:center; padding: 40px 20px; color:#9ca3af;
  }
  .hareket-empty .icon{
    width:72px; height:72px; border-radius:50%; background:#f3f4f6;
    display:inline-flex; align-items:center; justify-content:center; font-size:32px;
    color:#cbd5e1; margin-bottom:14px;
  }
  .hareket-empty .baslik{ font-size:15px; font-weight:600; color:#6b7280; margin-bottom:4px; }
  .hareket-empty .alt{ font-size:13px; color:#9ca3af; }

  /* Bonus/Kesinti ekleme modal — tip secici buyuk butonlar */
  .prim-tip-radio{ display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
  .prim-tip-radio input[type=radio]{ display:none; }
  .prim-tip-radio label{
    cursor:pointer; padding:14px 16px; border-radius:12px; border:2px solid #e5e7eb;
    text-align:center; font-weight:600; transition:all .15s; margin:0;
    display:flex; flex-direction:column; align-items:center; gap:6px;
  }
  .prim-tip-radio label .ic{ font-size: 22px; }
  .prim-tip-radio input[value=bonus]:checked + label{ border-color:var(--rmc-success); background:var(--rmc-success-bg); color:#065f46; }
  .prim-tip-radio input[value=kesinti]:checked + label{ border-color:var(--rmc-danger); background:var(--rmc-danger-bg); color:#991b1b; }
  .prim-tip-radio input[value=bonus] + label .ic{ color:var(--rmc-success); }
  .prim-tip-radio input[value=kesinti] + label .ic{ color:var(--rmc-danger); }

  .prim-form-group{ margin-bottom: 16px; }
  .prim-form-group label{ font-weight:700; color:var(--rmc-text); font-size:13px; margin-bottom:6px; display:block; }
  .prim-form-group .form-control{ border-radius:12px; border:2px solid var(--rmc-border); padding: 10px 14px; font-size:14px; background:#fafbfc; }
  .prim-form-group .form-control:focus{ border-color:var(--rmc-purple-2); background:#fff; box-shadow: 0 0 0 4px rgba(123,47,184,.08); }
  .prim-tutar-input{ position:relative; }
  .prim-tutar-input .form-control{ padding-left: 36px; font-size:18px; font-weight:700; }
  .prim-tutar-input::before{ content:'₺'; position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--rmc-purple-2); font-size:16px; font-weight:700; z-index:2; }

  .prim-btn-kaydet{
    background: var(--rmc-grad); border:0;
    color:#fff; font-weight:600; padding:10px 28px; border-radius:12px;
    transition:all .2s; box-shadow: 0 4px 14px rgba(92,0,142,.3);
  }
  .prim-btn-kaydet:hover{ transform: translateY(-1px); box-shadow: 0 8px 22px rgba(92,0,142,.4); color:#fff; }
  .prim-btn-iptal{
    background:#f3f0f7; color:var(--rmc-purple-1); border:0; padding:10px 22px; border-radius:12px; font-weight:600;
  }
  .prim-btn-iptal:hover{ background:var(--rmc-purple-bg); color:var(--rmc-purple-1); }

  @media(max-width:600px){
    .prim-ozet-row{ grid-template-columns: 1fr; }
    #primHareketListeModal .modal-dialog,
    #primHareketModal .modal-dialog{ width: 96vw; }
  }
</style>

<div class="pr-hero">
  <div class="pr-hero__inner">
    <div class="pr-hero__title">
      <div class="pr-hero__icon"><i class="fa fa-money"></i></div>
      <div>
        <h1>{{$sayfa_baslik}}</h1>
        <p>Personel başına aylık maaş, prim ve hak ediş takibi</p>
      </div>
    </div>
    <div class="pr-hero__period">
      <i class="fa fa-calendar"></i>
      {{date('d.m.Y', strtotime($tarih1))}} — {{date('d.m.Y', strtotime($tarih2))}}
    </div>
  </div>
</div>

<form method="get" id="primRaporFiltre" class="pr-filter">
  <input type="hidden" name="sube" value="{{$isletme->id}}">
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
  <div class="pr-filter__group" style="font-size:12px; color:var(--rmc-muted)">
    <i class="fa fa-info-circle"></i> Ay/Yıl seçimini değiştirdiğinizde rapor otomatik yenilenir.
  </div>
</form>

<div class="pr-stats">
  <div class="pr-stat pr-stat--maas">
    <div class="pr-stat__icon">₺</div>
    <div class="pr-stat__lbl">Toplam Maaş</div>
    <div class="pr-stat__val">{{number_format($toplamMaas,2,',','.')}} <small>₺</small></div>
  </div>
  <div class="pr-stat pr-stat--prim">
    <div class="pr-stat__icon">%</div>
    <div class="pr-stat__lbl">Toplam Prim</div>
    <div class="pr-stat__val">{{number_format($toplamPrim,2,',','.')}} <small>₺</small></div>
  </div>
  <div class="pr-stat pr-stat--bonus">
    <div class="pr-stat__icon">＋</div>
    <div class="pr-stat__lbl">Toplam Bonus</div>
    <div class="pr-stat__val">{{number_format($toplamBonus,2,',','.')}} <small>₺</small></div>
  </div>
  <div class="pr-stat pr-stat--kesinti">
    <div class="pr-stat__icon">−</div>
    <div class="pr-stat__lbl">Toplam Kesinti</div>
    <div class="pr-stat__val">{{number_format($toplamKesinti,2,',','.')}} <small>₺</small></div>
  </div>
  <div class="pr-stat pr-stat--net">
    <div class="pr-stat__icon"><i class="fa fa-credit-card"></i></div>
    <div class="pr-stat__lbl">Net Ödenecek</div>
    <div class="pr-stat__val">{{number_format($toplamNet,2,',','.')}} <small>₺</small></div>
  </div>
</div>

<div class="pr-table-card">
  <div class="pr-table-toolbar">
    <h3><i class="fa fa-users"></i> Personel Bazında Hak Ediş</h3>
  </div>
  <div style="padding: 0 14px 6px">
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
          <th class="datatable-nosort">İşlemler</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rapor as $r)
          @php $bas = mb_strtoupper(mb_substr($r['personel_adi'],0,1,'UTF-8'),'UTF-8'); @endphp
          <tr>
            <td class="pr-cell-personel"><span class="pr-avatar">{{$bas}}</span>{{$r['personel_adi']}}</td>
            <td>{{number_format($r['maas'],2,',','.')}} ₺</td>
            <td>{{number_format($r['hizmet_primi'],2,',','.')}} ₺</td>
            <td>{{number_format($r['urun_primi'],2,',','.')}} ₺</td>
            <td>{{number_format($r['paket_primi'],2,',','.')}} ₺</td>
            <td><strong>{{number_format($r['prim_toplam'],2,',','.')}} ₺</strong></td>
            <td class="pr-cell-bonus">+{{number_format($r['bonus'],2,',','.')}}@if($r['hareket_sayisi']>0) <small style="color:var(--rmc-muted); font-weight:500">({{$r['hareket_sayisi']}})</small>@endif</td>
            <td class="pr-cell-kesinti">−{{number_format($r['kesinti'],2,',','.')}}</td>
            <td class="pr-cell-net"><strong>{{number_format($r['net_hakedis'],2,',','.')}} ₺</strong></td>
            <td>
              <button class="pr-action-btn pr-action-btn--ekle prim-bonus-ekle" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Bonus/Kesinti Ekle">
                <i class="fa fa-plus"></i>
              </button>
              <button class="pr-action-btn pr-action-btn--liste prim-hareket-goster" data-value="{{$r['personel_id']}}" data-adi="{{$r['personel_adi']}}" title="Hareketleri Görüntüle">
                <i class="fa fa-list"></i>
              </button>
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
      <form id="primHareketForm">
        {!!csrf_field()!!}
        <input type="hidden" name="sube" value="{{$isletme->id}}">
        <input type="hidden" name="personel_id" id="primHareket_personelId">
        <div class="modal-header">
          <div>
            <h4 class="modal-title"><i class="fa fa-plus-circle"></i> <span>Prim Hareketi Ekle</span></h4>
            <span class="prim-modal-personel" id="primHareket_personelAdi"></span>
          </div>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="prim-form-group">
            <label>Hareket Tipi</label>
            <div class="prim-tip-radio">
              <input type="radio" id="prtip_bonus" name="tip" value="bonus" checked>
              <label for="prtip_bonus"><span class="ic">＋</span>Bonus / Ek Ödeme</label>
              <input type="radio" id="prtip_kesinti" name="tip" value="kesinti">
              <label for="prtip_kesinti"><span class="ic">−</span>Kesinti</label>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="prim-form-group">
                <label>Tutar</label>
                <div class="prim-tutar-input">
                  <input type="number" step="0.01" min="0.01" class="form-control" name="tutar" placeholder="0,00" required>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="prim-form-group">
                <label>Tarih</label>
                <input type="date" class="form-control" name="tarih" value="{{date('Y-m-d')}}" required>
              </div>
            </div>
          </div>
          <div class="prim-form-group">
            <label>Açıklama <small style="color:#9ca3af; font-weight:400">(opsiyonel)</small></label>
            <textarea class="form-control" name="aciklama" rows="2" maxlength="300" placeholder="Ör: Ay sonu performans bonusu / Geç gelme kesintisi"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="prim-btn-iptal" data-dismiss="modal">İptal</button>
          <button type="submit" class="prim-btn-kaydet"><i class="fa fa-check"></i> Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ========== Hareket Geçmişi Modal ========== --}}
<div class="modal fade" id="primHareketListeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h4 class="modal-title"><i class="fa fa-history"></i> <span>Prim Hareketleri</span></h4>
          <span class="prim-modal-personel" id="primListe_personelAdi"></span>
          <span class="prim-modal-donem"><i class="fa fa-calendar"></i> {{date('d.m.Y', strtotime($tarih1))}} — {{date('d.m.Y', strtotime($tarih2))}}</span>
        </div>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="prim-ozet-row" id="primListe_ozet" style="display:none">
          <div class="prim-ozet-card bonus">
            <div class="lbl">Toplam Bonus</div>
            <div class="val" id="primListe_toplamBonus">0,00 ₺</div>
          </div>
          <div class="prim-ozet-card kesinti">
            <div class="lbl">Toplam Kesinti</div>
            <div class="val" id="primListe_toplamKesinti">0,00 ₺</div>
          </div>
          <div class="prim-ozet-card net">
            <div class="lbl">Net Etki</div>
            <div class="val" id="primListe_netEtki">0,00 ₺</div>
          </div>
        </div>
        <div class="hareketler-listesi" id="primHareketListesi">
          <div class="text-center text-muted" style="padding:30px">Yükleniyor...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="prim-btn-iptal" data-dismiss="modal">Kapat</button>
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

  $(document).on('click','.prim-bonus-ekle', function(){
    $('#primHareket_personelId').val($(this).data('value'));
    $('#primHareket_personelAdi').text($(this).data('adi'));
    $('#primHareketForm')[0].reset();
    $('#primHareket_personelId').val($(this).data('value'));
    $('#primHareketForm input[name="tarih"]').val('{{date("Y-m-d")}}');
    $('#primHareketModal').modal('show');
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
            .then(()=>location.reload())
            .catch(()=>location.reload());
        } else {
          swal({title:'Hata', text: res.mesaj || 'Kaydedilemedi', type:'error'});
        }
      },
      error: function(){
        swal({title:'Hata', text:'Sunucu hatası', type:'error'});
      }
    });
  });

  function _formatTL(v){ return parseFloat(v||0).toLocaleString('tr-TR',{minimumFractionDigits:2, maximumFractionDigits:2}); }
  function _escHtml(s){ return $('<div>').text(s||'').html(); }

  $(document).on('click','.prim-hareket-goster', function(){
    var pid = $(this).data('value');
    var adi = $(this).data('adi');
    $('#primListe_personelAdi').text(adi);
    $('#primListe_ozet').hide();
    $('#primHareketListesi').html('<div class="text-center text-muted" style="padding:30px"><i class="fa fa-spinner fa-spin fa-2x" style="color:#6366f1"></i><div style="margin-top:10px">Yükleniyor...</div></div>');
    $('#primHareketListeModal').modal('show');

    $.ajax({
      url: '/isletmeyonetim/primhareketlistesi',
      method: 'GET',
      data: { personel_id: pid, sube: _sube, tarih1: _tarih1, tarih2: _tarih2 },
      success: function(res){
        if(!res.basarili || !res.hareketler || res.hareketler.length===0){
          $('#primHareketListesi').html(
            '<div class="hareket-empty">'+
              '<div class="icon"><i class="fa fa-inbox"></i></div>'+
              '<div class="baslik">Bu dönemde kayıt yok</div>'+
              '<div class="alt">Tabloda "+" butonuyla bonus veya kesinti ekleyebilirsiniz.</div>'+
            '</div>'
          );
          return;
        }

        var toplamBonus = 0, toplamKesinti = 0;
        var html = '';
        res.hareketler.forEach(function(h){
          var isBonus = h.tip === 'bonus';
          var tutarNum = parseFloat(h.tutar||0);
          if(isBonus) toplamBonus += tutarNum; else toplamKesinti += tutarNum;
          var tutarStr = _formatTL(h.tutar);
          var tarihStr = h.tarih ? (new Date(h.tarih)).toLocaleDateString('tr-TR') : '';
          var icon = isBonus ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>';
          var tipKisaltma = isBonus ? 'BONUS' : 'KESİNTİ';
          var tipBadge = isBonus ? 'prim-tip-bonus' : 'prim-tip-kesinti';
          var tutarSign = isBonus ? '+' : '−';

          html += '<div class="hareket-item tip-'+(isBonus?'bonus':'kesinti')+'">';
          html += '  <div class="hareket-icon '+(isBonus?'bonus':'kesinti')+'">'+icon+'</div>';
          html += '  <div class="hareket-info">';
          html += '    <div class="row1">';
          html += '      <span class="prim-tip-badge '+tipBadge+'">'+tipKisaltma+'</span>';
          html += '      <span class="tutar '+(isBonus?'bonus':'kesinti')+'">'+tutarSign+tutarStr+' ₺</span>';
          html += '      <span class="tarih"><i class="fa fa-calendar"></i> '+tarihStr+'</span>';
          html += '    </div>';
          if(h.aciklama){ html += '    <div class="aciklama">'+_escHtml(h.aciklama)+'</div>'; }
          html += '  </div>';
          html += '  <button class="prim-hareket-sil" data-id="'+h.id+'" title="Sil"><i class="fa fa-trash"></i></button>';
          html += '</div>';
        });

        $('#primListe_toplamBonus').text(_formatTL(toplamBonus)+' ₺');
        $('#primListe_toplamKesinti').text(_formatTL(toplamKesinti)+' ₺');
        var net = toplamBonus - toplamKesinti;
        $('#primListe_netEtki').text((net>=0?'+':'')+_formatTL(net)+' ₺').css('color', net>=0?'#10b981':'#ef4444');
        $('#primListe_ozet').css('display','grid');

        $('#primHareketListesi').html(html);
      }
    });
  });

  $(document).on('click','.prim-hareket-sil', function(){
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
            location.reload();
          } else {
            swal({title:'Hata', text: res.mesaj || 'Silinemedi', type:'error'});
          }
        }
      });
    });
  });
});
</script>
@endsection
