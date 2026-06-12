@if(Auth::guard('satisortakligi')->check())
    @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp
@else
    @php $_layout = 'layout.layout_isletmeadmin'; @endphp
@endif

@extends($_layout)

@section('content')
<input type="hidden" id="satis_takibi_ekrani" value="1">

<div class="rc-adi-page">

   {{-- Modern Page Header --}}
   <div class="rc-adi-header">
      <div class="rc-adi-header-left">
         <div class="rc-adi-title-row">
            <div class="rc-adi-icon-bubble"><i class="fa fa-credit-card"></i></div>
            <div>
               <h1 class="rc-adi-title">{{$sayfa_baslik}}</h1>
               <nav class="rc-adi-breadcrumb" aria-label="breadcrumb">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  <span class="rc-adi-sep">›</span>
                  <span class="rc-adi-active">{{$sayfa_baslik}}</span>
               </nav>
            </div>
         </div>
      </div>
      <div class="rc-adi-header-right">
         @yetki('satis.tahsilat_al')
         <button type="button" data-toggle="modal" data-target="#harici_tahsilat_modal"
            class="rc-adi-btn" style="background:#4338ca;color:#fff">
            <i class="fa fa-cloud-download"></i><span>Harici Tahsilat</span>
         </button>
         <a href="/isletmeyonetim/yenitahsilat{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}"
            class="rc-adi-btn rc-adi-btn-success yenieklebuton">
            <i class="fa fa-plus"></i><span>Yeni Satış &amp; Tahsilat</span>
         </a>
         @endyetki
      </div>
   </div>

   {{-- Özet Stat Kartları --}}
   <div class="rc-adi-stats">
      <div class="rc-adi-stat rc-adi-stat-primary">
         <div class="rc-adi-stat-icon"><i class="fa fa-shopping-bag"></i></div>
         <div class="rc-adi-stat-body">
            <span class="rc-adi-stat-label">Toplam Satış</span>
            <span class="rc-adi-stat-value" id="toplamSatisTutar">0,00 ₺</span>
         </div>
      </div>
      <div class="rc-adi-stat rc-adi-stat-success">
         <div class="rc-adi-stat-icon"><i class="fa fa-check-circle"></i></div>
         <div class="rc-adi-stat-body">
            <span class="rc-adi-stat-label">Ödenen Toplam</span>
            <span class="rc-adi-stat-value" id="odenenToplamTutar">0,00 ₺</span>
         </div>
      </div>
      <div class="rc-adi-stat rc-adi-stat-danger">
         <div class="rc-adi-stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
         <div class="rc-adi-stat-body">
            <span class="rc-adi-stat-label">Kalan Toplam</span>
            <span class="rc-adi-stat-value" id="kalanToplamTutar">0,00 ₺</span>
         </div>
      </div>
   </div>

   {{-- Filtreler --}}
   <div class="rc-adi-card rc-adi-filters-card">
      <div class="rc-adi-filters">
         <div class="rc-adi-filter">
            <label>Zaman Aralığı</label>
            <select class="form-control" id="satis_zamana_gore_filtre">
               <option value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
               <option value="{{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('-1 days',strtotime(date('Y-m-d'))))}}">Dün</option>
               <option selected value="<?php echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
               <option value="<?php echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
               <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
               <option value="<?php echo date(date('Y',strtotime('-1 year')).'-01-01') . " / ". date(date('Y',strtotime('-1 year')).'-12-31'); ?>">Geçen yıl</option>
               <option value="ozel">Özel</option>
            </select>
         </div>
         <div class="rc-adi-filter" id="satis_zamana_gore_filtre1" style="display:none;">
            <label>Başlangıç Tarihi</label>
            <input class="form-control" placeholder="Başlangıç tarihini seçiniz.." type="text" id="satisbaslangictarihi" />
         </div>
         <div class="rc-adi-filter" id="satis_zamana_gore_filtre2" style="display:none;">
            <label>Bitiş Tarihi</label>
            <input class="form-control" placeholder="Bitiş tarihini seçiniz.." type="text" id="satisbitistarihi" />
         </div>
         <div class="rc-adi-filter">
            <label>Personele Göre Filtrele</label>
            <select class="form-control personel_secimi" id="satisPersonelFiltre" style="width:100%">
               <option></option>
            </select>
         </div>
         <div class="rc-adi-filter">
            <label>Satış Durumu</label>
            <select class="form-control" id="satis_durumu_filtre">
               <option value="" selected>Tüm Satışlar</option>
               <option value="acik">Açık Satışlar</option>
               <option value="kapali">Kapalı Satışlar</option>
               <option value="faturali">Faturalı İşlemler</option>
               <option value="faturasiz">Faturasız İşlemler</option>
            </select>
         </div>
         <div class="rc-adi-filter rc-adi-filter-extras">
            <label>&nbsp;</label>
            <div class="rc-adi-extras">
               <span id="satisDurumuSayilari" class="rc-adi-status-counts"></span>
               @if(DB::table('model_has_roles')->where('role_id',1)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 1)
               <button type="button" id="faturasizGizleSatisBtn"
                       data-aktif="{{ (int)($isletme->faturasiz_gizle ?? 0) }}"
                       class="btn btn-{{ (int)($isletme->faturasiz_gizle ?? 0) === 1 ? 'warning' : 'default' }} rc-adi-fatura-btn"
                       title="Faturasız İşlemleri Gizle/Göster">
                   <i class="fa fa-file-text-o"></i>
               </button>
               @endif
            </div>
         </div>
      </div>
   </div>

   {{-- Modern Tab Card --}}
   <div class="rc-adi-card">

      {{-- Ana Tabs --}}
      <div class="rc-adi-tabs-wrap">
         <ul class="nav nav-tabs rc-adi-tabs" role="tablist">
            <li class="nav-item">
               <button class="rc-adi-tab active" data-toggle="tab" href="#tum_adisyonlar" role="tab" aria-selected="true">
                  <i class="fa fa-th-large"></i><span>Tümü</span><span class="rc-adi-tab-count" id="satis_takibi_tumu_sayisi"></span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-adi-tab" data-toggle="tab" href="#hizmet_adisyonlar" role="tab" aria-selected="false">
                  <i class="fa fa-cogs"></i><span>Hizmetler/İşlemler</span><span class="rc-adi-tab-count" id="satis_takibi_islem_sayisi"></span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-adi-tab" data-toggle="tab" href="#ürün_adisyonlar" role="tab" aria-selected="false">
                  <i class="fa fa-cube"></i><span>Ürünler</span><span class="rc-adi-tab-count" id="satis_takibi_urun_sayisi"></span>
               </button>
            </li>
            <li class="nav-item">
               <button class="rc-adi-tab" data-toggle="tab" href="#paket_adisyonlar" role="tab" aria-selected="false">
                  <i class="fa fa-archive"></i><span>Paketler</span><span class="rc-adi-tab-count" id="satis_takibi_paket_sayisi"></span>
               </button>
            </li>
            @yetki('finans.alacak_yonet')
            <li class="nav-item">
               <button class="rc-adi-tab" data-toggle="tab" href="#taksitli_alacaklar" role="tab" aria-selected="false">
                  <i class="fa fa-credit-card-alt"></i><span>Taksitler</span>
               </button>
            </li>
            @endyetki
         </ul>
      </div>

      {{-- Tab Panes --}}
      <div class="tab-content rc-adi-tab-content">
         {{-- Tüm Adisyonlar Tabı --}}
         <div class="tab-pane fade show active" id="tum_adisyonlar" role="tab-panel">
            <div class="rc-adi-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-adi-table" id="adisyon_liste" style="width:100%">
                  <thead>
                     <tr>
                        <th>Durum</th>
                        <th>Satış Tarihi</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Yaklaşan Ödeme Tarihi</th>
                        <th>Adisyon İçeriği</th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th class="rc-adi-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         {{-- Hizmet Adisyonları Tabı --}}
         <div class="tab-pane fade show" id="hizmet_adisyonlar" role="tab-panel">
            <div class="rc-adi-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-adi-table" id="adisyon_liste_hizmet" style="width:100%">
                  <thead>
                     <tr>
                        <th>Durum</th>
                        <th>Satış Tarihi</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Yaklaşan Ödeme Tarihi</th>
                        <th>Adisyon İçeriği</th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th class="rc-adi-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         {{-- Ürün Adisyonları Tabı --}}
         <div class="tab-pane fade show" id="ürün_adisyonlar" role="tab-panel">
            <div class="rc-adi-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-adi-table" id="adisyon_liste_urun" style="width:100%">
                  <thead>
                     <tr>
                        <th>Durum</th>
                        <th>Satış Tarihi</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Yaklaşan Ödeme Tarihi</th>
                        <th>Adisyon İçeriği</th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th class="rc-adi-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         {{-- Paket Adisyonları Tabı --}}
         <div class="tab-pane fade show" id="paket_adisyonlar" role="tab-panel">
            <div class="rc-adi-tablo-wrap">
               <table class="data-table table stripe hover nowrap rc-adi-table" id="adisyon_liste_paket" style="width:100%">
                  <thead>
                     <tr>
                        <th>Durum</th>
                        <th>Satış Tarihi</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Yaklaşan Ödeme Tarihi</th>
                        <th>Adisyon İçeriği</th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th class="rc-adi-col-actions">İşlemler</th>
                     </tr>
                  </thead>
                  <tbody></tbody>
               </table>
            </div>
         </div>

         @yetki('finans.alacak_yonet')
         {{-- Taksitli Alacaklar Tabı --}}
         <div class="tab-pane fade show" id="taksitli_alacaklar" role="tab-panel">
            <div class="rc-adi-subtabs-wrap">
               <ul class="nav nav-tabs rc-adi-subtabs" role="tablist">
                  <li class="nav-item">
                     <a class="rc-adi-subtab active" data-toggle="tab" href="#tum_taksit" role="tab" aria-selected="true">
                        <i class="fa fa-list"></i><span>Tümü</span>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="rc-adi-subtab" data-toggle="tab" href="#acik_taksit" role="tab" aria-selected="false">
                        <i class="fa fa-folder-open-o"></i><span>Açık Taksitler</span>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="rc-adi-subtab" data-toggle="tab" href="#kapali_taksit" role="tab" aria-selected="false">
                        <i class="fa fa-check-square-o"></i><span>Kapalı Taksitler</span>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="rc-adi-subtab" data-toggle="tab" href="#gecikmis_taksit" role="tab" aria-selected="false">
                        <i class="fa fa-clock-o"></i><span>Gecikmiş Taksitler</span>
                     </a>
                  </li>
               </ul>
            </div>
            <div class="tab-content rc-adi-subtab-content">
               <div class="tab-pane fade show active" id="tum_taksit" role="tab-panel">
                  <div class="rc-adi-tablo-wrap">
                     <table class="data-table table stripe hover nowrap rc-adi-table rc-adi-table-taksit" id="tum_taksitler" style="width:100%">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th class="rc-adi-col-actions">İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
               </div>
               <div class="tab-pane fade show" id="acik_taksit" role="tab-panel">
                  <div class="rc-adi-tablo-wrap">
                     <table class="data-table table stripe hover nowrap rc-adi-table rc-adi-table-taksit" id="acik_taksitler" style="width:100%">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th class="rc-adi-col-actions">İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
               </div>
               <div class="tab-pane fade show" id="kapali_taksit" role="tab-panel">
                  <div class="rc-adi-tablo-wrap">
                     <table class="data-table table stripe hover nowrap rc-adi-table rc-adi-table-taksit" id="kapali_taksitler" style="width:100%">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th class="rc-adi-col-actions">İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
               </div>
               <div class="tab-pane fade show" id="gecikmis_taksit" role="tab-panel">
                  <div class="rc-adi-tablo-wrap">
                     <table class="data-table table stripe hover nowrap rc-adi-table rc-adi-table-taksit" id="gecikmis_taksitler" style="width:100%">
                        <thead>
                           <tr>
                              <th>Durum</th>
                              <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                              <th>Vade Sayısı</th>
                              <th>Ödenmiş</th>
                              <th>Ödenmemiş</th>
                              <th>Yaklaşan Taksit</th>
                              <th class="rc-adi-col-actions">İşlemler</th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
         @endyetki
      </div>
   </div>
</div>

<style>
/* =================================================================
   SATIŞ TAKİBİ (ADİSYONLAR) — MODERN RESPONSIVE
   Marka mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */

.rc-adi-page {
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
.rc-adi-header {
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
.rc-adi-header-left,
.rc-adi-header-right {
   display: flex;
   align-items: center;
   gap: 10px;
   flex-wrap: wrap;
}
.rc-adi-title-row {
   display: flex;
   align-items: center;
   gap: 14px;
}
.rc-adi-icon-bubble {
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
.rc-adi-title {
   margin: 0;
   font-size: 19px;
   font-weight: 700;
   color: var(--rc-text);
   line-height: 1.2;
}
.rc-adi-breadcrumb {
   margin-top: 4px;
   font-size: 12.5px;
   color: var(--rc-text-soft);
   display: flex;
   align-items: center;
   gap: 6px;
   flex-wrap: wrap;
}
.rc-adi-breadcrumb a {
   color: var(--rc-text-soft);
   text-decoration: none;
   transition: color .15s;
}
.rc-adi-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-adi-breadcrumb .rc-adi-sep { color: #cbd5e1; }
.rc-adi-breadcrumb .rc-adi-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === HEADER BUTONLAR === */
.rc-adi-btn {
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
.rc-adi-btn i { font-size: 14px; }
.rc-adi-btn:hover { transform: translateY(-1px); filter: brightness(1.06); color: #fff; }
.rc-adi-btn:active { transform: translateY(0); }
.rc-adi-btn-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
   box-shadow: 0 6px 16px rgba(22, 163, 74, .28);
}
.rc-adi-btn-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
}

/* === STAT KARTLARI === */
.rc-adi-stats {
   display: grid;
   grid-template-columns: repeat(3, 1fr);
   gap: 14px;
   margin-bottom: 18px;
}
.rc-adi-stat {
   display: flex;
   align-items: center;
   gap: 14px;
   padding: 18px 20px;
   border-radius: 14px;
   color: #fff;
   position: relative;
   overflow: hidden;
   box-shadow: 0 6px 20px rgba(17, 24, 39, .06);
}
.rc-adi-stat::after {
   content: '';
   position: absolute;
   right: -40px; bottom: -40px;
   width: 140px; height: 140px;
   border-radius: 50%;
   background: rgba(255, 255, 255, .08);
}
.rc-adi-stat-primary {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 60%, var(--rc-pink) 100%);
}
.rc-adi-stat-success {
   background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}
.rc-adi-stat-danger {
   background: linear-gradient(135deg, #dc2626 0%, #f97316 100%);
}
.rc-adi-stat-icon {
   width: 52px; height: 52px;
   border-radius: 12px;
   background: rgba(255, 255, 255, .18);
   display: inline-flex;
   align-items: center;
   justify-content: center;
   font-size: 22px;
   flex-shrink: 0;
   z-index: 1;
}
.rc-adi-stat-body {
   display: flex;
   flex-direction: column;
   gap: 4px;
   z-index: 1;
   min-width: 0;
}
.rc-adi-stat-label {
   font-size: 12.5px;
   font-weight: 600;
   opacity: .92;
   text-transform: uppercase;
   letter-spacing: .04em;
}
.rc-adi-stat-value {
   font-size: 22px;
   font-weight: 800;
   line-height: 1.1;
   word-break: break-word;
}

/* === KART === */
.rc-adi-card {
   background: #fff;
   border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17, 24, 39, .04), 0 4px 16px rgba(92, 0, 142, .04);
   padding: 8px;
   margin-bottom: 18px;
}

/* === FİLTRELER === */
.rc-adi-filters-card { padding: 18px; }
.rc-adi-filters {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
   gap: 14px;
   align-items: end;
}
.rc-adi-filter { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.rc-adi-filter label {
   font-size: 12px;
   font-weight: 700;
   color: var(--rc-text-soft);
   text-transform: uppercase;
   letter-spacing: .04em;
   margin: 0;
}
.rc-adi-filter .form-control {
   border: 1px solid var(--rc-border) !important;
   border-radius: 10px !important;
   height: 42px;
   padding: 0 14px;
   font-size: 13.5px;
   color: var(--rc-text);
   background: #fff;
   box-shadow: none !important;
   transition: border-color .15s, box-shadow .15s;
}
.rc-adi-filter .form-control:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12) !important;
}
.rc-adi-filter .select2-container .select2-selection--single {
   height: 42px !important;
   border: 1px solid var(--rc-border) !important;
   border-radius: 10px !important;
   padding: 0 6px;
}
.rc-adi-filter .select2-container--default .select2-selection--single .select2-selection__rendered {
   line-height: 42px !important;
   color: var(--rc-text);
}
.rc-adi-filter .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
.rc-adi-extras {
   display: flex;
   align-items: center;
   gap: 8px;
   flex-wrap: wrap;
   min-height: 42px;
}
.rc-adi-status-counts { display: inline-flex; gap: 6px; flex-wrap: wrap; }
.rc-adi-status-counts .badge {
   display: inline-flex !important;
   align-items: center;
   padding: 5px 10px !important;
   border-radius: 999px !important;
   font-size: 11px !important;
   font-weight: 700 !important;
   line-height: 1.2 !important;
}
.rc-adi-status-counts .badge-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
}
.rc-adi-status-counts .badge-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
}
.rc-adi-status-counts .badge-info {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-adi-fatura-btn {
   width: 42px; height: 42px;
   border-radius: 10px !important;
   display: inline-flex !important;
   align-items: center;
   justify-content: center;
   padding: 0 !important;
   font-size: 16px !important;
   border: 1px solid var(--rc-border) !important;
   transition: filter .15s;
}
.rc-adi-fatura-btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border-color: #fde68a !important;
}
.rc-adi-fatura-btn.rc-adi-fatura-aktif {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border-color: #bbf7d0 !important;
}
.rc-adi-fatura-btn:hover { filter: brightness(.97); }

/* === ANA TAB BAR === */
.rc-adi-tabs-wrap {
   padding: 14px 14px 0;
   border-bottom: 1px solid var(--rc-border);
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
   scrollbar-width: thin;
}
.rc-adi-tabs-wrap::-webkit-scrollbar { height: 4px; }
.rc-adi-tabs-wrap::-webkit-scrollbar-thumb {
   background: var(--rc-purple-soft);
   border-radius: 4px;
}
.rc-adi-tabs {
   display: inline-flex !important;
   flex-wrap: nowrap;
   gap: 6px;
   border: none !important;
   margin: 0 !important;
   padding: 0 !important;
   list-style: none;
   min-width: max-content;
}
.rc-adi-tabs .nav-item { margin: 0; }
.rc-adi-tab {
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
.rc-adi-tab i { font-size: 14px; opacity: .85; }
.rc-adi-tab:hover {
   color: var(--rc-purple-dark);
   background: var(--rc-purple-light);
}
.rc-adi-tab.active,
.rc-adi-tab[aria-selected="true"] {
   color: var(--rc-purple-dark);
   background: var(--rc-purple-light);
}
.rc-adi-tab.active::after,
.rc-adi-tab[aria-selected="true"]::after {
   content: '';
   position: absolute;
   left: 12px; right: 12px;
   bottom: -1px;
   height: 3px;
   border-radius: 3px 3px 0 0;
   background: linear-gradient(90deg, var(--rc-purple-dark), var(--rc-purple), var(--rc-pink));
}
.rc-adi-tab-count {
   font-size: 11.5px;
   font-weight: 700;
   color: var(--rc-text-soft);
   opacity: .85;
}
.rc-adi-tab.active .rc-adi-tab-count,
.rc-adi-tab[aria-selected="true"] .rc-adi-tab-count {
   color: var(--rc-purple-dark);
}

.rc-adi-tab-content { padding: 14px; }

/* === ALT TAB (TAKSİTLER) === */
.rc-adi-subtabs-wrap {
   padding: 4px 4px 0;
   border-bottom: 1px solid var(--rc-border);
   margin-bottom: 14px;
   overflow-x: auto;
}
.rc-adi-subtabs {
   display: inline-flex !important;
   flex-wrap: nowrap;
   gap: 4px;
   border: none !important;
   margin: 0 !important;
   padding: 0 !important;
   list-style: none;
   min-width: max-content;
}
.rc-adi-subtabs .nav-item { margin: 0; }
.rc-adi-subtab {
   display: inline-flex !important;
   align-items: center;
   gap: 6px;
   height: 34px;
   padding: 0 14px !important;
   border: none !important;
   background: transparent !important;
   color: var(--rc-text-soft) !important;
   font-size: 12.5px;
   font-weight: 600;
   border-radius: 8px 8px 0 0 !important;
   cursor: pointer;
   position: relative;
   text-decoration: none !important;
   white-space: nowrap;
   transition: color .15s, background .15s;
}
.rc-adi-subtab:hover { color: var(--rc-purple-dark) !important; background: var(--rc-purple-light) !important; }
.rc-adi-subtab.active,
.rc-adi-subtab[aria-selected="true"] {
   color: var(--rc-purple-dark) !important;
   background: var(--rc-purple-light) !important;
}
.rc-adi-subtab.active::after,
.rc-adi-subtab[aria-selected="true"]::after {
   content: '';
   position: absolute;
   left: 10px; right: 10px;
   bottom: -1px;
   height: 2.5px;
   border-radius: 3px 3px 0 0;
   background: linear-gradient(90deg, var(--rc-purple-dark), var(--rc-purple), var(--rc-pink));
}
.rc-adi-subtab-content { padding: 4px; }

/* === TABLO WRAPPER === */
.rc-adi-tablo-wrap {
   width: 100%;
   overflow-x: auto;
   -webkit-overflow-scrolling: touch;
   border-radius: 10px;
}
.rc-adi-tablo-wrap .rc-adi-table { min-width: 900px; }
.rc-adi-tablo-wrap .rc-adi-table-taksit { min-width: 720px; }

/* === DATATABLES WRAPPER === */
.rc-adi-card .dataTables_wrapper { padding: 6px 4px 8px; }
.rc-adi-card .dataTables_length,
.rc-adi-card .dataTables_filter { margin-bottom: 14px; }
.rc-adi-card .dataTables_length label,
.rc-adi-card .dataTables_filter label {
   color: var(--rc-text-soft);
   font-size: 13px;
   font-weight: 500;
}
.rc-adi-card .dataTables_filter input {
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
.rc-adi-card .dataTables_filter input:focus {
   border-color: var(--rc-purple) !important;
   box-shadow: 0 0 0 4px rgba(157, 93, 200, .12);
}
.rc-adi-card .dataTables_length select {
   border: 1px solid var(--rc-border) !important;
   border-radius: 8px !important;
   padding: 4px 8px !important;
   font-size: 13px;
   margin: 0 6px;
}

/* === TABLO === */
.rc-adi-table {
   border-collapse: separate !important;
   border-spacing: 0 !important;
}
.rc-adi-table thead th {
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
.rc-adi-table thead th:first-child { border-top-left-radius: 10px; }
.rc-adi-table thead th:last-child { border-top-right-radius: 10px; }
.rc-adi-table tbody td {
   padding: 14px 14px !important;
   font-size: 13.5px;
   color: var(--rc-text);
   border: none !important;
   border-bottom: 1px solid var(--rc-border) !important;
   vertical-align: middle !important;
   background: #fff;
}
.rc-adi-table tbody tr { transition: background-color .15s; }
.rc-adi-table tbody tr:hover td { background: var(--rc-purple-light) !important; }
.rc-adi-table tbody tr:last-child td { border-bottom: none !important; }
.rc-adi-table.stripe tbody tr.odd td { background: #fbfaff; }
.rc-adi-table.stripe tbody tr.odd:hover td { background: var(--rc-purple-light) !important; }

/* === RESPONSIVE PLUGIN'I DEVRE DIŞI === */
.rc-adi-table thead th,
.rc-adi-table tbody td { display: table-cell !important; }
.rc-adi-table td.dtr-control,
.rc-adi-table th.dtr-control { display: none !important; }
.rc-adi-table tr.child { display: none !important; }
.rc-adi-table td.dtr-hidden,
.rc-adi-table th.dtr-hidden,
.rc-adi-table td.none,
.rc-adi-table th.none { display: table-cell !important; }

/* === DURUM BADGE OVERRIDE === */
.rc-adi-table tbody td .btn.btn-warning,
.rc-adi-table tbody td .btn.btn-success,
.rc-adi-table tbody td .btn.btn-danger,
.rc-adi-table tbody td .btn.btn-info,
.rc-adi-table tbody td .btn.btn-primary,
.rc-adi-table tbody td .btn.btn-secondary {
   border-radius: 999px !important;
   padding: 6px 14px !important;
   font-size: 12px !important;
   font-weight: 700 !important;
   line-height: 1.4 !important;
   display: inline-block !important;
   width: auto !important;
   min-width: 90px;
   text-align: center;
   box-shadow: none !important;
}
.rc-adi-table tbody td .btn.btn-warning {
   background: var(--rc-warning-soft) !important;
   color: #b45309 !important;
   border: 1px solid #fde68a !important;
}
.rc-adi-table tbody td .btn.btn-success {
   background: var(--rc-success-soft) !important;
   color: #15803d !important;
   border: 1px solid #bbf7d0 !important;
}
.rc-adi-table tbody td .btn.btn-danger {
   background: var(--rc-danger-soft) !important;
   color: #b91c1c !important;
   border: 1px solid #fecaca !important;
}
.rc-adi-table tbody td .btn.btn-info {
   background: var(--rc-info-soft) !important;
   color: #0369a1 !important;
   border: 1px solid #bae6fd !important;
}
.rc-adi-table tbody td .btn.btn-primary {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border: 1px solid var(--rc-purple-soft) !important;
}
.rc-adi-table tbody td .btn.btn-secondary {
   background: #f3f4f6 !important;
   color: #4b5563 !important;
   border: 1px solid #e5e7eb !important;
}

/* === İŞLEMLER SUTUNU: kompakt ikon butonlar, hepsi tek satirda === */
/* İşlemler her zaman son sutun; aksiyon butonlarini esit boyutlu kare ikon
   yapip tek satira sigdiriyoruz (genis pill gorunumu sadece tutar/durum
   sutunlarinda kalsin). */
.rc-adi-page .rc-adi-table tbody td:last-child {
   white-space: nowrap !important;
}
.rc-adi-page .rc-adi-table tbody td:last-child .btn {
   min-width: 0 !important;
   width: 34px !important;
   height: 34px !important;
   padding: 0 !important;
   margin: 0 1px !important;
   display: inline-flex !important;
   align-items: center !important;
   justify-content: center !important;
   border-radius: 8px !important;
   font-size: 14px !important;
   line-height: 1 !important;
   vertical-align: middle !important;
   box-shadow: none !important;
}
.rc-adi-page .rc-adi-table tbody td:last-child .btn i { font-size: 14px !important; margin: 0 !important; }

/* === İŞLEMLER DROPDOWN === */
.rc-adi-table tbody td .dropdown .dropdown-toggle.btn-link,
.rc-adi-table tbody td .dropdown > .btn-link {
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
.rc-adi-table tbody td .dropdown .dropdown-toggle.btn-link:hover,
.rc-adi-table tbody td .dropdown > .btn-link:hover {
   background: var(--rc-purple-soft) !important;
   transform: scale(1.05);
}
.rc-adi-table tbody td .dropdown-menu {
   border: 1px solid var(--rc-border) !important;
   border-radius: 12px !important;
   box-shadow: 0 12px 32px rgba(92, 0, 142, .12) !important;
   padding: 6px !important;
   min-width: 200px !important;
}
.rc-adi-table tbody td .dropdown-menu .dropdown-item {
   padding: 9px 12px !important;
   border-radius: 8px !important;
   font-size: 13px !important;
   color: var(--rc-text) !important;
   transition: background .12s;
}
.rc-adi-table tbody td .dropdown-menu .dropdown-item:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
}
.rc-adi-table tbody td .dropdown-menu .dropdown-item i {
   width: 18px;
   margin-right: 6px;
   color: var(--rc-purple);
}

/* === PAGINATION === */
.rc-adi-card .dataTables_paginate { padding: 14px; }
.rc-adi-card .dataTables_paginate .paginate_button {
   border-radius: 8px !important;
   padding: 6px 12px !important;
   margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important;
   color: var(--rc-text-soft) !important;
   background: #fff !important;
   font-size: 13px !important;
   transition: all .12s;
}
.rc-adi-card .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important;
   color: var(--rc-purple-dark) !important;
   border-color: var(--rc-purple-soft) !important;
}
.rc-adi-card .dataTables_paginate .paginate_button.current,
.rc-adi-card .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important;
   border-color: transparent !important;
   box-shadow: 0 4px 10px rgba(92, 0, 142, .25) !important;
}
.rc-adi-card .dataTables_info {
   color: var(--rc-text-soft);
   font-size: 12.5px;
   padding: 14px !important;
}

/* === SORT === */
.rc-adi-table thead th[class*="sorting"] { position: relative; cursor: pointer; }
.rc-adi-table thead th.sorting_asc,
.rc-adi-table thead th.sorting_desc { color: var(--rc-purple-dark) !important; }

/* === SPINNER === */
.rc-adi-card .dataTables_processing {
   border-radius: 12px !important;
   border: 1px solid var(--rc-border) !important;
   box-shadow: 0 10px 30px rgba(92, 0, 142, .12) !important;
   color: var(--rc-purple-dark) !important;
   font-weight: 600;
   padding: 14px 22px !important;
}

/* === RESPONSIVE: TABLET (≤1024px) === */
@media (max-width: 1024px) {
   .rc-adi-header { padding: 14px 16px; }
   .rc-adi-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-adi-title { font-size: 17px; }
   .rc-adi-btn { height: 38px; padding: 0 14px; font-size: 12.5px; }
   .rc-adi-stats { grid-template-columns: repeat(3, 1fr); gap: 10px; }
   .rc-adi-stat { padding: 14px 16px; gap: 12px; }
   .rc-adi-stat-icon { width: 44px; height: 44px; font-size: 18px; }
   .rc-adi-stat-value { font-size: 18px; }
   .rc-adi-stat-label { font-size: 11px; }
   .rc-adi-filters-card { padding: 14px; }
   .rc-adi-card { padding: 6px; }
   .rc-adi-tabs-wrap { padding: 10px 10px 0; }
   .rc-adi-tab { height: 36px; font-size: 12.5px; padding: 0 12px; }
   .rc-adi-tab-content { padding: 10px; }
   .rc-adi-card .dataTables_filter input { width: 180px; }
   .rc-adi-table thead th,
   .rc-adi-table tbody td { padding: 12px 10px !important; font-size: 13px; }
}

/* === RESPONSIVE: MOBILE (≤768px) — TABLE → CARD === */
@media (max-width: 768px) {
   .rc-adi-header { padding: 12px 14px; border-radius: 12px; }
   .rc-adi-header-left { width: 100%; }
   .rc-adi-header-right { width: 100%; }
   .rc-adi-header-right .rc-adi-btn { flex: 1; justify-content: center; min-width: 0; }
   .rc-adi-title { font-size: 16px; }
   .rc-adi-breadcrumb { font-size: 11.5px; }

   .rc-adi-stats { grid-template-columns: 1fr; gap: 10px; }

   .rc-adi-filters { grid-template-columns: 1fr 1fr; }
   .rc-adi-filter-extras { grid-column: 1 / -1; }

   .rc-adi-card { padding: 4px; border-radius: 12px; }
   .rc-adi-tab-content { padding: 8px; }
   .rc-adi-tab { padding: 0 10px; }
   .rc-adi-tab span { font-size: 12px; }
   .rc-adi-tab-count { display: none; }

   .rc-adi-card .dataTables_filter { float: none; text-align: left; }
   .rc-adi-card .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 6px; }
   .rc-adi-card .dataTables_filter label { display: block; width: 100%; }
   .rc-adi-card .dataTables_length { display: none; }

   /* TABLO -> KART GORUNUMU */
   .rc-adi-tablo-wrap { overflow: visible; }
   .rc-adi-tablo-wrap .rc-adi-table { min-width: 0; width: 100% !important; }

   .rc-adi-table,
   .rc-adi-table thead,
   .rc-adi-table tbody,
   .rc-adi-table tr,
   .rc-adi-table td,
   .rc-adi-table th {
      display: block !important;
      width: 100% !important;
   }
   .rc-adi-table thead { display: none !important; }

   .rc-adi-table tbody tr {
      background: #fff;
      border: 1px solid var(--rc-border);
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 12px;
      box-shadow: 0 1px 2px rgba(17, 24, 39, .03);
      position: relative;
   }
   .rc-adi-table tbody tr:hover td,
   .rc-adi-table tbody tr.odd td,
   .rc-adi-table tbody td {
      background: transparent !important;
      border: none !important;
      padding: 6px 0 !important;
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      font-size: 13px;
   }
   .rc-adi-table tbody td::before {
      content: attr(data-label);
      font-size: 11px;
      font-weight: 700;
      color: var(--rc-text-soft);
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
      min-width: 110px;
   }
   .rc-adi-table tbody td.rc-adi-td-actions,
   .rc-adi-table tbody td.rc-adi-td-durum {
      justify-content: flex-end;
   }
   .rc-adi-table tbody td .btn.btn-warning,
   .rc-adi-table tbody td .btn.btn-success,
   .rc-adi-table tbody td .btn.btn-danger,
   .rc-adi-table tbody td .btn.btn-info,
   .rc-adi-table tbody td .btn.btn-primary,
   .rc-adi-table tbody td .btn.btn-secondary { min-width: 0; }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤480px) === */
@media (max-width: 480px) {
   .rc-adi-filters { grid-template-columns: 1fr; }
   .rc-adi-header-right { flex-direction: column; gap: 8px; }
   .rc-adi-header-right .rc-adi-btn { width: 100%; flex: none; }
   .rc-adi-title-row { gap: 10px; }
   .rc-adi-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
   .rc-adi-tab span { display: none; }
   .rc-adi-tab { padding: 0 12px; }
   .rc-adi-tab i { font-size: 16px; }
   .rc-adi-subtab span { display: none; }
   .rc-adi-subtab { padding: 0 10px !important; }
   .rc-adi-subtab i { font-size: 14px; }
}
</style>

<script>
    var dataTablesInstances = {};
    var activeTableId = null;
    var tumAdisyonAcikSayisi = 0;
    var tumAdisyonKapaliSayisi = 0;
    var toplamAdisyonSayisi = 0;
 // Özet bilgileri güncelleme fonksiyonu
    function updateSummaryInfo(response) {
        if (!response) return;

        // Tüm tutarları güncelle
        $('#toplamSatisTutar').text(response.toplamSatis ? response.toplamSatis + ' ₺' : '0,00 ₺');
        $('#odenenToplamTutar').text(response.odenen ? response.odenen + ' ₺' : '0,00 ₺');
        $('#kalanToplamTutar').text(response.kalan ? response.kalan + ' ₺' : '0,00 ₺');

        // Açık/Kapalı sayılarını güncelle
        var acikSayi = response.acikAdisyonSayisi || 0;
        var kapaliSayi = response.kapaliAdisyonSayisi || 0;

        $('#acikAdisyonSayisi').text(acikSayi);
        $('#kapaliAdisyonSayisi').text(kapaliSayi);

        // Tüm adisyon sayılarını sakla (filtre değişikliklerinde kullanmak için)
        if (response.tumAdisyonAcikSayisi !== undefined) {
            tumAdisyonAcikSayisi = response.tumAdisyonAcikSayisi;
        }
        if (response.tumAdisyonKapaliSayisi !== undefined) {
            tumAdisyonKapaliSayisi = response.tumAdisyonKapaliSayisi;
        }
        if (response.toplamAdisyonSayisi !== undefined) {
            toplamAdisyonSayisi = response.toplamAdisyonSayisi;
        }

        // Satış durumu filtresi yanındaki sayıları güncelle
        updateSalesStatusCounts();

        // Tablardaki sayıları güncelle
        if (response.islemsayisi !== undefined) {
            $('#satis_takibi_islem_sayisi').text('(' + response.islemsayisi + ')');
        }
        if (response.urunsayisi !== undefined) {
            $('#satis_takibi_urun_sayisi').text('(' + response.urunsayisi + ')');
        }
        if (response.paketsayisi !== undefined) {
            $('#satis_takibi_paket_sayisi').text('(' + response.paketsayisi + ')');
        }

        // Tümü tabı için toplam sayı
        var toplamSayi = (response.recordsTotal || response.recordsFiltered || 0);
        $('#satis_takibi_tumu_sayisi').text('(' + toplamSayi + ')');
    }

    // Satış durumu filtresi yanındaki sayıları güncelleme fonksiyonu
    function updateSalesStatusCounts() {
        var selectedStatus = $('#satis_durumu_filtre').val();
        var acikText = '', kapaliText = '', tumText = '';

        // Eğer tüm satışlar seçiliyse, tüm sayıları göster
        if (selectedStatus === '' || selectedStatus === null) {
            acikText = tumAdisyonAcikSayisi;
            kapaliText = tumAdisyonKapaliSayisi;
            tumText = toplamAdisyonSayisi;
            $('#satisDurumuSayilari').html(
                '<span class="badge badge-success ml-1">Açık: ' + acikText + '</span>' +
                '<span class="badge badge-danger ml-1">Kapalı: ' + kapaliText + '</span>' +
                '<span class="badge badge-info ml-1">Toplam: ' + tumText + '</span>'
            );
        }
        // Eğer açık satışlar seçiliyse, sadece açık sayısını göster
        else if (selectedStatus === 'acik') {
            acikText = tumAdisyonAcikSayisi;
            $('#satisDurumuSayilari').html(
                '<span class="badge badge-success ml-1">Açık: ' + acikText + '</span>'
            );
        }
        // Eğer kapalı satışlar seçiliyse, sadece kapalı sayısını göster
        else if (selectedStatus === 'kapali') {
            kapaliText = tumAdisyonKapaliSayisi;
            $('#satisDurumuSayilari').html(
                '<span class="badge badge-danger ml-1">Kapalı: ' + kapaliText + '</span>'
            );
        }
    }

    // Personel seçimi başlatma
    function initializePersonelSelect() {
        var urlParams = new URLSearchParams(window.location.search);
        var sube = urlParams.get('sube') || '';

        $.ajax({
            url: '/isletmeyonetim/personel_listesi_getir',
            type: 'GET',
            data: { sube: sube },
            success: function(response) {
                var select = $('#satisPersonelFiltre');
                select.empty();
                select.append('<option value="">Tüm Personeller</option>');

                $.each(response, function(index, personel) {
                    select.append('<option value="' + personel.id + '">' + personel.personel_adi + '</option>');
                });

                select.select2({
                    placeholder: "Personel seçin",
                    allowClear: true,
                    width: '100%'
                });
            },
            error: function(xhr, status, error) {
                console.error('Personel listesi yüklenirken hata:', error);
                $('#satisPersonelFiltre').html('<option value="">Tüm Personeller</option>');
            }
        });
    }
     // Tab ID'sine göre tablo ID'sini bul
    function getTableIdFromTab(tabId) {
        var tableMap = {
            'tum_adisyonlar': '#adisyon_liste',
            'hizmet_adisyonlar': '#adisyon_liste_hizmet',
            'ürün_adisyonlar': '#adisyon_liste_urun',
            'paket_adisyonlar': '#adisyon_liste_paket',
            'tum_taksit': '#tum_taksitler',
            'acik_taksit': '#acik_taksitler',
            'kapali_taksit': '#kapali_taksitler',
            'gecikmis_taksit': '#gecikmis_taksitler'
        };

        if (tabId && tabId.startsWith('#')) {
            tabId = tabId.substring(1);
        }

        return tableMap[tabId];
    }

    // Filtreleri uygulama fonksiyonu
    function applyFilters() {
        var personelId = $('#satisPersonelFiltre').val() || '';
        var selectedRange = $('#satis_zamana_gore_filtre').val();
        var satisDurumu = $('#satis_durumu_filtre').val() || '';

        var startDate, endDate;

        if (selectedRange === 'ozel') {
            startDate = $('#satisbaslangictarihi').val();
            endDate = $('#satisbitistarihi').val();

            if (!startDate || !endDate) {
                var today = new Date();
                startDate = today.toISOString().split('T')[0];
                endDate = startDate;
            }
        } else {
            var dates = selectedRange.split(' / ');
            startDate = dates[0];
            endDate = dates[1];
        }

        if (!isValidDate(startDate) || !isValidDate(endDate)) {
            console.error('Geçersiz tarih formatı');
            return;
        }

        if (activeTableId) {
            refreshSpecificTable(activeTableId, personelId, startDate, endDate, satisDurumu);
        }
    }

    // Belirli bir tabloyu yenile
    function refreshSpecificTable(tableId, personelId, startDate, endDate, satisDurumu) {
        // Eğer parametreler verilmediyse, UI'dan al
        if (!personelId && !startDate && !endDate && !satisDurumu) {
            personelId = $('#satisPersonelFiltre').val() || '';
            var selectedRange = $('#satis_zamana_gore_filtre').val();
            satisDurumu = $('#satis_durumu_filtre').val() || '';

            if (selectedRange === 'ozel') {
                startDate = $('#satisbaslangictarihi').val();
                endDate = $('#satisbitistarihi').val();
                if (!startDate || !endDate) {
                    var today = new Date();
                    startDate = today.toISOString().split('T')[0];
                    endDate = startDate;
                }
            } else {
                var dates = selectedRange.split(' / ');
                startDate = dates[0];
                endDate = dates[1];
            }
        }

        // Tablo türünü belirle
        var tur = 0; // Varsayılan: Tümü
        switch(tableId) {
            case '#adisyon_liste_hizmet':
                tur = 1;
                break;
            case '#adisyon_liste_urun':
                tur = 3;
                break;
            case '#adisyon_liste_paket':
                tur = 2;
                break;
        }

        // Tablo zaten başlatılmış mı kontrol et ve temizle
        if (dataTablesInstances[tableId]) {
            try {
                // Önce responsive'yi temizle
                if (dataTablesInstances[tableId].responsive) {
                    dataTablesInstances[tableId].responsive.destroy();
                }

                // DataTable instance'ını tamamen yok et
                dataTablesInstances[tableId].clear().destroy();
            } catch (e) {
                console.log('Temizleme hatası:', e);
            }

            delete dataTablesInstances[tableId];

            // Tabloyu tamamen temizle
            $(tableId).empty();

            // Orijinal HTML yapısını geri yükle
            restoreTableHTML(tableId);
        }

        // Yeni instance oluştur
        initializeDataTable(tableId, tur, personelId, startDate, endDate, satisDurumu);
    }

    // Tablo HTML yapısını geri yükle
    function restoreTableHTML(tableId) {
        var isTaksitTable = tableId.includes('taksit');
        var originalHTML = '';

        if (isTaksitTable) {
            originalHTML = `
                <thead>
                    <tr>
                        <th>Durum</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Vade Sayısı</th>
                        <th>Ödenmiş</th>
                        <th>Ödenmemiş</th>
                        <th>Yaklaşan Taksit</th>
                        <th class="rc-adi-col-actions">İşlemler</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
        } else {
            originalHTML = `
                <thead>
                    <tr>
                        <th>Durum</th>
                        <th>Satış Tarihi</th>
                        <th>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</th>
                        <th>Yaklaşan Ödeme Tarihi</th>
                        <th>Adisyon İçeriği</th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th class="rc-adi-col-actions">İşlemler</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
        }

        $(tableId).html(originalHTML);
    }

    // DataTable başlatma fonksiyonu
    function initializeDataTable(tableId, tur = 0, personelId = '', startDate = '', endDate = '', satisDurumu = '') {
        if(!tableId.includes('taksit'))
        {
            // UI'dan değerleri al
        if (personelId === '' && startDate === '' && endDate === '' && satisDurumu === '') {
            personelId = $('#satisPersonelFiltre').val() || '';
            var selectedRange = $('#satis_zamana_gore_filtre').val();
            satisDurumu = $('#satis_durumu_filtre').val() || '';

            if (selectedRange === 'ozel') {
                startDate = $('#satisbaslangictarihi').val();
                endDate = $('#satisbitistarihi').val();
                if (!startDate || !endDate) {
                    var today = new Date();
                    startDate = today.toISOString().split('T')[0];
                    endDate = startDate;
                }
            } else {
                var dates = selectedRange.split(' / ');
                startDate = dates[0];
                endDate = dates[1];
            }
        }

        var tarihAraligi = startDate + ' / ' + endDate;
        if (!startDate || !endDate) {
            var today = new Date();
            var defaultDate = today.toISOString().split('T')[0];
            tarihAraligi = defaultDate + ' / ' + defaultDate;
        }

        var urlParams = new URLSearchParams(window.location.search);
        var sube = urlParams.get('sube') || '';

        var isTaksitTable = tableId.includes('taksit');
        var ajaxUrl = isTaksitTable ? "/isletmeyonetim/taksit-filtreli-getir" : "/isletmeyonetim/adisyon-filtreli-getir";

        console.log('Tabloyu başlatıyorum:', {
            tableId: tableId,
            tur: tur,
            tarihAraligi: tarihAraligi,
            personelId: personelId,
            satisDurumu: satisDurumu
        });

        var table = $(tableId).DataTable({
            autoWidth: false,
            responsive: false, // Responsive'i kapat
            processing: true,
            serverSide: true,
            deferRender: true,
            destroy: true, // Her seferinde yeni instance
            ajax: {
                url: ajaxUrl,
                type: "GET",
                data: function(d) {
                    var data = {
                        sube: sube,
                        tur: tur,
                        musteri_id: '',
                        tariharaligi: tarihAraligi,
                        personel_id: personelId,
                        draw: d.draw,
                        start: d.start,
                        length: d.length,
                        search: { value: d.search.value },
                        order: d.order
                    };

                    // Adisyon durumu parametresini ekle (sadece adisyon tabloları için)
                    if (!isTaksitTable && satisDurumu) {
                        data.adisyondurumu = satisDurumu;
                    }

                    return data;
                },
                dataSrc: function(json) {
                    // Özet bilgileri güncelle
                    updateSummaryInfo(json);

                    // DataTables'in beklediği formatta veri döndür
                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('DataTables AJAX error for ' + tableId + ':', error);
                    $(tableId + ' tbody').html(
                        '<tr><td colspan="' + (isTaksitTable ? '7' : '9') + '" class="text-center">Veri yüklenirken bir hata oluştu.</td></tr>'
                    );
                }
            },
            columns: isTaksitTable ? [
                { data: 'durum' },
                { data: 'musteri' },
                { data: 'vade_sayisi' },
                { data: 'odenen' },
                { data: 'kalan' },
                { data: 'yaklasan_taksit' },
                { data: 'islemler' }
            ] : [
                { data: 'durum' },
                { data: 'acilis_tarihi' },
                { data: 'musteri' },
                { data: 'planlanan_alacak_tarihi' },
                { data: 'icerik' },
                { data: 'toplam' },
                { data: 'odenen' },
                { data: 'kalan_tutar' },
                { data: 'islemler' }
            ],
            order: isTaksitTable ? [[5, "asc"]] : [[1, "desc"]],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                searchPlaceholder: "Ara",
                paginate: {
                    next: '<i class="ion-chevron-right"></i>',
                    previous: '<i class="ion-chevron-left"></i>'
                },
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Yükleniyor...</span></div> Yükleniyor...',
                emptyTable: "Tabloda veri bulunmamaktadır.",
                info: "_START_-_END_ arası _TOTAL_ kayıt",
                infoEmpty: "0-0 arası 0 kayıt",
                infoFiltered: "(_MAX_ kayıttan filtrelendi)",
                loadingRecords: "Yükleniyor...",
                zeroRecords: "Eşleşen kayıt bulunamadı."
            },
            initComplete: function(settings, json) {
                console.log(tableId + ' tablosu başarıyla yüklendi');
            }
        });

        dataTablesInstances[tableId] = table;
        return table;
        }

    }
    // Tarih formatını kontrol eden yardımcı fonksiyon
    function isValidDate(dateString) {
        var regEx = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateString.match(regEx)) return false;
        var d = new Date(dateString);
        var dNum = d.getTime();
        if (!dNum && dNum !== 0) return false;
        return d.toISOString().slice(0, 10) === dateString;
    }
$(document).ready(function() {

    // Datepicker ayarları
    $('#satisbaslangictarihi, #satisbitistarihi').datepicker({
        maxDate: new Date(),
        language: "tr",
        autoClose: true,
        dateFormat: "yyyy-mm-dd",
        onSelect: function(selectedDate) {
            if ($('#satisbaslangictarihi').val() && $('#satisbitistarihi').val()) {
                setTimeout(function() {
                    applyFilters();
                }, 300);
            }
        }
    });

    // Filtre değişikliklerini dinle
    $('#satis_zamana_gore_filtre').on('change', function() {
        var selectedValue = $(this).val();

        if (selectedValue === 'ozel') {
            $('#satis_zamana_gore_filtre1, #satis_zamana_gore_filtre2').show();
        } else {
            $('#satis_zamana_gore_filtre1, #satis_zamana_gore_filtre2').hide();
            setTimeout(function() {
                applyFilters();
            }, 100);
        }
    });

    $('#satisbaslangictarihi, #satisbitistarihi').on('change', function() {
        if ($('#satisbaslangictarihi').val() && $('#satisbitistarihi').val()) {
            setTimeout(function() {
                applyFilters();
            }, 300);
        }
    });

    $('#satisPersonelFiltre').on('change', function() {
        applyFilters();
    });

    // Satış durumu filtresini dinle
    $('#satis_durumu_filtre').on('change', function() {
        // Önce sayıları güncelle
        updateSalesStatusCounts();
        // Sonra filtreleri uygula
        applyFilters();
    });

    // Tab değiştiğinde
    $('button[data-toggle="tab"], .nav-link[data-toggle="tab"], a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr("href") || $(e.relatedTarget).attr("href");
        var tableId = getTableIdFromTab(target);

        if (tableId) {
            // Önceki tablonun responsive özelliklerini temizle
            if (activeTableId && dataTablesInstances[activeTableId]) {
                try {
                    // DataTables responsive instance'ını temizle
                    if (dataTablesInstances[activeTableId].responsive) {
                        dataTablesInstances[activeTableId].responsive.destroy();
                    }
                } catch (e) {
                    console.log('Responsive temizleme hatası:', e);
                }
            }

            // Yeni aktif tabloyu güncelle
            activeTableId = tableId;

            // Tab'ın görünür olmasını bekle
            setTimeout(function() {
                refreshSpecificTable(tableId);
            }, 50);
        }
    });

    // URL hash'i ile gelen tab'i aktif et (orn: #taksitli_alacaklar)
    (function openTabFromHash(){
        var h = (window.location.hash || '').replace('#','');
        if(!h) return;
        var $trigger = $('[data-toggle="tab"][href="#'+h+'"]');
        if($trigger.length){
            $trigger.tab('show');
        }
    })();

    // Modern sekmeler icin aktif sinifi senkron tut
    $(document).on('shown.bs.tab', '.rc-adi-tab', function(){
        $('.rc-adi-tab').removeClass('active').attr('aria-selected','false');
        $(this).addClass('active').attr('aria-selected','true');
    });
    $(document).on('shown.bs.tab', '.rc-adi-subtab', function(){
        var $parent = $(this).closest('.rc-adi-subtabs');
        $parent.find('.rc-adi-subtab').removeClass('active').attr('aria-selected','false');
        $(this).addClass('active').attr('aria-selected','true');
    });

    // Responsive plugin'in dinamik attigi siniflari geri al
    var ALL_TABLES = [
        '#adisyon_liste','#adisyon_liste_hizmet','#adisyon_liste_urun','#adisyon_liste_paket',
        '#tum_taksitler','#acik_taksitler','#kapali_taksitler','#gecikmis_taksitler'
    ];
    var ALL_SELECTOR = ALL_TABLES.join(',');
    $(document).on('init.dt', ALL_SELECTOR, function() {
        var $t = $(this);
        $t.removeClass('dtr-inline collapsed');
        setTimeout(function() {
            $t.find('td, th').removeClass('dtr-hidden none');
            $t.find('tr.child').remove();
            $t.find('td.dtr-control, th.dtr-control').remove();
        }, 50);
    });

    // Mobil kart gorunumu icin her hucreye sutun adi etiketi ekle
    var adisyonLabels = ['Durum','Satış Tarihi','{{ ($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) ? "Danışan" : "Müşteri" }}','Yaklaşan Ödeme','Adisyon İçeriği','Toplam ₺','Ödenen ₺','Kalan ₺','İşlemler'];
    var taksitLabels = ['Durum','{{ ($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) ? "Danışan" : "Müşteri" }}','Vade Sayısı','Ödenmiş','Ödenmemiş','Yaklaşan Taksit','İşlemler'];
    var adisyonTdClasses = ['rc-adi-td-durum','rc-adi-td-tarih','rc-adi-td-musteri','rc-adi-td-vade','rc-adi-td-icerik','rc-adi-td-toplam','rc-adi-td-odenen','rc-adi-td-kalan','rc-adi-td-actions'];
    var taksitTdClasses = ['rc-adi-td-durum','rc-adi-td-musteri','rc-adi-td-vade','rc-adi-td-odenen','rc-adi-td-kalan','rc-adi-td-vade','rc-adi-td-actions'];
    function applyLabels(){
        $(ALL_SELECTOR).each(function(){
            var $tbl = $(this);
            var isTaksit = $tbl.attr('id') && $tbl.attr('id').includes('taksit');
            var labels = isTaksit ? taksitLabels : adisyonLabels;
            var tdClasses = isTaksit ? taksitTdClasses : adisyonTdClasses;
            $tbl.find('tbody tr').each(function(){
                $(this).find('> td').each(function(idx){
                    var label = labels[idx] || '';
                    var cls = tdClasses[idx] || '';
                    $(this).attr('data-label', label);
                    if(cls && !$(this).hasClass(cls)) $(this).addClass(cls);
                });
            });
        });
    }
    $(document).on('draw.dt init.dt', ALL_SELECTOR, function(){
        setTimeout(applyLabels, 80);
    });

    // Sayfa yüklendiğinde
    setTimeout(function() {
        var activeTab = $('.tab-pane.active');
        if (activeTab.length) {
            var tableId = getTableIdFromTab(activeTab.attr('id'));
            if (tableId) {
                activeTableId = tableId;
                initializeDataTable(tableId);
            }
        }

        // Personel seçimini başlat
        initializePersonelSelect();

        // Satış durumu sayılarını başlangıçta güncelle
        updateSalesStatusCounts();

        // İlk filtreleme
        setTimeout(function() {
            applyFilters();
        }, 500);
    }, 300);


});
</script>

@include('modaldialogs.harici-tahsilat-modal')
@endsection
