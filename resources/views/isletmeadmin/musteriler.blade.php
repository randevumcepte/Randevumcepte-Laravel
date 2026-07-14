@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-mu-page">

   {{-- Modern Page Header --}}
   <div class="rc-mu-header">
      <div class="rc-mu-header-left">
         <div class="rc-mu-title-row">
            <div class="rc-mu-icon-bubble"><i class="fa fa-users"></i></div>
            <div>
               <h1 class="rc-mu-title">Müşteriler</h1>
               <nav class="rc-mu-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-mu-sep">›</span>
                  <span class="rc-mu-active">Müşteriler</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-mu-header-right">
         @yetki('musteri.ekle_duzenle')
         <a href="#" data-toggle="modal" data-target="#musteri-bilgi-modal" class="rc-mu-btn rc-mu-btn-success yanitli_musteri_ekleme yenieklebuton501"><i class="fa fa-plus"></i><span>Yeni Müşteri</span></a>
         @endyetki
         @if(!Auth::guard('satisortakligi')->check())
         @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'musteri.ekle_duzenle'))
         <a href="#" data-toggle="modal" data-target="#toplu-musteri-modal" class="rc-mu-btn rc-mu-btn-primary yenieklebuton502"><i class="fa fa-user-plus"></i><span>Toplu Müşteri Ekle</span></a>
         @endif
         @else
         <a href="#" data-toggle="modal" data-target="#toplu-musteri-modal" class="rc-mu-btn rc-mu-btn-primary yenieklebuton502"><i class="fa fa-user-plus"></i><span>Toplu Müşteri Ekle</span></a>
         @endif
      </div>
   </div>

   {{-- Modern Sekmeler --}}
   <ul class="nav nav-tabs rc-mu-tabs" role="tablist">
      <li class="nav-item">
         <button class="rc-mu-tab active" data-toggle="tab" href="#tum-musteriler" role="tab" aria-selected="true">
            <i class="fa fa-list-ul"></i> Tümü <span class="rc-mu-tab-count">{{ $tumMusteriSayisi }}</span>
         </button>
      </li>
      <li class="nav-item">
         <button class="rc-mu-tab" data-toggle="tab" href="#sadik-musteriler" role="tab" aria-selected="false">
            <i class="fa fa-star"></i> Sadık Müşteriler <span class="rc-mu-tab-count">{{ $sadikMusterilerSayisi }}</span>
         </button>
      </li>
      <li class="nav-item">
         <button class="rc-mu-tab" data-toggle="tab" href="#aktif-musteriler" role="tab" aria-selected="false">
            <i class="fa fa-bolt"></i> Aktif Müşteriler <span class="rc-mu-tab-count">{{ $azIslemYapanlarSayisi }}</span>
         </button>
      </li>
      <li class="nav-item">
         <button class="rc-mu-tab" data-toggle="tab" href="#pasif-musteriler" role="tab" aria-selected="false">
            <i class="fa fa-moon-o"></i> Pasif Müşteriler <span class="rc-mu-tab-count">{{ $odemeYapmayanlarSayisi }}</span>
         </button>
      </li>
      <li class="nav-item">
         <button class="rc-mu-tab" data-toggle="tab" href="#yeni-musteriler" role="tab" aria-selected="false">
            <i class="fa fa-user-plus"></i> Yeni Eklenen Müşteriler <span class="rc-mu-tab-count">{{ $yeniMusteriSayisi }}</span>
         </button>
      </li>
   </ul>

   <div class="tab-content rc-mu-tab-content">

      {{-- TÜMÜ --}}
      <div class="tab-pane fade show active" id="tum-musteriler" role="tab-panel">
         <div class="rc-mu-card">
            <div class="rc-mu-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-mu-table" id="musteri_tablo" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Telefon</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Randevusu</th>
                        <th>Randevu Sayısı</th>
                        <th>Toplam Alınan Ücret</th>
                        <th class="datatable-nosort rc-mu-col-actions" data-label="İşlemler"></th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>
      </div>

      {{-- SADIK --}}
      <div class="tab-pane fade show" id="sadik-musteriler" role="tab-panel">
         <div class="rc-mu-note">
            <i class="fa fa-info-circle"></i>
            <div><b>Sadık müşteri nedir?</b> 3 ay içinde en az 3 hizmet, ürün veya paket satın alan müşteri kategorisidir.</div>
            <span class="rc-mu-note-badge">Sadık İndirimi (%): {{$isletme->sadik_musteri_indirim_yuzde}}</span>
         </div>
         <div class="rc-mu-card">
            <div class="rc-mu-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-mu-table" id="musteri_tablo_sadik" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Telefon</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Randevusu</th>
                        <th>Randevu Sayısı</th>
                        <th>Toplam Alınan Ücret</th>
                        <th class="datatable-nosort rc-mu-col-actions" data-label="İşlemler"></th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>
      </div>

      {{-- AKTİF --}}
      <div class="tab-pane fade show" id="aktif-musteriler" role="tab-panel">
         <div class="rc-mu-note">
            <i class="fa fa-info-circle"></i>
            <div><b>Aktif müşteri nedir?</b> Pasif müşterilerden en az 1 hizmet, ürün veya paket satın alan müşteri kategorisidir.</div>
            <span class="rc-mu-note-badge">Aktif İndirimi (%): {{$isletme->aktif_musteri_indirim_yuzde}}</span>
         </div>
         <div class="rc-mu-card">
            <div class="rc-mu-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-mu-table" id="musteri_tablo_aktif" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Telefon</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Randevusu</th>
                        <th>Randevu Sayısı</th>
                        <th>Toplam Alınan Ücret</th>
                        <th class="datatable-nosort rc-mu-col-actions" data-label="İşlemler"></th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>
      </div>

      {{-- PASİF --}}
      <div class="tab-pane fade show" id="pasif-musteriler" role="tab-panel">
         <div class="rc-mu-note">
            <i class="fa fa-info-circle"></i>
            <div><b>Pasif müşteri nedir?</b> Hiçbir şekilde hizmet, ürün veya paket satın almamış müşteri kategorisidir.</div>
         </div>
         <div class="rc-mu-card">
            <div class="rc-mu-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-mu-table" id="musteri_tablo_pasif" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Telefon</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Randevusu</th>
                        <th>Randevu Sayısı</th>
                        <th>Toplam Alınan Ücret</th>
                        <th class="datatable-nosort rc-mu-col-actions" data-label="İşlemler"></th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>
      </div>

      {{-- YENİ EKLENEN --}}
      <div class="tab-pane fade show" id="yeni-musteriler" role="tab-panel">
         <div class="rc-mu-note">
            <i class="fa fa-info-circle"></i>
            <div><b>Yeni eklenen müşteriler:</b> {{ \Carbon\Carbon::parse(\App\Http\Controllers\StoreAdminController::YENI_MUSTERI_BASLANGIC)->format('d.m.Y') }} tarihinden itibaren portföye eklenen müşteriler, en yeniden eskiye doğru listelenir.</div>
         </div>
         <div class="rc-mu-card">
            <div class="rc-mu-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-mu-table" id="musteri_tablo_yeni" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Telefon</th>
                        <th>Kayıt Tarihi</th>
                        <th>Son Randevusu</th>
                        <th>Randevu Sayısı</th>
                        <th>Toplam Alınan Ücret</th>
                        <th class="datatable-nosort rc-mu-col-actions" data-label="İşlemler"></th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>
      </div>

   </div>
</div>

<style>
/* =================================================================
   MÜŞTERİLER — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */
.rc-mu-page {
   --rc-purple-dark: #5C008E;
   --rc-purple: #9D5DC8;
   --rc-purple-light: #f5eefe;
   --rc-purple-soft: #ead4ff;
   --rc-success: #16a34a;
   --rc-warning: #f59e0b;
   --rc-danger: #dc2626;
   --rc-info: #2563eb;
   --rc-text: #1f2937;
   --rc-text-soft: #6b7280;
   --rc-border: #eef0f4;
}

/* === HEADER === */
.rc-mu-header {
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 16px;
   flex-wrap: wrap;
   padding: 18px 22px;
   margin-bottom: 18px;
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
}
.rc-mu-header-left,
.rc-mu-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
.rc-mu-title-row { display: flex; align-items: center; gap: 14px; }
.rc-mu-icon-bubble {
   width: 46px; height: 46px;
   border-radius: 12px;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   color: #fff;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   font-size: 18px;
   box-shadow: 0 6px 18px rgba(92, 0, 142, .25);
   flex-shrink: 0;
}
.rc-mu-title { margin: 0; font-size: 19px; font-weight: 700; color: var(--rc-text); line-height: 1.2; }
.rc-mu-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.rc-mu-breadcrumb a { color: var(--rc-text-soft); text-decoration: none; transition: color .15s; }
.rc-mu-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-mu-breadcrumb .rc-mu-sep { color: #cbd5e1; }
.rc-mu-breadcrumb .rc-mu-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === BUTONLAR === */
.rc-mu-btn {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   gap: 8px;
   height: 42px;
   padding: 0 16px;
   border-radius: 999px;
   font-size: 13px;
   font-weight: 600;
   color: #fff !important;
   text-decoration: none !important;
   border: none;
   white-space: nowrap;
   cursor: pointer;
   transition: transform .15s, box-shadow .15s, filter .15s;
}
.rc-mu-btn i { font-size: 14px; }
.rc-mu-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-mu-btn:active { transform: translateY(0); }
.rc-mu-btn-success { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); box-shadow: 0 6px 16px rgba(22, 163, 74, .28); }
.rc-mu-btn-primary { background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%); box-shadow: 0 6px 16px rgba(92, 0, 142, .28); }
.rc-mu-btn-info { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); box-shadow: 0 6px 16px rgba(37, 99, 235, .26); }

/* === SEKMELER === */
.rc-mu-tabs {
   display: flex;
   flex-wrap: wrap;
   gap: 8px;
   border: none !important;
   padding: 8px;
   margin-bottom: 18px;
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
}
.rc-mu-tabs .nav-item { margin: 0; }
.rc-mu-tab {
   display: inline-flex;
   align-items: center;
   gap: 8px;
   height: 40px;
   padding: 0 16px;
   border-radius: 10px;
   border: 1px solid transparent;
   background: transparent;
   color: var(--rc-text-soft);
   font-size: 13px;
   font-weight: 600;
   cursor: pointer;
   transition: background .15s, color .15s, box-shadow .15s;
}
.rc-mu-tab i { font-size: 13px; opacity: .9; }
.rc-mu-tab:hover { background: var(--rc-purple-light); color: var(--rc-purple-dark); }
.rc-mu-tab.active {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   color: #fff;
   box-shadow: 0 4px 12px rgba(92, 0, 142, .25);
}
.rc-mu-tab-count {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   min-width: 22px;
   height: 20px;
   padding: 0 7px;
   border-radius: 999px;
   font-size: 11px;
   font-weight: 700;
   background: rgba(0, 0, 0, .06);
   color: inherit;
}
.rc-mu-tab.active .rc-mu-tab-count { background: rgba(255, 255, 255, .25); color: #fff; }

/* === BİLGİ NOTU === */
.rc-mu-note {
   display: flex;
   align-items: center;
   gap: 12px;
   flex-wrap: wrap;
   padding: 14px 18px;
   margin-bottom: 16px;
   background: var(--rc-purple-light);
   border: 1px solid var(--rc-purple-soft);
   border-radius: 12px;
   font-size: 13px;
   color: var(--rc-text);
   line-height: 1.5;
}
.rc-mu-note > i {
   color: var(--rc-purple-dark);
   font-size: 16px;
   flex-shrink: 0;
}
.rc-mu-note b { color: var(--rc-purple-dark); }
.rc-mu-note-badge {
   margin-left: auto;
   display: inline-flex;
   align-items: center;
   padding: 6px 14px;
   border-radius: 999px;
   background: #fff;
   border: 1px solid var(--rc-purple-soft);
   font-size: 12.5px;
   font-weight: 700;
   color: var(--rc-purple-dark);
   white-space: nowrap;
}

/* === TABLO KARTI === */
.rc-mu-card {
   background: #fff;
   border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 6px 24px rgba(92, 0, 142, .05);
   padding: 4px;
   margin-bottom: 30px;
   border: 1px solid var(--rc-border);
   overflow: hidden;
}
.rc-mu-tablo-wrap { width: 100%; border-radius: 12px; overflow-x: auto; }

/* === DATATABLES WRAPPER === */
.rc-mu-card .dataTables_wrapper { padding: 14px 16px 8px; }
.rc-mu-card .dataTables_length,
.rc-mu-card .dataTables_filter { margin-bottom: 14px; }
.rc-mu-card .dataTables_length label,
.rc-mu-card .dataTables_filter label { color: var(--rc-text-soft); font-size: 13px; font-weight: 500; }
.rc-mu-card .dataTables_filter input {
   border: 1px solid var(--rc-border) !important;
   border-radius: 999px !important;
   padding: 9px 18px !important;
   margin-left: 8px;
   font-size: 13px;
   width: 240px;
   max-width: 100%;
   outline: none;
   background: #fff !important;
   transition: border-color .15s, box-shadow .15s;
}
.rc-mu-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-mu-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
   background: #fff !important;
}
.rc-mu-card .dataTables_processing {
   border: none !important;
   background: rgba(255,255,255,.85) !important;
   color: var(--rc-purple-dark) !important;
   font-weight: 600;
   border-radius: 10px;
   box-shadow: 0 4px 16px rgba(92,0,142,.12);
}

/* === DataTables responsive plugin'ini devre disi === */
.rc-mu-table td.dtr-control,
.rc-mu-table th.dtr-control { display: none !important; }
.rc-mu-table tr.child { display: none !important; }
.rc-mu-table td.dtr-hidden,
.rc-mu-table th.dtr-hidden,
.rc-mu-table td.none,
.rc-mu-table th.none { display: table-cell !important; }

/* === MODERN VERİ TABLOSU === */
.rc-mu-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
   width: 100% !important;
   min-width: 820px;
}
.rc-mu-table thead { display: table-header-group !important; }
.rc-mu-table thead th {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   font-size: 11.5px;
   font-weight: 700;
   text-transform: uppercase;
   letter-spacing: .04em;
   padding: 13px 16px;
   text-align: left;
   border: none !important;
   border-bottom: 2px solid var(--rc-purple-soft) !important;
   white-space: nowrap;
   vertical-align: middle;
}
.rc-mu-table thead th:first-child { border-top-left-radius: 12px; }
.rc-mu-table thead th:last-child  { border-top-right-radius: 12px; }
.rc-mu-table thead th.rc-mu-col-actions { text-align: right; width: 80px; }

/* Sıralama okları */
.rc-mu-table thead th.sorting,
.rc-mu-table thead th.sorting_asc,
.rc-mu-table thead th.sorting_desc { cursor: pointer; position: relative; padding-right: 26px; }
.rc-mu-table thead th.sorting::after,
.rc-mu-table thead th.sorting_asc::after,
.rc-mu-table thead th.sorting_desc::after {
   position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
   font-family: "FontAwesome"; font-size: 11px; opacity: .35;
}
.rc-mu-table thead th.sorting::after     { content: "\f0dc"; }
.rc-mu-table thead th.sorting_asc::after { content: "\f0de"; opacity: .9; }
.rc-mu-table thead th.sorting_desc::after{ content: "\f0dd"; opacity: .9; }

/* Gövde */
.rc-mu-table tbody tr { background: #fff; transition: background .12s ease; }
.rc-mu-table.stripe tbody tr.even,
.rc-mu-table tbody tr.even { background: #fcfbfe; }
.rc-mu-table tbody tr:hover,
.rc-mu-table.stripe tbody tr.odd:hover,
.rc-mu-table.stripe tbody tr.even:hover { background: #f7f1fd; }
.rc-mu-table tbody td {
   padding: 13px 16px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   line-height: 1.5;
   white-space: nowrap;
}
.rc-mu-table tbody tr:last-child td { border-bottom: none !important; }

/* Müşteri adı = ilk hücre, satır başlığı */
.rc-mu-table tbody td:first-child {
   font-size: 14px;
   font-weight: 700;
   color: var(--rc-text);
   letter-spacing: -.1px;
   white-space: normal;
}
/* İşlemler hücresi = son hücre */
.rc-mu-table tbody td:last-child { text-align: right; width: 80px; white-space: nowrap; }

/* Toplam Alınan Ücret butonu → yeşil pill rozet */
.rc-mu-table tbody td .btn.btn-success {
   display: inline-block !important;
   width: auto !important;
   border-radius: 999px !important;
   padding: 5px 14px !important;
   font-size: 12.5px !important;
   font-weight: 700 !important;
   line-height: 1.5 !important;
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border: 1px solid var(--rc-purple-soft) !important;
   box-shadow: none !important;
   cursor: default;
}
.rc-mu-table tbody td .btn.btn-success::after { content: " ₺"; font-weight: 600; opacity: .7; }

/* İşlemler dropdown */
.rc-mu-table tbody td .dropdown .dropdown-toggle.btn-link {
   width: 38px; height: 38px;
   border-radius: 50%;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   display: inline-flex !important;
   align-items: center !important;
   justify-content: center !important;
   padding: 0 !important;
   font-size: 18px !important;
   line-height: 1 !important;
   box-shadow: 0 4px 12px rgba(92, 0, 142, .22);
   transition: transform .15s, filter .15s;
}
.rc-mu-table tbody td .dropdown .dropdown-toggle.btn-link:hover { transform: scale(1.08); filter: brightness(1.06); }
.rc-mu-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 190px !important;
}
.rc-mu-table tbody td .dropdown-menu .dropdown-item {
   padding: 9px 14px 9px 42px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
}
.rc-mu-table tbody td .dropdown-menu .dropdown-item:hover { background: var(--rc-purple-light) !important; color: var(--rc-purple-dark) !important; }
.rc-mu-table tbody td .dropdown-menu .dropdown-item i { width: 18px; margin-right: 6px; color: var(--rc-purple); }

/* Pagination */
.rc-mu-card .dataTables_paginate { padding: 14px; }
.rc-mu-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-mu-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-mu-card .dataTables_paginate .paginate_button.current,
.rc-mu-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-mu-card .dataTables_info { color: var(--rc-text-soft); font-size: 12.5px; padding: 14px !important; }
.rc-mu-table tbody td.dataTables_empty {
   text-align: center !important;
   padding: 50px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   background: #fff !important;
   white-space: normal;
}

/* === RESPONSIVE: TABLET LANDSCAPE (≤1024px) === */
@media (max-width: 1024px) {
   .rc-mu-header { padding: 14px 16px; }
   .rc-mu-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-mu-title { font-size: 17px; }
   .rc-mu-btn { height: 38px; padding: 0 13px; font-size: 12.5px; }
   .rc-mu-card .dataTables_wrapper { padding: 12px 10px 6px; }
   .rc-mu-card .dataTables_filter input { width: 200px; }
   .rc-mu-table tbody td { padding: 12px 14px !important; }
}

/* === RESPONSIVE: TABLET PORTRAIT + MOBILE (≤900px) — blok yığın === */
@media (max-width: 900px) {
   .rc-mu-header { padding: 12px 14px; border-radius: 12px; }
   .rc-mu-header-left { width: 100%; }
   .rc-mu-header-right { width: 100%; }
   .rc-mu-header-right .rc-mu-btn { flex: 1; min-width: 0; }
   .rc-mu-title { font-size: 16px; }
   .rc-mu-breadcrumb { font-size: 11.5px; }

   .rc-mu-tabs { gap: 6px; padding: 6px; }
   .rc-mu-tab { flex: 1; justify-content: center; height: 38px; padding: 0 10px; font-size: 12px; }

   .rc-mu-note { padding: 12px 14px; }
   .rc-mu-note-badge { margin-left: 0; }

   .rc-mu-card { padding: 8px; border-radius: 14px; }
   .rc-mu-card .dataTables_wrapper { padding: 10px 8px 4px; }
   .rc-mu-card .dataTables_filter { float: none; text-align: left; }
   .rc-mu-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-mu-card .dataTables_filter label { display: block; width: 100%; }
   .rc-mu-card .dataTables_length { display: none; }

   /* Tabloyu blok-yığın yap */
   .rc-mu-tablo-wrap { overflow-x: visible; }
   .rc-mu-table { min-width: 0 !important; display: block !important; }
   .rc-mu-table thead { display: none !important; }
   .rc-mu-table tbody { display: block !important; }
   .rc-mu-table tbody tr {
      display: block !important;
      background: #fff !important;
      border: 1px solid var(--rc-border) !important;
      border-radius: 14px !important;
      box-shadow: 0 2px 8px rgba(92, 0, 142, .05);
      padding: 6px 4px;
      margin-bottom: 12px;
   }
   .rc-mu-table tbody tr:last-child { margin-bottom: 0; }
   .rc-mu-table tbody td {
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      padding: 9px 14px !important;
      border: none !important;
      border-bottom: 1px solid #f4f0fa !important;
      text-align: right;
      white-space: normal !important;
      width: auto !important;
   }
   .rc-mu-table tbody tr td:last-child { border-bottom: none !important; justify-content: flex-end; }
   .rc-mu-table tbody td::before {
      content: attr(data-label);
      flex: 0 0 auto;
      margin-right: auto;
      font-size: 10.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .04em;
      color: var(--rc-purple-dark);
      text-align: left;
   }
   /* Müşteri adı = başlık satırı (etiketsiz, büyük) */
   .rc-mu-table tbody td:first-child {
      font-size: 15px !important;
      font-weight: 700;
      background: #faf7fe;
      border-radius: 10px;
      margin: 0 4px 6px;
   }
   .rc-mu-table tbody td:first-child::before { content: none; }
   .rc-mu-table tbody td:empty { display: none !important; }
   .rc-mu-table tbody td.dataTables_empty { justify-content: center; }
   .rc-mu-table tbody td.dataTables_empty::before { content: none; }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-mu-title-row { gap: 10px; }
   .rc-mu-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-mu-tab span.rc-mu-tab-count { display: none; }
   .rc-mu-table tbody td { padding: 8px 12px !important; font-size: 12.5px; }
   .rc-mu-table tbody td:first-child { font-size: 14.5px !important; }
}
</style>

<script>
/* DataTables responsive plugin'in eklediği sınıfları geri al + mobil yığın için
   her hücreye sütun başlığına göre data-label ekle (gizli kolon olsa bile kaymaz). */
(function(){
   var TABLES = ['#musteri_tablo', '#musteri_tablo_sadik', '#musteri_tablo_aktif', '#musteri_tablo_pasif', '#musteri_tablo_yeni'];

   function applyLabels(sel){
      var $t = $(sel);
      if(!$t.length) return;
      // Görünür başlıkları sırayla al (DataTables gizli kolonun th'sini DOM'dan kaldırır,
      // böylece th index == td index hizalı kalır).
      var heads = [];
      $t.find('thead th').each(function(){
         var lbl = $(this).attr('data-label');
         heads.push(lbl !== undefined ? lbl : $(this).text().trim());
      });
      $t.find('tbody tr').each(function(){
         $(this).find('> td').each(function(i){
            $(this).attr('data-label', heads[i] || '');
         });
      });
   }

   TABLES.forEach(function(sel){
      $(document).on('init.dt', sel, function(){
         var $t = $(sel);
         $t.removeClass('dtr-inline collapsed');
         setTimeout(function(){
            $t.find('td.dtr-control, th.dtr-control').remove();
            $t.find('td, th').removeClass('dtr-hidden none');
            $t.find('tr.child').remove();
            applyLabels(sel);
         }, 40);
      });
      $(document).on('draw.dt', sel, function(){ setTimeout(function(){ applyLabels(sel); }, 60); });
   });
})();
</script>

<script>
/* Hatirlatma "Yeni Musteri" karti ?filtre=yeni ile gelirse, "Yeni Eklenen Musteriler"
   sekmesini otomatik ac. Boylece son eklenen musteriler (en yeniler ustte) direkt gorunur. */
(function(){
   try {
      var p = new URLSearchParams(window.location.search);
      if (p.get('filtre') === 'yeni') {
         $(function(){
            var $btn = $('.rc-mu-tab[href="#yeni-musteriler"]');
            if ($btn.length) {
               if (typeof $btn.tab === 'function') { $btn.tab('show'); }
               else { $btn.trigger('click'); }
            }
         });
      }
   } catch(e){}
})();
</script>

<script>
// Musteri listesi actions dropdown'undan tek-tik anket gonderim
// (backend: WA-first + SMS fallback). Swal (SweetAlert2) yuklu degilse
// native confirm/alert'a duser (defansif).
function _anketListeConfirm(cb){
   if (typeof Swal !== 'undefined' && Swal.fire) {
      Swal.fire({
         title: 'Memnuniyet anketi gönderilsin mi?',
         text: 'Müşteriye anket linki WhatsApp veya SMS ile gönderilecek.',
         icon: 'question',
         showCancelButton: true,
         confirmButtonText: 'Evet, gönder',
         cancelButtonText: 'Vazgeç',
         confirmButtonColor: '#25D366',
      }).then(function(r){ if (r && r.value) cb(); });
   } else {
      if (confirm('Bu müşteriye memnuniyet anketi gönderilsin mi?')) cb();
   }
}
function _anketListeNotify(icon, title, text){
   if (typeof Swal !== 'undefined' && Swal.fire) {
      var opts = { icon: icon, title: title, text: text };
      if (icon === 'success') { opts.timer = 2500; opts.showConfirmButton = false; }
      Swal.fire(opts);
   } else {
      alert(title + (text ? '\n' + text : ''));
   }
}
function anketHizliGonderListe(userId, el){
   _anketListeConfirm(function(){
      var $el = $(el);
      var eskiHtml = $el.html();
      $el.html('<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...');
      $.ajax({
         url: '/isletmeyonetim/anket-hizli-gonder',
         method: 'POST',
         timeout: 20000,
         data: { user_id: userId, sube: (new URLSearchParams(location.search)).get('sube') || '' },
         dataType: 'json',
         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      }).done(function(res){
         if (res && res.basarili) {
            var kanal = res.kanal === 'whatsapp' ? 'WhatsApp' : 'SMS';
            _anketListeNotify('success', 'Gönderildi', 'Anket ' + kanal + ' ile iletildi.');
         } else {
            _anketListeNotify('error', 'Gönderilemedi', (res && res.mesaj) ? res.mesaj : 'Bilinmeyen hata.');
         }
      }).fail(function(xhr, status, err){
         var msg = 'İstek başarısız.';
         if (xhr && xhr.responseText) {
            try { var j = JSON.parse(xhr.responseText); if (j && j.mesaj) msg = j.mesaj; } catch(e){}
         }
         if (xhr && xhr.status) msg += ' (HTTP ' + xhr.status + ')';
         if (status === 'timeout') msg = 'Sunucu 20 saniye içinde cevap vermedi.';
         _anketListeNotify('error', 'Hata', msg);
      }).always(function(){
         $el.html(eskiHtml);
      });
   });
}
</script>

@endsection()
