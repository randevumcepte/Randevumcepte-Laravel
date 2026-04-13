  <div
            id="yeni_kampanya_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" >
               <div class="modal-content" style="max-height: 90%" >
                  <form id="kampanya_formu"  method="POST">
                     <div class="modal-header">
                        <h2 class="modal_baslik" id="kampanya_modal_baslik">Yeni Reklam Oluştur</h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="kampanya_id" value="">
                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                        <div class="row" >
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                              <input type="hidden"  name="paket_id" value="">
                              <label>Paket Adı</label>
                              <select id="kampanyapaket" name="kampanyapaketadi" class="form-control opsiyonelSelect" style="width: 100%;">
                                 <option></option>
                                 @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                 <option value="{{$paket->id}} ">{{$paket->paket_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                              <label>Fiyat (₺)</label>
                              <input type="tel" name="kampanyapaketfiyat" value=""  class="form-control" required>
                           </div>
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                              <label>Hizmet</label>
                              <input type="text" name="kampanyapakethizmet"  value="" class="form-control" required>
                           </div>
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                              <label>Seans</label>
                              <input type="tel"  name="kampanyapaketseans" value=""  class="form-control" required>
                           </div>
                        </div>
                        <div class="row" >
                           <div class="col-md-6">
                              <div class="col-md-12">
                                 <label>Şablon Seçiniz</label>
                                 <select class="form-control" id="kampanya_sablon_sec">
                                    <option value="">Seçiniz</option>
                                    @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                                    <option value="{{$sablon->taslak_icerik}}">{{$sablon->baslik}}</option>
                                    @endforeach
                                 </select>
                              </div>
                              <div class="col-md-12">
                                 <label>Mesaj İçeriği</label>
                                 <textarea class="form-control" style="height: 250px;" id="kampanya_sms" name="kampanya_sms"></textarea>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <label>Katılımcılar</label>
                              <div class="tab">
                                 <div class="row clearfix">
                                    <div class=" col-md-12 col-sm-12">
                                       <ul class="nav nav-tabs" role="tablist">
                                          <li class="nav-item">
                                             <a
                                                class="btn btn-outline-primary active "
                                                data-toggle="tab"
                                                style="margin-left: 20px;"
                                                href="#tumu_kampanya_katilimcilar"
                                                role="tab"
                                                aria-selected="true"
                                                >Tümü</a
                                                >
                                          </li>
                                          <li class="nav-item">
                                             <a
                                                class="btn btn-outline-primary "
                                                data-toggle="tab"
                                                style="margin-left: 20px;"
                                                href="#kampanya_grup_katilimcilar"
                                                role="tab"
                                                aria-selected="false"
                                                >Gruplar</a
                                                >
                                          </li>
                                          <li class="nav-item">
                                             <a
                                                class="btn btn-outline-primary "
                                                data-toggle="tab"
                                                style="margin-left: 20px;"
                                                href="#kampanya_hizmete_gore_katilimcilar"
                                                role="tab"
                                                aria-selected="false"
                                                >Hizmete Göre</a
                                                >
                                          </li>
                                       </ul>
                                    </div>
                                 </div>
                                 <div class="col-md-12 col-sm-12" style="margin-top:10px;">
                                    <div class="tab-content">
                                       <div class="tab-pane fade  show active" id="tumu_kampanya_katilimcilar" role="tabpanel">
                                          <div class="col-md-12"  style="overflow-y:auto; max-height: 300px ">
                                             <div class="form-group">
                                                <table class="table" id="musteri_sec_tablo">
                                                   <thead>
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input id="hepsinisec1" name="hepsinisec1" type="checkbox">
                                                         <label for="hepsinisec1"> Tümünü Seç</label>
                                                      </div>
                                                   </thead>
                                                   <tbody>
                                                      @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                                      <tr>
                                                         <td>
                                                            <div class="be-checkbox be-checkbox-color inline">
                                                               <input type="checkbox" name="kampanya_katilimci_musteriler[]" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
                                                            </div>
                                                         </td>
                                                      </tr>
                                                      @endforeach
                                                   </tbody>
                                                </table>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="tab-pane fade  show" id="kampanya_grup_katilimcilar" role="tabpanel">
                                          <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                                             <div class="form-group">
                                                <table class="table" id="grup_sec_tablo">
                                                   <thead>
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input id="hepsinisec2" name="hepsinisec2" type="checkbox">
                                                         <label for="hepsinisec2"> Tümünü Seç</label>
                                                      </div>
                                                   </thead>
                                                   <tbody>
                                                      @foreach(\App\GrupSMS::where('salon_id',$isletme->id)->where('aktif_mi',1)->get() as $gruplar)
                                                      <tr>
                                                         <td>
                                                            <div class="be-checkbox be-checkbox-color inline">
                                                               <input type="checkbox" name="grup_katilimci_musteriler[]" value="{{$gruplar->id}}"> {{$gruplar->grup_adi}}
                                                            </div>
                                                         </td>
                                                      </tr>
                                                      @endforeach
                                                   </tbody>
                                                </table>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="tab-pane fade show " id="kampanya_hizmete_gore_katilimcilar" role="tabpanel">
                                          <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                                             <div class="form-group">
                                                <table class="table">
                                                   <thead>
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input id="hepsinisec3" name="hepsinisec3" type="checkbox">
                                                         <label for="hepsinisec3"> Tümünü Seç</label>
                                                      </div>
                                                   </thead>
                                                   <tbody id="musteri_liste_hizmete_gore">
                                                      @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                                      <tr>
                                                         <td>
                                                            <div class="be-checkbox be-checkbox-color inline" >
                                                               <input type="checkbox"  name="kampanya_katilimci_musteriler[]" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
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
                                 <button type="submit" disabled class="btn btn-success btn-block">
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