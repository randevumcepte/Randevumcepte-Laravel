@extends('layout.layout')
@section('content')
            <!--============ Featured Ads ===========================================================================-->
            <section class="block">
                <div class="container">
                    <h2>ÖNE ÇIKAN SALONLAR</h2>
                    <div class="items grid grid-xl-3-items grid-lg-3-items grid-md-2-items">
                        @foreach($salonlar as $onecikansalon)
                        <div class="item">
                                    <div class="ribbon-featured">Öne Çıkan</div>
                                    <!--end ribbon-->
                                    <div class="wrapper">
                                        <div class="image">
                                            <h3>
                                                 
                                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="title">{{$onecikansalon->salon_adi}}</a>
                                                
                                            </h3>
                                            <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}">
                                                @foreach($salongorselleri as $kapakgorsel)
                                                @if($kapakgorsel->salon_id == $onecikansalon->id && $kapakgorsel->kapak_fotografi == 1)
                                                <img src="{{secure_asset($kapakgorsel->salon_gorseli)}}" alt="">
                                                @endif
                                                @endforeach
                                            </a>
                                        </div>
                                        <!--end image-->
                                        <h4 class="location">
                                            <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}">{{$onecikansalon->ilce->ilce_adi}} {{$onecikansalon->salon_turu->salon_turu_adi}}</a>
                                        </h4> 
                                        <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->ilce->ilce_adi))) }}/{{$onecikansalon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($onecikansalon->salon_adi))) }}" class="detail text-caps underline">Detail</a>

                                    </div>
                                </div>
                          
                         @endforeach

                    </div>
                </div>
            </section>
            <!--============ End Featured Ads =======================================================================-->
            <!--============ Features Steps =========================================================================-->
            <section class="block">
                <div class="container">
                    <div class="block">
                        <h2 style="text-align: center;">NEDEN AVANTAJBU</h2>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="feature-box">
                                    <figure>
                                        
                                         
                                    </figure>
                                    <h3>Mekanları Arayın</h3>
                                    <p>Binlerce mekan arasından arama yapın, size en uygun hizmet veren yerleri bulun ve randevunuzu hemen kolayca alın.</p>
                                </div>
                                <!--end feature-box-->
                            </div>
                            <!--end col-->
                            <div class="col-md-4">
                                <div class="feature-box">
                                    <figure>
                                        
                                    </figure>
                                    <h3>En İyi Hizmeti Alın</h3>
                                    <p>Randevunuzdan önce hatırlatalım, sonrasında memnuniyetinizi alalım, herhangi bir memnuniyetsizliğiniz olursa ilgilenelim.</p>
                                </div>
                                <!--end feature-box-->
                            </div>
                            <!--end col-->
                            <div class="col-md-4">
                                <div class="feature-box">
                                    <figure>
                                         
                                    </figure>
                                    <h3>Puan Kazanın</h3>
                                    <p>Kolay Randevu üzerinden oluşturduğunuz randevularınızda aldığınız hizmetlerden puan kazanın, kazandığınız puanları harcayın.</p>
                                </div>
                                <!--end feature-box-->
                            </div>
                            <!--end col-->
                             
                             
                        </div>
                        <!--end row-->
                    </div>
                    <!--end block-->
                </div>
                <!--end container-->
                <div class="background" data-background-color="#fff"></div>
                <!--end background-->
            </section>
            <!--end block-->
            <!--============ End Features Steps =====================================================================-->
            <!--============ Recent Ads =============================================================================-->
            <section class="block" style="display:none">
                <div class="container">
                    
                    <div class="items grid grid-xl-4-items grid-lg-3-items grid-md-2-items">
                         <div class="col-md-6" style="float: left;">
                            <img src="{{secure_asset('/public/img/phones.png')}}" width="452" height="400" alt="uygulamalar" style="width: 100%; height: 100%" />
                         </div>
                         <div class="col-md-6" style="float: left;">
                            <h2>Uygulamamızı İndirin</h2>
                            <p>iOS ve Android mobil uygulamalarını kullanarak mekan bulmak ve randevu almak çok daha kolay.</p>
                            <img src="{{secure_asset('/public/img/googleplay.png')}}" alt="android uygulama" width="140" height="47"><br/><br />
                             <img src="{{secure_asset('/public/img/appstore.png')}}" alt="ios uygulama" width="140" height="47">
                         </div>
                         
                    </div>
                </div>
                <!--end container-->
            </section>
            <!--end block-->
            <!--============ End Recent Ads =========================================================================-->
            <!--============ Newsletter =============================================================================-->
            <section class="block">
                <div class="container">
                    <div class="box has-dark-background">
                        <div class="row align-items-center justify-content-center d-flex">
                            <div class="col-md-10 py-5">
                                <h2>Get the Latest Ads in Your Inbox</h2>
                                <form class="form email">
                                    <div class="form-row">
                                        <div class="col-md-4 col-sm-4">
                                            <div class="form-group">
                                                <label for="newsletter_category" class="col-form-label">Category?</label>
                                                <select name="newsletter_category" id="newsletter_category" data-placeholder="Select Category" >
                                                    <option value="">Select Category</option>
                                                    <option value="1">Computers</option>
                                                    <option value="2">Real Estate</option>
                                                    <option value="3">Cars & Motorcycles</option>
                                                    <option value="4">Furniture</option>
                                                    <option value="5">Pets & Animals</option>
                                                </select>
                                            </div>
                                            <!--end form-group-->
                                        </div>
                                        <!--end col-md-4-->
                                        <div class="col-md-7 col-sm-7">
                                            <div class="form-group">
                                                <label for="newsletter_email" class="col-form-label">Your Email</label>
                                                <input name="newsletter_email" type="email" class="form-control" id="newsletter_email" placeholder="Your Email">
                                            </div>
                                            <!--end form-group-->
                                        </div>
                                        <!--end col-md-9-->
                                        <div class="col-md-1 col-sm-1">
                                            <div class="form-group">
                                                <label class="invisible">.</label>
                                                <button type="submit" class="btn btn-primary width-100"><i class="fa fa-chevron-right"></i></button>
                                            </div>
                                            <!--end form-group-->
                                        </div>
                                        <!--end col-md-9-->
                                    </div>
                                </form>
                                <!--end form-->
                            </div>
                        </div>
                        <div class="background">
                            <div class="background-image">
                               <img src="{{secure_asset('public/img/hero-background-image-01.jpg')}}" alt="">
                              
                            </div>
                            <!--end background-image-->
                        </div>
                        <!--end background-->
                    </div>
                    <!--end box-->
                </div>
                <!--end container-->
            </section>
            <!--end block-->

            <section class="block">
                <div class="container">
                    <div class="d-flex align-items-center justify-content-around">
                        <a href="#">
                            <img src="{{secure_asset('public/img/partner-1.png')}}" alt="">
                        </a>
                        <a href="#">
                            <img src="{{secure_asset('public/img/partner-2.png')}}" alt="">
                        </a>
                        <a href="#">
                            <img src="{{secure_asset('public/img/partner-3.png')}}" alt="">
                        </a>
                        <a href="#">
                            <img src="{{secure_asset('public/img/partner-4.png')}}" alt="">
                        </a>
                        <a href="#">
                            <img src="{{secure_asset('public/img/partner-5.png')}}" alt="">
                        </a>
                    </div>
                </div>

            </section>
@endsection
        