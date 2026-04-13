  <div
            id="yeni_senet_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="senet_formu"  method="POST">
                     <div class="modal-header" style="display:block">
                        <div class="row">
                           <div class="col-xs-3 col-3">
                              <h2 style="float: left;">Yeni Senet</h2>
                           </div>
                           <div class="col-xs-3 col-3">
                              <button type="button" data-toggle="modal" data-target="#senet_yeni_hizmet_modal" class="btn btn-outline-primary btn-lg btn-block" style="font-size:12px">Hizmet Ekle</button> 
                           </div>
                           <div class="col-xs-3 col-3">
                              <button type="button" data-toggle="modal" data-target="#senet_yeni_urun_modal" class="btn  btn-outline-primary btn-lg btn-block" style="font-size:12px">Ürün Ekle</button>
                           </div>
                           <div class="col-xs-3 col-3">
                              <button type="button" data-toggle="modal" data-target="#paket_satisi_modal_senet"  class="btn  btn-outline-primary btn-lg btn-block" style="font-size:12px">Paket Ekle</button>
                           </div>
                        </div>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                        @if($pageindex==111)
                        <input type="hidden" name="adisyon_id" value="{{$adisyon->id}}">
                        @endif
                        <div id="hizmetler_bolumu_senet">
                        </div>
                        <div id='urunler_bolumu_senet'>
                        </div>
                        <div id='paketler_bolumu_senet'>
                        </div>
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                              <label>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                              <select name="ad_soyad" class="form-control opsiyonelSelect" style="width: 100%;">
                                 <option></option>
                                 @foreach(\App\MusteriPortfoy::where('salon_id',$isletme->id)->where('aktif',true)->get() as $mevcutmusteri)
                                 @if($pageindex==111)
                                 @if($adisyon->user_id == $mevcutmusteri->user_id)
                                 <option selected value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @else
                                 <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @endif
                                 @else
                                 <option value="{{$mevcutmusteri->user_id}}">{{$mevcutmusteri->users->name}}</option>
                                 @endif
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                              <label>Vade Başlangıç Tarihi</label>
                              <input type="text" required class="form-control date-picker" name="vade_baslangic_tarihi" autocomplete="off">
                           </div>
                           <div class="col-md-3 col-sm-12 col-xs-12 col-12">
                              <label>Vade (Ay)</label>
                              <input type="tel" required name="vade" value=" " class="form-control">
                           </div>
                        </div>
                        <div class="row" style='background: #e2e2e2; margin:10px 0 10px 0;padding-bottom: 10px;'>
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6" >
                              <label>Ön Ödeme Tutarı</label>
                              <input type="tel" required {{($pageindex==111) ? 'disabled': ''}} name="on_odeme_tutari" id='on_odeme_tutari' value="" class="form-control try-currency">
                           </div>
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                              <label>Ön Ödeme Türü</label>
                              <select name="on_odeme_turu" class="form-control" >
                                 @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                                 <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-4 col-sm-4 col-xs-4 col-4">
                              <label>Senet Tutarı</label>
                              <input type="tel" required {{($pageindex==111) ? 'disabled': ''}} name="senet_tutar" id='senet_tutar' value="" class="form-control try-currency">
                           </div>
                           <div class="col-md-4 col-sm-4 col-xs-4 col-4">
                              <label>T.C NO</label>
                              <input type="tel" required name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'" value="{{($pageindex==111) ? $adisyon->musteri->tc_kimlik_no : ''}}" class="form-control">
                           </div>
                           <div class="col-md-4 col-sm-4 col-xs-4 col-4">
                              <label>Senet Türü</label>
                              <select name="senet_turu" class="form-control">
                                 <option value="1">Nakden</option>
                                 <option value="2">Malen</option>
                                 <option value="3">Hizmet</option>
                              </select>
                           </div>
                           <div class="col-md-12">
                              <label>Adres</label>
                              <input type="tel" required name="adres" value="{{($pageindex==111) ? $adisyon->musteri->adres : ''}}" class="form-control">
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                              <label>Kefil Adı ve Soyadı</label>
                              <input type="text" name="kefil_adi" required  class="form-control">
                           </div>
                           <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                              <label>Kefil T.C No</label>
                              <input type="text" name="kefil_tc_vergi_no" required data-inputmask =" 'mask' : '99999999999'" class="form-control">
                           </div>
                           <div class="col-md-12">
                              <label>Kefil Adres</label>
                              <input type="text" name="kefil_adres" required name="adres" value="" class="form-control">
                           </div>
                        </div>
                        <div class="modal-footer">
                           <button type="submit" disabled class="btn btn-success">
                           Yeni Senet Oluştur
                           </button>
                           <button  
                              type="button"
                              class="btn btn-danger"
                              data-dismiss="modal"
                              >
                           <i class="fa fa-times"></i>      
                           Kapat
                           </button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>