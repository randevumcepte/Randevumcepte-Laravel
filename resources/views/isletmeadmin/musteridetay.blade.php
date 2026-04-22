@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <input type="hidden" id="musteriKarti" value="{{$musteri_bilgi->id}}">
   <div class="row">
      <div class="col-md-3 col-sm-6 col-6">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">
                  {{$sayfa_baslik}}  
               </li>
            </ol>
         </nav>
      </div>
      <div class="col-md-1 col-sm-6 col-6">
         @if($tahsilatlar_count > 3 && $son_tahsilat_tarihi && strtotime('+90 days', strtotime($son_tahsilat_tarihi)) < strtotime('+90 days'))
         <img src="/public/img/sadik-1.png" style="width: 100px; height:auto;">
         @elseif($tahsilatlar_count == 0)
         <img src="/public/img/pasif-1.png" style="width: 100px; height:auto;">
         @else
         <img src="/public/img/aktif-1.png" style="width: 100px; height:auto;">
         @endif
      </div>
      <div class="col-md-2 col-sm-12"></div>
      <div class="col-md-6 col-sm-12 text-right">
         @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
         <div class="d-inline-block mr-2">
            <button class="btn btn-danger" id="toplamBorc">
            Toplam Borç : 0,00 ₺
            </button>
         </div>
         <div class="d-inline-block mr-2" >
            <button class="btn btn-success" id="toplamOdenen">
            Toplam Ödenen : 0,00 ₺
            </button>
         </div>
         @endif
         @if(!$is_personel_rolu)
         <div class="d-inline-block">
            <button style='display:{{($kara_liste != 1) ? "inline-block": "none"}}' class="btn btn-primary btn-lg" id='musteri_sms_kara_listeye_ekle' data-value='{{$musteri_bilgi->id}}'>
            <i class="fa fa-times"></i> Kara Listeye Ekle
            </button>
            <button style='display:{{($kara_liste == 1) ? "inline-block": "none"}}' class="btn btn-dark btn-lg" id='musteri_sms_kara_listeden_cikar' data-value='{{$musteri_bilgi->id}}'>
            <i class="fa fa-check"></i> Kara Listeden Çıkar
            </button>
         </div>
         @endif
      </div>
   </div>
</div>
<div class="row clearfix">
   <div class="col-lg-12 col-md-12 col-sm-12 mb-30">
      <div class="tab">
         <ul
            class="nav nav-tabs elementmusteridetay"
            role="tablist" 
            >
            <li class="nav-item" style="margin:5px; ">
               <a
                  class="btn btn-outline-primary active"
                  data-toggle="tab"
                  href="#musteri-bilgileri"
                  role="tab"
                  aria-selected="true"
                  style="width: 130px;"
                  >Genel Bilgiler</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary"
                  data-toggle="tab"
                  href="#randevular"
                  role="tab"
                  style="width: 130px;"
                  aria-selected="false"
                  >Randevular</a>
            </li>
            @if(!$is_personel_rolu)
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#formlar"
                  style="width: 150px;"
                  role="tab"
                  aria-selected="false"
                  >Seanslar</a>
            </li>
            @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#tum_adisyonlar"
                  style="width: 130px;"
                  role="tab"
                  aria-selected="false"
                  >Satışlar</a>
            </li>
            @endif
            <li class="nav-item" style="margin:5px;display: none;">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#borclar"
                  style="width: 130px;"
                  role="tab" 
                  aria-selected="false"
                  >Alacaklar</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#saglik-bilgileri"
                  role="tab"
                  style="width: 130px;"
                  aria-selected="false"
                  >Sağlık Bilgileri</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#belgeler"
                  style="width: 160px;"
                  role="tab"
                  aria-selected="false"
                  >Sözleşmeler/Belgeler</a>
            </li>
            <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-outline-primary "
                  data-toggle="tab"
                  href="#musteri_resimleri"
                  style="width: 160px;"
                  role="tab"
                  aria-selected="false"
                  >Müşteri Resimleri</a>
            </li>
             <li class="nav-item" style="margin:5px">
               <a
                  class="btn btn-warning "
                  data-toggle="tab"
                  href="#tahsilatEkrani"
                  style="width: 180px;"
                  role="tab"
                  aria-selected="false"
                  >Randevusuz Satış Yap</a>
            </li>
            @endif
         </ul>
         <div class="tab-content">
            <div
               class="tab-pane fade"
               id="formlar"
               role="tabpanel"
               >
               <div class="card-box  pd-10">
                  <h4 style="float:left;"><b>{{$musteri_bilgi->name}} in Tüm Seansları </b></h4>
                  <table class="data-table table stripe hover nowrap" id="seans_takip_liste">
                     <thead>
                        <tr>
                           <th scope="col">ID</th>
                         <th scope="col">Müşteri</th>
                     <th scope="col">Seans Başlangıcı</th>
                     <th scope="col">Paket Adı</th>
                     <th scope="col">Seans Detayı</th>
                           
                     <th class="datatable-nosort"></th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="hizmetler"
               role="tabpanel"
               >
               <div class="pd-20">
                  Hizmetler
               </div>
            </div>
            @if($_SERVER['HTTP_HOST']!='randevu.randevumcepte.com.tr')
            <div
               class="tab-pane fade show"
               id="tum_adisyonlar"
               role="tabpanel"
               >
               <input id="adisyon_musteriye_gore_filtrele" value="{{$musteri_bilgi->id}}" type="hidden">
               <div class="card-box pd-10">
                  <table class="data-table table stripe hover nowrap" id="adisyon_liste_musteri">
                     <thead>
                        <th>Satış Tarihi</th>
                        <th>Planlanan Alacak Tarihi</th>
                       
                        <th>Satış İçeriği </th>
                        <th>Toplam ₺</th>
                        <th>Ödenen ₺</th>
                        <th>Kalan ₺</th>
                        <th>İşlemler</th>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            @endif
            <div
               class="tab-pane fade show"
               id="belgeler"
               role="tabpanel"
               >
               <div class="card-box mb-30">
                  <div style="padding: 20px">
                     <ul class="nav nav-tabs element" role="tablist">
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary active"
                              data-toggle="tab"
                              href="#tum_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Tümü</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#onayli_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Onaylananlar</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#beklenen_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Beklenenler</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#iptal_arsiv"
                              role="tab"
                              aria-selected="false"
                              >İptal Edilenler</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary "
                              data-toggle="tab"
                              href="#harici_arsiv"
                              role="tab"
                              aria-selected="false"
                              >Harici Belgeler</button
                              >
                        </li>
                     </ul>
                     <div class="tab-content">
                        <div class="tab-pane fade show active" id="tum_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="onayli_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_onayli">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="beklenen_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_beklenen">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="iptal_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_iptal">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                        <div class="tab-pane fade show " id="harici_arsiv" role="tab-panel" style="margin-top: 20px;">
                           <table class="data-table table stripe hover nowrap" id="arsiv_liste_harici">
                              <thead>
                                 <th>Müşteri</th>
                                 <th>Başlık</th>
                                 <th>Oluşturulma Tarihi</th>
                                 <th>Belge Durumu</th>
                                 <th>Durum</th>
                                 <th>İşlemler</th>
                              </thead>
                              <tbody>
                              </tbody>
                                    
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div
               class="tab-pane fade show"
               id="randevular"
               role="tabpanel"
               >
               <div class="card-box pd-10">
                  <table class="data-table table stripe hover nowrap" id="randevu_liste">
                     <thead>
                        <tr>
                           <th>Tarih </th>
                           <th>Saat</th>
                           <th>Durum</th>
                           <th>Hizmetler</th>
                           <th>Personel/Cihaz/Oda</th>
                           
                           <th>Oluşturan</th>
                           <th>Oluşturulma</th>
                           <th class="datatable-nosort"></th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
               <div class="pd-20">
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="takvim-ayarlari"
               role="tabpanel"
               >
               <div class="pd-20">
                  Takvim Ayarları
               </div>
            </div>
            <div
               class="tab-pane fade show active"
               id="musteri-bilgileri"
               role="tabpanel"
               >
               <div class="row" style="padding:15px">
                  <div class="col-md-6">
                     <h3 class="text-blue">Bilgileri </h3>
                     <div class="card-box">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="profile-photo" style="margin-top:20px">
                                 <a href="#" class="edit-avatar" onclick="thisFileUpload();"><i class="fa fa-pencil"></i></a>
                                 <img id="mevcut_musteri_profil_resmi"
                                    src="{{($musteri_bilgi->profil_resim !== null ? $musteri_bilgi->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" alt="" class="avatar-photo" style="object-fit: cover; width: 160px; height: 160px;">
                                 <div
                                    class="modal fade"
                                    id="modal"
                                    tabindex="-1"
                                    role="dialog"
                                    aria-labelledby="modalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document"></div>
                                    <div class="modal-content">
                                       <div class="modal-body pd-5">
                                          <div class="img-container">
                                             <img                            
                                                src="{{($musteri_bilgi->profil_resim !== null ? $musteri_bilgi->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}"
                                                alt="Avatar"
                                                />
                                             <input type="file" id="musteri_profil_resmi" style="display:none;" />
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
                                 <button id="crop_modal_ac_musteri"  data-toggle="modal" data-target="#crop_modal_musteri" style="display:none"> modal aç</button>             
                                 <div class="modal fade"
                                    id="crop_modal_musteri"
                                    tabindex="-1"
                                    role="dialog"
                                    aria-labelledby="modalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog"
                                       role="document">
                                       <div class="modal-content">
                                          <div class="modal-body pd-5">
                                             <div class="img-container">
                                                <div class="row">
                                                   <div class="col-md-12">
                                                      <!--  default image where we will set the src via jquery-->
                                                      <img id="croppedimg" src="{{(Auth::guard('isletmeyonetim')->user()->profil_resim !== null ? Auth::guard('isletmeyonetim')->user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="modal-footer" style="display: block;">
                                             <div class="row">
                                                <div class="col-6 col-xs-6 col-sm-6">
                                                   <button id="crop" class="btn btn-primary btn-lg btn-block">Kırp</button>
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
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="card-body musteri_genel_bilgi_kart">
                                 <p style="margin-top: 10px;"><b>ID : </b>{{$musteri_bilgi->id}}</p>
                                 <p style="margin-top: 10px;"><b>Ad Soyad : </b>{{$musteri_bilgi->name}}</p>
                                 <p><b>Telefon : </b>{{$musteri_bilgi->cep_telefon}}</p>
                                 <p><b>E-posta : </b>{{$musteri_bilgi->email}}</p>
                                 <p><b>Referans : </b>
                                    @if($portfoy->musteri_tipi == 1)
                                    İnternet
                                    @elseif($portfoy->musteri_tipi == 2)
                                    Reklam
                                    @elseif($portfoy->musteri_tipi == 3)
                                    Instagram
                                    @elseif($portfoy->musteri_tipi == 4)
                                    Facebook
                                    @elseif($portfoy->musteri_tipi == 5)
                                    Tanıdık
                                    @else
                                    Yok
                                    @endif
                                 </p>
                                 <p><b>Doğum Tarihi : </b>{{date('d.m.Y', strtotime($musteri_bilgi->dogum_tarihi))}}</p>
                                 <p><b>TC Kimlik No : </b>{{$musteri_bilgi->tc_kimlik_no}}</p>
                                 <p><b>Cinsiyet : </b>
                                    @if($musteri_bilgi->cinsiyet === 0)
                                    Kadın @elseif($musteri_bilgi->cinsiyet===1) Erkek @else Belirtilmemiş @endif
                                 </p>
                                 <p><b>Notlar : </b>{{$portfoy->ozel_notlar}};
                              </div>
                           </div>
                        </div>
                        <div class="card-footer">
                           <button onclick='modalbaslikata("<?php echo $musteri_bilgi->name;?> Bilgilerini Düzenle","");' class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#musteri-bilgi-duzenle-modal">
                           <i class="fa fa-edit"></i> Düzenle
                           </button>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <ul class="nav nav-tabs element" role="tablist">
                        <li class="nav-item" style="margin-left: 20px;">
                           <button
                              class="btn btn-outline-primary active"
                              data-toggle="tab"
                              href="#musteri_hareketleri"
                              role="tab"
                              aria-selected="false"
                              >Müşteri Hareketleri</button
                              >
                        </li>
                        <li class="nav-item" style="margin-left: 20px;display: inline-block; ">
                           <button
                              class="btn btn-outline-primary"
                              data-toggle="tab"
                              href="#islem_notlari"
                              role="tab"
                              aria-selected="false"
                              style="width: 150px"
                              >İşlem Notları</button
                              >
                        </li>
                     </ul>
                     <div class="tab-content">
                        <div class="tab-pane fade show active" id="musteri_hareketleri" role="tab-panel" style="margin-top: 20px">
                           <div style="overflow-y: auto; max-height:572px">
                              @foreach($randevular as $randevu)
                              <div class="card-box" style="margin-bottom: 20px;">
                                 <div class="card-header">
                                    <div class="row">
                                       <div class="col-6 col-xs-6">
                                          {{date('d.m.Y',strtotime($randevu->tarih))}}
                                       </div>
                                       <div class="col-6 col-xs-6">
                                          @foreach($randevu->hizmetler as $hizmet)
                                          {{($hizmet->hizmet_id? $hizmet->hizmetler->hizmet_adi : "")}} &nbsp;
                                          @endforeach
                                       </div>
                                    </div>
                                 </div>
                                 <div class="card-body">
                                    {{$randevu->personel_notu}}
                                 </div>
                              </div>
                              @endforeach
                              @if($randevular->count()==0)
                              <div class="alert alert-danger" role="alert">
                                 Müşteriye ait randevu veya işlem bulunamadı!
                              </div>
                              @endif
                           </div>
                        </div>
                        <div class="tab-pane fade show " id="islem_notlari" role="tab-panel" style="margin-top: 20px">
                           <div style="overflow-y: auto; max-height:572px">
                              @foreach($randevular as $randevu)
                              <div class="card-box" style="margin-bottom: 20px;">
                                 <div class="card-header">
                                    <div class="row">
                                       <div class="col-6 col-xs-6">
                                          {{date('d.m.Y',strtotime($randevu->tarih))}}
                                       </div>
                                       <div class="col-6 col-xs-6">
                                          @foreach($randevu->hizmetler as $hizmet)
                                          {{($hizmet->hizmet_id? $hizmet->hizmetler->hizmet_adi : "")}}  &nbsp;
                                          @endforeach
                                       </div>
                                    </div>
                                 </div>
                                 <div class="card-body">
                                    {{$randevu->randevu_sonrasi_not}}
                                 </div>
                              </div>
                              @endforeach
                              @if($randevular->count()==0)
                              <div class="alert alert-danger" role="alert">
                                 Müşteriye ait randevu veya işlem bulunamadı!
                              </div>
                              @endif
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="saglik-bilgileri"
               role="tabpanel"
               >
               <div class="card-box pd-20">
                  <form id="musteri_saglik_bilgileri" method="GET">
                     <input name="musteri_id" type="hidden" value="{{$musteri_bilgi->id}}">
                     <div class="row">
                        <div class="col-md-6">
                           <label>Hemofili Hastalığı Var mı?</label>
                           <select name="hemofili_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->hemofili_hastaligi_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Şeker Hastalığı Var mı?</label>
                           <select name="seker_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->seker_hastaligi_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Hamile mi?</label>
                           <select name="seker_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->hamile)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Yakın bir zamanda ameliyat geçirdi mi?</label>
                           <select name="yakin_zamanda_ameliyat_gecirildi" class="form-control">
                              @if($musteri_bilgi->yakin_zamanda_ameliyat_gecirildi)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Herhangi bir alerjisi var?</label>
                           <select name="alerji_var" class="form-control">
                              @if($musteri_bilgi->alerji_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>48 saat içinde alkol alımı var mı?</label>
                           <select name="alkol_alimi_yapildi" class="form-control">
                              @if($musteri_bilgi->alkol_alimi_yapildi)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Regl döneminde mi?</label>
                           <select name="regl_doneminde" class="form-control">
                              @if($musteri_bilgi->regl_doneminde)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Deri veya yumuşak doku hastalığı var mı?</label>
                           <select name="deri_yumusak_doku_hastaligi_var" class="form-control">
                              @if($musteri_bilgi->deri_yumusak_doku_hastaligi_var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Sürekli kullanıdığı ilaç var mı?</label>
                           <select name="surekli_kullanilan_ilac_Var" class="form-control">
                              @if($musteri_bilgi->surekli_kullanilan_ilac_Var)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Kemoterapi görüyor mu?</label>
                           <select name="kemoterapi_goruyor" class="form-control">
                              @if($musteri_bilgi->kemoterapi_goruyor)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Daha önce uygulama yaptırdı mı?</label>
                           <select name="daha_once_uygulama_yaptirildi" class="form-control">
                              @if($musteri_bilgi->daha_once_uygulama_yaptirildi)
                              <option value="0">Hayır</option>
                              <option value="1" selected>Evet</option>
                              @else
                              <option value="0" selected>Hayır</option>
                              <option value="1">Evet</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label>Cilt Tipi</label>
                           <select name="cilt_tipi" class="form-control">
                              @if($musteri_bilgi->cilt_tipi == 0)
                              <option value="0" selected >Karma</option>
                              <option value="1">Yağlı</option>
                              <option value="2">Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 1)
                              <option value="0"  >Karma</option>
                              <option value="1" selected>Yağlı</option>
                              <option value="2">Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 2)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" selected>Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 3)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3" selected>Kuru</option>
                              <option value="4">Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 4)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4" selected>Nemli</option>
                              <option value="5">Normal</option>
                              @elseif($musteri_bilgi->cilt_tipi == 5)
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4" >Nemli</option>
                              <option value="5" selected>Normal</option>
                              @else
                              <option value="0"  >Karma</option>
                              <option value="1" >Yağlı</option>
                              <option value="2" >Hassas</option>
                              <option value="3">Kuru</option>
                              <option value="4" >Nemli</option>
                              <option value="5">Normal</option>
                              @endif
                           </select>
                        </div>
                        <div class="col-md-12">
                           <label>Ek sağlık sorunları var mı?</label>
                           <textarea class="form-control" name="ek_saglik_sorunu">@if(!empty($musteri_bilgi->ek_saglik_sorunu)) {{$musteri_bilgi->ek_saglik_sorunu}} @else Yok @endif</textarea>
                        </div>
                        <div class="col-md-12" style="margin-top:20px">
                           <button type="submit" class="btn btn-success" style="width:100%;text-align: center;">Formu Kaydet</button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="musteri_resimleri"
               role="tabpanel"
               >
               <div class="card-box pd-10">
                  <div class="row">
                     <div class="col-md-6">
                        <h4  class="text-blue">Müşteri 
                           Resimleri 
                        </h4>
                     </div>
                     <div class="col-md-6" style="text-align:right;">
                        <button class="btn btn-success brn-lg "  data-target="#musterifotoekle" data-toggle="modal" type="button">Yeni Resim Ekle</button>
                     </div>
                  </div>
               </div>
               <div class="card-box pd-20" style="margin-top: 20px">
                  <div id="buttonContainer">
                     @foreach($islemler as $islem)
                     <button class="btn btn-outline-primary" name="islemdetaygetir" style="margin-top: 10px" data-toggle="modal" data-target="#islemdetayigetirmodal" type="button" name="islemdetaygetir" data-value="{{$islem->id}}"> @php
                     $images = json_decode($islem->islem_fotolari, true);
                     @endphp
                     <img src="/{{ $images[0] }}" alt="İşlem Fotoğrafı" style="width:100px; height:100px;">
                     <br><br>{{date('d.m.Y',strtotime($islem->tarih))}}</button>
                     @endforeach
                  </div>
               </div>
            </div>
            <div
               class="tab-pane fade"
               id="tahsilatEkrani"
               role="tabpanel"
               >
               
               <div  style="margin-top: 20px;padding: 20px;">
                   <form id="adisyon_tahsilat"  method="POST">
<div class="row">
   <div class="col-md-9">
      <div class="card-box pd-5"  style="margin-bottom:20px">
        
            <input type="hidden" name='sube' value="{{$isletme->id}}">
            <input type="hidden" name="tahsilat_ekrani" id='tahsilat_ekrani' value="1">
            <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari' >
            <input type="hidden" name="adisyon_id" id="session_adisyon_id" value="">
            <div class="modal-header">
               <div class="col-6 col-xs-6 col-sm-6">
                  <h2>Tahsilat</h2>
               </div>
               
                     
               <div class="col-md-6 col-6 col-xs-6 col-sm-6" style="display:none">
                        <div class="from-group"  >
                           <select name='tahsilat_musteri_id' style="width:100%"  class="form-control"  id='tahsilat_musteri_id' >
                              <option value="{{$musteri_bilgi->id}}">{{$musteri_bilgi->name}}</option>
                           </select>
                        </div>
               </div>
                     
                  
                
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <div class="row">
                  <div class="col-md-12">
                     <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-2 col-sm-4 col-4" style="margin-bottom: 20px;">
                           <button disabled type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Hizmet Ekle</button>
                        </div>
                        <div class="col-md-2 col-sm-4 col-4" style="padding-left: 0;margin-bottom: 20px;">
                           <button disabled type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value=''onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn  btn-danger  btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Ürün Ekle</button>
                        </div>
                        <div class="col-md-2 col-sm-4 col-4" style="padding-left: 0;margin-bottom: 20px;">
                           <button disabled type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="btn  btn-primary  btn-block adisyon_ekle_buttonlar" style="font-size:12px">Paket Ekle</button>
                        </div>

                        <div class="col-md-6 text-right" id="tahsilats_type" >
                           <button type="button" class="btn btn-success adisyon_ekle_buttonlar" id='senetle_veya_taksitle_tahsil_et' disabled> Alacaklar</button>
                           <button type="button" id='yeni_taksitli_tahsilat_olusur' href="#"  data-value='' class="btn  btn-primary adisyon_ekle_buttonlar" style="font-weight: bold;" disabled>Taksit Yap</button> 
                        </div>
                     </div>
                     <div id='tum_tahsilatlar'>
                     </div>
                     <div id="taksitli_ve_senetli_tahsilatlar">
                     </div>
                  </div>
               </div>
               <div class="row tek_tahsilat_formu" data-value="0">
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Tarih</label>
                        <input type="text" required class="form-control" name="tahsilat_tarihi" id='tahsilat_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label style="width: 100%">Birim Tutar (₺)</label>
                        <input  class="form-control try-currency" id='birim_tutar' value=""   style="font-size:20px" >
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Müşteri İndirimi (%)</label>
                        <input type="text" class="form-control" disabled id='musteri_indirim' name="musteri_indirim" value="0">
                        <input type="hidden" id='musteri_indirimi' name="musteri_indirimi">
                     </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>İndirim (₺)</label>
                        <input  type="tel" name="indirim_tutari" id='harici_indirim_tutari' class="form-control try-currency">
                     </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödenecek Tutar (₺)</label>
                        <input type="tel" style="font-size: 20px; background-color: #d4edda; border-color: #c3e6cb;" class="form-control try-currency"  name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödeme Yönetmi</label>
                        <select class="form-control" id='adisyon_tahsilat_odeme_yontemi' name="odeme_yontemi">
                           @foreach($odeme_yontemleri as $odeme_yontemi)
                           <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Banka (opsiyonel)</label>
                        <select class="form-control" id='adisyon_tahsilat_banka' name="banka">
                           <option value=''>Seçiniz...</option>
                           @foreach($bankalar as $banka)
                           <option value="{{$banka->id}}">{{$banka->banka}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Kalan Alacak Tutarı (₺)</label>
                        <input type="tel" class="form-control try-currency" name="odenecek_tutar" id='odenecek_tutar'>
                     </div>
                  </div>
                  <div class="col-md-6"></div>
                  <div class="col-md-6">
                     <button disabled id='yeni_tahsilat_ekle' type="submit" class="btn btn-success btn-lg btn-block adisyon_ekle_buttonlar"> <i class="fa fa-money"></i>
                     Tahsil Et </button>
                  </div>
               </div>
            </div>
        
      </div>
   </div>
   <div class="col-md-3">
      <div id="odeme_kayit_bolumu">
         <h2>Ödeme</h2>
         <div class="card-box pd-20 odemeozeti"  style="margin-bottom:20px">
            <div class="row">
               <div class="col-12 col-xs-12 col-sm-12">
                  <b style="width: 100%;">Alacak Tutarı (₺)</b>
               </div>
               <div class="col-md-12">
                  <span id="tahsil_edilecek_kalan_tutar" style="color:#ff0000;font-size:30px">
                  </span>
               </div>
               <div class="col-md-12">
                  <table class="table" style="margin-top:20px">
                     <thead id="tahsilat_durumu">
                        <tr>
                           <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Özet</td>
                        </tr>
                        <tr>
                           <td colspan="3">Ara Toplam (₺)</td>
                           <td id='ara_toplam' style="text-align:right;">  </td>
                        </tr>
                        <tr>
                           <td colspan="3">Müşteri İndirimi (₺)</td>
                           <td id='uygulanan_indirim_tutari' style="text-align:right;"> </td>
                        </tr>
                        <tr>
                           <td colspan="3">Harici İndirim (₺)</td>
                           <td id='uygulanan_harici_indirim_tutari' style="text-align:right;"> </td>
                        </tr>
                        <tr style="font-weight: bold; color: green;display: none;">
                           <td colspan="3">
                              Ödenen Tutar (₺): 
                           </td>
                           <td id="tahsil_edilen_tutar" style="text-align:right;">
                              {{number_format(0,2,',','.')}}
                           </td>
                        </tr>
                        <tr style="font-weight: bold; color: red;">
                           <td colspan="3">
                              Alacak Tutarı (₺): 
                           </td>
                           <td class="tahsil_edilecek_kalan_tutar" style="text-align:right;">
                           </td>
                        </tr>
                     </thead>
                     <tbody id="tahsilat_listesi">
                        <tr>
                           <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Geçmiş Ödemeler</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
         <button type="submit" class="btn btn-success" style="width:100%;margin-top: 10px;display: none;">Değişiklikleri Kaydet</button>
      </div>
   </div>
</div>
 
</form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="musterifotoekle" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dailog-centered" style="max-width: 560px">
      <form id="musterifotoekleform">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <input name="musteri_id" type="hidden" value="{{$musteri_bilgi->id}}">
         <div class="modal-content" style="min-height: 200px;">
            <div class="modal-header">
               <h4 class="h4">Yeni Resim Ekle</h4>
               <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-hidden="true"
                  >
               ×
               </button>
            </div>
            <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Tarih</label>
                        <input type="text" required class="form-control" name="resimtarih" id="resimtarih" value="{{date('Y-m-d')}}" autocomplete="off">
                     </div>
                  </div>
                  <div class="col-md-6 col-xs-6 col-sm-6 col-6 form-group">
                     <label>Resim Yükle</label>
                     <input type="file" name="musteriresimyukle[]" id="musteriresimyukle" class="form-control-file form-control " multiple>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
               <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                  <button type="submit" class="btn btn-success btn-block"> Kaydet</button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>
<div id="islemdetayigetirmodal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
      <form id="islemdetayigetirform">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <input name="klasor_id" type="hidden" value="">
         <div class="modal-content" style="min-height: 550px;">
            <div class="modal-header">
               <h4 class="h4">Resimler</h4>
               <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-hidden="true"
                  >
               ×
               </button>
            </div>
            <div class="modal-body" style="padding:20px">
               <div class="row" style="overflow-y:scroll; max-height: 500px">
                  <div class="gallery-wrap" id="islembolumu">
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>
<script>
   function thisFileUpload() {
       document.getElementById("musteri_profil_resmi").click();
   };
</script>
@endsection