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
               <span class="randevuadimbaslik">Personel <br>Şube Seç<span>
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
               <span class="randevuadimbaslik">Personel<br>Şube<span>
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
                  @foreach($hizmetbolumleri as $hizmetbolumu)
                  @if($hizmetbolumleri->count() == 2)
                  @if($hizmetbolumu->bolum == 0)
                  <li class="nav-item">
                     <a class="nav-link active" id="one-tab" data-toggle="tab" href="#bayan" role="tab" aria-controls="bayan" aria-expanded="true">Bayan Bölümü</a>
                  </li>
                  @else
                  <li class="nav-item">
                     <a class="nav-link" id="one-tab" data-toggle="tab" href="#bay" role="tab" aria-controls="bay" aria-expanded="true">Bay Bölümü</a>
                  </li>
                  @endif
                  @else
                  @if($hizmetbolumu->bolum == 0)
                  <li class="nav-item">
                     <a class="nav-link active" id="one-tab" data-toggle="tab" href="#bayan" role="tab" aria-controls="bayan" aria-expanded="true">Bayan Bölümü</a>
                  </li>
                  @else
                  <li class="nav-item">
                     <a class="nav-link active" id="one-tab" data-toggle="tab" href="#bay" role="tab" aria-controls="bay" aria-expanded="true">Bay Bölümü</a>
                  </li>
                  @endif
                  @endif
                  @endforeach
               </ul>
               <input type="hidden"  id="salonid" value="{{$salon->id}}">
               <div class="tab-content" id="myTabContent">
                  <div class="tab-pane fade show active" id="bayan" role="tabpanel" aria-labelledby="bayan-tab">
                     @foreach($salonsunulanhizmetler_kategori as $key=>$kategori_baslik)
                     @if($kategori_baslik->bolum ==0)
                     @if($key==0)
                     <button class="accordion active">{{$kategori_baslik->hizmet_kategorisi->hizmet_kategorisi_adi}} Hizmetleri
                     </button>
                     <div class="panel_accordion" style="display: block;">
                        @else
                        <button class="accordion">{{$kategori_baslik->hizmet_kategorisi->hizmet_kategorisi_adi}} Hizmetlerinbvnvh
                        </button>
                        <div class="panel_accordion">
                           @endif
                           <table class="hizmettablo">
                              @foreach($salonsunulanhizmetler as $hizmetfiyatlistesi)
                              @if($hizmetfiyatlistesi->hizmet_kategori_id == $kategori_baslik->hizmet_kategori_id && $hizmetfiyatlistesi->bolum == $kategori_baslik->bolum)
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
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#ff7033; opacity: 1; text-align: center">Bilgi Alınız</p>
                                    @else
                                    @if($hizmetfiyatlistesi->baslangic_fiyat == $hizmetfiyatlistesi->son_fiyat)
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#ff7033; opacity: 1; text-align: center">
                                       {{$hizmetfiyatlistesi->baslangic_fiyat}} <span class="simge-tl">&#8378;</span> 
                                    </p>
                                    @else
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#ff7033; opacity: 1; text-align: center">   {{$hizmetfiyatlistesi->baslangic_fiyat}} ~ {{$hizmetfiyatlistesi->son_fiyat}} <span class="simge-tl">&#8378;</span>  
                                    </p>
                                    @endif
                                    @endif
                                 </td>
                              </tr>
                              @endif
                              @endforeach
                           </table>
                        </div>
                        @endif
                        @endforeach
                        <!-- <a href="#" class="btn btn-info" style="width:100%">FİYAT AL</a> -->
                     </div>
                     <div class="tab-pane fade" id="bay" role="tabpanel" aria-labelledby="bay-tab">
                        <?php $j=0;?>
                        @foreach($salonsunulanhizmetler_kategori as $kategori_baslik)
                        @if($kategori_baslik->bolum ==1)
                        @if($j == 0)
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
                                 @if($hizmetfiyatlistesi->hizmet_kategori_id == $kategori_baslik->hizmet_kategori_id && $hizmetfiyatlistesi->bolum == $kategori_baslik->bolum)
                                 <tr>
                                    <td style="width: 50px">
                                       <label class="checkboxcontainer">
                                       <input name="randevuhizmet[]" id="{{'hizmet-'.$hizmetfiyatlistesi->hizmetler->id}}" type="checkbox" name="type" value="{{$hizmetfiyatlistesi->hizmetler->id}}" required>
                                       <span class="checkmark">
                                       </label>
                                    </td>
                                    <td>
                                       {{$hizmetfiyatlistesi->hizmetler->hizmet_adi}}
                                    </td>
                                    <td>
                                       @if($hizmetfiyatlistesi->baslangic_fiyat==null&& $hizmetfiyatlistesi->son_fiyat == null)
                                       <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#ff7033; opacity: 1; text-align: center">Bilgi Alınız</p>
                                       @else
                                       @if($hizmetfiyatlistesi->baslangic_fiyat == $hizmetfiyatlistesi->son_fiyat)
                                       <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#ff7033; opacity: 1; text-align: center">
                                          {{$hizmetfiyatlistesi->baslangic_fiyat}} <span class="simge-tl">&#8378;</span> 
                                       </p>
                                       @else
                                       <p class="btn btn-primary small btn-rounded" style="width:100%;  background-color:#ff7033;opacity: 1; text-align: center">   {{$hizmetfiyatlistesi->baslangic_fiyat}} ~ {{$hizmetfiyatlistesi->son_fiyat}} <span class="simge-tl">&#8378;</span>  
                                       </p>
                                       @endif
                                       @endif
                                    </td>
                                 </tr>
                                 @endif
                                 <?php $j++; ?>
                                 @endforeach
                           </div>
                           </table>
                        </div>
                        @endif
                        @endforeach
                        <!--  <a href="#" class="btn btn-info" style="width:100%">FİYAT AL</a>-->
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
                     <div style="width: 70%;float: left;">
                        <input type="text" maxlength="10" id="cep_telefon" name="cep_telefon" placeholder="Cep Telefonu (Başında 0 olmadan 5XXXXXXXXX şeklinde)">
                     </div>
                     <div style="width: 30%;float: left;">
                        <button id="sifregonder" class="btn btn-primary small btn-rounded"> ->> Gönder</button> 
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
                     <table class="randevuozetonay">
                        <tr>
                           <td style="width: 190px">Şube : </td>
                           <td>
                              <div class="col-md-12">
                                 <input type="hidden" name="salonno" value="{{$salon->id}}">
                                 <input type="hidden" name="subeno">
                                 <span id="secilensube"></span>
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
                           </div>
                        </td>
                     </tr>
                     <tr style="border-bottom: 1px solid #e4e4e2">
                        <td>
                           Şube &  Personeller
                        </td>
                        <td>
                           <div id="personellistebos">
                              Henüz şube ve personel seçmediniz... 
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
                  <div class="col-md-12">
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
                                 <div class="background-image">
                                    @if($salonpersonelleri->profil_resmi == null || $salonpersonelleri->profil_resmi == '')
                                    @if($salonpersonelleri->cinsiyet==0)
                                    <img src="{{secure_asset('public/img/author0.jpg')}}" alt="Profil resmi">
                                    @else
                                    <img src="{{secure_asset('public/img/author1.jpg')}}" alt="Profil resmi">
                                    @endif
                                    @else
                                    <img src="{{secure_asset($salonpersonelleri->profil_resmi)}}" alt="Profil resmi">
                                    @endif
                                 </div>
                              </div>
                           </div>
                           {{$salonpersonelleri->personel_adi}} 
                        </div>
                        @endif
                        @endforeach
                     </div>
                  </div>
               </div>
               @endif
            </section>
            <!--end Description-->
          
            
            </div>
         
         </div>
         
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