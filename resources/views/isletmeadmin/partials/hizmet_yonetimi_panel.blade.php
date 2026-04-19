{{-- Hizmet Yönetimi paneli — Ayarlar > Hizmetler sekmesinde kullanılır --}}
<style>
:root {
   --hy-primary: #6366f1;
   --hy-primary-dark: #4f46e5;
   --hy-primary-light: #eef2ff;
   --hy-success: #10b981;
   --hy-success-light: #d1fae5;
   --hy-danger: #ef4444;
   --hy-danger-light: #fee2e2;
   --hy-warning: #f59e0b;
   --hy-gray-50: #f9fafb;
   --hy-gray-100: #f3f4f6;
   --hy-gray-200: #e5e7eb;
   --hy-gray-300: #d1d5db;
   --hy-gray-500: #6b7280;
   --hy-gray-700: #374151;
   --hy-gray-900: #111827;
   --hy-radius: 12px;
   --hy-radius-sm: 8px;
   --hy-shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
   --hy-shadow: 0 4px 20px rgba(99,102,241,0.08);
   --hy-shadow-lg: 0 10px 40px rgba(99,102,241,0.15);
}
.hizmet-yonetim-wrapper { padding: 0; }
.hizmet-yonetim-wrapper * { box-sizing: border-box; }

.hy-hero {
   background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
   border-radius: var(--hy-radius);
   padding: 24px 28px;
   color: #fff;
   margin-bottom: 20px;
   box-shadow: var(--hy-shadow-lg);
   position: relative;
   overflow: hidden;
}
.hy-hero::before {
   content:''; position:absolute; top:-60px; right:-60px;
   width:220px; height:220px; border-radius:50%;
   background: rgba(255,255,255,0.08);
}
.hy-hero::after {
   content:''; position:absolute; bottom:-80px; right:100px;
   width:160px; height:160px; border-radius:50%;
   background: rgba(255,255,255,0.06);
}
.hy-hero-inner { position:relative; z-index:2; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; }
.hy-hero-title { display:flex; align-items:center; gap:14px; }
.hy-hero-icon { width:52px; height:52px; border-radius:14px; background: rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:24px; backdrop-filter: blur(10px); flex-shrink:0; }
.hy-hero h1 { margin:0 0 4px 0; font-size:22px; font-weight:700; letter-spacing:-0.5px; color:#fff; }
.hy-hero p { margin:0; font-size:13px; opacity:0.9; color:#fff; }
.hy-hero-stats { display:flex; gap:12px; flex-wrap:wrap; }
.hy-stat { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 10px; padding: 10px 16px; min-width: 100px; border: 1px solid rgba(255,255,255,0.2); }
.hy-stat-label { font-size:11px; text-transform:uppercase; letter-spacing:0.5px; opacity:0.85; }
.hy-stat-value { font-size:20px; font-weight:700; margin-top:2px; }

.hy-toolbar { background:#fff; border-radius: var(--hy-radius); padding: 14px; margin-bottom: 18px; box-shadow: var(--hy-shadow-sm); border: 1px solid var(--hy-gray-200); display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.hy-search-box { position:relative; flex:1; min-width:200px; }
.hy-search-box input { padding:11px 14px 11px 40px; border:2px solid var(--hy-gray-200); border-radius: var(--hy-radius-sm); width:100%; font-size:14px; transition: all 0.2s; background: var(--hy-gray-50); }
.hy-search-box input:focus { outline:none; border-color: var(--hy-primary); background:#fff; box-shadow: 0 0 0 4px var(--hy-primary-light); }
.hy-search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--hy-gray-500); font-size:14px; }

.hy-btn { padding: 10px 16px; border-radius: var(--hy-radius-sm); font-size: 14px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; text-decoration: none; }
.hy-btn i { font-size:13px; }
.hy-btn-primary { background: var(--hy-primary); color:#fff; }
.hy-btn-primary:hover { background: var(--hy-primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.3); color:#fff; }
.hy-btn-success { background: var(--hy-success); color:#fff; }
.hy-btn-success:hover { background:#059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.3); color:#fff; }
.hy-btn-ghost { background: var(--hy-gray-100); color: var(--hy-gray-700); }
.hy-btn-ghost:hover { background: var(--hy-gray-200); color: var(--hy-gray-900); }
.hy-btn:disabled { opacity:0.6; cursor: not-allowed; transform:none !important; }

.hy-kategori-card { background:#fff; border-radius: var(--hy-radius); margin-bottom: 14px; overflow: hidden; box-shadow: var(--hy-shadow-sm); border: 1px solid var(--hy-gray-200); transition: all 0.25s; }
.hy-kategori-card:hover { box-shadow: var(--hy-shadow); }
.hy-kategori-header { padding: 16px 20px; background: linear-gradient(135deg, #fafbff 0%, #f4f6ff 100%); border-bottom: 1px solid var(--hy-gray-200); display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; transition: background 0.2s; }
.hy-kategori-header:hover { background: linear-gradient(135deg, #f4f6ff 0%, #eef2ff 100%); }
.hy-kategori-header-left { display:flex; align-items:center; gap:12px; }
.hy-kategori-icon { width: 38px; height: 38px; border-radius: 10px; background: var(--hy-primary-light); color: var(--hy-primary); display:flex; align-items:center; justify-content:center; font-size: 17px; }
.hy-kategori-header h3 { margin:0; font-size: 15px; color: var(--hy-gray-900); font-weight: 600; letter-spacing:-0.2px; }
.hy-kategori-header .hy-badge { background: var(--hy-primary); color:#fff; padding: 3px 11px; border-radius: 20px; font-size: 11px; margin-left: 10px; font-weight:600; }
.hy-kategori-chevron { color: var(--hy-gray-500); transition: transform 0.25s; font-size:12px; }
.hy-kategori-card.collapsed .hy-kategori-chevron { transform: rotate(-90deg); }
.hy-kategori-body { padding: 0; }

.hy-hizmet-header-row { display: grid; grid-template-columns: 2.5fr 1fr 1fr 2fr 110px; padding: 11px 20px; background: var(--hy-gray-50); border-bottom: 1px solid var(--hy-gray-200); font-size: 11px; font-weight: 700; color: var(--hy-gray-500); text-transform: uppercase; letter-spacing: 0.5px; gap: 12px; }
.hy-hizmet-row { display: grid; grid-template-columns: 2.5fr 1fr 1fr 2fr 110px; align-items: center; padding: 14px 20px; border-bottom: 1px solid var(--hy-gray-100); gap: 12px; transition: all 0.15s; }
.hy-hizmet-row:hover { background: var(--hy-gray-50); }
.hy-hizmet-row:last-child { border-bottom: none; }

.hy-hizmet-adi { font-weight: 600; color: var(--hy-gray-900); font-size: 14px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.hy-chip { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 8px; background: var(--hy-gray-100); color: var(--hy-gray-700); font-size: 12px; font-weight: 500; }
.hy-chip i { font-size:11px; }
.hy-chip-time { background:#fef3c7; color:#92400e; }
.hy-chip-price { background: var(--hy-success-light); color:#065f46; font-weight:700; font-size:13px; padding:4px 12px; }
.hy-chip-people { background: var(--hy-primary-light); color: var(--hy-primary-dark); }
.hy-chip-empty { background: var(--hy-danger-light); color:#991b1b; }

.hy-hizmet-personel { overflow:hidden; }
.hy-hizmet-personel .hy-chip { max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-flex; }

.hy-hizmet-islemler { display:flex; gap:8px; justify-content:flex-end; }
.hy-btn-icon { width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--hy-gray-200); background: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--hy-gray-500); transition: all 0.2s; font-size: 13px; }
.hy-btn-icon:hover { background: var(--hy-primary); color:#fff; border-color: var(--hy-primary); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(99,102,241,0.3); }
.hy-btn-icon.danger:hover { background: var(--hy-danger); border-color: var(--hy-danger); box-shadow: 0 4px 10px rgba(239,68,68,0.3); }

.hy-cinsiyet-badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
.hy-cinsiyet-0 { background:#fce7f3; color:#be185d; }
.hy-cinsiyet-1 { background:#dbeafe; color:#1d4ed8; }
.hy-cinsiyet-2 { background:#d1fae5; color:#047857; }

.hy-empty { padding: 60px 40px; text-align: center; color: var(--hy-gray-500); background:#fff; border-radius: var(--hy-radius); border: 2px dashed var(--hy-gray-200); }
.hy-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--hy-primary-light), #faf5ff); color: var(--hy-primary); display:inline-flex; align-items:center; justify-content:center; font-size: 34px; margin-bottom: 16px; }
.hy-empty h4 { color: var(--hy-gray-900); font-size:17px; margin-bottom:6px; }
.hy-empty p { font-size:13px; margin-bottom:0; }

.hy-mobile-card { display: none; padding: 14px 16px; border-bottom: 1px solid var(--hy-gray-100); gap: 10px; }
.hy-mobile-card:last-child { border-bottom:none; }
.hy-mobile-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.hy-mobile-title { flex:1; }
.hy-mobile-title h4 { margin:0 0 4px 0; font-size:14px; font-weight:600; color: var(--hy-gray-900); }
.hy-mobile-meta { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.hy-mobile-actions { display:flex; gap:6px; flex-shrink:0; }

@media (max-width: 991px) {
   .hy-hero { padding: 20px; }
   .hy-hero h1 { font-size:19px; }
   .hy-stat { min-width:85px; padding:9px 13px; }
   .hy-stat-value { font-size:17px; }
   .hy-hizmet-row, .hy-hizmet-header-row { grid-template-columns: 2fr 1fr 1fr 50px; }
   .hy-hizmet-header-row > div:nth-child(4),
   .hy-hizmet-row > div:nth-child(4) { display:none; }
   .hy-hizmet-islemler { gap:4px; }
}

@media (max-width: 767px) {
   .hy-hero { padding: 18px; border-radius:10px; }
   .hy-hero-inner { flex-direction:column; align-items:flex-start; }
   .hy-hero h1 { font-size:17px; }
   .hy-hero p { font-size:12px; }
   .hy-hero-icon { width:42px; height:42px; font-size:18px; border-radius:12px; }
   .hy-hero-stats { width:100%; }
   .hy-stat { flex:1; min-width:0; padding:9px 11px; }
   .hy-stat-value { font-size:15px; }
   .hy-stat-label { font-size:10px; }

   .hy-toolbar { padding:12px; gap:8px; }
   .hy-toolbar .hy-btn { padding: 10px 14px; font-size: 13px; flex: 1 1 calc(50% - 4px); justify-content: center; }
   .hy-search-box { flex: 1 1 100%; order:-1; }

   .hy-kategori-header { padding: 13px 14px; }
   .hy-kategori-icon { width:32px; height:32px; font-size:14px; border-radius:8px; }
   .hy-kategori-header h3 { font-size:14px; }

   .hy-hizmet-header-row { display:none; }
   .hy-hizmet-row { display:none; }
   .hy-mobile-card { display:flex; flex-direction:column; }

   .hy-empty { padding:45px 20px; }
   .hy-empty-icon { width:64px; height:64px; font-size:28px; }
}

@media (max-width: 420px) {
   .hy-toolbar .hy-btn { flex: 1 1 100%; }
}

@keyframes hyFadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
.hy-kategori-card { animation: hyFadeIn 0.3s ease-out; }

#hy_duzenle_modal .modal-content, #hy_kategori_ekle_modal .modal-content { border-radius: 16px; border:none; overflow:hidden; box-shadow: 0 25px 80px rgba(0,0,0,0.2); }
#hy_duzenle_modal .modal-header, #hy_kategori_ekle_modal .modal-header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color:#fff; border-bottom:none; padding: 18px 22px; }
#hy_duzenle_modal .modal-header h2, #hy_kategori_ekle_modal .modal-header h2 { color:#fff; font-size:17px; font-weight:700; margin:0; }
#hy_duzenle_modal .close, #hy_kategori_ekle_modal .close { color:#fff; opacity:0.9; font-size:26px; font-weight:300; }
.hy-modal-body { padding: 22px; }
.hy-modal-body label { font-weight:600; color: var(--hy-gray-700); font-size:13px; margin-bottom:6px; }
.hy-modal-body .form-control { border-radius: 10px; border: 2px solid var(--hy-gray-200); padding: 10px 14px; font-size: 14px; transition: all 0.2s; }
.hy-modal-body .form-control:focus { border-color: var(--hy-primary); box-shadow: 0 0 0 4px var(--hy-primary-light); }
</style>

<div class="hizmet-yonetim-wrapper">

   <div class="hy-hero">
      <div class="hy-hero-inner">
         <div class="hy-hero-title">
            <div class="hy-hero-icon"><i class="bi bi-scissors"></i></div>
            <div>
               <h1>Hizmet Yönetimi</h1>
               <p>İşletmenizde sunulan tüm hizmetleri kolayca yönetin.</p>
            </div>
         </div>
         <div class="hy-hero-stats">
            @php
               $toplam_hizmet = 0; foreach(($hizmet_gruplari ?? []) as $g) $toplam_hizmet += count($g);
               $toplam_kategori = count(array_filter(($hizmet_gruplari ?? []), function($g){return count($g)>0;}));
            @endphp
            <div class="hy-stat">
               <div class="hy-stat-label">Hizmet</div>
               <div class="hy-stat-value">{{$toplam_hizmet}}</div>
            </div>
            <div class="hy-stat">
               <div class="hy-stat-label">Kategori</div>
               <div class="hy-stat-value">{{$toplam_kategori}}</div>
            </div>
            <div class="hy-stat">
               <div class="hy-stat-label">Personel</div>
               <div class="hy-stat-value">{{$aktif_personel_sayisi ?? 0}}</div>
            </div>
         </div>
      </div>
   </div>

   <div class="hy-toolbar">
      <div class="hy-search-box">
         <i class="fa fa-search"></i>
         <input type="text" id="hy_hizmet_ara" placeholder="Hizmet ara..." autocomplete="off" />
      </div>
      <button type="button" class="hy-btn hy-btn-ghost" data-toggle="modal" data-target="#hy_kategori_ekle_modal">
         <i class="fa fa-folder"></i><span>Yeni Kategori</span>
      </button>
      <button type="button" class="hy-btn hy-btn-primary" data-toggle="modal" data-target="#hizmet_secimi_modal">
         <i class="fa fa-plus-circle"></i><span>Sistemden Ekle</span>
      </button>
      <button type="button" class="hy-btn hy-btn-success" data-toggle="modal" data-target="#yeni_hizmet_modal">
         <i class="fa fa-plus"></i><span>Yeni Hizmet</span>
      </button>
   </div>

   {{-- Gizli placeholder: custom.js icindeki $('#hizmet_liste').DataTable() cagrilarinin hata atmamasi icin --}}
   <table id="hizmet_liste" style="display:none;"><thead><tr><th></th><th></th><th></th></tr></thead><tbody></tbody></table>

   <div id="hy_kategori_liste">
      @if(!isset($hizmet_gruplari) || count($hizmet_gruplari) == 0)
         <div class="hy-empty">
            <div class="hy-empty-icon"><i class="bi bi-scissors"></i></div>
            <h4>Henüz hizmet eklenmemiş</h4>
            <p>İlk hizmetinizi eklemek için yukarıdaki "Yeni Hizmet" butonunu kullanabilirsiniz.</p>
         </div>
      @else
         @foreach($kategoriler as $kategori)
            @if(isset($hizmet_gruplari[$kategori->id]) && count($hizmet_gruplari[$kategori->id]) > 0)
            <div class="hy-kategori-card" data-kategori-id="{{$kategori->id}}">
               <div class="hy-kategori-header" data-toggle="collapse" data-target="#hy-kategori-body-{{$kategori->id}}">
                  <div class="hy-kategori-header-left">
                     <div class="hy-kategori-icon"><i class="fa fa-tag"></i></div>
                     <div><h3>{{$kategori->hizmet_kategorisi_adi}}<span class="hy-badge">{{count($hizmet_gruplari[$kategori->id])}}</span></h3></div>
                  </div>
                  <i class="fa fa-chevron-down hy-kategori-chevron"></i>
               </div>
               <div class="hy-kategori-body collapse show" id="hy-kategori-body-{{$kategori->id}}">
                  <div class="hy-hizmet-header-row">
                     <div>Hizmet Adı</div>
                     <div>Süre</div>
                     <div>Fiyat</div>
                     <div>Personel / Cihaz</div>
                     <div style="text-align:right;">İşlemler</div>
                  </div>

                  @foreach($hizmet_gruplari[$kategori->id] as $hizmet)
                     @php
                        $cinsiyet_html = '';
                        if($hizmet['cinsiyet'] === 0 || $hizmet['cinsiyet'] === '0') $cinsiyet_html = '<span class="hy-cinsiyet-badge hy-cinsiyet-0">Kadın</span>';
                        elseif($hizmet['cinsiyet'] === 1 || $hizmet['cinsiyet'] === '1') $cinsiyet_html = '<span class="hy-cinsiyet-badge hy-cinsiyet-1">Erkek</span>';
                        elseif($hizmet['cinsiyet'] === 2 || $hizmet['cinsiyet'] === '2') $cinsiyet_html = '<span class="hy-cinsiyet-badge hy-cinsiyet-2">Unisex</span>';
                     @endphp

                     <div class="hy-hizmet-row"
                          data-hizmet-id="{{$hizmet['hizmet_id']}}"
                          data-salon-hizmet-id="{{$hizmet['id']}}"
                          data-hizmet-adi="{{$hizmet['hizmet_adi']}}"
                          data-fiyat="{{$hizmet['fiyat']}}"
                          data-sure="{{$hizmet['sure_dk']}}"
                          data-kategori-id="{{$kategori->id}}"
                          data-cinsiyet="{{$hizmet['cinsiyet']}}">
                        <div class="hy-hizmet-adi">
                           <span>{{$hizmet['hizmet_adi']}}</span>{!!$cinsiyet_html!!}
                        </div>
                        <div><span class="hy-chip hy-chip-time"><i class="fa fa-clock-o"></i> {{$hizmet['sure_dk']}} dk</span></div>
                        <div><span class="hy-chip hy-chip-price">{{number_format($hizmet['fiyat'],2,',','.')}} ₺</span></div>
                        <div class="hy-hizmet-personel" title="{{$hizmet['personeller']}}">
                           @if($hizmet['personeller'] == '')
                              <span class="hy-chip hy-chip-empty"><i class="fa fa-exclamation-circle"></i> Atanmamış</span>
                           @else
                              <span class="hy-chip hy-chip-people"><i class="fa fa-user"></i> {{$hizmet['personeller']}}</span>
                           @endif
                        </div>
                        <div class="hy-hizmet-islemler">
                           <button type="button" class="hy-btn-icon hy-hizmet-duzenle" title="Düzenle"><i class="fa fa-pencil"></i></button>
                           <button type="button" class="hy-btn-icon danger hy-hizmet-sil" title="Sil" data-id="{{$hizmet['id']}}"><i class="fa fa-trash"></i></button>
                        </div>
                     </div>

                     <div class="hy-mobile-card"
                          data-hizmet-id="{{$hizmet['hizmet_id']}}"
                          data-salon-hizmet-id="{{$hizmet['id']}}"
                          data-hizmet-adi="{{$hizmet['hizmet_adi']}}"
                          data-fiyat="{{$hizmet['fiyat']}}"
                          data-sure="{{$hizmet['sure_dk']}}"
                          data-kategori-id="{{$kategori->id}}"
                          data-cinsiyet="{{$hizmet['cinsiyet']}}">
                        <div class="hy-mobile-top">
                           <div class="hy-mobile-title">
                              <h4>{{$hizmet['hizmet_adi']}} {!!$cinsiyet_html!!}</h4>
                              <div class="hy-mobile-meta">
                                 <span class="hy-chip hy-chip-time"><i class="fa fa-clock-o"></i> {{$hizmet['sure_dk']}} dk</span>
                                 <span class="hy-chip hy-chip-price">{{number_format($hizmet['fiyat'],2,',','.')}} ₺</span>
                              </div>
                           </div>
                           <div class="hy-mobile-actions">
                              <button type="button" class="hy-btn-icon hy-hizmet-duzenle" title="Düzenle"><i class="fa fa-pencil"></i></button>
                              <button type="button" class="hy-btn-icon danger hy-hizmet-sil" title="Sil" data-id="{{$hizmet['id']}}"><i class="fa fa-trash"></i></button>
                           </div>
                        </div>
                        <div>
                           @if($hizmet['personeller'] == '')
                              <span class="hy-chip hy-chip-empty"><i class="fa fa-exclamation-circle"></i> Personel atanmamış</span>
                           @else
                              <span class="hy-chip hy-chip-people"><i class="fa fa-user"></i> {{$hizmet['personeller']}}</span>
                           @endif
                        </div>
                     </div>
                  @endforeach
               </div>
            </div>
            @endif
         @endforeach
      @endif
   </div>
</div>

{{-- Hizmet Düzenleme Modal --}}
<div class="modal modal-top fade calendar-modal" id="hy_duzenle_modal">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
         <form id="hy_duzenle_formu">
            {!!csrf_field()!!}
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <input type="hidden" name="salon_hizmet_id" id="hy_edit_salon_hizmet_id">
            <div class="modal-header">
               <h2>Hizmet Düzenle</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body hy-modal-body">
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmet Adı</label>
                        <input type="text" name="hizmet_adi" id="hy_edit_hizmet_adi" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Süre (dk)</label>
                        <input type="number" name="sure_dk" id="hy_edit_sure_dk" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Fiyat (₺)</label>
                        <input type="number" step="0.01" name="fiyat" id="hy_edit_fiyat" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori_id" id="hy_edit_kategori_id" class="form-control">
                           @foreach($kategoriler as $cat)
                              <option value="{{$cat->id}}">{{$cat->hizmet_kategorisi_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Cinsiyet</label>
                        <select name="cinsiyet" id="hy_edit_cinsiyet" class="form-control">
                           <option value="">Belirtilmemiş</option>
                           <option value="0">Kadın</option>
                           <option value="1">Erkek</option>
                           <option value="2">Unisex</option>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Hizmeti Sunan Personeller & Cihazlar</label>
                        <select name="personel_ids[]" id="hy_edit_personeller" multiple class="form-control" style="width:100%">
                           @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',1)->get() as $personel)
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                           @foreach(\App\Cihazlar::where('salon_id',$isletme->id)->where('aktifmi',1)->get() as $cihaz)
                              <option value="cihaz-{{$cihaz->id}}">{{$cihaz->cihaz_adi}} (Cihaz)</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i> Kapat</button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>

{{-- Yeni Kategori Modal --}}
<div class="modal modal-top fade calendar-modal" id="hy_kategori_ekle_modal">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form id="hy_kategori_ekle_formu">
            {!!csrf_field()!!}
            <div class="modal-header">
               <h2>Yeni Kategori Ekle</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body hy-modal-body">
               <div class="form-group">
                  <label>Kategori Adı</label>
                  <input type="text" name="kategori_adi" required class="form-control" placeholder="Örn: Saç Bakımı">
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i> Kapat</button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
$(document).ready(function(){

   // --- Arama filtresi (desktop + mobile) ---
   $('#hy_hizmet_ara').on('keyup', function(){
      var q = $(this).val().toLowerCase();
      $('.hy-hizmet-row, .hy-mobile-card').each(function(){
         var ad = ($(this).data('hizmet-adi')||'').toString().toLowerCase();
         if(ad.indexOf(q) > -1) $(this).css('display',''); else $(this).hide();
      });
      $('.hy-kategori-card').each(function(){
         var gorunur = $(this).find('.hy-hizmet-row:visible, .hy-mobile-card:visible').length;
         if(gorunur > 0) $(this).show(); else $(this).hide();
      });
   });

   // --- Kategori collapse ---
   $(document).on('click', '.hy-kategori-header', function(){
      $(this).closest('.hy-kategori-card').toggleClass('collapsed');
   });

   // --- Düzenle ---
   $(document).on('click', '.hy-hizmet-duzenle', function(){
      var row = $(this).closest('.hy-hizmet-row, .hy-mobile-card');
      $('#hy_edit_salon_hizmet_id').val(row.data('salon-hizmet-id'));
      $('#hy_edit_hizmet_adi').val(row.data('hizmet-adi'));
      $('#hy_edit_fiyat').val(row.data('fiyat'));
      $('#hy_edit_sure_dk').val(row.data('sure'));
      $('#hy_edit_kategori_id').val(row.data('kategori-id')).trigger('change');
      var c = row.data('cinsiyet');
      $('#hy_edit_cinsiyet').val(c !== undefined && c !== null ? c : '').trigger('change');
      $('#hy_edit_personeller').val(null).trigger('change');

      var hizmetId = row.data('hizmet-id');
      $.ajax({
         type: 'GET',
         url: '/isletmeyonetim/hizmetpersonelsecimigetir',
         data: { 'salon_hizmetleri[]': [hizmetId], sube: {{$isletme->id}} },
         dataType: 'text',
         success: function(html){
            var $tmp = $('<div>').html(html);
            var checkedIds = [];
            $tmp.find('input[type=checkbox]:checked').each(function(){ checkedIds.push($(this).val()); });
            if(checkedIds.length > 0){
               $('#hy_edit_personeller').val(checkedIds).trigger('change');
            }
         }
      });
      $('#hy_duzenle_modal').modal('show');
   });

   // --- Düzenleme submit ---
   $('#hy_duzenle_formu').on('submit', function(e){
      e.preventDefault();
      var $btn = $(this).find('button[type=submit]');
      if($btn.prop('disabled')) return;
      $btn.prop('disabled', true);
      $.ajax({
         type: 'POST',
         url: '/isletmeyonetim/hizmet-yonetimi/guncelle',
         data: $(this).serialize(),
         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
         dataType: 'json',
         beforeSend: function(){ $('#preloader').show(); },
         success: function(result){
            $('#preloader').hide();
            $('#hy_duzenle_modal').modal('hide');
            swal({ type: result.status==='success'?'success':'error', title: result.status==='success'?'Başarılı':'Hata', text: result.message, showConfirmButton: false, timer: 1800 });
            if(result.status === 'success'){
               setTimeout(function(){ location.reload(); }, 1200);
            } else {
               $btn.prop('disabled', false);
            }
         },
         error: function(){
            $('#preloader').hide();
            $btn.prop('disabled', false);
            swal({ type:'error', title:'Hata', text:'İşlem sırasında bir hata oluştu', showConfirmButton:false, timer:2500 });
         }
      });
   });

   // --- Sil ---
   $(document).on('click', '.hy-hizmet-sil', function(){
      var id = $(this).data('id');
      var $rows = $('.hy-hizmet-row[data-salon-hizmet-id="'+id+'"], .hy-mobile-card[data-salon-hizmet-id="'+id+'"]');
      swal({
         title: 'Emin misiniz?', text: "Bu hizmet salonunuzdan kaldırılacak.", type: 'warning',
         showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6366f1',
         confirmButtonText: 'Evet, sil', cancelButtonText: 'İptal'
      }).then(function(result){
         if(result.value){
            $.ajax({
               type: 'POST', url: '/isletmeyonetim/salonhizmetsil',
               data: { sunulan_hizmet_id: id, sube: {{$isletme->id}}, _token: $('meta[name="csrf-token"]').attr('content') },
               dataType: 'json',
               beforeSend: function(){ $('#preloader').show(); },
               success: function(){
                  $('#preloader').hide();
                  $rows.fadeOut(300, function(){
                     $(this).remove();
                     $('.hy-kategori-card').each(function(){
                        if($(this).find('.hy-hizmet-row, .hy-mobile-card').length === 0) $(this).remove();
                     });
                  });
                  swal({ type:'success', title:'Silindi', text:'Hizmet kaldırıldı', showConfirmButton:false, timer:1600 });
               },
               error: function(){
                  $('#preloader').hide();
                  swal({ type:'error', title:'Hata', text:'İşlem sırasında hata oluştu', showConfirmButton:false, timer:2500 });
               }
            });
         }
      });
   });

   // --- Yeni kategori ekleme ---
   $('#hy_kategori_ekle_formu').on('submit', function(e){
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('button[type=submit]');
      if($btn.prop('disabled')) return;
      $btn.prop('disabled', true);
      $.ajax({
         type: 'POST', url: '/isletmeyonetim/hizmet-yonetimi/kategori-ekle',
         data: $form.serialize(),
         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
         dataType: 'json',
         beforeSend: function(){ $('#preloader').show(); },
         success: function(result){
            $('#preloader').hide();
            $btn.prop('disabled', false);
            $('#hy_kategori_ekle_modal').modal('hide');
            $form[0].reset();
            swal({ type:'success', title:'Başarılı', text: result.message, showConfirmButton:false, timer:1500 });
            if(result.status === 'success'){
               // Eklenen kategori: ilgili select'lere append ve select2 güncelle
               $('select[name="hizmet_kategorisi"], select[name="kategori_id"]').each(function(){
                  if($(this).find('option[value="'+result.kategori_id+'"]').length === 0){
                     $(this).append(new Option(result.kategori_adi, result.kategori_id, false, false));
                  }
                  if($(this).hasClass('select2-hidden-accessible')){
                     $(this).trigger('change.select2');
                  }
               });
            }
         },
         error: function(){
            $('#preloader').hide();
            $btn.prop('disabled', false);
            swal({ type:'error', title:'Hata', text:'Kategori eklenemedi', showConfirmButton:false, timer:2200 });
         }
      });
   });

   // --- Modal kapanışlarında form reset ve Select2 sıfırla ---
   $('#hy_kategori_ekle_modal').on('hidden.bs.modal', function(){
      var $f = $('#hy_kategori_ekle_formu');
      if($f.length) $f[0].reset();
      $f.find('button[type=submit]').prop('disabled', false);
   });
   $('#hy_duzenle_modal').on('hidden.bs.modal', function(){
      var $f = $('#hy_duzenle_formu');
      if($f.length) $f[0].reset();
      $f.find('button[type=submit]').prop('disabled', false);
      $('#hy_edit_personeller').val(null).trigger('change');
   });
   $('#yeni_hizmet_modal').on('hidden.bs.modal', function(){
      var $f = $('#yeni_hizmet_formu');
      if($f.length){
         $f[0].reset();
         $f.find('select').each(function(){
            $(this).val(null);
            if($(this).hasClass('select2-hidden-accessible')) $(this).trigger('change.select2');
         });
         $f.find('button[type=submit]').prop('disabled', false);
      }
   });
   $('#hizmet_secimi_modal').on('hidden.bs.modal', function(){
      var $f = $('#hizmet_ekle_formu');
      if($f.length){
         $f.find('input[type=checkbox]').prop('checked', false);
         $f.find('button[type=submit], button#hizmet_personel_ekleme_butonu').prop('disabled', false);
      }
      $('#hizmet_ara').val('');
      $('#secilmeyen_hizmetler_liste tr').show();
   });

   // --- Yeni hizmet / sistemden ekle / personel seçim: submit'de çift tıklamayı engelle + sayfayı yenile ---
   $(document).on('submit', '#yeni_hizmet_formu, #hizmet_personel_formu', function(){
      var $btn = $(this).find('button[type=submit]');
      if($btn.prop('disabled')) return false;
      $btn.prop('disabled', true);
      setTimeout(function(){ location.reload(); }, 1800);
   });
});
</script>
