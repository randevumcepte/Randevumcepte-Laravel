@extends('layout.layout_satisortakligi')
@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">Ödeme Talepleri</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde talep ettiğiniz ödemelerinizi görüntüleyebilirsiniz</p>
           
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
                  <h3 class="mb-0">Ödeme Talepleri</h3>
                   <button type="button" {{$hakedis['toplam']==0 ? "disabled" : ""}} id="odeme_talep_et_button" style="float: right;" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="ni ni-money-coins"></i>Ödeme Talep Et</button>
                  <span id="satis_ortagi_guncel_hakedis" style="display:none" data-value="{{number_format(($hakedis['toplam']),2,',','.')}}">  <?php echo number_format(($hakedis['toplam']),2,',','.');?> ₺</span>
               
                </div>
             
              </div>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="hakedis_talepleri">
                    <thead class="thead-light">
                      <tr>
                         
                        <th>Talep Tarih / Saat</th>
                       
                        <th>Talep Edilen Hakediş Tutarı</th>                       
                        <th>Durum</th>
                        
                         
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
        
     
 

 
@endsection