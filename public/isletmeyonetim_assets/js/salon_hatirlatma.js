/* =================================================================
 * SALON AKILLI HATIRLATMA SISTEMI - JS
 * v1.0
 * Layout'ta window.SHT_AYARLAR = { salon_id: N } set edilmelidir.
 * ================================================================= */
(function(){
    'use strict';
    if (window.__SalonHatirlatma) return; // duplicate guard
    window.__SalonHatirlatma = true;

    var SALON_ID = (window.SHT_AYARLAR && window.SHT_AYARLAR.salon_id) || 0;
    var FEED_URL = '/isletmeyonetim/api/hatirlatma-feed?sube=' + SALON_ID;
    var POLL_MS  = 30000;             // 30 sn'de bir feed yenile
    var GOSTERILEN_TOAST = {};        // {id+sayac: timestamp}
    var SON_FEED = [];
    var ILK_POPUP_GOSTERILDI = false; // sayfa basina 1 kez tam-ekran popup
    var SON_IMZA = '';                // hatirlatma listesinin imzasi

    function fetchFeed(force){
        var url = FEED_URL + (force ? '&refresh=1' : '');
        $.ajax({
            url: url,
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
    window.SalonHatirlatmaYenile = function(){ fetchFeed(true); };

    /* ---------- TOAST (sag alt) ---------- */
    function otomatikToastlar(liste){
        var sirada = 0;
        liste.forEach(function(h){
            var anahtar = h.id + ':' + (h.sayac || 0);
            if (GOSTERILEN_TOAST[anahtar]) return;
            GOSTERILEN_TOAST[anahtar] = Date.now();
            setTimeout(function(){ toastGoster(h); }, sirada * 350);
            sirada++;
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
        if (!liste || !liste.length) { SON_IMZA = ''; return; }
        var imza = liste.map(function(h){ return h.id + ':' + (h.sayac||0); }).sort().join('|');
        if (!ILK_POPUP_GOSTERILDI) {
            ILK_POPUP_GOSTERILDI = true;
            SON_IMZA = imza;
            bigPopupGoster(liste);
            return;
        }
        if (imza !== SON_IMZA) {
            SON_IMZA = imza;
            if (!$('#salon-hatirlatma-bigpopup').hasClass('show')) {
                bigPopupGoster(liste);
            }
        }
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
        if (liste.some(function(h){ return h.tema === 'konfeti-parti'; })) {
            konfetiYagdir();
        }
    }
    function bigPopupKapat(){
        $('#salon-hatirlatma-bigpopup').removeClass('show');
    }
    $(document).on('click', '#sht-btn-sonra, #sht-btn-anla', bigPopupKapat);
    $(document).on('click', '#salon-hatirlatma-bigpopup', function(e){
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

    /* ---------- AJAX COMPLETE: yazma islemi sonrasi anlik yenile ---------- */
    var TETIKLEYICI_RE = /(musteri|portfoy|randevu|alacak|adisyon|tahsilat|odeme|maas|prim|kvkk|destek)/i;
    $(document).ajaxComplete(function(evt, xhr, settings){
        try{
            var t = (settings.type || '').toUpperCase();
            if (t !== 'POST' && t !== 'PUT' && t !== 'DELETE') return;
            var url = (settings.url || '');
            if (url.indexOf('/api/hatirlatma-feed') !== -1) return;
            if (!TETIKLEYICI_RE.test(url)) return;
            setTimeout(function(){ fetchFeed(true); }, 600);
        }catch(e){}
    });

    /* ---------- BASLAT ---------- */
    $(function(){
        if (!$('#sht-bell').length && $('.header-right').length) {
            var $bell = $('<a href="#" id="sht-bell" class="sht-bell-badge" title="Hatırlatmalar"><i class="fa fa-bell"></i><span class="sht-bell-num" style="display:none">0</span></a>');
            $('.header-right').prepend($bell);
        }
        fetchFeed();
        setInterval(function(){ fetchFeed(false); }, POLL_MS);
    });
})();
