<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	 
  <title>{{mb_strtoupper(str_replace('i','İ',$aramaterimisayfa))}} | {{mb_strtoupper($salon->salon_adi)}} </title>

   <meta name="description" content="{{ucfirst($aramaterimimeta)}}">
    <meta name="keywords" content="{{$aramaterimimeta}}">
    <meta name="google-site-verification" content="egVhrqesilkAEu-6qarlBkXmIQ9zJPcr-P4wzrM5ZfQ" />
      <meta name="robots" content="index, follow">
        <meta name="googlebot" content="Index, Follow">
        <meta name="rating" content="All">
<meta property="og:url" content="http://{{$_SERVER['HTTP_HOST']}}/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi)))}}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi)))}}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi)))}}/{{$kampanya->id}}" />
  <meta property="og:type"          content="website" />
  <meta property="og:title"         content="{{$kampanya->kampanya_baslik}}" />
  <meta property="og:description"   content="{{$kampanya->kampanya_aciklama}}" />
  <meta property="og:image"         content="http://randevumcepte.com.tr/{{\App\SalonGorselleri::where('salon_id',$salon->id)->where('kampanya_gorsel_kapak',1)->value('salon_gorseli')}}" />
   <link rel="canonical" href="http://randevumcepte.com.tr/">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="{{secure_asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/fonts/font-awesome.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
    <link rel="stylesheet" href="{{secure_asset('public/css/style.css')}}">
    <link rel="stylesheet" href="{{secure_asset('public/css/user.css')}}">
 <link rel="stylesheet" href="{{secure_asset('public/css/navigationmobilemenu.css')}}">
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/perfect-scrollbar/css/perfect-scrollbar.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/material-design-icons/css/material-design-iconic-font.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.magnific-popup/magnific-popup.css')}}"/>
    <link rel="stylesheet" href="{{secure_asset('public/css/owl.carousel.min.css')}}" type="text/css">
   
      <script src="{{secure_asset('public/js/jquery.sticky-kit.min.js')}}"></script>
       <link rel="stylesheet" href="{{secure_asset('public/css/style_gallery.css')}}" type="text/css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
     <link rel="stylesheet" type="text/css" href="{{secure_asset('public/css/demo.css')}}">
	
</head>
<body>
    <input type="hidden" id="kampanyabaslangictarihi" value="{{strtotime($kampanya->kampanya_baslangic_tarihi)}}">
    <input type="hidden" id="kampanyabitistarihi" value="{{strtotime($kampanya->kampanya_bitis_tarihi)}}">
    <input type="hidden" id="buguntarih" value="{{strtotime(date('Y-m-d H:i:s'))}}">
    <div class="page sub-page">
         
        <section class="hero">
            <div class="hero-wrapper">
                
               
                 <div class="main-navigation" id="mobilmenu">
                    <div class="container">

                        <nav class="nav" role="navigation" style="position: relative;height: 90px;padding-top: 20px; padding-bottom: 20px">
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
                         
                          <a href="{{ route('logout') }}" class="nav__item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Çıkış Yap</a>

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
      <span class="toggleNav">
        <svg class="svg-hamburger" viewBox="0 0 1536 1280" xmlns="http://www.w3.org/2000/svg">
          <path class="svg-hamburger-path" d="M1536 1088v128q0 26-19 45t-45 19H64q-26 0-45-19t-19-45v-128q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 704V576q0-26 19-45t45-19h1408q26 0 45 19t19 45zm0-512v128q0 26-19 45t-45 19H64q-26 0-45-19T0 192V64q0-26 19-45T64 0h1408q26 0 45 19t19 45z" fill="rgba(226, 241, 236, 20.85)" />
        </svg>

      </span> 
       
      <a class="nav__logo" href="/avantajlikampanyalar">
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
                                         
                                        @foreach($salonturleri as $salonturu)
                                    <li>
                                       
                                       
                                        <a class="nav__item"  style="color: black" href="#">{{$salonturu->salon_turu_adi}}</a>
                                           
                                        
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
                            <a class="navbar-brand" style="float: left;" href="/avantajlikampanyalar">
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
                                      @foreach($salonturleri as $salonturu)
                                    <li class="nav-item">
                                       
                                       
                                      <a class="nav-link" href="#">{{$salonturu->salon_turu_adi}}</a>
                                           
                                        
                                    </li>
                                    @endforeach
                                    @if($aramaterimleri->count()>0)
                                     <li class="nav-item has-child">
                                        <a class="nav-link" href="#">Etiketler</a>
                                         <ul class="child">
                                            @foreach($aramaterimleri as $aramaterimi)
                                            <li class="nav-item"><a href="/avantajlikampanyalar/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi))) }}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi))) }}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi))) }}/{{$kampanya->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($aramaterimi->arama_terimi)))}}/{{$aramaterimi->id}}" class="nav-link">{{mb_strtoupper(str_replace('i','İ',$aramaterimi->arama_terimi))}}</a></li>
                                            @endforeach
                                              </ul>
                                         </li>
                                     @endif

                            
                                    
                                     

                                </ul>

                              
                                <!--Main navigation list-->
                            </div>

                              

                        </nav>


                        
                       
                    </div>

                    <!--end container-->
                </div>

                <div class="page-title">
                    <div class="container clearfix">
                        <div class="float-xs-none" style="text-align: center;">
                           <h1 class="kampanyabasaramaterimi">{{$aramaterimisayfa}}</h1>
                            <p class="isletmeadi">{{$salon->salon_adi}}</p>
                            <p class="kampanyaaciklama">{{$kampanya->kampanya_aciklama}}
                               
                            </p>
                            <h4 style="color: white; display: none;">
                                <a href="#" style="color:white">{{$kampanya->salonlar->ilce->ilce_adi}} {{$kampanya->salonlar->salon_turu->salon_turu_adi}}</a>
                            </h4>
                        </div>
                      <div class="countdown countdown-container">

                   
                          <div class="clock row">
                              <div class="clock-item clock-days countdown-time-value col-xs-3">
                                  <div class="wrap">
                                      <div class="inner">
                                          <div id="canvas-days" class="clock-canvas"></div>

                                          <div class="text">
                                              <p class="val">0</p>
                                              <p class="type-days type-time" style="margin:-10px 0 0 0">Gün</p>
                                          </div><!-- /.text -->
                                      </div><!-- /.inner -->
                                  </div><!-- /.wrap -->
                              </div><!-- /.clock-item -->

                              <div class="clock-item clock-hours countdown-time-value col-xs-3">
                                  <div class="wrap">
                                      <div class="inner">
                                          <div id="canvas-hours" class="clock-canvas"></div>

                                          <div class="text">
                                              <p class="val">0</p>
                                              <p class="type-hours type-time" style="margin:-10px 0 0 0">Saat</p>
                                          </div><!-- /.text -->
                                      </div><!-- /.inner -->
                                  </div><!-- /.wrap -->
                              </div><!-- /.clock-item -->

                              <div class="clock-item clock-minutes countdown-time-value col-xs-3">
                                  <div class="wrap">
                                      <div class="inner">
                                          <div id="canvas-minutes" class="clock-canvas"></div>

                                          <div class="text">
                                              <p class="val">0</p>
                                              <p class="type-minutes type-time" style="margin:-10px 0 0 0">Dakika</p>
                                          </div><!-- /.text -->
                                      </div><!-- /.inner -->
                                  </div><!-- /.wrap -->
                              </div><!-- /.clock-item -->

                              <div class="clock-item clock-seconds countdown-time-value col-xs-3">
                                  <div class="wrap">
                                      <div class="inner">
                                          <div id="canvas-seconds" class="clock-canvas"></div>

                                          <div class="text">
                                              <p class="val">0</p>
                                              <p class="type-seconds type-time" style="margin:-10px 0 0 0">Saniye</p>
                                          </div><!-- /.text -->
                                      </div><!-- /.inner -->
                                  </div><!-- /.wrap -->
                              </div><!-- /.clock-item -->
                          </div><!-- /.clock -->
                      </div><!-- /.countdown-wrapper -->
                 

                                        
                      <span class="avantajkalansure">Avantajın Kalan Süresi</span>
                      <span class="avantajkalansure">
                         <div id="fb-root"></div>
                                <script>(function(d, s, id) {
                                    var js, fjs = d.getElementsByTagName(s)[0];
                                    if (d.getElementById(id)) return;
                                    js = d.createElement(s); js.id = id;
                                    js.src = 'https://connect.facebook.net/tr_TR/sdk.js#xfbml=1&version=v3.1';
                                    fjs.parentNode.insertBefore(js, fjs);
                            }(document, 'script', 'facebook-jssdk'));</script>
                            <div class="fb-like" style="height: 20px" data-href="{{$salon->facebook_sayfa}}" data-layout="button_count" data-action="like" data-size="large" data-show-faces="true" data-share="false"></div>
                         <div class="fb-share-button" data-href="http://randevumcepte.com.tr/avantajlikampanyalar/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi)))}}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi)))}}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi)))}}/{{$kampanya->id}}" data-layout="button_count" data-size="large" data-mobile-iframe="true"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Frandevumcepte.com.tr%2Favantajlikampanyalar%2F{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi)))}}%2F{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi)))}}%2F{{$salon->id}}%2F{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi)))}}%2F{{$kampanya->id}}
                    &amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Paylaş</a></div>
                      </span>
                    <div class="row avantajkalansure">

                      <div class="col-6 col-sm-6 col-md-6" style="text-align: right;border-right: 1px dotted gray">
                                        @if($salonpuanlar->count()>0)
                                        @if($salonpuanlar->sum('puan')/$salonpuanlar->count()>=0.5 && $salonpuanlar->sum('puan')/$salonpuanlar->count()< 1.5)
                                            <img src="{{secure_asset('public/img/stars1.png')}}" width="128" height="26" alt="1 puan">
                                        @elseif($salonpuanlar->sum('puan')/$salonpuanlar->count()>=1.5 && $salonpuanlar->sum('puan')/$salonpuanlar->count()< 2.5)
                                         <img src="{{secure_asset('public/img/stars2.png')}}" width="128" height="26" alt="2 puan">
                                          @elseif($salonpuanlar->sum('puan')/$salonpuanlar->count()>=2.5 && $salonpuanlar->sum('puan')/$salonpuanlar->count()< 3.5)
                                         <img src="{{secure_asset('public/img/stars3.png')}}" width="128" height="26" alt="3 puan">
                                             @elseif($salonpuanlar->sum('puan')/$salonpuanlar->count()>=3.5 && $salonpuanlar->sum('puan')/$salonpuanlar->count()< 4.5)
                                         <img src="{{secure_asset('public/img/stars4.png')}}" width="128" height="26" alt="4 puan">
                                             @elseif($salonpuanlar->sum('puan')/$salonpuanlar->count()>=4.5 && $salonpuanlar->sum('puan')/$salonpuanlar->count()<=5)
                                         <img src="{{secure_asset('public/img/stars5.png')}}" width="128" height="26" alt="5 puan">
                                         @endif
                                        @else
                                           <img src="{{secure_asset('public/img/stars0.png')}}" width="128" height="26" alt="0 puan">
                                        @endif
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-6" style="transform:translateY(20%);text-align: left; white-space: nowrap; ">
                                         @if($salonpuanlar->count()>0)
                                                [{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}/5]
                                            @else
                                              [0/5]
                                          @endif
                                          {{$salonyorumlar->count()}} Yorum, {{$salonpuanlar->count()}} Puanlama
                                    </div>
                    </div>
                      
                    
                   
                
                <!--============ End Hero Form ======================================================================-->
                <div class="background">
                    <div class="background-image">
                          <img style="opacity:0.3" src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$salon->id)->where('kampanya_gorsel_kapak',1)->where('kampanya_id',$kampanya->id)->value('salon_gorseli'))}}" alt="Background">
                    </div>
                    <!--end background-image-->
                </div>
             
              
            </div>
          
        </section>
      

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
                            <a href="/avantajlikampanyalar">
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
                             <h5>Keşfet</h5>
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

	 <script language="JavaScript" type="text/javascript" src="{{secure_asset('public/js/jquery-3.3.1.min.js')}}"></script>

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
  <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
   <script type="text/javascript" src="{{secure_asset('public/js/kinetic.js')}}"></script>
<script type="text/javascript" src="{{secure_asset('public/js/jquery.final-countdown.js')}}"></script>
<script type="text/javascript">  
    $('document').ready(function() {
     
       var j1110 = $.noConflict(true);
        console.log('current : '+$().jquery);
        console.log('countdown : '+j1110().jquery);
       
       j1110('.countdown').final_countdown({

             'start': $('#kampanyabaslangictarihi').val(),
            'end': $('#kampanyabitistarihi').val(),
            'now': $('#buguntarih').val()    
        });
    });
  </script>
   

</body>
</html>
