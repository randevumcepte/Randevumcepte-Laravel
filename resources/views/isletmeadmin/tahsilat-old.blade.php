@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
 

   {!!csrf_field()!!}
     
     
   <div class="page-header">
      <div class="row">
         <div class="col-md-6 col-sm-6 col-10">
            <div class="title">
               <h1 style="font-size:20px">{{$sayfa_baslik}}</h1>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
                  </li>
                   
                  <li class="breadcrumb-item active" aria-current="page">
                     {{$sayfa_baslik}}
                  </li>
               </ol>
            </nav>
         </div>
         <div class="col-md-6 col-sm-6 col-2">
         @if(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->count()>3 
                  && 
               date('Y-m-d H:i:s', strtotime('+90 days',strtotime(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))
            <img src="/public/img/sadik-1.png" class="pasifaktifsadik">
            
            @elseif(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->count()==0)
            <img src="/public/img/pasif-1.png" class="pasifaktifsadik">
            @else
            <img src="/public/img/aktif-1.png" class="pasifaktifsadik">
            @endif
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-md-9"> 
         <div class="card-box pd-5"  style="margin-bottom:20px">
          <form id="adisyon_tahsilat"  method="POST">
            <input type="hidden" name="tahsilat_ekrani" id="tahsilat_ekrani" value="1">
            <input type="hidden" name="ad_soyad" id='tahsilat_musteri_id' value="{{$musteri->id}}">
            <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari' >
            <input type="hidden" name="sube"  value="{{$isletme->id}}">

            <div class="modal-header">
               <h2>Tahsilat</h2>
            </div>
            <div class="modal-body">
               {!!csrf_field()!!}
                
               <div class="row">
                  <div class="col-md-12">
                     <div class="row" style="margin-bottom: 20px;">
                       
                        <div class="col-2">
                           
                           <button type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Hizmet Ekle</button>
                           
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                            
                           <button type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value=''onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn  btn-danger  btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Ürün Ekle</button>
                           
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                            
                           <button type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="btn  btn-primary  btn-block adisyon_ekle_buttonlar" style="font-size:12px">Paket Ekle</button>
                           
                        </div>
                       
                        <div class="col-md-6 text-right" id="tahsilats_type">
                           
                                 
                                 <button type="button" class="btn btn-success" id='senetle_veya_taksitle_tahsil_et'> Alacaklar</button>
                                  
                                 
                                 

                                 <button type="button" id='yeni_taksitli_tahsilat_olusur' href="#"  data-value='' class="btn  btn-primary" style="font-weight: bold;">Taksit Yap</button> 
                                  
                          
                        </div>
                       </div>
                       <div id='tum_tahsilatlar'>
                       @foreach(\App\Adisyonlar::whereIn('id',$acik_adisyonlar->pluck('id'))->get() as $adisyon)
                       

                           @foreach($adisyon->hizmetler as $key=>$hizmet)
                           @if(($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari > 0 || $hizmet->hediye) &&  $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null)
                           <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="0">

                              <div class="col-md-4 col-5 col-xs-5  col-sm-4">
                                  
                                       {{$hizmet->hizmet->hizmet_adi}}  
                              </div>
                                  
                             
                                
                              
                               
                              <div class="col-md-3 col-7 col-xs-7  col-sm-3">
                                 @if($hizmet->personel_id !== null)
                                    {{$hizmet->personel->personel_adi}} 
                                 @else
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
                                          <a class="dropdown-item tahsilat_hizmet_bilgi" data-value="{{$hizmet->id}}" href="#"
                                             ><i class="dw dw-eye"></i> Bilgi</a
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
                                          @endif
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              @endif  
                           @endforeach
                        
                         @foreach($adisyon->urunler as $key=>$urun)
                           @if(($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari > 0 || $urun->hediye) &&  $urun->senet_id === null && $urun->taksitli_tahsilat_id===null )
                           <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="0">

                              <div class="col-md-4 col-5 col-xs-5 col-sm-4">
                                  {{$urun->urun->urun_adi}} 
                                 </div>
                                  
                                 
                                
                                 
                               
                              <div class="col-md-3  col-7 col-xs-7  col-sm-3">
                                  {{$urun->personel->personel_adi}}

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
                                          @endif
                                       </div>
                                    </div>
                                    
                                 
                              </div>

                                 
                           </div>
                           @endif
                        @endforeach
                         

                         @foreach($adisyon->paketler as $key=>$paket)
                           @if(($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari > 0 || $paket->hediye) &&  $paket->senet_id === null && $paket->taksitli_tahsilat_id === null   )
                           <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">

                              <div class="col-md-4 col-5 col-xs-5  col-sm-4">
                                 {{$paket->paket->paket_adi}} 
                              </div>
                               
                                 
                                 
                              
                              <div class="col-md-3  col-7 col-xs-7  col-sm-3">
                                  {{$paket->personel->personel_adi}}

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

                                          <a class="dropdown-item tahsilat_paket_bilgi" data-value="{{$paket->id}}" href="#"
                                             ><i class="dw dw-eye"></i> Bilgi</a
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
                                          @endif
                                       </div>
                                    </div>
                                 
                              </div>
                              
                            
                           </div>
                           @endif
                        @endforeach
                       
                      @endforeach
                   

                     </div>
                     <div id="taksitli_ve_senetli_tahsilatlar">
                           @foreach($taksit_gelen_vadeler as $taksit_gelen_vade)
                      <div class="row tahsilat_kalemleri_listesi taksit_vadeleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="{{$taksit_gelen_vade->taksit_vade_id}}">
                         <div class="col-md-4 col-5 col-xs-5  col-sm-4">Taksit Vadesi</div>
                         <div class="col-md-3 col-7 col-xs-7  col-sm-3">{{date('d.m.Y', strtotime($taksit_gelen_vade->tarih))}}</div>
                         <div class="col-md-2 col-5 col-xs-5  col-sm-2">1 adet</div>
                         <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">
                           <input type="hidden" name="taksit_vade_id[]" value="{{$taksit_gelen_vade->taksit_vade_id}}">
                           <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="taksit_tahsilat_tutari_girilen[]" value="{{number_format($taksit_gelen_vade->tutar,2,',','.')}}" style="text-align: right;">
                              <p style="position: relative; float: left; width: 70%;">{{number_format($taksit_gelen_vade->tutar,2,',','.')}}</p>
                              <div class="dropdown" style="width: 15%;float:left">
                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown"><i class="dw dw-more"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"> <a class="dropdown-item tahsilat_taksit_sil" data-value="{{$taksit_gelen_vade->taksit_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div> </div></div>

                      </div>
                      @endforeach
                      @foreach($senet_gelen_vadeler as $senet_gelen_vade)
                      <div class="row tahsilat_kalemleri_listesi senet_vadeleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="{{$senet_gelen_vade->senet_vade_id}}">
                         <div class="col-md-4 col-5 col-xs-5  col-sm-4">Senet Vadesi</div>
                         <div class="col-md-3 col-7 col-xs-7  col-sm-3">{{date('d.m.Y', strtotime($senet_gelen_vade->tarih))}}</div>
                         <div class="col-md-2 col-5 col-xs-5  col-sm-2">1 adet</div>
                         <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">
                           <input type="hidden" name="taksit_vade_id[]" value="{{$senet_gelen_vade->senet_vade_id}}">
                           <input type="hidden" class="form-control try-currency tahsilat_kalemleri" name="senet_tahsilat_tutari_girilen[]" value="{{number_format($senet_gelen_vade->tutar,2,',','.')}}" style="text-align: right;">
                              <p style="position: relative; float: left; width: 70%;">{{number_format($senet_gelen_vade->tutar,2,',','.')}}</p>
                              <div class="dropdown" style="width: 15%;float:left">
                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown"><i class="dw dw-more"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"> <a class="dropdown-item tahsilat_senet_sil" data-value="{{$senet_gelen_vade->senet_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div> </div></div>

                      </div>
                      @endforeach
                     </div>
                  </div>
               </div>
               <div class="row tek_tahsilat_formu" data-value="0">
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Tarih</label>
                        <input type="text" required class="form-control" name="tahsilat_tarihi" id='tahsilat_tarihi' value="{{date('Y-m-d')}}" autocomplete="off">
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label style="width: 100%">Birim Tutar (₺)</label>
                        <input  class="form-control try-currency" id='birim_tutar' value=""   style="font-size:20px" >
                        
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        
                        <label>Müşteri İndirimi (%)</label>
                           <input type="hidden" id='musteri_indirimi' name="musteri_indirimi">
                           @if(\App\Adisyonlar::where('user_id',$adisyon->user_id)->where('salon_id',$isletme->id)->count()>3 
                              && 
                           date('Y-m-d H:i:s', strtotime('+90 days',strtotime(\App\Adisyonlar::where('user_id',$adisyon->user_id)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))
                              <input type="tel" id="musteri_indirim" value="{{$isletme->sadik_musteri_indirim_yuzde}} " disabled class="form-control" style="font-size:20px">

                           @elseif(\App\Adisyonlar::where('user_id',$adisyon->user_id)->where('salon_id',$isletme->id)->count()==0)
                              <input type="tel" id="musteri_indirim" value="{{$isletme->pasif_musteri_indirim_yuzde}} "  disabled class="form-control" style="font-size:20px">
                           @else
                              <input type="tel" id="musteri_indirim" value="{{$isletme->aktif_musteri_indirim_yuzde}} " disabled class="form-control" style="font-size:20px">
                        @endif

                     </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>İndirim (₺)</label>

                        <input  type="tel" name="indirim_tutari" id='harici_indirim_tutari' class="form-control try-currency">
                       
                     </div>
                  </div>
                  <div class="col-md-3 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödenecek Tutar (₺)</label>
                        
                        <input type="tel" style="font-size: 20px; background-color: #d4edda; border-color: #c3e6cb;" class="form-control try-currency"  name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
                     </div>
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödeme Yönetmi</label>
                        <select class="form-control" id='adisyon_tahsilat_odeme_yontemi' name="odeme_yontemi">
                           @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                           <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Kalan Alacak Tutarı (₺)</label>

                        <input type="tel" class="form-control try-currency" name="odenecek_tutar" id='odenecek_tutar'>
                     </div>
                  </div>
                  <div class="col-md-6"></div>
                  <div class="col-md-6">
                     <button id='yeni_tahsilat_ekle' type="submit" class="btn btn-success btn-lg btn-block"> <i class="fa fa-money"></i>
                     Tahsil Et </button>
                  </div>
               
                 
               </div>
            </div>
             
         </form></div>
      </div>
      <div class="col-md-3">
         <div id="odeme_kayit_bolumu">
            <h2>Ödeme</h2>
            
            <div class="card-box pd-20 odemeozeti"  style="margin-bottom:20px">
               <div class="row">
                  <div class="col-12 col-xs-12 col-sm-12">
                     <b style="width: 100%;">Alacak Tutarı (₺)</b>
                  </div>
                  <div class="col-md-12">
                     <span id="tahsil_edilecek_kalan_tutar" style="color:#ff0000;font-size:30px">
                         
                     </span>

                  </div>
                   
                  <div class="col-md-12">
                   
                     <table class="table" style="margin-top:20px">
                        <thead id="tahsilat_durumu">
                           <tr>
                              <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Özet</td>
                           </tr>
                           <tr>
                              <td colspan="3">Ara Toplam (₺)</td>
                              <td id='ara_toplam' style="text-align:right;">  </td>
                           </tr>
                           <tr>
                              <td colspan="3">Müşteri İndirimi (₺)</td>
                              <td id='uygulanan_indirim_tutari' style="text-align:right;"> </td>
                           </tr>
                           <tr>
                              <td colspan="3">Harici İndirim (₺)</td>
                              <td id='uygulanan_harici_indirim_tutari' style="text-align:right;"> </td>
                           </tr>
                           <tr style="font-weight: bold; color: green;display: none;">
                              <td colspan="3">
                                 Ödenen Tutar (₺): 
                              </td>
                              <td id="tahsil_edilen_tutar" style="text-align:right;">
                                 {{number_format($tahsilatlar->sum('tutar'),2,',','.')}}
                              </td>
                           </tr>
                             <tr style="font-weight: bold; color: red;">
                              <td colspan="3">
                                 Alacak Tutarı (₺): 
                              </td>
                              <td class="tahsil_edilecek_kalan_tutar" style="text-align:right;">
                                   
                              </td>
                           </tr>
                          
                           
                        </thead>
                        <tbody id="tahsilat_listesi">
                           <tr>
                              <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Ödeme Akışı</td>
                           </tr>
                           @foreach($tahsilatlar as $key=>$tahsilat)
                           <tr>
                             
                              <td>{{date('d.m.Y',strtotime($tahsilat->odeme_tarihi))}} </td>
                              <td>{{number_format($tahsilat->tutar,2,',','.')}} </td>
                              <td>
                                 {{$tahsilat->odeme_yontemi->odeme_yontemi}}
                              </td>
                              <td>
                                 <button type="button" style="padding:1px; border-radius: 0; line-height: 1px ; font-size:12px;background-color: transparent; border-color: transparent;color:#dc3545" name="tahsilat_adisyondan_sil" data-value="{{$tahsilat->id}}" class="btn btn-danger"><i class="icon-copy fa fa-remove"></i></button>
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                        
                     </table>
                  </div>
               </div>
            </div>
            <button type="submit" class="btn btn-success" style="width:100%;margin-top: 10px;display: none;">Değişiklikleri Kaydet</button>
         </div>
      </div>
   </div>
</form>
 
 
 
</div>

@endsection