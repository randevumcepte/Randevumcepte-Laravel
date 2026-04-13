<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="_token" content="{{csrf_token() }}" /> 

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="{{secure_asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/fonts/font-awesome.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/style.css')}}">
    <link rel="stylesheet" href="{{secure_asset('public/css/user.css')}}">

    <link rel="stylesheet" href="{{secure_asset('public/css/owl.carousel.min.css')}}" type="text/css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
      <script src="{{secure_asset('public/js/jquery.sticky-kit.min.js')}}"></script>
    <title>{{mb_strtoupper($salon->salon_adi)}} İçin Randevu Al</title>

</head>
<body>
  
    <div class="page home-page">
        <!--*********************************************************************************************************-->
        <!--************ HERO ***************************************************************************************-->
        <!--*********************************************************************************************************-->
        <header class="hero has-dark-background">
            <div class="hero-wrapper">
                <!--============ Secondary Navigation ===============================================================-->
                <div class="secondary-navigation">
                    <div class="container">
                        
                        <!--end left-->
                        <ul class="right">
                            
                           @if(!Auth::check())
                            <li>

                                <a href="/login">
                                    <i class="fa fa-sign-in"></i>Giriş Yap
                                </a>
                            </li>
                            @endif
                            <li>
                                <a href="#>
                                    <i class="fa fa-pencil-square-o"></i>Salonunuzu Kaydedin
                                </a>
                            </li>
                        </ul>
                        <!--end right-->
                    </div>
                    <!--end container-->
                </div>
                <!--============ End Secondary Navigation ===========================================================-->
                <!--============ Main Navigation ====================================================================-->
                <div class="main-navigation">
                    <div class="container">
                        <nav class="navbar navbar-expand-lg navbar-light justify-content-between">
                            <a class="navbar-brand" href="/">
                                <img src="{{secure_asset('public/img/logo.png')}}" alt="">
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbar">
                                <!--Main navigation list-->
                                <ul class="navbar-nav">
                                     @foreach($hizmetkategorileri as $hizmetkategorisi)
                                    <li class="nav-item active has-child">
                                       
                                       
                                        <a class="nav-link" href="#">{{$hizmetkategorisi->hizmet_kategorisi_adi}}</a>
                                           <ul class="child">
                                            @foreach($hizmetler as $hizmet)
                                            @if($hizmet->hizmet_kategori_id == $hizmetkategorisi->id )
                                            <li class="nav-item">
                                                <a href="{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmet->hizmet_adi))) }}" class="nav-link">{{$hizmet->hizmet_adi}}</a>
                                            </li>
                                            @endif
                                             @endforeach
                                        </ul>
                                        
                                    </li>
                                  
                                  
                                     @endforeach
                                       @if(Auth::check()) 
                            <li class="nav-item active has-child">
                                                <a class="nav-link" href="#">{{Auth::user()->name}}</a>
                                                <ul class="child">
                                                    <li class="nav-item">
                                                        <a href="/profilim" class="nav-link">Profilim</a>
                                                    </li>
                                                      <li class="nav-item">
                                                        <a href="/randevularim" class="nav-link">Randevularım</a>
                                                    </li>
                                                      <li class="nav-item">
                                                        <a href="/firsatlarim" class="nav-link">Fırsatlarım</a>
                                                    </li>
                                                      <li class="nav-item">
                                                        <a href="/puanlarim" class="nav-link">Puanlarım</a>
                                                    </li>
                                                      <li class="nav-item">
                                                        <a href="/favorilerim" class="nav-link">Favorilerim</a>
                                                    </li>
                                                      <li class="nav-item">
                                                        <a href="/ayarlarim" class="nav-link">Ayarlarım</a>
                                                    </li>
                                                    <li class="nav-item">
                                                       <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Çıkış Yap</a>

                                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                                                    </li>
                                                </ul>
                                             </li>
                                     @endif
                                </ul>
                                <!--Main navigation list-->
                            </div>
                            <!--end navbar-collapse-->
                            <a href="#collapseMainSearchForm" class="main-search-form-toggle" data-toggle="collapse"  aria-expanded="false" aria-controls="collapseMainSearchForm">
                                <i class="fa fa-search"></i>
                                <i class="fa fa-close"></i>
                            </a>
                            <!--end main-search-form-toggle-->
                        </nav>
                        <!--end navbar-->
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/">Anasayfa</a></li>
                            <li class="breadcrumb-item"><a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}">{{$salon->salon_turu->salon_turu_adi}}</a></li>
                             <li class="breadcrumb-item"><a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}">{{$salon->il->il_adi}} {{$salon->salon_turu->salon_turu_adi}}</a></li>
                               <li class="breadcrumb-item"><a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}">{{$salon->ilce->ilce_adi}} {{$salon->salon_turu->salon_turu_adi}}</a></li>
                            <li class="breadcrumb-item active">{{$salon->salon_adi}}</li>
                        </ol> 
                        <!--end breadcrumb-->
                    </div>
                    <!--end container-->
                </div>
                <!--============ End Main Navigation ================================================================-->
                <!--============ Hero Form ==========================================================================-->
                <div class="collapse" id="collapseMainSearchForm">
                    <form class="hero-form form">
                        <div class="container">
                            <!--Main Form-->
                            <div class="main-search-form" style="margin-top: 20px">
                                <div class="form-row">
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label for="what" class="col-form-label">What?</label>
                                            <input name="keyword" type="text" class="form-control small" id="what" placeholder="What are you looking for?">
                                        </div>
                                        <!--end form-group-->
                                    </div>
                                    <!--end col-md-3-->
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label for="input-location" class="col-form-label">Where?</label>
                                            <input name="location" type="text" class="form-control small" id="input-location" placeholder="Enter Location">
                                            <span class="geo-location input-group-addon" data-toggle="tooltip" data-placement="top" title="Find My Position"><i class="fa fa-map-marker"></i></span>
                                        </div>
                                        <!--end form-group-->
                                    </div>
                                    <!--end col-md-3-->
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label for="category" class="col-form-label">Category?</label>
                                            <select name="category" id="category" class="small" data-placeholder="Select Category">
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
                                    <!--end col-md-3-->
                                    <div class="col-md-3 col-sm-3">
                                        <button type="submit" class="btn btn-primary width-100 small">Search</button>
                                    </div>
                                    <!--end col-md-3-->
                                </div>
                                <!--end form-row-->
                            </div>
                            <!--end main-search-form-->
                            <!--Alternative Form-->
                            <div class="alternative-search-form">
                                <a href="#collapseAlternativeSearchForm" class="icon" data-toggle="collapse"  aria-expanded="false" aria-controls="collapseAlternativeSearchForm"><i class="fa fa-plus"></i>More Options</a>
                                <div class="collapse" id="collapseAlternativeSearchForm">
                                    <div class="wrapper">
                                        <div class="form-row">
                                            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 d-xs-grid d-flex align-items-center justify-content-between">
                                                <label>
                                                    <input type="checkbox" name="new">
                                                    New
                                                </label>
                                                <label>
                                                    <input type="checkbox" name="used">
                                                    Used
                                                </label>
                                                <label>
                                                    <input type="checkbox" name="with_photo">
                                                    With Photo
                                                </label>
                                                <label>
                                                    <input type="checkbox" name="featured">
                                                    Featured
                                                </label>
                                            </div>
                                            <!--end col-xl-6-->
                                            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="col-md-4 col-sm-4">
                                                        <div class="form-group">
                                                            <input name="min_price" type="text" class="form-control small" id="min-price" placeholder="Minimal Price">
                                                            <span class="input-group-addon small">$</span>
                                                        </div>
                                                        <!--end form-group-->
                                                    </div>
                                                    <!--end col-md-4-->
                                                    <div class="col-md-4 col-sm-4">
                                                        <div class="form-group">
                                                            <input name="max_price" type="text" class="form-control small" id="max-price" placeholder="Maximal Price">
                                                            <span class="input-group-addon small">$</span>
                                                        </div>
                                                        <!--end form-group-->
                                                    </div>
                                                    <!--end col-md-4-->
                                                    <div class="col-md-4 col-sm-4">
                                                        <div class="form-group">
                                                            <select name="distance" id="distance" class="small" data-placeholder="Distance" >
                                                                <option value="">Distance</option>
                                                                <option value="1">1km</option>
                                                                <option value="2">5km</option>
                                                                <option value="3">10km</option>
                                                                <option value="4">50km</option>
                                                                <option value="5">100km</option>
                                                            </select>
                                                        </div>
                                                        <!--end form-group-->
                                                    </div>
                                                    <!--end col-md-3-->
                                                </div>
                                                <!--end form-row-->
                                            </div>
                                            <!--end col-xl-6-->
                                        </div>
                                        <!--end row-->
                                    </div>
                                    <!--end wrapper-->
                                </div>
                                <!--end collapse-->
                            </div>
                            <!--end alternative-search-form-->
                        </div>
                        <!--end container-->
                    </form>
                    <!--end hero-form-->
                </div>
                <!--end collapse-->
                <!--============ End Hero Form ======================================================================-->
                <!--============ Page Title =========================================================================-->
                <div class="page-title">
                    <div class="container" style="text-align: center">
                        <h1>{{$salon->salon_adi}}</h1>
                        <p>{{$salon->adres}}</p>
                    </div>
                    <!--end container-->
                </div>
                <!--============ End Page Title =====================================================================-->
                <div class="background">
                    
                     <div class="background-image">
                        
                        <img src="{{secure_asset($salon->salon_turu->salon_detay_banner)}}" alt="">
                        
                    </div>
                </div>

                <!--end background-->
            </header>

            <!--end hero-wrapper-->
         
      
        <!--end hero-->

        <!--*********************************************************************************************************-->
        <!--************ CONTENT ************************************************************************************-->
        <!--*********************************************************************************************************-->
       <section class="content">
             @yield('content')
            
        </section>
      

        <!--end content-->

        <!--*********************************************************************************************************-->
        <!--************ FOOTER *************************************************************************************-->
        <!--*********************************************************************************************************-->
       <footer class="footer">
           
            <div class="wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="/">
                                  <img src="{{secure_asset('public/img/avantajbu.png')}}" width="240" height="50" alt="randevumcepte.com.tr"  >
                            </a>
                            <br/>
                            <div style="padding-left: 10px; margin-top: 10px; width: 100%">
                            <p style="opacity: 1; font-size: 20px">Bir sorunuz mu var?</p>
                            <p style="opacity: 1; font-size: 25px"><a href="tel:08503801035"> 0850 380 10 35</a></p>
                            <p style="opacity: 1; font-size: 15px"><a href="mailto:info@randevumcepte.com.tr">info@randevumcepte.com.tr</a></p>
                          </div>
                          
                        </div>
                        <!--end col-md-5-->
                        <div class="col-md-4">
                            
                            <div class="row">
                                <div style="width: 50%;padding-left: 20px">
                                    <nav>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="/hakkimizda">Hakkımızda</a>
                                            </li>
                                            <li style="display: none">
                                                <a href="/blog">Blog</a>
                                            </li>
                                            <li>
                                                <a href="/kariyer">Kariyer</a>
                                            </li>
                                            <li>
                                                <a href="/gizlilik-politikasi">Gizlilik</a>
                                            </li>
                                            <li>
                                              <a href="/kullanici-sozlesmesi">Kullanıcı Sözleşmesi</a>
                                            </li>
                                            <li>
                                                <a href="/iletisim">İletişim</a>
                                            </li>
                                         
                                        </ul>
                                    </nav>
                                </div>
                                <div style="width: 50%;padding-left: 20px">
                                    <nav>
                                        <ul class="list-unstyled">
                                           
                                            <li>
                                                <a href="/login">Giriş Yap</a>
                                            </li>
                                            <li>
                                                <a href="/register">Üye Ol</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <!--end col-md-3-->
                        <div class="col-md-5">
                             <h2>Keşfet</h2>
                            <div class="row">
                                <div style="width: 50%;padding-left: 20px">
                                    <nav>
                                        <ul class="list-unstyled">
                                            @foreach($salonturleri as $key => $value)
                                            @if($key<=$salonturleri->count()/2)
                                            <li>
                                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salon_turu_adi))) }}">{{$value->salon_turu_adi}}</a>
                                            </li>
                                            @endif
                                             @endforeach
                                         
                                        </ul>
                                    </nav>
                                </div>
                                 <div style="width: 50%;padding-left: 20px">
                                    <nav>
                                        <ul class="list-unstyled">
                                            @foreach($salonturleri as $key => $value)
                                            @if($key>$salonturleri->count()/2)
                                            <li>
                                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value->salon_turu_adi))) }}">{{$value->salon_turu_adi}}</a>
                                            </li>
                                            @endif
                                             @endforeach
                                         
                                        </ul>
                                    </nav>
                                </div>
                               
                            </div>
                        </div>
                        
                        <!--end col-md-4-->
                    </div>
                    <!--end row-->
                </div>
                <div class="background">
                    <div class="background-image original-size">
                        <img src="{{secure_asset('public/img/footer-background-icons.jpg')}}" alt="Binlerce profesyonel hizmet arasından size en uygun ve en avantajlı randevuları oluşturun.">
                    </div>
                    <!--end background-image-->
                </div>
                <!--end background-->
            </div>
        </footer>
        <!--end footer-->
    </div>
    <!--end page-->
       
           
                  
            
             

 
 
    <script src="{{secure_asset('public/js/jquery-3.3.1.min.js')}}"></script>
    <script type="text/javascript" src="{{secure_asset('public/js/popper.min.js')}}"></script>
    <script type="text/javascript" src="{{secure_asset('public/bootstrap/js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyCSfzQkso3lLbKiuEtfoMSjw1KQb-LR14E&libraries=places"></script>
    
    <script src="{{secure_asset('public/js/selectize.min.js')}}"></script>
    <script src="{{secure_asset('public/js/masonry.pkgd.min.js')}}"></script>
    <script src="{{secure_asset('public/js/icheck.min.js')}}"></script>
    <script src="{{secure_asset('public/js/jquery.validate.min.js')}}"></script>
    <script src="{{secure_asset('public/js/custom.js')}}"></script>


 
    <script src="{{secure_asset('public/js/owl.carousel.min.js')}}"></script>
    
     

</body>
</html>