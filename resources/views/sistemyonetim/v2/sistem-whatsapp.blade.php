@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2 style="font-size:24px;font-weight:800"><span class="mdi mdi-whatsapp" style="color:#25d366"></span> Sistem WhatsApp</h2>
        <div class="subtitle">Ayrı bir numarayı QR ile bağla (gönderen), müşteri demo açınca o hattan senin numarana WhatsApp + SMS gelsin.</div>
    </div>
</div>

@if(session('basari'))<div class="sy-alert sy-alert-success"><span class="mdi mdi-check-circle"></span> {{ session('basari') }}</div>@endif
@if(session('hata'))<div class="sy-alert sy-alert-danger"><span class="mdi mdi-alert"></span> {{ session('hata') }}</div>@endif

{{-- 1) Bildirim ayari --}}
<div class="sy-card sy-mt-12">
    <div class="sy-card-head">
        <h3><span class="mdi mdi-bell-ring"></span> Bildirim Ayarı</h3>
    </div>
    <div class="sy-card-body">
        <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/ayar" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
            @csrf
            <div class="sy-form-group" style="margin:0;min-width:280px">
                <label><b>ALICI</b> — mesajın geleceği numara(lar). Birden fazlaysa <b>virgülle</b> ayır.</label>
                <input type="text" name="numara" class="sy-input" value="{{ str_replace(',', ', ', $ayar['numara']) }}" placeholder="0541 xxx xx xx, 0531 xxx xx xx">
            </div>
            <div class="sy-form-group" style="margin:0;min-width:230px">
                <label><b>GÖNDEREN</b> — QR ile bağlayacağın numara</label>
                <input type="text" name="gonderen_numara" class="sy-input" value="{{ $ayar['gonderen_numara'] }}" placeholder="0531 xxx xx xx">
            </div>
            <div class="sy-form-group" style="margin:0">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                    <input type="checkbox" name="aktif" value="1" {{ $ayar['aktif'] ? 'checked' : '' }}> Bildirimler açık
                </label>
            </div>
            <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
        </form>

        <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/test" style="margin-top:14px">
            @csrf
            <button type="submit" class="sy-btn sy-btn-soft"><span class="mdi mdi-send-check"></span> Test Mesajı Gönder (WA + SMS)</button>
        </form>
    </div>
</div>

{{-- 2) WhatsApp baglanti (GONDEREN numara - QR) --}}
<div class="sy-card sy-mt-12">
    <div class="sy-card-head">
        <h3><span class="mdi mdi-qrcode-scan"></span> Gönderen WhatsApp (QR ile bağla)</h3>
        <span id="wa-durum" class="sy-badge sy-badge-muted">Durum kontrol ediliyor…</span>
    </div>
    <div class="sy-card-body">
        <div id="wa-servis" class="sy-alert" style="background:#f3f4f6;color:#555;margin-bottom:12px">
            <span class="mdi mdi-server-network"></span> Servis durumu kontrol ediliyor…
        </div>
        <div id="wa-bagli-kutu" style="display:none">
            <div class="sy-alert sy-alert-success">
                <span class="mdi mdi-check-circle"></span> Gönderen WhatsApp bağlı: <b id="wa-telefon">—</b>
            </div>
            <div id="wa-self-uyari" class="sy-alert sy-alert-danger" style="display:none">
                <span class="mdi mdi-alert"></span> <b>Dikkat:</b> Gönderen numara ile bildirim (alıcı) numarası <b>aynı</b>. WhatsApp kendine mesaj göndermez. Gönderen için <b>farklı</b> bir numara bağla.
            </div>
            <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/cikis" data-confirm="Gönderen WhatsApp oturumu kapatılsın mı?">
                @csrf
                <button type="submit" class="sy-btn sy-btn-sm"><span class="mdi mdi-logout"></span> Oturumu Kapat</button>
            </form>
        </div>

        <div id="wa-baglan-kutu">
            <p class="sy-text-muted">
                @if(!empty($ayar['gonderen_numara']))
                    QR'ı <b style="color:var(--sy-primary)">{{ $ayar['gonderen_numara'] }}</b> numaralı telefonun WhatsApp'ından okut:
                    <b>WhatsApp &gt; Ayarlar &gt; Bağlı Cihazlar &gt; Cihaz Bağla</b>.
                @else
                    Önce yukarıya <b>GÖNDEREN</b> numarayı yazıp Kaydet, sonra o telefonun WhatsApp'ından
                    <b>Bağlı Cihazlar &gt; Cihaz Bağla</b> ile QR'ı okut.
                @endif
            </p>
            <button id="wa-baglan-btn" type="button" class="sy-btn sy-btn-primary"><span class="mdi mdi-whatsapp"></span> Bağlan / QR Göster</button>
            <div id="wa-teshis" class="sy-text-muted sy-fs-12" style="margin-top:8px;font-family:monospace"></div>
            <div id="wa-qr-kutu" style="display:none;margin-top:16px;text-align:center">
                <img id="wa-qr" src="" alt="QR" style="width:300px;height:300px;max-width:100%;border:1px solid #eee;padding:8px;background:#fff;border-radius:8px">
                <div class="sy-text-muted sy-fs-12" style="margin-top:8px">QR 30-60 sn geçerli, otomatik yenilenir. Okuttuktan sonra "Bağlı" olur.</div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var CSRF = '{{ csrf_token() }}';
    var durumEl = document.getElementById('wa-durum');
    var bagliKutu = document.getElementById('wa-bagli-kutu');
    var baglanKutu = document.getElementById('wa-baglan-kutu');
    var qrKutu = document.getElementById('wa-qr-kutu');
    var qrImg = document.getElementById('wa-qr');
    var telEl = document.getElementById('wa-telefon');
    var baglanBtn = document.getElementById('wa-baglan-btn');
    var AYAR_NUMARALAR = {!! json_encode(\App\Services\SistemBildirim::alicilar()) !!};
    var AYAR_GONDEREN = '{{ preg_replace('/[^0-9]/', '', $ayar['gonderen_numara']) }}';
    var pollTimer = null;

    function setDurum(text, renk) { durumEl.textContent = text; durumEl.className = 'sy-badge sy-badge-' + renk; }

    function bagli(phone) {
        setDurum('Bağlı', 'success');
        telEl.textContent = phone || '—';
        bagliKutu.style.display = 'block';
        baglanKutu.style.display = 'none';
        var self = document.getElementById('wa-self-uyari');
        if (!self) return;
        var p = String(phone || '');
        if (p && AYAR_NUMARALAR && AYAR_NUMARALAR.indexOf(String(p)) !== -1) {
            self.innerHTML = '<span class="mdi mdi-alert"></span> <b>Dikkat:</b> Bağlanan (gönderen) numara ile ALICI numaran <b>aynı</b>. WhatsApp kendine mesaj göndermez — farklı bir numara bağla.';
            self.style.display = 'block';
        } else if (p && AYAR_GONDEREN && p !== String(AYAR_GONDEREN)) {
            self.innerHTML = '<span class="mdi mdi-alert"></span> <b>Uyarı:</b> Bağlanan numara (' + p + ') yazdığın GÖNDEREN numaradan (' + AYAR_GONDEREN + ') farklı. Doğru telefonla bağla, ya da GÖNDEREN alanını güncelle.';
            self.style.display = 'block';
        } else {
            self.style.display = 'none';
        }
    }
    function bagliDegil() { bagliKutu.style.display = 'none'; baglanKutu.style.display = 'block'; }

    function statusCek() {
        fetch('/sistemyonetim/v2/sistem-whatsapp/status').then(function (r) { return r.json(); }).then(function (d) {
            var b = (d && d.body) ? d.body : {};
            var s = b.status || 'unknown';
            if (s === 'connected') { bagli(b.phone); qrKutu.style.display = 'none'; }
            else {
                bagliDegil();
                setDurum(s === 'connecting' ? 'Bağlanıyor / QR bekleniyor…' : (b.hasQr || s === 'qr' ? 'QR hazır — okut' : 'Bağlı değil'), 'warning');
                qrCek(); // status ne olursa olsun QR'i dene (connecting sirasinda da QR gelebilir)
            }
        }).catch(function () { setDurum('Servise ulaşılamadı', 'danger'); });
    }
    var teshisLog = [];
    function teshis(t) {
        var el = document.getElementById('wa-teshis');
        if (!el) return;
        teshisLog.push(t);
        if (teshisLog.length > 5) teshisLog.shift();
        el.innerHTML = teshisLog.join('<br>');
    }
    function qrCek() {
        fetch('/sistemyonetim/v2/sistem-whatsapp/qr').then(function (r) {
            return r.json().then(function (j) { return { st: r.status, j: j }; });
        }).then(function (o) {
            var d = o.j || {};
            var qr = d && d.body && d.body.qr ? d.body.qr : null;
            if (qr) {
                // whatsmeow HAM QR metni doner; Baileys data-URL. Ham metni resme cevir
                // (mevcut whatsmeow pilot sayfasiyla ayni: api.qrserver.com).
                if (String(qr).indexOf('data:') === 0) { qrImg.src = qr; }
                else { qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' + encodeURIComponent(qr); }
                qrKutu.style.display = 'block';
                teshis('QR alındı ✓ — okut.');
            } else { teshis('QR yok · /qr → http ' + o.st + ' · ' + JSON.stringify(d.body || d).slice(0, 140)); }
        }).catch(function () { teshis('QR isteği başarısız (ağ/servis).'); });
    }
    baglanBtn.addEventListener('click', function () {
        baglanBtn.disabled = true; baglanBtn.textContent = 'Başlatılıyor…';
        teshis('Başlatılıyor…');
        fetch('/sistemyonetim/v2/sistem-whatsapp/baglat', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (j) { return { st: r.status, j: j }; }); })
            .then(function (o) {
                teshis('baglat → http ' + o.st + ' · ' + JSON.stringify(o.j).slice(0, 140));
                setDurum('QR hazırlanıyor…', 'info');
                if (pollTimer) clearInterval(pollTimer);
                pollTimer = setInterval(function () { statusCek(); qrCek(); }, 2000);
                setTimeout(qrCek, 800); setTimeout(qrCek, 2000); setTimeout(qrCek, 4000);
            })
            .catch(function () { setDurum('Servise ulaşılamadı', 'danger'); teshis('baglat isteği başarısız.'); })
            .finally(function () { baglanBtn.disabled = false; baglanBtn.innerHTML = '<span class="mdi mdi-whatsapp"></span> Bağlan / QR Göster'; });
    });

    function healthCek() {
        var el = document.getElementById('wa-servis');
        if (!el) return;
        fetch('/sistemyonetim/v2/sistem-whatsapp/health').then(function (r) { return r.json(); }).then(function (d) {
            var b = d.baileys_3001, w = d.whatsmeow_3002;
            var iyi = function (x) { return x ? '<b style="color:#16a34a">AÇIK ✓</b>' : '<b style="color:#dc2626">KAPALI ✗</b>'; };
            el.innerHTML = '<span class="mdi mdi-server-network"></span> WhatsApp servisleri → Baileys(3001): ' + iyi(b) + ' · whatsmeow(3002): ' + iyi(w)
                + (!b && !w ? '  <b style="color:#dc2626">— İkisi de kapalı, WhatsApp bağlanamaz!</b>' : '');
        }).catch(function () { el.innerHTML = '<span class="mdi mdi-alert"></span> Servis durumu alınamadı.'; });
    }

    healthCek();
    statusCek();
    pollTimer = setInterval(statusCek, 5000);
})();
</script>

@endsection
