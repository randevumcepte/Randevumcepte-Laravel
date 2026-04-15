<!DOCTYPE html>
 
<html>
   <head>
      <!-- Basic Page Info -->
      <meta charset="utf-8" />
      <title>{{$sayfa_baslik}} | {{$isletme->salon_adi}} Yönetim Paneli</title>

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
      @if($pageindex == 2 || $pageindex == 1 )
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="/public/yeni_panel/src/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.css"
         />
      <link href="https://fullcalendar.io/js/fullcalendar-scheduler-1.5.0/scheduler.min.css" rel="stylesheet" />
      <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet" /> 
      <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.js"></script>
      <script src="https://fullcalendar.io/js/fullcalendar-3.1.0/fullcalendar.js"></script>

      @endif 
      @if($pageindex==40)
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="/public/yeni_panel/src/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.css"
         />
      <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet" />
      <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.js"></script>
      <script src="https://fullcalendar.io/js/fullcalendar-3.1.0/fullcalendar.js"></script>
      @endif
      <script src="{{asset('/public/js/dist/inputmask.min.js')}}"></script> 
      <script src="{{asset('/public/js/dist/jquery.inputmask.min.js')}}"></script> 
      <script src="{{asset('/public/js/dist/bindings/inputmask.binding.js')}}"></script>



      <style>
         .hidden {
      display: none;
    }

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
      <!-- Site favicon -->
      <link
         rel="apple-touch-icon"
         sizes="180x180"
         href="{{asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <link
         rel="icon"
         type="image/png"
         sizes="32x32"
         href="{{asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <link
         rel="icon"
         type="image/png"
         sizes="16x16"
         href="{{asset('public/yeni_panel/vendors/images/icon.png')}}"
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
      <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_panel/vendors/styles/core.css?v=1.4')}}" />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/datatables/css/dataTables.bootstrap4.min.css?v=3.0')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/datatables/css/responsive.bootstrap4.min.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
         />
         
      @if($pageindex==19 ||$pageindex==9)
      <link
         rel="stylesheet"
         type="text/css"
         href="/public/yeni_panel/src/plugins/fancybox/dist/jquery.fancybox.css"
      />
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css"  />
      @endif
      <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_panel/vendors/styles/style.css?v=8.0')}}" />
      <script src="{{asset('public/js/OneSignalSDKWorker.js')}}"></script>
      <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" defer></script>

      <script>
         window.OneSignal = window.OneSignal || [];
         OneSignal.push(function() {
           OneSignal.init({
             appId: "<?php echo $isletme->bildirim_app_id; ?>",
           });
         });
          OneSignal.push(function () {
             OneSignal.SERVICE_WORKER_PARAM = { scope: '/public/js/' };
             OneSignal.SERVICE_WORKER_PATH = 'public/js/OneSignalSDKWorker.js'
             OneSignal.SERVICE_WORKER_UPDATER_PATH = 'public/js/OneSignalSDKWorker.js'
             OneSignal.init(initConfig);
          });
      </script>
      <script type="text/javascript">  
         function selects(){  
             var ele=document.getElementsByName('katilimci_musteriler[]');  
             for(var i=0; i<ele.length; i++){  
                 if(ele[i].type=='checkbox')  
                     ele[i].checked=true;  
             }  
             var ele2=document.getElementsByName('salon_hizmetleri[]');  
             for(var i=0; i<ele2.length; i++){  
                 if(ele2[i].type=='checkbox')  
                     ele2[i].checked=true;  
             }  
         }  
         function deSelect(){  
             var ele=document.getElementsByName('katilimci_musteriler[]');  
             for(var i=0; i<ele.length; i++){  
                 if(ele[i].type=='checkbox')  
                     ele[i].checked=false;  
                   
             }  
            var ele2=document.getElementsByName('salon_hizmetleri[]');  
             for(var i=0; i<ele2.length; i++){  
                 if(ele2[i].type=='checkbox')  
                     ele2[i].checked=false;  
                   
             }  
         }             
      </script>  
      <style type="text/css">
         .single-file-input2 {
             overflow: hidden;
             position: relative;
             float: right;
             font-size: 1.2rem;
         }
         .single-file-input2 input[type="file"] {
    padding-top: 4rem;
    position: absolute;
    width: 100%;
    cursor: pointer;
    outline: none;
    z-index: 1;
}
      </style>

   </head>
   </head>
   <body>
      <?php 


         $headers = array(
                     'Authorization: Key '.$isletme->sms_apikey,
                     'Content-Type: application/json',
                     'Accept: application/json'
         );
       

         $ch=curl_init();
         curl_setopt($ch,CURLOPT_URL,'http://api.efetech.net.tr/v2/get/balance');
      
         curl_setopt($ch,CURLOPT_POST,1);
         curl_setopt($ch,CURLOPT_TIMEOUT,5);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
         $response = curl_exec($ch);
         curl_close($ch);
         $kalan_sms = json_decode($response,true);
         $day=0;
         if(date('D')=='Mon') $day=1;
         else if(date('D')=='Tue') $day=2;
         else if(date('D')=='Wed') $day=3;
         else if(date('D')=='Thu') $day=4;
         else if(date('D')=='Fri') $day=5;
         else if(date('D')=='Sat') $day=6;
         else if(date('D')=='Sun') $day=7;
      ?>
      <input id='dogrulama_kodu_ayari' type="hidden" value="{{\App\SalonSMSAyarlari::where('salon_id',$isletme->id)->where('ayar_id',16)->value('musteri')}}">
      <input name="sube" type="hidden" value="{{$isletme->id}}">
      <div id="preloader">
         <div id="loaderstatus">&nbsp;</div>
      </div>
      <div class="header">
         <div class="header-left">
            <div class="menu-icon bi bi-list"></div>
            <div
               class="search-toggle-icon bi bi-search"
               data-toggle="header_search"
               style="padding-left: 10px;"
               >
            </div>
            @if(!Auth::user()->hasRole('Personel'))
            <div class="header-search" >
 
               <select id="musteri_arama" class="form-control custom-select2" style="width: 100%;">
                 <option value="0">@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif Arayın...</option>
                  @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                  <option value="/isletmeyonetim/musteridetay/{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}} ({{$mevcutmusteri->users->cep_telefon}})</option>
                  @endforeach
               </select>
            </div>
            @endif
            <div style="margin-left: 20px;">
               <div class="form-group mb-0 " style="width: 300px;" >
                  <label id="myLabel">{{$isletme->salon_adi}}<br>{{$kalan_uyelik_suresi}} gün kaldı.</label>
               </div>
            </div>
         </div>
         <div class="header-right">
             @if(!Auth::user()->hasRole('Personel'))
            <div class="user-notification " style="padding:20px 0 0 0" id="kalansmskaybet"> 
                <div class="dropdown">
                <a
                     class="dropdown-toggle no-arrow"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                     title='Kalan SMS'
                     >

                  {{$kalan_sms['response']['balance']}} <i class="icon-copy fa fa-envelope-o" style="font-size:25px"></i> </a></div>
               
            </div>
            @endif
            <div class="user-notification">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle no-arrow"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                     >
                  <i class="icon-copy dw dw-notification"></i>
                  <span id="bildirim-badge" class="{{($bildirimler->where('okundu',false)->count()>0) ? 'badge notification-afctive' : ''}}">
                  @if($bildirimler->where('okundu',false)->count()>0)
                  {{$bildirimler->where('okundu',false)->count()}}
                  @endif
                  </span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-left">
                     <div class="notification-list  customscroll" id="bildirim_listesi" style="height:80vh">
                        @foreach($bildirimler as $bildirim)
                        <ul>
                           <li>
                              <a href="{{$bildirim->url}}" name="bildirim" data-index-number="{{$bildirim->id}}" data-value="{{$bildirim->randevu_id}}">
                                 <img src="{{$bildirim->img_src}}" alt="" class="mCS_img_loaded">
                                  
                                    @if(!$bildirim->okundu)
                                    <h3 style="background:#5C008E; padding: 5px; border-radius:5px; color:#fff"><b>
                                    @else
                                    <h3>

                                    @endif
                                    {{$bildirim->aciklama}}
                                    @if(!$bildirim->okundu)
                                    </b>
                                    @endif
                                 </h3>
                                 <p style="font-size: 12px;">
                                    <?php $to_time = strtotime(date('Y-m-d H:i:s'));
                                       $from_time = strtotime($bildirim->tarih_saat);
                                       $diff = round(abs($to_time - $from_time) / 60,0)." dakika önce";
                                       if ($diff >= 60){
                                          $diff = round(abs($to_time - $from_time) / 3600,0)." saat önce";
                                          if(round(abs($to_time - $from_time) / 3600,0) >= 24)
                                             $diff = date('d.m.Y H:i',strtotime($bildirim->tarih_saat));
                                       }
                                       echo $diff;
                                       ?>
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
            @if(!Auth::user()->hasRole('Personel'))
            <div class="user-notification">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle  no-arrow"
                     href="/isletmeyonetim/ayarlar?p=temelbilgiler"
                     >
                  <i class="dw dw-settings2"></i>
                  </a>
               </div>
            </div>
            @endif
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
                        ><i class="fa fa-calendar"></i> Yeni Randevu</a
                        >
                        @if(!Auth::user()->hasRole('Personel'))
                     <a  class="dropdown-item yanitli_musteri_ekleme" href="#" data-toggle="modal" data-target="#musteri-bilgi-modal"
                        ><i class="icon-copy fa fa-user-plus" aria-hidden="true"></i> Yeni @if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</a
                        >
                        
                     <a style="display: none;" onclick="modalbaslikata('Yeni Ürün','')" class="dropdown-item" href="#" data-toggle="modal" data-target="#urun-modal"
                        ><i class="fa fa-tags"></i> Yeni Ürün</a
                        > 
                        @endif
                     <a onclick="modalbaslikata('Yeni Adisyon','adisyon_formu')" class="dropdown-item" href="#" data-toggle="modal" data-target="#yeni_adisyon_modal"
                        ><i class="icon-copy fa fa-shopping-cart" aria-hidden="true"></i> Yeni Adisyon</a
                        >
                        @if(!Auth::user()->hasRole('Personel'))
                     <a onclick="modalbaslikata('Yeni Masraf','masraf_formu')" class="dropdown-item" href="#"  data-toggle="modal" data-target="#yeni_masraf_modal"
                        ><i class="fa fa-upload"></i> Yeni Masraf</a
                        >
                     
                        @endif
                      
                    
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
                  <img id="profil_resim_dashboard_top" src="{{(Auth::user()->profil_resim !== null) ? Auth::user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png'}}" alt="Avatar">
                  </span>
                  <span class="user-name">{{Auth::user()->name}}</span>
                  </a>
                  <div
                     class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                     <a class="dropdown-item" href="/isletmeyonetim/profil">
                     <i class="dw dw-user1"></i>
                     Profil Bilgileri
                     </a>
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
            <img src="{{asset('public/yeni_panel/vendors/images/randevumcepte.png')}}" alt=""   />
            </a>
            <div class="close-sidebar" data-toggle="left-sidebar-close">
               <i class="ion-close-round"></i>
            </div>
         </div>
         <div class="menu-block customscroll">
            <div class="sidebar-menu">
               <ul>
                  @if(!Auth::user()->hasRole('Personel'))
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
                  @endif

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
                     @if($pageindex==40)
                     <a href="/isletmeyonetim/ajanda" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ajanda" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-calendar4-week"></span
                        ><span class="mtext">Ajanda</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==3)
                     <a href="/isletmeyonetim/randevular-liste" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/randevular-liste" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon bi bi-card-heading"></span
                        ><span class="mtext">Randevular</span>
                     </a>
                  </li>
                  <li>
                   @if(!Auth::user()->hasRole('Personel'))
                     @if($pageindex==20)
                     <a href="/isletmeyonetim/etkinlik" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/etkinlik" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon bi bi-text-left"></span
                        ><span class="mtext">Etkinlikler</span>
                     </a>
                  </li>

                  <li>
                     @if($pageindex==22)
                     <a href="/isletmeyonetim/kampanya_yonetimi" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/kampanya_yonetimi" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon icon-copy bi bi-cash-coin"></span
                        ><span class="mtext">Reklam Yönetimi</span>
                     </a>
                  </li>
                  @endif
                  <li>

                     @if($pageindex==11 || $pageindex==111)
                     <a href="/isletmeyonetim/adisyonlar" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/adisyonlar" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-files"></span
                        ><span class="mtext">
                        
                        Satış Takibi
                       

                     </span>
                     </a>
                  </li>
                  
                  @if(!Auth::user()->hasRole('Personel'))
                  <li>
                     @if($pageindex==12)
                     <a href="/isletmeyonetim/ongorusmeler" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ongorusmeler" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-chat-left-text"></span
                        ><span class="mtext">Ön Görüşmeler</span>
                     </a>
                  </li>
                
                  <li>
                     @if($pageindex==4 ||$pageindex==41)
                     <a href="/isletmeyonetim/musteriler" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/musteriler" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-people"></span>
                     <span class="mtext">@if($isletme->salon_turu_id==15) Danışanlar @else Müşteriler @endif</span>
                     </a>
                  </li>
                 
                 <li>

                    @if($pageindex==30)
                     <a href="/isletmeyonetim/urunler" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/urunler" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-tags"></span>
                     <span class="mtext">Ürünler</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==13)
                     <a href="/isletmeyonetim/paketsatislari" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/paketsatislari" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-grid-3x3"></span>
                     <span class="mtext">Paketler</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==14)
                     <a href="/isletmeyonetim/seanstakip" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/seanstakip" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-card-checklist"></span>
                     <span class="mtext">Seans Takibi</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==17)
                     <a href="/isletmeyonetim/senetler" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/senetler" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-clipboard2"></span>
                     <span class="mtext">Senet Takibi</span>
                     </a>
                  </li>
                  <li> 
                  @if(!Auth::user()->hasRole('Sekreter'))
                     @if($pageindex==103)
                     <a href="/isletmeyonetim/kasadefteri" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/kasadefteri" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon dw dw-money-2"></span>
                     <span class="mtext">Kasa Raporu</span>
                     </a>
                  </li>
                  @endif
                  <li> 
                     @if($pageindex==15)
                     <a href="/isletmeyonetim/masraflar" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/masraflar" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-upload"></span>
                     <span class="mtext">Masraflar</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==16)
                     <a href="/isletmeyonetim/alacaklar" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/alacaklar" class="dropdown-toggle no-arrow">
                     @endif
                     <span class=" micon  bi bi-download"></span>
                     <span class="mtext">Alacak Takibi</span>
                     </a>
                  </li>

                 <li> 
                     @if($pageindex==106)
                      <a href="/isletmeyonetim/toplusms" class="dropdown-toggle no-arrow active">
                        @else
                     <a href="/isletmeyonetim/toplusms" class="dropdown-toggle no-arrow">
                        @endif
                     <span class="micon dw dw-message"></span>
                     <span class="mtext">SMS Yönetimi</span>
                     </a>
                  </li>
                  <li>  
                     @if($pageindex==9)
                     <a href="/isletmeyonetim/ayarlar?p=temelbilgiler" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ayarlar?p=temelbilgiler" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon dw dw-settings1"></span>
                     <span class="mtext">Ayarlar</span>
                     </a>
                  </li>

                  @endif
                  @if(Auth::user()->hasRole('Personel'))
                  <li>  
                     @if($pageindex==105)
                     <a href="/isletmeyonetim/personeldetay/{{Auth::user()->personel_id}}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/personeldetay/{{Auth::user()->personel_id}}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-pie-chart"></span>
                     <span class="mtext">Raporlar</span>
                     </a>
                  </li>
                  @endif
               </ul>
            </div>
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
                  {{$isletme->salon_adi}} &copy;. Her Hakkı Saklıdır. Tasarım : 
                  <a href="#" target="_blank"
                     ><img src='/public/yeni_panel/vendors/images/randevumcepte.png' style="height: 30px;"></a
                     >
               </div>
            </div>
         </div>
         <!-- welcome modal end -->
         <!-- js -->
         <script src="{{asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
         <script src="{{asset('public/yeni_panel/vendors/scripts/script.js?v=8.0')}}"></script>
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
             timeZone:'Europe/Istanbul',

             nowIndicator:true,
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
             
              
              
             businessHours: <?php echo json_encode($randevular["calismasaatleri"]) ?>,
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
            minTime: '06:00:00',
             //// uncomment this line to hide the all-day slot
             allDaySlot: false,
             slotDuration: '00:15:00',
             height:768,
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
             },
             dayClick: function (start) {
               var tarihsaattext = start.format().split("T");
              
                  
               if(new Date(start.format()) < new Date())
                     swal(
                            {
                                type: 'warning',
                                title: 'Uyarı',
                                text: 'Geçmiş tarih / saat için randevu oluşturulamaz!',
                                showCloseButton: false,
                                showCancelButton: false,
                                showConfirmButton:false,
                            }
                     );
               else{
                   
                  $('#randevutarihiyeni').val(tarihsaattext[0]);

                  randevusaatlerinigetir(tarihsaattext[0],$('input[name="sube"]').val(),tarihsaattext[1]);
                  
                   

                  jQuery("#modal-view-event-add").modal();
               }
            
             },

             eventResize: function(event) {
               clearInterval(interval);
               eventGuncelle(event);
             },
             eventDrop: function(event, delta, revertFunc) {
               
                console.log('Drop');
                console.log(event);
                eventGuncelle(event);
                
            
             },
             eventDragStart: function (event, jsEvent, view){
               clearInterval(interval);
               console.log('Event drag start');
               console.log(event);
             },
             eventDragStop: function( event, jsEvent, view){
                console.log('drag stop');
                console.log(event);
                //interval = setInterval(takvimyukle.bind(false), 10000);
             },
             resourceChange: function(resource, oldResource, revert){
            
               console.log('resource change');
               console.log(resource)
            },
             eventClick: function (event, jsEvent, view) {
                var randevuid = event.randevu_id;
                if(event.userid==2012)
                {
                    swal({
                        title: "Emin misiniz?",
                        text: "Bu kapalı saat kaydını silmek istediğinize emin misiniz? Bu işlem geri alınamaz",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#00bc8c',
                        confirmButtonText: 'Saat Kapamayı Kaldır',
                        cancelButtonText: "Vazgeç",
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonClass: 'btn btn-danger',
                               
                    }).then(function (result) {
                        
                        if(result.value){
                            kapalisaatsil(randevuid);
                                
                            
            
                        }
                     
                        
                              
                    
                    });
                }
               else{
                  jQuery(".event-icon").html("<i class='fa fa-" + event.icon + "'></i>");
                  jQuery(".event-title").html(event.title+" Randevu Detayı");
                  jQuery(".event-body").html(event.description);
                  jQuery(".event-buttons").html(event.eventbuttons);
                  jQuery(".eventUrl").attr("href", event.url);
                  jQuery('input[name="randevuhizmettarih"]').datepicker({
                     minDate: new Date(),
                     timepicker: true,
                     language: "tr",
                     autoClose: true,
                     dateFormat: "yyyy-mm-dd",
                     timeFormat:  "hh:mm",
                    
            
                  });
            
                  jQuery("#modal-view-event").modal();
               }
              
             },
             
            });
            
            $('html, body').animate({ scrollTop:  $('tr[data-time="<?php echo date('H:00:00',strtotime('-1 hours',strtotime(date('Y-m-d H:i'))))?>"]').offset().top }, 'slow');
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
               
            <?php } ?>
            });
         </script>
         @endif
         @if($pageindex==4)
         <script>
            $(document).ready(function(){
               if($('#musteri_tablo').length){
                    var sadiktablo = $('#musteri_tablo_sadik').DataTable({
                          
                            autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'ad_soyad',name: 'ad_soyad' },
                           { data: 'telefon' ,name: 'telefon'},
                           { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                           { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                           { data: 'randevu_sayisi',name: 'randevu_sayisi' },    
                           {data : 'islemler'},
                       ],
                       data: <?php echo $sadik_musteriler; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
                     });
                     var musteritablo = $('#musteri_tablo').DataTable({
                           autoWidth: false,
                       responsive: true,
                           
                          columns:[
                              { data: 'ad_soyad',name: 'ad_soyad' },
                              { data: 'telefon' ,name: 'telefon'},
                              { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                              { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                              { data: 'randevu_sayisi',name: 'randevu_sayisi' },    
                              {data : 'islemler'},
                          ],
                          data: <?php echo $musteriler; ?>,
               
                          "language" : {
                              "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                              searchPlaceholder: "Ara",
                              paginate: {
                                  next: '<i class="ion-chevron-right"></i>',
                                  previous: '<i class="ion-chevron-left"></i>'  
                              }
                          },
            
                        
                      
            
                     });
                     var aktiftablo = $('#musteri_tablo_aktif').DataTable({
                          
                            autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'ad_soyad',name: 'ad_soyad' },
                           { data: 'telefon' ,name: 'telefon'},
                           { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                           { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                           { data: 'randevu_sayisi',name: 'randevu_sayisi' },    
                           {data : 'islemler'},
                       ],
                       data: <?php echo $aktif_musteriler; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
                     });
                     var pasiftablo = $('#musteri_tablo_pasif').DataTable({
                          
                            autoWidth: false,
                       responsive: true,
                        columns:[
                           { data: 'ad_soyad',name: 'ad_soyad' },
                           { data: 'telefon' ,name: 'telefon'},
                           { data: 'kayit_tarihi',name: 'kayit_tarihi' }, 
                           { data: 'son_randevu_tarihi',name: 'son_randevu_tarihi' },
                           { data: 'randevu_sayisi',name: 'randevu_sayisi' },    
                           {data : 'islemler'},
                        ],
                        data: <?php echo $pasif_musteriler; ?>,
            
                        "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                        }, 
                     });
                     pasiftablo.columns.adjust().draw();
                     aktiftablo.columns.adjust().draw();
                     musteritablo.columns.adjust().draw();
                     sadiktablo.columns.adjust().draw();
                  }
               });
              
             
         </script>
          @endif
            @if($pageindex==40)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#ajanda_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 4, "desc" ]],
                      columns:[
                        
                         { data: 'title'   },
                         { data: 'description' },
                           
                         { data: 'ajanda_hatirlatma'   },
                       
            
                       
                         { data: 'start' },
                            
                         { data: 'ajanda_durum' },
            
                        
                         { data: 'ajanda_olusturan' },
                        
                        
                         { data: 'islemler' }
                          
                     
                         
                      ],
                      data: <?php echo  $ajanda['ajanda']; ?>,
            
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
            
            
            
              var tarih = getQueryStrings()["ajanda_tarih"];
            
            
              if(tarih){
                 
                  tarih = new Date(tarih);
              } 
              else{
               
                  tarih = new Date();
               }
            
            $('#calendar_ajanda').fullCalendar({
             timeZone:'Europe/Istanbul',
            
            dayClick: function () {
            
            jQuery("#yeni_ajanda_ekle").modal();
            },
             nowIndicator:true,
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
            
            
             defaultView: 'agendaWeek',
             defaultDate: tarih,
             editable: false,
             selectable: true,
             events:<?php echo json_encode($ajanda['ajanda'])?>,
             eventLimit: true, // allow "more" link when too many events
             header: {
               left: 'prev,next today',
               center: 'title',
               right: 'month,agendaWeek,agendaDay'
             },
            minTime: '06:00:00',
             //// uncomment this line to hide the all-day slot
             allDaySlot: false,
             slotDuration: '00:15:00',
             height:700,
            
            
             timeFormat: 'H:mm',
             views: {
                 agenda: {
                     slotLabelFormat: 'H:mm',
                 }
             },
            
            
             businessHours: false,
             
            select: function(start, end, jsEvent, view) {
               console.log(
                 'select',
                 start.format(),
                 end.format(),
            
              
               );
               
             },
       
            
             eventClick:function(event,jsEvent, view){
               updateState(event.id);
               jQuery(".event-icon").html("<i class='fa fa-" + event.icon + "'></i>");
               jQuery(".event-title").html(event.title+" Not Detayı");
               jQuery(".event-body").html("<div class='row' ><b style='margin-left:20px;'>İçerik :</b> <p style='margin-left:20px;'>"+event.description+"</p></div> <div class='row' ><b style='margin-left:20px;'>Tarih :</b> <p style='margin-left:23px;'>"+event.start.format('DD/MM/YYYY')+"</p></div> </div> <div class='row' ><b style='margin-left:20px;'>Saat :</b> <p style='margin-left:30px;'>"+event.start.format('H:mm')+"</p></div>");

               jQuery(".event-buttons").html(event.eventbuttons);
               jQuery(".eventUrl").attr("href", event.url);
               jQuery("#ajandadetayigetir").trigger('click');
             }
            
            
             
            });
                     
            
            
            
            
            });
         </script>
         @endif
         @if($pageindex==41)
         <script type="text/javascript">
            $(document).ready(function () {
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
               var adisyontablo = $('#adisyon_liste').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'acilis_tarihi'},
                           { data: 'musteri'},
                           { data: 'satis_turu'},
                           { data: 'icerik'},
                           
                          
                           {data : 'toplam'},
                           {data : 'odenen'},  
                           {data : 'kalan_tutar'},
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
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
          @if($pageindex==105 )
         <script type="text/javascript">
            $(document).ready(function () {
               $('#adisyon_liste_personel').DataTable().destroy();
               var adisyontablo = $('#adisyon_liste_personel').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'acilis_tarihi'},
                           { data: 'musteri'},
                           { data: 'satis_turu'},
                           { data: 'icerik'},
                           
                          
                           {data : 'toplam'},
                           {data : 'odenen'},  
                           {data : 'kalan_tutar'},
                           {data : 'hakedis'},
                       ],
                       "order": [[ 0, "asc" ]],
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
         @if($pageindex==41 )
         <script type="text/javascript">
            $(document).ready(function () {
               $('#adisyon_liste_musteri').DataTable().destroy();
               $('#adisyon_liste_musteri').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           { data: 'acilis_tarihi'},
                           
                           { data: 'satis_turu'},
                           { data: 'icerik'},
                           
                          
                           {data : 'toplam'},
                           {data : 'odenen'},  
                           {data : 'kalan_tutar'},
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
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
         @if($pageindex==13)
         <script type="text/javascript">
            $(document).ready(function(){
                  $('#paket_liste').DataTable().destroy();
                  $('#paket_liste').DataTable({
                          autoWidth:false,
                          responsive:true,
                           
                       columns:[
                       {data:'id'},
                           { data: 'paket_adi' },
                           
                           { data: 'hizmetler' }, 
                           { data: 'seanslar' }, 
                           { data: 'fiyat' },
                               
                           {data : 'islemler'},
                       ],
                       data: <?php echo $paketler["paket_liste"]; ?>,
            
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
         @if($pageindex==30)
         <script type="text/javascript">
            $(document).ready(function(){
               $('#urun_liste').DataTable().destroy();
               $('#urun_liste').DataTable({
                          autoWidth:false,
                          responsive:true,
                           
                       columns:[
                       {data:'id'},
                              { data: 'urun_adi',name: 'urun_adi' },
                              { data: 'stok_adedi' ,name: 'stok_adedi'},

                              { data: 'fiyat',name: 'fiyat' }, 
                              { data: 'barkod',name: 'barkod' },
                               { data: 'dusuk_stok_siniri'}, 
                              {data : 'islemler'},
                       ],
                       data: <?php echo $urunler["urun_liste"]; ?>,
            
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
               
               
               $('#hizmet_liste').DataTable().destroy();
               $('#hizmet_liste').DataTable({
                          autoWidth:false,
                          responsive:true,
                           
                       columns:[
                           { data: 'hizmet_adi' },
                           { data: 'personel' },
                           { data: 'islemler' }, 
                            
                       ],
                       data: <?php echo $hizmetler["hizmet_liste"] ?>,
            
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
                 $('#musteri_yorumlari').DataTable({
                     autoWidth: false,
                          responsive: true,
                       columns:[
                           { data: 'tarih'   },
                           { data: 'musteri' },
                           { data: 'yorum' },
                          { data: 'puan' },
                       ],
                       "order": [[ 0, "desc" ]],
                       data: <?php echo $salonyorumlar; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                     },
               });
               $('#adisyon_liste_ozet_urun').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           
                           { data: 'musteri'},
                           { data: 'icerik' }, 
                           { data: 'urun_satan'},
                           { data: 'toplam' },
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
                       data: <?php echo $gunluk_urun_satislari; ?>,
            
                       "language" : {
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                       },
            
                        
                      
            
               });
               $(document).ready(function(){
                 $('#on_gorusme_liste_gunluk').DataTable({
                    autoWidth: false,
                     responsive: true,
                       "order": [[ 1, "asc" ]],
                      columns:[
                         {data:'id'},
                          { data: 'olusturulma'   },
                          { data: 'musteri'   },
                          { data: 'musteri_tipi'   },

                          { data: 'telefon' },
                           
                          
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
                $('#adisyon_liste_ozet_paket').DataTable({
                       autoWidth: false,
                       responsive: true,
                       columns:[
                           
                           { data: 'musteri'},
                           { data: 'icerik' }, 
                           { data: 'paket_satan'},
                           { data: 'toplam' },
                           {data : 'islemler'},
                       ],
                       "order": [[ 0, "asc" ]],
                       data: <?php echo $gunluk_paket_satislari; ?>,
            
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
                $('#etkinlik_liste_ozet').DataTable({
                     autoWidth: false,
                          responsive: true,
                       columns:[
                           { data: 'etkinlik_adi'   },
                           { data: 'katilimci_sayisi' },
                           { data: 'toplam_tutar' }
                          
                       ],
                       data: <?php echo $etkinlikler; ?>,
            
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
                           { data: 'musteri'  },
                             { data: 'planlanan_odeme_tarihi' },
                           { data: 'tutar' },
                         
                          
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
               $('#randevu_liste_tum').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
            
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_tum; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });
                   $('#randevu_liste_salon').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_salon; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });   
                   $('#randevu_liste_web').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_web; ?>,
            
                           "language" : {
                               "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                               searchPlaceholder: "Ara",
                               paginate: {
                                   next: '<i class="ion-chevron-right"></i>',
                                   previous: '<i class="ion-chevron-left"></i>'  
                           }
                        },
                  });  
                   $('#randevu_liste_uygulama').DataTable({
                           autoWidth: false,
                           responsive: true,
                           "order": [[ 3, "asc" ]],
                           columns:[
                         
                              { data: 'musteri'   },
                              { data: 'telefon' },
                                
                              { data: 'hizmetler'   },
                              { data: 'tarih' },
                            
                              { data: 'saat' },
                                 
                              { data: 'durum' },
            
                             
                              { data: 'olusturan' },
                             
                             
                              { data: 'islemler' }
                               
                          
                              
                           ],
                           data: <?php echo $randevular_uygulama; ?>,
            
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

         @if($pageindex==111)
         

         <script type="text/javascript">
            $(document).ready(function(){
             
              if($('#adisyon_form').length){
                  
                  let total = 0;
                  $('input[name="hizmet_fiyati_adisyon"]').each(function(){
                    
                     total += parseFloat($(this).val()) || 0;
            
            
                  });
                   $('input[name="urun_fiyati_adisyon[]"]').each(function(){
                    
                     total += parseFloat($(this).val()) || 0;
                     
                  });
                  $('input[name="paket_fiyati_adisyon[]"]').each(function(){
                    
                     total += parseFloat($(this).val()) || 0;
                     
                  });
                  $('#hizmet_urunler_toplam_fiyat').empty();
                  var currency_symbol = "₺";
                  var formattedOutput = new Intl.NumberFormat('tr-TR', {
                   style: 'currency',
                   currency: 'TRY',
                   minimumFractionDigits: 2,
                  });
                   
                  $('#hizmet_urunler_toplam_fiyat').append(formattedOutput.format(total).replace(currency_symbol, ''));
                  $('#tahsilat_tutari').val(0);
                  $('#birim_tutar').val(formattedOutput.format(total-<?php echo $tahsil_edilen; ?>).replace(currency_symbol, ''));
                 
                  $('#tahsilat_tutari_numeric').val(total);

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
                     columns:[
                         {data:'ad_soyad'},
                          { data: 'hesap_turu'   },
                          { data: 'telefon' },
                           { data: 'durum'},
                           { data: 'islemler'   },
                        
                          
                     
                         
                      ],
                      data: <?php echo $personeller;?>,
                 });
             }
            });
             var hizmet_sec_tablo = $('#hizmet_sec_tablo').DataTable({
                    autoWidth:false,
                    responsive:true,
                    paging:false,
                    ordering: false,
                    "dom":"lrtip",
            
                    "drawCallback": function() {
                       $(this.api().table().header()).hide();
                    },  
            
                    "language" : {
                    "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
            
                       
                     
            
              });
            
             $('#hizmet_ara').keyup(function(){  
              hizmet_sec_tablo.search($(this).val()).draw();   // this  is for customized searchbox with datatable search feature.
            });
             $('#ozel_hizmet_kategorileri').DataTable({
                    autoWidth:false,
                    responsive:true,
                    paging:false,
                    ordering: false,
                    "dom":"lrtip",
            
                    "drawCallback": function() {
                       $(this.api().table().header()).hide();
                    },  
            
                    "language" : {
                    "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                    },
            
                       
                     
            
              });
            
            
            
         </script>
         @endif
         @if($pageindex==12)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#on_gorusme_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                       "order": [[ 1, "dsc" ]],
                      columns:[     
                         {data:'id'},
                          { data: 'olusturulma'   },
                          { data: 'musteri'   },
                          { data: 'musteri_tipi'   },

                          { data: 'telefon' },
                           
                          
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
                         { data: 'baslangic_tarihi' },
                           
                         { data: 'paket_adi'   },
                         { data: 'durum', "width": "250px"},
                       
                         
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
        
         @if($pageindex==3)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#randevu_liste').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 4, "desc" ]],
                      columns:[
                        
                         { data: 'musteri'   },
                         { data: 'telefon' },
                           
                         { data: 'hizmetler'   },
                          { data: 'odalar'   },
                         { data: 'tarih' },
                       
                         { data: 'saat' },
                            
                         { data: 'durum' },
            
                        
                         { data: 'olusturan' },
                        
                        
                         { data: 'islemler' }
                          
                     
                         
                      ],
                      data: <?php echo $randevular_liste; ?>,
            
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
         @if($pageindex==15)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#masraf_tablo').DataTable({
                    autoWidth: false,
                     responsive: true,
                      columns:[
                        
                         { data: 'tarih'   },
                         { data: 'kategori' },
                           
                         { data: 'aciklama'   },
                          { data: 'tutar'   },
                         { data: 'masraf_sahibi' },
                       
                        
                            
                         { data: 'odeme_yontemi' },
            
                        
                       
                        
                        
                         { data: 'islemler' }
                          
                     
                         
                      ],
                      data: <?php echo $masraflar; ?>,
                     "order": [[ 0, "desc" ]],
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
          @if($pageindex==16)
         <script type="text/javascript">
            $(document).ready(function(){
                 $('#alacaklar').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                      data: <?php echo $alacaklar['alacaklar']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
           
              
                 $('#alacaklar_hizmet').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                     data: <?php echo $alacaklar['alacaklar_hizmet']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
           
                
                 $('#alacaklar_urun').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                     data: <?php echo $alacaklar['alacaklar_urun']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
           
                  
                 $('#alacaklar_paket').DataTable({
                    autoWidth: false,
                     responsive: true,
                      "order": [[ 0, "dsc" ]],
                      columns:[ 
                       { data: 'olusturulma' },  
                         { data: 'musteri'   },
                          { data: 'icerik'   },
                         { data: 'tutar' }, 
                         { data: 'planlanan_odeme_tarihi'   },
                          
                        
                         { data: 'islemler' }  
                      ],
                     data: <?php echo $alacaklar['alacaklar_paket']; ?>,
            
                      "language" : {
                          "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                          searchPlaceholder: "Ara",
                          paginate: {
                              next: '<i class="ion-chevron-right"></i>',
                              previous: '<i class="ion-chevron-left"></i>'  
                          }
                       },
                 });
          
            
            $('#tum_taksitler').DataTable().destroy();
            $('#tum_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['tum_taksitler']; ?>,
         
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
         
                     
                   
         
            });

            $('#acik_taksitler').DataTable().destroy();
            $('#acik_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['taksitler_acik']; ?>,
         
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
         
                     
                   
         
            });

            $('#kapali_taksitler').DataTable().destroy();
            $('#kapali_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['taksitler_kapali']; ?>,
         
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
         
                     
                   
         
            });

            $('#gecikmis_taksitler').DataTable().destroy();
            $('#gecikmis_taksitler').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $alacaklar['taksitler_odenmemis']; ?>,
         
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
                
              
              // $('#masraf_duzenle_modal').DataTable();
              var musteri_sec_tablo = $('#musteri_sec_tablo').DataTable({
                     autoWidth:false,
                     responsive:true,
                     paging:false,  
                     "language" : {
                     "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                     },
            
                        
                      
            
               });
              $('#katilimci_ara').keyup(function(){  
               musteri_sec_tablo.search($(this).val()).draw();   // this  is for customized searchbox with datatable search feature.
             });
            
            });
         </script>
         <!-- Google Tag Manager (noscript) -->
         </script>
         <!-- End Google Tag Manager (noscript) -->
         
         <div
            id="modal-view-event-add"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width: 1000px;">
               <div class="modal-content" style="min-height:467px">
                  <div class="modal-header">
                      <h4 class="h4">
                        <span>Yeni</span>
                     </h4>
                     <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>

                  <div class="modal-body" style="
    padding: 1rem 1rem 0 1rem;
">
                      
                     
                     
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
                           @if(!Auth::user()->hasRole('Personel'))
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
                           @endif
                        </ul>
                        <div class="tab-content">
                           <div
                              class="tab-pane fade show active"
                              id="yeni-randevu"
                              role="tabpanel"
                              >
                              <div class="pd-10">
                                 <form id="yenirandevuekleform"  method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-md-4 col-sm-8 col-xs-8 col-8">
                                          <input type="hidden" name="sube" value="{{$isletme->id}}">
                                          @if($pageindex==2)
                                             <input type="hidden" name="takvim_sayfasi" value="1">
                                          @endif
                                         
                                             <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                                             <select name="adsoyad" class="form-control custom-select2" style="width: 100%;">
                                                @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                                                <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                                @endforeach
                                             </select>
                                         
                                       </div>
                                       <div class="col-md-2 col-sm-4 col-xs-4 col-4">
                                       
                                             <label style="visibility: hidden;width: 100%;">yenimüşteri</label>
                                             <button class="btn btn-primary yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal"><i class="icon-copy fi-plus"></i>Yeni @if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</button>
                                       
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                          
                                             <label>Tarih</label>
                                             <input required placeholder="Tarih"
                                                type="text"
                                                class="form-control"
                                                name="tarih" id="randevutarihiyeni" autocomplete="off"
                                                />
                                       
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                         
                                             <label>Saat </label>
                                             <select id='randevu_saat' name="saat" class="form-control">
                                                <?php $secanahtar=1; ?>
                                             @for($j = strtotime(date('00:00')) ; $j < strtotime(date('23:59')); $j+=(5*60)) 
                                                   
                                                   @if( $j< strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('baslangic_saati')) )) || $j >= strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('bitis_saati')) )) || $j < strtotime(date('H:i')) )

                                                
                                                       @if($j<=strtotime(date('H:i')))
                                                            <option disabled style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                      @else
                                                            <option style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                      @endif 
                                                   @else

                                                      <option {{($secanahtar==1) ? 'selected': ''}} value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                       <?php $secanahtar++; ?>
                                                   @endif

                                                @endfor 
                                             </select>
                                             
                                      
                                       </div>
                                       <div class="col-md-12">
                                             <label>Personel Notu</label>
                                             <textarea class="form-control" name="personel_notu" placeholder="Notlar"></textarea>
                                         
                                       </div>
                                    </div>
                                     
                                   
                                    <div class="hizmetler_bolumu" style="margin-top:20px">
                                       <div class="row" data-value="0" style=" background: #e2e2e2;margin: 5px 0 5px 0;">
                                          <div class="col-md-2 col-sm-6 col-xs-6 col-6">
                                
                                                <label>Personel</label>
                                                <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect" style="width: 100%;">
                                                   <option></option>
                                                   @if(Auth::user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                                   @else
                                                      @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)

                                                      <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                                      @endforeach
                                                      
                                                   @endif
                                                </select>
                                           
                                          </div>
                                          <div class="col-md-2 col-sm-6 col-xs-6 col-6">
                                             <label>Cihaz</label>
                                             <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect" style="width: 100%;">
                                                   
                                                      <option></option>
                                                      @foreach(\App\Cihazlar::where('salon_id',$isletme->id)->where('durum',true)->where('aktifmi',true)->get() as $cihaz)
                                                      <option value="{{$cihaz->id}}">{{$cihaz->cihaz_adi}}</option>
                                                      @endforeach
                                                 
                                             </select>
                                          </div>
                                          <div class="col-md-2 col-sm-6 col-xs-6 col-6">
                                       
                                                <label>Hizmet</label>
                                                <select name="randevuhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                                   @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->where('aktif',true)->get() as $hizmetliste)
                                                   <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                                   @endforeach
                                                </select>
                                             
                                          </div>
                                          <div class="col-md-2 col-sm-6 col-xs-6 col-6">
                                          
                                                <label>Oda (opsiyonel)</label>
                                                <select name="randevuodalariyeni[]"  class="form-control opsiyonelSelect" style="width:100%">
                                                   <option></option>
                                                   @foreach(\App\Odalar::where('salon_id',$isletme->id)->where('durum',true)->where('aktifmi',true)->get() as $oda)
                                                   <option value="{{$oda->id}}">{{$oda->oda_adi}}</option>
                                                   @endforeach
                                                </select>
                                            
                                          </div>
                                          <div class="col-md-1 col-sm-6 col-xs-6 col-64">
                                             
                                                <label>Süre</label>
                                                <input type="tel" class="form-control" name="hizmet_suresi[]" value="{{(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->first() !== null) ? \App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->value('sure_dk') : ''}}">
                                            
                                          </div>
                                          <div class="col-md-1 col-sm-6 col-xs-6 col-6">
                                             
                                                <label>Fiyat</label>
                                                <input type="tel" class="form-control" name="hizmet_fiyat[]" value="{{
                                                   (\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->first()!==null) ? \App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->value('baslangic_fiyat') : ''}}">
                                         
                                          </div>
                                         
                                          <div class="col-md-1  col-sm-6 col-xs-6 col-6">
                                            
                                                <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                                <button type="button" name="hizmet_formdan_sil"  data-value="0" class="btn btn-danger" disabled style="padding:1px; border-radius: 0; line-height: 1px ; font-size:18px;background-color: transparent; border-color: transparent;color:#dc3545"><i class="icon-copy fa fa-remove"></i></button>
                                            
                                          </div>
                                           <div class="col-md-1 col-sm-6 col-xs-6 col-6">
                                            
                                                <label style="visibility:visible;font-size:12px;width:100%" class='usttekiylebirlestiryazi'  >Üsttekiyle Birleştir</label>
                                                <div class="custom-control custom-checkbox mb-5">
                                                   <input type="checkbox" class="custom-control-input" name="birlestir0" disabled style="display:none" id="customCheck0"/>
                                                   <label class="custom-control-label" name="birlestir_label" for="customCheck0" style="display:none"></label>
                                                </div>
                                          
                                          </div>
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-6 col-sm-6 col-xs-6 ">

                                          <div class="row">
                                             <div class="col-md-4 col-xs-4 col-sm-4 col-4">
                                             
                                                   <label>Tekrarlayan</label><br>
                                                   <label class="switch">
                                                   <input id="tekrarlayan" name="tekrarlayan" type="checkbox">
                                                   <span class="slider"></span>
                                                   </label> 
                                              
                                             </div>
                                             <div class="col-md-4 col-xs-4 col-sm-4 col-4">
                                               
                                                   <label>Tekrar Sıklığı</label>
                                                   <select class="form-control tekrar_randevu" name="tekrar_sikligi" disabled>
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
                                             <div class="col-md-4 col-xs-4 col-sm-4 col-4">
                                               
                                                   <label>Tekrar Sayısı</label>
                                                   <input type="tel" name="tekrar_sayisi" class="form-control tekrar_randevu" required value="0" disabled>
                                              
                                             </div>
                                          </div>
                                         
                                       </div>
                                       <div class="col-md-6 col-sm-6 col-xs-6">
                                         <div class="row">
                                           <div class="col-md-6 col-6 ">
                                      
                                             <label style="visibility:hidden;">Bir Hizmet</label>
                                             <button type="button" id="bir_hizmet_daha_ekle" class="btn btn-primary">
                                             Bir Hizmet Daha Ekle
                                             </button>
                                       
                                         
                                       </div>

                                       <div class="col-md-6 col-6">
                                         
                                             <label style="visibility:hidden;">Randevu Oluştur</label>
                                             <button type="submit" class="btn btn-success btn-lg">Randevu Oluştur</button>
                                        
                                       </div>
                                         </div>
                                       </div>

                                       
                                    </div>
                                   
                                 </form>
                              </div>
                           </div>
                            @if(!Auth::user()->hasRole('Personel'))
                           <div class="tab-pane fade" id="saat-kapama" role="tabpanel">
                              <div class="pd-20">
                                 <form id="saat_kapama" method="POST">
                                    <input type="hidden" value="{{$isletme->id}}" name="sube">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-md-3">
                                         
                                             <label>Personel</label>
                                             <select name="personel" class="form-control custom-select2" style="width: 100%;">
                                                @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                                <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                                @endforeach
                                             </select>
                                      
                                       </div>
                                       <div class="col-md-3">
                                          
                                             <label>Tarih</label>
                                             <input type="text" required class="form-control date-picker" name="tarih" autocomplete="off" >
                                         
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='baslangic_saati_yazi'>
                                          
                                             <label>Başlangıç Saati</label>
                                             <input type="time" class="form-control" name="saat" id='kapama_saat_baslangic' required>
                                        
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='bitis_saati_yazi'>
                                         
                                             <label>Bitiş Saati</label>
                                             <input type="time" class="form-control" name="saat_bitis" id='kapama_saat_bitis' required>
                                        
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                        
                                             <label>Tüm gün</label><br>
                                             <label class="switch" >
                                             <input type="checkbox" name="tum_gun" id="tum_gun">
                                             <span class="slider"></span>
                                             </label>
                                         
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                         
                                             <label>Tekrarlayan</label><br>
                                             <label class="switch" >
                                             <input id="tekrarlayan_saat_kapama" name="tekrarlayan" type="checkbox">
                                             <span class="slider"></span>
                                             </label> 
                                         
                                       </div>
                                    
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                          
                                             <label>Tekrar Sıklığı</label>
                                             <select class="form-control tekrar_saat_kapama" name="tekrar_sikligi" disabled >
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
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                         
                                             <label>Tekrar Sayısı</label>
                                             <input type="tel" name="tekrar_sayisi" class="form-control tekrar_saat_kapama" required value="0" disabled>
                                          
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-12">
                                         
                                             <label>Notlar</label>
                                             <textarea name="personel_notu" class="form-control"></textarea>
                                          
                                       </div>
                                       <div class="col-md-12">
                                          <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           @endif
                        </div>
                     </div>
                  </div>
                   
               </div>
               </form>
            </div>
         </div>
         <div
            class="modal fade bs-example-modal-lg"
            id="modal-view-event"
            role="dialog"
            aria-labelledby="myLargeModalLabel"
            aria-hidden="true"
            >
            <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content" style="width:100%">
                  <div class="modal-header">
                     <h4 class="h4">
                        <span class="event-icon weight-400 mr-3"></span
                           ><span class="event-title"></span>
                     </h4>
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
                     <div class="event-body">
                     </div>
                  </div>
                  <div class="modal-footer event-buttons" style="display:block;">
                  </div>
               </div>
            </div>
         </div>
         <div
            id="randevu-duzenle-modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width: 1000px;">
               <div class="modal-content">
                  <div class="modal-body">
                     <h2 class="text-blue h2 mb-10">Randevu Düzenle</h2>
                     <form id="randevuduzenleform"  method="POST">
                        {!!csrf_field()!!}
                        <input type="hidden" name="randevu_id" id='duzenlenecek_randevu_id'>
                         @if($pageindex==2)
                                             <input type="hidden" name="takvim_sayfasi" value="1">
                                          @endif
                        <div class="row">
                           <div class="col-md-4">
                              <div class="form-group">
                                 <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                                 <select name="adsoyad" id='randevuduzenle_musteri_id' class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                                    <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                             <label style="visibility: hidden;width: 100%;">yenimüşteri</label>
                                             <button class="btn btn-primary yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal"><i class="icon-copy fi-plus"></i>Yeni @if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</button>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Tarih</label>
                                 <input required placeholder="Tarih"
                                    type="text"
                                    class="form-control"
                                    name="tarih" 
                                    id='randevuduzenle_tarih' autocomplete="off"
                                    />
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Saat</label>
                                 <select name="saat" class="form-control" id="randevuduzenle_saat">
                                      <?php $secanahtar=1; ?>
                                      @for($j = strtotime(date('00:00')) ; $j < strtotime(date('23:59')); $j+=($isletme->randevu_saat_araligi*60)) 
                                                 
                                                   @if( $j< strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('baslangic_saati')) )) || $j >= strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('bitis_saati')) )) || $j < strtotime(date('H:i')) )

                                                
                                                       @if($j<=strtotime(date('H:i')))
                                                            <option disabled style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                      @else
                                                            <option style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                      @endif 
                                                   @else

                                                      <option {{($secanahtar==1) ? 'selected': ''}} value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                       <?php $secanahtar++; ?>
                                                   @endif

                                                @endfor 
                                 </select>
                                 
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="form-group">
                                 <textarea class="form-control" name="personel_notu" id='randevuduzenle_personel_notu' placeholder="Notlar"></textarea>
                              </div>
                           </div>
                        </div>
                        
                        <div class="hizmetler_bolumu_randevu_duzenleme">
                        </div>
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                              <div class="form-group">
                                 <button type="button"  id='bir_hizmet_daha_ekle_randevu_duzenleme' class="btn btn-secondary btn-lg btn-block">
                                 Bir Hizmet Daha Ekle
                                 </button>
                              </div>
                           </div>
                        
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                              <div class="form-group">
                                 <button type="submit" class="btn btn-success btn-lg btn-block">Randevuyu Güncelle</button>
                              </div>
                           </div>
                        </div>
                     </form>
                  </div>
                  
               </div>
            </div>
         </div>
         <div
         id="yeni_senet_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="senet_formu"  method="POST">
                  <div class="modal-header" style="display:block">
                    
                        <div class="row">
                           <div class="col-xs-6 col-6">
                                <h2 style="float: left;">Yeni Senet</h2>
                           </div>
                           <div class="col-xs-2 col-2">
                              
                                 <button type="button" data-toggle="modal" data-target="#senet_yeni_hizmet_modal" class="btn btn-outline-primary btn-lg btn-block" style="font-size:12px">Hizmet Ekle</button> 
                             
                           </div>
                           <div class="col-xs-2 col-2">
                              <button type="button" data-toggle="modal" data-target="#senet_yeni_urun_modal" class="btn  btn-outline-primary btn-lg btn-block" style="font-size:12px">Ürün Ekle</button>
                           </div>
                           <div class="col-xs-2 col-2">
                              <button type="button" data-toggle="modal" data-target="#paket_satisi_modal_senet"  class="btn  btn-outline-primary btn-lg btn-block" style="font-size:12px">Paket Ekle</button>
                           </div>
                        </div>  
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     @if($pageindex==111)
                     <input type="hidden" name="adisyon_id" value="{{$adisyon->id}}">
                     @endif


                     <div id="hizmetler_bolumu_senet">
                         
                          
                     </div>
                     <div id='urunler_bolumu_senet'>
                        
                     </div>
                     <div id='paketler_bolumu_senet'>
                        

                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           
                              <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                              <select name="ad_soyad" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                                 @if($pageindex==111)
                                 @if($adisyon->user_id == $mevcutmusteri->user_id)
                                 <option selected value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @else
                                 <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @endif
                                 @else
                                 <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @endif
                                 @endforeach
                              </select>
                          
                        </div>
                        <div class="col-md-3">
                       
                              <label>Vade Başlangıç Tarihi</label>
                              <input type="text" required class="form-control date-picker" name="vade_baslangic_tarihi" autocomplete="off">
                      
                        </div>
                        <div class="col-md-3">
                          
                              <label>Vade (Ay)</label>
                              <input type="tel" required name="vade" value=" " class="form-control">
                           
                        </div>
                     </div>
                     <div class="row" style='background: #e2e2e2; margin:10px 0 10px 0;padding-bottom: 10px;'>
                        <div class="col-md-6" >
                           <label>Ön Ödeme Tutarı</label>
                           <input type="tel" required {{($pageindex==111) ? 'disabled': ''}} name="on_odeme_tutari" id='on_odeme_tutari' value="" class="form-control try-currency">
                        </div>
                        <div class="col-md-6">
                           <label>Ön Ödeme Türü</label>
                           <select name="on_odeme_turu" class="form-control" >
                              @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                                 <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                      
                              <label>Senet Tutarı</label>
                              <input type="tel" required {{($pageindex==111) ? 'disabled': ''}} name="senet_tutar" id='senet_tutar' value="" class="form-control try-currency">
                        
                        </div>
                        <div class="col-md-4">
                    
                              <label>T.C NO</label>
                              <input type="tel" required name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'" value="{{($pageindex==111) ? $adisyon->musteri->tc_kimlik_no : ''}}" class="form-control">
                         
                        </div>
                        <div class="col-md-4">
                         
                              <label>Senet Türü</label>
                              <select name="senet_turu" class="form-control">
                                 <option value="1">Nakden</option>
                                 <option value="2">Malen</option>
                                 <option value="3">Hizmet</option>
                              </select>
                          
                        </div>
                        <div class="col-md-12">
                          
                              <label>Adres</label>
                              <input type="tel" required name="adres" value="{{($pageindex==111) ? $adisyon->musteri->adres : ''}}" class="form-control">
                     
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                      
                              <label>Kefil Adı ve Soyadı</label>
                              <input type="text" name="kefil_adi"  class="form-control">
                        
                        </div>
                        <div class="col-md-6">
                         
                              <label>Kefil T.C No</label>
                              <input type="text" name="kefil_tc_vergi_no" data-inputmask =" 'mask' : '99999999999'" class="form-control">
                          
                        </div>
                        <div class="col-md-12">
                        
                              <label>Kefil Adres</label>
                              <input type="text" name="kefil_adres" name="adres" value="" class="form-control">
                          
                        </div>
                     </div>
                     <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            
                        Yeni Senet Oluştur
                        </button>
                        <button  
                           type="button"
                           class="btn btn-danger"
                           data-dismiss="modal"
                           >
                           <i class="fa fa-times"></i>      
                        Kapat
                        </button>
                     </div>
                  </div>
               </form>
            </div>
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
                        <input type="hidden" name="sube" id="sube" value="{{$isletme->id}}">

                        <input type="hidden" name="adisyon_id">

                        <div class="row" data-value="0">
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>Tarih</label>
                                 <input type="text" required class="form-control date-picker" name="urun_satis_tarihi" value="{{(isset($randevu)) ? $randevu->tarih : date('Y-m-d')}}" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>

                                 <select {{($pageindex=1111) ? 'disabled': ''}} name="musteri" class="form-control custom-select2" style="width:100%">
                                    @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                 @if($pageindex==111)
                                    @if($adisyon->user_id == $musteri_portfoy->user_id)
                                    <option selected value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                   

                                    @else
                                    <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                    @endif
                                 @endif
                                 @if($pageindex==1111)
                                    @if(isset($musteri))
                                       @if($musteri->id == $musteri_portfoy->user_id)
                                          <option selected value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                       @endif

                                    @else
                                    <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                    @endif
                                 @endif
                                 @endforeach
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="urunler_bolumu">
                           <div class="row" data-value="0">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Ürün</label>

                                    <select name="urunyeni[]" class="form-control custom-select2" style="width: 100%;">
                                       @foreach(\App\Urunler::where('salon_id',$isletme->id)->get() as $urun)
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

                                    <input type="tel" required name="urun_fiyati[]" value="{{(\App\Urunler::where('salon_id',$isletme->id)->first()!==null) ? \App\Urunler::where('salon_id',$isletme->id)->first()->fiyat : ''}}" class="form-control">
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
                                 <select name="urun_satici" class="form-control custom-select2" style="width: 100%;">
                                    @if(Auth::user()->hasRole('Personel'))
                                         <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                    @else
                                    @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                       <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                    @endforeach
                                    @endif
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
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                              Kaydet
                              </button>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button id="modal_kapat"
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 >
                              <i class="fa fa-times"></i> Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>


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
                              <input type="text" required class="form-control date-picker" name="tarih" value="{{date('Y-m-d')}}" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                              <select name="musteri" class="form-control custom-select2" style="width:100%">
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                 <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
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
                              <input type="text" required class="form-control date-picker" name="planlanan_odeme_tarihi" autocomplete="off">
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
      </div>
      </div>
      <!--yeni şube -->
      <div
         id="yeni_sube_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="sube_formu"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Şube</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="alacak_id" value="">
                     <div class="row" data-value="0">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>İşletme Adı</label>
                              <input type="text" name="firma_adi" required class="form-control">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Yetkili Adı Soyadı</label>
                              <input type="text" name="yetkili_adi"  required class="form-control">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>E-Posta Adresi</label>
                              <input type="email" name="email"  class="form-control">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Telefon Numarası</label>
                              <input type="text" name="telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Şifre</label>
                              <input type="password" name="password"  required class="form-control">
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="icon-copy dw dw-add"></i>
                           Şubeyi Oluştur </button>
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
      </div>
      </div>
       <div
         id="yeni_etkinlik_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="etkinlik_formu"  method="POST">
                  <div class="modal-header">
                     <h2 class="modal_baslik">Yeni Etkinlik</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="etkinlik_id" value="">
                     <div class="row" data-value="0">
                        <div class="col-md-4 col-sm-12">
                          
                              <label>Etkinlik İsmi</label>
                              <input type="text"  required class="form-control" name="etkinlik_adi">
                          
                        </div>
                  
                        <div class="col-md-3 ">
                          
                              <label>Tarih</label>
                              <input required placeholder="Tarih"
                                 type="text"
                                 class="form-control date-picker"
                                 name="etkinlik_tarihi" id="etkinlik_tarihi" autocomplete="off"
                                 />
                         
                        </div>
                        <div class="col-md-3">
                       
                              <label>Saat</label>
                              <input type="time" class="form-control" name="etkinlik_saati" id="etkinlik_saati" required>
                         
                        </div>
                        <div class="col-md-2">
                          
                              <label>Fiyat</label>
                              <input type="tel" name="etkinlik_fiyati"  class="form-control">
                          
                        </div>
                        
                 
                     </div>
                     <div class="row" data-value="0">
                        <div class="col-md-6" data-value="0">
                           <div class="col-md-12">
                     
                            <label>Şablon Seçiniz</label>
                          <select class="form-control" id="etkinlik_sablon_sec">
                            <option value="">Seçiniz</option> 
                            @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                            <option value="{{$sablon->taslak_icerik}}">{{$sablon->baslik}}</option>
                            @endforeach
                          </select>
                        
                           </div>
                           <div class="col-md-12">
                              
                                 <label>Mesaj İçeriği</label>
                                 <textarea class="form-control" style="height: 250px;" id="etkinlik_sms" name="etkinlik_sms"></textarea>
                            
                           </div>
                        </div>
                        <div class="col-md-6">
                            <label>Katılımcılar</label>

                           <div class="tab">
                              <div class="row clearfix">
                                <div class=" col-md-12 col-sm-12">
                                    <ul class="nav nav-tabs" role="tablist">
                              <li class="nav-item">
                                 <button
                                    class="btn btn-outline-primary active "
                                    data-toggle="tab"
                                    style="margin-left: 20px;"
                                    href="#tumu_etkinlik_katilimcilar"
                                    role="tab"
                                    aria-selected="true"
                                    >Tümü</button
                                    >
                                    
                              </li>
                              <li class="nav-item">
                                 <button
                                    class="btn btn-outline-primary "
                                    data-toggle="tab"
                                    style="margin-left: 20px;"
                                    href="#etkinlik_grup_katilimcilar"
                                    role="tab"
                                    aria-selected="false"
                                    >Gruplar</button
                                    >
                              </li>
                           
                           </ul>
                                </div>
                           </div>
                           <div class="col-md-12 col-sm-12" style="margin-top:10px;">
                              <div class="tab-content">
                                 <div class="tab-pane fade  show active" id="tumu_etkinlik_katilimcilar" role="tabpanel">
                                    <div class="col-md-12"  style="overflow-y:auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="data-table table stripe hover nowrap" id="musteri_sec_tablo">
                                             <thead>
                                              
                                                <div class="be-checkbox be-checkbox-color inline">
                                                   <input id="hepsinisec4" name="hepsinisec4" type="checkbox">
                                                   <label for="hepsinisec4"> Tümünü Seç</label>

                                                </div>
                                              
                                                
                                             </thead>
                                             <tbody>
                                                @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                                <tr>
                                                   <td> 
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input type="checkbox" name="etkinlik_katilimci_musteriler[]" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
                                                      </div>
                                                   </td>
                                                </tr>
                                                @endforeach
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tab-pane fade  show" id="etkinlik_grup_katilimcilar" role="tabpanel">
                                    <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="data-table table stripe hover nowrap" id="grup_sec_tablo">
                                             <thead>
                                                <div class="be-checkbox be-checkbox-color inline">
                                                   <input id="hepsinisec5" name="hepsinisec5" type="checkbox">
                                                   <label for="hepsinisec5"> Tümünü Seç</label>
                                                </div>
                                             </thead>
                                             <tbody>
                                               @foreach(\App\GrupSMS::where('salon_id',$isletme->id)->where('aktif_mi',1)->get() as $gruplar)
                                                <tr>
                                                   <td> 
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input type="checkbox" name="etkinlik_grup_katilimci_musteriler[]" value="{{$gruplar->id}}"> {{$gruplar->grup_adi}}
                                                      </div>
                                                   </td>
                                                </tr>
                                           @endforeach
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                             
                              
                           </div>
                           

                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row">
                           <div class="col-7 col-xs-7 col-sm-7">
                              <button type="submit"  class="btn btn-success btn-block">
                              Kaydet & Gönder </button>
                           </div>
                           <div class="col-5 col-xs-5 col-sm-5">
                              <button  
                                 type="button"
                                 class="btn btn-danger modal_kapat btn-block"
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
         </div>
      </div>
       
      <!-- yeni masraf -->
      <div
         id="yeni_masraf_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="masraf_formu"  method="POST">
                  <div class="modal-header">
                     <h2 class="modal_baslik"></h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="masraf_id" id='masraf_id' value="">
                     @if($pageindex==15)
                     <input type="hidden" name="masraf_sayfasi" value="1">
                     @endif
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                         
                              <label>Tarih</label>
                              <input type="text" required class="form-control" name="tarih" id='masraf_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                          
                        </div>
                        <div class="col-md-6">
                          
                              <label>Tutar (₺)</label>
                              <input type="tel" name="masraf_tutari" id='masraf_tutari' required class="form-control try-currency">
                          
                        </div>
                        <div class="col-md-12">
                        
                              <label>Açıklama</label>
                              <textarea name="masraf_aciklama" id='masraf_aciklama' class="form-control"></textarea>
                           
                        </div>
                     </div>
                     <div class="row" data-value="0">
                        <div class="col-md-12">
                         
                              <label>Masraf Kategorisi</label>
                              <select name="masraf_kategorisi" id='masraf_kategorisi' class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\MasrafKategorisi::all() as $cat)
                                 <option value="{{$cat->id}}">{{$cat->kategori}}</option>
                                 @endforeach
                              </select>
                         
                        </div>
                           
                     </div>
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                   
                              <label>Ödeme Yöntemi</label>
                              <select name="masraf_odeme_yontemi" id='masraf_odeme_yontemi' class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                                 <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                                 @endforeach
                              </select>
                        
                        </div>
                        <div class="col-md-6">
                         
                              <label>Harcayan</label>
                              <select name="harcayan" id='harcayan' class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                 <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                 @endforeach
                              </select>
                      
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           
                              <label>Notlar</label>
                              <textarea name="masraf_notlari" id='masraf_notlari' class="form-control"></textarea>
                          
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value="0">
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                              Kaydet </button>
                           </div>
                           <div class="col-md-6  col-sm-6 col-xs-6 col-6">
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
         </div>
      </div>
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
         id="paket_satisi_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content" >
               <form id="paket_satisi"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Paket Satışı</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="adisyon_id">
                     <input type="hidden" name="sube" value="{{$isletme->id}}">

                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Tarih</label>
                              <input type="text" required class="form-control geriye-yonelik" name="paket_satis_tarihi" value="{{date('Y-m-d')}}" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                               <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>

                                 <select {{($pageindex=1111) ? 'disabled': ''}} name="musteri" class="form-control custom-select2" style="width:100%">
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                    @if($pageindex==111)
                                       @if($adisyon->user_id == $musteri_portfoy->user_id)
                                       <option selected value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                      

                                       @else
                                       <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                       @endif
                                    @endif
                                    @if($pageindex==1111)
                                       @if(isset($musteri))
                                          @if($musteri->id == $musteri_portfoy->user_id)
                                             <option selected value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                          @endif

                                       @else
                                       <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                       @endif
                                    @endif
                                 @endforeach
                                 </select>
                           </div>
                        </div>
                     </div>
                     <div class="paketler_bolumu">
                        <div class="row" data-value="0">
                           <div class="col-md-3">
                              <div class="form-group">
                                 <input type="hidden" name="paket_id[]" value="">
                                 <label>Paket Adı</label>
                                 <select name="paketadi[]" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                    <option value="{{$paket->id}}">{{$paket->paket_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Fiyat (₺)</label>
                                 <input type="tel" name="paketfiyat[]" value="{{\App\PaketHizmetler::where('paket_id',\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->value('id'))->sum('fiyat')}}"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Başlangıç Tarihi</label>
                                 <input name="paketbaslangictarihi[]" id="" class="form-control geriye-yonelik" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>Seans Aralığı (gün)</label>
                                 <input type="tel" name="seansaralikgun[]"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                 <button type="button" name="paket_formdan_sil_yeni_ekle" disabled  data-value="0" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-secondary btn-lg btn-block" id="bir_paket_daha_ekle">
                              Bir Paket Daha Ekle
                              </button>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#paket-modal">Sisteme Yeni Paket Ekle</button>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Satıcı</label>
                              <select name="paket_satici" class="form-control custom-select2" style="width: 100%;">
                                  @if(Auth::user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                  @else
                                 @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                 <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                 @endforeach
                                 @endif
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
               
               </form>
            </div>
         </div>
      </div>
      
      <div
         id="yeni_seans_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="seans_form"  method="POST">
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
                                 @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmetliste)
                                 <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Tarih</label>
                              <input type="text" required class="form-control date-picker" name="paket_satis_tarihi" value="{{date('Y-m-d')}}" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                              <select name="musteri" id="musteri_paket" class="form-control custom-select2" style="width:100%">
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                 <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Kaç Seans</label>
                              <input type="tel" name="kac_seans" class="form-control" required>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Seans Ücreti (₺)</label>
                              <input type="tel" name="seans_ucreti" class="form-control" required>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Personel</label>
                              <select name="paket_satici" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
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
      </div>
      </div>
      </div>
      <div id="ongorusme-modal" class="modal fade">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="ongorusmeformu" method="POST">
                  <input type="hidden" name="on_gorusme_id" id="on_gorusme_id" value="">
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <div class="modal-header">
                     <h2 class="modal_baslik"></h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        <div class="col-md-4">
                              <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                              <select name="musteri" id="musteri_select_list" class="form-control opsiyonelSelect" style="width:100%">
                                 <option></option>
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                 <option value="{{$musteri_portfoy->user_id}}">{{$musteri_portfoy->users->name}}</option>
                                 @endforeach
                              </select>
                                                 </div>
                        <div class="col-md-4">
                           
                              <label>Ad Soyad</label>
                              <input type="text" required name="ad_soyad" id="ad_soyad" class="form-control" required>
                          
                        </div>
                        <div class="col-md-4">
                           
                              <label>Telefon</label> 
                              <input type="tel" required name="telefon"   data-inputmask =" 'mask' : '5999999999'" id="telefon" class="form-control" required>
                         
                        </div>
                        <div class="col-md-6">  
                         
                              <label>E-mail</label>
                              <input type="email" name="email" id="email" class="form-control">
                           
                        </div>
                        <div class="col-md-6">
                       
                              <label>Cinsiyet</label>
                              <select name="cinsiyet" id="cinsiyet" class="form-control">
                                 <option value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                              </select>
                         
                        </div>
                        <div class="col-md-12">
                       
                              <label>Adres</label>
                              <textarea class="form-control" id="adres" name="adres"></textarea>
                          
                        </div>
                        <div class="col-md-6">
                        
                              <label>Şehir</label>
                              <select name="sehir" id="sehir" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\Iller::all() as $il)
                                 <option value="{{$il->id}}">{{$il->il_adi}}</option>
                                 @endforeach
                              </select>
                       
                        </div>
                        <div class="col-md-6">
                  
                              <label>Referans</label>
                              <select id="musteri_tipi" name="musteri_tipi" class="form-control">
                                 <option value="0">Yok</option>
                                 <option value="1">İnternet</option>
                                 <option value="2">Reklam</option>
                                 <option value="3">Instagram</option>
                                 <option value="4">Facebook</option>
                                 <option value="5">Tanıdık</option>
                              </select>
                           
                        </div>
                        <div class="col-md-6">
                         
                              <label>Meslek</label>
                              <input type="text" id="meslek" name="meslek" class="form-control">
                         
                        </div>
                        <div class="col-md-6">
                 
                              <label>Ön Görüşme Sebebi</label>
                              <select name="paket_urun" id="paket" class="form-control opsiyonelSelect" style="width: 100%;">
                                 <option></option>
                                 @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                 <option value="{{$paket->id}}">
                                    {{$paket->paket_adi}}
                                 </option>
                                 @endforeach
                                 @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                 <option value="urun-{{$urun->id}}">
                                    {{$urun->urun_adi}}
                                 </option>
                                 @endforeach
                              </select>
         
                        </div>
                        
                       
                        <div class="col-md-6">
                
                              <label>Ön Görüşme Tarihi</label>
                              <input type="text" name="ongorusme_tarihi" id="ongorusme_tarihi" class="form-control date-picker" value="{{date('Y-m-d')}}" autocomplete="off">
                  
                        </div>
                        <div class="col-md-6">
                         
                              <label>Görüşmeyi Yapan</label>
                              <select name="gorusmeyi_yapan" id="gorusmeyi_yapan" class="form-control custom-select2" style="width: 100%;">
                                 @if(Auth::user()->hasRole('Personel'))
                                       <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                 @else
                                       @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                       <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                       @endforeach
                                 @endif
                              </select>
                     
                        </div>
                        <div class="col-md-12">
                         
                              <label>Açıklama</label>
                              <textarea name="aciklama" id="aciklama" class="form-control"></textarea>
                          
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block;">
                     <div class="row">
                        <div class="col-md-6 col-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                        </div>
                        <div class="col-md-6 col-6">
                           <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      
       <div
         id="yeni_taksitli_tahsilat_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="taksitli_tahsilat_formu"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Taksitli Tahsilat</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     @if($pageindex==111)
                     <input type="hidden" name="adisyon_id" value="{{$adisyon->id}}">
                     <input type="hidden" name="ad_soyad" value="{{$adisyon->user_id}}">
                     @endif
                     @if($pageindex==1111)
                     <input type="hidden" name="ad_soyad" value="{{(isset($musteri)) ? $musteri->id : ''}}">
                     @endif
                     <div class="row" data-value="0">
                        
                        
                        <div class="col-md-4">
                       
                              <label>Ödeme Başlangıç Tarihi</label>
                              <input type="text" required class="form-control date-picker" name="vade_baslangic_tarihi" autocomplete="off">
                      
                        </div>
                        <div class="col-md-4">
                          
                              <label>Taksit Sayısı (Ay)</label>
                              <input type="tel" required name="vade" value=" " class="form-control">
                           
                        </div>
                        <div class="col-md-4">
                      
                              <label>Tutar (₺)</label>
                              <input type="tel" required {{($pageindex==111) ? 'disabled': ''}} name="taksit_tutar" id='taksit_tutar' value="" class="form-control try-currency">
                        
                        </div>
                         
                     </div>
                     <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            
                          Kaydet
                        </button>
                        <button  
                           type="button"
                           class="btn btn-danger"
                           data-dismiss="modal"
                           >
                           <i class="fa fa-times"></i>      
                        Kapat
                        </button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <button style="display: none;" id="randevudetayigetir" data-toggle="modal" data-target="#modal-view-event"></button>
      <button style="display: none;" id="ajandadetayigetir" data-toggle="modal" data-target="#ajanda_detay_modal"></button>
       
      
       <div
         id="yeni_adisyon_modal"
         class="modal modal-top fade calendar-modal"

         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 950px;">
            <div class="modal-content" >
               <form id="adisyon_formu"  method="POST">
                   
                   <input type="hidden" value="{{$isletme->id}}" name="sube">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <h2 class="text-blue h2 mb-10" id="adisyon_modal_baslik">Yeni Adisyon</h2>
                     <div class="row">
                        <div class="col-md-3">
                           <div class="form-group">
                              <label>Adisyon Tarihi</label>
                              <input type="text" name="adisyon_tarihi" id='adisyon_tarihi' class="form-control" required value="{{date('Y-m-d')}}" autocomplete="off">
                           </div> 
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>@if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</label>
                              <select name="musteri" id='yeni_adisyon_musterisi'  class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                                 <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label style="visibility: hidden;width: 100%;">yenimüşteri</label>
                              <button class="btn btn-primary yanitsiz_musteri_ekleme" type="button"  data-toggle="modal" data-target="#musteri-bilgi-modal"><i class="icon-copy fi-plus"></i>Yeni @if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif</button>
                           </div>
                        </div>

                         <div class="col-md-4">
                                <div class="form-group">
                                  <button class="btn btn-success   btn-lg btn-block" type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal"><i class="fa fa-plus"></i> Hizmet Satışı</button>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <button id='adisyon_urun_satisi_button' class="btn btn-success  btn-lg btn-block" type="button" data-toggle="modal" data-target="#adisyon_yeni_urun_modal"><i class="fa fa-plus"></i> Ürün Satışı</button>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <button class="btn btn-success btn-lg btn-block" type="button" data-toggle="modal" data-target="#paket_satisi_modal_adisyon"><i class="fa fa-plus"></i> Paket Satışı</button>
                                </div>
                              </div>
                           </div>
                           <div class="col" style=" height: 150px;
  width:auto;
  overflow-y: scroll;">
     <p style='font-size: 15px;font-weight: bold;text-decoration: underline;'>Eklenen Hizmetler</p>

                        <div id='adisyon_secilen_hizmetler'>

                              
                        </div>
                        <p style='font-size: 15px;font-weight: bold;text-decoration: underline;'>Eklenen Ürünler</p>
                        <div id='adisyon_secilen_urunler'>

                              
                        </div>
                        <p style='font-size: 15px;font-weight: bold;text-decoration: underline;'>Eklenen Paketler</p>
                        <div id='adisyon_secilen_paketler'>

                              
                        </div>
                        
  </div>
                        
                        <div class="row" style="background: #e2e2e2; padding:5px; font-size:20px">
                           <div class="col-6 col-xs-6 col-sm-6"><b>TOPLAM : </b></div>
                           <div class="col-6 col-xs-6 col-sm-6" style="text-align:right;font-weight: bold;"><span id='adisyon_toplam_tutar'>0</span> ₺</div>
                        </div>
                        <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Adisyon Notu</label>
                              <input type="text" name="adisyon_not" id="adisyon_not" class="form-control">
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button 
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal" 
                              ><i class="fa fa times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
            
            </form>
            </div>
         </div>
      </div>
      <div
         id="adisyon_yeni_hizmet_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width:1200px">
            <div class="modal-content">
               <form id="adisyon_hizmet_formu"  method="POST">
                 
                  <input type="hidden" name="adisyon_id" value="{{($pageindex==111) ? $adisyon->id : ''}}">
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <h2 class="text-blue h2 mb-10" id="adisyon_hizmet_modal_baslik">Yeni Hizmet Satışı</h2>
                     <div class="hizmetler_bolumu_adisyon">
                        <div class="row" data-value="0">
                              <div class="col-md-2 col-xs-6 col-sm-6 col-6">
                              <div class="form-group">
                                 <label>Personel</label>

                                 <select name="adisyonhizmetpersonelleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                    @if(Auth::user()->hasRole('Personel'))
                                        <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                    @else
                                    @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                    <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                    @endforeach
                                    @endif
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3 col-xs-6 col-sm-6 col-6">
                              <div class="form-group">
                                 <label>Hizmet</label>
                                 <select name="adisyonhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmetliste)
                                    <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-2 col-xs-6 col-sm-6 col-6">
                              <div class="form-group">
                                 <label>İşlem Tarihi</label>
                                 <input name="islemtarihiyeni[]" required class="form-control" type="text" value="{{date('Y-m-d')}}" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2 col-xs-6 col-sm-6 col-6">
                              <div class="form-group">
                                 <label>İşlem Saati</label>
                                 <input name="islemsaatiyeni[]" required class="form-control" type="time" value="{{date('H:i')}}" autocomplete="off">
                              </div>
                           </div>
                       
                           <div class="col-md-1 col-xs-5 col-sm-5 col-5">
                              <div class="form-group">
                                 <label>Süre (dk)</label>
                                 <input type="tel" class="form-control" required name="adisyonhizmetsuresi[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("sure_dk")}}'>
                              </div>
                           </div>
                           <div class="col-md-1 col-xs-5 col-sm-5 col-5">
                              <div class="form-group">
                                 <label>Fiyat ₺</label>
                                 <input type="tel" class="form-control" required name="adisyonhizmetfiyati[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("baslangic_fiyat")}}'>
                              </div>
                           </div>
                           <div class="col-md-1 col-xs-1 col-sm-1 col-1">
                              <div class="form-group">
                                 <label style="visibility: hidden;">Kaldır</label>
                                 <button type="button" name="hizmet_formdan_sil_adisyon"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" id="bir_hizmet_daha_ekle_adisyon" class="btn btn-secondary btn-lg btn-block">
                              Bir Hizmet Daha Ekle
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button
                              type="button" id='adisyon_hizmet_modal_kapat'
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal" 
                              ><i class="fa fa times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <div
         id="senet_yeni_hizmet_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width:1200px">
            <div class="modal-content">
               <form id="senet_hizmet_formu"  method="POST">
                 
                  
                 
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <h2 class="text-blue h2 mb-10" id="adisyon_hizmet_modal_baslik">Yeni Hizmet Satışı</h2>
                     <div class="hizmetler_bolumu_senet">
                        <div class="row" data-value="0">
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>İşlem Tarihi</label>
                                 <input name="senetislemtarihiyeni[]" required class="form-control" type="text" value="{{date('Y-m-d')}}" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>İşlem Saati</label>
                                 <input name="senetislemsaatiyeni[]" required class="form-control" type="time" value="{{date('H:i')}}" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>Personel</label>

                                 <select name="senethizmetpersonelleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                    @if(Auth::user()->hasRole('Personel'))
                                        <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                    @else
                                    @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                    <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                    @endforeach
                                    @endif
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Hizmet</label>
                                 <select name="senethizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmetliste)
                                    <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label>Süre (dk)</label>
                                 <input type="tel" class="form-control" required name="senethizmetsuresi[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("sure_dk")}}'>
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label>Fiyat ₺</label>
                                 <input type="tel" class="form-control" required name="senethizmetfiyati[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("baslangic_fiyat")}}'>
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label style="visibility: hidden;">Kaldır</label>
                                 <button type="button" name="hizmet_formdan_sil_senet"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" id="bir_hizmet_daha_ekle_senet" class="btn btn-secondary btn-lg btn-block">
                              Bir Hizmet Daha Ekle
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button
                              type="button" id='senet_hizmet_modal_kapat'
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal" 
                              ><i class="fa fa times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <div
         id="etkinlik_detay_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%; max-width: 100%;">
               <div class="modal-body">
                  <h2 class="text-blue h2 mb-10">Etkinlik Detayı</h2>
                  <div class="tab">
                    <div class="col-12 col-sm-12 col-xs-12 elementetkinlikkampanya" >
                          <table class="data-table table stripe hover nowrap" id="etkinlik_tablo">
                        <thead>
                           <th>Tarih : <span id="etkinlik_tarih" style="font-weight: normal;"></span></th>
                           <th>Etkinlik Adı: <span id="etkinlik_adi" style="font-weight: normal;"></span></th>
                           <th>Katılımcı Sayısı: <span id="etkinlik_katilimci" style="font-weight: normal;"></span></th>
                           <th>Toplam Tutar: <span id="toplam_tutar" style="font-weight: normal;"></span></th>
                        </thead>
                     </table>
                    </div>
                 
                     <hr>
                     <ul class="nav nav-tabs elementbutton" role="tablist">
                        <li class="nav-item" style="margin-left: 20px">
                           <button
                              class="btn btn-outline-primary"
                              data-toggle="tab"
                              href="#tum_etkinlik"
                              role="tab"
                              aria-selected="true"
                              >Tümü</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px">
                           <button
                              class="btn btn-outline-primary"
                              data-toggle="tab"
                              href="#etkinlik_katilanlar"
                              role="tab"
                              aria-selected="false"
                              >Katılanlar</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px">
                           <button
                              class="btn btn-outline-primary"
                              data-toggle="tab"
                              href="#etkinlik_katilmayanlar"
                              role="tab"
                              aria-selected="false"
                              >Katılmayanlar</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px">
                           <button
                              class="btn btn-outline-primary"
                              data-toggle="tab"
                              href="#etkinlik_beklenen"
                              role="tab"
                              aria-selected="false"
                              >Beklenenler</button
                              >
                        </li>
                     </ul>
                     <div class="tab-content">
                        <div
                           class="tab-pane fade show active"
                           id="tum_etkinlik"
                           role="tabpanel"
                           >
                           <div class="pd-20">
                              <form id="tum_katilimcilar"  method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="data-table table stripe hover nowrap" id="etkinlik_tablo_tum_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                   <th>Durum</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                     
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="etkinlik_katilanlar" role="tabpanel">
                           <div class="pd-20">
                              <form id="etkinlik_katilan" method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="data-table table stripe hover nowrap" id="etkinlik_tablo_katilanlar_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                    
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="etkinlik_katilmayanlar" role="tabpanel">
                           <div class="pd-20">
                              <form id="etkinlik_katilmayan" method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="data-table table stripe hover nowrap" id="etkinlik_tablo_katilmayanlar_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                     
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="etkinlik_beklenen" role="tabpanel">
                           <div class="pd-20">
                              <form id="etkinlik_beklenenler" method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="etkinlik_tablo_beklenen_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                    <div class="col-md-12">
                                       <button id="etkinlikbeklenenleresmsgonder" class="btn btn-success btn-block"><i class="icon-copy fi-mail"></i> SMS GÖNDER</button>
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
         id="kampanya_detay_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"style="max-height: 90%; max-width: 100%;">
               <div class="modal-body">
                  <h2 class="text-blue h2 mb-10">Kampanya Detayı</h2>
                  <div class="tab">
                    <div class="col-xs-12 col-12 col-sm-12 elementetkinlikkampanya">
                        <table class="data-table table stripe hover nowrap" id="kampanyayonetim_tablo">
                        <thead>
                           <th>Paket Adı: <span id="paket_adi" style="font-weight: normal;"></span></th>
                           <th>Seans : <span id="kampanya_seans" style="font-weight: normal;"></span></th>
                           <th>Katılımcı Sayısı: <span id="kampanya_katilimci" style="font-weight: normal;"></span></th>
                           <th>Hizmet: <span id="kampanya_hizmeti" style="font-weight: normal;"></span> </th>
                           <th>Toplam Tutar: <span id="kampanya_toplam_tutar" style="font-weight: normal;"></span></th>
                        </thead>
                     </table>
                    </div>
                   
                     <hr>
                     <ul class="nav nav-tabs elementbutton" role="tablist">
                        <li class="nav-item">
                           <a
                              class="nav-link active text-blue"
                              data-toggle="tab"
                              href="#tum_kampanya"
                              role="tab"
                              aria-selected="true"
                              >Tümü</a
                              >
                        </li>
                        <li class="nav-item">
                           <a
                              class="nav-link text-blue"
                              data-toggle="tab"
                              href="#kampanya_katilanlar"
                              role="tab"
                              aria-selected="false"
                              >Katılanlar</a
                              >
                        </li>
                        <li class="nav-item">
                           <a
                              class="nav-link text-blue"
                              data-toggle="tab"
                              href="#kampanya_katilmayanlar"
                              role="tab"
                              aria-selected="false"
                              >Katılmayanlar</a
                              >
                        </li>
                        <li class="nav-item">
                           <a
                              class="nav-link text-blue"
                              data-toggle="tab"
                              href="#kampanya_beklenen"
                              role="tab"
                              aria-selected="false"
                              >Beklenenler</a
                              >
                        </li>
                     </ul>
                     <div class="tab-content">
                        <div
                           class="tab-pane fade show active"
                           id="tum_kampanya"
                           role="tabpanel"
                           >
                           <div class="pd-20">
                              <form id="tum_katilimcilar"  method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="kampanya_tablo_tum_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                   <th>Durum</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                   
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="kampanya_katilanlar" role="tabpanel">
                           <div class="pd-20">
                              <form id="kampanya_katilan" method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="kampanya_tablo_katilanlar_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                    
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="kampanya_katilmayanlar" role="tabpanel">
                           <div class="pd-20">
                              <form id="kampanya_katilmayan" method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="kampanya_tablo_katilmayanlar_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                    
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="tab-pane fade" id="kampanya_beklenen" role="tabpanel">
                           <div class="pd-20">
                              <form id="kampanya_beklenenler" method="POST">
                                 {!!csrf_field()!!}
                                 <div class="row">
                                    <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="kampanya_tablo_beklenen_katilimci">
                                             <thead>
                                                <tr>
                                                   <th>Ad Soyad</th>
                                                   <th>Telefon Numarası</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                    <button class="btn btn-success btn-block" id="kampanyabeklenenleresmsgonder"><i class="icon-copy fi-mail"></i> SMS GÖNDER</button>
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
         id="etkinlik_sms_tum_katilimci_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%; width: 900px">
               <form id="etkinlik_sms_formu"  method="POST">
                  <div class="modal-header">
                     <h2 class="modal_baslik">SMS Gönder</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="etkinlik_id" value="">
                     <div class="row" data-value="0">
                        <div class="col-md-6 "  style="overflow-y: auto; max-height: 300px ">
                           <div class="form-group">
                              <label>Mesajınızı Yazınız</label>
                              <textarea class="form-control" style="height: 200px;"></textarea>
                           </div>
                        </div>
                        <div class="col-md-6"  style="overflow-y: auto; max-height: 300px ">
                           <div class="form-group">
                              <label>@if($isletme->salon_turu_id==15) Danışanlar @else Müşteriler @endif</label>
                              <table class="table" id="etkinlik_tablo_tum_katilimci_sms">
                                 <tbody>
                                    @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                    <tr>
                                       <td>
                                          <input type="checkbox" name="sec" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
                                       </td>
                                    </tr>
                                    @endforeach
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row">
                           <div class="col-md-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="icon-copy fi-mail"></i>
                              Gönder </button>
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
         </div>
      </div>
      <div
         id="yeni_kampanya_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-height: 90%" >
               <form id="kampanya_formu"  method="POST">
                  <div class="modal-header">
                     <h2 class="modal_baslik" id="kampanya_modal_baslik">Yeni Reklam Oluştur</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="kampanya_id" value="">
                      <input type="hidden" name="sube" value="{{$isletme->id}}">
                   
                        <div class="row" >
                           <div class="col-md-3">
                            
                                 <input type="hidden"  name="paket_id" value="">
                                 <label>Paket Adı</label>
                                 <select id="kampanyapaket" name="kampanyapaketadi" class="form-control opsiyonelSelect" style="width: 100%;">
                                    <option></option>
                                    @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                    <option value="{{$paket->id}} ">{{$paket->paket_adi}}</option>
                                    @endforeach
                                 </select>
                          
                           </div>
                           <div class="col-md-3">
                             
                                 <label>Fiyat (₺)</label>
                                 <input type="tel" name="kampanyapaketfiyat" value=""  class="form-control" required>
                              
                           </div>
                           <div class="col-md-3">
                        
                                 <label>Hizmet</label>
                            
                                 <input type="text" name="kampanyapakethizmet"  value="" class="form-control" required>
                         
                           </div>
                           <div class="col-md-3">
                             
                                 <label>Seans</label>
                                 <input type="tel"  name="kampanyapaketseans" value=""  class="form-control" required>
                         
                           </div>
                          
                        
                     </div>
                     <div class="row" >
                        <div class="col-md-6">
                           <div class="col-md-12">
                      
                            <label>Şablon Seçiniz</label>
                          <select class="form-control" id="kampanya_sablon_sec">
                            <option value="">Seçiniz</option> 
                            @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                            <option value="{{$sablon->taslak_icerik}}">{{$sablon->baslik}}</option>
                            @endforeach
                          </select>
            
                           </div>
                           <div class="col-md-12">
                            
                                 <label>Mesaj İçeriği</label>
                                 <textarea class="form-control" style="height: 250px;" id="kampanya_sms" name="kampanya_sms"></textarea>
                              
                           </div>
                        </div>
                        <div class="col-md-6">
                            <label>Katılımcılar</label>

                           <div class="tab">
                              <div class="row clearfix">
                                <div class=" col-md-12 col-sm-12">
                                    <ul class="nav nav-tabs" role="tablist">
                              <li class="nav-item">
                                 <button
                                    class="btn btn-outline-primary active "
                                    data-toggle="tab"
                                    style="margin-left: 20px;"
                                    href="#tumu_kampanya_katilimcilar"
                                    role="tab"
                                    aria-selected="true"
                                    >Tümü</button
                                    >
                                    
                              </li>
                              <li class="nav-item">
                                 <button
                                    class="btn btn-outline-primary "
                                    data-toggle="tab"
                                    style="margin-left: 20px;"
                                    href="#kampanya_grup_katilimcilar"
                                    role="tab"
                                    aria-selected="false"
                                    >Gruplar</button
                                    >
                              </li>
                              <li class="nav-item">
                                 <button
                                    class="btn btn-outline-primary "
                                    data-toggle="tab"
                                    style="margin-left: 20px;"
                                    href="#kampanya_hizmete_gore_katilimcilar"
                                    role="tab"
                                    aria-selected="false"
                                    >Hizmete Göre</button
                                    >
                              </li>
                           </ul>
                                </div>
                           </div>
                           <div class="col-md-12 col-sm-12" style="margin-top:10px;">
                              <div class="tab-content">
                                 <div class="tab-pane fade  show active" id="tumu_kampanya_katilimcilar" role="tabpanel">
                                    <div class="col-md-12"  style="overflow-y:auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="musteri_sec_tablo">
                                             <thead>
                                              
                                                <div class="be-checkbox be-checkbox-color inline">
                                                   <input id="hepsinisec1" name="hepsinisec1" type="checkbox">
                                                   <label for="hepsinisec1"> Tümünü Seç</label>

                                                </div>
                                              
                                                
                                             </thead>
                                             <tbody>
                                                @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                                <tr>
                                                   <td> 
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input type="checkbox" name="kampanya_katilimci_musteriler[]" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
                                                      </div>
                                                   </td>
                                                </tr>
                                                @endforeach
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tab-pane fade  show" id="kampanya_grup_katilimcilar" role="tabpanel">
                                    <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table" id="grup_sec_tablo">
                                             <thead>
                                                <div class="be-checkbox be-checkbox-color inline">
                                                   <input id="hepsinisec2" name="hepsinisec2" type="checkbox">
                                                   <label for="hepsinisec2"> Tümünü Seç</label>
                                                </div>
                                             </thead>
                                             <tbody>
                                               @foreach(\App\GrupSMS::where('salon_id',$isletme->id)->where('aktif_mi',1)->get() as $gruplar)
                                                <tr>
                                                   <td> 
                                                      <div class="be-checkbox be-checkbox-color inline">
                                                         <input type="checkbox" name="grup_katilimci_musteriler[]" value="{{$gruplar->id}}"> {{$gruplar->grup_adi}}
                                                      </div>
                                                   </td>
                                                </tr>
                                           @endforeach
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tab-pane fade show " id="kampanya_hizmete_gore_katilimcilar" role="tabpanel">
                                    <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                                       <div class="form-group">
                                          <table class="table">
                                             <thead>
                                                <div class="be-checkbox be-checkbox-color inline">
                                                   <input id="hepsinisec3" name="hepsinisec3" type="checkbox">
                                                   <label for="hepsinisec3"> Tümünü Seç</label>
                                                </div>
                                             </thead>
                                             <tbody id="musteri_liste_hizmete_gore">
                                                @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $musteri_portfoy)
                                                <tr>
                                                   <td> 
                                                      <div class="be-checkbox be-checkbox-color inline" >
                                                         <input type="checkbox"  name="kampanya_katilimci_musteriler[]" value="{{$musteri_portfoy->user_id}}"> {{$musteri_portfoy->users->name}}
                                                      </div>
                                                   </td>
                                                </tr>
                                                @endforeach
                                           
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                             
                              
                           </div>
                           

                        </div>
                     </div>
                 
                   
                     <div class="modal-footer" style="display:block">
                   <div class="row">
                           <div class="col-7 col-xs-7 col-sm-7">
                              <button type="submit"  class="btn btn-success btn-block">
                              Kaydet & Gönder </button>
                           </div>
                           <div class="col-5 col-xs-5 col-sm-5">
                              <button  
                                 type="button"
                                 class="btn btn-danger modal_kapat btn-block"
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
         </div>
    
      </div>
      <button id='senet_detay_modal_ac' data-toggle="modal" data-target="#senet_detay_modal" style="display: none;"></button>
      <button id='taksit_detay_modal_ac' data-toggle="modal" data-target="#taksit_detay_modal" style="display: none;"></button>
      <div
            id="senet_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
             <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
               <div class="modal-content" style="width:100%">

                  <form method="POST" id="senet_adisyon" action="{{ URL::to('/isletmeyonetim/pdf') }}">
                     {{csrf_field()}}
                     <input type="hidden" id="senet_id" name='senetid'>
                     <div class="modal-header">
                        <h2>Senet Vadeleri</h2>
                     </div>
                     <div class="modal-body">
                        
                         
                        <div  id="senet_vade_listesi">
                                 
                        </div>
                     </div>
                  
                   
                     <div class="modal-footer">
                       <button id="senettahsilataktarma"  type="button" class="btn btn-success">Tahsilata Aktar</button>
                           <button type="submit" class="btn btn-primary">Yazdır</button>
                           <button id="modal_kapat"
                              type="button"
                              class="btn btn-danger"
                              data-dismiss="modal"
                            >
                           Kapat
                           </button>
                     </div>
                  </form>
                   
               </div>
            </div>
         </div>

<div
   id="taksit_detay_modal"
   class="modal modal-top fade calendar-modal"
   >
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
      <div class="modal-content" style="width:100%">
          
            {{csrf_field()}}
            <input type="hidden" id="taksitli_tahsilat_id" name='taksitlitahsilatid'>
            <div class="modal-header">
               <h2>Taksit Vadeleri</h2>
            </div>
            <div class="modal-body">
               
                
               <div  id="taksit_vade_listesi">
                        
               </div>
            </div>
         
          
            <div class="modal-footer">
                  
                  <button id="taksit_modal_kapat"
                     type="button"
                     class="btn btn-danger"
                     data-dismiss="modal"
                   >
                  Kapat
                  </button>
            </div>
         
          
      </div>
   </div>
</div>
     <div
            id="senet_taksit_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
             <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
               <div class="modal-content" style="width:100%">
                  <form id='senet_taksit_duzenleme_tahsilat' method="POST">
                     <div class="pd-20">
                        <div class="tab">
                           <ul class="nav nav-tabs" role="tablist">
                              <li class="nav-item">
                                 <a
                                    class="nav-link active text-blue"
                                    data-toggle="tab"
                                    href="#taksit-tahsilat"
                                    role="tab"
                                    aria-selected="true"
                                    >Taksitler</a
                                    >
                              </li>
                            
                              <li class="nav-item">
                                 <a
                                    class="nav-link text-blue"
                                    data-toggle="tab"
                                    href="#senet-tahsilat"
                                    role="tab"
                                    aria-selected="false"
                                    >Senetler</a
                                    >
                              </li>
                            
                           </ul>
                           <div class="tab-content">
                              <div
                                 class="tab-pane fade show active"
                                 id="taksit-tahsilat"
                                 role="tabpanel"
                                 >
                                 <div class="pd-10">
                                    <div  id="taksit_vade_listesi_tahsilat">
                           
                                    </div>
                                 </div>
                              </div>
                               
                              <div class="tab-pane fade" id="senet-tahsilat" role="tabpanel"> 
                                 <div  id="senet_vade_listesi_tahsilat">
                                    
                                 </div>
                              </div>
                              
                           </div>
                        </div>
                     </div>
                  
                  <div class="modal-footer" style="display:block;">
                     <div class="row">
                        
                        <div class="col-6 col-xs-6">
                           <button type="submit" id='secili_alacaklari_tahsil_et' class="btn btn-success btn-lg btn-block">
                              <i class="fa fa-money"></i> Seçilileri Tahsil Et
                           </button>

                        </div>
                        <div class="col-6 col-xs-6">
                           <button type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                               Kapat
                           </button>
                        </div>
                     </div>
                  </div>
                  </form>
               </div>
            </div>
         </div>
      <div
         id="paket-duzenle-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1100px; max-height: 90%;">
               <form id="paket_formu_duzenleme" method="POST">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="paket_id" id='paket_id_duzenleme'>
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <h2 class="text-blue h2 mb-10">Paket Düzenle</h2>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Paket Adı</label>
                              <input type="text" required name="adpaket" id="paketad" class="form-control" required>
                           </div>
                        </div>
                        <div class="paket_hizmetler_bolumu_duzenleme" style="margin-left: 20px">
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" id="paket_hizmet_daha_ekle_duzenleme" class="btn btn-secondary btn-lg btn-block">
                              Pakete Bir Hizmet Daha Ekle
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">
                           Kaydet
                           </button>
                        </div>
                        <div class="col-md-6">
                           <button id="modal_kapat_paket"
                              type="button"
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
      </div>
      </div>
       
      <div id="musteri-bilgi-modal" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style=" max-height: 90%;">
                  <form class="musteri_bilgi_formu" method="POST">
                     {{ csrf_field() }}
                     @if($pageindex==41)
                     <input type="hidden" name="musteri_id" value="{{$musteri_bilgi->id}}">
                     @else
                     <input type="hidden" name="musteri_id">
                     @endif
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name='eklendi_yanit_goster' id="eklendi_yanit_goster" >
                      <div class="modal-header">
                     <h2 class="modal_baslik">Yeni Müşteri</h2>
                  </div>
                     <div class="modal-body">
                        <div class="row">
                           <div class="col-md-6">
                              
                                 <label>Ad Soyad</label>
                                 @if($pageindex==41)
                                 <input type="text" name="ad_soyad" required class="form-control" value="{{$musteri_bilgi->name}}">
                                 @else
                                 <input type="text" name="ad_soyad" required class="form-control" value="">
                                 @endif
                  
                           </div>
                           <div class="col-md-6 col-xs-6 col-sm-6 col-6">
                              <label>Telefon  </label>
                              @if($pageindex==41)
                              <input type="tel" name="telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control" value="{{$musteri_bilgi->cep_telefon}}">
                              @else
                               <input type="tel" name="telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control" value="">
                              @endif
                           </div>
                             <div class="col-md-6 col-xs-6 col-sm-6 col-6">
                              <label>TC Kimlik No</label>
                              @if($pageindex==41)
                              <input type="tel" name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'"  class="form-control" value="{{$musteri_bilgi->tc_kimlik_no}}">
                              @else
                              <input type="tel" name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'"  class="form-control" value="">
                              @endif
                           </div>
                         
                           <div class="col-md-6 ">
                              <label style="width:100%">Doğum Tarihi</label>
                              @if($pageindex==41) 
                              <select class="form-control opsiyonelSelectGun" name="dogum_tarihi_gun">
                                 <option></option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='01') ? 'selected':''}} value="01">01</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='02') ? 'selected':''}} value="02">02</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='03') ? 'selected':''}} value="03">03</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='04') ? 'selected':''}} value="04">04</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='05') ? 'selected':''}} value="05">05</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='06') ? 'selected':''}} value="06">06</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='07') ? 'selected':''}} value="07">07</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='08') ? 'selected':''}} value="08">08</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='09') ? 'selected':''}} value="09">09</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='10') ? 'selected':''}} value="10">10</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='11') ? 'selected':''}} value="11">11</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='12') ? 'selected':''}} value="12">12</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='13') ? 'selected':''}} value="13">13</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='14') ? 'selected':''}} value="14">14</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='15') ? 'selected':''}} value="15">15</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='16') ? 'selected':''}} value="16">16</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='17') ? 'selected':''}} value="17">17</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='18') ? 'selected':''}} value="18">18</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='19') ? 'selected':''}} value="19">19</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='20') ? 'selected':''}} value="20">20</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='21') ? 'selected':''}} value="21">21</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='22') ? 'selected':''}} value="22">22</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='23') ? 'selected':''}} value="23">23</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='24') ? 'selected':''}} value="24">24</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='25') ? 'selected':''}} value="25">25</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='26') ? 'selected':''}} value="26">26</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='27') ? 'selected':''}} value="27">27</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='28') ? 'selected':''}} value="28">28</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='29') ? 'selected':''}} value="29">29</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='30') ? 'selected':''}} value="30">30</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='31') ? 'selected':''}} value="31">31</option>
                              </select>
                              <select class="form-control opsiyonelSelectAy" name="dogum_tarihi_ay">
                                 <option></option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='01') ? 'selected':''}} value="01">Ocak</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='02') ? 'selected':''}} value="02">Şubat</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='03') ? 'selected':''}} value="03">Mart</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='04') ? 'selected':''}} value="04">Nisan</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='05') ? 'selected':''}} value="05">Mayıs</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='06') ? 'selected':''}} value="06">Haziran</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='07') ? 'selected':''}} value="07">Temmuz</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='08') ? 'selected':''}} value="08">Ağustos</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='09') ? 'selected':''}} value="09">Eylül</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='10') ? 'selected':''}} value="10">Ekim</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='11') ? 'selected':''}} value="11">Kasım</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='12') ? 'selected':''}} value="12">Aralık</option>
                              </select>
                              <select class="form-control opsiyonelSelectYil" name="dogum_tarihi_yil">
                                 <option></option>
                                  @for($i=1900;$i<=date('Y');$i++)

                                    <option {{(date('Y',strtotime($musteri_bilgi->dogum_tarihi))==$i) ? 'selected':''}} value="{{$i}}">{{$i}}</option>
                                  @endfor
                              </select>
                              @else
                                 <select class="form-control opsiyonelSelectGun" name="dogum_tarihi_gun">
                                 <option></option>
                                 <option value="01">01</option>
                                 <option value="02">02</option>
                                 <option value="03">03</option>
                                 <option value="04">04</option>
                                 <option value="05">05</option>
                                 <option value="06">06</option>
                                 <option value="07">07</option>
                                 <option value="08">08</option>
                                 <option value="09">09</option>
                                 <option value="10">10</option>
                                 <option value="11">11</option>
                                 <option value="12">12</option>
                                 <option value="13">13</option>
                                 <option value="14">14</option>
                                 <option value="15">15</option>
                                 <option value="16">16</option>
                                 <option value="17">17</option>
                                 <option value="18">18</option>
                                 <option value="19">19</option>
                                 <option value="20">20</option>
                                 <option value="21">21</option>
                                 <option value="22">22</option>
                                 <option value="23">23</option>
                                 <option value="24">24</option>
                                 <option value="25">25</option>
                                 <option value="26">26</option>
                                 <option value="27">27</option>
                                 <option value="28">28</option>
                                 <option value="29">29</option>
                                 <option value="30">30</option>
                                 <option value="31">31</option>
                              </select>
                              <select class="form-control opsiyonelSelectAy" name="dogum_tarihi_ay">
                                 <option></option>
                                 <option value="01">Ocak</option>
                                 <option value="02">Şubat</option>
                                 <option value="03">Mart</option>
                                 <option value="04">Nisan</option>
                                 <option value="05">Mayıs</option>
                                 <option value="06">Haziran</option>
                                 <option value="07">Temmuz</option>
                                 <option value="08">Ağustos</option>
                                 <option value="09">Eylül</option>
                                 <option value="10">Ekim</option>
                                 <option value="11">Kasım</option>
                                 <option value="12">Aralık</option>
                              </select>
                              <select class="form-control opsiyonelSelectYil" name="dogum_tarihi_yil">
                                 <option></option>
                                  @for($i=1900;$i<=date('Y');$i++)
                                 <option value="{{$i}}">{{$i}}</option>
                                  @endfor
                              </select>
                              @endif
                           </div>
                             <div class="col-md-6">
                              <label>E-posta </label>
                              @if($pageindex==41)
                              <input type="email" name="email" class="form-control" value="{{$musteri_bilgi->email}}">
                              @else
                              <input type="email" name="email" class="form-control" value="">
                              @endif
                           </div>
                         
                           <div class="col-md-3 col-xs-6 col-sm-6 col-6">
                              <label>Cinsiyet</label>
                              <select class="form-control" name="cinsiyet">
                                 @if($pageindex==41 && $musteri_bilgi->cinsiyet === 0)
                                 <option value="">Belirtilmemiş</option>
                                 <option selected value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                                 @elseif($pageindex==41 && $musteri_bilgi->cinsiyet === 1)
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
                           <div class="col-md-3 col-xs-6 col-sm-6 col-6">
                              <label>Referans </label>
                              <select class="form-control" name="musteri_referans">
                                 @if($pageindex == 41)
                                       @if($portfoy->musteri_tipi==1)
                                       <option value='' >Yok</option>
                                          <option selected value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option  value="3">Instagram</option>
                                          <option   value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==2)
                                        <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option selected value="2">Reklam</option>
                                          <option  value="3">Instagram</option>
                                          <option   value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==3)
                                        <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option selected value="3">Instagram</option>
                                          <option   value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==4)
                                       <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option value="3">Instagram</option>
                                          <option  selected value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==5)
                                       <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option value="3">Instagram</option>
                                          <option  value="4">Facebook</option>
                                          <option selected value="5">Tanıdık</option>
                                       @else
                                          <option value='' selected>Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option value="3">Instagram</option>
                                          <option  value="4">Facebook</option>
                                          <option value="5">Tanıdık</option>
                                       @endif
                               
                                 @else
                                 <option value='' selected>Yok</option>
                                 <option  value="1">İnternet</option>
                                 <option  value="2">Reklam</option>
                                 <option value="3">Instagram</option>
                                 <option  value="4">Facebook</option>
                                 <option value="5">Tanıdık</option>
                                 @endif
                              </select>
                           </div>
                           <div class="col-md-12">
                              <label>Notlar</label>
                              <textarea class="form-control" name="ozel_notlar" >@if($pageindex==41){{$musteri_bilgi->ozel_notlar}}@endif</textarea>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" id='musteri_ekle_modal_kapat' data-dismiss="modal">Kapat</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
         <div id="musteri-bilgi-duzenle-modal" class="modal modal-top fade">
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="width: 950px; max-height: 90%;">
                  <form class="musteri_bilgi_formu" method="POST">
                     {{ csrf_field() }}
                     @if($pageindex==41)
                     <input type="hidden" name="musteri_id" value="{{$musteri_bilgi->id}}">
                     @else
                     <input type="hidden" name="musteri_id">
                     @endif
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name='eklendi_yanit_goster'>
                     <div class="modal-header">
                        <h2  class="modal_baslik"></h2>
                     </div>
                     <div class="modal-body">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>Ad Soyad</label>
                                 @if($pageindex==41)
                                 <input type="text" name="ad_soyad" required class="form-control" value="{{$musteri_bilgi->name}}">
                                 @else
                                 <input type="text" name="ad_soyad" required class="form-control" value="">
                                 @endif
                              </div>
                           </div>
                           <div class="col-md-6">
                              <label>Telefon  </label>
                              @if($pageindex==41)
                              <input type="tel" name="telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control" value="{{$musteri_bilgi->cep_telefon}}">
                              @else
                               <input type="tel" name="telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control" value="">
                              @endif
                           </div>
                           <div class="col-md-6">
                              <label>E-posta </label>
                              @if($pageindex==41)
                              <input type="email" name="email" class="form-control" value="{{$musteri_bilgi->email}}">
                              @else
                              <input type="email" name="email" class="form-control" value="">
                              @endif
                           </div>
                           <div class="col-md-6">
                              <label style="width:100%">Doğum Tarihi</label>
                              @if($pageindex==41) 
                              <select class="form-control opsiyonelSelectGun" name="dogum_tarihi_gun">
                                 <option></option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='01') ? 'selected':''}} value="01">01</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='02') ? 'selected':''}} value="02">02</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='03') ? 'selected':''}} value="03">03</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='04') ? 'selected':''}} value="04">04</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='05') ? 'selected':''}} value="05">05</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='06') ? 'selected':''}} value="06">06</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='07') ? 'selected':''}} value="07">07</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='08') ? 'selected':''}} value="08">08</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='09') ? 'selected':''}} value="09">09</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='10') ? 'selected':''}} value="10">10</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='11') ? 'selected':''}} value="11">11</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='12') ? 'selected':''}} value="12">12</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='13') ? 'selected':''}} value="13">13</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='14') ? 'selected':''}} value="14">14</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='15') ? 'selected':''}} value="15">15</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='16') ? 'selected':''}} value="16">16</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='17') ? 'selected':''}} value="17">17</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='18') ? 'selected':''}} value="18">18</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='19') ? 'selected':''}} value="19">19</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='20') ? 'selected':''}} value="20">20</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='21') ? 'selected':''}} value="21">21</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='22') ? 'selected':''}} value="22">22</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='23') ? 'selected':''}} value="23">23</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='24') ? 'selected':''}} value="24">24</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='25') ? 'selected':''}} value="25">25</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='26') ? 'selected':''}} value="26">26</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='27') ? 'selected':''}} value="27">27</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='28') ? 'selected':''}} value="28">28</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='29') ? 'selected':''}} value="29">29</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='30') ? 'selected':''}} value="30">30</option>
                                 <option {{(date('d',strtotime($musteri_bilgi->dogum_tarihi))=='31') ? 'selected':''}} value="31">31</option>
                              </select>
                              <select class="form-control opsiyonelSelectAy" name="dogum_tarihi_ay">
                                 <option></option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='01') ? 'selected':''}} value="01">Ocak</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='02') ? 'selected':''}} value="02">Şubat</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='03') ? 'selected':''}} value="03">Mart</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='04') ? 'selected':''}} value="04">Nisan</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='05') ? 'selected':''}} value="05">Mayıs</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='06') ? 'selected':''}} value="06">Haziran</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='07') ? 'selected':''}} value="07">Temmuz</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='08') ? 'selected':''}} value="08">Ağustos</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='09') ? 'selected':''}} value="09">Eylül</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='10') ? 'selected':''}} value="10">Ekim</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='11') ? 'selected':''}} value="11">Kasım</option>
                                 <option {{(date('m',strtotime($musteri_bilgi->dogum_tarihi))=='12') ? 'selected':''}} value="12">Aralık</option>
                              </select>
                              <select class="form-control opsiyonelSelectYil" name="dogum_tarihi_yil">
                                 <option></option>
                                  @for($i=1900;$i<=date('Y');$i++)

                                    <option {{(date('Y',strtotime($musteri_bilgi->dogum_tarihi))==$i) ? 'selected':''}} value="{{$i}}">{{$i}}</option>
                                  @endfor
                              </select>
                              @else
                                 <select class="form-control opsiyonelSelectGun" name="dogum_tarihi_gun">
                                    <option></option>
                                 <option value="01">01</option>
                                 <option value="02">02</option>
                                 <option value="03">03</option>
                                 <option value="04">04</option>
                                 <option value="05">05</option>
                                 <option value="06">06</option>
                                 <option value="07">07</option>
                                 <option value="08">08</option>
                                 <option value="09">09</option>
                                 <option value="10">10</option>
                                 <option value="11">11</option>
                                 <option value="12">12</option>
                                 <option value="13">13</option>
                                 <option value="14">14</option>
                                 <option value="15">15</option>
                                 <option value="16">16</option>
                                 <option value="17">17</option>
                                 <option value="18">18</option>
                                 <option value="19">19</option>
                                 <option value="20">20</option>
                                 <option value="21">21</option>
                                 <option value="22">22</option>
                                 <option value="23">23</option>
                                 <option value="24">24</option>
                                 <option value="25">25</option>
                                 <option value="26">26</option>
                                 <option value="27">27</option>
                                 <option value="28">28</option>
                                 <option value="29">29</option>
                                 <option value="30">30</option>
                                 <option value="31">31</option>
                              </select>
                              <select class="form-control opsiyonelSelectAy" name="dogum_tarihi_ay">
                                 <option></option>
                                 <option value="01">Ocak</option>
                                 <option value="02">Şubat</option>
                                 <option value="03">Mart</option>
                                 <option value="04">Nisan</option>
                                 <option value="05">Mayıs</option>
                                 <option value="06">Haziran</option>
                                 <option value="07">Temmuz</option>
                                 <option value="08">Ağustos</option>
                                 <option value="09">Eylül</option>
                                 <option value="10">Ekim</option>
                                 <option value="11">Kasım</option>
                                 <option value="12">Aralık</option>
                              </select>
                              <select class="form-control opsiyonelSelectYil" name="dogum_tarihi_yil">
                                 <option></option>
                                  @for($i=1900;$i<=date('Y');$i++)
                                 <option value="{{$i}}">{{$i}}</option>
                                  @endfor
                              </select>
                              @endif
                           </div>
                           <div class="col-md-6">
                              <label>TC Kimlik No</label>
                              @if($pageindex==41)
                              <input type="tel" name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'"  class="form-control" value="{{$musteri_bilgi->tc_kimlik_no}}">
                              @else
                              <input type="tel" name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'"  class="form-control" value="">
                              @endif
                           </div>
                           <div class="col-md-3">
                              <label>Cinsiyet</label>
                              <select class="form-control" name="cinsiyet">
                                 @if($pageindex==41 && $musteri_bilgi->cinsiyet === 0)
                                 <option value="">Belirtilmemiş</option>
                                 <option selected value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                                 @elseif($pageindex==41 && $musteri_bilgi->cinsiyet === 1)
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
                           <div class="col-md-3">
                              <label>Referans </label>
                              <select class="form-control" name="musteri_referans">
                                 @if($pageindex == 41)
                                       @if($portfoy->musteri_tipi==1)
                                       <option value='' >Yok</option>
                                          <option selected value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option  value="3">Instagram</option>
                                          <option   value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==2)
                                        <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option selected value="2">Reklam</option>
                                          <option  value="3">Instagram</option>
                                          <option   value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==3)
                                        <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option selected value="3">Instagram</option>
                                          <option   value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==4)
                                       <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option value="3">Instagram</option>
                                          <option  selected value="4">Facebook</option>
                                          <option  value="5">Tanıdık</option>
                                       @elseif($portfoy->musteri_tipi==5)
                                       <option value='' >Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option value="3">Instagram</option>
                                          <option  value="4">Facebook</option>
                                          <option selected value="5">Tanıdık</option>
                                       @else
                                          <option value='' selected>Yok</option>
                                          <option  value="1">İnternet</option>
                                          <option  value="2">Reklam</option>
                                          <option value="3">Instagram</option>
                                          <option  value="4">Facebook</option>
                                          <option value="5">Tanıdık</option>
                                       @endif
                               
                                 @else
                                 <option value='' selected>Yok</option>
                                 <option  value="1">İnternet</option>
                                 <option  value="2">Reklam</option>
                                 <option value="3">Instagram</option>
                                 <option  value="4">Facebook</option>
                                 <option value="5">Tanıdık</option>
                                 @endif
                              </select>
                           </div>
                           <div class="col-md-12">
                              <label>Notlar</label>
                              <textarea class="form-control" name="ozel_notlar" >@if($pageindex==41){{$musteri_bilgi->ozel_notlar}}@endif</textarea>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" id='musteri_ekle_modal_kapat' data-dismiss="modal">Kapat</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
         <div
            id="adisyon_yeni_urun_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="adisyon_urun_satisi_yeni_adisyon"  method="POST">
                     <div class="modal-header">
                        <h2>Yeni Ürün Satışı</h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="sube" id="sube" value="{{$isletme->id}}">
                        <input type="hidden" name="adisyon_id">
                         
                        <div class="urunler_bolumu_adisyon">
                           <div class="row" data-value="0">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Ürün</label>
                                    <select name="urunyeniadisyon[]" class="form-control custom-select2" style="width: 100%;">
                                       @foreach(\App\Urunler::where('salon_id',$isletme->id)->get() as $urun)
                                       <option value="{{$urun->id}}">{{$urun->urun_adi}}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-2 col-xs-5 col-sm-5 col-5">
                                 <div class="form-group">
                                    <label>Adet</label>
                                    <input type="tel" required name="urun_adedi_adisyon[]" value="1" class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-2   col-xs-5 col-sm-5 col-5">
                                 <div class="form-group">
                                    <label>Fiyat</label>
                                    <input type="tel" required name="urun_fiyatiadisyon[]" value="{{(\App\Urunler::where('salon_id',$isletme->id)->first()!==null) ? \App\Urunler::where('salon_id',$isletme->id)->first()->fiyat : ''}}" class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-2  col-xs-2 col-sm-2 col-2">
                                 <div class="form-group">
                                    <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                    <button type="button" name="urun_formdan_sil_adisyon"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <button type="button" id="bir_urun_daha_ekle_adisyon" class="btn btn-secondary btn-lg btn-block">
                                 Bir Ürün Daha Ekle
                                 </button>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label>Satıcı</label>
                                 
                                 <select name="urun_satici_adisyon" class="form-control custom-select2" style="width: 100%;">
                                     @if(Auth::user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                  @else
                                    @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                    <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                    @endforeach
                                    @endif
                                 </select>
                                
                              </div>
                           </div> 
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                              Kaydet
                              </button>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button id="adisyon_urun_modal_kapat"
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 >
                              <i class="fa fa-times"></i> Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
              
            </div>
         </div> 
         <div
            id="senet_yeni_urun_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="urun_satisi_senet"  method="POST">
                     <div class="modal-header">
                        <h2>Yeni Ürün Satışı</h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="sube" id="sube" value="{{$isletme->id}}">
                        <input type="hidden" name="adisyon_id">
                         
                        <div class="urunler_bolumu_senet">
                           <div class="row" data-value="0">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Ürün</label>
                                    <select name="urunyenisenet[]" class="form-control custom-select2" style="width: 100%;">
                                       @foreach(\App\Urunler::where('salon_id',$isletme->id)->get() as $urun)
                                       <option value="{{$urun->id}}">{{$urun->urun_adi}}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label>Adet</label>
                                    <input type="tel" required name="urun_adedi_senet[]" value="1" class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label>Fiyat</label>
                                    <input type="tel" required name="urun_fiyatisenet[]" value="{{(\App\Urunler::where('salon_id',$isletme->id)->first()!==null) ? \App\Urunler::where('salon_id',$isletme->id)->first()->fiyat : ''}}" class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-2">
                                 <div class="form-group">
                                    <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                    <button type="button" name="urun_senetten_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <button type="button" id="bir_urun_daha_ekle_senet" class="btn btn-secondary btn-lg btn-block">
                                 Bir Ürün Daha Ekle
                                 </button>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label>Satıcı</label>
                                 
                                 <select name="urun_satici_senet" class="form-control custom-select2" style="width: 100%;">
                                     @if(Auth::user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                  @else
                                    @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                    <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                    @endforeach
                                    @endif
                                 </select>
                                
                              </div>
                           </div> 
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                              Kaydet
                              </button>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6">
                              <button id="senet_urun_modal_kapat"
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 >
                              <i class="fa fa-times"></i> Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
              
            </div>
         </div>
         <div
         id="paket_satisi_modal_adisyon"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content" >
               <form id="paket_satisi_adisyon"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Paket Satışı</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                       
                     <div class="paketler_bolumu_adisyon">
                        <div class="row" data-value="0">
                           <div class="col-md-3">
                              <div class="form-group">
                                  
                                 <label>Paket Adı</label>
                                 <select name="paketadiadisyon[]" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                    <option value="{{$paket->id}}">{{$paket->paket_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3 col-3 col-xs-3 col-sm-3 ">
                              <div class="form-group">
                                 <label>Fiyat (₺)</label>
                                 <input type="tel" name="paketfiyatadisyon[]" value="{{\App\PaketHizmetler::where('paket_id',\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->value('id'))->sum('fiyat')}}"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-3 col-4 col-sm-4 col-xs-4">
                              <div class="form-group">
                                 <label>Başlangıç Tarihi</label>
                                 <input name="paketbaslangictarihiadisyon[]" id="" required class="form-control" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2 col-3 col-sm-3 col-xs-3">
                              <div class="form-group">
                                 <label>Seans Aralığı (gün)</label>
                                 <input type="tel" name="seansaralikgunadisyon[]"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-1 col-2 col-sm-2 col-xs-2">
                              <div class="form-group">
                                 <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                 <button type="button" name="paket_formdan_sil_adisyon" disabled  data-value="0" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-secondary btn-lg btn-block" id="bir_paket_daha_ekle_adisyon">
                              Bir Paket Daha Ekle
                              </button>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#paket-modal">Sisteme Yeni Paket Ekle</button>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                             
                              <label>Satıcı</label>
                             
                              <select name="paket_satici_adisyon" class="form-control custom-select2" style="width: 100%;">
                                    @if(Auth::user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                                   @else
                                 @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                 <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                 @endforeach
                                  @endif
                              </select>
                             

                           </div>
                        </div>
                         
                     </div>
                  </div>
                  <div class="modal-footer" style="display: block;">
                     <div class="row">
                        <div class="col-6 col-sm-6 col-xs-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                        </div>
                        <div class="col-6 col-sm-6 col-xs-6">
                           <button id='adisyon_paket_modal_kapat'
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               
               </form>
            </div>
         </div>
      </div>
      <div
         id="paket_satisi_modal_senet"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content" >
               <form id="paket_satisi_senet"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Paket Satışı</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                       
                     <div class="paketler_bolumu_senet">
                        <div class="row" data-value="0">
                           <div class="col-md-3">
                              <div class="form-group">
                                  
                                 <label>Paket Adı</label>
                                 <select name="paketadisenet[]" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                    <option value="{{$paket->id}}">{{$paket->paket_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Fiyat (₺)</label>
                                 <input type="tel" name="paketfiyatsenet[]" value="{{\App\PaketHizmetler::where('paket_id',\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->value('id'))->sum('fiyat')}}"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Başlangıç Tarihi</label>
                                 <input name="paketbaslangictarihisenet[]" id="" required class="form-control" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>Seans Aralığı (gün)</label>
                                 <input type="tel" name="seansaralikgunsenet[]"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                 <button type="button" name="paket_formdan_sil_senet" disabled  data-value="0" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-secondary btn-lg btn-block" id="bir_paket_daha_ekle_senet">
                              Bir Paket Daha Ekle
                              </button>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#paket-modal">Sisteme Yeni Paket Ekle</button>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                             
                              <label>Satıcı</label>
                             
                              <select name="paket_satici_senet" class="form-control custom-select2" style="width: 100%;">
                                    @if(Auth::user()->hasRole('Personel'))
                                                      <option selected value="{{Auth::user()->personel_id}}">{{Auth::user()->name}}</option> 
                                                   @else
                                 @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',true)->get() as $personel)
                                 <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                 @endforeach
                                  @endif
                              </select>
                             

                           </div>
                        </div>
                         
                     </div>
                  </div>
                  <div class="modal-footer" style="display: block;">
                     <div class="row">
                        <div class="col-6 col-sm-6 col-xs-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                        </div>
                        <div class="col-6 col-sm-6 col-xs-6">
                           <button id='senet_paket_modal_kapat'
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               
               </form>
            </div>
         </div>
      </div>
      <div
         id="paket-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1100px; max-height: 90%;">
               <form id="paket_formu"  method="POST">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <h2 class="text-blue h2 mb-10">Yeni Paket</h2>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Paket Adı</label>
                              <input type="text" required name="adpaket" class="form-control" required>
                           </div>
                        </div>
                        <div class="paket_hizmetler_bolumu" style="margin-left: 20px">
                           <div class="row" data-value="0">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Hizmet</label>
                                    <select name="hizmetler[]" class="form-control custom-select2" style="width:100%">
                                       @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmetliste)
                                       <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-3 col-5 col-xs-5 col-sm-5">
                                 <div class="form-group">
                                    <label>Seans</label>
                                    <input type="tel" required name="seanslar[]" class="form-control" required>
                                 </div>
                              </div>
                              <div class="col-md-4 col-5 col-xs-5 col-sm-5">
                                 <div class="form-group">
                                    <label>Fiyat (₺)</label>
                                    <input type="tel" name="fiyatlar[]" class="form-control" required>
                                 </div>
                              </div>
                              <div class="col-md-1 col-2 col-xs-2 col-sm-2">
                                 <div class="form-group">
                                    <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                    <button type="button" name="paket_hizmet_formdan_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" id="paket_hizmet_daha_ekle" class="btn btn-secondary btn-lg btn-block">
                              Pakete Bir Hizmet Daha Ekle
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button id="modal_kapat_paket"
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>

               </form>
            </div>
            
         </div>
      </div>
      <div
         id="toplu-musteri-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1100px; max-height: 90%;">
               <form id="yenimusterilistesiekle"  method="POST">
                  <div class="modal-header">
                      <h2 class="text-blue h2 mb-10">Toplu @if($isletme->salon_turu_id==15) Danışan @else Müşteri @endif Ekle</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                      <div class="form-group">
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                    
                    
                              <label>Yüklenecek Liste(*.xls veya *.csv excel dosyası)</label>
                              <label style="font-weight: bold;">Not : Excel dosyası kolon isimleri ad soyad, cep telefonu ve varsa e-posta şeklinde olmalıdır. csv dosyasındaki tırnak işaretlerini kaldırınız.</label>
                              <br>
                              <label style="color:green">Örnek xls,xlsx dosyası : <a href="/public/listeler/ornek_data_dosyasi.xlsx"><span class="mdi mdi-download"></span> İndir</a></label>
                               <br>
                              <label style="color:green">Örnek csv dosyası : <a href="/public/listeler/ornek_data_dosyasi.csv"><span class="mdi mdi-download"></span> İndir</a></label>
                              <input type="file" id="listedosyasi_yeni_musteri" name="listedosyasi_yeni_musteri" class="form-control">
                            </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">
                           Ekle
                           </button>
                        </div>
                        <div class="col-md-6">
                           <button id="modal_kapat_paket"
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>

               </form>
            </div>
            
         </div>
      </div>
      <button id='seans_detay_ac' data-toggle='modal' data-target='#seans-detay-modal' style="display:none">Seans Detayı</button>
      <div
         id="seans-detay-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1200px; max-height: 90%;">
               
                  <div class="modal-header">
                      <h2 class="modal_baslik">Seans Detayları</h2>
                       <button
                     type="button"
                     class="close"
                     data-dismiss="modal"
                     aria-hidden="true"
                     >
                  ×
                  </button>
                  </div>
                  <div class="modal-body" id='seans_detayi'>
                    
                       
                  </div>
                   

               
            </div>
            
         </div>
      </div>
      <div
         class="modal fade bs-example-modal-lg"
         id="senet_onay_modal"
        
         >
         <<div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="width:100%">
               <form id='senet_vade_odeme_guncelleme' method="POST">
                   {!!csrf_field()!!}
                  <input name="vade_id" id='vade_id' type="hidden">

                  <input name="sube" value="{{$isletme->id}}" type="hidden">
                  <div class="modal-header">
                     <h2 class="modal-title">
                         Senet Ödeme
                     </h2>
                     <button id='senet_odeme_ekrani_kapat'
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body">
                   
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Ödeme Tarihi</label>
                              <input type="text" name="planlanan_odeme_tarihi" id='vade_odeme_tarihi' required class="form-control date-picker"  value="" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Notlar</label>
                              <input type="text" name="notlar" required class="form-control">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value=0>
                           <div class="col-md-6">
                              <button type="button" id='vade_guncelle' class="btn btn-primary btn-lg btn-block">
                                 Vadeyi Güncelle
                              </button>
                           </div>
                           <div class="col-md-6">
                              <button type="button" id='vade_odendi_olarak_isaretle' class="btn btn-success btn-lg btn-block">
                                  Ödemeyi Tamamla
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <div
         class="modal fade bs-example-modal-lg"
         id="taksit_onay_modal"
        
         >
         <<div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="width:100%">
               <form id='taksit_vade_odeme_guncelleme' method="POST">
                   {!!csrf_field()!!}
                  <input name="vade_id" id='taksit_vade_id' type="hidden">

                  <input name="sube" value="{{$isletme->id}}" type="hidden">
                  <div class="modal-header">
                     <h2 class="modal-title">
                         Taksit Ödeme
                     </h2>
                     <button id='taksit_odeme_ekrani_kapat'
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body">
                   
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Ödeme Tarihi</label>
                              <input type="text" name="planlanan_odeme_tarihi" id='taksit_vade_odeme_tarihi' required class="form-control date-picker"  value="" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Notlar</label>
                              <input type="text" name="notlar" required class="form-control">
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value=0>
                           <div class="col-md-6">
                              <button type="button" id='taksit_vade_guncelle' class="btn btn-primary btn-lg btn-block">
                                 Vadeyi Güncelle
                              </button>
                           </div>
                           <div class="col-md-6">
                              <button type="button" id='taksit_vade_odendi_olarak_isaretle' class="btn btn-success btn-lg btn-block">
                                  Ödemeyi Tamamla
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <div
   id="urun-modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="urun_formu"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="urun_id" id="urun_id" value="0">
               <h2 class="text-blue h2 mb-10" id="urun_modal_baslik">Yeni Ürün</h2>
               <div class="form-group">
                  <label>Ürün Adı</label>
                  <input type="text" required name="urun_adi" id="urun_adi" class="form-control">
               </div>
               <div class="form-group">
                  <label>Fiyat</label>
                  <input type="tel" required name="fiyat" id="fiyat" class="form-control">
               </div>
               <div class="form-group">
                  <label>Stok Adedi</label>
                  <input type="tel" required name="stok_adedi" id="stok_adedi" class="form-control">
               </div>
               <div class="form-group">
                  <label>Düşük Stok Sınırı</label>
                  <input type="tel" required name="dusuk_stok_siniri" id="dusuk_stok_siniri" class="form-control">
               </div>
               <div class="form-group">
                  <label>Barkod</label>
                  <input type="text" name="barkod" id="barkod" class="form-control">
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                     Kaydet
                     </button>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal" 
                        ><i class="fa fa times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
      </div>
      </form>
   </div>
</div>
</div>
      <div
         id="urun-modal-duzenle"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content" style="width: 950px; max-height: 90%;">
               <form id="urun_formu_duzenle"  method="POST">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input name="sube" value="{{$isletme->id}}" type="hidden">
                     <input type="hidden" name="urun_id_duzenle" id="urun_id_duzenle" value="0">
                     <h2 class="text-blue h2 mb-10">Ürün Güncelleme</h2>
                     <div class="form-group">
                        <label>Ürün Adı</label>
                        <input type="text" required name="urun_ad" id="urun_ad" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Fiyat</label>
                        <input type="tel" required name="fiyat_duzenle" id="fiyat_duzenle" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Stok Adedi</label>
                        <input type="tel" required name="stok_aded" id="stok_aded" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Düşük Stok Sınırı</label>
                        <input type="tel" required name="dusuk_stok_siniri" id="dusuk_stok_siniri_duzenle" class="form-control">
                     </div>
                     <div class="form-group">
                        <label>Barkod</label>
                        <input type="text" name="barkod_duzenle" data-inputmask =" 'mask' : '9999999999999'"  id="barkod_duzenle" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button  
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal" 
                              ><i class="fa fa times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
            </div>
            </form>
         </div>
      </div>
      </div>
      <div
                  class="modal fade bs-example-modal-lg"
                  id="ajanda_detay_modal"
                  aria-labelledby="myLargeModalLabel"
                  aria-modal="true"

                  >
                  <div class="modal-dialog modal-lg modal-dialog-centered">
                     <div class="modal-content" style="width:90%">
                        <div class="modal-header">
                           <h4 class="h4">
                              <span class="event-icon weight-400 mr-3"></span
                                 ><span class="event-title"></span>
                           </h4>
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
                           <div class="event-body">
                           </div>
                        </div>
                        <div class="modal-footer event-buttons" style="display:block;">
                        </div>
                     </div>
                  </div>
               </div>
               <div id="yeni_ajanda_ekle" class="modal modal-top fade calendar-modal">
            <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
               <form id="yeni_ajanda_ekle_form">
                  {{ csrf_field() }}
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <input type="hidden" name="ajanda_id" id="ajanda_id" value="0">
                  <div class="modal-content" style="min-height: 350px;">
                     <div class="modal-header">
                        <h4 class="h4">Yeni Not Ekle</h4>
                        <button
                           type="button"
                           class="close"
                           data-dismiss="modal"
                           aria-hidden="true"
                           >
                        ×
                        </button>
                     </div>
                     <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
                        <div class="row">
                           <div class="col-md-6 col-xs-6 col-6 col-sm-6">
                              <label>Başlık</label>
                              <input type="text"  placeholder="Başlık" class="form-control" name="ajandabaslik" id="ajandabaslik" >
                           </div>
                           <div class="col-md-3 col-3 col-xs-3 col-sm-3">
                              <label>Tarih</label>
                              <input type="text" name="ajandatarih" id="ajandatarih" autocomplete="off" class="form-control date-picker" placeholder="Tarih">
                           </div>
                           <div class="col-md-3 col-3 col-xs-3 col-sm-3">
                              <label>Saat</label>
                              <input type="time" id='ajandasaat' class="form-control" value="00:00" name="ajandasaat"  >
                           </div>
                           <div  class="col-md-9 col-sm-9 col-xs-9 col-9">
                              <label>İçerik</label>
                              <textarea type="text" name="ajandaicerik" id="ajandaicerik"  placeholder="İçerik" class="form-control"></textarea> 
                           </div>
                           <div class="col-md-3 col-xs-3 col-sm-3 col-3">
                              <label>Hatırlatma</label><br>
                              <label class="switch">
                              <input   type="checkbox"  id="ajandahatirlatma" name="ajandahatirlatma">
                              <span class="slider" style="border-radius: 5px;"></span>
                              </label> 
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="justify-content: center;">
                        <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                           <button type="submit" class="btn btn-success btn-block"> Kaydet</button>
                        </div>
                     </div>
                   </div>
               </form>
               </div>
            </div>
            <div id="ajanda_duzenle_modal" class="modal modal-top fade calendar-modal" >
               <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
                  <form id="ajanda_duzenle_form">
                     {{ csrf_field() }}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <input type="hidden" name="ajanda_id_duzenle" id="ajanda_id_duzenle" value="0">
                     <div class="modal-content" style="min-height: 350px;">
                        <div class="modal-header">
                           <h4 class="h4">Notu Güncelle</h4>
                           <button
                              type="button"
                              class="close"
                              data-dismiss="modal"
                              aria-hidden="true"
                              >
                           ×
                           </button>
                        </div>
                        <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
                           <div class="row">
                              <div class="col-md-6 col-xs-6 col-6 col-sm-6">
                                 <label>Başlık</label>
                                 <input type="text"  placeholder="Başlık" class="form-control" name="ajandabaslikduzenle" id="ajandabaslikduzenle" >
                              </div>
                              <div class="col-md-3 col-3 col-xs-3 col-sm-3">
                                 <label>Tarih</label>
                                 <input type="text" name="ajandatarihduzenle" id="ajandatarihduzenle" autocomplete="off" class="form-control date-picker" placeholder="Tarih">
                              </div>
                              <div class="col-md-3 col-3 col-xs-3 col-sm-3">
                                 <label>Saat</label>
                                 <input type="time" id='ajandasaatduzenle' class="form-control" value="00:00" name="ajandasaatduzenle"  >
                              </div>
                              <div  class="col-md-9 col-sm-9 col-xs-9 col-9">
                                 <label>İçerik</label>
                                 <textarea type="text" name="ajandaicerikduzenle" id="ajandaicerikduzenle"  placeholder="İçerik" class="form-control"></textarea> 
                              </div>
                              <div class="col-md-3 col-xs-3 col-sm-3 col-3">
                                 <label>Hatırlatma</label><br>
                                 <label class="switch">
                                 <input   type="checkbox"  id="ajandahatirlatmaduzenle" name="ajandahatirlatmaduzenle">
                                 <span class="slider" style="border-radius: 5px;"></span>
                                 </label> 
                              </div>
                           </div>
                        </div>
                        <div class="modal-footer" style="justify-content: center;">
                           <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                              <button type="submit" class="btn btn-success btn-block"> Güncelle</button>
                           </div>
                        </div></div>
                  </form>
                  </div>
               </div>
      @if($pageindex==19||$pageindex==9)
      <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cropper/1.0.1/jquery-cropper.js"></script>
      <script src="/public/yeni_panel/src/plugins/fancybox/dist/jquery.fancybox.js"></script>
      @if($pageindex==19)
      <script>

         var bs_modal = $('#crop_modal');
         var image = document.getElementById('croppedimg');

         var imagewidth;
         var imageheight;
         var cropper,reader,file;
         
         
         $('#yetkili_profil_resmi').change( function(e) {
             var files = e.target.files;
            
         
             var done = function(url) {
                  
                 image.src = url;
         
                
             };
             image.onload = function(){
                 imagewidth = this.width;
                 imageheight = this.height;
                 
                $('#crop_modal_ac').trigger('click'); 
                  
             };
         
         
             if (files && files.length > 0) {
                 file = files[0];
                 
                
         
         
                 if (URL) {
                     done(URL.createObjectURL(file));
                 } else if (FileReader) {
                     reader = new FileReader();
                     reader.onload = function(e) {
                         done(reader.result);
                     };
                     reader.readAsDataURL(file);
                 }
             }
         });
         
         $('#crop_modal_ac').click( function() {
          
             cropper = new Cropper(image, {
                 aspectRatio: 1,
                  
                  
                 minContainerWidth:840,
                 
                 minContainerHeight:$( window ).height()-500
             });
         });
         $('#crop_modal_kapat').click( function() {
             cropper.destroy();
             cropper = null;
         });
         
         $("#crop").click(function() {
             canvas = cropper.getCroppedCanvas({
                 width: 300,
                 height: 300,
             });
         
             canvas.toBlob(function(blob) {
                 url = URL.createObjectURL(blob);
                 var reader = new FileReader();
                 reader.readAsDataURL(blob);
                 reader.onloadend = function() {
                     var base64data = reader.result;
         
                     $('#mevcut_yetkili_profil_resmi').attr('src',base64data);
                     $('#profil_resim_dashboard_top').attr('src',base64data);
                     $.ajax({
                       type: "POST",
                       url: '/isletmeyonetim/profilresimyukle',
                       data: {profilresmi:base64data,_token: $('input[name="_token"]').val()},
                       dataType: "text",
                     beforeSend: function(){
                          $('#preloader').show();
                        },
                      success: function(result) {
                           $('#preloader').hide();
                           swal({
                              type: "success",
                              title: "Başarılı",
                              
                              text:  "Profil resmi başarıyla değiştirildi",
                              showCloseButton: false,
                              showCancelButton: false,
                              showConfirmButton:false,
                              timer: 3000,
                           });  
                        $('#crop_modal_kapat').trigger('click');
                        cropper.destroy();
                        cropper = null;
         
                      },
                      error: function (request, status, error) {
                          $('#preloader').hide();
                          document.getElementById('hata').innerHTML = request.responseText;
                         
                      }
                  });
                     //$('#mevcut_yetkili_profil_resmi').attr('href',base64data);
                     //alert(base64data);
                     /*$.ajax({
                         type: "POST",
                         dataType: "json",
                         url: "crop_image_upload.php",
                         data: {image: base64data},
                         success: function(data) { 
                             bs_modal.modal('hide');
                             alert("success upload image");
                         }
                     });*/
                 };
             });
         });
         
         
      </script>
      @endif
      @if($pageindex==9)
      <script>
         var bs_modal = $('#crop_modal');
         var image = document.getElementById('croppedimg');
        var image2 = document.getElementById('croppedimg2');
         var imagewidth;
         var imageheight;
         var cropper,reader,file;
         
         
         $('#isletmekapakfoto').change( function(e) {
             var files = e.target.files;
             
             var done = function(url) {
                  
                 image.src = url;
         
                
             };
             image.onload = function(){
                 imagewidth = this.width;
                 imageheight = this.height;
                 
                $('#crop_modal_ac').trigger('click'); 
                  
             };
         
         
             if (files && files.length > 0) {
                 file = files[0]; 
         
                 if (URL) {
                     done(URL.createObjectURL(file));
                 } else if (FileReader) {
                     reader = new FileReader();
                     reader.onload = function(e) {
                         done(reader.result);
                     };
                     reader.readAsDataURL(file);
                 }
             }
         });
         
         $('#crop_modal_ac').click( function() {
            
             cropper = new Cropper(image, {
                 aspectRatio: 16/9,
                  
                  
                 minContainerWidth:840,
                 
                 minContainerHeight:$( window ).height()-500
             });
         });
         $('#crop_modal_kapat').click( function() {
             cropper.destroy();
             cropper = null;
         });
         
         $("#crop").click(function() {
             canvas = cropper.getCroppedCanvas({
                 width: 1200,
                 height: 500,
             });
         
             canvas.toBlob(function(blob) {
                 url = URL.createObjectURL(blob);
                 var reader = new FileReader();
                 reader.readAsDataURL(blob);
                 reader.onloadend = function() {
                     var base64data = reader.result;
         
                     $('#profilkapak').attr('src',base64data);
                     
                     $.ajax({
                        type: "POST",
                        url: '/isletmeyonetim/isletmekapakresimyukle',
                        data: {kapakresmi:base64data,_token: $('input[name="_token"]').val(),sube:$('input[name="sube"]').val()},
                        dataType: "text",
                        beforeSend: function(){
                          $('#preloader').show();
                        },
                        success: function(result) {
                           $('#preloader').hide();
                           swal({
                              type: "success",
                              title: "Başarılı",
                              
                              text:  "Kapak görseli başarıyla değiştirildi",
                              showCloseButton: false,
                              showCancelButton: false,
                              showConfirmButton:false,
                              timer: 3000,
                           });
                           $('#crop_modal_kapat').trigger('click');
                           cropper.destroy();
                           cropper = null;
            
                        },
                        error: function (request, status, error) {
                           $('#preloader').hide();
                          document.getElementById('hata').innerHTML = request.responseText;
                         
                      }
                  });
                     //$('#mevcut_yetkili_profil_resmi').attr('href',base64data);
                     //alert(base64data);
                     /*$.ajax({
                         type: "POST",
                         dataType: "json",
                         url: "crop_image_upload.php",
                         data: {image: base64data},
                         success: function(data) { 
                             bs_modal.modal('hide');
                             alert("success upload image");
                         }
                     });*/
                 };
             });
         });
          
         
         
      </script>
      @endif
      @endif
      @if($pageindex==17)
      <script type="text/javascript">
         $(document).ready(function(){
            $('#senet_liste').DataTable().destroy();
            $('#senet_liste').DataTable({
                       
                     autoWidth: false,
                     responsive: true,
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "dsc" ]],
                    data: <?php echo $senetler; ?>,
         
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
         
                     
                   
         
            });
            $('#senet_liste_acik').DataTable().destroy();
            $('#senet_liste_acik').DataTable({
                       
                     autoWidth: false,
                     responsive: true,
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $senetler_acik; ?>,
         
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
         
                     
                   
         
            });
            $('#senet_liste_kapali').DataTable().destroy();
            $('#senet_liste_kapali').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $senetler_kapali; ?>,
         
                    "language" : {
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                        searchPlaceholder: "Ara",
                        paginate: {
                            next: '<i class="ion-chevron-right"></i>',
                            previous: '<i class="ion-chevron-left"></i>'  
                        }
                    },
         
                     
                   
         
            });
            $('#senet_liste_odenmemis').DataTable().destroy();
            $('#senet_liste_odenmemis').DataTable({
                       autoWidth: false,
                     responsive: true,
                        
                    columns:[
                        {data: 'durum' },
                        {data: 'ad_soyad' },
                        {data: 'vade_sayisi' }, 
                        {data: 'odenmis' },
                        {data: 'odenmemis'},
                        {data: 'yaklasan_vade' },
                        {data: 'islemler'},
                    ],
                     "order": [[ 5, "asc" ]],
                    data: <?php echo $senetler_odenmemis; ?>,
         
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
      @if($pageindex==103)
      <script type="text/javascript">
         $(document).ready(function(){

         });
      </script>
      @endif
      @if($pageindex==20)
      <script type="text/javascript">
         $(document).ready(function(){
              $('#etkinlik_tablo').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                     
                      { data: 'tarih'   },
                      { data: 'etkinlik_adi' },
                        
                      { data: 'katilimci_sayisi'   },
                      { data: 'fiyat' },
                      { data: 'islemler' },
                 
                         
         
                       
                  
                      
                   ],
                   data: <?php echo $katilimci; ?>,
         
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
         
      </script>@endif
      @if($pageindex==22)
      <script type="text/javascript">
         $(document).ready(function(){
              $('#kampanyayonetim_tablo_1').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                     
                      { data: 'paket_isim'   },
                      { data: 'seans' },
                     { data: 'katilimci_sayisi'   },
                      { data: 'hizmet_adi'   },
                      { data: 'fiyat' },
                      { data: 'islemler' },
                 
                         
         
                       
                  
                      
                   ],
                   data: <?php echo $kampanya_yonetimi; ?>,
         
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
      
      @if($pageindex==106)
      <script type="text/javascript">

         
         $(document).ready(function(){
            $('#karaliste_sms_tablo').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                     
                      { data: 'ad_soyad', className: "text-center",   },
                      { data: 'telefon',className: "text-center", },
                       { data: 'eklenme_tarihi',className: "text-center", },
                      { data: 'islemler',className: "text-right"  }, 
                      
                   ],
                   data: <?php echo $karaliste; ?>,
         
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },
               });
               $('#grup_sms_tablo').DataTable({
                 autoWidth: false,
                  responsive: true,
                   columns:[
                     
                      { data: 'grup_adi', className: "text-center",   },
                      { data: 'grup_katilimci_sayisi',className: "text-center", },
                     
                      { data: 'islemler',className: "text-right"  },
                 
                         
         
                       
                  
                      
                   ],
                   data: <?php echo $grup; ?>,
         
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },
               });
               $('#bildirim_sms_raporlari').DataTable({
                  autoWidth: false,
                  responsive: true,
                  "order": [[ 0, "desc" ]],
                  columns:[
                     
                     { data: 'date' },
                     { data: 'count' },
                     { data: 'price' ,'render': function(data, type, row, meta){
                           return data*row.count;
                     }},
                     { data: 'msgdetails', "width": "400px"  },
                     { data: 'status' ,'render': function(data, type, row, meta){
                        if(data==0 || data=='' || data==null)
                           return 'Bekliyor';
                        if(data==1)
                           return 'Gönderildi';
                        if(data==2)
                           return 'Gönderildi';
                        if(data==3)
                           return 'Gönderildi';
                        if(data==4)
                           return 'İleri Tarihli';
                        if(data==10)
                           return 'Onay Bekliyor';
                        if(data==91)
                           return 'Gönderilemedi (Bakiye Yetersiz)';
                        if(data==92)
                           return 'Gönderilemedi (Gönderimler Durdurulmuştu)';
                        if(data==93)
                           return 'Gönderilemedi (Teknik Arıza)';
                        if(data==94)
                           return 'Gönderim Engellendi';
                        if(data==95)
                           return 'İptal Edildi';
                        if(data==99)
                           return 'Gönderildi';
                     }}, 
                   ],
                   data: <?php echo $raporlar['bildirim']; ?>,
                  
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },

               });
               $('#grup_sms_raporlari').DataTable({
                  autoWidth: false,
                  responsive: true,
                  "order": [[ 0, "desc" ]],
                  columns:[
                     
                     { data: 'date' },
                     { data: 'count' },
                     { data: 'price' ,'render': function(data, type, row, meta){
                           return data*row.count;
                     }},
                     { data: 'msgdetails', "width": "400px"  },
                     { data: 'status' ,'render': function(data, type, row, meta){
                        if(data==0)
                           return 'Bekliyor';
                        if(data==1)
                           return 'Gönderildi';
                        if(data==2)
                           return 'Gönderildi';
                        if(data==3)
                           return 'Gönderildi';
                        if(data==4)
                           return 'İleri Tarihli';
                        if(data==10)
                           return 'Onay Bekliyor';
                        if(data==91)
                           return 'Gönderilemedi (Bakiye Yetersiz)';
                        if(data==92)
                           return 'Gönderilemedi (Gönderimler Durdurulmuştu)';
                        if(data==93)
                           return 'Gönderilemedi (Teknik Arıza)';
                        if(data==94)
                           return 'Gönderim Engellendi';
                        if(data==95)
                           return 'İptal Edildi';
                        if(data==99)
                           return 'Gönderildi';
                     }}, 
                   ],
                   data: <?php echo $raporlar['grup']; ?>,
                  
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },

               });
               $('#filtreli_sms_raporlari').DataTable({
                  autoWidth: false,
                  responsive: true,
                  "order": [[ 0, "desc" ]],
                  columns:[
                     
                     { data: 'date' },
                     { data: 'count' },
                     { data: 'price' ,'render': function(data, type, row, meta){
                           return data*row.count;
                     }},
                     { data: 'msgdetails', "width": "400px"  },
                     { data: 'status' ,'render': function(data, type, row, meta){
                        if(data==0)
                           return 'Bekliyor';
                        if(data==1)
                           return 'Gönderildi';
                        if(data==2)
                           return 'Gönderildi';
                        if(data==3)
                           return 'Gönderildi';
                        if(data==4)
                           return 'İleri Tarihli';
                        if(data==10)
                           return 'Onay Bekliyor';
                        if(data==91)
                           return 'Gönderilemedi (Bakiye Yetersiz)';
                        if(data==92)
                           return 'Gönderilemedi (Gönderimler Durdurulmuştu)';
                        if(data==93)
                           return 'Gönderilemedi (Teknik Arıza)';
                        if(data==94)
                           return 'Gönderim Engellendi';
                        if(data==95)
                           return 'İptal Edildi';
                        if(data==99)
                           return 'Gönderildi';
                     }}, 
                   ],
                   data: <?php echo $raporlar['filtre']; ?>,
                  
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },

               });
         
               $('#toplu_sms_raporlari').DataTable({
                  autoWidth: false,
                  responsive: true,
                  "order": [[ 0, "desc" ]],
                  columns:[
                     
                     { data: 'date' },
                     { data: 'count' },
                     { data: 'price' ,'render': function(data, type, row, meta){
                           return data*row.count;
                     }},
                     { data: 'msgdetails', "width": "400px"  },
                     { data: 'status' ,'render': function(data, type, row, meta){
                        if(data==0)
                           return 'Bekliyor';
                        if(data==1)
                           return 'Gönderildi';
                        if(data==2)
                           return 'Gönderildi';
                        if(data==3)
                           return 'Gönderildi';
                        if(data==4)
                           return 'İleri Tarihli';
                        if(data==10)
                           return 'Onay Bekliyor';
                        if(data==91)
                           return 'Gönderilemedi (Bakiye Yetersiz)';
                        if(data==92)
                           return 'Gönderilemedi (Gönderimler Durdurulmuştu)';
                        if(data==93)
                           return 'Gönderilemedi (Teknik Arıza)';
                        if(data==94)
                           return 'Gönderim Engellendi';
                        if(data==95)
                           return 'İptal Edildi';
                        if(data==99)
                           return 'Gönderildi';
                     }}, 
                   ],
                   data: <?php echo $raporlar['toplu']; ?>,
                  
                   "language" : {
                       "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                       searchPlaceholder: "Ara",
                       paginate: {
                           next: '<i class="ion-chevron-right"></i>',
                           previous: '<i class="ion-chevron-left"></i>'  
                       }
                    },

               });
               $('#kampanya_sms_raporlari').DataTable({
                  autoWidth: false,
                  responsive: true,
                  "order": [[ 0, "desc" ]],
                  columns:[
                     
                     { data: 'date' },
                     { data: 'count' },
                     { data: 'price' ,'render': function(data, type, row, meta){
                           return data*row.count;
                     }},
                     { data: 'msgdetails', "width": "400px"  },
                     { data: 'status' ,'render': function(data, type, row, meta){
                        if(data==0)
                           return 'Bekliyor';
                        if(data==1)
                           return 'Gönderildi';
                        if(data==2)
                           return 'Gönderildi';
                        if(data==3)
                           return 'Gönderildi';
                        if(data==4)
                           return 'İleri Tarihli';
                        if(data==10)
                           return 'Onay Bekliyor';
                        if(data==91)
                           return 'Gönderilemedi (Bakiye Yetersiz)';
                        if(data==92)
                           return 'Gönderilemedi (Gönderimler Durdurulmuştu)';
                        if(data==93)
                           return 'Gönderilemedi (Teknik Arıza)';
                        if(data==94)
                           return 'Gönderim Engellendi';
                        if(data==95)
                           return 'İptal Edildi';
                        if(data==99)
                           return 'Gönderildi';
                     }}, 
                   ],
                   data: <?php echo $raporlar['kampanya']; ?>,
                  
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
      
       <style type="text/css">
.removeall {
  border: 1px solid #ccc !important;
  
  &:hover {
    background: #efefef;
  }
}
.moveall {
  border: 1px solid #ccc !important;
  
  &:hover {
    background: #efefef;
  }
}

.moveall::after {
  content: attr(title);
  
}
.removeall::after {
  content: attr(title);
  
}
ongorusmeformu
.removeall::after {
  content: attr(title);
}


.form-control option {
    padding: 10px;
    border-bottom: 1px solid #efefef;
}



  </style>
  <!-- common libraries -->
 

<!-- plugin -->
<script src="https://www.virtuosoft.eu/code/bootstrap-duallistbox/bootstrap-duallistbox/v3.0.2/jquery.bootstrap-duallistbox.js"></script>

<link rel="stylesheet" type="text/css" href="https://www.virtuosoft.eu/code/bootstrap-duallistbox/bootstrap-duallistbox/v3.0.2/bootstrap-duallistbox.css">
   
  <script type="text/javascript">
 $ ('select[name="duallistbox_demo1[]"]').bootstrapDualListbox({

   
    removeAllLabel: 'Hepsini Kaldır',
                moveAllLabel: 'Tümünü Seç',
    
                removeAllLabel:'Tümünü Kaldır',
    
                infoText: '{0} kişi',  
                infoTextEmpty: 'Boş müşteri listesi', 
                filterPlaceHolder: 'Müşteri Ara',
  });
  $ ('select[name="duallistbox_demo2[]"]').bootstrapDualListbox({

   
     removeAllLabel: 'Hepsini Kaldır',
                moveAllLabel: 'Tümünü Seç',
    
                removeAllLabel:'Tümünü Kaldır',
    
                infoText: '{0} kişi',  
                infoTextEmpty: 'Boş müşteri listesi', 
                filterPlaceHolder: 'Müşteri Ara',
  });
   $ ('select[name="duallistbox_demo3[]"]').bootstrapDualListbox({

   
    removeAllLabel: 'Hepsini Kaldır',
                moveAllLabel: 'Tümünü Seç',
    
                removeAllLabel:'Tümünü Kaldır',
    
                infoText: '{0} kişi',  
                infoTextEmpty: 'Boş müşteri listesi', 
                filterPlaceHolder: 'Müşteri Ara',
  });

 
  </script>
 


      @endif
    
       
      <script src="{{asset('public/js/custom.js?v=50.18')}}"></script>
       @if($pageindex==111)
      <script type="text/javascript">
       
        $(document).ready(function () {
           adisyonyenidenhesapla();
        })
        
      </script>
      @endif 
      @if($pageindex==1111)
      <script type="text/javascript">
       
        $(document).ready(function () {

           tahsilatyenidenhesapla();
        })
        
      </script>
       @endif
       <script src="{{asset('public/js/try.js?v=1.1')}}"></script>
       <script type="text/javascript">
           $(document).ready(function () {
            if($('.try-currency').length)
               $('.try-currency').turkLirasi();
         })
       </script>
       <script src="{{asset('public/js/accounting.js')}}"></script>
   </body>
</html>