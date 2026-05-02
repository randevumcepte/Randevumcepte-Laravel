<div id="sozlesmeOlusturModal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog" style="max-width: 700px">
      <form id="sozlesmeOlusturForm">
         {{ csrf_field() }}
         <input type="hidden" name="sube" value="{{$isletme->id}}">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="h4">Hizmet Sözleşmesi Oluştur</h4>
               <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="padding:20px;">
               <div class="row">
                  <div class="col-md-6 form-group">
                     <label><b>Müşteri *</b></label>
                     <select name="user_id" id="sozlesme_musteri" class="form-control opsiyonelSelect musteri_secimi" style="width:100%;" required>
                        <option></option>
                     </select>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Cep Telefon *</b></label>
                     <input type="tel" name="cep_telefon" id="sozlesme_telefon" class="form-control" required>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Hizmet Seç</b></label>
                     <select name="hizmet_id" id="sozlesme_hizmet" class="form-control opsiyonelSelect" style="width:100%;">
                        <option value="">— Hizmet seçin —</option>
                        @php
                           try {
                              $hizmetler = \App\SalonSunulanHizmetler::where('salon_id',$isletme->id)
                                 ->where('aktif',1)
                                 ->with('hizmet')
                                 ->get();
                           } catch(\Exception $e){ $hizmetler = collect(); }
                        @endphp
                        @foreach($hizmetler as $h)
                           <option value="{{$h->id}}" data-fiyat="{{$h->fiyat ?? 0}}">{{ $h->hizmet->hizmet_adi ?? '-' }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Paket Seç</b> <small class="text-muted">(opsiyonel)</small></label>
                     <select name="paket_id" id="sozlesme_paket" class="form-control opsiyonelSelect" style="width:100%;">
                        <option value="">— Paket seçin —</option>
                        @php
                           try {
                              $paketlerListesi = \App\Paket::where('salon_id',$isletme->id)->where('aktif',1)->get();
                           } catch(\Exception $e){ $paketlerListesi = collect(); }
                        @endphp
                        @foreach($paketlerListesi as $p)
                           <option value="{{$p->id}}" data-fiyat="{{$p->paket_fiyati ?? 0}}" data-seans="{{$p->seans_sayisi ?? 1}}">{{ $p->paket_adi }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Seans Sayısı</b></label>
                     <input type="number" name="seans_sayisi" id="sozlesme_seans" class="form-control" min="1" value="1">
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Toplam Ücret (₺) *</b></label>
                     <input type="number" name="toplam_ucret" id="sozlesme_toplam" class="form-control" step="0.01" min="0" required>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Kapora (₺)</b></label>
                     <input type="number" name="kapora" id="sozlesme_kapora" class="form-control" step="0.01" min="0" value="0">
                  </div>
                  <div class="col-md-12 form-group">
                     <label><b>Ek Not</b> <small class="text-muted">(opsiyonel — sözleşmede görünür)</small></label>
                     <textarea name="sozlesme_notu" class="form-control" rows="2" placeholder="Örn: Seans aralığı 15 günü geçemez."></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
               <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Sözleşmeyi Gönder</button>
            </div>
         </div>
      </form>
   </div>
</div>
<script>
$(document).ready(function(){
   // Müşteri seçimi → telefonu otomatik doldur
   $('#sozlesme_musteri').on('change', function(){
      var userId = $(this).val();
      if(!userId) return;
      $.get('/isletmeyonetim/formmusteribilgigetir', { user_id: userId, sube: '{{$isletme->id}}' }, function(data){
         if(data && data.cep_telefon) $('#sozlesme_telefon').val(data.cep_telefon);
      });
   });
   // Paket seçilince fiyat/seans otomatik doldur
   $('#sozlesme_paket').on('change', function(){
      var sel = $(this).find('option:selected');
      var fiyat = sel.data('fiyat'); var seans = sel.data('seans');
      if(fiyat) $('#sozlesme_toplam').val(fiyat);
      if(seans) $('#sozlesme_seans').val(seans);
   });
   // Hizmet seçilince fiyat doldur
   $('#sozlesme_hizmet').on('change', function(){
      var fiyat = $(this).find('option:selected').data('fiyat');
      if(fiyat && !$('#sozlesme_toplam').val()) $('#sozlesme_toplam').val(fiyat);
   });
   // Form submit
   $('#sozlesmeOlusturForm').on('submit', function(e){
      e.preventDefault();
      var $btn = $(this).find('button[type=submit]');
      $btn.prop('disabled', true).text('Gönderiliyor...');
      $.ajax({
         url: '/isletmeyonetim/sozlesme-olustur',
         type: 'POST',
         dataType: 'json',
         data: $(this).serialize(),
         success: function(resp){
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Sözleşmeyi Gönder');
            if(resp && resp.basarili){
               $('#sozlesmeOlusturModal').modal('hide');
               Swal.fire('Başarılı', 'Sözleşme oluşturuldu ve müşteriye SMS gönderildi.', 'success');
               setTimeout(function(){ location.reload(); }, 1500);
            } else {
               Swal.fire('Hata', (resp && resp.mesaj) ? resp.mesaj : 'Bir hata oluştu.', 'error');
            }
         },
         error: function(xhr){
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Sözleşmeyi Gönder');
            Swal.fire('Hata', 'Sunucu hatası: '+xhr.status, 'error');
         }
      });
   });
});
</script>
