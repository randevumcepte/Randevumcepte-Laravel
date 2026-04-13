@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
 <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-6">
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
           
              
      <div class="col-md-6 col-sm-6 text-right">
        
            <a href="#" data-toggle="modal" data-target="#yeni_senet_modal" class="btn btn-success btn-lg yenieklebuton"><i class="fa fa-plus"></i> Yeni Senet</a>
          
      </div>
      </div>
   </div>
<div class="card-box mb-30">
   <div class="pb-20" style="padding: 20px">
      <ul class="nav nav-tabs element" role="tablist">
         <li class="nav-item" style="margin-left: 20px;">
            <button
               class="btn btn-outline-primary active"
               data-toggle="tab"
               href="#tum-senetler"
               role="tab"
               aria-selected="false"
               >Tümü</button
               >
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#acik-senetler"
               role="tab"
               aria-selected="false"
               >Açık Senetler</button
               >
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#kapali-senetler"
               role="tab"
               aria-selected="false"
               >Kapalı Senetler</button
               >
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#odenmemis-senetler"
               role="tab"
               aria-selected="false"
               >Ödenmemiş Senetler</button
               >
         </li>

      </ul>
      <div class="tab-content">
         <div class="tab-pane fade show active" id="tum-senetler" role="tab-panel" style="margin-top: 20px;">
            <table class="data-table table stripe hover nowrap" id="senet_liste">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Senet</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
         </div>
         <div class="tab-pane fade show" id="acik-senetler" role="tab-panel" style="margin-top: 20px;">
            <table class="data-table table stripe hover nowrap" id="senet_liste_acik">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Senet</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
           
         </div>
         <div class="tab-pane fade show" id="kapali-senetler" role="tab-panel" style="margin-top: 20px;">
              <table class="data-table table stripe hover nowrap" id="senet_liste_kapali">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Senet</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
            
         </div>
         <div class="tab-pane fade show" id="odenmemis-senetler" role="tab-panel" style="margin-top: 20px;">
             <table class="data-table table stripe hover nowrap" id="senet_liste_odenmemis">
                  <thead>
             
                  <tr>
                  <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Senet</th>
                    <th>İşlemler</th>
                  </tr>
                     </thead>
                     <tbody>
                   
                  </tbody>
            </table>
         </div>
      </div>
   </div>
             
 


@endsection()