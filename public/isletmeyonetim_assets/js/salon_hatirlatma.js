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
    var POLL_MS  = 20000;             // 20 sn'de bir feed yenile
    var GOSTERILEN_TOAST = {};        // {id+sayac: timestamp}
    var SON_FEED = [];
    var ILK_POPUP_GOSTERILDI = false; // sayfa basina 1 kez tam-ekran popup
    var SON_IMZA = '';                // hatirlatma listesinin imzasi
    var SYNC_KEY = 'sht_yenile_sinyal_' + SALON_ID; // cross-tab senkron
    var POPUP_KAPATILDI_KEY = 'sht_popup_kapatildi_' + SALON_ID; // bu oturumda tam-ekran popup kapatildi mi

    function pencereleriTetikle(){
        // localStorage 'storage' event'i SADECE diger pencerelerde tetikler
        try { localStorage.setItem(SYNC_KEY, String(Date.now())); } catch(e){}
    }

    // Tam-ekran popup bir kez kapatilinca, sayfa gecislerinde otomatik geri acilmasin
    // (zil ikonundan istenince yine acilir). Boylece "kapanip hemen gene aciliyor" dongusu biter.
    function popupKapatildiMi(){
        try { return sessionStorage.getItem(POPUP_KAPATILDI_KEY) === '1'; } catch(e){ return false; }
    }
    function popupKapatildiIsaretle(){
        try { sessionStorage.setItem(POPUP_KAPATILDI_KEY, '1'); } catch(e){}
    }

    // Tiklanan / kapatilan toast'lar oturum boyu hatirlanir; sayfa yenilense bile geri gelmez.
    // (Hatirlatmanin asil isi cozulunce sayac/anahtar degisir, o zaman yine gosterilir.)
    var TOAST_KAPATILDI_KEY = 'sht_toast_kapatildi_' + SALON_ID;
    function kapatilanToastlar(){
        try { return JSON.parse(sessionStorage.getItem(TOAST_KAPATILDI_KEY) || '[]'); } catch(e){ return []; }
    }
    function toastKapatildiMi(anahtar){
        return kapatilanToastlar().indexOf(anahtar) !== -1;
    }
    function toastKapatildiIsaretle(anahtar){
        try {
            var arr = kapatilanToastlar();
            if (arr.indexOf(anahtar) === -1) { arr.push(anahtar); sessionStorage.setItem(TOAST_KAPATILDI_KEY, JSON.stringify(arr)); }
        } catch(e){}
    }

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
                // NOT: Tam-ekran popup kaldirildi (kullanici istegi). Hatirlatmalar artik
                // sadece sag-alt kartlar (toast) olarak gosterilir. otomatikBigPopup cagrilmiyor.
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
            if (toastKapatildiMi(anahtar)) return; // sadece tiklanan/kapatilan kart geri gelmez
            // Diger (kapatilmamis) kartlar her sayfada yeniden gosterilir -> "digerleri kalsin".
            GOSTERILEN_TOAST[anahtar] = Date.now();
            setTimeout(function(){ toastGoster(h); }, sirada * 350);
            sirada++;
        });
    }
    function toastGoster(h){
        var anahtar = h.id + ':' + (h.sayac || 0);
        // Ayni hatirlatmanin (ayni id) onceki kartini kaldir — sayac degisince tekrar birikmesin.
        $('#salon-hatirlatma-toaster').find('[data-id="' + escapeHtml(String(h.id)) + '"]').remove();
        var $el = $('<div class="sht-toast tema-' + (h.tema||'default') + '" data-id="' + escapeHtml(String(h.id)) + '" data-key="' + escapeHtml(anahtar) + '"></div>');
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
            if (h.link) {
                toastKapatildiIsaretle(anahtar); // gidince geri gelmesin
                window.location.href = h.link;
            }
        });
        $el.find('.sht-kapat').on('click', function(e){
            e.stopPropagation();
            toastKapatildiIsaretle(anahtar); // kapatilinca geri gelmesin
            $el.removeClass('show');
            setTimeout(function(){ $el.remove(); }, 400);
        });
        $('#salon-hatirlatma-toaster').append($el);
        if (h.tema === 'konfeti-parti') setTimeout(function(){ konfetiPatlat($el[0]); }, 600);
        setTimeout(function(){ $el.addClass('show'); }, 30);
        // NOT: Toast'lar artik otomatik kapanmaz; tepside (sag alt) kalir.
        // Kullanici ya ilgili alana tiklar (gider) ya da × ile kapatir. "Digerleri kalsin".
    }

    /* ---------- TAM EKRAN POPUP ---------- */
    var POPUP_GOSTERILDI_KEY = 'sht_popup_gosterildi_' + SALON_ID;
    function otomatikBigPopup(liste){
        if (!liste || !liste.length) { SON_IMZA = ''; return; }
        SON_IMZA = liste.map(function(h){ return h.id + ':' + (h.sayac||0); }).sort().join('|');
        // Oturumda kapatildiysa VEYA bir kez otomatik acildiysa, sayfa gecislerinde tekrar acma.
        // (Zil ikonu hep guncel listeyi acar.)
        if (popupKapatildiMi()) return;
        var oturumdaAcildi = false;
        try { oturumdaAcildi = sessionStorage.getItem(POPUP_GOSTERILDI_KEY) === '1'; } catch(e){}
        if (oturumdaAcildi || ILK_POPUP_GOSTERILDI) return;
        ILK_POPUP_GOSTERILDI = true;
        try { sessionStorage.setItem(POPUP_GOSTERILDI_KEY, '1'); } catch(e){}
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
            var $item;
            if (h.aksiyon === 'arama_baslat') {
                // Cagri Merkezi: "Hemen Ara" butonlu arama randevusu karti (numara gosterilmez)
                var sesHtml = h.son_ses
                    ? '<audio controls src="' + h.son_ses + '" style="width:100%;margin-top:6px;"></audio>'
                    : '';
                $item = $(
                    '<div class="sht-popup-item tema-' + (h.tema||'default') + '">' +
                        '<div class="sht-emoji">' + (h.emoji || '📞') + '</div>' +
                        '<div style="flex:1;">' +
                            '<p class="sht-baslik">' + escapeHtml(h.baslik) + '</p>' +
                            '<p class="sht-mesaj">' + escapeHtml(h.mesaj) + '</p>' +
                            (h.altMesaj ? '<p class="sht-altmesaj">' + escapeHtml(h.altMesaj) + '</p>' : '') +
                            sesHtml +
                            '<button type="button" class="btn btn-success sht-hemen-ara" data-aranacak-id="' + h.aranacak_musteri_id + '" style="margin-top:8px;">' +
                                '<i class="fa fa-phone"></i> ' + escapeHtml(h.cta_text || 'Hemen Ara') +
                            '</button>' +
                        '</div>' +
                    '</div>'
                );
            } else {
                $item = $(
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
            }
            $body.append($item);
        });
        $('#salon-hatirlatma-bigpopup').addClass('show');
        if (liste.some(function(h){ return h.tema === 'konfeti-parti'; })) {
            konfetiYagdir();
        }
    }
    function bigPopupKapat(){
        $('#salon-hatirlatma-bigpopup').removeClass('show');
        popupKapatildiIsaretle(); // bu oturumda otomatik geri acilmasin
        tepsiyeGoster();          // kapatinca kartlar sag-altta gorunsun (kullanici oradan islem yapar)
    }
    // Mevcut feed'deki hatirlatmalari sag-alt tepside (toast) goster.
    // Zaten ekranda olan veya kullanicinin kapattigi kartlari tekrar acmaz.
    function tepsiyeGoster(){
        var liste = SON_FEED || [];
        var sirada = 0;
        liste.forEach(function(h){
            var anahtar = h.id + ':' + (h.sayac || 0);
            if (toastKapatildiMi(anahtar)) return; // kullanici bunu kapatmis
            if ($('#salon-hatirlatma-toaster').find('[data-id="' + escapeHtml(String(h.id)) + '"]').length) return; // zaten ekranda
            GOSTERILEN_TOAST[anahtar] = Date.now();
            (function(gecikme){ setTimeout(function(){ toastGoster(h); }, gecikme); })(sirada * 200);
            sirada++;
        });
    }
    /* ---------- CAGRI MERKEZI: Hemen Ara (KVKK uyumlu, numara gosterilmez) ---------- */
    $(document).on('click', '.sht-hemen-ara', function(e){
        e.preventDefault();
        e.stopPropagation();
        aramaRandevusuAra($(this).data('aranacak-id'));
    });
    function aramaRandevusuAra(aranacakId){
        // Santral originate — numara tarayiciya gitmez; personelin Bria'si calar.
        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
        $.ajax({
            url: '/isletmeyonetim/arama-baslat',
            method: 'POST',
            data: { aranacak_musteri_id: aranacakId, _token: token },
            success: function(res){
                if (res.success) {
                    bigPopupKapat();
                    fetchFeed(true);
                    if (typeof swal === 'function') {
                        swal({ type:'success', title:'Arama başlatıldı', text: res.message || 'Telefonunuz (Bria) çalacak, açın.', timer:3500, showConfirmButton:false });
                    }
                } else {
                    alert(res.message || 'Arama başlatılamadı.');
                }
            },
            error: function(xhr){
                alert((xhr.responseJSON && xhr.responseJSON.message) || 'Arama başlatılamadı.');
            }
        });
    }

    $(document).on('click', '#sht-btn-sonra, #sht-btn-anla', bigPopupKapat);
    $(document).on('click', '#salon-hatirlatma-bigpopup', function(e){
        if (e.target === this) bigPopupKapat();
    });
    // NOT: Popup satirlari normal link gibi davranir; tiklayinca dogrudan ilgili sayfaya gider.
    // "Oturumda bir kez ac" guard'i (POPUP_GOSTERILDI_KEY) sayesinde navigasyon sonrasi
    // popup yeniden acilmaz -> eski "kapanip gene aciliyor" dongusu olusmaz.

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
            setTimeout(function(){
                fetchFeed(true);
                pencereleriTetikle(); // diger sekmeler/pencereler de yenile
            }, 600);
        }catch(e){}
    });

    /* ---------- CROSS-TAB SYNC: diger pencereden sinyal gelirse fetch ---------- */
    window.addEventListener('storage', function(e){
        if (e.key === SYNC_KEY && e.newValue) {
            fetchFeed(true);
        }
    });

    /* ---------- BASLAT ---------- */
    $(function(){
        // NOT: Header'daki can/zil ikonu kaldirildi (kullanici istegi).
        // Hatirlatmalar ilk popup + sag-alt kartlar uzerinden yonetilir.
        fetchFeed();
        setInterval(function(){ fetchFeed(false); }, POLL_MS);
        // sayfaya geri donulunce hemen yenile (sekme arasi gecis)
        document.addEventListener('visibilitychange', function(){
            if (!document.hidden) fetchFeed(true);
        });
    });
})();
