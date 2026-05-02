<button style="display:none;" id="randevudetayigetir" data-toggle="modal" data-target="#modal-view-event"></button>

<style>
   #modal-view-event .modal-dialog { max-width: 720px; }
   #modal-view-event .modal-content {
      border:0; border-radius:14px; overflow:hidden;
      box-shadow: 0 24px 60px rgba(92,0,142,.18);
   }
   #modal-view-event .modal-header {
      background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
      color: #fff; padding: 16px 22px; border-bottom: 0;
      display:flex; align-items:center; gap:12px;
      flex-wrap: wrap; position: relative;
   }
   #modal-view-event .modal-header .h4 {
      margin: 0; font-size: 16px; font-weight: 700;
      flex: 1; min-width: 0;
      color: #fff !important; line-height: 1.3;
   }
   #modal-view-event .event-title { color:#fff; }
   #modal-view-event .modal-header .close {
      position: absolute; top: 10px; right: 12px;
      color: #fff; opacity: .9; font-size: 22px; line-height: 1;
      background: transparent; border: 0; padding: 4px 8px; border-radius: 6px;
      transition: background .15s, opacity .15s;
      text-shadow: none;
   }
   #modal-view-event .modal-header .close:hover { background: rgba(255,255,255,.18); opacity: 1; }
   #modal-view-event #duzenle_butonu_bolumu { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
   #modal-view-event #duzenle_butonu_bolumu .btn {
      border-radius: 8px; font-weight: 600; padding: 6px 14px; font-size: 12.5px;
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
