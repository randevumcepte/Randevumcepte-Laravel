<div id="formutekrargondermodal" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
            <form id="formgonder">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="arsiv_id" id="arsiv_id" value="">
               <div class="modal-content" style="min-height: 230px;">
                  <div class="modal-header">
                     <h4 class="h4">Formu Tekrar Gönder</h4>
                     <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
                     <div class="row">
                        <div class="col-sm-12 col-md-12 col-12 col-xs-12" style="text-align: center;">
                           <p style="font-size: 18px;">Formu tekrardan göndermek için aşağıdakilerden birisini seçin.</p>
                        </div>
                        <br>
                        <br>
                        <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                           <button type="button" disabled name="musteriyeformutekrargonder"class="btn btn-success btn-block"> Müşteriye Gönder</button>
                        </div>
                        <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                           <button type="button" disabled name="personeleformutekrargonder" class="btn btn-primary btn-block"> Salona Gönder</button>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>