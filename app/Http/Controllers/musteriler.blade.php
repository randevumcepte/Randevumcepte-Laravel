@extends('layout.layout_isletmeadmin')
@section('content')
<div class="card-box mb-30">
             
            <div class="pb-20" style="padding-top:20px">
            
              <table class="data-table table stripe hover nowrap" id="musteri_tablo">
                <thead>
                  <tr>
                      <th>Müşteri</th>
                      <th>Telefon</th>
                         
                                            
                      <th>Kayıt Tarihi</th>
                      <th>Son Randevusu</th>
                      <th>Randevu Sayısı</th>
                       
                        
                      <th class="datatable-nosort"></th>
                  </tr>
                </thead>
                <tbody>
                
                   
                </tbody>
              </table>
            </div>
          </div>
  
         <div id="md-scale" class="modal-container modal-effect-1" style="display:none">


                    <div class="modal-content">
                      <div class="modal-header">
                        <span style="font-size:20px">Toplu Müşteri Ekle</span>

                        <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close"><span class="mdi mdi-close"></span></button>
                      </div>
                      <div class="modal-body">
                        <form id="yenimusterilistesiekle" method="post" enctype="multipart/form-data">
                          {!!csrf_field()!!}
                            <label style="font-size:15px">Müşteri Ekle</label>
                            
                            <div class="form-group">
                              <label>Yüklenecek Liste(*.xls veya *.csv excel dosyası)</label>
                              <label style="font-weight: bold;">Not : Excel dosyası kolon isimleri ad soyad, cep telefonu ve varsa e-posta şeklinde olmalıdır. csv dosyasındaki tırnak işaretlerini kaldırınız.</label>
                              <br />
                              <label style="color:green">Örnek xls,xlsx dosyası : <a href="/public/listeler/ornek_data_dosyasi.xlsx"><span class="mdi mdi-download"></span> İndir</a></label>
                               <br />
                              <label style="color:green">Örnek csv dosyası : <a href="/public/listeler/ornek_data_dosyasi.csv"><span class="mdi mdi-download"></span> İndir</a></label>
                              <input type="file" id="listedosyasi_yeni_musteri" name="listedosyasi_yeni_musteri" class="form-control">
                            </div>
                            

                         
                           <div class="text-center">
                            <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" id="modalkapat1" class="btn btn-default btn-space modal-close">İptal</button>
                            <button type="submit"  class="btn btn-primary">Ekle</button>
                          </div>
                        </div></form>
                      </div>
                      <div class="modal-footer"></div>
                    </div>
                  </div>
                   <div class="modal-overlay"></div>
                  <div id="hata"></div>

@endsection()