@php
    // ── PERFORMANS: Bu layout boyunca ASAGIDA onlarca kez TEKRAR eden iki sorgu
    // (kullanicinin model_has_roles rolleri + yetkili personel kaydi) burada TEK
    // seferde cekilip degisken olarak kullanilir. Onceden her sayfa render'inda
    // ~17 model_has_roles + ~13 Personeller = ~30 ozdes DB sorgusu atiliyordu;
    // simdi 2 sorgu. (E-Asistan dahil tum admin sayfalarini hizlandirir.)
    $_layoutAuthId = Auth::guard('isletmeyonetim')->check()
        ? Auth::guard('isletmeyonetim')->user()->id : null;
    $_layoutRoller = $_layoutAuthId
        ? \DB::table('model_has_roles')
            ->where('model_id', $_layoutAuthId)
            ->where('salon_id', $isletme->id)
            ->pluck('role_id')->all()
        : [];
    $_layoutYetkiliPersonel = $_layoutAuthId
        ? \App\Personeller::where('yetkili_id', $_layoutAuthId)
            ->where('salon_id', $isletme->id)->first()
        : null;
    // Yetkili personelin dahili numarasinin santral durumu (asagida 2 kez kullanilir).
    $_layoutDahiliDurum = optional($_layoutYetkiliPersonel)->dahili_no
        ? \App\Dahililer::where('numara', $_layoutYetkiliPersonel->dahili_no)->value('durum')
        : null;
@endphp
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
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/style.css?v=22.0')}}" />
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

      {{-- ============================================================
           ÜST BAR — RESPONSIVE & MODERN TASARIM (inline, cache-bypass)
           Marka renkleri: #5C008E mor, #9D5DC8 açık mor, #d946ef fuşya
           ============================================================ --}}
      <style id="ust-bar-responsive-2606">
         /* Telefon (Dahili) ve Faturasiz Gizle butonlarini ust bardan gizle.
            Markup'ta dururlar (JS hooklari korunsun diye) ama gorunmezler. */
         #webTelefonDropDown, #webtelefon,
         #faturasizGizleTopbarBtn { display: none !important; }
         .header-right > .user-notification:has(#webTelefonDropDown),
         .header-right > .header-right:has(#faturasizGizleTopbarBtn) { display: none !important; }

         /* Header-left'teki arama/sube toggle ikonlarini komple gizle (kullanilmiyor) */
         .header-left .search-toggle-icon { display: none !important; }

         .header { align-items: center !important; padding-right: 16px; }
         .header-right {
            display: flex !important; flex-wrap: nowrap; align-items: center;
            gap: 6px; padding-right: 12px !important; overflow: visible;
            justify-content: flex-end;
         }
         .header-right .user-notification {
            float: none !important; margin-right: 0 !important;
            padding: 0 !important; display: flex; align-items: center;
         }
         .header-right .user-notification > .dropdown,
         .header-right .user-notification > a { display: flex; align-items: center; }

         /* Pill butonlar: SMS sayacı, WhatsApp, Telefon, İndirim Kullan */
         .header-right .kalansms,
         .header-right #whatsappDurumKutu > a.btn,
         .header-right #webtelefon,
         .header-right #indirimKullan {
            height: 38px !important; padding: 0 12px !important;
            border-radius: 999px !important; display: inline-flex !important;
            align-items: center !important; gap: 6px !important;
            line-height: 1 !important; font-size: 13px !important;
            font-weight: 600 !important; white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0,0,0,.08); border: none !important;
        }
         .header-right .kalansms i,
         .header-right #whatsappDurumKutu > a.btn i,
         .header-right #webtelefon i { font-size: 16px !important; }

         /* Bildirim / Ayar / Plus → yumuşak mor yuvarlak ikon butonlar */
         .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:not(.kalansms):not(.btn-warning):not(.btn-success):not(.btn-primary) {
            width: 40px !important; height: 40px !important;
            border-radius: 50% !important; background: #f5eefe !important;
            color: #5C008E !important; display: inline-flex !important;
            align-items: center !important; justify-content: center !important;
            padding: 0 !important; font-size: 18px !important;
            transition: background .15s ease;
         }
         .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:not(.kalansms):not(.btn-warning):not(.btn-success):not(.btn-primary):hover {
            background: #ead4ff !important;
         }

         /* Bildirim badge'i marka fuşyası */
         .header-right .user-notification .dropdown-toggle .badge,
         .header-right .user-notification .dropdown-toggle span.badge {
            position: absolute !important; top: -3px !important; right: -3px !important;
            background: #d946ef !important; color: #fff !important; font-weight: 700 !important;
            box-shadow: 0 0 0 2px #fff !important; padding: 0 5px !important;
            height: 18px !important; min-width: 18px !important;
            border-radius: 999px !important; font-size: 11px !important; line-height: 18px !important;
         }

         /* ============================================================
            MODERN BILDIRIM DROPDOWN — kart, başlık, item, sil butonu
            ============================================================ */
         .user-notification .dropdown-menu.rc-notif-dropdown {
            width: 380px !important; max-width: 95vw !important;
            padding: 0 !important; margin-top: 10px !important;
            border: 1px solid #ead4ff !important;
            border-radius: 14px !important;
            box-shadow: 0 18px 48px rgba(92, 0, 142, 0.20), 0 4px 14px rgba(92, 0, 142, 0.08) !important;
            overflow: hidden !important;
            background: #fff !important;
            /* Popper.js'in transform/position calculation'ini ezerek dropdown'u
               viewport'un sag kenarina sabitle */
            position: fixed !important;
            top: 60px !important;
            right: 12px !important;
            left: auto !important;
            bottom: auto !important;
            transform: none !important;
         }
         @media (max-width: 480px) {
            .user-notification .dropdown-menu.rc-notif-dropdown {
               right: 6px !important; top: 56px !important;
            }
         }
         .rc-notif-card { display: flex; flex-direction: column; }
         .rc-notif-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 13px 14px;
            background: linear-gradient(135deg, #5C008E 0%, #9D5DC8 100%);
            color: #fff;
         }
         .rc-notif-title { display: flex; align-items: center; gap: 8px; min-width: 0; }
         .rc-notif-title-text {
            font-size: 15px; font-weight: 700; color: #fff;
            font-family: Inter, sans-serif; letter-spacing: .2px;
         }
         .rc-notif-count {
            background: rgba(255, 255, 255, .22); color: #fff;
            font-size: 11px; font-weight: 700;
            padding: 2px 9px; border-radius: 999px;
            white-space: nowrap;
         }
         .rc-notif-actions { display: flex; align-items: center; gap: 6px; }
         .rc-notif-clear {
            background: rgba(255, 255, 255, .16); color: #fff; border: 0;
            font-size: 11.5px; font-weight: 600;
            padding: 6px 11px; border-radius: 8px;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background .15s ease, transform .1s ease;
            font-family: Inter, sans-serif;
         }
         .rc-notif-clear:hover { background: rgba(255, 255, 255, .32); color: #fff; }
         .rc-notif-clear:active { transform: scale(.97); }
         .rc-notif-clear i { font-size: 12px; }

         .rc-notif-list {
            max-height: 65vh; min-height: 120px;
            overflow-y: auto; padding: 6px 6px 8px;
            background: #fafafd;
         }
         .rc-notif-list::-webkit-scrollbar { width: 6px; }
         .rc-notif-list::-webkit-scrollbar-thumb { background: #d4b5f0; border-radius: 999px; }
         .rc-notif-list::-webkit-scrollbar-thumb:hover { background: #9D5DC8; }
         .rc-notif-list::-webkit-scrollbar-track { background: transparent; }

         /* mCustomScrollbar tema override: arka plan beyaz olsun */
         .rc-notif-list.mCustomScrollbar { background: #fafafd !important; }

         .rc-notif-item {
            position: relative; background: #fff;
            border-radius: 12px; margin: 6px 4px;
            transition: box-shadow .18s ease, transform .15s ease, border-color .18s ease;
            border: 1px solid #f0eaf7;
         }
         .rc-notif-item:hover {
            box-shadow: 0 6px 18px rgba(92, 0, 142, .10);
            border-color: #ead4ff;
            transform: translateY(-1px);
         }
         .rc-notif-item.is-unread {
            background: linear-gradient(135deg, #faf3ff 0%, #fff 100%);
            border-color: #ead4ff;
         }

         .rc-notif-link {
            display: flex; gap: 11px; padding: 12px 38px 12px 12px;
            text-decoration: none !important; color: inherit !important;
            min-height: 0 !important;
            border-radius: 12px !important;
         }
         .rc-notif-link:hover, .rc-notif-link:focus { text-decoration: none !important; color: inherit !important; }

         .rc-notif-avatar { position: relative; flex-shrink: 0; }
         .rc-notif-avatar img {
            width: 44px !important; height: 44px !important;
            border-radius: 12px !important;
            object-fit: cover !important;
            position: static !important;
            top: auto !important; left: auto !important;
            box-shadow: 0 2px 8px rgba(92, 0, 142, .12) !important;
         }
         .rc-notif-dot {
            position: absolute; top: -3px; right: -3px;
            width: 12px; height: 12px;
            background: #d946ef; border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px rgba(217, 70, 239, .22);
         }

         .rc-notif-body { flex: 1; min-width: 0; }
         .rc-notif-text {
            margin: 0 0 5px !important; font-size: 13px !important;
            color: #222 !important; font-weight: 600 !important;
            line-height: 1.4 !important;
            font-family: Inter, sans-serif !important;
            word-wrap: break-word; overflow-wrap: anywhere;
            white-space: normal !important;
         }
         .rc-notif-item.is-read .rc-notif-text { color: #555 !important; font-weight: 500 !important; }
         .rc-notif-time {
            font-size: 11px; color: #9b8fb1; font-weight: 500;
            display: inline-flex; align-items: center; gap: 4px;
            font-family: Inter, sans-serif;
         }
         .rc-notif-time i { font-size: 10px; }

         .rc-notif-del {
            position: absolute; top: 50%; right: 8px; transform: translateY(-50%);
            width: 26px; height: 26px;
            background: #fff; color: #b08bd0;
            border: 1px solid #ead4ff;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; opacity: 0;
            transition: opacity .15s ease, background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
            font-size: 11px;
            padding: 0; line-height: 1;
            z-index: 2;
         }
         .rc-notif-item:hover .rc-notif-del { opacity: 1; }
         .rc-notif-del:hover {
            background: #fee2e2; border-color: #fecaca; color: #dc2626;
            transform: translateY(-50%) scale(1.08);
         }
         .rc-notif-del:active { transform: translateY(-50%) scale(.94); }

         .rc-notif-empty {
            text-align: center; padding: 44px 20px 36px;
            color: #9b8fb1;
         }
         .rc-notif-empty-icon {
            font-size: 42px; color: #d4b5f0; margin-bottom: 10px;
            line-height: 1;
         }
         .rc-notif-empty p {
            margin: 0; font-size: 13px; font-weight: 500;
            font-family: Inter, sans-serif;
         }

         /* Eski .notification-list ul li a stillerini bizim itemler için sıfırla */
         .rc-notif-list ul, .rc-notif-list ul li { list-style: none; margin: 0; padding: 0; }
         .rc-notif-list .rc-notif-link img.mCS_img_loaded {
            width: 44px !important; height: 44px !important;
            border-radius: 12px !important;
            position: static !important; top: auto !important; left: auto !important;
         }

         @media (max-width: 480px) {
            .user-notification .dropdown-menu.rc-notif-dropdown { width: 94vw !important; }
            .rc-notif-clear span { display: none; }
            .rc-notif-clear { padding: 6px 8px; }
         }

         /* Salon adı + gün kaldı: küçük mor kart, ellipsis */
         .header-left #myLabel {
            display: block !important; margin-bottom: 0 !important;
            padding: 6px 12px !important;
            background: linear-gradient(135deg, #faf3ff 0%, #f4e6ff 100%) !important;
            border: 1px solid #ead4ff !important; border-radius: 10px !important;
            font-size: 12px !important; line-height: 1.3 !important;
            color: #5C008E !important; font-weight: 600 !important;
            max-width: 260px !important; overflow: hidden !important;
            text-overflow: ellipsis !important; white-space: nowrap !important;
         }
         .header-left .form-group.mb-0 { width: auto !important; max-width: 260px !important; }

         /* Avatar dropdown */
         .user-info-dropdown { padding: 5px 4px 5px 8px !important; }
         .user-info-dropdown .dropdown-toggle .user-icon {
            width: 38px !important; height: 38px !important;
            line-height: 38px !important; font-size: 16px !important;
         }
         .user-info-dropdown .dropdown-toggle .user-name { font-size: 13px !important; }

         /* ----- ≤1400px ----- */
         @media (max-width: 1400px) {
            .header-right { gap: 4px !important; padding-right: 8px !important; }
            .header-left #myLabel { max-width: 200px !important; font-size: 11.5px !important; }
            .header-right .kalansms,
            .header-right #whatsappDurumKutu > a.btn,
            .header-right #webtelefon,
            .header-right #indirimKullan {
               height: 36px !important; padding: 0 10px !important; font-size: 12px !important;
            }
            .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:not(.kalansms):not(.btn-warning):not(.btn-success):not(.btn-primary) {
               width: 36px !important; height: 36px !important; font-size: 16px !important;
            }
         }

         /* ----- ≤1199px ----- */
         @media (max-width: 1199px) {
            .rc-salon-card .rc-salon-name { max-width: 130px !important; }
            .header-left .form-group.mb-0.rc-salon-card { max-width: 200px !important; }
            #whatsappDurumKutu > a.btn > span:not(:last-child) { display: none !important; }
            #whatsappDurumKutu > a.btn { padding: 0 10px !important; }
            #indirimKullan {
               width: 36px !important; padding: 0 !important;
               justify-content: center !important; font-size: 0 !important; position: relative !important;
            }
            #indirimKullan::after {
               content: "%" !important; font-size: 16px !important;
               font-weight: 700 !important; line-height: 1 !important; color: #fff !important;
            }
            .user-info-dropdown .dropdown-toggle .user-name { display: none !important; }
         }

         /* ----- ≤1300px: layout dosyasındaki inline kuralla uyumlu çalış ----- */
         @media (max-width: 1300px) {
            .header { height: auto !important; min-height: 50px !important; }
            .user-notification { padding: 0 !important; }
         }

         /* ============================================================
            MOCKUP MATCH — Gradient pill butonlar, shop ikonlu salon karti,
            caret'li kullanici pill'i
            ============================================================ */

         /* SMS pill: turuncu-kirmizi gradient (btn-warning sarisini override) */
         .header-right .kalansms {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%) !important;
            box-shadow: 0 1px 2px rgba(0,0,0,.06), inset 0 1px 0 rgba(255,255,255,.18) !important;
         }
         .header-right .kalansms:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
         }

         /* WhatsApp pill: yesil/kirmizi gradient (inline solid renkleri override) */
         .header-right #whatsappDurumKutu > a.btn[style*="#25D366"] {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
         }
         .header-right #whatsappDurumKutu > a.btn[style*="#DC2626"] {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
         }
         .header-right #whatsappDurumKutu > a.btn {
            box-shadow: 0 1px 2px rgba(0,0,0,.06), inset 0 1px 0 rgba(255,255,255,.18) !important;
         }
         .header-right #whatsappDurumKutu > a.btn:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
         }

         /* Plus butonu: mor gradient + golgeli (icindeki fa-plus'a gore tespit) */
         .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:has(> i.fa-plus) {
            background: linear-gradient(135deg, #5C008E 0%, #9D5DC8 100%) !important;
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(92, 0, 142, .35) !important;
         }
         .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:has(> i.fa-plus):hover {
            background: linear-gradient(135deg, #4a006e 0%, #8a4cb8 100%) !important;
            color: #fff !important;
            transform: translateY(-1px);
         }

         /* Salon kart: shop ikonu solda, isim + gun kaldi sagda */
         .rc-salon-wrap { margin-left: 12px; }
         .header-left .form-group.mb-0.rc-salon-card {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 6px 12px 6px 8px !important;
            background: linear-gradient(135deg, #faf3ff 0%, #f4e6ff 100%) !important;
            border: 1px solid #ead4ff !important;
            border-radius: 10px !important;
            max-width: 260px !important;
            width: auto !important;
            min-width: 0;
         }
         .rc-salon-card .rc-salon-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #5C008E 0%, #9D5DC8 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex: 0 0 auto;
            box-shadow: 0 2px 6px rgba(92, 0, 142, .25);
         }
         .rc-salon-card #myLabel {
            display: flex !important;
            flex-direction: column !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            line-height: 1.2 !important;
            min-width: 0;
            overflow: hidden;
         }
         .rc-salon-card .rc-salon-name {
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #2c2c3a !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
         }
         .rc-salon-card .rc-salon-meta {
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #5C008E !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            margin-top: 1px;
         }
         .rc-salon-card .rc-salon-meta i { font-size: 12px; }

         /* Kullanici pill (avatar + isim + caret) */
         .user-info-dropdown {
            padding: 0 !important;
         }
         .user-info-dropdown .dropdown-toggle {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 3px 12px 3px 3px !important;
            background: #f5eefe !important;
            border-radius: 999px !important;
            border: 1px solid transparent !important;
            transition: background .15s, border-color .15s !important;
            position: relative;
         }
         .user-info-dropdown .dropdown-toggle:hover {
            background: #ead4ff !important;
            border-color: #d6c0f0 !important;
         }
         .user-info-dropdown .dropdown-toggle .user-icon {
            width: 34px !important;
            height: 34px !important;
            line-height: 30px !important;
            border: 2px solid #fff !important;
            box-shadow: 0 0 0 1px #d6c0f0 !important;
            background: #fff !important;
            padding: 0 !important;
            overflow: hidden;
         }
         .user-info-dropdown .dropdown-toggle .user-icon img {
            width: 100% !important;
            height: 100% !important;
            border-radius: 50% !important;
            object-fit: cover;
            display: block;
         }
         .user-info-dropdown .dropdown-toggle .user-name {
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #2c2c3a !important;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
         }
         .user-info-dropdown .dropdown-toggle::after {
            content: "\f282" !important; /* bi-chevron-down */
            font-family: "bootstrap-icons" !important;
            font-size: 11px !important;
            color: #5C008E !important;
            border: none !important;
            margin: 0 !important;
            line-height: 1 !important;
            display: inline-block !important;
            vertical-align: middle !important;
            transition: transform .15s;
         }
         .user-info-dropdown .dropdown.show .dropdown-toggle::after {
            transform: rotate(180deg);
         }

         /* ----- TEK SATIR GARANTISI: header asla satira inmesin ----- */
         .header { flex-wrap: nowrap !important; }
         .header-left { flex: 1 1 0 !important; min-width: 0 !important; overflow: hidden; flex-wrap: nowrap !important; }
         .header-right { flex-wrap: nowrap !important; flex-shrink: 0 !important; }

         /* Arama select'leri daralabilsin; salon kart hicbir zaman yari kirpilmasin.
            (Aksi halde header-left overflow:hidden son elemani = salon kartini keser) */
         .header-left .header-search,
         .header-left .header-search2 {
            flex: 0 1 auto !important;
            min-width: 0 !important;
         }
         .header-left .header-search select,
         .header-left .header-search2 select { min-width: 0 !important; }
         .header-left .header-search .select2-container,
         .header-left .header-search2 .select2-container {
            width: 100% !important; max-width: 100% !important; min-width: 0 !important;
         }
         .rc-salon-wrap { flex: 0 0 auto !important; }

         /* ----- ≤1024px: salon kart kucult, search'leri gizle, header tek satir ----- */
         @media (max-width: 1024px) {
            .header { min-height: 60px !important; }
            .header-left .form-group.mb-0.rc-salon-card { max-width: 180px !important; padding: 5px 10px 5px 5px !important; }
            .rc-salon-card .rc-salon-name { max-width: 110px !important; }
            .rc-salon-card .rc-salon-icon { width: 28px; height: 28px; font-size: 13px; }
            .header-left .header-search,
            .header-left .header-search2 { display: none !important; }
         }

         /* ----- ≤900px: salon kart gizle (search butonlarina yer kalsin) ----- */
         @media (max-width: 900px) {
            .rc-salon-wrap,
            .header-left .form-group.mb-0.rc-salon-card { display: none !important; }
         }

         /* ----- ≤767px: kompakt - boyutlar kuculur, hala tek satir ----- */
         @media (max-width: 767px) {
            .header { padding: 4px 8px !important; }
            .header-right { gap: 3px !important; padding-right: 4px !important; }
            .header-right .kalansms,
            .header-right #whatsappDurumKutu > a.btn,
            .header-right #webtelefon,
            .header-right #indirimKullan {
               height: 34px !important; padding: 0 8px !important;
            }
            .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:not(.kalansms):not(.btn-warning):not(.btn-success):not(.btn-primary) {
               width: 34px !important; height: 34px !important; font-size: 15px !important;
            }
            /* SMS sayisini gizle, sadece zarf ikonu kalsin */
            .header-right .kalansms { font-size: 0 !important; padding: 0 9px !important; }
            .header-right .kalansms i { font-size: 16px !important; }
         }

         /* ----- ≤575px: en kompakt mod, sube secici de gizle ----- */
         @media (max-width: 575px) {
            .header-right { gap: 2px !important; }
            .header-right .user-notification .dropdown > a.dropdown-toggle.no-arrow:not(.kalansms):not(.btn-warning):not(.btn-success):not(.btn-primary) {
               width: 32px !important; height: 32px !important; font-size: 14px !important;
            }
            #whatsappDurumKutu > a.btn,
            #indirimKullan,
            #webtelefon { height: 32px !important; padding: 0 8px !important; }
            /* Sube home ikonu gizle (zaten search-toggle ile aciliyor) */
            .header-left .search-toggle-icon.fa-home { display: none !important; }
         }

         /* ----- ≤420px: yardimci ikonlari da gizle, tek satir korunur ----- */
         @media (max-width: 420px) {
            .header-left .search-toggle-icon { display: none !important; }
            .header-right .kalansms { display: none !important; }
            #whatsappDurumKutu { display: none !important; }
         }
      </style>

   </head>
   
   <body>
      @if(session('sysadmin_impersonation_id'))
      <div style="background: linear-gradient(90deg, #d99a1f, #d04d5e); color: #fff; padding: 10px 20px; text-align: center; font-size: 13px; font-weight: 600; position: fixed; bottom: 0; left: 0; right: 0; z-index: 99999; box-shadow: 0 -4px 20px rgba(208, 77, 94, 0.35);">
         <span>⚠ Bu hesaba <strong>sistem yöneticisi olarak</strong> giriş yaptınız. Yapacağınız tüm işlemler loglanmaktadır.</span>
         <a href="/sistemyonetim/v2/impersonation-bitir" style="color: #fff; text-decoration: underline; margin-left: 14px; font-weight: 700;">↩ Yönetim Paneline Dön</a>
      </div>
      @endif

      {{-- Sistem yonetimi duyurulari --}}
      @php
         $aktifDuyurular = collect();
         $duyuruOnizle = request()->has('duyuru_onizle'); // ?duyuru_onizle=1 → okundu/oturum dinlemeden modali goster (QA/onizleme)
         try {
            $userId = Auth::guard('isletmeyonetim')->check() ? Auth::guard('isletmeyonetim')->user()->id : null;
            $salonId = isset($isletme) ? $isletme->id : null;
            $ilId = isset($isletme) ? $isletme->il_id : null;
            if ($userId && $salonId) {
               $tumDuyurular = \App\SistemYonetim\Duyuru::where('aktif', 1)
                  ->where(function($q){
                     $q->whereNull('baslangic_tarihi')->orWhere('baslangic_tarihi', '<=', now());
                  })
                  ->where(function($q){
                     $q->whereNull('bitis_tarihi')->orWhere('bitis_tarihi', '>=', now());
                  })
                  ->orderBy('id', 'desc')
                  ->get();
               $okunanlar = \App\SistemYonetim\DuyuruOkundu::where('user_id', $userId)->pluck('duyuru_id')->toArray();
               foreach ($tumDuyurular as $d) {
                  if (!$d->salonIcinGecerli($salonId, $ilId)) continue;
                  if (!$duyuruOnizle && !$d->sticky && in_array($d->id, $okunanlar)) continue;
                  $aktifDuyurular->push($d);
               }
            }
         } catch (\Exception $e) {}
         // Tum aktif duyurular ekran ortasinda kaliteli modal olarak gosterilir (tip'e gore temali).
         $tipMeta = [
            'guncelleme' => ['emoji'=>'🚀','c1'=>'#4f46e5','c2'=>'#6366f1','ad'=>'YENİLİK'],
            'bilgi'      => ['emoji'=>'💡','c1'=>'#2563eb','c2'=>'#3b82f6','ad'=>'BİLGİ'],
            'uyari'      => ['emoji'=>'⚠️','c1'=>'#d97706','c2'=>'#f59e0b','ad'=>'UYARI'],
            'onemli'     => ['emoji'=>'🔔','c1'=>'#dc2626','c2'=>'#ef4444','ad'=>'ÖNEMLİ'],
            'bakim'      => ['emoji'=>'🛠️','c1'=>'#475569','c2'=>'#64748b','ad'=>'BAKIM'],
            'kampanya'   => ['emoji'=>'🎉','c1'=>'#059669','c2'=>'#10b981','ad'=>'KAMPANYA'],
         ];
         $basTip = $aktifDuyurular->count() ? ($aktifDuyurular->first()->tip ?: 'bilgi') : 'bilgi';
         $hm = $tipMeta[$basTip] ?? $tipMeta['bilgi'];
         $hepGuncelleme = $aktifDuyurular->count() > 0 && $aktifDuyurular->where('tip', '!=', 'guncelleme')->isEmpty();
         if ($hepGuncelleme) { $basBaslik = 'Sistemde Yenilikler Var'; $basAlt = 'Uygulamada yaptığımız son güncellemeler aşağıda.'; }
         elseif ($aktifDuyurular->count() > 1) { $basBaslik = 'Bilgilendirme'; $basAlt = 'Sizin için '.$aktifDuyurular->count().' yeni bildirim var.'; }
         else { $basBaslik = 'Bilgilendirme'; $basAlt = 'Sizin için önemli bir duyurumuz var.'; }
      @endphp

      {{-- Sistem Duyurulari — ekran ortasinda kaliteli modal (girisde 1 kez, "Anladim" deyince bir daha cikmaz) --}}
      @if($aktifDuyurular->count() > 0)
      <style>
         .rc-gun-overlay{position:fixed;inset:0;z-index:100050;display:flex;align-items:center;justify-content:center;padding:18px;background:radial-gradient(120% 120% at 50% 0%,rgba(30,41,59,.5),rgba(15,23,42,.72));backdrop-filter:blur(7px) saturate(1.15);-webkit-backdrop-filter:blur(7px) saturate(1.15);animation:rcGunFade .3s ease}
         .rc-gun-modal{position:relative;width:100%;max-width:500px;max-height:90vh;display:flex;flex-direction:column;background:#fff;border-radius:26px;box-shadow:0 40px 80px -24px rgba(15,23,42,.6),0 0 0 1px rgba(15,23,42,.04);overflow:hidden;animation:rcGunPop .5s cubic-bezier(.16,1,.3,1)}
         .rc-gun-kapat{position:absolute;top:16px;right:16px;width:36px;height:36px;border:none;border-radius:50%;background:rgba(255,255,255,.22);color:#fff;font-size:20px;line-height:36px;text-align:center;cursor:pointer;z-index:3;transition:.25s;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
         .rc-gun-kapat:hover{background:rgba(255,255,255,.42);transform:rotate(90deg)}
         .rc-gun-head{position:relative;padding:30px 26px 28px;color:#fff;overflow:hidden}
         .rc-gun-head::before{content:"";position:absolute;top:-55%;right:-12%;width:230px;height:230px;border-radius:50%;background:rgba(255,255,255,.16)}
         .rc-gun-head::after{content:"";position:absolute;bottom:-65%;left:-8%;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.10)}
         .rc-gun-head-inner{position:relative;z-index:1;display:flex;gap:16px;align-items:center}
         .rc-gun-ikon{flex:0 0 auto;width:60px;height:60px;border-radius:19px;background:rgba(255,255,255,.20);box-shadow:0 10px 24px rgba(0,0,0,.18),0 0 0 7px rgba(255,255,255,.10);display:flex;align-items:center;justify-content:center;font-size:31px;animation:rcGunFloat 3.2s ease-in-out infinite}
         .rc-gun-rozet{display:inline-flex;align-items:center;font-size:10.5px;font-weight:800;letter-spacing:1.5px;background:rgba(255,255,255,.24);padding:4px 12px;border-radius:999px;margin-bottom:9px;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
         .rc-gun-head h3{margin:0 0 4px;font-size:21px;font-weight:800;letter-spacing:-.3px;line-height:1.2;color:#fff}
         .rc-gun-head p{margin:0;font-size:13.5px;opacity:.94;line-height:1.45}
         .rc-gun-govde{padding:20px 22px 8px;overflow-y:auto}
         .rc-gun-govde::-webkit-scrollbar{width:7px}
         .rc-gun-govde::-webkit-scrollbar-thumb{background:#d4dae3;border-radius:10px}
         .rc-gun-item{position:relative;padding:15px 16px 16px 18px;margin-bottom:12px;background:#f8fafc;border:1px solid #edf1f6;border-left:4px solid transparent;border-radius:16px;transition:transform .2s,box-shadow .2s}
         .rc-gun-item:hover{transform:translateY(-2px);box-shadow:0 12px 26px -12px rgba(15,23,42,.28)}
         .rc-gun-item:last-child{margin-bottom:4px}
         .rc-gun-item-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:9px}
         .rc-gun-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:800;letter-spacing:.6px;padding:4px 11px;border-radius:999px}
         .rc-gun-item-baslik{margin:0 0 6px;font-size:16px;font-weight:800;color:#0f172a;letter-spacing:-.2px;line-height:1.35}
         .rc-gun-tarih{flex:0 0 auto;font-size:11.5px;color:#94a3b8;font-weight:600;white-space:nowrap}
         .rc-gun-item-text{font-size:13.5px;line-height:1.68;color:#475569}
         .rc-gun-cta{display:inline-flex;align-items:center;gap:6px;margin-top:13px;padding:8px 16px;border:1.5px solid;border-radius:10px;text-decoration:none;font-weight:700;font-size:13px;transition:.2s}
         .rc-gun-cta:hover{filter:brightness(.96);transform:translateX(2px)}
         .rc-gun-footer{padding:14px 22px 22px;display:flex;justify-content:flex-end;align-items:center;gap:14px}
         .rc-gun-footer-not{font-size:12px;color:#94a3b8;font-weight:600}
         .rc-gun-btn{position:relative;display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border:none;border-radius:14px;color:#fff;font-weight:800;font-size:14px;cursor:pointer;overflow:hidden;transition:transform .15s,box-shadow .2s;letter-spacing:.2px}
         .rc-gun-btn:hover{transform:translateY(-2px)}
         .rc-gun-btn:active{transform:translateY(0)}
         .rc-gun-btn::after{content:"";position:absolute;top:0;left:-120%;width:55%;height:100%;background:linear-gradient(120deg,transparent,rgba(255,255,255,.45),transparent);transition:left .55s}
         .rc-gun-btn:hover::after{left:140%}
         @keyframes rcGunFade{from{opacity:0}to{opacity:1}}
         @keyframes rcGunPop{0%{opacity:0;transform:translateY(22px) scale(.94)}100%{opacity:1;transform:none}}
         @keyframes rcGunFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
         @media(max-width:560px){.rc-gun-head{padding:26px 20px 24px}.rc-gun-govde{padding:16px 16px 6px}.rc-gun-footer{padding:12px 16px 18px;flex-direction:column-reverse;align-items:stretch}.rc-gun-btn{width:100%;justify-content:center}.rc-gun-footer-not{text-align:center}}
      </style>
      <div id="rcGuncellemeOverlay" class="rc-gun-overlay" role="dialog" aria-modal="true" aria-labelledby="rcGunBaslik">
         <div class="rc-gun-modal">
            <button type="button" class="rc-gun-kapat" onclick="rcGuncellemeKapat()" aria-label="Kapat">&times;</button>
            <div class="rc-gun-head" style="background:linear-gradient(135deg,{{ $hm['c1'] }} 0%,{{ $hm['c2'] }} 100%)">
               <div class="rc-gun-head-inner">
                  <div class="rc-gun-ikon">{{ $hm['emoji'] }}</div>
                  <div>
                     <span class="rc-gun-rozet">{{ $hepGuncelleme ? '✨ YENİ SÜRÜM' : '📢 DUYURU' }}</span>
                     <h3 id="rcGunBaslik">{{ $basBaslik }}</h3>
                     <p>{{ $basAlt }}</p>
                  </div>
               </div>
            </div>
            <div class="rc-gun-govde">
               @foreach($aktifDuyurular as $d)
                  @php $m = $tipMeta[$d->tip] ?? $tipMeta['bilgi']; @endphp
                  <div class="rc-gun-item" style="border-left-color:{{ $m['c2'] }}">
                     <div class="rc-gun-item-head">
                        <span class="rc-gun-badge" style="background:{{ $m['c1'] }}1a;color:{{ $m['c1'] }}">{{ $m['emoji'] }} {{ $m['ad'] }}</span>
                        @if($d->created_at)<span class="rc-gun-tarih">{{ \Carbon\Carbon::parse($d->created_at)->format('d.m.Y') }}</span>@endif
                     </div>
                     <h4 class="rc-gun-item-baslik">{{ $d->baslik }}</h4>
                     <div class="rc-gun-item-text">{!! nl2br(e($d->icerik)) !!}</div>
                     @if($d->cta_metin && $d->cta_link)
                        <a href="{{ $d->cta_link }}" class="rc-gun-cta" style="border-color:{{ $m['c2'] }};color:{{ $m['c1'] }}">{{ $d->cta_metin }} →</a>
                     @endif
                  </div>
               @endforeach
            </div>
            <div class="rc-gun-footer">
               @if($aktifDuyurular->count() > 1)<span class="rc-gun-footer-not">{{ $aktifDuyurular->count() }} duyuru</span>@endif
               <button type="button" class="rc-gun-btn" style="background:linear-gradient(135deg,{{ $hm['c1'] }},{{ $hm['c2'] }});box-shadow:0 10px 24px {{ $hm['c1'] }}55" onclick="rcGuncellemeKapat()">✓ Anladım, Teşekkürler</button>
            </div>
         </div>
      </div>
      <script>
      (function(){
         var overlay = document.getElementById('rcGuncellemeOverlay');
         if(!overlay) return;
         var ids = [@foreach($aktifDuyurular as $d){{ $d->id }}@if(!$loop->last),@endif @endforeach];
         var onizle = {{ $duyuruOnizle ? 'true' : 'false' }};
         var SKEY = 'rc_gun_acildi_' + ids.join('_');
         try { if(!onizle && sessionStorage.getItem(SKEY)){ overlay.parentNode && overlay.remove(); return; } } catch(e){}
         var prevOverflow = document.body.style.overflow;
         document.body.style.overflow = 'hidden';
         var kapandi = false;
         window.rcGuncellemeKapat = function(){
            if(kapandi) return; kapandi = true;
            overlay.style.transition = 'opacity .2s ease';
            overlay.style.opacity = '0';
            setTimeout(function(){ if(overlay && overlay.parentNode){ overlay.remove(); } document.body.style.overflow = prevOverflow; }, 200);
            if(onizle) return; // onizleme modunda okundu isaretleme / oturum kilidi koyma
            try { sessionStorage.setItem(SKEY, '1'); } catch(e){}
            var t = document.querySelector('meta[name=csrf-token]');
            var token = t ? t.content : '{{ csrf_token() }}';
            ids.forEach(function(id){
               fetch('/isletmeyonetim/duyuru/' + id + '/okundu', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                  body: '{}'
               }).catch(function(){});
            });
         };
         overlay.addEventListener('click', function(e){ if(e.target === overlay) rcGuncellemeKapat(); });
         document.addEventListener('keydown', function(e){ if(e.key === 'Escape' || e.keyCode === 27) rcGuncellemeKapat(); });
      })();
      </script>
      @endif

      <button style="display: none;" id="randevudetayigetir" data-toggle="modal" data-target="#randevu-duzenle-modal"></button>
      <button style="display: none;" id="ajandadetayigetir" data-toggle="modal" data-target="#ajanda_detay_modal"></button>

      {{-- Yardim & Destek floating buton --}}
      @if(Auth::guard('isletmeyonetim')->check() && !session('sysadmin_impersonation_id'))
      <style>
      #rcYardimBtn {
         position: fixed;
         bottom: 14px;
         right: 14px;
         display: none !important;
         align-items: center;
         gap: 7px;
         background: linear-gradient(135deg, #5C008E 0%, #8a5cc7 100%);
         color: #fff !important;
         padding: 9px 15px 9px 10px;
         border-radius: 999px;
         box-shadow: 0 7px 18px rgba(92, 0, 142, 0.55);
         z-index: 9000;
         text-decoration: none !important;
         font-size: 13px;
         font-weight: 700;
         letter-spacing: 0.2px;
         transition: transform 0.2s, box-shadow 0.2s;
         animation: rcYardimPulse 2.4s ease-out infinite;
         border: 2px solid rgba(255,255,255,0.22);
         text-transform: none;
         line-height: 1;
      }
      #rcYardimBtn:hover {
         transform: translateY(-2px) scale(1.05);
         box-shadow: 0 9px 22px rgba(92, 0, 142, 0.7);
         color: #fff !important;
         text-decoration: none !important;
      }
      #rcYardimBtn .rc-yardim-icon {
         width: 22px;
         height: 22px;
         border-radius: 50%;
         background: rgba(255,255,255,0.22);
         display: inline-flex;
         align-items: center;
         justify-content: center;
         flex-shrink: 0;
      }
      #rcYardimBtn .rc-yardim-icon svg {
         width: 14px;
         height: 14px;
         display: block;
      }
      @keyframes rcYardimPulse {
         0%   { box-shadow: 0 7px 18px rgba(92, 0, 142, 0.55), 0 0 0 0 rgba(217, 179, 245, 0.65); }
         70%  { box-shadow: 0 7px 18px rgba(92, 0, 142, 0.55), 0 0 0 13px rgba(217, 179, 245, 0); }
         100% { box-shadow: 0 7px 18px rgba(92, 0, 142, 0.55), 0 0 0 0 rgba(217, 179, 245, 0); }
      }
      @media (max-width: 720px) {
         #rcYardimBtn { padding: 7px 11px 7px 7px; font-size: 11px; gap: 5px; }
         #rcYardimBtn .rc-yardim-icon { width: 18px; height: 18px; }
         #rcYardimBtn .rc-yardim-icon svg { width: 11px; height: 11px; }
      }
      </style>
      <a href="/isletmeyonetim/destek" id="rcYardimBtn" title="Bir sorunun mu var? Destek ekibimize yaz">
         <span class="rc-yardim-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
               <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
         </span>
         <span>Yardım &amp; Destek</span>
      </a>
      @endif
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
      <input id='santral_dahili_no' type="hidden" value="{{optional($_layoutYetkiliPersonel)->dahili_no}}">
      <input id='santral_dahili_sifre' type="hidden" value="{{optional($_layoutYetkiliPersonel)->dahili_sifre}}">
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
            @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'musteri.detay_gor')  &&  $kalan_uyelik_suresi >= 0)
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
            <div class="rc-salon-wrap">
               <div class="form-group mb-0 rc-salon-card">
                  <span class="rc-salon-icon"><i class="bi bi-shop"></i></span>
                  <label id="myLabel">
                     <span class="rc-salon-name">{{$isletme->salon_adi}}</span>
                     <span class="rc-salon-meta"><i class="bi bi-clock-history"></i> {{$kalan_uyelik_suresi}} gün kaldı</span>
                  </label>
               </div>
            </div>
         </div>
         <div class="header-right">
            @if(!in_array(5, $_layoutRoller)  )
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
            {{-- WhatsApp durum ikonu sadece uyelik_turu == 3 --}}
            @if($isletme->uyelik_turu == 3)
            @php
               $waAktif  = (int)($isletme->whatsapp_aktif ?? 0) === 1;
               $waBagli  = $waAktif && (($isletme->whatsapp_durum ?? '') === 'connected');
               $waNumara = $isletme->whatsapp_numara ?? '';
               $waTitle  = $waBagli
                  ? ('WhatsApp Bağlı'.($waNumara ? ' ('.$waNumara.')' : ''))
                  : 'WhatsApp Bağlı Değil — tıklayın, QR ile bağlayın';
            @endphp
            <div class="user-notification" style="padding:20px 0 0 0" id="whatsappDurumKutu">
               <a href="/isletmeyonetim/whatsapp{{ isset($_GET['sube']) ? '?sube='.$isletme->id : '' }}"
                  class="btn"
                  title="{{ $waTitle }}"
                  style="background:{{ $waBagli ? '#25D366' : '#DC2626' }};color:#fff;padding:5px 9px;border:none;display:inline-flex;align-items:center;gap:6px;">
                  <i class="bi bi-whatsapp" style="font-size:16px;"></i>
                  <span style="font-size:12px;font-weight:600;">{{ $waBagli ? 'Bağlı' : 'Bağlı Değil' }}</span>
                  <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#fff;box-shadow:0 0 0 2px {{ $waBagli ? '#25D366' : '#DC2626' }};"></span>
               </a>
            </div>
            @endif
            @endif
            @if(optional($_layoutYetkiliPersonel)->dahili_no !== null)
            <div class="user-notification " style="padding:20px 0 0 0">
               <div class="dropdown" id="webTelefonDropDown">
                  <!--{{(!$_layoutDahiliDurum) ? 'dropdown' : 'modal'}}
                     {{(!$_layoutDahiliDurum) ? '' : 'data-target=#santral-ustune-al'}}
                     
                     -->
                  <span
                     id='webtelefon'
                     class="dropdown-toggle no-arrow {{(optional($_layoutYetkiliPersonel)->dahili_no !== null) ? 'btn btn-success':''}}"
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
                              @if(in_array(5, $_layoutRoller))

                              <input type="tel" style="display: none;" id="dial" class="form-control"  placeholder="{{(optional($_layoutYetkiliPersonel)->dahili_no === null) ? 'Web telefonunu kullanabilmek için lütfen ayarlardan dahili numara ataması yapınız!' : 'Çevirmek istediğiniz dahili/numara'}}"   aria-describedby="dial-input">
                              @else
                              <input type="tel" style="border-radius: 0; padding: 35px; text-align:center; border-color: #fff;" id="dial" class="form-control"  placeholder="{{(optional($_layoutYetkiliPersonel)->dahili_no === null) ? 'Web telefonunu kullanabilmek için lütfen ayarlardan dahili numara ataması yapınız!' : 'Çevirmek istediğiniz dahili/numara'}}"   aria-describedby="dial-input">
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
                     data-display="static"
                     >
                  <i class="icon-copy dw dw-notification"></i>
                  <span id="bildirim-badge" class="{{($bildirimler->where('okundu',false)->count()>0) ? 'badge notification-afctive' : ''}}">
                  @if($bildirimler->where('okundu',false)->count()>0)
                  {{$bildirimler->where('okundu',false)->count()}}
                  @endif
                  </span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right rc-notif-dropdown">
                     <div class="rc-notif-card">
                        <div class="rc-notif-head">
                           <div class="rc-notif-title">
                              <span class="rc-notif-title-text">Bildirimler</span>
                              @if($bildirimler->where('okundu',false)->count() > 0)
                                 <span class="rc-notif-count" id="bildirim-count-pill">{{ $bildirimler->where('okundu',false)->count() }} yeni</span>
                              @endif
                           </div>
                           <div class="rc-notif-actions">
                              @if($bildirimler->count() > 0)
                                 <button type="button" class="rc-notif-clear" id="bildirim-tumusil" title="Tümünü Sil">
                                    <i class="fa fa-trash-o"></i> <span>Tümünü Sil</span>
                                 </button>
                              @endif
                           </div>
                        </div>
                        <div class="rc-notif-list" id="bildirim_listesi">
                           @foreach($bildirimler->take(100) as $bildirim)
                              @php
                                 $to_time = strtotime(date('Y-m-d H:i:s'));
                                 $from_time = strtotime($bildirim->tarih_saat);
                                 $diff = round(abs($to_time - $from_time) / 60, 0) . " dakika önce";
                                 if ($diff >= 60) {
                                    $diff = round(abs($to_time - $from_time) / 3600, 0) . " saat önce";
                                    if (round(abs($to_time - $from_time) / 3600, 0) >= 24) {
                                       $diff = date('d.m.Y H:i', strtotime($bildirim->tarih_saat));
                                    }
                                 }
                              @endphp
                              <div class="rc-notif-item {{ $bildirim->okundu ? 'is-read' : 'is-unread' }}" data-bildirim-id="{{ $bildirim->id }}">
                                 <a href="{{ $bildirim->url }}" name="bildirim" data-index-number="{{ $bildirim->id }}" data-value="{{ $bildirim->randevu_id }}" class="rc-notif-link">
                                    <div class="rc-notif-avatar">
                                       @if(!empty($bildirim->img_src))
                                          <img src="{{ $bildirim->img_src }}" alt="" loading="lazy" decoding="async">
                                       @else
                                          <img src="/public/isletmeyonetim_assets/img/avatar.png" alt="" loading="lazy" decoding="async">
                                       @endif
                                       @if(!$bildirim->okundu)
                                          <span class="rc-notif-dot"></span>
                                       @endif
                                    </div>
                                    <div class="rc-notif-body">
                                       <p class="rc-notif-text">{{ $bildirim->aciklama }}</p>
                                       <span class="rc-notif-time"><i class="fa fa-clock-o"></i> {{ $diff }}</span>
                                    </div>
                                 </a>
                                 <button type="button" class="rc-notif-del" name="bildirim-sil" data-bildirim-id="{{ $bildirim->id }}" title="Sil" aria-label="Bildirimi sil">
                                    <i class="fa fa-times"></i>
                                 </button>
                              </div>
                           @endforeach
                           @if($bildirimler->count() == 0)
                              <div class="rc-notif-empty">
                                 <div class="rc-notif-empty-icon"><i class="icon-copy dw dw-notification"></i></div>
                                 <p>Bildiriminiz bulunmamaktadır</p>
                              </div>
                           @endif
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
            @if(!in_array(5, $_layoutRoller) )
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
                     <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal-view-event-add-v2"
                        ><i class="fa fa-calendar"></i> Yeni Randevu</a>
                     <a class="dropdown-item" href="#" data-toggle="modal" data-target="#ongorusme-modal" onclick="modalbaslikata('Yeni Ön Görüşme','ongorusmeformu')"
                        ><i class="fa fa-calendar"></i> Yeni Ön Görüşme</a>
                     @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'musteri.ekle_duzenle'))
                     <a  class="dropdown-item yanitli_musteri_ekleme" href="#" data-toggle="modal" data-target="#musteri-bilgi-modal"
                        ><i class="icon-copy fa fa-user-plus" aria-hidden="true"></i> Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</a
                        >
                     @endif
                     @if($isletme->uyelik_turu > 1 )
                     @if( $_SERVER["HTTP_HOST"]!="randevu.randevumcepte.com.tr")
                     <a class="dropdown-item" href="/isletmeyonetim/yenitahsilat/?sube={{$isletme->id}}" 
                        ><i class="icon-copy fa fa-shopping-cart" aria-hidden="true"></i> Yeni Satış & Tahsilat</a
                        >@endif
                     @yetki('finans.masraf_ekle')
                     <a onclick="modalbaslikata('Yeni Masraf','masraf_formu')" class="dropdown-item" href="#"  data-toggle="modal" data-target="#yeni_masraf_modal"
                        ><i class="fa fa-upload"></i> Yeni Masraf</a
                        >
                     @endyetki
                     @endif
                     @if(!in_array(5, $_layoutRoller))
                     <a class="dropdown-item" href="#" data-toggle="modal" data-target="#formugondermodal"
                        ><i class="fa fa-paper-plane"></i> Form Gönder</a>
                     @endif
                  </div>
               </div>
            </div>
            @endif
            @if(in_array(1, $_layoutRoller))
            <div class="header-right" style="display:inline-block;margin-right:12px;vertical-align:middle;">
                <a href="#" id="faturasizGizleTopbarBtn"
                   data-aktif="{{ (int)($isletme->faturasiz_gizle ?? 0) }}"
                   title="Faturasiz Gizle"
                   style="font-size:22px;cursor:pointer;color:{{ (int)($isletme->faturasiz_gizle ?? 0) === 1 ? '#f0ad4e' : '#888' }}">
                    <i class="fa fa-file-text-o"></i>
                </a>
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
                     <a class="dropdown-item" href="/isletmeyonetim/hesabim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">
                     <i class="fa fa-id-card-o"></i>
                     Hesabım & Faturalar
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
                  {{-- Ozet (yeni dashboard, hizli paralel-yukleme) --}}
                  @if(!in_array(5, $_layoutRoller))
                  <li>
                     @if(($pageindex ?? -1) === 0)
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-grid-1x2-fill"></span
                        ><span class="mtext">Özet</span>
                     </a>
                  </li>
                  @endif

                  {{-- 1) Asistanım --}}
                  @if($isletme->uyelik_turu > 2)
                  @if(!in_array(5, $_layoutRoller))
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

                  {{-- 2) Santral --}}
                  @if(($isletme->santral_aktif) && !in_array(5, $_layoutRoller))
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

                  {{-- 2b) Cagri Merkezi Dashboard (yonetici) --}}
                  @if(($isletme->santral_aktif) && !in_array(5, $_layoutRoller))
                  <li>
                     @if($pageindex==45)
                     <a href="/isletmeyonetim/arama-dashboard{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/arama-dashboard{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-headset"></span>
                     <span class="mtext"> Çağrı Merkezi </span>
                     </a>
                  </li>
                  @endif

                  {{-- 2c) Cagri Merkezi: Arama Ekrani (yonetici de gorur/test edebilir) --}}
                  @if(($isletme->santral_aktif) && !in_array(5, $_layoutRoller))
                  <li>
                     @if($pageindex==44)
                     <a href="/isletmeyonetim/arama-listelerim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/arama-listelerim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-telephone-outbound"></span>
                     <span class="mtext"> Arama Ekranı </span>
                     </a>
                  </li>
                  @endif

                  {{-- 2d) Cagri Merkezi Ayarlari (yonetici): scriptler + sonuc kategorileri --}}
                  @if(($isletme->santral_aktif) && !in_array(5, $_layoutRoller))
                  <li>
                     @if($pageindex==46)
                     <a href="/isletmeyonetim/cagri-ayarlari{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/cagri-ayarlari{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-card-list"></span>
                     <span class="mtext"> Çağrı Merkezi Ayarları </span>
                     </a>
                  </li>
                  @endif

                  {{-- 3) Randevu Takvimi --}}
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.takvim_gor'))
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
                  @endif

                  {{-- 4) Ön Görüşmeler --}}
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

                  {{-- 5) Randevular --}}
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

                  {{-- 6) Form Yönetimi --}}
                  @if(
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'form.olustur') ||
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'form.gonder')
                  )
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

                  {{-- 7) Memnuniyet Anketi (sadece uyelik_turu == 3) --}}
                  @if($isletme->uyelik_turu == 3)
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.anket_yonet'))
                  <li>
                     @if($pageindex==52 || $pageindex==53)
                     <a href="/isletmeyonetim/anket-sonuclari?sube={{$isletme->id}}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/anket-sonuclari?sube={{$isletme->id}}" class="dropdown-toggle no-arrow">
                     @endif
                        <span class="micon bi bi-chat-dots"></span>
                        <span class="mtext">Memnuniyet Anketi</span>
                     </a>
                  </li>
                  @endif
                  @endif

                  {{-- 8) Reklam Yönetimi (simdilik gizli - uzerinde calisiliyor) --}}
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if($isletme->uyelik_turu>2)
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.kampanya_yonet'))
                  <li style="display:none;">
                     @if($pageindex==22)
                     <a href="/isletmeyonetim/kampanya_yonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/kampanya_yonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow ">
                     @endif
                     <span class="micon icon-copy bi bi-cash-coin"></span
                     ><span class="mtext">Reklam Yönetimi</span>
                     </a>
                  </li>
                  @endif
                  {{-- Etkinlikler (gizli) --}}
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

                  {{-- 9) Seans Takibi --}}
                  @if($isletme->uyelik_turu>1)
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'paket.seans_takip'))
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

                  {{-- 10) Çarkıfelek (sadece uyelik_turu == 3) --}}
                  @if($isletme->uyelik_turu == 3)
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.cark_yonet'))
                  <li>
                     @if(in_array($pageindex ?? 0, [500, 501, 502]))
                     <a href="/isletmeyonetim/carkifelek{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/carkifelek{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon fa fa-life-ring" style="color:#9D5DC8"></span
                        ><span class="mtext">Çarkıfelek</span>
                     </a>
                  </li>
                  @endif
                  @endif
                  {{-- Çark Kazananlar ve Puan Ödülleri linkleri Çarkıfelek sayfasına tab olarak entegre edildi --}}

                  {{-- 11) Müşteriler/Danışanlar --}}
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'musteri.liste_gor'))
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

                  {{-- 12) Personeller --}}
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'personel.liste_gor'))
                  <li>
                     @if($pageindex==401)
                     <a href="/isletmeyonetim/personel-yonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/personel-yonetimi{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-person-lines-fill"></span>
                     <span class="mtext">Personeller</span>
                     </a>
                  </li>
                  @endif

                  {{-- 13) Paket Yönetimi --}}
                  @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
                  @if($isletme->uyelik_turu>1)
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'paket.tanim_olustur'))
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
                  @endif
                  @endif
                  @endif

                  {{-- 13.5) Isletme Raporlari (yeni — tabbed rapor dashboard) --}}
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'rapor.satis') && !in_array(4, $_layoutRoller))
                  <li>
                     @if(($pageindex ?? -1) == 600)
                     <a href="/isletmeyonetim/isletmeraporlari{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/isletmeraporlari{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-graph-up-arrow"></span><span class="mtext">İşletme Raporları</span>
                     </a>
                  </li>
                  @endif
                  @endif

                  {{-- 14) Satış Raporları --}}
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'rapor.satis') && !in_array(4, $_layoutRoller))
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
                  @endif
                  @endif

                  {{-- 15) Satış Takibi --}}
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
                  {{-- Modern Tahsilat (BETA) — simdilik gizli, ileride acilacak.
                  <li>
                     @if($pageindex==1111 || $pageindex==1112)
                     <a href="/isletmeyonetim/tahsilat-modern{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/tahsilat-modern{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-stars"></span
                        ><span class="mtext">
                     Modern Tahsilat
                     <span style="background:linear-gradient(135deg,#5C008E,#9D5DC8); color:#fff; font-size:9px; padding:1px 6px; border-radius:8px; margin-left:4px; font-weight:700;">BETA</span>
                     </span>
                     </a>
                  </li>
                  --}}
                  @endif

                  {{-- 16) Stok Yönetimi --}}
                  @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
                  @if($isletme->uyelik_turu>1)
                  @if(
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'urun.tanim_olustur') ||
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'urun.stok_giris') ||
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'urun.stok_sayim') ||
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'urun.tedarikci_yonet')
                  )
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

                  {{-- 17) Ajanda (gizli) --}}
                  <li style="display:none;">
                     @if($pageindex==40)
                     <a href="/isletmeyonetim/ajanda{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/ajanda{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-calendar4-week"></span
                        ><span class="mtext">Ajanda</span>
                     </a>
                  </li>

                  {{-- Senet Takibi (gizli) --}}
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if($isletme->uyelik_turu>2)
                  @if(false)
                  @if(
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'satis.senet_olustur') ||
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'finans.alacak_yonet')
                  )
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
                  @endif
                  @endif

                  {{-- 18) Kasa Raporu --}}
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if($isletme->uyelik_turu>1)
                  @if(
                     (
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'rapor.kasa') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'finans.kasa_giris_cikis') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'finans.masraf_gor') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'finans.masraf_ekle') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'finans.alacak_yonet')
                     )
                     && !in_array(4, $_layoutRoller)
                  )
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

                  {{-- 19) WhatsApp (sadece uyelik_turu == 3) --}}
                  @if($isletme->uyelik_turu == 3)
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if(!in_array(5, $_layoutRoller))
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
                  @endif
                  @endif
                  @endif

                  {{-- 20) SMS Yönetimi --}}
                  @if(
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.sms_gonder') ||
                     \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.toplu_sms')
                  )
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

                  {{-- 21) Ayarlar --}}
                  @if($_SERVER['HTTP_HOST']!="randevu.randevumcepte.com.tr")
                  @if(
                     (
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'ayar.salon_bilgi') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'ayar.sube_yonet') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'ayar.cihaz_oda_yonet') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.online_ayar') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'hizmet.tanim_olustur') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'hizmet.kategori_yonet') ||
                        \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'satis.indirim_uygula')
                     )
                     && !in_array(4, $_layoutRoller)
                  )
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
                  @endif
                  @endif

                  {{-- 22) Log Hareketleri: sadece Hesap Sahibi (1), Yonetici (2),
                       Supervisor (4) gorsun. Sekreter (3) ve Personel (5) gizli. --}}
                  @if(!array_intersect([3,5], $_layoutRoller))
                  <li>
                     @if($pageindex==999)
                     <a href="/isletmeyonetim/log-hareketleri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/log-hareketleri{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-clock-history"></span>
                     <span class="mtext">Log Hareketleri</span>
                     </a>
                  </li>
                  @endif

                  {{-- Personel rolu (5) icin Raporlar linki --}}
                  @if(in_array(5, $_layoutRoller) )
                  <li>
                     @if($pageindex==105)
                     <a href="/isletmeyonetim/personeldetay/{{optional($_layoutYetkiliPersonel)->id}}{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/personeldetay/{{optional($_layoutYetkiliPersonel)->id}}{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-pie-chart"></span>
                     <span class="mtext">Raporlar</span>
                     </a>
                  </li>
                  @endif

                  {{-- Cagri Merkezi: Personel rolu (5) icin Arama Listesi (santral aktif + dahili varsa) --}}
                  @if(in_array(5, $_layoutRoller) && $isletme->santral_aktif && optional($_layoutYetkiliPersonel)->dahili_no)
                  <li>
                     @if($pageindex==44)
                     <a href="/isletmeyonetim/arama-listelerim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow active">
                     @else
                     <a href="/isletmeyonetim/arama-listelerim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}" class="dropdown-toggle no-arrow">
                     @endif
                     <span class="micon bi bi-telephone-outbound"></span>
                     <span class="mtext">Arama Listesi</span>
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

      {{-- WhatsApp 2 Ay Ücretsiz — son 5 gün ödeme uyarısı popup --}}
      @php $rcWaUyari = isset($isletme) ? \App\Salonlar::whatsappPromoBilgisi($isletme) : ['promo'=>false]; @endphp
      @if(!empty($rcWaUyari['uyari']))
      <div id="waPromoUyariOverlay" class="wa-promo-overlay" data-kalan="{{ (int) $rcWaUyari['kalan_gun'] }}" data-bitis="{{ \Carbon\Carbon::parse($rcWaUyari['bitis'])->format('d.m.Y') }}">
         <div class="wa-promo-card" role="dialog" aria-modal="true" aria-labelledby="waPromoBaslik">
            <button type="button" class="wa-promo-close" aria-label="Kapat" onclick="rcWaPromoKapat()">&times;</button>
            <div class="wa-promo-ikon"><i class="fa fa-whatsapp"></i></div>
            <h3 id="waPromoBaslik" class="wa-promo-baslik">WhatsApp Ücretsiz Süreniz Bitiyor</h3>
            <p class="wa-promo-metin">
               WhatsApp hatırlatma hizmetinizin <strong>2 aylık ücretsiz</strong> kullanım süresi
               @if((int)$rcWaUyari['kalan_gun'] <= 0)
                  <strong style="color:#dc2626;">bugün</strong> doluyor.
               @else
                  <strong style="color:#dc2626;">{{ (int) $rcWaUyari['kalan_gun'] }} gün</strong> içinde ({{ \Carbon\Carbon::parse($rcWaUyari['bitis'])->format('d.m.Y') }}) doluyor.
               @endif
               Hizmetin kesintisiz devam etmesi için lütfen ödeme amacıyla bizimle iletişime geçin. Aksi halde süre dolduğunda WhatsApp hatırlatmaları otomatik olarak devre dışı kalacaktır.
            </p>
            <div class="wa-promo-iletisim">
               <i class="fa fa-phone"></i>
               <a href="tel:{{ preg_replace('/\D/','',$rcWaUyari['iletisim']) }}">0 541 294 81 44</a>
            </div>
            <div class="wa-promo-aksiyon">
               <a class="wa-promo-btn ara" href="tel:{{ preg_replace('/\D/','',$rcWaUyari['iletisim']) }}"><i class="fa fa-phone"></i> Hemen Ara</a>
               <button type="button" class="wa-promo-btn kapat" onclick="rcWaPromoKapat()">Daha Sonra</button>
            </div>
         </div>
      </div>
      <style>
         .wa-promo-overlay{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;
            background:rgba(15,23,42,.55);backdrop-filter:blur(3px);padding:16px;}
         .wa-promo-card{background:#fff;border-radius:18px;max-width:430px;width:100%;padding:30px 26px 24px;
            box-shadow:0 24px 60px rgba(15,23,42,.30);text-align:center;position:relative;
            animation:waPromoUp .28s cubic-bezier(.2,.8,.2,1);}
         @keyframes waPromoUp{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:none}}
         .wa-promo-close{position:absolute;top:12px;right:14px;border:0;background:transparent;font-size:26px;line-height:1;
            color:#94a3b8;cursor:pointer;}
         .wa-promo-close:hover{color:#475569;}
         .wa-promo-ikon{width:66px;height:66px;border-radius:50%;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;
            background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);color:#fff;font-size:32px;
            box-shadow:0 10px 24px rgba(34,197,94,.40);}
         .wa-promo-baslik{font-size:19px;font-weight:800;color:#0f172a;margin:0 0 10px;}
         .wa-promo-metin{font-size:14px;line-height:1.6;color:#475569;margin:0 0 18px;}
         .wa-promo-iletisim{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:18px;
            font-size:20px;font-weight:800;color:#16a34a;}
         .wa-promo-iletisim i{font-size:17px;}
         .wa-promo-iletisim a{color:#16a34a;text-decoration:none;}
         .wa-promo-aksiyon{display:flex;gap:10px;}
         .wa-promo-btn{flex:1;border:0;border-radius:12px;padding:12px 14px;font-size:14px;font-weight:700;cursor:pointer;
            text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px;}
         .wa-promo-btn.ara{background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);color:#fff;
            box-shadow:0 8px 18px rgba(34,197,94,.35);}
         .wa-promo-btn.kapat{background:#f1f5f9;color:#475569;}
         .wa-promo-btn.kapat:hover{background:#e2e8f0;}
      </style>
      <script>
         (function(){
            try{
               var ov = document.getElementById('waPromoUyariOverlay');
               if(!ov) return;
               // Günde bir kez göster (Daha Sonra denirse o gün tekrar açılmaz)
               var bugun = new Date().toISOString().slice(0,10);
               if(localStorage.getItem('rcWaPromoUyari') === bugun){
                  if(ov.parentNode) ov.parentNode.removeChild(ov);
                  return;
               }
               ov.style.display = 'flex';
            }catch(e){}
         })();
         function rcWaPromoKapat(){
            try{
               var bugun = new Date().toISOString().slice(0,10);
               localStorage.setItem('rcWaPromoUyari', bugun);
            }catch(e){}
            var ov = document.getElementById('waPromoUyariOverlay');
            if(ov) ov.style.display = 'none';
         }
      </script>
      @endif

      <!-- js -->
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.js?v=11.8')}}"></script>
      <!-- Sol menü scroll konumunu sayfalar arası koru (tıklayınca menü tepeye kaymasın) -->
      <script>
         (function () {
            var SEL = '.menu-block.customscroll';
            var KEY = 'rcSidebarScrollTop';
            function readPos() {
               var $m = jQuery(SEL);
               var inst = $m.length ? $m.data('mCS') : null;
               if (inst && inst.mcs && typeof inst.mcs.top !== 'undefined') {
                  return Math.abs(parseInt(inst.mcs.top)) || 0;
               }
               var c = document.querySelector(SEL + ' .mCSB_container');
               if (c) { return Math.abs(parseInt(window.getComputedStyle(c).top)) || 0; }
               return 0;
            }
            function save() {
               try { sessionStorage.setItem(KEY, readPos()); } catch (e) {}
            }
            function restore() {
               var pos = parseInt(sessionStorage.getItem(KEY));
               var $m = jQuery(SEL);
               if (pos && $m.length && $m.data('mCS')) {
                  // Animasyonsuz, anlık konumlandırma
                  $m.mCustomScrollbar('scrollTo', pos, { scrollInertia: 0, timeout: 0, callbacks: false });
               }
            }
            // Menü linkine tıklanınca ve sayfadan ayrılırken mevcut konumu kaydet
            jQuery(document).on('click', SEL + ' a[href]', save);
            jQuery(window).on('beforeunload', save);
            // DOMContentLoaded: mCustomScrollbar henüz init olmadıysa erkenden init edip
            // konuma yerleştir; böylece görseller yüklenmeden menü doğru yerde olur.
            jQuery(function () {
               var $m = jQuery(SEL);
               if ($m.length && !$m.data('mCS') && jQuery.fn.mCustomScrollbar) {
                  $m.mCustomScrollbar({
                     theme: 'dark-2',
                     scrollInertia: 300,
                     autoExpandScrollbar: true,
                     advanced: { autoExpandHorizontalScroll: true }
                  });
               }
               restore();
            });
            // window load: script.js mCustomScrollbar'ı yeniden init edip tepeye alır;
            // aynı işlem turunda (arada ekran boyaması olmadan) tekrar geri yükleriz
            jQuery(window).on('load', restore);
         })();
      </script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/jquery.dataTables.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/dataTables.bootstrap4.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/dataTables.responsive.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/responsive.bootstrap4.min.js')}}"></script>
      <!-- buttons for Export datatable — SADECE export butonu olan sayfalarda
           (prim_hakedis_panel: personelyonetimi=401, primraporu=402).
           pdfmake+vfs_fonts+buttons ~500KB; digerlerinde bosuna iniyordu. -->
      @if($pageindex==401 || $pageindex==402)
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/dataTables.buttons.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.bootstrap4.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.print.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.html5.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/buttons.flash.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/pdfmake.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/datatables/js/vfs_fonts.js')}}"></script>
      @endif
      <!-- Datatable Setting js -->
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/datatable-setting.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
      <script>
        (function(){
            var __sessionExpiredShown = false;
            function __handleSessionExpired(xhr){
                if (__sessionExpiredShown) return;
                var loginUrl = '/isletmeyonetim/girisyap';
                try {
                    if (xhr && xhr.responseJSON && xhr.responseJSON.redirect) {
                        loginUrl = xhr.responseJSON.redirect;
                    }
                } catch(e){}
                __sessionExpiredShown = true;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oturum Sonlandı',
                        text: 'Oturumunuz sonlanmıştır. Tekrar giriş yapmanız gerekmektedir.',
                        confirmButtonText: 'Giriş Yap',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(function(){
                        window.location.href = loginUrl;
                    });
                } else {
                    alert('Oturumunuz sonlanmıştır. Tekrar giriş yapmanız gerekmektedir.');
                    window.location.href = loginUrl;
                }
            }
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ajaxError(function(event, xhr){
                    if (xhr && xhr.status === 401) {
                        __handleSessionExpired(xhr);
                    }
                });
            }
        })();
      </script>
      <script src="//cdn.datatables.net/plug-ins/1.13.7/sorting/absolute.js"></script>
      <script src="//cdn.datatables.net/plug-ins/1.13.7/sorting/datetime-moment.js"></script>
      @if($pageindex == 2)
      <script src="{{secure_asset('public/yeni_panel/src/plugins/fullcalendar/fullcalendar.min.js')}}"></script>
      <script src="{{secure_asset('public/yeni_panel/vendors/scripts/calendar-setting.js')}}"></script>
     
      @endif
       
      <!-- End Google Tag Manager (noscript) -->
      @if($pageindex==2)
      @include('modaldialogs.randevu-detayi-kart')
      @include('isletmeadmin.partials.whatsapp_mesaj_modal')
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
      @include('modaldialogs.sozlesme-olustur-modal')
      @include('modaldialogs.arsiv-form-gonder-modal')
      @endif
      @if($pageindex==43)

      @include('modaldialogs.arama_listesi_ekle')
       @include('modaldialogs.arama_listesi_detay')
       @include('modaldialogs.santral_not_ekle')
        @include('modaldialogs.santral-ses-kaydi-cal-modal')
      @endif
      @if($pageindex==45)
      {{-- Cagri Merkezi paneli: 'Arama Listesi Olustur' modali buradan yonetilir --}}
      @include('modaldialogs.arama_listesi_ekle')
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
      @include('modaldialogs.randevu-ekle-modal-v2')
      @include('modaldialogs.ongorusme-modal')
      @include('modaldialogs.musteri-ekle-modal')
      @include('modaldialogs.masraf-ekle-modal')
      @include('modaldialogs.arsiv-form-ekle-modal')
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
                           <div class="col-md-5 col-6 col-sm-6 col-xs-6">
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
                                 <input type="tel" class="form-control" name="adisyonhizmetsuresi[]" value="">
                              </div>
                           </div>
                           <div class="col-md-3 col-6 col-sm-6 col-xs-6">
                              <div class="form-group">
                                 <label>Fiyat ₺</label>
                                 <input type="tel" class="form-control" required name="adisyonhizmetfiyati[]" value="" placeholder="0">
                              </div>
                           </div>
                           <div class="col-md-3 col-xs-6 col-sm-6 col-6" style="display:none">
                              <div class="form-group">
                                 <label>Seans Sayısı</label>
                                 <input type="tel" class="form-control" name="hizmetseanssayisi[]" value="1">

                              </div>
                           </div>

                           <div class="col-md-3 col-6 col-sm-6 col-xs-6">
                              <div class="form-group">
                                 <label>Personel</label>
                                 <select name="adisyonhizmetpersonelleriyeni[]" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                    <option></option>
                                     @php $defaultPersonelId = optional($_layoutYetkiliPersonel)->id; @endphp
                                     @if($defaultPersonelId)
                                     <option selected value="{{$defaultPersonelId}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option>
                                     @endif
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
      <script>
         // Hizmet modal her acilista sifirla (kalan deger kalmasin, fiyat 9000'de takili kalmasin)
         (function(){
            var bugun = '{{date("Y-m-d")}}';
            function resetHizmetEkleModal(){
               var $form = $('#adisyon_hizmet_formu');
               if(!$form.length) return;
               var $cont = $form.find('.hizmetler_bolumu_adisyon');
               $cont.find('> .row').slice(1).remove();
               var $first = $cont.find('> .row').first();
               if(!$first.length) return;
               $first.find('select[name="adisyonhizmetleriyeni[]"]').val(null).trigger('change.select2');
               $first.find('select.cihaz_secimi, select.oda_secimi').val(null).trigger('change.select2');
               $first.find('input[name="islemtarihiyeni[]"]').val(bugun);
               $first.find('input[name="adisyonhizmetsuresi[]"]').val('');
               $first.find('input[name="adisyonhizmetfiyati[]"]').val('');
               $first.find('input[name="hizmetseanssayisi[]"]').val(1);
               $first.find('input[name="hizmetseansperiyodu[]"]').val(1);
               $first.find('button[name="hizmet_formdan_sil_adisyon"]').prop('disabled',true).attr('data-value','0');
            }
            $(document).on('show.bs.modal','#adisyon_yeni_hizmet_modal', resetHizmetEkleModal);
         })();
      </script>
      <div
         id="urun_satisi_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width:850px;width:92%;">
            <div class="modal-content" style="max-height: 90%;width:100%;">
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
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Satıcı</label>
                              <select name="urun_satici" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                  <option></option>
                                  @php $defaultPersonelId = optional($_layoutYetkiliPersonel)->id; @endphp
                                  @if($defaultPersonelId)
                                  <option selected value="{{$defaultPersonelId}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option>
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
      <div
         id="paket_satisi_modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width:850px;width:92%;">
            <div class="modal-content" style="width:100%;">
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
                     <div class="row">
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
                                 @php $defaultPaketSaticiId = optional($_layoutYetkiliPersonel)->id; @endphp
                                 @if($defaultPaketSaticiId)
                                 <option selected value="{{$defaultPaketSaticiId}}">{{Auth::guard('isletmeyonetim')->user()->name}}</option>
                                 @endif
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
      @if($pageindex==43 && !in_array(5, $_layoutRoller) )
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
       @if($pageindex==43 && in_array(5, $_layoutRoller) )
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
      <script src="{{secure_asset('public/js/custom.js?v=261.0')}}"></script>
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
      
      @if(optional($_layoutYetkiliPersonel)->dahili_no!==null)
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
      @if($isletme->santral_aktif && (optional($_layoutYetkiliPersonel)->dahili_no !== null))
      <script src="{{secure_asset('public/js/santral/webphone.js?v=12.0')}}"></script>
      @endif
 
      <select id="audioOutputSelect" style="display: none"></select>
      <button id="telefonSesiniCal" style="display: none;" onclick="playSound()">Sesi Çal</button>
      <button id="telefonSesiniCalmayiDurdur" style="display: none;" onclick="stopSound()">Sesi Durdur</button>

      <audio id="audioElement" src="/public/telefon-ses/phone_incoming.mp3"></audio>

      {{-- Salon akilli hatirlatma + Cagri Merkezi arama randevusu popup'i (tum sayfalarda) --}}
      @include('isletmeadmin.partials.hatirlatma_popup')

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