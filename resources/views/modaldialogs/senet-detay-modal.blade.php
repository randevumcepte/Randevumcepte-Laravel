 <button id='senet_detay_modal_ac' data-toggle="modal" data-target="#senet_detay_modal" style="display: none;"></button>
         <div
            id="senet_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
               <div class="modal-content" style="width:100%">
                  <form method="POST" id="senet_adisyon" action="{{ URL::to('/isletmeyonetim/pdf') }}">
                     {{csrf_field()}}
                     <input type="hidden" id="senet_id" name='senetid'>
                     <div class="modal-header">
                        <h2>Senet Vadeleri</h2>
                     </div>
                     <div class="modal-body">
                        <div  id="senet_vade_listesi">
                        </div>
                     </div>
                     <div class="modal-footer">
                        <button style="display:none" id="senettahsilataktarma"  type="button" class="btn btn-success">Tahsilata Aktar</button>
                        <button type="submit" class="btn btn-primary">Yazdır</button>
                        <button id="modal_kapat"
                           type="button"
                           class="btn btn-danger"
                           data-dismiss="modal"
                           >
                        Kapat
                        </button>
                     </div>
                  </form>
               </div>
            </div>
         </div>