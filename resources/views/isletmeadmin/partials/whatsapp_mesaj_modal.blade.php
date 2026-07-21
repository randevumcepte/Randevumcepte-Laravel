<style>
/* Takvim detay modali z-index 100001 — compose modal onun da ustunde kalsin */
/* Tam viewport ortasi: Bootstrap'in scrollbar telafisi (body/modal padding-right) ortalamayi
   bozar; padding'i sifirlayip .modal-dialog'u flex ile kesin ortala. */
#whatsapp-mesaj-modal {
   z-index:100060 !important;
   position:fixed !important; inset:0; padding:0 !important;
}
#whatsapp-mesaj-modal .modal-dialog {
   max-width:720px; width:calc(100% - 32px);
   margin:auto !important;
   display:flex; align-items:center; justify-content:center;
   min-height:100%; pointer-events:none;
}
#whatsapp-mesaj-modal .modal-content.wam-modal { pointer-events:auto; width:100%; }
#whatsapp-mesaj-modal .wam-modal * { box-sizing:border-box; }
.wam-modal {
   border-radius:20px; border:0; overflow:hidden;
   box-shadow:0 30px 80px rgba(17,24,39,.28);
   font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",sans-serif;
}

/* Header */
.wam-header {
   display:flex; align-items:center; gap:13px;
   padding:20px 24px; background:#fff;
   border-bottom:1px solid #eef0f4; position:relative;
}
.wam-header .wam-icon {
   width:46px; height:46px; border-radius:14px;
   background:linear-gradient(135deg,#25D366,#128C7E); color:#fff;
   display:inline-flex; align-items:center; justify-content:center;
   font-size:23px; flex-shrink:0; box-shadow:0 6px 16px rgba(37,211,102,.35);
}
.wam-header h2 { margin:0; font-size:18px; color:#111827; font-weight:700; letter-spacing:-.2px; }
.wam-header p { margin:2px 0 0; font-size:12.5px; color:#8a94a6; }
.wam-close {
   position:absolute; top:16px; right:16px;
   background:#f3f4f6; border:0; font-size:18px; line-height:1;
   color:#6b7280; cursor:pointer; transition:all .15s;
   width:32px; height:32px; border-radius:50%;
   display:inline-flex; align-items:center; justify-content:center;
}
.wam-close:hover { color:#ef4444; background:#fee2e2; }

/* Body */
.wam-body { padding:20px 24px 8px; max-height:64vh; overflow-y:auto; background:#fff; }

/* Musteri karti */
.wam-musteri {
   display:flex; align-items:center; gap:13px;
   padding:14px 16px; margin-bottom:18px;
   background:#f9fafb; border:1px solid #eef0f4; border-radius:14px;
}
.wam-musteri .wam-av {
   width:48px; height:48px; border-radius:50%;
   background:linear-gradient(135deg,#7B2FB8,#5C008E); color:#fff; flex-shrink:0;
   display:inline-flex; align-items:center; justify-content:center; font-size:20px;
   box-shadow:0 4px 12px rgba(92,0,142,.30);
}
.wam-musteri .wam-ad { font-size:15.5px; font-weight:700; color:#1f2937; line-height:1.2; }
.wam-musteri .wam-tel { font-size:13px; color:#6b7280; margin-top:3px; display:flex; align-items:center; gap:5px; }
.wam-musteri .wam-tel i { font-size:11px; opacity:.7; }
.wam-musteri .wam-wa-tag {
   margin-left:auto; align-self:center;
   font-size:11px; font-weight:600; color:#128C7E;
   background:#e7f8ef; border:1px solid #c9efd9; border-radius:20px; padding:4px 10px;
   display:inline-flex; align-items:center; gap:5px; white-space:nowrap;
}

/* Uyari */
.wam-uyari {
   display:none; align-items:flex-start; gap:9px;
   padding:11px 13px; margin-bottom:16px;
   background:#fef2f2; border:1px solid #fecaca; border-radius:12px;
   color:#b91c1c; font-size:12px; line-height:1.45;
}
.wam-uyari i { margin-top:2px; font-size:13px; }

/* Mesaj */
.wam-section__title {
   font-size:11px; font-weight:700; color:#6b7280;
   text-transform:uppercase; letter-spacing:.5px;
   margin-bottom:8px; display:flex; align-items:center; gap:6px;
}
.wam-body textarea.form-control {
   width:100%; border-radius:14px; border:1.5px solid #e5e7eb; min-height:150px;
   font-size:14px; padding:13px 15px; resize:vertical; color:#1f2937; line-height:1.5;
   transition:border-color .15s, box-shadow .15s;
}
.wam-body textarea.form-control::placeholder { color:#9ca3af; }
.wam-body textarea.form-control:focus {
   outline:none; border-color:#25D366; box-shadow:0 0 0 4px rgba(37,211,102,.13);
}
.wam-sayac { display:block; text-align:right; color:#9ca3af; font-size:11.5px; margin-top:5px; }

/* Hazir baglanti butonlari (Konum / Instagram / Web) */
.wam-hizli-baslik {
   font-size:11px; font-weight:700; color:#9ca3af;
   text-transform:uppercase; letter-spacing:.5px; margin-top:18px; margin-bottom:9px;
}
.wam-linkler { display:flex; flex-wrap:wrap; gap:9px; }
.wam-link-chip {
   display:inline-flex; align-items:center; gap:6px;
   border-radius:24px; font-size:13px; font-weight:600; padding:9px 16px;
   border:1.5px solid transparent; cursor:pointer; transition:all .15s; line-height:1;
}
.wam-link-chip:hover { transform:translateY(-1px); }
.wam-link-chip:active { transform:translateY(0) scale(.98); }
.wam-link-chip.konum { background:#eff6ff; color:#1d4ed8; border-color:#dbeafe; }
.wam-link-chip.konum:hover { background:#dbeafe; }
.wam-link-chip.insta { background:#fdf2f8; color:#be185d; border-color:#fce7f3; }
.wam-link-chip.insta:hover { background:#fce7f3; }
.wam-link-chip.web { background:#ecfdf5; color:#047857; border-color:#d1fae5; }
.wam-link-chip.web:hover { background:#d1fae5; }

/* Footer */
.wam-footer {
   display:flex; justify-content:flex-end; gap:10px; align-items:center;
   padding:16px 24px; border-top:1px solid #eef0f4; background:#fafbfc;
}
.wam-btn-send {
   background:linear-gradient(135deg,#25D366,#12b455); color:#fff !important;
   padding:11px 24px; border-radius:12px; font-weight:700; font-size:14px;
   border:0; box-shadow:0 8px 20px rgba(37,211,102,.35);
   transition:all .15s; display:inline-flex; align-items:center; gap:8px;
}
.wam-btn-send:hover { box-shadow:0 10px 26px rgba(37,211,102,.45); transform:translateY(-1px); }
.wam-btn-send:disabled { opacity:.55; cursor:not-allowed; box-shadow:none; transform:none; }
.wam-btn-cancel {
   background:#fff; color:#6b7280 !important;
   padding:11px 20px; border-radius:12px; font-weight:600; font-size:14px;
   border:1.5px solid #e5e7eb; transition:all .15s;
}
.wam-btn-cancel:hover { background:#f3f4f6; color:#374151 !important; }

@media (max-width:640px) {
   #whatsapp-mesaj-modal .modal-dialog { max-width:96%; margin:12px auto; }
   .wam-header { padding:16px 18px; }
   .wam-body { padding:16px 18px 6px; }
   .wam-footer { padding:14px 18px; }
   .wam-btn-send, .wam-btn-cancel { flex:1; justify-content:center; }
}
</style>

<div id="whatsapp-mesaj-modal" class="modal fade" tabindex="-1">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content wam-modal">
         <input type="hidden" name="_token" value="{{ csrf_token() }}" id="wam_token">
         <input type="hidden" name="sube" value="{{ $isletme->id }}" id="wam_sube">
         <input type="hidden" id="wam_user_id" value="">

         <div class="wam-header">
            <div class="wam-icon"><i class="fa fa-whatsapp"></i></div>
            <div>
               <h2>WhatsApp Mesajı</h2>
               <p>Müşterinize doğrudan WhatsApp üzerinden yazın.</p>
            </div>
            <button type="button" class="wam-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
         </div>

         <div class="modal-body wam-body">

            <div class="wam-musteri">
               <div class="wam-av"><i class="fa fa-user"></i></div>
               <div>
                  <div class="wam-ad" id="wam_musteri_ad">—</div>
                  <div class="wam-tel"><i class="fa fa-phone"></i> <span id="wam_musteri_tel">—</span></div>
               </div>
               <span class="wam-wa-tag"><i class="fa fa-whatsapp"></i> WhatsApp</span>
            </div>

            <div class="wam-uyari" id="wam_onay_uyari">
               <i class="fa fa-exclamation-triangle"></i>
               <span>Bu müşteri WhatsApp bilgilendirme onayı vermemiş. Mesaj gönderebilirsiniz ancak istenmeyen mesaj şikâyeti ve hesap engellenme riski daha yüksektir. Yalnızca gerçekten gerekliyse gönderin.</span>
            </div>

            <div class="wam-section__title"><i class="fa fa-comment"></i> Mesajınız</div>
            <textarea id="wam_mesaj" class="form-control" maxlength="1000" placeholder="Müşterinize göndermek istediğiniz mesajı buraya yazın..."></textarea>
            <small class="wam-sayac"><span id="wam_sayac">0</span> / 1000</small>

            @php
               $_wamLinkVar = !empty($isletme->konum_linki) || !empty($isletme->instagram_linki) || !empty($isletme->web_linki);
               $_igBaslik  = trim($isletme->instagram_baslik ?? '') ?: 'Instagram';
               $_webBaslik = trim($isletme->web_baslik ?? '') ?: 'Web Sitesi';
            @endphp
            @if($_wamLinkVar)
            <div class="wam-hizli-baslik">Hızlı ekle</div>
            <div class="wam-linkler">
               @if(!empty($isletme->konum_linki))
               <input type="hidden" id="wam_konum_link" value="{{ $isletme->konum_linki }}">
               <button type="button" class="wam-link-chip konum" data-link="#wam_konum_link" data-emoji="📍" data-etiket="Konumumuz">📍 Konum</button>
               @endif
               @if(!empty($isletme->instagram_linki))
               <input type="hidden" id="wam_instagram_link" value="{{ $isletme->instagram_linki }}">
               <button type="button" class="wam-link-chip insta" data-link="#wam_instagram_link" data-emoji="🔗" data-etiket="{{ $_igBaslik }}">🔗 {{ $_igBaslik }}</button>
               @endif
               @if(!empty($isletme->web_linki))
               <input type="hidden" id="wam_web_link" value="{{ $isletme->web_linki }}">
               <button type="button" class="wam-link-chip web" data-link="#wam_web_link" data-emoji="🔗" data-etiket="{{ $_webBaslik }}">🔗 {{ $_webBaslik }}</button>
               @endif
            </div>
            @endif

         </div>

         <div class="wam-footer">
            <button type="button" class="btn wam-btn-cancel" data-dismiss="modal">Vazgeç</button>
            <button type="button" class="btn wam-btn-send" id="wam_gonder_btn">
               <i class="fa fa-whatsapp"></i> WhatsApp'tan Gönder
            </button>
         </div>
      </div>
   </div>
</div>

<script>
// Handler'lar burada (custom.js'e bagimli degil). Idempotent: birden fazla include olsa da bir kez baglanir.
// Bu partial jQuery yuklenmeden ONCE render olabilir (layout ortasi). Head'deki eski jQuery 2.1.3'te
// Bootstrap .modal() YOK; modal eklentisi footer'daki core.js (jQuery 3.2.1 + Bootstrap) ile geliyor.
// Bu yuzden sadece window.jQuery degil, .fn.modal hazir olana kadar bekle ki dogru jQuery'yi yakalayalim.
(function waMsgInit(){
   if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.modal !== 'function') { return setTimeout(waMsgInit, 50); }
   if (window.__waMsgInit) { return; }
   window.__waMsgInit = true;
   var $ = window.jQuery;

   // Butona tiklayinca: bagli degilse uyari, bagliysa mesaj ekrani
   $(document).on('click', '.whatsapp-mesaj-ac', function(e){
      e.preventDefault();
      var ad     = $(this).data('ad') || '';
      var telefon= $(this).data('telefon') || '';
      var userid = $(this).data('userid') || '';
      var onay   = parseInt($(this).data('onay') || 0, 10);
      var bagli  = parseInt($(this).data('bagli') || 0, 10);

      // Takvim randevu detay modali aciksa once onu kapat (z-index cakismasi)
      $('#modal-view-event').modal('hide');

      if(bagli !== 1){
         swal({
            type: "warning",
            title: "WhatsApp Bağlı Değil",
            text: "Müşterilerinize WhatsApp mesajı gönderebilmek için önce WhatsApp hesabınızı sisteme bağlamanız gerekiyor. WhatsApp Ayarları sayfasından QR kodu okutarak birkaç saniyede bağlayabilirsiniz.",
            showCancelButton: true,
            confirmButtonText: "WhatsApp Ayarlarına Git",
            cancelButtonText: "Daha Sonra",
            confirmButtonColor: '#25D366',
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-secondary'
         }).then(function(result){
            if(result && result.value){
               var _sube = $('#wam_sube').val();
               window.location.href = '/isletmeyonetim/whatsapp' + (_sube ? ('?sube=' + _sube) : '');
            }
         });
         return;
      }

      $('#wam_user_id').val(userid);
      $('#wam_musteri_ad').text(ad || '-');
      $('#wam_musteri_tel').text(telefon || '-');
      $('#wam_mesaj').val('');
      $('#wam_sayac').text('0');
      $('#wam_gonder_btn').prop('disabled', false);
      $('#wam_onay_uyari').css('display', onay === 1 ? 'none' : 'flex');

      // appendTo body — parent container centering'i bozmasin; setTimeout: detay modali kapanma animasyonu bitsin
      setTimeout(function(){
         $('#whatsapp-mesaj-modal').appendTo('body').modal('show');
      }, 200);
   });

   // Karakter sayaci
   $(document).on('input', '#wam_mesaj', function(){
      $('#wam_sayac').text($(this).val().length);
   });

   // Hazir baglanti ekle (Konum / Instagram / Web) — chip'ten emoji, etiket ve link okunur
   $(document).on('click', '.wam-link-chip', function(){
      var link = $($(this).data('link')).val();
      if(!link){ return; }
      var emoji  = $(this).data('emoji')  || '🔗';
      var etiket = $(this).data('etiket') || 'Bağlantı';
      var ta = $('#wam_mesaj');
      var cur = ta.val();
      // Ayni link zaten eklenmisse tekrar ekleme
      if(cur.indexOf(link) !== -1){ return; }
      var prefix = (cur && cur.slice(-1) !== '\n') ? '\n' : '';
      var next = cur + prefix + emoji + ' ' + etiket + ': ' + link;
      if(next.length > 1000){ next = next.slice(0, 1000); }
      ta.val(next);
      $('#wam_sayac').text(ta.val().length);
      ta.focus();
   });

   // Gonder
   $(document).on('click', '#wam_gonder_btn', function(e){
      e.preventDefault();
      var mesaj = $.trim($('#wam_mesaj').val());
      if(mesaj === ''){
         swal({type:"warning", title:"Mesaj boş", text:"Lütfen göndermek istediğiniz mesajı yazın."});
         return;
      }
      var btn = $(this);
      $.ajax({
         type: "POST",
         url: '/isletmeyonetim/musterimanuelwhatsapp',
         dataType: "json",
         data: {
            user_id: $('#wam_user_id').val(),
            mesaj: mesaj,
            sube: $('#wam_sube').val(),
            _token: $('#wam_token').val()
         },
         beforeSend: function(){ btn.prop('disabled', true); $("#preloader").show(); },
         success: function(res){
            $("#preloader").hide();
            if(res && res.ok){
               $('#whatsapp-mesaj-modal').modal('hide');
               swal({type:"success", title:"Gönderildi", text: res.mesaj || "Mesaj gönderim kuyruğuna alındı.", showConfirmButton:false, timer:2500});
            } else {
               btn.prop('disabled', false);
               swal({type:"error", title:"Gönderilemedi", text: (res && res.mesaj) ? res.mesaj : "Mesaj gönderilemedi."});
            }
         },
         error: function(request){
            $("#preloader").hide();
            btn.prop('disabled', false);
            var msg = "Mesaj gönderilemedi. Lütfen tekrar deneyin.";
            try { var j = JSON.parse(request.responseText); if(j && j.mesaj){ msg = j.mesaj; } } catch(err){}
            swal({type:"error", title:"Gönderilemedi", text: msg});
         }
      });
   });
})();
</script>
