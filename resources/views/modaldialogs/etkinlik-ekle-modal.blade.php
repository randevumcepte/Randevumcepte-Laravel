 <div
            id="yeni_etkinlik_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="etkinlik_formu"  method="POST">
                     <div class="modal-header">
                        <h2 class="modal_baslik">Yeni Etkinlik</h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="etkinlik_id" value="">
                         <input type="hidden" name="sube" value="{{$isletme->id}}">
                        <div class="row" data-value="0">
                           <div class="col-md-4 col-sm-6 col-xs-6 col-6">
                              <label>Etkinlik İsmi</label>
                              <input type="text"  required class="form-control" name="etkinlik_adi">
                           </div>
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                              <label>Tarih</label>
                              <input required placeholder="Tarih"
                                 type="text"
                                 class="form-control date-picker"
                                 name="etkinlik_tarihi" id="etkinlik_tarihi" autocomplete="off"
                                 />
                           </div>
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                              <label>Saat</label>
                              <input type="time" class="form-control" name="etkinlik_saati" id="etkinlik_saati" required>
                           </div>
                           <div class="col-md-2 col-sm-6 col-xs-6 col-6">
                              <label>Fiyat</label>
                              <input type="tel" name="etkinlik_fiyati"  class="form-control">
                           </div>
                        </div>
                        <div class="row" data-value="0">
                           <div class="col-md-6" data-value="0">
                              <div class="col-md-12">
                                 <label>Şablon Seçiniz</label>
                                 <select class="form-control" id="etkinlik_sablon_sec">
                                    <option value="">Seçiniz</option>
                                    @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                                    <option value="{{$sablon->taslak_icerik}}">{{$sablon->baslik}}</option>
                                    @endforeach
                                 </select>
                              </div>
                              <div class="col-md-12">
                                 <label>Mesaj İçeriği</label>
                                 <textarea class="form-control" required style="height: 250px;" id="etkinlik_sms" name="etkinlik_sms"></textarea>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <label>Katılımcılar</label>
                              <div class="tab">
                                 <div class="row clearfix">
                                    <div class=" col-md-12 col-sm-12">
                                       <ul class="nav nav-tabs" role="tablist">
                                          <li class="nav-item">
                                             <button
                                                class="btn btn-outline-primary active "
                                                data-toggle="tab"
                                                style="margin-left: 20px;"
                                                href="#tumu_etkinlik_katilimcilar"
                                                role="tab"
                                                aria-selected="true"
                                                >Tümü</button
                                                >
                                          </li>
                                          <li class="nav-item">
                                             <button
                                                class="btn btn-outline-primary "
                                                data-toggle="tab"
                                                style="margin-left: 20px;"
                                                href="#etkinlik_grup_katilimcilar"
                                                role="tab"
                                                aria-selected="false"
                                                >Gruplar</button
                                                >
                                          </li>
                                       </ul>
                                    </div>
                                 </div>
                                 <div class="col-md-12 col-sm-12" style="margin-top:10px;">
                                    <div class="tab-content">
                                       <div class="tab-pane fade  show active" id="tumu_etkinlik_katilimcilar" role="tabpanel">
                                          <div class="col-md-12"  style="overflow-y:auto; max-height: 300px ">
                                             <div class="form-group">
                                                <table class="data-table table stripe hover nowrap" id="musteri_sec_tablo">
                                                   <thead>
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input id="hepsinisec4" name="hepsinisec4" type="checkbox">
                                                         <label for="hepsinisec4"> Tümünü Seç</label>
                                                      </div>
                                                   </thead>
                                                   <tbody>
                                                      @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                                      <tr>
                                                         <td>
                                                            <div class="be-checkbox be-checkbox-color inline">
                                                               <input type="checkbox" name="etkinlik_katilimci_musteriler[]" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
                                                            </div>
                                                         </td>
                                                      </tr>
                                                      @endforeach
                                                   </tbody>
                                                </table>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="tab-pane fade  show" id="etkinlik_grup_katilimcilar" role="tabpanel">
                                          <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                                             <div class="form-group">
                                                <table class="data-table table stripe hover nowrap" id="grup_sec_tablo">
                                                   <thead>
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input id="hepsinisec5" name="hepsinisec5" type="checkbox">
                                                         <label for="hepsinisec5"> Tümünü Seç</label>
                                                      </div>
                                                   </thead>
                                                   <tbody>
                                                      @foreach(\App\GrupSMS::where('salon_id',$isletme->id)->where('aktif_mi',1)->get() as $gruplar)
                                                      <tr>
                                                         <td>
                                                            <div class="be-checkbox be-checkbox-color inline">
                                                               <input type="checkbox" name="etkinlik_grup_katilimci_musteriler[]" value="{{$gruplar->id}}"> {{$gruplar->grup_adi}}
                                                            </div>
                                                         </td>
                                                      </tr>
                                                      @endforeach
                                                   </tbody>
                                                </table>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="modal-footer" style="display:block">
                           <div class="row">
                              <div class="col-7 col-xs-7 col-sm-7">
                                 <button type="submit"  class="btn btn-success btn-block">
                                 Kaydet & Gönder </button>
                              </div>
                              <div class="col-5 col-xs-5 col-sm-5">
                                 <button  
                                    type="button"
                                    class="btn btn-danger modal_kapat btn-block"
                                    data-dismiss="modal"
                                    > <i class="fa fa-times"></i>
                                 Kapat
                                 </button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>