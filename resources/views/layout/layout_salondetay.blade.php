<!doctype html>
<html lang="{{ config('app.locale') }}">
   <head>
       <meta charset="UTF-8"> 
       <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
       <meta name="robots" content="index, follow">
       <meta name="googlebot" content="Index, Follow">
       <title>{{ucwords($aramaterimisayfa)}} | {{$salon->salon_adi}} </title>
       <meta name="description" content="{{$salon->meta_description}}">
       
       <link rel="canonical" href="https://{{$_SERVER['HTTP_HOST']}}">
       <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">
       <meta property="og:locale" content="tr_TR">
       <meta property="og:site_name" content="{{$salon->salon_adi}}">
       <meta property="og:url" content="https://{{$_SERVER['HTTP_HOST']}}{{request()->getPathInfo()}}">
       <meta property="og:type" content="website">
       <meta property="og:title" content="{{ucwords($aramaterimisayfa)}} | {{$salon->salon_adi}}">
       <meta property="og:description" content="{{$salon->meta_description}}">
       @php $kapakGorsel = \App\SalonGorselleri::where('salon_id',$salon->id)->where('kapak_fotografi',1)->value('salon_gorseli'); @endphp
       @if($kapakGorsel)
       <meta property="og:image" content="https://{{$_SERVER['HTTP_HOST']}}{{$kapakGorsel}}">
       @else
       <meta property="og:image" content="https://{{$_SERVER['HTTP_HOST']}}/public/img/randevumcepte.jpg">
       @endif
       <meta property="og:image:width" content="1200">
       <meta property="og:image:height" content="630">
       <meta name="twitter:card" content="summary_large_image">
       <meta name="twitter:title" content="{{ucwords($aramaterimisayfa)}} | {{$salon->salon_adi}}">
       <meta name="twitter:description" content="{{$salon->meta_description}}">
      
      <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
      <link rel="stylesheet" href="{{secure_asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
      <link rel="stylesheet" href="{{secure_asset('public/fonts/font-awesome.css')}}" type="text/css">
      <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
      <link rel="stylesheet" href="/public/css/style.css?v=1.9">
      <link rel="stylesheet" href="{{secure_asset('public/css/user.css')}}">
      <link rel="stylesheet" href="{{secure_asset('public/css/randevu-luxe.css')}}?v=14">
      <link rel="stylesheet" href="{{secure_asset('public/css/salon-landing.css')}}?v=6">
      <link rel="stylesheet" href="{{secure_asset('public/css/navigationmobilemenu.css')}}">
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/perfect-scrollbar/css/perfect-scrollbar.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/material-design-icons/css/material-design-iconic-font.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.magnific-popup/magnific-popup.css')}}"/>
      <link rel="stylesheet" href="{{secure_asset('public/css/owl.carousel.min.css')}}" type="text/css">
      <script src="{{secure_asset('public/js/jquery.sticky-kit.min.js')}}"></script>
      <link rel="stylesheet" href="{{secure_asset('public/css/style_gallery.css')}}" type="text/css"/>
      <script src="{{secure_asset('public/js/OneSignalSDKWorker.js')}}"></script>
      <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" defer></script>
      <script>
        window.OneSignal = window.OneSignal || [];
        OneSignal.push(function() {
          OneSignal.init({
            appId: "80d23c5f-24e2-4030-9df2-6fdbbede127a",
          });
        });
         OneSignal.push(function () {
            OneSignal.getUserId(function(userId) {
               if($('#onesignalid').length)
                  $('#onesignalid').val(userId);
               console.log("OneSignal User ID:", userId);
                // (Output) OneSignal User ID: 270a35cd-4dda-4b3f-b04e-41d7463a2316    
            });
            OneSignal.SERVICE_WORKER_PARAM = { scope: '/public/js/' };
            OneSignal.SERVICE_WORKER_PATH = 'public/js/OneSignalSDKWorker.js'
            OneSignal.SERVICE_WORKER_UPDATER_PATH = 'public/js/OneSignalSDKWorker.js'
            OneSignal.init(initConfig);
         });
      </script>
      <style type="text/css">
         .sosyalmedyabolumu {
             position: fixed;
             top: 200px;
             right: 0;
             width: 48px;
             height: auto;
             z-index: 10;
         }
      </style>
   <script type="application/ld+json">
   {
     "@context": "https://schema.org",
     "@type": "LocalBusiness",
     "name": "{{ $salon->salon_adi }}",
     "description": "{{ $salon->meta_description }}",
     "url": "https://{{ $_SERVER['HTTP_HOST'] }}",
     "telephone": "{{ $salon->telefon_1 ?? '' }}",
     "address": {
       "@type": "PostalAddress",
       "streetAddress": "{{ $salon->adres ?? '' }}",
       "addressCountry": "TR"
     }@if($kapakGorsel ?? false),
     "image": "https://{{ $_SERVER['HTTP_HOST'] }}{{ $kapakGorsel }}"@endif
     @if($salon->facebook_sayfa ?? false),"sameAs": ["{{ $salon->facebook_sayfa }}"@if($salon->instagram_sayfa ?? false),"{{ $salon->instagram_sayfa }}"@endif]@endif
   }
   </script>
   </head>
   <body>
      <div id="preloader">
         <div id="loaderstatus">&nbsp;</div>
      </div>
      
      <div class="page home-page">
      <header class="hero has-dark-background">
         <div class="hero-wrapper">
            <div class="main-navigation" id="mobilmenu">
               <div class="container">
                  <nav class="nav" role="navigation" style="position: relative;height: 90px;padding-top: 20px; padding-bottom: 20px">
                     @if(!Auth::check())
                     <span class="toggleNav">
                        <svg class="svg-hamburger" viewBox="0 0 1536 1280" xmlns="http://www.w3.org/2000/svg">
                           <path class="svg-hamburger-path" d="M1536 1088v128q0 26-19 45t-45 19H64q-26 0-45-19t-19-45v-128q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 704V576q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 192V64q0-26 19-45T64 0h1408q26 0 45 19t19 45z" fill="rgba(226, 241, 236, 20.85)" />
                        </svg>
                     </span>
                     @endif
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
                     <a class="nav__logo" href="/" style="width: 100px;">
                     <img style="width: 80%;height:auto" src="{{secure_asset($salon->logo)}}" alt="{{$salon->salon_adi}}">
                     </a>
                     <ul class="nav__list" id="sideNav">
                        <div class="nav__list-left">
                           <a class="closeBtn">
                              <svg class="svg-close" viewBox="0 0 1188 1188" xmlns="http://www.w3.org/2000/svg">
                                 <path class="svg-close-path" d="M1188 956q0 40-28 68l-136 136q-28 28-68 28t-68-28L594 866l-294 294q-28 28-68 28t-68-28L28 1024Q0 996 0 956t28-68l294-294L28 300Q0 272 0 232t28-68L164 28q28-28 68-28t68 28l294 294L888 28q28-28 68-28t68 28l136 136q28 28 28 68t-28 68L866 594l294 294q28 28 28 68z" fill="black" />
                              </svg>
                           </a>
                           
                           @if(!Auth::check())
                           <li> 
                              <a class="nav__item" href="/login">Giriş Yap</a> 
                           </li>
                           <li>
                              <a class="nav__item" href="/register">Üye Ol</a>
                           </li>
                           <li>
                              <a class="nav__item" href="/isletmeyonetim/girisyap">İşletme Giriş</a>
                           </li>
                           @endif
                       
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
                     <a class="navbar-brand" style="float: left; width: 150px;" href="/" style="width: ;">
                     <img src="{{secure_asset($salon->logo)}}" style="width: 150px;height:auto" alt="{{secure_asset($salon->salon_adi)}}">
                     </a>
                     <button class="navbar-toggler" style="opacity: 1;color:white" type="button" data-toggle="collapse" data-target="#navbar1" aria-controls="navbar1" aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon" style="opacity: 1;color;white"></span>
                     </button>
                     <div class="collapse navbar-collapse" id="navbar1">
                        <!--Main navigation list-->
                        <ul class="navbar-nav">
                          
                           <li class="nav-item"> 
                              <a  style="display: none" class="btn btn-primary text-caps btn-rounded btn-framed" href="/kampanyalar" style="background-color: #5C008E; color:white">Kampanyalar</a> 
                           </li>
                           @if(!Auth::check())
                           <li class="nav-item"> 
                              <a class="btn btn-primary text-caps btn-rounded btn-framed" href="/login">Giriş Yap</a> 
                           </li>
                           <li class="nav-item">
                              <a class="btn btn-primary text-caps btn-rounded btn-framed" href="/register">Üye Ol</a>
                           </li>
                           <li class="nav-item">
                              <a style="background-color: #5C008E" class="btn btn-primary text-caps btn-rounded btn-framed" href="/isletmeyonetim/girisyap">İşletme Giriş</a>
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
                                    <a href="/profilim" class="nav-link" style="color:#000">Profilim</a>
                                 </li>
                                 <li class="nav-item">
                                    <a href="/randevularim" class="nav-link" style="color:#000">Randevularım</a>
                                 </li>
                               
                              
                                 <li class="nav-item">
                                    <a href="/ayarlarim" class="nav-link" style="color:#000">Ayarlarım</a>
                                 </li>
                                 <li class="nav-item">
                                    <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"  style="color:#000">Çıkış Yap</a>
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
           
            <div class="page-title">
               <div class="container" style="text-align: center">
                  <h1 style="font-size:22px;color:white;margin-bottom:4px">{{ucwords($aramaterimisayfa)}}</h1>
                  <p style="font-size:26px; opacity: 1">{{$salon->salon_adi}} Randevu Sistemi</p>
                   
                  
                  <!--end container--> 
                     <!--end container-->
               </div>
            </div>
                  <div class="background">
                     
                     <div class="background-image">
                        @if(\App\SalonGorselleri::where('salon_id',$salon->id)->where('kapak_fotografi',1)->count()==1)
                        <img style="opacity:0.5" src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$salon->id)->where('kapak_fotografi',1)->value('salon_gorseli'))}}" alt="Background">
                        @else
                        <img style="opacity:0.5" src="/public/img/randevumcepte.jpg">
                        @endif
                        
                        
                    </div>
                   
                  </div>
            <!--end background-->
      </header>
      <!--end hero-wrapper-->
      <section class="content">
      @yield('content')
      </section>
      <footer class="footer">
            <div class="wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <p style="text-align: center;">
                                  Her Hakkı Saklıdır. 2018-{{date('Y')}} © Tasarlayan  
                  <a href="#" target="_blank"
                     ><img src='/public/yeni_panel/vendors/images/randevumcepte.png' style="height: 30px;"></a
                     >
                            </p>
                        </div>
                        <!--end col-md-5-->
                        <div class="col-md-6">
                             
                        </div>
                      
                    </div>
                    <!--end row-->
                </div>
                <div class="background">
                    <div class="background-image original-size">
                        <img src="{{secure_asset('public/img/randevumcepte.jpg')}}" alt="">
                    </div>
                    <!--end background-image-->
                </div>
                <!--end background-->
            </div>
        </footer>
      <!--end footer-->
      </div>
      <!--end page-->
      <script language="JavaScript" type="text/javascript" src="{{secure_asset('public/js/jquery-3.3.1.min.js')}}"></script>
      <script type="text/javascript" src="{{secure_asset('public/js/popper.min.js')}}"></script>
      <script type="text/javascript" src="{{secure_asset('public/bootstrap/js/bootstrap.min.js')}}"></script>
      <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyCSfzQkso3lLbKiuEtfoMSjw1KQb-LR14E&libraries=places"></script>
      <script src="{{secure_asset('public/js/selectize.min.js')}}"></script>
      <script src="{{secure_asset('public/js/masonry.pkgd.min.js')}}"></script>
       <script src="{{secure_asset('public/js/icheck.min.js')}}"></script>
      <script src="{{secure_asset('public/js/jquery.validate.min.js')}}"></script>
      <script src="{{secure_asset('public/js/custom.js?v=1.0.826')}}"></script>
      <script src="{{secure_asset('public/js/navigationmobile.js')}}"></script>
      <script src="{{secure_asset('public/js/owl.carousel.min.js')}}"></script>
      <script>
         var latitude = 51.511971;
         var longitude = -0.137597;
         var markerImage = "{{secure_asset('public/img/map-marker.png')}}";
         var mapTheme = "light";
         var mapElement = "map-small";
         simpleMap(latitude, longitude, markerImage, mapTheme, mapElement);
         var modal = document.getElementById('myModal');
         
         
         
         
         
      </script>
   </body>
</html>