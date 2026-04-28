@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Yeni Destek Talebi</h2>
        <div class="subtitle">Salon adına ya da içeriden talep aç</div>
    </div>
    <a href="/sistemyonetim/v2/ticket" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Geri</a>
</div>

<div class="sy-card" style="max-width:880px">
    <form method="post" action="/sistemyonetim/v2/ticket">
        @csrf
        <div class="sy-card-body">
            <div class="sy-form-group">
                <label>Konu *</label>
                <input type="text" name="konu" class="sy-input" required value="{{ old('konu') }}" placeholder="Kısa özet">
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İlgili Salon</label>
                    <select name="salon_id" class="sy-select">
                        <option value="">— yok / iç talep —</option>
                        @foreach($salonlar as $s)
                            <option value="{{ $s->id }}" {{ request('salon_id')==$s->id?'selected':'' }}>{{ $s->salon_adi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sy-form-group">
                    <label>Kategori *</label>
                    <select name="kategori" class="sy-select" required>
                        <option value="teknik">Teknik</option>
                        <option value="odeme">Ödeme</option>
                        <option value="egitim">Eğitim</option>
                        <option value="ozellik">Özellik İsteği</option>
                        <option value="sikayet">Şikayet</option>
                        <option value="diger" selected>Diğer</option>
                    </select>
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Öncelik *</label>
                    <select name="oncelik" class="sy-select" required>
                        <option value="dusuk">Düşük</option>
                        <option value="orta" selected>Orta</option>
                        <option value="yuksek">Yüksek</option>
                        <option value="acil">Acil</option>
                    </select>
                </div>
                <div class="sy-form-group">
                    <label>İletişim — Ad</label>
                    <input type="text" name="iletisim_ad" class="sy-input" value="{{ old('iletisim_ad') }}">
                </div>
            </div>

            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>İletişim — Telefon</label>
                    <input type="text" name="iletisim_telefon" class="sy-input" value="{{ old('iletisim_telefon') }}">
                </div>
                <div class="sy-form-group">
                    <label>İletişim — E-posta</label>
                    <input type="email" name="iletisim_email" class="sy-input" value="{{ old('iletisim_email') }}">
                </div>
            </div>

            <div class="sy-form-group">
                <label>Açıklama</label>
                <textarea name="aciklama" class="sy-textarea" rows="6" placeholder="Detaylar..."></textarea>
            </div>

            <div class="sy-flex-row" style="justify-content:flex-end">
                <a href="/sistemyonetim/v2/ticket" class="sy-btn">İptal</a>
                <button class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Talep Oluştur</button>
            </div>
        </div>
    </form>
</div>

@endsection
