@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2 style="font-size:24px;font-weight:800">Yeni Salon (Demo)</h2>
        <div class="subtitle">Demo hesabı oluşturur — giriş bilgileri kurulur, salon hemen giriş yapabilir.</div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/salonlar" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Liste</a>
    </div>
</div>

<form method="post" action="/sistemyonetim/v2/salon-ekle">
    @csrf

    <div class="sy-card sy-mt-12">
        <div class="sy-card-head">
            <h3><span class="mdi mdi-store-plus"></span> Salon Bilgileri</h3>
        </div>
        <div class="sy-card-body">
            <div class="sy-form-group">
                <label>Salon Adı <span style="color:var(--sy-danger)">*</span></label>
                <input type="text" name="salon_adi" class="sy-input" value="{{ old('salon_adi') }}" required autofocus>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İşletme Türü <span style="color:var(--sy-danger)">*</span></label>
                    <select name="salon_turu_id" class="sy-select" required>
                        <option value="">Seçiniz</option>
                        @foreach($salonTurleri as $st)
                            <option value="{{ $st->id }}" {{ old('salon_turu_id') == $st->id ? 'selected' : '' }}>{{ $st->salon_turu_adi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sy-form-group">
                    <label>Demo Süresi (gün)</label>
                    <input type="number" name="demo_gun" class="sy-input" value="{{ old('demo_gun', 7) }}" min="1" max="3650">
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İl</label>
                    <select name="il_id" id="sy-il-select" class="sy-select">
                        <option value="">Seçiniz</option>
                        @foreach($iller as $il)
                            <option value="{{ $il->id }}" {{ old('il_id') == $il->id ? 'selected' : '' }}>{{ $il->il_adi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sy-form-group">
                    <label>İlçe</label>
                    <select name="ilce_id" id="sy-ilce-select" class="sy-select">
                        <option value="">Seçiniz</option>
                    </select>
                </div>
            </div>

            <div class="sy-form-group">
                <label>Adres</label>
                <input type="text" name="adres" class="sy-input" value="{{ old('adres') }}">
            </div>
        </div>
    </div>

    <div class="sy-card sy-mt-12">
        <div class="sy-card-head">
            <h3><span class="mdi mdi-account-key"></span> Yetkili / Giriş Bilgileri</h3>
            <span class="sy-text-muted sy-fs-12">Demo hesabı bu bilgilerle giriş yapar</span>
        </div>
        <div class="sy-card-body">
            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Yetkili Adı <span style="color:var(--sy-danger)">*</span></label>
                    <input type="text" name="yetkili_ad" class="sy-input" value="{{ old('yetkili_ad') }}" required>
                </div>
                <div class="sy-form-group">
                    <label>Yetkili Telefon <span style="color:var(--sy-danger)">*</span></label>
                    <input type="text" name="yetkili_telefon" class="sy-input" value="{{ old('yetkili_telefon') }}" placeholder="05xx xxx xx xx" required>
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Yetkili E-posta <span style="color:var(--sy-danger)">*</span></label>
                    <input type="email" name="yetkili_email" class="sy-input" value="{{ old('yetkili_email') }}" placeholder="giris@ornek.com" required>
                </div>
                <div class="sy-form-group">
                    <label>Şifre <span class="sy-text-muted sy-fs-12">(boş bırakılırsa otomatik üretilir)</span></label>
                    <input type="text" name="yetkili_sifre" class="sy-input" value="{{ old('yetkili_sifre') }}" placeholder="Otomatik">
                </div>
            </div>
        </div>
    </div>

    <div class="sy-flex-row sy-mt-12" style="gap:8px">
        <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-check"></span> Demo Salon Oluştur</button>
        <a href="/sistemyonetim/v2/salonlar" class="sy-btn">Vazgeç</a>
    </div>
</form>

<script>
(function () {
    var ilceData = {!! json_encode($ilceler->map(function ($i) { return ['id' => (int) $i->id, 'il_id' => (int) $i->il_id, 'ad' => $i->ilce_adi]; })->values()) !!};
    var ilSel = document.getElementById('sy-il-select');
    var ilceSel = document.getElementById('sy-ilce-select');
    var seciliIlce = {{ (int) old('ilce_id', 0) }};
    if (!ilSel || !ilceSel) return;

    function doldur() {
        var il = parseInt(ilSel.value, 10) || 0;
        ilceSel.innerHTML = '<option value="">Seçiniz</option>';
        for (var k = 0; k < ilceData.length; k++) {
            var d = ilceData[k];
            if (d.il_id === il) {
                var o = document.createElement('option');
                o.value = d.id;
                o.textContent = d.ad;
                if (d.id === seciliIlce) o.selected = true;
                ilceSel.appendChild(o);
            }
        }
    }
    ilSel.addEventListener('change', function () { seciliIlce = 0; doldur(); });
    doldur();
})();
</script>

@endsection
