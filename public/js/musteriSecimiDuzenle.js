class MusteriSecimiDuzenle {
    constructor(config) {
       this.config = {
            containerId: '#musteriListesiGrupSMSDuzenle',
            seciliMusteriSayisi: '#grupSMSSeciliMusterilerDuzenle',
            hepsiniSecButon: '#tumunuSecGrupSMSDuzenle',
            musteriAramaInput: '#musteriarama_grupsmsDuzenle',
            ajaxUrl: '/isletmeyonetim/musteriportfoydropliste',
            yukleniyorElement: '#musteriYukleniyorDuzenle',
            ilkMesajElement: '#musteriListesiIlkMesajDuzenle',
            toplamMusteriSayisi: '#toplamMusteriSayisiDuzenle',
            toplamMusteriSayisiFooter: '#toplamMusteriSayisiFooterDuzenle',
            gosterilenMusteriSayisi: '#gosterilenMusteriSayisiDuzenle',
            mevcutSeciliIdler: [], // BU SATIRI EKLEYİN
            ...config
        };

        this.state = {
            hepsiSecili: false,
            seciliIdler: new Set(this.config.mevcutSeciliIdler.map(String)),
            toplamMusteriler: 0,
            currentPage: 1,
            perPage: 1000,
            aramaTerimi: '',
            currentFilter: '0',
            isLoading: false,
            isFirstLoad: true,
            lastSearchTime: 0,
            searchDelay: 500,
            hasMore: true,
            allCustomers: [],
            scrollLocked :false,

            mevcutMusterilerYuklendi: false // Yeni ekle
        };

        this.init();
    }

    init() {
        this.bindEvents();
        this.musterileriGetir(1, false);
    }

    bindEvents() {
        const self = this;

        // Tümünü Seç
        $(document).on('change', this.config.hepsiniSecButon, function(e) {
            self.tumunuSecToggle(e.target.checked);
        });

        // Bireysel müşteri checkbox'ları
        $(this.config.containerId).on('change', '.musteri-secimi-checkbox', function(e) {
            self.bireyselSecimToggle($(this).val(), e.target.checked);
        });

        // Arama input - Debounce ile
        let searchTimeout;
        $(this.config.musteriAramaInput).on('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val().trim();
            
            searchTimeout = setTimeout(() => {
                self.state.aramaTerimi = searchTerm;
                self.state.currentPage = 1;
                self.state.hasMore = true;
                self.musterileriGetir(1, false);
            }, self.state.searchDelay);
        });

        // Lazy load scroll
        $(this.config.containerId).on('scroll', function() {
            self.handleScroll($(this));
        });

        // Modal açıldığında
        $('#grup_sms_duzenle_modal').on('shown.bs.modal', function() {
            if (self.state.isFirstLoad) {
                self.musterileriGetir(1, false);
            }
        });
    }

    tumunuSecToggle(checked) {
        this.state.hepsiSecili = checked;
        
        if (checked) {
            // Cache'den tüm ID'leri al
            const allIds = this.state.allCustomers.map(c => c.id);

            this.state.seciliIdler = new Set(allIds);
            $.ajax({
                url: '/isletmeyonetim/musteriportfoydropliste',
                method: 'POST',
                data: {
                  page: 1,
                  perPage: this.toplamMusteriler,
                  filtre: this.currentFilter,
                  search: this.aramaTerimi,
                  _token: $('input[name="_token"]').val()
                },
                success: (res) => {
                  this.state.seciliIdler = new Set(res.musteriIdler); 
                  
                  $(this.config.containerId + ' .musteri-secimi-checkbox').prop('checked', true);
                  
                }
              });
            
            // Görünen tüm checkbox'ları işaretle
            //$(this.config.containerId + ' .musteri-secimi-checkbox').prop('checked', true);
        } else {
            this.state.seciliIdler.clear();
            $(this.config.containerId + ' .musteri-secimi-checkbox').prop('checked', false);
        }
        
        this.seciliElemanSayisiniGuncelle();
    }

    bireyselSecimToggle(userId, isChecked) {
        if (isChecked) {
            this.state.seciliIdler.add(userId);
        } else {
            this.state.seciliIdler.delete(userId);
            // Bir müşteri kaldırıldıysa, "Tümünü Seç" işaretini kaldır
            this.state.hepsiSecili = false;
            $(this.config.hepsiniSecButon).prop('checked', false);
        }
        
        this.seciliElemanSayisiniGuncelle();
    }

    musteriListesiRenderEt(customers, append = false) {
        const $list = $(this.config.containerId);
        
        // İlk yükleme mesajını gizle
        if (this.state.isFirstLoad) {
            $(this.config.ilkMesajElement).hide();
            this.state.isFirstLoad = false;
        }

        if (!append) {
            $list.empty();
            
            // Tümünü seç checkbox'ını ekle
            if (customers.length > 0) {
                const hepsiniSecItem = $(`
                    <div class="musteri-item hepsini-sec" style="background: #f8f9fa; padding: 10px 15px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tumunuSecGrupSMSDuzenle">
                            <label class="form-check-label font-weight-600" for="tumunuSecGrupSMSDuzenle">
                                Tümünü Seç
                            </label>
                        </div>
                    </div>
                `);
                $list.append(hepsiniSecItem);
            }
        }

        // Müşterileri render et
        customers.forEach(customer => {
            const item = this.createMusteriItem(customer);
            $list.append(item);
        });

        // Sayıları güncelle
        this.updateCounts(customers.length, append);
    }

    createMusteriItem(customer) {
         const userId = String(customer.id);
    const isChecked =
        this.state.hepsiSecili || this.state.seciliIdler.has(userId);
        const ad = customer.name || customer.ad || customer.isim || '(İsimsiz)';
        const telefon = customer.phone || customer.telefon || '';
        
        return $(`
            <div class="musteri-item">
                <div class="form-check">
                    <input class="form-check-input musteri-secimi-checkbox" 
                           type="checkbox" 
                           value="${userId}"
                           ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label">
                        <strong>${this.escapeHtml(ad)}</strong>
                        ${telefon ? `<br><small class="text-muted">${this.escapeHtml(telefon)}</small>` : ''}
                    </label>
                </div>
            </div>
        `);
    }

    updateCounts() {
        const seciliSayisi = this.state.hepsiSecili
            ? this.state.toplamMusteriler
            : this.state.seciliIdler.size;

        $(this.config.seciliMusteriSayisi)
            .text(`${seciliSayisi} müşteri seçildi`);

        $(this.config.toplamMusteriSayisi)
            .text(`${this.state.toplamMusteriler} müşteri`);

        $(this.config.toplamMusteriSayisiFooter)
            .text(this.state.toplamMusteriler);
    }


    musterileriGetir(page = 1, append = false) {
        if (this.state.isLoading) return;
        
        this.state.isLoading = true;
        this.showLoading(!append);

        const requestData = {
            page: page,
            perPage: this.state.perPage,
            filtre: this.state.currentFilter,
            search: this.state.aramaTerimi,
            salonId: $('input[name="sube"]').val(),
            _token: $('input[name="_token"]').val()
        };

        $.ajax({
            url: this.config.ajaxUrl,
            method: 'POST',
            data: requestData,
            dataType: 'json',
            success: (res) => {
                this.handleSuccess(res, page, append);
            },
            error: (xhr) => {
                this.handleError(xhr, append);
            },
            complete: () => {
                this.state.isLoading = false;
                this.hideLoading();
            }
        });
    }

    handleSuccess(res, page, append) {
        const customers = res.customers || [];
        const total = res.total || 0;
        
        if (page === 1) {
            this.state.allCustomers = customers.slice(); // Cache'le
        } else {
            this.state.allCustomers.push(...customers);
        }
        
        this.state.toplamMusteriler = total;
        this.state.hasMore = (page * this.state.perPage) < total;
        
        this.musteriListesiRenderEt(customers, append);
        this.seciliElemanSayisiniGuncelle();
        this.restoreSelections();
        this.state.scrollLocked = false;


    }
    restoreSelections() {
        $(this.config.containerId + ' .musteri-secimi-checkbox').each((_, el) => {
           const id = String($(el).val());
            if (this.state.seciliIdler.has(id)) {
                $(el).prop('checked', true);
            }
        });
    }
    mevcutIdleriGuncelle(idler) {
        this.state.seciliIdler = new Set(idler.map(String));

        this.state.hepsiSecili = false;

        // Görünen checkbox'ları tekrar işaretle
        $(this.config.containerId + ' .musteri-secimi-checkbox').each((_, el) => {
            const id = String($(el).val());
            $(el).prop('checked', this.state.seciliIdler.has(id));
        });

        this.seciliElemanSayisiniGuncelle();
    }
    handleError(xhr, append) {
        console.error('Müşteri yükleme hatası:', xhr);
        
        if (!append) {
            $(this.config.containerId).html(`
                <div class="text-center py-5 text-danger">
                    <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Müşteriler yüklenirken bir hata oluştu.</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                        Tekrar Dene
                    </button>
                </div>
            `);
        }
    }

    handleScroll($container) {
        if (this.state.scrollLocked) return;

        const scrollTop = $container.scrollTop();
        const containerHeight = $container.innerHeight();
        const scrollHeight = $container[0].scrollHeight;

        if (scrollTop + containerHeight >= scrollHeight - 100) {
            if (this.state.hasMore && !this.state.isLoading) {
                this.state.scrollLocked = true;
                this.state.currentPage++;

                this.musterileriGetir(this.state.currentPage, true);
            }
        }
    }


    seciliElemanSayisiniGuncelle() {
        let seciliSayi = this.state.seciliIdler.size;
        
        if (this.state.hepsiSecili) {
            seciliSayi = this.state.toplamMusteriler;
        }
        
        $(this.config.seciliMusteriSayisi).text(`${seciliSayi} müşteri seçildi`);
    }

    showLoading(showFull = false) {
        if (showFull) {
            $(this.config.yukleniyorElement).show();
        } else if (!this.state.isFirstLoad) {
            // Alt kısımda mini loading göster
            $(this.config.containerId).append(`
                <div class="text-center py-2" id="miniLoadingDuzenle">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                        <span class="sr-only">Yükleniyor...</span>
                    </div>
                </div>
            `);
        }
    }

    hideLoading() {
        $(this.config.yukleniyorElement).hide();
        $('#miniLoadingDuzenle').remove();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getSelectedIdsForForm() {
        
            return Array.from(this.state.seciliIdler);
         
    }
}

// Document Ready
$(document).ready(function() {
    window.musteriSecimi = new MusteriSecimiDuzenle();
    
    // Form submit
    $('#grup_sms_formuDuzenle').on('submit', function(e) {
        e.preventDefault();
        
        handleGrupDuzenle();
    });
    // Grup düzenleme modal'ı açılırken mevcut müşteri ID'lerini al
    $('#grup_sms_tablo').on('click', 'button[name="grup_duzenle"]', function(e) {
        console.log('Düzenle açıldı');
        const grupId = $(this).attr('data-value');
        const tds = $(this).closest('tr').children('td');
        const grupAdi = tds[0].innerHTML.trim();
        
        $('#grup_adiDuzenle').val(grupAdi);
        $('#grup_idDuzenle').val(grupId);
        
        // Modal'ı göster
        $('#grup_sms_duzenle_modal').modal('show');
        
        // Mevcut müşterileri getir
        $.ajax({
            url: '/isletmeyonetim/grup-bilgileri-getir',
            method: 'POST',
            data: {
                grup_id: grupId,
                _token: $('input[name="_token"]').val()
            },
            dataType: 'json',
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    const mevcutMusteriIdleri = response.musteri_idler || [];
                    console.log('Mevcut müşteri ID\'leri:', mevcutMusteriIdleri);
                    
                    // İlk kontrol: window.musteriSecimiDuzenle var mı?
                    console.log('window.musteriSecimi:', window.musteriSecimi);
                    console.log('typeof:', typeof window.musteriSecimi);
                    
                    if (typeof window.musteriSecimi === 'undefined') {
                        console.log('Instance yok, yeni oluşturuluyor...');
                        window.musteriSecimi = new MusteriSecimiDuzenle({
                            mevcutSeciliIdler: mevcutMusteriIdleri
                        });
                    } else {
                        console.log('Instance var, güncelleniyor...');
                        // Instance varsa güncelle
                        window.musteriSecimi.mevcutIdleriGuncelle(mevcutMusteriIdleri);
                        
                    }
                } else {
                    console.error('Grup bilgileri alınamadı:', response);
                }
            },
            error: function(xhr) {
                console.error('AJAX hatası:', xhr);
            }
        });
    });
});

function handleGrupDuzenle() {
    const seciliMusteriler = window.musteriSecimi.getSelectedIdsForForm();
    const grupAdi = $('#grup_adiDuzenle').val().trim();
    
    if (!grupAdi) {
        showAlert('Lütfen grup adı giriniz!', 'warning');
        return;
    }
    
    if (seciliMusteriler === 'all' && window.musteriSecimi.state.toplamMusteriler === 0) {
        showAlert('Henüz müşteri bulunmuyor!', 'warning');
        return;
    }
    
    if (seciliMusteriler !== 'all' && seciliMusteriler.length === 0) {
        showAlert('Lütfen en az bir müşteri seçin!', 'warning');
        return;
    }
    
    const formData = new FormData($('#grup_sms_formuDuzenle')[0]);
    formData.append('musteri_idler', JSON.stringify(seciliMusteriler));
    console.log('müşteri idler '+JSON.stringify(seciliMusteriler));
    // Submit butonunu disable et
    const submitBtn = $('#grup_sms_formuDuzenle button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');
    
    $.ajax({
        url: $('#grup_sms_formuDuzenle').attr('action') || '/isletmeyonetim/grup-olustur',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('Grup başarıyla oluşturuldu!', 'success');
                $('#grup_sms_duzenle_modal').modal('hide');
                $('#grup_adiDuzenle').val('');
                $('#grup_sms_tablo').DataTable().destroy();
                $('#grup_sms_tablo').DataTable({
             
                   autoWidth: false,
                   responsive: true,
                   columns:[
                          { data: 'grup_adi', className: "text-center",   },
                          { data: 'grup_katilimci_sayisi',className: "text-center", },
                          { data: 'islemler',className: "text-right"  },
                   ],
                   data: response.grup,
                   "language" : {
             
                           "url" : "//cdn.datatables.net/plug-ins/1.10.20/i18n/Turkish.json",
                           searchPlaceholder: "Ara",
                           paginate: {
                               next: '<i class="ion-chevron-right"></i>',
                               previous: '<i class="ion-chevron-left"></i>'  
                           }
                   },
             
                });
            } else {
                showAlert(response.message || 'Bir hata oluştu!', 'error');
            }
        },
        error: function(xhr) {
            showAlert('Grup oluşturulurken bir hata oluştu!', 'error');
            console.error(xhr);
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

function showAlert(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    // Varolan alert'leri temizle
    $('.alert-dismissible').remove();
    
    const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(alert);
    
    // 5 saniye sonra otomatik kapat
    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}

function modalbaslikata(baslik, formId) {
    $('#grupSMSModalLabelDuzenle').text(baslik);
    // Formu resetle
    $('#' + formId)[0].reset();
    // Müşteri seçimini resetle
    if (window.musteriSecimi) {
        window.musteriSecimi.state.seciliIdler.clear();
        window.musteriSecimi.state.hepsiSecili = false;
        window.musteriSecimi.seciliElemanSayisiniGuncelle();
        $('.musteri-secimi-checkbox').prop('checked', false);
        $('#tumunuSecGrupSMSDuzenle').prop('checked', false);
    }
}