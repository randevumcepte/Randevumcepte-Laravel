@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="page-header">
   <div class="row">
      <div class="col-md-6">
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
      <div class="col-md-6">
         
      </div>
     
   </div>
</div>
<div class="card-box mb-30">
             
    <div class="pb-20" style="padding-top:20px">
              <table class="data-table table stripe hover nowrap" id="musteri_tablo">
                <thead>
                  <tr>
                      <th>Müşteri</th>
                      <th>Telefon</th>
                         
                                            
                      <th>Hizmetler</th>
                      <th>Tarih</th>
                      <th>Saat</th>
                      <th>Durum</th>
                      <th>Toplam Hizmet Fiyatı</th>
                      <th>Oluşturan</th>
                      <th>Oluşturulma</th>
                       
                        
                      <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
                  
                   
                </tbody>
              </table> 
              
    </div>
</div>
  
         

@endsection()