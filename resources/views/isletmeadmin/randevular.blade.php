@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
   <div class="col-md-4 col-sm-6 col-xs-7 col-7">
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

<div class="col-md-8 col-sm-6 col-xs-5 col-5">
   <div class="d-flex justify-content-end">
   <button class="btn btn-primary mr-2 randevu-count-button">
    Toplam Randevu: {{$randevular['randevu_sayisi']}}
</button>
      
      <a href="#" data-toggle="modal" data-target="#modal-view-event-add" class="btn btn-success btn-lg yenieklebuton">
         <i class="fa fa-plus"></i> Yeni Randevu
      </a>
   </div>
</div>
   </div>
</div>
<div class="pd-20 card-box mb-30" >
   <div class="row" style="margin-bottom: 10px;">

      @if(Auth::guard('satisortakligi')->check() || ( Auth::guard('isletmeyonetim')->check() && !Auth::guard('isletmeyonetim')->user()->hasRole('Personel')))
      <div class="col-md-6 col-sm-6 col-xs-6 col-6">
      @else
      <div class="col-md-6 col-sm-6 col-xs-6 col-6" style="display:none">
      @endif 
         <select class="form-control" id="randevu_ayarina_gore">
                     
                    <option {{($isletme->randevu_takvim_turu==1) ? 'selected' : ''}} value="1">Personele Göre</option>
                    <option {{($isletme->randevu_takvim_turu==0) ? 'selected' : ''}} value="0">Hizmete Göre</option>
                    <option {{($isletme->randevu_takvim_turu==2) ? 'selected' : ''}} value="2">Cihaza Göre</option>
                    <option {{($isletme->randevu_takvim_turu==3) ? 'selected' : ''}} value="3">Odaya Göre</option>
         </select>
      </div>
      
      <div class="col-md-6 col-sm-6 col-xs-6 col-6">
         <input type="text" class="form-control calendardatepicker" autocomplete="off" id='takvim_tarihe_gore' placeholder='Tarih Seçiniz'>
      </div>
   </div>
   <div style="position:relative; width:100%; overflow-y:auto">

       
      <div class="calendar-wrap">
         <div id="calendar">
         </div>
      </div>
   </div>
   
  
                      
   
</div>
<div id="hata"></div>
@endsection