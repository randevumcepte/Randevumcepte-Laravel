@extends('sistemyonetim.v2.layout')

@section('content')
<style>
    .ss-izgara { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:16px; align-items:start; }
    @media (max-width: 1100px) { .ss-izgara { grid-template-columns:1fr; } }
    .ss-kutu { background:var(--sy-surface,#fff); border:1px solid var(--sy-border,#ece6f7); border-radius:12px; padding:16px; box-shadow:var(--sy-shadow-sm); margin-bottom:16px; color:var(--sy-text,#2a1f48); }
    .ss-kutu h4 { margin:0 0 10px; font-size:13px; text-transform:uppercase; letter-spacing:.4px; color:var(--sy-text-muted,#7e7595); font-weight:700; }
    .ss-kutu textarea { width:100%; min-height:86px; border:1px solid var(--sy-border,#ece6f7); border-radius:10px; padding:10px 12px; font:inherit; font-size:14px; resize:vertical; background:var(--sy-surface,#fff); color:inherit; }
    .ss-alt { font-size:11.5px; color:var(--sy-text-muted,#7e7595); margin-top:4px; display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .ss-cipler { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
    .ss-cip { border:1px solid var(--sy-border,#ece6f7); background:var(--sy-surface,#fff); color:inherit; border-radius:16px; padding:4px 10px; font-size:12px; cursor:pointer; max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .ss-cip:hover, .ss-cip.aktif { background:var(--sy-primary-soft,#f0e8fb); border-color:var(--sy-primary,#7c4dcc); }
    .ss-ayar { display:flex; gap:14px; flex-wrap:wrap; align-items:center; margin-top:12px; font-size:13px; }
    .ss-ayar label { display:flex; align-items:center; gap:6px; cursor:pointer; }
    .ss-ayar select, .ss-ayar input[type=range] { font:inherit; }
    .ss-ayar select { border:1px solid var(--sy-border,#ece6f7); border-radius:8px; padding:5px 8px; background:var(--sy-surface,#fff); color:inherit; }
    .ss-sekme { display:flex; gap:6px; margin-bottom:12px; }
    .ss-sekme button { border:1px solid var(--sy-border,#ece6f7); background:var(--sy-surface,#fff); color:inherit; border-radius:8px; padding:7px 14px; cursor:pointer; font-weight:600; font-size:13px; }
    .ss-sekme button.aktif { background:var(--sy-primary,#7c4dcc); color:#fff; border-color:var(--sy-primary,#7c4dcc); }
    .ss-filtre { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
    .ss-filtre input, .ss-filtre select { border:1px solid var(--sy-border,#ece6f7); border-radius:8px; padding:6px 10px; font:inherit; font-size:13px; background:var(--sy-surface,#fff); color:inherit; }
    .ss-liste { max-height:520px; overflow:auto; border:1px solid var(--sy-border,#ece6f7); border-radius:10px; }
    .ss-satir { display:flex; align-items:center; gap:10px; padding:8px 12px; border-bottom:1px solid var(--sy-border,#ece6f7); }
    .ss-satir:last-child { border-bottom:0; }
    .ss-satir:hover { background:var(--sy-primary-soft,#f0e8fb); }
    .ss-oynat { width:34px; height:34px; border-radius:50%; border:0; background:var(--sy-primary,#7c4dcc); color:#fff; cursor:pointer; flex:none; font-size:18px; display:flex; align-items:center; justify-content:center; }
    .ss-oynat:disabled { opacity:.5; cursor:wait; }
    .ss-ad { font-weight:600; font-size:13.5px; }
    .ss-bilgi { font-size:11.5px; color:var(--sy-text-muted,#7e7595); }
    .ss-rozet { font-size:10.5px; font-weight:700; padding:2px 7px; border-radius:10px; background:var(--sy-border,#ece6f7); color:var(--sy-text-muted,#7e7595); white-space:nowrap; }
    .ss-rozet.hd { background:#e6f5ee; color:#21835a; } .ss-rozet.kut { background:#fff4e2; color:#a8670f; }
    .ss-hazir button { display:flex; width:100%; align-items:center; gap:10px; text-align:left; border:1px solid var(--sy-border,#ece6f7); background:var(--sy-surface,#fff); color:inherit; border-radius:10px; padding:9px 10px; margin-bottom:8px; cursor:pointer; font-size:13px; }
    .ss-hazir button:hover { background:var(--sy-primary-soft,#f0e8fb); }
    .ss-hazir .mdi { font-size:20px; color:var(--sy-primary,#7c4dcc); }
    .ss-gecmis-ogesi { padding:8px 0; border-bottom:1px solid var(--sy-border,#ece6f7); font-size:12.5px; }
    .ss-gecmis-ogesi:last-child { border-bottom:0; }
    .ss-gecmis-ogesi audio { width:100%; height:32px; margin-top:4px; }
    .ss-uyari { background:#fff4e2; color:#8a560c; border-radius:10px; padding:10px 12px; font-size:12.5px; margin-bottom:12px; }
    .ss-hata { color:#d04d5e; font-size:12.5px; margin-top:8px; min-height:16px; }
    .ss-bos { padding:30px; text-align:center; color:var(--sy-text-muted,#7e7595); font-size:13px; }
</style>

<div class="sy-page-head">
    <div>
        <h2>Santral Ses Önizleme</h2>
        <div class="subtitle">Google ve ElevenLabs seslerini aynı cümleyle dinleyip karşılaştırın. "Telefon kalitesi" açıkken ses, müşterinin telefonda duyduğu biçimle (8 kHz) birebir üretilir.</div>
    </div>
</div>

@if(!$googleVar || !$elVar)
<div class="ss-uyari">
    @if(!$googleVar) Google sesleri için Laravel <code>.env</code> dosyasında <code>GOOGLE_TTS_API_KEY</code> gerekli. @endif
    @if(!$elVar) ElevenLabs sesleri için Laravel <code>.env</code> dosyasına <code>ELEVENLABS_API_KEY=...</code> ekleyin (santral sunucusundakiyle aynı anahtar). @endif
</div>
@endif
@if(!$yetkili)
<div class="ss-uyari">Ses üretmek ücretli olduğu için yalnız süper yönetici ve yönetici rolü dinleyebilir.</div>
@endif

<div class="ss-izgara">
    <div>
        <div class="ss-kutu">
            <h4>Metin</h4>
            <textarea id="ssMetin" maxlength="{{ $maks }}">{{ $ornekler[1] }}</textarea>
            <div class="ss-alt"><span>Örnek cümleler:</span><span id="ssSayac"></span></div>
            <div class="ss-cipler">
                @foreach($ornekler as $o)
                    <button type="button" class="ss-cip" data-metin="{{ $o }}" title="{{ $o }}">{{ \Illuminate\Support\Str::limit($o, 48) }}</button>
                @endforeach
            </div>
            <div class="ss-ayar">
                <label><input type="checkbox" id="ssTelefon" checked> Telefon kalitesi (8 kHz, santralle aynı)</label>
                <label>Hız <input type="range" id="ssHiz" min="0.8" max="1.15" step="0.05" value="1"> <span id="ssHizD">1.00</span></label>
            </div>
        </div>

        <div class="ss-kutu">
            <div class="ss-sekme">
                <button type="button" data-s="elevenlabs" class="aktif">ElevenLabs</button>
                <button type="button" data-s="google">Google</button>
            </div>
            <div class="ss-ayar" id="ssElAyar" style="margin-top:0;margin-bottom:12px">
                <label>Model
                    <select id="ssModel">
                        @foreach($elModeller as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </label>
                <label>Stabilite
                    <select id="ssStab">
                        <option value="0">0.0 — yaratıcı / en doğal</option>
                        <option value="0.5" selected>0.5 — dengeli</option>
                        <option value="1">1.0 — tutarlı</option>
                    </select>
                </label>
            </div>
            <div class="ss-filtre">
                <input type="search" id="ssAra" placeholder="Ses ara…">
                <select id="ssCins"><option value="">Tüm cinsiyetler</option><option value="kadın">Kadın</option><option value="erkek">Erkek</option></select>
                <select id="ssTur"><option value="">Tüm türler</option></select>
            </div>
            <div class="ss-liste" id="ssListe"><div class="ss-bos">Yükleniyor…</div></div>
            <div class="ss-hata" id="ssHata"></div>
        </div>
    </div>

    <div>
        <div class="ss-kutu ss-hazir">
            <h4>Hızlı karşılaştırma</h4>
            @foreach($hazir as $h)
                <button type="button" class="ss-hazir-btn" data-h='@json($h)'><span class="mdi mdi-play-circle"></span><span>{{ $h['ad'] }}</span></button>
            @endforeach
        </div>
        <div class="ss-kutu">
            <h4>Dinlenenler</h4>
            <div id="ssGecmis"><div class="ss-bos" style="padding:14px">Henüz ses dinlenmedi.</div></div>
        </div>
    </div>
</div>

<script>
(function () {
    var csrf = '{{ csrf_token() }}';
    var yetkili = {{ $yetkili ? 'true' : 'false' }};
    var saglayici = 'elevenlabs';
    var sesler = { google: null, elevenlabs: null };
    var $ = function (id) { return document.getElementById(id); };
    var calan = null;

    function sayac() { $('ssSayac').textContent = $('ssMetin').value.length + ' / {{ $maks }} karakter'; }
    $('ssMetin').addEventListener('input', sayac); sayac();
    $('ssHiz').addEventListener('input', function () { $('ssHizD').textContent = parseFloat(this.value).toFixed(2); });
    document.querySelectorAll('.ss-cip').forEach(function (b) {
        b.addEventListener('click', function () { $('ssMetin').value = b.dataset.metin; sayac(); });
    });
    document.querySelectorAll('.ss-sekme button').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('.ss-sekme button').forEach(function (x) { x.classList.toggle('aktif', x === b); });
            saglayici = b.dataset.s;
            $('ssElAyar').style.display = saglayici === 'elevenlabs' ? '' : 'none';
            yukle();
        });
    });
    ['ssAra', 'ssCins', 'ssTur'].forEach(function (id) { $(id).addEventListener('input', ciz); });

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

    function yukle() {
        if (sesler[saglayici]) { turlar(); ciz(); return; }
        $('ssListe').innerHTML = '<div class="ss-bos">Yükleniyor…</div>';
        fetch('/sistemyonetim/v2/santral-ses/sesler?saglayici=' + saglayici, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.ok) { $('ssListe').innerHTML = '<div class="ss-bos">' + esc(d.hata) + '</div>'; return; }
                sesler[saglayici] = d.sesler; turlar(); ciz();
            })
            .catch(function () { $('ssListe').innerHTML = '<div class="ss-bos">Ses listesi alınamadı.</div>'; });
    }
    function turlar() {
        var t = {}; (sesler[saglayici] || []).forEach(function (s) { t[s.tur] = 1; });
        $('ssTur').innerHTML = '<option value="">Tüm türler</option>' + Object.keys(t).map(function (k) { return '<option>' + esc(k) + '</option>'; }).join('');
    }
    function ciz() {
        var q = $('ssAra').value.toLocaleLowerCase('tr'), c = $('ssCins').value, tur = $('ssTur').value;
        var l = (sesler[saglayici] || []).filter(function (s) {
            return (!c || s.cinsiyet === c) && (!tur || s.tur === tur) && (!q || (s.ad + ' ' + s.id + ' ' + s.not).toLocaleLowerCase('tr').indexOf(q) >= 0);
        });
        if (!l.length) { $('ssListe').innerHTML = '<div class="ss-bos">Ses bulunamadı.</div>'; return; }
        $('ssListe').innerHTML = l.map(function (s, i) {
            var cls = /Chirp/.test(s.tur) ? 'hd' : (/kütüphane/.test(s.tur) ? 'kut' : '');
            return '<div class="ss-satir"><button type="button" class="ss-oynat" data-i="' + i + '" title="Dinle"><span class="mdi mdi-play"></span></button>'
                + '<div style="flex:1;min-width:0"><div class="ss-ad">' + esc(s.ad) + (s.cinsiyet ? ' <span class="ss-bilgi">· ' + esc(s.cinsiyet) + '</span>' : '') + '</div>'
                + '<div class="ss-bilgi">' + esc(s.not || '') + ' <span style="opacity:.6">' + esc(s.id) + '</span></div></div>'
                + '<span class="ss-rozet ' + cls + '">' + esc(s.tur) + '</span></div>';
        }).join('');
        $('ssListe').querySelectorAll('.ss-oynat').forEach(function (b) {
            b.addEventListener('click', function () {
                var s = l[+b.dataset.i];
                var p = { saglayici: saglayici, ses: s.id, ses_ad: s.ad };
                if (saglayici === 'elevenlabs') { p.model = $('ssModel').value; p.stabilite = parseFloat($('ssStab').value); }
                dinle(p, b);
            });
        });
    }

    document.querySelectorAll('.ss-hazir-btn').forEach(function (b) {
        b.addEventListener('click', function () { dinle(JSON.parse(b.dataset.h), b); });
    });

    function dinle(p, btn) {
        $('ssHata').textContent = '';
        if (!yetkili) { $('ssHata').textContent = 'Ses üretme yetkiniz yok.'; return; }
        var metin = $('ssMetin').value.trim();
        if (!metin) { $('ssHata').textContent = 'Önce bir metin yazın.'; return; }
        var govde = new FormData();
        govde.append('_token', csrf);
        govde.append('saglayici', p.saglayici); govde.append('ses', p.ses); govde.append('metin', metin);
        govde.append('telefon', $('ssTelefon').checked ? '1' : '0');
        govde.append('hiz', $('ssHiz').value);
        if (p.saglayici === 'elevenlabs') { govde.append('model', p.model || 'eleven_v3'); govde.append('stabilite', p.stabilite == null ? 0.5 : p.stabilite); }
        if (btn) btn.disabled = true;
        var t0 = performance.now();
        fetch('/sistemyonetim/v2/santral-ses/uret', { method: 'POST', body: govde, credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok || !/audio/.test(r.headers.get('Content-Type') || '')) {
                    return r.json().then(function (d) { throw new Error(d.hata || ('HTTP ' + r.status)); }, function () { throw new Error('HTTP ' + r.status); });
                }
                var onb = r.headers.get('X-Onbellek') === '1', ms = r.headers.get('X-Uretim-Ms');
                return r.blob().then(function (b) { return { b: b, onb: onb, ms: ms }; });
            })
            .then(function (x) {
                var url = URL.createObjectURL(x.b);
                if (calan) { try { calan.pause(); } catch (e) {} }
                calan = new Audio(url); calan.play().catch(function () {});
                var etiket = (p.saglayici === 'elevenlabs' ? 'ElevenLabs ' + (p.model || '').replace('eleven_', '') + ' · ' : 'Google · ') + (p.ses_ad || p.ses)
                    + (p.saglayici === 'elevenlabs' ? ' · stab ' + (p.stabilite == null ? 0.5 : p.stabilite) : '')
                    + ($('ssTelefon').checked ? ' · 8 kHz' : ' · HD');
                var sure = x.onb ? 'önbellekten' : ('üretim ' + (x.ms || Math.round(performance.now() - t0)) + ' ms');
                var g = $('ssGecmis'); if (g.querySelector('.ss-bos')) g.innerHTML = '';
                var el = document.createElement('div'); el.className = 'ss-gecmis-ogesi';
                el.innerHTML = '<b>' + esc(etiket) + '</b><div class="ss-bilgi">' + esc(sure) + ' · ' + esc(metin.slice(0, 60)) + (metin.length > 60 ? '…' : '') + '</div>';
                var a = document.createElement('audio'); a.controls = true; a.src = url; el.appendChild(a);
                g.insertBefore(el, g.firstChild);
            })
            .catch(function (e) { $('ssHata').textContent = (p.ses_ad || p.ses) + ': ' + e.message; })
            .then(function () { if (btn) btn.disabled = false; });
    }

    yukle();
})();
</script>
@endsection
