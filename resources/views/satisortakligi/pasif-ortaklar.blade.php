@extends('layout.layout_satisortakligi')
 @section('content')
   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">Pasif Ortaklar</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde müşteri datalarınız sağlayan pasif ortaklarınız ve satışlardan kazanacakları prim yüzdesini görebilirsiniz</p>
           
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
                <div class="col-12">
                  <h3 class="mb-0">Pasif Ortaklar
                     
               <button type="button" id="pasif_ortak_ekle" style="float: right;" class="btn btn-primary" data-toggle="modal" data-target="#pasif-ortak-bilgi">Yeni Pasif Ortak</button>
                
                  </h3>
                </div>
             
              </div>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="pasif_ortaklar">
                    <thead class="thead-light">
                      <tr>
                        <th>Ad Soyad</th>
                        <th>Telefon</th>
                        <th>E-mail</th>
                        <th>Prim </th>
                       
                        <th>İşlemler</th>
                         
                      </tr>
                    </thead>
                    <tbody class="list">
                      
                   
                   
                    </tbody>
             
                   
                     
                  </table>
                </div>
             
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="pasif-ortak-bilgi" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
          <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
              <div class="modal-content">
                <div class="modal-header bg-success">
                  <h6 class="modal-title text-white" id="modal-title-default">Pasif Ortak Bilgileri</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-white">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <form id="pasif_ortak_formu" method="POST">
                          @csrf
                          <input type="hidden" name="pasifortakid" id="pasif_ortak_id">
                         
                           <div class="form-group">
                            <label>Ad Soyad (zorunlu)</label>
                            <input type="text" required name="adsoyad" id="pasif_ortak_ad_soyad" class="form-control">
                          </div>
                           <div class="form-group">
                            <label>Telefon (Zorunlu)</label>
                            <input type="text" required name="telefon" id="pasif_ortak_telefon" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>E-mail (opsiyonel)</label>
                            <input type="text" name="email" id="pasif_ortak_email" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Prim Yüzde (opsiyonel)</label>
                            <input type="tel" required name="satisyuzde" id="pasif_ortak_yuzde" class="form-control">
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
       <div id="hata"></div>
     
 

 
@endsection