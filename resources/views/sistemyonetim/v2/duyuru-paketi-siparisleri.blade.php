@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $durumEt = [0 => ['Beklemede','warning'], 1 => ['Ödendi','success'], 2 => ['Başarısız','danger']];
    $faturaEt = [0 => ['Kesilmedi','muted'], 1 => ['Kesildi','success'], 2 => ['Hata','danger']];
@endphp

<div class="sy-page-head">
    <div>
        <h2>Duyuru Paketi Siparişleri</h2>
        <div class="subtitle">SMS (duyuru) paketi satın alımları · Ödenenleri VoiceTelekom panelinden yükleyip "Yüklendi" işaretleyin</div>
    </div>
    @if($bekleyen > 0)
        <span class="sy-badge sy-badge-warning" style="font-size:14px;padding:8px 14px">
            <span class="mdi mdi-bell-alert"></span> {{ $bekleyen }} bekleyen yükleme
        </span>
    @endif
</div>

<div class="sy-card">
    <div class="sy-card-body tight">
        <table class="sy-table">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Salon</th>
                    <th>Paket</th>
                    <th class="sy-text-right">Tutar</th>
                    <th>Ödeme</th>
                    <th>Yükleme</th>
                    <th>Fatura</th>
                    <th class="sy-text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siparisler as $s)
                    @php
                        $d = $durumEt[$s->durum] ?? ['—','muted'];
                        $f = $faturaEt[$s->fatura_durumu] ?? ['—','muted'];
                    @endphp
                    <tr>
                        <td class="sy-fs-12 nowrap">{{ \Carbon\Carbon::parse($s->created_at)->format('d.m.Y H:i') }}</td>
                        <td class="sy-fs-13">{{ $salonlar[$s->salon_id] ?? ('Salon #'.$s->salon_id) }}</td>
                        <td class="sy-fw-600">{{ number_format($s->sms_adet, 0, ',', '.') }} SMS</td>
                        <td class="sy-text-right">{{ number_format($s->tutar, 2, ',', '.') }} ₺</td>
                        <td><span class="sy-badge sy-badge-{{ $d[1] }}">{{ $d[0] }}</span></td>
                        <td>
                            @if($s->yukleme_durumu == 1)
                                <span class="sy-badge sy-badge-success">Yüklendi</span>
                                <div class="sy-text-muted sy-fs-12">{{ $s->yukleme_tarihi ? \Carbon\Carbon::parse($s->yukleme_tarihi)->format('d.m.Y H:i') : '' }}</div>
                            @elseif($s->durum == 1)
                                <span class="sy-badge sy-badge-warning">Bekliyor</span>
                            @else
                                <span class="sy-text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="sy-badge sy-badge-{{ $f[1] }}">{{ $f[0] }}</span></td>
                        <td class="sy-text-right nowrap">
                            @if($s->durum == 1 && $s->yukleme_durumu == 0)
                                <form method="post" action="/sistemyonetim/v2/duyuru-paketi-siparisleri/{{ $s->id }}/yuklendi" style="display:inline" onsubmit="return confirm('VoiceTelekom panelinden SMS yüklemesini yaptınız mı?')">
                                    @csrf
                                    <button class="sy-btn sy-btn-sm sy-btn-primary"><span class="mdi mdi-check"></span> Yüklendi</button>
                                </form>
                            @else
                                <span class="sy-text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="sy-empty"><div class="icon mdi mdi-cart-outline"></div><div class="baslik">Henüz sipariş yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-pagination">{{ $siparisler->links() }}</div>

@endsection
