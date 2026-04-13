<!DOCTYPE html>
<html>
   <head>
      <!-- Basic Page Info -->
      <meta charset="utf-8" />
      <title>{{$title}}</title>
      <meta
         name="viewport"
         content="width=device-width, initial-scale=1, maximum-scale=1"
         />
      <!-- Google Font -->
      <link
         href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
         rel="stylesheet"
         />
      <!-- CSS -->
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/core.css')}}" />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css')}}"
         />
      <link rel="stylesheet" type="text/css" href="{{secure_asset('public/yeni_panel/vendors/styles/style.css?v=1.13')}}" />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css')}}"
         />
      <link
         rel="stylesheet"
         type="text/css"
         href="{{secure_asset('public/css/sign/jquery.signaturepad.css')}}"
         />
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
         /* Rounded sliders */
         .slider.round {
         border-radius: 20px;
         }
         .slider.round:before {
         border-radius: 50%;
         }
      </style>
   </head>
   <body>
      <input type="hidden" id="formdoldurma" value="1">
      <div id="preloader">
         <div id="loaderstatus">&nbsp;</div>
      </div>
      <form id="arsivmusteriformgonderme">
         {!!csrf_field()!!}
         <input id='arsiv_id' name='arsiv_id' type="hidden" value='{{$arsiv->id}}'>
         <input id='user_id' name='user_id' type="hidden" value='{{$user->id}}'>
         @if($arsiv->cevapladi==0)
         <div class="card-box mb-30" id="form_bolumu_musteri">
            @if($arsiv->durum===0)
            <div class="card-box text-center pd-20" >
               <p style="text-align: center;">Merhaba {{$user->name}}</p>
               <div class="alert alert-success" style="text-align:center;" role="alert">
                  {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} iptal edilmiştir bu yüzden formu görüntüleyemezsiniz. Teşekkürler.
               </div>
            </div>
            @elseif($arsiv->durum===1)
            <div class="card-box text-center pd-20" >
               <p style="text-align: center;">Merhaba {{$user->name}}</p>
               <div class="alert alert-success" style="text-align:center;" role="alert">
                  {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} onaylandığı için formu görüntüleyemezsiniz. Teşekkürler.
               </div>
            </div>
            @else        
            <div style="padding: 20px">
              
               <h3 style="text-align: center;">{{$isletme->salon_adi}}</h3>
               <h3 style="text-align: center;">Hizmet Sözleşmesi</h3>
               <div class="row">
                  <div class="col-md-12 col-sm-12 mb-30">
                     <div class="pd-20 card-box mb-10">
                        
                        <p>İşbu sözleşme metni, ekleri ile birlikte bir bütün arz eder ve SÖZLEŞME olarak adlandırılır. Sözleşme metnindeki hükümler ile sözleşmenin ekleri arasında çıkabilecek her türlü ihtilaf durumunda İşbu ana sözleşme maddeleri geçerli olacaktır.</p>

<p><strong><u>1. TARAFLAR</u></strong>

<p>Taraflar bu sözleşmede kısaca Hizmet veren ve Hizmet alan olarak adlandırılacaktır.

<p>İşbu sözleşme {{$isletme->adres}} adresinde faaliyet gösteren {{$isletme->salon_adi}} İşletmecisi ile {{$arsiv->musteri->adres}} adresinde bulunan {{$arsiv->musteri->name}} arasında imzalanmıştır.

<p><strong><u>2. YAPILACAK İŞ</u></strong>

<p>{{$arsiv->hizmet->hizmet_adi}}</p>

<p><strong><u>3. ÜCRET </u></strong>
<p>{{number_format($arsiv->toplam_ucret,2,',','.')}}</p>

<p><strong><u>4. SÜRE</u></strong></p>

<p>Bu sözleşme; 2 no'lu maddesinde belirtilen işlemin yapıldığı tarih ile işlem bitiş tarihinde kendiliğinden son bulur.</p>

<p><strong><u>5. GENEL ŞARTLAR</u></strong></p>

<p>Hizmet alan işyerinde yer alan iş emniyeti ve sağlığı kurullarına aynen uyacaktır.</p>

<p><strong><u>6. ÖZEL ŞARTLAR </u></strong></p>

<p>*Hizmet alan kendisine verilen Randevu bilgileri doğrultusunda , Hizmet verence bildirilen saatin en fazla 10 dakika sonrasında işleme hazır bir şekilde salonda bulunmalıdır. Randevu saatinde hazır olmaması durumunda , Hizmet verenin diğer işlerinin aksamasına sebebiyet vereceğinden o gün işlemi ifa edilmeyecektir.</p>

<p>*Hizmet Alan , yapılacak işlem ile ilgili bilgilendirilmiş , Uygulama esnasında öncesinin ve sonrasının net bir şekilde görülmesi amacıyla fotoğraf veya video görüntülerinin alınabileceğini ve bunların eğitsel ve bilimsel çalışmalarda kullanılabileceğini kabul etmiştir. (BELİRLİ UYGULAMALARDA) KALICI MAKYAJ VB UYGULAMALAR İÇİN GEÇERLİ</p>

<p>*Hizmet Alana uygulanacak işlemden önce nelere dikkat etmesi gerektiği ve işlem sonrasında oluşabilecek yan etkiler anlatılmıştır.</p>

<p>*Hizmet alan , yapılacak işleme engel olan herhangi bir sağlık probleminin olmadığını beyan etmiştir.</p>

<p>*Salondan alınan hizmetlerin kullanım süresi, satın alınan tarihten itibaren 6 aydır.</p>

<p>*Cihazlar Yıllık, Aylık, Günlük Bakımlara tabi tutulur. Bu süreçte işlem verilemediği durumlarda kişiye önceden bilgilendirme yapılır. Ve yeni seansları oluşturulur.</p>

<p>*Seanslar haber verilmeden gelinmediği takdirde, kişinin o seansı yanar.</p>

<p>*Satın Alınan Paket, alınan tarihten itibaren 6 ay içinde salona her hangi bir bilgi vermez ise ve seanslarına gelmez ise paket hakkı yanar.</p>

<p><strong><u>7. CAYMA HAKKI </u></strong></p>

<p>Sözleşmenin imzalanmasından itibaren 14 gün içinde herhangi gerekçe göstermeksizin ve cezai şart ödemeksizin , hiçbir hukuki ve cezai sorumluluk üstlenmeksizin sözleşmeden cayma hakkına sahiptir.</p>

<p><strong><u>8. İPTAL ŞARTLARI</u></strong></p>

<p>Bu sözleşme imzalandığı tarihte yürürlüğe girecek olup hizmetlerden yararlanıp yararlanmadığına bakılmaksızın sözleşme imzalandığı tarihten itibaren yürürlükte kalacaktır. Hizmet alan sözleşmesinin sona ereceği tarihten önce sözleşmeyi herhangi bir şekilde feshederse Hizmet verene aldığı işlem hizmeti karşılığında belirtilen KDV dahil toplam hizmet bedelinin %25 i kadar tazminat ödemeyi kabul eder.</p>

<p><strong><u>9. UYUŞMAZLIK</u></strong></p>

<p>Bu sözleşmeden,doğacak uyuşmazlık {{$isletme->il_id ? $isletme->il->il_adi : ''}} Mahkemelerince çözümlenir.</p>

<p><strong><u>10. HÜKÜM OLMAYAN HALLER</u></strong></p>

<p>Sözleşmede hüküm bulunmayan hallerde 4857 sayılı İş Kanunu hükümleri uygulanır.</p>

<p><strong><u>11. İMZA</u></strong></p>

<p>Bir sayfadan oluşan iş bu belirli süreli hizmet sözleşmesi, taraflarca {{date('d.m.Y',strtotime($arsiv->created_at))}} tarihinde tanzim edilip, okunarak imzalanmakla, belirtilen şartlarla iş görmeyi karşılıklı olarak kabul, beyan ve taahhüt etmişlerdir.</p>

<p>Satın almış olduğunuz hizmet her hangi 2. şahsa devredilemez.</p>

<p>Satın almış olduğunuz hizmet her hangi bir işlemle değiştirilemez.</p>

<p>İşletmede kullanılan cihazlar bakıma/tamire gidebilir ve bu süreç 30-45 gün arası sürebilir. Bu süreçte kişiyi mağdur etmemek adına paketine artı seans ilave edilir.</p>
                     </div>
                      
                     <div class="pd-20 card-box mb-10">
                        <p>OKUDUM ANLADIM </p>
                        <div class="form-group">
                           <p>İMZA  <button class="btn btn-danger" id='imzasil' style="float:right;"><i class="fa fa-times"></i> Sil</button></p>
                           <canvas id="musteriimza" name="musteri_imza" style="background-color:#fff;border:1px solid #000;"></canvas>
                        </div>
                     </div>
                     <div class="pd-20 card-box mb-10">
                        <div class="form-group">
                           <label>Onay Kodu</label>
                           <input type="tel" required name="formdogrulama" value=" " class="form-control">
                        </div>
                     </div>
                     <div class="col-md-12 col-xs-12 col-sm-12 col-12">
                        <button type="submit" name="formugonder" class="btn btn-primary btn-lg btn-block"> Formu Kaydet</button>
                     </div>
                  </div>
               </div>
            </div>
            @endif
         </div>
      </form>
      @endif
      <form id="cevapformumusteri" style="{{(!$arsiv->cevapladi) ? 'display: none' : 'display: block'}}">
         <div class="card-box text-center pd-20" >
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
             $(document).ready(function () {
         var $canvas = $('#musteriimza');
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
          
         });
      </script>
      <div id='hata'></div>
   </body>
</html>