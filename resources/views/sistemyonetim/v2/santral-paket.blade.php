@extends('sistemyonetim.v2.layout')

@section('content')
@php
    $tl = function ($v, $d = 0) { return number_format((float) $v, $d, ',', '.'); };
@endphp
<style>
    .sp-ozet { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:18px; }
    .sp-kutu { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:14px 16px; box-shadow:var(--sy-shadow-sm); }
    .sp-kutu .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; }
    .sp-kutu .d { font-size:21px; font-weight:700; margin-top:4px; color:var(--sy-text,#2a1f48); font-variant-numeric:tabular-nums; }
    .sp-kutu .a { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .sp-yesil { color:#2f9e6f !important; } .sp-kirmizi { color:#d04d5e !important; }
    .sp-tablo { width:100%; border-collapse:collapse; font-size:13px; color:var(--sy-text,#2a1f48); }
    .sp-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); white-space:nowrap; }
    .sp-tablo td { padding:11px 10px; border-bottom:1px solid var(--sy-border,#ece6f7); vertical-align:middle; }
    .sp-tablo tr:hover td { background:var(--sy-primary-soft,#f0e8fb); }
    .sp-tablo .r { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .sp-ad { font-weight:600; } .sp-alt { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .sp-bar { height:8px; border-radius:6px; background:var(--sy-border,#ece6f7); overflow:hidden; min-width:120px; }
    .sp-bar span { display:block; height:100%; border-radius:6px; background:#2f9e6f; }
    .sp-bar.uyari span { background:#e0932f; } .sp-bar.kritik span { background:#d04d5e; }
    .sp-rozet { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; white-space:nowrap; }
    .sp-rozet.devam { background:#e6f5ee; color:#21835a; } .sp-rozet.durdur { background:#fdecee; color:#b53a4a; }
    .sp-rozet.yok { background:var(--sy-border,#ece6f7); color:var(--sy-text-muted,#7e7595); }
    .sp-rozet.kapali { background:#d04d5e; color:#fff; }
    .sp-bos { text-align:center; padding:40px; color:var(--sy-text-muted,#7e7595); }
    .sp-not { font-size:12px; color:var(--sy-text-muted,#7e7595); margin-top:12px; line-height:1.6; }

    .sp-modal-bg { display:none; position:fixed; inset:0; background:rgba(30,27,46,.5); backdrop-filter:blur(2px); z-index:9999; align-items:center; justify-content:center; padding:16px; }
    .sp-modal-bg.acik { display:flex; }
    .sp-modal { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:14px; width:100%; max-width:480px; max-height:92vh; overflow:auto; padding:22px; box-shadow:0 24px 60px rgba(20,20,40,.25); color:var(--sy-text,#2a1f48); }
    .sp-modal h3 { margin:0 0 4px; font-size:17px; }
    .sp-modal .alt { font-size:12.5px; color:var(--sy-text-muted,#7e7595); margin-bottom:14px; }
    .sp-modal label { display:block; font-size:12.5px; font-weight:600; margin-bottom:5px; }
    .sp-modal .ac { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:4px; }
    .sp-satir { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
    .sp-hazir { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
    .sp-secim { display:grid; gap:8px; margin-bottom:12px; }
    .sp-secim label { display:flex; gap:10px; align-items:flex-start; font-weight:500; border:1px solid var(--sy-border,#ece6f7); border-radius:10px; padding:10px 12px; cursor:pointer; }
    .sp-secim label b { display:block; font-size:13px; }
    .sp-secim label span { font-size:11.5px; color:var(--sy-text-muted,#7e7595); }
    .sp-modal-alt { display:flex; justify-content:space-between; gap:10px; margin-top:18px; }
    .sp-oneri { position:relative; }
    .sp-oneri-liste { position:absolute; left:0; right:0; top:100%; background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:8px; z-index:5; max-height:200px; overflow:auto; display:none; }
    .sp-oneri-liste div { padding:8px 10px; cursor:pointer; font-size:13px; } .sp-oneri-liste div:hover { background:var(--sy-primary-soft,#f0e8fb); }
</style>

<div class="sy-page-head">
    <div>
        <h2>Santral Asistan Paketleri</h2>
        <div class="subtitle">Yapay zeka asistanının salon bazlı aylık dakika paketi, kullanım, maliyet ve aşım ayarı</div>
    </div>
    @if($yetkili)
    <div><button type="button" class="sy-btn sy-btn-primary" id="spYeni"><span class="mdi mdi-plus"></span> Paket tanımla</button></div>
    @endif
</div>

<div class="sp-ozet">
    <div class="sp-kutu"><div class="e">Gelen</div><div class="d">{{ $tl($top['gelen_dk'], 1) }} dk</div><div class="a">{{ $top['gelen_adet'] }} görüşme</div></div>
    <div class="sp-kutu"><div class="e">Giden (hatırlatma)</div><div class="d">{{ $tl($top['giden_dk'], 1) }} dk</div><div class="a">{{ $top['giden_adet'] }} görüşme</div></div>
    <div class="sp-kutu"><div class="e">Token (girdi / çıktı)</div><div class="d">{{ $tl($top['girdi_token'] / 1000) }}K / {{ $tl($top['cikti_token'] / 1000) }}K</div><div class="a">Claude</div></div>
    <div class="sp-kutu"><div class="e">Maliyet</div><div class="d">{{ $tl($top['maliyet_tl']) }} ₺</div><div class="a">Claude + STT + TTS (tahmini)</div></div>
    <div class="sp-kutu"><div class="e">Faturalanacak</div><div class="d">{{ $tl($top['fatura_tl']) }} ₺</div><div class="a">paket + aşım</div></div>
    <div class="sp-kutu"><div class="e">Kâr</div><div class="d {{ $top['kar_tl'] >= 0 ? 'sp-yesil' : 'sp-kirmizi' }}">{{ $tl($top['kar_tl']) }} ₺</div><div class="a">fatura − maliyet</div></div>
</div>

<div class="sy-card">
    <div class="sy-card-body">
        <form method="GET" action="/sistemyonetim/v2/santral-paket" class="sy-flex-row" style="gap:8px;margin-bottom:16px;flex-wrap:wrap">
            <select name="ay" class="sy-input" style="max-width:180px" onchange="this.form.submit()">
                @foreach($aylar as $k => $v)<option value="{{ $k }}" {{ $k === $ay ? 'selected' : '' }}>{{ $v }}</option>@endforeach
            </select>
            <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="Salon ara..." style="max-width:260px">
            <button type="submit" class="sy-btn sy-btn-soft sy-btn-sm"><span class="mdi mdi-magnify"></span> Ara</button>
        </form>

        @if(count($satirlar) === 0)
            <div class="sp-bos"><span class="mdi mdi-headset-off" style="font-size:32px;display:block;margin-bottom:8px"></span>Bu dönemde asistan görüşmesi veya tanımlı paket yok.</div>
        @else
        <div style="overflow-x:auto">
        <table class="sp-tablo">
            <thead><tr>
                <th>Salon</th><th>Paket</th>
                <th class="r">Gelen</th><th class="r">Giden</th>
                <th>Kullanım</th><th class="r">Kalan</th>
                <th class="r">Token</th><th class="r">Maliyet</th><th class="r">Fatura</th><th class="r">Kâr</th>
                <th>Aşımda</th><th></th>
            </tr></thead>
            <tbody>
            @foreach($satirlar as $s)
                @php $p = $s['paket']; $bar = $s['yuzde'] >= 100 ? 'kritik' : ($s['yuzde'] >= 80 ? 'uyari' : ''); @endphp
                <tr>
                    <td><div class="sp-ad">{{ $s['salon_adi'] }}</div><div class="sp-alt">#{{ $s['salon_id'] }}</div></td>
                    <td>
                        @if($p)
                            <div class="sp-ad">{{ $p->paket_adi ?: 'Özel' }}</div>
                            <div class="sp-alt">{{ $tl($p->dahil_dakika) }} dk · {{ $tl($p->aylik_ucret) }} ₺ · aşım {{ $tl($p->asim_dk_ucret, 2) }} ₺/dk</div>
                        @else
                            <span class="sp-rozet yok">Paket yok (sınırsız)</span>
                        @endif
                    </td>
                    <td class="r">{{ $tl($s['gelen_dk'], 1) }} dk<div class="sp-alt">{{ $s['gelen_adet'] }} görüşme</div></td>
                    <td class="r">{{ $tl($s['giden_dk'], 1) }} dk<div class="sp-alt">{{ $s['giden_adet'] }} görüşme</div></td>
                    <td>
                        @if($p)
                            <div class="sp-bar {{ $bar }}"><span style="width:{{ $s['yuzde'] }}%"></span></div>
                            <div class="sp-alt">{{ $tl($s['toplam_dk'], 1) }} / {{ $tl($s['dahil_dk']) }} dk · %{{ $s['yuzde'] }}</div>
                        @else
                            <div class="sp-alt">{{ $tl($s['toplam_dk'], 1) }} dk</div>
                        @endif
                    </td>
                    <td class="r">
                        @if($p)
                            <span class="{{ $s['kalan_dk'] > 0 ? 'sp-yesil' : 'sp-kirmizi' }}" style="font-weight:600">{{ $tl(max(0, $s['kalan_dk']), 1) }} dk</span>
                            @if($s['asim_dk'] > 0)<div class="sp-alt sp-kirmizi">+{{ $s['asim_dk'] }} dk aşım</div>@endif
                        @else — @endif
                    </td>
                    <td class="r">{{ $tl($s['girdi_token'] / 1000, 1) }}K<div class="sp-alt">{{ $tl($s['cikti_token'] / 1000, 1) }}K çıktı</div></td>
                    <td class="r" title="Claude {{ $tl($s['claude_tl'], 2) }} ₺ · STT {{ $tl($s['stt_tl'], 2) }} ₺ · TTS {{ $tl($s['tts_tl'], 2) }} ₺">{{ $tl($s['maliyet_tl'], 2) }} ₺<div class="sp-alt">Claude {{ $tl($s['claude_tl'], 2) }} ₺</div></td>
                    <td class="r">{{ $p ? $tl($s['fatura_tl']) . ' ₺' : '—' }}</td>
                    <td class="r {{ $s['kar_tl'] >= 0 ? 'sp-yesil' : 'sp-kirmizi' }}">{{ $p ? $tl($s['kar_tl']) . ' ₺' : '—' }}</td>
                    <td>
                        @if(!$p) <span class="sp-rozet yok">—</span>
                        @elseif($buAy && $s['kapali']) <span class="sp-rozet kapali">Asistan durdu</span>
                        @elseif($p->asim_davranis === 'durdur') <span class="sp-rozet durdur">Durdur</span>
                        @else <span class="sp-rozet devam">Devam (ücretli)</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($yetkili)
                        <button type="button" class="sy-btn sy-btn-soft sy-btn-sm sp-duzenle"
                            data-id="{{ $s['salon_id'] }}" data-ad="{{ $s['salon_adi'] }}"
                            data-paket="{{ $p ? json_encode(['ad' => $p->paket_adi, 'dk' => (int) $p->dahil_dakika, 'ucret' => (float) $p->aylik_ucret, 'asim' => (float) $p->asim_dk_ucret, 'davranis' => $p->asim_davranis, 'not' => $p->notlar]) : '' }}">
                            <span class="mdi mdi-pencil"></span> {{ $p ? 'Düzenle' : 'Paket tanımla' }}
                        </button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif

        <div class="sp-not">
            <b>Nasıl hesaplanır:</b> Dakika, asistanın hatta kaldığı süredir (operatöre aktarıldıktan sonraki konuşma dahil değil); dönem takvim ayıdır.
            Aşım tam dakikaya yuvarlanır. Maliyet = Claude (gerçek token) + Google STT (dakika × {{ config('services.santral_ai.stt_usd_dk') }} $) + Wavenet TTS (karakter, tahmini), kur {{ $tl($kur, 2) }} ₺ (AI Kredi sayfasından).
            <b>Aşımda durdur:</b> paket bitince gelen çağrılar asistan yerine operatöre aktarılır, hatırlatmalar klasik tuşlamalı sesli mesajla yapılır.
            Token/süre kaydı bu güncellemeden itibaren tutulur; önceki görüşmelerde token 0 görünür.
        </div>
    </div>
</div>

@if($yetkili)
<div class="sp-modal-bg" id="spBg">
    <form class="sp-modal" method="POST" action="/sistemyonetim/v2/santral-paket" id="spForm">
        {{ csrf_field() }}
        <input type="hidden" name="salon_id" id="spSalonId">
        <h3>Asistan Paketi</h3>
        <div class="alt" id="spSalonAd">—</div>

        <div class="sp-oneri" id="spSalonSec" style="margin-bottom:14px;display:none">
            <label>Salon</label>
            <input type="text" class="sy-input" id="spSalonAra" placeholder="Salon adı yazın..." autocomplete="off">
            <div class="sp-oneri-liste" id="spOneri"></div>
        </div>

        <label>Hazır paketler</label>
        <div class="sp-hazir">
            @foreach($hazir as $h)
                <button type="button" class="sy-btn sy-btn-soft sy-btn-sm sp-hazir-btn" data-h="{{ json_encode($h) }}">{{ $h['ad'] }} · {{ $h['dk'] }} dk · {{ $tl($h['ucret']) }} ₺</button>
            @endforeach
        </div>

        <div class="sp-satir">
            <div><label>Paket adı</label><input type="text" name="paket_adi" id="spAd" class="sy-input" maxlength="60" placeholder="Örn. Standart"></div>
            <div><label>Dahil dakika / ay</label><input type="number" name="dahil_dakika" id="spDk" class="sy-input" min="0" step="1" required></div>
        </div>
        <div class="sp-satir">
            <div><label>Aylık ücret (₺)</label><input type="number" name="aylik_ucret" id="spUcret" class="sy-input" min="0" step="0.01" required></div>
            <div><label>Aşım ücreti (₺ / dk)</label><input type="number" name="asim_dk_ucret" id="spAsim" class="sy-input" min="0" step="0.01" required></div>
        </div>

        <label>Paket bitince</label>
        <div class="sp-secim">
            <label><input type="radio" name="asim_davranis" value="devam" id="spDevam"><div><b>Asistan çalışmaya devam etsin</b><span>Aşan dakikalar aşım ücretiyle faturalanır.</span></div></label>
            <label><input type="radio" name="asim_davranis" value="durdur" id="spDurdur"><div><b>Asistan dursun</b><span>Gelen çağrılar operatöre aktarılır, hatırlatmalar klasik tuşlamalı mesajla yapılır. Ay başında otomatik açılır.</span></div></label>
        </div>

        <label>Not</label>
        <textarea name="notlar" id="spNot" class="sy-input" rows="2"></textarea>

        <div class="sp-modal-alt">
            <button type="button" class="sy-btn sy-btn-soft" id="spSil" style="color:#d04d5e">Paketi kaldır</button>
            <div style="display:flex;gap:10px">
                <button type="button" class="sy-btn sy-btn-soft" id="spIptal">Vazgeç</button>
                <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
            </div>
        </div>
    </form>
</div>
<form method="POST" id="spSilForm" style="display:none">{{ csrf_field() }}</form>

<script>
(function(){
    var bg = document.getElementById('spBg'), $ = function(id){ return document.getElementById(id); };
    function doldur(p){
        $('spAd').value = p.ad || ''; $('spDk').value = p.dk != null ? p.dk : '';
        $('spUcret').value = p.ucret != null ? p.ucret : ''; $('spAsim').value = p.asim != null ? p.asim : '';
        if (p.davranis !== undefined) { $(p.davranis === 'durdur' ? 'spDurdur' : 'spDevam').checked = true; }
        if (p.not !== undefined) $('spNot').value = p.not || '';
    }
    function ac(id, ad, p){
        $('spSalonId').value = id || '';
        $('spSalonAd').textContent = id ? (ad + ' (#' + id + ')') : 'Salon seçin';
        $('spSalonSec').style.display = id ? 'none' : 'block';
        $('spSalonAra').value = '';
        $('spSil').style.display = p ? '' : 'none';
        doldur(p || { ad: '', dk: '', ucret: '', asim: '', davranis: 'devam', not: '' });
        bg.classList.add('acik');
    }
    function kapat(){ bg.classList.remove('acik'); }
    document.querySelectorAll('.sp-duzenle').forEach(function(b){
        b.addEventListener('click', function(){ ac(b.dataset.id, b.dataset.ad, b.dataset.paket ? JSON.parse(b.dataset.paket) : null); });
    });
    $('spYeni').addEventListener('click', function(){ ac('', '', null); $('spSalonAra').focus(); });
    document.querySelectorAll('.sp-hazir-btn').forEach(function(b){
        b.addEventListener('click', function(){ doldur(JSON.parse(b.dataset.h)); });
    });
    $('spIptal').addEventListener('click', kapat);
    bg.addEventListener('click', function(e){ if (e.target === bg) kapat(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') kapat(); });
    $('spForm').addEventListener('submit', function(e){
        if (!$('spSalonId').value) { e.preventDefault(); alert('Lütfen bir salon seçin.'); }
    });
    $('spSil').addEventListener('click', function(){
        var id = $('spSalonId').value;
        if (!id || !confirm('Bu salonun asistan paketi kaldırılsın mı? Asistan sınırsız çalışmaya devam eder.')) return;
        var f = $('spSilForm'); f.action = '/sistemyonetim/v2/santral-paket/' + id + '/sil'; f.submit();
    });
    // Salon arama (yeni paket)
    var t = null, liste = $('spOneri');
    $('spSalonAra').addEventListener('input', function(){
        var q = this.value.trim(); clearTimeout(t);
        if (q.length < 2) { liste.style.display = 'none'; return; }
        t = setTimeout(function(){
            fetch('/sistemyonetim/v2/api/salon-ara?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    liste.innerHTML = '';
                    (d || []).forEach(function(s){
                        var el = document.createElement('div');
                        el.textContent = s.salon_adi + ' (#' + s.id + ')';
                        el.addEventListener('click', function(){
                            $('spSalonId').value = s.id; $('spSalonAd').textContent = s.salon_adi + ' (#' + s.id + ')';
                            $('spSalonAra').value = s.salon_adi; liste.style.display = 'none';
                        });
                        liste.appendChild(el);
                    });
                    liste.style.display = d && d.length ? 'block' : 'none';
                });
        }, 250);
    });
})();
</script>
@endif

@endsection
