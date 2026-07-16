@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2 style="font-size:24px;font-weight:800"><span class="mdi mdi-whatsapp" style="color:#25d366"></span> Sistem WhatsApp</h2>
        <div class="subtitle">Müşteri yeni demo açınca sana <b>SMS</b> gelir (salon adı + yetkili + telefon → hemen ararsın). İstersen ek olarak WhatsApp: kendi bağlı salon hattından.</div>
    </div>
</div>

<div class="sy-card sy-mt-12">
    <div class="sy-card-head">
        <h3><span class="mdi mdi-bell-ring"></span> Bildirim Ayarı</h3>
    </div>
    <div class="sy-card-body">
        <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/ayar" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
            @csrf
            <div class="sy-form-group" style="margin:0;min-width:220px">
                <label>Bildirim Numarası (senin numaran)</label>
                <input type="text" name="numara" class="sy-input" value="{{ $ayar['numara'] }}" placeholder="05xx xxx xx xx">
            </div>

            <div class="sy-form-group" style="margin:0;min-width:260px">
                <label>WhatsApp Gönderen Salon <span class="sy-text-muted sy-fs-12">(opsiyonel)</span></label>
                <select name="gonderen_salon_id" class="sy-select">
                    <option value="">Sadece SMS (WhatsApp gönderme)</option>
                    @foreach($bagliSalonlar as $bs)
                        <option value="{{ $bs->id }}" {{ (int)($ayar['gonderen_salon_id'] ?? 0) === (int)$bs->id ? 'selected' : '' }}>
                            {{ $bs->salon_adi }}{{ $bs->whatsapp_numara ? ' ('.$bs->whatsapp_numara.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sy-form-group" style="margin:0">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                    <input type="checkbox" name="aktif" value="1" {{ $ayar['aktif'] ? 'checked' : '' }}> Bildirimler açık
                </label>
            </div>
            <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
        </form>

        @if($bagliSalonlar->isEmpty())
            <div class="sy-alert sy-alert-warning" style="margin-top:12px">
                <span class="mdi mdi-alert"></span> Şu an WhatsApp'ı <b>bağlı</b> salon yok. WhatsApp gönderimi için en az bir salonun WhatsApp'ı bağlı olmalı (SMS yine de gider). Salon paneli → WhatsApp'tan bağlayabilirsin.
            </div>
        @else
            <div class="sy-text-muted sy-fs-12" style="margin-top:10px">
                <span class="mdi mdi-information-outline"></span> Not: Bildirim numarası, seçilen gönderen salonun WhatsApp numarasıyla <b>aynı olmamalı</b> (WhatsApp kendine mesaj göndermez).
            </div>
        @endif

        <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/test" style="margin-top:14px">
            @csrf
            <button type="submit" class="sy-btn sy-btn-soft"><span class="mdi mdi-send-check"></span> Test Mesajı Gönder (WA + SMS)</button>
        </form>
    </div>
</div>

@endsection
