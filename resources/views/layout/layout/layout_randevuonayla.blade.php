<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="_token" content="{{csrf_token() }}" /> 

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('public/fonts/font-awesome.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('public/css/selectize.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('public/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/user.css')}}">

    <link rel="stylesheet" href="{{asset('public/css/owl.carousel.min.css')}}" type="text/css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
      <script src="{{asset('public/js/jquery.sticky-kit.min.js')}}"></script>
    <title>{{mb_strtoupper($salon->salon_adi)}} İçin Randevu Al</title>

</head>
<body>
  
    <div class="page home-page">
        <!--*********************************************************************************************************-->
        <!--************ HERO ***************************************************************************************-->
        <!--*********************************************************************************************************-->
         <header class="hero has-dark-background">
            <div class="hero-wrapper">
                
                <div class="main-navigation">
                    <div class="container">
                        <nav class="navbar navbar-expand-lg navbar-light justify-content-between">
                            <a class="navbar-brand" href="/">
                                <img src="{{asset('public/img/avantajbu.png')}}" width="240" height="50" alt="Avantajbu.com">
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse">
                                <!--Main navigation list-->
                                <ul class="navbar-nav"> 
                                     
                                 
                                        <li class="nav-item"> 
 
                                        <a  style="display: none" class="btn btn-primary text-caps btn-rounded btn-framed" href="/kampanyalar" style="background-color: #FF4E00; color:white">Kampanyalar</a> 
                                        
                                    </li>
                                       @if(!Auth::check())
                                    <li class="nav-item"> 
 
                                        <a class="btn btn-primary text-caps btn-rounded btn-framed" href="/login">Giriş Yap</a> 
                                        
                                    </li>
                                    <li class="nav-item">
                                         <a class="btn btn-primary text-caps btn-rounded btn-framed" href="/register">Üye Ol</a>
                                    </li>
                                  
                                  @endif
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

                              

                        </nav>


                        <!--end navbar-->
                    </div>
                    <!--end container-->
                </div>
                <div class="main-navigation">
                    <div class="container">
                        <nav class="navbar navbar-expand-lg navbar-light justify-content-between" style="border-bottom:none">
                         
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse" id="navbar">
                                <!--Main navigation list-->
                                <ul class="navbar-nav" style="left:0"> 

                                      @foreach($hizmetkategorileri as $hizmetkategorisi)
                                    <li class="nav-item active has-child">
                                       
                                       
                                        <a class="nav-link" href="#">{{$hizmetkategorisi->hizmet_kategorisi_adi}}</a>
                                           <ul class="child">
                                            @foreach($hizmetler as $hizmet)
                                            @if($hizmet->hizmet_kategori_id == $hizmetkategorisi->id )
                                            <li class="nav-item">
                                                <a href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmet->hizmet_adi))) }}" class="nav-link">{{$hizmet->hizmet_adi}}</a>
                                            </li>
                                            @endif
                                             @endforeach
                                        </ul>
                                        
                                    </li>
                                
                                  
                                     @endforeach 
                                  
                                      <li class="nav-item has-child" style="position:absolute;right:0;bottom: 0">
                                        <a class="nav-link" href="#">Etiketler</a>
                                        <ul class="child">
                                            @foreach($aramaterimlerihepsi as $key => $value)
                                          
                                            <li class="nav-item">
                                                <a href="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi)))}}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($value)))}}/{{$aramaterimleriid[$key]}}" class="nav-link">{{mb_strtoupper($value)}}</a>
                                            </li>
                                            @endforeach
                                        </ul>

                                </ul>

                              
                                <!--Main navigation list-->
                            </div>

                              

                        </nav>


                        <!--end navbar-->
                    </div>
                    <!--end container-->
                </div>
                
                <div class="page-title">
                    <div class="container" style="text-align: center">
                      <!--  <h1 style="font-size:20px">{{$aramaterimisayfa}}</h1>-->
                        <h1 style="font-size:30px">{{$aramaterimisayfa}}</h1>
                        <p style="font-size:40px; opacity: 1">{{$salon->salon_adi}}</p>
                        <p style="opacity: 1">{{$salon->adres}}</p>
                         <div class="row">
                                 <div class="col-md-12" style="text-align: center;">
                                    <div class="col-sm-6" style="float:left;text-align: right;">
                                        @if($salonpuanlar->count()>0)
                                            <div class="rating" data-rating="{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}">
                                            </div>
                                         @else
                                            <div class="rating" data-rating="0"></div>
                                         @endif
                                     </div>
                                     <div class="col-sm-6" style="float:left;text-align: left; color: white"> 
                                          {{$salonyorumlar->count()}} Yorum, 
                                            
                                            {{$salonpuanlar->count()}}
                                             Puanlama
                                         
                                            @if($salonpuanlar->count()>0)
                                                [{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}/5]
                                            @else
                                              [0/5]
                                            @endif
                                        </div>
                                    </div>

                    </div><!--end container->
                    <!--end container-->
                </div>
                <!--============ End Page Title =====================================================================-->
                <div class="background">
                    
                     <div class="background-image">
                        
                        <img src="{{asset($salon->salon_turu->salon_detay_banner)}}" alt="Salon Detay Banner">
                        
                    </div>
                </div>

                <!--end background-->
            </header>
         
      
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
                                  <img src="{{asset('public/img/avantajbu.png')}}" width="240" height="50" alt="Avantajbu.com"  >
                            </a>
                            <br/>
                            <div style="padding-left: 10px; margin-top: 10px; width: 100%">
                            <p style="opacity: 1; font-size: 20px">Bir sorunuz mu var?</p>
                            <p style="opacity: 1; font-size: 25px"><a href="tel:08503801035"> 0850 380 10 35</a></p>
                            <p style="opacity: 1; font-size: 15px"><a href="mailto:info@avantajbu.com">info@avantajbu.com</a></p>
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
                        <img src="{{asset('public/img/footer-background-icons.jpg')}}" alt="Binlerce profesyonel hizmet arasından size en uygun ve en avantajlı randevuları oluşturun.">
                    </div>
                    <!--end background-image-->
                </div>
                <!--end background-->
            </div>
        </footer>
        <!--end footer-->
    </div>
    <!--end page-->
       
           
                  
            
             

 
 
    <script src="{{asset('public/js/jquery-3.3.1.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('public/js/popper.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('public/bootstrap/js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyCSfzQkso3lLbKiuEtfoMSjw1KQb-LR14E&libraries=places"></script>
    
    <script src="{{asset('public/js/selectize.min.js')}}"></script>
    <script src="{{asset('public/js/masonry.pkgd.min.js')}}"></script>
    <script src="{{asset('public/js/icheck.min.js')}}"></script>
    <script src="{{asset('public/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('public/js/custom.js')}}"></script>


 
    <script src="{{asset('public/js/owl.carousel.min.js')}}"></script>
    
     

</body>
</html>