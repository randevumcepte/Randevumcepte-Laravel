@extends('sistemyonetim.v2.layout')

@section('content')

<div class="sy-page-head">
    <div>
        <h2 style="font-size:24px;font-weight:800"><span class="mdi mdi-whatsapp" style="color:#25d366"></span> Sistem WhatsApp</h2>
        <div class="subtitle">Kendi WhatsApp'ını QR ile bağla — müşteri demo açınca buradan sana mesaj gelsin (salonlardan bağımsız).</div>
    </div>
</div>

{{-- Bildirim numarasi + aktiflik --}}
<div class="sy-card sy-mt-12">
    <div class="sy-card-head">
        <h3><span class="mdi mdi-bell-ring"></span> Bildirim Ayarı</h3>
    </div>
    <div class="sy-card-body">
        <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/ayar" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            @csrf
            <div class="sy-form-group" style="margin:0;min-width:220px">
                <label>Bildirim Numarası (senin numaran)</label>
                <input type="text" name="numara" class="sy-input" value="{{ $ayar['numara'] }}" placeholder="05xx xxx xx xx">
            </div>
            <div class="sy-form-group" style="margin:0">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                    <input type="checkbox" name="aktif" value="1" {{ $ayar['aktif'] ? 'checked' : '' }}> Bildirimler açık
                </label>
            </div>
            <button type="submit" class="sy-btn sy-btn-primary"><span class="mdi mdi-content-save"></span> Kaydet</button>
        </form>

        <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/test" style="margin-top:12px">
            @csrf
            <button type="submit" class="sy-btn sy-btn-soft"><span class="mdi mdi-send-check"></span> Test Mesajı Gönder (WA + SMS)</button>
        </form>
    </div>
</div>

{{-- WhatsApp baglanti (QR) --}}
<div class="sy-card sy-mt-12">
    <div class="sy-card-head">
        <h3><span class="mdi mdi-qrcode-scan"></span> WhatsApp Bağlantısı</h3>
        <span id="wa-durum" class="sy-badge sy-badge-muted">Durum kontrol ediliyor…</span>
    </div>
    <div class="sy-card-body">
        <div id="wa-bagli-kutu" style="display:none">
            <div class="sy-alert sy-alert-success">
                <span class="mdi mdi-check-circle"></span> WhatsApp bağlı: <b id="wa-telefon">—</b>
            </div>
            <div id="wa-self-uyari" class="sy-alert sy-alert-danger" style="display:none">
                <span class="mdi mdi-alert"></span> <b>Dikkat:</b> Bildirim numarası bağlı WhatsApp numarasıyla <b>aynı</b>. WhatsApp kendine mesaj göndermez — bu yüzden WA bildirimi <b>gelmez</b>. Bildirim numarasını <b>farklı</b> bir numara yap (SMS yine bu numaraya gider).
            </div>
            <form method="post" action="/sistemyonetim/v2/sistem-whatsapp/cikis" onsubmit="return confirm('Sistem WhatsApp oturumu kapatılsın mı?')">
                @csrf
                <button type="submit" class="sy-btn sy-btn-sm"><span class="mdi mdi-logout"></span> Oturumu Kapat</button>
            </form>
        </div>

        <div id="wa-baglan-kutu">
            <p class="sy-text-muted">Bağlamak için butona bas, sonra telefonundan <b>WhatsApp &gt; Ayarlar &gt; Bağlı Cihazlar &gt; Cihaz Bağla</b> ile QR'ı okut.</p>
            <button id="wa-baglan-btn" type="button" class="sy-btn sy-btn-primary"><span class="mdi mdi-whatsapp"></span> Bağlan / QR Göster</button>
            <div id="wa-qr-kutu" style="display:none;margin-top:16px;text-align:center">
                <img id="wa-qr" src="" alt="QR" style="width:300px;height:300px;max-width:100%;border:1px solid #eee;padding:8px;background:#fff;border-radius:8px">
                <div class="sy-text-muted sy-fs-12" style="margin-top:8px">QR 30-60 sn geçerli, otomatik yenilenir. Okuttuktan sonra bu ekran "Bağlı" olur.</div>
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
    var pollTimer = null;

    function setDurum(text, renk) {
        durumEl.textContent = text;
        durumEl.className = 'sy-badge sy-badge-' + renk;
    }

    var AYAR_NUMARA = '{{ preg_replace('/[^0-9]/', '', $ayar['numara']) }}';
    function bagli(phone) {
        setDurum('Bağlı', 'success');
        telEl.textContent = phone || '—';
        bagliKutu.style.display = 'block';
        baglanKutu.style.display = 'none';
        // Alici == gonderen ise WA kendine gonderemez -> uyari
        var self = document.getElementById('wa-self-uyari');
        if (self) self.style.display = (phone && AYAR_NUMARA && String(phone) === String(AYAR_NUMARA)) ? 'block' : 'none';
    }

    function bagliDegil() {
        bagliKutu.style.display = 'none';
        baglanKutu.style.display = 'block';
    }

    function statusCek() {
        fetch('/sistemyonetim/v2/sistem-whatsapp/status').then(function (r) { return r.json(); }).then(function (d) {
            var b = (d && d.body) ? d.body : {};
            var s = b.status || 'unknown';
            if (s === 'connected') {
                bagli(b.phone);
                qrKutu.style.display = 'none';
            } else {
                bagliDegil();
                if (s === 'qr' || b.hasQr) { setDurum('QR bekliyor', 'warning'); qrCek(); }
                else if (s === 'connecting') setDurum('Bağlanıyor…', 'info');
                else setDurum('Bağlı değil', 'muted');
            }
        }).catch(function () { setDurum('Servise ulaşılamadı', 'danger'); });
    }

    function qrCek() {
        fetch('/sistemyonetim/v2/sistem-whatsapp/qr').then(function (r) { return r.json(); }).then(function (d) {
            var qr = d && d.body && d.body.qr ? d.body.qr : null;
            if (qr) { qrImg.src = qr; qrKutu.style.display = 'block'; }
        }).catch(function () {});
    }

    baglanBtn.addEventListener('click', function () {
        baglanBtn.disabled = true;
        baglanBtn.textContent = 'Başlatılıyor…';
        fetch('/sistemyonetim/v2/sistem-whatsapp/baglat', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).then(function () {
            setDurum('QR hazırlanıyor…', 'info');
            if (pollTimer) clearInterval(pollTimer);
            pollTimer = setInterval(statusCek, 3000);
            setTimeout(statusCek, 1200);
        }).finally(function () {
            baglanBtn.disabled = false;
            baglanBtn.innerHTML = '<span class="mdi mdi-whatsapp"></span> Bağlan / QR Göster';
        });
    });

    statusCek();
    pollTimer = setInterval(statusCek, 5000);
})();
</script>

@endsection
