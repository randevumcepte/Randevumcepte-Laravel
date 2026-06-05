@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-pk-page">
 <form id="paket_satis_form" action="{{route('pakettahsilatagit')}}" method="POST">
  <input name="sube" type="hidden" value="{{$isletme->id}}">

   {{-- Modern Page Header --}}
   <div class="rc-pk-header">
      <div class="rc-pk-header-left">
         <div class="rc-pk-title-row">
            <div class="rc-pk-icon-bubble"><i class="fa fa-cubes"></i></div>
            <div>
               <h1 class="rc-pk-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-pk-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-pk-sep">›</span>
                  <span class="rc-pk-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-pk-header-right">
         <button type="button" data-toggle="modal" data-target="#paket-modal"
                 class="rc-pk-btn rc-pk-btn-success yenieklebuton501">
            <i class="fa fa-plus"></i><span>Yeni Paket</span>
         </button>
      </div>
   </div>

   {{-- Modern Satış Kartı --}}
   <div class="rc-pk-action-card">
      <div class="rc-pk-action-head">
         <i class="fa fa-shopping-cart"></i>
         <span>Paket Satışı</span>
         <span class="rc-pk-action-hint">Listeden paket(ler) seçin, müşteri ve personel belirleyip “Satış Yap”a basın.</span>
      </div>
      <div class="rc-pk-action-grid">
         <div class="rc-pk-field">
            <label for="paket_satis_musteri_id">Müşteri / Danışan</label>
            <select class="form-control custom-select2 musteri_secimi" id="paket_satis_musteri_id"
                    name="paket_satis_musteri_id" style="width:100%">
               <option></option>
            </select>
         </div>
         <div class="rc-pk-field">
            <label for="paket_satis_personel_id">Personel</label>
            <select class="form-control custom-select2 personel_secimi" id="paket_satis_personel_id"
                    name="paket_satis_personel_id" style="width:100%">
               <option value=""></option>
            </select>
         </div>
         <div class="rc-pk-field rc-pk-field-action">
            <a title="Tahsil Et" id="secilenpaket_satis_yap" href="isletmeyonetim/adisyondetay"
               class="rc-pk-btn rc-pk-btn-primary yenieklebuton502">
               <i class="fa fa-check"></i><span>Satış Yap</span>
            </a>
         </div>
      </div>
   </div>

   {{-- Modern Tablo Kartı --}}
   <div class="rc-pk-card">
      <div class="rc-pk-tablo-wrap">
         <table class="data-table table stripe hover nowrap rc-pk-table" id="paket_liste" style="width:100%">
            <thead>
               <tr>
                  <th class="rc-pk-col-check">
                     <div class="dt-checkbox">
                        <input type="checkbox" id="paket_hepsini_sec_liste" />
                        <span class="dt-checkbox-label"></span>
                     </div>
                  </th>
                  <th>Paket Adı</th>
                  <th>Hizmet(-ler)</th>
                  <th>Seans(-lar)</th>
                  <th>Fiyat (₺)</th>
                  <th class="datatable-nosort rc-pk-col-actions">İşlemler</th>
               </tr>
            </thead>
            <tbody></tbody>
         </table>
      </div>
   </div>

 </form>
 <div id="hata"></div>
</div>

<style>
/* =================================================================
   PAKET YÖNETİMİ — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */

.rc-pk-page {
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
   --rc-info: #2563eb;
   --rc-info-soft: #dbeafe;
   --rc-text: #1f2937;
   --rc-text-soft: #6b7280;
   --rc-border: #eef0f4;
}

/* === HEADER === */
.rc-pk-header {
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
.rc-pk-header-left,
.rc-pk-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
.rc-pk-title-row {
   display: flex;
   align-items: center;
   gap: 14px;
}
.rc-pk-icon-bubble {
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
.rc-pk-title {
   margin: 0;
   font-size: 19px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.2;
}
.rc-pk-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex;
   align-items: center;
   gap: 6px;
   flex-wrap: wrap;
}
.rc-pk-breadcrumb a {
   color: var(--rc-text-soft);
   text-decoration: none;
   transition: color .15s;
}
.rc-pk-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-pk-breadcrumb .rc-pk-sep { color: #cbd5e1; }
.rc-pk-breadcrumb .rc-pk-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === BUTONLAR === */
.rc-pk-btn {
   display: inline-flex;
   align-items: center;
   justify-content: center;
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
.rc-pk-btn i { font-size: 14px; }
.rc-pk-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-pk-btn:active { transform: translateY(0); }
.rc-pk-btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
   box-shadow: 0 6px 16px rgba(22, 163, 74, .28);
}
.rc-pk-btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
}

/* === SATIŞ KARTI === */
.rc-pk-action-card {
   background: #fff;
   border-radius: 14px;
   padding: 18px 20px 20px;
   margin-bottom: 18px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
}
.rc-pk-action-head {
   display: flex;
   align-items: center;
   gap: 8px;
   flex-wrap: wrap;
   font-size: 12.5px;
   font-weight: 700;
   color: var(--rc-purple-dark);
   text-transform: uppercase;
   letter-spacing: .04em;
   padding-bottom: 14px;
   margin-bottom: 16px;
   border-bottom: 1px solid var(--rc-border);
   width: 100%;
}
.rc-pk-action-head > i {
   width: 28px; height: 28px;
   border-radius: 8px;
   background: var(--rc-purple-light);
   display: inline-flex;
   align-items: center;
   justify-content: center;
   font-size: 13px;
   flex-shrink: 0;
}
.rc-pk-action-hint {
   margin-left: auto;
   font-size: 11.5px;
   font-weight: 500;
   color: var(--rc-text-soft);
   text-transform: none;
   letter-spacing: 0;
}
.rc-pk-action-grid {
   display: grid;
   grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
   gap: 14px;
   align-items: end;
}
.rc-pk-field {
   display: flex;
   flex-direction: column;
   gap: 6px;
   min-width: 0;
}
.rc-pk-field label {
   font-size: 11.5px;
   font-weight: 700;
   text-transform: uppercase;
   letter-spacing: .04em;
   color: var(--rc-text-soft);
   margin: 0;
}
.rc-pk-field-action .rc-pk-btn { min-width: 150px; }

/* select2 görünümünü modern input ile eşle */
.rc-pk-page .select2-container { width: 100% !important; min-width: 0; }
.rc-pk-page .select2-container--default .select2-selection--single {
   height: 42px !important;
   border: 1px solid var(--rc-border) !important;
   border-radius: 10px !important;
   background-color: #fafbfc !important;
   display: flex;
   align-items: center;
   transition: border-color .15s, box-shadow .15s, background-color .15s;
}
.rc-pk-page .select2-container--default.select2-container--focus .select2-selection--single,
.rc-pk-page .select2-container--default.select2-container--open .select2-selection--single {
   border-color: var(--rc-purple) !important;
   background-color: #fff !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12) !important;
}
.rc-pk-page .select2-container--default .select2-selection--single .select2-selection__rendered {
   line-height: 40px !important;
   padding-left: 14px;
   padding-right: 36px;
   font-size: 13.5px;
   color: var(--rc-text);
}
.rc-pk-page .select2-container--default .select2-selection--single .select2-selection__placeholder {
   color: var(--rc-text-soft);
}
.rc-pk-page .select2-container--default .select2-selection--single .select2-selection__arrow {
   height: 40px !important;
   right: 8px;
}

/* === TABLO KARTI === */
.rc-pk-card {
   background: #fff;
   border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 6px 24px rgba(92, 0, 142, .05);
   padding: 4px;
   margin-bottom: 30px;
   border: 1px solid var(--rc-border);
   overflow: hidden;
}
.rc-pk-tablo-wrap {
   width: 100%;
   border-radius: 12px;
   overflow-x: auto;
}

/* === DATATABLES WRAPPER === */
.rc-pk-card .dataTables_wrapper { padding: 14px 16px 8px; }
.rc-pk-card .dataTables_length,
.rc-pk-card .dataTables_filter { margin-bottom: 14px; }
.rc-pk-card .dataTables_length label,
.rc-pk-card .dataTables_filter label {
   color: var(--rc-text-soft);
   font-size: 13px;
   font-weight: 500;
}
.rc-pk-card .dataTables_filter input {
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
.rc-pk-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-pk-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
   background: #fff !important;
}

/* === DataTables responsive plugin'ini devre disi === */
#paket_liste td.dtr-control,
#paket_liste th.dtr-control { display: none !important; }
#paket_liste tr.child { display: none !important; }
#paket_liste td.dtr-hidden,
#paket_liste th.dtr-hidden,
#paket_liste td.none,
#paket_liste th.none { display: block !important; }

/* === MODERN VERİ TABLOSU === */
.rc-pk-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
   width: 100% !important;
   min-width: 680px;
}
.rc-pk-table thead { display: table-header-group !important; }
.rc-pk-table thead th {
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
.rc-pk-table thead th:first-child { border-top-left-radius: 12px; }
.rc-pk-table thead th:last-child  { border-top-right-radius: 12px; }
.rc-pk-table thead th.rc-pk-col-check { width: 46px; }
.rc-pk-table thead th.rc-pk-col-actions { text-align: right; width: 90px; }

/* DataTables siralama oklari */
.rc-pk-table thead th.sorting,
.rc-pk-table thead th.sorting_asc,
.rc-pk-table thead th.sorting_desc {
   cursor: pointer;
   position: relative;
   padding-right: 26px;
}
.rc-pk-table thead th.sorting::after,
.rc-pk-table thead th.sorting_asc::after,
.rc-pk-table thead th.sorting_desc::after {
   position: absolute;
   right: 12px;
   top: 50%;
   transform: translateY(-50%);
   font-family: "FontAwesome";
   font-size: 11px;
   opacity: .35;
}
.rc-pk-table thead th.sorting::after     { content: "\f0dc"; }
.rc-pk-table thead th.sorting_asc::after { content: "\f0de"; opacity: .9; }
.rc-pk-table thead th.sorting_desc::after{ content: "\f0dd"; opacity: .9; }

/* GOVDE */
.rc-pk-table tbody tr {
   background: #fff;
   transition: background .12s ease;
}
.rc-pk-table.stripe tbody tr.even,
.rc-pk-table tbody tr.even { background: #fcfbfe; }
.rc-pk-table tbody tr:hover,
.rc-pk-table.stripe tbody tr.odd:hover,
.rc-pk-table.stripe tbody tr.even:hover { background: #f7f1fd; }

.rc-pk-table tbody td {
   padding: 14px 16px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   line-height: 1.5;
   white-space: normal;
}
.rc-pk-table tbody tr:last-child td { border-bottom: none !important; }

/* PAKET ADI — satır başlığı */
.rc-pk-table tbody td.rc-pk-td-ad {
   font-size: 14px;
   font-weight: 700;
   color: var(--rc-text);
   letter-spacing: -.1px;
}
/* HIZMETLER */
.rc-pk-table tbody td.rc-pk-td-hizmet {
   color: var(--rc-text-soft);
   max-width: 320px;
   overflow-wrap: anywhere;
}
/* SEANSLAR */
.rc-pk-table tbody td.rc-pk-td-seans {
   color: var(--rc-text);
   overflow-wrap: anywhere;
}
/* FIYAT */
.rc-pk-table tbody td.rc-pk-td-fiyat {
   white-space: nowrap;
   font-weight: 700;
   color: var(--rc-purple-dark);
}
.rc-pk-table tbody td.rc-pk-td-fiyat::after {
   content: " ₺";
   font-weight: 600;
   color: var(--rc-text-soft);
}
/* CHECKBOX hücresi */
.rc-pk-table tbody td.rc-pk-td-check { width: 46px; }
.rc-pk-table tbody td.rc-pk-td-check .dt-checkbox { margin: 0; }
/* ISLEMLER hücresi */
.rc-pk-table tbody td.rc-pk-td-actions {
   text-align: right;
   width: 90px;
   white-space: nowrap;
}

/* Ekle butonu (addition modunda) → pill */
.rc-pk-table tbody td .btn.btn-success {
   border-radius: 999px !important;
   padding: 6px 14px !important;
   font-size: 12px !important;
   font-weight: 600 !important;
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important;
   color: #fff !important;
   border: none !important;
   box-shadow: 0 2px 6px rgba(22, 163, 74, .25) !important;
}

/* === İŞLEMLER DROPDOWN === */
.rc-pk-table tbody td .dropdown .dropdown-toggle.btn-link {
   width: 38px;
   height: 38px;
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
.rc-pk-table tbody td .dropdown .dropdown-toggle.btn-link:hover {
   transform: scale(1.08);
   filter: brightness(1.06);
}
.rc-pk-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 180px !important;
}
.rc-pk-table tbody td .dropdown-menu .dropdown-item {
   padding: 9px 14px 9px 42px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
}
.rc-pk-table tbody td .dropdown-menu .dropdown-item:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-pk-table tbody td .dropdown-menu .dropdown-item i {
   width: 18px;
   margin-right: 6px;
   color: var(--rc-purple);
}

/* === PAGINATION === */
.rc-pk-card .dataTables_paginate { padding: 14px; }
.rc-pk-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-pk-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-pk-card .dataTables_paginate .paginate_button.current,
.rc-pk-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-pk-card .dataTables_info {
   color: var(--rc-text-soft);
   font-size: 12.5px;
   padding: 14px !important;
}

/* === BOŞ DURUM === */
.rc-pk-table tbody td.dataTables_empty {
   text-align: center !important;
   padding: 50px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   background: #fff !important;
}

/* === RESPONSIVE: TABLET LANDSCAPE (≤1024px) === */
@media (max-width: 1024px) {
   .rc-pk-header { padding: 14px 16px; }
   .rc-pk-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-pk-title { font-size: 17px; }
   .rc-pk-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-pk-action-hint { display: none; }
   .rc-pk-card .dataTables_wrapper { padding: 12px 10px 6px; }
   .rc-pk-card .dataTables_filter input { width: 200px; }
   .rc-pk-table tbody td { padding: 12px 14px !important; }
}

/* === RESPONSIVE: TABLET PORTRAIT + MOBILE (≤900px) === */
@media (max-width: 900px) {
   .rc-pk-header {
      padding: 12px 14px;
      border-radius: 12px;
   }
   .rc-pk-header-left { width: 100%; }
   .rc-pk-header-right { width: 100%; }
   .rc-pk-header-right .rc-pk-btn { flex: 1; }
   .rc-pk-title { font-size: 16px; }
   .rc-pk-breadcrumb { font-size: 11.5px; }

   .rc-pk-action-card { padding: 14px 14px 16px; border-radius: 12px; }
   .rc-pk-action-grid { grid-template-columns: 1fr; gap: 12px; }
   .rc-pk-field-action { width: 100%; }
   .rc-pk-field-action .rc-pk-btn { width: 100%; }

   .rc-pk-card { padding: 8px; border-radius: 14px; }
   .rc-pk-card .dataTables_wrapper { padding: 10px 8px 4px; }
   .rc-pk-card .dataTables_filter { float: none; text-align: left; }
   .rc-pk-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-pk-card .dataTables_filter label { display: block; width: 100%; }
   .rc-pk-card .dataTables_length { display: none; }

   /* Tabloyu blok-yığın yap */
   .rc-pk-tablo-wrap { overflow-x: visible; }
   .rc-pk-table { min-width: 0 !important; display: block !important; }
   .rc-pk-table thead { display: none !important; }
   .rc-pk-table tbody { display: block !important; }
   .rc-pk-table tbody tr {
      display: block !important;
      background: #fff !important;
      border: 1px solid var(--rc-border) !important;
      border-radius: 14px !important;
      box-shadow: 0 2px 8px rgba(92, 0, 142, .05);
      padding: 6px 4px;
      margin-bottom: 12px;
   }
   .rc-pk-table tbody tr:last-child { margin-bottom: 0; }
   .rc-pk-table tbody td {
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
   .rc-pk-table tbody tr td:last-child { border-bottom: none !important; }
   .rc-pk-table tbody td::before {
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
   /* Checkbox satırı: etiket "Seç" solda, kutu sağda */
   .rc-pk-table tbody td.rc-pk-td-check {
      background: #faf7fe;
      border-radius: 10px;
      margin: 2px 4px 2px;
   }
   .rc-pk-table tbody td.rc-pk-td-check::before { content: "Seç"; }
   /* Paket adı = başlık satırı (büyük, etiketsiz) */
   .rc-pk-table tbody td.rc-pk-td-ad {
      font-size: 15px !important;
      font-weight: 700;
      background: #faf7fe;
      border-radius: 10px;
      margin: 0 4px 6px;
   }
   .rc-pk-table tbody td.rc-pk-td-ad::before { content: none; }
   .rc-pk-table tbody td.rc-pk-td-actions { justify-content: flex-end; }
   .rc-pk-table tbody td:empty { display: none !important; }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-pk-title-row { gap: 10px; }
   .rc-pk-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-pk-table tbody td { padding: 8px 12px !important; font-size: 12.5px; }
   .rc-pk-table tbody td.rc-pk-td-ad { font-size: 14.5px !important; }
}
</style>

<script>
/* DataTables responsive plugin'in dinamik attığı sınıfları geri al
   (modern tablo kendi responsive yığın düzenini kullanıyor) */
$(document).on('init.dt', '#paket_liste', function() {
   var $t = $('#paket_liste');
   $t.removeClass('dtr-inline collapsed');
   setTimeout(function() {
      $t.find('td, th').removeClass('dtr-hidden none');
      $t.find('tr.child').remove();
      $t.find('td.dtr-control, th.dtr-control').remove();
   }, 50);
});

/* Mobil yığın görünümü için her hücreye sütun adı etiketi + sınıf ekle.
   DataTables her redraw'da satırları yeniden çizdiği için draw.dt'de tekrar uygula. */
(function(){
   var labels = ['Seç', 'Paket Adı', 'Hizmet(-ler)', 'Seans(-lar)', 'Fiyat', 'İşlemler'];
   var tdClasses = [
      'rc-pk-td-check',
      'rc-pk-td-ad',
      'rc-pk-td-hizmet',
      'rc-pk-td-seans',
      'rc-pk-td-fiyat',
      'rc-pk-td-actions'
   ];
   function applyLabels(){
      $('#paket_liste tbody tr').each(function(){
         $(this).find('> td').each(function(idx){
            $(this).attr('data-label', labels[idx] || '');
            var cls = tdClasses[idx] || '';
            if(cls && !$(this).hasClass(cls)) $(this).addClass(cls);
         });
      });
   }
   $(document).on('draw.dt init.dt', '#paket_liste', function(){
      setTimeout(applyLabels, 80);
   });
   $(document).ready(function(){ setTimeout(applyLabels, 200); });
})();
</script>

@endsection()
