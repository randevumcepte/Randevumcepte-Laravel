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
            <select style="display:none" name="tahsilat_musteri_id" id='tahsilat_musteri_id'>
               <option selected value="{{$musteri->id}}">777</option>
            </select>
            <input type="hidden" name="tahsilat_tutari" id='toplam_tahsilat_tutari' >
            <input type="hidden" name="sube"  value="{{$isletme->id}}">
            <input type="hidden" name="adisyon_id" id="session_adisyon_id" value="{{$adisyon_id}}">
            <div class="modal-header">
               <h2>Satış & Tahsilat Düzenleme</h2>
               @if(\Schema::hasColumn('carkifelek_odulleri','adisyon_id'))
                  @php
                     $_kullanilan_kupon = \App\CarkifelekOdulleri::where('adisyon_id',$adisyon_id)->where('kullanildi',1)->first();
                  @endphp
                  @if($_kullanilan_kupon)
                     @php
                        $_tipAd = $_kullanilan_kupon->tip == 'hizmet_indirimi' ? 'Hizmet' : ($_kullanilan_kupon->tip == 'urun_indirimi' ? 'Ürün' : 'Paket');
                        $_indMetni = (isset($_kullanilan_kupon->indirim_tipi) && $_kullanilan_kupon->indirim_tipi === 'tutar')
                           ? ((int)$_kullanilan_kupon->deger).' ₺'
                           : '%'.((int)$_kullanilan_kupon->deger);
                     @endphp
                     <div style="margin-left:auto;background:#fff3cd;color:#856404;border:1px solid #ffeeba;border-radius:6px;padding:6px 12px;font-size:13px;font-weight:bold;">
                        🎁 Çark kuponu kullanıldı: {{ $_kullanilan_kupon->kod }} — {{ $_indMetni }} {{ $_tipAd }} İndirimi
                        @if($_kullanilan_kupon->kullanim_tarihi)
                           <span style="font-weight:normal;color:#7a6010;font-size:11px;">({{ date('d.m.Y', strtotime($_kullanilan_kupon->kullanim_tarihi)) }})</span>
                        @endif
                     </div>
                  @endif
               @endif
            </div>
            @if(!empty($isletme->studyo_modu))
            {{-- Studyo modu (fiyat gizleme): tutar makinesi gizli, ikili odeme durumu --}}
            <style>
               .tek_tahsilat_formu, #odeme_kayit_bolumu, #tahsilats_type, .tahsilat_kalemleri { display:none !important; }
            </style>
            <div class="modal-body" style="padding-bottom:0">
               <div id="studyo_odeme_panel" style="border:1px solid #e5e5ef;border-radius:12px;padding:16px;background:#faf9ff;">
                  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                     <div>
                        <div style="font-weight:700;font-size:15px;color:#333;">Ödeme Durumu</div>
                        <div id="studyo_odeme_rozet" style="margin-top:6px;"></div>
                     </div>
                     <div>
                        <button type="button" class="btn btn-success" onclick="studyoOdemeIsaretle(1)"><i class="fa fa-check"></i> Ödeme Alındı</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="studyoOdemeIsaretle(0)">Ödeme Alınmadı</button>
                     </div>
                  </div>
               </div>
            </div>
            <script>
               (function(){
                  var mevcut = {{ (int)(\Schema::hasColumn('adisyonlar','odendi') ? (\App\Adisyonlar::where('id',$adisyon_id)->value('odendi') ?? 0) : 0) }};
                  function rozetCiz(v){
                     var el = document.getElementById('studyo_odeme_rozet'); if(!el) return;
                     el.innerHTML = (v==1)
                        ? '<span style="background:#1fbf6f;color:#fff;padding:4px 12px;border-radius:20px;font-weight:600;font-size:13px;">✓ Ödeme Alındı</span>'
                        : '<span style="background:#9097ad;color:#fff;padding:4px 12px;border-radius:20px;font-weight:600;font-size:13px;">Ödeme Alınmadı</span>';
                  }
                  window.studyoOdemeIsaretle = function(alindi){
                     var csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                     fetch('/isletmeyonetim/adisyon-odeme-isaretle', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},
                        body: JSON.stringify({adisyon_id: {{ (int)$adisyon_id }}, alindi: alindi})
                     }).then(function(r){return r.json();}).then(function(d){
                        if(d && d.durum==='ok'){ rozetCiz(d.odendi); if(window.swal){ swal({title:'Kaydedildi',icon:'success',timer:1000,buttons:false}); } }
                        else { if(window.swal) swal('Hata','Kaydedilemedi','error'); }
                     }).catch(function(){ if(window.swal) swal('Hata','İstek başarısız','error'); });
                  };
                  document.addEventListener('DOMContentLoaded', function(){ rozetCiz(mevcut); });
               })();
            </script>
            @endif
            <div class="modal-body">
               {!!csrf_field()!!}
               <div class="row">
                  <div class="col-md-12">
                     <div class="row" style="margin-bottom: 20px;">
                        <div class="col-2">
                           <button type="button" data-toggle="modal" data-target="#adisyon_yeni_hizmet_modal" id="adisyon_hizmet_ekle_button" class="btn btn-info btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Hizmet Ekle</button>
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                           @if(empty($isletme->studyo_modu))
                           <button type="button" data-toggle="modal" id="adisyon_urun_ekle_button" data-target="#urun_satisi_modal" data-value=''onclick="modalbaslikata('Yeni Ürün Satışı Ekle','')" class="btn  btn-danger  btn-block adisyon_ekle_buttonlar"  style="font-size:12px">Ürün Ekle</button>
                           @endif
                        </div>
                        <div class="col-2" style="padding-left: 0;">
                           <button type="button" data-toggle="modal" id="adisyon_paket_ekle_button" data-target="#paket_satisi_modal" data-value='' class="btn  btn-primary  btn-block adisyon_ekle_buttonlar" style="font-size:12px">Paket Ekle</button>
                        </div>
                        <div class="col-md-6 text-right" id="tahsilats_type">
                           <button  type="button" class="btn btn-success" id='senetle_veya_taksitle_tahsil_et'> Alacaklar</button>
                           @yetki('satis.senet_olustur')
                           <button type="button" id='yeni_taksitli_tahsilat_olusur' href="#"  data-value='' class="btn  btn-primary" style="font-weight: bold;">Taksit Yap</button>
                           @endyetki
                        </div>
                     </div>
                     <div id='tum_tahsilatlar'>
                        @php
                            $_adi_ids = collect($acik_adisyonlar)->pluck('id')->push($adisyon_id)->filter()->unique()->values()->all();
                        @endphp
                        @foreach(\App\Adisyonlar::whereIn('id',$_adi_ids)->get() as $adisyon)
                         
                        @foreach($adisyon->hizmetler as $key=>$hizmet)

                        @if(($hizmet->fiyat - \App\TahsilatHizmetler::where('adisyon_hizmet_id',$hizmet->id)->sum('tutar') - $hizmet->indirim_tutari >= 0 || $hizmet->hediye) &&  $hizmet->senet_id === null && $hizmet->taksitli_tahsilat_id === null)
                        <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"   data-value="0">
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
                               <input type="tel" value="{{$hizmet->seans_sayisi}}" data-value="{{$hizmet->id}}" class="form-control" style="height:26px;float:left;width: 60%;" name="hizmet_seans_girilen[]"> <span style="float:left;position:relative;">seans</span> 
                           </div>
                           <div class="col-md-3 col-7 col-xs-7  col-sm-3" style="text-align:right">
                              <input type="hidden" name="adisyon_hizmet_id[]" value="{{$hizmet->id}}"> 
                              <input type="hidden" name="indirim[]" data-value="{{$hizmet->id}}" value="{{$hizmet->indirim_tutari}}">
                              <input type="hidden" name="adisyon_hizmet_senet_id[]" value="{{$hizmet->senet_id}}">
                              <input type="hidden" name="adisyon_hizmet_taksitli_tahsilat_id[]" value="{{$hizmet->taksitli_tahsilat_id}}">
                              @if($hizmet->senet_id == null && $hizmet->taksitli_tahsilat_id == null)
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
                                    @yetki('satis.hediye_isle')
                                    @if(!$hizmet->hediye)
                                    <a class="dropdown-item tahsilat_hizmet_hediye_ver" data-value="{{$hizmet->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediye Ver</a
                                       >
                                    @else
                                    <a class="dropdown-item tahsilat_hizmet_hediye_kaldir" data-value="{{$hizmet->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a
                                       >
                                    @endif
                                    @endyetki
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
                        @if(($urun->fiyat - \App\TahsilatUrunler::where('adisyon_urun_id',$urun->id)->sum('tutar') - $urun->indirim_tutari >= 0 || $urun->hediye) &&  $urun->senet_id === null && $urun->taksitli_tahsilat_id===null )
                        <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px"  data-value="0">
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
                              @if($urun->senet_id == null && $urun->taksitli_tahsilat_id == null)
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
                                    @yetki('satis.hediye_isle')
                                    @if(!$urun->hediye)
                                    <a class="dropdown-item tahsilat_urun_hediye_ver" data-value="{{$urun->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediye Ver</a
                                       >
                                    @else
                                    <a class="dropdown-item tahsilat_urun_hediye_kaldir" data-value="{{$urun->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a
                                       >
                                    @endif
                                    @endyetki
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
                        @if(($paket->fiyat - \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->sum('tutar') - $paket->indirim_tutari >= 0 || $paket->hediye) &&  $paket->senet_id === null && $paket->taksitli_tahsilat_id === null   )
                        <div class="row tahsilat_kalemleri_listesi" style="background:#cce5ff;margin:5px 0 5px 0; padding:5px;font-size:14px" data-value="0">
                           <div class="col-md-4 col-5 col-xs-5  col-sm-4">
                              {{$paket->paket->paket_adi}} 
                           </div>
                           <div class="col-md-3  col-7 col-xs-7  col-sm-3">
                              {{($paket->personel_id ? $paket->personel->personel_adi:"")}}
                           </div>
                           <div class="col-md-2 col-5 col-xs-5  col-sm-2">
                              <input type="tel" value="{{$paket->seans_sayisi}}" data-value="{{$paket->id}}" class="form-control" style="height:26px;float:left;width: 60%;" name="paket_seans_girilen[]"> <span style="float:left;position:relative;">seans</span> 
                              
                           </div>
                           <div class="col-md-3 col-7 col-xs-7  col-sm-3"  style="text-align:right">
                              <input type="hidden" name="adisyon_paket_id[]" value="{{$paket->id}}"> 
                              <input type="hidden" name="adisyon_paket_senet_id[]" value="{{$paket->senet_id}}">
                              <input type="hidden" name="adisyon_paket_taksitli_tahsilat_id[]" value="{{$paket->taksitli_tahsilat_id}}">
                              <input type="hidden" name="indirim[]" data-value="{{$paket->id}}" value="{{$paket->indirim_tutari}}">
                              @if($paket->senet_id == null && $paket->taksitli_tahsilat_id == null)
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
                                    {{-- Paketi Yukselt: odenmis pakette de gorunur (silme/hediye aksiyonlarindan AYRI, onlar odenmisken gizli kalir) --}}
                                    @yetki('satis.adisyon_olustur')
                                    <a class="dropdown-item tahsilat_paket_yukselt" href="#"
                                       data-value="{{$paket->id}}"
                                       data-paket-adi="{{$paket->paket->paket_adi}}"
                                       ><i class="fa fa-arrow-up"></i> Paketi Yükselt</a>
                                    @endyetki

                                    @if(($paket->senet_id == null && $paket->taksitli_tahsilat_id == null && \App\TahsilatPaketler::where('adisyon_paket_id',$paket->id)->count()==0) || $paket->fiyat == 0)
                                    @yetki('satis.hediye_isle')
                                    @if(!$paket->hediye)
                                    <a class="dropdown-item tahsilat_paket_hediye_ver" data-value="{{$paket->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediye Ver</a
                                       >
                                    @else
                                    <a class="dropdown-item tahsilat_paket_hediye_kaldir" data-value="{{$paket->id}}" href="#"
                                       ><i class="fa fa-gift"></i> Hediyeyi Kaldır</a
                                       >
                                    @endif
                                    @endyetki
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
                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown"><i class="dw dw-more"></i></a>
                                 <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"> <a class="dropdown-item tahsilat_taksit_sil" data-value="{{$taksit_gelen_vade->taksit_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div>
                              </div>
                           </div>
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
                                 <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown"><i class="dw dw-more"></i></a>
                                 <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list"> <a class="dropdown-item tahsilat_senet_sil" data-value="{{$senet_gelen_vade->senet_vade_id}}" href="#"><i class="dw dw-delete-3"></i> Sil</a> </div>
                              </div>
                           </div>
                        </div>
                        @endforeach
                     </div>
                     <div id="cark_kupon_bolumu" style="display:none;background:#fff3cd;border:1px solid #ffeeba;border-radius:6px;padding:10px;margin:10px 0;">
                        <label style="font-weight:bold;color:#856404;margin:0 0 6px 0;display:block;">🎁 Çark Kuponu</label>
                        <div id="cark_kupon_listesi" style="font-size:12px;color:#856404;margin-bottom:6px;"></div>
                        <div class="row" style="margin:0;">
                           <div class="col-md-8 col-8" style="padding-left:0;">
                              <input type="text" id="cark_kupon_kod" class="form-control" placeholder="Kupon kodu" autocomplete="off" style="text-transform:uppercase;">
                           </div>
                           <div class="col-md-4 col-4" style="padding:0;">
                              <button type="button" id="cark_kupon_uygula_btn" class="btn btn-warning btn-block">Uygula</button>
                           </div>
                        </div>
                     </div>
                     @php
                        $_kampanya_kod_kullanildi = null;
                        if(\Schema::hasColumn('kampanya_katilimcilari','indirim_kodu_adisyon_id') && !empty($adisyon_id)){
                           $_kampanya_kod_kullanildi = \App\KampanyaKatilimcilari::where('indirim_kodu_adisyon_id',$adisyon_id)->where('indirim_kodu_kullanildi',1)->first();
                        }
                     @endphp
                     <div id="kampanya_indirim_kodu_bolumu" style="{{ $_kampanya_kod_kullanildi ? '' : 'display:none;' }}background:#e8f5e9;border:1px solid #c8e6c9;border-radius:6px;padding:10px;margin:10px 0;">
                        <label style="font-weight:bold;color:#2e7d32;margin:0 0 6px 0;display:block;">🎟️ Kampanya İndirim Kodu</label>
                        <div id="kampanya_indirim_kodu_sonuc" style="font-size:12px;color:#2e7d32;margin-bottom:6px;">@if($_kampanya_kod_kullanildi)Bu adisyona uygulandı.@endif</div>
                        <div class="row" style="margin:0;">
                           <div class="col-md-8 col-8" style="padding-left:0;">
                              @if($_kampanya_kod_kullanildi)
                              <input type="text" class="form-control" value="{{ $_kampanya_kod_kullanildi->indirim_kodu }}" disabled>
                              @else
                              <input type="text" id="kampanya_indirim_kodu" class="form-control" placeholder="SMS/WhatsApp ile gelen indirim kodu" autocomplete="off">
                              @endif
                           </div>
                           <div class="col-md-4 col-4" style="padding:0;">
                              @if($_kampanya_kod_kullanildi)
                              <button type="button" class="btn btn-secondary btn-block" disabled>Uygulandı ✓</button>
                              @else
                              <button type="button" id="kampanya_indirim_kodu_uygula_btn" class="btn btn-success btn-block">Uygula</button>
                              @endif
                           </div>
                        </div>
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
                        @if(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->count()>3 
                        && 
                        date('Y-m-d H:i:s', strtotime('+90 days',strtotime(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->orderBy('id','desc')->value('created_at')))) < date('Y-m-d H:i:s', strtotime('+90 days', strtotime(date('Y-m-d H:i:s')))))
                        <input type="tel" id="musteri_indirim" value="{{$isletme->sadik_musteri_indirim_yuzde}} " disabled class="form-control" style="font-size:20px">
                        @elseif(\App\Adisyonlar::where('user_id',$musteri->id)->where('salon_id',$isletme->id)->count()==0)
                        <input type="tel" id="musteri_indirim" value="{{$isletme->pasif_musteri_indirim_yuzde}} "  disabled class="form-control" style="font-size:20px">
                        @else
                        <input type="tel" id="musteri_indirim" value="{{$isletme->aktif_musteri_indirim_yuzde}} " disabled class="form-control" style="font-size:20px">
                        @endif
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>İndirim (₺)</label>
                        <input  type="tel" name="indirim_tutari" id='harici_indirim_tutari' class="form-control try-currency">
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Komisyon (₺)</label>
                        <input type="tel" name="komisyon_tutari" id="komisyon_tutari" class="form-control try-currency" value="0">
                     </div>
                  </div>
                  <div class="col-md-2 col-sm-6 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödenecek Tutar (₺)</label>
                        <input type="tel" style="font-size: 20px; background-color: #d4edda; border-color: #c3e6cb;" class="form-control try-currency"  name="indirimli_toplam_tahsilat_tutari" id="indirimli_toplam_tahsilat_tutari" value="0">
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Ödeme Yönetmi</label>
                        <select class="form-control" id='adisyon_tahsilat_odeme_yontemi' name="odeme_yontemi">
                           @foreach(\App\OdemeYontemleri::all() as $odeme_yontemi)
                           <option value="{{$odeme_yontemi->id}}">{{$odeme_yontemi->odeme_yontemi}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                   <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
                     <div class="form-group">
                        <label>Banka (opsiyonel)</label>
                        
                        <select class="form-control" id='adisyon_tahsilat_banka' name="banka">
                            <option value=''>Seçiniz...</option>
                           @foreach(\App\SatisOrtakligiModel\Bankalar::all() as $banka)
                           <option value="{{$banka->id}}">{{$banka->banka}}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-4 col-sm-4 col-xs-6 col-6 ">
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
         </form>
      </div>
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
                        <tr style="color:#7a6010; font-weight:600;">
                           <td colspan="3">+ Komisyon (₺)</td>
                           <td id='uygulanan_komisyon_tutari' style="text-align:right;">0,00</td>
                        </tr>
                        <tr style="font-weight: bold; color: green;display: none;">
                           <td colspan="3">
                              Ödenen Tutar (₺): f
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
                           <td colspan="4" style='border:none;font-weight: bold;padding: 20px 0 20px 0;font-size: 16px;'>Geçmiş Ödemeler</td>
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

{{-- ============ PAKETİ YÜKSELT SİHİRBAZI (Yol A: ek seanslı tek-hizmet kalemi) ============ --}}
<div class="modal fade" id="paket_yukselt_modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Paketi Yükselt</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Kapat"><span aria-hidden="true">&times;</span></button>
         </div>
         <div class="modal-body">
            <input type="hidden" id="py_adisyon_paket_id" value="">
            <input type="hidden" id="py_hizmet_id" value="">
            <input type="hidden" id="py_hizmet_adi" value="">

            {{-- Adim 1: var olan mi / yeni paket mi --}}
            <div id="py_adim1">
               <p style="margin-bottom:14px"><strong id="py_paket_adi_baslik"></strong> için ne yapmak istersiniz?</p>
               <button type="button" class="btn btn-primary btn-block" id="py_varolan_btn" style="margin-bottom:10px">
                  <i class="fa fa-arrow-up"></i> Var Olan Paketi Yükselt
               </button>
               <button type="button" class="btn btn-outline-secondary btn-block" id="py_yeni_btn">
                  <i class="fa fa-plus"></i> Yeni Paket Ekle
               </button>
            </div>

            {{-- Adim 2: paket icindeki hizmet secimi --}}
            <div id="py_adim2" style="display:none">
               <p style="margin-bottom:10px">Hangi hizmetin seansını yükseltmek istiyorsunuz?</p>
               <div id="py_hizmet_listesi" style="max-height:260px;overflow:auto"></div>
               <div style="margin-top:14px;text-align:right">
                  <button type="button" class="btn btn-link" id="py_geri1">‹ Geri</button>
               </div>
            </div>

            {{-- Adim 3: ek seans + ek fiyat --}}
            <div id="py_adim3" style="display:none">
               <p style="margin-bottom:12px">Seçilen hizmet: <strong id="py_secili_hizmet_adi"></strong>
                  <span id="py_secili_kalan" style="color:#888;font-size:13px"></span></p>
               <div class="form-group">
                  <label>Eklenecek seans adedi</label>
                  <input type="number" min="1" step="1" class="form-control" id="py_ek_seans" value="1">
               </div>
               <div class="form-group">
                  <label>Ek ücret (₺)</label>
                  <input type="number" min="0" step="0.01" class="form-control" id="py_ek_fiyat" value="0">
                  <small style="color:#888">Bu tutar yeni bir kalem olarak eklenir; kalanı aşağıdaki “Tahsil Et” ile alırsınız.</small>
               </div>
               <div style="text-align:right">
                  <button type="button" class="btn btn-link" id="py_geri2">‹ Geri</button>
                  <button type="button" class="btn btn-success" id="py_kaydet"><i class="fa fa-check"></i> Kaydet</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
(function(){
   if(!window.jQuery) return;
   var $ = window.jQuery;
   var BASE = '/isletmeyonetim';
   function csrf(){ var m=document.querySelector('meta[name=csrf-token]'); return m?m.getAttribute('content'):$('input[name="_token"]').val(); }
   function goAdim(n){ $('#py_adim1,#py_adim2,#py_adim3').hide(); $('#py_adim'+n).show(); }

   // Dropdown "Paketi Yükselt" -> modal ac (Adim 1)
   $(document).on('click', '.tahsilat_paket_yukselt', function(e){
      e.preventDefault();
      $('#py_adisyon_paket_id').val($(this).attr('data-value'));
      $('#py_hizmet_id').val(''); $('#py_hizmet_adi').val('');
      $('#py_paket_adi_baslik').text($(this).attr('data-paket-adi') || 'Bu paket');
      $('#py_ek_seans').val(1); $('#py_ek_fiyat').val(0);
      goAdim(1);
      $('#paket_yukselt_modal').modal('show');
   });

   // Yeni Paket Ekle -> mevcut paket satis modalini ac
   $('#py_yeni_btn').on('click', function(){
      $('#paket_yukselt_modal').modal('hide');
      var $b = $('#adisyon_paket_ekle_button');
      if($b.length){ $b.trigger('click'); }
   });

   // Var Olan Paketi Yükselt -> hizmetleri getir (Adim 2)
   $('#py_varolan_btn').on('click', function(){
      var apid = $('#py_adisyon_paket_id').val();
      $('#py_hizmet_listesi').html('<div style="padding:12px;color:#888">Yükleniyor...</div>');
      goAdim(2);
      $.ajax({
         type:'POST', url:BASE+'/paketicihizmetlergetir', dataType:'json',
         data:{ _token:csrf(), adisyonpaketid:apid, sube:$('input[name="sube"]').val() },
         success:function(res){
            if(!res || !res.success || !res.hizmetler || !res.hizmetler.length){
               $('#py_hizmet_listesi').html('<div style="padding:12px;color:#c00">Bu pakette hizmet bulunamadı.</div>');
               return;
            }
            var html='';
            res.hizmetler.forEach(function(h){
               html += '<label class="py-hizmet-sat" style="display:block;border:1px solid #e3e3ef;border-radius:8px;padding:10px 12px;margin-bottom:8px;cursor:pointer">'+
                  '<input type="radio" name="py_hizmet_radio" value="'+h.hizmet_id+'" data-adi="'+$('<div>').text(h.hizmet_adi).html()+'" data-kalan="'+h.kalan+'" style="margin-right:8px">'+
                  '<strong>'+$('<div>').text(h.hizmet_adi).html()+'</strong>'+
                  '<span style="float:right;color:#888;font-size:13px">kalan: '+h.kalan+' / '+h.satin_alinan+'</span>'+
                  '</label>';
            });
            $('#py_hizmet_listesi').html(html);
         },
         error:function(req){ $('#py_hizmet_listesi').html('<div style="padding:12px;color:#c00">Hata: '+(req.responseText||'istek başarısız')+'</div>'); }
      });
   });

   // Hizmet secimi -> Adim 3
   $(document).on('change', 'input[name="py_hizmet_radio"]', function(){
      $('#py_hizmet_id').val($(this).val());
      $('#py_hizmet_adi').val($(this).attr('data-adi'));
      $('#py_secili_hizmet_adi').text($(this).attr('data-adi'));
      $('#py_secili_kalan').text('(mevcut kalan: '+$(this).attr('data-kalan')+')');
      goAdim(3);
   });

   $('#py_geri1').on('click', function(){ goAdim(1); });
   $('#py_geri2').on('click', function(){ goAdim(2); });

   // Kaydet
   $('#py_kaydet').on('click', function(){
      var $btn=$(this);
      var ekSeans=parseInt($('#py_ek_seans').val(),10);
      if(!($('#py_hizmet_id').val())){ if(window.swal) swal('Eksik','Lütfen bir hizmet seçin.','warning'); return; }
      if(!ekSeans || ekSeans<1){ if(window.swal) swal('Eksik','Eklenecek seans adedi en az 1 olmalı.','warning'); return; }
      $btn.prop('disabled',true);
      $.ajax({
         type:'POST', url:BASE+'/paketicihizmetyukselt', dataType:'json',
         data:{
            _token:csrf(),
            adisyonpaketid:$('#py_adisyon_paket_id').val(),
            hizmet_id:$('#py_hizmet_id').val(),
            hizmet_adi:$('#py_hizmet_adi').val(),
            ek_seans:ekSeans,
            ek_fiyat:$('#py_ek_fiyat').val(),
            sube:$('input[name="sube"]').val()
         },
         success:function(res){
            $('#paket_yukselt_modal').modal('hide');
            if(res && res.success){
               if(window.swal){ swal({title:'Eklendi', text:(res.mesaj||''), icon:'success', timer:1400, buttons:false}); }
               setTimeout(function(){ location.reload(); }, 700);
            } else {
               $btn.prop('disabled',false);
               if(window.swal) swal('Hata', (res && res.mesaj) ? res.mesaj : 'Kaydedilemedi', 'error');
            }
         },
         error:function(req){
            $btn.prop('disabled',false);
            var msg='İstek başarısız'; try{ var j=JSON.parse(req.responseText); if(j.mesaj) msg=j.mesaj; }catch(e){}
            if(window.swal) swal('Hata', msg, 'error');
         }
      });
   });
})();
</script>

<script>
(function(){
    var ADISYON_ID = @json($adisyon_id);
    if(!ADISYON_ID) return;
    function lockAdisyonId(){
        document.querySelectorAll('input[name="adisyon_id"]').forEach(function(el){
            el.value = ADISYON_ID;
            el.setAttribute('value', ADISYON_ID);
        });
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', lockAdisyonId);
    } else {
        lockAdisyonId();
    }
    // Modal aciliminda ve form submit oncesi sigorta olarak tekrar set et
    if(window.jQuery){
        jQuery(document).on('show.bs.modal', lockAdisyonId);
        jQuery(document).on('submit', '#adisyon_hizmet_formu, #urun_satisi, #paket_satisi, #adisyon_tahsilat', lockAdisyonId);
        jQuery(function(){
            var mid = jQuery('#tahsilat_musteri_id').val();
            if(mid && typeof carkKuponlariniYukle === 'function'){
                carkKuponlariniYukle(mid);
            }
        });
    }
})();
</script>
@endsection