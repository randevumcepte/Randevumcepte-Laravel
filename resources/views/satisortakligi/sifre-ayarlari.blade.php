@extends('layout.layout_satisortakligi')
@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; background-size: cover; background-position: center top;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-7 col-md-10">
            <h1 class="display-2 text-white">{{$bayi_bilgileri->ad_soyad}}</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde şifrenizi ve profil resminizi değiştirebilirsiniz.</p>
           
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
                    <img id="profil_resmi_gorsel" src="{{secure_asset($bayi_bilgileri->profil_resmi)}}" class="rounded-circle" style="    background: #fff;">
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
                <div class="h5 font-weight-300">
                  <i class="ni location_pin mr-2"></i>{{$bayi_bilgileri->firma_unvani}}
                </div>
                <div class="h5 mt-4">
                  <i class="ni location_pin mr-2"></i>{{$bayi_bilgileri->adres}}
                </div>
              
              </div>
            </div>
          </div>
          
        
        </div>
        <div class="col-xl-12 order-xl-1">
          
          <div class="card">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Şifre Ayarları</h3>
                </div>
                
              </div>
            </div>
            <div class="card-body">
              <form id="sifre_bilgi_satis_ortagi" method="POST">
                 @csrf
                  
                <div class="pl-lg-4">
                  <div id="alert_box">
                       
                  </div>
             
                  <div class="form-group">
                    <input type="password" required id="mevcut_sifre" name="mevcut_sifre" class="form-control" placeholder="Mevcut şifre">
                  </div>
                  <div class="form-group">
                    <input type="password" required id="yeni_sifre" name="yeni_sifre" class="form-control" placeholder="Yeni şifre">
                  </div>
                  <div class="form-group">
                    <input type="password" required id="yeni_sifre_tekrar" name="yeni_sifre_tekrar" class="form-control" placeholder="Yeni şifre (tekrar)">
                  </div>
                </div>
               
                <hr class="my-4" />
                <button type="submit" id="sifre_guncelle" name="sifre_guncelle" style="width:100%;" class="btn btn-success">Şifre Güncelle</button> 
              </form>
              <div id="hata"></div>
            </div>
          </div>
        </div>
      </div>
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
                            <h4 class="heading mt-4">Hesap bilgileri başarı ile güncellendi!</h4>
                            
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