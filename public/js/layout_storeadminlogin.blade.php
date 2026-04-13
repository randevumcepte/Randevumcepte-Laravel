<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
 
   

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     
     <title>İşletme Yönetim Paneli | {{\App\Salonlar::where('domain',$_SERVER['SERVER_NAME'])->value('salon_adi')}}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap"
        rel="stylesheet">
    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/fontawesome.css')}}">
    <!-- Theme css -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/login.css?v=1.1')}}">
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
        
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
         
        <script>
          window.OneSignalDeferred = window.OneSignalDeferred || [];
          OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
              appId: "5e50f84e-2cd8-4532-a765-f2cb82a22ff9",
            });
          });
        </script>
       
     
</head>
<body>
  
     @yield('content')
   
   
    <script src="{{asset('public/yeni_login/assets/js/jquery-3.5.1.min.js')}}"></script>
    <!-- theme particles script -->
    <script src="{{asset('public/yeni_login/assets/js/particles.min.js')}}"></script>
    <script src="{{asset('public/yeni_login/assets/js/app.js')}}"></script>
    <!-- Theme js-->
    <script src="{{asset('public/yeni_login/assets/js/script.js')}}"></script>

</body>
</html>