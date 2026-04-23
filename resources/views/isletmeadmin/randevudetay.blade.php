@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<form id="adisyon_form">
   {!!csrf_field()!!}
   <input type="hidden" name="randevu_id" value="{{$randevu->id}}">
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-6">
            <div class="title">
               <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  </li>
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim/musteriler{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Adisyonlar</a>
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
      <div class="col-md-9">
         

                <div class="card"  style="margin-bottom:20px">
            <div class="card-header" style="background-color: #5C008E;">
               <h5 style="position: relative;float: left;color: #fff">Hizmet Satışları</h5>
                           
                           <a href="#" role="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyona_hizmet_ekle" style="position: relative;
    float: right;
    color: #fff;
   
    
    font-size: 20px;
    margin: 0;
    z-index: 5;
"><i class="fa fa-plus"></i> Ekle</a>
                 

                  </div>
           
                <div class="card-body">
               <div class="hizmetler_bolumu_adisyon">
                  @foreach($hizmetler as $hizmet)
                  <input type="hidden" name="randevuhizmetidleri[]" value="{{$hizmet->id}}">
                  <div class="row" data-value="0" style="background-color:#e2e2e2;margin-bottom: 5px;">
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>İşlem Tarihi</label>

                           <input type="text" required class="form-control date-picker" name="hizmet_islem_tarihi" value="{{date('d/m/Y')}}">
                        </div>
                        </div>
                     
                     <div class="col-md-2">
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
                     <div class="col-md-2">
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
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Durum</label>
                           <select name="adisyon_durum" id="adisyon_durum" class="form-control">
                       
                        <option >Geldi</option>
                        <option selected>Bekliyor</option>
                        <option >Gelmedi</option>

                    </select>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Fiyat (₺)</label>
                           <input type="text" inputmode="decimal" class="form-control hy-fiyat-input" name="hizmet_fiyati_adisyon[]" placeholder="0,00" autocomplete="off" value="{{$hizmet->fiyat}}">
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label style="visibility: hidden;width: 100%;">Kaldır</label>
                           <button type="button" name="hizmet_formdan_sil_2"  data-value="{{$hizmet->id}}" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                        </div>
                     </div>
                  </div>
                  @endforeach
               </div>
            </div>
        
           
         </div>
    
            <div class="card"  style="margin-bottom:20px">
            <div class="card-header" style="background-color: #5C008E;">
               <h5 style="position: relative;float: left;color: #fff">Ürün Satışları</h5>
                           
                           <a href="#" role="button" data-toggle="modal" data-target="#urun_satisi_modal" id="adisyona_urun_ekle" style="position: relative;
  float: right;
    color: #fff;
   
    
    font-size: 20px;
    margin: 0;
    z-index: 5;
"><i class="fa fa-plus"></i> Ekle</a>
                 

                  
             
            </div>
      
               <div class="card-body table-responsive">
               

               <table class="table">
                  <thead>
                     <tr>
                        <th scope="col">Ürün</th>
                        <th scope="col">Adet</th>
                        <th scope="col">Fiyat (₺)</th>
                        
                        <th scope="col"></th>
                     </tr>
                  </thead>
                  <tbody id="adisyon_detay_urun_tablo">
                     @foreach($urunler as $urun)
                     <tr>
                        <td>{{$urun->urunler->urun_adi}} </td>
                        <td>{{$urun->adet}}</td>
                        <td>
                           <input type="hidden" name="urun_fiyati_adisyon[]" value="{{$urun->urunler->fiyat}}"> 
                           {{$urun->urunler->fiyat}}

                        </td>
                      <td style="width:30px">
                          
                           <button type="button" name="urun_formdan_sil" data-value="{{$urun->id}}" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                           
                          
                        </td>
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
    
   
            <div class="card"  style="margin-bottom:20px">
          <div class="card-header" style="background-color: #5C008E;">
               <h5 style="position: relative;float: left;color: #fff">Paket Satışları</h5>
                           
                           <a href="#" role="button" data-toggle="modal" data-target="#paket_satisi_modal" id="adisyona_paket_ekle" style="position: relative;
    float: right;
    color: #fff;
   
    
    font-size: 20px;
    margin: 0;
    z-index: 5;
"><i class="fa fa-plus"></i> Ekle</a>
                 

                     
               
             
            </div>
       
               <div class="card-body table-responsive">
               

               <table class="table">
                  <thead>
                     <tr>
                        <th scope="col">İşlem Tarihi</th>
                        <th scope="col">Paket Adı</th>
                        <th scope="col">Personel</th>
                        <th scope="col">Seans</th>
                        <th scope="col">Durum</th>
                        <th scope="col"></th>
                     </tr>
                  </thead>
                  <tbody id="adisyon_detay_paket_tablo">
                     @foreach($urunler as $urun)
                     <tr>
                        <td>{{$urun->urunler->urun_adi}} </td>
                        <td>{{$urun->adet}}</td>
                        <td>
                           <input type="hidden" name="urun_fiyati_adisyon[]" value="{{$urun->urunler->fiyat}}"> 
                           {{$urun->urunler->fiyat}}

                        </td>
                      <td style="width:30px">
                          
                           <button type="button" name="paket_formdan_sil" data-value="{{$urun->id}}" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                           
                          
                        </td>
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
       
      <div class="col-md-3">
         <div id="odeme_kayit_bolumu">
         <h2>Ödeme</h2>
         <div class="card-box pd-20"  style="margin-bottom:20px">
            <div class="row">
               <div class="col-9 col-xs-9 col-sm-9">
                  <b>Hizmet ve Ürünler Toplamı</b>
               </div>
               <div class="col-3 col-xs-3 col-sm-3 text-right">
                  <span id="hizmet_urunler_toplam_fiyat">100</span> ₺
               </div>
            </div>
             

         </div>
         <div class="card-box pd-20"  style="margin-bottom:20px">
            <div class="row">
               <div class="col-6 col-xs-6 col-sm-6">
                  <b>Tahsilatlar</b>
               </div>
               <div class="col-6 col-xs-6 col-sm-6  text-right">
                  <div class="btn-group">
                    <button type="button" id="adisyon_islemleri" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      İşlemler
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#tahsilat_modal">Yeni Tahsilat</a>
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#alacak_modal_adisyon">Yeni Alacak</a>
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#senet_modal_adisyon">Yeni Senet</a>
                      
                    </div>
                  </div>
               </div>
               <div class="col-md-12">
                  
                  <table class="table" style="margin-top:20px">
                    
                     <tbody id="tahsilat_listesi">
                        @foreach($tahsilatlar as $tahsilat)
                        <tr>
                           <td>{{date('d.m.Y',strtotime($tahsilat->odeme_tarihi))}} </td>
                           <td>{{$tahsilat->tutar}} </td>
                           <td>
                              {{$tahsilat->odeme_yontemi->odeme_yontemi}}
                           </td>
                           <td>
                              <button type="button" name="tahsilat_adisyondan_sil" data-value="{{$tahsilat->id}}" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                           </td>
                            
                        </tr>
                        @endforeach
                       
                     </tbody>
                     <tfoot id="tahsilat_durumu">
                        @if($tahsilatlar->count() == 0)
                        <tr>
                           <td colspan="4" class="text-center" style="color:#ff0000">
                              
                              Kayıtlı tahsilat bulunmamaktadır!
                           </td>  
                        </tr>
                         @else
                         <tr>
                             <td colspan="3">
                              Tahsil Edilen Tutar (₺): 
                           </td>
                           <td id="tahsil_edilen_tutar">
                              {{$tahsilat_tutari}}
                           </td>

                         </tr>
                         <tr>
                             <td colspan="3">
                              Tahsil Edilecek Kalan Tutar (₺): 
                           </td>
                           <td id="tahsil_edilecek_kalan_tutar">
                              {{$toplam_tutar-$tahsilat_tutari}}
                           </td>
                         </tr>
                          @endif
                     </tfoot>
                  </table>


               </div>
            </div>
         </div>
          <button type="submit" class="btn btn-success" style="width:100%;margin-top: 10px;">Değişiklikleri Kaydet</button></div>
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
                           <input type="tel" class="form-control" name="hizmet_suresi[]" required>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Fiyat (₺)</label>
                           <input type="text" inputmode="decimal" class="form-control hy-fiyat-input" name="hizmet_fiyat[]" placeholder="0,00" autocomplete="off" required>
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
   id="tahsilat_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="adisyon_tahsilat"  method="POST">
            <div class="modal-header">
               <h2>Yeni Tahsilat</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="randevu_id" value="{{$randevu->id}}">
                
               <div class="row" data-value="0">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="tahsilat_tarihi" value="{{$randevu->tarih}}">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="tahsilat_tutari" class="form-control">
                        </div>
                     </div>
                     <div class="col-md-12">
                        <div class="form-group">
                           <label>Ödeme Yönetmi</label>
                           <select class="form-control" name="odeme_yontemi">
                           @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                                 <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                           @endforeach
                           </select>
                        </div>
                     </div>
               </div>

              
               <div class="row">
                   
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="tahsilat_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
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
<div
   id="alacak_modal_adisyon"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="max-height: 90%;">
         <form id="alacak_formu"  method="POST">
            <div class="modal-header">
               <h2>Yeni Alacak</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="randevu_id" value="{{$randevu->id}}">
                
               <div class="row" data-value="0">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tarih</label>
                           <input type="text" required class="form-control date-picker" name="olusturma_tarih" value="{{date('Y-m-d')}}">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Tutar (₺)</label>
                           <input type="tel" name="tahsilat_tutari" required class="form-control">
                        </div>
                     </div>
                      <div class="col-md-12">
                        <div class="form-group">
                           <label>Planlanan Ödeme Tarihi</label>
                            <input type="text" required class="form-control date-picker" name="planlanan_odeme_tarihi">
                        </div>
                     </div>
                     
               </div>

              
               <div class="row">
                   
                  <div class="col-md-12">
                     <div class="form-group">
                        <label>Notlar</label>
                        <textarea name="alacak_notlari" class="form-control"></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
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
<div id="hata"></div>
                        </div>

<div id="adisyon_yeni_hizmet_modal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
      <div class="modal-content" style="width: 950px; max-height: 90%">
         <div class="modal-body">
            {!!csrf_field()!!}
            <input type="hidden" name="adisyon_yeni_hizmet" id="adisyon_yeni_hizmet">
            <h2>Yeni Hizmet Satışı</h2>
            <div class="row">
               <div class="col-md-12">
                  <div class="form-group">
                      <label>Personel</label>
                                       <select name="randevupersonelleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\Personeller::where('salon_id',$isletme->id)->get() as $personel)
                                          <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                          @endforeach
                                       </select>
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="form-group">
                     <label>Hizmet</label>
                                       <select name="randevuhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->get() as $hizmetliste)
                                          <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                          @endforeach
                                       </select>
                  </div>
               </div>
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
   </div>
</div>

<div
      id="adisyon_yeni_hizmet_modal"
      class="modal modal-top fade calendar-modal"
      >
      <div class="modal-dialog modal-dialog-centered"  style="max-width: 500px;">
         <div class="modal-content" style="width: 950px; max-height: 90%;">
            <form id="adisyon_hizmet_formu"  method="POST">

               <div class="modal-body">
                {!!csrf_field()!!}
                  <input type="hidden" name="urun_id" id="urun_id" value="0">
                  <h2 class="text-blue h2 mb-10" id="urun_modal_baslik">Yeni Hizmet Satışı</h2>
                  <div class="row" data-value="0">
                          <div class="col-md-12">
                             <div class="form-group">
                                   <label>Personel</label>
                                       <select name="randevupersonelleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\Personeller::where('salon_id',$isletme->id)->get() as $personel)
                                          <option value="{{$personel->id}}">{{$personel->personel_adi}}</option>
                                          @endforeach
                                       </select>
                             </div>
                          </div>
                          <div class="col-md-12">
                             <div class="form-group">
                                <label>Hizmet</label>
                                       <select name="randevuhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                          @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->get() as $hizmetliste)
                                          <option value="{{$hizmetliste->hizmet_id}}">{{$hizmetliste->hizmetler->hizmet_adi}}</option>
                                          @endforeach
                                       </select>
                             </div>
                          </div>
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
               </form>
         </div>
         
      </div>      

</div>
@endsection