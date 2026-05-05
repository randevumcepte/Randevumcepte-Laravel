@php
   // Hangi tab aktif — pageindex'e gore belirlenir
   $_aktifTab = isset($pageindex) ? $pageindex : 500;
   $_subeQs = isset($_GET['sube']) && isset($isletme) ? '?sube='.$isletme->id : '';
   $_urlCark      = '/isletmeyonetim/carkifelek'.$_subeQs;
   $_urlKaz       = '/isletmeyonetim/carkkazananlar'.$_subeQs;
   $_urlPuan      = '/isletmeyonetim/puanodulleri'.$_subeQs;
   $_urlHatirla   = '/isletmeyonetim/carkhatirlatma'.$_subeQs;
@endphp

{{-- Prefetch — tarayici arka planda diger tab'leri yukler, click anli olur --}}
@if($_aktifTab != 500) <link rel="prefetch" href="{{ $_urlCark }}" as="document"> @endif
@if($_aktifTab != 501) <link rel="prefetch" href="{{ $_urlKaz }}" as="document"> @endif
@if($_aktifTab != 502) <link rel="prefetch" href="{{ $_urlPuan }}" as="document"> @endif
@if($_aktifTab != 503) <link rel="prefetch" href="{{ $_urlHatirla }}" as="document"> @endif

<style>
   .ck-tabs {
      display: flex; gap: 6px; flex-wrap: wrap;
      background: #fff; border-radius: 14px;
      padding: 6px; margin: 0 0 18px;
      box-shadow: 0 4px 18px rgba(92, 0, 142, .08);
      border: 1px solid #ece6f3;
      position: relative;
   }
   .ck-tabs a {
      flex: 1 1 auto; min-width: 140px;
      text-align: center; text-decoration: none !important;
      padding: 11px 16px; border-radius: 10px;
      font-weight: 600; font-size: 13.5px; color: #5a4f78;
      transition: background .12s, color .12s;
      display: flex; align-items: center; justify-content: center; gap: 7px;
      white-space: nowrap;
      position: relative;
   }
   .ck-tabs a i { font-size: 15px; opacity: .85; }
   .ck-tabs a:hover { background: #faf5ff; color: #5C008E; }
   .ck-tabs a.active {
      background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
      color: #fff !important;
      box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
   }
   .ck-tabs a.active i { opacity: 1; }
   .ck-tabs a.is-loading {
      background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
      color: #fff !important;
      pointer-events: none;
      opacity: .85;
   }
   .ck-tabs a.is-loading i.fa { animation: ckSpin .7s linear infinite; }
   /* Ust progress bar */
   .ck-tabs::after {
      content: ""; position: absolute; left: 0; right: 0; bottom: -3px;
      height: 3px; background: linear-gradient(90deg, #5C008E, #9D5DC8);
      transform: scaleX(0); transform-origin: left;
      transition: transform .25s ease;
      border-radius: 0 0 14px 14px;
      pointer-events: none;
   }
   .ck-tabs.is-navigating::after { transform: scaleX(.95); transition: transform 1.5s ease-out; }
   @keyframes ckSpin { from{transform:rotate(0)} to{transform:rotate(360deg)} }
   @media (max-width: 600px) {
      .ck-tabs a { font-size: 12.5px; padding: 9px 12px; min-width: 110px; }
   }
</style>

<div class="ck-tabs" id="ckTabs">
   <a href="{{ $_urlCark }}" class="{{ $_aktifTab == 500 ? 'active' : '' }}">
      <i class="fa fa-circle-o-notch"></i> Çarkıfelek
   </a>
   <a href="{{ $_urlKaz }}" class="{{ $_aktifTab == 501 ? 'active' : '' }}">
      <i class="fa fa-trophy"></i> Çark Kazananlar
   </a>
   <a href="{{ $_urlPuan }}" class="{{ $_aktifTab == 502 ? 'active' : '' }}">
      <i class="fa fa-star"></i> Puan Ödülleri
   </a>
   <a href="{{ $_urlHatirla }}" class="{{ $_aktifTab == 503 ? 'active' : '' }}">
      <i class="fa fa-bell"></i> Hatırlatma
   </a>
</div>

<script>
   // Tab tiklandiginda anlik gorsel feedback (loading state) + ust progress bar
   (function(){
      var nav = document.getElementById('ckTabs');
      if (!nav) return;
      nav.querySelectorAll('a').forEach(function(a){
         a.addEventListener('click', function(){
            if (a.classList.contains('active')) return;
            // Diger tab'lerin active'ini al, tiklanana loading ver
            nav.querySelectorAll('a.active').forEach(function(x){ x.classList.remove('active'); });
            a.classList.add('is-loading');
            nav.classList.add('is-navigating');
         });
      });
      // bfcache'ten gelen kisilere de progress'i temizle
      window.addEventListener('pageshow', function(){
         nav.classList.remove('is-navigating');
         nav.querySelectorAll('a.is-loading').forEach(function(x){ x.classList.remove('is-loading'); });
      });
   })();
</script>
