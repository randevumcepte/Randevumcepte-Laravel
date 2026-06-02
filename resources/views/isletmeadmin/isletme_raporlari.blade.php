@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')

@php
    $subeQuery = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
    $apiSubeParam = isset($_GET['sube']) ? '?sube='.$isletme->id : '';
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/countUp.js/2.8.0/countUp.umd.min.js" defer></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
  --rep-bg:#f6f7fb;
  --rep-card-bg:#ffffff;
  --rep-border:#ecedf4;
  --rep-text:#181b2c;
  --rep-text-soft:#5c6486;
  --rep-text-muted:#9097ad;
  --rep-accent:#6e4bff;
  --rep-green:#1fbf6f;
  --rep-orange:#ff8a3d;
  --rep-pink:#ff5c8a;
  --rep-blue:#1e9fff;
  --rep-violet:#a16bff;
  --rep-radius:18px;
  --rep-shadow:0 4px 24px rgba(28,32,72,.06);
}

.rep-wrap{padding:6px 4px 40px 4px;}

/* ===== Üst Tab Şeridi ===== */
.rep-tabs{
  display:flex;gap:6px;border-bottom:1px solid var(--rep-border);
  margin-bottom:20px;overflow-x:auto;
}
.rep-tab{
  display:flex;align-items:center;gap:8px;padding:12px 18px;cursor:pointer;
  background:transparent;border:0;border-bottom:2px solid transparent;margin-bottom:-1px;
  font-size:14px;font-weight:600;color:var(--rep-text-soft);
  text-decoration:none !important;transition:color .15s,border-color .15s;
  white-space:nowrap;
}
.rep-tab:hover{color:var(--rep-text);}
.rep-tab.is-active{color:var(--rep-accent);border-bottom-color:var(--rep-accent);}
.rep-tab .star{color:#dde1ec;font-size:14px;transition:color .15s;}
.rep-tab.is-active .star{color:#1fbf6f;}

/* ===== Period Filter Bar ===== */
.rep-filter{
  display:grid;grid-template-columns:repeat(3,1fr) 1fr auto;gap:14px;
  margin-bottom:22px;align-items:center;
}
@media (max-width:1199px){.rep-filter{grid-template-columns:repeat(3,1fr);}}
@media (max-width:767px){.rep-filter{grid-template-columns:1fr 1fr;}}

.rep-pchip{
  background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:14px;
  padding:14px 16px;text-align:center;cursor:pointer;font-weight:600;font-size:13.5px;
  color:var(--rep-text-soft);transition:all .2s;
}
.rep-pchip:hover{border-color:var(--rep-accent);color:var(--rep-text);}
.rep-pchip.var-1{color:#1e9fff;background:#e3f2ff;border-color:#cfe7ff;}
.rep-pchip.var-2{color:#1fbf6f;background:#defaeb;border-color:#bff0d6;}
.rep-pchip.var-3{color:#ff5c8a;background:#ffe3eb;border-color:#ffc9d7;}
.rep-pchip.is-active{box-shadow:0 0 0 2px var(--rep-accent) inset;color:var(--rep-accent);background:#fff;}
.rep-pchip-date{
  background:#fff3e3;border:1px solid #ffd9ad;border-radius:14px;
  padding:12px 16px;display:flex;align-items:center;gap:10px;color:#ff8a3d;font-weight:600;font-size:13px;
}
.rep-pchip-date input{
  border:0;background:transparent;color:#ff8a3d;font-weight:600;font-size:13px;
  outline:none;width:106px;
}
.rep-pchip-date input:focus{outline:none;}
.rep-pchip-date .sep{opacity:.55;}

.rep-toplam-gelir{
  background:#defaeb;border:1px solid #bff0d6;border-radius:14px;
  padding:14px 18px;display:flex;align-items:center;gap:10px;font-weight:700;color:#1fbf6f;
  min-width:200px;justify-content:flex-end;
}
.rep-toplam-gelir .lbl{font-size:13px;color:#1a8f53;}
.rep-toplam-gelir .val{font-size:18px;font-weight:800;color:#0c6b3a;}

/* ===== 4 İstatistik Kartı ===== */
.rep-stats{
  display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;
}
@media (max-width:1199px){.rep-stats{grid-template-columns:repeat(2,1fr);}}
@media (max-width:599px){.rep-stats{grid-template-columns:1fr;}}

.rep-stat{
  background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:14px;
  padding:18px 20px;display:flex;align-items:center;gap:14px;
  box-shadow:var(--rep-shadow);position:relative;overflow:hidden;
  opacity:0;animation:repRise .55s ease forwards;
}
.rep-stat.delay-1{animation-delay:.04s} .rep-stat.delay-2{animation-delay:.10s}
.rep-stat.delay-3{animation-delay:.16s} .rep-stat.delay-4{animation-delay:.22s}
.rep-stat .ico{
  width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;
  font-size:20px;flex-shrink:0;
}
.rep-stat .num{font-size:26px;font-weight:800;color:var(--rep-text);line-height:1;}
.rep-stat .lbl{font-size:12.5px;color:var(--rep-text-soft);font-weight:600;margin-top:4px;}
.rep-stat.bg-1{background:#eaf4ff;border-color:#cfe7ff;}
.rep-stat.bg-1 .ico{background:#cfe7ff;color:#1e9fff;}
.rep-stat.bg-2{background:#defaeb;border-color:#bff0d6;}
.rep-stat.bg-2 .ico{background:#bff0d6;color:#1fbf6f;}
.rep-stat.bg-3{background:#ffe3eb;border-color:#ffc9d7;}
.rep-stat.bg-3 .ico{background:#ffc9d7;color:#ff5c8a;}
.rep-stat.bg-4{background:#fff3e3;border-color:#ffd9ad;}
.rep-stat.bg-4 .ico{background:#ffd9ad;color:#ff8a3d;}

/* ===== Gelir Raporları Card ===== */
.rep-card{
  background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:var(--rep-radius);
  padding:20px 24px;box-shadow:var(--rep-shadow);
  opacity:0;animation:repRise .55s ease forwards;animation-delay:.28s;
  margin-bottom:18px;
}
.rep-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:12px;}
.rep-card-title{font-size:15px;font-weight:700;color:var(--rep-text);display:flex;align-items:center;gap:10px;}
.rep-card-title .ico{
  width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#1e9fff,#6e4bff);color:#fff;font-size:15px;
}
.rep-collapse{background:none;border:0;color:var(--rep-text-soft);font-size:16px;cursor:pointer;}

.rep-gelir-body{display:grid;grid-template-columns:1.4fr 1fr;gap:32px;align-items:center;min-height:240px;}
@media (max-width:991px){.rep-gelir-body{grid-template-columns:1fr;}}

.rep-gelir-chart-box{position:relative;height:240px;display:flex;align-items:center;justify-content:center;}
.rep-gelir-chart-box canvas{max-height:240px;}
.rep-gelir-empty{text-align:center;color:var(--rep-text-muted);font-size:13px;}
.rep-gelir-empty i{font-size:36px;color:#dde1ec;display:block;margin-bottom:8px;}

.rep-gelir-list{display:flex;flex-direction:column;gap:10px;}
.rep-gelir-row{
  border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;align-items:flex-end;
  background:#fafbfd;border:1px solid var(--rep-border);
}
.rep-gelir-row .v{font-size:16px;font-weight:800;}
.rep-gelir-row .l{font-size:11.5px;font-weight:600;opacity:.85;margin-top:2px;}
.rep-gelir-row.nakit{background:#e3f2ff;border-color:#cfe7ff;color:#1e9fff;}
.rep-gelir-row.kart{background:#defaeb;border-color:#bff0d6;color:#1fbf6f;}
.rep-gelir-row.havale{background:#ffe3eb;border-color:#ffc9d7;color:#ff5c8a;}
.rep-gelir-row.odenen{background:#fff3e3;border-color:#ffd9ad;color:#ff8a3d;}

.rep-detay-btn{
  display:flex;align-items:center;gap:6px;margin:18px auto 0 auto;
  background:linear-gradient(135deg,#efe9ff,#dccfff);border:0;border-radius:24px;
  padding:10px 22px;color:#6e4bff;font-weight:700;font-size:13px;cursor:pointer;
  transition:transform .18s ease;text-decoration:none;
}
.rep-detay-btn:hover{transform:translateY(-2px);color:#6e4bff;text-decoration:none;}
.rep-detay-wrap{text-align:center;}

/* Skeleton & animasyon */
.rep-skel{background:linear-gradient(90deg,#f3f4f8 0%,#eaecf4 50%,#f3f4f8 100%);
  background-size:200% 100%;animation:repShimmer 1.4s infinite;border-radius:8px;}
.rep-skel-num{height:24px;width:60%;}
@keyframes repShimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
@keyframes repRise{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}

/* "Yakında" placeholder */
.rep-soon{
  background:var(--rep-card-bg);border:1px dashed var(--rep-border);border-radius:var(--rep-radius);
  padding:60px 20px;text-align:center;
}
.rep-soon i{font-size:48px;color:#dde1ec;display:block;margin-bottom:12px;}
.rep-soon h4{font-size:16px;font-weight:700;color:var(--rep-text);margin:0 0 6px 0;}
.rep-soon p{font-size:13px;color:var(--rep-text-muted);margin:0;}
</style>

<div class="rep-wrap">

  {{-- Üst tab şeridi --}}
  <div class="rep-tabs">
    <a class="rep-tab is-active" href="/isletmeyonetim/isletmeraporlari{{$subeQuery}}">İşletme Raporları <i class="bi bi-star-fill star"></i></a>
    <a class="rep-tab" href="#" data-soon="Hizmet">Hizmet Raporları <i class="bi bi-star star"></i></a>
    <a class="rep-tab" href="#" data-soon="Ürün">Ürün Raporları <i class="bi bi-star star"></i></a>
    <a class="rep-tab" href="#" data-soon="Personel">Personel Raporları <i class="bi bi-star star"></i></a>
    <a class="rep-tab" href="#" data-soon="Müşteri">Müşteri Raporları <i class="bi bi-star star"></i></a>
    <a class="rep-tab" href="#" data-soon="Randevu">Randevu Raporları <i class="bi bi-star star"></i></a>
  </div>

  {{-- Period filter bar --}}
  <div class="rep-filter">
    <button class="rep-pchip var-1 is-active" data-period="bugun">Bugün</button>
    <button class="rep-pchip var-2" data-period="hafta">Bu Hafta</button>
    <button class="rep-pchip var-3" data-period="ay">Bu Ay</button>
    <div class="rep-pchip-date">
      <i class="bi bi-calendar-event"></i>
      <input type="date" id="rep-bas" value="{{ date('Y-m-d') }}">
      <span class="sep">—</span>
      <input type="date" id="rep-bit" value="{{ date('Y-m-d') }}">
    </div>
    <div class="rep-toplam-gelir">
      <i class="bi bi-currency-lira" style="font-size:18px"></i>
      <div style="text-align:right;">
        <div class="lbl">Toplam Gelir</div>
        <div class="val"><span id="rep-toplam-gelir">0,00</span> ₺</div>
      </div>
    </div>
  </div>

  {{-- 4 stat kartı --}}
  <div class="rep-stats">
    <div class="rep-stat bg-1 delay-1">
      <div class="ico"><i class="bi bi-calendar-check"></i></div>
      <div>
        <div class="num" id="rep-stat-randevu">0</div>
        <div class="lbl">Toplam Randevu Sayısı</div>
      </div>
    </div>
    <div class="rep-stat bg-2 delay-2">
      <div class="ico"><i class="bi bi-receipt"></i></div>
      <div>
        <div class="num" id="rep-stat-adisyon">0</div>
        <div class="lbl">Toplam Adisyon Sayısı</div>
      </div>
    </div>
    <div class="rep-stat bg-3 delay-3">
      <div class="ico"><i class="bi bi-cart-fill"></i></div>
      <div>
        <div class="num" id="rep-stat-urun">0</div>
        <div class="lbl">Satılan Ürün</div>
      </div>
    </div>
    <div class="rep-stat bg-4 delay-4">
      <div class="ico"><i class="bi bi-scissors"></i></div>
      <div>
        <div class="num" id="rep-stat-hizmet">0</div>
        <div class="lbl">Uygulanan Hizmet</div>
      </div>
    </div>
  </div>

  {{-- Gelir Raporları kartı --}}
  <div class="rep-card" id="rep-gelir-card">
    <div class="rep-card-head">
      <div class="rep-card-title"><span class="ico"><i class="bi bi-graph-up-arrow"></i></span> Gelir Raporları</div>
      <button class="rep-collapse" title="Daralt/Genişlet" onclick="this.closest('.rep-card').classList.toggle('is-collapsed')"><i class="bi bi-chevron-up"></i></button>
    </div>
    <div class="rep-gelir-body" id="rep-gelir-body">
      <div class="rep-gelir-chart-box" id="rep-gelir-chart-box">
        <canvas id="rep-gelir-chart"></canvas>
      </div>
      <div class="rep-gelir-list">
        <div class="rep-gelir-row nakit">
          <div class="v"><span id="rep-gelir-nakit">0,00</span> ₺</div>
          <div class="l">Nakit</div>
        </div>
        <div class="rep-gelir-row kart">
          <div class="v"><span id="rep-gelir-kart">0,00</span> ₺</div>
          <div class="l">Kredi / Banka Kartı</div>
        </div>
        <div class="rep-gelir-row havale">
          <div class="v"><span id="rep-gelir-havale">0,00</span> ₺</div>
          <div class="l">Havale / EFT</div>
        </div>
        <div class="rep-gelir-row odenen">
          <div class="v"><span id="rep-gelir-odenen">0,00</span> ₺</div>
          <div class="l">Ödenen Gelir</div>
        </div>
      </div>
    </div>
    <div class="rep-detay-wrap">
      <a class="rep-detay-btn" href="/isletmeyonetim/raporlar{{$subeQuery}}">Detaya Git <i class="bi bi-chevron-right"></i></a>
    </div>
  </div>

</div>

<script>
function repInit(){
  var apiBase = '/isletmeyonetim/api/isletmeraporlari';
  var subeParam = @json($apiSubeParam);

  function api(path, extra){
    var url = apiBase + path + subeParam;
    if(extra){
      url += (subeParam ? '&' : '?') + extra;
    }
    return fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); });
  }
  function trMoney(n){
    n = Number(n)||0;
    return n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2});
  }
  function animCount(el, to, decimals){
    if(!el) return;
    try{
      var c = new countUp.CountUp(el, to, {
        duration:1.0, separator:'.', decimal:',',
        decimalPlaces: decimals || 0
      });
      if(!c.error){ c.start(); return; }
    }catch(e){}
    el.textContent = (decimals ? Number(to).toFixed(decimals).replace('.', ',') : trMoney(to));
  }

  var gelirChart = null;
  function renderChart(d){
    var ctx = document.getElementById('rep-gelir-chart').getContext('2d');
    if(gelirChart) gelirChart.destroy();
    var box = document.getElementById('rep-gelir-chart-box');
    if((d.toplam_gelir||0) <= 0){
      box.innerHTML = '<div class="rep-gelir-empty"><i class="bi bi-pie-chart"></i>Değerler sıfıra eşit olduğu için grafik gösterilmiyor.</div>';
      return;
    }
    // Ensure canvas exists (re-create if previously replaced by empty state)
    if(!document.getElementById('rep-gelir-chart')){
      box.innerHTML = '<canvas id="rep-gelir-chart"></canvas>';
      ctx = document.getElementById('rep-gelir-chart').getContext('2d');
    }
    var values = [d.nakit||0, d.kart||0, d.havale||0, d.diger||0];
    var colors = ['#1e9fff','#1fbf6f','#ff5c8a','#9097ad'];
    var labels = ['Nakit','Kredi/Banka Kartı','Havale/EFT','Diğer'];
    gelirChart = new Chart(ctx, {
      type:'doughnut',
      data:{labels:labels, datasets:[{data:values, backgroundColor:colors, borderWidth:0, hoverOffset:6}]},
      options:{
        responsive:true, maintainAspectRatio:false, cutout:'62%',
        plugins:{
          legend:{position:'bottom', labels:{font:{size:11, weight:'600'}, color:'#5c6486', padding:8, usePointStyle:true, pointStyle:'circle'}},
          tooltip:{enabled:true, callbacks:{
            label: function(ctx){
              var v = ctx.parsed || 0;
              return ctx.label + ': ' + v.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' ₺';
            }
          }}
        },
        animation:{duration:900, easing:'easeOutQuart'}
      }
    });
  }

  function loadOzet(){
    var bas = document.getElementById('rep-bas').value;
    var bit = document.getElementById('rep-bit').value;
    var activeChip = document.querySelector('.rep-pchip.is-active');
    var period = activeChip ? activeChip.getAttribute('data-period') : 'custom';
    var qs = 'period='+period;
    if(period === 'custom' || (bas && bit)){
      qs = 'period=custom&bas='+bas+'&bit='+bit;
    }
    api('/ozet', qs).then(function(d){
      animCount(document.getElementById('rep-stat-randevu'), d.toplam_randevu||0);
      animCount(document.getElementById('rep-stat-adisyon'), d.toplam_adisyon||0);
      animCount(document.getElementById('rep-stat-urun'), d.satilan_urun||0);
      animCount(document.getElementById('rep-stat-hizmet'), d.uygulanan_hizmet||0);
      animCount(document.getElementById('rep-toplam-gelir'), d.toplam_gelir||0, 2);
      animCount(document.getElementById('rep-gelir-nakit'), d.nakit||0, 2);
      animCount(document.getElementById('rep-gelir-kart'), d.kart||0, 2);
      animCount(document.getElementById('rep-gelir-havale'), d.havale||0, 2);
      animCount(document.getElementById('rep-gelir-odenen'), d.odenen||0, 2);
      // Custom period seçilmişse tarih input'ları güncelle
      if(d.tarih_bas) document.getElementById('rep-bas').value = d.tarih_bas;
      if(d.tarih_bit) document.getElementById('rep-bit').value = d.tarih_bit;
      renderChart(d);
    }).catch(function(e){ console.warn('rapor ozet err', e); });
  }

  // Event: chip click
  document.querySelectorAll('.rep-pchip').forEach(function(chip){
    chip.addEventListener('click', function(){
      document.querySelectorAll('.rep-pchip').forEach(function(c){ c.classList.remove('is-active'); });
      chip.classList.add('is-active');
      loadOzet();
    });
  });

  // Event: date input change → switch to custom
  ['rep-bas','rep-bit'].forEach(function(id){
    document.getElementById(id).addEventListener('change', function(){
      document.querySelectorAll('.rep-pchip').forEach(function(c){ c.classList.remove('is-active'); });
      loadOzet();
    });
  });

  // Yakında tab tıklama
  document.querySelectorAll('.rep-tab[data-soon]').forEach(function(t){
    t.addEventListener('click', function(e){
      e.preventDefault();
      var ad = t.getAttribute('data-soon');
      alert(ad + ' Raporları yakında!');
    });
  });

  // İlk yükleme
  loadOzet();
}

(function repBoot(){
  function ready(){
    if(typeof Chart === 'undefined'){ return setTimeout(ready, 50); }
    repInit();
  }
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', ready);
  } else { ready(); }
})();
</script>

@endsection
