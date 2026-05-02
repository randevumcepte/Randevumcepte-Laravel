<button style="display:none;" id="randevudetayigetir" data-toggle="modal" data-target="#modal-view-event"></button>

<style>
   #modal-view-event .modal-dialog { max-width: 720px; }
   #modal-view-event .modal-content {
      border:0; border-radius:14px; overflow:hidden;
      box-shadow: 0 24px 60px rgba(92,0,142,.18);
   }
   #modal-view-event .modal-header {
      background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
      color: #fff; padding: 14px 56px 12px 20px; border-bottom: 0;
      display: block; position: relative;
   }
   #modal-view-event .modal-header .h4 {
      margin: 0 0 8px; font-size: 15.5px; font-weight: 700;
      color: #fff !important; line-height: 1.35;
      word-break: break-word; display: block;
   }
   #modal-view-event .event-title { color:#fff; }
   #modal-view-event .modal-header .close {
      position: absolute; top: 10px; right: 10px;
      color: #fff; opacity: .9; font-size: 22px; line-height: 1;
      background: rgba(255,255,255,.12); border: 0;
      width: 30px; height: 30px; border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      transition: background .15s, opacity .15s;
      text-shadow: none; z-index: 5;
   }
   #modal-view-event .modal-header .close:hover { background: rgba(255,255,255,.28); opacity: 1; }
   #modal-view-event #duzenle_butonu_bolumu {
      display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin: 0;
   }
   #modal-view-event #duzenle_butonu_bolumu:empty { display: none; }
   #modal-view-event #duzenle_butonu_bolumu .btn {
      border-radius: 8px; font-weight: 600; padding: 6px 12px; font-size: 12.5px; line-height: 1.2;
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
