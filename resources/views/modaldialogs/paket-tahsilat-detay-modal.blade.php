<div id="paket_odeme_detay_modal" class="modal modal-top fade" data-backdrop="true" data-keyboard="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 640px; width: 96%;">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 20px 60px rgba(31,23,75,0.25); overflow: hidden;">

            {{-- Gradient header --}}
            <div style="background: linear-gradient(135deg, #4338ca 0%, #7c3aed 55%, #c026d3 100%); color: #fff; padding: 16px 20px; position: relative;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.18); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-credit-card" style="font-size: 1.1rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <h5 style="margin: 0; font-weight: 600; font-size: 1.02rem; color: #fff;">Paket Tahsilat Geçmişi</h5>
                        <span style="font-size: 0.78rem; color: rgba(255,255,255,0.85);">Bu pakete ait ödeme kayıtları</span>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.85; font-size: 1.4rem; line-height: 1;">×</button>
                </div>
            </div>

            {{-- Bilgi banneri: paket satildi, fiyat 0 ise tahsilat yapilamaz uyarisi --}}
            <div style="padding: 14px 20px 0;">
                <div id="paket_tahsilat_info_banner" style="background: linear-gradient(135deg, #fff8e1, #fef3c7); border: 1px solid #fcd34d; border-radius: 10px; padding: 12px 14px; display: flex; gap: 10px; align-items: flex-start;">
                    <i class="fa fa-info-circle" style="color: #d97706; font-size: 1.1rem; margin-top: 2px; flex-shrink: 0;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #92400e; font-size: 0.9rem; margin-bottom: 3px;">Satış kaydı oluşturuldu</div>
                        <div style="font-size: 0.83rem; color: #78350f; line-height: 1.45;" id="paket_tahsilat_info_metin">
                            Eğer satış sırasında <strong>fiyat girilmediyse</strong>, otomatik tahsilat yapılamaz. Fiyatı eklemek/güncellemek için
                            <strong>Satış Takibi</strong> menüsünden ilgili kaydı düzenleyin.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tahsilat listesi (backend'den HTML olarak gelir) --}}
            <div class="modal-body" id="paket_gecmis_odemeler" style="padding: 14px 20px 20px;">
                {{-- Backend dolduracak. Bos ise kullaniciya 'henuz tahsilat yok' mesaji gosterilecek (asagidaki CSS empty-state) --}}
            </div>

            <div style="padding: 0 20px 16px; display: flex; justify-content: flex-end; gap: 8px;">
                <a href="javascript:void(0)" onclick="$('#paket_odeme_detay_modal').modal('hide');" style="background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; padding: 7px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
                    <i class="fa fa-times"></i> Kapat
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Empty state: tablo bos ise ve sadece header gorunuyorsa kullaniciya bir info goster */
    #paket_gecmis_odemeler table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }
    #paket_gecmis_odemeler table thead th {
        background: #faf5ff;
        color: #5b21b6;
        font-weight: 700;
        padding: 10px 12px;
        text-align: left;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 2px solid #ede9fe;
    }
    #paket_gecmis_odemeler table tbody td {
        padding: 9px 12px;
        border-bottom: 1px solid #f3f0fc;
        color: #1f2937;
    }
    #paket_gecmis_odemeler table tbody tr:last-child td { border-bottom: none; }
    #paket_gecmis_odemeler table tbody tr:hover td { background: #faf5ff; }
    #paket_gecmis_odemeler table tbody:empty + .v2-empty,
    #paket_gecmis_odemeler table tbody tr:not(:has(td)):only-child { display: none; }
    /* Henuz tahsilat yok mesaji icin yer tutucu */
    #paket_gecmis_odemeler:has(table tbody:empty)::after,
    #paket_gecmis_odemeler:not(:has(table tbody tr))::after {
        content: 'Henüz tahsilat kaydı yok';
        display: block;
        text-align: center;
        padding: 24px 12px;
        color: #9ca3af;
        font-size: 0.88rem;
        background: #fafafa;
        border-radius: 8px;
        margin-top: 8px;
    }
</style>

<script>
    // Modal her acildiginda info banner'i resetle (alt akislardan icerik degismis olabilir)
    $(document).on('shown.bs.modal', '#paket_odeme_detay_modal', function(){
        // Cocuk tablo gercekten bos mu kontrol et — eger bos ise banner'da
        // metnini "fiyat girilmedi" odakli tut, doluysa "gecmis odemeler" odakli yap.
        var $body = $('#paket_gecmis_odemeler');
        var hasRows = $body.find('table tbody tr').length > 0;
        var $banner = $('#paket_tahsilat_info_banner');
        var $title = $banner.find('div > div:first-child');
        var $metin = $('#paket_tahsilat_info_metin');
        if(hasRows){
            $title.text('Tahsilat geçmişi');
            $metin.html('Bu pakete ait yapılmış tahsilat kayıtları aşağıda listeleniyor.');
            $banner.css({
                'background': 'linear-gradient(135deg, #f0fdf4, #dcfce7)',
                'border-color': '#86efac'
            });
            $banner.find('i').css('color', '#16a34a');
            $title.css('color', '#166534');
            $metin.css('color', '#14532d');
        } else {
            $title.text('Satış kaydı oluşturuldu — tahsilat yok');
            $metin.html('Yapılan satışta <strong>fiyat girilmediği için</strong> otomatik tahsilat yapılamadı. Fiyat eklemek/güncellemek için <strong>Satış Takibi</strong> menüsünden ilgili kaydı düzenleyin.');
            $banner.css({
                'background': 'linear-gradient(135deg, #fff8e1, #fef3c7)',
                'border-color': '#fcd34d'
            });
            $banner.find('i').css('color', '#d97706');
            $title.css('color', '#92400e');
            $metin.css('color', '#78350f');
        }
    });
</script>
