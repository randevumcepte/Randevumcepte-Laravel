@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-6">
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
      <div class="col-md-6 col-sm-6 text-right">
            
            <a href="#" data-toggle="modal" onclick='modalbaslikata("Yeni Etkinlik","etkinlik_formu")' data-target="#yeni_etkinlik_modal" class="btn btn-success btn-lg yenieklebuton"><i class="fa fa-plus"></i> Yeni Etkinlik</a>

      </div>
   </div>
</div>
<div class="pd-20 card-box mb-30">

	<div class="pb-20" style="padding-top:20px">
   
              
  
 

		<table class="data-table table stripe hover nowrap" id="etkinlik_tablo">
                  <thead>
                    <th>Tarih</th>
                    <th>Etkinlik Adı</th>
                    <th>Katılımcı Sayısı</th>
                         
                    <th>Toplam Tutar</th>
             
                     <th>İşlemler</th>
                  </thead>
                  <tbody>
                    
                  </tbody>
                </table>
	</div>
</div>
@endsection