@extends('layout.layout_sistemadmin')
@section('content')
 <div class="main-content container-fluid">
 	<form id="yeniisletmeekleme" method="post" enctype="multipart/form-data">
    {!!csrf_field()!!}
     <div class="user-profile">
     
		<div class="row">
			<div class="col-md-12">
               <div class="user-display">
                  <div class="user-display-bg">
                  	<img id="profilkapak" src="{{secure_asset('public/isletmeyonetim_assets/img/user-profile-display.png')}}" alt="Profile Background">
                  	
                  </div>
                  <div class="single-file-input2">
                            <input type="file" id="isletmekapakfoto" name="isletmekapakfoto">
                             <div class="btn btn-primary">İşletme kapak fotoğrafını düzenle</span></div>
                     	</div>
                  <div class="user-display-bottom row" style="margin-top: 30px">
                    <div class="col-xs-2 col-sm-2 col-md-2">
                    <div class="user-display-avatar">
                    	<img id="profillogo" src="{{secure_asset('public/isletmeyonetim_assets/img/avatar-150.png')}}" alt="Avatar">
                    	 <div class="single-file-input" style="left:0">
                            <input type="file" id="isletmelogo" name="isletmelogo">
                             <div class="btn btn-primary">İşletme logosu seç</div>
                     	</div>

                    </div>
                  </div>
                  <div class="col-xs-10 col-sm-10 col-md-10" style="margin-top: 10px">
                    
                    <div class="user-display-info">
                      <div class="name">
                          <div class="col-md-12">
                      		  <div class="form-group">
                          
                      			<input type="text" name="isletmeadi" placeholder="İşletme adı..." class="form-control">
                          </div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
                            
                            <input type="text" name="adres" placeholder="Adres..." class="form-control">
                            </div>
                          </div>
                          <div class="col-md-6">
                          <div class="form-group">
                            
                              <select id="illistesi" name="il" class="tags input-xs">
                                  <option value="0">İl seçiniz...</option>
                                  @foreach(\App\Iller::all() as $iller)
                                       <option value="{{$iller->id}}">{{$iller->il_adi}}</option>
                                  @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-md-6">
                              <div class="form-group">
                              <select id="ilcelistesi" name="ilce" class="tags input-xs">
                                  <option value="0">İlçe seçiniz...</option>
                                 
                              </select>
                            </div>
                      		</div>
                          <div class="col-md-12">
                             <div class="form-group">
                               <select id="uyelikturu" name="uyelikturu" class="tags input-xs">
                                  <option value="0">Üyelik Türü & Paket Seçiniz...</option>
                                  <option value="1">Avantajlı Randevu Paketi</option>
                                  <option value="2">Avantajlı Kampanya Paketi</option>
                                  <option value="3">Full Avantaj Paketi</option>
                               </select>
                             </div>
                          </div>
                          
                       </div>
                       
                    </div>
                    <div class="row user-display-details">
                      <div class="col-md-6">
                       <div class="panel-heading">
                           İşletme Yetkilileri
                          <div class="tools">
                               <button type="button" data-modal="md-scale2" class="btn btn-space btn-primary md-trigger">Yeni Yetkili Ekle</button>
                          </div>
                       </div>
                       <div class="panel-body">
                          <div class="form-group">
                       <select id="isletmeyetkililiste" name="isletmeyetkilileri" class="tags input-xs">
                      	   
                      	  {!!$salonyetkilileri!!}
                           
                        </select>
                           
                      </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel-heading">
                          İşletme Türü
                          <div class="tools">
                            <button type="button" data-modal="md-scale3" class="btn btn-space btn-primary md-trigger">Yeni İşletme Türü Ekle</button>
                          </div>
                        </div>
                        <div class="panel-body">
                            <select id="isletmeturulistesi" name="isletmeturu" class="tags input-xs">
                                {!!$isletmeturulistesi!!}
                            </select>
                        </div>
                    </div>
                    </div></div>
                  </div>
                </div>
                
              </div>
            </div>
           
            <div class="row" id="sunulanhizmetlerbaybayanbolumu">
              <div class="col-md-6">
                <div class="panel panel-default">
                      <div class="panel-heading panel-heading-divider">
                        Sunulan Hizmetler (Bayan)
                        <div class="tools">
                            <button type="button" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Yeni Hizmet Ekle</button>

                        </div>

                    </div>
                   
                   <div class="panel-body">
                      
                         
                       <div class="form-group">
                        <label>İşletmenin sunduğu hizmetleri seçiniz...</label>
                        <select multiple="" id="hizmetlerlistesi_bayan" name="hizmetler_bayan[]" class="tags input-xs">
                          
                           {!!$hizmetlistesi!!}
                           
                        </select>
                        <button class="btn btn-primary" id="fiyatlistesineeklebayan">Fiyat Listesine Ekle</button>
                     </div>
                    </div>
                    <div class="panel-heading panel-heading-divider">
                      Fiyat Listesi
                    </div>
                    <div class="panel-body">
                       <table class="table table-striped table-borderless">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Hizmet</th>
                        <th>Başlangıç Fiyat</th>
                     
                        <th>Son Fiyat</th>
                         
                      </tr>
                    </thead>
                    <tbody class="no-border-x" id="hizmetfiyatlaribayan">
                       
                      
                    </tbody>
                  </table>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="panel panel-default">
                      <div class="panel-heading panel-heading-divider">
                        Sunulan Hizmetler (Bay)
                         <div class="tools">
                            <button type="button" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Yeni Hizmet Ekle</button>
                        </div>
                    </div>
                   
                   <div class="panel-body">
                        
                        <div class="form-group">
                        <label>İşletmenin sunduğu hizmetleri seçiniz...</label>
                 
                        <select multiple="" id="hizmetlerlistesi_bay" name="hizmetler_bay[]" class="tags input-xs">
                           {!!$hizmetlistesi!!}
                           
                        </select>
                        <button class="btn btn-primary" id="fiyatlistesineeklebay">Fiyat Listesine Ekle</button>
                      </div>
                     
                    </div>
                     <div class="panel-heading panel-heading-divider">
                       Fiyat Listesi
                    </div>
                    <div class="panel-body">
                       <table class="table table-striped table-borderless">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Hizmet</th> 
                        <th>Başlangıç Fiyat</th>
                        <th>Son Fiyat</th>
                         
                      </tr>
                    </thead>
                    <tbody class="no-border-x" id="hizmetfiyatlaribay">

                       
                      
                    </tbody>
                  </table>
                    </div>
                </div>
              </div>
              
            </div>
            <div class="row">
            	<div class="col-md-12">

            		<div class="panel panel-default">
                  		<div class="panel-heading panel-heading-divider">
                  			Açıklama & Hakkında

                 		</div>
                 	 
                  		<div class="panel-body">
                  	 		<textarea name="aciklama" placeholder="Açıklama & hakkımızda yazısı ekle..." class="form-control"></textarea>
            		  		</div>
            		</div>
            	</div>
            	
           	 </div>
           	 <div class="row">
           	 	<div class="col-md-6" id="isletmecalismasaatleribolumu">
            		<div class="panel panel-default">
                  		<div class="panel-heading panel-heading-divider">
                  			Çalışma Saatleri

                 		</div>
                 	 
                  		<div class="panel-body">
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
            		</div>
            	</div>
            	<div class="col-md-6" id="isletmepersonelbolumu">
            		<div class="panel panel-default">
                  		<div class="panel-heading panel-heading-divider">
                  			Personeller
                        <div class="tools">
                              <button type="button" data-modal="md-scale4" class="btn btn-space btn-primary md-trigger">Yeni Personel Ekle</button>
                            </div>
                 		</div>
                 	 
                  		<div class="panel-body">
                  			 <select multiple="" id="personelliste" name="personeller[]" class="tags input-xs" style="height: 300px">
                      	  		 
                      	  		{!!$personelliste!!}
                           
                        	</select>
                  	 		 
            		  	 </div>
            		</div>
            	</div>

           	 </div>
           	 <div class="row">
               @if(Auth::user()->admin==1)
           	 	<div class="col-md-6" id="isletmearamaterimibolumu">            	
            		<div class="panel panel-default">
            			<div class="panel-heading panel-heading-divider">
            				Etiketler & Arama Terimleri
            			</div>
            			<div class="panel-body">
                    <div class="form-group">
                    <input type="text" class="form-control" name="etiket1" placeholder="Etiket 1">
                  </div>
                    <div class="form-group">
                    <input type="text" class="form-control" name="etiket2" placeholder="Etiket 2">
                  </div>
                    <div class="form-group">
                    <input type="text" class="form-control" name="etiket3" placeholder="Etiket 3">
                  </div>
                    <div class="form-group">
                    <input type="text" class="form-control" name="etiket4" placeholder="Etiket 4">
                  </div>
                    <div class="form-group">
                    <input type="text" class="form-control" name="etiket5" placeholder="Etiket 5">
                  </div>
                 <div class="form-group">
                    <input type="text" class="form-control" name="etiket6" placeholder="Etiket 6">
                  </div>
                   
            				 
            			</div>
            		</div>
            	</div>
              <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Google Maps Kaydı
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                       <textarea style="height: 150px" class="form-control" name="googlemapskaydi" placeholder="Maps embed kodunun src kısmını giriniz. Ör. https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3130.01752138898!2d26.76607081482223!3d38.325425079662956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14bb9361ed210cf1%3A0x511804e1bd79a3c2!2sCadde+Kuaf%C3%B6r!5e0!3m2!1str!2str!4v1539167247992"></textarea>
                    </div>
                  </div>
                </div>
              </div>
               <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Facebook Sayfa Adresi
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                       <input type="text" class="form-control" name="facebookadres" placeholder="İşletmenin facebook adresini giriniz...">
                    </div>
                  </div>
                </div>
              </div>
            	 <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Instagram Ayarları
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                       <input type="text" class="form-control" name="instagramaccesstoken" placeholder="Instagram access token">
                    </div>
                  </div>
                </div>
              </div>
              @else
              <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Google Maps Kaydı
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                       <textarea style="height: 150px" class="form-control" name="googlemapskaydi" placeholder="Maps embed kodunun src kısmını giriniz. Ör. https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3130.01752138898!2d26.76607081482223!3d38.325425079662956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14bb9361ed210cf1%3A0x511804e1bd79a3c2!2sCadde+Kuaf%C3%B6r!5e0!3m2!1str!2str!4v1539167247992"></textarea>
                    </div>
                  </div>
                </div>
              </div>
               <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Facebook Sayfa Adresi
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                       <input type="text" class="form-control" name="facebookadres" placeholder="İşletmenin facebook adresini giriniz...">
                    </div>
                  </div>
                </div>
              </div>
               <div class="col-md-6">
                <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Instagram Ayarları
                  </div>
                  <div class="panel-body">
                    <div class="form-group">
                       <input type="text" class="form-control" name="instagramaccesstoken" placeholder="Instagram access token">
                    </div>
                  </div>
                </div>
              </div>
              @endif
           	 </div>
           	 <div class="row" id="isletmegorselbolumu">
           	 	<div class="col-md-12">
            	
            		<div class="panel panel-default">
            			<div class="panel-heading panel-heading-divider">
            				Görseller : Görsellerin işletme sayfasında görünmesi için en az 5 resim yüklemelisiniz
            				<div class="single-file-input2">
                            <input type="file" id="isletmegorselleri" name="isletmegorselleri" multiple>
                             <div class="btn btn-primary">İşletme Görsellerini Ekleyin (Max:12 adet en az 5 adet)</span></div>
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
           	 <div class="row">
           	 	 <div class="col-md-12">
           	 	 <button type="submit" class="btn btn-primary btn-big" style="width: 100%">İşletmeyi Ekle</button>
           	 	</div>

           	 </div>
            
         </div>
       
        </form>
     </div>
       <div id="md-scale" class="modal-container modal-effect-1">
                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px"> Sisteme Yeni Hizmet Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                      
                          <div class="form-group">

                              <label>Hizmet Adı</label>
                              <input id="hizmetadi_yeni" class="form-control">
                             
                          </div>
                          <div class="form-group">
                             <label>Hizmet Kategorisi</label>
                               <select  id="hizmetkateogirisi_yeni" class="tags input-xs">
                                  <option value="0">Hizmet kategorisi seçin yada yeni bir kategori girin...</option>
                                    @foreach(\App\Hizmet_Kategorisi::all() as $hizmetkategorisi)
                                      <option value="{{$hizmetkategorisi->id}}">{{$hizmetkategorisi->hizmet_kategorisi_adi}}</option>
                                    @endforeach
                           
                                 </select>
                          </div>
                            <div class="text-center">
                          <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="button" id="yenihizmetgir" class="btn btn-primary">Ekle</button>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
          <div id="md-scale2" class="modal-container modal-effect-1">
                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px">Yeni İşletme Yetkilisi Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                      
                          <div class="form-group">

                              
                              <input id="yetkiliadi_yeni" required placeholder="Yetkili adı..." class="form-control">
                             
                          </div>
                          <div class="form-group">
                              <input type="email" required id="yetkili_eposta_yeni" placeholder="Yetkili e-posta & kullanıcı adı" class="form-control">
                          </div>
                           <div class="form-group">
                              <input type="number" required id="yetkili_cep_telefon_yeni" placeholder="Yetkili cep telefonu" class="form-control">
                          </div>
                          <div class="form-group">
                              <div data-min-view="2" data-id="dogum_tarihi"  data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                              <input name="dogum_tarihi" id="dogum_tarihi" size="16" type="text" value="{{date('Y-m-d')}}" class="form-control"><span class="input-group-addon"><i class="icon-th mdi mdi-calendar"></i></span>
                          </div>
                          <div class="form-group">
                              <input type="password" required id="yetkili_sifre_yeni" placeholder="Yetkili şifre..." class="form-control">
                          </div>
                            <div class="form-group">
                              <input type="password" required id="yetkili_sifre_tekrar_yeni" placeholder="Yetkili şifre tekrar..." class="form-control">
                          </div>
                            <div class="text-center">
                          <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="button" id="yeniisletmeyetikilisigir" class="btn btn-primary">Ekle</button>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                 <div id="md-scale3" class="modal-container modal-effect-1">
                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px">Yeni İşletme Türü Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                      
                          <div class="form-group">

                              
                              <input id="isletmeturuadi_yeni" required placeholder="İşletme türü..." class="form-control">
                             
                          </div>
                          
                            <div class="text-center">
                          <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="button" id="yeniisletmeturugir" class="btn btn-primary">Ekle</button>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
 <div id="md-scale4" class="modal-container modal-effect-1">
                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px">Yeni Personel Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                         <form id="yenipersonelgirisi" method="GET">
                          <div class="form-group">
       
                              <label>Personel Adı</label>
                              <input id="personeladi_yeni" name="personeladi_yeni" required placeholder="Personel adı..." class="form-control">
                            </div>
                            <div class="form-group">
                              <label>Unvan</label>
                              <input id="personelunvan_yeni" name="personelunvan_yeni" placeholder="Personel Unvanı (stilist vb)" class="form-control">
                            </div>
                            <div class="form-group">
                              <label>Cinsiyet</label>
                              <select name="personelcinsiyet_yeni" id="personelcinsiyet_yeni" class="form-control">
                                <option value="0">Bayan</option>
                                <option value="1">Bay</option>
                              </select>
                            </div>
                            <div class="form-group">
                              <label>Sunulan Hizmetler (Bayan)</label>
                              <select id="personelsunulanhizmetlerbayan_yeni" multiple name="personelsunulanhizmetlerbayan_yeni[]" class="tags input-xs">
                                
                                 {!!$hizmetlistesi!!}
                              </select>
                            </div>
                            <div class="form-group">
                                <label>Sunulan Hizmetler (Bay)</label>
                              <select id="personelsunulanhizmetlerbay_yeni" multiple name="personelsunulanhizmetlerbay_yeni[]" class="tags input-xs">
                                
                                 {!!$hizmetlistesi!!}
                              </select>
                             
                          </div>
                         
                            <div class="text-center">
                          <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="button" id="yenipersonelgir" class="btn btn-primary">Ekle</button>
                          </div>
                        </div></form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                  <div class="modal-overlay"></div>
                  <div id="hata"></div>
 
@endsection