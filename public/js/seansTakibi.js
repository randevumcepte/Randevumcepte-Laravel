// global değişken
// global değişken
var seansTablo;

// air-datepicker icin Turkce dil tanimi. Kutuphanede 'tr' dosyasi yok ve varsayilan
// dil 'ru'; bu obje sadece seans tarihi secicisine verilir, global kayit yapilmaz
// (baska sayfalardaki datepicker'lar etkilenmesin).
// Popup'taki seans tarihini oku. Alan elle yazilabildigi icin bicimi dogrula:
// gecerli YYYY-MM-DD degilse null don (cagiran uyarir), bos ise '' don
// (backend bos tarihte bugune duser).
function seansTarihiOku() {
    var deger = $.trim($('#seansTarihiYeni').val() || '');
    if (deger === '') return '';
    if (!/^\d{4}-\d{2}-\d{2}$/.test(deger)) return null;
    var p = deger.split('-');
    var d = new Date(+p[0], +p[1] - 1, +p[2]);
    // Takvimde olmayan tarihleri ele (orn. 2026-02-31 -> 3 Mart'a kayar)
    if (d.getFullYear() !== +p[0] || d.getMonth() !== +p[1] - 1 || d.getDate() !== +p[2]) return null;
    return deger;
}

// Gecersiz tarihte kullaniciyi popup'i kapatmadan uyar (yeni bir swal acmak
// mevcut popup'in yerine gecer ve secim kaybolurdu).
function seansTarihUyar() {
    var $inp = $('#seansTarihiYeni');
    $inp.css({'border':'1px solid #e53e3e','background':'#fff5f5'});
    if (!$inp.next('.seans-tarih-hata').length) {
        $inp.after("<div class='seans-tarih-hata' style='color:#e53e3e; font-size:12px; margin-top:6px;'>Geçerli bir tarih girin (YYYY-AA-GG) veya takvimden seçin.</div>");
    }
    $inp.focus();
}

function seansTarihUyariTemizle() {
    $('#seansTarihiYeni').css({'border':'','background':'#fff'});
    $('.seans-tarih-hata').remove();
}

var seansTarihLocale = {
    days: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
    daysShort: ['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'],
    daysMin: ['Pz','Pt','Sa','Ça','Pe','Cu','Ct'],
    months: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
    monthsShort: ['Oca','Şub','Mar','Nis','May','Haz','Tem','Ağu','Eyl','Eki','Kas','Ara'],
    today: 'Bugün',
    clear: 'Temizle',
    dateFormat: 'yyyy-mm-dd',
    timeFormat: 'hh:ii',
    firstDay: 1
};

function seanslariGetir(rowId) {
    if (rowId === undefined || rowId === null || rowId === '') {

        if ($.fn.DataTable.isDataTable('#seans_takip_liste')) {
            $('#seans_takip_liste').DataTable().destroy();
        }

        seansTablo = $('#seans_takip_liste').DataTable({
            processing: true,
            serverSide: true,
            rowId: 'id', // Artık satırları id ile bulabiliriz
            stateDuration: 0,
            ajax: {
                url: '/isletmeyonetim/seansGetir',
                data: function(d) {
                    d.musteriid = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
                    d.sube = $('input[name="sube"]').val();
                }
            },
            columns: [
                { data: 'id', visible: false },
                { data: 'musteri', visible: !$('#musteriKarti').length },
                { data: 'baslangic_tarihi' },
                { data: 'paket_adi' },
                { data: 'durum' },
                { data: 'islemler' }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                searchPlaceholder: "Ara"
            }
        });

    } else {
        var table = $('#seans_takip_liste').DataTable();

        // Yeni veriyi server'dan çek
        $.ajax({
            url: '/isletmeyonetim/seansGetir',
            data: {
                paketHizmetId: rowId,
                musteriid: $('#musteriKarti').length ? $('#musteriKarti').val() : ''
            },
            success: function(yeniVeri) {
                if (Array.isArray(yeniVeri) && yeniVeri.length > 0) {
                    yeniVeri = yeniVeri[0];
                }

                // Satırı bul ve child row açık mı kontrol et
                var oldRow = table.row('#' + rowId);
                var wasShown = oldRow.any() ? oldRow.child.isShown() : false;

                if (oldRow.any()) {
                    var mevcutVeri = oldRow.data();

                    // Yeni veri ile mevcut veriyi birleştir
                    var guncelVeri = {
                        id: mevcutVeri.id,
                        musteri: yeniVeri.musteri || mevcutVeri.musteri,
                        baslangic_tarihi: yeniVeri.baslangic_tarihi || mevcutVeri.baslangic_tarihi,
                        paket_adi: yeniVeri.paket_adi || mevcutVeri.paket_adi,
                        durum: yeniVeri.durum || mevcutVeri.durum,
                        islemler: yeniVeri.islemler || mevcutVeri.islemler,
                        hizmet_detaylari: yeniVeri.hizmet_detaylari || mevcutVeri.hizmet_detaylari || '[]'
                    };

                    console.log("Güncellenmiş veri:", guncelVeri);
                    oldRow.data(guncelVeri);

                    // Eğer child row açıksa server'dan gelen güncel veri ile tekrar aç
                    if (wasShown) {
                        table.ajax.reload(function () {
                            var row = table.row('#' + rowId);
                            if (!row.any()) return;

                            var trNode = $(row.node());
                            var hizmetData = row.data().hizmet_detaylari;

                            if (typeof hizmetData === "string") {
                                try {
                                    hizmetData = JSON.parse(hizmetData);
                                } catch(e) {
                                    hizmetData = [];
                                }
                            }

                            var detayHtml = formatHizmetDetaylari(hizmetData);

                            row.child(detayHtml).show();
                            trNode.addClass('shown');
                            trNode.find('.toggle-paket-detay').html('<i class="fa fa-chevron-up"></i>');

                        }, false); // false: pagination ve state bozulmasın
                    }
                }
            }
        });
    }
}

// Child row toggle
$(document).on('click', '.toggle-paket-detay', function() {
    var table = $('#seans_takip_liste').DataTable();
    var tr = $(this).closest('tr');
    var button = $(this);

    if (tr.hasClass('child')) tr = tr.prev();

    var row = table.row(tr);
    if (!row || !row.data()) return;

    var rowData = row.data();
    var hizmetDetaylari = [];

    try {
        if (rowData.hizmet_detaylari) {
            hizmetDetaylari = typeof rowData.hizmet_detaylari === 'string' ? JSON.parse(rowData.hizmet_detaylari) : rowData.hizmet_detaylari;
        }
    } catch(e) {
        hizmetDetaylari = [];
    }

    if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        button.html('<i class="fa fa-chevron-down"></i>');
    } else {
        if (hizmetDetaylari.length > 0) {
            var detayHtml = formatHizmetDetaylari(hizmetDetaylari);
            row.child(detayHtml).show();
            tr.addClass('shown');
            button.html('<i class="fa fa-chevron-up"></i>');
        } else {
            row.child('<div class="alert alert-info m-3">Bu pakette hizmet detayı bulunmamaktadır.</div>').show();
            tr.addClass('shown');
            button.html('<i class="fa fa-chevron-up"></i>');
        }
    }
});

// formatHizmetDetaylari fonksiyonu olduğu gibi korundu
function formatHizmetDetaylari(hizmetler) {
    var html = '<div class="child-row-wrapper p-3" style="background-color: #f8f9fa; border-radius: 8px; margin: 5px 0;">' +
               '<div class="container-fluid px-0">' +
               '<div class="row">';
    
    $.each(hizmetler, function(index, hizmet) {
        var toplam = parseInt(hizmet.toplam_seans) || 0;
        var seansDetaylari = [];

        try {
            if (hizmet.seans_detaylari) {
                seansDetaylari = JSON.parse(hizmet.seans_detaylari) || [];
            }
        } catch(e) {
            seansDetaylari = [];
        }

        // Lazer epilasyon mu? (backend isimden hesaplar) -> seans ikonuna tiklayinca cihaz formu
        var lazer = hizmet.lazer ? 1 : 0;

        var ikonlar = '';
        var gosterilecekIkon = seansDetaylari.length;
        var kalanSeans = Math.max(0, (parseInt(hizmet.toplam_seans) || 0) - gosterilecekIkon);

        for (var i = 0; i < gosterilecekIkon; i++) {
            var seans = seansDetaylari[i];
            var ikonClass = 'fa-circle-o';
            var ikonColor = '#adb5bd';
            var title = seans.seans_tarih + ' ' + (seans.seans_saat || '');

            if (seans.geldi === 1) { ikonClass = 'fa-check-circle'; ikonColor = '#28a745'; title += ' - Geldi'; }
            else if (seans.geldi === 0) { ikonClass = 'fa-times-circle'; ikonColor = '#dc3545'; title += ' - Gelmedi'; }
            else { title += ' - Beklemede'; }

            ikonlar += '<i data-index-number="'+hizmet.hizmetId+'" data-tarih="'+seans.seans_tarih+'" data-saat="'+seans.seans_saat+'" data-value="'+hizmet.id+'" data-lazer="'+lazer+'" name="seansDetay" class="fa ' + ikonClass + '" style="font-size:20px;color:' + ikonColor + ';margin:0 2px;cursor:pointer;" title="' + title + '" data-seans-id="' + seans.id + '"></i>';
        }

        for (var j = 0; j < kalanSeans; j++) {
            var paket = hizmet.seansTuru === "PAKET" ? 1 : 0;
            ikonlar += '<i name="yeniSeansEkle" data-paket="'+paket+'" data-index-number="'+hizmet.hizmetId+'" data-value="'+hizmet.id+'" class="fa fa-circle-o" style="font-size:20px;color:#adb5bd;margin:0 2px;cursor:pointer;"></i>';
        }

        var kullanilan = seansDetaylari.filter(function(s){ return s.geldi===1; }).length;
        var gelmedi = seansDetaylari.filter(function(s){ return s.geldi===0; }).length;
        var kalanS = hizmet.toplam_seans - (kullanilan+gelmedi);
        html += '<input type="hidden" name="paketMusteriAdi" data-value="'+hizmet.id+'" value="'+hizmet.musteriAdi+'">' +
                '<div class="col-sm-6 col-md-4 col-lg-3 mb-3">' +
                '<div class="card h-100 shadow-sm" style="border: 1px solid rgba(0,0,0,0.05); border-radius: 12px;">' +
                '<div class="card-header bg-white border-0 pt-3">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                '<h6 name="paketHizmetAdi" data-index-number="'+hizmet.hizmetId+'" data-value="'+hizmet.id+'" class="mb-0 font-weight-bold text-dark" style="font-size: 14px;">' + (hizmet.hizmet_adi || '-') + '</h6>' +
                '<span class="badge bg-success text-white px-2 py-1" style="border-radius: 20px; font-size: 11px;">' + toplam + ' Seans</span>' +
                '</div></div>' +
                '<div class="card-body pt-0">' +
                '<div class="mb-2 seans-ikonlari" style="min-height: 45px;">' + ikonlar + '</div>' +
                '<div class="row mt-2">' +
                '<div class="col-4 text-center"><small class="text-muted d-block">Kullanıldı</small><strong class="text-success">'+kullanilan+'</strong></div>' +
                '<div class="col-4 text-center"><small class="text-muted d-block">Kalan</small><strong class="text-warning">'+kalanS+'</strong></div>' +
                '<div class="col-4 text-center"><small class="text-muted d-block">Kullanılmadı</small><strong class="text-danger">'+gelmedi+'</strong></div>' +
                '</div>' +
                (lazer ? '<div class="mt-2 pt-2" style="border-top:1px dashed #e5e7eb;text-align:center;">' +
                         '<span style="display:block;font-size:10px;color:#7c3aed;font-weight:600;"><i class="fa fa-bolt"></i> Lazer cihaz takibi — seans ikonuna tıklayıp bilgileri girin</span>' +
                         '</div>' : '') +
                '</div></div></div>';
    });

    html += '</div></div></div>';

    if (!$('#child-row-styles').length) {
        $('head').append(`
            <style id="child-row-styles">
                .child-row-wrapper { background-color: #f8f9fa !important; border-radius: 12px !important; margin: 10px !important; width: calc(100% - 20px) !important; }
                .child-row-wrapper .card { transition: all 0.2s ease; border: 1px solid rgba(0,0,0,0.05) !important; }
                .child-row-wrapper .card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.05) !important; }
                table.dataTable tbody tr.child { background-color: #f8f9fa !important; }
            </style>
        `);
    }

    return html;
}
$(document).on('click','i[name="seansDetay"]',function(e)
{       
    e.preventDefault();
    var tarih = $(this).attr('data-tarih');
    var saat = $(this).attr('data-saat');
    var musteriAdi = $('input[name="paketMusteriAdi"][data-value="'+$(this).attr('data-value')+'"]').val();
    var hizmetAdi = $('h6[name="paketHizmetAdi"][data-index-number="'+$(this).attr('data-index-number')+'"][data-value="'+$(this).attr('data-value')+'"]').text();
    var paketId = $(this).attr('data-value');
    var seansId = $(this).attr('data-seans-id');
    var lazer = $(this).attr('data-lazer');

    swal({
        title: "Seans Düzenle",
        html: "<div style='padding:5px;'>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-user' style='color:#667eea; width:20px;'></i> " + musteriAdi + "</div>" +
              "</div>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-tag' style='color:#667eea; width:20px;'></i> " + hizmetAdi + "</div>" +
              "</div>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:20px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-calendar' style='color:#667eea; width:20px;'></i> " + tarih + " | " + (saat || '--:--') + "</div>" +
              "</div>" +
              "<div style='display:flex; gap:6px; justify-content:center;'>" +
              "<button type='button' class='btn btn-sm btn-success' id='seansKullanildi' data-value='"+paketId+"' data-seans-id='" + seansId + "' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-check'></i> Kullanıldı</button>" +

              "<button type='button' class='btn btn-sm btn-danger' id='seansKullanilmadi' data-value='"+paketId+"' data-seans-id='" + seansId + "' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-times'></i> Kullanılmadı</button>" +
                            "<button type='button' class='btn btn-sm btn-warning' id='seansBeklemede' data-value='"+paketId+"' data-seans-id='" + seansId + "' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-clock-o'></i> Beklemede</button>" +
              "</div>" +
              (lazer === '1'
                ? "<div style='margin-top:14px; padding-top:12px; border-top:1px dashed #e2e8f0;'>" +
                  "<button type='button' class='btn btn-sm' id='seansCihazBilgileri' data-seans-id='" + seansId + "' style='background:#4f46e5; color:#fff; border-radius:20px; padding:8px 18px; font-weight:600;'><i class='fa fa-bolt'></i> Cihaz Bilgileri (Lazer)</button>" +
                  "</div>"
                : "") +
              "</div>",
        showCancelButton: false,
        showConfirmButton: false
    });
});

// ================= CİHAZ BİLGİLERİ MODALI (lazer epilasyon seans parametreleri) =================
// Modal design: tam viewport ortasi, body'ye eklenir, soft slate/indigo palet.
var cihazPersoneller = [];
var cihazVarsayilanPersonel = null;

function cihazEsc(s){
    return String(s == null ? '' : s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

var cihazBolgeAdi = '';

function cihazModalOlustur(){
    if ($('#cihazModalOverlay').length) return;
    var inp = "width:100%; padding:9px 11px; border:1px solid #cbd5e1; border-radius:9px; font-size:14px; background:#fff; box-sizing:border-box;";
    var lbl = "display:block; font-size:10.5px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.03em; margin-bottom:5px;";
    var html =
      "<div id='cihazModalOverlay' style='display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,.55); overflow:auto; padding:24px;'>" +
        "<div id='cihazModalBox' style='max-width:620px; margin:24px auto; background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(15,23,42,.35); overflow:hidden;'>" +
          "<div style='display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; background:#4f46e5; color:#fff;'>" +
            "<div style='font-size:16px; font-weight:700;'><i class='fa fa-bolt'></i> Cihaz / Seans Bilgileri</div>" +
            "<span id='cihazModalKapat' style='cursor:pointer; font-size:22px; line-height:1; opacity:.9;'>&times;</span>" +
          "</div>" +
          "<div style='padding:14px 20px; background:#f8fafc; border-bottom:1px solid #eef2f7;'>" +
            "<div id='cihazSeansBilgi' style='font-size:13px; color:#334155;'></div>" +
          "</div>" +
          "<div style='padding:18px 20px; max-height:60vh; overflow:auto;'>" +
            "<div style='background:#eef2ff; border:1px solid #c7d2fe; border-radius:10px; padding:10px 14px; margin-bottom:16px;'>" +
              "<span style='font-size:10.5px; font-weight:700; color:#4f46e5; text-transform:uppercase; letter-spacing:.03em;'>Uygulama Bölgesi</span>" +
              "<div id='cihazBolgeAdi' style='font-size:15px; font-weight:700; color:#312e81; margin-top:2px;'></div>" +
            "</div>" +
            "<div style='display:flex; flex-wrap:wrap; gap:12px;'>" +
              "<div style='flex:1 1 22%; min-width:110px;'><label style='"+lbl+"'>Enerji (Jül)</label><input type='text' id='cf-enerji' style='"+inp+"'></div>" +
              "<div style='flex:1 1 22%; min-width:100px;'><label style='"+lbl+"'>Hız</label><input type='text' id='cf-hiz' style='"+inp+"'></div>" +
              "<div style='flex:1 1 22%; min-width:100px;'><label style='"+lbl+"'>MS</label><input type='text' id='cf-ms' style='"+inp+"'></div>" +
              "<div style='flex:1 1 22%; min-width:110px;'><label style='"+lbl+"'>Atış Sayısı</label><input type='text' id='cf-atis' style='"+inp+"'></div>" +
              "<div style='flex:1 1 48%; min-width:180px;'><label style='"+lbl+"'>Uygulamayı Yapan</label><select id='cf-personel' style='"+inp+"'></select></div>" +
              "<div style='flex:1 1 48%; min-width:180px;'><label style='"+lbl+"'>Not (opsiyonel)</label><input type='text' id='cf-not' style='"+inp+"'></div>" +
            "</div>" +
          "</div>" +
          "<div style='display:flex; justify-content:flex-end; gap:10px; padding:14px 20px; background:#f8fafc; border-top:1px solid #eef2f7;'>" +
            "<button type='button' id='cihazModalIptal' style='background:#e2e8f0; color:#334155; border:none; border-radius:10px; padding:10px 18px; font-weight:600; cursor:pointer;'>İptal</button>" +
            "<button type='button' id='cihazModalKaydet' style='background:#4f46e5; color:#fff; border:none; border-radius:10px; padding:10px 22px; font-weight:700; cursor:pointer;'><i class='fa fa-check'></i> Kaydet</button>" +
          "</div>" +
        "</div>" +
      "</div>";
    $('body').append(html);
}

function cihazPersonelSelect(seciliId){
    var opt = "<option value=''>— Seçiniz —</option>";
    var sec = (seciliId != null && seciliId !== '') ? String(seciliId) : String(cihazVarsayilanPersonel || '');
    for (var i=0; i<cihazPersoneller.length; i++){
        var p = cihazPersoneller[i];
        opt += "<option value='"+p.id+"'"+(String(p.id)===sec?" selected":"")+">"+cihazEsc(p.personel_adi)+"</option>";
    }
    return opt;
}

function cihazModalAc(seansId){
    cihazModalOlustur();
    $('#cihazSeansBilgi').html('Yükleniyor...');
    $('#cihazBolgeAdi').text('');
    $('#cf-enerji, #cf-hiz, #cf-ms, #cf-atis, #cf-not').val('');
    $('#cf-personel').html('');
    $('#cihazModalKaydet').data('seans-id', seansId);
    $('#cihazModalOverlay').show();

    $.ajax({
        url: '/isletmeyonetim/seansCihazVeriGetir',
        data: { seansId: seansId },
        dataType: 'json',
        success: function(res){
            if (!res || res.hatali == 1){
                $('#cihazSeansBilgi').html("<span style='color:#dc2626;'>"+cihazEsc(res && res.mesaj ? res.mesaj : 'Kayıt bulunamadı')+"</span>");
                return;
            }
            cihazPersoneller = res.personeller || [];
            cihazVarsayilanPersonel = res.seans ? res.seans.varsayilan_personel_id : null;
            var s = res.seans || {};
            var tarih = s.seans_tarih ? s.seans_tarih : '';
            cihazBolgeAdi = s.hizmet_adi || '';

            $('#cihazSeansBilgi').html(
                "<b style='color:#0f172a;'>"+cihazEsc(s.musteri_adi||'')+"</b>" +
                (s.seans_no ? " &middot; "+cihazEsc(s.seans_no)+". Seans" : "") +
                (tarih ? " &middot; "+cihazEsc(tarih) : "")
            );
            $('#cihazBolgeAdi').text(cihazBolgeAdi || '-');

            // Tek bolge: varsa mevcut kaydi doldur (ilk satir), yoksa bos
            var d = (res.bolgeler && res.bolgeler.length) ? res.bolgeler[0] : {};
            $('#cf-enerji').val(d.enerji || '');
            $('#cf-hiz').val(d.hiz || '');
            $('#cf-ms').val(d.ms || '');
            $('#cf-atis').val(d.atis_sayisi || '');
            $('#cf-not').val(d.notlar || '');
            $('#cf-personel').html(cihazPersonelSelect(d.personel_id));
        },
        error: function(){
            $('#cihazSeansBilgi').html("<span style='color:#dc2626;'>Veri alınamadı.</span>");
        }
    });
}

function cihazModalKapat(){ $('#cihazModalOverlay').hide(); }

$(document).on('click', '#seansCihazBilgileri', function(e){
    e.preventDefault();
    var seansId = $(this).attr('data-seans-id');
    try { if (typeof swal !== 'undefined' && swal.close) swal.close(); } catch(_){}
    cihazModalAc(seansId);
});
$(document).on('click', '#cihazModalKapat, #cihazModalIptal', cihazModalKapat);
$(document).on('click', '#cihazModalOverlay', function(e){ if (e.target && e.target.id === 'cihazModalOverlay') cihazModalKapat(); });

$(document).on('click', '#cihazModalKaydet', function(e){
    e.preventDefault();
    var seansId = $(this).data('seans-id');
    var bolgeler = [{
        uygulama_bolgesi: cihazBolgeAdi,          // otomatik: kartin (bolge) adi
        enerji:  $('#cf-enerji').val(),
        hiz:     $('#cf-hiz').val(),
        ms:      $('#cf-ms').val(),
        atis_sayisi: $('#cf-atis').val(),
        personel_id: $('#cf-personel').val(),
        notlar:  $('#cf-not').val()
    }];
    $.ajax({
        type: 'POST',
        url: '/isletmeyonetim/seansCihazVeriKaydet',
        data: {
            seansId: seansId,
            bolgeler: JSON.stringify(bolgeler),
            sube: $('input[name="sube"]').val(),
            _token: $('input[name="_token"]').val()
        },
        dataType: 'json',
        beforeSend: function(){ $('#preloader').show(); },
        success: function(res){
            $('#preloader').hide();
            cihazModalKapat();
            swal({
                type: (res && res.hatali == 0) ? "success" : "error",
                title: (res && res.hatali == 0) ? "Kaydedildi" : "Hata",
                text: res && res.mesaj ? res.mesaj : "",
                showConfirmButton: false, timer: 2500
            });
        },
        error: function(){
            $('#preloader').hide();
            swal({ type:"error", title:"Hata", text:"Cihaz bilgileri kaydedilemedi.", showConfirmButton:false, timer:2500 });
        }
    });
});

$(document).on('click','i[name="yeniSeansEkle"]',function(e)
{       
    e.preventDefault();
    
    var musteriAdi = $('input[name="paketMusteriAdi"][data-value="'+$(this).attr('data-value')+'"]').val();
    var hizmetAdi = $('h6[name="paketHizmetAdi"][data-index-number="'+$(this).attr('data-index-number')+'"][data-value="'+$(this).attr('data-value')+'"]').text();

    var paketId = $(this).attr('data-value');
    var hizmetId = $(this).attr('data-index-number');
    var paket = $(this).attr('data-paket');

    // Varsayilan seans tarihi = bugun (yerel saat, YYYY-MM-DD)
    var bugun = new Date();
    var bugunStr = bugun.getFullYear() + '-' +
                   ('0' + (bugun.getMonth() + 1)).slice(-2) + '-' +
                   ('0' + bugun.getDate()).slice(-2);

    swal({
        title: "Yeni Seans Kullanımı",
        html: "<div style='padding:5px;'>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-user' style='color:#667eea; width:20px;'></i> " + musteriAdi + "</div>" +
              "</div>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-tag' style='color:#667eea; width:20px;'></i> " + hizmetAdi + "</div>" +
              "</div>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px; text-align:left;'>" +
              "<label for='seansTarihiYeni' style='color:#718096; font-size:12px; display:block; margin-bottom:6px; font-weight:600;'><i class='fa fa-calendar' style='color:#667eea; width:20px;'></i> Seans Tarihi</label>" +
              "<input type='text' id='seansTarihiYeni' class='form-control date-picker' value='" + bugunStr + "' autocomplete='off' placeholder='YYYY-AA-GG' style='background:#fff; cursor:pointer;'>" +
              "</div>" +

              "<div style='display:flex; gap:6px; justify-content:center;'>" +
              "<button type='button' class='btn btn-sm btn-success' id='seansKullanildiYeni' data-index-number='"+hizmetId+"' data-paket='"+paket+"' data-value='"+paketId+"' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-check'></i> Kullanıldı</button>" +

              "<button type='button' class='btn btn-sm btn-danger' id='seansKullanilmadiYeni'  data-index-number='"+hizmetId+"' data-paket='"+paket+"' data-value='"+paketId+"' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-times'></i> Kullanılmadı</button>" +

              "</div>" +
              "</div>",
        showCancelButton: false,
        showConfirmButton: false,
        // Popup sonradan olustugu icin sayfa yuklenirken calisan global .date-picker
        // init'i bu input'u yakalamaz; burada elle baslatiyoruz.
        // Kutuphane air-datepicker (core.js icinde): varsayilani ru + dd.mm.yyyy,
        // 'tr' dil dosyasi yok. Dil objesini inline veriyoruz ki backend'in bekledigi
        // yyyy-mm-dd bicimi ve Turkce takvim garanti olsun.
        onOpen: function(){
            $('#seansTarihiYeni').datepicker({
                language: seansTarihLocale,
                dateFormat: 'yyyy-mm-dd',
                autoClose: true,
                todayButton: new Date(),
                // Varsayilan showEvent 'focus'. Tarih secildikten sonra kutuphane
                // odagi input'ta birakiyor; input zaten odakliyken tekrar tiklayinca
                // tarayici yeni focus olayi uretmedigi icin takvim bir daha acilmiyordu.
                // 'click' ile her tiklamada guvenilir acilir.
                showEvent: 'click',
                onSelect: function(){ seansTarihUyariTemizle(); }
            });
            $('#seansTarihiYeni').on('input', seansTarihUyariTemizle);
        },
        // Popup her acilista yeni input uretiyor; kapanista instance'i yok et ki
        // body'deki takvim div'leri birikmesin.
        onClose: function(){
            var dp = $('#seansTarihiYeni').data('datepicker');
            if (dp) dp.destroy();
        }
    });
});


    
 


$(document).on('click','#seansKullanildiYeni',function(e){
   
    var paket  = $(this).attr('data-paket');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
    var paketId = $(this).attr('data-value');
    var hizmetId = $(this).attr('data-index-number');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
    var seansTarihi = seansTarihiOku();
    e.preventDefault();
    if (seansTarihi === null) {
        seansTarihUyar();
        return;
    }
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansEkle',
                data:  {hizmetId:hizmetId,paketId:paketId,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),musteriId:musteriId,geldi:1,paket:paket,seansTarihi:seansTarihi} ,
                dataType: "text",
                
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                     
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Seans kullanımı geldi olarak başarıyla güncellendi.",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                    seanslariGetir(paketId);
                    
                     
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });

});
$(document).on('click','#seansKullanilmadiYeni',function(e){
   
    var paket  = $(this).attr('data-paket');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
    var paketId = $(this).attr('data-value');
    var hizmetId = $(this).attr('data-index-number');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
    var seansTarihi = seansTarihiOku();
    e.preventDefault();
    if (seansTarihi === null) {
        seansTarihUyar();
        return;
    }
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansEkle',
                data:  {hizmetId:hizmetId,paketId:paketId,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),musteriId:musteriId,geldi:0,paket:paket,seansTarihi:seansTarihi} ,
                dataType: "text",
                
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                     
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Seans kullanımı geldi olarak başarıyla güncellendi.",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                    seanslariGetir(paketId);
                    
                     
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });

});




$(document).on('click','#seansKullanildi',function(e){
    var seansId = $(this).attr('data-seans-id');
     var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
     var paketId = $(this).attr('data-value');
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansGuncelle',
                data: {seansId:seansId,geldi:1,_token:$('input[name="_token"]').val(),musteriId:musteriId} ,
                dataType: "text",
                
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                     
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Seans kullanıldı olarak başarıyla güncellendi.",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                    seanslariGetir(paketId);
                   
                     
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });

});
$(document).on('click','#seansKullanilmadi',function(e){
    var seansId = $(this).attr('data-seans-id');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
      var paketId = $(this).attr('data-value');
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansGuncelle',
                data: {seansId:seansId,geldi:0,_token:$('input[name="_token"]').val(),musteriId:musteriId} ,
                dataType: "text",
                
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                     
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Seans kullanılmadı olarak başarıyla güncellendi.",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                    seanslariGetir(paketId);
                    
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });

});
$(document).on('click','#seansBeklemede',function(e){
    var seansId = $(this).attr('data-seans-id');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
      var paketId = $(this).attr('data-value');
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansGuncelle',
                data: {seansId:seansId,geldi:'',_token:$('input[name="_token"]').val(),musteriId:musteriId} ,
                dataType: "text",
                
                beforeSend: function(){
                    $('#preloader').show();
                },
               success: function(result)  {
                    $('#preloader').hide();
                     
                    swal({
                        type: "success",
                        title: "Başarılı",
                        text:  "Seans kullanımı beklemede olarak başarıyla güncellendi.",
                        showCloseButton: false,
                        showCancelButton: false,
                        showConfirmButton:false,
                        timer: 3000,
                    });
                    seanslariGetir(paketId);
                   
                     
                },
                error: function (request, status, error) {
                     document.getElementById('hata').innerHTML =request.responseText;
                     $('#preloader').hide();
                }
            });

});
