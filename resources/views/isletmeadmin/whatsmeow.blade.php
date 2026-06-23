@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif
@extends($_layout)
@section('content')
<style>
.wmw-wrap { max-width: 980px; margin: 24px auto; padding: 0 16px; }
.wmw-card { background: #fff; border-radius: 10px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 18px; }
.wmw-title { display: flex; align-items: center; gap: 10px; margin: 0 0 6px; font-size: 22px; font-weight: 700; color: #222; }
.wmw-badge { display: inline-block; padding: 3px 11px; border-radius: 99px; font-size: 12px; font-weight: 600; }
.wmw-badge.pilot { background: #fff3cd; color: #856404; }
.wmw-badge.ok { background: #d4edda; color: #155724; }
.wmw-badge.warn { background: #fff3cd; color: #856404; }
.wmw-badge.bad { background: #f8d7da; color: #721c24; }
.wmw-badge.info { background: #cce5ff; color: #004085; }
.wmw-subtitle { color: #666; margin: 4px 0 18px; font-size: 13px; }
.wmw-row { display: flex; gap: 18px; flex-wrap: wrap; }
.wmw-col { flex: 1 1 320px; }
.wmw-stat { display: flex; gap: 8px; align-items: center; padding: 6px 0; font-size: 14px; }
.wmw-stat-label { color: #888; min-width: 130px; }
.wmw-stat-val { font-weight: 600; color: #333; }
.wmw-btn { padding: 9px 16px; background: #25D366; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
.wmw-btn:hover { background: #1ebe57; }
.wmw-btn.secondary { background: #6c757d; }
.wmw-btn.danger { background: #dc3545; }
.wmw-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.wmw-qr-box { text-align: center; padding: 16px; background: #fafbfc; border-radius: 8px; }
.wmw-qr-box canvas { max-width: 280px; height: auto; }
.wmw-qr-help { color: #666; font-size: 13px; margin-top: 12px; line-height: 1.6; }
.wmw-qr-help ol { margin: 8px 0; padding-left: 20px; }
.wmw-test-form { display: grid; gap: 10px; margin-top: 12px; }
.wmw-test-form input, .wmw-test-form textarea { padding: 8px 11px; border: 1px solid #ced4da; border-radius: 5px; font-size: 13px; width: 100%; box-sizing: border-box; }
.wmw-test-form label { font-size: 12px; color: #666; font-weight: 600; }
.wmw-log { background: #1e1e1e; color: #e8e8e8; border-radius: 6px; padding: 12px; font-family: ui-monospace, "SF Mono", monospace; font-size: 12px; max-height: 220px; overflow: auto; }
.wmw-log .ok { color: #6dd56d; }
.wmw-log .err { color: #ff6b6b; }
.wmw-log .info { color: #79c0ff; }
.wmw-spin { display: inline-block; width: 12px; height: 12px; border: 2px solid #ddd; border-top-color: #25D366; border-radius: 50%; animation: wmwspin .8s linear infinite; vertical-align: middle; }
@keyframes wmwspin { to { transform: rotate(360deg); } }
.wmw-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
</style>

<div class="wmw-wrap">
  <div class="wmw-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
      <div>
        <h1 class="wmw-title">
          whatsmeow Pilot
          <span class="wmw-badge pilot">TEST</span>
        </h1>
        <div class="wmw-subtitle">
          Baileys'ten bağımsız ikinci WhatsApp bridge'i. Production akışını
          etkilemez — sadece bu salonun yeni bridge ile bağlantısını test eder.
        </div>
      </div>
      <div style="text-align:right;">
        <div class="wmw-stat-label" style="margin-bottom:4px;">Salon</div>
        <div class="wmw-stat-val">#{{ $isletme->id ?? '-' }} {{ $isletme->salon_adi ?? '' }}</div>
      </div>
    </div>
  </div>

  <div class="wmw-row">
    <div class="wmw-col">
      <div class="wmw-card">
        <h3 style="margin:0 0 10px;font-size:15px;color:#333;">Bridge Durumu</h3>
        <div class="wmw-stat">
          <span class="wmw-stat-label">Servis sağlığı</span>
          <span id="wmwHealth" class="wmw-stat-val">—</span>
        </div>
        <div class="wmw-stat">
          <span class="wmw-stat-label">Oturum durumu</span>
          <span id="wmwStatus" class="wmw-stat-val">—</span>
        </div>
        <div class="wmw-stat">
          <span class="wmw-stat-label">Bağlı numara</span>
          <span id="wmwPhone" class="wmw-stat-val">—</span>
        </div>
        <div class="wmw-stat">
          <span class="wmw-stat-label">Son hata</span>
          <span id="wmwLastErr" class="wmw-stat-val">—</span>
        </div>
        <div class="wmw-actions">
          <button class="wmw-btn" onclick="wmwBaslat()">Bağlan / QR Üret</button>
          <button class="wmw-btn secondary" onclick="wmwDurum()">Durumu Yenile</button>
          <button class="wmw-btn danger" onclick="wmwCikis()">Çıkış Yap (Oturum Sil)</button>
        </div>
      </div>
    </div>

    <div class="wmw-col">
      <div class="wmw-card">
        <h3 style="margin:0 0 10px;font-size:15px;color:#333;">QR Kodu</h3>
        <div class="wmw-qr-box">
          <img id="wmwQR" alt="" style="display:none; max-width:280px; width:100%; height:auto; image-rendering:pixelated;">
          <div id="wmwQrHint" style="color:#888;font-size:13px;margin-top:8px;">QR henüz üretilmedi. "Bağlan" butonuna basın.</div>
        </div>
        <div class="wmw-qr-help">
          <b>Nasıl bağlanır?</b>
          <ol>
            <li>"Bağlan / QR Üret" butonuna basın</li>
            <li>Salonun WhatsApp Business uygulamasını açın</li>
            <li>Sağ üst <b>3 nokta → Bağlı Cihazlar</b></li>
            <li><b>Cihaz Bağla</b> deyip ekrandaki QR'ı tarayın</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="wmw-card">
    <h3 style="margin:0 0 10px;font-size:15px;color:#333;">Test Mesajı Gönder</h3>
    <div class="wmw-subtitle">Bağlandıktan sonra burayı kullanın. Anti-burst için 12-30sn arası gecikme olabilir.</div>
    <div class="wmw-test-form">
      <label>Alıcı telefon (uluslararası formatta, örn. +905XXXXXXXXX)</label>
      <input type="text" id="wmwTo" placeholder="+90...">
      <label>Mesaj</label>
      <textarea id="wmwMsg" rows="3" placeholder="whatsmeow test mesajı">whatsmeow test mesajı — {{ date('H:i') }}</textarea>
      <div>
        <button class="wmw-btn" onclick="wmwGonder()">Test Mesajı Gönder</button>
      </div>
    </div>
  </div>

  <div class="wmw-card">
    <h3 style="margin:0 0 10px;font-size:15px;color:#333;">Olay Logu</h3>
    <div id="wmwLog" class="wmw-log"></div>
  </div>
</div>

<script>
const sube = new URLSearchParams(location.search).get('sube') || '';
const csrf = '{{ csrf_token() }}';

function log(text, cls = 'info') {
  const el = document.getElementById('wmwLog');
  const ts = new Date().toLocaleTimeString();
  el.innerHTML = `<div class="${cls}">[${ts}] ${text}</div>` + el.innerHTML;
}

async function api(method, path, body = null) {
  const opts = {
    method,
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
    credentials: 'same-origin',
  };
  if (body) {
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  }
  const url = path + (path.includes('?') ? '&' : '?') + 'sube=' + encodeURIComponent(sube);
  const r = await fetch(url, opts);
  let j;
  try { j = await r.json(); } catch(e) { j = {}; }
  return { status: r.status, body: j };
}

async function refreshHealth() {
  const r = await api('GET', '/isletmeyonetim/whatsmeow/health');
  const ok = r.status === 200 && r.body && r.body.status === 'ok';
  document.getElementById('wmwHealth').innerHTML = ok
    ? '<span class="wmw-badge ok">Bridge aktif</span>'
    : '<span class="wmw-badge bad">Bridge erişilemiyor</span>';
  return ok;
}

async function wmwDurum() {
  const r = await api('GET', '/isletmeyonetim/whatsmeow/durum');
  const b = r.body || {};
  const dur = b.status || '—';
  let badge = '<span class="wmw-badge warn">' + dur + '</span>';
  if (dur === 'connected') badge = '<span class="wmw-badge ok">connected</span>';
  else if (dur === 'qr-pending') badge = '<span class="wmw-badge info">qr-pending — QR taratılmayı bekliyor</span>';
  else if (dur === 'disconnected' || dur === 'logged-out') badge = '<span class="wmw-badge bad">' + dur + '</span>';
  document.getElementById('wmwStatus').innerHTML = badge;
  document.getElementById('wmwPhone').textContent = b.phone || '—';
  document.getElementById('wmwLastErr').textContent = b.lastError || '—';
  log('durum: ' + dur + (b.phone ? ' (phone: ' + b.phone + ')' : ''));
  // QR pending ise getir
  if (dur === 'qr-pending') {
    setTimeout(wmwQrGetir, 500);
  }
}

async function wmwBaslat() {
  log('bridge bağlatma başlatıldı...', 'info');
  const r = await api('POST', '/isletmeyonetim/whatsmeow/baslat');
  if (r.status === 200 || r.status === 202) {
    log('start OK: ' + (r.body.status || '?'), 'ok');
    if (r.body.qr) {
      drawQR(r.body.qr);
    } else {
      setTimeout(wmwQrGetir, 1000);
    }
    setTimeout(wmwDurum, 800);
  } else {
    log('start HATA: ' + JSON.stringify(r.body), 'err');
  }
}

async function wmwQrGetir() {
  const r = await api('GET', '/isletmeyonetim/whatsmeow/qr');
  if (r.body && r.body.qr) {
    drawQR(r.body.qr);
    log('QR alındı (' + r.body.qr.length + ' karakter)', 'ok');
  } else {
    log('QR henüz yok', 'info');
  }
}

function drawQR(text) {
  const img = document.getElementById('wmwQR');
  // QR'i image olarak ureten public API (no CDN dependency)
  img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=10&data=' + encodeURIComponent(text);
  img.style.display = 'inline-block';
  document.getElementById('wmwQrHint').textContent = 'Yukarıdaki QR\'ı tarat. ~40 saniye geçerli.';
}

async function wmwCikis() {
  if (!confirm('Oturumu silmek istiyor musun? Yeniden QR taratman gerekir.')) return;
  const r = await api('POST', '/isletmeyonetim/whatsmeow/cikis');
  log('logout: ' + JSON.stringify(r.body), r.status === 200 ? 'ok' : 'err');
  const img = document.getElementById('wmwQR');
  img.style.display = 'none';
  img.src = '';
  document.getElementById('wmwQrHint').textContent = 'Oturum silindi. "Bağlan" ile tekrar QR üretin.';
  setTimeout(wmwDurum, 500);
}

async function wmwGonder() {
  const to = document.getElementById('wmwTo').value.trim();
  const msg = document.getElementById('wmwMsg').value.trim();
  if (!to || !msg) { alert('Telefon ve mesaj zorunlu'); return; }
  const r = await api('POST', '/isletmeyonetim/whatsmeow/test-mesaj', { to, message: msg });
  if (r.status === 202 || r.status === 200) {
    log('mesaj kuyruğa eklendi: ' + to, 'ok');
  } else {
    log('mesaj HATA: ' + JSON.stringify(r.body), 'err');
  }
}

// İlk yükleme: health + durum
(async () => {
  const healthy = await refreshHealth();
  if (healthy) wmwDurum();

  // Durum auto-refresh her 5sn (QR pending iken bağlandığını yakalamak için)
  setInterval(() => {
    refreshHealth();
    wmwDurum();
  }, 5000);
})();
</script>
@endsection
