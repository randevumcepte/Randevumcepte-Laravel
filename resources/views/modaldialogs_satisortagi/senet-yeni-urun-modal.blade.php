 <div
         id="senet_yeni_urun_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="urun_satisi_senet"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Ürün Satışı</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" id="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="adisyon_id">
                     <div class="urunler_bolumu_senet">
                        <div class="row" data-value="0">
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>Ürün</label>
                                 <select name="urunyenisenet[]" class="form-control custom-select2" style="width: 100%;">
                                 {!!$urun_drop!!}
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>Adet</label>
                                 <input type="tel" required name="urun_adedi_senet[]" value="1" class="form-control">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>Fiyat</label>
                                 <input type="tel" required name="urun_fiyatisenet[]" value="{{(\App\Urunler::where('salon_id',$isletme->id)->first()!==null) ? \App\Urunler::where('salon_id',$isletme->id)->first()->fiyat : ''}}" class="form-control">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                 <button type="button" name="urun_senetten_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" id="bir_urun_daha_ekle_senet" class="btn btn-secondary btn-lg btn-block">
                              Bir Ürün Daha Ekle
                              </button>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Satıcı</label>
                              <select name="urun_satici_senet" class="form-control custom-select2" style="width: 100%;">
                                 @if(Auth::guard('satisortakligi')->user()->hasRole('Personel'))
                                 <option selected value="{{Auth::guard('satisortakligi')->user()->personel_id}}">{{Auth::guard('satisortakligi')->user()->name}}</option>
                                 @else
                                 {!!$personel_drop!!}
                                 @endif
                              </select>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block;">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" disabled class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button id="senet_urun_modal_kapat"
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           <i class="fa fa-times"></i> Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>