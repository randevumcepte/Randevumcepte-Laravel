@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
 <form id="paket_satis_form" action="{{route('pakettahsilatagit')}}" method="POST">

  <input name="sube" type="hidden" value="{{$isletme->id}}">
              <div class="page-header">

                  <div class="row">
                     <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                        <div class="title">
                           <h1>{{$sayfa_baslik}}</h1>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                           <ol class="breadcrumb">
                              <li class="breadcrumb-item">
                                 <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                              </li>
                              <li class="breadcrumb-item active" aria-current="page">
                                 {{$sayfa_baslik}}
                              </li>
                           </ol>
                        </nav>
                     </div>
                     <div class="col-md-6 col-sm-6 col-xs-6 col-6 text-right">
                          <select class="form-control custom-select2 musteri_secimi"  name="paket_satis_musteri_id" style="width: 150px">
                              <option></option>


                           </select>
                          <select class="form-control custom-select2 personel_secimi" name="paket_satis_personel_id" style="width: 180px; margin-left: 5px;">
                              <option value=""></option>
                           </select>
                           <a  style="margin-left: 10px;"  title="Tahsil Et" id="secilenpaket_satis_yap" href="isletmeyonetim/adisyondetay" type="button" class="btn btn-primary btn-lg yenieklebuton502">Satış Yap</a>



                           <button style="margin-left: 3px;margin-right: 3px;" data-toggle="modal" data-target="#paket-modal" type="button" class="btn btn-success yenieklebuton501">
                              <i class="fa fa-plus"></i> Yeni Paket
                           </button>
                     </div>
                   
                  </div>
              </div>
            <div class="card-box mb-30">
             
            <div class="pb-20" style="padding:20px">
             
                            <table class="data-table table stripe hover nowrap" id="paket_liste">
                     <thead>
                        
                            <th>
                                 <div class="dt-checkbox">
                                    <input
                                       type="checkbox"
                                        
                                       id="paket_hepsini_sec_liste"
                                    />
                                    <span class="dt-checkbox-label"></span>
                                 </div>
                    </th>
                           <th>Paket Adı</th>
                           <th>Hizmet(-ler)</th>
                           <th>Seans(-lar)</th>
                           <th>Fiyat (₺)</th>
                           <th class="datatable-nosort">İşlemler</th>
                        
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
             


            </div>
          </div>
      </form>
   
          <div id="hata"></div>


 
      

         

@endsection()