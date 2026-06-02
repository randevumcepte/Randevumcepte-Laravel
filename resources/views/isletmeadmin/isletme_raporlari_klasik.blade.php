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
  --rep-bg:#f6f7fb; --rep-card-bg:#ffffff; --rep-border:#ecedf4;
  --rep-text:#181b2c; --rep-text-soft:#5c6486; --rep-text-muted:#9097ad;
  --rep-accent:#5C008E; --rep-green:#1fbf6f; --rep-orange:#ff8a3d;
  --rep-pink:#ff5c8a; --rep-blue:#9D5DC8; --rep-violet:#9D5DC8; --rep-cyan:#22c4cb; --rep-yellow:#f7c948;
  --rep-fuchsia:#d946ef;
  --rep-radius:18px; --rep-shadow:0 4px 24px rgba(28,32,72,.06);
}
.rep-wrap{padding:6px 4px 40px 4px;}
.rep-tabs{display:flex;gap:6px;border-bottom:1px solid var(--rep-border);margin-bottom:20px;overflow-x:auto;}
.rep-tab{display:flex;align-items:center;gap:8px;padding:12px 18px;cursor:pointer;background:transparent;border:0;
  border-bottom:2px solid transparent;margin-bottom:-1px;font-size:14px;font-weight:600;color:var(--rep-text-soft);
  text-decoration:none !important;transition:color .15s,border-color .15s;white-space:nowrap;}
.rep-tab:hover{color:var(--rep-text);}
.rep-tab.is-active{color:var(--rep-accent);border-bottom-color:var(--rep-accent);}
.rep-tab .star{color:#dde1ec;font-size:14px;transition:color .15s;}
.rep-tab.is-active .star{color:#d946ef;}

.rep-filter{display:grid;grid-template-columns:repeat(3,1fr) 1fr auto;gap:14px;margin-bottom:22px;align-items:center;}
@media (max-width:1199px){.rep-filter{grid-template-columns:repeat(3,1fr);}}
@media (max-width:767px){.rep-filter{grid-template-columns:1fr 1fr;}}
.rep-pchip{background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:14px;padding:14px 16px;
  text-align:center;cursor:pointer;font-weight:600;font-size:13.5px;color:var(--rep-text-soft);transition:all .2s;}
.rep-pchip:hover{border-color:var(--rep-accent);color:var(--rep-text);}
.rep-pchip.var-1{color:#5C008E;background:#f3eafa;border-color:#e0c8f0;}
.rep-pchip.var-2{color:#9D5DC8;background:#ede0f7;border-color:#d8bfee;}
.rep-pchip.var-3{color:#c83361;background:#ffe3eb;border-color:#ffc9d7;}
.rep-pchip.is-active{box-shadow:0 0 0 2px var(--rep-accent) inset;color:var(--rep-accent);background:#fff;}
.rep-pchip-date{background:#fbf6ff;border:1px solid #e0c8f0;border-radius:14px;padding:12px 16px;display:flex;align-items:center;gap:10px;color:#5C008E;font-weight:600;font-size:13px;}
.rep-pchip-date input{border:0;background:transparent;color:#5C008E;font-weight:600;font-size:13px;outline:none;width:106px;}
.rep-pchip-date .sep{opacity:.55;}
.rep-toplam-gelir{background:linear-gradient(135deg,#5C008E,#9D5DC8);border:1px solid #5C008E;border-radius:14px;padding:14px 18px;display:flex;align-items:center;gap:10px;font-weight:700;color:#fff;min-width:200px;justify-content:flex-end;box-shadow:0 8px 20px -10px rgba(92,0,142,.5);}
.rep-toplam-gelir .lbl{font-size:13px;color:#f0d8ff;}
.rep-toplam-gelir .val{font-size:18px;font-weight:800;color:#fff;}

/* Stat kartları */
.rep-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
@media (max-width:1199px){.rep-stats{grid-template-columns:repeat(2,1fr);}}
@media (max-width:599px){.rep-stats{grid-template-columns:1fr;}}
.rep-stat{background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:var(--rep-shadow);position:relative;overflow:hidden;opacity:0;animation:repRise .55s ease forwards;}
.rep-stat.delay-1{animation-delay:.04s} .rep-stat.delay-2{animation-delay:.10s}
.rep-stat.delay-3{animation-delay:.16s} .rep-stat.delay-4{animation-delay:.22s}
.rep-stat .ico{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.rep-stat .num{font-size:26px;font-weight:800;color:var(--rep-text);line-height:1;}
.rep-stat .lbl{font-size:12.5px;color:var(--rep-text-soft);font-weight:600;margin-top:4px;}
.rep-stat.bg-1{background:#f3eafa;border-color:#e0c8f0;}.rep-stat.bg-1 .ico{background:#e0c8f0;color:#5C008E;}
.rep-stat.bg-2{background:#defaeb;border-color:#bff0d6;}.rep-stat.bg-2 .ico{background:#bff0d6;color:#1fbf6f;}
.rep-stat.bg-3{background:#ffe3eb;border-color:#ffc9d7;}.rep-stat.bg-3 .ico{background:#ffc9d7;color:#c83361;}
.rep-stat.bg-4{background:#fdf2f8;border-color:#fbcfe8;}.rep-stat.bg-4 .ico{background:#fbcfe8;color:#d946ef;}
.rep-stat.bg-5{background:#ede0f7;border-color:#d8bfee;}.rep-stat.bg-5 .ico{background:#d8bfee;color:#9D5DC8;}
.rep-stat.bg-6{background:#fff3e3;border-color:#ffd9ad;}.rep-stat.bg-6 .ico{background:#ffd9ad;color:#c47214;}

/* Card */
.rep-card{background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:var(--rep-radius);
  padding:20px 24px;box-shadow:var(--rep-shadow);opacity:0;animation:repRise .55s ease forwards;animation-delay:.28s;margin-bottom:18px;position:relative;overflow:hidden;}
.rep-card::before{content:'';position:absolute;left:0;top:0;width:100%;height:4px;background:linear-gradient(90deg,var(--c-from,#5C008E),var(--c-to,#9D5DC8));}
.rep-card.tone-blue{--c-from:#5C008E;--c-to:#9D5DC8;}
.rep-card.tone-green{--c-from:#1fbf6f;--c-to:#46d68b;}
.rep-card.tone-pink{--c-from:#c83361;--c-to:#d946ef;}
.rep-card.tone-orange{--c-from:#c47214;--c-to:#d946ef;}
.rep-card.tone-violet{--c-from:#5C008E;--c-to:#9D5DC8;}
.rep-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:12px;}
.rep-card-title{font-size:15px;font-weight:700;color:var(--rep-text);display:flex;align-items:center;gap:10px;}
.rep-card-title .ico{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--c-from,#5C008E),var(--c-to,#9D5DC8));color:#fff;font-size:15px;}

/* Gelir 2-col body */
.rep-gelir-body{display:grid;grid-template-columns:1.4fr 1fr;gap:32px;align-items:center;min-height:240px;}
@media (max-width:991px){.rep-gelir-body{grid-template-columns:1fr;}}
.rep-gelir-chart-box{position:relative;height:240px;display:flex;align-items:center;justify-content:center;}
.rep-gelir-chart-box canvas{max-height:240px;}
.rep-gelir-empty{text-align:center;color:var(--rep-text-muted);font-size:13px;}
.rep-gelir-empty i{font-size:36px;color:#dde1ec;display:block;margin-bottom:8px;}
.rep-gelir-list{display:flex;flex-direction:column;gap:10px;}
.rep-gelir-row{border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;align-items:flex-end;background:#fafbfd;border:1px solid var(--rep-border);}
.rep-gelir-row .v{font-size:16px;font-weight:800;}
.rep-gelir-row .l{font-size:11.5px;font-weight:600;opacity:.85;margin-top:2px;}
.rep-gelir-row.nakit{background:#f3eafa;border-color:#e0c8f0;color:#5C008E;}
.rep-gelir-row.kart{background:#defaeb;border-color:#bff0d6;color:#1fbf6f;}
.rep-gelir-row.havale{background:#ffe3eb;border-color:#ffc9d7;color:#c83361;}
.rep-gelir-row.odenen{background:#ede0f7;border-color:#d8bfee;color:#9D5DC8;}
.rep-detay-btn{display:flex;align-items:center;gap:6px;margin:18px auto 0 auto;background:linear-gradient(135deg,#5C008E,#9D5DC8);border:0;border-radius:24px;padding:10px 22px;color:#fff;font-weight:700;font-size:13px;cursor:pointer;transition:transform .18s ease;text-decoration:none;box-shadow:0 6px 16px -8px rgba(92,0,142,.5);}
.rep-detay-btn:hover{transform:translateY(-2px);color:#fff;text-decoration:none;box-shadow:0 8px 20px -8px rgba(92,0,142,.6);}
.rep-detay-wrap{text-align:center;}

/* Tablo */
.rep-tbl{width:100%;border-collapse:collapse;}
.rep-tbl th{font-size:11px;color:var(--rep-text-muted);font-weight:700;letter-spacing:.4px;text-transform:uppercase;text-align:left;padding:10px 14px;border-bottom:1px solid var(--rep-border);}
.rep-tbl td{padding:12px 14px;font-size:13px;color:var(--rep-text);border-bottom:1px solid var(--rep-border);}
.rep-tbl tbody tr:hover{background:#fafbfd;}
.rep-tbl tbody tr:last-child td{border-bottom:0;}
.rep-tbl td.num,.rep-tbl th.num{text-align:right;font-variant-numeric:tabular-nums;}
.rep-tbl .rank{width:30px;color:var(--rep-text-muted);font-weight:700;}
.rep-tbl .bar-cell{width:30%;}
.rep-tbl .bar-cell .bar{height:8px;background:#f3f4f8;border-radius:6px;overflow:hidden;}
.rep-tbl .bar-cell .bar .fill{height:100%;background:linear-gradient(90deg,var(--rep-accent),var(--rep-violet));border-radius:6px;width:0%;transition:width 1s cubic-bezier(.16,1,.3,1);}

/* Podium (personel top 3) */
.rep-podium{display:grid;grid-template-columns:1fr 1.1fr 1fr;gap:14px;align-items:end;margin-bottom:20px;}
@media (max-width:767px){.rep-podium{grid-template-columns:1fr;}}
.podium-block{background:var(--rep-card-bg);border:1px solid var(--rep-border);border-radius:16px;padding:16px;text-align:center;box-shadow:var(--rep-shadow);position:relative;}
.podium-block .medal{font-size:32px;margin-bottom:6px;}
.podium-block.no1{transform:translateY(-12px);background:linear-gradient(180deg,#fff8e0,#fff);border-color:#ffe3a0;}
.podium-block.no2{background:linear-gradient(180deg,#f3f4f8,#fff);}
.podium-block.no3{background:linear-gradient(180deg,#fce4cf,#fff);border-color:#ffd9ad;}
.podium-block .ad{font-size:14px;font-weight:700;color:var(--rep-text);margin-bottom:6px;}
.podium-block .ciro{font-size:18px;font-weight:800;color:var(--rep-text);}
.podium-block .ciro-lbl{font-size:11px;color:var(--rep-text-muted);font-weight:600;letter-spacing:.4px;text-transform:uppercase;}
.podium-block .extra{font-size:11px;color:var(--rep-text-soft);margin-top:6px;}

/* Chart container */
.rep-chart-box{position:relative;height:300px;}
.rep-chart-box.tall{height:340px;}

/* Empty */
.rep-empty{padding:40px 16px;text-align:center;color:var(--rep-text-muted);font-size:13px;}
.rep-empty i{font-size:42px;color:#dde1ec;display:block;margin-bottom:10px;}

/* Skeleton */
.rep-skel{background:linear-gradient(90deg,#f3f4f8 0%,#eaecf4 50%,#f3f4f8 100%);background-size:200% 100%;animation:repShimmer 1.4s infinite;border-radius:8px;}
.rep-skel-line{height:14px;margin:6px 0;}
@keyframes repShimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}
@keyframes repRise{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}

/* 2-col layout for randevu/musteri tabs */
.rep-2col{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
@media (max-width:991px){.rep-2col{grid-template-columns:1fr;}}

/* Cinsiyet rows */
.cinsiyet-row{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:12px;margin-bottom:8px;font-weight:600;}
.cinsiyet-row.kadin{background:#ffe3eb;color:#c83361;}
.cinsiyet-row.erkek{background:#f3eafa;color:#5C008E;}
.cinsiyet-row.belirsiz{background:#f3f4f8;color:#5c6486;}
.cinsiyet-row .v{margin-left:auto;font-weight:800;}
.cinsiyet-row .ico{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.6);font-size:16px;}

/* Kaynak chip */
.kaynak-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.kaynak-chip{padding:14px;border-radius:14px;text-align:center;}
.kaynak-chip .v{font-size:24px;font-weight:800;line-height:1;}
.kaynak-chip .l{font-size:11px;font-weight:600;margin-top:4px;letter-spacing:.4px;text-transform:uppercase;}
.kaynak-chip.k1{background:#f3eafa;color:#5C008E;}
.kaynak-chip.k2{background:#defaeb;color:#1fbf6f;}
.kaynak-chip.k3{background:#ede0f7;color:#9D5DC8;}
</style>

<div class="rep-wrap">

  <div class="rep-tabs">
    <button class="rep-tab is-active" data-section="isletme">İşletme Raporları <i class="bi bi-star-fill star"></i></button>
    <button class="rep-tab" data-section="hizmet">Hizmet Raporları <i class="bi bi-star star"></i></button>
    <button class="rep-tab" data-section="urun">Ürün Raporları <i class="bi bi-star star"></i></button>
    <button class="rep-tab" data-section="personel">Personel Raporları <i class="bi bi-star star"></i></button>
    <button class="rep-tab" data-section="musteri">Müşteri Raporları <i class="bi bi-star star"></i></button>
    <button class="rep-tab" data-section="randevu">Randevu Raporları <i class="bi bi-star star"></i></button>
  </div>

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

  <div id="rep-content">
    {{-- içerik JS ile dolar --}}
  </div>

</div>

<script>
function repInit(){
  var apiBase = '/isletmeyonetim/api/isletmeraporlari';
  var subeParam = @json($apiSubeParam);
  var subeQuery = @json($subeQuery);

  function api(path, extra){
    var url = apiBase + path + subeParam;
    if(extra) url += (subeParam ? '&' : '?') + extra;
    return fetch(url, {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
      .then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); });
  }
  function trMoney(n){ n = Number(n)||0; return n.toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}); }
  function trInt(n){ n = Number(n)||0; return n.toLocaleString('tr-TR'); }
  function animCount(el, to, decimals){
    if(!el) return;
    try{
      var c = new countUp.CountUp(el, to, {duration:1.0, separator:'.', decimal:',', decimalPlaces: decimals || 0});
      if(!c.error){ c.start(); return; }
    }catch(e){}
    el.textContent = (decimals ? Number(to).toFixed(decimals).replace('.', ',') : trInt(to));
  }
  function escape(s){ return (s==null?'':String(s)).replace(/[<>&"]/g,function(c){return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c];}); }
  function currentPeriodQS(){
    var bas = document.getElementById('rep-bas').value;
    var bit = document.getElementById('rep-bit').value;
    var activeChip = document.querySelector('.rep-pchip.is-active');
    var period = activeChip ? activeChip.getAttribute('data-period') : 'custom';
    if(period === 'custom' || (!activeChip && bas && bit)){
      return 'period=custom&bas='+bas+'&bit='+bit;
    }
    return 'period='+period;
  }

  var charts = {};
  function killCharts(){
    Object.keys(charts).forEach(function(k){ try{ charts[k].destroy(); }catch(e){} });
    charts = {};
  }

  // ======================== İŞLETME ========================
  function renderIsletme(){
    var html = ''
      + '<div class="rep-stats">'
      +   '<div class="rep-stat bg-1 delay-1"><div class="ico"><i class="bi bi-calendar-check"></i></div>'
      +     '<div><div class="num" id="r-i-rnd">0</div><div class="lbl">Toplam Randevu Sayısı</div></div></div>'
      +   '<div class="rep-stat bg-2 delay-2"><div class="ico"><i class="bi bi-receipt"></i></div>'
      +     '<div><div class="num" id="r-i-adi">0</div><div class="lbl">Toplam Adisyon Sayısı</div></div></div>'
      +   '<div class="rep-stat bg-3 delay-3"><div class="ico"><i class="bi bi-cart-fill"></i></div>'
      +     '<div><div class="num" id="r-i-urn">0</div><div class="lbl">Satılan Ürün</div></div></div>'
      +   '<div class="rep-stat bg-4 delay-4"><div class="ico"><i class="bi bi-scissors"></i></div>'
      +     '<div><div class="num" id="r-i-hzm">0</div><div class="lbl">Uygulanan Hizmet</div></div></div>'
      + '</div>'
      + '<div class="rep-card tone-blue">'
      +   '<div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-graph-up-arrow"></i></span> Gelir Raporları</div></div>'
      +   '<div class="rep-gelir-body">'
      +     '<div class="rep-gelir-chart-box" id="r-i-chartbox"><canvas id="r-i-chart"></canvas></div>'
      +     '<div class="rep-gelir-list">'
      +       '<div class="rep-gelir-row nakit"><div class="v"><span id="r-i-nakit">0,00</span> ₺</div><div class="l">Nakit</div></div>'
      +       '<div class="rep-gelir-row kart"><div class="v"><span id="r-i-kart">0,00</span> ₺</div><div class="l">Kredi / Banka Kartı</div></div>'
      +       '<div class="rep-gelir-row havale"><div class="v"><span id="r-i-havale">0,00</span> ₺</div><div class="l">Havale / EFT</div></div>'
      +       '<div class="rep-gelir-row odenen"><div class="v"><span id="r-i-odenen">0,00</span> ₺</div><div class="l">Ödenen Gelir</div></div>'
      +     '</div>'
      +   '</div>'
      +   '<div class="rep-detay-wrap"><a class="rep-detay-btn" href="/isletmeyonetim/raporlar'+subeQuery+'">Detaya Git <i class="bi bi-chevron-right"></i></a></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/ozet', currentPeriodQS()).then(function(d){
      animCount(document.getElementById('r-i-rnd'), d.toplam_randevu||0);
      animCount(document.getElementById('r-i-adi'), d.toplam_adisyon||0);
      animCount(document.getElementById('r-i-urn'), d.satilan_urun||0);
      animCount(document.getElementById('r-i-hzm'), d.uygulanan_hizmet||0);
      animCount(document.getElementById('rep-toplam-gelir'), d.toplam_gelir||0, 2);
      animCount(document.getElementById('r-i-nakit'), d.nakit||0, 2);
      animCount(document.getElementById('r-i-kart'), d.kart||0, 2);
      animCount(document.getElementById('r-i-havale'), d.havale||0, 2);
      animCount(document.getElementById('r-i-odenen'), d.odenen||0, 2);
      if(d.tarih_bas) document.getElementById('rep-bas').value = d.tarih_bas;
      if(d.tarih_bit) document.getElementById('rep-bit').value = d.tarih_bit;

      var box = document.getElementById('r-i-chartbox');
      if((d.toplam_gelir||0) <= 0){
        box.innerHTML = '<div class="rep-gelir-empty"><i class="bi bi-pie-chart"></i>Değerler sıfıra eşit olduğu için grafik gösterilmiyor.</div>';
        return;
      }
      var ctx = document.getElementById('r-i-chart').getContext('2d');
      charts.gelir = new Chart(ctx, {
        type:'doughnut',
        data:{labels:['Nakit','Kredi/Banka Kartı','Havale/EFT','Diğer'],
              datasets:[{data:[d.nakit||0,d.kart||0,d.havale||0,d.diger||0],
                         backgroundColor:['#5C008E','#1fbf6f','#c83361','#9D5DC8'],borderWidth:0,hoverOffset:6}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'62%',
          plugins:{legend:{position:'bottom',labels:{font:{size:11,weight:'600'},color:'#5c6486',padding:8,usePointStyle:true,pointStyle:'circle'}},
            tooltip:{callbacks:{label:function(ctx){return ctx.label+': '+trMoney(ctx.parsed)+' ₺';}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });
    });
  }

  // ======================== HİZMET ========================
  function renderHizmet(){
    var html = '<div class="rep-stats">'
      + '<div class="rep-stat bg-2 delay-1"><div class="ico"><i class="bi bi-scissors"></i></div><div><div class="num" id="r-h-adet">0</div><div class="lbl">Toplam Uygulanan</div></div></div>'
      + '<div class="rep-stat bg-1 delay-2"><div class="ico"><i class="bi bi-currency-lira"></i></div><div><div class="num"><span id="r-h-ciro">0,00</span> ₺</div><div class="lbl">Hizmet Cirosu</div></div></div>'
      + '<div class="rep-stat bg-5 delay-3"><div class="ico"><i class="bi bi-tag-fill"></i></div><div><div class="num"><span id="r-h-ort">0,00</span> ₺</div><div class="lbl">Ortalama Fiyat</div></div></div>'
      + '<div class="rep-stat bg-4 delay-4"><div class="ico"><i class="bi bi-trophy-fill"></i></div><div><div class="num" id="r-h-cesit">0</div><div class="lbl">Farklı Hizmet</div></div></div>'
      + '</div>'
      + '<div class="rep-2col">'
      +   '<div class="rep-card tone-green"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-bar-chart-fill"></i></span> En Çok Yapılan Hizmetler</div></div><div class="rep-chart-box" id="r-h-chartbox"><canvas id="r-h-chart"></canvas></div></div>'
      +   '<div class="rep-card tone-violet"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-table"></i></span> Detaylı Liste</div></div><div id="r-h-tbl"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/hizmet', currentPeriodQS()).then(function(d){
      var list = d.hizmetler || [];
      animCount(document.getElementById('r-h-adet'), d.toplam_adet||0);
      animCount(document.getElementById('r-h-ciro'), d.toplam_ciro||0, 2);
      animCount(document.getElementById('r-h-ort'), d.ortalama_fiyat||0, 2);
      animCount(document.getElementById('r-h-cesit'), list.length);

      if(list.length === 0){
        document.getElementById('r-h-chartbox').innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Bu dönemde hizmet uygulanmamış.</div>';
        document.getElementById('r-h-tbl').innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        return;
      }
      var top10 = list.slice(0, 10);
      var ctx = document.getElementById('r-h-chart').getContext('2d');
      charts.hizmet = new Chart(ctx, {
        type:'bar',
        data:{labels:top10.map(function(h){return h.hizmet_adi;}),
              datasets:[{label:'Adet',data:top10.map(function(h){return h.adet;}),
                         backgroundColor:'rgba(31,191,111,.85)',borderRadius:8,maxBarThickness:24}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{
            label:function(c){return c.parsed.x+' adet';},
            afterLabel:function(c){return 'Ciro: '+trMoney(top10[c.dataIndex].ciro)+' ₺';}
          }}},
          scales:{x:{beginAtZero:true,ticks:{font:{size:10}}},y:{ticks:{font:{size:11}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var thtml = '<table class="rep-tbl"><thead><tr><th class="rank">#</th><th>Hizmet</th><th class="num">Adet</th><th class="num">Ciro</th></tr></thead><tbody>';
      list.forEach(function(h,i){
        thtml += '<tr><td class="rank">'+(i+1)+'</td><td>'+escape(h.hizmet_adi)+'</td><td class="num">'+trInt(h.adet)+'</td><td class="num"><b>'+trMoney(h.ciro)+' ₺</b></td></tr>';
      });
      thtml += '</tbody></table>';
      document.getElementById('r-h-tbl').innerHTML = thtml;
    });
  }

  // ======================== ÜRÜN ========================
  function renderUrun(){
    var html = '<div class="rep-stats">'
      + '<div class="rep-stat bg-3 delay-1"><div class="ico"><i class="bi bi-cart-fill"></i></div><div><div class="num" id="r-u-adet">0</div><div class="lbl">Satılan Adet</div></div></div>'
      + '<div class="rep-stat bg-1 delay-2"><div class="ico"><i class="bi bi-currency-lira"></i></div><div><div class="num"><span id="r-u-ciro">0,00</span> ₺</div><div class="lbl">Ürün Cirosu</div></div></div>'
      + '<div class="rep-stat bg-5 delay-3"><div class="ico"><i class="bi bi-tag-fill"></i></div><div><div class="num"><span id="r-u-ort">0,00</span> ₺</div><div class="lbl">Ortalama Fiyat</div></div></div>'
      + '<div class="rep-stat bg-2 delay-4"><div class="ico"><i class="bi bi-box-seam"></i></div><div><div class="num" id="r-u-cesit">0</div><div class="lbl">Farklı Ürün</div></div></div>'
      + '</div>'
      + '<div class="rep-2col">'
      +   '<div class="rep-card tone-pink"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-bar-chart-fill"></i></span> En Çok Satan Ürünler</div></div><div class="rep-chart-box" id="r-u-chartbox"><canvas id="r-u-chart"></canvas></div></div>'
      +   '<div class="rep-card tone-violet"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-table"></i></span> Detaylı Liste</div></div><div id="r-u-tbl"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/urun', currentPeriodQS()).then(function(d){
      var list = d.urunler || [];
      animCount(document.getElementById('r-u-adet'), d.toplam_adet||0);
      animCount(document.getElementById('r-u-ciro'), d.toplam_ciro||0, 2);
      animCount(document.getElementById('r-u-ort'), d.ortalama_fiyat||0, 2);
      animCount(document.getElementById('r-u-cesit'), list.length);

      if(list.length === 0){
        document.getElementById('r-u-chartbox').innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Bu dönemde ürün satışı yok.</div>';
        document.getElementById('r-u-tbl').innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        return;
      }
      var top10 = list.slice(0,10);
      var ctx = document.getElementById('r-u-chart').getContext('2d');
      charts.urun = new Chart(ctx, {
        type:'bar',
        data:{labels:top10.map(function(u){return u.urun_adi;}),
              datasets:[{label:'Adet',data:top10.map(function(u){return u.adet;}),
                         backgroundColor:'rgba(217,70,239,.85)',borderRadius:8,maxBarThickness:24}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{
            label:function(c){return c.parsed.x+' adet';},
            afterLabel:function(c){return 'Ciro: '+trMoney(top10[c.dataIndex].ciro)+' ₺';}
          }}},
          scales:{x:{beginAtZero:true},y:{ticks:{font:{size:11}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var thtml = '<table class="rep-tbl"><thead><tr><th class="rank">#</th><th>Ürün</th><th class="num">Adet</th><th class="num">Ciro</th></tr></thead><tbody>';
      list.forEach(function(u,i){
        thtml += '<tr><td class="rank">'+(i+1)+'</td><td>'+escape(u.urun_adi)+'</td><td class="num">'+trInt(u.adet)+'</td><td class="num"><b>'+trMoney(u.ciro)+' ₺</b></td></tr>';
      });
      thtml += '</tbody></table>';
      document.getElementById('r-u-tbl').innerHTML = thtml;
    });
  }

  // ======================== PERSONEL ========================
  function renderPersonel(){
    var html = '<div id="r-p-podium"></div>'
      + '<div class="rep-2col">'
      +   '<div class="rep-card tone-violet"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-bar-chart-fill"></i></span> Personel Cirosu</div></div><div class="rep-chart-box" id="r-p-chartbox"><canvas id="r-p-chart"></canvas></div></div>'
      +   '<div class="rep-card tone-blue"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-table"></i></span> Detaylı Liste</div></div><div id="r-p-tbl"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/personel', currentPeriodQS()).then(function(d){
      var list = (d.personeller || []).filter(function(p){ return p.randevu_say>0 || p.hizmet_say>0 || p.ciro>0; });
      // Hero kart toplam gelir alani sadece isletme tab'inda doluyor, bunu temizleyelim
      animCount(document.getElementById('rep-toplam-gelir'), list.reduce(function(s,p){return s+(p.ciro||0);},0), 2);

      var podHtml = '';
      if(list.length === 0){
        podHtml = '<div class="rep-card tone-violet"><div class="rep-empty"><i class="bi bi-people"></i>Bu dönemde personel aktivitesi yok.</div></div>';
      } else {
        var top3 = list.slice(0,3);
        var medals = ['🥇','🥈','🥉'];
        var classes = ['no1','no2','no3'];
        var order = top3.length >= 3 ? [1,0,2] : (top3.length === 2 ? [1,0] : [0]);
        podHtml = '<div class="rep-podium">';
        order.forEach(function(idx){
          var p = top3[idx]; if(!p) return;
          podHtml += '<div class="podium-block '+classes[idx]+'">'
            + '<div class="medal">'+medals[idx]+'</div>'
            + '<div class="ad">'+escape(p.personel_adi)+'</div>'
            + '<div class="ciro">'+trMoney(p.ciro)+' ₺</div>'
            + '<div class="ciro-lbl">Ciro</div>'
            + '<div class="extra">'+trInt(p.randevu_say)+' randevu · '+trInt(p.hizmet_say)+' hizmet</div></div>';
        });
        podHtml += '</div>';
      }
      document.getElementById('r-p-podium').innerHTML = podHtml;

      if(list.length === 0){
        document.getElementById('r-p-chartbox').innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        document.getElementById('r-p-tbl').innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Veri yok.</div>';
        return;
      }
      var ctx = document.getElementById('r-p-chart').getContext('2d');
      var labels = list.map(function(p){return p.personel_adi;});
      var ciros = list.map(function(p){return p.ciro;});
      charts.personel = new Chart(ctx, {
        type:'bar',
        data:{labels:labels,datasets:[{label:'Ciro (₺)',data:ciros,backgroundColor:'rgba(92,0,142,.85)',borderRadius:8,maxBarThickness:36}]},
        options:{responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return trMoney(c.parsed.y)+' ₺';}}}},
          scales:{y:{beginAtZero:true,ticks:{callback:function(v){return trMoney(v)+' ₺';}}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var thtml = '<table class="rep-tbl"><thead><tr><th class="rank">#</th><th>Personel</th><th class="num">Randevu</th><th class="num">Hizmet</th><th class="num">Ciro</th></tr></thead><tbody>';
      list.forEach(function(p,i){
        thtml += '<tr><td class="rank">'+(i+1)+'</td><td>'+escape(p.personel_adi)+'</td><td class="num">'+trInt(p.randevu_say)+'</td><td class="num">'+trInt(p.hizmet_say)+'</td><td class="num"><b>'+trMoney(p.ciro)+' ₺</b></td></tr>';
      });
      thtml += '</tbody></table>';
      document.getElementById('r-p-tbl').innerHTML = thtml;
    });
  }

  // ======================== MÜŞTERİ ========================
  function renderMusteri(){
    var html = '<div class="rep-stats">'
      + '<div class="rep-stat bg-1 delay-1"><div class="ico"><i class="bi bi-people-fill"></i></div><div><div class="num" id="r-m-aktif">0</div><div class="lbl">Aktif Müşteri</div></div></div>'
      + '<div class="rep-stat bg-2 delay-2"><div class="ico"><i class="bi bi-person-plus-fill"></i></div><div><div class="num" id="r-m-yeni">0</div><div class="lbl">Yeni Müşteri</div></div></div>'
      + '<div class="rep-stat bg-5 delay-3"><div class="ico"><i class="bi bi-arrow-repeat"></i></div><div><div class="num" id="r-m-tekrar">0</div><div class="lbl">Tekrar Gelen</div></div></div>'
      + '<div class="rep-stat bg-3 delay-4"><div class="ico"><i class="bi bi-heart-fill"></i></div><div><div class="num" id="r-m-orn">0</div><div class="lbl">Sadakat %</div></div></div>'
      + '</div>'
      + '<div class="rep-2col">'
      +   '<div class="rep-card tone-pink"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-gender-ambiguous"></i></span> Cinsiyet Dağılımı</div></div><div id="r-m-cinsiyet"></div></div>'
      +   '<div class="rep-card tone-orange"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-trophy-fill"></i></span> En Çok Harcayan 10 Müşteri</div></div><div id="r-m-top"></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/musteri', currentPeriodQS()).then(function(d){
      animCount(document.getElementById('r-m-aktif'), d.toplam_aktif||0);
      animCount(document.getElementById('r-m-yeni'), d.yeni_musteri||0);
      animCount(document.getElementById('r-m-tekrar'), d.tekrar_gelen||0);
      var sadakat = (d.toplam_aktif||0) > 0 ? Math.round(((d.tekrar_gelen||0)/(d.toplam_aktif))*100) : 0;
      var sadEl = document.getElementById('r-m-orn');
      var s = performance.now();
      function st(t){ var k=Math.min(1,(t-s)/1100); var e=1-Math.pow(1-k,4); sadEl.textContent=Math.round(sadakat*e)+'%'; if(k<1) requestAnimationFrame(st); }
      requestAnimationFrame(st);

      var c = d.cinsiyet || {kadin:0,erkek:0,belirsiz:0};
      var cinsBox = document.getElementById('r-m-cinsiyet');
      cinsBox.innerHTML = ''
        + '<div class="cinsiyet-row kadin"><div class="ico"><i class="bi bi-gender-female"></i></div>Kadın <span class="v">'+trInt(c.kadin)+'</span></div>'
        + '<div class="cinsiyet-row erkek"><div class="ico"><i class="bi bi-gender-male"></i></div>Erkek <span class="v">'+trInt(c.erkek)+'</span></div>'
        + '<div class="cinsiyet-row belirsiz"><div class="ico"><i class="bi bi-question-circle"></i></div>Belirtilmemiş <span class="v">'+trInt(c.belirsiz)+'</span></div>';

      var top = d.top_musteriler || [];
      var topBox = document.getElementById('r-m-top');
      if(top.length === 0){
        topBox.innerHTML = '<div class="rep-empty"><i class="bi bi-inbox"></i>Bu dönemde tahsilat yok.</div>';
      } else {
        var thtml = '<table class="rep-tbl"><thead><tr><th class="rank">#</th><th>Müşteri</th><th>Telefon</th><th class="num">İşlem</th><th class="num">Harcama</th></tr></thead><tbody>';
        top.forEach(function(m,i){
          thtml += '<tr><td class="rank">'+(i+1)+'</td><td>'+escape(m.name)+'</td><td>'+escape(m.cep_telefon||'-')+'</td><td class="num">'+trInt(m.islem_say||0)+'</td><td class="num"><b>'+trMoney(m.toplam||0)+' ₺</b></td></tr>';
        });
        thtml += '</tbody></table>';
        topBox.innerHTML = thtml;
      }
    });
  }

  // ======================== RANDEVU ========================
  function renderRandevu(){
    var html = '<div class="rep-stats">'
      + '<div class="rep-stat bg-1 delay-1"><div class="ico"><i class="bi bi-calendar-check"></i></div><div><div class="num" id="r-r-toplam">0</div><div class="lbl">Toplam Randevu</div></div></div>'
      + '<div class="rep-stat bg-2 delay-2"><div class="ico"><i class="bi bi-check-circle-fill"></i></div><div><div class="num" id="r-r-son">0</div><div class="lbl">Sonuçlanan (Geldi)</div></div></div>'
      + '<div class="rep-stat bg-3 delay-3"><div class="ico"><i class="bi bi-x-circle-fill"></i></div><div><div class="num" id="r-r-gel">0</div><div class="lbl">Gelmedi</div></div></div>'
      + '<div class="rep-stat bg-4 delay-4"><div class="ico"><i class="bi bi-slash-circle"></i></div><div><div class="num" id="r-r-ipt">0</div><div class="lbl">İptal Edilen</div></div></div>'
      + '</div>'
      + '<div class="rep-card tone-orange"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-globe2"></i></span> Randevu Kaynağı</div></div><div class="kaynak-grid" id="r-r-kaynak"></div></div>'
      + '<div class="rep-2col">'
      +   '<div class="rep-card tone-violet"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-clock"></i></span> Saatlik Yoğunluk</div></div><div class="rep-chart-box tall" id="r-r-saatbox"><canvas id="r-r-saat"></canvas></div></div>'
      +   '<div class="rep-card tone-green"><div class="rep-card-head"><div class="rep-card-title"><span class="ico"><i class="bi bi-calendar-week"></i></span> Haftanın Günleri</div></div><div class="rep-chart-box tall" id="r-r-gunbox"><canvas id="r-r-gun"></canvas></div></div>'
      + '</div>';
    document.getElementById('rep-content').innerHTML = html;

    api('/randevu', currentPeriodQS()).then(function(d){
      animCount(document.getElementById('r-r-toplam'), d.toplam||0);
      animCount(document.getElementById('r-r-son'), d.sonuclanan||0);
      animCount(document.getElementById('r-r-gel'), d.gelmedi||0);
      animCount(document.getElementById('r-r-ipt'), d.iptal||0);

      var k = d.kaynak || {isletme:0,web:0,uygulama:0};
      document.getElementById('r-r-kaynak').innerHTML =
        '<div class="kaynak-chip k1"><div class="v">'+trInt(k.isletme)+'</div><div class="l">İşletme</div></div>'
       +'<div class="kaynak-chip k2"><div class="v">'+trInt(k.web)+'</div><div class="l">Website</div></div>'
       +'<div class="kaynak-chip k3"><div class="v">'+trInt(k.uygulama)+'</div><div class="l">Uygulama</div></div>';

      var saatData = d.saat_dagilim || [];
      // Sadece 6-23 saatlerini goster (calisma saatleri)
      var saatFiltered = saatData.filter(function(s){ var h = parseInt(s.saat,10); return h>=6 && h<=23; });
      var ctx1 = document.getElementById('r-r-saat').getContext('2d');
      charts.saat = new Chart(ctx1, {
        type:'line',
        data:{labels:saatFiltered.map(function(s){return s.saat+':00';}),
              datasets:[{data:saatFiltered.map(function(s){return s.adet;}),
                         borderColor:'#5C008E',backgroundColor:'rgba(92,0,142,.15)',
                         fill:true,tension:.35,pointRadius:3,pointBackgroundColor:'#5C008E'}]},
        options:{responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.parsed.y+' randevu';}}}},
          scales:{y:{beginAtZero:true,ticks:{precision:0}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });

      var gunData = d.gun_dagilim || [];
      var ctx2 = document.getElementById('r-r-gun').getContext('2d');
      charts.gun = new Chart(ctx2, {
        type:'bar',
        data:{labels:gunData.map(function(g){return g.gun;}),
              datasets:[{data:gunData.map(function(g){return g.adet;}),
                         backgroundColor:['#5C008E','#1fbf6f','#9D5DC8','#c47214','#c83361','#d946ef','#f7c948'],
                         borderRadius:8,maxBarThickness:42}]},
        options:{responsive:true,maintainAspectRatio:false,
          plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return c.parsed.y+' randevu';}}}},
          scales:{y:{beginAtZero:true,ticks:{precision:0}}},
          animation:{duration:900,easing:'easeOutQuart'}}
      });
    });
  }

  // ======================== ROUTER ========================
  var activeSection = 'isletme';
  function loadSection(section){
    activeSection = section;
    killCharts();
    if(section === 'isletme') renderIsletme();
    else if(section === 'hizmet') renderHizmet();
    else if(section === 'urun') renderUrun();
    else if(section === 'personel') renderPersonel();
    else if(section === 'musteri') renderMusteri();
    else if(section === 'randevu') renderRandevu();
  }
  function reload(){ loadSection(activeSection); }

  // Tabs
  document.querySelectorAll('.rep-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.rep-tab').forEach(function(b){ b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      loadSection(btn.getAttribute('data-section'));
    });
  });
  // Period chips
  document.querySelectorAll('.rep-pchip').forEach(function(chip){
    chip.addEventListener('click', function(){
      document.querySelectorAll('.rep-pchip').forEach(function(c){ c.classList.remove('is-active'); });
      chip.classList.add('is-active');
      reload();
    });
  });
  // Date inputs
  ['rep-bas','rep-bit'].forEach(function(id){
    document.getElementById(id).addEventListener('change', function(){
      document.querySelectorAll('.rep-pchip').forEach(function(c){ c.classList.remove('is-active'); });
      reload();
    });
  });

  // İlk yükleme
  loadSection('isletme');
}

(function repBoot(){
  function ready(){
    if(typeof Chart === 'undefined'){ return setTimeout(ready, 50); }
    repInit();
  }
  if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', ready); }
  else { ready(); }
})();
</script>

@endsection
