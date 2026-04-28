@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $durumRenk = ['kritik'=>'danger','riskli'=>'warning','orta'=>'info','iyi'=>'success'];
@endphp

<div class="sy-page-head">
    <div>
        <h2>Risk Altındaki Salonlar</h2>
        <div class="subtitle">Skor 50'nin altındaki salonlar (otomatik tespit)</div>
    </div>
</div>

<div class="sy-card sy-mt-12">
    <div class="sy-card-body">
        <div class="sy-fs-13 sy-text-muted">
            <strong>Skor faktörleri:</strong>
            son giriş zamanı, son randevu zamanı, 30 günlük randevu hacmi, açık talep sayısı, son 30 gün şikayet, askıda durumu.
            <strong>Durum:</strong>
            <span class="sy-badge sy-badge-success">iyi</span> ≥75 ·
            <span class="sy-badge sy-badge-info">orta</span> 50-74 ·
            <span class="sy-badge sy-badge-warning">riskli</span> 25-49 ·
            <span class="sy-badge sy-badge-danger">kritik</span> &lt;25
        </div>
    </div>
</div>

<div class="sy-card sy-mt-18">
    <div class="sy-card-head">
        <h3>{{ count($list) }} salon risk altında</h3>
    </div>
    <div class="sy-card-body tight">
        @if(count($list) === 0)
            <div class="sy-empty"><div class="icon mdi mdi-emoticon-happy"></div><div class="baslik">Hepsi sağlıklı!</div><div>Risk altında salon yok.</div></div>
        @else
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Salon</th>
                    <th>Skor</th>
                    <th>Durum</th>
                    <th>Sebepler</th>
                    <th>MT</th>
                    <th class="sy-text-right">Aksiyon</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $r)
                    <tr>
                        <td>
                            <div class="sy-fw-600">{{ $r['salon_adi'] }}</div>
                            <div class="sy-text-muted sy-fs-12">ID: {{ $r['salon_id'] }}</div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <strong style="font-size:18px;color:var(--sy-{{ $durumRenk[$r['durum']] ?? 'muted' }})">{{ $r['skor'] }}</strong>
                                <div class="sy-progress" style="flex:1;width:60px"><div class="fill" style="width:{{ $r['skor'] }}%;background:var(--sy-{{ $durumRenk[$r['durum']] ?? 'muted' }})"></div></div>
                            </div>
                        </td>
                        <td><span class="sy-badge sy-badge-{{ $durumRenk[$r['durum']] ?? 'muted' }}">{{ $r['durum'] }}</span></td>
                        <td class="sy-fs-13">
                            <ul style="margin:0;padding-left:16px">
                                @foreach($r['sebepler'] as $s)
                                    <li>{{ $s }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="sy-fs-13">{{ $mtMap[$r['mt_id']] ?? '—' }}</td>
                        <td class="sy-text-right nowrap">
                            <a href="/sistemyonetim/v2/salon/{{ $r['salon_id'] }}" class="sy-btn sy-btn-sm sy-btn-soft"><span class="mdi mdi-information-outline"></span> Detay</a>
                            <a href="/sistemyonetim/v2/ticket/yeni?salon_id={{ $r['salon_id'] }}" class="sy-btn sy-btn-sm sy-btn-primary"><span class="mdi mdi-phone"></span> İletişim Aç</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@endsection
