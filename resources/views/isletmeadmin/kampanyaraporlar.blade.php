@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
  					  <div class="panel-heading panel-heading-divider xs-pb-15 avantaj" style="font-weight: bold;font-size: 20px"> Tarih Aralığı 
  					  </div>
  					  <div class="panel-body" style="padding-bottom: 10px">
  					  	<label>Tarih Aralığı Seçerek, Seçtiğiniz Tarihteki Satış ve Kullanım Bilgilerini Görebilirsiniz</label>
  					  	<form id="avantajraporgetir" method="get">
  					  	 <div class="col-xs-4 col-sm-3">
  					  	   <div data-min-view="2" id="tarihbaslangicdp"  data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                          <input name="tarih" required id="tarihbaslangic"  placeholder="yyyy-mm-dd"  size="16" type="text" value="" class="form-control"><span class="input-group-addon"><i class="icon-th mdi mdi-calendar"></i></span>
                        </div>
                    </div>
                    <div class="col-xs-4 col-sm-3">
                         <div data-min-view="2" id="tarihbitisdp"   data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                          <input name="tarih" required id="tarihbitis" placeholder="yyyy-mm-dd" size="16" type="text" value="" class="form-control"><span class="input-group-addon"><i class="icon-th mdi mdi-calendar"></i></span>
                        </div> </div>
                         <div class="col-xs-4 col-sm-3">
  					  	<button type="submit" id="avantajraporsubmit" class="btn btn-primary btn-big"><span class="icon mdi mdi-search"></span> Ara</button>
  					  </div>

  					  </div>

  					</form>

              </div>
          </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <div class="panel panel-default panel-table">
            <div class="panel-heading panel-heading-divider xs-pb-15 avantaj" style="font-weight: bold;font-size:20px">Avantajlar</div>
            <div class="panel-body" style="padding-bottom: 10px;overflow-x:auto">
               <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        
                        <th style="width: 300px">Avantaj</th>     
                        <th style="width: 110px">Toplam Satış</th>
                        <th>Kullanılan</th>
                        <th>Kullanılmayan</th>                
                         <th>Durum</th>   
                         
                        
                        
                      </tr>
                    </thead>
                    <tbody id="kampanyaraportablohtml">
                        
                    </tbody>
                  </table>
            </div>
          </div>
        </div>
      </div>
  </div>
@endsection