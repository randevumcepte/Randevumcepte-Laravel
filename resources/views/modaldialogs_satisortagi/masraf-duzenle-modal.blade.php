   <div
            class="modal fade bs-example-modal-lg"
            id="masraf_duzenle_modal"
            tabindex="-1"
            role="dialog"
            aria-labelledby="myLargeModalLabel"
            aria-hidden="true" style="z-index: 99999999999;"
            >
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="width:100%">
                  <div class="modal-header">
                     <h2 class="modal-title"  >
                        Masraf Kategorileri
                     </h2>
                     <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body">
                     {!!csrf_field()!!}
                     <div class="row" data-value="0">
                        <table class="data-table table stripe hover nowrap" id="masraflar_liste">
                           @foreach(\App\MasrafKategorisi::all() as $cat)
                           <tr>
                              <td> {{$cat->kategori}}</td>
                           </tr>
                           @endforeach
                        </table>
                     </div>
                     <div class="modal-footer" style="display:block">
                        <div class="row" data-value="0">
                           <div class="col-md-12">
                              <button type="submit"  data-toggle="modal" class="btn btn-success btn-lg btn-block" data-target="#masraf_ekle_modal"> <i class="icon-copy dw dw-add"></i>
                              Kategori Ekle </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>