@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-rl-page">

   {{-- Modern Page Header --}}
   <div class="rc-rl-header">
      <div class="rc-rl-header-left">
         <div class="rc-rl-title-row">
            <div class="rc-rl-icon-bubble"><i class="fa fa-calendar-check-o"></i></div>
            <div>
               <h1 class="rc-rl-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-rl-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-rl-sep">›</span>
                  <span class="rc-rl-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-rl-header-right">
         <a href="#" data-toggle="modal" data-target="#modal-view-event-add"
            class="rc-rl-btn rc-rl-btn-success yenieklebuton">
            <i class="fa fa-plus"></i><span>Yeni Randevu</span>
         </a>
      </div>
   </div>

   {{-- Modern Filtre Kartı --}}
   <div class="rc-rl-filter-card">
      <div class="rc-rl-filter-head">
         <i class="fa fa-filter"></i>
         <span>Filtrele</span>
      </div>
      <div class="rc-rl-filter-grid">
         <div class="rc-rl-field">
            <label for="olusturulmaya_gore_filtre">Kaynak</label>
            <select name="olusturulma" id="olusturulmaya_gore_filtre" class="form-control rc-rl-select">
               <option selected value="">Tümü</option>
               <option value="salon">Salon</option>
               <option value="web">Web</option>
               <option value="uygulama">Uygulama</option>
            </select>
         </div>
         <div class="rc-rl-field">
            <label for="duruma_gore_filtre">Durum</label>
            <select class="form-control rc-rl-select" id="duruma_gore_filtre">
               <option selected value="">Tüm Randevu Durumları</option>
               <option value="0">Onay bekleyen</option>
               <option value="1">Onaylı</option>
               <option value="2">Reddedilen</option>
               <option value="3">Müşteri tarafından iptal edilen</option>
            </select>
         </div>
         <div class="rc-rl-field">
            <label for="zamana_gore_filtre">Zaman</label>
            <select class="form-control rc-rl-select" id="zamana_gore_filtre">
               <option value="">Tüm Zamanlar</option>
               <option selected value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
               <option value="{{date('Y-m-d', strtotime('+1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('+1 days',strtotime(date('Y-m-d'))))}}">Yarın</option>
               <option value="<?php echo date('Y-m-01') . ' / '. date('Y-m-t'); ?>">Bu ay</option>
               <option value="<?php echo date('Y-m-01',strtotime('+1 months')) . ' / '. date('Y-m-t',strtotime('+1 months')); ?>">Önümüzdeki ay</option>
               <option value="<?php echo date('Y-01-01') . ' / '. date('Y-12-31'); ?>">Bu yıl</option>
               <option value="<?php echo date(date('Y',strtotime('+1 year')).'-01-01') . ' / '. date(date('Y',strtotime('+1 year')).'-12-31'); ?>">Önümüzdeki yıl</option>
               <option value="ozel">Özel</option>
            </select>
         </div>
         <div class="rc-rl-field" id="ozel_tarih_filtresi" style="display:none;">
            <label for="tarihe_gore_filtre">Özel Tarih Aralığı</label>
            <input
               class="form-control rc-rl-input datetimepicker-range"
               placeholder="Tarih aralığını seçiniz.."
               type="text" id="tarihe_gore_filtre" style="display: none;"
            />
         </div>
      </div>
   </div>

   {{-- Modern Tablo Kartı --}}
   <div class="rc-rl-card">
      <div class="rc-rl-tablo-wrap">
         <table class="data-table table stripe hover nowrap rc-rl-table" id="randevu_liste" style="width:100%">
            <thead>
               <tr>
                  <th>Müşteri</th>
                  <th>Telefon Numarası</th>
                  <th>Hizmetler</th>
                  <th>Personel/Cihaz/Oda</th>
                  <th>Tarih</th>
                  <th>Saat</th>
                  <th>Durum</th>
                  <th>Oluşturan</th>
                  <th class="rc-rl-col-actions"></th>
               </tr>
            </thead>
            <tbody></tbody>
         </table>
      </div>
   </div>

</div>

<style>
/* =================================================================
   RANDEVU LİSTESİ — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */

.rc-rl-page {
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
.rc-rl-header {
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
.rc-rl-header-left,
.rc-rl-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
.rc-rl-title-row {
   display: flex;
   align-items: center;
   gap: 14px;
}
.rc-rl-icon-bubble {
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
.rc-rl-title {
   margin: 0;
   font-size: 19px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.2;
}
.rc-rl-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex;
   align-items: center;
   gap: 6px;
   flex-wrap: wrap;
}
.rc-rl-breadcrumb a {
   color: var(--rc-text-soft);
   text-decoration: none;
   transition: color .15s;
}
.rc-rl-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-rl-breadcrumb .rc-rl-sep { color: #cbd5e1; }
.rc-rl-breadcrumb .rc-rl-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === HEADER BUTONLAR === */
.rc-rl-btn {
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
.rc-rl-btn i { font-size: 14px; }
.rc-rl-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-rl-btn:active { transform: translateY(0); }
.rc-rl-btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
   box-shadow: 0 6px 16px rgba(22, 163, 74, .28);
}
.rc-rl-btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
}

/* === FİLTRE KARTI === */
.rc-rl-filter-card {
   background: #fff;
   border-radius: 14px;
   padding: 18px 20px 20px;
   margin-bottom: 18px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
}
.rc-rl-filter-head {
   display: inline-flex;
   align-items: center;
   gap: 8px;
   font-size: 12.5px;
   font-weight: 700;
   color: var(--rc-purple-dark);
   text-transform: uppercase;
   letter-spacing: .04em;
   padding-bottom: 14px;
   margin-bottom: 14px;
   border-bottom: 1px solid var(--rc-border);
   width: 100%;
}
.rc-rl-filter-head i {
   width: 28px; height: 28px;
   border-radius: 8px;
   background: var(--rc-purple-light);
   display: inline-flex;
   align-items: center;
   justify-content: center;
   font-size: 13px;
}
.rc-rl-filter-grid {
   display: grid;
   grid-template-columns: repeat(4, minmax(0, 1fr));
   gap: 14px;
}
.rc-rl-field {
   display: flex;
   flex-direction: column;
   gap: 6px;
   min-width: 0;
}
.rc-rl-field label {
   font-size: 11.5px;
   font-weight: 700;
   text-transform: uppercase;
   letter-spacing: .04em;
   color: var(--rc-text-soft);
   margin: 0;
}
.rc-rl-select,
.rc-rl-input {
   height: 42px !important;
   border: 1px solid var(--rc-border) !important;
   border-radius: 10px !important;
   padding: 0 14px !important;
   font-size: 13.5px !important;
   color: var(--rc-text) !important;
   background-color: #fafbfc !important;
   transition: border-color .15s, box-shadow .15s, background-color .15s;
   width: 100%;
   appearance: none;
   -webkit-appearance: none;
   -moz-appearance: none;
   background-repeat: no-repeat;
   background-position: right 12px center;
   background-size: 12px;
}
.rc-rl-select {
   background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
   padding-right: 36px !important;
}
.rc-rl-select:focus,
.rc-rl-input:focus {
   border-color: var(--rc-purple) !important;
   background-color: #fff !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12) !important;
   outline: none;
}

/* === KART === */
.rc-rl-card {
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
   padding: 8px;
   margin-bottom: 30px;
}

/* === TABLO WRAPPER === */
.rc-rl-tablo-wrap {
   width: 100%;
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
   border-radius: 10px;
}
.rc-rl-tablo-wrap #randevu_liste {
   min-width: 1200px;
}

/* === DATATABLES WRAPPER === */
.rc-rl-card .dataTables_wrapper {
   padding: 14px 14px 8px;
}
.rc-rl-card .dataTables_length,
.rc-rl-card .dataTables_filter {
   margin-bottom: 14px;
}
.rc-rl-card .dataTables_length label,
.rc-rl-card .dataTables_filter label {
   color: var(--rc-text-soft);
   font-size: 13px;
   font-weight: 500;
}
.rc-rl-card .dataTables_filter input {
   border: 1px solid var(--rc-border) !important;
   border-radius: 999px !important;
   padding: 8px 16px !important;
   margin-left: 8px;
   font-size: 13px;
   width: 220px;
   max-width: 100%;
   outline: none;
   transition: border-color .15s, box-shadow .15s;
}
.rc-rl-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-rl-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
}

/* === TABLO (KART SATIR YAPISI) ===
   Her tr bir "kart satir" gibi davranir: rounded, soft border,
   aralarinda dikey bosluk, hover'da hafif yukari kalkma + sol mor
   gradient seritin yanmasi. Thead kolon legend'i olarak korunur. */
.rc-rl-table {
   border-collapse: separate !important;
   border-spacing: 0 10px !important;
}
.rc-rl-table thead th {
   background: transparent !important;
   color: var(--rc-text-soft) !important;
   font-size: 11px !important;
   font-weight: 700 !important;
   text-transform: uppercase;
   letter-spacing: .06em;
   padding: 4px 14px 8px !important;
   border: none !important;
   white-space: nowrap;
}
.rc-rl-table thead th:first-child { padding-left: 22px !important; }

.rc-rl-table tbody td {
   padding: 16px 14px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-top: 1px solid var(--rc-border) !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   background: #fff !important;
   transition: border-color .2s, background .2s;
}
.rc-rl-table tbody td:first-child {
   border-left: 1px solid var(--rc-border) !important;
   border-top-left-radius: 12px;
   border-bottom-left-radius: 12px;
   padding-left: 26px !important;
   position: relative;
}
.rc-rl-table tbody td:last-child {
   border-right: 1px solid var(--rc-border) !important;
   border-top-right-radius: 12px;
   border-bottom-right-radius: 12px;
}

/* Sol mor gradient accent serit */
.rc-rl-table tbody td:first-child::before {
   content: '';
   position: absolute;
   left: 8px; top: 12px; bottom: 12px;
   width: 3px;
   border-radius: 3px;
   background: var(--rc-border);
   transition: background .2s, width .2s;
}

/* Hover: kart yukari kalk + accent yan + golge */
.rc-rl-table tbody tr {
   transition: transform .2s, box-shadow .2s;
}
.rc-rl-table tbody tr:hover {
   transform: translateY(-2px);
}
.rc-rl-table tbody tr:hover td {
   border-color: var(--rc-purple-soft) !important;
   background: #fff !important;
   box-shadow: 0 0 0 transparent;
}
.rc-rl-table tbody tr:hover td:first-child {
   box-shadow: -6px 8px 18px -8px rgba(92, 0, 142, .18);
}
.rc-rl-table tbody tr:hover td:last-child {
   box-shadow: 6px 8px 18px -8px rgba(92, 0, 142, .18);
}
.rc-rl-table tbody tr:hover td:not(:first-child):not(:last-child) {
   box-shadow: 0 8px 18px -8px rgba(92, 0, 142, .18);
}
.rc-rl-table tbody tr:hover td:first-child::before {
   background: linear-gradient(180deg, var(--rc-purple-dark), var(--rc-purple), var(--rc-pink));
   width: 4px;
}

/* Stripe'i devre disi birak — kart goruntusu icin gerekli degil */
.rc-rl-table.stripe tbody tr.odd td,
.rc-rl-table.stripe tbody tr.even td {
   background: #fff !important;
}

/* === RESPONSIVE PLUGIN'I DEVRE DIŞI === */
#randevu_liste thead th,
#randevu_liste tbody td { display: table-cell !important; }
#randevu_liste td.dtr-control,
#randevu_liste th.dtr-control { display: none !important; }
#randevu_liste tr.child { display: none !important; }
#randevu_liste td.dtr-hidden,
#randevu_liste th.dtr-hidden,
#randevu_liste td.none,
#randevu_liste th.none { display: table-cell !important; }

/* === DURUM BADGE OVERRIDE === */
.rc-rl-table tbody td .btn.btn-warning,
.rc-rl-table tbody td .btn.btn-success,
.rc-rl-table tbody td .btn.btn-primary,
.rc-rl-table tbody td .btn.btn-danger,
.rc-rl-table tbody td .btn.btn-dark {
   border-radius: 999px !important;
   padding: 6px 14px !important;
   font-size: 12px !important;
   font-weight: 700 !important;
   line-height: 1.4 !important;
   display: inline-block !important;
   width: auto !important;
   min-width: 100px;
   text-align: center;
   box-shadow: none !important;
   pointer-events: none;
}
.rc-rl-table tbody td .btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border: 1px solid #fde68a !important;
}
.rc-rl-table tbody td .btn.btn-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border: 1px solid #bbf7d0 !important;
}
.rc-rl-table tbody td .btn.btn-primary {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border: 1px solid var(--rc-purple-soft) !important;
}
.rc-rl-table tbody td .btn.btn-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
   border: 1px solid #fecaca !important;
}
.rc-rl-table tbody td .btn.btn-dark {
   background: #f1f5f9 !important;
   color: #475569 !important;
   border: 1px solid #e2e8f0 !important;
}

/* === İŞLEMLER DROPDOWN === */
.rc-rl-table tbody td .dropdown .dropdown-toggle.btn-link {
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
.rc-rl-table tbody td .dropdown .dropdown-toggle.btn-link:hover {
   background: var(--rc-purple-soft) !important;
   transform: scale(1.05);
}
.rc-rl-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 180px !important;
}
.rc-rl-table tbody td .dropdown-menu .dropdown-item {
   padding: 9px 12px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
}
.rc-rl-table tbody td .dropdown-menu .dropdown-item:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-rl-table tbody td .dropdown-menu .dropdown-item i {
   width: 18px;
   margin-right: 6px;
   color: var(--rc-purple);
}

/* === PAGINATION === */
.rc-rl-card .dataTables_paginate {
   padding: 14px;
}
.rc-rl-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-rl-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-rl-card .dataTables_paginate .paginate_button.current,
.rc-rl-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-rl-card .dataTables_info {
   color: var(--rc-text-soft);
   font-size: 12.5px;
   padding: 14px !important;
}

/* === SORT İKONLARI === */
.rc-rl-table thead th[class*="sorting"] {
   position: relative;
   cursor: pointer;
}
.rc-rl-table thead th.sorting_asc,
.rc-rl-table thead th.sorting_desc {
   color: var(--rc-purple-dark) !important;
}

/* === RESPONSIVE: TABLET (≤1024px) === */
@media (max-width: 1024px) {
   .rc-rl-header { padding: 14px 16px; }
   .rc-rl-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-rl-title { font-size: 17px; }
   .rc-rl-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-rl-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
   .rc-rl-card { padding: 6px; }
   .rc-rl-card .dataTables_wrapper { padding: 10px 10px 4px; }
   .rc-rl-card .dataTables_filter input { width: 180px; }
   .rc-rl-table thead th,
   .rc-rl-table tbody td { padding: 12px 10px !important; font-size: 13px; }
}

/* === RESPONSIVE: MOBILE (≤768px) — TABLE → CARD === */
@media (max-width: 768px) {
   .rc-rl-header {
      padding: 12px 14px;
      border-radius: 12px;
   }
   .rc-rl-header-left { width: 100%; }
   .rc-rl-header-right { width: 100%; justify-content: stretch; }
   .rc-rl-header-right .rc-rl-btn { flex: 1; justify-content: center; }
   .rc-rl-title { font-size: 16px; }
   .rc-rl-breadcrumb { font-size: 11.5px; }

   .rc-rl-filter-card { padding: 14px 14px 16px; border-radius: 12px; }
   .rc-rl-filter-grid { grid-template-columns: 1fr; gap: 10px; }

   .rc-rl-card { padding: 4px; border-radius: 12px; }
   .rc-rl-card .dataTables_wrapper { padding: 8px 8px 4px; }
   .rc-rl-card .dataTables_filter { float: none; text-align: left; }
   .rc-rl-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-rl-card .dataTables_filter label { display: block; width: 100%; }
   .rc-rl-card .dataTables_length { display: none; }

   /* TABLO -> KART GORUNUMU */
   .rc-rl-tablo-wrap { overflow: visible; }
   .rc-rl-tablo-wrap #randevu_liste { min-width: 0; width: 100% !important; }

   /* Desktop kart-satir border-spacing/transform'larini sifirla */
   .rc-rl-table { border-spacing: 0 !important; }
   .rc-rl-table tbody tr { transform: none !important; }

   .rc-rl-table,
   .rc-rl-table thead,
   .rc-rl-table tbody,
   .rc-rl-table tr,
   .rc-rl-table td,
   .rc-rl-table th {
      display: block !important;
      width: 100% !important;
   }
   .rc-rl-table thead { display: none !important; }

   .rc-rl-table tbody tr {
      background: #fff;
      border: 1px solid var(--rc-border);
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 12px;
      box-shadow: 0 1px 2px rgba(17, 24, 39, .03);
      position: relative;
   }
   .rc-rl-table tbody tr:hover td,
   .rc-rl-table tbody tr.odd td,
   .rc-rl-table tbody td {
      background: transparent !important;
      border: none !important;
      padding: 6px 0 !important;
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      font-size: 13px;
      border-radius: 0 !important;
      box-shadow: none !important;
   }
   /* Desktop'taki ilk td accent serit + radius'u kapat */
   .rc-rl-table tbody td:first-child,
   .rc-rl-table tbody td:last-child {
      padding-left: 0 !important;
      padding-right: 0 !important;
      border: none !important;
      border-radius: 0 !important;
   }
   .rc-rl-table tbody td:first-child::before {
      content: attr(data-label) !important;
      position: static !important;
      width: auto !important;
      background: transparent !important;
      font-size: 11px;
      font-weight: 700;
      color: var(--rc-text-soft);
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
      min-width: 115px;
      border-radius: 0;
   }
   .rc-rl-table tbody td::before {
      content: attr(data-label);
      font-size: 11px;
      font-weight: 700;
      color: var(--rc-text-soft);
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
      min-width: 115px;
   }
   .rc-rl-table tbody td.rc-rl-td-actions,
   .rc-rl-table tbody td.rc-rl-td-durum {
      justify-content: flex-end;
   }
   .rc-rl-table tbody td .btn.btn-warning,
   .rc-rl-table tbody td .btn.btn-success,
   .rc-rl-table tbody td .btn.btn-primary,
   .rc-rl-table tbody td .btn.btn-danger,
   .rc-rl-table tbody td .btn.btn-dark {
      min-width: 0;
   }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-rl-header-right { flex-direction: column; }
   .rc-rl-header-right .rc-rl-btn { width: 100%; }
   .rc-rl-title-row { gap: 10px; }
   .rc-rl-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
}
</style>

<script>
/* Zaman = "Özel" oldugunda ozel tarih kutusunun parent'ini de gosterip gizle.
   custom.js sadece input'un kendisini toggle ediyor — modern grid'de parent
   field konteyner da var, onu da senkron tutuyoruz. */
$(document).on('change', '#zamana_gore_filtre', function(){
   if($(this).val() === 'ozel') $('#ozel_tarih_filtresi').show();
   else $('#ozel_tarih_filtresi').hide();
});
$(document).ready(function(){
   if($('#zamana_gore_filtre').val() === 'ozel') $('#ozel_tarih_filtresi').show();
});

/* Responsive plugin'in dinamik attigi siniflari geri al */
$(document).on('init.dt', '#randevu_liste', function() {
   var $t = $('#randevu_liste');
   $t.removeClass('dtr-inline collapsed');
   setTimeout(function() {
      $t.find('td, th').removeClass('dtr-hidden none');
      $t.find('tr.child').remove();
      $t.find('td.dtr-control, th.dtr-control').remove();
   }, 50);
});

/* Mobil kart gorunumu icin her hucreye sutun adi etiketi ekle.
   DataTables her data update'inde row'lari yeniden cizdigi icin
   draw.dt eventinde tekrar uygula. */
(function(){
   var labels = [
      'Müşteri',
      'Telefon',
      'Hizmetler',
      'Personel/Cihaz/Oda',
      'Tarih',
      'Saat',
      'Durum',
      'Oluşturan',
      'İşlemler'
   ];
   var tdClasses = [
      'rc-rl-td-musteri',
      'rc-rl-td-telefon',
      'rc-rl-td-hizmetler',
      'rc-rl-td-personel',
      'rc-rl-td-tarih',
      'rc-rl-td-saat',
      'rc-rl-td-durum',
      'rc-rl-td-olusturan',
      'rc-rl-td-actions'
   ];
   function applyLabels(){
      $('#randevu_liste tbody tr').each(function(){
         $(this).find('> td').each(function(idx){
            var label = labels[idx] || '';
            var cls = tdClasses[idx] || '';
            $(this).attr('data-label', label);
            if(cls && !$(this).hasClass(cls)) $(this).addClass(cls);
         });
      });
   }
   $(document).on('draw.dt init.dt', '#randevu_liste', function(){
      setTimeout(applyLabels, 80);
   });
   $(document).ready(function(){ setTimeout(applyLabels, 200); });
})();
</script>

@endsection
