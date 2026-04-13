<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="RandevumCepte Satış Ortaklığı Yönetim Paneli">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="author" content="RandevumCepte">
  <title>{{ $sayfa_baslik }} | RandevumCepte Satış Ortaklığı Yönetim Paneli</title>
  <!-- Favicon -->
  <link rel="icon" href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/nucleo/css/nucleo.css')}}" type="text/css">
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" type="text/css">
 

  <!-- Page plugins -->
    <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/select2/dist/css/select2.min.css')}}">
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/quill/dist/quill.core.css')}}">
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">
   <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/animate.css/animate.min.css')}}">
     <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/fullcalendar/dist/fullcalendar.min.css')}}">

  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/vendor/sweetalert2/dist/sweetalert2.min.css')}}">
  <!-- Argon CSS -->
  <link rel="stylesheet" href="{{secure_asset('public/satisortakligipanel/assets/css/credit-card-form.css')}}">
  <link rel="stylesheet" href="{{secure_asset('/public/satisortakligipanel/assets/css/argon.css?v=2.5')}}" type="text/css"/>

  <style type="text/css">
    html{
      overflow-y: scroll;
    }
    pre {
display: block;
padding: 9.5px;
margin: 0 0 10px;
font-size: 13px;
line-height: 1.42857143;
color: #333;
word-break: break-all;
word-wrap: break-word;
background-color: #F5F5F5;
border: 1px solid #CCC;
border-radius: 4px;
}

 
 
#a-footer {
  margin: 20px 0;
}

.new-react-version {
  padding: 20px 20px;
  border: 1px solid #eee;
  border-radius: 20px;
  box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
  
  text-align: center;
  font-size: 14px;
  line-height: 1.7;
}

.new-react-version .react-svg-logo {
  text-align: center;
  max-width: 60px;
  margin: 20px auto;
  margin-top: 0;

}
 
.success-box {
  margin:50px 0;
  padding:10px 10px;
  border:1px solid #eee;
  background:#f9f9f9;
  float: left;
  width: 100%;
}

.success-box img {
  margin-right:10px;
  display:inline-block;
  vertical-align:top;
}

.success-box > div {
  vertical-align:top;
  display:inline-block;
  color:#888;
}



/* Rating Star Widgets Style */
.rating-stars ul {
  list-style-type:none;
  padding:0;
  float: left;
  -moz-user-select:none;
  -webkit-user-select:none;
}
.rating-stars ul > li.star {
  display:inline-block;
  
}

/* Idle State of the stars */
.rating-stars ul > li.star > i.fa {
    /* Change the size of the stars */
  color:#ccc; /* Color on idle state */
}

/* Hover state of the stars */
.rating-stars ul > li.star.hover > i.fa {
  color:#FFCC36;
}

/* Selected state of the stars */
.rating-stars ul > li.star.selected > i.fa {
  color:#FF912C;
}
    
    #preloader {
    display: none;
    position:fixed;
    width: 100%;
    height: 100%;
    background-color:black; /* sayfa yüklenirken gösterilen arkaplan rengimiz */
    z-index:999999; /* efektin arkada kalmadığından emin oluyoruz */
    opacity: 0.3;
}
 
#loaderstatus {
    
    width:200px;
    height:200px;
    position:absolute;
    left:50%;
    top:50%;
    background-image:url('/public/satisortakligipanel/assets/img/loader.gif'); /* burası yazının ilk başında bahsettiğimiz animasyonu çağırır */
    background-repeat:no-repeat;
    background-position:center;
    margin:-100px   -100px;
}
   
@media screen and (max-width: 767px) {

    div.dataTables_wrapper,
    div.dataTables_wrapper div.dataTables_filter,
    div.dataTables_wrapper div.dataTables_info,
    div.dataTables_wrapper div.dataTables_paginate {
        text-align: center !important;
    }
    .truncate-option {
    white-space: nowrap;  /* Prevents text from wrapping */
    overflow: hidden;     /* Hides any overflow text */
    text-overflow: ellipsis;  /* Adds '...' when text overflows */
  }
}
@media screen and (max-width: 767px) {

    div.dataTables_length
 {
        display: none !important;
    }
}
.tab-content > .active {
    display: block;
    margin-top: 30px !important;
}
@if($pageindex==2)

.selectf2.select2-container {
    width:  !important;
}

@endif

  </style>
   @if($pageindex==13)
   

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.8.335/pdf.min.js"></script>
    <style type="text/css">
      
       #pdf-canvas {
            width: 100%;
            max-width: 100%;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

        .navigation-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
            width: 100%;
        }

        

        .navigation-buttons button:hover {
            background-color: #0056b3;
        }

        #page-num {
            font-size: 16px;
            margin-top: 10px;
        }
        .pdfloader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }

         
    </style>

    @endif


</head>
 
 
<body>

  <input type="hidden" name="pasifortakid" id="pasifortakid" value="{{($pageindex==12) ? $pasifortakid :''}}">
  <div id="preloader">
            <div id="loaderstatus">&nbsp;</div>
      </div>
  <!-- Sidenav -->
  <nav class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
      <!-- Brand -->
      <div class="sidenav-header d-flex align-items-center">
        <a class="navbar-brand" href="#">
          <img src="{{secure_asset('public/yeni_panel/vendors/images/randevumcepte.png')}}" style="max-height:2.5rem" class="navbar-brand-img" alt="">
        </a>
        <div class="ml-auto">
          <!-- Sidenav toggler -->
          <div class="sidenav-toggler d-none d-xl-block" data-action="sidenav-unpin" data-target="#sidenav-main">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="navbar-inner">
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <!-- Nav items -->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi" role="button" aria-expanded="true" aria-controls="navbar-dashboards">
                <i class="ni ni-shop text-primary"></i>
                <span class="nav-link-text">Anasayfa</span>
              </a>
               
            </li>
            
            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/yeni-musteri">
                <i class="ni ni-single-02 text-info"></i>
                <span class="nav-link-text">Yeni Müşteri Girişi</span>
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/pasif-musteriler">
                <i class="ni ni-single-02 text-primary"></i>
                <span class="nav-link-text">Pasif Müşteriler</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/demosu-olan-musteriler">
                <i class="ni ni-single-02 text-warning"></i>
                <span class="nav-link-text">Demosu Olan Müşteriler</span>
              </a>
            </li>
              <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/satis-yapilamayan-musteriler">
                <i class="ni ni-single-02 text-danger"></i>
                <span class="nav-link-text">Satış Yapılamayan Müşteriler</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/aktif-musteriler">
                <i class="ni ni-single-02 text-success"></i>
                <span class="nav-link-text">Aktif Müşteriler</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/pasif-ortaklar">
                <i class="ni ni-single-02 text-info"></i>
                <span class="nav-link-text">Pasif Ortaklar</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/satisortakligi/odeme-talepleri">
                <i class="ni ni-money-coins text-warning"></i>
                <span class="nav-link-text">Ödeme Talepleri</span>
              </a>
            </li>
            <li class="nav-item" >
              <a class="nav-link" href="/satisortakligi/gecmis-odemeler">
                <i class="ni ni-money-coins text-default"></i>
                <span class="nav-link-text">Geçmiş Ödemelerim</span>
              </a>
           
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#pazarlama-materyalleri" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="navbar-tables">
               <i class="ni ni-folder-17 text-success"></i>
                <span class="nav-link-text">Pazaralam Materyalleri</span>
              </a>
              <div class="collapse" id="pazarlama-materyalleri">
                <ul class="nav nav-sm flex-column">
                  <li class="nav-item">
                    <a href="/satisortakligi/one-cikan-ozellikler" class="nav-link">RandevumCepte Öne Çıkan Özellikleri</a>
                  </li>
                   <li class="nav-item">
                    <a href="/satisortakligi/sunulanlar" class="nav-link">Satış Ortaklarına Sunduklarımız</a>
                  </li>
                  <li class="nav-item">
                    <a href="/satisortakligi/basarili-satis" class="nav-link">Başarılı Satış Nasıl Olur</a>
                  </li>
                  <li class="nav-item">
                    <a href="/satisortakligi/satis-sunumu" class="nav-link">Satış Sunumu</a>
                  </li>
                  <li class="nav-item">
                    <a href="/satisortakligi/satis-artirici-ozelllikler" class="nav-link">Satış Artırıcı Özellikler</a>
                  </li>
                    <li class="nav-item">
                    <a href="/satisortakligi/materyalleri-indir" class="nav-link">Tüm Materyalleri İndir</a>
                  </li>
                 
                </ul>
              </div>
            </li>
            <li class="nav-item"  >
            <a class="nav-link" href="/satisortakligi/reklam-kurallari" >
                <i class="ni ni-single-copy-04 text-danger"></i>
                <span class="nav-link-text">Reklam Kuralları</span>
              </a>
            </li>
            
             <li class="nav-item"  >
            <a class="nav-link"  href="/isletmeyonetim">
                <i class="ni ni-shop text-success"></i>
                <span class="nav-link-text">Demo Hesaba Geçiş Yap</span>
              </a>
           
              
            </li>
             
           
          </ul>
          <!-- Divider -->
          <hr style="display: none;" class="my-3">
          <!-- Heading -->
          <h6 style="display: none;" class="navbar-heading p-0 text-muted">Documentation</h6>
          <!-- Navigation -->
          <ul class="navbar-nav mb-md-3" style="display: none;">
            <li class="nav-item">
              <a class="nav-link" href="https://demos.creative-tim.com/argon-dashboard/docs/getting-started/overview.html" target="_blank">
                <i class="ni ni-spaceship"></i>
                <span class="nav-link-text">Getting started</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://demos.creative-tim.com/argon-dashboard/docs/foundation/colors.html" target="_blank">
                <i class="ni ni-palette"></i>
                <span class="nav-link-text">Foundation</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://demos.creative-tim.com/argon-dashboard/docs/components/alerts.html" target="_blank">
                <i class="ni ni-ui-04"></i>
                <span class="nav-link-text">Components</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://demos.creative-tim.com/argon-dashboard/docs/plugins/charts.html" target="_blank">
                <i class="ni ni-chart-pie-35"></i>
                <span class="nav-link-text">Plugins</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <!-- Main content -->
  <div class="main-content" id="panel">
    <!-- Topnav -->
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-gradient-default border-bottom ">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          
          <!-- Navbar links -->
          <ul class="navbar-nav align-items-center ml-md-auto">
            <li class="nav-item d-xl-none">
              <!-- Sidenav toggler -->
              <div class="pr-3 sidenav-toggler sidenav-toggler-light" data-action="sidenav-pin" data-target="#sidenav-main">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </div>
            </li>
         
            <li class="nav-item dropdown" style="display: none">
              <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ni ni-bell-55"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-xl dropdown-menu-right py-0 overflow-hidden">
                <!-- Dropdown header -->
                <div class="px-3 py-3">
                  <h6 class="text-sm text-muted m-0">You have <strong class="text-primary">13</strong> notifications.</h6>
                </div>
                <!-- List group -->
                <div class="list-group list-group-flush">
                  <a href="#!" class="list-group-item list-group-item-action">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <!-- Avatar -->
                        <img alt="Image placeholder" src="{{secure_asset('public/satisortakligipanel/assets/img/theme/team-1.jpg')}}" class="avatar rounded-circle">
                      </div>
                      <div class="col ml--2">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <h4 class="mb-0 text-sm">John Snow</h4>
                          </div>
                          <div class="text-right text-muted">
                            <small>2 hrs ago</small>
                          </div>
                        </div>
                        <p class="text-sm mb-0">Let's meet at Starbucks at 11:30. Wdyt?</p>
                      </div>
                    </div>
                  </a>
                  <a href="#!" class="list-group-item list-group-item-action">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <!-- Avatar -->
                        <img alt="Image placeholder" src="{{secure_asset('public/satisortakligipanel/assets/img/theme/team-2.jpg')}}" class="avatar rounded-circle">
                      </div>
                      <div class="col ml--2">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <h4 class="mb-0 text-sm">John Snow</h4>
                          </div>
                          <div class="text-right text-muted">
                            <small>3 hrs ago</small>
                          </div>
                        </div>
                        <p class="text-sm mb-0">A new issue has been reported for Argon.</p>
                      </div>
                    </div>
                  </a>
                  <a href="#!" class="list-group-item list-group-item-action">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <!-- Avatar -->
                        <img alt="Image placeholder" src="{{secure_asset('public/satisortakligipanel/assets/img/theme/team-3.jpg')}}" class="avatar rounded-circle">
                      </div>
                      <div class="col ml--2">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <h4 class="mb-0 text-sm">John Snow</h4>
                          </div>
                          <div class="text-right text-muted">
                            <small>5 hrs ago</small>
                          </div>
                        </div>
                        <p class="text-sm mb-0">Your posts have been liked a lot.</p>
                      </div>
                    </div>
                  </a>
                  <a href="#!" class="list-group-item list-group-item-action">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <!-- Avatar -->
                        <img alt="Image placeholder" src="{{secure_asset('public/satisortakligipanel/assets/img/theme/team-4.jpg')}}" class="avatar rounded-circle">
                      </div>
                      <div class="col ml--2">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <h4 class="mb-0 text-sm">John Snow</h4>
                          </div>
                          <div class="text-right text-muted">
                            <small>2 hrs ago</small>
                          </div>
                        </div>
                        <p class="text-sm mb-0">Let's meet at Starbucks at 11:30. Wdyt?</p>
                      </div>
                    </div>
                  </a>
                  <a href="#!" class="list-group-item list-group-item-action">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <!-- Avatar -->
                        <img alt="Image placeholder" src="{{secure_asset('public/satisortakligipanel/assets/img/theme/team-5.jpg')}}" class="avatar rounded-circle">
                      </div>
                      <div class="col ml--2">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <h4 class="mb-0 text-sm">John Snow</h4>
                          </div>
                          <div class="text-right text-muted">
                            <small>3 hrs ago</small>
                          </div>
                        </div>
                        <p class="text-sm mb-0">A new issue has been reported for Argon.</p>
                      </div>
                    </div>
                  </a>
                </div>
                <!-- View all -->
                <a href="#!" class="dropdown-item text-center text-primary font-weight-bold py-3">View all</a>
              </div>
            </li>
            <li class="nav-item dropdown" style="display: none;">
              <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ni ni-ungroup"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-dark bg-default dropdown-menu-right">
                <div class="row shortcuts px-4">
                  <a href="#!" class="col-4 shortcut-item">
                    <span class="shortcut-media avatar rounded-circle bg-gradient-red">
                      <i class="ni ni-calendar-grid-58"></i>
                    </span>
                    <small>Calendar</small>
                  </a>
                  <a href="#!" class="col-4 shortcut-item">
                    <span class="shortcut-media avatar rounded-circle bg-gradient-orange">
                      <i class="ni ni-email-83"></i>
                    </span>
                    <small>Email</small>
                  </a>
                  <a href="#!" class="col-4 shortcut-item">
                    <span class="shortcut-media avatar rounded-circle bg-gradient-info">
                      <i class="ni ni-credit-card"></i>
                    </span>
                    <small>Payments</small>
                  </a>
                  <a href="#!" class="col-4 shortcut-item">
                    <span class="shortcut-media avatar rounded-circle bg-gradient-green">
                      <i class="ni ni-books"></i>
                    </span>
                    <small>Reports</small>
                  </a>
                  <a href="#!" class="col-4 shortcut-item">
                    <span class="shortcut-media avatar rounded-circle bg-gradient-purple">
                      <i class="ni ni-pin-3"></i>
                    </span>
                    <small>Maps</small>
                  </a>
                  <a href="#!" class="col-4 shortcut-item">
                    <span class="shortcut-media avatar rounded-circle bg-gradient-yellow">
                      <i class="ni ni-basket"></i>
                    </span>
                    <small>Shop</small>
                  </a>
                </div>
              </div>
            </li>
          </ul>
          <ul class="navbar-nav align-items-center ml-auto ml-md-0">
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                   <span class="avatar avatar-sm rounded-circle">
                  
                     <img alt="RandevumCepte Satış Ortaklığı Yönetim Paneli" src="{{Auth::user()->profil_resmi}}">
                   
                    
                  </span>
                  <div class="media-body ml-2 d-none d-lg-block">
                   
                   <span class="mb-0 text-sm  font-weight-bold">  {{Auth::user()->ad_soyad}}   </span> 
                  </div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                
                <a href="/satisortakligi/hesap-ayarlari/" class="dropdown-item">
                  <i class="ni ni-settings-gear-65"></i>
                  <span>Hesap Ayarları</span>
                </a>
                <a href="/satisortakligi/sifre-ayarlari/" class="dropdown-item">
                  <i class="ni ni-settings-gear-65"></i>
                  <span>Şifre Değiştir</span>
                </a>
                 <a style="display: none;" href="/satisortakligi/sikayet-memnuniyet-bildir" class="dropdown-item">
                  <i class="ni ni-support-16"></i>
                  <span>Şikayet & Memnuniyet Bildir</span>
                </a>
                
                <div class="dropdown-divider"></div>
                <a href="/satisortakligi/cikis-yap" class="dropdown-item">
                  <i class="ni ni-user-run"></i>
                  <span>Çıkış Yap</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- Header -->
    <!-- Header -->
      @yield('content')
      <div id="hata"></div>
    <!-- Footer -->
      <footer class="footer pt-0  order-xl-4" style="display:none">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-12">
            <div class="copyright text-center text-lg-left text-muted">
              &copy; 2024 <a href="https://randevumcepte.com.tr" class="font-weight-bold ml-1" target="_blank">RandevumCepte</a>
            </div>
          </div>
          
        </div>
      </footer>
  </div>
  <div class ="modal fade" id="musteri-bilgi-formu" tabindex="-1" role="dialog" aria-labelledby="musteri-bilgi-formu">
    <div class="modal-dialog modal- modal-dialog-centered modal-" role="document" style="width:80%; max-width: 80%">
      <div class="modal-content">
          <div class="modal-header bg-success">
               <h6 class="modal-title text-white" id="modal-title-default">Müşteri Düzenleme</h6>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true" class="text-white">×</span>
               </button>
            </div>
            <div class="modal-body">
                 <form id="musteri_duzenle" method="POST">
                 @csrf
               <input type="hidden" name="salon_id" id="salon_id" value="">
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="yetkili_adi">Yetkili Ad soyad (Zorunlu)</label>
                        <input type="text" id="yetkili_adi_duzenleme" required name="yetkili_adi" class="form-control">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="yetkili_telefon">Cep Telefonu (Zorunlu, başında 0 olmadan 5 ile başlayarak girilmelidir.)</label>
                        <input type="tel" id="yetkili_telefon_duzenleme" data-inputmask =" 'mask' : '5999999999'" required name="yetkili_telefon" class="form-control">
                      </div>
                    </div>
                
                  </div>
                  
                </div>
                 
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="salon_adi">İşletme Adı(Zorunlu)</label>
                        <input id="salon_adi_duzenleme" name="salon_adi" required class="form-control" type="text">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefon_1">Firma Telefon 1 (Opsiyonel)</label>
                        <input id="telefon_1_duzenleme" name="telefon_1" data-inputmask =" 'mask' : '09999999999'" class="form-control" type="tel">
                      </div>
                    </div>
                     <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefon_2">Firma Telefon 2 (Opsiyonel)</label>
                        <input id="telefon_2_duzenleme" name="telefon_2" data-inputmask =" 'mask' : '09999999999'" class="form-control" type="tel">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefon_2">Firma Telefon 3 (Opsiyonel)</label>
                        <input id="telefon_3_duzenleme" name="telefon_3" data-inputmask =" 'mask' : '09999999999'" class="form-control" type="tel">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-control-label" for="adres">Adres (Zorunlu)</label>
                        <textarea id="adres_duzenleme" name="adres" required class="form-control"></textarea>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-control-label" for="il_id_musteri_duzenleme">İl (Zorunlu)</label>
                        
                         <select  data-toggle="select" id="il_id_musteri_duzenleme" name="il_id_yeni_musteri" class="form-control">
                          <option value="0">Seçiniz</option>
                          @foreach(\App\Iller::all() as $il)

                          <option value="{{$il->id}}">{{$il->il_adi}}</option>
                          @endforeach
                         </select>
                      </div>
                       
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-control-label" for="ilce_id_musteri_duzenleme">İlçe (Zorunlu)</label>
                         <select  data-toggle="select" id="ilce_id_musteri_duzenleme" name="ilce_id_yeni_musteri" class="form-control">
                           
                         </select>
                      </div>
                    </div>
                     <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-control-label" for="pasif_ortak">Pasif Ortak</label>
                         <select  data-toggle="select" id="pasif_ortak_duzenleme" name="pasif_ortak" class="form-control">
                           <option value="0">Seçiniz</option>
                          @foreach(\App\SatisOrtakligiModel\SatisOrtaklari::where('ana_satis_ortagi_id',Auth::user()->id)->where('pasif_ortak',true)->where('aktif',true)->get() as $ortak)

                          <option value="{{$ortak->id}}">{{$ortak->ad_soyad}}</option>
                          @endforeach
                         </select>
                      </div>
                    </div>
                   <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-control-label" for="satis_ortagi_notu_duzenleme">Notlar (Opsiyonel)</label>
                        <textarea id="satis_ortagi_notu_duzenleme" name="satis_ortagi_notu" class="form-control" rows="10"></textarea> 
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="my-4" />

                <button type="submit" style="width:100%;" class="btn btn-success">Bilgileri Güncelle</button> 
              </form>   
            </div>
      </div>
    </div>

  </div>
  <div class="modal fade" id="satis-formu" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-" role="document" style="width:80%; max-width: 80%">
         <div class="modal-content">
            <div class="modal-header bg-success">
               <h6 class="modal-title text-white" id="modal-title-default">Yeni Satış</h6>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true" class="text-white">×</span>
               </button>
            </div>
            <div class="modal-body">
               <form id="musteri-leads" method="POST">
                  @csrf
                  <input type="hidden" name="form_islemleri_musteri_id" id="form_islemleri_musteri_id" class="form-control"> 
                  <input type="hidden" name="form_islemleri_form_id" id="form_islemleri_form_id" class="form-control"> 
                  <input type="hidden" name="pasifortakid" value="{{($pageindex==12) ? $pasifortakid :''}}">
                  <div class="row">
                     <div class="col-md-6">
                        <h3>Firma ve Fatura Bilgileri</h3>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group"><label>Tarih</label> <input type="date" name="form_islemleri_tarih" value="{{date('Y-m-d')}}" class="form-control"></div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group"><label>İşletme Adı</label> <input type="text" name="form_islemleri_isletme_adi" required id="form_islemleri_isletme_adi" class="form-control"></div>
                        <div class="form-group"><label>Yetkili Adı</label> <input type="text" name="form_islemleri_yetkili_kisi" required id="form_islemleri_yetkili_kisi" class="form-control"></div>

                         
                       
                     </div>
                     <div class="col-md-6">
                        <div class="form-group"><label>Yetkili Telefon</label> <input type="text" name="form_islemleri_yetkili_telefon" required id="form_islemleri_yetkili_telefon" class="form-control"></div>
                        <div class="form-group"><label>Yetkili E-Posta</label> <input type="text" required name="form_islemleri_email" id="form_islemleri_email" class="form-control"></div>
                     </div>
                     <div class="col-md-6">
                      <div class="form-group"><label>Firma Unvanı</label> <input type="text" name="form_islemleri_firma_unvani" required id="form_islemleri_firma_unvani" class="form-control"></div>
                      <div class="form-group"><label>İşletme Telefon 1</label> <input type="text" name="form_islemleri_gsm1" id="form_islemleri_gsm1" class="form-control"></div>
                      <div class="form-group"><label>İşletme Telefon 2</label> <input type="text" name="form_islemleri_gsm2" id="form_islemleri_gsm2" class="form-control"></div>
                      <div class="form-group"><label>İşletme Telefon 3</label> <input type="text" name="form_islemleri_gsm3" id="form_islemleri_gsm3" class="form-control"></div>
                      <div class="form-group"><label>Adres</label> <input type="text" name="form_islemleri_adres" required id="form_islemleri_adres" class="form-control"></div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group"><label>Vergi Dairesi</label> <input type="text" name="form_islemleri_vergi_dairesi" required id="form_islemleri_vergi_dairesi" class="form-control"></div>
                      <div class="form-group"><label>Vergi / TC No</label> <input type="text" name="form_islemleri_vergi_tc_no" required id="form_islemleri_vergi_tc_no" class="form-control"></div>
                      <div class="form-group">
                        <label>İl</label>
                         <select  data-toggle="select" id="il_id" name="il_id" class="form-control">
                          <option value="0">Seçiniz</option>
                          @foreach(\App\Iller::all() as $il)
                          <option value="{{$il->id}}">{{$il->il_adi}}</option>
                          @endforeach
                         </select>
                      </div>
                      <div class="form-group">
                        <label>İlçe</label>
                        <select  data-toggle="select" id="ilce_id" name="ilce_id" class="form-control">
                           
                         </select>
                      </div>
                    </div>
                  </div>
                  <div class="card" >
                     <div class="card-header">
                        <h3>Üyelik Bilgileri</h3>
                        <div class="row" class="form-group">
                           <div class="col-sm-6 col-md-6" >
                              <label><!--Satmak istediğiniz paket-->Verilecek Hizmet</label>
                              
                              <select  style="font-size: 12px;" data-toggle="select" id="form_hizmet" name="form_hizmet" >
                                 @foreach(\App\Uyelik::all() as $hizmet)
                                  @if($hizmet->id != 3 && $hizmet->interaktif_iletisim != true)
                                 <option style="font-size: 12px;" value="{{$hizmet->id}}-aylik">{{$hizmet->uyelik_adi}} Aylık - {{number_format($hizmet->aylik_tutar,2,',','.')}} ₺ (KDV DAHİL)</option>
                                  @endif
                                 @endforeach
                                 @foreach(\App\Uyelik::all() as $hizmet)
                                
                                 <option style="font-size: 12px;" value="{{$hizmet->id}}-yillik">{{$hizmet->uyelik_adi}} Yıllık - {{number_format($hizmet->yillik_tutar,2,',','.')}} ₺ (KDV DAHİL)</option>
                                
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-sm-6 col-md-6">
                                <label>Ödeme Türü</label>
                                <select  data-toggle="select" id="satis_odeme_turu" name="satis_odeme_turu" class="form-control">
                                  <option value="1">Kredi / Banka Kartı</option>
                                  <option value="2">Banka Havalesi / EFT</option>
                                </select>
                           </div>
                           
                        </div>
                     </div>
                      
                  </div>
                  <div class="row">
                  </div>
                  <div class="row">
                     <div class="form-group col-md-12" style="text-align: center;">
                        <button type="submit" class="btn btn-success">Formu Kaydet</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="demo_onay" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
         <div class="modal-content">
            <div class="modal-header bg-success">
               <h6 class="modal-title text-white" id="modal-title-default">Demo Hesabı Aç</h6>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true" class="text-white">×</span>
               </button>
            </div>
            <div class="modal-body">
               <form id="musteri-leads" method="POST">
                  @csrf
                  <input type="hidden" name="onaykodu_musteri_id" id="onaykodu_musteri_id" class="form-control"> 
                  <input type="hidden" name="onaykodu_form_id" id="onaykodu_form_id" class="form-control"> 
                  
                 
                        <div class="form-group"><label>Onay Kodu</label> <input type="text" name="onay_kodu_demo" required id="onay_kodu_demo" class="form-control"></div>
                       
             
              
                  </div>
                  <div class="row">
                     <div class="form-group col-md-12" style="text-align: center;">
                        <button type="submit" class="btn btn-success">Onayla</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
   
 <div class="modal fade" id="modal-default" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true" >
      <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h6 class="modal-title" id="modal-title-default">ÖDEME TALEBİ</h6>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span>
               </button>
            </div>
            <form id="odeme_talep_et_formu" enctype='multipart/form-data'  method="POST">
               @csrf
               <div class="modal-body">
                  <div class="card card-pricing bg-success border-0 text-center mb-4" style="padding:0">
                     <div class="card-header bg-transparent" style="padding: 0">
                        <h4 class="text-uppercase ls-1 text-white py-3 mb-0" style="padding: 0">TALEP EDEBİLECEĞİNİZ HAKEDİŞ TUTARINIZ</h4>
                     </div>
                     <div class="card-body px-lg-7" style="padding: 0">
                        <input type="hidden" name="hakedis_miktari" id="hakedis_miktari">
                        <div class="display-2 text-white" id="hakedis_miktari_text"></div>
                     </div>
                  </div>
                  <div class="alert alert-info fade show" role="alert">
                     <span class="alert-icon"><i class="fa fa-info"></i></span>
                     <span class="alert-text"><strong>Bilgi Notu!</strong> Hakedişinizi hesabınıza transfer edebilmemiz için lütfen komisyon faturanızı ekleyiniz.</span>
                  </div>
                  <div class="form-group">
                     <label>Fatura ekleyin</label>
                     <input type="file" id="komisyon_fatura_gider_pusulasi" required name="komisyon_fatura_gider_pusulasi" class="form-control">
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="submit" class="btn btn-primary"><i class="ni ni-send"></i>Ödeme Talebi Gönder</button>
                  <button type="button" class="btn btn-danger  ml-auto" data-dismiss="modal"><i class="fa fa-times-circle"></i>Kapat</button>
               </div>
            </form>
         </div>
      </div>
   </div>
  <!-- Argon Scripts -->
  <!-- Core -->
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/js-cookie/js.cookie.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js')}}"></script>
  <!-- Optional JS -->
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/chart.js/dist/Chart.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/chart.js/dist/Chart.extension.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/jvectormap-next/jquery-jvectormap.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/js/vendor/jvectormap/jquery-jvectormap-world-mill.js')}}"></script>

  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/select2/dist/js/select2.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/nouislider/distribute/nouislider.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/quill/dist/quill.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/dropzone/dist/min/dropzone.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js')}}"></script>
   <!-- Datatable JS -->
  <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/jquery.dataTables.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/dataTables.bootstrap4.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/dataTables.responsive.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/responsive.bootstrap4.min.js')}}"></script>
      <!-- buttons for Export datatable -->
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/dataTables.buttons.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.bootstrap4.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.print.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.html5.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.flash.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/pdfmake.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/vfs_fonts.js')}}"></script>
      <!-- Datatable Setting js -->
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/datatable-setting.js')}}"></script>



  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/sweetalert2/dist/sweetalert2.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/bootstrap-notify/bootstrap-notify.min.js')}}"></script>
  <!-- Argon JS -->
    <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/moment/min/moment.min.js')}}"></script>
   <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/fullcalendar/dist/fullcalendar.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/vendor/fullcalendar/dist/locale-all.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/js/argon.js')}}"></script>
  <!-- Demo JS - remove this in your project -->
  <script src="{{secure_asset('public/satisortakligipanel/assets/js/demo.min.js')}}"></script>
  <script src="{{secure_asset('public/satisortakligipanel/assets/js/custom.js?v=16.1')}}"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/3.4.0/imask.min.js"></script>

  <script src="{{secure_asset('public/satisortakligipanel/assets/js/credit-card-form.js')}}"></script>
  <script src="{{secure_asset('/public/js/dist/inputmask.min.js')}}"></script> 
      <script src="{{secure_asset('/public/js/dist/jquery.inputmask.min.js')}}"></script> 

      <script src="{{secure_asset('/public/js/dist/bindings/inputmask.binding.js')}}"></script>
      @if($pageindex==11)
        <script type="text/javascript">
    $(document).ready(function(){
      $('#pasif_ortaklar').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "asc" ]],
                            columns:[
                                 { data: 'adsoyad'   },
                                 { data: 'telefon' },
                                 { data: 'email'   },
                                 { data: 'satisyuzde'   },
                                  { data: 'islemler'   },
                                 
                            ],
                            data:  <?php echo  $pasif_ortaklar; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
      @endif
      @if($pageindex==6)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#gecmis_odemeler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'tarih'   },
                                 { data: 'tutar' },
                                 { data: 'banka'   },
                                 
                            ],
                            data:  <?php echo  $gecmis_odemeler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
     @if($pageindex==5)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#hakedis_talepleri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'tarih'   },
                                 { data: 'tutar' },
                                 { data: 'durum'   },
                                 
                            ],
                            data:  <?php echo  $talepler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
       @if($pageindex==8)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#satis_ortagi_banka_bilgileri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "asc" ]],
                            columns:[ 
                               
                                 { data: 'banka' },
                                 { data: 'iban'   },
                                  { data: 'sube_kodu' },
                                  { data: 'hesap_no' },
                                  { data: 'alici'   },
                              
                                   { data: 'islemler' },
                            ],
                            data:  <?php echo  $bayi_banka_hesaplari; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
  @if($pageindex== 12)
  <script type="text/javascript">
    $(document).ready(function(){
      $('#tum_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                     { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                  { data: 'durum'   },
                                   { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  <?php echo  $tum_musteriler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
  @if($pageindex==3 || $pageindex==12)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#pasif_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                     { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                    { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  <?php echo  $pasif_musteriler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
   @if($pageindex==7 || $pageindex==12)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#demosu_olan_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                     { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                    { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  <?php echo  $demosu_olan_musteriler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
   @if($pageindex==10 || $pageindex==12)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#satis_yapilamayan_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                     { data: 'yetkili_telefon'   },
                                  { data: 'created_at'   },
                                    { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  <?php echo  $satis_yapilamayan_musteriler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
  @if($pageindex==4 || $pageindex==1 || $pageindex==12)   
  <script type="text/javascript">
    $(document).ready(function(){
      $('#aktif_musteriler').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                { data: 'salon_id'   },
                                { data: 'salon_adi' },
                                { data: 'yetkili_bilgisi'   },
                                { data: 'yetkili_telefon'   },
                                { data: 'satilan_paket'   },
                                { data: 'kalan_sure'   },
                                { data: 'islemler' } 
                            ],
                            data:  <?php echo  $aktif_musteriler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                   searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '',
                                      previous: ''
                                  }
                            },
      });
    });
  </script>
  @endif
  @if($pageindex==1)
  <script type="text/javascript">
    $(document).ready(function(){
      $('#demomusterileri').DataTable({
                            autoWidth: false,
                            responsive: true,
                            "order": [[ 0, "desc" ]],
                            columns:[
                                 { data: 'salon_id'   },
                                 { data: 'salon_adi' },
                                 { data: 'yetkili_bilgisi'   },
                                     { data: 'yetkili_telefon'   },
                                  { data: 'kalan_sure'   },
                                    { data: 'notlar'   },
                                 { data: 'islemler' } 
                            ],
                            data:  <?php echo  $demosu_olan_musteriler; ?>,
                            "language" : {
                                  "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                                  searchPlaceholder: "Ara",
                                  paginate: {
                                      next: '<i class="ion-chevron-right"></i>',
                                      previous: '<i class="ion-chevron-left"></i>'
                                  }
                            },
      });
    });
  </script>
  @endif
   


 
</body>

</html>