<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="{{secure_asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/fonts/font-awesome.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/style.css')}}">
    <link rel="stylesheet" href="{{secure_asset('public/css/user.css')}}">

    <link rel="stylesheet" href="{{secure_asset('public/css/owl.carousel.min.css')}}" type="text/css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
      <script src="{{secure_asset('public/js/jquery.sticky-kit.min.js')}}"></script>
    <title>Sistem Yönetim Paneli | randevumcepte.com.tr </title>

</head>
<body>
  
    <div class="page home-page">
        <!--*********************************************************************************************************-->
        <!--************ HERO ***************************************************************************************-->
        <!--*********************************************************************************************************-->
        <header class="hero has-dark-background">
            <div class="hero-wrapper">
              
                 
                <!--============ Page Title =========================================================================-->
                <div class="page-title">
                    <div class="container" style="text-align: center">
                         
                          <p class="navbar-brand" style="opacity: 1">
                                <img src="{{secure_asset('public/img/avantajbu.png')}}" alt="randevumcepte.com.tr" width="240" height="50">
                            </p>
                        <img src="{{secure_asset('public/img/auth.png')}}" width="100" height="100" alt="">
                        <h1>Sistem Yönetim Paneli</h1> 
                    </div>
                    <!--end container-->
                </div>
                <!--============ End Page Title =====================================================================-->
                <div class="background">
                    
                     <div class="background-image">
                         
                        <img src="{{secure_asset('public/img/kuafor.jpg')}}" alt="">
                    
                        
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
                        <div class="col-md-6">
                            
                            <p>
                                  Her Hakkı Saklıdır. 2018 © randevumcepte.com.tr
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