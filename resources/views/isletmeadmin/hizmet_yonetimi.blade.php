@extends('layout.layout_isletmeadmin')
@section('content')
<style>
.hizmet-yonetim-wrapper { padding: 0; }
.hy-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
.hy-header h2 { margin:0; color:#1b68ca; font-size:22px; }
.hy-actions { display:flex; gap:10px; flex-wrap:wrap; }
.hy-search-box { position:relative; }
.hy-search-box input { padding:8px 12px 8px 36px; border:1px solid #e2e2e2; border-radius:6px; width:260px; font-size:14px; }
.hy-search-box i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#999; }
.hy-kategori-card { background:#fff; border:1px solid #e9ecef; border-radius:8px; margin-bottom:16px; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,0.04); }
.hy-kategori-header { padding:14px 18px; background:#f8f9fb; border-bottom:1px solid #e9ecef; display:flex; justify-content:space-between; align-items:center; cursor:pointer; }
.hy-kategori-header h3 { margin:0; font-size:16px; color:#2d3748; font-weight:600; }
.hy-kategori-header .hy-badge { background:#1b68ca; color:#fff; padding:2px 10px; border-radius:12px; font-size:12px; margin-left:8px; }
.hy-kategori-body { padding:0; }
.hy-hizmet-row { display:grid; grid-template-columns: 2fr 1fr 1fr 2fr 110px; align-items:center; padding:12px 18px; border-bottom:1px solid #f0f0f0; gap:10px; transition:background 0.15s; }
.hy-hizmet-row:hover { background:#fafbfc; }
.hy-hizmet-row:last-child { border-bottom:none; }
.hy-hizmet-adi { font-weight:500; color:#2d3748; }
.hy-hizmet-sure { color:#667; font-size:13px; }
.hy-hizmet-sure i { margin-right:4px; color:#999; }
.hy-hizmet-fiyat { font-weight:600; color:#1b68ca; font-size:14px; }
.hy-hizmet-personel { color:#667; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hy-hizmet-islemler { display:flex; gap:6px; justify-content:flex-end; }
.hy-btn-icon { width:32px; height:32px; border-radius:6px; border:1px solid #e2e2e2; background:#fff; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; color:#666; transition:all 0.2s; }
.hy-btn-icon:hover { background:#1b68ca; color:#fff; border-color:#1b68ca; }
.hy-btn-icon.danger:hover { background:#dc3545; border-color:#dc3545; }
.hy-empty { padding:40px; text-align:center; color:#999; }
.hy-hizmet-header-row { display:grid; grid-template-columns: 2fr 1fr 1fr 2fr 110px; padding:10px 18px; background:#fafbfc; border-bottom:2px solid #e9ecef; font-size:12px; font-weight:600; color:#667; text-transform:uppercase; gap:10px; }
.hy-cinsiyet-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; margin-left:6px; }
.hy-cinsiyet-0 { background:#ffe4f1; color:#d63384; }
.hy-cinsiyet-1 { background:#e0eeff; color:#0d6efd; }
.hy-cinsiyet-2 { background:#e6f4ea; color:#198754; }
@media (max-width: 768px){
   .hy-hizmet-row, .hy-hizmet-header-row { grid-template-columns: 1fr; gap:4px; }
   .hy-hizmet-header-row { display:none; }
   .hy-hizmet-row { padding:14px; }
   .hy-hizmet-islemler { justify-content:flex-start; margin-top:8px; }
   .hy-search-box input { width:100%; }
}
</style>

<div class="row clearfix hizmet-yonetim-wrapper">
   <div class="col-lg-12 col-md-12 col-sm-12">
      <div class="pd-20 card-box mb-30">
         <div class="hy-header">
            <div>
               <h2><i class="bi bi-scissors"></i> Hizmet Yönetimi</h2>
               <small style="color:#888;">İşletmenizde sunulan hizmetleri buradan yönetebilirsiniz.</small>
            </div>
            <div class="hy-actions">
               <div class="hy-search-box">
                  <i class="fa fa-search"></i>
                  <input type="text" id="hy_hizmet_ara" placeholder="Hizmet ara..." />
               </div>
               <button class="btn btn-outline-primary" data-toggle="modal" data-target="#hy_kategori_ekle_modal"><i class="fa fa-folder-plus"></i> Yeni Kategori</button>
               <button class="btn btn-primary" data-toggle="modal" data-target="#hizmet_secimi_modal"><i class="fa fa-plus"></i> Sistemden Hizmet Ekle</button>
               <button class="btn btn-success" data-toggle="modal" data-target="#yeni_hizmet_modal"><i class="fa fa-plus"></i> Yeni Hizmet Oluştur</button>
            </div>
         </div>

         <div id="hy_kategori_liste">
            @if(count($hizmet_gruplari) == 0)
               <div class="hy-empty">
                  <i class="fa fa-scissors" style="font-size:48px; color:#ddd; margin-bottom:16px;"></i>
                  <h4>Henüz hizmet eklenmemiş</h4>
                  <p>İşletmenizde sunacağınız ilk hizmeti eklemek için yukarıdaki butonları kullanabilirsiniz.</p>
               </div>
            @else
               @foreach($kategoriler as $kategori)
                  @if(isset($hizmet_gruplari[$kategori->id]) && count($hizmet_gruplari[$kategori->id]) > 0)
                  <div class="hy-kategori-card" data-kategori-id="{{$kategori->id}}">
                     <div class="hy-kategori-header" data-toggle="collapse" data-target="#kategori-body-{{$kategori->id}}">
                        <h3>
                           {{$kategori->hizmet_kategorisi_adi}}
                           <span class="hy-badge">{{count($hizmet_gruplari[$kategori->id])}}</span>
                        </h3>
                        <i class="fa fa-chevron-down"></i>
                     </div>
                     <div class="hy-kategori-body collapse show" id="kategori-body-{{$kategori->id}}">
                        <div class="hy-hizmet-header-row">
                           <div>Hizmet Adı</div>
                           <div>Süre</div>
                           <div>Fiyat</div>
                           <div>Personel / Cihaz</div>
                           <div style="text-align:right;">İşlemler</div>
                        </div>
                        @foreach($hizmet_gruplari[$kategori->id] as $hizmet)
                        <div class="hy-hizmet-row"
                             data-hizmet-id="{{$hizmet['hizmet_id']}}"
                             data-salon-hizmet-id="{{$hizmet['id']}}"
                             data-hizmet-adi="{{$hizmet['hizmet_adi']}}"
                             data-fiyat="{{$hizmet['fiyat']}}"
                             data-sure="{{$hizmet['sure_dk']}}"
                             data-kategori-id="{{$kategori->id}}"
                             data-cinsiyet="{{$hizmet['cinsiyet']}}">
                           <div class="hy-hizmet-adi">
                              {{$hizmet['hizmet_adi']}}
                              @if($hizmet['cinsiyet'] !== null)
                                 @if($hizmet['cinsiyet']==0)<span class="hy-cinsiyet-badge hy-cinsiyet-0">Kadın</span>
                                 @elseif($hizmet['cinsiyet']==1)<span class="hy-cinsiyet-badge hy-cinsiyet-1">Erkek</span>
                                 @elseif($hizmet['cinsiyet']==2)<span class="hy-cinsiyet-badge hy-cinsiyet-2">Unisex</span>
                                 @endif
                              @endif
                           </div>
                           <div class="hy-hizmet-sure"><i class="fa fa-clock-o"></i> {{$hizmet['sure_dk']}} dk</div>
                           <div class="hy-hizmet-fiyat">{{number_format($hizmet['fiyat'],2,',','.')}} ₺</div>
                           <div class="hy-hizmet-personel" title="{{$hizmet['personeller']}}">
                              @if($hizmet['personeller'] == '')
                                 <span style="color:#c33;"><i class="fa fa-exclamation-circle"></i> Atanmamış</span>
                              @else
                                 <i class="fa fa-user"></i> {{$hizmet['personeller']}}
                              @endif
                           </div>
                           <div class="hy-hizmet-islemler">
                              <button class="hy-btn-icon hy-hizmet-duzenle" title="Düzenle"><i class="fa fa-edit"></i></button>
                              <button class="hy-btn-icon danger hy-hizmet-sil" title="Sil" data-id="{{$hizmet['id']}}"><i class="fa fa-trash"></i></button>
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
   </div>
</div>

<!-- Hizmet Düzenleme Modal -->
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
            <div class="modal-body">
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
                        <select name="personel_ids[]" id="hy_edit_personeller" multiple class="form-control custom-select2" style="width:100%">
                           @foreach($personeller as $personel)
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                           @foreach($cihazlar as $cihaz)
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

<!-- Yeni Kategori Ekle Modal -->
<div class="modal modal-top fade calendar-modal" id="hy_kategori_ekle_modal">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form id="hy_kategori_ekle_formu">
            {!!csrf_field()!!}
            <div class="modal-header">
               <h2>Yeni Kategori Ekle</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
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

<!-- Sistemden Hizmet Seçimi Modal (mevcut altyapıyı kullanır) -->
<div id="hizmet_secimi_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id='hizmet_ekle_formu' method="POST">
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <div class="modal-header">
               <h2>Hizmet Seçimi</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <div class="row">
                  <div class="col-md-6">
                     <input type="button" class="btn btn-primary" style="width:100%;" onclick='selects()' value="Hepsini Seç"/>
                  </div>
                  <div class="col-md-6">
                     <input type="button" class="btn btn-secondary" style="width:100%;" onclick='deSelect()' value="Hiçbirini Seçme"/>
                  </div>
               </div>
               <div class="row" style="margin-top:20px;">
                  <div class="col-md-12">
                     <div class="form-group">
                        <input type="text" class="form-control search-input" placeholder="Hizmet Ara" id='hizmet_ara'/>
                     </div>
                  </div>
                  <div class="col-md-12" style="overflow-y:auto; max-height:400px;">
                     <button type="button" style="display:none" id='hizmet_personel_ekle_modal_ac' data-toggle="modal" data-target="#personel_sec_modal"></button>
                     <table class="table" id="hizmet_sec_tablo">
                        <thead>
                           <tr>
                              <td><input type="checkbox" id='tum_hizmetleri_sec'></td>
                              <td>Hizmet</td>
                           </tr>
                        </thead>
                        <tbody id='secilmeyen_hizmetler_liste'>
                        @foreach(\App\Hizmet_Kategorisi::all() as $hizmet_kategorisi)
                           @if(\App\Hizmetler::where(function($q) use ($isletme, $hizmet_kategorisi){
                              $q->where('ozel_hizmet', true);
                              $q->where('salon_id', $isletme->id);
                              $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));
                           })->orWhere(function($q) use ($isletme, $hizmet_kategorisi){
                              $q->whereNull('salon_id');
                              $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));
                              $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
                              $q->where('id', '!=', 463);
                           })->orWhere(function($q) use ($isletme, $hizmet_kategorisi){
                              $q->where('ozel_hizmet', true);
                              $q->where('salon_id', '!=', $isletme->id);
                              $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
                              $q->where('id', '!=', 463);
                           })->select('hizmet_adi')->distinct()->count() > 0)
                           <tr style="background:#e2e2e2;">
                              <td></td>
                              <td><strong>{{$hizmet_kategorisi->hizmet_kategorisi_adi}}</strong></td>
                           </tr>
                           @foreach(\App\Hizmetler::where(function($q) use ($isletme, $hizmet_kategorisi){
                              $q->where('salon_id', $isletme->id);
                              $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));
                           })->orWhere(function($q) use ($isletme, $hizmet_kategorisi){
                              $q->whereNull('salon_id');
                              $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));
                              $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
                              $q->where('id', '!=', 463);
                           })->orWhere(function($q) use ($isletme, $hizmet_kategorisi){
                              $q->where('ozel_hizmet', true);
                              $q->where('salon_id', $isletme->id);
                              $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
                              $q->where('id', '!=', 463);
                           })->select('hizmet_adi', 'id')->distinct()->get() as $secilmeyenhizmetler)
                              <tr>
                                 <td><input type="checkbox" name="salon_hizmetleri[]" value="{{$secilmeyenhizmetler->id}}"></td>
                                 <td>{{$secilmeyenhizmetler->hizmet_adi}}</td>
                              </tr>
                           @endforeach
                           @endif
                        @endforeach
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <div class="row">
                  <div class="col-md-9">
                     <button type="button" class="btn btn-success btn-lg btn-block" id='hizmet_personel_ekleme_butonu'>Hizmetlerin ekleneceği personelleri seç</button>
                  </div>
                  <div class="col-md-3">
                     <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i> Kapat</button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Personel seçim modal (mevcut yapı) -->
<div id="personel_sec_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered" style="max-width:800px;">
      <div class="modal-content" style="width:750px; max-height:90%;">
         <form id="hizmet_personel_formu" method="POST">
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            {!!csrf_field()!!}
            <div class="modal-header">
               <h2>Hizmet Personel Seçimi</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id='hizmet_personel_sec_bolumu'></div>
         </form>
      </div>
   </div>
</div>

<!-- Yeni Hizmet Oluştur Modal (mevcut altyapı) -->
<div id="yeni_hizmet_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="max-height:90%;">
         <form id="yeni_hizmet_formu" method="POST">
            <div class="modal-header">
               <h2>Yeni Hizmet Oluştur</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmet Adı</label>
                        <input type="text" name="hizmet_adi" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Süre (dk)</label>
                        <input type="number" name="hizmet_sure" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Fiyat (₺)</label>
                        <input type="number" step="0.01" name="hizmet_fiyati" class="form-control">
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Hizmeti Sunan Personeller & Cihazlar</label>
                        <select name="personeller[]" multiple class="form-control custom-select2" style="width:100%;">
                           @foreach($personeller as $personel)
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                           @foreach($cihazlar as $cihaz)
                              <option value="cihaz-{{$cihaz->id}}">{{$cihaz->cihaz_adi}} (Cihaz)</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-9">
                     <div class="form-group">
                        <label>Hizmet Kategorisi</label>
                        <select name="hizmet_kategorisi" class="form-control custom-select2" style="width:100%;">
                           @foreach($kategoriler as $cat)
                              <option value="{{$cat->id}}">{{$cat->hizmet_kategorisi_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label style="visibility:hidden;width:100%;">.</label>
                        <button type="button" class="btn btn-outline-primary btn-block" data-toggle="modal" data-target="#hy_kategori_ekle_modal" data-dismiss="modal"><i class="fa fa-plus"></i> Yeni Kategori</button>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Hizmetin Sunulduğu Müşteri Cinsiyeti</label>
                        <select class="form-control" name="cinsiyet">
                           <option selected value="">Belirtilmemiş</option>
                           <option value="0">Kadın</option>
                           <option value="1">Erkek</option>
                           <option value="2">Unisex</option>
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

<script>
$(document).ready(function(){

   // Select2 init
   $('.custom-select2').each(function(){
      if(!$(this).hasClass('select2-hidden-accessible')){
         $(this).select2({ placeholder: "Seçiniz..." });
      }
   });

   // Arama filtresi
   $('#hy_hizmet_ara').on('keyup', function(){
      var q = $(this).val().toLowerCase();
      $('.hy-hizmet-row').each(function(){
         var ad = ($(this).data('hizmet-adi')||'').toString().toLowerCase();
         if(ad.indexOf(q) > -1) $(this).show(); else $(this).hide();
      });
      $('.hy-kategori-card').each(function(){
         var gorunur = $(this).find('.hy-hizmet-row:visible').length;
         if(gorunur > 0) $(this).show(); else $(this).hide();
      });
   });

   // Kategori collapse ikon rotasyonu
   $('.hy-kategori-header').on('click', function(){
      var icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
      setTimeout(function(){
         icon.toggleClass('fa-chevron-down fa-chevron-up');
      }, 50);
   });

   // Düzenle butonuna tıklama
   $(document).on('click', '.hy-hizmet-duzenle', function(){
      var row = $(this).closest('.hy-hizmet-row');
      $('#hy_edit_salon_hizmet_id').val(row.data('salon-hizmet-id'));
      $('#hy_edit_hizmet_adi').val(row.data('hizmet-adi'));
      $('#hy_edit_fiyat').val(row.data('fiyat'));
      $('#hy_edit_sure_dk').val(row.data('sure'));
      $('#hy_edit_kategori_id').val(row.data('kategori-id'));
      $('#hy_edit_cinsiyet').val(row.data('cinsiyet') !== null ? row.data('cinsiyet') : '');

      // Personelleri sıfırla; güncel olanlar sunucudan getirilebilir
      $('#hy_edit_personeller').val(null).trigger('change');

      // Mevcut atanmış personelleri getir
      var hizmetId = row.data('hizmet-id');
      $.ajax({
         type: 'GET',
         url: '/isletmeyonetim/hizmetpersonelsecimigetir',
         data: { 'salon_hizmetleri[]': [hizmetId], sube: {{$isletme->id}} },
         dataType: 'text',
         success: function(html){
            var $tmp = $('<div>').html(html);
            var checkedIds = [];
            $tmp.find('input[type=checkbox]:checked').each(function(){
               checkedIds.push($(this).val());
            });
            if(checkedIds.length > 0){
               $('#hy_edit_personeller').val(checkedIds).trigger('change');
            }
         }
      });

      $('#hy_duzenle_modal').modal('show');
   });

   // Düzenleme formu gönderimi
   $('#hy_duzenle_formu').on('submit', function(e){
      e.preventDefault();
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
            swal({ type: result.status==='success'?'success':'error', title: result.status==='success'?'Başarılı':'Hata', text: result.message, showConfirmButton: false, timer: 2000 });
            if(result.status === 'success'){
               setTimeout(function(){ location.reload(); }, 1500);
            }
         },
         error: function(){
            $('#preloader').hide();
            swal({ type:'error', title:'Hata', text:'İşlem sırasında bir hata oluştu', showConfirmButton:false, timer:2500 });
         }
      });
   });

   // Hizmet silme
   $(document).on('click', '.hy-hizmet-sil', function(){
      var id = $(this).data('id');
      var row = $(this).closest('.hy-hizmet-row');
      swal({
         title: 'Emin misiniz?',
         text: "Bu hizmet salonunuzdan kaldırılacak.",
         type: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#d33',
         cancelButtonColor: '#3085d6',
         confirmButtonText: 'Evet, sil',
         cancelButtonText: 'İptal'
      }).then(function(result){
         if(result.value){
            $.ajax({
               type: 'POST',
               url: '/isletmeyonetim/salonhizmetsil',
               data: { sunulan_hizmet_id: id, sube: {{$isletme->id}}, _token: $('meta[name="csrf-token"]').attr('content') },
               dataType: 'json',
               beforeSend: function(){ $('#preloader').show(); },
               success: function(){
                  $('#preloader').hide();
                  row.fadeOut(300, function(){
                     $(this).remove();
                     $('.hy-kategori-card').each(function(){
                        if($(this).find('.hy-hizmet-row').length === 0) $(this).remove();
                     });
                  });
                  swal({ type:'success', title:'Silindi', text:'Hizmet kaldırıldı', showConfirmButton:false, timer:1800 });
               },
               error: function(){
                  $('#preloader').hide();
                  swal({ type:'error', title:'Hata', text:'İşlem sırasında hata oluştu', showConfirmButton:false, timer:2500 });
               }
            });
         }
      });
   });

   // Yeni kategori ekleme
   $('#hy_kategori_ekle_formu').on('submit', function(e){
      e.preventDefault();
      $.ajax({
         type: 'POST',
         url: '/isletmeyonetim/hizmet-yonetimi/kategori-ekle',
         data: $(this).serialize(),
         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
         dataType: 'json',
         beforeSend: function(){ $('#preloader').show(); },
         success: function(result){
            $('#preloader').hide();
            $('#hy_kategori_ekle_modal').modal('hide');
            $('#hy_kategori_ekle_formu')[0].reset();
            swal({ type:'success', title:'Başarılı', text: result.message, showConfirmButton:false, timer:1800 });
            if(result.status === 'success'){
               var opt = new Option(result.kategori_adi, result.kategori_id, true, true);
               $('select[name="hizmet_kategorisi"], select[name="kategori_id"]').append(opt).trigger('change');
            }
         },
         error: function(){
            $('#preloader').hide();
            swal({ type:'error', title:'Hata', text:'Kategori eklenemedi', showConfirmButton:false, timer:2500 });
         }
      });
   });
});

// Mevcut hizmet_ekle_formu flow için yardımcı fonksiyonlar (ayarlar sayfasındaki ile aynı)
function selects(){
   $('input[name="salon_hizmetleri[]"]').prop('checked', true);
   $('#tum_hizmetleri_sec').prop('checked', true);
}
function deSelect(){
   $('input[name="salon_hizmetleri[]"]').prop('checked', false);
   $('#tum_hizmetleri_sec').prop('checked', false);
}
$(document).on('change', '#tum_hizmetleri_sec', function(){
   $('input[name="salon_hizmetleri[]"]').prop('checked', $(this).prop('checked'));
});
$(document).on('keyup', '#hizmet_ara', function(){
   var q = $(this).val().toLowerCase();
   $('#secilmeyen_hizmetler_liste tr').each(function(){
      var t = $(this).text().toLowerCase();
      if(t.indexOf(q) > -1) $(this).show(); else $(this).hide();
   });
});

// Başarılı ekleme sonrası sayfayı yenile
$(document).on('submit', '#hizmet_personel_formu, #yeni_hizmet_formu', function(){
   setTimeout(function(){ location.reload(); }, 2000);
});
</script>
@endsection
