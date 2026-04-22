@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
{!!csrf_field()!!}
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
               <li class="breadcrumb-item active" aria-current="page">
                  {{$sayfa_baslik}}
               </li>
            </ol>
         </nav>
      </div>
      <div class="col-md-6 col-sm-6"> 
      </div>
   </div>
</div>
<form id="adisyon_tahsilat"  method="POST">

<div class="row">
   <div class="col-md-9">
      <div class="card-box pd-5"  style="margin-bottom:20px">
        
            <input type="hidden" name='sube' value="{{$isletme->id}}">
            <input type="hidden" name="tahsilat_ekrani" id='tahsilat_ekrani' value="1">
            <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari' >
             <input type="hidden" name="adisyonsuz" id='adisyonsuz' value ='1'>
            <input type="hidden" name="adisyon_id" id="session_adisyon_id" value="">
            <div class="modal-header">
               <div class="col-6 col-xs-6 col-sm-6">
                  <h2>Tahsilat</h2>
               </div>
               <div class="col-6 col-xs-6 col-sm-6">
                  <div class="row">
                     @if(Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))
                     <div class="col-6 col-xs-6 col-sm-6"></div>
                     @endif
                     <div class="col-md-6 col-6 col-xs-6 col-sm-6" >
                        <div class="from-group"  >
                           <select name='tahsilat_musteri_id' style="width:100%"  class="form-control custom-select2 musteri_secimi"  id='tahsilat_musteri_id' >
                              <option></option>
                           </select>
                        </div>
                     </div>
                     @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel'))
                     <div class="col-md-6 col-6 col-xs-6 col-sm-6">
                        <button type="button" class="btn btn-primary btn-block yanitsiz_musteri_ekleme" data-toggle="modal"  data-target="#musteri-bilgi-modal">+ Yeni Müşteri/Danışan Ekle</button>
                     </div>
                     @endif
                  </div>
               </div>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <div class="row">
                  <div class="col-md-12">
                     <div class="row" style="margin-bottom: 20px;">
                        <div class="col-2">
                           <button disabled type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Hizmet Ekle</button>
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                           <button disabled type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value=''onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn  btn-danger  btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Ürün Ekle</button>
                        </div>
                        <div class="col-2" style="padding-left: 0;">
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
                           @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
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
                           @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
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
@endsection