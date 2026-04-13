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
             <a id="yeni_on_gorusme_ekle" href="#" data-toggle="modal" data-target="#ongorusme-modal" onclick="modalbaslikata('Yeni Ön Görüşme','ongorusmeformu')" class="btn btn-success btn-lg yenieklebuton501"><i class="fa fa-plus"></i> Yeni Ön Görüşme</a>
             <a id="secilenlere_sms_gonder" href="#" 
             class="btn btn-primary btn-lg yenieklebuton502"><i class="fa fa-envelope"></i> SMS Gönder</a>
         </div>
      </div>
   </div>
<div class="card-box mb-30">
             
            <div class="pb-20" style="padding : 20px">
               <form id="on_gorusme_liste_form">
                <table class="data-table table stripe hover nowrap" id="on_gorusme_liste">
                  <thead>
                     <th>
                                 <div class="dt-checkbox">
                                    <input
                                       type="checkbox"
                                        
                                       id="hepsini_sec_liste"
                                    />
                                    <span class="dt-checkbox-label"></span>
                                 </div>
                    </th>
                    <th>Oluşturma</th>
                    <th>Müşteri</th>
                    <th>Müşteri Tipi</th>
                    <th>Telefon</th> 
                    <th>Randevu Tarihi</th>
                    <th>Ön Görüşme Nedeni</th>
                    <th>Görüşmeyi Yapan</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                  </thead>
                  <tbody>
                    
                  </tbody>
                </table></form>
            </div>
</div>

@endsection()