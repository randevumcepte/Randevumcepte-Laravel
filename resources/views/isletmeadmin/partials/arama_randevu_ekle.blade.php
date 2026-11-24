{{-- Manuel Arama Randevusu Ekle — Arama Randevu Takvimi ve On Gorusme Takvimi ORTAK kullanir.
     Acmak : window.aramaRandevuEkleAc({tarih, saat, personelId, musteriId, musteriAd}) (hepsi istege bagli)
     Olaylar: 'arama:randevu-eklendi' {tarih, saat, personelId}; 'arama:personeller' [liste]
     $aramaYalnizPersonel (opsiyonel): verilirse secimde SADECE bu personel cikar (kendi kaydina kisitli kullanici). --}}
@php
   $_areYalniz = !empty($aramaYalnizPersonel) ? [['id' => (int) $aramaYalnizPersonel->id, 'personel_adi' => $aramaYalnizPersonel->personel_adi]] : null;
@endphp
<div class="modal fade" id="art_ekle_modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius:16px;">
         <div class="modal-header" style="background:linear-gradient(120deg,#5C008E,#7B2FB8);color:#fff;border-top-left-radius:16px;border-top-right-radius:16px;border-bottom:none;">
            <h5 style="margin:0;font-weight:800;color:#fff;"><i class="fa fa-calendar-plus-o"></i> Arama Randevusu Ekle</h5>
            <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;"><span>&times;</span></button>
         </div>
         <div class="modal-body" style="padding:18px 20px;">
            <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Müşteri</label>
            <select id="are_musteri" class="form-control" style="width:100%;margin-bottom:12px;"></select>

            <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Arayacak personel</label>
            <select id="are_personel" class="form-control" style="width:100%;margin-bottom:12px;"><option value="">Personel seçin...</option></select>

            <div class="row">
               <div class="col-7">
                  <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Tarih</label>
                  <input type="text" id="are_tarih" class="form-control" autocomplete="off" readonly placeholder="Tarih seçin">
               </div>
               <div class="col-5">
                  <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin-bottom:4px;">Saat</label>
                  <select id="are_saat" class="form-control"></select>
               </div>
            </div>

            <label style="font-size:12.5px;font-weight:700;color:#4b5163;display:block;margin:12px 0 4px;">Not (opsiyonel)</label>
            <textarea id="are_not" class="form-control" rows="2" placeholder="Görüşme notu / hatırlatma..."></textarea>
         </div>
         <div class="modal-footer" style="border:none;padding:0 20px 18px;">
            <button type="button" id="are_kaydet" style="width:100%;background:linear-gradient(120deg,#6d28d9,#7c3aed);border:none;color:#fff;font-weight:800;border-radius:10px;padding:12px;cursor:pointer;font-size:14px;"><i class="fa fa-check"></i> Arama Randevusu Ekle</button>
         </div>
      </div>
   </div>
</div>

<script>
$(document).ready(function(){
   var SUBE = {{ (int) $isletme->id }}, TOKEN = '{{ csrf_token() }}', BUGUN = '{{ date('Y-m-d') }}';
   var YALNIZ = {!! json_encode($_areYalniz, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
   var $modal = $('#art_ekle_modal'), yuklendi = false, yukleniyor = false, bekleyenPid = '';

   function esc(s){ return $('<div>').text(s==null?'':s).html(); }
   function uyar(tip, baslik, metin){ if (typeof swal==='function') swal({type:tip, title:baslik, text:metin}); }
   function s2Kapat(sel){ if ($.fn.select2 && $(sel).hasClass('select2-hidden-accessible')){ try{ $(sel).select2('destroy'); }catch(e){} } }

   function personelYaz(ps){
      var o = '<option value="">Personel seçin...</option>';
      ps.forEach(function(p){ o += '<option value="'+p.id+'">'+esc(p.personel_adi)+'</option>'; });
      $('#are_personel').html(o);
      yuklendi = true;
      personelSec(bekleyenPid);
      $(document).trigger('arama:personeller', [ps]);
   }
   // Listede olmayan (giris hesabi olmayan / gizli) personel secilmez, alan bos kalir
   function personelSec(pid){
      if (YALNIZ) pid = YALNIZ[0].id;
      var $p = $('#are_personel');
      $p.val(pid && $p.find('option[value="'+pid+'"]').length ? String(pid) : '').trigger('change');
   }
   window.aramaPersonelleriYukle = function(){
      if (YALNIZ){ personelYaz(YALNIZ); return; }
      if (yukleniyor) return;
      yukleniyor = true;
      $.get('/isletmeyonetim/arama-personelleri', { sube: SUBE, randevu: 1 }, function(res){ personelYaz(res || []); })
         .always(function(){ yukleniyor = false; });
   };

   // Saat slotlari (15 dk, 08:00-22:00); disaridan gelen slot disi saat da secilebilsin
   function saatSlotlari(secili){
      var l = [];
      for (var h=8; h<=22; h++) for (var m=0; m<60; m+=15){ if (h===22 && m>0) break; l.push(('0'+h).slice(-2)+':'+('0'+m).slice(-2)); }
      if (secili && l.indexOf(secili) === -1){ l.push(secili); l.sort(); }
      $('#are_saat').html('<option value="">Saat seçin...</option>'+l.map(function(s){ return '<option value="'+s+'">'+s+'</option>'; }).join('')).val(secili || '');
   }

   window.aramaRandevuEkleAc = function(v){
      v = v || {};
      $('#are_not').val('');
      saatSlotlari(v.saat || '');
      $('#are_tarih').val(v.tarih || BUGUN);
      $('#are_musteri').empty();
      if (v.musteriId) $('#are_musteri').append(new Option(v.musteriAd || ('#'+v.musteriId), v.musteriId, true, true));
      bekleyenPid = v.personelId || '';
      if (yuklendi) personelSec(bekleyenPid); else window.aramaPersonelleriYukle();
      $modal.modal('show');
   };

   $modal.on('shown.bs.modal', function(){
      if ($.fn.select2){
         ['#are_musteri','#are_personel','#are_saat'].forEach(s2Kapat);
         $('#are_musteri').select2({
            width:'100%', dropdownParent: $modal, placeholder:'Müşteri ara (ad / telefon)...', minimumInputLength:2,
            ajax: {
               url:'/isletmeyonetim/cagri-musteri-select', dataType:'json', delay:250,
               data: function(params){ return { q: params.term, sube: SUBE }; },
               processResults: function(data){ return { results: (data && data.results) ? data.results : [] }; }
            }
         });
         $('#are_personel').select2({ width:'100%', dropdownParent: $modal, placeholder:'Personel seçin...' });
         $('#are_saat').select2({ width:'100%', dropdownParent: $modal, placeholder:'Saat seçin...' });
      }
      try { if ($.fn.datepicker){ $('#are_tarih').datepicker({ language:'tr', autoClose:true, dateFormat:'yyyy-mm-dd' }); } } catch(e){}
   });
   $modal.on('hidden.bs.modal', function(){ ['#are_musteri','#are_personel','#are_saat'].forEach(s2Kapat); });

   $('#are_kaydet').on('click', function(){
      var musteriId = $('#are_musteri').val(), personelId = $('#are_personel').val();
      var tarih = $('#are_tarih').val(), saat = $('#are_saat').val();
      if (!musteriId){ uyar('warning','Müşteri seçin','Arama randevusu için müşteri seçin.'); return; }
      if (!personelId){ uyar('warning','Personel seçin','Arayacak personeli seçin.'); return; }
      if (!tarih || !saat){ uyar('warning','Tarih/saat','Tarih ve saat seçin.'); return; }
      var $btn = $(this).prop('disabled', true);
      $.post('/isletmeyonetim/cagri-arama-randevu-ekle',
         { musteri_id:musteriId, personel_id:personelId, tarih:tarih, saat:saat, not:($('#are_not').val()||''), sube:SUBE, _token:TOKEN },
         function(res){
            if (res && res.success){
               $modal.modal('hide');
               if (typeof swal==='function') swal({type:'success',title:'Eklendi',text:res.message||'Arama randevusu eklendi.',timer:2600,showConfirmButton:false});
               $(document).trigger('arama:randevu-eklendi', [{ tarih:tarih, saat:saat, personelId:personelId }]);
            } else uyar('error','Hata',(res&&res.message)||'Eklenemedi.');
         }
      ).fail(function(x){ uyar('error','Hata',(x.responseJSON&&x.responseJSON.message)||'İşlem başarısız.'); })
       .always(function(){ $btn.prop('disabled', false); });
   });
});
</script>
