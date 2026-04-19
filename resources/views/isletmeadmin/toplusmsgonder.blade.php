@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-6">
            <div class="title">
               <h1 style="font-size:20px">SMS Yönetimi</h1>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  </li>
                  
                  <li class="breadcrumb-item active" aria-current="page">
                     SMS Yönetimi
                  </li>
               </ol>
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
                  <button href="#filtreli_sms"
                  class="btn btn-outline-primary active"
                  data-toggle='tab'
                  role="tab"
                   style="width: 150px;height: 60px;"
                  aria-selected="true"
                  > Filtreli SMS Gönder </button>
                </li>
                 <li class="nav-item">
                  <button href="#sablon_ayarlari"
                  class="btn btn-outline-primary "
                  data-toggle='tab'
                  role="tab"
                   style="width: 200px;height: 60px;margin-left: 10px;" 
                
                  aria-selected="false" 
                  >Şablon Ayarları  ve Toplu SMS</button>
                </li>
                 <li class="nav-item">
                  <button href="#sms_raporlari"
                  class="btn btn-outline-primary "
                  data-toggle='tab'
                  style="margin-left: 10px; width: 130px;height: 60px" 
                  role="tab"
                  aria-selected="false" 
                  > SMS Raporları </button>
                </li>
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
               <div class="tab-pane fade show active" id="filtreli_sms" role="tabpanel">
                 <div class="pd-20">
                  <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                    <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Filtreli Sms Gönder</h2>
                   </div>
                  </div>
                  <form id="filtrelismsform"  method="POST">
                     {{csrf_field()}}
                <input type="hidden" name="filtreli_id" id="smsfiltreliid">
                 <div class="row" data-value="0">
                    <div class="col-md-6">
                      <div class="col-md-12">
                      
                            <label>Cinsiyet</label>
                            <select name="filtre_cinsiyet" id="filtre_cinsiyet" class="form-control">
                              <option  selected="">Yok</option>
                              <option  value="0">Kadın</option>
                              <option value="1">Erkek</option>
                            </select>
                     
                      </div>
                      <div class="col-md-12">
                       
                            <label>Şablon Seçiniz</label>
                          <select class="form-control" id="filtre_sablon_sec">
                            <option value="">Seçiniz</option> 
                            @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                            <option value="{{$sablon->taslak_icerik}}">{{$sablon->baslik}}</option>
                            @endforeach
                          </select>
                         
                      </div>
                      <div class="col-md-12">
                          
                            <label>Mesaj İçeriği</label>
                           <textarea class="form-control" style="height: 170px;" id="filtre_sms" name="filtre_sms"></textarea>
                          
                      </div>
                      <div class="col-md-12">
                          
                          <button type="button" id="filtrelismsgonder" class="btn btn-success">SMS Gönder</button>
                          
                       
                      </div>
                    </div>
                    <div class="col-md-6">
                          <div class="col-md-12">
        
                            <label>Hizmet</label>
                                <select name="filtrehizmetsecim" id="filtrehizmetsecim" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->get() as $hizmetliste)
                                    <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                    @endforeach
                                </select>
                       
                          </div>
                          <div class="col-sm-12">
                              <div class="container">
                                  <label>Müşterileri Seçiniz</label>
                                  <div class="row" id="arama_musteri_liste_filtreliSMS" style="margin-bottom: 40px;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                          <input type="text" id="musteriarama_filtrelisms" name="musteriarama_filtrelisms" class="form-control" placeholder="Müşteri arayın...">

                                        </div>
                                    </div>
                                    <div class="col-md-3"><button id="filtreliSMSTumMusterileriSec" type="button" class="btn btn-info btn-block">Tümünü Seç</button></div>
                                    <div class="col-md-3"> <button id="filtreliSMSTumMusterileriKaldir" type="button" class="btn btn-info btn-block">Tümünü Kaldır</button></div>
                                    <div class="col-md-12">
                                      <div id="musteriListesiFiltreliSMS" style="width:100%;border:1px solid #e2e2e2;border-radius: 5px;height: 200px;overflow-y: scroll;">
            
                                      </div>
                                      <div class="loading" style="display: none;">Yükleniyor...</div>
                                      <div id="filtreliSMSSeciliMusteriler" style="margin-top: 20px; font-weight: bold;">
                                           0 müşteri seçildi
                                      </div>
        
   
      

                                    </div>

                                  </div>
                              </div>
                            
                          </div>
                      </div>
                  </div>
                  </form>
                 
                 </div>
               </div>
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
                          <button type="button" id="toplusmsgonder" class="btn btn-success">Toplu SMS'i Gönder</button>
                          
                        </div>
                     </div>
                      <div class="col-md-6">
                          <div class="col-sm-12">
                              <div class="container">
                                  <label>Müşterileri Seçiniz</label>
                                  <div class="row" id="arama_musteri_liste_TopluSMS" style="margin-bottom: 40px;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                          <input type="text" id="musteriarama_toplusms" name="musteriarama_toplusms" class="form-control" placeholder="Müşteri arayın...">
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
               <div class="tab-pane fade show" id="sms_raporlari" role="tabpanel">
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

    function bindTopluSmsGonderHandler(){
        $('#toplusmsgonder').off('click').on('click', function(e){
            e.preventDefault();
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
            var formData = $('#sablonsmsform').serializeArray();
            formData.push({ name: 'musteri_idler', value: JSON.stringify(idler) });
            $.ajax({
                type: 'POST',
                url: '/isletmeyonetim/toplusmsgonder',
                dataType: 'json',
                data: formData,
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
        });
    }

    $(document).ready(function(){
        $('a[href="#sablon_ayarlari"], button[href="#sablon_ayarlari"]').on('shown.bs.tab click', function(){
            TopluSMSSecici.init();
        });
        setTimeout(bindTopluSmsGonderHandler, 0);
    });

    window.TopluSMSSecici = TopluSMSSecici;
})();
</script>

@endsection