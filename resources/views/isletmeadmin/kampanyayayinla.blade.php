@extends('layout.layout_isletmeadminpaketornek')
@section('content')
 <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Yeni Kampanya Yayınla
                 
                  
                  </div>
                <div class="panel-body" >
                  <div class="col-md-12">
                   <form id="yenikampanyayayinla" method="post">
                     <div class="row">
                        <div class="col-md-12">
                       
                           <div class="from-group">
                              {!!csrf_field()!!}
                              <label>Kampanya başlığı</label>
                              <input type="text" required class="form-control" placeholder="Başlık..." name="kampanya_baslik">
                           </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <label>Kampanya açıklaması</label>
                                <textarea required class="form-control" placeholder="Açıklama..." name="kampanya_aciklama"></textarea>
                             </div>
                        </div>
                      </div>
                      <div class="row">
                          <div class="col-6 col-sm-6 col-md-6">
                             <div class="form-group">
                              <label>Hizmet Normal Fiyatı</label>
                                <input class="form-control" type="text" name="hizmet_normal_fiyat" placeholder="Hizmet normal fiyatı..." required>
                             </div>
                          </div>
                          <div class="col-6 col-sm-6 col-md-6">
                             <div class="form-group">
                              <label>Kampanya Fiyatı</label>
                                <input class="form-control" type="text" name="kampanya_fiyat" placeholder="Kampanya fiyatı..." required>
                             </div>
                          </div>
                      </div>
                       
                         <div class="col-12 col-sm-12 col-md-12">
                             <div class="form-group">
                              <label>Kampanya hakkında</label>
                                <textarea class="form-control"></textarea>  
                             </div>
                          </div>
                      </div>
                      <div class="row">
                      	<
                      </div>
                   </form></div>
                </div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Ödeme Bilgileri
                 
                  
                  </div>
                <div class="panel-body" >
                  <div class="col-md-12">
                         <img src="{{secure_asset('/public/img/ornekpos2.jpg')}}" style="width: 100%;height: 100% auto">
                       </div>
                   </form></div>
                </div>
              </div>
            </div>
          </div>
@endsection