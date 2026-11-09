@extends('sistemyonetim.v2.layout')

@section('content')

@php
    $sn = function ($ms) { return $ms === null ? '—' : number_format($ms / 1000, 1, ',', '.') . ' sn'; };
    $aralikEtiket = [1 => 'Son 1 saat', 6 => 'Son 6 saat', 24 => 'Son 24 saat', 72 => 'Son 3 gün', 168 => 'Son 7 gün'];
@endphp

<style>
    .pf-scroll { overflow-x: auto; }
    .pf-sql { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11.5px; color: var(--sy-text-muted, #6b7280); word-break: break-all; }
    .pf-bar { height: 8px; border-radius: 4px; background: var(--sy-warning, #d97706); min-width: 2px; }
    .pf-bar.zaman { background: var(--sy-danger, #dc2626); }
    .pf-detay summary { cursor: pointer; font-size: 12px; color: var(--sy-primary, #5C008E); }
    .pf-detay pre { white-space: pre-wrap; font-size: 11.5px; margin: 8px 0 0; }
    .pf-araliklar { display: flex; flex-wrap: wrap; gap: 6px; }
</style>

<div class="sy-page-head">
    <div>
        <h2>Sistem Performansı</h2>
        <div class="subtitle">Yavaş istekler, tarayıcı zaman aşımları ve nöbetçinin takılma anı görüntüleri · {{ $aralikEtiket[$saat] }}</div>
    </div>
    <div class="pf-araliklar">
        @foreach($aralikEtiket as $s => $etiket)
            <a href="?saat={{ $s }}{{ $hataTur ? '&tur='.$hataTur : '' }}" class="sy-btn sy-btn-sm {{ $s === $saat ? '' : 'sy-btn-soft' }}">{{ $etiket }}</a>
        @endforeach
    </div>
</div>

@if(!$tabloVar || !$anlikTablosu || !$hataTablosu)
<div class="sy-card" style="margin-top:12px;border-left:4px solid #d97706">
    <div class="sy-card-body" style="font-size:13px">
        <strong>Tablolar eksik.</strong> Migration çalışmamış: <code>/opt/php74/bin/php artisan migrate --force</code>
    </div>
</div>
@endif

<div class="sy-metric-grid">
    <div class="sy-metric {{ $ozet['anlik'] ? 'danger' : 'success' }}">
        <div class="icon-bg mdi mdi-alarm-light"></div>
        <div class="label">Takılma Anı</div>
        <div class="value">{{ $ozet['anlik'] }}</div>
        <div class="delta">Nöbetçinin eşik aşımı görüntüsü</div>
    </div>
    <div class="sy-metric {{ $ozet['zaman_asimi'] ? 'danger' : 'success' }}">
        <div class="icon-bg mdi mdi-timer-off-outline"></div>
        <div class="label">Tarayıcı Zaman Aşımı</div>
        <div class="value">{{ $ozet['zaman_asimi'] }}</div>
        <div class="delta">Kullanıcının cevap alamadığı istek</div>
    </div>
    <div class="sy-metric {{ $ozet['cok_yavas'] ? 'warning' : 'success' }}">
        <div class="icon-bg mdi mdi-turtle"></div>
        <div class="label">5 sn+ İstek</div>
        <div class="value">{{ $ozet['cok_yavas'] }}</div>
        <div class="delta">Belirgin takılma</div>
    </div>
    <div class="sy-metric">
        <div class="icon-bg mdi mdi-speedometer-slow"></div>
        <div class="label">1,5 sn+ İstek</div>
        <div class="value">{{ $ozet['yavas'] }}</div>
        <div class="delta">Kayda giren tüm yavaş istekler</div>
    </div>
</div>

@php
    $sunucuHata = ($hataSayilari['istisna'] ?? 0) + ($hataSayilari['http'] ?? 0) + ($hataSayilari['log'] ?? 0) + ($hataSayilari['komut'] ?? 0);
    $tipRozet = ['hata' => ['sy-badge-danger', 'Sunucu hatası'], 'yavas' => ['sy-badge-warning', 'Sunucu yavaş'], 'ulasmadi' => ['sy-badge-danger', 'Sunucuya ulaşmadı']];
    $turRozet = ['istisna' => 'sy-badge-danger', 'http' => 'sy-badge-danger', 'log' => 'sy-badge-warning', 'js' => 'sy-badge-warning', 'zaman_asimi' => 'sy-badge-danger', 'komut' => 'sy-badge'];
@endphp
<div class="sy-metric-grid" style="margin-top:12px">
    <div class="sy-metric {{ $sunucuHata ? 'danger' : 'success' }}">
        <div class="icon-bg mdi mdi-server-off"></div>
        <div class="label">Sunucu Hatası</div>
        <div class="value">{{ $sunucuHata }}</div>
        <div class="delta">İstisna · 5xx · log · komut</div>
    </div>
    <div class="sy-metric {{ !empty($hataSayilari['js']) ? 'warning' : 'success' }}">
        <div class="icon-bg mdi mdi-language-javascript"></div>
        <div class="label">Tarayıcı JS Hatası</div>
        <div class="value">{{ $hataSayilari['js'] ?? 0 }}</div>
        <div class="delta">Form/ekran takılmalarının sık kaynağı</div>
    </div>
    <div class="sy-metric {{ !empty($hataSayilari['zaman_asimi']) ? 'danger' : 'success' }}">
        <div class="icon-bg mdi mdi-lan-disconnect"></div>
        <div class="label">Zaman Aşımı / Kopma</div>
        <div class="value">{{ $hataSayilari['zaman_asimi'] ?? 0 }}</div>
        <div class="delta">Kullanıcı cevap alamadı</div>
    </div>
</div>

{{-- Zaman asimi analizi --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head"><strong>Zaman Aşımı Analizi</strong> <span class="sy-text-muted sy-fs-12"> — her olay o sırada sunucuda olanla eşleştirilir</span></div>
    <div class="sy-card-body tight pf-scroll">
        <table class="sy-table">
            <thead><tr><th>Zaman</th><th>İşletme</th><th>Uç</th><th>Bekleme</th><th>Neden</th></tr></thead>
            <tbody>
                @forelse($zamanAsimlari as $z)
                    <tr>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($z->zaman)->format('d.m H:i:s') }}</td>
                        <td class="sy-fs-13">@if($z->salon_id)<a href="/sistemyonetim/v2/salon/{{ $z->salon_id }}">{{ $z->salon_adi }}</a>@else — @endif</td>
                        <td class="sy-fs-13" style="word-break:break-all">{{ $z->uc }} <span class="sy-text-muted sy-fs-12">({{ $z->durum }})</span></td>
                        <td class="sy-fs-13 nowrap">{{ $sn($z->bekleme_ms) }}</td>
                        <td class="sy-fs-13" style="min-width:320px">
                            <span class="sy-badge {{ $tipRozet[$z->tip][0] }}">{{ $tipRozet[$z->tip][1] }}</span>
                            @if($z->ayni_anda > 0)<span class="sy-badge sy-badge-danger" title="±2 dk içinde zaman aşımına düşen diğer işletme sayısı">Genel: +{{ $z->ayni_anda }} işletme</span>@else<span class="sy-badge" title="±2 dk içinde başka işletmede zaman aşımı yok">Yalnız bu işletme</span>@endif
                            <div style="margin-top:4px">{{ $z->neden }}</div>
                            @if($z->en_yavas_sorgu)<div class="pf-sql" style="margin-top:3px">{{ \Illuminate\Support\Str::limit($z->en_yavas_sorgu, 220) }}</div>@endif
                            @if($z->anlik)<div class="sy-fs-12" style="margin-top:3px;color:#dc2626">O sırada takılma anı #{{ $z->anlik->id }}: {{ $z->anlik->nedenler }}</div>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="sy-empty"><div class="baslik">Bu aralıkta zaman aşımı yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Hatalar --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between">
        <div><strong>Hatalar</strong> <span class="sy-text-muted sy-fs-12"> — aynı hata tek satırda, bu aralıktaki tekrar sayısıyla</span></div>
        <div class="pf-araliklar">
            <a href="?saat={{ $saat }}" class="sy-btn sy-btn-sm {{ $hataTur === '' ? '' : 'sy-btn-soft' }}">Tümü</a>
            @foreach($hataTurleri as $k => $etiket)
                <a href="?saat={{ $saat }}&tur={{ $k }}" class="sy-btn sy-btn-sm {{ $hataTur === $k ? '' : 'sy-btn-soft' }}">{{ $etiket }} @if(!empty($hataSayilari[$k]))({{ $hataSayilari[$k] }})@endif</a>
            @endforeach
        </div>
    </div>
    <div class="sy-card-body tight pf-scroll">
        <table class="sy-table">
            <thead><tr><th>Hata</th><th>Tekrar</th><th>Etkilenen</th><th>Son</th><th>Ayrıntı</th></tr></thead>
            <tbody>
                @forelse($hatalar as $h)
                    <tr>
                        <td style="min-width:320px">
                            <span class="sy-badge {{ $turRozet[$h->tur] ?? 'sy-badge' }}">{{ $hataTurleri[$h->tur] ?? $h->tur }}</span>
                            <strong class="sy-fs-13">{{ \Illuminate\Support\Str::limit($h->baslik, 90) }}</strong>
                            @if($h->mesaj)<div class="sy-fs-13" style="margin-top:3px;word-break:break-word">{{ \Illuminate\Support\Str::limit($h->mesaj, 240) }}</div>@endif
                            <div class="pf-sql" style="margin-top:3px">{{ $h->uc }}@if($h->konum) · {{ $h->konum }}@endif</div>
                        </td>
                        <td class="sy-fs-13 nowrap"><strong>{{ $h->aralik_adet }}</strong><div class="sy-text-muted sy-fs-12">toplam {{ $h->adet }}</div></td>
                        <td class="sy-fs-12">
                            {{ $h->salon_sayisi }} işletme · {{ $h->kullanici_sayisi }} kullanıcı
                            @foreach($h->salonlar as $s)
                                <div><a href="/sistemyonetim/v2/salon/{{ $s['id'] }}">{{ $s['ad'] }}</a> ({{ $s['adet'] }})</div>
                            @endforeach
                        </td>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($h->aralik_son)->format('d.m H:i') }}<div class="sy-text-muted">ilk {{ \Carbon\Carbon::parse($h->ilk_gorulme)->format('d.m H:i') }}</div></td>
                        <td style="min-width:200px">
                            @if($h->ornek)
                                <details class="pf-detay"><summary>İz / yığın</summary><pre>{{ $h->ornek }}</pre></details>
                            @else <span class="sy-text-muted sy-fs-12">—</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="sy-empty"><div class="baslik">Bu aralıkta hata yok</div>@if(!$hataTablosu)<div>Hata tabloları henüz oluşmamış (migration).</div>@endif</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Nobetci goruntuleri --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head"><strong>Takılma Anları</strong> <span class="sy-text-muted sy-fs-12"> — o anda çalışan sorgular, bellek ve en yavaş uçlar</span></div>
    <div class="sy-card-body tight pf-scroll">
        <table class="sy-table">
            <thead><tr><th>Zaman</th><th>Neden</th><th>Boş RAM</th><th>Swap</th><th>Load</th><th>En uzun sorgu</th><th>Alarm</th><th>Ayrıntı</th></tr></thead>
            <tbody>
                @forelse($anliklar as $a)
                    <tr>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($a->created_at)->format('d.m H:i') }}</td>
                        <td class="sy-fs-13">{{ $a->nedenler }}</td>
                        <td class="sy-fs-13 nowrap">{{ $a->bos_bellek_mb !== null ? $a->bos_bellek_mb . ' MB' : '—' }}</td>
                        <td class="sy-fs-13 nowrap">{{ $a->swap_kullanilan_mb !== null ? $a->swap_kullanilan_mb . ' MB' : '—' }}</td>
                        <td class="sy-fs-13">{{ $a->load1 ?? '—' }}</td>
                        <td class="sy-fs-13 nowrap">{{ $a->en_uzun_sorgu_sn ? $a->en_uzun_sorgu_sn . ' sn' : '—' }}</td>
                        <td>@if($a->alarm_gonderildi)<span class="sy-badge sy-badge-danger">Gönderildi</span>@else<span class="sy-badge">—</span>@endif</td>
                        <td style="min-width:260px">
                            <details class="pf-detay">
                                <summary>Sorgular · uçlar · süreçler</summary>
                                @if(!empty($a->detay['sorgular']))
                                    <div style="margin-top:8px"><strong class="sy-fs-12">Çalışan sorgular</strong></div>
                                    @foreach($a->detay['sorgular'] as $q)
                                        <div class="pf-sql" style="margin-top:4px"><strong>{{ $q['sn'] }} sn</strong> · {{ $q['durum'] }} · {{ $q['sql'] }}</div>
                                    @endforeach
                                @endif
                                @if(!empty($a->detay['yavaslar']))
                                    <div style="margin-top:8px"><strong class="sy-fs-12">Son 5 dk en yavaş uçlar</strong></div>
                                    @foreach($a->detay['yavaslar'] as $y)
                                        <div class="sy-fs-12" style="margin-top:2px">{{ $y['uc'] }} ({{ $y['method'] }}) · {{ $y['adet'] }} kez · en uzun {{ $sn($y['en_uzun_ms']) }} · salon {{ $y['salonlar'] ?: '—' }}</div>
                                    @endforeach
                                @endif
                                @if(!empty($a->detay['hatalar']))
                                    <div style="margin-top:8px"><strong class="sy-fs-12">Son 5 dk hatalar</strong></div>
                                    @foreach($a->detay['hatalar'] as $hh)
                                        <div class="sy-fs-12" style="margin-top:2px">{{ $hh['adet'] }}× {{ $hh['baslik'] }} @if($hh['mesaj'])— {{ \Illuminate\Support\Str::limit($hh['mesaj'], 120) }}@endif · {{ $hh['uc'] }}</div>
                                    @endforeach
                                @endif
                                @if(!empty($a->detay['surecler']))
                                    <pre>{{ $a->detay['surecler'] }}</pre>
                                @endif
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="sy-empty"><div class="baslik">Bu aralıkta takılma anı yok</div><div>Nöbetçi her dakika kontrol eder; eşik aşılırsa burada görünür ve alarm gider.</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- En cok etkileyen uclar --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head"><strong>En Çok Yavaşlatan Uçlar</strong> <span class="sy-text-muted sy-fs-12"> — toplam süreye göre (sıklık × süre)</span></div>
    <div class="sy-card-body tight pf-scroll">
        <table class="sy-table">
            <thead><tr><th>Uç</th><th>Tür</th><th>Adet</th><th>Ort.</th><th>En uzun</th><th>Ort. sorgu</th>@if($sorguKolonu)<th>Ort. DB süresi</th>@endif<th>Salon</th></tr></thead>
            <tbody>
                @forelse($uclar as $u)
                    <tr>
                        <td class="sy-fs-13" style="word-break:break-all">{{ $u->uc }}</td>
                        <td>@if($u->method === 'TIMEOUT')<span class="sy-badge sy-badge-danger">Zaman aşımı</span>@else<span class="sy-badge">{{ $u->method }}</span>@endif</td>
                        <td class="sy-fs-13">{{ $u->adet }}</td>
                        <td class="sy-fs-13 nowrap">{{ $sn($u->ort_ms) }}</td>
                        <td class="sy-fs-13 nowrap"><strong>{{ $sn($u->en_uzun_ms) }}</strong></td>
                        <td class="sy-fs-13">{{ $u->ort_sorgu ?? '—' }}</td>
                        @if($sorguKolonu)<td class="sy-fs-13 nowrap">{{ $sn($u->ort_sorgu_ms) }}</td>@endif
                        <td class="sy-fs-13">{{ $u->salon_sayisi }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="sy-empty"><div class="baslik">Bu aralıkta yavaş istek yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sy-grid-2" style="margin-top:16px">
    <div class="sy-card">
        <div class="sy-card-head"><strong>Etkilenen İşletmeler</strong></div>
        <div class="sy-card-body tight pf-scroll">
            <table class="sy-table">
                <thead><tr><th>İşletme</th><th>Yavaş</th><th>Zaman aşımı</th><th>En uzun</th></tr></thead>
                <tbody>
                    @forelse($salonlar as $s)
                        <tr>
                            <td class="sy-fs-13"><a href="/sistemyonetim/v2/salon/{{ $s->salon_id }}">{{ $s->salon_adi }}</a></td>
                            <td class="sy-fs-13">{{ $s->adet }}</td>
                            <td class="sy-fs-13">@if($s->zaman_asimi)<span class="sy-badge sy-badge-danger">{{ $s->zaman_asimi }}</span>@else 0 @endif</td>
                            <td class="sy-fs-13 nowrap">{{ $sn($s->en_uzun_ms) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="sy-empty"><div class="baslik">Kayıt yok</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="sy-card">
        <div class="sy-card-head"><strong>Saatlik Dağılım</strong> <span class="sy-text-muted sy-fs-12"> — sarı: yavaş, kırmızı: zaman aşımı</span></div>
        <div class="sy-card-body tight pf-scroll">
            @php $tepe = max(1, (int) $saatlik->max('adet')); @endphp
            <table class="sy-table">
                <thead><tr><th>Saat</th><th>Adet</th><th style="width:55%"></th></tr></thead>
                <tbody>
                    @forelse($saatlik as $h)
                        <tr>
                            <td class="sy-fs-12 nowrap">{{ $h->dilim }}</td>
                            <td class="sy-fs-13 nowrap">{{ $h->adet }}@if($h->zaman_asimi) · <span style="color:#dc2626">{{ $h->zaman_asimi }}</span>@endif</td>
                            <td>
                                <div style="display:flex;gap:2px">
                                    <div class="pf-bar" style="width:{{ round(($h->adet - $h->zaman_asimi) / $tepe * 100) }}%"></div>
                                    @if($h->zaman_asimi)<div class="pf-bar zaman" style="width:{{ round($h->zaman_asimi / $tepe * 100) }}%"></div>@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="sy-empty"><div class="baslik">Kayıt yok</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Son istekler --}}
<div class="sy-card" style="margin-top:16px">
    <div class="sy-card-head"><strong>Son Yavaş İstekler</strong> <span class="sy-text-muted sy-fs-12"> — son 100 kayıt</span></div>
    <div class="sy-card-body tight pf-scroll">
        <table class="sy-table">
            <thead><tr><th>Zaman</th><th>Uç</th><th>Süre</th><th>Sorgu</th>@if($sorguKolonu)<th>DB süresi</th>@endif<th>Salon</th>@if($sorguKolonu)<th>En yavaş sorgu</th>@endif</tr></thead>
            <tbody>
                @forelse($sonIstekler as $r)
                    <tr>
                        <td class="nowrap sy-fs-12">{{ \Carbon\Carbon::parse($r->created_at)->format('d.m H:i:s') }}</td>
                        <td class="sy-fs-13" style="word-break:break-all">
                            @if($r->method === 'TIMEOUT')<span class="sy-badge sy-badge-danger">Zaman aşımı</span>@endif
                            {{ $r->aksiyon ?: $r->uri }}
                        </td>
                        <td class="sy-fs-13 nowrap"><strong>{{ $sn($r->sure_ms) }}</strong></td>
                        <td class="sy-fs-13">{{ $r->sorgu_sayisi ?? '—' }}</td>
                        @if($sorguKolonu)<td class="sy-fs-13 nowrap">{{ $r->method === 'TIMEOUT' ? '—' : $sn($r->sorgu_ms) }}</td>@endif
                        <td class="sy-fs-13">{{ $r->salon_id ?: '—' }}</td>
                        @if($sorguKolonu)
                            <td class="pf-sql">@if($r->en_yavas_sorgu)<strong>{{ $sn($r->en_yavas_sorgu_ms) }}</strong> · {{ \Illuminate\Support\Str::limit($r->en_yavas_sorgu, 160) }}@else — @endif</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="sy-empty"><div class="baslik">Bu aralıkta yavaş istek yok</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
