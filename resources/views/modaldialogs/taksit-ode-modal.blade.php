   <div
         class="modal fade bs-example-modal-lg"
         id="taksit_onay_modal"
         >
         <
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="width:100%">
               <form id='taksit_vade_guncelleme' method="POST">
                  {!!csrf_field()!!}
                  <input name="vade_id" id='taksit_vade_id' type="hidden">
                  <input name="sube" value="{{$isletme->id}}" type="hidden">
                  <div class="modal-header">
                     <h2 class="modal-title">
                        Taksit Güncelleme
                     </h2>
                     <button id='taksit_odeme_ekrani_kapat'
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body">
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Ödeme Tarihi</label>
                              <input type="text" name="planlanan_odeme_tarihi" id='taksit_vade_odeme_tarihi' required class="form-control date-picker"  value="" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Notlar</label>
                              <input type="text" name="notlar" required class="form-control">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value=0>
                           <div class="col-md-12">
                              <button type="button" id='taksit_vade_guncelle' class="btn btn-primary btn-lg btn-block">
                              Vadeyi Güncelle
                              </button>
                           </div>
                           <div class="col-md-6">
                              <button style="display: none;" type="button" id='taksit_vade_odendi_olarak_isaretle' class="btn btn-success btn-lg btn-block">
                              Ödemeyi Tamamla
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>