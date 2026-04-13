@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
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
     
   </div>
</div>
<div class="card-box mb-30">
  <div class="pb-20"  style="padding: 20px">
    <ul class=" nav nav-tabs element" role="tablist">
      <li class="nav-item" style="margin-left: 20px;">
        <button 
        class="btn btn-outline-primary active"
        data-toggle="tab"
        href="#tum_alacaklar"
        role="tab"
        aria-selected="false" 
         style="width: 120px;" 
        >
          Tümü
        </button>
      </li>
       <li class="nav-item" style="margin-left: 20px;display: inline-block; ">
        <button 
        class="btn btn-outline-primary"
        data-toggle="tab"
        href="#hizmet_alacaklar"
        role="tab"
        aria-selected="false" 
        style="width: 120px;" 
        >
          Hizmete Göre
        </button>
      </li>
       <li class="nav-item" style="margin-left: 20px;display: inline-block;">
        <button 
        class="btn btn-outline-primary"
        data-toggle="tab"
        href="#ürün_alacaklar"
        role="tab"
        aria-selected="false" 
        style="width: 120px;" 
        >
          Ürüne Göre
        </button>
      </li>
       <li class="nav-item" style="margin-left: 20px;display: inline-block;">
        <button 
        class="btn btn-outline-primary"
        data-toggle="tab"
        href="#paket_alacaklar"
        role="tab"
        style="width: 120px;" 
        aria-selected="false" 
        >
          Pakete Göre
        </button>
      </li>
       <li class="nav-item" style="margin-left: 20px;display: inline-block;">
        <button 
        class="btn btn-outline-primary"
        data-toggle="tab"
        href="#taksitli_alacaklar"
        role="tab"
        style="width: 120px;" 
        aria-selected="false" 
        >
          Taksitler
        </button>
      </li>
    </ul>
    <div class="tab-content">

      <div class="tab-pane fade show active" id="tum_alacaklar" role="tab-panel" style="margin-top: 20px;">

         <table class="data-table table stripe hover nowrap" id="alacaklar">
                <thead>
                  <tr>
                    <th>Oluşturulma</th>
                    <th>Müşteri</th>
                    <th>Hizmet & Ürün & Paket</th>
                    <th>Tutar (₺)</th>
                    <th>Planlanan Ödeme Tarihi</th>
                    
                    <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
               
                </tbody>
              </table>
        }
      </div>
       <div class="tab-pane fade show" id="hizmet_alacaklar" role="tab-panel" style="margin-top: 20px;">
         <table class="data-table table stripe hover nowrap" id="alacaklar_hizmet">
                <thead>
                  <tr>
                    <th>Oluşturulma</th>
                    <th>Müşteri</th>
                    <th>Hizmet</th>
                    <th>Tutar (₺)</th>
                    <th>Planlanan Ödeme Tarihi</th>
                    
                    <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
               
                </tbody>
              </table>
      </div>
       <div class="tab-pane fade show " id="ürün_alacaklar" role="tab-panel" style="margin-top: 20px;">
         <table class="data-table table stripe hover nowrap" id="alacaklar_urun">
                <thead>
                  <tr>
                    <th>Oluşturulma</th>
                    <th>Müşteri</th>
                    <th>Ürün</th>
                    <th>Tutar (₺)</th>
                    <th>Planlanan Ödeme Tarihi</th>
                    
                    <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
               
                </tbody>
              </table>
      </div>
       <div class="tab-pane fade show" id="paket_alacaklar" role="tab-panel" style="margin-top: 20px;">
         <table class="data-table table stripe hover nowrap" id="alacaklar_paket">
                <thead>
                  <tr>
                    <th>Oluşturulma</th>
                    <th>Müşteri</th>
                    <th>Paket</th>
                    <th>Tutar (₺)</th>
                    <th>Planlanan Ödeme Tarihi</th>
                    
                    <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
               
                </tbody>
              </table>
      </div>
       <div class="tab-pane fade show" id="taksitli_alacaklar" role="tab-panel" style="margin-top: 20px;">
         <div class="tab">
           
                                     <ul class="nav nav-tabs element" role="tablist" >
                                       <li class="nav-item">
                                          <a class="nav-link active" data-toggle="tab" href="#tum_taksit" role="tab" aria-selected="true" style="height: 80px;" >Tümü</a>
                                       </li>
                                       <li class="nav-item">
                                          <a class="nav-link" data-toggle="tab" href="#acik_taksit" role="tab" aria-selected="false" style="height: 80px;">Açık Taksitler</a>
                                       </li>
                                       <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#kapali_taksit" role="tab"aria-selected="false" style="height: 80px;">Kapalı Taksitler</a>

                                       </li>
                                       <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#gecikmis_taksit" role="tab"aria-selected="false" style="height: 80px;">Gecikmiş Taksitler</a>

                                       </li>
                                    </ul>
                                     <div class="tab-content">
                                        <div class="tab-pane fade show active" id="tum-tum_taksit" role="tab-panel" style="margin-top: 20px; ">

                                         <table class="data-table table stripe hover nowrap" id="tum_taksitler">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Taksit</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
                                           
                                       </div>
                                       <div class="tab-pane fade show " id="acik_taksit" role="tab-panel" style="margin-top: 20px; ">

                                         <table class="data-table table stripe hover nowrap" id="acik_taksitler">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Taksit</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
                                           
                                       </div>
                                       <div class="tab-pane fade show " id="kapali_taksit" role="tab-panel" style="margin-top: 20px; ">

                                         <table class="data-table table stripe hover nowrap" id="kapali_taksitler">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Taksit</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
                                           
                                       </div>
                                       <div class="tab-pane fade show " id="gecikmis_taksit" role="tab-panel" style="margin-top: 20px; ">

                                         <table class="data-table table stripe hover nowrap" id="gecikmis_taksitler">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Taksit</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
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