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

/* ───── Paket Bölümü ───── */
.wpkt-wrapper { margin: 30px 0; }
.wpkt-header { text-align: center; margin-bottom: 24px; }
.wpkt-header h2 { font-size: 28px; font-weight: 700; color: #222; margin: 0 0 8px; }
.wpkt-header p { color: #6c757d; font-size: 15px; max-width: 600px; margin: 0 auto; }
.wpkt-current { display:inline-block; margin-top:12px; padding:6px 14px; background:#e8f7ee; color:#1a7f3e; border-radius:99px; font-size:13px; font-weight:600; }

.wpkt-toggle { display: flex; justify-content: center; gap: 4px; margin-bottom: 28px; background: #f1f3f5; padding: 4px; border-radius: 99px; max-width: 360px; margin-left: auto; margin-right: auto; }
.wpkt-toggle button { flex: 1; padding: 10px 18px; border: none; background: transparent; color: #6c757d; font-weight: 600; font-size: 14px; cursor: pointer; border-radius: 99px; transition: all 0.2s; position:relative; }
.wpkt-toggle button.active { background: #fff; color: #25D366; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.wpkt-discount { display: inline-block; background: #25D366; color: #fff; padding: 2px 7px; border-radius: 99px; font-size: 10px; margin-left: 4px; font-weight: 700; }

.wpkt-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.wpkt-grid-2 { grid-template-columns: repeat(2, 1fr); max-width: 820px; margin: 0 auto; }
@media (max-width: 980px) { .wpkt-grid, .wpkt-grid-2 { grid-template-columns: 1fr; } }

.wpkt-card { background: #fff; border-radius: 16px; padding: 28px 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 2px solid transparent; position: relative; transition: all 0.3s; display: flex; flex-direction: column; }
.wpkt-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.wpkt-card.popular { border-color: #25D366; box-shadow: 0 4px 20px rgba(37,211,102,0.15); transform: scale(1.03); }
.wpkt-card.popular:hover { transform: scale(1.03) translateY(-4px); }
.wpkt-card.current { border-color: #1a7f3e; background: linear-gradient(180deg, #f0fdf4 0%, #fff 100%); }
.wpkt-popular-tag { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #25D366, #1ebe57); color: #fff; padding: 5px 14px; border-radius: 99px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; box-shadow: 0 2px 8px rgba(37,211,102,0.4); white-space: nowrap; z-index: 2; }
.wpkt-current-tag { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #1a7f3e; color: #fff; padding: 5px 14px; border-radius: 99px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; z-index: 2; }
/* Mevcut paket gosterilirken 'EN POPULER' rozeti gizlenir (kullanici zaten o pakette, ust uste ust uste gerek yok) */
.wpkt-card.popular.current .wpkt-popular-tag { display: none; }

.wpkt-tier-name { font-size: 22px; font-weight: 700; color: #222; margin-bottom: 6px; }
.wpkt-tier-desc { color: #6c757d; font-size: 13px; line-height: 1.5; min-height: 40px; margin-bottom: 18px; }

.wpkt-price-block { padding: 14px 0; border-top: 1px solid #f1f3f5; border-bottom: 1px solid #f1f3f5; margin-bottom: 18px; }
.wpkt-price { font-size: 36px; font-weight: 800; color: #222; line-height: 1; }
.wpkt-price small { font-size: 14px; font-weight: 500; color: #6c757d; }
.wpkt-price-aylik { font-size: 13px; color: #6c757d; margin-top: 6px; min-height: 18px; }

.wpkt-features { list-style: none; padding: 0; margin: 0 0 22px; flex: 1; }
.wpkt-features li { padding: 8px 0; padding-left: 26px; position: relative; font-size: 14px; color: #495057; line-height: 1.4; }
.wpkt-features li:before { content: "✓"; position: absolute; left: 0; color: #25D366; font-weight: 700; font-size: 16px; }
.wpkt-features li.no { color: #adb5bd; }
.wpkt-features li.no:before { content: "✕"; color: #dee2e6; }

.wpkt-btn { width: 100%; padding: 13px 20px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; }
.wpkt-btn-primary { background: linear-gradient(135deg, #25D366, #1ebe57); color: #fff; }
.wpkt-btn-primary:hover { box-shadow: 0 4px 12px rgba(37,211,102,0.4); transform: translateY(-1px); }
.wpkt-btn-outline { background: #fff; color: #25D366; border: 2px solid #25D366; }
.wpkt-btn-outline:hover { background: #25D366; color: #fff; }
.wpkt-btn-current { background: #f1f3f5; color: #6c757d; cursor: not-allowed; }

/* Deneme bandı — paket bölümünün üstünde */
.wpkt-deneme-band { background: linear-gradient(135deg, #25D366, #1ebe57); color: #fff; padding: 18px 24px; border-radius: 14px; margin: 0 0 24px; display: flex; align-items: center; gap: 18px; box-shadow: 0 4px 14px rgba(37,211,102,0.25); }
.wpkt-deneme-band.uyari { background: linear-gradient(135deg, #f0ad4e, #e89028); box-shadow: 0 4px 14px rgba(240,173,78,0.3); }
.wpkt-deneme-icon { font-size: 38px; }
.wpkt-deneme-text { flex: 1; }
.wpkt-deneme-baslik { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
.wpkt-deneme-detay { font-size: 13px; opacity: 0.95; }
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

                <!-- Telefon numarasi ile bagla — QR taratmakta sorun yasayanlar icin (ozellikle iPhone) -->
                <div style="margin-top:18px; padding-top:16px; border-top:1px dashed #cfd6de;">
                    <button type="button" id="wa-phone-toggle" class="btn-wa" style="background:#4b5563;">
                        📞 QR taratamıyor musunuz? Telefon numarasıyla bağlanın
                    </button>
                    <div id="wa-phone-box" style="display:none; margin-top:14px; padding:14px; background:#f7f9fc; border-radius:8px; text-align:left;">
                        <p style="margin:0 0 10px; color:#333; font-size:13px;">
                            <b>1.</b> Salonun WhatsApp Business numarasını başında <b>90</b> ile girin (örn: <code>905321234567</code>)<br>
                            <b>2.</b> "Kod Üret" butonuna basın<br>
                            <b>3.</b> Salonun telefonunda: <b>WA Business → Bağlı Cihazlar → Cihaz Bağla → Telefon numarasıyla bağla</b> → çıkan kodu girin
                        </p>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <input type="tel" id="wa-phone-input" placeholder="905321234567" style="flex:1; min-width:180px; padding:8px 11px; border:1px solid #ced4da; border-radius:5px; font-size:13px;">
                            <button type="button" id="wa-phone-submit" class="btn-wa">Kod Üret</button>
                        </div>
                        <div id="wa-phone-result" style="margin-top:12px; display:none; padding:14px; background:#e8f5e9; border-radius:6px; text-align:center;">
                            <div style="font-size:13px; color:#555; margin-bottom:6px;">Telefonda girilecek kod:</div>
                            <div id="wa-phone-code" style="font-size:24px; font-weight:700; letter-spacing:3px; color:#1a7f3e; font-family:ui-monospace, monospace;">—</div>
                            <div style="font-size:12px; color:#666; margin-top:6px;">Bu kod 60 saniye geçerlidir</div>
                        </div>
                        <div id="wa-phone-error" style="display:none; margin-top:10px; padding:10px; background:#fce4e4; color:#c62828; border-radius:6px; font-size:13px;"></div>
                    </div>
                </div>
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

<div class="wa-card" style="margin-top:18px;">
    <h3 style="margin-bottom:8px">🔗 İşletme Bağlantıları</h3>
    <p style="color:#555; margin-bottom:14px; font-size:13.5px; line-height:1.5;">
        Buraya girdiğiniz bağlantıları, müşteriye WhatsApp mesajı yazarken <b>tek tıkla</b> mesaja ekleyebilirsiniz.
        <b>Buton başlığını siz belirlersiniz</b> — Instagram, Facebook, web sitesi, kampanya… ne isterseniz yazın. Boş bıraktığınız butonlar görünmez.
    </p>

    <div style="display:grid; gap:16px; max-width:700px;">
        <div>
            <label class="wa-link-label">📍 Konum (Google Maps → Paylaş → Bağlantıyı kopyala)</label>
            <input type="url" id="wa-konum-link" class="wa-link-input" value="{{ $isletme->konum_linki ?? '' }}" placeholder="https://maps.app.goo.gl/...">
        </div>

        <div class="wa-link-row">
            <div style="flex:0 0 190px; min-width:150px;">
                <label class="wa-link-label">Buton Başlığı</label>
                <input type="text" id="wa-instagram-baslik" class="wa-link-input" maxlength="60" value="{{ $isletme->instagram_baslik ?? 'Instagram' }}" placeholder="Örn. Instagram">
            </div>
            <div style="flex:1; min-width:230px;">
                <label class="wa-link-label">Bağlantı</label>
                <input type="url" id="wa-instagram-link" class="wa-link-input" value="{{ $isletme->instagram_linki ?? '' }}" placeholder="https://instagram.com/kullaniciadi">
            </div>
        </div>

        <div class="wa-link-row">
            <div style="flex:0 0 190px; min-width:150px;">
                <label class="wa-link-label">Buton Başlığı</label>
                <input type="text" id="wa-web-baslik" class="wa-link-input" maxlength="60" value="{{ $isletme->web_baslik ?? 'Web Sitesi' }}" placeholder="Örn. Web Sitesi">
            </div>
            <div style="flex:1; min-width:230px;">
                <label class="wa-link-label">Bağlantı</label>
                <input type="url" id="wa-web-link" class="wa-link-input" value="{{ $isletme->web_linki ?? '' }}" placeholder="https://...">
            </div>
        </div>
    </div>

    <div style="margin-top:14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <button type="button" class="btn-wa" id="wa-konum-kaydet">Bağlantıları Kaydet</button>
        <span id="wa-konum-status" style="font-size:13px; display:none;"></span>
    </div>
    <input type="hidden" id="wa-konum-sube" value="{{ $isletme->id }}">
    <input type="hidden" id="wa-konum-token" value="{{ csrf_token() }}">
</div>
<style>
.wa-link-label { display:block; font-size:12.5px; font-weight:600; color:#333; margin-bottom:4px; }
.wa-link-input { width:100%; padding:9px 11px; border:1px solid #ced4da; border-radius:6px; font-size:13px; box-sizing:border-box; }
.wa-link-input:focus { border-color:#25D366; outline:none; box-shadow:0 0 0 3px rgba(37,211,102,.12); }
.wa-link-row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
</style>

<script>
(function(){
    var kBtn = document.getElementById('wa-konum-kaydet');
    if(kBtn){
        kBtn.addEventListener('click', function(){
            var konum = document.getElementById('wa-konum-link').value.trim();
            var insta = document.getElementById('wa-instagram-link').value.trim();
            var web   = document.getElementById('wa-web-link').value.trim();
            var igBaslik  = document.getElementById('wa-instagram-baslik').value.trim();
            var webBaslik = document.getElementById('wa-web-baslik').value.trim();
            var sube  = document.getElementById('wa-konum-sube').value;
            var token = document.getElementById('wa-konum-token').value;
            var st    = document.getElementById('wa-konum-status');
            kBtn.disabled = true;
            fetch('/isletmeyonetim/whatsapp/konum-kaydet', {
                method: 'POST',
                headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token, 'Accept':'application/json' },
                body: 'konum_linki=' + encodeURIComponent(konum)
                    + '&instagram_linki=' + encodeURIComponent(insta)
                    + '&web_linki=' + encodeURIComponent(web)
                    + '&instagram_baslik=' + encodeURIComponent(igBaslik)
                    + '&web_baslik=' + encodeURIComponent(webBaslik)
                    + '&sube=' + encodeURIComponent(sube) + '&_token=' + encodeURIComponent(token)
            }).then(function(r){
                // Sunucu JSON yerine HTML donerse (404/419/500) once durum kodunu yakala
                return r.text().then(function(t){
                    var res = null;
                    try { res = JSON.parse(t); } catch(e){}
                    return { status: r.status, res: res };
                });
            }).then(function(d){
                kBtn.disabled = false;
                st.style.display = 'block';
                if(d.res && d.res.ok){
                    st.style.color = '#1a7f3e';
                    st.textContent = '✓ Bağlantılar kaydedildi. Artık mesaj ekranında tek tıkla ekleyebilirsiniz.';
                    setTimeout(function(){ st.style.display='none'; }, 4000);
                } else if(d.res && d.res.mesaj){
                    st.style.color = '#dc3545';
                    st.textContent = d.res.mesaj;
                } else if(d.status === 419){
                    st.style.color = '#dc3545';
                    st.textContent = 'Oturum süresi doldu. Sayfayı yenileyip tekrar deneyin.';
                } else if(d.status === 404){
                    st.style.color = '#dc3545';
                    st.textContent = 'Kayıt adresi bulunamadı (404). Sistem güncellemesi gerekiyor olabilir.';
                } else {
                    st.style.color = '#dc3545';
                    st.textContent = 'Kaydedilemedi (hata kodu: ' + d.status + ').';
                }
            }).catch(function(){
                kBtn.disabled = false;
                st.style.display = 'block'; st.style.color = '#dc3545';
                st.textContent = 'Bağlantı hatası, tekrar deneyin.';
            });
        });
    }
})();
</script>

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
        return fetchJson('/isletmeyonetim/whatsapp/qr' + qs).then(function(res){
            if(res.status === 200 && res.body && res.body.qr){
                var q = res.body.qr;
                // SADECE data:image URI'sini direkt kullan. Diger her sey (wa.me URL,
                // whatsmeow ham string vs.) QR verisi olarak api.qrserver.com'a gonderilir.
                // whatsmeow QR ornek: "https://wa.me/settings/linked_devices#2@..."
                if(/^data:image\//.test(q)){
                    qrImg.src = q;
                } else {
                    qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=1&data=' + encodeURIComponent(q);
                }
                return true;
            }
            return false;
        }).catch(function(){ return false; });
    }

    function tick(){
        fetchJson('/isletmeyonetim/whatsapp/durum' + qs).then(function(res){
            var b = res.body || {};
            var s = b.status || 'not-initialized';
            lastStatus = s;
            setStatus(s);
            if(b.lastError){ lastErrorEl.textContent = b.lastError; lastErrorWrap.style.display='block'; }
            else { lastErrorWrap.style.display='none'; }

            // Gercekten bagli: status=connected VE telefon numarasi var.
            if(s === 'connected' && b.phone){
                showOnly('ok');
                phoneEl.textContent = b.phone;
                connectedAt.textContent = b.connectedAt ? new Date(b.connectedAt).toLocaleString('tr-TR') : '-';
            } else {
                // Her diger durumda QR olabilir (bridge disconnected/qr-timeout dese bile
                // yeni QR uretilmis olabilir). Once QR'i dene; gelirse goster.
                loadQr().then(function(gotQr){
                    if(gotQr){
                        showOnly('qr');
                    } else {
                        showOnly('off');
                    }
                });
            }
        }).catch(function(){
            setStatus('servis-kapali');
            showOnly('off');
        });
    }

    // ── Telefon numarasi ile bagla (QR alternatifi) ─────────────────────────
    var phoneToggleBtn = document.getElementById('wa-phone-toggle');
    var phoneBox       = document.getElementById('wa-phone-box');
    var phoneInput     = document.getElementById('wa-phone-input');
    var phoneSubmit    = document.getElementById('wa-phone-submit');
    var phoneResult    = document.getElementById('wa-phone-result');
    var phoneCode      = document.getElementById('wa-phone-code');
    var phoneError     = document.getElementById('wa-phone-error');

    if (phoneToggleBtn) {
        phoneToggleBtn.addEventListener('click', function(){
            phoneBox.style.display = (phoneBox.style.display === 'none') ? 'block' : 'none';
        });
    }
    if (phoneSubmit) {
        phoneSubmit.addEventListener('click', function(){
            var phone = (phoneInput.value || '').replace(/\D/g,'');
            if (phone.length < 11) { phoneError.style.display='block'; phoneError.textContent='Numarayı başında 90 olacak şekilde girin (ör: 905321234567).'; return; }
            phoneError.style.display='none';
            phoneResult.style.display='none';
            phoneSubmit.disabled = true;
            phoneSubmit.textContent = 'Üretiliyor…';

            // Bridge'de oturum acik degilken pair kodu uretilemez (409
            // pairing-not-ready). Salon "Bağlantı Başlat"a basmadan dogrudan
            // kod uretmeye kalkabilir; o yuzden once baslat, sonra kod iste.
            // WhatsApp el sikismasi ilk saniyelerde bitmemis olabilir diye
            // 409 gelirse bir kez daha denenir.
            function pairIstegi(){
                return fetch('/isletmeyonetim/whatsapp/pair-phone' + qs, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')},
                    credentials: 'same-origin',
                    body: JSON.stringify({phone: phone}),
                }).then(function(r){ return r.json().then(function(b){ return {ok:r.ok, body:b}; }); });
            }

            fetchJson('/isletmeyonetim/whatsapp/baslat' + qs, { method:'POST' })
                .catch(function(){ /* oturum zaten acikssa hatasi onemsiz */ })
                .then(pairIstegi)
                .then(function(res){
                    if (!res.ok && res.body && res.body.error === 'pairing-not-ready') {
                        phoneSubmit.textContent = 'Bağlantı bekleniyor…';
                        return new Promise(function(devam){ setTimeout(devam, 4000); }).then(pairIstegi);
                    }
                    return res;
                })
                .then(function(res){
                    if (res.ok && res.body.code) {
                        // Kodu 4-4 formatinda goster
                        var c = res.body.code.toString();
                        if (c.length === 8) c = c.substring(0,4) + '-' + c.substring(4);
                        phoneCode.textContent = c;
                        phoneResult.style.display = 'block';
                    } else {
                        var msg = 'Kod üretilemedi.';
                        if (res.body && res.body.error) {
                            if (res.body.error === 'already-paired') msg = 'Bu salon zaten bağlı görünüyor. Önce "Oturumu Kapat" yapın.';
                            else if (res.body.error === 'invalid-phone') msg = 'Numara formatı geçersiz. 905XXXXXXXXX şeklinde girin.';
                            else if (res.body.error === 'pairing-not-ready') msg = 'WhatsApp bağlantısı henüz hazır değil. Birkaç saniye bekleyip tekrar deneyin.';
                            else msg = 'Hata: ' + res.body.error;
                        }
                        phoneError.textContent = msg;
                        phoneError.style.display = 'block';
                    }
                })
                .catch(function(){
                    phoneError.textContent = 'İstek başarısız. Sunucuya ulaşılamadı.';
                    phoneError.style.display = 'block';
                })
                .finally(function(){
                    phoneSubmit.disabled = false;
                    phoneSubmit.textContent = 'Kod Üret';
                });
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

    // Uyarlanabilir polling: baglanana kadar 4 sn, baglandiktan sonra 15 sn;
    // sekme arka plandayken hic sorgulama (gorunur olunca devam eder).
    var lastStatus = null, waPollTimer = null;
    tick();
    (function scheduleTick(){
        waPollTimer = setTimeout(function(){
            if (!document.hidden) { tick(); }
            scheduleTick();
        }, lastStatus === 'connected' ? 15000 : 4000);
    })();
})();
</script>

<style>
.wkp-wrap { margin:30px 0 10px; }
.wkp-free-banner { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#e7f8ef 0%,#d6f3e3 100%); border:1.5px solid #b8ebcf; border-radius:18px; padding:18px 22px; margin-bottom:26px; box-shadow:0 6px 20px rgba(37,211,102,.10); }
.wkp-free-ic { font-size:34px; line-height:1; flex-shrink:0; }
.wkp-free-t1 { font-size:17px; font-weight:800; color:#12805a; letter-spacing:-.2px; }
.wkp-free-t2 { font-size:13.5px; color:#3f6b57; margin-top:3px; line-height:1.45; }
.wkp-free-pill { margin-left:auto; flex-shrink:0; align-self:center; background:#12805a; color:#fff; font-weight:700; font-size:12px; padding:7px 14px; border-radius:20px; white-space:nowrap; }
.wkp-header { text-align:center; margin-bottom:22px; }
.wkp-header h2 { font-size:26px; font-weight:800; color:#1f2937; margin:0 0 8px; letter-spacing:-.4px; }
.wkp-header p { color:#6b7280; font-size:14.5px; max-width:620px; margin:0 auto; line-height:1.5; }
.wkp-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
@media (max-width:900px){ .wkp-grid{ grid-template-columns:repeat(2,1fr);} }
@media (max-width:560px){ .wkp-grid{ grid-template-columns:1fr;} }
.wkp-card { position:relative; background:#fff; border:1.5px solid #eceff3; border-radius:18px; padding:24px 20px 20px; text-align:center; transition:all .18s; display:flex; flex-direction:column; }
.wkp-card:hover { transform:translateY(-4px); box-shadow:0 16px 34px rgba(17,24,39,.10); border-color:#c9efd9; }
.wkp-card.best { border-color:#25D366; box-shadow:0 10px 30px rgba(37,211,102,.18); }
.wkp-tag { position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:linear-gradient(135deg,#25D366,#12b455); color:#fff; font-size:11px; font-weight:800; letter-spacing:.4px; padding:5px 14px; border-radius:20px; white-space:nowrap; box-shadow:0 4px 12px rgba(37,211,102,.4); }
.wkp-adet { font-size:26px; font-weight:800; color:#111827; letter-spacing:-.5px; }
.wkp-adet span { font-size:14px; font-weight:600; color:#8a94a6; }
.wkp-birim { font-size:12.5px; color:#8a94a6; margin-top:2px; }
.wkp-fiyat { font-size:34px; font-weight:800; color:#12805a; margin:16px 0 2px; line-height:1; letter-spacing:-1px; }
.wkp-fiyat small { font-size:16px; font-weight:600; color:#8a94a6; }
.wkp-tasarruf { display:inline-block; margin:8px auto 0; min-height:24px; font-size:12.5px; font-weight:700; color:#12805a; background:#e7f8ef; border-radius:20px; padding:4px 12px; }
.wkp-tasarruf.bos { background:transparent; color:transparent; }
.wkp-btn2 { margin-top:18px; width:100%; padding:12px; border:0; border-radius:12px; font-weight:700; font-size:14px; cursor:pointer; transition:all .15s; background:#f3f4f6; color:#374151; }
.wkp-btn2:hover { background:#e5e7eb; }
.wkp-card.best .wkp-btn2 { background:linear-gradient(135deg,#25D366,#12b455); color:#fff; box-shadow:0 8px 18px rgba(37,211,102,.32); }
.wkp-card.best .wkp-btn2:hover { transform:translateY(-1px); box-shadow:0 10px 22px rgba(37,211,102,.42); }
@media (max-width:600px){ .wkp-free-banner{ flex-wrap:wrap; padding:16px; } .wkp-free-pill{ margin-left:0; } }
</style>

<div class="wkp-wrap">
    @php
        $_kBakiye = (int) ($isletme->whatsapp_kontor ?? 0);
        $_kDonem  = \App\Services\KontorServisi::kontorlusDonemMi($isletme);
        // Salon-ozel deneme bitisi (yoksa global fallback)
        $_denemeBitisRaw = $isletme->whatsapp_deneme_bitis ?? '2026-08-31';
        $_denemeBitis = \Carbon\Carbon::parse($_denemeBitisRaw);
        $_aylarTr = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
        $_trTarih = function($t) use ($_aylarTr) { return $t->format('d') . ' ' . $_aylarTr[(int)$t->format('n')] . ' ' . $t->format('Y'); };
        $_denemeBitisMetin = $_trTarih($_denemeBitis);
        $_kontorluBaslangicMetin = $_trTarih($_denemeBitis->copy()->addDay());
    @endphp
    @if(!$_kDonem)
    <div class="wkp-free-banner">
        <div class="wkp-free-ic">🎉</div>
        <div>
            <div class="wkp-free-t1">{{ $_denemeBitisMetin }} tarihine kadar WhatsApp tamamen ÜCRETSİZ</div>
            <div class="wkp-free-t2"><b>{{ $_kontorluBaslangicMetin }}</b> tarihinden itibaren kontörlü sisteme geçilecek — <b>1 mesaj = 1 kontör</b>. Şimdiden paketleri inceleyip hazırlanabilirsiniz.</div>
        </div>
        <div class="wkp-free-pill">ŞU AN ÜCRETSİZ</div>
    </div>
    @else
    <div class="wkp-free-banner" style="{{ $_kBakiye <= 500 ? 'background:linear-gradient(135deg,#fdeaea,#fbdada);border-color:#f5b5b5;' : ($_kBakiye <= 1000 ? 'background:linear-gradient(135deg,#fff4e0,#ffe9c7);border-color:#f5d59a;' : '') }}">
        <div class="wkp-free-ic">{{ $_kBakiye <= 0 ? '⛔' : ($_kBakiye <= 500 ? '⚠️' : '💰') }}</div>
        <div>
            <div class="wkp-free-t1" style="{{ $_kBakiye <= 500 ? 'color:#b91c1c;' : ($_kBakiye <= 1000 ? 'color:#9a6a00;' : '') }}">Kontör bakiyeniz: {{ number_format($_kBakiye,0,',','.') }}</div>
            <div class="wkp-free-t2">
                @if($_kBakiye <= 0)
                    Kontörünüz bitti — hatırlatma ve mesajlar müşteri/danışanlarınıza <b>gitmiyor</b>. Hemen kontör alın.
                @elseif($_kBakiye <= 500)
                    Kontörünüz azaldı, çok yakında bitecek. Mesajlarınız durmadan kontör alın.
                @else
                    1 mesaj = 1 kontör. Bittiğinde WhatsApp mesajları durur.
                @endif
            </div>
        </div>
        <div class="wkp-free-pill" style="{{ $_kBakiye <= 500 ? 'background:#b91c1c;' : ($_kBakiye <= 1000 ? 'background:#9a6a00;' : '') }}">{{ number_format($_kBakiye,0,',','.') }} KONTÖR</div>
    </div>
    @endif

    <div class="wkp-header">
        <h2>WhatsApp Kontör Paketleri</h2>
        <p>1 mesaj = 1 kontör. Randevu hatırlatma, iptal/güncelleme bildirimi ve manuel mesajlar kontörden düşer. Çok alırsanız tanesi daha ucuza gelir.</p>
    </div>

    <div class="wkp-grid">
        <div class="wkp-card">
            <div class="wkp-adet">10.000 <span>kontör</span></div>
            <div class="wkp-birim">tanesi 0,285 TL</div>
            <div class="wkp-fiyat">2.850 <small>TL</small></div>
            <div class="wkp-tasarruf bos">—</div>
            <button class="wkp-btn2" onclick="wpktTalepAc('kontor_10000')">Kontör Al</button>
        </div>
        <div class="wkp-card">
            <div class="wkp-adet">20.000 <span>kontör</span></div>
            <div class="wkp-birim">tanesi 0,265 TL</div>
            <div class="wkp-fiyat">5.300 <small>TL</small></div>
            <div class="wkp-tasarruf">400 TL avantaj</div>
            <button class="wkp-btn2" onclick="wpktTalepAc('kontor_20000')">Kontör Al</button>
        </div>
        <div class="wkp-card">
            <div class="wkp-adet">40.000 <span>kontör</span></div>
            <div class="wkp-birim">tanesi 0,245 TL</div>
            <div class="wkp-fiyat">9.800 <small>TL</small></div>
            <div class="wkp-tasarruf">1.600 TL avantaj</div>
            <button class="wkp-btn2" onclick="wpktTalepAc('kontor_40000')">Kontör Al</button>
        </div>
        <div class="wkp-card">
            <div class="wkp-adet">60.000 <span>kontör</span></div>
            <div class="wkp-birim">tanesi 0,230 TL</div>
            <div class="wkp-fiyat">13.800 <small>TL</small></div>
            <div class="wkp-tasarruf">3.300 TL avantaj</div>
            <button class="wkp-btn2" onclick="wpktTalepAc('kontor_60000')">Kontör Al</button>
        </div>
        <div class="wkp-card">
            <div class="wkp-adet">80.000 <span>kontör</span></div>
            <div class="wkp-birim">tanesi 0,213 TL</div>
            <div class="wkp-fiyat">17.000 <small>TL</small></div>
            <div class="wkp-tasarruf">5.800 TL avantaj</div>
            <button class="wkp-btn2" onclick="wpktTalepAc('kontor_80000')">Kontör Al</button>
        </div>
        <div class="wkp-card best">
            <div class="wkp-tag">⭐ EN AVANTAJLI · %30</div>
            <div class="wkp-adet">100.000 <span>kontör</span></div>
            <div class="wkp-birim">tanesi 0,200 TL</div>
            <div class="wkp-fiyat">20.000 <small>TL</small></div>
            <div class="wkp-tasarruf">8.500 TL avantaj</div>
            <button class="wkp-btn2" onclick="wpktTalepAc('kontor_100000')">Kontör Al</button>
        </div>
    </div>

    <p style="text-align:center;color:#9ca3af;font-size:12.5px;margin-top:18px;">
        Ödeme sonrası kontörünüz manuel olarak yüklenir. "Kontör Al" ile talebinizi bırakın, en kısa sürede sizinle iletişime geçelim.
    </p>
</div>

<div class="wsi-modal" id="wpktTalepModal">
    <div class="wsi-modal-content" style="max-width:480px;">
        <div class="wsi-modal-header">
            <h4 style="margin:0;" id="wpktTalepBaslik">Paket Yükseltme Talebi</h4>
            <span class="wsi-modal-close" onclick="document.getElementById('wpktTalepModal').classList.remove('show')">×</span>
        </div>
        <div id="wpktTalepBody">
            <p style="color:#6c757d;font-size:14px;line-height:1.5;">Bu paket için talebinizi bırakıyorsunuz. Onaylarsanız yetkilimize bildirim gider ve ödeme + kontör yükleme için sizinle iletişime geçilir.</p>
            <label style="font-size:13px;color:#444;font-weight:600;margin-top:14px;display:block;">İletişim / Not <span style="color:#94a3b8;font-weight:500;">(opsiyonel)</span></label>
            <input type="text" id="wpktIletisim" style="width:100%;padding:10px;border:1px solid #ced4da;border-radius:6px;margin-top:6px;font-size:14px;" placeholder="örn. bugün ödemek istiyorum / 0555 123 45 67">
            <button id="wpktTalepGonder" class="wpkt-btn2" style="margin-top:16px;background:linear-gradient(135deg,#25D366,#12b455);color:#fff;">Talep Gönder</button>
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

    // ───────── KONTÖR PAKET BÖLÜMÜ ─────────
    var wpktSecilenPaket = null;
    var WKP_PAKETLER = {
        kontor_10000:  { ad:'10.000 Kontör',  fiyat:'2.850 TL' },
        kontor_20000:  { ad:'20.000 Kontör',  fiyat:'5.300 TL' },
        kontor_40000:  { ad:'40.000 Kontör',  fiyat:'9.800 TL' },
        kontor_60000:  { ad:'60.000 Kontör',  fiyat:'13.800 TL' },
        kontor_80000:  { ad:'80.000 Kontör',  fiyat:'17.000 TL' },
        kontor_100000: { ad:'100.000 Kontör', fiyat:'20.000 TL' }
    };

    window.wpktTalepAc = function(key){
        wpktSecilenPaket = key;
        var p = WKP_PAKETLER[key] || { ad:key, fiyat:'' };
        document.getElementById('wpktTalepBaslik').textContent = p.ad + ' — ' + p.fiyat;
        document.getElementById('wpktTalepSonuc').innerHTML = '';
        document.getElementById('wpktIletisim').value = '';
        document.getElementById('wpktTalepModal').classList.add('show');
    };

    document.getElementById('wpktTalepGonder').addEventListener('click', function(){
        var btn = this;
        var sonuc = document.getElementById('wpktTalepSonuc');
        var iletisim = document.getElementById('wpktIletisim').value.trim(); // opsiyonel
        btn.disabled = true; btn.textContent = 'Gönderiliyor...';

        var bitti = false;
        function finish(mesaj, ok){
            if (bitti) return; bitti = true;
            btn.disabled = false; btn.textContent = 'Talep Gönder';
            sonuc.innerHTML = ok
                ? '<span style="color:#1a7f3e;font-weight:600;">✓ ' + mesaj + '</span>'
                : '<span style="color:#dc3545;">' + mesaj + '</span>';
            if (ok) setTimeout(function(){ document.getElementById('wpktTalepModal').classList.remove('show'); }, 2600);
        }
        // Bildirim WA+SMS senkron gittigi icin yavas olabilir. 9 sn sonra iyimser kapat —
        // talep sunucuda islenmeye devam eder, buton asla takili kalmaz.
        var zamanAsimi = setTimeout(function(){
            finish('Talebiniz alındı, en kısa sürede sizinle iletişime geçeceğiz. 🙌', true);
        }, 12000);

        // csrf/qs2 bu kapsamda tanimsiz olabiliyor (ReferenceError) — token'i Blade'den, sube'yi URL'den al.
        var _tkn = '{{ csrf_token() }}';
        var _qs  = location.search || '';
        var fd = new FormData();
        fd.append('paket', wpktSecilenPaket);
        fd.append('iletisim', iletisim);
        fd.append('_token', _tkn);
        fetch('/isletmeyonetim/whatsapp/paket-talep' + _qs, {
            method:'POST', credentials:'same-origin',
            headers:{'X-CSRF-TOKEN':_tkn, 'Accept':'application/json'}, body:fd
        }).then(function(r){
            return r.text().then(function(t){ var j=null; try{ j=JSON.parse(t); }catch(e){} return {status:r.status, j:j}; });
        }).then(function(res){
            clearTimeout(zamanAsimi);
            if (res.j && res.j.ok)          finish(res.j.mesaj || 'Talebiniz alındı.', true);
            else if (res.j && res.j.mesaj)  finish(res.j.mesaj, false);
            else if (res.status === 404)    finish('Sistem güncelleniyor, birkaç dakika sonra tekrar deneyin.', false);
            else                            finish('Talebiniz alındı, sizinle iletişime geçeceğiz. 🙌', true);
        }).catch(function(){
            clearTimeout(zamanAsimi);
            finish('Talebiniz alındı, sizinle iletişime geçeceğiz. 🙌', true);
        });
    });

    function yuklePaketDurum(){
        fetch('/isletmeyonetim/whatsapp/paket-durum' + qs2, {credentials:'same-origin'})
            .then(function(r){ return r.json(); }).then(function(d){
                if (d.error) return;
                var paket = d.paket || 'baslangic';
                var labels = { baslangic: 'Başlangıç (Ücretsiz)', pro: 'WhatsApp Hatırlatma', premium: 'WhatsApp Hatırlatma' };

                // Deneme bandı (varsa) — başlığın hemen üstüne
                if (d.deneme && d.bitis) {
                    var existing = document.getElementById('wpktDenemeBand');
                    if (!existing) {
                        var band = document.createElement('div');
                        band.id = 'wpktDenemeBand';
                        band.className = 'wpkt-deneme-band';
                        var header = document.querySelector('.wpkt-header');
                        if (header) header.parentNode.insertBefore(band, header);
                        existing = band;
                    }
                    var kalan = d.kalan_gun !== null ? d.kalan_gun : '?';
                    var renkSinif = (d.kalan_gun !== null && d.kalan_gun <= 7) ? 'uyari' : '';
                    existing.className = 'wpkt-deneme-band ' + renkSinif;
                    existing.innerHTML = '<div class="wpkt-deneme-icon">🎁</div>'
                        + '<div class="wpkt-deneme-text">'
                        + '<div class="wpkt-deneme-baslik">Ücretsiz Deneme Aktif — <b>' + (labels[paket] || paket) + '</b></div>'
                        + '<div class="wpkt-deneme-detay">📅 Başlangıç: <b>' + (d.baslangic || '—') + '</b> &nbsp;·&nbsp; Bitiş: <b>' + d.bitis + '</b> &nbsp;·&nbsp; <b>' + kalan + ' gün kaldı</b></div>'
                        + '</div>';
                }

                var ad = labels[paket] || paket;
                if (d.bitis && d.kalan_gun !== null) ad += ' — ' + d.kalan_gun + ' gün kaldı';
                if (d.deneme) ad += ' (Deneme)';
                document.getElementById('wpktCurrentName').textContent = ad;
                document.getElementById('wpktCurrentBadge').style.display = 'inline-block';

                // Kart vurgusu: hangi pakettese o kartı işaretle
                var aktifKartId = (paket === 'pro' || paket === 'premium') ? 'wpktCardPro' : 'wpktCardBaslangic';
                var card = document.getElementById(aktifKartId);
                if (card) {
                    card.classList.add('current');
                    if (!card.querySelector('.wpkt-current-tag')) {
                        var tag = document.createElement('div');
                        tag.className = 'wpkt-current-tag';
                        tag.textContent = d.deneme ? '🎁 DENEME — ' + (d.kalan_gun || 0) + ' GÜN KALDI' : '✓ MEVCUT PAKETİNİZ';
                        card.insertBefore(tag, card.firstChild);
                    }
                    var btn = card.querySelector('.wpkt-btn');
                    if (btn) {
                        btn.className = 'wpkt-btn wpkt-btn-current';
                        btn.textContent = d.deneme ? 'Deneme Aktif' : 'Mevcut Paket';
                        btn.disabled = true;
                    }
                }
            });
    }

    // Kontör modeli: eski abonelik durum/fiyat JS'i (yuklePaketDurum/guncelleFiyatlar) artık çağrılmıyor.
})();
</script>

@if($_kDonem && $_kBakiye <= 1000)
{{-- Agresif kontör uyarısı — SADECE kontörlü dönemde (1 Eylül+). 1000→günde1, 500→günde3, 0→her seferinde --}}
<script>
(function(){
    var bakiye = {{ $_kBakiye }};
    var seviye = bakiye <= 0 ? 'bitti' : (bakiye <= 500 ? 'kritik' : 'dusuk');
    var limit  = seviye === 'bitti' ? 99 : (seviye === 'kritik' ? 3 : 1);
    var bugun = new Date().toISOString().slice(0,10);
    var key = 'wa_kontor_uyari_{{ $isletme->id }}';
    var kayit = {}; try { kayit = JSON.parse(localStorage.getItem(key) || '{}'); } catch(e){}
    if (kayit.tarih !== bugun) kayit = { tarih: bugun, sayi: 0 };
    if (kayit.sayi >= limit) return;
    function goster(){
        if (typeof swal !== 'function') return false;
        kayit.sayi++; try { localStorage.setItem(key, JSON.stringify(kayit)); } catch(e){}
        var tip, baslik, metin;
        if (seviye === 'bitti')       { tip='error';   baslik='⛔ WhatsApp Kontörünüz Bitti!'; metin='Hatırlatma ve mesajlar müşteri/danışanlarınıza GİTMİYOR. Hemen kontör satın alın, yoksa randevu hatırlatmaları çalışmaz.'; }
        else if (seviye === 'kritik') { tip='warning'; baslik='⚠️ Kontörünüz Kritik! (' + bakiye + ' kaldı)'; metin='Çok yakında bitecek ve mesajlarınız duracak. Şimdi kontör yükleyin.'; }
        else                          { tip='warning'; baslik='Kontörünüz Azalıyor (' + bakiye + ')'; metin='Kesinti yaşamamak için kontör almayı düşünün.'; }
        swal({ type:tip, title:baslik, text:metin, showCancelButton:true, confirmButtonText:'Kontör Al ⬇', cancelButtonText:'Daha Sonra', confirmButtonColor:'#25D366', confirmButtonClass:'btn btn-success', cancelButtonClass:'btn btn-secondary' })
        .then(function(r){ if (r && r.value) { var g=document.querySelector('.wkp-grid'); if(g) g.scrollIntoView({behavior:'smooth'}); } });
        return true;
    }
    var t = setInterval(function(){ if (goster()) clearInterval(t); }, 400);
    setTimeout(function(){ clearInterval(t); }, 8000);
})();
</script>
@endif
@endsection
