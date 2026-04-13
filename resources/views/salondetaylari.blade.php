@extends('layout.layout_salondetay')
@section('content')
<div id="googlemapsarea" tabindex="-1" style="display: none">
   <iframe src="{{$salon->maps_iframe}}" style="width:100%; height: 300px"  frameborder="0" style="border:0" allowfullscreen></iframe>
   <button id="mapshidingbutton" class="btn btn-secondary" style="display:none; width: 100%">HARİTAYI GİZLE</button>
</div>
<section class="block">
   <div class="container">
   <div class="row">
      <ul class="nav2 randevumenu" id="randevunavigation1">
         <li id="hizmetsecbaslik" class="active" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 1</p>
               <span class="randevuadimbaslik">Hizmet Seç<span>
            </a>
         </li>
         <li id="personelsecbaslik" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 2</p>
               <span class="randevuadimbaslik">Personel Seç<span>
            </a>
         </li>
         <li id="tarihsaatsecbaslik" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 3</p>
               <span class="randevuadimbaslik">Tarih & Saat<span>
            </a>
         </li>
         <li id="onaybaslik" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 4</p>
               <span class="randevuadimbaslik">Randevu Onayı<span>
            </a>
         </li>
      </ul>
      <ul class="nav2 randevumenu" id="randevunavigation2">
         <li id="hizmetsecbaslikmobil" class="active" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 1</p>
               <span class="randevuadimbaslik">Hizmet<span>
            </a>
         </li>
         <li id="personelsecbaslikmobil" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 2</p>
               <span class="randevuadimbaslik">Personel<span>
            </a>
         </li>
         <li id="tarihsaatsecbaslikmobil" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 3</p>
               <span class="randevuadimbaslik">Tarih-Saat<span>
            </a>
         </li>
         <li id="onaybaslikmobil" style="width: 25%">
            <a href="#">
               <p class="randevuadimlarirakam"> 4</p>
               <span class="randevuadimbaslik">Onay<span>
            </a>
         </li>
      </ul>
   </div>
   <div class="row" style="margin-top:20px">
      <div class="col-lg-8" id="randevusistemi">
         <div id="hizmetsecimbolumu">
            <aside class="sidebar">
               <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item">
                     <a class="nav-link active" id="one-tab" data-toggle="tab" href="#bayan" role="tab" aria-controls="bayan" aria-expanded="true">Kadın Bölümü</a>
                  </li>
                 
               </ul>
               <input type="hidden"  id="salonid" value="{{$salon->id}}">
               <div class="tab-content" id="myTabContent">
                  <div class="tab-pane fade show active" id="bayan" role="tabpanel" aria-labelledby="bayan-tab">
                     @foreach($salonsunulanhizmetler_kategori as $key=>$kategori_baslik)
                         
                            @if($key==0)
                                <button class="accordion active">{{$kategori_baslik->hizmet_kategorisi->hizmet_kategorisi_adi}} Hizmetleri
                                </button>
                                <div class="panel_accordion" style="display: block;">
                            @else
                                <button class="accordion">{{$kategori_baslik->hizmet_kategorisi->hizmet_kategorisi_adi}} Hizmetleri
                                </button>
                                <div class="panel_accordion">
                            @endif
                            <table class="hizmettablo">
                              @foreach($salonsunulanhizmetler as $hizmetfiyatlistesi)
                              @if($hizmetfiyatlistesi->hizmet_kategori_id == $kategori_baslik->hizmet_kategori_id && $hizmetfiyatlistesi->aktif)
                              <tr>
                                 <td style="width: 50px">
                                    <label class="checkboxcontainer">
                                    <input name="randevuhizmet[]" id="{{'hizmet-'.$hizmetfiyatlistesi->hizmetler->id}}" type="checkbox" class="icheckbox" name="type" value="{{$hizmetfiyatlistesi->hizmetler->id}}">
                                    <span class="checkmark"></span>
                                    </label>
                                 </td>
                                 <td style="padding-top: 0">
                                    {{$hizmetfiyatlistesi->hizmetler->hizmet_adi}}
                                 </td>
                                 <td>
                                    @if($hizmetfiyatlistesi->baslangic_fiyat==null&& $hizmetfiyatlistesi->son_fiyat == null)
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#5C008E; opacity: 1; text-align: center">Bilgi Alınız</p>
                                    @else
                                    @if($hizmetfiyatlistesi->baslangic_fiyat == $hizmetfiyatlistesi->son_fiyat)
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#5C008E; opacity: 1; text-align: center">
                                       {{$hizmetfiyatlistesi->baslangic_fiyat}} <span class="simge-tl">&#8378;</span> 
                                    </p>
                                    @else
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#5C008E; opacity: 1; text-align: center">   {{$hizmetfiyatlistesi->baslangic_fiyat}} ~ {{$hizmetfiyatlistesi->son_fiyat}} <span class="simge-tl">&#8378;</span>  
                                    </p>
                                    @endif
                                    @endif
                                 </td>
                              </tr>
                              @endif
                              @endforeach
                           </table>
                        </div>
                        
                        @endforeach
                        <!-- <a href="#" class="btn btn-info" style="width:100%">FİYAT AL</a> -->
                     </div>
                     
                  </div>
            </aside>
            <button id="personelsecimadiminagec"  class="btn btn-primary width-100 btn-rounded" style="width:100%; margin-top: 10px; margin-bottom: 10px">DEVAM ET <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></button>
            </div> 
            <div id="personelsecimbolumu" style="padding-top:10px">
            </div>
            <div id="tarihsaatsecimbolumu">
               <button id='personelseckisminageridon' style='width:200px;border-radius:60px' class='btn btn-primary'><< GERİ DÖN</button>
               <p style='font-size:20px; font-weight:bold; margin-top:15px'>Tarih Seçimi</
               <p>
               <div id="tarihtablosu" class="tarihler">
                  <div class="input-radio"><input type="radio" id="bugun" name="randevutarihi" value="{{date('Y-m-d')}}" checked> <label for="bugun">Bugün</label></div>
                  <div class="input-radio"><input type="radio" id="yarin" name="randevutarihi" value="{{date('Y-m-d',strtotime('+1 days',strtotime(date('Y-m-d'))))}}"> <label for="yarin">Yarın</label></div>
                  @for ($i = 2 ;$i <= 30; $i++)
                  <div class="input-radio tarihradio"><input  id="nextdays{{$i}}"  type="radio" name="randevutarihi" value="{{date('Y-m-d',strtotime('+'.$i.' days',strtotime(date('Y-m-d')))) }}"> <label for="nextdays{{$i}}">{{str_replace(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],['Pzt','Sal','Çar','Per','Cum','Cts','Paz'], date('d.m D',strtotime('+'.$i.' days',strtotime(date('Y-m-d'))))) }}</label></div>
                  @endfor
               </div>
               <p style='font-size:20px; font-weight:bold;margin-top: 15px'>Saat Seçimi</p>
               <div id="saatsecimtablosu" class="saatler">
               </div>
               <button id="onayadiminagec" class="btn btn-primary width-100 btn-rounded" style="width:100%; margin-top: 10px; margin-bottom: 10px">DEVAM ET <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></button>
            </div>
            <div id="onaybolumu">
               <div id="kisiselbilgileralani">
                  <button id='tarihsaatseckisminageridon' style='width:200px;border-radius:60px' class='btn btn-primary'><< GERİ DÖN</button>
                  <p style='font-size:20px; font-weight:bold'>
                  <p style="font-size:20px; font-weight: bold">Kişisel Bilgiler ve Onay</p>
                  @if(!Auth::check())
                  <div class="form-group" style="margin-bottom: 20px;height: 40px">
                     <div style="width: 60%;float: left;">
                     <input type="tel" maxlength="11" minlength="11" id="cep_telefon" name="cep_telefon" placeholder="05XXXXXXXXX" pattern="05[0-9]{9}" value="05">
                     </div>
                     <div style="width: 30%;float: left;margin-left:10px;margin-top:2%;">
                        <button id="sifregonder" class="btn btn-primary small btn-rounded">Gönder</button> 
                     </div>
                  </div>
                  <div id="hosgeldinizbildirimalani">
                  </div>
                  <div id="sifrealaniregister">
                  </div>
                  <div id="epostahata"></div>
                  @else
                  <div class="form-group" style="margin-bottom: 20px">
                     <label>E-posta adresiniz</label>
                     <input type="email" disabled id="eposta" name="eposta" value="{{Auth::user()->email}}">
                  </div>
                  <div class="form-group" style="margin-bottom: 20px">
                     <label>Cep Telefonu</label>
                     <input type="number" disabled id="ceptelefon" name="ceptelefon" value="{{Auth::user()->cep_telefon}}">
                  </div>
                  <button id="randevuonayla_auth" class="btn btn-primary width-100 btn-rounded" style="width:100%; margin-top: 10px; margin-bottom: 10px">DEVAM ET <i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></button>
                  @endif
               </div>
               <div id="randevudokumu">
                  <div class="col-md-12" style="text-align: center;">
                     <span class="randevuonaybaslik">Randevu Onayı</span>
                  </div>
                  <form id="randevuonayformu" method="POST">
                     {!! csrf_field() !!}
                     <input type="hidden" id="onesignalid" name="onesignalid">
                     <table class="randevuozetonay">
                        <tr>
                           <td style="width: 190px">Salon adı : </td>
                           <td>
                              <div class="col-md-12">
                                 <input type="hidden" name="salonno" value="{{$salon->id}}">{{$salon->salon_adi}}
                              </div>
                           </td>
                           <td colspan="2" rowspan="2" >
                              <div class="col-md-12">
                                 Seçilen Personeller
                              </div>
                              <div class="col-md-12">
                                 <div id="secilenpersoneldokumu">
                                 </div>
                              </div>
                           </td>
                        </tr>
                        <tr>
                           <td>Seçilen hizmetler : </td>
                           <td>
                              <div class="col-md-12">
                                 <div id="secilenhizmetdokumu">
                                 </div>
                              </div>
                           </td>
                           <td>
                           </td>
                           <td>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="2">
                              <div style="position:relative;float:left;border:2px solid #FF4E00; border-radius: 60px;padding:0 10px 0 10px">
                                 <span style="float:left"> Tarih&nbsp;&nbsp;:&nbsp;&nbsp;</span>
                                 <div id="randevutarihidokumu" style="float: left;">
                                 </div>
                              </div>
                           </td>
                           <td colspan="2">
                              <div style="position:relative;float:left;border:2px solid #FF4E00; border-radius: 60px;padding:0 10px 0 10px">
                                 <span style="float:left">Saat&nbsp;&nbsp;:&nbsp;&nbsp;</span>
                                 <div id="randevusaatidokumu" style="float: left;">
                                 </div>
                              </div>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="4">
                              <textarea name="randevunotu" style="width:100%;height: 100px;" placeholder="Randevu için notunuz..."></textarea>
                           </td>
                        </tr>
                        <tr>
                           <td colspan="4"><input type="checkbox" checked id="gizlilikkosulukabul"> <a href="/kullanim-ve-gizlik-kosullari" target="_blank"> Kullanım ve gizlilik koşulları </a>sayfasını okudum ve kabul ediyorum  </td>
                        </tr>
                        <tr>
                           <td colspan="4">Yukarıda detayları listelenen randevunuzu onaylamak istiyor musunuz? 
                           </td>
                        </tr>
                        <tr>
                           <td colspan=4>
                              <button type="button" id="randevuonaylabutton" class="btn btn-success btn-rounded" style="width:100%; float: left;">Evet</button> &nbsp;&nbsp;
                           </td>
                           <td>
                           </td>
                        </tr>
                     </table>
                     <div id="randevuonaybildirim" class="btn btn-success btn-rounded" style="width: 100%; text-align: center;"> </div>
                  </form>
               </div>
            </div>
         </div>
         <div class="col-lg-4" id="randevuozetbolumu">
            <div class="secilenhizmetlertablo">
               <span class="baslik">Randevu Özeti</span>
               <form id="randevuozeti" method="get">
                  {!! csrf_field() !!}
                  <input type="hidden" name="isletmeno"  id="isletmeno" value="{{$salon->id}}">
                  <table class="randevuozet">
                     <tr style="border-bottom: 1px solid #e4e4e2">
                        <td>
                           Hizmetler
                        </td>
                        <td>
                           <div id="secilenhizmetlistebos">
                              Henüz hizmet seçmediniz... 
                           </div>
                           <div id="secilenhizmetliste" >
               </form>
               </td>
               </tr>
               <tr style="border-bottom: 1px solid #e4e4e2">
               <td>
               Personeller
               </td>
               <td>
               <div id="personellistebos">
               Henüz personel seçmediniz... 
               </div>  
               <div id="personelliste">
               </div>
               </td>
               </tr>
               <tr style="border-bottom: 1px solid #e4e4e2">
               <td>
               Tarih ve Saat
               </td>
               <td>
               <div id="tarihsaatbos">
               Henüz tarih saat seçmediniz... 
               </div>
               <div id="tarihsaat">
               </div>
               </td>
               </tr>
               <tr>
               <td colspan="2">
               </td>
               </tr>
               </table>
               </form>
               </div>
               <div style="position: relative;float: left;width: 100%;margin-top: 20px; text-align: center;display: none;">
                  @if(Auth::check())
                  <button id="favorilereekle" class="btn btn-light" style="background-color: transparent;border:none; font-size: 30px" title="Favorilerime Ekle"> <img src="{{secure_asset('public/img/2.png')}}" width="60" height="50" alt="Favorilere Ekle"></button>
                  @endif
                  @if($salon->facebook_sayfa != null ||$salon->facebook_sayfa != '')
                  <div id="fb-root"></div>
                  <script>(function(d, s, id) {
                     var js, fjs = d.getElementsByTagName(s)[0];
                     if (d.getElementById(id)) return;
                     js = d.createElement(s); js.id = id;
                     js.src = 'https://connect.facebook.net/tr_TR/sdk.js#xfbml=1&version=v3.1';
                     fjs.parentNode.insertBefore(js, fjs);
                     }(document, 'script', 'facebook-jssdk'));
                  </script>
                  <div class="fb-like likebutton" style="float: left; width: 70px;margin-right: 0" data-href="{{$salon->facebook_sayfa}}" data-layout="button_count" data-action="like" data-size="large" data-show-faces="true" data-share="false">
                  </div>
                  @endif
               </div>
            </div>
         </div>
         <div class="row">
            <div id="hata"></div>
         </div>
         <div class="row">
            <!--============ Listing Detail =============================================================-->
            <div class="col-md-12">
               <div class="gallery-carousel-thumbs owl-carousel">
                  @foreach($salongorselleri as $carouselgorsel)
                  @if($carouselgorsel->salon_id== $salon->id)
                  <a class="owl-thumb active-thumb background-image" onclick="buyut('{{secure_asset($carouselgorsel->salon_gorseli)}}');">
                  <img src="{{secure_asset($carouselgorsel->salon_gorseli)}}" name="salon_gorselleri" alt="Salon Görseli" data-src="{{secure_asset($carouselgorsel->salon_gorseli)}}" />
                  </a>
                  @endif
                  @endforeach
               </div>
               @if($aramaterimimeta != '' || $aramaterimimeta != null)
               <section style="text-align: center;">
                  Etiketler : {{$aramaterimimeta}}
               </section>
               @endif
               <!--end Gallery Carousel-->
               <!--Description-->
               @if($salon->aciklama != null ||$salon->aciklama != '')
               <section>
                  <p class="salondetaybasliklar">Açıklama</p>
                  <p>
                     {{$salon->aciklama}}
                  </p>
               </section>
               @endif
               <section>
                  <div class="row">
                     <div class="col-md-12" style="margin:30px 0 30px 0">
                        <p class="salondetaybasliklar">Adres</p>
                        <p>{{$salon->adres}}</p>
                        @if($salon->maps_iframe != null ||$salon->maps_iframe != '')
                        <iframe src="{{$salon->maps_iframe}}" style="width:100%; height: 300px;border:2px solid #FF4E00;border-radius: 4px"  frameborder="0" style="border:0" allowfullscreen></iframe>
                        @endif
                     </div>
                  </div>
                  @if($saloncalismasaatleri->count()>0 && $personeller->count()>0)       
                  <div class="row">
                     <div class="col-md-6" style="padding:20px;">
                        <p class="salondetaybasliklar">Çalışma Saatleri</p>
                        @foreach($saloncalismasaatleri as $calismasaatleri)
                        @if($calismasaatleri->salon_id == $salon->id)
                        <div class="row" style="min-height: 35px; padding-top: 5px; border-top: 1px solid rgba(0,0,0,.1);">
                           <div style="width: 50%;padding-left: 20px">
                              @if($calismasaatleri->haftanin_gunu == 1) Pazartesi
                              @elseif($calismasaatleri->haftanin_gunu == 2) Salı
                              @elseif($calismasaatleri->haftanin_gunu == 3) Çarşamba
                              @elseif($calismasaatleri->haftanin_gunu == 4) Perşembe
                              @elseif($calismasaatleri->haftanin_gunu == 5) Cuma
                              @elseif($calismasaatleri->haftanin_gunu == 6) Cumartesi
                              @elseif($calismasaatleri->haftanin_gunu == 7) Pazar
                              @endif
                           </div>
                           <div style="width: 50%;padding-right: 20px">
                              @if($calismasaatleri->calisiyor==1)
                              {{date('H:i',strtotime($calismasaatleri->baslangic_saati))}} - {{date('H:i',strtotime($calismasaatleri->bitis_saati))}}
                              @else
                              Kapalı
                              @endif
                           </div>
                        </div>
                        @endif
                        @endforeach
                     </div>
                     <div class="col-md-6" style="padding:20px;">
                        <p class="salondetaybasliklar">Personeller</p>
                        <div class="row" style="padding:0 20px 0 20px">
                           @foreach($personeller as $salonpersonelleri)
                           @if($salonpersonelleri->salon_id == $salon->id)
                           <div style="font-size: 12px; width: 25%">
                              <div class="author small" style="position: relative;">
                                 <div class="author-image" style="float: none">
                                    <div class="background-image image-wrapper">
                                       @if(\App\IsletmeYetkilileri::where('personel_id',$salonpersonelleri->id)->value('profil_resim') == null || \App\IsletmeYetkilileri::where('personel_id',$salonpersonelleri->id)->value('profil_resim') == '')
                                       @if($salonpersonelleri->cinsiyet==0)
                                       <img src="{{secure_asset('public/img/author0.jpg')}}" alt="Profil resmi">
                                       @else
                                       <img src="{{secure_asset('public/img/author1.jpg')}}" alt="Profil resmi">
                                       @endif
                                       @else
                                       <img src="{{\App\IsletmeYetkilileri::where('personel_id',$salonpersonelleri->id)->value('profil_resim')}}" alt="Profil resmi">
                                       @endif
                                    </div>
                                 </div>
                              </div>
                              {{\App\IsletmeYetkilileri::where('personel_id',$salonpersonelleri->id)->value('name')}} 
                           </div>
                           @endif
                           @endforeach
                        </div>
                     </div>
                  </div>
                  @endif
               </section>
               <section>
                  <p class="salondetaybasliklar">Müşteri Yorumları</p>
                  <div class="comments">
                     <div class="row">
                        <div class="col-md-6">
                           @if(Auth::check() && \App\SalonYorumlar::where('salon_id',$salon->id)->where('user_id',Auth::user()->id)->count() ==0)
                           <form id="salonyorumyap" action="{{route('yorumyap')}}" method="get">
                              <div class="form-group">
                                 <input type="hidden" value="{{$salon->id}}" name="yorum_isletmeid">
                                 <label>Puanlama</label>
                                 <input required type="radio" value="1" id="puanlama1" name="puanlama">
                                 <label for="puanlama1">
                                    <div class="rating" data-rating="1"></div>
                                 </label>
                                 <input required type="radio" value="2" id="puanlama2" name="puanlama">
                                 <label for="puanlama2">
                                    <div class="rating" data-rating="2"></div>
                                 </label>
                                 <input required type="radio" value="3" id="puanlama3" name="puanlama">
                                 <label for="puanlama3">
                                    <div class="rating" data-rating="3"></div>
                                 </label>
                                 <input required type="radio" value="4" id="puanlama4" name="puanlama">
                                 <label for="puanlama4">
                                    <div class="rating" data-rating="4"></div>
                                 </label>
                                 <input checked required type="radio" value="5" id="puanlama5" name="puanlama">
                                 <label for="puanlama5">
                                    <div class="rating" data-rating="5"></div>
                                 </label>
                                 <textarea class="form-control" required style="border-radius: 0" type="text" placeholder="Yorumunuzu Yazın" name="yorumtext_yorum" id="#yorumtext_yorum"></textarea>
                                 <button type="submit" class="btn btn-primary" style="margin-top:10px">Gönder</button>
                              </div>
                           </form>
                           @endif
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6" style="float: left;">
                           <div class="float-left">
                              @if($salonpuanlar->count()>0)
                              <div class="rating" data-rating="{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}">
                              </div>
                              @else
                              <div class="rating" data-rating="0"></div>
                              @endif
                           </div>
                           {{$salonyorumlar->count()}} Yorum, 
                           {{$salonpuanlar->count()}}
                           Puanlama
                        </div>
                        <div class="col-md-6" style="float:left;text-align: right;">
                           @if($salonpuanlar->count()>0)
                           [{{$salonpuanlar->sum('puan')/$salonpuanlar->count()}}/5]
                           @else
                           [0/5]
                           @endif
                        </div>
                     </div>
                     @foreach($salonyorumlar as $salonyorum)
                     <div class="comment">
                        <div class="author">
                           <a href="#" class="author-image">
                              <div class="background-image">
                                 @if(\App\User::where('id',$salonyorum->user_id)->value('profil_resim')==null || \App\User::where('id',$salonyorum->user_id)->value('profil_resim')=='')
                                 @if(\App\User::where('id',$salonyorum->user_id)->value('cinsiyet')==0)
                                 <img src="{{secure_asset('public/img/author0.jpg')}}" alt="Profil resmi">
                                 @else
                                 <img src="{{secure_asset('public/img/author1.jpg')}}" alt="Profil resmi">
                                 @endif
                                 @else
                                 <img src="{{secure_asset(\App\User::where('id',$salonyorum->user_id)->value('profil_resim'))}}" alt="Profil resmi">
                                 @endif
                              </div>
                           </a>
                           <div class="author-description">
                              <p> 
                                 {{\App\User::where('id',$salonyorum->user_id)->value('name')}} 
                              </p>
                              <div class="meta">
                                 <span>
                                 @if(date('d')==date('d',strtotime($salonyorum->updated_at)))
                                 Bugün {{date('H:i',strtotime($salonyorum->updated_at))}}
                                 @elseif(date('d')-1 == date('d',strtotime($salonyorum->updated_at)))
                                 Dün {{date('H:i',strtotime($salonyorum->updated_at))}}
                                 @else
                                 {{date('d.m.Y H:i',strtotime($salonyorum->updated_at))}}
                                 @endif
                                 </span>
                              </div>
                              <!--end meta-->
                              <p>
                                 {{$salonyorum->yorum}}
                              </p>
                              <p>
                                 @if(\App\SalonPuanlar::where('user_id',$salonyorum->user_id)->where('salon_id',$salon->id)->value('puan') > 0)
                              <div class="rating" data-rating="{{\App\SalonPuanlar::where('user_id',$salonyorum->user_id)->where('salon_id',$salon->id)->value('puan')}}"></div>
                              @else
                              <div class="rating" data-rating="0"></div>
                              @endif
                              </p>
                           </div>
                           <!--end author-description-->
                        </div>
                        <!--end author-->
                     </div>
                     @endforeach
                     <!--end comment-->
                  </div>
               </section>
               <!--end Details and Locations-->
               <!--Features-->
               <section style="display: none">
                  <div class="row">
                     <div class="col-md-6" style="text-align: center;">
                        @if($aramaterimlerihepsi)
                        @foreach($aramaterimlerihepsi as $key => $value)
                        <?php $i = number_format(sizeof($aramaterimlerihepsi)/2); ?>
                        @for($j=1; $j<=$i;$j++) 
                        @if($j-1 === $key)
                        @if($j===1)
                        <p>{{$value}}</p>
                        @else
                        <h2>{{$value}}</h2>
                        @endif
                        @endif
                        @endfor
                        @endforeach
                        @endif
                     </div>
                     <div class="col-md-6" style="text-align: center;">
                        @if($aramaterimlerihepsi)
                        @foreach($aramaterimlerihepsi as $key => $value)
                        <?php $i = number_format(sizeof($aramaterimlerihepsi)/2); ?>
                        @for($j=$i+1; $j<=sizeof($aramaterimlerihepsi);$j++) 
                        @if($j-1 === $key)
                        @if($key != 5)
                        <h2>{{$value}}</h2>
                        @else
                        <h3>{{$value}}</h3>
                        @endif
                        @endif
                        @endfor
                        @endforeach
                        @endif
                     </div>
                  </div>
               </section>
               <!--End Author-->
            </div>
            <!--============ End Listing Detail =========================================================-->
            <!--============ Sidebar ====================================================================-->
         </div>
         <!--============ End Sidebar ================================================================-->
      </div>
   </div>
   <!--end container-->
</section>
<div id="myModal2" class="modalimage">
   <span class="modalimageclose">&times;</span>
   <img class="modalimage-content" id="img01">
   <div id="caption"></div>
</div>
<script>
   function buyut(imgsrc){
       
         var modal2 = document.getElementById('myModal2');
   
   
       var modalImg = document.getElementById("img01");
       var captionText = document.getElementById("caption"); 
   
       modal2.style.display = "block";
       modalImg.src = imgsrc; 
       var span = document.getElementsByClassName("modalimageclose")[0];
   
   
       span.onclick = function() { 
           modal2.style.display = "none";
       }
   }
   var acc = document.getElementsByClassName("accordion");
   var i;
   
   for (i = 0; i < acc.length; i++) {
   acc[i].addEventListener("click", function() {
   this.classList.toggle("active");
   var panel = this.nextElementSibling;
   if (panel.style.display === "block") {
   panel.style.display = "none";
   } else {
   panel.style.display = "block";
   }
   });
   }
   
   
                                              
                                           
</script>
<!--end block-->
@endsection