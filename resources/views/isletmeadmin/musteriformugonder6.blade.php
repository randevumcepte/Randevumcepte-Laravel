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
               <p style="text-align: center;">Merhaba {{$user->name}}</p>
               <p style="text-align: center;">{{$isletme->salon_adi}} tarafından {{\App\FormTaslaklari::where("id",$arsiv->form_id)->value("form_adi")}} için aşağıdaki bilgileri doldurunuz.</p>

               <div class="row">
                  <div class="col-md-12 col-sm-12 mb-30">
                     
                     <div class="pd-20 card-box mb-10">
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <p style="font-size: 15px">Şeker hastalığınız var mı? </p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <div class="row" style="margin-top: 10px;">
                                 <p>Hayır</p>
                                 <label class="switch" style="margin-left: 5px; margin-right: 5px;">
                                 <input   type="checkbox" id="seker" name="seker" {{($arsiv->seker) ? 'checked' : ''}}>
                                 <span class="slider" style="border-radius: 20px;"></span>
                                 </label> 
                                 <p>Evet</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="pd-20 card-box mb-10">
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <p style="font-size: 15px">Herhangi bir  alerjiniz var mı? </p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <div class="row" style="margin-top: 20px;">
                                 <p>Hayır</p>
                                 <label class="switch" style="margin-left: 5px; margin-right: 5px;">
                                 <input   type="checkbox" id="alerji" name="alerji" {{($arsiv->alerji_bagisiklik_romatizma) ? 'checked' : ''}} >
                                 <span class="slider" style="border-radius: 20px;"></span>
                                 </label> 
                                 <p>Evet</p>
                              </div>
                           </div>
                        </div>
                     </div>
                    <div class="pd-20 card-box mb-10">
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <p style="font-size: 15px">Herhangi bir kronik rahatsızlığınız var mı? </p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <div class="row" style="margin-top: 20px;">
                                 <p>Hayır</p>
                                 <label class="switch" style="margin-left: 5px; margin-right: 5px;">
                                 <input   type="checkbox"  id="kronik" name="kronik" {{($arsiv->kronik) ? 'checked' : ''}}>
                                 <span class="slider" style="border-radius: 20px;"></span>
                                 </label> 
                                 <p>Evet</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <div class="pd-20 card-box mb-10">
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <p style="font-size: 15px">Doktor tarafından reçeteli kullandığınız ilaç var mı? </p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <div class="row" style="margin-top: 20px;">
                                 <p>Hayır</p>
                                 <label class="switch" style="margin-left: 5px; margin-right: 5px;">
                                 <input   type="checkbox"  id="receteli_ilaclar_var" name="receteli_ilaclar_var" {{($arsiv->receteli_ilaclar_var) ? 'checked' : ''}}>
                                 <span class="slider" style="border-radius: 20px;"></span>
                                 </label> 
                                 <p>Evet</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                   
                     
                     <div class="pd-20 card-box mb-10">
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <p style="font-size: 15px">Gebelik riski, gebelik ya da emzirme durumunuz var mı? </p>
                           </div>
                           <div class="col-md-6 col-sm-6 col-6 col-xs-6">
                              <div class="row" style="margin-top: 20px;">
                                 <p>Hayır</p>
                                 <label class="switch" style="margin-left: 5px; margin-right: 5px;">
                                 <input   type="checkbox"  id="gebelik" name="gebelik" {{($arsiv->gebelik) ? 'checked' : ''}}>
                                 <span class="slider" style="border-radius: 20px;"></span>
                                 </label> 
                                 <p>Evet</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="pd-20 card-box mb-10">
                       <div class="row">
                         <div class="col-md-12">
                           <ul style="list-style-type: circle;">
<ol>* Tarafıma uygulanacak LİFU  uygulaması izah edilmiştir .İşlem sırasında ve sonrasında yaplması gerekenler bildirilmiştir.</ol>
<ol>* İşlemin kalıcılık süresinin cilt tipi ve yaşa bağlı olarak değiştiğini ve bu süre maksimum 3 senedir.</ol>
<ol>* Nadiren’de olsa ödem ve kızarıklık yaşanabilir bunlar  geçici komplikasyonlardır işlem sırasında oluşabilcek riskleri kabul ediyorum </ol>
<ol>* İşlem tamamlandıktan sonra evde yapacağım bakım tarafıma tebliğ edilmiştir..ilk gün sıcak su uygulamasıı yok</ol>
<ol>*LİFU  uygulaması tek seanslık bir işlemdir kontrol seansı 1 ay veya 3 ay sonra yapılır.</ol>
<ol>* Uygulama öncesi ve sonrası resimlerinin paylaşılmasına  müsaade ediyorum. </ol>
                          

                           </ul>

                         </div>
                       </div>
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