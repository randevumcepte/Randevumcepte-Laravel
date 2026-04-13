 <div id="musteri-bilgi-modal" class="modal modal-top fade calendar-modal">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style=" max-height: 90%;">
               <form class="musteri_bilgi_formu" method="POST" id="yeniMusteriEklemeFormu">
                  {{ csrf_field() }}
                 
                  <input type="hidden" name="musteri_id">
                 
                  <input type="hidden" name="sube" value="{{$isletme->id}}">
                  <input type="hidden" name='eklendi_yanit_goster' id="eklendi_yanit_goster" >
                  <div class="modal-header">
                     <h2>Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif Ekle</h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        <div class="col-md-6">
                           <label>Ad Soyad</label>
                          
                           <input type="text" name="ad_soyad" required class="form-control" value="">
                          
                        </div>
                        <div class="col-md-6 col-xs-6 col-sm-6 col-6">
                           <label>Telefon  </label>
                          
                           <input type="tel" name="telefon" data-inputmask =" 'mask' : '5999999999'" required class="form-control" value="">
                          
                        </div>
                        <div class="col-md-6 col-xs-6 col-sm-6 col-6">
                           <label>TC Kimlik No</label>
                         
                           <input type="tel" name="tc_kimlik_no"  data-inputmask =" 'mask' : '99999999999'"  class="form-control" value="">
                           
                        </div>
                        <div class="col-md-6 ">
                           <label style="width:100%">Doğum Tarihi</label>
                         
                           <select class="form-control opsiyonelSelectGun" name="dogum_tarihi_gun">
                              <option></option>
                              <option value="01">01</option>
                              <option value="02">02</option>
                              <option value="03">03</option>
                              <option value="04">04</option>
                              <option value="05">05</option>
                              <option value="06">06</option>
                              <option value="07">07</option>
                              <option value="08">08</option>
                              <option value="09">09</option>
                              <option value="10">10</option>
                              <option value="11">11</option>
                              <option value="12">12</option>
                              <option value="13">13</option>
                              <option value="14">14</option>
                              <option value="15">15</option>
                              <option value="16">16</option>
                              <option value="17">17</option>
                              <option value="18">18</option>
                              <option value="19">19</option>
                              <option value="20">20</option>
                              <option value="21">21</option>
                              <option value="22">22</option>
                              <option value="23">23</option>
                              <option value="24">24</option>
                              <option value="25">25</option>
                              <option value="26">26</option>
                              <option value="27">27</option>
                              <option value="28">28</option>
                              <option value="29">29</option>
                              <option value="30">30</option>
                              <option value="31">31</option>
                           </select>
                           <select class="form-control opsiyonelSelectAy" name="dogum_tarihi_ay">
                              <option></option>
                              <option value="01">Ocak</option>
                              <option value="02">Şubat</option>
                              <option value="03">Mart</option>
                              <option value="04">Nisan</option>
                              <option value="05">Mayıs</option>
                              <option value="06">Haziran</option>
                              <option value="07">Temmuz</option>
                              <option value="08">Ağustos</option>
                              <option value="09">Eylül</option>
                              <option value="10">Ekim</option>
                              <option value="11">Kasım</option>
                              <option value="12">Aralık</option>
                           </select>
                           <select class="form-control opsiyonelSelectYil" name="dogum_tarihi_yil">
                              <option></option>
                              @for($i=1900;$i<=date('Y');$i++)
                              <option value="{{$i}}">{{$i}}</option>
                              @endfor
                           </select>
                          
                        </div>
                        <div class="col-md-6">
                           <label>E-posta </label>
                          
                           <input type="email" name="email" class="form-control" value="">
                          
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6">
                           <label>Cinsiyet</label>
                           <select class="form-control" name="cinsiyet">
                             
                              <option selected value="">Belirtilmemiş</option>
                              <option value="0">Kadın</option>
                              <option value="1">Erkek</option>
                             
                           </select>
                        </div>
                        <div class="col-md-3 col-xs-6 col-sm-6 col-6">
                           <label>Referans </label>
                           <select class="form-control" name="musteri_referans">
                            
                             
                              <option value='' selected>Yok</option>
                              <option  value="1">İnternet</option>
                              <option  value="2">Reklam</option>
                              <option value="3">Instagram</option>
                              <option  value="4">Facebook</option>
                              <option value="5">Tanıdık</option>
                              
                           </select>
                        </div>
                        <div class="col-md-12">
                           <label>Notlar</label>
                           <textarea class="form-control" name="ozel_notlar" ></textarea>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block;">
                     <div class="row">
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6">
                           <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" id='musteri_ekle_modal_kapat' data-dismiss="modal">Kapat</button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>