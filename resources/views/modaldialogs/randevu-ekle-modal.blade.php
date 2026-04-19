<div id="modal-view-event-add" class="modal modal-top fade calendar-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="h4" style="color:white">
                    <span>Yeni Randevu</span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id='randevu_modal_kapat'>
                    ×
                </button>
            </div>
            <div class="modal-body" style="padding: 1rem;">
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
                            <button type="button" class="btn btn-info btn-sm" id="paketleri-goster-btn" style="margin-top: 5px;" disabled>
                                <i class="icon-copy fa fa-gift"></i> Paketleri Göster
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Randevu Ekleme Bölümü -->
                        <div class="tab-pane fade show active" id="yeni-randevu" role="tabpanel">
                            <form id="yenirandevuekleform" method="POST" action="#">
                                {!!csrf_field()!!}
                                <div class="row">
                                    <!-- Sol Taraf: Temel Bilgiler ve Hizmetler -->
                                    <div class="col-md-8">
                                        <!-- Temel Bilgiler -->
                                        <div class="card mb-2">
                                            <div class="card-header py-1">
                                                <h6 class="mb-0" style="font-size: 0.9rem;">Temel Bilgiler</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="row">
                                                    <div class="col-lg-4 col-md-3 col-sm-12 mb-2">
                                                        <input type="hidden" name="sube" value="{{$isletme->id}}">
                                                        @if($pageindex==2)
                                                        <input type="hidden" name="takvim_sayfasi" value="1">
                                                        @endif
                                                        <label class="form-label" style="font-size: 0.8rem;">@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                                                        <select name="adsoyad" id="randevuekle_musteri_id" class="form-control opsiyonelSelect musteri_secimi" style="width: 100%; height: 32px; font-size: 0.85rem;">
                                                            <option></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-2">
                                                        <label class="form-label" style="visibility: hidden; font-size: 0.8rem;">Yeni müşteri</label>
                                                        <button class="btn btn-outline-primary w-100 yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal" style="padding: 4px 8px; font-size: 0.8rem; height: 32px;">
                                                           Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif
                                                        </button>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                                                        <label class="form-label" style="font-size: 0.8rem;">Tarih</label>
                                                        <input required placeholder="Tarih" type="text" class="form-control" name="tarih" id="randevutarihiyeni" autocomplete="off" novalidate value="{{date('Y-m-d')}}" style="height: 32px; font-size: 0.85rem;" />
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                                                        <label class="form-label" style="font-size: 0.8rem;">Saat</label>
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
                                            <div class="card-body p-2 hizmetler_bolumu" style="max-height: 350px; overflow-y: auto;">
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
                                                            <!-- Personel -->
                                                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 mb-1">
                                                                <label class="form-label" style="font-size: 0.8rem;">Personel</label>
                                                                <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect personel_secimi personel-select" data-index="0" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                                                    <option></option>
                                                                </select>
                                                            </div>
                                                            
                                                            <!-- Yardımcı Personel -->
                                                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 mb-1">
                                                                <label class="form-label" style="font-size: 0.8rem;">Yardımcı Personel</label>
                                                                <select name="randevuyardimcipersonelleriyeni" id="randevuyardimcipersonelleriyeni_0" multiple class="form-control custom-select2 personel_secimi" data-index="0" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                                                                </select>
                                                            </div>
                                                            
                                                            <!-- Cihaz -->
                                                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 mb-1">
                                                                <label class="form-label" style="font-size: 0.8rem;">Cihaz</label>
                                                                <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect cihaz_secimi cihaz-select" data-index="0" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                                                    <option></option>
                                                                </select>
                                                            </div>
                                                            
                                                            <!-- Oda -->
                                                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 mb-1">
                                                                <label class="form-label" style="font-size: 0.8rem;">Oda</label>
                                                                <select name="randevuodalariyeni[]" class="form-control opsiyonelSelect oda_secimi oda-select" data-index="0" style="width:100%; height: 30px; font-size: 0.8rem;">
                                                                    <option></option>
                                                                </select>
                                                            </div>
                                                            
                                                            <!-- Hizmet Seçimi -->
                                                            <div class="col-12 mb-1">
                                                                <label class="form-label" style="font-size: 0.8rem;">Hizmetler (Çoklu Seçim)</label>
                                                                <select name="randevuhizmetleriyeni" id="randevuhizmetleriyeni_0" multiple class="form-control custom-select2 hizmet_secimi hizmet-select" data-index="0" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
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

                                        <!-- Notlar Bölümü -->
                                        <div class="card mb-2">
                                            <div class="card-header py-1">
                                                <h6 class="mb-0" style="font-size: 0.9rem;">Notlar</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label class="form-label" style="font-size: 0.8rem;">Personel Notu</label>
                                                        <textarea class="form-control" name="personel_notu" placeholder="Randevu ile ilgili notlarınızı buraya yazın..." rows="2" style="min-height: 60px; font-size: 0.85rem;"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sağ Taraf: Özet ve Tekrarlayan Randevu -->
                                    <div class="col-md-4">
                                        <!-- Randevu Özeti -->
                                        <div class="card mb-2">
                                            <div class="card-header py-1">
                                                <h6 class="mb-0" style="font-size: 0.9rem;">Randevu Özeti</h6>
                                            </div>
                                            <div class="card-body p-2">
                                                <div id="randevu-ozeti" style="min-height: 180px; font-size: 0.85rem;">
                                                    <div class="text-center text-muted py-3">
                                                        <i class="fa fa-search fa-lg mb-2" style="opacity: 0.3;"></i>
                                                        <p class="mb-1 fw-bold" style="font-size: 0.9rem;">Henüz hizmet seçilmedi</p>
                                                        <p class="small mb-0" style="font-size: 0.75rem;">Hizmet eklemek için yukarıdan arama yapın</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tekrarlayan Randevu -->
                                        <div class="card">
                                            <div class="card-header py-1 d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0" style="font-size: 0.9rem;">Tekrarlayan Randevu</h6>
                                                <span><input class="form-check-input" style="height: 14px; width: 28px;" id="tekrarlayan" name="tekrarlayan" type="checkbox"></span>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1" style="font-size: 0.75rem;">Sıklık</label>
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
                                                    
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1" style="font-size: 0.75rem;">Tekrar Sayısı</label>
                                                        <input type="tel" name="tekrar_sayisi" class="form-control tekrar_randevu form-control-sm" required value="0" disabled style="height: 28px; font-size: 0.75rem; padding: 2px 5px;">
                                                    </div>
                                                    
                                                    <div class="col-12 mt-2">
                                                        <small class="text-muted" style="font-size: 0.7rem;">
                                                            <i class="fa fa-info-circle"></i> Tekrarlayan randevular otomatik olarak belirtilen aralıklarla oluşturulacaktır.
                                                        </small>
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
                <button type="submit" form="saat_kapama" class="btn btn-warning btn-sm" id="saat-kapama-kaydet" style="padding: 5px 12px; font-size: 0.85rem;">
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
#modal-view-event-add .select2-dropdown {
    border-radius: 8px !important;
    border: 2px solid #e5e7eb !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
}
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

/* Hizmetler Scroll Alanı */
.hizmetler_bolumu {
    max-height: 350px;
    overflow-y: auto;
    padding-right: 4px;
}

.hizmetler_bolumu::-webkit-scrollbar {
    width: 4px;
}

.hizmetler_bolumu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.hizmetler_bolumu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.hizmetler_bolumu::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

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
</style>

<script>
let hizmetSatirSayisi = 1;
let hizmetDataCache = {};
let seciliMusteriId = null;
let musteriPaketleri = [];

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
    // Tab değişimlerini takip et ve butonları göster/gizle
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
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
        const activeTabId = $('.tab-pane.active').attr('id');
        
        if (activeTabId === 'yeni-randevu') {
            // Randevu tab'ı aktif
            $('#randevu-olustur').show();
            @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel') && !Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))
            $('#saat-kapama-kaydet').hide();
            @endif
        } else if (activeTabId === 'saat-kapama') {
            // Saat kapama tab'ı aktif
            @if(!Auth::guard('isletmeyonetim')->user()->hasRole('Personel') && !Auth::guard('isletmeyonetim')->user()->hasRole('Sosyal Medya Uzmanı'))
            $('#saat-kapama-kaydet').show();
            @endif
            $('#randevu-olustur').hide();
        }
    }
    
    $('#modal-view-event-add').on('show.bs.modal', function() {
        // Varsayılan olarak randevu tab'ı aktif
        $('#randevu-olustur').show();
        $('#saat-kapama-kaydet').hide();
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
    
    // Müşteri temizlendiğinde hizmetleri de temizle
    $('.hizmet_secimi').each(function() {
        $(this).val(null).trigger('change');
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
    
    // Hizmet select2 event handler'larini bagla (re-init sonrasi yeniden cagrilmali)
    // Secim durumuna gore placeholder'i gizle/goster
    function hizmetPlaceholderGuncelle($sel){
        var varMi = ($sel.val() || []).length > 0;
        var $search = $sel.next('.select2-container').find('.select2-search__field');
        if(varMi){
            $search.attr('placeholder','').css('min-width','4px');
        } else {
            $search.attr('placeholder', $sel.data('placeholder-orijinal') || '').css('min-width','');
        }
    }

    function attachHizmetSelect2Events($sel){
        // Orijinal placeholder'i sakla
        var phOrig = $sel.next('.select2-container').find('.select2-search__field').attr('placeholder') || '';
        if(phOrig) $sel.data('placeholder-orijinal', phOrig);

        $sel.off('select2:select select2:unselect select2:open change.phUp')
            .on('select2:select', function(e){
                const service = e.params.data;
                const index = $(this).data('index');
                if(!service || !service.id) return;
                if(!hizmetDataCache[service.id]){
                    hizmetDataCache[service.id] = {
                        id: service.id,
                        text: service.text || service.ad,
                        sure: service.sure || 0,
                        fiyat: service.fiyat || 0,
                        kategori: service.kategori || '',
                        renk: service.renk || '#6366f1'
                    };
                }
                updateHizmetDetaylari(index);
                updateRandevuOzeti();
                hizmetPlaceholderGuncelle($(this));
            })
            .on('select2:unselect', function(){
                const index = $(this).data('index');
                updateHizmetDetaylari(index);
                updateRandevuOzeti();
                hizmetPlaceholderGuncelle($(this));
            })
            .on('select2:open', function(){
                var $me = $(this);
                hizmetDropdownAsagiZorla($me);
                setTimeout(function(){ $('.select2-search__field').focus(); }, 100);
            })
            .on('select2:select select2:unselect', function(){
                // Secim sonrasi dropdown asagida kalsin
                hizmetDropdownAsagiZorla($(this));
            })
            .on('change.phUp', function(){ hizmetPlaceholderGuncelle($(this)); });

        // Baslangicta bir kez ayarla
        hizmetPlaceholderGuncelle($sel);
    }

    function initHizmetSelect2Tek($sel, placeholder){
        if($sel.hasClass('select2-hidden-accessible')){ try{ $sel.select2('destroy'); }catch(e){} }
        $sel.select2({
            placeholder: placeholder || 'Önce personel veya cihaz seçin...',
            allowClear: true,
            width: '100%',
            multiple: true,
            closeOnSelect: false,
            dropdownParent: $('#modal-view-event-add'),
            language: {
                noResults: function(){ return 'Bu personel/cihaz için hizmet atanmamış'; },
                searching: function(){ return 'Aranıyor...'; }
            },
            escapeMarkup: function(markup){ return markup; },
            templateResult: formatHizmetSonuc,
            templateSelection: formatHizmetSecim
        });
        attachHizmetSelect2Events($sel);
    }

    // Modal scroll edildiginde aktif dropdown'i yeniden hizala
    $(document).off('scroll.hizmetDropdown').on('scroll.hizmetDropdown', '#modal-view-event-add', function(){
        if($('.select2-container--open').length) hizmetDropdownAsagiZorla();
    });

    // Select2 dropdown'unu select'in tam altina manuel konumlandir.
    // Modal scroll'u ve hizmet-detaylari elementi dropdown'u asagi itiyordu.
    function hizmetDropdownAsagiZorla($sel){
        setTimeout(function(){
            var $open = $('.select2-container--open');
            if(!$open.length) return;

            // --above/--below siniflari duzelt
            $open.removeClass('select2-container--above').addClass('select2-container--below');
            $open.find('.select2-dropdown').removeClass('select2-dropdown--above').addClass('select2-dropdown--below');

            // Tetikleyen select'i bul: $sel parametresi yoksa DOM'dan hesapla
            var $target = $sel && $sel.length ? $sel : null;
            if(!$target){
                var $sc = $open.find('.select2-selection');
                if($sc.length){
                    // select2 container'inin kardesi olan hidden select
                    $target = $open.prev('select');
                }
            }
            if(!$target || !$target.length) return;

            // Selection box (gorunen kutu) - bu select'in bir sonraki kardesinin select2-container'i
            var $container = $target.next('.select2-container');
            var $selBox = $container.find('.select2-selection').first();
            var $dropdown = $open.find('.select2-dropdown');
            if(!$selBox.length || !$dropdown.length) return;

            var modalEl = document.getElementById('modal-view-event-add');
            if(!modalEl) return;

            var selRect = $selBox[0].getBoundingClientRect();
            var modalRect = modalEl.getBoundingClientRect();

            // Dropdown'in modal icindeki konumu: selection'in altina yapistir
            var top = (selRect.bottom - modalRect.top) + modalEl.scrollTop + 2;
            var left = (selRect.left - modalRect.left) + modalEl.scrollLeft;

            $dropdown.css({
                'position': 'absolute',
                'top': top + 'px',
                'left': left + 'px',
                'width': selRect.width + 'px',
                'bottom': 'auto',
                'margin': '0'
            });
        }, 0);
    }

    // Hizmet select2'lerini başlatma fonksiyonu (local options; personel/cihaz secildiginde doldurulur)
    function initHizmetSelect2() {
        $('.hizmet-select').each(function(){
            initHizmetSelect2Tek($(this));
        });

        // Personel veya cihaz secildiginde: ayni satirdaki hizmet select'ini doldur
        $(document).off('change.hizmetLoad', 'select[name="randevupersonelleriyeni[]"], select[name^="randevucihazlariyeni"]')
            .on('change.hizmetLoad', 'select[name="randevupersonelleriyeni[]"], select[name^="randevucihazlariyeni"]', function(){
                var $row = $(this).closest('.row');
                var personelId = $row.find('select[name="randevupersonelleriyeni[]"]').val() || '';
                var cihazId = $row.find('select[name^="randevucihazlariyeni"]').val() || '';
                var $hizmet = $row.find('.hizmet-select');
                if(!$hizmet.length) return;

                // Iki secim de bos ise: hizmet listesini temizle
                if(personelId === '' && cihazId === ''){
                    $hizmet.empty().append('<option></option>');
                    initHizmetSelect2Tek($hizmet, 'Önce personel veya cihaz seçin...');
                    $hizmet.trigger('change');
                    return;
                }

                $.ajax({
                    url: '/isletmeyonetim/personel-cihaz-hizmetleri-json',
                    type: 'GET',
                    dataType: 'json',
                    data: { personel_id: personelId, cihaz_id: cihazId, sube: '{{$isletme->id}}' },
                    success: function(resp){
                        var list = (resp && resp.results) ? resp.results : [];
                        list.forEach(function(h){
                            hizmetDataCache[h.id] = {
                                id: h.id,
                                text: h.ad,
                                sure: h.sure || 0,
                                fiyat: h.fiyat || 0,
                                kategori: h.kategori || '',
                                renk: h.renk || '#6366f1'
                            };
                        });

                        var secili = $hizmet.val() || [];
                        if($hizmet.hasClass('select2-hidden-accessible')){ try{$hizmet.select2('destroy');}catch(e){} }
                        $hizmet.empty().append('<option></option>');
                        list.forEach(function(h){
                            $hizmet.append(new Option(h.ad, h.id, false, false));
                        });
                        var korunan = (Array.isArray(secili) ? secili : []).filter(function(id){
                            return list.some(function(h){ return String(h.id) === String(id); });
                        });
                        $hizmet.val(korunan);
                        initHizmetSelect2Tek($hizmet, list.length ? 'Hizmet seçin...' : 'Atanmış hizmet bulunamadı');
                        $hizmet.trigger('change');
                    },
                    error: function(){
                        console.warn('Hizmetler yüklenemedi');
                    }
                });
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
        
        let currentSelections = selectElement.select2('data');
        currentSelections = currentSelections.filter(item => item.id != serviceId);
        
        selectElement.val(currentSelections.map(item => item.id)).trigger('change');
        selectElement.select2('data', currentSelections);
        
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
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-1">
                            <label class="form-label" style="font-size: 0.8rem;">Personel</label>
                            <select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect personel_secimi personel-select" data-index="${newIndex}" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-1">
                            <label class="form-label" style="font-size: 0.8rem;">Yardımcı Personel</label>
                            <select name="randevuyardimcipersonelleriyeni" id="randevuyardimcipersonelleriyeni_${newIndex}" multiple class="form-control custom-select2 personel_secimi" data-index="${newIndex}" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-1">
                            <label class="form-label" style="font-size: 0.8rem;">Cihaz</label>
                            <select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect cihaz_secimi cihaz-select" data-index="${newIndex}" style="width: 100%; height: 30px; font-size: 0.8rem;">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-1">
                            <label class="form-label" style="font-size: 0.8rem;">Oda</label>
                            <select name="randevuodalariyeni[]" class="form-control opsiyonelSelect oda_secimi oda-select" data-index="${newIndex}" style="width:100%; height: 30px; font-size: 0.8rem;">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label" style="font-size: 0.8rem;">Hizmetler (Çoklu Seçim)</label>
                            <select name="randevuhizmetleriyeni" id="randevuhizmetleriyeni_${newIndex}" multiple class="form-control custom-select2 hizmet_secimi hizmet-select" data-index="${newIndex}" style="width: 100%; font-size: 0.8rem; min-height: 30px;">
                                <option></option>
                            </select>
                        </div>
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

        initSelect2();
        $('.hizmet-sil[data-value="0"]').removeAttr('disabled');
        
        hizmetSatirSayisi++;
        updateRandevuOzeti();
        
        setTimeout(function() {
            $('.hizmetler_bolumu').scrollTop($('.hizmetler_bolumu')[0].scrollHeight);
        }, 100);

        select2YenidenYukle();
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
            const selectedServices = $(this).find('.hizmet-select').select2('data');
            
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
    
    // Hizmet kaldırma butonu
    $(document).on('click', '.hizmet-kaldir', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        const serviceId = $(this).data('service-id');
        const selectElement = $(`.hizmet-select[data-index="${index}"]`);
        
        let currentSelections = selectElement.select2('data') || [];
        currentSelections = currentSelections.filter(item => 
            item && item.id && item.id.toString() !== serviceId.toString()
        );
        
        const selectedIds = currentSelections.map(item => item.id);
        selectElement.val(selectedIds).trigger('change');
        
        updateHizmetDetaylari(index);
        updateRandevuOzeti();
    });
    
    // Modal kapatıldığında formu temizle
    $('#modal-view-event-add').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Modal açıldığında select2'leri yeniden başlat
    $('#modal-view-event-add').on('shown.bs.modal', function() {
        $('.custom-select2').select2('destroy');
        $('.opsiyonelSelect').select2('destroy');

        hizmetDataCache = {};

        // Personel/cihaz/oda secimlerini aktif + musait olanlarla doldur
        doldurRandevuSecenekleri();

        setTimeout(() => {
            initSelect2();
            updateRandevuOzeti();
        }, 100);
    });

});

// Hizmet detaylarını güncelle
function updateHizmetDetaylari(index) {
    const selectElement = $(`.hizmet-select[data-index="${index}"]`);
    const selectedServices = selectElement.select2('data') || [];
    const container = $(`#hizmet-detaylari-${index}`);
    
    container.empty();
    
    const validServices = selectedServices.filter(service => 
        service && service.id && service.text && service.text.trim() !== ''
    );
    
    validServices.forEach((service, serviceIndex) => {
        const cachedData = hizmetDataCache[service.id] || service;
        const sure = cachedData.sure || 0;
        const fiyat = cachedData.fiyat || 0;
        const renk = cachedData.renk || '#007bff';
        const serviceText = cachedData.text || service.text || '';
        const checkboxDisabled = serviceIndex === 0 ? 'disabled' : '';
        if (!serviceText.trim()) {
            return;
        }
        
        const hizmetDetayHtml = `
            <div class="hizmet-detay-item">
                <div class="hizmet-detay-header">
                    <span class="hizmet-ad" style="color: ${renk}; font-size: 0.8rem;">${serviceText}</span>
                    <input 
                        type="checkbox"
                        
                        name="usttekiyleBirlestir-${service.id}"

                        id="usttekiyleBirlestir-${service.id}"
                        ${checkboxDisabled}
                        style="margin-top: 0;"
                    >
                    <label class="form-check-label ms-1" for="usttekiyleBirlestir-${service.id}" style="font-size: 0.75rem; margin-left:5px">Üstteki Hizmetle Birleştir</label>
                    <button type="button" class="btn btn-sm btn-outline-danger hizmet-kaldir" data-index="${index}" data-service-id="${service.id}" style="padding: 1px 4px; font-size: 0.7rem;">
                        <i class="fa fa-times"></i>
                    </button>
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
                        <input type="number" 
                               class="form-control form-control-sm hizmet-fiyati" 
                               name="hizmet_fiyatlari-${service.id}" 
                               value="${fiyat}" 
                               min="0" 
                               step="0.01"
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
        const selectedServices = $(this).find('.hizmet-select').select2('data');
        
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

// Select2 yeniden yükleme fonksiyonu
function select2YenidenYukle() {
    $('.custom-select2').select2('destroy');
    $('.opsiyonelSelect').select2('destroy');
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
    
    $('.hizmet-satiri[data-value="0"]').find('select').val(null).trigger('change');
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