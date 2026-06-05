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

/* === KART KONTEYNER ===
   Liste alani artik beyaz boslukta degil — hafif mor gradient
   bir zeminde her randevu icon'lu bir kart olarak goruntuleniyor. */
.rc-rl-card {
   background: linear-gradient(180deg, #f8f4fd 0%, #f3ebfb 100%);
   border-radius: 16px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 6px 24px rgba(92, 0, 142, .06);
   padding: 10px;
   margin-bottom: 30px;
   border: 1px solid #ede4f7;
}

/* === TABLO WRAPPER === */
.rc-rl-tablo-wrap {
   width: 100%;
   border-radius: 12px;
}

/* === DATATABLES WRAPPER === */
.rc-rl-card .dataTables_wrapper {
   padding: 16px 14px 10px;
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

/* === TABLO -> KART GRID DONUSUMU (TUM CIHAZLAR) ===
   tbody auto-fill grid; her tr ayri bir kart. thead gizli.
   td'ler grid-area ile kart icinde yerlesir. DataTables yine
   normal tablo gibi davranir — sadece CSS gorunum farkli. */
.rc-rl-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
   display: block !important;
   width: 100% !important;
   min-width: 0 !important;
}
.rc-rl-table thead { display: none !important; }

.rc-rl-table tbody {
   display: grid !important;
   grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
   gap: 16px;
   padding: 6px 4px;
   width: 100% !important;
}

/* RANDEVU KARTI */
.rc-rl-table tbody tr,
.rc-rl-table.stripe tbody tr.odd,
.rc-rl-table.stripe tbody tr.even {
   display: grid !important;
   grid-template-columns: minmax(0, 1fr) auto;
   grid-template-areas:
      "musteri  durum"
      "telefon  durum"
      "hizmetler hizmetler"
      "personel  personel"
      "metarow   metarow"
      "footer    footer";
   gap: 4px 14px;
   padding: 18px 20px 16px;
   background: #fff !important;
   border: 1px solid #ece4f5 !important;
   border-radius: 16px !important;
   box-shadow: 0 2px 8px rgba(92, 0, 142, .04);
   transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
   position: relative;
   overflow: hidden;
   width: 100% !important;
   margin: 0 !important;
}
.rc-rl-table tbody tr::before {
   content: "";
   position: absolute;
   left: 0; top: 0; bottom: 0;
   width: 4px;
   background: linear-gradient(180deg, var(--rc-purple-dark), var(--rc-purple) 60%, var(--rc-pink));
}
.rc-rl-table tbody tr:hover,
.rc-rl-table.stripe tbody tr.odd:hover,
.rc-rl-table.stripe tbody tr.even:hover {
   transform: translateY(-3px);
   box-shadow: 0 14px 28px rgba(92, 0, 142, .14);
   border-color: var(--rc-purple-soft) !important;
}

/* HUCRE RESET (her td bir kart bolumu) */
.rc-rl-table tbody tr td,
.rc-rl-table.stripe tbody tr.odd td,
.rc-rl-table.stripe tbody tr.even td {
   display: block !important;
   padding: 0 !important;
   border: none !important;
   background: transparent !important;
   color: var(--rc-text);
   font-size: 13.5px;
   line-height: 1.5;
   vertical-align: top !important;
   white-space: normal !important;
   min-width: 0;
   overflow-wrap: break-word;
   word-break: break-word;
   box-shadow: none !important;
   border-radius: 0 !important;
}

/* GRID-AREA atamalari (JS data-label/class ekledigi icin calisir) */
.rc-rl-table tbody td.rc-rl-td-musteri    { grid-area: musteri; }
.rc-rl-table tbody td.rc-rl-td-telefon    { grid-area: telefon; }
.rc-rl-table tbody td.rc-rl-td-hizmetler  { grid-area: hizmetler; }
.rc-rl-table tbody td.rc-rl-td-personel   { grid-area: personel; }
.rc-rl-table tbody td.rc-rl-td-durum      { grid-area: durum; justify-self: end; align-self: start; }
.rc-rl-table tbody td.rc-rl-td-tarih      { grid-area: metarow; justify-self: start; align-self: center; }
.rc-rl-table tbody td.rc-rl-td-saat       { grid-area: metarow; justify-self: start; align-self: center; margin-left: 8px; }
.rc-rl-table tbody td.rc-rl-td-olusturan  { grid-area: footer; }
.rc-rl-table tbody td.rc-rl-td-actions    { grid-area: footer; justify-self: end; align-self: center; }

/* MUSTERI (kart basligi) */
.rc-rl-table tbody td.rc-rl-td-musteri {
   font-size: 16px !important;
   font-weight: 700 !important;
   color: var(--rc-text) !important;
   letter-spacing: -.1px;
   min-width: 0 !important;
   overflow-wrap: anywhere;
}
.rc-rl-table tbody td.rc-rl-td-musteri::before { content: none !important; display: none; }

/* TELEFON */
.rc-rl-table tbody td.rc-rl-td-telefon {
   color: var(--rc-text-soft) !important;
   font-size: 13px !important;
   font-weight: 500 !important;
   display: flex !important;
   align-items: center;
   gap: 6px;
   min-width: 0 !important;
   overflow-wrap: anywhere;
}
.rc-rl-table tbody td.rc-rl-td-telefon::before {
   content: "\f095";
   font-family: "FontAwesome";
   color: var(--rc-purple);
   font-size: 12px;
   font-weight: 400;
   margin: 0;
   min-width: 0;
   letter-spacing: 0;
   text-transform: none;
}

/* HIZMETLER */
.rc-rl-table tbody td.rc-rl-td-hizmetler {
   margin-top: 14px;
   padding-top: 14px !important;
   border-top: 1px dashed #ece4f5 !important;
}
.rc-rl-table tbody td.rc-rl-td-hizmetler::before {
   content: "Hizmetler";
   display: block;
   font-size: 10.5px;
   font-weight: 700;
   color: var(--rc-purple-dark);
   text-transform: uppercase;
   letter-spacing: .06em;
   margin-bottom: 4px;
}

/* PERSONEL/CIHAZ/ODA */
.rc-rl-table tbody td.rc-rl-td-personel {
   margin-top: 10px;
}
.rc-rl-table tbody td.rc-rl-td-personel::before {
   content: "Personel / Cihaz / Oda";
   display: block;
   font-size: 10.5px;
   font-weight: 700;
   color: var(--rc-purple-dark);
   text-transform: uppercase;
   letter-spacing: .06em;
   margin-bottom: 4px;
}

/* TARIH + SAAT — mor pill chip'ler yan yana */
.rc-rl-table tbody td.rc-rl-td-tarih,
.rc-rl-table tbody td.rc-rl-td-saat {
   display: inline-flex !important;
   margin-top: 14px;
   background: linear-gradient(135deg, #faf5ff 0%, #f5eefe 100%) !important;
   border: 1px solid #ece4f5 !important;
   border-radius: 10px !important;
   padding: 8px 12px !important;
   align-items: center;
   gap: 6px;
   font-size: 13px !important;
   font-weight: 600 !important;
   color: var(--rc-purple-dark) !important;
}
.rc-rl-table tbody td.rc-rl-td-tarih::before {
   content: "\f073";
   font-family: "FontAwesome";
   color: var(--rc-purple);
   font-size: 12px;
   font-weight: 400;
   margin: 0;
   min-width: 0;
   letter-spacing: 0;
   text-transform: none;
}
.rc-rl-table tbody td.rc-rl-td-saat::before {
   content: "\f017";
   font-family: "FontAwesome";
   color: var(--rc-purple);
   font-size: 12px;
   font-weight: 400;
   margin: 0;
   min-width: 0;
   letter-spacing: 0;
   text-transform: none;
}

/* FOOTER: olusturan + actions */
.rc-rl-table tbody td.rc-rl-td-olusturan {
   margin-top: 14px;
   padding-top: 12px !important;
   border-top: 1px dashed #ece4f5 !important;
   color: var(--rc-text-soft) !important;
   font-size: 12px !important;
   display: inline-flex !important;
   align-items: center;
   gap: 6px;
}
.rc-rl-table tbody td.rc-rl-td-olusturan::before {
   content: "Oluşturan:";
   font-size: 11px;
   font-weight: 700;
   color: var(--rc-text-soft);
   text-transform: uppercase;
   letter-spacing: .04em;
   margin: 0;
   min-width: 0;
}
.rc-rl-table tbody td.rc-rl-td-actions {
   margin-top: 14px;
   padding-top: 12px !important;
   border-top: 1px dashed #ece4f5 !important;
}
.rc-rl-table tbody td.rc-rl-td-actions::before { content: none !important; display: none; }

/* DURUM */
.rc-rl-table tbody td.rc-rl-td-durum::before { content: none !important; display: none; }
.rc-rl-table tbody td .btn.btn-warning,
.rc-rl-table tbody td .btn.btn-success,
.rc-rl-table tbody td .btn.btn-primary,
.rc-rl-table tbody td .btn.btn-danger,
.rc-rl-table tbody td .btn.btn-dark {
   border-radius: 999px !important;
   padding: 5px 12px !important;
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

/* === BOS DURUM === */
.rc-rl-table tbody .dataTables_empty {
   display: block !important;
   text-align: center;
   padding: 50px 20px !important;
   color: var(--rc-text-soft);
   font-size: 14px;
   background: #fff !important;
   border-radius: 12px !important;
   border: 1px dashed var(--rc-border) !important;
   grid-column: 1 / -1;
}

/* === RESPONSIVE: BUYUK TABLET / KUCUK LAPTOP (≤1200px) === */
@media (max-width: 1200px) {
   .rc-rl-table tbody {
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 14px;
   }
}

/* === RESPONSIVE: TABLET LANDSCAPE (≤1024px) === */
@media (max-width: 1024px) {
   .rc-rl-header { padding: 14px 16px; }
   .rc-rl-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-rl-title { font-size: 17px; }
   .rc-rl-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-rl-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
   .rc-rl-card .dataTables_wrapper { padding: 12px 10px 6px; }
   .rc-rl-card .dataTables_filter input { width: 200px; }
   .rc-rl-table tbody {
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 12px;
   }
   .rc-rl-table tbody tr { padding: 16px 18px 14px; }
   .rc-rl-table tbody td.rc-rl-td-musteri { font-size: 15px !important; }
}

/* === RESPONSIVE: TABLET PORTRAIT (≤900px) — tek sutun kart ===
   Laravel sidebar acikken icerik genisligi yetersiz oluyor;
   bu noktada kart'lar tek sutuna geciyor. */
@media (max-width: 900px) {
   .rc-rl-table tbody {
      grid-template-columns: 1fr;
      gap: 12px;
   }
   .rc-rl-table tbody tr {
      grid-template-columns: minmax(0, 1fr) auto;
      padding: 16px 18px;
   }
   .rc-rl-table tbody td.rc-rl-td-musteri { font-size: 15.5px !important; }
}

/* === RESPONSIVE: MOBILE (≤768px) === */
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

   .rc-rl-card { padding: 8px; border-radius: 14px; }
   .rc-rl-card .dataTables_wrapper { padding: 10px 8px 4px; }
   .rc-rl-card .dataTables_filter { float: none; text-align: left; }
   .rc-rl-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-rl-card .dataTables_filter label { display: block; width: 100%; }
   .rc-rl-card .dataTables_length { display: none; }

   .rc-rl-table tbody { padding: 4px 2px; }
   .rc-rl-table tbody tr { padding: 14px 16px; }
   .rc-rl-table tbody td.rc-rl-td-musteri { font-size: 15px !important; }
   .rc-rl-table tbody td.rc-rl-td-tarih,
   .rc-rl-table tbody td.rc-rl-td-saat {
      padding: 7px 10px !important;
      font-size: 12.5px !important;
   }
   .rc-rl-table tbody td.rc-rl-td-saat { margin-left: 6px; }
}

/* === RESPONSIVE: KUCUK MOBILE (≤480px) ===
   Cok dar ekranda durum + dropdown footer'a dussun, header sade kalsin. */
@media (max-width: 480px) {
   .rc-rl-table tbody tr {
      grid-template-columns: 1fr !important;
      grid-template-areas:
         "musteri"
         "telefon"
         "durum"
         "hizmetler"
         "personel"
         "metarow"
         "footer" !important;
      gap: 6px;
   }
   .rc-rl-table tbody td.rc-rl-td-durum {
      justify-self: start !important;
      align-self: auto !important;
      margin-top: 8px;
   }
   .rc-rl-table tbody td.rc-rl-td-tarih { margin-right: 0; }
   .rc-rl-table tbody td.rc-rl-td-saat { margin-left: 0; }
}

/* === RESPONSIVE: KUCUCUK MOBILE (≤380px) === */
@media (max-width: 380px) {
   .rc-rl-header-right { flex-direction: column; }
   .rc-rl-header-right .rc-rl-btn { width: 100%; }
   .rc-rl-title-row { gap: 10px; }
   .rc-rl-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-rl-table tbody tr { padding: 14px; }
   .rc-rl-table tbody td.rc-rl-td-musteri { font-size: 14.5px !important; }
   .rc-rl-table tbody td.rc-rl-td-telefon { font-size: 12.5px !important; }
   .rc-rl-table tbody td.rc-rl-td-tarih,
   .rc-rl-table tbody td.rc-rl-td-saat {
      font-size: 12px !important;
      padding: 6px 10px !important;
   }
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
