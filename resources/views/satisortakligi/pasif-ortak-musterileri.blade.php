@extends('layout.layout_satisortakligi')
 @section('content')
   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">{{$pasifortakadi}} tarafından yönlendirilen müşteriler</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde pasif ortağınız aracılığı ile eklediğiniz müşterilerinizin takibini yapabilirsiniz</p>
           
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

               <ul class="nav nav-pills nav-fill flex-md-row" id="tabs-icons-text" role="tablist">
                 <li class="nav-item ">
                    <a class="nav-link mb-sm-3 mb-md-0 active" id="tabs-icons-text-1-tab" data-toggle="tab" href="#tumu" role="tab" aria-controls="tumu" aria-selected="true">Tümü</a>
                 </li>
                 <li class="nav-item ">
                    <a class="nav-link mb-sm-3 mb-md-0 btn btn-danger" style="background-color:grey;color:#fff;border:grey" id="tabs-icons-text-2-tab" data-toggle="tab" href="#pasif-musteriler" role="tab" aria-controls="pasif-musteriler" aria-selected="false">Pasif Müşteriler</a>
                 </li>
                 <li class="nav-item ">
                    <a class="nav-link mb-sm-3 mb-md-0 btn btn-primary" style="background-color:#324cdd;color:#fff" id="tabs-icons-text-2-tab" data-toggle="tab" href="#demosu-olan-musteriler" role="tab" aria-controls="demosu-olan-musteriler" aria-selected="false">Demosu Oluşturulmuş Müşteriler</a>
                 </li>
                 <li class="nav-item ">
                    <a class="nav-link mb-sm-3 mb-md-0 btn btn-info" id="tabs-icons-text-2-tab" style="background-color: #0da5c0;color:#fff" data-toggle="tab" href="#satis-olmayan-musteriler" role="tab" aria-controls="satis-olmayan-musteriler" aria-selected="false">Satışı Kapanmayan Müşteriler</a>
                 </li>
                 <li class="nav-item  ">
                    <a class="nav-link mb-sm-3 mb-md-0 btn btn-success" id="tabs-icons-text-2-tab" style="background-color: #24a46d;color:#fff" data-toggle="tab" href="#aktif-musteriler" role="tab" aria-controls="aktif-musteriler" aria-selected="false">Aktif Müşteriler</a>
                 </li>
              </ul>
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="tumu" role="tabpanel" aria-labelledby="tumu">
                   <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="tum_musteriler">
                      <thead class="thead-light">
                        <tr>
                          <th>İşletme ID</th>
                          <th>İşletme Adı</th>
                          <th>İşletme Yetkilisi </th>
                          <th>Telefon</th>
                          <th>Ekleme Tarihi</th>
                         <th>Durum</th>
                         <th>Notlar</th>
                          <th>İşlemler</th>
                           
                        </tr>
                      </thead>
                      <tbody class="list">
                        
                     
                     
                      </tbody>
               
                     
                       
                    </table>
                  </div>
                </div>
                <div class="tab-pane fade" id="pasif-musteriler" role="tabpanel" aria-labelledby="pasif-musteriler">
                   <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="pasif_musteriler">
                      <thead class="thead-light">
                        <tr>
                          <th>İşletme ID</th>
                          <th>İşletme Adı</th>
                          <th>İşletme Yetkilisi </th>
                          <th>Telefon</th>
                          <th>Ekleme Tarihi</th>
                         <th>Notlar</th>
                          <th>İşlemler</th>
                           
                        </tr>
                      </thead>
                      <tbody class="list">
                        
                     
                     
                      </tbody>
               
                     
                       
                    </table>
                  </div>
               
                </div>
                <div class="tab-pane fade" id="demosu-olan-musteriler" role="tabpanel" aria-labelledby="demosu-olan-musteriler">
                   <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="demosu_olan_musteriler">
                    <thead class="thead-light">
                      <tr>
                        <th>İşletme ID</th>
                        <th>İşletme Adı</th>
                        <th>İşletme Yetkilisi </th>
                          <th>Telefon</th>
                        <th>Ekleme Tarihi</th>
                        <th>Notlar</th>
                        <th>İşlemler</th>
                         
                      </tr>
                    </thead>
                    <tbody class="list">
                      
                   
                   
                    </tbody>
             
                   
                     
                  </table>
                </div>
             
                </div>
                <div class="tab-pane fade" id="satis-olmayan-musteriler" role="tabpanel" aria-labelledby="satis-olmayan-musteriler">
                   <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="satis_yapilamayan_musteriler">
                    <thead class="thead-light">
                      <tr>
                        <th>İşletme ID</th>
                        <th>İşletme Adı</th>
                        <th>İşletme Yetkilisi </th>
                          <th>Telefon</th>
                        <th>Ekleme Tarihi</th>
                        <th>Notlar</th>
                        <th>İşlemler</th>
                         
                      </tr>
                    </thead>
                    <tbody class="list">
                      
                   
                   
                    </tbody>
             
                   
                     
                  </table>
                </div>
                </div>
                  
                <div class="tab-pane fade" id="aktif-musteriler" role="tabpanel" aria-labelledby="aktif-musteriler">
                  <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="aktif_musteriler">
                    <thead class="thead-light">
                      <tr>
                        <th>İşletme ID</th>
                        <th>İşletme Adı</th>
                        <th>İşletme Yetkilisi </th>
                        <th>Telefon</th>
                        <th>Üyelik Türü / Satılan Paket</th>
                        <th>Üyelik Süresi</th>
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
        </div>
      </div>
      
      <div id="hata"></div>
     
 

 
@endsection