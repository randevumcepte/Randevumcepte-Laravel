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

   {{-- Kart Grid Tablosu --}}
   <div class="rc-og-card">
      <form id="on_gorusme_liste_form">
         <div class="on-gorusme-tablo-wrap">
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
                     <th>İşlemler</th>
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
   ÖN GÖRÜŞMELER — KART YAPISI (tüm ekranlarda)
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

/* === DIŞ KAP === */
.rc-og-card {
   background: transparent;
   margin-bottom: 30px;
}
.on-gorusme-tablo-wrap {
   width: 100%;
   overflow: visible;
}
.on-gorusme-tablo-wrap #on_gorusme_liste {
   min-width: 0 !important;
   width: 100% !important;
}

/* === DATATABLES WRAPPER (search/length/info/pagination) === */
.rc-og-card .dataTables_wrapper {
   padding: 6px 0;
}
.rc-og-card .dataTables_length,
.rc-og-card .dataTables_filter {
   margin-bottom: 16px;
   padding: 0 8px;
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
   background: #fff;
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
   background: #fff;
}

/* =================================================================
   KART YAPISI — tüm ekranlar
   ================================================================= */

/* THEAD GİZLE (sıralama hâlâ JS ile çalışır, sadece UI yok) */
.rc-og-table thead { display: none !important; }

/* TABLE/TBODY -> Grid Container */
.rc-og-table,
.rc-og-table tbody {
   display: block !important;
   width: 100% !important;
   border: none !important;
}
.rc-og-table tbody {
   display: grid !important;
   grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
   gap: 16px;
   padding: 4px;
}

/* TR -> Kart */
.rc-og-table tbody tr {
   display: grid !important;
   grid-template-columns: 1fr 1fr;
   grid-template-areas:
      "musteri durum"
      "tipi tipi"
      "randevu telefon"
      "yapan yapan"
      "neden neden"
      "olusturma islemler";
   gap: 12px;
   background: #fff !important;
   border: 1px solid var(--rc-border) !important;
   border-radius: 16px !important;
   padding: 18px !important;
   box-shadow: 0 1px 3px rgba(17,24,39,.04) !important;
   transition: transform .2s, box-shadow .2s, border-color .2s !important;
   position: relative;
}
.rc-og-table tbody tr:hover {
   transform: translateY(-3px);
   box-shadow: 0 14px 30px rgba(92, 0, 142, .12) !important;
   border-color: var(--rc-purple-soft) !important;
}

/* Striped'i kaldır */
.rc-og-table.stripe tbody tr.odd,
.rc-og-table tbody tr.odd td,
.rc-og-table tbody tr:hover td {
   background: transparent !important;
}

/* TD -> Kart İçi Alan */
.rc-og-table tbody td {
   display: flex !important;
   flex-direction: column !important;
   align-items: flex-start;
   gap: 3px !important;
   padding: 0 !important;
   border: none !important;
   background: transparent !important;
   font-size: 13.5px;
   color: var(--rc-text);
   min-width: 0;
   width: auto !important;
}
.rc-og-table tbody td::before {
   content: attr(data-label);
   font-size: 10.5px;
   font-weight: 700;
   color: var(--rc-text-soft);
   text-transform: uppercase;
   letter-spacing: .05em;
   display: block;
   line-height: 1.2;
}

/* Sutun adlari -> Grid Areas */
.rc-og-table tbody td.rc-og-td-musteri { grid-area: musteri; }
.rc-og-table tbody td.rc-og-td-tipi { grid-area: tipi; }
.rc-og-table tbody td.rc-og-td-telefon { grid-area: telefon; }
.rc-og-table tbody td.rc-og-td-randevu { grid-area: randevu; }
.rc-og-table tbody td.rc-og-td-yapan { grid-area: yapan; }
.rc-og-table tbody td.rc-og-td-neden { grid-area: neden; }
.rc-og-table tbody td.rc-og-td-durum { grid-area: durum; justify-self: end; align-items: flex-end; }
.rc-og-table tbody td.rc-og-td-olusturma { grid-area: olusturma; }
.rc-og-table tbody td.rc-og-td-islemler { grid-area: islemler; justify-self: end; align-items: flex-end; }

/* MÜŞTERİ – büyük başlık, label gizli */
.rc-og-table tbody td.rc-og-td-musteri {
   font-size: 16px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.3;
}
.rc-og-table tbody td.rc-og-td-musteri::before { display: none; }

/* DURUM – sağ üstte rozetle */
.rc-og-table tbody td.rc-og-td-durum::before { display: none; }

/* İŞLEMLER – sağ altta ikon button */
.rc-og-table tbody td.rc-og-td-islemler::before { display: none; }

/* OLUŞTURMA – sol altta küçük tarih ikonuyla */
.rc-og-table tbody td.rc-og-td-olusturma {
   flex-direction: row !important;
   align-items: center !important;
   gap: 6px !important;
   font-size: 12px;
   color: var(--rc-text-soft);
   padding-top: 6px !important;
   border-top: 1px solid var(--rc-border) !important;
   margin-top: 2px;
   width: 100% !important;
}
.rc-og-table tbody td.rc-og-td-olusturma::before {
   content: "\f073"; /* fa-calendar */
   font-family: "FontAwesome";
   font-size: 12px;
   color: var(--rc-purple);
   text-transform: none;
   letter-spacing: 0;
   font-weight: 400;
   margin-right: 2px;
}
.rc-og-table tbody td.rc-og-td-islemler {
   padding-top: 6px !important;
   border-top: 1px solid var(--rc-border) !important;
   margin-top: 2px;
   align-self: stretch !important;
   width: 100% !important;
   align-items: flex-end !important;
}

/* RESPONSIVE PLUGIN'I DEVRE DIŞI */
#on_gorusme_liste td.dtr-control,
#on_gorusme_liste th.dtr-control { display: none !important; }
#on_gorusme_liste tr.child { display: none !important; }
#on_gorusme_liste td.dtr-hidden,
#on_gorusme_liste th.dtr-hidden,
#on_gorusme_liste td.none,
#on_gorusme_liste th.none { display: flex !important; }

/* === DURUM BADGE OVERRIDE === */
.rc-og-table tbody td .btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border: 1px solid #fde68a !important;
   border-radius: 999px !important;
   padding: 5px 14px !important;
   font-size: 11.5px !important;
   font-weight: 700 !important;
   line-height: 1.4 !important;
   display: inline-block !important;
   width: auto !important;
   min-width: 0;
   text-align: center;
   box-shadow: none !important;
   pointer-events: none;
   text-transform: uppercase;
   letter-spacing: .03em;
}
.rc-og-table tbody td .btn.btn-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border: 1px solid #bbf7d0 !important;
   border-radius: 999px !important;
   padding: 5px 14px !important;
   font-size: 11.5px !important;
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
.rc-og-table tbody td .btn.btn-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
   border: 1px solid #fecaca !important;
   border-radius: 999px !important;
   padding: 5px 14px !important;
   font-size: 11.5px !important;
   font-weight: 700 !important;
   line-height: 1.4 !important;
   display: inline-block !important;
   width: auto !important;
   min-width: 0;
   text-align: center;
   box-shadow: none !important;
   cursor: pointer;
   text-transform: uppercase;
   letter-spacing: .03em;
}
.rc-og-table tbody td .btn.btn-danger:hover { filter: brightness(.95); }

/* === İŞLEMLER DROPDOWN === */
.rc-og-table tbody td .dropdown .dropdown-toggle.btn-link {
   width: 38px;
   height: 38px;
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
   padding: 16px 8px 8px;
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
   padding: 16px 8px 0 !important;
}

/* === BOŞ DURUM === */
.rc-og-table tbody tr.dataTables_empty {
   grid-column: 1 / -1;
   display: flex !important;
   align-items: center;
   justify-content: center;
   padding: 60px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   background: #fff !important;
   border: 2px dashed var(--rc-border) !important;
   border-radius: 16px !important;
}
.rc-og-table tbody tr.dataTables_empty td {
   border: none !important;
   width: 100%;
   text-align: center;
   align-items: center;
}
.rc-og-table tbody tr.dataTables_empty td::before { display: none; }
.rc-og-table tbody tr.dataTables_empty:hover {
   transform: none;
   box-shadow: none !important;
}

/* === RESPONSIVE === */

/* Tablet: 2 kart yan yana */
@media (max-width: 1200px) {
   .rc-og-table tbody {
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 14px;
   }
}

/* Tablet medium */
@media (max-width: 1024px) {
   .rc-og-header { padding: 14px 16px; }
   .rc-og-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-og-title { font-size: 17px; }
   .rc-og-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-og-card .dataTables_filter input { width: 200px; }
}

/* Mobil: tek kart */
@media (max-width: 768px) {
   .rc-og-header { padding: 12px 14px; border-radius: 12px; }
   .rc-og-header-left { width: 100%; }
   .rc-og-header-right { width: 100%; }
   .rc-og-header-right .rc-og-btn { flex: 1; justify-content: center; }
   .rc-og-title { font-size: 16px; }
   .rc-og-breadcrumb { font-size: 11.5px; }

   .rc-og-card .dataTables_filter { float: none; text-align: left; }
   .rc-og-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-og-card .dataTables_filter label { display: block; width: 100%; }
   .rc-og-card .dataTables_length { display: none; }

   .rc-og-table tbody {
      grid-template-columns: 1fr;
      gap: 12px;
   }
   .rc-og-table tbody tr {
      padding: 16px !important;
   }
}

@media (max-width: 420px) {
   .rc-og-header-right { flex-direction: column; }
   .rc-og-header-right .rc-og-btn { width: 100%; }
   .rc-og-title-row { gap: 10px; }
   .rc-og-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-og-table tbody tr {
      grid-template-columns: 1fr;
      grid-template-areas:
         "musteri"
         "durum"
         "tipi"
         "randevu"
         "telefon"
         "yapan"
         "neden"
         "olusturma"
         "islemler" !important;
   }
   .rc-og-table tbody td.rc-og-td-durum,
   .rc-og-table tbody td.rc-og-td-islemler {
      justify-self: start;
      align-items: flex-start;
   }
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

/* Her hucreye data-label + sutun adi sinifi ekle (kart icin) */
(function(){
   var labels = [
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
      'rc-og-td-olusturma',
      'rc-og-td-musteri',
      'rc-og-td-tipi',
      'rc-og-td-telefon',
      'rc-og-td-randevu',
      'rc-og-td-neden',
      'rc-og-td-yapan',
      'rc-og-td-durum',
      'rc-og-td-actions rc-og-td-islemler'
   ];
   function applyLabels(){
      $('#on_gorusme_liste tbody tr').not('.dataTables_empty').each(function(){
         $(this).find('> td').each(function(idx){
            var label = labels[idx] || '';
            var cls = tdClasses[idx] || '';
            $(this).attr('data-label', label);
            if(cls){
               cls.split(' ').forEach(function(c){
                  if(c && !$(this).hasClass(c)) $(this).addClass(c);
               }.bind(this));
            }
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
