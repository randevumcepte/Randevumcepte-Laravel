  <div
         id="toplu-musteri-modal"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1100px; max-height: 90%;">
               <form id="yenimusterilistesiekle"  method="POST">
                  <div class="modal-header">
                     <h2 class="text-blue h2 mb-10">Toplu @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif Ekle</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <div class="form-group">
                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                        <label>Yüklenecek Liste(*.xls veya *.csv excel dosyası)</label>
                        <label style="font-weight: bold;">Not : Excel dosyası kolon isimleri ad soyad, cep telefonu ve varsa e-posta şeklinde olmalıdır. csv dosyasındaki tırnak işaretlerini kaldırınız.</label>
                        <br>
                        <label style="color:green">Örnek xls,xlsx dosyası : <a href="/public/listeler/ornek_data_dosyasi.xlsx"><span class="mdi mdi-download"></span> İndir</a></label>
                        <br>
                        <label style="color:green">Örnek csv dosyası : <a href="/public/listeler/ornek_data_dosyasi.csv"><span class="mdi mdi-download"></span> İndir</a></label>
                        <input type="file" id="listedosyasi_yeni_musteri" name="listedosyasi_yeni_musteri" class="form-control">
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">
                           Ekle
                           </button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 col-6">
                           <button id="modal_kapat_paket"
                              type="button"
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>