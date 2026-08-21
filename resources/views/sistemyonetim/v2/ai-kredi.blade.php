@extends('sistemyonetim.v2.layout')

@section('content')
@php
    $kur = $kur ?: 40;
    $usd = function ($v) { return '$' . number_format((float)$v, ((float)$v < 1 ? 4 : 2), ',', '.'); };
    $tl  = function ($v) use ($kur) { return number_format((float)$v * $kur, 2, ',', '.') . ' ₺'; };
    $turAd = ['niyet'=>'Rapor niyeti','sohbet'=>'Sohbet','karne'=>'Personel karnesi','yorum'=>'Personel yorumu','kampanya'=>'Kampanya','ai'=>'Diğer'];
    $turRenk = ['niyet'=>'#38bdf8','sohbet'=>'#a78bfa','karne'=>'#f472b6','yorum'=>'#fbbf24','kampanya'=>'#34d399','ai'=>'#94a3b8'];
    $gunlukMax = 0; foreach(($gunluk ?? []) as $g){ $gunlukMax = max($gunlukMax, (float)$g->maliyet); }
@endphp

<style>
    .ai-baslik { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
    .ai-baslik h1 { font-size:20px; font-weight:700; margin:0; }
    .ai-filtre { display:flex; gap:8px; }
    .ai-filtre a { padding:7px 14px; border-radius:20px; font-size:12.5px; font-weight:600; text-decoration:none;
        border:1px solid var(--sy-border,#2a3550); color:var(--sy-muted,#94a3b8); }
    .ai-filtre a.act { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border-color:transparent; }
    .ai-kartlar { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; margin-bottom:18px; }
    .ai-kart { background:var(--sy-card-bg,#0f172a); border:1px solid var(--sy-border,#2a3550); border-radius:14px; padding:16px; }
    .ai-kart .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-muted,#94a3b8); font-weight:600; }
    .ai-kart .d { font-size:23px; font-weight:800; margin-top:5px; }
    .ai-kart .alt { font-size:12px; color:var(--sy-muted,#94a3b8); margin-top:3px; }
    .ai-kart.iyi .d { color:#34d399; } .ai-kart.kotu .d { color:#f87171; }
    .ai-panel { background:var(--sy-card-bg,#0f172a); border:1px solid var(--sy-border,#2a3550); border-radius:14px; padding:16px 18px; margin-bottom:18px; }
    .ai-panel h2 { font-size:13px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-muted,#94a3b8); font-weight:700; margin:0 0 12px; }
    .ai-tablo { width:100%; border-collapse:collapse; font-size:13px; }
    .ai-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-muted,#94a3b8); font-weight:600; padding:9px 10px; border-bottom:1px solid var(--sy-border,#2a3550); }
    .ai-tablo td { padding:11px 10px; border-bottom:1px solid var(--sy-border,#1e293b); }
    .ai-tablo td.sag, .ai-tablo th.sag { text-align:right; }
    .ai-rozet { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:7px; vertical-align:middle; }
    .ai-form { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .ai-form label { font-size:11px; color:var(--sy-muted,#94a3b8); font-weight:600; display:block; margin-bottom:4px; }
    .ai-form input { background:#0b1220; border:1px solid var(--sy-border,#2a3550); border-radius:9px; padding:9px 12px; color:#e5e7eb; font-size:14px; width:160px; }
    .ai-form button { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border:none; border-radius:9px; padding:10px 18px; font-weight:700; cursor:pointer; }
    .ai-bar-bg { background:#0b1220; border-radius:6px; height:8px; overflow:hidden; }
    .ai-bar { height:8px; background:linear-gradient(90deg,#7B2FB8,#38bdf8); border-radius:6px; }
    .ai-uyari { background:rgba(251,191,36,.1); border:1px solid rgba(251,191,36,.3); color:#fbbf24; border-radius:10px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
    .ai-ok { background:rgba(52,211,153,.12); border:1px solid rgba(52,211,153,.35); color:#34d399; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }
</style>

<div class="ai-baslik">
    <h1>🤖 AI Kredi &amp; Kullanım</h1>
    <div class="ai-filtre">
        @foreach([1=>'Bugün',7=>'7 Gün',30=>'30 Gün',0=>'Tümü'] as $g=>$ad)
            <a href="?gun={{ $g }}" class="{{ (int)$gun===$g ? 'act':'' }}">{{ $ad }}</a>
        @endforeach
    </div>
</div>

@if(session('ok'))<div class="ai-ok">✓ {{ session('ok') }}</div>@endif
@if(!empty($tabloYok))
    <div class="ai-uyari">⚠ <b>ai_kullanim</b> tablosu henüz yok. Sunucuda <code>php artisan migrate</code> çalıştırın; loglama ondan sonra başlar.</div>
@endif

{{-- Ozet kartlari --}}
<div class="ai-kartlar">
    <div class="ai-kart {{ $kalanUsd < 0 ? 'kotu' : 'iyi' }}">
        <div class="e">Kalan Kredi</div>
        <div class="d">{{ $usd($kalanUsd) }}</div>
        <div class="alt">{{ $tl($kalanUsd) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">Yüklenen Kredi</div>
        <div class="d">{{ $usd($ayar['yuklenen_usd']) }}</div>
        <div class="alt">Tüm zaman harcama: {{ $usd($tumHarcamaUsd) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">Harcama (seçili dönem)</div>
        <div class="d">{{ $usd($ozet['maliyet_usd']) }}</div>
        <div class="alt">{{ $tl($ozet['maliyet_usd']) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">Bugün</div>
        <div class="d">{{ $usd($ozet['bugun_usd']) }}</div>
        <div class="alt">{{ $tl($ozet['bugun_usd']) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">Toplam Çağrı</div>
        <div class="d">{{ number_format($ozet['cagri'],0,',','.') }}</div>
        <div class="alt">{{ number_format($ozet['gercek'],0,',','.') }} AI · {{ number_format($ozet['cache'],0,',','.') }} cache</div>
    </div>
    <div class="ai-kart iyi">
        <div class="e">Cache Tasarrufu</div>
        <div class="d">{{ $usd($ozet['cache_tasarruf_usd']) }}</div>
        <div class="alt">{{ number_format($ozet['cache'],0,',','.') }} çağrı bedava geldi</div>
    </div>
    <div class="ai-kart">
        <div class="e">Ort. Maliyet / Çağrı</div>
        <div class="d">{{ $usd($ozet['ort_usd']) }}</div>
        <div class="alt">≈ {{ $tl($ozet['ort_usd']) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">Token (dönem)</div>
        <div class="d" style="font-size:18px">{{ number_format($ozet['girdi'],0,',','.') }} <span style="color:#94a3b8;font-size:13px">girdi</span></div>
        <div class="alt">{{ number_format($ozet['cikti'],0,',','.') }} çıktı</div>
    </div>
</div>

{{-- Kredi yukle --}}
<div class="ai-panel">
    <h2>Kredi Yükleme</h2>
    <div class="alt" style="font-size:12.5px;color:#94a3b8;margin-bottom:12px">
        Anthropic bakiyeyi API'den vermez; buraya <b>yüklediğin toplam krediyi</b> (USD) gir. Kalan = yüklenen − tüm zaman harcama.
        Son güncelleme: {{ $ayar['guncelleme'] ?? '—' }}
    </div>
    <form class="ai-form" method="POST" action="/sistemyonetim/v2/ai-kredi/kredi-yukle">
        {{ csrf_field() }}
        <div><label>Toplam yüklenen kredi (USD)</label><input type="number" step="0.01" name="yuklenen" value="{{ $ayar['yuklenen_usd'] }}"></div>
        <div><label>USD → TL kuru</label><input type="number" step="0.01" name="kur" value="{{ $ayar['kur'] }}"></div>
        <button type="submit">Kaydet</button>
    </form>
</div>

{{-- Tur dagilimi --}}
<div class="ai-panel">
    <h2>Tür Bazında Dağılım</h2>
    <table class="ai-tablo">
        <thead><tr><th>Tür</th><th class="sag">Çağrı</th><th class="sag">Cache</th><th class="sag">Token</th><th class="sag">Maliyet ($)</th><th class="sag">Maliyet (₺)</th></tr></thead>
        <tbody>
        @forelse($turler as $t)
            <tr>
                <td><span class="ai-rozet" style="background:{{ $turRenk[$t->tur] ?? '#94a3b8' }}"></span>{{ $turAd[$t->tur] ?? $t->tur }}</td>
                <td class="sag">{{ number_format($t->cagri,0,',','.') }}</td>
                <td class="sag">{{ number_format($t->cache,0,',','.') }}</td>
                <td class="sag">{{ number_format($t->girdi + $t->cikti,0,',','.') }}</td>
                <td class="sag">{{ $usd($t->maliyet) }}</td>
                <td class="sag">{{ $tl($t->maliyet) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde AI kullanımı yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Salon bazinda --}}
<div class="ai-panel">
    <h2>Salon Bazında Kullanım</h2>
    <table class="ai-tablo">
        <thead><tr><th>Salon</th><th class="sag">Çağrı</th><th class="sag">Cache</th><th class="sag">Token</th><th class="sag">Maliyet ($)</th><th class="sag">Maliyet (₺)</th><th class="sag">Son</th></tr></thead>
        <tbody>
        @forelse($salonlar as $s)
            <tr>
                <td>
                    @if($s->salon_id)<a href="/sistemyonetim/v2/salon/{{ $s->salon_id }}" style="color:#c4b5fd;text-decoration:none">{{ $s->salon_adi }}</a>
                    @else {{ $s->salon_adi }} @endif
                </td>
                <td class="sag">{{ number_format($s->cagri,0,',','.') }}</td>
                <td class="sag">{{ number_format($s->cache,0,',','.') }}</td>
                <td class="sag">{{ number_format($s->token,0,',','.') }}</td>
                <td class="sag">{{ $usd($s->maliyet) }}</td>
                <td class="sag">{{ $tl($s->maliyet) }}</td>
                <td class="sag" style="color:#94a3b8;font-size:12px">{{ $s->son ? \Carbon\Carbon::parse($s->son)->format('d.m H:i') : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde salon kullanımı yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Gunluk akis --}}
<div class="ai-panel">
    <h2>Günlük Akış (son 30 gün)</h2>
    <table class="ai-tablo">
        <thead><tr><th>Gün</th><th class="sag">Çağrı</th><th class="sag">Maliyet ($)</th><th style="width:38%">Akış</th></tr></thead>
        <tbody>
        @forelse($gunluk as $g)
            <tr>
                <td>{{ \Carbon\Carbon::parse($g->gun)->format('d.m.Y') }}</td>
                <td class="sag">{{ number_format($g->cagri,0,',','.') }}</td>
                <td class="sag">{{ $usd($g->maliyet) }}</td>
                <td><div class="ai-bar-bg"><div class="ai-bar" style="width:{{ $gunlukMax>0 ? round((float)$g->maliyet/$gunlukMax*100) : 0 }}%"></div></div></td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Veri yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
