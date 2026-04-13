class MusteriSecimi {
    constructor(config) {
        this.config = {
            containerId: '#musteriListesiGrupSMS',
            seciliMusteriSayisi: '#grupSMSSeciliMusteriler',
            hepsiniSecButon: '#tumunuSecGrupSMS',
            musteriAramaInput: '#musteriarama_grupsms',
            ajaxUrl: '/isletmeyonetim/musteriportfoydropliste',
            yukleniyorElement: '#musteriYukleniyor',
            ilkMesajElement: '#musteriListesiIlkMesaj',
            toplamMusteriSayisi: '#toplamMusteriSayisi',
            toplamMusteriSayisiFooter: '#toplamMusteriSayisiFooter',
            gosterilenMusteriSayisi: '#gosterilenMusteriSayisi',
            ...config
        };

        this.state = {
            hepsiSecili: false,
            seciliIdler: new Set(),
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
            allCustomerIds: new Set(), // SADECE BU SATIRI EKLEYİN
            isFetchingAll: false // VE BU SATIRI
        };

        this.init();
    }

    // ... diğer metodlar aynı ...

    handleSuccess(res, page, append) {
        const customers = res.customers || [];
        const total = res.total || 0;
        
        if (page === 1) {
            this.state.allCustomers = customers.slice();
            this.state.allCustomerIds.clear(); // Temizle
        } else {
            this.state.allCustomers.push(...customers);
        }
        
        // Tüm ID'leri allCustomerIds'e ekle
        customers.forEach(customer => {
            this.state.allCustomerIds.add(customer.id.toString());
        });
        
        this.state.toplamMusteriler = total;
        this.state.hasMore = (page * this.state.perPage) < total;
        
        this.musteriListesiRenderEt(customers, append);
        this.seciliElemanSayisiniGuncelle();
    }

    async tumunuSecToggle(checked) {
        if (checked) {
            this.state.hepsiSecili = true;
            this.state.isFetchingAll = true;
            
            // Önce görünenleri seç
            const visibleIds = this.state.allCustomers.map(c => c.id);
            this.state.seciliIdler = new Set(visibleIds);
            
            $(this.config.containerId + ' .musteri-secimi-checkbox').prop('checked', true);
            
            // Arka planda tüm sayfaları yükle
            this.fetchAllPagesInBackground();
        } else {
            this.state.hepsiSecili = false;
            this.state.seciliIdler.clear();
            $(this.config.containerId + ' .musteri-secimi-checkbox').prop('checked', false);
        }
        
        this.seciliElemanSayisiniGuncelle();
    }

    // Arka planda tüm sayfaları yükle
    async fetchAllPagesInBackground() {
        console.log('Tüm sayfalar arka planda yükleniyor...');
        
        let page = this.state.currentPage + 1; // Bir sonraki sayfadan başla
        
        while (this.state.hasMore && !this.state.isLoading) {
            try {
                await this.fetchPageForSelection(page);
                page++;
                
                // Her 5 sayfada bir kısa bekle (performans için)
                if (page % 5 === 0) {
                    await this.delay(100);
                }
            } catch (error) {
                console.error('Sayfa yükleme hatası:', error);
                break;
            }
        }
        
        this.state.isFetchingAll = false;
        console.log('Tüm sayfalar yüklendi. Toplam ID:', this.state.allCustomerIds.size);
    }

    fetchPageForSelection(page) {
        return new Promise((resolve, reject) => {
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
                    const customers = res.customers || [];
                    const total = res.total || 0;
                    
                    // ID'leri allCustomerIds'e ekle
                    customers.forEach(customer => {
                        this.state.allCustomerIds.add(customer.id.toString());
                    });
                    
                    this.state.hasMore = (page * this.state.perPage) < total;
                    resolve();
                },
                error: (xhr) => {
                    reject(xhr);
                }
            });
        });
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    getSelectedIdsForForm() {
        if (this.state.hepsiSecili) {
            // Tümünü seç aktifse, allCustomerIds'den tüm ID'leri gönder
            return Array.from(this.state.allCustomerIds);
        } else {
            return Array.from(this.state.seciliIdler);
        }
    }
}