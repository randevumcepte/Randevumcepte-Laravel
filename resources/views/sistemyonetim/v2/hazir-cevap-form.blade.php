@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>{{ $item ? $item->baslik.' — Düzenle' : 'Yeni Hazır Cevap' }}</h2>
        <div class="subtitle">Şablon değişkenler: <code>{salon_adi}</code>, <code>{musteri_adi}</code>, <code>{ekip_adi}</code></div>
    </div>
    <a href="/sistemyonetim/v2/hazir-cevap" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Geri</a>
</div>

<div class="sy-card" style="max-width:880px">
    <form method="post" action="{{ $item ? '/sistemyonetim/v2/hazir-cevap/'.$item->id : '/sistemyonetim/v2/hazir-cevap' }}">
        @csrf
        @if($item)<input type="hidden" name="_method" value="PUT">@endif
        <div class="sy-card-body">
            <div class="sy-form-row">
                <div class="sy-form-group">
                    <label>Başlık *</label>
                    <input type="text" name="baslik" class="sy-input" required value="{{ old('baslik', $item->baslik ?? '') }}" placeholder="Örn: WhatsApp aktivasyonu">
                </div>
                <div class="sy-form-group">
                    <label>Kategori *</label>
                    @php $k = old('kategori', $item->kategori ?? 'genel'); @endphp
                    <select name="kategori" class="sy-select" required>
                        <option value="genel" {{ $k=='genel'?'selected':'' }}>Genel</option>
                        <option value="teknik" {{ $k=='teknik'?'selected':'' }}>Teknik</option>
                        <option value="odeme" {{ $k=='odeme'?'selected':'' }}>Ödeme</option>
                        <option value="egitim" {{ $k=='egitim'?'selected':'' }}>Eğitim</option>
                        <option value="iade" {{ $k=='iade'?'selected':'' }}>İade</option>
                        <option value="kapanis" {{ $k=='kapanis'?'selected':'' }}>Kapanış</option>
                    </select>
                </div>
            </div>

            <div class="sy-form-group">
                <label>Kısayol (opsiyonel)</label>
                <input type="text" name="kisayol" class="sy-input" maxlength="30" value="{{ old('kisayol', $item->kisayol ?? '') }}" placeholder="/wa-aktif">
                <div class="sy-text-muted sy-fs-12">Yanıt formunda hızlı bulmak için (örn: <code>/wa-aktif</code>)</div>
            </div>

            <div class="sy-form-group">
                <label>İçerik *</label>
                <textarea name="icerik" class="sy-textarea" rows="10" required>{{ old('icerik', $item->icerik ?? '') }}</textarea>
            </div>

            <div class="sy-form-group">
                <label class="sy-flex-row" style="align-items:center"><input type="checkbox" name="aktif" value="1" {{ ($item && $item->aktif) || !$item ? 'checked' : '' }}> Aktif (yanıt formunda görünsün)</label>
            </div>

            <div class="sy-flex-row" style="justify-content:flex-end">
                <a href="/sistemyonetim/v2/hazir-cevap" class="sy-btn">İptal</a>
                <button class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
            </div>
        </div>
    </form>
</div>

@endsection
