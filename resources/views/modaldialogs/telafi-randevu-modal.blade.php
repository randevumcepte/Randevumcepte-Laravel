<div class="modal fade" id="telafi-randevu-modal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:100040;">
   <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:560px;">
      <div class="modal-content" style="border-radius:12px;overflow:hidden;">
         <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#fb923c);color:#fff;">
            <h5 class="modal-title"><i class="fa fa-undo"></i> &nbsp;Telafi Randevusu Oluştur</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:.9;"><span>&times;</span></button>
         </div>
         <div class="modal-body" style="padding:20px;">
            <div class="alert alert-info" style="font-size:12.5px;padding:10px 12px;margin-bottom:14px;">
               Ücretsiz telafi randevusu oluşturulur. Personel değişirse orijinal satışın primi
               yeni personele devredilir (prim ödenmemişse adisyonu güncelleriz, ödenmişse +/− prim
               hareketi yazılır).
            </div>

            <form id="telafi-randevu-form">
               <input type="hidden" id="telafi_orijinal_rh_id" name="orijinal_rh_id">

               <div style="display:flex;gap:10px;flex-wrap:wrap;font-size:12.5px;color:#374151;margin-bottom:12px;">
                  <div><strong>Müşteri:</strong> <span id="telafi_musteri">—</span></div>
                  <div><strong>Hizmet:</strong> <span id="telafi_hizmet">—</span></div>
               </div>

               <div class="row g-2">
                  <div class="col-md-6">
                     <label class="small"><i class="fa fa-calendar"></i> Tarih</label>
                     <input type="text" id="telafi_tarih" name="tarih" class="form-control form-control-sm geriye-yonelik" autocomplete="off" value="{{ date('Y-m-d') }}" required>
                  </div>
                  <div class="col-md-3">
                     <label class="small"><i class="fa fa-clock-o"></i> Saat</label>
                     <input type="time" id="telafi_saat" name="saat" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-3">
                     <label class="small">Süre (dk)</label>
                     <input type="number" id="telafi_sure" name="sure_dk" class="form-control form-control-sm" min="5" step="5" value="30">
                  </div>
               </div>

               <div class="row g-2" style="margin-top:8px;">
                  <div class="col-md-12">
                     <label class="small"><i class="fa fa-user"></i> Personel (telafiyi yapacak)</label>
                     <select id="telafi_personel" name="personel_id" class="form-control form-control-sm" required>
                        <option value="">Seçin…</option>
                     </select>
                     <small class="text-muted" id="telafi_personel_bilgi" style="font-size:11px;"></small>
                  </div>
               </div>

               <div class="row g-2" style="margin-top:8px;">
                  <div class="col-md-6">
                     <label class="small">Oda (opsiyonel)</label>
                     <select id="telafi_oda" name="oda_id" class="form-control form-control-sm">
                        <option value="">—</option>
                     </select>
                  </div>
                  <div class="col-md-6">
                     <label class="small">Cihaz (opsiyonel)</label>
                     <select id="telafi_cihaz" name="cihaz_id" class="form-control form-control-sm">
                        <option value="">—</option>
                     </select>
                  </div>
               </div>

               <div class="row g-2" style="margin-top:8px;">
                  <div class="col-md-12">
                     <label class="small">Not</label>
                     <textarea id="telafi_not" name="not" rows="2" class="form-control form-control-sm" placeholder="Örn. tırnak düzeltme"></textarea>
                  </div>
               </div>
            </form>
         </div>
         <div class="modal-footer" style="padding:12px 20px;background:#f9fafb;">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fa fa-times"></i> Vazgeç</button>
            <button type="button" class="btn btn-warning" id="telafi-olustur-btn"><i class="fa fa-check"></i> Telafi Randevusunu Oluştur</button>
         </div>
      </div>
   </div>
</div>

<script>
(function(){
   if(window._telafiModalWired) return;
   window._telafiModalWired = true;

   var $modal = $('#telafi-randevu-modal');
   var sube   = $('input[name="sube"]').first().val();

   // Personel + oda + cihaz dropdownlarini randevu modal cachinden al (varsa).
   function doldurDropdown($sel, liste, keyAd, keyId){
      $sel.find('option:not([value=""])').remove();
      (liste || []).forEach(function(o){
         $sel.append(new Option(o[keyAd] || o.ad || '', o[keyId] || o.id));
      });
   }

   function dropdownlariDoldur(){
      var d = (window.randevuModalData || {});
      doldurDropdown($('#telafi_personel'), d.personeller || [], 'ad', 'id');
      doldurDropdown($('#telafi_oda'),      d.odalar      || [], 'ad', 'id');
      doldurDropdown($('#telafi_cihaz'),    d.cihazlar    || [], 'ad', 'id');
   }

   // Datepicker (sayfadaki global air-datepicker/bootstrap datepicker init'inde geriye-yonelik class'ina baglanir)

   $(document).on('click', '.telafi-randevu-ac', function(e){
      e.preventDefault();
      var $b = $(this);
      $('#telafi_orijinal_rh_id').val($b.data('rh-id'));
      $('#telafi_sure').val($b.data('sure') || 30);
      $('#telafi_musteri').text($b.data('musteri') || '—');
      $('#telafi_hizmet').text($b.data('hizmet') || '—');
      $('#telafi_tarih').val('{{ date("Y-m-d") }}');
      $('#telafi_saat').val('');
      $('#telafi_not').val('');
      dropdownlariDoldur();
      // Varsayilan: ayni personel/oda/cihaz
      setTimeout(function(){
         $('#telafi_personel').val($b.data('personel-id') || '');
         $('#telafi_oda').val($b.data('oda-id') || '');
         $('#telafi_cihaz').val($b.data('cihaz-id') || '');
         _personelInfoYenile();
      }, 50);
      $modal.modal('show');
   });

   $(document).on('change', '#telafi_personel', _personelInfoYenile);
   function _personelInfoYenile(){
      var yeniId = parseInt($('#telafi_personel').val() || 0, 10);
      var origId = parseInt($('.telafi-randevu-ac').first().data('personel-id') || 0, 10);
      var $info = $('#telafi_personel_bilgi');
      if(yeniId && origId && yeniId !== origId){
         $info.html('<i class="fa fa-exchange text-warning"></i> Farklı personel seçildi — prim devredilecek.').removeClass('text-muted').addClass('text-dark');
      } else {
         $info.html('Aynı personel: prim devri yapılmayacak.').removeClass('text-dark').addClass('text-muted');
      }
   }

   $(document).on('click', '#telafi-olustur-btn', function(){
      var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Oluşturuluyor...');
      $.ajax({
         type: 'POST',
         url: '/isletmeyonetim/telafi-randevu-olustur',
         dataType: 'json',
         data: {
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val(),
            orijinal_rh_id: $('#telafi_orijinal_rh_id').val(),
            tarih: $('#telafi_tarih').val(),
            saat:  $('#telafi_saat').val(),
            sure_dk: $('#telafi_sure').val(),
            personel_id: $('#telafi_personel').val(),
            oda_id:  $('#telafi_oda').val(),
            cihaz_id:$('#telafi_cihaz').val(),
            not:     $('#telafi_not').val()
         }
      }).done(function(res){
         if(res && res.ok){
            if(typeof swal !== 'undefined'){
               swal({type:'success', title:'Telafi randevusu oluşturuldu', timer:1800, showConfirmButton:false});
            }
            $modal.modal('hide');
            try { if(window._rcDetayCache) window._rcDetayCache = {}; } catch(_){}
            try { $('#modal-view-event').modal('hide'); } catch(_){}
            if($('#calendar').length && typeof takvimyukle === 'function') takvimyukle(true, true);
         } else {
            swal({type:'warning', title:'Uyarı', text: (res && res.hata) || 'Oluşturulamadı', showConfirmButton:true});
         }
      }).fail(function(xhr){
         var msg = 'Hata';
         try { var j = JSON.parse(xhr.responseText); if(j && j.hata) msg = j.hata; } catch(_){}
         swal({type:'error', title:'Hata', text: msg, showConfirmButton:true});
      }).always(function(){
         $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Telafi Randevusunu Oluştur');
      });
   });
})();
</script>
