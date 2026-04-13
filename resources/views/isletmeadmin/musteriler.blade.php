@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-6">
         <div class="title">
            <h1>Müşteriler</h1>
         </div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
               </li>
               <li class="breadcrumb-item active" aria-current="page">
                  Müşteriler
               </li>
            </ol>
         </nav>
      </div>
      <div class="col-md-6 col-sm-6 text-right">
         <a href="#" data-toggle="modal" data-target="#musteri-bilgi-modal" class="btn btn-success btn-lg yanitli_musteri_ekleme yenieklebuton501"><i class="fa fa-plus"></i> Yeni Müşteriler</a>
         @if(!Auth::guard('satisortakligi')->check())
         @if(DB::table('model_has_roles')->where('role_id',5)->where('model_id',Auth::guard('isletmeyonetim')->user()->id)->where('salon_id',$isletme->id)->count() == 0)
         <a href="#" data-toggle="modal" data-target="#toplu-musteri-modal" class="btn btn-primary btn-lg yenieklebuton502"><i class="fa fa-user-plus"></i> Toplu Müşteriler Ekle</a>
         @endif
         @else
         <a href="#" data-toggle="modal" data-target="#toplu-musteri-modal" class="btn btn-primary btn-lg yenieklebuton502"><i class="fa fa-user-plus"></i> Toplu Müşteriler Ekle</a>
         @endif

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
               href="#tum-musteriler"
               role="tab"
               aria-selected="false"
               >Tümü ({{ $tumMusteriSayisi }})</button
               >
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#sadik-musteriler"
               role="tab"
               aria-selected="false"
               >Sadık Müşteriler ({{ $sadikMusterilerSayisi }})</button
               >
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#aktif-musteriler"
               role="tab"
               aria-selected="false"
               >Aktif Müşteriler ({{ $azIslemYapanlarSayisi }})</button
               >
         </li>
         <li class="nav-item" style="margin-left: 20px;display: inline-block;">
            <button
               class="btn btn-outline-primary"
               data-toggle="tab"
               href="#pasif-musteriler"
               role="tab"
               aria-selected="false"
               >Pasif Müşteriler ({{ $odemeYapmayanlarSayisi }})</button
               >
         </li>
      </ul>
      <div class="tab-content">
         <div class="tab-pane fade show" id="sadik-musteriler" role="tab-panel" style="margin-top: 20px;">
            <div class="alert alert-primary" role="alert">
                  <div class="row">
                    <div class="col-md-10 col-sm-10 col-xs-10"> <b>Sadık müşteri nedir ? </b> 3 ay içinde en az 3 hizmet, ürün veya paket satın alan müşteri kategorisidir.</div>
                  <div  class="col-md-2 col-sm-2 col-xs-2 text-right">
                     <div style="border: 1px solid royalblue;width: 160px;height: 30px;text-align: center;padding: 2px; border-radius: 10px;"><b style="font-size: 14px">Sadık İndirmi (%) : {{$isletme->sadik_musteri_indirim_yuzde}}</b></div>
                  </div>
                 </div>
             </div>
            <table class="data-table table stripe hover nowrap" id="musteri_tablo_sadik" style="width:100%">
               <thead>
                  <tr>
                     <th>Müşteri</th>
                     <th>Telefon</th>
                     <th>Kayıt Tarihi</th>
                     <th>Son Randevusu</th>
                     <th>Randevu Sayısı</th>
                     <th>Toplam Alınan Ücret</th>
                     <th class="datatable-nosort"></th>
                  </tr>
               </thead>
               <tbody>
               </tbody>
            </table>
         </div>
         <div class="tab-pane fade show active" id="tum-musteriler" role="tab-panel" style="margin-top: 20px;">
            
            <table class="data-table table stripe hover nowrap" id="musteri_tablo" style="width:100%">
               <thead>
                  <tr>
                     <th>Müşteri</th>
                     <th>Telefon</th>
                     <th>Kayıt Tarihi</th>
                     <th>Son Randevusu</th>
                     <th>Randevu Sayısı</th>
                     <th>Toplam Alınan Ücret</th>
                     <th class="datatable-nosort"></th>
                  </tr>
               </thead>
               <tbody>
               </tbody>
            </table>
         </div>
         <div class="tab-pane fade show" id="aktif-musteriler" role="tab-panel" style="margin-top: 20px;">
             <div class="alert alert-primary" role="alert">
                  <div class="row">
                    <div class="col-md-10 col-sm-10 col-xs-10"> <b>Aktif müşteri nedir ? </b> Pasif müşterilerden en az 1 hizmet, ürün veya paket satın alan müşteri kategorisidir.</div>
                  <div  class="col-md-2 col-sm-2 col-xs-2 text-right">
                     <div style="border: 1px solid royalblue;width: 160px;height: 30px;text-align: center;padding: 2px; border-radius: 10px;"><b style="font-size: 14px">Aktif İndirmi (%) : {{$isletme->aktif_musteri_indirim_yuzde}}</b></div>
                  </div>
                 </div>
             </div>
              
            <table class="data-table table stripe hover nowrap" id="musteri_tablo_aktif" style="width:100%">
               <thead>
                  <tr>
                     <th>Müşteri</th>
                     <th>Telefon</th>
                     <th>Kayıt Tarihi</th>
                     <th>Son Randevusu</th>
                     <th>Randevu Sayısı</th>
                     <th>Toplam Alınan Ücret</th>
                     <th class="datatable-nosort"></th>
                  </tr>
               </thead>
               <tbody>
               </tbody>
            </table>
         </div>
         <div class="tab-pane fade show" id="pasif-musteriler" role="tab-panel" style="margin-top: 20px;">
            <div class="alert alert-primary" role="alert">
                  <b>Pasif müşteri nedir ? </b> Hiçbir şekilde hizmet, ürün veya paket satın almamış müşteri kategorisidir.
             </div>
            <table class="data-table table stripe hover nowrap" id="musteri_tablo_pasif" style="width:100%">
               <thead>
                  <tr>
                     <th>Müşteri</th>
                     <th>Telefon</th>
                     <th>Kayıt Tarihi</th>
                     <th>Son Randevusu</th>
                     <th>Randevu Sayısı</th>

                     <th>Toplam Alınan Ücret</th>
                     <th class="datatable-nosort"></th>
                  </tr>
               </thead>
               <tbody>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
 
@endsection()