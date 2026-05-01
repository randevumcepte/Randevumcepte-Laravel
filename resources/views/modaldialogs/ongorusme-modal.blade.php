<style>
#ongorusme-modal .modal-dialog { max-width:640px; }
.og-modal { border-radius:12px; border:0; overflow:hidden; box-shadow:0 20px 50px rgba(92,0,142,.18); }
.og-header {
   display:flex; align-items:center; gap:10px;
   padding:12px 18px;
   background:#faf5ff;
   border-bottom:1px solid #ede1f7;
   position:relative;
}
.og-header .og-icon {
   width:34px; height:34px; border-radius:9px;
   background:#5C008E; color:#fff;
   display:inline-flex; align-items:center; justify-content:center;
   font-size:15px; flex-shrink:0;
}
.og-header h2 { margin:0; font-size:15px; color:#3a1a52; font-weight:700; }
.og-header p { margin:1px 0 0; font-size:11.5px; color:#7c6c8a; }
.og-close {
   position:absolute; top:8px; right:10px;
   background:transparent; border:0; font-size:20px; line-height:1;
   color:#9d8ba8; cursor:pointer; transition:color .15s, background .15s;
   width:26px; height:26px; border-radius:6px;
}
.og-close:hover { color:#ef4444; background:#fdecec; }

.og-body { padding:12px 18px 4px; max-height:62vh; overflow-y:auto; background:#fff; }

.og-section {
   margin-bottom:10px;
   padding:10px 12px 4px;
   background:#fbfafd;
   border:1px solid #ece6f3;
   border-radius:9px;
}
.og-section__title {
   font-size:10.5px; font-weight:700;
   color:#5C008E;
   text-transform:uppercase; letter-spacing:.4px;
   margin-bottom:7px; display:flex; align-items:center; gap:5px;
}
.og-section .form-group { margin-bottom:7px; }
.og-section label { font-size:11.5px; font-weight:600; color:#3a2e57; margin-bottom:2px; display:block; }
/* Sadece duz inputlar/native selectler — select2 dropdown elementlerine asla dokunma */
.og-section input.form-control:not(.select2-search__field),
.og-section select.form-control,
.og-section textarea.form-control {
   border-radius:7px; border:1px solid #dfd6ea; min-height:32px;
   font-size:12.5px; padding:4px 10px;
}
.og-section textarea.form-control { height:auto; min-height:50px; }
.og-section input.form-control:not(.select2-search__field):focus,
.og-section select.form-control:focus,
.og-section textarea.form-control:focus {
   border-color:#5C008E; box-shadow:0 0 0 3px rgba(92,0,142,.1);
}
/* Select2 — sadece gorunur kismi ayarla, icindeki search input'a dokunma */
.og-section .select2-container--default .select2-selection--single {
   border-radius:7px; border:1px solid #dfd6ea; height:32px;
}
.og-section .select2-container--default .select2-selection--single .select2-selection__rendered {
   line-height:30px; padding-left:10px; font-size:12.5px; color:#3a2e57;
}
.og-section .select2-container--default .select2-selection--single .select2-selection__arrow { height:30px; }
.og-section .select2-container--default.select2-container--focus .select2-selection--single,
.og-section .select2-container--default.select2-container--open .select2-selection--single {
   border-color:#5C008E; box-shadow:0 0 0 3px rgba(92,0,142,.1);
}
.og-hint { display:block; color:#9d8ba8; font-size:10.5px; margin-top:2px; }

.og-footer {
   display:flex; justify-content:flex-end; gap:8px;
   padding:10px 18px; border-top:1px solid #ece6f3;
   background:#fbfafd;
}
.og-btn-save {
   background:#5C008E; color:#fff !important;
   padding:7px 18px; border-radius:8px; font-weight:700; font-size:13px;
   border:0; box-shadow:0 4px 10px rgba(92,0,142,.25);
   transition:background .15s;
}
.og-btn-save:hover { background:#48006e; }
.og-btn-cancel {
   background:#fff; color:#7c6c8a !important;
   padding:7px 16px; border-radius:8px; font-weight:600; font-size:13px;
   border:1px solid #dfd6ea;
}
.og-btn-cancel:hover { background:#f5f0fa; color:#3a2e57 !important; }

@media (max-width:600px) {
   #ongorusme-modal .modal-dialog { max-width:96%; margin:10px auto; }
   .og-body { padding:10px 12px; max-height:62vh; }
   .og-section { padding:9px 10px 4px; }
   .og-header { padding:10px 14px; gap:8px; }
   .og-header .og-icon { width:30px; height:30px; font-size:13px; }
   .og-footer { padding:8px 12px; }
}
</style>

@php
   // Giris yapan kullanicinin (Auth) bu salondaki personel kaydi — yoksa hesap sahibine fallback
   $_authUserId = \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->check()
      ? \Illuminate\Support\Facades\Auth::guard('isletmeyonetim')->user()->id
      : null;
   $_currentPersonel = null;
   if ($_authUserId) {
      $_currentPersonel = \App\Personeller::where('salon_id',$isletme->id)
         ->where('yetkili_id',$_authUserId)->first();
   }
   if (!$_currentPersonel) {
      // fallback: hesap sahibi
      $_currentPersonel = \App\Personeller::where('salon_id',$isletme->id)
         ->where('role_id',1)->first();
   }
   $_currentPersonelId = $_currentPersonel ? $_currentPersonel->id : null;
   $_currentPersonelAdi = $_currentPersonel ? $_currentPersonel->personel_adi : null;
@endphp

<div id="ongorusme-modal" class="modal fade" tabindex="-1">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content og-modal">
         <form id="ongorusmeformu" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="on_gorusme_id" id="on_gorusme_id" value="">
            <input type="hidden" name="sube" value="{{$isletme->id}}">

            <div class="og-header">
               <div class="og-icon"><i class="fa fa-user-plus"></i></div>
               <div>
                  <h2 class="modal_baslik">Yeni Ön Görüşme</h2>
                  <p>Müşteri ile yapılacak ön görüşmeyi planlayın.</p>
               </div>
               <button type="button" class="og-close modal_kapat" data-dismiss="modal" aria-label="Kapat">&times;</button>
            </div>

            <div class="modal-body og-body">

               {{-- 1) MÜŞTERİ BİLGİLERİ --}}
               <div class="og-section">
                  <div class="og-section__title"><i class="fa fa-user"></i> Müşteri Bilgileri</div>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label>Mevcut @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif (kayıtlıysa)</label>
                           <select name="musteri" id="musteri_select_list" class="form-control opsiyonelSelect musteri_secimi" style="width:100%">
                              <option></option>
                           </select>
                           <small class="og-hint">Daha önce kaydedilmişse buradan seçin, aksi halde aşağıdaki alanları doldurun.</small>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Ad Soyad</label>
                           <input type="text" required name="ad_soyad" id="ad_soyad" class="form-control" placeholder="Adı ve soyadı">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Telefon</label>
                           <input type="tel" required name="telefon" id="telefon" data-inputmask=" 'mask' : '5999999999'" class="form-control" placeholder="5XX XXX XX XX">
                        </div>
                     </div>
                     <div class="col-md-7">
                        <div class="form-group">
                           <label>E-mail <span style="color:#94a3b8;font-weight:500">(opsiyonel)</span></label>
                           <input type="email" name="email" id="email" class="form-control" placeholder="ornek@email.com">
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Cinsiyet</label>
                           <select name="cinsiyet" id="cinsiyet" class="form-control">
                              <option value="0">Kadın</option>
                              <option value="1">Erkek</option>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>

               {{-- 2) GÖRÜŞME DETAYI --}}
               <div class="og-section">
                  <div class="og-section__title"><i class="fa fa-clipboard-list"></i> Görüşme Detayı</div>
                  <div class="row">
                     <div class="col-md-7">
                        <div class="form-group">
                           <label>Ön Görüşme Sebebi</label>
                           <input type="text" name="paket_urun" id="paket" class="form-control" placeholder="Örn. Saç bakımı, lazer epilasyon, cilt analizi...">
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Referans</label>
                           <select id="musteri_tipi" name="musteri_tipi" class="form-control">
                              <option value="0">Yok</option>
                              <option value="1">İnternet</option>
                              <option value="2">Reklam</option>
                              <option value="3">Instagram</option>
                              <option value="4">Facebook</option>
                              <option value="5">Tanıdık</option>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>

               {{-- 3) TARİH & PERSONEL --}}
               <div class="og-section">
                  <div class="og-section__title"><i class="fa fa-calendar-alt"></i> Tarih & Personel</div>
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label>Tarih <span style="color:#dc2626">*</span></label>
                           <input type="text" required name="ongorusme_tarihi" id="ongorusme_tarihi" class="form-control date-picker" value="" placeholder="GG-AA-YYYY" autocomplete="off">
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                           <label>Saat <span style="color:#dc2626">*</span></label>
                           <select required id='ongorusme_saati' name="ongorusme_saati" class="form-control">
                              <option value="">Seçiniz</option>
                              @for($j = strtotime(date('07:00')); $j < strtotime(date('23:15')); $j += (15*60))
                                 <option value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                              @endfor
                           </select>
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Görüşmeyi Yapan <span style="color:#dc2626">*</span></label>
                           <select required name="gorusmeyi_yapan" id="gorusmeyi_yapan" class="form-control custom-select2 opsiyonelSelect personel_secimi" style="width:100%">
                              @if($_currentPersonelId)
                                 <option value="{{$_currentPersonelId}}" selected>{{$_currentPersonelAdi}}</option>
                              @else
                                 <option></option>
                              @endif
                           </select>
                        </div>
                     </div>
                     <div class="col-md-12">
                        <div class="form-group" style="margin-bottom:0">
                           <label>Açıklama <span style="color:#94a3b8;font-weight:500">(opsiyonel)</span></label>
                           <textarea name="aciklama" id="aciklama" class="form-control" rows="2" placeholder="Görüşme ile ilgili kısa notlar..."></textarea>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="og-footer">
               <button type="button" class="btn og-btn-cancel modal_kapat" data-dismiss="modal">Vazgeç</button>
               <button type="submit" class="btn og-btn-save"><i class="fa fa-check"></i> Kaydet</button>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
   // Bootstrap modal'in Select2 search input'undan focus calmasini engelle.
   // Aksi halde "musteri", "personel" dropdown'unda yazi yazilamiyor.
   $(document).on('shown.bs.modal', '#ongorusme-modal', function () {
      $(document).off('focusin.bs.modal');
   });
</script>
