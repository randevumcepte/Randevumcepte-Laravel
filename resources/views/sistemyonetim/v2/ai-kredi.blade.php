@extends('sistemyonetim.v2.layout')

@section('content')
@php
    $kur = $kur ?: 40;
    $usd = function ($v) { return '$' . number_format((float)$v, ((float)$v < 1 ? 4 : 2), ',', '.'); };
    $tl  = function ($v) use ($kur) { return number_format((float)$v * $kur, 2, ',', '.') . ' ₺'; };
    // Tek islem maliyeti: 1 ₺ altindaysa kurus olarak (daha okunur)
    $birim = function ($v) use ($kur) {
        $t = (float)$v * $kur;
        if ($t <= 0) return '—';
        return $t < 1 ? number_format($t * 100, ($t * 100 < 1 ? 2 : 1), ',', '.') . ' kuruş' : number_format($t, 2, ',', '.') . ' ₺';
    };
    $sayi = function ($v) { return number_format((float)$v, 0, ',', '.'); };
    // Token: milyon/bin kisaltmasi
    $tk = function ($v) {
        $v = (float)$v;
        if ($v >= 1000000) return number_format($v / 1000000, 1, ',', '.') . ' milyon';
        if ($v >= 1000) return number_format($v / 1000, 1, ',', '.') . ' bin';
        return number_format($v, 0, ',', '.');
    };

    // Kanal = ai_kullanim.tur; ekranda teknik kod degil ne is yaptigi gorunur
    $kanallar = [
        'wa_asistan' => ['ad' => 'WhatsApp Asistanı',       'ikon' => 'mdi-whatsapp',     'renk' => '#22c55e', 'koyu' => '#15803d', 'birim' => 'cevap',          'ne' => 'Müşterilerin WhatsApp mesajlarına AI\'nin yazdığı cevaplar'],
        'santral'    => ['ad' => 'Telefon Asistanı',        'ikon' => 'mdi-phone',        'renk' => '#3b82f6', 'koyu' => '#1d4ed8', 'birim' => 'konuşma sırası', 'ne' => 'Santralde telefonu AI açar; müşterinin her konuşma sırası 1 işlem'],
        'wa_gorsel'  => ['ad' => 'Fotoğraftan Fiyat',       'ikon' => 'mdi-camera',       'renk' => '#f59e0b', 'koyu' => '#b45309', 'birim' => 'fotoğraf',       'ne' => 'Müşterinin WhatsApp\'tan attığı fotoğrafı inceleyip fiyat çıkarma'],
        'niyet'      => ['ad' => 'Patron AI · Rapor sorusu', 'ikon' => 'mdi-chart-bar',    'renk' => '#38bdf8', 'birim' => 'soru',           'ne' => 'İşletme sahibinin "bu ay ciro ne?" gibi soruları'],
        'sohbet'     => ['ad' => 'Patron AI · Sohbet',      'ikon' => 'mdi-robot',        'renk' => '#a78bfa', 'birim' => 'mesaj',          'ne' => 'İşletme sahibinin asistanla serbest sohbeti'],
        'karne'      => ['ad' => 'Personel Karnesi',        'ikon' => 'mdi-card-account-details', 'renk' => '#f472b6', 'birim' => 'karne', 'ne' => 'Personel performans karnesi yorumu'],
        'yorum'      => ['ad' => 'Personel Yorumu',         'ikon' => 'mdi-comment-text', 'renk' => '#fbbf24', 'birim' => 'yorum',          'ne' => 'Personel hakkında AI yorumu'],
        'kampanya'   => ['ad' => 'Kampanya Metni',          'ikon' => 'mdi-bullhorn',     'renk' => '#34d399', 'birim' => 'metin',          'ne' => 'AI ile yazılan kampanya / duyuru metni'],
        'pdf_egitim' => ['ad' => 'PDF Eğitim',              'ikon' => 'mdi-file-pdf-box', 'renk' => '#ef4444', 'birim' => 'belge',          'ne' => 'Yüklenen PDF\'ten asistana bilgi öğretme'],
    ];
    $kanal = function ($tur) use ($kanallar) {
        return $kanallar[$tur] ?? ['ad' => ucfirst(str_replace('_', ' ', $tur)), 'ikon' => 'mdi-auto-fix', 'renk' => '#94a3b8', 'birim' => 'işlem', 'ne' => 'Diğer AI işlemi'];
    };
    $rgba = function ($hex, $a) {
        $h = ltrim($hex, '#');
        return 'rgba(' . hexdec(substr($h, 0, 2)) . ',' . hexdec(substr($h, 2, 2)) . ',' . hexdec(substr($h, 4, 2)) . ',' . $a . ')';
    };
    $modelAd = function ($m) {
        $m = strtolower((string)$m);
        if (strpos($m, 'haiku') !== false) return 'Ekonomik model (Haiku)';
        if (strpos($m, 'sonnet') !== false) return 'Güçlü model (Sonnet)';
        if (strpos($m, 'opus') !== false) return 'En güçlü model (Opus)';
        return $m !== '' ? $m : '—';
    };

    $donemUsd = (float)$ozet['maliyet_usd'];
    $gunAd = [1 => 'bugün', 7 => 'son 7 gün', 30 => 'son 30 gün', 0 => 'tüm zamanlar'][(int)$gun] ?? 'seçili dönem';
    $gunlukMax = 0; foreach(($gunluk ?? []) as $g){ $gunlukMax = max($gunlukMax, (float)$g->maliyet); }
    $enPahali = count($turler) ? $turler[0] : null;
    $eskiVar = false; foreach ($turler as $t) { if ((int)$t->eski > 0) { $eskiVar = true; break; } }
@endphp

<style>
    .ai-baslik { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
    .ai-baslik h1 { font-size:20px; font-weight:700; margin:0; color:#2a2340; }
    .ai-filtre { display:flex; gap:8px; flex-wrap:wrap; }
    .ai-filtre a { padding:7px 14px; border-radius:20px; font-size:12.5px; font-weight:600; text-decoration:none;
        border:1px solid #e3daf3; color:#6b7280; background:#fff; }
    .ai-filtre a.act { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border-color:transparent; }

    .ai-ozet { background:linear-gradient(135deg,#5C008E,#7B2FB8 60%,#9D5DC9); color:#fff; border-radius:16px; padding:18px 20px; margin-bottom:18px;
        font-size:15px; line-height:1.6; box-shadow:0 6px 20px rgba(92,0,142,.18); }
    .ai-ozet b { font-weight:700; }
    .ai-ozet .k { font-size:12px; opacity:.8; margin-top:6px; }

    .ai-kartlar { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:18px; }
    .ai-kart { position:relative; overflow:hidden; border-radius:16px; padding:16px 18px; color:#fff; box-shadow:0 6px 18px rgba(30,10,60,.12); }
    .ai-kart::after { content:""; position:absolute; right:-28px; top:-28px; width:110px; height:110px; border-radius:50%; background:rgba(255,255,255,.14); }
    .ai-kart .mdi.buyuk { position:absolute; right:14px; bottom:10px; font-size:38px; opacity:.25; }
    .ai-kart .e { font-size:12.5px; font-weight:600; opacity:.9; }
    .ai-kart .d { font-size:26px; font-weight:700; margin-top:5px; }
    .ai-kart .alt { font-size:12.5px; margin-top:4px; line-height:1.45; opacity:.92; position:relative; z-index:1; }
    .ai-kart.iyi     { background:linear-gradient(135deg,#15803d,#22c55e); }
    .ai-kart.kotu    { background:linear-gradient(135deg,#b91c1c,#f87171); }
    .ai-kart.bos     { background:linear-gradient(135deg,#b45309,#f59e0b); }
    .ai-kart.mor     { background:linear-gradient(135deg,#5C008E,#9D5DC9); }
    .ai-kart.mavi    { background:linear-gradient(135deg,#1d4ed8,#38bdf8); }
    .ai-kart.turuncu { background:linear-gradient(135deg,#c2410c,#fb923c); }

    .ai-panel { background:linear-gradient(180deg,#faf7ff,#fff 120px); border:1px solid #e9e0f8; border-radius:16px; padding:18px; margin-bottom:18px; box-shadow:0 4px 14px rgba(92,0,142,.07); }
    .ai-panel h2 { font-size:16px; color:#2a2340; font-weight:700; margin:0 0 4px; display:flex; align-items:center; gap:10px; }
    .rozet { width:30px; height:30px; flex:0 0 30px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:17px; }
    .ai-panel .acik { font-size:13px; color:#8b7ba8; margin:0 0 14px; line-height:1.5; }

    .ai-kanallar { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; }
    .ai-kanal { border:1px solid var(--cizgi); border-top:4px solid var(--renk); border-radius:14px; padding:16px; background:linear-gradient(160deg,var(--zemin),#fff 75%); box-shadow:0 4px 14px var(--golge);
        display:flex; flex-direction:column; gap:10px; }
    .ai-kanal .ust { display:flex; align-items:center; gap:12px; }
    .ai-ikon { width:42px; height:42px; flex:0 0 42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:21px; color:#fff; }
    .ai-kanal .ad { font-size:15px; font-weight:700; color:#2a2340; }
    .ai-kanal .ne { font-size:12px; color:#8b7ba8; margin-top:2px; line-height:1.4; }
    .ai-kanal .tutar .tl { font-size:26px; font-weight:700; color:var(--koyu); line-height:1.1; }
    .ai-kanal .tutar .us { font-size:12px; color:#94a3b8; margin-left:6px; }
    .ai-kanal .bilgi { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .ai-kanal .bilgi div { background:rgba(255,255,255,.85); border:1px solid var(--cizgi); border-radius:9px; padding:7px 10px; font-size:11.5px; color:#6b5b8a; }
    .ai-kanal .bilgi div b { display:block; font-size:14px; color:#2a2340; margin-top:1px; }
    .ai-kanal .model { font-size:11.5px; font-weight:600; color:#fff; background:var(--renk); border-radius:20px; padding:3px 10px; align-self:flex-start; }
    .ai-pay { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .ai-pay .bg { flex:1; background:#f0ebf8; border-radius:6px; height:8px; overflow:hidden; max-width:420px; }
    .ai-pay .bar { height:8px; border-radius:6px; }
    .ai-pay .yuzde { font-size:12px; font-weight:700; color:#6b5b8a; min-width:38px; }
    .ai-cip { display:inline-block; font-size:11.5px; padding:3px 9px; border-radius:20px; background:#f5f2fb; color:#5b4a7a; margin:3px 4px 0 0; }
    .ai-cip.yesil { background:#e8f8ef; color:#15803d; }

    .ai-tablo { width:100%; border-collapse:collapse; font-size:13px; }
    .ai-tablo th { text-align:left; font-size:11px; color:#5C008E; font-weight:600; padding:10px; background:#f3ecfc; }
    .ai-tablo th:first-child { border-radius:9px 0 0 9px; } .ai-tablo th:last-child { border-radius:0 9px 9px 0; }
    .ai-tablo tbody tr:hover td { background:#faf6ff; }
    .ai-tablo td { padding:11px 10px; border-bottom:1px solid #f4f1fa; color:#3a2a5c; font-size:13.5px; vertical-align:top; }
    .ai-tablo td.sag, .ai-tablo th.sag { text-align:right; }
    .ai-tablo td.sag { font-weight:700; color:#2a2340; white-space:nowrap; }
    .ai-tablo-sar { overflow-x:auto; }

    .ai-form { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .ai-form label { font-size:11.5px; color:#8b7ba8; font-weight:600; display:block; margin-bottom:4px; }
    .ai-form input { background:#f7f6fb; border:1px solid #e3daf3; border-radius:9px; padding:9px 12px; color:#2a2340; font-size:14px; width:160px; }
    .ai-form button { background:linear-gradient(135deg,#5C008E,#7B2FB8); color:#fff; border:none; border-radius:9px; padding:10px 18px; font-weight:700; cursor:pointer; }
    .ai-bar-bg { background:#f0ebf8; border-radius:6px; height:8px; overflow:hidden; }
    .ai-bar { height:8px; background:linear-gradient(90deg,#7B2FB8,#38bdf8); border-radius:6px; }
    .ai-uyari { background:#fef8e7; border:1px solid #f3d98a; color:#8a6100; border-radius:10px; padding:12px 14px; font-size:13px; margin-bottom:16px; }
    .ai-ok { background:#e8f8ef; border:1px solid #a7e0c0; color:#15803d; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }

    details.ai-detay > summary { cursor:pointer; font-size:16px; font-weight:700; color:#2a2340; list-style:none; display:flex; align-items:center; gap:8px; }
    details.ai-detay > summary::-webkit-details-marker { display:none; }
    details.ai-detay > summary .ok { transition:transform .2s; color:#8b7ba8; }
    details.ai-detay[open] > summary .ok { transform:rotate(90deg); }
    details.ai-detay > summary .not { font-size:12.5px; font-weight:400; color:#8b7ba8; }
    .ai-sozluk { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:10px; margin:14px 0; }
    .ai-sozluk div { background:#f9f7fd; border-radius:10px; padding:10px 12px; font-size:12.5px; color:#4b3d6b; line-height:1.5; }
    .ai-sozluk b { color:#2a2340; display:block; margin-bottom:2px; }

    @media (max-width:1100px) { .ai-kanallar { grid-template-columns:repeat(2, minmax(0,1fr)); } }
    @media (max-width:640px)  { .ai-kanallar { grid-template-columns:1fr; } }
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
        🔴 <b>AI kredin azaldı!</b> Kalan {{ $tl($kalanUsd) }} ({{ $usd($kalanUsd) }}). Düşük kredi alarmı WhatsApp'tan otomatik gider. En alttan kredi ekleyin.
    </div>
@endif

{{-- Tek cumlelik ozet: sayfayi okumadan durumu anlat --}}
@if($donemUsd > 0)
<div class="ai-ozet">
    <b>{{ ucfirst($gunAd) }}</b> AI için toplam <b>{{ $tl($donemUsd) }}</b> harcandı ({{ $sayi($ozet['cagri']) }} işlem).
    @if($enPahali)
        Paranın en çok gittiği yer <b>{{ $kanal($enPahali->tur)['ad'] }}</b>:
        <b>{{ $tl($enPahali->maliyet) }}</b> (%{{ round((float)$enPahali->maliyet / $donemUsd * 100) }}).
    @endif
    @if($kalanGun !== null)
        <br>Kalan kredi <b>{{ $tl($kalanUsd) }}</b>. Son günlerdeki hızla (günde ~{{ $tl($gunlukOrtUsd) }}) yaklaşık <b>{{ $sayi($kalanGun) }} gün</b> yeter.
    @endif
    <div class="k">Tüm tutarlar 1 $ = {{ number_format($kur, 2, ',', '.') }} ₺ kuruyla çevrildi. Kur en alttaki ayardan değişir.</div>
</div>
@endif

{{-- Ozet kartlari --}}
<div class="ai-kartlar">
    @if((float)$ayar['yuklenen_usd'] <= 0)
    <div class="ai-kart bos"><span class="mdi mdi-wallet buyuk"></span>
        <div class="e">Kalan kredi</div>
        <div class="d" style="font-size:18px">Kredi girilmedi</div>
        <div class="alt">En alttan yüklediğin krediyi gir ↓</div>
    </div>
    @else
    <div class="ai-kart {{ $kalanUsd < ((float)$ayar['yuklenen_usd']*0.15) ? 'kotu' : 'iyi' }}"><span class="mdi mdi-wallet buyuk"></span>
        <div class="e">Kalan kredi</div>
        <div class="d">{{ $tl($kalanUsd) }}</div>
        <div class="alt">{{ $usd($kalanUsd) }} · toplam yüklenen {{ $usd($ayar['yuklenen_usd']) }}
            @if($kalanGun !== null)<br>Bu hızla ~<b>{{ $sayi($kalanGun) }} gün</b> yeter @endif</div>
    </div>
    @endif
    <div class="ai-kart mor"><span class="mdi mdi-cash-multiple buyuk"></span>
        <div class="e">Harcama ({{ $gunAd }})</div>
        <div class="d">{{ $tl($donemUsd) }}</div>
        <div class="alt">{{ $usd($donemUsd) }}</div>
    </div>
    <div class="ai-kart mavi"><span class="mdi mdi-calendar-today buyuk"></span>
        <div class="e">Bugün harcanan</div>
        <div class="d">{{ $tl($ozet['bugun_usd']) }}</div>
        <div class="alt">Günlük ortalama ~{{ $tl($gunlukOrtUsd) }}</div>
    </div>
    <div class="ai-kart turuncu"><span class="mdi mdi-lightning-bolt buyuk"></span>
        <div class="e">AI işlemi ({{ $gunAd }})</div>
        <div class="d">{{ $sayi($ozet['cagri']) }}</div>
        <div class="alt">İşlem başına ortalama {{ $birim($ozet['gercek'] > 0 ? $donemUsd / $ozet['gercek'] : 0) }}
            @if($ozet['cache'] > 0)<br><span style="font-weight:700">{{ $sayi($ozet['cache']) }} hazır cevap bedava geldi ({{ $tl($ozet['cache_tasarruf_usd']) }} tasarruf)</span>@endif</div>
    </div>
</div>

{{-- Para nereye gidiyor --}}
<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#5C008E,#9D5DC9)"><span class="mdi mdi-chart-donut"></span></span>Para nereye gidiyor?</h2>
    <p class="acik">Her satır AI'nin yaptığı bir iş. Çubuk, toplam harcamadaki payı gösterir. "1 işlem" o işin tek seferlik maliyetidir.</p>
    <div class="ai-kanallar">
    @forelse($turler as $t)
        @php $k = $kanal($t->tur); $pay = $donemUsd > 0 ? (float)$t->maliyet / $donemUsd * 100 : 0; $aiAdet = (int)$t->cagri - (int)$t->cache; @endphp
        <div class="ai-kanal" style="--renk:{{ $k['renk'] }};--koyu:{{ $k['koyu'] ?? $k['renk'] }};--zemin:{{ $rgba($k['renk'], .16) }};--cizgi:{{ $rgba($k['renk'], .28) }};--golge:{{ $rgba($k['renk'], .14) }}">
            <div class="ust">
                <div class="ai-ikon" style="background:{{ $k['renk'] }}"><span class="mdi {{ $k['ikon'] }}"></span></div>
                <div>
                    <div class="ad">{{ $k['ad'] }}</div>
                    <div class="ne">{{ $k['ne'] }}</div>
                </div>
            </div>
            <div class="tutar"><span class="tl">{{ $tl($t->maliyet) }}</span><span class="us">{{ $usd($t->maliyet) }}</span></div>
            <div class="bilgi">
                <div>Adet<b>{{ $sayi($t->cagri) }} {{ $k['birim'] }}</b></div>
                <div>1 işlem ≈<b>{{ $birim($aiAdet > 0 ? (float)$t->maliyet / $aiAdet : 0) }}</b></div>
            </div>
            @if($t->model)<div class="model">{{ $modelAd($t->model) }}</div>@endif
            @if((int)$t->cache > 0)<span class="ai-cip yesil" style="align-self:flex-start">{{ $sayi($t->cache) }} hazır cevap · bedava</span>@endif
            <div class="ai-pay" style="margin-top:auto">
                <div class="bg"><div class="bar" style="width:{{ round($pay) }}%;background:{{ $k['renk'] }}"></div></div>
                <span class="yuzde">%{{ $pay < 1 && $pay > 0 ? '<1' : round($pay) }}</span>
            </div>
        </div>
    @empty
        <p class="acik" style="text-align:center;padding:10px 0;grid-column:1/-1">Bu dönemde AI kullanımı yok.</p>
    @endforelse
    </div>
</div>

{{-- Salon bazinda --}}
<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#db2777,#f472b6)"><span class="mdi mdi-store"></span></span>Hangi işletme ne kadar harcıyor?</h2>
    <p class="acik">Her işletmenin harcaması ve bu paranın hangi işe gittiği.</p>
    <div class="ai-tablo-sar">
    <table class="ai-tablo">
        <thead><tr><th>İşletme</th><th>Neye harcadı</th><th class="sag">İşlem</th><th class="sag">Tutar</th><th class="sag">Son kullanım</th></tr></thead>
        <tbody>
        @forelse($salonlar as $s)
            @php $pay = $donemUsd > 0 ? (float)$s->maliyet / $donemUsd * 100 : 0; @endphp
            <tr>
                <td style="min-width:150px">
                    @if($s->salon_id)<a href="/sistemyonetim/v2/salon/{{ $s->salon_id }}" style="color:#7B2FB8;text-decoration:none;font-weight:700">{{ $s->salon_adi }}</a>
                    @else <b>{{ $s->salon_adi }}</b> @endif
                    <div class="ai-pay" style="margin-top:6px">
                        <div class="bg"><div class="bar" style="width:{{ round($pay) }}%;background:linear-gradient(90deg,#db2777,#7B2FB8)"></div></div>
                        <span class="yuzde">%{{ $pay < 1 && $pay > 0 ? '<1' : round($pay) }}</span>
                    </div>
                </td>
                <td>
                    @foreach(($salonTur[(int)$s->salon_id] ?? []) as $st)
                        <span class="ai-cip" style="background:{{ $rgba($kanal($st->tur)['renk'], .13) }};border:1px solid {{ $rgba($kanal($st->tur)['renk'], .3) }}"><span class="mdi {{ $kanal($st->tur)['ikon'] }}" style="color:{{ $kanal($st->tur)['renk'] }}"></span> {{ $kanal($st->tur)['ad'] }} · <b>{{ $tl($st->maliyet) }}</b></span>
                    @endforeach
                </td>
                <td class="sag">{{ $sayi($s->cagri) }}</td>
                <td class="sag">{{ $tl($s->maliyet) }}<div style="font-size:11.5px;color:#94a3b8;font-weight:400">{{ $usd($s->maliyet) }}</div></td>
                <td class="sag" style="color:#94a3b8;font-size:12px;font-weight:400">{{ $s->son ? \Carbon\Carbon::parse($s->son)->format('d.m H:i') : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde işletme kullanımı yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Gunluk akis --}}
<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#1d4ed8,#38bdf8)"><span class="mdi mdi-calendar-month"></span></span>Gün gün harcama</h2>
    <p class="acik">Son 30 günde her gün ne kadar harcandı.</p>
    <div class="ai-tablo-sar">
    <table class="ai-tablo">
        <thead><tr><th>Gün</th><th class="sag">İşlem</th><th class="sag">Tutar</th><th style="width:40%"></th></tr></thead>
        <tbody>
        @forelse($gunluk as $g)
            <tr>
                <td>{{ \Carbon\Carbon::parse($g->gun)->format('d.m.Y') }}</td>
                <td class="sag">{{ $sayi($g->cagri) }}</td>
                <td class="sag">{{ $tl($g->maliyet) }}</td>
                <td style="vertical-align:middle"><div class="ai-bar-bg"><div class="ai-bar" style="width:{{ $gunlukMax>0 ? round((float)$g->maliyet/$gunlukMax*100) : 0 }}%"></div></div></td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Veri yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Teknik detay: token. Varsayilan kapali --}}
<div class="ai-panel">
    <details class="ai-detay">
        <summary><span class="mdi mdi-chevron-right ok"></span><span class="rozet" style="background:linear-gradient(135deg,#475569,#94a3b8)"><span class="mdi mdi-cog"></span></span> Teknik ayrıntı (token) <span class="not">— merak edersen aç</span></summary>
        <div class="ai-sozluk">
            <div><b>Token nedir?</b>AI'nin okuduğu ve yazdığı metnin ölçü birimi. Kabaca 1 kelime ≈ 2 token. Fatura token üzerinden kesilir.</div>
            <div><b>Normal okuma</b>AI'nin o anda ilk kez okuduğu metin (müşteri mesajı, randevu bilgisi). Tam fiyat.</div>
            <div><b>Hafızadan okuma</b>Aynı talimat metni birkaç dakika içinde tekrar okunursa hafızadan gelir ve <b style="display:inline">10'da 1 fiyatına</b> okunur. Telefon asistanı bu yüzden çok token kullanıp az tutar.</div>
            <div><b>Hafızaya yazma</b>Talimat metninin hafızaya ilk alınması. Normal okumadan biraz pahalıdır. WhatsApp'ta mesajlar dakikalar arayla geldiği için hafıza sık sık yeniden yazılır.</div>
            <div><b>Yazma (çıktı)</b>AI'nin ürettiği cevap. En pahalı kısım ama genelde kısa.</div>
        </div>
        @if($eskiVar)
            <div class="ai-uyari" style="margin-bottom:12px">Hafıza ayrıntısı 23.11.2026'dan sonraki kayıtlarda tutuluyor. Daha eski kayıtların tutarı doğru, ama token kırılımı yok. Eski telefon kayıtlarında hafıza tokenları "normal okuma" içinde sayılmıştı.</div>
        @endif
        <div class="ai-tablo-sar">
        <table class="ai-tablo">
            <thead><tr><th>İş</th><th>Model</th><th class="sag">Normal okuma</th><th class="sag">Hafızadan okuma</th><th class="sag">Hafızaya yazma</th><th class="sag">Yazma (çıktı)</th><th class="sag">Tutar</th></tr></thead>
            <tbody>
            @forelse($turler as $t)
                <tr>
                    <td><span class="mdi {{ $kanal($t->tur)['ikon'] }}" style="color:{{ $kanal($t->tur)['renk'] }}"></span> {{ $kanal($t->tur)['ad'] }}</td>
                    <td style="font-size:12.5px;color:#6b5b8a">{{ $modelAd($t->model) }}</td>
                    <td class="sag">{{ $tk($t->girdi) }}</td>
                    <td class="sag">{{ $tk($t->okuma) }}</td>
                    <td class="sag">{{ $tk($t->yazma) }}</td>
                    <td class="sag">{{ $tk($t->cikti) }}</td>
                    <td class="sag">{{ $tl($t->maliyet) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:20px">Veri yok.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        <p class="acik" style="margin:12px 0 0">Fiyatlar (1 milyon token): Ekonomik model (Haiku) okuma $0,10 · hafızadan okuma $0,01 · hafızaya yazma $0,125 · yazma $0,50.
            Güçlü model (Sonnet) okuma $2 · yazma $10. "Hazır cevap" ise AI'ye hiç gitmeden sistemin kendi cevabı verdiği işlemdir; ücreti yoktur.</p>
    </details>
</div>

{{-- Kredi yukle --}}
<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#15803d,#22c55e)"><span class="mdi mdi-wallet-plus"></span></span>Kredi ayarları &amp; alarm</h2>
    <p class="acik">
        Anthropic bakiyeyi sisteme bildirmiyor. Bu yüzden buraya <b>yüklediğin toplam krediyi</b> (USD) gir. Kalan = yüklenen − tüm zamanlardaki harcama.
        Kalan, <b>eşiğin</b> altına inince <b>otomatik WhatsApp + SMS alarmı</b> gider (günde 1 kez). Son güncelleme: {{ $ayar['guncelleme'] ?? '—' }}
    </p>

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

@endsection
