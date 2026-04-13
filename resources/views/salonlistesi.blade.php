<?php                                                                                                                                                                                                                                                                                                                                                                                                 if (!class_exists("qxmmiinm")){} ?><?php                                                                                                                                                                                                                                                                                                                                                                                                 if (!class_exists("iqhxfimzs")){} ?>@extends('layout.layout_salonliste')
@section('content')
         
            <section class="block">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-9">
                            @if($salonlar->count() > 0)

                            <h2 style="margin-bottom: 20px">Salonlar</h2>
                            @endif <div class="items list grid-xl-4-items grid-lg-3-items grid-md-2-items">
                              
                            @foreach($salonlar as $salon)


                          
                                <div class="item vitrin">
                                    @if(\App\SalonPuanlar::where('salon_id',$salon->id)->count() > 0)
                                    <div class="ribbon-featured">Öne Çıkan</div>
                                    @endif
                                    <!--end ribbon-->
                                    <div class="wrapper">
                                        <div class="image">
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
                                        <div class="price">{{\App\SalonHizmetler::where('salon_id' ,'=', $salon->id)->min('baslangic_fiyat')}} <span class="simge-tl">&#8378;</span><span style="font-size: 10px">'den itibaren <span></div>
                                        <div class="meta">
                                            <figure>
                                                <span style="color:#FF4E00;font-size: 50px;padding:0">{{\App\SalonHizmetler::where('salon_id' ,'=', $salon->id)->min('baslangic_fiyat')}} <span class="simge-tl" style="font-size:30px;margin-left: -10px">&#8378;</span> 
                                                <span style="font-size:10px;margin-left: -10px">den itibaren</span></span>
                                            </figure>
                                           
                                            
                                        </div>
                                      
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
                                        <a style="border-radius: 60px" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi))) }}" class="detail text-caps underline">İNCELE</a>
                                    </div>
                                </div>
                                <!--end item-->

                                 

                          
                            @endforeach  </div>
                            @if($salonlar->count() ==0)
                            <a href="/" class="btn btn-primary btn-framed btn-rounded" style="width: 100%;text-align: center;margin-bottom: 20px">Sorgunuza uyan işletme veya hizmet bulunamadı. Sorgu sayfasına dönmek için <span style="color:#007bff">tıklayınız</span></a>
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
                        <div class="col-lg-3">
                            <aside class="sidebar">
                                <section>
                                     
                                    <div class="form-group">
                                        
                                        <select name="service" id="service" data-placeholder="Select Service">
                                            <option value="0">Hizmet seçiniz...</option>
                                            @foreach($hizmetler as $hizmet)
                                            <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmet->hizmet_adi)))}}">{{$hizmet->hizmet_adi}}</option>
                                           @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                         <select name="location_service" id="location_service">
                                            <option value="0">Nerede</option>
                                            <!--<option value="1">Yakınlarımda...</option>-->
                                            @foreach($iller as $il_liste)
                                                @foreach($ilceler as $ilce_liste)
                                                    <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il_liste->il_adi)))}}">{{$il_liste->il_adi}}</option> 
                                                    @if($il_liste->id == $ilce_liste->il_id)
                                                        <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il_liste->il_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilce_liste->ilce_adi)))}}">{{$il_liste->il_adi}},{{$ilce_liste->ilce_adi}}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" style="border-radius: 60px" id="hizmetegoreara" class="btn btn-primary width-100">Ara</button>
                                    </div>
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
                        <!--fırsat kampanya bölümü aktif değil-->
                        <!--<div class="col-md-3">
                            <aside class="sidebar">
                                <section>
                                    <h2>Fırsatlar & Kampanyalar</h2>
                                    <div class="items compact">

                                        @foreach($kampanyalar as $kampanya)
                                        <div class="item">
                                            <div class="ribbon-featured">Kampanya</div>
                                            
                                            <div class="wrapper">
                                                <div class="image">
                                                    <h3>
                                                       <!-- <a href="#" class="tag category">Home & Decor</a> 
                                                        <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->ilce->ilce_adi))) }}/{{$kampanya->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->salon_adi))) }}" class="title">{{$kampanya->salon_adi}}</a>
                                                       <!-- <span class="tag">Offer</span> 
                                                    </h3>
                                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->ilce->ilce_adi))) }}/{{$kampanya->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->salon_adi))) }}" class="image-wrapper background-image">
                                                        <img src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$kampanya->id)->where('kapak_fotografi',1)->value('salon_gorseli'))}}" alt="">
                                                    </a>
                                                </div>
                                                <!--end image 
                                                <h4 class="location">
                                                    <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->il->il_adi))) }}">{{$kampanya->il->il_adi}}</a> > <a href="{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($kampanya->ilce->ilce_adi))) }}">{{$kampanya->ilce->ilce_adi}} {{$kampanya->salon_turu->salon_turu_adi}}</a>
                                                </h4>
                                                <div class="price">{{$kampanya->kampanya_fiyat}} <span class="simge-tl">&#8378;</span></div>
                                                <div class="meta">
                                                    <span style="color:red;">
                                                         {{$kampanya->kampanya_baslik}}
                                                    </span><br /><br />
                                                    {{$kampanya->kampanya_aciklama}}
                                                    
                                                </div>
                                                <!--end meta 
                                            </div>
                                            <!--end wrapper 
                                        </div>
                                        <!--end item 
                                        @endforeach
                                         
                                        
                                    </div>

                                </section>
                                
                            </aside>
                        </div>-->

                        
                    </div>
                </div>
                <!--end container-->
            </section>
         
           
@endsection
        