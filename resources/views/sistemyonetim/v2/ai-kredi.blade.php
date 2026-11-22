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
        'wa_asistan' => ['ad' => 'WhatsApp Asistanı',       'ikon' => 'mdi-whatsapp',     'renk' => '#22c55e', 'birim' => 'cevap',          'ne' => 'Müşterilerin WhatsApp mesajlarına AI\'nin yazdığı cevaplar'],
        'santral'    => ['ad' => 'Telefon Asistanı',        'ikon' => 'mdi-phone',        'renk' => '#3b82f6', 'birim' => 'konuşma sırası', 'ne' => 'Santralde telefonu AI açar; müşterinin her konuşma sırası 1 işlem'],
        'wa_gorsel'  => ['ad' => 'Fotoğraftan Fiyat',       'ikon' => 'mdi-camera',       'renk' => '#f59e0b', 'birim' => 'fotoğraf',       'ne' => 'Müşterinin WhatsApp\'tan attığı fotoğrafı inceleyip fiyat çıkarma'],
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
    .ai-kart { background:#fff; border:1px solid #ece7f6; border-radius:14px; padding:16px; box-shadow:0 2px 8px rgba(92,0,142,.05); }
    .ai-kart .e { font-size:12px; color:#8b7ba8; font-weight:600; }
    .ai-kart .d { font-size:24px; font-weight:700; margin-top:5px; color:#2a2340; }
    .ai-kart .alt { font-size:12.5px; color:#8b7ba8; margin-top:4px; line-height:1.45; }
    .ai-kart.iyi .d { color:#16a34a; } .ai-kart.kotu .d { color:#dc2626; }

    .ai-panel { background:#fff; border:1px solid #ece7f6; border-radius:14px; padding:18px; margin-bottom:18px; box-shadow:0 2px 8px rgba(92,0,142,.05); }
    .ai-panel h2 { font-size:16px; color:#2a2340; font-weight:700; margin:0 0 4px; }
    .ai-panel .acik { font-size:13px; color:#8b7ba8; margin:0 0 14px; line-height:1.5; }

    .ai-kanal { display:grid; grid-template-columns:44px 1fr auto; gap:14px; align-items:center; padding:14px 0; border-bottom:1px solid #f4f1fa; }
    .ai-kanal:last-child { border-bottom:none; }
    .ai-ikon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; color:#fff; }
    .ai-kanal .ad { font-size:15px; font-weight:700; color:#2a2340; }
    .ai-kanal .ne { font-size:12.5px; color:#8b7ba8; margin-top:2px; }
    .ai-kanal .bilgi { font-size:13px; color:#4b3d6b; margin-top:7px; display:flex; gap:6px 16px; flex-wrap:wrap; }
    .ai-kanal .bilgi b { color:#2a2340; }
    .ai-kanal .tutar { text-align:right; white-space:nowrap; }
    .ai-kanal .tutar .tl { font-size:19px; font-weight:700; color:#2a2340; }
    .ai-kanal .tutar .us { font-size:12px; color:#94a3b8; }
    .ai-pay { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .ai-pay .bg { flex:1; background:#f0ebf8; border-radius:6px; height:8px; overflow:hidden; max-width:420px; }
    .ai-pay .bar { height:8px; border-radius:6px; }
    .ai-pay .yuzde { font-size:12px; font-weight:700; color:#6b5b8a; min-width:38px; }
    .ai-cip { display:inline-block; font-size:11.5px; padding:3px 9px; border-radius:20px; background:#f5f2fb; color:#5b4a7a; margin:3px 4px 0 0; }
    .ai-cip.yesil { background:#e8f8ef; color:#15803d; }

    .ai-tablo { width:100%; border-collapse:collapse; font-size:13px; }
    .ai-tablo th { text-align:left; font-size:11px; color:#8b7ba8; font-weight:600; padding:9px 10px; border-bottom:2px solid #f0ebf8; }
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

    @media (max-width:600px) {
        .ai-kanal { grid-template-columns:36px 1fr; }
        .ai-ikon { width:36px; height:36px; font-size:18px; }
        .ai-kanal .tutar { grid-column:2; text-align:left; }
    }
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
    <div class="ai-kart">
        <div class="e">Kalan kredi</div>
        <div class="d" style="font-size:16px;color:#b45309">Kredi girilmedi</div>
        <div class="alt">En alttan yüklediğin krediyi gir ↓</div>
    </div>
    @else
    <div class="ai-kart {{ $kalanUsd < ((float)$ayar['yuklenen_usd']*0.15) ? 'kotu' : 'iyi' }}">
        <div class="e">Kalan kredi</div>
        <div class="d">{{ $tl($kalanUsd) }}</div>
        <div class="alt">{{ $usd($kalanUsd) }} · toplam yüklenen {{ $usd($ayar['yuklenen_usd']) }}
            @if($kalanGun !== null)<br>Bu hızla ~<b>{{ $sayi($kalanGun) }} gün</b> yeter @endif</div>
    </div>
    @endif
    <div class="ai-kart">
        <div class="e">Harcama ({{ $gunAd }})</div>
        <div class="d">{{ $tl($donemUsd) }}</div>
        <div class="alt">{{ $usd($donemUsd) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">Bugün harcanan</div>
        <div class="d">{{ $tl($ozet['bugun_usd']) }}</div>
        <div class="alt">Günlük ortalama ~{{ $tl($gunlukOrtUsd) }}</div>
    </div>
    <div class="ai-kart">
        <div class="e">AI işlemi ({{ $gunAd }})</div>
        <div class="d">{{ $sayi($ozet['cagri']) }}</div>
        <div class="alt">İşlem başına ortalama {{ $birim($ozet['gercek'] > 0 ? $donemUsd / $ozet['gercek'] : 0) }}
            @if($ozet['cache'] > 0)<br><span style="color:#15803d">{{ $sayi($ozet['cache']) }} hazır cevap bedava geldi ({{ $tl($ozet['cache_tasarruf_usd']) }} tasarruf)</span>@endif</div>
    </div>
</div>

{{-- Para nereye gidiyor --}}
<div class="ai-panel">
    <h2>Para nereye gidiyor?</h2>
    <p class="acik">Her satır AI'nin yaptığı bir iş. Çubuk, toplam harcamadaki payı gösterir. "1 işlem" o işin tek seferlik maliyetidir.</p>
    @forelse($turler as $t)
        @php $k = $kanal($t->tur); $pay = $donemUsd > 0 ? (float)$t->maliyet / $donemUsd * 100 : 0; $aiAdet = (int)$t->cagri - (int)$t->cache; @endphp
        <div class="ai-kanal">
            <div class="ai-ikon" style="background:{{ $k['renk'] }}"><span class="mdi {{ $k['ikon'] }}"></span></div>
            <div>
                <div class="ad">{{ $k['ad'] }}</div>
                <div class="ne">{{ $k['ne'] }}</div>
                <div class="bilgi">
                    <span><b>{{ $sayi($t->cagri) }}</b> {{ $k['birim'] }}</span>
                    <span>1 işlem ≈ <b>{{ $birim($aiAdet > 0 ? (float)$t->maliyet / $aiAdet : 0) }}</b></span>
                    @if($t->model)<span>{{ $modelAd($t->model) }}</span>@endif
                </div>
                @if((int)$t->cache > 0)<span class="ai-cip yesil">{{ $sayi($t->cache) }} hazır cevap · bedava</span>@endif
                <div class="ai-pay">
                    <div class="bg"><div class="bar" style="width:{{ round($pay) }}%;background:{{ $k['renk'] }}"></div></div>
                    <span class="yuzde">%{{ $pay < 1 && $pay > 0 ? '<1' : round($pay) }}</span>
                </div>
            </div>
            <div class="tutar">
                <div class="tl">{{ $tl($t->maliyet) }}</div>
                <div class="us">{{ $usd($t->maliyet) }}</div>
            </div>
        </div>
    @empty
        <p class="acik" style="text-align:center;padding:10px 0">Bu dönemde AI kullanımı yok.</p>
    @endforelse
</div>

{{-- Salon bazinda --}}
<div class="ai-panel">
    <h2>Hangi işletme ne kadar harcıyor?</h2>
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
                        <div class="bg"><div class="bar" style="width:{{ round($pay) }}%;background:#7B2FB8"></div></div>
                        <span class="yuzde">%{{ $pay < 1 && $pay > 0 ? '<1' : round($pay) }}</span>
                    </div>
                </td>
                <td>
                    @foreach(($salonTur[(int)$s->salon_id] ?? []) as $st)
                        <span class="ai-cip"><span class="mdi {{ $kanal($st->tur)['ikon'] }}" style="color:{{ $kanal($st->tur)['renk'] }}"></span> {{ $kanal($st->tur)['ad'] }} · <b>{{ $tl($st->maliyet) }}</b></span>
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
    <h2>Gün gün harcama</h2>
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
        <summary><span class="mdi mdi-chevron-right ok"></span> Teknik ayrıntı (token) <span class="not">— merak edersen aç</span></summary>
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
    <h2>Kredi ayarları &amp; alarm</h2>
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
