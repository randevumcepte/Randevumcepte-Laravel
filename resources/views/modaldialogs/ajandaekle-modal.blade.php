<div id="yeni_ajanda_ekle" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
            <form id="yeni_ajanda_ekle_form">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="ajanda_id" id="ajanda_id" value="0">
               <div class="modal-content" style="min-height: 350px;">
                  <div class="modal-header">
                     <h4 class="h4">Yeni Not Ekle</h4>
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
                        <div class="col-md-6 col-xs-6 col-6 col-sm-6">
                           <label>Başlık</label>
                           <input type="text"  placeholder="Başlık" class="form-control" name="ajandabaslik" id="ajandabaslik" >
                        </div>
                        <div class="col-md-3  col-sm-3 col-6 col-xs-6">
                           <label>Tarih</label>
                           <input type="text" name="ajandatarih" id="ajandatarih" autocomplete="off" class="form-control date-picker" placeholder="Tarih">
                        </div>
                        <div class="col-md-3  col-sm-3 col-6 col-xs-6">
                           <label>Saat</label>
                           <input type="time" id='ajandasaat' class="form-control" value="00:00" name="ajandasaat"  >
                        </div>
                        <div  class="col-md-6 col-sm-6 col-xs-6 col-6">
                           <label>İçerik</label>
                           <textarea type="text" name="ajandaicerik" id="ajandaicerik"  placeholder="İçerik" class="form-control"></textarea> 
                        </div>
                        <div class="col-md-2 col-xs-6 col-sm-2 col-6">
                           <label>Hatırlatma</label><br>
                           <label class="switch">
                           <input   type="checkbox"  id="ajandahatirlatma" name="ajandahatirlatma">
                           <span class="slider" style="border-radius: 5px;"></span>
                           </label> 
                        </div>
                        <div class="col-md-4 ccol-sm-4 col-xs-6 col-6">
                           <label>Hatırlatma ne zaman yapılsın?</label>
                           <select class="form-control" id="ajanda_hatirlatma_saat_once" name="ajanda_hatirlatma_saat_once" >
                           <option {{($isletme->ajanda_hatirlatma_saat==1)  ? 'selected' : ''}} value="1">1 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==2) ? 'selected' : ''}} value="2" selected="">2 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==3) ?'selected' : ''}} value="3">3 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==4) ?'selected' : ''}} value="4">4 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==5)  ? 'selected' : ''}} value="5">5 saat</option>
                           </select>
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