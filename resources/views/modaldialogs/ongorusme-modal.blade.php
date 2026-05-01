<style>
.og-modal { border-radius:14px; border:0; overflow:hidden; box-shadow:0 30px 60px rgba(15,23,42,.18); }
.og-header {
   display:flex; align-items:center; gap:14px;
   padding:18px 24px;
   background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 100%);
   border-bottom:1px solid #e2e8f0;
   position:relative;
}
.og-header .og-icon {
   width:46px; height:46px; border-radius:12px;
   background:#4f46e5; color:#fff;
   display:inline-flex; align-items:center; justify-content:center;
   font-size:20px; flex-shrink:0;
}
.og-header h2 { margin:0; font-size:17px; color:#0f172a; font-weight:700; }
.og-header p { margin:2px 0 0; font-size:12.5px; color:#64748b; }
.og-close {
   position:absolute; top:12px; right:14px;
   background:transparent; border:0; font-size:24px; line-height:1;
   color:#94a3b8; cursor:pointer; transition:color .2s;
   width:32px; height:32px; border-radius:8px;
}
.og-close:hover { color:#ef4444; background:#fee2e2; }

.og-body { padding:16px 24px 8px; max-height:70vh; overflow-y:auto; background:#fff; }

.og-section {
   margin-bottom:14px;
   padding:14px 16px 8px;
   background:#f8fafc;
   border:1px solid #e2e8f0;
   border-radius:10px;
}
.og-section__title {
   font-size:11.5px; font-weight:700;
   color:#4f46e5;
   text-transform:uppercase; letter-spacing:.5px;
   margin-bottom:10px; display:flex; align-items:center; gap:6px;
}
.og-section .form-group { margin-bottom:10px; }
.og-section label { font-size:12.5px; font-weight:600; color:#334155; margin-bottom:4px; display:block; }
.og-section .form-control,
.og-section .select2-selection {
   border-radius:8px !important; border-color:#e2e8f0 !important; min-height:38px;
   font-size:13.5px;
}
.og-section .form-control:focus { border-color:#4f46e5 !important; box-shadow:0 0 0 3px rgba(79,70,229,.12) !important; }
.og-hint { display:block; color:#94a3b8; font-size:11.5px; margin-top:3px; }

.og-footer {
   display:flex; justify-content:flex-end; gap:10px;
   padding:14px 24px; border-top:1px solid #e2e8f0;
   background:#f8fafc;
}
.og-btn-save {
   background:#4f46e5; color:#fff !important;
   padding:10px 22px; border-radius:10px; font-weight:700;
   border:0; box-shadow:0 4px 10px rgba(79,70,229,.25);
   transition:background .15s;
}
.og-btn-save:hover { background:#4338ca; }
.og-btn-cancel {
   background:#fff; color:#64748b !important;
   padding:10px 18px; border-radius:10px; font-weight:600;
   border:1px solid #e2e8f0;
}
.og-btn-cancel:hover { background:#f1f5f9; color:#334155 !important; }

@media (max-width:600px) {
   .og-body { padding:14px 16px; max-height:65vh; }
   .og-section { padding:12px 12px 6px; }
   .og-header { padding:14px 18px; gap:10px; }
   .og-header .og-icon { width:38px; height:38px; font-size:16px; }
   .og-footer { padding:12px 16px; }
}
</style>

<div id="ongorusme-modal" class="modal fade" tabindex="-1">
   <div class="modal-dialog modal-dialog-centered modal-lg">
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
                           <select name="paket_urun" id="paket" class="form-control opsiyonelSelect" style="width:100%">
                              <option></option>
                              @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                 <option value="{{$paket->id}}">{{$paket->paket_adi}}</option>
                              @endforeach
                              @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $urun)
                                 <option value="urun-{{$urun->id}}">{{$urun->urun_adi}}</option>
                              @endforeach
                              @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmet)
                                 <option value="hizmet-{{$hizmet->hizmetler->id}}">{{$hizmet->hizmetler->hizmet_adi}}</option>
                              @endforeach
                           </select>
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
                           <label>Tarih</label>
                           <input type="text" name="ongorusme_tarihi" id="ongorusme_tarihi" class="form-control date-picker" value="{{date('Y-m-d')}}" autocomplete="off">
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                           <label>Saat</label>
                           <select id='ongorusme_saati' name="ongorusme_saati" class="form-control">
                              @for($j = strtotime(date('07:00')); $j < strtotime(date('23:15')); $j += (15*60))
                                 <option value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                              @endfor
                           </select>
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Görüşmeyi Yapan</label>
                           <select name="gorusmeyi_yapan" id="gorusmeyi_yapan" class="form-control custom-select2 opsiyonelSelect personel_secimi" style="width:100%">
                              <option></option>
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
