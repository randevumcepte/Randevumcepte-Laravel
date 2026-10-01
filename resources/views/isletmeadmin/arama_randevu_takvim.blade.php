@extends("layout.layout_isletmeadmin")
@section("content")

<style>
.art-wrap{ --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC8; }
.art-hero{ background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%); border-radius:18px; padding:24px 26px; color:#fff; margin-bottom:20px; box-shadow:0 12px 30px -10px rgba(92,0,142,.45); position:relative; overflow:hidden; }
.art-hero:after{ content:""; position:absolute; right:-40px; top:-40px; width:170px; height:170px; border-radius:50%; background:rgba(255,255,255,.10); }
.art-hero h4{ margin:0 0 6px; font-weight:700; font-size:22px; color:#fff; }
.art-hero p{ margin:0; opacity:.92; font-size:13.5px; max-width:640px; }
.art-hero-btn{ display:inline-flex; align-items:center; gap:8px; margin-top:14px; background:#fff; color:#5C008E; font-weight:800; font-size:13.5px; padding:10px 18px; border-radius:11px; text-decoration:none; position:relative; z-index:2; }
.art-hero-btn:hover{ color:#5C008E; }
.art-panel{ background:#fff; border-radius:18px; border:1px solid #eef0f5; box-shadow:0 6px 20px -12px rgba(30,30,60,.16); padding:18px 20px; }
.art-legend{ display:flex; gap:16px; flex-wrap:wrap; margin-bottom:14px; font-size:12.5px; color:#5a5570; }
.art-legend .lg{ display:inline-flex; align-items:center; gap:6px; }
.art-legend .dot{ width:12px; height:12px; border-radius:3px; display:inline-block; }
#ar_takvim{ min-height:560px; }
#ar_takvim .fc-event{ cursor:pointer; border:none; padding:2px 4px; font-size:12px; }
</style>

<div class="art-wrap">
   <div class="art-hero">
      <h4><i class="fa fa-calendar-check-o"></i> {{ $sayfa_baslik }}</h4>
      <p>Tüm personelin arama randevularını tek takvimde görün. Bir randevuya tıklayınca o personelin görüşme detayını açabilirsiniz. Renkler: planlı, geciken, zamanında/geç arandı.</p>
      <a href="/isletmeyonetim/arama-dashboard?sube={{ $isletme->id }}" class="art-hero-btn"><i class="fa fa-arrow-left"></i> Performans Paneline Dön</a>
   </div>

   <div class="art-panel">
      <div class="art-legend">
         <span class="lg"><span class="dot" style="background:#2563eb;"></span> Planlı</span>
         <span class="lg"><span class="dot" style="background:#dc2626;"></span> Geciken (aranmadı)</span>
         <span class="lg"><span class="dot" style="background:#16a34a;"></span> Zamanında arandı</span>
         <span class="lg"><span class="dot" style="background:#f59e0b;"></span> Geç arandı</span>
      </div>
      <div id="ar_takvim"></div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

<script>
$(document).ready(function(){
   var sube = $('input[name="sube"]').val();
   $('#ar_takvim').fullCalendar({
      locale: 'tr',
      header: { left:'prev,next today', center:'title', right:'month,agendaWeek,agendaDay,listWeek' },
      defaultView: 'month',
      height: 'auto',
      nowIndicator: true,
      timeFormat: 'HH:mm',
      events: function(start, end, tz, cb){
         $.get('/isletmeyonetim/arama-randevu-takvim-verileri', {
            sube: sube,
            start: start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD')
         }, function(res){ cb(res || []); }).fail(function(){ cb([]); });
      },
      eventClick: function(ev){
         var metin = '👤 Personel: ' + (ev.personel||'-') + '\n'
                   + '📞 Müşteri: ' + (ev.musteri||'-') + '\n'
                   + '🕒 Zaman: ' + (ev.start ? ev.start.format('DD.MM.YYYY HH:mm') : '-') + '\n'
                   + '📌 Durum: ' + (ev.durum_metin||'-');
         var git = function(){ window.location.href = '/isletmeyonetim/arama-dashboard?sube=' + sube + '&acpersonel=' + ev.personel_id; };
         if (typeof swal === 'function'){
            var r = swal({ title:'Arama Randevusu', text:metin, showCancelButton:true,
               confirmButtonText:'Personel Detayı', cancelButtonText:'Kapat' },
               function(onay){ if(onay) git(); });
            if (r && typeof r.then === 'function'){ r.then(function(x){ if(x===true||(x&&x.value)) git(); }).catch(function(){}); }
         } else {
            if (confirm(metin + '\n\nPersonel detayına gidilsin mi?')) git();
         }
         return false;
      }
   });
});
</script>
@endsection
