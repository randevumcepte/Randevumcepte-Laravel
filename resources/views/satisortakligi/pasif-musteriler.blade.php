@extends('layout.layout_satisortakligi')

@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">Pasif Müşteriler</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde satış yapılamamış müşterilerinizin durumunu görüntüleyebilirsiniz</p>
           
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
                <div class="col-8">
                  <h3 class="mb-0">Pasif Müşteriler</h3>
                </div>
             
              </div>
            </div>
            <div class="card-body">
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
          </div>
        </div>
      </div>
      
       <div id="hata"></div>
     
 

 
@endsection