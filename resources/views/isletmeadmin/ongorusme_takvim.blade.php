@extends('layout.layout_isletmeadmin')
@section('content')

@php
   $_ogtPersoneller = $personeller->map(function ($p) {
      return ['id' => (int) $p->id, 'ad' => $p->personel_adi];
   })->values()->all();
@endphp

<style>
/* =================================================================
   ÖN GÖRÜŞME TAKVİMİ (ay / hafta / gün)
   ================================================================= */
.ogt { --mor:#5C008E; --mor2:#9D5DC8; --mor-acik:#f5eefe; --yazi:#1f2937; --soluk:#6b7280; --cizgi:#eceaf2; --saat-h:60px; }
.ogt *{ box-sizing:border-box; }
.ogt-ust, .ogt-panel{ background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(17,24,39,.04),0 6px 24px rgba(92,0,142,.05); }
.ogt-ust{ display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; padding:16px 20px; margin-bottom:16px; }
.ogt-baslik{ display:flex; align-items:center; gap:13px; min-width:0; }
.ogt-ikon{ width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg,#5C008E,#9D5DC8); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:19px; flex-shrink:0; }
.ogt-baslik h1{ margin:0; font-size:19px; font-weight:700; color:var(--yazi); line-height:1.2; }
.ogt-baslik nav{ margin-top:3px; font-size:12.5px; color:var(--soluk); }
.ogt-baslik nav a{ color:var(--soluk); }
.ogt-baslik nav b{ color:var(--mor); font-weight:600; }
.ogt-ust-sag{ display:flex; gap:8px; flex-wrap:wrap; }
.ogt-btn{ display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border-radius:999px; font-size:13.5px; font-weight:600; border:1px solid #e3d5f5; background:#fff; color:#5C008E !important; cursor:pointer; text-decoration:none !important; white-space:nowrap; }
.ogt-btn:hover{ background:#f5eefe; }
.ogt-btn-yesil{ background:#15803d; border-color:#15803d; color:#fff !important; }
.ogt-btn-yesil:hover{ background:#166534; }

.ogt-panel{ padding:16px 18px 18px; margin-bottom:30px; }
.ogt-arac{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.ogt-gez{ display:inline-flex; gap:6px; align-items:center; }
.ogt-gez button{ height:38px; min-width:38px; padding:0 12px; border:1px solid #e3d5f5; background:#faf7ff; color:#5C008E; font-weight:700; border-radius:10px; cursor:pointer; font-size:13px; }
.ogt-gez button:hover{ background:#f1e9fb; }
#ogt_tarih{ height:38px; width:118px; border:1px solid #d9c9f0; border-radius:10px; text-align:center; font-size:13.5px; cursor:pointer; background:#fff; color:var(--yazi); }
.ogt-aralik{ font-weight:800; color:#2b1b45; font-size:16px; }
.ogt-sag{ margin-left:auto; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
#ogt_personel{ height:38px; border:1px solid #d9c9f0; border-radius:10px; padding:0 10px; font-size:13.5px; background:#fff; color:var(--yazi); max-width:210px; }
.ogt-gorunum{ display:inline-flex; background:#f3eefa; border-radius:11px; padding:3px; }
.ogt-gorunum button{ border:0; background:transparent; height:32px; padding:0 15px; border-radius:9px; font-size:13px; font-weight:700; color:#6b5b7b; cursor:pointer; }
.ogt-gorunum button.aktif{ background:#5C008E; color:#fff; }

.ogt-filtre{ display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
.ogt-filtre button{ display:inline-flex; align-items:center; gap:7px; border:1px solid #ece7f6; background:#fff; color:#5a5570; border-radius:20px; padding:6px 12px; font-size:12.5px; cursor:pointer; }
.ogt-filtre button i{ width:11px; height:11px; border-radius:3px; display:inline-block; }
.ogt-filtre button b{ background:#eef0f5; color:#5b6172; border-radius:20px; padding:1px 8px; font-size:11.5px; }
.ogt-filtre button.aktif{ background:#f1edfb; border-color:#8b5cf6; color:#6d28d9; font-weight:700; }
.ogt-filtre button.aktif b{ background:#6d28d9; color:#fff; }

.ogt-alan{ position:relative; min-height:220px; }
.ogt-alan.yukleniyor{ opacity:.45; pointer-events:none; }
.ogt-hata{ padding:40px 16px; text-align:center; color:#b91c1c; font-size:14px; }
.ogt-bos{ padding:10px 14px; margin-bottom:10px; background:#faf7ff; border-radius:10px; color:#6b5b7b; font-size:13px; }

/* --- Durum renkleri --- */
.ogt-e{ --r:#b45309; --z:#fef3c7; --k:#f59e0b; }
.ogt-e.satildi{ --r:#166534; --z:#dcfce7; --k:#16a34a; }
.ogt-e.satilmadi{ --r:#991b1b; --z:#fee2e2; --k:#dc2626; }
.ogt-e.iptal{ --r:#4b5563; --z:#f1f2f5; --k:#9ca3af; }
.ogt-e{ background:var(--z); color:var(--r); border-left:4px solid var(--k); border-radius:7px; cursor:pointer; overflow:hidden; }
.ogt-e.iptal .ad{ text-decoration:line-through; }
.ogt-e:hover{ filter:brightness(.97); box-shadow:0 4px 12px -5px rgba(30,30,60,.35); }
.ogt-e:focus{ outline:2px solid #5C008E; outline-offset:1px; }

/* --- AY --- */
.ogt-ay{ border:1px solid var(--cizgi); border-radius:12px; overflow:hidden; }
.ogt-ay-bas, .ogt-ay-satir{ display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); }
.ogt-ay-bas div{ background:var(--mor-acik); color:var(--mor); font-size:11.5px; font-weight:700; padding:10px 8px; text-align:center; }
.ogt-gun{ min-height:118px; border-top:1px solid var(--cizgi); border-left:1px solid var(--cizgi); padding:5px; display:flex; flex-direction:column; gap:3px; min-width:0; background:#fff; }
.ogt-gun:first-child{ border-left:0; }
.ogt-gun.disi{ background:#fafafc; }
.ogt-gun.disi .ogt-gno{ color:#b6b3c2; }
.ogt-gun.ekle{ cursor:pointer; }
.ogt-gun.ekle:hover{ background:#fcfaff; }
.ogt-gno{ align-self:flex-end; border:0; background:transparent; width:27px; height:27px; border-radius:50%; font-size:13px; font-weight:700; color:#4a4461; cursor:pointer; padding:0; }
.ogt-gno:hover{ background:#ead4ff; }
.ogt-gun.bugun .ogt-gno{ background:#5C008E; color:#fff; }
.ogt-gun .ogt-e{ font-size:12px; padding:3px 6px; white-space:nowrap; text-overflow:ellipsis; line-height:1.35; }
.ogt-gun .ogt-e b{ font-weight:800; }
.ogt-dahasi{ border:0; background:transparent; color:#6d28d9; font-size:11.5px; font-weight:700; text-align:left; padding:1px 6px; cursor:pointer; }
.ogt-dahasi:hover{ text-decoration:underline; }

/* --- HAFTA / GÜN saat ızgarası --- */
.ogt-kaydir{ border:1px solid var(--cizgi); border-radius:12px; overflow:auto; max-height:72vh; -webkit-overflow-scrolling:touch; }
.ogt-izgara{ display:grid; min-width:100%; position:relative; }
.ogt-kb{ position:sticky; top:0; z-index:6; background:var(--mor-acik); color:var(--mor); text-align:center; padding:9px 6px; font-size:12.5px; font-weight:700; border-left:1px solid var(--cizgi); min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ogt-kb small{ display:block; font-size:11px; font-weight:600; color:#7c6c8a; }
.ogt-kb button{ border:0; background:transparent; color:inherit; font:inherit; cursor:pointer; padding:0; }
.ogt-kb button:hover{ text-decoration:underline; }
.ogt-kb.bugun{ background:#5C008E; color:#fff; }
.ogt-kb.bugun small{ color:#e9d5ff; }
.ogt-kb.kose{ left:0; z-index:8; border-left:0; }
.ogt-saatler{ position:sticky; left:0; z-index:5; background:#fff; }
.ogt-saatler div{ height:var(--saat-h); border-top:1px solid var(--cizgi); font-size:11.5px; color:#7c7690; text-align:right; padding:3px 7px 0 0; }
.ogt-sutun{ position:relative; border-left:1px solid var(--cizgi); background-image:linear-gradient(to bottom,var(--cizgi) 1px,transparent 1px),linear-gradient(to bottom,#f6f4fa 1px,transparent 1px); background-size:100% var(--saat-h),100% calc(var(--saat-h) / 2); min-width:0; }
.ogt-sutun.ekle{ cursor:copy; }
.ogt-sutun .ogt-e{ position:absolute; padding:4px 6px; font-size:12px; line-height:1.3; border-top:1px solid #fff; border-right:1px solid #fff; z-index:2; }
.ogt-sutun .ogt-e .s{ font-weight:800; }
.ogt-sutun .ogt-e .ad{ font-weight:700; }
.ogt-sutun .ogt-e div{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ogt-simdi{ position:absolute; left:0; right:0; height:2px; background:#dc2626; z-index:4; pointer-events:none; }
.ogt-simdi:before{ content:""; position:absolute; left:-4px; top:-3px; width:8px; height:8px; border-radius:50%; background:#dc2626; }
.ogt-saatsiz{ border-left:1px solid var(--cizgi); border-top:1px solid var(--cizgi); padding:4px; display:flex; flex-direction:column; gap:3px; min-width:0; background:#fcfbfe; }
.ogt-saatsiz.kose{ position:sticky; left:0; z-index:5; border-left:0; font-size:10.5px; color:#7c7690; text-align:right; padding:6px 7px 0 0; display:block; }
.ogt-saatsiz .ogt-e{ font-size:12px; padding:3px 6px; white-space:nowrap; text-overflow:ellipsis; }

/* --- Detay penceresi --- */
#ogt_detay .modal-dialog{ max-width:500px; }
#ogt_detay .modal-content{ border:0; border-radius:14px; overflow:hidden; }
#ogt_detay .ogt-d-bas{ background:#fff; border-bottom:1px solid #ece6f3; padding:15px 46px 13px 18px; position:relative; }
#ogt_detay .ogt-d-bas h2{ margin:0; font-size:17px; font-weight:700; color:#5C008E; }
#ogt_detay .ogt-d-bas .kapat{ position:absolute; right:10px; top:10px; width:30px; height:30px; border:0; background:transparent; border-radius:7px; font-size:22px; line-height:1; color:#6b7280; cursor:pointer; }
#ogt_detay .ogt-d-bas .kapat:hover{ background:#f3eefa; }
.ogt-rozet{ display:inline-block; margin-top:7px; font-size:11.5px; font-weight:700; padding:3px 11px; border-radius:20px; background:var(--z); color:var(--r); border:0; cursor:default; }
.ogt-e.ogt-rozet:hover{ filter:none; box-shadow:none; }
#ogt_detay .modal-body{ padding:14px 18px; }
.ogt-d-tablo{ display:grid; grid-template-columns:128px 1fr; gap:9px 12px; font-size:13.5px; margin:0; }
.ogt-d-tablo dt{ color:#6b7280; font-weight:500; }
.ogt-d-tablo dd{ margin:0; color:#1f2937; font-weight:600; overflow-wrap:anywhere; white-space:pre-line; }
.ogt-d-alt{ display:flex; gap:8px; flex-wrap:wrap; padding:12px 18px 16px; border-top:1px solid #ece6f3; background:#fbfafd; }
.ogt-d-alt .ogt-btn{ flex:1 1 auto; justify-content:center; border-radius:10px; }
.ogt-btn-kirmizi{ background:#b91c1c; border-color:#b91c1c; color:#fff !important; }
.ogt-btn-kirmizi:hover{ background:#991b1b; }
.ogt-not{ width:100%; font-size:12px; color:#6b7280; }

@media (max-width:767px){
   .ogt-ust{ padding:14px; }
   .ogt-ust-sag{ width:100%; }
   .ogt-ust-sag .ogt-btn{ flex:1; justify-content:center; padding:0 10px; }
   .ogt-panel{ padding:12px; }
   .ogt-aralik{ width:100%; order:-1; text-align:center; }
   .ogt-gez{ width:100%; justify-content:center; }
   .ogt-sag{ margin-left:0; width:100%; justify-content:space-between; }
   .ogt-gorunum button{ padding:0 11px; }
   #ogt_personel{ flex:1; max-width:none; min-width:0; }
   .ogt-gun{ min-height:62px; padding:2px; align-items:center; cursor:pointer; }
   .ogt-gun .ogt-e, .ogt-dahasi{ display:none; }
   .ogt-gno{ align-self:center; }
   .ogt-nokta{ display:inline-block !important; }
   .ogt-d-tablo{ grid-template-columns:104px 1fr; }
}
.ogt-nokta{ display:none; min-width:22px; text-align:center; background:#5C008E; color:#fff; font-size:11px; font-weight:700; border-radius:20px; padding:1px 6px; }
</style>

<div class="ogt" id="ogt">
   <div class="ogt-ust">
      <div class="ogt-baslik">
         <div class="ogt-ikon"><i class="fa fa-calendar"></i></div>
         <div>
            <h1>{{ $sayfa_baslik }}</h1>
            <nav aria-label="breadcrumb">
               <a href="/isletmeyonetim{{ isset($_GET['sube']) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a> ›
               <a href="/isletmeyonetim/ongorusmeler{{ isset($_GET['sube']) ? '?sube='.$isletme->id : '' }}">Ön Görüşmeler</a> ›
               <b>Takvim</b>
            </nav>
         </div>
      </div>
      <div class="ogt-ust-sag">
         <a class="ogt-btn" href="/isletmeyonetim/ongorusmeler{{ isset($_GET['sube']) ? '?sube='.$isletme->id : '' }}"><i class="fa fa-list"></i> Liste</a>
         @if($ekleDuzenleYetki)
         <button type="button" class="ogt-btn ogt-btn-yesil" id="ogt_yeni"><i class="fa fa-plus"></i> Yeni Ön Görüşme</button>
         @endif
      </div>
   </div>

   <div class="ogt-panel">
      <div class="ogt-arac">
         <div class="ogt-gez">
            <button type="button" id="ogt_geri" aria-label="Önceki"><i class="fa fa-chevron-left"></i></button>
            <button type="button" id="ogt_bugun">Bugün</button>
            <button type="button" id="ogt_ileri" aria-label="Sonraki"><i class="fa fa-chevron-right"></i></button>
            <input type="text" id="ogt_tarih" autocomplete="off" readonly aria-label="Tarihe git">
         </div>
         <span class="ogt-aralik" id="ogt_aralik"></span>
         <div class="ogt-sag">
            @if(count($_ogtPersoneller) > 1)
            <select id="ogt_personel" aria-label="Personel">
               <option value="">Tüm personel</option>
               @foreach($_ogtPersoneller as $_p)
               <option value="{{ $_p['id'] }}">{{ $_p['ad'] }}</option>
               @endforeach
            </select>
            @endif
            <div class="ogt-gorunum" id="ogt_gorunum">
               <button type="button" data-g="ay">Ay</button>
               <button type="button" data-g="hafta">Hafta</button>
               <button type="button" data-g="gun">Gün</button>
            </div>
         </div>
      </div>

      <div class="ogt-filtre" id="ogt_filtre">
         <button type="button" data-d="" class="aktif"><i style="background:#9a93ad"></i> Tümü <b>0</b></button>
         <button type="button" data-d="bekliyor"><i style="background:#f59e0b"></i> Beklemede <b>0</b></button>
         <button type="button" data-d="satildi"><i style="background:#16a34a"></i> Satış Yapıldı <b>0</b></button>
         <button type="button" data-d="satilmadi"><i style="background:#dc2626"></i> Satış Yapılmadı <b>0</b></button>
         <button type="button" data-d="iptal"><i style="background:#9ca3af"></i> Randevu İptal <b>0</b></button>
      </div>

      <div class="ogt-alan" id="ogt_alan"></div>
   </div>
</div>

<div class="modal fade" id="ogt_detay" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="ogt-d-bas">
            <h2 id="ogt_d_ad"></h2>
            <span id="ogt_d_durum"></span>
            <button type="button" class="kapat" data-dismiss="modal" aria-label="Kapat">&times;</button>
         </div>
         <div class="modal-body"><dl class="ogt-d-tablo" id="ogt_d_bilgi"></dl></div>
         <div class="ogt-d-alt" id="ogt_d_alt"></div>
      </div>
   </div>
</div>

{{-- custom.js'teki delege handler'i (a[name="ongorusme_duzenle"]) tetiklemek icin gizli baglanti --}}
<a href="#" name="ongorusme_duzenle" id="ogt_duzenle_tetik" data-value="" style="display:none" aria-hidden="true" tabindex="-1"></a>

<script>
$(document).ready(function(){
   var SUBE = {{ (int) $isletme->id }};
   var YETKI = {{ $ekleDuzenleYetki ? 'true' : 'false' }};
   var PERSONELLER = {!! json_encode($_ogtPersoneller, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
   var BUGUN = '{{ date('Y-m-d') }}';
   var AYLAR = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
   var GUNLER = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];
   var GUN_KISA = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
   var DURUM_AD = {bekliyor:'Beklemede', satildi:'Satış Yapıldı', satilmadi:'Satış Yapılmadı', iptal:'Randevu İptal'};
   var SAAT_H = 60, VARSAYILAN_BAS = 8, VARSAYILAN_BIT = 21;

   var gorunum = oku('gorunum') || (window.innerWidth < 768 ? 'gun' : 'ay');
   if (['ay','hafta','gun'].indexOf(gorunum) === -1) gorunum = 'ay';
   var secili = BUGUN, durumF = '', olaylar = [], istekNo = 0, detayOlay = null, dp = null, dpProg = false;

   /* ---------- yardımcılar ---------- */
   function oku(k){ try { return localStorage.getItem('ogt_'+k); } catch(e){ return null; } }
   function yaz(k,v){ try { localStorage.setItem('ogt_'+k, v); } catch(e){} }
   function esc(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
   function iki(n){ return ('0'+n).slice(-2); }
   function tarih(s){ var p = s.split('-'); return new Date(+p[0], +p[1]-1, +p[2]); }
   function ymd(d){ return d.getFullYear()+'-'+iki(d.getMonth()+1)+'-'+iki(d.getDate()); }
   function gunEkle(s,n){ var d = tarih(s); d.setDate(d.getDate()+n); return ymd(d); }
   function haftaGunu(s){ return (tarih(s).getDay()+6)%7; } // 0=Pazartesi
   function haftaBasi(s){ return gunEkle(s, -haftaGunu(s)); }
   function ayEkle(s,n){ var d = tarih(s), g = d.getDate(); d.setDate(1); d.setMonth(d.getMonth()+n); var son = new Date(d.getFullYear(), d.getMonth()+1, 0).getDate(); d.setDate(Math.min(g,son)); return ymd(d); }
   function dk(s){ var p = s.split(':'); return (+p[0])*60 + (+p[1]); }
   function uzun(s){ var d = tarih(s); return d.getDate()+' '+AYLAR[d.getMonth()]+' '+d.getFullYear()+', '+GUNLER[haftaGunu(s)]; }

   function aralik(){
      if (gorunum === 'gun') return [secili, secili];
      if (gorunum === 'hafta'){ var h = haftaBasi(secili); return [h, gunEkle(h,6)]; }
      var d = tarih(secili);
      var bas = haftaBasi(ymd(new Date(d.getFullYear(), d.getMonth(), 1)));
      var son = ymd(new Date(d.getFullYear(), d.getMonth()+1, 0));
      return [bas, gunEkle(haftaBasi(son), 6)];
   }
   function baslikYaz(){
      var d = tarih(secili), t;
      if (gorunum === 'ay') t = AYLAR[d.getMonth()]+' '+d.getFullYear();
      else if (gorunum === 'gun') t = uzun(secili);
      else {
         var a = aralik(), b = tarih(a[0]), s = tarih(a[1]);
         t = b.getDate() + (b.getMonth() !== s.getMonth() ? ' '+AYLAR[b.getMonth()] : '') + (b.getFullYear() !== s.getFullYear() ? ' '+b.getFullYear() : '') +
             ' – ' + s.getDate()+' '+AYLAR[s.getMonth()]+' '+s.getFullYear();
      }
      $('#ogt_aralik').text(t);
      $('#ogt_gorunum button').removeClass('aktif').filter('[data-g="'+gorunum+'"]').addClass('aktif');
      dpProg = true;
      if (dp) { try { dp.selectDate(tarih(secili)); } catch(e){} }
      dpProg = false;
      $('#ogt_tarih').val(secili);
   }

   /* ---------- veri ---------- */
   function yukle(){
      var a = aralik(), no = ++istekNo;
      baslikYaz();
      $('#ogt_alan').addClass('yukleniyor');
      $.ajax({ url:'/isletmeyonetim/ongorusme-takvim-verileri', dataType:'json', cache:false, data:{ sube:SUBE, start:a[0], end:a[1] } })
         .done(function(r){
            if (no !== istekNo) return;
            olaylar = r || [];
            ciz(true);
         })
         .fail(function(){
            if (no !== istekNo) return;
            olaylar = [];
            $('#ogt_alan').html('<div class="ogt-hata">Ön görüşmeler yüklenemedi. Sayfayı yenileyip tekrar deneyin.</div>');
         })
         .always(function(){ if (no === istekNo) $('#ogt_alan').removeClass('yukleniyor'); });
   }
   function personelListesi(){
      var pid = $('#ogt_personel').val();
      return pid ? olaylar.filter(function(o){ return String(o.personel_id) === String(pid); }) : olaylar;
   }
   function sirala(l){ return l.slice().sort(function(a,b){ return (a.tarih+(a.saat||'')).localeCompare(b.tarih+(b.saat||'')); }); }

   function ciz(ilk){
      var liste = personelListesi();
      // Ay gorunumunde komsu ayin gunleri de yuklenir; sayaclar yalniz secili ayi sayar.
      var sayilan = gorunum === 'ay' ? liste.filter(function(o){ return o.tarih.substr(0,7) === secili.substr(0,7); }) : liste;
      $('#ogt_filtre button').each(function(){
         var d = $(this).attr('data-d');
         $(this).find('b').text(d ? sayilan.filter(function(o){ return o.durum === d; }).length : sayilan.length);
      });
      if (durumF) liste = liste.filter(function(o){ return o.durum === durumF; });
      liste = sirala(liste);

      var html = '';
      if (!liste.length) html += '<div class="ogt-bos">'+(olaylar.length ? 'Bu filtreye uyan ön görüşme yok.' : 'Bu tarihlerde ön görüşme yok.')+'</div>';
      html += gorunum === 'ay' ? ayHtml(liste) : izgaraHtml(liste);
      var st = $('#ogt_alan .ogt-kaydir').scrollTop();
      $('#ogt_alan').html(html);
      if (gorunum !== 'ay') {
         var $k = $('#ogt_alan .ogt-kaydir');
         if (ilk) kaydir($k, liste); else $k.scrollTop(st || 0);
      }
   }

   function ipucu(o){ return (o.saat ? o.saat+' · ' : '')+o.musteri+' · '+o.neden+' · '+o.personel+' · '+DURUM_AD[o.durum]; }

   /* ---------- AY ---------- */
   function ayHtml(liste){
      var a = aralik(), ay = tarih(secili).getMonth(), gunler = {};
      liste.forEach(function(o){ (gunler[o.tarih] = gunler[o.tarih] || []).push(o); });
      var h = '<div class="ogt-ay"><div class="ogt-ay-bas">';
      GUN_KISA.forEach(function(g){ h += '<div>'+g+'</div>'; });
      h += '</div>';
      for (var g = a[0]; g <= a[1]; ) {
         h += '<div class="ogt-ay-satir">';
         for (var i = 0; i < 7; i++) {
            var l = gunler[g] || [], goster = l.length > 4 ? 3 : l.length;
            h += '<div class="ogt-gun'+(tarih(g).getMonth() !== ay ? ' disi' : '')+(g === BUGUN ? ' bugun' : '')+(YETKI ? ' ekle' : '')+'" data-gun="'+g+'">';
            h += '<button type="button" class="ogt-gno" data-gun="'+g+'" title="Günü aç">'+tarih(g).getDate()+'</button>';
            for (var j = 0; j < goster; j++) {
               var o = l[j];
               h += '<div class="ogt-e '+o.durum+'" tabindex="0" role="button" data-id="'+o.id+'" title="'+esc(ipucu(o))+'">'+(o.saat ? '<b>'+o.saat+'</b> ' : '')+'<span class="ad">'+esc(o.musteri)+'</span></div>';
            }
            if (l.length > goster) h += '<button type="button" class="ogt-dahasi" data-gun="'+g+'">+'+(l.length-goster)+' görüşme daha</button>';
            if (l.length) h += '<span class="ogt-nokta">'+l.length+'</span>';
            h += '</div>';
            g = gunEkle(g,1);
         }
         h += '</div>';
      }
      return h + '</div>';
   }

   /* ---------- HAFTA / GÜN ---------- */
   function sutunlar(liste){
      var s = [];
      if (gorunum === 'hafta') {
         var b = haftaBasi(secili);
         for (var i = 0; i < 7; i++) (function(g){
            s.push({ gun:g, pid:$('#ogt_personel').val() || '', bugun:g === BUGUN,
               ust:'<button type="button" class="ogt-gunac" data-gun="'+g+'" title="Günü aç">'+GUN_KISA[haftaGunu(g)]+'<small>'+tarih(g).getDate()+' '+AYLAR[tarih(g).getMonth()]+'</small></button>',
               l: liste.filter(function(o){ return o.tarih === g; }) });
         })(gunEkle(b,i));
         return s;
      }
      var secPid = $('#ogt_personel').val(), gorulen = {};
      function ekle(id, ad){
         if (gorulen[id]) return; gorulen[id] = 1;
         var l = liste.filter(function(o){ return o.personel_id === id; });
         s.push({ gun:secili, pid:id || '', bugun:false, ust:esc(ad)+'<small>'+l.length+' görüşme</small>', l:l });
      }
      PERSONELLER.forEach(function(p){ if (!secPid || String(p.id) === String(secPid)) ekle(p.id, p.ad); });
      liste.forEach(function(o){ ekle(o.personel_id, o.personel); }); // pasif/silinmis personelin kayitlari
      if (!s.length) s.push({ gun:secili, pid:'', bugun:false, ust:'Ön Görüşmeler', l:liste });
      return s;
   }
   function saatAraligi(liste){
      var b = VARSAYILAN_BAS, e = VARSAYILAN_BIT;
      liste.forEach(function(o){
         if (!o.saat) return;
         var z = zaman(o);
         b = Math.min(b, Math.floor(z.b/60)); e = Math.max(e, Math.min(24, Math.ceil(z.e/60)));
      });
      return [b, e];
   }
   function zaman(o){
      var b = dk(o.saat), e = o.bitis ? dk(o.bitis) : b+60;
      if (e <= b) e = Math.min(1440, b+60); // gece yarisini asan / bozuk bitis
      return { b:b, e:e };
   }
   // Cakisan gorusmeleri yan yana serit olarak yerlestir
   function yerlestir(l){
      var ol = l.filter(function(o){ return o.saat; }).map(function(o){ var z = zaman(o); return { o:o, b:z.b, e:Math.max(z.e, z.b+30), s:0, n:1 }; });
      ol.sort(function(x,y){ return x.b-y.b || y.e-x.e; });
      var kume = [], kumeSon = -1;
      function kapat(){
         var bitis = [];
         kume.forEach(function(k){
            var i = 0; while (i < bitis.length && bitis[i] > k.b) i++;
            bitis[i] = k.e; k.s = i;
         });
         kume.forEach(function(k){ k.n = bitis.length; });
         kume = [];
      }
      ol.forEach(function(k){
         if (kume.length && k.b >= kumeSon) { kapat(); kumeSon = -1; }
         kume.push(k); kumeSon = Math.max(kumeSon, k.e);
      });
      kapat();
      return ol;
   }
   function izgaraHtml(liste){
      var s = sutunlar(liste), sa = saatAraligi(liste), min = gorunum === 'hafta' ? 118 : 170;
      var saatsizVar = liste.some(function(o){ return !o.saat; });
      var h = '<div class="ogt-kaydir" data-bas="'+sa[0]+'"><div class="ogt-izgara" style="grid-template-columns:54px repeat('+s.length+',minmax('+min+'px,1fr))">';
      h += '<div class="ogt-kb kose"></div>';
      s.forEach(function(k){ h += '<div class="ogt-kb'+(k.bugun ? ' bugun' : '')+'">'+k.ust+'</div>'; });
      if (saatsizVar) {
         h += '<div class="ogt-saatsiz kose">Saatsiz</div>';
         s.forEach(function(k){
            h += '<div class="ogt-saatsiz">';
            k.l.forEach(function(o){ if (!o.saat) h += '<div class="ogt-e '+o.durum+'" tabindex="0" role="button" data-id="'+o.id+'" title="'+esc(ipucu(o))+'"><span class="ad">'+esc(o.musteri)+'</span></div>'; });
            h += '</div>';
         });
      }
      h += '<div class="ogt-saatler">';
      for (var i = sa[0]; i < sa[1]; i++) h += '<div>'+iki(i)+':00</div>';
      h += '</div>';
      var simdi = new Date(), sdk = simdi.getHours()*60+simdi.getMinutes();
      s.forEach(function(k){
         h += '<div class="ogt-sutun'+(YETKI ? ' ekle' : '')+'" data-gun="'+k.gun+'" data-pid="'+k.pid+'" style="height:'+((sa[1]-sa[0])*SAAT_H)+'px">';
         if (k.gun === BUGUN && sdk >= sa[0]*60 && sdk < sa[1]*60) h += '<div class="ogt-simdi" style="top:'+((sdk-sa[0]*60)*SAAT_H/60)+'px"></div>';
         yerlestir(k.l).forEach(function(y){
            var o = y.o, top = (y.b-sa[0]*60)*SAAT_H/60, boy = Math.min(y.e, sa[1]*60)-y.b, w = 100/y.n;
            h += '<div class="ogt-e '+o.durum+'" tabindex="0" role="button" data-id="'+o.id+'" title="'+esc(ipucu(o))+'" style="top:'+top+'px;height:'+(boy*SAAT_H/60-1)+'px;left:'+(y.s*w)+'%;width:calc('+w+'% - 2px)">'+
               '<div><span class="s">'+o.saat+'</span> <span class="ad">'+esc(o.musteri)+'</span></div>'+
               (boy >= 40 ? '<div>'+esc(o.neden)+'</div>' : '')+
               (boy >= 55 && gorunum === 'hafta' ? '<div>'+esc(o.personel)+'</div>' : '')+
               (boy >= 55 && gorunum === 'gun' && o.oda ? '<div>'+esc(o.oda)+'</div>' : '')+'</div>';
         });
         h += '</div>';
      });
      return h + '</div></div>';
   }
   // Ilk acilista en erken gorusme gorunsun; hic yoksa ve bugun ekrandaysa simdiki saat
   function kaydir($k, liste){
      var bas = parseInt($k.attr('data-bas'),10)*60, hedef = null, a = aralik();
      liste.forEach(function(o){ if (o.saat && (hedef === null || dk(o.saat)-30 < hedef)) hedef = dk(o.saat)-30; });
      if (hedef === null && BUGUN >= a[0] && BUGUN <= a[1]) { var s = new Date(); hedef = s.getHours()*60+s.getMinutes()-60; }
      if (hedef !== null) $k.scrollTop(Math.max(0,(hedef-bas)*SAAT_H/60));
   }

   /* ---------- yeni / detay / düzenle ---------- */
   function yeniAc(gun, saat, pid){
      if (!YETKI) return;
      if (typeof modalbaslikata === 'function') modalbaslikata('Yeni Ön Görüşme','');
      var f = $('#ongorusmeformu');
      f[0].reset();
      $('#on_gorusme_id').val('');
      $('#musteri_select_list').val(null).trigger('change.select2');
      $('#paket').val(null).trigger('change.select2');
      $('#ongorusme_oda_id').val('');
      $('#ongorusme_tarihi').val(gun || '');
      $('#ongorusme_saati').val(saat ? saat+':00' : '');
      var p = pid ? PERSONELLER.filter(function(x){ return String(x.id) === String(pid); })[0] : null;
      var $g = $('#gorusmeyi_yapan');
      if (p) {
         secenekEkle($g, p.id, p.ad);
         $g.val(String(p.id));
      }
      $g.trigger('change.select2');
      $('#ongorusme-modal').modal('show');
   }
   // Ajax'li select2'lerde secenek yoksa adi bos gorunur; once secenegi ekle
   function secenekEkle($s, id, ad){
      if (id && !$s.find('option[value="'+id+'"]').length) $s.append(new Option(ad, id, false, false));
   }
   function detayAc(id){
      var o = olaylar.filter(function(x){ return x.id === id; })[0];
      if (!o) return;
      detayOlay = o;
      $('#ogt_d_ad').text(o.musteri);
      $('#ogt_d_durum').attr('class','ogt-e ogt-rozet '+o.durum).text(DURUM_AD[o.durum]);
      var satir = [
         ['Tarih', uzun(o.tarih)],
         ['Saat', o.saat ? o.saat+(o.bitis ? ' – '+o.bitis : '') : 'Belirtilmemiş'],
         ['Görüşmeyi yapan', o.personel],
         ['Oda', o.oda],
         ['Sebep', o.neden],
         ['Telefon', o.telefon],
         ['Referans', o.referans],
         ['Açıklama', o.aciklama],
         ['Satış olmama nedeni', o.durum === 'satilmadi' ? (o.neden_yok || 'Belirtilmemiş') : ''],
         ['Oluşturma', o.olusturma]
      ];
      $('#ogt_d_bilgi').html(satir.filter(function(s){ return s[1]; }).map(function(s){ return '<dt>'+s[0]+'</dt><dd>'+esc(s[1])+'</dd>'; }).join(''));
      var b = '';
      if (o.user_id) b += '<a class="ogt-btn" target="_blank" rel="noopener" href="/isletmeyonetim/musteridetay/'+o.user_id+'?sube='+SUBE+'"><i class="fa fa-user"></i> Müşteri</a>';
      if (o.sonucsuz) {
         if (YETKI && o.randevulu) b += '<button type="button" class="ogt-btn" data-is="duzenle"><i class="fa fa-edit"></i> Düzenle</button>';
         if (o.tur) b += '<button type="button" class="ogt-btn ogt-btn-yesil" data-is="satildi"><i class="fa fa-check"></i> Satış Yapıldı</button>';
         b += '<button type="button" class="ogt-btn ogt-btn-kirmizi" data-is="satilmadi"><i class="fa fa-times"></i> Satış Yapılmadı</button>';
      }
      $('#ogt_d_alt').html(b).toggle(!!b);
      $('#ogt_detay').modal('show');
   }
   // Detay penceresi TAM kapandiktan sonra devam et (ust uste modal/backdrop karismasin)
   function detayKapat(sonra){
      $('#ogt_detay').one('hidden.bs.modal', function(){ setTimeout(sonra, 30); }).modal('hide');
   }
   $(document).on('click', '#ogt_d_alt [data-is]', function(){
      var is = $(this).attr('data-is'), o = detayOlay;
      if (!o) return;
      detayKapat(function(){
         if (is === 'duzenle') {
            if (typeof modalbaslikata === 'function') modalbaslikata('Ön Görüşme Düzenleme','');
            $('#ongorusmeformu')[0].reset();
            secenekEkle($('#gorusmeyi_yapan'), o.personel_id, o.personel);
            secenekEkle($('#musteri_select_list'), o.user_id, o.musteri);
            $('#ogt_duzenle_tetik').attr('data-value', o.id).trigger('click');
            $('#ongorusme-modal').modal('show');
         } else if (typeof ongorusmeseansgirdikontrol === 'function') {
            ongorusmeseansgirdikontrol(o.id, is === 'satildi', is === 'satildi' ? o.tur : 1);
         }
      });
   });

   /* ---------- olaylar ---------- */
   function git(g, v){ secili = g; if (v) { gorunum = v; yaz('gorunum', v); } yukle(); }
   function adim(n){ git(gorunum === 'ay' ? ayEkle(secili,n) : gunEkle(secili, gorunum === 'hafta' ? 7*n : n)); }
   $('#ogt_geri').on('click', function(){ adim(-1); });
   $('#ogt_ileri').on('click', function(){ adim(1); });
   $('#ogt_bugun').on('click', function(){ git(BUGUN); });
   $('#ogt_yeni').on('click', function(){ yeniAc(gorunum === 'ay' ? '' : secili, '', $('#ogt_personel').val()); });
   $('#ogt_gorunum').on('click', 'button', function(){ git(secili, $(this).attr('data-g')); });
   $('#ogt_personel').on('change', function(){ ciz(false); });
   $('#ogt_filtre').on('click', 'button', function(){
      durumF = $(this).attr('data-d');
      $('#ogt_filtre button').removeClass('aktif'); $(this).addClass('aktif');
      ciz(false);
   });
   $('#ogt_alan').on('click', '.ogt-e', function(e){ e.stopPropagation(); detayAc(+$(this).attr('data-id')); });
   $('#ogt_alan').on('keydown', '.ogt-e', function(e){ if (e.which === 13 || e.which === 32) { e.preventDefault(); detayAc(+$(this).attr('data-id')); } });
   $('#ogt_alan').on('click', '.ogt-gno, .ogt-dahasi, .ogt-gunac', function(e){ e.stopPropagation(); git($(this).attr('data-gun'), 'gun'); });
   $('#ogt_alan').on('click', '.ogt-gun', function(){
      var g = $(this).attr('data-gun');
      if (window.innerWidth < 768) git(g, 'gun'); else yeniAc(g, '', $('#ogt_personel').val());
   });
   $('#ogt_alan').on('click', '.ogt-sutun.ekle', function(e){
      var bas = parseInt($(this).closest('.ogt-kaydir').attr('data-bas'),10)*60;
      var m = bas + Math.floor((e.clientY - this.getBoundingClientRect().top)*60/SAAT_H/15)*15; // 15 dk adim
      m = Math.max(0, Math.min(1425, m));
      yeniAc($(this).attr('data-gun'), iki(Math.floor(m/60))+':'+iki(m%60), $(this).attr('data-pid'));
   });

   // Kayit / duzenleme / satis sonucu basarili olunca takvimi tazele. Prefilter: custom.js'in kendi
   // success'inden ONCE baglanir; oradaki bir hata tazelemeyi engelleyemez.
   $.ajaxPrefilter(function(s, o, xhr){
      if (/\/isletmeyonetim\/(ongorusmeekleduzenle|ongorusmesatisyapildi|ongorusmesatisyapilmadi)\b/.test(s.url || '')) xhr.done(function(){ yukle(); });
   });

   if ($.fn.datepicker) {
      try {
         $('#ogt_tarih').datepicker({ language:'tr', dateFormat:'yyyy-mm-dd', autoClose:true, position:'bottom left',
            onSelect:function(fd){ if (!dpProg && fd && fd !== secili) git(fd); } });
         dp = $('#ogt_tarih').data('datepicker');
         if (dp && typeof dp.selectDate !== 'function') dp = null;
      } catch(e){ dp = null; }
   }

   yukle();
});
</script>
@endsection
