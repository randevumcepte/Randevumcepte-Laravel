@extends('layout.layout_sistemadmin')
@section('content')
<style>
.wa-panel { padding: 20px; }
.wa-tabs { display:flex; gap:8px; margin-bottom:20px; border-bottom:2px solid #e3e8f0; }
.wa-tab { padding:10px 20px; cursor:pointer; border-radius:6px 6px 0 0; font-weight:600; color:#666; transition:all .2s; }
.wa-tab:hover { background:#f7f9fc; }
.wa-tab.active { background:#25D366; color:#fff; }
.wa-section { display:none; }
.wa-section.active { display:block; }

.wa-stat-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:24px; }
.wa-stat-card { background:#fff; border-radius:10px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,.05); border-left:4px solid #25D366; }
.wa-stat-card.warn { border-left-color:#f0ad4e; }
.wa-stat-card.danger { border-left-color:#dc3545; }
.wa-stat-card.info { border-left-color:#0099ff; }
.wa-stat-label { color:#777; font-size:13px; margin-bottom:6px; }
.wa-stat-value { font-size:28px; font-weight:700; color:#222; }
.wa-stat-sub { color:#999; font-size:12px; margin-top:4px; }

.wa-table { width:100%; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); border-collapse:collapse; }
.wa-table th, .wa-table td { padding:10px 12px; text-align:left; border-bottom:1px solid #f0f3f7; font-size:13px; }
.wa-table th { background:#f7f9fc; font-weight:600; color:#333; font-size:12px; text-transform:uppercase; letter-spacing:0.3px; }
.wa-table tr:hover { background:#fafbfc; }
.wa-table tbody tr.clickable { cursor:pointer; }

.wa-badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; }
.wa-badge.connected { background:#d4edda; color:#155724; }
.wa-badge.disconnected { background:#f8d7da; color:#721c24; }
.wa-badge.qr-pending { background:#fff3cd; color:#856404; }
.wa-badge.banned-or-loggedout, .wa-badge.auto-paused-ban-risk, .wa-badge.rate-limited { background:#f8d7da; color:#721c24; }
.wa-badge.connecting { background:#cce5ff; color:#004085; }
.wa-badge.gri { background:#e9ecef; color:#495057; }
.wa-badge.success { background:#d4edda; color:#155724; }
.wa-badge.fail { background:#f8d7da; color:#721c24; }
.wa-badge.fallback { background:#fff3cd; color:#856404; }
.wa-badge.queued { background:#cce5ff; color:#004085; }

.wa-filter { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; align-items:flex-end; }
.wa-filter input, .wa-filter select { padding:8px 10px; border:1px solid #ced4da; border-radius:6px; font-size:13px; }
.wa-filter label { display:block; font-size:11px; color:#666; margin-bottom:4px; font-weight:600; }
.wa-filter .group { flex: 0 0 auto; }
.wa-btn { padding:8px 16px; background:#25D366; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600; }
.wa-btn:hover { background:#1ebe57; }
.wa-btn.secondary { background:#6c757d; }
.wa-btn.secondary:hover { background:#5a6268; }

.wa-pagination { display:flex; gap:6px; justify-content:center; margin-top:16px; align-items:center; }
.wa-pagination button { padding:6px 12px; border:1px solid #dee2e6; background:#fff; border-radius:4px; cursor:pointer; font-size:13px; }
.wa-pagination button:hover { background:#f7f9fc; }
.wa-pagination button:disabled { opacity:0.4; cursor:not-allowed; }
.wa-pagination .info { padding:0 12px; color:#666; font-size:13px; }

.wa-mesaj-truncate { max-width:380px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

.wa-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
.wa-modal.show { display:flex; }
.wa-modal-content { background:#fff; border-radius:10px; padding:24px; max-width:600px; width:90%; max-height:90vh; overflow:auto; }
.wa-modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.wa-modal-close { cursor:pointer; font-size:24px; color:#999; }

#waChart { max-height:300px; }
.wa-chart-container { background:#fff; border-radius:10px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.05); margin-bottom:20px; }

.wa-btn-mini { display:inline-block; padding:5px 10px; border-radius:5px; font-size:11px; font-weight:600; text-decoration:none; border:1px solid #dee2e6; background:#fff; color:#333; cursor:pointer; }
.wa-btn-mini:hover { background:#f7f9fc; }
.wa-btn-mini-primary { background:#25D366; color:#fff; border-color:#25D366; }
.wa-btn-mini-primary:hover { background:#1ebe57; color:#fff; }

.wa-spinner { display:inline-block; width:16px; height:16px; border:2px solid #ddd; border-top-color:#25D366; border-radius:50%; animation:waspin 0.8s linear infinite; }
@keyframes waspin { to { transform:rotate(360deg); } }
</style>

<div class="wa-panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;color:#222;">📱 WhatsApp Yönetim Paneli</h2>
        <div>
            <button class="wa-btn secondary" id="waRefreshAll">⟳ Yenile</button>
            <span id="waLastUpdate" style="color:#999;font-size:12px;margin-left:10px;"></span>
        </div>
    </div>

    <div class="wa-tabs">
        <div class="wa-tab active" data-tab="dashboard">📊 Özet</div>
        <div class="wa-tab" data-tab="salonlar">🏢 Salonlar</div>
        <div class="wa-tab" data-tab="loglar">📨 Mesaj Logları</div>
        <div class="wa-tab" data-tab="grafik">📈 Grafik</div>
    </div>

    {{-- DASHBOARD --}}
    <div class="wa-section active" id="section-dashboard">
        <div class="wa-stat-grid" id="waStatsGrid">
            <div class="wa-stat-card"><div class="wa-stat-label">Yükleniyor...</div><div class="wa-stat-value">—</div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
            <div class="wa-chart-container">
                <h4 style="margin-top:0;">Mesaj Türü Dağılımı (son 30 gün)</h4>
                <table class="wa-table" id="tipDagilimTable">
                    <thead><tr><th>Tür</th><th>Toplam</th><th>Başarı</th><th>Fail</th><th>SMS↓</th></tr></thead>
                    <tbody><tr><td colspan="5">Yükleniyor...</td></tr></tbody>
                </table>
            </div>
            <div class="wa-chart-container">
                <h4 style="margin-top:0;">En Çok Mesaj Atan Salonlar (Top 10, son 30 gün)</h4>
                <table class="wa-table" id="topSalonTable">
                    <thead><tr><th>#</th><th>Salon</th><th>Mesaj</th></tr></thead>
                    <tbody><tr><td colspan="3">Yükleniyor...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SALONLAR --}}
    <div class="wa-section" id="section-salonlar">
        <div class="wa-filter">
            <div class="group">
                <label>Durum</label>
                <select id="filterSalonDurum">
                    <option value="">Tümü</option>
                    <option value="connected">Bağlı</option>
                    <option value="disconnected">Bağlantı Kesildi</option>
                    <option value="qr-pending">QR Bekliyor</option>
                    <option value="banned-or-loggedout">Ban/Çıkış</option>
                    <option value="auto-paused-ban-risk">Oto-Durdu</option>
                </select>
            </div>
            <div class="group">
                <label>Arama (Salon Adı)</label>
                <input type="text" id="filterSalonArama" placeholder="Ara...">
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="wa-table" id="salonTable">
                <thead><tr>
                    <th>ID</th><th>Salon</th><th>Sağlayıcı</th><th>Numara</th><th>Durum</th>
                    <th>Limit (gün)</th><th>Bugün</th><th>Hafta</th>
                    <th>Bağlandı</th><th>Son Hata</th><th>İşlem</th>
                </tr></thead>
                <tbody><tr><td colspan="11">Yükleniyor...</td></tr></tbody>
            </table>
        </div>
    </div>

    {{-- MESAJ LOGLARI --}}
    <div class="wa-section" id="section-loglar">
        <div class="wa-filter">
            <div class="group"><label>Salon ID</label><input type="number" id="logSalonId" placeholder="Tümü"></div>
            <div class="group">
                <label>Durum</label>
                <select id="logDurum">
                    <option value="">Tümü</option>
                    <option value="0">Kuyrukta</option>
                    <option value="1">Gönderildi</option>
                    <option value="2">Başarısız</option>
                    <option value="3">SMS'e Düştü</option>
                </select>
            </div>
            <div class="group"><label>Telefon</label><input type="text" id="logTelefon" placeholder="905..."></div>
            <div class="group"><label>Başlangıç</label><input type="date" id="logBaslangic"></div>
            <div class="group"><label>Bitiş</label><input type="date" id="logBitis"></div>
            <div class="group"><label>Mesaj İçinde Ara</label><input type="text" id="logArama" placeholder="..."></div>
            <div class="group"><button class="wa-btn" id="logFiltreUygula">Filtrele</button></div>
            <div class="group"><button class="wa-btn secondary" id="logFiltreSifirla">Sıfırla</button></div>
            <div class="group"><button class="wa-btn" id="logCsvIndir" style="background:#0d6efd;">📥 Excel İndir</button></div>
        </div>
        <div style="overflow-x:auto;">
            <table class="wa-table" id="logTable">
                <thead><tr>
                    <th>ID</th><th>Tarih</th><th>Salon</th><th>Müşteri</th>
                    <th>Telefon</th><th>Durum</th><th>Mesaj</th><th>Hata</th>
                </tr></thead>
                <tbody><tr><td colspan="8">Yükleniyor...</td></tr></tbody>
            </table>
        </div>
        <div class="wa-pagination" id="logPagination"></div>
    </div>

    {{-- GRAFİK --}}
    <div class="wa-section" id="section-grafik">
        <div class="wa-chart-container">
            <h4 style="margin-top:0;">Son 30 Gün — Günlük Mesaj Hacmi</h4>
            <canvas id="waChart"></canvas>
        </div>
    </div>
</div>

<div class="wa-modal" id="logDetayModal">
    <div class="wa-modal-content">
        <div class="wa-modal-header">
            <h4 style="margin:0;">Mesaj Detayı</h4>
            <span class="wa-modal-close" onclick="document.getElementById('logDetayModal').classList.remove('show')">×</span>
        </div>
        <div id="logDetayBody">Yükleniyor...</div>
    </div>
</div>

<div class="wa-modal" id="aliciModal">
    <div class="wa-modal-content" style="max-width:900px;">
        <div class="wa-modal-header">
            <h4 style="margin:0;" id="aliciModalBaslik">Alıcılar</h4>
            <span class="wa-modal-close" onclick="document.getElementById('aliciModal').classList.remove('show')">×</span>
        </div>
        <div id="aliciModalBody">Yükleniyor...</div>
    </div>
</div>

<div class="wa-modal" id="aliciGecmisModal">
    <div class="wa-modal-content" style="max-width:700px;">
        <div class="wa-modal-header">
            <h4 style="margin:0;" id="aliciGecmisBaslik">Mesaj Geçmişi</h4>
            <span class="wa-modal-close" onclick="document.getElementById('aliciGecmisModal').classList.remove('show')">×</span>
        </div>
        <div id="aliciGecmisBody">Yükleniyor...</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    var DURUM_LABEL = {0:'Kuyrukta', 1:'Gönderildi', 2:'Başarısız', 3:"SMS'e Düştü"};
    var DURUM_BADGE = {0:'queued', 1:'success', 2:'fail', 3:'fallback'};

    function fetchJson(url){
        return fetch(url, {credentials:'same-origin', headers:{'Accept':'application/json'}})
            .then(function(r){ return r.json(); });
    }

    function fmtDate(s){
        if (!s) return '—';
        try { return new Date(s).toLocaleString('tr-TR'); } catch(e){ return s; }
    }

    function escHtml(s){
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    // TAB switching
    document.querySelectorAll('.wa-tab').forEach(function(t){
        t.addEventListener('click', function(){
            document.querySelectorAll('.wa-tab').forEach(function(x){ x.classList.remove('active'); });
            document.querySelectorAll('.wa-section').forEach(function(x){ x.classList.remove('active'); });
            t.classList.add('active');
            document.getElementById('section-' + t.dataset.tab).classList.add('active');
            if (t.dataset.tab === 'salonlar') yukleSalonlar();
            if (t.dataset.tab === 'loglar') yukleLoglar();
            if (t.dataset.tab === 'grafik') yukleGrafik();
        });
    });

    // ───────── DASHBOARD ─────────
    function yukleDashboard(){
        fetchJson('/sistemyonetim/whatsapp-panel/dashboard-data').then(function(d){
            var html = '';
            html += '<div class="wa-stat-card"><div class="wa-stat-label">🟢 Aktif Bağlı Salon</div><div class="wa-stat-value">' + d.aktifSalon + '</div></div>';
            html += '<div class="wa-stat-card ' + (d.banRiskSalon > 0 ? 'danger' : '') + '"><div class="wa-stat-label">⚠️ Ban/Risk Durumu</div><div class="wa-stat-value">' + d.banRiskSalon + '</div><div class="wa-stat-sub">otomatik durdurulmuş</div></div>';
            html += '<div class="wa-stat-card info"><div class="wa-stat-label">📤 Bugün Toplam</div><div class="wa-stat-value">' + d.bugun.toplam + '</div><div class="wa-stat-sub">' + d.bugun.basari + ' başarılı / ' + d.bugun.fail + ' fail / ' + d.bugun.fallback + ' SMS\'e düştü</div></div>';
            html += '<div class="wa-stat-card"><div class="wa-stat-label">📅 7 Gün Toplam</div><div class="wa-stat-value">' + d.hafta.toplam + '</div><div class="wa-stat-sub">' + d.hafta.basari + ' başarılı</div></div>';
            html += '<div class="wa-stat-card"><div class="wa-stat-label">🗓️ 30 Gün Toplam</div><div class="wa-stat-value">' + d.ay.toplam + '</div><div class="wa-stat-sub">' + d.ay.basari + ' başarılı</div></div>';
            var basariClass = d.basariOrani >= 90 ? '' : (d.basariOrani >= 70 ? 'warn' : 'danger');
            html += '<div class="wa-stat-card ' + basariClass + '"><div class="wa-stat-label">✅ Haftalık Başarı</div><div class="wa-stat-value">' + d.basariOrani + '%</div></div>';
            document.getElementById('waStatsGrid').innerHTML = html;
            document.getElementById('waLastUpdate').textContent = 'Son güncelleme: ' + new Date().toLocaleTimeString('tr-TR');
        });
        yukleTipDagilim();
    }

    function yukleTipDagilim(){
        fetchJson('/sistemyonetim/whatsapp-panel/tip-dagilim?gun=30').then(function(d){
            var tipBody = document.querySelector('#tipDagilimTable tbody');
            if (!d.tipler || d.tipler.length === 0) {
                tipBody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;">Veri yok</td></tr>';
            } else {
                tipBody.innerHTML = d.tipler.sort(function(a,b){return b.toplam-a.toplam;}).map(function(t){
                    return '<tr><td><b>' + escHtml(t.tip) + '</b></td><td>' + t.toplam + '</td>'
                        + '<td><span class="wa-badge success">' + t.basari + '</span></td>'
                        + '<td><span class="wa-badge fail">' + t.fail + '</span></td>'
                        + '<td><span class="wa-badge fallback">' + t.fallback + '</span></td></tr>';
                }).join('');
            }
            var topBody = document.querySelector('#topSalonTable tbody');
            if (!d.topSalon || d.topSalon.length === 0) {
                topBody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#999;">Veri yok</td></tr>';
            } else {
                topBody.innerHTML = d.topSalon.map(function(s, i){
                    return '<tr><td>' + (i+1) + '</td><td><b>' + escHtml(s.salon_adi || ('#' + s.salon_id)) + '</b></td><td>' + s.adet + '</td></tr>';
                }).join('');
            }
        });
    }

    // ───────── SALONLAR ─────────
    var salonlarCache = [];
    function yukleSalonlar(){
        var tbody = document.querySelector('#salonTable tbody');
        tbody.innerHTML = '<tr><td colspan="9"><span class="wa-spinner"></span> Yükleniyor...</td></tr>';
        fetchJson('/sistemyonetim/whatsapp-panel/salonlar-data').then(function(d){
            salonlarCache = d.rows || [];
            renderSalonlar();
        });
    }
    function renderSalonlar(){
        var durumF = document.getElementById('filterSalonDurum').value;
        var aramaF = document.getElementById('filterSalonArama').value.toLowerCase();
        var tbody = document.querySelector('#salonTable tbody');
        var rows = salonlarCache.filter(function(r){
            if (durumF && r.durum !== durumF) return false;
            if (aramaF && (r.salon_adi || '').toLowerCase().indexOf(aramaF) < 0) return false;
            return true;
        });
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;color:#999;padding:30px;">Filtre eşleşmesi yok</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function(r){
            var durumClass = r.durum || 'gri';
            var yonetUrl = '/isletmeyonetim/whatsapp?sube=' + r.id;
            var aliciBtn = '<button class="wa-btn-mini" data-salon-id="' + r.id + '" data-salon-adi="' + escHtml(r.salon_adi) + '" data-action="aliciler">👥 Alıcılar</button>';
            var loglarBtn = '<button class="wa-btn-mini" data-salon-id="' + r.id + '" data-action="loglar">📨 Loglar</button>';
            var yonetBtn = '<a class="wa-btn-mini wa-btn-mini-primary" href="' + yonetUrl + '" target="_blank">🔧 Yönet</a>';
            var saglayiciBadge = r.saglayici === 'cloud_api'
                ? '<span class="wa-badge" style="background:#1877F2;color:#fff;">☁️ Cloud API</span>'
                : '<span class="wa-badge gri">📱 Baileys</span>';
            return '<tr>'
                + '<td>' + r.id + '</td>'
                + '<td><b>' + escHtml(r.salon_adi) + '</b></td>'
                + '<td>' + saglayiciBadge + '</td>'
                + '<td>' + escHtml(r.numara || '—') + '</td>'
                + '<td><span class="wa-badge ' + escHtml(durumClass) + '">' + escHtml(r.durum || 'pasif') + '</span></td>'
                + '<td>' + r.gunluk_limit + '</td>'
                + '<td>' + r.bugun_toplam + ' <span style="color:#999;font-size:11px;">(✓' + r.bugun_basari + ' / ✗' + r.bugun_fail + ' / ⤵' + r.bugun_fallback + ')</span></td>'
                + '<td>' + r.hafta_toplam + '</td>'
                + '<td>' + escHtml(r.baglanti_tarihi || '—') + '</td>'
                + '<td style="color:#dc3545;font-size:12px;">' + escHtml(r.son_hata || '') + '</td>'
                + '<td style="white-space:nowrap;">' + yonetBtn + ' ' + aliciBtn + ' ' + loglarBtn + '</td>'
                + '</tr>';
        }).join('');

        // Salon log'una hızlı geçiş
        document.querySelectorAll('#salonTable button[data-action="loglar"]').forEach(function(b){
            b.addEventListener('click', function(){
                var sid = b.dataset.salonId;
                document.querySelector('.wa-tab[data-tab="loglar"]').click();
                document.getElementById('logSalonId').value = sid;
                yukleLoglar(1);
            });
        });

        // Salonun alıcı detaylarını aç
        document.querySelectorAll('#salonTable button[data-action="aliciler"]').forEach(function(b){
            b.addEventListener('click', function(){
                aciAliciModal(b.dataset.salonId, b.dataset.salonAdi);
            });
        });
    }
    document.getElementById('filterSalonDurum').addEventListener('change', renderSalonlar);
    document.getElementById('filterSalonArama').addEventListener('input', renderSalonlar);

    // ───────── LOGLAR ─────────
    var logPage = 1;
    function yukleLoglar(p){
        if (p) logPage = p;
        var params = new URLSearchParams();
        params.set('page', logPage);
        params.set('per_page', 50);
        ['logSalonId','logDurum','logTelefon','logBaslangic','logBitis','logArama'].forEach(function(id){
            var v = document.getElementById(id).value;
            var key = {logSalonId:'salon_id', logDurum:'durum', logTelefon:'telefon',
                       logBaslangic:'baslangic', logBitis:'bitis', logArama:'arama'}[id];
            if (v) params.set(key, v);
        });

        var tbody = document.querySelector('#logTable tbody');
        tbody.innerHTML = '<tr><td colspan="8"><span class="wa-spinner"></span> Yükleniyor...</td></tr>';

        fetchJson('/sistemyonetim/whatsapp-panel/loglar-data?' + params.toString()).then(function(d){
            var rows = d.rows || [];
            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#999;padding:30px;">Kayıt bulunamadı</td></tr>';
            } else {
                tbody.innerHTML = rows.map(function(r){
                    var badge = DURUM_BADGE[r.durum] || 'gri';
                    return '<tr class="clickable" data-id="' + r.id + '">'
                        + '<td>' + r.id + '</td>'
                        + '<td>' + escHtml(fmtDate(r.created_at)) + '</td>'
                        + '<td>' + escHtml(r.salon_adi || '#'+r.salon_id) + '</td>'
                        + '<td>' + escHtml(r.musteri_adi || '—') + '</td>'
                        + '<td>' + escHtml(r.telefon) + '</td>'
                        + '<td><span class="wa-badge ' + badge + '">' + (DURUM_LABEL[r.durum] || r.durum) + '</span></td>'
                        + '<td><div class="wa-mesaj-truncate" title="' + escHtml(r.mesaj) + '">' + escHtml(r.mesaj || '') + '</div></td>'
                        + '<td style="color:#dc3545;font-size:12px;">' + escHtml(r.hata || '') + '</td>'
                        + '</tr>';
                }).join('');
                document.querySelectorAll('#logTable tbody tr.clickable').forEach(function(tr){
                    tr.addEventListener('click', function(){ logDetay(tr.dataset.id); });
                });
            }
            renderPagination(d);
        });
    }
    function renderPagination(d){
        var pag = document.getElementById('logPagination');
        var info = '<span class="info">Toplam ' + d.toplam + ' kayıt — Sayfa ' + d.page + ' / ' + (d.son_sayfa || 1) + '</span>';
        var prev = '<button ' + (d.page <= 1 ? 'disabled' : '') + ' onclick="window.__waLogSayfa(' + (d.page-1) + ')">← Önceki</button>';
        var next = '<button ' + (d.page >= d.son_sayfa ? 'disabled' : '') + ' onclick="window.__waLogSayfa(' + (d.page+1) + ')">Sonraki →</button>';
        pag.innerHTML = prev + info + next;
    }
    window.__waLogSayfa = yukleLoglar;
    document.getElementById('logFiltreUygula').addEventListener('click', function(){ yukleLoglar(1); });
    document.getElementById('logFiltreSifirla').addEventListener('click', function(){
        ['logSalonId','logDurum','logTelefon','logBaslangic','logBitis','logArama'].forEach(function(id){ document.getElementById(id).value = ''; });
        yukleLoglar(1);
    });
    document.getElementById('logCsvIndir').addEventListener('click', function(){
        var params = new URLSearchParams();
        ['logSalonId','logDurum','logTelefon','logBaslangic','logBitis','logArama'].forEach(function(id){
            var v = document.getElementById(id).value;
            var key = {logSalonId:'salon_id', logDurum:'durum', logTelefon:'telefon',
                       logBaslangic:'baslangic', logBitis:'bitis', logArama:'arama'}[id];
            if (v) params.set(key, v);
        });
        window.location.href = '/sistemyonetim/whatsapp-panel/loglar-csv?' + params.toString();
    });

    function logDetay(id){
        var modal = document.getElementById('logDetayModal');
        var body = document.getElementById('logDetayBody');
        modal.classList.add('show');
        body.innerHTML = '<span class="wa-spinner"></span> Yükleniyor...';
        fetchJson('/sistemyonetim/whatsapp-panel/mesaj/' + id).then(function(d){
            var l = d.log;
            var rows = [
                ['ID', l.id],
                ['Tarih', fmtDate(l.created_at)],
                ['Salon', (l.salon_adi || '') + ' (#' + l.salon_id + ')'],
                ['Müşteri', l.musteri_adi || '—'],
                ['Randevu ID', l.randevu_id || '—'],
                ['Telefon', l.telefon],
                ['Durum', DURUM_LABEL[l.durum] || l.durum],
                ['Hata', l.hata || '—'],
                ['Mesaj ID', l.mesaj_id || '—'],
                ['Gönderim Tarihi', fmtDate(l.gonderim_tarihi)],
            ];
            var html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            rows.forEach(function(r){
                html += '<tr><td style="padding:6px;color:#666;font-weight:600;width:140px;">' + r[0] + '</td><td style="padding:6px;">' + escHtml(r[1]) + '</td></tr>';
            });
            html += '</table><h5 style="margin-top:18px;">Mesaj İçeriği</h5><div style="background:#f7f9fc;padding:12px;border-radius:6px;white-space:pre-wrap;font-family:monospace;font-size:13px;">' + escHtml(l.mesaj) + '</div>';
            body.innerHTML = html;
        });
    }

    // ───────── ALICILAR (salon bazlı) ─────────
    window.aciAliciModal = function(salonId, salonAdi){
        var modal = document.getElementById('aliciModal');
        var body = document.getElementById('aliciModalBody');
        document.getElementById('aliciModalBaslik').textContent = '👥 ' + salonAdi + ' — Alıcılar';
        modal.classList.add('show');
        body.innerHTML = '<span class="wa-spinner"></span> Yükleniyor...';
        fetchJson('/sistemyonetim/whatsapp-panel/salon/' + salonId + '/aliciler').then(function(d){
            if (d.error) { body.innerHTML = '<div style="color:#dc3545;">' + d.error + '</div>'; return; }
            var rows = d.aliciList || [];
            if (rows.length === 0) {
                body.innerHTML = '<div style="text-align:center;color:#999;padding:30px;">Bu salon henüz mesaj atmamış</div>';
                return;
            }
            var html = '<div style="margin-bottom:12px;color:#666;">'
                + '<b>' + d.toplamAlici + '</b> farklı alıcıya mesaj gönderilmiş.'
                + (d.salon.whatsapp_numara ? ' Gönderen: <b>' + escHtml(d.salon.whatsapp_numara) + '</b>' : '')
                + '</div>';
            html += '<div style="overflow:auto;max-height:60vh;"><table class="wa-table">';
            html += '<thead><tr><th>Müşteri</th><th>Telefon</th><th>Toplam</th><th>Durum</th><th>İlk</th><th>Son</th><th></th></tr></thead><tbody>';
            rows.forEach(function(r){
                html += '<tr>'
                    + '<td><b>' + escHtml(r.musteri_adi || '—') + '</b></td>'
                    + '<td>' + escHtml(r.telefon) + '</td>'
                    + '<td>' + r.toplam + '</td>'
                    + '<td><span class="wa-badge success">✓' + r.basari + '</span> '
                    + '<span class="wa-badge fail">✗' + r.fail + '</span> '
                    + '<span class="wa-badge fallback">⤵' + r.fallback + '</span></td>'
                    + '<td style="font-size:11px;color:#666;">' + escHtml(fmtDate(r.ilk_mesaj)) + '</td>'
                    + '<td style="font-size:11px;color:#666;">' + escHtml(fmtDate(r.son_mesaj)) + '</td>'
                    + '<td><button class="wa-btn-mini" data-salon-id="' + salonId + '" data-telefon="' + escHtml(r.telefon) + '" data-musteri="' + escHtml(r.musteri_adi || '') + '">📋 Geçmiş</button></td>'
                    + '</tr>';
            });
            html += '</tbody></table></div>';
            body.innerHTML = html;
            body.querySelectorAll('button[data-telefon]').forEach(function(btn){
                btn.addEventListener('click', function(){
                    aciAliciGecmis(btn.dataset.salonId, btn.dataset.telefon, btn.dataset.musteri);
                });
            });
        });
    };

    function aciAliciGecmis(salonId, telefon, musteriAdi){
        var modal = document.getElementById('aliciGecmisModal');
        var body = document.getElementById('aliciGecmisBody');
        document.getElementById('aliciGecmisBaslik').textContent = '📋 ' + (musteriAdi || telefon) + ' — Mesaj Geçmişi';
        modal.classList.add('show');
        body.innerHTML = '<span class="wa-spinner"></span> Yükleniyor...';
        fetchJson('/sistemyonetim/whatsapp-panel/salon/' + salonId + '/alici/' + encodeURIComponent(telefon) + '/gecmis').then(function(d){
            var rows = d.rows || [];
            if (rows.length === 0) { body.innerHTML = '<div style="text-align:center;color:#999;padding:30px;">Mesaj yok</div>'; return; }
            var html = '<div style="display:flex;flex-direction:column;gap:10px;max-height:65vh;overflow:auto;">';
            rows.forEach(function(r){
                var badge = DURUM_BADGE[r.durum] || 'gri';
                var label = DURUM_LABEL[r.durum] || r.durum;
                html += '<div style="background:#f7f9fc;border-left:3px solid #25D366;padding:10px 14px;border-radius:6px;">'
                    + '<div style="display:flex;justify-content:space-between;font-size:12px;color:#666;margin-bottom:6px;">'
                    + '<span><b>' + escHtml(fmtDate(r.created_at)) + '</b>' + (r.randevu_id ? ' — Randevu #' + r.randevu_id : '') + '</span>'
                    + '<span class="wa-badge ' + badge + '">' + label + '</span></div>'
                    + '<div style="white-space:pre-wrap;font-size:13px;color:#333;">' + escHtml(r.mesaj) + '</div>'
                    + (r.hata ? '<div style="margin-top:6px;color:#dc3545;font-size:11px;">Hata: ' + escHtml(r.hata) + '</div>' : '')
                    + '</div>';
            });
            html += '</div>';
            body.innerHTML = html;
        });
    }

    // ───────── GRAFİK ─────────
    var chartInstance = null;
    function yukleGrafik(){
        fetchJson('/sistemyonetim/whatsapp-panel/grafik-data').then(function(d){
            var ctx = document.getElementById('waChart').getContext('2d');
            var labels = d.gunler.map(function(g){ return g.gun.substring(5); });
            if (chartInstance) chartInstance.destroy();
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Başarılı', data: d.gunler.map(function(g){ return g.basari; }), backgroundColor: '#25D366' },
                        { label: 'Başarısız', data: d.gunler.map(function(g){ return g.fail; }), backgroundColor: '#dc3545' },
                        { label: "SMS'e Düştü", data: d.gunler.map(function(g){ return g.fallback; }), backgroundColor: '#f0ad4e' },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
                }
            });
        });
    }

    // İlk yükleme + manuel refresh
    document.getElementById('waRefreshAll').addEventListener('click', function(){
        yukleDashboard();
        if (document.getElementById('section-salonlar').classList.contains('active')) yukleSalonlar();
        if (document.getElementById('section-loglar').classList.contains('active')) yukleLoglar();
        if (document.getElementById('section-grafik').classList.contains('active')) yukleGrafik();
    });

    yukleDashboard();
    setInterval(yukleDashboard, 30000);
})();
</script>
@endsection
