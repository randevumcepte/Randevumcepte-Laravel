{{-- =============================================================
     SALON AKILLI HATIRLATMA SISTEMI
     Sag-alt toast yigini + gunde 2 kez tam ekran eglenceli popup
     ============================================================= --}}

<style>
/* ===== Toast (sag alt yigin) ===== */
#salon-hatirlatma-toaster{
    position: fixed; right: 18px; bottom: 18px;
    width: 360px; max-width: calc(100vw - 36px);
    z-index: 99990; pointer-events: none;
    display: flex; flex-direction: column; gap: 10px;
}
#salon-hatirlatma-toaster .sht-toast{
    pointer-events: auto;
    background: linear-gradient(135deg,#ffffff 0%,#f7f8fc 100%);
    border-radius: 14px;
    padding: 14px 16px 14px 16px;
    box-shadow: 0 12px 40px rgba(0,0,0,.18), 0 2px 8px rgba(0,0,0,.06);
    border-left: 5px solid #5C008E;
    display: flex; align-items: flex-start; gap: 12px;
    transform: translateX(120%); opacity: 0;
    transition: transform .55s cubic-bezier(.2,.9,.2,1.4), opacity .35s;
    position: relative;
    cursor: pointer;
    font-family: inherit;
}
#salon-hatirlatma-toaster .sht-toast.show{ transform: translateX(0); opacity: 1; }
#salon-hatirlatma-toaster .sht-toast .sht-emoji{
    font-size: 28px; line-height: 1; flex-shrink: 0;
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(92,0,142,.08);
}
#salon-hatirlatma-toaster .sht-toast .sht-body{ flex: 1; min-width: 0; }
#salon-hatirlatma-toaster .sht-toast .sht-baslik{
    font-size: 13.5px; font-weight: 700; color: #1f2937;
    margin: 0 0 3px; line-height: 1.25;
}
#salon-hatirlatma-toaster .sht-toast .sht-mesaj{
    font-size: 12px; color: #4b5563; margin: 0;
    line-height: 1.4;
}
#salon-hatirlatma-toaster .sht-toast .sht-kapat{
    position: absolute; top: 6px; right: 8px;
    width: 22px; height: 22px;
    border: none; background: transparent;
    color: #9ca3af; font-size: 18px; cursor: pointer;
    line-height: 1;
}
#salon-hatirlatma-toaster .sht-toast .sht-kapat:hover{ color: #1f2937; }
#salon-hatirlatma-toaster .sht-toast .sht-sayac{
    display: inline-block; font-size: 10px; font-weight: 700;
    background: #5C008E; color: #fff;
    padding: 2px 7px; border-radius: 10px; margin-left: 6px;
    vertical-align: middle;
}

/* Tema renkleri */
.sht-toast.tema-kirmizi-uyari{ border-left-color: #ef4444; }
.sht-toast.tema-kirmizi-uyari .sht-emoji{ background: rgba(239,68,68,.12); animation: sht-pulse 1.6s infinite; }
.sht-toast.tema-kirmizi-uyari .sht-sayac{ background: #ef4444; }

.sht-toast.tema-konfeti-parti{ border-left-color: #ec4899; }
.sht-toast.tema-konfeti-parti .sht-emoji{ background: rgba(236,72,153,.12); animation: sht-bounce 1.4s infinite; }
.sht-toast.tema-konfeti-parti .sht-sayac{ background: #ec4899; }

.sht-toast.tema-altin-yagmur{ border-left-color: #d97706; }
.sht-toast.tema-altin-yagmur .sht-emoji{ background: rgba(217,119,6,.12); animation: sht-jiggle 2s infinite; }
.sht-toast.tema-altin-yagmur .sht-sayac{ background: #d97706; }

.sht-toast.tema-mavi-cinglir{ border-left-color: #3b82f6; }
.sht-toast.tema-mavi-cinglir .sht-emoji{ background: rgba(59,130,246,.12); animation: sht-ring 1.4s infinite; }
.sht-toast.tema-mavi-cinglir .sht-sayac{ background: #3b82f6; }

.sht-toast.tema-pasta-balon{ border-left-color: #a855f7; }
.sht-toast.tema-pasta-balon .sht-emoji{ background: rgba(168,85,247,.12); animation: sht-balloon 2.4s infinite; }
.sht-toast.tema-pasta-balon .sht-sayac{ background: #a855f7; }

.sht-toast.tema-turuncu-kasa{ border-left-color: #f97316; }
.sht-toast.tema-turuncu-kasa .sht-emoji{ background: rgba(249,115,22,.12); animation: sht-flip 2.6s infinite; }
.sht-toast.tema-turuncu-kasa .sht-sayac{ background: #f97316; }

@keyframes sht-pulse{ 0%,100%{ transform: scale(1);} 50%{ transform: scale(1.12);} }
@keyframes sht-bounce{ 0%,100%{ transform: translateY(0) rotate(-6deg);} 50%{ transform: translateY(-6px) rotate(6deg);} }
@keyframes sht-jiggle{ 0%,100%{ transform: rotate(-4deg);} 50%{ transform: rotate(4deg);} }
@keyframes sht-ring{ 0%,100%{ transform: rotate(0);} 25%{ transform: rotate(-15deg);} 75%{ transform: rotate(15deg);} }
@keyframes sht-balloon{ 0%,100%{ transform: translateY(0);} 50%{ transform: translateY(-4px);} }
@keyframes sht-flip{ 0%,40%,100%{ transform: rotateY(0);} 50%{ transform: rotateY(180deg);} 90%{ transform: rotateY(360deg);} }

/* ===== Tam ekran egilenceli popup ===== */
#salon-hatirlatma-bigpopup{
    position: fixed; inset: 0;
    background: rgba(15,17,32,.55);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 99995;
    display: none; align-items: center; justify-content: center;
    animation: sht-fadeIn .35s ease;
    padding: 20px;
}
#salon-hatirlatma-bigpopup.show{ display: flex; }
#salon-hatirlatma-bigpopup .sht-popup-card{
    background: #fff;
    border-radius: 22px;
    width: 520px; max-width: 100%; max-height: calc(100vh - 40px);
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,.4);
    transform: scale(.85) translateY(20px); opacity: 0;
    animation: sht-popIn .55s cubic-bezier(.2,.9,.2,1.5) forwards;
    display: flex; flex-direction: column;
}
#salon-hatirlatma-bigpopup .sht-popup-header{
    padding: 28px 24px 22px;
    background: linear-gradient(135deg,#5C008E 0%,#7B2FB8 50%,#9D5DC8 100%);
    color: #fff;
    text-align: center;
    position: relative;
    overflow: hidden;
}
#salon-hatirlatma-bigpopup .sht-popup-header::after{
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 30% 20%, rgba(255,255,255,.18) 0%, transparent 50%);
    pointer-events: none;
}
#salon-hatirlatma-bigpopup .sht-popup-header h2{
    margin: 0; font-size: 22px; font-weight: 800;
    letter-spacing: .2px;
}
#salon-hatirlatma-bigpopup .sht-popup-header p{
    margin: 6px 0 0; font-size: 13px; opacity: .9;
}
#salon-hatirlatma-bigpopup .sht-popup-body{
    padding: 18px; overflow: auto; flex: 1;
    background: linear-gradient(180deg,#fafbff 0%,#fff 100%);
}
#salon-hatirlatma-bigpopup .sht-popup-item{
    display: flex; gap: 14px; align-items: flex-start;
    padding: 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #eef0f6;
    margin-bottom: 10px;
    transition: transform .25s ease, box-shadow .25s ease;
    text-decoration: none; color: inherit;
}
#salon-hatirlatma-bigpopup .sht-popup-item:hover{
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(92,0,142,.12);
    text-decoration: none;
    color: inherit;
}
#salon-hatirlatma-bigpopup .sht-popup-item .sht-emoji{
    font-size: 32px; width: 56px; height: 56px;
    flex-shrink: 0;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(92,0,142,.08);
}
#salon-hatirlatma-bigpopup .sht-popup-item.tema-kirmizi-uyari .sht-emoji{ background: rgba(239,68,68,.12); }
#salon-hatirlatma-bigpopup .sht-popup-item.tema-konfeti-parti .sht-emoji{ background: rgba(236,72,153,.12); }
#salon-hatirlatma-bigpopup .sht-popup-item.tema-altin-yagmur .sht-emoji{ background: rgba(217,119,6,.12); }
#salon-hatirlatma-bigpopup .sht-popup-item.tema-mavi-cinglir .sht-emoji{ background: rgba(59,130,246,.12); }
#salon-hatirlatma-bigpopup .sht-popup-item.tema-pasta-balon .sht-emoji{ background: rgba(168,85,247,.12); }
#salon-hatirlatma-bigpopup .sht-popup-item.tema-turuncu-kasa .sht-emoji{ background: rgba(249,115,22,.12); }
#salon-hatirlatma-bigpopup .sht-popup-item .sht-baslik{
    margin: 0 0 3px; font-size: 14px; font-weight: 700; color: #111827;
}
#salon-hatirlatma-bigpopup .sht-popup-item .sht-mesaj{
    margin: 0; font-size: 12.5px; color: #4b5563; line-height: 1.45;
}
#salon-hatirlatma-bigpopup .sht-popup-item .sht-altmesaj{
    margin: 6px 0 0; font-size: 11.5px; color: #6b7280; font-style: italic;
}
#salon-hatirlatma-bigpopup .sht-popup-footer{
    display: flex; gap: 10px; padding: 14px 18px 18px;
    border-top: 1px solid #eef0f6;
    background: #fff;
}
#salon-hatirlatma-bigpopup .sht-popup-footer button{
    flex: 1; padding: 11px 16px; border-radius: 10px;
    border: none; font-weight: 700; cursor: pointer;
    font-size: 13px; transition: all .2s;
}
#salon-hatirlatma-bigpopup .sht-popup-footer .sht-btn-sonra{
    background: #f3f4f6; color: #374151;
}
#salon-hatirlatma-bigpopup .sht-popup-footer .sht-btn-sonra:hover{ background: #e5e7eb; }
#salon-hatirlatma-bigpopup .sht-popup-footer .sht-btn-anla{
    background: linear-gradient(135deg,#5C008E,#7B2FB8);
    color: #fff;
}
#salon-hatirlatma-bigpopup .sht-popup-footer .sht-btn-anla:hover{ filter: brightness(1.08); }

/* Konfeti partikulleri */
.sht-confetti{
    position: fixed; pointer-events: none; z-index: 99996;
    width: 8px; height: 14px; opacity: 0;
    border-radius: 2px;
    top: -20px;
}

@keyframes sht-fadeIn{ from{ opacity: 0;} to{ opacity: 1;} }
@keyframes sht-popIn{
    0%{ transform: scale(.7) translateY(40px); opacity: 0; }
    70%{ transform: scale(1.04) translateY(-4px); opacity: 1; }
    100%{ transform: scale(1) translateY(0); opacity: 1; }
}

/* Header bell badge */
.sht-bell-badge{
    position: relative;
    display: inline-flex; align-items: center;
    margin-right: 10px;
    cursor: pointer;
    color: #fff; font-size: 18px;
    padding: 6px 8px;
    border-radius: 8px;
    transition: background .2s;
}
.sht-bell-badge:hover{ background: rgba(255,255,255,.12); }
.sht-bell-badge .sht-bell-num{
    position: absolute; top: 0; right: 0;
    background: #ef4444; color: #fff;
    font-size: 10px; font-weight: 800;
    border-radius: 10px;
    padding: 1px 5px; min-width: 16px; text-align: center;
    line-height: 1.2;
    box-shadow: 0 0 0 2px #5C008E;
}
.sht-bell-badge.has-count i{ animation: sht-ring 1.6s infinite; transform-origin: top center; }
</style>

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

<script>
(function(){
    'use strict';
    if (window.__SalonHatirlatma) return; // duplicate guard
    window.__SalonHatirlatma = true;

    var FEED_URL  = '/isletmeyonetim/api/hatirlatma-feed?sube={{ $isletme->id ?? 0 }}';
    var POLL_MS   = 120000;          // 2 dk'da bir feed yenile
    var POPUP_HOURS = [11, 17];      // gunde 2 kez tam ekran popup
    var SS_KEY    = 'sht.gosterildi.' + (new Date().toISOString().slice(0,10)) + '.{{ $isletme->id ?? 0 }}';
    var GOSTERILEN_TOAST = {};       // {id: timestamp}
    var SON_FEED  = [];

    function bugun(){ return new Date().toISOString().slice(0,10); }

    function getGosterimDurumu(){
        try{
            var raw = localStorage.getItem(SS_KEY);
            return raw ? JSON.parse(raw) : { popupSayisi: 0, sonPopupSaat: -1 };
        }catch(e){ return { popupSayisi: 0, sonPopupSaat: -1 }; }
    }
    function setGosterimDurumu(d){
        try{ localStorage.setItem(SS_KEY, JSON.stringify(d)); }catch(e){}
    }

    function fetchFeed(){
        $.ajax({
            url: FEED_URL,
            dataType: 'json',
            cache: false,
            success: function(res){
                var liste = (res && res.hatirlatmalar) ? res.hatirlatmalar : [];
                SON_FEED = liste;
                bellGuncelle(liste);
                otomatikToastlar(liste);
                otomatikBigPopup(liste);
            },
            error: function(){ /* sessiz */ }
        });
    }

    /* ---------- TOAST (sag alt) ---------- */
    function otomatikToastlar(liste){
        // her toast en az 2 saat tekrarlamasin (yeni kayit gelirse goster)
        var simdi = Date.now();
        liste.forEach(function(h){
            var key = h.id + ':' + (h.sayac || 0);
            var sonGosterim = GOSTERILEN_TOAST[h.id];
            if (sonGosterim && (simdi - sonGosterim) < 7200000) return;
            // yuksek oncelik (3) hemen gosterilsin; orta/dusuk sadece big popup'a kalsin
            if ((h.oncelik || 1) >= 3) {
                toastGoster(h);
                GOSTERILEN_TOAST[h.id] = simdi;
            }
        });
    }
    function toastGoster(h){
        var $el = $('<div class="sht-toast tema-' + (h.tema||'default') + '"></div>');
        $el.append(
            '<div class="sht-emoji">' + (h.emoji || '🔔') + '</div>' +
            '<div class="sht-body">' +
                '<p class="sht-baslik">' + escapeHtml(h.baslik) +
                    (h.sayac ? '<span class="sht-sayac">' + h.sayac + '</span>' : '') +
                '</p>' +
                '<p class="sht-mesaj">' + escapeHtml(h.mesaj) + '</p>' +
            '</div>' +
            '<button type="button" class="sht-kapat" aria-label="Kapat">&times;</button>'
        );
        if (h.tema === 'konfeti-parti') $el.on('click', function(e){
            if (!$(e.target).is('.sht-kapat')) konfetiPatlat($el[0]);
        });
        $el.on('click', function(e){
            if ($(e.target).is('.sht-kapat')) return;
            if (h.link) window.location.href = h.link;
        });
        $el.find('.sht-kapat').on('click', function(e){
            e.stopPropagation();
            $el.removeClass('show');
            setTimeout(function(){ $el.remove(); }, 400);
        });
        $('#salon-hatirlatma-toaster').append($el);
        if (h.tema === 'konfeti-parti') setTimeout(function(){ konfetiPatlat($el[0]); }, 600);
        setTimeout(function(){ $el.addClass('show'); }, 30);
        setTimeout(function(){
            $el.removeClass('show');
            setTimeout(function(){ $el.remove(); }, 500);
        }, 12000);
    }

    /* ---------- TAM EKRAN POPUP ---------- */
    function otomatikBigPopup(liste){
        if (!liste || !liste.length) return;
        var saat = new Date().getHours();
        if (POPUP_HOURS.indexOf(saat) === -1) return;
        var d = getGosterimDurumu();
        if (d.sonPopupSaat === saat) return;       // ayni saatte tekrar gosterme
        if (d.popupSayisi >= 2) return;            // gunluk limit
        d.popupSayisi += 1; d.sonPopupSaat = saat;
        setGosterimDurumu(d);
        bigPopupGoster(liste);
    }
    function bigPopupGoster(liste){
        if (!liste || !liste.length) return;
        var saat = new Date().getHours();
        var selam = saat < 12 ? 'Günaydın!' : (saat < 18 ? 'İyi günler!' : 'İyi akşamlar!');
        $('#sht-popup-baslik').text(selam + ' Salonunuz için ' + liste.length + ' hatırlatma var');
        $('#sht-popup-altbaslik').text('Aşağıdakileri hızlıca halletmek günü kurtarır 💪');
        var $body = $('#sht-popup-body').empty();
        liste.forEach(function(h){
            var $item = $(
                '<a class="sht-popup-item tema-' + (h.tema||'default') + '" href="' + (h.link || '#') + '">' +
                    '<div class="sht-emoji">' + (h.emoji || '🔔') + '</div>' +
                    '<div>' +
                        '<p class="sht-baslik">' + escapeHtml(h.baslik) +
                            (h.sayac ? ' <span style="color:#5C008E;font-weight:800">(' + h.sayac + ')</span>' : '') +
                        '</p>' +
                        '<p class="sht-mesaj">' + escapeHtml(h.mesaj) + '</p>' +
                        (h.altMesaj ? '<p class="sht-altmesaj">' + escapeHtml(h.altMesaj) + '</p>' : '') +
                    '</div>' +
                '</a>'
            );
            $body.append($item);
        });
        $('#salon-hatirlatma-bigpopup').addClass('show');
        // Konfeti varsa
        if (liste.some(function(h){ return h.tema === 'konfeti-parti'; })) {
            konfetiYagdir();
        }
    }
    function bigPopupKapat(){
        $('#salon-hatirlatma-bigpopup').removeClass('show');
    }
    $('#sht-btn-sonra, #sht-btn-anla').on('click', bigPopupKapat);
    $('#salon-hatirlatma-bigpopup').on('click', function(e){
        if (e.target === this) bigPopupKapat();
    });

    /* ---------- HEADER BELL ---------- */
    function bellGuncelle(liste){
        var $bell = $('#sht-bell');
        if (!$bell.length) return;
        var n = liste.length;
        if (n > 0) {
            $bell.addClass('has-count');
            $bell.find('.sht-bell-num').text(n).show();
        } else {
            $bell.removeClass('has-count');
            $bell.find('.sht-bell-num').hide();
        }
    }
    $(document).on('click', '#sht-bell', function(e){
        e.preventDefault();
        bigPopupGoster(SON_FEED);
    });

    /* ---------- KONFETI ---------- */
    var KONFETI_RENK = ['#ec4899','#f59e0b','#10b981','#3b82f6','#a855f7','#ef4444','#fbbf24'];
    function konfetiYagdir(){
        for (var i=0; i<60; i++) {
            (function(idx){
                setTimeout(function(){
                    var p = document.createElement('div');
                    p.className = 'sht-confetti';
                    p.style.left = (Math.random()*100) + 'vw';
                    p.style.background = KONFETI_RENK[Math.floor(Math.random()*KONFETI_RENK.length)];
                    p.style.transform = 'rotate(' + (Math.random()*360) + 'deg)';
                    document.body.appendChild(p);
                    var dur = 2200 + Math.random()*2000;
                    p.animate([
                        { transform: p.style.transform + ' translateY(0)', opacity: 1 },
                        { transform: 'rotate(' + (Math.random()*720) + 'deg) translateY(110vh)', opacity: 0 }
                    ], { duration: dur, easing: 'cubic-bezier(.2,.7,.4,1)' });
                    setTimeout(function(){ p.remove(); }, dur);
                }, idx*30);
            })(i);
        }
    }
    function konfetiPatlat(rect){
        var b = rect.getBoundingClientRect();
        var cx = b.left + b.width/2;
        for (var i=0; i<25; i++) {
            (function(idx){
                setTimeout(function(){
                    var p = document.createElement('div');
                    p.className = 'sht-confetti';
                    p.style.left = cx + 'px';
                    p.style.top = b.top + 'px';
                    p.style.background = KONFETI_RENK[Math.floor(Math.random()*KONFETI_RENK.length)];
                    document.body.appendChild(p);
                    var ang = Math.random()*Math.PI*2;
                    var dist = 80 + Math.random()*120;
                    var dur = 900 + Math.random()*600;
                    p.animate([
                        { transform: 'translate(0,0) rotate(0)', opacity: 1 },
                        { transform: 'translate(' + Math.cos(ang)*dist + 'px,' + (Math.sin(ang)*dist + 200) + 'px) rotate(' + (Math.random()*720) + 'deg)', opacity: 0 }
                    ], { duration: dur, easing: 'cubic-bezier(.2,.7,.4,1)' });
                    setTimeout(function(){ p.remove(); }, dur);
                }, idx*15);
            })(i);
        }
    }

    /* ---------- UTIL ---------- */
    function escapeHtml(s){
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, function(c){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
        });
    }

    /* ---------- BASLAT ---------- */
    $(function(){
        // tepe header'a bell ekle (zil ikonu)
        if (!$('#sht-bell').length && $('.header-right').length) {
            var $bell = $('<a href="#" id="sht-bell" class="sht-bell-badge" title="Hatırlatmalar"><i class="fa fa-bell"></i><span class="sht-bell-num" style="display:none">0</span></a>');
            $('.header-right').prepend($bell);
        }
        fetchFeed();
        setInterval(fetchFeed, POLL_MS);
    });
})();
</script>
