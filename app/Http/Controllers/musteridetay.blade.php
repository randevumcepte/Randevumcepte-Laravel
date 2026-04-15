@extends('layout.layout_isletmeadmin')
@section('content')

<div class="page-header">
   <div class="row">
      <div class="col-md-4 col-sm-12">
         <div class="title">
            <h1>{{$sayfa_baslik}}</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">
                  {{$sayfa_baslik}}  
               </li>
            </ol>
         </nav>
      </div> 

      <div class="col-md-5 col-sm-12">
         @if(\App\Adisyonlar::where('user_id',$musteri_bilgi->id)->where('salon_id',$isletme->id)->count()>3 
               && 
            date('Y-m-d H:i:s', strtotime('+90 days',strtotime(\App\Adisyonlar::where('user_id',$musteri_bilgi->id)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))
         <img src="/public/img/sadik-1.png" style="width: 100px; height:auto;">
         
         @elseif(\App\Adisyonlar::where('user_id',$musteri_bilgi->id)->where('salon_id',$isletme->id)->count()==0)
         <img src="/public/img/pasif-1.png" style="width: 100px; height:auto;">
         @else
         <img src="/public/img/aktif-1.png" style="width: 100px; height:auto;">
         @endif
      </div>
      @if(!Auth::user()->hasRole('Personel'))
      <div class="col-md-3 col-sm-12" style="text-align:right;">

          

            
               <button  style='display:{{(\App\MusteriPortfoy::where('user_id',$musteri_bilgi->id)->where('salon_id',$isletme->id)->value('kara_liste')!=1) ? "inline-block": "none"}}' class="btn btn-primary btn-lg " id='musteri_sms_kara_listeye_ekle' data-value='{{$musteri_bilgi->id}}'>
                  <i class="fa fa-times"></i>  Kara Listeye Ekle
               </button>
                                    
               <button style='display:{{(\App\MusteriPortfoy::where('user_id',$musteri_bilgi->id)->where('salon_id',$isletme->id)->value('kara_liste')==1) ? "block": "none"}}' class="btn btn-dark btn-lg " id='musteri_sms_kara_listeden_cikar' data-value='{{$musteri_bilgi->id}}'>
                  <i class="fa fa-check"></i>  Kara Listeden Çıkar
               </button>

      </div>
      @endif
     
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
                     @if(!Auth::user()->hasRole('Personel'))
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
                     <li class="nav-item" style="margin:5px">
                        <a
                           class="btn btn-outline-primary "
                           data-toggle="tab"
                           href="#adisyonlar"
                            style="width: 130px;"
                           role="tab"
                           aria-selected="false"
                           >Satışlar</a>
                     </li>
                     
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
                          <table class="table">
                              <thead>
                                 <tr>
                                    <th scope="col">Seans Başlangıcı</th>
                                    <th scope="col">Paket Adı</th>
                                    <th scope="col">Seans Detayı</th>
                                    <th scope="col">Toplam Ücret (₺)</th>
                                    <th scope="col"></th>
                                 </tr>
                              </thead>
                              <tbody id="adisyon_detay_paket_tablo_2">
                                 @if($adisyonlar->count()>0)
                                 @foreach($adisyonlar as $adisyon)
                                    @foreach(\App\AdisyonPaketler::where('adisyon_id',$adisyon->id)->get() as $paket)
                                     <tr>
                                    <td>{{date('d.m.Y',strtotime($paket->baslangic_tarihi))}}</td>
                                    <td>{{$paket->paket->paket_adi}}</td>
                                    <td>
                                       <button name='paketteki_seanslari_beklemede_isaretle' title="Beklemede" class="btn btn-warning">
                                       {{\App\AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',null)->count()}} &nbsp;
                                        <i class="fa fa-calendar"></i></button>
                                       <button name='paketteki_seanslari_geldi_isaretle' title='Geldi' class="btn btn-success">
                                       {{\App\AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',true)->count()}} &nbsp;
                                        <i class="fa fa-check"></i></button>
                                       <button name='paketteki_seanslari_gelmedi_isaretle' title='Gelmedi' class="btn btn-danger">
                                       {{\App\AdisyonPaketSeanslar::where('adisyon_paket_id',$paket->id)->where('geldi',false)->count()}} &nbsp;
                                        <i class="fa fa-times"></i></button>

                                    </td> 
                                    <td>
                                       <input type="hidden" name="paket_fiyati_adisyon[]" value="{{$paket->fiyat}}"> 
                                       {{$paket->fiyat}} ₺

                                    </td>
                                    <td>
                                          
                                          <button type="button" name="paket_seans_detay_getir_modal"  data-value="{{$paket->id}}" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                                        
                                    </td>
                                        
                                    
                                 </tr>

                                    @endforeach
                                 @endforeach
                                  
                                 @if(\App\AdisyonPaketler::where('adisyon_id',$adisyon->id)->count()==0)
                                 <tr>
                                    <td colspan="5" style="text-align: center;">Kayıt Bulunamadı</td>
                                 </tr>
                                 @endif
                                 @else
                                <tr>
                                    <td colspan="5" style="text-align: center;">Kayıt Bulunamadı</td>
                                 </tr>
                                 @endif
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
                     <div
                        class="tab-pane fade show"
                        id="adisyonlar"
                        role="tabpanel"
                        >
                        <div class="card-box pd-10">
                           <table class="data-table table stripe hover nowrap" id="adisyon_liste_musteri">
                              <thead>
                                <th>Satış Tarihi</th>
                                <th>Planlanan Alacak Tarihi</th>
                              
                               
                                <th>Satış Türü</th>
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
                        <div class="row" style="margin-top:30px">
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
                                              <img id="croppedimg" src="{{(Auth::user()->profil_resim !== null ? Auth::user()->profil_resim : '/public/isletmeyonetim_assets/img/avatar.png' )}}" style="display:block;max-width: 100%;position: relative;height: auto;">
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
                    <p style="margin-top: 10px;"><b>Ad Soyad : </b>{{$musteri_bilgi->name}}</p>

                                  </div>
                                </div>
                                 <div class="col-md-6">
                                    <div class="card-body musteri_genel_bilgi_kart">
                                  
                                    
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
                                    <p><b>Cinsiyet : </b>
                                       @if($musteri_bilgi->cinsiyet === 0)
                                        Kadın @elseif($musteri_bilgi->cinsiyet===1) Erkek @else Belirtilmemiş @endif</p>
                                        <p><b>Notlar : </b>{{$portfoy->ozel_notlar}};
                                 </div>
                                 </div>
                               </div>
                                
                                 <div class="card-footer">
                                    <button onclick='modalbaslikata("<?php echo $musteri_bilgi->name;?> Bilgilerini Düzenle","");' class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#musteri-bilgi-modal">
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
                                                {{$hizmet->hizmetler->hizmet_adi}} &nbsp;
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
                                                {{$hizmet->hizmetler->hizmet_adi}} &nbsp;
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
                           Resimleri </h4>
                          </div>
                          <div class="col-md-6" style="text-align:right;">
                            <button class="btn btn-success brn-lg "  data-target="#musterifotoekle" data-toggle="modal" type="button">Yeni Resim Ekle</button>
                             
                          </div>
                        </div>
                       </div>
                         
                        <div class="card-box pd-20" style="margin-top: 20px">
                         <div id="buttonContainer">
                            @foreach($islemler as $islem)
                 
                                 <button class="btn btn-outline-primary" name="islemdetaygetir" data-toggle="modal" data-target="#islemdetayigetirmodal" type="button" name="islemdetaygetir" data-value="{{$islem->id}}"> @php
                $images = json_decode($islem->islem_fotolari, true);
            @endphp
                           <img src="/{{ $images[0] }}" alt="İşlem Fotoğrafı" style="width:100px; height:100px;">
         
                                  <br><br>{{date('d.m.Y',strtotime($islem->tarih))}}</button>
                            @endforeach
                         </div>
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
                                 <input type="text" required class="form-control date-picker" name="resimtarih" id="resimtarih" value="{{date('Y-m-d')}}" autocomplete="off">
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
                <input name="klasor_id" type="hidden" data-value='isl'>
               <div class="modal-content" style="min-height: 350px;">
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
                  <div class="modal-body" style="padding:20px">
                     
        
                      <div class="gallery-wrap" id="islembolumu">
                       
                   
              
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