<div
            id="yeni_taksitli_tahsilat_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="taksitli_tahsilat_formu"  method="POST">
                     <div class="modal-header">
                        <h2>Yeni Taksitli Tahsilat</h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                        @if($pageindex==111)
                        <input type="hidden" name="adisyon_id" value="{{$adisyon->id}}">
                        <input type="hidden" name="ad_soyad" value="{{$adisyon->user_id}}">
                        @endif
                        @if($pageindex==1111)
                        <input type="hidden" name="ad_soyad" value="{{(isset($musteri)) ? $musteri->id : ''}}">
                        <input type="hidden" name="adisyon_id">
                        @endif
                        <div class="row" data-value="0">
                           <div class="col-md-4">
                              <label>Ödeme Başlangıç Tarihi</label>
                              <input type="text" required class="form-control date-picker" name="vade_baslangic_tarihi" autocomplete="off">
                           </div>
                           <div class="col-md-4">
                              <label>Taksit Sayısı (Ay)</label>
                              <input type="tel" required name="vade" value=" " class="form-control">
                           </div>
                           <div class="col-md-4">
                              <label>Tutar (₺)</label>
                              <input type="tel" required {{($pageindex==111) ? 'disabled': ''}} name="taksit_tutar" id='taksit_tutar' value="" class="form-control try-currency">
                           </div>
                        </div>
                        <div class="modal-footer">
                           <button type="submit" disabled class="btn btn-success">
                           Kaydet
                           </button>
                           <button  
                              type="button"
                              class="btn btn-danger"
                              data-dismiss="modal"
                              >
                           <i class="fa fa-times"></i>      
                           Kapat
                           </button>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>