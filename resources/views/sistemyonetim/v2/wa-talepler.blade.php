@extends('sistemyonetim.v2.layout')

@section('content')
<style>
    .wt-ozet { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:18px; }
    .wt-kutu { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:14px 16px; box-shadow:var(--sy-shadow-sm); }
    .wt-kutu .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; }
    .wt-kutu .d { font-size:21px; font-weight:700; margin-top:4px; color:var(--sy-text,#2a1f48); }
    .wt-tablo { width:100%; border-collapse:collapse; font-size:13px; color:var(--sy-text,#2a1f48); }
    .wt-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); white-space:nowrap; }
    .wt-tablo td { padding:11px 10px; border-bottom:1px solid var(--sy-border,#ece6f7); vertical-align:top; }
    .wt-alt { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .wt-rozet { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; white-space:nowrap; }
    .wt-rozet.bekliyor { background:#fdecee; color:#b53a4a; } .wt-rozet.cevaplandi { background:#e6f5ee; color:#21835a; } .wt-rozet.cevapsiz { background:#d04d5e; color:#fff; }
    .wt-bos { text-align:center; padding:40px; color:var(--sy-text-muted,#7e7595); }
</style>

<div class="sy-page-head">
    <div>
        <h2>WhatsApp Asistan Talepleri</h2>
        <div class="subtitle">Asistanın cevaplayamayıp işletmeye sorduğu konular (fotoğraf fiyatı, yetkili isteği, şikâyet…). Asistan bu sırada müşteriyle ilgilenmeye devam eder; işletme dönmezse 15 dk'da hatırlatma, 45 dk'da asistan müşteriye nazikçe geri döner.</div>
    </div>
    <form method="GET" action="/sistemyonetim/v2/wa-talepler">
        <select name="gun" class="sy-select" onchange="this.form.submit()">
            @foreach([1 => 'Son 24 saat', 7 => 'Son 7 gün', 30 => 'Son 30 gün'] as $g => $ad)
                <option value="{{ $g }}" {{ $gun === $g ? 'selected' : '' }}>{{ $ad }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="wt-ozet">
    <div class="wt-kutu"><div class="e">Dönüş bekleyen</div><div class="d" style="color:#b53a4a">{{ $say['bekliyor'] }}</div></div>
    <div class="wt-kutu"><div class="e">İşletme cevapladı</div><div class="d" style="color:#21835a">{{ $say['cevaplandi'] }}</div></div>
    <div class="wt-kutu"><div class="e">İşletme dönmedi (24 saat)</div><div class="d">{{ $say['cevapsiz'] }}</div></div>
</div>

<div class="sy-card"><div class="sy-card-body" style="overflow-x:auto">
    @if(!count($liste))
        <div class="wt-bos">Bu dönemde işletmeye sorulan konu yok.</div>
    @else
    <table class="wt-tablo">
        <thead><tr><th>Durum</th><th>Salon</th><th>Müşteri</th><th>Konu</th><th>Bildirim gidenler</th><th>Açıldı</th><th>Süre</th><th>Sonuç</th></tr></thead>
        <tbody>
        @foreach($liste as $t)
            <tr>
                <td><span class="wt-rozet {{ $t['durum'] }}">{{ ['bekliyor' => 'Bekliyor', 'cevaplandi' => 'Cevaplandı', 'cevapsiz' => 'Dönülmedi'][$t['durum']] ?? $t['durum'] }}</span></td>
                <td><b>{{ $t['salon'] }}</b><div class="wt-alt">#{{ $t['isletme_id'] }}</div></td>
                <td>{{ $t['ad'] && $t['ad'] !== '.' ? $t['ad'] : 'Müşteri' }}<div class="wt-alt">{{ $t['telefon'] ?: 'gizli numara' }}</div></td>
                <td>{{ $t['konu_ad'] }}<div class="wt-alt">{{ \Illuminate\Support\Str::limit((string) $t['ozet'], 110) }}</div></td>
                <td>{{ $t['bildirilen'] ? implode(', ', $t['bildirilen']) : '— (yalnız panel bildirimi)' }}</td>
                <td style="white-space:nowrap">{{ date('d.m H:i', strtotime($t['acildi'])) }}</td>
                <td style="white-space:nowrap">{{ $t['sure'] }}</td>
                <td>
                    @if($t['durum'] === 'cevaplandi'){{ $t['cevaplayan'] }}
                    @else
                        {{ $t['hatirlatma_at'] ? 'Hatırlatma gitti' : 'Hatırlatma bekliyor' }}@if($t['asistan_dondu_at']) · asistan müşteriye döndü @endif
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div></div>
@endsection
