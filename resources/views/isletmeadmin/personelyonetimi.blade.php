@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-12 col-sm-12">
         <div class="title">
            <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
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
   
   <div class="pb-20" style="padding-top:20px">

     
      <ul class=" nav nav-tabs element" role="tablist">
         <li class="nav-item" style="margin-left: 20px;">
            <a 
               class="btn btn-outline-primary active"
               data-toggle="tab"
               href="#personeller"
               role="tab"
               aria-selected="false" 
               style="width: 160px;" 
               >
             Takvim Tablosu
            </a>
         </li>
         <li class="nav-item" style="margin-left: 20px;">
            <a 
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#personelOdemeIslemleri"
               role="tab"
               aria-selected="false" 
               style="width: 160px;" 
               >
             Ödeme İşlemleri
            </a>
         </li>
        
         
      </ul>
      <div class="tab-content" style="padding: 0 30px 0 30px;">
         <div class="tab-pane fade show active" id="personeller" role="tab-panel" style="margin-top: 20px;">
              
            <div class="row" style="border-bottom: 1px solid #e2e2e2;margin-bottom: 10px;padding-bottom: 10px;">
                  <div class="col-6 col-xs-6 col-sm-6">
                     <h2 class="text-blue">Personeller</h2>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-6 text-right">
                     <button onclick="modalbaslikata('Yeni Personel','yenipersonelbilgiekle')" class="btn btn-success" data-toggle="modal" data-target="#personel-modal"><i class="fa fa-plus"></i> Yeni Personel</button>
                  </div>
               </div>
               <div class="pd-20">
                  <table class="data-table table stripe hover nowrap" id="personel_tablo">
                     <thead>
                        <tr>
                           <th>Takvim Sırası</th>
                           <th>Personel</th>
                           <th>Hesap Tipi</th>
                           <th>Telefon</th>
                           <th>Durum</th>
                           <th class="datatable-nosort">İşlemler</th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
               
             
         </div>
         <div class="tab-pane fade" id="personelOdemeIslemleri" role="tab-panel" style="margin-top: 20px;">
             
            
         </div>
         
        
         
      </div>
   </div>
</div>

@endsection()