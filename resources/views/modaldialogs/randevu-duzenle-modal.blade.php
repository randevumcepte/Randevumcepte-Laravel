{{-- Randevu Duzenle Modal - Ekleme modali ile birebir ayni yapi. Eski sade hali randevu-duzenle-modal-eski.blade.php'de. --}}
@php
    $__dz_takvim_turu = $isletme->randevu_takvim_turu ?? 0;
    // Personel secimi her zaman gorunur (cihaz/oda turunde de personel atanabilsin)
    $__dz_personel_style = '';
    $__dz_cihaz_style    = in_array($__dz_takvim_turu, [1, 3]) ? 'display:none;' : '';
    $__dz_oda_style      = in_array($__dz_takvim_turu, [1, 2]) ? 'display:none;' : '';
    $__dz_yardimci_style = 'display:none;';
@endphp
<div id="randevu-duzenle-modal" class="modal modal-top fade calendar-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="max-width: 1200px !important; width: 96% !important; margin: 0 auto !important;">
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
                        <!-- Tam genislik: Temel Bilgiler ve Hizmetler -->
                        <div class="col-md-12">
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
                                    <div class="d-flex" style="gap:6px;">
                                        <button type="button" id="duzenle-paketleri-goster" class="btn btn-sm" style="padding: 3px 8px; font-size: 0.75rem; background:#5C008E; color:#fff; border:none;">
                                            <i class="fa fa-gift"></i> Paketleri Göster
                                        </button>
                                        <button type="button" id="bir_hizmet_daha_ekle_randevu_duzenleme" class="btn btn-outline-success btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                                            <i class="icon-copy fi-plus"></i> Yeni Hizmet Ekle
                                        </button>
                                    </div>
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

                    </div>
                    {{-- Gizli ozet alani: duzenleUpdateOzeti hata vermesin diye --}}
                    <div id="randevu-duzenle-ozeti" style="display:none;"></div>
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
/* Detay modal + swal v1 (99999) acikken duzenleme modal arkada kalmasin — yuksek z-index */
#randevu-duzenle-modal { z-index: 100003 !important; }
.modal-backdrop.show + #randevu-duzenle-modal,
body.modal-open #randevu-duzenle-modal { z-index: 100003 !important; }

/* SWAL/SweetAlert uyarilari modal uzerinde (100003) kalsin — aksi halde
   "Hizmet secin" / "Personel secin" gibi form uyarilari modalin ARKASINDA
   gorunmez kaliyor. */
.sweet-overlay { z-index: 100029 !important; }
.sweet-alert   { z-index: 100030 !important; }
.swal2-container { z-index: 100030 !important; }

/* Ekle modal ile ayni: dikey ortalama (modal-dialog-centered ::before hack i olmadan) */
#randevu-duzenle-modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
#randevu-duzenle-modal .modal-dialog {
    margin: 0 auto !important;
    align-self: center !important;
    max-width: 1200px !important;
    width: 96% !important;
}
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
    z-index: 100015;
    opacity:1 !important;
}
#randevu-duzenle-modal .ts-dropdown .active { background:#6366f1 !important; color:#fff !important; }
#randevu-duzenle-modal .ts-dropdown .option { padding:8px 12px !important; background:#fff !important; color:#111827 !important; }
#randevu-duzenle-modal .ts-dropdown .option:hover,
#randevu-duzenle-modal .ts-dropdown .option.active { background:#6366f1 !important; color:#fff !important; }
#randevu-duzenle-modal .ts-dropdown .option:hover *,
#randevu-duzenle-modal .ts-dropdown .option.active * { color:#fff !important; }
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
                    cihaz: (resp && resp.cihaz_hizmet_map) ? resp.cihaz_hizmet_map : {},
                    oda: (resp && resp.oda_hizmet_map) ? resp.oda_hizmet_map : {},
                    oda_personel: (resp && resp.oda_personel_map) ? resp.oda_personel_map : {}
                };
                if(onReady) onReady();
            },
            error: function(xhr){
                console.error('Hizmet verisi yuklenemedi:', xhr.status);
                if(onReady) onReady();
            }
        });
    }

    // CRITICAL: Submit butonu CLICK'inde (submit event'ten BAGIMSIZ) satir-birlestir
    // hidden input'larini enjekte et. Form submit tetiklendiginde serialize bunlari
    // dahil eder. Capture-phase submit handler calismasa bile bu CLICK handler calisir.
    $(document).on('click', '#randevu-duzenle-modal button[type="submit"], #randevuduzenleform button[type="submit"]', function(){
        try {
            var $form = $('#randevuduzenleform');
            // Eski birlestirN input/checkbox'larini temizle (cakisma)
            $form.find('input.satir-birlestir-flat').remove();
            $form.find('input[type="checkbox"][name^="birlestir"]').each(function(){
                $(this).prop('checked', false).removeAttr('name');
            });
            var _flatIdx = 0;
            var _log = [];
            $form.find('.hizmet-satiri-duzenle').each(function(satirIdx){
                var $satir = $(this);
                var $hzSel = $satir.find('.duzenle-hizmet-select');
                var tsLocal = $hzSel[0] && $hzSel[0].tomselect;
                var secimler = tsLocal ? tsLocal.items.slice() : ($hzSel.val() || []);
                if(!Array.isArray(secimler)) secimler = secimler ? [secimler] : [];
                secimler = secimler.filter(function(x){return x;});
                if(secimler.length === 0) return;
                if(satirIdx > 0 && $satir.find('.satir-birlestir-cb').is(':checked')){
                    $form.append('<input type="hidden" class="satir-birlestir-flat" name="birlestir'+_flatIdx+'" value="1">');
                    _log.push({satirIdx:satirIdx, flatIdx:_flatIdx});
                }
                _flatIdx += secimler.length;
            });
            console.log('[DUZENLE CLICK SATIR BIRLESTIR]', _log);
        } catch(err){ console.error('[DUZENLE CLICK ENJEKT HATA]', err); }
    });

    // Custom.js submit handler'ini bypass et - form scope'lu dogru validation yap.
    // CAPTURE PHASE (3. arg = true) ile bind: jQuery event delegation (custom.js'teki
    // $(document).on('submit', '#randevuduzenleform', ...)) bubble phase'de calisir.
    // Capture'da once bizim handler calisir, stopImmediatePropagation sonrasi bubble'a
    // devam etmez -> custom.js handler cagrilmaz, cift AJAX gitmez.
    document.addEventListener('submit', function(e){
        if(!e.target || e.target.id !== 'randevuduzenleform') return;
        // Custom.js validation bug'i: cihaz selector form scope degil, document-wide. Override et.
        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();

        var formScope = $('#randevuduzenleform');

        // Kara liste on-kontrol — stopImmediatePropagation custom.js handler'ini iptal
        // ettiginden burada aciktan cagirmak zorundayiz. Flag ile recursive.
        if (!e.target.__klChecked && typeof window.karaListeOnKontrol === 'function') {
            var _klUser = $('#randevuduzenle_musteri_id').val();
            var _klSalon = formScope.find('input[name="sube"]').val();
            var _klForm = e.target;
            window.karaListeOnKontrol(_klUser, _klSalon, 'guncelleme', function(){
                _klForm.__klChecked = true;
                $(_klForm).submit();
            });
            return;
        }
        e.target.__klChecked = false;
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

        // SATIR BAZLI birlestir -> FLAT INDEX birlestir<N> hidden input enjekte et.
        // ONEMLI: Eski hizmet-detay kutularinda kalmis olabilecek 'birlestirN' name'li
        // checkbox'lari + eski hidden input'lari TEMIZLE (serialize'da cakismasin).
        formScope.find('input.satir-birlestir-flat').remove();
        formScope.find('input[type="checkbox"][name^="birlestir"]').each(function(){
            $(this).prop('checked', false).removeAttr('name');
        });
        var _flatIdx = 0;
        var _birlestirLog = [];
        formScope.find('.hizmet-satiri-duzenle').each(function(satirIdx){
            var $satir = $(this);
            var $hzSel = $satir.find('.duzenle-hizmet-select');
            var tsLocal = $hzSel[0] && $hzSel[0].tomselect;
            var secimler = tsLocal ? tsLocal.items.slice() : ($hzSel.val() || []);
            if(!Array.isArray(secimler)) secimler = secimler ? [secimler] : [];
            secimler = secimler.filter(function(x){return x;});
            if(secimler.length === 0) return;
            var isChecked = $satir.find('.satir-birlestir-cb').is(':checked');
            if(satirIdx > 0 && isChecked){
                formScope.append('<input type="hidden" class="satir-birlestir-flat" name="birlestir'+_flatIdx+'" value="1">');
                _birlestirLog.push({satirIdx:satirIdx, flatIdx:_flatIdx, hidden:'birlestir'+_flatIdx+'=1'});
            }
            _flatIdx += secimler.length;
        });
        console.log('[DUZENLE SATIR BIRLESTIR]', _birlestirLog);

        // TESHIS LOG: Submit oncesi form alanlarini sirali yazdir
        try {
            var __dbg = {
                tarih: formScope.find('#randevuduzenle_tarih').val(),
                saat: formScope.find('#randevuduzenle_saat').val(),
                personeller: formScope.find('select[name="randevupersonelleriyeni[]"]').map(function(){ return $(this).val() || ''; }).get(),
                cihazlar: formScope.find('select[name="randevucihazlariyeni[]"]').map(function(){ return $(this).val() || ''; }).get(),
                odalar: formScope.find('select[name="randevuodalariyeni[]"]').map(function(){ return $(this).val() || ''; }).get(),
                hizmetler: formScope.find('select[name="randevuhizmetleriyeni[]"]').map(function(){
                    var v = $(this).val(); return Array.isArray(v) ? v.join('|') : (v || '');
                }).get(),
                sureler: formScope.find('input[name="hizmet_suresi[]"]').map(function(){ return $(this).val(); }).get(),
                fiyatlar: formScope.find('input[name="hizmet_fiyat[]"]').map(function(){ return $(this).val(); }).get(),
                miktarlar: formScope.find('input[name="hizmet_miktari[]"]').map(function(){ return $(this).val(); }).get(),
                birlestir: {}
            };
            for(var i=1;i<=10;i++){
                var b = formScope.find('input[name="birlestir'+i+'"]:checked').length;
                if(formScope.find('input[name="birlestir'+i+'"]').length){
                    __dbg.birlestir['birlestir'+i] = b ? 1 : 0;
                }
            }
            console.log('[DUZENLE SUBMIT DBG]', __dbg);
        } catch(e){ console.warn('debug log hata:', e); }

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
                                    $('#modal-view-event').modal('hide');
                                    $('.modal-backdrop').remove();
                                    $('body').removeClass('modal-open').css('padding-right','');
                                    if($('#calendar').length) takvimyukle(false,false);
                                },
                                error: function(xhr){ $('#preloader').hide(); swal({type:'error',title:'Hata',text:'Güncelleme başarısız: '+xhr.status,showConfirmButton:false,timer:2500}); }
                            });
                        }
                    });
                } else {
                    swal({type:'success',title:'Başarılı',html:result.success,showConfirmButton:false,timer:result.timer||2500});
                    $('#randevu-duzenle-modal').modal('hide');
                    $('#modal-view-event').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right','');
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
        // V2 DUZENLE MODAL DEVREDE ISE bu eski handler'i atla — v2 kendi
        // capture handler'ini calistiracak. Bayrak randevu-duzenle-modal-v2
        // blade'inde set edilir; rollback icin bayrak false yapilabilir
        // ya da v2 modal include'u kaldirilabilir.
        if (window._v2DuzenleModalAktif === true) return;
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
        // Randevu detay modal'i acik kalirsa duzenleme modal'i onun arkasinda gozukuyor
        // -> Detay modali kapat ve sonra duzenleme modalini ac (backdrop kalintilari da temizle)
        try { $('#modal-view-event').modal('hide'); } catch(err){}
        setTimeout(function(){
            $('.modal-backdrop').not(':last').remove();
            $('#randevu-duzenle-modal').modal('show');
        }, 50);
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
        // SATIR BAZLI birlestir — sadece 2+ satirlarda gorunur. Checked ise bu satirin
        // ilk hizmeti ustteki satirin BITIS saatine degil, ustteki satirin BASLANGIC
        // saatine denk gelir (paralel randevu). Backend birlestir<flat_idx> ile algilar.
        var satirBirlestirHtml = (num > 1)
            ? '<label class="satir-birlestir-wrap" style="font-size:0.72rem;margin:0;display:flex;align-items:center;gap:4px;cursor:pointer;">' +
                '<input type="checkbox" class="satir-birlestir-cb" style="margin:0;">' +
                'Üstteki satırla aynı saatte başlasın' +
              '</label>'
            : '';
        var html =
            '<div class="hizmet-satiri-duzenle card mb-2" data-value="'+idx+'" style="border: 1px solid #dee2e6;">' +
              '<div class="card-header py-1 d-flex justify-content-between align-items-center" style="padding:4px 8px; background:#f8f9fa;">' +
                '<span class="fw-bold" style="font-size:0.85rem;">Hizmet #'+num+'</span>' +
                '<div style="display:flex;align-items:center;gap:10px;">' +
                   satirBirlestirHtml +
                   '<button type="button" name="hizmet_formdan_sil" data-value="'+idx+'" class="btn btn-sm btn-danger duzenle-hizmet-sil" style="padding:2px 6px; font-size:0.7rem;">' +
                     '<i class="icon-copy fa fa-trash"></i> Sil</button>' +
                '</div>' +
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

    // ===================== PAKET SECIMI (DUZENLEME) =====================
    // Paket secim modalindan (custom.js showSoftPackageSelectionModal) gelen hizmetleri
    // DUZENLEME formuna ekler — her hizmet ayri satir. addServicesToForm dispatcher'i
    // (ekle blade) duzenle modali aciksa burayi cagirir.
    function _duzenleAddServicesToForm(hizmetData, result, showMsg){
        if(!hizmetData || !hizmetData.length) return;
        hizmetData.forEach(function(item){
            var $row = duzenleYeniHizmetSatiri();
            if(!$row) return;
            var hid = item.hizmet_id || item.id;
            (function wait(tries){
                var $hz = $row.find('.duzenle-hizmet-select');
                var ts = $hz[0] && $hz[0].tomselect;
                if(ts){
                    if(!ts.options[hid]){
                        ts.addOption({ value:String(hid), text:item.text,
                            kategori:(item.tur==='paket' ? ('Paket: '+(item.paket_adi||'')) : 'Hizmet'),
                            sure:item.sure||0, fiyat:item.fiyat||0 });
                    }
                    ts.addItem(String(hid), false); // onChange -> detay render
                    setTimeout(function(){
                        var $sure = $row.find('input.hizmet-suresi').last();
                        if($sure.length && item.sure) $sure.val(item.sure);
                    }, 60);
                } else if(tries < 40){
                    setTimeout(function(){ wait(tries+1); }, 80);
                }
            })(0);
        });
        try { if(typeof duzenleUpdateOzeti === 'function') duzenleUpdateOzeti(); } catch(e){}
    }
    window._duzenleAddServicesToForm = _duzenleAddServicesToForm;

    // "Paketleri Göster" (duzenleme) — secili musterinin paketlerini soft modalda goster.
    // paketKontrolü(uid, false): false => onay gerekli => modal acilir (true olsaydi otomatik eklerdi).
    $(document).on('click', '#duzenle-paketleri-goster', function(){
        var uid = $('#randevuduzenle_musteri_id').val();
        if(!uid){
            if(typeof swal === 'function') swal({type:'warning', title:'Uyarı', text:'Önce müşteri seçiniz.', timer:2500, showConfirmButton:false});
            return;
        }
        try { paketKontrolü(uid, false); }
        catch(e){ try { window['paketKontrolü'](uid, false); } catch(e2){ console.warn('paketKontrolü bulunamadi', e2); } }
    });

    // Hizmet select options'i doldur (tum hizmetler veya personel/cihaz/oda filtreli)
    // Eslesme yoksa filtreleme YAPMA (tum hizmetler gosterilir) — v2 modaliyle ayni mantik.
    function duzenleHizmetSelectOptionsYukle($hz, tsParam, personelId, cihazId, odaId){
        var ts = tsParam || ($hz[0] && $hz[0].tomselect);
        if(!ts || !window.randevuHizmetVerisi) return;
        var v = window.randevuHizmetVerisi;
        var liste;
        if(personelId || cihazId || odaId){
            var izinli = [];
            if(personelId && v.personel && v.personel[personelId]) izinli = izinli.concat(v.personel[personelId]);
            if(cihazId && v.cihaz && v.cihaz[cihazId]) izinli = izinli.concat(v.cihaz[cihazId]);
            // ODA: oncelikle endpoint'ten gelen v.oda map, yoksa blade randevuModalData fallback
            if(odaId){
                if(v.oda && v.oda[odaId] && v.oda[odaId].length){
                    izinli = izinli.concat(v.oda[odaId].map(String));
                } else {
                    var odaObj = ((window.randevuModalData && window.randevuModalData.odalar) || []).find(function(o){ return String(o.id) === String(odaId); });
                    if(odaObj && Array.isArray(odaObj.hizmet_idleri) && odaObj.hizmet_idleri.length){
                        izinli = izinli.concat(odaObj.hizmet_idleri);
                    }
                }
            }
            izinli = Array.from(new Set(izinli.map(String)));
            if(izinli.length){
                liste = v.tum.filter(function(h){ return izinli.indexOf(String(h.id)) > -1; });
            } else {
                liste = v.tum.slice(); // eslesme yoksa hepsi (filtreleme yapma)
            }
        } else {
            liste = v.tum.slice();
        }
        // Mevcut secimi her zaman koru — filtre disi olsa bile.
        // Onceden secili olan hizmet'in option'i listeye eklenmezse TS'den
        // dusurulurdu (kullanici "hizmet seçili gelmiyor" diye rapor ediyordu).
        var mevcut = ts.getValue() || [];
        if(!Array.isArray(mevcut)) mevcut = mevcut ? [mevcut] : [];
        // Onceki secimlerin option detayini sakla (text, sure, fiyat)
        var mevcutOptions = mevcut.map(function(id){
            return ts.options[id] ? Object.assign({}, ts.options[id]) : null;
        }).filter(Boolean);

        ts.clearOptions();
        liste.forEach(function(h){
            ts.addOption({ value: h.id, text: h.ad, kategori: h.kategori || '', sure: h.sure || 0, fiyat: h.fiyat || 0 });
        });
        // Listede olmayan ama secili olanlari geri ekle
        mevcutOptions.forEach(function(opt){
            if(!ts.options[opt.value]){
                ts.addOption(opt);
            }
        });
        ts.refreshOptions(false);
        if(mevcut.length){ ts.setValue(mevcut, true); }
        console.log('[DUZENLE FILTRE]', {personelId: personelId, cihazId: cihazId, odaId: odaId, liste: liste.length, korundu: mevcut.length});
    }

    // ODA -> PERSONEL filtresi (v2 modaliyle ayni mantik)
    // Secilen odaya tanimli personel varsa, satirdaki Personel <select> sadece
    // o personelleri gosterir. Tanimli personel yoksa: tum personeller gosterilir.
    function duzenleRefreshPersonelByOda($row){
        var $p = $row.find('.duzenle-personel-select');
        if(!$p.length) return;
        var odaId = $row.find('.duzenle-oda-select').val() || '';
        var allPersoneller = (window.randevuModalData && window.randevuModalData.personeller) ? window.randevuModalData.personeller : [];
        var verisi = window.randevuHizmetVerisi || {};
        var izinli = null;
        if(odaId && verisi.oda_personel && verisi.oda_personel[odaId] && verisi.oda_personel[odaId].length){
            izinli = verisi.oda_personel[odaId].map(String);
        }
        var liste = (izinli && izinli.length)
            ? allPersoneller.filter(function(p){ return izinli.indexOf(String(p.id)) > -1; })
            : allPersoneller;
        var prevVal = $p.val() || '';
        // Select2 sarmaliysa once destroy edip yeniden init etmek gerekir
        var isSelect2 = $p.hasClass('select2-hidden-accessible');
        if(isSelect2){
            try { $p.select2('destroy'); } catch(e){}
        }
        $p.empty().append('<option></option>');
        liste.forEach(function(p){ $p.append(new Option(p.ad, p.id)); });
        if(prevVal && liste.some(function(p){ return String(p.id) === String(prevVal); })){
            $p.val(prevVal);
        } else if(prevVal){
            $p.val('');
        }
        if(isSelect2){
            try { $p.select2({ placeholder: 'Seçiniz', allowClear: true, dropdownParent: $('#randevu-duzenle-modal') }); } catch(e){}
        }
        console.log('[DUZENLE ODA→PERSONEL]', {odaId: odaId, izinli_count: izinli ? izinli.length : 0, liste_count: liste.length, prevVal: prevVal});
    }

    // Hizmet secimi sonrasi sure/fiyat input'larini render et (ekleme modali gibi)
    // DEBUG v5: hizmet-suresi input'una value yazimini INTERCEPT et, stack trace at
    (function installValueTracer(){
        if(window._duzenleSureTracerInstalled) return;
        window._duzenleSureTracerInstalled = true;
        var origDesc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        if(!origDesc || !origDesc.set) return;
        document.addEventListener('focusin', function(ev){
            var el = ev.target;
            if(!el || !el.matches || !el.matches('#randevu-duzenle-modal .hizmet-suresi, #randevu-duzenle-modal .hizmet-fiyati')) return;
            if(el._tracerAttached) return;
            el._tracerAttached = true;
        }, true);
        // Daha agresif: hiz-suresi tum DOM input'larina direkt attach
        var tryAttach = function(){
            document.querySelectorAll('#randevu-duzenle-modal .hizmet-suresi, #randevu-duzenle-modal .hizmet-fiyati').forEach(function(el){
                if(el._tracerAttached) return;
                el._tracerAttached = true;
                var sym = el.className.indexOf('hizmet-suresi')>-1 ? 'SURE' : 'FIYAT';
                Object.defineProperty(el, 'value', {
                    configurable: true,
                    get: function(){ return origDesc.get.call(this); },
                    set: function(v){
                        var hid = (el.closest('.hizmet-detay-item') || {}).getAttribute && el.closest('.hizmet-detay-item').getAttribute('data-hid');
                        var oldVal = origDesc.get.call(this);
                        console.warn('[TRACER '+sym+'] hid='+hid+' old='+oldVal+' new='+v, {stack:new Error().stack});
                        origDesc.set.call(this, v);
                    }
                });
            });
        };
        setInterval(tryAttach, 300);
    })();

    // DELTA RENDER (v4): Container'i ASLA empty + rebuild etme.
    // Mevcut input'lara DOKUNMA — user edit + default degerler hic bir timer tarafindan
    // override edilemez. Sadece ekle/sil fark operasyonu + birlestir numaralandirmasi.
    function duzenleRenderHizmetDetay(index){
        var $sel = $('.duzenle-hizmet-select[data-index="'+index+'"]');
        if(!$sel.length) return;
        var ts = $sel[0] && $sel[0].tomselect;
        var $container = $('#duzenle-hizmet-detaylari-'+index);
        if(!ts) return;
        var tumHizmetler = (window.randevuHizmetVerisi && window.randevuHizmetVerisi.tum) || [];
        // DOM'daki mevcut satirlari hid->$row map'le
        var mevcutRows = {};
        $container.find('.hizmet-detay-item').each(function(){
            var hid = $(this).attr('data-hid');
            if(hid) mevcutRows[String(hid)] = $(this);
        });
        var secili = ts.items.map(String);
        // (b) Secili olmayan DOM satirlarini sil
        Object.keys(mevcutRows).forEach(function(hid){
            if(secili.indexOf(hid) === -1) mevcutRows[hid].remove();
        });
        // (a) Secili olup DOM'da olmayan icin yeni satir ekle (sirali)
        secili.forEach(function(id, i){
            if(mevcutRows[id]) return; // zaten var -> DOKUNMA
            var opt = ts.options[id] || {};
            var ad = opt.text || '';
            var sysH = tumHizmetler.find(function(h){ return String(h.id) === String(id); });
            var defSure  = sysH ? (Number(sysH.sure)  || 0) : (Number(opt.sure)  || 0);
            var defFiyat = sysH ? (Number(sysH.fiyat) || 0) : (Number(opt.fiyat) || 0);
            var savedMiktar = opt.dusum_miktari || 1;
            console.log('[RENDER DEBUG v4] YENI satir', {hid:id, ad:ad, sysH_sure:sysH?sysH.sure:'YOK', opt_sure:opt.sure, SONUC_sure:defSure, SONUC_fiyat:defFiyat});
            var html =
                '<div class="hizmet-detay-item" data-hid="'+id+'" style="background:#fafbff;border:1px solid #e5e7eb;border-radius:6px;padding:8px;margin-top:6px;">' +
                  '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:8px;">' +
                    '<span style="font-size:0.82rem;font-weight:600;color:#111827;">'+ad+'</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger duzenle-hizmet-kaldir" data-index="'+index+'" data-service-id="'+id+'" style="padding:1px 6px;font-size:0.7rem;">' +
                      '<i class="fa fa-times"></i></button>' +
                  '</div>' +
                  '<div class="row g-1">' +
                    '<div class="col-md-5">' +
                      '<label style="font-size:0.7rem;">Süre (dakika)</label>' +
                      '<input type="number" class="form-control form-control-sm hizmet-suresi" name="hizmet_suresi[]" value="'+defSure+'" min="0" step="5" style="height:26px;padding:2px 6px;font-size:0.75rem;">' +
                    '</div>' +
                    '<div class="col-md-5">' +
                      '<label style="font-size:0.7rem;">Fiyat (₺)</label>' +
                      '<input type="text" inputmode="decimal" class="form-control form-control-sm hizmet-fiyati hy-fiyat-input" name="hizmet_fiyat[]" value="'+defFiyat+'" placeholder="0,00" autocomplete="off" style="height:26px;padding:2px 6px;font-size:0.75rem;">' +
                    '</div>' +
                    '<div class="col-md-2">' +
                      '<label style="font-size:0.7rem;" title="Paketten kac seans veya dakika dusulecek">Miktar</label>' +
                      '<input type="number" class="form-control form-control-sm hizmet-miktari" name="hizmet_miktari[]" value="'+ savedMiktar +'" min="1" step="1" style="height:26px;padding:2px 6px;font-size:0.75rem;text-align:center;">' +
                    '</div>' +
                  '</div>' +
                '</div>';
            // Dogru sirada ekle: ts.items[i]'nin sirasi
            if(i === 0){
                $container.prepend(html);
            } else {
                var prevId = secili[i-1];
                var $prevRow = $container.find('.hizmet-detay-item[data-hid="'+prevId+'"]');
                if($prevRow.length) $prevRow.after(html);
                else $container.append(html);
            }
        });
        // (c) Birlestir checkbox satir basliga tasindi — hizmet detay'da gerek yok.
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

    // Duzenleme: personel/cihaz/oda degisince hizmet select'i yenile.
    // V2 modaliyle ayni mantik — takvim turu gating yok; tum secimler birlestirilir.
    $(document).on('change', '#randevu-duzenle-modal .duzenle-personel-select, #randevu-duzenle-modal .duzenle-cihaz-select, #randevu-duzenle-modal .duzenle-oda-select', function(){
        var $row = $(this).closest('.hizmet-satiri-duzenle');
        if(!$row.length) return;
        // Oda degisirse once personel <select>'i ona gore filtrele
        if($(this).hasClass('duzenle-oda-select')){
            try { duzenleRefreshPersonelByOda($row); } catch(e){ console.warn('[DUZENLE oda→personel] hata:', e); }
        }
        var personelId = $row.find('.duzenle-personel-select').val() || '';
        var cihazId    = $row.find('.duzenle-cihaz-select').val()    || '';
        var odaId      = $row.find('.duzenle-oda-select').val()      || '';
        var $hz = $row.find('.duzenle-hizmet-select');
        if($hz.length) duzenleHizmetSelectOptionsYukle($hz, null, personelId, cihazId, odaId);
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
                    // IDEMPOTENT: cift fetch/cift tiklama durumunda satirlar birikmesin
                    // (paralel iki randevu-duzenle-json call'i her ikisi de append yapip
                    // 4 satir olusturuyordu -> backend duplicate insert. #1911070 bug.)
                    $('.hizmetler_bolumu_randevu_duzenleme').empty();
                    window.duzenleHizmetIndex = 0;
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
                            if(ts && (h.hizmet_id !== undefined && h.hizmet_id !== null)){
                                // Mevcut hizmet TS'de yoksa once cache'ten, sonra backend'in
                                // dondurdugu 'hizmet_adi' fallback ile mutlaka eklenir. Boylece
                                // hizmet_id=0 (Ön Görüşme) veya filtre disi (oda/personel ile
                                // eslesmeyen) hizmetler de SECILI olarak gelir.
                                // NOT: sure/fiyat icin RANDEVUYA OZEL h.sure_dk/h.fiyat
                                // kullanilir (hizmet tanimi default'u DEGIL); kullanici randevu
                                // olusurken farkli bir sure girmis olabilir.
                                var hData = null;
                                if(window.randevuHizmetVerisi){
                                    hData = window.randevuHizmetVerisi.tum.find(function(x){ return String(x.id) === String(h.hizmet_id); });
                                }
                                var optAd = hData ? hData.ad : (h.hizmet_adi || ('Hizmet #' + h.hizmet_id));
                                var optKat = hData ? (hData.kategori || '') : '';
                                // RANDEVUYA OZEL sure/fiyat: tanim default'u DEGIL, randevudaki kaydedilmis deger.
                                // (0 dk de gecerli — undefined/null kontrolu yapiyoruz, falsy check yok)
                                var optSure  = (h.sure_dk !== undefined && h.sure_dk !== null && h.sure_dk !== '') ? Number(h.sure_dk) : (hData ? hData.sure : 0);
                                var optFiyat = (h.fiyat !== undefined && h.fiyat !== null && h.fiyat !== '')      ? Number(h.fiyat)   : (hData ? hData.fiyat : 0);
                                // Force overwrite: hem cache'ten gelen option'u, hem yeni eklediklerimizi
                                // RANDEVU degerleriyle yeniden olustur.
                                if(ts.options[h.hizmet_id]){
                                    try { ts.removeOption(h.hizmet_id); } catch(e){}
                                }
                                ts.addOption({ value: h.hizmet_id, text: optAd, kategori: optKat, sure: optSure, fiyat: optFiyat });
                                ts.addItem(String(h.hizmet_id), false); // false=event fire et -> onChange -> detay render
                                // Belt-and-suspenders: render sonrasi input'larida overwrite et.
                                // (Tom Select option store cache'lemesi durumunda fallback)
                                // 3 kat guvenlik: 50ms, 200ms, 500ms — filter re-run'larin
                                // override etme ihtimaline karsi.
                                // v4: Modal acilisinda RANDEVU'ya ozel degerler option'a
                                // zaten yazildi (sure=optSure, fiyat=optFiyat line 870 civarinda).
                                // Delta render ilk render'da bu option'dan okuyacak.
                                // Belt-and-suspenders: option tanimli degilse diye bir kerelik set
                                // ONCE option guncelle (bu zaten yukarida yapildi).
                                // Eski 3 kat setTimeout _overwriteRow KALDIRILDI — render sonrasi
                                // input'a mudahale eden hic bir timer kalmadi.
                                // Spesifik olarak bu hizmet icin option.sure/fiyat'i tekrar garanti et:
                                try {
                                    var _tsLocal = ts;
                                    var _curOpt = _tsLocal && _tsLocal.options[h.hizmet_id];
                                    if(_curOpt){
                                        if(h.sure_dk !== undefined && h.sure_dk !== null && h.sure_dk !== '') _curOpt.sure = Number(h.sure_dk);
                                        if(h.fiyat !== undefined && h.fiyat !== null && h.fiyat !== '') _curOpt.fiyat = Number(h.fiyat);
                                        if(h.dusum_miktari) _curOpt.dusum_miktari = Number(h.dusum_miktari);
                                    }
                                    // Delta render yeni satir icin option'dan okur — direkt input'u da yaz
                                    var $tItem = $row.find('.hizmet-detay-item[data-hid="'+h.hizmet_id+'"]');
                                    if($tItem.length){
                                        if(h.sure_dk !== undefined && h.sure_dk !== null && h.sure_dk !== '') $tItem.find('input.hizmet-suresi').val(h.sure_dk);
                                        if(h.fiyat !== undefined && h.fiyat !== null && h.fiyat !== '') $tItem.find('input.hizmet-fiyati').val(h.fiyat);
                                        if(h.dusum_miktari) $tItem.find('input.hizmet-miktari').val(h.dusum_miktari);
                                    }
                                } catch(_e){}
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
