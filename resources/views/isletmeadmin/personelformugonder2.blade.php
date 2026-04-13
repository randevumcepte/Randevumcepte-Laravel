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
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{secure_asset('public/css/sign/jquery.signaturepad.css')}}" />
  </head>
  <body>
    <div id="preloader">
      <div id="loaderstatus">&nbsp;</div>
    </div>
    
    <form id="arsivpersonelformgonderme2">
      {!!csrf_field()!!}

      <input id='arsiv_id' name='arsiv_id' type="hidden" value='{{$arsiv->id}}'>
      <input id='personel_id' name='personel_id' type="hidden" value='{{$personel->id}}'>
      
      @if($arsiv->cevapladi2==0)
      <div class="card-box mb-30" id="form_bolumu">

        @if($arsiv->durum===0)
        <div class="card-box text-center pd-20">
          <p style="text-align: center;">Merhaba {{$personel->personel_adi}}</p>
          <div class="alert alert-success" style="text-align:center;" role="alert">
            {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} iptal edilmiştir bu yüzden formu görüntüleyemezsiniz. Teşekkürler.
          </div>
        </div>
        @elseif($arsiv->durum===1)
        <div class="card-box text-center pd-20">
          <p style="text-align: center;">Merhaba {{$personel->personel_adi}}</p>
          <div class="alert alert-success" style="text-align:center;" role="alert">
            {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} onaylandığı için formu görüntüleyemezsiniz. Teşekkürler.
          </div>
        </div>
        @else         
        <div style="padding: 20px">
          <p style="text-align: center;">Merhaba {{$personel->personel_adi}}</p>
          <p style="text-align: center;">{{$isletme->salon_adi}} tarafından {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} için imzanızı atınız.</p>
          
          <div class="row">
            <div class="col-md-6 col-sm-12 mb-30">
              
              <!-- Toplam Ücret ve Kapora Alanları -->
              <div class="pd-20 card-box mb-10">
                <div class="form-group">
                  <label for="toplam_ucret">Toplam Ücret (₺)</label>
                  <input type="number" class="form-control" id="toplam_ucret" name="toplam_ucret" placeholder="Toplam ücreti giriniz" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                  <label for="kapora">Kapora (₺)</label>
                  <input type="number" class="form-control" id="kapora" name="kapora" placeholder="Kapora miktarını giriniz" step="0.01" min="0">
                </div>
              </div>

              <div class="pd-20 card-box mb-10"> 
                <div class="form-group">
                  <p>İMZA  <button class="btn btn-danger" id='imzasil' style="float:right;"><i class="fa fa-times"></i> Sil</button></p>
                  <canvas id="personelimza" name="personel_imza" style="background-color:#fff;border:1px solid #000;"></canvas>
                </div>
              </div>
              
              <div class="col-md-12 col-xs-12 col-sm-12 col-12">
                <button type="submit" name="formugonderpersonel" class="btn btn-primary btn-lg btn-block">Formu Kaydet</button>
              </div>
            </div>
          </div>
        </div>    
        @endif

      </div>
      @endif
    </form>
    
    <form id="cevapformupersonel" style="{{(!$arsiv->cevapladi2) ? 'display: none' : 'display: block'}}"> 
      <div class="card-box text-center pd-20">
        <div class="alert alert-success" style="text-align:center;" role="alert">
          Teşekkürler.
        </div>
      </div>
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="{{secure_asset('public/yeni_panel/vendors/scripts/core.js')}}"></script>
    <script src="{{secure_asset('public/yeni_panel/vendors/scripts/script.js?v=1.60')}}"></script>
    <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js')}}"></script>
    <script src="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweet-alert.init.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="{{secure_asset('public/js/sign/json2.min.js')}}"></script>
    <script src="{{secure_asset('public/js/custom.js?v=75.1')}}"></script>
    
    <script type="text/javascript">
      $(document).ready(function () {
        var $canvas = $('#personelimza');
        var canvas = $canvas.get(0);
        var ctx = canvas.getContext("2d");

        var signaturePad = new SignaturePad(canvas, {
          penColor: 'blue'
        });

        function onResize() {
          // 1. Eski imzayı geçici olarak kaydet
          var dataUrl = canvas.toDataURL();

          // 2. Boyutlandır (bu işlem canvas'ı sıfırlar!)
          $canvas.attr({
            width: 270,
            height: 200
          });

          // 3. İmzayı geri yükle
          var img = new Image();
          img.onload = function () {
            ctx.drawImage(img, 0, 0);
          };
          img.src = dataUrl;
        }

        // resize + orientationchange yakala
        window.addEventListener('orientationchange', onResize, false);
        window.addEventListener('resize', onResize, false);

        // Sayfa yüklendiğinde çalıştır
        onResize();

        // İmza sil butonu
        $('#imzasil').click(function (e) {
          e.preventDefault();
          signaturePad.clear();
        });

         
      });
    </script>
  </body>
</html>