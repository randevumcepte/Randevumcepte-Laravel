@extends('sistemyonetim.v2.layout')

@section('content')
<style>
    .is-ozet { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:18px; }
    .is-kutu { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:14px 16px; box-shadow:var(--sy-shadow-sm); }
    .is-kutu .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; }
    .is-kutu .d { font-size:21px; font-weight:700; margin-top:4px; color:var(--sy-text,#2a1f48); font-variant-numeric:tabular-nums; }
    .is-kutu .a { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .is-sekme { display:flex; gap:6px; flex-wrap:wrap; }
    .is-sekme a { font-size:12.5px; font-weight:600; padding:6px 12px; border-radius:20px; text-decoration:none; color:var(--sy-text-muted,#7e7595); border:1px solid var(--sy-border,#ece6f7); }
    .is-sekme a.aktif { background:var(--sy-primary-soft,#f0e8fb); color:var(--sy-text,#2a1f48); border-color:transparent; }
    .is-tablo { width:100%; border-collapse:collapse; font-size:13px; color:var(--sy-text,#2a1f48); }
    .is-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); white-space:nowrap; }
    .is-tablo td { padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); vertical-align:middle; }
    .is-tablo tr:hover td { background:var(--sy-primary-soft,#f0e8fb); }
    .is-tablo .r { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .is-ad { font-weight:600; } .is-alt { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .is-rozet { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; white-space:nowrap; }
    .is-rozet.r1 { background:#e6eefb; color:#2f5fb3; } .is-rozet.r0 { background:#fbe9f3; color:#b33a7d; }
    .is-rozet.r2 { background:var(--sy-border,#ece6f7); color:var(--sy-text-muted,#7e7595); }
    .is-rozet.bek { background:#fff4e2; color:#a8670f; }
    .is-karar { display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; }
    .is-karar button { font-size:12px; font-weight:600; padding:5px 10px; border-radius:8px; cursor:pointer; border:1px solid var(--sy-border,#ece6f7); background:var(--sy-surface,#fff); color:var(--sy-text,#2a1f48); }
    .is-karar button.k1:hover, .is-karar button.k1.secili { background:#2f5fb3; color:#fff; border-color:#2f5fb3; }
    .is-karar button.k0:hover, .is-karar button.k0.secili { background:#b33a7d; color:#fff; border-color:#b33a7d; }
    .is-karar button.k2:hover, .is-karar button.k2.secili { background:#7e7595; color:#fff; border-color:#7e7595; }
    .is-karar button.kg { color:var(--sy-text-muted,#7e7595); }
    .is-bos { text-align:center; padding:40px; color:var(--sy-text-muted,#7e7595); }
    .is-not { font-size:12px; color:var(--sy-text-muted,#7e7595); margin-top:12px; line-height:1.6; }
    .is-ekle { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    @media (max-width: 720px) { .is-tablo .gizle { display:none; } .is-karar { justify-content:flex-start; } }
</style>

<div class="sy-page-head">
    <div>
        <h2>İsim Sözlüğü</h2>
        <div class="subtitle">Santral asistanının "Ferdi Bey" / "Ayşe Hanım" hitabı. Asistanın tanımadığı adlar buraya düşer; karar verdiğiniz an bir sonraki aramada kullanılır.</div>
    </div>
</div>

<div class="is-ozet">
    <div class="is-kutu"><div class="e">Karar bekleyen</div><div class="d">{{ number_format($bekleyen, 0, ',', '.') }}</div><div class="a">Aramada geçti, sözlükte yok</div></div>
    <div class="is-kutu"><div class="e">Panelden karar verilen</div><div class="d">{{ number_format($karar, 0, ',', '.') }}</div><div class="a">Sözlüğün önüne geçer</div></div>
    <div class="is-kutu"><div class="e">Hazır sözlük</div><div class="d">7.413+</div><div class="a">49,6 milyon kişiden (1991 öncesi) + yeni adlar</div></div>
</div>

@if($yetkili)
<div class="sy-card" style="margin-bottom:16px">
    <div class="sy-card-body">
        <form method="POST" action="/sistemyonetim/v2/isim-sozluk" class="is-ekle">
            {{ csrf_field() }}
            <input type="text" name="ad" class="sy-input" placeholder="Ad ekle / düzelt (ör. Eymen)" style="max-width:240px" required>
            <select name="cinsiyet" class="sy-input" style="max-width:160px">
                <option value="1">Erkek</option><option value="0">Kadın</option><option value="2">Hitapsız</option>
            </select>
            <button type="submit" class="sy-btn sy-btn-primary sy-btn-sm"><span class="mdi mdi-plus"></span> Kaydet</button>
            <span class="is-alt">Sözlükteki bir adı da buradan değiştirebilirsiniz; panel kararı her şeyin önüne geçer.</span>
        </form>
    </div>
</div>
@endif

<div class="sy-card">
    <div class="sy-card-body">
        <form method="GET" action="/sistemyonetim/v2/isim-sozluk" class="sy-flex-row" style="gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;justify-content:space-between">
            <div class="is-sekme">
                @foreach(['bekleyen' => 'Karar bekleyen', 'karar' => 'Karar verilen', 'hepsi' => 'Hepsi'] as $k => $v)
                    <a href="/sistemyonetim/v2/isim-sozluk?durum={{ $k }}{{ $q !== '' ? '&q=' . urlencode($q) : '' }}" class="{{ $durum === $k ? 'aktif' : '' }}">{{ $v }}</a>
                @endforeach
            </div>
            <div class="sy-flex-row" style="gap:8px">
                <input type="hidden" name="durum" value="{{ $durum }}">
                <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="Ad ara..." style="max-width:220px">
                <button type="submit" class="sy-btn sy-btn-soft sy-btn-sm"><span class="mdi mdi-magnify"></span> Ara</button>
            </div>
        </form>

        @if(count($satirlar) === 0)
            <div class="is-bos"><span class="mdi mdi-account-check" style="font-size:32px;display:block;margin-bottom:8px"></span>
                {{ $durum === 'bekleyen' ? 'Karar bekleyen ad yok. Asistan sözlükte olmayan bir adla karşılaşınca burada görünecek.' : 'Kayıt yok.' }}</div>
        @else
        <div style="overflow-x:auto">
        <table class="is-tablo">
            <thead><tr>
                <th>Ad</th>
                <th class="r">Aramada</th>
                <th class="gizle">Sistemdeki müşteriler</th>
                <th>Durum</th>
                <th class="r">{{ $yetkili ? 'Karar' : '' }}</th>
            </tr></thead>
            <tbody>
            @foreach($satirlar as $s)
                @php
                    $o = $oneriler[$s->anahtar] ?? null;
                    $t = $o ? $o['e'] + $o['k'] : 0;
                @endphp
                <tr>
                    <td><div class="is-ad">{{ $s->ad }}</div>
                        <div class="is-alt">{{ $s->son_gorulme ? 'Son: ' . \Carbon\Carbon::parse($s->son_gorulme)->format('d.m.Y H:i') : 'Elle eklendi' }}</div></td>
                    <td class="r">{{ number_format($s->gorulme, 0, ',', '.') }} kez</td>
                    <td class="gizle">
                        @if($s->cinsiyet !== null)
                            <span class="is-alt">{{ $s->karar_veren ? 'Karar: ' . $s->karar_veren : '' }}</span>
                        @elseif($t > 0)
                            {{ $o['e'] }} erkek, {{ $o['k'] }} kadın
                            <div class="is-alt">Öneri: {{ $o['e'] >= $o['k'] ? 'Erkek' : 'Kadın' }} (%{{ round(100 * max($o['e'], $o['k']) / $t) }})</div>
                        @else
                            <span class="is-alt">Cinsiyeti girilmiş müşteri yok</span>
                        @endif
                    </td>
                    <td>
                        @if($s->cinsiyet === null)<span class="is-rozet bek">Karar bekliyor</span>
                        @else<span class="is-rozet r{{ $s->cinsiyet }}">{{ $etiket[$s->cinsiyet] }}</span>@endif
                    </td>
                    <td class="r">
                        @if($yetkili)
                        <form method="POST" action="/sistemyonetim/v2/isim-sozluk" class="is-karar">
                            {{ csrf_field() }}
                            <input type="hidden" name="ad" value="{{ $s->ad }}">
                            <button type="submit" name="cinsiyet" value="1" class="k1 {{ $s->cinsiyet === 1 ? 'secili' : '' }}">Erkek</button>
                            <button type="submit" name="cinsiyet" value="0" class="k0 {{ $s->cinsiyet === 0 ? 'secili' : '' }}">Kadın</button>
                            <button type="submit" name="cinsiyet" value="2" class="k2 {{ $s->cinsiyet === 2 ? 'secili' : '' }}">Hitapsız</button>
                            @if($s->cinsiyet !== null)<button type="submit" name="cinsiyet" value="bekle" class="kg" title="Kararı geri al">Geri al</button>@endif
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif

        <div class="is-not">
            Hitap sırası: müşteri kartındaki cinsiyet → bu paneldeki karar → hazır sözlük → sistemdeki aynı adlı müşterilerin istatistiği (en az 5 kayıt, %95).
            Hiçbiri emin değilse asistan "Bey/Hanım" demez. "Hitapsız" seçilen adlarda (ör. Deniz) asistan ad soyadla hitap eder.
        </div>
    </div>
</div>
@endsection
