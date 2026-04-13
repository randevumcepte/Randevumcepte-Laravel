@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

 <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                  <div class="panel-heading panel-heading-divider xs-pb-15" style="font-weight: bold;">Yeni Kampanya Yayınla
                   <!-- <br /> <span style="font-size:12px;color:#FF4E00">Avantajbu'dan gelen :  {{\App\MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tur',1)->count()}}</span>
                    <br />
                    <span style="font-size:12px;color:#1266f1">Eklediklerim & kendi müşterilerim :{{\App\MusteriPortfoy::where('salon_id',Auth::guard('isletmeyonetim')->user()->salon_id)->where('tur',0)->count()}} </span> -->

                  
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
                       
                            <div class="email editor">
                              <div id="email-editor"></div>
                                <div class="form-group">

                                <button type="submit" class="btn btn-primary btn-space"><i class="icon s7-mail"></i> Yayınla</button>
                         
                                 </div>
                             </div>
                         
                     </div>
                   </form></div>
                </div>
              </div>
            </div>
          </div>
        

@endsection()