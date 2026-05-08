@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
   // Personel istatistikleri (formatted Collection icindeki HTML'den parse et)
   $totalPers = 0; $aktifPers = 0; $pasifPers = 0; $hesapSahibiSayisi = 0; $takvimdePers = 0;
   foreach(($personeller ?? []) as $p){
      $totalPers++;
      $durum = is_array($p) ? ($p['durum'] ?? '') : ($p->durum ?? '');
      $hesap = is_array($p) ? ($p['hesap_turu'] ?? '') : ($p->hesap_turu ?? '');
      $takvim = is_array($p) ? ($p['takvimde_gorunsun'] ?? 0) : ($p->takvimde_gorunsun ?? 0);
      if(strpos((string)$durum, 'Aktif') !== false) $aktifPers++; else $pasifPers++;
      if(trim($hesap) === 'Hesap Sahibi') $hesapSahibiSayisi++;
      if((int)$takvim === 1) $takvimdePers++;
   }
@endphp
<style>
   :root{
      --pyo-purple-1:#5C008E;
      --pyo-purple-2:#7B2FB8;
      --pyo-purple-3:#9D5DC8;
      --pyo-purple-soft:#B88ED8;
      --pyo-purple-bg:#f7f1fb;
      --pyo-grad: linear-gradient(135deg,#5C008E 0%,#7B2FB8 50%,#9D5DC8 100%);
      --pyo-text:#2d1b3f;
      --pyo-muted:#8a8295;
      --pyo-border:#ece6f2;
   }
   .pyo-hero{
      background: var(--pyo-grad);
      border-radius: 24px; padding: 28px 32px; color:#fff;
      position:relative; overflow:hidden;
      margin-bottom: 22px;
      box-shadow: 0 8px 24px rgba(92,0,142,.18);
   }
   .pyo-hero::before{
      content:''; position:absolute; top:-80px; right:-80px;
      width:280px; height:280px; border-radius:50%;
      background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
   }
   .pyo-hero::after{
      content:''; position:absolute; bottom:-60px; left:-60px;
      width:220px; height:220px; border-radius:50%;
      background: radial-gradient(circle, rgba(184,142,216,.25) 0%, transparent 70%);
   }
   .pyo-hero__inner{ position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; }
   .pyo-hero__title{ display:flex; align-items:center; gap:14px; }
   .pyo-hero__icon{
      width:54px; height:54px; border-radius:14px; background:rgba(255,255,255,.18);
      display:flex; align-items:center; justify-content:center; font-size:22px; backdrop-filter: blur(6px);
   }
   .pyo-hero__title h1{ font-size:24px; font-weight:700; margin:0; color:#fff; letter-spacing:-.3px; }
   .pyo-hero__title p{ margin:4px 0 0; color:rgba(255,255,255,.82); font-size:13px; }
   .pyo-hero__breadcrumb{ font-size:12px; color:rgba(255,255,255,.7); margin-bottom:10px; }
   .pyo-hero__breadcrumb a{ color:rgba(255,255,255,.85); text-decoration:none; }
   .pyo-hero__breadcrumb a:hover{ color:#fff; }

   /* Stat kartlari */
   .pyo-stats{ display:grid; grid-template-columns: repeat(4, 1fr); gap:14px; margin-bottom:22px; }
   @media(max-width:900px){ .pyo-stats{ grid-template-columns: repeat(2, 1fr); } }
   @media(max-width:500px){ .pyo-stats{ grid-template-columns: 1fr; } }
   .pyo-stat{
      background:#fff; border-radius:18px; padding:18px 20px;
      box-shadow: 0 2px 8px rgba(92,0,142,.06); border:1px solid var(--pyo-border);
      transition:all .25s cubic-bezier(.2,.8,.2,1);
   }
   .pyo-stat:hover{ transform:translateY(-3px); box-shadow: 0 8px 24px rgba(92,0,142,.12); }
   .pyo-stat__icon{
      width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center;
      font-size:18px; font-weight:700; margin-bottom:12px; color:#fff;
   }
   .pyo-stat__lbl{ font-size:11px; color:var(--pyo-muted); font-weight:700; letter-spacing:.5px; text-transform:uppercase; margin-bottom:4px; }
   .pyo-stat__val{ font-size:28px; font-weight:800; color:var(--pyo-text); letter-spacing:-.5px; line-height:1; }
   .pyo-stat--toplam .pyo-stat__icon{ background: var(--pyo-grad); }
   .pyo-stat--aktif .pyo-stat__icon{ background: linear-gradient(135deg,#10b981,#34d399); }
   .pyo-stat--aktif .pyo-stat__val{ color:#059669; }
   .pyo-stat--pasif .pyo-stat__icon{ background: linear-gradient(135deg,#94a3b8,#cbd5e1); }
   .pyo-stat--pasif .pyo-stat__val{ color:#64748b; }
   .pyo-stat--sahibi .pyo-stat__icon{ background: linear-gradient(135deg,#f59e0b,#fbbf24); }
   .pyo-stat--sahibi .pyo-stat__val{ color:#d97706; }
   .pyo-stat--takvim .pyo-stat__icon{ background: linear-gradient(135deg,#3b82f6,#60a5fa); }
   .pyo-stat--takvim .pyo-stat__val{ color:#2563eb; }

   /* Tab nav */
   .pyo-tabs{
      display:flex; gap:8px; padding:6px; background:#fff;
      border-radius:14px; box-shadow: 0 2px 8px rgba(92,0,142,.06);
      border:1px solid var(--pyo-border); margin-bottom:22px; flex-wrap:wrap;
   }
   .pyo-tabs .nav-item{ list-style:none; flex:1; min-width:160px; }
   .pyo-tabs .nav-item a{
      display:flex; align-items:center; justify-content:center; gap:8px;
      padding:11px 18px; border-radius:10px; border:0;
      font-weight:600; font-size:14px; color:var(--pyo-muted);
      background:transparent; transition:all .15s; text-decoration:none;
      cursor:pointer;
   }
   .pyo-tabs .nav-item a:hover{ background:var(--pyo-purple-bg); color:var(--pyo-purple-1); }
   .pyo-tabs .nav-item a.active{
      background: var(--pyo-grad) !important; color:#fff !important;
      box-shadow: 0 4px 12px rgba(92,0,142,.25);
   }

   /* Tablo karti */
   .pyo-table-card{
      background:#fff; border-radius:18px; padding:0;
      box-shadow: 0 2px 8px rgba(92,0,142,.06); border:1px solid var(--pyo-border);
      overflow:hidden;
   }
   .pyo-table-toolbar{
      padding:18px 22px 14px; display:flex; align-items:center; justify-content:space-between;
      gap:12px; flex-wrap:wrap; border-bottom:1px solid var(--pyo-border);
   }
   .pyo-table-toolbar h3{
      margin:0; font-size:17px; font-weight:700; color:var(--pyo-text);
      display:flex; align-items:center; gap:10px;
   }
   .pyo-table-toolbar h3 i{ color:var(--pyo-purple-2); }
   .pyo-table-toolbar h3 .pyo-count{
      background: var(--pyo-purple-bg); color: var(--pyo-purple-1);
      padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;
   }
   .pyo-btn-yeni{
      background: var(--pyo-grad); color:#fff; border:0;
      padding:10px 20px; border-radius:11px; font-weight:600; font-size:13.5px;
      display:inline-flex; align-items:center; gap:8px;
      box-shadow: 0 4px 14px rgba(92,0,142,.25); transition:all .15s; cursor:pointer;
   }
   .pyo-btn-yeni:hover{
      transform: translateY(-1px); color:#fff;
      box-shadow: 0 8px 22px rgba(92,0,142,.35);
   }
   .pyo-table-wrap{ padding: 8px 14px 14px; overflow-x:auto; }

   /* DataTable override */
   .pyo-table-wrap .dataTables_filter{ padding:14px 8px 6px; }
   .pyo-table-wrap .dataTables_filter input{
      border:2px solid var(--pyo-border); border-radius:10px; padding:7px 12px;
      background:#fafbfc; font-size:13px; min-width: 220px;
   }
   .pyo-table-wrap .dataTables_filter input:focus{
      outline:none; border-color:var(--pyo-purple-2); background:#fff;
      box-shadow: 0 0 0 3px rgba(123,47,184,.08);
   }
   .pyo-table-wrap .dataTables_info{ color:var(--pyo-muted); font-size:12.5px; padding:14px 8px; }

   #personel_tablo{ width:100% !important; border-collapse: separate; border-spacing:0; margin-top:8px !important; }
   #personel_tablo thead th{
      background:#fafbfc !important; color:var(--pyo-muted); font-size:11px; font-weight:700;
      letter-spacing:.5px; text-transform:uppercase; border:0 !important;
      padding:14px 12px !important; vertical-align:middle; white-space:nowrap;
   }
   #personel_tablo tbody td{
      border-bottom:1px solid #f4f0f8 !important; border-top:0 !important;
      padding:14px 12px !important; vertical-align:middle; font-size:13.5px; color:var(--pyo-text);
   }
   #personel_tablo tbody tr:hover td{ background:#fbfaff !important; }
   #personel_tablo tbody tr:last-child td{ border-bottom:0 !important; }

   /* Personel hucresi: avatar + isim */
   .pyo-pers-cell{ display:inline-flex; align-items:center; gap:11px; min-width:0; }
   .pyo-avatar{
      display:inline-flex; align-items:center; justify-content:center;
      width:36px; height:36px; border-radius:50%;
      background: var(--pyo-purple-bg); color: var(--pyo-purple-1);
      font-weight:700; font-size:14px; flex-shrink:0;
      border: 2px solid #fff; box-shadow: 0 1px 3px rgba(92,0,142,.08);
   }
   .pyo-pers-name{ font-weight:600; color:var(--pyo-text); }

   /* Takvim toggle pill */
   .pyo-takvim-toggle{
      display:inline-flex; align-items:center; gap:6px;
      padding:5px 12px; border-radius:20px;
      font-size:11.5px; font-weight:600; letter-spacing:.2px;
      border:1px solid transparent; cursor:pointer; transition:all .15s;
   }
   .pyo-takvim-toggle.is-on{
      background:#dcfce7; color:#15803d; border-color:#bbf7d0;
   }
   .pyo-takvim-toggle.is-on:hover{ background:#bbf7d0; }
   .pyo-takvim-toggle.is-off{
      background:#f3f4f6; color:#6b7280; border-color:#e5e7eb;
   }
   .pyo-takvim-toggle.is-off:hover{ background:#e5e7eb; color:#475569; }
   .pyo-takvim-toggle i{ font-size:10.5px; }

   /* Hesap turu pill */
   .pyo-hesap-badge{
      display:inline-block; padding:4px 11px; border-radius:20px;
      font-size:11.5px; font-weight:600; letter-spacing:.2px;
      background: var(--pyo-purple-bg); color: var(--pyo-purple-1);
      border: 1px solid #e0d4ec;
   }
   .pyo-hesap-badge--sahibi{ background:#fef3c7; color:#92400e; border-color:#fde68a; }
   .pyo-hesap-badge--bos{ color: var(--pyo-muted); background:transparent; border:0; padding:0; font-size:12px; font-style:italic; }

   /* Durum buton override (controller HTML'i btn-success/btn-danger uretiyor) */
   #personel_tablo td .btn-success{
      background:#dcfce7 !important; color:#15803d !important; border:0 !important;
      padding:5px 14px !important; border-radius:20px !important;
      font-size:11.5px !important; font-weight:700 !important; letter-spacing:.3px;
      cursor:default; pointer-events:none;
   }
   #personel_tablo td .btn-danger{
      background:#fee2e2 !important; color:#991b1b !important; border:0 !important;
      padding:5px 14px !important; border-radius:20px !important;
      font-size:11.5px !important; font-weight:700 !important; letter-spacing:.3px;
      cursor:default; pointer-events:none;
   }

   /* Siralama (yukari/asagi) butonlari */
   #personel_tablo td .btn-info{
      background: var(--pyo-purple-bg) !important; color: var(--pyo-purple-1) !important;
      border:0 !important; width:30px !important; height:30px !important;
      padding:0 !important; border-radius:8px !important;
      display:inline-flex !important; align-items:center; justify-content:center;
      margin: 0 2px; transition: .15s;
   }
   #personel_tablo td .btn-info:hover{
      background: var(--pyo-purple-2) !important; color:#fff !important;
      transform: translateY(-1px);
   }

   /* Islemler dropdown */
   #personel_tablo td .dropdown-toggle{
      width:36px; height:36px; border-radius:10px;
      background: var(--pyo-purple-bg) !important; color: var(--pyo-purple-1) !important;
      display:inline-flex; align-items:center; justify-content:center;
      transition:.15s; line-height:1;
   }
   #personel_tablo td .dropdown-toggle:hover{
      background: var(--pyo-purple-2) !important; color:#fff !important;
   }
   #personel_tablo td .dropdown-menu{
      border-radius:12px; border:1px solid var(--pyo-border);
      box-shadow: 0 10px 30px rgba(92,0,142,.12); padding:6px;
   }
   #personel_tablo td .dropdown-menu,
   #personel_tablo td .dropdown-menu-icon-list{
      padding:6px !important;
   }
   #personel_tablo td .dropdown-menu .dropdown-item,
   #personel_tablo td .dropdown-menu-icon-list .dropdown-item{
      padding:9px 12px !important;
      padding-left:12px !important;
      border-radius:8px !important;
      font-size:13px !important;
      color:var(--pyo-text) !important;
      display:flex !important;
      align-items:center !important;
      gap:10px !important;
      white-space:nowrap !important;
      position:relative !important;
   }
   #personel_tablo td .dropdown-menu .dropdown-item:hover,
   #personel_tablo td .dropdown-menu-icon-list .dropdown-item:hover{
      background: var(--pyo-purple-bg) !important;
      color: var(--pyo-purple-1) !important;
   }
   #personel_tablo td .dropdown-menu .dropdown-item i,
   #personel_tablo td .dropdown-menu-icon-list .dropdown-item i{
      position:static !important;
      width:16px !important;
      height:auto !important;
      min-width:16px !important;
      flex-shrink:0 !important;
      text-align:center !important;
      color: var(--pyo-purple-2) !important;
      margin:0 !important;
      padding:0 !important;
      left:auto !important;
      top:auto !important;
      right:auto !important;
      font-size:14px !important;
      line-height:1 !important;
   }
   #personel_tablo td .dropdown-menu .dropdown-item:hover i,
   #personel_tablo td .dropdown-menu-icon-list .dropdown-item:hover i{
      color: var(--pyo-purple-1) !important;
   }

   @media(max-width:600px){
      .pyo-hero{ padding:20px 22px; border-radius:18px; }
      .pyo-hero__title h1{ font-size:18px; }
      .pyo-hero__icon{ width:42px; height:42px; font-size:18px; }
      .pyo-table-card{ border-radius:14px; }
      #personel_tablo tbody td{ padding:10px 8px !important; font-size:12.5px; }
   }
</style>

<div class="pyo-hero">
   <div class="pyo-hero__inner">
      <div>
         <div class="pyo-hero__breadcrumb">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
            <span> · </span>
            <span>{{$sayfa_baslik}}</span>
         </div>
         <div class="pyo-hero__title">
            <div class="pyo-hero__icon"><i class="fa fa-users"></i></div>
            <div>
               <h1>{{$sayfa_baslik}}</h1>
               <p>Salonunuzdaki personellerin yönetimi, çalışma saatleri ve hak edişleri</p>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="pyo-stats">
   <div class="pyo-stat pyo-stat--takvim">
      <div class="pyo-stat__icon"><i class="fa fa-calendar"></i></div>
      <div class="pyo-stat__lbl">Takvimde</div>
      <div class="pyo-stat__val">{{$takvimdePers}}</div>
   </div>
   <div class="pyo-stat pyo-stat--aktif">
      <div class="pyo-stat__icon"><i class="fa fa-check"></i></div>
      <div class="pyo-stat__lbl">Aktif</div>
      <div class="pyo-stat__val">{{$aktifPers}}</div>
   </div>
   <div class="pyo-stat pyo-stat--pasif">
      <div class="pyo-stat__icon"><i class="fa fa-pause"></i></div>
      <div class="pyo-stat__lbl">Pasif</div>
      <div class="pyo-stat__val">{{$pasifPers}}</div>
   </div>
   <div class="pyo-stat pyo-stat--toplam">
      <div class="pyo-stat__icon"><i class="fa fa-users"></i></div>
      <div class="pyo-stat__lbl">Toplam Personel</div>
      <div class="pyo-stat__val">{{$totalPers}}</div>
   </div>
</div>
<ul class="pyo-tabs nav nav-tabs" role="tablist" id="personelYonetimiTabs">
   <li class="nav-item">
      <a class="nav-link active" data-toggle="tab" href="#personeller" role="tab" aria-selected="true" id="tabBtn-personeller">
         <i class="fa fa-users"></i> Personeller
      </a>
   </li>
   <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#primHakedis" role="tab" aria-selected="false" id="tabBtn-primHakedis">
         <i class="fa fa-money"></i> Prim & Hak Ediş
      </a>
   </li>
</ul>

<div class="tab-content">
   <div class="tab-pane fade show active" id="personeller" role="tabpanel">
      <div class="pyo-table-card">
         <div class="pyo-table-toolbar">
            <h3>
               <i class="fa fa-list-ul"></i> Personel Listesi
               <span class="pyo-count">{{$totalPers}}</span>
            </h3>
            <button type="button" onclick="modalbaslikata('Yeni Personel','yenipersonelbilgiekle')" class="pyo-btn-yeni" data-toggle="modal" data-target="#personel-modal">
               <i class="fa fa-plus"></i> Yeni Personel
            </button>
         </div>
         <div class="pyo-table-wrap">
            <table class="data-table table hover nowrap" id="personel_tablo" style="width:100%">
               <thead>
                  <tr>
                     <th style="width:90px">Sıralama</th>
                     <th>Personel</th>
                     <th>Hesap Tipi</th>
                     <th>Telefon</th>
                     <th>Durum</th>
                     <th class="datatable-nosort">Takvim</th>
                     <th class="datatable-nosort" style="width:90px">İşlemler</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>
      </div>
   </div>
   <div class="tab-pane fade" id="primHakedis" role="tabpanel">
      @include('isletmeadmin.partials.prim_hakedis_panel')
   </div>
</div>

@include('isletmeadmin.partials.personel_modal')

<script type="text/javascript">
$(document).ready(function(){
   // URL ?_tab=prim ise Prim & Hak Ediş tab'ini aktif et (ödeme/bonus/kesinti sonrası reload bu paramla geliyor)
   try {
      var _tabParam = (new URL(window.location.href)).searchParams.get('_tab');
      if(_tabParam === 'prim'){
         var $btn = $('#tabBtn-primHakedis');
         if($btn.length){
            // Bootstrap tab tetikleme
            try { $btn.tab('show'); } catch(e){ $btn.trigger('click'); }
         }
      }
   } catch(e){}

   if($('#personel_tablo').length){
      try { $('#personel_tablo').DataTable().destroy(); } catch(e){}
      $('#personel_tablo').DataTable({
         ordering: false,
         autoWidth: false,
         responsive: true,
         paging: false,
         "language" : {
            "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
            searchPlaceholder: "Ara",
            paginate: {
               next: '<i class="ion-chevron-right"></i>',
               previous: '<i class="ion-chevron-left"></i>'
            }
         },
         columns:[
            { data : 'siralama', className: "text-center" },
            { data : 'ad_soyad', render: function(data, type, row){
               if(type !== 'display') return data;
               var initial = (data && data.length) ? data.charAt(0).toUpperCase() : '?';
               return '<span class="pyo-pers-cell"><span class="pyo-avatar">'+initial+'</span><span class="pyo-pers-name">'+(data||'')+'</span></span>';
            }},
            { data : 'hesap_turu', render: function(data, type, row){
               if(type !== 'display') return data;
               var t = (data || '').trim();
               if(!t) return '<span class="pyo-hesap-badge pyo-hesap-badge--bos">—</span>';
               var cls = 'pyo-hesap-badge';
               if(t === 'Hesap Sahibi') cls += ' pyo-hesap-badge--sahibi';
               return '<span class="'+cls+'">'+t+'</span>';
            }},
            { data : 'telefon' },
            { data : 'durum' },
            { data : 'takvim', className: "text-center" },
            { data : 'islemler' },
         ],
         data: <?php echo $personeller; ?>,
      });
   }

   // Tab degisince DataTable column genisliklerini tekrar hesapla
   $('a[data-toggle="tab"]').on('shown.bs.tab', function(){
      try { $('#personel_tablo').DataTable().columns.adjust().responsive.recalc(); } catch(e){}
   });

   // ====== Siralama: capture-phase handler (custom.js'i tamamen baypas) ======
   function _pyoSiralamaUpdate(url, btn){
      $.ajax({
         type: 'GET',
         url: url,
         data: {
            personelid: btn.getAttribute('data-value'),
            sube: '{{$isletme->id}}',
            siraNo: btn.getAttribute('data-index-number')
         },
         dataType: 'json',
         beforeSend: function(){ $('#preloader').show(); },
         success: function(result){
            $('#preloader').hide();
            try {
               var dt = $('#personel_tablo').DataTable();
               dt.clear();
               dt.rows.add(result);
               dt.draw(false);
            } catch(e){
               console.error('DataTable update failed', e);
               location.reload();
            }
         },
         error: function(xhr, status, err){
            $('#preloader').hide();
            console.error('Siralama hatasi:', xhr.status, xhr.statusText, xhr.responseText);
            swal({title:'Hata', text:'Sıralama güncellenemedi (HTTP '+xhr.status+')', type:'error'});
         }
      });
   }
   // Capture phase'de dinle: custom.js'in bubble handler'larindan ONCE firing yapar
   if(!window._pyoSiraCaptureBound){
      window._pyoSiraCaptureBound = true;
      document.addEventListener('click', function(e){
         var asagi = e.target.closest && e.target.closest('#personel_tablo button[name="personel_siralamayi_bir_asagi_tasi"]');
         var yukari = e.target.closest && e.target.closest('#personel_tablo button[name="personel_siralamayi_bir_yukari_tasi"]');
         var takvim = e.target.closest && e.target.closest('#personel_tablo button[name="personel_takvim_toggle"]');
         var silBtn = e.target.closest && e.target.closest('#personel_tablo a[name="personel_sil"]');
         if(asagi){
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            _pyoSiralamaUpdate('/isletmeyonetim/personelSiralamaArtir', asagi);
            return;
         }
         if(yukari){
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            _pyoSiralamaUpdate('/isletmeyonetim/personelSiralamaAzalt', yukari);
            return;
         }
         if(takvim){
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            _pyoSiralamaUpdate('/isletmeyonetim/personelTakvimdeGorunsunToggle', takvim);
            return;
         }
         if(silBtn){
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            var pid = silBtn.getAttribute('data-value');
            var adi = silBtn.getAttribute('data-adi') || 'Bu personel';
            swal({
               title: adi+' silinsin mi?',
               html: '<div style="text-align:left; line-height:1.6"><b>Personel kalıcı olarak listeden gizlenecek.</b><br>'+
                     '✓ Geçmiş randevu, satış, prim ve hak ediş kayıtları korunacak<br>'+
                     '✓ Raporlar ve istatistikler etkilenmeyecek<br>'+
                     '✓ Listeden, takvimden ve online randevudan kalkacak</div>',
               type: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Evet, sil',
               cancelButtonText: 'Vazgeç',
               confirmButtonColor: '#dc2626',
               cancelButtonClass: 'btn btn-secondary'
            }).then(function(r){
               if(!r.value) return;
               $.ajax({
                  type:'POST',
                  url:'/isletmeyonetim/personelArsivle',
                  data:{ personelid: pid, sube: '{{$isletme->id}}', _token: $('input[name="_token"]').val() },
                  headers:{ 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
                  dataType:'json',
                  beforeSend:function(){ $('#preloader').show(); },
                  success:function(result){
                     $('#preloader').hide();
                     try{
                        var dt = $('#personel_tablo').DataTable();
                        dt.clear(); dt.rows.add(result); dt.draw(false);
                     }catch(e){ location.reload(); }
                     swal({title:'Silindi', type:'success', timer:1200, showConfirmButton:false});
                  },
                  error:function(xhr){
                     $('#preloader').hide();
                     swal({title:'Hata', text:'Silinemedi (HTTP '+xhr.status+')', type:'error'});
                  }
               });
            });
            return;
         }
      }, true); // <-- capture phase: bubble'dan once firing
   }

   // ====== Personel kayit/duzenleme sonrasi tablo + hak edis tutarlarini tazelemek icin
   //         tab korunarak sayfa reload ======
   $(document).ajaxComplete(function(e, xhr, settings){
      if(!settings || !settings.url) return;
      // Personel save endpoint'i mi?
      if(settings.url.indexOf('/personelekleduzenle') === -1) return;
      if(xhr.status !== 200) return;
      try {
         // Aktif tab'i koru
         var $active = $('.pyo-tabs .nav-link.active');
         var href = $active.attr('href') || '#personeller';
         var tabParam = (href === '#primHakedis') ? 'prim' : 'personeller';
         var url = new URL(window.location.href);
         url.searchParams.set('_tab', tabParam);
         // swal'in 3sn timer'i bitmesin diye 1.5sn yeterli (modal kapanmis olur)
         setTimeout(function(){ window.location.href = url.toString(); }, 1500);
      } catch(err){
         setTimeout(function(){ window.location.reload(); }, 1500);
      }
   });
});
</script>

@endsection()
