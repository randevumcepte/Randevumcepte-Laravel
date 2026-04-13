@extends('layout.layout_isletmeadmin')
@section('content')
<form id="adisyon_form">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-6">
            <div class="title">
               <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim">Ana Sayfa</a>
                  </li>
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim/musteriler">Müşteriler</a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">
                     {{$sayfa_baslik}}
                  </li>
               </ol>
            </nav>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-md-7">
         <div class="card-box mb-10">
            <div class="card-header">
               {{$randevu->users->name}} : {{$randevu->users->cep_telefon}}
            </div>
            <div class="card-body">
               <div class="from-group">
                  <label>Randevu Tarihi & Saati</label>
                  <div class="row">
                     <div class="col-9 col-xs-9 col-sm-9">
                        <input type="text" class="form-control date-picker" name="randevu_tarihi" value="{{$randevu->tarih}}">             
                     </div>
                     <div class="col-3 col-xs-3 col-sm-3">
                        <input type="time" name="randevu_saati" class="form-control" value="{{$randevu->saat}}">
                     </div>
                     <div class="col-md-12">
                        <label>Müşteri Notu</label>
                        <textarea class="form-control" name="notlar">{{$randevu->notlar}}</textarea>
                     </div>
                     <div class="col-md-12">
                        <label>Personel Notu</label>
                        <textarea class="form-control" name="personel_notu">{{$randevu->personel_notu}}</textarea>
                     </div>
                     <div class="col-md-12" style="margin-top:20px">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width:100%">

                           <label class="btn btn-outline-secondary">
                           <input  
                           type="radio"
                           name="options"
                           id="option1"
                           autocomplete="off"
                           style="width:33.3333333%"  
                           {{($randevu->randevuya_geldi != null) ? 'disabled' : ''}}
                           {{($randevu->randevuya_geldi == null) ? 'checked' : ''}}
                           
                           />
                           Belirtilmemiş
                           </label>
                           <label class="btn btn-outline-secondary">
                           <input
                           type="radio"
                           name="options"
                           id="option2"
                           autocomplete="off" style="width:33.3333333%"
                           {{($randevu->randevuya_geldi != null) ? 'disabled' : ''}}
                            {{($randevu->randevuya_geldi == 1) ? 'checked' : ''}}
                          
                           />
                           Geldi
                           </label>
                           <label class="btn btn-outline-secondary">
                           <input
                           type="radio"
                           name="options"
                           id="option3"
                           autocomplete="off" style="width:33.3333333%"
                           {{($randevu->randevuya_geldi != null) ? 'disabled' : ''}}
                            {{($randevu->randevuya_geldi == 0 && $randevu->randevuya_geldi != null) ? 'checked' : ''}}
                           />
                           Gelmedi
                           </label>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="card-box   mb-10">
            <div class="card-header">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     Hizmetler
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button type="button" class="btn btn-outline-success" data-toggle="modal"
                        data-target="#hizmet_modal">
                     <i class="fa fa-plus"></i> Yeni Hizmet 
                     </button>
                  </div>
               </div>
            </div>
            <div class="card-body">
               <div class="hizmetler_bolumu_adisyon">
                  @foreach($hizmetler as $hizmet)
                  <div class="row" data-value="0" style="background-color:#e2e2e2;margin-bottom: 5px;">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Personel</label>
                           <select name="randevupersonelleri[]" class="form-control custom-select2" style="width: 100%;">
                              @foreach($personeller as $personel)
                              @if($hizmet->personel_id == $personel->id)
                              <option selected value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                              @else
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                              @endif
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Hizmet</label>
                           <select name="randevuhizmetleri[]" class="form-control custom-select2" style="width: 100%;">
                              @foreach($sunulanhizmetler as $hizmetliste)
                              @if($hizmet->hizmet_id == $hizmetliste->hizmet_id)
                              <option selected value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                              @else
                              <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                              @endif
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Süre (dk)</label>
                           <input type="tel" class="form-control" name="hizmet_suresi[]" value="{{$hizmet->sure_dk}}" >
                        </div>
                     </div>
                     <div class="col-md-5">
                        <div class="form-group">
                           <label>Fiyat (₺)</label>
                           <input type="tel" class="form-control" name="hizmet_fiyati[]" value="{{$hizmet->fiyat}}">
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">Kaldır</label>
                           <button type="button" name="hizmet_formdan_sil_2"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                        </div>
                     </div>
                  </div>
                  @endforeach
               </div>
            </div>
         </div>
         <div class="card-box   mb-10">
            <div class="card-header">
               <div class="row">
                  <div class="col-6 col-xs-6 col-sm-6">
                     Ürün Satışları
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button type="button" class="btn btn-outline-success"  data-toggle="modal"
                        data-target="#urun_satisi_modal">
                     <i class="fa fa-plus"></i> Yeni Ürün Satışı 
                     </button>
                  </div>
               </div>
            </div>
            <div class="card-body table-responsive">
               

               <table id="adisyon_detay_urun_tablo" class="table table-striped">
                  <thead>
                     <tr>
                        <th scope="col">Ürün</th>
                        <th scope="col">Adet</th>
                        <th scope="col">Fiyat (₺)</th>
                        <th scope="col">Satıcı</th>
                        <th scope="col"></th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($urunler as $urun)
                     <tr>
                        <td>{{$urun->urunler->urun_adi}} </td>
                        <td>{{$urun->adet}}</td>
                        <td>{{$urun->urunler->fiyat}}</td>
                        <td>{{$urun->personeller->personel_adi}}</td>
                        <td></td>
                     </tr>
                     @endforeach
                     @if($urunler->count() == 0)
                     <tr>
                        <td colspan="5" class="text-center">
                           Kayıt Bulunamadı
                        </td>
                     </tr>
                     @endif 
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <div class="col-md-5">
         <h2>Ödeme</h2>
         <div class="card-box  mb-10">
            <div class="row">
               <div class="col-9 col-xs-9 col-sm-9">
                  <b>Hizmet ve Ürünler Toplamı</b>
               </div>
               <div class="col-3 col-xs-3 col-sm-3 text-right">
                  <span id="hizmet_urunler_toplam_fiyat">100</span> ₺
               </div>
            </div>
             

         </div>
         <div class="card-box  mb-10s">
            <div class="row">
               <div class="col-6 col-xs-6 col-sm-6">
                  <b>Tahsilatlar</b>
               </div>
               <div class="col-6 col-xs-6 col-sm-6  text-right">
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      İşlemler
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#tahsilat_modal">Yeni Tahsilat</a>
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#alacak_modal">Yeni Alacak</a>
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#senet_modal">Yeni Senet</a>
                      
                    </div>
                  </div>
               </div>
               <div class="col-md-12">
                  
                  <table class="table table-striped" style="margin-top:20px">
                     <thead>
                        <tr>
                           <th scope="col">Tarih</th>
                           <th scope="col">Tutar (₺)</th>
                           <th scope="col">Satici</th>
                           
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($tahsilatlar as $tahsilat)
                        <tr>
                           <td>{{date('d.m.Y',strtotime($tahsilat->created_at))}} </td>
                           <td>{{$tahsilat->tutar}} </td>
                           <td>
                              @if($tahsilat->odeme_yontemi == 1)
                                 Nakit
                              @elseif($tahsilat->odeme_yontemi == 2)
                                 Havale/EFT
                              @elseif($tahsilat->odeme_yontemi == 3)
                                 Kredi Kartı
                              @endif
                           </td>
                            
                        </tr>
                        @endforeach
                        @if($tahsilatlar->count() == 0)
                        <tr>
                           <td colspan="3" class="text-center" style="#ff0000">
                             Kayıtlı tahsilat bulunmamaktadır
                           </td>
                        </tr>
                        @endif 
                     </tbody>
                  </table>


               </div>
            </div>
         </div>
          <button type="submit" class="btn btn-success" style="width:100%;margin-top: 10px;">Değişiklikleri Kaydet</button>
      </div>
   </div>
</form>
<div
   id="hizmet_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="randevu_hizmetler"  method="POST">
            <div class="modal-header">
               <h2>Yeni Hizmet</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="randevu_id" id="randevu_id" value="{{$randevu->id}}">
               <div class="hizmetler_bolumu">
                  <div class="row" data-value="0">
                     <div class="col-md-3">
                        <div class="form-group">
                           <label>Personel</label>
                           <select name="randevupersonelleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                              @foreach($personeller as $personel)
                              <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                           <label>Hizmet</label>
                           <select name="randevuhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                              @foreach($sunulanhizmetler as $hizmetliste)
                              <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Süre (dk)</label>
                           <input type="tel" class="form-control" name="hizmetsuresiyeni[]" required>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Fiyat (₺)</label>
                           <input type="tel" class="form-control" name="hizmetfiyatiyeni[]" required>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">Kaldır</label>
                           <button type="button" name="hizmet_formdan_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <button type="button" id="bir_hizmet_daha_ekle" class="btn btn-secondary btn-lg btn-block">
                        Bir Hizmet Daha Ekle
                        </button>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-success">
               Kaydet
               </button>
               <button id="modal_kapat"
                  type="button"
                  class="btn btn-danger"
                  data-dismiss="modal"
                  >
               Kapat
               </button>
            </div>
      </div>
      </form>
   </div>
</div>

<div
   id="urun_satisi_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="adisyon_urun_satisi"  method="POST">
            <div class="modal-header">
               <h2>Yeni Ürün Satışı</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="randevu_id" id="randevu_id" value="{{$randevu->id}}">
                
               <div class="row" data-value="0">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="urun_satis_tarihi">
                        </div>
                     </div>
               </div>

               <div class="urunler_bolumu"> 
                  <div class="row" data-value="0">
                        <div class="col-md-6">
                           <div class="form-group">
                              <label>Ürün</label>
                              <select name="urunyeni[]" class="form-control custom-select2" style="width: 100%;">
                                 @foreach($tum_urunler as $urun)
                                 <option value="{{$urun->id}}">{{$urun->urun_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                          
                        </div>
                         <div class="col-md-2">
                           <div class="form-group">
                              <label>Adet</label>
                              <input type="tel" required name="urun_adedi[]" value="1" class="form-control">
                           </div>
                          
                        </div>
                         <div class="col-md-2">
                           <div class="form-group">
                              <label>Fiyat</label>
                              <input type="tel" required name="urun_fiyati[]" value="{{$tum_urunler[0]->fiyat}}" class="form-control">
                           </div>
                           
                        </div>
                        <div class="col-md-2">
                           <div class="form-group">
                              <label style="visibility: hidden;width: 100%;">Kaldır</label>
                              <button type="button" name="urun_formdan_sil"  data-value="0" class="btn btn-danger" disabled><i class="icon-copy fa fa-remove"></i></button>
                           </div>
                        </div>
                  </div>
                      
               </div>
              
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <button type="button" id="bir_urun_daha_ekle" class="btn btn-secondary btn-lg btn-block">
                        Bir Ürün Daha Ekle
                        </button>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Satıcı</label>
                        <select name="urun_satici[]" class="form-control custom-select2" style="width: 100%;">
                           @foreach($personeller as $personel)
                           <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="satis_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-success">
               Kaydet
               </button>
               <button id="modal_kapat"
                  type="button"
                  class="btn btn-danger"
                  data-dismiss="modal"
                  >
               Kapat
               </button>
            </div>
      </div>
      </form>
   </div>
</div>

<div id="hata"></div>
@endsection