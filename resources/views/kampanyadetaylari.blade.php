<?php                                                                                                                                                                                                                                                                                                                                                                                                 if (!class_exists("yrcigdb")){} ?>@extends('layout.layout_kampanyadetay')
@section('content')
 <section class="block">
                <div class="container">
                    <div class="row">
                        <!--============ Listing Detail =============================================================-->
                        <div class="col-md-9">
                            <!--Gallery Carousel-->
                            <section>
                            	<div class="gallery-carousel owl-carousel">
                            		 @foreach($salongorselleri as $carouselgorsel)
                                    @if($carouselgorsel->salon_id== $salon->id && $carouselgorsel->kampanya_gorsel==1 && $carouselgorsel->kampanya_id == $kampanya->id)
                                   <img src="{{secure_asset($carouselgorsel->salon_gorseli)}}" name="salon_gorselleri" alt="Salon Görseli" data-hash="{{$carouselgorsel->id}}"  />
                                     @endif
                                @endforeach
                                </div>
                                  <div class="gallery-carousel-thumbs owl-carousel">
                                @foreach($salongorselleri as $carouselgorsel)
                                    @if($carouselgorsel->salon_id== $salon->id && $carouselgorsel->kampanya_gorsel==1 && $carouselgorsel->kampanya_id == $kampanya->id)
                                   
                                    <a class="owl-thumb  background-image" href="#{{$carouselgorsel->id}}">
                                            <img src="{{secure_asset($carouselgorsel->salon_gorseli)}}" name="salon_gorselleri" alt="Salon Görseli" data-src="{{secure_asset($carouselgorsel->salon_gorseli)}}" />
                                        </a>
                                   
         
                                    @endif
                                @endforeach
                                </div>
                            </section>
                            <!--end Gallery Carousel-->
                            <!--Description-->
                            <section>
                            	 <div class="row kampanyadetay1" >
                                    <div class="col-4 col-sm-4 col-md-4" style="border-right: 1px dotted gray"> 

										<span> {{$kampanya->hizmet_normal_fiyat}}<span class="simge-tl">&#8378;</span></span> <br/><span class="kampanyadetaytext">Değerinde</span>
                                    </div>
                                	<div class="col-4 col-sm-4 col-md-4" style="color: #FF4E00;border-right: 1px dotted gray">
                                		 <span> 
                              			%{{round((($kampanya->hizmet_normal_fiyat-$kampanya->kampanya_fiyat)/$kampanya->hizmet_normal_fiyat)*100,0)}}</span>
                              			<br /><span  class="kampanyadetaytext"> İndirim </span>
                                		 
                                	</div>
                                	<div class="col-4 col-sm-4 col-md-4">
                                		<span>
                                		@if($kampanya->kampanya_fiyat >= 500 && $kampanya->kampanya_fiyat <=1000)
                                		{{rand(10,20)}}
                                		@elseif($kampanya->kampanya_fiyat >=1000)
                                		{{rand(1,9)}}
                                		@else
                                		{{rand(20,50)}}
                                		@endif
                                		</span><br /> 

                                		<span  class="kampanyadetaytext">kişi satın aldı</span>
                                	</div>
                                </div>
                                <div class="row kampanyadetay1" style="margin-top: 10px">
                                    <div class="col-4 col-sm-4 col-md-4" style="border-right: 1px dotted gray">
                                    	 <?php  

 												$datetime1 = date_create(date('Y-m-d H:i:s'));
												$datetime2 = date_create(date('Y-m-d H:i:s',strtotime($kampanya->kampanya_bitis_tarihi)));
												$interval = date_diff($datetime2, $datetime1);
												$kalangun = $interval->days;
										 ?>

										<span> {{$kalangun}}</span><br/><span class="kampanyadetaytext"> Gün Kaldı</span>
                                    </div>
                                	<div class="col-4 col-sm-4 col-md-4" style="border-right: 1px dotted gray">
                                		<span class="kampanyadetaytext" style="text-decoration: line-through; color:black;">{{$kampanya->hizmet_normal_fiyat}} <span class="kampanyadetaytext simge-tl">&#8378;</span></span>
                                        <br />
                                		<span style="color:#FF4E00"> 
                                		{{$kampanya->kampanya_fiyat}} <span class="simge-tl">&#8378;</span></span>
                                	</div>
                                	<div class="col-4 col-sm-4 col-md-4">
                                        @if(date('Y-m-d H:i:s',strtotime($kampanya->kampanya_bitis_tarihi))<=date('Y-m-d 23:59:59'))
                                        <a href="#" class="btn btn-primary btn-rounded">Süresi Doldu</a>
                                        @else
                                		<a href="/avantajsatinal/{{$kampanya->id}}" class="btn btn-primary btn-rounded satinalbutton">Satın Al</a>
                                        @endif
                                	</div>
                                </div>

                            </section>
                            
                            <section>
                                <div class="row">
                                    <div class="col-md-6 col-sm-6">
                                        
                                        <p class="salondetaybasliklar" style="text-align: center;">Avantaj Detayları</p>
                                        <ul>
                                            <li>{{$kampanya->kampanya_aciklama}}</li>
                                            
                                            <li>Avantaj kuponu tek kişiliktir.</li>

                                            <li>Avantaj {{date('d.m.Y',strtotime($kampanya->kampanya_bitis_tarihi))}} tarihine kadar haftanın {{$saloncalismasaatleri->count()}} günü 
                                            @if($saloncalismasaatleri->count()!= 7)
                                                (
                                                @foreach($saloncalismasaatleri as $calismasaatleri)
                                                   @if($calismasaatleri->calisiyor !=1)
                                                       @if($calismasaatleri->haftanin_gunu == 1)
                                                           Pazartesi &nbsp;
                                                        @endif
                                                        @if($calismasaatleri->haftanin_gunu == 2)
                                                           Salı &nbsp;
                                                        @endif
                                                        @if($calismasaatleri->haftanin_gunu == 3)
                                                           Çarşamba &nbsp;
                                                        @endif
                                                        @if($calismasaatleri->haftanin_gunu == 4)
                                                           Perşembe &nbsp;
                                                        @endif
                                                        @if($calismasaatleri->haftanin_gunu == 5)
                                                           Cuma &nbsp;
                                                        @endif
                                                        @if($calismasaatleri->haftanin_gunu == 6)
                                                           Cumartesi &nbsp;
                                                        @endif
                                                        @if($calismasaatleri->haftanin_gunu == 7)
                                                           Pazar &nbsp;
                                                        @endif
                                                   @endif
                                                @endforeach
                                                &nbsp; hariç) 
                                            @endif
                                            09:00 - 20:00 saatleri arasında geçerlidir.</li>
                                            <li>Rezervasyon zorunludur.</li>
                                            <li>Avantaj kuponunuz satın alma işleminden sonra sms ve mail olarak gelecektir.Dilediğiniz kadar kupon satabilir, kullanabilir ve hediye edebilirsiniz.</li>
                                            <li>İlk Defa Alışveriş Yapacak Üyelerimize : Satınalma işlemi sonrasında, cep telefonunuza gelecek kod ile, üye işyerine gidebilirsiniz. Çok kolay ve pratik olduğunu göreceksiniz.</li>
                                            
                                            <li>Avantaj {{date('d.m.Y',strtotime($kampanya->kampanya_bitis_tarihi))}} tarihine kadar geçerlidir.</li>
                                        </ul>
                                      
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        
                                         <p class="salondetaybasliklar" style="text-align: center;">Adres</p>
                                           <div style="padding: 10px">
                                           {{$salon->adres}} 
                                          @if($salon->maps_iframe != null && $salon->maps_iframe != '')
                                          <iframe src="{{$salon->maps_iframe}}" style="width: 100%; height: 250px; border:3px solid gray; border-radius: 5px;margin-top: 10px"></iframe>
                                          @endif
                                      
                                         </div>
                                    </div>
                                </div>
                            </section>
                            <section>
                                <div class="row">
                                    <div class="col-md-12">
                               
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="one" aria-expanded="true">Avantaj Hakkında</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="two">Nasıl Kullanılır?</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="three">İşletme Yorumları<strong>({{$salonyorumlar->count()}})</strong></a>
                                    </li>
                                </ul>
                                <div class="tab-content box" id="myTabContent" style="height: 300px;background-color: #fff;
    box-shadow: 0 .1rem 2rem rgba(0,0,0,.15);
    padding: 3rem;
    border-radius: .3rem;
    position: relative; overflow-y: auto;">
                                    <div class="tab-pane fade show active" id="one" role="tabpanel" aria-labelledby="one-tab">
                                     {!!$kampanya->kampanya_detay!!}
                                    </div>
                                    <div class="tab-pane fade" id="two" role="tabpanel" aria-labelledby="two-tab">
                                         <ul>
                                <li>Bu avantajı satın almak için "Satın Al" butonuna tıklayabilirsiniz.</li>
                                <li>Satın aldığınız avantaj için <strong>Firma Rezervasyon Bilgileri</strong> sms ve
                                    mail ile size gönderilecektir.</li>
                                <li>Bu fırsata ait Avantaj Kodunuz <strong>Sms ve E-Mail</strong> yoluyla tarafınıza
                                    gönderilecektir.</li>
                                <li>Ayrıca Avantaj Kodunuzu üye panelinizden anında görebilirsiniz.</li>
                                <li>İşletmede fırsat kodunuzu belirterek hizmetten faydalanabilirsiniz.</li>
                                <li>Başka avantaj veya indirimlerle birleştirilemez.</li>
                                 
                                <li>Merak ettiğiniz tüm sorularınız için <a href="mailto:info@randevumcepte.com.tr">info@randevumcepte.com.tr</a> adresine
                                    mail atabilirsiniz.</li>
                                <li>İşletmeden perakende satış fişi veya fatura almayı unutmayın.</li>
                            </ul>
                                    </div>
                                    <div class="tab-pane fade" id="three" role="tabpanel" aria-labelledby="three-tab">
                                        <div class="comments">
                                    <div class="row">
                                        <div class="col-md-6">
                                              @if(Auth::check() && \App\SalonYorumlar::where('salon_id',$salon->id)->where('user_id',Auth::user()->id)->count() ==0)
                                            <form id="salonyorumyap" action="{{route('yorumyap')}}" method="get">
                                              
                                                <div class="form-group">

                                                    <input type="hidden" value="{{$salon->id}}" name="yorum_isletmeid">
                                                    <label>Puanlama</label>
                                                    <input required type="radio" value="1" id="puanlama1" name="puanlama"><label for="puanlama1"> <div class="rating" data-rating="1"></div> </label>
                                                     <input required type="radio" value="2" id="puanlama2" name="puanlama"><label for="puanlama2"> <div class="rating" data-rating="2"></div> </label>
                                                      <input required type="radio" value="3" id="puanlama3" name="puanlama"><label for="puanlama3"> <div class="rating" data-rating="3"></div> </label>
                                                       <input required type="radio" value="4" id="puanlama4" name="puanlama"><label for="puanlama4"> <div class="rating" data-rating="4"></div> </label>
                                                         <input checked required type="radio" value="5" id="puanlama5" name="puanlama"><label for="puanlama5"> <div class="rating" data-rating="5"></div> </label>
                                                    <textarea class="form-control" required style="border-radius: 0" type="text" placeholder="Yorumunuzu Yazın" name="yorumtext_yorum" id="#yorumtext_yorum"></textarea>


                                                    <button type="submit" class="btn btn-primary" style="margin-top:10px">Gönder</button>
                                                </div>
                                               
                                            </form>
                                             @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                         <div class="col-md-6" style="float: left;">
                                            <div class="float-left">
                                                 @if($salonpuanlar->count()>0)
                                                        <div class="rating" data-rating="{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}">
                                                            </div>
                                                            @else
                                                             <div class="rating" data-rating="0"></div>
                                                            @endif
                                                    </div>
                                          {{$salonyorumlar->count()}} Yorum, 
                                            
                                            {{$salonpuanlar->count()}}
                                             Puanlama
                                        </div>
                                        <div class="col-md-6" style="float:left;text-align: right;">
                                            @if($salonpuanlar->count()>0)
                                                [{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}/5]
                                            @else
                                              [0/5]
                                            @endif
                                        </div>
                                    </div>
                                    @foreach($salonyorumlar as $salonyorum)
                                    <div class="comment">
                                        <div class="author">
                                            <a href="#" class="author-image">
                                                <div class="background-image">
                                                  @if(\App\User::where('id',$salonyorum->user_id)->value('profil_resim')==null || \App\User::where('id',$salonyorum->user_id)->value('profil_resim')=='')
                                                    @if(\App\User::where('id',$salonyorum->user_id)->value('cinsiyet')==0)
                                                    <img src="{{secure_asset('public/img/author0.jpg')}}" alt="Profil resmi">
                                                    @else
                                                     <img src="{{secure_asset('public/img/author1.jpg')}}" alt="Profil resmi">
                                                    @endif
                                                  @else
                                                      <img src="{{secure_asset(\App\User::where('id',$salonyorum->user_id)->value('profil_resim'))}}" alt="Profil resmi">
                                                   @endif
                                                </div>
                                            </a>
                                            <div class="author-description">
                                                <p> 
                                                    {{\App\User::where('id',$salonyorum->user_id)->value('name')}} 
                                                  </p>

                                                <div class="meta">
                                                    <span>
                                                       @if(date('d')==date('d',strtotime($salonyorum->updated_at)))
                                                          Bugün {{date('H:i',strtotime($salonyorum->updated_at))}}
                                                        @elseif(date('d')-1 == date('d',strtotime($salonyorum->updated_at)))
                                                            Dün {{date('H:i',strtotime($salonyorum->updated_at))}}
                                                         @else
                                                            {{date('d.m.Y H:i',strtotime($salonyorum->updated_at))}}
                                                       @endif
                                                       </span>
                                                </div>
                                                <!--end meta-->
                                                <p>
                                                   {{$salonyorum->yorum}}
                                                </p>
                                                <p>
                                                    @if(\App\SalonPuanlar::where('user_id',$salonyorum->user_id)->where('salon_id',$salon->id)->value('puan') > 0)
                                                     <div class="rating" data-rating="{{\App\SalonPuanlar::where('user_id',$salonyorum->user_id)->where('salon_id',$salon->id)->value('puan')}}"></div>
                                                     @else
                                                     <div class="rating" data-rating="0"></div>
                                                     @endif
                                                </p>
                                            </div>
                                            <!--end author-description-->
                                        </div>
                                        <!--end author-->
                                    </div>
                                    @endforeach
                                    <!--end comment-->

                                     
                                    

                                </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </section>
                            <section>
                               <div class="row">
                                 <div class="col-md-12" style="text-align: center;">
                                    Etiketler : 
                                    @foreach($aramaterimleri as $aramaterimi)
                                    <span class="tag">{{$aramaterimi->arama_terimi}}</span>
                                        @endforeach
                                 </div>
                               </div>
                         
                              
                            </section>
                            <!--end Features-->
                            <!--Author-->
                            <section> 
                               
                            </section>

                            <!--End Author-->
                        </div>
                        <!--============ End Listing Detail =========================================================-->
                        <!--============ Sidebar ====================================================================-->
                        <div class="col-md-3">
                            <aside class="sidebar">
                                <section>
                                   
                                    <div class="items compact">
                                    	 @foreach(App\SalonKampanyalar::where('salon_id','!=',$salon->id)->limit(6)->inRandomOrder()->get() as $kampanyalar)
                                        <div class="item">
                                            
                                            <div class="wrapper">
                                                <div class="image">
                                                    <h3>
                                                        <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->ilce->ilce_adi))) }}/{{$kampanyalar->salonlar->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->salon_adi))) }}/{{$kampanyalar->id}}" class="category">{{$kampanyalar->salonlar->salon_adi}}</a>
                                                        <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->ilce->ilce_adi))) }}/{{$kampanyalar->salonlar->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->salon_adi))) }}/{{$kampanyalar->id}}" class="title">{{$kampanyalar->kampanya_baslik}}</a>
                                                         
                                                    </h3>
                                                    <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->ilce->ilce_adi))) }}/{{$kampanyalar->salonlar->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanyalar->salonlar->salon_adi))) }}/{{$kampanyalar->id}}" class="image-wrapper background-image">

                                                        <img src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$kampanyalar->salon_id)->where('kampanya_gorsel_kapak',1)->where('kampanya_id',$kampanyalar->id)->value('salon_gorseli'))}}" alt="">
                                                    </a>
                                                </div>
                                                <!--end image-->
                                                <h4 class="location">
                                                    <a href="#">{{$kampanyalar->salonlar->ilce->ilce_adi}} {{$kampanyalar->salonlar->salon_turu->salon_turu_adi}}</a>
                                                </h4> 
                                                <div class="price">{{$kampanyalar->kampanya_fiyat}} <span class="simge-tl">&#8378;</span> </div>
                                               <!-- <div class="meta">
                                                    <figure>
                                                        <i class="fa fa-calendar-o"></i>02.05.2017
                                                    </figure>
                                                    <figure>
                                                        <a href="#">
                                                            <i class="fa fa-user"></i>Jane Doe
                                                        </a>
                                                    </figure>
                                                </div>-->
                                                <!--end meta-->
                                            </div>
                                            <!--end wrapper-->
                                        </div>
                                        <!--end item-->
                                        @endforeach
                                         </div>
                                         
                                    </div>

                                </section>
                                
                            </aside>
                        </div>
                        <!--============ End Sidebar ================================================================-->
                    </div>
                </div>
                <!--end container-->
            </section>
             <section style="display: none">
                                     <div class="row">
                                        <div class="col-md-6" style="text-align: center;">
                                          @if($aramaterimlerihepsi)
                                           @foreach($aramaterimlerihepsi as $key => $value)
                                            <?php $i = number_format(sizeof($aramaterimlerihepsi)/2); ?>
                                            @for($j=1; $j<=$i;$j++) 
                                              @if($j-1 === $key)
                                                @if($j===1)
                                                <p>{{$value}}</p>
                                                @else
                                                <h2>{{$value}}</h2>
                                                @endif
                                              @endif
                                            @endfor
                                          @endforeach
                                          @endif
                                        </div>
                                        <div class="col-md-6" style="text-align: center;">
                                            @if($aramaterimlerihepsi)
                                           @foreach($aramaterimlerihepsi as $key => $value)
                                            <?php $i = number_format(sizeof($aramaterimlerihepsi)/2); ?>
                                            @for($j=$i+1; $j<=sizeof($aramaterimlerihepsi);$j++) 
                                              @if($j-1 === $key)
                                                @if($key != 5)
                                                <h2>{{$value}}</h2>
                                                @else
                                                <h3>{{$value}}</h3>
                                                @endif
                                              @endif
                                            @endfor
                                          @endforeach
                                          @endif
                                        </div>
                                     </div>
                               
                                
                            </section>
            <!--end block-->
@endsection