 <div
         id="satisKalemleri"
         class="modal modal-top fade calendar-modal"
         >
         <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:1200px;width:95%;">
            <div class="modal-content" style="max-width:1200px; width:100%">
               <form id="satis_listesi">
                  <input type="hidden" name="adisyon_id">
                  <input type="hidden" id="harici_indirim_tutari" value="0">
                  <input type="hidden" id="musteri_indirim" value="0">
                  <input type="hidden" style="font-size: 20px; background-color: #d4edda; border-color: #c3e6cb;" class="form-control try-currency"  name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
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
                     <div class="row" style="margin-bottom:15px;">
                        <div class="col-md-4 col-12">
                           <label style="font-weight:bold;font-size:13px;margin-bottom:5px;">Satış Tarihi</label>
                           <input type="text" name="satis_tarihi_duzenle" id="satis_tarihi_duzenle" class="form-control geriye-yonelik" autocomplete="off" value="">
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
                      <div id='tum_tahsilatlar_duzenleme'>
                         
                      </div>
                       <div id="odeme_kayit_bolumu">
                          
                           <div class="card-box pd-20 odemeozeti"  style="margin-bottom:20px">

                              <div class="row">
                                 <div class="col-12 col-xs-12 col-sm-6">
                                    <p><b style="width: 100%;">Toplam Tutar (₺)</b>&nbsp;&nbsp;
                                    <span id="adisyon_toplam_tutar" style="color:#ff0000;font-size:30px"></span></p>
                                    <p><b style="width: 100%;">Ödenen Tutar (₺)</b>&nbsp;&nbsp;
                                    <span id="adisyon_odenen_tutar" style="color:#ff0000;font-size:30px"></span></p>
                                    <p> <b style="width: 100%;">Kalan Tutar (₺)</b>&nbsp;&nbsp;
                                    <span id="tahsil_edilecek_kalan_tutar" style="color:#ff0000;font-size:30px">
                                    </span></p>
                                 </div>
                                 
                                 <div class="col-md-6 col-sm-6 col-12 col-xs-12">
                                    <table class="table">
                                       <thead id="tahsilat_durumu" style="display:none">
                                          <tr>
                                             <td colspan="4" style='border:none;font-weight: bold; font-size: 16px;'>Özet</td>
                                          </tr>
                                          <tr>
                                             <td colspan="3">Ara Toplam (₺)</td>
                                             <td id='ara_toplam' style="text-align:right;">  </td>
                                          </tr>
                                          <tr>
                                             <td colspan="3">Müşteri İndirimi (₺)</td>
                                             <td id='uygulanan_indirim_tutari' style="text-align:right;"> </td>
                                          </tr>
                                          <tr>
                                             <td colspan="3">Harici İndirim (₺)</td>
                                             <td id='uygulanan_harici_indirim_tutari' style="text-align:right;"> </td>
                                          </tr>
                                          <tr style="font-weight: bold; color: green;display: none;">
                                             <td colspan="3">
                                                Ödenen Tutar (₺): 
                                             </td>
                                             <td id="tahsil_edilen_tutar" style="text-align:right;">
                                                
                                             </td>
                                          </tr>
                                          <tr style="font-weight: bold; color: red;">
                                             <td colspan="3">
                                                Alacak Tutarı (₺): 
                                             </td>
                                             <td class="tahsil_edilecek_kalan_tutar" style="text-align:right;">
                                             </td>
                                          </tr>
                                       </thead>
                                       <tbody id="tahsilat_listesi_duzenleme">
                                          <tr>
                                             <td colspan="4" style='border:none;font-weight: bold; font-size: 16px;'>Geçmiş Ödemeler</td>
                                          </tr>
                                         
                                         
                                         
                                          <tr>
                                             <td> </td>
                                             <td> </td>
                                             <td>
                                               
                                             </td>
                                             <td>
                                                <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" name="tahsilat_adisyondan_sil" data-value="" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                                             </td>
                                          </tr>
                                          
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
                           <button type="submit" class="btn btn-success" style="width:100%;margin-top: 10px;display: none;">Değişiklikleri Kaydet</button>
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
