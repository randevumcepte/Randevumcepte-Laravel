@extends('sistemyonetim.v2.layout')

@section('content')

@php
    // Fiyatlar tüm vergiler dahil girilir → matrah = ucret / 1,20
    $kdvOran = 0.20;
@endphp

<div class="sy-page-head">
    <div>
        <h2>İnteraktif Duyuru Paketleri</h2>
        <div class="subtitle">Salonlara satılan SMS (duyuru) paketleri · Fiyatlar tüm vergiler dahildir</div>
    </div>
    <a href="/sistemyonetim/v2/sms-paket/yeni" class="sy-btn sy-btn-primary"><span class="mdi mdi-plus"></span> Yeni Paket</a>
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Paket</th>
                    <th class="sy-text-right">Tutar (KDV dahil)</th>
                    <th class="sy-text-right">Matrah</th>
                    <th class="sy-text-right">KDV (%20)</th>
                    <th>Kart Rengi</th>
                    <th class="sy-text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paketler as $p)
                    @php
                        $tutar  = (float) $p->ucret;
                        $matrah = $tutar / (1 + $kdvOran);
                        $kdv    = $tutar - $matrah;
                    @endphp
                    <tr>
                        <td class="sy-fw-600">
                            {{ $p->paket_adi ?: (number_format($p->sms_adet, 0, ',', '.').' SMS') }}
                            @if($p->paket_adi)<div class="sy-text-muted sy-fs-12">{{ number_format($p->sms_adet, 0, ',', '.') }} SMS</div>@endif
                            @if($p->alt_baslik)<div class="sy-text-muted sy-fs-12">{{ $p->alt_baslik }}</div>@endif
                        </td>
                        <td class="sy-text-right sy-fw-600">{{ number_format($tutar, 2, ',', '.') }} ₺</td>
                        <td class="sy-text-right sy-fs-13">{{ number_format($matrah, 2, ',', '.') }} ₺</td>
                        <td class="sy-text-right sy-fs-13">{{ number_format($kdv, 2, ',', '.') }} ₺</td>
                        <td><span class="sy-badge sy-badge-info">{{ $p->class ?: 'primary' }}</span></td>
                        <td class="sy-text-right nowrap">
                            <a href="/sistemyonetim/v2/sms-paket/{{ $p->id }}/duzenle" class="sy-btn sy-btn-sm sy-btn-soft"><span class="mdi mdi-pencil"></span></a>
                            <form method="post" action="/sistemyonetim/v2/sms-paket/{{ $p->id }}" style="display:inline" data-confirm="Bu paket silinsin mi?" data-confirm-danger>
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="sy-btn sy-btn-sm sy-btn-danger"><span class="mdi mdi-delete"></span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="sy-empty"><div class="icon mdi mdi-message-text-outline"></div><div class="baslik">Henüz paket yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
