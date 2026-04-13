<div
   id="yeni_kampanya_modal"
   class="modal modal-top fade calendar-modal"
   >
   <div class="modal-dialog modal-dialog-centered" >
      <div class="modal-content" style="max-height: 90%" >
         <form id="kampanya_formu"  method="POST">
            <div class="modal-header">
               <h2 class="modal_baslik" id="kampanya_modal_baslik">Yeni Reklam Oluştur</h2>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"  >
               ×
               </button>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
               <input type="hidden" name="kampanya_id" value="">
               <input type="hidden" name="sube" value="{{$isletme->id}}">
               <div class="row" >
                  <div class="col-md-12" style="padding: 0 20px 20px 20px">
                     
                     <div class="row" style="display: none;">
                        <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                           <label></label>
                           <button id="kampanyaHizmetOlarakSec" type="button"  class="btn btn-success btn-block" style="font-size:11px;margin-bottom: 10px;">
                           Hizmet </button>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                           <label></label>
                           <button id="kampanyaUrunOlarakSec" type="button"  class="btn btn-info btn-block" style="font-size:11px;margin-bottom: 10px;">
                           Ürün </button>
                        </div>
                        <div class="col-12 col-xs-12 col-sm-12 col-md-2">
                           <label></label>
                           <button id="kampanyaPaketOlarakSec" type="button"  class="btn btn-primary btn-block" style="font-size:11px;margin-bottom: 10px;">
                           Paket</button>
                        </div>
                     </div>
                     <div class="row">
                          <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                             <label>Görev Türü</label>
                             <select id="gorevTuru" name="gorevTuru" class="form-control opsiyonelSelect" style="width: 100%;">
                               <option></option>
                              <option value="hizmet">Arama</option>
                              <option value="urun">SMS</option>
                              
                           </select>
                        
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                             <label>Kampanya Türü</label>
                             <select id="kampanyaTuru" name="kampanyaTuru" class="form-control opsiyonelSelect" style="width: 100%;">
                              <option></option>
                              <option value="hizmet">Hizmet</option>
                              <option value="urun">Ürün</option>
                              <option value="paket">Paket</option>
                              
                           </select>
                        
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6 col-md-2">
                           <label>Hizmet/Ürün/Paket</label>
                           <select id="hizmetUrunPaket" name="hizmetUrunPaket" class="form-control opsiyonelSelect" style="width: 100%;">
                              <option></option>
                              @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                              <option value="{{$paket->id}}">
                                 {{$paket->paket_adi}} (P)
                              </option>
                              @endforeach
                              @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $urun)
                              <option value="urun-{{$urun->id}}">
                                 {{$urun->urun_adi}} (Ü)
                              </option>
                              @endforeach
                              @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmet)
                              <option value="hizmet-{{$hizmet->hizmetler->id}}">
                                 {{$hizmet->hizmetler->hizmet_adi}} (H)
                              </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-6 col-xs-6 col-sm-6  col-md-3">
                           <label style="">Katılımcılar</label>
                           <select class="form-control" name="katilimciTuru">

                              <option value="all">Tüm Müşteriler</option>
                              <option value="men">Erkekler</option>
                              <option value="women">Kadınlar</option>
                             
                             
                           </select>
                        </div>
                  
                        <div class="col-6 col-xs-6 col-sm-6  col-md-3">
                        <label style="visibility:hidden;">Seçim</label>
                           <select class="form-control" name="gelmeyenMusteri">

                              <option value="15">15 gün gelmeyen</option>
                              <option value="30">30 gün gelmeyen</option>
                              <option value="45">45 gün gelmeyen</option>
                              <option value="60">60 gün gelmeyen</option>
                              <option value="90">90 gün gelmeyen</option>
                           </select>
                        </div>
                     </div>
                  </div>
                  <!-- <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                     <label>Fiyat (₺)</label>
                     <input type="tel" name="kampanyapaketfiyat" value=""  class="form-control" required>
                     </div>
                     <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                     <label>Hizmet</label>
                     <input type="text" name="kampanyapakethizmet"  value="" class="form-control" required>
                     </div>
                     <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                     <label>Seans</label>
                     <input type="tel"  name="kampanyapaketseans" value=""  class="form-control" required>
                     </div>-->
                  <div class="col-md-6">
                     <label>Şablon Seçiniz</label>
                     <!--<select class="form-control" id="kampanya_sablon_sec">
                        <option value="">Seçiniz</option>
                        @foreach(\App\SMSTaslaklari::where('salon_id',$isletme->id)->get() as $sablon)
                        <option value="{{$sablon->taslak_icerik}}">{{$sablon->baslik}}</option>
                        @endforeach
                        </select>-->
                     <div style="height:300;overflow-y: scroll;border: 1px solid #e2e2e2;border-radius: 10px;padding: 10px;">
                        @foreach(\App\KampanyaSablonlari::all() as $sablon)
                        <a class="kampanyaSablonSecim" title="Metni Seç" data-value="1" style="position:relative; cursor: pointer;" name="kampanyaSablonSecim">
                           <p style="border:1px solid grey;padding:5px;background-color: #e4e4e2; border-radius: 20px;border-bottom-left-radius: 0;color:black;font-size:15px; overflow: hidden;
                              ">
                              {{$sablon->icerik}}
                           </p>
                        </a>
                        @endforeach
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Kampanya Metni</label>
                        <div style="height: 300px;border: 1px solid #e2e2e2;border-radius: 10px;padding: 10px; overflow-y: scroll;" id="kampanyaPrompt" name="kampanyaPrompt"></div>
                     </div>
                  </div>
                  <div class="col-md-12" style="padding:10px 0 10px 10px">
                     <label style="font-size: 16px; "><b>Planlama</b></label>
                  </div>
                  <div class="col-md-3">
                     <label>Başlangıç Tarihi</label>
                     <i class="fa fa-calendar" style="
                        position: absolute;
                        top: 30px;
                        right: 28px;
                        font-size: 13px; z-index: 0;
                        "></i>
                     <input type="text" class="form-control date-picker" name="asistan_tarih" id="kampanyatarih" value="{{date('Y-m-d')}}" autocomplete="off">
                  </div>
                  <div class="col-md-3">
                     <label>Bitis Tarihi</label>
                     <i class="fa fa-calendar" style="
                        position: absolute;
                        top: 30px;
                        right: 28px;
                        font-size: 13px; z-index: 0;
                        "></i>
                     <input type="text" id='kampanyaGecerlilikTarihi' name='kampanyaGecerlilikTarihi' value="{{date('Y-m-d')}}" class="form-control date-picker">
                  </div>
                  <div class="col-md-3" style="margin-bottom: 30px;">
                     <label>Saat</label>
                     <input type="time" class="form-control" name="asistan_saat" id="kampanyasaat" value="{{date('H:i')}}" autocomplete="off">
                  </div>
                  <div class="col-md-1 col-sm-3 col-xs-6 col-6">
                     <label>İndirim(%)</label>
                     <input type="tel" id='kampanyaIndirim'  name='kampanyaIndirim' class="form-control">
                  </div>
                   <div class="col-md-2 col-sm-3 col-xs-6 col-6" style="text-align:center;">
                     <label>Katılımcı Sayısı </label>
                     <p style="font-size:20px; margin-top: 3px;font-weight: bold;">1.000.000</p>
                  </div>
               </div>
               <div class="row">
               </div>
               <div class="modal-footer" style="display:none">
                  <div class="row">
                     <div class="col-7 col-xs-7 col-sm-7">
                        <button type="submit"  class="btn btn-success btn-block">
                        Kaydet & Gönder </button>
                     </div>
                     <div class="col-5 col-xs-5 col-sm-5">
                        <button  
                           type="button"
                           class="btn btn-danger modal_kapat btn-block"
                           data-dismiss="modal"
                           > <i class="fa fa-times"></i>
                        Kapat
                        </button>
                     </div>
                  </div>
               </div>
               <div class="modal-footer" style="display:block">
                  
                  <div class="row">
                     <div class="col-6 col-xs-6 col-sm-6 col-md-3">
                        <button id="gorevTanimla" type="button"  class="btn btn-success btn-block">
                           <i class="fa fa-play"></i>
                        </button>
                     </div>
                      <div class="col-6 col-xs-6 col-sm-6 col-md-3">
                     
                     </div>
                     <div class="col-6 col-xs-6 col-sm-6 col-md-3">
                     
                     </div>
                     <div class="col-6 col-xs-6 col-sm-6 col-md-3">
                        <button id="gorevTanimla" type="button"  class="btn btn-success btn-block">
                       Görev Tanımla</button>
                     </div>
                    
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>