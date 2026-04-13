 <div
         id="paket_satisi_modal_senet"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content" >
               <form id="paket_satisi_senet"  method="POST">
                  <div class="modal-header">
                     <h2>Yeni Paket Satışı</h2>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <div class="paketler_bolumu_senet">
                        <div class="row" data-value="0">
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Paket Adı</label>
                                 <select name="paketadisenet[]" class="form-control custom-select2" style="width: 100%;">
                                    @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                    <option value="{{$paket->id}}">{{$paket->paket_adi}}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Fiyat (₺)</label>
                                 <input type="tel" name="paketfiyatsenet[]" value="{{\App\PaketHizmetler::where('paket_id',\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->value('id'))->sum('fiyat')}}"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Seans Başlangıç Tarihi</label>
                                 <input name="paketbaslangictarihisenet[]" id="" required class="form-control" autocomplete="off">
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="form-group">
                                 <label>Seans Aralığı (gün)</label>
                                 <input type="tel" name="seansaralikgunsenet[]"  class="form-control" required>
                              </div>
                           </div>
                           <div class="col-md-1">
                              <div class="form-group">
                                 <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                 <button type="button" name="paket_formdan_sil_senet" disabled  data-value="0" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-secondary btn-lg btn-block" id="bir_paket_daha_ekle_senet">
                              Bir Paket Daha Ekle
                              </button>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#paket-modal">Sisteme Yeni Paket Ekle</button>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label>Satıcı</label>
                              <select name="paket_satici_senet" class="form-control custom-select2 personel_secimi" style="width: 100%;">
                                  
                              </select>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display: block;">
                     <div class="row">
                        <div class="col-6 col-sm-6 col-xs-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">Kaydet</button>
                        </div>
                        <div class="col-6 col-sm-6 col-xs-6">
                           <button id='senet_paket_modal_kapat'
                              class="btn btn-danger btn-lg btn-block"
                              data-dismiss="modal"
                              >
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>