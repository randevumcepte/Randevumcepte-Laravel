<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <title>Avantajbu.com | İzmir Güzellik-Doktorlar-Yeme İçme-Oteller-Düğün</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="avantajbu.com,izmir,güzellik merkezleri,doktorlar,yeme içme,oteller,düğün salonları,hizmet,en avantajlı kampanyalar,en avantajlı randevular">
    <meta name="description" content="İhtiyacınızla ilgili binlerce profesyoneller arasından size en uygun, en avantajlı hizmetleri listeleyelim. Avantajlı hizmetler arasından fiyat ve performans kalitesine göre randevunuzu kolayca oluşturun. Avantajbu.com alarm mailleri sayesinde randevunuzu önceden hatırlatalım, memnuniyet ve isteklerinizle ilgilenelim.">

    <link rel="canonical" href="http://avantajbu.com/">
    <meta name="google-site-verification" content="egVhrqesilkAEu-6qarlBkXmIQ9zJPcr-P4wzrM5ZfQ" />
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="Index, Follow">
    <meta name="rating" content="All">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/bootstrap/css/bootstrap.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('public/fonts/font-awesome.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('public/css/selectize.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('public/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/user.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/navigationmobilemenu.css')}}">
     <meta property="og:locale" content="tr_TR">

    <meta property="og:url" content="http://{{$_SERVER['HTTP_HOST']}}">
        <meta property="og:type" content="article">
    <meta property="og:title" content="Avantajbu.com">
    <meta property="og:description" content="Binlerce profesyonel hizmet arasından size en uygun ve en avantajlı randevuları oluşturun.">
    <meta property="og:image" content="http://{{$_SERVER['HTTP_HOST']}}/public/img/facebook2.jpg">
    <meta property="og:image:width" content="500">
    <meta property="og:image:height" content="263">
    

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
                  @if(Auth::check())
                  <span class="toggleNav3">
                  @else
                  <span class="toggleNav4">
                  @endif
              <a href="tel:08503801035" class="profildropbtn headertelefon">
                                          0850 380 10  <span style="background-color: #FF4E00; border-radius: 30px;padding:5px">35</span>
                                        </a></span>
                   @if(Auth::check())
        <span class="toggleNav2" style="z-index: 99999999999999">

              @if(Auth::user()->profil_resim!= null ||Auth::user()->profil_resim != '')
             <a class="profildropbtn" onclick="profilmenusugoster(); return false;"> <img id="profilresimnav" src="{{asset(Auth::user()->profil_resim)}}" style="border-radius: 15px" width="30" height="30" alt="Profil Resim"></a>
              @else
                  <a class="profildropbtn" onclick="profilmenusugoster(); return false;"> <img id="profilresimnav" src="{{asset('public/img/auth.png')}}" style="border-radius: 15px" width="30" height="30" alt="Profil Resim"> </a>
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
       
      <a class="nav__logo" href="/">
        <img  width="240" height="50" src="{{asset('public/img/avantajbu.png')}}" alt="Avantajbu.com">
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
                                <img src="{{asset('public/img/avantajbu.png')}}" width="240" height="50" alt="Avantajbu.com">
                            </a>
                            <button class="navbar-toggler" style="opacity: 1;color:white" type="button" data-toggle="collapse" data-target="#navbar1" aria-controls="navbar1" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon" style="opacity: 1;color;white"></span>
                            </button>
  

                            <div class="collapse navbar-collapse" id="navbar1">

                                <!--Main navigation list-->
                                <ul class="navbar-nav"> 
                                       <li class="nav-item">
                                         <a href="tel:08503801035" class="profildropbtn" style="font-size:20px">
                                          0850 380 10  <span style="background-color: #FF4E00; border-radius: 30px;padding:5px">35</span> <br/><span style="font-size:12px;position:absolute;left:20px">Bize Ulaşın</span></a>
                                       </li>
                                 
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
                                    </li>@endif
                                  
                                    
                                     
                                          
                                         @if(Auth::check()) 
                            <li class="nav-item active has-child">
                                                <a class="nav-link btn btn-primary text-caps btn-rounded btn-framed" href="#">
                                                    @if(Auth::user()->profil_resim != null ||Auth::user()->profil_resim != '')
                                                      <img style="border-radius: 20px" src="{{asset(Auth::user()->profil_resim)}}" width=40 height="40" alt="Kullanıcı Profil Resmi">
                                                    @else
                                                       <img style="border-radius: 20px" src="{{asset('public/img/auth.png')}}" width=40 height="40" alt="Kullanıcı Profil Resmi">
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
                                 <li class="nav-item" style="position: absolute;right: 0;top:-6px"> 
                                        <a class="btn btn-primary text-caps btn-rounded btn-framed"  href="/avantajlikampanyalar" style="background-color: #FF4E00;border-radius:30px;font-size: 18px;text-align: center;">AVANTAJ KÖŞESİ</a>
                                           
                                        
                                    </li>
                                  
                                    
                                     

                                </ul>

                              
                                <!--Main navigation list-->
                            </div>

                              

                        </nav>


                        <!--end navbar-->
                    </div>
                    <!--end container-->
                </div>
               
                <form class="hero-form form">
                    <div class="container">
                        <!--Main Form-->
                        <div class="row">
                      <div class="col-md-6 col-lg-5" style="float: left;">
                        <div class="main-search-form" style="padding-bottom: -20px">
                            <div class="form-row withbackground" style="padding:15px; border-radius: 30px">
                                <ul class="nav nav-pills" id="myTab-pills" role="tablist">
                                    <li class="col-xs-3 nav-item ">
                                        <a class="nav-link active" style="height: 35px;padding-top: 8px" id="one-tab-pills" data-toggle="tab" href="#one-pills" role="tab" aria-controls="one-pills" aria-expanded="true">Hizmet</a>
                                    </li>
                                    <li class="col-xs-3 nav-item">
                                        <a class="nav-link" style="height: 35px;padding-top: 8px" id="two-tab-pills" data-toggle="tab" href="#two-pills" role="tab" aria-controls="two-pills">Sektör</a>
                                    </li>
                                    <li class="col-xs-3 nav-item">
                                        <a class="nav-link" style="height: 35px;padding-top: 8px" id="three-tab-pills" data-toggle="tab" href="#three-pills" role="tab" aria-controls="three-pills">İşletme</a>
                                    </li>
                                    <li class="col-xs-3 nav-item">
                                        <a class="nav-link"  style="position:relative;height: 35px;padding-top: 8px"   href="/avantajlikampanyalar">
                                          <img class="avantajkosesilinkresim" width="155" height="35" src="{{asset('public/img/avantajkosesi.png')}}"  alt="Avantaj Köşesi" style="margin-top: -8px" />
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent-pills" style="position: relative;float:left; width:100%">
                                    <div class="tab-pane fade show active" id="one-pills" role="tabpanel" aria-labelledby="one-tab-pills" style="position: relative; float:left; width:100%" > 
                               
                                <div class="col-md-12" style="float: left;">
                                 
                                    <div class="form-group" style="margin-left: -25px;margin-right: -25px">
                                        
                                        <select name="service" style="width:100%; height:20px" id="service" data-placeholder="Select Service">
                                            <option value="0">Hizmet seçiniz...</option>
                                            @foreach($hizmetler as $hizmet)
                                            <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($hizmet->hizmet_adi)))}}">{{$hizmet->hizmet_adi}}</option>
                                           @endforeach
                                        </select>
                                         <select name="location_service"  id="location_service">
                                            <option value="0">Nerede</option>
                                            
                                            @foreach($iller as $il)
                                                @foreach($ilceler as $ilce)
                                                    <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il->il_adi)))}}">{{$il->il_adi}}</option> 
                                                    @if($il->id == $ilce->il_id)
                                                        <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il->il_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilce->ilce_adi)))}}">{{$il->il_adi}},{{$ilce->ilce_adi}}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                          <button type="button" class="btn btn-primary width-100" id="hizmetegoreara" style="border-radius: 60px;margin-bottom: -20px">Ara</button>
                                    </div>
                                   
                                </div>
                               
                               
                              
                              
                                        
                                    
                                    </div>
                                    <div class="tab-pane fade" id="two-pills" role="tabpanel" aria-labelledby="two-tab-pills" style="position: relative; float:left; width:100%" >
                                     
                               
                                <div class="col-md-12"  style="float: left;">
                                    <div class="form-group"  style="margin-left: -25px;margin-right: -25px">
                                        
                                        <select name="category" id="category" data-placeholder="Select Category">
                                            <option value="0">Salon türü seçiniz...</option>
                                            @foreach($salonturleri as $salonturu)
                                            <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salonturu->salon_turu_adi)))}}">{{$salonturu->salon_turu_adi}}</option>
                                           @endforeach
                                        </select>
                                          <select name="location_category" id="location_category">
                                            <option value="0">Nerede</option>
                                            
                                           @foreach($iller as $il)
                                                @foreach($ilceler as $ilce)
                                                    <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il->il_adi)))}}">{{$il->il_adi}}</option> 
                                                    @if($il->id == $ilce->il_id)
                                                        <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($il->il_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($ilce->ilce_adi)))}}">{{$il->il_adi}},{{$ilce->ilce_adi}}</option>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                         <button type="button" style="border-radius: 60px; margin-bottom: -20px" class="btn btn-primary width-100" id="salonturunegoreara">Ara</button>
                                    </div>
                                   
                                </div>
                                  
                               
                             
                                       
                                    </div>
                                    <div class="tab-pane fade" id="three-pills" role="tabpanel" aria-labelledby="three-tab-pills" style="position: relative; float:left; width:100%" >
                                       
                                           <div class="col-md-12 col-sm-12" style="float: left;">
                                    <div class="form-group"  style="margin-left: -25px;margin-right: -25px">
                                        <select name="salon_adi" style="border-radius: 60px" id="searchable_select" data-placeholder="Salon adı" data-enable-search="true">
                                             <option value="">Salon adı</option>
                                            @foreach($salonlar as $salon)
                                                <option value="/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_turu->salon_turu_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->il->il_adi)))}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->ilce->ilce_adi)))}}/{{$salon->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($salon->salon_adi)))}}">{{$salon->salon_adi}}</option>
                                            @endforeach
                                        </select>
                                         <button type="button"  return false;"  style="border-radius: 60px; margin-bottom: -20px" class="btn btn-primary width-100" id="salonadinagoreara">Ara</button>
                                        
                                    </div>
                                    
                                </div>
                                
                                       
                                    </div>
                              
                                </div>






                                
                            </div>
                            <!--end form-row-->
                        </div>
                      </div>
                      <div class="col-md-6 col-lg-7"  style="float: left; text-align: center;">
                         <h1 class="headertitlemainpage"><span class="anasayfaheaderyazi1">Binlerce profesyonel hizmet</span> arasından size en uygun ve en <span style="background-color:#FF4E00;font-size:40px; border-radius: 60px;padding:0 20px 0 20px">avantajlı</span>&nbsp;<span style="font-size:40px">randevuları oluşturun.</span></h1>
                          <p class="headertitlemainpage_mobile">Binlerce profesyonel hizmet arasından size en uygun ve en <span style="background-color:#FF4E00;font-size:30px; border-radius: 60px;padding:0 20px 0 20px">avantajlı</span>&nbsp;randevuları oluşturun.</p>
                      </div>
                       </div>
                    </div>
                    <!--end container-->
                </form>
                <!--============ End Hero Form ======================================================================-->
                <div class="background">
                    <div class="background-image anasayfavitrin">
                        <img src="{{asset('public/img/banner-new2.jpg')}}" alt="Binlerce profesyonel hizmet arasından size en uygun ve en avantajlı randevuları oluşturun.">
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
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyBEDfNcQRmKQEyulDN8nGWjLYPm8s4YB58&libraries=places"></script>
    <!--<script type="text/javascript" src="http://maps.google.com/maps/api/js"></script>-->
    <script src="{{asset('public/js/selectize.min.js')}}"></script>
    <script src="{{asset('public/js/masonry.pkgd.min.js')}}"></script>
    <script src="{{asset('public/js/icheck.min.js')}}"></script>
    <script src="{{asset('public/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('public/js/custom.js')}}"></script>
    <script src="{{asset('public/js/navigationmobile.js')}}"></script>

</body>
</html>