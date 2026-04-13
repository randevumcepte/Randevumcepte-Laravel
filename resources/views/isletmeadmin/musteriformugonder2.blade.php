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
    <style>
      .hidden {
        display: none;
      }
      .switch {
        position: relative;
        display: inline-block;
        width: 56px;
        height: 29px;
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
        height: 18px;
        width: 18px;
        left: 4px;
        bottom: 5px;
        background-color: white;
        border-radius: 20px;
        -webkit-transition: .4s;
        transition: .4s;
      }
      input:checked + .slider {
        background-color: #5C008E;
      }
      input:focus + .slider {
        box-shadow: 0 0 1px #5C008E;
      }
      input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
      }
      .slider.round {
        border-radius: 20px;
      }
      .slider.round:before {
        border-radius: 50%;
      }
      
      /* Sözleşme Stilleri */
      .contract-container {
        width: 100%;
        max-width: 900px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        margin: 20px 0;
      }
      .contract-page {
        padding: 30px;
        min-height: auto;
        position: relative;
      }
      .contract-header {
        text-align: center;
        border-bottom: 2px solid #b76e79;
      }
      .contract-h1 {
        color: #b76e79;
        font-size: 24px;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
      }
      .contract-h2 {
        color: #b76e79;
        font-size: 16px;
        margin: 18px 0 10px;
        border-bottom: 1px solid #f0e6e6;
      }
      .contract-p {
        margin-bottom: 5px;
        text-align: justify;
      }
      .contract-ul {
        margin-left: 18px;
        margin-bottom: 10px;
      }
      .contract-li {
        margin-bottom: 5px;
        text-align: justify;
      }
      .highlight {
        font-weight: bold;
        color: #b76e79;
      }
      .signature-section {
        margin-top: 30px;
        width: 100%;
      }
      .signature-box {
        width: 48%;
        margin-bottom: 15px;
        float: left;
      }
      .stamp-area {
        position: absolute;
        bottom: 60px;
        right: 30px;
        width: 100px;
        height: 100px;
        border: 1px dashed #ccc;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #888;
        font-size: 11px;
      }
      .compact-list {
        margin-bottom: 8px;
      }
      .compact-list li {
        margin-bottom: 4px;
      }
    </style>
  </head>
  <body>
    <input type="hidden" id="formdoldurma" value="1">
    <div id="preloader">
      <div id="loaderstatus">&nbsp;</div>
    </div>
    
    <form id="arsivmusteriformgonderme2">
      {!!csrf_field()!!}
      <input id='arsiv_id' name='arsiv_id' type="hidden" value='{{$arsiv->id}}'>
      <input id='user_id' name='user_id' type="hidden" value='{{$user->id}}'>
      
      @if($arsiv->cevapladi==0)
      <div class="card-box mb-30" id="form_bolumu_musteri">
        @if($arsiv->durum===0)
        <div class="card-box text-center pd-20">
          <p style="text-align: center;">Merhaba {{$user->name}}</p>
          <div class="alert alert-success" style="text-align:center;" role="alert">
            {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} iptal edilmiştir bu yüzden formu görüntüleyemezsiniz. Teşekkürler.
          </div>
        </div>
        @elseif($arsiv->durum===1)
        <div class="card-box text-center pd-20">
          <p style="text-align: center;">Merhaba {{$user->name}}</p>
          <div class="alert alert-success" style="text-align:center;" role="alert">
            {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} onaylandığı için formu görüntüleyemezsiniz. Teşekkürler.
          </div>
        </div>
        @else        
        <div style="padding: 20px">
          <p style="text-align: center;">Merhaba {{$user->name}}</p>
          <p style="text-align: center;">{{$isletme->salon_adi}} tarafından {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} için aşağıdaki bilgileri doldurunuz.</p>
          
          <!-- SÖZLEŞME BÖLÜMÜ -->
          <div class="contract-container">
            <div class="contract-page">
              <div class="contract-header">
                <h1 class="contract-h1">GELİN BAŞI HİZMET SÖZLEŞMESİ</h1>
              </div>
              
              <section>
                <h2 class="contract-h2">1. SÖZLEŞMENİN KONUSU</h2>
                <p class="contract-p">Bu sözleşme, gelin başı, makyaj, prova ve aksesuar yerleştirme hizmetlerinin kapsamı, koşulları, bedeli ve tarafların sorumluluklarını düzenler.</p>
                
                <h2 class="contract-h2">2. HİZMETİN KAPSAMI</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Paket içeriği: Gelin saçı, makyaj, prova, aksesuar sabitleme</li>
                  <li class="contract-li">Ek hizmetler (saç boyama, protez, ek makyaj) ayrı ücretlendirilir</li>
                  <li class="contract-li">Kullanılan ürünler ve teknikler, müşterinin saç/deri yapısına göre belirlenir</li>
                  <li class="contract-li">Olası alerjik veya kişisel reaksiyonlardan Hizmet Veren sorumlu değildir</li>
                </ul>
                
                <h2 class="contract-h2">3. ÜCRET VE ÖDEME</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Toplam Ücret: {{$arsiv->toplam_ucret}}₺</li>
                  <li class="contract-li">Kapora (rezervasyon teminatı): {{$arsiv->kapora}}₺ (iade edilmez)</li>
                  <li class="contract-li">Kalan Tutar: Hizmet günü tahsil edilir</li>
                  <li class="contract-li">Ödeme Yöntemleri: Nakit / Kart / Havale</li>
                </ul>
                
                <h2 class="contract-h2">4. RANDEVU, TARİH VE SAAT</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Hizmet, taraflarca belirlenen tarihte ve saatte yapılacaktır</li>
                  <li class="contract-li">Gecikmelerde hizmet süresi kısalabilir, ek ücret talep edilebilir</li>
                  <li class="contract-li">Müşteri belirtilen saatte hazır bulunmazsa, sözleşme tek taraflı feshedilmiş sayılır</li>
                </ul>
                
                <h2 class="contract-h2">5. DEĞİŞİKLİK VE İPTAL</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Randevu değişikliği/iptali: En az 15 gün öncesine kadar yazılı bildirim</li>
                  <li class="contract-li">Daha geç iptallerde kapora iadesi yapılmaz</li>
                  <li class="contract-li">Düğün/mikalı tarihindeki değişiklik, Hizmet Veren'in uygunluk durumuna göre değerlendirilir</li>
                </ul>
                
                <h2 class="contract-h2">6. PROVA VE MEMNUNİYET</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Prova yapılmışsa, prova sonucu esas alınır</li>
                  <li class="contract-li">Uygulama günü yapılan değişiklikler ek ücret gerektirir</li>
                </ul>
                
                <h2 class="contract-h2">7. HİZMET SONRASI MEMNUNİYET</h2>
                <p class="contract-p">Hizmet sonrası memnuniyetsizlik, saç veya makyajın bozulması durumunda <span class="highlight">Hizmet Veren sorumlu değildir</span></p>
                
                <h2 class="contract-h2">8. GÖRSEL KULLANIM</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Hizmet öncesi ve sonrası fotoğraf/video çekimi tanıtım ve portföy amaçlı yapılabilir</li>
                  <li class="contract-li">Kişisel bilgiler paylaşılmaz</li>
                  <li class="contract-li">Bu görseller için ek ücret talep edilmez</li>
                </ul>
                
                <h2 class="contract-h2">9. MÜCBİR SEBEPLER</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Doğal afet, hastalık veya resmi yasaklama gibi durumlarda Hizmet Veren sorumlu değildir</li>
                  <li class="contract-li">Hizmet yeni bir tarihe ertelenir; ödemeler iade edilmez</li>
                </ul>
                
                <h2 class="contract-h2">10. SORUMLULUK REDDİ</h2>
                <ul class="contract-ul compact-list">
                  <li class="contract-li">Müşteri, sağlık ve alerji geçmişi hakkında doğru bilgi vermekle yükümlüdür</li>
                  <li class="contract-li">İşlem sonrası saç veya makyajda kişisel beğeni farkından Hizmet Veren sorumlu değildir</li>
                  <li class="contract-li">Geçici bozulmalar, renk değişiklikleri veya şekil farklılıkları iade nedeni değildir</li>
                </ul>
                
                <h2 class="contract-h2">11. YETKİLİ MAHKEME</h2>
                <p class="contract-p">İşbu sözleşmeden doğacak uyuşmazlıklarda <span class="highlight">Hizmet Veren'in bulunduğu yer mahkemeleri ve icra daireleri yetkilidir</span></p>
              </section>
              
         
              <div class="stamp-area">MÜHÜR</div>
            </div>
          </div>
          <!-- SÖZLEŞME BÖLÜMÜ SONU -->
          
          <div class="row">
            <div class="col-md-6 col-sm-12 mb-30">
              <div class="pd-20 card-box mb-10">
                <div class="row">
                  <div class="pd-20 card-box mb-10"> 
                    <div class="form-group">
                      <p>İMZA  <button class="btn btn-danger" id='imzasil' style="float:right;"><i class="fa fa-times"></i> Sil</button></p>
                      <canvas id="musteriimza" name="musteri_imza" style="background-color:#fff;border:1px solid #000;"></canvas>
                    </div>
                  </div>

                  <div class="pd-20 card-box mb-10">
                    <div class="form-group">
                      <label>Onay Kodu</label>
                      <input type="tel" required name="formdogrulama" value="" class="form-control">
                    </div>
                  </div>
                  
                  <div class="col-md-12 col-xs-12 col-sm-12 col-12">
                    <button type="submit" name="formugonder" class="btn btn-primary btn-lg btn-block">Formu Kaydet</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
      @endif
    </form>
    
    <form id="cevapformumusteri" style="{{(!$arsiv->cevapladi) ? 'display: none' : 'display: block'}}"> 
      <div class="card-box text-center pd-20">
        <div class="alert alert-success" style="text-align:center;" role="alert">
          Formu doldurduğunuz için teşekkürler.
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
        var $canvas = $('#musteriimza');
        var canvas = $canvas.get(0);
        var ctx = canvas.getContext("2d");

        var signaturePad = new SignaturePad(canvas, {
          penColor: 'blue'
        });

        function onResize() {
          var dataUrl = canvas.toDataURL();
          $canvas.attr({
            width: 270,
            height: 200
          });
          var img = new Image();
          img.onload = function () {
            ctx.drawImage(img, 0, 0);
          };
          img.src = dataUrl;
        }

        window.addEventListener('orientationchange', onResize, false);
        window.addEventListener('resize', onResize, false);
        onResize();

        $('#imzasil').click(function (e) {
          e.preventDefault();
          signaturePad.clear();
        });
      });
    </script>
    
    <div id='hata'></div>
  </body>
</html>