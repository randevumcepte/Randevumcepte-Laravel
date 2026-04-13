 <div id="ajanda_duzenle_modal" class="modal modal-top fade calendar-modal" >
         <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
            <form id="ajanda_duzenle_form">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="ajanda_id_duzenle" id="ajanda_id_duzenle" value="0">
               <div class="modal-content" style="min-height: 350px;">
                  <div class="modal-header">
                     <h4 class="h4">Notu Güncelle</h4>
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
                           <input type="text"  placeholder="Başlık" class="form-control" name="ajandabaslikduzenle" id="ajandabaslikduzenle" >
                        </div>
                        <div class="col-md-3 col-3 col-xs-3 col-sm-3">
                           <label>Tarih</label>
                           <input type="text" name="ajandatarihduzenle" id="ajandatarihduzenle" autocomplete="off" class="form-control date-picker" placeholder="Tarih">
                        </div>
                        <div class="col-md-3 col-3 col-xs-3 col-sm-3">
                           <label>Saat</label>
                           <input type="time" id='ajandasaatduzenle' class="form-control" value="00:00" name="ajandasaatduzenle"  >
                        </div>
                        <div  class="col-md-6 col-sm-6 col-xs-6 col-6">
                           <label>İçerik</label>
                           <textarea type="text" name="ajandaicerikduzenle" id="ajandaicerikduzenle"  placeholder="İçerik" class="form-control"></textarea> 
                        </div>
                        <div class="col-md-2 col-xs-2 col-sm-2 col-2">
                           <label>Hatırlatma</label><br>
                           <label class="switch">
                           <input   type="checkbox"  id="ajandahatirlatmaduzenle" name="ajandahatirlatmaduzenle">
                           <span class="slider" style="border-radius: 5px;"></span>
                           </label> 
                        </div>
                        <div class="col-md-4 ccol-sm-4 col-xs-4 col-4">
                           <label>Hatırlatma ne zaman yapılsın?</label>
                           <select class="form-control" id="ajanda_hatirlatma_saat_once_duzenle" name="ajanda_hatirlatma_saat_once_duzenle" >
                           <option {{($isletme->ajanda_hatirlatma_saat==1) ? 'selected' : ''}} value="1">1 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==2) ? 'selected' : ''}} value="2">2 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==3) ? 'selected' : ''}} value="3">3 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==4) ? 'selected' : ''}} value="4">4 saat</option>
                           <option {{($isletme->ajanda_hatirlatma_saat==5) ? 'selected' : ''}} value="5">5 saat</option>
                           </select>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="justify-content: center;">
                     <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                        <button type="submit" disabled class="btn btn-success btn-block"> Güncelle</button>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>