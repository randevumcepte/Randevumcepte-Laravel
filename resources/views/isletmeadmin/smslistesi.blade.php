@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<div class="main-content container-fluid">
    <div class="row">
			 <div class="panel panel-default">
                      <div class="panel-heading panel-heading-divider">
                       SMS Listelerim
                        <div class="tools">
                            <button type="button" data-modal="md-scale" class="btn btn-space btn-primary md-trigger">Yeni Liste Oluştur</button>

                        </div>

              </div>
              <div class="panel-body">

              	    <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Liste Adı</th>
                        <th>Oluşturma Zamanı</th>
                        <th>Güncellenme Zamanı</th>
                        <th>İşlemler</th>
                         
                      </tr>
                    </thead>
                    <tbody>
                       <tr>
                       		<td>Müşterilerim</td>
                       		<td></td>
                       		<td></td>
                       		<td  class="actions"> <a title="Liste Detayları" href="/isletmeyonetim/smslistedetay/0" class="icon"><i class="mdi mdi-settings"></i></a></td>
                       </tr>
                     @foreach($listeler as $liste)
                      <tr>
                        <td>{{$liste->sms_liste_adi}}</td>
                        <td>
                         {{date('d.m.Y H:i',strtotime($liste->created_at))}}
                        </td>
                        <td>{{date('d.m.Y H:i',strtotime($liste->updated_at))}}</td>
                        <td class="actions">
                        	 <a title="Liste Detayları" href="/isletmeyonetim/smslistedetay/{{$liste->id}}" class="icon"><i class="mdi mdi-settings"></i></a>
                        	  <a title="Liste Sil" id="#smslistesil" style="cursor: pointer;" class="icon"><i class="mdi mdi-delete"></i></a>

                        </td>
                        
                      </tr>
                      @endforeach
                       @if($listeler->count()==0)
                       <tr>
                       		<td colspan="4" style="color:red; font-weight: bold;text-align: center;">Kayıt Bulunamadı</td>
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
                        <span style="font-size:20px">Yeni SMS Listesi Ekle</span>
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                       	<form id="yenismslistesiekle" method="post" enctype="multipart/form-data">
                       		{!!csrf_field()!!}
                          <div class="form-group">
       
                              <label>Liste Adı</label>
                              <input id="listeadi_yeni" name="listeadi_yeni" required placeholder="Liste adı..." class="form-control">
                            </div>
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

                  <div class="modal-overlay"></div>
                  <div id="hata"></div>
@endsection