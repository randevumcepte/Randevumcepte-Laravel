/*
 * Senaryo Sihirbazı — salonların adım adım konuşma/mesaj senaryosu oluşturması.
 *
 * Akış: 1) Kanal + hazır senaryo  2) Adım metinleri (placeholder chip'leri)
 *       3) Aksiyonlar  4) Önizleme (+sesli) & Kaydet
 *
 * Konuşma dallanmaları santral dialplan/AGI'de sabit; burada yalnızca METİNLER ve
 * hangi AKSİYONLARIN açık olduğu belirlenir.
 */
(function(){
    'use strict';

    var presetler = {};
    var placeholders = [];
    var adim = 1;
    var sonFocusTextarea = null;

    // Santral Arama'da gösterilecek adım alanları (sıra önemli)
    var ARAMA_ALANLARI = [
        { key:'acilis',        baslik:'Açılış',                 ipucu:'Bot ilk bunu okur' },
        { key:'soru_kod',      baslik:'İndirim kodu sorusu',    ipucu:'"…göndermemi ister misiniz?"' },
        { key:'onay_kod',      baslik:'Kod gönderildi mesajı',  ipucu:'Müşteri evet dedikten sonra' },
        { key:'soru_randevu',  baslik:'Randevu sorusu',         ipucu:'"…randevu oluşturayım mı?"' },
        { key:'soru_gun_saat', baslik:'Gün/saat sorusu',        ipucu:'"Hangi gün ve saat olsun?"' },
        { key:'kapanis',       baslik:'Kapanış',                ipucu:'Randevu sonrası veda' },
        { key:'red',           baslik:'Müşteri "hayır" derse',  ipucu:'Kibar kapanış' }
    ];
    var TEK_METIN_ALANI = [
        { key:'acilis', baslik:'Mesaj metni', ipucu:'Müşteriye gidecek metin' }
    ];

    function esc(s){ return $('<div>').text(s == null ? '' : s).html(); }
    function token(){ return $('input[name="_token"]').val(); }
    function sube(){ return $('input[name="sube"]').val(); }
    function kanal(){ return parseInt($('#senaryo_sihirbaz_modal .ssb-kanal-btn.is-active').data('kanal'), 10) || 1; }
    function aramaMi(){ return kanal() === 1; }

    function alanlar(){ return aramaMi() ? ARAMA_ALANLARI : TEK_METIN_ALANI; }

    /* ---------- Adım (pane) yönetimi ---------- */
    function adimGoster(n){
        // Arama değilse "Aksiyonlar" adımını atla
        if(n === 3 && !aramaMi()) n = (adim < 3) ? 4 : 2;
        adim = n;

        $('#senaryo_sihirbaz_modal .ssb-pane').hide();
        $('#senaryo_sihirbaz_modal .ssb-pane[data-pane="'+n+'"]').show();

        $('#senaryo_sihirbaz_modal .ssb-step').each(function(){
            var s = parseInt($(this).data('step'), 10);
            $(this).toggleClass('is-active', s === n).toggleClass('is-done', s < n);
        });

        $('#ssbGeri').toggle(n > 1);
        $('#ssbIleri').toggle(n < 4);
        $('#ssbKaydet').toggle(n === 4);

        if(n === 2) metinAlanlariCiz();
        if(n === 4) onizlemeYukle();
    }

    /* ---------- Adım 1: presetler ---------- */
    function presetleriCiz(){
        var html = '';
        Object.keys(presetler).forEach(function(tip){
            var p = presetler[tip];
            var aktif = (tip === $('#ssbSenaryoTipi').val()) ? ' is-active' : '';
            html += '<button type="button" class="ssb-preset'+aktif+'" data-tip="'+tip+'">'
                  + '<span class="ssb-preset-emoji">'+(p.emoji||'✨')+'</span>'
                  + '<span class="ssb-preset-ad">'+esc(p.ad)+'</span>'
                  + '<span class="ssb-preset-ac">'+esc(p.aciklama||'')+'</span>'
                  + '</button>';
        });
        $('#ssbPresetler').html(html);
    }

    function presetUygula(tip){
        var p = presetler[tip];
        if(!p) return;
        $('#ssbSenaryoTipi').val(tip);
        $('#senaryo_sihirbaz_modal .ssb-preset').removeClass('is-active');
        $('#senaryo_sihirbaz_modal .ssb-preset[data-tip="'+tip+'"]').addClass('is-active');

        // metinleri + aksiyonları belleğe al (adım 2/3 bunları kullanır)
        $('#senaryo_sihirbaz_modal').data('adimlar', $.extend({}, p.adimlar || {}));
        var a = p.aksiyonlar || {};
        $('#ssbAksIndirim').prop('checked',   !!a.indirim_kodu_sms);
        $('#ssbAksYolTarifi').prop('checked', !!a.yol_tarifi_sms);
        $('#ssbAksRandevu').prop('checked',   !!a.randevu_olustur);

        if(!$('#ssbSenaryoAd').val()) $('#ssbSenaryoAd').val(p.ad || '');
    }

    /* ---------- Adım 2: metin alanları ---------- */
    function metinAlanlariCiz(){
        var mevcut = $('#senaryo_sihirbaz_modal').data('adimlar') || {};
        var html = '';
        alanlar().forEach(function(a){
            html += '<div class="ssb-alan">'
                  + '<div class="ssb-alan-baslik">'+esc(a.baslik)+' <small>'+esc(a.ipucu)+'</small></div>'
                  + '<textarea data-key="'+a.key+'" rows="2">'+esc(mevcut[a.key] || '')+'</textarea>'
                  + '</div>';
        });
        $('#ssbMetinAlanlari').html(html);

        var chips = '';
        placeholders.forEach(function(p){
            chips += '<button type="button" class="ssb-chip" data-kod="'+esc(p.kod)+'" title="'+esc(p.aciklama)+'">'+esc(p.kod)+'</button>';
        });
        $('#ssbPlaceholderChips').html(chips);
    }

    function adimlariTopla(){
        var d = $.extend({}, $('#senaryo_sihirbaz_modal').data('adimlar') || {});
        $('#ssbMetinAlanlari textarea').each(function(){
            d[$(this).data('key')] = $(this).val();
        });
        $('#senaryo_sihirbaz_modal').data('adimlar', d);
        return d;
    }

    function aksiyonlariTopla(){
        if(!aramaMi()) return { indirim_kodu_sms:false, yol_tarifi_sms:false, randevu_olustur:false };
        return {
            indirim_kodu_sms: $('#ssbAksIndirim').is(':checked'),
            yol_tarifi_sms:   $('#ssbAksYolTarifi').is(':checked'),
            randevu_olustur:  $('#ssbAksRandevu').is(':checked')
        };
    }

    /* ---------- Adım 4: önizleme ---------- */
    function onizlemeYukle(){
        $('#ssbDiyalog').html('<div class="text-muted" style="font-size:13px;"><i class="fa fa-spinner fa-spin"></i> Hazırlanıyor...</div>');
        $.ajax({
            url: '/isletmeyonetim/senaryo-onizle',
            method: 'POST',
            dataType: 'json',
            data: {
                sube: sube(),
                gorev_turu: kanal(),
                adimlar: JSON.stringify(adimlariTopla()),
                aksiyonlar: JSON.stringify(aksiyonlariTopla()),
                _token: token()
            },
            success: function(res){
                if(!res || !res.success){ $('#ssbDiyalog').html('<div class="text-danger">Önizleme oluşturulamadı.</div>'); return; }
                var html = '';
                (res.diyalog || []).forEach(function(b){
                    html += '<div class="ssb-balon ssb-balon--'+(b.kim === 'bot' ? 'bot' : 'musteri')+'">'+esc(b.metin)+'</div>';
                });
                $('#ssbDiyalog').html(html || '<div class="text-muted">Metin girilmemiş.</div>');
            },
            error: function(){ $('#ssbDiyalog').html('<div class="text-danger">Önizleme oluşturulamadı.</div>'); }
        });
    }

    /* ---------- Sesli önizleme (tarayıcı TTS) ---------- */
    function sesliOnizle(){
        if(!('speechSynthesis' in window)){
            if(typeof swal === 'function') swal({type:'info', title:'Desteklenmiyor', text:'Tarayıcınız sesli önizlemeyi desteklemiyor.', timer:2500, showConfirmButton:false});
            return;
        }
        window.speechSynthesis.cancel();
        var botLines = $('#ssbDiyalog .ssb-balon--bot').map(function(){ return $(this).text(); }).get();
        if(!botLines.length) return;
        botLines.forEach(function(t){
            var u = new SpeechSynthesisUtterance(t);
            u.lang = 'tr-TR'; u.rate = 1.05;
            window.speechSynthesis.speak(u);
        });
    }

    /* ---------- Kaydet ---------- */
    function kaydet(){
        var ad = ($('#ssbSenaryoAd').val() || '').trim();
        if(!ad){
            if(typeof swal === 'function') swal({type:'warning', title:'Senaryo adı gerekli', timer:2000, showConfirmButton:false});
            $('#ssbSenaryoAd').focus();
            return;
        }
        var $btn = $('#ssbKaydet'); var eski = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');

        $.ajax({
            url: '/isletmeyonetim/senaryo-kaydet',
            method: 'POST',
            dataType: 'json',
            data: {
                sube: sube(),
                senaryo_id: $('#ssbSenaryoId').val() || '',
                ad: ad,
                senaryo_tipi: $('#ssbSenaryoTipi').val(),
                gorev_turu: kanal(),
                adimlar: JSON.stringify(adimlariTopla()),
                aksiyonlar: JSON.stringify(aksiyonlariTopla()),
                _token: token()
            },
            success: function(res){
                $btn.prop('disabled', false).html(eski);
                if(!res || !res.success){
                    if(typeof swal === 'function') swal({type:'error', title:'Hata', text:(res && res.message) || 'Kaydedilemedi', timer:2500, showConfirmButton:false});
                    return;
                }
                $('#senaryo_sihirbaz_modal').modal('hide');
                if(typeof swal === 'function') swal({type:'success', title:'Kaydedildi', text:'Senaryo şablon listenize eklendi.', timer:2000, showConfirmButton:false});
                if(typeof kampanyaSablonGetir === 'function') kampanyaSablonGetir();
            },
            error: function(){
                $btn.prop('disabled', false).html(eski);
                if(typeof swal === 'function') swal({type:'error', title:'Hata', text:'Senaryo kaydedilemedi', timer:2500, showConfirmButton:false});
            }
        });
    }

    /* ---------- Açılış / veri yükleme ---------- */
    function sihirbaziAc(senaryo){
        $('#ssbSenaryoId').val(senaryo ? senaryo.id : '');
        $('#ssbSenaryoAd').val(senaryo ? senaryo.ad : '');
        $('#ssbSenaryoTipi').val(senaryo ? senaryo.senaryo_tipi : 'geri_kazanim');

        var k = senaryo ? senaryo.gorev_turu : 1;
        $('#senaryo_sihirbaz_modal .ssb-kanal-btn').removeClass('is-active');
        $('#senaryo_sihirbaz_modal .ssb-kanal-btn[data-kanal="'+k+'"]').addClass('is-active');

        $.get('/isletmeyonetim/senaryo-presetleri', function(res){
            if(res && res.success){
                presetler = res.presetler || {};
                placeholders = res.placeholders || [];
            }
            presetleriCiz();
            if(senaryo){
                $('#senaryo_sihirbaz_modal').data('adimlar', senaryo.adimlar || {});
                var a = senaryo.aksiyonlar || {};
                $('#ssbAksIndirim').prop('checked', !!a.indirim_kodu_sms);
                $('#ssbAksYolTarifi').prop('checked', !!a.yol_tarifi_sms);
                $('#ssbAksRandevu').prop('checked', !!a.randevu_olustur);
            } else {
                presetUygula($('#ssbSenaryoTipi').val());
            }
            adimGoster(1);
            $('#senaryo_sihirbaz_modal').modal('show');
        }, 'json');
    }
    window.senaryoSihirbaziAc = sihirbaziAc;

    /* ---------- Olaylar ---------- */
    $(document).on('click', '#senaryoSihirbaziBtn', function(e){
        e.preventDefault();
        // Reklam modalını kapat, sihirbazı aç; kapanınca reklam modalı geri gelsin
        sessionStorage.setItem('ssbReklamGeriDon', '1');
        $('#yeni_kampanya_modal').modal('hide');
        setTimeout(function(){ sihirbaziAc(null); }, 350);
    });

    $(document).on('hidden.bs.modal', '#senaryo_sihirbaz_modal', function(){
        window.speechSynthesis && window.speechSynthesis.cancel();
        if(sessionStorage.getItem('ssbReklamGeriDon') === '1'){
            sessionStorage.removeItem('ssbReklamGeriDon');
            setTimeout(function(){ $('#yeni_kampanya_modal').modal('show'); }, 300);
        }
    });

    // Kanal değişimi
    $(document).on('click', '#senaryo_sihirbaz_modal .ssb-kanal-btn', function(){
        $('#senaryo_sihirbaz_modal .ssb-kanal-btn').removeClass('is-active');
        $(this).addClass('is-active');
        $('#ssbKanalHint').html(aramaMi()
            ? 'Santral Arama\'da bot açılışı okur, müşteri <b>evet/hayır</b> der; kod SMS\'i ve randevu adımları otomatik ilerler.'
            : 'Bu kanalda tek bir mesaj metni gönderilir; konuşma adımları ve aksiyonlar kullanılmaz.');
    });

    // Preset seçimi
    $(document).on('click', '#senaryo_sihirbaz_modal .ssb-preset', function(){
        presetUygula($(this).data('tip'));
    });

    // Placeholder chip -> odaktaki textarea'ya imleç konumuna ekle
    $(document).on('focus', '#ssbMetinAlanlari textarea', function(){ sonFocusTextarea = this; });
    $(document).on('click', '#senaryo_sihirbaz_modal .ssb-chip', function(){
        var kod = $(this).data('kod');
        var ta = sonFocusTextarea || $('#ssbMetinAlanlari textarea').first()[0];
        if(!ta) return;
        var s = ta.selectionStart || 0, e = ta.selectionEnd || 0;
        ta.value = ta.value.substring(0, s) + kod + ta.value.substring(e);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = s + kod.length;
    });

    // Navigasyon
    $(document).on('click', '#ssbIleri', function(){ if(adim === 2) adimlariTopla(); adimGoster(adim + 1); });
    $(document).on('click', '#ssbGeri',  function(){ if(adim === 2) adimlariTopla(); adimGoster(adim - 1); });
    $(document).on('click', '#ssbSesliOnizle', sesliOnizle);
    $(document).on('click', '#ssbKaydet', kaydet);

    /* ---------- Şablon listesindeki senaryo düzenle / sil ---------- */
    $(document).on('click', 'a[name="senaryoDuzenle"]', function(e){
        e.stopPropagation(); e.preventDefault();
        var id = $(this).attr('data-value');
        $.get('/isletmeyonetim/senaryo-liste', { sube: sube() }, function(res){
            if(!res || !res.success) return;
            var s = (res.senaryolar || []).filter(function(x){ return String(x.id) === String(id); })[0];
            if(!s) return;
            sessionStorage.setItem('ssbReklamGeriDon', '1');
            $('#yeni_kampanya_modal').modal('hide');
            setTimeout(function(){ sihirbaziAc(s); }, 350);
        }, 'json');
    });

    $(document).on('click', 'a[name="senaryoSil"]', function(e){
        e.stopPropagation(); e.preventDefault();
        var id = $(this).attr('data-value');
        var sil = function(){
            $.ajax({
                url: '/isletmeyonetim/senaryo-sil',
                method: 'POST',
                dataType: 'json',
                data: { senaryo_id: id, sube: sube(), _token: token() },
                success: function(){ if(typeof kampanyaSablonGetir === 'function') kampanyaSablonGetir(); }
            });
        };
        if(typeof swal === 'function'){
            swal({ title:'Emin misiniz?', html:'Senaryo silinecek.', type:'warning', showCancelButton:true,
                   confirmButtonColor:'#dc2626', confirmButtonText:'Sil', cancelButtonText:'Vazgeç' })
              .then(function(r){ if(r.value) sil(); });
        } else if(confirm('Senaryo silinsin mi?')) sil();
    });
})();
