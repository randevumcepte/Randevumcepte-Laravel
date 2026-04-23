@extends('layout.layout')
@section('content')
         
            <section class="block" style="margin-top:90px">
                <div class="container">
                    <div class="row">
                        
                        <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                           

                            <div class="items list grid-xl-4-items grid-lg-3-items grid-md-2-items">
                              
                            @foreach($salonlar as $salon)


                          
                                <div class="item avantajvitrin">
                                    @if(\App\SalonPuanlar::where('salon_id',$salon->id)->count() > 0)
                                    @if($salon->salon_turu->sektor_id != 2)
                                    <div class="ribbon-featured">Öne Çıkan</div>
                                    @else
                                     <div class="ribbon-featured doktor">Öne Çıkan</div>
                                    @endif
                                    @endif
                                    <!--end ribbon-->
                                    <div class="wrapper">
                                        <div class="image kategorivitrin">
                                            <h3>
                                              <!--  <a href="#" class="tag category">{{$salon->salon_adi}}</a>-->
                                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi))) }}" class="title">{{$salon->salon_adi}}</a>
                                                <!--<span class="tag">Offer</span>-->
                                            </h3>
                                            <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi))) }}" class="image-wrapper background-image">

                                                <img src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$salon->id)->where('kapak_fotografi',1)->value('salon_gorseli'))}}" alt="Salon Görseli">
                                            </a>
                                        </div>
                                        <h4 class="location">
                                            
                                                 @if(\App\SalonPuanlar::where('salon_id',$salon->id)->count()>0)
                                                        <div class="rating"  data-rating="{{round(\App\SalonPuanlar::where('salon_id',$salon->id)->sum('puan')/\App\SalonPuanlar::where('salon_id',$salon->id)->count(),2)}}">
                                                           
                                                          <span class="ratingdescription" style=" float: right;"> |  {{round(\App\SalonPuanlar::where('salon_id',$salon->id)->sum('puan')/\App\SalonPuanlar::where('salon_id',$salon->id)->count(),2)}} ({{\App\SalonPuanlar::where('salon_id',$salon->id)->count()}} tekil puanlama)</span>

                                                          </div> 
                                                  @else
                                                         <div class="rating" data-rating="0"></div>
                                                  @endif
                                            
                                        </h4>
                                        <!--end image-->
                                        <h4 class="location">
                                            <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}">{{$salon->il->il_adi}}&nbsp;>&nbsp;</a><a href="{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}">{{$salon->ilce->ilce_adi}} {{$salon->salon_turu->salon_turu_adi}}</a>
                                        </h4>
                                        @if($salon->salon_turu->sektor_id!=2)
                                        <div class="price randevu">Bilgi Alınız</div>

                                        <div class="meta">
                                            <figure>
                                                <span style="color:#FF4E00;font-size: 28px;padding:0">Bilgi Alınız</span>
                                            </figure>
                                        </div>
                                         @endif
                                      
                                        @foreach($salonyorumlar as $key => $salonensonyapilanyorum)
                                         @if($salonensonyapilanyorum->salon_id == $salon->id && $key==$salonyorumlar->count()-1)

                                        <div class="description">
                                            <p>{{\App\User::where('id',$salonensonyapilanyorum->user_id)->value('name')}} : {{$salonensonyapilanyorum->yorum}} </p>
                                        </div>
                                        @endif
                                        @endforeach
                                        @if($salonyorumlar->count()==0)
                                            <div class="description">
                                                 <p></p>
                                            </div>

                                        @endif

                                        @if($salon->memnuniyet_garantisi == 1)
                                       <a   class="detail text-caps" id="memnuniyetgarantisi">
                                         <img src="{{secure_asset('public/img/altin.png')}}" width="80" height="70" alt="%100 Memnuniyet Garantisi"></a> 
                                        @endif
                                        <!--end description-->
                                        @if($salon->salon_turu->sektor_id!=2)
                                        <a style="border-radius: 60px" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi))) }}" class="detail text-caps underline">RANDEVU AL</a>
                                        @else
                                         <a style="border-radius: 60px" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi))) }}" class="detail text-caps underline doktor">RANDEVU AL</a>
                                        @endif
                                    </div>
                                </div>
                                <!--end item-->

                                 

                          
                            @endforeach  </div>
                            @if($salonlar->count() ==0)
                            <a href="/" class="btn btn-primary btn-framed btn-rounded hizmetsektorbulunamadi" style="width: 100%;text-align: center;margin-bottom: 20px">Sorgunuza uyan işletme veya hizmet bulunamadı.</span></a>
                            @endif
                            <!--============ End Items ==============================================================-->
                            <!--<div class="page-pagination">
                                <nav aria-label="Pagination">
                                    <ul class="pagination">
                                        <li class="page-item">
                                            <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">
                                            <i class="fa fa-chevron-left"></i>
                                        </span>
                                                <span class="sr-only">Previous</span>
                                            </a>
                                        </li>
                                        <li class="page-item active">
                                            <a class="page-link" href="#">1</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">2</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">3</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="#" aria-label="Next">
                                        <span aria-hidden="true">
                                            <i class="fa fa-chevron-right"></i>
                                        </span>
                                                <span class="sr-only">Next</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                            <!--end page-pagination-->
                        </div>
                        <!--end col-md-9-->
                        <!--fırsat ve kampanya bölümü-->
                        <div class="col-12 col-sm-12 col-md-12 col-lg-3">
                            <aside class="sidebar">
                                <section>
                                     
                                    
                                     @foreach($salonturleri as $salonturuliste)
                                    <div class="form-group">
                                        @if(($il != null || $il != '') && ($ilce=='' || $ilce == null))
                                                <a style="border-radius: 60px" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturuliste->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il))) }}" class="btn btn-light btn-framed width-100 kategoributton">{{$salonturuliste->salon_turu_adi}}</a>
                                        @endif
                                        @if($ilce != null || $ilce != '')
                                         <a style="border-radius: 60px" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturuliste->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilce))) }}" class="btn btn-light btn-framed width-100 kategoributton">{{$salonturuliste->salon_turu_adi}}</a>
                                        @endif
                                        @if($il == null || $il == '')
                                              <a style="border-radius: 60px" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturuliste->salon_turu_adi))) }}" class="btn btn-light btn-framed width-100 kategoributton">{{$salonturuliste->salon_turu_adi}}</a>

                                        @endif


                                    </div>
                                    @endforeach
                                  
                                </section>
                                
                            </aside>
                        </div>
                        

                        
                    </div>
                </div>
                <!--end container-->
            </section>
        
         
           
@endsection
        