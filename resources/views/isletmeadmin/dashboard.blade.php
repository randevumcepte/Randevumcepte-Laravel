@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

@php
    $subeQuery = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
    $subeParam = isset($_GET['sube']) ? '&sube='.$isletme->id : '';
    $apiSubeParam = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/countUp.js/2.8.0/countUp.umd.min.js" defer></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
  --dash-bg:#f7f8fc;
  --dash-card-bg:#ffffff;
  --dash-border:#eef0f4;
  --dash-text:#1a2233;
  --dash-text-soft:#5c6678;
  --dash-text-muted:#9aa3b2;
  --dash-accent:#5b6bf7;
  --dash-accent-soft:#eef0ff;
  --dash-green:#27c281;
  --dash-orange:#ff9f43;
  --dash-pink:#ff6f91;
  --dash-blue:#3aaaff;
  --dash-violet:#9b5cff;
  --dash-radius:18px;
  --dash-shadow:0 2px 14px rgba(34,42,82,0.05);
}

.rmc-dash-wrap{padding:6px 4px 40px 4px;}

/* ===== Hızlı Eylem Şeridi ===== */
.rmc-quick-actions{
  display:grid;
  grid-template-columns:repeat(7,minmax(0,1fr));
  gap:14px;
  margin-bottom:22px;
}
@media (max-width:1399px){.rmc-quick-actions{grid-template-columns:repeat(4,1fr);}}
@media (max-width:767px){.rmc-quick-actions{grid-template-columns:repeat(2,1fr);}}

.rmc-qa{
  background:var(--dash-card-bg);
  border:1px solid var(--dash-border);
  border-radius:14px;
  padding:14px 16px;
  display:flex;align-items:center;gap:12px;
  cursor:pointer;
  text-decoration:none !important;
  color:var(--dash-text) !important;
  transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  position:relative;overflow:hidden;
  opacity:0;animation:rmcRise .55s ease forwards;
}
.rmc-qa:hover{transform:translateY(-3px);box-shadow:var(--dash-shadow);border-color:#dde1ec;}
.rmc-qa .rmc-qa-icon{
  width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;
  font-size:18px;flex-shrink:0;
}
.rmc-qa .rmc-qa-text{font-weight:600;font-size:13.5px;line-height:1.25;}
.rmc-qa:nth-child(1){animation-delay:.00s}
.rmc-qa:nth-child(2){animation-delay:.05s}
.rmc-qa:nth-child(3){animation-delay:.10s}
.rmc-qa:nth-child(4){animation-delay:.15s}
.rmc-qa:nth-child(5){animation-delay:.20s}
.rmc-qa:nth-child(6){animation-delay:.25s}
.rmc-qa:nth-child(7){animation-delay:.30s}

/* ===== Card Genel ===== */
.rmc-card{
  background:var(--dash-card-bg);
  border:1px solid var(--dash-border);
  border-radius:var(--dash-radius);
  padding:20px 22px;
  box-shadow:var(--dash-shadow);
  opacity:0;animation:rmcRise .55s ease forwards;
}
.rmc-card .rmc-card-head{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:14px;
}
.rmc-card-title{font-size:15px;font-weight:700;color:var(--dash-text);display:flex;align-items:center;gap:8px;}
.rmc-card-title i{color:var(--dash-accent);}

/* periyot sekmesi */
.rmc-period{display:flex;background:#f3f4f8;border-radius:10px;padding:3px;gap:0;}
.rmc-period button{
  background:transparent;border:0;font-size:12px;font-weight:600;padding:7px 14px;
  border-radius:8px;color:var(--dash-text-soft);cursor:pointer;transition:all .2s;
}
.rmc-period button.is-active{background:#fff;color:var(--dash-accent);box-shadow:0 1px 3px rgba(0,0,0,.07);}

.rmc-grid-top{
  display:grid;grid-template-columns:1.15fr 1fr 1.15fr;gap:18px;margin-bottom:22px;
}
@media (max-width:1199px){.rmc-grid-top{grid-template-columns:1fr;}}

.rmc-card.delay-1{animation-delay:.10s}
.rmc-card.delay-2{animation-delay:.18s}
.rmc-card.delay-3{animation-delay:.26s}
.rmc-card.delay-4{animation-delay:.34s}
.rmc-card.delay-5{animation-delay:.42s}
.rmc-card.delay-6{animation-delay:.50s}

/* ===== Kasa İstatistikleri ===== */
.rmc-kasa-body{display:grid;grid-template-columns:160px 1fr;gap:18px;align-items:center;}
.rmc-kasa-chart-box{position:relative;width:160px;height:160px;}
.rmc-kasa-chart-box canvas{width:100%!important;height:100%!important;}
.rmc-kasa-legend{display:flex;flex-direction:column;gap:10px;}
.rmc-kasa-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--dash-text-soft);}
.rmc-kasa-row .rmc-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.rmc-kasa-row b{margin-left:auto;color:var(--dash-text);font-weight:700;}
.rmc-kasa-total{margin-top:16px;padding-top:14px;border-top:1px dashed var(--dash-border);text-align:center;}
.rmc-kasa-total .lbl{font-size:12px;color:var(--dash-text-muted);font-weight:600;letter-spacing:.4px;text-transform:uppercase;}
.rmc-kasa-total .val{font-size:26px;font-weight:800;color:var(--dash-text);margin-top:4px;}
.rmc-kasa-spark{margin-top:10px;height:36px;}

/* ===== Mini Takvim ===== */
.rmc-cal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.rmc-cal-nav{background:none;border:0;color:var(--dash-text-soft);font-size:18px;cursor:pointer;padding:4px 10px;border-radius:8px;}
.rmc-cal-nav:hover{background:#f3f4f8;}
.rmc-cal-month{font-weight:700;font-size:14px;color:var(--dash-text);}
.rmc-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;}
.rmc-cal-grid .day-name{font-size:11px;color:var(--dash-text-muted);font-weight:600;padding:6px 0;}
.rmc-cal-grid .day{
  position:relative;padding:9px 0;border-radius:9px;font-size:13px;color:var(--dash-text);cursor:default;
  transition:background .15s;
}
.rmc-cal-grid .day:not(.empty):hover{background:#f3f4f8;}
.rmc-cal-grid .day.today{background:var(--dash-accent);color:#fff;font-weight:700;}
.rmc-cal-grid .day .heat-dot{
  position:absolute;bottom:3px;left:50%;transform:translateX(-50%);
  width:6px;height:6px;border-radius:50%;
}
.rmc-cal-grid .day.today .heat-dot{background:#fff;}

/* ===== Randevu Ayrıntıları ===== */
.rmc-randevu-body{display:grid;grid-template-columns:1fr 130px;gap:16px;align-items:center;}
.rmc-randevu-list{display:flex;flex-direction:column;gap:12px;}
.rmc-randevu-item{display:flex;align-items:center;gap:12px;}
.rmc-randevu-item .ico{
  width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;
}
.rmc-randevu-item .txt .num{font-size:18px;font-weight:800;color:var(--dash-text);line-height:1;}
.rmc-randevu-item .txt .lbl{font-size:12px;color:var(--dash-text-soft);}

.rmc-doluluk-ring{
  width:130px;height:130px;border-radius:50%;
  background:conic-gradient(var(--dash-orange) 0deg, #f3f4f8 0deg);
  display:flex;align-items:center;justify-content:center;
  transition:background 1.2s cubic-bezier(.16,1,.3,1);
}
.rmc-doluluk-ring-inner{
  width:104px;height:104px;border-radius:50%;background:#fff;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
}
.rmc-doluluk-pct{font-size:22px;font-weight:800;color:var(--dash-text);}
.rmc-doluluk-lbl{font-size:10.5px;color:var(--dash-text-muted);font-weight:600;text-align:center;letter-spacing:.3px;}

/* ===== Tabbed Alt Panel ===== */
.rmc-tabs-card{margin-bottom:22px;}
.rmc-tabs-nav{display:flex;gap:6px;flex-wrap:wrap;border-bottom:1px solid var(--dash-border);margin-bottom:14px;padding-bottom:0;}
.rmc-tab{
  background:transparent;border:0;padding:10px 16px;font-size:13px;font-weight:600;color:var(--dash-text-soft);
  cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;display:flex;align-items:center;gap:6px;
  transition:color .15s, border-color .15s;
}
.rmc-tab:hover{color:var(--dash-text);}
.rmc-tab.is-active{color:var(--dash-accent);border-bottom-color:var(--dash-accent);}
.rmc-tab .badge-new{background:var(--dash-pink);color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:6px;letter-spacing:.3px;}
.rmc-tab-content{min-height:160px;}
.rmc-tab-table{width:100%;border-collapse:collapse;}
.rmc-tab-table th{font-size:11px;color:var(--dash-text-muted);font-weight:700;letter-spacing:.4px;text-transform:uppercase;text-align:left;padding:8px 12px;border-bottom:1px solid var(--dash-border);}
.rmc-tab-table td{padding:11px 12px;font-size:13px;color:var(--dash-text);border-bottom:1px solid var(--dash-border);}
.rmc-tab-table tbody tr:last-child td{border-bottom:0;}
.rmc-tab-table tbody tr:hover{background:#fafbfd;}
.rmc-tab-empty{padding:36px 16px;text-align:center;color:var(--dash-text-muted);font-size:13px;}

/* ===== Bottom Row (Timeline + Personel) ===== */
.rmc-grid-bottom{display:grid;grid-template-columns:1.3fr 1fr;gap:18px;}
@media (max-width:1199px){.rmc-grid-bottom{grid-template-columns:1fr;}}

.rmc-timeline{max-height:380px;overflow-y:auto;padding-right:6px;}
.rmc-timeline-item{
  display:flex;align-items:flex-start;gap:14px;padding:10px 0;border-left:2px solid var(--dash-border);
  margin-left:8px;padding-left:18px;position:relative;
}
.rmc-timeline-item::before{
  content:'';position:absolute;left:-7px;top:14px;width:12px;height:12px;border-radius:50%;
  background:var(--dash-accent);border:2px solid #fff;box-shadow:0 0 0 2px var(--dash-accent);
}
.rmc-timeline-item.tahsilat::before{background:var(--dash-green);box-shadow:0 0 0 2px var(--dash-green);}
.rmc-timeline-item .t-saat{font-size:12px;color:var(--dash-text-muted);font-weight:700;width:46px;flex-shrink:0;}
.rmc-timeline-item .t-baslik{font-size:13px;color:var(--dash-text);font-weight:500;}
.rmc-timeline-now{
  display:flex;align-items:center;gap:10px;padding:8px 0;margin-left:8px;padding-left:18px;
  position:relative;border-left:2px solid var(--dash-pink);
}
.rmc-timeline-now::before{
  content:'';position:absolute;left:-7px;top:50%;transform:translateY(-50%);width:12px;height:12px;border-radius:50%;
  background:var(--dash-pink);box-shadow:0 0 0 4px rgba(255,111,145,.2);
  animation:rmcPulse 1.6s infinite;
}
.rmc-timeline-now span{font-size:11px;font-weight:800;color:var(--dash-pink);letter-spacing:.5px;text-transform:uppercase;}

.rmc-personel-list{display:flex;flex-direction:column;gap:14px;max-height:380px;overflow-y:auto;}
.rmc-personel-row{display:grid;grid-template-columns:90px 1fr 50px;gap:10px;align-items:center;}
.rmc-personel-row .ad{font-size:13px;color:var(--dash-text);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rmc-personel-row .bar{height:8px;background:#f3f4f8;border-radius:6px;overflow:hidden;}
.rmc-personel-row .bar-fill{
  height:100%;background:linear-gradient(90deg,var(--dash-accent),var(--dash-violet));border-radius:6px;
  width:0%;transition:width 1.2s cubic-bezier(.16,1,.3,1);
}
.rmc-personel-row .pct{font-size:12px;color:var(--dash-text-soft);font-weight:700;text-align:right;}

/* ===== Skeleton Shimmer ===== */
.rmc-skel{
  background:linear-gradient(90deg,#f3f4f8 0%,#eaecf4 50%,#f3f4f8 100%);
  background-size:200% 100%;
  animation:rmcShimmer 1.4s infinite;
  border-radius:8px;
}
.rmc-skel-line{height:14px;margin:6px 0;}
.rmc-skel-circle{border-radius:50%;}

@keyframes rmcShimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
@keyframes rmcRise{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
@keyframes rmcPulse{0%,100%{box-shadow:0 0 0 4px rgba(255,111,145,.2);}50%{box-shadow:0 0 0 8px rgba(255,111,145,.05);}}

/* renkler */
.bg-soft-blue{background:#eaf4ff;color:#3aaaff;}
.bg-soft-green{background:#e7faf1;color:#27c281;}
.bg-soft-orange{background:#fff3e3;color:#ff9f43;}
.bg-soft-pink{background:#ffe9ef;color:#ff6f91;}
.bg-soft-violet{background:#f1e9ff;color:#9b5cff;}
.bg-soft-accent{background:#eef0ff;color:#5b6bf7;}
.bg-soft-gray{background:#f3f4f8;color:#5c6678;}

/* Eylem badge */
.rmc-yeni{position:absolute;top:6px;right:8px;background:var(--dash-pink);color:#fff;font-size:9px;font-weight:800;padding:2px 6px;border-radius:5px;letter-spacing:.4px;}

/* Üyelik bilgi şeridi */
.rmc-lic-bar{
  display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#5b6bf7,#9b5cff);color:#fff;
  padding:10px 16px;border-radius:12px;margin-bottom:16px;font-size:13px;font-weight:500;
}
.rmc-lic-bar i{font-size:16px;}
</style>

<div class="rmc-dash-wrap">

  {{-- Üyelik bilgi şeridi --}}
  @if(isset($kalan_uyelik_suresi) && is_numeric($kalan_uyelik_suresi) && $kalan_uyelik_suresi >= 0 && $kalan_uyelik_suresi <= 30)
  <div class="rmc-lic-bar">
    <i class="bi bi-info-circle-fill"></i>
    Üyelik sürenizin bitmesine <b>{{$kalan_uyelik_suresi}} gün</b> kaldı.
  </div>
  @endif

  {{-- HIZLI EYLEM ŞERIDI --}}
  <div class="rmc-quick-actions">
    <a class="rmc-qa" href="#" onclick="$('#randevu-ekle-modal').modal('show');return false;">
      <div class="rmc-qa-icon bg-soft-green"><i class="bi bi-calendar-plus-fill"></i></div>
      <div class="rmc-qa-text">Yeni Randevu Oluştur</div>
    </a>
    <a class="rmc-qa" href="/isletmeyonetim/adisyonlar{{$subeQuery}}">
      <div class="rmc-qa-icon bg-soft-blue"><i class="bi bi-receipt"></i></div>
      <div class="rmc-qa-text">Adisyon Oluştur</div>
    </a>
    <a class="rmc-qa" href="/isletmeyonetim/paketler{{$subeQuery}}">
      <div class="rmc-qa-icon bg-soft-violet"><i class="bi bi-gift-fill"></i></div>
      <div class="rmc-qa-text">Yeni Paket Oluştur</div>
    </a>
    <a class="rmc-qa" href="/isletmeyonetim/hizmetler{{$subeQuery}}">
      <div class="rmc-qa-icon bg-soft-accent"><i class="bi bi-stars"></i></div>
      <div class="rmc-qa-text">Yeni Hizmet Oluştur</div>
    </a>
    <a class="rmc-qa" href="/isletmeyonetim/islemraporlari{{$subeQuery}}">
      <div class="rmc-qa-icon bg-soft-orange"><i class="bi bi-bar-chart-line-fill"></i></div>
      <div class="rmc-qa-text">İşletme Raporları</div>
    </a>
    <a class="rmc-qa" href="/isletmeyonetim/musteriler{{$subeQuery}}">
      <div class="rmc-qa-icon bg-soft-pink"><i class="bi bi-people-fill"></i></div>
      <div class="rmc-qa-text">Müşteri Listesi</div>
    </a>
    <a class="rmc-qa" href="/isletmeyonetim/excelmusteritopluekle{{$subeQuery}}">
      <div class="rmc-qa-icon bg-soft-gray"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
      <div class="rmc-qa-text">Excel ile Toplu Müşteri</div>
    </a>
  </div>

  {{-- ÜST GRID: Kasa / Takvim / Randevu Ayrıntıları --}}
  <div class="rmc-grid-top">

    {{-- KASA İSTATISTIKLERI --}}
    <div class="rmc-card delay-1" id="rmc-kasa-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><i class="bi bi-wallet2"></i> Kasa İstatistikleri</div>
        <div class="rmc-period" data-target="kasa">
          <button data-period="daily" class="is-active">Günlük</button>
          <button data-period="7d">Son 7 gün</button>
          <button data-period="30d">Son 30 gün</button>
        </div>
      </div>
      <div class="rmc-kasa-body">
        <div class="rmc-kasa-chart-box"><canvas id="rmc-kasa-chart"></canvas></div>
        <div class="rmc-kasa-legend">
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#27c281"></span> Nakit <span id="rmc-kasa-nakit-pct" style="margin-left:auto;color:#9aa3b2;font-weight:600;">— %</span></div>
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#5b6bf7"></span> Kart <span id="rmc-kasa-kart-pct" style="margin-left:auto;color:#9aa3b2;font-weight:600;">— %</span></div>
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#ff9f43"></span> Havale/EFT <span id="rmc-kasa-havale-pct" style="margin-left:auto;color:#9aa3b2;font-weight:600;">— %</span></div>
          <div class="rmc-kasa-row"><span class="rmc-dot" style="background:#9aa3b2"></span> Diğer <span id="rmc-kasa-diger-pct" style="margin-left:auto;color:#9aa3b2;font-weight:600;">— %</span></div>
        </div>
      </div>
      <div class="rmc-kasa-total">
        <div class="lbl">Toplam Gelir</div>
        <div class="val"><span id="rmc-kasa-total">0</span> ₺</div>
        <svg class="rmc-kasa-spark" id="rmc-kasa-spark" viewBox="0 0 200 36" preserveAspectRatio="none"></svg>
      </div>
    </div>

    {{-- RANDEVU TAKVIMI --}}
    <div class="rmc-card delay-2" id="rmc-cal-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><i class="bi bi-calendar3"></i> Randevu Takvimi</div>
      </div>
      <div class="rmc-cal-head">
        <button class="rmc-cal-nav" data-cal-nav="-1"><i class="bi bi-chevron-left"></i></button>
        <div class="rmc-cal-month" id="rmc-cal-month">—</div>
        <button class="rmc-cal-nav" data-cal-nav="1"><i class="bi bi-chevron-right"></i></button>
      </div>
      <div class="rmc-cal-grid" id="rmc-cal-grid">
        <div class="day-name">Pts</div><div class="day-name">Sal</div><div class="day-name">Çar</div><div class="day-name">Per</div><div class="day-name">Cum</div><div class="day-name">Cts</div><div class="day-name">Paz</div>
        @for($i=0;$i<35;$i++)<div class="rmc-skel" style="height:32px;margin:2px 0;"></div>@endfor
      </div>
    </div>

    {{-- RANDEVU AYRINTILARI --}}
    <div class="rmc-card delay-3" id="rmc-randevu-card">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><i class="bi bi-clock-history"></i> Randevu Ayrıntıları</div>
        <div class="rmc-period" data-target="randevu">
          <button data-period="daily" class="is-active">Günlük</button>
          <button data-period="7d">7g</button>
          <button data-period="30d">30g</button>
        </div>
      </div>
      <div class="rmc-randevu-body">
        <div class="rmc-randevu-list">
          <div class="rmc-randevu-item">
            <div class="ico bg-soft-violet"><i class="bi bi-alarm"></i></div>
            <div class="txt"><div class="num" id="rmc-rnd-olusturulan">0</div><div class="lbl">Oluşturulanlar</div></div>
          </div>
          <div class="rmc-randevu-item">
            <div class="ico bg-soft-green"><i class="bi bi-check-circle-fill"></i></div>
            <div class="txt"><div class="num" id="rmc-rnd-sonuclanan">0</div><div class="lbl">Sonuçlananlar</div></div>
          </div>
          <div class="rmc-randevu-item">
            <div class="ico bg-soft-pink"><i class="bi bi-x-circle-fill"></i></div>
            <div class="txt"><div class="num" id="rmc-rnd-sonuclanmayan">0</div><div class="lbl">Sonuçlanmayanlar</div></div>
          </div>
        </div>
        <div>
          <div class="rmc-doluluk-ring" id="rmc-doluluk-ring">
            <div class="rmc-doluluk-ring-inner">
              <div class="rmc-doluluk-pct" id="rmc-doluluk-pct">0%</div>
              <div class="rmc-doluluk-lbl">Anlık Doluluk<br>Oranı</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ALT PANEL: SEKMELER --}}
  <div class="rmc-card rmc-tabs-card delay-4">
    <div class="rmc-tabs-nav">
      <button class="rmc-tab is-active" data-tab="online-talep"><i class="bi bi-globe2"></i> Online Randevu Talepleri</button>
      <button class="rmc-tab" data-tab="bugunku-randevu"><i class="bi bi-calendar-day"></i> Bugünkü Randevular <span class="badge-new">YENİ</span></button>
      <button class="rmc-tab" data-tab="acik-adisyon"><i class="bi bi-receipt-cutoff"></i> Açık Adisyonlar</button>
      <button class="rmc-tab" data-tab="alacak"><i class="bi bi-cash-coin"></i> Alacaklılar</button>
      <button class="rmc-tab" data-tab="dogum-gunu"><i class="bi bi-gift"></i> Yaklaşan Doğum Günleri</button>
    </div>
    <div class="rmc-tab-content" id="rmc-tab-content">
      <div style="padding:20px 12px;">
        @for($i=0;$i<4;$i++)
          <div class="rmc-skel rmc-skel-line" style="width: {{ 100 - ($i*8) }}%;"></div>
        @endfor
      </div>
    </div>
  </div>

  {{-- ALT GRID: Timeline + Personel Anlık Durum --}}
  <div class="rmc-grid-bottom">
    <div class="rmc-card delay-5">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><i class="bi bi-activity"></i> Bugünkü Akış</div>
        <span style="font-size:11px;color:var(--dash-text-muted);font-weight:600;">Randevu + Tahsilat</span>
      </div>
      <div class="rmc-timeline" id="rmc-timeline">
        @for($i=0;$i<5;$i++)
          <div class="rmc-skel rmc-skel-line" style="width:{{90-$i*8}}%;margin:14px 0;"></div>
        @endfor
      </div>
    </div>
    <div class="rmc-card delay-6">
      <div class="rmc-card-head">
        <div class="rmc-card-title"><i class="bi bi-person-workspace"></i> Personel Anlık Durum</div>
        <span style="font-size:11px;color:var(--dash-text-muted);font-weight:600;">Bugün</span>
      </div>
      <div class="rmc-personel-list" id="rmc-personel-list">
        @for($i=0;$i<4;$i++)
          <div class="rmc-personel-row">
            <div class="rmc-skel" style="height:14px;"></div>
            <div class="rmc-skel" style="height:8px;"></div>
            <div class="rmc-skel" style="height:14px;"></div>
          </div>
        @endfor
      </div>
    </div>
  </div>

</div>

<script>
function rmcDashInit(){
  var apiBase = '/isletmeyonetim/api/dashboard';
  var subeParam = @json($apiSubeParam);

  function api(path, extra){
    var url = apiBase + path + subeParam;
    if(extra){
      url += (subeParam ? '&' : '?') + extra;
    }
    return fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); });
  }

  function trFmt(n){
    n = Number(n)||0;
    return n.toLocaleString('tr-TR',{minimumFractionDigits:0,maximumFractionDigits:0});
  }
  function pct(v,t){ return t>0 ? Math.round((v/t)*1000)/10 : 0; }
  function animateCount(el, to, opts){
    if(!el) return;
    opts = opts||{};
    try{
      var c = new countUp.CountUp(el, to, Object.assign({duration:1.2,separator:'.',decimal:',',useEasing:true},opts));
      if(!c.error){ c.start(); return; }
    }catch(e){}
    el.textContent = trFmt(to);
  }

  // ===== KASA =====
  var kasaChart = null;
  function renderKasa(period){
    api('/kasa', 'period='+period).then(function(d){
      var ctx = document.getElementById('rmc-kasa-chart').getContext('2d');
      if(kasaChart) kasaChart.destroy();
      var values = [d.nakit, d.kart, d.havale, d.diger];
      var colors = ['#27c281','#5b6bf7','#ff9f43','#9aa3b2'];
      if(d.toplam <= 0){ values = [0,0,0,1]; colors = ['#27c281','#5b6bf7','#ff9f43','#eef0f4']; }
      kasaChart = new Chart(ctx, {
        type:'doughnut',
        data:{labels:['Nakit','Kart','Havale','Diğer'],datasets:[{data:values,backgroundColor:colors,borderWidth:0,hoverOffset:6}]},
        options:{cutout:'68%',plugins:{legend:{display:false},tooltip:{enabled:d.toplam>0}},animation:{duration:900,easing:'easeOutQuart'}}
      });
      var t = d.toplam || 0;
      document.getElementById('rmc-kasa-nakit-pct').textContent = pct(d.nakit,t)+' %';
      document.getElementById('rmc-kasa-kart-pct').textContent = pct(d.kart,t)+' %';
      document.getElementById('rmc-kasa-havale-pct').textContent = pct(d.havale,t)+' %';
      document.getElementById('rmc-kasa-diger-pct').textContent = pct(d.diger,t)+' %';
      animateCount(document.getElementById('rmc-kasa-total'), t);

      // sparkline (son 7 gün)
      var svg = document.getElementById('rmc-kasa-spark');
      var pts = (d.sparkline||[]).map(function(p){return Number(p.tutar)||0;});
      if(pts.length>0){
        var max = Math.max.apply(null,pts.concat([1]));
        var w = 200, h = 36, step = pts.length>1 ? w/(pts.length-1) : w;
        var path = pts.map(function(v,i){
          var x = i*step, y = h - (v/max)*(h-4) - 2;
          return (i===0?'M':'L')+x.toFixed(1)+','+y.toFixed(1);
        }).join(' ');
        var area = path + ' L'+w+','+h+' L0,'+h+' Z';
        svg.innerHTML =
          '<defs><linearGradient id="rmcSparkG" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#5b6bf7" stop-opacity=".35"/><stop offset="100%" stop-color="#5b6bf7" stop-opacity="0"/></linearGradient></defs>'
        + '<path d="'+area+'" fill="url(#rmcSparkG)"/>'
        + '<path d="'+path+'" fill="none" stroke="#5b6bf7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>';
      }
    }).catch(function(e){console.warn('kasa err',e);});
  }

  // ===== RANDEVU ÖZET =====
  function renderRandevu(period){
    api('/randevu-ozet', 'period='+period).then(function(d){
      animateCount(document.getElementById('rmc-rnd-olusturulan'), d.olusturulan);
      animateCount(document.getElementById('rmc-rnd-sonuclanan'), d.sonuclanan);
      animateCount(document.getElementById('rmc-rnd-sonuclanmayan'), d.sonuclanmayan);
      var ring = document.getElementById('rmc-doluluk-ring');
      var pctVal = Number(d.doluluk)||0;
      var deg = Math.round(pctVal*3.6);
      var color = pctVal>=70 ? '#27c281' : pctVal>=40 ? '#ff9f43' : '#ff6f91';
      ring.style.background = 'conic-gradient('+color+' '+deg+'deg, #f3f4f8 '+deg+'deg)';
      var pctEl = document.getElementById('rmc-doluluk-pct');
      var startTs = performance.now(); var dur = 1100;
      function step(t){
        var k = Math.min(1,(t-startTs)/dur);
        var eased = 1 - Math.pow(1-k, 4);
        pctEl.textContent = Math.round(pctVal*eased)+'%';
        if(k<1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }).catch(function(e){console.warn('randevu err',e);});
  }

  // ===== TAKVIM =====
  var calState = {year:0,month:0};
  var aylar = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
  function renderCal(y,m){
    api('/takvim', 'year='+y+'&month='+m).then(function(d){
      calState.year = y; calState.month = m;
      document.getElementById('rmc-cal-month').textContent = aylar[m-1]+' '+y;
      var firstDay = new Date(y, m-1, 1).getDay(); // 0=Paz
      var startOffset = firstDay === 0 ? 6 : firstDay - 1; // Pts başlat
      var daysInMonth = new Date(y, m, 0).getDate();
      var grid = document.getElementById('rmc-cal-grid');
      var html = '<div class="day-name">Pts</div><div class="day-name">Sal</div><div class="day-name">Çar</div><div class="day-name">Per</div><div class="day-name">Cum</div><div class="day-name">Cts</div><div class="day-name">Paz</div>';
      for(var i=0;i<startOffset;i++) html += '<div class="day empty"></div>';
      var bugun = new Date(); var by=bugun.getFullYear(), bm=bugun.getMonth()+1, bd=bugun.getDate();
      var max = 1;
      for(var k in d.gunler){ if(d.gunler[k]>max) max = d.gunler[k]; }
      for(var j=1;j<=daysInMonth;j++){
        var adet = d.gunler[j]||0;
        var isToday = (y===by && m===bm && j===bd);
        var ratio = adet/max;
        var dot = '';
        if(adet>0){
          var bg = ratio>0.66 ? '#27c281' : ratio>0.33 ? '#5b6bf7' : '#9b5cff';
          dot = '<span class="heat-dot" style="background:'+bg+'" title="'+adet+' randevu"></span>';
        }
        html += '<div class="day '+(isToday?'today':'')+'" title="'+(adet>0?adet+' randevu':'')+'">'+j+dot+'</div>';
      }
      grid.innerHTML = html;
    }).catch(function(e){console.warn('cal err',e);});
  }

  // ===== SEKME =====
  function renderTab(tab){
    var box = document.getElementById('rmc-tab-content');
    box.innerHTML = '<div style="padding:20px 12px;">'
      + '<div class="rmc-skel rmc-skel-line" style="width:95%"></div>'
      + '<div class="rmc-skel rmc-skel-line" style="width:88%"></div>'
      + '<div class="rmc-skel rmc-skel-line" style="width:80%"></div></div>';
    api('/sekme', 'tab='+tab).then(function(d){
      var rows = d.liste||[];
      if(rows.length === 0){
        var msg = tab==='online-talep' ? 'Bekleyen randevu talebiniz bulunmamakta.'
          : tab==='bugunku-randevu' ? 'Bugün için randevu bulunmuyor.'
          : tab==='acik-adisyon' ? 'Açık adisyon bulunmuyor.'
          : tab==='alacak' ? 'Bekleyen alacak yok.'
          : 'Yaklaşan doğum günü bulunmuyor.';
        box.innerHTML = '<div class="rmc-tab-empty"><i class="bi bi-emoji-smile" style="font-size:32px;color:#dde1ec;display:block;margin-bottom:8px;"></i>'+msg+'</div>';
        return;
      }
      var headers, rowFn;
      switch(tab){
        case 'online-talep':
          headers = ['Tarih','Saat','Müşteri','Telefon','Hizmet'];
          rowFn = function(r){ return '<td>'+(r.tarih||'')+'</td><td>'+(r.saat||'').substr(0,5)+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td>'+(r.hizmet||'-')+'</td>'; };
          break;
        case 'bugunku-randevu':
          headers = ['Saat','Müşteri','Telefon','Hizmet','Personel'];
          rowFn = function(r){ return '<td>'+(r.saat||'').substr(0,5)+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td>'+(r.hizmet||'-')+'</td><td>'+(r.personel||'-')+'</td>'; };
          break;
        case 'acik-adisyon':
          headers = ['Adisyon No','Tarih','Müşteri','Telefon','Kalan'];
          rowFn = function(r){
            var t = r.created_at ? new Date(r.created_at).toLocaleDateString('tr-TR') : '-';
            var kalan = r.kalan !== undefined ? r.kalan : (Number(r.toplam_tutar||0) - Number(r.odenen_tutar||0));
            return '<td>'+(r.adisyon_no||r.id||'-')+'</td><td>'+t+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td style="color:#ff6f91;font-weight:600">'+trFmt(kalan)+' ₺</td>';
          };
          break;
        case 'alacak':
          headers = ['Planlanan Ödeme','Müşteri','Telefon','Tutar'];
          rowFn = function(r){
            var t = r.planlanan_odeme_tarihi ? new Date(r.planlanan_odeme_tarihi).toLocaleDateString('tr-TR') : '-';
            return '<td>'+t+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td><td>'+trFmt(r.tutar||0)+' ₺</td>';
          };
          break;
        case 'dogum-gunu':
          headers = ['Doğum Tarihi','Müşteri','Telefon'];
          rowFn = function(r){
            var t = r.dogum_tarihi ? new Date(r.dogum_tarihi).toLocaleDateString('tr-TR') : '-';
            return '<td>'+t+'</td><td>'+(r.musteri||'-')+'</td><td>'+(r.telefon||'-')+'</td>';
          };
          break;
      }
      var html = '<table class="rmc-tab-table"><thead><tr>';
      headers.forEach(function(h){ html += '<th>'+h+'</th>'; });
      html += '</tr></thead><tbody>';
      rows.forEach(function(r){ html += '<tr>'+rowFn(r)+'</tr>'; });
      html += '</tbody></table>';
      box.innerHTML = html;
    }).catch(function(e){ box.innerHTML = '<div class="rmc-tab-empty">Veri yüklenemedi.</div>'; console.warn(e); });
  }

  // ===== TIMELINE =====
  function renderTimeline(){
    api('/timeline').then(function(d){
      var box = document.getElementById('rmc-timeline');
      var rows = d.liste||[];
      if(rows.length === 0){ box.innerHTML = '<div class="rmc-tab-empty">Bugün için kayıt yok.</div>'; return; }
      var simdi = d.simdi || '00:00';
      var html = '';
      var nowInserted = false;
      rows.forEach(function(r){
        if(!nowInserted && r.saat > simdi){
          html += '<div class="rmc-timeline-now"><span>Şimdi · '+simdi+'</span></div>';
          nowInserted = true;
        }
        html += '<div class="rmc-timeline-item '+(r.tip==='tahsilat'?'tahsilat':'')+'">'
              + '<div class="t-saat">'+r.saat+'</div>'
              + '<div class="t-baslik">'+(r.baslik||'-')+'</div></div>';
      });
      if(!nowInserted){ html += '<div class="rmc-timeline-now"><span>Şimdi · '+simdi+'</span></div>'; }
      box.innerHTML = html;
    }).catch(function(e){console.warn('timeline err',e);});
  }

  // ===== PERSONEL =====
  function renderPersonel(){
    api('/personel-durum').then(function(d){
      var box = document.getElementById('rmc-personel-list');
      var rows = d.personeller||[];
      if(rows.length === 0){ box.innerHTML = '<div class="rmc-tab-empty">Aktif personel yok.</div>'; return; }
      var html = '';
      rows.forEach(function(p){
        html += '<div class="rmc-personel-row">'
              + '<div class="ad" title="'+(p.ad||'')+'">'+(p.ad||'-')+'</div>'
              + '<div class="bar"><div class="bar-fill" data-pct="'+p.doluluk+'"></div></div>'
              + '<div class="pct">'+p.doluluk+'%</div></div>';
      });
      box.innerHTML = html;
      setTimeout(function(){
        box.querySelectorAll('.bar-fill').forEach(function(el){ el.style.width = el.getAttribute('data-pct')+'%'; });
      }, 50);
    }).catch(function(e){console.warn('personel err',e);});
  }

  // ===== Event bağlantıları =====
  document.querySelectorAll('.rmc-period').forEach(function(grp){
    var target = grp.getAttribute('data-target');
    grp.querySelectorAll('button').forEach(function(btn){
      btn.addEventListener('click', function(){
        grp.querySelectorAll('button').forEach(function(b){ b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        var p = btn.getAttribute('data-period');
        if(target === 'kasa') renderKasa(p);
        else if(target === 'randevu') renderRandevu(p);
      });
    });
  });

  document.querySelectorAll('.rmc-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.rmc-tab').forEach(function(b){ b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      renderTab(btn.getAttribute('data-tab'));
    });
  });

  document.querySelectorAll('[data-cal-nav]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var dir = parseInt(btn.getAttribute('data-cal-nav'),10);
      var y = calState.year, m = calState.month + dir;
      if(m < 1){ m = 12; y--; }
      if(m > 12){ m = 1; y++; }
      renderCal(y, m);
    });
  });

  // ===== İlk yükleme — paralel =====
  var today = new Date();
  renderKasa('daily');
  renderRandevu('daily');
  renderCal(today.getFullYear(), today.getMonth()+1);
  renderTab('online-talep');
  renderTimeline();
  renderPersonel();
}

(function rmcDashBoot(){
  function ready(){
    if(typeof Chart === 'undefined'){
      // chart.js henuz yuklenmedi, kucuk bir gecikme
      return setTimeout(ready, 50);
    }
    rmcDashInit();
  }
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }
})();
</script>

@endsection
