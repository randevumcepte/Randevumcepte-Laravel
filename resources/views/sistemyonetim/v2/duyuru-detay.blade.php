@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $tipRenk = ['bilgi'=>'info','uyari'=>'warning','onemli'=>'danger','bakim'=>'muted','kampanya'=>'success'];
    $hedefEt = ['hepsi'=>'Tüm Salonlar','secili'=>'Seçili Salonlar','il'=>'İle Göre'];
@endphp

<div class="sy-page-head">
    <div>
        <h2>{{ $duyuru->baslik }}</h2>
        <div class="subtitle">
            <span class="sy-badge sy-badge-{{ $tipRenk[$duyuru->tip] ?? 'muted' }}">{{ $duyuru->tip }}</span>
            <span class="sy-badge sy-badge-muted">{{ $hedefEt[$duyuru->hedef_tipi] ?? $duyuru->hedef_tipi }}</span>
            · {{ $duyuru->olusturan_user_name }} tarafından {{ \Carbon\Carbon::parse($duyuru->created_at)->format('d.m.Y H:i') }}
        </div>
    </div>
    <div class="sy-flex-row">
        <a href="/sistemyonetim/v2/duyuru" class="sy-btn"><span class="mdi mdi-arrow-left"></span> Liste</a>
        <a href="/sistemyonetim/v2/duyuru/{{ $duyuru->id }}/duzenle" class="sy-btn sy-btn-primary"><span class="mdi mdi-pencil"></span> Düzenle</a>
    </div>
</div>

<div class="sy-grid-2-1">
    <div class="sy-card">
        <div class="sy-card-head"><h3>İçerik</h3></div>
        <div class="sy-card-body">
            <div style="font-size:14px;line-height:1.7">{!! nl2br(e($duyuru->icerik)) !!}</div>
            @if($duyuru->cta_metin && $duyuru->cta_link)
                <div class="sy-mt-18">
                    <a href="{{ $duyuru->cta_link }}" class="sy-btn sy-btn-primary" target="_blank">{{ $duyuru->cta_metin }}</a>
                </div>
            @endif
        </div>
    </div>

    <div class="sy-stack">
        <div class="sy-metric-grid" style="grid-template-columns:1fr;gap:12px">
            <div class="sy-metric">
                <div class="icon-bg mdi mdi-target"></div>
                <div class="label">Hedef Salon</div>
                <div class="value">{{ $hedefSayisi }}</div>
            </div>
            <div class="sy-metric success">
                <div class="icon-bg mdi mdi-eye-check"></div>
                <div class="label">Okundu</div>
                <div class="value">{{ count($okundu) }}</div>
                <div class="delta">@if($hedefSayisi)%{{ round((count($okundu)/$hedefSayisi)*100, 1) }}@endif</div>
            </div>
        </div>

        <div class="sy-card">
            <div class="sy-card-head"><h3>Geçerlilik</h3></div>
            <div class="sy-card-body">
                <div class="sy-fs-13">
                    <strong>Başlangıç:</strong> {{ $duyuru->baslangic_tarihi ? \Carbon\Carbon::parse($duyuru->baslangic_tarihi)->format('d.m.Y H:i') : '—' }}<br>
                    <strong>Bitiş:</strong> {{ $duyuru->bitis_tarihi ? \Carbon\Carbon::parse($duyuru->bitis_tarihi)->format('d.m.Y H:i') : 'süresiz' }}<br>
                    <strong>Durum:</strong> {!! $duyuru->aktif ? '<span class="sy-badge sy-badge-success">Aktif</span>' : '<span class="sy-badge sy-badge-muted">Pasif</span>' !!}
                    @if($duyuru->sticky)<span class="sy-badge sy-badge-warning"><span class="mdi mdi-pin"></span> Sabit</span>@endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sy-card sy-mt-24">
    <div class="sy-card-head"><h3>Okuyanlar ({{ count($okundu) }})</h3></div>
    <div class="sy-card-body tight">
        @if(count($okundu) === 0)
            <div class="sy-empty"><div class="icon mdi mdi-eye-off"></div><div class="baslik">Henüz okuyan yok</div></div>
        @else
            <table class="sy-table">
                <thead><tr><th>Salon</th><th>Yetkili</th><th>Okundu</th></tr></thead>
                <tbody>
                    @foreach($okundu as $o)
                        <tr>
                            <td>{{ $o->salon_adi ?: '—' }}</td>
                            <td>{{ $o->yetkili_adi ?: '—' }}</td>
                            <td class="sy-text-muted sy-fs-13">{{ \Carbon\Carbon::parse($o->okundu_tarihi)->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection
