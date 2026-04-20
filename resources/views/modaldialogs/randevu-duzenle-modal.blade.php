{{-- Randevu Duzenle Modal - Ekleme modali ile birebir ayni yapi. Eski sade hali randevu-duzenle-modal-eski.blade.php'de. --}}
@php
    $__dz_takvim_turu = $isletme->randevu_takvim_turu ?? 0;
    $__dz_personel_style = in_array($__dz_takvim_turu, [2, 3]) ? 'display:none;' : '';
    $__dz_cihaz_style    = in_array($__dz_takvim_turu, [1, 3]) ? 'display:none;' : '';
    $__dz_oda_style      = in_array($__dz_takvim_turu, [1, 2]) ? 'display:none;' : '';
    $__dz_yardimci_style = 'display:none;';
@endphp
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
                                            <label class="form-label" style="font-size: 0.8rem;">@if($isletme->salon_turu_id==15 || $isletme->salon_turu_id==28||$isletme->salon_turu_id==29) Danışan @else Müşteri @endif</label>
                                            <select name="adsoyad" id="randevuduzenle_musteri_id" class="form-control opsiyonelSelect musteri_secimi" style="width: 100%; height: 32px; font-size: 0.85rem;">
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
                                            <input required placeholder="Tarih" type="text" class="form-control" name="tarih" id="randevuduzenle_tarih" autocomplete="off" style="height: 32px; font-size: 0.85rem;" />
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-12 mb-2">
                                            <label class="form-label" style="font-size: 0.8rem;">Saat</label>
                                            <select name="saat" class="form-control" id="randevuduzenle_saat" style="height: 32px; font-size: 0.85rem;">
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
                                    <button type="button" id="bir_hizmet_daha_ekle_randevu_duzenleme" class="btn btn-outline-success btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                        <i class="icon-copy fi-plus"></i> Yeni Hizmet Ekle
                                    </button>
                                </div>
                                <div class="card-body p-2 hizmetler_bolumu_randevu_duzenleme" style="overflow: visible;">
                                    {{-- Hizmet satirlari JS ile dinamik olarak bu container'a eklenir (ekleme modalinin template'i ile ayni) --}}
                                </div>
                            </div>

                            <!-- Notlar -->
                            <div class="card mb-2">
                                <div class="card-header py-1">
                                    <h6 class="mb-0" style="font-size: 0.9rem;">Notlar</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="col-12">
                                        <label class="form-label" style="font-size: 0.8rem;">Personel Notu</label>
                                        <textarea class="form-control" name="personel_notu" id="randevuduzenle_personel_notu" placeholder="Randevu ile ilgili notlarınızı buraya yazın..." rows="2" style="min-height: 60px; font-size: 0.85rem;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sağ Taraf: Özet -->
                        <div class="col-md-4">
                            <div class="card mb-2">
                                <div class="card-header py-1">
                                    <h6 class="mb-0" style="font-size: 0.9rem;">Randevu Özeti</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="randevu-duzenle-ozeti" style="min-height: 180px; font-size: 0.85rem;">
                                        <div class="text-center text-muted py-3">
                                            <i class="fa fa-edit fa-lg mb-2" style="opacity: 0.3;"></i>
                                            <p class="mb-1 fw-bold" style="font-size: 0.9rem;">Yükleniyor...</p>
                                            <p class="small mb-0" style="font-size: 0.75rem;">Randevu bilgileri getiriliyor</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 8px 16px;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="padding: 5px 12px; font-size: 0.85rem;">
                    <i class="icon-copy fa fa-times"></i> İptal
                </button>
                <button type="submit" form="randevuduzenleform" class="btn btn-success btn-sm" id="randevu-guncelle-btn" style="padding: 5px 12px; font-size: 0.85rem;">
                    <i class="icon-copy fa fa-save"></i> Randevuyu Güncelle
                </button>
            </div>
        </div>
    </div>
</div>


<style>
/* Modal ortalama: Bootstrap default davranisi zorla */
#randevu-duzenle-modal {
    padding-right: 0 !important;
    padding-left: 0 !important;
    display: none;
}
#randevu-duzenle-modal.show {
    display: block !important;
}
#randevu-duzenle-modal .modal-dialog {
    max-width: 1200px !important;
    width: auto !important;
    margin: 1.75rem auto !important;
    min-height: calc(100% - 3.5rem);
    display: flex;
    align-items: center;
}
#randevu-duzenle-modal .modal-content {
    width: 100%;
    border-radius: 8px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
@media (min-width: 1250px) {
    #randevu-duzenle-modal .modal-dialog { max-width: 1200px !important; }
}
#randevu-duzenle-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
}
#randevu-duzenle-modal .modal-header .close { color:#fff; opacity:0.85; text-shadow:none; font-size:1.5rem; }
#randevu-duzenle-modal .modal-header .close:hover { opacity:1; }
#randevu-duzenle-modal .modal-body { max-height: calc(100vh - 180px); overflow-y: auto; }
#randevu-duzenle-modal .card { border: 1px solid #e5e7eb; border-radius: 8px; }
#randevu-duzenle-modal .card-header { background:#f8f9fa; padding:8px 12px; border-bottom:1px solid #e5e7eb; }
#randevu-duzenle-modal label.form-label { font-weight:500; color:#495057; margin-bottom:2px; }
#randevu-duzenle-modal .form-control { border: 1px solid #d1d5db; border-radius: 6px; }
#randevu-duzenle-modal .form-control:focus { border-color:#6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }

/* Tom Select stilleri — ekleme modali ile birebir ayni */
#randevu-duzenle-modal .ts-wrapper { min-height: 40px; }
#randevu-duzenle-modal .ts-wrapper.multi .ts-control {
    min-height: 40px !important;
    padding: 4px 8px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    background: #fff !important;
    flex-wrap: wrap !important;
}
#randevu-duzenle-modal .ts-wrapper.focus .ts-control { border-color:#6366f1 !important; box-shadow:0 0 0 3px rgba(99,102,241,0.15) !important; }
#randevu-duzenle-modal .ts-wrapper.multi .ts-control > .item { background:#eef2ff !important; color:#4338ca !important; border:1px solid #c7d2fe !important; border-radius:6px !important; padding:3px 26px 3px 10px !important; margin:2px 3px 2px 0 !important; font-size:0.78rem !important; position:relative; }
#randevu-duzenle-modal .ts-wrapper.plugin-remove_button .item .remove { color:#6366f1 !important; border-left:none !important; padding:0 6px !important; line-height:1 !important; font-weight:700 !important; }
#randevu-duzenle-modal .ts-wrapper.plugin-remove_button .item .remove:hover { background:#6366f1 !important; color:#fff !important; border-radius:0 4px 4px 0 !important; }
.ts-dropdown, .ts-dropdown-content { background:#fff !important; }
#randevu-duzenle-modal .ts-dropdown {
    background:#fff !important;
    border:2px solid #e5e7eb !important;
    border-radius:8px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
    margin-top:4px;
    z-index: 100000;
    opacity:1 !important;
}
#randevu-duzenle-modal .ts-dropdown .active { background:#6366f1 !important; color:#fff !important; }
#randevu-duzenle-modal .ts-dropdown .option { padding:8px 12px !important; background:#fff !important; }
#randevu-duzenle-modal .ts-dropdown .option:hover { background:#f3f4f6 !important; }
#randevu-duzenle-modal .ts-wrapper.disabled .ts-control { background:#f9fafb !important; opacity:0.7; }

/* Hizmet satiri görsel (ekleme modali ile ayni) */
#randevu-duzenle-modal .hizmet-satiri-duzenle {
    background: #fff;
}
#randevu-duzenle-modal .hizmet-satiri-duzenle .card-header {
    background-color: #f8f9fa !important;
}

/* Select2 opsiyonel select'ler - ekleme modali ile ayni */
#randevu-duzenle-modal .select2-container--default .select2-selection--single {
    height: 30px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
}
#randevu-duzenle-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
    padding-left: 10px !important;
    font-size: 0.8rem !important;
}
#randevu-duzenle-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px !important;
}
</style>

<script>
(function(){
    window.duzenleHizmetIndex = 0;

    // Kendi fetch fonksiyonumuz - ekleme modalinin scope'una bagimsiz
    function duzenleFetchHizmetVerisi(onReady){
        if(window.randevuHizmetVerisi){ if(onReady) onReady(); return; }
        var subeId = $('input[name=sube]', '#randevuduzenleform').val() || '{{$isletme->id}}';
        $.ajax({
            url: '/isletmeyonetim/randevu-modal-hizmet-verisi',
            type: 'GET',
            dataType: 'json',
            data: { sube: subeId },
            success: function(resp){
                window.randevuHizmetVerisi = {
                    tum: (resp && resp.tum_hizmetler) ? resp.tum_hizmetler : [],
                    personel: (resp && resp.personel_hizmet_map) ? resp.personel_hizmet_map : {},
                    cihaz: (resp && resp.cihaz_hizmet_map) ? resp.cihaz_hizmet_map : {}
                };
                if(onReady) onReady();
            },
            error: function(xhr){
                console.error('Hizmet verisi yuklenemedi:', xhr.status);
                if(onReady) onReady();
            }
        });
    }

    // Custom.js submit handler'ini bypass et - form scope'lu dogru validation yap
    document.addEventListener('submit', function(e){
        if(!e.target || e.target.id !== 'randevuduzenleform') return;
        // Custom.js validation bug'i: cihaz selector form scope degil, document-wide. Override et.
        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();

        var formScope = $('#randevuduzenleform');
        var personelveyacihasecili = true;
        formScope.find('select[name="randevupersonelleriyeni[]"]').each(function(index){
            var $cihaz = formScope.find('select[name="randevucihazlariyeni[]"]').eq(index);
            var $oda = formScope.find('select[name="randevuodalariyeni[]"]').eq(index);
            if($(this).val() == '' && $cihaz.val() == '' && $oda.val() == ''){
                personelveyacihasecili = false;
            }
        });
        if(!personelveyacihasecili){
            swal({ type:'warning', title:'Uyarı', text:'Her hizmet satırı için en az bir personel, cihaz veya oda seçin', showConfirmButton:false, timer:3000 });
            return;
        }

        // AJAX - custom.js'in yaptigi ile ayni (validation atlanmis)
        $('#preloader').show();
        $.ajax({
            type: 'POST',
            url: '/isletmeyonetim/randevuguncelle',
            dataType: 'json',
            data: formScope.serialize(),
            success: function(result){
                $('#preloader').hide();
                if(result.cakismavar){
                    swal({
                        type:'warning',
                        title:"<h2 style='font-size:40px;font-weight:bold;color:#fff'>Uyarı</h2>",
                        background:'#ff0000',
                        html:"<p style='color:#fff;font-size:20px'>Bu randevu aşağıdakilerle çakışmaktadır</p>"+result.cakismavar+"<p style='color:#fff;font-size:20px;padding:10px;border:1px solid #fff;border-radius:10px;margin:0 20px 0 20px'>Yine de kayıt etmek istiyor musunuz?</p>",
                        showCancelButton:true,
                        confirmButtonText:'Randevuyu Güncelle',
                        cancelButtonText:'Vazgeç'
                    }).then(function(res){
                        if(res.value){
                            $.ajax({
                                type:'POST',
                                url:'/isletmeyonetim/randevuguncelle',
                                dataType:'json',
                                data: formScope.serialize() + '&cakisanrandevuekle=1',
                                beforeSend: function(){ $('#preloader').show(); },
                                success: function(r){
                                    $('#preloader').hide();
                                    swal({type:'success',title:'Başarılı',html:r.success,showConfirmButton:false,timer:r.timer||2500});
                                    $('#randevu-duzenle-modal').modal('hide');
                                    if($('#calendar').length) takvimyukle(false,false);
                                },
                                error: function(xhr){ $('#preloader').hide(); swal({type:'error',title:'Hata',text:'Güncelleme başarısız: '+xhr.status,showConfirmButton:false,timer:2500}); }
                            });
                        }
                    });
                } else {
                    swal({type:'success',title:'Başarılı',html:result.success,showConfirmButton:false,timer:result.timer||2500});
                    $('#randevu-duzenle-modal').modal('hide');
                    if($('#calendar').length) takvimyukle(true,false);
                }
            },
            error: function(xhr){
                $('#preloader').hide();
                swal({type:'error',title:'Hata',text:'Güncelleme başarısız: '+xhr.status,showConfirmButton:false,timer:3000});
                console.error(xhr.responseText);
            }
        });
    }, true); // capture phase

    // Eski custom.js handler'i capture phase ile bypass et (jQuery bubble handler'lardan once calisir)
    document.addEventListener('click', function(e){
        var target = e.target.closest && e.target.closest('[name="randevu_duzenle"]');
        if(!target) return;
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        var randevuId = target.getAttribute('data-value');
        console.log('[DUZENLE] capture click', randevuId);
        if(!randevuId){ return; }
        window.duzenlenecekRandevuId = randevuId;
        $('#duzenlenecek_randevu_id').val(randevuId);
        $('.hizmetler_bolumu_randevu_duzenleme').empty();
        $('#randevu-duzenle-modal').modal('show');
    }, true); // capture = true, bubble handler'lardan once calisir

    // Ham veri pencereye atandi - hizmetler/personel/cihaz/oda cache'leri ekleme modalinda
    // zaten hazirlaniyor (window.randevuHizmetVerisi, window.randevuModalData)

    // Yeni hizmet satiri olustur (template dependency yok, direkt HTML string)
    var DZ_PERSONEL_STYLE = @json($__dz_personel_style);
    var DZ_CIHAZ_STYLE = @json($__dz_cihaz_style);
    var DZ_ODA_STYLE = @json($__dz_oda_style);

    function duzenleYeniHizmetSatiri(){
        var idx = window.duzenleHizmetIndex++;
        var num = $('.hizmet-satiri-duzenle').length + 1;
        var html =
            '<div class="hizmet-satiri-duzenle card mb-2" data-value="'+idx+'" style="border: 1px solid #dee2e6;">' +
              '<div class="card-header py-1 d-flex justify-content-between align-items-center" style="padding:4px 8px; background:#f8f9fa;">' +
                '<span class="fw-bold" style="font-size:0.85rem;">Hizmet #'+num+'</span>' +
                '<button type="button" name="hizmet_formdan_sil" data-value="'+idx+'" class="btn btn-sm btn-danger duzenle-hizmet-sil" style="padding:2px 6px; font-size:0.7rem;">' +
                  '<i class="icon-copy fa fa-trash"></i> Sil</button>' +
              '</div>' +
              '<div class="card-body p-2"><div class="row g-2">' +
                '<div class="col-md-6"><div class="row g-2">' +
                  '<div class="col-12 mb-1 secim-personel" style="'+DZ_PERSONEL_STYLE+'">' +
                    '<label class="form-label" style="font-size:0.8rem;">Personel</label>' +
                    '<select name="randevupersonelleriyeni[]" class="form-control opsiyonelSelect personel-select duzenle-personel-select" data-index="'+idx+'" style="width:100%;height:30px;font-size:0.8rem;"><option></option></select>' +
                  '</div>' +
                  '<div class="col-12 mb-1 secim-cihaz" style="'+DZ_CIHAZ_STYLE+'">' +
                    '<label class="form-label" style="font-size:0.8rem;">Cihaz</label>' +
                    '<select name="randevucihazlariyeni[]" class="form-control opsiyonelSelect cihaz-select duzenle-cihaz-select" data-index="'+idx+'" style="width:100%;height:30px;font-size:0.8rem;"><option></option></select>' +
                  '</div>' +
                  '<div class="col-12 mb-1 secim-oda" style="'+DZ_ODA_STYLE+'">' +
                    '<label class="form-label" style="font-size:0.8rem;">Oda</label>' +
                    '<select name="randevuodalariyeni[]" class="form-control opsiyonelSelect oda-select duzenle-oda-select" data-index="'+idx+'" style="width:100%;height:30px;font-size:0.8rem;"><option></option></select>' +
                  '</div>' +
                '</div></div>' +
                '<div class="col-md-6 mb-1">' +
                  '<label class="form-label" style="font-size:0.8rem;">Hizmetler (Çoklu Seçim)</label>' +
                  '<select name="randevuhizmetleriyeni[]" id="duzenlerandevuhizmetleriyeni_'+idx+'" multiple class="form-control duzenle-hizmet-select" data-index="'+idx+'" style="width:100%;font-size:0.8rem;min-height:30px;"><option></option></select>' +
                '</div>' +
                '<div class="col-12 mt-1 duzenle-hizmet-detaylari" id="duzenle-hizmet-detaylari-'+idx+'" style="font-size:0.8rem;"></div>' +
              '</div></div>' +
            '</div>';
        var $el = $(html);
        $('.hizmetler_bolumu_randevu_duzenleme').append($el);
        // Personel/cihaz/oda options doldur (ekleme modalindaki mantik)
        if(typeof window.doldurRandevuSecenekleri === 'function'){
            // Sadece duzenle select'lerini doldur
            var rmd = window.randevuModalData || {};
            $el.find('.duzenle-personel-select').each(function(){
                var $s = $(this); $s.empty().append('<option></option>');
                (rmd.personeller || []).forEach(function(p){ $s.append(new Option(p.ad, p.id)); });
            });
            $el.find('.duzenle-cihaz-select').each(function(){
                var $s = $(this); $s.empty().append('<option></option>');
                (rmd.cihazlar || []).forEach(function(c){ $s.append(new Option(c.ad, c.id)); });
            });
            $el.find('.duzenle-oda-select').each(function(){
                var $s = $(this); $s.empty().append('<option></option>');
                (rmd.odalar || []).forEach(function(o){ $s.append(new Option(o.ad, o.id)); });
            });
        }
        // Select2 init (personel/cihaz/oda)
        $el.find('.opsiyonelSelect').each(function(){
            try { $(this).select2({ placeholder: 'Seçiniz', allowClear: true, dropdownParent: $('#randevu-duzenle-modal') }); } catch(e){}
        });
        // Tom Select init (hizmet)
        var $hz = $el.find('.duzenle-hizmet-select');
        if($hz.length && window.TomSelect){
            var ph = 'Hizmet seçin...';
            try {
                var ts = new TomSelect($hz[0], {
                    plugins: ['remove_button'],
                    placeholder: ph,
                    allowEmptyOption: true,
                    persist: false,
                    maxOptions: null,
                    closeAfterSelect: false,
                    searchField: ['text', 'kategori'],
                    render: {
                        option: function(data, escape){
                            var kat = data.kategori ? '<div style="font-size:.72rem;color:#6b7280;">' + escape(data.kategori) + '</div>' : '';
                            return '<div><div style="font-weight:500;">'+escape(data.text)+'</div>'+kat+'</div>';
                        },
                        item: function(data, escape){ return '<div>'+escape(data.text)+'</div>'; },
                        no_results: function(){ return '<div style="padding:12px;color:#6b7280;">Hizmet bulunamadı</div>'; }
                    },
                    onChange: function(){
                        var idx = $hz.data('index');
                        duzenleRenderHizmetDetay(idx);
                        duzenleUpdateOzeti();
                    }
                });
                // Options'a hizmet verilerini yukle
                duzenleHizmetSelectOptionsYukle($hz, ts);
            } catch(e){ console.warn('Tom Select init hata:', e); }
        }
        return $el;
    }

    // Hizmet select options'i doldur (tum hizmetler veya personel/cihaz filtreli)
    function duzenleHizmetSelectOptionsYukle($hz, tsParam, personelId, cihazId){
        var ts = tsParam || ($hz[0] && $hz[0].tomselect);
        if(!ts || !window.randevuHizmetVerisi) return;
        var v = window.randevuHizmetVerisi;
        var liste;
        if(personelId || cihazId){
            var izinli = [];
            if(personelId && v.personel[personelId]) izinli = izinli.concat(v.personel[personelId]);
            if(cihazId && v.cihaz[cihazId]) izinli = izinli.concat(v.cihaz[cihazId]);
            izinli = Array.from(new Set(izinli.map(String)));
            if(izinli.length){
                liste = v.tum.filter(function(h){ return izinli.indexOf(String(h.id)) > -1; });
            } else {
                liste = v.tum.slice(); // atama yoksa hepsi
            }
        } else {
            liste = v.tum.slice();
        }
        ts.clearOptions();
        liste.forEach(function(h){
            ts.addOption({ value: h.id, text: h.ad, kategori: h.kategori || '', sure: h.sure || 0, fiyat: h.fiyat || 0 });
        });
        ts.refreshOptions(false);
    }

    // Hizmet secimi sonrasi sure/fiyat input'larini render et (ekleme modali gibi)
    function duzenleRenderHizmetDetay(index){
        var $sel = $('.duzenle-hizmet-select[data-index="'+index+'"]');
        if(!$sel.length) return;
        var ts = $sel[0] && $sel[0].tomselect;
        var $container = $('#duzenle-hizmet-detaylari-'+index);
        $container.empty();
        if(!ts) return;
        ts.items.forEach(function(id){
            var opt = ts.options[id] || {};
            var ad = opt.text || '';
            var sure = Number(opt.sure) || 0;
            var fiyat = Number(opt.fiyat) || 0;
            var html =
                '<div class="hizmet-detay-item" style="background:#fafbff;border:1px solid #e5e7eb;border-radius:6px;padding:8px;margin-top:6px;">' +
                  '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">' +
                    '<span style="font-size:0.82rem;font-weight:600;color:#111827;">'+ad+'</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger duzenle-hizmet-kaldir" data-index="'+index+'" data-service-id="'+id+'" style="padding:1px 6px;font-size:0.7rem;">' +
                      '<i class="fa fa-times"></i></button>' +
                  '</div>' +
                  '<div class="row g-1">' +
                    '<div class="col-md-6">' +
                      '<label style="font-size:0.7rem;">Süre (dakika)</label>' +
                      '<input type="number" class="form-control form-control-sm hizmet-suresi" name="hizmet_suresi[]" value="'+sure+'" min="0" step="5" style="height:26px;padding:2px 6px;font-size:0.75rem;">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                      '<label style="font-size:0.7rem;">Fiyat (₺)</label>' +
                      '<input type="number" class="form-control form-control-sm hizmet-fiyati" name="hizmet_fiyat[]" value="'+fiyat+'" min="0" step="0.01" style="height:26px;padding:2px 6px;font-size:0.75rem;">' +
                    '</div>' +
                  '</div>' +
                '</div>';
            $container.append(html);
        });
    }

    // Detay icindeki hizmet kaldirma butonu
    $(document).on('click', '.duzenle-hizmet-kaldir', function(){
        var idx = $(this).data('index');
        var sid = $(this).data('service-id');
        var $sel = $('.duzenle-hizmet-select[data-index="'+idx+'"]');
        var ts = $sel[0] && $sel[0].tomselect;
        if(ts) ts.removeItem(String(sid));
    });

    // Sure/fiyat degisince ozeti guncelle
    $(document).on('input', '#randevu-duzenle-modal .hizmet-suresi, #randevu-duzenle-modal .hizmet-fiyati', function(){
        duzenleUpdateOzeti();
    });

    // Duzenleme satirindaki personel/cihaz degisince hizmet select'i yenile
    $(document).on('change', '#randevu-duzenle-modal .duzenle-personel-select, #randevu-duzenle-modal .duzenle-cihaz-select', function(){
        var t = window.randevuTakvimTuru;
        if(t === 0 || t === 3) return; // filtre yok
        var $row = $(this).closest('.hizmet-satiri-duzenle');
        var personelId = $row.find('.duzenle-personel-select').val() || '';
        var cihazId = $row.find('.duzenle-cihaz-select').val() || '';
        var $hz = $row.find('.duzenle-hizmet-select');
        if($hz.length) duzenleHizmetSelectOptionsYukle($hz, null, personelId, cihazId);
    });

    // Yeni satir ekle butonu (custom.js'in eski handler'ini bypass et - capture phase)
    document.addEventListener('click', function(e){
        var target = e.target.closest && e.target.closest('#bir_hizmet_daha_ekle_randevu_duzenleme');
        if(!target) return;
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        duzenleYeniHizmetSatiri();
    }, true);

    // Satir sil
    $(document).on('click', '#randevu-duzenle-modal .duzenle-hizmet-sil', function(){
        $(this).closest('.hizmet-satiri-duzenle').remove();
        duzenleUpdateOzeti();
    });

    // Ozet guncelle - detay input'lari varsa onlardan oku, yoksa option metadata'sindan
    function duzenleUpdateOzeti(){
        var toplamSure = 0, toplamFiyat = 0, hizmetSayisi = 0;
        $('#randevu-duzenle-modal .hizmet-satiri-duzenle').each(function(){
            var $row = $(this);
            var $hz = $row.find('.duzenle-hizmet-select');
            var ts = $hz[0] && $hz[0].tomselect;
            if(!ts) return;
            var $sureInputs = $row.find('input.hizmet-suresi');
            var $fiyatInputs = $row.find('input.hizmet-fiyati');
            ts.items.forEach(function(id, i){
                var opt = ts.options[id];
                if(!opt) return;
                // Detay input'lari varsa onlardan (kullanicinin degistirdigi deger), siradaki input
                var sure = $sureInputs.eq(i).length ? Number($sureInputs.eq(i).val()) : Number(opt.sure || 0);
                var fiyat = $fiyatInputs.eq(i).length ? Number($fiyatInputs.eq(i).val()) : Number(opt.fiyat || 0);
                toplamSure += sure;
                toplamFiyat += fiyat;
                hizmetSayisi++;
            });
        });
        var html;
        if(hizmetSayisi === 0){
            html = '<div class="text-center text-muted py-3"><i class="fa fa-edit fa-lg mb-2" style="opacity:.3;"></i><p class="mb-1 fw-bold">Hizmet seçilmedi</p></div>';
        } else {
            html = '<div class="d-flex justify-content-between py-1"><strong>Hizmet sayısı:</strong><span>'+hizmetSayisi+'</span></div>' +
                   '<div class="d-flex justify-content-between py-1"><strong>Toplam süre:</strong><span>'+toplamSure+' dk</span></div>' +
                   '<div class="d-flex justify-content-between py-1"><strong>Toplam tutar:</strong><span style="color:#10b981;font-weight:700;">'+toplamFiyat.toFixed(2)+' ₺</span></div>';
        }
        $('#randevu-duzenle-ozeti').html(html);
    }

    // Modal acilisi - randevu ID window.duzenlenecekRandevuId veya #duzenlenecek_randevu_id'den
    $('#randevu-duzenle-modal').on('show.bs.modal', function(e){
        console.log('[DUZENLE] show.bs.modal tetiklendi');
        // Global degiskeni once dene (kesin kayipsiz)
        var randevuId = window.duzenlenecekRandevuId || $('#duzenlenecek_randevu_id').val();
        console.log('[DUZENLE] yakalanan randevu_id:', randevuId, 'global:', window.duzenlenecekRandevuId, 'input:', $('#duzenlenecek_randevu_id').val(), 'input elem sayisi:', $('#duzenlenecek_randevu_id').length);

        // Form'u temizle
        $('#randevuduzenleform')[0].reset();
        // Reset sonrasi hidden randevu_id'yi geri yaz
        $('#duzenlenecek_randevu_id').val(randevuId);
        $('.hizmetler_bolumu_randevu_duzenleme').empty();
        window.duzenleHizmetIndex = 0;
        $('#randevu-duzenle-ozeti').html('<div class="text-center text-muted py-3"><p>Yükleniyor...</p></div>');

        // Hizmet verisi cache - kendi fetch'imizi kullan (ekleme modalina bagimli degil)
        duzenleFetchHizmetVerisi();

        if(!randevuId) return;

        // Randevu detayini cek
        $.ajax({
            url: '/isletmeyonetim/randevu-duzenle-json',
            type: 'GET',
            dataType: 'json',
            data: { randevu_id: randevuId, sube: $('input[name="sube"]', '#randevuduzenleform').val() },
            success: function(data){
                console.log('[DUZENLE] randevu-duzenle-json success:', data);
                if(!data || data.error){ console.warn(data); return; }

                $('#duzenlenecek_randevu_id').val(data.randevu_id);
                $('#randevuduzenle_tarih').val(data.tarih);
                $('#randevuduzenle_saat').val(data.saat);
                $('#randevuduzenle_personel_notu').val(data.personel_notu);

                // Musteri select'i — mevcut musteri select akisiyla set et
                var $m = $('#randevuduzenle_musteri_id');
                if($m.length && data.musteri_id){
                    if($m.find('option[value="'+data.musteri_id+'"]').length === 0){
                        $m.append(new Option(data.musteri_adi || ('#'+data.musteri_id), data.musteri_id, true, true));
                    }
                    $m.val(data.musteri_id).trigger('change');
                }

                // Hizmet verisi cache hazir degilse bekle
                var hizmetleriYukle = function(){
                    console.log('[DUZENLE] hizmetleriYukle basladi, hizmet sayisi:', (data.hizmetler || []).length);
                    if(!data.hizmetler || !Array.isArray(data.hizmetler)){
                        console.warn('[DUZENLE] hizmetler array degil:', data.hizmetler);
                        duzenleUpdateOzeti();
                        return;
                    }
                    data.hizmetler.forEach(function(h){
                        var $row = duzenleYeniHizmetSatiri();
                        if(!$row) return;
                        // Set personel, cihaz, oda
                        setTimeout(function(){
                            if(h.personel_id) $row.find('.duzenle-personel-select').val(h.personel_id).trigger('change');
                            if(h.cihaz_id) $row.find('.duzenle-cihaz-select').val(h.cihaz_id).trigger('change');
                            if(h.oda_id) $row.find('.duzenle-oda-select').val(h.oda_id).trigger('change');
                            // Hizmet select - Tom Select
                            var $hz = $row.find('.duzenle-hizmet-select');
                            var ts = $hz[0] && $hz[0].tomselect;
                            if(ts && h.hizmet_id){
                                if(!ts.options[h.hizmet_id] && window.randevuHizmetVerisi){
                                    var hData = window.randevuHizmetVerisi.tum.find(function(x){ return String(x.id) === String(h.hizmet_id); });
                                    if(hData){ ts.addOption({ value: hData.id, text: hData.ad, kategori: hData.kategori || '', sure: hData.sure, fiyat: hData.fiyat }); }
                                }
                                ts.addItem(String(h.hizmet_id), false); // false=event fire et -> onChange -> detay render
                                // Randevudan gelen gercek sure/fiyat degerlerini detay input'larina yaz
                                setTimeout(function(){
                                    // Son eklenen sure/fiyat input'u (bu hizmet icin render edilen)
                                    var $sureInputs = $row.find('input.hizmet-suresi');
                                    var $fiyatInputs = $row.find('input.hizmet-fiyati');
                                    var $lastSure = $sureInputs.last();
                                    var $lastFiyat = $fiyatInputs.last();
                                    if($lastSure.length && h.sure_dk) $lastSure.val(h.sure_dk);
                                    if($lastFiyat.length && h.fiyat) $lastFiyat.val(h.fiyat);
                                }, 50);
                            }
                        }, 100);
                    });
                    setTimeout(duzenleUpdateOzeti, 300);
                };

                if(window.randevuHizmetVerisi){
                    hizmetleriYukle();
                } else {
                    // Cache yoksa fetch et + tamamlanince yukle
                    duzenleFetchHizmetVerisi(function(){
                        hizmetleriYukle();
                    });
                }
            },
            error: function(xhr){
                console.error('Randevu duzenle JSON hatasi:', xhr.status, xhr.responseText);
                $('#randevu-duzenle-ozeti').html('<div class="text-danger text-center py-3">Randevu bilgisi alınamadı<br><small>' + (xhr.responseText || 'HTTP ' + xhr.status) + '</small></div>');
            }
        });
    });

    // Guvenceli fallback: modal aciladan sonra eski HTML kalintilarini temizle
    $('#randevu-duzenle-modal').on('shown.bs.modal', function(){
        // Eski custom.js'in ekledigi .row'lar (hizmet-satiri-duzenle olmayanlar) temizle
        $('.hizmetler_bolumu_randevu_duzenleme > .row').not('.hizmet-satiri-duzenle').remove();
        // 3 saniye icinde ozet dolmazsa
        setTimeout(function(){
            var $oz = $('#randevu-duzenle-ozeti');
            if($oz.find('p.fw-bold').text() === 'Yükleniyor...'){
                duzenleUpdateOzeti();
            }
        }, 3000);
    });
})();
</script>
