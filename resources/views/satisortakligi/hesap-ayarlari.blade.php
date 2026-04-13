@extends('layout.layout_satisortakligi')
@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 300px;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-7 col-md-10">
            <h1 class="display-2 text-white">{{$bayi_bilgileri->ad_soyad}}</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde profil bilgilerinizi görüntüleyebilir ve düzenleyebilirsiniz.</p>
           
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--6">
      <div class="row">
        <div class="col-xl-4 order-xl-2" style="display:none">
          <div class="card card-profile">
          
            <div class="row justify-content-center">
              <div class="col-lg-3 order-lg-2">
                <div class="card-profile-image">
                  <a id="resim_yukle_link" href="#">
                    <img id="profil_resmi_gorsel" src="{{secure_asset($bayi_bilgileri->profil_resmi)}}" class="rounded-circle" style="background: #fff;">
                  </a>
                  <form id="profil_resmi_formu" enctype="multipart/form-data" method="POST">
                  	@csrf
                  	<input type="file" id="profil_resmi" name="profil_resmi" style="display: none" accept="image/*">
                  </form>
                </div>
              </div>
            </div>
           
            <div class="card-body pt-0">
              
              <div class="text-center" style="margin-top: 100px">
                <h5 class="h3">
                 {{$bayi_bilgileri->ad_soyad}}  

                </h5>
                <button id="hesap_silme_talebi" style="display:none;" data-toggle="modal" data-target="#sozlesme_fesih_talebi"  class="btn btn-danger btn-sm">Sözleşme Fesih Talebi / Hesap Silme</button>
              
              </div>
            </div>
          </div>
          
        
        </div>
        <div class="col-md-6">
          
          <div class="card">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Profil Bilgileri</h3>
                </div>
                
              </div>
            </div>
            <div class="card-body">
              <form id="profil_satis_ortagi_bilgi" method="POST">
              	 @csrf
                <h6 class="heading-small text-muted mb-4">Kullanıcı Bilgileri</h6>
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label class="form-control-label" for="input-username">Ad soyad</label>
                        <input type="text" id="ad_soyad" name="ad_soyad" class="form-control"  value="{{$bayi_bilgileri->ad_soyad}}">
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label class="form-control-label" for="input-email">E-mail adresi</label>
                        <input type="email" id="email" name="email" class="form-control" value ="{{$bayi_bilgileri->email}}">
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="form-group">
                        <label class="form-control-label" for="input-first-name">Telefon</label>
                        <input type="tel" id="telefon" name="telefon" class="form-control"  value="{{$bayi_bilgileri->telefon}}">
                      </div>
                    </div>
                    <div class="col-lg-4">
                      
                    </div>
                    <div class="col-lg-4">
                       <button type="submit" id="profil_firma_bilgi_guncelle" name="profil_firma_bilgi_guncelle" style="width:100%;" class="btn btn-success">Bilgileri Güncelle</button> 
                    </div>
                    <div class="col-lg-4">
                    </div>
                  </div>
                  
                </div>
                 
                 
               </form>
                 
              
                
                
               
               
             
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3>Banka Hesapları <button type="button" title="Yeni banka bilgisi ekle" id="yeni_banka_bilgisi_ekle" class="btn btn-primary" data-toggle="modal" data-target="#banka-bilgi-ekleme" style="float: right;"> 
                         <span class="btn-inner--icon"><i class="ni ni-credit-card"></i> Banka Bilgisi Ekle</span>
                      </button></h3>
            </div>
            <div class="card-body">
                <table class="table align-items-center table-flush" id="satis_ortagi_banka_bilgileri">
                      <thead class="thead-light">
                        <tr>
                          
                         
                          <th>Banka</th>
                          <th>IBAN</th>
                          <th>Şube Kodu</th>
                          <th>Hesap No</th>
                          <th>Alıcı</th>                       
                          
                          <th>İşlemler</th>
                           
                        </tr>
                      </thead>
                      <tbody class="list" id="satis_ortagi_banka_bilgileri_liste">
                       
                     
                     
                      </tbody>
                   
                     
                       
                    </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        
      </div>
   
    </div>

    <div class="modal fade" id="banka-bilgi-ekleme" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
          <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
              <div class="modal-content">
                <div class="modal-header bg-success">
                  <h6 class="modal-title text-white" id="modal-title-default">Banka Bilgileri</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-white">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <form id="satis-ortagi-banka-bilgileri" method="POST">
                          @csrf
                          <input type="hidden" name="satis_ortagi_banka_id" id="satis_ortagi_banka_id">
                          <div class="form-group">
                            <label>Banka</label>
                            <select name="satis_ortagi_banka_adi" id="satis_ortagi_banka_adi" class="form-control">
                              @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $bankalar)
                              <option value="{{$bankalar->id}}">{{$bankalar->banka}}</option>
                              @endforeach()
                            </select>
                          </div>
                           <div class="form-group">
                            <label>IBAN (zorunlu)</label>
                            <input type="text" required name="satis_ortagi_hesap_iban" id="satis_ortagi_hesap_iban" data-inputmask =" 'mask' : 'TR999999999999999999999999'" class="form-control">
                          </div>
                           <div class="form-group">
                            <label>Şube Kodu (opsiyonel)</label>
                            <input type="text" name="satis_ortagi_hesap_sube_kodu" id="satis_ortagi_hesap_sube_kodu" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Hesap No (opsiyonel)</label>
                            <input type="text" name="satis_ortagi_hesap_no" id="satis_ortagi_hesap_no" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Alıcı Hesap Adı (zorunlu)</label>
                            <input type="text" required name="satis_ortagi_alici_hesap_adi" id="satis_ortagi_alici_hesap_adi" class="form-control">
                          </div>
                          <div class="form-group" style="text-align: center;">
                            <button type="submit" class="btn btn-success">Kaydet</button>
                            <button type="button" class="btn btn-danger">Kapat</button>

                          </div>
                       
                         
                    </form>

                  </div>
                        
                </div>
              </div>
          </div>
    </div>
    <div class="modal fade" id="sozlesme_fesih_talebi" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
          <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
              <div class="modal-content">
                <div class="modal-header bg-success">
                  <h6 class="modal-title text-white" id="modal-title-default">Sözleşme Fesih / Hesap Silme Talebi</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-white">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <form id="sozlesme_fesih_hesap_silme" method="POST">
                          @csrf
                           <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                            <span class="alert-text"><strong>ÖNEMLİ BİLGİ!</strong> Sözleşmenizin feshi; fesih tarihine kadar kazandırdığınız müşterilerden doğan komisyon haklarınızı etkilemeyecek olup, fesih sonrasında yeni kazançlar sağlayamayacaksınız.<br><br>Yazılı talebiniz tarafımıza ulaştıktan sonra form üzerinden verilerinizin ve hesabınızın da silinmesini talep etmiş olmanız durumunda 30 iş günü içerisinde verileriniz kalıcı olarak silinecektir.</span>
                             
                          </div>
                         
                           <div class="form-group">
                            <label>Sözleşme fesih talebinizin nedenini kısaca yazınız. </label>
                            <textarea required name="fesih_nedeni" class="form-control"></textarea>
                          </div>
                           <div class="custom-control custom-checkbox mb-3">
                        <input class="custom-control-input" name="hesap_sil" id="hesap_sil" type="checkbox">
                        <label class="custom-control-label" for="hesap_sil">Sistemdeki tüm verilerim ve hesabım silinsin</label>
                      </div> 
                          <div class="form-group" style="text-align: center;">
                            <button type="submit" class="btn btn-success">Talebi Gönder</button>
                            <button type="button" class="btn btn-danger">Kapat</button>

                          </div>
                       
                         
                    </form>

                  </div>
                        
                </div>
              </div>
          </div>
    </div>
    
      
@endsection