<!doctype html>
<html lang="{{ config('app.locale') }}">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
      <link rel="stylesheet" href="{{secure_asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
      <link rel="stylesheet" href="{{secure_asset('public/fonts/font-awesome.css')}}" type="text/css">
      <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
       <link rel="stylesheet" href="/public/css/style.css?v=1.5">
      <link rel="stylesheet" href="{{secure_asset('public/css/user.css')}}">
      <link rel="stylesheet" href="{{secure_asset('public/css/navigationmobilemenu.css')}}">
      <link rel="stylesheet" href="{{secure_asset('public/css/owl.carousel.min.css')}}" type="text/css">
      <link rel="stylesheet" href="{{secure_asset('public/css/modern-profile.css')}}">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
         />
      <title>Profilim | {{$salon->salon_adi}}</title>
   </head>
   <body>
      <div id="preloader">
         <div id="loaderstatus">&nbsp;</div>
      </div>
      <div class="page home-page">
      <!--*********************************************************************************************************-->
      <!--************ HERO ***************************************************************************************-->
      <!--*********************************************************************************************************-->
      <header class="customer-app-header">
         <div class="customer-app-header-inner container">
            <a href="/" class="customer-app-logo">
               <img src="{{secure_asset($salon->logo)}}" alt="{{$salon->salon_adi}}">
            </a>

            @if(Auth::check())
            <div class="customer-user-pill" id="customerUserPill">
               <button type="button" class="customer-user-btn" onclick="customerUserToggle(event)">
                  <span class="customer-user-avatar">
                     @if(Auth::user()->profil_resim != null && Auth::user()->profil_resim != '')
                        <img src="{{secure_asset(Auth::user()->profil_resim)}}" alt="">
                     @else
                        <img src="{{secure_asset('public/img/auth.png')}}" alt="">
                     @endif
                  </span>
                  <span class="customer-user-name">{{Auth::user()->name}}</span>
                  <i class="fa fa-chevron-down customer-user-caret"></i>
               </button>
               <div class="customer-user-menu" id="customerUserMenu">
                  <a href="/profilim" class="customer-user-item">
                     <i class="fa fa-user"></i> Profilim
                  </a>
                  <a href="/" class="customer-user-item primary">
                     <i class="fa fa-calendar-plus-o"></i> Randevu Al
                  </a>
                  <hr class="customer-user-divider">
                  <a href="{{ route('logout') }}" class="customer-user-item logout"
                     onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                     <i class="fa fa-sign-out"></i> Çıkış Yap
                  </a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">{{ csrf_field() }}</form>
               </div>
            </div>
            @else
            <nav class="customer-app-guestnav">
               <a href="/login" class="customer-guest-link">Giriş Yap</a>
               <a href="/register" class="customer-guest-link">Üye Ol</a>
               <a href="/isletmeyonetim/girisyap" class="customer-guest-link primary">Mağaza Girişi</a>
            </nav>
            @endif
         </div>
         <script>
            function customerUserToggle(e){
               e.stopPropagation();
               var m = document.getElementById('customerUserMenu');
               m.classList.toggle('open');
            }
            document.addEventListener('click', function(e){
               var pill = document.getElementById('customerUserPill');
               if(pill && !pill.contains(e.target)){
                  var m = document.getElementById('customerUserMenu');
                  if(m) m.classList.remove('open');
               }
            });
         </script>
      </header>
    
      <section class="content">
      @yield('content')
      </section>
     
      <footer class="footer">
            <div class="wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <p>
                                  Her Hakkı Saklıdır. 2018-{{date('Y')}} © {{ $salon->salon_adi }}. <a href="https://randevumcepte.com.tr/" target="_blank"><img src="{{secure_asset('public/yeni_panel/vendors/images/randevumcepte.png')}}" style="height:28px;vertical-align:middle"></a>
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
                        <img src="{{secure_asset('public/img/footer-background-icons.jpg')}}" alt="">
                    </div>
                    <!--end background-image-->
                </div>
                <!--end background-->
            </div>
        </footer>
      <!--end footer-->
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
       <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
         <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
     <script src="{{secure_asset('public/js/custom.js?v=1.0.821')}}"></script>
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