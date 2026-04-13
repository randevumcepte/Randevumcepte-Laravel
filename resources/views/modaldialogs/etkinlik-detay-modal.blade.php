 <div
            id="etkinlik_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%; max-width: 100%;">
                  <div class="modal-body">
                     <h2 class="text-blue h2 mb-10">Etkinlik Detayı</h2>
                     <div class="tab">
                        <div class="col-12 col-sm-12 col-xs-12 elementetkinlikkampanya" >
                           <table class="data-table table stripe hover nowrap" id="etkinlik_tablo">
                              <thead>
                                 <th>Tarih : <span id="etkinlik_tarih" style="font-weight: normal;"></span></th>
                                 <th>Etkinlik Adı: <span id="etkinlik_adi" style="font-weight: normal;"></span></th>
                                 <th>Katılımcı Sayısı: <span id="etkinlik_katilimci" style="font-weight: normal;"></span></th>
                                 <th>Toplam Tutar: <span id="toplam_tutar" style="font-weight: normal;"></span></th>
                              </thead>
                           </table>
                        </div>
                        <hr>
                        <ul class="nav nav-tabs elementbutton" role="tablist">
                           <li class="nav-item" style="margin-left: 20px">
                              <button
                                 class="btn btn-outline-primary"
                                 data-toggle="tab"
                                 href="#tum_etkinlik"
                                 role="tab"
                                 aria-selected="true"
                                 >Tümü</button
                                 >
                           </li>
                           <li class="nav-item" style="margin-left: 20px">
                              <button
                                 class="btn btn-outline-primary"
                                 data-toggle="tab"
                                 href="#etkinlik_katilanlar"
                                 role="tab"
                                 aria-selected="false"
                                 >Katılanlar</button
                                 >
                           </li>
                           <li class="nav-item" style="margin-left: 20px">
                              <button
                                 class="btn btn-outline-primary"
                                 data-toggle="tab"
                                 href="#etkinlik_katilmayanlar"
                                 role="tab"
                                 aria-selected="false"
                                 >Katılmayanlar</button
                                 >
                           </li>
                           <li class="nav-item" style="margin-left: 20px">
                              <button
                                 class="btn btn-outline-primary"
                                 data-toggle="tab"
                                 href="#etkinlik_beklenen"
                                 role="tab"
                                 aria-selected="false"
                                 >Beklenenler</button
                                 >
                           </li>
                        </ul>
                        <div class="tab-content">
                           <div
                              class="tab-pane fade show active"
                              id="tum_etkinlik"
                              role="tabpanel"
                              >
                              <div class="pd-20">
                                 <form id="tum_katilimcilar"  method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="data-table table stripe hover nowrap" id="etkinlik_tablo_tum_katilimci">
                                                <thead>
                                                   <tr>
                                                      <th>Ad Soyad</th>
                                                      <th>Telefon Numarası</th>
                                                      <th>Durum</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <div class="tab-pane fade" id="etkinlik_katilanlar" role="tabpanel">
                              <div class="pd-20">
                                 <form id="etkinlik_katilan" method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="data-table table stripe hover nowrap" id="etkinlik_tablo_katilanlar_katilimci">
                                                <thead>
                                                   <tr>
                                                      <th>Ad Soyad</th>
                                                      <th>Telefon Numarası</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <div class="tab-pane fade" id="etkinlik_katilmayanlar" role="tabpanel">
                              <div class="pd-20">
                                 <form id="etkinlik_katilmayan" method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="data-table table stripe hover nowrap" id="etkinlik_tablo_katilmayanlar_katilimci">
                                                <thead>
                                                   <tr>
                                                      <th>Ad Soyad</th>
                                                      <th>Telefon Numarası</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <div class="tab-pane fade" id="etkinlik_beklenen" role="tabpanel">
                              <div class="pd-20">
                                 <form id="etkinlik_beklenenler" method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="table" id="etkinlik_tablo_beklenen_katilimci">
                                                <thead>
                                                   <tr>
                                                      <th>Ad Soyad</th>
                                                      <th>Telefon Numarası</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                       <div class="col-md-12">
                                          <button id="etkinlikbeklenenleresmsgonder" class="btn btn-success btn-block"><i class="icon-copy fi-mail"></i> SMS GÖNDER</button>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block;">
                     <button
                        type="button"
                        class="btn btn-danger btn-lg btn-block"
                        data-dismiss="modal"
                        ><i class="fa fa-times"></i>
                     Kapat
                     </button>
                  </div>
               </div>
               </form>
            </div>
         </div>