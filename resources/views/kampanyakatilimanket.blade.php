<!DOCTYPE html>
<html>
<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8" />
    <title>{{$title}}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/core.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/style.css?v=1.13')}}" />
</head>
<body class="login-page">
    <div id="preloader">
        <div id="loaderstatus">&nbsp;</div>
    </div>
    <input id='kampanya_id' type="hidden" value='{{$kampanya->id}}'>
    <input id='user_id' type="hidden" value='{{$user->id}}'>
    {!!csrf_field()!!}
    <div class="login-header box-shadow">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="brand-logo">
                <a href="#">
                    <img src="{{secure_asset('public/yeni_panel/vendors/images/randevumcepte.png')}}" alt="" />
                </a>
            </div>
        </div>
    </div>

    <div class="row pb-10" style="margin:20px 0 20px 0">
        <div class="col-md-12">
            <div class="card-box text-center pd-20" id="kampanya_anket_bolumu">
                @if(!$dahaoncecevapladi)
                    <p style="text-align: center;">Merhaba {{$user->name}}</p>
                    <p style="text-align: center;">{{$isletme->salon_adi}} tarafından {{$kampanya->paket_isim}} kampanyasına davet edildiniz. Cevabınız bizim için değerli. Katılımınızı aşağıdaki butonlara tıklayarak tarafımıza bildirebilirsiniz.</p>

                    <div class="row">
                        <div class="col-xs-6 col-6 col-sm-6 col-md-6">
                            <button id="btn-participate" style="font-size: 12px;" type="button" name="kampanya_katilim" data-value='1' class="btn btn-success btn-lg btn-block"><i class="fa fa-check"></i> Katılıyorum</button>
                        </div>
                        <div class="col-xs-6 col-6 col-sm-6 col-md-6">
                            <button id="btn-not-participate" style="font-size: 12px;" type="button" name="kampanya_katilim" data-value='0' class="btn btn-danger btn-lg btn-block"><i class="fa fa-times"></i> Katılmıyorum</button>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger" style="text-align:center;" role="alert">
                        {{$isletme->salon_adi}} tarafından {{$kampanya->paket_isim}} için size yönlendirmiş olduğumuz katılım anketimizi daha önce cevapladınız. İlginiz için teşekkür ederiz. 
                    </div>
                @endif
            </div>
            <div class="card-box text-center pd-20" id="kampanya_anket_bolumu_cevap" style="display: none;">
                <div class="alert alert-success" style="text-align:center;" role="alert">
                    {{$kampanya->paket_isim}} için katılım cevabınız tarafımıza ulaşmıştır. İlginiz için teşekkür ederiz. Cevabınız : <b id='kampanya_cevap'></b>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
    <script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.js?v=1.60')}}"></script>
    <script src="{{secure_asset('public/js/custom.js?v=1.0.739')}}"></script>

    <script>
        $(document).ready(function() {
            // Handle button clicks
            $('#btn-participate').click(function() {
                // Make the request or handle the logic for participation
                $('#kampanya_anket_bolumu_cevap').show();
                $('#kampanya_anket_bolumu').hide();
                
                // Optionally, set the response text here
             
            });
            
            $('#btn-not-participate').click(function() {
                // Make the request or handle the logic for not participating
                $('#kampanya_anket_bolumu_cevap').show();
                $('#kampanya_anket_bolumu').hide();
                
                // Optionally, set the response text here
     
            });
        });
    </script>
</body>
</html>
