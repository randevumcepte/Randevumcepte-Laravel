@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $rolEt = ['super_admin'=>'Süper Admin','yonetici'=>'Yönetici','destek'=>'Destek','izleyici'=>'İzleyici'];
    $cozumOrani = ($genel->toplam ?? 0) > 0 ? round(($genel->cozulen / $genel->toplam) * 100, 1) : 0;
@endphp

<div class="sy-page-head">
    <div>
        <h2>Ekip Performansı</h2>
        <div class="subtitle">Son {{ $gunler }} günde ekibin yükü ve SLA göstergeleri</div>
    </div>
    <form method="get" class="sy-flex-row">
        <select name="gun" class="sy-select" onchange="this.form.submit()">
            <option value="7" {{ $gunler==7?'selected':'' }}>Son 7 gün</option>
            <option value="30" {{ $gunler==30?'selected':'' }}>Son 30 gün</option>
            <option value="90" {{ $gunler==90?'selected':'' }}>Son 90 gün</option>
            <option value="180" {{ $gunler==180?'selected':'' }}>Son 180 gün</option>
        </select>
    </form>
</div>

<div class="sy-metric-grid">
    <div class="sy-metric"><div class="icon-bg mdi mdi-lifebuoy"></div><div class="label">Toplam Talep</div><div class="value">{{ $genel->toplam ?? 0 }}</div></div>
    <div class="sy-metric success"><div class="icon-bg mdi mdi-check-all"></div><div class="label">Çözülen</div><div class="value">{{ $genel->cozulen ?? 0 }}</div><div class="delta">%{{ $cozumOrani }} çözüm oranı</div></div>
    <div class="sy-metric info"><div class="icon-bg mdi mdi-clock-fast"></div><div class="label">Ort. İlk Yanıt</div><div class="value">@if($genel->ort_yanit_dk){{ round($genel->ort_yanit_dk, 0) }} dk@else—@endif</div></div>
    <div class="sy-metric warning"><div class="icon-bg mdi mdi-progress-clock"></div><div class="label">Ort. Çözüm</div><div class="value">@if($genel->ort_cozum_saat){{ round($genel->ort_cozum_saat, 0) }} sa@else—@endif</div></div>
</div>

<div class="sy-card">
    <div class="sy-card-head"><h3>Personel Bazlı</h3></div>
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Üye</th>
                    <th>Rol</th>
                    <th class="sy-text-right">Atanan</th>
                    <th class="sy-text-right">Çözülen</th>
                    <th class="sy-text-right">Açık</th>
                    <th class="sy-text-right">İlk Yanıt</th>
                    <th class="sy-text-right">Çözüm</th>
                    <th class="sy-text-right">Mesaj</th>
                    <th class="sy-text-right">Salon Girişi</th>
                    <th class="sy-text-right">Aktivite</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perf as $p)
                    @php $cOrani = $p['toplam'] > 0 ? round(($p['cozulen'] / $p['toplam']) * 100) : 0; @endphp
                    <tr>
                        <td>
                            <div class="sy-flex-row">
                                <div style="width:30px;height:30px;border-radius:50%;background:var(--sy-primary-soft);color:var(--sy-primary);display:flex;align-items:center;justify-content:center;font-weight:600">{{ mb_substr($p['user']->name, 0, 1) }}</div>
                                <strong>{{ $p['user']->name }}</strong>
                            </div>
                        </td>
                        <td><span class="sy-badge sy-badge-muted">{{ $rolEt[$p['rol']] ?? $p['rol'] }}</span></td>
                        <td class="sy-text-right"><strong>{{ $p['toplam'] }}</strong></td>
                        <td class="sy-text-right">
                            <strong style="color:var(--sy-success)">{{ $p['cozulen'] }}</strong>
                            @if($p['toplam'] > 0)<div class="sy-text-muted sy-fs-12">%{{ $cOrani }}</div>@endif
                        </td>
                        <td class="sy-text-right">{{ $p['acik'] > 0 ? $p['acik'] : '—' }}</td>
                        <td class="sy-text-right sy-fs-13">{{ $p['ort_yanit_dk'] ? round($p['ort_yanit_dk']).' dk' : '—' }}</td>
                        <td class="sy-text-right sy-fs-13">{{ $p['ort_cozum_saat'] ? round($p['ort_cozum_saat']).' sa' : '—' }}</td>
                        <td class="sy-text-right">{{ $p['mesaj_sayisi'] }}</td>
                        <td class="sy-text-right">{{ $p['imp_count'] }}</td>
                        <td class="sy-text-right">{{ $p['aktivite_sayisi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="sy-card sy-mt-18">
    <div class="sy-card-body">
        <div class="sy-fs-12 sy-text-muted">
            <strong>İlk Yanıt:</strong> ticket açılışından ekipten ilk mesaja geçen süre. <strong>Çözüm:</strong> ticket açılışından durum 'çözümlendi'ye geçişe kadar. <strong>Salon Girişi:</strong> impersonation sayısı. <strong>Aktivite:</strong> audit log kaydı sayısı.
        </div>
    </div>
</div>

@endsection
