<div
         id="urun-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content" style="width: 950px; max-height: 90%;">
               <form id="urun_formu"  method="POST">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube"   value="{{$isletme->id}}">
                     <input type="hidden" name="urun_id" id="urun_id" value="0">
                     <h2 class="text-blue h2 mb-10" id="urun_modal_baslik">Yeni Ürün</h2>
                     <div class="form-group">
                        <label>Ürün Adı</label>
                        <input type="text" required name="urun_adi" id="urun_adi" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Fiyat</label>
                        <input type="tel" required name="fiyat" id="fiyat" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Stok Adedi</label>
                        <input type="tel" required name="stok_adedi" id="stok_adedi" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Düşük Stok Sınırı</label>
                        <input type="tel" required name="dusuk_stok_siniri" id="dusuk_stok_siniri" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Barkod</label>
                        <input type="text" name="barkod" id="barkod" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" disabled class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button id="modal_kapat"
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal" 
                              ><i class="fa fa times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>