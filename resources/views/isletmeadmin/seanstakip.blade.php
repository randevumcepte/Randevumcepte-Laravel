@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-st-page">

   {{-- Modern Page Header --}}
   <div class="rc-st-header">
      <div class="rc-st-header-left">
         <div class="rc-st-title-row">
            <div class="rc-st-icon-bubble"><i class="fa fa-calendar-check-o"></i></div>
            <div>
               <h1 class="rc-st-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-st-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-st-sep">›</span>
                  <span class="rc-st-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
   </div>

   {{-- Renk Açıklama Notu --}}
   <div class="rc-st-note">
      <i class="fa fa-info-circle"></i>
      <span><b>Seans Detayı</b> sütunundaki sayaçlar:</span>
      <span class="rc-st-legend rc-st-legend-total"><i class="fa fa-plus"></i> Toplam</span>
      <span class="rc-st-legend rc-st-legend-wait"><i class="fa fa-calendar"></i> Beklemede</span>
      <span class="rc-st-legend rc-st-legend-came"><i class="fa fa-check"></i> Geldi</span>
      <span class="rc-st-legend rc-st-legend-miss"><i class="fa fa-times"></i> Gelmedi</span>
   </div>

   {{-- Modern Tablo Kartı --}}
   <div class="rc-st-card">
      <div class="rc-st-tablo-wrap">
         <table class="data-table table stripe hover nowrap rc-st-table" id="seans_takip_liste" style="width:100%">
            <thead>
               <tr>
                  {{-- id kolonu seansTakibi.js'te visible:false; DataTables DOM'dan kaldirir.
                       Yine de kolon sayisi (6) eslesmesi icin th burada bulunmali. --}}
                  <th scope="col" class="rc-st-col-id" data-label="">ID</th>
                  <th scope="col">Müşteri</th>
                  <th scope="col">Seans Başlangıcı</th>
                  <th scope="col">Paket Adı</th>
                  <th scope="col">Seans Detayı</th>
                  <th class="datatable-nosort rc-st-col-actions" data-label="İşlemler"></th>
               </tr>
            </thead>
            <tbody></tbody>
         </table>
      </div>
   </div>

</div>
<div id="hata"></div>

<style>
/* =================================================================
   SEANS TAKİBİ — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */
.rc-st-page {
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
.rc-st-header {
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
.rc-st-header-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.rc-st-title-row { display: flex; align-items: center; gap: 14px; }
.rc-st-icon-bubble {
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
.rc-st-title { margin: 0; font-size: 19px; font-weight: 700; color: var(--rc-text); line-height: 1.2; }
.rc-st-breadcrumb {
   margin-top: 4px; font-size: 12.5px; color: var(--rc-text-soft);
   display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.rc-st-breadcrumb a { color: var(--rc-text-soft); text-decoration: none; transition: color .15s; }
.rc-st-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-st-breadcrumb .rc-st-sep { color: #cbd5e1; }
.rc-st-breadcrumb .rc-st-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === AÇIKLAMA NOTU + LEJANT === */
.rc-st-note {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
   padding: 13px 18px;
   margin-bottom: 18px;
   background: #fff;
   border: 1px solid var(--rc-border);
   border-radius: 12px;
   font-size: 13px;
   color: var(--rc-text);
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
}
.rc-st-note > i { color: var(--rc-purple-dark); font-size: 16px; }
.rc-st-note b { color: var(--rc-purple-dark); }
.rc-st-legend {
   display: inline-flex;
   align-items: center;
   gap: 6px;
   padding: 4px 12px;
   border-radius: 999px;
   font-size: 11.5px;
   font-weight: 700;
   color: #fff;
}
.rc-st-legend i { font-size: 11px; }
.rc-st-legend-total { background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%); }
.rc-st-legend-wait  { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
.rc-st-legend-came  { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); }
.rc-st-legend-miss  { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); }

/* === TABLO KARTI === */
.rc-st-card {
   background: #fff;
   border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 6px 24px rgba(92, 0, 142, .05);
   padding: 4px;
   margin-bottom: 30px;
   border: 1px solid var(--rc-border);
   overflow: hidden;
}
.rc-st-tablo-wrap { width: 100%; border-radius: 12px; overflow-x: auto; }

/* === DATATABLES WRAPPER === */
.rc-st-card .dataTables_wrapper { padding: 14px 16px 8px; }
.rc-st-card .dataTables_length,
.rc-st-card .dataTables_filter { margin-bottom: 14px; }
.rc-st-card .dataTables_length label,
.rc-st-card .dataTables_filter label { color: var(--rc-text-soft); font-size: 13px; font-weight: 500; }
.rc-st-card .dataTables_filter input {
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
.rc-st-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-st-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
   background: #fff !important;
}

/* === DataTables responsive plugin'ini devre disi === */
.rc-st-table td.dtr-control,
.rc-st-table th.dtr-control { display: none !important; }
.rc-st-table tr.child { display: none !important; }
.rc-st-table td.dtr-hidden,
.rc-st-table th.dtr-hidden,
.rc-st-table td.none,
.rc-st-table th.none { display: table-cell !important; }

/* === MODERN VERİ TABLOSU === */
.rc-st-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
   width: 100% !important;
   min-width: 720px;
}
.rc-st-table thead { display: table-header-group !important; }
.rc-st-table thead th {
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
.rc-st-table thead th:first-child { border-top-left-radius: 12px; }
.rc-st-table thead th:last-child  { border-top-right-radius: 12px; }
.rc-st-table thead th.rc-st-col-actions { text-align: right; width: 70px; }

/* Sıralama okları */
.rc-st-table thead th.sorting,
.rc-st-table thead th.sorting_asc,
.rc-st-table thead th.sorting_desc { cursor: pointer; position: relative; padding-right: 26px; }
.rc-st-table thead th.sorting::after,
.rc-st-table thead th.sorting_asc::after,
.rc-st-table thead th.sorting_desc::after {
   position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
   font-family: "FontAwesome"; font-size: 11px; opacity: .35;
}
.rc-st-table thead th.sorting::after     { content: "\f0dc"; }
.rc-st-table thead th.sorting_asc::after { content: "\f0de"; opacity: .9; }
.rc-st-table thead th.sorting_desc::after{ content: "\f0dd"; opacity: .9; }

/* Gövde */
.rc-st-table tbody tr { background: #fff; transition: background .12s ease; }
.rc-st-table.stripe tbody tr.even,
.rc-st-table tbody tr.even { background: #fcfbfe; }
.rc-st-table tbody tr:hover,
.rc-st-table.stripe tbody tr.odd:hover,
.rc-st-table.stripe tbody tr.even:hover { background: #f7f1fd; }
.rc-st-table tbody td {
   padding: 13px 16px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   line-height: 1.5;
   white-space: nowrap;
}
.rc-st-table tbody tr:last-child td { border-bottom: none !important; }

/* Müşteri = ilk hücre, satır başlığı */
.rc-st-table tbody td:first-child {
   font-size: 14px; font-weight: 700; color: var(--rc-text);
   letter-spacing: -.1px; white-space: normal;
}
/* Paket Adı (3. hücre) */
.rc-st-table tbody td:nth-child(3) { color: var(--rc-text); font-weight: 600; }
/* İşlemler = son hücre */
.rc-st-table tbody td:last-child { text-align: right; width: 70px; white-space: nowrap; }

/* === SEANS DETAYI sayaç butonları (4. sütun) — tıklanabilir renkli pill'ler === */
.rc-st-table tbody td:nth-child(4) { white-space: normal; }
.rc-st-table tbody td:nth-child(4) .btn {
   width: auto !important;
   min-width: 48px;
   font-size: 11px !important;
   font-weight: 700 !important;
   padding: 6px 11px !important;
   margin: 2px !important;
   border-radius: 999px !important;
   border: none !important;
   color: #fff !important;
   line-height: 1.4 !important;
   display: inline-flex !important;
   align-items: center;
   justify-content: center;
   gap: 4px;
   transition: transform .12s, filter .12s, box-shadow .12s;
}
.rc-st-table tbody td:nth-child(4) .btn:hover { transform: translateY(-1px); filter: brightness(1.06); }
.rc-st-table tbody td:nth-child(4) .btn.btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   box-shadow: 0 2px 6px rgba(92, 0, 142, .25) !important;
}
.rc-st-table tbody td:nth-child(4) .btn.btn-warning {
   background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
   box-shadow: 0 2px 6px rgba(245, 158, 11, .25) !important;
}
.rc-st-table tbody td:nth-child(4) .btn.btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%) !important;
   box-shadow: 0 2px 6px rgba(22, 163, 74, .25) !important;
}
.rc-st-table tbody td:nth-child(4) .btn.btn-danger {
   background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
   box-shadow: 0 2px 6px rgba(220, 38, 38, .25) !important;
}

/* === İŞLEMLER (göz) butonu — daire mor === */
.rc-st-table tbody td:last-child .btn {
   width: 38px; height: 38px;
   border-radius: 50% !important;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border: none !important;
   display: inline-flex !important;
   align-items: center !important;
   justify-content: center !important;
   padding: 0 !important;
   font-size: 15px !important;
   box-shadow: 0 4px 12px rgba(92, 0, 142, .22);
   transition: transform .15s, filter .15s;
}
.rc-st-table tbody td:last-child .btn:hover { transform: scale(1.08); filter: brightness(1.06); }

/* Pagination */
.rc-st-card .dataTables_paginate { padding: 14px; }
.rc-st-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-st-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-st-card .dataTables_paginate .paginate_button.current,
.rc-st-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-st-card .dataTables_info { color: var(--rc-text-soft); font-size: 12.5px; padding: 14px !important; }
.rc-st-table tbody td.dataTables_empty {
   text-align: center !important;
   padding: 50px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   background: #fff !important;
   white-space: normal;
}

/* === RESPONSIVE: TABLET LANDSCAPE (≤1024px) === */
@media (max-width: 1024px) {
   .rc-st-header { padding: 14px 16px; }
   .rc-st-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-st-title { font-size: 17px; }
   .rc-st-card .dataTables_wrapper { padding: 12px 10px 6px; }
   .rc-st-card .dataTables_filter input { width: 200px; }
   .rc-st-table tbody td { padding: 12px 14px !important; }
}

/* === RESPONSIVE: TABLET PORTRAIT + MOBILE (≤900px) — blok yığın === */
@media (max-width: 900px) {
   .rc-st-header { padding: 12px 14px; border-radius: 12px; }
   .rc-st-title { font-size: 16px; }
   .rc-st-breadcrumb { font-size: 11.5px; }
   .rc-st-note { padding: 12px 14px; }

   .rc-st-card { padding: 8px; border-radius: 14px; }
   .rc-st-card .dataTables_wrapper { padding: 10px 8px 4px; }
   .rc-st-card .dataTables_filter { float: none; text-align: left; }
   .rc-st-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-st-card .dataTables_filter label { display: block; width: 100%; }
   .rc-st-card .dataTables_length { display: none; }

   /* Tabloyu blok-yığın yap */
   .rc-st-tablo-wrap { overflow-x: visible; }
   .rc-st-table { min-width: 0 !important; display: block !important; }
   .rc-st-table thead { display: none !important; }
   .rc-st-table tbody { display: block !important; }
   .rc-st-table tbody tr {
      display: block !important;
      background: #fff !important;
      border: 1px solid var(--rc-border) !important;
      border-radius: 14px !important;
      box-shadow: 0 2px 8px rgba(92, 0, 142, .05);
      padding: 6px 4px;
      margin-bottom: 12px;
   }
   .rc-st-table tbody tr:last-child { margin-bottom: 0; }
   .rc-st-table tbody td {
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
   .rc-st-table tbody tr td:last-child { border-bottom: none !important; justify-content: flex-end; }
   .rc-st-table tbody td::before {
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
   /* Müşteri = başlık satırı */
   .rc-st-table tbody td:first-child {
      font-size: 15px !important;
      font-weight: 700;
      background: #faf7fe;
      border-radius: 10px;
      margin: 0 4px 6px;
   }
   .rc-st-table tbody td:first-child::before { content: none; }
   /* Seans Detayı: butonlar sağda sarılı */
   .rc-st-table tbody td:nth-child(4) { flex-wrap: wrap; }
   .rc-st-table tbody td.dataTables_empty { justify-content: center; }
   .rc-st-table tbody td.dataTables_empty::before { content: none; }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-st-title-row { gap: 10px; }
   .rc-st-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-st-table tbody td { padding: 8px 12px !important; font-size: 12.5px; }
   .rc-st-table tbody td:first-child { font-size: 14.5px !important; }
   .rc-st-table tbody td:nth-child(4) { justify-content: flex-end; }
}
</style>

<script>
/* DataTables responsive plugin'in eklediği sınıfları geri al + mobil yığın için
   her hücreye sütun başlığına göre data-label ekle.
   Tablo custom.js'teki seanslariGetir() ile kurulur/yenilenir; init.dt & draw.dt yakalanır. */
(function(){
   var SEL = '#seans_takip_liste';
   function applyLabels(){
      var $t = $(SEL);
      if(!$t.length) return;
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
   $(document).on('init.dt', SEL, function(){
      var $t = $(SEL);
      $t.removeClass('dtr-inline collapsed');
      setTimeout(function(){
         $t.find('td.dtr-control, th.dtr-control').remove();
         $t.find('td, th').removeClass('dtr-hidden none');
         $t.find('tr.child').remove();
         applyLabels();
      }, 40);
   });
   $(document).on('draw.dt', SEL, function(){ setTimeout(applyLabels, 60); });
   $(document).ready(function(){ setTimeout(applyLabels, 250); });
})();
</script>

@endsection()
