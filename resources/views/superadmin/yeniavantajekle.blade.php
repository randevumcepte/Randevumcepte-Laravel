@extends('layout.layout_sistemadmin')
@section('content')
     <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Yeni Avantaj Ekle
                   <!-- <br /> <span style="font-size:12px;color:#FF4E00">Avantajbu'dan gelen :  {{\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->where('tur',1)->count()}}</span>
                    <br />
                    <span style="font-size:12px;color:#1266f1">Eklediklerim & kendi müşterilerim :{{\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->where('tur',0)->count()}} </span> -->

                  
                  </div>
                <div class="panel-body" >
                  <form id="yeniavantajyayinla" method="post">
                     {!!csrf_field()!!}
                  <div class="user-display">
                    <div class="user-display-bg">
                      <img id="profilkapak" src="{{secure_asset('public/isletmeyonetim_assets/img/user-profile-display.png')}}" alt="Profile Background">
                    
                    </div>
                    <div class="single-file-input2">
                        <input type="file" id="isletmekapakfoto" name="isletmekapakfoto">
                        <div class="btn btn-primary">Avantaj kapak fotoğrafını düzenle</span></div>
                     </div>
                   </div>
                  <div class="col-md-12">
                   
                    <div class="row">
                        <div class="col-md-12">
                          <div class="from-group">
                                <label style="font-size: 16px">İşletme</label>
                               <select class="tags input-xs" name="isletme" id="isletme">
                                   @foreach($isletmeler as $isletme)
                                     <option value="{{$isletme->id}}">{{$isletme->salon_adi}}</option>
                                   @endforeach
                               </select>
                          </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-12">
                       
                           <div class="from-group">
                             
                              <label style="font-size: 16px">Avantaj başlığı</label>
                              <input type="text" required class="form-control" placeholder="Başlık..." name="kampanya_baslik">
                           </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <label style="font-size: 16px">Avantaj açıklaması</label>
                                <textarea required class="form-control" placeholder="Açıklama..." name="kampanya_aciklama"></textarea>
                             </div>
                        </div>
                      </div>
                      <div class="row">
                          <div class="col-xs-4 col-sm-4 col-md-4">
                             <div class="form-group">
                              <label style="font-size: 16px">Hizmet Normal Fiyatı</label>
                                <input class="form-control" type="text" name="hizmet_normal_fiyat" placeholder="Hizmet normal fiyatı..." required>
                             </div>
                          </div>
                          <div class="col-xs-4 col-sm-4 col-md-4">
                             <div class="form-group">
                              <label style="font-size: 16px">Avantajlı Fiyatı</label>
                                <input class="form-control" type="text" name="kampanya_fiyat" placeholder="Kampanya fiyatı..." required>
                             </div>
                          </div>
                           <div class="col-xs-4 col-sm-4 col-md-4">
                             <div class="form-group">
                              <label style="font-size: 16px">Avantaj Bitiş Tarihi</label>
                                <div data-min-view="2" data-id="avantajbitistarih"  data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                          <input name="avantajbitistarih" id="avantajbitistarih" size="16" type="text" value="{{date('Y-m-d')}}" class="form-control"><span class="input-group-addon"><i class="icon-th mdi mdi-calendar"></i></span>
                        </div>
                             </div>
                          </div>
                      </div>
                      <div class="row">
                         <div class="col-md-12">
                            <div class="form-group">
                               <label style="font-size: 20px;font-weight: bold;">Avantaj Detayları</label>
                                  <div class="email editor">
                                     <div id="email-editor"></div>
                                     
                                  </div>
                            </div>
                          </div>
                      </div>
                      @if(Auth::user()->admin==1)
                      <div class="row">
                          <div class="col-md-12">
                              <div class="from-group">
                                 <label style="font-size:16px"><strong>Arama Terimleri</strong></label>
                               </div>
                               <div class="form-group">
                                 <input class="form-control" type="text" placeholder="Etiket 1" name="etiket1">
                               </div>
                               <div class="form-group">
                                 <input class="form-control" type="text" placeholder="Etiket 2" name="etiket2">
                              </div>
                              <div class="form-group">
                                 <input class="form-control" type="text" placeholder="Etiket 3" name="etiket3">
                              </div>
                              <div class="form-group">
                                 <input class="form-control" type="text" placeholder="Etiket 4" name="etiket4">
                              </div>
                              <div class="form-group">
                                 <input class="form-control" type="text" placeholder="Etiket 5" name="etiket5">
                              </div>
                              <div class="form-group">
                                 <input class="form-control" type="text" placeholder="Etiket 6" name="etiket6">
                              </div>
                          </div>
                      </div>
                      @endif
                      <div class="row" id="isletmegorselbolumu">
              <div class="col-md-12">
              
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                   <strong>Avantaj Görselleri</strong>
                    <div class="single-file-input2">
                            <input type="file" id="isletmegorselleri" name="isletmegorselleri" multiple>
                             <div class="btn btn-primary">Görsel Ekle</span></div>
                      </div>
                  </div>
                  <div class="panel-body">
                     <div class="gallery-container">

                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel1" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink1" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel2" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink2" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel3" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink3" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel4" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink4" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel5" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink5" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel6" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink6" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel7" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink7" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel8" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink8" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel9" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink9" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel10" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink10" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel11" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink11" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                      <div class="item">
                          <div class="photo">
                            <div class="img"><img id="gorsel12" src="{{secure_asset('public/img/image-01.jpg')}}" alt="Salon Görseli">
                                <div class="over">
                                  <div class="info-wrapper">
                                      <div class="info">
                         
                                        <div class="func"><a href="#"><i class="icon mdi mdi-link"></i></a><a id="gorsellink12" href="{{secure_asset('public/img/image-01.jpg')}}" class="image-zoom"><i class="icon mdi mdi-search"></i></a>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                          </div>
                      </div>
                       
                       
             
                    </div>
                  </div>
                </div>
              </div>
             </div>
                     </div>
                     <div class="form-group">

                                         <button type="submit" class="btn btn-primary" style="width: 100%"><i class="icon s7-mail"></i> Avantaj Ekle</button>
                    </div>
                   </form>
                 </div>
                </div>
              </div>
            </div>
          </div>
@endsection