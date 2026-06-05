@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-og-page">

   {{-- Modern Page Header --}}
   <div class="rc-og-header">
      <div class="rc-og-header-left">
         <div class="rc-og-title-row">
            <div class="rc-og-icon-bubble"><i class="fa fa-comments"></i></div>
            <div>
               <h1 class="rc-og-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-og-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-og-sep">›</span>
                  <span class="rc-og-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-og-header-right">
         @yetki('gorusme.ekle_duzenle')
         <a id="yeni_on_gorusme_ekle" href="#" data-toggle="modal" data-target="#ongorusme-modal"
            onclick="modalbaslikata('Yeni Ön Görüşme','ongorusmeformu')"
            class="rc-og-btn rc-og-btn-success yenieklebuton501">
            <i class="fa fa-plus"></i><span>Yeni Ön Görüşme</span>
         </a>
         @endyetki
      </div>
   </div>

   {{-- Modern Tablo --}}
   <div class="rc-og-card">
      <form id="on_gorusme_liste_form">
         <div class="rc-og-table-scroll">
            <table class="data-table rc-og-table" id="on_gorusme_liste" style="width:100%">
               <thead>
                  <tr>
                     <th>Oluşturma</th>
                     <th>Müşteri</th>
                     <th>Müşteri Tipi</th>
                     <th>Telefon</th>
                     <th>Randevu Tarihi</th>
                     <th>Ön Görüşme Nedeni</th>
                     <th>Görüşmeyi Yapan</th>
                     <th>Durum</th>
                     <th class="rc-og-col-actions">İşlemler</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>
      </form>
   </div>

</div>

<style>
/* =================================================================
   ÖN GÖRÜŞMELER — MODERN TABLO
   ================================================================= */

.rc-og-page {
   --rc-purple-dark: #5C008E;
   --rc-purple: #9D5DC8;
   --rc-purple-light: #f5eefe;
   --rc-purple-soft: #ead4ff;
   --rc-pink: #d946ef;
   --rc-success: #16a34a;
   --rc-success-soft: #dcfce7;
   --rc-warning: #f59e0b;
   --rc-warning-soft: #fef3c7;
   --rc-danger: #dc2626;
   --rc-danger-soft: #fee2e2;
   --rc-text: #1f2937;
   --rc-text-soft: #6b7280;
   --rc-border: #eef0f4;
}

/* === HEADER === */
.rc-og-header {
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
.rc-og-header-left,
.rc-og-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
.rc-og-title-row {
   display: flex;
   align-items: center;
   gap: 14px;
}
.rc-og-icon-bubble {
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
.rc-og-title {
   margin: 0;
   font-size: 19px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.2;
}
.rc-og-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex;
   align-items: center;
   gap: 6px;
   flex-wrap: wrap;
}
.rc-og-breadcrumb a { color: var(--rc-text-soft); text-decoration: none; transition: color .15s; }
.rc-og-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-og-breadcrumb .rc-og-sep { color: #cbd5e1; }
.rc-og-breadcrumb .rc-og-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === HEADER BUTONLAR === */
.rc-og-btn {
   display: inline-flex;
   align-items: center;
   gap: 8px;
   height: 42px;
   padding: 0 18px;
   border-radius: 999px;
   font-size: 13.5px;
   font-weight: 600;
   color: #fff !important;
   text-decoration: none !important;
   border: none;
   white-space: nowrap;
   cursor: pointer;
   transition: transform .15s, box-shadow .15s, filter .15s;
}
.rc-og-btn i { font-size: 14px; }
.rc-og-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-og-btn:active { transform: translateY(0); }
.rc-og-btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
   box-shadow: 0 6px 16px rgba(22, 163, 74, .28);
}

/* === DIŞ KAP (tek beyaz panel) === */
.rc-og-card {
   background: #fff;
   border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 6px 24px rgba(92, 0, 142, .05);
   padding: 10px 18px 18px;
   margin-bottom: 30px;
}

/* === DATATABLES TOOLBAR (search/length) === */
.rc-og-card .dataTables_wrapper {
   padding: 6px 0 0;
}
.rc-og-card .dataTables_length,
.rc-og-card .dataTables_filter {
   margin-bottom: 14px;
   padding: 6px 4px;
}
.rc-og-card .dataTables_length label,
.rc-og-card .dataTables_filter label {
   color: var(--rc-text-soft);
   font-size: 13px;
   font-weight: 500;
}
.rc-og-card .dataTables_filter input {
   border: 1px solid var(--rc-border) !important;
   border-radius: 999px !important;
   padding: 9px 18px !important;
   margin-left: 8px;
   font-size: 13px;
   width: 240px;
   max-width: 100%;
   outline: none;
   background: #fafafe;
   transition: border-color .15s, box-shadow .15s, background .15s;
}
.rc-og-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   background: #fff;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-og-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
   background: #fff;
}

/* === SCROLL KABI (mobilde yatay kaydirma) === */
.rc-og-table-scroll {
   width: 100%;
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
}

/* === MODERN TABLO === */
.rc-og-table {
   width: 100% !important;
   border-collapse: separate !important;
   border-spacing: 0 !important;
}

/* THEAD — yapiskan, yumusak basliklar */
.rc-og-table thead th {
   background: var(--rc-purple-light);
   color: var(--rc-purple-dark);
   font-size: 11px;
   font-weight: 700;
   text-transform: uppercase;
   letter-spacing: .04em;
   text-align: left;
   padding: 13px 16px !important;
   border: none !important;
   white-space: nowrap;
   vertical-align: middle;
}
.rc-og-table thead th:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
.rc-og-table thead th:last-child  { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

/* DataTables siralama oklari */
.rc-og-table thead th.sorting,
.rc-og-table thead th.sorting_asc,
.rc-og-table thead th.sorting_desc {
   cursor: pointer;
}
.rc-og-table thead th.sorting:after,
.rc-og-table thead th.sorting_asc:after,
.rc-og-table thead th.sorting_desc:after {
   opacity: .45;
   font-size: 10px;
   margin-left: 4px;
}

/* TBODY satirlari */
.rc-og-table tbody td {
   padding: 14px 16px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle;
   background: transparent !important;
}
.rc-og-table tbody tr {
   transition: background .15s;
}
.rc-og-table tbody tr:hover td {
   background: #faf7fe !important;
}
.rc-og-table tbody tr:last-child td {
   border-bottom: none !important;
}

/* Striped'i kaldir (tek tip zemin) */
.rc-og-table.stripe tbody tr.odd td,
.rc-og-table tbody tr.odd td { background: transparent !important; }

/* Musteri sutunu (2.) vurgulu */
.rc-og-table tbody td:nth-child(2) {
   font-weight: 700;
   color: var(--rc-text);
}
/* Oluşturma (1.) ve Görüşmeyi Yapan (7.) — soluk */
.rc-og-table tbody td:nth-child(1),
.rc-og-table tbody td:nth-child(7) {
   color: var(--rc-text-soft);
   white-space: nowrap;
}
/* Telefon (4.) — okunakli rakam */
.rc-og-table tbody td:nth-child(4) {
   font-variant-numeric: tabular-nums;
   white-space: nowrap;
}
/* Islemler sutunu — saga hizali, dar */
.rc-og-table thead th.rc-og-col-actions,
.rc-og-table tbody td:nth-child(9) {
   text-align: right;
   width: 1%;
   white-space: nowrap;
}

/* === DURUM BADGE === */
.rc-og-table tbody td .btn.btn-warning,
.rc-og-table tbody td .btn.btn-success,
.rc-og-table tbody td .btn.btn-danger {
   border-radius: 999px !important;
   padding: 5px 14px !important;
   font-size: 11px !important;
   font-weight: 700 !important;
   line-height: 1.4 !important;
   display: inline-block !important;
   width: auto !important;
   min-width: 0;
   text-align: center;
   box-shadow: none !important;
   text-transform: uppercase;
   letter-spacing: .03em;
}
.rc-og-table tbody td .btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border: 1px solid #fde68a !important;
   pointer-events: none;
}
.rc-og-table tbody td .btn.btn-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border: 1px solid #bbf7d0 !important;
}
.rc-og-table tbody td .btn.btn-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
   border: 1px solid #fecaca !important;
   cursor: pointer;
}
.rc-og-table tbody td .btn.btn-danger:hover { filter: brightness(.96); }

/* === İŞLEMLER DROPDOWN === */
.rc-og-table tbody td .dropdown .dropdown-toggle.btn-link {
   width: 36px;
   height: 36px;
   border-radius: 50%;
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   display: inline-flex !important;
   align-items: center !important;
   justify-content: center !important;
   padding: 0 !important;
   font-size: 18px !important;
   transition: background .15s, transform .15s;
   line-height: 1 !important;
}
.rc-og-table tbody td .dropdown .dropdown-toggle.btn-link:hover {
   background: var(--rc-purple-soft) !important;
   transform: scale(1.05);
}
.rc-og-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 190px !important;
}
.rc-og-table tbody td .dropdown-menu .dropdown-item {
   display: flex !important;
   align-items: center;
   gap: 10px;
   padding: 9px 12px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
   white-space: nowrap;
}
.rc-og-table tbody td .dropdown-menu .dropdown-item:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-og-table tbody td .dropdown-menu .dropdown-item i {
   position: static !important;
   display: inline-flex !important;
   align-items: center;
   justify-content: center;
   width: 18px;
   height: 18px;
   margin: 0 !important;
   font-size: 13px;
   color: var(--rc-purple);
   flex-shrink: 0;
}

/* === PAGINATION === */
.rc-og-card .dataTables_paginate {
   padding: 16px 4px 6px;
}
.rc-og-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-og-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-og-card .dataTables_paginate .paginate_button.current,
.rc-og-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-og-card .dataTables_info {
   color: var(--rc-text-soft);
   font-size: 12.5px;
   padding: 16px 4px 0 !important;
}

/* === BOŞ DURUM === */
.rc-og-table tbody tr.dataTables_empty td {
   text-align: center;
   padding: 56px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   border-bottom: none !important;
}
.rc-og-table tbody tr.dataTables_empty:hover td {
   background: transparent !important;
}

/* Responsive plugin artiklarini gizle (bu tabloda kullanilmiyor) */
#on_gorusme_liste td.dtr-control,
#on_gorusme_liste th.dtr-control,
#on_gorusme_liste tr.child { display: none !important; }

/* === RESPONSIVE === */
@media (max-width: 1024px) {
   .rc-og-header { padding: 14px 16px; }
   .rc-og-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-og-title { font-size: 17px; }
   .rc-og-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-og-card { padding: 8px 12px 14px; }
   .rc-og-card .dataTables_filter input { width: 200px; }

   /* Tablet: 9 sutun da sigsin diye sikistir, Islemler kirpilmasin */
   .rc-og-table thead th { padding: 11px 9px !important; font-size: 10px; letter-spacing: .02em; }
   .rc-og-table tbody td { padding: 11px 9px !important; font-size: 12.5px; }
   .rc-og-table tbody td .btn.btn-warning,
   .rc-og-table tbody td .btn.btn-success,
   .rc-og-table tbody td .btn.btn-danger {
      padding: 4px 9px !important;
      font-size: 10px !important;
   }
   .rc-og-table tbody td .dropdown .dropdown-toggle.btn-link {
      width: 32px; height: 32px; font-size: 16px !important;
   }
   /* Islemler hucresini en dara sabitle ve gorunur tut */
   .rc-og-table thead th.rc-og-col-actions,
   .rc-og-table tbody td:nth-child(9) {
      position: sticky;
      right: 0;
      background: #fff;
      box-shadow: -6px 0 8px -6px rgba(17, 24, 39, .12);
   }
   .rc-og-table tbody tr:hover td:nth-child(9) { background: #faf7fe; }
   .rc-og-table thead th.rc-og-col-actions { background: var(--rc-purple-light); }
}

@media (max-width: 768px) {
   .rc-og-header { padding: 12px 14px; border-radius: 12px; }
   .rc-og-header-left { width: 100%; }
   .rc-og-header-right { width: 100%; }
   .rc-og-header-right .rc-og-btn { flex: 1; justify-content: center; }
   .rc-og-title { font-size: 16px; }
   .rc-og-breadcrumb { font-size: 11.5px; }

   .rc-og-card { padding: 8px 10px 14px; }
   .rc-og-card .dataTables_filter { float: none; text-align: left; }
   .rc-og-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-og-card .dataTables_filter label { display: block; width: 100%; }
   .rc-og-card .dataTables_length { display: none; }

   /* tablo yatay kayar; sutunlar daralmasin */
   .rc-og-table { min-width: 760px; }
   .rc-og-table thead th,
   .rc-og-table tbody td { padding: 12px 12px !important; }
}

@media (max-width: 420px) {
   .rc-og-header-right { flex-direction: column; }
   .rc-og-header-right .rc-og-btn { width: 100%; }
   .rc-og-title-row { gap: 10px; }
   .rc-og-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
}
</style>

@endsection()
