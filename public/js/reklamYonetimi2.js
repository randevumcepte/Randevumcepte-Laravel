let aramaDetayId2 = null;
let currentPages2 = { 1: 1, 2: 1, 3: 1, 4: 1 };
let totalKayit2 = 0;
let perPage3 = 50;
let loading2 = false;
let kampanyaId = null;

$(document).on('click', 'a[name="kampanya_detay"]', function (e) {
    e.preventDefault();
    aramaDetayId2 = $(this).attr('data-value');
    kampanyaId = aramaDetayId2;
    
    // Tüm tabloların page'lerini sıfırla
    currentPages2 = { 1: 1, 2: 1, 3: 1, 4: 1 };
    loadKampanyaDetaylari(1, 1, '');
    
    // Müşteri listesini yükle
    loadMusteriListesiForSelect();
    
    $('#kampanya_detay_modal').modal('show');
});

function loadKampanyaDetaylari(page = 1, tur, hasta) {
    if (loading2) return;
    loading2 = true;
    
    kampanyaId = aramaDetayId2;
    $.ajax({
        url: '/isletmeyonetim/kampanyadetay',
        method: 'POST',
        data: {
            kampanyaid: kampanyaId,
            sube: $('input[name="sube"]').val(),
            page: page,
            search: hasta,
            perPage: perPage3,
            katilimDurumu: tur,
            _token: $('input[name="_token"]').val()
        },
        success: function (response) {
            console.log('AJAX başarılı. Gelen veri sayısı:', response.data.length);

            $('#paket_adi').empty();
            $('#paket_adi').append(response.kampanya.gorev_turu);

            $('#kampanya_seans').empty();
            $('#kampanya_seans').append(response.kampanya.paket_isim);
            $('#kampanya_katilimci').empty();
            $('#kampanya_katilimci').append(response.kampanya.katilimci_sayisi);
            $('#kampanya_hizmeti').empty();
            $('#kampanya_hizmeti').append(response.kampanya.hizmet_adi);
            $('#mesajIcerigiContent').empty();
            $('#mesajIcerigiContent').append(response.kampanya.mesaj);
            console.log(response.kampanya.mesaj);
            // Mesaj içeriğini modal için sakla
            if (response.kampanya.mesaj!= null) {
                $('#mesajIcerigiContent').text(response.kampanya.mesaj);
            } else {
                $('#mesajIcerigiContent').text('Bu kampanya için mesaj içeriği bulunmamaktadır.');
            }

            // Tabloyu belirle
            let tableId = '';
            let emptyId = '';
            let containerId = '';
            let countId = '';

            if (tur == 1) {
                tableId = '#kampanya_tablo_tum_katilimci_arama';
                emptyId = 'tum_arama_empty';
                containerId = '#aranacak_musteriler1';
                countId = '#tum_arama_count';
            }
            if (tur == 2) {
                tableId = '#kampanya_tablo_katilanlar_katilimci_arama';
                emptyId = 'katilan_arama_empty';
                containerId = '#aranacak_musteriler2';
                countId = '#katilan_arama_count';
            }
            if (tur == 3) {
                tableId = '#kampanya_tablo_katilmayanlar_katilimci_arama';
                emptyId = 'katilmayan_arama_empty';
                containerId = '#aranacak_musteriler3';
                countId = '#katilmayan_arama_count';
            }
            if (tur == 4) {
                tableId = '#kampanya_tablo_beklenen_katilimci_arama';
                emptyId = 'beklenen_arama_empty';
                containerId = '#aranacak_musteriler4';
                countId = '#beklenen_arama_count';
            }

            // Count badge'leri güncelle
            if (countId) {
                $(countId).text(response.total || '0');
            }

            totalKayit2 = response.total;
            const tbody = $(tableId + ' tbody');

            if (page === 1) {
                tbody.empty();
                // İlk sayfa yüklendiğinde scroll'u en üste al
                $(containerId).scrollTop(0);
            }

            let htmlRows = [];
            if (response.data.length === 0 && page === 1) {
                $(`#${emptyId}`).show();
            } else {
                $(`#${emptyId}`).hide();
            }

            response.data.forEach(function (item, index) {
                let statusClass = '';
                let statusText = item.durum || '';

                if (statusText.includes('Katıldı') || statusText.includes('Katılan')) {
                    statusClass = 'status-aktif';
                } else if (statusText.includes('Katılmadı') || statusText.includes('Katılmayan')) {
                    statusClass = 'status-pasif';
                } else if (statusText.includes('Bekleniyor') || statusText.includes('Beklenen')) {
                    statusClass = 'status-beklemede';
                }
                // Silme butonu için HTML
                let deleteButton = `
                    <button class="btn btn-sm btn-danger delete-katilimci" 
                            data-value="${item.id}"
                            data-adsoyad="${item.ad_soyad || ''}"
                            data-tablo="${tableId}"
                            data-tur="${tur}">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
                // "Tümü" tabloları için durum sütunu da var
                if (tur == 1) {
                    htmlRows.push(`
                        <tr data-index="${index}">
                            <td>${item.ad_soyad || ''}</td>
                            <td>${formatPhoneNumber(item.telefon || '')}</td>
                            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                            <td>${deleteButton}</td>
                        </tr>
                    `);
                } else {
                    // Diğer tablolar için
                    htmlRows.push(`
                        <tr data-index="${index}">
                            <td>${item.ad_soyad || ''}</td>
                            <td>${formatPhoneNumber(item.telefon || '')}</td>
                            <td>${deleteButton}</td>
                        </tr>
                    `);
                }
            });

            tbody.append(htmlRows.join(''));
            
            // Sadece başarılı yüklemede page'i artır
            currentPages2[tur] = page + 1;
            loading2 = false;
            
            console.log(`Tur ${tur} için yeni page: ${currentPages2[tur]}, Toplam kayıt: ${totalKayit2}, Yüklenen: ${page * perPage3}`);
        },
        error: function (xhr) {
            console.error("AJAX Hatası:", xhr);
            loading2 = false;
        }
    });
}

// Scroll event'lerini birleştirilmiş fonksiyon ile yönet
function setupScrollEvent(containerId, tur) {
    $(containerId).off('scroll').on('scroll', function () {
        const container = $(this);
        const scrollBottom = container[0].scrollHeight - container.scrollTop() - container.innerHeight();
        
        // Eğer konteyner yüksekliği scrollHeight'dan küçükse (yani scroll bar yoksa) infinite scroll yapma
        if (container[0].scrollHeight <= container.innerHeight()) {
            return;
        }
        
        console.log(`Scroll event - Tur ${tur}:`, {
            scrollBottom: scrollBottom,
            loading: loading2,
            currentPage: currentPages2[tur],
            perPage: perPage3,
            total: totalKayit2,
            yuklenen: (currentPages2[tur] - 1) * perPage3
        });
        
        if (scrollBottom < 100 && !loading2 && ((currentPages2[tur] - 1) * perPage3) < totalKayit2) {
            console.log(`Tur ${tur} için daha fazla veri yüklenecek... Page: ${currentPages2[tur]}`);
            loadKampanyaDetaylari(currentPages2[tur], tur, getSearchInputForTur(tur).val());
        }
    });
}

// Sayfa yüklendiğinde scroll event'lerini kur
$(document).ready(function() {
    setupScrollEvent('#aranacak_musteriler1', 1);
    setupScrollEvent('#aranacak_musteriler2', 2);
    setupScrollEvent('#aranacak_musteriler3', 3);
    setupScrollEvent('#aranacak_musteriler4', 4);
    
    // Select2 başlatma
    initSelect2();
    
    // Mesaj içeriği modalı için kopyalama butonu işlevi
    $('#mesajIcerigiKopyala').on('click', function() {
        const mesajIcerigi = $('#mesajIcerigiContent').text();
        if (mesajIcerigi && mesajIcerigi !== 'Bu kampanya için mesaj içeriği bulunmamaktadır.') {
            navigator.clipboard.writeText(mesajIcerigi).then(function() {
                // Buton metnini geçici olarak değiştir
                const originalText = $(this).html();
                $(this).html('<i class="fa fa-check mr-1"></i>Kopyalandı!');
                $(this).addClass('btn-success').removeClass('btn-primary');
                
                setTimeout(() => {
                    $(this).html(originalText);
                    $(this).removeClass('btn-success').addClass('btn-primary');
                }, 2000);
            }.bind(this)).catch(function(err) {
                console.error('Kopyalama hatası:', err);
                swal({
                    type: "error",
                    title: "Hata",
                    html: 'Mesaj kopyalanamadı. Lütfen manuel olarak kopyalayın.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
            });
        }
    });
});

// Modal açıldığında da scroll event'lerini yeniden kur
$('#kampanya_detay_modal').on('shown.bs.modal', function () {
    setupScrollEvent('#aranacak_musteriler1', 1);
    setupScrollEvent('#aranacak_musteriler2', 2);
    setupScrollEvent('#aranacak_musteriler3', 3);
    setupScrollEvent('#aranacak_musteriler4', 4);
});

// Select2 başlatma fonksiyonu
function initSelect2() {
    $('#katilimciSecimSelect').select2({
        placeholder: "Müşteri seçin...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#kampanya_detay_modal'),
        language: {
            noResults: function() {
                return "Sonuç bulunamadı";
            },
            searching: function() {
                return "Aranıyor...";
            },
            inputTooShort: function(args) {
                var remainingChars = args.minimum - args.input.length;
                return "En az " + remainingChars + " karakter daha girin";
            }
        },
        ajax: {
            url: '/isletmeyonetim/musteriarama',
            method: 'GET',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    query: params.term,
                    sube: $('input[name="sube"]').val(),
                    _token: $('input[name="_token"]').val()
                };
            },
            processResults: function(response) {
                // PHP'den gelen veriyi Select2 formatına dönüştür
                return {
                    results: response.map(function(item) {
                        return {
                            id: item.id,
                            text: item.ad_soyad,
                            telefon: item.cep_telefon,
                            detay_url: item.detayli_bilgi
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: formatMusteri,
        templateSelection: formatMusteriSelection
    });
}

// Müşteri formatlama fonksiyonu (dropdown'da görünecek)
function formatMusteri(musteri) {
    if (musteri.loading) {
        return 'Aranıyor...';
    }
    
    if (!musteri.id) {
        return musteri.text;
    }
    
    var telefon = musteri.telefon ? formatPhoneNumber(musteri.telefon) : 'Telefon yok';
    var $result = $(
        '<div class="musteri-option">' +
            '<div class="musteri-ad">' + musteri.text + '</div>' +
            '<div class="musteri-telefon text-muted small">' + telefon + '</div>' +
        '</div>'
    );
    
    return $result;
}

// Seçili müşteriyi formatlama fonksiyonu
function formatMusteriSelection(musteri) {
    if (!musteri.id) {
        return musteri.text || 'Müşteri seçin...';
    }
    
    const text = musteri.text || '';
    const telefonRegex = /\(([^)]+)\)/;
    const telefonMatch = text.match(telefonRegex);
    
    if (telefonMatch) {
        const adSoyad = text.replace(telefonRegex, '').trim();
        return adSoyad;
    }
    
    return text;
}

// Telefon numarasını formatlama fonksiyonu
function formatPhoneNumber(phone) {
    if (!phone) return '';
    
    const cleaned = phone.toString().replace(/\D/g, '');
    
    if (cleaned.length === 11) {
        return cleaned.replace(/(\d{4})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
    } else if (cleaned.length === 10) {
        return cleaned.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
    }
    
    return phone;
}

// Silme işlemi için event handler
$(document).on('click', '.delete-katilimci', function (e) {
    e.preventDefault();
    
    const katilimciId = $(this).attr('data-value');
    const adSoyad = $(this).data('adsoyad');
    const tabloId = $(this).data('tablo');
    const tur = $(this).data('tur');
    
    // Modal mesajını güncelle
    $('#silmeOnayMesaji').html(`
        <strong>${adSoyad}</strong> isimli katılımcıyı silmek istediğinizden emin misiniz?<br>
        <small class="text-muted">Bu işlem geri alınamaz.</small>
    `);
    
    // Gerekli verileri sakla
    $('#silinecekKatilimciId').val(katilimciId);
    $('#silinecekTabloId').val(tabloId);
    $('#silinecekTabloTuru').val(tur);
    
    // Modalı göster
    $('#silmeOnayModal').modal('show');
});

// Silme onayı butonu
$(document).on('click', '#silmeOnayBtn', function () {
    const katilimciId = $('#silinecekKatilimciId').val();
    const tur = $('#silinecekTabloTuru').val();
    const searchInput = getSearchInputForTur(tur);
    const searchValue = searchInput ? searchInput.val() : '';
    
    // AJAX ile silme işlemi
    $.ajax({
        url: '/isletmeyonetim/kampanyakatilimcisil',
        method: 'POST',
        data: {
            id: katilimciId,
            kampanyaid: kampanyaId,
            _token: $('input[name="_token"]').val()
        },
        success: function (response) {
            if (response.success) {
                // Modalı kapat
                $('#silmeOnayModal').modal('hide');
                
                // Tüm tabloların page'lerini sıfırla
                currentPages2 = { 1: 1, 2: 1, 3: 1, 4: 1 };
                
                // Tüm tabloları yeniden yükle
                [1, 2, 3, 4].forEach(turValue => {
                    const input = getSearchInputForTur(turValue);
                    const searchTerm = input ? input.val() : '';
                    loadKampanyaDetaylari(1, turValue, searchTerm);
                });
                
                // Başarılı Swal bildirimi
                swal({
                    type: "success",
                    title: "Başarılı",
                    html: 'Katılımcı başarıyla silindi.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
                
            } else {
                $('#silmeOnayModal').modal('hide');
                swal({
                    type: "error",
                    title: "Hata",
                    html: response.message || 'Silme işlemi başarısız.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
            }
        },
        error: function (xhr) {
            console.error('Silme hatası:', xhr);
            $('#silmeOnayModal').modal('hide');
            swal({
                type: "error",
                title: "Hata",
                html: 'Bir hata oluştu. Lütfen tekrar deneyin.',
                showCloseButton: false,
                showCancelButton: false,
                showConfirmButton: false,
                timer: 3000,
            });
        }
    });
});

// Tür değerine göre ilgili arama inputunu döndüren yardımcı fonksiyon
function getSearchInputForTur(tur) {
    switch(parseInt(tur)) {
        case 1: return $('#katilimciArama1');
        case 2: return $('#katilimciArama2');
        case 3: return $('#katilimciArama3');
        case 4: return $('#katilimciArama4');
        default: return null;
    }
}

// Arama input event'leri
$(document).on('input', '#katilimciArama1', function(e){
    console.log('Arama1 değişti:', $(this).val());
    currentPages2[1] = 1;
    loadKampanyaDetaylari(1, 1, $(this).val());
});

$(document).on('input', '#katilimciArama2', function(e){
    console.log('Arama2 değişti:', $(this).val());
    currentPages2[2] = 1;
    loadKampanyaDetaylari(1, 2, $(this).val());
});

$(document).on('input', '#katilimciArama3', function(e){
    console.log('Arama3 değişti:', $(this).val());
    currentPages2[3] = 1;
    loadKampanyaDetaylari(1, 3, $(this).val());
});

$(document).on('input', '#katilimciArama4', function(e){
    console.log('Arama4 değişti:', $(this).val());
    currentPages2[4] = 1;
    loadKampanyaDetaylari(1, 4, $(this).val());
});

// Müşteri listesini yükleme fonksiyonu
function loadMusteriListesiForSelect() {
    console.log('Select2 aktif - müşteriler otomatik yüklenecek');
}

// Katılımcı ekleme butonu
$(document).on('click', '#katilimciEkleBtn', function() {
    const musteriId = $('#katilimciSecimSelect').val();
    const musteriData = $('#katilimciSecimSelect').select2('data')[0];
    
    // Validasyon
    if (!musteriId) {
        swal({
            type: "warning",
            title: "Uyarı",
            html: 'Lütfen bir müşteri seçin.',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            timer: 3000,
        });
        return;
    }
    
    if (!kampanyaId) {
        swal({
            type: "error",
            title: "Hata",
            html: 'Kampanya ID bulunamadı.',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            timer: 3000,
        });
        return;
    }
    
    // Butonu disable et
    const btn = $(this);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Ekleniyor...');
    
    // AJAX ile katılımcı ekleme
    $.ajax({
        url: '/isletmeyonetim/kampanyakatilimciekle',
        method: 'POST',
        data: {
            kampanyaid: kampanyaId,
            musteriid: musteriId,
            durum: 1,
            sube: $('input[name="sube"]').val(),
            _token: $('input[name="_token"]').val()
        },
        success: function(response) {
            btn.prop('disabled', false).html(originalHtml);
            
            if (response.success) {
                // Select'i temizle
                $('#katilimciSecimSelect').val('').trigger('change');
                
                // Swal bildirimi göster
                swal({
                    type: response.type || 'success',
                    title: response.type === 'warning' ? 'Uyarı' : 'Başarılı',
                    html: response.message,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
                
                if (response.type === 'success') {
                    // Tüm tabloların page'lerini sıfırla ve yeniden yükle
                    currentPages2 = { 1: 1, 2: 1, 3: 1, 4: 1 };
                    
                    [1, 2, 3, 4].forEach(turValue => {
                        const input = getSearchInputForTur(turValue);
                        const searchTerm = input ? input.val() : '';
                        loadKampanyaDetaylari(1, turValue, searchTerm);
                    });
                }
                
            } else {
                swal({
                    type: "error",
                    title: "Hata",
                    html: response.message || 'Katılımcı eklenemedi.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html(originalHtml);
            
            let errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            swal({
                type: "error",
                title: "Hata",
                html: errorMessage,
                showCloseButton: false,
                showCancelButton: false,
                showConfirmButton: false,
                timer: 3000,
            });
            console.error('Katılımcı ekleme hatası:', xhr);
        }
    });
});

// Tab butonları için katılımcı ekleme
$(document).on('click', '.katilimciEkleTabBtn', function(e) {
    e.preventDefault();
    const tur = $(this).data('tur');
    const musteriId = $('#katilimciSecimSelect').val();
    
    // Validasyon
    if (!musteriId) {
        swal({
            type: "warning",
            title: "Uyarı",
            html: 'Lütfen önce bir müşteri seçin.',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            timer: 3000,
        });
        return;
    }
    
    if (!kampanyaId) {
        swal({
            type: "error",
            title: "Hata",
            html: 'Kampanya ID bulunamadı.',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            timer: 3000,
        });
        return;
    }
    
    // Butonu disable et
    const btn = $(this);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    
    // AJAX ile katılımcı ekleme
    $.ajax({
        url: '/isletmeyonetim/kampanyakatilimciekle',
        method: 'POST',
        data: {
            kampanyaid: kampanyaId,
            musteriid: musteriId,
            durum: tur,
            sube: $('input[name="sube"]').val(),
            _token: $('input[name="_token"]').val()
        },
        success: function(response) {
            btn.prop('disabled', false).html(originalHtml);
            
            if (response.success) {
                // Select'i temizle
                $('#katilimciSecimSelect').val('').trigger('change');
                
                // Swal bildirimi göster
                swal({
                    type: response.type || 'success',
                    title: response.type === 'warning' ? 'Uyarı' : 'Başarılı',
                    html: response.message,
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
                
                if (response.type === 'success') {
                    // İlgili tab'ı aktif yap
                    $('.subtabs-nav .nav-link').removeClass('active');
                    $(`.subtabs-nav .nav-link[href="#kampanya_${getTabNameForTur(tur)}_arama"]`).addClass('active');
                    $('.tab-pane').removeClass('show active');
                    $(`#kampanya_${getTabNameForTur(tur)}_arama`).addClass('show active');
                    
                    // Tüm tabloların page'lerini sıfırla ve yeniden yükle
                    currentPages2 = { 1: 1, 2: 1, 3: 1, 4: 1 };
                    
                    [1, 2, 3, 4].forEach(turValue => {
                        const input = getSearchInputForTur(turValue);
                        const searchTerm = input ? input.val() : '';
                        loadKampanyaDetaylari(1, turValue, searchTerm);
                    });
                }
                
            } else {
                swal({
                    type: "error",
                    title: "Hata",
                    html: response.message || 'Katılımcı eklenemedi.',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 3000,
                });
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html(originalHtml);
            
            let errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            swal({
                type: "error",
                title: "Hata",
                html: errorMessage,
                showCloseButton: false,
                showCancelButton: false,
                showConfirmButton: false,
                timer: 3000,
            });
            console.error('Katılımcı ekleme hatası:', xhr);
        }
    });
});

// Tür değerine göre tab adını döndüren yardımcı fonksiyon
function getTabNameForTur(tur) {
    switch(parseInt(tur)) {
        case 1: return 'tum';
        case 2: return 'katilanlar';
        case 3: return 'katilmayanlar';
        case 4: return 'beklenen';
        default: return 'tum';
    }
}

// Diğer işlevler için Swal bildirimleri
$(document).on('click', '#kampanyabeklenenleriara', function() {
    swal({
        type: "info",
        title: "Bilgi",
        html: 'Beklenenler listesi için tekrar arama isteği gönderildi.',
        showCloseButton: false,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000,
    });
});

$(document).on('click', '#kampanyabeklenenleritekrarara', function() {
    swal({
        type: "info",
        title: "Bilgi",
        html: 'Katılmayanlar için tekrar arama isteği gönderildi.',
        showCloseButton: false,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000,
    });
});

// Mesaj İçeriği modalını açma butonu
$(document).on('click', '#mesajIcerigiBtn', function() {
    $('#mesajIcerigiModal').modal('show');
});

// Swal için CSS güncellemesi
const originalSwal = window.swal;
if (originalSwal) {
    const setupSwalButtons = () => {
        setTimeout(() => {
            $('.swal2-confirm').css({
                'background-color': '#e53e3e',
                'border-color': '#e53e3e'
            });
            
            $('.swal2-cancel').css({
                'background-color': '#6c757d',
                'border-color': '#6c757d'
            });
        }, 100);
    };
    
    window.swal = function(obj) {
        const result = originalSwal(obj);
        setupSwalButtons();
        return result;
    };
    
    Object.assign(window.swal, originalSwal);
}