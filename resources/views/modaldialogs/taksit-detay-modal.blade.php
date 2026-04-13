
<button id='taksit_detay_modal_ac' data-toggle="modal" data-target="#taksit_detay_modal" style="display: none;"></button>
<div
   id="taksit_detay_modal"
   class="modal modal-top fade calendar-modal"
   >
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
      <div class="modal-content" style="width:100%">
          
            {{csrf_field()}}
            <input type="hidden" id="taksitli_tahsilat_id" name='taksitlitahsilatid'>
            <div class="modal-header">
               <h2>Taksit Vadeleri</h2>
            </div>
            <div class="modal-body">
               
                
               <div  id="taksit_vade_listesi">
                        
               </div>
            </div>
         
          
            <div class="modal-footer">
                  
                  <button id="taksit_modal_kapat"
                     type="button"
                     class="btn btn-danger"
                     data-dismiss="modal"
                   >
                  Kapat
                  </button>
            </div>
         
          
      </div>
   </div>
</div>