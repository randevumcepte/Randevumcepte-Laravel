<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sistem Yönetim' }} | randevumcepte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{ secure_asset('public/css/sistemyonetim-v2.css') }}">
    @stack('head')
</head>
<body>
@php
    $u = Auth::guard('sistemyonetim')->user();
    $rol = $u ? ($u->rol ?: ($u->admin == 1 ? 'super_admin' : 'destek')) : 'misafir';
    $aktifMenu = $aktifMenu ?? '';
    $rolEtiketleri = [
        'super_admin' => 'Süper Admin',
        'yonetici'    => 'Yönetici',
        'destek'      => 'Destek',
        'izleyici'    => 'İzleyici',
    ];
    try {
        // Layout her sayfada render edildigi icin cacheli; yeni ticket badge'da hizli gorunsun diye 15sn
        $bekleyen = \Cache::remember('sy.layout.bekleyen_ticket', 15, function () {
            return \App\SistemYonetim\DestekTalebi::whereIn('durum', ['acik','islemde','bekliyor'])->count();
        });
    } catch (\Exception $e) { $bekleyen = 0; }
@endphp

<div class="sy-app">

    <aside class="sy-sidebar" id="sySidebar">
        <div class="sy-brand">
            <div class="logo-mark">RC</div>
            <div class="brand-text">
                <div class="name">randevumcepte</div>
                <div class="sub">Sistem Yönetim</div>
            </div>
        </div>

        <nav class="sy-nav">
            <div class="sy-nav-section">Genel</div>
            <a href="/sistemyonetim/v2/dashboard" class="sy-nav-item {{ $aktifMenu === 'dashboard' ? 'active' : '' }}">
                <span class="icon mdi mdi-view-dashboard"></span>
                Dashboard
            </a>
            <a href="/sistemyonetim/v2/salonlar" class="sy-nav-item {{ $aktifMenu === 'salonlar' ? 'active' : '' }}">
                <span class="icon mdi mdi-store"></span>
                Salonlar
            </a>
            <a href="/sistemyonetim/v2/ticket" class="sy-nav-item {{ $aktifMenu === 'ticket' ? 'active' : '' }}">
                <span class="icon mdi mdi-lifebuoy"></span>
                Destek Talepleri
                @if($bekleyen > 0)
                    <span class="badge">{{ $bekleyen }}</span>
                @endif
            </a>

            <a href="/sistemyonetim/v2/whatsapp" class="sy-nav-item {{ $aktifMenu === 'whatsapp' ? 'active' : '' }}">
                <span class="icon mdi mdi-whatsapp"></span>
                WhatsApp Yönetim
            </a>
            <a href="/sistemyonetim/v2/duyuru" class="sy-nav-item {{ $aktifMenu === 'duyuru' ? 'active' : '' }}">
                <span class="icon mdi mdi-bullhorn"></span>
                Duyurular
            </a>
            <a href="/sistemyonetim/v2/risk" class="sy-nav-item {{ $aktifMenu === 'risk' ? 'active' : '' }}">
                <span class="icon mdi mdi-alert-circle"></span>
                Risk Altındakiler
            </a>
            <a href="/sistemyonetim/v2/hazir-cevap" class="sy-nav-item {{ $aktifMenu === 'hazircevap' ? 'active' : '' }}">
                <span class="icon mdi mdi-message-text-fast"></span>
                Hazır Cevaplar
            </a>

            @if(in_array($rol, ['super_admin','yonetici']))
                <div class="sy-nav-section">Ekip & İzleme</div>
                <a href="/sistemyonetim/v2/ekip" class="sy-nav-item {{ $aktifMenu === 'ekip' ? 'active' : '' }}">
                    <span class="icon mdi mdi-account-group"></span>
                    Ekip & Roller
                </a>
                <a href="/sistemyonetim/v2/performans" class="sy-nav-item {{ $aktifMenu === 'performans' ? 'active' : '' }}">
                    <span class="icon mdi mdi-chart-line"></span>
                    Ekip Performansı
                </a>
                <a href="/sistemyonetim/v2/aktivite-log" class="sy-nav-item {{ $aktifMenu === 'aktivite' ? 'active' : '' }}">
                    <span class="icon mdi mdi-timeline-clock"></span>
                    Aktivite Logu
                </a>
                <a href="/sistemyonetim/v2/guvenlik/girisler" class="sy-nav-item {{ $aktifMenu === 'guvenlik' ? 'active' : '' }}">
                    <span class="icon mdi mdi-shield-account"></span>
                    Güvenlik
                </a>
                <a href="/sistemyonetim/v2/sistem-saglik" class="sy-nav-item {{ $aktifMenu === 'saglik' ? 'active' : '' }}">
                    <span class="icon mdi mdi-pulse"></span>
                    Sistem Sağlık
                </a>
            @endif

            <div class="sy-nav-section">Eski Panel</div>
            <a href="/sistemyonetim/isletmeler" class="sy-nav-item">
                <span class="icon mdi mdi-arrow-left"></span>
                Klasik Görünüm
            </a>
        </nav>

        <div class="sy-sidebar-foot">
            <div>v2.0 · {{ now()->format('d.m.Y') }}</div>
            <div style="margin-top:4px"><a href="/sistemyonetim/cikisyap"><span class="mdi mdi-logout"></span> Çıkış</a></div>
        </div>
    </aside>

    <header class="sy-topbar">
        <div class="sy-flex-row">
            <span class="sy-sidebar-toggle mdi mdi-menu" onclick="document.getElementById('sySidebar').classList.toggle('open')" style="font-size:22px"></span>
            <h1>{{ $title ?? '' }}</h1>
        </div>

        <div class="sy-search-box">
            <span class="mdi mdi-magnify"></span>
            <input type="text" id="syGlobalAra" placeholder="Salon, ticket, ekip ara... (Ctrl+K)" autocomplete="off">
            <div id="syGlobalAraSonuc"></div>
        </div>

        <div class="sy-topbar-right">
            <a href="/sistemyonetim/v2/ticket/yeni" class="sy-btn sy-btn-soft sy-btn-sm" title="Yeni Talep">
                <span class="mdi mdi-plus"></span> Yeni Talep
            </a>

            <div class="sy-bell-wrap">
                <button class="sy-bell" id="syBell" type="button" title="Bildirimler">
                    <span class="mdi mdi-bell"></span>
                    <span class="sy-bell-badge" id="syBellBadge" style="display:none">0</span>
                </button>
                <div class="sy-bell-panel" id="syBellPanel">
                    <div class="sy-bell-head">Bildirimler</div>
                    <div class="sy-bell-list" id="syBellList">
                        <div class="sy-text-muted sy-fs-13" style="padding:30px;text-align:center">Yükleniyor...</div>
                    </div>
                </div>
            </div>

            <div class="sy-user-chip" id="syUserMenuTrigger" style="cursor:pointer">
                <div class="avatar">{{ mb_substr($u->name ?? '?', 0, 1) }}</div>
                <span>{{ $u->name }}</span>
                <span class="rol">· {{ $rolEtiketleri[$rol] ?? $rol }}</span>
                <div class="sy-user-menu" id="syUserMenu">
                    <a href="/sistemyonetim/v2/profil"><span class="mdi mdi-account-cog"></span> Profilim</a>
                    <a href="/sistemyonetim/v2/duyuru"><span class="mdi mdi-bullhorn"></span> Duyurular</a>
                    <hr style="margin:6px 0;border:0;border-top:1px solid var(--sy-border)">
                    <a href="/sistemyonetim/cikisyap" style="color:var(--sy-danger)"><span class="mdi mdi-logout"></span> Çıkış Yap</a>
                </div>
            </div>
        </div>
    </header>

    <main class="sy-main">
        @if(session('basari'))
            <div class="sy-alert sy-alert-success"><span class="mdi mdi-check-circle"></span> {{ session('basari') }}</div>
        @endif
        @if(session('hata'))
            <div class="sy-alert sy-alert-danger"><span class="mdi mdi-alert"></span> {{ session('hata') }}</div>
        @endif
        @if($errors->any())
            <div class="sy-alert sy-alert-danger">
                <strong>Hata:</strong>
                <ul style="margin:6px 0 0 18px">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
// Global arama
(function(){
    const inp = document.getElementById('syGlobalAra');
    const sonuc = document.getElementById('syGlobalAraSonuc');
    if (!inp) return;

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            inp.focus();
        }
        if (e.key === 'Escape') {
            sonuc.classList.remove('visible');
            inp.blur();
        }
    });

    let timer = null;
    inp.addEventListener('input', function(){
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) {
            sonuc.classList.remove('visible');
            sonuc.innerHTML = '';
            return;
        }
        timer = setTimeout(()=>{
            fetch('/sistemyonetim/v2/api/global-arama?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(d => {
                    let html = '';
                    if (d.salon && d.salon.length) {
                        html += '<div class="sy-search-section">Salonlar (' + d.salon.length + ')</div>';
                        d.salon.forEach(s => {
                            html += '<a class="sy-search-item" href="/sistemyonetim/v2/salon/' + s.id + '">' +
                                '<div class="name">' + escapeHtml(s.salon_adi || '') + (s.askiya_alindi ? ' <span class="sy-badge sy-badge-danger">askıda</span>' : '') + '</div>' +
                                '<div class="meta">' + (s.yetkili_adi ? escapeHtml(s.yetkili_adi) + ' · ' : '') + (s.telefon_1 ? escapeHtml(s.telefon_1) : '') + '</div>' +
                                '</a>';
                        });
                    }
                    if (d.ticket && d.ticket.length) {
                        html += '<div class="sy-search-section">Talepler (' + d.ticket.length + ')</div>';
                        d.ticket.forEach(t => {
                            html += '<a class="sy-search-item" href="/sistemyonetim/v2/ticket/' + t.id + '">' +
                                '<div class="name">' + escapeHtml(t.numara) + ' — ' + escapeHtml(t.konu || '') + '</div>' +
                                '<div class="meta">' + (t.salon_adi ? escapeHtml(t.salon_adi) + ' · ' : '') + escapeHtml(t.durum) + ' · ' + escapeHtml(t.oncelik) + '</div>' +
                                '</a>';
                        });
                    }
                    if (d.ekip && d.ekip.length) {
                        html += '<div class="sy-search-section">Ekip (' + d.ekip.length + ')</div>';
                        d.ekip.forEach(e => {
                            html += '<a class="sy-search-item" href="/sistemyonetim/v2/ekip/' + e.id + '/duzenle">' +
                                '<div class="name">' + escapeHtml(e.name) + '</div>' +
                                '<div class="meta">' + escapeHtml(e.email) + ' · ' + escapeHtml(e.rol || '') + '</div>' +
                                '</a>';
                        });
                    }
                    if (!html) html = '<div class="sy-text-muted" style="padding:20px;text-align:center">Sonuç yok</div>';
                    sonuc.innerHTML = html;
                    sonuc.classList.add('visible');
                });
        }, 200);
    });
    document.addEventListener('click', function(e){
        if (!sonuc.contains(e.target) && e.target !== inp) {
            sonuc.classList.remove('visible');
        }
    });
})();

function escapeHtml(t) {
    if (t === null || t === undefined) return '';
    return String(t).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// Notification bell
(function(){
    const bell = document.getElementById('syBell');
    const panel = document.getElementById('syBellPanel');
    const badge = document.getElementById('syBellBadge');
    const list = document.getElementById('syBellList');
    if (!bell) return;

    function load() {
        fetch('/sistemyonetim/v2/api/bildirim-feed')
            .then(r => r.json())
            .then(d => {
                if (d.sayi > 0) {
                    badge.textContent = d.sayi;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
                if (!d.liste.length) {
                    list.innerHTML = '<div class="sy-empty"><div class="icon mdi mdi-check-all"></div><div class="baslik">Bildirim yok</div></div>';
                    return;
                }
                list.innerHTML = d.liste.map(b =>
                    '<a class="sy-bell-item" href="' + b.link + '">' +
                    '<div class="baslik"><span class="mdi ' + b.ikon + '" style="color:var(--sy-' + b.renk + ')"></span> ' + escapeHtml(b.baslik) + '</div>' +
                    '<div class="meta">' + escapeHtml(b.aciklama) + ' · ' + escapeHtml(b.zaman) + '</div>' +
                    '</a>'
                ).join('');
            });
    }

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.toggle('visible');
        if (panel.classList.contains('visible')) load();
    });
    document.addEventListener('click', e => {
        if (!panel.contains(e.target)) panel.classList.remove('visible');
    });
    load();
    setInterval(load, 60000); // 1dk arayla yenile
})();

// User menu
(function(){
    const trig = document.getElementById('syUserMenuTrigger');
    const menu = document.getElementById('syUserMenu');
    if (!trig) return;
    trig.addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.toggle('visible');
    });
    document.addEventListener('click', e => {
        if (!menu.contains(e.target)) menu.classList.remove('visible');
    });
})();
</script>

@stack('scripts')
</body>
</html>
