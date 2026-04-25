@php
    // Randevu takvim turune gore personel / cihaz / oda secimlerinin gorunurlugu
    // 0: Hizmete Gore (hepsi gorunur)
    // 1: Personele Gore (sadece personel)
    // 2: Cihaza Gore (sadece cihaz)
    // 3: Odaya Gore (sadece oda)
    $__takvim_turu = $isletme->randevu_takvim_turu ?? 0;
    $__personel_style = in_array($__takvim_turu, [2, 3]) ? 'display:none;' : '';
    $__cihaz_style    = in_array($__takvim_turu, [1, 3]) ? 'display:none;' : '';
    $__oda_style      = in_array($__takvim_turu, [1, 2]) ? 'display:none;' : '';
    $__yardimci_style = 'display:none;'; // her zaman gizli
@endphp

{{-- Tom Select — sadece Randevu modalinda hizmet secimi icin kullanilir --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<div id="modal-view-event-add" class="modal modal-top fade calendar-modal randevu-modal-compact" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="h4" style="color:white; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-calendar-plus-o" style="font-size:0.95rem;opacity:0.9;"></i>
                    <span>Yeni Randevu</span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id='randevu_modal_kapat'>
                    ×
                </button>
            </div>
            <div class="modal-body">
                <div class="tab">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active text-blue" data-toggle="tab" href="#yeni-randevu" role="tab" aria-selected="true">Randevu</a>
                        </li>
                        @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel') && !Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))
                        <li class="nav-item">
                            <a class="nav-link text-blue" data-toggle="tab" href="#saat-kapama" role="tab" aria-selected="false">Saat Kapama</a>
                        </li>
                        @endif
                        <!-- Paketleri Göster Butonu -->
                        <li class="nav-item ml-auto">
                            <button type="button" class="btn btn-info btn-sm" id="paketleri-goster-btn" disabled>
                                <i class="icon-copy fa fa-gift"></i> Paketleri Göster
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Randevu Ekleme Bölümü -->
                        <div class="tab-pane fade show active" id="yeni-randevu" role="tabpanel">
                            <form id="yenirandevuekleform" method="POST" action="#">
                                {!!csrf_field()!!}
                                {{-- Gizli ozet konteyneri: updateRandevuOzeti() JS tarafindan hala calisir, sadece kullaniciya gosterilmez --}}
                                <div id="randevu-ozeti" style="display:none;"></div>
                                <div class="row">
                                    <!-- Tum icerik tek sutunda -->
                                    <div class="col-12">
                                        <!-- Temel Bilgiler -->
                                        <div class="card mb-2">
                                            <div class="card-header py-1">
                                                <h6 class="mb-0" style="font-size: 0.9rem;">Temel Bilgiler</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="row g-2">
                                                    <div class="col-lg-5 col-md-12 col-sm-12 mb-2">
                                                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                                                        @if($pageindex==2)
                                                        <input type="hidden" name="takvim_sayfasi" value="1">
                                                        @endif
                                                        <label class="form-label">@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                                                        <div class="d-flex" style="gap:6px;">
                                                            <select name="adsoyad" id="randevuekle_musteri_id" class="form-control opsiyonelSelect musteri_secimi" style="flex:1; height: 32px; font-size: 0.85rem;">
                                                                <option></option>
                                                            </select>
                                                            <button class="btn btn-outline-primary yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal" title="Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29)Danışan @else Müşteri @endif ekle" style="padding: 0 10px; height: 32px; white-space: nowrap;">
                                                                <i class="fa fa-plus"></i> Yeni
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6 col-sm-6 mb-2">
                                                        <label class="form-label">Tarih</label>
                                                        <input required placeholder="Tarih" type="text" class="form-control" name="tarih" id="randevutarihiyeni" autocomplete="off" novalidate value="{{date('Y-m-d')}}" style="height: 32px; font-size: 0.85rem;" />
                                                    </div>
                                                    <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                                                        <label class="form-label">Saat</label>
                                                        <select id='randevu_saat' name="saat" class="form-control" style="height: 32px; font-size: 0.85rem;">
                                                            @for($j = strtotime(date('07:00')) ; $j < strtotime(date('23:15')); $j+=(15*60))
                                                            <option value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hizmetler Bölümü -->
                                        <div class="card mb-2">
                                            <div class="card-header py-1 d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0" style="font-size: 0.9rem;">Hizmetler</h6>
                                                <button type="button" id="bir_hizmet_daha_ekle" class="btn btn-outline-success btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                                    <i class="icon-copy fi-plus"></i> Yeni Hizmet Ekle
                                                </button>
                                            </div>
                                            <div class="card-body p-2 hizmetler_bolumu" style="overflow: visible;">
                                                <!-- Hizmet Satırı 0 -->
                                                <div class="hizmet-satiri card mb-2" data-value="0" style="border: 1px solid #dee2e6;">
                                                    <div class="card-header py-1 d-flex justify-content-between align-items-center" style="padding: 4px 8px; background-color: #f8f9fa;">
                                                        <span class="fw-bold" style="font-size: 0.85rem;">Hizmet #1</span>
                                                        <button type="button" name="hizmet_formdan_sil" data-value="0" class="btn btn-sm btn-danger hizmet-sil" style="padding: 2px 6px; font-size: 0.7rem;" disabled>
                                                            <i class="icon-copy fa fa-trash"></i> Sil
                                                        </button>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <div class="row g-2">
                                                            <!-- Sol kolon: Personel / Yardimci / Cihaz / Oda -->
                                                            <div class="col-md-6">
                                                                <div class="row g-2">
                                                                    <!-- Personel -->
                                                                    <div class="col-12 mb-1 secim-personel" style="{{ $__personel_style }}">
                                                                        <label class="form-label" style="font-size: 0.8rem;">Personel</label>
                                                                        <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect personel_secimi personel-select" data-index="0" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                                                            <option></option>
                                                                        </select>
                                                                    </div>

                                                                    <!-- Yardımcı Personel -->
                                                                    <div class="col-12 mb-1 secim-yardimci" style="{{ $__yardimci_style }}">
                                                                        <label class="form-label" style="font-size: 0.8rem;">Yardımcı Personel</label>
                                                                        <select name="randevuyardimcipersonelleriyeni" id="randevuyardimcipersonelleriyeni_0" multiple class="form-control custom-select2 personel_secimi" data-index="0" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                                                                        </select>
                                                                    </div>

                                                                    <!-- Cihaz -->
                                                                    <div class="col-12 mb-1 secim-cihaz" style="{{ $__cihaz_style }}">
                                                                        <label class="form-label" style="font-size: 0.8rem;">Cihaz</label>
                                                                        <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect cihaz_secimi cihaz-select" data-index="0" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                                                            <option></option>
                                                                        </select>
                                                                    </div>

                                                                    <!-- Oda -->
                                                                    <div class="col-12 mb-1 secim-oda" style="{{ $__oda_style }}">
                                                                        <label class="form-label" style="font-size: 0.8rem;">Oda</label>
                                                                        <select name="randevuodalariyeni[]" class="form-control opsiyonelSelect oda_secimi oda-select" data-index="0" style="width:100%; height: 30px; font-size: 0.8rem;">
                                                                            <option></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Sağ kolon: Hizmet Seçimi -->
                                                            <div class="col-md-6 mb-1">
                                                                <label class="form-label" style="font-size: 0.8rem;">Hizmetler (Çoklu Seçim)</label>
                                                                <select name="randevuhizmetleriyeni_0[]" id="randevuhizmetleriyeni_0" multiple class="form-control hizmet-select" data-index="0" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                                                                    <option></option>
                                                                </select>
                                                            </div>

                                                            <!-- Hizmet Detayları -->
                                                            <div class="col-12 mt-1" id="hizmet-detaylari-0" style="font-size: 0.8rem;">
                                                                <!-- Hizmet detayları dinamik olarak buraya eklenecek -->
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Notlar + Tekrarlayan Randevu yan yana -->
                                        <div class="row g-2">
                                            <div class="col-md-7">
                                                <div class="card mb-2">
                                                    <div class="card-header py-1">
                                                        <h6 class="mb-0" style="font-size: 0.9rem;">Notlar</h6>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <label class="form-label" style="font-size: 0.8rem;">Personel Notu</label>
                                                        <textarea class="form-control" name="personel_notu" placeholder="Randevu ile ilgili notlarınızı buraya yazın..." rows="2" style="min-height: 60px; font-size: 0.85rem;"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="card mb-2">
                                                    <div class="card-header py-1 d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0" style="font-size: 0.9rem;">Tekrarlayan Randevu</h6>
                                                        <span><input class="form-check-input" style="height: 14px; width: 28px;" id="tekrarlayan" name="tekrarlayan" type="checkbox"></span>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <div class="row g-2">
                                                            <div class="col-7">
                                                                <label class="form-label small mb-1" style="font-size: 0.72rem;">Sıklık</label>
                                                                <select class="form-control tekrar_randevu form-control-sm" name="tekrar_sikligi" disabled style="height: 28px; font-size: 0.75rem; padding: 2px 5px;">
                                                                    <option value="+1 day">Her gün</option>
                                                                    <option value="+2 days">2 günde bir</option>
                                                                    <option value="+3 days">3 günde bir</option>
                                                                    <option value="+4 days">4 günde bir</option>
                                                                    <option value="+5 days">5 günde bir</option>
                                                                    <option value="+6 days">6 günde bir</option>
                                                                    <option value="+1 week">Haftada bir</option>
                                                                    <option value="+2 weeks">2 Haftada bir</option>
                                                                    <option value="+3 weeks">3 Haftada bir</option>
                                                                    <option value="+4 weeks">4 Haftada bir</option>
                                                                    <option value="+1 month">Her ay</option>
                                                                    <option value="+45 days">45 günde bir</option>
                                                                    <option value="+2 months">2 ayda bir</option>
                                                                    <option value="+3 months">3 ayda bir</option>
                                                                    <option value="+6 months">6 ayda bir</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-5">
                                                                <label class="form-label small mb-1" style="font-size: 0.72rem;">Tekrar Sayısı</label>
                                                                <input type="tel" name="tekrar_sayisi" class="form-control tekrar_randevu form-control-sm" required value="0" disabled style="height: 28px; font-size: 0.75rem; padding: 2px 5px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Saat Kapama Bölümü -->
                        @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel') && !Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))
                        <div class="tab-pane fade" id="saat-kapama" role="tabpanel">
                            <div class="pd-15">
                                <form id="saat_kapama" method="POST">
                                    <input type="hidden" value="{{$isletme->id}}" name="sube">
                                    {!!csrf_field()!!}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label style="font-size: 0.8rem;">Personel</label>
                                            <select name="personel" class="form-control custom-select2 personel_secimi" style="width: 100%; height: 32px; font-size: 0.85rem;">
                                            <option></option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label style="font-size: 0.8rem;">Tarih</label>
                                            <input type="text" required class="form-control date-picker" name="tarih" value="{{date('Y-m-d')}}" autocomplete="off" style="height: 32px; font-size: 0.85rem;">
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='baslangic_saati_yazi'>
                                            <label style="font-size: 0.8rem;">Başlangıç Saati</label>
                                            <input type="time" class="form-control" name="saat" id='kapama_saat_baslangic' required style="height: 32px; font-size: 0.85rem;">
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='bitis_saati_yazi'>
                                            <label style="font-size: 0.8rem;">Bitiş Saati</label>
                                            <input type="time" class="form-control" name="saat_bitis" id='kapama_saat_bitis' required style="height: 32px; font-size: 0.85rem;">
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                            <label style="font-size: 0.8rem;">Tüm gün</label><br>
                                            <label class="switch" style="transform: scale(0.8);">
                                            <input type="checkbox" name="tum_gun" id="tum_gun">
                                            <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                            <label style="font-size: 0.8rem;">Tekrarlayan</label><br>
                                            <label class="switch" style="transform: scale(0.8);">
                                            <input id="tekrarlayan_saat_kapama" name="tekrarlayan" type="checkbox">
                                            <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                            <label style="font-size: 0.8rem;">Tekrar Sıklığı</label>
                                            <select class="form-control tekrar_saat_kapama" name="tekrar_sikligi" disabled style="height: 32px; font-size: 0.85rem;">
                                                <option value="+1 day">Her gün</option>
                                                <option value="+2 days">2 günde bir </option>
                                                <option value="+3 days">3 günde bir </option>
                                                <option value="+4 days">4 günde bir </option>
                                                <option value="+5 days">5 günde bir </option>
                                                <option value="+6 days">6 günde bir </option>
                                                <option value="+1 week">Haftada bir</option>
                                                <option value="+2 weeks">2 Haftada bir</option>
                                                <option value="+3 weeks">3 Haftada bir</option>
                                                <option value="+4 weeks">4 Haftada bir</option>
                                                <option value="+1 month">Her ay</option>
                                                <option value="+45 days">45 günde bir</option>
                                                <option value="+2 months">2 ayda bir</option>
                                                <option value="+3 months">3 ayda bir</option>
                                                <option value="+6 months">6 ayda bir</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6">
                                            <label style="font-size: 0.8rem;">Tekrar Sayısı</label>
                                            <input type="tel" name="tekrar_sayisi" class="form-control tekrar_saat_kapama" required value="0" disabled style="height: 32px; font-size: 0.85rem;">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label style="font-size: 0.8rem;">Notlar</label>
                                            <textarea name="personel_notu" class="form-control" style="font-size: 0.85rem; min-height: 60px;"></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 8px 16px;">
                @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel') && !Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))
                <button type="submit" form="saat_kapama" class="btn btn-warning btn-sm" id="saat-kapama-kaydet" style="padding: 5px 12px; font-size: 0.85rem; display:none;">
                    <i class="icon-copy fa fa-save"></i> Saat Kapama Kaydet
                </button>
                @endif
                <button type="submit" form="yenirandevuekleform" class="btn btn-success btn-sm" id="randevu-olustur" style="padding: 5px 12px; font-size: 0.85rem;">
                    <i class="icon-copy fa fa-calendar-plus"></i> Randevu Oluştur
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Genel Modal Stilleri */

.select2-container .select2-selection--single {
    height: inherit !important;

}

/* Tom Select ozel stili */
#modal-view-event-add .ts-wrapper {
    min-height: 40px;
}
#modal-view-event-add .ts-wrapper.multi .ts-control {
    min-height: 40px !important;
    padding: 4px 8px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    background: #fff !important;
    flex-wrap: wrap !important;
}
#modal-view-event-add .ts-wrapper.focus .ts-control {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
}
#modal-view-event-add .ts-wrapper.multi .ts-control > .item {
    background: #eef2ff !important;
    color: #4338ca !important;
    border: 1px solid #c7d2fe !important;
    border-radius: 6px !important;
    padding: 3px 26px 3px 10px !important;
    margin: 2px 3px 2px 0 !important;
    font-size: 0.78rem !important;
    position: relative;
}
#modal-view-event-add .ts-wrapper.plugin-remove_button .item .remove {
    color: #6366f1 !important;
    border-left: none !important;
    padding: 0 6px !important;
    line-height: 1 !important;
    font-weight: 700 !important;
}
#modal-view-event-add .ts-wrapper.plugin-remove_button .item .remove:hover {
    background: #6366f1 !important;
    color: #fff !important;
    border-radius: 0 4px 4px 0 !important;
}
/* Global: tum Tom Select dropdown'lar icin beyaz arka plan */
.ts-dropdown, .ts-dropdown-content {
    background: #ffffff !important;
}
#modal-view-event-add .ts-dropdown {
    background: #ffffff !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 8px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
    margin-top: 4px;
    z-index: 100000;
    opacity: 1 !important;
}
#modal-view-event-add .ts-dropdown .option {
    background: #ffffff !important;
    color: #111827 !important;
}
#modal-view-event-add .ts-dropdown .option:hover,
#modal-view-event-add .ts-dropdown .option.active {
    background: #6366f1 !important;
    color: #ffffff !important;
}
#modal-view-event-add .ts-dropdown .option:hover *,
#modal-view-event-add .ts-dropdown .option.active * {
    color: #ffffff !important;
}
#modal-view-event-add .ts-dropdown .active {
    background: #6366f1 !important;
    color: #fff !important;
}
#modal-view-event-add .ts-dropdown .option {
    padding: 8px 12px !important;
}
#modal-view-event-add .ts-dropdown .hy-ts-option {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
#modal-view-event-add .ts-dropdown .hy-ts-ad {
    font-size: 0.85rem;
    font-weight: 500;
    color: #111827;
}
#modal-view-event-add .ts-dropdown .active .hy-ts-ad,
#modal-view-event-add .ts-dropdown .active .hy-ts-kat { color: #fff !important; }
#modal-view-event-add .ts-dropdown .hy-ts-kat {
    font-size: 0.72rem;
    color: #6b7280;
}
#modal-view-event-add .ts-dropdown .no-results {
    padding: 12px;
    color: #6b7280;
    text-align: center;
    font-size: 0.85rem;
}
#modal-view-event-add .ts-wrapper.disabled .ts-control {
    background: #f9fafb !important;
    opacity: 0.7;
}

/* Hizmet select'i icin stabil ve kullanisli multi-select */
#modal-view-event-add .hizmet-select + .select2-container .select2-selection--multiple {
    min-height: 40px !important;
    max-height: 40px !important;
    height: 40px !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    white-space: nowrap !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 4px 30px 4px 6px !important;
    background: #fff;
    box-sizing: border-box;
}
#modal-view-event-add .hizmet-select + .select2-container .select2-selection__rendered {
    display: inline-flex !important;
    flex-wrap: nowrap !important;
    white-space: nowrap !important;
    align-items: center !important;
    height: 100% !important;
    padding: 0 !important;
}
/* Tum chip'ler ve search inline tek satir */
#modal-view-event-add .hizmet-select + .select2-container .select2-selection__choice,
#modal-view-event-add .hizmet-select + .select2-container .select2-search--inline {
    display: inline-flex !important;
    flex-shrink: 0 !important;
    vertical-align: middle !important;
}
/* Scrollbar inceltme */
#modal-view-event-add .hizmet-select + .select2-container .select2-selection--multiple::-webkit-scrollbar {
    height: 4px;
}
#modal-view-event-add .hizmet-select + .select2-container .select2-selection--multiple::-webkit-scrollbar-thumb {
    background: #c7d2fe;
    border-radius: 2px;
}
#modal-view-event-add .hizmet-select + .select2-container--default.select2-container--focus .select2-selection--multiple,
#modal-view-event-add .hizmet-select + .select2-container--default.select2-container--open .select2-selection--multiple {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
}
#modal-view-event-add .hizmet-select + .select2-container .select2-selection__choice {
    background: #eef2ff !important;
    color: #4338ca !important;
    border: 1px solid #c7d2fe !important;
    border-radius: 6px !important;
    padding: 3px 10px 3px 8px !important;
    margin: 2px 3px 2px 0 !important;
    font-size: 0.78rem !important;
    line-height: 1.2 !important;
}
#modal-view-event-add .hizmet-select + .select2-container .select2-selection__choice__remove {
    color: #6366f1 !important;
    margin-right: 4px !important;
    font-weight: 700 !important;
    border-right: none !important;
    padding-right: 4px !important;
}
#modal-view-event-add .hizmet-select + .select2-container .select2-search--inline .select2-search__field {
    min-width: 80px !important;
    margin-top: 3px !important;
    font-size: 0.8rem !important;
}
/* Secim yapildiysa arama kutusunun placeholder'i gizlensin (secim uzerine binmesin) */
#modal-view-event-add .select2-selection--multiple .select2-selection__choice ~ li .select2-search__field::placeholder,
#modal-view-event-add .select2-selection--multiple:has(.select2-selection__choice) .select2-search__field::placeholder {
    color: transparent !important;
}
#modal-view-event-add .select2-selection--multiple:has(.select2-selection__choice) .select2-search__field {
    min-width: 4px !important;
}
#modal-view-event-add .hizmet-select + .select2-container .select2-selection__rendered {
    padding: 0 !important;
}
/* Dropdown option modern */
#modal-view-event-add .hy-secim-option {
    display: flex;
    flex-direction: column;
    padding: 2px 0;
    line-height: 1.3;
}
#modal-view-event-add .hy-secim-option-ad {
    color: #111827;
    font-size: 0.85rem;
    font-weight: 500;
}
#modal-view-event-add .hy-secim-option-kat {
    color: #6b7280;
    font-size: 0.72rem;
    margin-top: 2px;
}
/* Secili satir + hover */
#modal-view-event-add .select2-results__option--highlighted {
    background: #6366f1 !important;
}
#modal-view-event-add .select2-results__option--highlighted .hy-secim-option-ad,
#modal-view-event-add .select2-results__option--highlighted .hy-secim-option-kat {
    color: #fff !important;
}
#modal-view-event-add .select2-results__option[aria-selected=true] {
    background: #f0f4ff !important;
}
#modal-view-event-add .select2-results__option[aria-selected=true] .hy-secim-option-ad {
    color: #4338ca !important;
    font-weight: 600;
}
#modal-view-event-add .select2-dropdown,
body > .select2-container .select2-dropdown {
    border-radius: 8px !important;
    border: 2px solid #e5e7eb !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
}
/* Hizmet dropdown'i modal uzerinde gorunmeli (body'ye eklenir) */
body > .select2-container--open { z-index: 99999 !important; }
/* Clear button'u (x) dikey ortalama, selection alanini buyutmesin */
#modal-view-event-add .hizmet-select + .select2-container .select2-selection__clear {
    position: absolute !important;
    right: 10px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    margin: 0 !important;
    line-height: 1 !important;
    height: auto !important;
    font-size: 18px !important;
}
#modal-view-event-add .select2-search--dropdown .select2-search__field {
    border-radius: 6px !important;
    padding: 6px 10px !important;
    border: 1px solid #d1d5db !important;
}

/* Modal genisligi (1200px max - Bootstrap 4 modal-xl boyutu) */
#modal-view-event-add.modal .modal-dialog,
.modal#modal-view-event-add .modal-dialog {
    max-width: 1200px !important;
    margin: 1.75rem auto !important;
}

.modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
    font-size: 1.5rem;
    padding: 0;
    margin: 0;
    line-height: 1;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-body {
    max-height: 65vh;
    overflow-y: auto;
    padding: 12px;
}
h1, h2 {
    margin: 0;
    padding: 0;
    font-weight: 700;
    color: white;
    font-family: 'Inter', sans-serif;
}
.title h1{
    color:black;
}
.modal-header h4{
    color:white;
}
#odeme_kayit_bolumu h2{
    color:black;
}
.modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 8px 16px;
    background-color: #f8f9fa;
    border-radius: 0 0 8px 8px;
}

/* Paketleri Göster Butonu */
#paketleri-goster-btn {
    margin-right: 8px;
    padding: 4px 10px;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

#paketleri-goster-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(59, 130, 246, 0.2);
}

#paketleri-goster-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Kart Stilleri */
.card {
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
    margin-bottom: 8px;
}

.card:hover {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e3e6f0;
    padding: 6px 10px;
    font-weight: 600;
}

.card-header h6 {
    margin: 0;
    color: #2c3e50;
    font-size: 0.85rem;
}

.card-body {
    padding: 10px;
}

/* Hizmetler bölümü: dropdown'un taşması icin scroll yok, auto yukseklik */
.hizmetler_bolumu {
    height: auto;
    overflow: visible !important;
    padding-right: 4px;
    position: relative;
}
/* Tum nested container'lar overflow:visible - dropdown'lar kirpilmasin */
#modal-view-event-add .card,
#modal-view-event-add .card-body,
#modal-view-event-add .hizmetler_bolumu,
#modal-view-event-add .hizmet-satiri,
#modal-view-event-add .hizmet-satiri .card-body,
#modal-view-event-add .hizmet-satiri .row {
    overflow: visible !important;
}
/* Tom Select dropdown z-index modal uzerinde */
.ts-dropdown { z-index: 100000 !important; }
body > .select2-container--open { z-index: 100001 !important; }

/* Form Element Stilleri */
.form-label {
    font-weight: 500;
    color: #495057;
    font-size: 0.8rem;
    margin-bottom: 3px;
}

.form-control, .custom-select2, .opsiyonelSelect {
    border: 1px solid #d1d3e2;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.form-control:focus, .custom-select2:focus, .opsiyonelSelect:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.2);
    outline: none;
}

textarea.form-control {
    resize: vertical;
    min-height: 60px;
}

/* Switch Toggle Stilleri - Daha küçük */
.form-switch .form-check-input {
    width: 2.2em !important;
    height: 1.1em !important;
    cursor: pointer;
    transform: scale(0.7) !important;
    margin: 0 !important;
}

.form-switch .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.form-switch .form-check-label {
    font-size: 0.75rem;
    color: #495057;
}

.form-switch.d-flex {
    margin-bottom: 0;
}

/* Buton Stilleri */
.btn {
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.2s;
    font-size: 0.85rem;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
}

.btn-outline-primary {
    border-color: #667eea;
    color: #667eea;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
}

.btn-outline-success {
    border-color: #10b981;
    color: #10b981;
}

.btn-outline-success:hover {
    background-color: #10b981;
    color: white;
}

.btn-sm {
    padding: 3px 8px;
    font-size: 0.75rem;
}

/* Hizmet Satırı Stilleri */
.hizmet-satiri {
    border: 1px solid #e9ecef;
    margin-bottom: 8px;
}

.hizmet-satiri .card-header {
    background-color: #f8f9fa;
    padding: 4px 8px;
}

/* Hizmet Detayları */
.hizmet-detay-item {
    background-color: #f8f9fa;
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 6px;
    border: 1px solid #e9ecef;
}

.hizmet-detay-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px dashed #dee2e6;
}

.hizmet-ad {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.8rem;
}

/* Özet Bölümü Stilleri */
#randevu-ozeti {
    min-height: 180px;
}

.ozet-item {
    margin-bottom: 6px;
    padding-bottom: 6px;
    border-bottom: 1px dashed #e9ecef;
}

.ozet-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.ozet-label {
    color: #6c757d;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.ozet-value {
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.8rem;
}

.toplam-tutar {
    font-weight: 700;
    color: #10b981;
    font-size: 0.9rem;
    text-align: right;
    padding: 4px 0;
    border-top: 2px solid #e9ecef;
    margin-top: 6px;
}

/* Tab Stilleri */
.nav-tabs {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 12px;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px 6px 0 0;
    margin-right: 2px;
    font-size: 0.85rem;
}

.nav-tabs .nav-link.active {
    color: #667eea;
    background-color: white;
    border-bottom: 2px solid #667eea;
}

.nav-tabs .nav-link:hover {
    color: #667eea;
    background-color: rgba(102, 126, 234, 0.05);
}

.form-control-sm {
    padding: 3px 6px;
    font-size: 0.75rem;
    height: 28px;
}

/* Footer Butonları */
.modal-footer .btn {
    min-width: 100px;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    border: none;
}

.modal-footer .btn-secondary:hover {
    background-color: #5a6268;
}

.modal-footer .btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border: none;
    color: white;
}

.modal-footer .btn-warning:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
}

/* Responsive Düzenlemeler */
@media (max-width: 992px) {
    .modal-dialog {
        margin: 10px;
    }
    
    .col-md-8, .col-md-4 {
        width: 100%;
        margin-bottom: 8px;
    }
    
    #paketleri-goster-btn {
        margin-top: 8px;
        width: 100%;
        margin-right: 0;
    }
}

@media (max-width: 768px) {
    .modal-dialog {
        margin: 5px;
    }
    
    .modal-body {
        padding: 8px;
    }
    
    .nav-tabs .nav-link {
        padding: 4px 8px;
        font-size: 0.8rem;
    }
    
    .card-body .row > div {
        margin-bottom: 6px;
    }
    
    #paketleri-goster-btn {
        font-size: 0.7rem;
        padding: 3px 8px;
    }
}

/* Scrollbar Özelleştirmesi */
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Select2 Özelleştirmeleri */
.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--multiple {
    border: 1px solid #d1d3e2;
    border-radius: 4px;
    min-height: 30px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    padding-left: 8px;
    font-size: 0.85rem;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #667eea;
    border-color: #5a6fd8;
    color: white;
    border-radius: 3px;
    padding: 1px 6px;
    margin: 2px;
    font-size: 0.75rem;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d3e2;
    border-radius: 3px;
    padding: 4px 8px;
    font-size: 0.85rem;
}

/* Animasyonlar */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

.hizmet-satiri:not(:first-child) {
    animation: fadeIn 0.2s ease-out;
}

/* İkon Stilleri */
.icon-copy {
    margin-right: 4px;
}

/* Badge Stilleri */
.badge {
    font-size: 0.7rem;
    padding: 2px 5px;
    border-radius: 8px;
}

.bg-light-blue {
    background-color: rgba(59, 130, 246, 0.1) !important;
    color: #3b82f6 !important;
}

.bg-light-green {
    background-color: rgba(16, 185, 129, 0.1) !important;
    color: #10b981 !important;
}

/* Notlar kartı */
.card.mb-2:last-child {
    margin-bottom: 0 !important;
}

/* Saat Kapama Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 20px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #667eea;
}

input:focus + .slider {
    box-shadow: 0 0 1px #667eea;
}

input:checked + .slider:before {
    transform: translateX(20px);
}

/* Paket Modal Stilleri */
.paket-item {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
    background-color: #fff;
    font-size: 0.85rem;
}

.paket-item:hover {
    border-color: #4CAF50;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transform: translateY(-1px);
}

.paket-item.active {
    border-color: #4CAF50;
    background-color: #f0fff4;
}

.paket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.paket-ad {
    font-weight: 600;
    font-size: 0.9rem;
    color: #333;
}

.paket-fiyat {
    font-weight: 700;
    color: #4CAF50;
    font-size: 0.9rem;
}

.paket-icerik {
    margin-bottom: 6px;
}

.paket-icerik-item {
    padding: 3px 0;
    border-bottom: 1px dashed #eee;
    font-size: 0.8rem;
}

.paket-icerik-item:last-child {
    border-bottom: none;
}

.paket-durum {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.7rem;
    font-weight: 500;
}

.paket-durum.aktif {
    background-color: #4CAF50;
    color: white;
}

.paket-durum.tukendi {
    background-color: #f44336;
    color: white;
}

.paket-durum.beklemede {
    background-color: #ff9800;
    color: white;
}

.paket-secilen-hizmet {
    background-color: #e8f5e9;
    border-left: 2px solid #4CAF50;
}

/* Tab'a göre buton görünürlüğü */
#saat-kapama.active ~ .modal-footer #randevu-olustur {
    display: none !important;
}

#yeni-randevu.active ~ .modal-footer #saat-kapama-kaydet {
    display: none !important;
}

/* Küçük Hizmet Detayları */
.hizmet-detay-inputs {
    font-size: 0.75rem;
}

.hizmet-detay-inputs .form-control {
    height: 26px;
    padding: 2px 6px;
    font-size: 0.75rem;
}

/* Daha Kompakt Özet */
.compact-summary {
    font-size: 0.8rem;
}

.compact-summary .summary-item {
    padding: 3px 0;
}

.compact-summary .summary-label {
    font-size: 0.7rem;
    color: #6c757d;
}

.compact-summary .summary-value {
    font-size: 0.8rem;
    font-weight: 500;
}

/* Hizmet Kaldırma Butonu */
.hizmet-kaldir {
    padding: 1px 4px !important;
    font-size: 0.7rem !important;
    line-height: 1;
}

/* ==================== KOMPAKT MODAL TASARIMI ==================== */
/* Bulletproof yatay ortalama: flex tabanli, Bootstrap'in scrollbar-telafi
   padding'i sola kaydirmayi engelle */
.randevu-modal-compact.modal {
    padding-right: 0 !important;
    padding-left: 0 !important;
}
/* display:flex Bootstrap default 'display:block'i ezerken modal-dialog
   flex item olarak max-width'i bazen kucultebiliyor. Block birakalim. */
.randevu-modal-compact .modal-dialog {
    margin: 1.75rem auto !important;
    max-width: 1200px !important;
    width: auto !important;
}
body.modal-open {
    padding-right: 0 !important;
}

.randevu-modal-compact .modal-content {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(17, 24, 39, 0.25);
}

.randevu-modal-compact .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 10px 16px;
    border-bottom: none;
    position: relative;
}
.randevu-modal-compact .modal-header::after {
    content: "";
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 3px;
    background: linear-gradient(90deg, #f59e0b, #10b981, #6366f1);
    opacity: 0.85;
}
.randevu-modal-compact .modal-header h4 {
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    margin: 0;
}

.randevu-modal-compact .modal-body {
    padding: 12px 14px 10px !important;
    max-height: 72vh;
}

.randevu-modal-compact .nav-tabs {
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 10px;
    padding-bottom: 0;
    align-items: center;
}

.randevu-modal-compact .nav-tabs .nav-link {
    padding: 6px 14px;
    font-size: 0.82rem;
    font-weight: 500;
    color: #6b7280;
    border-radius: 6px 6px 0 0;
    transition: all 0.15s ease;
}
.randevu-modal-compact .nav-tabs .nav-link.active {
    color: #4f46e5;
    border-bottom: 2px solid #6366f1;
    background: transparent;
}

.randevu-modal-compact #paketleri-goster-btn {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    color: white;
    padding: 5px 12px;
    font-size: 0.75rem;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(14, 165, 233, 0.25);
}
.randevu-modal-compact #paketleri-goster-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
}

.randevu-modal-compact .card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
    margin-bottom: 8px;
    background: #fff;
}

.randevu-modal-compact .card-header {
    background: #f9fafb;
    padding: 6px 10px;
    border-bottom: 1px solid #eef0f3;
}

.randevu-modal-compact .card-header h6 {
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    letter-spacing: 0.1px;
}

.randevu-modal-compact .card-body {
    padding: 8px 10px;
}

.randevu-modal-compact .form-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 3px;
    letter-spacing: 0.1px;
}

.randevu-modal-compact .form-control,
.randevu-modal-compact .opsiyonelSelect {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.82rem;
    transition: all 0.15s ease;
}
.randevu-modal-compact .form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

/* Hizmet Satırı — daha zarif */
.randevu-modal-compact .hizmet-satiri {
    border: 1px solid #e5e7eb !important;
    border-radius: 7px;
    margin-bottom: 8px;
    background: #fff;
    overflow: hidden;
}
.randevu-modal-compact .hizmet-satiri .card-header {
    background: linear-gradient(90deg, #f3f4f6 0%, #fafbfc 100%) !important;
    padding: 5px 10px !important;
    border-bottom: 1px solid #e5e7eb;
}
.randevu-modal-compact .hizmet-satiri .card-header .fw-bold {
    color: #4338ca;
    font-size: 0.8rem;
    font-weight: 600;
}
.randevu-modal-compact .hizmet-satiri .hizmet-sil {
    padding: 2px 8px !important;
    font-size: 0.7rem !important;
    border-radius: 5px;
}

/* Outline buton — Yeni Müşteri vb. */
.randevu-modal-compact .btn-outline-primary {
    border-color: #c7d2fe;
    color: #4f46e5;
    font-size: 0.78rem;
    font-weight: 500;
    border-radius: 6px;
}
.randevu-modal-compact .btn-outline-primary:hover {
    background: #6366f1;
    border-color: #6366f1;
    color: white;
}
.randevu-modal-compact .btn-outline-success {
    border-color: #bbf7d0;
    color: #059669;
    font-size: 0.72rem;
    padding: 3px 10px;
    border-radius: 5px;
}
.randevu-modal-compact .btn-outline-success:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

/* Özet kart — hafif vurgulu kenar */
.randevu-modal-compact .ozet-card {
    border-left: 3px solid #6366f1 !important;
    background: linear-gradient(180deg, #fafbff 0%, #ffffff 100%);
}

.ozet-bos-durum {
    text-align: center;
    padding: 14px 8px;
    color: #9ca3af;
}
.ozet-bos-icon {
    width: 44px;
    height: 44px;
    margin: 0 auto 8px;
    border-radius: 50%;
    background: linear-gradient(135deg, #eef2ff 0%, #f3e8ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a5b4fc;
    font-size: 1.1rem;
}

/* Tekrarlayan — daha temiz */
.randevu-modal-compact #tekrarlayan {
    width: 32px !important;
    height: 18px !important;
    cursor: pointer;
}

/* Modal footer — daha kompakt */
.randevu-modal-compact .modal-footer {
    background: #f9fafb;
    padding: 8px 14px;
    border-top: 1px solid #eef0f3;
}
.randevu-modal-compact .modal-footer .btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    padding: 6px 16px;
    font-size: 0.82rem;
    font-weight: 500;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}
.randevu-modal-compact .modal-footer .btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}

/* Küçük ekranlarda kolonlar birbirinin altina binsin — 2-kolon layout kuvvetlice buyusun */
@media (max-width: 991px) {
    .randevu-modal-compact .modal-dialog {
        max-width: 95% !important;
        margin: 10px auto;
    }
}

/* Icon-copy boyutunu sabitle */
.randevu-modal-compact .icon-copy {
    margin-right: 3px;
}
</style>

<script>
let hizmetSatirSayisi = 1;
let hizmetDataCache = {};
let seciliMusteriId = null;
let musteriPaketleri = [];

// Tom Select uyumlu addServicesToForm — paket popup'tan gelen hizmetleri aktif Tom Select'e ekler
window.addServicesToForm = function(hizmetData, result, showSuccessMessage){
    if(!hizmetData || !hizmetData.length){ return; }
    var $sel = $('#yenirandevuekleform .hizmet-select').first();
    if(!$sel.length) return;
    var el = $sel[0];
    var ts = el.tomselect;
    if(!ts){
        // Tom Select henuz init olmamissa biraz bekle, tekrar dene
        setTimeout(function(){ window.addServicesToForm(hizmetData, result, showSuccessMessage); }, 200);
        return;
    }
    ts.clear(true); // mevcut secimleri sessizce temizle
    var ids = [];
    hizmetData.forEach(function(item){
        if(!item || !item.id) return;
        ts.addOption({
            value: String(item.id),
            text: item.text,
            kategori: item.tur === 'paket' ? ('Paket: ' + (item.paket_adi || '')) : 'Hizmet',
            sure: item.sure || 0,
            fiyat: item.fiyat || 0,
            seans: item.seans,
            paket_adi: item.paket_adi || null,
            tur: item.tur,
            paket_id: item.paket_id || null,
            adisyon_hizmet_id: item.adisyon_hizmet_id || null,
            adisyon_paket_id: item.adisyon_paket_id || null
        });
        // Cache de guncel olsun
        hizmetDataCache[item.id] = {
            id: item.id, text: item.text,
            sure: item.sure || 0, fiyat: item.fiyat || 0,
            kategori: item.tur === 'paket' ? ('Paket: ' + (item.paket_adi || '')) : 'Hizmet',
            renk: item.tur === 'paket' ? '#f59e0b' : '#3b82f6'
        };
        ids.push(String(item.id));
    });
    ts.refreshOptions(false);
    ts.setValue(ids, false); // false = onChange tetikle -> updateHizmetDetaylari + updateRandevuOzeti
    // Soft paket modal'i kapat
    if($('#softPaketSecimModal').length){
        $('#softPaketSecimModal').modal('hide');
        setTimeout(function(){ $('#softPaketSecimModal').remove(); }, 300);
    }
};

// Randevu modali icin: aktif personel + aktif & musait cihaz + aktif & musait oda listeleri
window.randevuModalData = {
    personeller: [
        @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',1)->orderBy('personel_adi','asc')->get() as $p)
            { id: {{ (int)$p->id }}, ad: @json($p->personel_adi) },
        @endforeach
    ],
    cihazlar: [
        @foreach(\App\Cihazlar::where('salon_id',$isletme->id)->where('aktifmi',1)->where('durum',1)->orderBy('cihaz_adi','asc')->get() as $c)
            { id: {{ (int)$c->id }}, ad: @json($c->cihaz_adi) },
        @endforeach
    ],
    odalar: [
        @foreach(\App\Odalar::where('salon_id',$isletme->id)->where('aktifmi',1)->where('durum',1)->orderBy('oda_adi','asc')->get() as $o)
            { id: {{ (int)$o->id }}, ad: @json($o->oda_adi) },
        @endforeach
    ]
};

// Bir select elementine verilen liste ile option'lari doldur (mevcut deger korunur)
function doldurSelect($sel, liste){
    if(!$sel || !$sel.length) return;
    var mevcut = $sel.val();
    $sel.empty().append('<option></option>');
    liste.forEach(function(item){
        $sel.append(new Option(item.ad, item.id, false, false));
    });
    if(mevcut !== null && mevcut !== undefined && mevcut !== '') $sel.val(mevcut);
}

// Tum .personel-select, .cihaz-select, .oda-select ve yardimci personelleri doldur
function doldurRandevuSecenekleri(){
    $('#modal-view-event-add .personel-select, #modal-view-event-add .personel_secimi').each(function(){
        // Hizmet select'ini atla (hizmet-select class'i var)
        if($(this).hasClass('hizmet-select')) return;
        doldurSelect($(this), window.randevuModalData.personeller);
    });
    $('#modal-view-event-add .cihaz-select').each(function(){
        doldurSelect($(this), window.randevuModalData.cihazlar);
    });
    $('#modal-view-event-add .oda-select').each(function(){
        doldurSelect($(this), window.randevuModalData.odalar);
    });
}

$(document).ready(function() {
    // Tab değişimlerini takip et ve butonları göster/gizle — sadece bu modal icinde
    $('#modal-view-event-add').on('shown.bs.tab', 'a[data-toggle="tab"]', function(e) {
        updateFooterButtons();
    });
    $('#modal-view-event-add').on('show.bs.modal', function() {
        // Saat kapama tab'ını kapat, randevu tab'ını aç
        $('.nav-tabs a[href="#yeni-randevu"]').tab('show');
        
        // Footer butonlarını güncelle
        updateFooterButtons();
    });
    // Sayfa yüklendiğinde varsayılan butonu göster
    updateFooterButtons();
    
    function updateFooterButtons() {
        // SADECE bu modal icindeki tab'i kontrol et (ayarlar vs baska tab'lar bu mantigi bozmasin)
        const activeTabId = $('#modal-view-event-add .tab-pane.active').attr('id');
        var $kaydetBtn = $('#modal-view-event-add #saat-kapama-kaydet');
        var $olusturBtn = $('#modal-view-event-add #randevu-olustur');

        if (activeTabId === 'saat-kapama') {
            @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel') && !Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))
            $kaydetBtn.show();
            @endif
            $olusturBtn.hide();
        } else {
            // Varsayilan: yeni-randevu veya bilinmeyen -> randevu olustur goster, kaydet gizle
            $olusturBtn.show();
            $kaydetBtn.hide();
        }
    }
    
    $('#modal-view-event-add').on('show.bs.modal', function() {
        // Varsayılan olarak randevu tab'ı aktif
        $('#randevu-olustur').show();
        $('#saat-kapama-kaydet').hide();

        // Takvim turune gore personel/cihaz/oda secimlerini guncelle
        if (typeof window.randevuSecimleriniGuncelle === 'function') {
            window.randevuSecimleriniGuncelle();
        }
    });

    // Randevu takvim turu degisince modal icindeki secim kolonlarini dinamik olarak guncelle
    // 0: Hizmete Gore (hepsi) | 1: Personele Gore | 2: Cihaza Gore | 3: Odaya Gore
    window.randevuSecimleriniGuncelle = function() {
        var turu = parseInt($('#randevu_ayarina_gore').val());
        if (isNaN(turu)) return;
        var persGizli  = (turu === 2 || turu === 3);
        var cihazGizli = (turu === 1 || turu === 3);
        var odaGizli   = (turu === 1 || turu === 2);
        $('#modal-view-event-add .secim-personel').css('display', persGizli ? 'none' : '');
        $('#modal-view-event-add .secim-cihaz').css('display', cihazGizli ? 'none' : '');
        $('#modal-view-event-add .secim-oda').css('display', odaGizli ? 'none' : '');
        // Yardimci personel her zaman gizli
        $('#modal-view-event-add .secim-yardimci').css('display', 'none');
    };

    $(document).on('change', '#randevu_ayarina_gore', function() {
        if (typeof window.randevuSecimleriniGuncelle === 'function') {
            window.randevuSecimleriniGuncelle();
        }
    });

 let paketKontrolTimeout = null;

$('#randevuekle_musteri_id').on('select2:select', function(e) {
    seciliMusteriId = e.params.data.id;
    $('#paketleri-goster-btn').prop('disabled', false);
    
    // Önceki timeout'u temizle
    if (paketKontrolTimeout) {
        clearTimeout(paketKontrolTimeout);
    }
    
    // Önceki modal varsa kapat
    if ($('#softPaketSecimModal').length) {
        $('#softPaketSecimModal').modal('hide');
        $('#softPaketSecimModal').remove();
    }
    
    // Yeni müşteri için paket kontrolünü geciktir
    paketKontrolTimeout = setTimeout(() => {
        paketKontrolü(seciliMusteriId, false);
    }, 500); // 500ms gecikme
}).on('select2:clear', function() {
    seciliMusteriId = null;
    $('#paketleri-goster-btn').prop('disabled', true);
    musteriPaketleri = [];
    
    // Timeout'u temizle
    if (paketKontrolTimeout) {
        clearTimeout(paketKontrolTimeout);
    }
    
    // AJAX isteğini iptal et
    if (aktifPaketIsteki) {
        aktifPaketIsteki.abort();
    }
    
    // Modal varsa kapat
    if ($('#softPaketSecimModal').length) {
        $('#softPaketSecimModal').modal('hide');
        $('#softPaketSecimModal').remove();
    }
    
    // Müşteri temizlendiğinde hizmetleri de temizle (Tom Select & native)
    $('.hizmet_secimi').each(function() {
        if(this.tomselect){ try { this.tomselect.clear(true); } catch(e){} }
        else $(this).val(null).trigger('change');
    });
    updateRandevuOzeti();
});

    // Paketleri Göster butonu tıklama
    $('#paketleri-goster-btn').on('click', function() {
    if (!seciliMusteriId) return;
    
    // Önceki modal varsa kapat
    if ($('#softPaketSecimModal').length) {
        $('#softPaketSecimModal').modal('hide');
        $('#softPaketSecimModal').remove();
    }
    
    // Önceki timeout'u temizle
    if (paketKontrolTimeout) {
        clearTimeout(paketKontrolTimeout);
    }
    
    paketKontrolü(seciliMusteriId, false);
});

    // Select2 başlatma
    function initSelect2() {
        select2YenidenYukle();
        initHizmetSelect2();
    }
    
    // Hizmet-select'ten secili servisleri [{id, text}] seklinde dondurur (Tom Select veya native)
    // window'a at ki global updateHizmetDetaylari erisebilsin
    window.getHizmetSecimi = function($sel){
        if(!$sel || !$sel.length) return [];
        var el = $sel[0];
        if(el && el.tomselect){
            var ts = el.tomselect;
            return ts.items.map(function(id){
                var opt = ts.options[id];
                return { id: id, text: opt ? opt.text : '' };
            });
        }
        var vals = $sel.val() || [];
        if(!Array.isArray(vals)) vals = vals ? [vals] : [];
        return vals.map(function(id){
            var txt = $sel.find('option[value="'+id+'"]').text();
            return { id: id, text: txt };
        });
    };
    var getHizmetSecimi = window.getHizmetSecimi;

    // ===================== Tom Select ile Hizmet Secimi =====================
    // Select2 yerine Tom Select kullanilir — modal scroll/pozisyon problemi olmaz.

    // Takvim turu: 0=Hizmete gore, 1=Personele gore, 2=Cihaza gore, 3=Odaya gore
    window.randevuTakvimTuru = {{ (int)($isletme->randevu_takvim_turu ?? 0) }};

    // Client-side hizmet cache: modal acilisinda bir kez doldurulur
    window.randevuHizmetVerisi = null;
    window._randevuHizmetVerisiPending = null;

    function fetchRandevuHizmetVerisi(onReady){
        if(window.randevuHizmetVerisi){ if(onReady) onReady(); return; }
        if(window._randevuHizmetVerisiPending){
            if(onReady) window._randevuHizmetVerisiPending.push(onReady);
            return;
        }
        window._randevuHizmetVerisiPending = onReady ? [onReady] : [];
        var bitir = function(){
            var pending = window._randevuHizmetVerisiPending || [];
            window._randevuHizmetVerisiPending = null;
            pending.forEach(function(fn){ try { if(fn) fn(); } catch(e){} });
        };
        $.ajax({
            url: '/isletmeyonetim/randevu-modal-hizmet-verisi',
            type: 'GET',
            dataType: 'json',
            data: { sube: '{{$isletme->id}}' },
            success: function(resp){
                window.randevuHizmetVerisi = {
                    tum: (resp && resp.tum_hizmetler) ? resp.tum_hizmetler : [],
                    personel: (resp && resp.personel_hizmet_map) ? resp.personel_hizmet_map : {},
                    cihaz: (resp && resp.cihaz_hizmet_map) ? resp.cihaz_hizmet_map : {}
                };
                window.randevuHizmetVerisi.tum.forEach(function(h){
                    hizmetDataCache[h.id] = {
                        id: h.id, text: h.ad,
                        sure: h.sure || 0, fiyat: h.fiyat || 0,
                        kategori: h.kategori || '', renk: h.renk || '#6366f1'
                    };
                });
                bitir();
            },
            error: function(){
                console.warn('Hizmet verisi yuklenemedi');
                bitir();
            }
        });
    }

    // Client-side filtreleme (personel/cihaz bazli)
    function filtrelenmisHizmetler(personelId, cihazId){
        var v = window.randevuHizmetVerisi;
        if(!v) return { liste: [], fallback: false };
        var izinli = null;
        var hp = personelId ? (v.personel[personelId] || null) : null;
        var hc = cihazId ? (v.cihaz[cihazId] || null) : null;
        if(hp && hp.length) izinli = hp.slice();
        if(hc && hc.length) izinli = (izinli ? izinli : []).concat(hc);
        if(izinli && izinli.length){
            izinli = Array.from(new Set(izinli.map(String)));
            return { liste: v.tum.filter(function(h){ return izinli.indexOf(String(h.id)) > -1; }), fallback: false };
        }
        return { liste: v.tum.slice(), fallback: !!(personelId || cihazId) };
    }

    // Her hizmet-select icin TomSelect instance'i sakla (data-index -> instance)
    window.hizmetTomInstances = window.hizmetTomInstances || {};

    function tomDestroyHizmet($sel){
        var el = $sel[0];
        if(!el) return;
        if(el.tomselect){
            try { el.tomselect.destroy(); } catch(e){}
        }
    }

    // Hizmet select'ini Tom Select ile baslat
    function initHizmetTom($sel, placeholder){
        tomDestroyHizmet($sel);
        var el = $sel[0];
        var ph = placeholder || 'Önce personel veya cihaz seçin...';
        var ts = new TomSelect(el, {
            plugins: ['remove_button'],
            placeholder: ph,
            allowEmptyOption: true,
            persist: false,
            maxOptions: null,
            hideSelected: true,
            closeAfterSelect: false,
            searchField: ['text', 'kategori'],
            render: {
                option: function(data, escape){
                    var kat = data.kategori ? '<div class="hy-ts-kat">' + escape(data.kategori) + '</div>' : '';
                    return '<div class="hy-ts-option"><div class="hy-ts-ad">' + escape(data.text) + '</div>' + kat + '</div>';
                },
                item: function(data, escape){
                    return '<div>' + escape(data.text) + '</div>';
                },
                no_results: function(){
                    return '<div class="no-results">Hizmet bulunamadı</div>';
                }
            },
            onChange: function(val){
                var idx = $sel.data('index');
                updateHizmetDetaylari(idx);
                updateRandevuOzeti();
            }
        });
        window.hizmetTomInstances[$sel.data('index')] = ts;
        return ts;
    }

    // Bir select'i verilen hizmet listesiyle doldur (Tom Select)
    function doldurHizmetTom($hizmet, liste, placeholder){
        var idx = $hizmet.data('index');
        var secili = $hizmet.val() || [];
        if(!Array.isArray(secili)) secili = secili ? [secili] : [];

        // Native select'e option'lari koy (form submit icin gerekli)
        tomDestroyHizmet($hizmet);
        $hizmet.empty();
        liste.forEach(function(h){
            $hizmet.append(new Option(h.ad, h.id, false, false));
        });

        var ts = initHizmetTom($hizmet, placeholder);

        // Option'lara meta (kategori, sure, fiyat) ekle — updateHizmetDetaylari bunlari kullanir
        liste.forEach(function(h){
            if(ts.options[h.id]){
                ts.options[h.id].kategori = h.kategori || '';
                ts.options[h.id].sure = h.sure || 0;
                ts.options[h.id].fiyat = h.fiyat || 0;
            }
            // hizmetDataCache'i guncel tut ki updateHizmetDetaylari dogru gostersin
            hizmetDataCache[h.id] = {
                id: h.id, text: h.ad,
                sure: h.sure || 0, fiyat: h.fiyat || 0,
                kategori: h.kategori || '', renk: h.renk || '#6366f1'
            };
        });
        ts.refreshOptions(false);

        // Onceki secimleri koru (yeni listede varsa)
        var korunan = secili.filter(function(id){
            return liste.some(function(h){ return String(h.id) === String(id); });
        });
        if(korunan.length) ts.setValue(korunan, true); // silent
    }

    // Hizmet secimini AJAX cache'e gore yukle
    function yukleHizmetler($hizmet, params){
        params = params || {};
        var fn = function(){
            if(params.hepsi){
                doldurHizmetTom($hizmet, window.randevuHizmetVerisi.tum, 'Tüm hizmetler');
                return;
            }
            var personelId = params.personel_id || '';
            var cihazId = params.cihaz_id || '';
            var f = filtrelenmisHizmetler(personelId, cihazId);
            var ph = f.liste.length
                ? (f.fallback ? 'Tüm hizmetler' : 'Hizmet seçin...')
                : 'Hizmet bulunamadı';
            doldurHizmetTom($hizmet, f.liste, ph);
        };
        if(!window.randevuHizmetVerisi) fetchRandevuHizmetVerisi(fn); else fn();
    }

    // Tum hizmet-select'leri Tom Select ile baslat (takvim turune gore davran)
    function initHizmetSelect2() {
        var t = window.randevuTakvimTuru;

        // Ilk kurulum: placeholder'li bos Tom Select
        $('.hizmet-select').each(function(){
            var $s = $(this);
            var ph;
            if(t === 0 || t === 3) ph = 'Hizmet seçin...';
            else if(t === 1) ph = 'Önce personel seçin...';
            else if(t === 2) ph = 'Önce cihaz seçin...';
            else ph = 'Hizmet seçin...';
            tomDestroyHizmet($s);
            $s.empty();
            initHizmetTom($s, ph);
        });

        // Hizmete gore (0) / Odaya gore (3): tum hizmetler yuklensin
        if(t === 0 || t === 3){
            $('.hizmet-select').each(function(){
                yukleHizmetler($(this), { hepsi: 1 });
            });
            return;
        }

        // Personele gore (1) / Cihaza gore (2): secim degisince filtrele
        $(document).off('change.hizmetLoad', 'select[name="randevupersonelleriyeni[]"], select[name^="randevucihazlariyeni"]')
            .on('change.hizmetLoad', 'select[name="randevupersonelleriyeni[]"], select[name^="randevucihazlariyeni"]', function(){
                var $row = $(this).closest('.hizmet-satiri');
                var personelId = $row.find('select[name="randevupersonelleriyeni[]"]').val() || '';
                var cihazId = $row.find('select[name^="randevucihazlariyeni"]').val() || '';
                var $hizmet = $row.find('.hizmet-select');
                if(!$hizmet.length) return;

                if(personelId === '' && cihazId === ''){
                    tomDestroyHizmet($hizmet);
                    $hizmet.empty();
                    initHizmetTom($hizmet, t === 1 ? 'Önce personel seçin...' : 'Önce cihaz seçin...');
                    return;
                }
                yukleHizmetler($hizmet, { personel_id: personelId, cihaz_id: cihazId });
            });
    }
    
    // Tarih seçiciyi başlat
    $('#randevutarihiyeni').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        language: 'tr'
    });
    
    // Tekrarlayan randevu ayarları
    $('#tekrarlayan').on('change', function() {
        $('.tekrar_randevu').prop('disabled', !$(this).is(':checked'));
        if ($(this).is(':checked')) {
            $('.tekrar_randevu').removeClass('text-muted');
        } else {
            $('.tekrar_randevu').addClass('text-muted');
        }
    });
    
    // Hizmet kaldırma butonu
    $(document).on('click', '.hizmet-kaldir', function() {
        const index = $(this).data('index');
        const serviceId = $(this).data('service-id');
        const selectElement = $(`.hizmet-select[data-index="${index}"]`);
        var ts = selectElement[0] ? selectElement[0].tomselect : null;
        if(ts){
            ts.removeItem(String(serviceId));
        } else {
            // Fallback: native select
            var vals = (selectElement.val() || []).filter(v => String(v) != String(serviceId));
            selectElement.val(vals).trigger('change');
        }
        updateHizmetDetaylari(index);
        updateRandevuOzeti();
    });
    
    // Süre veya fiyat değiştiğinde toplamı güncelle
    $(document).on('input', '.hizmet-suresi, .hizmet-fiyati', function() {
        updateRandevuOzeti();
    });
    
    // Yeni hizmet satırı ekle
    $(document).on('click', '#bir_hizmet_daha_ekle', function(e) {
        e.preventDefault();
        
        $("select.custom-select2").each(function(i) {
            $(this).removeAttr('data-select2-id').removeAttr('id');
            $(this).find('option').removeAttr('data-select2-id');
            $(this).select2({width: '100%'});
        });
        
        $("select.opsiyonelSelect").each(function(i) {
            $(this).removeAttr('data-select2-id').removeAttr('id');
            $(this).find('option').removeAttr('data-select2-id');
            $(this).select2({
                placeholder: "Seçiniz",
                allowClear: true,
            });
        });
        
        const newIndex = hizmetSatirSayisi;


        const newRow = `
            <div class="hizmet-satiri card mb-2" data-value="${newIndex}" style="border: 1px solid #dee2e6;">
                <div class="card-header py-1 d-flex justify-content-between align-items-center" style="padding: 4px 8px; background-color: #f8f9fa;">
                    <span class="fw-bold" style="font-size: 0.85rem;">Hizmet #${newIndex + 1}</span>
                    <button type="button" name="hizmet_formdan_sil" data-value="${newIndex}" class="btn btn-sm btn-danger hizmet-sil" style="padding: 2px 6px; font-size: 0.7rem;">
                        <i class="icon-copy fa fa-trash"></i> Sil
                    </button>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <!-- Sol kolon: Personel / Yardimci / Cihaz / Oda -->
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-12 mb-1 secim-personel" style="{{ $__personel_style }}">
                                    <label class="form-label" style="font-size: 0.8rem;">Personel</label>
                                    <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect personel_secimi personel-select" data-index="${newIndex}" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="col-12 mb-1 secim-yardimci" style="{{ $__yardimci_style }}">
                                    <label class="form-label" style="font-size: 0.8rem;">Yardımcı Personel</label>
                                    <select name="randevuyardimcipersonelleriyeni" id="randevuyardimcipersonelleriyeni_${newIndex}" multiple class="form-control custom-select2 personel_secimi" data-index="${newIndex}" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                                    </select>
                                </div>
                                <div class="col-12 mb-1 secim-cihaz" style="{{ $__cihaz_style }}">
                                    <label class="form-label" style="font-size: 0.8rem;">Cihaz</label>
                                    <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect cihaz_secimi cihaz-select" data-index="${newIndex}" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="col-12 mb-1 secim-oda" style="{{ $__oda_style }}">
                                    <label class="form-label" style="font-size: 0.8rem;">Oda</label>
                                    <select name="randevuodalariyeni[]" class="form-control opsiyonelSelect oda_secimi oda-select" data-index="${newIndex}" style="width:100%; height: 30px; font-size: 0.8rem;">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Sağ kolon: Hizmet Seçimi -->
                        <div class="col-md-6 mb-1">
                            <label class="form-label" style="font-size: 0.8rem;">Hizmetler (Çoklu Seçim)</label>
                            <select name="randevuhizmetleriyeni_${newIndex}[]" id="randevuhizmetleriyeni_${newIndex}" multiple class="form-control hizmet-select" data-index="${newIndex}" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                                <option></option>
                            </select>
                        </div>
                        <!-- Hizmet Detaylari -->
                        <div class="col-12 mt-1" id="hizmet-detaylari-${newIndex}" style="font-size: 0.8rem;">
                            <!-- Hizmet detayları dinamik olarak buraya eklenecek -->
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-check form-switch mt-1 d-flex align-items-center justify-content-start">
                                <input class="form-check-input birlestir-checkbox" type="checkbox" name="birlestir" id="customCheck${newIndex}" style="height: 14px; width: 28px;"/>
                                <label class="form-check-label ms-1" for="customCheck${newIndex}" style="font-size: 0.75rem; margin-left:5px">Üsttekiyle Birleştir</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('.hizmetler_bolumu').append(newRow);

        // Yeni satirdaki personel/cihaz/oda secimlerini aktif+musait olanlarla doldur
        doldurRandevuSecenekleri();

        // Yeni eklenen satirdaki personel/cihaz/oda gorunurlugunu takvim turune gore ayarla
        if (typeof window.randevuSecimleriniGuncelle === 'function') {
            window.randevuSecimleriniGuncelle();
        }

        // SADECE yeni satirdaki hizmet-select'i Tom Select ile init et (eski satirlarin secimleri kaybolmasin)
        var $yeniSatir = $('.hizmet-satiri').last();
        $yeniSatir.find('.hizmet-select').each(function(){
            var $s = $(this);
            var t = window.randevuTakvimTuru;
            var ph = (t === 1) ? 'Önce personel seçin...' : (t === 2 ? 'Önce cihaz seçin...' : 'Hizmet seçin...');
            tomDestroyHizmet($s);
            $s.empty();
            initHizmetTom($s, ph);
            if(t === 0 || t === 3){
                yukleHizmetler($s, { hepsi: 1 });
            }
        });
        // Select2'leri sadece yeni satir icin init et
        $yeniSatir.find('.opsiyonelSelect').each(function(){
            try { $(this).select2({ placeholder: 'Seçiniz', allowClear: true }); } catch(e){}
        });
        $yeniSatir.find('.custom-select2').not('.hizmet-select').each(function(){
            try { $(this).select2({ width: '100%' }); } catch(e){}
        });
        $('.hizmet-sil[data-value="0"]').removeAttr('disabled');
        
        hizmetSatirSayisi++;
        updateRandevuOzeti();
        
        // Yeni satir eklendiginde sayfayi oraya kaydir
        setTimeout(function() {
            var $last = $('.hizmet-satiri').last();
            if($last.length && $last[0].scrollIntoView){
                $last[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
        // select2YenidenYukle() cagrilmiyor - eski satirlarin secimlerini sifirlar
    });
    
    // Hizmet satırını sil
    $(document).on('click', '.hizmet-sil', function() {
        const index = $(this).data('value');
        
        if (index === 0) {
            $('.hizmet-satiri[data-value="0"]').find('select').val(null).trigger('change');
            $('.hizmet-satiri[data-value="0"]').find('input[type="number"], textarea').val('');
            $('#hizmet-detaylari-0').empty();
            $('.hizmet-sil[data-value="0"]').attr('disabled', true);
            $('.birlestir-checkbox[data-index="0"]').prop('disabled', true);
        } else {
            $(this).closest('.hizmet-satiri').remove();
            
            if ($('.hizmet-satiri').length === 1) {
                $('.hizmet-sil:first').attr('disabled', true);
            }
            
            //reorganizeRowIndexes();
        }
        
        updateRandevuOzeti();
    });
    
    // Satır indekslerini yeniden düzenle
    function reorganizeRowIndexes() {
        hizmetSatirSayisi = 0;
        $('.hizmet-satiri').each(function(index) {
            $(this).attr('data-value', index);
            $(this).find('.card-header span').text(`Hizmet #${index + 1}`);
            $(this).find('.personel-select').attr('data-index', index);
            $(this).find('.yardimci-personel-select').attr('data-index', index);
            $(this).find('.cihaz-select').attr('data-index', index);
            $(this).find('.hizmet-select').attr('data-index', index);
            $(this).find('.oda-select').attr('data-index', index);
            $(this).find('.hizmet-sil').attr('data-value', index);
            
            $(this).find('.personel-select').attr('name', 'randevupersonelleriyeni[]');
            $(this).find('.yardimci-personel-select').attr('name', 'randevuyardimcipersonelleriyeni');
            $(this).find('.cihaz-select').attr('name', 'randevucihazlariyeni[]');
            $(this).find('.hizmet-select').attr('name', 'randevuhizmetleriyeni');
            $(this).find('.oda-select').attr('name', 'randevuodalariyeni[]');
            
            const detayContainer = $(this).find('[id^="hizmet-detaylari-"]');
            detayContainer.attr('id', 'hizmet-detaylari-' + index);
            
            $(this).find('input.hizmet-suresi').each(function() {
                const oldName = $(this).attr('name');
                const newName = oldName.replace(/\[(\d+)\]/, '[' + index + ']');
                $(this).attr('name', newName);
            });
            
            $(this).find('input.hizmet-fiyati').each(function() {
                const oldName = $(this).attr('name');
                const newName = oldName.replace(/\[(\d+)\]/, '[' + index + ']');
                $(this).attr('name', newName);
            });
            
            hizmetSatirSayisi++;
        });
    }
    
    // Form gönderilmeden önce toplam süre ve fiyatları hesapla
    $('#yenirandevuekleform').on('submit', function(e) {
        e.preventDefault();
        
        let totalFormDuration = 0;
        let totalFormPrice = 0;
        let hasServices = false;
        let hizmetDetaylari = [];
        
        $('.hizmet-satiri').each(function(index) {
            const selectedServices = getHizmetSecimi($(this).find('.hizmet-select'));
            
            if (selectedServices && selectedServices.length > 0) {
                hasServices = true;
                
                $(this).find('.hizmet-suresi').each(function() {
                    totalFormDuration += parseFloat($(this).val()) || 0;
                });
                
                $(this).find('.hizmet-detay-item').each(function() {
                    const serviceName = $(this).find('.hizmet-ad').text();
                    const sure = $(this).find('.hizmet-suresi').val();
                    const fiyat = $(this).find('.hizmet-fiyati').val();
                    
                    totalFormPrice += parseFloat(fiyat) || 0;
                    
                    hizmetDetaylari.push({
                        ad: serviceName,
                        sure: sure,
                        fiyat: fiyat
                    });
                });
            }
        });
        
        if (!hasServices) {
            Swal.fire({
                icon: 'warning',
                title: 'Uyarı',
                text: 'Lütfen en az bir hizmet seçiniz.',
                confirmButtonText: 'Tamam',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        
        if (!seciliMusteriId) {
            Swal.fire({
                icon: 'warning',
                title: 'Uyarı',
                text: 'Lütfen bir müşteri seçiniz.',
                confirmButtonText: 'Tamam',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        
        $(this).append(`
            <input type="hidden" name="toplam_sure" value="${totalFormDuration}">
            <input type="hidden" name="toplam_fiyat" value="${totalFormPrice.toFixed(2)}">
            <input type="hidden" name="hizmet_detaylari" value='${JSON.stringify(hizmetDetaylari)}'>
            <input type="hidden" name="musteri_id" value="${seciliMusteriId}">
        `);
        
        Swal.fire({
            title: 'Randevu Oluşturuluyor',
            html: `
                <div class="text-start">
                    <div class="mb-2">
                        <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Randevu Özeti:</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Toplam Hizmet:</span>
                            <span class="fw-bold">${hizmetDetaylari.length} adet</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Toplam Süre:</span>
                            <span class="fw-bold">${totalFormDuration} dakika</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <span>Toplam Tutar:</span>
                            <span class="fw-bold text-success" style="font-size: 0.95rem;">${totalFormPrice.toFixed(2)} ₺</span>
                        </div>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-calendar-plus"></i> Randevuyu Oluştur',
            cancelButtonText: '<i class="fa fa-times"></i> İptal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            reverseButtons: true,
            showCloseButton: true,
            width: '450px'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).find('button[type="submit"]').html('<i class="fa fa-spinner fa-spin"></i> Oluşturuluyor...').prop('disabled', true);
                
                setTimeout(() => {
                    $(this).unbind('submit').submit();
                }, 500);
            }
        });
        
        return false;
    });
    
    // Modal kapatıldığında formu temizle
    $('#randevu_modal_kapat').on('click', function() {
        resetForm();
    });
    
   
    
    // Başlangıçta select2'leri başlat
    initSelect2();
    updateRandevuOzeti();
    
    // Demo AJAX endpoint simülasyonu - Hizmetler
    $.mockjax({
        url: '/api/hizmet-ara',
        responseTime: 300,
        response: function(settings) {
            const searchTerm = settings.data.search || '';
            const page = settings.data.page || 1;
            const limit = 20;
            
            const demoHizmetler = [
                {id: '1', ad: 'Saç Kesimi', sure: 30, fiyat: 50, kategori: 'Kuaför', renk: '#007bff'},
                {id: '2', ad: 'Saç Boyama', sure: 60, fiyat: 120, kategori: 'Kuaför', renk: '#dc3545'},
                {id: '3', ad: 'Cilt Bakımı', sure: 45, fiyat: 80, kategori: 'Estetik', renk: '#28a745'},
                {id: '4', ad: 'Masaj', sure: 60, fiyat: 100, kategori: 'Spa', renk: '#ffc107'},
                {id: '5', ad: 'Manikür', sure: 30, fiyat: 40, kategori: 'El & Ayak Bakımı', renk: '#17a2b8'},
                {id: '6', ad: 'Pedikür', sure: 45, fiyat: 60, kategori: 'El & Ayak Bakımı', renk: '#6f42c1'},
                {id: '7', ad: 'Epilasyon', sure: 30, fiyat: 70, kategori: 'Estetik', renk: '#e83e8c'},
                {id: '8', ad: 'Kaş Şekillendirme', sure: 15, fiyat: 25, kategori: 'Estetik', renk: '#fd7e14'},
                {id: '9', ad: 'Kirpik Lifting', sure: 60, fiyat: 150, kategori: 'Estetik', renk: '#20c997'},
                {id: '10', ad: 'Makyaj', sure: 45, fiyat: 90, kategori: 'Makyaj', renk: '#6610f2'}
            ];
            
            let filteredResults = demoHizmetler;
            if (searchTerm) {
                const searchLower = searchTerm.toLowerCase();
                filteredResults = demoHizmetler.filter(hizmet => 
                    hizmet.ad.toLowerCase().includes(searchLower) || 
                    hizmet.kategori.toLowerCase().includes(searchLower)
                );
            }
            
            const startIndex = (page - 1) * limit;
            const endIndex = startIndex + limit;
            const paginatedResults = filteredResults.slice(startIndex, endIndex);
            
            const formattedResults = paginatedResults.map(hizmet => ({
                id: hizmet.id,
                text: hizmet.ad,
                sure: hizmet.sure,
                fiyat: hizmet.fiyat,
                kategori: hizmet.kategori,
                renk: hizmet.renk
            }));
            
            this.responseText = {
                results: formattedResults,
                has_more: filteredResults.length > endIndex,
                total_count: filteredResults.length
            };
        }
    });
    
    // Demo AJAX endpoint simülasyonu - Müşteri arama
    $.mockjax({
        url: '/api/musteri-ara',
        responseTime: 300,
        response: function(settings) {
            const searchTerm = settings.data.search || '';
            const page = settings.data.page || 1;
            
            const demoMusteriler = [
                {id: '1', text: 'Ahmet Yılmaz', telefon: '555-123-4567', email: 'ahmet@example.com'},
                {id: '2', text: 'Ayşe Demir', telefon: '555-234-5678', email: 'ayse@example.com'},
                {id: '3', text: 'Mehmet Kaya', telefon: '555-345-6789', email: 'mehmet@example.com'},
                {id: '4', text: 'Fatma Şahin', telefon: '555-456-7890', email: 'fatma@example.com'},
                {id: '5', text: 'Ali Çelik', telefon: '555-567-8901', email: 'ali@example.com'}
            ];
            
            let filteredResults = demoMusteriler;
            if (searchTerm) {
                const searchLower = searchTerm.toLowerCase();
                filteredResults = demoMusteriler.filter(musteri => 
                    musteri.text.toLowerCase().includes(searchLower) ||
                    musteri.telefon.includes(searchTerm)
                );
            }
            
            this.responseText = {
                results: filteredResults,
                has_more: false
            };
        }
    });
    
    // Tekrarlayan saat kapama ayarları
    $('#tekrarlayan_saat_kapama').on('change', function() {
        $('.tekrar_saat_kapama').prop('disabled', !$(this).is(':checked'));
    });
    
    // Tüm gün seçeneği
    $('#tum_gun').on('change', function() {
        if ($(this).is(':checked')) {
            $('#kapama_saat_baslangic, #kapama_saat_bitis').prop('disabled', true).val('');
        } else {
            $('#kapama_saat_baslangic, #kapama_saat_bitis').prop('disabled', false);
        }
    });
    
    // Tarih seçici için
    $('.date-picker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        language: 'tr'
    });
    
    // Hizmet kaldırma butonu (ikinci handler)
    $(document).on('click', '.hizmet-kaldir', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        const serviceId = $(this).data('service-id');
        const selectElement = $(`.hizmet-select[data-index="${index}"]`);
        var ts = selectElement[0] ? selectElement[0].tomselect : null;
        if(ts){
            ts.removeItem(String(serviceId));
        } else {
            var currentSelections = getHizmetSecimi(selectElement)
                .filter(item => item && item.id && item.id.toString() !== serviceId.toString());
            selectElement.val(currentSelections.map(i => i.id)).trigger('change');
        }
        updateHizmetDetaylari(index);
        updateRandevuOzeti();
    });
    
    // Modal kapatıldığında formu temizle
    $('#modal-view-event-add').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Modal açıldığında select2'leri yeniden başlat
    $('#modal-view-event-add').on('shown.bs.modal', function() {
        // Guvence: eger hizmet-select'e yanlislikla Select2 eklenmisse onu da destroy et
        $('.hizmet-select').each(function(){
            if($(this).hasClass('select2-hidden-accessible')){
                try { $(this).select2('destroy'); } catch(e){}
            }
            // Select2 tarafindan eklenen container'i da temizle (double dropdown'a karsi)
            $(this).next('.select2-container').remove();
        });
        try { $('.custom-select2').not('.hizmet-select').select2('destroy'); } catch(e){}
        try { $('.opsiyonelSelect').not('.hizmet-select').select2('destroy'); } catch(e){}

        hizmetDataCache = {};
        window.randevuHizmetVerisi = null;
        window._randevuHizmetVerisiPending = null;

        // Personel/cihaz/oda secimlerini aktif + musait olanlarla doldur
        doldurRandevuSecenekleri();

        // Hizmet verisini paralel olarak cek
        fetchRandevuHizmetVerisi();

        setTimeout(() => {
            initSelect2();
            updateRandevuOzeti();
            // Tab durumuna gore butonlari guncelle (varsayilan: Randevu tab -> kaydet gizli)
            if (typeof updateFooterButtons === 'function') updateFooterButtons();
        }, 100);
    });

});

// Hizmet detaylarını güncelle
function updateHizmetDetaylari(index) {
    const selectElement = $(`.hizmet-select[data-index="${index}"]`);
    const selectedServices = getHizmetSecimi(selectElement);
    const container = $(`#hizmet-detaylari-${index}`);

    container.empty();

    // Sadece id'si olan secimleri al (text bossa cache'ten al)
    const validServices = selectedServices
        .filter(service => service && service.id)
        .map(function(service){
            var cached = hizmetDataCache[service.id];
            var text = (service.text && String(service.text).trim()) || (cached ? cached.text : '') || String(service.id);
            return Object.assign({}, service, { text: text });
        });

    validServices.forEach((service, serviceIndex) => {
        const cachedData = hizmetDataCache[service.id] || service;
        const sure = cachedData.sure || 0;
        const fiyat = cachedData.fiyat || 0;
        const renk = cachedData.renk || '#007bff';
        const serviceText = cachedData.text || service.text || '';
        const checkboxDisabled = serviceIndex === 0 ? 'disabled' : '';
        
        const birlestirNum = serviceIndex + 1;
        const birlestirDisabled = serviceIndex === 0 ? 'disabled' : '';
        const hizmetDetayHtml = `
            <div class="hizmet-detay-item">
                <div class="hizmet-detay-header" style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                    <span class="hizmet-ad" style="color: ${renk}; font-size: 0.8rem;">${serviceText}</span>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <label style="font-size:0.72rem; margin:0; display:flex; align-items:center; gap:4px; ${serviceIndex === 0 ? 'opacity:0.4;' : ''}">
                            <input type="checkbox" name="birlestir${birlestirNum}" ${birlestirDisabled} style="margin:0;">
                            Üstteki ile birleştir
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-danger hizmet-kaldir" data-index="${index}" data-service-id="${service.id}" style="padding: 1px 4px; font-size: 0.7rem;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="row g-1 hizmet-detay-inputs">
                    <div class="col-md-6">
                        <label class="form-label small" style="font-size: 0.7rem;">Süre (dakika)</label>
                        <input type="number" 
                               class="form-control form-control-sm hizmet-suresi" 
                               name="hizmet_sureleri-${service.id}" 
                               value="${sure}" 
                               min="0" 
                               step="5"
                               style="height: 26px; padding: 2px 6px; font-size: 0.75rem;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small" style="font-size: 0.7rem;">Fiyat (₺)</label>
                        <input type="text"
                               inputmode="decimal"
                               class="form-control form-control-sm hizmet-fiyati hy-fiyat-input"
                               name="hizmet_fiyatlari-${service.id}"
                               value="${fiyat}"
                               placeholder="0,00"
                               autocomplete="off"
                               style="height: 26px; padding: 2px 6px; font-size: 0.75rem;">
                    </div>
                </div>
            </div>
        `;
        
        container.append(hizmetDetayHtml);
    });
    
    if (validServices.length > 0) {
        const validIds = validServices.map(s => s.id);
        selectElement.val(validIds).trigger('change');
        
        if (index > 0) {
            $(`.birlestir-checkbox[data-index="${index}"]`).prop('disabled', false);
        }
    } else {
        $(`.birlestir-checkbox[data-index="${index}"]`).prop('disabled', true);
    }
    
    if (index === 0) {
        const firstRowHasServices = validServices.length > 0;
        $('.hizmet-sil[data-value="0"]').prop('disabled', !firstRowHasServices);
    }
}

// Randevu özetini güncelle
function updateRandevuOzeti() {
    let totalFormDuration = 0;
    let totalFormPrice = 0;
    let serviceCount = 0;
    let uniqueHizmetler = new Set();
    let totalSureByService = {};
    
    $('.hizmet-satiri').each(function() {
        const selectedServices = getHizmetSecimi($(this).find('.hizmet-select'));

        if (selectedServices && selectedServices.length > 0) {
            $(this).find('.hizmet-suresi').each(function() {
                totalFormDuration += parseFloat($(this).val()) || 0;
            });
            
            $(this).find('.hizmet-detay-item').each(function() {
                const originalPrice = parseFloat($(this).find('.hizmet-fiyati').val()) || 0;
                const serviceName = $(this).find('.hizmet-ad').text();
                const serviceSure = $(this).find('.hizmet-suresi').val();
                
                totalFormPrice += originalPrice;
                
                if (serviceName) {
                    uniqueHizmetler.add(serviceName);
                    
                    if (!totalSureByService[serviceName]) {
                        totalSureByService[serviceName] = 0;
                    }
                    totalSureByService[serviceName] += parseInt(serviceSure);
                }
                serviceCount++;
            });
        }
    });
    
    const ozetContainer = $('#randevu-ozeti');
    ozetContainer.empty();
    
    if (serviceCount > 0) {
        let hizmetListHtml = '';
        if (Array.from(uniqueHizmetler).length > 0) {
            hizmetListHtml = '<div class="small text-muted mb-1" style="font-size: 0.7rem;"><i class="fa fa-list"></i> Seçilen Hizmetler:</div>';
            Array.from(uniqueHizmetler).forEach((hizmet, i) => {
                const toplamSure = totalSureByService[hizmet] || 0;
                hizmetListHtml += `
                    <div class="small mb-1 d-flex justify-content-between align-items-center" title="${hizmet}" style="font-size: 0.75rem;">
                        <div>
                            <i class="fa fa-check-circle text-success me-1" style="font-size: 0.7rem;"></i>
                            <span class="text-truncate" style="max-width: 120px;">${hizmet}</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.7rem;">${toplamSure}dk</div>
                    </div>`;
            });
        }
        
        ozetContainer.html(`
            <div class="compact-summary">
                <div class="row">
                    <div class="col-12 mb-2">
                        ${hizmetListHtml}
                    </div>
                    <div class="col-6">
                        <div class="summary-item mb-2">
                            <div class="summary-label">Toplam Hizmet</div>
                            <div class="summary-value fw-bold">${serviceCount} adet</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="summary-item mb-2">
                            <div class="summary-label">Toplam Süre</div>
                            <div class="summary-value fw-bold">${totalFormDuration} dakika</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="summary-item mt-2 pt-2 border-top">
                            <div class="summary-label">Toplam Tutar 
                            <span class="toplam-tutar" style="margin-left:50%" >${totalFormPrice.toFixed(2)} ₺</span></div>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
    } else {
        ozetContainer.html(`
            <div class="text-center text-muted py-3">
                <i class="fa fa-search fa-lg mb-2" style="opacity: 0.3;"></i>
                <p class="mb-1 fw-bold" style="font-size: 0.9rem;">Henüz hizmet seçilmedi</p>
                <p class="small mb-0" style="font-size: 0.75rem;">Hizmet eklemek için yukarıdan arama yapın</p>
            </div>
        `);
    }
}

// Select2 yeniden yükleme fonksiyonu (hizmet-select Tom Select kullaniyor, ona dokunma)
function select2YenidenYukle() {
    try { $('.custom-select2').not('.hizmet-select').select2('destroy'); } catch(e){}
    try { $('.opsiyonelSelect').not('.hizmet-select').select2('destroy'); } catch(e){}
    initSelect2();
}

// Hizmet arama sonuçlarını formatlama
function formatHizmetSonuc(hizmet) {
    if (hizmet.loading) {
        return '<div class="loading-results"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>';
    }

    if (!hizmet.id) {
        return hizmet.text;
    }

    const kategori = hizmet.kategori || '';

    var $result = $(
        '<div class="hy-secim-option">' +
            '<span class="hy-secim-option-ad">' + hizmet.text + '</span>' +
            (kategori ? '<span class="hy-secim-option-kat">' + kategori + '</span>' : '') +
        '</div>'
    );

    return $result;
}

// Seçili hizmeti formatlama (sade: sadece ad)
function formatHizmetSecim(hizmet) {
    if (!hizmet.id) return hizmet.text;
    return hizmet.text;
}
 // Form resetleme fonksiyonu
  function resetForm() {
    $('.hizmet-satiri').slice(1).remove();

    // Hizmet-select Tom Select kullanir: native val degisimi TS'i etkilemez, TS API ile temizle
    $('.hizmet-satiri[data-value="0"]').find('.hizmet-select').each(function(){
        var el = this;
        if(el.tomselect){ try { el.tomselect.clear(true); } catch(e){} }
    });
    $('.hizmet-satiri[data-value="0"]').find('select').not('.hizmet-select').val(null).trigger('change');
    $('.hizmet-satiri[data-value="0"]').find('input[type="number"], textarea').val('');
    $('#hizmet-detaylari-0').empty();
    $('.hizmet-sil[data-value="0"]').attr('disabled', true);
    
    $('#randevutarihiyeni').val('{{date('Y-m-d')}}');
    $('#yenirandevuekleform select[name="adsoyad"]').val(null).trigger('change');
    $('#randevuekle_musteri_id').val(null).trigger('change');
    $('textarea[name="personel_notu"]').val('');
    
    // AJAX isteğini iptal et
    if (aktifPaketIsteki) {
        aktifPaketIsteki.abort();
    }
    
    // Timeout'u temizle
    if (paketKontrolTimeout) {
        clearTimeout(paketKontrolTimeout);
    }
    
    // Modal varsa kapat ve temizle
    if ($('#softPaketSecimModal').length) {
        $('#softPaketSecimModal').modal('hide');
        $('#softPaketSecimModal').remove();
    }
    
    seciliMusteriId = null;
    musteriPaketleri = [];
    $('#paketleri-goster-btn').prop('disabled', true);
    
    $('#tekrarlayan').prop('checked', false);
    $('.tekrar_randevu').prop('disabled', true).addClass('text-muted');
    
    hizmetSatirSayisi = 1;
    updateRandevuOzeti();
}
</script>