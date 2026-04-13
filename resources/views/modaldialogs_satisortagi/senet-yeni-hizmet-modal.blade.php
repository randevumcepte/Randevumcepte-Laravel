 <div
            id="senet_yeni_hizmet_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width:1200px">
               <div class="modal-content">
                  <form id="senet_hizmet_formu"  method="POST">
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <h2 class="text-blue h2 mb-10" id="adisyon_hizmet_modal_baslik">Yeni Hizmet Satışı</h2>
                        <div class="hizmetler_bolumu_senet">
                           <div class="row" data-value="0">
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label>İşlem Tarihi</label>
                                    <input name="senetislemtarihiyeni[]" required class="form-control" type="text" value="{{date('Y-m-d')}}" autocomplete="off">
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label>İşlem Saati</label>
                                    <input name="senetislemsaatiyeni[]" required class="form-control" type="time" value="{{date('H:i')}}" autocomplete="off">
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label>Personel</label>
                                    <select name="senethizmetpersonelleriyeni[]" class="form-control opsiyonelSelect" style="width: 100%;">
                                       <option></option>
                                       @if(Auth::guard('satisortakligi')->user()->hasRole('Personel'))
                                       <option selected value="{{Auth::guard('satisortakligi')->user()->personel_id}}">{{Auth::guard('satisortakligi')->user()->name}}</option>
                                       @else
                                       {!!$personel_drop!!}
                                       @endif
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Hizmet</label>
                                    <select name="senethizmetleriyeni[]" class="form-control opsiyonelSelect" style="width: 100%;">
                                       <option></option>
                                    {!!$hizmet_drop!!}
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-1">
                                 <div class="form-group">
                                    <label>Süre (dk)</label>
                                    <input type="tel" class="form-control" required name="senethizmetsuresi[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("sure_dk")}}'>
                                 </div>
                              </div>
                              <div class="col-md-1">
                                 <div class="form-group">
                                    <label>Fiyat ₺</label>
                                    <input type="tel" class="form-control" required name="senethizmetfiyati[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("baslangic_fiyat")}}'>
                                 </div>
                              </div>
                              <div class="col-md-1">
                                 <div class="form-group">
                                    <label style="visibility: hidden;">Kaldır</label>
                                    <button type="button" name="hizmet_formdan_sil_senet"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <button type="button" id="bir_hizmet_daha_ekle_senet" class="btn btn-secondary btn-lg btn-block">
                                 Bir Hizmet Daha Ekle
                                 </button>
                              </div>
                           </div>
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
                              <button
                                 type="button" id='senet_hizmet_modal_kapat'
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