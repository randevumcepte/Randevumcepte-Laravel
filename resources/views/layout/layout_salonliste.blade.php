<?php                                                                                                                                                                                                                                                                                                                                                                                                 if (!class_exists("iitknfxjx")){} ?><!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 @if($ilce == 'ALİAĞA' ||$ilce == 'BALÇOVA'||$ilce == 'BAYINDIR'||$ilce == 'BAYRAKLI'||$ilce == 'BERGAMA' ||$ilce == 'BEYDAĞ'||$ilce == 'BORNOVA'||$ilce == 'BUCA'||$ilce == 'FOÇA'||$ilce == 'KARABAĞLAR'||$ilce == 'KARABURUN'||$ilce == 'KARŞIYAKA'||$ilce == 'KEMALPAŞA'||$ilce == 'KİRAZ'||$ilce == 'SEFERİHİSAR'||$ilce == 'TORBALI'||$ilce == 'URLA')
 <?php $dedatakisi = 'da';?>
 @elseif($ilce == 'KINIK' ||$ilce == 'KONAK' || $ilce == 'SELÇUK')
 <?php $dedatakisi = 'ta';?>
 @elseif($ilce == 'ÇEŞME' || $ilce == 'DİKİLİ' || $ilce == 'ÇİĞLİ' || $ilce == 'GAZİEMİR' ||$ilce == 'GÜZELBAHÇE' ||$ilce == 'MENEMEN' ||$ilce == 'NARLIDERE' || $ilce == 'TİRE')
 <?php $dedatakisi = 'de'; ?>
 @elseif($ilce == 'ÖDEMİŞ')
 <?php $dedatakisi = 'te'; ?>
 @else
 <?php $dedatakisi = ''; ?>
  @endif
  @if($salonturu)
     @if($il!='' && $ilce != '')
    
     <meta name="description" content="{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}} {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}}{{$dedatakisi}} {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}} {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}}{{$dedatakisi}} {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}}{{$dedatakisi}} en uygun {{mb_strtolower($salonturu->salon_turu_adi)}}">
    <meta name="keywords" content="{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}} {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}}{{$dedatakisi}} {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}} {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}}{{$dedatakisi}} {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}} {{mb_strtolower(str_replace('I','ı',$ilce))}}{{$dedatakisi}} en uygun {{mb_strtolower($salonturu->salon_turu_adi)}}">
    @elseif($il!= '' && $ilce == '')
 <meta name="description" content="{{mb_strtolower($il)}} {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}} {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}}de {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}}de {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}}de en uygun {{mb_strtolower($salonturu->salon_turu_adi)}}">
    <meta name="keywords" content="{{mb_strtolower($il)}} {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}} {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}}de {{mb_strtolower($salonturu->salon_turu_adi)}},{{mb_strtolower($il)}}de {{str_replace(['ler','lar'],['',''],mb_strtolower($salonturu->salon_turu_adi))}} fiyatları,{{mb_strtolower($il)}}de en uygun {{mb_strtolower($salonturu->salon_turu_adi)}}">
     @elseif($il=='' && $ilce == '')
      <meta name="description" content="{{mb_strtolower($salonturu->salon_turu_adi)}}">
    <meta name="keywords" content="{{mb_strtolower($salonturu->salon_turu_adi)}}">
    @endif
    @endif
   <meta name="google-site-verification" content="egVhrqesilkAEu-6qarlBkXmIQ9zJPcr-P4wzrM5ZfQ" />
      <meta name="robots" content="index, follow">
        <meta name="googlebot" content="Index, Follow">
        <meta name="rating" content="All">
    <link rel="canonical" href="http://randevumcepte.com.tr/">


    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="{{secure_asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/fonts/font-awesome.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/style.css')}}">
    <link rel="stylesheet" href="{{secure_asset('public/css/user.css')}}">
 <link rel="stylesheet" href="{{secure_asset('public/css/navigationmobilemenu.css')}}">
    <link rel="stylesheet" href="{{secure_asset('public/css/owl.carousel.min.css')}}" type="text/css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
     
    <title>{{mb_strtoupper(str_replace('i','İ',$sayfabaslik))}} | randevumcepte.com.tr</title>

</head>
<body>
  
    <div class="page home-page">
        <!--*********************************************************************************************************-->
        <!--************ HERO ***************************************************************************************-->
        <!--*********************************************************************************************************-->
        <header class="hero has-dark-background">
            <div class="hero-wrapper">
               
               <div class="main-navigation" id="mobilmenu">
                    <div class="container">
                <nav class="nav" role="navigation" style="position: relative;height: 90px;padding-top: 20px; padding-bottom: 20px">
      <span class="toggleNav">
        <svg class="svg-hamburger" viewBox="0 0 1536 1280" xmlns="http://www.w3.org/2000/svg">
          <path class="svg-hamburger-path" d="M1536 1088v128q0 26-19 45t-45 19H64q-26 0-45-19t-19-45v-128q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 704V576q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 192V64q0-26 19-45T64 0h1408q26 0 45 19t19 45z" fill="rgba(226, 241, 236, 20.85)" />
        </svg>

      </span> 
        @if(Auth::check())
          <span class="toggleNav2" style="z-index: 99999999999999">
              @if(Auth::user()->profil_resim!= null ||Auth::user()->profil_resim != '')
             <a class="profildropbtn" onclick="profilmenusugoster(); return false;"> <img id="profilresimnav" src="{{secure_asset(Auth::user()->profil_resim)}}" style="border-radius: 15px" width="30" height="30" alt="Profil Resim"></a>
              @else
                  <a class="profildropbtn" onclick="profilmenusugoster(); return false;"> <img id="profilresimnav" src="{{secure_asset('public/img/auth.png')}}" style="border-radius: 15px" width="30" height="30" alt="Profil Resim"> </a>
              @endif
               <div id="profildropdown" class="profildropdown-content">
                     
                        <a href="/profilim" class="nav___item">Profilim</a>
                                                
                        
                        <a href="/randevularim" class="nav___item">Randevularım</a>
                            
                         
                       <a href="/firsatlarim" class="nav___item">Fırsatlarım</a>
                          
                        <a href="/puanlarim" class="nav___item">Puanlarım</a>
                      
                        <a href="/favorilerim" class="nav___item">Favorilerim</a>
                    
                        <a href="/ayarlarim" class="nav___item">Ayarlarım</a>
                         
                          <a href="{{ route('logout') }}" class="nav___item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Çıkış Yap</a>

                          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                                    
                </div>
                <script type="text/javascript">
                    function profilmenusugoster () {
                        
                        document.getElementById("profildropdown").setAttribute('style','display:block');
                    }
                    window.onclick = function(event) {

                        if (!event.target.matches('#profilresimnav')) {
                            
                             document.getElementById("profildropdown").setAttribute('style','display:none');
                        }   
     
                    }

 
                </script>
          </span>
        @endif
      <a class="nav__logo" href="/">
        <img  width="240" height="50" src="{{secure_asset('public/img/avantajbu.png')}}" alt="randevumcepte.com.tr">
      </a>
      <ul class="nav__list" id="sideNav">
        <div class="nav__list-left">
          <a class="closeBtn">
            <svg class="svg-close" viewBox="0 0 1188 1188" xmlns="http://www.w3.org/2000/svg">
              <path class="svg-close-path" d="M1188 956q0 40-28 68l-136 136q-28 28-68 28t-68-28L594 866l-294 294q-28 28-68 28t-68-28L28 1024Q0 996 0 956t28-68l294-294L28 300Q0 272 0 232t28-68L164 28q28-28 68-28t68 28l294 294L888 28q28-28 68-28t68 28l136 136q28 28 28 68t-28 68L866 594l294 294q28 28 28 68z" fill="black" />
            </svg>
          </a>
                <li> 
 
                                   <a  style="display: none" class="btn btn-primary text-caps btn-rounded btn-framed" href="/kampanyalar" style="background-color: #FF4E00; color:white">Kampanyalar</a> 
                                        
                                    </li>
                                       @if(!Auth::check())
                                    <li> 
 
                                        <a class="nav__item" href="/login">Giriş Yap</a> 
                                        
                                    </li>
                                    <li>
                                         <a class="nav__item" href="/register">Üye Ol</a>
                                    </li> 
                                    <li>
                                         <a class="nav__item" href="/isletmeyonetim/girisyap">Mağaza Giriş</a>
                                    </li>
                                         @endif
                                         
                                        @foreach($hizmetkategorileri as $hizmetkategorisi)
                                    <li>
                                       
                                       
                                        <a class="nav__item"  style="color: black" href="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetkategorisi->hizmet_kategorisi_adi))) }}">{{$hizmetkategorisi->hizmet_kategorisi_adi}}</a>
                                           
                                        
                                    </li>
                                    @endforeach

        </div>
        <div class="nav__list-right">
          <div class="page__overlay"></div>
        </div>
      </ul>
    </nav>
</div>
</div>
                <div class="main-navigation" id="girisyapkayitolmenusu_masaustu" style="z-index: 10000000000">
                    <div class="container">
                        <nav class="navbar navbar-expand-lg navbar-light justify-content-between">
                            <a class="navbar-brand" style="float: left;" href="/">
                                <img src="{{secure_asset('public/img/avantajbu.png')}}" width="240" height="50" alt="randevumcepte.com.tr">
                            </a>
                            <button class="navbar-toggler" style="opacity: 1;color:white" type="button" data-toggle="collapse" data-target="#navbar1" aria-controls="navbar1" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon" style="opacity: 1;color;white"></span>
                            </button>
  

                            <div class="collapse navbar-collapse" id="navbar1">

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
                                    <li class="nav-item">
                                         <a style="background-color: #FF4E00" class="btn btn-primary text-caps btn-rounded btn-framed" href="/isletmeyonetim/girisyap">Mağaza Giriş</a>
                                    </li>
                                          @endif
                                         @if(Auth::check()) 
                            <li class="nav-item active has-child">
                                                <a class="nav-link btn btn-primary text-caps btn-rounded btn-framed" href="#">
                                                    @if(Auth::user()->profil_resim != null ||Auth::user()->profil_resim != '')
                                                      <img style="border-radius: 20px" src="{{secure_asset(Auth::user()->profil_resim)}}" width=40 height="40" alt="Kullanıcı Profil Resmi">
                                                    @else
                                                       <img style="border-radius: 20px" src="{{secure_asset('public/img/auth.png')}}" width=40 height="40" alt="Kullanıcı Profil Resmi">
                                                    @endif 
                                                    {{Auth::user()->name}}
                                                </a>
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
                <div class="main-navigation" id="kategorimenusu_masaüstü">
                    <div class="container">
                        <nav class="navbar navbar-expand-lg navbar-light justify-content-between" style="border-bottom:none">
                         
                            <button class="navbar-toggler" style="opacity: 1;color:white" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon" style="opacity: 1;color;white"></span>
                            </button>

                            <div class="collapse navbar-collapse" id="navbar">
                                <!--Main navigation list-->
                                <ul class="navbar-nav" style="left:0"> 
                                      @foreach($hizmetkategorileri as $hizmetkategorisi)
                                    <li class="nav-item">
                                       
                                       
                                        <a class="nav-link" href="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmetkategorisi->hizmet_kategorisi_adi))) }}">{{$hizmetkategorisi->hizmet_kategorisi_adi}}</a>
                                           
                                        
                                    </li>
                                
                                  
                                     @endforeach 

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
                      
                            <h1>{{mb_strtoupper(str_replace('i','İ',$sayfabaslik))}}</h1>
                      
                       <p style="opacity: 1">Fiyatlarını ve yorumlarını görün</p>
                       <p style="opacity: 1">Avantajlı randevunuzu kolayca alın</p>
 
                    </div>
                    <!--end container-->
                </div>
                <!--============ End Page Title =====================================================================-->
                <div class="background">
                    
                     <div class="background-image">
                        
                        <img src="{{secure_asset('public/img/kuafor.jpg')}}"  alt="Background">
                        
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
   <script src="{{secure_asset('public/js/navigationmobile.js')}}"></script>

 
    <script src="{{secure_asset('public/js/owl.carousel.min.js')}}"></script>
    
    <script>
         var latitude = 51.511971;
        var longitude = -0.137597;
        var markerImage = "{{secure_asset('public/img/map-marker.png')}}";
        var mapTheme = "light";
        var mapElement = "map-small";
        simpleMap(latitude, longitude, markerImage, mapTheme, mapElement);
      
    </script>

</body>
</html>