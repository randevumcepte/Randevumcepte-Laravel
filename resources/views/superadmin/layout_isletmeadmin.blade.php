<!DOCTYPE html>
<html>
   <head>
      <!-- Basic Page Info -->
      <meta charset="utf-8" />
      <title>{{\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi')}} İşletme Yönetim Paneli</title>
      @if($pageindex == 2 || $pageindex == 1)
        <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.css')}}"
      />
      <link href="https://fullcalendar.io/js/fullcalendar-scheduler-1.5.0/scheduler.min.css" rel="stylesheet" />
      <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet" />
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.js"></script>
      <script src="https://fullcalendar.io/js/fullcalendar-3.1.0/fullcalendar.js"></script>

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
         href="{{secure_asset('public/yeni_panel/vendors/images/apple-touch-icon.png')}}"
      />
      <link
         rel="icon"
         type="image/png"
         sizes="32x32"
         href="{{secure_asset('public/yeni_panel/vendors/images/favicon-32x32.png')}}"
      />
      <link
         rel="icon"
         type="image/png"
         sizes="16x16"
         href="{{secure_asset('public/yeni_panel/vendors/images/favicon-16x16.png')}}"
      />

      <!-- Mobile Specific Metas -->
      <meta
         name="viewport"
         content="width=device-width, initial-scale=1, maximum-scale=1"
      />

      <!-- Google Font -->
      <link
         href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
         rel="stylesheet"
      />
      <!-- CSS -->
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/core.css')}}" />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}"
      />
        
      @if($pageindex==5||$pageindex == 4|| $pageindex==6 ||$pageindex==1)
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/datatables/css/dataTables.bootstrap4.min.css')}}"
      />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/datatables/css/responsive.bootstrap4.min.css')}}"
      />
      @endif
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
      />
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/style.css')}}" />
 
     

     
      <!-- Global site tag (gtag.js) - Google Analytics -->
      <script
         async
         src="https://www.googletagmanager.com/gtag/js?id=G-GBZ3SGGX85"
      ></script>
      <script>
         window.dataLayer = window.dataLayer || [];
         function gtag() {
            dataLayer.push(arguments);
         }
         gtag("js", new Date());

         gtag("config", "G-GBZ3SGGX85");
      </script>
      <!-- Google Tag Manager -->
      <script>
         (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
            var f = d.getElementsByTagName(s)[0],
               j = d.createElement(s),
               dl = l != "dataLayer" ? "&l=" + l : "";
            j.async = true;
            j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
            f.parentNode.insertBefore(j, f);
         })(window, document, "script", "dataLayer", "GTM-NXZMQSS");
      </script>
      <!-- End Google Tag Manager -->
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
                     <span class="badge notification-active"></span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right">
                     <div class="notification-list mx-h-350 customscroll">
                        <ul>
                           <li>
                              <a href="#">
                                 <img src="vendors/images/img.jpg" alt="" />
                                 <h3>John Doe</h3>
                                 <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed...
                                 </p>
                              </a>
                           </li>
                           <li>
                              <a href="#">
                                 <img src="vendors/images/photo1.jpg" alt="" />
                                 <h3>Lea R. Frith</h3>
                                 <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed...
                                 </p>
                              </a>
                           </li>
                           <li>
                              <a href="#">
                                 <img src="vendors/images/photo2.jpg" alt="" />
                                 <h3>Erik L. Richards</h3>
                                 <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed...
                                 </p>
                              </a>
                           </li>
                           <li>
                              <a href="#">
                                 <img src="vendors/images/photo3.jpg" alt="" />
                                 <h3>John Doe</h3>
                                 <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed...
                                 </p>
                              </a>
                           </li>
                           <li>
                              <a href="#">
                                 <img src="vendors/images/photo4.jpg" alt="" />
                                 <h3>Renee I. Hansen</h3>
                                 <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed...
                                 </p>
                              </a>
                           </li>
                           <li>
                              <a href="#">
                                 <img src="vendors/images/img.jpg" alt="" />
                                 <h3>Vicki M. Coleman</h3>
                                 <p>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing
                                    elit, sed...
                                 </p>
                              </a>
                           </li>
                        </ul>
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
                
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-info"></i> Temel Bilgiler</a
                     >
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-clock-o"></i> Çalışma Saatleri</a
                     >
                      
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-users"></i> Personeller</a
                     >
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-list" aria-hidden="true"></i>Hizmetler</a
                     >
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-tags"></i> Ürünler</a
                     >
                      <a class="dropdown-item" href="#"
                        ><i class="fa fa-upload"></i> Paketler</a
                     >
                       <a class="dropdown-item" href="#"
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
                
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-calendar"></i> Yeni Randevu</a
                     >
                     <a onclick="baslikata();"  class="dropdown-item" href="#" data-toggle="modal" data-target="#musteri-bilgi-modal"
                        ><i class="fa fa-user"></i> Yeni Müşteri</a
                     >
                      
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-tags"></i> Yeni Ürün Satışı</a
                     >
                     <a class="dropdown-item" href="#"
                        ><i class="icon-copy fa fa-table" aria-hidden="true"></i>Yeni Paket Satışı</a
                     >
                     <a class="dropdown-item" href="#"
                        ><i class="fa fa-download"></i> Yeni Masraf</a
                     >
                      <a class="dropdown-item" href="#"
                        ><i class="fa fa-upload"></i> Yeni Alacak</a
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
                                    <img src="{{secure_asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar">
                              @else
                                 <img src="{{secure_asset('public/isletmeyonetim_assets/img/avatar.png')}}" alt="Avatar"> 
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

       
      <div class="right-sidebar">
         <div class="sidebar-title">
            <h3 class="weight-600 font-16 text-blue">
               Layout Settings
               <span class="btn-block font-weight-400 font-12"
                  >User Interface Settings</span
               >
            </h3>
            <div class="close-sidebar" data-toggle="right-sidebar-close">
               <i class="icon-copy ion-close-round"></i>
            </div>
         </div>
         <div class="right-sidebar-body customscroll">
            <div class="right-sidebar-body-content">
               <h4 class="weight-600 font-18 pb-10">Header Background</h4>
               <div class="sidebar-btn-group pb-30 mb-10">
                  <a
                     href="javascript:void(0);"
                     class="btn btn-outline-primary header-white active"
                     >White</a
                  >
                  <a
                     href="javascript:void(0);"
                     class="btn btn-outline-primary header-dark"
                     >Dark</a
                  >
               </div>

               <h4 class="weight-600 font-18 pb-10">Sidebar Background</h4>
               <div class="sidebar-btn-group pb-30 mb-10">
                  <a
                     href="javascript:void(0);"
                     class="btn btn-outline-primary sidebar-light"
                     >White</a
                  >
                  <a
                     href="javascript:void(0);"
                     class="btn btn-outline-primary sidebar-dark active"
                     >Dark</a
                  >
               </div>

               <h4 class="weight-600 font-18 pb-10">Menu Dropdown Icon</h4>
               <div class="sidebar-radio-group pb-10 mb-10">
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebaricon-1"
                        name="menu-dropdown-icon"
                        class="custom-control-input"
                        value="icon-style-1"
                        checked=""
                     />
                     <label class="custom-control-label" for="sidebaricon-1"
                        ><i class="fa fa-angle-down"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebaricon-2"
                        name="menu-dropdown-icon"
                        class="custom-control-input"
                        value="icon-style-2"
                     />
                     <label class="custom-control-label" for="sidebaricon-2"
                        ><i class="ion-plus-round"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebaricon-3"
                        name="menu-dropdown-icon"
                        class="custom-control-input"
                        value="icon-style-3"
                     />
                     <label class="custom-control-label" for="sidebaricon-3"
                        ><i class="fa fa-angle-double-right"></i
                     ></label>
                  </div>
               </div>

               <h4 class="weight-600 font-18 pb-10">Menu List Icon</h4>
               <div class="sidebar-radio-group pb-30 mb-10">
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebariconlist-1"
                        name="menu-list-icon"
                        class="custom-control-input"
                        value="icon-list-style-1"
                        checked=""
                     />
                     <label class="custom-control-label" for="sidebariconlist-1"
                        ><i class="ion-minus-round"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebariconlist-2"
                        name="menu-list-icon"
                        class="custom-control-input"
                        value="icon-list-style-2"
                     />
                     <label class="custom-control-label" for="sidebariconlist-2"
                        ><i class="fa fa-circle-o" aria-hidden="true"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebariconlist-3"
                        name="menu-list-icon"
                        class="custom-control-input"
                        value="icon-list-style-3"
                     />
                     <label class="custom-control-label" for="sidebariconlist-3"
                        ><i class="dw dw-check"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebariconlist-4"
                        name="menu-list-icon"
                        class="custom-control-input"
                        value="icon-list-style-4"
                        checked=""
                     />
                     <label class="custom-control-label" for="sidebariconlist-4"
                        ><i class="icon-copy dw dw-next-2"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebariconlist-5"
                        name="menu-list-icon"
                        class="custom-control-input"
                        value="icon-list-style-5"
                     />
                     <label class="custom-control-label" for="sidebariconlist-5"
                        ><i class="dw dw-fast-forward-1"></i
                     ></label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline">
                     <input
                        type="radio"
                        id="sidebariconlist-6"
                        name="menu-list-icon"
                        class="custom-control-input"
                        value="icon-list-style-6"
                     />
                     <label class="custom-control-label" for="sidebariconlist-6"
                        ><i class="dw dw-next"></i
                     ></label>
                  </div>
               </div>

               <div class="reset-options pt-30 text-center">
                  <button class="btn btn-danger" id="reset-settings">
                     Reset Settings
                  </button>
               </div>
            </div>
         </div>
      </div>
      <div class="left-side-bar">
         <div class="brand-logo">
            <a href="/">
               <img src="{{secure_asset('public/yeni_panel/vendors/images/randevu-sistemim.png')}}" alt=""   />
             
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
                     <a href="/isletmeyonetim/randevular-liste" class="dropdown-toggle no-arrow">
                        <span class="micon bi bi-table"></span
                        ><span class="mtext">Randevular</span>
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
                     @if($pageindex==5)

                     <a href="/isletmeyonetim/personeller" class="dropdown-toggle no-arrow active">
                     @else
                      <a href="/isletmeyonetim/personeller" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon fa fa-user"></span>
                        <span class="mtext">Personeller</span>
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
                     @if($pageindex==7)
                     <a href="/isletmeyonetim/isletmem" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/isletmem" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon bi bi-house"></span>
                        <span class="mtext">İşletmem</span>
                     </a>
 
                  </li>
                  <li> 
                     <a href="/isletmeyonetim/kasadefteri" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-money-2"></span>
                        <span class="mtext">Kasa Defteri</span>
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
              
                  

                   
               
            </div>
            <div class="footer-wrap pd-20 mb-20 card-box">
               {{\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi')}} &copy;. Her Hakkı Saklıdır. Tasarım
               <a href="https://webfirmam.com.tr" target="_blank"
                  >Web Firmam İnternet Hizmetleri</a
               >
            </div>
         </div>
      </div>
       
      <!-- welcome modal end -->
      <!-- js -->
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.js')}}"></script>
       
     
      @if($pageindex == 5|| $pageindex==4 ||$pageindex==6 ||$pageindex==1)
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
      
      @endif 
      <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
      @if($pageindex == 2)
      <script src="{{secure_asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/calendar-setting.js')}}"></script>
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
      @if($pageindex==6)
      <script>
         $(document).ready(function(){
            $('#urun_liste').DataTable().destroy();
            $('#urun_liste').DataTable({
                       
                        
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
      <script src="{{secure_asset('public/js/custom.js')}}"></script>
      <!-- Google Tag Manager (noscript) -->
      <script type="text/javascript">
         
             function baslikAta(){
            $('.modal_baslik').empty();
            $('.modal_baslik').append("ff");
          }
         
         
      </script>
      <noscript
         ><iframe
            src="https://www.googletagmanager.com/ns.html?id=GTM-NXZMQSS"
            height="0"
            width="0"
            style="display: none; visibility: hidden"
         ></iframe
      ></noscript>
      <!-- End Google Tag Manager (noscript) -->

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
   </body>
</html>
