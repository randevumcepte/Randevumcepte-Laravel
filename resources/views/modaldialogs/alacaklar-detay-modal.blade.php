 






 <div
            id="senet_taksit_detay_modal"
            class="modal modal-top fade calendar-modal"
            >
            <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;height: 70vh;">
               <div class="modal-content" style="width:100%; height: 100%;max-height: 70vh; overflow-y: auto;">
                  <form id='senet_taksit_duzenleme_tahsilat' method="GET">
                     <div class="pd-20">
                        <div class="tab">
                           <ul class="nav nav-tabs" role="tablist">
                           <li class="nav-item">
                                 <a
                                    class="nav-link active text-blue"
                                    data-toggle="tab"
                                    href="#kalan_odemeler"
                                    role="tab"
                                    aria-selected="true"
                                    >Kalan Ödemeler</a
                                    >
                              </li>
                              <li class="nav-item">
                                 <a id="taksitli_tahsilatlar_bolumu"
                                    class="nav-link text-blue"
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
                                 id="kalan_odemeler"
                                 role="tabpanel"
                                 >
                                 <div class="pd-10">
                                    <div  id="kalan_odemeler_tahsil">
                                       @if($pageindex==11111)

                                       
                                       @endif
                                       @if($pageindex==1111)
                                       
                                          @foreach(\App\Adisyonlar::whereIn('id',$kalan_adisyonlar->pluck('id'))->get() as $adisyon)
                       

                                             @foreach($adisyon->hizmetler as $key=>$hizmet)
                                                @if(($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari > 0 || $hizmet->hediye) &&  $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null)
                                                   <div class="row" style="margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="0">
                                                      <label for="kalemcheck{{ $hizmet->id }}" style="width:100%;height:60px; font-size:18px;" class="list-group-item list-group-item-action" data-value="{{ $hizmet->id }}">                             
                                 
                                                         <input name="kalemcheck[]" id="kalemcheck{{ $hizmet->id }}" type="checkbox" name="secilen_hizmetler[]" data-value="{{ $hizmet->id }}">                                      
                                                         <span name="hizmet_adi[]" data-value="{{ $hizmet->id }}">{{ ($hizmet->hizmet_id != null ? $hizmet->hizmet->hizmet_adi : '') }}</span> <b> :</b> <b><span name="hizmet_fiyat[]" data-value="{{ $hizmet->id }}">{{number_format($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,2,',','.') }}</span> ₺</b> 
                                                         <button type="button" data-value="{{ $hizmet->id }}"  class="btn btn-success" style="float:right">{{ $hizmet->created_at->format('Y-m-d') }}</button>
                                                      </label>                                              
                                                   </div>
                                                   <div class="row aktarilacakKalem" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px;display:none"   data-value="{{$hizmet->id}}">
                                                      <div class="col-md-4 col-5 col-xs-5  col-sm-4">
                                                            {{ ($hizmet->hizmet_id != null ? $hizmet->hizmet->hizmet_adi : '') }}
                                                      </div>
                                                      <div class="col-md-3 col-7 col-xs-7  col-sm-3">
                                                         @if($hizmet->personel_id !== null)
                                                         {{$hizmet->personel->personel_adi}} 
                                                         @elseif($hizmet->cihaz_id !== null)
                                                         {{$hizmet->cihaz->cihaz_adi}} 
                                                         @endif
                                                      </div>
                                                      <div class="col-md-2 col-5 col-xs-5  col-sm-2">
                                                         1 adet
                                                      </div>
                                                      <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">
                                                         <input type="hidden" name="adisyon_hizmet_id[]" value="{{$hizmet->id}}"> 
                                                         <input type="hidden" name="indirim[]" data-value="{{$hizmet->id}}" value="{{$hizmet->indirim_tutari}}">
                                                         <input type="hidden" name="adisyon_hizmet_senet_id[]" value="{{$hizmet->senet_id}}">
                                                         <input type="hidden" name="adisyon_hizmet_taksitli_tahsilat_id[]" value="{{$hizmet->taksitli_tahsilat_id}}">
                                                         @if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)
                                                         <input type="tel" class="form-control try-currency tahsilat_kalemleri" style="height:26px;width: 70%;float:left;" name="himzet_tahsilat_tutari_girilen[]" data-value="{{$hizmet->id}}" value="{{number_format($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar')  - $hizmet->indirim_tutari,2,',','.')}}" >
                                                         @else
                                                         <input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="himzet_tahsilat_tutari_girilen[]" data-value="{{$hizmet->id}}" value="{{number_format($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari ,2,',','.')}}" >
                                                         <p style='position: relative; float: left; width: 70%;'>  {{number_format($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari,2,',','.')}} ₺</p>
                                                         @if($hizmet->hediye)
                                                         <i class="fa fa-gift"></i>
                                                         @endif
                                                         @endif
                                                         <p style="position: relative; float: left;width: 15%;margin: 0;">
                                                            @if($hizmet->hediye)
                                                            <i class="fa fa-gift" style="font-size: 25px"></i>
                                                            @else
                                                            <i class="fa fa-gift" style="visibility: hidden"></i>
                                                            @endif
                                                         </p>
                                                         <div class="dropdown" style="width: 15%;float:left">
                                                            <a
                                                               class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                               href="#"
                                                               role="button"
                                                               data-toggle="dropdown"
                                                               >
                                                            <i class="dw dw-more"></i>
                                                            </a>
                                                            <div
                                                               class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                                                               >
                                                              
                                                               @if(($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null && \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->count()==0) || $hizmet->fiyat == 0)
                                                               @if(!$hizmet->hediye)
                                                               <a class="dropdown-item tahsilat_hizmet_hediye_ver" data-value="{{$hizmet->id}}" href="#"
                                                                  ><i class="fa fa-gift"></i> Hediye Ver</a
                                                                  >
                                                               @else
                                                               <a class="dropdown-item tahsilat_hizmet_hediye_kaldir" data-value="{{$hizmet->id}}" href="#"
                                                                  ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a
                                                                  >
                                                               @endif
                                                               <a class="dropdown-item tahsilat_hizmet_sil" data-value="{{$hizmet->id}}" href="#"
                                                                  ><i class="dw dw-delete-3"></i> Sil</a
                                                                  >
                                                               @else
                                                                <a class="dropdown-item tahsilat_kalem_sil" href="#" data-value="{{$hizmet->id}}"
                                                                  ><i class="dw dw-delete-3"></i> Sil</a
                                                                  >
                                                               @endif
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                @endif  
                                             @endforeach
                                          
                                             @foreach($adisyon->urunler as $key=>$urun)
                                                @if(($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari > 0 || $urun->hediye) &&  $urun->senet_id === null && $urun->taksitli_tahsilat_id===null )
                                                   <div class="row" style="margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="0">

                                                      <label for="kalemcheck{{ $urun->id }}" style="width:100%;height:60px; font-size:18px;" class="list-group-item list-group-item-action" data-value="{{ $urun->id }}">
                                                         <input type="hidden" name="urun_id[]" data-value="{{ $urun->id }}" value="{{ $urun->id }}">                                                      
                                                         <input name="kalemcheck[]" id="kalemcheck{{ $urun->id }}" type="checkbox" name="secilen_urunler[]" data-value="{{ $urun->id }}">                                                        
                                                         {{ $urun->urun->urun_adi }}<b> :</b> <b><span name="urun_fiyat[]" data-value="{{ $urun->id }}">{{ number_format($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.') }}</span> ₺</b>
                                                         <button type="button" data-value="{{ $urun->id }}" data-toggle="modal"  class="btn btn-success" style="float:right">{{ $urun->created_at->format('Y-m-d') }}</button>
                                                                                                        
                                                      </label>


                                                   </div>
                                                   <div class="row  aktarilacakKalem" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px;display: none;"  data-value="{{$urun->id}}">
                                                      <div class="col-md-4 col-5 col-xs-5 col-sm-4">
                                                         {{$urun->urun->urun_adi}} 
                                                      </div>
                                                      <div class="col-md-3  col-7 col-xs-7  col-sm-3">
                                                         {{($urun->personel_id ? $urun->personel->personel_adi : "")}}
                                                      </div>
                                                      <div class="col-md-2 col-5 col-xs-5  col-sm-2">
                                                         @if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)
                                                         <input type="tel" value="{{$urun->adet}}" data-value="{{$urun->id}}" class="form-control" style="height:26px;float:left;width: 60%;" name="urun_adet_girilen[]"> <span style="float:left;position:relative;">adet</span> 
                                                         @else
                                                         {{$urun->adet}} adet
                                                         @endif
                                                      </div>
                                                      <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">
                                                         <input type="hidden" name="adisyon_urun_id[]" value="{{$urun->id}}"> 
                                                         <input type="hidden" name="indirim[]" data-value="{{$urun->id}}" value="{{$urun->indirim_tutari}}">
                                                         <input type="hidden" name="adisyon_urun_senet_id[]" value="{{$urun->senet_id}}">
                                                         <input type="hidden" name="adisyon_urun_taksitli_tahsilat_id[]" value="{{$urun->taksitli_tahsilat_id}}">
                                                         @if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)
                                                         <input type="tel" class="form-control try-currency tahsilat_kalemleri" style="height:26px;width: 70%;float:left" name="urun_tahsilat_tutari_girilen[]" data-value="{{$urun->id}}" value="{{number_format($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.')}}" >
                                                         @else
                                                         @if($urun->senet_id == null || $urun->taksitli_tahsilat_id == null)
                                                         <input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="urun_tahsilat_tutari_girilen[]" data-value="{{$urun->id}}" value="{{number_format($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.')}}" >
                                                         @else
                                                         <input type="hidden" class="form-control try-currency tahsilat_kalemleri"  name="urun_tahsilat_tutari_girilen[]" data-value="{{$urun->id}}" value="{{number_format(0,2,',','.')}}" >
                                                         @endif
                                                         <p style='position: relative; float: left; width: 70%;'> {{number_format($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari,2,',','.')}} ₺</p>
                                                         @endif
                                                         <p style="position: relative; float: left;width: 15%;margin: 0;">
                                                            @if($urun->hediye)
                                                            <i class="fa fa-gift" style="font-size: 25px"></i>
                                                            @else
                                                            <i class="fa fa-gift" style="visibility: hidden"></i>
                                                            @endif
                                                         </p>
                                                         <div class="dropdown" style="width: 15%;float:left">
                                                            <a
                                                               class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                                               href="#"
                                                               role="button"
                                                               data-toggle="dropdown"
                                                               >
                                                            <i class="dw dw-more"></i>
                                                            </a>
                                                            <div
                                                               class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                                                               >
                                                               @if(($urun->senet_id == null && $urun->taksitli_tahsilat_id == null && \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->count()==0) || $urun->fiyat == 0)
                                                               @if(!$urun->hediye)
                                                               <a class="dropdown-item tahsilat_urun_hediye_ver" data-value="{{$urun->id}}" href="#"
                                                                  ><i class="fa fa-gift"></i> Hediye Ver</a
                                                                  >
                                                               @else
                                                               <a class="dropdown-item tahsilat_urun_hediye_kaldir" data-value="{{$urun->id}}" href="#"
                                                                  ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a
                                                                  >
                                                               @endif
                                                               <a class="dropdown-item tahsilat_urun_sil" href="#" data-value="{{$urun->id}}"
                                                                  ><i class="dw dw-delete-3"></i> Sil</a
                                                                  >
                                                               @else
                                                                <a class="dropdown-item tahsilat_kalem_sil" href="#" data-value="{{$urun->id}}"
                                                                  ><i class="dw dw-delete-3"></i> Sil</a
                                                                  >
                                                               @endif
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                @endif
                                             @endforeach
                                           

                                             @foreach($adisyon->paketler as $key=>$paket)
                                                @if(($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari > 0 || $paket->hediye) &&  $paket->senet_id === null && $paket->taksitli_tahsilat_id === null   )
                                                <div class="row" style="margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">

                                                   <label for="kalemcheck{{ $paket->id }}" style="width:100%;height:60px; font-size:18px;" class="list-group-item list-group-item-action" data-value="{{ $paket->id }}">
                                                      <input type="hidden" name="paket_id[]" data-value="{{ $paket->id }}" value="{{ $paket->id }}"> 
                                                      <input name="kalemcheck[]" id="kalemcheck{{ $paket->id }}" type="checkbox" name="secilen_paketler[]" data-value="{{ $paket->id }}">  
                                                      {{ $paket->paket->paket_adi }}<b> :</b> <b><span name="paket_fiyat[]" data-value="{{ $paket->id }}">{{ number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.') }}</span> ₺</b>  
                                                      <button type="button" data-value="{{ $paket->id }}" data-toggle="modal"  class="btn btn-success" style="float:right">{{ $paket->created_at->format('Y-m-d') }}</button> 
                                                   </label>
                                                
                                              
                                                </div>
                                                <div class="row aktarilacakKalem" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px;display: none" data-value="{{$paket->id}}">
                           <div class="col-md-4 col-5 col-xs-5  col-sm-4">
                              {{$paket->paket->paket_adi}} 
                           </div>
                           <div class="col-md-3  col-7 col-xs-7  col-sm-3">
                              {{($paket->personel_id ? $paket->personel->personel_adi:"")}}
                           </div>
                           <div class="col-md-2 col-5 col-xs-5  col-sm-2">
                              1 adet
                           </div>
                           <div class="col-md-3 col-7 col-xs-7  col-sm-3"  style="text-align:right">
                              <input type="hidden" name="adisyon_paket_id[]" value="{{$paket->id}}"> 
                              <input type="hidden" name="adisyon_paket_senet_id[]" value="{{$paket->senet_id}}">
                              <input type="hidden" name="adisyon_paket_taksitli_tahsilat_id[]" value="{{$paket->taksitli_tahsilat_id}}">
                              <input type="hidden" name="indirim[]" data-value="{{$paket->id}}" value="{{$paket->indirim_tutari}}">
                              @if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)
                              <input type="tel"  style="height: 26px;width: 70%;float:left" class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="{{$paket->id}}" value="{{number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.')}}">
                              @else
                              <input type="hidden"  class="form-control try-currency tahsilat_kalemleri" name="paket_tahsilat_tutari_girilen[]" data-value="{{$paket->id}}" value="{{number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.')}}">
                              <p style='position: relative; float: left; width: 70%;'>   {{number_format($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari,2,',','.')}} ₺ </p>
                              @endif
                              <p style="position: relative; float: left;width: 15%; margin:0">
                                 @if($paket->hediye)
                                 <i class="fa fa-gift" style="font-size: 25px"></i>
                                 @else
                                 <i class="fa fa-gift" style="visibility: hidden"></i>
                                 @endif
                              </p>
                              <div class="dropdown"  style="width: 15%;float:left">
                                 <a
                                    class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle"
                                    href="#"
                                    role="button"
                                    data-toggle="dropdown"
                                    >
                                 <i class="dw dw-more"></i>
                                 </a>
                                 <div
                                    class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"
                                    >
                                   
                                    @if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)
                                    @if(!$paket->hediye)
                                    <a class="dropdown-item tahsilat_paket_hediye_ver" data-value="{{$paket->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediye Ver</a
                                       >
                                    @else
                                    <a class="dropdown-item tahsilat_paket_hediye_kaldir" data-value="{{$paket->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a
                                       >
                                    @endif
                                    <a class="dropdown-item tahsilat_paket_sil" data-value="{{$paket->id}}" href="#"
                                       ><i class="dw dw-delete-3"></i> Sil</a
                                       >
                                    @else
                                    <a class="dropdown-item tahsilat_kalem_sil" data-value="{{$paket->id}}" href="#"
                                       ><i class="dw dw-delete-3"></i> Sil</a
                                       >
                                    @endif
                                 </div>
                              </div>
                           </div>
                        </div>
                                                
                                                @endif
                                             @endforeach
                                         
                                          @endforeach
                   


                                       @endif
                                    </div>
                                 </div>
                              </div>
                              <div
                                 class="tab-pane fade"
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
                     <div class="modal-footer"  style=" position: sticky;
  bottom: 0;
  background: white;
  padding: 15px;
  border-top: 1px solid #dee2e6;
  z-index: 10;display: block;">
                        <div class="row">
                           <div class="col-6 col-xs-6">
                              <button type="submit" id='secili_alacaklari_tahsil_et' class="btn btn-success btn-lg btn-block">
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