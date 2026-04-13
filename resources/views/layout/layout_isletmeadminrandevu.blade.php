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
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/perfect-scrollbar/css/perfect-scrollbar.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/material-design-icons/css/material-design-iconic-font.min.css')}}"/>
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.vectormap/jquery-jvectormap-1.2.2.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/jqvmap/jqvmap.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/datetimepicker/css/bootstrap-datetimepicker.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.fullcalendar/fullcalendar.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/css/dataTables.bootstrap.min.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.magnific-popup/magnific-popup.css')}}"/>
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/isletmeyonetim_assets/lib/select2/css/select2.min.css')}}"/>
      <link rel="stylesheet" href="{{secure_asset('public/css/selectize.css')}}" type="text/css">
      <link rel="stylesheet" href="{{secure_asset('public/isletmeyonetim_assets/css/style.css')}}" type="text/css"/>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
   </head>
   <body>
      <div id="preloader">
         <div id="loaderstatus">&nbsp;</div>
      </div>
      <div class="be-wrapper be-fixed-sidebar">
         <nav class="navbar navbar-default navbar-fixed-top be-top-header">
            <div class="container-fluid">
               <div class="navbar-header"></div>
               <div class="be-right-navbar">
                  <ul class="nav navbar-nav navbar-right be-user-nav">
                     <li class="dropdown" style="position: absolute;right: 30px">{{Auth::user()->salonlar->salon_adi}} Yönetim Paneli</li>
                     <li class="dropdown" style="margin-top: 10px">
                        <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
                        @if(\App\Salonlar::where('id',Auth::user()->salon_id)->value('logo') != null || \App\Salonlar::where('id',Auth::user()->salon_id)->value('logo') != '')
                        <img src="{{secure_asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar"><span class="user-name">{{Auth::user()->name}}</span>
                        @else
                        <img src="{{secure_asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar"><span class="user-name">{{Auth::user()->name}}</span>
                        @endif
                        </a>
                        <ul role="menu" class="dropdown-menu">
                           <li>
                              <div class="user-info">
                                 <div class="user-name">{{Auth::user()->name}}</div>
                              </div>
                           </li>
                           <li><a href="/isletmeyonetim/ayarlar"><span class="icon mdi mdi-face"></span>Hesap Bilgileri</a></li>
                           <li><a href="/isletmeyonetim/ayarlar"><span class="icon mdi mdi-face"></span>Şifre Değiştir</a></li>
                           <li><a href="/isletmeyonetim/cikisyap"><span class="icon mdi mdi-power"></span>Çıkış Yap</a></li>
                        </ul>
                     </li>
                  </ul>
                  <div class="page-title"><span>@if($pageindex == 0) Başlangıç @elseif($pageindex==102)Randevular @elseif($pageindex==101) İşletmem  @elseif($pageindex==103) Kasa Defteri @elseif($pageindex==104) Hesap Ayarları @elseif($pageindex==105) Kampanyalar @elseif($pageindex==106)Toplu SMS Gönder @elseif($pageindex==108) SMS Paketlerim @elseif($pageindex==107) Müşterilerim @elseif($pageindex==109) SMS Raporlarım @elseif($pageindex==111) Personellerim @endif</span></div>
                  <ul class="nav navbar-nav navbar-right be-icons-nav">
                     <li class="dropdown" style="margin-top: 10px">
                        <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle" style="display:none"><span class="icon mdi mdi-notifications"></span><span style="display: none" class="indicator"></span></a>
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
            <div class="left-sidebar-wrapper">
               <a href="#" class="left-sidebar-toggle">MENÜ</a>
               <div class="left-sidebar-spacer">
                  <div class="left-sidebar-scroll">
                     <div class="left-sidebar-content">
                        <ul class="sidebar-elements">
                           <li class="divider">
                              @if(\App\Salonlar::where('id',Auth::user()->salon_id)->value('logo') != null || \App\Salonlar::where('id',Auth::user()->salon_id)->value('logo') != '')
                              <div class="user-display-avatar" style="background-image: url('{{secure_asset($isletme->logo)}}');background-position: center; background-repeat: no-repeat; background-size: contain;"> </div>
                              @else
                              <div class="user-display-avatar" style="background-image: url('{{secure_asset('public/isletmeyonetim_assets/img/avatar-150.png')}}');background-position: center; background-repeat: no-repeat;"> </div>
                              @endif
                           </li>
                           @if($pageindex==0)
                           <li class="active"><a href="/isletmeyonetim"><i class="icon mdi mdi-home"></i><span>Başlangıç</span></a>
                           </li>
                           @else
                           <li><a href="/isletmeyonetim"><i class="icon mdi mdi-home"></i><span>Başlangıç</span></a></li>
                           @endif
                           @if(Auth::user()->salonlar->uyelik_turu == 1 ||Auth::user()->salonlar->uyelik_turu==3)
                           @if($pageindex==102)
                           <li class="active">
                              <a href="/isletmeyonetim/randevular">
                                 <img class="icon" src="{{secure_asset('public/img/appointmenticonactive.png')}}" width="20" height="20" alt="Randevular" /><span>Randevular</span>
                           </li>
                           @else
                           <li><a href="/isletmeyonetim/randevular"><img class="icon" src="{{secure_asset('public/img/appointmenticon.png')}}" width="20" height="20" alt="Randevular" /><span>Randevular</span></li>
                           @endif
                           @endif
                           @if($pageindex==103)
                           <li class="active"><a href="/isletmeyonetim/kasadefteri"><img class="icon" src="{{secure_asset('public/img/tliconactive.png')}}" width="20" height="20" alt="Kasa Defteri" /><span>Kasa Defteri</span></a></li>
                           @else
                           <li><a href="/isletmeyonetim/kasadefteri"><img class="icon" src="{{secure_asset('public/img/tlicon.png')}}" width="20" height="20" alt="Kasa Defteri" /><span>Kasa Defteri</span></a></li>
                           @endif
                           @if(Auth::user()->is_admin)
                           @if($pageindex==101)
                           <li class="active"><a href="/isletmeyonetim/isletmem"><img class="icon" src="{{secure_asset('public/img/storeactive.png')}}" width="20" height="20" alt="İşletmem" /><span>İşletmem</span></a></li>
                           @else
                           <li><a href="/isletmeyonetim/isletmem"><img class="icon" src="{{secure_asset('public/img/store.png')}}" width="20" height="20" alt="İşletmem" /><span>İşletmem</span></a></li>
                           @endif
                           @if($pageindex==107 ||$pageindex == 1071)
                           <li class="active"><a href="/isletmeyonetim/musteriler"><img class="icon" src="{{secure_asset('public/img/customersiconactive.png')}}" width="20" height="20" alt="Müşteriler" /><span>Müşterilerim</span></a></li>
                           @else
                           <li><a href="/isletmeyonetim/musteriler"><img class="icon" src="{{secure_asset('public/img/customersicon.png')}}" width="20" height="20" alt="Müşteriler" /><span>Müşterilerim</span></a></li>
                           @endif
                           @if($pageindex==111)
                           <li class="active"><a href="/isletmeyonetim/personeller"><i class="icon mdi mdi-face"></i><span>Personellerim</span></a></li>
                           @else
                           <li><a href="/isletmeyonetim/personeller"><i class="icon mdi mdi-face"></i><span>Personellerim</span></a></li>
                           @endif
                           @endif 
                           @if($pageindex==104)
                           <li class="active"><a href="/isletmeyonetim/ayarlar"><i class="icon mdi mdi-settings"></i><span> Ayarlar</span></a></li>
                           @else
                           <li><a href="/isletmeyonetim/ayarlar"><i class="icon mdi mdi-settings"></i><span>Ayarlar</span></a></li>
                           @endif
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
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery/jquery.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/main.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/bootstrap/dist/js/bootstrap.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery-flot/jquery.flot.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery-flot/jquery.flot.pie.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery-flot/jquery.flot.resize.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery-flot/plugins/jquery.flot.orderBars.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery-flot/plugins/curvedLines.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.sparkline/jquery.sparkline.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/countup/countUp.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jqvmap/jquery.vmap.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jqvmap/maps/jquery.vmap.world.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/app-dashboard.js')}}" type="text/javascript"></script> 
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/moment.js/min/moment.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datetimepicker/js/bootstrap-datetimepicker.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery-ui/jquery-ui.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.nestable/jquery.nestable.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.fullcalendar/fullcalendar.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.fullcalendar/locale-all.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/app-page-calendar.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/scheduler.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/js/jquery.dataTables.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/js/dataTables.bootstrap.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/dataTables.buttons.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.html5.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.flash.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.print.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.colVis.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/datatables/plugins/buttons/js/buttons.bootstrap.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/app-form-elements.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/select2/js/select2.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/app-tables-datatables.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.magnific-popup/jquery.magnific-popup.min.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/masonry/masonry.pkgd.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/js/app-page-gallery.js')}}" type="text/javascript"></script>
      <script src="{{secure_asset('public/isletmeyonetim_assets/lib/jquery.niftymodals/dist/jquery.niftymodals.js')}}" type="text/javascript"></script>
      
      <script src="{{secure_asset('public/js/custom.js')}}" type="text/javascript"></script>
      <script type="text/javascript">
         $.fn.niftyModal('setDefaults',{
         overlaySelector: '.modal-overlay',
         closeSelector: '.modal-close',
         classAddAfterOpen: 'modal-show',
         });
         $(document).ready(function(){
         //initialize the javascript
         
         App.init(); 
          App.pageCalendar();
          App.dataTables();
          App.formElements();
         
         App.dashboard();
          
         });
         
         
      </script>
   </body>
</html>