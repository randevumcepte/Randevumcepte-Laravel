@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
 <form id="urun_satis_form" action="{{route('uruntahsilatagit')}}" method="POST">

  <input name="sube" type="hidden" value="{{$isletme->id}}">
              <div class="page-header">

                  <div class="row">
                     <div class="col-md-6 col-sm-6 col-xs-6 col-6">
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
                           <select class="form-control custom-select2 musteri_secimi"  name="urun_satis_musteri_id" style="width: 150px">
                              <option></option>
                             
                           </select>
                           <a  style="margin-left: 3px;"  title="Tahsil Et" id="secilenurun_satis_yap" href="isletmeyonetim/adisyondetay" type="button" class="btn btn-primary btn-lg yenieklebuton332">
                           Satış Yap
                           </a>
                           <a   style="margin-left: 3px;" title="Fiyat" id="urun_fiyat_degistir" data-target='#urun_fiyat_degistir_modal'  data-toggle="modal" type="button" class="btn btn-warning btn-lg yenieklebuton333">Fiyat Değiştir
                           </a>
                         
                         
                           <button style="margin-left: 3px;margin-right: 3px;" data-toggle="modal"  data-target="#urun-modal" type="button" class="btn btn-success yenieklebuton331">
                              <i class="fa fa-plus"></i> Yeni Ürün
                           </button> 
                     </div>
                  </div>
              </div>
            <div class="card-box mb-30">
             
            <div class="pb-20" style="padding:20px">
             
                            <table class="data-table table stripe hover nowrap" id="urun_liste">
                     <thead>
                        
                            <th>
                                 <div class="dt-checkbox">
                                    <input
                                       type="checkbox"
                                        
                                       id="urun_hepsini_sec_liste"
                                    />
                                    <span class="dt-checkbox-label"></span>
                                 </div>
                    </th>
                          <th>Ürün</th>
                           <th>Stok</th>
                           <th>Fiyat</th>
                           <th>Barkod</th>
                           <th>Düşük Stok Sınırı</th>
                           <th class="datatable-nosort">İşlemler</th>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
             


            </div>
          </div>
      </form>
   
          <div id="hata"></div>


 
    <div
   id="urun_fiyat_degistir_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
      <div class="modal-content" style="width: 950px; max-height: 90%;">
         <form id="urun_fiyat_degistir_formu"  method="POST">
            <div class="modal-body">
               {!!csrf_field()!!}
              <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="urun_id" id="urun_id" value="0">
               <h2 class="text-blue h2 mb-10" id="urun_modal_baslik">Fiyat Değiştir</h2>
          
               <div class="form-group">
                  <label style="font-size: 16px;font-weight: bold;">Oran % (Yüzdelik olarak giriniz!)</label>
                  <input type="tel"  name="urun_oran" id="urun_oran" class="form-control">
               </div>
            
             
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-md-4 col-4 col-xs-6 col-sm-6">
                     <button type="button" id="urun_fiyat_indirim_yap" class="btn btn-primary btn-lg btn-block">
                     İndirim Yap
                     </button>
                  </div>
                     <div class="col-md-4 col-4 col-xs-6 col-sm-6">
                     <button type="button" id="urun_fiyat_zam_yap" class="btn btn-success btn-lg btn-block">
                     Zam Yap
                     </button>
                  </div>
                  <div class="col-md-4 col-4 col-xs-6 col-sm-6">

                     <button id="modal_kapat"
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal" 
                        ><i class="fa fa times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
            </div>
   
      </form>
   </div>
</div>
</div>
   

         

@endsection()