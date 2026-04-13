@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-12">
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
      <div class="col-md-6 col-sm-12 text-right">
        
            <a onclick="modalbaslikata('Yeni Masraf','musteri_bilgi_formu');" href="#" data-toggle="modal" data-target="#yeni_masraf_modal" class="btn btn-success btn-lg"><i class="fa fa-plus"></i> Yeni Masraf </a>
           
         
      </div>
   </div>
</div>
<div class="card-box mb-30">
             
            <div class="pb-20" style="padding-top:20px">
               <div class="form-group row">
                <div class="col-sm-4">
                    
                </div>
                <div class="col-sm-4">
                
                </div>
                <div class="col-sm-4">
                  
                </div>
                
            </div>
            
              <table class="data-table table stripe hover nowrap" id="masraf_tablo">
                <thead>
                  <tr>
                      <th>Tarih</th>
                      <th>Masraf Kategorisi </th>
                      <th>Açıklama</th>       
                      <th>Tutar (₺)</th>
                      <th>Masraf Sahibi</th>
                      <th>Ödeme Yönetmi</th>
                       
                        
                      <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
                
                   
                </tbody>
              </table>
            </div>
          </div>
  
          
             

@endsection()