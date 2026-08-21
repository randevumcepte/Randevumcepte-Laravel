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
    .ai-baslik h1 { font-size:20px; font-weight:700; margin:0; color:#2a2340; }
    .ai-filtre { display:flex; gap:8px; }
    .ai-filtre a { padding:7px 14px; border-radius:20px; font-size:12.5px; font-weight:600; text-decoration:none;
        border:1px solid #e3daf3; color:#6b7280; background:#fff; }
    .ai-filtre a.act { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border-color:transparent; }
    .ai-kartlar { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; margin-bottom:18px; }
    .ai-kart { background:#fff; border:1px solid #ece7f6; border-radius:14px; padding:16px; box-shadow:0 2px 8px rgba(92,0,142,.05); }
    .ai-kart .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#8b7ba8; font-weight:700; }
    .ai-kart .d { font-size:23px; font-weight:800; margin-top:5px; color:#2a2340; }
    .ai-kart .alt { font-size:12px; color:#8b7ba8; margin-top:3px; }
    .ai-kart.iyi .d { color:#16a34a; } .ai-kart.kotu .d { color:#dc2626; }
    .ai-panel { background:#fff; border:1px solid #ece7f6; border-radius:14px; padding:16px 18px; margin-bottom:18px; box-shadow:0 2px 8px rgba(92,0,142,.05); }
    .ai-panel h2 { font-size:13px; text-transform:uppercase; letter-spacing:.4px; color:#8b7ba8; font-weight:700; margin:0 0 12px; }
    .ai-tablo { width:100%; border-collapse:collapse; font-size:13px; }
    .ai-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:#8b7ba8; font-weight:600; padding:9px 10px; border-bottom:2px solid #f0ebf8; }
    .ai-tablo td { padding:11px 10px; border-bottom:1px solid #f4f1fa; color:#3a2a5c; font-size:13.5px; }
    .ai-tablo td.sag, .ai-tablo th.sag { text-align:right; }
    .ai-tablo td.sag { font-weight:700; color:#2a2340; }
    .ai-rozet { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:7px; vertical-align:middle; }
    .ai-form { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .ai-form label { font-size:11px; color:#8b7ba8; font-weight:600; display:block; margin-bottom:4px; }
    .ai-form input { background:#f7f6fb; border:1px solid #e3daf3; border-radius:9px; padding:9px 12px; color:#2a2340; font-size:14px; width:160px; }
    .ai-form button { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border:none; border-radius:9px; padding:10px 18px; font-weight:700; cursor:pointer; }
    .ai-bar-bg { background:#f0ebf8; border-radius:6px; height:8px; overflow:hidden; }
    .ai-bar { height:8px; background:linear-gradient(90deg,#7B2FB8,#38bdf8); border-radius:6px; }
    .ai-uyari { background:#fef8e7; border:1px solid #f3d98a; color:#8a6100; border-radius:10px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
    .ai-ok { background:#e8f8ef; border:1px solid #a7e0c0; color:#15803d; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }
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
@if((float)$ayar['yuklenen_usd'] > 0 && $kalanUsd < (float)$ayar['esik_usd'])
    <div class="ai-uyari" style="background:#fdecec;border-color:#f3b4b4;color:#b91c1c">
        🔴 <b>AI kredin azaldı!</b> Kalan {{ $usd($kalanUsd) }} (eşik {{ $usd($ayar['esik_usd']) }}). Düşük-kredi WhatsApp alarmı otomatik gönderilir. Aşağıdan kredi ekleyin.
    </div>
@endif

{{-- Ozet kartlari --}}
<div class="ai-kartlar">
    @if((float)$ayar['yuklenen_usd'] <= 0)
    <div class="ai-kart">
        <div class="e">Kalan Kredi</div>
        <div class="d" style="font-size:16px;color:#b45309">Kredi girilmedi</div>
        <div class="alt">Aşağıdan yüklediğin krediyi gir ↓</div>
    </div>
    @else
    <div class="ai-kart {{ $kalanUsd < ((float)$ayar['yuklenen_usd']*0.15) ? 'kotu' : 'iyi' }}">
        <div class="e">Kalan Kredi</div>
        <div class="d">{{ $usd($kalanUsd) }}</div>
        <div class="alt">{{ $tl($kalanUsd) }} · yükledin {{ $usd($ayar['yuklenen_usd']) }}</div>
    </div>
    @endif
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
    <h2>Kredi Yükleme &amp; Alarm</h2>
    <div class="alt" style="font-size:12.5px;color:#94a3b8;margin-bottom:14px">
        Anthropic bakiyeyi API'den vermez; buraya <b>yüklediğin toplam krediyi</b> (USD) gir. Kalan = yüklenen − tüm zaman harcama.
        Kalan, <b>eşiğin</b> altına inince <b>otomatik WhatsApp + SMS alarmı</b> gider (günde 1 kez). Son güncelleme: {{ $ayar['guncelleme'] ?? '—' }}
    </div>

    {{-- Toplam + kur + esik --}}
    <form class="ai-form" method="POST" action="/sistemyonetim/v2/ai-kredi/kredi-yukle" style="margin-bottom:14px">
        {{ csrf_field() }}
        <div><label>Toplam yüklenen kredi (USD)</label><input type="number" step="0.01" name="yuklenen" value="{{ $ayar['yuklenen_usd'] }}"></div>
        <div><label>USD → TL kuru</label><input type="number" step="0.01" name="kur" value="{{ $ayar['kur'] }}"></div>
        <div><label>Alarm eşiği (USD)</label><input type="number" step="0.01" name="esik" value="{{ $ayar['esik_usd'] }}"></div>
        <button type="submit">Kaydet</button>
    </form>

    {{-- Hizli ekle + test --}}
    <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:flex-end; border-top:1px solid #ece7f6; padding-top:14px">
        <form class="ai-form" method="POST" action="/sistemyonetim/v2/ai-kredi/kredi-ekle">
            {{ csrf_field() }}
            <div><label>Yeni yükleme ekle (USD)</label><input type="number" step="0.01" name="eklenen" placeholder="ör. 20"></div>
            <button type="submit">+ Krediye Ekle</button>
        </form>
        <form method="POST" action="/sistemyonetim/v2/ai-kredi/test-alarm">
            {{ csrf_field() }}
            <button type="submit" style="background:#f0ebf8;color:#5C008E;border:1px solid #e3daf3;border-radius:9px;padding:10px 16px;font-weight:700;cursor:pointer">
                <span class="mdi mdi-whatsapp"></span> Test alarmı gönder
            </button>
        </form>
    </div>
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
