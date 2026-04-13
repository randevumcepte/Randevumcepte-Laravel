<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
 
   

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     
     <title>İşletme Yönetim Paneli | RandevumCepte</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap"
        rel="stylesheet">
    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/fontawesome.css')}}">
    <!-- Theme css -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/login.css?v=1.5')}}">
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
        
     
     
</head>
<body>
  
     @yield('content')
   
   
    <script src="{{secure_asset('public/yeni_login/assets/js/jquery-3.5.1.min.js')}}"></script>
    <!-- theme particles script -->
    <script src="{{secure_asset('public/yeni_login/assets/js/particles.min.js')}}"></script>
    <script src="{{secure_asset('public/yeni_login/assets/js/app.js')}}"></script>
    <!-- Theme js-->
    <script src="{{secure_asset('public/yeni_login/assets/js/script.js')}}"></script>

</body>
</html>