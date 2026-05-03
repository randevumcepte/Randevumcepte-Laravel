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
                              $hizmetler = \DB::table('salon_sunulan_hizmetler')
                                 ->leftJoin('hizmetler','salon_sunulan_hizmetler.hizmet_id','=','hizmetler.id')
                                 ->where('salon_sunulan_hizmetler.salon_id',$isletme->id)
                                 ->select('salon_sunulan_hizmetler.id','hizmetler.hizmet_adi','salon_sunulan_hizmetler.son_fiyat')
                                 ->get();
                           } catch(\Exception $e){ $hizmetler = collect(); }
                        @endphp
                        @foreach($hizmetler as $h)
                           <option value="{{$h->id}}" data-fiyat="{{$h->son_fiyat ?? 0}}">{{ $h->hizmet_adi ?? '-' }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 form-group">
                     <label><b>Paket Seç</b> <small class="text-muted">(opsiyonel)</small></label>
                     <select name="paket_id" id="sozlesme_paket" class="form-control opsiyonelSelect" style="width:100%;">
                        <option value="">— Paket seçin —</option>
                        @php
                           try {
                              $paketlerListesi = \DB::table('paketler')->where('salon_id',$isletme->id)->get();
                           } catch(\Exception $e){ $paketlerListesi = collect(); }
                        @endphp
                        @foreach($paketlerListesi as $p)
                           <option value="{{$p->id}}" data-fiyat="{{$p->paket_fiyati ?? $p->fiyat ?? 0}}" data-seans="{{$p->seans_sayisi ?? 1}}">{{ $p->paket_adi }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Seans Sayısı</b></label>
                     <input type="number" name="seans_sayisi" id="sozlesme_seans" class="form-control" min="1" value="1">
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Toplam Ücret (₺) *</b></label>
                     <div style="position:relative;">
                        <input type="text" id="sozlesme_toplam_display" class="form-control tl-input" inputmode="decimal" required placeholder="0,00" style="padding-right:30px;">
                        <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#666; font-weight:600;">₺</span>
                        <input type="hidden" name="toplam_ucret" id="sozlesme_toplam">
                     </div>
                  </div>
                  <div class="col-md-4 form-group">
                     <label><b>Kapora (₺)</b></label>
                     <div style="position:relative;">
                        <input type="text" id="sozlesme_kapora_display" class="form-control tl-input" inputmode="decimal" placeholder="0,00" style="padding-right:30px;">
                        <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#666; font-weight:600;">₺</span>
                        <input type="hidden" name="kapora" id="sozlesme_kapora" value="0">
                     </div>
                  </div>
                  <div class="col-md-12 form-group">
                     <label><b>Sözleşme Şartları *</b> <small class="text-muted">(müşteriye gösterilecek metin — düzenleyebilirsiniz)</small></label>
                     <textarea name="sozlesme_metni" id="sozlesme_metni" class="form-control" rows="10" required style="font-size:13px;font-family:monospace;"></textarea>
                     <small class="text-muted">İpucu: Müşteri adı, hizmet, fiyat ve kapora otomatik olarak metnin üstünde tabloda gösterilir — burada sadece sözleşme şartlarını yazın.</small>
                  </div>
                  <div class="col-md-12 form-group">
                     <label><b>Ek Not</b> <small class="text-muted">(opsiyonel — sözleşmenin altında ayrıca görünür)</small></label>
                     <textarea name="sozlesme_notu" class="form-control" rows="2" placeholder="Örn: Seans aralığı 15 günü geçemez."></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
               <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Oluştur ve Müşteriye Gönder</button>
            </div>
         </div>
      </form>
   </div>
</div>
<script>
function sozlesmeTlFormat(num){
   if(num === null || num === undefined || num === '') return '';
   var n = parseFloat(num);
   if(isNaN(n)) return '';
   return n.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function sozlesmeTlParse(str){
   if(!str) return 0;
   var s = String(str).replace(/[^0-9,.-]/g,'').replace(/\./g,'').replace(',','.');
   var n = parseFloat(s);
   return isNaN(n) ? 0 : n;
}
function sozlesmeTlInputBind(displayId, hiddenId){
   var $disp = $('#'+displayId), $hid = $('#'+hiddenId);
   $disp.off('input.tl blur.tl');
   $disp.on('input.tl', function(){
      var raw = $(this).val();
      var parsed = sozlesmeTlParse(raw);
      $hid.val(parsed);
   });
   $disp.on('blur.tl', function(){
      var parsed = sozlesmeTlParse($(this).val());
      if(parsed > 0) $(this).val(sozlesmeTlFormat(parsed));
      $hid.val(parsed);
   });
}

function sozlesmeVarsayilanMetin(){
   return '1. Bu sözleşme {{ addslashes($isletme->salon_adi) }} ile yukarıda bilgileri yazılı müşteri arasında akdedilmiştir.\n' +
          '2. Müşteri, alacağı hizmet/paket karşılığında belirtilen toplam ücreti ödemeyi kabul ve taahhüt eder.\n' +
          '3. Kapora/ön ödeme alındığı durumda kalan bakiye, hizmet süresi içerisinde tahsil edilecektir.\n' +
          '4. Müşteri belirlenen randevu saatlerinde hazır bulunmakla yükümlüdür. Mazeretsiz iptaller veya gelmemeler için ücret iadesi yapılmaz.\n' +
          '5. İşletme, hizmeti taahhüt edilen kalitede sunmakla yükümlüdür.\n' +
          '6. Taraflar bu sözleşmeyi okuyup, anladığını ve kabul ettiğini beyan eder.';
}

$(document).ready(function(){
   // Modal açılınca select2'yi yeniden başlat (dialog içindeki select için gerekli)
   $('#sozlesmeOlusturModal').on('shown.bs.modal', function(){
      if(!$('#sozlesme_metni').val().trim()){
         $('#sozlesme_metni').val(sozlesmeVarsayilanMetin());
      }
      sozlesmeTlInputBind('sozlesme_toplam_display', 'sozlesme_toplam');
      sozlesmeTlInputBind('sozlesme_kapora_display', 'sozlesme_kapora');
      try {
         if($('#sozlesme_musteri').data('select2')) $('#sozlesme_musteri').select2('destroy');
      } catch(e){}
      $('#sozlesme_musteri').select2({
         placeholder: 'Müşteri seçin (en az 2 karakter)',
         allowClear: true,
         dropdownParent: $('#sozlesmeOlusturModal'),
         minimumInputLength: 2,
         language: {
            inputTooShort: function(){ return 'En az 2 karakter girin.'; },
            searching: function(){ return 'Aranıyor...'; },
            noResults: function(){ return 'Sonuç bulunamadı.'; }
         },
         ajax: {
            url: '/isletmeyonetim/musteri-arama-bolumu-verileri',
            dataType: 'json',
            delay: 250,
            data: function(params){
               return { query: params.term || '', normalized_query: params.term || '', sube: '{{$isletme->id}}', aramaMi: false };
            },
            processResults: function(data){
               return { results: data.map(function(m){ return { id: m.id, text: m.ad_soyad }; }) };
            }
         }
      });
   });

   // Müşteri seçimi → telefonu otomatik doldur (endpoint: musteri_id, dönen: telefon)
   $(document).on('change','#sozlesme_musteri', function(){
      var musteriId = $(this).val();
      $('#sozlesme_telefon').val('');
      if(!musteriId || musteriId === '0') return;
      $.ajax({
         url: '/isletmeyonetim/formmusteribilgigetir',
         type: 'GET',
         dataType: 'json',
         data: { musteri_id: musteriId, sube: '{{$isletme->id}}' },
         success: function(result){
            if(result && result.telefon){
               $('#sozlesme_telefon').val(result.telefon);
            }
         }
      });
   });
   // Paket seçilince fiyat/seans otomatik doldur
   $('#sozlesme_paket').on('change', function(){
      var sel = $(this).find('option:selected');
      var fiyat = sel.data('fiyat'); var seans = sel.data('seans');
      if(fiyat){
         $('#sozlesme_toplam').val(fiyat);
         $('#sozlesme_toplam_display').val(sozlesmeTlFormat(fiyat));
      }
      if(seans) $('#sozlesme_seans').val(seans);
   });
   // Hizmet seçilince fiyat doldur
   $('#sozlesme_hizmet').on('change', function(){
      var fiyat = $(this).find('option:selected').data('fiyat');
      if(fiyat && !$('#sozlesme_toplam').val()){
         $('#sozlesme_toplam').val(fiyat);
         $('#sozlesme_toplam_display').val(sozlesmeTlFormat(fiyat));
      }
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
