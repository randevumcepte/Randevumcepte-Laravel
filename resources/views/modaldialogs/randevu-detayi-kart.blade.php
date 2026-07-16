<button style="display:none;" id="randevudetayigetir" data-toggle="modal" data-target="#modal-view-event"></button>

<style>
   /* Tam viewport ortasi (sidebar'dan etkilenmesin) */
   /* z-index: swal v1 99999 kullaniyor, modal'in onun ustunde kalmasi icin 100001 */
   #modal-view-event {
      position: fixed !important;
      inset: 0;
      width: 100vw; height: 100vh;
      z-index: 100001;
   }
   #modal-view-event .modal-dialog {
      max-width: 800px;
      width: calc(100% - 24px);
      margin: auto !important;
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh; pointer-events: none;
   }
   #modal-view-event .modal-content {
      border: 0; border-radius: 14px; overflow: hidden;
      box-shadow: 0 24px 60px rgba(92, 0, 142, .22);
      width: 100%;
      pointer-events: auto;
   }
   #modal-view-event .modal-header {
      background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
      color: #fff; padding: 12px 50px 12px 18px; border-bottom: 0;
      display: flex; align-items: center; gap: 10px;
      position: relative; min-height: 0;
   }
   #modal-view-event .modal-header .h4 {
      margin: 0; font-weight: 700;
      color: #fff !important; flex: 1 1 0; min-width: 0;
      display: flex; flex-direction: column; line-height: 1.2;
   }
   #modal-view-event .event-title { color:#fff; display: flex; flex-direction: column; gap: 1px; }
   #modal-view-event .rd-mt-name {
      font-size: 15px; font-weight: 700; line-height: 1.25;
      word-break: break-word;
   }
   #modal-view-event .rd-mt-sub {
      font-size: 11.5px; font-weight: 500; opacity: .82;
      line-height: 1.2; letter-spacing: .1px;
   }
   #modal-view-event .modal-header .close {
      position: absolute; top: 10px; right: 10px;
      color: #fff; opacity: .9; font-size: 20px; line-height: 1;
      background: rgba(255,255,255,.12); border: 0;
      width: 28px; height: 28px; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      transition: background .15s, opacity .15s;
      text-shadow: none; z-index: 5;
   }
   #modal-view-event .modal-header .close:hover { background: rgba(255,255,255,.28); opacity: 1; }
   #modal-view-event #duzenle_butonu_bolumu {
      display: flex; gap: 6px; align-items: center; flex-wrap: wrap;
      flex: 0 0 auto; margin: 0;
   }
   #modal-view-event #duzenle_butonu_bolumu:empty { display: none; }
   #modal-view-event #duzenle_butonu_bolumu .btn {
      border-radius: 7px; font-weight: 600; padding: 9px 14px; font-size: 12.5px; line-height: 1.2;
      display: inline-flex; align-items: center; gap: 5px;
   }
   #modal-view-event .modal-body { padding: 18px 18px 14px; background:#fff; }
   #modal-view-event .modal-footer.event-buttons {
      padding: 12px 18px; background: #fbfafd; border-top: 1px solid #ece6f3;
      display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;
   }
   #modal-view-event .modal-footer.event-buttons .btn {
      border-radius: 8px; font-weight: 600; padding: 8px 18px; font-size: 13px;
      flex: 0 1 auto;
   }
   @media (max-width: 600px) {
      #modal-view-event .modal-dialog { max-width: 96%; margin: 10px auto; }
      #modal-view-event .modal-header .h4 { font-size: 14.5px; }
      #modal-view-event .modal-footer.event-buttons .btn { flex: 1 1 100%; }
   }
</style>

<div class="modal fade bs-example-modal-lg" id="modal-view-event" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="h4">
               <span class="event-title"></span>
            </h4>
            <div id="duzenle_butonu_bolumu"></div>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
         </div>
         <div class="modal-body">
            <div class="event-body"></div>
         </div>
         <div class="modal-footer event-buttons"></div>
      </div>
   </div>
</div>

<script>
   // Modal acilirken body'ye tasi — parent container'lar centering'i bozmasin
   $(document).on('show.bs.modal', '#modal-view-event', function () {
      if (this.parentNode !== document.body) {
         document.body.appendChild(this);
      }
   });

   // Tek-tik memnuniyet anketi gonderim (musteri kartindaki anketHizliGonder ile ayni akis).
   // Randevu detay kartinin header'indaki "Memnuniyet Anketi Gönder" butonu.
   $(document).on('click', '.anket-hizli-gonder-btn', function (e) {
      e.preventDefault();
      var userId = $(this).data('userid');
      var sube   = $(this).data('sube') || (new URLSearchParams(location.search)).get('sube') || '';
      if (!userId) { swal('Hata', 'Müşteri bulunamadı.', 'error'); return; }
      swal({
         title: 'Memnuniyet anketi gönderilsin mi?',
         text: 'Müşteriye anket linki WhatsApp veya SMS ile gönderilecek.',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: 'Evet, gönder',
         cancelButtonText: 'Vazgeç',
         confirmButtonColor: '#25D366',
      }).then(function (r) {
         if (!r || !r.value) return;
         $('#preloader').show();
         $.ajax({
            url: '/isletmeyonetim/anket-hizli-gonder',
            method: 'POST',
            timeout: 20000,
            data: { user_id: userId, sube: sube },
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': ($('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()) },
         }).done(function (res) {
            if (res && res.basarili) {
               var kanal = res.kanal === 'whatsapp' ? 'WhatsApp' : 'SMS';
               swal('Gönderildi', 'Anket ' + kanal + ' ile iletildi.', 'success');
            } else {
               swal('Gönderilemedi', (res && res.mesaj) || 'Bilinmeyen hata.', 'error');
            }
         }).fail(function (xhr, status) {
            var msg = 'İstek başarısız.';
            if (xhr && xhr.responseText) { try { var j = JSON.parse(xhr.responseText); if (j && j.mesaj) msg = j.mesaj; } catch (e) {} }
            if (xhr && xhr.status) msg += ' (HTTP ' + xhr.status + ')';
            if (status === 'timeout') msg = 'Sunucu 20 saniye içinde cevap vermedi.';
            swal('Hata', msg, 'error');
         }).always(function () { $('#preloader').hide(); });
      });
   });
</script>
