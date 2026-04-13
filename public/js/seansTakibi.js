// global değişken
// global değişken
var seansTablo;

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

        var ikonlar = '';
        var gosterilecekIkon = Math.min(seansDetaylari.length, 10);
        var kalanSeans = hizmet.toplam_seans - gosterilecekIkon;

        for (var i = 0; i < gosterilecekIkon; i++) {
            var seans = seansDetaylari[i];
            var ikonClass = 'fa-circle-o';
            var ikonColor = '#adb5bd';
            var title = seans.seans_tarih + ' ' + (seans.seans_saat || '');
            
            if (seans.geldi === 1) { ikonClass = 'fa-check-circle'; ikonColor = '#28a745'; title += ' - Geldi'; }
            else if (seans.geldi === 0) { ikonClass = 'fa-times-circle'; ikonColor = '#dc3545'; title += ' - Gelmedi'; }
            else { title += ' - Beklemede'; }

            ikonlar += '<i data-index-number="'+hizmet.hizmetId+'" data-tarih="'+seans.seans_tarih+'" data-saat="'+seans.seans_saat+'" data-value="'+hizmet.id+'" name="seansDetay" class="fa ' + ikonClass + '" style="font-size:20px;color:' + ikonColor + ';margin:0 2px;cursor:pointer;" title="' + title + '" data-seans-id="' + seans.id + '"></i>';
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
                '</div></div></div></div>';
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
              "</div>",
        showCancelButton: false,
        showConfirmButton: false
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
    
    swal({
        title: "Yeni Seans Kullanımı",
        html: "<div style='padding:5px;'>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-user' style='color:#667eea; width:20px;'></i> " + musteriAdi + "</div>" +
              "</div>" +
              "<div style='background:#f5f7fa; padding:12px; border-radius:10px; margin-bottom:12px;'>" +
              "<div style='color:#2d3748; font-size:15px;'><i class='fa fa-tag' style='color:#667eea; width:20px;'></i> " + hizmetAdi + "</div>" +
              "</div>" +
               
              "<div style='display:flex; gap:6px; justify-content:center;'>" +
              "<button type='button' class='btn btn-sm btn-success' id='seansKullanildiYeni' data-index-number='"+hizmetId+"' data-paket='"+paket+"' data-value='"+paketId+"' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-check'></i> Kullanıldı</button>" +

              "<button type='button' class='btn btn-sm btn-danger' id='seansKullanilmadiYeni'  data-index-number='"+hizmetId+"' data-paket='"+paket+"' data-value='"+paketId+"' style='border-radius:20px; padding:6px 12px;'><i class='fa fa-times'></i> Kullanılmadı</button>" +
            
              "</div>" +
              "</div>",
        showCancelButton: false,
        showConfirmButton: false
    });
});


    
 


$(document).on('click','#seansKullanildiYeni',function(e){
   
    var paket  = $(this).attr('data-paket');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
    var paketId = $(this).attr('data-value');
    var hizmetId = $(this).attr('data-index-number');
    var musteriId = $('#musteriKarti').length ? $('#musteriKarti').val() : '';
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansEkle',
                data:  {hizmetId:hizmetId,paketId:paketId,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),musteriId:musteriId,geldi:1,paket:paket} ,
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
    e.preventDefault();
     $.ajax({
                type: "POST",
                url: '/isletmeyonetim/seansEkle',
                data:  {hizmetId:hizmetId,paketId:paketId,_token:$('input[name="_token"]').val(),sube:$('input[name="sube"]').val(),musteriId:musteriId,geldi:0,paket:paket} ,
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
