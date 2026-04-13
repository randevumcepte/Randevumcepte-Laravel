 <div
         id="satisKalemleri"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered" >
            <div class="modal-content" style="max-width:1100px; max-height: 90%;width:100%">
               <form id="satis_listesi">
                  <input type="hidden" name="adisyon_id">
                  <div class="modal-body">
                     
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     
                      <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-6">
                            <h2 class="text-blue h2 mb-10">Satış Detayları</h2>
                        </div>
                        <div class="col-2">
                           <button type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Hizmet Ekle</button>
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                           <button type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value=''onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn  btn-danger  btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Ürün Ekle</button>
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                           <button type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="btn  btn-primary  btn-block adisyon_ekle_buttonlar" style="font-size:12px">Paket Ekle</button>
                        </div>
                      
                     </div>
                    
                      <div class="row" style="margin:5px 0 5px 0;padding:5px;font-size: 12px;">
                         <div class="col-md-4 col-5 col-xs-5 col-sm-4">
                            Hizmet/Ürün/Paket
                         </div>
                         <div class="col-md-3  col-7 col-xs-7  col-sm-3">
                           Satıcı
                         </div>
                         <div class="col-md-2 col-5 col-xs-5  col-sm-2">
                            Miktar/Seans Sayısı
                         </div>
                         <div class="col-md-2 col-7 col-xs-7  col-sm-2" style="text-align:right">
                            Tutar (₺)
                         </div>
                      </div>
                      <div id='tum_tahsilatlar'>
                         
                      </div>
                    
                  </div>
                  <div class="modal-footer">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block">
                           Kaydet
                           </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button 
                              type="button"
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
