@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<style>
    .wa-card { background:#fff; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
    .wa-grid { display:grid; grid-template-columns: 1fr 1fr; gap:24px; }
    @media (max-width:900px){ .wa-grid{ grid-template-columns: 1fr; } }
    .wa-qr { text-align:center; }
    .wa-qr img { width:300px; height:300px; max-width:100%; border:1px solid #eee; padding:8px; background:#fff; border-radius:8px; }
    .wa-status { display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; font-weight:600; font-size:14px; }
    .wa-status .dot { width:10px; height:10px; border-radius:50%; background:#aaa; }
    .wa-status.connected{ background:#e8f7ee; color:#1a7f3e; } .wa-status.connected .dot{ background:#1a7f3e; }
    .wa-status.qr-pending{ background:#fff6e5; color:#b67a00; } .wa-status.qr-pending .dot{ background:#b67a00; }
    .wa-status.disconnected,.wa-status.connecting{ background:#eee; color:#444; } .wa-status.connecting .dot{ background:#666; }
    .wa-status.banned-or-loggedout,.wa-status.servis-kapali,.wa-status.auto-paused-ban-risk,.wa-status.rate-limited{ background:#fde8e8; color:#b02020; } .wa-status.banned-or-loggedout .dot,.wa-status.auto-paused-ban-risk .dot,.wa-status.rate-limited .dot{ background:#b02020; }
    .wa-info { color:#555; line-height:1.6; font-size:14px; }
    .wa-info ul { margin:8px 0; padding-left:18px; }
    .wa-actions { margin-top:18px; display:flex; gap:12px; flex-wrap:wrap; }
    .btn-wa { background:#25D366; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; }
    .btn-wa:hover{ background:#1ebe57; color:#fff; }
    .btn-wa-danger{ background:#dc3545; } .btn-wa-danger:hover{ background:#c82333; }
    .wa-meta { color:#666; font-size:13px; margin-top:10px; }
    .wa-meta b { color:#222; }
</style>

<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-6">
         <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a></li>
               <li class="breadcrumb-item active" aria-current="page">{{$sayfa_baslik}}</li>
            </ol>
         </nav>
      </div>
   </div>
</div>

<div class="wa-card">
    <div class="wa-grid">
        <div class="wa-qr">
            <h3 style="margin-bottom:16px">Bağlantı Durumu
                <span id="wa-status-badge" class="wa-status connecting"><span class="dot"></span><span id="wa-status-text">Yükleniyor…</span></span>
            </h3>
            <div id="wa-qr-wrapper" style="display:none">
                <p style="color:#555;margin-bottom:12px">Telefonunuzdan <b>WhatsApp &gt; Ayarlar &gt; Bağlı Cihazlar &gt; Cihaz Bağla</b> menüsünden aşağıdaki QR kodu okutun.</p>
                <img id="wa-qr-img" src="" alt="QR Kod">
                <p class="wa-meta">QR 30-60 saniye geçerlidir, otomatik yenilenir.</p>
            </div>
            <div id="wa-connected-wrapper" style="display:none">
                <div style="font-size:48px;margin:20px 0;">✅</div>
                <p>WhatsApp bağlı numara: <b id="wa-phone">-</b></p>
                <p class="wa-meta">Son bağlantı: <span id="wa-connected-at">-</span></p>
            </div>
            <div id="wa-offline-wrapper" style="display:none">
                <div style="font-size:48px;margin:20px 0;">📴</div>
                <p id="wa-offline-msg">WhatsApp oturumu kapalı. Başlatmak için butona tıklayın.</p>
            </div>

            <div class="wa-actions" style="justify-content:center">
                <button type="button" class="btn-wa" id="wa-start-btn">WhatsApp'ı Bağla</button>
                <button type="button" class="btn-wa btn-wa-danger" id="wa-logout-btn" style="display:none">Oturumu Kapat</button>
            </div>

            <div id="wa-kanal-box" style="margin-top:24px; padding:16px; background:#f7f9fc; border-radius:8px; border:1px solid #e3e8f0; text-align:left; display:none;">
                <label style="display:flex; align-items:center; gap:12px; cursor:pointer; margin:0;">
                    <input type="checkbox" id="wa-kanal-switch" style="width:18px; height:18px; cursor:pointer;">
                    <div>
                        <div style="font-weight:600; color:#222;">WhatsApp üzerinden hatırlatma gönder</div>
                        <div style="color:#666; font-size:13px; margin-top:2px;">
                            Açık: <b>1 gün öncesi</b> ve <b>salon hatırlatma saatinde (örn. 2 saat önce)</b> hatırlatmalar önce WhatsApp'tan gider, başarısızsa SMS'e düşer.
                            Kapalı: Sadece SMS kullanılır.
                        </div>
                    </div>
                </label>
                <div id="wa-kanal-status" style="margin-top:10px; font-size:13px; color:#1a7f3e; display:none;">✓ Ayar kaydedildi</div>
            </div>
        </div>

        <div class="wa-info">
            <h3>Nasıl Çalışır?</h3>
            <ul>
                <li>WhatsApp bağlandıktan sonra SMS Ayarları'nda <b>"Müşteri" işaretli</b> randevu hatırlatmaları, müşterilerinize <b>kendi WhatsApp numaranız</b> üzerinden iletilir.</li>
                <li>Müşterinin WhatsApp'ı yoksa veya iletilemezse, mesaj <b>otomatik SMS</b> olarak gider — hiçbir hatırlatma kaybolmaz.</li>
                <li>Mesajlar müşterilere <b>doğal aralıklarla, kişiselleştirilmiş</b> şekilde iletilir (her müşteriye özel selamlama ve metin).</li>
                <li><b>İlk hafta hazırlık dönemi:</b> Numaranızın WhatsApp Business sisteminde stabil çalışması için ilk 7 gün günlük gönderim sayısı kademeli artar (1. gün 15, 7. gün tam kapasite).</li>
                <li><b>Çalışma saatleri:</b> Müşterilerinize gece geç saatte rahatsız edici mesaj gitmemesi için gönderimler gündüz saatlerinde yapılır.</li>
                <li>Bağlantı koparsa veya bir sorun olduğunda <b>panel size haber verir</b>, hatırlatmalar otomatik SMS'e geçer.</li>
                <li><b>İpucu:</b> En az 2 haftadır kullanılan, WhatsApp Business uygulaması yüklü bir numara bağlamak en sağlıklı sonucu verir.</li>
            </ul>
            <div class="wa-meta">
                <div>Günlük limit: <b id="wa-daily-limit">{{ $isletme->whatsapp_gunluk_limit ?? 150 }}</b></div>
                <div id="wa-last-error-wrap" style="display:none">Son hata: <b id="wa-last-error">-</b></div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    var statusBadge = document.getElementById('wa-status-badge');
    var statusText  = document.getElementById('wa-status-text');
    var qrWrap      = document.getElementById('wa-qr-wrapper');
    var qrImg       = document.getElementById('wa-qr-img');
    var okWrap      = document.getElementById('wa-connected-wrapper');
    var offWrap     = document.getElementById('wa-offline-wrapper');
    var phoneEl     = document.getElementById('wa-phone');
    var connectedAt = document.getElementById('wa-connected-at');
    var startBtn    = document.getElementById('wa-start-btn');
    var logoutBtn   = document.getElementById('wa-logout-btn');
    var lastErrorWrap = document.getElementById('wa-last-error-wrap');
    var lastErrorEl = document.getElementById('wa-last-error');

    var csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';
    var qs = window.location.search || '';

    var LABELS = {
        'connected': 'Bağlı',
        'qr-pending': 'QR Bekleniyor',
        'connecting': 'Bağlanıyor…',
        'disconnected': 'Bağlantı Kesildi',
        'banned-or-loggedout': 'Oturum Sonlandı (Ban Riski)',
        'auto-paused-ban-risk': 'Ban Riski — Otomatik Durduruldu',
        'rate-limited': 'Rate-Limit (Durduruldu)',
        'not-initialized': 'Bağlı Değil',
        'servis-kapali': 'Servis Kapalı',
        'cikis-yapildi': 'Çıkış Yapıldı'
    };

    function setStatus(s){
        var label = LABELS[s] || s;
        statusText.textContent = label;
        statusBadge.className = 'wa-status ' + (s || 'connecting');
    }

    function showOnly(which){
        qrWrap.style.display  = which === 'qr' ? 'block' : 'none';
        okWrap.style.display  = which === 'ok' ? 'block' : 'none';
        offWrap.style.display = which === 'off' ? 'block' : 'none';
        startBtn.style.display  = which === 'off' ? 'inline-block' : 'none';
        logoutBtn.style.display = (which === 'ok' || which === 'qr') ? 'inline-block' : 'none';
    }

    function fetchJson(url, opts){
        opts = opts || {};
        opts.headers = Object.assign({
            'Accept':'application/json',
            'X-CSRF-TOKEN': csrf,
        }, opts.headers || {});
        opts.credentials = 'same-origin';
        return fetch(url, opts).then(function(r){
            return r.json().then(function(j){ return { status: r.status, body: j }; });
        });
    }

    function loadQr(){
        fetchJson('/isletmeyonetim/whatsapp/qr' + qs).then(function(res){
            if(res.status === 200 && res.body.qr){
                qrImg.src = res.body.qr;
            }
        });
    }

    function tick(){
        fetchJson('/isletmeyonetim/whatsapp/durum' + qs).then(function(res){
            var b = res.body || {};
            var s = b.status || 'not-initialized';
            setStatus(s);
            if(b.lastError){ lastErrorEl.textContent = b.lastError; lastErrorWrap.style.display='block'; }
            else { lastErrorWrap.style.display='none'; }

            if(s === 'connected'){
                showOnly('ok');
                phoneEl.textContent = b.phone || '-';
                connectedAt.textContent = b.connectedAt ? new Date(b.connectedAt).toLocaleString('tr-TR') : '-';
            } else if(s === 'qr-pending' || b.hasQr){
                showOnly('qr');
                loadQr();
            } else {
                showOnly('off');
            }
        }).catch(function(){
            setStatus('servis-kapali');
            showOnly('off');
        });
    }

    startBtn.addEventListener('click', function(){
        startBtn.disabled = true;
        startBtn.textContent = 'Başlatılıyor…';
        fetchJson('/isletmeyonetim/whatsapp/baslat' + qs, { method:'POST' }).then(function(){
            setTimeout(function(){
                startBtn.disabled = false;
                startBtn.textContent = 'WhatsApp\'ı Bağla';
                tick();
            }, 1500);
        });
    });

    logoutBtn.addEventListener('click', function(){
        if(!confirm('WhatsApp oturumunu kapatmak istediğinize emin misiniz?')) return;
        logoutBtn.disabled = true;
        fetchJson('/isletmeyonetim/whatsapp/cikis' + qs, { method:'POST' }).then(function(){
            logoutBtn.disabled = false;
            tick();
        });
    });

    // Kanal ayari toggle
    var kanalBox = document.getElementById('wa-kanal-box');
    var kanalSwitch = document.getElementById('wa-kanal-switch');
    var kanalStatus = document.getElementById('wa-kanal-status');

    function loadKanalDurum(){
        fetchJson('/isletmeyonetim/whatsapp/kanal-durum' + qs).then(function(res){
            if(res.status === 200){
                kanalBox.style.display = 'block';
                kanalSwitch.checked = !!res.body.aktif;
            }
        });
    }

    kanalSwitch.addEventListener('change', function(){
        var val = kanalSwitch.checked ? 1 : 0;
        kanalSwitch.disabled = true;
        var fd = new FormData();
        fd.append('aktif', val);
        fetchJson('/isletmeyonetim/whatsapp/kanal-toggle' + qs, { method:'POST', body: fd }).then(function(res){
            kanalSwitch.disabled = false;
            if(res.status === 200){
                kanalStatus.style.display = 'block';
                setTimeout(function(){ kanalStatus.style.display = 'none'; }, 2000);
            } else {
                kanalSwitch.checked = !kanalSwitch.checked;
                alert('Ayar kaydedilemedi');
            }
        }).catch(function(){
            kanalSwitch.disabled = false;
            kanalSwitch.checked = !kanalSwitch.checked;
        });
    });

    loadKanalDurum();
    tick();
    setInterval(tick, 4000);
})();
</script>
@endsection
