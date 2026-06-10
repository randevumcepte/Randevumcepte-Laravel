@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>{{ $paket ? $paket->sms_adet.' SMS — Düzenle' : 'Yeni İnteraktif Duyuru Paketi' }}</h2>
        <div class="subtitle">Salon panelinde "SATIN AL" olarak gözükür · Fiyat tüm vergiler dahil girilir</div>
    </div>
    <a href="/sistemyonetim/v2/sms-paket" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Geri</a>
</div>

<form method="post" action="{{ $paket ? '/sistemyonetim/v2/sms-paket/'.$paket->id : '/sistemyonetim/v2/sms-paket' }}">
    @csrf
    @if($paket)<input type="hidden" name="_method" value="PUT">@endif

    <div class="sy-grid-2-1">
        <div class="sy-card">
            <div class="sy-card-head"><h3>Paket Bilgileri</h3></div>
            <div class="sy-card-body">
                <div class="sy-form-row">
                    <div class="sy-form-group">
                        <label>SMS Adedi *</label>
                        <input type="number" name="sms_adet" class="sy-input" min="1" step="1" required
                               value="{{ old('sms_adet', $paket->sms_adet ?? '') }}" placeholder="1000">
                    </div>
                    <div class="sy-form-group">
                        <label>Tutar — KDV/Tüm Vergiler Dahil (₺) *</label>
                        <input type="number" name="ucret" class="sy-input" min="0" step="0.01" required
                               value="{{ old('ucret', $paket->ucret ?? '') }}" placeholder="500.00">
                        <div class="sy-text-muted sy-fs-12 sy-mt-12">Müşteriye gösterilen toplam tutar. KDV bu tutarın içindedir.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sy-stack">
            <div class="sy-card">
                <div class="sy-card-head"><h3>Görünüm</h3></div>
                <div class="sy-card-body">
                    @php $secili = old('class', $paket->class ?? 'primary'); @endphp
                    <div class="sy-form-group">
                        <label>Kart Rengi (tema)</label>
                        <select name="class" class="sy-select">
                            @foreach($classSecenekleri as $c)
                                <option value="{{ $c }}" {{ $secili == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                        <div class="sy-text-muted sy-fs-12 sy-mt-12">Salon panelindeki paket kartının rengini belirler.</div>
                    </div>
                </div>
            </div>

            <div class="sy-card">
                <div class="sy-card-body sy-flex-row" style="justify-content:flex-end">
                    <a href="/sistemyonetim/v2/sms-paket" class="sy-btn">İptal</a>
                    <button class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
