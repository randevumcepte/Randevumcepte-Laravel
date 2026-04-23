@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')@section('content')
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
     
        
            <a href="#" data-toggle="modal" type="button" data-target="#formugondermodal"  class="btn btn-success btn-lg yenieklebuton501"><i class="fa fa-paper-plane"></i> Yeni Form Gönder</a>
              <a href="#" data-toggle="modal" type="button" data-target="#haricibelgeeklemodal" class="btn btn-primary btn-lg yenieklebuton502"><i class="fa fa-plus"></i> Belge Ekle</a>
              <a href="/isletmeyonetim/form-sablonlari?sube={{$isletme->id}}" class="btn btn-warning btn-lg"><i class="fa fa-file-text-o"></i> Form Şablonları</a>

          
      </div>
   </div>
</div>
<div class="card-box mb-30">
   <div style="padding: 20px">
          <ul class="nav nav-tabs element" role="tablist" style="border-bottom:0">
            <li class="nav-item">
              <button
               class="btn btn-outline-primary active"
               data-toggle="tab"
               href="#tum_arsiv"
               role="tab"
               aria-selected="false"
               >Tümü</button
               >
            </li>
              <li class="nav-item" style="margin-left: 20px;">
              <button
               class="btn btn-outline-primary "
               data-toggle="tab"
               href="#onayli_arsiv"
               role="tab"
               aria-selected="false"
               >Onaylananlar</button
               >
            </li>
              <li class="nav-item" style="margin-left: 20px;">
              <button
               class="btn btn-outline-primary "
               data-toggle="tab"
               href="#beklenen_arsiv"
               role="tab"
               aria-selected="false"
               >Beklenenler</button
               >
            </li>
              <li class="nav-item" style="margin-left: 20px;">
              <button
               class="btn btn-outline-primary "
               data-toggle="tab"
               href="#iptal_arsiv"
               role="tab"
               aria-selected="false" style="min-width: 113px;"
               >İptal Edilenler</button
               >
            </li>
              <li class="nav-item" style="margin-left: 20px;">
              <button
               class="btn btn-outline-primary "
               data-toggle="tab"
               href="#harici_arsiv"
               role="tab"
               aria-selected="false" style="min-width: 120px;"
               >Harici Belgeler</button
               >
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="tum_arsiv" role="tab-panel" style="margin-top: 20px;">
              <table class="data-table table stripe hover nowrap" id="arsiv_liste">
        <thead>
          <th>Müşteri</th>
          <th>Başlık</th>
          <th>Oluşturulma Tarihi</th>
          <th>Belge Durumu</th>
      <th>Durum</th>
      <th>İşlemler</th>
        </thead>
         <tbody>
                    
                  </tbody>
      </table>
            </div>
             <div class="tab-pane fade show " id="onayli_arsiv" role="tab-panel" style="margin-top: 20px;">
              <table class="data-table table stripe hover nowrap" id="arsiv_liste_onayli">
        <thead>
          <th>Müşteri</th>
          <th>Başlık</th>
          <th>Oluşturulma Tarihi</th>
          <th>Belge Durumu</th>
      <th>Durum</th>
      <th>İşlemler</th>
        </thead>
         <tbody>
                    
                  </tbody>
      </table>
            </div>
             <div class="tab-pane fade show " id="beklenen_arsiv" role="tab-panel" style="margin-top: 20px;">
              <table class="data-table table stripe hover nowrap" id="arsiv_liste_beklenen">
        <thead>
          <th>Müşteri</th>
          <th>Başlık</th>
          <th>Oluşturulma Tarihi</th>
          <th>Belge Durumu</th>
      <th>Durum</th>
      <th>İşlemler</th>
        </thead>
         <tbody>
                    
                  </tbody>
      </table>
            </div>
             <div class="tab-pane fade show " id="iptal_arsiv" role="tab-panel" style="margin-top: 20px;">
              <table class="data-table table stripe hover nowrap" id="arsiv_liste_iptal">
        <thead>
          <th>Müşteri</th>
          <th>Başlık</th>
          <th>Oluşturulma Tarihi</th>
          <th>Belge Durumu</th>
      <th>Durum</th>
      <th>İşlemler</th>
        </thead>
         <tbody>
                    
                  </tbody>
      </table>
            </div>
             <div class="tab-pane fade show " id="harici_arsiv" role="tab-panel" style="margin-top: 20px;">
              <table class="data-table table stripe hover nowrap" id="arsiv_liste_harici">
        <thead>
          <th>Müşteri</th>
          <th>Başlık</th>
          <th>Oluşturulma Tarihi</th>
          <th>Belge Durumu</th>
      <th>Durum</th>
      <th>İşlemler</th>
        </thead>
         <tbody>
                    
                  </tbody>
</table>
            </div>
          </div>
        </div>    



</div>
<div id="haricibelgeeklemodal" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
            <form id="haricibelgeekleform">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <div class="modal-content" style="min-height: 200px;">
                  <div class="modal-header">
                     <h4 class="h4">Harici Belge Ekle</h4>
                     <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
                     <div class="row">
                        
               <div class="col-md-4 col-xs-3 col-sm-3 col-3 form-group">
                 <label>Form Başlığı</label>
                 <input class="form-control" type="text" name="haricibelgeformbaslik" id="haricibelgeformbaslik">
               </div>
                     <div class="col-md-4 col-sm-6 col-xs-6 col-6 form-group">
                 <label>Müşteri</label>
                   <select name="haricibelgemusteri" id="haricibelgemusteri" class="form-control opsiyonelSelect musteri_secimi" style="width: 100%;">
                        <option></option>
                     
                    </select>
               </div>
                       <div class="col-md-4 col-sm-6 col-xs-6 col-6 form-group">
                         <label>İşlemi Yapan Personel</label>
                   <select name="haricibelgepersonel" id="haricibelgepersonel" class="form-control opsiyonelSelect personel_secimi" style="width: 100%;">
                         <option></option>
                                             
                    </select>
               </div>
               <div class="col-md-12 col-xs-6 col-sm-6 col-6 form-group">
                 <label>Formu Yükle</label>
                 <input type="file" name="hariciformyukle" required id="hariciformyukle" class="form-control-file form-control ">
               </div>
                     </div>
                  </div>
                    <div class="modal-footer" style="justify-content: center;">
                     <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                        <button type="submit" class="btn btn-success btn-block"> Kaydet</button>
                     </div>
                  </div>
              
               </div>
            </form>
         </div>
      </div>



 

<div id="hata"></div>
<div id='yazdirilacak' style="display:none"></div>
@endsection