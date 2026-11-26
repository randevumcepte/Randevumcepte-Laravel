@extends('sistemyonetim.v2.layout')

@section('content')
<style>
    .bs-ozet { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:18px; }
    .bs-kutu { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:14px 16px; box-shadow:var(--sy-shadow-sm); }
    .bs-kutu .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; }
    .bs-kutu .d { font-size:21px; font-weight:700; margin-top:4px; color:var(--sy-text,#2a1f48); font-variant-numeric:tabular-nums; }
    .bs-kutu .a { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .bs-sekme { display:flex; gap:6px; flex-wrap:wrap; }
    .bs-sekme a { font-size:12.5px; font-weight:600; padding:6px 12px; border-radius:20px; text-decoration:none; color:var(--sy-text-muted,#7e7595); border:1px solid var(--sy-border,#ece6f7); }
    .bs-sekme a.aktif { background:var(--sy-primary-soft,#f0e8fb); color:var(--sy-text,#2a1f48); border-color:transparent; }
    .bs-tablo { width:100%; border-collapse:collapse; font-size:13px; color:var(--sy-text,#2a1f48); }
    .bs-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); white-space:nowrap; }
    .bs-tablo td { padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); vertical-align:top; }
    .bs-tablo tr:hover td { background:var(--sy-primary-soft,#f0e8fb); }
    .bs-ad { font-weight:600; } .bs-alt { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .bs-rozet { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; white-space:nowrap; }
    .bs-rozet.izin_kapali { background:#fde8e8; color:#b42318; }
    .bs-rozet.token_yok { background:#fff4e2; color:#a8670f; }
    .bs-rozet.ok { background:#e5f6ec; color:#1e7a46; }
    .bs-bos { text-align:center; padding:30px; color:var(--sy-text-muted,#7e7595); }
    .bs-not { font-size:12px; color:var(--sy-text-muted,#7e7595); margin-top:12px; line-height:1.6; }
    .bs-salonlar { display:flex; flex-wrap:wrap; gap:6px; }
    .bs-salonlar a { font-size:12px; padding:4px 10px; border-radius:16px; border:1px solid var(--sy-border,#ece6f7); text-decoration:none; color:var(--sy-text,#2a1f48); }
    .bs-salonlar a b { margin-left:4px; }
    .bs-salonlar a.aktif { background:var(--sy-primary-soft,#f0e8fb); border-color:transparent; }
    @media (max-width: 720px) { .bs-tablo .gizle { display:none; } }
</style>

@php
    $say = function ($k, $d) use ($ozet) { return (int) ($ozet[$k][$d] ?? 0); };
    $url = function (array $ek) use ($tip, $durum, $q, $salonId) {
        $p = array_merge(['tip' => $tip, 'durum' => $durum, 'q' => $q, 'salon_id' => $salonId], $ek);
        $p = array_filter($p, function ($v) { return $v !== null && $v !== ''; });
        return '/sistemyonetim/v2/bildirim-sorunlari' . ($p ? '?' . http_build_query($p) : '');
    };
    $sureMetni = function ($t) {
        if (!$t) return '';
        $sn = time() - strtotime($t);
        if ($sn < 3600) return max(1, (int) floor($sn / 60)) . ' dk';
        if ($sn < 86400) return (int) floor($sn / 3600) . ' saat';
        return (int) floor($sn / 86400) . ' gün';
    };
@endphp

<div class="sy-page-head">
    <div>
        <h2>Bildirim Sorunları</h2>
        <div class="subtitle">Telefonunda bildirim çalışmayan müşteri ve personeller. Uygulama artık kullanıcıya uyarı göstermiyor; durumu buraya yazıyor. Sorun düzelince kayıt listeden kendiliğinden düşer.</div>
    </div>
</div>

@if($semaHata)
    <div class="sy-card" style="margin-bottom:16px"><div class="sy-card-body" style="color:#b42318">Tablo alanları eklenemedi: {{ $semaHata }}</div></div>
@endif

<div class="bs-ozet">
    <div class="bs-kutu"><div class="e">Müşteri · izin kapalı</div><div class="d">{{ number_format($say('musteri','izin_kapali'), 0, ',', '.') }}</div><div class="a">Telefon ayarlarından kapatmış</div></div>
    <div class="bs-kutu"><div class="e">Müşteri · token yok</div><div class="d">{{ number_format($say('musteri','token_yok'), 0, ',', '.') }}</div><div class="a">FCM/APNs kaydı alınamıyor</div></div>
    <div class="bs-kutu"><div class="e">Personel · izin kapalı</div><div class="d">{{ number_format($say('personel','izin_kapali'), 0, ',', '.') }}</div><div class="a">Randevu bildirimini kaçırır</div></div>
    <div class="bs-kutu"><div class="e">Personel · token yok</div><div class="d">{{ number_format($say('personel','token_yok'), 0, ',', '.') }}</div><div class="a">FCM/APNs kaydı alınamıyor</div></div>
    <div class="bs-kutu"><div class="e">Çalışıyor</div><div class="d">{{ number_format($say('musteri','ok') + $say('personel','ok'), 0, ',', '.') }}</div><div class="a">Müşteri + personel, yeni sürümden bildiren</div></div>
</div>

@if(count($salonOzet))
<div class="sy-card" style="margin-bottom:16px">
    <div class="sy-card-body">
        <div class="bs-alt" style="margin:0 0 8px">En çok sorunlu şubeler</div>
        <div class="bs-salonlar">
            @if($salonId)<a href="{{ $url(['salon_id' => null]) }}">Tümü</a>@endif
            @foreach($salonOzet as $so)
                <a href="{{ $url(['salon_id' => $so->salon_id]) }}" class="{{ (int) $salonId === (int) $so->salon_id ? 'aktif' : '' }}">{{ $so->salon_adi }}<b>{{ $so->adet }}</b></a>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="sy-card">
    <div class="sy-card-body">
        <form method="GET" action="/sistemyonetim/v2/bildirim-sorunlari" class="sy-flex-row" style="gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;justify-content:space-between">
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <div class="bs-sekme">
                    @foreach(['sorunlu' => 'Sorunlu', 'izin_kapali' => 'İzin kapalı', 'token_yok' => 'Token yok', 'ok' => 'Çalışıyor'] as $k => $v)
                        <a href="{{ $url(['durum' => $k]) }}" class="{{ $durum === $k ? 'aktif' : '' }}">{{ $v }}</a>
                    @endforeach
                </div>
                <div class="bs-sekme">
                    @foreach(['hepsi' => 'Hepsi', 'musteri' => 'Müşteri', 'personel' => 'Personel'] as $k => $v)
                        <a href="{{ $url(['tip' => $k]) }}" class="{{ $tip === $k ? 'aktif' : '' }}">{{ $v }}</a>
                    @endforeach
                </div>
            </div>
            <div class="sy-flex-row" style="gap:8px">
                <input type="hidden" name="tip" value="{{ $tip }}">
                <input type="hidden" name="durum" value="{{ $durum }}">
                @if($salonId)<input type="hidden" name="salon_id" value="{{ $salonId }}">@endif
                <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="Ad, telefon, şube..." style="max-width:220px">
                <button type="submit" class="sy-btn sy-btn-soft sy-btn-sm"><span class="mdi mdi-magnify"></span> Ara</button>
            </div>
        </form>

        @foreach(['personel' => 'Personeller', 'musteri' => 'Müşteriler'] as $k => $baslik)
            @php $satirlar = $k === 'personel' ? $personeller : $musteriler; @endphp
            @if($tip === 'hepsi' || $tip === $k)
                <h4 style="margin:18px 0 8px;font-size:14px">{{ $baslik }} <span class="bs-alt">({{ count($satirlar) }}{{ count($satirlar) >= 300 ? '+' : '' }})</span></h4>
                @if(count($satirlar) === 0)
                    <div class="bs-bos">Kayıt yok.</div>
                @else
                <div style="overflow-x:auto">
                <table class="bs-tablo">
                    <thead><tr>
                        <th>Kişi</th><th>Şube</th><th>Durum</th><th>Ne zamandır</th><th class="gizle">Son bildirim</th><th class="gizle">Cihaz / hata</th>
                    </tr></thead>
                    <tbody>
                    @foreach($satirlar as $r)
                        <tr>
                            <td><div class="bs-ad">{{ $r->ad }}</div><div class="bs-alt">{{ $r->telefon }}</div></td>
                            <td><a href="/sistemyonetim/v2/salon/{{ $r->salon_id }}">{{ $r->salon_adi }}</a></td>
                            <td><span class="bs-rozet {{ $r->bildirim_durum }}">{{ $etiket[$r->bildirim_durum] ?? $r->bildirim_durum }}</span></td>
                            <td>{{ $r->bildirim_sorun_baslangic ? $sureMetni($r->bildirim_sorun_baslangic) : '—' }}
                                @if($r->bildirim_sorun_baslangic)<div class="bs-alt">{{ date('d.m.Y H:i', strtotime($r->bildirim_sorun_baslangic)) }}</div>@endif
                            </td>
                            <td class="gizle">{{ $r->bildirim_durum_tarihi ? date('d.m.Y H:i', strtotime($r->bildirim_durum_tarihi)) : '' }}</td>
                            <td class="gizle"><div>{{ $r->bildirim_cihaz }}</div>@if($r->bildirim_hata)<div class="bs-alt">{{ $r->bildirim_hata }}</div>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            @endif
        @endforeach

        <div class="bs-not">
            <b>İzin kapalı:</b> kullanıcı telefon ayarlarından uygulamanın bildirimlerini kapatmış; işletme kişiyi arayıp açtırmalı.
            <b>Token alınamıyor:</b> cihaz Firebase'e kayıt olamıyor (Google Play Hizmetleri yok, internet, iOS APNs anahtarı vb.); aynı markada çok sayıda varsa Firebase / APNs ayarına bakın.
            Listede yalnızca uygulamanın yeni sürümünü kullananlar görünür.
        </div>
    </div>
</div>
@endsection
