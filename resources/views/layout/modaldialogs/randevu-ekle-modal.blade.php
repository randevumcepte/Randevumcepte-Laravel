<div
            id="modal-view-event-add"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width: 1000px;">
               <div class="modal-content" style="min-height:467px">
                  <div class="modal-header">
                     <h4 class="h4">
                        <span>Yeni</span>
                     </h4>
                     <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true"
                        >
                     ×
                     </button>
                  </div>
                  <div class="modal-body" style="
                     padding: 1rem 1rem 0 1rem;
                     ">
                     <div class="tab">
                        <ul class="nav nav-tabs" role="tablist">
                           <li class="nav-item">
                              <a
                                 class="nav-link active text-blue"
                                 data-toggle="tab"
                                 href="#yeni-randevu"
                                 role="tab"
                                 aria-selected="true"
                                 >Randevu</a
                                 >
                           </li>
                           @if(!Auth::user()->hasRole('Personel') && !Auth::user()->hasRole('Sosyal Medya Uzmanı'))
                           <li class="nav-item">
                              <a
                                 class="nav-link text-blue"
                                 data-toggle="tab"
                                 href="#saat-kapama"
                                 role="tab"
                                 aria-selected="false"
                                 >Saat Kapama</a
                                 >
                           </li>
                           @endif
                        </ul>
                        <div class="tab-content">
                           <div
                              class="tab-pane fade show active"
                              id="yeni-randevu"
                              role="tabpanel"
                              >
                              <div class="pd-10">
                                 <form id="yenirandevuekleform"  method="POST">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-md-4 col-sm-8 col-xs-8 col-8">
                                          <input type="hidden" name="sube" value="{{$isletme->id}}">
                                          @if($pageindex==2)
                                          <input type="hidden" name="takvim_sayfasi" value="1">
                                          @endif
                                          <label>@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                                          <select name="adsoyad" class="form-control custom-select2" style="width: 100%;">
                                             {!!$portfoy_drop!!}
                                          </select>
                                       </div>
                                       <div class="col-md-2 col-sm-4 col-xs-4 col-4">
                                          <label style="visibility: hidden;width: 100%;">yenimüşteri</label>
                                          <button class="btn btn-primary yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal"><i class="icon-copy fi-plus"></i>Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</button>
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                          <label>Tarih</label>
                                          <input required placeholder="Tarih"
                                             type="text"
                                             class="form-control"
                                             name="tarih" id="randevutarihiyeni" autocomplete="off"
                                             />
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                          <label>Saat </label>
                                          <select id='randevu_saat' name="saat" class="form-control">
                                             <?php $secanahtar=1; ?>
                                             @for($j = strtotime(date('00:00')) ; $j < strtotime(date('23:59')); $j+=(5*60)) 
                                             @if( $j< strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('baslangic_saati')) )) || $j >= strtotime(date('H:i', strtotime(\App\SalonCalismaSaatleri::where('salon_id',$isletme->id)->where ('haftanin_gunu',$day)->value('bitis_saati')) )) || $j < strtotime(date('H:i')) )
                                             @if($j<=strtotime(date('H:i')))
                                             <option style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                             @else
                                             <option style="background-color:red;color:#fff" value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                             @endif 
                                             @else
                                             <option {{($secanahtar==1) ? 'selected': ''}} value="{{date('H:i',$j)}}:00 ">{{date('H:i',$j)}}</option>
                                             <?php $secanahtar++; ?>
                                             @endif
                                             @endfor 
                                          </select>
                                       </div>
                                       <div class="col-md-12">
                                          <label>Personel Notu</label>
                                          <textarea class="form-control" name="personel_notu" placeholder="Notlar"></textarea>
                                       </div>
                                    </div>
                                    <div class="hizmetler_bolumu" style="margin-top:20px">
                                       <div class="row" data-value="0" style="background: #e2e2e2;margin: 5px 0 5px 0;padding-bottom: 5px;">
                                          
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                             <label>Personel </label>
                                             <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect" style="width: 100%;">
                                                <option></option>

                                                
                                                {!!$personel_drop!!}
                                                
                                             </select>
                                          </div>
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                             <label>Yardımcı Personel(-ler) </label>
                                             <select name="randevuyardimcipersonelleriyeni" id="randevuyardimcipersonelleriyeni_0[]" multiple class="form-control custom-select2" style="width: 100%;">
                                                   
                                                
                                                {!!$personel_drop!!}
                                                
                                             </select>
                                          </div>
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                             <label>Cihaz</label>
                                             <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect" style="width: 100%;">
                                                <option></option>
                                                @foreach(\App\Cihazlar::where('salon_id',$isletme->id)->where('durum',true)->where('aktifmi',true)->get() as $cihaz)
                                                <option value="{{$cihaz->id}}">{{$cihaz->cihaz_adi}}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                             <label>Hizmet</label>
                                             <select name="randevuhizmetleriyeni[]" class="form-control custom-select2" style="width: 100%;">
                                                 {!!$hizmet_drop!!}
                                             </select>
                                          </div>
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                             <label>Oda (opsiyonel)</label>
                                             <select name="randevuodalariyeni[]"  class="form-control opsiyonelSelect" style="width:100%">
                                                <option></option>
                                                @foreach(\App\Odalar::where('salon_id',$isletme->id)->where('durum',true)->where('aktifmi',true)->get() as $oda)
                                                <option value="{{$oda->id}}">{{$oda->oda_adi}}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                         
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-64">
                                             <label>Süre</label>
                                             <input type="tel" class="form-control" name="hizmet_suresi[]" value="{{(\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->first() !== null) ? \App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->value('sure_dk') : ''}}">
                                          </div>
                                          <div class="col-md-3 col-sm-6 col-xs-6 col-6">
                                             <label>Fiyat</label>
                                             <input type="tel" class="form-control" name="hizmet_fiyat[]" value="{{
                                                (\App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->first()!==null) ? \App\SalonHizmetler::where('salon_id',$isletme->id)->where('aktif',true)->value('baslangic_fiyat') : ''}}">
                                          </div>
                                          <div class="col-md-1  col-sm-6 col-xs-6 col-6">
                                             <label style="visibility: hidden;width: 100%;">Kaldır</label>
                                             <button type="button" name="hizmet_formdan_sil"  data-value="0" class="btn btn-danger" disabled style="padding:1px; border-radius: 0; line-height: 1px ; font-size:18px;background-color: transparent; border-color: transparent;color:#dc3545"><i class="icon-copy fa fa-remove"></i></button>
                                          </div>
                                          <div class="col-md-2 col-sm-6 col-xs-6 col-6">
                                             <label>Üsttekiyle Birleştir</label>
                                             <div class="custom-control custom-checkbox mb-5">
                                                <input type="checkbox" class="custom-control-input" name="birlestir" disabled style="display:none" id="customCheck0"/>
                                                <label class="custom-control-label" name="birlestir_label" for="customCheck0" style="display:none"></label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-6 col-sm-6 col-xs-6 ">
                                          <div class="row">
                                             <div class="col-md-4 col-xs-4 col-sm-4 col-4">
                                                <label>Tekrarlayan</label><br>
                                                <label class="switch">
                                                <input id="tekrarlayan" name="tekrarlayan" type="checkbox">
                                                <span class="slider"></span>
                                                </label> 
                                             </div>
                                             <div class="col-md-4 col-xs-4 col-sm-4 col-4">
                                                <label>Tekrar Sıklığı</label>
                                                <select class="form-control tekrar_randevu" name="tekrar_sikligi" disabled>
                                                   <option value="+1 day">Her gün</option>
                                                   <option value="+2 days">2 günde bir </option>
                                                   <option value="+3 days">3 günde bir </option>
                                                   <option value="+4 days">4 günde bir </option>
                                                   <option value="+5 days">5 günde bir </option>
                                                   <option value="+6 days">6 günde bir </option>
                                                   <option value="+1 week">Haftada bir</option>
                                                   <option value="+2 weeks">2 Haftada bir</option>
                                                   <option value="+3 weeks">3 Haftada bir</option>
                                                   <option value="+4 weeks">4 Haftada bir</option>
                                                   <option value="+1 month">Her ay</option>
                                                   <option value="+45 days">45 günde bir</option>
                                                   <option value="+2 months">2 ayda bir</option>
                                                   <option value="+3 months">3 ayda bir</option>
                                                   <option value="+6 months">6 ayda bir</option>
                                                </select>
                                             </div>
                                             <div class="col-md-4 col-xs-4 col-sm-4 col-4">
                                                <label>Tekrar Sayısı</label>
                                                <input type="tel" name="tekrar_sayisi" class="form-control tekrar_randevu" required value="0" disabled>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-6 col-sm-6 col-xs-6">
                                          <div class="row">
                                             <div class="col-md-6 col-6" style="text-align:center;">
                                                <label style="visibility:hidden; width: 100%;">Bir Hizmet</label>
                                                <button type="button" id="bir_hizmet_daha_ekle" class="btn btn-primary">
                                                Bir Hizmet Daha Ekle
                                                </button>
                                             </div>
                                             <div class="col-md-6 col-6">
                                                <label style="visibility:hidden;">Randevu Oluştur</label>
                                                <button type="submit" class="btn btn-success btn-lg">Randevu Oluştur</button>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           @if(!Auth::user()->hasRole('Personel') && !Auth::user()->hasRole('Sosyal Medya Uzmanı'))
                           <div class="tab-pane fade" id="saat-kapama" role="tabpanel">
                              <div class="pd-20">
                                 <form id="saat_kapama" method="POST">
                                    <input type="hidden" value="{{$isletme->id}}" name="sube">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                       <div class="col-md-3">
                                          <label>Personel</label>
                                          <select name="personel" class="form-control custom-select2" style="width: 100%;">
                                             {!!$personel_drop!!}
                                          </select>
                                       </div>
                                       <div class="col-md-3">
                                          <label>Tarih</label>
                                          <input type="text" required class="form-control date-picker" name="tarih" autocomplete="off" >
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='baslangic_saati_yazi'>
                                          <label>Başlangıç Saati</label>
                                          <input type="time" class="form-control" name="saat" id='kapama_saat_baslangic' required>
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='bitis_saati_yazi'>
                                          <label>Bitiş Saati</label>
                                          <input type="time" class="form-control" name="saat_bitis" id='kapama_saat_bitis' required>
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                          <label>Tüm gün</label><br>
                                          <label class="switch" >
                                          <input type="checkbox" name="tum_gun" id="tum_gun">
                                          <span class="slider"></span>
                                          </label>
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                          <label>Tekrarlayan</label><br>
                                          <label class="switch" >
                                          <input id="tekrarlayan_saat_kapama" name="tekrarlayan" type="checkbox">
                                          <span class="slider"></span>
                                          </label> 
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                          <label>Tekrar Sıklığı</label>
                                          <select class="form-control tekrar_saat_kapama" name="tekrar_sikligi" disabled >
                                             <option value="+1 day">Her gün</option>
                                             <option value="+2 days">2 günde bir </option>
                                             <option value="+3 days">3 günde bir </option>
                                             <option value="+4 days">4 günde bir </option>
                                             <option value="+5 days">5 günde bir </option>
                                             <option value="+6 days">6 günde bir </option>
                                             <option value="+1 week">Haftada bir</option>
                                             <option value="+2 weeks">2 Haftada bir</option>
                                             <option value="+3 weeks">3 Haftada bir</option>
                                             <option value="+4 weeks">4 Haftada bir</option>
                                             <option value="+1 month">Her ay</option>
                                             <option value="+45 days">45 günde bir</option>
                                             <option value="+2 months">2 ayda bir</option>
                                             <option value="+3 months">3 ayda bir</option>
                                             <option value="+6 months">6 ayda bir</option>
                                          </select>
                                       </div>
                                       <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                          <label>Tekrar Sayısı</label>
                                          <input type="tel" name="tekrar_sayisi" class="form-control tekrar_saat_kapama" required value="0" disabled>
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-12">
                                          <label>Notlar</label>
                                          <textarea name="personel_notu" class="form-control"></textarea>
                                       </div>
                                       <div class="col-md-12">
                                          <button type="submit" class="btn btn-success btn-lg btn-block"><i class="fa fa-save"></i> Kaydet</button>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           @endif
                        </div>
                     </div>
                  </div>
               </div>
               </form>
            </div>
         </div>