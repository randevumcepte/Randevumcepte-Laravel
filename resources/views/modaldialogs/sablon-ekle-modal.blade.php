<div
   id="sablon_olustur_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="height: 90%; width: 100%;">
         <form id="sablon_formu"  method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <input type="hidden" name="sablon_id">
            <div class="modal-header">
               <h2>Yeni Şablon</h2>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12">
                     <input
                        class="form-control form-group" id="sablon_adi" name='sablon_adi'
                        placeholder="Başlık"
                       
                        type="text"
                        />
                  </div>
                  <div class="col-md-12">
                     <textarea style="height: 230px" onchange="countChar(this,event)" onkeyup="countChar(this,event)" onkeydown="countChar(this,event)" class="form-control form-group" name="sablonsmsmesaj" id="sablonsmsmesaj" placeholder="Mesaj İçeriği"></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-md-6">
                     <button type="button" id="smstaslakolarakkaydet"  class="btn btn-success btn-lg btn-block">Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button 
                        type="button"
                        id="sablonkapatmodal"
                        class="btn btn-danger btn-lg btn-block modal_kapat"
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
<div
   id="sablon_duzenle_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="height: 90%; width: 100%;">
         <form id="sablon_formu_duzenleme"  method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <input type="hidden" name="sablon_id" id='sablonId'>
            <div class="modal-header">
               <h2>Şablon Düzenle</h2>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12">
                     <input
                        class="form-control form-group" id="sablon_adi_duzenleme" name='sablon_adi'
                        placeholder="Başlık"
                      
                        type="text"
                        />
                  </div>
                  <div class="col-md-12">
                     <textarea style="height: 230px" onchange="countChar(this,event)" onkeyup="countChar(this,event)" onkeydown="countChar(this,event)" class="form-control form-group" name="sablonsmsmesaj" id="sablonsmsmesaj_duzenleme" placeholder="Mesaj İçeriği"></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer" style="display:block">
               <div class="row">
                  <div class="col-md-6">
                     <button type="button" id='smsSablonGuncelle'  class="btn btn-success btn-lg btn-block">Kaydet</button>
                  </div>
                  <div class="col-md-6">
                     <button 
                        type="button"
                        id="sablonkapatmodal2"
                        class="btn btn-danger btn-lg btn-block modal_kapat"
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