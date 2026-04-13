 

         <div
            id="yeni_masraf_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="masraf_formu"  method="POST">
                     <div class="modal-header">
                        <h2 class="modal_baslik"></h2>
                     </div>
                     <div class="modal-body">
                        {!!csrf_field()!!}
                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                        <input type="hidden" name="masraf_id" id='masraf_id' value="">
                        @if($pageindex==15)
                        <input type="hidden" name="masraf_sayfasi" value="1">
                        @endif
                        @if($pageindex==103)
                        <input type="hidden" id='kasa_sayfasi' name="kasa_sayfasi" value="1">
                        @endif
                        <div class="row" data-value="0">
                           <div class="col-md-6">
                              <label>Tarih</label>
                              <input type="text" required class="form-control" name="tarih" id='masraf_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                           </div>
                           <div class="col-md-6">
                              <label>Tutar (₺)</label>
                              <input type="tel" name="masraf_tutari" id='masraf_tutari' required class="form-control try-currency">
                           </div>
                           <div class="col-md-12">
                              <label>Açıklama</label>
                              <textarea name="masraf_aciklama" id='masraf_aciklama' class="form-control"></textarea>
                           </div>
                        </div>
                        <div class="row" data-value="0">
                           <div class="col-md-12">
                              <label>Masraf Kategorisi</label>
                              <select name="masraf_kategorisi" id='masraf_kategorisi' class="form-control  opsiyonelSelect" style="width: 100%;">
                                   <option></option>
                                 @foreach(\App\MasrafKategorisi::all() as $cat)
                                 <option value="{{$cat->id}}">{{$cat->kategori}}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="row" data-value="0">
                           <div class="col-md-6">
                              <label>Ödeme Yöntemi</label>
                              <select name="masraf_odeme_yontemi" id='masraf_odeme_yontemi' class="form-control  opsiyonelSelect" style="width: 100%;">
                                   <option></option>
                                 @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                                 <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label>Harcayan</label>
                              <select name="harcayan" id='harcayan' class="form-control  opsiyonelSelect" style="width: 100%;">
                                 <option></option>
                              {!!$personel_drop!!}
                              </select>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <label>Notlar</label>
                              <textarea name="masraf_notlari" id='masraf_notlari' class="form-control"></textarea>
                           </div>
                        </div>
                        <div class="modal-footer" style="display:block">
                           <div class="row" data-value="0">
                              <div class="col-md-6  col-sm-6 col-xs-6 col-6">
                                 <button type="submit" disabled class="btn btn-success btn-lg btn-block"> <i class="fa fa-save"></i>
                                 Kaydet </button>
                              </div>
                              <div class="col-md-6  col-sm-6 col-xs-6 col-6">
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
                     </div>
                  </form>
               </div>
            </div>
         </div>