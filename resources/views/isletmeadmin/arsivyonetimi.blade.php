@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="rc-arsiv-page">

   {{-- Modern Page Header --}}
   <div class="rc-arsiv-header">
      <div class="rc-arsiv-header-left">
         <div class="rc-arsiv-title-row">
            <div class="rc-arsiv-icon-bubble"><i class="fa fa-folder-open"></i></div>
            <div>
               <h1 class="rc-arsiv-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-arsiv-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-arsiv-sep">›</span>
                  <span class="rc-arsiv-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-arsiv-header-right">
         @yetki('form.gonder')
         <a href="#" data-toggle="modal" type="button" data-target="#formugondermodal"
            class="rc-arsiv-btn rc-arsiv-btn-success yenieklebuton501">
            <i class="fa fa-paper-plane"></i><span>Form Gönder</span>
         </a>
         @endyetki
         @yetki('form.olustur')
         <a href="#" data-toggle="modal" type="button" data-target="#sozlesmeOlusturModal"
            class="rc-arsiv-btn rc-arsiv-btn-info">
            <i class="fa fa-file-text"></i><span>Sözleşme Oluştur</span>
         </a>
         <a href="#" data-toggle="modal" type="button" data-target="#haricibelgeeklemodal"
            class="rc-arsiv-btn rc-arsiv-btn-primary yenieklebuton502">
            <i class="fa fa-plus"></i><span>Belge Ekle</span>
         </a>
         @endyetki
      </div>
   </div>

   {{-- Modern Tab Card --}}
   <div class="rc-arsiv-card">

      {{-- Tabs --}}
      <div class="rc-arsiv-tabs-wrap">
         <ul class="nav nav-tabs rc-arsiv-tabs" role="tablist">
            <li class="nav-item">
               <button class="rc-arsiv-tab active" data-toggle="tab" href="#tum_arsiv" role="tab" aria-selected="true">
                  <i class="fa fa-th-large"></i><span>Tümü</span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-arsiv-tab" data-toggle="tab" href="#onayli_arsiv" role="tab" aria-selected="false">
                  <i class="fa fa-check-circle"></i><span>Onaylananlar</span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-arsiv-tab" data-toggle="tab" href="#beklenen_arsiv" role="tab" aria-selected="false">
                  <i class="fa fa-clock-o"></i><span>Beklenenler</span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-arsiv-tab" data-toggle="tab" href="#iptal_arsiv" role="tab" aria-selected="false">
                  <i class="fa fa-times-circle"></i><span>İptal Edilenler</span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-arsiv-tab" data-toggle="tab" href="#harici_arsiv" role="tab" aria-selected="false">
                  <i class="fa fa-file"></i><span>Harici Belgeler</span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-arsiv-tab" data-toggle="tab" href="#form_sablonlari_tab" role="tab"
                  aria-selected="false" onclick="formSablonlariTabAc()">
                  <i class="fa fa-clone"></i><span>Form Şablonları</span>
               </button>
            </li>
         </ul>
      </div>

      {{-- Tab Panes --}}
      <div class="tab-content rc-arsiv-tab-content">
         <div class="tab-pane fade show active" id="tum_arsiv" role="tab-panel">
            <div class="rc-arsiv-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-arsiv-table" id="arsiv_liste" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Başlık</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>Belge Durumu</th>
                        <th>Durum</th>
                        <th class="rc-arsiv-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         <div class="tab-pane fade show" id="onayli_arsiv" role="tab-panel">
            <div class="rc-arsiv-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-arsiv-table" id="arsiv_liste_onayli" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Başlık</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>Belge Durumu</th>
                        <th>Durum</th>
                        <th class="rc-arsiv-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         <div class="tab-pane fade show" id="beklenen_arsiv" role="tab-panel">
            <div class="rc-arsiv-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-arsiv-table" id="arsiv_liste_beklenen" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Başlık</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>Belge Durumu</th>
                        <th>Durum</th>
                        <th class="rc-arsiv-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         <div class="tab-pane fade show" id="iptal_arsiv" role="tab-panel">
            <div class="rc-arsiv-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-arsiv-table" id="arsiv_liste_iptal" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Başlık</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>Belge Durumu</th>
                        <th>Durum</th>
                        <th class="rc-arsiv-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         <div class="tab-pane fade show" id="harici_arsiv" role="tab-panel">
            <div class="rc-arsiv-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-arsiv-table" id="arsiv_liste_harici" style="width:100%">
                  <thead>
                     <tr>
                        <th>Müşteri</th>
                        <th>Başlık</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>Belge Durumu</th>
                        <th>Durum</th>
                        <th class="rc-arsiv-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         <div class="tab-pane fade show" id="form_sablonlari_tab" role="tab-panel">
            <iframe id="form_sablonlari_iframe" src="about:blank"
               style="width:100%; height:80vh; border:1px solid #eef0f4; border-radius:10px;"></iframe>
         </div>
      </div>
   </div>
</div>

<script>
function formSablonlariTabAc(){
   var ifr = document.getElementById('form_sablonlari_iframe');
   if(ifr && (ifr.src === 'about:blank' || !ifr.src.includes('form-sablonlari'))){
      ifr.src = '/isletmeyonetim/form-sablonlari?sube={{$isletme->id}}&embed=1';
   }
}
</script>

{{-- Harici Belge Ekle Modal --}}
<div id="haricibelgeeklemodal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
      <form id="haricibelgeekleform">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <div class="modal-content" style="min-height: 200px;">
            <div class="modal-header">
               <h4 class="h4">Harici Belge Ekle</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
               <div class="row">
                  <div class="col-md-4 col-xs-3 col-sm-3 col-3 form-group">
                     <label>Form Başlığı</label>
                     <input class="form-control" type="text" name="haricibelgeformbaslik" id="haricibelgeformbaslik">
                  </div>
                  <div class="col-md-4 col-sm-6 col-xs-6 col-6 form-group">
                     <label>Müşteri</label>
                     <select name="haricibelgemusteri" id="haricibelgemusteri" class="form-control opsiyonelSelect musteri_secimi" style="width: 100%;">
                        <option></option>
                     </select>
                  </div>
                  <div class="col-md-4 col-sm-6 col-xs-6 col-6 form-group">
                     <label>İşlemi Yapan Personel</label>
                     <select name="haricibelgepersonel" id="haricibelgepersonel" class="form-control opsiyonelSelect personel_secimi" style="width: 100%;">
                        <option></option>
                     </select>
                  </div>
                  <div class="col-md-12 col-xs-6 col-sm-6 col-6 form-group">
                     <label>Formu Yükle</label>
                     <input type="file" name="hariciformyukle" required id="hariciformyukle" class="form-control-file form-control ">
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
               <div class="col-md-6 col-xs-6 col-6 col-sm-6">
                  <button type="submit" class="btn btn-success btn-block">Kaydet</button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>

<div id="hata"></div>
<div id='yazdirilacak' style="display:none"></div>

<style>
/* =================================================================
   ARŞİV YÖNETİMİ — MODERN RESPONSIVE
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */

.rc-arsiv-page {
   --rc-purple-dark: #5C008E;
   --rc-purple: #9D5DC8;
   --rc-purple-light: #f5eefe;
   --rc-purple-soft: #ead4ff;
   --rc-pink: #d946ef;
   --rc-info: #0ea5e9;
   --rc-info-soft: #e0f2fe;
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
.rc-arsiv-header {
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
.rc-arsiv-header-left,
.rc-arsiv-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
.rc-arsiv-title-row {
   display: flex;
   align-items: center;
   gap: 14px;
}
.rc-arsiv-icon-bubble {
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
.rc-arsiv-title {
   margin: 0;
   font-size: 19px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.2;
}
.rc-arsiv-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex;
   align-items: center;
   gap: 6px;
   flex-wrap: wrap;
}
.rc-arsiv-breadcrumb a {
   color: var(--rc-text-soft);
   text-decoration: none;
   transition: color .15s;
}
.rc-arsiv-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-arsiv-breadcrumb .rc-arsiv-sep { color: #cbd5e1; }
.rc-arsiv-breadcrumb .rc-arsiv-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === HEADER BUTONLAR === */
.rc-arsiv-btn {
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
.rc-arsiv-btn i { font-size: 14px; }
.rc-arsiv-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-arsiv-btn:active { transform: translateY(0); }
.rc-arsiv-btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
   box-shadow: 0 6px 16px rgba(22, 163, 74, .28);
}
.rc-arsiv-btn-info {
   background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
   box-shadow: 0 6px 16px rgba(14, 165, 233, .28);
}
.rc-arsiv-btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
}

/* === KART === */
.rc-arsiv-card {
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
   padding: 8px;
   margin-bottom: 30px;
}

/* === MODERN TAB BAR === */
.rc-arsiv-tabs-wrap {
   padding: 14px 14px 0;
   border-bottom: 1px solid var(--rc-border);
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
   scrollbar-width: thin;
}
.rc-arsiv-tabs-wrap::-webkit-scrollbar { height: 4px; }
.rc-arsiv-tabs-wrap::-webkit-scrollbar-thumb {
   background: var(--rc-purple-soft);
   border-radius: 4px;
}
.rc-arsiv-tabs {
   display: inline-flex !important;
   flex-wrap: nowrap;
   gap: 6px;
   border: none !important;
   margin: 0 !important;
   padding: 0 !important;
   list-style: none;
   min-width: max-content;
}
.rc-arsiv-tabs .nav-item { margin: 0; }
.rc-arsiv-tab {
   display: inline-flex;
   align-items: center;
   gap: 8px;
   height: 40px;
   padding: 0 16px;
   border: none;
   background: transparent;
   color: var(--rc-text-soft);
   font-size: 13.5px;
   font-weight: 600;
   border-radius: 10px 10px 0 0;
   cursor: pointer;
   position: relative;
   transition: color .15s, background .15s;
   white-space: nowrap;
}
.rc-arsiv-tab i { font-size: 14px; opacity: .85; }
.rc-arsiv-tab:hover {
   color: var(--rc-purple-dark);
   background: var(--rc-purple-light);
}
.rc-arsiv-tab.active,
.rc-arsiv-tab[aria-selected="true"] {
   color: var(--rc-purple-dark);
   background: var(--rc-purple-light);
}
.rc-arsiv-tab.active::after,
.rc-arsiv-tab[aria-selected="true"]::after {
   content: '';
   position: absolute;
   left: 12px; right: 12px;
   bottom: -1px;
   height: 3px;
   border-radius: 3px 3px 0 0;
   background: linear-gradient(90deg, var(--rc-purple-dark), var(--rc-purple), var(--rc-pink));
}

.rc-arsiv-tab-content { padding: 14px; }

/* === TABLO WRAPPER === */
.rc-arsiv-tablo-wrap {
   width: 100%;
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
   border-radius: 10px;
}
.rc-arsiv-tablo-wrap .rc-arsiv-table { min-width: 900px; }

/* === DATATABLES WRAPPER === */
.rc-arsiv-card .dataTables_wrapper { padding: 6px 4px 8px; }
.rc-arsiv-card .dataTables_length,
.rc-arsiv-card .dataTables_filter { margin-bottom: 14px; }
.rc-arsiv-card .dataTables_length label,
.rc-arsiv-card .dataTables_filter label {
   color: var(--rc-text-soft);
   font-size: 13px;
   font-weight: 500;
}
.rc-arsiv-card .dataTables_filter input {
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
.rc-arsiv-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-arsiv-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
}

/* === TABLO === */
.rc-arsiv-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
}
.rc-arsiv-table thead th {
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
.rc-arsiv-table thead th:first-child { border-top-left-radius: 10px; }
.rc-arsiv-table thead th:last-child { border-top-right-radius: 10px; }
.rc-arsiv-table tbody td {
   padding: 14px 14px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   background: #fff;
}
.rc-arsiv-table tbody tr { transition: background-color .15s; }
.rc-arsiv-table tbody tr:hover td { background: var(--rc-purple-light) !important; }
.rc-arsiv-table tbody tr:last-child td { border-bottom: none !important; }
.rc-arsiv-table.stripe tbody tr.odd td { background: #fbfaff; }
.rc-arsiv-table.stripe tbody tr.odd:hover td { background: var(--rc-purple-light) !important; }

/* === RESPONSIVE PLUGIN'I DEVRE DIŞI === */
.rc-arsiv-table thead th,
.rc-arsiv-table tbody td { display: table-cell !important; }
.rc-arsiv-table td.dtr-control,
.rc-arsiv-table th.dtr-control { display: none !important; }
.rc-arsiv-table tr.child { display: none !important; }
.rc-arsiv-table td.dtr-hidden,
.rc-arsiv-table th.dtr-hidden,
.rc-arsiv-table td.none,
.rc-arsiv-table th.none { display: table-cell !important; }

/* === DURUM BADGE OVERRIDE === */
.rc-arsiv-table tbody td .btn.btn-warning,
.rc-arsiv-table tbody td .btn.btn-success,
.rc-arsiv-table tbody td .btn.btn-danger,
.rc-arsiv-table tbody td .btn.btn-info {
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
.rc-arsiv-table tbody td .btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border: 1px solid #fde68a !important;
}
.rc-arsiv-table tbody td .btn.btn-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border: 1px solid #bbf7d0 !important;
}
.rc-arsiv-table tbody td .btn.btn-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
   border: 1px solid #fecaca !important;
}
.rc-arsiv-table tbody td .btn.btn-info {
   background: var(--rc-info-soft) !important;
   color: #0369a1 !important;
   border: 1px solid #bae6fd !important;
}

/* === İŞLEMLER DROPDOWN === */
.rc-arsiv-table tbody td .dropdown .dropdown-toggle.btn-link {
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
.rc-arsiv-table tbody td .dropdown .dropdown-toggle.btn-link:hover {
   background: var(--rc-purple-soft) !important;
   transform: scale(1.05);
}
.rc-arsiv-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 200px !important;
}
.rc-arsiv-table tbody td .dropdown-menu .dropdown-item {
   padding: 9px 12px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
}
.rc-arsiv-table tbody td .dropdown-menu .dropdown-item:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-arsiv-table tbody td .dropdown-menu .dropdown-item i {
   width: 18px;
   margin-right: 6px;
   color: var(--rc-purple);
}

/* === PAGINATION === */
.rc-arsiv-card .dataTables_paginate { padding: 14px; }
.rc-arsiv-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-arsiv-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-arsiv-card .dataTables_paginate .paginate_button.current,
.rc-arsiv-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-arsiv-card .dataTables_info {
   color: var(--rc-text-soft);
   font-size: 12.5px;
   padding: 14px !important;
}

/* === SORT === */
.rc-arsiv-table thead th[class*="sorting"] { position: relative; cursor: pointer; }
.rc-arsiv-table thead th.sorting_asc,
.rc-arsiv-table thead th.sorting_desc { color: var(--rc-purple-dark) !important; }

/* === RESPONSIVE: TABLET (≤1024px) === */
@media (max-width: 1024px) {
   .rc-arsiv-header { padding: 14px 16px; }
   .rc-arsiv-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-arsiv-title { font-size: 17px; }
   .rc-arsiv-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-arsiv-card { padding: 6px; }
   .rc-arsiv-tabs-wrap { padding: 10px 10px 0; }
   .rc-arsiv-tab { height: 36px; font-size: 12.5px; padding: 0 12px; }
   .rc-arsiv-tab-content { padding: 10px; }
   .rc-arsiv-card .dataTables_filter input { width: 180px; }
   .rc-arsiv-table thead th,
   .rc-arsiv-table tbody td { padding: 12px 10px !important; font-size: 13px; }
}

/* === RESPONSIVE: MOBILE (≤768px) — TABLE → CARD === */
@media (max-width: 768px) {
   .rc-arsiv-header {
      padding: 12px 14px;
      border-radius: 12px;
   }
   .rc-arsiv-header-left { width: 100%; }
   .rc-arsiv-header-right { width: 100%; }
   .rc-arsiv-header-right .rc-arsiv-btn { flex: 1; justify-content: center; min-width: 0; }
   .rc-arsiv-header-right .rc-arsiv-btn span { display: inline; }
   .rc-arsiv-title { font-size: 16px; }
   .rc-arsiv-breadcrumb { font-size: 11.5px; }

   .rc-arsiv-card { padding: 4px; border-radius: 12px; }
   .rc-arsiv-tab-content { padding: 8px; }
   .rc-arsiv-tab { padding: 0 10px; }
   .rc-arsiv-tab span { font-size: 12px; }

   .rc-arsiv-card .dataTables_filter { float: none; text-align: left; }
   .rc-arsiv-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-arsiv-card .dataTables_filter label { display: block; width: 100%; }
   .rc-arsiv-card .dataTables_length { display: none; }

   /* TABLO -> KART GORUNUMU */
   .rc-arsiv-tablo-wrap { overflow: visible; }
   .rc-arsiv-tablo-wrap .rc-arsiv-table { min-width: 0; width: 100% !important; }

   .rc-arsiv-table,
   .rc-arsiv-table thead,
   .rc-arsiv-table tbody,
   .rc-arsiv-table tr,
   .rc-arsiv-table td,
   .rc-arsiv-table th {
      display: block !important;
      width: 100% !important;
   }
   .rc-arsiv-table thead { display: none !important; }

   .rc-arsiv-table tbody tr {
      background: #fff;
      border: 1px solid var(--rc-border);
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 12px;
      box-shadow: 0 1px 2px rgba(17, 24, 39, .03);
      position: relative;
   }
   .rc-arsiv-table tbody tr:hover td,
   .rc-arsiv-table tbody tr.odd td,
   .rc-arsiv-table tbody td {
      background: transparent !important;
      border: none !important;
      padding: 6px 0 !important;
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      font-size: 13px;
   }
   .rc-arsiv-table tbody td::before {
      content: attr(data-label);
      font-size: 11px;
      font-weight: 700;
      color: var(--rc-text-soft);
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
      min-width: 110px;
   }
   .rc-arsiv-table tbody td.rc-arsiv-td-actions,
   .rc-arsiv-table tbody td.rc-arsiv-td-durum,
   .rc-arsiv-table tbody td.rc-arsiv-td-belge {
      justify-content: flex-end;
   }
   .rc-arsiv-table tbody td .btn.btn-warning,
   .rc-arsiv-table tbody td .btn.btn-success,
   .rc-arsiv-table tbody td .btn.btn-danger,
   .rc-arsiv-table tbody td .btn.btn-info {
      min-width: 0;
   }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤480px) === */
@media (max-width: 480px) {
   .rc-arsiv-header-right { flex-direction: column; gap: 8px; }
   .rc-arsiv-header-right .rc-arsiv-btn { width: 100%; flex: none; }
   .rc-arsiv-title-row { gap: 10px; }
   .rc-arsiv-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-arsiv-tab span { display: none; }
   .rc-arsiv-tab { padding: 0 12px; }
   .rc-arsiv-tab i { font-size: 16px; }
}
</style>

<script>
/* Responsive plugin'in dinamik attigi siniflari geri al */
(function(){
   var TABLES = ['#arsiv_liste','#arsiv_liste_onayli','#arsiv_liste_beklenen','#arsiv_liste_iptal','#arsiv_liste_harici'];
   var SELECTOR = TABLES.join(',');

   $(document).on('init.dt', SELECTOR, function() {
      var $t = $(this);
      $t.removeClass('dtr-inline collapsed');
      setTimeout(function() {
         $t.find('td, th').removeClass('dtr-hidden none');
         $t.find('tr.child').remove();
         $t.find('td.dtr-control, th.dtr-control').remove();
      }, 50);
   });

   /* Mobil kart gorunumu icin her hucreye sutun adi etiketi ekle. */
   var labels = ['Müşteri','Başlık','Oluşturulma Tarihi','Belge Durumu','Durum','İşlemler'];
   var tdClasses = [
      'rc-arsiv-td-musteri',
      'rc-arsiv-td-baslik',
      'rc-arsiv-td-tarih',
      'rc-arsiv-td-belge',
      'rc-arsiv-td-durum',
      'rc-arsiv-td-actions'
   ];
   function applyLabels(){
      $(SELECTOR).each(function(){
         $(this).find('tbody tr').each(function(){
            $(this).find('> td').each(function(idx){
               var label = labels[idx] || '';
               var cls = tdClasses[idx] || '';
               $(this).attr('data-label', label);
               if(cls && !$(this).hasClass(cls)) $(this).addClass(cls);
            });
         });
      });
   }
   $(document).on('draw.dt init.dt', SELECTOR, function(){
      setTimeout(applyLabels, 80);
   });
   $(document).ready(function(){ setTimeout(applyLabels, 200); });

   /* Sekme degisiminde aktif sinifi guncelle */
   $(document).on('shown.bs.tab', '.rc-arsiv-tab', function(){
      $('.rc-arsiv-tab').removeClass('active').attr('aria-selected','false');
      $(this).addClass('active').attr('aria-selected','true');
   });
})();
</script>

@endsection
