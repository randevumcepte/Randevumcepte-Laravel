<style>
#whatsapp-mesaj-modal .modal-dialog { max-width:520px; }
.wam-modal { border-radius:12px; border:0; overflow:hidden; box-shadow:0 20px 50px rgba(92,0,142,.18); }
.wam-header {
   display:flex; align-items:center; gap:10px;
   padding:12px 18px;
   background:#faf5ff;
   border-bottom:1px solid #ede1f7;
   position:relative;
}
.wam-header .wam-icon {
   width:34px; height:34px; border-radius:9px;
   background:#25D366; color:#fff;
   display:inline-flex; align-items:center; justify-content:center;
   font-size:17px; flex-shrink:0;
}
.wam-header h2 { margin:0; font-size:15px; color:#3a1a52; font-weight:700; }
.wam-header p { margin:1px 0 0; font-size:11.5px; color:#7c6c8a; }
.wam-close {
   position:absolute; top:8px; right:10px;
   background:transparent; border:0; font-size:20px; line-height:1;
   color:#9d8ba8; cursor:pointer; transition:color .15s, background .15s;
   width:26px; height:26px; border-radius:6px;
}
.wam-close:hover { color:#ef4444; background:#fdecec; }

.wam-body { padding:12px 18px 4px; max-height:62vh; overflow-y:auto; background:#fff; }

.wam-musteri {
   display:flex; align-items:center; gap:10px;
   padding:10px 12px; margin-bottom:10px;
   background:#fbfafd; border:1px solid #ece6f3; border-radius:9px;
}
.wam-musteri .wam-av {
   width:36px; height:36px; border-radius:50%;
   background:#5C008E; color:#fff; flex-shrink:0;
   display:inline-flex; align-items:center; justify-content:center; font-size:15px;
}
.wam-musteri .wam-ad { font-size:13px; font-weight:700; color:#3a2e57; }
.wam-musteri .wam-tel { font-size:12px; color:#7c6c8a; margin-top:1px; }

.wam-uyari {
   display:none;
   align-items:flex-start; gap:8px;
   padding:9px 11px; margin-bottom:10px;
   background:#fef2f2; border:1px solid #fecaca; border-radius:8px;
   color:#b91c1c; font-size:11.5px; line-height:1.4;
}
.wam-uyari i { margin-top:1px; }

.wam-section__title {
   font-size:10.5px; font-weight:700;
   color:#5C008E;
   text-transform:uppercase; letter-spacing:.4px;
   margin-bottom:6px; display:flex; align-items:center; gap:5px;
}
.wam-body textarea.form-control {
   border-radius:7px; border:1px solid #dfd6ea; min-height:120px;
   font-size:13px; padding:8px 10px; resize:vertical;
}
.wam-body textarea.form-control:focus {
   border-color:#5C008E; box-shadow:0 0 0 3px rgba(92,0,142,.1);
}
.wam-sayac { display:block; text-align:right; color:#9d8ba8; font-size:10.5px; margin-top:3px; }

.wam-footer {
   display:flex; justify-content:flex-end; gap:8px;
   padding:10px 18px; border-top:1px solid #ece6f3;
   background:#fbfafd;
}
.wam-btn-send {
   background:#25D366; color:#fff !important;
   padding:7px 18px; border-radius:8px; font-weight:700; font-size:13px;
   border:0; box-shadow:0 4px 10px rgba(37,211,102,.25);
   transition:background .15s;
}
.wam-btn-send:hover { background:#1da851; }
.wam-btn-send:disabled { opacity:.6; cursor:not-allowed; }
.wam-btn-cancel {
   background:#fff; color:#7c6c8a !important;
   padding:7px 16px; border-radius:8px; font-weight:600; font-size:13px;
   border:1px solid #dfd6ea;
}
.wam-btn-cancel:hover { background:#f5f0fa; color:#3a2e57 !important; }

@media (max-width:600px) {
   #whatsapp-mesaj-modal .modal-dialog { max-width:96%; margin:10px auto; }
   .wam-body { padding:10px 12px; }
   .wam-header { padding:10px 14px; gap:8px; }
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
               <h2>WhatsApp Mesaj Gönder</h2>
               <p>Müşterinize doğrudan WhatsApp üzerinden mesaj yazın.</p>
            </div>
            <button type="button" class="wam-close" data-dismiss="modal" aria-label="Kapat">&times;</button>
         </div>

         <div class="modal-body wam-body">

            <div class="wam-musteri">
               <div class="wam-av"><i class="fa fa-user"></i></div>
               <div>
                  <div class="wam-ad" id="wam_musteri_ad">—</div>
                  <div class="wam-tel" id="wam_musteri_tel">—</div>
               </div>
            </div>

            <div class="wam-uyari" id="wam_onay_uyari">
               <i class="fa fa-exclamation-triangle"></i>
               <span>Bu müşteri WhatsApp bilgilendirme onayı vermemiş. Mesaj gönderebilirsiniz ancak istenmeyen mesaj şikâyeti ve hesap engellenme riski daha yüksektir. Yalnızca gerçekten gerekliyse gönderin.</span>
            </div>

            <div class="wam-section__title"><i class="fa fa-comment"></i> Mesajınız</div>
            <textarea id="wam_mesaj" class="form-control" maxlength="1000" placeholder="Müşterinize göndermek istediğiniz mesajı buraya yazın..."></textarea>
            <small class="wam-sayac"><span id="wam_sayac">0</span> / 1000</small>

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
