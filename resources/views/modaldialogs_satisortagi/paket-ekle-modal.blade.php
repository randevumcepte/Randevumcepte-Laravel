<div
         id="paket-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1100px; max-height: 90%;">
               <form id="paket_formu"  method="POST">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <h2 class="text-blue h2 mb-10">Yeni Paket</h2>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Paket Adı</label>
                              <input type="text" required name="adpaket" class="form-control" required>
                           </div>
                        </div>
                        <div class="paket_hizmetler_bolumu" style="margin-left: 20px">
                           <div class="row" data-value="0">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Hizmet</label>
                                    <select name="hizmetler[]" id="hizmetler" class="form-control opsiyonelSelect" style="width:100%">
                                       <option></option>
                                    {!!$hizmet_drop!!}
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-3 col-5 col-xs-5 col-sm-5">
                                 <div class="form-group">
                                    <label>Seans</label>
                                    <input type="tel" required name="seanslar[]" class="form-control" required>
                                 </div>
                              </div>
                              <div class="col-md-4 col-5 col-xs-5 col-sm-5">
                                 <div class="form-group">
                                    <label>Fiyat (₺)</label>
                                    <input type="tel" name="fiyatlar[]" class="form-control" required>
                                 </div>
                              </div>
                              <div class="col-md-1 col-2 col-xs-2 col-sm-2">
                                 <div class="form-group">
                                    <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                    <button type="button" name="paket_hizmet_formdan_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" id="paket_hizmet_daha_ekle" class="btn btn-secondary btn-lg btn-block">
                              Pakete Bir Hizmet Daha Ekle
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" disabled class="btn btn-success btn-lg btn-block">
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button id="modal_kapat_paket"
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>