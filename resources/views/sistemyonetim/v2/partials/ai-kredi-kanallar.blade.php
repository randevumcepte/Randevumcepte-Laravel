{{-- Kanal kartlari (genel + isletme detay). Girdi: $turler, $donemUsd, $kur --}}
@php extract(\App\Services\AiKrediGorunum::yardimcilar($kur)); @endphp
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
