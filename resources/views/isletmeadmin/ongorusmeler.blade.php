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
         @yetki('pazarlama.sms_gonder')
         <a id="secilenlere_sms_gonder" href="#" class="rc-og-btn rc-og-btn-primary yenieklebuton502">
            <i class="fa fa-envelope"></i><span>SMS Gönder</span>
         </a>
         @endyetki
      </div>
   </div>

   {{-- Modern Table Card --}}
   <div class="rc-og-card">
      <form id="on_gorusme_liste_form">
         <div class="on-gorusme-tablo-wrap">
            <table class="data-table rc-og-table" id="on_gorusme_liste" style="width:100%">
               <thead>
                  <tr>
                     <th class="rc-og-col-check">
                        <div class="dt-checkbox">
                           <input type="checkbox" id="hepsini_sec_liste" />
                           <span class="dt-checkbox-label"></span>
                        </div>
                     </th>
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
   ÖN GÖRÜŞMELER — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
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
.rc-og-breadcrumb a {
   color: var(--rc-text-soft);
   text-decoration: none;
   transition: color .15s;
}
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
.rc-og-btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
}

/* === KART === */
.rc-og-card {
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
   padding: 8px;
   margin-bottom: 30px;
}

/* === TABLO WRAPPER === */
.on-gorusme-tablo-wrap {
   width: 100%;
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
   border-radius: 10px;
}
.on-gorusme-tablo-wrap #on_gorusme_liste {
   min-width: 1100px;
}

/* === DATATABLES WRAPPER (search/length/info/pagination) === */
.rc-og-card .dataTables_wrapper {
   padding: 14px 14px 8px;
}
.rc-og-card .dataTables_length,
.rc-og-card .dataTables_filter {
   margin-bottom: 14px;
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
   padding: 8px 16px !important;
   margin-left: 8px;
   font-size: 13px;
   width: 220px;
   max-width: 100%;
   outline: none;
   transition: border-color .15s, box-shadow .15s;
}
.rc-og-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-og-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
}

/* === TABLO === */
.rc-og-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
}
.rc-og-table thead th {
   background: #fafbfc !important;
   color: var(--rc-text-soft) !important;
   font-size: 11.5px !important;
   font-weight: 700 !important;
   text-transform: uppercase;
   letter-spacing: .04em;
   padding: 14px 14px !important;
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   white-space: nowrap;
}
.rc-og-table thead th:first-child { border-top-left-radius: 10px; }
.rc-og-table thead th:last-child { border-top-right-radius: 10px; }
.rc-og-table tbody td {
   padding: 14px 14px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   background: #fff;
}
.rc-og-table tbody tr {
   transition: background-color .15s;
}
.rc-og-table tbody tr:hover td {
   background: var(--rc-purple-light) !important;
}
.rc-og-table tbody tr:last-child td {
   border-bottom: none !important;
}
.rc-og-table.stripe tbody tr.odd td {
   background: #fbfaff;
}
.rc-og-table.stripe tbody tr.odd:hover td {
   background: var(--rc-purple-light) !important;
}

/* === RESPONSIVE PLUGIN'I DEVRE DIŞI === */
#on_gorusme_liste thead th,
#on_gorusme_liste tbody td { display: table-cell !important; }
#on_gorusme_liste td.dtr-control,
#on_gorusme_liste th.dtr-control { display: none !important; }
#on_gorusme_liste tr.child { display: none !important; }
#on_gorusme_liste td.dtr-hidden,
#on_gorusme_liste th.dtr-hidden,
#on_gorusme_liste td.none,
#on_gorusme_liste th.none { display: table-cell !important; }

/* === CHECKBOX === */
.rc-og-table .dt-checkbox {
   display: inline-flex;
   align-items: center;
}
.rc-og-table .dt-checkbox input[type="checkbox"] {
   width: 18px;
   height: 18px;
   accent-color: var(--rc-purple-dark);
   cursor: pointer;
}

/* === DURUM BADGE OVERRIDE === */
/* Backend "btn btn-warning/success/danger btn-block" uretiyor — modern badge'e cevir */
.rc-og-table tbody td .btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border: 1px solid #fde68a !important;
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
.rc-og-table tbody td .btn.btn-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border: 1px solid #bbf7d0 !important;
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
}
.rc-og-table tbody td .btn.btn-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
   border: 1px solid #fecaca !important;
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
   cursor: pointer;
}
.rc-og-table tbody td .btn.btn-danger:hover { filter: brightness(.95); }

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
   min-width: 180px !important;
}
.rc-og-table tbody td .dropdown-menu .dropdown-item {
   padding: 9px 12px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
}
.rc-og-table tbody td .dropdown-menu .dropdown-item:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-og-table tbody td .dropdown-menu .dropdown-item i {
   width: 18px;
   margin-right: 6px;
   color: var(--rc-purple);
}

/* === PAGINATION === */
.rc-og-card .dataTables_paginate {
   padding: 14px;
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
   padding: 14px !important;
}

/* === SORT İKONLARI === */
.rc-og-table thead th[class*="sorting"] {
   position: relative;
   cursor: pointer;
}
.rc-og-table thead th.sorting_asc,
.rc-og-table thead th.sorting_desc {
   color: var(--rc-purple-dark) !important;
}

/* === RESPONSIVE: TABLET (≤1024px) === */
@media (max-width: 1024px) {
   .rc-og-header { padding: 14px 16px; }
   .rc-og-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-og-title { font-size: 17px; }
   .rc-og-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-og-card { padding: 6px; }
   .rc-og-card .dataTables_wrapper { padding: 10px 10px 4px; }
   .rc-og-card .dataTables_filter input { width: 180px; }
   .rc-og-table thead th,
   .rc-og-table tbody td { padding: 12px 10px !important; font-size: 13px; }
}

/* === RESPONSIVE: MOBILE (≤768px) — TABLE → CARD === */
@media (max-width: 768px) {
   .rc-og-header {
      padding: 12px 14px;
      border-radius: 12px;
   }
   .rc-og-header-left { width: 100%; }
   .rc-og-header-right { width: 100%; justify-content: stretch; }
   .rc-og-header-right .rc-og-btn { flex: 1; justify-content: center; }
   .rc-og-title { font-size: 16px; }
   .rc-og-breadcrumb { font-size: 11.5px; }

   .rc-og-card { padding: 4px; border-radius: 12px; }
   .rc-og-card .dataTables_wrapper { padding: 8px 8px 4px; }
   .rc-og-card .dataTables_filter { float: none; text-align: left; }
   .rc-og-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-og-card .dataTables_filter label { display: block; width: 100%; }
   .rc-og-card .dataTables_length { display: none; }

   /* TABLO -> KART GORUNUMU */
   .on-gorusme-tablo-wrap { overflow: visible; }
   .on-gorusme-tablo-wrap #on_gorusme_liste { min-width: 0; width: 100% !important; }

   .rc-og-table,
   .rc-og-table thead,
   .rc-og-table tbody,
   .rc-og-table tr,
   .rc-og-table td,
   .rc-og-table th {
      display: block !important;
      width: 100% !important;
   }
   .rc-og-table thead { display: none !important; }

   .rc-og-table tbody tr {
      background: #fff;
      border: 1px solid var(--rc-border);
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 12px;
      box-shadow: 0 1px 2px rgba(17, 24, 39, .03);
      position: relative;
   }
   .rc-og-table tbody tr:hover td,
   .rc-og-table tbody tr.odd td,
   .rc-og-table tbody td {
      background: transparent !important;
      border: none !important;
      padding: 6px 0 !important;
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      font-size: 13px;
   }
   .rc-og-table tbody td::before {
      content: attr(data-label);
      font-size: 11px;
      font-weight: 700;
      color: var(--rc-text-soft);
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
      min-width: 105px;
   }
   .rc-og-table tbody td.rc-og-td-actions,
   .rc-og-table tbody td.rc-og-td-durum {
      justify-content: flex-end;
   }
   .rc-og-table tbody td.rc-og-td-check {
      position: absolute;
      top: 12px; right: 12px;
      padding: 0 !important;
      justify-content: flex-end;
   }
   .rc-og-table tbody td.rc-og-td-check::before { display: none; }
   .rc-og-table tbody td .btn.btn-warning,
   .rc-og-table tbody td .btn.btn-success,
   .rc-og-table tbody td .btn.btn-danger {
      min-width: 0;
   }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-og-header-right { flex-direction: column; }
   .rc-og-header-right .rc-og-btn { width: 100%; }
   .rc-og-title-row { gap: 10px; }
   .rc-og-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
}
</style>

<script>
/* Responsive plugin'in dinamik attigi siniflari geri al */
$(document).on('init.dt', '#on_gorusme_liste', function() {
   var $t = $('#on_gorusme_liste');
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
      '',           // checkbox
      'Oluşturma',
      'Müşteri',
      'Müşteri Tipi',
      'Telefon',
      'Randevu Tarihi',
      'Ön Görüşme Nedeni',
      'Görüşmeyi Yapan',
      'Durum',
      'İşlemler'
   ];
   var tdClasses = [
      'rc-og-td-check',
      'rc-og-td-olusturma',
      'rc-og-td-musteri',
      'rc-og-td-tipi',
      'rc-og-td-telefon',
      'rc-og-td-randevu',
      'rc-og-td-neden',
      'rc-og-td-yapan',
      'rc-og-td-durum',
      'rc-og-td-actions'
   ];
   function applyLabels(){
      $('#on_gorusme_liste tbody tr').each(function(){
         $(this).find('> td').each(function(idx){
            var label = labels[idx] || '';
            var cls = tdClasses[idx] || '';
            $(this).attr('data-label', label);
            if(cls && !$(this).hasClass(cls)) $(this).addClass(cls);
         });
      });
   }
   $(document).on('draw.dt init.dt', '#on_gorusme_liste', function(){
      setTimeout(applyLabels, 80);
   });
   $(document).ready(function(){ setTimeout(applyLabels, 200); });
})();
</script>

@endsection()
