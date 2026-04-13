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
            
            <a href="#" data-toggle="modal" data-target="#modal-view-event-add" class="btn btn-success btn-lg yenieklebuton"><i class="fa fa-plus"></i> Yeni Randevu</a>
             
         
      </div>
   </div>
</div>
<div class="pd-20 card-box mb-30">

	<div class="pb-20" style="padding-top:20px">
  <div class="form-group row">
                <div class="col-sm-3 col-xs-6 col-6">
                    <label>Kaynak</label>
                    <select name="olusturulma" id="olusturulmaya_gore_filtre" class="form-control">
                        <option selected value="">Tümü</option>
                        <option  value="salon">Salon</option>
                        <option value="web">Web</option>
                        <option value="uygulama">Uygulama</option>

                    </select>
                </div>
                <div class="col-sm-3 col-xs-6 col-6">
                    <label>Durum</label>
                  <select class="form-control" id="duruma_gore_filtre">
                    <option selected value="">Tüm Randevu Durumları</option>
                    <option value="0">Onay bekleyen</option>
                    <option  value="1">Onaylı</option>
                    <option value="2">Reddedilen</option>
                    <option value="3">Müşteri tarafından iptal edilen</option>
                  </select>
                </div>
                <div class="col-sm-3 col-xs-6 col-6">
                <label>Zaman</label>
                  <select class="form-control" id="zamana_gore_filtre" >
                    <option  value="">Tüm Zamanlar</option>
                    <option selected value="{{date('Y-m-d')}} / {{date('Y-m-d')}}">Bugün</option>
                    <option value="{{date('Y-m-d', strtotime('+1 days',strtotime(date('Y-m-d'))))}} / {{date('Y-m-d', strtotime('+1 days',strtotime(date('Y-m-d'))))}}">Yarın</option>
                    <option value="<?php  echo date('Y-m-01') . " / ". date('Y-m-t'); ?>">Bu ay</option>
                    <option value="<?php  echo date('Y-m-01',strtotime('+1 months')) . " / ". date('Y-m-t',strtotime('+1 months')); ?>">Önümüzdeki ay</option>
                    <option value="<?php echo date('Y-01-01') . " / ". date('Y-12-31'); ?>">Bu yıl</option>
                    <option value="<?php echo date(date('Y',strtotime('+1 year')).'-01-01') . " / ". date(date('Y',strtotime('+1 year')).'-12-31'); ?>">Önümüzdeki yıl</option>
                    <option value="ozel">Özel</option>
                  </select>
                </div>
                <div class="col-sm-3  col-xs-6 col-6" style="" id="ozel_tarih_filtresi">
                   
                    <input
                      class="form-control datetimepicker-range"
                      placeholder="Tarih aralağını seçiniz.."
                      type="text" id="tarihe_gore_filtre" style="display: none;"
                    />
              </div>
              </div>
              
  
<br>

		<table class="data-table table stripe hover nowrap" id="randevu_liste">
                  <thead>
                    <th>Müşteri</th>
                    <th>Telefon Numarası</th>
                    <th>Hizmetler</th>
                    <th>Personel/Cihaz/Oda</th>
                    <th>Tarih</th>
                    <th>Saat</th>
                    <th>Durum </th>
                     
                     <th>Oluşturan</th>
                     <th></th>
                  </thead>
                  <tbody>
                    
                  </tbody>
                </table>
	</div>
</div>
@endsection