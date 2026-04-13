@extends('layout.layout')
@section('content')

 <section class="block">
                <div class="container">
                  <div class="row">
                   <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                    <h2 style="text-align: center;">Arama Sonuçları</h2>
                    <p style="text-align: center;"><strong>"{{$aramakelimeler}}"</strong> kelimelerini içeren <strong>{{$salonlar->count()}}</strong> sonuç bulundu</p>
                      <div class="items grid grid-xl-4-items grid-lg-4-items grid-md-3-items" style="margin-top: 30px">
                         <?php $goruntulenen_salonlar = array();?>
                        @foreach($salonlar as $onecikansalon)
                        
                        @if(!in_array($onecikansalon->salon_id, $goruntulenen_salonlar) && ($onecikansalon->uyelik_turu==1 ||$onecikansalon->uyelik_turu==3))
                        <div class="item vitrin">
                            @if($onecikansalon->salon_turu->sektor_id==2)
                            <div class="ribbon-featured doktor">
                                @else
                                <div class="ribbon-featured">
                                @endif
                             
                             {{$onecikansalon->salon_turu->salon_turu_adi}}
                               
                            </div>
                           
                            <!--end ribbon-->
                            <div class="wrapper">
                                <div class="image anasayfavitrin">
                                    <h3>
                                        
                                        <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="title">{{$onecikansalon->salon_adi}}</a>
                                       
                                    </h3>
                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="image-wrapper background-image">
                                         @foreach($salongorselleri as $kapakgorsel)
                                                @if($kapakgorsel->salon_id == $onecikansalon->salon_id && $kapakgorsel->kapak_fotografi == 1)
                                        <img src="{{secure_asset($kapakgorsel->salon_gorseli)}}"  alt="{{$onecikansalon->salon_adi}}">
                                        @endif
                                        @endforeach
                                    </a>
                                </div>
                                <!--end image-->
                                <h4 class="location">
                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}">{{$onecikansalon->ilce->ilce_adi}} {{$onecikansalon->salon_turu->salon_turu_adi}}</a>
                                </h4>
                                
                              
                               @if($onecikansalon->salon_turu->sektor_id==2)
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="price doktor">Randevu Al</a>
                                @else
                                 <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="price">Randevu Al</a>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($onecikansalon->kampanya_baslik)
                                <div class="item vitrin">
                                      <div class="ribbon-featured avantajlikampanya">
                             
                                             AVANTAJLI KAMPANYA
                              
                                      </div>
                                            <div class="wrapper">
                                                <div class="image anasayfavitrin">
                                                    <h3>
                                                        <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}/{{$onecikansalon->kampanya_id}}" class="category">{{$onecikansalon->salon_adi}}</a>
                                                        <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}/{{$onecikansalon->kampanya_id}}" class="title">{{$onecikansalon->kampanya_baslik}}</a>
                                                         
                                                    </h3>
                                                    <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->salon_id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}/{{$onecikansalon->kampanya_id}}" class="image-wrapper background-image">

                                                        <img src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$onecikansalon->salon_id)->where('kampanya_gorsel_kapak',1)->where('kampanya_id',$onecikansalon->kampanya_id)->value('salon_gorseli'))}}" alt="{{$onecikansalon->kampanya_baslik}}">
                                                    </a>
                                                </div>
                                                <!--end image-->
                                                <h4 class="location">
                                                    <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}">{{$onecikansalon->ilce->ilce_adi}} {{$onecikansalon->salon_turu->salon_turu_adi}}</a>
                                                </h4> 
                                                <div class="priceavantaj">{{$onecikansalon->kampanya_fiyat}} <span class="simge-tl">&#8378;</span>  (%{{round((($onecikansalon->hizmet_normal_fiyat-$onecikansalon->kampanya_fiyat)/$onecikansalon->hizmet_normal_fiyat)*100,0)}} <span class="indirimyazimin432">indirim</span><span class="indirimyazimax431">ind.</span>) </div>
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
                        @endif
                        <?php array_push($goruntulenen_salonlar,$onecikansalon->salon_id); ?>
                        @endforeach
                    
                      
                    </div>
                    <div class="col-md-12" style="text-align: center;">
                    <button id="tumvitrinigoruntule" class="btn btn-primary">TÜM VİTRİNİ GÖRÜNTÜLE</button>
                    </div> 
                </div>
                 
            </section>
@endsection