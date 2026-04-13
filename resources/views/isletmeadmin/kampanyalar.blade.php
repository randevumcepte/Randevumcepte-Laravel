@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

 <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15 avantaj" style="font-weight: bold; text-align: center; font-size:25px; padding-bottom: 20px"><span style="border-radius: 30px; background-color: #FF4E00; color:white;padding:10px 20px 10px 20px"> Avantaj Kodu Doğrulama</span>  
                   <!-- <br /> <span style="font-size:12px;color:#FF4E00">Avantajbu'dan gelen :  {{\App\MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tur',1)->count()}}</span>
                    <br />
                    <span style="font-size:12px;color:#1266f1">Eklediklerim & kendi müşterilerim :{{\App\MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tur',0)->count()}} </span> -->

                   
                  </div>
                <div class="panel-body"  style="overflow-x: auto">
                       <div class="col-xs-9 col-sm-9 col-md-9">
                          <div class="form-group">
                          </div></div>
                      <div class="col-xs-12 col-sm-12 col-md-12">
                          <div class="form-group">
                            <div class="text-center">
                                 <label style="width: 100%">Avantaj kodunu aşağıya girerek arama yapabilirsiniz</label>
                                <input type="text"  id="avantajkuponkodu" placeholder="___-___-___" data-inputmask="'mask': '999-999-999'" pattern="\d{3}\-\d{3}\-\d{4}" class="input-lg" style="font-size: 25px;font-weight: bold;width: 200px;float: none; text-align: center;"> 
                              </div>
                          </div>
                           <div class="form-group">
                              <div class="text-center" id="avantajkodkullan">
                                <button type="button" id="avantajkuponara" class="btn btn-space btn-primary" style="width: 200px;height: 30px;font-size: 20px"><i class="icon mdi mdi-search"></i> Kodu Ara</button>
                                 </div>
                          </div>

                      </div>
                        
                       <div class="col-md-12" style="overflow-x: auto">
                           <div class="col-md-12" id="avantajkodbulunamadi">
                            <div role="alert" class="alert alert-danger alert-dismissible"><button type="button" data-dismiss="alert" aria-label="Close" class="close"><span aria-hidden="true" class="mdi mdi-close"></span></button><span class="icon mdi mdi-close-circle-o"></span><strong id="avantajkodbulunamadimesaj"></strong></div>
                           </div>
                            <table class="table table-striped table-borderless" style="display: none" id="avantajkuponlartablosu">
                    <thead>
                      <tr>
                        <th>Kupon Kodu</th>
                        <th>Müşteri Ad Soyad</th>
                        <th>Avantaj</th>
                        <th>Son Kul. Tarihi</th>
                        
                        <th>Birim Fiyat</th>
                        <th>Kullanıldı mı?</th>
                        
                      </tr>
                    </thead>
                    
                    <tbody class="no-border-x" id="avantajkupontablo">
                           <tr><td colspan='7' style='color:red;text-align: center;'><strong>Satın alınan avantajı görüntülemek için lütfen avantaj kodunu giriniz</strong></td></tr>
                    </tbody>
                  </table>
                      </div>
                      
                      <div class="col-md-12">
                           
                      </div>
                     
                    
                </div>

            </div>
<div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15 avantaj" style="font-weight: bold; text-align: center;font-size: 25px"><span style="border-radius: 30px; background-color: #FF4E00; color:white;padding:10px 20px 10px 20px"> Avantajlarım</span>
                   <!-- <br /> <span style="font-size:12px;color:#FF4E00">Avantajbu'dan gelen :  {{\App\MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tur',1)->count()}}</span>
                    <br />
                    <span style="font-size:12px;color:#1266f1">Eklediklerim & kendi müşterilerim :{{\App\MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tur',0)->count()}} </span> -->

                   
                  </div>
                  <div class="panel-body" style="overflow-x:auto">
                      <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        
                        <th style="width: 300px">Avantaj</th>
                        <th style="width: 120px">Yayın Tarihi</th>
                        <th style="width: 100px">Bitiş Tarihi</th>
                        
                        <th style="width: 110px">Toplam Satış</th>
                        <th>Kullanılan</th>
                        <th>Kullanılmayan</th>                
                         <th>Durum</th>   
                         
                        
                        
                      </tr>
                    </thead>
                    <tbody id="kampanyatablohtml">
                       {!!$kampanyahtml!!}
                    </tbody>
                  </table>
                  </div>
              </div>
          </div>
          <div id="hata"></div>
          <script type="text/javascript">
              $(document).ready(function(){
                 $('#avantajkuponkodu').inputmask();
            });
          </script>
        

@endsection()