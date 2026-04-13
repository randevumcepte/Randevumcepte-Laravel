@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="main-content container-fluid">
    <div class="row">
			 <div class="panel panel-default">
                      <div class="panel-heading panel-heading-divider">
                        {{$title}}
                       <div class="tools">
                            <button type="button" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Yeni Bilgi Ekle (Excel)</button>
  <button style="display: none" id="smsbilgiguncellememodal" data-modal="md-scale2" class="btn btn-space btn-primary md-trigger"></button>
                        </div>

              </div>
              <div class="panel-body">
                    
              	    <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Ad Soyad</th>
                        <th>Cep Telefon</th>
                        <th>Kara Liste</th>
                        <th>Kara Liste Nedeni</th>
                         <th>İşlemler</th>
                      </tr>
                    </thead>
                    <tbody>

                     @foreach($smslistedetaylari as $liste)
                      <tr>
                        <td>{{$liste->ad_soyad}}</td>
                        <td>
                         {{$liste->cep_telefon}}
                        </td>
                        <td>@if($liste->sms_kampanya_karaliste==1)
                          <span style="color:green">Evet</span>
                          @else
                          Hayır
                          @endif
                        </td>
                        <td>
                        	{{$liste->sms_kampanya_karaliste_nedeni}}

                        </td>
                        <td style="font-size: 20px">
                          <a title="Bilgileri Düzenle" name="bilgileriduzenle" data-value="{{$liste->id}}" style="cursor: pointer;"><i class="mdi mdi-edit"></i></a>
 
                          <a title="Listeden Kaldır" name="listedenkaldir" data-value="{{$liste->id}}" style="cursor: pointer;"> <i class="mdi mdi-delete"></i></a>
                        </td>
                        
                      </tr>
                      @endforeach
                       @if($smslistedetaylari->count()==0)
                       <tr>
                       		<td colspan="5" style="color:red; font-weight: bold;text-align: center;">Kayıt Bulunamadı</td>
                       </tr>
                       @endif
                    </tbody>
                  </table>
               </div>
     </div>

</div>
<div id="md-scale"class="modal-container modal-effect-1">


                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px">SMS Listesine Yeni Bilgi Ekle(Excel)</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                       	<form id="yenismsbilgisiexcelekle" method="post" enctype="multipart/form-data">
                       		{!!csrf_field()!!}
                          
                            <div class="form-group">
                              <label>Yüklenecek Liste(*.xls excel dosyası)</label>
                              <input type="file" id="listedosyasi_yeni" required name="listedosyasi_yeni" class="form-control">
                            </div>
                           
                         
                           <div class="text-center">
                          	<div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="submit"  class="btn btn-primary">Yükle</button>
                          </div>
                        </div></form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                  <div id="md-scale2" class="modal-container modal-effect-1" style="max-height: 500px; overflow-y: auto">


                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px">Bilgi Düzenle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                        <form id="smsbilgiguncelle" method="get">
                          {!!csrf_field()!!}
                             <input type="hidden" name="smslistebilgiid" id="smslistebilgiid">
                            <div class="form-group">
                              <label>Ad Soyad</label>
                              <input type="text" id="liste_ad_soyad" required name="liste_ad_soyad" class="form-control">
                            </div>
                           <div class="form-group">
                              <label>Cep Telefon</label>
                              <input type="text" id="liste_cep_telefon" required name="liste_cep_telefon" class="form-control">
                            </div>
                           <div class="form-group">
                             <div class="be-checkbox be-checkbox-color inline">
                          <input id="liste_karaliste" name="liste_karaliste" type="checkbox">
                          <label for="liste_karaliste">Kara Listeye Al</label>
                        </div>
                            
                           </div>
                           <div class="form-group" id="karalistenedengirdi" style="display: none">
                              
                      <label>Kara Liste Nedeni</label><br />
                       
                        <div class="be-radio">
                          <input type="radio" checked  value="1" name="liste_karalistenedeni" id="liste_karalistenedeni1">
                          <label for="liste_karalistenedeni1">Çok fazla gönderim yapılıyor</label>
                        </div>
                        <div class="be-radio">
                          <input type="radio" value="2" name="liste_karalistenedeni" id="liste_karalistenedeni2">
                          <label for="liste_karalistenedeni2">Gönderimlerle ilgilenmiyor</label>
                        </div>
                        <div class="be-radio">
                          <input type="radio" value="3" name="liste_karalistenedeni" id="liste_karalistenedeni3">
                          <label for="liste_karalistenedeni3">Diğer</label>
                        </div>
                         <textarea class="form-control" name="liste_karalistenedeni_diger" id="liste_karalistenedeni_diger" placeholder="Diğer nedeni belirtiniz"></textarea>
                   
                    
                               
                           </div>
                            
                           <div class="text-center">
                            <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="submit"  class="btn btn-primary">Güncelle</button>
                          </div>
                        </div></form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>

                  <div class="modal-overlay"></div>
                  <div id="hata"></div>
@endsection