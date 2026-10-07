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
    
    // Modern rapor: Tumu filtresiyle ac
    currentPages2 = { 1: 1, 2: 1, 3: 1, 4: 1 };
    rrTur = 1;
    $('#rrArama').val('');
    $('#rrPills .rr-pill').removeClass('is-active');
    $('#rrPills .rr-pill[data-tur="1"]').addClass('is-active');
    $('#rrBaslik').text('Tüm Katılımcılar');
    rrYukle(true);

    // Müşteri listesini yükle
    loadMusteriListesiForSelect();
    
    $('#kampanya_detay_modal').modal('show');
});

// Tab'a tiklaninca ilgili turu yukle: 1=Tumu, 2=Indirim Kullanan, 3=Indirim Kullanmayan,
// 4=Beklenenler. (Eskiden sadece "Tumu" aciliyordu; diger sekmeler tiklaninca bos/0 kaliyordu.)
// ===== MODERN tek-tablo + pill filtre (rapor) =====
var rrTur = 1, rrPage = 1, rrTotal = 0, rrLoading = false, rrSearchTimer = null;
var rrPerPage = 50;

function rrYukle(reset) {
    if (rrLoading) return;
    rrLoading = true;
    if (reset) rrPage = 1;
    $.ajax({
        url: '/isletmeyonetim/kampanyadetay',
        method: 'POST',
        data: {
            kampanyaid: aramaDetayId2,
            sube: $('input[name="sube"]').val(),
            page: rrPage,
            search: $('#rrArama').val() || '',
            perPage: rrPerPage,
            katilimDurumu: rrTur,
            _token: $('input[name="_token"]').val()
        },
        success: function (res) {
            if (res.kampanya) {
                $('#paket_adi').text(res.kampanya.gorev_turu || '');
                $('#kampanya_seans').text(res.kampanya.paket_isim || '');
                $('#kampanya_katilimci').text(res.kampanya.katilimci_sayisi || '0');
                $('#kampanya_hizmeti').text(res.kampanya.hizmet_adi || '-');
                if (res.kampanya.mesaj != null) $('#mesajIcerigiContent').text(res.kampanya.mesaj);
                else $('#mesajIcerigiContent').text('Bu kampanya için mesaj içeriği bulunmamaktadır.');
            }
            if (res.sayilar) {
                $('#rrc1').text(res.sayilar.tumu || 0);
                $('#rrc5').text(res.sayilar.katilan || 0);
                $('#rrc6').text(res.sayilar.katilmayan || 0);
                $('#rrc7').text(res.sayilar.ulasilamadi || 0);
                $('#rrc2').text(res.sayilar.indirimKullanan || 0);
                $('#rrc3').text(res.sayilar.indirimKullanmayan || 0);
            }
            rrTotal = res.total || 0;
            var tbody = $('#rrTablo tbody');
            if (rrPage === 1) { tbody.empty(); $('#rrContainer').scrollTop(0); }
            var rows = [];
            (res.data || []).forEach(function (item) {
                var st = item.durum || '', sc = '';
                if (st.indexOf('Katıldı') > -1) sc = 'status-aktif';
                else if (st.indexOf('Katılmadı') > -1 || st.indexOf('Ulaşılamadı') > -1) sc = 'status-pasif';
                else sc = 'status-beklemede';
                var del = '<button class="btn btn-sm btn-danger delete-katilimci" data-value="' + item.id + '" data-adsoyad="' + (item.ad_soyad || '') + '" data-tablo="#rrTablo" data-tur="' + rrTur + '"><i class="fa fa-trash"></i></button>';
                rows.push('<tr><td>' + (item.ad_soyad || '') + '</td><td>' + formatPhoneNumber(item.telefon || '') + '</td><td><span class="status-badge ' + sc + '">' + st + '</span></td><td>' + del + '</td></tr>');
            });
            if ((res.data || []).length === 0 && rrPage === 1) $('#rrEmpty').show(); else $('#rrEmpty').hide();
            tbody.append(rows.join(''));
            rrPage = rrPage + 1;
            $('#rrTekrarAraFooter').toggle(rrTur === 7 && (res.total || 0) > 0);
            rrLoading = false;
        },
        error: function (xhr) { console.error('rapor yukleme hatasi', xhr); rrLoading = false; }
    });
}

// Eski cagiranlar icin uyumluluk katmani (katilimci ekle/sil basarisinda cagriliyor).
function loadKampanyaDetaylari(page, tur, hasta) {
    if (tur) rrTur = parseInt(tur, 10) || rrTur;
    rrYukle(true);
}

// Pill filtre tiklama
$(document).on('click', '#rrPills .rr-pill', function () {
    $('#rrPills .rr-pill').removeClass('is-active');
    $(this).addClass('is-active');
    rrTur = parseInt($(this).data('tur'), 10) || 1;
    $('#rrBaslik').text($(this).data('baslik') || 'Katılımcılar');
    rrYukle(true);
});

// Arama (debounce)
$(document).on('input', '#rrArama', function () {
    clearTimeout(rrSearchTimer);
    rrSearchTimer = setTimeout(function () { rrYukle(true); }, 350);
});

// Sonsuz kaydirma (tek konteyner)
$(document).on('scroll', '#rrContainer', function () {
    var el = this;
    if (el.scrollHeight - el.scrollTop - el.clientHeight < 120 && !rrLoading && ((rrPage - 1) * rrPerPage) < rrTotal) {
        rrYukle(false);
    }
});

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