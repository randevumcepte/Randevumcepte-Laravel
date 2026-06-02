@php
    // V2 — Onerilen tasarima gore yeniden duzenlenmis Yeni Randevu modali.
    // Eskisi (modal-view-event-add) bozulmadan paralel calisir: tetikleyici #v2-randevu-ac.
    // Saha calismasi: bu modal sadece UI onizlemedir; submit handler ileride baglanacak.
    $__turu = $isletme->randevu_takvim_turu ?? 0;
    $__odaVar = !in_array($__turu, [1, 2]); // 1: personel, 2: cihaz -> oda gizli
    $__musteriEtiket = in_array(($isletme->salon_turu_id ?? 0), [15, 28, 29]) ? 'Danışan' : 'Müşteri';
@endphp

<div id="modal-view-event-add-v2" class="modal modal-top fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="max-width: 640px; width: 96%; margin: 0 auto;">
        <div class="modal-content v2-modal">

            <div class="modal-header v2-header">
                <div class="d-flex align-items-center" style="gap:10px;">
                    <i class="fa fa-calendar-plus-o" style="color:#fff;opacity:0.95;"></i>
                    <h5 class="m-0" style="color:#fff;font-weight:600;font-size:0.98rem;">Yeni Randevu <span style="opacity:0.7;font-size:0.7rem;font-weight:400;">(v2 önizleme)</span></h5>
                </div>
                <div class="d-flex align-items-center" style="gap:6px;">
                    <button type="button" class="btn btn-sm v2-mode-toggle" data-mode="randevu" title="Saat Kapamaya geç">
                        <i class="fa fa-lock"></i>
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff;opacity:0.9;font-size:1.4rem;line-height:1;">×</button>
                </div>
            </div>

            <div class="modal-body p-0">

                {{-- =================== RANDEVU FORM =================== --}}
                <form id="randevuekleform_v2" class="v2-form v2-mode-randevu" method="POST" action="#">
                    {!! csrf_field() !!}
                    <input type="hidden" name="sube" value="{{ $isletme->id }}">

                    {{-- Musteri --}}
                    <div class="v2-block">
                        <div class="v2-row">
                            <div class="v2-col-grow">
                                <label class="v2-label">{{ $__musteriEtiket }}</label>
                                <select name="adsoyad" id="v2_musteri_id" class="form-control v2-input">
                                    <option></option>
                                </select>
                            </div>
                            <button class="btn btn-sm v2-btn-add" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal" title="Yeni {{ $__musteriEtiket }}">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Tarih + Saat --}}
                    <div class="v2-block">
                        <div class="v2-row" style="gap:8px;">
                            <div style="flex:3;">
                                <label class="v2-label">Tarih</label>
                                <input type="text" name="tarih" id="v2_tarih" class="form-control v2-input" autocomplete="off" value="{{ date('Y-m-d') }}">
                            </div>
                            <div style="flex:2;">
                                <label class="v2-label">Saat</label>
                                <select name="saat" id="v2_saat" class="form-control v2-input">
                                    @for ($j = strtotime('07:00'); $j < strtotime('23:15'); $j += 15 * 60)
                                        <option value="{{ date('H:i', $j) }}:00">{{ date('H:i', $j) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Hizmetler --}}
                    <div class="v2-block">
                        <div class="v2-block-header">
                            <span class="v2-section-title">Hizmet</span>
                            <div class="v2-actions">
                                <button type="button" class="btn btn-sm v2-btn-link" id="v2_paketleri_goster" disabled title="Müşteri seçildiğinde aktifleşir">
                                    <i class="fa fa-gift"></i> Paketler
                                </button>
                                <button type="button" class="btn btn-sm v2-btn-link v2-add-row" title="Hizmet satırı ekle">
                                    <i class="fa fa-plus"></i> Hizmet Ekle
                                </button>
                            </div>
                        </div>

                        {{-- Toplu uygula — varsayilan gizli, 2+ satir olunca acilir --}}
                        <div class="v2-bulk" id="v2_bulk_panel" style="display:none;">
                            <div class="v2-bulk-title">
                                <i class="fa fa-magic"></i> Tümüne uygula
                            </div>
                            <div class="v2-row" style="gap:6px;">
                                <div style="flex:2;">
                                    <select class="form-control v2-input v2-bulk-personel"><option value="">Personel...</option></select>
                                </div>
                                @if ($__odaVar)
                                <div style="flex:2;">
                                    <select class="form-control v2-input v2-bulk-oda"><option value="">Oda...</option></select>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="v2-services" id="v2_services">
                            {{-- ilk satir --}}
                            <div class="v2-service-row" data-index="0">
                                <div class="v2-row v2-row-tight">
                                    <div class="v2-col-grow">
                                        <label class="v2-label-sm">Hizmet</label>
                                        <select multiple name="randevuhizmetleriyeni_v2_0[]" class="form-control v2-input v2-hizmet" data-index="0">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="v2-row v2-row-tight" style="gap:6px;margin-top:4px;">
                                    <div style="flex:3;">
                                        <select name="randevupersonelleriyeni_v2[]" class="form-control v2-input v2-personel" data-index="0">
                                            <option value="">Personel...</option>
                                        </select>
                                    </div>
                                    @if ($__odaVar)
                                    <div style="flex:3;">
                                        <select name="randevuodalariyeni_v2[]" class="form-control v2-input v2-oda" data-index="0">
                                            <option value="">Oda...</option>
                                        </select>
                                    </div>
                                    @endif
                                    <div style="flex:1.4;">
                                        <input type="number" min="0" step="5" name="randevusureleriyeni_v2[]" class="form-control v2-input v2-sure" placeholder="dk" data-index="0">
                                    </div>
                                    <button type="button" class="btn v2-btn-icon v2-remove-row" disabled title="Sil">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notlar (collapsible) --}}
                    <div class="v2-block">
                        <button type="button" class="v2-toggle" data-target="#v2_notlar_body">
                            <i class="fa fa-sticky-note-o"></i> Not ekle
                            <i class="fa fa-chevron-down v2-chev"></i>
                        </button>
                        <div id="v2_notlar_body" class="v2-collapse" style="display:none;">
                            <textarea name="personel_notu" class="form-control v2-input" rows="2" placeholder="Randevu ile ilgili not..."></textarea>
                        </div>
                    </div>

                    {{-- Tekrarlayan (sadece checkbox; detaylar checkbox isaretlenince acilir) --}}
                    <div class="v2-block">
                        <label class="v2-switch-row" for="v2_tekrarlayan">
                            <span><i class="fa fa-refresh"></i> Tekrarlayan randevu</span>
                            <input type="checkbox" id="v2_tekrarlayan" name="tekrarlayan" class="v2-switch">
                        </label>
                        <div id="v2_tekrarlayan_body" class="v2-collapse" style="display:none;">
                            <div class="v2-row" style="gap:8px;">
                                <div style="flex:3;">
                                    <label class="v2-label-sm">Sıklık</label>
                                    <select name="tekrar_sikligi" class="form-control v2-input">
                                        <option value="+1 day">Her gün</option>
                                        <option value="+1 week" selected>Haftada bir</option>
                                        <option value="+2 weeks">2 haftada bir</option>
                                        <option value="+1 month">Her ay</option>
                                    </select>
                                </div>
                                <div style="flex:2;">
                                    <label class="v2-label-sm">Tekrar sayısı</label>
                                    <input type="number" min="1" max="52" name="tekrar_sayisi" class="form-control v2-input" value="4">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- =================== SAAT KAPAMA FORM =================== --}}
                @if (\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
                <form id="saatkapamaform_v2" class="v2-form v2-mode-kapama" method="POST" style="display:none;">
                    {!! csrf_field() !!}
                    <input type="hidden" name="sube" value="{{ $isletme->id }}">

                    <div class="v2-block">
                        <label class="v2-label">Personel</label>
                        <select name="personel" class="form-control v2-input"><option></option></select>
                    </div>

                    <div class="v2-block">
                        <div class="v2-row" style="gap:8px;">
                            <div style="flex:2;">
                                <label class="v2-label-sm">Tarih</label>
                                <input type="text" name="tarih" class="form-control v2-input" autocomplete="off" value="{{ date('Y-m-d') }}">
                            </div>
                            <div style="flex:1.5;">
                                <label class="v2-label-sm">Başlangıç</label>
                                <input type="time" name="saat" class="form-control v2-input">
                            </div>
                            <div style="flex:1.5;">
                                <label class="v2-label-sm">Bitiş</label>
                                <input type="time" name="saat_bitis" class="form-control v2-input">
                            </div>
                        </div>
                    </div>

                    <div class="v2-block">
                        <label class="v2-switch-row" for="v2_tum_gun">
                            <span><i class="fa fa-sun-o"></i> Tüm gün</span>
                            <input type="checkbox" id="v2_tum_gun" name="tum_gun" class="v2-switch">
                        </label>
                    </div>

                    <div class="v2-block">
                        <label class="v2-label-sm">Not</label>
                        <textarea name="personel_notu" class="form-control v2-input" rows="2"></textarea>
                    </div>
                </form>
                @endif
            </div>

            <div class="modal-footer v2-footer">
                <button type="button" class="btn btn-sm v2-btn-secondary" data-dismiss="modal">Vazgeç</button>
                <button type="submit" form="randevuekleform_v2" class="btn btn-sm v2-btn-primary v2-submit-randevu">
                    <i class="fa fa-check"></i> Randevu Oluştur
                </button>
                <button type="submit" form="saatkapamaform_v2" class="btn btn-sm v2-btn-warning v2-submit-kapama" style="display:none;">
                    <i class="fa fa-lock"></i> Saati Kapat
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ============ V2 Modal Stilleri (eski modali etkilemez) ============ */
#modal-view-event-add-v2 { z-index: 100003 !important; }
#modal-view-event-add-v2.show { display:flex !important; align-items:center !important; justify-content:center !important; }
#modal-view-event-add-v2 .modal-dialog { margin:0 auto !important; align-self:center !important; }
#modal-view-event-add-v2 .v2-modal {
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    overflow: hidden;
}
#modal-view-event-add-v2 .v2-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    padding: 10px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#modal-view-event-add-v2 .v2-mode-toggle {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: none;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.78rem;
}
#modal-view-event-add-v2 .v2-mode-toggle:hover { background: rgba(255,255,255,0.28); }
#modal-view-event-add-v2 .v2-mode-toggle.active { background: #fff; color: #7c3aed; }

#modal-view-event-add-v2 .modal-body { background: #fafafa; max-height: 70vh; overflow-y: auto; }
#modal-view-event-add-v2 .v2-form { padding: 12px 14px; }

#modal-view-event-add-v2 .v2-block {
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 8px;
}
#modal-view-event-add-v2 .v2-block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
#modal-view-event-add-v2 .v2-section-title {
    font-weight: 600;
    font-size: 0.82rem;
    color: #374151;
}
#modal-view-event-add-v2 .v2-actions { display: flex; gap: 4px; }

#modal-view-event-add-v2 .v2-row { display:flex; align-items:flex-end; gap:10px; }
#modal-view-event-add-v2 .v2-row-tight { gap: 6px; }
#modal-view-event-add-v2 .v2-col-grow { flex: 1; min-width: 0; }

#modal-view-event-add-v2 .v2-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
#modal-view-event-add-v2 .v2-label-sm {
    display: block;
    font-size: 0.68rem;
    color: #9ca3af;
    margin-bottom: 2px;
}
#modal-view-event-add-v2 .v2-input {
    height: 34px;
    font-size: 0.85rem;
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
}
#modal-view-event-add-v2 textarea.v2-input { height: auto; padding: 6px 8px; }
#modal-view-event-add-v2 .v2-input:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
    outline: none;
}

#modal-view-event-add-v2 .v2-btn-add {
    height: 34px;
    width: 34px;
    padding: 0;
    margin-bottom: 0;
    align-self: flex-end;
    background: #ede9fe;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
    border-radius: 6px;
}
#modal-view-event-add-v2 .v2-btn-add:hover { background:#ddd6fe; }

#modal-view-event-add-v2 .v2-btn-link {
    background: transparent;
    color: #4f46e5;
    border: 1px solid transparent;
    padding: 3px 8px;
    font-size: 0.76rem;
    border-radius: 5px;
}
#modal-view-event-add-v2 .v2-btn-link:hover:not(:disabled) { background:#eef2ff; }
#modal-view-event-add-v2 .v2-btn-link:disabled { opacity:0.45; cursor:not-allowed; }

#modal-view-event-add-v2 .v2-btn-icon {
    height:34px; width:34px; padding:0;
    background:#fef2f2; color:#dc2626;
    border:1px solid #fecaca; border-radius:6px;
    flex: 0 0 auto;
}
#modal-view-event-add-v2 .v2-btn-icon:hover:not(:disabled) { background:#fee2e2; }
#modal-view-event-add-v2 .v2-btn-icon:disabled { opacity:0.35; cursor:not-allowed; }

#modal-view-event-add-v2 .v2-bulk {
    background: #f5f3ff;
    border: 1px dashed #c4b5fd;
    border-radius: 6px;
    padding: 8px 10px;
    margin-bottom: 8px;
}
#modal-view-event-add-v2 .v2-bulk-title {
    font-size: 0.72rem;
    color: #6d28d9;
    font-weight: 600;
    margin-bottom: 5px;
}

#modal-view-event-add-v2 .v2-service-row {
    background: #fafafa;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 8px;
    margin-bottom: 6px;
}
#modal-view-event-add-v2 .v2-service-row:last-child { margin-bottom: 0; }

#modal-view-event-add-v2 .v2-toggle {
    width:100%;
    text-align: left;
    background: transparent;
    border: none;
    padding: 4px 0;
    color: #4f46e5;
    font-size: 0.82rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#modal-view-event-add-v2 .v2-toggle .v2-chev { transition: transform 0.2s; font-size: 0.7rem; }
#modal-view-event-add-v2 .v2-toggle.open .v2-chev { transform: rotate(180deg); }
#modal-view-event-add-v2 .v2-collapse { margin-top: 6px; }

#modal-view-event-add-v2 .v2-switch-row {
    display:flex;
    justify-content: space-between;
    align-items: center;
    margin: 0;
    font-size: 0.85rem;
    color: #374151;
    cursor: pointer;
}
#modal-view-event-add-v2 .v2-switch {
    appearance: none;
    width: 36px; height: 20px;
    background: #d1d5db;
    border-radius: 10px;
    position: relative;
    cursor: pointer;
    transition: background 0.2s;
}
#modal-view-event-add-v2 .v2-switch::after {
    content:'';
    position: absolute;
    top: 2px; left: 2px;
    width: 16px; height: 16px;
    background: #fff;
    border-radius: 50%;
    transition: left 0.2s;
}
#modal-view-event-add-v2 .v2-switch:checked { background: #7c3aed; }
#modal-view-event-add-v2 .v2-switch:checked::after { left: 18px; }

#modal-view-event-add-v2 .v2-footer {
    background: #fff;
    border-top: 1px solid #ececec;
    padding: 8px 14px;
    display: flex;
    justify-content: flex-end;
    gap: 6px;
}
#modal-view-event-add-v2 .v2-btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff; border: none;
    padding: 6px 14px;
    font-size: 0.82rem;
    border-radius: 6px;
}
#modal-view-event-add-v2 .v2-btn-warning {
    background: #f59e0b; color:#fff; border:none;
    padding: 6px 14px; font-size: 0.82rem; border-radius: 6px;
}
#modal-view-event-add-v2 .v2-btn-secondary {
    background: #f3f4f6; color:#374151; border:1px solid #e5e7eb;
    padding: 6px 12px; font-size: 0.82rem; border-radius: 6px;
}
</style>

<script>
(function(){
    // Eski modali bozmamak icin sadece v2 namespace'inde calisiyoruz
    const $modal = $('#modal-view-event-add-v2');
    if(!$modal.length) return;

    // ---- Hizmet satiri ekle ----
    function newRowHtml(idx){
        var odaCol = {{ $__odaVar ? 'true' : 'false' }} ?
            '<div style="flex:3;"><select name="randevuodalariyeni_v2[]" class="form-control v2-input v2-oda" data-index="'+idx+'"><option value="">Oda...</option></select></div>' : '';
        return ''+
        '<div class="v2-service-row" data-index="'+idx+'">'+
            '<div class="v2-row v2-row-tight">'+
                '<div class="v2-col-grow">'+
                    '<label class="v2-label-sm">Hizmet</label>'+
                    '<select multiple name="randevuhizmetleriyeni_v2_'+idx+'[]" class="form-control v2-input v2-hizmet" data-index="'+idx+'"><option></option></select>'+
                '</div>'+
            '</div>'+
            '<div class="v2-row v2-row-tight" style="gap:6px;margin-top:4px;">'+
                '<div style="flex:3;"><select name="randevupersonelleriyeni_v2[]" class="form-control v2-input v2-personel" data-index="'+idx+'"><option value="">Personel...</option></select></div>'+
                odaCol+
                '<div style="flex:1.4;"><input type="number" min="0" step="5" name="randevusureleriyeni_v2[]" class="form-control v2-input v2-sure" placeholder="dk" data-index="'+idx+'"></div>'+
                '<button type="button" class="btn v2-btn-icon v2-remove-row" title="Sil"><i class="fa fa-trash"></i></button>'+
            '</div>'+
        '</div>';
    }

    function reindexRows(){
        $('#v2_services .v2-service-row').each(function(i){
            $(this).attr('data-index', i);
            $(this).find('[data-index]').attr('data-index', i);
        });
        const count = $('#v2_services .v2-service-row').length;
        // Bulk paneli sadece 2+ satirda goster
        $('#v2_bulk_panel').toggle(count >= 2);
        // Tek satir kalirsa sil butonu disable
        $('#v2_services .v2-remove-row').prop('disabled', count <= 1);
    }

    $modal.on('click', '.v2-add-row', function(){
        const idx = $('#v2_services .v2-service-row').length;
        $('#v2_services').append(newRowHtml(idx));
        reindexRows();
    });

    $modal.on('click', '.v2-remove-row', function(){
        if($('#v2_services .v2-service-row').length <= 1) return;
        $(this).closest('.v2-service-row').remove();
        reindexRows();
    });

    // ---- Not ve tekrarlayan toggle ----
    $modal.on('click', '.v2-toggle', function(){
        const $btn = $(this);
        const $target = $($btn.data('target'));
        $btn.toggleClass('open');
        $target.slideToggle(160);
    });

    $modal.on('change', '#v2_tekrarlayan', function(){
        $('#v2_tekrarlayan_body').slideToggle(160, !!this.checked);
        if(this.checked) $('#v2_tekrarlayan_body').show(); else $('#v2_tekrarlayan_body').hide();
    });

    // ---- Randevu / Saat Kapama mode toggle ----
    $modal.on('click', '.v2-mode-toggle', function(){
        const $btn = $(this);
        const current = $btn.data('mode');
        if(current === 'randevu'){
            $btn.data('mode','kapama').addClass('active').attr('title','Randevuya geç');
            $btn.find('i').removeClass('fa-lock').addClass('fa-calendar');
            $('.v2-mode-randevu').hide();
            $('.v2-mode-kapama').show();
            $('.v2-submit-randevu').hide();
            $('.v2-submit-kapama').show();
        } else {
            $btn.data('mode','randevu').removeClass('active').attr('title','Saat Kapamaya geç');
            $btn.find('i').removeClass('fa-calendar').addClass('fa-lock');
            $('.v2-mode-kapama').hide();
            $('.v2-mode-randevu').show();
            $('.v2-submit-kapama').hide();
            $('.v2-submit-randevu').show();
        }
    });

    // ---- Modal acildiginda reset ----
    $modal.on('show.bs.modal', function(){
        reindexRows();
    });

    // ---- Submit (demo): backende baglanmadi, sadece uyari ver ----
    $('#randevuekleform_v2, #saatkapamaform_v2').on('submit', function(e){
        e.preventDefault();
        alert('V2 önizleme modalı — submit henüz backende bağlanmadı.\nFormu eski modal ile karşılaştırmak için kullanın.');
    });
})();
</script>
