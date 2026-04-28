@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2>Sistem Sağlık</h2>
        <div class="subtitle">Veritabanı, disk, log ve servis durumu</div>
    </div>
</div>

<div class="sy-metric-grid">
    <div class="sy-metric {{ $dbDurum === 'OK' ? 'success' : 'danger' }}">
        <div class="icon-bg mdi mdi-database"></div>
        <div class="label">Veritabanı</div>
        <div class="value">{{ $dbDurum === 'OK' ? 'OK' : 'HATA' }}</div>
        <div class="delta">{{ $dbVersion ?? '—' }}</div>
    </div>
    <div class="sy-metric info">
        <div class="icon-bg mdi mdi-language-php"></div>
        <div class="label">PHP</div>
        <div class="value">{{ $phpVersion }}</div>
        <div class="delta">Laravel {{ $laravelVersion }}</div>
    </div>
    <div class="sy-metric {{ $diskKullanim > 90 ? 'danger' : ($diskKullanim > 75 ? 'warning' : 'success') }}">
        <div class="icon-bg mdi mdi-harddisk"></div>
        <div class="label">Disk Kullanımı</div>
        <div class="value">%{{ $diskKullanim }}</div>
        <div class="delta">{{ round($diskFree / 1073741824, 1) }} GB boş / {{ round($diskTotal / 1073741824, 1) }} GB</div>
        <div class="sy-progress sy-mt-12"><div class="fill" style="width: {{ $diskKullanim }}%"></div></div>
    </div>
    <div class="sy-metric success">
        <div class="icon-bg mdi mdi-whatsapp"></div>
        <div class="label">WhatsApp Aktif Salon</div>
        <div class="value">{{ $whatsappAktif }}</div>
    </div>
</div>

<div class="sy-card">
    <div class="sy-card-head">
        <h3>Son Log Hataları (laravel.log)</h3>
        <span class="sy-text-muted sy-fs-12">{{ count($logHatalari) }} kayıt</span>
    </div>
    <div class="sy-card-body">
        @if(empty($logHatalari))
            <div class="sy-empty"><div class="icon mdi mdi-check-all"></div><div class="baslik">Hata yok</div></div>
        @else
            <div style="background:#1d1235; color:#f4eafd; border-radius:10px; padding:14px; font-family: 'SF Mono', Consolas, monospace; font-size:11.5px; max-height:480px; overflow:auto">
                @foreach($logHatalari as $line)
                    <div style="padding:6px 0; border-bottom:1px solid #2e1d4d">{{ $line }}</div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
