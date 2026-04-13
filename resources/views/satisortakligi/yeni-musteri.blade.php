@extends('layout.layout_satisortakligi')
@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;   background-size: cover; background-position: center top;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">Yeni Müşteri Ekle</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde müşterilerinizi aşağıdaki formdan tek tek veya excel tablosundan toplu olarak ekleyebilirsiniz</p>
           
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--6">
      <div class="row">
        
        <div class="col-xl-12 order-xl-1">
          
          <div class="card">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="mb-0">Yeni Müşteri Girişi</h3>
                </div>
                <div class="col-md-4 text-right">
                  <form id="yeni_musteri_ekle_excel" method="post" enctype="multipart/form-data">
                    @csrf
                     <div class="form-group">
                        <label>Yüklenecek Liste(*.xls excel dosyası)</label>
                        <input type="file" id="excel_dosyasi_yeni" required name="excel_dosyasi_yeni" class="form-control">
                    </div>
                     <select data-toggle="select"  id="pasif_ortak_excel" name="pasif_ortak_excel" style="max-width:150px;float: left;">
                        <option value="0">Pasif Ortak (varsa)</option>
                          @foreach(\App\SatisOrtakligiModel\SatisOrtaklari::where('ana_satis_ortagi_id',Auth::user()->id)->where('pasif_ortak',true)->where('aktif',true)->get() as $ortak)

                          <option value="{{$ortak->id}}">{{$ortak->ad_soyad}}</option>
                          @endforeach
                         </select>
                    <button type="submit" style="float: left;" class="btn btn-success btn-sm" id="excelden_aktar"><i class="fa fa-excel"></i> &nbsp;Excel'den Aktar</button>
                    <a href="{{secure_asset('public/belgeler/ornek-excel.xlsx')}}" style="float: left;" class="btn btn-danger btn-sm" id="excel_ornek_indir"> <i class="fa fa-download"></i>&nbsp; Örnek Excel</a>
                   </form>
                </div>
                
              </div>
            </div>
            <div class="card-body">
              <form id="yeni_musteri_ekle" method="POST">
                 @csrf
                <h6 class="heading-small text-muted mb-4">Müşteri & İşletme Bilgileri</h6>
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="yetkili_adi">Yetkili Ad soyad (Zorunlu)</label>
                        <input type="text" id="yetkili_adi" required name="yetkili_adi" class="form-control">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="yetkili_telefon">Cep Telefonu </label>
                        <input type="tel" id="yetkili_telefon" data-inputmask =" 'mask' : '0(999)9999999'" name="yetkili_telefon" class="form-control">
                      </div>
                    </div>
                
                  </div>
                  
                </div>
                 
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="salon_adi">İşletme Adı(Zorunlu)</label>
                        <input id="salon_adi" name="salon_adi" required class="form-control" type="text">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefon_1">Firma Telefon 1 (Opsiyonel)</label>
                        <input id="telefon_1" name="telefon_1" data-inputmask =" 'mask' : '09999999999'" class="form-control" type="tel">
                      </div>
                    </div>
                     <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefon_2">Firma Telefon 2 (Opsiyonel)</label>
                        <input id="telefon_2" name="telefon_2" data-inputmask =" 'mask' : '09999999999'" class="form-control" type="tel">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-control-label" for="telefon_2">Firma Telefon 3 (Opsiyonel)</label>
                        <input id="telefon_3" name="telefon_3" data-inputmask =" 'mask' : '09999999999'" class="form-control" type="tel">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-control-label" for="adres">Adres (Zorunlu)</label>
                        <textarea id="adres" name="adres" required class="form-control"></textarea>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-control-label" for="il_id_yeni_musteri">İl (Zorunlu)</label>
                        
                         <select  data-toggle="select" id="il_id_yeni_musteri" name="il_id_yeni_musteri" class="form-control">
                          <option value="0">Seçiniz</option>
                          @foreach(\App\Iller::all() as $il)

                          <option value="{{$il->id}}">{{$il->il_adi}}</option>
                          @endforeach
                         </select>
                      </div>
                       
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-control-label" for="ilce_id_yeni_musteri">İlçe (Zorunlu)</label>
                         <select  data-toggle="select" id="ilce_id_yeni_musteri" name="ilce_id_yeni_musteri" class="form-control">
                           
                         </select>
                      </div>
                    </div>
                     <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-control-label" for="pasif_ortak">Pasif Ortak</label>
                         <select  data-toggle="select" id="pasif_ortak" name="pasif_ortak" class="form-control">
                           <option value="0">Seçiniz</option>
                          @foreach(\App\SatisOrtakligiModel\SatisOrtaklari::where('ana_satis_ortagi_id',Auth::user()->id)->where('pasif_ortak',true)->where('aktif',true)->get() as $ortak)

                          <option value="{{$ortak->id}}">{{$ortak->ad_soyad}}</option>
                          @endforeach
                         </select>
                      </div>
                    </div>
                   <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-control-label" for="satis_ortagi_notu">Notlar (Opsiyonel)</label>
                        <textarea id="satis_ortagi_notu" name="satis_ortagi_notu" class="form-control" rows="10"></textarea> 
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="my-4" />

                <button type="submit" style="width:100%;" class="btn btn-success">Yeni Müşteri Ekle</button> 
              </form>
             
            </div>
          </div>
        </div>
      </div>
      <div id="hata"></div>
       <button style="display: none" id="bilgi-basarı-ile-guncellendi-bildirim" type="button" class="btn btn-block btn-warning mb-3" data-toggle="modal" data-target="#modal-notification">Notification</button>
                  <div class="modal fade" id="modal-notification" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                    <div class="modal-dialog modal-success modal-dialog-centered modal-" role="document">
                      <div class="modal-content bg-gradient-success">
                        <div class="modal-header">
                          
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <div class="py-3 text-center">
                            <i class="ni ni-check-bold ni-3x"></i>
                            <h4 class="heading mt-4">Hesap bilgileri başarı ile eklendi!</h4>
                            
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button name="bildirim-kapat" id="bilgi-basarı-ile-guncellendi-bildirim-kapat" type="button" class="btn btn-white"  data-dismiss="modal">KAPAT</button>
                        
                        </div>
                      </div>
                    </div>
                  </div>
    </div>

 

 
@endsection