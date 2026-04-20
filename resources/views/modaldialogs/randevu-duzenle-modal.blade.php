{{-- Randevu Duzenle Modal - Modern (2026). Eski hali randevu-duzenle-modal-eski.blade.php'de yedek. --}}
<div id="randevu-duzenle-modal" class="modal modal-top fade calendar-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="h4" style="color:white">
                    <span>Randevu Düzenle</span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" style="padding: 1rem;">
                <form id="randevuduzenleform" method="POST" action="#">
                    {!!csrf_field()!!}
                    <input type="hidden" name="randevu_id" id="duzenlenecek_randevu_id">
                    @if($pageindex==2)
                    <input type="hidden" name="takvim_sayfasi" value="1">
                    @endif
                    <input type="hidden" name="sube" value="{{$isletme->id}}">

                    <!-- Temel Bilgiler -->
                    <div class="card mb-2">
                        <div class="card-header py-1">
                            <h6 class="mb-0" style="font-size: 0.9rem;">Temel Bilgiler</h6>
                        </div>
                        <div class="card-body p-2">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-2">
                                    <label class="form-label" style="font-size: 0.8rem;">@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                                    <select name="adsoyad" id="randevuduzenle_musteri_id" class="form-control" style="width: 100%; height: 32px; font-size: 0.85rem;"></select>
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
                                    <label class="form-label" style="visibility: hidden; font-size: 0.8rem;">Yeni müşteri</label>
                                    <button class="btn btn-outline-primary w-100 yanitsiz_musteri_ekleme" type="button" data-toggle="modal" data-target="#musteri-bilgi-modal" style="padding: 4px 8px; font-size: 0.8rem; height: 32px;">
                                        Yeni @if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                                    <label class="form-label" style="font-size: 0.8rem;">Tarih</label>
                                    <input required placeholder="Tarih" type="text" class="form-control" name="tarih" id="randevuduzenle_tarih" autocomplete="off" style="height: 32px; font-size: 0.85rem;" />
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                                    <label class="form-label" style="font-size: 0.8rem;">Saat</label>
                                    <select name="saat" class="form-control" id="randevuduzenle_saat" style="height: 32px; font-size: 0.85rem;">
                                        @for($j = strtotime(date('07:00')) ; $j < strtotime(date('23:15')); $j+=(15*60))
                                        <option value="{{date('H:i',$j)}}:00">{{date('H:i',$j)}}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-size: 0.8rem;">Personel Notu</label>
                                    <textarea class="form-control" name="personel_notu" id="randevuduzenle_personel_notu" placeholder="Randevu ile ilgili notlarınızı buraya yazın..." rows="2" style="min-height: 60px; font-size: 0.85rem;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hizmetler -->
                    <div class="card mb-2">
                        <div class="card-header py-1 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0" style="font-size: 0.9rem;">Hizmetler</h6>
                            <button type="button" id="bir_hizmet_daha_ekle_randevu_duzenleme" class="btn btn-outline-success btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                <i class="icon-copy fi-plus"></i> Yeni Hizmet Ekle
                            </button>
                        </div>
                        <div class="card-body p-2">
                            {{-- Hizmet satirlari custom.js tarafindan bu container'a HTML olarak eklenir --}}
                            <div class="hizmetler_bolumu_randevu_duzenleme" style="overflow: visible;"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 8px 16px;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="padding: 5px 12px; font-size: 0.85rem;">
                    <i class="icon-copy fa fa-times"></i> İptal
                </button>
                <button type="submit" form="randevuduzenleform" class="btn btn-success btn-sm" style="padding: 5px 12px; font-size: 0.85rem;">
                    <i class="icon-copy fa fa-save"></i> Randevuyu Güncelle
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#randevu-duzenle-modal .modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
#randevu-duzenle-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
}
#randevu-duzenle-modal .modal-header .close {
    color: #fff;
    opacity: 0.85;
    text-shadow: none;
    font-size: 1.5rem;
    margin: 0;
    padding: 0 6px;
}
#randevu-duzenle-modal .modal-header .close:hover { opacity: 1; }
#randevu-duzenle-modal .modal-body { max-height: calc(100vh - 180px); overflow-y: auto; }
#randevu-duzenle-modal .card { border: 1px solid #e5e7eb; border-radius: 8px; }
#randevu-duzenle-modal .card-header { background: #f8f9fa; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
#randevu-duzenle-modal .card-body { padding: 10px 12px; }
#randevu-duzenle-modal label.form-label { font-weight: 500; color: #495057; margin-bottom: 2px; }
#randevu-duzenle-modal .form-control { border: 1px solid #d1d5db; border-radius: 6px; }
#randevu-duzenle-modal .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
/* hizmet satirlari (backend HTML snippet) - modern kart gorunumu */
#randevu-duzenle-modal .hizmetler_bolumu_randevu_duzenleme > .row {
    background: #fafbff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin: 6px 0;
    padding: 8px;
}
#randevu-duzenle-modal .hizmetler_bolumu_randevu_duzenleme > .row:hover { background: #f4f6ff; }
#randevu-duzenle-modal .select2-container--default .select2-selection--single,
#randevu-duzenle-modal .select2-container--default .select2-selection--multiple {
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    min-height: 32px !important;
}
</style>
