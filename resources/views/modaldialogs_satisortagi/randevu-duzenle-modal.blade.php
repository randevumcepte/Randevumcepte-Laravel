<div
   id="randevu-duzenle-modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 1000px;">
      <div class="modal-content">
         <div class="modal-body">
            <h2 class="text-blue h2 mb-10">Randevu Düzenle</h2>
            <form id="randevuduzenleform"  method="POST">
               {!!csrf_field()!!}
               <input type="hidden" name="randevu_id" id='duzenlenecek_randevu_id'>
               @if($pageindex==2)
               <input type="hidden" name="takvim_sayfasi" value="1">
               @endif
               <div class="row">
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                        <select name="adsoyad" id='randevuduzenle_musteri_id' class="form-control custom-select2" style="width: 100%;">
                        {!!$portfoy_drop!!}
                        </select>
                     </div>
                  </div>
                  <div class="col-md-2">
                     <div class="form-group">
                        <label style="visibility: hidden;width: 100%;">yenimüşteri</label>
                        <button class="btn btn-primary yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal"><i class="icon-copy fi-plus"></i>Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</button>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Tarih</label>
                        <input required placeholder="Tarih"
                           type="text"
                           class="form-control"
                           name="tarih" 
                           id='randevuduzenle_tarih' autocomplete="off"
                           />
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Saat</label>
                        <select name="saat" class="form-control" id="randevuduzenle_saat">
                           <?php $secanahtar=1; ?>
                           @for($j = strtotime(date('00:00')) ; $j < strtotime(date('23:59')); $j+=(5*60)) 
                           @if( $j< strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('baslangic_saati')) )) || $j >= strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('bitis_saati')) )) || $j < strtotime(date('H:i')) )
                           @if($j<=strtotime(date('H:i')))
                           <option  style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                           @else
                           <option style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                           @endif 
                           @else
                           <option {{($secanahtar==1) ? 'selected': ''}} value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                           <?php $secanahtar++; ?>
                           @endif
                           @endfor 
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <textarea class="form-control" name="personel_notu" id='randevuduzenle_personel_notu' placeholder="Notlar"></textarea>
                     </div>
                  </div>
               </div>
               <div class="hizmetler_bolumu_randevu_duzenleme">
               </div>
               <div class="row">
                  <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                     <div class="form-group">
                        <button type="button"  id='bir_hizmet_daha_ekle_randevu_duzenleme' class="btn btn-secondary btn-lg btn-block">
                        Bir Hizmet Daha Ekle
                        </button>
                     </div>
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                     <div class="form-group">
                        <button type="submit" disabled class="btn btn-success btn-lg btn-block">Randevuyu Güncelle</button>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>