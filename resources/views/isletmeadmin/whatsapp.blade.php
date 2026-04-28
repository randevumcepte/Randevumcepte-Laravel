@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<style>
.wsi-tabs { display:flex; gap:6px; margin:24px 0 16px; border-bottom:2px solid #e3e8f0; flex-wrap:wrap; }
.wsi-tab { padding:10px 18px; cursor:pointer; border-radius:6px 6px 0 0; font-weight:600; color:#666; background:#f7f9fc; border:1px solid #e3e8f0; border-bottom:none; }
.wsi-tab.active { background:#25D366; color:#fff; border-color:#25D366; }
.wsi-section { display:none; }
.wsi-section.active { display:block; }
.wsi-stat-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:18px; }
.wsi-stat-card { background:#fff; border-radius:8px; padding:14px; box-shadow:0 1px 4px rgba(0,0,0,.05); border-left:4px solid #25D366; }
.wsi-stat-card.warn { border-left-color:#f0ad4e; }
.wsi-stat-card.danger { border-left-color:#dc3545; }
.wsi-stat-card.info { border-left-color:#0099ff; }
.wsi-stat-label { color:#777; font-size:12px; margin-bottom:4px; }
.wsi-stat-value { font-size:24px; font-weight:700; color:#222; }
.wsi-stat-sub { color:#999; font-size:11px; margin-top:3px; }
.wsi-table { width:100%; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); border-collapse:collapse; font-size:13px; }
.wsi-table th, .wsi-table td { padding:9px 11px; text-align:left; border-bottom:1px solid #f0f3f7; }
.wsi-table th { background:#f7f9fc; font-weight:600; color:#333; font-size:11px; text-transform:uppercase; }
.wsi-table tr:hover { background:#fafbfc; }
.wsi-badge { display:inline-block; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:600; }
.wsi-badge.success { background:#d4edda; color:#155724; }
.wsi-badge.fail { background:#f8d7da; color:#721c24; }
.wsi-badge.fallback { background:#fff3cd; color:#856404; }
.wsi-badge.queued { background:#cce5ff; color:#004085; }
.wsi-filter { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; align-items:flex-end; }
.wsi-filter input, .wsi-filter select { padding:7px 10px; border:1px solid #ced4da; border-radius:5px; font-size:13px; }
.wsi-filter label { display:block; font-size:11px; color:#666; margin-bottom:3px; font-weight:600; }
.wsi-btn { padding:7px 14px; background:#25D366; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:13px; font-weight:600; }
.wsi-btn:hover { background:#1ebe57; }
.wsi-btn.secondary { background:#6c757d; }
.wsi-pagination { display:flex; gap:6px; justify-content:center; margin-top:14px; align-items:center; }
.wsi-pagination button { padding:6px 12px; border:1px solid #dee2e6; background:#fff; border-radius:4px; cursor:pointer; font-size:13px; }
.wsi-pagination button:disabled { opacity:0.4; cursor:not-allowed; }
.wsi-mesaj-trunc { max-width:340px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wsi-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
.wsi-modal.show { display:flex; }
.wsi-modal-content { background:#fff; border-radius:10px; padding:22px; max-width:700px; width:90%; max-height:90vh; overflow:auto; }
.wsi-modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
.wsi-modal-close { cursor:pointer; font-size:22px; color:#999; }
.wsi-spinner { display:inline-block; width:14px; height:14px; border:2px solid #ddd; border-top-color:#25D366; border-radius:50%; animation:wsispin 0.8s linear infinite; }
@keyframes wsispin { to { transform:rotate(360deg); } }
</style>
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
                <li><b>1 gün öncesi hatırlatma:</b> Yarınki tüm randevular için bugün <b>12:00-17:00</b> arasında bir kez gönderilir. Müşteri iptal/erteleme isterse salonun açık olduğu saatlerde sizi arayabilir.</li>
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
    // Kanal ayari toggle
    var kanalBox = document.getElementById('wa-kanal-box');
    var kanalSwitch = document.getElementById('wa-kanal-switch');
    var kanalStatus = document.getElementById('wa-kanal-status');

    tick();
    setInterval(tick, 4000);
})();
</script>

<div class="wpkt-wrapper">
    <div class="wpkt-header">
        <h2>WhatsApp Paketleri</h2>
        <p>Müşterilerinizi randevudan haberdar edin, no-show'u azaltın. Size uygun paketi seçin.</p>
        <div id="wpktCurrentBadge" class="wpkt-current" style="display:none;">Mevcut paket: <b id="wpktCurrentName">—</b></div>
    </div>

    <div class="wpkt-toggle">
        <button id="wpktAylik" class="active" onclick="wpktSetPeriyot('aylik')">Aylık</button>
        <button id="wpktYillik" onclick="wpktSetPeriyot('yillik')">Yıllık <span class="wpkt-discount">2 AY BEDAVA</span></button>
    </div>

    <div class="wpkt-grid">
        <div class="wpkt-card" id="wpktCardBaslangic">
            <div class="wpkt-tier-name">Başlangıç</div>
            <div class="wpkt-tier-desc">Sadece SMS hatırlatma kullanmak isteyen küçük işletmeler için</div>
            <div class="wpkt-price-block">
                <div class="wpkt-price">Ücretsiz</div>
                <div class="wpkt-price-aylik">Ek ücret yok</div>
            </div>
            <ul class="wpkt-features">
                <li>SMS ile randevu hatırlatma</li>
                <li>Mevcut SMS bakiyenizden düşülür</li>
                <li>Temel raporlama</li>
                <li class="no">WhatsApp gönderimi</li>
                <li class="no">Detaylı istatistik</li>
            </ul>
            <button class="wpkt-btn wpkt-btn-current" id="wpktBtnBaslangic">Mevcut Paket</button>
        </div>

        <div class="wpkt-card popular" id="wpktCardPro">
            <div class="wpkt-popular-tag">⭐ EN POPÜLER</div>
            <div class="wpkt-tier-name">Pro</div>
            <div class="wpkt-tier-desc">WhatsApp ile profesyonel hatırlatma — çoğu salon için ideal</div>
            <div class="wpkt-price-block">
                <div class="wpkt-price" id="wpktProFiyat">149 <small>TL/ay</small></div>
                <div class="wpkt-price-aylik" id="wpktProAylikInfo"></div>
            </div>
            <ul class="wpkt-features">
                <li><b>Başlangıç paketinin tüm özellikleri</b></li>
                <li>WhatsApp ile randevu hatırlatma</li>
                <li>Otomatik SMS fallback</li>
                <li>Mesaj geçmişi ve alıcı listesi</li>
                <li>Detaylı istatistik paneli</li>
                <li>İptal/güncelleme bildirimleri</li>
                <li class="no">Toplu kampanya gönderimi</li>
            </ul>
            <button class="wpkt-btn wpkt-btn-primary" onclick="wpktTalepAc('pro')">Pro'ya Yükselt</button>
        </div>

        <div class="wpkt-card" id="wpktCardPremium">
            <div class="wpkt-tier-name">Premium</div>
            <div class="wpkt-tier-desc">Yoğun salonlar ve kurumsal kullanım için sınırsız özellikler</div>
            <div class="wpkt-price-block">
                <div class="wpkt-price" id="wpktPremiumFiyat">299 <small>TL/ay</small></div>
                <div class="wpkt-price-aylik" id="wpktPremiumAylikInfo"></div>
            </div>
            <ul class="wpkt-features">
                <li><b>Pro paketinin tüm özellikleri</b></li>
                <li>Sınırsız mesaj gönderimi</li>
                <li>Toplu kampanya/duyuru gönderimi</li>
                <li>Resmi WhatsApp Business API</li>
                <li>Öncelikli teknik destek</li>
                <li>Excel detay raporları</li>
                <li>Özel mesaj şablonları</li>
            </ul>
            <button class="wpkt-btn wpkt-btn-outline" onclick="wpktTalepAc('premium')">Premium'a Yükselt</button>
        </div>
    </div>
</div>

<div class="wsi-modal" id="wpktTalepModal">
    <div class="wsi-modal-content" style="max-width:480px;">
        <div class="wsi-modal-header">
            <h4 style="margin:0;" id="wpktTalepBaslik">Paket Yükseltme Talebi</h4>
            <span class="wsi-modal-close" onclick="document.getElementById('wpktTalepModal').classList.remove('show')">×</span>
        </div>
        <div id="wpktTalepBody">
            <p style="color:#6c757d;font-size:14px;line-height:1.5;">Müşteri temsilcimiz sizinle iletişime geçerek ödeme ve aktivasyon süreci hakkında bilgi verecektir.</p>
            <label style="font-size:13px;color:#444;font-weight:600;margin-top:14px;display:block;">İletişim Bilgisi (telefon veya email)</label>
            <input type="text" id="wpktIletisim" style="width:100%;padding:10px;border:1px solid #ced4da;border-radius:6px;margin-top:6px;font-size:14px;" placeholder="örn. 0555 123 45 67">
            <button id="wpktTalepGonder" class="wpkt-btn wpkt-btn-primary" style="margin-top:16px;">Talebi Gönder</button>
            <div id="wpktTalepSonuc" style="margin-top:12px;font-size:13px;"></div>
        </div>
    </div>
</div>

<div class="wsi-tabs">
    <div class="wsi-tab active" data-wsi="ozet">📊 İstatistik</div>
    <div class="wsi-tab" data-wsi="loglar">📨 Mesajlarım</div>
    <div class="wsi-tab" data-wsi="aliciler">👥 Alıcılarım</div>
</div>

<div class="wsi-section active" id="wsi-section-ozet">
    <div class="wsi-stat-grid" id="wsiOzetGrid">
        <div class="wsi-stat-card"><div class="wsi-stat-label">Yükleniyor...</div><div class="wsi-stat-value">—</div></div>
    </div>
    <div style="background:#fff; border-radius:10px; padding:18px; box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <h4 style="margin-top:0;">Son 30 Gün — Günlük Mesaj Hacmi</h4>
        <canvas id="wsiChart" style="max-height:280px;"></canvas>
    </div>
</div>

<div class="wsi-section" id="wsi-section-loglar">
    <div class="wsi-filter">
        <div><label>Durum</label><select id="wsiLogDurum"><option value="">Tümü</option><option value="0">Kuyrukta</option><option value="1">Gönderildi</option><option value="2">Başarısız</option><option value="3">SMS'e Düştü</option></select></div>
        <div><label>Telefon</label><input type="text" id="wsiLogTelefon" placeholder="905..."></div>
        <div><label>Başlangıç</label><input type="date" id="wsiLogBaslangic"></div>
        <div><label>Bitiş</label><input type="date" id="wsiLogBitis"></div>
        <div><label>Mesaj İçinde Ara</label><input type="text" id="wsiLogArama" placeholder="..."></div>
        <div><button class="wsi-btn" id="wsiLogFiltreUygula">Filtrele</button></div>
        <div><button class="wsi-btn secondary" id="wsiLogFiltreSifirla">Sıfırla</button></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="wsi-table" id="wsiLogTable">
            <thead><tr><th>Tarih</th><th>Müşteri</th><th>Telefon</th><th>Durum</th><th>Mesaj</th><th>Hata</th></tr></thead>
            <tbody><tr><td colspan="6">Yükleniyor...</td></tr></tbody>
        </table>
    </div>
    <div class="wsi-pagination" id="wsiLogPagination"></div>
</div>

<div class="wsi-section" id="wsi-section-aliciler">
    <div style="overflow-x:auto;">
        <table class="wsi-table" id="wsiAliciTable">
            <thead><tr><th>Müşteri</th><th>Telefon</th><th>Toplam</th><th>Durum</th><th>İlk</th><th>Son</th><th></th></tr></thead>
            <tbody><tr><td colspan="7">Yükleniyor...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="wsi-modal" id="wsiAliciGecmisModal">
    <div class="wsi-modal-content">
        <div class="wsi-modal-header"><h4 style="margin:0;" id="wsiAliciGecmisBaslik">Mesaj Geçmişi</h4>
            <span class="wsi-modal-close" onclick="document.getElementById('wsiAliciGecmisModal').classList.remove('show')">×</span>
        </div>
        <div id="wsiAliciGecmisBody">Yükleniyor...</div>
    </div>
</div>

<div class="wsi-modal" id="wsiLogDetayModal">
    <div class="wsi-modal-content">
        <div class="wsi-modal-header"><h4 style="margin:0;">Mesaj Detayı</h4>
            <span class="wsi-modal-close" onclick="document.getElementById('wsiLogDetayModal').classList.remove('show')">×</span>
        </div>
        <div id="wsiLogDetayBody">Yükleniyor...</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    var DURUM_LABEL = {0:'Kuyrukta',1:'Gönderildi',2:'Başarısız',3:"SMS'e Düştü"};
    var DURUM_BADGE = {0:'queued',1:'success',2:'fail',3:'fallback'};
    var qs2 = window.location.search || '';

    function fetchJson(url){ return fetch(url, {credentials:'same-origin'}).then(function(r){ return r.json(); }); }
    function fmtDate(s){ if(!s) return '—'; try { return new Date(s).toLocaleString('tr-TR'); } catch(e){ return s; } }
    function escHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    document.querySelectorAll('.wsi-tab').forEach(function(t){
        t.addEventListener('click', function(){
            document.querySelectorAll('.wsi-tab').forEach(function(x){ x.classList.remove('active'); });
            document.querySelectorAll('.wsi-section').forEach(function(x){ x.classList.remove('active'); });
            t.classList.add('active');
            document.getElementById('wsi-section-' + t.dataset.wsi).classList.add('active');
            if (t.dataset.wsi === 'loglar') yukleLog(1);
            if (t.dataset.wsi === 'aliciler') yukleAlici();
            if (t.dataset.wsi === 'ozet') yukleOzet();
        });
    });

    var wsiChart = null;
    function yukleOzet(){
        fetchJson('/isletmeyonetim/whatsapp/ozet-data' + qs2).then(function(d){
            if (d.error) { document.getElementById('wsiOzetGrid').innerHTML = '<div style="color:#dc3545;">' + d.error + '</div>'; return; }
            var html = '';
            html += '<div class="wsi-stat-card info"><div class="wsi-stat-label">Bağlı Numara</div><div class="wsi-stat-value" style="font-size:18px;">' + escHtml(d.numara || '—') + '</div></div>';
            html += '<div class="wsi-stat-card"><div class="wsi-stat-label">Bugün Toplam</div><div class="wsi-stat-value">' + d.bugun.toplam + '</div><div class="wsi-stat-sub">✓' + d.bugun.basari + ' / ✗' + d.bugun.fail + ' / ⤵' + d.bugun.fallback + '</div></div>';
            html += '<div class="wsi-stat-card"><div class="wsi-stat-label">7 Gün</div><div class="wsi-stat-value">' + d.hafta.toplam + '</div><div class="wsi-stat-sub">' + d.hafta.basari + ' başarılı</div></div>';
            html += '<div class="wsi-stat-card"><div class="wsi-stat-label">30 Gün</div><div class="wsi-stat-value">' + d.ay.toplam + '</div><div class="wsi-stat-sub">' + d.ay.basari + ' başarılı</div></div>';
            var orClass = d.basariOrani >= 90 ? '' : (d.basariOrani >= 70 ? 'warn' : 'danger');
            html += '<div class="wsi-stat-card ' + orClass + '"><div class="wsi-stat-label">Haftalık Başarı</div><div class="wsi-stat-value">' + d.basariOrani + '%</div></div>';
            html += '<div class="wsi-stat-card"><div class="wsi-stat-label">Günlük Limit</div><div class="wsi-stat-value">' + d.gunluk_limit + '</div></div>';
            document.getElementById('wsiOzetGrid').innerHTML = html;

            // Chart
            if (typeof Chart !== 'undefined' && d.gunler) {
                var ctx = document.getElementById('wsiChart').getContext('2d');
                var labels = d.gunler.map(function(g){ return g.gun.substring(5); });
                if (wsiChart) wsiChart.destroy();
                wsiChart = new Chart(ctx, {
                    type:'bar',
                    data:{ labels:labels, datasets:[
                        { label:'Başarılı', data:d.gunler.map(function(g){return g.basari;}), backgroundColor:'#25D366'},
                        { label:'Başarısız', data:d.gunler.map(function(g){return g.fail;}), backgroundColor:'#dc3545'},
                        { label:"SMS'e Düştü", data:d.gunler.map(function(g){return g.fallback;}), backgroundColor:'#f0ad4e'}
                    ]},
                    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top'}}, scales:{x:{stacked:true},y:{stacked:true,beginAtZero:true}} }
                });
            }
        });
    }

    var wsiLogPage = 1;
    function yukleLog(p){
        if (p) wsiLogPage = p;
        var params = new URLSearchParams(qs2.replace(/^\?/, ''));
        params.set('page', wsiLogPage); params.set('per_page', 50);
        ['wsiLogDurum','wsiLogTelefon','wsiLogBaslangic','wsiLogBitis','wsiLogArama'].forEach(function(id){
            var v = document.getElementById(id).value;
            var key = {wsiLogDurum:'durum',wsiLogTelefon:'telefon',wsiLogBaslangic:'baslangic',wsiLogBitis:'bitis',wsiLogArama:'arama'}[id];
            if (v) params.set(key, v);
        });
        var tbody = document.querySelector('#wsiLogTable tbody');
        tbody.innerHTML = '<tr><td colspan="6"><span class="wsi-spinner"></span> Yükleniyor...</td></tr>';
        fetchJson('/isletmeyonetim/whatsapp/loglar-data?' + params.toString()).then(function(d){
            if (d.error) { tbody.innerHTML = '<tr><td colspan="6" style="color:#dc3545;">' + d.error + '</td></tr>'; return; }
            var rows = d.rows || [];
            if (rows.length === 0) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">Kayıt yok</td></tr>'; }
            else {
                tbody.innerHTML = rows.map(function(r){
                    var badge = DURUM_BADGE[r.durum] || 'gri';
                    return '<tr style="cursor:pointer;" data-id="' + r.id + '" data-mesaj="' + escHtml(r.mesaj || '') + '" data-hata="' + escHtml(r.hata || '') + '" data-tarih="' + escHtml(fmtDate(r.created_at)) + '" data-musteri="' + escHtml(r.musteri_adi || '') + '" data-telefon="' + escHtml(r.telefon) + '" data-durum="' + (DURUM_LABEL[r.durum] || r.durum) + '">'
                        + '<td>' + escHtml(fmtDate(r.created_at)) + '</td>'
                        + '<td>' + escHtml(r.musteri_adi || '—') + '</td>'
                        + '<td>' + escHtml(r.telefon) + '</td>'
                        + '<td><span class="wsi-badge ' + badge + '">' + (DURUM_LABEL[r.durum] || r.durum) + '</span></td>'
                        + '<td><div class="wsi-mesaj-trunc" title="' + escHtml(r.mesaj || '') + '">' + escHtml(r.mesaj || '') + '</div></td>'
                        + '<td style="color:#dc3545;font-size:11px;">' + escHtml(r.hata || '') + '</td>'
                        + '</tr>';
                }).join('');
                document.querySelectorAll('#wsiLogTable tbody tr').forEach(function(tr){
                    tr.addEventListener('click', function(){
                        var html = '<table style="width:100%;font-size:13px;"><tr><td style="padding:5px;color:#666;">Tarih</td><td style="padding:5px;">' + tr.dataset.tarih + '</td></tr>'
                            + '<tr><td style="padding:5px;color:#666;">Müşteri</td><td style="padding:5px;">' + (tr.dataset.musteri || '—') + '</td></tr>'
                            + '<tr><td style="padding:5px;color:#666;">Telefon</td><td style="padding:5px;">' + tr.dataset.telefon + '</td></tr>'
                            + '<tr><td style="padding:5px;color:#666;">Durum</td><td style="padding:5px;">' + tr.dataset.durum + '</td></tr>'
                            + (tr.dataset.hata ? '<tr><td style="padding:5px;color:#666;">Hata</td><td style="padding:5px;color:#dc3545;">' + tr.dataset.hata + '</td></tr>' : '')
                            + '</table>'
                            + '<h5 style="margin-top:14px;">Mesaj İçeriği</h5>'
                            + '<div style="background:#f7f9fc;padding:10px;border-radius:6px;white-space:pre-wrap;font-size:13px;">' + tr.dataset.mesaj + '</div>';
                        document.getElementById('wsiLogDetayBody').innerHTML = html;
                        document.getElementById('wsiLogDetayModal').classList.add('show');
                    });
                });
            }
            var pag = document.getElementById('wsiLogPagination');
            pag.innerHTML = '<button ' + (d.page<=1?'disabled':'') + ' onclick="window.__wsiLogP(' + (d.page-1) + ')">← Önceki</button>'
                + '<span style="padding:0 12px;color:#666;font-size:12px;">Toplam ' + d.toplam + ' — Sayfa ' + d.page + '/' + (d.son_sayfa || 1) + '</span>'
                + '<button ' + (d.page>=d.son_sayfa?'disabled':'') + ' onclick="window.__wsiLogP(' + (d.page+1) + ')">Sonraki →</button>';
        });
    }
    window.__wsiLogP = yukleLog;
    document.getElementById('wsiLogFiltreUygula').addEventListener('click', function(){ yukleLog(1); });
    document.getElementById('wsiLogFiltreSifirla').addEventListener('click', function(){
        ['wsiLogDurum','wsiLogTelefon','wsiLogBaslangic','wsiLogBitis','wsiLogArama'].forEach(function(id){ document.getElementById(id).value=''; });
        yukleLog(1);
    });

    function yukleAlici(){
        var tbody = document.querySelector('#wsiAliciTable tbody');
        tbody.innerHTML = '<tr><td colspan="7"><span class="wsi-spinner"></span> Yükleniyor...</td></tr>';
        fetchJson('/isletmeyonetim/whatsapp/aliciler-data' + qs2).then(function(d){
            if (d.error) { tbody.innerHTML = '<tr><td colspan="7" style="color:#dc3545;">' + d.error + '</td></tr>'; return; }
            var rows = d.rows || [];
            if (rows.length === 0) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;padding:30px;">Henüz mesaj göndermediniz</td></tr>'; return; }
            tbody.innerHTML = rows.map(function(r){
                return '<tr>'
                    + '<td><b>' + escHtml(r.musteri_adi || '—') + '</b></td>'
                    + '<td>' + escHtml(r.telefon) + '</td>'
                    + '<td>' + r.toplam + '</td>'
                    + '<td><span class="wsi-badge success">✓' + r.basari + '</span> <span class="wsi-badge fail">✗' + r.fail + '</span> <span class="wsi-badge fallback">⤵' + r.fallback + '</span></td>'
                    + '<td style="font-size:11px;color:#666;">' + escHtml(fmtDate(r.ilk_mesaj)) + '</td>'
                    + '<td style="font-size:11px;color:#666;">' + escHtml(fmtDate(r.son_mesaj)) + '</td>'
                    + '<td><button class="wsi-btn" data-tel="' + escHtml(r.telefon) + '" data-musteri="' + escHtml(r.musteri_adi || '') + '" style="padding:5px 10px;font-size:11px;">📋 Geçmiş</button></td>'
                    + '</tr>';
            }).join('');
            document.querySelectorAll('#wsiAliciTable button[data-tel]').forEach(function(b){
                b.addEventListener('click', function(){ aciAliciGecmis(b.dataset.tel, b.dataset.musteri); });
            });
        });
    }

    function aciAliciGecmis(telefon, musteri){
        document.getElementById('wsiAliciGecmisBaslik').textContent = '📋 ' + (musteri || telefon) + ' — Mesaj Geçmişi';
        document.getElementById('wsiAliciGecmisBody').innerHTML = '<span class="wsi-spinner"></span> Yükleniyor...';
        document.getElementById('wsiAliciGecmisModal').classList.add('show');
        fetchJson('/isletmeyonetim/whatsapp/alici/' + encodeURIComponent(telefon) + '/gecmis' + qs2).then(function(d){
            var rows = d.rows || [];
            if (rows.length === 0) { document.getElementById('wsiAliciGecmisBody').innerHTML = '<div style="text-align:center;color:#999;padding:20px;">Mesaj yok</div>'; return; }
            var html = '<div style="display:flex;flex-direction:column;gap:8px;max-height:65vh;overflow:auto;">';
            rows.forEach(function(r){
                var badge = DURUM_BADGE[r.durum] || 'gri';
                html += '<div style="background:#f7f9fc;border-left:3px solid #25D366;padding:9px 12px;border-radius:6px;">'
                    + '<div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:4px;">'
                    + '<span><b>' + escHtml(fmtDate(r.created_at)) + '</b>' + (r.randevu_id ? ' — Randevu #' + r.randevu_id : '') + '</span>'
                    + '<span class="wsi-badge ' + badge + '">' + (DURUM_LABEL[r.durum] || r.durum) + '</span></div>'
                    + '<div style="white-space:pre-wrap;font-size:13px;">' + escHtml(r.mesaj) + '</div>'
                    + (r.hata ? '<div style="margin-top:4px;color:#dc3545;font-size:11px;">Hata: ' + escHtml(r.hata) + '</div>' : '')
                    + '</div>';
            });
            html += '</div>';
            document.getElementById('wsiAliciGecmisBody').innerHTML = html;
        });
    }

    yukleOzet();

    // ───────── PAKET BÖLÜMÜ ─────────
    var wpktSecilenPaket = null;
    var wpktPeriyot = 'aylik';
    var WPKT_FIYAT = {
        pro: { aylik: 149, yillik: 1499 },
        premium: { aylik: 299, yillik: 2999 }
    };

    window.wpktSetPeriyot = function(p){
        wpktPeriyot = p;
        document.getElementById('wpktAylik').classList.toggle('active', p === 'aylik');
        document.getElementById('wpktYillik').classList.toggle('active', p === 'yillik');
        guncelleFiyatlar();
    };

    function guncelleFiyatlar(){
        var p = wpktPeriyot;
        if (p === 'aylik') {
            document.getElementById('wpktProFiyat').innerHTML = WPKT_FIYAT.pro.aylik + ' <small>TL/ay</small>';
            document.getElementById('wpktPremiumFiyat').innerHTML = WPKT_FIYAT.premium.aylik + ' <small>TL/ay</small>';
            document.getElementById('wpktProAylikInfo').textContent = '';
            document.getElementById('wpktPremiumAylikInfo').textContent = '';
        } else {
            var proAylikEsdeger = (WPKT_FIYAT.pro.yillik / 12).toFixed(0);
            var premAylikEsdeger = (WPKT_FIYAT.premium.yillik / 12).toFixed(0);
            document.getElementById('wpktProFiyat').innerHTML = WPKT_FIYAT.pro.yillik + ' <small>TL/yıl</small>';
            document.getElementById('wpktPremiumFiyat').innerHTML = WPKT_FIYAT.premium.yillik + ' <small>TL/yıl</small>';
            document.getElementById('wpktProAylikInfo').innerHTML = '≈ ' + proAylikEsdeger + ' TL/ay <span style="color:#25D366;font-weight:600;">— 2 ay bedava</span>';
            document.getElementById('wpktPremiumAylikInfo').innerHTML = '≈ ' + premAylikEsdeger + ' TL/ay <span style="color:#25D366;font-weight:600;">— 2 ay bedava</span>';
        }
    }

    window.wpktTalepAc = function(paket){
        wpktSecilenPaket = paket;
        var paketAd = paket === 'pro' ? 'Pro' : 'Premium';
        var fiyat = WPKT_FIYAT[paket][wpktPeriyot];
        var birim = wpktPeriyot === 'aylik' ? 'TL/ay' : 'TL/yıl';
        document.getElementById('wpktTalepBaslik').textContent = paketAd + ' Paket — ' + fiyat + ' ' + birim;
        document.getElementById('wpktTalepSonuc').innerHTML = '';
        document.getElementById('wpktIletisim').value = '';
        document.getElementById('wpktTalepModal').classList.add('show');
    };

    document.getElementById('wpktTalepGonder').addEventListener('click', function(){
        var btn = this;
        var iletisim = document.getElementById('wpktIletisim').value.trim();
        if (!iletisim) {
            document.getElementById('wpktTalepSonuc').innerHTML = '<span style="color:#dc3545;">Lütfen iletişim bilgisi girin.</span>';
            return;
        }
        btn.disabled = true; btn.textContent = 'Gönderiliyor...';
        var fd = new FormData();
        fd.append('paket', wpktSecilenPaket);
        fd.append('periyot', wpktPeriyot);
        fd.append('iletisim', iletisim);
        fd.append('_token', csrf);
        fetch('/isletmeyonetim/whatsapp/paket-talep' + qs2, {
            method:'POST', credentials:'same-origin',
            headers:{'X-CSRF-TOKEN':csrf}, body:fd
        }).then(function(r){ return r.json(); }).then(function(d){
            btn.disabled = false; btn.textContent = 'Talebi Gönder';
            if (d.ok) {
                document.getElementById('wpktTalepSonuc').innerHTML = '<span style="color:#1a7f3e;font-weight:600;">✓ ' + (d.mesaj || 'Talebiniz alındı.') + '</span>';
                setTimeout(function(){ document.getElementById('wpktTalepModal').classList.remove('show'); }, 2500);
            } else {
                document.getElementById('wpktTalepSonuc').innerHTML = '<span style="color:#dc3545;">' + (d.error || d.mesaj || 'Hata oluştu.') + '</span>';
            }
        }).catch(function(){
            btn.disabled = false; btn.textContent = 'Talebi Gönder';
            document.getElementById('wpktTalepSonuc').innerHTML = '<span style="color:#dc3545;">Bağlantı hatası. Tekrar deneyin.</span>';
        });
    });

    function yuklePaketDurum(){
        fetch('/isletmeyonetim/whatsapp/paket-durum' + qs2, {credentials:'same-origin'})
            .then(function(r){ return r.json(); }).then(function(d){
                if (d.error) return;
                var paket = d.paket || 'baslangic';
                var labels = { baslangic: 'Başlangıç (Ücretsiz)', pro: 'Pro', premium: 'Premium' };
                var ad = labels[paket] || paket;
                if (d.bitis && d.kalan_gun !== null) ad += ' — ' + d.kalan_gun + ' gün kaldı';
                document.getElementById('wpktCurrentName').textContent = ad;
                document.getElementById('wpktCurrentBadge').style.display = 'inline-block';

                // Kart vurgusu
                ['baslangic','pro','premium'].forEach(function(p){
                    var card = document.getElementById('wpktCard' + p.charAt(0).toUpperCase() + p.slice(1));
                    if (!card) return;
                    if (p === paket) {
                        card.classList.add('current');
                        if (!card.querySelector('.wpkt-current-tag')) {
                            var tag = document.createElement('div');
                            tag.className = 'wpkt-current-tag';
                            tag.textContent = '✓ MEVCUT PAKETİNİZ';
                            card.insertBefore(tag, card.firstChild);
                        }
                        var btn = card.querySelector('.wpkt-btn');
                        if (btn) {
                            btn.className = 'wpkt-btn wpkt-btn-current';
                            btn.textContent = 'Mevcut Paket';
                            btn.disabled = true;
                        }
                    }
                });
            });
    }

    guncelleFiyatlar();
    yuklePaketDurum();
})();
</script>
@endsection
