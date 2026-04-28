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

      {{-- Top bar: logo + profil/auth (eski beyaz navbar bu alana tasindi) --}}
      <div class="slp-hero__topbar">
         <div class="slp-hero__topbar-inner">
            <a href="/" class="slp-hero__logo" aria-label="Anasayfa">
               @if(!empty($salon->logo))
                  <img src="{{ secure_asset($salon->logo) }}" alt="{{ $salon->salon_adi }}">
               @else
                  <img src="{{ secure_asset('public/img/randevumcepte.jpg') }}" alt="Randevumcepte">
               @endif
            </a>
            <div class="slp-hero__nav">
               @if(Auth::check())
                  <div class="slp-hero__userwrap">
                     <button type="button" class="slp-hero__userchip" id="slpUserChip" aria-haspopup="true" aria-expanded="false">
                        @if(!empty(Auth::user()->profil_resim))
                           <img src="{{ secure_asset(Auth::user()->profil_resim) }}" alt="">
                        @else
                           <img src="{{ secure_asset('public/img/auth.png') }}" alt="">
                        @endif
                        <span>{{ mb_strtoupper(Auth::user()->name) }}</span>
                        <i class="fa fa-caret-down" style="margin-left:4px; font-size:11px;"></i>
                     </button>
                     <div class="slp-hero__menu" id="slpUserMenu" role="menu">
                        <a href="/profilim" role="menuitem"><i class="fa fa-user"></i> Profilim</a>
                        <a href="/randevularim" role="menuitem"><i class="fa fa-calendar-check-o"></i> Randevularım</a>
                        <a href="/ayarlarim" role="menuitem"><i class="fa fa-cog"></i> Ayarlarım</a>
                        <hr>
                        <a href="#" role="menuitem" onclick="event.preventDefault(); document.getElementById('logout-form-slp').submit();"><i class="fa fa-sign-out"></i> Çıkış Yap</a>
                        <form id="logout-form-slp" action="{{ route('logout') }}" method="POST" style="display:none;">{{ csrf_field() }}</form>
                     </div>
                  </div>
               @else
                  <a href="/login" class="slp-hero__auth-btn">Giriş Yap</a>
                  <a href="/register" class="slp-hero__auth-btn slp-hero__auth-btn--primary">Üye Ol</a>
               @endif
            </div>
         </div>
      </div>

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
      {{-- ============ CARKIFELEK SECTION (eski silik banner yerine) ============ --}}
      <a href="javascript:void(0)" onclick="window.openCarkModal()" class="cark-section" aria-label="Çarkıfelek'i çevir, ödül kazan">
         <div class="cark-section__inner">
            <div class="cark-section__visual">
               <div class="cark-wheel cark-wheel--lg">
                  <span class="cark-wheel__pointer"></span>
                  <span class="cark-wheel__hub"><i class="fa fa-gift"></i></span>
               </div>
               <div class="cark-section__sparkles">
                  <span>✨</span><span>🎁</span><span>⭐</span>
               </div>
            </div>
            <div class="cark-section__content">
               <span class="cark-section__eyebrow"><i class="fa fa-bolt"></i> Sana Özel · Hediye Çarkı</span>
               <h2 class="cark-section__title">Çarkı Çevir, <em>Hediyeni</em> Kap!</h2>
               <p class="cark-section__sub">Onaylanan her randevuyla çark hakkı kazanırsın. Puan, indirim ve sürpriz hediyeler seni bekliyor.</p>
               <span class="cark-section__cta">
                  <i class="fa fa-bolt"></i> Şimdi Tam Zamanı, Çevir!
                  <i class="fa fa-long-arrow-right cark-section__cta-arrow"></i>
               </span>
            </div>
         </div>
      </a>

      {{-- ============ CARKIFELEK POPUP (orijinal tasarım + GERÇEK SVG çark + tek tıkla spin) ============ --}}
      @php
         $_dilimler = \App\CarkifelekDilimleri::where('cark_id', $_aktifCark->id)->orderBy('sira')->get();
         $_dilimlerJson = $_dilimler->map(function($d){
            return [
               'id'    => $d->id,
               'ismi'  => $d->dilim_ismi,
               'renk'  => $d->renk_kodu,
               'tip'   => isset($d->tip) ? $d->tip : 'bos',
               'deger' => $d->deger !== null ? (float) $d->deger : null,
            ];
         })->values()->toArray();
         $_isMisafir = !\Auth::check();
         $_force = isset($_GET['carkforce']) && (int) $_GET['carkforce'] === 1;
         if ($_force) {
            session()->forget("cark_bugun_{$salon->id}");
         }
         $_sessionBugunMarker = session("cark_bugun_{$salon->id}") === \Carbon\Carbon::today()->toDateString();
         $_bugunCevirdi = !$_force && $_sessionBugunMarker;
         if (!$_force && !$_isMisafir && !$_bugunCevirdi) {
            $_bugunCevirdi = \App\CarkifelekCevirmeLoglari::where('salon_id', $salon->id)
               ->where('user_id', \Auth::id())
               ->where('tip', '!=', 'tekrar_dene')
               ->whereDate('created_at', \Carbon\Carbon::today())
               ->exists();
         }
      @endphp
      <div class="cark-popup" id="carkPopup" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="carkPopupTitle">
         <div class="cark-popup__backdrop" data-cark-close></div>
         <div class="cark-popup__panel">
            <button type="button" class="cark-popup__close" data-cark-close aria-label="Kapat">
               <i class="fa fa-times"></i>
            </button>
            <div class="cark-popup__decor cark-popup__decor--1">✨</div>
            <div class="cark-popup__decor cark-popup__decor--2">🎁</div>
            <div class="cark-popup__decor cark-popup__decor--3">⭐</div>
            <div class="cark-popup__decor cark-popup__decor--4">💎</div>

            {{-- GERÇEK çark — orijinal .cark-wheel--xl yerine SVG slice'lar --}}
            <div class="cark-popup__wheel-wrap" style="position:relative; padding-top:14px;">
               <div style="position:relative; width:240px; height:240px; margin:0 auto; border-radius:50%; box-shadow: 0 0 0 6px #fff, 0 0 0 8px rgba(92,0,142,.5), 0 26px 60px rgba(92,0,142,.45); overflow:visible;">
                  {{-- Pointer (orijinal sarı üçgen) --}}
                  <span style="position:absolute; top:-14px; left:50%; transform:translateX(-50%); width:0; height:0; border-left:14px solid transparent; border-right:14px solid transparent; border-top:26px solid #FBBF24; filter:drop-shadow(0 4px 8px rgba(0,0,0,.3)); z-index:3;"></span>
                  {{-- SVG çark — salonun gerçek dilimleri --}}
                  <div style="border-radius:50%; overflow:hidden; width:100%; height:100%; background:#160630;">
                     <svg id="carkPopupSvg" viewBox="0 0 300 300" style="width:100%; height:100%; display:block; transform-origin:50% 50%; transition: transform 9s cubic-bezier(0.17, 0.67, 0.12, 0.99);"></svg>
                  </div>
                  {{-- Hub (orijinal beyaz orta + altın çerçeve) --}}
                  <span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:56px; height:56px; border-radius:50%; background:#fff; border:3px solid #FBBF24; display:flex; align-items:center; justify-content:center; font-size:22px; color:#5C008E; z-index:2; pointer-events:none;">
                     <i class="fa fa-gift"></i>
                  </span>
               </div>
            </div>

            <div class="cark-popup__content" id="carkPopupContent">
               <span class="cark-popup__eyebrow">🎰 Şimdi Tam Zamanı</span>
               <h2 class="cark-popup__title" id="carkPopupTitle">Çarkı Çevir,<br><em>Hediyeni Kazan!</em></h2>
               <p class="cark-popup__sub">{{ $salon->salon_adi }}'a özel sürpriz çarkıfelek seni bekliyor. Bedava deneme hakkı şimdi açık!</p>
               @if($_bugunCevirdi)
                  <div style="padding:12px 18px; background:rgba(255,255,255,.18); border-radius:14px; color:#fff; font-weight:700; margin-bottom:12px;">
                     ✓ Bugün çarkı çevirdiniz. Yarın tekrar deneyebilirsiniz.
                  </div>
               @else
                  <button type="button" class="cark-popup__cta" id="carkPopupSpinBtn" onclick="window.carkSpin()">
                     <i class="fa fa-bolt"></i>
                     <span>ŞİMDİ ÇEVİR</span>
                  </button>
               @endif
               <button type="button" class="cark-popup__skip" data-cark-close>Belki daha sonra</button>
            </div>
         </div>
      </div>

      <div id="carkPopupToast" style="position:fixed; top:20px; right:20px; z-index:100000; padding:14px 20px; background:#e17055; color:#fff; border-radius:12px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,.2); display:none;"></div>

      <script>
         (function(){
            const DILIMLER = {!! json_encode($_dilimlerJson) !!};
            const SALON_ID = {{ $salon->id }};
            const CSRF     = '{{ csrf_token() }}';
            const FORCE    = /[?&]carkforce=1\b/.test(window.location.search) ? 1 : 0;
            const CEVIR_URL    = '{{ route("cark.cevir") }}';
            const SMSKOD_URL   = '{{ route("cark.smskod") }}';
            const SMSDOG_URL   = '{{ route("cark.smsdogrula") }}';

            const svgEl  = (n) => document.createElementNS('http://www.w3.org/2000/svg', n);
            const wheel  = document.getElementById('carkPopupSvg');
            const toast  = document.getElementById('carkPopupToast');
            const content = document.getElementById('carkPopupContent');
            const CX = 150, CY = 150, R = 130;
            let spinning = false, currentRot = 0;

            function showToast(msg){
               toast.textContent = msg;
               toast.style.display = 'block';
               setTimeout(() => toast.style.display = 'none', 3500);
            }
            function buildLabel(d){
               switch(d.tip){
                  case 'puan':            return d.deger != null ? 'Puan' : (d.ismi || 'Puan');
                  case 'hizmet_indirimi': return d.deger != null ? 'Hizmet İnd.' : (d.ismi || 'Hizmet İnd.');
                  case 'urun_indirimi':   return d.deger != null ? 'Ürün İnd.'   : (d.ismi || 'Ürün İnd.');
                  case 'tekrar_dene':     return 'Tekrar Dene';
                  case 'bos':             return 'Boş';
                  default:                return d.ismi || 'Ödül';
               }
            }
            function buildFullLabel(d){
               if (['puan','hizmet_indirimi','urun_indirimi'].includes(d.tip) && d.deger != null) {
                  const numStr = d.tip.includes('indirimi') ? '%' + d.deger : d.deger;
                  return numStr + ' ' + buildLabel(d);
               }
               return buildLabel(d);
            }
            function wrapText(t, m){
               const w = (t||'').split(/\s+/), L = []; let c = '';
               w.forEach(x => { if((c+' '+x).trim().length<=m) c=(c+' '+x).trim(); else { if(c) L.push(c); c=x; } });
               if (c) L.push(c); return L.length ? L : [''];
            }
            function renderWheel(){
               wheel.innerHTML = '';
               const n = DILIMLER.length, ang = 360/n;
               DILIMLER.forEach((sl, i) => {
                  const sa = i*ang, ea = (i+1)*ang;
                  const sr=(sa-90)*Math.PI/180, er=(ea-90)*Math.PI/180;
                  const x1=CX+R*Math.cos(sr), y1=CY+R*Math.sin(sr);
                  const x2=CX+R*Math.cos(er), y2=CY+R*Math.sin(er);
                  const lg = ang>180?1:0;
                  const path = svgEl('path');
                  path.setAttribute('d', `M ${CX} ${CY} L ${x1} ${y1} A ${R} ${R} 0 ${lg} 1 ${x2} ${y2} Z`);
                  path.setAttribute('fill', sl.renk);
                  path.setAttribute('stroke', 'rgba(255,255,255,.7)');
                  path.setAttribute('stroke-width', '2');
                  wheel.appendChild(path);

                  const tAng = sa+ang/2, tRad=(tAng-90)*Math.PI/180;
                  const textRot = tAng<=180 ? tAng-90 : tAng-270;
                  const hasDeger = ['puan','hizmet_indirimi','urun_indirimi'].includes(sl.tip) && sl.deger != null;
                  if (hasDeger){
                     const numFs = n<=8?17:14, catFs = n<=8?11:9;
                     const numStr = sl.tip.includes('indirimi') ? '%'+sl.deger : String(sl.deger);
                     const numDist = R-(n<=8?16:13);
                     const nx = CX+numDist*Math.cos(tRad), ny = CY+numDist*Math.sin(tRad);
                     const ng = svgEl('g'); ng.setAttribute('transform', `rotate(${tAng}, ${nx}, ${ny})`);
                     const nt = svgEl('text');
                     nt.setAttribute('x', nx); nt.setAttribute('y', ny);
                     nt.setAttribute('text-anchor','middle'); nt.setAttribute('dominant-baseline','middle');
                     nt.setAttribute('font-size', numFs); nt.setAttribute('font-weight','900');
                     nt.setAttribute('fill','white');
                     nt.setAttribute('paint-order','stroke');
                     nt.setAttribute('stroke','rgba(0,0,0,.75)'); nt.setAttribute('stroke-width','3.5');
                     nt.setAttribute('stroke-linejoin','round');
                     nt.textContent = numStr;
                     ng.appendChild(nt); wheel.appendChild(ng);

                     const innerLabel = buildLabel(sl);
                     const catDist = n<=8?68:60;
                     const cx2 = CX+catDist*Math.cos(tRad), cy2 = CY+catDist*Math.sin(tRad);
                     const catMaxCh = Math.max(4, Math.floor(55/(catFs*0.62)));
                     let lines = wrapText(innerLabel, catMaxCh);
                     if (lines.length>2) lines = [innerLabel.slice(0, catMaxCh-1)+'…'];
                     const catLH = catFs+2;
                     const catSY = cy2-((lines.length-1)*catLH/2);
                     const catG = svgEl('g'); catG.setAttribute('transform', `rotate(${textRot}, ${cx2}, ${cy2})`);
                     lines.forEach((ln, li) => {
                        const t = svgEl('text');
                        t.setAttribute('x', cx2); t.setAttribute('y', catSY+li*catLH);
                        t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle');
                        t.setAttribute('font-size', catFs); t.setAttribute('font-weight','700');
                        t.setAttribute('fill','rgba(255,255,255,.92)');
                        t.setAttribute('paint-order','stroke');
                        t.setAttribute('stroke','rgba(0,0,0,.5)'); t.setAttribute('stroke-width','2');
                        t.setAttribute('stroke-linejoin','round');
                        t.textContent = ln; catG.appendChild(t);
                     });
                     wheel.appendChild(catG);
                  } else {
                     const dist = n<=8?76:68, fs = n<=8?12:10;
                     const maxCh = Math.max(6, Math.floor(80/(fs*0.60))), lh = fs+3;
                     const label = buildLabel(sl);
                     let lines = wrapText(label, maxCh);
                     if (lines.length>2) lines = [label.slice(0, maxCh-1)+'…'];
                     const tx = CX+dist*Math.cos(tRad), ty = CY+dist*Math.sin(tRad);
                     const sy = ty-((lines.length-1)*lh/2);
                     const g = svgEl('g'); g.setAttribute('transform', `rotate(${textRot}, ${tx}, ${ty})`);
                     lines.forEach((ln, li) => {
                        const t = svgEl('text');
                        t.setAttribute('x', tx); t.setAttribute('y', sy+li*lh);
                        t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','middle');
                        t.setAttribute('font-size', fs); t.setAttribute('font-weight','700');
                        t.setAttribute('fill','white');
                        t.setAttribute('paint-order','stroke');
                        t.setAttribute('stroke','rgba(0,0,0,.55)'); t.setAttribute('stroke-width','2.5');
                        t.setAttribute('stroke-linejoin','round');
                        t.textContent = ln; g.appendChild(t);
                     });
                     wheel.appendChild(g);
                  }
               });
            }
            renderWheel();

            // Sonuç ekranı (kayıt formu / kupon kodu)
            function showResult(d, kod, kayitGerekli){
               let html = '';
               if (kayitGerekli){
                  // Misafir + ödül var → kayıt formu
                  html = `
                     <span class="cark-popup__eyebrow">🎉 Tebrikler!</span>
                     <h2 class="cark-popup__title" style="font-size:24px;">Kazandın: <em>${buildFullLabel(d)}</em></h2>
                     <p class="cark-popup__sub" style="margin-bottom:14px;">Kodunu almak için 10 saniyelik kayıt — telefonuna SMS gönderilecek.</p>
                     <div style="display:flex; gap:8px; margin-bottom:10px;">
                        <input type="text" id="ky-ad" placeholder="Ad" style="flex:1; padding:11px 12px; border:2px solid rgba(255,255,255,.4); border-radius:10px; background:rgba(255,255,255,.95); font-size:14px;">
                        <input type="text" id="ky-soyad" placeholder="Soyad" style="flex:1; padding:11px 12px; border:2px solid rgba(255,255,255,.4); border-radius:10px; background:rgba(255,255,255,.95); font-size:14px;">
                     </div>
                     <input type="tel" id="ky-tel" placeholder="5XX XXX XX XX" maxlength="11" style="width:100%; padding:11px 12px; border:2px solid rgba(255,255,255,.4); border-radius:10px; margin-bottom:10px; background:rgba(255,255,255,.95); font-size:15px; letter-spacing:1px;">
                     <button type="button" class="cark-popup__cta" id="btn-smskod" onclick="window.carkSmsKod()">📨 SMS Gönder</button>
                  `;
               } else if (kod){
                  // Üye + kupon
                  html = `
                     <span class="cark-popup__eyebrow">🎉 Tebrikler!</span>
                     <h2 class="cark-popup__title" style="font-size:24px;">Kazandın: <em>${buildFullLabel(d)}</em></h2>
                     <div style="margin:14px 0; padding:14px 22px; background:#fef3c7; color:#92400e; border-radius:12px; font-family:monospace; font-size:24px; font-weight:800; letter-spacing:4px; border:2px dashed #f59e0b; display:inline-block;">${kod}</div>
                     <p class="cark-popup__sub" style="font-size:13px; margin-bottom:12px;">Bu kodu 30 gün içinde salonda kullanabilirsiniz.</p>
                     <button type="button" class="cark-popup__cta" data-cark-close>Tamam</button>
                  `;
               } else {
                  // Tekrar Dene / Boş / üye + puan
                  html = `
                     <span class="cark-popup__eyebrow">🎉 Sonuç</span>
                     <h2 class="cark-popup__title" style="font-size:24px;"><em>${buildFullLabel(d)}</em></h2>
                     <p class="cark-popup__sub">${d.tip === 'tekrar_dene' ? 'Yarın tekrar deneyebilirsiniz.' : (d.tip === 'puan' ? 'Puan hesabınıza eklendi.' : 'Bir dahaki sefere şanslı olursunuz!')}</p>
                     <button type="button" class="cark-popup__cta" data-cark-close>Tamam</button>
                  `;
               }
               content.innerHTML = html;
            }

            // Kod gönderme adımı (misafir kayıt)
            window.carkSmsKod = async function(){
               const ad = document.getElementById('ky-ad').value.trim();
               const soyad = document.getElementById('ky-soyad').value.trim();
               let tel = document.getElementById('ky-tel').value.replace(/\D/g, '');
               if (!ad || !soyad){ showToast('Ad ve soyad zorunlu'); return; }
               if (tel.length === 11 && tel[0] === '0') tel = tel.substring(1);
               if (tel.length !== 10 || tel[0] !== '5'){ showToast('Geçerli cep telefon (5XX...)'); return; }

               const btn = document.getElementById('btn-smskod');
               btn.disabled = true;
               btn.querySelector ? btn.innerHTML = '⏳ Gönderiliyor...' : btn.textContent = '⏳ Gönderiliyor...';

               try {
                  const r = await fetch(SMSKOD_URL, {
                     method:'POST',
                     headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                     body: JSON.stringify({ad, soyad, telefon: tel})
                  });
                  const data = await r.json();
                  if (!data.success){ showToast(data.message || 'Hata'); btn.disabled = false; btn.innerHTML = '📨 SMS Gönder'; return; }
                  if (data.dev_kod) showToast('Test kodu: ' + data.dev_kod);
                  // SMS adımı
                  content.innerHTML = `
                     <span class="cark-popup__eyebrow">📱 Kod Bekleniyor</span>
                     <h2 class="cark-popup__title" style="font-size:22px;">SMS Kodunu Gir</h2>
                     <p class="cark-popup__sub">0${tel} numarasına gelen 4 haneli kodu girin.</p>
                     <input type="text" id="kd-kod" maxlength="4" placeholder="• • • •" style="width:100%; padding:14px; border:2px solid rgba(255,255,255,.4); border-radius:10px; background:rgba(255,255,255,.95); font-size:28px; font-weight:800; letter-spacing:14px; text-align:center; font-family:monospace; margin-bottom:10px;">
                     <button type="button" class="cark-popup__cta" id="btn-dogrula" onclick="window.carkSmsDogrula()">✓ Doğrula ve Kodu Al</button>
                  `;
                  setTimeout(() => document.getElementById('kd-kod').focus(), 100);
               } catch(e){ showToast('Bağlantı hatası'); btn.disabled = false; btn.innerHTML = '📨 SMS Gönder'; }
            };

            window.carkSmsDogrula = async function(){
               const kod = (document.getElementById('kd-kod').value || '').trim();
               if (kod.length !== 4){ showToast('4 haneli kodu girin'); return; }
               const btn = document.getElementById('btn-dogrula');
               btn.disabled = true; btn.innerHTML = '⏳ Doğrulanıyor...';
               try {
                  const r = await fetch(SMSDOG_URL, {
                     method:'POST',
                     headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                     body: JSON.stringify({kod})
                  });
                  const data = await r.json();
                  if (!data.success){ showToast(data.message || 'Hatalı kod'); btn.disabled = false; btn.innerHTML = '✓ Doğrula ve Kodu Al'; return; }
                  if (data.odulKodu){
                     content.innerHTML = `
                        <span class="cark-popup__eyebrow">🎉 Hesabın Hazır</span>
                        <h2 class="cark-popup__title" style="font-size:22px;">Kupon Kodunuz</h2>
                        <div style="margin:14px 0; padding:14px 22px; background:#fef3c7; color:#92400e; border-radius:12px; font-family:monospace; font-size:26px; font-weight:800; letter-spacing:5px; border:2px dashed #f59e0b; display:inline-block;">${data.odulKodu}</div>
                        <p class="cark-popup__sub" style="font-size:13px;">Bu kodu 30 gün içinde salonda kullanabilirsiniz.</p>
                        <button type="button" class="cark-popup__cta" data-cark-close>Tamam</button>`;
                  } else {
                     content.innerHTML = `
                        <span class="cark-popup__eyebrow">🎉 Hesabın Hazır</span>
                        <h2 class="cark-popup__title" style="font-size:22px;">Puanın eklendi!</h2>
                        <p class="cark-popup__sub">Profilinizden puan ödüllerinize göz atabilirsiniz.</p>
                        <button type="button" class="cark-popup__cta" data-cark-close>Tamam</button>`;
                  }
               } catch(e){ showToast('Bağlantı hatası'); btn.disabled = false; btn.innerHTML = '✓ Doğrula ve Kodu Al'; }
            };

            /* ===== Web Audio: tık sesi + alkış + fanfar ===== */
            let _ctx = null;
            function ac(){ if (!_ctx) _ctx = new (window.AudioContext || window.webkitAudioContext)(); return _ctx; }
            function playTick(vol){
               try {
                  const c = ac(); if (c.state === 'suspended') c.resume();
                  const now = c.currentTime;
                  const len = Math.floor(c.sampleRate * 0.055);
                  const buf = c.createBuffer(1, len, c.sampleRate);
                  const d = buf.getChannelData(0);
                  for (let i = 0; i < len; i++){
                     const t = i/len;
                     d[i] = (Math.random()*2-1) * Math.pow(1-t, 4) * (vol || 0.45);
                  }
                  const src = c.createBufferSource(); src.buffer = buf;
                  const g = c.createGain(); g.gain.setValueAtTime(1, now);
                  src.connect(g); g.connect(c.destination); src.start(now);
               } catch(e){}
            }
            function startTickLoop(){
               const n = DILIMLER.length, sliceAng = 360/n;
               let last = -1;
               function frame(){
                  if (!spinning) return;
                  let a = 0;
                  try {
                     const mat = new DOMMatrix(window.getComputedStyle(wheel).transform);
                     a = (Math.atan2(mat.b, mat.a) * 180 / Math.PI + 360) % 360;
                  } catch(e){ a = currentRot % 360; }
                  const idx = Math.floor(((360-a)%360) / sliceAng) % n;
                  if (idx !== last){
                     last = idx; playTick(0.45);
                  }
                  requestAnimationFrame(frame);
               }
               requestAnimationFrame(frame);
            }
            function playCheer(){
               try {
                  const c = ac(); if (c.state === 'suspended') c.resume();
                  const now = c.currentTime;
                  const dur = 4, sr = c.sampleRate;
                  const buf = c.createBuffer(2, sr*dur, sr);
                  for (let ch = 0; ch < 2; ch++){
                     const d = buf.getChannelData(ch);
                     for (let i = 0; i < d.length; i++){
                        const t = i/sr;
                        const env = t<.5 ? t/.5 : t<3 ? 1 : Math.pow(1-(t-3)/1, 1.5);
                        const mod = .5 + .5 * Math.abs(Math.sin(t*7.5 + ch*.4 + Math.random()*.05));
                        d[i] = (Math.random()*2-1) * mod * env * .3;
                     }
                  }
                  const src = c.createBufferSource(); src.buffer = buf;
                  const bp = c.createBiquadFilter(); bp.type = 'bandpass'; bp.frequency.value = 1600; bp.Q.value = .7;
                  const gv = c.createGain(); gv.gain.value = 2.2;
                  src.connect(bp); bp.connect(gv); gv.connect(c.destination);
                  src.start(now);
                  [[0,523],[280,659],[520,784],[720,880],[880,1047]].forEach(([ms,f]) => {
                     const o = c.createOscillator(), g = c.createGain();
                     o.type = 'sine'; o.frequency.value = f;
                     const t0 = now + ms/1000;
                     g.gain.setValueAtTime(0, t0);
                     g.gain.linearRampToValueAtTime(.2, t0+.06);
                     g.gain.exponentialRampToValueAtTime(.001, t0+.5);
                     o.connect(g); g.connect(c.destination);
                     o.start(t0); o.stop(t0+.6);
                  });
               } catch(e){}
            }

            /* ===== Kutlama: konfeti + balon + havai fişek ===== */
            let cCan = null, cCtx = null, cRAF = null, cParts = [];
            function startCeleb(){
               if (!cCan){
                  cCan = document.createElement('canvas');
                  cCan.style.cssText = 'position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:100002; pointer-events:none;';
                  document.body.appendChild(cCan);
                  cCtx = cCan.getContext('2d');
               }
               cCan.width = innerWidth; cCan.height = innerHeight; cCan.style.display = 'block';
               cParts = [];
               const cols = ['#FF6B6B','#FFE66D','#4ECDC4','#A29BFE','#FD79A8','#6C5CE7','#00B894','#FDCB6E','#E17055','#74B9FF'];
               for (let i = 0; i < 120; i++) cParts.push({type:'c', x:Math.random()*cCan.width, y:-20-Math.random()*300, vx:(Math.random()-.5)*3, vy:3+Math.random()*5, rot:Math.random()*360, rs:(Math.random()-.5)*9, w:6+Math.random()*8, h:3+Math.random()*5, color:cols[Math.floor(Math.random()*cols.length)], a:1});
               for (let i = 0; i < 14; i++) cParts.push({type:'b', x:40+Math.random()*(cCan.width-80), y:cCan.height+30+Math.random()*120, vx:(Math.random()-.5)*1.4, vy:-(2+Math.random()*2.8), sw:Math.random()*Math.PI*2, ss:.02+Math.random()*.02, r:18+Math.random()*14, color:cols[Math.floor(Math.random()*cols.length)], a:1});
               for (let b = 0; b < 5; b++){
                  const bx = 80+Math.random()*(cCan.width-160), by = 60+Math.random()*(cCan.height*.45), bc = cols[Math.floor(Math.random()*cols.length)];
                  setTimeout(() => {
                     for (let p = 0; p < 32; p++){
                        const ang = (p/32)*Math.PI*2, sp = 3+Math.random()*6;
                        cParts.push({type:'s', x:bx, y:by, vx:Math.cos(ang)*sp, vy:Math.sin(ang)*sp, r:3+Math.random()*3, color:bc, a:1, life:1});
                     }
                  }, b*500);
               }
               if (cRAF) cancelAnimationFrame(cRAF);
               animCeleb();
               playCheer();
            }
            function animCeleb(){
               if (!cCtx) return;
               const W = cCan.width, H = cCan.height;
               cCtx.clearRect(0, 0, W, H);
               cParts.forEach(p => {
                  if (p.a <= .01) return;
                  cCtx.save(); cCtx.globalAlpha = p.a;
                  if (p.type === 'c'){
                     p.x += p.vx; p.y += p.vy; p.vy += .09; p.rot += p.rs;
                     if (p.y > H+20){ p.a = 0; cCtx.restore(); return; }
                     cCtx.translate(p.x, p.y); cCtx.rotate(p.rot*Math.PI/180);
                     cCtx.fillStyle = p.color; cCtx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
                  } else if (p.type === 'b'){
                     p.sw += p.ss; p.x += p.vx + Math.sin(p.sw)*.6; p.y += p.vy;
                     if (p.y < -70){ p.a = 0; cCtx.restore(); return; }
                     cCtx.translate(p.x, p.y);
                     cCtx.beginPath(); cCtx.arc(0, 0, p.r, 0, Math.PI*2); cCtx.fillStyle = p.color; cCtx.fill();
                     cCtx.beginPath(); cCtx.arc(-p.r*.3, -p.r*.3, p.r*.27, 0, Math.PI*2); cCtx.fillStyle = 'rgba(255,255,255,.38)'; cCtx.fill();
                     cCtx.beginPath(); cCtx.moveTo(0, p.r); cCtx.quadraticCurveTo(5, p.r+14, -3, p.r+28);
                     cCtx.strokeStyle = 'rgba(255,255,255,.55)'; cCtx.lineWidth = 1.2; cCtx.stroke();
                  } else if (p.type === 's'){
                     p.x += p.vx; p.y += p.vy; p.vy += .18; p.life -= .022; p.a = Math.max(0, p.life);
                     cCtx.beginPath(); cCtx.arc(p.x, p.y, Math.max(.5, p.r*p.life), 0, Math.PI*2);
                     cCtx.fillStyle = p.color; cCtx.fill();
                  }
                  cCtx.restore();
               });
               cRAF = requestAnimationFrame(animCeleb);
            }
            function stopCeleb(){
               if (cRAF){ cancelAnimationFrame(cRAF); cRAF = null; }
               if (cCan) cCan.style.display = 'none';
               cParts = [];
            }
            window.stopCarkCeleb = stopCeleb; // dışarıdan kapatabilelim

            // ŞİMDİ ÇEVİR
            window.carkSpin = async function(){
               if (spinning) return;
               spinning = true;
               try { ac().resume(); } catch(e){}
               const btn = document.getElementById('carkPopupSpinBtn');
               if (btn){ btn.disabled = true; btn.innerHTML = '⏳ Çevriliyor...'; }
               let data;
               try {
                  const r = await fetch(CEVIR_URL, {
                     method:'POST',
                     headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                     body: JSON.stringify({salon_id: SALON_ID, carkforce: FORCE})
                  });
                  data = await r.json();
               } catch(e){
                  showToast('Bağlantı hatası');
                  spinning = false;
                  if (btn){ btn.disabled = false; btn.innerHTML = '<i class="fa fa-bolt"></i> <span>ŞİMDİ ÇEVİR</span>'; }
                  return;
               }
               if (!data.success){
                  showToast(data.message || 'Hata');
                  spinning = false;
                  if (btn){ btn.disabled = false; btn.innerHTML = '<i class="fa fa-bolt"></i> <span>ŞİMDİ ÇEVİR</span>'; }
                  return;
               }

               const n = DILIMLER.length, ang = 360/n;
               const idx = data.dilimIndex;
               const jitter = (Math.random()-.5) * ang * 0.6;
               const stopAt = (idx+0.5) * ang + jitter;
               const offset = ((360-stopAt)%360 + 360) % 360;
               const nSpins = (12 + Math.floor(Math.random()*5)) * 360;
               const curMod = ((currentRot%360)+360) % 360;
               let diff = offset - curMod;
               if (diff < 0) diff += 360;
               currentRot += nSpins + diff;
               wheel.style.transform = `rotate(${currentRot}deg)`;

               // Tık sesi döngüsü — dilim geçişlerinde çalar
               startTickLoop();

               setTimeout(() => {
                  spinning = false; // tick loop'u durdur
                  showResult(data.dilim, data.odulKodu, data.kayitGerekli);
                  // Ödül varsa kutlama (puan/hizmet/ürün) — boş/tekrar dene'de gösterme
                  if (['puan','hizmet_indirimi','urun_indirimi'].includes(data.dilim.tip) && data.dilim.deger){
                     startCeleb();
                  }
               }, 9200);
            };
         })();

         // Popup açma/kapatma
         window.openCarkModal = function(){
            var pop = document.getElementById('carkPopup');
            if (!pop) return;
            pop.classList.add('is-open');
            pop.setAttribute('aria-hidden', 'false');
            document.body.classList.add('cark-popup-open');
         };
         window.closeCarkModal = function(){
            var pop = document.getElementById('carkPopup');
            if (!pop) return;
            pop.classList.remove('is-open');
            pop.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('cark-popup-open');
            try { window.stopCarkCeleb && window.stopCarkCeleb(); } catch(e){}
         };
      </script>
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

   {{-- ============ BOTTOM DOCK — ORTADA CIFT BUTON (HEMEN ARA + RANDEVU AL) ============ --}}
   <div class="slp-dock" role="region" aria-label="Hizli aksiyonlar">
      @if(!empty($salon->telefon_1))
         <a href="tel:{{$salon->telefon_1}}" class="slp-dock__btn slp-dock__btn--call" aria-label="Hemen Ara">
            <span class="slp-dock__icon slp-dock__icon--call"><i class="fa fa-phone"></i></span>
            <span class="slp-dock__label">
               <span class="slp-dock__top">Hemen Ara</span>
               <span class="slp-dock__sub">{{$salon->telefon_1}}</span>
            </span>
         </a>
      @endif
      <a href="#randevu-al" data-slp-open class="slp-dock__btn slp-dock__btn--book" aria-label="Randevu Al">
         <span class="slp-dock__icon"><i class="fa fa-calendar-check-o"></i></span>
         <span class="slp-dock__label">
            <span class="slp-dock__top">Randevu Al</span>
            <span class="slp-dock__sub">Saniyeler içinde</span>
         </span>
      </a>
   </div>

   {{-- ============ SAGDA SABIT SOSYAL MEDYA SERIDI ============ --}}
   @php
      $_igRaw = trim($salon->instagram_sayfa ?? '');
      $_igUrl = '';
      if (!empty($_igRaw)) {
          $_igUrl = preg_match('#^https?://#i', $_igRaw)
              ? $_igRaw
              : 'https://instagram.com/' . ltrim($_igRaw, '@');
      }
      $_fbRaw = trim($salon->facebook_sayfa ?? '');
      $_fbUrl = '';
      if (!empty($_fbRaw)) {
          $_fbUrl = preg_match('#^https?://#i', $_fbRaw)
              ? $_fbRaw
              : 'https://facebook.com/' . ltrim($_fbRaw, '@');
      }
   @endphp
   @if(!empty($_igUrl) || !empty($_fbUrl) || !empty($salon->telefon_1) || !empty($salon->adres))
      <aside class="slp-social" aria-label="Sosyal Medya Hesaplari">
         @if(!empty($_igUrl))
            <a href="{{ $_igUrl }}" target="_blank" rel="noopener" class="slp-social__btn slp-social__btn--ig" aria-label="Instagram'da takip et" title="Instagram'da takip et">
               <i class="fa fa-instagram"></i>
            </a>
         @endif
         @if(!empty($_fbUrl))
            <a href="{{ $_fbUrl }}" target="_blank" rel="noopener" class="slp-social__btn slp-social__btn--fb" aria-label="Facebook sayfasi" title="Facebook sayfasi">
               <i class="fa fa-facebook"></i>
            </a>
         @endif
         @if(!empty($salon->telefon_1))
            <a href="tel:{{ $salon->telefon_1 }}" class="slp-social__btn slp-social__btn--phone" aria-label="Telefon ile ara" title="Telefon">
               <i class="fa fa-phone"></i>
            </a>
         @endif
         @if(!empty($salon->maps_iframe) || !empty($salon->adres))
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($salon->adres ?? '').' '.($salon->ilce->ilce_adi ?? '').' '.($salon->il->il_adi ?? '')) }}" target="_blank" rel="noopener" class="slp-social__btn slp-social__btn--map" aria-label="Yol Tarifi" title="Yol Tarifi">
               <i class="fa fa-map-marker"></i>
            </a>
         @endif
      </aside>
   @endif

   {{-- ============ WHATSAPP FLOATING CHAT WIDGET (sag taraf) ============ --}}
   @if(!empty($salon->telefon_1))
      @php
         // Telefon numarasini wa.me formatina cevir (sadece rakam, basina 90 ekle)
         $_waDigits = preg_replace('/\D/', '', $salon->telefon_1 ?? '');
         if (strlen($_waDigits) === 11 && substr($_waDigits, 0, 1) === '0') {
             $_waNumber = '9' . $_waDigits; // 0531... -> 90531...
         } elseif (strlen($_waDigits) === 10 && substr($_waDigits, 0, 1) === '5') {
             $_waNumber = '90' . $_waDigits; // 531... -> 90531...
         } elseif (strlen($_waDigits) >= 12 && substr($_waDigits, 0, 2) === '90') {
             $_waNumber = $_waDigits; // 90... oldugu gibi
         } else {
             $_waNumber = $_waDigits;
         }
         $_waMsg = urlencode('Merhaba, '.$salon->salon_adi.' hakkında bilgi almak istiyorum.');
         $_waLink = 'https://wa.me/'.$_waNumber.'?text='.$_waMsg;
      @endphp
      <div class="slp-wa" id="slpWa">
         <button type="button" class="slp-wa__fab" id="slpWaToggle" aria-label="WhatsApp ile yazışın">
            <i class="fa fa-whatsapp"></i>
            <span class="slp-wa__pulse"></span>
            <span class="slp-wa__badge">1</span>
         </button>
         <div class="slp-wa__panel" id="slpWaPanel" role="dialog" aria-hidden="true" aria-label="WhatsApp Sohbet">
            <div class="slp-wa__header">
               <div class="slp-wa__avatar">
                  @if(!empty($salon->logo))
                     <img src="{{ secure_asset($salon->logo) }}" alt="{{ $salon->salon_adi }}">
                  @else
                     <i class="fa fa-comments"></i>
                  @endif
                  <span class="slp-wa__online" title="Çevrimiçi"></span>
               </div>
               <div class="slp-wa__head-info">
                  <div class="slp-wa__name">{{ $salon->salon_adi }}</div>
                  <div class="slp-wa__status"><span class="slp-wa__dot"></span> Genellikle dakikalar içinde yanıtlar</div>
               </div>
               <button type="button" class="slp-wa__close" id="slpWaClose" aria-label="Kapat">
                  <i class="fa fa-times"></i>
               </button>
            </div>
            <div class="slp-wa__body">
               <div class="slp-wa__bubble">
                  Merhaba 👋<br>
                  <strong>{{ $salon->salon_adi }}</strong>'a hoş geldiniz!<br>
                  Hizmetler, fiyatlar veya randevu hakkında sorularınızı cevaplayalım.
               </div>
            </div>
            <div class="slp-wa__footer">
               <a href="{{ $_waLink }}" target="_blank" rel="noopener" class="slp-wa__cta">
                  <i class="fa fa-whatsapp"></i> WhatsApp'ta Yazın
               </a>
            </div>
         </div>
      </div>
   @endif

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
            <div class="slp-about">
               <p>{!! nl2br(e($salon->aciklama)) !!}</p>
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
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-gift"></i></div>
                  <h3>Sadakat Ödülleri</h3>
                  <p>Her ziyaretinizde puan kazanın, çarkı çevirin ve özel indirim fırsatları elde edin.</p>
               </div>
               <div class="slp-feature">
                  <div class="slp-feature__icon"><i class="fa fa-coffee"></i></div>
                  <h3>Konforlu Ortam</h3>
                  <p>Keyifle vakit geçirebileceğiniz ferah, şık ve modern bir atmosfer sizi bekliyor.</p>
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
                           <p class="slp-team-card__bio">{{ \Illuminate\Support\Str::limit($per->aciklama, 140) }}</p>
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
                        @if(!empty($per->aciklama) || !empty($per->uzmanlik) || !empty($per->yillik_tecrube) || !empty($per->instagram))
                           <a class="slp-team-card__detail" href="{{url('/'.str_slug($salon->salon_adi).'-'.$salon->id.'/personel/'.$per->id)}}" style="display:inline-block; margin-top:12px; padding:8px 16px; background:#007bff; color:#fff; border-radius:20px; font-size:12px; font-weight:600; text-decoration:none; transition:all .2s">
                              Detaylı Görüntüle <i class="fa fa-arrow-right" style="margin-left:4px"></i>
                           </a>
                        @endif
                     </div>
                  @endif
               @endforeach
            </div>
         </section>
         @endif

         {{-- ================= GALERI ================= --}}
         @php
            // Yanlislikla yuklenen / blocklist'teki gorseller (filename pattern) filtrelenir.
            $_blockedImagePatterns = ['685e8dd97888b'];
            $_galleryImages = $salongorselleri
                ->where('salon_id', $salon->id)
                ->filter(function($g) use ($_blockedImagePatterns) {
                    foreach ($_blockedImagePatterns as $pat) {
                        if (stripos($g->salon_gorseli ?? '', $pat) !== false) return false;
                    }
                    return true;
                });
            $_isletmeAdminLogged = Auth::guard('isletmeyonetim')->check()
                && Auth::guard('isletmeyonetim')->user()->salon_id == $salon->id;
         @endphp
         @if($_galleryImages->count())
         <section class="slp-section">
            <div class="slp-section__head">
               <span class="slp-eyebrow">Galeri</span>
               <h2 class="slp-section__title">Salonumuzdan Kareler</h2>
               <p class="slp-section__sub">Görsellerimizle atmosferimizi yakından tanıyın.</p>
               @if($_isletmeAdminLogged)
                  <p style="font-size:12px;color:var(--slp-soft);margin-top:8px;">
                     <i class="fa fa-info-circle"></i> Sadece sizin görebildiğiniz: yanlış yüklenen bir görseli kaldırmak için üzerine gelip kırmızı çöp ikonuna tıklayın.
                  </p>
               @endif
            </div>
            <div class="slp-gallery">
               @foreach($_galleryImages as $g)
                  <div class="slp-gallery__item {{$_isletmeAdminLogged ? 'is-admin' : ''}}" data-gorsel-id="{{$g->id}}">
                     <img src="{{secure_asset($g->salon_gorseli)}}" alt="Salon Görseli" loading="lazy" onclick="buyut('{{secure_asset($g->salon_gorseli)}}');">
                     @if($_isletmeAdminLogged)
                        <button type="button"
                                class="slp-gallery__delete"
                                data-gorsel-id="{{$g->id}}"
                                title="Görseli kaldır"
                                aria-label="Görseli kaldır">
                           <i class="fa fa-trash"></i>
                        </button>
                     @endif
                  </div>
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

       /* --- Galeri admin sil butonu (sadece isletmeyonetim guard'inda gosterilir) --- */
       (function(){
           var btns = document.querySelectorAll('.slp-gallery__delete');
           if (!btns.length) return;
           btns.forEach(function(btn){
               btn.addEventListener('click', function(e){
                   e.preventDefault();
                   e.stopPropagation();
                   var id = btn.getAttribute('data-gorsel-id');
                   if (!id) return;
                   if (!confirm('Bu görseli galeriden silmek istediğinize emin misiniz? Geri alınamaz.')) return;
                   var item = btn.closest('.slp-gallery__item');
                   if (item) item.style.opacity = '0.4';
                   var form = new FormData();
                   form.append('gorselid', id);
                   form.append('_token', document.querySelector('meta[name="_token"]')?.content
                                       || document.querySelector('meta[name="csrf-token"]')?.content || '');
                   fetch('/isletmeyonetim/gorselsil?gorselid=' + encodeURIComponent(id), {
                       method: 'GET',
                       credentials: 'same-origin',
                       headers: { 'X-Requested-With': 'XMLHttpRequest' }
                   }).then(function(r){
                       if (r.ok) {
                           if (item) {
                               item.style.transition = 'opacity .25s ease, transform .25s ease';
                               item.style.transform = 'scale(.85)';
                               item.style.opacity = '0';
                               setTimeout(function(){ item.remove(); }, 280);
                           }
                       } else {
                           alert('Silme başarısız oldu. Tekrar deneyiniz.');
                           if (item) item.style.opacity = '1';
                       }
                   }).catch(function(){
                       alert('Bağlantı hatası. Tekrar deneyiniz.');
                       if (item) item.style.opacity = '1';
                   });
               });
           });
       })();

       /* --- Carkifelek auto-popup + spinning wheel sound --- */
       (function(){
           var pop = document.getElementById('carkPopup');
           if (!pop) return;
           var SEEN = 'rdvCarkPopupSeen';
           var DELAY = 1600;

           function open(){
               // iframe lazy yükleme
               var iframe = document.getElementById('carkIframe');
               if (iframe && !iframe.src) iframe.src = iframe.getAttribute('data-src');
               pop.classList.add('is-open');
               pop.setAttribute('aria-hidden', 'false');
               document.body.classList.add('cark-popup-open');
               try { sessionStorage.setItem(SEEN, '1'); } catch(e){}
               playWheelSound();
           }
           function close(){
               pop.classList.remove('is-open');
               pop.setAttribute('aria-hidden', 'true');
               document.body.classList.remove('cark-popup-open');
           }

           // Web Audio ile cark ticking sesi (asset gerektirmez)
           function playWheelSound(){
               try {
                   var Ctx = window.AudioContext || window.webkitAudioContext;
                   if (!Ctx) return;
                   var ctx = new Ctx();
                   if (ctx.state === 'suspended' && ctx.resume) {
                       ctx.resume().catch(function(){});
                   }
                   var ticks = 14;
                   var spacing = 95;
                   for (var i = 0; i < ticks; i++) {
                       (function(idx){
                           setTimeout(function(){
                               var osc = ctx.createOscillator();
                               var gain = ctx.createGain();
                               osc.type = 'square';
                               osc.frequency.value = 1100 + Math.random() * 350;
                               gain.gain.setValueAtTime(0.08, ctx.currentTime);
                               gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.04);
                               osc.connect(gain).connect(ctx.destination);
                               osc.start();
                               osc.stop(ctx.currentTime + 0.05);
                           }, idx * spacing);
                       })(i);
                   }
                   // Final 'ding' sound
                   setTimeout(function(){
                       var osc = ctx.createOscillator();
                       var gain = ctx.createGain();
                       osc.type = 'sine';
                       osc.frequency.setValueAtTime(880, ctx.currentTime);
                       osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.18);
                       gain.gain.setValueAtTime(0.18, ctx.currentTime);
                       gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
                       osc.connect(gain).connect(ctx.destination);
                       osc.start();
                       osc.stop(ctx.currentTime + 0.7);
                   }, ticks * spacing + 60);
               } catch(e){}
           }

           // Test/debug: ?carkforce=1 ile her seferinde göster
           var FORCE = /[?&]carkforce=1\b/.test(window.location.search);
           if (FORCE) {
               try { sessionStorage.removeItem(SEEN); } catch(e){}
           }

           // Daha onceden gosterildiyse acma
           var alreadySeen = false;
           try { alreadySeen = sessionStorage.getItem(SEEN) === '1'; } catch(e){}
           if (FORCE || !alreadySeen) {
               setTimeout(open, FORCE ? 200 : DELAY);
           }

           // Kapatma triggers
           document.addEventListener('click', function(e){
               var c = e.target.closest && e.target.closest('[data-cark-close]');
               if (c) { e.preventDefault(); close(); }
           });
           document.addEventListener('keydown', function(e){
               if (e.key === 'Escape' && pop.classList.contains('is-open')) close();
           });
           // CTA tiklayinca: kisa bir anim'dan sonra kapatip yonlendirme normal akista (href tetikler)
           var spinBtn = pop.querySelector('[data-cark-spin]');
           if (spinBtn) {
               spinBtn.addEventListener('click', function(e){
                   playWheelSound();
                   try { sessionStorage.setItem(SEEN, '1'); } catch(err){}
               });
           }
       })();

       /* --- WhatsApp chat widget --- */
       (function(){
           var wa = document.getElementById('slpWa');
           var toggle = document.getElementById('slpWaToggle');
           var closeBtn = document.getElementById('slpWaClose');
           if (!wa || !toggle) return;
           function openWa(){ wa.classList.add('is-open'); toggle.setAttribute('aria-expanded', 'true'); }
           function closeWa(){ wa.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); }
           toggle.addEventListener('click', function(e){
               e.stopPropagation();
               wa.classList.contains('is-open') ? closeWa() : openWa();
           });
           if (closeBtn) {
               closeBtn.addEventListener('click', function(e){
                   e.stopPropagation();
                   closeWa();
               });
           }
           document.addEventListener('click', function(e){
               if (!wa.contains(e.target)) closeWa();
           });
           document.addEventListener('keydown', function(e){
               if (e.key === 'Escape') closeWa();
           });
           // Ilk yuklemede badge dikkat cekmek icin sallanir
           setTimeout(function(){ toggle.classList.add('is-jiggle'); }, 1200);
           setTimeout(function(){ toggle.classList.remove('is-jiggle'); }, 4400);
       })();

       /* --- Hero topbar user chip dropdown --- */
       (function(){
           var chip = document.getElementById('slpUserChip');
           var menu = document.getElementById('slpUserMenu');
           if (!chip || !menu) return;
           chip.addEventListener('click', function(e){
               e.stopPropagation();
               var open = menu.classList.toggle('is-open');
               chip.setAttribute('aria-expanded', open ? 'true' : 'false');
           });
           document.addEventListener('click', function(e){
               if (!menu.contains(e.target) && !chip.contains(e.target)) {
                   menu.classList.remove('is-open');
                   chip.setAttribute('aria-expanded', 'false');
               }
           });
           document.addEventListener('keydown', function(e){
               if (e.key === 'Escape') {
                   menu.classList.remove('is-open');
                   chip.setAttribute('aria-expanded', 'false');
               }
           });
       })();

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