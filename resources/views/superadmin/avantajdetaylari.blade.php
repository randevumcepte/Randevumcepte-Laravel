@extends('layout.layout_sistemadmin')
@section('content')
	<input type="hidden" id="avantajdetayi" value="{{$avantaj->kampanya_detay}}">
     <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">{{$avantaj->kampanya_aciklama}}
                   <!-- <br /> <span style="font-size:12px;color:#FF4E00">Avantajbu'dan gelen :  {{\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->where('tur',1)->count()}}</span>
                    <br />
                    <span style="font-size:12px;color:#1266f1">Eklediklerim & kendi müşterilerim :{{\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->where('tur',0)->count()}} </span> -->

                  
                  </div>
                <div class="panel-body" >
                  <form id="mevcutavantajduzenleme" method="post" enctype="multipart/form-data">
                     {!!csrf_field()!!}
                     <input type="hidden" name="isletmeid" value="{{$avantaj->salon_id}}">
                     <input type="hidden" name="avantajid" value="{{$avantaj->id}}">
                  <div class="user-display">
                    <div class="user-display-bg">
                      @if(\App\SalonGorselleri::where('salon_id',$avantaj->salon_id)->where('kampanya_gorsel_kapak',1)->value('salon_gorseli')!= '' && \App\SalonGorselleri::where('salon_id',$avantaj->salon_id)->where('kampanya_gorsel_kapak',1)->value('salon_gorseli')!= null)
                         	<img id="profilkapak" src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$avantaj->salon_id)->where('kampanya_gorsel_kapak',1)->value('salon_gorseli'))}}" alt="Profile Background">
                      @else
                      		 	<img id="profilkapak" src="{{secure_asset('public/isletmeyonetim_assets/img/user-profile-display.png')}}" alt="Profile Background">
                      @endif
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

                          	  <label style="float: left; font-size: 16px">İşletme : {{\App\Salonlar::where('id',$avantaj->salon_id)->value('salon_adi')}}</label>
                                
                          </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-12">
                       
                           <div class="from-group">
                             
                              <label style="font-size: 16px">Avantaj başlığı</label>
                              <input type="text" required class="form-control" placeholder="Başlık..." name="kampanya_baslik" value="{{$avantaj->kampanya_baslik}}">
                           </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <label style="font-size: 16px">Avantaj açıklaması</label>
                                <textarea required class="form-control" placeholder="Açıklama..." name="kampanya_aciklama">{{$avantaj->kampanya_aciklama}}</textarea>
                             </div>
                        </div>
                      </div>
                      <div class="row">
                          <div class="col-xs-4 col-sm-4 col-md-4">
                             <div class="form-group">
                              <label style="font-size: 16px">Hizmet Normal Fiyatı</label>
                                <input class="form-control" type="text" name="hizmet_normal_fiyat" placeholder="Hizmet normal fiyatı..." value="{{$avantaj->hizmet_normal_fiyat}}" required>
                             </div>
                          </div>
                          <div class="col-xs-4 col-sm-4 col-md-4">
                             <div class="form-group">
                              <label style="font-size: 16px">Avantajlı Fiyatı</label>
                                <input class="form-control" type="text" name="kampanya_fiyat" placeholder="Kampanya fiyatı..." value="{{$avantaj->kampanya_fiyat}}" required>
                             </div>
                          </div>
                           <div class="col-xs-4 col-sm-4 col-md-4">
                             <div class="form-group">
                              <label style="font-size: 16px">Avantaj Bitiş Tarihi</label>
                                <div data-min-view="2" data-id="avantajbitistarih"  data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                          <input name="avantajbitistarih" id="avantajbitistarih" size="16" type="text" value="{{date('Y-m-d',strtotime($avantaj->kampanya_bitis_tarihi))}}" class="form-control"><span class="input-group-addon"><i class="icon-th mdi mdi-calendar"></i></span>
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
                      <script type="text/javascript">
                      	$(window).on('load',function(){
                      		 App.mailCompose();
                      		 
                      		document.getElementById('detayicerikhtml').innerHTML = $('#avantajdetayi').val();
                      	 });
                      </script>
                      @if(Auth::user()->admin==1)
                      <div class="row">
                          <div class="col-md-12">
                                <div class="from-group">
                                 <label style="font-size:16px"><strong>Arama Terimleri</strong></label>
                               </div>
                          	  @foreach($etiketler as $key=>$value)

			                    <div class="form-group">
			                      <input type="hidden" name="mevcutaramaterimiid[]" value="{{$value->id}}">
			                      <input type="text" class="form-control" name="etiket{{$key+1}}" value="{{$value->arama_terimi}}" placeholder="Etiket {{$key+1}}">
			                  </div>
                     
			                  @endforeach
			                  @if($etiketler->count()<=6)
			                     @for($i=$etiketler->count();$i<6;$i++)
			                      <div class="form-group">
			                        <input type="text" class="form-control" name="etiket{{$i+1}}" placeholder="Etiket {{$i+1}}">
			                      </div>
			                     @endfor
			                   @endif
                             
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
                             <div class="btn btn-primary">Avantaj Görsellerini Ekleyin (Max:{{12-$salongorselleri->count()}} adet)</span></div>
                      </div>
                  </div>
                  <div class="panel-body">
                     <div class="gallery-container" id="gorselbolumu">
                       
                       
                       {!!$gorseller_html!!}
                       
             
                    </div>
                  </div>
                </div>
              </div>
             </div>
                     </div>
                     <div class="form-group">

                                         <button type="submit" class="btn btn-primary" style="width: 100%"><i class="icon s7-mail"></i> Avantaj Güncelle</button>
                    </div>
                   </form>
                 </div>
                </div>
              </div>
            </div>
          </div>

@endsection