<div id="yeni_not_ekle_santral" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
           <div  class="modal-content" style="max-height: 90%;">
             <form id="yeni_not_ekle_form" method="GET">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="not_id" id="not_id" value="0">
               <div class="modal-content" style="min-height: 350px;">
                  <div class="modal-header">
                     <h4 class="h4"> Tekrar Arama Notu Ekle</h4>
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
       
                           <div class="col-md-6  col-sm-6 col-6 col-xs-6">
                           <label>Tarih</label>
                           <input type="text" name="santralnottarih" id="santralnottarih" autocomplete="off" class="form-control date-picker" placeholder="Tarih">
                        </div>
                        <div class="col-md-6  col-sm-6 col-6 col-xs-6">
                           <label>Saat</label>
                           <input type="time" id='santralnotsaat' class="form-control" value="00:00" name="santralnotsaat"  >
                        </div>

                    
                        <div  class="col-md-12 col-sm-12 col-xs-12 col-12">
                           <label>İçerik</label>
                           <textarea type="text" name="noticerik" id="noticerik"  placeholder="İçerik" class="form-control"></textarea> 
                        </div>
                       
                     </div>
                  </div>
                  <div class="modal-footer" style="justify-content: center;">
                     <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                        <button type="submit" class="btn btn-success btn-block"> Kaydet</button>
                     </div>
                  </div>
               </div>
            </form>
           </div>
         </div>
      </div>