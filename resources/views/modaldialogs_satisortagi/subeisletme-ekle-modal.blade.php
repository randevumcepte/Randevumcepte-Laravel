<div
            id="yeni_sube_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="sube_formu"  method="POST" autocomplete="off">
                     <div class="modal-header">
                        <h2>Yeni Şube</h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="alacak_id" value="">
                        <div class="row" data-value="0">
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>İşletme Adı</label>
                                 <input type="text" name="firma_adi" required class="form-control">
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>İşletme Telefon</label>
                                 <input type="tel" name="firma_telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control">
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label>İşletme Adresi</label>
                                 <textarea name="firma_adresi" required class="form-control"></textarea>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>İl</label>
                                 <select name="firma_il" id="firma_il" class="form-control opsiyonelSelect" style="width:100%">
                                    <option></option>
                                    @foreach(\App\Iller::all() as $il)
                                       <option value="{{$il->id}}">{{$il->il_adi}}</option>
                                    @endforeach    
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>İlçe</label>
                                 <select name="firma_ilce" id="firma_ilce" class="form-control opsiyonelSelect"  style="width:100%">
                                    <option></option>
                                  
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="form-group">
                                <label>Şubem için yönetici eklemek istiyorum</label><br>
                                          <label class="switch" >
                                             <input type="checkbox" name="yonetici_ekle" id="yonetici_ekle">
                                            <span class="slider"></span>
                                           </label>
                              </div>
                           </div>
                        </div>
                        <div class="row" id="sube_yonetici_ekle" style="display:none">
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>Yetkili Adı Soyadı</label>
                                 <input type="text" name="yetkili_adi" id="sube_yetkili_adi"  class="form-control">
                              </div>
                           </div>
                           
                           <div class="col-md-6">
                              <div class="form-group">
                                 <label>Telefon Numarası</label>
                                 <input type="tel" name="telefon" id="sube_yetkili_telefon" data-inputmask =" 'mask' : '5999999999'" class="form-control">
                              </div>
                           </div>
                            
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row">
                           <div class="col-md-6">
                              <button type="submit" disabled class="btn btn-success btn-lg btn-block"> <i class="icon-copy dw dw-add"></i>
                              Şubeyi Oluştur </button>
                           </div>
                           <div class="col-md-6">
                              <button  
                                 type="button"
                                 class="btn btn-danger btn-lg btn-block"
                                 data-dismiss="modal"
                                 > <i class="fa fa-times"></i>
                              Kapat
                              </button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>