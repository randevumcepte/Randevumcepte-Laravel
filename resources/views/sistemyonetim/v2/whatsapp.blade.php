@extends('sistemyonetim.v2.layout')

@section('content')

<style>
.wa-tabs {
    display: flex;
    gap: 6px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--sy-border);
    padding-bottom: 0;
}
.wa-tab {
    padding: 10px 18px;
    cursor: pointer;
    border-radius: 10px 10px 0 0;
    font-weight: 500;
    font-size: 13.5px;
    color: var(--sy-text-muted);
    transition: all 0.15s;
    border: 1px solid transparent;
    border-bottom: none;
    margin-bottom: -1px;
}
.wa-tab:hover { color: var(--sy-primary); background: var(--sy-primary-soft); }
.wa-tab.active {
    background: var(--sy-surface);
    color: var(--sy-primary-deep);
    border-color: var(--sy-border);
    border-bottom-color: var(--sy-surface);
    font-weight: 600;
}
.wa-section { display: none; }
.wa-section.active { display: block; }

.wa-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.wa-mesaj-truncate { max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.wa-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(45, 31, 72, 0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.wa-modal.show { display: flex; }
.wa-modal-content {
    background: var(--sy-surface);
    border-radius: var(--sy-radius);
    padding: 22px;
    max-width: 700px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
    box-shadow: var(--sy-shadow-lg);
}
.wa-modal-content.lg { max-width: 980px; }
.wa-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--sy-border);
}
.wa-modal-header h4 { margin: 0; font-size: 16px; font-weight: 600; }
.wa-modal-close { cursor: pointer; font-size: 24px; line-height: 1; color: var(--sy-text-muted); border: none; background: none; }
.wa-modal-close:hover { color: var(--sy-danger); }

#waChart { max-height: 320px; }

.wa-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid var(--sy-border);
    border-top-color: var(--sy-primary);
    border-radius: 50%;
    animation: waspin 0.8s linear infinite;
    vertical-align: middle;
}
@keyframes waspin { to { transform: rotate(360deg); } }

.wa-pagination-wrap {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-top: 16px;
    align-items: center;
}
.wa-pagination-wrap .info { color: var(--sy-text-muted); font-size: 13px; padding: 0 12px; }

.wa-saglayici-cloud {
    background: #e3eefb;
    color: #1877F2;
}
.wa-saglayici-baileys {
    background: #e2f6ec;
    color: #1f7a4f;
}
</style>

<div class="sy-page-head">
    <div>
        <h2><span class="mdi mdi-whatsapp" style="color:#25D366"></span> WhatsApp Yönetim</h2>
        <div class="subtitle">Tüm salonların WhatsApp bağlantı durumu, mesaj logları, alıcı geçmişi</div>
    </div>
    <div class="sy-flex-row">
        <span id="waLastUpdate" class="sy-text-muted sy-fs-12"></span>
        <button class="sy-btn" id="waRefreshAll"><span class="mdi mdi-refresh"></span> Yenile</button>
    </div>
</div>

<div class="wa-tabs">
    <div class="wa-tab active" data-tab="dashboard"><span class="mdi mdi-view-dashboard"></span> Özet</div>
    <div class="wa-tab" data-tab="salonlar"><span class="mdi mdi-store"></span> Salonlar</div>
    <div class="wa-tab" data-tab="loglar"><span class="mdi mdi-message-text"></span> Mesaj Logları</div>
    <div class="wa-tab" data-tab="grafik"><span class="mdi mdi-chart-bar"></span> Grafik</div>
</div>

{{-- DASHBOARD --}}
<div class="wa-section active" id="section-dashboard">
    <div class="sy-metric-grid" id="waStatsGrid">
        <div class="sy-metric"><div class="label">Yükleniyor</div><div class="value">—</div></div>
    </div>

    <div class="sy-grid-2">
        <div class="sy-card">
            <div class="sy-card-head"><h3>Mesaj Türü Dağılımı (son 30 gün)</h3></div>
            <div class="sy-card-body tight">
                <table class="sy-table" id="tipDagilimTable">
                    <thead><tr><th>Tür</th><th>Toplam</th><th>Başarı</th><th>Fail</th><th>SMS↓</th></tr></thead>
                    <tbody><tr><td colspan="5" class="sy-text-center sy-text-muted" style="padding:20px"><span class="wa-spinner"></span></td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="sy-card">
            <div class="sy-card-head"><h3>Top 10 Salon (son 30 gün)</h3></div>
            <div class="sy-card-body tight">
                <table class="sy-table" id="topSalonTable">
                    <thead><tr><th>#</th><th>Salon</th><th>Mesaj</th></tr></thead>
                    <tbody><tr><td colspan="3" class="sy-text-center sy-text-muted" style="padding:20px"><span class="wa-spinner"></span></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- SALONLAR --}}
<div class="wa-section" id="section-salonlar">
    <div class="sy-filters">
        <div class="sy-form-group" style="max-width:200px">
            <label>Durum</label>
            <select id="filterSalonDurum" class="sy-select">
                <option value="">Tümü</option>
                <option value="connected">Bağlı</option>
                <option value="disconnected">Bağlantı Kesildi</option>
                <option value="qr-pending">QR Bekliyor</option>
                <option value="banned-or-loggedout">Ban / Çıkış</option>
                <option value="auto-paused-ban-risk">Oto-Durdu</option>
            </select>
        </div>
        <div class="sy-form-group">
            <label>Salon Ara</label>
            <input type="text" id="filterSalonArama" class="sy-input" placeholder="Salon adı...">
        </div>
    </div>

    <div class="sy-card">
        <div class="sy-card-body tight" style="overflow-x:auto">
            <table class="sy-table" id="salonTable">
                <thead><tr>
                    <th>ID</th><th>Salon</th><th>Sağlayıcı</th><th>Numara</th><th>Durum</th>
                    <th>Limit</th><th>Bugün</th><th>Hafta</th><th>Bağlandı</th><th>Son Hata</th><th class="sy-text-right">İşlem</th>
                </tr></thead>
                <tbody><tr><td colspan="11" class="sy-text-center sy-text-muted" style="padding:24px"><span class="wa-spinner"></span> Yükleniyor</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

{{-- MESAJ LOGLARI --}}
<div class="wa-section" id="section-loglar">
    <div class="sy-filters">
        <div class="sy-form-group" style="max-width:130px"><label>Salon ID</label><input type="number" id="logSalonId" class="sy-input" placeholder="Tümü"></div>
        <div class="sy-form-group" style="max-width:170px">
            <label>Durum</label>
            <select id="logDurum" class="sy-select">
                <option value="">Tümü</option>
                <option value="0">Kuyrukta</option>
                <option value="1">Gönderildi</option>
                <option value="2">Başarısız</option>
                <option value="3">SMS'e Düştü</option>
            </select>
        </div>
        <div class="sy-form-group" style="max-width:170px"><label>Telefon</label><input type="text" id="logTelefon" class="sy-input" placeholder="905..."></div>
        <div class="sy-form-group" style="max-width:160px"><label>Başlangıç</label><input type="date" id="logBaslangic" class="sy-input"></div>
        <div class="sy-form-group" style="max-width:160px"><label>Bitiş</label><input type="date" id="logBitis" class="sy-input"></div>
        <div class="sy-form-group"><label>Mesaj İçinde</label><input type="text" id="logArama" class="sy-input" placeholder="..."></div>
        <button class="sy-btn sy-btn-primary" id="logFiltreUygula"><span class="mdi mdi-magnify"></span> Filtrele</button>
        <button class="sy-btn" id="logFiltreSifirla">Sıfırla</button>
        <button class="sy-btn" id="logCsvIndir"><span class="mdi mdi-file-download"></span> Excel</button>
    </div>

    <div class="sy-card">
        <div class="sy-card-body tight" style="overflow-x:auto">
            <table class="sy-table" id="logTable">
                <thead><tr>
                    <th>ID</th><th>Tarih</th><th>Salon</th><th>Müşteri</th><th>Telefon</th><th>Durum</th><th>Mesaj</th><th>Hata</th>
                </tr></thead>
                <tbody><tr><td colspan="8" class="sy-text-center sy-text-muted" style="padding:24px"><span class="wa-spinner"></span> Yükleniyor</td></tr></tbody>
            </table>
        </div>
    </div>
    <div class="wa-pagination-wrap" id="logPagination"></div>
</div>

{{-- GRAFİK --}}
<div class="wa-section" id="section-grafik">
    <div class="sy-card">
        <div class="sy-card-head"><h3>Son 30 Gün — Günlük Mesaj Hacmi</h3></div>
        <div class="sy-card-body">
            <canvas id="waChart" height="120"></canvas>
        </div>
    </div>
</div>

{{-- MODALS --}}
<div class="wa-modal" id="logDetayModal">
    <div class="wa-modal-content">
        <div class="wa-modal-header">
            <h4>Mesaj Detayı</h4>
            <button class="wa-modal-close" onclick="document.getElementById('logDetayModal').classList.remove('show')">×</button>
        </div>
        <div id="logDetayBody"><span class="wa-spinner"></span> Yükleniyor</div>
    </div>
</div>

<div class="wa-modal" id="aliciModal">
    <div class="wa-modal-content lg">
        <div class="wa-modal-header">
            <h4 id="aliciModalBaslik">Alıcılar</h4>
            <button class="wa-modal-close" onclick="document.getElementById('aliciModal').classList.remove('show')">×</button>
        </div>
        <div id="aliciModalBody"><span class="wa-spinner"></span> Yükleniyor</div>
    </div>
</div>

<div class="wa-modal" id="aliciGecmisModal">
    <div class="wa-modal-content">
        <div class="wa-modal-header">
            <h4 id="aliciGecmisBaslik">Mesaj Geçmişi</h4>
            <button class="wa-modal-close" onclick="document.getElementById('aliciGecmisModal').classList.remove('show')">×</button>
        </div>
        <div id="aliciGecmisBody"><span class="wa-spinner"></span> Yükleniyor</div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    var DURUM_LABEL = {0:'Kuyrukta', 1:'Gönderildi', 2:'Başarısız', 3:"SMS'e Düştü"};
    var DURUM_BADGE = {0:'sy-badge-info', 1:'sy-badge-success', 2:'sy-badge-danger', 3:'sy-badge-warning'};

    var DURUM_ETIKETI = {
        'connected': 'sy-badge-success',
        'disconnected': 'sy-badge-danger',
        'qr-pending': 'sy-badge-warning',
        'banned-or-loggedout': 'sy-badge-danger',
        'auto-paused-ban-risk': 'sy-badge-danger',
        'rate-limited': 'sy-badge-danger',
        'connecting': 'sy-badge-info',
    };

    function fetchJson(url){
        return fetch(url, {credentials:'same-origin', headers:{'Accept':'application/json'}})
            .then(function(r){ return r.json(); });
    }
    function fmtDate(s){ if (!s) return '—'; try { return new Date(s).toLocaleString('tr-TR'); } catch(e){ return s; } }
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
            html += metricKart('Aktif Bağlı Salon', d.aktifSalon, 'mdi-whatsapp', 'success');
            html += metricKart('Ban / Risk', d.banRiskSalon, 'mdi-alert', d.banRiskSalon > 0 ? 'danger' : '', 'otomatik durdurulmuş');
            html += metricKart('Bugün Toplam', d.bugun.toplam, 'mdi-message-arrow-right', 'info', '✓' + d.bugun.basari + ' · ✗' + d.bugun.fail + ' · ⤵' + d.bugun.fallback);
            html += metricKart('7 Gün', d.hafta.toplam, 'mdi-calendar-week', '', '✓' + d.hafta.basari);
            html += metricKart('30 Gün', d.ay.toplam, 'mdi-calendar-month', '', '✓' + d.ay.basari);
            var basariClass = d.basariOrani >= 90 ? 'success' : (d.basariOrani >= 70 ? 'warning' : 'danger');
            html += metricKart('Haftalık Başarı', '%' + d.basariOrani, 'mdi-check-decagram', basariClass);
            document.getElementById('waStatsGrid').innerHTML = html;
            document.getElementById('waLastUpdate').textContent = 'Son güncelleme: ' + new Date().toLocaleTimeString('tr-TR');
        });
        yukleTipDagilim();
    }
    function metricKart(label, value, ikon, sinif, alt){
        return '<div class="sy-metric ' + (sinif || '') + '">'
             + '<div class="icon-bg mdi ' + ikon + '"></div>'
             + '<div class="label">' + escHtml(label) + '</div>'
             + '<div class="value">' + escHtml(value) + '</div>'
             + (alt ? '<div class="delta">' + escHtml(alt) + '</div>' : '')
             + '</div>';
    }

    function yukleTipDagilim(){
        fetchJson('/sistemyonetim/whatsapp-panel/tip-dagilim?gun=30').then(function(d){
            var tipBody = document.querySelector('#tipDagilimTable tbody');
            if (!d.tipler || d.tipler.length === 0) {
                tipBody.innerHTML = '<tr><td colspan="5" class="sy-text-center sy-text-muted" style="padding:20px">Veri yok</td></tr>';
            } else {
                tipBody.innerHTML = d.tipler.sort(function(a,b){return b.toplam-a.toplam;}).map(function(t){
                    return '<tr><td><strong>' + escHtml(t.tip) + '</strong></td><td>' + t.toplam + '</td>'
                        + '<td><span class="sy-badge sy-badge-success">' + t.basari + '</span></td>'
                        + '<td><span class="sy-badge sy-badge-danger">' + t.fail + '</span></td>'
                        + '<td><span class="sy-badge sy-badge-warning">' + t.fallback + '</span></td></tr>';
                }).join('');
            }
            var topBody = document.querySelector('#topSalonTable tbody');
            if (!d.topSalon || d.topSalon.length === 0) {
                topBody.innerHTML = '<tr><td colspan="3" class="sy-text-center sy-text-muted" style="padding:20px">Veri yok</td></tr>';
            } else {
                topBody.innerHTML = d.topSalon.map(function(s, i){
                    return '<tr><td>' + (i+1) + '</td><td><strong>' + escHtml(s.salon_adi || ('#' + s.salon_id)) + '</strong></td><td>' + s.adet + '</td></tr>';
                }).join('');
            }
        });
    }

    // ───────── SALONLAR ─────────
    var salonlarCache = [];
    function yukleSalonlar(){
        var tbody = document.querySelector('#salonTable tbody');
        tbody.innerHTML = '<tr><td colspan="11" class="sy-text-center sy-text-muted" style="padding:24px"><span class="wa-spinner"></span> Yükleniyor</td></tr>';
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
            tbody.innerHTML = '<tr><td colspan="11" class="sy-text-center sy-text-muted" style="padding:30px">Eşleşme yok</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function(r){
            var durumClass = DURUM_ETIKETI[r.durum] || 'sy-badge-muted';
            var saglayiciBadge = r.saglayici === 'cloud_api'
                ? '<span class="sy-badge wa-saglayici-cloud">Cloud API</span>'
                : '<span class="sy-badge wa-saglayici-baileys">Baileys</span>';
            var yonetUrl = '/isletmeyonetim/whatsapp?sube=' + r.id;
            return '<tr>'
                + '<td>' + r.id + '</td>'
                + '<td><strong>' + escHtml(r.salon_adi) + '</strong></td>'
                + '<td>' + saglayiciBadge + '</td>'
                + '<td>' + escHtml(r.numara || '—') + '</td>'
                + '<td><span class="sy-badge ' + durumClass + '">' + escHtml(r.durum || 'pasif') + '</span></td>'
                + '<td>' + r.gunluk_limit + '</td>'
                + '<td>' + r.bugun_toplam + ' <span class="sy-text-muted sy-fs-12">(✓' + r.bugun_basari + ' / ✗' + r.bugun_fail + ' / ⤵' + r.bugun_fallback + ')</span></td>'
                + '<td>' + r.hafta_toplam + '</td>'
                + '<td class="sy-text-muted sy-fs-12">' + escHtml(r.baglanti_tarihi || '—') + '</td>'
                + '<td style="color:var(--sy-danger);font-size:12px">' + escHtml(r.son_hata || '') + '</td>'
                + '<td class="nowrap sy-text-right">'
                + '<a class="sy-btn sy-btn-sm sy-btn-primary" href="' + yonetUrl + '" target="_blank" title="Yönet"><span class="mdi mdi-cog"></span></a> '
                + '<button class="sy-btn sy-btn-sm sy-btn-soft" data-salon-id="' + r.id + '" data-salon-adi="' + escHtml(r.salon_adi) + '" data-action="aliciler" title="Alıcılar"><span class="mdi mdi-account-multiple"></span></button> '
                + '<button class="sy-btn sy-btn-sm" data-salon-id="' + r.id + '" data-action="loglar" title="Logları gör"><span class="mdi mdi-message"></span></button>'
                + '</td>'
                + '</tr>';
        }).join('');

        document.querySelectorAll('#salonTable button[data-action="loglar"]').forEach(function(b){
            b.addEventListener('click', function(){
                var sid = b.dataset.salonId;
                document.querySelector('.wa-tab[data-tab="loglar"]').click();
                document.getElementById('logSalonId').value = sid;
                yukleLoglar(1);
            });
        });
        document.querySelectorAll('#salonTable button[data-action="aliciler"]').forEach(function(b){
            b.addEventListener('click', function(){ aciAliciModal(b.dataset.salonId, b.dataset.salonAdi); });
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
        tbody.innerHTML = '<tr><td colspan="8" class="sy-text-center sy-text-muted" style="padding:24px"><span class="wa-spinner"></span> Yükleniyor</td></tr>';

        fetchJson('/sistemyonetim/whatsapp-panel/loglar-data?' + params.toString()).then(function(d){
            var rows = d.rows || [];
            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="sy-text-center sy-text-muted" style="padding:30px">Kayıt bulunamadı</td></tr>';
            } else {
                tbody.innerHTML = rows.map(function(r){
                    var badge = DURUM_BADGE[r.durum] || 'sy-badge-muted';
                    return '<tr style="cursor:pointer" data-id="' + r.id + '">'
                        + '<td>' + r.id + '</td>'
                        + '<td class="sy-text-muted sy-fs-12">' + escHtml(fmtDate(r.created_at)) + '</td>'
                        + '<td>' + escHtml(r.salon_adi || '#'+r.salon_id) + '</td>'
                        + '<td>' + escHtml(r.musteri_adi || '—') + '</td>'
                        + '<td>' + escHtml(r.telefon) + '</td>'
                        + '<td><span class="sy-badge ' + badge + '">' + (DURUM_LABEL[r.durum] || r.durum) + '</span></td>'
                        + '<td><div class="wa-mesaj-truncate" title="' + escHtml(r.mesaj) + '">' + escHtml(r.mesaj || '') + '</div></td>'
                        + '<td style="color:var(--sy-danger);font-size:12px">' + escHtml(r.hata || '') + '</td>'
                        + '</tr>';
                }).join('');
                document.querySelectorAll('#logTable tbody tr[data-id]').forEach(function(tr){
                    tr.addEventListener('click', function(){ logDetay(tr.dataset.id); });
                });
            }
            renderPagination(d);
        });
    }
    function renderPagination(d){
        var pag = document.getElementById('logPagination');
        var info = '<span class="info">Toplam <strong>' + d.toplam + '</strong> · Sayfa ' + d.page + '/' + (d.son_sayfa || 1) + '</span>';
        var prev = '<button class="sy-btn sy-btn-sm" ' + (d.page <= 1 ? 'disabled' : '') + ' onclick="window.__waLogSayfa(' + (d.page-1) + ')"><span class="mdi mdi-chevron-left"></span> Önceki</button>';
        var next = '<button class="sy-btn sy-btn-sm" ' + (d.page >= d.son_sayfa ? 'disabled' : '') + ' onclick="window.__waLogSayfa(' + (d.page+1) + ')">Sonraki <span class="mdi mdi-chevron-right"></span></button>';
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
        body.innerHTML = '<span class="wa-spinner"></span> Yükleniyor';
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
            var html = '<table class="sy-table">';
            rows.forEach(function(r){
                html += '<tr><td class="sy-text-muted" style="width:140px">' + r[0] + '</td><td><strong>' + escHtml(r[1]) + '</strong></td></tr>';
            });
            html += '</table><h5 style="margin:18px 0 8px">Mesaj İçeriği</h5><div style="background:var(--sy-surface-2);padding:14px;border-radius:8px;white-space:pre-wrap;font-family:monospace;font-size:13px;border:1px solid var(--sy-border)">' + escHtml(l.mesaj) + '</div>';
            body.innerHTML = html;
        });
    }

    // ───────── ALICILAR ─────────
    window.aciAliciModal = function(salonId, salonAdi){
        var modal = document.getElementById('aliciModal');
        var body = document.getElementById('aliciModalBody');
        document.getElementById('aliciModalBaslik').textContent = salonAdi + ' — Alıcılar';
        modal.classList.add('show');
        body.innerHTML = '<span class="wa-spinner"></span> Yükleniyor';
        fetchJson('/sistemyonetim/whatsapp-panel/salon/' + salonId + '/aliciler').then(function(d){
            if (d.error) { body.innerHTML = '<div class="sy-alert sy-alert-danger">' + escHtml(d.error) + '</div>'; return; }
            var rows = d.aliciList || [];
            if (rows.length === 0) {
                body.innerHTML = '<div class="sy-empty"><div class="icon mdi mdi-message-off"></div><div class="baslik">Bu salon henüz mesaj atmamış</div></div>';
                return;
            }
            var html = '<div class="sy-text-muted" style="margin-bottom:12px">'
                + '<strong>' + d.toplamAlici + '</strong> farklı alıcıya mesaj gönderilmiş.'
                + (d.salon.whatsapp_numara ? ' Gönderen: <strong>' + escHtml(d.salon.whatsapp_numara) + '</strong>' : '')
                + '</div>';
            html += '<div style="overflow:auto;max-height:60vh"><table class="sy-table">';
            html += '<thead><tr><th>Müşteri</th><th>Telefon</th><th>Toplam</th><th>Durum</th><th>İlk</th><th>Son</th><th></th></tr></thead><tbody>';
            rows.forEach(function(r){
                html += '<tr>'
                    + '<td><strong>' + escHtml(r.musteri_adi || '—') + '</strong></td>'
                    + '<td>' + escHtml(r.telefon) + '</td>'
                    + '<td>' + r.toplam + '</td>'
                    + '<td><span class="sy-badge sy-badge-success">✓' + r.basari + '</span> '
                    + '<span class="sy-badge sy-badge-danger">✗' + r.fail + '</span> '
                    + '<span class="sy-badge sy-badge-warning">⤵' + r.fallback + '</span></td>'
                    + '<td class="sy-text-muted sy-fs-12">' + escHtml(fmtDate(r.ilk_mesaj)) + '</td>'
                    + '<td class="sy-text-muted sy-fs-12">' + escHtml(fmtDate(r.son_mesaj)) + '</td>'
                    + '<td><button class="sy-btn sy-btn-sm sy-btn-soft" data-salon-id="' + salonId + '" data-telefon="' + escHtml(r.telefon) + '" data-musteri="' + escHtml(r.musteri_adi || '') + '"><span class="mdi mdi-history"></span></button></td>'
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
        document.getElementById('aliciGecmisBaslik').textContent = (musteriAdi || telefon) + ' — Mesaj Geçmişi';
        modal.classList.add('show');
        body.innerHTML = '<span class="wa-spinner"></span> Yükleniyor';
        fetchJson('/sistemyonetim/whatsapp-panel/salon/' + salonId + '/alici/' + encodeURIComponent(telefon) + '/gecmis').then(function(d){
            var rows = d.rows || [];
            if (rows.length === 0) { body.innerHTML = '<div class="sy-empty"><div class="baslik">Mesaj yok</div></div>'; return; }
            var html = '<div style="display:flex;flex-direction:column;gap:10px;max-height:65vh;overflow:auto">';
            rows.forEach(function(r){
                var badge = DURUM_BADGE[r.durum] || 'sy-badge-muted';
                var label = DURUM_LABEL[r.durum] || r.durum;
                html += '<div style="background:var(--sy-surface-2);border-left:3px solid #25D366;padding:10px 14px;border-radius:8px">'
                    + '<div style="display:flex;justify-content:space-between;font-size:12px;color:var(--sy-text-muted);margin-bottom:6px">'
                    + '<span><strong>' + escHtml(fmtDate(r.created_at)) + '</strong>' + (r.randevu_id ? ' · Randevu #' + r.randevu_id : '') + '</span>'
                    + '<span class="sy-badge ' + badge + '">' + label + '</span></div>'
                    + '<div style="white-space:pre-wrap;font-size:13px;color:var(--sy-text)">' + escHtml(r.mesaj) + '</div>'
                    + (r.hata ? '<div style="margin-top:6px;color:var(--sy-danger);font-size:11px">Hata: ' + escHtml(r.hata) + '</div>' : '')
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
                        { label: 'Başarılı', data: d.gunler.map(function(g){ return g.basari; }), backgroundColor: '#2cae71' },
                        { label: 'Başarısız', data: d.gunler.map(function(g){ return g.fail; }), backgroundColor: '#d04d5e' },
                        { label: "SMS'e Düştü", data: d.gunler.map(function(g){ return g.fallback; }), backgroundColor: '#d99a1f' },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
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
@endpush

@endsection
