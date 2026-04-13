<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
 
   

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     
     <title>İşletme Yönetim Paneli | {{\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi')}}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap"
        rel="stylesheet">
    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/fontawesome.css')}}">
    <!-- Theme css -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/login.css?v=1.2')}}">

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