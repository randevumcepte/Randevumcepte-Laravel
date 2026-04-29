{{-- =============================================================
     SALON AKILLI HATIRLATMA SISTEMI
     CSS  : public/isletmeyonetim_assets/css/salon_hatirlatma.css
     JS   : public/isletmeyonetim_assets/js/salon_hatirlatma.js
     Asset versiyonu icin asagidaki ?v=X.Y degerini guncelle.
     ============================================================= --}}

@php $shtVer = '1.0'; @endphp

<link rel="stylesheet" href="{{ secure_asset('public/isletmeyonetim_assets/css/salon_hatirlatma.css') }}?v={{ $shtVer }}">

<script>window.SHT_AYARLAR = { salon_id: {{ (int)($isletme->id ?? 0) }} };</script>

<div id="salon-hatirlatma-toaster" aria-live="polite" aria-atomic="true"></div>

<div id="salon-hatirlatma-bigpopup" role="dialog" aria-modal="true">
    <div class="sht-popup-card" onclick="event.stopPropagation();">
        <div class="sht-popup-header">
            <h2 id="sht-popup-baslik">Bugün Yapılacaklar</h2>
            <p id="sht-popup-altbaslik">Salonunuz için biriken hatırlatmalar.</p>
        </div>
        <div class="sht-popup-body" id="sht-popup-body"></div>
        <div class="sht-popup-footer">
            <button type="button" class="sht-btn-sonra" id="sht-btn-sonra">Daha Sonra</button>
            <button type="button" class="sht-btn-anla" id="sht-btn-anla">Anladım, Kapat</button>
        </div>
    </div>
</div>

<script src="{{ secure_asset('public/isletmeyonetim_assets/js/salon_hatirlatma.js') }}?v={{ $shtVer }}"></script>
