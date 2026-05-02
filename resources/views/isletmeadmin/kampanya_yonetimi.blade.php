@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@php
   // Kanal bazlı istatistik
   $reklamlar       = $kampanya_yonetimi ?? collect();
   $toplamReklam    = is_countable($reklamlar) ? count($reklamlar) : 0;
   $smsSayisi       = 0;
   $aramaSayisi     = 0;
   $bildirimSayisi  = 0;
   $bilgiSayisi     = 0;
   $toplamKatilimci = 0;
   foreach($reklamlar as $r) {
      $gt = is_object($r) ? ($r->gorev_turu ?? '') : ($r['gorev_turu'] ?? '');
      $kt = is_object($r) ? (int)($r->katilimci_sayisi ?? 0) : (int)($r['katilimci_sayisi'] ?? 0);
      $toplamKatilimci += $kt;
      if($gt === 'SMS')         $smsSayisi++;
      elseif($gt === 'Arama')   $aramaSayisi++;
      elseif($gt === 'Bildirim') $bildirimSayisi++;
      elseif($gt === 'Bilgilendirme') $bilgiSayisi++;
   }
@endphp
<div class="page-header">
   <div class="row align-items-center">
      <div class="col-md-7 col-sm-7">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">{{$sayfa_baslik}}</li>
            </ol>
         </nav>
      </div>
      <div class="col-md-5 col-sm-5 text-right reklam-quickactions">
         <button class="btn-reklam-grup-gor" data-toggle="modal" data-target="#grup_sms_liste_modal">
            <i class="fa fa-eye"></i> <span class="d-none d-md-inline">Grupları Gör</span>
         </button>
         <button class="btn-reklam-grup-yeni" onclick="modalbaslikata('Yeni Grup','grup_sms_formu')" data-toggle="modal" id='grup_olustur_buton' data-target="#grup_sms_olustur_modal">
            <i class="fa fa-user-plus"></i> <span class="d-none d-md-inline">Grup Oluştur</span>
         </button>
      </div>
   </div>
</div>

<!-- KANAL HERO ÖZET KARTLARI -->
<div class="reklam-hero">
   <div class="reklam-hero-bg"></div>
   <div class="reklam-hero-inner">
      <div class="reklam-hero-title">
         <div class="reklam-hero-title-icon">
            <i class="fa fa-bullhorn"></i>
         </div>
         <div class="reklam-hero-title-text">
            <h2>Reklam Yönetimi</h2>
            <p>Müşterilerinize <b>SMS</b>, <b>arama</b> veya <b>uygulama bildirimi</b> ile özel kampanyalar gönderin. Her kanalın kendi rengi ve ikonu vardır — birbirine karıştırmadan yönetebilirsiniz.</p>
         </div>
         <a href="#" data-toggle="modal" data-target="#yeni_kampanya_modal" class="btn-reklam-yeni-cta">
            <i class="fa fa-plus-circle"></i>
            <span>Yeni Reklam<br><b>Oluştur</b></span>
         </a>
      </div>

      <div class="reklam-stats">
         <div class="reklam-stat reklam-stat--total" data-kanal="">
            <div class="reklam-stat-ic"><i class="fa fa-list"></i></div>
            <div class="reklam-stat-body">
               <div class="reklam-stat-num">{{$toplamReklam}}</div>
               <div class="reklam-stat-label">Toplam Reklam</div>
               <div class="reklam-stat-sub">{{$toplamKatilimci}} katılımcı</div>
            </div>
         </div>
         <div class="reklam-stat reklam-stat--sms" data-kanal="SMS">
            <div class="reklam-stat-ic"><i class="fa fa-comment-alt"></i></div>
            <div class="reklam-stat-body">
               <div class="reklam-stat-num">{{$smsSayisi}}</div>
               <div class="reklam-stat-label">SMS Reklamı</div>
               <div class="reklam-stat-sub">Toplu SMS gönderimi</div>
            </div>
         </div>
         <div class="reklam-stat reklam-stat--call" data-kanal="Arama">
            <div class="reklam-stat-ic"><i class="fa fa-phone-alt"></i></div>
            <div class="reklam-stat-body">
               <div class="reklam-stat-num">{{$aramaSayisi}}</div>
               <div class="reklam-stat-label">Santral Arama</div>
               <div class="reklam-stat-sub">Sesli kayıt ile arama</div>
            </div>
         </div>
         <div class="reklam-stat reklam-stat--push" data-kanal="Bildirim">
            <div class="reklam-stat-ic"><i class="fa fa-bell"></i></div>
            <div class="reklam-stat-body">
               <div class="reklam-stat-num">{{$bildirimSayisi}}</div>
               <div class="reklam-stat-label">Uygulama Bildirimi</div>
               <div class="reklam-stat-sub">Push notification</div>
            </div>
         </div>
         <div class="reklam-stat reklam-stat--info" data-kanal="Bilgilendirme">
            <div class="reklam-stat-ic"><i class="fa fa-info-circle"></i></div>
            <div class="reklam-stat-body">
               <div class="reklam-stat-num">{{$bilgiSayisi}}</div>
               <div class="reklam-stat-label">Bilgilendirme</div>
               <div class="reklam-stat-sub">Genel duyuru</div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- KANAL FİLTRE TABLARI + TABLO -->
<div class="card-box reklam-card mb-30">
   <div class="reklam-card-header">
      <div class="reklam-tabs" role="tablist">
         <button type="button" class="reklam-tab is-active" data-kanal="">
            <i class="fa fa-list"></i> Tümü
            <span class="reklam-tab-count">{{$toplamReklam}}</span>
         </button>
         <button type="button" class="reklam-tab reklam-tab--sms" data-kanal="SMS">
            <i class="fa fa-comment-alt"></i> SMS
            <span class="reklam-tab-count">{{$smsSayisi}}</span>
         </button>
         <button type="button" class="reklam-tab reklam-tab--call" data-kanal="Arama">
            <i class="fa fa-phone-alt"></i> Arama
            <span class="reklam-tab-count">{{$aramaSayisi}}</span>
         </button>
         <button type="button" class="reklam-tab reklam-tab--push" data-kanal="Bildirim">
            <i class="fa fa-bell"></i> Bildirim
            <span class="reklam-tab-count">{{$bildirimSayisi}}</span>
         </button>
         <button type="button" class="reklam-tab reklam-tab--info" data-kanal="Bilgilendirme">
            <i class="fa fa-info-circle"></i> Bilgilendirme
            <span class="reklam-tab-count">{{$bilgiSayisi}}</span>
         </button>
      </div>
   </div>

   <div class="reklam-card-body">
      <div class="reklam-empty" id="reklamBosDurum" style="display:none;">
         <div class="reklam-empty-ic"><i class="fa fa-bullhorn"></i></div>
         <h4>Henüz reklam oluşturulmadı</h4>
         <p>İlk reklamınızı oluşturmak için <b>Yeni Reklam</b> butonuna tıklayın.</p>
         <a href="#" data-toggle="modal" data-target="#yeni_kampanya_modal" class="btn btn-reklam-primary">
            <i class="fa fa-plus"></i> Yeni Reklam Oluştur
         </a>
      </div>

      <table class="data-table table stripe hover nowrap reklam-table" id="kampanyayonetim_tablo">
         <thead>
            <tr>
               <th>Kanal</th>
               <th>Kampanya</th>
               <th>Başlangıç</th>
               <th>Bitiş</th>
               <th>Saat</th>
               <th>Hizmet/Ürün</th>
               <th>İndirim</th>
               <th>Müşteri</th>
               <th>Katılımcı</th>
               <th class="text-right">İşlemler</th>
            </tr>
         </thead>
         <tbody></tbody>
      </table>
   </div>
</div>

@if($toplamReklam == 0)
<script>document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('reklamBosDurum');if(b)b.style.display='block';});</script>
@endif

<style>
/* ============================================================
   REKLAM YÖNETİMİ — MODERN UI
   3 kanal: SMS (mavi/cyan), Arama (yeşil), Bildirim (mor),
   Bilgilendirme (turuncu). Renkler birbirine karışmaz.
   ============================================================ */
/* Page header grup butonları (yeşil) */
.reklam-quickactions { display: flex; gap: 8px; justify-content: flex-end; align-items: center; flex-wrap: wrap; }
.btn-reklam-grup-gor,
.btn-reklam-grup-yeni {
   display: inline-flex; align-items: center; gap: 7px;
   padding: 9px 18px; border-radius: 999px;
   font-weight: 600; font-size: 13px;
   cursor: pointer; transition: all .15s ease;
   border: none; line-height: 1;
}
.btn-reklam-grup-gor {
   background: #fff; color: #059669;
   border: 2px solid #10b981;
}
.btn-reklam-grup-gor:hover {
   background: #ecfdf5; color: #047857;
   border-color: #059669; transform: translateY(-1px);
}
.btn-reklam-grup-yeni {
   background: linear-gradient(135deg, #10b981 0%, #059669 100%);
   color: #fff;
   box-shadow: 0 6px 16px rgba(5,150,105,.28);
}
.btn-reklam-grup-yeni:hover {
   color: #fff;
   box-shadow: 0 10px 22px rgba(5,150,105,.36);
   transform: translateY(-1px);
}
.btn-reklam-grup-gor i, .btn-reklam-grup-yeni i { font-size: 12px; }

/* Eski btn-reklam-primary (Empty state CTA için) — mor gradient */
.btn-reklam-primary {
   background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
   color: #fff !important; border: none; border-radius: 999px;
   padding: 10px 20px; font-weight: 600; font-size: 13px;
   box-shadow: 0 6px 18px rgba(123,47,184,.28);
   transition: transform .15s ease, box-shadow .15s ease;
   display: inline-flex; align-items: center; gap: 8px;
}
.btn-reklam-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(123,47,184,.34); color: #fff; }

/* HERO içinde sağda büyük "Yeni Reklam Oluştur" CTA */
.btn-reklam-yeni-cta {
   margin-left: auto; flex-shrink: 0;
   display: inline-flex; align-items: center; gap: 12px;
   padding: 14px 22px; border-radius: 16px;
   background: linear-gradient(135deg, #10b981 0%, #059669 100%);
   color: #fff !important; text-decoration: none !important;
   font-size: 14px; font-weight: 600;
   box-shadow: 0 12px 26px rgba(5,150,105,.35),
               inset 0 1px 0 rgba(255,255,255,.2);
   transition: all .18s ease;
   border: 2px solid rgba(255,255,255,.18);
}
.btn-reklam-yeni-cta:hover {
   transform: translateY(-2px) scale(1.02);
   box-shadow: 0 16px 32px rgba(5,150,105,.45),
               inset 0 1px 0 rgba(255,255,255,.3);
   color: #fff !important;
}
.btn-reklam-yeni-cta i {
   font-size: 30px;
   text-shadow: 0 2px 6px rgba(0,0,0,.2);
}
.btn-reklam-yeni-cta span { line-height: 1.15; text-align: left; }
.btn-reklam-yeni-cta b { font-size: 17px; font-weight: 800; }
.reklam-hero-title { display: flex; align-items: center; gap: 18px; margin-bottom: 22px; flex-wrap: wrap; }
.reklam-hero-title-text { flex: 1; min-width: 240px; }

/* Eski yenieklebuton mobile sabit konumlanmasını sıfırla (kullanılmıyor artık ama emin olalım) */
.reklam-hero .yenieklebuton, .page-header .yenieklebuton { position: static !important; width: auto !important; }

/* HERO */
.reklam-hero { position: relative; border-radius: 18px; overflow: hidden; margin-bottom: 22px; }
.reklam-hero-bg {
   position: absolute; inset: 0;
   background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
   z-index: 0;
}
.reklam-hero-bg::after {
   content:""; position:absolute; inset:0;
   background:
      radial-gradient(circle at 12% 20%, rgba(255,255,255,.18), transparent 35%),
      radial-gradient(circle at 88% 80%, rgba(255,255,255,.12), transparent 40%);
}
.reklam-hero-inner { position: relative; z-index: 1; padding: 28px 28px 22px; color: #fff; }
.reklam-hero-title { display: flex; align-items: center; gap: 18px; margin-bottom: 22px; }
.reklam-hero-title-icon {
   width: 64px; height: 64px; border-radius: 18px;
   background: rgba(255,255,255,.18); backdrop-filter: blur(10px);
   display: flex; align-items: center; justify-content: center;
   font-size: 28px; color: #fff; flex-shrink: 0;
}
.reklam-hero-title h2 { color: #fff; font-weight: 700; margin: 0 0 4px; font-size: 26px; }
.reklam-hero-title p { color: rgba(255,255,255,.88); margin: 0; font-size: 13.5px; line-height: 1.55; max-width: 760px; }
.reklam-hero-title p b { color: #fff; }

.reklam-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.reklam-stat {
   background: rgba(255,255,255,.95); border-radius: 14px; padding: 14px 16px;
   display: flex; align-items: center; gap: 12px; cursor: pointer;
   transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
   border: 2px solid transparent;
}
.reklam-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(0,0,0,.18); }
.reklam-stat.is-active { background: #fff; border-color: rgba(255,255,255,.95); box-shadow: 0 14px 32px rgba(0,0,0,.22); }
.reklam-stat-ic {
   width: 44px; height: 44px; border-radius: 12px;
   display: flex; align-items: center; justify-content: center;
   font-size: 18px; flex-shrink: 0; color: #fff;
}
.reklam-stat-body { min-width: 0; }
.reklam-stat-num { font-size: 22px; font-weight: 800; line-height: 1; color: #1e293b; }
.reklam-stat-label { font-size: 12.5px; font-weight: 700; color: #1e293b; margin-top: 4px; }
.reklam-stat-sub { font-size: 11px; color: #64748b; margin-top: 2px; }

/* Kanal renkleri */
.reklam-stat--total .reklam-stat-ic { background: linear-gradient(135deg,#475569,#1e293b); }
.reklam-stat--sms   .reklam-stat-ic { background: linear-gradient(135deg,#06b6d4,#0284c7); }
.reklam-stat--call  .reklam-stat-ic { background: linear-gradient(135deg,#10b981,#059669); }
.reklam-stat--push  .reklam-stat-ic { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
.reklam-stat--info  .reklam-stat-ic { background: linear-gradient(135deg,#f59e0b,#d97706); }

/* TABLO KART */
.reklam-card { padding: 0 !important; border-radius: 16px; overflow: hidden; border: 1px solid #eef0f3; box-shadow: 0 4px 14px rgba(15,23,42,.04); }
.reklam-card-header { padding: 14px 18px 0; border-bottom: 1px solid #f1f5f9; background: #fafbfc; }
.reklam-card-body { padding: 18px; }

/* TABLAR */
.reklam-tabs { display: flex; flex-wrap: wrap; gap: 6px; }
.reklam-tab {
   border: 1px solid transparent; background: transparent;
   padding: 10px 16px; border-radius: 10px 10px 0 0;
   font-size: 13px; font-weight: 600; color: #64748b;
   display: inline-flex; align-items: center; gap: 8px;
   transition: all .15s ease; cursor: pointer; margin-bottom: -1px;
}
.reklam-tab:hover { background: #f1f5f9; color: #1e293b; }
.reklam-tab.is-active {
   background: #fff; color: #1e293b;
   border-color: #f1f5f9 #f1f5f9 #fff;
   box-shadow: 0 -2px 0 #5C008E inset;
}
.reklam-tab--sms.is-active   { box-shadow: 0 -2px 0 #0284c7 inset; }
.reklam-tab--call.is-active  { box-shadow: 0 -2px 0 #059669 inset; }
.reklam-tab--push.is-active  { box-shadow: 0 -2px 0 #6d28d9 inset; }
.reklam-tab--info.is-active  { box-shadow: 0 -2px 0 #d97706 inset; }
.reklam-tab i { font-size: 12px; }
.reklam-tab-count {
   display: inline-block; min-width: 22px; padding: 2px 7px;
   background: #e2e8f0; color: #475569;
   border-radius: 999px; font-size: 11px; font-weight: 700; margin-left: 4px;
}
.reklam-tab.is-active .reklam-tab-count { background: #f1f5f9; color: #1e293b; }
.reklam-tab--sms.is-active   .reklam-tab-count { background: #cffafe; color: #0e7490; }
.reklam-tab--call.is-active  .reklam-tab-count { background: #d1fae5; color: #065f46; }
.reklam-tab--push.is-active  .reklam-tab-count { background: #ede9fe; color: #5b21b6; }
.reklam-tab--info.is-active  .reklam-tab-count { background: #fef3c7; color: #92400e; }

/* TABLO */
.reklam-table thead th {
   background: #f8fafc; color: #475569; font-weight: 700;
   font-size: 12px; text-transform: uppercase; letter-spacing: .3px;
   border-bottom: 2px solid #e2e8f0; padding: 12px 14px;
}
.reklam-table tbody td { padding: 14px; vertical-align: middle; font-size: 13.5px; color: #1e293b; border-bottom: 1px solid #f1f5f9; }
.reklam-table tbody tr:hover { background: #fafbfc; }

/* Kanal pill (görev türü hücresi için) */
.kanal-pill {
   display: inline-flex; align-items: center; gap: 6px;
   padding: 4px 10px; border-radius: 999px;
   font-size: 11.5px; font-weight: 700;
   border: 1px solid;
}
.kanal-pill i { font-size: 11px; }
.kanal-pill--sms   { background:#ecfeff; color:#0e7490; border-color:#a5f3fc; }
.kanal-pill--call  { background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
.kanal-pill--push  { background:#f5f3ff; color:#5b21b6; border-color:#ddd6fe; }
.kanal-pill--info  { background:#fffbeb; color:#92400e; border-color:#fde68a; }
.kanal-pill--none  { background:#f1f5f9; color:#64748b; border-color:#e2e8f0; }

.musteri-pill, .indirim-pill {
   display: inline-block; padding: 3px 9px; border-radius: 999px;
   font-size: 11.5px; font-weight: 600;
   background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
}
.musteri-pill--erkek  { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.musteri-pill--kadin  { background:#fdf2f8; color:#be185d; border-color:#fbcfe8; }
.musteri-pill--tum    { background:#f1f5f9; color:#334155; border-color:#cbd5e1; }
.indirim-pill--yuzde  { background:#fff7ed; color:#c2410c; border-color:#fed7aa; }
.indirim-pill--xaly   { background:#eef2ff; color:#3730a3; border-color:#c7d2fe; }

/* Kanal vurgu çizgi (sol kenar) */
.reklam-table tbody tr.row-kanal-sms   { box-shadow: inset 3px 0 0 #06b6d4; }
.reklam-table tbody tr.row-kanal-call  { box-shadow: inset 3px 0 0 #10b981; }
.reklam-table tbody tr.row-kanal-push  { box-shadow: inset 3px 0 0 #8b5cf6; }
.reklam-table tbody tr.row-kanal-info  { box-shadow: inset 3px 0 0 #f59e0b; }

/* Empty state */
.reklam-empty { text-align: center; padding: 60px 20px; }
.reklam-empty-ic {
   width: 88px; height: 88px; border-radius: 50%;
   background: linear-gradient(135deg,#f1f5f9,#e2e8f0);
   color: #94a3b8; font-size: 36px;
   display: inline-flex; align-items: center; justify-content: center;
   margin-bottom: 18px;
}
.reklam-empty h4 { font-weight: 700; color: #1e293b; margin: 0 0 6px; }
.reklam-empty p  { color: #64748b; margin: 0 0 18px; }

/* DataTables wrapper rötuşu */
#kampanyayonetim_tablo_wrapper .dataTables_filter input { border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; }
#kampanyayonetim_tablo_wrapper .dataTables_length select { border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px 8px; }
#kampanyayonetim_tablo_wrapper .dataTables_paginate .paginate_button.current,
#kampanyayonetim_tablo_wrapper .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg,#5C008E,#7B2FB8) !important; color: #fff !important; border: none !important; border-radius: 6px;
}

/* RESPONSIVE */
@media (max-width: 1199px) { .reklam-stats { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) {
   .reklam-stats { grid-template-columns: repeat(2, 1fr); }
   .reklam-hero-inner { padding: 22px 18px 18px; }
   .reklam-hero-title h2 { font-size: 22px; }
   .reklam-hero-title-icon { width: 52px; height: 52px; font-size: 22px; }
   .reklam-quickactions { margin-top: 12px; justify-content: flex-start; }
   .reklam-tab { padding: 8px 12px; font-size: 12px; }
   .btn-reklam-yeni-cta { margin-left: 0; width: 100%; justify-content: center; padding: 12px 18px; }
   .btn-reklam-yeni-cta i { font-size: 24px; }
   .btn-reklam-yeni-cta b { font-size: 15px; }
}
@media (max-width: 480px) {
   .reklam-stats { grid-template-columns: 1fr; }
}
</style>

<script>
(function(){
   // Tab + stat kart filtreleri DataTable'a bağlanacak.
   // Tablo init "frontendscripts/layout_isletmeadmin.blade.php"'de yapılıyor — biraz beklemek gerekiyor.
   function bindFilters(){
      if(!window.jQuery || !$.fn.dataTable || !$('#kampanyayonetim_tablo').length) return false;
      var tbl = $.fn.dataTable.isDataTable('#kampanyayonetim_tablo')
                   ? $('#kampanyayonetim_tablo').DataTable() : null;
      if(!tbl) return false;

      function uygula(kanal){
         // 0. kolon = Kanal (gorev_turu). Boşsa filtre temizle.
         if(!kanal) tbl.column(0).search('').draw();
         else tbl.column(0).search(kanal, true, false).draw();
      }

      $(document).on('click','.reklam-tab',function(){
         var k = $(this).data('kanal') || '';
         $('.reklam-tab').removeClass('is-active');
         $(this).addClass('is-active');
         $('.reklam-stat').removeClass('is-active');
         $('.reklam-stat[data-kanal="'+k+'"]').addClass('is-active');
         uygula(k);
      });
      $(document).on('click','.reklam-stat',function(){
         var k = $(this).data('kanal') || '';
         $('.reklam-stat').removeClass('is-active');
         $(this).addClass('is-active');
         $('.reklam-tab').removeClass('is-active');
         $('.reklam-tab[data-kanal="'+k+'"]').addClass('is-active');
         uygula(k);
      });
      return true;
   }
   var tries = 0;
   var iv = setInterval(function(){
      if(bindFilters() || ++tries > 30) clearInterval(iv);
   }, 200);
})();
</script>
    <!--grup sms ekle -->
<div id="grup_sms_liste_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style=" width: 100%;">
              <div class="modal-header bg-soft-primary">
                  
                  <div class="d-flex align-items-center w-100">
                        <div class="modal-icon mr-3 bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa fa-users text-white" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title mb-0 font-weight-600" id="grupSMSModalLabel">Gruplar</h5>
                            <p class="text-muted mb-0 small">Oluşturduğunuz grupları görüntüleyebilir ve düzenleyebilirsiniz</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
               </div>
               <div class="modal-body py-4">
                     <table class="data-table table stripe hover nowrap" id="grup_sms_tablo">
                     <thead>
                       <tr>
                         <th>Grup Adı </th>
                         <th>Müşteri Sayısı</th>
                         <th></th>
                       </tr>
                     </thead>
                     <tbody>
                      
                     </tbody>
                   </table>
               </div>

        </div>
    </div>
</div>


<div id="grup_sms_olustur_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="grupSMSModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="margin-left: 15%; width: 70%;">
            <form id="grup_sms_formu" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="sube" value="{{$isletme->id}}">
                <input type="hidden" name="grup_id">
                
                <!-- Modal Header -->
                <div class="modal-header bg-soft-primary">
                    <div class="d-flex align-items-center w-100">
                        <div class="modal-icon mr-3 bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fa fa-users text-white" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="modal-title mb-0 font-weight-600" id="grupSMSModalLabel">Yeni Grup Oluştur</h5>
                            <p class="text-muted mb-0 small">Müşteri grubu oluşturarak toplu SMS gönderebilirsiniz</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body py-4">
                    <!-- Grup Adı ve Müşteri Arama (Yan Yana) -->
                    <div class="form-section">
                        <div class="section-header mb-3">
                            <h6 class="section-title text-primary mb-0">
                                <i class="fa fa-info-circle mr-2"></i>Grup Bilgileri ve Müşteri Seçimi
                            </h6>
                           
                        </div>
                        
                        <div class="row">
                            <!-- Grup Adı Alanı -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grup_adi" class="form-label font-weight-600">
                                        Grup Adı <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-soft-primary border-primary">
                                                <i class="fa fa-tag"></i>
                                            </span>
                                        </div>
                                        <input type="text" 
                                               class="form-control border-primary" 
                                               id="grup_adi" 
                                               name="grup_adi"
                                               placeholder="Örn: Sadık Müşteriler, Özel Kampanya Grubu"
                                               required>
                                    </div>
                                    <small class="form-text text-muted">Grubu kolayca tanımlayabileceğiniz bir isim verin</small>
                                </div>
                            </div>
                            
                            <!-- Müşteri Arama Alanı -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="musteriarama_grupsms" class="form-label font-weight-600">
                                        Müşteri Ara
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-soft-info border-info">
                                                <i class="fa fa-search"></i>
                                            </span>
                                        </div>
                                        <input type="text" 
                                               id="musteriarama_grupsms" 
                                               name="musteriarama_grupsms" 
                                               class="form-control border-info" 
                                               placeholder="İsim ile müşteri arayın...">
                                    </div>
                                    <small class="form-text text-muted">Müşteriler arasında hızlı arama yapın</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Müşteri Listesi -->
                    <div class="form-section">
                        <div class="card border-soft">
                            <div class="card-header bg-soft-light py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-600">Müşteri Listesi</h6>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <!-- Yükleme Göstergesi -->
                                <div class="loading text-center py-5" id="musteriYukleniyor" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Yükleniyor...</span>
                                    </div>
                                </div>
                                
                                <!-- Müşteri Listesi Container -->
                                <div id="musteriListesiGrupSMS" style="max-height: 300px; overflow-y: auto; min-height: 200px;">
                                    <!-- İlk yükleme mesajı -->
                                    <div class="text-center py-5 text-muted" id="musteriListesiIlkMesaj">
                                        <i class="fa fa-user-friends fa-3x mb-3 opacity-50"></i>
                                        <p>Lütfen bekleyin, müşteriler yükleniyor...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seçili Müşteri Bilgisi -->
                            <div class="card-footer bg-soft-success py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-check-circle text-success mr-2"></i>
                                        <span class="font-weight-600" id="grupSMSSeciliMusteriler">0 müşteri seçildi</span>
                                    </div>
                                    <div class="text-muted small">
                                        <span id="gosterilenMusteriSayisi">0</span> / <span id="toplamMusteriSayisiFooter">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer bg-soft-light border-top">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa fa-save mr-2"></i>Grubu Kaydet
                            </button>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Grup SMS Düzenle Modalı -->
<div id="grup_sms_duzenle_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content" style="  margin-left:  15%;  width: 70%;">
         
         <!-- Modal Header -->
         <div class="modal-header border-bottom-0">
            <div class="d-flex align-items-center justify-content-between w-100">
               <div>
                  <h2 class="modal-title h4 font-weight-600 text-gray-800 mb-0">Grup Düzenle</h2>
                  <p class="text-muted small mb-0 mt-1">Grup bilgilerini düzenleyin ve katılımcıları yönetin</p>
               </div>
               <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
         </div>

         <!-- Modal Body -->
         <div class="modal-body p-0">
            <form id="grup_sms_formuDuzenle" method="POST">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="grup_id" id="grup_id_duzenle">
               
               <!-- Grup Bilgileri Kartları -->
               <div class="dashboard-cards p-4 border-bottom">
                  <div class="row">
                     <!-- Grup Adı Kartı -->
                     <div class="col-xl-6 col-lg-6 mb-3">
                        <div class="card summary-card">
                           <div class="card-body p-3">
                              <div class="d-flex align-items-center">
                                 <div class="icon-circle bg-blue-soft">
                                 <i class="fa fa-tasks text-blue"></i>
                                 </div>
                                 <div class="ml-3 flex-grow-1">
                                    <h6 class="card-subtitle text-muted small mb-1">Grup Adı</h6>
                                    <div class="form-group mb-0">
                                       <input type="text" 
                                              class="form-control form-control-sm border-0 p-0 h5 font-weight-600 text-gray-800" 
                                              id="grup_adi_duzenle" 
                                              name="grup_adi"
                                              placeholder="Grup adını girin..."
                                              style="background: transparent; box-shadow: none;"
                                              required>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <!-- Katılımcı Sayısı Kartı -->
                     <div class="col-xl-6 col-lg-6 mb-3">
                        <div class="card summary-card">
                           <div class="card-body p-3">
                              <div class="d-flex align-items-center">
                                 <div class="icon-circle bg-green-soft">
                                 <i class="fa fa-users text-orange"></i>
                                 </div>
                                 <div class="ml-3">
                                    <h6 class="card-subtitle text-muted small mb-1">Katılımcı Sayısı</h6>
                                    <p class="card-title h5 font-weight-600 mb-0 text-gray-800" id="grup_katilimci_sayisi">0</p>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Katılımcı Yönetimi -->
               <div class="p-4">
                  <div class="row">
                     <div class="col-md-12">
                        <!-- Katılımcı Ekleme Alanı -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                           <div class="d-flex align-items-center" style="min-width: 400px;">
                              <!-- Müşteri Seçimi -->
                              <div class="katilimci-select-container mr-2" style="flex: 1;">
                                 <select class="form-control form-control-sm katilimci-secim-select" id="katilimciSecimSelectDuzenle">
                                    <option value="">Müşteri seçin...</option>
                                 </select>
                              </div>
                              <button class="btn btn-primary btn-sm" id="katilimciEkleBtnDuzenle">
                                 <i class="fa fa-plus mr-1"></i> Katılımcı Ekle
                              </button>
                           </div>
                        </div>

                        <!-- Katılımcılar Tablosu -->
                        <div class="data-table-card">
                           <div class="card-header border-bottom bg-white">
                              <div class="d-flex justify-content-between align-items-center">
                                 <h4 class="h6 font-weight-600 text-gray-800 mb-0">Grup Katılımcıları</h4>
                                 <div class="d-flex align-items-center">
                                    <div class="search-box">
                                       <div class="input-group input-group-sm">
                                          <div class="input-group-prepend">
                                             <span class="input-group-text bg-white border-right-0">
                                                <i class="fa fa-search text-muted"></i>
                                             </span>
                                          </div>
                                          <input id="grupKatilimciArama" type="text" class="form-control border-left-0" placeholder="İsim veya telefon ara...">
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="table-responsive" id="grup_katilimcilari_liste" style="max-height: 400px;">
                              <table class="table table-hover mb-0" id="grup_katilimci_tablosu">
                                 <thead class="thead-light">
                                    <tr>
                                       <th width="45%">Ad Soyad</th>
                                       <th width="45%">Telefon</th>
                                       <th width="10%" class="text-center">İşlemler</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <!-- Grup katılımcıları buraya gelecek -->
                                 </tbody>
                              </table>
                              <div id="grup_katilimci_empty" class="empty-state">
                                 <div class="empty-state-icon">
                                    <i class="fa fa-users text-muted"></i>
                                 </div>
                                 <p class="empty-state-text">Grup katılımcısı bulunmamaktadır</p>
                              </div>
                           </div>
                           <div class="card-footer bg-white border-top py-3">
                              <div class="text-center">
                                 <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fa fa-save mr-2"></i> Grubu Güncelle
                                 </button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>

<!-- Katılımcı Silme Onay Modalı -->
<div class="modal fade delete-confirm-modal" id="katilimciSilOnayModal" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title text-danger">
               <i class="fa fa-exclamation-circle mr-2"></i>Katılımcı Silme
            </h5>
            <button type="button" class="close" data-dismiss="modal">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <p id="katilimciSilOnayMesaji" class="mb-4">Bu katılımcıyı gruptan çıkarmak istediğinizden emin misiniz?</p>
            <input type="hidden" id="silinecekKatilimciId">
            <input type="hidden" id="silinecekGrupId">
         </div>
         <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-light" data-dismiss="modal">
               İptal
            </button>
            <button type="button" class="btn btn-danger" id="katilimciSilOnayBtn">
               <i class="fa fa-trash mr-1"></i> Gruptan Çıkar
            </button>
         </div>
      </div>
   </div>
</div>

<style>
/* Grup Düzenle Modalı için ek stiller */
#grup_sms_duzenle_modal .modal-content {
   border: none;
   border-radius: var(--radius-lg);
   overflow: hidden;
   box-shadow: var(--shadow-lg);
}

#grup_sms_duzenle_modal .modal-header {
   background: white;
   padding: 1.5rem 1.5rem 0.5rem;
}

#grup_sms_duzenle_modal .modal-body {
   padding: 0;
}

#grup_sms_duzenle_modal .dashboard-cards {
   background: var(--gray-50);
   border-bottom: 1px solid var(--gray-200);
}

#grup_sms_duzenle_modal .summary-card {
   border: 1px solid var(--gray-200);
   border-radius: var(--radius-md);
   background: white;
   transition: all 0.3s ease;
   height: 100%;
   height: 90px;
}

#grup_sms_duzenle_modal .summary-card:hover {
   transform: translateY(-2px);
   box-shadow: var(--shadow-md);
   border-color: var(--primary);
}

#grup_sms_duzenle_modal .icon-circle {
   width: 48px;
   height: 48px;
   border-radius: 12px;
   display: flex;
   align-items: center;
   justify-content: center;
}

#grup_sms_duzenle_modal .bg-blue-soft { background-color: var(--primary-light); }
#grup_sms_duzenle_modal .bg-green-soft { background-color: var(--success-light); }

#grup_sms_duzenle_modal .text-blue { color: var(--primary); }
#grup_sms_duzenle_modal .text-green { color: var(--success); }

#grup_sms_duzenle_modal .form-control.border-0 {
   border: none !important;
   padding-left: 0;
   padding-right: 0;
}

#grup_sms_duzenle_modal .form-control.border-0:focus {
   box-shadow: none;
   border-bottom: 2px solid var(--primary) !important;
}

/* Katılımcı Yönetimi Alanı */
#grup_sms_duzenle_modal .data-table-card {
   border: 1px solid var(--gray-200);
   border-radius: var(--radius-md);
   overflow: hidden;
   background: white;
   margin-top: 1rem;
}

#grup_sms_duzenle_modal .data-table-card .card-header {
   padding: 1rem 1.5rem;
   background: white;
   border-bottom: 1px solid var(--gray-200);
}

#grup_sms_duzenle_modal .data-table-card .card-footer {
   padding: 1rem 1.5rem;
   background: var(--gray-50);
   border-top: 1px solid var(--gray-200);
}

#grup_sms_duzenle_modal .table-responsive {
   position: relative;
   scrollbar-width: thin;
   scrollbar-color: var(--gray-300) var(--gray-100);
}

#grup_sms_duzenle_modal .table-responsive::-webkit-scrollbar {
   width: 8px;
   height: 8px;
}

#grup_sms_duzenle_modal .table-responsive::-webkit-scrollbar-track {
   background: var(--gray-100);
   border-radius: 4px;
}

#grup_sms_duzenle_modal .table-responsive::-webkit-scrollbar-thumb {
   background: var(--gray-300);
   border-radius: 4px;
}

#grup_sms_duzenle_modal .table-responsive::-webkit-scrollbar-thumb:hover {
   background: var(--gray-400);
}

#grup_sms_duzenle_modal .table {
   margin-bottom: 0;
   border-collapse: separate;
   border-spacing: 0;
}

#grup_sms_duzenle_modal .table thead th {
   border: none;
   padding: 1rem 1.5rem;
   background: var(--gray-50);
   color: var(--gray-700);
   font-weight: 600;
   font-size: 0.875rem;
   text-transform: uppercase;
   letter-spacing: 0.5px;
   border-bottom: 2px solid var(--gray-200);
   position: sticky;
   top: 0;
   z-index: 10;
}

#grup_sms_duzenle_modal .table tbody td {
   padding: 1rem 1.5rem;
   border-bottom: 1px solid var(--gray-100);
   vertical-align: middle;
   font-size: 0.875rem;
   color: var(--gray-800);
}

#grup_sms_duzenle_modal .table tbody tr:last-child td {
   border-bottom: none;
}

#grup_sms_duzenle_modal .table tbody tr:hover {
   background-color: var(--gray-50);
}

/* Boş Durum */
#grup_sms_duzenle_modal .empty-state {
   display: none;
   padding: 3rem 1.5rem;
   text-align: center;
   background: white;
}

#grup_sms_duzenle_modal .empty-state-icon {
   font-size: 3rem;
   color: var(--gray-300);
   margin-bottom: 1rem;
}

#grup_sms_duzenle_modal .empty-state-text {
   color: var(--gray-400);
   font-size: 0.875rem;
   margin: 0;
}

/* Arama Kutusu (Sağ Üst Köşe) */
#grup_sms_duzenle_modal .search-box {
   min-width: 250px;
}

#grup_sms_duzenle_modal .search-box .input-group {
   border: 1px solid var(--gray-300);
   border-radius: var(--radius-sm);
   overflow: hidden;
}

#grup_sms_duzenle_modal .search-box .input-group:focus-within {
   border-color: var(--primary);
   box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.15);
}

#grup_sms_duzenle_modal .search-box .form-control {
   border: none;
   background: white;
   font-size: 0.875rem;
   padding: 0.5rem 0.75rem;
}

#grup_sms_duzenle_modal .search-box .form-control:focus {
   box-shadow: none;
}

#grup_sms_duzenle_modal .search-box .input-group-text {
   border: none;
   background: white;
   color: var(--gray-400);
}

/* Katılımcı Ekleme Alanı */
#grup_sms_duzenle_modal .katilimci-select-container {
   flex: 1;
}

#grup_sms_duzenle_modal .katilimci-secim-select {
   height: 36px;
   font-size: 0.875rem;
   border-radius: var(--radius-sm);
   border: 1px solid var(--gray-300);
   transition: all 0.2s ease;
}

#grup_sms_duzenle_modal .katilimci-secim-select:focus {
   border-color: var(--primary);
   box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.15);
}

/* Butonlar */
#grup_sms_duzenle_modal .btn-primary {
   background: var(--primary);
   border-color: var(--primary);
   padding: 0.5rem 1.25rem;
   border-radius: var(--radius-sm);
   font-weight: 500;
   height: 36px;
   display: inline-flex;
   align-items: center;
   justify-content: center;
}

#grup_sms_duzenle_modal .btn-primary:hover {
   background: #c53030;
   border-color: #c53030;
}

#grup_sms_duzenle_modal .btn-success {
   background: var(--success);
   border-color: var(--success);
   padding: 0.75rem 2rem;
   border-radius: var(--radius-md);
   font-weight: 600;
   font-size: 1rem;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   transition: all 0.3s ease;
}

#grup_sms_duzenle_modal .btn-success:hover {
   background: #2f855a;
   border-color: #2f855a;
   transform: translateY(-1px);
   box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

#grup_sms_duzenle_modal .btn-success:active {
   transform: translateY(0);
}

/* Silme Butonu */
#grup_sms_duzenle_modal .btn-delete {
   background: transparent;
   border: none;
   color: var(--danger);
   padding: 0.5rem 1rem;
   border-radius: var(--radius-sm);
   transition: all 0.2s ease;
   font-size: 0.875rem;
   display: inline-flex;
   align-items: center;
   justify-content: center;
}

#grup_sms_duzenle_modal .btn-delete:hover {
   background: var(--danger-light);
   color: #c53030;
   transform: scale(1.1);
}

/* Select2 için ek stiller */
#grup_sms_duzenle_modal .select2-container--default .select2-selection--single {
   height: 36px;
   border: 1px solid var(--gray-300);
   border-radius: var(--radius-sm);
   background: white;
}

#grup_sms_duzenle_modal .select2-container--default .select2-selection--single .select2-selection__rendered {
   line-height: 36px;
   color: var(--gray-800);
   font-size: 0.875rem;
   padding-left: 10px;
}

#grup_sms_duzenle_modal .select2-container--default .select2-selection--single .select2-selection__arrow {
   height: 36px;
}

#grup_sms_duzenle_modal .select2-container--default .select2-results__option--highlighted[aria-selected] {
   background-color: var(--primary-light);
   color: var(--primary);
}

#grup_sms_duzenle_modal .select2-container--default .select2-dropdown {
   border: 1px solid var(--gray-300);
   border-radius: var(--radius-sm);
   box-shadow: var(--shadow-md);
}

/* Silme Onay Modalı Stilleri */
#katilimciSilOnayModal .modal-content {
   border: none;
   border-radius: var(--radius-md);
   box-shadow: var(--shadow-lg);
}

#katilimciSilOnayModal .modal-header {
   border-bottom: 1px solid var(--danger-light);
   background: var(--danger-light);
}

#katilimciSilOnayModal .modal-footer {
   border-top: 1px solid var(--gray-200);
}

/* Responsive Tasarım */
@media (max-width: 1200px) {
   #grup_sms_duzenle_modal .katilimci-ekle-alani {
      min-width: 400px;
   }
}

@media (max-width: 992px) {
   #grup_sms_duzenle_modal .modal-dialog {
      margin: 1rem;
   }
   
   #grup_sms_duzenle_modal .katilimci-ekle-alani {
      min-width: 100%;
      justify-content: space-between;
   }
   
   #grup_sms_duzenle_modal .katilimci-select-container {
      margin-right: 10px;
   }
}

@media (max-width: 768px) {
   #grup_sms_duzenle_modal .modal-header {
      padding: 1rem 1rem 0.5rem;
   }
   
   #grup_sms_duzenle_modal .dashboard-cards {
      padding: 1rem;
   }
   
   #grup_sms_duzenle_modal .summary-card {
      height: auto;
      min-height: 90px;
   }
   
   #grup_sms_duzenle_modal .data-table-card .card-header {
      flex-direction: column;
      align-items: flex-start !important;
   }
   
   #grup_sms_duzenle_modal .data-table-card .card-header h4 {
      margin-bottom: 1rem;
   }
   
   #grup_sms_duzenle_modal .search-box {
      min-width: 100%;
      margin-top: 0.5rem;
   }
   
   #grup_sms_duzenle_modal .katilimci-ekle-alani {
      flex-direction: column;
   }
   
   #grup_sms_duzenle_modal .katilimci-select-container {
      margin-right: 0;
      margin-bottom: 10px;
      width: 100%;
   }
   
   #grup_sms_duzenle_modal #katilimciEkleBtnDuzenle {
      width: 100%;
      margin-bottom: 10px;
   }
   
   #grup_sms_duzenle_modal .table thead th,
   #grup_sms_duzenle_modal .table tbody td {
      padding: 0.75rem 1rem;
   }
   
   #grup_sms_duzenle_modal .btn-success {
      padding: 0.6rem 1.5rem;
      font-size: 0.95rem;
      width: 100%;
   }
}

@media (max-width: 576px) {
   #grup_sms_duzenle_modal .modal-dialog {
      margin: 0.5rem;
   }
   
   #grup_sms_duzenle_modal .modal-content {
      border-radius: var(--radius-md);
   }
   
   #grup_sms_duzenle_modal .table-responsive {
      font-size: 0.8125rem;
   }
   
   #grup_sms_duzenle_modal .table thead th,
   #grup_sms_duzenle_modal .table tbody td {
      padding: 0.5rem 0.75rem;
   }
   
   #grup_sms_duzenle_modal .empty-state {
      padding: 2rem 1rem;
   }
   
   #grup_sms_duzenle_modal .empty-state-icon {
      font-size: 2rem;
   }
   
   #grup_sms_duzenle_modal .dashboard-cards .row {
      margin-left: -5px;
      margin-right: -5px;
   }
   
   #grup_sms_duzenle_modal .dashboard-cards .col-xl-6 {
      padding-left: 5px;
      padding-right: 5px;
   }
}

/* Animasyonlar */
#grup_sms_duzenle_modal .fade {
   transition: opacity 0.15s linear;
}

#grup_sms_duzenle_modal .modal.fade .modal-dialog {
   transition: transform 0.3s ease-out;
   transform: translateY(-50px);
}

#grup_sms_duzenle_modal .modal.show .modal-dialog {
   transform: none;
}

/* Accessibility */
#grup_sms_duzenle_modal .btn:focus,
#grup_sms_duzenle_modal .form-control:focus {
   outline: none;
   box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.25);
}
</style>





<style>
/* Özel Stiller */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.modal-header.bg-soft-primary {
    background-color: rgba(94, 114, 228, 0.1);
    border-bottom: 1px solid rgba(94, 114, 228, 0.2);
    border-radius: 12px 12px 0 0;
    padding: 1.25rem 1.5rem;
}

.modal-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-primary {
    background-color: rgba(94, 114, 228, 0.1) !important;
}

.bg-soft-info {
    background-color: rgba(23, 162, 184, 0.1) !important;
}

.bg-soft-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-soft-light {
    background-color: rgba(248, 249, 250, 0.8) !important;
}

.border-primary {
    border-color: #5e72e4 !important;
}

.border-info {
    border-color: #17a2b8 !important;
}

.border-soft {
    border-color: #e9ecef !important;
}

.form-section {
    margin-bottom: 0rem;
}

.section-header {
    position: relative;
}

.section-divider {
    height: 2px;
    background: linear-gradient(90deg, #5e72e4, transparent);
    margin-top: 5px;
}

.form-label {
    font-size: 0.875rem;
    color: #525f7f;
    margin-bottom: 0.5rem;
}

.input-group-text {
    transition: all 0.3s ease;
}

.input-group:focus-within .input-group-text {
    background-color: #5e72e4;
    color: white;
}

#musteriListesiGrupSMS {
    scrollbar-width: thin;
    scrollbar-color: #c1c9d4 transparent;
}

#musteriListesiGrupSMS::-webkit-scrollbar {
    width: 6px;
}

#musteriListesiGrupSMS::-webkit-scrollbar-track {
    background: #f8f9fa;
}

#musteriListesiGrupSMS::-webkit-scrollbar-thumb {
    background-color: #c1c9d4;
    border-radius: 3px;
}

/* Müşteri kartı stilleri */
.musteri-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f1f1f1;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.musteri-item:hover {
    background-color: rgba(94, 114, 228, 0.05);
}

.musteri-item.selected {
    background-color: rgba(40, 167, 69, 0.1);
    border-left: 3px solid #28a745;
}

.musteri-info {
    flex: 1;
}

.musteri-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #5e72e4;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-weight: 600;
}

.musteri-ad {
    font-weight: 600;
    color: #32325d;
    margin-bottom: 2px;
}

.musteri-detay {
    font-size: 0.85rem;
    color: #6c757d;
}

.checkbox-wrapper {
    width: 20px;
    height: 20px;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
}

/* Responsive ayarlamalar */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .btn-lg {
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
    }
    
    /* Mobilde grup adı ve arama alanları alt alta */
    .row .col-md-6 {
        width: 100%;
        margin-bottom: 1rem;
    }
}

@media (max-width: 576px) {
    .modal-header .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .modal-icon {
        margin-bottom: 10px;
        margin-right: 0;
    }
    
    .modal-footer .row {
        flex-direction: column;
    }
    
    .modal-footer .col-md-2,
    .modal-footer .col-md-8 {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>

@endsection