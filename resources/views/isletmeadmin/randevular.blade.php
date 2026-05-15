@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
<style>
   /* Randevu detay popup gorunumu (eskiden her event icin partial'dan tekrar tekrar gomuluyordu, sayfaya bir kez tasindi) */
   .rd-detail { font-size:13.5px; color:#3a2e57; margin:-10px -15px; }
   .rd-detail .rd-row { display:flex; align-items:flex-start; padding:9px 14px; border-bottom:1px solid #f1ecf7; gap:10px; }
   .rd-detail .rd-row:last-child { border-bottom:0; }
   .rd-detail .rd-row:nth-child(odd) { background:#fbfafd; }
   .rd-detail .rd-label { flex:0 0 160px; color:#7c6c8a; font-weight:600; font-size:12.5px; display:flex; align-items:center; gap:6px; }
   .rd-detail .rd-label i { color:#5C008E; opacity:.75; width:14px; text-align:center; }
   .rd-detail .rd-value { flex:1; color:#2d2143; font-weight:500; word-break:break-word; }
   .rd-detail .rd-value.empty { color:#bcb3c9; font-style:italic; font-weight:400; }
   .rd-status { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11.5px; font-weight:700; }
   .rd-status.beklemede { background:#fff4e0; color:#a86200; }
   .rd-status.basarili  { background:#e6f9ed; color:#0c7a3a; }
   .rd-status.iptal     { background:#fdecec; color:#c81e1e; }
   .rd-status.geldi     { background:#e6f9ed; color:#0c7a3a; }
   .rd-status.gelmedi   { background:#fdecec; color:#c81e1e; }
   .rdb-row { display:flex; gap:8px; flex-wrap:wrap; width:100%; }
   .rdb-row .btn { flex: 1 1 130px; min-width: 0; border-radius: 8px; font-weight: 600; font-size: 13px; padding: 9px 12px; line-height: 1.2; white-space: normal; }
   .rdb-row .btn i { margin-right: 4px; }
   .rdb-row .rdb-pull-right { margin-left: auto; flex-grow: 0; }
</style>
<div class="page-header">
   <div class="row">
   <div class="col-md-4 col-sm-6 col-xs-7 col-7">
   <div class="title">
      <h1>{{$sayfa_baslik}}</h1>
   </div>
   <nav aria-label="breadcrumb" role="navigation">
      <ol class="breadcrumb">
         <li class="breadcrumb-item">
            <a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a>
         </li>
         <li class="breadcrumb-item active" aria-current="page">
            {{$sayfa_baslik}}
         </li>
      </ol>
   </nav>
</div>

<div class="col-md-8 col-sm-6 col-xs-5 col-5">
   <div class="d-flex justify-content-end">
   <button class="btn btn-primary mr-2 randevu-count-button">
    Toplam Randevu: {{$randevular['randevu_sayisi']}}
</button>
      
      <a href="#" data-toggle="modal" data-target="#modal-view-event-add" class="btn btn-success btn-lg yenieklebuton">
         <i class="fa fa-plus"></i> Yeni Randevu
      </a>
   </div>
</div>
   </div>
</div>
<div class="pd-20 card-box mb-30" >
   <div class="row" style="margin-bottom: 10px;">

      @if(Auth::guard('satisortakligi')->check() || ( Auth::guard('isletmeyonetim')->check() && !Auth::guard('isletmeyonetim')->user()->hasRole('Personel')))
      <div class="col-md-6 col-sm-6 col-xs-6 col-6">
      @else
      <div class="col-md-6 col-sm-6 col-xs-6 col-6" style="display:none">
      @endif 
         <select class="form-control" id="randevu_ayarina_gore">
                     
                    <option {{($isletme->randevu_takvim_turu==1) ? 'selected' : ''}} value="1">Personele Göre</option>
                    <option {{($isletme->randevu_takvim_turu==0) ? 'selected' : ''}} value="0">Hizmete Göre</option>
                    <option {{($isletme->randevu_takvim_turu==2) ? 'selected' : ''}} value="2">Cihaza Göre</option>
                    <option {{($isletme->randevu_takvim_turu==3) ? 'selected' : ''}} value="3">Odaya Göre</option>
         </select>
      </div>
      
      <div class="col-md-6 col-sm-6 col-xs-6 col-6">
         <input type="text" class="form-control calendardatepicker" autocomplete="off" id='takvim_tarihe_gore' placeholder='Tarih Seçiniz'>
      </div>
   </div>
   <div style="position:relative; width:100%; overflow-y:auto">

       
      <div class="calendar-wrap">
         <div id="calendar">
         </div>
      </div>
   </div>




</div>
<div id="hata"></div>

{{-- Gap kampanyasi takvim renklendirmesi (Sabah/Ogleden sonra/Aksam indirim saatleri) --}}
<style type="text/css">
  /* Bilgi seridi */
  .gap-info-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    margin: 0 0 12px 0;
    background: #FFFBEB;
    border: 1px solid rgba(251, 191, 36, 0.35);
    border-radius: 10px;
    flex-wrap: wrap;
    font-size: 13px;
  }
  .gap-info-strip .gap-strip-label {
    font-weight: 700;
    color: #B45309;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .gap-info-strip .gap-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #fff;
    border: 1px solid #FCD34D;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: #1a1a1a;
    cursor: default;
  }
  .gap-info-strip .gap-chip.gap-morning { border-color: #FCD34D; }
  .gap-info-strip .gap-chip.gap-afternoon { border-color: #FB923C; }
  .gap-info-strip .gap-chip.gap-evening { border-color: #8B5CF6; }
  .gap-info-strip .gap-chip .gap-disc {
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: #fff;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: -0.2px;
  }
  /* Randevu kartlarinin sag-ust kosesinde indirim rozeti */
  #calendar .fc-event { position: relative; overflow: visible !important; }
  #calendar .gap-event-badge {
    position: absolute;
    top: -5px;
    right: -4px;
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    padding: 1.5px 6px;
    border-radius: 999px;
    border: 1.5px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.22);
    z-index: 10;
    letter-spacing: -0.2px;
    line-height: 1.1;
    pointer-events: none;
  }
</style>

<script>
(function() {
  // Aktif gap kampanyalarini getir ve takvim saat slot'larina renkli arka plan ekle
  var salonId = '{{ $isletme->id }}';
  var apiBase = 'https://apptest.randevumcepte.com.tr/api/v1';
  var gapCampaigns = [];

  function buildInfoStrip() {
    if (gapCampaigns.length === 0) return;
    if (document.querySelector('.gap-info-strip')) return; // zaten var

    var strip = document.createElement('div');
    strip.className = 'gap-info-strip';
    var label = document.createElement('span');
    label.className = 'gap-strip-label';
    label.innerHTML = '<i class="fa fa-tag"></i> Aktif Kampanya:';
    strip.appendChild(label);

    gapCampaigns.forEach(function(c) {
      var chip = document.createElement('span');
      chip.className = 'gap-chip gap-' + c.gapKey;
      var sh = String(c.startHour).padStart(2, '0');
      var eh = String(c.endHour).padStart(2, '0');
      chip.innerHTML = (c.gapLabel || '') + ' ' + sh + '-' + eh +
        ' <span class="gap-disc">%' + c.discount + '</span>';
      strip.appendChild(chip);
    });

    var wrap = document.querySelector('.calendar-wrap');
    if (wrap && wrap.parentNode) {
      wrap.parentNode.insertBefore(strip, wrap);
    }
  }

  function applyEventBadges() {
    if (gapCampaigns.length === 0) return;
    var events = document.querySelectorAll('#calendar .fc-event');
    events.forEach(function(el) {
      // Eski rozet varsa kaldır (idempotent — rerender'larda dublike olmasın)
      var oldBadge = el.querySelector('.gap-event-badge');
      if (oldBadge) oldBadge.parentNode.removeChild(oldBadge);

      // Boş slot / disabled / background event'lere rozet basma
      if (el.classList.contains('disabled-event')) return;
      if (el.classList.contains('fc-bgevent')) return;
      if (el.classList.contains('fc-helper')) return;

      // Title boşsa ya da "Boş slot" yazıyorsa atla
      // (FullCalendar disabled-event class'ını async eklediği için yedek kontrol)
      var titleEl = el.querySelector('.fc-title');
      var titleText = titleEl ? (titleEl.textContent || '').trim() : '';
      if (!titleText || titleText === 'Boş slot' || titleText === ' ') return;

      // Saat — .fc-time text içeriğinden parse et ("09:00 - 10:00" veya "09:00")
      var timeEl = el.querySelector('.fc-time');
      if (!timeEl) return;
      var timeText = timeEl.textContent || timeEl.getAttribute('data-start') || '';
      var match = timeText.match(/(\d{1,2}):/);
      if (!match) return;
      var hour = parseInt(match[1], 10);

      for (var i = 0; i < gapCampaigns.length; i++) {
        var c = gapCampaigns[i];
        if (hour >= c.startHour && hour < c.endHour && c.discount > 0) {
          var badge = document.createElement('span');
          badge.className = 'gap-event-badge';
          badge.textContent = '%' + c.discount;
          badge.title = (c.gapLabel || '') + ' Kampanyası — %' + c.discount + ' indirim';
          el.appendChild(badge);
          break;
        }
      }
    });
  }

  function fetchAndApply() {
    if (!salonId) return;
    fetch(apiBase + '/aktifGapKampanyalari/' + salonId, { credentials: 'omit' })
      .then(function(r) { return r.ok ? r.json() : null; })
      .then(function(data) {
        if (!data || !data.kampanyalar) return;
        gapCampaigns = data.kampanyalar;
        buildInfoStrip();
        // Event badges: FullCalendar render'ın bitmesi için kısa gecikme
        setTimeout(applyEventBadges, 300);
      })
      .catch(function() { /* sessiz geç */ });
  }

  function init() {
    fetchAndApply();

    // FullCalendar view değişikliklerinde sadece .fc-event eklenmesini izle
    // (slot yapısına dokunmuyoruz — sadece event kartlarına rozet)
    var target = document.getElementById('calendar');
    if (target && window.MutationObserver) {
      var observer = new MutationObserver(function(mutations) {
        if (gapCampaigns.length === 0) return;
        // Sadece event eklendiyse tetikle (optimize: her DOM mutation'ı için değil)
        var eventChanged = false;
        for (var m = 0; m < mutations.length; m++) {
          var added = mutations[m].addedNodes;
          for (var i = 0; i < added.length; i++) {
            var node = added[i];
            if (node.nodeType === 1 && (
              (node.classList && node.classList.contains('fc-event')) ||
              (node.querySelector && node.querySelector('.fc-event'))
            )) {
              eventChanged = true;
              break;
            }
          }
          if (eventChanged) break;
        }
        if (!eventChanged) return;

        // Throttle: 300ms — FullCalendar disabled-event class'ı async geliyor
        clearTimeout(window.__gapApplyTimer);
        window.__gapApplyTimer = setTimeout(applyEventBadges, 300);
      });
      observer.observe(target, { childList: true, subtree: true });
    }

    // Her 5 dakikada bir kampanya listesi yenile (60sn yerine — gereksiz yük)
    setInterval(fetchAndApply, 300000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>
@endsection