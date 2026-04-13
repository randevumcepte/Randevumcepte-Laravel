<!DOCTYPE html>
<html>
   <head>
      <!-- Basic Page Info -->
      <meta charset="utf-8" />
      <title>{{\App\Salonlar::where('domain',$_SERVER['SERVER_NAME'])->value('salon_adi')}} İşletme Yönetim Paneli</title>
      @if($pageindex == 2 || $pageindex == 1)
        <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.css')}}"
      />
      <link
      rel="stylesheet"
      type="text/css"
      href="src/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.css"
    />
      <link href="https://fullcalendar.io/js/fullcalendar-scheduler-1.5.0/scheduler.min.css" rel="stylesheet" />
      <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet" />
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.js"></script>
      <script src="https://fullcalendar.io/js/fullcalendar-3.1.0/fullcalendar.js"></script>
<link rel="stylesheet" type="text/css" href="vendors/styles/style.css" />
         <style>
          
         .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
      </style>
      @endif 
      <!-- Site favicon -->
      <link
         rel="apple-touch-icon"
         sizes="180x180"
         href="{{asset('public/yeni_panel/vendors/images/apple-touch-icon.png')}}"
      />
      <link
         rel="icon"
         type="image/png"
         sizes="32x32"
         href="{{asset('public/yeni_panel/vendors/images/favicon-32x32.png')}}"
      />
      <link
         rel="icon"
         type="image/png"
         sizes="16x16"
         href="{{asset('public/yeni_panel/vendors/images/favicon-16x16.png')}}"
      />

      <!-- Mobile Specific Metas -->
      <meta
         name="viewport"
         content="width=device-width, initial-scale=1, maximum-scale=1"
      />
      <!-- Google Font -->
         <link 
     href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" 
     rel="stylesheet"
    />
      <link
         href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
         rel="stylesheet"
      />
      <!-- CSS -->
      <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_panel/vendors/styles/core.css')}}" />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}"
      />
        
      @if($pageindex==5||$pageindex == 4|| $pageindex==6 ||$pageindex==1 ||$pageindex==9 ||$pageindex==11 ||$pageindex==12|| $pageindex==13 || $pageindex==14)
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/datatables/css/dataTables.bootstrap4.min.css')}}"
      />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/datatables/css/responsive.bootstrap4.min.css')}}"
      />
      @endif
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
      />
      <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_panel/vendors/styles/style.css')}}" />
 
     

      
       
      <script src="{{asset('public/js/OneSignalSDKWorker.js')}}"></script>
      <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" defer></script>
      <script>
        window.OneSignal = window.OneSignal || [];
        OneSignal.push(function() {
          OneSignal.init({
            appId: "80d23c5f-24e2-4030-9df2-6fdbbede127a",
          });
        });
      </script>
   </head>
   <body>
      <div id="preloader">
         <div id="loaderstatus">&nbsp;</div>
      </div>

      <div class="header">
         <div class="header-left">
            <div class="menu-icon bi bi-list"></div>
            <div
               class="search-toggle-icon bi bi-search"
               data-toggle="header_search"
            >
               

            </div>
            <div class="header-search">
               <form>
                  <div class="form-group mb-0">
                     <i class="dw dw-search2 search-icon"></i>
                     <input id="musteri_arama"
                        type="text"
                        class="form-control search-input"
                        placeholder="Müşteri arayın..."
                        list="musteriler_filtre_liste" oninput="getParameter();">
                     
                       <datalist id="musteriler_filtre_liste">
                        
                          
                       </datalist>
                       <script type="text/javascript">
                          function getParameter() {
                              var selectedTitle = document.getElementById("musteri_arama").value;
                              var value2send = document.querySelector(`#musteriler_filtre_liste option[value='${selectedTitle}']`).dataset.value; // Here now you have the book title id.
                              window.location.href= value2send;
                           }
                       </script>
                      
                  </div>
               </form>
            </div>
         </div>

         <div class="header-right">
            
            
            <div class="user-notification">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle no-arrow"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                  >
                     <i class="icon-copy dw dw-notification"></i>
                     @if($bildirimler->count()>0)
                     <span class="badge notification-active"></span>
                     @endif
                  </a>
                  <div class="dropdown-menu dropdown-menu-right">
                     <div class="notification-list mx-h-350 customscroll">
                      @foreach($bildirimler as $bildirim)
                        <ul>
                           <li>
                              <a href="{{$bildirim->url}}">
                                 
                                 <p>
                                  @if(!$bildirim->okundu)
                                  <b>
                                    @endif
                                  {{$bildirim->aciklama}}
                                   @if(!$bildirim->okundu)
                                  </b>
                                    @endif
                                 </p>
                              </a>
                           </li>
                          
                        </ul>
                        @endforeach
                        @if($bildirimler->count() == 0)
                        <p style="color: black;text-align: center;">Bildiriminiz bulunmamaktadır</p>
                        @endif
                     </div>
                  </div>
               </div>
            </div>
            <div class="user-notification">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle  no-arrow"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                  >

                    <i class="dw dw-settings2"></i>
                  </a>
                  <div
                     class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                     style="width: 200px;">
                
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar"
                        ><i class="fa fa-info"></i> Temel Bilgiler</a
                     >
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar?p=calismasaatleri"
                        ><i class="fa fa-clock-o"></i> Çalışma Saatleri</a
                     >
                      
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar?p=personeller"
                        ><i class="fa fa-users"></i> Personeller</a
                     >
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar?p=hizmetler"
                        ><i class="fa fa-list" aria-hidden="true"></i>Hizmetler</a
                     >
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar?p=urunler"
                        ><i class="fa fa-tags"></i> Ürünler</a
                     >
                      <a class="dropdown-item" href="/isletmeyonetim/ayarlar?p=paketler"
                        ><i class="fa fa-upload"></i> Paketler</a
                     >
                       <a class="dropdown-item" href="/isletmeyonetim/ayarlar?p=randevuayarlari"
                        ><i class="fa fa-table"></i> Randevu Ayarları</a
                     >
                  </div>
               </div>
            </div>
            
            <div class="user-notification">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle  no-arrow"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                  >

                    <i class="fa fa-plus"></i>
                  </a>
                  <div
                     class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                     style="width: 200px;">
                
                     <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal-view-event-add"
                        ><i class="fa fa-calendar"></i> Yeni Randevu & Saat Kapama</a
                     >
                     <a onclick="modalbaslikata('Yeni Müşteri','musteri_bilgi_formu');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#musteri-bilgi-modal"
                        ><i class="fa fa-user"></i> Yeni Müşteri</a
                     >
                     <a onclick="modalbaslikata('Yeni Ürün','')" class="dropdown-item" href="#" data-toggle="modal" data-target="#urun_satisi_modal"
                        ><i class="fa fa-tags"></i> Yeni Ürün</a
                     > 
                     <a onclick="modalbaslikata('Yeni Ürün Satışı','')" class="dropdown-item" href="#" data-toggle="modal" data-target="#urun_satisi_modal"
                        ><i class="fa fa-tags"></i> Yeni Ürün Satışı</a
                     >
                     <a class="dropdown-item" href="#" id="button_paket_button"
                        ><i class="icon-copy fa fa-table" aria-hidden="true"></i>Yeni Paket Satışı</a
                     >
                     <a class="dropdown-item" href="#"  data-toggle="modal" data-target="#yeni_masraf_modal"
                        ><i class="fa fa-download"></i> Yeni Masraf</a
                     >
                       <a class="dropdown-item" href="#" data-toggle="modal" data-target="#alacak_modal_adisyon"
                        ><i class="fa fa-download"></i> Yeni Alacak</a
                     >
                  </div>
               </div>
            </div>

            <div class="user-info-dropdown">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                  >

                     <span class="user-icon">
                        @if(\App\Salonlar::where('id',Auth::user()->salon_id)->value('logo') != null || \App\Salonlar::where('id',Auth::user()->salon_id)->value('logo') != '')
                                    <img src="{{asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar">
                              @else
                                 <img src="{{asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar"> 
                              @endif
                     </span>
                     <span class="user-name">{{Auth::user()->name}}</span>
                  </a>
                  <div
                     class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                  >
                
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar"
                        ><i class="dw dw-user1"></i> Hesap Bilgileri</a
                     >
                     <a class="dropdown-item" href="/isletmeyonetim/ayarlar"
                        ><i class="dw dw-settings2"></i> Şifre Değiştir</a
                     >
                      
                     <a class="dropdown-item" href="/isletmeyonetim/cikisyap"
                        ><i class="dw dw-logout"></i> Çıkış Yap</a
                     >
                  </div>
               </div>
            </div>
             
         </div>
      </div>

       
     
      <div class="left-side-bar">
         <div class="brand-logo">
            <a href="/isletmeyonetim">
               <img src="{{asset('public/yeni_panel/vendors/images/randevu-sistemim.png')}}" alt=""   />
             
            </a>
            <div class="close-sidebar" data-toggle="left-sidebar-close">
               <i class="ion-close-round"></i>
            </div>
         </div>
         <div class="menu-block customscroll">
            <div class="sidebar-menu">
               <ul>
                   
                  <li>
                      @if($pageindex==1)
                     <a href="/isletmeyonetim" class="dropdown-toggle no-arrow active">
                        @else
                        <a href="/isletmeyonetim" class="dropdown-toggle no-arrow">
                        @endif
                        <span class="micon bi bi-house"></span
                        ><span class="mtext">Özet</span>
                     </a>
                  </li>
                 
                  <li class="active">
                 
                  <li>
                
                   @if($pageindex==2)
                     <a href="/isletmeyonetim/randevular" class="dropdown-toggle no-arrow active">
                   @else
                        <a href="/isletmeyonetim/randevular" class="dropdown-toggle no-arrow">
                   @endif
                        <span class="micon bi bi-calendar4-week"></span
                        ><span class="mtext">Randevu Takvimi</span>
                     </a>
                  </li>
                  <li>
                    @if($pageindex==3)
                     <a href="/isletmeyonetim/randevular-liste" class="dropdown-toggle no-arrow active">
                      @else
                       <a href="/isletmeyonetim/randevular-liste" class="dropdown-toggle no-arrow ">
                        @endif
                        <span class="micon bi bi-table"></span
                        ><span class="mtext">Randevular</span>
                     </a>
 
                  </li>

                   <li>
                      @if($pageindex==11)
                     <a href="/isletmeyonetim/adisyonlar" class="dropdown-toggle no-arrow active">
                        @else
                     <a href="/isletmeyonetim/adisyonlar" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon bi bi-table"></span
                        ><span class="mtext">Adisyonlar</span>
                     </a>
 
                  </li>
                  <li>
                      @if($pageindex==12)
                     <a href="/isletmeyonetim/ongorusmeler" class="dropdown-toggle no-arrow active">
                        @else
                     <a href="/isletmeyonetim/ongorusmeler" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon bi bi-table"></span
                        ><span class="mtext">Ön Görüşmeler</span>
                     </a>
 
                  </li>
                  <li>
                     @if($pageindex==4 ||$pageindex==41)
                     <a href="/isletmeyonetim/musteriler" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/musteriler" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon fa fa-users"></span>
                        <span class="mtext">Müşteriler</span>
                     </a>
 
                  </li>
                  
                   <li>
                     @if($pageindex==6)

                     <a href="/isletmeyonetim/urunler" class="dropdown-toggle no-arrow active">
                     @else
                      <a href="/isletmeyonetim/urunler" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon fa fa-tags"></span>
                        <span class="mtext">Ürünler</span>
                     </a>

 
                  </li>
                    <li>
                 @if($pageindex==14)

                     <a href="/isletmeyonetim/seanstakip" class="dropdown-toggle no-arrow active">
                     @else
                      <a href="/isletmeyonetim/seanstakip" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon fa fa-tags"></span>
                        <span class="mtext">Seanslar</span>
                     </a>

 
                  </li>
                  <li>
                 @if($pageindex==13)

                     <a href="/isletmeyonetim/paketsatislari" class="dropdown-toggle no-arrow active">
                     @else
                      <a href="/isletmeyonetim/paketsatislari" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon fa fa-tags"></span>
                        <span class="mtext">Paket Satışları</span>
                     </a>

 
                  </li>
                  <li> 
                     <a href="/isletmeyonetim/kasadefteri" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-money-2"></span>
                        <span class="mtext">Kasa Defteri</span>
                     </a>
 
                  </li>
                  <li> 
                     @if($pageindex==15)
                     <a href="/isletmeyonetim/masraflar" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/masraflar" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="fa fa-upload"></span>
                        <span class="mtext">Masraflar</span>
                     </a>
 
                  </li>

                  <li>
                     @if($pageindex==16)
                     <a href="/isletmeyonetim/alacaklar" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/alacaklar" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="fa fa-download"></span>
                        <span class="mtext">Alacaklar</span>
                     </a>
 
                  </li>
                  <li> 
                     <a href="/isletmeyonetim/toplusms" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-message"></span>
                        <span class="mtext">Toplu SMS</span>
                     </a>
 
                  </li> 

                   <li>  

                     @if($pageindex==9)
                     <a href="/isletmeyonetim/ayarlar" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ayarlar" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon dw dw-settings1"></span>
                        <span class="mtext">Ayarlar</span>
                     </a>
 
                  </li> 
               </ul>
            </div>
         </div>
      </div>
      <div class="mobile-menu-overlay"></div>

      <div class="main-container">
         @if($pageindex != 2)
         <div class="pd-ltr-10 xs-pd-10-10">
         @else
         <div class="pd-0">
            @endif
            <div class="min-height-200px">
               
               @yield('content')
               <div id="hata"></div>
                  

                   
               
            </div>
            <div class="footer-wrap pd-20 mb-20 card-box">
               {{\App\Salonlar::where('domain',$_SERVER['SERVER_NAME'])->value('salon_adi')}} &copy;. Her Hakkı Saklıdır. Tasarım
               <a href="https://webfirmam.com.tr" target="_blank"
                  >Web Firmam İnternet Hizmetleri</a
               >
            </div>
         </div>
      </div>
       
      <!-- welcome modal end -->
      <!-- js -->
      <script src="{{asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
      <script src="{{asset('public/yeni_panel/vendors/scripts/script.js')}}"></script>
       
     
      @if($pageindex == 5|| $pageindex==4 ||$pageindex==6 ||$pageindex==1||$pageindex==9 ||$pageindex==11 || $pageindex==12|| $pageindex==13 || $pageindex==14)
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/jquery.dataTables.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/dataTables.bootstrap4.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/dataTables.responsive.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/responsive.bootstrap4.min.js')}}"></script>
      <!-- buttons for Export datatable -->
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/dataTables.buttons.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/buttons.bootstrap4.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/buttons.print.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/buttons.html5.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/buttons.flash.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/pdfmake.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/datatables/js/vfs_fonts.js')}}"></script>
      <!-- Datatable Setting js -->
      <script src="{{asset('public/yeni_panel/vendors/scripts/datatable-setting.js')}}"></script>
      
      @endif 
      <script src="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
      <script src="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
      @if($pageindex == 2)
      <script src="{{asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
      <script src="{{asset('public/yeni_panel/vendors/scripts/calendar-setting.js')}}"></script>
      <script type="text/javascript">
         function getQueryStrings()
          {
            var vars = [], hash; //vars arrayi ve hash değişkeni tanımlıyoruz
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&'); //QueryString değerlerini ayıklıyoruz.
            for(var i = 0; i < hashes.length; i++)
            {
              hash = hashes[i].split('=');
              vars.push(hash[0]);
              vars[hash[0]] = hash[1]; //Değerlerimizi dizimize ekliyoruz
            }
            return vars;
          }

        $(document).ready(function () {
        


           var tarih = getQueryStrings()["tarih"];

         
           if(tarih){
              
               tarih = new Date(tarih);
           } 
           else{
            
               tarih = new Date();
            }
        
         $('#calendar').fullCalendar({
        
 
          monthNames: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
          monthNamesShort: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
          dayNames: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
          dayNamesShort: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
          editable:true,
          buttonText: {
                today:    'Bugün',
                month:    'Ay',
                week:     'Hafta',
                day:      'Gün',
                list:     'Liste',
                listMonth: 'Aylık Liste',
                listYear: 'Yıllık Liste',
                listWeek: 'Haftalık Liste',
                listDay: 'Günlük Liste'
          },
          
          defaultView: 'agendaDay',
          defaultDate: tarih,
          editable: true,
          selectable: true,
          eventLimit: true, // allow "more" link when too many events
          header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
          },

          //// uncomment this line to hide the all-day slot
          allDaySlot: false,
          resources: <?php echo json_encode($randevular["resource"])?>,
          events: <?php echo json_encode($randevular["randevu"])?>,
          timeFormat: 'H:mm',
          views: {
              agenda: {
                  slotLabelFormat: 'H:mm',
              }
          },
          moreLinkContent:function(args){
             return '+'+args.num+' Randevu Daha';
          },
          select: function(start, end, jsEvent, view, resource) {
            console.log(
              'select',
              start.format(),
              end.format(),
              resource ? resource.id : '(no resource)'
            );
            var tarihsaattext = start.format().split("T");

            $('#randevutarihiyeni').val(tarihsaattext[0]);
            $('#randevu_saat').val(tarihsaattext[1]);


          },
          dayClick: function (start) {

            jQuery("#modal-view-event-add").modal();

          },
          eventClick: function (event, jsEvent, view) {
            jQuery(".event-icon").html("<i class='fa fa-" + event.icon + "'></i>");
            jQuery(".event-title").html(event.title);
            jQuery(".event-body").html(event.description);
            jQuery(".eventUrl").attr("href", event.url);
            jQuery("#modal-view-event").modal();
          },
        });
         $('.fc-header-toolbar button').click(function(){
               var view = $('#calendar').fullCalendar('getView');
               $('.fc-axis.fc-widget-header').attr('style','width:25px');
               if(view.type=='agendaDay'){
                  <?php $headdata = json_decode($randevular['resource'],true); ?>
               <?php foreach($headdata as $key=>$res){ ?>
                  $('.fc th:nth-child('+<?php echo $key+2 ;?>+'n)').css({'background':'<?php echo $res['bgcolor']; ?>','color':'#fff'});
                  console.log('<?php echo $res['bgcolor']; ?>');
               <?php } ?>
               }
              
               
         });
         $('.fc-axis.fc-widget-header').attr('style','width:25px');
         <?php $headdata = json_decode($randevular['resource'],true); ?>
         <?php foreach($headdata as $key=>$res){ ?>
            $('.fc th:nth-child('+<?php echo $key+2 ;?>+'n)').css({'background':'<?php echo $res['bgcolor']; ?>','color':'#fff'});
            console.log('<?php echo $res['bgcolor']; ?>');
         <?php } ?>
      });
      </script>
      @endif
      @if($pageindex==4)
      <script>
         $(document).ready(function(){
            if($('#randevu_liste').length)
               $('#randevu_liste').DataTable({
                  
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
       @if($pageindex==11 )
      <script type="text/javascript">
         $(document).ready(function () {
            $('#adisyon_liste').DataTable().destroy();
            $('#adisyon_liste').DataTable({
                    autoWidth: false,
                    responsive: true,
                    columns:[
                        { data: 'durum'},
                        { data: 'musteri'},
                        { data: 'hizmetler'},
                        { data: 'urunler'},
                        
                        {data : 'tarih'},
                         {data : 'saat'},
                          {data : 'geldimi'},
                           {data : 'toplam'},
                             {data : 'odenen'},  
                             {data : 'kalan_tutar'},
                               {data : 'islemler'},
                    ],
                    data: <?php echo $adisyonlar; ?>,

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
      @if($pageindex==6 ||$pageindex == 9 )
      <script>
         $(document).ready(function(){
            $('#urun_liste').DataTable().destroy();
            $('#urun_liste').DataTable({
                       autoWidth:false,
                       responsive:true,
                        
                    columns:[
                        { data: 'urun_adi',name: 'urun_adi' },
                                        { data: 'stok_adedi' ,name: 'stok_adedi'},
                                        { data: 'fiyat',name: 'fiyat' }, 
                                        { data: 'barkod',name: 'barkod' },
                            
                            {data : 'islemler'},
                    ],
                    data: <?php echo $urunler["urun_liste"] ?>,

                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },

                     
                   

            });
            $('#paket_liste').DataTable().destroy();
            $('#paket_liste').DataTable({
                       autoWidth:false,
                       responsive:true,
                        
                    columns:[
                        { data: 'miktar' },
                        { data: 'tip' },
                        { data: 'hizmet' }, 
                        { data: 'fiyat' },
                            
                        {data : 'islemler'},
                    ],
                    data: <?php echo $paketler["paket_liste"] ?>,

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
      @if($pageindex==1)
      <script type="text/javascript">
         $(document).ready(function(){
             
            $('#adisyon_liste_ozet').DataTable({
                    autoWidth: false,
                    responsive: true,
                    columns:[
                        { data: 'tarih',name: 'urun_adi' ,width:"100px" },
                        { data: 'musteri' ,name: 'musteri', width:"200px"},
                        { data: 'hizmetler',name: 'hizmetler', width:"250px" }, 
                        { data: 'urunler',name: 'urunler',width:"250px" },
                        { data: 'toplam',name: 'toplam' },
                        {data : 'islemler'},
                    ],
                    data: <?php echo $acik_adisyonlar; ?>,

                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },

                     
                   

            });
            $('#dogum_tarihleri_ozet').DataTable({
                  autoWidth: false,
                       responsive: true,
                    columns:[
                        { data: 'ad_soyad'   },
                        { data: 'telefon' },
                        { data: 'dogum_tarihi' }
                       
                    ],
                    data: <?php echo $yaklasan_dogumgunleri; ?>,

                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                  },
            });
              $('#alacak_hatirlatmalari').DataTable({
                  autoWidth: false,
                   responsive: true,
                    columns:[
                        { data: 'ad_soyad'   },
                        { data: 'telefon' },
                        { data: 'dogum_tarihi' }
                       
                    ],
                    data: <?php echo $alacak_hatirlatmalari; ?>,

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
      @if($pageindex==4)
      <script type="text/javascript">
         $(document).ready(function(){

           if($('#adisyon_form').length){
               
               let total = 0;
               $('input[name="hizmet_fiyati_adisyon[]"]').each(function(){
                  total += parseFloat($(this).val()) || 0;


               });
                $('input[name="urun_fiyati_adisyon[]"]').each(function(){
                  
                  total += parseFloat($(this).val()) || 0;
                  
               });
               $('#hizmet_urunler_toplam_fiyat').empty();
               
               $('#hizmet_urunler_toplam_fiyat').append(total);

           }
           if($('#musteri_tablo').length){
                $('#musteri_tablo').DataTable(/*{
                       
                        
                    columns:[
                        { data: 'ad_soyad',name: 'ad_soyad' },
                        { data: 'telefon' ,name: 'telefon'},
                        { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                        { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                        { data: 'randevu_sayisi',name: 'randevu_sayisi' },    
                        {data : 'islemler'},
                    ],
                    data: <?php /*echo $musteriler*/ ?>,

                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },

                     
                   

            }*/);
           }
         });
      </script>
      @endif
      @if($pageindex==9)
      <script type="text/javascript">
          $(document).ready(function(){
             if($('#personel_tablo').length){
                $('#personel_tablo').DataTable({
                  autoWidth: false,
                   responsive: true,
                   

                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                  },
            });
           }
          });

      </script>
      @endif
       @if($pageindex==12)
      <script type="text/javascript">
          $(document).ready(function(){
               $('#on_gorusme_liste').DataTable({
                  autoWidth: false,
                   responsive: true,
                    columns:[
                       {data:'id'},
                        { data: 'musteri'   },
                        { data: 'telefon' },
                         
                         { data: 'olusturulma'   },
                        { data: 'hatirlatma' },
                     
                         { data: 'paket' },
                          
                            { data: 'gorusmeyiyapan' },

                           { data: 'durum' },
                            { data: 'islemler' },
                        
                   
                       
                    ],
                    data: <?php echo $on_gorusmeler; ?>,

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
      @if($pageindex==14)
      <script type="text/javascript">
          $(document).ready(function(){
               $('#seans_takip_liste').DataTable({
                  autoWidth: false,
                   responsive: true,
                    columns:[
                       { data: 'musteri'   },
                       { data: 'satici' },
                         
                       { data: 'hizmet'   },
                       { data: 'miktar' },
                     
                       { data: 'kullanilan' },
                          
                       { data: 'kalan_kullanim' },

                       { data: 'toplam_tutar' },
                       { data: 'kalan_tutar' },
                          { data: 'islemler' }
                        
                   
                       
                    ],
                    data: <?php echo $seanstakip; ?>,

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
      @if($pageindex==13)
      <script type="text/javascript">
          $(document).ready(function(){
               $('#paket_satislari_liste').DataTable({
                  autoWidth: false,
                   responsive: true,
                    columns:[
                       {data:'satis_tarihi'},
                       { data: 'musteri'   },
                       { data: 'satici' },
                         
                       { data: 'hizmet'   },
                       { data: 'miktar' },
                     
                       { data: 'kullanilan' },
                          
                       { data: 'kalan_kullanim' },

                       { data: 'toplam_tutar' },
                         { data: 'odenen_tutar' },
                       { data: 'kalan_tutar' },
                        { data: 'olusturan' },
                         { data: 'olusturulma' },
                          { data: 'islemler' }
                        
                   
                       
                    ],
                    data: <?php echo $paketsatislari; ?>,

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
     
      <script type="text/javascript">
         $(document).ready(function(){
             
            $('#paket_liste_modal').DataTable().destroy();
            $('#paket_liste_modal').DataTable({
                       autoWidth:false,
                       responsive:true,
                     paging:false,
                    columns:[
                        { data: 'miktar' },
                        { data: 'tip' },
                        { visible:false,data: 'hizmet_id' },
                        { data: 'hizmet' }, 
                        { data: 'fiyat' },
                            
                        {data : 'islemler'},
                    ],
                    data: <?php echo $paketler["paket_liste"] ?>,

                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },

                     
                   

            });
            $('#masraf_duzenle_modal').DataTable();
         });
      </script>
       
      <!-- Google Tag Manager (noscript) -->
     
      </script>
      <!-- End Google Tag Manager (noscript) -->
      <div id="personel-modal" class="modal modal-top fade calendar-modal">
         <form id="yenipersonelbilgiekle" method="POST">
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="width: 90%; max-height: 90%;">
                  <div class="modal-header">

                     <h2 class="modal_baslik"></h2>
                  </div>
                  <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6">
                           <h3 style="font-size: 15px;font-weight: bold;">Personel Bilgileri</h3>
                           <div class="form-group">
                              <label>Personel Adı</label>
                              <input id="personeladi_yeni" name="personeladi_yeni" required placeholder="Personel adı..." class="form-control">
                           </div>
                           <div class="form-group">
                              <label>Unvan</label>
                              <input id="unvan_yeni" name="unvan_yeni" required placeholder="Unvan..." class="form-control">
                           </div>
                          
                            
                           <div class="form-group">
                              <label>Profil Resmi</label>
                              <input type="file" id="profilresmi_yeni" name="profilresmi_yeni" class="form-control">
                           </div>
                        </div>
                        <div class="col-md-6">
                           
                            
                           <div class="form-group">
                              <label>Cep Telefon (başında 0 olmadan 5XXXXXXXXX şeklinde)</label>
                              <input type="text" id="ceptelefon_yeni" maxlength="10" pattern="[0-9]*" name="ceptelefon_yeni" placeholder="Cep Telefonu..." class="form-control">
                           </div>
                            <div class="form-group">
                              <label>Cinsiyet</label>
                              <select id="cinsiyet_yeni" name="cinsiyet_yeni" required class="form-control">
                                 <option value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                              </select>
                           </div>
                           <div class="form-group">
                              <label>Hesap Türü</label>
                              <select class="form-control" name="sistemyetki_yeni" id="sistemyetkiyeni1"> 
                                 @foreach(\App\YetkiliHesapTurleri::all() as $hesap_turu)
                                 <option value="{{$hesap_turu->id}}">{{$hesap_turu->hesap_turu}}</option>
                                 @endforeach
                              </select>
                           
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <h3 style="font-size: 15px; font-weight: bold;">Personel Çalışma Saatleri</h3>
                           <table class="table table table-striped table-hover">
                              <tbody>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Pazartesi</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati1" style="float: left;">  
                                    </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati1"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Salı</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati2" style="float: left;"> 
                                    </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati2"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Çarşamba</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati3" style="float: left;"> 
                                    </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati3"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Perşembe</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati4" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati4"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Cuma</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati5" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati5"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Cumartesi</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati6" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati6"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="calisiyor7" value="00:00" name="calisiyor7"><label for="calisiyor7">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Pazar</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="baslangicsaati7" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="bitissaati7"  style="float: left;">
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                        <div class="col-md-12">
                           <h3 style="font-size: 15px; font-weight: bold;">Personel Mola Saatleri</h3>
                           <table class="table table table-striped table-hover">
                              <tbody>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar1" name="molavar1"><label for="molavar1">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Pazartesi</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati1" style="float: left;">  
                                    </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati1"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar2" name="molavar2"><label for="molavar2">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Salı</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati2" style="float: left;"> 
                                    </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati2"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar3" name="molavar3"><label for="molavar3">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Çarşamba</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati3" style="float: left;"> 
                                    </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati3"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar4" name="molavar4"><label for="molavar4">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Perşembe</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati4" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati4"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar5" name="molavar5"><label for="molavar5">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Cuma</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati5" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati5"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar6" name="molavar6"><label for="molavar6">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Cumartesi</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati6" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati6"  style="float: left;">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <div class="be-checkbox be-checkbox-color inline">
                                          <input type="checkbox" id="molavar7" value="00:00" name="molavar7"><label for="molavar7">
                                          </label>
                                       </div>
                                    </td>
                                    <td>Pazar</td>
                                    <td>
                                       <input type="time" class="form-control" value="00:00" name="molabaslangicsaati7" style="float: left;"> 
                                       </td>
                                    <td> 
                                       <input type="time" class="form-control" value="00:00" name="molabitissaati7"  style="float: left;">
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                        <div class="col-md-12">
                           <h3 style="font-size: 15px; font-weight: bold;">Personelin Sunduğu Hizmetler
                           </h3>
                           <div class="form-group">
                              <select multiple name="sunulanhizmetler_yeni[]" id="sunulanhizmetler_yeni" class="custom-select2 form-control" style="width: 100%"> 
                                 @foreach(\App\Hizmetler::all() as $hizmetler)
                                 <option value="{{$hizmetler->id}}">{{$hizmetler->hizmet_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                     </div>

                  </div>
                  <div class="modal-footer" style="display:block;">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                        </div>
                     </div>
                  </div>

               </div>
            </div>
         </form>
      </div>
      <div id="musteri-bilgi-modal" class="modal modal-top fade calendar-modal">
         <form id="musteri_bilgi_formu" method="POST">
         {{ csrf_field() }}
         <input type="hidden" name="musteri_id" value="{{($pageindex== 41) ? $musteri->id : ''}}">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="width: 950px; max-height: 90%;">
               <div class="modal-header">

                  <h2  class="modal_baslik"></h2>
               </div>
               <div class="modal-body">

                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Ad Soyad</label>
                           <input type="text" name="ad_soyad" required class="form-control" value="{{($pageindex== 41) ? $musteri->name : ''}}">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <label>Telefon</label>
                        <input type="tel" name="telefon" required class="form-control" value="{{($pageindex== 41) ? $musteri->cep_telefon : ''}}">
                     </div>

                     <div class="col-md-6">
                        <label>E-posta</label>
                        <input type="email" name="email" class="form-control" value="{{($pageindex== 41) ? $musteri->email : ''}}">
                     </div>
                     <div class="col-md-6">
                        <label>Doğum Tarihi</label>
                        <input type="text" name="dogum_tarihi" class="form-control date-picker" value="{{($pageindex== 41) ? $musteri->dogum_tarihi : ''}}">
                     </div>
                     <div class="col-md-6">
                        <label>TC Kimlik No</label>
                        <input type="tel" name="tc_kimlik_no" class="form-control" value="{{($pageindex== 41) ? $musteri->tc_kimlik_no : ''}}">
                     </div>
                     <div class="col-md-6">
                        <label>Cinsiyet</label>
                        <select class="form-control" name="cinsiyet">
                           @if($pageindex==41 && $musteri->cinsiyet === 0)
                              <option value="">Belirtilmemiş</option>
                              <option selected value="0">Kadın</option>
                              <option value="1">Erkek</option>
                           @elseif($pageindex==41 && $musteri->cinsiyet === 1)
                              <option value="">Belirtilmemiş</option>
                              <option value="0">Kadın</option>
                              <option selected value="1">Erkek</option>
                           @else
                              <option selected value="">Belirtilmemiş</option>
                              <option value="0">Kadın</option>
                              <option value="1">Erkek</option>
                           @endif

                        </select>
                     </div>
                     <div class="col-md-12">
                        <label>Notlar</label>
                        <textarea class="form-control" name="ozel_notlar" >{{($pageindex== 41) ? $musteri->ozel_notlar : ''}}</textarea>
                     </div>

                  </div>
                 
                   
                   
                

               </div>
               <div class="modal-footer" style="display:block;">
                  <div class="row">
                     <div class="col-6 col-xs-6 col-sm-6">
                        <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>

                     </div>
                     <div class="col-6 col-xs-6 col-sm-6">
                          <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         </form>
      </div>
      <div
      id="modal-view-event-add"
      class="modal modal-top fade calendar-modal"
      >
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content" style="width: 950px; max-height: 90%;">
            <div class="modal-body">
               <h2 class="text-blue h2 mb-10">Yeni</h2>
               <div class="tab">
                  <ul class="nav nav-tabs" role="tablist">
                     <li class="nav-item">
                        <a
                           class="nav-link active text-blue"
                           data-toggle="tab"
                           href="#yeni-randevu"
                           role="tab"
                           aria-selected="true"
                           >Randevu</a
                           >
                     </li>
                     <li class="nav-item">
                        <a
                           class="nav-link text-blue"
                           data-toggle="tab"
                           href="#saat-kapama"
                           role="tab"
                           aria-selected="false"
                           >Saat Kapama</a
                           >
                     </li>
                  </ul>
                  <div class="tab-content">
                     <div
                        class="tab-pane fade show active"
                        id="yeni-randevu"
                        role="tabpanel"
                        >
                        <div class="pd-20">
                           <form id="yenirandevuekleform"  method="POST">
                              {!!csrf_field()!!}
                              <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Şube</label>
                                    <select name="suberandevu" id="suberandevu" class="form-control">
                                       @foreach(\App\Subeler::where('salon_id',Auth::user()->salon_id)->get() as $sube)
                                       <option value="{{$sube->id}}">{{$sube->sube}}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Müşteri</label>
                                    <select name="adsoyad" id="adsoyad" class="form-control custom-select2" style="width: 100%;">
                                       @foreach(\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get() as $mevcutmusteri)
                                       <option class="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Tarih</label>
                                    <input required placeholder="Tarih"
                                       type="text"
                                       class="form-control date-picker"
                                       name="tarih" id="randevutarihiyeni"
                                       />
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Saat</label>
                                    <input type="time" class="form-control" name="saat" id="randevu_saat" required>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <textarea class="form-control" name="personel_notu" placeholder="Notlar"></textarea>
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>SMS ile hatırlat</label>
                                    <label class="switch" style="margin-left: 25px;">
                                    <input type="checkbox" name="sms_hatirlatma" id="sms_hatirlatma">
                                    <span class="slider"></span>
                                    </label>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Tekrarlayan</label>
                                    <label class="switch" style="margin-left: 25px;">
                                    <input id="tekrarlayan" name="tekrarlayan" type="checkbox">
                                    <span class="slider"></span>
                                    </label> 
                                 </div>
                              </div>
                           </div>
                           <div class="row tekrar_randevu" style="display:none">
                             <div class="col-md-6">
                                <div class="form-group">
                                  <label>Tekrar Sıklığı</label>
                                  <select class="form-control" name="tekrar_sikligi">
                                    <option value="+1 day">Her gün</option>
                                    <option value="+2 days">2 günde bir </option>
                                    <option value="+3 days">3 günde bir </option>
                                    <option value="+4 days">4 günde bir </option>
                                    <option value="+5 days">5 günde bir </option>
                                    <option value="+6 days">6 günde bir </option>
                                    <option value="+1 week">Haftada bir</option>
                                    <option value="+2 weeks">2 Haftada bir</option>
                                    <option value="+3 weeks">3 Haftada bir</option>
                                    <option value="+4 weeks">4 Haftada bir</option>
                                    <option value="+1 month">Her ay</option>
                                    <option value="+45 days">45 günde bir</option>
                                    <option value="+2 months">2 ayda bir</option>
                                    <option value="+3 months">3 ayda bir</option>
                                    <option value="+6 months">6 ayda bir</option>
                                  </select>
                                </div>
                             </div>
                             <div class="col-md-6">
                               <div class="form-group">
                                 <label>Tekrar Sayısı</label>
                                 <input type="tel" name="tekrar_sayisi" class="form-control" required value="0">
                               </div>
                             </div>
                           </div>
                           <div class="hizmetler_bolumu">
                              <div class="row" data-value="0">
                                 <div class="col-md-3">
                                    <div class="form-group">
                                       <label>Personel</label>
                                       <select name="randevupersonelleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                                          <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-3">
                                    <div class="form-group">
                                       <label>Hizmet</label>
                                       <select name="randevuhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get() as $hizmetliste)
                                          <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-2">
                                    <div class="form-group">
                                       <label>Süre</label>
                                       <input type="tel" class="form-control" name="hizmet_suresi[]" >
                                    </div>
                                 </div>
                                 <div class="col-md-2">
                                    <div class="form-group">
                                       <label>Fiyat</label>
                                       <input type="tel" class="form-control" name="hizmet_fiyat[]">
                                    </div>
                                 </div>
                                 <div class="col-md-2">
                                    <div class="form-group">
                                       <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                       <button type="button" name="hizmet_formdan_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <button type="button" id="bir_hizmet_daha_ekle" class="btn btn-secondary btn-lg btn-block">
                                    Bir Hizmet Daha Ekle
                                    </button>
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-lg btn-block">Randevu Oluştur</button>
                                 </div>
                              </div>
                           </div>
                           </form>
                        </div>
                     </div>
                     <div class="tab-pane fade" id="saat-kapama" role="tabpanel">
                        <div class="pd-20">
                           <form id="saat_kapama" method="POST">
                              {!!csrf_field()!!}
                              <div class="row">
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label>Personel</label>
                                       <select name="personel" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                                          <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label>Tarih</label>
                                       <input type="text" required class="form-control date-picker" name="tarih">
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label>Başlangıç Saati</label>
                                       <input type="time" class="form-control" name="saat" required>
                                    </div>
                                    
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label>Bitiş Saati</label>
                                       <input type="time" class="form-control" name="saat_bitis"  required>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label>Tüm gün</label>
                                       <label class="switch" style="margin-left: 25px;">
                                       <input type="checkbox" name="tum_gun" id="tum_gun">
                                       <span class="slider"></span>
                                       </label>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label>Tekrarlayan</label>
                                       <label class="switch" style="margin-left: 25px;">
                                       <input id="tekrarlayan_saat_kapama" name="tekrarlayan" type="checkbox">
                                       <span class="slider"></span>
                                       </label> 
                                    </div>
                                 </div>                                 

                              </div>
                              <div class="row tekrar_saat_kapama" style="display:none">
                                 <div class="col-md-6">
                                   <div class="form-group">
                                     <label>Tekrar Sıklığı</label>
                                     <select class="form-control" name="tekrar_sikligi">
                                       <option value="+1 day">Her gün</option>
                                       <option value="+2 days">2 günde bir </option>
                                       <option value="+3 days">3 günde bir </option>
                                       <option value="+4 days">4 günde bir </option>
                                       <option value="+5 days">5 günde bir </option>
                                       <option value="+6 days">6 günde bir </option>
                                       <option value="+1 week">Haftada bir</option>
                                       <option value="+2 weeks">2 Haftada bir</option>
                                       <option value="+3 weeks">3 Haftada bir</option>
                                       <option value="+4 weeks">4 Haftada bir</option>
                                       <option value="+1 month">Her ay</option>
                                       <option value="+45 days">45 günde bir</option>
                                       <option value="+2 months">2 ayda bir</option>
                                       <option value="+3 months">3 ayda bir</option>
                                       <option value="+6 months">6 ayda bir</option>
                                     </select>
                                   </div>
                                 </div>
                                 <div class="col-md-6">
                                  <div class="form-group">
                                    <label>Tekrar Sayısı</label>
                                    <input type="tel" name="tekrar_sayisi" class="form-control" required value="0">
                                  </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="form-group">
                                       <label>Notlar</label>
                                       <textarea name="personel_notu" class="form-control"></textarea>
                                    </div>
                                 </div>
                                 <div class="col-md-12">
                                    <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                                 </div>
                              </div>

                               
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <button
                  type="button"
                  class="btn btn-danger btn-lg btn-block"
                  data-dismiss="modal"
                  ><i class="fa fa-times"></i>
               Kapat
               </button>
            </div>
         </div>
         </form>
      </div>
   </div>
   <div
   id="urun_satisi_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="adisyon_urun_satisi"  method="POST">
            <div class="modal-header">
               <h2 class="modal_baslik"></h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="randevu_id" id="randevu_id" value="{{(isset($randevu)) ? $randevu->id : ''}}">
                
               <div class="row" data-value="0">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label>Tarih</label>

                           <input type="text" required class="form-control date-picker" name="urun_satis_tarihi" value="{{(isset($randevu)) ? $randevu->tarih : ''}}">
                        </div>
                     </div>
               </div>

               <div class="urunler_bolumu"> 
                  <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Ürün</label>
                              <select name="urunyeni[]" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\Urunler::where('salon_id',Auth::user()->salon_id)->get() as $urun)
                                 <option value="{{$urun->id}}">{{$urun->urun_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                          
                        </div>
                         <div class="col-md-2">
                           <div class="form-group">
                              <label>Adet</label>
                              <input type="tel" required name="urun_adedi[]" value="1" class="form-control">
                           </div>
                          
                        </div>
                         <div class="col-md-2">
                           <div class="form-group">
                              <label>Fiyat</label>
                              <input type="tel" required name="urun_fiyati[]" value="{{\App\Urunler::where('salon_id',Auth::user()->salon_id)->first()->fiyat}}" class="form-control">
                           </div>
                           
                        </div>
                        <div class="col-md-2">
                           <div class="form-group">
                              <label style="visibility: hidden;width: 100%;">Kaldır</label>
                              <button type="button" name="urun_formdan_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                           </div>
                        </div>
                  </div>
                      
               </div>
              
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <button type="button" id="bir_urun_daha_ekle" class="btn btn-secondary btn-lg btn-block">
                        Bir Ürün Daha Ekle
                        </button>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Satıcı</label>
                        <select name="urun_satici[]" class="form-control custom-select2" style="width: 100%;">
                           @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                           <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="satis_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-success">
               Kaydet
               </button>
               <button id="modal_kapat"
                  type="button"
                  class="btn btn-danger"
                  data-dismiss="modal"
                  >
               Kapat
               </button>
            </div>
      </div>
      </form>
   </div>
</div></div>
<!-- yeni alacak  -->
<div
   id="alacak_modal_adisyon"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="alacak_formu"  method="POST">
            <div class="modal-header">
               <h2>Yeni Alacak</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="alacak_id" value="">
                
               <div class="row" data-value="0">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="tarih" value="{{date('Y-m-d')}}">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                        <label>Müşteri</label>
                        <select name="musteri" class="form-control custom-select2" style="width:100%">
                           @foreach(\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get() as $musteri)
                           <option value="{{$musteri->user_id}}">{{$musteri->users->name}}</option>
                           @endforeach
                        </select>
                     </div>
                     </div>                     
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="alacak_tutari" required class="form-control">
                        </div>
                     </div>
                      <div class="col-md-6">
                        <div class="form-group">
                           <label>Planlanan Ödeme Tarihi</label>
                            <input type="text" required class="form-control date-picker" name="planlanan_odeme_tarihi">
                        </div>
                     </div>
                     
               </div>

              
               <div class="row">
                   
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="alacak_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-md-6">
                       <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
               Kaydet </button>
                  </div>
                  <div class="col-md-6">
                      <button  
                     type="button"
                     class="btn btn-danger btn-lg btn-block"
                     data-dismiss="modal"
                     > <i class="fa fa-times"></i>
                  Kapat
                  </button>
                  </div>
               </div>
             
                  
              
            </div>
      </div>
      </form>
   </div>
</div></div>
<!-- yeni masraf -->
<div
   id="yeni_masraf_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="masraf_formu"  method="POST">
            <div class="modal-header">
               <h2>Yeni Masraf</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="masraf_id" value="">
                
                 <div class="row" data-value="0">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="tarih" value="{{date('Y-m-d')}}">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="masraf_tutari" required class="form-control">
                        </div>
                     </div>
                     <div class="col-md-12">
                     <div class="form-group">
                        <label>Açıklama</label>
                        <textarea name="masraf_aciklama" class="form-control"></textarea>
                     </div>
                  </div>
                </div>
                <div class="row" data-value="0">
                   <div class="col-md-9">
                           <div class="form-group">
                              <label>Masraf Kategorisi</label>
                              <select name="masraf_kategorisi" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\MasrafKategorisi::all() as $cat)
                                 <option value="{{$cat->id}}">{{$cat->kategori}}</option>
                                 @endforeach
                              </select>
                           </div>
                          
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label style="visibility: hidden;width: 100%;">Masraflar</label>
                              <button type="button" data-value="0" class="btn btn-success" data-toggle="modal" data-target="#masraf_duzenle_modal" ><i class="icon-copy dw dw-settings2"></i> Düzenle</button>
                           </div>
                        </div>
                </div>
                <div class="row" data-value="0">
                      <div class="col-md-6">
                           <div class="form-group">
                              <label>Ödeme Yöntemi</label>
                              <select name="masraf_odeme_yontemi" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                                    <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                                 @endforeach
                              </select>
                           </div>
                          
                        </div>
                     
            
                 <div class="col-md-6">
                           <div class="form-group">
                              <label>Harcayan</label>
                              <select name="harcayan" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                                 <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                          
                        </div>
                </div>                 
                
                   <div class="row">
                     <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="masraf_notlari" class="form-control"></textarea>
                     </div>
                 </div>    
                   </div>
                     
                   
            
              
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-6">
                       <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
               Kaydet </button>
                  </div>
                  <div class="col-md-6">
                      <button  
                     type="button"
                     class="btn btn-danger btn-lg btn-block"
                     data-dismiss="modal"
                     > <i class="fa fa-times"></i>
                  Kapat
                  </button>
                  </div>
               </div>
             
        
            </div>
      </div>
      </form>
   </div>
</div></div>
<!-- masraf kategorilerini düzenleme -->
<div
                           class="modal fade bs-example-modal-lg"
                           id="masraf_duzenle_modal"
                           tabindex="-1"
                           role="dialog"
                           aria-labelledby="myLargeModalLabel"
                           aria-hidden="true" style="z-index: 99999999999;"
                        >
                           <div class="modal-dialog modal-dialog-centered">
                              <div class="modal-content" style="width:100%">
                                 <div class="modal-header">
                                    <h2 class="modal-title"  >
                                      Masraf Kategorileri
                                    </h2>
                                    <button
                                       type="button"
                                       class="close"
                                       data-dismiss="modal"
                                       aria-hidden="true"
                                    >
                                       ×
                                    </button>
                                 </div>
                                 <div class="modal-body">
                                     {!!csrf_field()!!}
                                   <div class="row" data-value="0">
            
     
                <table class="data-table table stripe hover nowrap" id="masraflar_liste">
                  
                    @foreach(\App\MasrafKategorisi::all() as $cat)
                    <tr>
                      <td> {{$cat->kategori}}</td>
                    </tr>
                    @endforeach
                </table>
              </div>
                        
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-12">
                       <button type="submit" data-toggle="modal" class="btn btn-success btn-lg btn-block" data-target="#masraf_ekle_modal"> <i class="icon-copy dw dw-add"></i>
               Kategori Ekle </button>
                  </div>
                 
               </div>
             
        
            </div>
                                 </div>
                                 
                              </div>
                           </div>
                        </div>
<!-- masraf ekleme -->
<div
                           class="modal fade bs-example-modal-lg"
                           id="masraf_ekle_modal"
                           tabindex="1"
                           role="dialog"
                           aria-labelledby="myLargeModalLabel"
                           aria-hidden="true" style="z-index: 9999999999999;"
                        >
                           <div class="modal-dialog modal-lg modal-dialog-centered">
                              <div class="modal-content" style="width:100%">
                                 <div class="modal-header">
                                    <h2 class="modal-title"  >
                                      Yeni Masraf Kategorisi
                                    </h2>
                                    <button
                                       type="button"
                                       class="close"
                                       data-dismiss="modal"
                                       aria-hidden="true"
                                    >
                                       ×
                                    </button>
                                 </div>
                                 <div class="modal-body">
                                     {!!csrf_field()!!}
                                   <div class="row" data-value="0">
            
     <div class="col-md-12">
       
                <div class="form-group">
                        <label>Açıklama</label>
                        <textarea name="masraf_aciklama" class="form-control"></textarea>
                     </div>
              </div>
     </div>
                        
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-12">
                       <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="icon-copy dw dw-add"></i>
              Kaydet </button>
                  </div>
                 
               </div>
             
        
            </div>
                                 </div>
                                 
                              </div>
                           </div>
                        </div>
<div
                           class="modal fade bs-example-modal-lg"
                           id="kayitli_paket_ekle_modal"
                           tabindex="-1"
                           role="dialog"
                           aria-labelledby="myLargeModalLabel"
                           aria-hidden="true" style="z-index: 99999999999;"
                        >
                           <div class="modal-dialog modal-lg modal-dialog-centered">
                              <div class="modal-content" style="width:100%">
                                 <div class="modal-header">
                                    <h2 class="modal-title"  >
                                      Kayıtlı Paketlerden Seç
                                    </h2>
                                    <button
                                       type="button"
                                       class="close"
                                       data-dismiss="modal"
                                       aria-hidden="true"
                                    >
                                       ×
                                    </button>
                                 </div>
                                 <div class="modal-body">
                                     {!!csrf_field()!!}
                                    <table class="data-table table stripe hover nowrap" id="paket_liste_modal">
                                        <thead>
                                          <tr>
                                              <th>Adet</th>
                                              <th>Tip</th>
                                             <th style="display: none;">Hizmet ID</th>
                                                                    
                                              <th>Hizmet</th>
                                              <th>Fiyat (₺)</th>
                                               
                                               
                                                
                                              <th class="datatable-nosort"></th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          
                                           
                                           
                                        </tbody>
                                    </table>
                                 </div>
                                    <div class="modal-footer" style="display:none">
                                     <button  id="kayitli_paket_ekle_modal_kapat" class="btn btn-danger btn-lg btn-block" data-dismiss="modal">Kapat</button>
                                 </div>
                                 
                              </div>
                           </div>
                        </div>
   
<div
   id="paket_satisi_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="paket_satisi"  method="POST">
            <div class="modal-header">
               <h2 class="modal_baslik"></h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
                
                
               <div class="row" data-value="0">
                     <div class="col-md-6">
                        <div class="form-group">
                          
                           <label>Tarih</label>

                           <input type="text" required class="form-control date-picker" name="paket_satis_tarihi" value="{{date('Y-m-d')}}">
                        </div>
                     </div>
                
                  
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Müşteri</label>
                        <select name="musteri" class="form-control custom-select2" style="width:100%">
                           @foreach(\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get() as $musteri)
                           <option value="{{$musteri->user_id}}">{{$musteri->users->name}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
               </div>
               <div class="paketler_bolumu">
                  <div class="row" data-value="0">
                     <div class="col-md-1">
                          <input type="hidden" name="paket_id[]" value="">
                        <div class="form-group">
                          <label>Adet</label>
                          <input type="tel" required name="paketadet[]" id="paketadet" class="form-control" required>
                         
                          
                        </div>
                     </div>
                   
                     <div class="col-md-2">
                        <div class="form-group">
                          <label>Tip</label>
                          <select name="pakettip[]" id="" class="form-control">
                             <option value="1">Seans</option>
                             <option value="0">Dakika</option>
                          </select>
                    
                          
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                           <label>Hizmet</label>
                           <select name="pakethizmet[]"  class="form-control custom-select2" style="width:100%">
                           @foreach(\App\SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get() as $hizmetliste)
                                          <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                           @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Fiyat (₺)</label>
                           <input type="tel" name="paketfiyat[]"  class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                          <label>Başlangıç Tarihi</label>
                          <input name="paketbaslangictarihi[]" id="" class="form-control date-picker">
                            
                    
                          
                        </div>
                     </div> 
                     <div class="col-md-1">
                           <div class="form-group">
                              <label style="visibility: hidden;width: 100%;">Kaldır</label>
                              <button type="button" name="paket_formdan_sil"  data-value="0" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                           </div>
                     </div>
                  </div> 
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <button type="button" class="btn btn-secondary btn-lg btn-block" id="bir_paket_daha_ekle">
                        Yeni Paket Ekle
                        </button>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#kayitli_paket_ekle_modal">Kayıtlı Paketlerden Seç</button>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Satıcı</label>
                        <select name="paket_satici" class="form-control custom-select2" style="width: 100%;">
                           @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                           <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="paket_satis_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display: block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                  </div>
                  <div class="col-md-6">
                        <button  
                  class="btn btn-danger btn-lg btn-block"
                  data-dismiss="modal"
                  >
                     Kapat
               </button>
                  </div>
               </div>
            
            
            </div>
         </div>
      </form>
   </div>
</div></div>
</div>
<div
   id="yeni_seans_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="paket_satisi"  method="POST">
            <div class="modal-header">
               <h2 class="modal_baslik"></h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
                
                
               <div class="row" data-value="0">
                 <div class="col-md-12">
                        <div class="form-group">
                           <label>Hizmet</label>
                           <select name="hizmet" id="pakethizmet" class="form-control custom-select2" style="width:100%">
                           @foreach(\App\SalonHizmetler::where('salon_id',Auth::user()->salon_id)->get() as $hizmetliste)
                                          <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                           @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tarih</label>

                           <input type="text" required class="form-control date-picker" name="paket_satis_tarihi" value="{{date('Y-m-d')}}">
                        </div>
                     </div>
                
                  
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Müşteri</label>
                        <select name="musteri" id="musteri_paket" class="form-control custom-select2" style="width:100%">
                           @foreach(\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get() as $musteri)
                           <option value="{{$musteri->user_id}}">{{$musteri->users->name}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                        <div class="form-group">
                           <label>Kaç Seans</label>
                           <input type="tel" name="fiyat" id="paketfiyat" class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Seans Ücreti (₺)</label>
                           <input type="tel" name="fiyat" id="paketfiyat" class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-12">
                     <div class="form-group">
                        <label>Personel</label>
                        <select name="paket_satici" class="form-control custom-select2" style="width: 100%;">
                           @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                           <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="paket_satis_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
               
             
            </div>
            <div class="modal-footer" style="display: block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                  </div>
                  <div class="col-md-6">
                        <button  
                  class="btn btn-danger btn-lg btn-block"
                  data-dismiss="modal"
                  >
                     Kapat
               </button>
                  </div>
               </div>
            
            
            </div>
         </div>
      </form>
   </div>
</div></div>
</div>

 <div id="ongorusme-modal" class="modal fade">
         <form id="ongorusmeformu" method="POST">
            <input type="hidden" name="on_gorusme_id" id="on_gorusme_id" value="">
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="width: 90%; max-height: 90%;">
                  <div class="modal-header">

                     <h2 class="modal_baslik"></h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                           <div class="col-md-4">
                              <div class="form-group">
                                 <label>Müşteri</label>
                                 <select name="musteri" id="musteri_select_list" class="form-control custom-select2" style="width:100%">
                                    <option value="0">Müşteri seçin...</option>
                                    @foreach(\App\MusteriPortfoy::where('salon_id',Auth::user()->salon_id)->get() as $musteri)
                                       <option value="{{$musteri->user_id}}">{{$musteri->users->name}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                          <div class="col-md-4">
                             <div class="form-group">
                                <label>Ad Soyad</label>
                                <input type="text" name="ad_soyad" id="ad_soyad" class="form-control" required>
                             </div>
                          </div>
                          <div class="col-md-4">
                             <div class="form-group">
                                <label>Telefon</label>
                                <input type="tel" name="telefon" id="telefon" class="form-control" required>
                             </div>
                          </div>
                          <div class="col-md-6">
                             <div class="form-group">
                                <label>E-mail</label>
                                <input type="email" name="email" id="email" class="form-control">
                             </div>
                          </div>
                          <div class="col-md-6">
                             <div class="form-group">
                                <label>Cinsiyet</label>
                                <select name="cinsiyet" id="cinsiyet" class="form-control">
                                   <option value="0">Kadın</option>
                                   <option value="1">Erkek</option>
                                </select>
                             </div>
                          </div>
                          <div class="col-md-12">
                             <div class="form-group">
                                 <label>Adres</label>
                                <textarea class="form-control" id="adres" name="adres"></textarea>
                             </div>

                          </div>

                          <div class="col-md-6">
                             <div class="form-group">
                                <label>Şehir</label>
                                <select name="sehir" id="sehir" class="form-control custom-select2" style="width: 100%;">
                                   
                                    @foreach(\App\Iller::all() as $il)
                                    <option value="{{$il->id}}">{{$il->il_adi}}</option>
                                    @endforeach
                                </select>
                             </div>
                          </div>
                          <div class="col-md-6">
                             <div class="form-group">
                                <label>Müşteri Tipi</label>
                                <input type="text" id="musteri_tipi" name="musteri_tipi" class="form-control">
                             </div>
                          </div>
                          <div class="col-md-6">
                             <div class="form-group">
                                <label>Meslek</label>
                                <input type="text" id="meslek" name="meslek" class="form-control">
                             </div>
                          </div>
                          <div class="col-md-6">
                             <div class="form-group">
                                <label>Paket</label>
                                <select name="paket" id="paket" class="form-control custom-select2" style="width: 100%;">
                                   @foreach(\App\Paketler::where('salon_id',Auth::user()->salon_id)->get() as $paket)
                                   <option value="{{$paket->id}}">
                                    {{$paket->miktar}}
                                       @if($paket->tip==0) Dakika @else Seans @endif
                                       {{$paket->hizmet->hizmet_adi}}

                                    </option>
                                   @endforeach
                                </select>
                             </div>
                           </div>
                           <div class="col-md-6" id="hatirlatma_yeni_ekleme">
                               <div class="form-group">
                                  <label>Kaç Gün Sonra Hatırlatılsın?</label>
                                  <input type="tel" name="hatirlatma_kac_gun_sonra" class="form-control">
                               </div>
                           </div>
                           <div class="col-md-6" id="hatirlatma_tarihi_guncelleme">
                               <div class="form-group">
                                  <label>Hatirlatma Tarihi</label>
                                  <input type="text" name="tarih" id="hatirlatma_tarihi" class="form-control date-picker">
                               </div>
                           </div>

                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>Görüşmeyi Yapan</label>
                                 <select name="gorusmeyi_yapan" id="gorusmeyi_yapan" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\Personeller::where('salon_id',Auth::user()->salon_id)->get() as $personel)
                                    <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label>Açıklama</label>
                                 <textarea name="aciklama" id="aciklama" class="form-control"></textarea>
                              </div>
                           </div>
                     </div>
                         

                  </div>
                  <div class="modal-footer" style="display:block;">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                        </div>
                     </div>
                  </div>

               </div>
            </div>
         </form>
      </div>
      <script src="{{asset('public/js/custom.js?v=1.0.23')}}"></script>
       
   </body>
</html>
