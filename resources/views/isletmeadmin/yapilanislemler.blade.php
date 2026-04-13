 


 @if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
 @section('content')
        <div class="page-head" style="position: relative; width: 100%;">
          <h2 class="page-head-title" style="float: left;">Genel Rapor</h2>
          <span style="float: left;"><button id="yenirandevuekle" type="button" data-modal="md-scale3" class="btn btn-space btn-success md-trigger" style="margin-top: 10px;margin-left: 20px" >Yeni Rapor Oluştur</button></span>
          <button type="button" id="rapordetayigetir" style="display: none" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Modal</button>
           
        </div>
        <div class="main-content container-fluid" style="float:left; position:relative;width:100%;">
          <div class="row full-calendar">
            <div class="col-md-12">
               <div class="panel panel-default panel-fullcalendar">
                <div class="panel-heading">Filtrele</div>
                <div class="panel-body">
                  <div class="row">
                    @if(Auth::guard('isletmeyonetim')->user()->is_admin)
                    <div class="col-md-6 form-group">
                      <label>Şube</label>
                      <select name="sube_secim_rapor" id="sube_secim_rapor" class="select2 select2-lg">
                        <option value="0" selected>Tüm Şubeler</option>
                        @foreach($subeler as $key =>$sube)

                          @if($key==0)
                            <option value="{{$sube->id}}" >{{$sube->sube}}</option>
                          @else
                            <option value="{{$sube->id}}" >{{$sube->sube}}</option>
                          @endif
                        @endforeach


                      </select>
                 
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label>Rapor Tarihi</label>
                        <input type="date" id="raportarihi" class="form-control" name="raportarihi" value="{{$tarih}}">

                    </div>
                  </div>


                </div>
               </div>

            </div>

          </div>
          <div class="row">
            <div class="col-xs-12 col-md-4 col-lg-4" >
                        <div class="widget widget-tile" style="background-color: #2a75f3; color:white">
                          <div id="spark4" class="chart sparkline">
                             <img src="{{secure_asset('public/img/customers.png')}}" width="76" height="50" alt="Gelen Müşteri">
                          </div>
                          <div class="data-info">
                            <div class="desc" style="font-size: 19px; font-weight: bold;">Gelen Müşteri</div>
                            <div class="value">

                              <span style="color: white" class="indicator indicator-positive mdi mdi-chevron-up"></span><span data-toggle="counter" id="gelenmusteri"  data-end="0" class="number">{{$gelen_musteri}}</span>
                            </div>
                          </div>
                        </div>
                </div>
                <div class="col-xs-12 col-md-4 col-lg-4">
                        <div class="widget widget-tile" style="background-color: #2e9549; color: white">
                          <div id="spark1" class="chart sparkline">
                             <img src="{{secure_asset('public/img/tl.png')}}" width="50" height="50" alt="Alınan Ödeme">
                          </div>
                          <div class="data-info">
                            <div class="desc" style="font-size: 19px; font-weight: bold;">Alınan Ödeme</div>
                            <div class="value"><span style="color: white" class="indicator indicator-positive mdi mdi-chevron-up"></span><span data-toggle="counter" id="alinanodeme"  data-end="0" class="number">{{$alinan_odeme}}</span>
                            </div>
                          </div>
                        </div>
                </div>
                <div class="col-xs-12 col-md-4 col-lg-4">
                        <div class="widget widget-tile" style="background-color: #e3aa04; color:white">
                          <div id="spark1" class="chart sparkline">
                             <img src="{{secure_asset('public/img/tl.png')}}" width="50" height="50" alt="Kalan Ödeme">
                          </div>
                          <div class="data-info">
                            <div class="desc" style="font-size: 19px; font-weight: bold;">Kalan Ödeme</div>
                            <div class="value"><span class="indicator indicator-positive mdi mdi-chevron-down" style="color: white"></span><span data-toggle="counter" id="kalanodeme"  data-end="0" class="number">{{$kalan_odeme}}</span>
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
                        <th>Tarih & Saat</th>
                        <th>Müşteri</th>
                        <th>İşlem Yapan Şube & Personel</th>
                        <th>Yapılan İşlem(-ler)</th>
                        <th>Alınan Ödeme ₺</th>
                           <th>Kalan Ödeme ₺</th>        
                        <th>İşlemler</th>
                         
                        
                        
                      </tr>
                    </thead>
                    <tbody id="islemtablo">
                      @foreach($islemler as $islem)
                      <tr>
                          <td>
                            
                            {{date('d.m.y H:i', strtotime($islem->tarih))}}
                          </td>
                          <td>
                            
                            {{$islem->user->name}}
                          </td>
                          <td>
                            {{$islem->personeller->sube->sube}} şubesi<br>
                            {{$islem->personeller->personel_adi}}

                          </td>
                          <td>{{$islem->yapilan_islemler}}</td>
                          <td>{{$islem->alinan_odeme}}</td>
                          <td>{{$islem->kalan_odeme}}</td>
                          <td>
                            <button class="btn btn-primary islemdetayigetir" data-value="{{$islem->id}}">    
                               <span class="mdi mdi-edit"></span> Düzenle 
                            </button>
                            @if($islem->kalan_odeme>0)
                            <button class="btn btn-success kalanodemealindi" data-value="{{$islem->id}}">    
                               <span class="mdi mdi-money-box"></span> Kalan Ödeme Alındı 
                            </button>
                            @endif



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
                    <div class="modal-content" style="overflow-y: auto">
                      <div class="modal-header">
                        <span style="font-size:20px" id="raporbaslik">İşlem Raporu Düzenle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body"> 
                           <form class="islemsonuraporu" data-value="update" method="GET">
                               <input type="hidden" name="islem_id" id="islem_id">
                              <div class="row">
                                <div class="col-md-8">
                                  <div class="form-group">
                                    <label>Tarih</label>
                                    <input type="date" name="tarih" id="islem_tarihi" class="form-control">
                                  </div>

                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Saat</label>
                                    <input type="time" name="saat" id="islem_saati" class="form-control">
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class=form-group>
                                    <label>Müşteri</label>
                                     <select required name="musteri" id="islem_musteri" class="tags input-xs">
                                     @foreach($mevcutmusteriler as $mevcutmusteri)
                                        <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->name}}</option>
                                         @endforeach
                                    </select>
                                     
                                     
                                  
                                   
                                  </div>
                                   
                                </div>
                                 <div class="col-md-6">
                                  <div class=form-group>
                                    <label>Müşteri Telefon</label>
                                     <input type="number" disabled name="musteritelefon" id="islem_musteri_telefon" class="form-control">
                                     
                                     
                                  
                                   
                                  </div>
                                   
                                </div>

                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>İşlemi yapan personel</label>
                                    <select required name="islemiyapanpersonel" id="islem_personel" class="tags input-xs">
                                      @foreach($personeller as $personel)
                                      <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>

                                      @endforeach
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>Yapılan işlem(-ler)</label>
                                    <select multiple required name="yapilan_islemler[]" id="islem_yapilan" class="tags input-xs">
                                      @foreach($sunulanhizmetler as $hizmetliste)
                                        <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                       @endforeach
                                     </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                     <label>Alınan Ödeme (Küsuratları noktalı olarak giriniz. Ör: 14.50)</label>
                                     <input type="number" required  name="alinan_odeme" id="islem_alinan_odeme" class="form-control">
                                  </div>
                                  
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                     <label>Kalan Ödeme (Küsuratları noktalı olarak giriniz. Ör: 14.50)</label>
                                     <input type="number"  required name="kalan_odeme" id="islem_kalan_odeme" class="form-control" value="0">
                                  </div>
                                  
                                </div>
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>Notlar</label>
                                    <textarea class="form-control" name="personel_notu" id="islem_personel_notu"></textarea>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                   <div class="col-md-12" style="margin-top: 10px">
                                       <button type="submit" class="btn btn-primary" style="width:100%">Raporu Güncelle</button>
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
                        <span style="font-size:20px" id="raporbaslik">Yeni İşlem Sonu Raporu Oluştur</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body"> 
                           <form class="islemsonuraporu" data-value="insert" method="GET">
                               
                              <div class="row">
                                <div class="col-md-8">
                                  <div class="form-group">
                                    <label>Tarih</label>
                                    <input type="date" required name="tarih" value="{{date('Y-m-d')}}" class="form-control">
                                  </div>

                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Saat</label>
                                    <input type="time" required name="saat" value="{{date('H:i')}}" class="form-control">
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class=form-group>
                                    <label>Müşteri</label>
                                     <select required name="musteri" id="rapor_musteri" class="tags input-xs">
                                     @foreach($mevcutmusteriler as $mevcutmusteri)
                                        <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->name}}</option>
                                         @endforeach
                                    </select>
                                     
                                     
                                  
                                   
                                  </div>
                                   
                                </div>
                                 <div class="col-md-6">
                                  <div class=form-group>
                                    <label>Müşteri Telefon</label>
                                     <input type="number" name="musteritelefon" id="musteritelefon" class="form-control">
                                     
                                     
                                  
                                   
                                  </div>
                                   
                                </div>

                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>İşlemi yapan personel</label>
                                    <select required name="islemiyapanpersonel" class="tags input-xs">
                                      @foreach($personeller as $personel)
                                      <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>

                                      @endforeach
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label>Yapılan işlem(-ler)</label>
                                    <select multiple required name="yapilan_islemler[]" class="tags input-xs">
                                      @foreach($sunulanhizmetler as $hizmetliste)
                                        <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                       @endforeach
                                     </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                     <label>Alınan Ödeme (Küsuratları noktalı olarak giriniz. Ör: 14.50)</label>
                                     <input type="number" required step="."  name="alinan_odeme" class="form-control">
                                  </div>
                                  
                                </div>

                                <div class="col-md-6">
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
 