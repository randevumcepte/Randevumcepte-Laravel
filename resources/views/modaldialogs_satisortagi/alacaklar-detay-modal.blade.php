 <div
            id="senet_taksit_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
               <div class="modal-content" style="width:100%">
                  <form id='senet_taksit_duzenleme_tahsilat' method="GET">
                     <div class="pd-20">
                        <div class="tab">
                           <ul class="nav nav-tabs" role="tablist">
                              <li class="nav-item">
                                 <a
                                    class="nav-link active text-blue"
                                    data-toggle="tab"
                                    href="#taksit-tahsilat"
                                    role="tab"
                                    aria-selected="true"
                                    >Taksitler</a
                                    >
                              </li>
                              <li class="nav-item">
                                 <a
                                    class="nav-link text-blue"
                                    data-toggle="tab"
                                    href="#senet-tahsilat"
                                    role="tab"
                                    aria-selected="false"
                                    >Senetler</a
                                    >
                              </li>
                           </ul>
                           <div class="tab-content">
                              <div
                                 class="tab-pane fade show active"
                                 id="taksit-tahsilat"
                                 role="tabpanel"
                                 >
                                 <div class="pd-10">
                                    <div  id="taksit_vade_listesi_tahsilat">
                                       @if($pageindex==1111)
                                       {!!$tum_taksitler!!}
                                       @endif
                                    </div>
                                 </div>
                              </div>
                              <div class="tab-pane fade" id="senet-tahsilat" role="tabpanel">
                                 <div  id="senet_vade_listesi_tahsilat">
                                    @if($pageindex==1111)
                                    {!!$tum_senetler!!}
                                    @endif
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-6 col-xs-6">
                              <button type="submit" disabled id='secili_alacaklari_tahsil_et' class="btn btn-success btn-lg btn-block">
                              <i class="fa fa-money"></i> Tahsilata Aktar
                              </button>
                           </div>
                           <div class="col-6 col-xs-6">
                              <button type="button"
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