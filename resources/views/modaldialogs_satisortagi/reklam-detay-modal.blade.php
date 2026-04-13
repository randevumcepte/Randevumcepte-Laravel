<div
            id="kampanya_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content"style="max-height: 90%; max-width: 100%;">
                  <div class="modal-body">
                     <h2 class="text-blue h2 mb-10">Kampanya Detayı</h2>
                     <div class="tab">
                        <div class="col-xs-12 col-12 col-sm-12 elementetkinlikkampanya">
                           <table class="table stripe hover nowrap">
                              <thead>
                                 <th>Paket Adı: <span id="paket_adi" style="font-weight: normal;"></span></th>
                                 <th>Seans : <span id="kampanya_seans" style="font-weight: normal;"></span></th>
                                 <th>Katılımcı Sayısı: <span id="kampanya_katilimci" style="font-weight: normal;"></span></th>
                                 <th>Hizmet: <span id="kampanya_hizmeti" style="font-weight: normal;"></span> </th>
                                 <th>Toplam Tutar: <span id="kampanya_toplam_tutar" style="font-weight: normal;"></span></th>
                              </thead>
                           </table>
                        </div>
                        <hr>
                        <ul class="nav nav-tabs elementbutton" role="tablist">
                           <li class="nav-item">
                              <a
                                 class="nav-link active text-blue"
                                 data-toggle="tab"
                                 href="#tum_kampanya"
                                 role="tab"
                                 aria-selected="true"
                                 >Tümü</a
                                 >
                           </li>
                           <li class="nav-item">
                              <a
                                 class="nav-link text-blue"
                                 data-toggle="tab"
                                 href="#kampanya_katilanlar"
                                 role="tab"
                                 aria-selected="false"
                                 >Katılanlar</a
                                 >
                           </li>
                           <li class="nav-item">
                              <a
                                 class="nav-link text-blue"
                                 data-toggle="tab"
                                 href="#kampanya_katilmayanlar"
                                 role="tab"
                                 aria-selected="false"
                                 >Katılmayanlar</a
                                 >
                           </li>
                           <li class="nav-item">
                              <a
                                 class="nav-link text-blue"
                                 data-toggle="tab"
                                 href="#kampanya_beklenen"
                                 role="tab"
                                 aria-selected="false"
                                 >Beklenenler</a
                                 >
                           </li>
                        </ul>
                        <div class="tab-content">
                           <div
                              class="tab-pane fade show active"
                              id="tum_kampanya"
                              role="tabpanel"
                              >
                              <div class="pd-20">
                                 <form id="tum_katilimcilar"  method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="table" id="kampanya_tablo_tum_katilimci">
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
                           <div class="tab-pane fade" id="kampanya_katilanlar" role="tabpanel">
                              <div class="pd-20">
                                 <form id="kampanya_katilan" method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="table" id="kampanya_tablo_katilanlar_katilimci">
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
                           <div class="tab-pane fade" id="kampanya_katilmayanlar" role="tabpanel">
                              <div class="pd-20">
                                 <form id="kampanya_katilmayan" method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="table" id="kampanya_tablo_katilmayanlar_katilimci">
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
                           <div class="tab-pane fade" id="kampanya_beklenen" role="tabpanel">
                              <div class="pd-20">
                                 <form id="kampanya_beklenenler" method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-sm-12"  style="overflow-y: auto; max-height: 300px ">
                                          <div class="form-group">
                                             <table class="table" id="kampanya_tablo_beklenen_katilimci">
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
                                       <button disabled class="btn btn-success btn-block" id="kampanyabeklenenleresmsgonder2"><i class="icon-copy fi-mail"></i> SMS GÖNDER</button>
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