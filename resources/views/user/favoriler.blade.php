@extends('layout.layout_favoriler')
@section('content')

        <section class="block">
                <div class="container">
                	<div class="col-md-12" >

                             <ul class="nav nav-pills" id="myTab-pills" role="tablist" style="text-align: center;">
                                    <li class="nav-item">
                                        <a class="nav-link icon" href="/profilim"><i class="fa fa-user" style="color:white"></i>Profilim</a>
                                    </li>
                                    <li class="nav-item">
                                       <a class="nav-link icon" href="/randevularim">
                                    <i class="fa fa-heart"></i>Randevularım
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                         <a class="nav-link active icon" href="/favorilerim">
                                             <i class="fa fa-star"></i>Favorilerim
                                         </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link icon" href="/firsatlarim">
                                            <i class="fa fa-check"></i>Fırsatlarım
                                          </a>
                                    </li>
                                     <li class="nav-item">
                                         <a class="nav-link icon" href="/ayarlarim">
                                             <i class="fa fa-recycle"></i>Ayarlarım
                                        </a> 
                                    </li>
                                </ul>
                        
                        </div>
                	<div class="items grid grid-xl-4-items grid-lg-4-items grid-md-4-items" style="margin-top:30px">

                		@foreach($favoriler as $favori)
                           <div class="item">
                           	  <div class="image">
                           	  	@foreach(\App\Salonlar::where('id',$favori->id)->get() as $onecikansalon)
                           	  	<h3>
                                        
                                        <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="title">{{$onecikansalon->salon_adi}}</a>
                                       
                                    </h3>
                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="image-wrapper background-image">
                                         @foreach(\App\SalonGorselleri::all() as $kapakgorsel)
                                                @if($kapakgorsel->salon_id == $onecikansalon->id && $kapakgorsel->kapak_fotografi == 1)
                                        <img src="{{secure_asset($kapakgorsel->salon_gorseli)}}"  alt="Kapak Görseli">
                                        	@endif
                                        @endforeach
                                    </a>
                                     <h4 class="location">
                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}">{{$onecikansalon->ilce->ilce_adi}} {{$onecikansalon->salon_turu->salon_turu_adi}}</a>
                                </h4> 
                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="detail text-caps underline">Detaylar</a>
                                 </div>
                                <!--end image-->
                               
                                    @endforeach
                           	  </div>
                           

                		@endforeach
                	</div>
                </div>
     </section>



@endsection