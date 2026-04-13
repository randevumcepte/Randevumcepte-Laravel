<!DOCTYPE html> 
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Erratum – Multi purpose error page template for Service, corporate, agency, Consulting, startup.">
    <meta name="keywords" content="Error page 404, page not found design, wrong url">
    <meta name="author" content="Ashishmaraviya">
    <link rel="icon" href="assets/images/favicon.png" type="image/x-icon"/>
    <link rel="shortcut icon" href="assets/images/favicon.png" type="image/x-icon"/>
    <title>Lisans Kullanım Süreniz Bitti! | Randevum Cepte</title>
    <!--Google font-->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap" rel="stylesheet">
    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/bootstrap.css')}}">
    <!-- Theme css -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/error-page.css')}}">
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_login/assets/css/error-page-responsive.css')}}">
</head>
<body>
    <!-- 01 Preloader -->
    
    <!-- Preloader end -->
    <!-- 02 Main page -->
    <section class="page-section">
        <div class="full-width-screen">
            <div class="container-fluid p-0">
            <div class="particles-bg" id="particles-js" style="background-image:none">
                <div class="content-detail">
                    

                    <h4 class="sub-title">@if($isletme->uyelik_bitis_tarihi == null || $isletme->uyelik_bitis_tarihi == '') Lisans Kullanımınız Henüz Aktif Olmadı! @else Lisans Kullanım Süreniz Bitti! @endif</h4>

                    <p class="detail-text">Sayın {{$isletme->salon_adi}}. @if($isletme->uyelik_bitis_tarihi == null || $isletme->uyelik_bitis_tarihi == '')Panel kullanımı için lisansınız henüz aktif olmamıştır. @else Panel kullanım süreniz sona ermiştir.@endif İşletmeniz için uygun olacağını düşündüğünüz paketi satın alarak paneli kullanmaya devam edebilirsiniz. İşletme id niz : {{$isletme->id}}</p> 

                    <div class="back-btn">
                        <a href="tel:05412948144" class="btn"><i class="fa fa-phone"></i> İLETİŞİME GEÇMEK İÇİN : 0541 294 81 44</a>
                    </div>
                    <a class="back-btn" href="/isletmeyonetim/cikisyap"
                        ><i class="dw dw-logout"></i> ÇIKIŞ YAPIN</a
                        >
                </div></div>
            </div>
        </div>
    </section>
    <!-- latest jquery-->
    <script src="{{secure_asset('public/yeni_login/assets/js/jquery-3.5.1.min.js')}}"></script>
    <!-- theme particles script -->
    <script src="{{secure_asset('public/yeni_login/assets/js/particles.min.js')}}"></script>
    <script src="{{secure_asset('public/yeni_login/assets/js/app.js')}}"></script>
    <!-- Theme js-->
    <script src="{{secure_asset('public/yeni_login/assets/js/script.js')}}"></script>  
</body>
</html>



