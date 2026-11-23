@extends('sistemyonetim.v2.layout')

@section('content')
@php
    $kur = $kur ?: 40;
    extract(\App\Services\AiKrediGorunum::yardimcilar($kur)); // $usd $tl $birim $sayi $tk $kanal $rgba $modelAd $tarih $telMaske
    $renk = \App\Services\AiKrediGorunum::avatarRenk($salonId);
    $gunAd = [1 => 'bugün', 7 => 'son 7 gün', 30 => 'son 30 gün', 0 => 'tüm zamanlar'][(int)$gun] ?? 'seçili dönem';
    $gunlukMax = 0; foreach ($gunluk as $g) { $gunlukMax = max($gunlukMax, (float)$g['maliyet']); }
    $sonucAd = ['randevu' => 'Randevu aldı', 'iptal' => 'İptal etti', 'guncelle' => 'Randevu değişti', 'aktar' => 'Personele aktarıldı',
                'teyit' => 'Teyit etti', 'bilgi' => 'Bilgi aldı', 'odeme' => 'Ödeme'];
    $sonucEt = function ($s) use ($sonucAd) {
        if (!$s) return '<span style="color:#94a3b8">—</span>';
        return '<span class="ai-etiket ' . e($s) . '">' . e($sonucAd[$s] ?? ucfirst($s)) . '</span>';
    };
    $sure = function ($sn) { $sn = (int)$sn; return $sn <= 0 ? '—' : ($sn >= 60 ? floor($sn / 60) . ' dk ' . ($sn % 60) . ' sn' : $sn . ' sn'); };
@endphp

@include('sistemyonetim.v2.partials.ai-kredi-stil')

<a href="/sistemyonetim/v2/ai-kredi?gun={{ (int)$gun }}" class="ai-geri"><span class="mdi mdi-arrow-left"></span> AI Kredi &amp; Kullanım</a>

<div class="ai-baslik">
    <div class="ai-kimlik-bas">
        <div class="avatar" style="background:{{ $renk }}">{{ mb_strtoupper(mb_substr(trim($salonAdi), 0, 1)) }}</div>
        <div>
            <h1>{{ $salonAdi }}</h1>
            <div class="alt">AI kullanım detayı · {{ $gunAd }} · <a href="/sistemyonetim/v2/salon/{{ $salonId }}">İşletme kartına git</a></div>
        </div>
    </div>
    <div class="ai-filtre">
        @foreach([1=>'Bugün',7=>'7 Gün',30=>'30 Gün',0=>'Tümü'] as $g=>$ad)
            <a href="?gun={{ $g }}" class="{{ (int)$gun===$g ? 'act':'' }}">{{ $ad }}</a>
        @endforeach
    </div>
</div>

<div class="ai-kartlar">
    <div class="ai-kart mor"><span class="mdi mdi-cash-multiple buyuk"></span>
        <div class="e">Harcama ({{ $gunAd }})</div>
        <div class="d">{{ $tl($ozet['maliyet_usd']) }}</div>
        <div class="alt">{{ $usd($ozet['maliyet_usd']) }} · tüm işletmelerin %{{ $ozet['pay'] > 0 && $ozet['pay'] < 1 ? '<1' : round($ozet['pay']) }}</div>
    </div>
    <div class="ai-kart mavi"><span class="mdi mdi-calendar-today buyuk"></span>
        <div class="e">Bugün harcanan</div>
        <div class="d">{{ $tl($ozet['bugun_usd']) }}</div>
        <div class="alt">Son kullanım {{ $tarih($ozet['son'], 'd.m.Y H:i') }}</div>
    </div>
    <div class="ai-kart turuncu"><span class="mdi mdi-lightning-bolt buyuk"></span>
        <div class="e">AI işlemi ({{ $gunAd }})</div>
        <div class="d">{{ $sayi($ozet['cagri']) }}</div>
        <div class="alt">İşlem başına ortalama {{ $birim($ozet['gercek'] > 0 ? $ozet['maliyet_usd'] / $ozet['gercek'] : 0) }}</div>
    </div>
    <div class="ai-kart iyi"><span class="mdi mdi-chat-processing buyuk"></span>
        <div class="e">WhatsApp sohbeti / telefon görüşmesi</div>
        <div class="d">{{ $sayi($waAdet) }} / {{ $sayi($santralAdet) }}</div>
        <div class="alt">AI'nin para harcadığı sohbet ve görüşmeler</div>
    </div>
</div>

<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#5C008E,#9D5DC9)"><span class="mdi mdi-chart-donut"></span></span>Para nereye gidiyor?</h2>
    <p class="acik">Bu işletmenin parası hangi AI işine gitti. Çubuk, işletmenin kendi harcamasındaki payı gösterir.</p>
    @include('sistemyonetim.v2.partials.ai-kredi-kanallar')
</div>

<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#1d4ed8,#38bdf8)"><span class="mdi mdi-calendar-month"></span></span>Gün gün harcama</h2>
    <p class="acik">Her gün ne kadar harcandı. Çubuktaki renkler hangi işe gittiğini gösterir (son 30 gün).</p>
    <div class="ai-tablo-sar">
    <table class="ai-tablo">
        <thead><tr><th>Gün</th><th class="sag">İşlem</th><th class="sag">Tutar</th><th style="width:45%">Dağılım</th></tr></thead>
        <tbody>
        @forelse($gunluk as $gunTarih => $g)
            <tr>
                <td>{{ date('d.m.Y', strtotime($gunTarih)) }}</td>
                <td class="sag">{{ $sayi($g['cagri']) }}</td>
                <td class="sag">{{ $tl($g['maliyet']) }}</td>
                <td style="vertical-align:middle">
                    <div style="display:flex;height:10px;border-radius:6px;overflow:hidden;background:#f0ebf8;width:{{ $gunlukMax > 0 ? max(3, round($g['maliyet'] / $gunlukMax * 100)) : 0 }}%">
                        @foreach($g['tur'] as $tur => $m)
                            @if($g['maliyet'] > 0 && $m > 0)
                            <span style="width:{{ round($m / $g['maliyet'] * 100, 1) }}%;background:{{ $kanal($tur)['renk'] }}" title="{{ $kanal($tur)['ad'] }}: {{ $tl($m) }}"></span>
                            @endif
                        @endforeach
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde kullanım yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="ai-iki">
    <div class="ai-panel">
        <h2><span class="rozet" style="background:linear-gradient(135deg,#15803d,#22c55e)"><span class="mdi mdi-whatsapp"></span></span>En çok harcayan WhatsApp sohbetleri</h2>
        <p class="acik">Sohbet boyunca AI'nin toplam harcaması. Uzun ya da tekrar eden sohbetler burada üstte çıkar.</p>
        <div class="ai-tablo-sar">
        <table class="ai-tablo">
            <thead><tr><th>Müşteri</th><th class="sag">Mesaj / Cevap</th><th>Sonuç</th><th class="sag">Tutar</th></tr></thead>
            <tbody>
            @forelse($waSohbetler as $w)
                <tr>
                    <td><b>{{ $w->ad ?: 'İsimsiz' }}</b><div style="font-size:12px;color:#8b7ba8">{{ $telMaske($w->musteri_tel ?: $w->telefon) }} · {{ $tarih($w->son_mesaj_at ?: $w->created_at) }}</div></td>
                    <td class="sag">{{ $sayi($w->mesaj_sayisi) }} / {{ $sayi($w->cevap_sayisi) }}</td>
                    <td>{!! $sonucEt($w->sonuc) !!}</td>
                    <td class="sag">{{ $tl($w->maliyet_usd) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde ücretli WhatsApp sohbeti yok.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="ai-panel">
        <h2><span class="rozet" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)"><span class="mdi mdi-phone"></span></span>En çok harcayan telefon görüşmeleri</h2>
        <p class="acik">Görüşme başına AI maliyeti (konuşma metni). Ses (TTS) ücreti Santral Asistan Paketleri ekranında ayrıca görünür.</p>
        <div class="ai-tablo-sar">
        <table class="ai-tablo">
            <thead><tr><th>Arayan</th><th class="sag">Süre</th><th>Sonuç</th><th class="sag">Tutar</th></tr></thead>
            <tbody>
            @forelse($santralGorusmeler as $s)
                <tr>
                    <td><b>{{ $telMaske($s->telefon) }}</b><div style="font-size:12px;color:#8b7ba8">{{ $tarih($s->created_at) }}{{ $s->yon === 'giden' ? ' · giden arama' : '' }}</div></td>
                    <td class="sag" style="font-weight:400">{{ $sure($s->sure_sn) }}</td>
                    <td>{!! $sonucEt($s->sonuc) !!}</td>
                    <td class="sag">{{ $tl($s->maliyet_usd) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde ücretli telefon görüşmesi yok.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="ai-panel">
    <h2><span class="rozet" style="background:linear-gradient(135deg,#475569,#94a3b8)"><span class="mdi mdi-format-list-bulleted"></span></span>Son AI işlemleri</h2>
    <p class="acik">Tek tek son 60 işlem. "Hafızadan" okunan kısım 10'da 1 fiyatınadır; boş görünen hafıza değerleri 23.11.2026 öncesi kayıtlardır.</p>
    <div class="ai-tablo-sar">
    <table class="ai-tablo">
        <thead><tr><th>Zaman</th><th>İş</th><th>Model</th><th class="sag">Normal okuma</th><th class="sag">Hafızadan</th><th class="sag">Hafızaya yazma</th><th class="sag">Çıktı</th><th class="sag">Tutar</th></tr></thead>
        <tbody>
        @forelse($sonIslemler as $r)
            @php $k = $kanal($r->tur); $eski = !property_exists($r, 'cache_okuma_token') || $r->cache_okuma_token === null; @endphp
            <tr>
                <td style="white-space:nowrap">{{ $tarih($r->created_at, 'd.m H:i:s') }}</td>
                <td style="white-space:nowrap"><span class="mdi {{ $k['ikon'] }}" style="color:{{ $k['renk'] }}"></span> {{ $k['ad'] }}</td>
                <td style="font-size:12.5px;color:#6b5b8a">{{ $r->cache ? 'Hazır cevap' : $modelAd($r->model) }}</td>
                <td class="sag" style="font-weight:400">{{ $tk($r->girdi_token) }}</td>
                <td class="sag" style="font-weight:400">{{ $eski ? '—' : $tk($r->cache_okuma_token) }}</td>
                <td class="sag" style="font-weight:400">{{ $eski ? '—' : $tk($r->cache_yazma_token) }}</td>
                <td class="sag" style="font-weight:400">{{ $tk($r->cikti_token) }}</td>
                <td class="sag">{{ $r->cache ? 'Bedava' : $birim($r->maliyet_usd) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:20px">Bu dönemde işlem yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

@endsection
