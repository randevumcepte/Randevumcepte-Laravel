@extends('layout.layout_randevual')
@section('content')

<div class="rdv-booking-v2">
    <div id="preloader"></div>

    <div class="rdv-page-head">
        <h1>Yeni Randevu Oluştur</h1>
        <p>{{$salon->salon_adi}} için birkaç adımda randevunuzu tamamlayın.</p>
    </div>

    {{-- Stepper --}}
    <div class="rdv-page-head">
        <div class="rdv-stepper" id="rdvStepper">
            <div class="rdv-step rdv-step--active" data-step="1">
                <div class="rdv-step__circle"><span>1</span></div>
                <div class="rdv-step__text">
                    <span class="rdv-step__label">Adım 1</span>
                    <span class="rdv-step__title">Hizmet</span>
                </div>
            </div>
            <div class="rdv-step__divider"></div>
            <div class="rdv-step" data-step="2">
                <div class="rdv-step__circle"><span>2</span></div>
                <div class="rdv-step__text">
                    <span class="rdv-step__label">Adım 2</span>
                    <span class="rdv-step__title">Personel</span>
                </div>
            </div>
            <div class="rdv-step__divider"></div>
            <div class="rdv-step" data-step="3">
                <div class="rdv-step__circle"><span>3</span></div>
                <div class="rdv-step__text">
                    <span class="rdv-step__label">Adım 3</span>
                    <span class="rdv-step__title">Tarih &amp; Saat</span>
                </div>
            </div>
            <div class="rdv-step__divider"></div>
            <div class="rdv-step" data-step="4">
                <div class="rdv-step__circle"><span>4</span></div>
                <div class="rdv-step__text">
                    <span class="rdv-step__label">Adım 4</span>
                    <span class="rdv-step__title">Onay</span>
                </div>
            </div>
        </div>
    </div>

    <div class="rdv-layout">
        {{-- Main column: form cards --}}
        <div class="rdv-main">
            <form id="randevuhizmetvepersonelleri" method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="salonno" id="salonno" value="{{$salon->id}}">
                <input type="hidden" id="salonid" value="{{$salon->id}}">

                {{-- STEP 1: Service selection --}}
                <div class="rdv-card" data-step="1">
                    <div class="rdv-card__head">
                        <div class="rdv-card__num">1</div>
                        <div class="rdv-card__title-wrap">
                            <h3 class="rdv-card__title">Hizmet Seçimi</h3>
                            <div class="rdv-card__subtitle">Randevunuzda almak istediğiniz hizmetleri ekleyin.</div>
                        </div>
                    </div>
                    <div class="rdv-card__body">
                        <ul class="rdv-service-list">
                            @foreach($secilenhizmetler as $secilenhizmet)
                                <li>
                                    <input type="hidden" name="hizmetler[]" value="{{$secilenhizmet->id}}">
                                    {{$secilenhizmet->hizmet_adi}}
                                </li>
                            @endforeach
                        </ul>
                        <div class="rdv-actions">
                            <button type="button" id="randevualmodal" class="rdv-btn rdv-btn--ghost">
                                <i class="fa fa-plus"></i> Hizmet Ekle / Çıkar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Personnel selection --}}
                <div class="rdv-card" data-step="2">
                    <div class="rdv-card__head">
                        <div class="rdv-card__num">2</div>
                        <div class="rdv-card__title-wrap">
                            <h3 class="rdv-card__title">Personel Seç</h3>
                            <div class="rdv-card__subtitle">Her hizmet için tercih ettiğiniz personeli seçin veya "Farketmez" deyin.</div>
                        </div>
                    </div>
                    <div class="rdv-card__body">
                        {{-- Selected personnel summary (shown after form submit via JS) --}}
                        <div id="secilenpersonelkismi" style="display:none">
                            <div class="rdv-selected">
                                <div class="rdv-selected__value" id="secilenpersoneldeger" name="secilenpersoneldeger"></div>
                                <button type="button" id="personeldegistir" class="rdv-btn rdv-btn--outline rdv-btn--sm">
                                    <i class="fa fa-pencil"></i> Personel Değiştir
                                </button>
                            </div>
                        </div>

                        {{-- Personnel selection table --}}
                        <div id="personeltablosu" class="personeller2">
                            @foreach($secilenhizmetler as $secilenhizmet)
                                <div class="rdv-staff-group">
                                    <div class="rdv-staff-group__label">{{$secilenhizmet->hizmet_adi}}</div>
                                    <div class="form-group" style="margin:0">
                                        <select name="personeller[]">
                                            <option value="0">Farketmez</option>
                                            @foreach($personelhizmetleri as $personelhizmet)
                                                @if($personelhizmet->hizmet_id == $secilenhizmet->id)
                                                    <option>{{$personelhizmet->personeller->personel_adi}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach

                            <div class="rdv-actions" style="margin-top:16px">
                                <button type="submit" id="tarihsaatsecimaktifet" class="rdv-btn rdv-btn--primary">
                                    <i class="fa fa-calendar"></i> Tarih ve Saat Seç
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 3a: Date selection --}}
                <div class="rdv-card" id="randevutarihalani" data-step="3" tabindex="-1">
                    <div class="rdv-card__head">
                        <div class="rdv-card__num">3</div>
                        <div class="rdv-card__title-wrap">
                            <h3 class="rdv-card__title">Tarih Seç</h3>
                            <div class="rdv-card__subtitle">Randevu almak istediğiniz günü seçin.</div>
                        </div>
                    </div>
                    <div class="rdv-card__body">
                        {{-- Selected date summary --}}
                        <div id="secilentarihkismi" style="display:none">
                            <div class="rdv-selected">
                                <div class="rdv-selected__value">
                                    <input id="secilentarih" type="hidden">
                                    <span id="secilentarihdeger"></span>
                                </div>
                                <button type="button" id="tarihdegistir" class="rdv-btn rdv-btn--outline rdv-btn--sm">
                                    <i class="fa fa-pencil"></i> Tarih Değiştir
                                </button>
                            </div>
                        </div>

                        {{-- Date grid --}}
                        <div id="tarihtablosu" class="tarihler rdv-date-grid">
                            <div class="input-radio">
                                <input type="radio" id="bugun" name="randevutarihi" value="{{date('Y-m-d')}}" checked>
                                <label for="bugun">Bugün<br><small style="font-weight:400;opacity:.85">{{date('d.m')}}</small></label>
                            </div>
                            <div class="input-radio">
                                <input type="radio" id="yarin" name="randevutarihi" value="{{date('Y-m-d',strtotime('+1 days',strtotime(date('Y-m-d')))) }}">
                                <label for="yarin">Yarın<br><small style="font-weight:400;opacity:.85">{{date('d.m',strtotime('+1 days',strtotime(date('Y-m-d')))) }}</small></label>
                            </div>
                            @for ($i = 2 ;$i <= 30; $i++)
                                <div class="input-radio">
                                    <input id="nextdays{{$i}}" type="radio" name="randevutarihi" value="{{date('Y-m-d',strtotime('+'.$i.' days',strtotime(date('Y-m-d')))) }}">
                                    <label for="nextdays{{$i}}">{{date('D',strtotime('+'.$i.' days',strtotime(date('Y-m-d')))) }}<br><small style="font-weight:400;opacity:.85">{{date('d.m',strtotime('+'.$i.' days',strtotime(date('Y-m-d')))) }}</small></label>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- STEP 3b: Time selection --}}
                <div class="rdv-card" id="randevusaatalani" data-step="3" tabindex="-1">
                    <div class="rdv-card__head">
                        <div class="rdv-card__num">3</div>
                        <div class="rdv-card__title-wrap">
                            <h3 class="rdv-card__title">Saat Seç</h3>
                            <div class="rdv-card__subtitle">Müsait saat dilimlerinden birini seçin.</div>
                        </div>
                    </div>
                    <div class="rdv-card__body">
                        {{-- Selected time summary --}}
                        <div id="secilensaatkismi" style="display:none">
                            <div class="rdv-selected">
                                <div class="rdv-selected__value">
                                    <input id="secilensaat" name="randevusaati" type="hidden">
                                    <span id="secilensaatdeger"></span>
                                </div>
                                <button type="button" id="saatdegistir" class="rdv-btn rdv-btn--outline rdv-btn--sm">
                                    <i class="fa fa-pencil"></i> Saat Değiştir
                                </button>
                            </div>
                        </div>

                        {{-- Time grid (populated via AJAX: /saatgetir) --}}
                        <div id="saatsecimtablosu" class="saatler rdv-time-grid"></div>
                    </div>
                </div>

                {{-- STEP 4: Personal info / approval --}}
                @if(!Auth::check())
                    <div class="rdv-card" id="kisiselbilgiler" data-step="4" tabindex="-1">
                        <div class="rdv-card__head">
                            <div class="rdv-card__num">4</div>
                            <div class="rdv-card__title-wrap">
                                <h3 class="rdv-card__title">Kişisel Bilgiler</h3>
                                <div class="rdv-card__subtitle">Randevunuzu tamamlamak için e-posta adresinizi giriniz.</div>
                            </div>
                        </div>
                        <div class="rdv-card__body">
                            <div class="kisiselbilgialani">
                                <div class="rdv-field">
                                    <label for="eposta">E-posta adresi</label>
                                    <input type="email" id="eposta" name="eposta" placeholder="ornek@eposta.com">
                                </div>
                                <div id="epostahata"></div>
                                <div id="hosgeldinizbildirimalani"></div>
                                <div id="sifrealaniregister"></div>

                                <div class="rdv-actions">
                                    <button type="submit" id="sifregonder" class="rdv-btn rdv-btn--primary">
                                        <i class="fa fa-paper-plane"></i> Gönder
                                    </button>
                                </div>

                                <div class="rdv-notice">
                                    <i class="fa fa-lock"></i>
                                    <span>Bilgileriniz yalnızca randevu oluşturmak için kullanılır ve üçüncü taraflarla paylaşılmaz.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rdv-card" id="randevuonay" data-step="4">
                        <div class="rdv-card__head">
                            <div class="rdv-card__num">4</div>
                            <div class="rdv-card__title-wrap">
                                <h3 class="rdv-card__title">Randevuyu Onaylayın</h3>
                                <div class="rdv-card__subtitle">Özet bilgileri kontrol ederek randevunuzu tamamlayın.</div>
                            </div>
                        </div>
                        <div class="rdv-card__body">
                            <div class="rdv-actions">
                                <button type="button" class="rdv-btn rdv-btn--primary">
                                    <i class="fa fa-check"></i> Randevuyu Gönder
                                </button>
                                <button type="button" class="rdv-btn rdv-btn--danger">
                                    <i class="fa fa-times"></i> İptal
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        {{-- Sidebar: summary --}}
        <aside class="rdv-sidebar">
            <div class="rdv-summary randevuozetbaslik">
                <div class="rdv-summary__head">
                    <i class="fa fa-calendar-check-o"></i>
                    <div>
                        <h3>Randevu Özeti</h3>
                        <small>Seçimleriniz burada görünür</small>
                    </div>
                </div>
                <div class="rdv-summary__body">
                    <table class="randevuozet" style="display:none">
                        <tr>
                            <td></td><td>{{$salon->salon_adi}}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                @foreach($secilenhizmetler as $secilenhizmet)
                                    {{$secilenhizmet->hizmet_adi}} <br />
                                @endforeach
                            </td>
                        </tr>
                        <tr><td></td><td></td></tr>
                        <tr><td></td><td></td></tr>
                        <tr><td></td><td></td></tr>
                    </table>

                    <div class="rdv-summary__row">
                        <div class="rdv-summary__icon"><i class="fa fa-building-o"></i></div>
                        <div class="rdv-summary__col">
                            <span class="rdv-summary__label">Salon</span>
                            <span class="rdv-summary__value">{{$salon->salon_adi}}</span>
                        </div>
                    </div>
                    <div class="rdv-summary__row">
                        <div class="rdv-summary__icon"><i class="fa fa-list-alt"></i></div>
                        <div class="rdv-summary__col">
                            <span class="rdv-summary__label">Hizmetler</span>
                            <span class="rdv-summary__value">
                                @if($secilenhizmetler->count())
                                    @foreach($secilenhizmetler as $key => $secilenhizmet)
                                        {{$secilenhizmet->hizmet_adi}}@if($key+1 != $secilenhizmetler->count()), @endif
                                    @endforeach
                                @else
                                    <span class="rdv-summary__value--empty">Henüz seçilmedi</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="rdv-summary__row">
                        <div class="rdv-summary__icon"><i class="fa fa-users"></i></div>
                        <div class="rdv-summary__col">
                            <span class="rdv-summary__label">Personeller</span>
                            <span class="rdv-summary__value rdv-summary__value--empty" id="rdvOzetPersonel">Henüz seçilmedi</span>
                        </div>
                    </div>
                    <div class="rdv-summary__row">
                        <div class="rdv-summary__icon"><i class="fa fa-calendar"></i></div>
                        <div class="rdv-summary__col">
                            <span class="rdv-summary__label">Tarih</span>
                            <span class="rdv-summary__value rdv-summary__value--empty" id="rdvOzetTarih">Henüz seçilmedi</span>
                        </div>
                    </div>
                    <div class="rdv-summary__row">
                        <div class="rdv-summary__icon"><i class="fa fa-clock-o"></i></div>
                        <div class="rdv-summary__col">
                            <span class="rdv-summary__label">Saat</span>
                            <span class="rdv-summary__value rdv-summary__value--empty" id="rdvOzetSaat">Henüz seçilmedi</span>
                        </div>
                    </div>

                    <div class="rdv-summary__cta">
                        <i class="fa fa-shield"></i> Güvenli online randevu sistemi
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
(function(){
    // Live-update the sidebar summary as the user makes selections.
    // This is additive and does not interfere with existing custom.js handlers.
    function rdvUpdateOzet() {
        // Personnel
        var staffValues = [];
        document.querySelectorAll('select[name="personeller[]"]').forEach(function(sel){
            if (sel.selectedOptions && sel.selectedOptions.length) {
                staffValues.push(sel.selectedOptions[0].text);
            }
        });
        var psnEl = document.getElementById('rdvOzetPersonel');
        if (psnEl) {
            if (staffValues.length) {
                psnEl.textContent = staffValues.join(', ');
                psnEl.classList.remove('rdv-summary__value--empty');
            } else {
                psnEl.textContent = 'Henüz seçilmedi';
                psnEl.classList.add('rdv-summary__value--empty');
            }
        }

        // Date
        var dateRadio = document.querySelector('input[name="randevutarihi"]:checked');
        var dtEl = document.getElementById('rdvOzetTarih');
        if (dtEl) {
            if (dateRadio) {
                var lbl = document.querySelector('label[for="' + dateRadio.id + '"]');
                dtEl.textContent = (lbl ? lbl.textContent.replace(/\s+/g,' ').trim() : dateRadio.value);
                dtEl.classList.remove('rdv-summary__value--empty');
            } else {
                dtEl.textContent = 'Henüz seçilmedi';
                dtEl.classList.add('rdv-summary__value--empty');
            }
        }

        // Time
        var timeRadio = document.querySelector('input[name="randevusaati"]:checked');
        var stEl = document.getElementById('rdvOzetSaat');
        if (stEl) {
            if (timeRadio) {
                stEl.textContent = (timeRadio.value || '').replace('_',':');
                stEl.classList.remove('rdv-summary__value--empty');
            } else {
                stEl.textContent = 'Henüz seçilmedi';
                stEl.classList.add('rdv-summary__value--empty');
            }
        }
    }

    // Update stepper progress
    function rdvUpdateStepper(activeStep, doneSteps) {
        var steps = document.querySelectorAll('#rdvStepper .rdv-step');
        steps.forEach(function(el){
            var n = parseInt(el.getAttribute('data-step'), 10);
            el.classList.remove('rdv-step--active','rdv-step--done');
            if (doneSteps.indexOf(n) > -1) el.classList.add('rdv-step--done');
            if (n === activeStep) el.classList.add('rdv-step--active');
        });
    }

    document.addEventListener('change', function(e){
        if (!e.target) return;
        var name = e.target.name || '';
        if (name === 'personeller[]' || name === 'randevutarihi' || name === 'randevusaati') {
            rdvUpdateOzet();
            if (name === 'randevusaati') rdvUpdateStepper(4, [1,2,3]);
            else if (name === 'randevutarihi') rdvUpdateStepper(3, [1,2]);
            else if (name === 'personeller[]') rdvUpdateStepper(2, [1]);

            // Fallback time-slot fetch: if iCheck's ifChecked handler is active it will
            // also fetch /saatgetir; debounce so we never issue two AJAX calls for the
            // same tarih change.
            if (name === 'randevutarihi' && window.jQuery) {
                var $ = window.jQuery;
                if (window.__rdvSaatLock) return;
                window.__rdvSaatLock = true;
                setTimeout(function(){ window.__rdvSaatLock = false; }, 400);

                // If #saatsecimtablosu already has fresh content for this date, skip.
                // Otherwise wait a tick to let ifChecked run; if nothing happened, fetch.
                var before = $('#saatsecimtablosu').html();
                setTimeout(function(){
                    if ($('#saatsecimtablosu').html() !== before) return; // ifChecked already handled it
                    var secilenPersoneller = [];
                    $('select[name="personeller[]"] option:selected').each(function(){ secilenPersoneller.push($(this).val()); });
                    var secilenHizmetler = [];
                    $('input[name="hizmetler[]"]').each(function(){ secilenHizmetler.push($(this).val()); });
                    $.ajax({
                        type: 'GET',
                        url: '/saatgetir',
                        data: {
                            randevutarihi: e.target.value,
                            isletmeno: $('#salonid').val() || $('#salonno').val(),
                            secilenpersoneller: secilenPersoneller,
                            secilenhizmetler: secilenHizmetler,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'text',
                        beforeSend: function(){ $('#preloader').show(); },
                        success: function(result){ $('#preloader').hide(); $('#saatsecimtablosu').html(result); },
                        error: function(){ $('#preloader').hide(); }
                    });
                }, 250);
            }
        }
    });

    // When the personnel form is submitted, advance to step 3
    var pForm = document.getElementById('randevuhizmetvepersonelleri');
    if (pForm) {
        pForm.addEventListener('submit', function(){
            setTimeout(function(){ rdvUpdateOzet(); rdvUpdateStepper(3,[1,2]); }, 10);
        });
    }

    // Initial pass
    rdvUpdateOzet();
})();
</script>

@endsection
