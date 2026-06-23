@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-rl-page">

   {{-- Modern Page Header — baslik + filtreler + aksiyon TEK blokta --}}
   <div class="rc-rl-header rc-rl-header--merged">
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

      <div class="rc-rl-header-filters">
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
               <option selected value="">Tüm Durumlar</option>
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
            <label for="tarihe_gore_filtre">Özel Tarih</label>
            <input
               class="form-control rc-rl-input datetimepicker-range"
               placeholder="Tarih aralığı.."
               type="text" id="tarihe_gore_filtre" style="display: none;"
            />
         </div>
      </div>

      <div class="rc-rl-header-right">
         <a href="#" data-toggle="modal" data-target="#modal-view-event-add-v2"
            class="rc-rl-btn rc-rl-btn-success yenieklebuton">
            <i class="fa fa-plus"></i><span>Yeni Randevu</span>
         </a>
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

/* === BIRLESIK HEADER: baslik + filtreler + aksiyon tek blokta === */
.rc-rl-header--merged { gap: 14px 22px; }
.rc-rl-header--merged .rc-rl-header-left { flex: 0 1 auto; }
.rc-rl-header--merged .rc-rl-header-right { flex: 0 0 auto; }
.rc-rl-header-filters {
   display: flex;
   align-items: flex-end;
   gap: 12px;
   flex: 1 1 auto;
   flex-wrap: wrap;
   justify-content: center;
   min-width: 0;
}
.rc-rl-header-filters .rc-rl-field {
   flex: 1 1 150px;
   min-width: 130px;
   max-width: 230px;
}
.rc-rl-field {
   display: flex;
   flex-direction: column;
   gap: 5px;
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

/* === TABLO KARTI (konteyner) ===
   Kartli grid kaldirildi — randevular artik temiz, modern bir
   veri tablosu olarak listeleniyor. */
.rc-rl-card {
   background: #fff;
   border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 6px 24px rgba(92, 0, 142, .05);
   padding: 4px;
   margin-bottom: 30px;
   border: 1px solid var(--rc-border);
   overflow: hidden;
}

/* === TABLO WRAPPER === */
.rc-rl-tablo-wrap {
   width: 100%;
   border-radius: 12px;
   overflow-x: auto;
}

/* === DATATABLES WRAPPER === */
.rc-rl-card .dataTables_wrapper {
   padding: 14px 16px 8px;
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
   padding: 9px 18px !important;
   margin-left: 8px;
   font-size: 13px;
   width: 240px;
   max-width: 100%;
   outline: none;
   background: #fff !important;
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
   background: #fff !important;
}

/* === DataTables responsive plugin'ini devre disi === */
#randevu_liste td.dtr-control,
#randevu_liste th.dtr-control { display: none !important; }
#randevu_liste tr.child { display: none !important; }
#randevu_liste td.dtr-hidden,
#randevu_liste th.dtr-hidden,
#randevu_liste td.none,
#randevu_liste th.none { display: block !important; }

/* === MODERN VERI TABLOSU ===
   thead geri acildi; her satir temiz bir tablo satiri (kart degil). */
.rc-rl-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
   width: 100% !important;
   min-width: 760px;
}

/* BASLIK SATIRI */
.rc-rl-table thead { display: table-header-group !important; }
.rc-rl-table thead th {
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
.rc-rl-table thead th:first-child { border-top-left-radius: 12px; }
.rc-rl-table thead th:last-child  { border-top-right-radius: 12px; }
/* DataTables siralama oklari */
.rc-rl-table thead th.sorting,
.rc-rl-table thead th.sorting_asc,
.rc-rl-table thead th.sorting_desc {
   cursor: pointer;
   position: relative;
   padding-right: 26px;
}
.rc-rl-table thead th.sorting::after,
.rc-rl-table thead th.sorting_asc::after,
.rc-rl-table thead th.sorting_desc::after {
   position: absolute;
   right: 12px;
   top: 50%;
   transform: translateY(-50%);
   font-family: "FontAwesome";
   font-size: 11px;
   opacity: .35;
}
.rc-rl-table thead th.sorting::after     { content: "\f0dc"; }
.rc-rl-table thead th.sorting_asc::after { content: "\f0de"; opacity: .9; }
.rc-rl-table thead th.sorting_desc::after{ content: "\f0dd"; opacity: .9; }

/* GOVDE SATIRLARI */
.rc-rl-table tbody tr {
   background: #fff;
   transition: background .12s ease;
}
.rc-rl-table.stripe tbody tr.even,
.rc-rl-table tbody tr.even { background: #fcfbfe; }
.rc-rl-table tbody tr:hover,
.rc-rl-table.stripe tbody tr.odd:hover,
.rc-rl-table.stripe tbody tr.even:hover { background: #f7f1fd; }

.rc-rl-table tbody td {
   padding: 14px 16px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   line-height: 1.5;
   white-space: normal;
}
.rc-rl-table tbody tr:last-child td { border-bottom: none !important; }

/* MUSTERI — satir basligi */
.rc-rl-table tbody td.rc-rl-td-musteri {
   font-size: 14px;
   font-weight: 700;
   color: var(--rc-text);
   letter-spacing: -.1px;
}
/* TELEFON */
.rc-rl-table tbody td.rc-rl-td-telefon {
   color: var(--rc-text-soft);
   font-weight: 500;
   white-space: nowrap;
}
/* HIZMETLER */
.rc-rl-table tbody td.rc-rl-td-hizmetler {
   color: var(--rc-text);
   max-width: 280px;
   overflow-wrap: anywhere;
}
/* PERSONEL/CIHAZ/ODA */
.rc-rl-table tbody td.rc-rl-td-personel {
   color: var(--rc-text-soft);
   overflow-wrap: anywhere;
}
/* TARIH + SAAT */
.rc-rl-table tbody td.rc-rl-td-tarih,
.rc-rl-table tbody td.rc-rl-td-saat {
   white-space: nowrap;
   font-weight: 600;
   color: var(--rc-text);
}
/* OLUSTURAN */
.rc-rl-table tbody td.rc-rl-td-olusturan {
   color: var(--rc-text-soft);
   font-size: 12.5px;
   white-space: nowrap;
}
/* ISLEMLER hucresi */
.rc-rl-table tbody td.rc-rl-td-actions {
   text-align: right;
   width: 60px;
   white-space: nowrap;
}

/* DURUM ROZETI (server btn class'larini pill'e cevir) */
.rc-rl-table tbody td .btn.btn-warning,
.rc-rl-table tbody td .btn.btn-success,
.rc-rl-table tbody td .btn.btn-primary,
.rc-rl-table tbody td .btn.btn-danger,
.rc-rl-table tbody td .btn.btn-dark {
   border-radius: 999px !important;
   padding: 5px 13px !important;
   font-size: 11px !important;
   font-weight: 700 !important;
   line-height: 1.4 !important;
   display: inline-block !important;
   width: auto !important;
   min-width: 0 !important;
   text-align: center;
   box-shadow: none !important;
   pointer-events: none;
   text-transform: uppercase;
   letter-spacing: .03em;
   white-space: nowrap;
}
/* Durum rozetleri — dolgulu/canli (belirgin), beyaz yazi */
.rc-rl-table tbody td .btn.btn-warning {
   background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
   color: #fff !important;
   border: none !important;
   box-shadow: 0 2px 6px rgba(245, 158, 11, .30) !important;
}
.rc-rl-table tbody td .btn.btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important;
   color: #fff !important;
   border: none !important;
   box-shadow: 0 2px 6px rgba(22, 163, 74, .30) !important;
}
.rc-rl-table tbody td .btn.btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border: none !important;
   box-shadow: 0 2px 6px rgba(92, 0, 142, .30) !important;
}
.rc-rl-table tbody td .btn.btn-danger {
   background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
   color: #fff !important;
   border: none !important;
   box-shadow: 0 2px 6px rgba(220, 38, 38, .30) !important;
}
.rc-rl-table tbody td .btn.btn-dark {
   background: linear-gradient(135deg, #475569 0%, #64748b 100%) !important;
   color: #fff !important;
   border: none !important;
   box-shadow: 0 2px 6px rgba(71, 85, 105, .28) !important;
}

/* === İŞLEMLER DROPDOWN === */
.rc-rl-table tbody td .dropdown .dropdown-toggle.btn-link {
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
.rc-rl-table tbody td .dropdown .dropdown-toggle.btn-link:hover {
   transform: scale(1.08);
   filter: brightness(1.06);
}
.rc-rl-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 180px !important;
}
.rc-rl-table tbody td .dropdown-menu .dropdown-item {
   /* Tema ikonu absolute (left:16px) konumluyor; sol padding korunmali
      yoksa ikon yazinin uzerine biner. */
   padding: 9px 14px 9px 42px !important;
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

/* === BOS DURUM === */
.rc-rl-table tbody td.dataTables_empty {
   text-align: center !important;
   padding: 50px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   background: #fff !important;
}

/* === RESPONSIVE: TABLET LANDSCAPE (≤1024px) ===
   Tek satira sigmazsa filtreler basligin altina, tam genislige gecer. */
@media (max-width: 1024px) {
   .rc-rl-header { padding: 14px 16px; }
   .rc-rl-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-rl-title { font-size: 17px; }
   .rc-rl-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-rl-header-filters {
      flex-basis: 100%;
      order: 3;
      justify-content: flex-start;
   }
   .rc-rl-header-filters .rc-rl-field { max-width: none; }
   .rc-rl-card .dataTables_wrapper { padding: 12px 10px 6px; }
   .rc-rl-card .dataTables_filter input { width: 200px; }
   .rc-rl-table tbody td { padding: 12px 14px !important; }
}

/* === RESPONSIVE: TABLET PORTRAIT + MOBILE (≤900px) — yigin (etiket: deger) ===
   Sidebar acikken icerik daraldigi icin bu noktada tablo yatay kaymak yerine
   her satir kompakt bir blok olur; her hucre solda kucuk etiket, sagda deger.
   Bu KART degil — standart responsive tablo desenidir (JS data-label ekliyor). */
@media (max-width: 900px) {
   .rc-rl-header {
      padding: 12px 14px;
      border-radius: 12px;
   }
   .rc-rl-header-left { width: 100%; }
   .rc-rl-header-right { width: 100%; justify-content: stretch; }
   .rc-rl-header-right .rc-rl-btn { flex: 1; justify-content: center; }
   .rc-rl-title { font-size: 16px; }
   .rc-rl-breadcrumb { font-size: 11.5px; }

   .rc-rl-header-filters {
      flex-basis: 100%;
      order: 3;
      width: 100%;
      justify-content: stretch;
      gap: 10px;
   }
   .rc-rl-header-filters .rc-rl-field { flex: 1 1 140px; max-width: none; }

   .rc-rl-card { padding: 8px; border-radius: 14px; }
   .rc-rl-card .dataTables_wrapper { padding: 10px 8px 4px; }
   .rc-rl-card .dataTables_filter { float: none; text-align: left; }
   .rc-rl-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-rl-card .dataTables_filter label { display: block; width: 100%; }
   .rc-rl-card .dataTables_length { display: none; }

   /* Tabloyu blok-yigin yap */
   .rc-rl-tablo-wrap { overflow-x: visible; }
   .rc-rl-table { min-width: 0 !important; display: block !important; }
   .rc-rl-table thead { display: none !important; }
   .rc-rl-table tbody { display: block !important; }
   .rc-rl-table tbody tr {
      display: block !important;
      background: #fff !important;
      border: 1px solid var(--rc-border) !important;
      border-radius: 14px !important;
      box-shadow: 0 2px 8px rgba(92, 0, 142, .05);
      padding: 6px 4px;
      margin-bottom: 12px;
   }
   .rc-rl-table tbody tr:last-child { margin-bottom: 0; }
   .rc-rl-table tbody td {
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      padding: 9px 14px !important;
      border: none !important;
      border-bottom: 1px solid #f4f0fa !important;
      text-align: right;
      white-space: normal !important;
   }
   .rc-rl-table tbody tr td:last-child { border-bottom: none !important; }
   .rc-rl-table tbody td::before {
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
   /* Musteri = baslik satiri (etiketsiz, buyuk) */
   .rc-rl-table tbody td.rc-rl-td-musteri {
      font-size: 15px !important;
      font-weight: 700;
      background: #faf7fe;
      border-radius: 10px;
      margin: 2px 4px 6px;
   }
   .rc-rl-table tbody td.rc-rl-td-musteri::before { content: none; }
   /* Bos hucreleri (ornek: durum=2 islemler bos) gizle */
   .rc-rl-table tbody td:empty { display: none !important; }
}

/* === RESPONSIVE: KUCUK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-rl-header-right { flex-direction: column; }
   .rc-rl-header-right .rc-rl-btn { width: 100%; }
   .rc-rl-title-row { gap: 10px; }
   .rc-rl-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-rl-table tbody td { padding: 8px 12px !important; font-size: 12.5px; }
   .rc-rl-table tbody td.rc-rl-td-musteri { font-size: 14.5px !important; }
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
