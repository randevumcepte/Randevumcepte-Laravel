@extends('sistemyonetim.v2.layout')

@section('content')
@php
    $tl = function ($v, $d = 0) { return number_format((float) $v, $d, ',', '.'); };
@endphp
<style>
    .wp-ozet { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:18px; }
    .wp-kutu { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:14px 16px; box-shadow:var(--sy-shadow-sm); }
    .wp-kutu .e { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; }
    .wp-kutu .d { font-size:21px; font-weight:700; margin-top:4px; color:var(--sy-text,#2a1f48); font-variant-numeric:tabular-nums; }
    .wp-kutu .a { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .wp-yesil { color:#2f9e6f !important; } .wp-kirmizi { color:#d04d5e !important; }
    .wp-tablo { width:100%; border-collapse:collapse; font-size:13px; color:var(--sy-text,#2a1f48); }
    .wp-tablo th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:600; padding:10px; border-bottom:1px solid var(--sy-border,#ece6f7); white-space:nowrap; }
    .wp-tablo td { padding:11px 10px; border-bottom:1px solid var(--sy-border,#ece6f7); vertical-align:middle; }
    .wp-tablo .r { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .wp-ad { font-weight:600; } .wp-alt { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:2px; }
    .wp-bar { height:8px; border-radius:6px; background:var(--sy-border,#ece6f7); overflow:hidden; min-width:120px; }
    .wp-bar span { display:block; height:100%; border-radius:6px; background:#2f9e6f; }
    .wp-bar.uyari span { background:#e0932f; } .wp-bar.kritik span { background:#d04d5e; }
    .wp-rozet { display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; white-space:nowrap; }
    .wp-rozet.devam { background:#e6f5ee; color:#21835a; } .wp-rozet.durdur { background:#fdecee; color:#b53a4a; }
    .wp-rozet.yok { background:var(--sy-border,#ece6f7); color:var(--sy-text-muted,#7e7595); } .wp-rozet.kapali { background:#d04d5e; color:#fff; }
    .wp-bos { text-align:center; padding:40px; color:var(--sy-text-muted,#7e7595); }
    .wp-not { font-size:12px; color:var(--sy-text-muted,#7e7595); margin-top:12px; line-height:1.6; }
    .wp-modal-bg { display:none; position:fixed; inset:0; background:rgba(30,27,46,.5); backdrop-filter:blur(2px); z-index:9999; align-items:center; justify-content:center; padding:16px; }
    .wp-modal-bg.acik { display:flex; }
    .wp-modal { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:14px; width:100%; max-width:480px; max-height:92vh; overflow:auto; padding:22px; box-shadow:0 24px 60px rgba(20,20,40,.25); color:var(--sy-text,#2a1f48); }
    .wp-modal h3 { margin:0 0 4px; font-size:17px; } .wp-modal .alt { font-size:12.5px; color:var(--sy-text-muted,#7e7595); margin-bottom:14px; }
    .wp-modal label { display:block; font-size:12.5px; font-weight:600; margin-bottom:5px; }
    .wp-satir { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
    .wp-hazir { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
    .wp-secim { display:grid; gap:8px; margin-bottom:12px; }
    .wp-secim label { display:flex; gap:10px; align-items:flex-start; font-weight:500; border:1px solid var(--sy-border,#ece6f7); border-radius:10px; padding:10px 12px; cursor:pointer; }
    .wp-secim label b { display:block; font-size:13px; } .wp-secim label span { font-size:11.5px; color:var(--sy-text-muted,#7e7595); }
    .wp-modal-alt { display:flex; justify-content:space-between; gap:10px; margin-top:18px; }
    .wp-oneri { position:relative; }
    .wp-oneri-liste { position:absolute; left:0; right:0; top:100%; background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:8px; z-index:5; max-height:200px; overflow:auto; display:none; }
    .wp-oneri-liste div { padding:8px 10px; cursor:pointer; font-size:13px; } .wp-oneri-liste div:hover { background:var(--sy-primary-soft,#f0e8fb); }
</style>

<div class="sy-page-head">
    <div>
        <h2>WhatsApp Asistan Paketleri</h2>
        <div class="subtitle">WhatsApp yapay zekâ asistanının salon bazlı aylık mesaj paketi. Yalnız yapay zekâ cevapları sayılır; fiyat, bilgi, selam ve öğrenilmiş cevaplar ücretsizdir.</div>
    </div>
    @if($yetkili)
    <div><button type="button" class="sy-btn sy-btn-primary" id="wpYeni"><span class="mdi mdi-plus"></span> Paket tanımla</button></div>
    @endif
</div>

<div class="wp-ozet">
    <div class="wp-kutu"><div class="e">Toplam cevap</div><div class="d">{{ $tl($top['toplam']) }}</div><div class="a">{{ $tl($top['toplam'] - $top['ai']) }} ücretsiz</div></div>
    <div class="wp-kutu"><div class="e">Yapay zekâ cevabı</div><div class="d">{{ $tl($top['ai']) }}</div><div class="a">paketten düşen</div></div>
    <div class="wp-kutu"><div class="e">Maliyet</div><div class="d">{{ $tl($top['maliyet_tl'], 2) }} ₺</div><div class="a">Claude (Haiku + görsel)</div></div>
    <div class="wp-kutu"><div class="e">Faturalanacak</div><div class="d">{{ $tl($top['fatura_tl']) }} ₺</div><div class="a">aylık paket ücretleri</div></div>
    <div class="wp-kutu"><div class="e">Kâr</div><div class="d {{ $top['kar_tl'] >= 0 ? 'wp-yesil' : 'wp-kirmizi' }}">{{ $tl($top['kar_tl']) }} ₺</div><div class="a">fatura − maliyet</div></div>
</div>

<div class="sy-card"><div class="sy-card-body">
    <form method="GET" action="/sistemyonetim/v2/wa-paket" class="sy-flex-row" style="gap:8px;margin-bottom:16px;flex-wrap:wrap">
        <select name="ay" class="sy-input" style="max-width:180px" onchange="this.form.submit()">
            @foreach($aylar as $k => $v)<option value="{{ $k }}" {{ $k === $ay ? 'selected' : '' }}>{{ $v }}</option>@endforeach
        </select>
        <input type="text" name="q" value="{{ $q }}" class="sy-input" placeholder="Salon ara..." style="max-width:260px">
        <button type="submit" class="sy-btn sy-btn-soft sy-btn-sm"><span class="mdi mdi-magnify"></span> Ara</button>
    </form>

    @if(count($satirlar) === 0)
        <div class="wp-bos"><span class="mdi mdi-whatsapp" style="font-size:32px;display:block;margin-bottom:8px"></span>Bu dönemde WhatsApp asistanı kullanımı veya tanımlı paket yok.</div>
    @else
    <div style="overflow-x:auto">
    <table class="wp-tablo">
        <thead><tr><th>Salon</th><th>Paket</th><th>Kullanım (AI)</th><th class="r">Kalan</th><th class="r">Toplam cevap</th><th class="r">Maliyet</th><th class="r">Fatura</th><th class="r">Kâr</th><th>Bitince</th><th></th></tr></thead>
        <tbody>
        @foreach($satirlar as $s)
            @php $p = $s['paket']; $bar = $s['yuzde'] >= 100 ? 'kritik' : ($s['yuzde'] >= 80 ? 'uyari' : ''); @endphp
            <tr>
                <td><div class="wp-ad">{{ $s['salon_adi'] }}</div><div class="wp-alt">#{{ $s['salon_id'] }}</div></td>
                <td>
                    @if($p)
                        <div class="wp-ad">{{ $p->paket_adi ?: 'Özel' }}</div>
                        <div class="wp-alt">{{ $tl($p->dahil_mesaj) }} mesaj · {{ $tl($p->aylik_ucret) }} ₺</div>
                    @elseif($s['env'])
                        <span class="wp-rozet yok">Paketsiz · sınırsız (sunucu ayarı)</span>
                    @else
                        <span class="wp-rozet kapali">Paket yok · asistan kapalı</span>
                    @endif
                </td>
                <td>
                    @if($p)
                        <div class="wp-bar {{ $bar }}"><span style="width:{{ $s['yuzde'] }}%"></span></div>
                        <div class="wp-alt">{{ $tl($s['ai']) }} / {{ $tl($s['dahil']) }} · %{{ $s['yuzde'] }}</div>
                    @else <div class="wp-alt">{{ $tl($s['ai']) }}</div> @endif
                </td>
                <td class="r">{!! $p ? '<span class="' . ($s['kalan'] > 0 ? 'wp-yesil' : 'wp-kirmizi') . '" style="font-weight:600">' . e($tl($s['kalan'])) . '</span>' : '—' !!}</td>
                <td class="r">{{ $tl($s['toplam']) }}<div class="wp-alt">{{ $tl($s['toplam'] - $s['ai']) }} ücretsiz</div></td>
                <td class="r">{{ $tl($s['maliyet_tl'], 2) }} ₺<div class="wp-alt">{{ $s['ai'] ? $tl($s['maliyet_tl'] / $s['ai'], 3) . ' ₺/AI cevap' : '' }}</div></td>
                <td class="r">{{ $p ? $tl($s['fatura_tl']) . ' ₺' : '—' }}</td>
                <td class="r {{ $s['kar_tl'] >= 0 ? 'wp-yesil' : 'wp-kirmizi' }}">{{ $p ? $tl($s['kar_tl']) . ' ₺' : '—' }}</td>
                <td>
                    @if(!$p) —
                    @elseif($buAy && $s['kapali']) <span class="wp-rozet kapali">Asistan durdu</span>
                    @elseif($p->asim_davranis === 'durdur') <span class="wp-rozet durdur">Durdur</span>
                    @else <span class="wp-rozet devam">Devam</span> @endif
                </td>
                <td class="r">
                    @if($yetkili)
                    <button type="button" class="sy-btn sy-btn-soft sy-btn-sm wp-duzenle" data-id="{{ $s['salon_id'] }}" data-ad="{{ $s['salon_adi'] }}"
                        data-paket="{{ $p ? json_encode(['ad' => $p->paket_adi, 'mesaj' => (int) $p->dahil_mesaj, 'ucret' => (float) $p->aylik_ucret, 'davranis' => $p->asim_davranis, 'not' => $p->notlar]) : '' }}">
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
    <div class="wp-not">
        <b>Sayım:</b> Paketten yalnız yapay zekânın (Claude) ürettiği cevaplar düşer: randevu işlemleri, tanınmayan sorular ve yeni fotoğraf analizi. Fiyat, hizmet bilgisi, selam/teşekkür, öğrenilmiş cevaplar, aynı fotoğrafın tekrarı ve kaçan müşteriye geri kazanma mesajları ücretsizdir. Dönem takvim ayıdır, kullanılmayan hak devretmez.
        <b>Bitince durdur:</b> asistan o hatta ay sonuna kadar cevap vermez; %80 ve %100'de işletmeye bildirim gider.
        <b>Paket tanımlanan salonda WhatsApp asistanı açılır</b> (sunucudaki WA_ASISTAN_SALONLAR listesine gerek kalmaz). Maliyet güncel kurla ({{ $tl($kur, 4) }} ₺) TL'ye çevrilir.
    </div>
</div></div>

@if($yetkili)
<div class="wp-modal-bg" id="wpBg">
    <form class="wp-modal" method="POST" action="/sistemyonetim/v2/wa-paket" id="wpForm">
        {{ csrf_field() }}
        <input type="hidden" name="salon_id" id="wpSalonId">
        <h3>WhatsApp Asistan Paketi</h3>
        <div class="alt" id="wpSalonAd">—</div>
        <div class="wp-oneri" id="wpSalonSec" style="margin-bottom:14px;display:none">
            <label>Salon</label>
            <input type="text" class="sy-input" id="wpSalonAra" placeholder="Salon adı ya da numarası (ör. 20)" autocomplete="off">
            <div class="wp-oneri-liste" id="wpOneri"></div>
        </div>
        <label>Hazır paketler</label>
        <div class="wp-hazir">
            @foreach($hazir as $h)
                <button type="button" class="sy-btn sy-btn-soft sy-btn-sm wp-hazir-btn" data-h="{{ json_encode($h) }}">{{ $h['ad'] }} · {{ $tl($h['mesaj']) }} mesaj · {{ $tl($h['ucret']) }} ₺</button>
            @endforeach
        </div>
        <div class="wp-satir">
            <div><label>Paket adı</label><input type="text" name="paket_adi" id="wpAd" class="sy-input" maxlength="60" placeholder="Örn. Standart"></div>
            <div><label>Aylık AI mesaj hakkı</label><input type="number" name="dahil_mesaj" id="wpMesaj" class="sy-input" min="1" step="1" required></div>
        </div>
        <label>Aylık ücret (₺)</label>
        <input type="number" name="aylik_ucret" id="wpUcret" class="sy-input" min="0" step="0.01" required>
        <div style="height:12px"></div>
        <label>Paket bitince</label>
        <div class="wp-secim">
            <label><input type="radio" name="asim_davranis" value="durdur" id="wpDurdur"><div><b>Asistan dursun</b><span>Ay sonuna kadar cevap vermez; ay başında otomatik açılır.</span></div></label>
            <label><input type="radio" name="asim_davranis" value="devam" id="wpDevam"><div><b>Asistan devam etsin</b><span>Aşım ayrıca faturalanır (panelde aşım adedi görünür).</span></div></label>
        </div>
        <label>Not</label>
        <textarea name="notlar" id="wpNot" class="sy-input" rows="2"></textarea>
        <div class="wp-modal-alt">
            <button type="button" class="sy-btn sy-btn-soft" id="wpSil" style="color:#d04d5e">Paketi kaldır</button>
            <div style="display:flex;gap:10px">
                <button type="button" class="sy-btn sy-btn-soft" id="wpIptal">Vazgeç</button>
                <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
            </div>
        </div>
    </form>
</div>
<form method="POST" id="wpSilForm" style="display:none">{{ csrf_field() }}</form>
<script>
(function(){
    var bg = document.getElementById('wpBg'), g = function(id){ return document.getElementById(id); };
    function doldur(p){
        g('wpAd').value = p.ad || ''; g('wpMesaj').value = p.mesaj != null ? p.mesaj : ''; g('wpUcret').value = p.ucret != null ? p.ucret : '';
        if (p.davranis !== undefined) g(p.davranis === 'devam' ? 'wpDevam' : 'wpDurdur').checked = true;
        if (p.not !== undefined) g('wpNot').value = p.not || '';
    }
    function ac(id, ad, p){
        g('wpSalonId').value = id || '';
        g('wpSalonAd').textContent = id ? (ad + ' (#' + id + ')') : 'Salon seçin';
        g('wpSalonSec').style.display = id ? 'none' : 'block';
        g('wpSalonAra').value = '';
        g('wpSil').style.display = p ? '' : 'none';
        doldur(p || { ad: '', mesaj: '', ucret: '', davranis: 'durdur', not: '' });
        bg.classList.add('acik');
    }
    function kapat(){ bg.classList.remove('acik'); }
    document.querySelectorAll('.wp-duzenle').forEach(function(b){ b.addEventListener('click', function(){ ac(b.dataset.id, b.dataset.ad, b.dataset.paket ? JSON.parse(b.dataset.paket) : null); }); });
    g('wpYeni').addEventListener('click', function(){ ac('', '', null); g('wpSalonAra').focus(); });
    document.querySelectorAll('.wp-hazir-btn').forEach(function(b){ b.addEventListener('click', function(){ doldur(JSON.parse(b.dataset.h)); }); });
    g('wpIptal').addEventListener('click', kapat);
    bg.addEventListener('click', function(e){ if (e.target === bg) kapat(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') kapat(); });
    g('wpForm').addEventListener('submit', function(e){
        // Listeden secilmediyse: yazilan salon numarasi dogrudan kullanilir (ör. "20" ya da "#20")
        var yazi = g('wpSalonAra').value.trim().replace(/^#/, '');
        if (!g('wpSalonId').value && /^\d+$/.test(yazi)) g('wpSalonId').value = yazi;
        if (!g('wpSalonId').value) { e.preventDefault(); alert('Salonu listeden seçin ya da salon numarasını yazın (ör. 20).'); }
    });
    g('wpSil').addEventListener('click', function(){
        var id = g('wpSalonId').value;
        if (!id || !confirm('Bu salonun WhatsApp asistan paketi kaldırılsın mı? Salon sunucu listesinde değilse asistan bu hatta kapanır.')) return;
        var f = g('wpSilForm'); f.action = '/sistemyonetim/v2/wa-paket/' + id + '/sil'; f.submit();
    });
    var t = null, liste = g('wpOneri');
    g('wpSalonAra').addEventListener('input', function(){
        var q = this.value.trim(); clearTimeout(t);
        if (q.length < 2) { liste.style.display = 'none'; return; }
        t = setTimeout(function(){
            fetch('/sistemyonetim/v2/api/salon-ara?q=' + encodeURIComponent(q), { credentials: 'same-origin' }).then(function(r){ return r.json(); }).then(function(d){
                liste.innerHTML = '';
                (d || []).forEach(function(s){
                    var el = document.createElement('div');
                    el.textContent = s.salon_adi + ' (#' + s.id + ')';
                    el.addEventListener('click', function(){ g('wpSalonId').value = s.id; g('wpSalonAd').textContent = s.salon_adi + ' (#' + s.id + ')'; g('wpSalonAra').value = s.salon_adi; liste.style.display = 'none'; });
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
