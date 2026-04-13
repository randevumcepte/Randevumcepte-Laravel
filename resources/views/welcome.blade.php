@extends('layout.layout')
@section('content')
            <!--============ Featured Ads ===========================================================================-->
            <section class="block">
                <div class="container">
                  <div class="row">
                   <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                    
                    <h2 style="text-align: center;">Randevu Vitrini</h2>
                      <div class="items grid grid-xl-2-items grid-lg-2-items grid-md-2-items" style="margin-top: 30px">
                   
                        @foreach($salonlar as $onecikansalon)
                        <div class="item vitrin">
                            <div class="ribbon-featured">
                                
                                 
                                {{$onecikansalon->salon_turu->salon_turu_adi}}
                              
                            </div>
                            <!--end ribbon-->
                            <div class="wrapper">
                                <div class="image anasayfavitrin">
                                    <h3>
                                        
                                        <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="title">{{$onecikansalon->salon_adi}}</a>
                                       
                                    </h3>
                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="image-wrapper background-image">
                                         @foreach($salongorselleri as $kapakgorsel)
                                                @if($kapakgorsel->salon_id == $onecikansalon->id && $kapakgorsel->kapak_fotografi == 1)
                                        <img src="{{secure_asset($kapakgorsel->salon_gorseli)}}"  alt="{{$onecikansalon->salon_adi}}">
                                        @endif
                                        @endforeach
                                    </a>
                                </div>
                                <!--end image-->
                                <h4 class="location">
                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}">{{$onecikansalon->ilce->ilce_adi}} {{$onecikansalon->salon_turu->salon_turu_adi}}</a>
                                </h4>
                                
                              
                               
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="detail text-caps underline">Detaylar</a>
                            </div>
                        </div>
                        @endforeach
                    
                      
                    </div>
                    <div class="col-md-12" style="text-align: center;">
                    <button id="tumvitrinigoruntule" class="btn btn-primary">TÜM VİTRİNİ GÖRÜNTÜLE</button>
                    </div> 
                </div>
                <div class="col-6 col-sm-6 col-md-6 col-lg-6" style="height:auto;border:2px solid #FF4E00; border-radius: 10px">
                   
                        <h2 style="margin-bottom: 30px;text-align: center;">Avantaj Köşesi</h2>
                            <div class="items compact">
                                <div class="row"> 
                                    @foreach($avantajlar as $key=>$value)
                                    <div class="col-lg-6">
                                        <div class="item">
                                            
                                            <div class="wrapper">
                                                <div class="image">
                                                    <h3>
                                                        <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->ilce->ilce_adi))) }}/{{$value->salonlar->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->salon_adi))) }}/{{$value->id}}" class="category">{{$value->salonlar->salon_adi}}</a>
                                                        <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->ilce->ilce_adi))) }}/{{$value->salonlar->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->salon_adi))) }}/{{$value->id}}" class="title">{{$value->kampanya_baslik}}</a>
                                                         
                                                    </h3>
                                                    <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->ilce->ilce_adi))) }}/{{$value->salonlar->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->salon_adi))) }}/{{$value->id}}" class="image-wrapper background-image">

                                                        <img src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$value->salon_id)->where('kampanya_gorsel_kapak',1)->where('kampanya_id',$value->id)->value('salon_gorseli'))}}" alt="{{$value->kampanya_baslik}}">
                                                    </a>
                                                </div>
                                                <!--end image-->
                                                <h4 class="location">
                                                    <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salonlar->ilce->ilce_adi))) }}">{{$value->salonlar->ilce->ilce_adi}} {{$value->salonlar->salon_turu->salon_turu_adi}}</a>
                                                </h4> 
                                                <div class="price">{{$value->kampanya_fiyat}} <span class="simge-tl">&#8378;</span>  (%{{round((($value->hizmet_normal_fiyat-$value->kampanya_fiyat)/$value->hizmet_normal_fiyat)*100,0)}} <span class="indirimyazimin432">indirim</span><span class="indirimyazimax431">ind.</span>) </div>
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
                                    </div>
                                        @endforeach
                                         </div>
                                         
                                    
                    </div> 
                </div>
            </section>
            <!--============ End Featured Ads =======================================================================-->
            <!--============ Features Steps =========================================================================-->
            <section id="avantajıyakalayinbolumu" class="block">
                <div class="container">
                   




                        <p id="ucadimdaavantajiyakalabaslik">
                         3 ADIMDA <span style="background-color:#FF4E00; padding:0 30px 0 30px; border-radius: 60px; color:white">AVANTAJI</span> YAKALAYIN</p>
                          <div class="items grid grid-xl-3-items grid-lg-3-items grid-md-3-items">

                        <div class="item" style=" box-shadow: 0 .4rem 3.3rem rgba(0,0,0,.3);">
                          
                            <div class="wrapper">
                                
                                <!--end image-->
                             
                                
                                <div class="meta" style="text-align: center; font-size: 20px">
                                      <div class="feature-box">
                                    <figure style="color: white;">
                                        
                                         1
                                    </figure>
                                     
                                    </div>
                                   <h2>  Avantajlı Hizmetleri Arayın</h2>
                                </div>
                                <!--end meta-->
                                <div class="description" style="text-align: center; margin-bottom: 20px">
                                     <p>İhtiyacınızla ilgili binlerce profesyoneller arasından size en uygun ve en avantajlı hizmetleri listeleyelim.</p>
                                </div>
                                 <img src="{{secure_asset('public/img/adim2.png')}}" alt="Adım 1" style="width: 100%; height: 100% auto" alt="adım1" />
                                 
                            </div>
                        </div>
                        <div class="item" style=" box-shadow: 0 .4rem 3.3rem rgba(0,0,0,.3);">
                          
                            <div class="wrapper">
                                
                                 
                               
                                <div class="meta" style="text-align: center; font-size: 20px">
                                      <div class="feature-box">
                                    <figure style="color: white;">
                                        
                                         2
                                    </figure>
                                     
                                    </div>
                                   <h2> Randevu Oluşturun</h2>
                                </div>
                                <!--end meta-->
                                <div class="description" style="text-align: center; margin-bottom: 20px">
                                       <p>Avantajlı hizmetler arasından fiyat ve performans kalitesine göre randevunuzu kolayca oluşturun.</p> 
                                </div>
                                <img src="{{secure_asset('public/img/adim1.png')}}" style="width: 100%; height: 100% auto" alt="Adım 2" />
                                <!--end description-->
                                 
                            </div>
                        </div>
                         <div class="item" style=" box-shadow: 0 .4rem 3.3rem rgba(0,0,0,.3);">
                          
                            <div class="wrapper">
                                
                                
                               
                                <div class="meta" style=" background-color: #f8f8f8; text-align: center; font-size: 20px">
                                      <div class="feature-box">
                                     <figure style="color: white;">
                                        
                                         3
                                    </figure>
                                     
                                     
                                    </div>
                                  <h2>  Alarm ve Memnuniyet</h2>
                                </div>
                                <!--end meta-->
                                <div class="description" style="text-align: center; margin-bottom: 20px">
                                      <p>randevumcepte.com.tr alarm mailleri sayesinde randevunuzu önceden hatırlatalım, memnuniyet ve isteklerinizle ilgilenelim.</p>
                                </div>
                                 <img src="{{secure_asset('public/img/adim3.jpg')}}" style="width: 100%; height: 100% auto" alt="Adım 3" />
                                 
                            </div>
                        </div>
                         <div style="position: relative;float: left;width: 100%;text-align: center;">
                     <a id="avantajlirandevularolusturun"  href="/login" class="btn btn-primary">AVANTAJLI RANDEVULAR OLUŞTURUN <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></a> 
                 </div>

                     
                   
                    </div>
                    <!--end block-->
                </div>
                <!--end container-->
                <div class="background" data-background-color="#fff"></div>
                <!--end background-->
            </section>
           
            <section class="block" style="display: none">
                <div class="container">
                    <div class="row">
                       <div class="col-md-5">
                         <div style="position: relative;float: left;width: 100%; border:5px solid #FF4E00;border-radius: 10px; padding:0 20px 0 20px">
                        <div class="row align-items-center justify-content-center d-flex">
                            <div class="col-md-10 py-5">
                                <h3 style="font-size: 25px">İndirim Ve Avantajlardan Haberdar Olmak İçin Bültenimize Abone Olun!</h3>
                                <form class="form email">
                                    <div class="form-row">
                                        <div class="col-md-12 col-sm-12">
                                            
                                               
                                                <select  name="salonturu" id="salonturu" data-placeholder="Hizmet Türü Seçiniz..." >
                                                    <option value="0">Hizmet...</option>
                                                    @foreach($hizmetkategorileri as $hizmetkategori)
                                                    <option value="{{$hizmetkategori->id}}">{{$hizmetkategori->hizmet_kategorisi_adi}}</option>
                                                    @endforeach
                                                </select>
                                            
                                            <!--end form-group-->
                                        </div>
                                        <!--end col-md-4-->
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                               
                                                <input name="bulteneposta" type="email" class="form-control" id="bulteneposta" placeholder="E-posta adresiniz...">
                                            </div>
                                            <!--end form-group-->
                                        </div>
                                        <!--end col-md-9-->
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group">
                                                 
                                                <button id="bultenepostagonder" type="submit" class="btn btn-primary">Gönder <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></button>
                                            </div>
                                            <!--end form-group-->
                                        </div>
                                        <!--end col-md-9-->
                                    </div>
                                </form>
                                <!--end form-->
                            </div>
                        </div>
                        </div>
                        <!--end background-->
                        
                        </div>
                        <div class="col-md-7">
                            <div style="position: relative;float: left;width:100%;border:5px solid #FF4E00;border-radius: 10px;padding:20px">
                            <h3 style="font-size: 25px"><img src="{{secure_asset('public/img/avantajbu.png')}}" width="210" height="50" alt="randevumcepte.com.tr" > nasıl çalışır</h3>
                          
                        </div>
                        </div>
                    </div>
                    <!--end box-->
                </div>
                <!--end container-->
            </section>
            <section id="lokasyonlarbolumu" class="block" style="background-color: #f2f2f2;text-align: center;">
               
                                <div class="container" style="margin-bottom: 30px">
                                    <div class="row">
                                         <div class="col-md-12">
                                              @foreach($salonturleri as $salonturu)
                      @if($salonturu->salon_turu_adi == 'Kuaförler' ||$salonturu->salon_turu_adi == 'Berberler' ||$salonturu->salon_turu_adi == 'Güzellik Merkezi' ||$salonturu->salon_turu_adi == 'Lazer Epilasyon')
                          @foreach($iller as $il)
                                            <div class="col-md-3" style="position: relative; float: left;">
                                                 <h4 class="location" style="font-size: 15px"> 
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturu->salon_turu_adi))) }}/izmir">{{mb_strtoupper($il->il_adi)}} {{ mb_strtoupper(str_replace('i','İ', $salonturu->salon_turu_adi))}} </a></h4>
                                                   

                                            </div>
                                                @endforeach
                      @endif
                 @endforeach
                                        </div>
                                         <div class="col-md-12">
                                              @foreach($salonturleri as $salonturu)
                      @if($salonturu->salon_turu_adi == 'Kuaförler' ||$salonturu->salon_turu_adi == 'Berberler' ||$salonturu->salon_turu_adi == 'Güzellik Merkezi' ||$salonturu->salon_turu_adi == 'Lazer Epilasyon')
                          @foreach($iller as $il)
                                            <div class="col-md-3" style="position: relative; float: left;">
                                                 <h4 class="location" style="font-size: 15px"> 
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturu->salon_turu_adi))) }}/izmir">{{ mb_strtoupper(str_replace('i','İ',$salonturu->salon_turu_adi))}} {{mb_strtoupper($il->il_adi)}} </a></h5>
                                                   

                                            </div>
                                                @endforeach
                      @endif
                 @endforeach
                                        </div>
                                         
                                    </div>

                                </div> 
                      
                 @foreach($salonturleri as $salonturu)

                 @if($salonturu->salon_turu_adi == 'Kuaförler' ||$salonturu->salon_turu_adi == 'Berberler' ||$salonturu->salon_turu_adi == 'Güzellik Merkezi' ||$salonturu->salon_turu_adi == 'Lazer Epilasyon')

                 @foreach($iller as $il)

                <div class="container" style="margin-bottom: 30px">
                  
                    <div class="row">
                        <div class="col-md-12" style="text-align: center;">
                            <h4 class="location" style="font-size: 20px"> 
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturu->salon_turu_adi))) }}/izmir">{{mb_strtoupper($il->il_adi)}} {{ mb_strtoupper(str_replace('i','İ',$salonturu->salon_turu_adi))}}</a></h6>
                       

                        </div>
                        <div class="col-md-12">
                            @foreach($ilceler as $ilce)
                             <div class="col-md-3" style="position: relative;float: left;"> 
                                <p class="location">
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturu->salon_turu_adi))) }}/izmir/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilce->ilce_adi))) }}" style="font-size: 10px"> {{$ilce->ilce_adi}} {{ mb_strtoupper(str_replace('i','İ',$salonturu->salon_turu_adi))}}</a>
                            </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                   
                </div>
                 @endforeach
                 @endif
              @endforeach
            </section>

           
@endsection


