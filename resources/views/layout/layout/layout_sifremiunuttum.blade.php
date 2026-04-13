 </!DOCTYPE html>
<html>
<head>   <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     
     <title>İşletme Yönetim Paneli | {{\App\Salonlar::where('domain',$_SERVER['HTTP_HOST'])->value('salon_adi')}}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap"
        rel="stylesheet">
    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/bootstrap.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/fontawesome.css')}}">
    <!-- Theme css -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/yeni_login/assets/css/login.css')}}">

     <link
         rel="stylesheet"
         type="text/css"
         href="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
         />
    <style type="text/css">
        
        #preloader {
    display: none;
    position:fixed;
    width: 100%;
    height: 100%;
    background-color:black; /* sayfa yüklenirken gösterilen arkaplan rengimiz */
    z-index:999999999999; /* efektin arkada kalmadığından emin oluyoruz */
    opacity: 0.3;
}
 
#loaderstatus {
    
    width:200px;
    height:200px;
    position:absolute;
    left:50%;
    top:50%;
    background-image:url('/public/img/loader.gif'); /* burası yazının ilk başında bahsettiğimiz animasyonu çağırır */
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
     <div id='hata'></div>
    <script src="{{asset('public/yeni_login/assets/js/jquery-3.5.1.min.js')}}"></script>
      <script src="{{asset('/public/js/dist/inputmask.min.js')}}"></script> 
      <script src="{{asset('/public/js/dist/jquery.inputmask.min.js')}}"></script> 
      <script src="{{asset('/public/js/dist/bindings/inputmask.binding.js')}}"></script>
    <!-- theme particles script -->
    <script src="{{asset('public/yeni_login/assets/js/particles.min.js')}}"></script>
    <script src="{{asset('public/yeni_login/assets/js/app.js')}}"></script>
    <!-- Theme js-->
    <script src="{{asset('public/yeni_login/assets/js/script.js')}}"></script>
    <script src="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
    <script src="{{asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
    <script src="{{asset('public/js/custom.js?v=1.0.810')}}"></script>

</body>
</html>