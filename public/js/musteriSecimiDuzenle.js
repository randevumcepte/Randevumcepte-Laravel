/*
 * Grup Düzenle modalı (#grup_sms_duzenle_modal) — tablo tabanlı katılımcı yönetimi.
 *
 * Akış:
 *   - "Düzenle" (button[name="grup_duzenle"]) tıklanınca /grup-katilimci-bilgi ile
 *     grup adı + katılımcılar + aday müşteriler çekilir; tablo, sayaç ve ekleme
 *     dropdown'ı doldurulur.
 *   - Katılımcı Ekle / Çıkar: tabloda anlık (staging) düzenlenir.
 *   - Grubu Güncelle: tablodaki user_id'ler /grupsmsekle ile kaydedilir; ardından
 *     Gruplar listesi (#grup_sms_tablo) tazelenir.
 *
 * Not: Eski grid tabanlı (MusteriSecimiDuzenle class) uygulama, blade'deki tablo
 * modalıyla uyumsuzdu ve /grup-bilgileri-getir uçları yoktu; bu dosya o yüzden
 * modalın gerçek yapısına göre yeniden yazıldı.
 */
(function(){
    'use strict';

    var adaylar = []; // eklenebilecek müşteriler: [{id, ad_soyad, telefon}]

    function esc(s){ return $('<div>').text(s == null ? '' : s).html(); }
    function token(){ return $('input[name="_token"]').val(); }
    function sube(){
        return $('#grup_sms_duzenle_modal input[name="sube"]').val() || $('input[name="sube"]').val();
    }

    function katilimciSatir(k){
        return '<tr data-uid="'+k.id+'" data-tel="'+esc(k.telefon||'')+'">'
            + '<td>'+esc(k.ad_soyad)+'</td>'
            + '<td>'+esc(k.telefon || '-')+'</td>'
            + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger grupKatilimciSil" title="Gruptan çıkar"><i class="fa fa-trash"></i></button></td>'
            + '</tr>';
    }

    function sayiGuncelle(){
        var n = $('#grup_katilimci_tablosu tbody tr[data-uid]').length;
        $('#grup_katilimci_sayisi').text(n);
        $('#grup_katilimci_empty').toggle(n === 0);
    }

    function dropdownDoldur(){
        var $sel = $('#katilimciSecimSelectDuzenle');
        var html = '<option value="">Müşteri seçin...</option>';
        adaylar.forEach(function(a){
            html += '<option value="'+a.id+'">'+esc(a.ad_soyad)+(a.telefon ? ' ('+esc(a.telefon)+')' : '')+'</option>';
        });
        if($sel.hasClass('select2-hidden-accessible')){ try { $sel.select2('destroy'); } catch(e){} }
        $sel.html(html);
        if($.fn.select2){
            try {
                $sel.select2({ dropdownParent: $('#grup_sms_duzenle_modal'), placeholder: 'Müşteri seçin...', width: '100%' });
            } catch(e){}
        }
    }

    // "Düzenle" -> modalı doldur (buton data-toggle ile modalı zaten açıyor)
    $(document).on('click', '#grup_sms_tablo button[name="grup_duzenle"]', function(){
        var grupId  = $(this).attr('data-value');
        var grupAdi = $(this).closest('tr').children('td').eq(0).text().trim();

        $('#grup_id_duzenle').val(grupId);
        $('#grup_adi_duzenle').val(grupAdi);
        $('#grupKatilimciArama').val('');
        $('#grup_katilimci_sayisi').text('0');
        $('#grup_katilimci_empty').hide();
        $('#grup_katilimci_tablosu tbody').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</td></tr>');
        adaylar = [];
        dropdownDoldur();

        $.ajax({
            url: '/isletmeyonetim/grup-katilimci-bilgi',
            method: 'POST',
            dataType: 'json',
            data: { grup_id: grupId, sube: sube(), _token: token() },
            success: function(res){
                if(!res || !res.success){
                    $('#grup_katilimci_tablosu tbody').empty();
                    sayiGuncelle();
                    return;
                }
                if(res.grup_adi) $('#grup_adi_duzenle').val(res.grup_adi);

                var rows = '';
                (res.katilimcilar || []).forEach(function(k){ rows += katilimciSatir(k); });
                $('#grup_katilimci_tablosu tbody').html(rows);

                adaylar = res.adaylar || [];
                dropdownDoldur();
                sayiGuncelle();
            },
            error: function(){
                $('#grup_katilimci_tablosu tbody').html('<tr><td colspan="3" class="text-center text-danger py-3">Katılımcılar yüklenemedi</td></tr>');
            }
        });
    });

    // Katılımcı ekle
    $(document).on('click', '#katilimciEkleBtnDuzenle', function(e){
        e.preventDefault();
        var $sel = $('#katilimciSecimSelectDuzenle');
        var uid = $sel.val();
        if(!uid) return;
        if($('#grup_katilimci_tablosu tbody tr[data-uid="'+uid+'"]').length) return;

        var idx = -1;
        for(var i = 0; i < adaylar.length; i++){ if(String(adaylar[i].id) === String(uid)){ idx = i; break; } }
        var aday = idx > -1 ? adaylar[idx] : { id: uid, ad_soyad: $sel.find('option:selected').text(), telefon: '' };

        $('#grup_katilimci_tablosu tbody tr').filter(function(){ return !$(this).attr('data-uid'); }).remove(); // "yükleniyor/boş" satırı temizle
        $('#grup_katilimci_tablosu tbody').append(katilimciSatir(aday));
        if(idx > -1) adaylar.splice(idx, 1);
        dropdownDoldur();
        sayiGuncelle();
    });

    // Katılımcı çıkar
    $(document).on('click', '#grup_katilimci_tablosu .grupKatilimciSil', function(){
        var $tr = $(this).closest('tr');
        adaylar.push({ id: $tr.attr('data-uid'), ad_soyad: $tr.children('td').eq(0).text(), telefon: $tr.attr('data-tel') });
        adaylar.sort(function(a,b){ return String(a.ad_soyad).localeCompare(String(b.ad_soyad), 'tr'); });
        $tr.remove();
        dropdownDoldur();
        sayiGuncelle();
    });

    // Katılımcı ara (tablo içi filtre)
    $(document).on('input', '#grupKatilimciArama', function(){
        var q = ($(this).val() || '').toLowerCase();
        $('#grup_katilimci_tablosu tbody tr[data-uid]').each(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1);
        });
    });

    // Grubu Güncelle (kaydet)
    $(document).on('submit', '#grup_sms_formuDuzenle', function(e){
        e.preventDefault();
        var grupAdi = ($('#grup_adi_duzenle').val() || '').trim();
        if(!grupAdi){
            if(typeof swal === 'function') swal({ type:'warning', title:'Grup adı gerekli', timer:2000, showConfirmButton:false });
            return;
        }
        var ids = $('#grup_katilimci_tablosu tbody tr[data-uid]').map(function(){ return $(this).attr('data-uid'); }).get();

        var $btn = $('#grup_sms_formuDuzenle button[type="submit"]');
        var eski = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');

        $.ajax({
            url: '/isletmeyonetim/grupsmsekle',
            method: 'POST',
            dataType: 'json',
            data: {
                grup_id: $('#grup_id_duzenle').val(),
                grup_adi: grupAdi,
                sube: sube(),
                musteri_idler: JSON.stringify(ids),
                _token: token()
            },
            success: function(res){
                $btn.prop('disabled', false).html(eski);
                $('#grup_sms_duzenle_modal').modal('hide');
                if(res && res.grup && $.fn.DataTable){
                    if($.fn.dataTable.isDataTable('#grup_sms_tablo')) $('#grup_sms_tablo').DataTable().destroy();
                    $('#grup_sms_tablo').DataTable({
                        columns: [
                            { data: 'grup_adi', className: 'text-center' },
                            { data: 'grup_katilimci_sayisi', className: 'text-center' },
                            { data: 'islemler', className: 'text-right' }
                        ],
                        data: res.grup,
                        language: { url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json', searchPlaceholder: 'Ara' }
                    });
                }
                if(typeof swal === 'function') swal({ type:'success', title:'Başarılı', text:'Grup güncellendi', timer:2000, showConfirmButton:false });
            },
            error: function(){
                $btn.prop('disabled', false).html(eski);
                if(typeof swal === 'function') swal({ type:'error', title:'Hata', text:'Grup güncellenemedi', timer:2500, showConfirmButton:false });
            }
        });
    });
})();
