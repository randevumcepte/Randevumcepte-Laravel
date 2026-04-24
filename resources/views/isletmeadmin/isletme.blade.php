@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="main-content container-fluid">
   <form id="mevcutisletmeduzenleme_isletmeadmin" method="post" enctype="multipart/form-data">
      {!!csrf_field()!!}
      <input id="isletmeid" name="isletmeid" value="{{$isletme->id}}" type="hidden">
      <div class="user-profile">
         <div class="row">
            <div class="col-md-12">
               <div class="user-display">
                  <div class="user-display-bg" style="display:none">
                     @if(\App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli')!= null || \App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli')!= '')
                     <img id="profilkapak" src="{{secure_asset(\App\SalonGorselleri::where('salon_id',$isletme->id)->where('kapak_fotografi',1)->value('salon_gorseli'))}}" alt="Profile Background"> 
                     @else
                     <img id="profilkapak" src="{{secure_asset('public/isletmeyonetim_assets/img/user-profile-display.png')}}" alt="Kapak Fotoğrafı">
                     @endif
                  </div>
                  <div class="single-file-input2" style="display:none">
                     <input type="file" id="isletmekapakfoto" name="isletmekapakfoto">
                     <div class="btn btn-primary">İşletme kapak fotoğrafını düzenle</span></div>
                  </div>
                  <div class="user-display-bottom">
                     <div class="user-display-avatar">
                        @if($isletme->logo != null || $isletme->logo != '')
                        <img id="profillogo" src="{{secure_asset($isletme->logo)}}" alt="Avatar">
                        @else
                        <img id="profillogo" src="{{secure_asset('public/isletmeyonetim_assets/img/avatar-150.png')}}" alt="Avatar">
                        @endif
                        <div class="single-file-input" style="left:0">
                           <input type="file" id="isletmelogo" name="isletmelogo">
                           <div class="btn btn-primary">İşletme logosu seç</div>
                        </div>
                     </div>
                     <div class="user-display-info">
                        <div class="name">
                           <div class="col-md-12">
                              <div class="form-group">
                                <label>İşletme Adı</label>
                                 <input type="text" name="isletmeadi" value="{{$isletme->salon_adi}}" placeholder="İşletme adı..." class="form-control">
                              </div>
                           </div>
                           
                        </div>
                     </div>
                     <div class="row user-display-details">
                       
                         
                           
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         {{-- ============ TANITIM SAYFASI BILGILERI ============ --}}
         <div class="row">
            <div class="col-md-12">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Tanıtım Sayfası Bilgileri
                     <span style="font-size:12px; color:#888; font-weight:400; margin-left:8px;">
                        Bu alanlar müşteri tanıtım sayfanızda (randevumcepte.com.tr salon sayfanız) görüntülenir.
                     </span>
                  </div>
                  <div class="panel-body">
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Hakkımızda / Açıklama</label>
                              <textarea name="aciklama" rows="4" placeholder="Salonunuzu tanıtan kısa bir yazı yazın. (Örn: 2010 yılından beri İstanbul Kadıköy'de müşterilerimize hizmet veriyoruz. Uzman kadromuz ve modern ekipmanlarımızla...)" class="form-control" maxlength="1200">{{$isletme->aciklama}}</textarea>
                              <small style="color:#888;">Bu yazı tanıtım sayfasında "Hakkımızda" bölümünde gösterilir.</small>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Telefon</label>
                              <input type="tel" name="telefon_1" value="{{$isletme->telefon_1 ?? ''}}" placeholder="0212 555 55 55" class="form-control">
                              <small style="color:#888;">Tanıtım sayfasında "Hemen Ara" butonunda kullanılır.</small>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Adres</label>
                              <input type="text" name="adres" value="{{$isletme->adres ?? ''}}" placeholder="Mahalle, cadde, no, ilçe" class="form-control">
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Google Maps Konum Kodu</label>
                              <textarea name="googlemapskaydi" rows="3" placeholder='Google Maps&apos;ten "Paylaş → Haritayı yerleştir" diyerek kopyaladığınız iframe kodunu veya sadece src URL&apos;sini buraya yapıştırın. Hem tam iframe hem URL kabul edilir.' class="form-control">{{$isletme->maps_iframe ?? ''}}</textarea>
                              <small style="color:#888;">
                                 <i class="fa fa-info-circle"></i>
                                 Nasıl alınır:
                                 <a href="https://www.google.com/maps" target="_blank" style="color:#5C008E;font-weight:600;">maps.google.com</a>
                                 → konumunuzu bulun → <b>Paylaş</b> → <b>Haritayı yerleştir</b> → <b>HTML&apos;yi kopyala</b> → buraya yapıştırın.
                              </small>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label><i class="fa fa-facebook" style="color:#1877F2;"></i> Facebook Sayfası</label>
                              <input type="url" name="facebookadres" value="{{$isletme->facebook_sayfa ?? ''}}" placeholder="https://facebook.com/salonunuz" class="form-control">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <label><i class="fa fa-instagram" style="color:#E4405F;"></i> Instagram</label>
                              <input type="text" name="instagramaccesstoken" value="{{$isletme->instagram_sayfa ?? ''}}" placeholder="@kullaniciadi veya https://instagram.com/kullaniciadi" class="form-control">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

          <div class="row">
            <div class="col-md-12">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                    Şubeler

                     <div class="tools">
                        <button type="button" data-modal="md-scale5" class="btn btn-space btn-primary md-trigger"><span style="color:white" class="icon icon mdi mdi-accounts-add"></span> Şube Ekle</button>
                      
                     
                    </div>
                  </div>
                  <div class="panel-body">
                      
                            <table class="table table-striped table-borderless subeler">
                              <thead>
                                 <tr>
                                    <th>Şube</th>
                                    <th>Adres</th>
                                    <th>Telefon</th>
                                
                                    <th>İşlemler</th>
                                 </tr>
                              </thead>
                             
                              <tbody class="no-border-x" id="subelistesi">
                                 @foreach($subeler as $sube)
                                
                                 <tr name="subesatir" data-value="{{$sube->id}}">
                                    <td>
                                        {{$sube->sube}}
                                    </td>
                                    <td>
                                       {{$sube->adres}}
                                    </td>
                                    <td>
                                      {{$sube->sube_tel}}
                                    </td>
                                    
                                    <td>
                                       <a title="Şube Bilgi Düzenle" name="subebilgiduzenle" style="font-size: 20px;cursor: pointer;" data-value="{{$sube->id}}" class="icon"> 
                                            <div class="icon"><span class="mdi mdi-edit"></span></div><span class="icon-class"></span>
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
            </div>
         </div>

         <div class="row" style="display:none">
            <div class="col-md-12">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Açıklama & Hakkında
                  </div>
                  <div class="panel-body">
                     <textarea name="aciklama"   placeholder="Açıklama & hakkımızda yazısı ekle..." class="form-control">{{$isletme->aciklama}}</textarea>
                  </div>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Hizmetler (Kadın)
                     <div class="tools">
                        <button type="button" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Yeni Hizmet Ekle</button>
                     </div>
                  </div>
                  <div class="panel-body">
                     <div class="form-group">
                        <label>İşletmenin sunduğu hizmetleri seçiniz...</label>
                        <select multiple="" id="hizmetlerlistesi_bayan" name="hizmetler_bayan[]" class="tags input-xs">
                        {!!$hizmetlistesi!!}
                        </select>
                        <button class="btn btn-primary" id="fiyatlistesineeklebayan">Fiyat Listesine Ekle</button>
                     </div>
                  </div>
                  <div class="panel-heading panel-heading-divider">
                     Fiyat Listesi
                  </div>
                  <div class="panel-body">
                     <table class="table table-striped table-borderless">
                        <thead>
                           <tr>
                              <th></th>
                              <th>Hizmet</th>
                              <th>Başlangıç Fiyat</th>
                              <th>Son Fiyat</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody class="no-border-x" id="hizmetfiyatlaribayan">
                        </tbody>
                        <tbody class="no-border-x" id="mevcuthizmetlistebayan">
                           @foreach($salonhizmetler as $hizmetliste)
                           @if($hizmetliste->bolum==0)
                           <tr>
                              <td>
                                 <input type='hidden' name='salonsunulanhizmetbayanid[]' value='{{$hizmetliste->hizmet_id}}'>
                              </td>
                              <td>
                                 {{$hizmetliste->hizmetler->hizmet_adi}}
                              </td>
                              <td>
                                 <input type='text' class='form-control input-xs' name='salonsunulanhizmetbayanbaslangicfiyat[]' value="{{$hizmetliste->baslangic_fiyat}}">
                              </td>
                              <td>
                                 <input type='text' value="{{$hizmetliste->son_fiyat}}" class='form-control input-xs' name='salonsunulanhizmetbayansonfiyat[]'>
                              </td>
                              <td><a name="hizmetlistedensil" title="Hizmet Pasif Et" style="font-size: 20px;cursor: pointer;" data-value="{{$hizmetliste->id}}" class="icon">
                                
                                 <span class="mdi mdi-minus-circle"></span></div><span class="icon-class"></span>
                              </a></td>
                           </tr>
                           @endif
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <div class="col-md-6">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Hizmetler (Erkek)
                     <div class="tools">
                        <button type="button" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Yeni Hizmet Ekle</button>
                     </div>
                  </div>
                  <div class="panel-body">
                     <div class="form-group">
                        <label>İşletmenin sunduğu hizmetleri seçiniz...</label>
                        <select multiple="" id="hizmetlerlistesi_bay" name="hizmetler_bay[]" class="tags input-xs">
                        {!!$hizmetlistesi!!}
                        </select>
                        <button class="btn btn-primary" id="fiyatlistesineeklebay">Fiyat Listesine Ekle</button>
                     </div>
                  </div>
                  <div class="panel-heading panel-heading-divider">
                     Fiyat Listesi
                  </div>
                  <div class="panel-body">
                     <table class="table table-striped table-borderless">
                        <thead>
                           <tr>
                              <th></th>
                              <th>Hizmet</th>
                              <th>Başlangıç Fiyat</th>
                              <th>Son Fiyat</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody class="no-border-x" id="hizmetfiyatlaribay">
                        </tbody>
                        <tbody class="no-border-x" id="mevcuthizmetlistebay">
                           @foreach($salonhizmetler as $hizmetliste)
                           @if($hizmetliste->bolum==1)
                           <tr>
                              <td>
                                 <input type='hidden' name='salonsunulanhizmetbayid[]' value='{{$hizmetliste->hizmet_id}}'>
                              </td>
                              <td>
                                 {{$hizmetliste->hizmetler->hizmet_adi}}
                              </td>
                              <td>
                                 <input type='text' class='form-control input-xs' name='salonsunulanhizmetbaybaslangicfiyat[]' value="{{$hizmetliste->baslangic_fiyat}}">
                              </td>
                              <td>
                                 <input type='text' value="{{$hizmetliste->son_fiyat}}" class='form-control input-xs' name='salonsunulanhizmetbaysonfiyat[]'>
                              </td>


                              <td>
                                <a name="hizmetlistedensil" title="Hizmet Pasif Et" style="font-size: 20px;cursor: pointer;" data-value="{{$hizmetliste->id}}" class="icon"> 

 <span class="mdi mdi-minus-circle"></span></div><span class="icon-class"></span>
                                             


                                </a>
                              </td>
                           </tr>
                           @endif
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Çalışma Saatleri
                  </div>
                  <div class="panel-body">
                     <table class="table table table-striped table-hover">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati1" style="float: left; width: 80px">   
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati1"  style="float: left; width: 80px">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati2" style="float: left; width: 80px"> 
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati2"  style="float: left; width: 80px">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati3" style="float: left; width: 80px"> 
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati3"  style="float: left; width: 80px">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati4" style="float: left; width: 80px"> 
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati4"  style="float: left; width: 80px">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati5" style="float: left; width: 80px"> 
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati5"  style="float: left; width: 80px">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati6" style="float: left; width: 80px"> 
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati6"  style="float: left; width: 80px">
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
                                 <input type="time" class="form-control input-xs" value="{{$value->baslangic_saati}}" name="baslangicsaati7" style="float: left; width: 80px"> 
                                 <input type="time" class="form-control input-xs" value="{{$value->bitis_saati}}" name="bitissaati7"  style="float: left; width: 80px">
                              </td>
                           </tr>
                           @endif
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <div class="col-md-6">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Personeller
                     <div class="tools">
                        <button type="button" data-modal="md-scale4" class="btn btn-space btn-primary md-trigger">Yeni Personel Ekle</button>
                     </div>
                  </div>
                  <div class="panel-body">
                     <select multiple="" id="personelliste" name="personeller[]" class="tags input-xs" style="height: 300px">
                     </select>
                  </div>
                  <div class="panel-body">
                     <div class="widget-chart-container">
                        <table id="table" class="table table-striped table-hover table-fw-widget">
                           <thead>
                              <tr>
                                 <th>Personel Adı</th>
                                 <th>İşlemler</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach($personeller as $personelliste)
                              <tr>
                                 <td>{{$personelliste->personel_adi}}</td>
                                 <td class="actions">
                                    <a href="/isletmeyonetim/personeldetay/{{$personelliste->id}}" class="icon"><i class="mdi mdi-settings"></i></a>
                                    <a name="personelsil" data-value="{{$personelliste->id}}" class="icon"><i class="mdi mdi-delete"></i></a>
                                 </td>
                              </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="row" style="display:none">
            <div class="col-md-12">
               <div class="panel panel-default">
                  <div class="panel-heading panel-heading-divider">
                     Görseller
                     <div class="single-file-input2">
                        <input type="file" id="isletmegorselleri" name="isletmegorselleri" multiple>
                        <div id="gorseleklemetext" class="btn btn-primary">İşletme Görsellerini Ekleyin (Max:{{12-$salongorselleri->count()}} adet)</span></div>
                     </div>
                  </div>
                  <div class="panel-body">
                     <div class="gallery-container" id="gorselbolumu">
                        {!!$gorseller_html!!}
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <button type="submit" class="btn btn-primary btn-big" style="width: 100%">İşletme Bilgilerini Güncelle</button>
            </div>
         </div>
      </div>
   </form>
</div>
<div id="md-scale" class="modal-container modal-effect-1">
   <div class="modal-content">
      <div class="modal-header">
         <span style="font-size:20px"> Sisteme Yeni Hizmet Ekle</span>
         <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
      </div>
      <div class="modal-body">
         <div class="form-group">
            <label>Hizmet Adı</label>
            <input id="hizmetadi_yeni" class="form-control">
         </div>
         <div class="form-group">
            <label>Hizmet Kategorisi</label>
            <select  id="hizmetkateogirisi_yeni" class="tags input-xs">
               <option value="0">Hizmet kategorisi seçin yada yeni bir kategori girin...</option>
               @foreach(\App\Hizmet_Kategorisi::all() as $hizmetkategorisi)
               <option value="{{$hizmetkategorisi->id}}">{{$hizmetkategorisi->hizmet_kategorisi_adi}}</option>
               @endforeach
            </select>
         </div>
         <div class="text-center">
            <div class="xs-mt-50">
               <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
               <button type="button" id="yenihizmetgir_isletme" class="btn btn-primary">Ekle</button>
            </div>
         </div>
      </div>
      <div class="modal-footer"></div>
   </div>
</div>
<div id="md-scale2" class="modal-container modal-effect-1">
   <div class="modal-content">
      <div class="modal-header">
         <span style="font-size:20px">Yeni İşletme Yetkilisi Ekle</span>
         <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
      </div>
      <div class="modal-body">
         <div class="form-group">
            <input id="yetkiliadi_yeni" required placeholder="Yetkili adı..." class="form-control">
         </div>
         <div class="form-group">
            <input type="email" required id="yetkili_eposta_yeni" placeholder="Yetkili e-posta & kullanıcı adı" class="form-control">
         </div>
         <div class="form-group">
            <input type="password" required id="yetkili_sifre_yeni" placeholder="Yetkili şifre..." class="form-control">
         </div>
         <div class="form-group">
            <input type="password" required id="yetkili_sifre_tekrar_yeni" placeholder="Yetkili şifre tekrar..." class="form-control">
         </div>
         <div class="text-center">
            <div class="xs-mt-50">
               <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
               <button type="button" id="yeniisletmeyetikilisigir" class="btn btn-primary">Ekle</button>
            </div>
         </div>
      </div>
      <div class="modal-footer"></div>
   </div>
</div>
<div id="md-scale3" class="modal-container modal-effect-1">
   <div class="modal-content">
      <div class="modal-header">
         <span style="font-size:20px">Yeni İşletme Türü Ekle</span>
         <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
      </div>
      <div class="modal-body">
         <div class="form-group">
            <input id="isletmeturuadi_yeni" required placeholder="İşletme türü..." class="form-control">
         </div>
         <div class="text-center">
            <div class="xs-mt-50">
               <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
               <button type="button" id="yeniisletmeturugir" class="btn btn-primary">Ekle</button>
            </div>
         </div>
      </div>
      <div class="modal-footer"></div>
   </div>
</div>
<div id="md-scale4" class="modal-container modal-effect-1">
   <div class="modal-content">
      <div class="modal-header">
         <span style="font-size:20px">Yeni Personel Ekle</span>
         <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
      </div>
      <div class="modal-body">
         <form id="yenipersonelgirisi" method="GET">
            <div class="form-group">
               <label>Personel Adı</label>
               <input id="personeladi_yeni" name="personeladi_yeni" required placeholder="Personel adı..." class="form-control">
            </div>
            <div class="form-group">
               <label>Çalıştığı Şube</label>
               <select name="personelsube_yeni" class="form-control select2" id="personelsube_yeni">
                  @foreach($subeler as $sube)
                      <option value="{{$sube->id}}">{{$sube->sube}}</option>
                  @endforeach
               </select>
            </div>
            <div class="form-group">
               <label>Sunulan Hizmetler (Kadın)</label>
               <select id="personelsunulanhizmetlerbayan_yeni" multiple name="personelsunulanhizmetlerbayan_yeni[]" class="tags input-xs">
               {!!$hizmetlistesi!!}
               </select>
            </div>
            <div class="form-group">
               <label>Sunulan Hizmetler (Erkek)</label>
               <select id="personelsunulanhizmetlerbay_yeni" multiple name="personelsunulanhizmetlerbay_yeni[]" class="tags input-xs">
               {!!$hizmetlistesi!!}
               </select>
            </div>
            <div class="text-center">
               <div class="xs-mt-50">
                  <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                  <button type="button" id="yenipersonelgir_isletme" class="btn btn-primary">Ekle</button>
               </div>
            </div>
         </form>
      </div>
      <div class="modal-footer"></div>
   </div>
</div>

<div id="md-scale5" class="modal-container modal-effect-1">
   <div class="modal-content">
      <div class="modal-header">
         <span style="font-size:20px">Yeni Şube Ekle</span>
         <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
      </div>
      <div class="modal-body">
         <form id="yenisubegir_isletme" method="GET">
            <div class="form-group">
               <label>Şube</label>
               <input type="text" id="subeadi_yeni" name="subeadi_yeni" required placeholder="Şube adı..." class="form-control">
            </div>
            <div class="form-group">
               <label>Adres</label>
                <input type="text" id="subeadres_yeni" name="subeadres_yeni" required placeholder="Şube adresi..." class="form-control">
            </div>
            <div class="form-group">
               <label>Telefon</label>
                <input type="number" id="subetel_yeni" maxlength="10" name="subetel_yeni" required placeholder="Boşluk bırakmadan 10 haneli giriniz" class="form-control">
            </div>
            <div class="text-center">
               <div class="xs-mt-50">
                  <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                  <button type="submit"  class="btn btn-primary">Ekle</button>
               </div>
            </div>
         </form>
      </div>
      <div class="modal-footer"></div>
   </div>
</div>
<div class="modal-overlay"></div>
<div id="hata"></div>
@endsection