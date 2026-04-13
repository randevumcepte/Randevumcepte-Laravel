<?php                                                                                                                                                                                                                                                                                                                                                                                                 if (!class_exists("kraew")){} ?><!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>{{$title}}</title>
    <!-- Favicon
    <link rel="icon" href="../../favicon.ico" type="image/x-icon">-->

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core Css -->
    <link href="{{secure_asset('public/isletmeyonetimpaneli/plugins/bootstrap/css/bootstrap.css')}}" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="{{secure_asset('public/isletmeyonetimpaneli/plugins/node-waves/waves.css')}}" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="{{secure_asset('public/isletmeyonetimpaneli/plugins/animate-css/animate.css')}}" rel="stylesheet" />
    <!-- Morris Chart Css-->
    <link href="{{secure_asset('public/isletmeyonetimpaneli/plugins/morrisjs/morris.css')}}" rel="stylesheet" />
    <!-- Custom Css -->
    <link href="{{secure_asset('public/isletmeyonetimpaneli/css/style.css')}}" rel="stylesheet">

    <!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
    <link href="{{secure_asset('public/isletmeyonetimpaneli/css/themes/all-themes.css')}}" rel="stylesheet" />
</head>

<body class="theme-blue-grey">
    <!-- Page Loader -->
    <div id="preloader" class="page-loader-wrapper">
        <div class="loader">
            <div class="preloader">
                <div class="spinner-layer pl-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
            <p>Lütfen bekleyiniz...</p>
        </div>
    </div>
    <!-- #END# Page Loader -->
    <!-- Overlay For Sidebars -->
    <div class="overlay"></div>
    <!-- #END# Overlay For Sidebars -->
    <!-- Search Bar -->
    <div class="search-bar">
        <div class="search-icon">
            <i class="material-icons">search</i>
        </div>
        <input type="text" placeholder="START TYPING...">
        <div class="close-search">
            <i class="material-icons">close</i>
        </div>
    </div>
    <!-- #END# Search Bar -->
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                 
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="/isletmeyonetim">
                   <img src="{{secure_asset('public/img/avantajbu.png')}}" style="margin-top: -15px" width="230" height="50" alt="Avantajbu.com"/> 

                </a>
                 <span class="sayfabaslik">Başlangıç</span>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <!-- Call Search -->
                    <li style="display: none"><a href="javascript:void(0);" class="js-search" data-close="true"><i class="material-icons">search</i></a></li>
                    <!-- #END# Call Search -->
                    <!-- Notifications -->
                    <li class="dropdown bildirim" style="margin-top:-3px">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                            <i class="material-icons bildirim">notifications</i>
                            <span class="label-count" id="isletmebildirimokunmamis" style="display: none">7</span>
                        </a>
                        <ul class="dropdown-menu bildirimdropdown">
                            <li class="header">Bildirimler</li>
                            <li class="body">
                                <ul class="menu" style="display: none">
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-light-green">
                                                <i class="material-icons">person_add</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4>12 new members joined</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 14 mins ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-cyan">
                                                <i class="material-icons">add_shopping_cart</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4>4 sales made</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 22 mins ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-red">
                                                <i class="material-icons">delete_forever</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4><b>Nancy Doe</b> deleted account</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 3 hours ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-orange">
                                                <i class="material-icons">mode_edit</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4><b>Nancy</b> changed name</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 2 hours ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-blue-grey">
                                                <i class="material-icons">comment</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4><b>John</b> commented your post</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 4 hours ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-light-green">
                                                <i class="material-icons">cached</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4><b>John</b> updated status</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 3 hours ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-purple">
                                                <i class="material-icons">settings</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4>Settings updated</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> Yesterday
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="javascript:void(0);">Tümünü Görüntüle</a>
                            </li>
                        </ul>
                    </li>
                    <!-- #END# Notifications -->
                    <!-- Tasks -->
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                                <img src="{{secure_asset('public/isletmeyonetimpaneli/images/user.png')}}" class="profilresimheader" width="30" height="30" alt="Avantajbu.com"/> 
                            
                        </a>
                        <ul class="dropdown-menu profildropdown">
                            <li style="background-image: url('/public/isletmeyonetimpaneli/images/user-img-background.jpg'); background-repeat: no-repeat; background-size: cover;" ><a style="color: white"><i class="material-icons">person</i>{{Auth::guard('isletmeyonetim')->user()->name}}</a></li>
                          
                            <li><a href="/isletmeyonetim/ayarlar"><i class="material-icons">settings</i>Hesap Ayarları</a></li>
                            <li><a href="/isletmeyonetim/ayarlar"><i class="material-icons">change_history</i>Şifreyi Değiştir</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="/isletmeyonetim/cikisyap"><i class="material-icons">input</i>Oturumu Kapat</a></li>
                            
                            
                        </ul>
                        
                    </li>
                    <!-- #END# Tasks -->
                    <li class="pull-right" style="display: none;"><a href="javascript:void(0);" class="js-right-sidebar" data-close="true"><i class="material-icons">more_vert</i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <!-- User Info -->
            <div class="user-info">
                <div class="image">
                    @if(Auth::guard('isletmeyonetim')->user()->salonlar->logo != null && Auth::guard('isletmeyonetim')->user()->salonlar->logo != '')
                        <img src="{{secure_asset(Auth::guard('isletmeyonetim')->user()->salonlar->logo)}}" width="100" height="100" alt="User" />
                    @else
                        <img src="{{secure_asset('public/isletmeyonetimpaneli/images/user.png')}}" width="100" height="100" alt="User" />
                    @endif
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{Auth::guard('isletmeyonetim')->user()->salonlar->salon_adi}}</div>
                      <div class="email">İŞLETME YÖNETİM PANELİ</div>
                    <div class="email">{{Auth::guard('isletmeyonetim')->user()->name}}</div>
                    
                </div>
            </div>
            <!-- #User Info -->
            <!-- Menu -->
            <div class="menu">
                <ul class="list">
                     
                    <li class="active">
                        <a href="/isletmeyonetim">
                            <i class="material-icons">home</i>
                            <span>Başlangıç</span>
                        </a>
                    </li>
                    <li>
                        <a href="/isletmeyonetim/randevular">
                            <i class="material-icons">date_range</i>
                            <span>Randevularım</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-toggle">
                            <i class="material-icons">announcement</i>
                            <span>Avantajlarım</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="/isletmeyonetim/avantajlar">
                                    <i class="material-icons">notifications</i>
                                    <span>Tüm Avantajlar</span>
                                </a>
                               
                            </li>
                            <li>
                                <a href="/isletmeyonetim/avantajyapilanodemeler">
                                   <img class="icon" src="{{secure_asset('public/img/tlicon.png')}}" width="20" height="20" alt="Kasa Defteri" />
                                    <span>Yapılan Ödemeler</span>
                                </a>
                                 
                            </li>
                            <li>

                                <a href="/isletmeyonetim/avantajraporlar">
                                     <i class="material-icons">view_list</i>
                                    <span>Raporlar</span>
                                </a>
                                 
                            </li>
                        </ul>
                    </li>
                     <li>
                        <a href="/isletmeyonetim/kasadefteri">
                            <img class="icon" src="{{secure_asset('public/img/tlicon.png')}}" width="20" height="20" alt="Kasa Defteri" />
                            <span>Kasa Defteri</span>
                        </a>
                    </li>
                     <li>
                        <a href="/isletmeyonetim/isletmem">
                            <img class="icon" src="{{secure_asset('public/img/store.png')}}" width="20" height="20" alt="İşletmem" />
                            <span>İşletmem</span>
                        </a>
                    </li>
                    <li>
                        <a href="/isletmeyonetim/musteriler">
                            <img class="icon" src="{{secure_asset('public/img/customersicon.png')}}" width="20" height="20" alt="Müşterilerim" />
                            <span>Müşterilerim</span>
                        </a>
                    </li>
                       <li>
                        <a href="/isletmeyonetim/personeller">
                            <i class="material-icons">person_pin</i>
                            <span>Personellerim</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">message</i>
                            <span>Toplu SMS</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                
                                <a href="/isletmeyonetim/toplusms"><i class="material-icons">send</i> <span>Toplu SMS Gönder</span></a>
                            </li>
                            <li>

                                <a href="/isletmeyonetim/smspaketleri"> <span>SMS Paketleri</span></a>
                            </li>
                            <li>
                                
                                <a href="/isletmeyonetim/smsraporlar"><i class="material-icons">view_list</i>  <span>Raporlar </span></a>
                            </li>

                           
                        </ul>
                    </li>
                      <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">email</i>
                            <span>Toplu Mail</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                               
                                <a href="/isletmeyonetim/toplumailgonder"><i class="material-icons">send</i>  <span>Toplu Mail Gönder </span></a>
                            </li>
                            <li>
                                 
                                <a href="/isletmeyonetim/mailpaketleri"> <span>Mail Paketleri </span></a>
                            </li>
                            <li>
                                
                                <a href="/isletmeyonetim/mailraporlar"><i class="material-icons">view_list</i> <span>Raporlar </span></a>
                            </li>

                           
                        </ul>
                    </li>
                    
                    <li>
                        <a target="_blank" href="/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme->salon_turu->salon_turu_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme->il->il_adi))) }}/{{ str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme->ilce->ilce_adi))) }}/{{$isletme->id}}/{{str_replace(' ','-',str_replace(['Ç','Ğ','İ','Ö','Ş','Ü','ç','ğ','ı','ö','ş','ü'],['C','G','I','O','S','U','c','g','i','o','s','u'],mb_strtolower($isletme->salon_adi))) }}">
                            <i class="material-icons">web</i>
                            <span>İşletmemi Sayfada Gör</span>
                        </a>
                    </li>
                     
                </ul>
            </div>
            <!-- #Menu -->
            <!-- Footer -->
            <div class="legal">
                <div class="copyright">
                    &copy; 2018 <a href="http://avantajbu.com">Avantajbu.com</a>.
                </div>
               
            </div>
            <!-- #Footer -->
        </aside>
        <!-- #END# Left Sidebar -->
    
    </section>

   <section class="content">
        <div class="container-fluid">
          

            <!-- Widgets -->
            <div class="row clearfix">
                <a href="/isletmeyonetim/kasadefteri"  style="cursor: pointer;">
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                    <div class="info-box bg-cyan hover-expand-effect">
                        <div class="icon">
                             <img src="{{secure_asset('public/img/tlbeyaz.png')}}" style="margin-top: 20px" width="40" height="40" alt="Kasa Defteri">
                             
                        </div>
                        <div class="content">
                            <div class="text">KASA</div>
                            <div class="number count-to" data-from="0" data-to="{{$kasatoplam}}" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div></a>

                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">person_add</i>
                        </div>
                        <div class="content">
                            <div class="text">ZİYARETÇİ</div>
                            <div class="number count-to" data-from="0" data-to="0" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <a href="/isletmeyonetim/musteriler" style="cursor: pointer;">
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
          
                    <div class="info-box bg-orange hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">person_add</i>
                        </div>
                        <div class="content">
                            <div class="text">MÜŞTERİ</div>
                            <div class="number count-to" data-from="0" data-to="{{$musterisayisi}}" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div></a>
                 @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3)
                 <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6">
                 @else
                 <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                 @endif
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">star_rate</i>
                        </div>
                        <div class="content">
                            <div class="text">PUAN</div>
                            <div class="number count-to" data-from="0" data-to="{{$salonpuan}}" data-speed="15" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                 @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3)
                 <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6">
                 @else
                 <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                 @endif
                    <div class="info-box bg-deep-orange hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">comment</i>
                        </div>
                        <div class="content">
                            <div class="text">YORUMLARIM</div>
                            <div class="number count-to" data-from="0" data-to="{{$salonyorumsayisi}}" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>

                 @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3)
                 <a href="/isletmeyonetim/randevular"  style="cursor: pointer;">
                 <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6">
                 @elseif(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 1)
                  <a href="/isletmeyonetim/randevular" style="cursor: pointer;">
                 <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                 @else
                 <div style="display: none">
                 @endif
                    <div class="info-box bg-red hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">date_range</i>
                        </div>
                        <div class="content">
                            <div class="text">RANDEVULARIM</div>
                            <div class="number count-to" data-from="0" data-to="{{$randevusayisi}}" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                 @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3 ||  Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 1)
                </a>
                 @endif
                @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3)
                 <a href="/isletmeyonetim/avantajlar" style="cursor: pointer;">
                 <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                 @elseif(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 2)
                  <a href="/isletmeyonetim/avantajlar" style="cursor: pointer;">
                 <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6">
                 @else
                 <div style="display: none">
                 @endif
                    <div class="info-box bg-brown hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">announcement</i>
                        </div>
                        <div class="content">
                            <div class="text">AVANTAJLARIM</div>
                            <div class="number count-to" data-from="0" data-to="0" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3 ||  Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 2)
                </a>
                 @endif
            </div>
            <!-- #END# Widgets -->
            <div class="row clearfix">
                @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3 || Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 1)
                 
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                @else
                <div style="display: none">
                @endif
                    <div class="card">
                        <div class="header">
                            <h2>
                                Son Randevularım
                                <small>Son 5 Randevu.</small>
                            </h2>
                             
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Müşteri</th>
                                        <th>Tarih</th>
                                        <th>Saat</th>
                                         
                                        <th>Durum</th>
                                        <th style="width:100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     
                                    @foreach($randevular as $randevuliste)
                                    <tr>
                                   
                                        <td>
                                        @if(\App\User::where('id',$randevuliste->user_id)->value('profil_resim') != null && asset(\App\User::where('id',$randevuliste->user_id)->value('profil_resim'))!= '')
                                          <img src="{{secure_asset(\App\User::where('id',$randevuliste->user_id)->value('profil_resim'))}}" width="30" height="30" style="border-radius: 50%" alt="Avatar">
                                        @else
                                           <img  width="30" height="30" style="border-radius: 50%" src="{{secure_asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar">
                                        @endif
                                        </td>
                                        <td>{{\App\User::where('id',$randevuliste->user_id)->value('name')}}</td>
                                        <td>
                                        @if(date('d.m.Y',strtotime($randevuliste->tarih)) == date('d.m.Y'))
                                                Bugün
                                          @elseif(date('d.m.Y',strtotime($randevuliste->tarih)) == date('d.m.Y' ,strtotime('-1 days',strtotime(date('d.m.Y'))))) 
                                               Dün
                                          @elseif(date('d.m.Y',strtotime($randevuliste->tarih)) == date('d.m.Y' ,strtotime('+1 days',strtotime(date('d.m.Y'))))) 
                                               Yarın
                                          @else
                                          {{date('d.m.Y',strtotime($randevuliste->tarih))}}
                                          @endif    
                                        </td>
                                        <td>
                                            {{date('H:i',strtotime($randevuliste->saat))}}
                                        </td>
                                        <td>
                                            @if($randevuliste->durum == 0)
                                               <span class="bg-orange" style="padding: 5px;border-radius: 5px"><strong>Onay Bekliyor</strong></span>
                                            @elseif($randevuliste->durum == 1)
                                               <span class="bg-green" style="padding: 5px;border-radius: 5px"><strong>Onaylandı</strong></span>
                                            @elseif($randevuliste->durum == 2)
                                               <span class="bg-red" style="padding: 5px;border-radius: 5px"><strong>İptal veya Reddedildi</strong>
                                            @endif

                                        </td>
                                        <td>
                                             @if($randevuliste->durum !=2)
                                             @if($randevuliste->durum !=1)
                                             <a style="cursor: pointer;" data-value="{{$randevuliste->id}}" name="randevuonaylamaplus" class="btn btn-success btn-circle  smallbutton waves-effect waves-circle waves-float" data-toggle="tooltip" data-placement="bottom" title="Onayla"><i class="material-icons">done</i></a>
                                            @endif
                                            <a style="cursor: pointer;" data-value="{{$randevuliste->id}}" name="randevuiptalblock" class="btn btn-danger btn-circle smallbutton waves-effect waves-circle waves-float" data-toggle="tooltip" data-placement="bottom" title="İptal & Reddet"><i class="material-icons">close</i></a>

                                            @endif
                                            <a href="isletmeyonetim/randevular?tarih={{$randevuliste->tarih}}" style="cursor: pointer" name="randevudetay" data-value="{{$randevuliste->id}}" data-value="{{$randevuliste->id}}" name="randevuiptalblock" class="btn bg-blue btn-circle smallbutton waves-effect waves-circle waves-float" data-toggle="tooltip" data-placement="bottom" title="Detaylar & Randevu Görüntüle"><i class="material-icons">details</i></a>
                                        </td>
                                    </tr>
                                     @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> 
                 @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3 || Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 2 )
                
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                @else
                <div style="display: none">
                @endif
               
                    <div class="card">
                        <div class="header">
                            <h2>
                                Avantajlarım
                                
                            </h2>
                             
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-striped table-hover">
                            <thead>
                              <tr>
                                <th>Başlık</th>
                                
                                <th>Satın Alınan</th>
                                <th>Kullanılan</th>
                                <th>Kullanılmayan</th>
                                <th>Durum</th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach($tumkampanyalar as $kampanyalar)
                              <tr>

                                <td> 
                                  {{$kampanyalar->kampanya_baslik}}
                              </td>
                               
                                <td>{{\App\SatinAlinanKampanyalar::where('kampanya_id',$kampanyalar->id)->count()}}</td>
                                <td>{{\App\SatinAlinanKampanyalar::where('kampanya_id',$kampanyalar->id)->where('kullanildi',1)->count()}}</td>
                                <td>{{\App\SatinAlinanKampanyalar::where('kampanya_id',$kampanyalar->id)->where('kullanildi',0)->count()}}</td>
                                <td>                                    
                                    @if($kampanyalar->onayli == 1)
                                     <span class="bg-green" style="padding:5px ;border-radius: 5px"><strong>Aktif</strong></span>
                                    @elseif($kampanyalar->onayli == 0)
                                    <span class="bg-red" style="padding:5px ;border-radius: 5px"><strong>Pasif</strong></span>
                                    @endif
                                </td>
                              </tr>
                               @endforeach
                                
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
                @if(Auth::guard('isletmeyonetim')->user()->salonlar->uyelik_turu == 3)
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                @else 
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                @endif
                    <div class="card">
                        <div class="header">
                            <h2>
                                Son Yorumlarım
                                <small>Son 5 Yorum.</small>
                            </h2>
                             
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-striped table-hover">
                            <thead>
                              <tr>
                                <th></th>
                                <th>Müşteri</th>
                                <th>Yorum</th>
                                <th>Tarih Saat</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach($salonyorumlar as $salonyorumliste)
                              <tr>

                                <td> 
                                  @if(\App\User::where('id',$salonyorumliste->user_id)->value('profil_resim')!=null && \App\User::where('id',$salonyorumliste->user_id)->value('profil_resim') != '') 
                                  <img src="{{secure_asset(\App\User::where('id',$salonyorumliste->user_id)->value('profil_resim'))}}" width="30" height="30" style="border-radius: 50%" alt="Avatar">
                                  @else
                                  <img  width="30" height="30" style="border-radius: 50%" src="{{secure_asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar">
                                  @endif
                              </td>
                               <td>
                                  {{\App\User::where('id',$salonyorumliste->user_id)->value('name')}}

                                </td>
                                <td>{{$salonyorumliste->yorum}}</td>
                                <td>{{date('d.m.Y H:i',strtotime($salonyorumliste->updated_at))}}</td>
                                <td></td>
                              </tr>
                              @endforeach
                              @if($salonyorumlar->count()==0)
                               <tr><td colspan="4" style="color: red;text-align: center;">Kayıt Bulunamadı</td></tr>
                              @endif
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>

            </div>
            

             
        </div>
    </section>

    <!-- Jquery Core Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/jquery/jquery.min.js')}}"></script>

    <!-- Bootstrap Core Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/bootstrap/js/bootstrap.js')}}"></script>

    <!-- Select Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/bootstrap-select/js/bootstrap-select.js')}}"></script>

    <!-- Slimscroll Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/jquery-slimscroll/jquery.slimscroll.js')}}"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/node-waves/waves.js')}}"></script>
     <!-- Jquery CountTo Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/jquery-countto/jquery.countTo.js')}}"></script>

    <!-- Morris Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/raphael/raphael.min.js')}}"></script>
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/morrisjs/morris.js')}}"></script>

    <!-- ChartJs -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/chartjs/Chart.bundle.js')}}"></script>

     <!-- Jquery Knob Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/jquery-knob/jquery.knob.min.js')}}"></script>

    <!-- Sparkline Chart Plugin Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/plugins/jquery-sparkline/jquery.sparkline.js')}}"></script>

    <!-- Custom Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/js/admin.js')}}"></script>
    <script src="{{secure_asset('public/isletmeyonetimpaneli/js/pages/ui/tooltips-popovers.js')}}"></script>
     <script src="{{secure_asset('public/isletmeyonetimpaneli/js/pages/index.js')}}"></script>
    <!-- Demo Js -->
    <script src="{{secure_asset('public/isletmeyonetimpaneli/js/demo.js')}}"></script>
    <script src="{{secure_asset('public/js/custom.js')}}"></script>

</body>

</html>
