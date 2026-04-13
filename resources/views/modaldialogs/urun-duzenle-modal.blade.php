 <div
         id="urun-modal-duzenle"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content" style="width: 950px; max-height: 90%;">
               <form id="urun_formu_duzenle"  method="POST">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input name="sube" value="{{$isletme->id}}" type="hidden">
                     <input type="hidden" name="urun_id_duzenle" id="urun_id_duzenle" value="0">
                     <h2 class="text-blue h2 mb-10">Ürün Güncelleme</h2>
                     <div class="form-group">
                        <label>Ürün Adı</label>
                        <input type="text" required name="urun_ad" id="urun_ad" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Fiyat</label>
                        <input type="tel" required name="fiyat_duzenle" id="fiyat_duzenle" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Stok Adedi</label>
                        <input type="tel" required name="stok_aded" id="stok_aded" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Düşük Stok Sınırı</label>
                        <input type="tel"  name="dusuk_stok_siniri" id="dusuk_stok_siniri_duzenle" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Barkod</label>
                        <input type="text" name="barkod_duzenle" data-inputmask =" 'mask' : '9999999999999'"  id="barkod_duzenle" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button  
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal" 
                              ><i class="fa fa times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>