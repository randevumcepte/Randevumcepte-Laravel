@php
    // V2 — Modern Yeni Randevu modali. v1 (modal-view-event-add) bozulmadan paralel calisir.
    // Submit stratejisi: v2'deki degerler v1 form alanlarina kopyalanir ve v1'in
    // mevcut submit handler'i ($('#yenirandevuekleform').trigger('submit')) tetiklenir.
    // Bu sayede tum validation/paket/cakisma/save pipeline'i tekrar yazilmadan calisir.
    $__turu = $isletme->randevu_takvim_turu ?? 0;
    $__odaVar = !in_array($__turu, [1, 2]);
    $__cihazVar = !in_array($__turu, [1, 3]);
    $__musteriEtiket = in_array(($isletme->salon_turu_id ?? 0), [15, 28, 29]) ? 'Danışan' : 'Müşteri';
@endphp

<div id="modal-view-event-add-v2" class="modal modal-top fade" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog v2-dialog">
        <div class="modal-content v2-modal">

            {{-- ============ HEADER ============ --}}
            <div class="v2-header">
                <div class="v2-header-left">
                    <div class="v2-header-icon"><i class="fa fa-calendar-plus-o"></i></div>
                    <div>
                        <h5 class="v2-title">Yeni Randevu</h5>
                        <span class="v2-subtitle" id="v2_subtitle_text">Müşteri ve hizmet seç</span>
                    </div>
                </div>
                <div class="v2-header-right">
                    <button type="button" class="v2-icon-btn" id="v2_mode_toggle" title="Saat Kapamaya geç">
                        <i class="fa fa-lock"></i>
                    </button>
                    <button type="button" class="v2-icon-btn" id="v2_open_v1" title="Gelişmiş moda geç (paket, yardımcı personel, cihaz)">
                        <i class="fa fa-cog"></i>
                    </button>
                    <button type="button" class="v2-icon-btn v2-close" data-dismiss="modal" title="Kapat">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- ============ BODY ============ --}}
            <div class="v2-body">

                {{-- ----- RANDEVU FORM ----- --}}
                <form id="randevuekleform_v2" class="v2-form v2-mode-randevu">
                    {!! csrf_field() !!}

                    {{-- Musteri --}}
                    <div class="v2-field">
                        <label class="v2-label">
                            <i class="fa fa-user-circle-o"></i> {{ $__musteriEtiket }}
                        </label>
                        <div class="v2-input-group">
                            <select id="v2_musteri" class="form-control v2-input v2-musteri-select" style="width:100%;">
                                <option></option>
                            </select>
                            <button class="v2-input-addon" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal" title="Yeni {{ $__musteriEtiket }} ekle">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <div class="v2-musteri-info" id="v2_musteri_info" style="display:none;"></div>
                    </div>

                    {{-- Tarih + Saat --}}
                    <div class="v2-row-2">
                        <div class="v2-field">
                            <label class="v2-label"><i class="fa fa-calendar"></i> Tarih</label>
                            <input type="text" id="v2_tarih" class="form-control v2-input" autocomplete="off" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="v2-field">
                            <label class="v2-label"><i class="fa fa-clock-o"></i> Saat</label>
                            <select id="v2_saat" class="form-control v2-input">
                                @for ($j = strtotime('07:00'); $j < strtotime('23:15'); $j += 15 * 60)
                                    <option value="{{ date('H:i', $j) }}:00">{{ date('H:i', $j) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Hizmetler --}}
                    <div class="v2-section">
                        <div class="v2-section-header">
                            <span class="v2-section-title"><i class="fa fa-scissors"></i> Hizmetler</span>
                            <div class="v2-section-actions">
                                <button type="button" class="v2-chip-btn" id="v2_paket_aç" disabled title="Müşteri seçilince aktif olur">
                                    <i class="fa fa-gift"></i> Paketler
                                </button>
                                <button type="button" class="v2-chip-btn v2-chip-primary" id="v2_add_row">
                                    <i class="fa fa-plus"></i> Hizmet Ekle
                                </button>
                            </div>
                        </div>

                        {{-- Bulk paneli — sadece 2+ satirda gosterilir --}}
                        <div class="v2-bulk" id="v2_bulk_panel" style="display:none;">
                            <div class="v2-bulk-title"><i class="fa fa-magic"></i> Tüm satırlara uygula</div>
                            <div class="v2-bulk-grid">
                                <select id="v2_bulk_personel" class="form-control v2-input v2-sm"><option value="">Personel...</option></select>
                                @if ($__cihazVar)
                                <select id="v2_bulk_cihaz" class="form-control v2-input v2-sm"><option value="">Cihaz...</option></select>
                                @endif
                                @if ($__odaVar)
                                <select id="v2_bulk_oda" class="form-control v2-input v2-sm"><option value="">Oda...</option></select>
                                @endif
                            </div>
                        </div>

                        <div class="v2-services" id="v2_services">
                            {{-- ilk satir JS ile eklenir --}}
                        </div>

                        {{-- Toplam ozet --}}
                        <div class="v2-summary" id="v2_summary" style="display:none;">
                            <div class="v2-summary-item">
                                <i class="fa fa-hourglass-half"></i>
                                <span class="v2-summary-label">Toplam Süre</span>
                                <span class="v2-summary-value" id="v2_total_sure">0 dk</span>
                            </div>
                            <div class="v2-summary-item">
                                <i class="fa fa-try"></i>
                                <span class="v2-summary-label">Toplam Tutar</span>
                                <span class="v2-summary-value" id="v2_total_fiyat">0 ₺</span>
                            </div>
                        </div>
                    </div>

                    {{-- Not (collapsible) --}}
                    <div class="v2-collapse-block">
                        <button type="button" class="v2-collapse-toggle" data-target="#v2_not_body">
                            <span><i class="fa fa-sticky-note-o"></i> Not ekle</span>
                            <i class="fa fa-chevron-down v2-chev"></i>
                        </button>
                        <div id="v2_not_body" class="v2-collapse-body" style="display:none;">
                            <textarea id="v2_not" class="form-control v2-input v2-textarea" rows="2" placeholder="Randevu ile ilgili not..."></textarea>
                        </div>
                    </div>

                    {{-- Tekrarlayan --}}
                    <div class="v2-switch-block">
                        <label class="v2-switch-row" for="v2_tekrarlayan">
                            <span><i class="fa fa-refresh"></i> Tekrarlayan randevu</span>
                            <input type="checkbox" id="v2_tekrarlayan" class="v2-switch">
                        </label>
                        <div id="v2_tekrarlayan_body" class="v2-collapse-body" style="display:none;">
                            <div class="v2-row-2">
                                <div class="v2-field">
                                    <label class="v2-label-sm">Sıklık</label>
                                    <select id="v2_tekrar_sikligi" class="form-control v2-input v2-sm">
                                        <option value="+1 day">Her gün</option>
                                        <option value="+1 week" selected>Haftada bir</option>
                                        <option value="+2 weeks">2 haftada bir</option>
                                        <option value="+1 month">Her ay</option>
                                    </select>
                                </div>
                                <div class="v2-field">
                                    <label class="v2-label-sm">Tekrar sayısı</label>
                                    <input type="number" id="v2_tekrar_sayisi" min="1" max="52" class="form-control v2-input v2-sm" value="4">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- ----- SAAT KAPAMA FORM ----- --}}
                @if (\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
                <form id="saatkapamaform_v2" class="v2-form v2-mode-kapama" method="POST" style="display:none;">
                    {!! csrf_field() !!}
                    <input type="hidden" name="sube" value="{{ $isletme->id }}">

                    <div class="v2-field">
                        <label class="v2-label"><i class="fa fa-user"></i> Personel</label>
                        <select name="personel" id="v2_kapama_personel" class="form-control v2-input"><option></option></select>
                    </div>

                    <div class="v2-row-3">
                        <div class="v2-field">
                            <label class="v2-label-sm"><i class="fa fa-calendar"></i> Tarih</label>
                            <input type="text" name="tarih" id="v2_kapama_tarih" class="form-control v2-input v2-sm" autocomplete="off" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="v2-field">
                            <label class="v2-label-sm">Başlangıç</label>
                            <input type="time" name="saat" class="form-control v2-input v2-sm">
                        </div>
                        <div class="v2-field">
                            <label class="v2-label-sm">Bitiş</label>
                            <input type="time" name="saat_bitis" class="form-control v2-input v2-sm">
                        </div>
                    </div>

                    <label class="v2-switch-row" for="v2_kapama_tumgun">
                        <span><i class="fa fa-sun-o"></i> Tüm gün</span>
                        <input type="checkbox" id="v2_kapama_tumgun" name="tum_gun" class="v2-switch">
                    </label>

                    <div class="v2-field">
                        <label class="v2-label-sm">Not</label>
                        <textarea name="personel_notu" class="form-control v2-input v2-textarea" rows="2"></textarea>
                    </div>
                </form>
                @endif
            </div>

            {{-- ============ FOOTER ============ --}}
            <div class="v2-footer">
                <div class="v2-footer-left" id="v2_footer_hint">
                    <i class="fa fa-info-circle"></i> v2 önizleme — gelişmiş özellikler için <i class="fa fa-cog"></i> butonu
                </div>
                <div class="v2-footer-right">
                    <button type="button" class="v2-btn v2-btn-ghost" data-dismiss="modal">Vazgeç</button>
                    <button type="button" class="v2-btn v2-btn-primary v2-submit-randevu" id="v2_submit_btn">
                        <i class="fa fa-check"></i> Randevu Oluştur
                    </button>
                    <button type="button" class="v2-btn v2-btn-warning v2-submit-kapama" id="v2_submit_kapama_btn" style="display:none;">
                        <i class="fa fa-lock"></i> Saati Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================================
   V2 Modal — Modern, ferah, animasyonlu
   v1 modali ile %0 stil cakismasi (tum selektorler #modal-view-event-add-v2 altinda)
======================================================== */

#modal-view-event-add-v2 { z-index: 100003 !important; }
#modal-view-event-add-v2.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
#modal-view-event-add-v2 .v2-dialog {
    max-width: 680px;
    width: 96%;
    margin: 0 auto !important;
    align-self: center;
}
#modal-view-event-add-v2 .v2-modal {
    border-radius: 16px;
    border: none;
    box-shadow: 0 25px 70px rgba(31, 23, 75, 0.32);
    overflow: hidden;
    background: #fbfbfd;
    animation: v2-pop 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes v2-pop {
    from { opacity: 0; transform: scale(0.94); }
    to   { opacity: 1; transform: scale(1); }
}

/* ===== HEADER ===== */
#modal-view-event-add-v2 .v2-header {
    background: linear-gradient(135deg, #4338ca 0%, #7c3aed 55%, #c026d3 100%);
    color: #fff;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}
#modal-view-event-add-v2 .v2-header::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(255,255,255,0.18), transparent 60%);
    pointer-events: none;
}
#modal-view-event-add-v2 .v2-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    z-index: 1;
}
#modal-view-event-add-v2 .v2-header-icon {
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.18);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(8px);
}
#modal-view-event-add-v2 .v2-title {
    margin: 0; color: #fff;
    font-size: 1.02rem; font-weight: 600;
    letter-spacing: -0.01em;
}
#modal-view-event-add-v2 .v2-subtitle {
    color: rgba(255,255,255,0.78);
    font-size: 0.72rem;
    margin-top: 2px;
    display: block;
}
#modal-view-event-add-v2 .v2-header-right {
    display: flex; gap: 6px;
    position: relative; z-index: 1;
}
#modal-view-event-add-v2 .v2-icon-btn {
    width: 32px; height: 32px;
    background: rgba(255,255,255,0.14);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.15s;
    display: flex; align-items: center; justify-content: center;
}
#modal-view-event-add-v2 .v2-icon-btn:hover {
    background: rgba(255,255,255,0.28);
    transform: translateY(-1px);
}
#modal-view-event-add-v2 .v2-icon-btn.active {
    background: #fff;
    color: #7c3aed;
}

/* ===== BODY ===== */
#modal-view-event-add-v2 .v2-body {
    max-height: 72vh;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #d1c7e6 transparent;
}
#modal-view-event-add-v2 .v2-body::-webkit-scrollbar { width: 6px; }
#modal-view-event-add-v2 .v2-body::-webkit-scrollbar-thumb { background: #d1c7e6; border-radius: 3px; }
#modal-view-event-add-v2 .v2-form { padding: 16px 18px 4px; }

/* ===== FIELDS ===== */
#modal-view-event-add-v2 .v2-field { margin-bottom: 12px; }
#modal-view-event-add-v2 .v2-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}
#modal-view-event-add-v2 .v2-row-3 {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}
#modal-view-event-add-v2 .v2-row-2 > .v2-field,
#modal-view-event-add-v2 .v2-row-3 > .v2-field { margin-bottom: 0; }

#modal-view-event-add-v2 .v2-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.74rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
#modal-view-event-add-v2 .v2-label i { color: #7c3aed; opacity: 0.8; }
#modal-view-event-add-v2 .v2-label-sm {
    display: block;
    font-size: 0.7rem;
    color: #6b7280;
    margin-bottom: 4px;
    font-weight: 500;
}
#modal-view-event-add-v2 .v2-input {
    height: 38px;
    font-size: 0.88rem;
    padding: 4px 10px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    background: #fff;
    transition: all 0.15s;
    width: 100%;
}
#modal-view-event-add-v2 .v2-input.v2-sm { height: 34px; font-size: 0.84rem; }
#modal-view-event-add-v2 textarea.v2-input,
#modal-view-event-add-v2 .v2-textarea { height: auto; padding: 8px 10px; resize: vertical; }
#modal-view-event-add-v2 .v2-input:hover { border-color: #d1d5db; }
#modal-view-event-add-v2 .v2-input:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
    outline: none;
}

#modal-view-event-add-v2 .v2-input-group {
    display: flex; gap: 6px;
}
#modal-view-event-add-v2 .v2-input-group > .v2-input,
#modal-view-event-add-v2 .v2-input-group > .select2,
#modal-view-event-add-v2 .v2-input-group > .v2-musteri-select { flex: 1; }
#modal-view-event-add-v2 .v2-input-addon {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    color: #6d28d9;
    border: 1.5px solid #ddd6fe;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.15s;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-input-addon:hover {
    background: linear-gradient(135deg, #ddd6fe, #c4b5fd);
    transform: translateY(-1px);
}

#modal-view-event-add-v2 .v2-musteri-info {
    margin-top: 6px;
    padding: 8px 10px;
    background: linear-gradient(135deg, #f5f3ff, #faf5ff);
    border-radius: 8px;
    font-size: 0.78rem;
    color: #5b21b6;
    border-left: 3px solid #7c3aed;
}

/* ===== SECTION ===== */
#modal-view-event-add-v2 .v2-section {
    background: #fff;
    border: 1px solid #ececf0;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
#modal-view-event-add-v2 .v2-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
#modal-view-event-add-v2 .v2-section-title {
    font-weight: 600;
    font-size: 0.86rem;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 6px;
}
#modal-view-event-add-v2 .v2-section-title i { color: #7c3aed; }
#modal-view-event-add-v2 .v2-section-actions { display: flex; gap: 6px; }

#modal-view-event-add-v2 .v2-chip-btn {
    background: #f9fafb;
    color: #4b5563;
    border: 1px solid #e5e7eb;
    padding: 5px 10px;
    border-radius: 7px;
    font-size: 0.76rem;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
#modal-view-event-add-v2 .v2-chip-btn:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #d1d5db;
    transform: translateY(-1px);
}
#modal-view-event-add-v2 .v2-chip-btn:disabled { opacity: 0.45; cursor: not-allowed; }
#modal-view-event-add-v2 .v2-chip-primary {
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    color: #6d28d9;
    border-color: #c4b5fd;
}
#modal-view-event-add-v2 .v2-chip-primary:hover {
    background: linear-gradient(135deg, #ddd6fe, #c4b5fd);
}

/* ===== BULK ===== */
#modal-view-event-add-v2 .v2-bulk {
    background: linear-gradient(135deg, #faf5ff, #fdf4ff);
    border: 1px dashed #d8b4fe;
    border-radius: 9px;
    padding: 10px 12px;
    margin-bottom: 10px;
    animation: v2-slide-down 0.2s ease;
}
@keyframes v2-slide-down {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
#modal-view-event-add-v2 .v2-bulk-title {
    font-size: 0.74rem;
    color: #7c2d92;
    font-weight: 600;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}
#modal-view-event-add-v2 .v2-bulk-grid {
    display: flex;
    gap: 6px;
}
#modal-view-event-add-v2 .v2-bulk-grid > select { flex: 1; min-width: 0; }

/* ===== SERVICE ROW ===== */
#modal-view-event-add-v2 .v2-services { display: flex; flex-direction: column; gap: 8px; }
#modal-view-event-add-v2 .v2-service-row {
    background: linear-gradient(180deg, #fcfcfd, #f9fafb);
    border: 1px solid #ececf0;
    border-radius: 10px;
    padding: 10px;
    transition: all 0.15s;
    animation: v2-slide-down 0.18s ease;
}
#modal-view-event-add-v2 .v2-service-row:hover {
    border-color: #d1c7e6;
    box-shadow: 0 2px 6px rgba(124, 58, 237, 0.06);
}
#modal-view-event-add-v2 .v2-row-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
#modal-view-event-add-v2 .v2-row-num {
    font-size: 0.72rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
#modal-view-event-add-v2 .v2-row-meta {
    font-size: 0.74rem;
    color: #6d28d9;
    font-weight: 600;
}
#modal-view-event-add-v2 .v2-paket-rozet {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
    padding: 2px 8px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    border-radius: 999px;
    font-size: 0.66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
#modal-view-event-add-v2 .v2-paket-rozet i { font-size: 0.66rem; }

/* ===== PAKET KART (akordiyon) ===== */
#modal-view-event-add-v2 .v2-paket-card {
    background: linear-gradient(180deg, #fdfbff, #faf5ff);
    border: 1px solid #ddd6fe;
    box-shadow: 0 1px 3px rgba(124, 58, 237, 0.06);
    padding: 0;
    overflow: hidden;
}
#modal-view-event-add-v2 .v2-paket-card:hover {
    border-color: #c4b5fd;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.12);
}
#modal-view-event-add-v2 .v2-paket-head { padding: 0; }
#modal-view-event-add-v2 .v2-paket-toggle {
    width: 100%;
    background: transparent;
    border: none;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    text-align: left;
    transition: background 0.12s;
}
#modal-view-event-add-v2 .v2-paket-toggle:hover { background: rgba(124,58,237,0.04); }
#modal-view-event-add-v2 .v2-paket-toggle > .fa-gift {
    color: #7c3aed;
    font-size: 1.1rem;
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-paket-title {
    font-weight: 700;
    font-size: 0.92rem;
    color: #4c1d95;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#modal-view-event-add-v2 .v2-paket-count {
    font-size: 0.7rem;
    color: #6d28d9;
    background: #ede9fe;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 600;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-paket-meta {
    font-size: 0.78rem;
    color: #6b21a8;
    font-weight: 600;
    flex-shrink: 0;
    margin-left: 4px;
}
#modal-view-event-add-v2 .v2-paket-chev {
    color: #9ca3af;
    font-size: 0.72rem;
    transition: transform 0.2s;
    margin-left: 4px;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-paket-card.expanded .v2-paket-chev { transform: rotate(180deg); }

#modal-view-event-add-v2 .v2-paket-controls {
    padding: 0 14px 12px;
    border-top: 1px dashed rgba(124, 58, 237, 0.15);
    padding-top: 10px;
}

#modal-view-event-add-v2 .v2-paket-detay {
    padding: 0 14px 14px;
    border-top: 1px solid #ede9fe;
    background: rgba(255,255,255,0.6);
}
#modal-view-event-add-v2 .v2-paket-hizmet-list {
    list-style: none;
    margin: 10px 0 0;
    padding: 0;
}
#modal-view-event-add-v2 .v2-paket-hizmet-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    margin-bottom: 4px;
    background: #fff;
    border: 1px solid #f3f0fc;
    border-radius: 7px;
    font-size: 0.82rem;
}
#modal-view-event-add-v2 .v2-paket-hizmet-list li::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-paket-hizmet-ad {
    color: #1f2937;
    font-weight: 500;
    flex: 1;
    min-width: 0;
}
#modal-view-event-add-v2 .v2-seans-badge {
    background: #ede9fe;
    color: #6d28d9;
    padding: 2px 7px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-paket-hizmet-sure {
    color: #9ca3af;
    font-size: 0.74rem;
    font-weight: 500;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-service-grid {
    display: grid;
    gap: 6px;
}
#modal-view-event-add-v2 .v2-service-grid .v2-hizmet-wrap { margin-bottom: 4px; }
/* Flexbox: degisken sutun sayisi (Personel + Cihaz? + Oda? + Trash) — her select esit pay alir */
#modal-view-event-add-v2 .v2-service-grid-row {
    display: flex;
    gap: 6px;
    align-items: end;
}
#modal-view-event-add-v2 .v2-service-grid-row > .v2-field { flex: 1; min-width: 0; }
#modal-view-event-add-v2 .v2-service-grid-row > .v2-icon-trash { flex: 0 0 auto; }
#modal-view-event-add-v2 .v2-icon-trash {
    width: 38px; height: 38px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.15s;
}
#modal-view-event-add-v2 .v2-icon-trash:hover:not(:disabled) {
    background: #fee2e2;
    transform: translateY(-1px);
}
#modal-view-event-add-v2 .v2-icon-trash:disabled { opacity: 0.3; cursor: not-allowed; }

/* ===== SUMMARY ===== */
#modal-view-event-add-v2 .v2-summary {
    margin-top: 10px;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
    border-radius: 9px;
    padding: 10px 12px;
    display: flex;
    justify-content: space-around;
    border: 1px solid #ddd6fe;
}
#modal-view-event-add-v2 .v2-summary-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
#modal-view-event-add-v2 .v2-summary-item i {
    color: #7c3aed;
    font-size: 0.92rem;
}
#modal-view-event-add-v2 .v2-summary-label {
    font-size: 0.72rem;
    color: #6b7280;
    font-weight: 500;
}
#modal-view-event-add-v2 .v2-summary-value {
    font-size: 0.92rem;
    color: #5b21b6;
    font-weight: 700;
}

/* ===== COLLAPSE & SWITCH ===== */
#modal-view-event-add-v2 .v2-collapse-block,
#modal-view-event-add-v2 .v2-switch-block {
    background: #fff;
    border: 1px solid #ececf0;
    border-radius: 12px;
    padding: 10px 14px;
    margin-bottom: 10px;
}
#modal-view-event-add-v2 .v2-collapse-toggle {
    width: 100%;
    text-align: left;
    background: transparent;
    border: none;
    padding: 4px 0;
    color: #4b5563;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
}
#modal-view-event-add-v2 .v2-collapse-toggle:hover { color: #7c3aed; }
#modal-view-event-add-v2 .v2-collapse-toggle i:first-child { color: #7c3aed; margin-right: 4px; }
#modal-view-event-add-v2 .v2-chev {
    font-size: 0.7rem;
    transition: transform 0.2s;
}
#modal-view-event-add-v2 .v2-collapse-toggle.open .v2-chev { transform: rotate(180deg); }
#modal-view-event-add-v2 .v2-collapse-body { padding-top: 8px; }

#modal-view-event-add-v2 .v2-switch-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0;
    font-size: 0.88rem;
    color: #1f2937;
    font-weight: 500;
    cursor: pointer;
}
#modal-view-event-add-v2 .v2-switch-row > span i { color: #7c3aed; margin-right: 5px; }
#modal-view-event-add-v2 .v2-switch {
    appearance: none;
    -webkit-appearance: none;
    width: 40px; height: 22px;
    background: #d1d5db;
    border-radius: 11px;
    position: relative;
    cursor: pointer;
    transition: background 0.25s;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-switch::after {
    content: '';
    position: absolute;
    top: 2px; left: 2px;
    width: 18px; height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: left 0.25s, transform 0.15s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
#modal-view-event-add-v2 .v2-switch:checked {
    background: linear-gradient(135deg, #7c3aed, #c026d3);
}
#modal-view-event-add-v2 .v2-switch:checked::after { left: 20px; }
#modal-view-event-add-v2 .v2-switch:hover::after { transform: scale(1.05); }

/* ===== FOOTER ===== */
#modal-view-event-add-v2 .v2-footer {
    background: #fff;
    border-top: 1px solid #ececf0;
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}
#modal-view-event-add-v2 .v2-footer-left {
    font-size: 0.72rem;
    color: #9ca3af;
}
#modal-view-event-add-v2 .v2-footer-left i { color: #7c3aed; }
#modal-view-event-add-v2 .v2-footer-right { display: flex; gap: 8px; }

#modal-view-event-add-v2 .v2-btn {
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
#modal-view-event-add-v2 .v2-btn-ghost {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #e5e7eb;
}
#modal-view-event-add-v2 .v2-btn-ghost:hover { background: #e5e7eb; }
#modal-view-event-add-v2 .v2-btn-primary {
    background: linear-gradient(135deg, #4338ca, #7c3aed);
    color: #fff;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.32);
}
#modal-view-event-add-v2 .v2-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
    filter: brightness(1.05);
}
#modal-view-event-add-v2 .v2-btn-primary:active { transform: translateY(0); }
#modal-view-event-add-v2 .v2-btn-warning {
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    color: #fff;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.32);
}
#modal-view-event-add-v2 .v2-btn-warning:hover { transform: translateY(-1px); filter: brightness(1.05); }

/* ===== Select2 / TomSelect override (sadece v2 modali icinde) ===== */
#modal-view-event-add-v2 .select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    padding: 4px 4px;
}
#modal-view-event-add-v2 .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    color: #1f2937;
    font-size: 0.88rem;
}
#modal-view-event-add-v2 .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
#modal-view-event-add-v2 .ts-wrapper.form-control {
    height: auto !important;
    min-height: 38px;
    border-radius: 9px;
    border: 1.5px solid #e5e7eb;
    padding: 2px 4px;
}
#modal-view-event-add-v2 .ts-wrapper.form-control.focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
}
</style>

<script>
(function(){
    var $modal = $('#modal-view-event-add-v2');
    if(!$modal.length) return;

    /* ============================================================
       V2 → V1 PROXY YAKLASIMI
       Submit'te v2 degerlerini v1 form alanlarina kopyalayip
       v1'in submit handler'ini tetikliyoruz. Bu sayede tum
       backend bagintilari (validation, paket, conflict, save)
       tek bir noktadan calisiyor.
       ============================================================ */

    var v2RowIndex = 0;
    var v2Init = false;

    // -------- DOM SHORTCUTS --------
    var $musteri = $('#v2_musteri');
    var $tarih = $('#v2_tarih');
    var $saat = $('#v2_saat');
    var $services = $('#v2_services');
    var $summary = $('#v2_summary');
    var $totalSure = $('#v2_total_sure');
    var $totalFiyat = $('#v2_total_fiyat');

    // -------- HELPERS --------
    function getRandevuModalData(){
        return window.randevuModalData || { personeller: [], cihazlar: [], odalar: [] };
    }
    function fmtTL(n){
        var v = parseFloat(n) || 0;
        return v.toLocaleString('tr-TR', {maximumFractionDigits:0}) + ' ₺';
    }

    // -------- ROW MANAGEMENT --------
    function newServiceRowHTML(idx){
        var cihazHTML = @json($__cihazVar) ?
            '<div class="v2-field"><select class="form-control v2-input v2-sm v2-cihaz" data-index="'+idx+'"><option value="">Cihaz...</option></select></div>' : '';
        var odaHTML = @json($__odaVar) ?
            '<div class="v2-field"><select class="form-control v2-input v2-sm v2-oda" data-index="'+idx+'"><option value="">Oda...</option></select></div>' : '';
        return ''+
        '<div class="v2-service-row" data-index="'+idx+'">'+
            '<div class="v2-row-head">'+
                '<span class="v2-row-num">Hizmet #'+(idx+1)+'</span>'+
                '<span class="v2-row-meta" data-meta-for="'+idx+'"></span>'+
            '</div>'+
            '<div class="v2-service-grid">'+
                '<div class="v2-hizmet-wrap">'+
                    '<select multiple class="form-control v2-input v2-hizmet" id="v2_hizmet_'+idx+'" data-index="'+idx+'">'+
                        '<option></option>'+
                    '</select>'+
                '</div>'+
                '<div class="v2-service-grid-row">'+
                    '<div class="v2-field"><select class="form-control v2-input v2-sm v2-personel" data-index="'+idx+'"><option value="">Personel...</option></select></div>'+
                    cihazHTML+
                    odaHTML+
                    '<button type="button" class="v2-icon-trash v2-remove-row" title="Bu hizmeti sil"><i class="fa fa-trash"></i></button>'+
                '</div>'+
            '</div>'+
        '</div>';
    }

    function reindexRows(){
        $services.find('.v2-service-row').each(function(i){
            $(this).attr('data-index', i);
            $(this).find('.v2-row-num').text('Hizmet #'+(i+1));
            $(this).find('[data-index]').attr('data-index', i);
            $(this).find('.v2-row-meta').attr('data-meta-for', i);
        });
        var count = $services.find('.v2-service-row').length;
        $('#v2_bulk_panel').toggle(count >= 2);
        $services.find('.v2-remove-row').prop('disabled', count <= 1);
    }

    function initRowSelects($row){
        var idx = parseInt($row.attr('data-index'), 10);
        var data = getRandevuModalData();

        // Personel
        var $p = $row.find('.v2-personel');
        $p.empty().append('<option value="">Personel...</option>');
        data.personeller.forEach(function(p){
            $p.append(new Option(p.ad, p.id));
        });

        // Cihaz
        var $c = $row.find('.v2-cihaz');
        if($c.length){
            $c.empty().append('<option value="">Cihaz...</option>');
            data.cihazlar.forEach(function(c){
                $c.append(new Option(c.ad, c.id));
            });
        }

        // Oda
        var $o = $row.find('.v2-oda');
        if($o.length){
            $o.empty().append('<option value="">Oda...</option>');
            data.odalar.forEach(function(o){
                $o.append(new Option(o.ad, o.id));
            });
        }

        // Hizmet — Tom Select
        var $hizmet = $row.find('.v2-hizmet');
        var el = $hizmet[0];
        if(el && typeof TomSelect !== 'undefined'){
            if(el.tomselect){ try{el.tomselect.destroy();}catch(e){} }
            var hizmetler = (window.hizmetDataCache && Object.keys(window.hizmetDataCache).length)
                ? Object.keys(window.hizmetDataCache).map(function(id){
                    var d = window.hizmetDataCache[id];
                    return { value: id, text: d.text, sure: d.sure, fiyat: d.fiyat, kategori: d.kategori };
                  }) : [];
            new TomSelect(el, {
                plugins: ['remove_button'],
                placeholder: 'Hizmet seçin...',
                options: hizmetler,
                searchField: ['text','kategori'],
                hideSelected: true,
                closeAfterSelect: false,
                render: {
                    option: function(data, escape){
                        var meta = '';
                        if(data.sure || data.fiyat){
                            meta = '<div style="font-size:0.72rem;color:#6b7280;margin-top:2px;">'
                                + (data.sure ? data.sure+' dk' : '')
                                + (data.sure && data.fiyat ? ' · ' : '')
                                + (data.fiyat ? fmtTL(data.fiyat) : '')
                                + '</div>';
                        }
                        return '<div style="padding:6px 4px;"><div style="font-weight:500;color:#1f2937;">'+escape(data.text)+'</div>'+meta+'</div>';
                    },
                    no_results: function(){
                        return '<div style="padding:8px;color:#9ca3af;font-size:0.85rem;">Hizmet bulunamadı</div>';
                    }
                },
                onChange: function(){
                    updateRowMeta(idx);
                    updateSummary();
                }
            });
        }
    }

    function addRow(){
        var idx = $services.find('.v2-service-row').length;
        var $row = $(newServiceRowHTML(idx));
        $services.append($row);
        initRowSelects($row);
        reindexRows();
        updateSummary();
    }

    // -------- SURE/FIYAT META --------
    function updateRowMeta(idx){
        var $row = $services.find('.v2-service-row[data-index="'+idx+'"]');
        if(!$row.length) return;
        var $hizmet = $row.find('.v2-hizmet');
        var ids = $hizmet.val() || [];
        var sure = 0, fiyat = 0;
        ids.forEach(function(id){
            var d = window.hizmetDataCache ? window.hizmetDataCache[id] : null;
            if(d){
                sure += parseFloat(d.sure) || 0;
                fiyat += parseFloat(d.fiyat) || 0;
            }
        });
        var meta = '';
        if(sure > 0) meta += sure + ' dk';
        if(fiyat > 0) meta += (meta ? ' · ' : '') + fmtTL(fiyat);
        $row.find('.v2-row-meta').text(meta);
    }

    function updateSummary(){
        var totalSure = 0, totalFiyat = 0;
        $services.find('.v2-service-row').each(function(){
            var $row = $(this);
            // Paket card data('hizmetIds') saklar; normal satir .v2-hizmet'i kullanir
            var ids = $row.data('hizmetIds');
            if(!ids || !ids.length){ ids = $row.find('.v2-hizmet').val() || []; }
            if(!Array.isArray(ids)) ids = ids ? [ids] : [];
            ids.forEach(function(id){
                var d = window.hizmetDataCache ? window.hizmetDataCache[id] : null;
                if(d){
                    totalSure += parseFloat(d.sure) || 0;
                    totalFiyat += parseFloat(d.fiyat) || 0;
                }
            });
        });
        if(totalSure > 0 || totalFiyat > 0){
            $totalSure.text(totalSure + ' dk');
            $totalFiyat.text(fmtTL(totalFiyat));
            $summary.show();
        } else {
            $summary.hide();
        }
    }

    // -------- MUSTERI AJAX --------
    function initMusteriSelect(){
        if($musteri.data('select2')) return;
        $musteri.select2({
            dropdownParent: $modal,
            placeholder: '{{ $__musteriEtiket }} ara (ad, soyad veya telefon)...',
            allowClear: true,
            ajax: {
                url: '/isletmeyonetim/musteri-arama-bolumu-verileri',
                dataType: 'json',
                delay: 250,
                data: function(params){ return { query: params.term, sube: {{ $isletme->id }} }; },
                processResults: function(data){
                    var arr = Array.isArray(data) ? data : (data.musteriler || []);
                    return {
                        results: arr.map(function(m){
                            return { id: m.id, text: m.ad_soyad || (m.name + ' (' + (m.cep_telefon||'') + ')') };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });

        $musteri.on('select2:select', function(e){
            var data = e.params.data;
            $('#v2_musteri_info').text('✓ '+data.text).show();
            $('#v2_paket_aç').prop('disabled', false);
            $('#v2_subtitle_text').text('Hizmet ve personel seç');

            // V1'in musteri select'ine de yansit (paketKontrolü v1 form fieldlarini okuyor)
            var userId = data.id;
            var $v1Musteri = $('#randevuekle_musteri_id');
            if($v1Musteri.find('option[value="'+userId+'"]').length === 0){
                $v1Musteri.append(new Option(data.text, userId, true, true));
            }
            $v1Musteri.val(userId).trigger('change');

            // Paket otomatik popup (v1'in paketKontrolü fonksiyonu)
            window._v2PaketAkisi = true; // dispatcher routing icin flag
            if(typeof paketKontrolü === 'function'){
                paketKontrolü(userId, false);
            }
        });
        $musteri.on('select2:clear', function(){
            $('#v2_musteri_info').hide().text('');
            $('#v2_paket_aç').prop('disabled', true);
            $('#v2_subtitle_text').text('Müşteri ve hizmet seç');
            window._v2PaketAkisi = false;
        });

        // Manuel "Paketler" butonu — secili musteri icin paket modali ac
        $('#v2_paket_aç').off('click.v2paket').on('click.v2paket', function(){
            var userId = $musteri.val();
            if(!userId) return;
            window._v2PaketAkisi = true;
            if(typeof paketKontrolü === 'function'){
                paketKontrolü(userId, true); // onayVar=true: modal her zaman acilir
            }
        });
    }

    // -------- PAKET → V2 SATIRLARI SYNC --------
    // Paket modali her hizmeti ayri v1 satiri olarak ekler. UX icin v2'de
    // KATEGORIYE GORE gruplayip her grubu TEK satir gosteriyoruz: ayni
    // "Lazer Epilasyon" alti dort hizmet -> 1 satir; "Lazer" + "Cilt Bakimi"
    // -> 2 satir. Her v2 satiri data('v1RowIndices') ile temsil ettigi
    // v1 satirlarinin indekslerini saklar; submit/silme bunlari kullanir.
    function syncFromV1ToV2(){
        var $v1Rows = $('#modal-view-event-add .hizmet-satiri');
        if(!$v1Rows.length) return;

        // V1 satirlarindan hizmet secili olanlari topla + v1 index'leri sakla
        var v1Data = [];
        $v1Rows.each(function(v1i){
            var $r = $(this);
            var $h = $r.find('.hizmet-select');
            var hEl = $h[0];
            var ids = [];
            if(hEl && hEl.tomselect){
                ids = hEl.tomselect.getValue();
                if(!Array.isArray(ids)) ids = ids ? [ids] : [];
            } else {
                ids = $h.val() || [];
                if(!Array.isArray(ids)) ids = ids ? [ids] : [];
            }
            ids = ids.filter(function(x){ return x; });
            if(!ids.length) return;
            v1Data.push({
                v1Index: v1i,
                hizmetIds: ids,
                personelId: $r.find('.personel-select, .personel_secimi').not('.hizmet-select').val() || '',
                cihazId: $r.find('.cihaz-select, .cihaz_secimi').val() || '',
                odaId: $r.find('.oda-select, .oda_secimi').val() || ''
            });
        });

        if(!v1Data.length) return;

        // Kategoriye gore grupla — ayni kategori = ayni v2 satiri
        // (kategori bos ise hizmet adinin ilk kelimesi fallback olarak kullanilir;
        // boylece "kategori" alani veri tabaninda doldurulmasa bile makul gruplama olur)
        function getKategori(hizmetId){
            var d = window.hizmetDataCache ? window.hizmetDataCache[hizmetId] : null;
            if(d && d.kategori) return d.kategori;
            // Fallback: hizmet adinin ilk anlamli kelimesi (parantez/sayilari at)
            if(d && d.text){
                var clean = d.text.replace(/\(.*?\)/g,'').trim();
                return clean.split(/\s+/).slice(0,2).join(' ');
            }
            return '_default_';
        }

        var groups = {};      // kategori -> { hizmetIds, v1Indices, personelId, cihazId, odaId }
        var groupOrder = [];  // kategorilerin gorunum sirasi
        v1Data.forEach(function(row){
            row.hizmetIds.forEach(function(hid){
                var kat = getKategori(hid);
                if(!groups[kat]){
                    groups[kat] = {
                        hizmetIds: [],
                        v1Indices: [],
                        personelId: row.personelId,
                        cihazId: row.cihazId,
                        odaId: row.odaId
                    };
                    groupOrder.push(kat);
                }
                if(groups[kat].hizmetIds.indexOf(String(hid)) === -1){
                    groups[kat].hizmetIds.push(String(hid));
                }
                if(groups[kat].v1Indices.indexOf(row.v1Index) === -1){
                    groups[kat].v1Indices.push(row.v1Index);
                }
                // Personel/cihaz/oda: ilk dolu deger kazansin
                if(!groups[kat].personelId && row.personelId) groups[kat].personelId = row.personelId;
                if(!groups[kat].cihazId && row.cihazId)       groups[kat].cihazId = row.cihazId;
                if(!groups[kat].odaId && row.odaId)           groups[kat].odaId = row.odaId;
            });
        });

        // V2 mevcut satirlarini temizle
        $services.find('.v2-service-row').each(function(){
            var el = $(this).find('.v2-hizmet')[0];
            if(el && el.tomselect){ try{el.tomselect.destroy();}catch(e){} }
        });
        $services.empty();

        // Her kategori icin TEK v2 satiri olustur
        groupOrder.forEach(function(kat, i){
            var g = groups[kat];
            addRow();
            var $row = $services.find('.v2-service-row').eq(i);
            // Paket'ten geldigini ve hangi v1 satirlarini temsil ettigini sakla
            $row.data('isPaket', true);
            $row.data('v1RowIndices', g.v1Indices.slice());

            // Hizmet listesini Tom Select'e bas (cogul)
            var hizmetEl = $row.find('.v2-hizmet')[0];
            if(hizmetEl && hizmetEl.tomselect){
                hizmetEl.tomselect.setValue(g.hizmetIds, true);
            }
            // Personel + Cihaz + Oda
            if(g.personelId) $row.find('.v2-personel').val(g.personelId);
            if(g.cihazId)    $row.find('.v2-cihaz').val(g.cihazId);
            if(g.odaId)      $row.find('.v2-oda').val(g.odaId);

            // Paket rozeti (gorsel ipucu)
            if(!$row.find('.v2-paket-rozet').length){
                var rozet = '<span class="v2-paket-rozet" title="Bu satir paketten geldi"><i class="fa fa-gift"></i> Paket</span>';
                $row.find('.v2-row-num').after(rozet);
            }
            updateRowMeta(i);
        });
        updateSummary();
        reindexRows();
    }
    window._v2SyncFromV1 = syncFromV1ToV2;

    // -------- PAKET DISPATCHER OVERRIDE --------
    function installPaketDispatcher(){
        if(window._v2DispatcherInstalled) return;
        var original = window.addServicesToForm;
        window.addServicesToForm = function(hizmetData, result, showMsg){
            var v2Visible = $modal.hasClass('show') || $modal.is(':visible');
            var v2Akis = window._v2PaketAkisi && v2Visible;

            // Once: v1'deki dolu satirlarin indekslerini sakla (sonradan eklenenleri bulmak icin)
            var beforeFilledV1 = [];
            $('#modal-view-event-add .hizmet-satiri').each(function(i){
                var hEl = $(this).find('.hizmet-select')[0];
                var vals = (hEl && hEl.tomselect) ? hEl.tomselect.getValue() : ($(this).find('.hizmet-select').val() || []);
                if(!Array.isArray(vals)) vals = vals ? [vals] : [];
                if(vals.filter(function(x){return x;}).length) beforeFilledV1.push(i);
            });

            var ret = original ? original.apply(this, arguments) : null;

            if(v2Akis && Array.isArray(hizmetData) && hizmetData.length){
                // V1'e eklenmesini bekle, sonra hizmetData -> v2 render
                setTimeout(function(){
                    var newV1Indices = []; // { v1Index, hizmetIds:[id,...] }
                    $('#modal-view-event-add .hizmet-satiri').each(function(i){
                        if(beforeFilledV1.indexOf(i) !== -1) return; // onceden doluydu, paket icin degil
                        var hEl = $(this).find('.hizmet-select')[0];
                        var vals = (hEl && hEl.tomselect) ? hEl.tomselect.getValue() : ($(this).find('.hizmet-select').val() || []);
                        if(!Array.isArray(vals)) vals = vals ? [vals] : [];
                        vals = vals.filter(function(x){return x;}).map(String);
                        if(!vals.length) return;
                        newV1Indices.push({ v1Index: i, hizmetIds: vals });
                    });
                    renderPaketRowsInV2(hizmetData, newV1Indices);
                }, 700);
            }
            return ret;
        };
        window._v2DispatcherInstalled = true;
    }

    // -------- PAKET RENDER (akordiyon kartlar) --------
    // hizmetData: convertAllPackagesToServiceData cikti yapisindaki dizi
    //   { id, text, seans, tur:'paket'|'hizmet', sure, paket_adi, paket_id, ... }
    // newV1Indices: paket secimi sonrasi v1'e eklenen satirlarin indeksleri
    function renderPaketRowsInV2(hizmetData, newV1Indices){
        // 1. Paket grubu vs standalone hizmet ayrimi
        var paketler = {};     // paket_id -> { adi, paket_id, hizmetler:[], v1Indices:[] }
        var paketOrder = [];   // gorunum sirasi
        var standalone = [];   // tur='hizmet'

        hizmetData.forEach(function(h){
            if(h.tur === 'paket' && h.paket_id){
                var key = String(h.paket_id);
                if(!paketler[key]){
                    paketler[key] = {
                        adi: h.paket_adi || ('Paket #'+h.paket_id),
                        paket_id: h.paket_id,
                        hizmetler: [],
                        v1Indices: []
                    };
                    paketOrder.push(key);
                }
                paketler[key].hizmetler.push(h);
            } else {
                standalone.push(h);
            }
        });

        // 2. Her paket grubuna v1 satir indekslerini bagla (hizmet ID'siyle eslesen)
        paketOrder.forEach(function(key){
            paketler[key].hizmetler.forEach(function(h){
                var hidStr = String(h.id);
                newV1Indices.forEach(function(item){
                    if(item.hizmetIds.indexOf(hidStr) !== -1){
                        if(paketler[key].v1Indices.indexOf(item.v1Index) === -1){
                            paketler[key].v1Indices.push(item.v1Index);
                        }
                    }
                });
            });
        });

        // 3. V2'yi temizle (paket-card ve service-row hepsi)
        $services.find('.v2-service-row, .v2-paket-card').each(function(){
            var el = $(this).find('.v2-hizmet')[0];
            if(el && el.tomselect){ try{el.tomselect.destroy();}catch(e){} }
        });
        $services.empty();

        // 4. Her paket icin akordiyon kart olustur
        paketOrder.forEach(function(key, i){
            var grp = paketler[key];
            $services.append(buildPaketCardHTML(grp, i));
        });

        // 5. Standalone hizmetler icin kategori bazli gruplama (eski mantik)
        if(standalone.length){
            renderStandaloneAsRows(standalone, newV1Indices, paketOrder.length);
        }

        // 6. Dropdownlari doldur, secimleri uygula + v1RowIndices data'sini ata
        $services.find('.v2-paket-card').each(function(idx){
            var $card = $(this);
            var key = $card.attr('data-paket-id');
            var grp = paketler[String(key)];
            if(!grp) return;
            // V1 indekslerini sakla (submit & silme bunlari kullanir)
            $card.data('isPaket', true);
            $card.data('v1RowIndices', grp.v1Indices.slice());
            // Summary icin paketin tum hizmet ID'lerini sakla (paket card'da .v2-hizmet yok)
            $card.data('hizmetIds', grp.hizmetler.map(function(h){ return String(h.id); }));
            // Personel/cihaz/oda dropdownlarini doldur
            populateDropdownsInCard($card);
            // V1'in ilk grup satirindan personel/oda al
            if(grp.v1Indices.length){
                var $v1First = $('#modal-view-event-add .hizmet-satiri').eq(grp.v1Indices[0]);
                var p = $v1First.find('.personel-select, .personel_secimi').not('.hizmet-select').val();
                var c = $v1First.find('.cihaz-select, .cihaz_secimi').val();
                var o = $v1First.find('.oda-select, .oda_secimi').val();
                if(p) $card.find('.v2-personel').val(p);
                if(c) $card.find('.v2-cihaz').val(c);
                if(o) $card.find('.v2-oda').val(o);
            }
        });

        reindexRows();
        updateSummary();
    }

    function buildPaketCardHTML(grp, idx){
        var totalSure = 0, totalFiyat = 0;
        var hizmetListHTML = grp.hizmetler.map(function(h){
            var d = window.hizmetDataCache ? window.hizmetDataCache[h.id] : null;
            var sure = (d && d.sure) || h.sure || 0;
            var fiyat = (d && d.fiyat) || 0;
            totalSure += parseFloat(sure) || 0;
            totalFiyat += parseFloat(fiyat) || 0;
            var seansTxt = h.seans ? ' <span class="v2-seans-badge">'+h.seans+' seans</span>' : '';
            return '<li><span class="v2-paket-hizmet-ad">'+escapeHtml(h.text)+'</span>'+seansTxt+'<span class="v2-paket-hizmet-sure">'+sure+' dk</span></li>';
        }).join('');

        var metaParts = [];
        if(totalSure > 0) metaParts.push(totalSure+' dk');
        if(totalFiyat > 0) metaParts.push(fmtTL(totalFiyat));
        var metaTxt = metaParts.join(' · ');

        var cihazSelHTML = @json($__cihazVar) ?
            '<div class="v2-field"><select class="form-control v2-input v2-sm v2-cihaz"><option value="">Cihaz...</option></select></div>' : '';
        var odaSelHTML = @json($__odaVar) ?
            '<div class="v2-field"><select class="form-control v2-input v2-sm v2-oda"><option value="">Oda...</option></select></div>' : '';

        return ''+
        '<div class="v2-service-row v2-paket-card" data-paket-id="'+escapeAttr(grp.paket_id)+'" data-index="'+idx+'">'+
            '<div class="v2-paket-head">'+
                '<button type="button" class="v2-paket-toggle" title="Hizmetleri göster/gizle">'+
                    '<i class="fa fa-gift"></i>'+
                    '<span class="v2-paket-title">'+escapeHtml(grp.adi)+'</span>'+
                    '<span class="v2-paket-count">'+grp.hizmetler.length+' hizmet</span>'+
                    '<span class="v2-paket-meta">'+metaTxt+'</span>'+
                    '<i class="fa fa-chevron-down v2-paket-chev"></i>'+
                '</button>'+
            '</div>'+
            '<div class="v2-paket-controls">'+
                '<div class="v2-service-grid-row">'+
                    '<div class="v2-field"><select class="form-control v2-input v2-sm v2-personel"><option value="">Personel...</option></select></div>'+
                    cihazSelHTML+
                    odaSelHTML+
                    '<button type="button" class="v2-icon-trash v2-remove-row" title="Bu paketi kaldır"><i class="fa fa-trash"></i></button>'+
                '</div>'+
            '</div>'+
            '<div class="v2-paket-detay" style="display:none;">'+
                '<ul class="v2-paket-hizmet-list">'+hizmetListHTML+'</ul>'+
            '</div>'+
        '</div>';
    }

    function populateDropdownsInCard($card){
        var data = getRandevuModalData();
        var $p = $card.find('.v2-personel');
        $p.empty().append('<option value="">Personel...</option>');
        data.personeller.forEach(function(p){ $p.append(new Option(p.ad, p.id)); });
        var $c = $card.find('.v2-cihaz');
        if($c.length){
            $c.empty().append('<option value="">Cihaz...</option>');
            data.cihazlar.forEach(function(c){ $c.append(new Option(c.ad, c.id)); });
        }
        var $o = $card.find('.v2-oda');
        if($o.length){
            $o.empty().append('<option value="">Oda...</option>');
            data.odalar.forEach(function(o){ $o.append(new Option(o.ad, o.id)); });
        }
    }

    function renderStandaloneAsRows(standalone, newV1Indices, startIdx){
        // Paket disindaki bireysel hizmetler — kategori bazli gruplama
        function getKategori(hid){
            var d = window.hizmetDataCache ? window.hizmetDataCache[hid] : null;
            if(d && d.kategori) return d.kategori;
            if(d && d.text){
                var clean = d.text.replace(/\(.*?\)/g,'').trim();
                return clean.split(/\s+/).slice(0,2).join(' ');
            }
            return '_default_';
        }
        var groups = {}, order = [];
        standalone.forEach(function(h){
            var kat = getKategori(h.id);
            if(!groups[kat]){ groups[kat] = { hizmetIds:[], v1Indices:[] }; order.push(kat); }
            if(groups[kat].hizmetIds.indexOf(String(h.id)) === -1) groups[kat].hizmetIds.push(String(h.id));
        });
        // V1 indeks eslestir
        order.forEach(function(kat){
            groups[kat].hizmetIds.forEach(function(hid){
                newV1Indices.forEach(function(item){
                    if(item.hizmetIds.indexOf(hid) !== -1 && groups[kat].v1Indices.indexOf(item.v1Index) === -1){
                        groups[kat].v1Indices.push(item.v1Index);
                    }
                });
            });
        });
        order.forEach(function(kat, i){
            addRow();
            var $row = $services.find('.v2-service-row').last();
            $row.data('isPaket', true);
            $row.data('v1RowIndices', groups[kat].v1Indices.slice());
            var hizmetEl = $row.find('.v2-hizmet')[0];
            if(hizmetEl && hizmetEl.tomselect){
                hizmetEl.tomselect.setValue(groups[kat].hizmetIds, true);
            }
            // V1'in ilk satirindan personel/oda al
            if(groups[kat].v1Indices.length){
                var $v1First = $('#modal-view-event-add .hizmet-satiri').eq(groups[kat].v1Indices[0]);
                var p = $v1First.find('.personel-select, .personel_secimi').not('.hizmet-select').val();
                var c = $v1First.find('.cihaz-select, .cihaz_secimi').val();
                var o = $v1First.find('.oda-select, .oda_secimi').val();
                if(p) $row.find('.v2-personel').val(p);
                if(c) $row.find('.v2-cihaz').val(c);
                if(o) $row.find('.v2-oda').val(o);
            }
            updateRowMeta(startIdx + i);
        });
    }

    function escapeHtml(s){ return $('<div>').text(s == null ? '' : s).html(); }
    function escapeAttr(s){ return String(s == null ? '' : s).replace(/"/g,'&quot;'); }

    function initBulkSelects(){
        var data = getRandevuModalData();
        var $bp = $('#v2_bulk_personel').empty().append('<option value="">Personel...</option>');
        data.personeller.forEach(function(p){ $bp.append(new Option(p.ad, p.id)); });
        var $bc = $('#v2_bulk_cihaz');
        if($bc.length){
            $bc.empty().append('<option value="">Cihaz...</option>');
            data.cihazlar.forEach(function(c){ $bc.append(new Option(c.ad, c.id)); });
        }
        var $bo = $('#v2_bulk_oda');
        if($bo.length){
            $bo.empty().append('<option value="">Oda...</option>');
            data.odalar.forEach(function(o){ $bo.append(new Option(o.ad, o.id)); });
        }
        $bp.off('change.v2bulk').on('change.v2bulk', function(){
            var v = $(this).val();
            $services.find('.v2-personel').val(v);
        });
        $bc.off('change.v2bulk').on('change.v2bulk', function(){
            var v = $(this).val();
            $services.find('.v2-cihaz').val(v);
        });
        $bo.off('change.v2bulk').on('change.v2bulk', function(){
            var v = $(this).val();
            $services.find('.v2-oda').val(v);
        });
    }

    // -------- HIZMET VERISI YUKLE (v1'in cache'i bos ise) --------
    function ensureHizmetVerisi(cb){
        // Eger v1 modali zaten cache'i doldurmussa kullan
        if(window.hizmetDataCache && Object.keys(window.hizmetDataCache).length > 0){ cb && cb(); return; }
        if(window.randevuHizmetVerisi && window.randevuHizmetVerisi.tum && window.randevuHizmetVerisi.tum.length){
            window.hizmetDataCache = window.hizmetDataCache || {};
            window.randevuHizmetVerisi.tum.forEach(function(h){
                window.hizmetDataCache[h.id] = { id:h.id, text:h.ad, sure:h.sure||0, fiyat:h.fiyat||0, kategori:h.kategori||'' };
            });
            cb && cb();
            return;
        }
        // Fetch
        $.ajax({
            url: '/isletmeyonetim/randevu-modal-hizmet-verisi',
            type: 'GET',
            dataType: 'json',
            data: { sube: {{ $isletme->id }} },
            success: function(resp){
                window.hizmetDataCache = window.hizmetDataCache || {};
                window.randevuHizmetVerisi = {
                    tum: (resp && resp.tum_hizmetler) ? resp.tum_hizmetler : [],
                    personel: (resp && resp.personel_hizmet_map) ? resp.personel_hizmet_map : {},
                    cihaz: (resp && resp.cihaz_hizmet_map) ? resp.cihaz_hizmet_map : {}
                };
                window.randevuHizmetVerisi.tum.forEach(function(h){
                    window.hizmetDataCache[h.id] = { id:h.id, text:h.ad, sure:h.sure||0, fiyat:h.fiyat||0, kategori:h.kategori||'' };
                });
                cb && cb();
            },
            error: function(){ cb && cb(); }
        });
    }

    // -------- INIT --------
    function initV2(){
        if(v2Init){
            // Cache senkron olsun diye yine de hizmet listesini Tom Select'lere bas
            $services.find('.v2-service-row').each(function(){
                var el = $(this).find('.v2-hizmet')[0];
                if(el && el.tomselect && (!el.tomselect.options || Object.keys(el.tomselect.options).length === 0)){
                    if(window.hizmetDataCache){
                        var opts = Object.keys(window.hizmetDataCache).map(function(id){
                            var d = window.hizmetDataCache[id];
                            return { value: id, text: d.text, sure: d.sure, fiyat: d.fiyat, kategori: d.kategori };
                        });
                        el.tomselect.addOptions(opts);
                    }
                }
            });
            return;
        }
        initMusteriSelect();
        ensureHizmetVerisi(function(){
            initBulkSelects();
            if($services.children().length === 0) addRow();
            installPaketDispatcher();
            v2Init = true;
        });
    }

    // -------- FORM RESET --------
    function resetV2Form(){
        // Musteri
        if($musteri.data('select2')){
            $musteri.val(null).trigger('change');
        } else {
            $musteri.val('');
        }
        $('#v2_musteri_info').hide().text('');
        $('#v2_paket_aç').prop('disabled', true);
        $('#v2_subtitle_text').text('Müşteri ve hizmet seç');

        // Tarih + saat default
        $tarih.val('{{ date("Y-m-d") }}');
        // Saat default ilk option
        $saat.prop('selectedIndex', 0);

        // Hizmet satirlari sifirla
        $services.find('.v2-service-row').each(function(){
            var el = $(this).find('.v2-hizmet')[0];
            if(el && el.tomselect){ try{el.tomselect.destroy();}catch(e){} }
        });
        $services.empty();
        addRow();
        $('#v2_bulk_panel').hide();

        // Bulk panel inputs
        $('#v2_bulk_personel').val('');
        $('#v2_bulk_cihaz').val('');
        $('#v2_bulk_oda').val('');

        // Notlar
        $('#v2_not').val('');
        $('#v2_not_body').hide();
        $('.v2-collapse-toggle').removeClass('open');

        // Tekrarlayan
        $('#v2_tekrarlayan').prop('checked', false);
        $('#v2_tekrarlayan_body').hide();
        $('#v2_tekrar_sikligi').val('+1 week');
        $('#v2_tekrar_sayisi').val(4);

        // Summary
        $summary.hide();
        $totalSure.text('0 dk');
        $totalFiyat.text('0 ₺');

        // Paket flag
        window._v2PaketAkisi = false;
    }
    window._v2ResetForm = resetV2Form;

    // -------- EVENTS --------
    $modal.on('shown.bs.modal', function(){
        initV2();
        // Date picker init (eski v1 modaldaki helper varsa kullan)
        if($.fn.datepicker && !$tarih.data('datepicker-init')){
            try {
                $tarih.datepicker({ format: 'yyyy-mm-dd', autoclose: true, language: 'tr', todayHighlight: true });
                $tarih.data('datepicker-init', true);
            } catch(e){}
        }
    });

    $modal.on('hidden.bs.modal', function(){
        // Form'u tamamen sifirla: musteri, hizmet, personel, oda, not, tekrar, summary
        resetV2Form();
    });

    $modal.on('click', '#v2_add_row', addRow);

    // Paket kart akordiyon toggle
    $modal.on('click', '.v2-paket-toggle', function(e){
        e.preventDefault();
        var $card = $(this).closest('.v2-paket-card');
        $card.toggleClass('expanded');
        $card.find('.v2-paket-detay').slideToggle(180);
    });

    $modal.on('click', '.v2-remove-row', function(e){
        e.stopPropagation(); // paket-toggle'i tetiklemesin
        var $row = $(this).closest('.v2-service-row');
        var rowCount = $services.find('.v2-service-row').length;
        if(rowCount <= 1) return;
        var hizmetEl = $row.find('.v2-hizmet')[0];
        if(hizmetEl && hizmetEl.tomselect){ try{hizmetEl.tomselect.destroy();}catch(e){} }

        // Paket satiriysa: v1'deki temsil eden satirlari da temizle (hizmet'i bosalt)
        // — fiziksel olarak silmiyoruz cunku tum v1 row indeksleri kayar; sadece
        //   hizmet secimini bosaltinca v1 submit handler'i bos satirlari atlar
        var v1Indices = $row.data('v1RowIndices');
        if(v1Indices && v1Indices.length){
            v1Indices.forEach(function(v1i){
                var $v1Row = $('#modal-view-event-add .hizmet-satiri').eq(v1i);
                if(!$v1Row.length) return;
                var el = $v1Row.find('.hizmet-select')[0];
                if(el && el.tomselect){ try{el.tomselect.clear(true);}catch(e){} }
                $v1Row.find('.personel-select, .personel_secimi').not('.hizmet-select').val(null).trigger('change');
                $v1Row.find('.cihaz-select, .cihaz_secimi').val(null).trigger('change');
                $v1Row.find('.oda-select, .oda_secimi').val(null).trigger('change');
            });
            // Diger paket satirlarinin v1Indices'larini guncellemek gerekmiyor:
            // silmiyoruz, sadece hizmet'i bosaltiyoruz -> indeksler kayar olmaz.
        }

        $row.remove();
        reindexRows();
        updateSummary();
    });

    // Collapse toggle
    $modal.on('click', '.v2-collapse-toggle', function(){
        var $btn = $(this);
        var $target = $($btn.data('target'));
        $btn.toggleClass('open');
        $target.slideToggle(180);
    });

    // Tekrarlayan switch
    $modal.on('change', '#v2_tekrarlayan', function(){
        $('#v2_tekrarlayan_body').slideToggle(180, this.checked);
        if(this.checked) $('#v2_tekrarlayan_body').show();
        else $('#v2_tekrarlayan_body').hide();
    });

    // Mode toggle (Randevu <-> Saat Kapama)
    var modeIsKapama = false;
    $modal.on('click', '#v2_mode_toggle', function(){
        modeIsKapama = !modeIsKapama;
        var $btn = $(this);
        if(modeIsKapama){
            $('.v2-mode-randevu').hide();
            $('.v2-mode-kapama').show();
            $('.v2-submit-randevu').hide();
            $('.v2-submit-kapama').show();
            $btn.addClass('active').attr('title','Randevuya geç').find('i').removeClass('fa-lock').addClass('fa-calendar');
            $('.v2-title').text('Saat Kapama');
            $('#v2_subtitle_text').text('Personel için zaman bloku oluştur');
        } else {
            $('.v2-mode-kapama').hide();
            $('.v2-mode-randevu').show();
            $('.v2-submit-kapama').hide();
            $('.v2-submit-randevu').show();
            $btn.removeClass('active').attr('title','Saat Kapamaya geç').find('i').removeClass('fa-calendar').addClass('fa-lock');
            $('.v2-title').text('Yeni Randevu');
            $('#v2_subtitle_text').text('Müşteri ve hizmet seç');
        }
    });

    // Gelismis moda gec (v1 modali ac)
    $modal.on('click', '#v2_open_v1', function(){
        window._v2BypassIntercept = true; // intercept bunu yakalayinca v1'i normal acsin
        $modal.modal('hide');
        setTimeout(function(){
            $('#modal-view-event-add').modal('show');
            window._v2BypassIntercept = false;
        }, 250);
    });

    // ============================================================
    // V1 -> V2 REDIRECT INTERCEPT
    // window.useV2Modal=true ise (v2 takvim sayfasi), takvim slot
    // tiklamasi v1 modalini acmaya calisir; biz yakalayip kapatip
    // alanlari v2'ye kopyalayip v2'yi aciyoruz.
    // ============================================================
    $(document).on('show.bs.modal', '#modal-view-event-add', function(e){
        if(!window.useV2Modal) return;
        if(window._v2BypassIntercept) return;
        // Bootstrap 4: show.bs.modal'da preventDefault() v1'in acilmasini engeller
        if(e.preventDefault) e.preventDefault();
        var $v1 = $(this);
        // Yedek: yine de acilirsa hide
        setTimeout(function(){
            if($v1.hasClass('show') || $v1.is(':visible')){ $v1.modal('hide'); }
        }, 50);

        // Slot bilgilerini v1 alanlarindan oku
        var tarih = $('#randevutarihiyeni').val();
        var saat  = $('#randevu_saat').val();
        var v1PersonelOpt = $('select[name="randevupersonelleriyeni[]"]:first option:selected');
        var v1OdaOpt      = $('select[name="randevuodalariyeni[]"]:first option:selected');
        var v1CihazOpt    = $('select[name="randevucihazlariyeni[]"]:first option:selected');

        // V2'yi ac, shown event'inde alanlari doldur
        var fillV2 = function(){
            if(tarih) $tarih.val(tarih);
            if(saat) $saat.val(saat);

            // Ilk satira personel/oda/cihaz yaz
            var $firstRow = $services.find('.v2-service-row').first();
            if(!$firstRow.length){
                addRow();
                $firstRow = $services.find('.v2-service-row').first();
            }
            if(v1PersonelOpt.val()){
                var $vp = $firstRow.find('.v2-personel');
                if($vp.find('option[value="'+v1PersonelOpt.val()+'"]').length === 0){
                    $vp.append(new Option(v1PersonelOpt.text(), v1PersonelOpt.val()));
                }
                $vp.val(v1PersonelOpt.val());
            }
            if(v1CihazOpt.val()){
                var $vc = $firstRow.find('.v2-cihaz');
                if($vc.length){
                    if($vc.find('option[value="'+v1CihazOpt.val()+'"]').length === 0){
                        $vc.append(new Option(v1CihazOpt.text(), v1CihazOpt.val()));
                    }
                    $vc.val(v1CihazOpt.val());
                }
            }
            if(v1OdaOpt.val()){
                var $vo = $firstRow.find('.v2-oda');
                if($vo.length){
                    if($vo.find('option[value="'+v1OdaOpt.val()+'"]').length === 0){
                        $vo.append(new Option(v1OdaOpt.text(), v1OdaOpt.val()));
                    }
                    $vo.val(v1OdaOpt.val());
                }
            }
            $modal.off('shown.bs.modal.slotfill').on('shown.bs.modal.slotfill', function(){
                // Bu handler bir kez calissin; yukaridaki kopyalamalar zaten run etti.
                $modal.off('shown.bs.modal.slotfill');
            });
        };

        // V2 init zaten yapildiysa direkt doldur; degilse shown'da
        if(v2Init){
            $modal.modal('show');
            setTimeout(fillV2, 100);
        } else {
            $modal.one('shown.bs.modal', function(){ setTimeout(fillV2, 100); });
            $modal.modal('show');
        }
    });

    // -------- SUBMIT (proxy to v1) --------
    $modal.on('click', '#v2_submit_btn', function(){
        // 1. V1 modalini gorunmez sekilde acmak yerine, mevcut formuna direkt yaz:
        //    V1 modali sayfada include edilmis durumda, form alanlari DOM'da var.
        //    V1 submit handler'i jQuery .trigger('submit') ile calistirilacak.

        // Musteri
        var musteriId = $musteri.val();
        if(musteriId){
            // V1'in select2'sini doldur ve trigger
            var $v1Musteri = $('#randevuekle_musteri_id');
            // Eger v1'de option yoksa, ekle
            if($v1Musteri.find('option[value="'+musteriId+'"]').length === 0){
                var text = $musteri.find('option:selected').text();
                $v1Musteri.append(new Option(text, musteriId, true, true));
            }
            $v1Musteri.val(musteriId).trigger('change');
        }

        // Tarih ve saat
        $('#randevutarihiyeni').val($tarih.val()).trigger('change');
        $('#randevu_saat').val($saat.val()).trigger('change');

        // Not
        $('#yenirandevuekleform textarea[name="personel_notu"]').val($('#v2_not').val());

        // Tekrarlayan
        var tek = $('#v2_tekrarlayan').is(':checked');
        $('#tekrarlayan').prop('checked', tek).trigger('change');
        if(tek){
            $('#yenirandevuekleform select[name="tekrar_sikligi"]').val($('#v2_tekrar_sikligi').val());
            $('#yenirandevuekleform input[name="tekrar_sayisi"]').val($('#v2_tekrar_sayisi').val());
        }

        // ===== HIZMET SATIRLARI: paket vs manuel ayrimi =====
        // Paket satirlari: v2 satiri data('v1RowIndices') ile birden fazla v1
        //   satirini temsil eder. Sadece personel/cihaz/oda kopyalanir; hizmet
        //   secimi (paket tracking metadata'sini koruyabilmek icin) korunur.
        // Manuel satirlar: v2 -> yeni v1 satiri (her hizmet ayri satir).
        var $v1RowsLive = function(){ return $('#modal-view-event-add .hizmet-satiri'); };

        // Phase 1: Paket satirlari - personel/cihaz/oda v1'deki gruplanmis satirlara uygula
        $services.find('.v2-service-row').each(function(){
            var $v2Row = $(this);
            var v1Indices = $v2Row.data('v1RowIndices');
            if(!v1Indices || !v1Indices.length) return; // manuel satir, phase 2'de
            var personelId = $v2Row.find('.v2-personel').val();
            var cihazId    = $v2Row.find('.v2-cihaz').val();
            var odaId      = $v2Row.find('.v2-oda').val();
            v1Indices.forEach(function(v1i){
                var $v1Row = $v1RowsLive().eq(v1i);
                if(!$v1Row.length) return;
                if(personelId) $v1Row.find('.personel-select, .personel_secimi').not('.hizmet-select').val(personelId).trigger('change');
                if(cihazId)    $v1Row.find('.cihaz-select, .cihaz_secimi').val(cihazId).trigger('change');
                if(odaId)      $v1Row.find('.oda-select, .oda_secimi').val(odaId).trigger('change');
            });
        });

        // Phase 2: Manuel satirlar - her biri icin v1 satiri olustur ve doldur
        var manualV2Rows = $services.find('.v2-service-row').filter(function(){
            var idx = $(this).data('v1RowIndices');
            return !idx || !idx.length;
        });

        if(manualV2Rows.length){
            var existingV1Count = $v1RowsLive().length;
            // V1'in basinda olustugunda zaten 1 bos satir vardir; manuel sayi kadar lazim
            // Once mevcut bos satirlari say (hizmet secili olmayan v1 satirlari kullanilabilir)
            var bosV1Indices = [];
            $v1RowsLive().each(function(i){
                var hEl = $(this).find('.hizmet-select')[0];
                var vals = (hEl && hEl.tomselect) ? hEl.tomselect.getValue() : ($(this).find('.hizmet-select').val() || []);
                if(!Array.isArray(vals)) vals = vals ? [vals] : [];
                vals = vals.filter(function(x){ return x; });
                if(!vals.length) bosV1Indices.push(i);
            });
            // Eksik kalan satir sayisini "Yeni Hizmet Ekle" ile uret
            var needed = manualV2Rows.length - bosV1Indices.length;
            while(needed > 0){
                $('#bir_hizmet_daha_ekle').trigger('click');
                bosV1Indices.push($v1RowsLive().length - 1);
                needed--;
                if(bosV1Indices.length > 30) break;
            }

            manualV2Rows.each(function(mi){
                var $v2Row = $(this);
                var hizmetIds = $v2Row.find('.v2-hizmet').val() || [];
                var personelId = $v2Row.find('.v2-personel').val();
                var cihazId    = $v2Row.find('.v2-cihaz').val();
                var odaId      = $v2Row.find('.v2-oda').val();

                var v1i = bosV1Indices[mi];
                var $v1Row = $v1RowsLive().eq(v1i);
                if(!$v1Row.length) return;

                var v1HizmetEl = $v1Row.find('.hizmet-select')[0];
                if(v1HizmetEl && v1HizmetEl.tomselect){
                    v1HizmetEl.tomselect.setValue(hizmetIds, true);
                } else if(v1HizmetEl){
                    $(v1HizmetEl).val(hizmetIds).trigger('change');
                }
                if(personelId) $v1Row.find('.personel-select, .personel_secimi').not('.hizmet-select').val(personelId).trigger('change');
                if(cihazId)    $v1Row.find('.cihaz-select, .cihaz_secimi').val(cihazId).trigger('change');
                if(odaId)      $v1Row.find('.oda-select, .oda_secimi').val(odaId).trigger('change');
            });
        }

        // V1 submit handler'ini tetikle
        setTimeout(function(){
            // V1 modalini gor; submit oncesi gosterip arka planda calistirilabilir
            // V1 submit handler #yenirandevuekleform submit event'ine bagli
            $('#yenirandevuekleform').trigger('submit');

            // Submit basariliysa v2 modal kapanir; backend'in donusu otomatik gosterilir
            // (v1'in cakisma uyarisi vs. zaten gorunecek)
        }, 80);
    });

    // Saat Kapama submit — basitce v1 saat kapama formunu kullan
    $modal.on('click', '#v2_submit_kapama_btn', function(){
        // V1'deki saat kapama formunu doldur ve submit et
        $('#saat_kapama select[name="personel"]').val($('#v2_kapama_personel').val()).trigger('change');
        $('#saat_kapama input[name="tarih"]').val($('#v2_kapama_tarih').val());
        $('#saat_kapama input[name="saat"]').val($('#saatkapamaform_v2 input[name="saat"]').val());
        $('#saat_kapama input[name="saat_bitis"]').val($('#saatkapamaform_v2 input[name="saat_bitis"]').val());
        $('#saat_kapama input[name="tum_gun"]').prop('checked', $('#v2_kapama_tumgun').is(':checked'));
        $('#saat_kapama textarea[name="personel_notu"]').val($('#saatkapamaform_v2 textarea[name="personel_notu"]').val());
        $('#saat_kapama').trigger('submit');
    });

})();
</script>
