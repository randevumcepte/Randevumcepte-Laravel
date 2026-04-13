  <div
            id="paket-duzenle-modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" >
               <div class="modal-content" style="max-width:1100px; max-height: 90%;">
                  <form id="paket_formu_duzenleme" method="POST">
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="paket_id" id='paket_id_duzenleme'>
                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                        <h2 class="text-blue h2 mb-10">Paket Düzenle</h2>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label>Paket Adı</label>
                                 <input type="text" required name="adpaket" id="paketad" class="form-control" required>
                              </div>
                           </div>
                           <div class="paket_hizmetler_bolumu_duzenleme" style="margin-left: 20px">
                           </div>
                           <div class="col-md-12">
                              <div class="form-group">
                                 <button type="button" id="paket_hizmet_daha_ekle_duzenleme" class="btn btn-secondary btn-lg btn-block">
                                 Pakete Bir Hizmet Daha Ekle
                                 </button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row">
                           <div class="col-md-6">
                              <button type="submit" disabled class="btn btn-success btn-lg btn-block">
                              Kaydet
                              </button>
                           </div>
                           <div class="col-md-6">
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