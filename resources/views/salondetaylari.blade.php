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

   @php
       $_aktifCark = \App\CarkifelekSistemi::where('salon_id', $salon->id)->where('aktifmi', 1)->first();
       $_cark_dilim_sayisi = $_aktifCark ? \App\CarkifelekDilimleri::where('cark_id', $_aktifCark->id)->count() : 0;
       // Salon acik mi? Bugunun calisma saati
       $_bugun = (int) date('N');
       $_bugunCalisma = $saloncalismasaatleri->first(function($c) use ($_bugun) {
           return $c->haftanin_gunu == $_bugun;
       });
       $_simdiAcik = false;
       if ($_bugunCalisma && $_bugunCalisma->calisiyor == 1) {
           $_simdiAcik = (date('H:i') >= date('H:i', strtotime($_bugunCalisma->baslangic_saati))
                       && date('H:i') <= date('H:i', strtotime($_bugunCalisma->bitis_saati)));
       }
       $_bugunMetin = $_bugunCalisma && $_bugunCalisma->calisiyor == 1
           ? date('H:i', strtotime($_bugunCalisma->baslangic_saati)).' - '.date('H:i', strtotime($_bugunCalisma->bitis_saati))
           : 'Bugün Kapalı';
       $_ortPuan = $salonpuanlar->count() > 0 ? number_format($salonpuanlar->sum('puan') / $salonpuanlar->count(), 1) : null;
       $_hizmetSayisi = $salonsunulanhizmetler ? $salonsunulanhizmetler->where('aktif', 1)->count() : 0;
   @endphp

   {{-- =========================== SALON LANDING HERO =========================== --}}
   <section class="slp-hero">
      <div class="slp-hero__scrim"></div>
      <div class="slp-hero__inner container">
         <div class="slp-hero__left">
            <span class="slp-hero__eyebrow"><i class="fa fa-bolt"></i> {{$salon->salon_turu->salon_turu_adi ?? 'Güzellik & Bakım'}}</span>
            <h1 class="slp-hero__title">{{$salon->salon_adi}}</h1>
            <p class="slp-hero__sub">
               @if(!empty($salon->meta_description))
                  {{ \Illuminate\Support\Str::limit($salon->meta_description, 160) }}
               @else
                  Profesyonel ekibimiz ve modern hizmet anlayışımızla sizi en iyi şekilde ağırlamak için buradayız.
               @endif
            </p>
            <div class="slp-hero__meta">
               @if($_ortPuan)
                  <span class="slp-hero__chip"><i class="fa fa-star"></i> {{$_ortPuan}} / 5 ({{$salonyorumlar->count()}} yorum)</span>
               @endif
               <span class="slp-hero__chip"><i class="fa fa-map-marker"></i> {{ $salon->ilce->ilce_adi ?? 'Türkiye' }}</span>
               @if($_simdiAcik)
                  <span class="slp-hero__chip slp-hero__chip--open"><i class="fa fa-circle"></i> Şu an Açık · {{$_bugunMetin}}</span>
               @else
                  <span class="slp-hero__chip"><i class="fa fa-clock-o"></i> {{$_bugunMetin}}</span>
               @endif
            </div>
            <div class="slp-hero__cta">
               <a href="#randevu-al" class="slp-btn slp-btn--primary" data-slp-open>
                  <i class="fa fa-calendar-check-o"></i> Randevu Al
               </a>
               @if(!empty($salon->telefon_1))
                  <a href="tel:{{$salon->telefon_1}}" class="slp-btn slp-btn--ghost">
                     <i class="fa fa-phone"></i> Hemen Ara
                  </a>
               @endif
            </div>
         </div>
         <div class="slp-hero__right">
            <div class="slp-hero__card">
               <div class="slp-hero__card-head">
                  @if(!empty($salon->logo))
                     <div class="slp-hero__card-logo"><img src="{{secure_asset($salon->logo)}}" alt="{{$salon->salon_adi}}"></div>
                  @endif
                  <div>
                     <h3 class="slp-hero__card-name">{{$salon->salon_adi}}</h3>
                     <div class="slp-hero__card-status">
                        @if($_simdiAcik)
                           <span class="slp-dot"></span> Açık · {{$_bugunMetin}}
                        @else
                           <span class="slp-dot" style="background:#F59E0B; box-shadow: 0 0 10px #F59E0B;"></span> {{$_bugunMetin}}
                        @endif
                     </div>
                  </div>
               </div>
               <div class="slp-hero__card-row">
                  <i class="fa fa-map-marker"></i>
                  <span>{{ \Illuminate\Support\Str::limit($salon->adres, 90) }}</span>
               </div>
               @if(!empty($salon->telefon_1))
                  <div class="slp-hero__card-row">
                     <i class="fa fa-phone"></i>
                     <span>{{$salon->telefon_1}}</span>
                  </div>
               @endif
               <div class="slp-hero__card-row">
                  <i class="fa fa-users"></i>
                  <span>{{$personeller->count()}} profesyonel personel</span>
               </div>
            </div>
         </div>
      </div>
   </section>

   {{-- =========================== QUICK STATS BAR =========================== --}}
   <div class="slp-stats">
      <div class="slp-stats__grid">
         <div class="slp-stat">
            <i class="fa fa-users"></i>
            <span class="slp-stat__num">{{$personeller->count()}}</span>
            <span class="slp-stat__lbl">Personel</span>
         </div>
         <div class="slp-stat">
            <i class="fa fa-magic"></i>
            <span class="slp-stat__num">{{$_hizmetSayisi}}</span>
            <span class="slp-stat__lbl">Hizmet</span>
         </div>
         <div class="slp-stat">
            <i class="fa fa-star"></i>
            <span class="slp-stat__num">{{$_ortPuan ?? '—'}}</span>
            <span class="slp-stat__lbl">Puan</span>
         </div>
         <div class="slp-stat">
            <i class="fa fa-comments-o"></i>
            <span class="slp-stat__num">{{$salonyorumlar->count()}}</span>
            <span class="slp-stat__lbl">Yorum</span>
         </div>
      </div>
   </div>

   @if($_aktifCark && $_cark_dilim_sayisi >= 2)
   <div class="row" style="margin-top:18px">
      <div class="col-12">
         <a href="{{ url('/cark/'.$salon->id) }}" style="display:block;text-decoration:none;">
            <div style="background:linear-gradient(135deg,#6c5ce7 0%,#a29bfe 50%,#fd79a8 100%);border-radius:16px;padding:16px 22px;color:#fff;display:flex;align-items:center;gap:14px;box-shadow:0 10px 26px rgba(108,92,231,.3);transition:.25s;">
               <div style="font-size:38px;line-height:1;">🎡</div>
               <div style="flex:1;">
                  <div style="font-size:17px;font-weight:800;letter-spacing:-.3px;">Çarkıfelek — Size Özel Ödüller!</div>
                  <div style="font-size:13px;opacity:.92;margin-top:2px;">Onaylanmış randevularınız üzerinden çarkı çevirip puan ve indirim kazanın.</div>
               </div>
               <div style="font-size:20px;">›</div>
            </div>
         </a>
      </div>
   </div>
   @endif

   {{-- ====================== RANDEVU DRAWER BASLANGIC ===================== --}}
   <div class="slp-drawer" id="slpDrawer" role="dialog" aria-modal="true" aria-label="Randevu Oluştur" aria-hidden="true">
      <div class="slp-drawer__backdrop" data-slp-close></div>
      <div class="slp-drawer__panel">
         <button type="button" class="slp-drawer__close" data-slp-close aria-label="Kapat">
            <i class="fa fa-times"></i>
         </button>
         <div class="slp-drawer__body">

   {{-- ======================= LUXE HERO BANNER ========================= --}}
   <div class="lx-hero" id="lxHero">
      @if(!empty($salongorselikapak))
         <div class="lx-hero__bg" style="background-image:url('{{secure_asset($salongorselikapak)}}');"></div>
      @endif
      <div class="lx-hero__grid">
         <div class="lx-hero__left">
            <span class="lx-hero__eyebrow">Online Randevu</span>
            <h2 class="lx-hero__title">{{$salon->salon_adi}} <em>&times;</em> Size Özel</h2>
            <p class="lx-hero__sub">Saniyeler içinde randevunuzu oluşturun. Dilediğiniz hizmeti, personeli ve saati seçin — gerisini biz hallederiz.</p>
            <div class="lx-hero__meta">
               @if($salonpuanlar->count() > 0)
                  <span class="lx-hero__chip"><i class="fa fa-star"></i> {{ number_format($salonpuanlar->sum('puan')/$salonpuanlar->count(), 1) }} / 5</span>
               @endif
               <span class="lx-hero__chip"><i class="fa fa-users"></i> {{$personeller->count()}} Personel</span>
               <span class="lx-hero__chip"><i class="fa fa-map-marker"></i> {{ $salon->ilce->ilce_adi ?? $salon->il->il_adi ?? 'Türkiye' }}</span>
               <span class="lx-hero__chip"><i class="fa fa-shield"></i> Güvenli & Anında Onay</span>
            </div>
         </div>
         <div class="lx-hero__right">
            <div class="lx-progress" id="lxProgress" data-lstep="1">
               <div class="lx-progress__track">
                  <div class="lx-progress__bar" style="width:12.5%"></div>
                  <div class="lx-progress__dots">
                     <span class="lx-progress__dot is-active" data-lxs="1"><span>1</span></span>
                     <span class="lx-progress__dot" data-lxs="2"><span>2</span></span>
                     <span class="lx-progress__dot" data-lxs="3"><span>3</span></span>
                     <span class="lx-progress__dot" data-lxs="4"><span>4</span></span>
                  </div>
               </div>
               <div class="lx-progress__labels">
                  <span class="lx-progress__label is-active" data-lxl="1">Hizmet</span>
                  <span class="lx-progress__label" data-lxl="2">Personel</span>
                  <span class="lx-progress__label" data-lxl="3">Tarih &amp; Saat</span>
                  <span class="lx-progress__label" data-lxl="4">Onay</span>
               </div>
            </div>
         </div>
      </div>
   </div>
   {{-- ======================= /LUXE HERO =============================== --}}

   <div class="row rdv-luxe-bookingrow" style="margin-top:20px">
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
                                <button type="button" class="accordion active" data-kategori-id="{{$kategori_baslik->hizmet_kategori_id}}">{{$kategori_baslik->hizmet_kategorisi->hizmet_kategorisi_adi}} Hizmetleri
                                </button>
                                <div class="panel_accordion" style="display: block;">
                            @else
                                <button type="button" class="accordion" data-kategori-id="{{$kategori_baslik->hizmet_kategori_id}}">{{$kategori_baslik->hizmet_kategorisi->hizmet_kategorisi_adi}} Hizmetleri
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
                                    <p class="btn btn-primary small btn-rounded" style="width:100%; background-color:#5C008E; opacity: 1; text-align: center">Bilgi Alınız</p>
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
            <button id="personelsecimadiminagec"  class="btn btn-primary width-100 btn-rounded" style="width:100%; margin-top: 10px; margin-bottom: 10px">DEVAM ET <i class="fa fa-chevron-right"></i></button>
            </div> 
            <div id="personelsecimbolumu" style="padding-top:10px">
            </div>
            <div id="tarihsaatsecimbolumu">
               <button id='personelseckisminageridon' style='width:auto' class='btn btn-primary'><i class="fa fa-arrow-left"></i> Geri Dön</button>
               <p style='font-size:20px; font-weight:bold; margin-top:15px'>Tarih Seçimi</p>
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
               <button id="onayadiminagec" class="btn btn-primary width-100 btn-rounded" style="width:100%; margin-top: 10px; margin-bottom: 10px">DEVAM ET <i class="fa fa-chevron-right"></i></button>
            </div>
            <div id="onaybolumu">
               <div id="kisiselbilgileralani">
                  <button id='tarihsaatseckisminageridon' style='width:auto' class='btn btn-primary'><i class="fa fa-arrow-left"></i> Geri Dön</button>
                  <p style="font-size:20px; font-weight: bold; margin-top:15px">Kişisel Bilgiler ve Onay</p>
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
                  <button id="randevuonayla_auth" class="btn btn-primary width-100 btn-rounded" style="width:100%; margin-top: 10px; margin-bottom: 10px">DEVAM ET <i class="fa fa-chevron-right"></i></button>
                  @endif
               </div>
               <div id="randevudokumu">
                  <div class="col-md-12" style="text-align: center;">
                     <span class="randevuonaybaslik">Randevu Onayı</span>
                  </div>
                  <form id="randevuonayformu" method="POST">
                     {!! csrf_field() !!}
                     <input type="hidden" id="onesignalid" name="onesignalid">
                     <input type="hidden" name="salonno" value="{{$salon->id}}">
                     <div class="randevuozetonay">
                        <div class="rdv-onay-grid">
                           <div class="rdv-onay-field">
                              <span class="rdv-onay-label">Salon adı</span>
                              <div class="rdv-onay-value">{{$salon->salon_adi}}</div>
                           </div>
                           <div class="rdv-onay-field">
                              <span class="rdv-onay-label">Seçilen hizmetler</span>
                              <div class="rdv-onay-value" id="secilenhizmetdokumu"></div>
                           </div>
                           <div class="rdv-onay-field rdv-onay-field--full">
                              <span class="rdv-onay-label">Seçilen personeller</span>
                              <div class="rdv-onay-value rdv-onay-personel" id="secilenpersoneldokumu"></div>
                           </div>
                        </div>
                        <div class="rdv-onay-datetime">
                           <div class="rdv-onay-pill">
                              <span class="rdv-onay-pill-label">Tarih</span>
                              <div class="rdv-onay-pill-value" id="randevutarihidokumu"></div>
                           </div>
                           <div class="rdv-onay-pill">
                              <span class="rdv-onay-pill-label">Saat</span>
                              <div class="rdv-onay-pill-value" id="randevusaatidokumu"></div>
                           </div>
                        </div>
                        <textarea name="randevunotu" placeholder="Randevu için notunuz..."></textarea>
                        <label class="rdv-onay-check">
                           <input type="checkbox" checked id="gizlilikkosulukabul">
                           <span><a href="/kullanim-ve-gizlik-kosullari" target="_blank">Kullanım ve gizlilik koşulları</a> sayfasını okudum ve kabul ediyorum</span>
                        </label>
                        <p class="rdv-onay-confirm">Yukarıda detayları listelenen randevunuzu onaylamak istiyor musunuz?</p>
                        <button type="button" id="randevuonaylabutton" class="btn btn-success btn-rounded">Evet</button>
                     </div>
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
                           <div id="secilenhizmetliste" ></div>
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

         </div>{{-- /.slp-drawer__body --}}
      </div>{{-- /.slp-drawer__panel --}}
   </div>{{-- /.slp-drawer --}}

   {{-- ============ FLOATING "RANDEVU AL" BUTTON (FAB) ================ --}}
   <a href="#randevu-al" class="slp-fab" data-slp-open aria-label="Randevu Al">
      <span class="slp-fab__icon"><i class="fa fa-calendar-check-o"></i></span>
      <span>Randevu Al</span>
   </a>

         <div class="row">
            <div id="hata"></div>
         </div>

         {{-- ================= HAKKIMIZDA / STORY ================= --}}
         @if(!empty($salon->aciklama))
         <section class="slp-section">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Hakkımızda</span>
               <h2 class="slp-section__title">Sadece bir salon değil, bir deneyim.</h2>
               <p class="slp-section__sub">Profesyonel ekibimiz, modern anlayışımız ve kişisel dokunuşlarımızla sizi ağırlıyoruz.</p>
            </div>
            <div class="slp-story-grid">
               <div class="slp-story__text">
                  <p>{!! nl2br(e($salon->aciklama)) !!}</p>
               </div>
               <div class="slp-story__image">
                  @if(!empty($salongorselikapak))
                     <img src="{{secure_asset($salongorselikapak)}}" alt="{{$salon->salon_adi}}" loading="lazy">
                  @elseif($salongorselleri->where('salon_id',$salon->id)->count())
                     <img src="{{secure_asset($salongorselleri->where('salon_id',$salon->id)->first()->salon_gorseli)}}" alt="{{$salon->salon_adi}}" loading="lazy">
                  @endif
                  <div class="slp-story__badge">
                     <strong>{{$_ortPuan ?? '5.0'}}</strong>
                     <span>{{$salonyorumlar->count()}} Yorum</span>
                  </div>
               </div>
            </div>
         </section>
         @endif

         {{-- ================= HIZMETLER ================= --}}
         @if($salonsunulanhizmetler_kategori && $salonsunulanhizmetler_kategori->count())
         <section class="slp-section slp-section--alt">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Hizmetler</span>
               <h2 class="slp-section__title">Profesyonel Hizmetlerimiz</h2>
               <p class="slp-section__sub">Sizi en iyi şekilde ağırlamak için geniş hizmet yelpazemiz.</p>
            </div>
            @php
               $_kategoriIconMap = [
                   'saç' => 'fa-magic',
                   'makyaj' => 'fa-paint-brush',
                   'tırnak' => 'fa-hand-peace-o',
                   'cilt' => 'fa-heart',
                   'kaş' => 'fa-eye',
                   'masaj' => 'fa-spa',
                   'epilasyon' => 'fa-bolt',
                   'gelin' => 'fa-diamond',
                   'sakal' => 'fa-user',
                   'bay' => 'fa-male',
                   'bayan' => 'fa-female',
               ];
            @endphp
            <div class="slp-services-grid">
               @foreach($salonsunulanhizmetler_kategori as $kat)
                  @php
                     $_katAdi = $kat->hizmet_kategorisi->hizmet_kategorisi_adi ?? '';
                     $_katSayi = $salonsunulanhizmetler->where('hizmet_kategori_id', $kat->hizmet_kategori_id)->where('aktif', 1)->count();
                     $_katIcon = 'fa-magic';
                     foreach ($_kategoriIconMap as $kw => $ic) {
                         if (mb_stripos($_katAdi, $kw) !== false) { $_katIcon = $ic; break; }
                     }
                  @endphp
                  <div class="slp-service-card">
                     <div class="slp-service-card__icon"><i class="fa {{$_katIcon}}"></i></div>
                     <h3>{{$_katAdi}}</h3>
                     <p>{{$_katSayi}} hizmet seçeneği</p>
                     <a href="#randevu-al" data-slp-open data-slp-category="{{$kat->hizmet_kategori_id}}">Randevu Al <i class="fa fa-arrow-right"></i></a>
                  </div>
               @endforeach
            </div>
         </section>
         @endif

         {{-- ================= NEDEN BIZ / FEATURES ================= --}}
         <section class="slp-section">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Neden Biz?</span>
               <h2 class="slp-section__title">Farkımız Detaylarda</h2>
               <p class="slp-section__sub">{{$salon->salon_adi}}'i tercih etmeniz için pek çok sebep var.</p>
            </div>
            <div class="slp-features-grid">
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-user-md"></i></div>
                  <h3>Uzman Ekip</h3>
                  <p>Alanında deneyimli profesyonel personelimiz en yeni teknikleri kullanarak hizmet veriyor.</p>
               </div>
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-heart"></i></div>
                  <h3>Hijyenik Ortam</h3>
                  <p>Tüm ekipman ve alanlarımız her kullanım öncesi özenle dezenfekte edilir.</p>
               </div>
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-mobile"></i></div>
                  <h3>Online Randevu</h3>
                  <p>7/24 dilediğiniz saatte birkaç tıkla randevunuzu anında onaylayın.</p>
               </div>
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-diamond"></i></div>
                  <h3>Kaliteli Ürünler</h3>
                  <p>Sadece güvenilir markaların profesyonel serilerini kullanıyoruz.</p>
               </div>
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-clock-o"></i></div>
                  <h3>Dakiklik</h3>
                  <p>Randevu saatinize tam zamanında başlıyor, vaktinize değer veriyoruz.</p>
               </div>
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-star"></i></div>
                  <h3>Müşteri Memnuniyeti</h3>
                  <p>{{$salonyorumlar->count()}}+ mutlu müşterimizin deneyimini siz de yaşayın.</p>
               </div>
            </div>
         </section>

         {{-- ================= EKIBIMIZ ================= --}}
         @if($personeller && $personeller->count())
         <section class="slp-section slp-section--alt">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Ekibimiz</span>
               <h2 class="slp-section__title">Profesyonel Ekibimizle Tanışın</h2>
               <p class="slp-section__sub">Her biri alanında uzman, gülümseyen yüzler sizi bekliyor.</p>
            </div>
            <div class="slp-team-grid">
               @foreach($personeller as $per)
                  @if($per->salon_id == $salon->id)
                     @php
                        $_perResim = \App\IsletmeYetkilileri::where('personel_id',$per->id)->value('profil_resim');
                        if (empty($_perResim)) {
                            $_perResim = $per->cinsiyet==0 ? 'public/img/author0.jpg' : 'public/img/author1.jpg';
                        }
                        $_perName = \App\IsletmeYetkilileri::where('personel_id',$per->id)->value('name');
                        if (empty($_perName)) $_perName = $per->personel_adi;
                     @endphp
                     <div class="slp-team-card">
                        <div class="slp-team-card__avatar">
                           <img src="{{secure_asset($_perResim)}}" alt="{{$_perName}}" loading="lazy">
                        </div>
                        <h4 class="slp-team-card__name">{{$_perName}}</h4>
                        @if(!empty($per->uzmanlik))
                           <span class="slp-team-card__specialty">{{$per->uzmanlik}}</span>
                        @elseif(!empty($per->unvan))
                           <span class="slp-team-card__specialty">{{$per->unvan}}</span>
                        @endif
                        @if(!empty($per->aciklama))
                           <p class="slp-team-card__bio">{{$per->aciklama}}</p>
                        @endif
                        <div style="display:flex; flex-direction:column; gap:4px; margin-top:4px;">
                           @if(!empty($per->yillik_tecrube))
                              <span class="slp-team-card__exp"><i class="fa fa-star"></i> {{$per->yillik_tecrube}}+ yıl tecrübe</span>
                           @endif
                           @if(!empty($per->instagram))
                              <a class="slp-team-card__ig" href="https://instagram.com/{{ltrim($per->instagram,'@')}}" target="_blank" rel="noopener">
                                 <i class="fa fa-instagram"></i> @{{ltrim($per->instagram,'@')}}
                              </a>
                           @endif
                        </div>
                     </div>
                  @endif
               @endforeach
            </div>
         </section>
         @endif

         {{-- ================= GALERI ================= --}}
         @if($salongorselleri && $salongorselleri->where('salon_id',$salon->id)->count())
         <section class="slp-section">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Galeri</span>
               <h2 class="slp-section__title">Salonumuzdan Kareler</h2>
               <p class="slp-section__sub">Görsellerimizle atmosferimizi yakından tanıyın.</p>
            </div>
            <div class="slp-gallery">
               @foreach($salongorselleri as $g)
                  @if($g->salon_id == $salon->id)
                     <div class="slp-gallery__item" onclick="buyut('{{secure_asset($g->salon_gorseli)}}');" role="button" tabindex="0">
                        <img src="{{secure_asset($g->salon_gorseli)}}" alt="Salon Görseli" loading="lazy">
                     </div>
                  @endif
               @endforeach
            </div>
         </section>
         @endif

         {{-- ================= SAATLER + HARITA ================= --}}
         @if(($saloncalismasaatleri && $saloncalismasaatleri->count()) || !empty($salon->maps_iframe))
         <section class="slp-section slp-section--alt">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Ziyaret</span>
               <h2 class="slp-section__title">Çalışma Saatleri &amp; Konum</h2>
               <p class="slp-section__sub">Açık olduğumuz saatleri ve adresimizi buradan görüntüleyebilirsiniz.</p>
            </div>
            <div class="slp-hourmap-grid">
               <div class="slp-hours">
                  <h3><i class="fa fa-clock-o"></i> Çalışma Saatleri</h3>
                  @php $_gunler = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar']; @endphp
                  @for($_i=1; $_i<=7; $_i++)
                     @php
                        $_cs = $saloncalismasaatleri->firstWhere('haftanin_gunu', $_i);
                        $_isToday = ((int) date('N')) === $_i;
                     @endphp
                     <div class="slp-hours__row {{$_isToday ? 'slp-hours__row--today' : ''}}">
                        <span class="slp-hours__day">
                           {{$_gunler[$_i-1]}}@if($_isToday) · Bugün @endif
                        </span>
                        <span class="slp-hours__time {{$_cs && $_cs->calisiyor ? '' : 'slp-hours__time--closed'}}">
                           @if($_cs && $_cs->calisiyor)
                              {{date('H:i', strtotime($_cs->baslangic_saati))}} – {{date('H:i', strtotime($_cs->bitis_saati))}}
                           @else
                              Kapalı
                           @endif
                        </span>
                     </div>
                  @endfor
               </div>
               <div class="slp-map">
                  @php
                     $_mapsSrc = $salon->maps_iframe ?? null;
                     // Admin yalnizca iframe HTML yapistirdiysa src'yi cikar
                     if ($_mapsSrc && stripos($_mapsSrc, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $_mapsSrc, $_mm)) {
                         $_mapsSrc = $_mm[1];
                     }
                     // Bos ise adres'ten otomatik Google Maps embed fallback
                     if (empty($_mapsSrc) && !empty($salon->adres)) {
                         $_adresQuery = urlencode(trim($salon->adres.' '.($salon->ilce->ilce_adi ?? '').' '.($salon->il->il_adi ?? '')));
                         $_mapsSrc = 'https://maps.google.com/maps?q='.$_adresQuery.'&output=embed';
                     }
                  @endphp
                  @if(!empty($_mapsSrc))
                     <iframe src="{{$_mapsSrc}}" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                  @else
                     <div style="height:320px; display:flex; align-items:center; justify-content:center; background:var(--slp-bg); color:var(--slp-muted); font-size:14px;">
                        <i class="fa fa-map-marker" style="font-size:28px; margin-right:10px; opacity:.4;"></i> Konum henüz eklenmedi
                     </div>
                  @endif
                  <div class="slp-map__addr">
                     <i class="fa fa-map-marker"></i>
                     <span>{{$salon->adres}}</span>
                     @if(!empty($salon->adres))
                        <a href="https://www.google.com/maps/search/?api=1&query={{urlencode($salon->adres.' '.($salon->ilce->ilce_adi ?? '').' '.($salon->il->il_adi ?? ''))}}" target="_blank" rel="noopener" style="margin-left:auto; color:var(--slp-brand); font-weight:600; font-size:13px; white-space:nowrap;">
                           <i class="fa fa-external-link"></i> Yol Tarifi
                        </a>
                     @endif
                  </div>
               </div>
            </div>
         </section>
         @endif
         {{-- ================= MUSTERI YORUMLARI ================= --}}
         <section class="slp-section">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Müşteri Yorumları</span>
               <h2 class="slp-section__title">
                  @if($_ortPuan)
                     {{$_ortPuan}}/5 · {{$salonyorumlar->count()}} Yorum
                  @else
                     Müşteri Deneyimleri
                  @endif
               </h2>
               <p class="slp-section__sub">Bizi tercih eden değerli müşterilerimizin deneyimleri.</p>
            </div>

            @if(Auth::check() && \App\SalonYorumlar::where('salon_id',$salon->id)->where('user_id',Auth::user()->id)->count() == 0)
               <div class="slp-review-form-wrap">
                  <h3>Deneyiminizi Paylaşın</h3>
                  <form id="salonyorumyap" action="{{route('yorumyap')}}" method="get">
                     <div class="form-group">
                        <input type="hidden" value="{{$salon->id}}" name="yorum_isletmeid">
                        <label style="display:block; margin-bottom:6px; font-weight:600;">Puanlama</label>
                        <div style="display:flex; gap:6px; margin-bottom:14px;">
                           @for($_r=1; $_r<=5; $_r++)
                              <input type="radio" value="{{$_r}}" id="puanlama{{$_r}}" name="puanlama" {{$_r==5?'checked':''}} required style="display:none">
                              <label for="puanlama{{$_r}}" style="cursor:pointer; margin:0;"><div class="rating" data-rating="{{$_r}}"></div></label>
                           @endfor
                        </div>
                        <textarea class="form-control" required rows="3" placeholder="Deneyiminizi yazın..." name="yorumtext_yorum" id="yorumtext_yorum" style="resize:vertical; border:1.5px solid var(--slp-line-2); border-radius:10px; padding:12px;"></textarea>
                        <button type="submit" class="slp-btn slp-btn--primary" style="margin-top:14px;">
                           <i class="fa fa-paper-plane"></i> Yorumu Gönder
                        </button>
                     </div>
                  </form>
               </div>
            @endif

            @if($salonyorumlar && $salonyorumlar->count())
               <div class="slp-reviews-grid">
                  @foreach($salonyorumlar as $_yorum)
                     @php
                        $_yUser = \App\User::where('id', $_yorum->user_id)->first();
                        $_yName = $_yUser->name ?? 'Müşteri';
                        $_yPic  = $_yUser && !empty($_yUser->profil_resim) ? $_yUser->profil_resim : null;
                        if (empty($_yPic)) {
                            $_yPic = ($_yUser && $_yUser->cinsiyet == 0) ? 'public/img/author0.jpg' : 'public/img/author1.jpg';
                        }
                        $_yPuan = \App\SalonPuanlar::where('user_id', $_yorum->user_id)->where('salon_id', $salon->id)->value('puan') ?? 0;
                     @endphp
                     <div class="slp-review">
                        <div class="slp-review__head">
                           <div class="slp-review__avatar">
                              <img src="{{secure_asset($_yPic)}}" alt="{{$_yName}}" loading="lazy">
                           </div>
                           <div style="flex:1; min-width:0;">
                              <p class="slp-review__name">{{$_yName}}</p>
                              <div class="slp-review__date">
                                 @if(date('d')==date('d',strtotime($_yorum->updated_at)))
                                    Bugün {{date('H:i',strtotime($_yorum->updated_at))}}
                                 @elseif(date('d')-1 == date('d',strtotime($_yorum->updated_at)))
                                    Dün {{date('H:i',strtotime($_yorum->updated_at))}}
                                 @else
                                    {{date('d.m.Y',strtotime($_yorum->updated_at))}}
                                 @endif
                              </div>
                           </div>
                           <div class="slp-review__stars">
                              @for($_i=1; $_i<=5; $_i++)<i class="fa fa-star" style="opacity:{{$_i <= $_yPuan ? 1 : 0.22}}"></i>@endfor
                           </div>
                        </div>
                        <p class="slp-review__text">{{$_yorum->yorum}}</p>
                     </div>
                  @endforeach
               </div>
            @else
               <p style="text-align:center; color:var(--slp-muted); padding:20px;">Henüz yorum yapılmamış — ilk yorumu yapan siz olun!</p>
            @endif
         </section>

         {{-- ================= FINAL CTA BANNER ================= --}}
         <div class="slp-cta">
            <div class="slp-cta__inner">
               <div class="slp-cta__text">
                  <h2>Size Özel Deneyime Hazır mısınız?</h2>
                  <p>Saniyeler içinde randevunuzu oluşturun, uzman ekibimizle tanışmaya gelin.</p>
               </div>
               <div class="slp-cta__actions">
                  <a href="#randevu-al" class="slp-btn slp-btn--primary" data-slp-open>
                     <i class="fa fa-calendar-check-o"></i> Hemen Randevu Al
                  </a>
                  @if(!empty($salon->telefon_1))
                     <a href="tel:{{$salon->telefon_1}}" class="slp-btn slp-btn--ghost">
                        <i class="fa fa-phone"></i> Bizi Arayın
                     </a>
                  @endif
               </div>
            </div>
         </div>

         {{-- ================= ILETISIM ================= --}}
         <section class="slp-section slp-section--tight">
            <div class="slp-section__head">
               <span class="slp-eyebrow">İletişim</span>
               <h2 class="slp-section__title">Bize Ulaşın</h2>
            </div>
            <div class="slp-contact-grid">
               <div class="slp-contact-card">
                  <div class="slp-contact-card__icon"><i class="fa fa-map-marker"></i></div>
                  <p class="slp-contact-card__lbl">Adres</p>
                  <p class="slp-contact-card__val">{{$salon->adres}}</p>
               </div>
               @if(!empty($salon->telefon_1))
                  <div class="slp-contact-card">
                     <div class="slp-contact-card__icon"><i class="fa fa-phone"></i></div>
                     <p class="slp-contact-card__lbl">Telefon</p>
                     <p class="slp-contact-card__val"><a href="tel:{{$salon->telefon_1}}">{{$salon->telefon_1}}</a></p>
                  </div>
               @endif
               <div class="slp-contact-card">
                  <div class="slp-contact-card__icon"><i class="fa fa-share-alt"></i></div>
                  <p class="slp-contact-card__lbl">Sosyal Medya</p>
                  <div class="slp-contact-card__social">
                     @if(!empty($salon->instagram_sayfa))
                        <a href="https://instagram.com/{{ltrim($salon->instagram_sayfa,'@')}}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa fa-instagram"></i></a>
                     @endif
                     @if(!empty($salon->facebook_sayfa))
                        <a href="{{$salon->facebook_sayfa}}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                     @endif
                     <a href="#randevu-al" data-slp-open aria-label="Randevu Al"><i class="fa fa-calendar"></i></a>
                  </div>
               </div>
            </div>
         </section>

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

   /* ============== LUXE HERO — progress sync ============== */
   (function(){
       var sections = {
           1: document.getElementById('hizmetsecimbolumu'),
           2: document.getElementById('personelsecimbolumu'),
           3: document.getElementById('tarihsaatsecimbolumu'),
           4: document.getElementById('onaybolumu')
       };
       var bar  = document.querySelector('#lxProgress .lx-progress__bar');
       var dots = document.querySelectorAll('#lxProgress .lx-progress__dot');
       var labs = document.querySelectorAll('#lxProgress .lx-progress__label');
       if (!bar || !dots.length) return;

       function isVisible(el){
           if (!el) return false;
           var cs = window.getComputedStyle(el);
           if (cs.display === 'none' || cs.visibility === 'hidden') return false;
           return el.offsetParent !== null || cs.position === 'fixed';
       }
       function setStep(n){
           var pct = [12.5, 37.5, 62.5, 92][n-1];
           bar.style.width = pct + '%';
           dots.forEach(function(d){
               var s = parseInt(d.getAttribute('data-lxs'),10);
               d.classList.remove('is-active','is-done');
               if (s < n) d.classList.add('is-done');
               else if (s === n) d.classList.add('is-active');
           });
           labs.forEach(function(l){
               var s = parseInt(l.getAttribute('data-lxl'),10);
               l.classList.remove('is-active','is-done');
               if (s < n) l.classList.add('is-done');
               else if (s === n) l.classList.add('is-active');
           });
           document.getElementById('lxProgress').setAttribute('data-lstep', n);
       }
       function detect(){
           var active = 1;
           if (isVisible(sections[4])) active = 4;
           else if (isVisible(sections[3])) active = 3;
           else if (isVisible(sections[2])) active = 2;
           else active = 1;
           setStep(active);
       }
       detect();

       ['personelsecimadiminagec','onayadiminagec','randevuonayla_auth',
        'personelseckisminageridon','tarihsaatseckisminageridon'].forEach(function(id){
           var b = document.getElementById(id);
           if (b) b.addEventListener('click', function(){ setTimeout(detect, 60); });
       });

       // Observe display changes on each step section
       Object.keys(sections).forEach(function(k){
           var el = sections[k];
           if (!el) return;
           new MutationObserver(function(){ setTimeout(detect, 30); })
               .observe(el, { attributes: true, attributeFilter: ['style','class'] });
       });

       /* --- Sticky hero: publish height var so summary sidebar knows how far to push --- */
       var hero = document.getElementById('lxHero');
       if (hero) {
           function measureHero() {
               document.documentElement.style.setProperty('--lx-hero-h', hero.offsetHeight + 'px');
           }
           window.addEventListener('resize', measureHero);
           window.addEventListener('load', measureHero);
           measureHero();
       }

       /* ============== RANDEVU DRAWER — open/close/hash/scroll-lock ============== */
       var drawer = document.getElementById('slpDrawer');
       if (drawer) {
           var HASH = '#randevu-al';
           var body = document.body;

           function openDrawer(pushHash, categoryId) {
               if (drawer.classList.contains('is-open')) return;
               drawer.classList.add('is-open');
               drawer.setAttribute('aria-hidden', 'false');
               body.classList.add('slp-drawer-open');
               if (pushHash !== false && window.location.hash !== HASH) {
                   history.pushState({ rdvOpen: true }, '', HASH);
               }
               // Re-measure lx-hero inside drawer after layout settles
               setTimeout(function(){
                   if (typeof measureHero === 'function') measureHero();
               }, 420);
               // Hedef kategori varsa ilgili akordiyonu ac, digerlerini kapat, ustune kaydir
               if (categoryId) {
                   setTimeout(function(){ activateCategory(categoryId); }, 200);
               }
           }

           function activateCategory(categoryId) {
               var hsec = document.getElementById('hizmetsecimbolumu');
               if (!hsec) return;
               var targetBtn = hsec.querySelector('.accordion[data-kategori-id="' + categoryId + '"]');
               if (!targetBtn) return;
               // Tum akordiyonlari kapat
               hsec.querySelectorAll('.accordion').forEach(function(b){
                   b.classList.remove('active');
                   var panel = b.nextElementSibling;
                   if (panel && panel.classList.contains('panel_accordion')) {
                       panel.style.display = 'none';
                   }
               });
               // Hedefi ac
               targetBtn.classList.add('active');
               var targetPanel = targetBtn.nextElementSibling;
               if (targetPanel && targetPanel.classList.contains('panel_accordion')) {
                   targetPanel.style.display = 'block';
               }
               // Panel icinde hedefi goster
               setTimeout(function(){
                   var panelEl = document.querySelector('.slp-drawer__panel');
                   if (panelEl && targetBtn.offsetParent !== null) {
                       panelEl.scrollTo({
                           top: targetBtn.offsetTop - 80,
                           behavior: 'smooth'
                       });
                   }
               }, 300);
           }

           function closeDrawer(popHash) {
               if (!drawer.classList.contains('is-open')) return;
               drawer.classList.remove('is-open');
               drawer.setAttribute('aria-hidden', 'true');
               body.classList.remove('slp-drawer-open');
               if (popHash !== false && window.location.hash === HASH) {
                   history.replaceState(null, '', window.location.pathname + window.location.search);
               }
           }

           // Open triggers: any [data-slp-open]; [data-slp-category] hint'i varsa
           // drawer aciliktan sonra o kategori akordiyonu aciktir.
           document.addEventListener('click', function(e){
               var openEl = e.target.closest ? e.target.closest('[data-slp-open]') : null;
               if (openEl) {
                   e.preventDefault();
                   var catId = openEl.getAttribute('data-slp-category');
                   if (drawer.classList.contains('is-open') && catId) {
                       // Drawer zaten aciksa sadece kategori degistir
                       activateCategory(catId);
                   } else {
                       openDrawer(true, catId);
                   }
                   return;
               }
               var closeEl = e.target.closest ? e.target.closest('[data-slp-close]') : null;
               if (closeEl) {
                   e.preventDefault();
                   closeDrawer(true);
               }
           });

           // ESC kapatir
           document.addEventListener('keydown', function(e){
               if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
                   closeDrawer(true);
               }
           });

           // Hash degisince (geri tusu vb.) senkronla
           window.addEventListener('hashchange', function(){
               if (window.location.hash === HASH) openDrawer(false);
               else closeDrawer(false);
           });
           window.addEventListener('popstate', function(){
               if (window.location.hash === HASH) openDrawer(false);
               else closeDrawer(false);
           });

           // Ilk yukleme: URL zaten #randevu-al ise otomatik ac
           if (window.location.hash === HASH) {
               setTimeout(function(){ openDrawer(false); }, 60);
           }
       }
   })();
</script>
<!--end block-->
@endsection