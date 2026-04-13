 


 @if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
 @section('content')
        <div class="page-head" style="position: relative; width: 100%;">
          <h2 class="page-head-title" style="float: left;">Randevular</h2>
          <span style="float: left;"><button id="yenirandevuekle" type="button" data-modal="md-scale2" class="btn btn-space btn-success md-trigger" style="margin-top: 10px;margin-left: 20px" >Yeni Randevu Ekle</button></span>
          <button type="button" id="randevudetayigetir" style="display: none" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Modal</button>
            <button type="button" id="randevuraporformunuac" style="display: none" data-modal="md-scale3" class="btn btn-space btn-primary md-trigger">Modal</button>
        </div>
        <div class="main-content container-fluid" style="float:left; position:relative;width:100%;">
          <div class="row full-calendar">
            <div class="col-md-12">
               <div class="panel panel-default panel-fullcalendar">
                <div class="panel-heading">Filtrele</div>
                <div class="panel-body">
                  <div class="row">
                    @if(Auth::guard('isletmeyonetim')->user()->is_admin)
                    @if($subeler->count()>1)
                    <div class="col-md-6 form-group">
                      @else
                       <div class="col-md-6 form-group" style="display:none">
                       @endif
                      <label>Şube</label>
                      <select name="sube_secim_randevu" id="sube_secim_randevu" class="select2 select2-lg">

                        @foreach($subeler as $key =>$sube)
                          @if($key==0)
                            <option value="{{$sube->id}}" selected>{{$sube->sube}}</option>
                          @else
                            <option value="{{$sube->id}}" >{{$sube->sube}}</option>
                          @endif
                        @endforeach


                      </select>
                 
                    </div>
                   
                    @endif
                    <div class="col-md-6">
                        <label>Randevu Tarihi</label>
                        <input type="date" id="randevutarihi_randevuliste" class="form-control" name="randevutarihi_randevuliste">

                    </div>
                  </div>


                </div>
               </div>

            </div>

          </div>
          <div class="row full-calendar">
            <div class="col-md-12">

              <div class="panel panel-default panel-fullcalendar">
                <div class="panel-body">
                  
                    <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                       
                        <th>Müşteri</th>
                        <th>Şube</th>
                        <th>Tarih & Saat</th>  
                        <th>Hizmetler</th>
                        <th>Durum</th>                      
                        <th>İşlemler</th>
                         
                        
                        
                      </tr>
                    </thead>
                    <tbody id="randevutablo">
                        @foreach($randevular as $randevu)

                        <tr>
                          <td>
                            <span style='display:none'>{{strtotime($randevu->tarih)}}</span>
                            {{$randevu->users->name}}
                            
                          </td>
                          <td>
                            {{$randevu->sube->sube}}
                          </td>
                          <td>
                            
                            {{date('d.m.Y',strtotime($randevu->tarih))}} {{date('H:i', strtotime($randevu->saat))}} 
                          </td>
                          <td>
                            
                            @foreach(\App\RandevuHizmetler::where('randevu_id',$randevu->id)->get() as $hizmet)
                            
                              {{$hizmet->hizmetler->hizmet_adi}} <br>
                            @endforeach
                          </td>
                          <td>
                            @if($randevu->durum == 1)
                             <button class="btn btn-success">Onaylı</button>
                            @elseif($randevu->durum == 2)
                             <button class="btn btn-danger">İptal</button>
                            @else
                             <button class="btn btn-warning">Bekliyor</button>
                            @endif


                          </td>
                          <td>

                            <button class="btn btn-primary randevudetayigetir" data-value="{{$randevu->id}}">    
                               <span class="mdi mdi-edit"></span> Düzenle 
                            </button>

                            @if($randevu->durum == 0)
                             <button class="btn btn-success randevuonayla" data-value="{{$randevu->id}}">    
                               <span class="mdi mdi-check-circle"></span> Onayla 
                            </button>
                            @endif
                            @if($randevu->durum != 2)
                             <button class="btn btn-danger randevuiptalet" data-value="{{$randevu->id}}">    
                               <span class="mdi mdi-minus-circle"></span> İptal Et 
                            </button>
                             @endif
                             <button class="btn btn-default randevusil" style="background-color: #0080FF;color:#fff" data-value="{{$randevu->id}}">
                               
                               <span class="mdi mdi-delete"></span> Sil
                             </button>
                              
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
      <div id="hata"></div>
       <div id="md-scale" class="modal-container modal-effect-1">
                    <div class="modal-content" style="overflow-y: auto;">
                      <div class="modal-header">
                        <span style="font-size:20px">Randevu Detayı</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                          <form id="randevudetaylari" method="GET">
                            <input type="hidden" name="randevuid">
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                  <label>Ad Soyad : </label><input name="randevualan" id="randevualan" type="text" disabled class="form-control"> 
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                      <label>E-posta : </label>
                                      <input name="eposta" id="eposta" type="text" class="form-control" disabled>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label>Telefon : </label>
                                    <input name="telefonev" id="telefonev" type="number" class="form-control" disabled>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label>GSM : </label>
                                    <input name="telefoncep" id="telefoncep" type="number" class="form-control" disabled>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label>Randevu Tarihi : </label>
                                    <input type="date" id="randevutarihi" class="form-control" name="randevutarihi">
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label>Randevu Saat Aralığı : </label>
                                    <div class="row">
                                      <div class="col-md-6">
                                        <div class="form-group">
                                        <label style="width: 100%;">Başlangıç Saati : </label>

                                        <input type="time" id="randevusaatibaslangic" class="form-control" name="randevusaatibaslangic"></div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <label style="width: 100%;" >Bitiş Saati : </label>
                                      
                                        <input type="time" id="randevusaatibitis" name="randevusaatibitis" class="form-control">
                                      </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                @if($subeler->count()>1)
                                 <div class="col-md-12">
                                  @else
                                  <div class="col-md-12" style="display:none">
                                    @endif
                                  <div class="form-group">
                                    <label>Şube</label>
                                    <select name="randevusube" id="randevusube_duzenle" class="form-control" >
                                      

                                    </select>
                                  </div>

                                </div>
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>Hizmetler (en az 1 hizmet seçiniz)</label>
                                    <select name="randevuhizmetler" id="randevuhizmetler_duzenle" class="tags input-xs" multiple>
                                      

                                    </select>
                                  </div>

                                </div>
                              </div>
                              
                              <div class="text-center" id="randevuislemleri">
                                <div class="xs-mt-50" style="margin-top: 0 !important;">
                                  <button type="button" class="btn btn-danger" id="randevuiptalet" style="float:left">İptal Et</button>
                                  <button type="button" class="btn btn-warning" id="randevubilgiguncelle" style="float:left">Randevu Güncelle</button>
                                    <button type="button" class="btn btn-success" id="randevuonayla" style="float:left">Onayla</button>
                                </div>
                              </div>
                          </form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
           <div id="md-scale2" class="modal-container modal-effect-1">
                    <div class="modal-content" style="overflow-y: auto">
                      <div class="modal-header">
                        <span style="font-size:20px">Yeni Randevu Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                           <form id="yenirandevuekleform" method="GET">
                            <div class="row">
                              <div class="col-md-4">
                                <div class=form-group>
                                     <input type="text" required id="adsoyad" name="adsoyad" placeholder="Ad Soyad..." class="form-control" list="adsoyad2">
                                      <datalist id="adsoyad2">
                                          @foreach($mevcutmusteriler as $mevcutmusteri)
                                        <option class="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->name}}</option>
                                         @endforeach
                                      </datalist>
                                     
                                  
                                   
                                </div>

                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <input type="email" name="eposta"  class="form-control" placeholder="E-posta">
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <input type="text" maxlength="10" minlength="10" name="ceptelefon" required class="form-control" placeholder="Cep Telefonu">
                                </div>
                              </div>
                             </div>
                             <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label>Randevu Tarihi</label>
                                  <div data-min-view="2" id="randevutarihidp"  data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                                                  <input name="tarih" required id="randevutarihiyeni" size="16" type="text" value="" class="form-control"><span class="input-group-addon"><i class="icon-th mdi mdi-calendar"></i></span>
                                                </div>
                                  <!--<input type="date" name="tarih" class="form-control">-->
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label>Randevu Saat Aralığı</label>
                                  <div class="row" id="saataraliklari">
                                  <div class="col-md-6" id="saatbaslangicbolumu">
                                    <div class="form-group">
                                      <label style="width: 100%; float:left">Başlangıç Saati : </label>
                                    <select style="float:left;width:100%;" name="saatbaslangic" id="saatbaslangic" class="adsoyadadmin" style="border-radius: 0" placeholder="Başlangıç Saati" data-enable-search="true">
                                      
                                      
                                    </select>
                                      </div>
                                    </div>
                                    <div class="col-md-6" id="saatbitisbolumu">
                                        <div class="form-group">
                                        <label style="width: 100%; float:left">Bitiş Saati : </label>
                                       <select style="float:left;width:100%;" name="saatbitis" id="saatbitis" class="adsoyadadmin" style="border-radius: 0" placeholder="Bitiş Saati" data-enable-search="true"></select>
                                       </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                             </div>
                              <div class="row">
                              @if($subeler->count()>1)
                              <div class="col-md-12">
                              @else
                              <div class="col-md-12" style="display:none">
                                @endif
                                 <label>Şube Seçiniz</label>
                                 <select name="suberandevu" required placeholder="Listeden Hizmet Seçin" id="searchable_select" class="adsoyadadmin form-control">
                                   
                                   @foreach($subeler as $sube)
                                    <option value="{{$sube->id}}">{{$sube->sube}}</option>
                                   @endforeach
                                 </select>

                              </div>

                             </div>
                             <div class="row">
                              <div class="col-md-12">
                                 <label>Hizmetler (en az bir hizmet seçiniz)</label>
                                 <select name="randevuhizmetleriyeni[]" required placeholder="Listeden Hizmet Seçin" multiple id="searchable_select" class="tags input-xs">
                                    
                                   @foreach($sunulanhizmetler as $hizmetliste)
                                    <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                   @endforeach
                                 </select>

                              </div>

                             </div>
                             <div class="row" style="margin-top: 10px;display: none;">
                              <div class="col-md-12" style="margin-top: 10px">
                                  
                                  <label>Personeller</label> &nbsp; <button type="button" id="hizmetpersonelgetir" class="btn btn-primary">Personel Seç</button>
                                  
                                </div>
                            </div>
                             <div class="row" id="hizmetpersonelbolumu"  style="margin-top: 10px">
                                  
                                
                              </div>
                              <div class="row">
                                   <div class="col-md-12" style="margin-top: 10px">
                                       <button type="submit" id="yenirandevugir" class="btn btn-success" style="width:100%">Randevuyu Onayla</button>
                                    </div>
                               </div>
                            </form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
           <div id="md-scale3" class="modal-container modal-effect-1">
                    <div class="modal-content" style="overflow-y: auto">
                      <div class="modal-header">
                        <span style="font-size:20px" id="randevuraporbaslik">Randevu İşlem Sonu Raporu Oluştur</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                           <form id="islemsonuraporu" method="GET">
                               <input type="hidden" name="rapor_randevuid" id="rapor_randevuid">
                              <div class="row">
                                <div class="col-md-12">
                                  
                                  <div class="form-group">
                                    <label>Müşteri randevuya geldi mi? : </label><br>
                                    <input required type="radio" name="randevuyageldi_gelmedi" value="1"> Geldi 
                                    <input type="radio" name="randevuyageldi_gelmedi" value="0"> Gelmedi
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>İşlemi yapan personel</label>
                                    <select required name="islemiyapanpersonel" class="tags input-xs">
                                      @foreach($personeller as $personel)
                                      <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>

                                      @endforeach
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                     <label>Alınan Ödeme (Küsuratları noktalı olarak giriniz. Ör: 14.50)</label>
                                     <input type="text" required  name="alinan_odeme" class="form-control">
                                  </div>
                                  
                                </div>

                                <div class="col-md-4">
                                  <div class="form-group">
                                     <label>Kalan Ödeme (Küsuratları noktalı olarak giriniz. Ör: 14.50)</label>
                                     <input type="number"  required name="kalan_odeme" class="form-control" value="0">
                                  </div>
                                  
                                </div>
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>Notlar</label>
                                    <textarea class="form-control" name="personel_notu"></textarea>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                   <div class="col-md-12" style="margin-top: 10px">
                                       <button type="submit" class="btn btn-primary" style="width:100%">Rapor Oluştur</button>
                                    </div>
                               </div>
                            </form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                  
                          <div class="modal-overlay"></div>
     
      </div>
 @endsection
 