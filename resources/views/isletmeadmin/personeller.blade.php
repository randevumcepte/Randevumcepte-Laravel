@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="card-box mb-30">
             
            <div class="pb-20" style="padding-top:20px">
              <table class="data-table table stripe hover nowrap" id="personeller">
                <thead>
                  <tr>
                    <th>Personel</th>
                    <th>Hesap Tipi</th>
                    <th>Telefon</th>
                    <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($personeller as $key => $value)
                    @if(Auth::guard('isletmeyonetim')->user()->personel_id != $value->id)
                      <tr>
                          <td>
                             <input type="hidden" id="personeladi'.$value->id.'" value="{{$value->personel_adi}}'">{{$value->personel_adi}}
                          </td>
                          <td>
                              
                          </td>
                          <td>
                            {{\App\IsletmeYetkilileri::where('personel_id',$value->id)->value('gsm1')}}
                          </td>
                          <td>
                              <div class="dropdown">
                                <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                  href="#"
                                  role="button"
                                  data-toggle="dropdown"
                                >
                                  <i class="dw dw-more"></i>
                                </a>
                                <div
                                  class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                                >
                                  <a class="dropdown-item" href="/isletmeyonetim/personeldetay/{{$value->id}}"
                                    ><i class="dw dw-eye"></i> Detaylar</a
                                  >
                                  <a class="dropdown-item" href="#"
                                    ><i class="icon-copy fa fa-key"></i>Yetkiler</a
                                  >
                                   <a class="dropdown-item" href="#"
                                    ><i class="icon-copy bi bi-gear"></i>Hizmetler</a
                                  >
                                   <a class="dropdown-item" href="#"
                                    ><i class="icon-copy dw dw-password"></i>Şifre Değiştir & Gönder</a
                                  >
                                  <a class="dropdown-item" href="#"
                                    ><i class="dw dw-delete-3"></i> Pasif Yap</a
                                  >
                                </div>
                              </div>
                          </td>
                      </tr>
                      @endif
                    @endforeach
                   
                </tbody>
              </table>
            </div>
          </div>

 
         <div id="md-scale"  class="modal-container modal-effect-1" style="display: none;overflow-y: auto; max-height: 500px">


                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px;font-weight: bold;">Yeni Personel Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                        <form id="yenipersonelbilgiekle" method="post" enctype="multipart/form-data">
                          {!!csrf_field()!!}
                          <div class="row">
                          <div class="col-md-6">
                            <h3 style="font-size: 15px;font-weight: bold;">Personel Bilgileri</h3>
                          <div class="form-group">
       
                              <label>Personel Adı</label>
                              <input id="personeladi_yeni" name="personeladi_yeni" required placeholder="Personel adı..." class="form-control input-xs">
                            </div>
                            <div class="form-group">
       
                              <label>Unvan</label>
                              <input id="unvan_yeni" name="unvan_yeni" required placeholder="Unvan..." class="form-control input-xs">
                            </div>
                            <div class="form-group">
       
                              <label>Cinsiyet</label>
                              <select id="cinsiyet_yeni" name="cinsiyet_yeni" required class="form-control input-xs">
                                <option value="0">Kadın</option>
                                <option value="1">Erkek</option>
                              </select>
                            </div>
                            <div class="form-group">
                              <label>Şube</label>
                               <select id="personel_sube" name="personel_sube" required class="form-control input-xs">
                                  @foreach($subeler as $sube)
                                  <option value="{{$sube->id}}">{{$sube->sube}}</option>

                                  @endforeach
                               </select>
                            </div>
                            <div class="form-group">
                              <label>Profil Resmi</label>
                              <input type="file" id="profilresmi_yeni" name="profilresmi_yeni" class="form-control input-xs">
                            </div>
                           
                         </div>
                         <div class="col-md-6">
                              <h3 style="font-size: 15px;font-weight: bold;">Personel Sistem Kullanıcı Bilgileri (Opsiyonel)</h3>
                               <div class="form-group">
       
                              <label>E-posta</label>
                              <input type="email" id="eposta_yeni" name="eposta_yeni" placeholder="E-posta..." class="form-control input-xs">
                            </div>
                            <div class="form-group">
       
                              <label>Cep Telefon (başında 0 olmadan 5XXXXXXXXX şeklinde)</label>
                              <input type="text" id="ceptelefon_yeni" maxlength="10" pattern="[0-9]*" name="ceptelefon_yeni" placeholder="Cep Telefonu..." class="form-control input-xs">
                            </div>
                              <div class="form-group">
       
                              <label>Şifre</label>
                              <input type="password" id="sifre_yeni" name="sifre_yeni" placeholder="Şifre..." class="form-control input-xs">
                            </div>
                                <div class="form-group">
       
                              <label>Şifre</label>
                              <input type="password" id="sifre_yeni_tekrar" name="sifre_yeni_tekrar" placeholder="Şifre (tekrar)..." class="form-control input-xs">
                            </div>
                            <div class="form-group">
                              <label>Yetkiler</label>
                               <div class="be-radio">
                               <input type="radio" checked name="sistemyetki_yeni"  id="sistemyetkiyeni1" value="0">

                               <label for="sistemyetkiyeni1">Sadece randevu ve kasa defteri yönetimi.</label>
                             </div>
                             <div class="be-radio">

                               <input type="radio" name="sistemyetki_yeni" id="sistemyetkiyeni2" value="1">
                                <label for="sistemyetkiyeni2">Tüm yetkiler</label>
                             </div>
                            </div>
                         </div>
                       </div>
                       <div class="row">
                          <div class="col-md-12">
                              <h3 style="font-size: 15px; font-weight: bold;">Personel Çalışma Saatleri (Opsiyonel)</h3>
                           <table class="table table table-striped table-hover">
                            <tbody>
                               <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                   
                                  </label>
                                </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati1" style="float: left; width: 80px">   
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati1"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                    
                                  </label>
                                </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati2" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati2"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                       
                                  </label>
                                </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati3" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati3"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                           
                                  </label>
                                </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati4" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati4"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                  
                                  </label>
                                </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati5" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati5"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                    
                                  </label>
                                </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati6" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati6"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <div class="be-checkbox be-checkbox-color inline">
                                  <input type="checkbox" id="calisiyor7" value="00:00" name="calisiyor7"><label for="calisiyor7">
                               
                                  </label>
                                </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                <input type="time" class="form-control input-xs" value="00:00" name="baslangicsaati7" style="float: left; width: 80px"> 
                                <input type="time" class="form-control input-xs" value="00:00" name="bitissaati7"  style="float: left; width: 80px">
                              </td>
                            </tr>
                            </tbody>
                           </table>
                          </div>
                          <div class="col-md-12">
                            <h3 style="font-size: 15px; font-weight: bold;">Personelin Sunduğu Hizmetler (Opsiyonel)</h3>
                            <div class="form-group">
                              <select multiple name="sunulanhizmetler_yeni[]" id="sunulanhizmetler_yeni" class="select2 input-xs">
                                @foreach(\App\Hizmetler::all() as $hizmetler)
                                <option value="{{$hizmetler->id}}">{{$hizmetler->hizmet_adi}}</option>
                                  @endforeach
                              </select>
                            </div>
                          </div>
                       </div>
                           <div class="text-center">
                            <div class="xs-mt-50">
                            <button type="button" id="modalkapat1" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="submit"  class="btn btn-primary">Ekle</button>
                          </div>
                        </div></form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                 <div id="md-scale2" class="modal-container modal-effect-1" style="overflow-y: auto; max-height: 500px; display: none;">


                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px;font-weight: bold;">Personel İçin Yetki Oluştur</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                        <form id="personelyetkiolustur" method="get">
                          {!!csrf_field()!!}
                          <input type="hidden" id="yetkili_personelid" name="yetkili_personelid">
                          <div class="row">
                           
                         <div class="col-md-12">
                              <h3 style="font-size: 15px;font-weight: bold;"><span id="yetkilipersoneladi"></span> İçin Sistem Kullanıcı Bilgileri</h3>
                               <div class="form-group">
       
                              <label>E-posta</label>
                              <input type="email" id="eposta_yeni2" name="eposta_yeni" placeholder="E-posta..." class="form-control input-xs">
                            </div>
                            <div class="form-group">
       
                              <label>Cep Telefon (başında 0 olmadan 5XXXXXXXXX şeklinde)</label>
                              <input type="text" id="ceptelefon_yeni2" required  maxlength="10" pattern="[0-9]*" name="ceptelefon_yeni" placeholder="Cep Telefonu..." class="form-control input-xs">
                            </div>
                              <div class="form-group">
       
                              <label>Şifre</label>
                              <input type="password" id="sifre_yeni2" required name="sifre_yeni" placeholder="Şifre..." class="form-control input-xs">
                            </div>
                                <div class="form-group">
       
                              <label>Şifre</label>
                              <input type="password" id="sifre_yeni_tekrar2" required name="sifre_yeni_tekrar" placeholder="Şifre (tekrar)..." class="form-control input-xs">
                            </div>
                            <div class="form-group">
                              <label>Yetkiler</label>
                               <div class="be-radio">
                               <input type="radio" checked name="sistemyetki_yeni2"  id="sistemyetkiyeni1_2" value="0">

                               <label for="sistemyetkiyeni1_2">Sadece randevu ve kasa defteri yönetimi.</label>
                             </div>
                             <div class="be-radio">

                               <input type="radio" name="sistemyetki_yeni2" id="sistemyetkiyeni2_2" value="1">
                                <label for="sistemyetkiyeni2_2">Tüm yetkiler</label>
                             </div>
                            </div>
                         </div>
                       </div>
                        
                           <div class="text-center">
                            <div class="xs-mt-50">
                            <button type="button" id="modalkapat2" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="submit"  class="btn btn-primary">Ekle</button>
                          </div>
                        </div></form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                  
                  <div class="modal-overlay"></div>
                  <div id="hata"></div>
@endsection