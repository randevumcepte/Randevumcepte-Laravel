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

<div id="modal-view-event-add-v2" class="modal modal-top fade" data-backdrop="true" data-keyboard="true">
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
                    <button type="button" class="v2-icon-btn v2-close" data-dismiss="modal" title="Kapat">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            @if (\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
            {{-- ============ MODE TABS (Randevu / Saat Kapama) ============ --}}
            <div class="v2-tabs">
                <button type="button" class="v2-tab active" data-mode="randevu">
                    <i class="fa fa-calendar-plus-o"></i> Randevu
                </button>
                <button type="button" class="v2-tab" data-mode="kapama">
                    <i class="fa fa-ban"></i> Saat Kapama
                </button>
            </div>
            @endif

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
                <div class="v2-footer-left" id="v2_footer_hint"></div>
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

/* ===== MODE TABS (Randevu / Saat Kapama) ===== */
#modal-view-event-add-v2 .v2-tabs {
    display: flex;
    background: #fff;
    border-bottom: 1px solid #ececf0;
    padding: 0;
    gap: 0;
}
#modal-view-event-add-v2 .v2-tab {
    flex: 1;
    background: transparent;
    border: none;
    padding: 12px 16px;
    font-size: 0.86rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    position: relative;
    border-bottom: 2px solid transparent;
}
#modal-view-event-add-v2 .v2-tab i { font-size: 0.95rem; }
#modal-view-event-add-v2 .v2-tab:hover { color: #4b5563; background: rgba(124,58,237,0.04); }
#modal-view-event-add-v2 .v2-tab.active {
    color: #7c3aed;
    background: #faf5ff;
    border-bottom-color: #7c3aed;
}
#modal-view-event-add-v2 .v2-tab.active i { color: #7c3aed; }
#modal-view-event-add-v2 .v2-tab[data-mode="kapama"].active {
    color: #d97706;
    background: #fffbeb;
    border-bottom-color: #f59e0b;
}
#modal-view-event-add-v2 .v2-tab[data-mode="kapama"].active i { color: #d97706; }

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
@keyframes v2-slide-down {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

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
#modal-view-event-add-v2 .v2-paket-head {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: background 0.12s;
}
#modal-view-event-add-v2 .v2-paket-head:hover { background: rgba(124,58,237,0.04); }
#modal-view-event-add-v2 .v2-paket-head-main {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}
#modal-view-event-add-v2 .v2-paket-icon {
    color: #7c3aed;
    font-size: 1.1rem;
    width: 32px; height: 32px;
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

/* Sure + Fiyat input wrappers — prominent, editable */
#modal-view-event-add-v2 .v2-paket-totals {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
#modal-view-event-add-v2 .v2-paket-input-wrap {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1.5px solid #ddd6fe;
    border-radius: 8px;
    padding: 4px 8px 4px 6px;
    gap: 4px;
    transition: all 0.12s;
    box-shadow: 0 1px 2px rgba(124, 58, 237, 0.05);
}
#modal-view-event-add-v2 .v2-paket-input-wrap:hover {
    border-color: #c4b5fd;
    box-shadow: 0 2px 6px rgba(124, 58, 237, 0.12);
}
#modal-view-event-add-v2 .v2-paket-input-wrap:focus-within {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}
#modal-view-event-add-v2 .v2-paket-sure-input,
#modal-view-event-add-v2 .v2-paket-fiyat-input {
    border: none;
    background: transparent;
    font-size: 0.9rem;
    font-weight: 700;
    color: #4c1d95;
    width: 56px;
    padding: 2px;
    text-align: right;
    outline: none;
    appearance: textfield;
    -moz-appearance: textfield;
}
#modal-view-event-add-v2 .v2-paket-fiyat-input { width: 72px; }
#modal-view-event-add-v2 .v2-paket-sure-input::-webkit-inner-spin-button,
#modal-view-event-add-v2 .v2-paket-sure-input::-webkit-outer-spin-button {
    -webkit-appearance: none; margin: 0;
}
#modal-view-event-add-v2 .v2-paket-input-unit {
    font-size: 0.74rem;
    font-weight: 600;
    color: #7c3aed;
    flex-shrink: 0;
}

#modal-view-event-add-v2 .v2-paket-toggle-btn {
    background: transparent;
    border: none;
    color: #9ca3af;
    width: 24px; height: 24px;
    padding: 0;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.12s;
}
#modal-view-event-add-v2 .v2-paket-toggle-btn:hover {
    background: rgba(124,58,237,0.1);
    color: #7c3aed;
}
#modal-view-event-add-v2 .v2-paket-chev {
    font-size: 0.78rem;
    transition: transform 0.2s;
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
                // Hizmet 6 | Süre 3 | Fiyat 3 (Bootstrap row)
                '<div class="row g-2 v2-hizmet-wrap">'+
                    '<div class="col-md-6">'+
                        '<select multiple class="form-control v2-input v2-hizmet" id="v2_hizmet_'+idx+'" data-index="'+idx+'">'+
                            '<option></option>'+
                        '</select>'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<input type="number" min="0" step="5" class="form-control v2-input v2-sm v2-row-sure" data-index="'+idx+'" placeholder="Süre (dk)">'+
                    '</div>'+
                    '<div class="col-md-3">'+
                        '<input type="text" inputmode="decimal" class="form-control v2-input v2-sm v2-row-fiyat hy-fiyat-input" data-index="'+idx+'" placeholder="Fiyat (₺)" autocomplete="off">'+
                    '</div>'+
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
        // Hizmet seçilince satır seviyesindeki Süre + Fiyat input'larına default değerleri yaz
        // (kullanıcı sonradan manuel düzenleyebilir; manuel edit varsa override etmemek için
        // sadece input boşsa veya hizmet hiç seçili değilse yaz)
        var $sureInp = $row.find('.v2-row-sure');
        var $fiyatInp = $row.find('.v2-row-fiyat');
        if(!$row.data('userEditedSure')){
            $sureInp.val(sure > 0 ? sure : '');
        }
        if(!$row.data('userEditedFiyat')){
            var fmt = sure > 0 || fiyat > 0
                ? fiyat.toLocaleString('tr-TR', {minimumFractionDigits:2, maximumFractionDigits:2})
                : '';
            $fiyatInp.val(fiyat > 0 ? fmt : '');
        }
        var meta = '';
        if(sure > 0) meta += sure + ' dk';
        if(fiyat > 0) meta += (meta ? ' · ' : '') + fmtTL(fiyat);
        $row.find('.v2-row-meta').text(meta);
    }

    // Kullanıcı manuel edit yaparsa flag set et (sonraki hizmet selection override etmesin)
    $(document).on('input', '#modal-view-event-add-v2 .v2-row-sure', function(){
        $(this).closest('.v2-service-row').data('userEditedSure', true);
        updateSummary();
    });
    $(document).on('input', '#modal-view-event-add-v2 .v2-row-fiyat', function(){
        $(this).closest('.v2-service-row').data('userEditedFiyat', true);
        updateSummary();
    });

    // -------- PERSONEL/CIHAZ/ODA → HIZMET FILTRESI --------
    // Seçilen personel/cihaz/oda'ya tanımlı hizmetler dropdown'a yüklenir.
    // Eslesme bulunamazsa (hiçbiri tanımlı değil) filtreleme yapılmaz — tüm hizmetler gösterilir.
    // v1'in filtrelenmisHizmetler mantığının v2 karşılığı.
    function v2FilterHizmetler(personelId, cihazId, odaId){
        var verisi = window.randevuHizmetVerisi || {};
        var tum = (verisi.tum || []).slice();
        var hp = (personelId && verisi.personel) ? (verisi.personel[personelId] || null) : null;
        var hc = (cihazId    && verisi.cihaz)    ? (verisi.cihaz[cihazId]       || null) : null;
        // ODA: oncelikle endpoint'ten gelen taze map, yoksa blade randevuModalData fallback
        var ho = null;
        if(odaId){
            if(verisi.oda && verisi.oda[odaId] && verisi.oda[odaId].length){
                ho = verisi.oda[odaId].map(String);
            } else {
                var odaObj = ((window.randevuModalData && window.randevuModalData.odalar) || []).find(function(o){
                    return String(o.id) === String(odaId);
                });
                if(odaObj && Array.isArray(odaObj.hizmet_idleri) && odaObj.hizmet_idleri.length){
                    ho = odaObj.hizmet_idleri.map(String);
                }
            }
        }
        // KESISIM (INTERSECTION) mantigi: her aktif kisit ayri liste doner,
        // sadece hepsinde ORTAK olan hizmet_id'ler kalir. Kullanici bildirimi:
        // 'oda lazer, personel bu odada calisiyor -> sadece hem personelin yaptigi
        // hem odadaki hizmetler gorunsun'. Eski union (concat) yaklasimi personel
        // + oda birlestiriyordu, tum hizmetleri gosteriyordu.
        var kisitlar = [];
        if(hp && hp.length) kisitlar.push(hp.map(String));
        if(hc && hc.length) kisitlar.push(hc.map(String));
        if(ho && ho.length) kisitlar.push(ho.map(String));

        var intersect = null;
        if(kisitlar.length){
            intersect = kisitlar[0].slice();
            for(var i = 1; i < kisitlar.length; i++){
                var next = kisitlar[i];
                intersect = intersect.filter(function(x){ return next.indexOf(x) > -1; });
            }
        }

        console.log('[V2 FILTRE]', {
            personelId: personelId, cihazId: cihazId, odaId: odaId,
            hp_count: hp ? hp.length : 0,
            hc_count: hc ? hc.length : 0,
            ho_count: ho ? ho.length : 0,
            tum_count: tum.length,
            kesisim: intersect ? intersect.length : null,
            verisi_oda_map_keys: verisi.oda ? Object.keys(verisi.oda) : null,
            verisi_oda_bu_id_var: !!(verisi.oda && verisi.oda[odaId]),
            fallback_odaObj_bulundu: !!(window.randevuModalData && Array.isArray(window.randevuModalData.odalar) && window.randevuModalData.odalar.find(function(o){return String(o.id)===String(odaId);})),
        });

        if(intersect && intersect.length){
            return tum.filter(function(h){ return intersect.indexOf(String(h.id)) > -1; });
        }
        // Kisit yok VEYA kesisim bos -> tum hizmetler (fallback; boş listeyle
        // kullaniciyi kilitlemeyelim, veri eksigi olabilir).
        return tum;
    }

    // Verilen satirin hizmet Tom Select'ini personel/oda/cihaz secimine gore yeniler.
    // Halihazirda secili hizmetlerden listede kalanlar korunur; listede olmayanlar dusurulur.
    function v2RefreshHizmetTS($row){
        var $h = $row.find('.v2-hizmet');
        var el = $h[0];
        if(!el || !el.tomselect) return;
        var ts = el.tomselect;
        var personelId = $row.find('.v2-personel').val() || '';
        var cihazId    = $row.find('.v2-cihaz').val()    || '';
        var odaId      = $row.find('.v2-oda').val()      || '';
        var liste = v2FilterHizmetler(personelId, cihazId, odaId);
        var mevcutSecim = ts.getValue() || [];
        if(!Array.isArray(mevcutSecim)) mevcutSecim = mevcutSecim ? [mevcutSecim] : [];

        // TS options'i yeniden yukle: clear + addOption
        ts.clearOptions();
        liste.forEach(function(h){
            ts.addOption({
                value: String(h.id),
                text: h.ad,
                sure: h.sure,
                fiyat: h.fiyat,
                kategori: h.kategori
            });
        });
        ts.refreshOptions(false);
        // Secimi koru — sadece yeni listede olanlar
        var korunan = mevcutSecim.filter(function(id){
            return liste.some(function(h){ return String(h.id) === String(id); });
        });
        ts.setValue(korunan, true); // silent
        try { updateRowMeta(parseInt($row.attr('data-index'), 10)); } catch(e){}
        try { updateSummary(); } catch(e){}
    }

    // -------- ODA → PERSONEL FILTRESI --------
    // Secilen odaya tanimli personel varsa, satirdaki Personel <select> sadece
    // o personelleri gosterir. Tanimli personel yoksa: tum personeller gosterilir.
    function v2RefreshPersonelByOda($row){
        var $p = $row.find('.v2-personel');
        if(!$p.length) return;
        var odaId = $row.find('.v2-oda').val() || '';
        var data = getRandevuModalData();
        var allPersoneller = (data && data.personeller) ? data.personeller : [];
        var verisi = window.randevuHizmetVerisi || {};
        var izinli = null;
        if(odaId && verisi.oda_personel && verisi.oda_personel[odaId] && verisi.oda_personel[odaId].length){
            izinli = verisi.oda_personel[odaId].map(String);
        }
        var liste = (izinli && izinli.length)
            ? allPersoneller.filter(function(p){ return izinli.indexOf(String(p.id)) > -1; })
            : allPersoneller;
        var prevVal = $p.val() || '';
        $p.empty().append('<option value="">Personel...</option>');
        liste.forEach(function(p){ $p.append(new Option(p.ad, p.id)); });
        // Onceki secimi koru — sadece yeni listede varsa
        if(prevVal && liste.some(function(p){ return String(p.id) === String(prevVal); })){
            $p.val(prevVal);
        } else if(prevVal){
            // Secim listede yok: temizle (silent — change tetiklenmesin ki hizmet TS recurse etmesin)
            $p.val('');
        }
        console.log('[V2 ODA→PERSONEL]', {odaId: odaId, izinli_count: izinli ? izinli.length : 0, liste_count: liste.length, prevVal: prevVal});
    }

    // Personel/Cihaz/Oda degisince hizmet TS'ini yenile
    $(document).on('change', '#modal-view-event-add-v2 .v2-personel, #modal-view-event-add-v2 .v2-cihaz, #modal-view-event-add-v2 .v2-oda', function(){
        var $row = $(this).closest('.v2-service-row');
        var isPaket = $row.hasClass('v2-paket-card');
        console.log('[V2 FILTRE handler]', {
            elem: this.className,
            val: this.value,
            rowFound: $row.length > 0,
            isPaket: isPaket
        });
        if(!$row.length || isPaket) return; // paket kartlari farkli logic kullanir
        // Oda degisirse once Personel listesini guncelle (oda-personel eslesmesine gore)
        if($(this).hasClass('v2-oda')){
            v2RefreshPersonelByOda($row);
        }
        v2RefreshHizmetTS($row);
    });

    function updateSummary(){
        var totalSure = 0, totalFiyat = 0;
        $services.find('.v2-service-row').each(function(){
            var $row = $(this);
            // Paket card: kullanicinin overrideladigi sure/fiyat input'larindan oku
            if($row.hasClass('v2-paket-card')){
                var s = parseFloat($row.find('.v2-paket-sure-input').val()) || 0;
                var f = parseFloat(String($row.find('.v2-paket-fiyat-input').val() || '').replace(',','.')) || 0;
                totalSure += s;
                totalFiyat += f;
                return;
            }
            // Normal satir: ÖNCE row-level Süre/Fiyat input'larını oku (kullanıcı manuel girmiş olabilir);
            // input boşsa cache'den default değerleri kullan
            var $rs = $row.find('.v2-row-sure');
            var $rf = $row.find('.v2-row-fiyat');
            var rawSure = String($rs.val() || '').trim();
            var rawFiyat = String($rf.val() || '').trim();
            var manualSure = parseFloat(rawSure);
            var manualFiyat = parseFloat(rawFiyat.replace(/\./g,'').replace(',','.'));
            if(!isNaN(manualSure) && rawSure !== ''){
                totalSure += manualSure;
            }
            if(!isNaN(manualFiyat) && rawFiyat !== ''){
                totalFiyat += manualFiyat;
            }
            // Input boşsa cache'den oku (geriye dönük summary)
            if((isNaN(manualSure) || rawSure === '') || (isNaN(manualFiyat) || rawFiyat === '')){
                var ids = $row.find('.v2-hizmet').val() || [];
                if(!Array.isArray(ids)) ids = ids ? [ids] : [];
                ids.forEach(function(id){
                    var d = window.hizmetDataCache ? window.hizmetDataCache[id] : null;
                    if(d){
                        if(isNaN(manualSure) || rawSure === '') totalSure += parseFloat(d.sure) || 0;
                        if(isNaN(manualFiyat) || rawFiyat === '') totalFiyat += parseFloat(d.fiyat) || 0;
                    }
                });
            }
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

        // Manuel "Paketler" butonu — secili musteri icin paket modalini TEKRAR ac.
        // Mevcut paket kartlarini SILMIYORUZ; kullanici ek paket secebilir ya da
        // ayni paketi tekrar secerse duplicate edilmez (renderPaketRowsInV2 paket_id
        // bazli skip yapiyor).
        $('#v2_paket_aç').off('click.v2paket').on('click.v2paket', function(){
            var userId = $musteri.val();
            if(!userId) return;
            window._v2PaketAkisi = true;
            if(typeof paketKontrolü === 'function'){
                paketKontrolü(userId, false);
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
                // V1 paket yerlesimi async ve uzun surer (her hizmet icin Tom Select init,
                // her satir icin oda/personel inheritance, vs.). window._paketYerlestiriliyor
                // flag'i true iken bekliyoruz; false oldugu an v1 satirlarini okuyup
                // hizmetData ile birlikte v2'ye yansitiyoruz. Bu sayede tum paket
                // hizmetleri (saç + lazer gibi) dogru gruplaniyor, hicbiri kacmiyor.
                var startWait = Date.now();
                var pollPlacement = function(){
                    var stillPlacing = !!window._paketYerlestiriliyor;
                    var elapsed = Date.now() - startWait;
                    // PERFORMANS: minimum bekleme 600ms→100ms, poll 200ms→30ms,
                    // safety 250ms→40ms (v1 batch mode toplu render daha hizli bitiyor)
                    if(stillPlacing || elapsed < 100){
                        if(elapsed > 30000){ console.warn('[V2 DISP] timeout 30s'); return doRender(); }
                        setTimeout(pollPlacement, 30);
                        return;
                    }
                    setTimeout(doRender, 40);
                };
                var doRender = function(){
                    var newV1Indices = [];
                    $('#modal-view-event-add .hizmet-satiri').each(function(i){
                        if(beforeFilledV1.indexOf(i) !== -1) return;
                        var hEl = $(this).find('.hizmet-select')[0];
                        var vals = (hEl && hEl.tomselect) ? hEl.tomselect.getValue() : ($(this).find('.hizmet-select').val() || []);
                        if(!Array.isArray(vals)) vals = vals ? [vals] : [];
                        vals = vals.filter(function(x){return x;}).map(String);
                        if(!vals.length) return;
                        newV1Indices.push({ v1Index: i, hizmetIds: vals });
                    });
                    console.log('[V2 DISP] yerlesim bitti, v1 yeni satir sayisi:', newV1Indices.length, 'hizmetData uzunlugu:', hizmetData.length);
                    renderPaketRowsInV2(hizmetData, newV1Indices);
                };
                pollPlacement();
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

        // 3. Mevcut paket kartlarini ve ilk-acilis bos manuel satirini koru.
        //    DUPLICATE PROTECTION: V2'de zaten bulunan paket_id'leri tespit et,
        //    yeni hizmetData'da bunlar varsa: skip + ilgili duplicate v1 satirlarini bosalt.
        var existingPaketIds = {};
        $services.find('.v2-paket-card').each(function(){
            existingPaketIds[String($(this).attr('data-paket-id'))] = true;
        });

        var paketOrderFiltered = [];
        paketOrder.forEach(function(key){
            if(existingPaketIds[key]){
                // Bu paket zaten v2'de var — yeni eklenen v1 satirlarini bosalt
                paketler[key].v1Indices.forEach(function(v1i){
                    var $v1Row = $('#modal-view-event-add .hizmet-satiri').eq(v1i);
                    if(!$v1Row.length) return;
                    var el = $v1Row.find('.hizmet-select')[0];
                    if(el && el.tomselect){ try{el.tomselect.clear(true);}catch(e){} }
                    $v1Row.find('.personel-select, .personel_secimi').not('.hizmet-select').val(null).trigger('change');
                    $v1Row.find('.cihaz-select, .cihaz_secimi').val(null).trigger('change');
                    $v1Row.find('.oda-select, .oda_secimi').val(null).trigger('change');
                });
            } else {
                paketOrderFiltered.push(key);
            }
        });

        // Paket VEYA standalone (tek hizmet paketi/hizmeti) eklenirken bos manuel
        // satiri kaldir. Aksi halde tek-hizmet paket ekleyince: bos satir + yeni
        // standalone satir = alta 2. satir gozukuyordu. (Kullanici raporu.)
        var inheritedFromRemoved = { personel:'', cihaz:'', oda:'' };
        var yeniIcerikVar = (paketOrderFiltered.length > 0 || standalone.length > 0);
        if(yeniIcerikVar){
            var existingPaketCount = $services.find('.v2-paket-card').length;
            if(existingPaketCount === 0){
                // Hicbir paket yokken: bos manuel satirlari sil (hizmet seçimi yoksa)
                $services.find('.v2-service-row').not('.v2-paket-card').filter(function(){
                    var $h = $(this).find('.v2-hizmet');
                    var el = $h[0];
                    var vals = (el && el.tomselect) ? el.tomselect.getValue() : ($h.val() || []);
                    if(!Array.isArray(vals)) vals = vals ? [vals] : [];
                    return vals.filter(function(x){return x;}).length === 0;
                }).each(function(){
                    // Silmeden önce personel/cihaz/oda yakala (ilk dolu olanı kullan)
                    var p = $(this).find('.v2-personel').val();
                    var c = $(this).find('.v2-cihaz').val();
                    var o = $(this).find('.v2-oda').val();
                    if(!inheritedFromRemoved.personel && p) inheritedFromRemoved.personel = p;
                    if(!inheritedFromRemoved.cihaz && c)    inheritedFromRemoved.cihaz = c;
                    if(!inheritedFromRemoved.oda && o)       inheritedFromRemoved.oda = o;
                    var el = $(this).find('.v2-hizmet')[0];
                    if(el && el.tomselect){ try{el.tomselect.destroy();}catch(e){} }
                    $(this).remove();
                });
            }
        }
        // Paket kartlarına aktarmak için sakla
        window._v2InheritFromRemoved = inheritedFromRemoved;

        // 4. Yeni paketler icin (mevcut olmayanlar) akordiyon kart EKLE
        var startIdx = $services.find('.v2-service-row').length;
        paketOrderFiltered.forEach(function(key, i){
            var grp = paketler[key];
            $services.append(buildPaketCardHTML(grp, startIdx + i));
        });

        // 5. Standalone hizmetler — yalnizca paket akisindan duplicate degilse ekle
        if(standalone.length){
            renderStandaloneAsRows(standalone, newV1Indices, $services.find('.v2-service-row').length);
        }

        // 6. Yeni eklenen paket kartlarinda data + dropdownlari ata
        $services.find('.v2-paket-card').each(function(idx){
            var $card = $(this);
            if($card.data('v1RowIndices') && $card.data('v1RowIndices').length) return; // zaten ayarlandi (mevcut kart)
            var key = $card.attr('data-paket-id');
            var grp = paketler[String(key)];
            if(!grp) return;
            // V1 indekslerini sakla (submit & silme bunlari kullanir)
            $card.data('isPaket', true);
            $card.data('v1RowIndices', grp.v1Indices.slice());
            // Summary icin paketin tum hizmet ID'lerini sakla (paket card'da .v2-hizmet yok)
            $card.data('hizmetIds', grp.hizmetler.map(function(h){ return String(h.id); }));
            // Orijinal sure/fiyat hizmet detaylari (submit'te orantili dagilim icin)
            var origDetails = grp.hizmetler.map(function(h){
                var d = window.hizmetDataCache ? window.hizmetDataCache[h.id] : null;
                return {
                    id: String(h.id),
                    sure: parseFloat((d && d.sure) || h.sure || 0) || 0,
                    fiyat: parseFloat((d && d.fiyat) || 0) || 0
                };
            });
            $card.data('origHizmetler', origDetails);
            $card.data('origTotalSure', parseFloat($card.attr('data-orig-sure')) || 0);
            $card.data('origTotalFiyat', parseFloat($card.attr('data-orig-fiyat')) || 0);
            // Personel/cihaz/oda dropdownlarini doldur
            populateDropdownsInCard($card);

            // Inheritance hiyerarşisi:
            // 1) Silinen boş manuel satırdan (kullanıcı paket'ten ÖNCE personel/cihaz/oda seçmiş)
            // 2) Hala duran v2 manuel satırdan
            // 3) v1'in ilk grup satırından (slot tıklaması)
            var inheritP = '', inheritC = '', inheritO = '';
            var removedInh = window._v2InheritFromRemoved || {};
            if(removedInh.personel) inheritP = removedInh.personel;
            if(removedInh.cihaz)    inheritC = removedInh.cihaz;
            if(removedInh.oda)      inheritO = removedInh.oda;

            if(!inheritP || !inheritC || !inheritO){
                var $v2FirstManual = $services.find('.v2-service-row').not('.v2-paket-card').filter(function(){
                    return ($(this).find('.v2-personel').val() || $(this).find('.v2-cihaz').val() || $(this).find('.v2-oda').val());
                }).first();
                if($v2FirstManual.length){
                    if(!inheritP) inheritP = $v2FirstManual.find('.v2-personel').val() || '';
                    if(!inheritC) inheritC = $v2FirstManual.find('.v2-cihaz').val() || '';
                    if(!inheritO) inheritO = $v2FirstManual.find('.v2-oda').val() || '';
                }
            }
            // Fallback: v1'in ilk grup satırından
            if((!inheritP || !inheritC || !inheritO) && grp.v1Indices.length){
                var $v1First = $('#modal-view-event-add .hizmet-satiri').eq(grp.v1Indices[0]);
                if(!inheritP) inheritP = $v1First.find('.personel-select, .personel_secimi').not('.hizmet-select').val() || '';
                if(!inheritC) inheritC = $v1First.find('.cihaz-select, .cihaz_secimi').val() || '';
                if(!inheritO) inheritO = $v1First.find('.oda-select, .oda_secimi').val() || '';
            }

            if(inheritP) $card.find('.v2-personel').val(inheritP);
            if(inheritC) $card.find('.v2-cihaz').val(inheritC);

            // ODA: kullanıcı zaten seçmişse onu kullan; aksi takdirde paketin
            // hizmetlerine göre sistemde tanımlı oda'yı (oda_sunulan_hizmetler) bul.
            if(inheritO){
                var $odaSelManual = $card.find('.v2-oda');
                if($odaSelManual.length){
                    if($odaSelManual.find('option[value="'+inheritO+'"]').length === 0){
                        // Inherited oda dropdown'da yoksa append et
                        var odaObj = ((window.randevuModalData && window.randevuModalData.odalar) || []).find(function(o){ return String(o.id) === String(inheritO); });
                        if(odaObj) $odaSelManual.append(new Option(odaObj.ad, odaObj.id));
                    }
                    $odaSelManual.val(inheritO);
                }
            } else {
                var hizmetIdsInPaket = grp.hizmetler.map(function(h){ return h.id; });
                var autoOda = findOdaForPaketHizmetler(hizmetIdsInPaket);
                if(autoOda){
                    var $odaSel = $card.find('.v2-oda');
                    if($odaSel.length){
                        if($odaSel.find('option[value="'+autoOda.id+'"]').length === 0){
                            $odaSel.append(new Option(autoOda.ad, autoOda.id));
                        }
                        $odaSel.val(autoOda.id);
                    }
                }
                // autoOda null ise: oda bos kalir, backend hizmet bazinda OdaAtamaServisi::uygunOdaSec ile atar.
            }
        });

        reindexRows();
        updateSummary();
    }

    function buildPaketCardHTML(grp, idx){
        var totalSure = 0, totalFiyat = 0;
        // Orijinal hizmet bilgilerini topla (submit'te orantili dagilim icin gerekli)
        var origHizmetler = [];
        var hizmetListHTML = grp.hizmetler.map(function(h){
            var d = window.hizmetDataCache ? window.hizmetDataCache[h.id] : null;
            var sure = parseFloat((d && d.sure) || h.sure || 0) || 0;
            var fiyat = parseFloat((d && d.fiyat) || 0) || 0;
            totalSure += sure;
            totalFiyat += fiyat;
            origHizmetler.push({ id: String(h.id), sure: sure, fiyat: fiyat });
            var seansTxt = h.seans ? ' <span class="v2-seans-badge">'+h.seans+' seans</span>' : '';
            return '<li><span class="v2-paket-hizmet-ad">'+escapeHtml(h.text)+'</span>'+seansTxt+'<span class="v2-paket-hizmet-sure">'+sure+' dk</span></li>';
        }).join('');

        // Musteriye satilan paket fiyati (adisyon_paketler.fiyat) — varsa hizmet
        // default fiyatlari toplamini override eder. Boylece input musteriye
        // satilan gercek tutari gosterir.
        if (grp && grp.paket_fiyat !== undefined && grp.paket_fiyat !== null) {
            var satisFiyati = parseFloat(grp.paket_fiyat) || 0;
            if (satisFiyati > 0) {
                totalFiyat = satisFiyati;
            }
        }

        var cihazSelHTML = @json($__cihazVar) ?
            '<div class="v2-field"><select class="form-control v2-input v2-sm v2-cihaz"><option value="">Cihaz...</option></select></div>' : '';
        var odaSelHTML = @json($__odaVar) ?
            '<div class="v2-field"><select class="form-control v2-input v2-sm v2-oda"><option value="">Oda...</option></select></div>' : '';

        // Orijinal degerleri JSON olarak sakla (event handler'larin direkt erisebilmesi icin)
        var dataAttrs = ' data-orig-sure="'+totalSure+'" data-orig-fiyat="'+totalFiyat+'"';

        return ''+
        '<div class="v2-service-row v2-paket-card" data-paket-id="'+escapeAttr(grp.paket_id)+'" data-index="'+idx+'"'+dataAttrs+'>'+
            '<div class="v2-paket-head">'+
                '<div class="v2-paket-head-main">'+
                    '<i class="fa fa-gift v2-paket-icon"></i>'+
                    '<span class="v2-paket-title">'+escapeHtml(grp.adi)+'</span>'+
                    '<span class="v2-paket-count">'+grp.hizmetler.length+' hizmet</span>'+
                '</div>'+
                '<div class="v2-paket-totals">'+
                    '<div class="v2-paket-input-wrap" title="Toplam süreyi düzenleyebilirsiniz">'+
                        '<input type="number" class="v2-paket-sure-input" value="'+totalSure+'" min="0" step="5" autocomplete="off">'+
                        '<span class="v2-paket-input-unit">dk</span>'+
                    '</div>'+
                    '<div class="v2-paket-input-wrap" title="Toplam fiyatı düzenleyebilirsiniz">'+
                        '<input type="text" class="v2-paket-fiyat-input" value="'+totalFiyat+'" inputmode="decimal" autocomplete="off">'+
                        '<span class="v2-paket-input-unit">₺</span>'+
                    '</div>'+
                '</div>'+
                '<button type="button" class="v2-paket-toggle-btn" title="Hizmetleri göster/gizle">'+
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

    // Bir paketin tum hizmetleri icin sistemde tanimli (oda_sunulan_hizmetler)
    // ortak oda'yi bulur. Tum hizmetler ayni oda'ya tanimliysa o oda'yi doner.
    // Karisik durum (lazer + cilt bakim ayni paket) varsa null doner — bu
    // durumda backend her hizmet icin ayri ayri otomatik atar.
    function findOdaForPaketHizmetler(hizmetIds){
        if(!window.randevuModalData || !Array.isArray(window.randevuModalData.odalar)) return null;
        var odalar = window.randevuModalData.odalar;
        var ids = hizmetIds.map(function(x){ return parseInt(x, 10); }).filter(function(x){ return !!x; });
        if(!ids.length) return null;

        // Her oda icin: paketin kac hizmetini icerebiliyor
        var best = null;
        var bestScore = 0;
        odalar.forEach(function(oda){
            if(!Array.isArray(oda.hizmet_idleri) || !oda.hizmet_idleri.length) return;
            var match = 0;
            ids.forEach(function(hid){
                if(oda.hizmet_idleri.indexOf(hid) !== -1) match++;
            });
            if(match > bestScore){ bestScore = match; best = oda; }
        });

        // SADECE tum hizmetler eslesirse oda'yi don
        if(best && bestScore === ids.length) return best;
        return null;
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

    // -------- PAKET SURE/FIYAT OVERRIDE (orantili dagilim) --------
    // Kullanici paket kartinda toplam sure/fiyat'i degistirdiyse, v1'deki her
    // hizmet satirinin hizmet-suresi / hizmet-fiyati input'larina orantili
    // olarak yansit. Boylece v1 submit handler dogru toplami hesaplar.
    function applyPaketSureFiyatOverride($card, v1Indices){
        var newSure  = parseFloat($card.find('.v2-paket-sure-input').val()) || 0;
        var newFiyat = parseFloat(String($card.find('.v2-paket-fiyat-input').val() || '').replace(',','.')) || 0;
        var origSure  = parseFloat($card.data('origTotalSure')) || 0;
        var origFiyat = parseFloat($card.data('origTotalFiyat')) || 0;
        var origHizmetler = $card.data('origHizmetler') || [];
        if(!origHizmetler.length) return;

        var n = origHizmetler.length;

        // Sure dagilim mantigi:
        // - origSure > 0 ise: orantili (her hizmet * yeniSure/origSure)
        // - origSure = 0 ise (hizmet detaylari cache'de eksik): TOPLAM newSure'yi
        //   hizmet sayisina esit dagit (her birine newSure/n)
        function calcSure(orig){
            if(origSure > 0) return Math.round((orig.sure || 0) * (newSure / origSure));
            return Math.round(newSure / n);
        }
        function calcFiyat(orig){
            if(origFiyat > 0) return ((orig.fiyat || 0) * (newFiyat / origFiyat));
            return (newFiyat / n);
        }

        // Her v1 satirinda, hizmet ID'sine gore orijinal degeri bulup yeni degeri yaz
        v1Indices.forEach(function(v1i){
            var $v1Row = $('#modal-view-event-add .hizmet-satiri').eq(v1i);
            if(!$v1Row.length) return;
            var hEl = $v1Row.find('.hizmet-select')[0];
            var ids = (hEl && hEl.tomselect) ? hEl.tomselect.getValue() : ($v1Row.find('.hizmet-select').val() || []);
            if(!Array.isArray(ids)) ids = ids ? [ids] : [];
            ids.forEach(function(hid){
                var orig = null;
                for(var k=0; k<origHizmetler.length; k++){
                    if(String(origHizmetler[k].id) === String(hid)){ orig = origHizmetler[k]; break; }
                }
                if(!orig) orig = { id:hid, sure:0, fiyat:0 };
                var newH_sure  = calcSure(orig);
                var newH_fiyat = calcFiyat(orig);

                // V1'in hizmet detay alanindaki input'larini guncelle.
                // Name pattern: hizmet_sureleri-{hizmetId} / hizmet_fiyatlari-{hizmetId}
                var $sureInput  = $v1Row.find('input[name="hizmet_sureleri-'+hid+'"]');
                var $fiyatInput = $v1Row.find('input[name="hizmet_fiyatlari-'+hid+'"]');
                // class bazli fallback (eger ad farkli ise)
                if(!$sureInput.length)  $sureInput  = $v1Row.find('.hizmet-suresi').first();
                if(!$fiyatInput.length) $fiyatInput = $v1Row.find('.hizmet-fiyati').first();
                if($sureInput.length){
                    $sureInput.val(newH_sure).trigger('input').trigger('change');
                }
                if($fiyatInput.length){
                    $fiyatInput.val(newH_fiyat.toFixed(2)).trigger('input').trigger('change');
                }
            });
        });
    }

    // -------- HIZMET VERISI YUKLE (v1'in cache'i bos ise) --------
    function ensureHizmetVerisi(cb){
        // V2 filtre haritalari (personel/cihaz/oda/oda_personel) yuklendiyse short-circuit.
        // NOT: hizmetDataCache v1'den dolu olabilir ama randevuHizmetVerisi.oda_personel
        // bos olabilir; bu durumda mutlaka fetch yapmaliyiz, aksi takdirde oda-personel
        // filtresi calismaz.
        var v = window.randevuHizmetVerisi;
        if(v && v.tum && v.tum.length && v.oda_personel && typeof v.oda_personel === 'object'){
            window.hizmetDataCache = window.hizmetDataCache || {};
            v.tum.forEach(function(h){
                if(!window.hizmetDataCache[h.id]){
                    window.hizmetDataCache[h.id] = { id:h.id, text:h.ad, sure:h.sure||0, fiyat:h.fiyat||0, kategori:h.kategori||'' };
                }
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
                    cihaz: (resp && resp.cihaz_hizmet_map) ? resp.cihaz_hizmet_map : {},
                    oda: (resp && resp.oda_hizmet_map) ? resp.oda_hizmet_map : {},
                    oda_personel: (resp && resp.oda_personel_map) ? resp.oda_personel_map : {}
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
            if($services.children().length === 0) addRow();
            // YARIS KOSULU FIX: resetV2Form modal acilinca addRow cagirdi ama
            // o anda hizmetDataCache henuz bos olabiliyordu -> Row 1'in Tom Select'i
            // bos options ile init edildi. Cache simdi dolu -> bos TS'leri yeniden init et.
            try {
                $services.find('.v2-service-row').each(function(){
                    var el = $(this).find('.v2-hizmet')[0];
                    if(!el) return;
                    var ts = el.tomselect;
                    // TS yok ya da options bos -> yeniden init
                    if(!ts || (ts && Object.keys(ts.options).length === 0)){
                        if(ts){ try { ts.destroy(); } catch(e){} }
                        initRowSelects($(this));
                    }
                });
            } catch(e){ console.warn('[V2] hizmet TS reinit hata:', e); }
            installPaketDispatcher();
            initKapamaPersonel();
            v2Init = true;
        });
    }

    // Saat Kapama formundaki personel dropdownini doldur
    function initKapamaPersonel(){
        var $sel = $('#v2_kapama_personel');
        if(!$sel.length) return;
        var data = getRandevuModalData();
        $sel.empty().append('<option value="">Personel seçin...</option>');
        data.personeller.forEach(function(p){
            $sel.append(new Option(p.ad, p.id));
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

        // Mode'u Randevu'ya sifirla (tab varsa)
        $modal.find('.v2-tab').removeClass('active');
        $modal.find('.v2-tab[data-mode="randevu"]').addClass('active');
        $('.v2-mode-kapama').hide();
        $('.v2-mode-randevu').show();
        $('.v2-submit-kapama').hide();
        $('.v2-submit-randevu').show();
        $('.v2-title').text('Yeni Randevu');
        $('#v2_subtitle_text').text('Müşteri ve hizmet seç');
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

    // Datepicker locale/format ne olursa olsun input degisince YYYY-MM-DD'ye
    // normalize et (kullanici gorsel olarak da YMD gorsun, submit'te de temiz gitsin).
    $modal.on('change blur', '#v2_tarih, #v2_kapama_tarih', function(){
        var norm = _normalizeTarihYMD($(this).val());
        if (norm && norm !== $(this).val()) {
            $(this).val(norm);
        }
    });

    $modal.on('hidden.bs.modal', function(){
        // Form'u tamamen sifirla: musteri, hizmet, personel, oda, not, tekrar, summary
        resetV2Form();
        // V1 modal'inin Bootstrap state'ini sifirla — sonraki slot tiklamasi calissin
        var $v1 = $('#modal-view-event-add');
        $v1.removeClass('show in').css('display','none').attr('aria-hidden','true').removeAttr('aria-modal');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right','');
        var bsData = $v1.data('bs.modal');
        if(bsData){
            bsData._isShown = false;
            bsData._isTransitioning = false;
        }
    });

    $modal.on('click', '#v2_add_row', addRow);

    // Paket kart akordiyon: header'a tiklayinca veya chevron butonuna basinca toggle
    // ANCAK input/select/butonlara tiklamak tetiklemesin
    $modal.on('click', '.v2-paket-head', function(e){
        if($(e.target).closest('.v2-paket-totals, .v2-paket-input-wrap, input, select, button').length){
            return; // input/buton'a tiklandi, toggle yapma
        }
        var $card = $(this).closest('.v2-paket-card');
        $card.toggleClass('expanded');
        $card.find('.v2-paket-detay').slideToggle(180);
    });
    $modal.on('click', '.v2-paket-toggle-btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        var $card = $(this).closest('.v2-paket-card');
        $card.toggleClass('expanded');
        $card.find('.v2-paket-detay').slideToggle(180);
    });

    // Sure / Fiyat input degisince summary'yi yenile
    $modal.on('input change', '.v2-paket-sure-input, .v2-paket-fiyat-input', function(){
        updateSummary();
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

    // Mode toggle: header tab'lariyla (Randevu / Saat Kapama)
    $modal.on('click', '.v2-tab', function(){
        var $tab = $(this);
        if($tab.hasClass('active')) return;
        $modal.find('.v2-tab').removeClass('active');
        $tab.addClass('active');
        var mode = $tab.data('mode');
        if(mode === 'kapama'){
            $('.v2-mode-randevu').hide();
            $('.v2-mode-kapama').show();
            $('.v2-submit-randevu').hide();
            $('.v2-submit-kapama').show();
            $('.v2-title').text('Saat Kapama');
            $('#v2_subtitle_text').text('Personel için zaman bloku oluştur');
        } else {
            $('.v2-mode-kapama').hide();
            $('.v2-mode-randevu').show();
            $('.v2-submit-kapama').hide();
            $('.v2-submit-randevu').show();
            $('.v2-title').text('Yeni Randevu');
            $('#v2_subtitle_text').text('Müşteri ve hizmet seç');
        }
    });


    // ============================================================
    // V1 -> V2 REDIRECT INTERCEPT
    // window.useV2Modal=true ise (v2 takvim sayfasi), takvim slot
    // tiklamasi v1 modalini acmaya calisir; biz yakalayip kapatip
    // alanlari v2'ye kopyalayip v2'yi aciyoruz.
    // ============================================================
    function _v2InterceptHandler(e){
        if(!window.useV2Modal) return;
        if(window._v2BypassIntercept) return;
        console.log('[V2 INTERCEPT] v1 modal show yakalandi → v2 acilacak');
        // Bootstrap 4'te preventDefault bazen yetmiyor; her yolu dene
        if(e && e.preventDefault) e.preventDefault();
        if(e && e.stopImmediatePropagation) e.stopImmediatePropagation();
        if(e && e.stopPropagation) e.stopPropagation();

        var $v1 = $('#modal-view-event-add');
        // V1'i ZORLA gizle (Bootstrap classlari + inline style + backdrop temizligi)
        $v1.removeClass('show in').css('display','none').attr('aria-hidden','true').removeAttr('aria-modal');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right','');

        // KRITIK: Bootstrap modal'inin INTERNAL STATE'ini de sifirla. Aksi takdirde
        // _isShown=true kalir, bir sonraki .modal('show') cagrisi no-op olur ve
        // show.bs.modal hic firlamaz — bizim intercept de calismaz, slot tiklamasi
        // hicbir sey acmaz (yenileme gerekene kadar).
        var bsData = $v1.data('bs.modal');
        if(bsData){
            bsData._isShown = false;
            bsData._isTransitioning = false;
        }

        // Slot bilgilerini v1 alanlarindan oku (eventClick handler zaten doldurmustu)
        var tarih = $('#randevutarihiyeni').val();
        var saat  = $('#randevu_saat').val();
        var v1PersonelOpt = $('select[name="randevupersonelleriyeni[]"]:first option:selected');
        var v1OdaOpt      = $('select[name="randevuodalariyeni[]"]:first option:selected');
        var v1CihazOpt    = $('select[name="randevucihazlariyeni[]"]:first option:selected');

        // V2'yi ac, shown event'inde alanlari doldur
        // KRITIK: ensureHizmetVerisi async AJAX; setTimeout(100) bunu beklemez.
        // Oda-personel haritasi yuklenmeden filtre calismaz. Bu yuzden gercek doldurma
        // mantigini ensureHizmetVerisi callback'ine sariyoruz.
        var fillV2 = function(){
            var doFill = function(){
                if(tarih) $tarih.val(tarih);
                if(saat) $saat.val(saat);

                // Ilk satira personel/oda/cihaz yaz
                var $firstRow = $services.find('.v2-service-row').first();
                if(!$firstRow.length){
                    addRow();
                    $firstRow = $services.find('.v2-service-row').first();
                }
                // ODA ONCE: oda-personel eslesmesini personel listesine yansitabilmek icin
                if(v1OdaOpt.val()){
                    var $vo = $firstRow.find('.v2-oda');
                    if($vo.length){
                        if($vo.find('option[value="'+v1OdaOpt.val()+'"]').length === 0){
                            $vo.append(new Option(v1OdaOpt.text(), v1OdaOpt.val()));
                        }
                        $vo.val(v1OdaOpt.val());
                    }
                }
                // Oda secildiyse personel <select>'i ona gore filtrele (programmatik
                // .val() change tetiklemediginden elden cagiriyoruz).
                try {
                    if(typeof v2RefreshPersonelByOda === 'function'){
                        v2RefreshPersonelByOda($firstRow);
                    }
                } catch(e){ console.warn('[V2 slot fill oda→personel] hata:', e); }
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
                // Programmatik .val() change event tetiklemiyor — filtreyi elden zorla.
                // TomSelect init'inin async tamamlanma ihtimaline karsi 4 zamanda dene:
                // hemen, 100ms, 300ms, 800ms. Filtre en gec 800ms'de uygulanir.
                var _slotFilter = function(tag){
                    try {
                        if(typeof v2RefreshHizmetTS === 'function'){
                            var $hz = $firstRow.find('.v2-hizmet');
                            var el = $hz[0];
                            var haz = el && el.tomselect;
                            console.log('[V2 slot filter '+tag+']', {
                                tsHazir: !!haz,
                                odaVal: $firstRow.find('.v2-oda').val() || null,
                                persVal: $firstRow.find('.v2-personel').val() || null,
                                cihazVal: $firstRow.find('.v2-cihaz').val() || null,
                            });
                            v2RefreshHizmetTS($firstRow);
                        }
                    } catch(e){ console.warn('[V2 slot fill filter '+tag+'] hata:', e); }
                };
                _slotFilter('sync');
                setTimeout(function(){ _slotFilter('100ms'); }, 100);
                setTimeout(function(){ _slotFilter('300ms'); }, 300);
                setTimeout(function(){ _slotFilter('800ms'); }, 800);
                $modal.off('shown.bs.modal.slotfill').on('shown.bs.modal.slotfill', function(){
                    $modal.off('shown.bs.modal.slotfill');
                });
            };
            // Haritalar yuklenmis mi? Yuklenmediyse ensureHizmetVerisi cb'sinde calistir.
            var v = window.randevuHizmetVerisi;
            if(v && v.tum && v.tum.length && v.oda_personel){
                doFill();
            } else if(typeof ensureHizmetVerisi === 'function'){
                console.log('[V2 fillV2] ensureHizmetVerisi bekleniyor (oda_personel henuz yok)');
                ensureHizmetVerisi(function(){ doFill(); });
            } else {
                doFill();
            }
        };

        // V2 init zaten yapildiysa direkt doldur; degilse shown'da
        if(v2Init){
            $modal.modal('show');
            setTimeout(fillV2, 100);
        } else {
            $modal.one('shown.bs.modal', function(){ setTimeout(fillV2, 100); });
            $modal.modal('show');
        }
    }

    // V1 modal hem show hem shown event'lerinde yakalanir (safety net)
    $(document).on('show.bs.modal',   '#modal-view-event-add', _v2InterceptHandler);
    $(document).on('shown.bs.modal',  '#modal-view-event-add', _v2InterceptHandler);

    // -------- SUBMIT (proxy to v1) --------
    // ============================================================
    // V2 SUBMIT — Her paket AYRI bir randevu kaydi (sequential POST).
    // V1 proxy yaklasimi tek randevu olusturuyor (backend foreach
    // randevupersonelleriyeni). Iki paket secince kullanici takvimde
    // iki ayri blok bekliyor. Bu yuzden her paket card icin ayri
    // FormData kurup /yenirandevuekle'ye sirayla POST atiyoruz.
    // Manuel satirlar tek bir randevuda toplaniyor.
    // ============================================================
    $modal.on('click', '#v2_submit_btn', function(){ v2SubmitAll(); });

    // Tarih normalize: kullanicinin datepicker'i / tarayici locale'i gibi
    // sebeplerle '16.08.2026', '16/08/2026', '2026-08-16', '2026/08/16'
    // gibi degerlerde gelebilir. Backend 'Y-m-d' bekliyor — donduremezsek
    // 0000-00-00 kaydediliyor.
    function _normalizeTarihYMD(v){
        if (!v) return '';
        var s = String(v).trim();
        // Zaten YYYY-MM-DD ise dokunma
        var m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (m) return m[1] + '-' + ('0'+m[2]).slice(-2) + '-' + ('0'+m[3]).slice(-2);
        // YYYY/MM/DD
        m = s.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
        if (m) return m[1] + '-' + ('0'+m[2]).slice(-2) + '-' + ('0'+m[3]).slice(-2);
        // DD.MM.YYYY veya D.M.YYYY
        m = s.match(/^(\d{1,2})[.\-\/](\d{1,2})[.\-\/](\d{4})$/);
        if (m) return m[3] + '-' + ('0'+m[2]).slice(-2) + '-' + ('0'+m[1]).slice(-2);
        // Son care: Date parse (locale'ye bagimli)
        try {
            var d = new Date(s);
            if (!isNaN(d.getTime())) {
                var yy = d.getFullYear();
                var mm = ('0'+(d.getMonth()+1)).slice(-2);
                var dd = ('0'+d.getDate()).slice(-2);
                return yy + '-' + mm + '-' + dd;
            }
        } catch(e){}
        return s; // parse edilemedi -> orjinali gonder (backend hata verecek)
    }

    function v2SubmitAll(){
        var musteriId = $musteri.val();
        var tarih = _normalizeTarihYMD($tarih.val());
        var saat = $saat.val();

        var paketCards = $services.find('.v2-paket-card').toArray();
        var manualRows = $services.find('.v2-service-row').not('.v2-paket-card').filter(function(){
            var vals = $(this).find('.v2-hizmet').val();
            if(!vals) return false;
            if(!Array.isArray(vals)) vals = [vals];
            return vals.filter(function(x){return x;}).length > 0;
        }).toArray();

        // Validation
        var errs = [];
        if(!musteriId) errs.push('Müşteri seçiniz');
        if(!tarih) errs.push('Tarih seçiniz');
        if(!saat) errs.push('Saat seçiniz');
        if(!paketCards.length && !manualRows.length) errs.push('Hizmet seçiniz');

        // Her grup icin personel/cihaz/oda kontrolu (takvim turune gore)
        var turu = parseInt(window.randevuTakvimTuru || 0, 10);
        function eksikKaynak($el){
            var p = $el.find('.v2-personel').val();
            var c = $el.find('.v2-cihaz').val();
            var o = $el.find('.v2-oda').val();
            return (turu === 1) ? !p : !(p || c || o);
        }
        var kaynakErrAdded = false;
        var allGroups = paketCards.concat(manualRows);
        allGroups.forEach(function(el){
            if(eksikKaynak($(el)) && !kaynakErrAdded){
                errs.push(turu === 1 ? 'Her satıra personel seçiniz' : 'Her satıra personel, cihaz veya oda seçiniz');
                kaynakErrAdded = true;
            }
        });

        if(errs.length){
            var html = errs.map(function(e){ return '- '+e+'<br>'; }).join('');
            if(typeof swal !== 'undefined'){
                swal({type:'warning', title:'Uyarı', html:'Devam etmek için;<br><br>'+html, timer:4000, showConfirmButton:false});
            } else { alert(html.replace(/<br>/g,'\n')); }
            return;
        }

        var common = {
            musteriId: musteriId, tarih: tarih, saat: saat,
            not: $('#v2_not').val() || '',
            tek: $('#v2_tekrarlayan').is(':checked'),
            tekSiklik: $('#v2_tekrar_sikligi').val(),
            tekSayi: $('#v2_tekrar_sayisi').val()
        };

        var groups = [];
        paketCards.forEach(function(c){ groups.push({type:'paket', $el: $(c)}); });
        if(manualRows.length){ groups.push({type:'manual', rows: manualRows}); }

        $('#preloader').show();
        submitGroupsSeq(groups, 0, common, []);
    }

    function submitGroupsSeq(groups, idx, common, results){
        if(idx >= groups.length){
            $('#preloader').hide();
            var ok = results.filter(function(r){return r.success;}).length;
            var fail = results.filter(function(r){return r.error;}).length;
            var skipped = results.filter(function(r){return r.skipped;}).length;
            if(ok > 0){
                $modal.modal('hide');
                if(typeof takvimyukle === 'function') takvimyukle(true, true);
            }
            // Hicbir sey olusmadi VE hata yok (kullanici cakismada 'Vazgec' dedi) =>
            // gereksiz "0 olusturuldu, N atlandi" ozetini gosterme, sessizce cik.
            if(ok === 0 && fail === 0){
                return;
            }
            var summary = ok + ' randevu oluşturuldu';
            if(skipped) summary += ', ' + skipped + ' atlandı';
            if(fail) summary += ', ' + fail + ' hata';
            if(typeof swal !== 'undefined'){
                swal({type: fail ? 'warning' : 'success', title: fail ? 'Kısmi başarı' : 'Başarılı', text: summary, timer:3500, showConfirmButton:false});
            }
            return;
        }
        var group = groups[idx];
        postOneGroup(group, common, false, function(result){
            if(result.kaynak_cakismasi){
                $('#preloader').hide(); // popup arkasinda spinner takili kalmasin
                var kc = result.kaynak_cakismasi || {};
                if(typeof swal !== 'undefined'){
                    swal({
                        type: 'warning',
                        title: 'Çakışma Uyarısı',
                        html: '<p style="font-size:14.5px;color:#4b5563;margin:6px 0 12px;line-height:1.5;">'+(kc.mesaj || 'Seçilen kaynak bu saatte dolu.')+'</p>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa fa-check"></i> Evet, oluştur',
                        cancelButtonText: '<i class="fa fa-times"></i> Vazgeç',
                        confirmButtonColor: '#7c3aed',
                        cancelButtonColor: '#9ca3af',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function(confirm){
                        if(confirm.value){
                            $('#preloader').show(); // onayladi -> tekrar gonderiliyor
                            postOneGroup(group, common, false, function(r2){
                                results.push(r2.success ? {success:true} : {error:true});
                                submitGroupsSeq(groups, idx+1, common, results);
                            }, true); // forceKaynak=1 -> backend kaynak kontrolunu atlar
                        } else {
                            results.push({skipped:true});
                            submitGroupsSeq(groups, idx+1, common, results);
                        }
                    });
                } else {
                    if(confirm((kc.mesaj || 'Seçilen kaynak bu saatte dolu.').replace(/<[^>]*>/g,''))){
                        postOneGroup(group, common, false, function(r2){
                            results.push(r2.success ? {success:true} : {error:true});
                            submitGroupsSeq(groups, idx+1, common, results);
                        }, true);
                    } else {
                        results.push({skipped:true});
                        submitGroupsSeq(groups, idx+1, common, results);
                    }
                }
                return;
            }
            if(result.cakismavar){
                $('#preloader').hide(); // popup arkasinda spinner takili kalmasin
                if(typeof swal !== 'undefined'){
                    swal({
                        type: 'warning',
                        title: 'Randevu Çakışması',
                        html: '<p style="font-size:14.5px;color:#4b5563;margin:6px 0 12px;line-height:1.5;">Vermek istediğiniz randevu saati dolu, gene de randevu vermek istiyor musunuz?</p>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa fa-check"></i> Evet',
                        cancelButtonText: '<i class="fa fa-times"></i> Hayır',
                        confirmButtonColor: '#7c3aed',
                        cancelButtonColor: '#9ca3af',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function(confirm){
                        if(confirm.value){
                            postOneGroup(group, common, true, function(r2){
                                results.push(r2.success ? {success:true} : {error:true});
                                submitGroupsSeq(groups, idx+1, common, results);
                            });
                        } else {
                            results.push({skipped:true});
                            submitGroupsSeq(groups, idx+1, common, results);
                        }
                    });
                } else {
                    if(confirm('Vermek istediğiniz randevu saati dolu, gene de vermek istiyor musunuz?')){
                        postOneGroup(group, common, true, function(r2){
                            results.push(r2.success ? {success:true} : {error:true});
                            submitGroupsSeq(groups, idx+1, common, results);
                        });
                    } else {
                        results.push({skipped:true});
                        submitGroupsSeq(groups, idx+1, common, results);
                    }
                }
            } else if(result.success){
                results.push({success:true});
                submitGroupsSeq(groups, idx+1, common, results);
            } else {
                results.push({error:true});
                if(typeof swal !== 'undefined'){
                    swal({type:'error', title:'Hata', text: result.errorMsg || 'Randevu oluşturulamadı', timer:4000, showConfirmButton:false});
                }
                submitGroupsSeq(groups, idx+1, common, results);
            }
        });
    }

    function postOneGroup(group, common, forceCakisma, cb, forceKaynak){
        var formData = new FormData();
        var csrf = $('input[name="_token"]').first().val() || $('meta[name="csrf-token"]').attr('content') || '';
        formData.append('_token', csrf);
        formData.append('sube', '{{ $isletme->id }}');
        formData.append('adsoyad', common.musteriId);
        formData.append('tarih', common.tarih);
        formData.append('saat', common.saat);
        formData.append('personel_notu', common.not);
        if(forceCakisma) formData.append('cakisanrandevuekle', '1');
        if(forceKaynak) formData.append('kaynak_cakisma_onay', '1');
        if(common.tek){
            formData.append('tekrarlayan', 'on');
            formData.append('tekrar_sikligi', common.tekSiklik);
            formData.append('tekrar_sayisi', common.tekSayi);
        }

        // BACKEND'IN BEKLEDIGI ALANLAR:
        // 1) Create logic (line 6484+ yenirandevuekle):
        //    foreach($request->randevupersonelleriyeni as $key2 => $value){
        //      $rHizmetler = $request->{"randevuhizmetleriyeni_{$key2}"};
        //      ...
        //    }
        //    Bu yuzden randevupersonelleriyeni[] (her satir icin 1) + her satir
        //    icin ayri randevuhizmetleriyeni_N[] gerek.
        // 2) Conflict check (line 21070 cakisan_randevu_kontrol):
        //    foreach($request->randevuhizmetleriyeni as $key => $value){
        //      $request->hizmet_suresi[$key]
        //    }
        //    Bu yuzden EK olarak FLAT randevuhizmetleriyeni[] ve hizmet_suresi[]
        //    de gonderilmeli (her hizmet icin 1 entry, satir bazli sirayla).
        if(group.type === 'paket'){
            var $card = group.$el;
            var personelId = $card.find('.v2-personel').val() || '';
            var cihazId    = $card.find('.v2-cihaz').val() || '';
            var odaId      = $card.find('.v2-oda').val() || '';
            var hizmetIds  = $card.data('hizmetIds') || [];
            var newSure    = parseFloat($card.find('.v2-paket-sure-input').val()) || 0;
            var newFiyat   = parseFloat(String($card.find('.v2-paket-fiyat-input').val()||'').replace(',','.')) || 0;
            var origHizmetler = $card.data('origHizmetler') || [];
            var origTotalSure  = origHizmetler.reduce(function(s,h){return s+(parseFloat(h.sure)||0);},0);
            var origTotalFiyat = origHizmetler.reduce(function(s,h){return s+(parseFloat(h.fiyat)||0);},0);

            // Her hizmet = bir satir (key2 = i)
            hizmetIds.forEach(function(hid, i){
                formData.append('randevupersonelleriyeni[]', personelId);
                formData.append('randevucihazlariyeni[]', cihazId);
                formData.append('randevuodalariyeni[]', odaId);
                formData.append('randevuhizmetleriyeni_'+i+'[]', hid);
                formData.append('randevuyardimcipersonelleriyeni_'+i+'[]', '');

                var origH = null;
                for(var k=0; k<origHizmetler.length; k++){
                    if(String(origHizmetler[k].id) === String(hid)){ origH = origHizmetler[k]; break; }
                }
                if(!origH) origH = {sure:0, fiyat:0};
                var calcSure = origTotalSure > 0
                    ? Math.round((parseFloat(origH.sure)||0) * newSure/origTotalSure)
                    : Math.round(newSure/hizmetIds.length);
                var calcFiyat = origTotalFiyat > 0
                    ? ((parseFloat(origH.fiyat)||0) * newFiyat/origTotalFiyat)
                    : (newFiyat/hizmetIds.length);

                formData.append('hizmet_sureleri-'+hid, calcSure);
                formData.append('hizmet_fiyatlari-'+hid, calcFiyat.toFixed(2));
                formData.append('hizmet_miktarlari-'+hid, '1');

                // BACKWARD COMPAT (conflict check icin)
                formData.append('randevuhizmetleriyeni[]', hid);
                formData.append('hizmet_suresi[]', calcSure);
            });
        } else {
            // Manuel satirlar (tek bir randevu icinde gruplanir)
            var flatIdx = 0; // her satirdaki her hizmet icin global index (flat compat)
            group.rows.forEach(function(row, i){
                var $row = $(row);
                var personelId = $row.find('.v2-personel').val() || '';
                var cihazId    = $row.find('.v2-cihaz').val() || '';
                var odaId      = $row.find('.v2-oda').val() || '';
                var hizmetIds  = $row.find('.v2-hizmet').val() || [];
                if(!Array.isArray(hizmetIds)) hizmetIds = [hizmetIds];

                formData.append('randevupersonelleriyeni[]', personelId);
                formData.append('randevucihazlariyeni[]', cihazId);
                formData.append('randevuodalariyeni[]', odaId);
                formData.append('randevuyardimcipersonelleriyeni_'+i+'[]', '');

                // Satır seviyesi manuel Süre + Fiyat input'larından oku
                var rowSureRaw = String($row.find('.v2-row-sure').val() || '').trim();
                var rowFiyatRaw = String($row.find('.v2-row-fiyat').val() || '').trim();
                var rowSureMan = parseFloat(rowSureRaw);
                var rowFiyatMan = parseFloat(rowFiyatRaw.replace(/\./g,'').replace(',','.'));
                var hasManSure  = !isNaN(rowSureMan)  && rowSureRaw  !== '';
                var hasManFiyat = !isNaN(rowFiyatMan) && rowFiyatRaw !== '';

                hizmetIds.forEach(function(hid, hidx){
                    formData.append('randevuhizmetleriyeni_'+i+'[]', hid);
                    var d = window.hizmetDataCache ? window.hizmetDataCache[hid] : null;
                    var defaultSure  = d ? parseFloat(d.sure)||0  : 0;
                    var defaultFiyat = d ? parseFloat(d.fiyat)||0 : 0;
                    // Manuel girilmişse: ilk hizmete TOPLAM ata, diğerlerine 0 (paket mantığı,
                    // randevu blok-bazlı toplam süre/fiyat kadar yer kaplar)
                    var sure  = hasManSure  ? (hidx === 0 ? rowSureMan  : 0) : defaultSure;
                    var fiyat = hasManFiyat ? (hidx === 0 ? rowFiyatMan : 0) : defaultFiyat;
                    formData.append('hizmet_sureleri-'+hid, sure);
                    formData.append('hizmet_fiyatlari-'+hid, (typeof fiyat === 'number' ? fiyat : 0).toFixed(2));
                    formData.append('hizmet_miktarlari-'+hid, '1');
                    // BACKWARD COMPAT (cakisma kontrolu)
                    formData.append('randevuhizmetleriyeni[]', hid);
                    formData.append('hizmet_suresi[]', sure);
                    flatIdx++;
                });
            });
        }

        $.ajax({
            type: 'POST',
            url: '/isletmeyonetim/yenirandevuekle',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res){
                if(res && res.cakismavar){ cb({cakismavar: res.cakismavar}); }
                else if(res && res.kaynak_cakismasi){ cb({kaynak_cakismasi: res}); }
                else { cb({success:true, response: res}); }
            },
            error: function(req){
                var msg = '';
                try { var j = JSON.parse(req.responseText); msg = j && j.error ? j.error : req.responseText; }
                catch(e){ msg = req.responseText || ('HTTP '+req.status); }
                cb({error:true, errorMsg: msg});
            }
        });
    }

    // Saat Kapama submit — dogrudan /isletmeyonetim/saatkapamaekle endpoint'ine
    // (V1 saat_kapama formu personel select'i bos kaliyor cunku v1 modal acilmamis
    // oldugundan options yuklenmemis — proxy patliyor.)
    $modal.on('click', '#v2_submit_kapama_btn', function(){
        var personelId = $('#v2_kapama_personel').val();
        var tarih      = _normalizeTarihYMD($('#v2_kapama_tarih').val());
        var $saat      = $('#saatkapamaform_v2 input[name="saat"]');
        var $saatBitis = $('#saatkapamaform_v2 input[name="saat_bitis"]');
        var tumGun     = $('#v2_kapama_tumgun').is(':checked');
        var notu       = $('#saatkapamaform_v2 textarea[name="personel_notu"]').val();

        // Frontend validation
        var hata = '';
        if(!personelId) hata += '- Personel seçiniz<br>';
        if(!tarih)      hata += '- Tarih giriniz<br>';
        if(!tumGun){
            if(!$saat.val())      hata += '- Başlangıç saati giriniz<br>';
            if(!$saatBitis.val()) hata += '- Bitiş saati giriniz<br>';
        }
        if(hata){
            if(typeof swal !== 'undefined'){
                swal({ type:'warning', title:'Uyarı', html:hata, timer:3000, showConfirmButton:false });
            } else {
                alert(hata.replace(/<br>/g,'\n'));
            }
            return;
        }

        var formData = new FormData();
        formData.append('_token', $('input[name="_token"]').first().val() || $('meta[name="csrf-token"]').attr('content') || '');
        formData.append('sube', '{{ $isletme->id }}');
        formData.append('personel', personelId);
        formData.append('tarih', tarih);
        formData.append('saat', $saat.val() || '');
        formData.append('saat_bitis', $saatBitis.val() || '');
        formData.append('tum_gun', tumGun ? 'on' : '');
        formData.append('personel_notu', notu || '');
        // Tekrarlayan v2'de yok — v1 backend opsiyonel kabul ediyor
        formData.append('tekrarlayan', '');
        formData.append('tekrar_sikligi', '+1 day');
        formData.append('tekrar_sayisi', '0');

        $.ajax({
            type: 'POST',
            url: '/isletmeyonetim/saatkapamaekle',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(){ $('#preloader').show(); },
            success: function(result){
                $('#preloader').hide();
                var msg = (result && result.message) ? result.message : 'Saat kapama eklendi';
                if(typeof swal !== 'undefined'){
                    swal({ type:'success', title:'Başarılı', text:msg, timer:2000, showConfirmButton:false });
                }
                $modal.modal('hide');
                if(typeof takvimyukle === 'function'){ takvimyukle(true,true); }
            },
            error: function(req){
                $('#preloader').hide();
                var errMsg = '';
                try { var j = JSON.parse(req.responseText); errMsg = j && j.error ? j.error : req.responseText; }
                catch(e){ errMsg = req.responseText || 'Bilinmeyen hata'; }
                if(typeof swal !== 'undefined'){
                    swal({ type:'error', title:'Saat kapama kaydedilemedi', text:errMsg });
                } else {
                    alert('Hata: '+errMsg);
                }
            }
        });
    });

    // ---- Musteri ekleme modali, randevu (v2) modali ustunde acilsin ----
    // v2 randevu modali z-index:100003. Bootstrap musteri-bilgi-modal varsayilan 1050
    // oldugu icin arkada kaliyordu. Randevu modali acikken ustune cikar.
    $(document).on('show.bs.modal', '#musteri-bilgi-modal', function(){
        if ($('#modal-view-event-add-v2').hasClass('show') || $('#modal-view-event-add-v2').is(':visible')) {
            $(this).css('z-index', 100020);
            // bu modal icin olusan backdrop'i da randevu modali ustune al (dimming icin)
            setTimeout(function(){
                $('.modal-backdrop').last().css('z-index', 100015);
            }, 0);
        }
    });
    $(document).on('hidden.bs.modal', '#musteri-bilgi-modal', function(){
        $(this).css('z-index', '');
        // alttaki randevu modali hala acikken Bootstrap'in scroll kilidini geri koy
        if ($('#modal-view-event-add-v2').hasClass('show') || $('#modal-view-event-add-v2').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

})();
</script>
