@php
    // Randevu takvim turune gore personel / cihaz / oda secimlerinin gorunurlugu
    // 0: Hizmete Gore (hepsi gorunur)
    // 1: Personele Gore (sadece personel)
    // 2: Cihaza Gore (sadece cihaz)
    // 3: Odaya Gore (sadece oda)
    $__takvim_turu = $isletme->randevu_takvim_turu ?? 0;
    // Personel secimi her zaman gorunur (cihaz/oda turunde de personel atanabilsin)
    $__personel_style = '';
    $__cihaz_style    = in_array($__takvim_turu, [1, 3]) ? 'display:none;' : '';
    $__oda_style      = in_array($__takvim_turu, [1, 2]) ? 'display:none;' : '';
    $__yardimci_style = 'display:none;'; // her zaman gizli
@endphp

{{-- Tom Select — sadece Randevu modalinda hizmet secimi icin kullanilir --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<div id="modal-view-event-add" class="modal modal-top fade calendar-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="max-width: 1200px !important; width: 96% !important; margin: 0 auto !important;">
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
                        @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
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
                                                        <div class="d-flex justify-content-between align-items-center" style="margin-bottom:4px;">
                                                            <label class="form-label mb-0">@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                                                            <button class="btn btn-sm btn-outline-primary yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal" title="Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29)Danışan @else Müşteri @endif ekle" style="padding: 2px 10px; font-size: 0.75rem; white-space: nowrap;">
                                                                <i class="fa fa-plus"></i> Yeni
                                                            </button>
                                                        </div>
                                                        <select name="adsoyad" id="randevuekle_musteri_id" class="form-control opsiyonelSelect musteri_secimi" style="width: 100%; height: 50px; font-size: 1rem;">
                                                            <option></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6 col-sm-6 mb-2">
                                                        <label class="form-label">Tarih</label>
                                                        <input required placeholder="Tarih" type="text" class="form-control" name="tarih" id="randevutarihiyeni" autocomplete="off" novalidate value="{{date('Y-m-d')}}" style="height: 50px; font-size: 1rem;" />
                                                    </div>
                                                    <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                                                        <label class="form-label">Saat</label>
                                                        <select id='randevu_saat' name="saat" class="form-control" style="height: 50px; font-size: 1rem;">
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
                                                <!-- Tüm hizmetlere uygula: genel kaynak paneli (name YOK -> submit'e girmez) -->
                                                <div class="card mb-2 genel-kaynak-panel" style="border:1px solid #bae6fd;background:#f0f9ff;border-radius:8px;">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex align-items-center mb-1" style="gap:6px;flex-wrap:wrap;">
                                                            <i class="fa fa-users" style="color:#0369a1;"></i>
                                                            <span class="fw-bold" style="font-size:0.82rem;color:#075985;">Tüm hizmetlere uygula</span>
                                                            <small style="color:#0369a1;font-size:0.72rem;">— buradan seçtikleriniz tüm hizmet satırlarına uygulanır; tek bir hizmeti ayrı ayarlamak için satırdaki <strong>Özelleştir</strong>’e tıklayın</small>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-md-4 secim-personel" style="{{ $__personel_style }}">
                                                                <label class="form-label" style="font-size:0.78rem;color:#0369a1;">Personel</label>
                                                                <select class="form-control personel-select genel-personel-select" data-genel="1" style="width:100%;min-height:36px;font-size:0.85rem;"><option></option></select>
                                                            </div>
                                                            <div class="col-md-4 secim-cihaz" style="{{ $__cihaz_style }}">
                                                                <label class="form-label" style="font-size:0.78rem;color:#0369a1;">Cihaz</label>
                                                                <select class="form-control opsiyonelSelect cihaz-select genel-cihaz-select" data-genel="1" style="width:100%;height:32px;font-size:0.8rem;"><option></option></select>
                                                            </div>
                                                            <div class="col-md-4 secim-oda" style="{{ $__oda_style }}">
                                                                <label class="form-label" style="font-size:0.78rem;color:#0369a1;">Oda</label>
                                                                <select class="form-control opsiyonelSelect oda-select genel-oda-select" data-genel="1" style="width:100%;height:32px;font-size:0.8rem;"><option></option></select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
                                                            <div class="col-md-6 kaynak-kolon">
                                                                <div class="row g-2">
                                                                    <!-- Personel -->
                                                                    <div class="col-12 mb-1 secim-personel" style="{{ $__personel_style }}">
                                                                        <label class="form-label" style="font-size: 0.8rem;">Personel</label>
                                                                        <select name="randevupersonelleriyeni[]" class="form-control personel_secimi personel-select" data-index="0" style="width: 100%; min-height: 38px; font-size: 0.85rem;">
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
                                                            <div class="col-md-6 mb-1 hizmet-kolon">
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
                        @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
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
                                            <input type="time" class="form-control" name="saat" id='kapama_saat_baslangic' style="height: 32px; font-size: 0.85rem;">
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-6 col-xs-6" id='bitis_saati_yazi'>
                                            <label style="font-size: 0.8rem;">Bitiş Saati</label>
                                            <input type="time" class="form-control" name="saat_bitis" id='kapama_saat_bitis' style="height: 32px; font-size: 0.85rem;">
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
                @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
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

/* Kolay kullanim: hizmet satirlarindaki kaynak (personel/oda/cihaz) kolonu
   varsayilan gizli; "Ozellestir" ile acilir. Gizliyken hizmet kolonu tam genislik. */
#modal-view-event-add .hizmet-satiri .kaynak-kolon { display: none; }
#modal-view-event-add .hizmet-satiri.kaynak-acik .kaynak-kolon { display: block; }
#modal-view-event-add .hizmet-satiri:not(.kaynak-acik) .hizmet-kolon { flex: 0 0 100%; max-width: 100%; }

/* Yeni Randevu modali dikey ortalama (modal-dialog-centered ::before hack i olmadan) */
/* z-index: swal v1 99999 kullaniyor, modal'in onun ustunde kalmasi icin 100002 (Tom Select dropdown=100000, Select2=100001 ile uyumlu) */
#modal-view-event-add { z-index: 100002 !important; }
/* Oda secim modali parent'in uzerinde olmali (parent 100002 !important) */
#hizmetOdaAtamaModal { z-index: 100020 !important; }
.modal-backdrop.hizmet-oda-backdrop { z-index: 100015 !important; }
#modal-view-event-add.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
#modal-view-event-add .modal-dialog {
    margin: 0 auto !important;
    align-self: center !important;
}

.select2-container .select2-selection--single {
    height: inherit !important;

}

/* Musteri secimi Select2 boyutu — buyuk ve belirgin */
#modal-view-event-add #randevuekle_musteri_id + .select2-container .select2-selection--single {
    height: 50px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 6px 10px !important;
}
#modal-view-event-add #randevuekle_musteri_id + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 1rem !important;
    padding-left: 4px !important;
}
#modal-view-event-add #randevuekle_musteri_id + .select2-container .select2-selection--single .select2-selection__arrow {
    height: 48px !important;
}
#modal-view-event-add #randevuekle_musteri_id + .select2-container--default.select2-container--focus .select2-selection--single,
#modal-view-event-add #randevuekle_musteri_id + .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
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
/* Tek secimli Tom Select (personel-select) — multi ile ayni gorsel dil */
#modal-view-event-add .ts-wrapper.single .ts-control {
    min-height: 38px !important;
    padding: 6px 10px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    background: #fff !important;
    font-size: 0.85rem !important;
}
#modal-view-event-add .ts-wrapper.single .ts-control > .item {
    line-height: 24px !important;
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
    z-index: 100010;
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
/* Hizmet dropdown'i modal uzerinde gorunmeli (modal z-index 100002, dropdown > modal) */
body > .select2-container--open { z-index: 100010 !important; }
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
#modal-view-event-add {
    padding-right: 0 !important;
    padding-left: 0 !important;
}
#modal-view-event-add .modal-dialog,
#modal-view-event-add.modal .modal-dialog,
.modal#modal-view-event-add .modal-dialog {
    max-width: 1200px !important;
    width: auto !important;
    margin: 1.75rem auto !important;
}
#modal-view-event-add .modal-content {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(17, 24, 39, 0.25);
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
/* Tom Select ve Select2 dropdown'lari modal'in (z=100002/100003) uzerinde gorunmeli */
.ts-dropdown { z-index: 100010 !important; }
body > .select2-container--open { z-index: 100010 !important; }

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

// Tom Select uyumlu addServicesToForm — paket popup'tan gelen hizmetlerden once
// kullaniciya HER HIZMET icin oda secim ekrani gosterir. Sonra:
//   - Ayni odaya atanan hizmetler tek satirda toplanir (multi-select)
//   - Farkli odadakiler ayri satirlara dagilir
//   - Oda secilmeyenler her biri ayri satirda kalir (backend otomatik atayacak)
function _yeniRandevuAddServicesToForm(hizmetData, result, showSuccessMessage){
    console.log('[PAKET] addServicesToForm cagrildi:', hizmetData);
    if(!hizmetData || !hizmetData.length){ console.warn('[PAKET] hizmetData bos'); return; }
    // Custom.js'in delegated click handler'i ayni anda 2-3 kez tetiklenebiliyor
    if(window._paketEklemeKilidi){
        console.warn('[PAKET] zaten ekleniyor, yinelenen cagri atlandi');
        return;
    }
    window._paketEklemeKilidi = true;
    setTimeout(function(){ window._paketEklemeKilidi = false; }, 3500);

    // Hizli Paket Randevu modali KALDIRILDI: tek/cok hizmet farketmeksizin tum
    // paket hizmetleri dogrudan ana Yeni Randevu formuna (her hizmet ayri satir,
    // personel/oda/cihaz row0'dan miras) yerlesir. Boylece Personel Notu alani da
    // kullanilabilir; kullanici secimleri/notu duzenleyip "Randevu Olustur"a kendi basar.
    var $soft = $('#softPaketSecimModal');
    var _acSiradaki = function(){
        console.log('[PAKET] '+hizmetData.length+' hizmet -> ana forma yerlestir (modalsiz)');
        try { _paketHizmetleriniAyriSatirlaraEkle(hizmetData); }
        catch(e){ console.error('[PAKET] forma yerlestirme hatasi:', e); window._paketEklemeKilidi = false; }
    };
    if($soft.length){
        $soft.one('hidden.bs.modal', function(){
            $('#softPaketSecimModal').remove();
            $('.modal-backdrop').filter(function(){ return !$('.modal.show, .modal.in').length || $(this).next('.modal.show, .modal.in').length === 0; }).remove();
            _acSiradaki();
        });
        $soft.modal('hide');
        setTimeout(function(){ if($('#softPaketSecimModal').length){ $('#softPaketSecimModal').remove(); _acSiradaki(); } }, 350);
    } else {
        _acSiradaki();
    }
    return;
    // ESKI AKIS (asagisi calismaz; bypass icin yukarida return var):
    // Onceki soft paket modalini kapat
    var $soft = $('#softPaketSecimModal');
    if($soft.length){
        $soft.one('hidden.bs.modal', function(){
            $('#softPaketSecimModal').remove();
            $('.modal-backdrop').filter(function(){ return !$('.modal.show, .modal.in').length || $(this).next('.modal.show, .modal.in').length === 0; }).remove();
            _paketHizmetleriniAyriSatirlaraEkle(hizmetData);
        });
        $soft.modal('hide');
        setTimeout(function(){
            if($('#softPaketSecimModal').length){
                $('#softPaketSecimModal').remove();
                _paketHizmetleriniAyriSatirlaraEkle(hizmetData);
            }
        }, 350);
    } else {
        _paketHizmetleriniAyriSatirlaraEkle(hizmetData);
    }
}

// ============================================================
// HIZLI PAKET RANDEVU MODAL — UX: tek pencerede tum hizmetler icin
// inline personel/oda/cihaz secimi yapilir; direkt randevu olusturulur.
// "Detayli duzenle" basilirsa eski form akisina dusulur.
// ============================================================
function _hizliPaketRandevuModalAc(hizmetData){
    if(!hizmetData || !hizmetData.length) return;

    // Row 0'daki mevcut secimleri base olarak kullan (takvimden inheritance)
    var $row0 = $('#yenirandevuekleform .hizmet-satiri').first();
    var basePersonel = $row0.find('.personel-select').val() || '';
    var baseCihaz    = $row0.find('.cihaz-select').val() || '';
    var baseOda      = $row0.find('.oda-select').val() || '';

    var personeller = (window.randevuModalData && window.randevuModalData.personeller) || [];
    var cihazlar    = (window.randevuModalData && window.randevuModalData.cihazlar) || [];
    var tumOdalar   = (window.randevuModalData && window.randevuModalData.odalar) || [];

    // Hizmet bazli oda filtrele
    function _odaSecenekleri(hizmetId){
        var hid = parseInt(hizmetId, 10);
        var liste = tumOdalar;
        if(hid){
            var filt = tumOdalar.filter(function(o){ return Array.isArray(o.hizmet_idleri) && o.hizmet_idleri.indexOf(hid) !== -1; });
            if(filt.length) liste = filt;
        }
        return liste;
    }

    function _opt(arr, selected, labelKey){
        labelKey = labelKey || 'ad';
        return '<option value="">— Seçiniz —</option>' + arr.map(function(o){
            var sel = String(o.id) === String(selected) ? ' selected' : '';
            return '<option value="'+o.id+'"'+sel+'>'+$('<div>').text(o[labelKey]).html()+'</option>';
        }).join('');
    }

    // Takvim turune gore hangi dropdown'lar gosterilsin:
    // 0=Hizmete (hepsi), 1=Personele (sadece personel), 2=Cihaza (cihaz+personel), 3=Odaya (oda+personel)
    var turu = parseInt(window.randevuTakvimTuru || 0, 10);
    var goster = {
        personel: true, // her durumda
        cihaz:    (turu === 0 || turu === 2),
        oda:      (turu === 0 || turu === 3),
    };
    // Ozellestir alanindaki dropdown sutun genisligi: Personel + (Oda?) + (Cihaz?)
    var ozelSayi = 1 + (goster.cihaz ? 1 : 0) + (goster.oda ? 1 : 0);
    var dropdownColClass = (ozelSayi === 1) ? 'col-12' : (ozelSayi === 2 ? 'col-md-6' : 'col-md-4');

    // --- ORTAK AYAR PANELI: tum hizmetlere tek seferde personel/oda/cihaz uygula ---
    // Salon vakalarinin cogu "hepsi ayni kiside, art arda" oldugundan tek dropdown'la biter.
    var ortakCols = ''
        + '<div class="col-md-4"><label style="font-size:0.75rem;color:#0369a1;font-weight:600;">Personel</label><select class="form-control form-control-sm" id="paketHizli_ortakPersonel" style="font-size:0.85rem;">'+_opt(personeller, basePersonel)+'</select></div>'
        + (goster.oda   ? '<div class="col-md-4"><label style="font-size:0.75rem;color:#0369a1;font-weight:600;">Oda</label><select class="form-control form-control-sm" id="paketHizli_ortakOda" style="font-size:0.85rem;">'+_opt(tumOdalar, baseOda)+'</select></div>' : '')
        + (goster.cihaz ? '<div class="col-md-4"><label style="font-size:0.75rem;color:#0369a1;font-weight:600;">Cihaz</label><select class="form-control form-control-sm" id="paketHizli_ortakCihaz" style="font-size:0.85rem;">'+_opt(cihazlar, baseCihaz)+'</select></div>' : '');
    var ortakPanelHtml = ''
        + '<div class="card mb-3" style="border:1px solid #bae6fd;background:#f0f9ff;border-radius:10px;">'
        +   '<div class="card-body" style="padding:12px 14px;">'
        +     '<div style="font-weight:700;font-size:0.85rem;color:#075985;margin-bottom:8px;"><i class="fa fa-users"></i> Tüm hizmetlere uygula</div>'
        +     '<div class="row g-2">'+ortakCols+'</div>'
        +     '<div style="font-size:0.72rem;color:#0369a1;margin-top:6px;">Buradan seçtiğiniz tüm hizmetlere uygulanır. Bir hizmeti ayrı ayarlamak için satırdaki <strong>Özelleştir</strong>’e tıklayın.</div>'
        +   '</div>'
        + '</div>';

    // Satir kartlari uret (sade: isim + sure gorunur; personel/oda/cihaz "Ozellestir" arkasinda gizli)
    var hizmetSatirlariHtml = ortakPanelHtml + hizmetData.map(function(item, i){
        var hizmetId = item.hizmet_id || item.id;
        var sure = item.sure || 0;
        var fiyat = item.fiyat || 0;
        var odaSec = _odaSecenekleri(hizmetId);
        var paketRozet = item.tur === 'paket' && item.paket_adi
            ? '<span class="badge" style="background:#f59e0b;color:#fff;font-size:0.7rem;margin-left:6px;font-weight:600;padding:3px 8px;border-radius:6px;">📦 '+$('<div>').text(item.paket_adi).html()+'</span>'
            : '<span class="badge" style="background:#3b82f6;color:#fff;font-size:0.7rem;margin-left:6px;padding:3px 8px;border-radius:6px;">Hizmet</span>';

        // Ozellestir alani (varsayilan GIZLI): personel/oda/cihaz + paralel
        var dropdowns = ''
            + '<div class="'+dropdownColClass+'"><label style="font-size:0.75rem;color:#6b7280;font-weight:600;">Personel</label><select class="form-control form-control-sm hizli-personel" style="font-size:0.85rem;">'+_opt(personeller, basePersonel)+'</select></div>'
            + (goster.oda    ? '<div class="'+dropdownColClass+'"><label style="font-size:0.75rem;color:#6b7280;font-weight:600;">Oda</label><select class="form-control form-control-sm hizli-oda" style="font-size:0.85rem;">'+_opt(odaSec, baseOda)+'</select></div>' : '')
            + (goster.cihaz  ? '<div class="'+dropdownColClass+'"><label style="font-size:0.75rem;color:#6b7280;font-weight:600;">Cihaz</label><select class="form-control form-control-sm hizli-cihaz" style="font-size:0.85rem;">'+_opt(cihazlar, baseCihaz)+'</select></div>' : '');

        // Ilk satir DISINDA "Ustteki ile birlestir" checkbox'i goster (paralel hizmet icin)
        var birlestirHtml = (i > 0)
            ? '<div class="mt-2 pt-2" style="border-top:1px dashed #e5e7eb;">'
            +   '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.8rem;color:#374151;margin:0;">'
            +     '<input type="checkbox" class="hizli-birlestir" style="width:16px;height:16px;cursor:pointer;">'
            +     '<i class="fa fa-link" style="color:#6366f1;"></i> Üstteki hizmetle aynı saatte (paralel) çalıştır'
            +   '</label>'
            + '</div>'
            : '';

        var ozelAlan = ''
            + '<div class="hizli-ozel-alan" style="display:none;margin-top:10px;padding-top:10px;border-top:1px dashed #e5e7eb;">'
            +   '<div class="row g-2">'+dropdowns+'</div>'
            +   birlestirHtml
            + '</div>';

        return ''
        + '<div class="paket-hizli-satir card mb-2" data-hizmet-id="'+item.id+'" data-hizmet-orig-id="'+hizmetId+'" data-sure="'+sure+'" data-fiyat="'+fiyat+'" style="border:1px solid #e5e7eb;border-radius:10px;">'
        +   '<div class="card-body" style="padding:12px 14px;">'
        +     '<div class="d-flex justify-content-between align-items-center" style="gap:10px;">'
        +       '<div style="font-weight:700;color:#111827;font-size:0.92rem;flex:1;min-width:0;">'+(i+1)+'. '+$('<div>').text(item.text).html()+paketRozet+(fiyat > 0 ? ' <small style="color:#6b7280;font-weight:500;">'+fiyat+' ₺</small>' : '')+'</div>'
        +       '<div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">'
        +         '<label style="font-size:0.72rem;color:#6b7280;font-weight:600;margin:0;">Süre</label>'
        +         '<input type="number" min="0" step="5" value="'+sure+'" class="form-control form-control-sm hizli-sure" style="font-size:0.85rem;width:72px;text-align:center;">'
        +         '<button type="button" class="btn btn-sm hizli-ozellestir-btn" style="font-size:0.72rem;color:#6366f1;background:#eef2ff;border:1px solid #e0e7ff;border-radius:6px;padding:4px 9px;white-space:nowrap;"><i class="fa fa-sliders"></i> Özelleştir</button>'
        +       '</div>'
        +     '</div>'
        +     ozelAlan
        +   '</div>'
        + '</div>';
    }).join('');

    // Baslik metni
    var musteriAd = '';
    try { musteriAd = $('#randevuekle_musteri_id option:selected').text() || ''; } catch(e){}
    var tarih = $('#yenirandevuekleform input[name="tarih"]').val();
    var saat = $('#yenirandevuekleform select[name="saat"]').val();
    var baslik = (musteriAd ? musteriAd + ' — ' : '') + tarih + ' ' + saat + ' • Paket\'ten '+hizmetData.length+' hizmet';

    $('#paketHizli_baslikYazi').text(baslik);
    $('#paketHizli_satirlar').html(hizmetSatirlariHtml);

    // "Tum hizmetlere uygula": ortak panelden secince TUM satir dropdownlarini doldur.
    // Oda hizmet bazli filtreli oldugundan, secilen oda satirda yoksa o satir es gecilir (best-effort).
    function _ortakYay(sinif, val){
        $('#paketHizli_satirlar .paket-hizli-satir').find(sinif).each(function(){
            if($(this).find('option[value="'+val+'"]').length || val === ''){ $(this).val(val); }
        });
    }
    $('#paketHizli_ortakPersonel').off('change.ortak').on('change.ortak', function(){ _ortakYay('.hizli-personel', $(this).val()); });
    $('#paketHizli_ortakOda').off('change.ortak').on('change.ortak', function(){ _ortakYay('.hizli-oda', $(this).val()); });
    $('#paketHizli_ortakCihaz').off('change.ortak').on('change.ortak', function(){ _ortakYay('.hizli-cihaz', $(this).val()); });

    // "Ozellestir": satira ozel personel/oda/cihaz alanini ac/kapat
    $(document).off('click.ozellestir').on('click.ozellestir', '#paketHizli_satirlar .hizli-ozellestir-btn', function(){
        var $alan = $(this).closest('.paket-hizli-satir').find('.hizli-ozel-alan');
        var acik = $alan.is(':visible');
        $alan.slideToggle(120);
        $(this).toggleClass('active', !acik)
               .css(acik ? {color:'#6366f1',background:'#eef2ff'} : {color:'#fff',background:'#6366f1'});
    });

    // Ozet (canli guncellenir)
    function _ozetGuncelle(){
        var t = 0;
        $('#paketHizli_satirlar .paket-hizli-satir').each(function(){
            var s = parseInt($(this).find('.hizli-sure').val(), 10);
            if(!isNaN(s) && s >= 0) t += s;
        });
        $('#paketHizli_ozetSayi').text(hizmetData.length);
        $('#paketHizli_ozetSure').text(t);
    }
    _ozetGuncelle();
    $('#paketHizli_ozet').show();
    // Sure inputu degisince ozetı tazele
    $(document).off('input.hizliSure').on('input.hizliSure', '#paketHizli_satirlar .hizli-sure', function(){
        _ozetGuncelle();
    });

    // Modali en sona tasi (z-index dogal stack), ac
    var $m = $('#paketHizliRandevuModal');
    $m.detach().appendTo('body');
    $m.modal('show');

    // Modal kapanmasi: kilidi serbest birak
    $m.off('hidden.bs.modal.hizli').on('hidden.bs.modal.hizli', function(){
        window._paketEklemeKilidi = false;
    });

    // Hizli olustur: dogrudan POST
    $('#paketHizli_olustur').off('click.hizli').on('click.hizli', function(){
        var $btn = $(this);
        if($btn.prop('disabled')) return;
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Oluşturuluyor...');

        // Her satirin secimini + birlestir bayragini + DUZENLENMIS sureyi topla
        var atamalar = [];
        $('#paketHizli_satirlar .paket-hizli-satir').each(function(idx){
            var $r = $(this);
            var editedSure = parseInt($r.find('.hizli-sure').val(), 10);
            if(isNaN(editedSure) || editedSure < 0) editedSure = parseInt($r.data('sure'),10) || 0;
            atamalar.push({
                hizmetItemId: $r.data('hizmet-id'),
                hizmetOrigId: $r.data('hizmet-orig-id'),
                sure: editedSure,
                fiyat: parseFloat($r.data('fiyat')) || 0,
                personel: $r.find('.hizli-personel').val() || '',
                cihaz:    $r.find('.hizli-cihaz').val() || '',
                oda:      $r.find('.hizli-oda').val() || '',
                // Ilk satirda checkbox yok; sonraki satirlarda "ustteki ile paralel" bayragi
                birlestir: idx > 0 ? !!$r.find('.hizli-birlestir').prop('checked') : false,
            });
        });

        // hizmetData ile esle (sirayla ayni)
        var hizmetDataWithChoice = hizmetData.map(function(h, i){
            var a = atamalar[i] || {};
            return Object.assign({}, h, {
                _personel: a.personel,
                _cihaz:    a.cihaz,
                _oda:      a.oda,
                _birlestir: a.birlestir,
                sure: a.sure || h.sure || 0,
                fiyat: a.fiyat || h.fiyat || 0,
            });
        });

        _hizliRandevuOlustur(hizmetDataWithChoice, function(ok){
            $btn.prop('disabled', false).html('<i class="fa fa-calendar-check-o"></i> Hızlı Oluştur');
            if(ok){ $m.modal('hide'); }
        });
    });
}

// Hizli olustur: FormData ile dogrudan /yenirandevuekle endpoint'ine POST
// Her hizmet kendi grubunda (oda baska olsa bile ayri satir): backend personel/cihaz/oda key2 sirali iter eder.
function _hizliRandevuOlustur(hizmetData, onBitti){
    var sube = $('#yenirandevuekleform input[name="sube"]').val();
    var musteriId = (typeof seciliMusteriId !== 'undefined' && seciliMusteriId) ? seciliMusteriId : $('#randevuekle_musteri_id').val();
    var tarih = $('#yenirandevuekleform input[name="tarih"]').val();
    var saat = $('#yenirandevuekleform select[name="saat"]').val();
    var personelNotu = $('#yenirandevuekleform textarea[name="personel_notu"]').val() || '';
    var token = $('#yenirandevuekleform input[name="_token"]').val();

    if(!musteriId){ swal({type:'warning',title:'Uyarı',text:'Lütfen önce müşteri seçin.'}); if(onBitti) onBitti(false); return; }
    if(!tarih || !saat){ swal({type:'warning',title:'Uyarı',text:'Tarih ve saat zorunludur.'}); if(onBitti) onBitti(false); return; }

    function _build(cakismaOnayli){
        var fd = new FormData();
        fd.append('_token', token);
        fd.append('sube', sube);
        fd.append('adsoyad', musteriId);
        fd.append('musteri_id', musteriId);
        fd.append('tarih', tarih);
        fd.append('saat', saat);
        fd.append('personel_notu', personelNotu);
        if(cakismaOnayli) fd.append('cakisanrandevuekle', 1);
        @if(($pageindex ?? 0) == 2)
        fd.append('takvim_sayfasi', 1);
        @endif

        var hizmetDetaylari = [];
        var toplamSure = 0, toplamFiyat = 0;
        hizmetData.forEach(function(h, key2){
            fd.append('randevupersonelleriyeni[]', h._personel || '');
            fd.append('randevucihazlariyeni[]', h._cihaz || '');
            fd.append('randevuodalariyeni[]', h._oda || '');
            fd.append('randevuhizmetleriyeni_'+key2+'[]', h.id);
            // BACKWARD COMPAT: cakisan_randevu_kontrol $request->randevuhizmetleriyeni (flat) + $request->hizmet_suresi (flat) bekliyor
            fd.append('randevuhizmetleriyeni[]', h.id);
            var sure = parseInt(h.sure || 0, 10) || 0;
            var fiyat = parseFloat(h.fiyat || 0) || 0;
            fd.append('hizmet_suresi[]', sure);
            fd.append('hizmet_sureleri-'+h.id, sure);
            fd.append('hizmet_fiyatlari-'+h.id, fiyat);
            // Üstteki ile birleştir (paralel): backend "birlestir{key2}" anahtarini key2-1 ile birlikte degerlendirir
            // Backend kodu: if(!isset($request->{"birlestir{$birsonraki}"})) -> $birsonraki = $key2+1
            // Yani $key2=0 isleminin sonunda birlestir1 kontrolune bakar; birlestir1 SET ise saat ilerlemez (row1 row0 ile birlesir)
            if(h._birlestir && key2 > 0){
                fd.append('birlestir'+key2, 1);
            }
            hizmetDetaylari.push({ad: h.text || '', sure: sure, fiyat: fiyat});
            toplamSure += sure; toplamFiyat += fiyat;
        });
        fd.append('toplam_sure', toplamSure);
        fd.append('toplam_fiyat', toplamFiyat.toFixed(2));
        fd.append('hizmet_detaylari', JSON.stringify(hizmetDetaylari));
        return fd;
    }

    function _post(cakismaOnayli){
        var fd = _build(cakismaOnayli);
        // Debug: gonderilen tum FormData icerigini console'a yaz
        try {
            var dbg = {};
            for(var pair of fd.entries()){
                if(dbg[pair[0]] === undefined) dbg[pair[0]] = pair[1];
                else if(Array.isArray(dbg[pair[0]])) dbg[pair[0]].push(pair[1]);
                else dbg[pair[0]] = [dbg[pair[0]], pair[1]];
            }
            console.log('[PAKET-HIZLI] POST -> /yenirandevuekle', dbg);
        } catch(e){}
        $.ajax({
            type:'POST', url:'/isletmeyonetim/yenirandevuekle', dataType:'json',
            data: fd, processData:false, contentType:false,
            beforeSend: function(){ $('#preloader').show(); },
            success: function(result){
                $('#preloader').hide();
                console.log('[PAKET-HIZLI] response:', result);
                if(result.cakismavar){
                    swal({
                        type:'warning', title:"<h2 style='font-size:24px;color:#fff'>Çakışma Var</h2>",
                        background:'#ef4444',
                        html:"<p style='color:#fff;font-size:14px'>"+result.cakismavar+"</p><p style='color:#fff'>Yine de oluşturmak ister misiniz?</p>",
                        showCancelButton:true, confirmButtonText:'Evet, Oluştur', cancelButtonText:'Vazgeç',
                    }).then(function(r2){
                        if(r2.value) _post(true);
                        else if(onBitti) onBitti(false);
                    });
                } else if(result.eklenemez){
                    swal({type:'warning', title:'Uyarı', html: result.eklenemez});
                    if(onBitti) onBitti(false);
                } else if(result.success) {
                    $('#modal-view-event-add').modal('hide');
                    swal({type:'success', title:'Başarılı', html: result.success, showConfirmButton:false, timer: result.timer || 2500});
                    if($('#calendar').length && typeof takvimyukle === 'function') takvimyukle(false, false);
                    try { resetForm && resetForm(); } catch(e){}
                    if(onBitti) onBitti(true);
                } else {
                    // Beklenmeyen response — kullaniciya net mesaj goster
                    console.warn('[PAKET-HIZLI] beklenmeyen response:', result);
                    swal({type:'warning', title:'Beklenmeyen Yanit', html: '<pre style="font-size:11px;text-align:left;">'+JSON.stringify(result).slice(0,500)+'</pre>'});
                    if(onBitti) onBitti(false);
                }
            },
            error: function(req){
                $('#preloader').hide();
                console.error('[PAKET-HIZLI] AJAX hata:', req.status, req.responseText);
                var msg = 'Randevu oluşturulamadı.';
                try {
                    var j = JSON.parse(req.responseText);
                    if(j.message) msg = j.message;
                    else if(j.error) msg = j.error;
                } catch(e){
                    if(req.responseText) msg = req.responseText.slice(0,200);
                }
                swal({type:'error', title:'Hata ('+req.status+')', html: msg});
                if(onBitti) onBitti(false);
            }
        });
    }
    _post(false);
}

// Paketten gelen her hizmeti AYRI bir satira yerlestirir (oda atama popup'i acmadan).
// Row 0'daki personel/cihaz/oda secimleri (takvimden gelen otomatik atama) yeni satirlara da kopyalanir.
function _paketHizmetleriniAyriSatirlaraEkle(hizmetData){
    if(!hizmetData || !hizmetData.length) return;

    // Row 0'daki mevcut personel/cihaz/oda degerlerini sakla (takvimden inheritance icin)
    var $row0 = $('#yenirandevuekleform .hizmet-satiri').first();
    var basePersonel = $row0.find('.personel-select').val() || '';
    var baseCihaz    = $row0.find('.cihaz-select').val() || '';
    var baseOda      = $row0.find('.oda-select').val() || '';
    console.log('[PAKET] base secimler row0:', { personel: basePersonel, cihaz: baseCihaz, oda: baseOda });

    function _setHizmetInRow($row, hizmet){
        var $sel = $row.find('.hizmet-select');
        if($sel[0] && $sel[0].tomselect){
            try { $sel[0].tomselect.clear(true); } catch(e){}
        }
        _hizmetiSatiraKoy($sel, hizmet, true);
    }

    function _applyBaseSelections($row){
        // Sadece bos olan dropdown'lara base degeri uygula (kullanici daha onceden ozellestirmis olabilir)
        if(basePersonel){
            var $p = $row.find('.personel-select');
            if($p.length && !$p.val()){
                if($p[0] && $p[0].tomselect){
                    try { $p[0].tomselect.setValue(basePersonel, true); $p.val(basePersonel).trigger('change'); } catch(e){ $p.val(basePersonel).trigger('change'); }
                } else { $p.val(basePersonel).trigger('change'); }
            }
        }
        if(baseCihaz){
            var $c = $row.find('.cihaz-select');
            if($c.length && !$c.val()) $c.val(baseCihaz).trigger('change');
        }
        if(baseOda){
            var $o = $row.find('.oda-select');
            if($o.length){
                // Once oda options'i bu satirdaki hizmete gore yenile (bos listeden kacin)
                try {
                    var $hizmet = $row.find('.hizmet-select');
                    var v = $hizmet.val();
                    var hizmetIds = Array.isArray(v) ? v.filter(Boolean) : (v ? [v] : []);
                    if(typeof doldurOdaSelectByHizmet === 'function'){
                        doldurOdaSelectByHizmet($o, hizmetIds);
                    }
                } catch(e){}
                // baseOda listede yoksa option olarak ekle (kullanici secimini koruyalim)
                if($o.find('option[value="'+baseOda+'"]').length === 0){
                    var bO = ((window.randevuModalData && window.randevuModalData.odalar) || []).find(function(o){ return String(o.id) === String(baseOda); });
                    if(bO) $o.append(new Option(bO.ad, bO.id, false, false));
                }
                if(!$o.val()) $o.val(baseOda).trigger('change');
            }
        }
    }

    // Bir satirin hizmet-select Tom Select'i hazir + hizmet listesi yuklenmis olana kadar bekle
    // (yukleHizmetler async AJAX, biz bekleemeden yerlestirsek doldurHizmetTom secimi siliyor)
    function _waitRowReady($row, cb){
        var tries = 0;
        (function w(){
            var sel = $row.find('.hizmet-select')[0];
            var ts = sel && sel.tomselect;
            // TS hazir VE en az 1 option yuklenmis (yukleHizmetler tamamlandi)
            if(ts && Object.keys(ts.options).length >= 1){
                cb($row);
                return;
            }
            if(tries++ < 80){ // 80 * 80ms = 6.4sn
                setTimeout(w, 80);
            } else {
                console.warn('[PAKET] _waitRowReady timeout, best-effort yerlestir');
                cb($row);
            }
        })();
    }

    // Tum satirlar yerlestiktan SONRA cagrilan final pass: base secimleri yeniden uygula
    // (yeni satir eklenirken doldurRandevuSecenekleri eski satirlarin oda secimini siliyor)
    function _finalRebaseAllRows(){
        $('#yenirandevuekleform .hizmet-satiri').each(function(){
            _applyBaseSelections($(this));
        });
    }

    // Sirayla her hizmeti yerlestir (Promise zinciri); biri bittikten sonra digerine gec
    function _yerlestirSira(idx){
        if(idx >= hizmetData.length){
            // SON pass: tum satirlara base secimleri tekrar uygula (eski satirlar reset olmuş olabilir)
            setTimeout(function(){
                _finalRebaseAllRows();
                try { updateRandevuOzeti(); } catch(e){}
                window._paketEklemeKilidi = false;
                console.log('[PAKET] yerlestirme tamamlandi, kilit serbest');
            }, 150);
            return;
        }
        var hizmet = hizmetData[idx];

        if(idx === 0){
            // Ilk hizmet -> mevcut row 0
            var $row = $('#yenirandevuekleform .hizmet-satiri').first();
            _waitRowReady($row, function($r){
                _setHizmetInRow($r, hizmet);
                _applyBaseSelections($r);
                console.log('[PAKET] row 0 yerlesti:', hizmet.text);
                setTimeout(function(){ _yerlestirSira(idx+1); }, 120);
            });
        } else {
            // Yeni satir ekle
            var rowCountBefore = $('#yenirandevuekleform .hizmet-satiri').length;
            $('#bir_hizmet_daha_ekle').trigger('click');
            // Yeni satir DOM'a eklenmesini bekle
            var tries = 0;
            (function waitNewRow(){
                var $rows = $('#yenirandevuekleform .hizmet-satiri');
                if($rows.length > rowCountBefore){
                    var $row = $rows.last();
                    _waitRowReady($row, function($r){
                        _setHizmetInRow($r, hizmet);
                        _applyBaseSelections($r);
                        console.log('[PAKET] yeni satir '+idx+' yerlesti:', hizmet.text);
                        setTimeout(function(){ _yerlestirSira(idx+1); }, 120);
                    });
                    return;
                }
                if(tries++ < 30){
                    setTimeout(waitNewRow, 80);
                } else {
                    console.warn('[PAKET] yeni satir DOM eklenmedi, atlandi', idx);
                    _yerlestirSira(idx+1);
                }
            })();
        }
    }

    _yerlestirSira(0);
}

// Tek bir hizmet item'ini bir satira yerlestirir (Tom Select uyumlu).
// returnEcho: true ise sec degeri secili olur, false ise sadece option eklenir.
function _hizmetiSatiraKoy($sel, item, secVe){
    if(!$sel || !$sel.length || !item || !item.id) return false;
    var el = $sel[0];
    var ts = el.tomselect;
    if(!ts) return false;
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
    hizmetDataCache[item.id] = {
        id: item.id, text: item.text,
        sure: item.sure || 0, fiyat: item.fiyat || 0,
        kategori: item.tur === 'paket' ? ('Paket: ' + (item.paket_adi || '')) : 'Hizmet',
        renk: item.tur === 'paket' ? '#f59e0b' : '#3b82f6'
    };
    ts.refreshOptions(false);
    if(secVe !== false){
        var mevcut = ts.getValue();
        if(!Array.isArray(mevcut)) mevcut = mevcut ? [mevcut] : [];
        if(mevcut.indexOf(String(item.id)) === -1) mevcut.push(String(item.id));
        ts.setValue(mevcut, false);
    }
    return true;
}

// Hizmet icin ozel oda listesi (sadece o hizmeti veren odalar).
// Tanimli yoksa: bos array. UI tarafinda fallback gosterilir.
function _odalarHizmetIcin(hizmetId){
    var tum = (window.randevuModalData && window.randevuModalData.odalar) || [];
    var hid = parseInt(hizmetId, 10);
    if(!hid) return [];
    return tum.filter(function(o){
        return Array.isArray(o.hizmet_idleri) && o.hizmet_idleri.indexOf(hid) !== -1;
    });
}

// Hizmetler icin oda secim modalini goster — statik modal (en altta tanimli)
function showHizmetOdaAtamaModal(hizmetData){
    // Hizmet kartlari HTML
    var hizmetKartlari = hizmetData.map(function(item, i){
        var hizmetId = item.hizmet_id || item.id; // paket hizmetlerinde hizmet_id ayri olabilir
        var odalar = _odalarHizmetIcin(hizmetId);
        var hasFiltered = odalar.length > 0;
        if(!hasFiltered){
            // Fallback: tum odalari goster (admin oda-hizmet eslemesi yapmamissa)
            odalar = (window.randevuModalData && window.randevuModalData.odalar) || [];
        }
        var optionsHtml = '<option value="">— Otomatik / Boş —</option>' +
            odalar.map(function(o){
                return '<option value="'+o.id+'">'+$('<div>').text(o.ad).html()+'</option>';
            }).join('');
        var paketRozet = item.tur === 'paket' && item.paket_adi
            ? '<span class="badge" style="background:#f59e0b;color:#fff;font-size:0.65rem;margin-left:6px;">Paket: '+$('<div>').text(item.paket_adi).html()+'</span>'
            : '';
        var fallbackNotu = hasFiltered ? '' :
            '<small class="text-warning d-block" style="font-size:0.7rem;margin-top:2px;"><i class="fa fa-info-circle"></i> Bu hizmete tanımlı oda bulunamadı, tüm odalar listelendi.</small>';
        return ''
            + '<div class="d-flex align-items-center mb-2 p-2" style="border:1px solid #e5e7eb;border-radius:8px;background:#fafafa;">'
            +   '<div style="flex:1;min-width:0;">'
            +     '<div style="font-weight:600;font-size:0.85rem;color:#111827;">'+$('<div>').text(item.text).html()+paketRozet+'</div>'
            +     fallbackNotu
            +   '</div>'
            +   '<div style="width:240px;margin-left:10px;">'
            +     '<select class="form-control form-control-sm hizmet-oda-secimi" data-hizmet-id="'+item.id+'" data-hizmet-text="'+$('<div>').text(item.text).html()+'" style="font-size:0.8rem;">'
            +       optionsHtml
            +     '</select>'
            +   '</div>'
            + '</div>';
    }).join('');

    // Statik modaldaki yer tutuculara icerigi koy ve goster
    $('#hizmetOdaAtama_info').text('Pakette '+hizmetData.length+' hizmet var. Her hizmet için oda seçin. Boş bırakırsanız sistem otomatik atayacak. Aynı odaya atadığınız hizmetler tek satırda birleştirilir.');
    $('#hizmetOdaAtama_kartlar').html(hizmetKartlari);

    var $m = $('#hizmetOdaAtamaModal');
    // KRITIK: modali fiziksel olarak body'nin EN SONUNA tasi (DOM stack icin).
    $m.detach().appendTo('body');

    // Acik olan diger modallari (soft paket vs.) kapat — sadece parent randevu modali kalsin
    $('.modal.show, .modal.in').not('#modal-view-event-add').not('#hizmetOdaAtamaModal').modal('hide');

    $m.modal('show');

    // shown.bs.modal'dan sonra: kendi backdrop'umuza yuksek z-index sinifi ekle
    $m.off('shown.bs.modal.zfix').on('shown.bs.modal.zfix', function(){
        // En son eklenen backdrop bizim
        var $bd = $('.modal-backdrop').last();
        $bd.addClass('hizmet-oda-backdrop');
    });

    function _kapatHizmetOdaModal(){
        try { $('#hizmetOdaAtamaModal').modal('hide'); } catch(e){}
    }

    // Kapanma: X ve Vazgec — her cagrida tekrar baglanmasin diye .off ile reset
    $(document).off('click.hizmetOdaModal').on('click.hizmetOdaModal', '.hizmet-oda-modal-kapat', function(e){
        e.preventDefault();
        _kapatHizmetOdaModal();
    });

    // Onay butonu: forma yerlestirmeden DOGRUDAN backend'e POST et
    $('#hizmet-oda-atama-onayla').off('click').on('click', function(){
        var atama = {}; // hizmet_item_id -> oda_id (veya '')
        $('#hizmetOdaAtamaModal .hizmet-oda-secimi').each(function(){
            atama[$(this).data('hizmet-id')] = $(this).val() || '';
        });
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Oluşturuluyor...');
        _odaModaliOlustur(hizmetData, atama, function(){
            $btn.prop('disabled', false).html('<i class="fa fa-calendar-check-o"></i> Randevu Oluştur');
        });
    });
}

// Oda atama modalindan DOGRUDAN backend'e randevu olustur.
// Forma yerlestirme atlanir; FormData elle insa edilir.
function _odaModaliOlustur(hizmetData, odaAtama, onBitti){
    // 1) Temel validasyonlar
    var sube = $('#yenirandevuekleform input[name="sube"]').val();
    var musteriId = (typeof seciliMusteriId !== 'undefined' && seciliMusteriId) ? seciliMusteriId : $('#randevuekle_musteri_id').val();
    var tarih = $('#yenirandevuekleform input[name="tarih"]').val();
    var saat = $('#yenirandevuekleform select[name="saat"]').val();
    var personelNotu = $('#yenirandevuekleform textarea[name="personel_notu"]').val() || '';
    var token = $('#yenirandevuekleform input[name="_token"]').val();

    if(!musteriId){
        swal({ type:'warning', title:'Uyarı', text:'Lütfen önce müşteri seçin.' });
        if(onBitti) onBitti();
        return;
    }
    if(!tarih || !saat){
        swal({ type:'warning', title:'Uyarı', text:'Tarih ve saat zorunludur.' });
        if(onBitti) onBitti();
        return;
    }

    // 2) Oda bazinda gruplari kur (forma yerlestirmedeki ile ayni mantik)
    var gruplar = [];
    var indexByOda = {};
    hizmetData.forEach(function(item){
        var oid = (odaAtama[item.id] || '').toString();
        if(!oid){
            gruplar.push({ oda_id:'', hizmetler:[item] });
            return;
        }
        if(indexByOda[oid] !== undefined){
            gruplar[indexByOda[oid]].hizmetler.push(item);
        } else {
            indexByOda[oid] = gruplar.length;
            gruplar.push({ oda_id: oid, hizmetler:[item] });
        }
    });
    if(!gruplar.length){
        swal({ type:'warning', title:'Uyarı', text:'Eklenecek hizmet yok.' });
        if(onBitti) onBitti();
        return;
    }

    // 3) FormData insa et (backend yenirandevuekle action'inin bekledigi sema)
    function _buildFormData(cakismaOnayli){
        var fd = new FormData();
        fd.append('_token', token);
        fd.append('sube', sube);
        fd.append('adsoyad', musteriId);
        fd.append('musteri_id', musteriId);
        fd.append('tarih', tarih);
        fd.append('saat', saat);
        fd.append('personel_notu', personelNotu);
        if(cakismaOnayli) fd.append('cakisanrandevuekle', 1);
        @if(($pageindex ?? 0) == 2)
        fd.append('takvim_sayfasi', 1);
        @endif

        var hizmetDetaylari = [];
        var toplamSure = 0, toplamFiyat = 0;
        gruplar.forEach(function(grup, key2){
            // Grup basina TEK personel/cihaz/oda alani
            fd.append('randevupersonelleriyeni[]', '');
            fd.append('randevucihazlariyeni[]', '');
            fd.append('randevuodalariyeni[]', grup.oda_id || '');
            // Hizmet ID'leri
            grup.hizmetler.forEach(function(h){
                fd.append('randevuhizmetleriyeni_'+key2+'[]', h.id);
                // Hizmet bazli sure ve fiyat
                var sure = parseInt(h.sure || 0, 10) || 0;
                var fiyat = parseFloat(h.fiyat || 0) || 0;
                fd.append('hizmet_sureleri-'+h.id, sure);
                fd.append('hizmet_fiyatlari-'+h.id, fiyat);
                hizmetDetaylari.push({ ad: h.text || '', sure: sure, fiyat: fiyat });
                toplamSure += sure;
                toplamFiyat += fiyat;
            });
        });
        fd.append('toplam_sure', toplamSure);
        fd.append('toplam_fiyat', toplamFiyat.toFixed(2));
        fd.append('hizmet_detaylari', JSON.stringify(hizmetDetaylari));
        return fd;
    }

    function _post(cakismaOnayli, basariCb){
        $.ajax({
            type:'POST',
            url:'/isletmeyonetim/yenirandevuekle',
            dataType:'json',
            data: _buildFormData(cakismaOnayli),
            processData:false,
            contentType:false,
            beforeSend: function(){ $('#preloader').show(); },
            success: function(result){
                $('#preloader').hide();
                if(result.cakismavar){
                    swal({
                        type:'warning',
                        title:"<h2 style='font-size:28px;color:#fff'>Çakışma Var</h2>",
                        background:'#ff0000',
                        html:"<p style='color:#fff;font-size:16px'>"+result.cakismavar+"</p><p style='color:#fff;padding:8px;border:1px solid #fff;border-radius:8px'>Yine de oluşturmak ister misiniz?</p>",
                        showCancelButton:true,
                        confirmButtonText:'Evet, Oluştur',
                        cancelButtonText:'Vazgeç',
                    }).then(function(r2){
                        if(r2.value) _post(true, basariCb);
                        else if(onBitti) onBitti();
                    });
                } else if(result.eklenemez){
                    swal({ type:'warning', title:'Uyarı', html: result.eklenemez });
                    if(onBitti) onBitti();
                } else {
                    // Basarili
                    $('#modal-view-event-add').modal('hide');
                    swal({
                        type:'success', title:'Başarılı', html: result.success || 'Randevu oluşturuldu.',
                        showCloseButton:false, showConfirmButton:false, timer: result.timer || 2500
                    });
                    if($('#calendar').length && typeof takvimyukle === 'function') takvimyukle(false, false);
                    try { resetForm && resetForm(); } catch(e){}
                    if(basariCb) basariCb();
                    if(onBitti) onBitti();
                }
            },
            error: function(req){
                $('#preloader').hide();
                try { document.getElementById('hata').innerHTML = req.responseText; } catch(e){}
                swal({ type:'error', title:'Hata', text:'Randevu oluşturulamadı. Lütfen tekrar deneyin.' });
                if(onBitti) onBitti();
            }
        });
    }

    // Modali kapat, ardindan POST
    try { $('#hizmetOdaAtamaModal').modal('hide'); } catch(e){}
    _post(false);
}

// Oda atamasina gore hizmetleri forma yerlestir.
// Ayni odaya secilenler tek satirda, farkli odadakiler ayri satirlarda.
// Bos kalanlar (atama yapilmayanlar) her biri ayri satirda toplanir (backend otomatik atayacak).
// onTamamlandi: tum yerlestirme + TS sync bittikten sonra cagrilir (otomatik submit icin)
function _formaYerlestir(hizmetData, odaAtama, onTamamlandi){
    // Gruplari olustur
    // - Anahtar: oda_id (string), bos icin 'BOS' + counter (her bos hizmet kendi grubunda)
    var gruplar = []; // [{oda_id, hizmetler:[]}]
    var bosCounter = 0;
    var indexByOda = {};
    hizmetData.forEach(function(item){
        var oid = (odaAtama[item.id] || '').toString();
        if(!oid){
            // Bos: her bos hizmet kendi grubu (backend ayri oda atayabilsin diye)
            gruplar.push({ oda_id: '', hizmetler: [item] });
            bosCounter++;
            return;
        }
        if(indexByOda[oid] !== undefined){
            gruplar[indexByOda[oid]].hizmetler.push(item);
        } else {
            indexByOda[oid] = gruplar.length;
            gruplar.push({ oda_id: oid, hizmetler: [item] });
        }
    });

    console.log('[PAKET] Gruplar:', gruplar);

    // Ilk satir mevcut, kalan gruplar icin yeni satir
    function _grupYerlestir(grupIdx){
        if(grupIdx >= gruplar.length){
            try { updateRandevuOzeti(); } catch(e){}
            // Tum yerlestirme bitti, callback'i cagir (otomatik submit vs.)
            if(typeof onTamamlandi === 'function'){
                setTimeout(onTamamlandi, 150);
            }
            return;
        }
        var grup = gruplar[grupIdx];
        function _setRow($row){
            // Hizmetleri ekle (multi-select destekler)
            var $sel = $row.find('.hizmet-select');
            if($sel[0] && $sel[0].tomselect){
                $sel[0].tomselect.clear(true);
            }
            grup.hizmetler.forEach(function(h){ _hizmetiSatiraKoy($sel, h, true); });
            // Oda dropdown filtresi onChange tarafindan yenilenir; degeri ata
            setTimeout(function(){
                var $oda = $row.find('.oda-select');
                if(grup.oda_id){
                    $oda.val(grup.oda_id).trigger('change');
                }
            }, 60);
            _grupYerlestir(grupIdx+1);
        }
        if(grupIdx === 0){
            var $first = $('#yenirandevuekleform .hizmet-satiri').first();
            // Bekle TS hazir olsun
            var tries = 0;
            (function w(){
                if($first.find('.hizmet-select')[0] && $first.find('.hizmet-select')[0].tomselect){
                    _setRow($first);
                } else if(tries++ < 12){ setTimeout(w, 80); }
                else _setRow($first); // best effort
            })();
        } else {
            // Yeni satir ekle
            $('#bir_hizmet_daha_ekle').trigger('click');
            setTimeout(function(){
                var $row = $('#yenirandevuekleform .hizmet-satiri').last();
                var tries = 0;
                (function w(){
                    if($row.find('.hizmet-select')[0] && $row.find('.hizmet-select')[0].tomselect){
                        _setRow($row);
                    } else if(tries++ < 12){ setTimeout(w, 80); }
                    else _setRow($row);
                })();
            }, 250);
        }
    }
    _grupYerlestir(0);
}

// custom.js'in `function addServicesToForm` hoisting'i bizim atamayi ezdigi icin
// DOMContentLoaded sonrasi window.addServicesToForm'u override et
$(window).on('load', function(){
    window.addServicesToForm = _yeniRandevuAddServicesToForm;
    console.log('[PAKET] addServicesToForm override aktif (window load)');
});
$(document).ready(function(){
    // Erken override (modal acilirken hazir olsun)
    window.addServicesToForm = _yeniRandevuAddServicesToForm;
});

// Randevu modali icin: aktif personel + aktif & musait cihaz + aktif & musait oda listeleri
window.randevuModalData = {
    personeller: [
        @foreach(\App\Personeller::where('salon_id',$isletme->id)->where('aktif',1)->where('takvimde_gorunsun',true)->orderBy('personel_adi','asc')->get() as $p)
            { id: {{ (int)$p->id }}, ad: @json($p->personel_adi) },
        @endforeach
    ],
    cihazlar: [
        @foreach(\App\Cihazlar::where('salon_id',$isletme->id)->where('aktifmi',1)->where('durum',1)->orderBy('cihaz_adi','asc')->get() as $c)
            { id: {{ (int)$c->id }}, ad: @json($c->cihaz_adi) },
        @endforeach
    ],
    odalar: [
        @php
            // Oda -> Hizmet eslesmesi (oda_sunulan_hizmetler tablosu varsa kullanilir)
            $__oda_hizmet_map = [];
            if (\Schema::hasTable('oda_sunulan_hizmetler')) {
                $__oda_hizmet_map = \App\OdaHizmetler::where('salon_id',$isletme->id)
                    ->get()
                    ->groupBy('oda_id')
                    ->map(function($g){ return $g->pluck('hizmet_id')->map(fn($x)=>(int)$x)->values()->all(); })
                    ->toArray();
            }
        @endphp
        @foreach(\App\Odalar::where('salon_id',$isletme->id)->where('aktifmi',1)->where('durum',1)->orderBy('oda_adi','asc')->get() as $o)
            { id: {{ (int)$o->id }}, ad: @json($o->oda_adi), hizmet_idleri: @json($__oda_hizmet_map[$o->id] ?? []) },
        @endforeach
    ]
};

// Bir oda-select'i, satirdaki secili hizmete gore filtreleyerek doldurur.
// Hicbir hizmet secilmemisse veya hizmete tanimli oda yoksa: tum odalar listelenir.
function doldurOdaSelectByHizmet($sel, hizmetIds){
    if(!$sel || !$sel.length) return;
    var tumOdalar = (window.randevuModalData && window.randevuModalData.odalar) || [];
    var liste = tumOdalar;
    if(Array.isArray(hizmetIds) && hizmetIds.length){
        var ids = hizmetIds.map(function(x){ return parseInt(x,10); }).filter(function(x){ return !!x; });
        if(ids.length){
            var filt = tumOdalar.filter(function(o){
                return Array.isArray(o.hizmet_idleri) && o.hizmet_idleri.some(function(h){ return ids.indexOf(h) !== -1; });
            });
            // Eger hizmete tanimli oda yoksa fallback: tum odalar (graceful)
            if(filt.length) liste = filt;
        }
    }
    doldurSelect($sel, liste);
}

// Bir satirdaki hizmet degisince ayni satirdaki oda dropdown'ini hizmete gore filtrele
function odaSecimleriniHizmeteGoreYenile(satirIndex){
    var $row = $('#yenirandevuekleform .hizmet-satiri[data-value="'+satirIndex+'"]');
    if(!$row.length) return;
    var $hizmet = $row.find('.hizmet-select');
    var hizmetIds = [];
    if($hizmet.length){
        var v = $hizmet.val();
        if(Array.isArray(v)) hizmetIds = v.filter(Boolean);
        else if(v) hizmetIds = [v];
    }
    var $oda = $row.find('.oda-select');
    doldurOdaSelectByHizmet($oda, hizmetIds);
}

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
    $('#modal-view-event-add select.personel-select, #modal-view-event-add select.personel_secimi').each(function(){
        // Hizmet select'ini atla (hizmet-select class'i var)
        if($(this).hasClass('hizmet-select')) return;
        // Genel kaynak paneli ayri yonetilir (satir ekleme/refresh churn'unden etkilenmesin)
        if($(this).hasClass('genel-personel-select')) return;
        doldurSelect($(this), window.randevuModalData.personeller);
    });
    $('#modal-view-event-add select.cihaz-select').each(function(){
        if($(this).hasClass('genel-cihaz-select')) return;
        doldurSelect($(this), window.randevuModalData.cihazlar);
    });
    // Oda dropdown'larini her satirin kendi hizmetine gore filtreleyerek doldur
    $('#modal-view-event-add .hizmet-satiri').each(function(){
        var idx = $(this).data('value');
        var $hizmet = $(this).find('.hizmet-select');
        var $oda = $(this).find('.oda-select');
        if(!$oda.length) return;
        var hids = [];
        if($hizmet.length){
            var v = $hizmet.val();
            if(Array.isArray(v)) hids = v.filter(Boolean);
            else if(v) hids = [v];
        }
        doldurOdaSelectByHizmet($oda, hids);
    });
    // Personel selectlerini Tom Select'e cevir (form submit name'i degismez)
    initPersonelTomAll();
}

// Personel select Tom Select destroy/init helpers — submit parametresi degisikligi yok.
function tomDestroyPersonel($sel){
    var el = $sel && $sel[0];
    if(!el) return;
    if(el.tomselect){ try { el.tomselect.destroy(); } catch(e){} }
}

function initPersonelTom($sel){
    if(!$sel || !$sel.length || typeof TomSelect === 'undefined') return null;
    var el = $sel[0];
    if(!el) return null;
    // KRITIK: Tom Select init olunca urettigi .ts-wrapper DIV'i orijinal select'in
    // class'larini (personel-select dahil) kopyaliyor. Sonraki cagrilarda selector
    // hem select'i hem o div'i yakalayinca div'e new TomSelect -> getSettings 'trim'
    // hatasi (div.value undefined). Sadece gercek <select> uzerinde calis.
    if(el.tagName !== 'SELECT') return null;
    // Zaten Tom Select init'liyse ve saglikli ise mevcudu dondur (double-init'i engelle)
    if(el.tomselect){
        try { return el.tomselect; } catch(e){}
    }
    try {
        return new TomSelect(el, {
            plugins: ['clear_button'],
            placeholder: 'Personel seçin...',
            allowEmptyOption: true,
            persist: false,
            maxOptions: null,
            searchField: ['text'],
            render: {
                no_results: function(){
                    return '<div class="no-results">Personel bulunamadı</div>';
                }
            }
        });
    } catch(err){
        console.warn('[initPersonelTom] hata:', err);
        return null;
    }
}

function initPersonelTomAll(){
    // select. ile sinirla: tom-select wrapper DIV'i de personel-select class'i tasiyor
    $('#modal-view-event-add select.personel-select').each(function(){
        // Genel kaynak paneli personeli genelPanelHazirla() tarafindan yonetilir
        if($(this).hasClass('genel-personel-select')) return;
        initPersonelTom($(this));
    });
}

// ===================== GENEL KAYNAK PANELI (kolay kullanim) =====================
// Ust "Tum hizmetlere uygula" panelinden secilen personel/oda/cihaz, tum hizmet
// satirlarina uygulanir. Satirlardaki kaynak alanlari varsayilan gizli; satir
// header'indaki "Ozellestir" ile acilir (acilirken personel Tom Select temiz reinit
// edilir -> dinamik satirlarda olusan bozuk render duzelir).
function genelPanelHazirla(){
    var data = window.randevuModalData || {};
    console.log('[GENEL PANEL] basladi — personel:', (data.personeller||[]).length, 'cihaz:', (data.cihazlar||[]).length, 'oda:', (data.odalar||[]).length);
    // --- Genel CIHAZ: destroy-first, options doldur, select2 ---
    var $gc = $('#modal-view-event-add select.genel-cihaz-select');
    console.log('[GENEL PANEL] cihaz select bulundu:', $gc.length);
    if($gc.length){
        var curC = $gc.val();
        try { if($gc.hasClass('select2-hidden-accessible')) $gc.select2('destroy'); } catch(e){}
        $gc.empty().append('<option></option>');
        (data.cihazlar || []).forEach(function(c){ $gc.append(new Option(c.ad, c.id, false, false)); });
        if(curC) $gc.val(curC);
        try { $gc.select2({ placeholder:'Seçiniz', allowClear:true, width:'100%' }); } catch(e){}
    }
    // --- Genel ODA: destroy-first, tam liste, select2 ---
    var $go = $('#modal-view-event-add select.genel-oda-select');
    console.log('[GENEL PANEL] oda select bulundu:', $go.length);
    if($go.length){
        var curO = $go.val();
        try { if($go.hasClass('select2-hidden-accessible')) $go.select2('destroy'); } catch(e){}
        $go.empty().append('<option></option>');
        (data.odalar || []).forEach(function(o){ $go.append(new Option(o.ad, o.id, false, false)); });
        if(curO) $go.val(curO);
        try { $go.select2({ placeholder:'Seçiniz', allowClear:true, width:'100%' }); } catch(e){}
    }
    // --- Genel PERSONEL: destroy-first, options doldur, Tom Select ---
    var $gp = $('#modal-view-event-add select.genel-personel-select');
    console.log('[GENEL PANEL] personel select bulundu:', $gp.length, 'TomSelect var mi:', (typeof TomSelect !== 'undefined'));
    if($gp.length){
        var curP = '';
        try { curP = ($gp[0] && $gp[0].tomselect) ? $gp[0].tomselect.getValue() : $gp.val(); } catch(e){ curP = $gp.val(); }
        try { tomDestroyPersonel($gp); } catch(e){}
        $gp.empty().append('<option></option>');
        (data.personeller || []).forEach(function(p){ $gp.append(new Option(p.ad, p.id, false, false)); });
        if(curP) $gp.val(curP);
        var tsg = initPersonelTom($gp);
        if(curP && tsg){ try { tsg.setValue(curP, true); } catch(e){} }
        // Takvimden gelen row0 secimini genel panele yansit (gorunur + tutarli) — sessizce
        try {
            var $r0 = $('#yenirandevuekleform .hizmet-satiri').first();
            var r0p = $r0.find('select.personel-select').val();
            if(!curP && r0p && tsg){ tsg.setValue(r0p, true); }
        } catch(e){}
    }
    var $r0g = $('#yenirandevuekleform .hizmet-satiri').first();
    var r0c = $r0g.find('.cihaz-select').val();
    var r0o = $r0g.find('.oda-select').val();
    if(r0c && $gc.length && !$gc.val() && $gc.find('option[value="'+r0c+'"]').length){ $gc.val(r0c).trigger('change.select2'); }
    if(r0o && $go.length && !$go.val() && $go.find('option[value="'+r0o+'"]').length){ $go.val(r0o).trigger('change.select2'); }
    console.log('[GENEL PANEL] bitti — genel personel option sayisi:', $('#modal-view-event-add .genel-personel-select option').length, 'genel oda option:', $('#modal-view-event-add .genel-oda-select option').length);
    // Satir header'larina Ozellestir butonu
    ozellestirButonlariEkle();
}

function genelKaynakUygula(){
    var p = $('#modal-view-event-add select.genel-personel-select').val() || '';
    var c = $('#modal-view-event-add select.genel-cihaz-select').val() || '';
    var o = $('#modal-view-event-add select.genel-oda-select').val() || '';
    var cihazlar = (window.randevuModalData && window.randevuModalData.cihazlar) || [];
    var odalar   = (window.randevuModalData && window.randevuModalData.odalar) || [];
    $('#modal-view-event-add .hizmet-satiri').each(function(){
        var $row = $(this);
        if(p !== ''){
            var $p = $row.find('select.personel-select');
            if($p.length){
                if($p[0] && $p[0].tomselect){ try { $p[0].tomselect.setValue(p, true); } catch(e){} }
                $p.val(p).trigger('change');
            }
        }
        if(c !== ''){
            var $c = $row.find('.cihaz-select');
            if($c.length){
                if($c.find('option[value="'+c+'"]').length === 0){
                    var cc = cihazlar.find(function(x){ return String(x.id) === String(c); });
                    if(cc) $c.append(new Option(cc.ad, cc.id, false, false));
                }
                $c.val(c).trigger('change');
            }
        }
        if(o !== ''){
            var $o = $row.find('.oda-select');
            if($o.length){
                if($o.find('option[value="'+o+'"]').length === 0){
                    var oo = odalar.find(function(x){ return String(x.id) === String(o); });
                    if(oo) $o.append(new Option(oo.ad, oo.id, false, false));
                }
                $o.val(o).trigger('change');
            }
        }
    });
    try { updateRandevuOzeti(); } catch(e){}
}

// Her hizmet satirinin header'ina "Ozellestir" butonu ekle (yoksa)
function ozellestirButonlariEkle(){
    $('#modal-view-event-add .hizmet-satiri').each(function(){
        var $row = $(this);
        var $hdr = $row.find('.card-header').first();
        if(!$hdr.length || $hdr.find('.kaynak-ozellestir-btn').length) return;
        var $btn = $('<button type="button" class="btn btn-sm kaynak-ozellestir-btn" style="font-size:0.7rem;color:#6366f1;background:#eef2ff;border:1px solid #e0e7ff;border-radius:6px;padding:2px 8px;margin-right:6px;"><i class="fa fa-sliders"></i> Özelleştir</button>');
        var $sil = $hdr.find('.hizmet-sil').first();
        if($sil.length) $btn.insertBefore($sil); else $hdr.append($btn);
    });
}

// Ozellestir toggle
$(document).on('click', '#modal-view-event-add .kaynak-ozellestir-btn', function(e){
    e.preventDefault();
    var $row = $(this).closest('.hizmet-satiri');
    var acildi = !$row.hasClass('kaynak-acik');
    $row.toggleClass('kaynak-acik', acildi);
    $(this).css(acildi ? { color:'#fff', background:'#6366f1' } : { color:'#6366f1', background:'#eef2ff' });
    if(acildi){
        // Personel Tom Select'i temiz yeniden kur (gizliyken bozuk render olabiliyor)
        var $p = $row.find('select.personel-select');
        if($p.length){
            var cur = '';
            try { cur = ($p[0] && $p[0].tomselect) ? $p[0].tomselect.getValue() : $p.val(); } catch(err){ cur = $p.val(); }
            try { tomDestroyPersonel($p); } catch(err){}
            doldurSelect($p, (window.randevuModalData && window.randevuModalData.personeller) || []);
            if(cur) $p.val(cur);
            var ts = initPersonelTom($p);
            if(cur && ts){ try { ts.setValue(cur, true); } catch(err){} }
        }
    }
});

// Genel panel degisince tum satirlara uygula
$(document).on('change', '#modal-view-event-add .genel-personel-select, #modal-view-event-add .genel-cihaz-select, #modal-view-event-add .genel-oda-select', function(){
    genelKaynakUygula();
});

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
            @if(\App\Services\PersonelYetkiServisi::yetkiliYetkiVar(Auth::guard('isletmeyonetim')->user()->id, $isletme->id, 'randevu.kapanis_blok_ekle'))
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
    // Not: Personel secimi her zaman gorunur (cihaz/oda turunde de personel atanabilsin)
    window.randevuSecimleriniGuncelle = function() {
        var turu = parseInt($('#randevu_ayarina_gore').val());
        if (isNaN(turu)) return;
        var cihazGizli = (turu === 1 || turu === 3);
        var odaGizli   = (turu === 1 || turu === 2);
        $('#modal-view-event-add .secim-personel').css('display', '');
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
    function filtrelenmisHizmetler(personelId, cihazId, odaId){
        var v = window.randevuHizmetVerisi;
        if(!v) return { liste: [], fallback: false };
        var izinli = null;
        var hp = personelId ? (v.personel[personelId] || null) : null;
        var hc = cihazId ? (v.cihaz[cihazId] || null) : null;
        // Oda -> hizmet eslesmesi window.randevuModalData.odalar[].hizmet_idleri icinde
        var ho = null;
        if(odaId){
            var odaObj = ((window.randevuModalData && window.randevuModalData.odalar) || []).find(function(o){ return String(o.id) === String(odaId); });
            if(odaObj && Array.isArray(odaObj.hizmet_idleri) && odaObj.hizmet_idleri.length){
                ho = odaObj.hizmet_idleri.map(String);
            }
        }
        if(hp && hp.length) izinli = hp.slice();
        if(hc && hc.length) izinli = (izinli ? izinli : []).concat(hc);
        if(ho && ho.length) izinli = (izinli ? izinli : []).concat(ho);
        if(izinli && izinli.length){
            izinli = Array.from(new Set(izinli.map(String)));
            return { liste: v.tum.filter(function(h){ return izinli.indexOf(String(h.id)) > -1; }), fallback: false };
        }
        return { liste: v.tum.slice(), fallback: !!(personelId || cihazId || odaId) };
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
                // Hizmet degisince ayni satirdaki oda dropdown'ini hizmete gore filtrele
                try { odaSecimleriniHizmeteGoreYenile(idx); } catch(e){}
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
            var odaId = params.oda_id || '';
            var f = filtrelenmisHizmetler(personelId, cihazId, odaId);
            // fallback=true: secim yapildi ama o secime tanimli hizmet yok -> filtreleme YAPMA, tum hizmetleri goster
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

        // Hizmete gore (0): tum hizmetler yuklensin
        if(t === 0){
            $('.hizmet-select').each(function(){
                yukleHizmetler($(this), { hepsi: 1 });
            });
            return;
        }

        // Odaya gore (3): oda secimine gore hizmetleri filtrele
        if(t === 3){
            $(document).off('change.hizmetLoadOda', 'select[name="randevuodalariyeni[]"]')
                .on('change.hizmetLoadOda', 'select[name="randevuodalariyeni[]"]', function(){
                    var $row = $(this).closest('.hizmet-satiri');
                    var odaId = $(this).val() || '';
                    var $hizmet = $row.find('.hizmet-select');
                    if(!$hizmet.length) return;
                    if(odaId === ''){
                        // Oda secilmemis -> tum hizmetler
                        yukleHizmetler($hizmet, { hepsi: 1 });
                        return;
                    }
                    yukleHizmetler($hizmet, { oda_id: odaId });
                });
            // Ilk yukleme: mevcut oda secimi varsa filtrele, yoksa tum hizmetler
            $('.hizmet-select').each(function(){
                var $row = $(this).closest('.hizmet-satiri');
                var odaId = $row.find('select[name="randevuodalariyeni[]"]').val() || '';
                if(odaId) yukleHizmetler($(this), { oda_id: odaId });
                else yukleHizmetler($(this), { hepsi: 1 });
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
                        <div class="col-md-6 kaynak-kolon">
                            <div class="row g-2">
                                <div class="col-12 mb-1 secim-personel" style="{{ $__personel_style }}">
                                    <label class="form-label" style="font-size: 0.8rem;">Personel</label>
                                    <select name="randevupersonelleriyeni[]" class="form-control personel_secimi personel-select" data-index="${newIndex}" style="width: 100%; min-height: 38px; font-size: 0.85rem;">
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
                        <div class="col-md-6 mb-1 hizmet-kolon">
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
        // Select2'leri sadece yeni satir icin init et (personel-select Tom Select kullanir, atlanir)
        $yeniSatir.find('.opsiyonelSelect').not('.personel-select').each(function(){
            try { $(this).select2({ placeholder: 'Seçiniz', allowClear: true }); } catch(e){}
        });
        $yeniSatir.find('.custom-select2').not('.hizmet-select').each(function(){
            try { $(this).select2({ width: '100%' }); } catch(e){}
        });
        // NOT: personel-select Tom Select doldurRandevuSecenekleri() icindeki
        // initPersonelTomAll() tarafindan zaten init edildi; tekrari kaldirdik
        // (double init Tom Select'te getSettings.trim() hatasini tetikliyordu).
        $('.hizmet-sil[data-value="0"]').removeAttr('disabled');

        hizmetSatirSayisi++;
        updateRandevuOzeti();

        // Yeni satira Ozellestir butonu ekle + genel paneldeki kaynaklari uygula
        try { ozellestirButonlariEkle(); } catch(e){}
        try { genelKaynakUygula(); } catch(e){}

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

            // Satir silindikten sonra index'leri sirayla yeniden yaz (form submit'inde key2 ile hizmet eslesmesi bozulmasin)
            reorganizeRowIndexes();
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
            $(this).find('.yardimci-personel-select').attr('name', 'randevuyardimcipersonelleriyeni').attr('id', 'randevuyardimcipersonelleriyeni_' + index);
            $(this).find('.cihaz-select').attr('name', 'randevucihazlariyeni[]');
            $(this).find('.hizmet-select').attr('name', 'randevuhizmetleriyeni_' + index + '[]').attr('id', 'randevuhizmetleriyeni_' + index);
            $(this).find('.oda-select').attr('name', 'randevuodalariyeni[]');

            const detayContainer = $(this).find('[id^="hizmet-detaylari-"]');
            detayContainer.attr('id', 'hizmet-detaylari-' + index);

            // birlestir checkbox'in name'i index bagli (birlestir{index}) -> backend birlestir{key2+1} kontrol eder
            // Ekstra: birlestir{0} (yani ilk satir) backend'de zaten kontrol edilmez
            $(this).find('.birlestir-checkbox').attr('name', 'birlestir' + index).attr('data-index', index).attr('id', 'customCheck' + index);
            $(this).find('label.form-check-label').attr('for', 'customCheck' + index);

            hizmetSatirSayisi++;
        });
    }
    
    // Form gönderilmeden önce toplam süre ve fiyatları hesapla
    $('#yenirandevuekleform').on('submit', function(e) {
        e.preventDefault();

        // Satir indekslerini sirali (0..N-1) yap; backend foreach($randevupersonelleriyeni as $key2) ile $request->{"randevuhizmetleriyeni_{$key2}"} eslesmesi icin sart
        try { reorganizeRowIndexes(); } catch(err){ console.warn('reorganizeRowIndexes hata:', err); }

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
        // Genel kaynak paneli + Ozellestir butonlari: AYRI setTimeout (initSelect2 hata
        // verirse bile calissin). Buton ekleme ve panel hazirligi ayri try ile izole.
        setTimeout(() => {
            try { ozellestirButonlariEkle(); } catch(e){ console.warn('[OZELLESTIR btn] hata:', e); }
            try { genelPanelHazirla(); } catch(e){ console.warn('[GENEL PANEL] hazirla hatasi:', e); }
        }, 300);
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
                    <div class="col-md-5">
                        <label class="form-label small" style="font-size: 0.7rem;">Süre (dakika)</label>
                        <input type="number"
                               class="form-control form-control-sm hizmet-suresi"
                               name="hizmet_sureleri-${service.id}"
                               value="${sure}"
                               min="0"
                               step="5"
                               style="height: 26px; padding: 2px 6px; font-size: 0.75rem;">
                    </div>
                    <div class="col-md-5">
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
                    <div class="col-md-2">
                        <label class="form-label small" style="font-size: 0.7rem;" title="Paketten kac seans veya dakika dusulecek">Miktar</label>
                        <input type="number"
                               class="form-control form-control-sm hizmet-miktari"
                               name="hizmet_miktarlari-${service.id}"
                               value="1"
                               min="1"
                               step="1"
                               style="height: 26px; padding: 2px 6px; font-size: 0.75rem; text-align:center;">
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

    // Hizmet-select ve personel-select Tom Select kullanir: native val degisimi TS'i etkilemez, TS API ile temizle
    $('.hizmet-satiri[data-value="0"]').find('.hizmet-select, .personel-select').each(function(){
        var el = this;
        if(el.tomselect){ try { el.tomselect.clear(true); } catch(e){} }
    });
    $('.hizmet-satiri[data-value="0"]').find('select').not('.hizmet-select').not('.personel-select').val(null).trigger('change');
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

{{-- ============================================================ --}}
{{-- HIZMETLER ICIN ODA SECIM MODALI (statik — en altta, dogal stack)  --}}
{{-- showHizmetOdaAtamaModal() icerigini doldurur ve gosterir.       --}}
{{-- ============================================================ --}}
<div class="modal fade" id="hizmetOdaAtamaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
        <div class="modal-content" style="border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.35);">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);border-radius:12px 12px 0 0;">
                <h5 class="modal-title" style="color:#fff;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-door-open"></i> Hizmetler İçin Oda Seçimi
                </h5>
                <button type="button" class="close hizmet-oda-modal-kapat" style="color:#fff;opacity:0.9;">×</button>
            </div>
            <div class="modal-body" style="padding:16px 20px;">
                <p class="text-muted mb-3" id="hizmetOdaAtama_info" style="font-size:0.8rem;"></p>
                <div id="hizmetOdaAtama_kartlar"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;">
                <button type="button" class="btn btn-light btn-sm hizmet-oda-modal-kapat"><i class="fa fa-times"></i> Vazgeç</button>
                <button type="button" class="btn btn-success btn-sm" id="hizmet-oda-atama-onayla"><i class="fa fa-calendar-check-o"></i> Randevu Oluştur</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- HIZLI PAKET RANDEVU MODAL — inline personel/oda/cihaz secimi --}}
{{-- Paket secimi sonrasi forma yerlestirme yerine tek pencerede --}}
{{-- tum hizmetler icin atama yapilir ve direkt randevu olusturulur --}}
{{-- ============================================================ --}}
<div class="modal fade" id="paketHizliRandevuModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="z-index:100025;">
    <div class="modal-dialog modal-dialog-centered" style="max-width:900px;">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;box-shadow:0 25px 80px rgba(0,0,0,0.35);">
            <div class="modal-header" style="background:linear-gradient(135deg,#10b981 0%,#0ea5e9 100%);border:none;padding:16px 22px;">
                <h5 class="modal-title" style="color:#fff;display:flex;align-items:center;gap:10px;font-weight:700;">
                    <i class="fa fa-bolt"></i> Hızlı Paket Randevu
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.9;font-size:24px;text-shadow:none;">&times;</button>
            </div>
            <div class="modal-body" style="padding:18px 22px;max-height:60vh;overflow-y:auto;">
                <div id="paketHizli_baslik" class="alert" style="background:#f0f9ff;border:1px solid #bae6fd;color:#075985;padding:10px 14px;font-size:0.85rem;margin-bottom:14px;border-radius:8px;">
                    <i class="fa fa-info-circle"></i> <span id="paketHizli_baslikYazi"></span>
                </div>
                <div id="paketHizli_satirlar"></div>
                <div id="paketHizli_ozet" class="mt-3 p-2" style="background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;font-size:0.82rem;display:none;">
                    <strong>Toplam:</strong> <span id="paketHizli_ozetSayi">0</span> hizmet • <span id="paketHizli_ozetSure">0</span> dk
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:12px 22px;justify-content:flex-end;">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Vazgeç</button>
                <button type="button" class="btn btn-success btn-sm" id="paketHizli_olustur" style="font-weight:600;padding:7px 18px;"><i class="fa fa-calendar-check-o"></i> Randevu Oluştur</button>
            </div>
        </div>
    </div>
</div>