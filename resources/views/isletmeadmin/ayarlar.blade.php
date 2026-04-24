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
                  </ul>
               </div>
               <div class="col-md-12 col-sm-12" style="margin-top:32px">
                  <div class="tab-content">
                     <div
                        class="tab-pane fade {{($_GET['p']=='temelbilgiler') ? 'active show' : ''}}"
                        id="isletme-bilgileri"
                        role="tabpanel"
                        >
                        {{-- ==== MODERN TEMEL BİLGİLER PANELİ ==== --}}
                        <style>
                           .rm-tb { padding: 0 4px; }
                           .rm-tb__header {
                              display:flex; align-items:center; justify-content:space-between;
                              gap:16px; padding:18px 20px; margin-bottom:18px;
                              background: linear-gradient(135deg, #faf5ff 0%, #fff 100%);
                              border:1px solid #ede1f7; border-left:4px solid #5C008E;
                              border-radius:12px;
                           }
                           .rm-tb__header h2 { color:#5C008E; margin:0; font-size:22px; font-weight:700; }
                           .rm-tb__header p { color:#6b7280; margin:4px 0 0; font-size:13px; }
                           .rm-card {
                              background:#fff; border:1px solid #ececf1; border-radius:14px;
                              box-shadow: 0 2px 6px rgba(17,17,26,.03);
                              margin-bottom:18px; overflow:hidden;
                           }
                           .rm-card__head {
                              padding:14px 20px; border-bottom:1px solid #f1f1f5;
                              background:#fafafc;
                           }
                           .rm-card__head h3 {
                              margin:0; font-size:16px; font-weight:700; color:#2d2143;
                              display:flex; align-items:center; gap:10px;
                           }
                           .rm-card__head h3 i { color:#5C008E; }
                           .rm-card__head small { display:block; color:#6b7280; margin-top:4px; font-size:12.5px; }
                           .rm-card__body { padding:20px; }
                           .rm-card__body .form-group label { font-weight:600; color:#3a2e57; font-size:13px; }
                           .rm-tb textarea.form-control { min-height:110px; resize:vertical; }
                           .rm-logo-wrap { display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
                           .rm-logo-wrap .profile-photo { margin:0; }
                           .rm-logo-hint { color:#6b7280; font-size:12px; max-width:320px; }
                           .rm-input-inline { display:flex; gap:8px; }
                           .rm-input-inline .form-control { flex:1; }
                           .rm-input-inline .btn { white-space:nowrap; }
                           /* In-form Kaydet barı (sayfa sonu) */
                           .rm-savebar {
                              position: sticky; bottom: 0;
                              background: rgba(255,255,255,.96);
                              backdrop-filter: saturate(140%) blur(8px);
                              -webkit-backdrop-filter: saturate(140%) blur(8px);
                              border-top:1px solid #ece6f3;
                              padding:12px 16px; margin: 10px -20px -20px;
                              display:flex; justify-content:flex-end; gap:10px; align-items:center;
                              z-index: 20;
                              box-shadow: 0 -6px 18px rgba(92,0,142,.06);
                           }
                           .rm-savebar__hint { color:#6b7280; font-size:12.5px; margin-right:auto; }
                           .rm-savebar .btn-save {
                              background:#5C008E; border-color:#5C008E; color:#fff; font-weight:700;
                              padding:10px 22px; border-radius:10px; min-width:180px;
                              box-shadow: 0 6px 16px rgba(92,0,142,.25);
                           }
                           .rm-savebar .btn-save:hover { background:#48006e; border-color:#48006e; }
                           .rm-qr-btn { background:#5C008E; border-color:#5C008E; color:#fff; font-weight:600; }
                           .rm-qr-btn:hover { background:#48006e; border-color:#48006e; color:#fff; }

                           /* Sağ altta sabit FAB Kaydet — nereden edit edilirse edilsin tek tıkla kaydet */
                           #rmFloatingSave {
                              position: fixed;
                              bottom: 22px; right: 22px;
                              background: #5C008E; color:#fff;
                              border:none; border-radius: 50px;
                              padding: 14px 26px;
                              font-weight: 700; font-size: 15px;
                              box-shadow: 0 10px 28px rgba(92,0,142,.35), 0 4px 10px rgba(0,0,0,.08);
                              z-index: 1050;
                              display: none;
                              align-items: center; gap: 8px;
                              transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
                           }
                           #rmFloatingSave:hover {
                              background:#48006e;
                              transform: translateY(-2px);
                              box-shadow: 0 14px 34px rgba(92,0,142,.45), 0 6px 14px rgba(0,0,0,.1);
                           }
                           #rmFloatingSave i { font-size: 16px; }
                           #rmFloatingSave.is-visible { display: inline-flex; }

                           @media (max-width: 600px) {
                              .rm-tb__header { flex-direction:column; align-items:flex-start; }
                              .rm-savebar { flex-wrap:wrap; }
                              .rm-savebar__hint { width:100%; margin:0 0 8px; }
                              .rm-savebar .btn-save { width:100%; min-width:0; }
                              #rmFloatingSave { bottom: 14px; right: 14px; left: 14px; justify-content:center; }
                           }
                        </style>

                        <div class="rm-tb">
                           <div class="rm-tb__header">
                              <div>
                                 <h2><i class="fa fa-store" style="margin-right:6px;"></i> Temel Ayarlar</h2>
                                 <p>Buradaki bilgiler müşterilerin gördüğü <b>tanıtım sayfasında</b> yayınlanır. Eksiksiz doldurmanız işletmenizin daha iyi görünmesini sağlar.</p>
                              </div>
                              <button type="button" class="btn rm-qr-btn" data-toggle="modal" data-target="#qr_kod_modal">
                                 <i class="fa fa-qrcode"></i> QR Kodu Gör
                              </button>
                           </div>

                           <form id="isletme_temel_bilgiler" method="POST">
                              <input type="hidden" name="sube" value="{{$isletme->id}}">
                              {!! csrf_field() !!}

                              {{-- ==== 1) İŞLETME KİMLİĞİ ==== --}}
                              <div class="rm-card">
                                 <div class="rm-card__head">
                                    <h3><i class="fa fa-id-card-o"></i> İşletme Kimliği</h3>
                                    <small>Logo, isim, tür ve iletişim bilgileri. Tanıtım sayfasında en üstte gösterilir.</small>
                                 </div>
                                 <div class="rm-card__body">
                                    <div class="rm-logo-wrap" style="margin-bottom:18px">
                                       <input type="file" id="isletmelogo" name='isletmelogo' style="display:none;" />
                                       <div class="profile-photo">
                                          <a href="#" class="edit-avatar" style='background:#fff;' onclick="thisFileUploadLogo();"><i class="fa fa-pencil"></i></a>
                                          <img id="profillogo"
                                             src="{{($isletme->logo !== null ? '/'.$isletme->logo : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
                                             alt=""
                                             class="avatar-photo" style="background:#444;object-fit:cover;width:140px;height:140px;border-radius:12px;" />
                                       </div>
                                       <div class="rm-logo-hint">
                                          <strong>Logo Yükle</strong><br>
                                          Maksimum 240px genişliğinde veya 100px yüksekliğinde olmalı. Kalem simgesine tıklayarak değiştirebilirsiniz.
                                       </div>
                                    </div>

                                    <div class="row">
                                       <div class="col-md-6">
                                          <div class="form-group">
                                             <label>İşletme Adı</label>
                                             <input type="text" name="isletme_adi" value="{{$isletme->salon_adi}}" required class="form-control">
                                          </div>
                                       </div>
                                       <div class="col-md-6">
                                          <div class="form-group">
                                             <label>İşletme Türü</label>
                                             <select class="form-control custom-select2" name="isletme_turu" style="width:100%">
                                                @foreach(\App\SalonTuru::all() as $isletme_turu)
                                                <option value="{{$isletme_turu->id}}" {{($isletme_turu->id == $isletme->salon_turu_id) ? 'selected' : ''}}>{{$isletme_turu->salon_turu_adi}}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                       </div>
                                       <div class="col-md-8">
                                          <div class="form-group">
                                             <label>Adres</label>
                                             <input type="text" name="isletme_adres" value="{{$isletme->adres}}" class="form-control" placeholder="İşletmenizin açık adresi">
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group">
                                             <label>Telefon</label>
                                             <input required data-inputmask=" 'mask' : '5999999999'" type="text" name="isletme_telefon" value="{{$isletme->telefon_1}}" class="form-control" placeholder="5XX XXX XX XX">
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group">
                                             <label><i class="fa fa-whatsapp" style="color:#25D366"></i> WhatsApp</label>
                                             <input type="tel" name="whatsapp" class="form-control" data-inputmask=" 'mask' : '5999999999'" value="{{$isletme->whatsapp}}" placeholder="5XX XXX XX XX">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              {{-- ==== 2) İŞLETME TANITIMI (HAKKIMIZDA) ==== --}}
                              <div class="rm-card">
                                 <div class="rm-card__head">
                                    <h3><i class="fa fa-bullhorn"></i> İşletme Tanıtımı</h3>
                                    <small>Müşterilerin tanıtım sayfanızda <b>"Hakkımızda"</b> bölümünde göreceği metin. Sizi kısa ve samimi bir dille anlatın.</small>
                                 </div>
                                 <div class="rm-card__body">
                                    <div class="form-group" style="margin-bottom:0">
                                       <label>İşletme Açıklaması</label>
                                       <textarea class="form-control" name="isletme_aciklama" rows="5" maxlength="1500"
                                          placeholder="Örn. 2010'dan bu yana Karşıyaka'da hizmet veriyoruz. Uzman kadromuz ve hijyenik ortamımızla saç bakımı, kalıcı makyaj ve cilt bakımında farkı hissedeceksiniz...">{{$isletme->aciklama}}</textarea>
                                       <small class="text-muted">Müşterinin gözünde samimi ve güven veren bir tanıtım, randevu alma oranını artırır.</small>
                                    </div>
                                 </div>
                              </div>

                              {{-- ==== 3) İLETİŞİM & LİNKLER ==== --}}
                              <div class="rm-card">
                                 <div class="rm-card__head">
                                    <h3><i class="fa fa-link"></i> İletişim & Sosyal Medya Linkleri</h3>
                                    <small>Online randevu sayfanızın linki ve sosyal medya profilleri. Müşteriler bu linkler üzerinden size ulaşır.</small>
                                 </div>
                                 <div class="rm-card__body">
                                    <div class="form-group">
                                       <label>Online Randevu URL</label>
                                       <div class="rm-input-inline">
                                          <input type="text" required class="form-control" placeholder="Online randevu link" value="https://{{$isletme->domain}}" id="myInput" readonly>
                                          <button class="btn btn-success" type="button" onclick="myFunction()"><i class="fa fa-copy"></i> Kopyala</button>
                                          <a target="_blank" href="https://{{$isletme->domain}}"><button class="btn btn-primary" type="button"><i class="fa fa-external-link"></i></button></a>
                                       </div>
                                    </div>

                                    <div class="form-group">
                                       <label><i class="fa fa-instagram" style="color:#E1306C"></i> Instagram URL</label>
                                       <div class="rm-input-inline">
                                          <input type="text" class="form-control" placeholder="https://instagram.com/..." value="{{$isletme->instagram_sayfa}}" id="instagram_url" name='instagram_url'>
                                          <button class="btn btn-success" type="button" onclick="myFunction2()"><i class="fa fa-copy"></i> Kopyala</button>
                                          <a target="_blank" href="{{$isletme->instagram_sayfa}}"><button class="btn btn-primary" type="button"><i class="fa fa-external-link"></i></button></a>
                                       </div>
                                    </div>

                                    <div class="form-group" style="margin-bottom:0">
                                       <label><i class="fa fa-facebook-official" style="color:#1877F2"></i> Facebook URL</label>
                                       <div class="rm-input-inline">
                                          <input type="text" class="form-control" name='facebook_url' placeholder="https://facebook.com/..." value="{{$isletme->facebook_sayfa}}" id="facebook_url">
                                          <button class="btn btn-success" type="button" onclick="myFunction3()"><i class="fa fa-copy"></i> Kopyala</button>
                                          <a target="_blank" href="{{$isletme->facebook_sayfa}}"><button class="btn btn-primary" type="button"><i class="fa fa-external-link"></i></button></a>
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
                                   var copyText = document.getElementById("instagram_url");
                                   copyText.select();
                                   copyText.setSelectionRange(0, 99999);
                                   navigator.clipboard.writeText(copyText.value);
                                 }
                                 function myFunction3(){
                                   var copyText = document.getElementById("facebook_url");
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

                              {{-- ==== 4) FATURA AYARLARI ==== --}}
                              <div class="rm-card">
                                 <div class="rm-card__head">
                                    <h3><i class="fa fa-file-text-o"></i> Fatura Ayarları</h3>
                                    <small>Adisyon ve fatura kesimlerinde kullanılacak yasal bilgiler.</small>
                                 </div>
                                 <div class="rm-card__body">
                                    <div class="row">
                                       <div class="col-md-6">
                                          <div class="form-group">
                                             <label>Firma Adı / Ünvanı</label>
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
                                             <input type="tel" class="form-control" name="vergi_tc_no" value="{{$isletme->vergi_no}}" data-inputmask=" 'mask' : '99999999999'">
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
                                             <label>KDV Oranı (%)</label>
                                             <input type="tel" name="kdv_orani" class="form-control" value="{{$isletme->kdv_orani}}">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              {{-- ==== 5) SEO & KAPAK ==== --}}
                              <div class="row">
                                 <div class="col-md-6">
                                    <div class="rm-card" style="height:calc(100% - 18px);">
                                       <div class="rm-card__head">
                                          <h3><i class="fa fa-search"></i> SEO Ayarları</h3>
                                          <small>Google gibi arama motorlarında işletmenizin görünümünü iyileştirir.</small>
                                       </div>
                                       <div class="rm-card__body">
                                          <div class="form-group">
                                             <label>Online Randevu Sayfası SEO Açıklaması</label>
                                             <textarea class="form-control" name="seo_description" placeholder="Ör. Kalıcı makyaj, cilt bakımı, lazer ve güzelliğe dair tüm hizmetler için güzellik merkezimiz hizmetinizde.">{{$isletme->meta_description}}</textarea>
                                             <small class="text-muted">Arama motorlarında çıkacak kısa tanıtım (maks. 160 karakter önerilir).</small>
                                          </div>
                                          <div class="form-group" style="margin-bottom:0">
                                             <label>Lokasyon Bazlı Anahtar Kelimeler</label>
                                             <small class="text-muted d-block" style="margin-bottom:8px">Ör. <i>izmirde güzellik merkezi</i>. Tüm kelimeler küçük harfle yazılmalıdır.</small>
                                             <?php $aramaterimisayisi = $aramaterimleri->count(); ?>
                                             @foreach($aramaterimleri as $key => $aramaterimi)
                                                <input type="text" name="anahtar_kelimeler[]" style="text-transform: lowercase;margin-bottom:6px;" placeholder="Anahtar Kelime {{$key+1}}" class="form-control" value="{{$aramaterimi->arama_terimi}}">
                                             @endforeach
                                             @if($aramaterimleri->count() < 6)
                                                @for($i=$aramaterimleri->count()+1; $i<=6; $i++)
                                                   <input type="text" name="anahtar_kelimeler[]" style="margin-bottom:6px;" placeholder="Anahtar Kelime {{$i}}" class="form-control">
                                                @endfor
                                             @endif
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="rm-card" style="height:calc(100% - 18px);">
                                       <div class="rm-card__head">
                                          <h3><i class="fa fa-image"></i> Kapak Resmi</h3>
                                          <small>Tanıtım sayfasının üst kısmında görünen büyük görsel. (Önerilen: 1600×600px)</small>
                                       </div>
                                       <div class="rm-card__body">
                                          <div class="profile-photo" style="width:100%;">
                                             <a href="#" class="edit-avatar" onclick="thisFileUpload();" style='background:#fff;'><i class="fa fa-pencil"></i></a>
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
                                 </div>
                              </div>

                              {{-- ==== 6) İŞLETME GÖRSELLERİ (GALERİ) ==== --}}
                              <div class="rm-card">
                                 <div class="rm-card__head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                    <div>
                                       <h3><i class="fa fa-picture-o"></i> İşletme Görselleri</h3>
                                       <small>Müşterinin tanıtım sayfasında göreceği galeri fotoğrafları. En fazla 12 adet ekleyebilirsiniz.</small>
                                    </div>
                                    <div class="single-file-input2" style="margin:0;">
                                       <input type="file" id="isletmegorselleri" name="isletmegorselleri" multiple="">
                                       <div id="gorseleklemetext" class="btn" style="background:#5C008E;color:#fff;font-weight:600;border-radius:10px;padding:10px 18px;">
                                          <i class="fa fa-plus"></i> Görsel Ekle (Kalan: {{12-$salongorselleri->count()}})
                                       </div>
                                    </div>
                                 </div>
                                 <div class="rm-card__body">
                                    <div class="gallery-wrap">
                                       <ul class="row" id='gorselbolumu'>
                                          {!!$gorseller_html!!}
                                       </ul>
                                    </div>
                                 </div>
                              </div>

                              <div style="padding-bottom:120px"></div>
                           </form>

                           {{-- Ekran sağ altında sabit floating Kaydet butonu (form dışında, form="" ile submit eder) --}}
                           <button type="submit" form="isletme_temel_bilgiler" id="rmFloatingSave" title="Tüm değişiklikleri kaydet">
                              <i class="fa fa-save"></i> Kaydet
                           </button>
                           <script>
                              (function(){
                                 var fab = document.getElementById('rmFloatingSave');
                                 if(!fab) return;
                                 function isTemelActive(){
                                    var pane = document.getElementById('isletme-bilgileri');
                                    return pane && pane.classList.contains('active') && pane.classList.contains('show');
                                 }
                                 function sync(){
                                    if(isTemelActive()) fab.classList.add('is-visible');
                                    else fab.classList.remove('is-visible');
                                 }
                                 sync();
                                 document.querySelectorAll('a[data-toggle="tab"]').forEach(function(a){
                                    a.addEventListener('click', function(){ setTimeout(sync, 60); });
                                 });
                                 window.addEventListener('hashchange', sync);
                              })();
                           </script>
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
                           <th>Personeller</th>
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
                <div style="display:none;"
               class="tab-pane fade"
               id="form_taslaklari"
               role="tabpanel"
               >
               <div class="pd-20">
                  <div class="row" style="border-bottom:1px solid #e2e2e2;margin-bottom:15px;padding-bottom:10px;">
                     <div class="col-6">
                        <h2 class="text-blue">Form Taslakları</h2>
                        <p class="text-muted" style="font-size:13px;">İşletmenize özel onam formları ve sözleşmeler. Buradan oluşturduğunuz formlar sadece size aittir.</p>
                     </div>
                     <div class="col-6 text-right">
                        <a href="/isletmeyonetim/form-sablonlari?sube={{ $isletme->id }}" class="btn btn-success">
                           <i class="fa fa-plus"></i> Yeni Form Oluştur
                        </a>
                     </div>
                  </div>
                  <div class="gallery-wrap">
                     <ul class="row">
                        @if(false)
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
                        @endif
                     @php
                        try {
                            $dinamikFormlar = DB::table('formtaslaklari')
                                ->where('salon_id', $isletme->id)
                                ->where('is_dinamik', 1)
                                ->orderBy('id','desc')
                                ->get();
                        } catch(\Exception $e) {
                            $dinamikFormlar = collect();
                        }
                     @endphp
                     @if($dinamikFormlar->isEmpty())
                        <li class="col-12 text-center" style="padding:40px 20px; background:#f8f9fa; border-radius:8px; margin-top:15px;">
                           <i class="fa fa-file-text-o" style="font-size:48px; color:#c0c4c8;"></i>
                           <h5 style="margin-top:15px; color:#6c757d;">Henüz form şablonu oluşturmadınız</h5>
                           <p class="text-muted">Sağ üstteki "Yeni Form Oluştur" butonuyla işletmenize özel onam formları ve sözleşmeler hazırlayabilirsiniz.</p>
                        </li>
                     @endif
                     @foreach($dinamikFormlar as $df)
                     <li class="col-lg-3 col-md-6 col-sm-12">
                        <div class="da-card box-shadow">
                           <div class="da-card-photo">
                              <div style="background:linear-gradient(135deg,#6c63ff,#48c774);min-height:190px;display:flex;align-items:center;justify-content:center;">
                                 <i class="fa fa-file-text-o" style="font-size:60px;color:rgba(255,255,255,0.7);"></i>
                              </div>
                              <div class="da-overlay">
                                 <div class="da-social">
                                    <h5 class="mb-10 color-white pd-20">{{ $df->form_adi }}</h5>
                                    <ul class="clearfix">
                                       <li>
                                          <a href="/isletmeyonetim/bosFormIndirDinamik?formId={{ $df->id }}&sube={{ $isletme->id }}" title="PDF İndir">
                                             <i class="fa fa-download"></i>
                                          </a>
                                       </li>
                                       <li>
                                          <a href="/isletmeyonetim/form-sablonlari?sube={{ $isletme->id }}" title="Düzenle">
                                             <i class="fa fa-pencil"></i>
                                          </a>
                                       </li>
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </li>
                     @endforeach
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
      <div class="modal-content" style="max-height: 90vh;">
         <form id='hizmet_ekle_formu' method="POST">
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <div class="modal-header">
               <h2><i class="fa fa-list-ul"></i> Hizmet Seçimi</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body hy-modal-body" style="padding:20px 22px;">
               {!!csrf_field()!!}

               <div class="hy-secim-info">
                  <i class="fa fa-info-circle"></i>
                  <span>Listeden eklemek istediğiniz hizmetlere tıklayın — seçtikten sonra <strong>personel atamak</strong> için "Devam Et" butonuna basın. Listede yoksa kendi hizmetinizi oluşturun.</span>
               </div>

               <div class="hy-secim-search">
                  <i class="fa fa-search"></i>
                  <input type="text" placeholder="Hizmet ara..." id='hizmet_ara' autocomplete="off" />
               </div>

               <div class="hy-secim-header-actions">
                  <button type="button" class="btn btn-outline-primary" onclick="selects()"><i class="fa fa-check-square-o"></i> Hepsini Seç</button>
                  <button type="button" class="btn btn-outline-secondary" onclick="deSelect()"><i class="fa fa-square-o"></i> Temizle</button>
               </div>

               <div class="hy-secim-list">
                  <button type="button" style="display:none" id='hizmet_personel_ekle_modal_ac' data-toggle="modal" data-target="#personel_sec_modal"></button>
                  <table class="table" id="hizmet_sec_tablo" style="display:none;">
                     <thead>
                        <tr>
                           <td><input type="checkbox" id='tum_hizmetleri_sec'></td>
                           <td>Hizmet</td>
                        </tr>
                     </thead>
                     <tbody id='secilmeyen_hizmetler_liste'>
                            
                         
                        {{-- Performans: kategori bazli gruplari controller'da hazirlandi (50-100 query -> 2 query) --}}
                        @isset($eklenebilir_hizmetler)
                           @foreach(\App\Hizmet_Kategorisi::all() as $hizmet_kategorisi)
                              @if(isset($eklenebilir_hizmetler[$hizmet_kategorisi->id]) && count($eklenebilir_hizmetler[$hizmet_kategorisi->id]) > 0)
                                 <tr style="background: #e2e2e2;">
                                    <td></td>
                                    <td><strong>{{$hizmet_kategorisi->hizmet_kategorisi_adi}}</strong></td>
                                 </tr>
                                 @foreach($eklenebilir_hizmetler[$hizmet_kategorisi->id] as $secilmeyenhizmetler)
                                    <tr>
                                       <td><input type="checkbox" name="salon_hizmetleri[]" value="{{$secilmeyenhizmetler->id}}"></td>
                                       <td>{{$secilmeyenhizmetler->hizmet_adi}}</td>
                                    </tr>
                                 @endforeach
                              @endif
                           @endforeach
                        @endisset


                     </tbody>
                  </table>

                  {{-- Modern secim listesi (JS ile doldurulur: #hizmet_sec_tablo'dan veri okunur) --}}
                  <div id="hy_secim_render"></div>
               </div>
            </div>
            <div class="hy-secim-footer">
               <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#yeni_hizmet_modal"><i class="fa fa-plus"></i> Listede Olmayan Hizmet</button>
               <button type="button" class="btn btn-success" id='hizmet_personel_ekleme_butonu'><i class="fa fa-arrow-right"></i> Devam Et — Personel Seç</button>
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
                        <input type="text" inputmode="decimal" name="hizmet_fiyati" class="form-control hy-fiyat-input" placeholder="0,00" autocomplete="off">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Hizmeti Sunan Personeller & Cihazlar</label>
                        <select name="personeller[]" multiple class="form-control custom-select2" style="width:100%">
                           @foreach(($personeller_raw ?? []) as $personel)
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                           @foreach(($cihazlar_raw ?? []) as $cihaz)
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
               {{-- ================= TANITIM SAYFASI ALANLARI ================= --}}
               <div class="row">
                  <div class="col-md-12">
                     <h3 style="font-size: 15px; font-weight: bold; margin-top: 6px;">Tanıtım Sayfası Bilgileri (opsiyonel)</h3>
                     <p style="color:#888; font-size:12px; margin:0 0 10px">Bu bilgiler salonunuzun tanıtım sayfasında personel kartlarında gösterilir.</p>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Uzmanlık Alanı</label>
                        <input class="form-control" id="uzmanlik" name="uzmanlik" type="text" placeholder="Ör: Saç Boyama · Balyaj · Kaynak" maxlength="200">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Yıllık Tecrübe</label>
                        <input class="form-control" id="yillik_tecrube" name="yillik_tecrube" type="number" min="0" max="80" placeholder="Ör: 8">
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="form-group">
                        <label>Instagram Kullanıcı Adı</label>
                        <input class="form-control" id="instagram" name="instagram" type="text" placeholder="kullaniciadi" maxlength="150">
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Kısa Açıklama / Biyografi</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3" maxlength="600" placeholder="Ör: 10 yılı aşkın deneyimiyle müşterilerine en uygun saç stilini öneriyor. Özel gün makyajı ve balyaj konusunda uzmandır."></textarea>
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
                  <select name="oda_personeli[]" multiple style="width:100%" class="form-control oda_personel_secimi" required >
                     @foreach($personeller_raw as $per)
                        <option value="{{ $per->id }}">{{ $per->personel_adi }}</option>
                     @endforeach
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
                  <select name="oda_personeli[]" multiple id="oda_personeli" style="width:100%" class="form-control oda_personel_secimi" required >
                     @foreach($personeller_raw as $per)
                        <option value="{{ $per->id }}">{{ $per->personel_adi }}</option>
                     @endforeach
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
<script>
$(document).ready(function(){
   // Tab değiştiğinde URL'i güncelle — böylece herhangi bir location.reload() doğru tab'a döner
   var tabPMap = {
      'isletme-bilgileri': 'temelbilgiler',
      'isletme-subeleri': 'subeler',
      'calisma-saatleri': 'calismasaatleri',
      'personeller': 'personeller',
      'cihazlar': 'cihazlar',
      'hizmetler': 'hizmetler',
      'odalar': 'odalar',
      'randevu-ayarlari': 'randevuayarlari',
      'musteri_indirimleri': 'musteri_indirimleri',
      'form_taslaklari': 'form_taslaklari',
      'urunler': 'urunler',
      'paketler': 'paketler'
   };
   $('a[data-toggle="tab"]').on('shown.bs.tab', function(){
      var tabId = $(this).attr('href').replace('#','');
      var pVal = tabPMap[tabId];
      if(pVal){
         var sube = new URLSearchParams(window.location.search).get('sube') || '';
         var newUrl = window.location.pathname + '?p=' + pVal + (sube ? '&sube=' + sube : '');
         history.replaceState(null, '', newUrl);
      }
   });
});
</script>
@endsection