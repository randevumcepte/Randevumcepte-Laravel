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

    <title>randevumcepte.com.tr | Kuaför, Güzellik Merkezi, Berber, Spa Randevunuzu Hemen Alın</title>

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
                                <img src="{{secure_asset('public/img/avantajbu.png')}}" width="240" height="50" alt="randevumcepte.com.tr">
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse">
                                <!--Main navigation list-->
                                <ul class="navbar-nav"> 
                                     
                                 
                                        <li class="nav-item"> 
 
                                        <a class="btn btn-primary text-caps btn-rounded btn-framed" href="/kampanyalar" style="background-color: #FF4E00; color:white">Kampanyalar</a> 
                                        
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
                                                        <a href="/avantajlarim" class="nav-link">Avantajlarım</a>
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
                                                <a href=" {{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmet->hizmet_adi))) }}" class="nav-link">{{$hizmet->hizmet_adi}}</a>
                                            </li>
                                            @endif
                                             @endforeach
                                        </ul>
                                        
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
                <!--============ End Main Navigation ================================================================-->
                <!--============ Page Title =========================================================================-->
                <div class="page-title">
                    <div class="container">
                        
                    </div>
                    <!--end container-->
                </div>
                <!--============ End Page Title =====================================================================-->
                <!--============ Hero Form ==========================================================================-->
                <form class="hero-form form">
                    <div class="container">
                        <!--Main Form-->
                      <div class="col-md-5">
                        <div class="main-search-form">
                            <div class="form-row withbackground" style="padding:15px; border-radius: 30px">
                                <ul class="nav nav-pills" id="myTab-pills" role="tablist">
                                    <li class="col-xs-4 nav-item ">
                                        <a class="nav-link active" id="one-tab-pills" data-toggle="tab" href="#one-pills" role="tab" aria-controls="one-pills" aria-expanded="true">Hizmet</a>
                                    </li>
                                    <li class="col-xs-4 nav-item">
                                        <a class="nav-link" id="two-tab-pills" data-toggle="tab" href="#two-pills" role="tab" aria-controls="two-pills">Salon Türü</a>
                                    </li>
                                    <li class="col-xs-4 nav-item">
                                        <a class="nav-link" id="three-tab-pills" data-toggle="tab" href="#three-pills" role="tab" aria-controls="three-pills">Salon Adı</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent-pills" style="position: relative;float:left; width:100%">
                                    <div class="tab-pane fade show active" id="one-pills" role="tabpanel" aria-labelledby="one-tab-pills" style="position: relative; float:left; width:100%" > 
                               
                                <div class="col-md-12" style="float: left;">
                                 
                                    <div class="form-group">
                                        
                                        <select name="service" id="service" data-placeholder="Select Service">
                                            <option value="0">Hizmet seçiniz...</option>
                                            @foreach($hizmetler as $hizmet)
                                            <option value="{{$hizmet->id}}">{{$hizmet->hizmet_adi}}</option>
                                           @endforeach
                                        </select>
                                    </div>
                                   
                                </div>
                                 <div class="col-md-12" style="float: left;">
                                    <div class="form-group">
                                         
                                         <select name="location_service"  id="location_service">
                                            <option value="0">Nerede</option>
                                            
                                            @foreach($iller as $il)
                                                @foreach($ilceler as $ilce)
                                                    <option value="{{$il->id}}">{{$il->il_adi}}</option> 
                                                    @if($il->id == $ilce->il_id)
                                                        <option value="{{$il->id}},{{$ilce->id}}">{{$il->il_adi}},{{$ilce->ilce_adi}}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                </div>
                               
                                <div class="col-md-12" style="float: left;">
                                    <button type="submit" onclick="hizmetegoreara(); return false;" class="btn btn-primary width-100" style="border-radius: 60px;">Ara</button>
                                </div>
                              
                                        
                                    
                                    </div>
                                    <div class="tab-pane fade" id="two-pills" role="tabpanel" aria-labelledby="two-tab-pills" style="position: relative; float:left; width:100%" >
                                     
                               
                                <div class="col-md-12"  style="float: left;">
                                    <div class="form-group">
                                        
                                        <select name="category" id="category" data-placeholder="Select Category">
                                            <option value="0">Salon türü seçiniz...</option>
                                            @foreach($salonturleri as $salonturu)
                                            <option value="{{$salonturu->id}}">{{$salonturu->salon_turu_adi}}</option>
                                           @endforeach
                                        </select>
                                    </div>
                                   
                                </div>
                                 <div class="col-md-12 col-sm-12" style="float: left;">
                                    <div class="form-group">
                                        <select name="location_category" id="location_category">
                                            <option value="0">Nerede</option>
                                            
                                            @foreach($iller as $il)
                                                @foreach($ilceler as $ilce)
                                                    <option value="{{$il->id}}">{{$il->il_adi}}</option> 
                                                    @if($il->id == $ilce->il_id)
                                                        <option value="{{$il->id}},{{$ilce->id}}">{{$il->il_adi}},{{$ilce->ilce_adi}}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                      <!--  <input name="location" type="text" class="form-control" id="input-location" placeholder="Nerede...">
                                        <span class="geo-location input-group-addon" data-toggle="tooltip" data-placement="top" title="Yakınlarımda"><i class="fa fa-map-marker"></i></span>-->
                                    </div>
                                    
                                </div>
                               
                                <div class="col-md-12 col-sm-12" style="float: left;">
                                    <button type="submit"  style="border-radius: 60px; onclick="salonturunegoreara();return false" class="btn btn-primary width-100">Ara</button>
                                </div>
                                       
                                    </div>
                                    <div class="tab-pane fade" id="three-pills" role="tabpanel" aria-labelledby="three-tab-pills" style="position: relative; float:left; width:100%" >
                                        <form action="{{route('salonara')}}"  class="form" enctype="multipart/form-data" method="POST" value="{{ csrf_token() }}" >
                                            {{ csrf_field() }}
                                           <div class="col-md-12 col-sm-12" style="float: left;">
                                    <div class="form-group">
                                        
                                        <input name="salon_adi" style="border-radius: 30px"  type="text" class="form-control" id="salon_adi" placeholder="Salon adı">
                                    </div>
                                    
                                </div>
                                  <div class="col-md-12 col-sm-12" style="float: left;">
                                    <button type="submit"  style="border-radius: 60px" class="btn btn-primary width-100">Ara</button>
                                </div>
                                       
                                    </div>
                                </form>
                                </div>






                                
                            </div>
                            <!--end form-row-->
                        </div>
                      </div>
                       
                    </div>
                    <!--end container-->
                </form>
                <!--============ End Hero Form ======================================================================-->
                <div class="background">
                    <div class="background-image">
                        <img src="{{secure_asset('public/img/banner-new2.jpg')}}" alt="">
                    </div>
                    <!--end background-image-->
                </div>
                <!--end background-->
            </div>
            <!--end hero-wrapper-->
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
                        <div class="col-md-5">
                            <a href="#" class="brand">
                                <img src="{{secure_asset('public/img/logo.png')}}" alt="">
                            </a>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut nec tincidunt arcu, sit amet
                                fermentum sem. Class aptent taciti sociosqu ad litora torquent per conubia nostra.
                            </p>
                        </div>
                        <!--end col-md-5-->
                        <div class="col-md-3">
                            <h2>Navigation</h2>
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <nav>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="#">Home</a>
                                            </li>
                                            <li>
                                                <a href="#">Listing</a>
                                            </li>
                                            <li>
                                                <a href="#">Pages</a>
                                            </li>
                                            <li>
                                                <a href="#">Extras</a>
                                            </li>
                                            <li>
                                                <a href="#">Contact</a>
                                            </li>
                                            <li>
                                                <a href="#">Submit Ad</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <nav>
                                        <ul class="list-unstyled">
                                            <li>
                                                <a href="#">My Ads</a>
                                            </li>
                                            <li>
                                                <a href="#">Sign In</a>
                                            </li>
                                            <li>
                                                <a href="#">Register</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <!--end col-md-3-->
                        <div class="col-md-4">
                            <h2>Contact</h2>
                            <address>
                                <figure>
                                    124 Abia Martin Drive<br>
                                    New York, NY 10011
                                </figure>
                                <br>
                                <strong>Email:</strong> <a href="#">hello@example.com</a>
                                <br>
                                <strong>Skype: </strong> Craigs
                                <br>
                                <br>
                                <a href="contact.html" class="btn btn-primary text-caps btn-framed">Contact Us</a>
                            </address>
                        </div>
                        <!--end col-md-4-->
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
    <script>
        /*function uyeollinkdegistir(){
            var uyeollink = document.getElementById('uyeollink');
            uyeollink.classList.remove('uyeolgirisyap2');
            var girisyaplink = document.getElementById('girisyaplink');
            girisyaplink.classList.add('uyeolgirisyap');
            girisyaplink.setAttribute('style','border-top-right-radius: 30px;border-bottom-right-radius: 30px');
 

        }
        function uyeollinkdegistir2(){
            var girisyaplink = document.getElementById('girisyaplink');
            girisyaplink.classList.remove('uyeolgirisyap');
           var uyeollink = document.getElementById('uyeollink');
           uyeollink.classList.add('uyeolgirisyap2');

        }*/
        function salonturunegoreara(){

            var category = document.getElementById('category');
            var location = document.getElementById('location_category');
            if(category.options[category.selectedIndex].value == 0){
                alert('Lütfen salon türünü seçiniz');
            } 
            else{
                 category = category.options[category.selectedIndex].text.toLowerCase();
                 var replaceCharsCategory={ "ö":"o", "ç":"c", "ğ":"g", "ş":"s", "ı":"i", "ü":"u", " ":"-"};
                category = category.replace(/ö|ü|ç|ş|ı|ğ| /g,function(match) {return replaceCharsCategory[match];});
                if(location.options[location.selectedIndex].value == "0")
                {
                    location = '';
                   
                }
                else if(location.options[location.selectedIndex].value == "1"){
                    
                }
                else{
                     location = location.options[location.selectedIndex].text.toLowerCase();

                      var replaceCharsLocation={ "ö":"o", "ç":"c", "ğ":"g", "ş":"s", "ı":"i", "ü":"u", " ":"-", ",":"/"};
                      location = location.replace(/ö|ü|ç|ş|ı|ğ|,| /g,function(match) {return replaceCharsLocation[match];});

                }
                 window.location.href = category+'/'+location;
            }
           
 
        }
        function hizmetegoreara(){

            var service = document.getElementById('service');
            var location = document.getElementById('location_service');
            if(service.options[category.selectedIndex].value == 0){
                alert('Lütfen aramak istediğniz hizmeti seçiniz');
            } 
            else{
                 service = service.options[service.selectedIndex].text.toLowerCase();
                 var replaceCharsCategory={ "ö":"o", "ç":"c", "ğ":"g", "ş":"s", "ı":"i", "ü":"u", " ":"-"};
                service = service.replace(/ö|ü|ç|ş|ı|ğ| /g,function(match) {return replaceCharsCategory[match];});
                if(location.options[location.selectedIndex].value == "0")
                {
                    location = '';
                   
                }
                else if(location.options[location.selectedIndex].value == "1"){
                    
                }
                else{
                     location = location.options[location.selectedIndex].text.toLowerCase();

                      var replaceCharsLocation={ "ö":"o", "ç":"c", "ğ":"g", "ş":"s", "ı":"i", "ü":"u", " ":"-", ",":"/"};
                      location = location.replace(/ö|ü|ç|ş|ı|ğ|,| /g,function(match) {return replaceCharsLocation[match];});

                }
                window.location.href = service+'/'+location;
            }
            
 
        }
    </script>
    <script src="{{secure_asset('public/js/jquery-3.3.1.min.js')}}"></script>
    <script type="text/javascript" src="{{secure_asset('public/js/popper.min.js')}}"></script>
    <script type="text/javascript" src="{{secure_asset('public/bootstrap/js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyBEDfNcQRmKQEyulDN8nGWjLYPm8s4YB58&libraries=places"></script>
    <!--<script type="text/javascript" src="http://maps.google.com/maps/api/js"></script>-->
    <script src="{{secure_asset('public/js/selectize.min.js')}}"></script>
    <script src="{{secure_asset('public/js/masonry.pkgd.min.js')}}"></script>
    <script src="{{secure_asset('public/js/icheck.min.js')}}"></script>
    <script src="{{secure_asset('public/js/jquery.validate.min.js')}}"></script>
    <script src="{{secure_asset('public/js/custom.js')}}"></script>

</body>
</html>