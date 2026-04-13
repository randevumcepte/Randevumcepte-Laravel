

@extends('layout.layout_kampanyalar')
@section('content')
<section class="block">
                <div class="container">
                    <h2 style="text-align: center;font-size:40px"><strong>İZMİR'İN EN AVANTAJLI KAMPANYALARI</strong></h2>
                    
                    <div class="items grid grid-xl-3-items grid-lg-3-items grid-md-3-items" style="margin-top: 30px">
                    
              @foreach($salonlar as $onecikansalon)
                        
                        <div class="item vitrin">
                           
                            <!--end ribbon-->
                            <div class="wrapper">
                                <div class="image anasayfavitrin">
                                     
                                    <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}/{{$onecikansalon->kampanya_id}}" class="image-wrapper background-image">
                                         @foreach($salongorselleri as $kapakgorsel)
                                                @if($kapakgorsel->salon_id == $onecikansalon->id && $kapakgorsel->kampanya_gorsel_kapak == 1 && $kapakgorsel->kampanya_gorsel==1 && $kapakgorsel->kampanya_id == $onecikansalon->kampanya_id)
                                        <img src="{{secure_asset($kapakgorsel->salon_gorseli)}}"  alt="Kapak Görseli">
                                        @endif
                                        @endforeach
                                    </a>
                                </div>
                                <!--end image-->
                               
                               <!-- <div class="price">{{$onecikansalon->kampanya_fiyat}} <span class="simge-tl">&#8378;</span></div>-->
                                <div class="description kampanyaaciklama"> 
                                	 <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi)))}}/{{$onecikansalon->kampanya_id}}">
                                	<h5 title="{{$onecikansalon->kampanya_baslik}}" class="kampanyabaslik">{{$onecikansalon->kampanya_baslik}}</h5> 

                                		{{$onecikansalon->kampanya_aciklama}}

                                		<br/>

                                	</a>
                                </div>
                                
                                <div class="avantajaltkisim">
                                	 <a href="/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi)))}}/{{$onecikansalon->kampanya_id}}" style="color: white" title="{{$onecikansalon->kampanya_aciklama}}">
                                	    <h5 style="float:left;margin-top:15px;font-size:16px" class="location">{{$onecikansalon->ilce->ilce_adi}}</h5>
										<div style="float: right;">
										<span style="text-decoration: line-through;">{{$onecikansalon->hizmet_normal_fiyat}} <span class="simge-tl">&#8378;</span></span>
										<span style="color:white;font-size:30px;margin-left: 10px; border:1px solid white; border-radius: 5px;padding:0 10px 0 10px">{{$onecikansalon->kampanya_fiyat}} <span class="simge-tl">&#8378;</span></span>
									</div>
                                	 
										</a>

                                </div>
                                
                                
                              
                               
                              <!--  <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="detail text-caps underline">Detaylar</a>-->
                            </div>
                        </div>
                        @endforeach
                    

                    </div>
                    <div class="col-md-12" style="text-align: center;">
                    <button id="tumvitrinigoruntule" class="btn btn-primary">TÜM KAMPANYALARI GÖRÜNTÜLE</button>
                </div>
                </div>
            </section>
@endsection