@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
  // Ciro/Kar gosterim yetkisi — kapaliysa kazanc tutarlari **** maskelenir
  $_ciroKarGor = true;
  try {
    $_uid = \Auth::guard('isletmeyonetim')->user()->id ?? null;
    if ($_uid && isset($isletme)) {
      $_ciroKarGor = \App\Services\PersonelYetkiServisi::yetkiliYetkiVar($_uid, $isletme->id, 'rapor.ciro_kar_gor');
    }
  } catch (\Throwable $e) { $_ciroKarGor = true; }
  $_fmtCK = function($v) use ($_ciroKarGor){ return $_ciroKarGor ? number_format($v,2,',','.') : '****'; };
@endphp

<style>
:root{
   --rpr-brand:#5C008E;
   --rpr-brand-2:#9D5DC8;
   --rpr-fuchsia:#d946ef;
   --rpr-bg:#f6f7fb;
   --rpr-card:#ffffff;
   --rpr-ink:#0f172a;
   --rpr-muted:#64748b;
   --rpr-line:#eef0f6;
   --rpr-shadow:0 4px 18px rgba(92,0,142,.06);
   --rpr-shadow-lg:0 14px 38px rgba(92,0,142,.10);
}
.rpr-wrap{ background:var(--rpr-bg); margin:-15px; padding:24px; min-height:calc(100vh - 60px); }
.rpr-wrap *{ box-sizing:border-box; }

/* HEADER */
.rpr-hero{
   background:linear-gradient(135deg, var(--rpr-brand) 0%, var(--rpr-brand-2) 60%, var(--rpr-fuchsia) 100%);
   color:#fff; border-radius:20px; padding:26px 28px;
   display:flex; align-items:center; justify-content:space-between; gap:16px;
   box-shadow: 0 18px 42px rgba(92,0,142,.22);
   margin-bottom:22px; position:relative; overflow:hidden;
}
.rpr-hero::after{
   content:""; position:absolute; right:-60px; top:-60px;
   width:240px; height:240px; border-radius:50%;
   background:radial-gradient(circle, rgba(255,255,255,.18), transparent 70%);
}
.rpr-hero h1{ font-size:24px; font-weight:800; margin:0 0 6px; letter-spacing:-.2px; }
.rpr-hero .rpr-crumb{ font-size:13px; opacity:.92; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.rpr-hero .rpr-crumb a{ color:#fff; text-decoration:none; opacity:.9; }
.rpr-hero .rpr-crumb a:hover{ opacity:1; text-decoration:underline; }
.rpr-hero .rpr-hero-icon{
   width:62px; height:62px; border-radius:18px;
   background:rgba(255,255,255,.18); backdrop-filter:blur(6px);
   display:flex; align-items:center; justify-content:center;
   font-size:30px; color:#fff; flex-shrink:0; z-index:1;
   border:1px solid rgba(255,255,255,.25);
}

/* TABS */
.rpr-tabs-card{
   background:var(--rpr-card); border-radius:18px;
   box-shadow:var(--rpr-shadow); border:1px solid var(--rpr-line);
   overflow:hidden;
}
.rpr-tabs{
   display:flex; gap:8px; padding:14px 14px 0;
   border-bottom:1px solid var(--rpr-line);
   overflow-x:auto; scrollbar-width:thin;
   list-style:none; margin:0; flex-wrap:nowrap;
}
.rpr-tabs::-webkit-scrollbar{ height:4px; }
.rpr-tabs::-webkit-scrollbar-thumb{ background:#e5e7eb; border-radius:4px; }
.rpr-tabs li{ list-style:none; }
.rpr-tab-btn{
   border:none; background:transparent;
   padding:12px 22px; font-size:14px; font-weight:600;
   color:var(--rpr-muted); cursor:pointer;
   border-radius:12px 12px 0 0; position:relative;
   white-space:nowrap; transition:.18s;
   display:flex; align-items:center; gap:8px;
   margin-bottom:-1px;
}
.rpr-tab-btn:hover{ color:var(--rpr-brand); background:#faf5ff; }
.rpr-tab-btn.active{
   color:var(--rpr-brand); background:#faf5ff;
}
.rpr-tab-btn.active::after{
   content:""; position:absolute; left:14px; right:14px; bottom:-1px; height:3px;
   background:linear-gradient(90deg, var(--rpr-brand), var(--rpr-fuchsia));
   border-radius:3px 3px 0 0;
}
.rpr-tab-btn i{ font-size:16px; }

.rpr-tab-body{ padding:24px; }

/* FILTERS */
.rpr-filter{
   background:#fbfbfd; border:1px solid var(--rpr-line); border-radius:14px;
   padding:18px 20px; margin-bottom:22px;
}
.rpr-filter .rpr-filter-grid{
   display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:14px;
}
.rpr-filter label{
   display:block; font-size:12px; color:var(--rpr-muted);
   font-weight:600; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px;
}
.rpr-filter .form-control,
.rpr-filter input.form-control,
.rpr-filter select.form-control{
   border:1px solid #e2e6ee; border-radius:10px;
   padding:10px 12px; height:42px; font-size:14px;
   background:#fff; color:var(--rpr-ink);
   width:100%; transition:.15s;
}
.rpr-filter .form-control:focus{
   border-color:var(--rpr-brand-2);
   box-shadow:0 0 0 3px rgba(157,93,200,.15);
   outline:none;
}
@media (max-width: 992px){
   .rpr-filter .rpr-filter-grid{ grid-template-columns:repeat(2, 1fr); }
}
@media (max-width: 560px){
   .rpr-filter .rpr-filter-grid{ grid-template-columns:1fr; }
}

/* KPI CARDS */
.rpr-kpis{
   display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:16px; margin-bottom:22px;
}
@media (max-width: 900px){ .rpr-kpis{ grid-template-columns:1fr; } }
.rpr-kpi{
   background:var(--rpr-card); border:1px solid var(--rpr-line); border-radius:16px;
   padding:20px; display:flex; align-items:center; gap:16px;
   box-shadow:var(--rpr-shadow); transition:.2s;
   position:relative; overflow:hidden;
}
.rpr-kpi:hover{ transform:translateY(-2px); box-shadow:var(--rpr-shadow-lg); }
.rpr-kpi::before{
   content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
   background:var(--_accent, var(--rpr-brand));
}
.rpr-kpi .rpr-kpi-icon{
   width:54px; height:54px; border-radius:14px;
   display:flex; align-items:center; justify-content:center;
   font-size:24px; color:#fff; font-weight:700;
   background:var(--_accent, var(--rpr-brand));
   flex-shrink:0;
}
.rpr-kpi .rpr-kpi-data{ flex:1; min-width:0; }
.rpr-kpi .rpr-kpi-label{
   font-size:12px; color:var(--rpr-muted); font-weight:600;
   text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;
}
.rpr-kpi .rpr-kpi-value{
   font-size:22px; font-weight:800; color:var(--rpr-ink);
   line-height:1.2; word-break:break-all;
}
.rpr-kpi .rpr-kpi-value::after{ content:" ₺"; font-size:13px; color:var(--rpr-muted); font-weight:600; }
.rpr-kpi--gelir{ --_accent: linear-gradient(135deg,#06b6d4,#0891b2); }
.rpr-kpi--gelir::before{ background:#06b6d4; }
.rpr-kpi--gelir .rpr-kpi-icon{ background:linear-gradient(135deg,#06b6d4,#0891b2); }
.rpr-kpi--kazanc{ --_accent: linear-gradient(135deg, var(--rpr-brand), var(--rpr-brand-2)); }
.rpr-kpi--kazanc::before{ background:var(--rpr-brand); }
.rpr-kpi--kazanc .rpr-kpi-icon{ background:linear-gradient(135deg, var(--rpr-brand), var(--rpr-brand-2)); }
.rpr-kpi--alacak{ --_accent: linear-gradient(135deg, var(--rpr-fuchsia), #ec4899); }
.rpr-kpi--alacak::before{ background:var(--rpr-fuchsia); }
.rpr-kpi--alacak .rpr-kpi-icon{ background:linear-gradient(135deg, var(--rpr-fuchsia), #ec4899); }

/* TABLE WRAPPER */
.rpr-table-card{
   background:var(--rpr-card); border:1px solid var(--rpr-line); border-radius:16px;
   padding:6px 8px 14px; box-shadow:var(--rpr-shadow); overflow:hidden;
}
.rpr-table-card .data-table{ width:100% !important; }
.rpr-table-card table.dataTable thead th{
   background:#faf7fc; color:#475569; font-weight:700;
   font-size:12px; text-transform:uppercase; letter-spacing:.4px;
   border-bottom:1px solid var(--rpr-line) !important;
   padding:14px 16px !important;
}
.rpr-table-card table.dataTable tbody td{
   padding:13px 16px !important; font-size:14px; color:#334155;
   border-bottom:1px solid #f3f4f8 !important;
}
.rpr-table-card table.dataTable tbody tr:hover td{ background:#fbf8ff !important; }
.rpr-table-card .dataTables_wrapper .dataTables_filter input{
   border:1px solid #e2e6ee; border-radius:10px;
   padding:8px 12px; height:38px; margin-left:8px;
}
.rpr-table-card .dataTables_wrapper .dataTables_length select{
   border:1px solid #e2e6ee; border-radius:8px; padding:4px 8px;
}
.rpr-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
.rpr-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
   background:linear-gradient(135deg, var(--rpr-brand), var(--rpr-brand-2)) !important;
   color:#fff !important; border:none !important; border-radius:8px;
}
.rpr-table-card .dataTables_wrapper .dataTables_paginate .paginate_button{
   border-radius:8px; margin:0 2px;
}
.rpr-table-card .btn-info{
   background:#eef2ff; color:var(--rpr-brand); border:none;
   border-radius:10px; padding:6px 12px; transition:.15s;
}
.rpr-table-card .btn-info:hover{
   background:linear-gradient(135deg, var(--rpr-brand), var(--rpr-brand-2));
   color:#fff;
}

/* SELECT2 OVERRIDE */
.rpr-filter .select2-container--default .select2-selection--single{
   height:42px; border:1px solid #e2e6ee; border-radius:10px;
}
.rpr-filter .select2-container--default .select2-selection--single .select2-selection__rendered{
   line-height:40px; padding-left:12px; color:var(--rpr-ink);
}
.rpr-filter .select2-container--default .select2-selection--single .select2-selection__arrow{
   height:40px;
}

/* PERSONEL TAB - no KPIs, just spacing */
#personel_raporlari .rpr-filter{ margin-bottom:22px; }
</style>

<div class="rpr-wrap">

   {{-- HERO --}}
   <div class="rpr-hero">
      <div class="rpr-hero-icon"><i class="dw dw-bar-chart"></i></div>
      <div style="flex:1; min-width:0; position:relative; z-index:1;">
         <h1>{{ $sayfa_baslik }}</h1>
         <nav aria-label="breadcrumb" role="navigation" class="rpr-crumb">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
            <span>›</span>
            <span style="font-weight:600;">{{ $sayfa_baslik }}</span>
         </nav>
      </div>
   </div>

   {{-- TABS CARD --}}
   <div class="rpr-tabs-card">

      <ul class="rpr-tabs nav nav-tabs element" role="tablist">
         <li class="nav-item">
            <button type="button" class="rpr-tab-btn nav-link active"
               data-toggle="tab" href="#tum_raporlari" role="tab" aria-selected="true">
               <i class="dw dw-layers"></i> Tümü
            </button>
         </li>
         <li class="nav-item">
            <button type="button" class="rpr-tab-btn nav-link"
               data-toggle="tab" href="#hizmet_raporlari" role="tab" aria-selected="false">
               <i class="dw dw-list"></i> Hizmet Raporları
            </button>
         </li>
         <li class="nav-item">
            <button type="button" class="rpr-tab-btn nav-link"
               data-toggle="tab" href="#urun_raporlari" role="tab" aria-selected="false">
               <i class="dw dw-package"></i> Ürün Raporları
            </button>
         </li>
         <li class="nav-item">
            <button type="button" class="rpr-tab-btn nav-link"
               data-toggle="tab" href="#paket_raporlari" role="tab" aria-selected="false">
               <i class="dw dw-gift"></i> Paket Raporları
            </button>
         </li>
         <li class="nav-item">
            <button type="button" class="rpr-tab-btn nav-link"
               data-toggle="tab" href="#personel_raporlari" role="tab" aria-selected="false">
               <i class="dw dw-user"></i> Personel Raporları
            </button>
         </li>
      </ul>

      <div class="tab-content rpr-tab-body">

         {{-- ============ TÜMÜ (HİZMET + ÜRÜN + PAKET) ============ --}}
         <div class="tab-pane fade show active" id="tum_raporlari" role="tab-panel">

            <div class="rpr-filter">
               <div class="rpr-filter-grid">
                  <div>
                     <label>Zaman Aralığı</label>
                     <select class="form-control" id="tum_rapor_zamana_gore_filtre">
                        <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                        <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                        <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                        <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                        <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                        <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                        <option value="ozel">Özel</option>
                     </select>
                  </div>
                  <div id="tum_rapor_ozel_tarih_filtresi_1" style="display:none;">
                     <label>Başlangıç Tarihi</label>
                     <input class="form-control" placeholder="Başlangıç Tarihini seçiniz.." type="text" id="tum_rapor_baslangic_tarihi" />
                  </div>
                  <div id="tum_rapor_ozel_tarih_filtresi_2" style="display:none;">
                     <label>Bitiş Tarihi</label>
                     <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="tum_rapor_bitis_tarihi" />
                  </div>
                  <div>
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id="tumRaporPersonelFiltre" style="width:100%">
                        <option></option>
                     </select>
                  </div>
               </div>
            </div>

            <div class="rpr-kpis">
               <div class="rpr-kpi rpr-kpi--gelir">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Gelir</div>
                     <div class="rpr-kpi-value" id="tumGeliri">{{number_format($tumRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--kazanc">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Kazanç</div>
                     <div class="rpr-kpi-value" id="tumKazanci">{{$_fmtCK($tumRaporlari->sum('toplamKazancNumeric'))}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--alacak">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Kalan Alacak</div>
                     <div class="rpr-kpi-value" id="tumBorc">{{number_format($tumRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                  </div>
               </div>
            </div>

            <div class="rpr-table-card">
               <table class="data-table table stripe hover nowrap" id="tum_rapor_tablo">
                  <thead>
                     <th>Tür</th>
                     <th>Ad</th>
                     <th>Adet</th>
                     <th>Gelir ₺</th>
                     <th>Toplam Kazanç ₺</th>
                     <th>Kalan Alacak ₺</th>
                  </thead>
                  <tbody>
                  @foreach($tumRaporlari as $rapor)
                     <tr>
                        <td>{{ $rapor->tur }}</td>
                        <td>{{ $rapor->ad }}</td>
                        <td>{{ $rapor->adet }}</td>
                        <td>{{ $rapor->toplam_tutar }}</td>
                        <td>{{ $rapor->toplamKazanc }}</td>
                        <td>{{ $rapor->borc }}</td>
                     </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
         </div>

         {{-- ============ HİZMET RAPORLARI ============ --}}
         <div class="tab-pane fade show" id="hizmet_raporlari" role="tab-panel">

            <div class="rpr-filter">
               <div class="rpr-filter-grid">
                  <div>
                     <label>Zaman Aralığı</label>
                     <select class="form-control" id="hizmet_rapor_zamana_gore_filtre">
                        <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                        <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                        <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                        <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                        <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                        <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                        <option value="ozel">Özel</option>
                     </select>
                  </div>
                  <div id="hizmet_rapor_ozel_tarih_filtresi_1" style="display:none;">
                     <label>Başlangıç Tarihi</label>
                     <input class="form-control" placeholder="Başlangıç Tarihini seçiniz.." type="text" id="hizmet_rapor_baslangic_tarihi" />
                  </div>
                  <div id="hizmet_rapor_ozel_tarih_filtresi_2" style="display:none;">
                     <label>Bitiş Tarihi</label>
                     <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="hizmet_rapor_bitis_tarihi" />
                  </div>
                  <div>
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id="hizmetRaporPersonelFiltre" style="width:100%">
                        <option></option>
                     </select>
                  </div>
               </div>
            </div>

            <div class="rpr-kpis">
               <div class="rpr-kpi rpr-kpi--gelir">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Hizmet Geliri</div>
                     <div class="rpr-kpi-value" id="hizmetGeliri">{{number_format($hizmetRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--kazanc">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Kazanç</div>
                     <div class="rpr-kpi-value" id="hizmetKazanci">{{$_fmtCK($hizmetRaporlari->sum('toplamKazancNumeric'))}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--alacak">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Kalan Alacak</div>
                     <div class="rpr-kpi-value" id="hizmetBorc">{{number_format($hizmetRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                  </div>
               </div>
            </div>

            <div class="rpr-table-card">
               <table class="data-table table stripe hover nowrap" id="hizmet_rapor_tablo">
                  <thead>
                     <th>Hizmet</th>
                     <th>Toplam Verilen Hizmet Sayısı</th>
                     <th>Hizmet Geliri ₺</th>
                     <th>Toplam Kazanç ₺</th>
                     <th>Kalan Alacak ₺</th>
                     <th>İşlemler</th>
                  </thead>
                  <tbody>
                  @foreach($hizmetRaporlari as $rapor)
                     <tr>
                        <td>{{ $rapor->hizmet_adi }}</td>
                        <td>{{ $rapor->adet }}</td>
                        <td>{{ $rapor->toplam_tutar }}</td>
                        <td>{{ $rapor->toplamKazanc }}</td>
                        <td>{{ $rapor->borc }}</td>
                        <td>
                           <a title="Detaylı Bilgi" name="hizmeti_alan_musteriler" data-value="{{ $rapor->id }}" data-adi="{{ $rapor->hizmet_adi }}" href="javascript:void(0)" class="btn btn-info">
                              <i class='dw dw-eye'></i>
                           </a>
                        </td>
                     </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
         </div>

         {{-- ============ ÜRÜN RAPORLARI ============ --}}
         <div class="tab-pane fade show" id="urun_raporlari" role="tab-panel">

            <div class="rpr-filter">
               <div class="rpr-filter-grid">
                  <div>
                     <label>Zaman Aralığı</label>
                     <select class="form-control" id="urun_rapor_zamana_gore_filtre">
                        <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                        <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                        <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                        <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                        <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                        <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                        <option value="ozel">Özel</option>
                     </select>
                  </div>
                  <div id="urun_rapor_ozel_tarih_filtresi_1" style="display:none;">
                     <label>Başlangıç Tarihi</label>
                     <input class="form-control" placeholder="Başlangıç Tarihini seçiniz.." type="text" id="urun_rapor_baslangic_tarihi" />
                  </div>
                  <div id="urun_rapor_ozel_tarih_filtresi_2" style="display:none;">
                     <label>Bitiş Tarihi</label>
                     <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="urun_rapor_bitis_tarihi" />
                  </div>
                  <div>
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id="urunRaporPersonelFiltre" style="width:100%">
                        <option></option>
                     </select>
                  </div>
               </div>
            </div>

            <div class="rpr-kpis">
               <div class="rpr-kpi rpr-kpi--gelir">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Ürün Geliri</div>
                     <div class="rpr-kpi-value" id="urunGeliri">{{number_format($urunRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--kazanc">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Kazanç</div>
                     <div class="rpr-kpi-value" id="urunKazanci">{{$_fmtCK($urunRaporlari->sum('toplamKazancNumeric'))}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--alacak">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Kalan Alacak</div>
                     <div class="rpr-kpi-value" id="urunBorc">{{number_format($urunRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                  </div>
               </div>
            </div>

            <div class="rpr-table-card">
               <table class="data-table table stripe hover nowrap" id="urun_rapor_tablo">
                  <thead>
                     <th>Ürün</th>
                     <th>Adet</th>
                     <th>Ürün Geliri ₺</th>
                     <th>Toplam Kazanç ₺</th>
                     <th>Kalan Alacak ₺</th>
                     <th>İşlemler</th>
                  </thead>
                  <tbody>
                  @foreach($urunRaporlari as $rapor)
                     <tr>
                        <td>{{ $rapor->urun_adi }}</td>
                        <td>{{ $rapor->adet }}</td>
                        <td>{{ $rapor->toplam_tutar }}</td>
                        <td>{{ $rapor->toplamKazanc }}</td>
                        <td>{{ $rapor->borc }}</td>
                        <td>
                           <a title="Detaylı Bilgi" href='' class='btn btn-info'>
                              <i class='dw dw-eye'></i>
                           </a>
                        </td>
                     </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
         </div>

         {{-- ============ PAKET RAPORLARI ============ --}}
         <div class="tab-pane fade show" id="paket_raporlari" role="tab-panel">

            <div class="rpr-filter">
               <div class="rpr-filter-grid">
                  <div>
                     <label>Zaman Aralığı</label>
                     <select class="form-control" id="paket_rapor_zamana_gore_filtre">
                        <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                        <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                        <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                        <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                        <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                        <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                        <option value="ozel">Özel</option>
                     </select>
                  </div>
                  <div id="paket_rapor_ozel_tarih_filtresi_1" style="display:none;">
                     <label>Başlangıç Tarihi</label>
                     <input class="form-control" placeholder="Başlangıç Tarihini seçiniz.." type="text" id="paket_rapor_baslangic_tarihi" />
                  </div>
                  <div id="paket_rapor_ozel_tarih_filtresi_2" style="display:none;">
                     <label>Bitiş Tarihi</label>
                     <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="paket_rapor_bitis_tarihi" />
                  </div>
                  <div>
                     <label>Personele Göre Filtrele</label>
                     <select class="form-control personel_secimi" id="paketRaporPersonelFiltre" style="width:100%">
                        <option></option>
                     </select>
                  </div>
               </div>
            </div>

            <div class="rpr-kpis">
               <div class="rpr-kpi rpr-kpi--gelir">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Paket Geliri</div>
                     <div class="rpr-kpi-value" id="paketGeliri">{{number_format($paketRaporlari->sum('toplamTutarNumeric'),2,',','.')}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--kazanc">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Toplam Kazanç</div>
                     <div class="rpr-kpi-value" id="paketKazanci">{{$_fmtCK($paketRaporlari->sum('toplamKazancNumeric'))}}</div>
                  </div>
               </div>
               <div class="rpr-kpi rpr-kpi--alacak">
                  <div class="rpr-kpi-icon">₺</div>
                  <div class="rpr-kpi-data">
                     <div class="rpr-kpi-label">Kalan Alacak</div>
                     <div class="rpr-kpi-value" id="paketBorc">{{number_format($paketRaporlari->sum('borcNumeric'),2,',','.')}}</div>
                  </div>
               </div>
            </div>

            <div class="rpr-table-card">
               <table class="data-table table stripe hover nowrap" id="paket_rapor_tablo">
                  <thead>
                     <th>Paket</th>
                     <th>Adet</th>
                     <th>Paket Geliri ₺</th>
                     <th>Toplam Kazanç ₺</th>
                     <th>Kalan Alacak ₺</th>
                     <th>İşlemler</th>
                  </thead>
                  <tbody>
                  @foreach($paketRaporlari as $rapor)
                     <tr>
                        <td>{{ $rapor->paket_adi }}</td>
                        <td>{{ $rapor->adet }}</td>
                        <td>{{ $rapor->toplam_tutar }}</td>
                        <td>{{ $rapor->toplamKazanc }}</td>
                        <td>{{ $rapor->borc }}</td>
                        <td>
                           <a title="Detaylı Bilgi" href='' class='btn btn-info'>
                              <i class='dw dw-eye'></i>
                           </a>
                        </td>
                     </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
         </div>

         {{-- ============ PERSONEL RAPORLARI ============ --}}
         <div class="tab-pane fade show" id="personel_raporlari" role="tab-panel">

            <div class="rpr-filter">
               <div class="rpr-filter-grid">
                  <div>
                     <label>Zaman Aralığı</label>
                     <select class="form-control" id="personel_rapor_zamana_gore_filtre">
                        <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                        <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
                        <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                        <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
                        <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                        <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
                        <option value="ozel">Özel</option>
                     </select>
                  </div>
                  <div id="personel_rapor_ozel_tarih_filtresi_1" style="display:none;">
                     <label>Başlangıç Tarihi</label>
                     <input class="form-control" placeholder="Başlangıç Tarihini seçiniz.." type="text" id="personel_rapor_baslangic_tarihi" />
                  </div>
                  <div id="personel_rapor_ozel_tarih_filtresi_2" style="display:none;">
                     <label>Bitiş Tarihi</label>
                     <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="personel_rapor_bitis_tarihi" />
                  </div>
               </div>
            </div>

            <div class="rpr-table-card">
               <table class="data-table table stripe hover nowrap" id="personel_rapor_tablo">
                  <thead>
                     <th>Personel Adı</th>
                     <th>Hizmet Geliri ₺</th>
                     <th>Hizmet Primi ₺</th>
                     <th>Ürün Geliri ₺</th>
                     <th>Ürün Primi ₺</th>
                     <th>Paket Geliri ₺</th>
                     <th>Paket Primi ₺</th>
                  </thead>
                  <tbody>
                  @foreach($personelRaporlari as $rapor)
                     <tr>
                        <td>{{ $rapor['personel_adi'] }}</td>
                        <td>{{ number_format($rapor['hizmet_geliri'],2,',','.') }}</td>
                        <td>{{ number_format($rapor['hizmet_primi'],2,',','.') }}</td>
                        <td>{{ number_format($rapor['urun_geliri'],2,',','.') }}</td>
                        <td>{{ number_format($rapor['urun_primi'],2,',','.') }}</td>
                        <td>{{ number_format($rapor['paket_geliri'],2,',','.') }}</td>
                        <td>{{ number_format($rapor['paket_primi'],2,',','.') }}</td>
                     </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
         </div>

      </div>
   </div>

</div>

<style>
#hizmetiAlanMusterilerModal{
   position: fixed !important;
   top: 0 !important; left: 0 !important;
   right: 0 !important; bottom: 0 !important;
   width: 100vw !important; height: 100vh !important;
   z-index: 10550 !important;
   display: none;
   overflow-x: hidden;
   overflow-y: auto;
}
#hizmetiAlanMusterilerModal.show{ display: block; }
#hizmetiAlanMusterilerModal .modal-dialog{
   max-width: 1100px;
   width: 95%;
   margin: 1.5rem auto !important;
   min-height: calc(100% - 3rem);
   display: flex;
   align-items: center;
   justify-content: center;
   pointer-events: none;
}
#hizmetiAlanMusterilerModal .modal-content{
   width: 100%;
   pointer-events: auto;
}
#hizmetiAlanMusterilerModal .modal-content{
   border: none;
   border-radius: 16px;
   overflow: hidden;
   box-shadow: 0 20px 50px rgba(15,23,42,0.18);
}
#hizmetiAlanMusterilerModal .ham-accent-bar{
   height: 4px;
   background: linear-gradient(90deg, #5C008E 0%, #9D5DC8 50%, #d946ef 100%);
}
#hizmetiAlanMusterilerModal .ham-header{
   background: #ffffff;
   color: #1e293b;
   padding: 22px 28px;
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 16px;
   border-bottom: 1px solid #f1f5f9;
}
#hizmetiAlanMusterilerModal .ham-header .ham-icon{
   width: 48px; height: 48px;
   border-radius: 12px;
   background: #faf5ff;
   color: #5C008E;
   display:flex; align-items:center; justify-content:center;
   font-size: 22px;
   flex-shrink:0;
}
#hizmetiAlanMusterilerModal .ham-header h4{
   margin:0; font-size:19px; font-weight:700; color:#0f172a;
}
#hizmetiAlanMusterilerModal .ham-header .ham-sub{
   font-size:13px; color:#64748b; margin-top:3px; font-weight:500;
}
#hizmetiAlanMusterilerModal .ham-close{
   color:#94a3b8; background:#f1f5f9;
   border:none; border-radius:10px; width:38px; height:38px;
   font-size:22px; line-height:1; cursor:pointer; transition:.15s;
}
#hizmetiAlanMusterilerModal .ham-close:hover{ background:#e2e8f0; color:#475569; }
#hizmetiAlanMusterilerModal .ham-body{
   padding: 22px 28px;
   background: #f8fafc;
   max-height: 65vh;
   overflow-y: auto;
}
#hizmetiAlanMusterilerModal .ham-summary{
   display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;
}
#hizmetiAlanMusterilerModal .ham-chip{
   background:#fff; border:1px solid #e2e8f0; border-radius:12px;
   padding:12px 18px; font-size:13px; color:#64748b; font-weight:500;
   display:flex; align-items:baseline; gap:8px;
}
#hizmetiAlanMusterilerModal .ham-chip strong{ color:#0f172a; font-size:16px; font-weight:700; }
#hizmetiAlanMusterilerModal .ham-chip.ham-chip-success strong{ color:#16a34a; }
#hizmetiAlanMusterilerModal .ham-chip.ham-chip-danger strong{ color:#dc2626; }
#hizmetiAlanMusterilerModal .ham-table{
   width:100%; border-collapse: separate; border-spacing:0;
   background:#fff; border-radius:12px; overflow:hidden;
   border:1px solid #e2e8f0;
}
#hizmetiAlanMusterilerModal .ham-table thead th{
   background: #f8fafc;
   color:#475569; font-weight:600; font-size:12px;
   padding:14px 14px; text-align:left; border:none;
   text-transform:uppercase; letter-spacing:.5px;
   border-bottom:1px solid #e2e8f0;
}
#hizmetiAlanMusterilerModal .ham-table tbody td{
   padding:14px 14px; border-bottom:1px solid #f1f5f9; font-size:14px; color:#334155;
}
#hizmetiAlanMusterilerModal .ham-table tbody tr:last-child td{ border-bottom:none; }
#hizmetiAlanMusterilerModal .ham-table tbody tr:hover td{ background:#f8fafc; }
#hizmetiAlanMusterilerModal .ham-table .ham-musteri{ font-weight:600; color:#0f172a; }
#hizmetiAlanMusterilerModal .ham-table .ham-tel{ color:#64748b; font-size:13px; }
#hizmetiAlanMusterilerModal .ham-table .ham-personel-badge{
   display:inline-block; padding:4px 12px; border-radius:20px;
   background:#faf5ff; color:#5C008E; font-size:12px; font-weight:600;
}
#hizmetiAlanMusterilerModal .ham-table .ham-amount{ font-weight:600; text-align:right; white-space:nowrap; }
#hizmetiAlanMusterilerModal .ham-table .ham-fiyat{ color:#0f172a; }
#hizmetiAlanMusterilerModal .ham-table .ham-odenen{ color:#16a34a; }
#hizmetiAlanMusterilerModal .ham-table .ham-kalan{ color:#dc2626; }
#hizmetiAlanMusterilerModal .ham-empty{
   text-align:center; padding:50px 20px; color:#94a3b8;
}
#hizmetiAlanMusterilerModal .ham-empty .ham-empty-icon{
   font-size:48px; margin-bottom:12px; opacity:.6;
}
#hizmetiAlanMusterilerModal .ham-loading{
   text-align:center; padding:60px 20px; color:#64748b;
}
#hizmetiAlanMusterilerModal .ham-spinner{
   display:inline-block; width:40px; height:40px;
   border:3px solid #e2e8f0; border-top-color:#5C008E;
   border-radius:50%; animation: hamSpin .8s linear infinite;
}
@keyframes hamSpin { to { transform: rotate(360deg); } }
#hizmetiAlanMusterilerModal .ham-footer{
   padding:16px 28px; background:#fff;
   display:flex; justify-content:flex-end; border-top:1px solid #f1f5f9;
}
#hizmetiAlanMusterilerModal .ham-footer .btn-kapat{
   background:linear-gradient(135deg, #5C008E, #9D5DC8); color:#fff; border:none;
   padding:10px 26px; border-radius:10px;
   font-weight:600; font-size:14px; cursor:pointer; transition:.15s;
}
#hizmetiAlanMusterilerModal .ham-footer .btn-kapat:hover{ filter:brightness(1.08); }

@media (max-width: 768px){
   #hizmetiAlanMusterilerModal .ham-header{ padding:18px; }
   #hizmetiAlanMusterilerModal .ham-body{ padding:16px; }
   #hizmetiAlanMusterilerModal .ham-table thead th,
   #hizmetiAlanMusterilerModal .ham-table tbody td{ padding:10px 8px; font-size:12px; }
}
</style>
<div class="modal fade" id="hizmetiAlanMusterilerModal" tabindex="-1" role="dialog" aria-labelledby="hizmetiAlanMusterilerModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="ham-accent-bar"></div>
         <div class="ham-header">
            <div style="display:flex; align-items:center; gap:16px; flex:1; min-width:0;">
               <div class="ham-icon"><i class="dw dw-eye"></i></div>
               <div style="flex:1; min-width:0;">
                  <h4 id="hizmetiAlanMusterilerModalLabel">Hizmeti Alan Müşteriler</h4>
                  <div class="ham-sub" id="hizmetiAlanMusteriler_hizmetAdi"></div>
               </div>
            </div>
            <button type="button" class="ham-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
         </div>
         <div class="ham-body">
            <div id="hizmetiAlanMusteriler_icerik">
               <div class="ham-loading">
                  <div class="ham-spinner"></div>
                  <div style="margin-top:14px; font-weight:500;">Yükleniyor...</div>
               </div>
            </div>
         </div>
         <div class="ham-footer">
            <button type="button" class="btn-kapat" data-dismiss="modal">Kapat</button>
         </div>
      </div>
   </div>
</div>

@endsection()
