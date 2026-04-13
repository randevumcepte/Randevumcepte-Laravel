<div id="formugondermodal" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dailog-centered" style="max-width: 750px">
            <form id="arsivformekleme">
               {{ csrf_field() }}
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <input type="hidden" name="arsiv_id" id="arsiv_id" value="">
               <div class="modal-content" style="min-height: 320px;">
                  <div class="modal-header">
                     <h4 class="h4">Form Oluştur</h4>
                     <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body" style="padding:1rem 1rem 0rem 1rem;">
                     <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-6 col-6 form-group">
                           <label>Form/Sözleşme Türü</label>
                           <select name="formtaslaklari" id="formtaslaklari" class="form-control opsiyonelSelect" style="width: 100%;">
                              <option></option>
                              <option value="1">Kimyasal Peeling Onam Formu</option>
                              <option value="2">Dövme Silme Onam Formu</option>
                              <option value="3">Mikropigmentasyon Uygulaması Onam Formu</option>
                              <option value="4">Lazer Epilasyon Onam Formu</option>
                              <option value="5">Dermoroller Onam Formu</option>
                              <option value="6">Bölgesel İncelme Onam Formu</option>
                              <option value="7">Cilt Üzerinde Kullanılan Lazer Onam Formu</option>
                           </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6 col-6 form-group">
                           <label>Müşteri</label>
                           <select name="formmusterisec" id="formmusterisec" class="form-control opsiyonelSelect" style="width: 100%;">
                              <option></option>
                              {!!$portfoy_drop!!}
                           </select>
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6 form-group">
                           <label>Cep Telefon</label>
                           <input class="form-control" required type="tel" name="formmustericeptelefon" id="formmustericeptelefon">
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6 form-group">
                           <label>TC Kimlik No</label>
                           <input class="form-control" required type="tel" name="formmusterikimlikno" id="formmusterikimlikno">
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6 form-group">
                           <label>Cinsiyet</label>
                           <select name="formmustericinsiyet" id="formmustericinsiyet" class="form-control">
                              <option value="0">Kadın</option>
                              <option value="1">Erkek</option>
                           </select>
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6 form-group">
                           <label>Doğum Tarihi</label>
                           <input type="text" name="formmusteriyas" id='formmusteriyas' class="form-control"  value="" >
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                           <label>İşlemi Yapan Personel</label>
                           <select name="formpersonelsec" id="formpersonelsec" class="form-control opsiyonelSelect" style="width: 100%;">
                              <option></option>
                              @if(Auth::guard('satisortakligi')->user()->hasRole('Personel'))
                              <option selected value="{{Auth::guard('satisortakligi')->user()->personel_id}}">{{Auth::guard('satisortakligi')->user()->name}}</option>
                              @else
                              {!!$personel_drop!!}
                              @endif
                           </select>
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6 form-group">
                           <label>Personel Cep Telefon</label>
                           <input class="form-control" required type="tel" name="formmpersonelceptelefon" id="formpersonelceptelefon">
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="justify-content: center;">
                     <div class="col-md-6 col-xs-6 col-6 col-sm-6" >
                        <button type="submit" disabled class="btn btn-success btn-block "> Gönder</button>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>