<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
     
    <title>{{$title}}</title>
    <link rel="shortcut icon" href="{{asset('public/img/logoicon.png')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/perfect-scrollbar/css/perfect-scrollbar.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/material-design-icons/css/material-design-iconic-font.min.css')}}"/><!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/jquery.vectormap/jquery-jvectormap-1.2.2.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/jqvmap/jqvmap.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>
    
   <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/jquery.fullcalendar/fullcalendar.min.css')}}"/>
     <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/datatables/css/dataTables.bootstrap.min.css')}}"/>
     <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/jquery.magnific-popup/magnific-popup.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/summernote/summernote.css')}}"/>
     <link rel="stylesheet" type="text/css" href="{{asset('public/isletmeyonetim_assets/lib/select2/css/select2.min.css')}}"/>
     <link rel="stylesheet" href="{{asset('public/isletmeyonetim_assets/css/style.css')}}" type="text/css"/>
     
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  </head>
  <body>
       <div id="preloader2">
            <div id="loaderstatus2">&nbsp;</div>
      </div>
    <div class="be-wrapper be-fixed-sidebar">
      <nav class="navbar navbar-default navbar-fixed-top be-top-header">
        <div class="container-fluid">
          <div class="navbar-header"><a href="/isletmeyonetim" class="navbar-brand" style="margin:10px 0 0 -20px"> <img src="{{asset('public/img/avantajbu.png')}}" width="230" height="50" alt="Avantajbu.com"/></a></div>
          <div class="be-right-navbar">

            <ul class="nav navbar-nav navbar-right be-user-nav">
              <li class="dropdown" style="position: absolute;right: 30px">GÖZDE ÖLMEZ MAKEUP STUDİO VE GÜZELLİK SALONU

 Yönetim Paneli</li>
              <li class="dropdown" style="margin-top: 10px"><a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
             
                <img src="{{asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar"><span class="user-name">Gözde Ölmez</span>
                
              </a>
                <ul role="menu" class="dropdown-menu">
                  <li>
                    <div class="user-info">
                      <div class="user-name">Gözde Ölmez</div>
                       
                    </div>
                  </li>
                  <li><a href="#"><span class="icon mdi mdi-face"></span>Hesap Bilgileri</a></li>
                     <li><a href="#"><span class="icon mdi mdi-face"></span>Şifre Değiştir</a></li>
                  <li><a href="#"><span class="icon mdi mdi-power"></span>Çıkış Yap</a></li>
                </ul>
              </li>
            </ul>
            <div class="page-title"><span> @if($pageindex==1001) SMS Paketi Satın Alma
              @elseif($pageindex==1002) Yeni Kampanya Yayınlama 
              @elseif($pageindex==1003) Google Seo Paketleri @endif
            </span></div>
            <ul class="nav navbar-nav navbar-right be-icons-nav">
              
              <li class="dropdown" style="margin-top: 10px"><a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle"><span class="icon mdi mdi-notifications"></span><span style="display: none" class="indicator"></span></a>
                <ul class="dropdown-menu be-notifications">
                  <li>
                    <div class="title">Bildirimler<span class="badge">0</span></div>
                    <div class="list">
                      <div class="be-scroller">
                        <div class="content">
                           
                        </div>
                      </div>
                    </div>
                    <div class="footer"></div>
                  </li>
                </ul>
              </li>
              
            </ul>
          </div>
        </div>
      </nav>
      <div class="be-left-sidebar">
        <div class="left-sidebar-wrapper"><a href="#" class="left-sidebar-toggle">MENÜ</a>
          <div class="left-sidebar-spacer">
            <div class="left-sidebar-scroll">
              <div class="left-sidebar-content">
                <ul class="sidebar-elements">
                  <li class="divider"> 
                         <div class="user-display-avatar" style="background-image: url('{{asset('public/isletmeyonetim_assets/img/avatar-150.png')}}');background-position: center; background-repeat: no-repeat;"> </div> 
                  </li>
                  
                 
                
                  <li><a href="#"><i class="icon mdi mdi-home"></i><span>Başlangıç</span></a></li>
                 
                 
                  <li><a href="#"><img class="icon" src="{{asset('public/img/appointmenticon.png')}}" width="20" height="20" alt="Randevular" /><span>Randevular</span></li>
                    
                    <li><a href="#"><img class="icon" src="{{asset('public/img/tlicon.png')}}" width="20" height="20" alt="Kasa Defteri" /><span>Kasa Defteri</span></a></li>
                    
                    <li><a href="#"><img class="icon" src="{{asset('public/img/store.png')}}" width="20" height="20" alt="İşletmem" /><span>İşletmem</span></a></li>
                   
                     
                     
                     <li><a href="#"><img class="icon" src="{{asset('public/img/customersicon.png')}}" width="20" height="20" alt="Müşteriler" /><span>Müşterilerim</span></a></li>
                     
                        <li><a href="#"><i class="icon mdi mdi-face"></i><span>Personellerim</span></a></li>
                      
                        
                     
                         
                       <li class="parent"><a href="#"><img class="icon" src="{{asset('public/img/campaign2active.png')}}" width="20" height="20" alt="Kampanyalar" /><span> Avantajlar</span></a>
                                <ul>
                                    <li> <a href="#"><i class="icon mdi mdi-notifications"></i> <span> Tüm Avantajlar</span></a></li>
                                 
                                     <li><a href="#"><i class="icon mdi mdi-notifications"></i> <span> Yapılan Ödemeler</span></a></li>
                                   
                                      <li> <a href="#"><i class="icon mdi mdi-format-list-bulleted"></i> <span> Raporlar</span></a></li>
                                   
                                </ul>
                            </li> 
                    <li><a href="#"><i class="icon mdi mdi-settings"></i><span>Ayarlar</span></a></li> 
                    <li class="parent"><a href="#"><i class="icon mdi mdi-email"></i><span>Toplu SMS</span></a>

                      <ul>
                          <li><a href="#"><span>Toplu SMS Gönder</span></a></li>
                         
                          <li><a href="#" ><span>SMS Paketlerim</span></a></li>
                          <li  class="active"> <a href="/smspaketleri"> <span>SMS Paketi Satın Al</span></a></li>
                            <li><a href="#"><span>Raporlarım</span></a></li>
                           
                      </ul>
                    </li>
                    
                  <li class="parent"><a href="#"><i class="icon mdi mdi-email"></i> <span>Toplu Mail</span></a> 
                      <ul>
                        <li><a href="#"><span>Toplu Mail Gönder</span></a></li>
                       
                        <li><a href="#"><span>Mail Paketlerim</span></a></li>
                         <li><a href="#"><span>Hazır Mail Taslaklarım</span></a></li>

                      </ul>

                  </li>
                   
                    <li><a target="_blank" href="#"></i><span>İşletmemi Sayfada Gör</span></a></li>
                     
                </ul>
              </div>
            </div>
          </div>
           
        </div>
      </div>
      <div class="be-content">
       @yield('content')
        </div>
      </div>
      
    </div>
  <script src="{{asset('public/isletmeyonetim_assets/lib/jquery/jquery.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/js/main.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/bootstrap/dist/js/bootstrap.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery-flot/jquery.flot.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery-flot/jquery.flot.pie.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery-flot/jquery.flot.resize.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery-flot/plugins/jquery.flot.orderBars.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery-flot/plugins/curvedLines.js')}}" type="text/javascript"></script>

    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery.sparkline/jquery.sparkline.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/countup/countUp.min.js')}}" type="text/javascript"></script>
    
    <script src="{{asset('public/isletmeyonetim_assets/lib/jqvmap/jquery.vmap.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jqvmap/maps/jquery.vmap.world.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/js/app-dashboard.js')}}" type="text/javascript"></script> 

    <script src="{{asset('public/isletmeyonetim_assets/lib/moment.js/min/moment.min.js')}}" type="text/javascript"></script>
     <script src="{{asset('public/isletmeyonetim_assets/lib/datetimepicker/js/bootstrap-datetimepicker.js')}}" type="text/javascript"></script>
   
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery-ui/jquery-ui.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/jquery.nestable/jquery.nestable.js')}}" type="text/javascript"></script>
        <script src="{{asset('public/isletmeyonetim_assets/lib/jquery.fullcalendar/fullcalendar.min.js')}}" type="text/javascript"></script>
          <script src="{{asset('public/isletmeyonetim_assets/lib/jquery.fullcalendar/lang-all.js')}}" type="text/javascript"></script>
            <script src="{{asset('public/isletmeyonetim_assets/js/app-page-calendar.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/js/jquery.dataTables.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/js/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/dataTables.buttons.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.html5.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.flash.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.print.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.colVis.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.bootstrap.js')}}" type="text/javascript"></script>
     <script src="{{asset('public/isletmeyonetim_assets/js/app-form-elements.js')}}" type="text/javascript"></script>
     <script src="{{asset('public/isletmeyonetim_assets/lib/select2/js/select2.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/js/app-tables-datatables.js')}}" type="text/javascript"></script>
      <script src="{{asset('public/isletmeyonetim_assets/lib/jquery.magnific-popup/jquery.magnific-popup.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/masonry/masonry.pkgd.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/js/app-page-gallery.js')}}" type="text/javascript"></script>
     <script src="{{asset('public/isletmeyonetim_assets/lib/jquery.niftymodals/dist/jquery.niftymodals.js')}}" type="text/javascript"></script>
    
     <script src="{{asset('public/isletmeyonetim_assets/lib/summernote/summernote.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/lib/summernote/summernote-ext-beagle.js')}}" type="text/javascript"></script>
    
    <script src="{{asset('public/isletmeyonetim_assets/js/app-mail-compose.js')}}" type="text/javascript"></script>
     
     <script src="{{asset('public/isletmeyonetim_assets/lib/chartjs/Chart.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/isletmeyonetim_assets/js/app-charts-chartjs.js')}}" type="text/javascript"></script>
   
    <script type='text/javascript' src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>
    
    <script src="{{asset('public/js/custom.js')}}" type="text/javascript"></script>
    
    
     <script type="text/javascript">
        $.fn.niftyModal('setDefaults',{
        overlaySelector: '.modal-overlay',
        closeSelector: '.modal-close',
        classAddAfterOpen: 'modal-show',
      });
      $(document).ready(function(){
        //initialize the javascript

        App.init(); 
          
         App.dataTables();
         App.formElements();
        App.mailCompose();
       App.dashboard();

         
      });
      $(window).on('load',function(){
        App.pageGallery();

         
        

      });
    </script>
    
  </body>
</html>