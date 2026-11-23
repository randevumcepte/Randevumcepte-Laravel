@extends('sistemyonetim.v2.layout')

@section('content')
@php
    $kur = $kur ?: 40;
    extract(\App\Services\AiKrediGorunum::yardimcilar($kur)); // $usd $tl $birim $sayi $tk $kanal $rgba $modelAd

    $donemUsd = (float)$ozet['maliyet_usd'];
    $gunAd = [1 => 'bugün', 7 => 'son 7 gün', 30 => 'son 30 gün', 0 => 'tüm zamanlar'][(int)$gun] ?? 'seçili dönem';
    $gunlukMax = 0; foreach(($gunluk ?? []) as $g){ $gunlukMax = max($gunlukMax, (float)$g->maliyet); }
    $enPahali = count($turler) ? $turler[0] : null;
    $eskiVar = false; foreach ($turler as $t) { if ((int)$t->eski > 0) { $eskiVar = true; break; } }
@endphp

@include('sistemyonetim.v2.partials.ai-kredi-stil')

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
    @include('sistemyonetim.v2.partials.ai-kredi-kanallar')
</div>

{{-- Salon bazinda --}}
<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#db2777,#f472b6)"><span class="mdi mdi-store"></span></span>Hangi işletme ne kadar harcıyor?</h2>
    <p class="acik">İşletmeye tıklayınca detay sayfası açılır: hangi gün ne harcadı, en pahalı sohbetler ve görüşmeler, tek tek tüm işlemler.</p>
    <div class="ai-isletmeler">
    @forelse($salonlar as $i => $s)
        @php
            $pay = $donemUsd > 0 ? (float)$s->maliyet / $donemUsd * 100 : 0;
            $renk = \App\Services\AiKrediGorunum::avatarRenk($s->salon_id);
            $dagilim = $salonTur[(int)$s->salon_id] ?? [];
            $link = $s->salon_id ? '/sistemyonetim/v2/ai-kredi/isletme/' . $s->salon_id . '?gun=' . (int)$gun : null;
        @endphp
        <a class="ai-isletme" @if($link) href="{{ $link }}" @endif style="--renk:{{ $renk }};--zemin:{{ $rgba($renk, .08) }}">
            <div class="sira">{{ $i + 1 }}</div>
            <div class="avatar" style="background:{{ $renk }}">{{ mb_strtoupper(mb_substr(trim($s->salon_adi), 0, 1)) }}</div>
            <div class="kimlik">
                <div class="ad">{{ $s->salon_adi }}</div>
                <div class="alt">{{ $sayi($s->cagri) }} işlem · son kullanım {{ $s->son ? \Carbon\Carbon::parse($s->son)->format('d.m H:i') : '—' }}</div>
                <div class="ai-pay" style="margin-top:6px">
                    <div class="bg"><div class="bar" style="width:{{ round($pay) }}%;background:{{ $renk }}"></div></div>
                    <span class="yuzde">pay %{{ $pay < 1 && $pay > 0 ? '<1' : round($pay) }}</span>
                </div>
            </div>
            <div class="dagilim">
                <div class="yigin">
                    @foreach($dagilim as $st)
                        @if((float)$s->maliyet > 0 && (float)$st->maliyet > 0)
                        <span style="width:{{ max(2, round((float)$st->maliyet / (float)$s->maliyet * 100, 1)) }}%;background:{{ $kanal($st->tur)['renk'] }}" title="{{ $kanal($st->tur)['ad'] }}: {{ $tl($st->maliyet) }}"></span>
                        @endif
                    @endforeach
                </div>
                <div class="lejant">
                    @foreach($dagilim as $st)
                        <span><i style="background:{{ $kanal($st->tur)['renk'] }}"></i>{{ $kanal($st->tur)['ad'] }} <b>{{ $tl($st->maliyet) }}</b></span>
                    @endforeach
                </div>
            </div>
            <div class="tutar">
                <div class="tl">{{ $tl($s->maliyet) }}</div>
                <div class="us">{{ $usd($s->maliyet) }}</div>
            </div>
            @if($link)<div class="git">Detay <span class="mdi mdi-chevron-right"></span></div>@endif
        </a>
    @empty
        <p class="acik" style="text-align:center;padding:10px 0">Bu dönemde işletme kullanımı yok.</p>
    @endforelse
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
