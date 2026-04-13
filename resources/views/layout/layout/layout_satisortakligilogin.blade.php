<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
 
   

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     
     <title>Satış Ortaklığı Paneli | RandevumCepte</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap"
        rel="stylesheet">
    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/fontawesome.css')}}">
    <!-- Theme css -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/login.css?v=1.5')}}">
      <link rel="stylesheet" href="{{asset('public/satisortakligipanel/assets/vendor/sweetalert2/dist/sweetalert2.min.css')}}">
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
         
         <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" defer></script>
      <script>
        var OneSignal = window.OneSignal || [];
        OneSignal.push(function() {
          OneSignal.init({
            appId: "5e50f84e-2cd8-4532-a765-f2cb82a22ff9",
            autoRegister: false,
            serviceWorkerPath: "/public/js/OneSignalSDKWorker.js",
            debug: true, // Hata ayıklama için
          });

          // Abonelik durumu kontrolü
          OneSignal.isPushNotificationsEnabled(function(isEnabled) {
            if (isEnabled) {
              console.log("Bildirimler etkin.");
              OneSignal.getUserId(function(userId) {
                console.log("OneSignal User ID:", userId);
                if ($('#onesignalid').length) {
                  $('#onesignalid').val(userId);
                }
              });
            } else {
              console.log("Bildirimler etkin değil.");
              OneSignal.registerForPushNotifications();
            }
          });

          // Abonelik değişikliği
          OneSignal.on('subscriptionChange', function(isSubscribed) {
            console.log("Abonelik değişti: ", isSubscribed);
            if (isSubscribed) {
              OneSignal.getUserId(function(userId) {
                console.log("OneSignal User ID:", userId);
                if ($('#onesignalid').length) {
                  $('#onesignalid').val(userId);
                }
              });
            }
          });
        });
      </script>
      <style type="text/css">
        input{
          width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border-bottom: 2px solid #ccc;
    border-top: 0;
    border-left: 0;
    border-right: 0;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    border-radius: 5px;
    font-size: 14px;
    color: #666;
    background-color: #f8f8f8;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
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
   
      </style>
              
     
     
</head>
<body>
  <div id="preloader">
            <div id="loaderstatus">&nbsp;</div>
      </div>
     @yield('content')
      <div id="hata"></div>
   
    <script src="{{asset('public/yeni_login/assets/js/jquery-3.5.1.min.js')}}"></script>
    <!-- theme particles script -->
    <script src="{{asset('public/yeni_login/assets/js/particles.min.js')}}"></script>
    <script src="{{asset('public/yeni_login/assets/js/app.js')}}"></script>
    <!-- Theme js-->
    <script src="{{asset('public/yeni_login/assets/js/script.js')}}"></script>
     <script src="{{asset('/public/js/dist/inputmask.min.js')}}"></script> 
      <script src="{{asset('/public/js/dist/jquery.inputmask.min.js')}}"></script> 

      <script src="{{asset('/public/js/dist/bindings/inputmask.binding.js')}}"></script>
        <script src="{{asset('public/satisortakligipanel/assets/vendor/sweetalert2/dist/sweetalert2.min.js')}}"></script>
      <script src="{{asset('public/satisortakligipanel/assets/js/custom.js?v=11.8')}}"></script>

</body>
</html>