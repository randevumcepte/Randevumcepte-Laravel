<html>
   <head>
      <!-- Basic Page Info -->
      <meta charset="utf-8" />
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>{{$sayfa_baslik}} | {{$isletme->salon_adi}} Yönetim Paneli</title>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
      
      @if($pageindex == 2 || $pageindex == 1 )
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.css?v=1.1')}}"
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
      @if($pageindex==40)
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.css?v=1.0')}}"
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
      <script src="{{secure_asset('/public/js/dist/inputmask.min.js')}}"></script> 
      <script src="{{secure_asset('/public/js/dist/jquery.inputmask.min.js')}}"></script> 
      <script src="{{secure_asset('/public/js/dist/bindings/inputmask.binding.js')}}"></script>
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
        #adisyon_yeni_hizmet_modal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

#adisyon_yeni_hizmet_modal .modal-dialog {
    width: 100%;
    max-width: 600px;
    margin: 0;
   
}
      </style>
      <!-- Site favicon -->
      <link
         rel="apple-touch-icon"
         sizes="180x180"
         href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <link
         rel="icon"
         type="image/png"
         sizes="32x32"
         href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <link
         rel="icon"
         type="image/png"
         sizes="16x16"
         href="{{secure_asset('public/yeni_panel/vendors/images/icon.png')}}"
         />
      <!-- Mobile Specific Metas -->
      <meta
         name="viewport"
         content="width=device-width, initial-scale=1, maximum-scale=1"
         />
      <!-- Google Font -->
      <!-- CSS -->
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/core.css?v=1.11')}}" />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/datatables/css/dataTables.bootstrap4.min.css?v=3.0')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/datatables/css/responsive.bootstrap4.min.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
         />
      @if($pageindex==19 ||$pageindex==9 ||$pageindex==41)
      <link
         rel="stylesheet"
         type="text/css"
         href="/public/yeni_panel/src/plugins/fancybox/dist/jquery.fancybox.css"
         />
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css"  />
      @endif
      @if($pageindex==70)
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/jquery-steps/jquery.steps.css')}}"
         />
      @endif
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/style.css?v=20.5')}}" />
      <script src="{{secure_asset('public/js/OneSignalSDKWorker.js')}}"></script>
      <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
      <script>
         window.OneSignal = window.OneSignal || [];
         
         OneSignal.push(function() {
         
           OneSignal.init({
         
             appId: "5e50f84e-2cd8-4532-a765-f2cb82a22ff9",
         
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
         @media(max-width: 1300px)
         {

            
            .header{
               height: 50px !important;
            }
            .user-info-dropdown .dropdown-toggle .user-icon{
               height: 35px !important;
               width: 35px !important;
            }
            .user-notification{
               padding: 10px 0 0 0 !important;
            }
         }
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
         #calendar{
         overflow-x: hidden;
         } 
         .saga-yasli{
            text-align:right !important;
         }
         .ortaya-yasli{
            text-align:center; !important;
         }
         
.fc-event.disabled-event {
   
    border: none !important; /* Kenarlıkları kaldır */
  
}
 .fc-event.disabled-event .fc-time, .fc-event.disabled-event .fc-title{
   display: none
 }
   .fc-right{
         display:none !important;
     }
 @media(max-width:420px)
 {
     .breadcrumb-item {
            
            font-size: 13px!important;
        }
     .fc-center {
         width:50% !important;
         float:left !important;
         
         display:block !important;
     }
     .fc-center h2{
         font-size:16px !important;
        
     }
     .fc-left {
         width:50% !important;
         float:left !important;
     }
     .fc-left button{
         font-size:12px !important;
     }
   
     h1{
         font-size:20px !important;
     }
     .fc-toolbar.fc-header-toolbar {
    margin-bottom: 5px !important;
}
.fc-header-toolbar h2 {
     
    padding-top: 3px !important;
    color: #000 !important;
}
     
 }
.fc-center h2{
   color: #000 !important;
}
      </style>

   </head>
   
   <body>
      <button style="display: none;" id="randevudetayigetir" data-toggle="modal" data-target="#randevu-duzenle-modal"></button>
      <button style="display: none;" id="ajandadetayigetir" data-toggle="modal" data-target="#ajanda_detay_modal"></button>
        <?php 
         require_once app_path('VoiceTelekom/Sms/SmsApi.php');
         require_once app_path('VoiceTelekom/Sms/SendMultiSms.php');
         require_once app_path('VoiceTelekom/Sms/PeriodicSettings.php');
         $kalan_sms_miktar = 0;
         if($isletme->yeni_sms){
   
            
           
           //$smsApi = new \SmsApi("smsvt.voicetelekom.com","webfirmam","nBJeB5xb*4");
           $smsApi = new \SmsApi("smsvt.voicetelekom.com",$isletme->sms_user_name,$isletme->sms_secret);

           $response = $smsApi->getCredit();

           if($response->err == null){
               $kalan_sms_miktar =  $response->credit;
           }              
        
    
         }
         else{
            $headers = array(
                     'Authorization: Key '.$isletme->sms_apikey,
                     'Content-Type: application/json',   
                     'Accept: application/json'   
            );
            
            
            $ch=curl_init();         
            curl_setopt($ch,CURLOPT_URL,'https://api.efetech.net.tr/v2/get/balance');        
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
            $response = curl_exec($ch);     
            curl_close($ch);
            
           if ($isletme->sms_apikey !== null) {
                $kalan_sms = json_decode($response, true);

               if (
                    is_array($kalan_sms) &&
                    isset($kalan_sms['response']) &&
                    is_array($kalan_sms['response']) &&
                    array_key_exists('balance', $kalan_sms['response'])
                ) {
                    $kalan_sms_miktar = $kalan_sms['response']['balance'];
                } else {
                    $kalan_sms_miktar = 0; // veya null, ihtiyacına göre
                }
            } else {
                $kalan_sms_miktar = 0; // veya null
            }
         }
         

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
      <input id='ekleme_onay_ayari' type="hidden" value="{{\App\SalonSMSAyarlari::where('salon_id',$isletme->id)->where('ayar_id',22)->value('musteri')}}">
      <input name="sube" type="hidden" value="{{$isletme->id}}">
      <input id='santral_dahili_no' type="hidden" value="{{\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no')}}">
      <input id='santral_dahili_sifre' type="hidden" value="{{\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_sifre')}}">
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
            @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0  &&  $kalan_uyelik_suresi >= 0)   
            <div class="header-search" >
               <select id="musteri_arama" class="form-control custom-select2" style="width: 100%;">
                  
               </select>
            </div>
            @endif
            @if(count($yetkiliolunanisletmeler)>1)
            <div
               class="search-toggle-icon fa fa-home"
               data-toggle="header_search2"
               style="padding-left: 10px;"
               >
            </div>
            <div class="header-search2" style="margin-left:20px; min-width: 230px;">
               <select id="sube_arama" class="form-control custom-select2" style="width: 100%;">
                  <option value="0">Şube...</option>
                  @foreach(\App\Salonlar::whereIn('id',$yetkiliolunanisletmeler)->get() as $sube)
                  @if($sube->id == $isletme->id)
                  <option selected value="https://{{$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'] , '?')}}?sube={{$sube->id}}">{{$sube->salon_adi}}</option>
                  @else
                  @if($pageindex==9)
                  <option  value="https://{{$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'] , '?')}}?p=temelbilgiler&sube={{$sube->id}}">{{$sube->salon_adi}}</option>
                  @elseif($pageindex==41)
                  <option  value="https://{{$_SERVER['HTTP_HOST']}}/isletmeyonetim?sube={{$sube->id}}">{{$sube->salon_adi}}</option>
                  @else
                  <option  value="https://{{$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'] , '?')}}?sube={{$sube->id}}">{{$sube->salon_adi}}</option>
                  @endif
                  @endif
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
            @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0  )
            <div class="user-notification " style="padding:20px 0 0 0" id="kalansmskaybet">
               <div class="dropdown">
                  <a 
                     class="dropdown-toggle no-arrow btn btn-warning kalansms"
                     href="#" 
                     role="button"
                     data-toggle="dropdown"
                     title='Kalan SMS'
                     style='color:#fff;padding: 5px 7px;'
                     >
                  {{$kalan_sms_miktar}} <i class="icon-copy fa fa-envelope-o headerbuttonicons"></i> </a>
               </div>
            </div>
            @endif
            @if(\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->where('salon_id',$isletme->id)->value('dahili_no') !== null)
            <div class="user-notification " style="padding:20px 0 0 0">
               <div class="dropdown" id="webTelefonDropDown">
                  <!--{{(!\App\Dahililer::where('numara',\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no'))->value('durum')) ? 'dropdown' : 'modal'}}
                     {{(!\App\Dahililer::where('numara',\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no'))->value('durum')) ? '' : 'data-target=#santral-ustune-al'}}
                     
                     -->
                  <span
                     id='webtelefon'
                     class="dropdown-toggle no-arrow {{(\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no') !== null) ? 'btn btn-success':''}}"
                     href="#"
                     role="button"
                     data-toggle="dropdown" 
                     style="cursor: pointer;padding: 5px 7px;color:#fff"
                     >  &nbsp;<i class="icon-copy fi-telephone" ></i> &nbsp<i data-toggle='tooltip' data-placement='bottom' title='Dakika bazlıdır' class="icon-copy bi bi-info-circle-fill" style="font-size:14px"></i>
                  </span>  
                  <div class="dropdown-menu webphone dropdown-menu-left" style="border:1px solid #5C008E">
                     <div class="dtmf bg-primary rounded p-3 text-white-50 text-monospace" style="min-height:110px">
                        <i class="fa fa-phone-square"></i> <span id="dtmf"></span>
                        <b>Durum:</b><br>
                        <code>
                        <span id="target">  
                        Bağlanıyor 
                        </span>
                        </code><br>
                     </div>
                     <div class="row input-group-prepend" id="dial-input">
                        <div class="col-12" style="padding-left: 0;padding-right: 0; border:1px solid #e2e2e2">
                           <div class="form-group">
                              @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 1)

                              <input type="tel" style="display: none;" id="dial" class="form-control"  placeholder="{{(\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no') === null) ? 'Web telefonunu kullanabilmek için lütfen ayarlardan dahili numara ataması yapınız!' : 'Çevirmek istediğiniz dahili/numara'}}"   aria-describedby="dial-input">
                              @else
                              <input type="tel" style="border-radius: 0; padding: 35px; text-align:center; border-color: #fff;" id="dial" class="form-control"  placeholder="{{(\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no') === null) ? 'Web telefonunu kullanabilmek için lütfen ayarlardan dahili numara ataması yapınız!' : 'Çevirmek istediğiniz dahili/numara'}}"   aria-describedby="dial-input">
                              @endif
                           </div>
                           <span style="display: none;" id='aranacak_dahili_telefon'></span>
                        </div>
                     </div>
                     <div class="text-monospace" style="padding:20px">
                        <div class="row">
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='1' class="keypad numkeypad btn btn-block btn-outline-secondary rounded-circle">1</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='2' class="keypad numkeypad btn btn-block btn-outline-secondary rounded-circle">2</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='3' class="keypad numkeypad btn btn-block btn-outline-secondary rounded-circle">3</button>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-4 col-xs-4  text-center">
                              <button  type="button" data-value='4' class="keypad numkeypad btn btn-block btn-outline-secondary rounded-circle">4</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='5' class="keypad numkeypad btn btn-block btn-outline-secondary rounded-circle">5</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type='button' data-value='6' class="keypad numkeypad btn btn-block btn-outline-secondary rounded-circle">6</button>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='7' class="keypad numkeypad btn-block btn btn-outline-secondary rounded-circle">7</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='8' class="keypad numkeypad btn-block btn btn-outline-secondary rounded-circle">8</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='9' class="keypad numkeypad btn-block btn btn-outline-secondary rounded-circle">9</button>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='*' class="keypad numkeypad btn-block btn btn-outline-secondary rounded-circle">*</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='0' class="keypad numkeypad btn-block btn btn-outline-secondary rounded-circle">0</button>
                           </div>
                           <div class="col-4 col-xs-4  text-center">
                              <button   type="button" data-value='#' class="keypad numkeypad btn-block btn btn-outline-secondary rounded-circle">#</button>
                           </div>
                        </div>
                        <div class="row" style="margin-top:30px;">
                           <div class="col-12 text-center">
                              <button id="answer" style="display:none"  class="keypad btn  btn-success rounded-circle" type="button" disabled>
                              <i class="fa fa-phone" id='cevaplayazi1' style="font-size:25px;color: #fff;"></i> <br>
                              <span style="font-size: 10px; color: #fff;" id='cevaplayazi2'>Cevapla</span>
                              </button>
                              <button   id="call" class="keypad btn btn-success rounded-circle" type="button" style="font-size:25px">
                              <i class="fa fa-phone" id='aramayapyazi1'></i><br> <span id='aramayapyazi2' class="callbutton" style="font-size: 10px;">Ara</span>
                              </button>
                              <button id="hangup" class="keypad btn btn-danger rounded-circle" type="button" disabled="">
                              <i id='kapatyazi1' class="icon-copy bi bi-telephone-x-fill" style="font-size:20px;color: #fff;"></i><br> <span id='kapatyazi2' class="callbutton" style="font-size: 10px;">Kapat</span>
                              </button>
                              <button id="hold" class="keypad btn btn-warning rounded-circle" type="button" value="" disabled="">
                              <i id='sesikapatyazi1' class="fa fa-microphone-slash" style="font-size:20px;color: #fff;"></i> <br> <span id='sesikapatyazi2' class="callbutton" style="font-size: 10px;">Beklet</span>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
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
                             <a href="{{ $bildirim->url }}" name="bildirim" data-index-number="{{ $bildirim->id }}" data-value="{{ $bildirim->randevu_id }}">
    
    {{-- Resim varsa onu göster, yoksa varsayılan kullanıcı ikonu --}}
    @if(!empty($bildirim->img_src))
        <img src="{{ $bildirim->img_src }}" alt="" class="mCS_img_loaded">
    @else
        <img src="/public/isletmeyonetim_assets/img/avatar.png" alt="Kullanıcı" class="mCS_img_loaded">
    @endif

    {{-- Başlık (okunmamışsa özel stil) --}}
    @if(!$bildirim->okundu)
        <h3 style="background: rgba(248, 244, 255, 0.9); padding: 5px; border-radius: 5px; color: #888; font-size:13px; ">
    @else
        <h3>
    @endif
        {{ $bildirim->aciklama }}
    </h3>

    {{-- Tarih / Zaman farkı --}}
    <p style="font-size: 10px;">
        <?php
        $to_time = strtotime(date('Y-m-d H:i:s'));
        $from_time = strtotime($bildirim->tarih_saat);
        $diff = round(abs($to_time - $from_time) / 60, 0) . " dakika önce";

        if ($diff >= 60) {
            $diff = round(abs($to_time - $from_time) / 3600, 0) . " saat önce";

            if (round(abs($to_time - $from_time) / 3600, 0) >= 24) {
                $diff = date('d.m.Y H:i', strtotime($bildirim->tarih_saat));
            }
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
            @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
            @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0 )
            <div class="user-notification">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle  no-arrow"
                     href="/isletmeyonetim/ayarlar?p=temelbilgiler&{{(isset($_GET['sube'])) ? 'sube='.$isletme->id : '' }}"
                     >
                  <i class="dw dw-settings2"></i>
                  </a>
               </div>
            </div>
            @endif
            @endif
            @if($kalan_uyelik_suresi >= 0)
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
                        ><i class="fa fa-calendar"></i> Yeni Randevu</a>
                     <a class="dropdown-item" href="#" data-toggle="modal" data-target="#ongorusme-modal" onclick="modalbaslikata('Yeni Ön Görüşme','ongorusmeformu')"
                        ><i class="fa fa-calendar"></i> Yeni Ön Görüşme</a>
                     @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0 )
                     <a  class="dropdown-item yanitli_musteri_ekleme" href="#" data-toggle="modal" data-target="#musteri-bilgi-modal"
                        ><i class="icon-copy fa fa-user-plus" aria-hidden="true"></i> Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</a
                        >
                     @endif
                     @if($isletme->uyelik_turu > 1 )
                     @if( $_SERVER["HTTP_HOST"]!="randevu.randevumcepte.com.tr")
                     <a class="dropdown-item" href="/isletmeyonetim/yenitahsilat/?sube={{$isletme->id}}" 
                        ><i class="icon-copy fa fa-shopping-cart" aria-hidden="true"></i> Yeni Satış & Tahsilat</a
                        >@endif
                     @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                     <a onclick="modalbaslikata('Yeni Masraf','masraf_formu')" class="dropdown-item" href="#"  data-toggle="modal" data-target="#yeni_masraf_modal"
                        ><i class="fa fa-upload"></i> Yeni Masraf</a
                        >
                     @endif
                     @endif
                     @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                     <a class="dropdown-item" href="/isletmeyonetim/form-sablonlari?sube={{$isletme->id}}"
                        ><i class="fa fa-file-text-o"></i> Yeni Form Oluştur</a>
                     @endif
                  </div>
               </div>
            </div>
            @endif
            <div class="user-info-dropdown">
               <div class="dropdown">
                  <a
                     class="dropdown-toggle"
                     href="#"
                     role="button"
                     data-toggle="dropdown"
                     >
                  <span class="user-icon">
                  <img id="profil_resim_dashboard_top" src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null) ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png'}}" alt="Avatar">
                  </span>
                  <span class="user-name">{{Auth::guard('isletmeyonetim')->user()->name}}</span>
                  </a>
                  <div
                     class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                     <a class="dropdown-item" href="/isletmeyonetim/profil{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">
                     <i class="dw dw-user1"></i>
                     Profil Bilgileri
                     </a>
                     <a style="display: none" class="dropdown-item" href="/isletmeyonetim/uyelik{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">
                     <i class="icon-copy fi-shopping-cart"></i>
                     Üyelik
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
            <a href="/isletmeyonetim/randevular{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">
            <img src="{{secure_asset('public/yeni_panel/vendors/images/randevumcepte.png')}}" alt=""   />
            </a>
            <div class="close-sidebar" data-toggle="left-sidebar-close">
               <i class="ion-close-round"></i>
            </div>
         </div>
         <div class="menu-block customscroll">
            <div class="sidebar-menu">
               <ul>
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <!--<li>
                     @if($pageindex==1)
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-house"></span
                        ><span class="mtext">Özet</span>
                     </a>
                  </li>-->
                  @endif
                  @if($isletme->uyelik_turu > 2)
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <li>
                  
                     @if($pageindex==60)
                     <a href="/isletmeyonetim/e_asistan{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/e_asistan{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon bi bi-card-checklist"></span
                        ><span class="mtext">Asistanım</span>
                     </a>
                  </li>
                  @endif
                  @endif
                   @if(($isletme->santral_aktif))
                  <li>
                     @if($pageindex==43)
                     <a href="/isletmeyonetim/santral{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/santral{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-phone"></span>
                     <span class="mtext"> Santral </span>
                     </a>
                  </li>
                 
                  @endif
                  <li>
                     @if($pageindex==2)
                     <a href="/isletmeyonetim/randevular{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/randevular{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-calendar4-week"></span
                        ><span class="mtext">Randevu Takvimi</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==12)
                     <a href="/isletmeyonetim/ongorusmeler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active"> 
                     @else
                     <a href="/isletmeyonetim/ongorusmeler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-chat-left-text"></span
                        ><span class="mtext"> Ön Görüşmeler</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==3)
                     <a href="/isletmeyonetim/randevular-liste{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/randevular-liste{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon bi bi-card-heading"></span
                        ><span class="mtext">Randevular</span>
                     </a>
                  </li>

                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <li>
                     @if($pageindex==4 ||$pageindex==41)
                     <a href="/isletmeyonetim/musteriler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/musteriler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-people"></span>
                     <span class="mtext"> @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışanlar @else Müşteriler @endif</span>
                     </a>
                  </li>
                  @endif
                   @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if($isletme->uyelik_turu>2)
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)

                  <li>
                     @if($pageindex==22)
                     <a href="/isletmeyonetim/kampanya_yonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/kampanya_yonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon icon-copy bi bi-cash-coin"></span
                     ><span class="mtext">Reklam Yönetimi</span>
                     </a>
                  </li>
                  <li style="display:none;">
                           @if($pageindex==20)
                           <a href="/isletmeyonetim/etkinlik{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                           @else
                           <a href="/isletmeyonetim/etkinlik{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow ">
                           @endif
                           <span class="micon bi bi-text-left"></span><span class="mtext">Etkinlikler</span>
                           </a>
                  </li>
                      
                  @endif
                  @endif
                  @endif
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <li>
                     @if($pageindex==50 || $pageindex==51)
                     <a href="/isletmeyonetim/arsivyonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/arsivyonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon bi bi-file-earmark-text"></span>
                        <span class="mtext">Form Yönetimi</span>
                     </a>
                  </li>
                  @endif
                  @if($isletme->uyelik_turu>1)
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)

                  <li>
                        @if($pageindex==14)
                        <a href="/isletmeyonetim/seanstakip{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                        @else
                        <a href="/isletmeyonetim/seanstakip{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                        @endif
                        <span class="micon bi bi-card-checklist"></span>
                        <span class="mtext">Seans Takibi</span>
                        </a>
                  </li>



                  @endif
                  @endif
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  <li>
                     @if($pageindex==11 || $pageindex==111)
                     <a href="/isletmeyonetim/adisyonlar{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/adisyonlar{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-files"></span
                        ><span class="mtext">
                     Satış Takibi
                     </span>
                     </a>
                  </li>

                  @endif
                   @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                   @if(DB::table('model_has_roles')->whereIn('role_id',[4,5])->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <li>
                     @if($pageindex==400)
                     <a href="/isletmeyonetim/raporlar{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/raporlar{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                      <span class="micon bi bi-bar-chart-fill"></span
                        ><span class="mtext">
                     Satış Raporları
                     </span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==402)
                     <a href="/isletmeyonetim/primraporu{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/primraporu{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                      <span class="micon bi bi-cash-coin"></span>
                      <span class="mtext">Prim & Hak Ediş</span>
                     </a>
                  </li>
                  @endif
                  @endif
                  

                  @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')   
                  @if($isletme->uyelik_turu>1)
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  
                      <li>
                        @if($pageindex==13)
                        <a href="/isletmeyonetim/paketsatislari{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                        @else
                        <a href="/isletmeyonetim/paketsatislari{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                        @endif
                        <span class="micon bi bi-grid-3x3"></span>
                        <span class="mtext">Paket Yönetimi</span>
                        </a>
                     </li>
                 
                     <li>
                        @if($pageindex==30)
                        <a href="/isletmeyonetim/urunler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                        @else
                        <a href="/isletmeyonetim/urunler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                        @endif
                        <span class="micon bi bi-tags"></span>
                        <span class="mtext">Stok Yönetimi</span>
                        </a>
                     </li>
                  @endif
                  @endif
                  @endif
                  <li>
                     @if($pageindex==40)
                     <a href="/isletmeyonetim/ajanda{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ajanda{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-calendar4-week"></span
                        ><span class="mtext">Ajanda</span>
                     </a>
                  </li>
                      <li>
                     @if($pageindex==500)
                     <a href="/isletmeyonetim/carkifelek{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/carkifelek{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-calendar4-week"></span
                        ><span class="mtext">Çarkıfelek</span>
                     </a>
                  </li>
                      <li>
                     @if($pageindex==501)
                     <a href="/isletmeyonetim/carkkazananlar{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/carkkazananlar{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-trophy"></span
                        ><span class="mtext">Çark Kazananlar</span>
                     </a>
                  </li>
                      <li>
                     @if($pageindex==502)
                     <a href="/isletmeyonetim/puanodulleri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/puanodulleri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-star"></span
                        ><span class="mtext">Puan Ödülleri</span>
                     </a>
                  </li>
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if($isletme->uyelik_turu>2)
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  
                    <li>
                           @if($pageindex==17)
                           <a href="/isletmeyonetim/senetler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                           @else
                           <a href="/isletmeyonetim/senetler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                           @endif
                           <span class="micon bi bi-clipboard2"></span>
                           <span class="mtext">Senet Takibi</span>
                           </a>
                        </li> 
                  @endif   
                  @endif
                 
                  @if($isletme->uyelik_turu>1)
                   
                   @if(DB::table('model_has_roles')->whereIn('role_id',[4,5])->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <li> 
                     @if($pageindex==103)
                     <a href="/isletmeyonetim/kasadefteri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/kasadefteri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon dw dw-money-2"></span>
                     <span class="mtext">Kasa Raporu</span>
                     </a>
                  </li>
                  @endif
                  @endif
                   @endif
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                
                  <li> 
                     @if($pageindex==106)
                     <a href="/isletmeyonetim/toplusms{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/toplusms{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon dw dw-message"></span>
                     <span class="mtext">SMS Yönetimi</span>
                     </a>
                  </li>
                  @endif
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0  && DB::table('model_has_roles')->where('role_id',4)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
                  <li>
                     @if($pageindex==9)
                     <a href="/isletmeyonetim/ayarlar?p=temelbilgiler&{{(isset($_GET['sube'])) ? 'sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ayarlar?p=temelbilgiler&{{(isset($_GET['sube'])) ? 'sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon dw dw-settings1"></span>
                     <span class="mtext">Ayarlar</span>
                     </a>
                  </li>
                  <li>
                     @if($pageindex==65)
                     <a href="/isletmeyonetim/whatsapp{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/whatsapp{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-whatsapp" style="color:#25D366;"></span>
                     <span class="mtext">WhatsApp</span>
                     </a>
                  </li>
                  @if(Auth::guard('isletmeyonetim')->user()->email == 'webfirmam1035@gmail.com')
                  <li>
                     @if($pageindex==99)
                     <a href="/sistemyonetim/whatsapp-panel" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/sistemyonetim/whatsapp-panel" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-graph-up" style="color:#25D366;"></span>
                     <span class="mtext">WhatsApp Yönetim</span>
                     </a>
                  </li>
                  @endif
                  @endif

                  @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() > 0 )
                  <li>  
                     @if($pageindex==105)
                     <a href="/isletmeyonetim/personeldetay/{{\App\Personeller::where('salon_id',$isletme->id)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id')}}{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/personeldetay/{{\App\Personeller::where('salon_id',$isletme->id)->where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->value('id')}}{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-pie-chart"></span>
                     <span class="mtext">Raporlar</span>
                     </a>
                  </li>
                  @endif
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
               
            </div>
            <div id="hata"></div>
            <div class="footer-wrap pd-20 mb-20 card-box" style="display:none">
               {{$isletme->salon_adi}} &copy;. Her Hakkı Saklıdır. Tasarım : 
               <a href="#" target="_blank"
                  ><img src='/public/yeni_panel/vendors/images/randevumcepte.png' style="height: 30px;"></a
                  >
            </div>
         </div>
      </div>
      <!-- welcome modal end -->
      <!-- js -->
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.js?v=11.8')}}"></script>
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
      <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
      <script src="//cdn.datatables.net/plug-ins/1.13.7/sorting/absolute.js"></script>
      <script src="//cdn.datatables.net/plug-ins/1.13.7/sorting/datetime-moment.js"></script>
      @if($pageindex == 2)
      <script src="{{secure_asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/calendar-setting.js')}}"></script>
     
      @endif
       
      <!-- End Google Tag Manager (noscript) -->
      @if($pageindex==2) 
      @include('modaldialogs.randevu-detayi-kart')
      @endif
      @if($pageindex==40)
      @include('modaldialogs.ajandaekle-modal')
      @include('modaldialogs.ajandaduzenle-modal')
      @include('modaldialogs.ajandadetay-modal')
      @endif
      @if($pageindex==4)
      @include('modaldialogs.toplumusteridanisan-modal')
      @include('modaldialogs.musteri-duzenle-modal')
      @endif
      @if($pageindex==41)
      @include('modaldialogs.musteri-duzenle-modal')
      @endif
      @if($pageindex==20)
      @include('modaldialogs.etkinlik-ekle-modal')
      @include('modaldialogs.etkinlik-detay-modal')
      @endif
      @if($pageindex==22)
      @include('modaldialogs.reklam-ekle-modal')
      @include('modaldialogs.sablon-ekle-modal')
      @endif
      @if($pageindex==1 || $pageindex==22 || $pageindex==60)

      @include('modaldialogs.reklam-detay-modal')
      @endif
      @if($pageindex==17)
      @include('modaldialogs.senet-ekle-modal')
      @include('modaldialogs.senet-detay-modal')
      @include('modaldialogs.senet-odeme-modal')
      @include('modaldialogs.senet-yeni-hizmet-modal')
      @include('modaldialogs.senet-yeni-urun-modal')
      @include('modaldialogs.senet-yeni-paket-modal')
      @endif
      @if($pageindex==50)
      @include('modaldialogs.arsiv-form-ekle-modal')
      @include('modaldialogs.arsiv-form-gonder-modal')
      @endif
      @if($pageindex==43)
     
      @include('modaldialogs.arama_listesi_ekle')
       @include('modaldialogs.arama_listesi_detay')
       @include('modaldialogs.santral_not_ekle')
        @include('modaldialogs.santral-ses-kaydi-cal-modal')
      @endif
      @if($pageindex==30)
      @include('modaldialogs.urun-ekle-modal')
      @include('modaldialogs.urun-duzenle-modal')
      @endif
     
      @if($pageindex==13)
      @include('modaldialogs.paket-duzenle-modal')
      @endif
      @if($pageindex==14 || $pageindex == 41)
      @include('modaldialogs.seans-detay-modal')
      @endif
      @if($pageindex==103)
      @include('modaldialogs.masraf-duzenle-modal')
      @endif
      @if($pageindex==11111 ||$pageindex==1111 || $pageindex==41)
      @include('modaldialogs.alacaklar-detay-modal')
      @include('modaldialogs.yeni-taksitli-tahsilat-modal')
      @include('modaldialogs.senet-odeme-modal')
      @include('modaldialogs.taksit-ode-modal')
      @endif
      @if($pageindex==9)
      @include('modaldialogs.subeisletme-ekle-modal')
      @endif
      @if($pageindex==1 || $pageindex==2 || $pageindex==3 || $pageindex==14|| $pageindex==41)
      @include('modaldialogs.randevu-duzenle-modal')
      @endif
      @if($pageindex==11)
      @include('modaldialogs.senet-detay-modal')
      @include('modaldialogs.taksit-detay-modal')
      @include('modaldialogs.taksit-ode-modal')
      @endif
      @include('modaldialogs.randevu-ekle-modal')
      @include('modaldialogs.ongorusme-modal')
      @include('modaldialogs.musteri-ekle-modal')
      @include('modaldialogs.masraf-ekle-modal')
      @include('modaldialogs.santral-web-telefon-ustune-al-modal')
      @include('modaldialogs.odeme-detay-modal')
       @include('modaldialogs.paket-tahsilat-detay-modal')
      @if($pageindex==11111 || $pageindex==1111 || $pageindex==41 || $pageindex==11)
      @include('modaldialogs.satis-detay-modal')
      <div
         id="adisyon_yeni_hizmet_modal"
         class="modal  fade calendar-modal"
         >
         <div class="modal-dialog">
            <div class="modal-content">
               <form id="adisyon_hizmet_formu"  method="POST">
                  <input type="hidden" name="adisyon_id" value="{{($pageindex==1111) ? $adisyon->id : ''}}">
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <h2 class="text-blue h2 mb-10" id="adisyon_hizmet_modal_baslik">Yeni Hizmet Satışı</h2>
                     <div class="hizmetler_bolumu_adisyon">
                        <div class="row" data-value="0" style="padding:2px; background-color:#e2e2e2;">
                           <div class="col-md-7 col-6 col-sm-6 col-xs-6">
                              <div class="form-group">
                                 <label>Hizmet</label>
                                 <select name="adisyonhizmetleriyeni[]" class="form-control custom-select2 hizmet_secimi" style="width: 100%;">
                                 <option></option>
                                 </select>
                              </div>
                           </div>
                           
                           <div class="col-md-3 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Süre (dk)</label>
                                 <input type="tel" class="form-control" name="adisyonhizmetsuresi[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("sure_dk")}}'>
                              </div>
                           </div>
                           <div class="col-md-4 col-6 col-sm-6 col-xs-6">
                              <div class="form-group">
                                 <label>Fiyat ₺</label>
                                 <input type="tel" class="form-control" required name="adisyonhizmetfiyati[]" value='{{\App\SalonHizmetler::where("salon_id",$isletme->id)->value("baslangic_fiyat")}}'>
                              </div>
                           </div>
                           <div class="col-md-3 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Seans Sayısı</label>
                                 <input type="tel" class="form-control" name="hizmetseanssayisi[]" value="1">

                              </div>
                           </div>
                           
                           <div class="col-md-2 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Personel</label>
                                 <select name="adisyonhizmetpersonelleriyeni[]" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                    <option></option>
                                     <option selected value="{{\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('id')}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option>
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-2 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Cihaz</label>
                                 <select name="adisyonhizmetcihazlariyeni[]" class="form-control custom-select2 cihaz_secimi" style="width: 100%;">
                                    <option></option>
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-2 col-xs-6 col-sm-6 col-6" style="display:none">
                               <div class="form-group">
                                 <label>Oda</label>
                                 <select name="adisyonhizmetodalariyeni[]" class="form-control custom-select2 oda_secimi" style="width: 100%;">
                                    <option></option>
                                 </select>
                              </div>

                           </div>
                           <div class="col-md-1 col-xs-6 col-sm-6 col-6" style="display:none">
                               <div class="form-group">
                                 <label>Periyot(Gün)</label>
                                 <input type="tel" class="form-control" name="hizmetseansperiyodu[]" value="1">

                              </div>
                           </div>
                           <div class="col-md-2 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Randevu Tarihi</label>
                                 <input name="islemtarihiyeni[]" class="form-control" type="text" value="{{date('Y-m-d')}}" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-1 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Randevu Saati</label>
                                 <select name="islemsaatiyeni[]" class="form-control" onautocomplete="off">
                                     @for($j = strtotime(date('07:00')) ; $j < strtotime(date('23:15')); $j+=(15*60)) 
                                                 
                                          <option value="{{date('H:i',$j)}}:00 ">{{date('H:i',$j)}}</option>
                                             
                                             
                                     @endfor 
                                 </select>
                                  
                              </div>
                           </div>
                           <div class="col-md-1 col-xs-6 col-sm-6 col-6" style="display:none">
                                 
                           </div>
                           <div class="col-md-1 col-xs-6 col-sm-6 col-6">
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
                        <div class="col-md-2 col-sm-6 col-xs-6 col-6" style="display:none">
                            <div class="form-group">
                               
                                                <label>Otomatik Randevu Oluştur</label><br>
                                                <label class="switch">
                                                <input id="hizmetRandevuOlustur" name="hizmetRandevuOlustur" type="checkbox">
                                                <span class="slider"></span>
                                                </label> 
                                            
                           </div>
                        </div>
                        <div class="col-md-6 col-6 col-xs-6 col-sm-6">
                           <label style="visibility: hidden;">Kaydet</label>
                           <button type="submit"   class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                           Kaydet
                           </button>
                        </div>
                          
                        <div class="col-md-6 col-6 col-xs-6 col-sm-6">
                           <label style="visibility: hidden;">Kapat</label>
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
                     @if($pageindex==1111)
                     <input type="hidden" name="adisyon_id" value="{{$adisyon->id}}">
                     @else
 <input type="hidden" name="adisyon_id" value="">
                     @endif
                     <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Tarih</label>
                              <input type="text" required class="form-control date-picker" name="urun_satis_tarihi" value="{{date('Y-m-d')}}" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                              <select {{($pageindex==1111 ||$pageindex==11111||$pageindex==41||$pageindex==11) ? 'disabled': ''}}   name="musteri_adi_yeni_urun" id='musteri_adi_yeni_urun' class="form-control custom-select2 musteri_satis musteri_secimi" style="width:100%">
                             <option></option>
                              @if($pageindex==1111)
                                 <option selected value="{{$musteri->id}}">{{$musteri->name}}</option> 
                              @endif
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
                                 {!!$urun_drop!!}
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
                     <div class="row" style="display: none;">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Satıcı</label>
                              <select name="urun_satici" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                  <option></option>
                                  <option selected value="{{\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('id')}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option>
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
      <div
         id="paket_satisi_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" >
               <form id="paket_satisi"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Paket Satışı</h2>
                   
                     <div id="yeniPaketEkleBolumu" style="float:right">
                         
                        <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#paket-modal">Sisteme Yeni Paket Ekle</button>
                     </div>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     @if($pageindex==1111)
                     <input type="hidden" name="adisyon_id" value="{{$adisyon->id}}">
                     @else
                     <input type="hidden" name="adisyon_id" value="">
                     @endif
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
                              <label>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                              <select {{($pageindex==1111 ||$pageindex==11111||$pageindex==41||$pageindex==11) ? 'disabled': ''}} name="musteri_adi_yeni_paket" id='musteri_adi_yeni_paket' class="form-control custom-select2 musteri_satis" style="width:100%">
                              <option>
                                 
                              </option>
                              @if($pageindex==1111)
                                 <option selected value="{{$musteri->id}}">{{$musteri->name}}</option> 
                              @endif
                              </select>
                           </div>
                        </div>
                     </div>
                     <div class="paketler_bolumu">
                        <div class="row" data-value="0" style="background-color: #e2e2e2;padding:4px;margin-bottom: 10px;">
                           <div class="col-md-5 ">
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
                                 <label>Seans Sayısı</label>
                                 <input type="tel" name="paketseans[]" value="{{App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->value('miktar')}}"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Fiyat (₺)</label>
                                 <input type="tel" name="paketfiyat[]" value="{{App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->value('fiyat')}}"  class="form-control" required>
                              </div>
                           </div>
                           
                           <div class="col-md-2" style="display:none">
                              <div class="form-group">
                                 <label>Seans Başlangıç Tarihi</label>
                                 <input  name="paketbaslangictarihi[]" id="" class="form-control geriye-yonelik" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2" style="display: none;">
                              <div class="form-group">
                                 <label>Randevu Saati</label>
                                 <input  type="time" name="paketbaslangicrandevusaati[]" id="" class="form-control" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2" style="display: none;">
                              <div class="form-group">
                                 <label>Seans Aralığı (gün)</label>
                                 <input type="tel" name="seansaralikgun[]"  class="form-control" >
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                 <button type="button" name="paket_formdan_sil_yeni_ekle" disabled  data-value="0" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                           <div class="col-md-4" style="display: none;">
                              <div class="form-group">
                                   <label>İşlemi Yapacak Personel</label>
                                    <select name="paket_personel[]" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                       <option></option>
                                    </select>
                              </div>
                           </div>
                           <div class="col-md-4" style="display: none;">
                              <div class="form-group">
                                   <label>İşlemi Yapacak Cihaz</label>
                                    <select name="paket_cihaz[]" class="form-control custom-select2 cihaz_secimi" style="width: 100%;">
                                       <option></option>
                                    </select>
                              </div>
                           </div>
                           <div class="col-md-3" style="display: none;">
                              <div class="form-group">
                                   <label>İşlem Yapılacak Oda</label>
                                    <select name="paket_oda[]" class="form-control custom-select2 oda_secimi" style="width: 100%;">
                                       <option></option>
                                    </select>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <button type="button" class="btn btn-secondary btn-lg btn-block" id="bir_paket_daha_ekle">
                              Bir Paket Daha Ekle
                              </button>
                           </div>
                        </div>
                         
                     </div>
                     <div class="row" style="display: none;">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Notlar</label>
                              <textarea name="paket_satis_notlari" style="height: 100px;" class="form-control" rows="6"></textarea>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Satıcı</label>
                              <select name="paket_satici" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                 <option></option>
                                 <option selected value="{{\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('id')}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option>
                              </select>
                           </div>
                           <div class="form-group">
                               
                                                <label>Randevu Oluştur</label><br>
                                                <label class="switch">
                                                <input id="paketRandevuOlustur" name="paketRandevuOlustur" type="checkbox">
                                                <span class="slider"></span>
                                                </label> 
                                            
                           </div>
                        </div>
                        
                     </div>
                  </div>
                  <div class="modal-footer" style="display: block;">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                        </div>
                       
                         <div class="col-6 col-xs-6 col-sm-6">
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
      <script>
document.addEventListener('DOMContentLoaded', function() {
    const randevuSlider = document.getElementById('paketRandevuOlustur');
    const paketEkleButton = document.getElementById('bir_paket_daha_ekle');
    
    // Paket bölümündeki tüm seans başlangıç tarihi, randevu saati, seans aralığı ve personel/cihaz/oda seçimlerini kontrol et
    function togglePaketFields(isDisabled) {
        const paketBolumu = document.querySelector('.paketler_bolumu');
        
        // Her paket satırı için işlem yap
        paketBolumu.querySelectorAll('[data-value]').forEach(paketRow => {
            // Seans başlangıç tarihi
            const baslangicTarihi = paketRow.querySelector('input[name="paketbaslangictarihi[]"]');
            // Randevu saati
            const randevuSaati = paketRow.querySelector('input[name="paketbaslangicrandevusaati[]"]');
            // Seans aralığı
            const seansAralik = paketRow.querySelector('input[name="seansaralikgun[]"]');
            // Personel seçimi
            const personelSelect = paketRow.querySelector('select[name="paket_personel[]"]');
            // Cihaz seçimi
            const cihazSelect = paketRow.querySelector('select[name="paket_cihaz[]"]');
            // Oda seçimi
            const odaSelect = paketRow.querySelector('select[name="paket_oda[]"]');
            
            // Seans sayısı ve fiyat her zaman aktif kalacak
            const seansSayisi = paketRow.querySelector('input[name="paketseans[]"]');
            const fiyat = paketRow.querySelector('input[name="paketfiyat[]"]');
            
            // Paket adı seçimi
            const paketAdi = paketRow.querySelector('select[name="paketadi[]"]');
            
            // Randevu oluştur kapalıysa (isDisabled = true), sadece seans sayısı ve fiyat aktif
            // Randevu oluştur açıksa (isDisabled = false), tüm alanlar aktif
            
            if (baslangicTarihi) baslangicTarihi.disabled = isDisabled;
            if (randevuSaati) randevuSaati.disabled = isDisabled;
            if (seansAralik) seansAralik.disabled = isDisabled;
            if (personelSelect) personelSelect.disabled = isDisabled;
            if (cihazSelect) cihazSelect.disabled = isDisabled;
            if (odaSelect) odaSelect.disabled = isDisabled;
            
            // Seans sayısı ve fiyat her zaman aktif - gerekli oldukları için
            if (seansSayisi) seansSayisi.disabled = false;
            if (fiyat) fiyat.disabled = false;
            
            // Paket adı da her zaman aktif olmalı
            if (paketAdi) paketAdi.disabled = false;
            
            // Select2'ler için güncelleme
            if (personelSelect && $.fn.select2) {
                $(personelSelect).select2({
                    disabled: isDisabled
                });
            }
            if (cihazSelect && $.fn.select2) {
                $(cihazSelect).select2({
                    disabled: isDisabled
                });
            }
            if (odaSelect && $.fn.select2) {
                $(odaSelect).select2({
                    disabled: isDisabled
                });
            }
        });
    }
    
    // İlk yüklemede durumu kontrol et
    togglePaketFields(!randevuSlider.checked);
    
    // Slider değiştiğinde
    randevuSlider.addEventListener('change', function() {
        togglePaketFields(!this.checked);
    });
    
    // Yeni paket eklendiğinde de durumu kontrol et
    if (paketEkleButton) {
        paketEkleButton.addEventListener('click', function() {
            // Yeni paket eklendikten sonra biraz bekleyip durumu güncelle
            setTimeout(() => {
                togglePaketFields(!randevuSlider.checked);
            }, 100);
        });
    }
    
    // Form submit edilmeden önce disabled alanların değerlerini temizle (isteğe bağlı)
    document.getElementById('paket_satisi').addEventListener('submit', function(e) {
        if (!randevuSlider.checked) {
            // Randevu oluştur kapalıysa, disabled alanların değerlerini temizle
            const paketBolumu = document.querySelector('.paketler_bolumu');
            
            paketBolumu.querySelectorAll('[data-value]').forEach(paketRow => {
                const baslangicTarihi = paketRow.querySelector('input[name="paketbaslangictarihi[]"]');
                const randevuSaati = paketRow.querySelector('input[name="paketbaslangicrandevusaati[]"]');
                const seansAralik = paketRow.querySelector('input[name="seansaralikgun[]"]');
                const personelSelect = paketRow.querySelector('select[name="paket_personel[]"]');
                const cihazSelect = paketRow.querySelector('select[name="paket_cihaz[]"]');
                const odaSelect = paketRow.querySelector('select[name="paket_oda[]"]');
                
                if (baslangicTarihi && baslangicTarihi.disabled) baslangicTarihi.value = '';
                if (randevuSaati && randevuSaati.disabled) randevuSaati.value = '';
                if (seansAralik && seansAralik.disabled) seansAralik.value = '';
                if (personelSelect && personelSelect.disabled) personelSelect.value = '';
                if (cihazSelect && cihazSelect.disabled) cihazSelect.value = '';
                if (odaSelect && odaSelect.disabled) odaSelect.value = '';
            });
        }
    });
});
</script>
      @endif
       @if($pageindex==13 || $pageindex==1111 || $pageindex==11111)
      @include('modaldialogs.paket-ekle-modal')
      @endif
      @if($pageindex==19||$pageindex==9|| $pageindex==41)
      <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cropper/1.0.1/jquery-cropper.js"></script>
      <script src="/public/yeni_panel/src/plugins/fancybox/dist/jquery.fancybox.js"></script>
      @if($pageindex==41)
      <script>
         var bs_modal = $('#crop_modal_musteri');
         
         var image = document.getElementById('croppedimg');
         
         
         
         var imagewidth;
         
         var imageheight;
         
         var cropper,reader,file;
         
         
         
         
         
         $('#musteri_profil_resmi').change( function(e) {
         
             var files = e.target.files;
         
            
         
         
         
             var done = function(url) {
         
                  
         
                 image.src = url;
         
         
         
                
         
             };
         
             image.onload = function(){
         
                 imagewidth = this.width;
         
                 imageheight = this.height;
         
                 
         
                $('#crop_modal_ac_musteri').trigger('click'); 
         
                  
         
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
         
         
         
         $('#crop_modal_ac_musteri').click( function() {
         
          
         
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
         
         
         
                     $('#mevcut_musteri_profil_resmi').attr('src',base64data);
         
                     $.ajax({
         
                       type: "POST",
         
                       url: '/isletmeyonetim/musteriprofilresimyukle',
         
                       data: {profilresmi:base64data,_token: $('input[name="_token"]').val(),user_id: $('input[name="musteri_id"]').val()},
         
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
         
         
         
      </script>
      @endif
      @if($pageindex==22)
      <script type="text/javascript">
         $(document).ready(function(){
         
              $('#kampanyayonetim_tablo').DataTable({

                 autoWidth: false,
                 pageLength: 100,
                 responsive: true,

                   columns:[
                      { data: 'gorev_turu' },
                      { data: 'kampanya', className:"ortaya-yasli" },
                      { data: 'baslangic_tarihi', className:"ortaya-yasli" },
                      { data: 'bitis_tarihi', className:"ortaya-yasli" },
                      { data: 'arama_saati', className:"ortaya-yasli" },
                      { data: 'hizmet_adi' },
                      { data: 'indirim_turu', className:"ortaya-yasli" },
                      { data: 'musteri_turu', className:"ortaya-yasli" },
                      { data: 'katilimci_sayisi', className:"ortaya-yasli" },
                      { data: 'islemler', className:"saga-yasli" },
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
      <script src="{{secure_asset('public/js/musteriListeSecimi.js?v=2.5')}}"></script>


      <script type="text/javascript">
         $(document).ready(function(){
                   const portfoy1 = new MusteriSecimi({
                                containerId: '#musteriListesiFiltreliSMS',
                                 
                                hepsiniSecButon: '#filtreliSMSTumMusterileriSec',
                                hepsiniKaldirButon: '#filtreliSMSTumMusterileriKaldir',
                                musteriArama: '#musteriarama_filtrelisms',
                                musteriAramaInput: 'input[name="musteriarama_filtrelisms"]',
                                ajaxUrl: '/isletmeyonetim/musteriportfoydropliste',
                                seciliMusteriSayisi : '#filtreliSMSSeciliMusteriler'
                              }); 
                        const portfoy2 = new MusteriSecimi({
                                containerId: '#musteriListesiSMS',
                                 
                                hepsiniSecButon: '#SMSTumMusterileriSec',
                                hepsiniKaldirButon: '#SMSTumMusterileriKaldir',
                                musteriArama: '#musteriarama_sms',
                                musteriAramaInput: 'input[name="musteriarama_sms"]',
                                ajaxUrl: '/isletmeyonetim/musteriportfoydropliste',
                                seciliMusteriSayisi : '#SMSSeciliMusteriler'
                              }); 
       const portfoy3 = new MusteriSecimi({
                                containerId: '#musteriListesiGrupSMS',
                                 
                                hepsiniSecButon: '#grupSMSTumMusterileriSec',
                                hepsiniKaldirButon: '#grupSMSTumMusterileriKaldir',
                                musteriArama: '#musteriarama_grupsms',
                                musteriAramaInput: 'input[name="musteriarama_grupsms"]',
                                ajaxUrl: '/isletmeyonetim/musteriportfoydropliste',
                                seciliMusteriSayisi : '#grupSMSSeciliMusteriler'
                              });


            $('#karaliste_sms_tablo').DataTable({
                 autoWidth: false,
                 responsive: true,
                 columns:[
                      { data: 'ad_soyad', className: "text-center" },
                      { data: 'telefon', className: "text-center" },
                      { data: 'eklenme_tarihi', className: "text-center" },
                      { data: 'islemler', className: "text-right" }
                 ],
                 data: <?php echo $karaliste; ?>,
                 "language" : {
                     "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                     searchPlaceholder: "Ara",
                     paginate: {
                         next: '<i class="ion-chevron-right"></i>',
                         previous: '<i class="ion-chevron-left"></i>'
                     }
                 }
            });
         
               $('#grup_sms_tablo').DataTable({
         
                 autoWidth: false,
         
                  responsive: true,
         
                   columns:[
         
                     
         
                      { data: 'grup_adi', className: "text-center",   },
         
                      { data: 'grup_katilimci_sayisi',className: "text-center", },
         
                     
         
                      { data: 'islemler',className: "text-right"  },
         
                 
         
                         
         
         
         
                       
         
                  
         
                      
         
                   ],
         
                   data: <?php echo json_encode($grup); ?>,
         
         
         
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

                     { data: 'id', orderable: false, searchable: false, 'render': function(data, type, row, meta){
                         if (!data) return '<span class="text-muted">-</span>';
                         return '<button type="button" class="btn btn-sm btn-outline-info sms-rapor-detay-btn" data-pkg-id="' + data + '"><i class="fa fa-list"></i> Detay</button>';
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

                     { data: 'id', orderable: false, searchable: false, 'render': function(data, type, row, meta){
                         if (!data) return '<span class="text-muted">-</span>';
                         return '<button type="button" class="btn btn-sm btn-outline-info sms-rapor-detay-btn" data-pkg-id="' + data + '"><i class="fa fa-list"></i> Detay</button>';
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

                     { data: 'id', orderable: false, searchable: false, 'render': function(data, type, row, meta){
                         if (!data) return '<span class="text-muted">-</span>';
                         return '<button type="button" class="btn btn-sm btn-outline-info sms-rapor-detay-btn" data-pkg-id="' + data + '"><i class="fa fa-list"></i> Detay</button>';
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

                     { data: 'id', orderable: false, searchable: false, 'render': function(data, type, row, meta){
                         if (!data) return '<span class="text-muted">-</span>';
                         return '<button type="button" class="btn btn-sm btn-outline-info sms-rapor-detay-btn" data-pkg-id="' + data + '"><i class="fa fa-list"></i> Detay</button>';
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

                     { data: 'id', orderable: false, searchable: false, 'render': function(data, type, row, meta){
                         if (!data) return '<span class="text-muted">-</span>';
                         return '<button type="button" class="btn btn-sm btn-outline-info sms-rapor-detay-btn" data-pkg-id="' + data + '"><i class="fa fa-list"></i> Detay</button>';
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
      @endif
      @if($pageindex==106 || $pageindex==43)
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
      @if($pageindex==50 || $pageindex==41)
      <script type="text/javascript">
         $(document).ready(function () {
         
             
         
            $('#arsiv_liste').DataTable().destroy();
         
            var adisyontablo = $('#arsiv_liste').DataTable({
         
                    autoWidth: false,
         
                    responsive: true,
         
                    columns:[
         
                        { data: 'musteriadi'},
         
                        { data: 'baslik'},
         
                        { data: 'tarih'},
         
                        {data:'belge_durum'},
         
                         { data: 'durum'},
         
                          { data: 'islemler'},
         
                     
         
                    ],
         
                   "order": [[ 2, "desc" ]],
         
                    data: <?php echo $arsiv; ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }
         
                    },
         
         
         
                     
         
                   
         
         
         
            });
         
            $('#arsiv_liste_onayli').DataTable().destroy();
         
            var adisyontablo = $('#arsiv_liste_onayli').DataTable({
         
                    autoWidth: false,
         
                    responsive: true,
         
                    columns:[
         
                        { data: 'musteriadi'},
         
                        { data: 'baslik'},
         
                        { data: 'tarih'},
         
                        {data:'belge_durum'},
         
                         { data: 'durum'},
         
                          { data: 'islemler'},
         
                     
         
                    ],
         
                   "order": [[ 2, "desc" ]],
         
                    data: <?php echo $arsiv_onayli; ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }
         
                    },
         
         
         
                     
         
                   
         
         
         
            });
         
            $('#arsiv_liste_beklenen').DataTable().destroy();
         
            var adisyontablo = $('#arsiv_liste_beklenen').DataTable({
         
                    autoWidth: false,
         
                    responsive: true,
         
                    columns:[
         
                        { data: 'musteriadi'},
         
                        { data: 'baslik'},
         
                        { data: 'tarih'},
         
                        {data:'belge_durum'},
         
                         { data: 'durum'},
         
                          { data: 'islemler'},
         
                     
         
                    ],
         
                   "order": [[ 2, "desc" ]],
         
                    data: <?php echo $arsiv_beklenen; ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }
         
                    },
         
         
         
                     
         
                   
         
         
         
            });
         
            $('#arsiv_liste_iptal').DataTable().destroy();
         
            var adisyontablo = $('#arsiv_liste_iptal').DataTable({
         
                    autoWidth: false,
         
                    responsive: true,
         
                    columns:[
         
                        { data: 'musteriadi'},
         
                        { data: 'baslik'},
         
                        { data: 'tarih'},
         
                        {data:'belge_durum'},
         
                         { data: 'durum'},
         
                          { data: 'islemler'},
         
                     
         
                    ],
         
                   "order": [[ 2, "desc" ]],
         
                    data: <?php echo $arsiv_iptal; ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }
         
                    },
         
         
         
                     
         
                   
         
         
         
            });
         
            $('#arsiv_liste_harici').DataTable().destroy();
         
            var adisyontablo = $('#arsiv_liste_harici').DataTable({
         
                    autoWidth: false,
         
                    responsive: true,
         
                    columns:[
         
                        { data: 'musteriadi'},
         
                        { data: 'baslik'},
         
                        { data: 'tarih'},
         
                        {data:'belge_durum'},
         
                         { data: 'durum'},
         
                          { data: 'islemler'},
         
                     
         
                    ],
         
                   "order": [[ 2, "desc" ]],
         
                    data: <?php echo $arsiv_harici; ?>,
         
         
         
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
      @if($pageindex==17)
      <script type="text/javascript">
         $(document).ready(function(){
         
            
         
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
      @if($pageindex==43 && DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0 )
      <script>
         $(document).ready(function(){
         
         
         
               $('#santral_arama_tum').DataTable({
         
                     autoWidth: false,
         
                     responsive: true,
         
                        
         
                    columns:[
         
                        {data: 'musteri' },
         
                        {data: 'telefon' },
         
                        {data: 'gorusmeyiyapan' }, 
         
                        {data: 'tarih' },
         
                        {data: 'saat'},
         
                        {data: 'durum' },
         
                        {data: 'seskaydi'},
         
                    ],
         
                     "order": [[ 3, "desc" ]],
         
                    data: <?php echo json_encode($santral_raporlari['rapor']); ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }
         
                    },
         
         
         
                     
         
                   
         
         
         
               });
         
               var santralraporgiden=$('#santral_giden_arama').DataTable({
         
                     autoWidth: false,
         
                     responsive: true,
         
                     "search": {
         
                       "search": "GİDEN"
         
                    },  
         
                    stateSave: true,
         
                    deferRender: true,   
         
                    columns:[
         
                        {data: 'musteri' },
         
                        {data: 'telefon' },
         
                        {data: 'gorusmeyiyapan' }, 
         
                        {data: 'tarih' },
         
                        {data: 'saat'},
         
                        {data: 'durum' },
         
                        {data: 'seskaydi'},
         
                    ],
         
                     "order": [[ 3, "desc" ]],
         
                    data: <?php echo json_encode($santral_raporlari['rapor']); ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }
         
                    },
         
         
         
                     
         
                   
         
         
         
               });
         
               
         
                     var santralraporgelen=$('#santral_gelen_arama').DataTable({
         
                     autoWidth: false,
         
                     responsive: true,
         
                     "search": {
         
                       "search": "GELEN"
         
                     },  
         
                    stateSave: true,
         
                    deferRender: true,   
         
                    columns:[
         
                        {data: 'musteri' },
         
                        {data: 'telefon' },
         
                        {data: 'gorusmeyiyapan' }, 
         
                        {data: 'tarih' },
         
                        {data: 'saat'},
         
                        {data: 'durum' },
         
                        {data: 'seskaydi'},
         
                    ],
         
                     
         "order": [[ 3, "desc" ]],
                    data: <?php echo json_encode($santral_raporlari['rapor']); ?>,
         
         
         
                    "language" : {
         
                        "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
         
                        searchPlaceholder: "Ara",
         
                        paginate: {
         
                            next: '<i class="ion-chevron-right"></i>',
         
                            previous: '<i class="ion-chevron-left"></i>'  
         
                        }     
         
                    },
         
         
         
                     
         
                   
         
         
         
               });
         
                
         
               var santralraporcevapsiz=$('#santral_cevapsiz_arama').DataTable({
         
                     autoWidth: false,
         
                     responsive: true,
         
                      "search": {
         
                       "search": "CEVAPSIZ"
         
                    },  
         
                    stateSave: true,
         
                    deferRender: true,
         
                    columns:[
         
                        {data: 'musteri' },
         
                        {data: 'telefon' },
         
                        {data: 'gorusmeyiyapan' }, 
         
                        {data: 'tarih' },
         
                        {data: 'saat'},
         
                        {data: 'durum' },
         
                        {data: 'seskaydi'},
         
                    ],
         "order": [[ 3, "desc" ]],
                    
         
                    data: <?php echo json_encode($santral_raporlari['rapor']); ?>,
         
         
         
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
       @if($pageindex==43 && DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 1 )
       <script type="text/javascript">
           $(document).ready(function(){
            $('#arama_listesi_tablosu_button').trigger('click');
         });

       </script>
      @endif

      @if($pageindex==70)
         <script src="{{secure_asset('public/yeni_panel/vendors/scripts/process.js')}}"></script>
         <script src="{{secure_asset('public/yeni_panel/vendors/scripts/layout-settings.js')}}"></script>
         <script src="{{secure_asset('public/yeni_panel/src/plugins/jquery-steps/jquery.steps.js')}}"></script>
         <script src="{{secure_asset('public/yeni_panel/vendors/scripts/steps-setting.js')}}"></script>
      @endif  
      <script src="{{secure_asset('public/js/seansTakibi.js?v=12.5')}}"></script>
      <script src="{{secure_asset('public/js/custom.js?v=223.0')}}"></script>
      @if($pageindex==22)
      <script src="{{secure_asset('public/js/reklamYonetimi2.js?v=9.5')}}"></script>
      <script src="{{secure_asset('public/js/musteriListeSecimi.js?v=12.0')}}"></script>
      <script src="{{secure_asset('public/js/musteriSecimiDuzenle.js?v=10.4')}}"></script>
      @endif

      @include('frontendscripts.frontend-scripts')
      @if($pageindex==111)
      <script type="text/javascript">
         $(document).ready(function () {
         
             tahsilatyenidenhesapla();
         
         })
         
         
         
      </script>
      @endif 
      @if($pageindex == 1111)
      <script type="text/javascript">
         $(document).ready(function () { 
            tahsilatyenidenhesapla();
         
         })
         
         
         
      </script>
      @endif
      <script src="{{secure_asset('public/js/try.js?v=1.1')}}"></script>
      <script type="text/javascript">
         $(document).ready(function () {
         
          if($('.try-currency').length)
         
             $('.try-currency').turkLirasi();
         
         })
         
      </script>
      <script src="{{secure_asset('public/js/accounting.js')}}"></script>
      <span id="server" style="display: none;"></span>
      
      @if(\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no')!==null)
      <audio id="ringtone" src="/public/telefon-ses/phone_incoming.mp3" class="d-none" loop></audio>
      <audio id="ringtone-local" src="/public/telefon-ses/phone_incoming.mp3" class="d-none" loop></audio>
      <audio id="ringbacktone" src="/public/telefon-ses/phone-outgoing.mp3" class="d-none" loop></audio>
      <audio id="ringbacktoneLocal" src="/public/telefon-ses/phone-outgoing.mp3" class="d-none" loop></audio>
      <audio id="dtmfTone" src="/public/telefon-ses/phone_dtmf.mp3" class="d-none"></audio>

      <audio id="ringtone-bt" src="/public/telefon-ses/phone_incoming.mp3"></audio>
      <!-- Dahili hoparlör için -->
      <audio id="ringtone-local" src="/public/telefon-ses/phone_incoming.mp3"></audio>
      <audio id="remoteAudio"class="d-none"></audio>
      <script src="{{secure_asset('public/js/santral/sip-0.21.2.min.js')}}"></script>
      @if($isletme->santral_aktif && (\App\Personeller::where('yetkili_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->value('dahili_no') !== null))
      <script src="{{secure_asset('public/js/santral/webphone.js?v=11.9')}}"></script>
      @endif
 
      <select id="audioOutputSelect" style="display: none"></select>
      <button id="telefonSesiniCal" style="display: none;" onclick="playSound()">Sesi Çal</button>
      <button id="telefonSesiniCalmayiDurdur" style="display: none;" onclick="stopSound()">Sesi Durdur</button>

      <audio id="audioElement" src="/public/telefon-ses/phone_incoming.mp3"></audio>


       <script>
    const audioElement = document.getElementById('audioElement');
    const selectElement = document.getElementById('audioOutputSelect');

    // Cihazları listele
    async function listAudioOutputDevices() {
      try {
        // Mikrofon izni olmadan bazı cihazlar gözükmeyebilir
        await navigator.mediaDevices.getUserMedia({ audio: true });
    
        const devices = await navigator.mediaDevices.enumerateDevices();
        const audioOutputs = devices.filter(device => device.kind === 'audiooutput');
    
        selectElement.innerHTML = ''; // Temizle
    
        // Varsayılan olarak hoparlör içeren cihazı seçmek için
        let defaultDeviceId = null;
        
        audioOutputs.forEach(device => {
          const option = document.createElement('option');
          option.value = device.deviceId;
          option.text = device.label || `Cihaz ${device.deviceId}`;
    
          // Hoparlör cihazını bulduğumuzda, onu varsayılan olarak seçili yapıyoruz
          if (device.label.toLowerCase().includes('speakers') || device.label.toLowerCase().includes('hoparlör')) {
            option.selected = true;  // Varsayılan olarak seçili yap
            defaultDeviceId = device.deviceId; // Varsayılan cihaz ID'sini sakla
          }
    
          selectElement.appendChild(option);
        });
    
        // Eğer hoparlör bulunmazsa ilk cihazı seçili yap
        if (!defaultDeviceId && audioOutputs.length > 0) {
          selectElement.value = audioOutputs[0].deviceId;
        }

        // Seçili cihaz değiştiğinde change olayını tetikle
        selectElement.dispatchEvent(new Event('change'));

      } catch (error) {
        console.error('Cihaz listelenemedi:', error);
      }
    }

    // Cihaz seçildiğinde uygulanacak ses çıkışı
    selectElement.addEventListener('change', async () => {
      const selectedDeviceId = selectElement.value;
      try {
        if (typeof audioElement.setSinkId !== 'undefined') {
          await audioElement.setSinkId(selectedDeviceId);
          console.log('Ses çıkışı cihazı ayarlandı:', selectedDeviceId);
        } else {
          console.warn('setSinkId desteklenmiyor.');
        }
      } catch (err) {
        console.error('Ses çıkışı ayarlanamadı:', err);
      }
    });

    // Ses çalma fonksiyonu
    function playSound() {
      audioElement.play().catch(error => {
        console.error('Ses çalınamadı:', error);
      });
    }

    // Ses durdurma fonksiyonu
    function stopSound() {
      audioElement.pause();  // Ses durduruluyor
      audioElement.currentTime = 0;  // Ses sıfırlanıyor (başlangıca alınıyor)
      console.log("Ses durduruldu.");
    }

    // Sayfa yüklendiğinde cihazları listele
    listAudioOutputDevices();
    @endif
  </script>
      <div style="display: none;">
     <?php dd($isletme); ?>
</div>

   </body>
  


</html>