<div id="ongorusme-modal" class="modal fade">
            <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content" style="max-height: 90%;">
                  <form id="ongorusmeformu" method="POST">
                     {{ csrf_field() }}
                     <input type="hidden" name="on_gorusme_id" id="on_gorusme_id" value="">
                     <input type="hidden" name="sube" value="{{$isletme->id}}">
                     <div class="modal-header">
                        <h2 class="modal_baslik"></h2>
                     </div>
                     <div class="modal-body">
                        <div class="row">
                           <div class="col-md-4">
                              <label>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                              <select name="musteri" id="musteri_select_list" class="form-control opsiyonelSelect musteri_secimi" style="width:100%">
                                 <option></option>
                                    
                              </select>
                           </div>
                           <div class="col-md-4">
                              <label>Ad Soyad</label>
                              <input type="text" required name="ad_soyad" id="ad_soyad" class="form-control" required>
                           </div>
                           <div class="col-md-4">
                              <label>Telefon</label> 
                              <input type="tel" required name="telefon" id="telefon"   data-inputmask =" 'mask' : '5999999999'" id="telefon" class="form-control" required>
                           </div>
                           <div class="col-md-6">  
                              <label>E-mail</label>
                              <input type="email" name="email" id="email" class="form-control">
                           </div>
                           <div class="col-md-6">
                              <label>Cinsiyet</label>
                              <select name="cinsiyet" id="cinsiyet" class="form-control">
                                 <option value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                              </select>
                           </div>
                           <div class="col-md-12">
                              <label>Adres</label>
                              <textarea class="form-control" id="adres" name="adres"></textarea>
                           </div>
                           <div class="col-md-6">
                              <label>Şehir</label>
                              <select name="sehir" id="sehir" class="form-control custom-select2" style="width: 100%;">
                                 @foreach(\App\Iller::all() as $il)
                                 <option value="{{$il->id}}">{{$il->il_adi}}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label>Referans</label>
                              <select id="musteri_tipi" name="musteri_tipi" class="form-control">
                                 <option value="0">Yok</option>
                                 <option value="1">İnternet</option>
                                 <option value="2">Reklam</option>
                                 <option value="3">Instagram</option>
                                 <option value="4">Facebook</option>
                                 <option value="5">Tanıdık</option>
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label>Meslek</label>
                              <input type="text" id="meslek" name="meslek" class="form-control">
                           </div>
                           <div class="col-md-6">
                              <label>Ön Görüşme Sebebi</label>
                              <select name="paket_urun" id="paket" class="form-control opsiyonelSelect" style="width: 100%;">
                                 <option></option>
                                 @foreach(\App\Paketler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $paket)
                                 <option value="{{$paket->id}}">
                                    {{$paket->paket_adi}}
                                 </option>
                                 @endforeach
                                 @foreach(\App\Urunler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $urun)
                                 <option value="urun-{{$urun->id}}">
                                    {{$urun->urun_adi}}
                                 </option>
                                 @endforeach
                                 @foreach(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->get() as $hizmet)
                                 <option value="hizmet-{{$hizmet->hizmetler->id}}">
                                    {{$hizmet->hizmetler->hizmet_adi}}
                                 </option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-4">
                              <label>Randevu Tarihi</label>
                              <input type="text" name="ongorusme_tarihi" id="ongorusme_tarihi" class="form-control date-picker" value="{{date('Y-m-d')}}" autocomplete="off">
                           </div>
                           <div class="col-md-2">
                              <label>Randevu Saati </label>
                              <select id='ongorusme_saati' name="ongorusme_saati" class="form-control">
                              @for($j = strtotime(date('07:00')) ; $j < strtotime(date('23:15')); $j+=(15*60)) 
                                                 
                                                 <option value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                 
                                                 
                                                 @endfor 
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label>Görüşmeyi Yapan</label>
                              <select name="gorusmeyi_yapan" id="gorusmeyi_yapan" class="form-control custom-select2 opsiyonelSelect personel_secimi" style="width: 100%;">
                                   <option></option>
                                 
                              </select>
                           </div>
                           <div class="col-md-12">
                              <label>Açıklama</label>
                              <textarea name="aciklama" id="aciklama" class="form-control"></textarea>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer" style="display:block;">
                        <div class="row">
                           <div class="col-md-6 col-6">
                              <button type="submit" class="btn btn-success btn-lg btn-block"> Kaydet</button>
                           </div>
                           <div class="col-md-6 col-6">
                              <button type="button" class="btn btn-danger btn-lg btn-block modal_kapat" data-dismiss="modal">Kapat</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>