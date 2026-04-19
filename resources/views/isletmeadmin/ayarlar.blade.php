@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="row clearfix">
   <div class="col-lg-12 col-md-12 col-sm-12 mb-30">
      <div class="pd-20 card-box">
         <div class="tab">
            <div class="row clearfix">
               <div class="col-md-12 col-sm-12">
                   <ul
                     class="nav nav-tabs elementayarlar"
                     role="tablist"
                     >
                     <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='temelbilgiler') ? 'active':''}}"
                           data-toggle="tab"
                           href="#isletme-bilgileri"
                           role="tab"
                           aria-selected="{{($_GET['p']=='temelbilgiler') ? 'true':'false'}}"
                           >İşletme Bilgileri</a>
                     </li>
                     <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='subeler') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#isletme-subeleri"
                           role="tab"
                           aria-selected="{{($_GET['p']=='subeler') ? 'true' : 'false'}}"
                            
                           >Şubeler</a>
                     </li>
                     <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='calismasaatleri') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#calisma-saatleri"
                           role="tab"

                           aria-selected="{{($_GET['p']=='calismasaatleri') ? 'true' : 'false'}}"
                           >Çalışma Saatleri</a>
                     </li>
                     <li class="nav-item" >
                        <a
                           class="nav-link {{($_GET['p']=='personeller') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#personeller"
                           role="tab"
                           aria-selected="{{($_GET['p']=='personeller') ? 'true':'false'}}"
                            
                           >Personeller</a>
                     </li>
                      <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='cihazlar') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#cihazlar"
                           role="tab"
                           aria-selected="{{($_GET['p']=='cihazlar') ? 'true':'false'}}"
                        
                           >Cihazlar</a>
                     </li>
                     <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='hizmetler') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#hizmetler"
                           role="tab"
                           aria-selected="{{($_GET['p']=='hizmetler') ? 'true':'false'}}"
                           
                           >Hizmetler</a>
                     </li>
                    
                     <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='odalar') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#odalar"
                           role="tab"
                           aria-selected="{{($_GET['p']=='odalar') ? 'true':'false'}}"
                          
                           >Odalar</a>
                     </li>
                   
                     <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='randevuayarlari') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#randevu-ayarlari"
                           role="tab"
                           aria-selected="{{($_GET['p']=='randevuayarlari') ? 'true':'false'}}"
                           >Randevu Ayarları</a>
                     </li>
                       <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='musteri_indirimleri') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#musteri_indirimleri"
                           role="tab"
                           aria-selected="{{($_GET['p']=='musteri_indirimleri') ? 'true':'false'}}"
                           
                           >Müşteri İndirimleri</a>
                     </li>
                       <li class="nav-item">
                        <a
                           class="nav-link {{($_GET['p']=='form_taslaklari') ? 'active' : ''}}"
                           data-toggle="tab"
                           href="#form_taslaklari"
                           role="tab"
                           aria-selected="{{($_GET['p']=='form_taslaklari') ? 'true':'false'}}"
                          
                           >Form Taslakları</a>
                     </li>
                  </ul>
               </div>
               <div class="col-md-12 col-sm-12" style="margin-top:32px">
                  <div class="tab-content">
                     <div
                        class="tab-pane fade {{($_GET['p']=='temelbilgiler') ? 'active show' : ''}}"
                        id="isletme-bilgileri"
                        role="tabpanel"
                        >
                        <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                           <div class="col-6 col-xs-6 col-sm-6">
                              <h2 class="text-blue">Temel Ayarlar</h2>
                           </div>
                           <div class="col-6 col-xs-6 col-sm-6 text-right">
                              <button style="max-width: 100%" class="btn btn-primary" type="button" data-toggle="modal" data-target="#qr_kod_modal"><i class="icon-copy fa fa-qrcode" aria-hidden="true"></i> QR Kodu Gör</button>
                           </div>
                        </div>
                        <div class="pd-20">
                           <form id="isletme_temel_bilgiler" method="POST">
                              <input type="hidden" name="sube" value="{{$isletme->id}}">
                              {!! csrf_field() !!}
                              <div class="row" data-value=0>
                                 <div class="col-md-6" data-vale=0>
                                   
                           <div class=" col-md-12">
                                 <div class="form-group">
                                    <label>Logo (maksimum 240px genişliğinde veya 100px yüksekliğine sahip olmalıdır)</label>
                                     <input type="file" id="isletmelogo" name='isletmelogo' style="display:none;" />
                                    <div class="profile-photo">
                                       <a
                                          href="#"
                                          class="edit-avatar" style='background: #fff;' onclick="thisFileUploadLogo();"
                                          ><i class="fa fa-pencil"></i
                                       ></a>
                                       <img
                                          id="profillogo"
                                          src="{{($isletme->logo !== null ? '/'.$isletme->logo : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
                                          alt=""
                                          class="avatar-photo"  style="background: #444;object-fit: cover; width: 240; height: auto;border-radius: 0;"
                                       />
                                    </div>

                                 </div>
                           <div class="form-group">
                           <label>İşletme Adı</label>
                           <input type="text" name="isletme_adi" value="{{$isletme->salon_adi}}" required class="form-control">
                           </div>
                           </div>
                           <div class="col-md-12">
                           <div class="form-group ">
                           <label>İşletme Türü</label>
                           <select class="form-control custom-select2" name="isletme_turu"  style="width:100%">
                           @foreach(\App\SalonTuru::all() as $isletme_turu) 
                           <option value="{{$isletme_turu->id}}" {{($isletme_turu->id == $isletme->salon_turu_id) ? 'selected' : ''}}>{{$isletme_turu->salon_turu_adi}}</option>
                           @endforeach
                           </select>
                           </div>
                           </div>
                           <div class=" col-md-12">
                           <div class="form-group">
                           <label>Adres</label>
                           <input type="text" name="isletme_adres" value="{{$isletme->adres}}" class="form-control">
                           </div>
                           </div>
                           <div class=" col-md-12">
                           <div class="form-group">
                           <label>Telefon</label>
                           <input required data-inputmask =" 'mask' : '5999999999'" type="text" name="isletme_telefon" value="{{$isletme->telefon_1}}" class="form-control">
                           </div>
                           </div>
                           </div>
                           <div class="col-md-6" data-value=0>
                           <div class="row">
                           <div class="col-md-6">
                           <div class="form-group">
                           <label>Online Randevu URL</label>
                           <input
                              type="text"
                              required class="form-control"
                              placeholder="Online randevu link"
                              value="https://{{$isletme->domain}}"
                              id="myInput"
                              />
                           </div>
                           </div>
                           <div class="col-md-3" >
                           <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">kopyala</label>
                           <button class="btn btn-success btn-block" type="button" onclick="myFunction()">Kopyala</button>
                           </div>
                           </div>
                           <div class="col-md-3" >
                           <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">link</label>
                           <a target="_blank" href="https://{{$isletme->domain}}">  <button class="btn btn-primary btn-block" type="button"><i class="bi bi-eye"></i></button></a>
                           </div>
                           </div>
                           </div>
                           <div class="row">
                           <div class="col-md-6">
                           <div class="form-group">
                           <label>Instagram URL</label>
                           <input
                              type="text"
                              class="form-control"
                              placeholder="Instagram Link"
                              value="{{$isletme->instagram_sayfa}}"
                              id="instagram_url" name='instagram_url'
                              />
                           </div>
                           </div>
                           <div class="col-md-3" >
                           <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">kopyala</label>
                           <button class="btn btn-success btn-block"  type="button" onclick="myFunction2()">Kopyala</button>
                           </div>
                           </div>
                           <div class="col-md-3" >
                           <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">link</label>
                           <a target="_blank" href="{{$isletme->instagram_sayfa}}">
                           <button class="btn btn-primary btn-block" type="button"><i class="bi bi-eye"></i></button>
                           </a>
                           </div>
                           </div>
                           </div>
                           <div class="row">
                           <div class="col-md-6">
                           <div class="form-group">
                           <label>Facebook URL</label>
                           <input
                              type="text"
                              class="form-control" name='facebook_url'
                              placeholder="Facebook Link"
                              value="{{$isletme->facebook_sayfa}}"
                              id="facebook_url"
                              />
                           </div>
                           </div>
                           <div class="col-md-3" >
                           <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">kopyala</label>
                           <button class="btn btn-success btn-block"  type="button" onclick="myFunction3()">Kopyala</button>
                           </div>
                           </div>
                           <div class="col-md-3" >
                           <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">link</label>
                           <a target="_blank" href="{{$isletme->facebook_sayfa}}">
                           <button class="btn btn-primary btn-block" type="button"><i class="bi bi-eye"></i></button>
                           </a>
                           </div>
                           </div>
                           </div>
                           <div class="row">
                              <div class="col-md-6">
                                 <label>Whatsapp</label>
                                 <input type="tel" name="whatsapp" class="form-control" data-inputmask =" 'mask' : '5999999999'" value="{{$isletme->whatsapp}}">
                              </div>
                           </div>
                           <div class="row">
                           <div class="col-md-6">
                           <div class="form-group">
                           <label>Uygulama URL</label>
                           <button class="btn btn-primary btn-block"  type="button" ><i class="icon-copy bi bi-apple"></i> IOS</button>
                           </div>
                           </div>
                           <div class="col-md-6" >
                           <label style="visibility: hidden;width: 100%;">android</label>
                           <button class="btn btn-primary btn-block"  type="button" ><i class="icon-copy fi-social-android"></i> Android</button>
                           </div>
                           </div>
                           </div>
                           </div>
                           <script>
                              function myFunction() {
                                var copyText = document.getElementById("myInput");
                                copyText.select();
                                copyText.setSelectionRange(0, 99999);
                                navigator.clipboard.writeText(copyText.value);
                                
                                
                              
                              }
                              function myFunction2(){
                              var copyText = document.getElementById("myInput2");
                                copyText.select();
                                copyText.setSelectionRange(0, 99999);
                                navigator.clipboard.writeText(copyText.value);
                              
                              }
                              function myFunction3(){
                              
                                  var copyText = document.getElementById("myInput3");
                                copyText.select();
                                copyText.setSelectionRange(0, 99999);
                                navigator.clipboard.writeText(copyText.value);
                              }
                              function myFunction4() {
                               
                                  var copyText = document.getElementById("myInput4");
                                copyText.select();
                                copyText.setSelectionRange(0, 99999);
                                navigator.clipboard.writeText(copyText.value);
                              }
                              
                           </script>
                           <h2  class="text-blue">Fatura Ayarları</h2>
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Firma Adı/Ünvanı</label>
                                    <input type="text" class="form-control" name="vergi_adi" value="{{$isletme->vergi_adi}}">
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Vergi Adresi</label>
                                    <input type="text" name="vergi_adresi" class="form-control" value="{{$isletme->vergi_adresi}}">
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Vergi / TC No</label>
                                    <input type="tel" class="form-control" name="vergi_tc_no" value="{{$isletme->vergi_no}}" data-inputmask =" 'mask' : '99999999999'" >
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Vergi Dairesi</label>
                                    <input type="text" name="vergi_dairesi" class="form-control" value="{{$isletme->vergi_dairesi}}">
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>KDV oranı (%)</label>
                                    <input type="tel" name="kdv_orani" class="form-control" value="{{$isletme->kdv_orani}}">
                                 </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-6">
                                 <h2 class="text-blue">SEO Ayarları</h2>
                                 <div class="form-group">
                                    <label>Online Randevu Sayfası Açıklaması (Online randevu sayfanızın arama motorlarında görünmesini istediğiniz açıklama.)</label>
                                    <textarea class="form-control" name="seo_description" placeholder="Ör. Kalıcı makyaj, cilt bakımı, lazer ve güzelliğe dair tüm hizmetler için güzellik merkezimizde hizmetinizde.">{{$isletme->meta_description}}</textarea>
                                 </div>
                                 <div class="form-group">
                                    <label>Online randevu sayfanızın arama motorlarında çıkmasını istediğiniz lokasyon bazlı anahtar kelimeler. Ör. izmirde güzellik merkezi. <b>NOT : (Tüm kelimeler küçük harflerden oluşmalıdır.)</b></label>
                                    <?php $aramaterimisayisi = $aramaterimleri->count(); ?>
                                    @foreach($aramaterimleri as $key => $aramaterimi)
                                       <input type="text" name="anahtar_kelimeler[]" style="text-transform: lowercase;" placeholder="Anahtar Kelime {{$key+1}}" class="form-control" value="{{$aramaterimi->arama_terimi}}"> 
                                    @endforeach
                                    @if($aramaterimleri->count() < 6)
                                       @for($i=$aramaterimleri->count()+1; $i<=6; $i++)
                                          <input type="text" name="anahtar_kelimeler[]" placeholder="Anahtar Kelime {{$i}}" class="form-control"> 
                                       @endfor
                                    @endif

                                 </div>
                                  <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                              </div>
                              <div class="col-md-6">
                                 <h2 class="text-blue">Kapak Resmi</h2>
                                    <div class="profile-photo" style="width: 100%;">
                                       <a
                                          href="#"
                                          class="edit-avatar" onclick="thisFileUpload();" style='background: #fff;'
                                          ><i class="fa fa-pencil"></i
                                          ></a>
                                       @if(\App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli')!= null || \App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli')!= '')
                                       <img
                                          id="profilkapak"
                                          src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli'))}}"
                                          alt=""
                                          class="avatar-photo" style="object-fit: cover; width: 100%; height:auto;border-radius: 0; float: left;margin-bottom: 20px;"
                                          />
                                       @else
                                       <img
                                          id="profilkapak"
                                          src="/public/img/randevumcepte.jpg"
                                          alt=""
                                          class="avatar-photo" style="object-fit: cover; width: 100%; height:auto;border-radius: 0; float:left; margin-bottom:20px"
                                          />
                                       @endif
                                       <div
                                          class="modal fade"
                                          id="modal"
                                          tabindex="-1"
                                          role="dialog"
                                          aria-labelledby="modalLabel"
                                          aria-hidden="true"
                                          >
                                          <div
                                             class="modal-dialog"
                                             role="document"
                                             >
                                             <div class="modal-content">
                                                <div class="modal-body pd-5">
                                                   <div class="img-container">
                                                      @if(\App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli')!= null || \App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli')!= '')
                                                      <img                                       
                                                         src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli'))}}"
                                                         alt="Avatar"
                                                         />
                                                      @else
                                                      <img                                       
                                                         src="/public/isletmeyonetim_assets/img/user-profile-display.png"
                                                         alt="Avatar"
                                                         />
                                                      @endif
                                                      <input type="file" id="isletmekapakfoto" name='isletmekapakfoto' style="display:none;" />
                                                   </div>
                                                </div>
                                                <div class="modal-footer" style="display: block;">
                                                   <div class="row">
                                                      <div class="col-6 col-xs-6 col-sm-6">
                                                         <button id="button" name="button" value="Upload" class="btn btn-primary btn-lg btn-block" onclick="thisFileUpload();"><i class="fa fa-upload"></i> Fotoğraf Yükle</button>
                                                      </div>
                                                      <div class="col-6 col-xs-6 col-sm-6">
                                                         <button type="button" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
                                                         Kapat
                                                         </button>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <button id="crop_modal_ac" type="button"  data-toggle="modal" data-target="#crop_modal" style="display:none"> modal aç</button> <button id="crop_modal_ac2" type="button"  data-toggle="modal" data-target="#crop_modal2" style="display:none"> modal aç</button>                   
                                       <div
                                          class="modal fade"
                                          id="crop_modal"
                                          tabindex="-1"
                                          role="dialog"
                                          aria-labelledby="modalLabel"
                                          aria-hidden="true"
                                          >
                                          <div
                                             class="modal-dialog"
                                             role="document"
                                             >
                                             <div class="modal-content">
                                                <div class="modal-body pd-5">
                                                   <div class="img-container">
                                                      <div class="row">
                                                         <div class="col-md-12">
                                                            <!--  default image where we will set the src via jquery-->
                                                            @if(Auth::guard('satisortakligi')->check())
                                                             <img id="croppedimg" src="{{(Auth::guard('satisortakligi')->user()->profil_resim !== null ? Auth::guard('satisortakligi')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
                                                            @else
                                                            <img id="croppedimg" src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
                                                            @endif
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="modal-footer" style="display: block;">
                                                   <div class="row">
                                                      <div class="col-6 col-xs-6 col-sm-6">
                                                         <button type="button" id="crop" class="btn btn-primary btn-lg btn-block">Kırp</button>
                                                      </div>
                                                      <div class="col-6 col-xs-6 col-sm-6">
                                                         <button type="button" id="crop_modal_kapat" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
                                                         Kapat
                                                         </button>
                                                      </div>
                                                   </div>
                            
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div
                                          class="modal fade"
                                          id="crop_modal2"
                                          tabindex="-1"
                                          role="dialog"
                                          aria-labelledby="modalLabel"
                                          aria-hidden="true"
                                          >
                                          <div
                                             class="modal-dialog"
                                             role="document"
                                             >
                                             <div class="modal-content">
                                                <div class="modal-body pd-5">
                                                   <div class="img-container">
                                                      <div class="row">
                                                         <div class="col-md-12">
                                                            <!--  default image where we will set the src via jquery-->
                                                            @if(Auth::guard('satisortakligi')->check())
                                                              <img id="croppedimg2" src="{{(Auth::guard('satisortakligi')->user()->profil_resim !== null ? Auth::guard('satisortakligi')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
                                                            @else
                                                            <img id="croppedimg2" src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
                                                            @endif
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="modal-footer" style="display: block;">
                                                   <div class="row">
                                                      <div class="col-6 col-xs-6 col-sm-6">
                                                         <button type="button" id="crop2" class="btn btn-primary btn-lg btn-block">Kırp</button>
                                                      </div>
                                                      <div class="col-6 col-xs-6 col-sm-6">
                                                         <button type="button" id="crop_modal_kapat2" class="btn btn-danger btn-lg btn-block" data-dismiss="modal"><i class="fa fa-times"></i>
                                                         Kapat
                                                         </button>
                                                      </div>
                                                   </div>
                            
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-xs-6 col-sm-6">
                                  <h2  class="text-blue" style="margin-bottom:30px">İşletme Görselleri</h2>
                              </div>
                              <div class="col-md-6 col-xs-6">
                                 <div class="single-file-input2">
                                     <input type="file" id="isletmegorselleri" name="isletmegorselleri" multiple="">
                                      <div id="gorseleklemetext" class="btn btn-primary">İşletme Görsellerini Ekleyin (Max:{{12-$salongorselleri->count()}} adet)</div>
                                 </div>
                              </div>
                           </div>
                          
                           <div class="gallery-wrap">
                              <ul class="row"  id='gorselbolumu'>
                                  {!!$gorseller_html!!}
                              </ul>
                           </div>
                          
                           </form>
                        </div>
                     </div>
                     <div
                        class="tab-pane fade {{($_GET['p']=='subeler') ? 'active show' : ''}}"
                        id="isletme-subeleri"
                        role="tabpanel"
                        >
                        <div class="pd-20">
                           <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                              <div class="col-6 col-xs-6 col-sm-6">
                                 <h2 class="text-blue">Şubeler</h2>
                              </div>
                              <div class="col-6 col-xs-6 col-sm-6 text-right">
                                 <button onclick="modalbaslikata('Yeni Şube','yenipersonelbilgiekle')" class="btn btn-success" data-toggle="modal" data-target="#yeni_sube_modal"><i class="fa fa-plus"></i> Yeni Şube</button>
                              </div>
                           </div>
                           <table class="data-table table stripe hover nowrap" id="sube_tablo">
                              <thead>
                                 <tr>
                                    <th>Şube</th>
                                    <th>Adres</th>
                                    <th>Telefon</th>
                                    <th>Üyelik Bitiş Tarihi</th>
                                    <th>İşlemler</th>
                                 </tr>
                              </thead>
                              <tbody class="no-border-x" id="subelistesi">
                                 @foreach($subeler as $sube)
                                 <tr name="subesatir" data-value="{{$sube->id}}">
                                    <td>
                                       {{$sube->salon_adi}}
                                    </td>
                                    <td>
                                       {{$sube->adres}}
                                    </td>
                                    <td>
                                       {{$sube->telefon_1}}
                                    </td>
                                    <td>
                                       
                                       {{date('d.m.Y', strtotime($sube->uyelik_bitis_tarihi))}}
                                    </td>
                                    <td>
                                       <a href='https://{{$_SERVER["HTTP_HOST"]}}/isletmeyonetim?sube={{$sube->id}}' class="btn btn-primary">Geçiş Yap <i class="icon-copy fa fa-share" aria-hidden="true"></i></button>
                                    </td>
                                    <td>
                                       <a title="Şube Bilgi Düzenle" name="subebilgiduzenle" style="font-size: 20px;cursor: pointer;" data-value="{{$sube->id}}" class="icon">
                                          <div class="icon"><span class="mdi mdi-edit"></span></div>
                                          <span class="icon-class"></span>
                        </div>
                        </a>
                        <span  name="subeislembuton" data-value="{{$sube->id}}">
                        @if($sube->aktif)
                        <a title="Şube Pasif Et" name="subepasifet" style="font-size: 20px;cursor: pointer;" data-value="{{$sube->id}}" class="icon"> 
                        <div class="icon"><span class="mdi mdi-minus-circle"></span></div><span class="icon-class"></span>
                     </div>
                     </a>
                     @else
                     <a title="Şube Aktif Et" name="subeaktifet" style="font-size: 20px;cursor: pointer;" data-value="{{$sube->id}}" class="icon">
                     <div class="icon"><span class="mdi mdi-check-circle"></span></div><span class="icon-class"></span>
                  </div>
                  </a>
                  @endif
                  </span>
                  </td>
                  </tr>
                  @endforeach
                  </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='calismasaatleri') ? 'active show' : ''}}"
               id="calisma-saatleri"
               role="tabpanel"
               >
               <form id="calisma_mola_saatleri" method="POST">
                  {{ csrf_field() }}
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <div class="row">
                     <div class="col-md-6">
                        <h2 class="text-blue">
                           Çalışma Saatleri
                        </h2>
                        <table class="table">
                           <tbody>
                              @foreach($saloncalismasaatleri as $key => $value)
                              @if($value->haftanin_gunu == 1)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor == 1)
                                       <input type="checkbox" checked id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                       @else
                                       <input type="checkbox" id="calisiyor1" name="calisiyor1"><label for="calisiyor1">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Pazartesi</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati1" style="float: left;"> 
                                 </td>
                                 <td>  
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati1"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu == 2)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor == 1)
                                       <input type="checkbox" checked="" id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                       @else
                                       <input type="checkbox" id="calisiyor2" name="calisiyor2"><label for="calisiyor2">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Salı</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati2" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati2"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==3)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor==1)
                                       <input type="checkbox" checked id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                       @else
                                       <input type="checkbox" id="calisiyor3" name="calisiyor3"><label for="calisiyor3">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Çarşamba</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati3" style="float: left;">
                                 </td>
                                 <td> 
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati3"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==4)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor==1)
                                       <input type="checkbox" checked id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                                       @else
                                       <input type="checkbox" id="calisiyor4" name="calisiyor4"><label for="calisiyor4">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Perşembe</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati4" style="float: left;">
                                 </td>
                                 <td> 
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati4"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==5)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor==1)
                                       <input type="checkbox" checked id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                       @else
                                       <input type="checkbox" id="calisiyor5" name="calisiyor5"><label for="calisiyor5">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Cuma</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati5" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati5"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==6)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor==1)
                                       <input type="checkbox" checked id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                       @else
                                       <input type="checkbox" id="calisiyor6" name="calisiyor6"><label for="calisiyor6">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Cumartesi</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati6" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati6"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==7)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->calisiyor==1)
                                       <input type="checkbox" checked id="calisiyor7" name="calisiyor7"><label for="calisiyor7">
                                       @else
                                       <input type="checkbox" id="calisiyor7" name="calisiyor7"><label for="calisiyor7">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Pazar</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="baslangicsaati7" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="bitissaati7"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                     <div class="col-md-6">
                        <h2 class="text-blue">Öğle Arası Mola Saatleri</h2>
                        <table class="table">
                           <tbody>
                              @foreach($salonmolasaatleri as $key => $value)
                              @if($value->haftanin_gunu == 1)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var == 1)
                                       <input type="checkbox" checked id="molavar1" name="molavar1"><label for="molavar1">
                                       @else
                                       <input type="checkbox" id="molavar1" name="molavar1"><label for="molavar1">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Pazartesi</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati1" style="float: left;"> 
                                 </td>
                                 <td>  
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati1"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu == 2)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var == 1)
                                       <input type="checkbox" checked="" id="molavar2" name="molavar2"><label for="molavar2">
                                       @else
                                       <input type="checkbox" id="molavar2" name="molavar2"><label for="molavar2">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Salı</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati2" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati2"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==3)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var==1)
                                       <input type="checkbox" checked id="molavar3" name="molavar3"><label for="molavar3">
                                       @else
                                       <input type="checkbox" id="molavar3" name="molavar3"><label for="molavar3">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Çarşamba</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati3" style="float: left;">
                                 </td>
                                 <td> 
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati3"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==4)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var==1)
                                       <input type="checkbox" checked id="molavar4" name="molavar4"><label for="molavar4">
                                       @else
                                       <input type="checkbox" id="molavar4" name="molavar4"><label for="molavar4">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Perşembe</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati4" style="float: left;">
                                 </td>
                                 <td> 
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati4"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==5)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var==1)
                                       <input type="checkbox" checked id="molavar5" name="molavar5"><label for="molavar5">
                                       @else
                                       <input type="checkbox" id="molavar5" name="molavar5"><label for="molavar5">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Cuma</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati5" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati5"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==6)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var==1)
                                       <input type="checkbox" checked id="molavar6" name="molavar6"><label for="molavar6">
                                       @else
                                       <input type="checkbox" id="molavar6" name="molavar6"><label for="molavar6">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Cumartesi</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati6" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati6"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @if($value->haftanin_gunu==7)
                              <tr>
                                 <td>
                                    <div class="be-checkbox be-checkbox-color inline">
                                       @if($value->mola_var==1)
                                       <input type="checkbox" checked id="molavar7" name="molavar7"><label for="molavar7">
                                       @else
                                       <input type="checkbox" id="molavar7" name="molavar7"><label for="molavar7">
                                       @endif
                                       </label>
                                    </div>
                                 </td>
                                 <td>Pazar</td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->baslangic_saati}}" name="molabaslangicsaati7" style="float: left;"> 
                                 </td>
                                 <td>
                                    <input type="time" class="form-control" value="{{$value->bitis_saati}}" name="molabitissaati7"  style="float: left;">
                                 </td>
                              </tr>
                              @endif
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                     <div class="col-md-12">
                        <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                     </div>
                  </div>
               </form>
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='personeller') ? 'active show' : ''}}"
               id="personeller"
               role="tabpanel"
               >
               <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Personeller</h2>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button onclick="modalbaslikata('Yeni Personel','yenipersonelbilgiekle')" class="btn btn-success" data-toggle="modal" data-target="#personel-modal"><i class="fa fa-plus"></i> Yeni Personel</button>
                  </div>
               </div>
               <div class="pd-20">
                  <table class="data-table table stripe hover nowrap" id="personel_tablo">
                     <thead>
                        <tr>
                           <th>Takvim Sırası</th>
                           <th>Personel</th>
                           <th>Hesap Tipi</th>
                           <th>Telefon</th>
                           <th>Durum</th>
                           <th class="datatable-nosort">İşlemler</th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='hizmetler') ? 'active show' : ''}}"
               id="hizmetler"
               role="tabpanel"
               >
               @include('isletmeadmin.partials.hizmet_yonetimi_panel')
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='cihazlar') ? 'active show' : ''}}"
               id="cihazlar"
               role="tabpanel"
               >
               <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Cihazlar</h2>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button onclick="modalbaslikata('Yeni Cihaz','yenicihazbilgiekle')"  class="btn btn-success" data-toggle="modal" data-target="#yeni_cihaz_modal"><i class="fa fa-plus"></i> Yeni Cihaz</button>
                  </div>
               </div>
               <div class="pd-20">
                  <table class="data-table table stripe hover nowrap" id="cihaz_tablo" >
                     <thead>
                        <tr>
                           <th>Takvim Sırası</th>
                           <th>Cihaz Adı</th>
                           <th>Durum</th>
                           <th>Açıklama</th>
                           <th class="datatable-nosort">İşlemler</th>
                        </tr>
                     </thead>
                     <tbody>
                       
                     </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='odalar') ? 'active show' : ''}}"
               id="odalar"
               role="tabpanel"
               >
               <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Odalar</h2>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button  class="btn btn-success" data-toggle="modal" data-target="#yeni_oda_modal"><i class="fa fa-plus"></i> Yeni Oda</button>
                  </div>
               </div>
               <div class="pd-20">
                  <table class="data-table table stripe hover nowrap" id="oda_tablo">
                     <thead>
                        <tr>
                           <th>Takvim Sırası</th>
                           <th>Oda Adı</th>
                           <th>Durum</th>
                           <th>Açıklama</th>
                           <th class="datatable-nosort">İşlemler</th>
                        </tr>
                     </thead>
                     <tbody>
                        
                     </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='randevuayarlari') ? 'active show' : ''}}"
               id="randevu-ayarlari"
               role="tabpanel"
               >
               <div class="pd-20">
                  <form id="randevu_ayarlari" method="POST">
                     {!!csrf_field()!!}
                     <input type="hidden" name="salon_id" value="{{$isletme->id}}">
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Randevu Aralığı</label>
                              <select class="form-control" name="randevu_saat_araligi">
                              <option value="15" {{($isletme->randevu_saat_araligi==15) ? 'selected' : ''}}>15 dakikada bir</option>
                              <option value="30" {{($isletme->randevu_saat_araligi==30) ? 'selected' : ''}}>30 dakikada bir</option>
                              <option value="45" {{($isletme->randevu_saat_araligi==45) ? 'selected' : ''}}>45 dakikada bir</option>
                              <option value="60" {{($isletme->randevu_saat_araligi==60) ? 'selected' : ''}}>60 dakikada bir</option>
                              <option value="90" {{($isletme->randevu_saat_araligi==90) ? 'selected' : ''}}>90 dakikada bir</option>
                              <option value="120" {{($isletme->randevu_saat_araligi==120) ? 'selected' : ''}}>120 dakikada bir</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Takvim Ayarı</label>
                              <select name="randevu_takvim_turu" class="form-control">
                              <option value="1" {{($isletme->randevu_takvim_turu==1) ? 'selected' : ''}} >Personele Göre</option>
                              <option value="0" {{($isletme->randevu_takvim_turu==0) ? 'selected' : ''}} >Hizmet Kategorisine Göre</option>
                              <option value="2" {{($isletme->randevu_takvim_turu==2) ? 'selected' : ''}} >Cihaza Göre</option>
                              <option value="3" {{($isletme->randevu_takvim_turu==3) ? 'selected' : ''}} >Odaya Göre</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
               <div
               class="tab-pane fade {{($_GET['p']=='musteri_indirimleri') ? 'active show' : ''}}"
               id="musteri_indirimleri"
               role="tabpanel"
               >
               <div class="pd-20">
                  <form id="musteriindirimleri" method="POST">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                   <div class="col-md-12">
                       
                     <div class="row">
                        <div class="col-md-6 ">
                           <div class="pd-20 card-box  mb-10 ">

                               <h6 style="text-align: center">Sadık Müşteri İndirimi (%)</h6>
                               <br>
                               <div class="row"  id="indirim_sadik_align">
                                 <div class="col-md-4 col-6 col-sm-6 col-xs-6 " style="text-align: center;">
                                      <p >İndirim Oranı</p>
                                   <input type="tel"  name="sadik_musteri_indirimi" id="sadik_musteri_indirimi" class="form-control" value="{{$isletme->sadik_musteri_indirim_yuzde}} " {{($isletme->sadik_musteri_indirim_yuzde==0) ? 'disabled' : '' }}  >
                                 </div>
                            
                           <div class="col-md-8 col-6 col-xs-6 col-sm-6">
                                <div  id="sadik_acik_kapali" class="custom-control custom-checkbox mb-5 form-group">

                                        <input type="checkbox" class="custom-control-input" {{($isletme->sadik_musteri_indirim_yuzde>0) ? 'checked' : '' }} name="sadik_acikkapali" id="sadik_acikkapali" >
                                        <label class="custom-control-label" for="sadik_acikkapali">Açık / Kapalı</label>
                                    
                                  </div>
                        
                           </div>
                           
                              
                           </div>
                                  
                           </div>
                           
                        </div>
                       
                         <div class="col-md-6 ">
                           <div class="pd-20 card-box  mb-10">
                               <h6 style="text-align: center">Aktif Müşteri İndirimi (%)</h6>
                               <br>
                              <div class="row"  id="indirim_aktif_align">
                                 <div class="col-md-4 col-6 col-sm-6 col-xs-6 " style="text-align: center;" >
                                      <p >İndirim Oranı</p>
                                   <input type="tel"  name="aktif_musteri_indirimi" id="aktif_musteri_indirimi" class="form-control" value="{{$isletme->aktif_musteri_indirim_yuzde}}" {{($isletme->aktif_musteri_indirim_yuzde==0) ? 'disabled' : '' }}>
                                 </div>
                            
                           <div class="col-md-8 col-6 col-xs-6 col-sm-6">
                                <div id="aktif_acik_kapali" class="custom-control custom-checkbox mb-5 form-group">

                                        <input type="checkbox" class="custom-control-input" {{($isletme->aktif_musteri_indirim_yuzde>0) ? 'checked' :  '' }} name="aktif_acikkapali" id="aktif_acikkapali" >
                                        <label class="custom-control-label" for="aktif_acikkapali">Açık / Kapalı</label>
                                    
                                  </div>
                        
                           </div>
                           
                              
                           </div>
                                  
                           </div>
                           
                        </div>
                   
                        <div class="col-md-3" id="musteri_indirim_kaydet"   >
                           <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                        </div>
                     </div>
                   </div>
                  </form>
               </div>
            </div>
                <div
               class="tab-pane fade {{($_GET['p']=='form_taslaklari') ? 'active show' : ''}}"
               id="form_taslaklari"
               role="tabpanel"
               >
               <div class="pd-20">
                  <div class="gallery-wrap">
                     <ul class="row">
                           <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/cilt.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Cilt Üzerinde Kullanılan Lazer Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=7"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/kimyasal.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Kimyasal Peeling Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=1"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/dovme.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Dövme Silme Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=2"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/dermoroller.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Dermoroller Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=5"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/mikro.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                      Mikropigmentasyon Uygulaması Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=3"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/bolgesel.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Bölgesel İncelme Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=6"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/lazer.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Lazer Epilasyon Onam Formu
                                    </h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=4"
                                              
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                            <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/dermoroller.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Gelin Başı Hizmet Sözleşmesi
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=9"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>

                            <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <img src="/public/taslakgorselleri/dermoroller.png" alt="" />
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">
                                       Riskli Saç Sözleşmesi
                                    <ul class="clearfix">
                                       <li>
                                          <a
                                             href="/isletmeyonetim/bosFormIndir?formId=10"
                                             
                                             ><i class="fa fa-download"></i
                                          ></a>
                                       </li>
                                       
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     </ul>
                  </div>
                   
            </div>
            <div
               class="tab-pane fade {{($_GET['p']=='urunler') ? 'active show' : ''}}"
               id="urunler"
               role="tabpanel"
               >
               <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Ürünler</h2>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button data-toggle="modal" data-target="#urun-modal" class="btn btn-success">
                     <i class="fa fa-plus"></i> Yeni Ürün
                     </button>
                  </div>
               </div>
               <div class="pd-20">
                  <table class="data-table table stripe hover nowrap" id="urun_liste">
                     <thead>
                        <tr>
                           <th>Ürün</th>
                           <th>Stok</th>
                           <th>Fiyat</th>
                           <th>Barkod</th>
                           <th>Düşük Stok Sınırı</th>
                           <th class="datatable-nosort">İşlemler</th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade  {{($_GET['p']=='paketler') ? 'active show' : ''}}"
               id="paketler"
               role="tabpanel"
               >
               <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Paketler</h2>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button data-toggle="modal" data-target="#paket-modal" class="btn btn-success">
                     <i class="fa fa-plus"></i> Yeni Paket
                     </button>
                  </div>
               </div>
               <div class="pd-20">
                  <table class="data-table table stripe hover nowrap" id="paket_liste">
                     <thead>
                        <tr>
                           <th>Adet</th>
                           <th>Hizmet(-ler)</th>
                           <th>Seans(-lar)</th>
                           <th>Fiyat (₺)</th>
                           <th class="datatable-nosort">İşlemler</th>
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
<div
   id="qr_kod_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="qr_kod"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="qr_kod" id="qr_kod_id" value="0">
               <h2 class="text-blue h2 mb-10" id="qr_kod">QR Kod</h2>
               <div class="form-group">
                  <div class="card-body" style="margin-left: 10px;">
                     {!! QrCode::size(300)->generate('https://'.$isletme->domain) !!}
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-6">
                     <a class="btn btn-primary btn-block btn-lg" href="{{ URL::to('/isletmeyonetim/qrpdf') }}">İndir</a>
                  </div>
                  <div class="col-md-6">
                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-block btn-lg"
                        data-dismiss="modal"
                        >
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<div
   id="hizmet_secimi_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id='hizmet_ekle_formu' method="POST">
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <div class="modal-header">
               <h2>Hizmet Seçimi</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <div class="row">
                  <div class="col-md-6">  
                     <input type="button" class="btn btn-primary" style="width: 100%" onclick='selects()' value="Hepsini Seç"/>
                  </div>
                  <div class="col-md-6"> 
                     <input type="button" class="btn btn-secondary" style="width: 100%" onclick='deSelect()' value="Hiçbirini Seçme"/> 
                  </div>
               </div>
               <div class="row" style="margin-top:20px">
                  <div class="col-md-6">
                     <div class="form-group">
                        <input
                           type="text"
                           class="form-control search-input"
                           placeholder="Hizmet Ara"
                           id='hizmet_ara'/>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <button class="btn btn-primary" type="button" data-value="0" data-toggle="modal" data-target="#yeni_hizmet_modal" style="width: 100%"><i class="fa fa-plus"></i> Listede olmayan hizmet</button>
                  </div>
                  <div class="col-md-12"  style="overflow-y: auto; max-height: 300px ">
                     <button type="button" style="display:none" id='hizmet_personel_ekle_modal_ac' data-toggle="modal" data-target="#personel_sec_modal" ></button>
                     <table class="table" id="hizmet_sec_tablo">
                        <thead>
                           <tr>
                              <td><input type="checkbox" id='tum_hizmetleri_sec'></td>
                              <td>Hizmet</td>
                           </tr>
                        </thead>
                        <tbody id='secilmeyen_hizmetler_liste'>
                            
                         
                        @foreach(\App\Hizmet_Kategorisi::all() as $hizmet_kategorisi)

@if(\App\Hizmetler::where(
   function($q) use ($isletme, $hizmet_kategorisi)
   { 
      // Ozel hizmet ve salon_id, girilen salon_id'sine eşitse
      $q->where('ozel_hizmet', true);
      $q->where('salon_id', $isletme->id);
      $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));   
   }
)->orWhere(
   function($q) use ($isletme, $hizmet_kategorisi){
      // salon_id null olanlar her zaman görünmeli
      $q->whereNull('salon_id');
      $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));
      $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
      $q->where('id', '!=', 463);
   }
)->orWhere(
   function($q) use ($isletme, $hizmet_kategorisi){
      // Ozel hizmet, salon_id'si girilen salon id'siyle eşleşmiyorsa
      $q->where('ozel_hizmet', true);
      $q->where('salon_id', '!=', $isletme->id);
      $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
      $q->where('id', '!=', 463);
   }
)->select('hizmet_adi')->distinct()->count() > 0)

<tr style="background: #e2e2e2;">
   <td></td>
   <td><strong>{{$hizmet_kategorisi->hizmet_kategorisi_adi}}</strong></td>
</tr>

@foreach(\App\Hizmetler::where(
   function($q) use ($isletme, $hizmet_kategorisi)
   { 
      // Salon hizmetlerini listele
      $q->where('salon_id', $isletme->id);
      $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));   
   }
)->orWhere(
   function($q) use ($isletme, $hizmet_kategorisi){
      // salon_id null olanları her zaman göster
      $q->whereNull('salon_id');
      $q->whereNotIn('id', \App\SalonHizmetler::where('salon_id', $isletme->id)->where('aktif', true)->pluck('hizmet_id'));
      $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
      $q->where('id', '!=', 463);
   }
)->orWhere(
   function($q) use ($isletme, $hizmet_kategorisi){
      // Ozel hizmet, salon_id'si girilen salon id'siyle eşleşenleri getir
      $q->where('ozel_hizmet', true);
      $q->where('salon_id', $isletme->id);
      $q->where('hizmet_kategori_id', $hizmet_kategorisi->id);
      $q->where('id', '!=', 463);
   }
)->select('hizmet_adi', 'id')->distinct()->get() as $secilmeyenhizmetler)

<tr>
   <td><input type="checkbox" name="salon_hizmetleri[]" value="{{$secilmeyenhizmetler->id}}"></td>
   <td>{{$secilmeyenhizmetler->hizmet_adi}}</td>
</tr>

@endforeach
@endif
@endforeach


                           </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-9">
                     <button type="button" class="btn btn-success btn-lg btn-block" id='hizmet_personel_ekleme_butonu'>Hizmetlerin ekleneceği personelleri seç </button>
                  </div>
                  <div class="col-md-3">
                     <button  
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
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
<!-- hizmet için personel seçimi -->
<div
   id="personel_sec_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
      <div class="modal-content" style="width: 750px; max-height: 90%;">
         <form id="hizmet_personel_formu"  method="POST">
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            {!!csrf_field()!!}
            <div class="modal-header">
               <h2>Hizmet Personel Seçimi</h2>
               <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-hidden="true"
                  >
               ×
               </button>
            </div>
            <div class="modal-body" id='hizmet_personel_sec_bolumu'>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- listede olmayan hizmetler -->
<div
   id="yeni_hizmet_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="yeni_hizmet_formu"  method="POST">
            <div class="modal-header">
               <h2>Yeni Hizmet</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <div class="row" data-value="0">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmet Adı</label>
                        <input type="text" name="hizmet_adi" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmet Süresi (dk)</label>
                        <input type="tel" name="hizmet_sure" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmet fiyatı (₺)</label>
                        <input type="tel" name="hizmet_fiyati" class="form-control">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmeti Sunan Personeller & Cihazlar</label>
                        <select name="personeller[]" multiple class="form-control custom-select2" style="width:100%">
                           @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',1)->get() as $personel)
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option> 
                           @endforeach
                           @foreach(\App\Cihazlar::where('salon_id',$isletme->id)->where('aktifmi',1)->get() as $cihaz)
                              <option value="cihaz-{{$cihaz->id}}">{{$cihaz->cihaz_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
               </div>
               <div class="row" data-value="0">
                  <div class="col-md-9">
                     <div class="form-group">
                        <label>Hizmet Kategorisi</label>
                        <select name="hizmet_kategorisi" class="form-control custom-select2" style="width: 100%;">
                           @foreach(\App\Hizmet_Kategorisi::all() as $cat)
                           <option value="{{$cat->id}}">{{$cat->hizmet_kategorisi_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label style="visibility: hidden;width: 100%;">Hizmetler</label>
                        <button type="button" data-value="0" class="btn btn-success" data-toggle="modal" data-target="#hizmet_kategori_ekle_modal" ><i class="icon-copy dw dw-settings2"></i> Yeni Kategori Ekle</button>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <label>Hizmetin Sunulduğu Müşteri Cinsiyeti</label>
                     <select class="form-control" name="cinsiyet">
                        <option selected value="">Belirtilmemiş</option>
                        <option value="0">Kadın</option>
                        <option value="1">Erkek</option>
                        <option value="2">Unisex</option>
                     </select>
                  </div>
               </div>
               <div class="modal-footer" style="display:block">
                  <div class="row" data-value="0">
                     <div class="col-md-6">
                        <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                        Kaydet </button>
                     </div>
                     <div class="col-md-6">
                        <button  
                           type="button"
                           class="btn btn-danger btn-lg btn-block"
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
<!-- hizmet kategorisi listleme -->
<div
   class="modal modal-top fade calendar-modal"
   id="hizmet_kategori_modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="width: 50%;">
      <div class="modal-content"  >
         <div class="modal-header">
            <h2 class="modal-title"  >
               Özel Hizmet Kategorileri
            </h2>
            <button
               type="button"
               class="close"
               data-dismiss="modal"
               aria-hidden="true"
               id='hizmet_kategori_modal_kapat'
               >
            ×
            </button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-12">
                  <table id='ozel_hizmet_kategorileri' class="data-table table stripe hover nowrap">
                     <thead>
                        <tr>
                           <td>Kategori</td>
                           <td>İşlemler</td>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach(\App\Hizmet_Kategorisi::where('ozel_kategori',true)->where('salon_id',$isletme->id)->get() as $kategori)
                        <tr>
                           <td>{{$kategori->hizmet_kategorisi_adi}}</td>
                           <td style="text-align: right;"><button name='hizmet_kategori_duzenle' class="btn btn-secondary" type="button" data-value='{{$kategori->id}}'><i class="icon-copy fa fa-pencil" aria-hidden="true"></i></button></td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>

            
               </div>
            </div>
         </div>
         <div class="modal-footer" style="display:block">
            <div class="row" data-value="0">
               <div class="col-md-12">
                  <button type="submit" data-toggle="modal" class="btn btn-success btn-lg btn-block" data-target="#hizmet_kategori_ekle_modal"> <i class="icon-copy dw dw-add"></i>
                  Yeni özel hizmet kategorisi ekle </button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="personel-modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form id="yenipersonelbilgiekle" method="POST">
            {!!csrf_field()!!}
            <input type="hidden" name="personel_id" id='personel_id'>
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <div class="modal-header">
               <h2 class="modal_baslik"></h2>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Personel Adı</label>
                        <input id="personel_adi" name="personel_adi" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Cinsiyet</label>
                        <select id="cinsiyet" name="cinsiyet" class="form-control">
                           <option value="0">Kadın</option>
                           <option value="1">Erkek</option>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Cep Telefon</label>
                        <input type="tel" name='cep_telefon' id='cep_telefon' data-inputmask =" 'mask' : '5999999999'" required class="form-control">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Unvan</label>
                        <input class="form-control" id='unvan' name="unvan" type="text">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hesap Türü</label>
                        <select class="form-control" name="sistem_yetki" id="sistem_yetki">
                           
                           <option disabled value="Hesap Sahibi">Hesap Sahibi</option>
                           @foreach($roller as $key => $rol)
                              @if($key != 0 && $key != 5)
                              <option value="{{$rol->name}}">{{$rol->name}}</option>
                              @endif
                           @endforeach
                          
                        </select>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <h3 style="font-size: 15px; font-weight: bold;">Çalışma Saatleri</h3>
                     <table class="table table table-striped table-hover">
                        <tbody>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor1" name="calisiyor1"><label for="calisiyor1">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>
                                 <input type="time" id='personelbaslangicsaati1' class="form-control" value="00:00" name="baslangicsaati1" style="float: left;">  
                              </td>
                              <td> 
                                 <input type="time" id='personelbitissaati1' class="form-control" value="00:00" name="bitissaati1"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor2" name="calisiyor2"><label for="calisiyor2">
                                    </label>
                                 </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                 <input type="time" id='personelbaslangicsaati2' class="form-control" value="00:00" name="baslangicsaati2" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelbitissaati2' class="form-control" value="00:00" name="bitissaati2"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor3" name="calisiyor3"><label for="calisiyor3">
                                    </label>
                                 </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                 <input type="time"  class="form-control" value="00:00" name="baslangicsaati3" id='personelbaslangicsaati3' style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="bitissaati3" id='personelbitissaati3' style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor4" name="calisiyor4"><label for="calisiyor4">
                                    </label>
                                 </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="baslangicsaati4" id='personelbaslangicsaati4' style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="bitissaati4" id='personelbitissaati4' style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor5" name="calisiyor5"><label for="calisiyor5">
                                    </label>
                                 </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="baslangicsaati5" id='personelbaslangicsaati5' style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="bitissaati5" id='personelbitissaati5'  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor6" name="calisiyor6"><label for="calisiyor6">
                                    </label>
                                 </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="baslangicsaati6" id='personelbaslangicsaati6' style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="bitissaati6" id='personelbitissaati6'  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelcalisiyor7" value="00:00" name="calisiyor7"><label for="calisiyor7">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                 <input type="time" id="personelbaslangicsaati7" class="form-control" value="00:00" name="baslangicsaati7" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id="personelbitissaati7"class="form-control" value="00:00" name="bitissaati7"  style="float: left;">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <div class="col-md-6">
                     <h3 style="font-size: 15px; font-weight: bold;">Personel Mola Saatleri</h3>
                     <table class="table table table-striped table-hover">
                        <tbody>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar1" name="molavar1"><label for="molavar1">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati1' class="form-control" value="00:00" name="molabaslangicsaati1" style="float: left;">  
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati1' class="form-control" value="00:00" name="molabitissaati1"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar2" name="molavar2"><label for="molavar2">
                                    </label>
                                 </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati2' class="form-control" value="00:00" name="molabaslangicsaati2" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati2' class="form-control" value="00:00" name="molabitissaati2"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar3" name="molavar3"><label for="molavar3">
                                    </label>
                                 </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati3' class="form-control" value="00:00" name="molabaslangicsaati3" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati3' class="form-control" value="00:00" name="molabitissaati3"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar4" name="molavar4"><label for="molavar4">
                                    </label>
                                 </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati4' class="form-control" value="00:00" name="molabaslangicsaati4" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati4' class="form-control" value="00:00" name="molabitissaati4"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar5" name="molavar5"><label for="molavar5">
                                    </label>
                                 </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati5' class="form-control" value="00:00" name="molabaslangicsaati5" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati5' class="form-control" value="00:00" name="molabitissaati5"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar6" name="molavar6"><label for="molavar6">
                                    </label>
                                 </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati6' class="form-control" value="00:00" name="molabaslangicsaati6" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati6' class="form-control" value="00:00" name="molabitissaati6"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="personelmolavar7" value="00:00" name="molavar7"><label for="molavar7">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                 <input type="time" id='personelmolabaslangicsaati7' class="form-control" value="00:00" name="molabaslangicsaati7" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" id='personelmolabitissaati7' class="form-control" value="00:00" name="molabitissaati7"  style="float: left;">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <div class="col-md-12">
                     <h3 style="font-size: 15px; font-weight: bold;">Hak Ediş Ayarları
                     </h3>
                  </div>
                  <div class="col-md-2">
                     <div class="form-group">
                        <label>Sabit Maaş</label>
                        <input type="tel" id='personel_maas' name="personel_maas" class="form-control">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="form-group">
                        <label>Hizmet Primi Hak Edişi (%)</label>
                        <input type="tel" id='hizmet_prim_yuzde' name="hizmet_prim_yuzde" class="form-control">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Ürün Primi Hak Edişi (%)</label>
                        <input type="tel" id='urun_prim_yuzde' name="urun_prim_yuzde" class="form-control">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Paket Primi Hak Edişi (%)</label>
                        <input type="tel" id='paket_prim_yuzde' name="paket_prim_yuzde" class="form-control">
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- hizmet kategorisi ekleme -->
<div
   class="modal fade bs-example-modal-lg"
   id="hizmet_kategori_ekle_modal"
   tabindex="2"
   role="dialog"
   aria-labelledby="myLargeModalLabel"
   aria-hidden="true"
   >
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="width:100%">
         <form id='hizmet_kategori_ekle_duzenle_form' method="POST">
            <input type="hidden" name="hizmet_kategori_id" id='hizmet_kategori_id'>
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            {!!csrf_field()!!}
            <div class="modal-header">
               <h2 class="modal-title"  >
                  Özel Hizmet Kategorisi
               </h2>
               <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-hidden="true"
                  id='hizmet_kategori_ekle_modal_kapat'
                  >
               ×
               </button>
            </div>
            <div class="modal-body">
               <div class="row" data-value="0">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Hizmet Kategorisi</label>
                        <input type="text" required name="hizmet_kategorisi" class="form-control"> 
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-12">
                     <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="icon-copy dw dw-add"></i>
                     Kaydet </button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- hizmet kategorisi düzenle -->
<div
   class="modal fade bs-example-modal-lg"
   id="hizmet_kategori_duzenle_modal"
   tabindex="2"
   role="dialog"
   aria-labelledby="myLargeModalLabel"
   aria-hidden="true"
   >
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="width:100%">
         <div class="modal-header">
            <h2 class="modal-title"  >
               Özel Hizmeti Düzenle
            </h2>
            <button
               type="button"
               class="close"
               data-dismiss="modal"
               aria-hidden="true"
               >
            ×
            </button>
         </div>
         <div class="modal-body">
            {!!csrf_field()!!}
            <div class="row" data-value="0">
               <div class="col-md-12">
                  <div class="form-group">
                     <label>Hizmet Kategorisi</label>
                     <textarea name="masraf_aciklama" class="form-control"></textarea>
                  </div>
               </div>
               <div class="col-md-12" style="margin-top: 15px">
                  <label>Online randevu sayfanızda görünsün mü?</label>
                  <label class="switch"  style="margin-left:350px;">
                  <input type="checkbox">
                  <span class="slider round"></span>
                  </label>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row" data-value="0">
                  <div class="col-md-12">
                     <button type="submit" class="btn btn-success btn-lg btn-block"> <i class="icon-copy dw dw-add"></i>
                     Kaydet </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="yeni_cihaz_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style=" max-height: 90%;">
         <form id="yenicihazbilgiekle" method="POST">
            <input type="hidden"name="_token" value="{{csrf_token()}}">
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <div class="modal-header">
               <h2 class="modal_baslik">Yeni Cihaz</h2>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Cihaz Adı</label>
                        <input id="cihazadi_yeni" name="cihaz_adi" required class="form-control">
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <h3 style="font-size: 15px; font-weight: bold;">Çalışma Saatleri</h3>
                     <table class="table table table-striped table-hover">
                        <tbody>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor1" name="cihaz_calisiyor1"><label for="calisiyor1">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati1" style="float: left;">  
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati1"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor2" name="cihaz_calisiyor2"><label for="calisiyor2">
                                    </label>
                                 </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati2" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati2"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor3" name="cihaz_calisiyor3"><label for="calisiyor3">
                                    </label>
                                 </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati3" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati3"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor4" name="cihaz_calisiyor4"><label for="calisiyor4">
                                    </label>
                                 </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati4" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati4"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor5" name="cihaz_calisiyor5"><label for="calisiyor5">
                                    </label>
                                 </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati5" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati5"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor6" name="cihaz_calisiyor6"><label for="calisiyor6">
                                    </label>
                                 </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati6" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati6"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="calisiyor7" value="00:00" name="cihaz_calisiyor7"><label for="calisiyor7">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_baslangicsaati7" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_bitissaati7"  style="float: left;">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <div class="col-md-6">
                     <h3 style="font-size: 15px; font-weight: bold;">Cihaz Mola Saatleri</h3>
                     <table class="table table table-striped table-hover">
                        <tbody>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar1" name="cihaz_molavar1"><label for="molavar1">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazartesi</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati1" style="float: left;">  
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati1"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar2" name="cihaz_molavar2"><label for="molavar2">
                                    </label>
                                 </div>
                              </td>
                              <td>Salı</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati2" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati2"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar3" name="cihaz_molavar3"><label for="molavar3">
                                    </label>
                                 </div>
                              </td>
                              <td>Çarşamba</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati3" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati3"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar4" name="cihaz_molavar4"><label for="molavar4">
                                    </label>
                                 </div>
                              </td>
                              <td>Perşembe</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati4" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati4"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar5" name="cihaz_molavar5"><label for="molavar5">
                                    </label>
                                 </div>
                              </td>
                              <td>Cuma</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati5" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati5"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar6" name="cihaz_molavar6"><label for="molavar6">
                                    </label>
                                 </div>
                              </td>
                              <td>Cumartesi</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati6" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati6"  style="float: left;">
                              </td>
                           </tr>
                           <tr>
                              <td>
                                 <div class="be-checkbox be-checkbox-color inline">
                                    <input type="checkbox" id="molavar7" value="00:00" name="cihaz_molavar7"><label for="molavar7">
                                    </label>
                                 </div>
                              </td>
                              <td>Pazar</td>
                              <td>
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabaslangicsaati7" style="float: left;"> 
                              </td>
                              <td> 
                                 <input type="time" class="form-control" value="00:00" name="cihaz_molabitissaati7"  style="float: left;">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block;">
               <div class="row">
                  <div class="col-md-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<div
   id="yeni_oda_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="yeniodabilgiekle"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <h2 class="text-blue h2 mb-10" >Yeni Oda</h2>
               <div class="form-group">
                  <label>Oda Adı</label>
                  <input type="text" required name="oda_adi"  class="form-control">

               </div>
               <div class="form-group">
                  <label>Personel</label>
                  <select name="oda_personeli[]" multiple style="width:100%" class="form-control  personel_secimi" required >
                     <option></option>
                  </select>
               </div>
               
            </div>

            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                     Kaydet
                     </button>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal" 
                        ><i class="fa fa times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>


<div
   id="oda_duzenle_modal2"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="odabilgiduzenle"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="oda_id" id="duzenlenecek_oda_id">
               <h2 class="text-blue h2 mb-10" >Oda Düzenle</h2>
               <div class="form-group">
                  <label>Oda Adı</label>
                  <input type="text" required name="oda_adi" id="oda_adi" class="form-control">

               </div>
               <div class="form-group">
                  <label>Personel</label>
                  <select name="oda_personeli[]" multiple id="oda_personeli" style="width:100%" class="form-control  personel_secimi" required >
                     <option></option>
                  </select>
               </div>
               
            </div>

            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                     Kaydet
                     </button>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal" 
                        ><i class="fa fa times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
      </div>
      </form>
   </div>
</div>


<div
   id="oda_duzenle_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="oda_duzenle"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="oda_id" id="oda_id" value="0">
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <h2 class="text-blue h2 mb-10" >Müsait Değil</h2>
               <div class="form-group">
                  <label>Açıklama</label>
                  <input type="text" placeholder="Örneğin; tadilat vs."  required name="oda_aciklama" id="oda_aciklama" class="form-control">
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                     Kaydet
                     </button>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal" 
                        ><i class="fa fa times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
      </div>
      </form>
   </div>
</div>
<div
   id="cihaz_duzenle_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="cihaz_duzenle"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="cihaz_id" id="cihaz_id" value="0">
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <h2 class="text-blue h2 mb-10" >Müsait Değil</h2>
               <div class="form-group">
                  <label>Açıklama</label>
                  <input type="text" placeholder="Örneğin; bozuk vs."  required name="cihaz_aciklama" id="cihaz_aciklama" class="form-control">
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i>
                     Kaydet
                     </button>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6">
                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal" 
                        ><i class="fa fa times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
      </div>
      </form>
   </div>
</div>

<script>
   function thisFileUpload() {
       document.getElementById("isletmekapakfoto").click();
   };
   function thisFileUploadLogo() {
       document.getElementById("isletmelogo").click();
   };
</script>
@endsection