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


      <div class="col-md-6 col-sm-12 text-right">

        
            <a href="#" data-toggle="modal" data-target="#yeni_ajanda_ekle" class="btn btn-success btn-lg yenieklebuton"><i class="fa fa-plus"></i> Yeni Not Ekle</a>

          
      </div>
   </div>
</div>
<div class="card-box mb-30">
  

  <div class="pb-20" style="padding-top: 20px;">
    <ul class="nav nav-tabs element" role="tablist">
      <li class="nav-item" style="margin-left: 20px;">
        <button class="btn btn-outline-primary "
        data-toggle="tab" href="#ajanda_liste_gorunum" role="tab" aria-selected="false" style="width: 150px" 
        >Liste Görünümü</button>
      </li>
      <li class="nav-item" style="margin-left: 20px;">
        <button class="btn btn-outline-primary active"
        data-toggle="tab" href="#ajanda_takvim_gorunum" role="tab" aria-selected="false" style="width: 150px" 
        >Takvim Görünümü</button>
      </li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane fade show " id="ajanda_liste_gorunum" role="tab-panel" style="margin-top: 20px">
            
        <div style="padding: 10px">
              <table class="data-table table stripe hover nowrap" id="ajanda_liste">
        <thead>
          <th>Başlık</th>
          <th>İçerik</th>
          <th>Hatırlatma</th>
      
          <th>Tarih ve Saat</th>
          <th>Durum</th>
          <th>Oluşturan</th>
          <th>İşlemler</th>
        </thead>
         <tbody>
                    
                  </tbody>
      </table>
        </div>    
  
      </div>
      <div class="tab-pane fade show active" id="ajanda_takvim_gorunum" role="tab-panel"  >
        
       <div style="padding: 20px;position:relative; width:100%; overflow-y:auto" >
          <div class="col-md-4 col-sm-6 col-xs-6 col-6 " style="justify-content: flex-end; margin-bottom: 20px; display: flex;"> 
         <input type="text" class="form-control calendardatepicker2" autocomplete="off"  id='takvim_tarihe_gore_ajanda' placeholder='Tarih Seçiniz'>
      </div>
      <div class="calendar-wrap">
         <div id="calendar_ajanda">
         </div>
      </div>
   </div>
    </div>


  </div>
                      
   
</div>
<style type="text/css"></style>
<div id="hata"></div>

</div>



@endsection