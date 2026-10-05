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

.art-legend{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; font-size:12.5px; color:#5a5570; }
.art-legend .lg{ display:inline-flex; align-items:center; gap:6px; }
.art-legend .dot{ width:12px; height:12px; border-radius:3px; display:inline-block; }
/* Tiklanabilir durum filtreleri */
.art-legend .flt{ cursor:pointer; padding:6px 12px; border-radius:20px; border:1px solid #ece7f6; background:#fff; transition:all .12s; user-select:none; }
.art-legend .flt:hover{ border-color:#8b5cf6; }
.art-legend .flt.aktif{ background:#f1edfb; border-color:#8b5cf6; color:#6d28d9; font-weight:700; }
.art-legend .cnt{ background:#eef0f5; color:#5b6172; border-radius:20px; padding:1px 8px; font-size:11.5px; margin-left:4px; font-weight:700; }
.art-legend .flt.aktif .cnt{ background:#8b5cf6; color:#fff; }

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

/* Arama Randevusu detay modali (cockpit) */
#artm_modal .modal-header{ align-items:center; gap:12px; background:linear-gradient(120deg,#5C008E,#7B2FB8); border-top-left-radius:16px; border-top-right-radius:16px; border-bottom:none; }
#artm_modal .modal-title-wrap h5{ margin:0; font-weight:800; color:#fff; font-size:17px; }
#artm_modal .modal-title-wrap small{ color:rgba(255,255,255,.88); }
#artm_modal .modal-header .close{ color:#fff; opacity:.9; text-shadow:none; }
#artm_modal .modal-header .close:hover{ opacity:1; }
.artm-ara-btn{ background:#16a34a; border:none; color:#fff; font-weight:800; border-radius:12px; padding:10px 18px; font-size:15px; margin-left:auto; cursor:pointer; }
.artm-ara-btn:hover{ filter:brightness(.96); }
.artm-ara-btn[disabled]{ opacity:.6; cursor:default; }
.artm-kart-bas{ font-weight:700; color:#5C008E; font-size:13px; margin:6px 0 10px; display:flex; align-items:center; gap:7px; }
.artm-sonuc-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
.artm-donusum-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:8px; margin-top:8px; }
.artm-sonuc{ border:1px solid #e7e9f0; border-radius:12px; padding:11px 8px; text-align:center; cursor:pointer; font-size:12.5px; font-weight:700; color:#5b6172; transition:all .12s; }
.artm-sonuc i{ display:block; font-size:16px; margin-bottom:4px; }
.artm-sonuc:hover{ border-color:#8b5cf6; }
.artm-sonuc.aktif{ color:#fff; border-color:transparent; }
.artm-sonuc.sec-yesil.aktif{ background:#16a34a; } .artm-sonuc.sec-kirmizi.aktif{ background:#dc2626; }
.artm-sonuc.sec-koyukirmizi.aktif{ background:#991b1b; } .artm-sonuc.sec-turuncu.aktif{ background:#f59e0b; }
.artm-sonuc.sec-mavi.aktif{ background:#2563eb; } .artm-sonuc.sec-altin.aktif{ background:#b8860b; }
.artm-alan{ width:100%; border:1px solid #e7e9f0; border-radius:12px; padding:10px 12px; font-size:13.5px; outline:none; margin-top:12px; }
textarea.artm-alan{ min-height:70px; resize:vertical; }
.artm-sonra{ display:flex; align-items:center; gap:8px; margin-top:12px; font-size:13px; font-weight:600; color:#4a4461; cursor:pointer; }
.artm-sonra-alan{ display:none; gap:10px; flex-wrap:wrap; margin-top:10px; background:#faf7ff; border:1px solid #e3d5f5; border-radius:12px; padding:12px; }
.artm-sonra-alan input{ flex:1; min-width:130px; border:1px solid #d9c9f0; border-radius:10px; padding:9px 11px; font-size:14px; }
.artm-kaydet{ width:100%; margin-top:14px; background:linear-gradient(120deg,#6d28d9,#7c3aed); border:none; color:#fff; font-weight:800; border-radius:12px; padding:12px; cursor:pointer; font-size:14px; }
.artm-kaydet:hover{ filter:brightness(.97); }
#artm_gecmis .artm-g-item{ border-left:4px solid #c9c2de; background:#faf9fe; border:1px solid #eef0f5; border-radius:10px; padding:9px 11px; margin-top:8px; }
#artm_gecmis audio{ width:100%; height:34px; margin-top:8px; }
.artm-g-bos{ text-align:center; color:#a9a3bb; padding:18px; font-size:12.5px; }
</style>

<div class="art-wrap">
   <div class="art-hero">
      <h4><i class="fa fa-calendar-check-o"></i> {{ $sayfa_baslik }}</h4>
      <p>Seçtiğiniz güne ait arama randevularını <b>personele göre</b> sütunlar halinde görün; her sütunda aramalar <b>saatine göre</b> sıralıdır. Bir aramaya tıklayınca <b>arama yapıp sonucu kaydedebilir</b>, geçmiş görüşmeleri ve ses kayıtlarını görebilirsiniz.</p>
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

      <div class="art-legend" id="art_legend">
         <span class="lg flt aktif" data-renk=""><span class="dot" style="background:#9a93ad;"></span> Tümü <span class="cnt" id="cnt_all">0</span></span>
         <span class="lg flt" data-renk="#2563eb"><span class="dot" style="background:#2563eb;"></span> Aranacak <span class="cnt" id="cnt_aranacak">0</span></span>
         <span class="lg flt" data-renk="#dc2626"><span class="dot" style="background:#dc2626;"></span> Geciken (aranmadı) <span class="cnt" id="cnt_gecikti">0</span></span>
         <span class="lg flt" data-renk="#16a34a"><span class="dot" style="background:#16a34a;"></span> Zamanında arandı <span class="cnt" id="cnt_zamaninda">0</span></span>
         <span class="lg flt" data-renk="#f59e0b"><span class="dot" style="background:#f59e0b;"></span> Geç arandı <span class="cnt" id="cnt_gec">0</span></span>
         <span class="lg flt" data-renk="#7c3aed"><span class="dot" style="background:#7c3aed;"></span> Ön Görüşme <span class="cnt" id="cnt_ongorusme">0</span></span>
         <span class="lg flt" data-renk="#b8860b"><span class="dot" style="background:#b8860b;"></span> Satış <span class="cnt" id="cnt_satis">0</span></span>
      </div>

      <div id="art_board"></div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">
<input type="hidden" name="_token" value="{{ csrf_token() }}">

{{-- Arama Randevusu detay modali: takvimden ARA + sonuc kaydetme (cockpit) --}}
<div class="modal fade" id="artm_modal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content" style="border-radius:16px;">
         <div class="modal-header">
            <div class="modal-title-wrap">
               <h5 id="artm_ad">Müşteri</h5>
               <small><i class="fa fa-phone"></i> <span id="artm_tel">gizli</span> &middot; <span id="artm_zaman">-</span> &middot; <i class="fa fa-user"></i> <span id="artm_personel">-</span></small>
            </div>
            <button type="button" class="artm-ara-btn" id="artm_ara"><i class="fa fa-phone"></i> ARA</button>
            <button type="button" class="close" data-dismiss="modal" aria-label="Kapat"><span aria-hidden="true">&times;</span></button>
         </div>
         <div class="modal-body">
            <div class="artm-kart-bas"><i class="fa fa-check-circle"></i> Görüşme Sonucu</div>
            <div class="artm-sonuc-grid">
               <div class="artm-sonuc sec-yesil" data-sonuc="4"><i class="fa fa-phone"></i>Görüşüldü</div>
               <div class="artm-sonuc sec-kirmizi" data-sonuc="2"><i class="fa fa-phone-square"></i>Cevapsız</div>
               <div class="artm-sonuc sec-koyukirmizi" data-sonuc="5"><i class="fa fa-ban"></i>Meşgul</div>
               <div class="artm-sonuc sec-turuncu" data-sonuc="0"><i class="fa fa-volume-off"></i>Ulaşılamadı</div>
            </div>
            <div class="artm-kart-bas" style="margin-top:12px;">🎯 Sonuç (hedef)</div>
            <div class="artm-donusum-grid">
               <div class="artm-sonuc sec-mavi" data-sonuc="6"><i class="fa fa-calendar-check-o"></i>Ön Görüşme Randevusu</div>
               <div class="artm-sonuc sec-altin" data-sonuc="7"><i class="fa fa-shopping-bag"></i>Telefonda Satış</div>
            </div>
            <textarea class="artm-alan" id="artm_not" placeholder="Görüşme notu (müşteri ne dedi, talep, vb.)..."></textarea>
            <div id="artm_satis_alan" style="display:none;background:#fffaf0;border:1px solid #f0dcb0;border-radius:12px;padding:12px;margin-top:12px;">
               <div style="font-size:12.5px;font-weight:700;color:#b26a00;margin-bottom:6px;"><i class="fa fa-shopping-bag"></i> Telefonda Satış tutarı (₺)</div>
               <input type="number" min="0" step="0.01" class="artm-alan" id="artm_satis_tutari" placeholder="örn: 1500" style="margin-top:0;">
               <div style="font-size:11.5px;color:#8a8398;margin-top:6px;"><i class="fa fa-info-circle"></i> Sadece görüşme kaydına yazılır (satış notu + tutar). Kasaya / tahsilata <b>otomatik işlenmez</b> — tahsilat için satışı ayrıca kaydedin.</div>
            </div>
            <label class="artm-sonra"><input type="checkbox" id="artm_sonra_chk"> 📅 ARAMA RANDEVUSU VER — müşteri sonra aranmak istedi (Tekrar Aranacak)</label>
            <div class="artm-sonra-alan" id="artm_sonra_alan">
               <input type="text" id="artm_sonra_tarih" autocomplete="off" readonly placeholder="Tarih seçin">
               <input type="time" id="artm_sonra_saat">
            </div>
            <button class="artm-kaydet" id="artm_kaydet"><i class="fa fa-save"></i> Sonucu Kaydet</button>

            <hr style="margin:18px 0 10px;">
            <div class="artm-kart-bas" style="justify-content:space-between;">
               <span><i class="fa fa-history"></i> Çağrı Geçmişi &amp; Ses Kayıtları</span>
               <button type="button" class="btn-nav" id="artm_gecmis_yenile" style="padding:4px 10px;font-size:12px;"><i class="fa fa-refresh"></i> Yenile</button>
            </div>
            <div id="artm_gecmis"></div>
         </div>
      </div>
   </div>
</div>

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

   var artOlaylar = [];   // o gune ait tum aramalar
   var artFiltre  = '';   // '' = tumu; '#2563eb' / '#dc2626' / '#16a34a' / '#f59e0b'

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
         artOlaylar = res || [];
         sayilariGuncelle();
         ciz();
      }).fail(function(){
         $('#art_board').html('<div class="art-bos" style="color:#c62828;"><i class="fa fa-exclamation-triangle"></i>Yüklenirken hata oluştu.</div>');
      });
   }

   // Durum (renk) bazli musteri sayilarini legend'e yaz
   function sayilariGuncelle(){
      var c = {'#2563eb':0,'#dc2626':0,'#16a34a':0,'#f59e0b':0,'#7c3aed':0,'#b8860b':0};
      artOlaylar.forEach(function(e){ if(c[e.color]!==undefined) c[e.color]++; });
      $('#cnt_all').text(artOlaylar.length);
      $('#cnt_aranacak').text(c['#2563eb']);
      $('#cnt_gecikti').text(c['#dc2626']);
      $('#cnt_zamaninda').text(c['#16a34a']);
      $('#cnt_gec').text(c['#f59e0b']);
      $('#cnt_ongorusme').text(c['#7c3aed']);
      $('#cnt_satis').text(c['#b8860b']);
   }

   // Aktif filtreye gore board'u ciz
   function ciz(){
      if(!artOlaylar.length){
         $('#art_board').html('<div class="art-bos"><i class="fa fa-calendar-o"></i>Bu güne ait arama randevusu yok.</div>');
         $('#art_say').text('0 arama');
         return;
      }
      var liste = artFiltre ? artOlaylar.filter(function(e){ return e.color === artFiltre; }) : artOlaylar;
      if(!liste.length){
         $('#art_board').html('<div class="art-bos"><i class="fa fa-filter"></i>Bu filtreye uygun arama yok.</div>');
         $('#art_say').text('0 / ' + artOlaylar.length + ' arama');
         return;
      }

      // Personele gore grupla
      var grup = {};
      liste.forEach(function(e){
         var k = e.personel_id || 0;
         if(!grup[k]) grup[k] = { ad: e.personel || 'Personel', items: [] };
         grup[k].items.push(e);
      });
      var pidler = Object.keys(grup).sort(function(a,b){ return grup[a].ad.localeCompare(grup[b].ad, 'tr'); });

      var html = '<div class="art-board">';
      pidler.forEach(function(pid){
         var g = grup[pid];
         g.items.sort(function(a,b){ return (a.start||'').localeCompare(b.start||''); });
         html += '<div class="art-col">';
         html +=   '<div class="art-col-head"><span>'+esc(g.ad)+'</span><span class="adet">'+g.items.length+'</span></div>';
         html +=   '<div class="art-col-body">';
         g.items.forEach(function(e){
            var saat = (e.start && e.start.length>=16) ? e.start.substr(11,5) : '';
            html += '<div class="art-item" style="border-left-color:'+esc(e.color)+'" '+
                     'data-id="'+esc(e.id)+'" '+
                     'data-arama="'+esc(e.arama_id)+'" '+
                     'data-pid="'+esc(e.personel_id)+'" '+
                     'data-musteri="'+esc(e.musteri)+'" '+
                     'data-personel="'+esc(e.personel)+'" '+
                     'data-zaman="'+esc(saat)+'" '+
                     'data-not="'+esc(e.not||'')+'" '+
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
      $('#art_say').text((artFiltre ? (liste.length + ' / ' + artOlaylar.length) : artOlaylar.length) + ' arama · ' + pidler.length + ' personel');
   }

   // Legend durum filtresi
   $(document).on('click', '#art_legend .flt', function(){
      artFiltre = $(this).attr('data-renk') || '';
      $('#art_legend .flt').removeClass('aktif');
      $(this).addClass('aktif');
      ciz();
   });

   // ================= Arama Randevusu detay modali (ARA + sonuc kaydetme) =================
   var token = $('input[name="_token"]').val();
   var artmAmId = null, artmAramaId = null, artmSonuc = null;

   function artmDurStil(kod){
      switch(parseInt(kod,10)){
         case 4: return {et:'Görüşüldü', c:'#16a34a'};
         case 2: return {et:'Cevapsız', c:'#dc2626'};
         case 5: return {et:'Meşgul', c:'#991b1b'};
         case 0: return {et:'Ulaşılamadı', c:'#f59e0b'};
         case 6: return {et:'Ön Görüşme Randevusu', c:'#2563eb'};
         case 7: return {et:'Telefonda Satış', c:'#b8860b'};
         case 3: return {et:'Tekrar Aranacak', c:'#2563eb'};
         default: return {et:'—', c:'#9a93ad'};
      }
   }

   // Bir aramaya tiklayinca cockpit modali ac
   $(document).on('click', '#art_board .art-item', function(){
      artmAmId   = $(this).data('id');
      artmAramaId= $(this).data('arama');
      artmSonuc  = null;
      $('#artm_ad').text($(this).data('musteri') || 'Müşteri');
      $('#artm_tel').text('gizli');
      $('#artm_zaman').text($(this).data('zaman') || '-');
      $('#artm_personel').text($(this).data('personel') || '-');
      // reset form — mevcut/aktarilan notu goster (duzenlenebilir; kaydedince guncellenir)
      $('.artm-sonuc').removeClass('aktif');
      $('#artm_not').val($(this).attr('data-not') || '');
      $('#artm_satis_alan').hide(); $('#artm_satis_tutari').val('');
      $('#artm_sonra_chk').prop('checked', false);
      $('#artm_sonra_alan').hide();
      $('#artm_sonra_tarih').val(''); $('#artm_sonra_saat').val('');
      $('#artm_ara').prop('disabled', false).html('<i class="fa fa-phone"></i> ARA');
      $('#artm_gecmis').html('<div class="artm-g-bos"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
      // tarih secici (varsa air-datepicker, sadece ileri tarih)
      try {
         if ($.fn.datepicker) {
            $('#artm_sonra_tarih').datepicker({ minDate:new Date(), language:'tr', autoClose:true, dateFormat:'yyyy-mm-dd' });
         }
      } catch(e){}
      artmGecmisYukle();
      $('#artm_modal').modal();
   });

   // Gorusme sonucu sec
   $(document).on('click', '#artm_modal .artm-sonuc', function(){
      var s = parseInt($(this).data('sonuc'),10);
      if (artmSonuc === s){ artmSonuc = null; $(this).removeClass('aktif'); }
      else { artmSonuc = s; $('.artm-sonuc').removeClass('aktif'); $(this).addClass('aktif'); }
      $('#artm_satis_alan').toggle(artmSonuc === 7);
      // On Gorusme Randevusu(6) -> aramayi HEMEN durum=6 isaretle + gercek randevu modalini ac
      if (artmSonuc === 6){ artmOnGorusmeKaydetVeAc(); }
   });

   // On Gorusme secilince: aramayi DOGRUDAN durum=6 isaretle (Sonucu Kaydet gerekmesin;
   // hook'a/zamanlamaya bagli degil), sonra gercek randevu modalini prefill'li ac.
   function artmOnGorusmeKaydetVeAc(){
      if (artmAmId && artmAramaId){
         $.post('/isletmeyonetim/santral_not_ekle',
            { arama_detay_id:artmAramaId, aranacak_musteri_id:artmAmId, noticerik:($('#artm_not').val()||''), sonuc:6, _token:token },
            function(r){ if(r && r.success){ yukle(); } }); // board'u tazele -> 'On Gorusme' gorunsun
      }
      artmOnGorusmeAc();
   }

   // On Gorusme: gercek randevu modalini musteri prefill'li acar (calisma ekrani ile ayni uc).
   // #ongorusme-modal layout'ta global include'lu, takvim sayfasinda da mevcut.
   function artmOnGorusmeAc(){
      if (!artmAmId) return;
      $.post('/isletmeyonetim/cagri-musteri-ongorusme-bilgi',
         { aranacak_musteri_id: artmAmId, _token: token },
         function(res){
            if (res && res.success){
               try {
                  var $sel = $('#musteri_select_list');
                  if ($sel.length){
                     if ($sel.find('option[value="'+res.user_id+'"]').length===0){
                        $sel.append(new Option(res.ad || ('#'+res.user_id), res.user_id, true, true));
                     } else { $sel.val(res.user_id); }
                     $sel.trigger('change');
                  }
                  if (res.ad){ $('#ad_soyad').val(res.ad); }
                  if (res.telefon){ $('#telefon').val(res.telefon); }
               } catch(e){}
               $('#ongorusme-modal').modal('show'); // cockpit uzerine acilir (stacked)
            } else if (typeof swal==='function'){
               swal({ type:'warning', title:'Açılamadı', text:(res&&res.message)||'Müşteri bilgisi alınamadı.' });
            }
         }
      ).fail(function(){ if(typeof swal==='function') swal({ type:'error', title:'Hata', text:'Ön görüşme ekranı açılamadı.' }); });
   }

   // Stacked modal (ongorusme-modal, cockpit uzerine) z-index + backdrop fix
   $(document).on('show.bs.modal', '.modal', function(){
      var acikSayi = $('.modal:visible').length;
      if (acikSayi >= 1){
         var z = 1050 + (acikSayi * 20);
         $(this).css('z-index', z);
         setTimeout(function(){ $('.modal-backdrop').not('.artm-stacked').last().css('z-index', z-10).addClass('artm-stacked'); }, 0);
      }
   });
   $(document).on('hidden.bs.modal', '.modal', function(){
      if ($('.modal:visible').length){ $('body').addClass('modal-open'); } // ust modal kapaninca alttaki scroll'u koru
   });

   // Tekrar aranacak toggle
   $(document).on('change', '#artm_sonra_chk', function(){
      $('#artm_sonra_alan').css('display', this.checked ? 'flex' : 'none');
   });

   // ARA — softphone hazirsa dogrudan, degilse santral originate (arama_listelerim ile ayni)
   $(document).on('click', '#artm_ara', function(){
      if (!artmAmId) return;
      var $btn = $(this);
      var softphoneHazir = (window.webphoneHazir === true && typeof window.webphoneAra === 'function');
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Bağlanıyor...');
      $.ajax({ url:'/isletmeyonetim/arama-baslat', method:'POST',
         data:{ aranacak_musteri_id:artmAmId, softphone: softphoneHazir ? 1 : 0, sube:sube, _token:token },
         success:function(res){
            if (res && res.success){
               if (res.softphone && res.numara_ara){
                  var ok = window.webphoneAra(res.numara_ara);
                  if (!ok && typeof swal==='function'){ swal({type:'warning', title:'Softphone hazır değil', text:'Üst köşedeki telefonun "Bağlandı" olduğundan emin olun.'}); }
               } else if (typeof swal==='function'){
                  swal({type:'success', title:'Arama başlatıldı', text:res.message||'Telefonunuz çalacak.', timer:3000, showConfirmButton:false});
               }
            } else if (typeof swal==='function'){
               swal({type:'warning', title:'Aranamadı', text:(res&&res.message)||'Arama başlatılamadı.'});
            }
            $btn.prop('disabled', false).html('<i class="fa fa-phone"></i> ARA');
         },
         error:function(xhr){
            $btn.prop('disabled', false).html('<i class="fa fa-phone"></i> ARA');
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Arama başlatılamadı.';
            if (typeof swal==='function') swal({type:'error', title:'Hata', text:msg});
         }
      });
   });

   // Sonucu kaydet
   $(document).on('click', '#artm_kaydet', function(){
      if (!artmAmId || !artmAramaId) return;
      var sonraMu = $('#artm_sonra_chk').is(':checked');
      var satisMi = (artmSonuc === 7);
      var tarih = $('#artm_sonra_tarih').val();
      var saat  = $('#artm_sonra_saat').val();
      var not   = $('#artm_not').val();
      var satisTutari = satisMi ? ($('#artm_satis_tutari').val()||'') : '';

      if (sonraMu && (!tarih || !saat)){ if(typeof swal==='function') swal({type:'warning', title:'Tarih/saat seçin', text:'Tekrar arama için tarih ve saat seçin.'}); return; }
      if (satisMi && (!satisTutari || parseFloat(satisTutari) <= 0)){ if(typeof swal==='function') swal({type:'warning', title:'Satış tutarı', text:'Telefonda satış için tutar girin.'}); return; }
      if (!sonraMu && artmSonuc===null && !(not||'').trim()){ if(typeof swal==='function') swal({type:'warning', title:'Sonuç seçin', text:'Bir sonuç seçin, tekrar arama oluşturun ya da not yazın.'}); return; }

      // Satis secildiyse ve not bos ise otomatik satis notu (tutar ile). Kasaya islemez, sadece kayit.
      if (satisMi && !(not||'').trim()){ not = 'Telefonda satış: ' + satisTutari + ' ₺'; }

      var $btn = $(this); $btn.prop('disabled', true);
      $.ajax({ url:'/isletmeyonetim/santral_not_ekle', method:'POST',
         data:{
            arama_detay_id: artmAramaId,
            aranacak_musteri_id: artmAmId,
            noticerik: not,
            sonuc: sonraMu ? '' : (artmSonuc===null ? '' : artmSonuc),
            satis_tutari: satisMi ? satisTutari : '',
            santralnottarih: sonraMu ? tarih : '',
            santralnotsaat:  sonraMu ? saat  : '',
            _token: token
         },
         success:function(res){
            if (res && res.success){
               if (typeof swal==='function') swal({type:'success', title:'Kaydedildi', timer:1300, showConfirmButton:false});
               $('#artm_modal').modal('hide');
               yukle(); // takvimi tazele (renk/durum guncellensin)
            } else if (typeof swal==='function'){
               swal({type:'error', title:'Hata', text:(res&&res.message)||'Kaydedilemedi.'});
            }
            $btn.prop('disabled', false);
         },
         error:function(){ $btn.prop('disabled', false); if(typeof swal==='function') swal({type:'error', title:'Hata', text:'Kaydedilirken sorun oluştu.'}); }
      });
   });

   // Cagri gecmisi + ses kayitlari
   function artmGecmisYukle(){
      $.ajax({ url:'/isletmeyonetim/cagri-musteri-gecmisi', method:'POST',
         data:{ aranacak_musteri_id:artmAmId, _token:token },
         success:function(res){
            var g = (res && res.gecmis) ? res.gecmis : [];
            if (!g.length){ $('#artm_gecmis').html('<div class="artm-g-bos"><i class="fa fa-microphone-slash"></i> Bu müşteriyle henüz görüşme kaydedilmedi.</div>'); return; }
            var html = '';
            g.forEach(function(x){
               var st = artmDurStil(x.sonuc_kod);
               var ses = x.ses ? '<audio controls preload="none" src="'+esc(x.ses)+'"></audio>'
                               : '<div style="font-size:11.5px;color:#aab0c0;margin-top:6px;"><i class="fa fa-hourglass-half"></i> Ses kaydı işleniyor — "Yenile"ye basın.</div>';
               var rnd = (x.randevu_tarih) ? ' <span style="color:#1565c0;"><i class="fa fa-calendar-check-o"></i> '+esc(x.randevu_tarih)+(x.randevu_saat?(' '+esc(x.randevu_saat)):'')+'</span>' : '';
               var sat = (x.satis_tutari) ? ' <span style="color:#b8860b;"><i class="fa fa-shopping-bag"></i> '+esc(x.satis_tutari)+' ₺</span>' : '';
               html += '<div class="artm-g-item" style="border-left-color:'+st.c+'">'+
                        '<div style="font-size:12.5px;"><i class="fa fa-clock-o"></i> '+esc(x.tarih)+' &middot; <b style="color:'+st.c+'">'+esc(st.et)+'</b>'+rnd+sat+'</div>'+
                        (x.not ? '<div style="font-size:12.5px;color:#574f6b;margin-top:5px;"><i class="fa fa-sticky-note-o"></i> '+esc(x.not)+'</div>' : '')+
                        ses+
                     '</div>';
            });
            $('#artm_gecmis').html(html);
         },
         error:function(){ $('#artm_gecmis').html('<div class="artm-g-bos" style="color:#c62828;">Geçmiş yüklenemedi.</div>'); }
      });
   }
   $(document).on('click', '#artm_gecmis_yenile', function(){ if(artmAmId){ $('#artm_gecmis').html('<div class="artm-g-bos"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>'); artmGecmisYukle(); } });

   $('#art_prev').on('click', function(){ $('#art_tarih').val(sonrakiGun($('#art_tarih').val(), -1)); yukle(); });
   $('#art_next').on('click', function(){ $('#art_tarih').val(sonrakiGun($('#art_tarih').val(), 1)); yukle(); });
   $('#art_bugun').on('click', function(){ $('#art_tarih').val('{{ date('Y-m-d') }}'); yukle(); });
   $('#art_tarih').on('change', yukle);

   yukle();
});
</script>
@endsection
