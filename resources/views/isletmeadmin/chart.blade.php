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
          <div class="col-md-3 col-sm-6 text-right">
        
                  <select class="form-control">
                    <option selected="">Tümü</option>
                    <option value="1">Açık Senetler</option>
                    <option value="2">Kapalı Senetler</option>
                  </select>
             
                
              </div>
              
      <div class="col-md-3 col-sm-6 text-right">
        
            <a href="#" data-toggle="modal" data-target="#yeni_senet_modal" class="btn btn-success btn-lg"><i class="fa fa-plus"></i> Yeni Senet</a>
          
      </div>
      </div>
   </div>
<div class="card-box mb-30">
             
            <div class="pb-20" style="padding-top:20px">
                <table class="data-table table stripe hover nowrap" id="senet_liste">
             
                  <tr>
                      <th>Durum</th>
                    <th>Müşteri</th>
                    <th>Vade Sayısı</th>
                    <th>Ödenmiş</th>                    
                    <th>Ödenmemiş</th>
                    <th>Yaklaşan Senet</th>
                    <th>İşlemler</th>
                  </tr>
           
                   <tr>
                      <td>Açık</td>
                    <td>Cevriye Efe</td>
                    <td>5</td>
                    <td>3</td>
                    <td>2</td>
                    <!-- tarihi geçmişse tarih kırmızı olacak geçmemişse normal olacak -->
                    <td style="color: red">10.06.2023</td>
                    <td> <a href="#" data-toggle="modal" data-target="#senet_detay_modal" class="btn btn-primary">Detaylar</a></td>
                   </tr>
          
                </table>
            </div>
</div>

@endsection()