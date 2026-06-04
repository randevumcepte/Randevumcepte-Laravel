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
             @yetki('gorusme.ekle_duzenle')
             <a id="yeni_on_gorusme_ekle" href="#" data-toggle="modal" data-target="#ongorusme-modal" onclick="modalbaslikata('Yeni Ön Görüşme','ongorusmeformu')" class="btn btn-success btn-lg yenieklebuton501"><i class="fa fa-plus"></i> Yeni Ön Görüşme</a>
             @endyetki
             @yetki('pazarlama.sms_gonder')
             <a id="secilenlere_sms_gonder" href="#"
             class="btn btn-primary btn-lg yenieklebuton502"><i class="fa fa-envelope"></i> SMS Gönder</a>
             @endyetki
         </div>
      </div>
   </div>
<div class="card-box mb-30">

            <div class="pb-20" style="padding : 20px">
               <form id="on_gorusme_liste_form">
                <div class="on-gorusme-tablo-wrap" style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table class="data-table table stripe hover nowrap" id="on_gorusme_liste" style="width:100%">
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
                </table>
                </div>
                </form>
            </div>
</div>

<style>
/* On Gorusme tablo: yatay scroll wrapper - sadece gerektiginde scroll */
.on-gorusme-tablo-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
/* Sutunlar sikismasin diye min-width, ama genis ekranda tabloyu uzatma */
.on-gorusme-tablo-wrap #on_gorusme_liste {
    min-width: 1100px;
}
/* Responsive plugin sutunlari gizlemesin */
#on_gorusme_liste thead th,
#on_gorusme_liste tbody td {
    display: table-cell !important;
}
#on_gorusme_liste td.dtr-control,
#on_gorusme_liste th.dtr-control {
    display: none !important;
}
#on_gorusme_liste tr.child {
    display: none !important;
}
#on_gorusme_liste td.dtr-hidden,
#on_gorusme_liste th.dtr-hidden,
#on_gorusme_liste td.none,
#on_gorusme_liste th.none {
    display: table-cell !important;
}
</style>

<script>
/* Responsive plugin'in dinamik attigi siniflari geri al */
$(document).on('init.dt', '#on_gorusme_liste', function() {
    var $t = $('#on_gorusme_liste');
    $t.removeClass('dtr-inline collapsed');
    setTimeout(function() {
        $t.find('td, th').removeClass('dtr-hidden none');
        $t.find('tr.child').remove();
        $t.find('td.dtr-control, th.dtr-control').remove();
    }, 50);
});
</script>

@endsection()