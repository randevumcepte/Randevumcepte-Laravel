@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="rc-sm-page">
   <div class="rc-sm-header">
      <div class="rc-sm-title-row">
         <div class="rc-sm-icon-bubble"><i class="fa fa-commenting-o"></i></div>
         <div>
            <h1 class="rc-sm-title">SMS Yönetimi</h1>
            <nav class="rc-sm-breadcrumb" aria-label="breadcrumb">
               <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               <span class="rc-sm-sep">›</span>
               <span class="rc-sm-active">SMS Yönetimi</span>
            </nav>
         </div>
      </div>
   </div>
   <div class="row clearfix">
     <div class="col-lg-12 col-md-12 col-sm-12 mb-30">
       <div class="pd-20 card-box">
       
         <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <img src="/public/img/caution-sign.png" >
              <span >
                   Yaptığınız SMS gönderimlerinin tüm yasal ve cezai yükümlülüğü size ait olup, Elektronik Ticaretin Düzenlenmesi Hakkında Kanun uyarınca, ticari maksatlı SMS gönderimi yapacağınız numaralardan daha önce ticari ileti izni alarak firmanıza ait İleti Yönetim Sistemi hesabınıza (İYS) kayıt etmiş olmanız gerekmektedir. Ticari ileti izni aldığınız numaralara gönderim yaparken, ücretsiz bir numaraya SMS göndererek SMS ret seçeceği sunulması ve firmanıza ait MERSİS numarasının gönderilen SMS içeriğinde belirtilmesi kanunen zorunludur. İzinsiz veya gerekli yükümlülükler yerine getirilmeksizin yapılan gönderimler için alıcıların şikayet etmesi durumunda, İl Ticaret Müdürlükleri tarafından gönderilen her bir SMS için yüklü miktarda para cezaları kesilmektedir.</span>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                </div>
        
         <div class="tab">
           <div class="row clearfix">
             <div class=" col-md-12 col-sm-12">
               <ul class="nav nav-tabs element" role="tablist" style="overflow-x:scroll;height: 80px; ">
                 <li class="nav-item">
                  <button href="#sms_raporlari"
                  class="btn btn-outline-primary active"
                  data-toggle='tab'
                  style="width: 130px;height: 60px"
                  role="tab"
                  aria-selected="true"
                  > SMS Raporları </button>
                </li>
                @if(
                   \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.sms_gonder') ||
                   \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.toplu_sms')
                )
                 <li class="nav-item">
                  <button href="#sablon_ayarlari"
                  class="btn btn-outline-primary "
                  data-toggle='tab'
                  role="tab"
                   style="width: 200px;height: 60px;margin-left: 10px;"
                  aria-selected="false"
                  >Şablon Ayarları ve Toplu SMS</button>
                </li>
                @endif
                 @if(DB::table('model_has_roles')->where('role_id',1)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() > 0  )
                 <li class="nav-item">
                  <button href="#sms_ayarlari"
                  class="btn btn-outline-primary"
                  data-toggle='tab'
                   
                  style="margin-left: 10px; width: 130px;height: 60px" 
                  role="tab"
                  aria-selected="false" 
                  > SMS Ayarları </button>
                </li>
                @endif
                 <li class="nav-item">
                  <button href="#sms_kara_liste"
                  class="btn btn-outline-primary "
                  style="margin-left: 10px; width: 130px;height: 60px" 
                  data-toggle='tab'
                  
                  role="tab"
                  aria-selected="false" 
                  > SMS Kara Liste </button>
                </li>

               </ul>
             </div>
             <div class="col-md-12 col-sm-12" style="margin-top: 10px;">
              <div class="tab-content">
               <div class="tab-pane fade show active" id="sms_raporlari" role="tabpanel">
                 <div class="pb-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">SMS Raporları</h2>
                   </div>
                  </div>
                  <div class="tab" >
                    <ul class="nav nav-tabs element" role="tablist" style="overflow-x: scroll;">
                      <li class="nav-item">
                         <button
                                    class="btn btn-outline-primary active"
                                    data-toggle="tab"
                                    href="#otomatik_sms_raporlar"
                                    role="tab"
                                    aria-selected="false"
                                    >Bildirim SMS Raporları</button
                                 >
                      </li>
                      <li class="nav-item">
                         <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#grup_sms_raporlar"
                                    role="tab"
                                    style="margin-left: 20px;"
                                    aria-selected="false"
                                    >Grup SMS Raporları</button
                                 >
                      </li>
                      <li class="nav-item">
                         <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#filtreli_sms_raporlar"
                                    role="tab"
                                    style="margin-left: 20px;"
                                    aria-selected="false"
                                    >Filtreli SMS Raporları</button
                                 >
                      </li>
                      <li class="nav-item">
                         <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#toplu_sms_raporlar"
                                    role="tab"
                                    style="margin-left: 20px;"
                                    aria-selected="false"
                                    >Toplu SMS Raporları</button
                                 >
                      </li>
                      <li class="nav-item">
                         <button
                                    class="btn btn-outline-primary"
                                    data-toggle="tab"
                                    href="#kampanya_sms_raporlar"
                                    role="tab"
                                    style="margin-left: 20px;"
                                    aria-selected="false"
                                    >Kampanya SMS Raporları</button
                                 >
                      </li>

                    </ul>
                    <div class="tab-content" >
                      <div class="tab-pane fade show active" id="otomatik_sms_raporlar" role="tab-panel" style="margin-top: 20px;">
                     
                       
                        <table class="data-table table stripe hover nowrap" id='bildirim_sms_raporlari' style="width:100%">
                        
                          <thead>
                           <tr>
                             <th>Tarih</th>
                             <th>Adet</th>
                             <th>Toplam Kredi</th>
                             <th>Mesaj İçeriği</th>
                             <th>Durum</th>
                             <th>Detay</th>

                           </tr>
                          </thead>
                          <tbody>
                          
                          </tbody>
                        </table>
                      </div>
                      <div class="tab-pane fade show" id="toplu_sms_raporlar" role="tab-panel" style="margin-top: 20px;">
                     
                       
                        <table class="data-table table stripe hover nowrap" id='toplu_sms_raporlari'  style="width:100%">
                        
                          <thead>
                           <tr>
                             <th>Tarih</th>
                             <th>Adet</th>
                             <th>Toplam Kredi</th>
                             <th>Mesaj İçeriği</th>
                             <th>Durum</th>
                             <th>Detay</th>

                           </tr>
                          </thead>
                          <tbody>
                          
                          </tbody>
                        </table>
                      </div>
                      <div class="tab-pane fade show" id="grup_sms_raporlar" role="tab-panel" style="margin-top: 20px; ">
                     
                       
                        <table class="data-table table stripe hover nowrap" id='grup_sms_raporlari'  style="width:100%">
                        
                          <thead>
                           <tr>
                             <th>Tarih</th>
                             <th>Adet</th>
                             <th>Toplam Kredi</th>
                             <th>Mesaj İçeriği</th>
                             <th>Durum</th>
                             <th>Detay</th>

                           </tr>
                          </thead>
                          <tbody>
                          
                          </tbody>
                        </table>
                      </div>
                      <div class="tab-pane fade show" id="filtreli_sms_raporlar" role="tab-panel" style="margin-top: 20px;">
                     
                       
                        <table class="data-table table stripe hover nowrap" id='filtreli_sms_raporlari'  style="width:100%">
                        
                          <thead>
                           <tr>
                             <th>Tarih</th>
                             <th>Adet</th>
                             <th>Toplam Kredi</th>
                             <th>Mesaj İçeriği</th>
                             <th>Durum</th>
                             <th>Detay</th>

                           </tr>
                          </thead>
                          <tbody>
                          
                          </tbody>
                        </table>
                      </div>
                      <div class="tab-pane fade show" id="kampanya_sms_raporlar" role="tab-panel" style="margin-top: 20px;">
                     
                       
                        <table class="data-table table stripe hover nowrap" id='kampanya_sms_raporlari'  style="width:100%">
                        
                          <thead>
                           <tr>
                             <th>Tarih</th>
                             <th>Adet</th>
                             <th>Toplam Kredi</th>
                             <th>Mesaj İçeriği</th>
                             <th>Durum</th>
                             <th>Detay</th>

                           </tr>
                          </thead>
                          <tbody>
                          
                          </tbody>
                        </table>
                      </div>
                    </div>
                     
                  </div>
                   
                 </div>
               </div>
              @if(
                 \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.sms_gonder') ||
                 \App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'pazarlama.toplu_sms')
              )
               <div class="tab-pane fade show" id="sablon_ayarlari" role="tabpanel">
                 <div class="pd-20" id="smsgonderimkismi">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Şablon Ayarları ve Toplu SMS</h2>
                   </div>
              <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button class="btn btn-success" data-toggle="modal" id='sablon_olustur' data-target="#sablon_olustur_modal"> <i class="fa fa-plus"></i> Şablon Oluştur</button>
                   </div>
                  </div>
                  <form id="sablonsmsform"  method="GET">
                     {{csrf_field()}}
                <input type="hidden" name="toplu_id" id="smstopluid">
                <input type="hidden" name="sube" value="{{$isletme->id}}">
                      <div class="row" data-value="0">
                     <div class="col-md-6">
                         <div class="form-group">
                           <input type="text" name="sablon_baslik" id="sablon_baslik" placeholder="Şablon Adı" class="form-control">
                           <br>
                          <textarea style="height: 230px;"  onchange="countChar(this,event)" onkeyup="countChar(this,event)" onkeydown="countChar(this,event)" class="form-control" name="smsmesaj" id="smsmesaj" placeholder="Mesaj İçeriği"></textarea>
                          <div id="karaktersayisi"></div>
                           <script>
                                function countChar(val,event) {

                                  var len = val.value.length;

                                  if(len<=155){
                                    $('#karaktersayisi').text(len+' (Gönderim başına 1 sms üzerinden ücretlendirilecektir)');
                                 $('#karaktersayisi').attr('style','color:black;background-color:white');
                                  }

                                      else if(len>155 && len <=292) {
                                          $('#karaktersayisi').text(len+' (Gönderim başına 2 sms üzerinden ücretlendirilecektir)');
                                           $('#karaktersayisi').attr('style','color:white;background-color:orange');

                                      }

                                     else if(len>292 && len <=439) {
                                        $('#karaktersayisi').text(len+' (Gönderim başına 3 sms üzerinden ücretlendirilecektir)');
                                           $('#karaktersayisi').attr('style','color:white;background-color:red');

                                     }

                                    else if(len>439 && len <=587) {
                                         $('#karaktersayisi').text(len+' (Gönderim başına 4 sms üzerinden ücretlendirilecektir)');
                                           $('#karaktersayisi').attr('style','color:white;background-color:red');

                                    }
                                   else if(len>587 && len <=735) {
                                         $('#karaktersayisi').text(len+' (Gönderim başına 5 sms üzerinden ücretlendirilecektir)');
                                           $('#karaktersayisi').attr('style','color:white;background-color:red');

                                    }
                                    else if(len>735 && len <=882) {
                                     $('#karaktersayisi').text(len+' (Gönderim başına 6 sms üzerinden ücretlendirilecektir)');
                                           $('#karaktersayisi').attr('style','color:white;background-color:red');

                                    }
                                };
                              </script>

                        </div>
                        <div class="form-group">
                          <button type="button" id="topluSmsGonderButon" class="btn btn-success">Toplu SMS'i Gönder</button>

                        </div>
                     </div>
                      <div class="col-md-6">
                          <div class="col-sm-12">
                              <div class="container">
                                  <label>Müşterileri Seçiniz</label>
                                  <div class="row" id="arama_musteri_liste_TopluSMS" style="margin-bottom: 40px;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                          <input type="text" id="musteriarama_toplusms" class="form-control" placeholder="Müşteri arayın..." autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-3"><button id="topluSMSTumMusterileriSec" type="button" class="btn btn-info btn-block">Tümünü Seç</button></div>
                                    <div class="col-md-3"><button id="topluSMSTumMusterileriKaldir" type="button" class="btn btn-info btn-block">Tümünü Kaldır</button></div>
                                    <div class="col-md-12">
                                      <div id="musteriListesiTopluSMS" style="width:100%;border:1px solid #e2e2e2;border-radius: 5px;height: 260px;overflow-y: auto;">
                                        <div class="text-center py-4 text-muted" id="topluSMSIlkMesaj">
                                          <i class="fa fa-users fa-2x mb-2"></i>
                                          <p class="mb-0">Müşteriler yükleniyor...</p>
                                        </div>
                                      </div>
                                      <div id="topluSMSYukleniyor" class="text-center py-2" style="display:none;">
                                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                                        <span class="text-muted">Yükleniyor...</span>
                                      </div>
                                      <div id="topluSMSSeciliMusteriler" style="margin-top: 10px; font-weight: bold;">
                                           0 müşteri seçildi
                                      </div>
                                    </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                    </div>

        </form>
         <div class="col-md-12">

           <div class="panel-heading panel-heading-divider text-center border-container" style="font-weight: bold;margin-top: 5px; width: 100% ">
              <span style=" color: #5f00bf; font-size: 16px;">Şablonlar (Aşağıdaki metin şablonlarını yollamak için üstüne tıklayın)</span>

           </div>
         </div>
        <div class="row" data-value="0"  id="taslaklarbolumu2">

            @foreach($taslaklar as $taslak)

               <div class="col-md-3">
                <div class="form-group">

<div style="   width:100%; max-height:100%; margin-left: 5px; margin-top: 15px; ">
                <input type="hidden" id="smstaslak{{$taslak->id}}" value="{{$taslak->taslak_icerik}}">
                 <input type="hidden" id="smstaslakbaslik{{$taslak->id}}" value="{{$taslak->baslik}}">
                <a class="smstaslaklari" title="Metni Kopyala"  data-value="{{$taslak->id}}" style="position:relative; cursor: pointer;"  name="smstaslaklari" >

                   <p style="border:1px solid grey;font-size:18px;font-weight: bold;color:black ;border-radius: 30px; text-align: center; ">{{$taslak->baslik}}</p>
                  <p style="border:1px solid grey;padding:5px;background-color: #e4e4e2; border-radius: 20px;border-bottom-left-radius: 0;color:black;font-size:15px; overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
 " >{{$taslak->taslak_icerik}}</p>


                </a>
           </div>

                </div>

           </div>

             @endforeach
        </div>


                 </div>
               </div>
              @endif
              @if(DB::table('model_has_roles')->where('role_id',1)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() > 0  )
                <div class="tab-pane fade show" id="sms_ayarlari" role="tabpanel">
                 <div class="pd-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">SMS Ayarları</h2>
                   </div>
                  </div>
                  <form id="otomatik_sms_ayarlari" method="POST">
                    {{csrf_field()}}
                    <input  type="hidden" name="sube" value="{{$isletme->id}}">
                   <div class="row" data-value="0">
                      <div class=" col-md-4 col-sm-12 mb-30">
                         
                            <div class="pd-20 card-box mb-10">
                             
                               <h6>Doğrulama Kodu</h6>
                               <p style="font-weight: 5px;">Randevu ve senet işlemlerinde müşterinin cep telefonuna doğrulama kodu gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[15]->musteri) ? 'checked' : ''}} id="customCheck16" name='randevuayar_16_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck16">Açık / Kapalı</label>
                                                                      </div>
                               </div>
                            </div>
                             
                            <div class="pd-20 card-box mb-10">
                               <h6>Randevu Talebi Onaylandığında</h6>
                               <p style="font-weight: 5px;">Gelen online randevu talebi/isteği onaylandığında SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[1]->musteri) ? 'checked' : ''}} id="customCheck3" name='randevuayar_2_musteri'>
                                        <label class="custom-control-label" for="customCheck3">Müşteri</label>
                                   
                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                                          
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[1]->personel) ? 'checked' : ''}} id="customCheck4" name='randevuayar_2_personel'>
                                        <label checked class="custom-control-label" for="customCheck4">Personel</label>
                                    
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box mb-10"  >
                               <h6>Aktif Randevu İptalinde</h6>
                               <p style="font-weight: 5px;">Oluşturulan randevu iptal edildiğinde SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[2]->musteri) ? 'checked' : ''}} id="customCheck5" name='randevuayar_3_musteri'>
                                        <label class="custom-control-label" for="customCheck5">Müşteri</label>
                                
                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                                    
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[2]->personel) ? 'checked' : ''}} id="customCheck6" name='randevuayar_3_personel'>
                                        <label class="custom-control-label" for="customCheck6">Personel</label>
                                  
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box mb-10"  >
                               <h6>Müşteri Eklendiğinde</h6>
                               <p style="font-weight: 5px;">Müşteri kaydı sonrasında müşterinize işletmenizin müşteri listesine kaydedildiğine dair bilgilendirme SMS'i gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[3]->musteri) ? 'checked' : ''}} id="customCheck7" name='randevuayar_4_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck7">Açık / Kapalı</label>
                                     
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box mb-10"  >
                              <?php  
                                 if( date('m-d 19:35') == date('m-d 19:35', strtotime('1988-08-31'.' 19:35')))
                                   echo 'eşit';
                              ?>
                               <h6>Aktif ve 60 Gün Boyunca İşletmenizi Ziyaret Etmemiş Müşteriye Hatırlatma</h6>
                               <p style="font-weight: 5px;">60 gün boyunca işletmenizi ziyaret etmemiş müşterilerinize otomatik hatırlatma SMS'i gönderilir. Örnek Mesaj İçeriği : Sayın Figen Çelik , sizi çok özledik! 60 gündür işlem yapmadığınızı farkettik. Tekrar görüşmek dileğiyle, Mutlu günler dileriz. Randevu almak için: https://{{$_SERVER['HTTP_HOST']}}/{{str_slug($isletme->salon_adi,'-')}}-{{$isletme->id}}
                               </p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" id="customCheck9" {{($sms_ayarlari[4]->musteri) ? 'checked' : ''}} name='randevuayar_5_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck9">Açık</label>
                                   
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box mb-10"  >
                        
                               <h6>Form SMS Olarak Gönderme</h6>
                               <p style="font-weight: 5px;">Formu müşterinin doldurması için linki sms olarak gönderilsin
                               </p>
                                   <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[17]->musteri) ? 'checked' : ''}} id="customCheck25" name='randevuayar_25_musteri'>
                                        <label class="custom-control-label" for="customCheck25">Müşteri</label>
                                
                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                                    
                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[17]->personel) ? 'checked' : ''}} id="customCheck26" name='randevuayar_25_personel'>
                                        <label class="custom-control-label" for="customCheck26">Personel</label>
                                  
                                  </div>
                               </div>
                          </div>
                          <div class="pd-20 card-box  mb-10"  >
                               <h6>Müşteri Geldi Bildirimi</h6>
                               <p style="font-weight: 5px;">Personelinize müşterinizin geldiğini bildirmek için SMS olarak gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                  
                                        <input type="checkbox" class="custom-control-input" id="customCheck34" {{($sms_ayarlari[20]->personel) ? 'checked' : ''}} name='geldiayar_34_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck34">Açık / Kapalı</label>
                            
                                  </div>
                               </div>
                            
                         </div>
                          <div class="pd-20 card-box  mb-10"  >
                               <h6>KVKK Bildirimi</h6>
                               <p style="font-weight: 5px;">  </p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                  
                                        <input type="checkbox" class="custom-control-input" id="customCheck37" {{($sms_ayarlari[21]->musteri) ? 'checked' : ''}} name='kvkk_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck37">Açık / Kapalı</label>
                            
                                  </div>
                               </div>
                            
                         </div>
                      </div>

                      <div class=" col-md-4 col-sm-12 mb-30">
                         <div class="pd-20 card-box  mb-10">
                           
                               <h6>Bir Gün Önce Randevu Hatırlatma</h6>
                               <p style="font-weight: 5px;">Randevu tarihine bir günden fazla gün varsa, randevu tarihinden bir gün öncesinden SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">

                                        <input type="checkbox" class="custom-control-input" id="customCheck13" {{($sms_ayarlari[5]->musteri) ? 'checked' : ''}} name='randevuayar_6_musteri'>
                                        <label class="custom-control-label" for="customCheck13">Müşteri</label>

                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >

                                        <input type="checkbox" class="custom-control-input" id="customCheck14" {{($sms_ayarlari[5]->personel) ? 'checked' : ''}} name='randevuayar_6_personel'>
                                        <label class="custom-control-label" for="customCheck14">Personel</label>

                                  </div>
                               </div>
                            </div>

                            <div class="pd-20 card-box mb-10">
                             
                               <h6>Yaklaşan Notu Hatırlatma</h6>
                               <p style="font-weight: 5px;">Notlara dair SMS gönderimlerinin gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" id="customCheck18" {{($sms_ayarlari[16]->personel) ? 'checked' : ''}}  name='randevuayar_17_personel_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck18">Açık / Kapalı</label>
                                                                      </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Randevu Talebi Reddedildiğinde</h6>
                               <p style="font-weight: 5px;">Gelen online randevu talebi/isteği reddedildiğinde SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                 
                                        <input type="checkbox" class="custom-control-input" id="customCheck35" {{($sms_ayarlari[6]->musteri) ? 'checked' : ''}} name='randevuayar_7_musteri'>
                                        <label class="custom-control-label" for="customCheck35">Müşteri</label>
                                                                   </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                              
                                        <input  type="checkbox" class="custom-control-input" id="customCheck36" {{($sms_ayarlari[6]->personel) ? 'checked' : ''}} name='randevuayar_7_personel'>
                                        <label class="custom-control-label" for="customCheck36">Personel</label>
                                    
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Doğum Günü Gönderimi</h6>
                               <p style="font-weight: 5px;">Doğum günü olan müşterilerinize kutlama SMS'i gitsin/gitmesin ayarıdır. Bu ayar işletmenize/kendinize özel gönderici adınızın olması durumunda çalışmaktadır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                  
                                        <input type="checkbox" class="custom-control-input" id="customCheck17" {{($sms_ayarlari[7]->musteri) ? 'checked' : ''}} name='randevuayar_8_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck17">Açık / Kapalı</label>
                                 
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Randevu Sürükle Ve Bırak</h6>
                               <p style="font-weight: 5px;">Randevu sürükle ve bırakıldığında müsteriye SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                      
                                        <input type="checkbox" class="custom-control-input" id="customCheck19" {{($sms_ayarlari[8]->musteri) ? 'checked' : ''}} name='randevuayar_9_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck19">Açık / Kapalı</label>
                                    
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>SMS'den Etkinlik & Kampanya Katılımı İçin Link Gönderimi</h6>
                               <p style="font-weight: 5px;">Müşterinin oluşturduğunuz etkinlik veya kampanyalara katılacağını ya da katılmayacağını öğrenmek için bir link gönderilir. Bu link müşterinin katılımının olumlu ya da olumsuz olarak seçmesi için bir ayardır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                  
                                        <input type="checkbox" class="custom-control-input" id="customCheck21" {{($sms_ayarlari[9]->musteri) ? 'checked' : ''}} name='randevuayar_10_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck21">Açık / Kapalı</label>
                            
                                  </div>
                               </div>
                            
                         </div>
                         <div class="pd-20 card-box  mb-10"  >
                               <h6>Seans Bilgisi Bildirimi</h6>
                               <p style="font-weight: 5px;">Müşterinizin seans bilgilerinin SMS olarak gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                  
                                        <input type="checkbox" class="custom-control-input" id="customCheck33" {{($sms_ayarlari[19]->musteri) ? 'checked' : ''}} name='seansayar_31_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck33">Açık / Kapalı</label>
                            
                                  </div>
                               </div>
                            
                         </div>
                          <div class="pd-20 card-box  mb-10"  >
                               <h6>Satış ve Tahsilat Silme Bildirimi</h6>
                               <p style="font-weight: 5px;">Tahsilat/satış silme işlemlerinde ve tahsil edilmiş kalem düzenlenirken hesap sahibine SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                  
                                        <input type="checkbox" class="custom-control-input" id="customCheck38" {{($sms_ayarlari[22]->personel) ? 'checked' : ''}} name='satis_tahsilat_bilgilendirme_personel_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck38">Açık / Kapalı</label>
                            
                                  </div>
                               </div>
                            
                         </div>
                      </div>
                      <div class=" col-md-4 col-sm-12 mb-30">
                         <div class="pd-20 card-box  mb-10">
                          
                               <h6>Yaklaşan Randevu Hatırlatma</h6>
                               <p style="font-weight: 5px;">Randevu hatırlatmalarına dair SMS gönderimlerinin gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">

                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[0]->musteri) ? 'checked' : ''}} name='randevuayar_1_musteri' id="customCheck1">
                                        <label class="custom-control-label" for="customCheck1"  >Müşteri</label>
                                                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >

                                        <input type="checkbox" class="custom-control-input" {{($sms_ayarlari[0]->personel) ? 'checked' : ''}} name='randevuayar_1_personel' id="customCheck2">
                                        <label class="custom-control-label" for="customCheck2">Personel</label>

                                  </div>
                               </div>
                               <p style="font-weight: 5px;">Kaç saat önce gönderilecek?</p>
                               <select class="form-control" name="randevu_hatirlatama_saat_once" >
                                 
                                  <option {{($isletme->randevu_sms_hatirlatma==1) ? 'selected' : ''}} value="1">1 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==2) ? 'selected' : ''}} value="2" selected="">2 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==3) ? 'selected' : ''}} value="3">3 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==4) ? 'selected' : ''}} value="4">4 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==5) ? 'selected' : ''}} value="5">5 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==6) ? 'selected' : ''}} value="6">6 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==7) ? 'selected' : ''}} value="7">7 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==8) ? 'selected' : ''}} value="8">8 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==9) ? 'selected' : ''}} value="9">9 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==10) ? 'selected' : ''}} value="10">10 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==11) ? 'selected' : ''}} value="11">11 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==12) ? 'selected' : ''}} value="12">12 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==13) ? 'selected' : ''}} value="13">13 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==14) ? 'selected' : ''}} value="14">14 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==15) ? 'selected' : ''}} value="15">15 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==16) ? 'selected' : ''}} value="16">16 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==17) ? 'selected' : ''}} value="17">17 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==18) ? 'selected' : ''}} value="18">18 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==19) ? 'selected' : ''}} value="19">19 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==20) ? 'selected' : ''}} value="20">20 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==21) ? 'selected' : ''}} value="21">21 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==22) ? 'selected' : ''}} value="22">22 saat</option>
                                  <option {{($isletme->randevu_sms_hatirlatma==23) ? 'selected' : ''}} value="23">23 saat</option>
                                 
                               </select>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Online Randevu Talebi Bilgilendirme</h6>
                               <p style="font-weight: 5px;">Yeni bir online randevu talebi/isteği geldiğinde SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                   
                                        <input type="checkbox" class="custom-control-input" id="customCheck23" {{($sms_ayarlari[10]->musteri) ? 'checked' : ''}} name='randevuayar_11_musteri'> 
                                        <label class="custom-control-label" for="customCheck23">Müşteri</label>
                                    
                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                                   
                                        <input type="checkbox" class="custom-control-input" id="customCheck24" {{($sms_ayarlari[10]->personel) ? 'checked' : ''}}  name='randevuayar_11_personel'>
                                        <label class="custom-control-label" for="customCheck24">Personel</label>
                                 
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Randevu Oluşturulduğunda</h6>
                               <p style="font-weight: 5px;">Randevu oluşturulduğu esnada SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                                    
                                        <input type="checkbox" class="custom-control-input" id="customCheck251" {{($sms_ayarlari[11]->musteri) ? 'checked' : ''}}  name='randevuayar_12_musteri'>
                                        <label class="custom-control-label" for="customCheck251">Müşteri</label>
                                  
                                  </div>
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                                
                                        <input type="checkbox" class="custom-control-input" id="customCheck261" {{($sms_ayarlari[11]->personel) ? 'checked' : ''}}  name='randevuayar_12_personel'>
                                        <label class="custom-control-label" for="customCheck261">Personel</label>
                         
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Randevu Sonrası Değerlendirme</h6>
                               <p style="font-weight: 5px;">Randevu sonrasında değerlendirme SMS'i gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                   
                                        <input type="checkbox" class="custom-control-input" id="customCheck27" {{($sms_ayarlari[12]->musteri) ? 'checked' : ''}} name='randevuayar_13_musteri_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck27">Açık / Kapalı</label>
                                     </div>
                                
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10"  >
                               <h6>Randevu Güncelleme</h6>
                               <p style="font-weight: 5px;">Güncellenen randevu saati ve tarihini SMS ile gönder/gönderme ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                            
                                        <input type="checkbox" class="custom-control-input" id="customCheck29" {{($sms_ayarlari[13]->musteri) ? 'checked' : ''}} name='randevuayar_14_musteri'>
                                        <label class="custom-control-label" for="customCheck29">Müşteri</label>
                                     </div>
                               
                                  <div class="col-md-6 custom-control custom-checkbox mb-5" >
                                    
                                        <input type="checkbox" class="custom-control-input" id="customCheck30" {{($sms_ayarlari[13]->personel) ? 'checked' : ''}} name='randevuayar_14_personel'>
                                        <label class="custom-control-label" for="customCheck30">Personel</label>
                                    
                                  </div>
                               </div>
                            </div>
                            <div class="pd-20 card-box  mb-10">
                               <h6>Kara Liste</h6>
                               <p style="font-weight: 5px;">Müsteri numarası kara listeye eklendiginde SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                               
                                        <input type="checkbox" class="custom-control-input" id="customCheck11" {{($sms_ayarlari[14]->musteri) ? 'checked' : ''}} name='randevuayar_15_musteri_acik_kapali'>

                                        <label class="custom-control-label" for="customCheck11">Açık / Kapalı</label>
                                 
                                  </div>
                               </div>
                            </div>
                              <div class="pd-20 card-box  mb-10">
                               <h6>Para İşlemleri Bilgilendirme</h6>
                               <p style="font-weight: 5px;">Kasaya para ekleme ve kasadan para alma işlemlerinde hesap sahibine SMS gitsin/gitmesin ayarıdır.</p>
                               <div class="row">
                                  <div class="col-md-6 custom-control custom-checkbox mb-5">
                               
                                        <input type="checkbox" class="custom-control-input" id="customCheck50" {{($sms_ayarlari[17]->personel) ? 'checked' : ''}} name='randevuayar_26_personel_acik_kapali'>
                                        <label class="custom-control-label" for="customCheck50">Açık / Kapalı</label>
                                 
                                  </div>
                               </div>
                            </div>
                            
                            <div class="col-md-12" style="margin-top: 80px;">
                               <button type="submit" class="btn btn-success btn-block">Ayarları Güncelle</button>
                            </div>
                         </div>
                       
                   </div>
                </form>
                 </div>
               </div>
               @endif
                <div class="tab-pane fade show" id="sms_kara_liste" role="tabpanel">
                 <div class="pb-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Kara Liste</h2>
                   </div>
                   <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button class="btn btn-dark" data-toggle="modal" data-target="#kara_liste_olustur_modal"> <i class="fa fa-plus"></i> Kara Listeye Ekle</button>
                   </div>
                  </div>
                   <table class="data-table table stripe hover nowrap" id='karaliste_sms_tablo' style="width: 100%">
                     <thead>
                       <tr>
                          <th>Müşteri</th>
                         <th>Telefon </th>
                         <th>Eklenme Tarihi</th>
                         <th>İşlemler</th>
                       </tr>
                     </thead>
                     <tbody>
                       
                     </tbody>
                   </table>
                 </div>
               </div>
              </div>
            

             </div>

           </div>
         </div>
       </div>
     </div>
   </div>
</div>{{-- /rc-sm-page --}}

 <!-- SMS rapor detay modal -->
 <style>
   #sms_rapor_detay_modal { display: none; }
   #sms_rapor_detay_modal.show { display: flex !important; align-items: center; justify-content: center; }
   #sms_rapor_detay_modal .modal-dialog {
     max-width: 1100px !important;
     width: 95% !important;
     margin: 0 auto !important;
     pointer-events: auto;
   }
   #sms_rapor_detay_modal .modal-content {
     width: 100%;
     border-radius: 8px;
     box-shadow: 0 10px 40px rgba(0,0,0,0.25);
   }
   #sms_rapor_detay_modal .modal-body { padding: 20px 24px; }
   #sms_rapor_detay_tablo { width: 100%; margin-bottom: 0; }
   #sms_rapor_detay_tablo thead th {
     background-color: #000 !important;
     color: #fff !important;
     border-color: #000 !important;
     font-weight: 600;
     padding: 12px 14px;
     white-space: nowrap;
   }
   #sms_rapor_detay_tablo tbody td {
     padding: 10px 14px;
     vertical-align: middle;
   }
 </style>
 <div id="sms_rapor_detay_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">SMS Gönderim Detayı</h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body" style="max-height:75vh;overflow-y:auto;">
         <div id="sms_rapor_detay_yukleniyor" class="text-center py-4" style="display:none;">
           <div class="spinner-border text-primary" role="status"><span class="sr-only">Yükleniyor...</span></div>
           <p class="mt-2 mb-0 text-muted">Alıcılar getiriliyor...</p>
         </div>
         <div id="sms_rapor_detay_hata" class="alert alert-warning" style="display:none;"></div>
         <table class="table table-striped" id="sms_rapor_detay_tablo" style="display:none;">
           <thead>
             <tr>
               <th>Müşteri</th>
               <th>Telefon</th>
               <th>Operatör</th>
               <th>Durum</th>
               <th>İletim Tarihi</th>
             </tr>
           </thead>
           <tbody></tbody>
         </table>
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
       </div>
     </div>
   </div>
 </div>

 <!--karaliste  ekle -->
      <div
         id="kara_liste_olustur_modal"
         class="modal modal-top fade calendar-modal"
         
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;width: 850px;">
               <form id="karaliste_sms_formu" name="karaliste_sms"  method="POST">
                <input type="hidden" value="{{$isletme->id}}" name="sube">
                <input type="hidden" value="1" name="karaliste">
                {{csrf_field()}}
                  <div class="modal-header">
                     <h2>Kara Listeye Ekle</h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        
                        <div class="col-sm-12 col-md-12">
                         
                            <label>Engellemek İstediğiniz Numarayı Girin</label>
                          <select class="form-control custom-select2 musteri_secimi" name="user_id" style="width:100%">
                            
                           </select>
                       
                          
                        </div>
          
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit"
                               class="btn btn-dark  btn-lg btn-block" > <i class="icon-copy dw dw-add"></i>
                           Ekle</button>
                        </div>
                        <div class="col-md-6">
                           <button 
                              type="button"
                              class="btn btn-danger btn-lg btn-block modal_kapat"
                              data-dismiss="modal"
                              > <i class="fa fa-times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
            </div>
            </form>
         </div>
      </div>
      </div>
 
<!--Şablon ekle -->
      <div
         id="sablon_olustur_modal"
         class="modal modal-top fade calendar-modal"
      
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="height: 90%; width: 100%;">
               <form id="sablon_formu"  method="POST">
                 {{ csrf_field() }}
                <input type="hidden" name="sube" value="{{$isletme->id}}">
                <input type="hidden" name="sablonn_id" id='sablon'>
                  <div class="modal-header">
                     <h2>Yeni Şablon</h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        
                        <div class="col-md-12">
                    
                             <input
                              class="form-control form-group" id="sablon_adi" name='sablon_adi'
                              placeholder="Şablon Adı"
                              maxlength="20"
                              type="text"
                              />
                 
                          
                        </div>
                        <div class="col-md-12">
                         
                            <textarea style="height: 230px" onchange="countChar(this,event)" onkeyup="countChar(this,event)" onkeydown="countChar(this,event)" class="form-control form-group" name="sablonsmsmesaj" id="sablonsmsmesaj" placeholder="Mesaj İçeriği"></textarea>
                      
                        </div>

                    
           
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="smstaslakolarakkaydet"  class="btn btn-success btn-lg btn-block">Kaydet</button>
                        </div>
                        <div class="col-md-6">
                           <button 
                              type="button"
                              id="sablonkapatmodal"
                              class="btn btn-danger btn-lg btn-block modal_kapat"
                              data-dismiss="modal"
                              > <i class="fa fa-times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
            
            </form>
         </div>
      </div>
      </div>


<script>
(function(){
    var TopluSMSSecici = {
        config: {
            container: '#musteriListesiTopluSMS',
            aramaInput: '#musteriarama_toplusms',
            sayac: '#topluSMSSeciliMusteriler',
            tumuSecBtn: '#topluSMSTumMusterileriSec',
            tumuKaldirBtn: '#topluSMSTumMusterileriKaldir',
            yukleniyor: '#topluSMSYukleniyor',
            ilkMesaj: '#topluSMSIlkMesaj',
            ajaxUrl: '/isletmeyonetim/musteriportfoydropliste'
        },
        state: {
            seciliIdler: new Set(),
            hepsiSecili: false,
            toplamMusteriler: 0,
            currentPage: 1,
            perPage: 200,
            aramaTerimi: '',
            isLoading: false,
            isFirstLoad: true,
            hasMore: true,
            baslatildi: false
        },
        escapeHtml: function(text){
            var d = document.createElement('div');
            d.textContent = text == null ? '' : text;
            return d.innerHTML;
        },
        init: function(){
            if (this.state.baslatildi) return;
            this.state.baslatildi = true;
            this.bindEvents();
            this.musterileriGetir(1, false);
        },
        bindEvents: function(){
            var self = this;
            var searchTimeout;
            $(self.config.aramaInput).on('input', function(){
                clearTimeout(searchTimeout);
                var term = $(this).val().trim();
                searchTimeout = setTimeout(function(){
                    self.state.aramaTerimi = term;
                    self.state.currentPage = 1;
                    self.state.hasMore = true;
                    self.musterileriGetir(1, false);
                }, 400);
            });
            $(self.config.container).on('scroll', function(){
                var el = this;
                if (self.state.isLoading || !self.state.hasMore) return;
                if (el.scrollTop + el.clientHeight >= el.scrollHeight - 120) {
                    self.musterileriGetir(self.state.currentPage, true);
                }
            });
            $(self.config.container).on('change', '.toplu-musteri-cb', function(){
                var id = String($(this).val());
                if (this.checked) {
                    self.state.seciliIdler.add(id);
                } else {
                    self.state.seciliIdler.delete(id);
                    if (self.state.hepsiSecili) {
                        self.state.hepsiSecili = false;
                    }
                }
                self.sayaciGuncelle();
            });
            $(self.config.tumuSecBtn).on('click', function(){ self.tumunuSec(); });
            $(self.config.tumuKaldirBtn).on('click', function(){ self.tumunuKaldir(); });
        },
        musterileriGetir: function(page, append){
            var self = this;
            if (self.state.isLoading) return;
            self.state.isLoading = true;
            if (!append) {
                $(self.config.yukleniyor).show();
            } else {
                $(self.config.container).append('<div class="text-center py-2" id="topluSMSMiniLoading"><div class="spinner-border spinner-border-sm text-secondary"></div></div>');
            }
            $.ajax({
                url: self.config.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    page: page,
                    perPage: self.state.perPage,
                    filtre: 0,
                    search: self.state.aramaTerimi,
                    salonId: $('#sablonsmsform input[name="sube"]').val() || $('input[name="sube"]').first().val(),
                    _token: $('input[name="_token"]').val()
                },
                success: function(res){
                    var customers = res.customers || [];
                    self.state.toplamMusteriler = res.total || 0;
                    self.state.currentPage = page + 1;
                    self.state.hasMore = customers.length >= self.state.perPage;
                    self.render(customers, append);
                },
                error: function(){
                    if (!append) {
                        $(self.config.container).html('<div class="text-center py-4 text-danger"><i class="fa fa-exclamation-triangle"></i> Müşteriler yüklenemedi.</div>');
                    }
                },
                complete: function(){
                    self.state.isLoading = false;
                    $(self.config.yukleniyor).hide();
                    $('#topluSMSMiniLoading').remove();
                }
            });
        },
        render: function(customers, append){
            var self = this;
            var $list = $(self.config.container);
            if (!append) {
                $list.empty();
                if (customers.length === 0) {
                    $list.html('<div class="text-center py-4 text-muted"><i class="fa fa-search fa-2x mb-2"></i><p class="mb-0">Müşteri bulunamadı.</p></div>');
                    self.sayaciGuncelle();
                    return;
                }
            }
            var html = '';
            customers.forEach(function(c){
                var id = String(c.id);
                var checked = (self.state.hepsiSecili || self.state.seciliIdler.has(id)) ? 'checked' : '';
                var ad = self.escapeHtml(c.name || c.ad || '(İsimsiz)');
                html += '<label class="d-flex align-items-center mb-0" style="padding:8px 12px;border-bottom:1px solid #f0f0f0;cursor:pointer;margin:0;">'
                     + '<input type="checkbox" class="toplu-musteri-cb" value="' + id + '" ' + checked + ' style="margin-right:10px;">'
                     + '<span>' + ad + '</span>'
                     + '</label>';
            });
            $list.append(html);
            self.sayaciGuncelle();
        },
        sayaciGuncelle: function(){
            var sayi = this.state.hepsiSecili ? this.state.toplamMusteriler : this.state.seciliIdler.size;
            $(this.config.sayac).text(sayi + ' müşteri seçildi');
        },
        tumunuSec: function(){
            var self = this;
            self.state.hepsiSecili = true;
            $.ajax({
                url: self.config.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    page: 1,
                    perPage: 1000000,
                    filtre: 0,
                    search: self.state.aramaTerimi,
                    salonId: $('#sablonsmsform input[name="sube"]').val() || $('input[name="sube"]').first().val(),
                    _token: $('input[name="_token"]').val()
                },
                beforeSend: function(){ $(self.config.yukleniyor).show(); },
                success: function(res){
                    self.state.seciliIdler = new Set((res.musteriIdler || []).map(String));
                    $(self.config.container + ' .toplu-musteri-cb').prop('checked', true);
                    self.sayaciGuncelle();
                },
                complete: function(){ $(self.config.yukleniyor).hide(); }
            });
        },
        tumunuKaldir: function(){
            this.state.hepsiSecili = false;
            this.state.seciliIdler.clear();
            $(this.config.container + ' .toplu-musteri-cb').prop('checked', false);
            this.sayaciGuncelle();
        },
        getSeciliIdler: function(){
            return Array.from(this.state.seciliIdler);
        },
        sifirla: function(){
            this.tumunuKaldir();
        }
    };

    function gonderimiBaslat(){
        var mesaj = $('#smsmesaj').val().trim();
        var idler = TopluSMSSecici.getSeciliIdler();
        if (!mesaj || idler.length === 0) {
            swal({
                type: 'warning',
                title: 'Uyarı',
                text: 'Lütfen alıcıları seçip mesajınızı yazınız!',
                showCloseButton: false,
                showCancelButton: false,
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }
        var payload = {
            _token: $('input[name="_token"]').val(),
            sube: $('#sablonsmsform input[name="sube"]').val(),
            toplu_id: $('#smstopluid').val(),
            sablon_baslik: $('#sablon_baslik').val(),
            smsmesaj: mesaj,
            musteri_idler: JSON.stringify(idler)
        };
        $.ajax({
            type: 'POST',
            url: '/isletmeyonetim/toplusmsgonder',
            dataType: 'json',
            data: payload,
            beforeSend: function(){ $('#preloader').show(); },
            success: function(result){
                swal({
                    type: result.status,
                    title: result.title,
                    text: result.text,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000
                });
                $('#preloader').hide();
                if (result.status === 'success') {
                    $('#smsmesaj').val('');
                    $('#sablon_baslik').val('');
                    TopluSMSSecici.sifirla();
                }
            },
            error: function(request){
                $('#preloader').hide();
                var hata = document.getElementById('hata');
                if (hata) hata.innerHTML = request.responseText;
            }
        });
    }

    $(document).ready(function(){
        $('a[href="#sablon_ayarlari"], button[href="#sablon_ayarlari"]').on('shown.bs.tab click', function(){
            TopluSMSSecici.init();
        });

        $('#topluSmsGonderButon').on('click', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            gonderimiBaslat();
        });

        $('#musteriarama_toplusms').on('keydown', function(e){
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
            }
        });

        $('#sablonsmsform').on('submit', function(e){
            e.preventDefault();
            return false;
        });

        $(document).on('click', '.sms-rapor-detay-btn', function(e){
            e.preventDefault();
            var pkgId = $(this).attr('data-pkg-id');
            if (!pkgId) {
                return;
            }
            var $modal = $('#sms_rapor_detay_modal');
            var $loading = $('#sms_rapor_detay_yukleniyor');
            var $hata = $('#sms_rapor_detay_hata');
            var $tablo = $('#sms_rapor_detay_tablo');
            var $tbody = $tablo.find('tbody');

            $tbody.empty();
            $tablo.hide();
            $hata.hide().text('');
            $loading.show();
            $modal.modal('show');

            $.ajax({
                type: 'POST',
                url: '/isletmeyonetim/sms-rapor-detay',
                dataType: 'json',
                data: {
                    _token: $('input[name="_token"]').val(),
                    sube: $('#sablonsmsform input[name="sube"]').val() || $('input[name="sube"]').first().val(),
                    pkg_id: pkgId
                },
                success: function(res){
                    $loading.hide();
                    if (!res.basarili) {
                        $hata.text(res.mesaj || 'Detay alınamadı').show();
                        return;
                    }
                    if (!res.kayitlar || res.kayitlar.length === 0) {
                        $hata.text('Bu paket için alıcı kaydı bulunamadı.').show();
                        return;
                    }
                    var escape = function(t){ var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; };
                    var html = '';
                    res.kayitlar.forEach(function(k){
                        html += '<tr>'
                             + '<td>' + (k.ad ? escape(k.ad) : '<span class="text-muted">Kayıtlı Değil</span>') + '</td>'
                             + '<td>' + escape(k.telefon) + '</td>'
                             + '<td>' + escape(k.operator) + '</td>'
                             + '<td>' + escape(k.durum) + '</td>'
                             + '<td>' + escape(k.iletim_tarihi) + '</td>'
                             + '</tr>';
                    });
                    $tbody.html(html);
                    $tablo.show();
                },
                error: function(){
                    $loading.hide();
                    $hata.text('Sunucuya ulaşılamadı, lütfen tekrar deneyin.').show();
                }
            });
        });
    });

    window.TopluSMSSecici = TopluSMSSecici;
})();
</script>

<style>
/* =================================================================
   SMS YÖNETİMİ — MODERN RESPONSIVE SKIN
   Markaya uygun mor (#5C008E / #9D5DC8 / #d946ef)
   ================================================================= */
.rc-sm-page {
   --rc-purple-dark: #5C008E;
   --rc-purple: #9D5DC8;
   --rc-purple-light: #f5eefe;
   --rc-purple-soft: #ead4ff;
   --rc-success: #16a34a;
   --rc-warning: #f59e0b;
   --rc-danger: #dc2626;
   --rc-info: #2563eb;
   --rc-text: #1f2937;
   --rc-text-soft: #6b7280;
   --rc-border: #eef0f4;
}

/* === HEADER === */
.rc-sm-header {
   display: flex; align-items: center; justify-content: space-between;
   gap: 16px; flex-wrap: wrap; padding: 18px 22px; margin-bottom: 18px;
   background: #fff; border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 4px 16px rgba(92,0,142,.04);
}
.rc-sm-title-row { display: flex; align-items: center; gap: 14px; }
.rc-sm-icon-bubble {
   width: 46px; height: 46px; border-radius: 12px;
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%);
   color: #fff; display: inline-flex; align-items: center; justify-content: center;
   font-size: 18px; box-shadow: 0 6px 18px rgba(92,0,142,.25); flex-shrink: 0;
}
.rc-sm-title { margin: 0; font-size: 19px; font-weight: 700; color: var(--rc-text); line-height: 1.2; }
.rc-sm-breadcrumb { margin-top: 4px; font-size: 12.5px; color: var(--rc-text-soft); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.rc-sm-breadcrumb a { color: var(--rc-text-soft); text-decoration: none; transition: color .15s; }
.rc-sm-breadcrumb a:hover { color: var(--rc-purple-dark); }
.rc-sm-breadcrumb .rc-sm-sep { color: #cbd5e1; }
.rc-sm-breadcrumb .rc-sm-active { color: var(--rc-purple-dark); font-weight: 600; }

/* === ANA KART KONTEYNER === */
.rc-sm-page .pd-20.card-box {
   border-radius: 16px !important;
   border: 1px solid var(--rc-border) !important;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 6px 24px rgba(92,0,142,.05) !important;
}

/* === YASAL UYARI === */
.rc-sm-page .alert-warning {
   background: #fffbeb !important; border: 1px solid #fde68a !important;
   border-left: 4px solid var(--rc-warning) !important; border-radius: 12px !important;
   color: #92400e !important; font-size: 12.5px; line-height: 1.55;
}
.rc-sm-page .alert-warning img { max-width: 34px; margin-right: 8px; vertical-align: top; }

/* === SEKMELER (her iki seviye) === */
.rc-sm-page .nav-tabs.element {
   border: none !important; height: auto !important; overflow-x: auto !important;
   overflow-y: hidden; display: flex; flex-wrap: wrap; gap: 8px;
   padding: 8px !important; background: #fff; border-radius: 14px;
   box-shadow: 0 1px 3px rgba(17,24,39,.04), 0 4px 16px rgba(92,0,142,.04);
}
.rc-sm-page .nav-tabs.element .nav-item { margin: 0 !important; }
.rc-sm-page .nav-tabs.element .nav-item > button,
.rc-sm-page .nav-tabs.element .nav-item > a {
   width: auto !important; height: auto !important; min-height: 40px;
   margin: 0 !important; padding: 9px 16px !important; border-radius: 10px !important;
   border: 1px solid transparent !important; background: transparent !important;
   color: var(--rc-text-soft) !important; font-size: 13px !important; font-weight: 600 !important;
   white-space: nowrap; transition: background .15s, color .15s, box-shadow .15s; box-shadow: none !important;
}
.rc-sm-page .nav-tabs.element .nav-item > button:hover,
.rc-sm-page .nav-tabs.element .nav-item > a:hover {
   background: var(--rc-purple-light) !important; color: var(--rc-purple-dark) !important;
}
.rc-sm-page .nav-tabs.element .nav-item > button.active,
.rc-sm-page .nav-tabs.element .nav-item > a.active {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important; border-color: transparent !important;
   box-shadow: 0 4px 12px rgba(92,0,142,.25) !important;
}

/* İç sekme paneli üst başlık + buton barı */
.rc-sm-page h2.text-blue { color: var(--rc-purple-dark) !important; font-size: 16px; font-weight: 700; }

/* === DATATABLES SKIN === */
.rc-sm-page .dataTables_wrapper { padding: 6px 2px 0; overflow-x: auto; }
.rc-sm-page .dataTables_filter input {
   border: 1px solid var(--rc-border) !important; border-radius: 999px !important;
   padding: 9px 18px !important; margin-left: 8px; font-size: 13px; max-width: 100%;
   background: #fff !important; outline: none; transition: border-color .15s, box-shadow .15s;
}
.rc-sm-page .dataTables_filter input:focus { border-color: var(--rc-purple) !important; box-shadow: 0 0 0 4px rgba(157,93,200,.12); }
.rc-sm-page .dataTables_length select {
   border: 1px solid var(--rc-border) !important; border-radius: 8px !important;
   padding: 4px 8px !important; font-size: 13px; margin: 0 6px; background: #fff !important;
}
.rc-sm-page .dataTables_length label, .rc-sm-page .dataTables_filter label,
.rc-sm-page .dataTables_info { color: var(--rc-text-soft); font-size: 12.5px; }
.rc-sm-page .dataTables_paginate .paginate_button {
   border-radius: 8px !important; padding: 6px 12px !important; margin: 0 2px !important;
   border: 1px solid var(--rc-border) !important; color: var(--rc-text-soft) !important;
   background: #fff !important; font-size: 13px !important;
}
.rc-sm-page .dataTables_paginate .paginate_button:hover {
   background: var(--rc-purple-light) !important; color: var(--rc-purple-dark) !important; border-color: var(--rc-purple-soft) !important;
}
.rc-sm-page .dataTables_paginate .paginate_button.current,
.rc-sm-page .dataTables_paginate .paginate_button.current:hover {
   background: linear-gradient(135deg, var(--rc-purple-dark) 0%, var(--rc-purple) 100%) !important;
   color: #fff !important; border-color: transparent !important; box-shadow: 0 4px 10px rgba(92,0,142,.25) !important;
}

/* DataTables responsive plugin'i devre disi (kendi yiginimizi kullaniyoruz) */
.rc-sm-page table.data-table td.dtr-control,
.rc-sm-page table.data-table th.dtr-control { display: none !important; }
.rc-sm-page table.data-table tr.child { display: none !important; }
.rc-sm-page table.data-table td.dtr-hidden,
.rc-sm-page table.data-table th.dtr-hidden,
.rc-sm-page table.data-table td.none,
.rc-sm-page table.data-table th.none { display: table-cell !important; }

/* === MODERN TABLO === */
.rc-sm-page table.data-table {
   border-collapse: separate !important; border-spacing: 0 !important; width: 100% !important;
}
.rc-sm-page table.data-table thead th {
   background: var(--rc-purple-light) !important; color: var(--rc-purple-dark) !important;
   font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
   padding: 12px 14px !important; text-align: left; border: none !important;
   border-bottom: 2px solid var(--rc-purple-soft) !important; white-space: nowrap; vertical-align: middle;
}
.rc-sm-page table.data-table thead th:first-child { border-top-left-radius: 12px; }
.rc-sm-page table.data-table thead th:last-child { border-top-right-radius: 12px; }
.rc-sm-page table.data-table tbody td {
   padding: 12px 14px !important; font-size: 13px; color: var(--rc-text);
   border: none !important; border-bottom: 1px solid var(--rc-border) !important; vertical-align: middle !important; line-height: 1.45;
}
.rc-sm-page table.data-table tbody tr:last-child td { border-bottom: none !important; }
.rc-sm-page table.data-table tbody tr:nth-child(even) { background: #fcfbfe; }
.rc-sm-page table.data-table tbody tr:hover { background: #f7f1fd; }
/* Detay/işlem butonları kompakt */
.rc-sm-page table.data-table tbody td .btn {
   border-radius: 8px !important; padding: 5px 12px !important; font-size: 12px !important; font-weight: 600 !important;
}

/* === SMS AYARLARI KARTLARI === */
.rc-sm-page #sms_ayarlari .card-box {
   border-radius: 12px !important; border: 1px solid var(--rc-border) !important;
   box-shadow: 0 1px 3px rgba(17,24,39,.03) !important; transition: box-shadow .15s, border-color .15s;
}
.rc-sm-page #sms_ayarlari .card-box:hover { box-shadow: 0 6px 18px rgba(92,0,142,.08) !important; border-color: var(--rc-purple-soft) !important; }
.rc-sm-page #sms_ayarlari h6 { font-weight: 700; color: var(--rc-purple-dark); font-size: 13.5px; }
.rc-sm-page #sms_ayarlari .card-box p { color: var(--rc-text-soft); font-size: 12px; }

/* === ŞABLON CHIP'LERİ === */
.rc-sm-page .smstaslaklari p:first-child {
   background: var(--rc-purple-light) !important; border: 1px solid var(--rc-purple-soft) !important;
   color: var(--rc-purple-dark) !important; font-size: 14px !important; padding: 6px 10px;
}
.rc-sm-page .smstaslaklari:hover p { filter: brightness(.99); }

/* === MÜŞTERİ SEÇİM KUTUSU === */
.rc-sm-page #musteriListesiTopluSMS { border-radius: 10px !important; border-color: var(--rc-border) !important; }

/* === RESPONSIVE: TABLET (≤1024px) === */
@media (max-width: 1024px) {
   .rc-sm-header { padding: 14px 16px; }
   .rc-sm-icon-bubble { width: 40px; height: 40px; font-size: 16px; }
   .rc-sm-title { font-size: 17px; }
   .rc-sm-page .nav-tabs.element { flex-wrap: nowrap; }
}

/* === RESPONSIVE: TABLET PORTRAIT + MOBILE (≤900px) === */
@media (max-width: 900px) {
   .rc-sm-header { padding: 12px 14px; border-radius: 12px; }
   .rc-sm-title { font-size: 16px; }
   .rc-sm-page .pd-20.card-box { padding: 14px !important; }

   /* Tablolari blok-yigin yap */
   .rc-sm-page table.data-table { display: block !important; }
   .rc-sm-page table.data-table thead { display: none !important; }
   .rc-sm-page table.data-table tbody { display: block !important; }
   .rc-sm-page table.data-table tbody tr {
      display: block !important; background: #fff !important;
      border: 1px solid var(--rc-border) !important; border-radius: 12px !important;
      box-shadow: 0 2px 8px rgba(92,0,142,.05); padding: 4px 2px; margin-bottom: 12px;
   }
   .rc-sm-page table.data-table tbody tr:nth-child(even) { background: #fff !important; }
   .rc-sm-page table.data-table tbody td {
      display: flex !important; justify-content: space-between; align-items: center; gap: 14px;
      padding: 8px 14px !important; border: none !important; border-bottom: 1px solid #f4f0fa !important;
      text-align: right; white-space: normal !important;
   }
   .rc-sm-page table.data-table tbody tr td:last-child { border-bottom: none !important; }
   .rc-sm-page table.data-table tbody td::before {
      content: attr(data-label); flex: 0 0 auto; margin-right: auto;
      font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
      color: var(--rc-purple-dark); text-align: left;
   }
   .rc-sm-page table.data-table tbody td:empty { display: none !important; }
}

/* === RESPONSIVE: KÜÇÜK MOBILE (≤420px) === */
@media (max-width: 420px) {
   .rc-sm-title-row { gap: 10px; }
   .rc-sm-icon-bubble { width: 36px; height: 36px; font-size: 14px; }
}
</style>

<script>
/* SMS Yönetimi tablolari: responsive plugin'in eklediği sınıfları geri al +
   mobil yığın için her hücreye sütun başlığına göre data-label ekle.
   draw.dt'de tekrar uygulanır (DataTables redraw sonrası). */
(function(){
   var TABLES = [
      '#bildirim_sms_raporlari', '#toplu_sms_raporlari', '#grup_sms_raporlari',
      '#filtreli_sms_raporlari', '#kampanya_sms_raporlari', '#karaliste_sms_tablo'
   ];
   function applyLabels(sel){
      var $t = $(sel);
      if(!$t.length) return;
      var heads = [];
      $t.find('thead th').each(function(){ heads.push($(this).text().trim()); });
      $t.find('tbody tr').each(function(){
         $(this).find('> td').each(function(i){ $(this).attr('data-label', heads[i] || ''); });
      });
   }
   TABLES.forEach(function(sel){
      $(document).on('init.dt', sel, function(){
         var $t = $(sel);
         $t.removeClass('dtr-inline collapsed');
         setTimeout(function(){
            $t.find('td.dtr-control, th.dtr-control').remove();
            $t.find('td, th').removeClass('dtr-hidden none');
            $t.find('tr.child').remove();
            applyLabels(sel);
         }, 40);
      });
      $(document).on('draw.dt', sel, function(){ setTimeout(function(){ applyLabels(sel); }, 60); });
   });
   $(document).ready(function(){ setTimeout(function(){ TABLES.forEach(applyLabels); }, 400); });
})();
</script>

@endsection