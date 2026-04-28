@extends("layout.layout_isletmeadmin")
@section("content")

<style>
/* ==========================================================================
   HESABIM SAYFASI - Modern, brand-renkli, premium tasarim
   ========================================================================== */
.hesabim-page {
    --rmc-purple: #5C008E;
    --rmc-purple-2: #7B2FB8;
    --rmc-purple-3: #9D5DC8;
    --rmc-magenta: #d946ef;
    --rmc-dark: #1a0533;
    --rmc-text: #2d3748;
    --rmc-muted: #6b7280;
    --rmc-line: #e5e7eb;
    --rmc-soft-bg: #f8f9fc;
    padding-bottom: 40px;
}

/* ===== Hero Header ===== */
.hesabim-hero {
    position: relative;
    background:
        radial-gradient(circle at 12% 25%, rgba(217,70,239,0.55) 0%, transparent 45%),
        radial-gradient(circle at 88% 78%, rgba(157,93,200,0.55) 0%, transparent 50%),
        linear-gradient(135deg, #1a0533 0%, #5C008E 55%, #7B2FB8 100%);
    border-radius: 22px;
    color: #fff;
    padding: 30px 40px;
    margin-bottom: 26px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(92, 0, 142, 0.30);
}
.hesabim-hero::before, .hesabim-hero::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
}
.hesabim-hero::before {
    top: -90px; left: -80px;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(217,70,239,0.55), transparent 70%);
}
.hesabim-hero::after {
    bottom: -100px; right: -90px;
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(157,93,200,0.6), transparent 70%);
}
.hesabim-hero-row {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
    justify-content: space-between;
}
.hesabim-hero-left {
    display: flex;
    align-items: center;
    gap: 22px;
    flex: 1;
    min-width: 280px;
}
.hesabim-hero-avatar {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.1);
    box-shadow: 0 8px 18px rgba(0,0,0,0.25);
}
.hesabim-hero-meta h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 4px;
    color: #fff;
    line-height: 1.2;
}
.hesabim-hero-meta p {
    font-size: 14px;
    margin: 0;
    color: rgba(255,255,255,0.8);
}
.hesabim-hero-meta .hesabim-userinfo {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    font-size: 13px;
}
.hesabim-hero-meta .hesabim-userinfo span {
    color: rgba(255,255,255,0.85);
}
.hesabim-hero-meta .hesabim-userinfo i {
    margin-right: 5px;
    opacity: 0.7;
}

.hesabim-hero-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}
.hesabim-paket-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    backdrop-filter: blur(8px);
}
.hesabim-paket-badge i {
    font-size: 14px;
    color: #fdba74;
}
.hesabim-kalan-gun {
    background: rgba(255,255,255,0.92);
    color: var(--rmc-purple);
    padding: 12px 22px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 15px;
    box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    display: flex;
    align-items: center;
    gap: 10px;
}
.hesabim-kalan-gun .num {
    font-size: 24px;
    line-height: 1;
}
.hesabim-kalan-gun.warn { color: #b45309; background: #fef3c7; }
.hesabim-kalan-gun.danger { color: #b91c1c; background: #fee2e2; }

/* ===== Sekmeler ===== */
.hesabim-tabs {
    display: flex;
    gap: 6px;
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.hesabim-tab {
    flex: 1;
    min-width: 140px;
    padding: 12px 18px;
    border-radius: 10px;
    background: transparent;
    border: none;
    color: var(--rmc-muted);
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.25s ease;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.hesabim-tab:hover { color: var(--rmc-purple); }
.hesabim-tab.active {
    background: linear-gradient(135deg, var(--rmc-purple) 0%, var(--rmc-magenta) 100%);
    color: #fff;
    box-shadow: 0 8px 18px rgba(92,0,142,0.30);
}
.hesabim-tab.active i { color: #fff; }
.hesabim-tab i { color: var(--rmc-purple); }

/* ===== Sekme Icerikleri ===== */
.hesabim-panel { display: none; }
.hesabim-panel.active { display: block; animation: hesabimFadeIn 0.4s ease; }
@keyframes hesabimFadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== Üyelik Kart Grid ===== */
.hesabim-uyelik-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 22px;
    margin-bottom: 22px;
}
@media (max-width: 900px) {
    .hesabim-uyelik-grid { grid-template-columns: 1fr; }
}

.hesabim-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    padding: 28px;
    border: 1px solid #f0f1f4;
}
.hesabim-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}
.hesabim-card-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--rmc-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.hesabim-card-title i {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--rmc-purple) 0%, var(--rmc-magenta) 100%);
    color: #fff;
    font-size: 15px;
    box-shadow: 0 4px 10px rgba(92,0,142,0.25);
}
.hesabim-card-title i.alt {
    background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%);
    box-shadow: 0 4px 10px rgba(6,182,212,0.25);
}

/* Plan ozelinde liste */
.hesabim-info-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.hesabim-info-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px dashed var(--rmc-line);
    font-size: 14px;
}
.hesabim-info-list li:last-child { border-bottom: none; }
.hesabim-info-list .label {
    color: var(--rmc-muted);
    font-weight: 500;
}
.hesabim-info-list .value {
    color: var(--rmc-text);
    font-weight: 700;
}
.hesabim-info-list .value.muted { color: var(--rmc-muted); font-weight: 500; }

/* Yan Aksiyon Karti */
.hesabim-action-card {
    background: linear-gradient(135deg, #1a0533 0%, #5C008E 100%);
    color: #fff;
    border-radius: 18px;
    padding: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 14px 30px rgba(92,0,142,0.30);
}
.hesabim-action-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(217,70,239,0.4), transparent 70%);
    filter: blur(30px);
}
.hesabim-action-card h3 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 800;
    position: relative;
}
.hesabim-action-card p {
    margin: 0 0 18px;
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.5;
    position: relative;
}
.hesabim-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: #fff;
    color: var(--rmc-purple);
    border-radius: 30px;
    font-weight: 800;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    text-decoration: none;
    transition: all 0.25s ease;
    position: relative;
    box-shadow: 0 6px 14px rgba(0,0,0,0.18);
}
.hesabim-action-btn:hover {
    transform: translateY(-2px);
    color: var(--rmc-magenta);
    box-shadow: 0 10px 18px rgba(0,0,0,0.25);
    text-decoration: none;
}

/* ===== Hizmet Kartlari ===== */
.hesabim-hizmet-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 18px;
}
.hizmet-card {
    background: #fff;
    border-radius: 16px;
    padding: 22px;
    border: 1px solid #f0f1f4;
    box-shadow: 0 4px 14px rgba(15,23,42,0.05);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}
.hizmet-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(15,23,42,0.08);
}
.hizmet-card-head {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 14px;
}
.hizmet-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    flex-shrink: 0;
    box-shadow: 0 6px 14px rgba(0,0,0,0.12);
}
.hizmet-icon.mor { background: linear-gradient(135deg, #5C008E 0%, #d946ef 100%); }
.hizmet-icon.yesil { background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%); }
.hizmet-icon.turuncu { background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%); }
.hizmet-icon.mavi { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
.hizmet-icon.kirmizi { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); }

.hizmet-card-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--rmc-text);
    margin: 0 0 3px;
}
.hizmet-card-desc {
    font-size: 13px;
    color: var(--rmc-muted);
    margin: 0;
}

.hizmet-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px dashed var(--rmc-line);
    font-size: 13px;
}
.hizmet-status-row .lbl { color: var(--rmc-muted); }
.hizmet-status-row .val { color: var(--rmc-text); font-weight: 700; }
.hizmet-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.hizmet-pill.aktif { background: #dcfce7; color: #15803d; }
.hizmet-pill.pasif { background: #fee2e2; color: #b91c1c; }
.hizmet-pill.deneme { background: #fef3c7; color: #b45309; }

/* ===== Fatura Tablosu ===== */
.hesabim-table-wrap {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(15,23,42,0.05);
    border: 1px solid #f0f1f4;
}
.hesabim-table {
    width: 100%;
    border-collapse: collapse;
}
.hesabim-table thead {
    background: var(--rmc-soft-bg);
}
.hesabim-table th {
    padding: 14px 18px;
    font-size: 12px;
    font-weight: 700;
    color: var(--rmc-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 1px solid var(--rmc-line);
}
.hesabim-table td {
    padding: 16px 18px;
    font-size: 14px;
    color: var(--rmc-text);
    border-bottom: 1px solid #f3f4f6;
}
.hesabim-table tr:last-child td { border-bottom: none; }
.hesabim-table tr:hover td { background: #fafbfc; }
.hesabim-table .tutar { font-weight: 800; color: var(--rmc-purple); }
.hesabim-table .durum-pill {
    display: inline-flex;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.hesabim-table .durum-pill.odendi { background: #dcfce7; color: #15803d; }
.hesabim-table .durum-pill.bekliyor { background: #fef3c7; color: #b45309; }
.hesabim-table .durum-pill.iptal { background: #fee2e2; color: #b91c1c; }

.hesabim-empty {
    text-align: center;
    padding: 60px 24px;
    color: var(--rmc-muted);
}
.hesabim-empty i {
    font-size: 56px;
    color: var(--rmc-line);
    margin-bottom: 14px;
    display: block;
}
.hesabim-empty p {
    font-size: 14px;
    margin: 0 0 4px;
}
.hesabim-empty small { color: #adb5bd; font-size: 12px; }

/* ===== Fatura Bilgileri Form ===== */
.hesabim-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 700px) {
    .hesabim-form-grid { grid-template-columns: 1fr; }
}
.hesabim-form-group { margin-bottom: 4px; }
.hesabim-form-group label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--rmc-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}
.hesabim-form-group input,
.hesabim-form-group textarea {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--rmc-line);
    border-radius: 10px;
    font-size: 14px;
    color: var(--rmc-text);
    background: #fafbfc;
    transition: all 0.2s ease;
    font-family: inherit;
}
.hesabim-form-group input:focus,
.hesabim-form-group textarea:focus {
    outline: none;
    border-color: var(--rmc-purple);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(92,0,142,0.10);
}
.hesabim-form-actions {
    margin-top: 18px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.hesabim-btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 24px;
    background: linear-gradient(135deg, var(--rmc-purple) 0%, var(--rmc-magenta) 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 6px 14px rgba(92,0,142,0.30);
}
.hesabim-btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(92,0,142,0.40);
    color: #fff;
}

/* Mobil */
@media (max-width: 600px) {
    .hesabim-hero { padding: 24px 22px; }
    .hesabim-hero-meta h1 { font-size: 22px; }
    .hesabim-hero-right { align-items: flex-start; width: 100%; }
    .hesabim-card { padding: 20px; }
}
</style>

<div class="hesabim-page">
    <div class="page-header" style="display:none;">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
            </div>
        </div>
    </div>

    @php
        $kalanGun = (int) preg_replace('/[^-0-9]/','', (string)$kalan_uyelik_suresi);
        $isLisansBitti = str_contains((string)$kalan_uyelik_suresi, '-');
        $kalanClass = '';
        if($isLisansBitti) $kalanClass = 'danger';
        elseif($kalanGun <= 7) $kalanClass = 'danger';
        elseif($kalanGun <= 30) $kalanClass = 'warn';

        $aktifPaket = $paketAdlari[$isletme->uyelik_turu] ?? 'Üyelik Yok';
        $aktifPeriyot = $periyotAdlari[$isletme->uyelik_periyodu] ?? '-';
        $kullaniciResim = $kullanici->profil_resim ?? '/public/isletmeyonetim_assets/img/avatar.png';
    @endphp

    <!-- HERO HEADER -->
    <div class="hesabim-hero">
        <div class="hesabim-hero-row">
            <div class="hesabim-hero-left">
                <img src="{{secure_asset($kullaniciResim)}}" alt="" class="hesabim-hero-avatar"
                     onerror="this.src='/public/isletmeyonetim_assets/img/avatar.png'">
                <div class="hesabim-hero-meta">
                    <h1>{{ $isletme->salon_adi }}</h1>
                    <p>Hesap sahibi: <strong>{{ $kullanici->name }}</strong></p>
                    <div class="hesabim-userinfo">
                        @if($kullanici->email)<span><i class="fa fa-envelope"></i>{{ $kullanici->email }}</span>@endif
                        @if($kullanici->gsm1)<span><i class="fa fa-phone"></i>{{ $kullanici->gsm1 }}</span>@endif
                    </div>
                </div>
            </div>
            <div class="hesabim-hero-right">
                <div class="hesabim-paket-badge">
                    <i class="fa fa-star"></i>
                    {{ $aktifPaket }} • {{ $aktifPeriyot }}
                </div>
                @if($isLisansBitti)
                <div class="hesabim-kalan-gun {{$kalanClass}}">
                    <i class="fa fa-exclamation-circle"></i>
                    Üyelik Süresi Doldu
                </div>
                @else
                <div class="hesabim-kalan-gun {{$kalanClass}}">
                    <span class="num">{{ $kalanGun }}</span>
                    <span>Gün Kaldı</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="hesabim-tabs" role="tablist">
        <button class="hesabim-tab active" data-tab="uyelik" type="button">
            <i class="fa fa-id-card-o"></i> Üyelik Bilgileri
        </button>
        <button class="hesabim-tab" data-tab="hizmetler" type="button">
            <i class="fa fa-th-large"></i> Aldığım Hizmetler
        </button>
        <button class="hesabim-tab" data-tab="fatura-bilgi" type="button">
            <i class="fa fa-file-text"></i> Fatura Bilgileri
        </button>
        <button class="hesabim-tab" data-tab="faturalar" type="button">
            <i class="fa fa-list-alt"></i> Faturalarım
        </button>
    </div>

    <!-- PANEL: ÜYELİK -->
    <div class="hesabim-panel active" data-panel="uyelik">
        <div class="hesabim-uyelik-grid">
            <div class="hesabim-card">
                <div class="hesabim-card-header">
                    <h3 class="hesabim-card-title"><i class="fa fa-rocket"></i> Aktif Üyelik Detayları</h3>
                </div>
                <ul class="hesabim-info-list">
                    <li>
                        <span class="label">Paket Türü</span>
                        <span class="value">{{ $aktifPaket }}</span>
                    </li>
                    <li>
                        <span class="label">Ödeme Periyodu</span>
                        <span class="value">{{ $aktifPeriyot }}</span>
                    </li>
                    <li>
                        <span class="label">Üyelik Bitiş Tarihi</span>
                        <span class="value {{ $isLisansBitti ? 'muted' : '' }}">
                            {{ $isletme->uyelik_bitis_tarihi ? \Carbon\Carbon::parse($isletme->uyelik_bitis_tarihi)->format('d.m.Y') : 'Belirtilmemiş' }}
                        </span>
                    </li>
                    <li>
                        <span class="label">Kalan Süre</span>
                        <span class="value">
                            @if($isLisansBitti)
                                <span style="color:#b91c1c;">Süre Doldu</span>
                            @else
                                {{ $kalanGun }} gün
                            @endif
                        </span>
                    </li>
                    <li>
                        <span class="label">İşletme Kayıt Tarihi</span>
                        <span class="value muted">{{ $isletme->created_at ? \Carbon\Carbon::parse($isletme->created_at)->format('d.m.Y') : '-' }}</span>
                    </li>
                </ul>
            </div>

            <div class="hesabim-action-card">
                <h3>Paketinizi Yükseltin</h3>
                <p>Daha fazla özellik, daha çok kullanıcı ve sınırsız randevu için üst pakete geçebilirsiniz.</p>
                <a href="/isletmeyonetim/uyelik{{ isset($_GET['sube']) ? '?sube='.$isletme->id : '' }}" class="hesabim-action-btn">
                    <i class="fa fa-arrow-up"></i> Paketi Yükselt
                </a>
            </div>
        </div>
    </div>

    <!-- PANEL: HİZMETLER -->
    <div class="hesabim-panel" data-panel="hizmetler">
        <div class="hesabim-hizmet-grid">
            @foreach($hizmetler as $hizmet)
            <div class="hizmet-card">
                <div class="hizmet-card-head">
                    <div class="hizmet-icon {{ $hizmet['renk'] }}">
                        <i class="fa {{ $hizmet['icon'] }}"></i>
                    </div>
                    <div>
                        <h4 class="hizmet-card-title">{{ $hizmet['ad'] }}</h4>
                        <p class="hizmet-card-desc">{{ $hizmet['aciklama'] }}</p>
                    </div>
                </div>
                <div class="hizmet-status-row">
                    <div>
                        <span class="lbl">Periyot:</span>
                        <span class="val">{{ $hizmet['periyot'] ?? '-' }}</span>
                    </div>
                    @if(!empty($hizmet['deneme']))
                        <span class="hizmet-pill deneme"><i class="fa fa-clock-o"></i> Deneme</span>
                    @elseif($hizmet['aktif'])
                        <span class="hizmet-pill aktif"><i class="fa fa-check"></i> Aktif</span>
                    @else
                        <span class="hizmet-pill pasif"><i class="fa fa-times"></i> Pasif</span>
                    @endif
                </div>
                @if(!empty($hizmet['bitis']))
                <div class="hizmet-status-row" style="margin-top:8px;padding-top:10px;">
                    <div>
                        <span class="lbl">Bitiş:</span>
                        <span class="val">{{ \Carbon\Carbon::parse($hizmet['bitis'])->format('d.m.Y') }}</span>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- PANEL: FATURA BİLGİLERİ -->
    <div class="hesabim-panel" data-panel="fatura-bilgi">
        <div class="hesabim-card">
            <div class="hesabim-card-header">
                <h3 class="hesabim-card-title"><i class="alt fa fa-id-card"></i> Fatura Bilgileriniz</h3>
            </div>
            <form id="faturaBilgiForm">
                @csrf
                <input type="hidden" name="sube" value="{{ $isletme->id }}">
                <div class="hesabim-form-grid">
                    <div class="hesabim-form-group">
                        <label>Vergi / Firma Ünvanı</label>
                        <input type="text" name="vergi_adi" value="{{ $isletme->vergi_adi }}" placeholder="Şirket Ünvanı">
                    </div>
                    <div class="hesabim-form-group">
                        <label>Vergi / TCKN Numarası</label>
                        <input type="text" name="vergi_no" value="{{ $isletme->vergi_no }}" placeholder="Vergi numarası">
                    </div>
                    <div class="hesabim-form-group" style="grid-column: 1 / -1;">
                        <label>Vergi Adresi</label>
                        <textarea name="vergi_adresi" rows="2" placeholder="Tam adres">{{ $isletme->vergi_adresi }}</textarea>
                    </div>
                    <div class="hesabim-form-group">
                        <label>KDV Oranı (%)</label>
                        <input type="number" name="kdv_orani" value="{{ $isletme->kdv_orani }}" placeholder="20" step="0.01">
                    </div>
                </div>
                <div class="hesabim-form-actions">
                    <button type="submit" class="hesabim-btn-save">
                        <i class="fa fa-check"></i> Bilgileri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PANEL: FATURALAR -->
    <div class="hesabim-panel" data-panel="faturalar">
        <div class="hesabim-table-wrap">
            @if($faturalar->count() > 0)
            <table class="hesabim-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Açıklama</th>
                        <th>Periyot</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($faturalar as $fatura)
                    <tr>
                        <td>{{ isset($fatura->odeme_tarihi) ? \Carbon\Carbon::parse($fatura->odeme_tarihi)->format('d.m.Y') : '-' }}</td>
                        <td>{{ $fatura->aciklama ?? '-' }}</td>
                        <td>{{ $fatura->periyot ?? '-' }}</td>
                        <td class="tutar">{{ isset($fatura->tutar) ? number_format($fatura->tutar, 2, ',', '.') : '0,00' }} ₺</td>
                        <td>
                            @php $d = $fatura->durum ?? 'odendi'; @endphp
                            <span class="durum-pill {{ $d }}">{{ ucfirst($d) }}</span>
                        </td>
                        <td style="text-align:right;">
                            @if(!empty($fatura->dosya_url))
                            <a href="{{ $fatura->dosya_url }}" target="_blank" class="hizmet-pill aktif" style="text-decoration:none;">
                                <i class="fa fa-download"></i> İndir
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="hesabim-empty">
                <i class="fa fa-file-text-o"></i>
                <p><strong>Henüz fatura kaydı bulunmamaktadır</strong></p>
                <small>Üyelik ve hizmet ödemeleriniz burada listelenecek.</small>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
(function(){
    var tabs = document.querySelectorAll('.hesabim-tab');
    var panels = document.querySelectorAll('.hesabim-panel');
    tabs.forEach(function(t){
        t.addEventListener('click', function(){
            var key = t.getAttribute('data-tab');
            tabs.forEach(function(x){ x.classList.remove('active'); });
            panels.forEach(function(p){ p.classList.remove('active'); });
            t.classList.add('active');
            document.querySelector('.hesabim-panel[data-panel="'+key+'"]').classList.add('active');
            try { history.replaceState(null, '', '#'+key); } catch(e){}
        });
    });

    // URL hash ile sekme acilabilir
    if(location.hash){
        var key = location.hash.replace('#','');
        var btn = document.querySelector('.hesabim-tab[data-tab="'+key+'"]');
        if(btn) btn.click();
    }

    // Fatura bilgi formu
    var form = document.getElementById('faturaBilgiForm');
    if(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var fd = new FormData(form);
            var btn = form.querySelector('button[type=submit]');
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...';

            fetch('/isletmeyonetim/hesabim/fatura-bilgi-guncelle', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r){ return r.json(); })
            .then(function(d){
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if(d.success){
                    if(window.swal){
                        swal('Başarılı', d.message, 'success');
                    } else {
                        alert(d.message || 'Kaydedildi');
                    }
                } else {
                    alert(d.message || 'Hata oluştu');
                }
            })
            .catch(function(){
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Bağlantı hatası. Lütfen tekrar deneyin.');
            });
        });
    }
})();
</script>
@endsection
