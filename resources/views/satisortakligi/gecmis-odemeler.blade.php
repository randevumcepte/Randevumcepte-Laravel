@extends('layout.layout_satisortakligi')
@section('content')

   <div class="header pb-6 d-flex align-items-center" style="min-height: 200px; max-height: 300px;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container-fluid d-flex align-items-center">
        <div class="row">
          <div class="col-lg-12 col-md-12">
            <h1 class="display-2 text-white">Geçmiş Ödemeler</h1>
            <p class="text-white mt-0 mb-5">Bu bölümde bugüne kadar almış olduğunuz ödemelerinizi görüntüleyebilirsiniz</p>
           
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--6">
      <div class="row input-daterange datepicker align-items-center">
        <div class="col-12 col-xs-12 col-md-4">
          <div class="form-group">
          <select class="form-control" id="gecmis_odeme_filtre">
              <option  value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
               <option value="<?php echo date('Y-m-d', strtotime('monday this week')); ?> / <?php echo date('Y-m-d', strtotime('sunday this week')); ?>">Bu hafta</option>

               <option selected value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
               <option value="<?php  echo date('Y-m-01',strtotime('-1 months')) . " / ". date('Y-m-t',strtotime('-1 months')); ?>">Geçen ay</option>
               <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
               <option value="<?php echo date('Y-01-01', strtotime('-1 year')) . " / " . date('Y-12-31', strtotime('-1 year')); ?>">Geçen yıl</option>
                <option value="ozel">Özel</option>
          </select></div>
        </div>
        <div class="col-xs-6 col-6 col-sm-6 col-md-4 odeme_tarihleri_ozel" style="display:none">
                      <div class="form-group">
                        
                        <input class="form-control odeme_tarihi_araligi" id="odeme_baslangic_tarihi" placeholder="Başlangıç Tarihi" type="text" >
                      </div>
                    </div>
                    <div class="col-xs-6 col-6 col-sm-6  col-md-4 odeme_tarihleri_ozel" style="display:none">
                      <div class="form-group">
                        
                        <input class="form-control odeme_tarihi_araligi" id="odeme_bitis_tarihi" placeholder="Bitiş Tarihi" type="text" value="{{date('Y-m-d')}}">
                      </div>
                    </div>
                  </div>
      </div>
    
      <div class="row">
        <div class="col-xl-12 order-xl-1">
          
          <div class="card">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Geçmiş Ödemeler</h3>
                </div>
             
              </div>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table align-items-center table-flush" id="gecmis_odemeler">
                    <thead class="thead-light">
                      <tr>
                        
                        <th>Ödeme Tarihi</th>
                        <th>Tutar</th>
                        
                        <th>Ödeme Yapılan Banka</th>
                        
                         
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