@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif
@extends($_layout)

@section('content')
<style>
    .yt-wrap { padding: 16px 22px; }
    .yt-header {
        display:flex; justify-content:space-between; align-items:center;
        background: linear-gradient(135deg,#4f46e5 0%, #7c3aed 100%);
        color:#fff; padding:16px 22px; border-radius:12px;
        margin-bottom: 18px;
        box-shadow: 0 6px 18px rgba(124,58,237,0.18);
    }
    .yt-header h1 { font-size: 1.25rem; margin:0; font-weight:600; }
    .yt-header .yt-sub { font-size:0.78rem; opacity:0.85; margin-top:4px; display:block; }
    .yt-cta {
        background:#fff; color:#6d28d9; border:none;
        padding: 10px 18px; border-radius: 8px;
        font-weight:600; font-size:0.92rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .yt-cta:hover { background:#f5f3ff; color:#5b21b6; }
    .yt-cta i { margin-right: 6px; }

    .yt-grid {
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 18px;
    }
    .yt-card {
        background:#fff; border:1px solid #ececec; border-radius:10px;
        padding: 16px 18px;
    }
    .yt-card h3 { font-size:0.95rem; margin:0 0 8px; color:#374151; font-weight:600; }
    .yt-card .yt-list { margin:0; padding-left:18px; font-size:0.82rem; color:#4b5563; line-height:1.7; }
    .yt-card .yt-list strong { color:#111827; }

    .yt-compare {
        background:#fff; border:1px solid #ececec; border-radius:10px;
        padding: 14px 18px;
    }
    .yt-compare h3 { font-size:0.95rem; margin:0 0 10px; color:#374151; font-weight:600; }
    .yt-compare-row { display:flex; gap:10px; }
    .yt-btn-old {
        flex:1; padding:14px; border-radius:8px;
        background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;
        text-align:center; font-weight:600; font-size:0.88rem;
        text-decoration:none;
    }
    .yt-btn-old:hover { background:#dcfce7; color:#166534; text-decoration:none; }
    .yt-btn-new {
        flex:1; padding:14px; border-radius:8px;
        background: linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; border:none;
        text-align:center; font-weight:600; font-size:0.88rem;
        cursor:pointer;
    }
    .yt-btn-new:hover { filter:brightness(1.05); }

    .yt-info {
        background:#fef9c3; border:1px solid #fde047; color:#713f12;
        padding:10px 14px; border-radius:8px; font-size:0.82rem;
        margin-bottom:14px;
    }
</style>

<div class="yt-wrap">

    <div class="yt-header">
        <div>
            <h1><i class="fa fa-calendar-check-o"></i> {{ $sayfa_baslik }}</h1>
            <span class="yt-sub">Yeni randevu ekleme tasarımının test sayfası — eski takvim aynen çalışmaya devam ediyor.</span>
        </div>
        <button type="button" class="yt-cta" data-toggle="modal" data-target="#modal-view-event-add-v2">
            <i class="fa fa-plus"></i> Yeni Randevu (v2)
        </button>
    </div>

    <div class="yt-info">
        <i class="fa fa-info-circle"></i> Bu sayfa <strong>sadece v2 randevu modalini</strong> test etmek için ayrı bir rotada açıldı (<code>/randevularyenitakvim</code>). Form henüz backend'e bağlı değil; submit demo amaçlı uyarı verir.
    </div>

    <div class="yt-grid">
        <div class="yt-card">
            <h3><i class="fa fa-check-circle" style="color:#10b981;"></i> v2'de değişen şeyler</h3>
            <ul class="yt-list">
                <li>Modal genişliği <strong>1200px → 640px</strong></li>
                <li>Tek kolon, kart-içinde-kart yok</li>
                <li>Hizmet satırı: <strong>Hizmet → Personel → Oda → Süre</strong></li>
                <li>"Tümüne uygula" sadece 2+ satırda görünür</li>
                <li>Tekrarlayan: switch açılınca detay açılır</li>
                <li>Notlar: collapsible (varsayılan kapalı)</li>
                <li>Saat Kapama: header'daki kilit ikonu ile geçiş</li>
                <li>Tek satırda <strong>Sil disabled</strong></li>
            </ul>
        </div>
        <div class="yt-card">
            <h3><i class="fa fa-clock-o" style="color:#6366f1;"></i> Test akışı</h3>
            <ul class="yt-list">
                <li><strong>Müşteri ara</strong> — eski modaldekiyle aynı select</li>
                <li><strong>"Hizmet Ekle"</strong> ile 2. satır ekle → bulk panel görünmeli</li>
                <li><strong>Tek satır kalınca</strong> sil butonu disable olmalı</li>
                <li><strong>Tekrarlayan</strong> switch'i — alanlar açılıp kapanmalı</li>
                <li><strong>Not</strong> butonu — toggle çalışmalı</li>
                <li>Header'daki <strong>kilit ikonu</strong> — Saat Kapama moduna geçer</li>
            </ul>
        </div>
    </div>

    <div class="yt-compare">
        <h3><i class="fa fa-columns"></i> Yan yana karşılaştır</h3>
        <div class="yt-compare-row">
            <a href="#" class="yt-btn-old" data-toggle="modal" data-target="#modal-view-event-add">
                <i class="fa fa-calendar"></i> Eski Modal (v1)
            </a>
            <button type="button" class="yt-btn-new" data-toggle="modal" data-target="#modal-view-event-add-v2">
                <i class="fa fa-magic"></i> Yeni Modal (v2)
            </button>
        </div>
    </div>
</div>

@endsection
