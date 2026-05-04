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
               class="btn btn-outline-primary"
               href="/isletmeyonetim/ayarlar?p=personeller{{(isset($_GET['sube'])) ? '&sube='.$isletme->id : '' }}"
               style="width: 160px;"
               >
             Personeller
            </a>
         </li>
         <li class="nav-item" style="margin-left: 20px;">
            <a
               class="btn btn-outline-primary active"
               data-toggle="tab"
               href="#primHakedis"
               role="tab"
               aria-selected="true"
               style="width: 160px;"
               >
             Prim & Hak Ediş
            </a>
         </li>
      </ul>
      <div class="tab-content" style="padding: 0 30px 0 30px;">
         <div class="tab-pane fade show active" id="primHakedis" role="tab-panel" style="margin-top: 20px;">
            @include('isletmeadmin.partials.prim_hakedis_panel')
         </div>
      </div>
   </div>
</div>

@endsection()
