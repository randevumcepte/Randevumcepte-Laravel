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

/* Kontroller */
.art-ctrl{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.art-ctrl .btn-nav{ border:1px solid #e3d5f5; background:#faf7ff; color:#5C008E; font-weight:700; border-radius:10px; padding:8px 12px; cursor:pointer; font-size:14px; }
.art-ctrl .btn-nav:hover{ background:#f1e9fb; }
.art-ctrl input[type="date"]{ border:1px solid #d9c9f0; border-radius:10px; padding:8px 11px; font-size:14px; outline:none; }
.art-ctrl .art-gun-baslik{ font-weight:800; color:#2b1b45; font-size:16px; margin-left:4px; }
.art-ctrl .art-say{ margin-left:auto; font-size:13px; color:#6d28d9; font-weight:700; }

.art-legend{ display:flex; gap:16px; flex-wrap:wrap; margin-bottom:14px; font-size:12.5px; color:#5a5570; }
.art-legend .lg{ display:inline-flex; align-items:center; gap:6px; }
.art-legend .dot{ width:12px; height:12px; border-radius:3px; display:inline-block; }

/* Resource board */
.art-board{ display:flex; gap:14px; overflow-x:auto; padding-bottom:10px; align-items:flex-start; }
.art-col{ flex:0 0 260px; background:#faf9fe; border:1px solid #ece7f6; border-radius:14px; overflow:hidden; }
.art-col-head{ background:linear-gradient(120deg,#5C008E,#7B2FB8); color:#fff; padding:11px 13px; font-weight:800; font-size:13.5px; display:flex; justify-content:space-between; align-items:center; gap:8px; }
.art-col-head .adet{ background:rgba(255,255,255,.22); border-radius:20px; padding:2px 9px; font-size:12px; font-weight:700; }
.art-col-body{ padding:10px; display:flex; flex-direction:column; gap:8px; max-height:65vh; overflow-y:auto; }
.art-item{ border-left:4px solid #2563eb; background:#fff; border:1px solid #eef0f5; border-radius:10px; padding:9px 11px; cursor:pointer; transition:box-shadow .12s; }
.art-item:hover{ box-shadow:0 4px 14px -6px rgba(30,30,60,.25); }
.art-item .saat{ font-weight:800; color:#1e1733; font-size:14px; }
.art-item .mus{ font-size:13px; color:#4a4461; margin-top:2px; }
.art-item .durum{ display:inline-block; margin-top:5px; font-size:11px; font-weight:700; padding:1px 8px; border-radius:20px; }
.art-col-bos{ padding:18px; text-align:center; color:#a9a3bb; font-size:12.5px; }
.art-bos{ padding:40px; text-align:center; color:#9a93ad; }
.art-bos i{ font-size:34px; display:block; margin-bottom:10px; opacity:.5; }
.art-spin{ padding:40px; text-align:center; color:#7B2FB8; }
</style>

<div class="art-wrap">
   <div class="art-hero">
      <h4><i class="fa fa-calendar-check-o"></i> {{ $sayfa_baslik }}</h4>
      <p>Seçtiğiniz güne ait arama randevularını <b>personele göre</b> sütunlar halinde görün; her sütunda aramalar <b>saatine göre</b> sıralıdır. Bir aramaya tıklayınca o personelin detayına gidebilirsiniz.</p>
      <a href="/isletmeyonetim/arama-dashboard?sube={{ $isletme->id }}" class="art-hero-btn"><i class="fa fa-arrow-left"></i> Performans Paneline Dön</a>
   </div>

   <div class="art-panel">
      <div class="art-ctrl">
         <button class="btn-nav" id="art_prev" title="Önceki gün"><i class="fa fa-chevron-left"></i></button>
         <input type="date" id="art_tarih" value="{{ date('Y-m-d') }}">
         <button class="btn-nav" id="art_next" title="Sonraki gün"><i class="fa fa-chevron-right"></i></button>
         <button class="btn-nav" id="art_bugun">Bugün</button>
         <span class="art-gun-baslik" id="art_gun_baslik"></span>
         <span class="art-say" id="art_say"></span>
      </div>

      <div class="art-legend">
         <span class="lg"><span class="dot" style="background:#2563eb;"></span> Planlı</span>
         <span class="lg"><span class="dot" style="background:#dc2626;"></span> Geciken (aranmadı)</span>
         <span class="lg"><span class="dot" style="background:#16a34a;"></span> Zamanında arandı</span>
         <span class="lg"><span class="dot" style="background:#f59e0b;"></span> Geç arandı</span>
      </div>

      <div id="art_board"></div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

<script>
$(document).ready(function(){
   var sube = $('input[name="sube"]').val();
   var AYLAR = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
   var GUNLER = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];

   function esc(s){ return $('<div>').text(s==null?'':s).html(); }

   function gunBaslik(d){
      var p = d.split('-'); // YYYY-MM-DD
      var dt = new Date(parseInt(p[0]), parseInt(p[1])-1, parseInt(p[2]));
      return parseInt(p[2],10) + ' ' + AYLAR[dt.getMonth()] + ' ' + p[0] + ', ' + GUNLER[dt.getDay()];
   }
   function sonrakiGun(d, adim){
      var p = d.split('-');
      var dt = new Date(parseInt(p[0]), parseInt(p[1])-1, parseInt(p[2]));
      dt.setDate(dt.getDate()+adim);
      var m = ('0'+(dt.getMonth()+1)).slice(-2), g = ('0'+dt.getDate()).slice(-2);
      return dt.getFullYear()+'-'+m+'-'+g;
   }

   function yukle(){
      var gun = $('#art_tarih').val();
      if(!gun){ return; }
      $('#art_gun_baslik').text(gunBaslik(gun));
      $('#art_say').text('');
      $('#art_board').html('<div class="art-spin"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

      $.get('/isletmeyonetim/arama-randevu-takvim-verileri', {
         sube: sube,
         start: gun,
         end: gun   // tek gun (endpoint whereBetween KAPSAYICI -> ayni gun)
      }, function(res){
         var olaylar = res || [];
         if(!olaylar.length){
            $('#art_board').html('<div class="art-bos"><i class="fa fa-calendar-o"></i>Bu güne ait arama randevusu yok.</div>');
            $('#art_say').text('0 arama');
            return;
         }

         // Personele gore grupla
         var grup = {};
         olaylar.forEach(function(e){
            var k = e.personel_id || 0;
            if(!grup[k]) grup[k] = { ad: e.personel || 'Personel', items: [] };
            grup[k].items.push(e);
         });

         // Personel adina gore sirala
         var pidler = Object.keys(grup).sort(function(a,b){
            return grup[a].ad.localeCompare(grup[b].ad, 'tr');
         });

         var html = '<div class="art-board">';
         pidler.forEach(function(pid){
            var g = grup[pid];
            // Saate gore sirala
            g.items.sort(function(a,b){ return (a.start||'').localeCompare(b.start||''); });
            html += '<div class="art-col">';
            html +=   '<div class="art-col-head"><span>'+esc(g.ad)+'</span><span class="adet">'+g.items.length+'</span></div>';
            html +=   '<div class="art-col-body">';
            g.items.forEach(function(e){
               var saat = (e.start && e.start.length>=16) ? e.start.substr(11,5) : '';
               html += '<div class="art-item" style="border-left-color:'+esc(e.color)+'" '+
                        'data-pid="'+esc(e.personel_id)+'" '+
                        'data-musteri="'+esc(e.musteri)+'" '+
                        'data-personel="'+esc(e.personel)+'" '+
                        'data-zaman="'+esc(saat)+'" '+
                        'data-durum="'+esc(e.durum_metin)+'">'+
                        '<div class="saat"><i class="fa fa-clock-o" style="color:'+esc(e.color)+'"></i> '+esc(saat)+'</div>'+
                        '<div class="mus">'+esc(e.musteri)+'</div>'+
                        '<span class="durum" style="background:'+esc(e.color)+'1a;color:'+esc(e.color)+'">'+esc(e.durum_metin)+'</span>'+
                     '</div>';
            });
            html +=   '</div>';
            html += '</div>';
         });
         html += '</div>';
         $('#art_board').html(html);
         $('#art_say').text(olaylar.length + ' arama · ' + pidler.length + ' personel');
      }).fail(function(){
         $('#art_board').html('<div class="art-bos" style="color:#c62828;"><i class="fa fa-exclamation-triangle"></i>Yüklenirken hata oluştu.</div>');
      });
   }

   // Bir aramaya tiklayinca personel detayina git
   $(document).on('click', '#art_board .art-item', function(){
      var pid = $(this).data('pid');
      var metin = '👤 Personel: ' + ($(this).data('personel')||'-') + '\n'
                + '📞 Müşteri: ' + ($(this).data('musteri')||'-') + '\n'
                + '🕒 Saat: ' + ($(this).data('zaman')||'-') + '\n'
                + '📌 Durum: ' + ($(this).data('durum')||'-');
      var git = function(){ window.location.href = '/isletmeyonetim/arama-dashboard?sube=' + sube + '&acpersonel=' + pid; };
      if (typeof swal === 'function'){
         var r = swal({ title:'Arama Randevusu', text:metin, showCancelButton:true,
            confirmButtonText:'Personel Detayı', cancelButtonText:'Kapat' },
            function(onay){ if(onay) git(); });
         if (r && typeof r.then === 'function'){ r.then(function(x){ if(x===true||(x&&x.value)) git(); }).catch(function(){}); }
      } else {
         if (confirm(metin + '\n\nPersonel detayına gidilsin mi?')) git();
      }
   });

   $('#art_prev').on('click', function(){ $('#art_tarih').val(sonrakiGun($('#art_tarih').val(), -1)); yukle(); });
   $('#art_next').on('click', function(){ $('#art_tarih').val(sonrakiGun($('#art_tarih').val(), 1)); yukle(); });
   $('#art_bugun').on('click', function(){ $('#art_tarih').val('{{ date('Y-m-d') }}'); yukle(); });
   $('#art_tarih').on('change', yukle);

   yukle();
});
</script>
@endsection
