@extends('layout.layout_isletmeadmin')
@section('content')

@php
   $_ogtPersoneller = $personeller->map(function ($p) use ($baslikRenkleri) {
      $r = $baslikRenkleri[$p->id] ?? null;
      return ['id' => (int) $p->id, 'ad' => $p->personel_adi, 'bg' => $r['bg'] ?? '', 'yazi' => $r['yazi'] ?? ''];
   })->values()->all();
@endphp

<style>
/* =================================================================
   ÖN GÖRÜŞME TAKVİMİ (günlük)
   ================================================================= */
.ogt { --mor:#5C008E; --mor2:#9D5DC8; --mor-acik:#f5eefe; --yazi:#1f2937; --soluk:#6b7280; --cizgi:#eceaf2; --saat-h:96px; }
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
#ogt_personel{ height:38px; border:1px solid #d9c9f0; border-radius:10px; padding:0 10px; font-size:13.5px; background:#fff; color:var(--yazi); }
.ogt-psec{ position:relative; width:230px; }
.ogt-psec .select2-container{ width:100% !important; margin:0 !important; }
.ogt-psec .select2-container .select2-selection--single{ height:38px !important; border:1px solid #d9c9f0 !important; border-radius:10px !important; background:#fff; }
.ogt-psec .select2-container .select2-selection__rendered{ line-height:36px !important; padding-left:12px; padding-right:26px; font-size:13.5px; color:#1f2937 !important; }
.ogt-psec .select2-container .select2-selection__arrow{ height:36px !important; right:6px; }

.ogt-filtre{ display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
.ogt-filtre button{ display:inline-flex; align-items:center; gap:7px; border:1px solid #ece7f6; background:#fff; color:#5a5570; border-radius:20px; padding:6px 12px; font-size:12.5px; cursor:pointer; }
.ogt-filtre button i{ width:11px; height:11px; border-radius:3px; display:inline-block; }
.ogt-filtre button b{ background:#eef0f5; color:#5b6172; border-radius:20px; padding:1px 8px; font-size:11.5px; }
.ogt-filtre button.aktif{ background:#f1edfb; border-color:#8b5cf6; color:#6d28d9; font-weight:700; }
.ogt-filtre button.aktif b{ background:#6d28d9; color:#fff; }

.ogt-alan{ position:relative; min-height:220px; }
.ogt-alan.yukleniyor{ pointer-events:none; }
.ogt-alan.yukleniyor.gun-degisti{ opacity:.45; } /* isaretleme/kayit sonrasi tazelemede soluklasma YOK */
#preloader{ background:transparent !important; } /* bu sayfada islem sirasinda ekran grilesmesin; tiklama kilidi kalir */
.ogt-hata{ padding:40px 16px; text-align:center; color:#b91c1c; font-size:14px; }
.ogt-bos{ padding:10px 14px; margin-bottom:10px; background:#faf7ff; border-radius:10px; color:#6b5b7b; font-size:13px; }

/* --- Kayıt: renk/yazı randevu takviminden gelir (inline --z / --y) --- */
.ogt-e{ --z:#3a87ad; --y:#fff; background-color:var(--z); color:var(--y); border-left:4px solid rgba(0,0,0,.28); border-radius:7px; cursor:pointer; overflow:hidden; }
.ogt-e.iptal .ad, .ogt-e.ertelendi .ad{ text-decoration:line-through; }
.ogt-e.ertelendi{ --z:#f4f2f8; --y:#5b5470; border:1px dashed #b3aac6; border-left:4px solid #b3aac6; background-image:repeating-linear-gradient(135deg,transparent 0 7px,rgba(91,84,112,.07) 7px 14px); }
.ogt-e:hover{ filter:brightness(.95); box-shadow:0 4px 12px -5px rgba(30,30,60,.35); }
.ogt-e:focus{ outline:2px solid #5C008E; outline-offset:1px; }
.ogt-mini{ display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle; margin-top:2px; padding:1px 7px; border-radius:20px; font-size:11px; font-weight:700; background:rgba(255,255,255,.92); color:#1f2937; }
.ogt-mini.ert{ background:#5C008E; color:#fff; }

.ogt-ara{ position:absolute; right:3px; top:2px; width:24px; height:20px; padding:0; border:0; border-radius:6px; background:#16a34a; color:#fff; font-size:12px; line-height:20px; text-align:center; cursor:pointer; box-shadow:0 0 0 1px rgba(255,255,255,.85); z-index:1; }
.ogt-ara:hover, .ogt-ara:focus{ background:#12813b; }
.ogt-ara:disabled{ opacity:.6; }
.ogt-e.arali > .ust, .ogt-saatsiz .ogt-e.arali{ padding-right:27px; }
.ogt-saatsiz .ogt-e{ position:relative; min-height:24px; overflow:hidden; flex-shrink:0; }

/* --- Saat ızgarası (sütun = personel) --- */
.ogt-kaydir{ border:1px solid var(--cizgi); border-radius:12px; overflow:auto; max-height:72vh; -webkit-overflow-scrolling:touch; }
.ogt-izgara{ display:grid; min-width:100%; position:relative; }
.ogt-kb{ position:sticky; top:0; z-index:6; background:var(--mor-acik); color:var(--mor); text-align:center; padding:9px 6px; font-size:12.5px; font-weight:700; border-left:1px solid var(--cizgi); min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ogt-kb small{ display:block; font-size:11px; font-weight:600; color:#7c6c8a; }
.ogt-kb.renkli{ background:var(--bz); color:var(--by); border-left-color:rgba(255,255,255,.45); }
.ogt-kb.renkli small{ color:inherit; opacity:.85; }
.ogt-kb.kose{ left:0; z-index:8; border-left:0; }
.ogt-saatler{ position:sticky; left:0; z-index:5; background:#fff; }
.ogt-saatler div{ height:calc(var(--saat-h) / 4); border-top:1px solid #f3f1f7; font-size:10.5px; color:#a39db3; text-align:right; padding:4px 7px 0 0; line-height:1.2; }
.ogt-saatler div.tam{ border-top-color:var(--cizgi); font-size:11.5px; font-weight:700; color:#4a4461; }
.ogt-sutun{ position:relative; border-left:1px solid var(--cizgi); background-image:linear-gradient(to bottom,var(--cizgi) 1px,transparent 1px),linear-gradient(to bottom,#f6f4fa 1px,transparent 1px); background-size:100% var(--saat-h),100% calc(var(--saat-h) / 4); min-width:0; }
.ogt-kapali{ position:absolute; left:0; right:0; background:rgba(108,99,128,.09); pointer-events:none; }
.ogt-sutun.ekle{ cursor:copy; }
.ogt-sutun .ogt-e{ position:absolute; padding:4px 6px; font-size:12px; line-height:1.3; box-shadow:0 0 0 1px #fff; z-index:2; }
.ogt-sutun .ogt-e .s{ font-weight:800; }
.ogt-sutun .ogt-e .ad{ font-weight:700; }
.ogt-sutun .ogt-e div{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ogt-e.tasinir{ cursor:grab; }
.ogt-tut{ position:absolute; left:0; right:0; bottom:0; height:8px; cursor:ns-resize; }
.ogt-e:hover .ogt-tut:after{ content:""; position:absolute; left:50%; bottom:2px; width:22px; height:3px; margin-left:-11px; border-radius:3px; background:var(--y); opacity:.8; }
.ogt-sutun .ogt-e.golge{ z-index:4; opacity:.94; box-shadow:0 10px 24px -8px rgba(43,27,69,.5); pointer-events:none; }
.ogt-e.kaynak{ opacity:.35; }
body.ogt-surukle, body.ogt-surukle *{ cursor:grabbing !important; user-select:none !important; }
body.ogt-boy, body.ogt-boy *{ cursor:ns-resize !important; user-select:none !important; }
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
.ogt-rozet{ display:inline-block; margin-top:7px; font-size:11.5px; font-weight:700; padding:3px 11px; border-radius:20px; border:0 !important; cursor:default; }
.ogt-e.ogt-rozet:hover{ filter:none; box-shadow:none; }
#ogt_detay .modal-body{ padding:14px 18px; }
.ogt-d-tablo{ display:grid; grid-template-columns:128px 1fr; gap:9px 12px; font-size:13.5px; margin:0; }
.ogt-d-tablo dt{ color:#6b7280; font-weight:500; }
.ogt-d-tablo dd{ margin:0; color:#1f2937; font-weight:600; overflow-wrap:anywhere; white-space:pre-line; }
.ogt-kat{ display:flex; gap:8px; padding:0 18px 14px; flex-wrap:wrap; }
.ogt-kat button{ flex:1 1 0; min-width:96px; height:42px; border-radius:10px; border:2px solid var(--b); background:#fff; color:var(--b); font-size:14px; font-weight:700; cursor:pointer; }
.ogt-kat button.secili, .ogt-kat button:hover{ background:var(--b); color:#fff; }
.ogt-kat button.kaldir{ --b:#5b5470; flex:1 0 100%; border-width:1px; }
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
   .ogt-psec{ flex:1 1 150px; width:auto; min-width:0; }
   #ogt_personel{ width:100%; }
   .ogt-d-tablo{ grid-template-columns:104px 1fr; }
}
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
            <div class="ogt-psec"><select id="ogt_personel" aria-label="Personel">
               <option value="">Tüm personel</option>
               @foreach($_ogtPersoneller as $_p)
               <option value="{{ $_p['id'] }}">{{ $_p['ad'] }}</option>
               @endforeach
            </select></div>
            @endif
         </div>
      </div>

      <div class="ogt-filtre" id="ogt_filtre">
         <button type="button" data-d="" class="aktif"><i style="background:#9a93ad"></i> Tümü <b>0</b></button>
         <button type="button" data-d="bekliyor"><i style="background:#fff;border:2px solid #9a93ad"></i> İşaretlenmedi <b>0</b></button>
         <button type="button" data-d="gelecek"><i style="background:#EF7400"></i> Gelecek <b>0</b></button>
         <button type="button" data-d="geldi"><i style="background:#008000"></i> Geldi <b>0</b></button>
         <button type="button" data-d="gelmedi"><i style="background:#ff0000"></i> Gelmedi <b>0</b></button>
         <button type="button" data-d="ertelendi"><i style="background:#f4f2f8;border:2px dashed #b3aac6"></i> Ertelendi <b>0</b></button>
         <button type="button" data-d="iptal"><i style="background:#6b7280"></i> Randevu İptal <b>0</b></button>
         <button type="button" data-d="satildi"><span class="fa fa-check" aria-hidden="true"></span> Satış Yapıldı <b>0</b></button>
         <button type="button" data-d="satilmadi"><span class="fa fa-times" aria-hidden="true"></span> Satış Yapılmadı <b>0</b></button>
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
         <div class="ogt-kat" id="ogt_d_katilim"></div>
         <div class="ogt-d-alt" id="ogt_d_alt"></div>
      </div>
   </div>
</div>

{{-- custom.js'teki delege handler'i (a[name="ongorusme_duzenle"]) tetiklemek icin gizli baglanti --}}
<a href="#" name="ongorusme_duzenle" id="ogt_duzenle_tetik" data-value="" style="display:none" aria-hidden="true" tabindex="-1"></a>

@if($aramaAktif)
{{-- Arama randevusu yalniz KAYDIN detayindan olusturulur; takvimde sadece on gorusmeler gosterilir. --}}
@include('isletmeadmin.partials.arama_randevu_ekle')
@endif

<script>
$(document).ready(function(){
   var SUBE = {{ (int) $isletme->id }};
   var YETKI = {{ $ekleDuzenleYetki ? 'true' : 'false' }};
   var ARAMA = {{ $aramaAktif ? 'true' : 'false' }}, KATILIM = {{ $katilimYetki ? 'true' : 'false' }};
   var PERSONELLER = {!! json_encode($_ogtPersoneller, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
   var CALISMA = {!! json_encode((object) $calismaSaatleri, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}; // 1=Pzt .. 7=Paz
   var TASI = YETKI && {{ $tasimaYetki ? 'true' : 'false' }}, TOKEN = '{{ csrf_token() }}';
   var BUGUN = '{{ date('Y-m-d') }}';
   var AYLAR = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
   var GUNLER = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];
   var DURUM_AD = {bekliyor:'İşaretlenmedi', gelecek:'Gelecek', geldi:'Geldi', gelmedi:'Gelmedi', iptal:'Randevu İptal', ertelendi:'Ertelendi'};
   var SATIS_AD = {satildi:'Satış Yapıldı', satilmadi:'Satış Yapılmadı'};
   var SAAT_H = 96, ADIM = 15, GUN_SONU = 1440, SON_BITIS = 1425; // bitis en gec 23:45 (00:00 ertesi gune kayar)

   var secili = BUGUN, durumF = '', olaylar = [], istekNo = 0, cizilenGun = '', detayOlay = null, dp = null, dpProg = false;

   /* ---------- yardımcılar ---------- */
   function esc(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
   function iki(n){ return ('0'+n).slice(-2); }
   function tarih(s){ var p = s.split('-'); return new Date(+p[0], +p[1]-1, +p[2]); }
   function ymd(d){ return d.getFullYear()+'-'+iki(d.getMonth()+1)+'-'+iki(d.getDate()); }
   function gunEkle(s,n){ var d = tarih(s); d.setDate(d.getDate()+n); return ymd(d); }
   function haftaGunu(s){ return (tarih(s).getDay()+6)%7; } // 0=Pazartesi
   function dk(s){ var p = s.split(':'); return (+p[0])*60 + (+p[1]); }
   function kisa(s){ var p = s.split('-'); return p[2]+'.'+p[1]+'.'+p[0]; }
   function uzun(s){ var d = tarih(s); return d.getDate()+' '+AYLAR[d.getMonth()]+' '+d.getFullYear()+', '+GUNLER[haftaGunu(s)]; }

   function baslikYaz(){
      $('#ogt_aralik').text(uzun(secili));
      dpProg = true;
      if (dp) { try { dp.selectDate(tarih(secili)); } catch(e){} }
      dpProg = false;
      $('#ogt_tarih').val(secili);
   }

   /* ---------- veri ---------- */
   function yukle(){
      var no = ++istekNo;
      baslikYaz();
      $('#ogt_alan').addClass('yukleniyor').toggleClass('gun-degisti', cizilenGun !== secili);
      $.ajax({ url:'/isletmeyonetim/ongorusme-takvim-verileri', dataType:'json', cache:false, data:{ sube:SUBE, start:secili, end:secili } })
         .done(function(r){
            if (no !== istekNo) return;
            olaylar = r || [];
            ciz(cizilenGun !== secili); // ayni gunde (isaret/kayit sonrasi) kaydirma korunur
            cizilenGun = secili;
         })
         .fail(function(){
            if (no !== istekNo) return;
            olaylar = []; cizilenGun = '';
            $('#ogt_alan').html('<div class="ogt-hata">Ön görüşmeler yüklenemedi. Sayfayı yenileyip tekrar deneyin.</div>');
         })
         .always(function(){ if (no === istekNo) $('#ogt_alan').removeClass('yukleniyor gun-degisti'); });
   }
   function personelListesi(){
      var pid = $('#ogt_personel').val();
      return pid ? olaylar.filter(function(o){ return String(o.personel_id) === String(pid); }) : olaylar;
   }
   function sirala(l){ return l.slice().sort(function(a,b){ return (a.tarih+(a.saat||'')).localeCompare(b.tarih+(b.saat||'')); }); }

   function uyar(o, d){ return d in SATIS_AD ? (o.kat !== 'ertelendi' && o.satis === d) : o.kat === d; }
   function ciz(ilk){
      var liste = personelListesi();
      $('#ogt_filtre button').each(function(){
         var d = $(this).attr('data-d');
         $(this).find('b').text(d ? liste.filter(function(o){ return uyar(o, d); }).length : liste.length);
      });
      if (durumF) liste = liste.filter(function(o){ return uyar(o, durumF); });
      liste = sirala(liste);

      var html = '';
      if (!liste.length) html += '<div class="ogt-bos">'+(olaylar.length ? 'Bu filtreye uyan ön görüşme yok.' : 'Bu günde ön görüşme yok.')+'</div>';
      html += izgaraHtml(liste);
      var $eski = $('#ogt_alan .ogt-kaydir'), st = $eski.scrollTop(), sl = $eski.scrollLeft();
      $('#ogt_alan').html(html);
      var $k = $('#ogt_alan .ogt-kaydir');
      $k.scrollLeft(sl || 0);
      if (ilk) kaydir($k, liste); else $k.scrollTop(st || 0);
   }

   function ipucu(o){ return (o.saat ? o.saat+(o.bitis ? ' - '+o.bitis : '')+' · ' : '')+o.musteri+' · '+o.neden+' · '+o.personel+' · '+DURUM_AD[o.kat]+(o.kat === 'ertelendi' ? ': '+yeniZaman(o) : '')+(o.satis ? ' · '+SATIS_AD[o.satis] : ''); }
   function yeniZaman(o){ return kisa(o.yeni_tarih)+(o.yeni_saat ? ' '+o.yeni_saat : ''); }
   function stil(o){ return o.renk ? '--z:'+esc(o.renk)+';--y:'+esc(o.yazi)+';' : ''; }
   // Telefonla ara: panelin ortak akışı (custom.js .cm-ara-islem: softphone, yoksa dahili üzerinden)
   function araOzellik(o){ return 'data-user="'+(o.ara_no ? '' : o.user_id)+'" data-numara="'+esc(o.ara_no)+'"'; }
   function araIkon(o){ return o.aranir ? '<button type="button" class="ogt-ara cm-ara-islem" '+araOzellik(o)+' title="Telefonla ara" aria-label="'+esc(o.musteri)+' kişisini ara"><i class="fa fa-phone"></i></button>' : ''; }
   function rozet(o){
      if (o.kat === 'ertelendi') return '<span class="ogt-mini ert">Ertelendi: '+kisa(o.yeni_tarih)+'</span>';
      return o.satis ? '<span class="ogt-mini">'+(o.satis === 'satildi' ? '✓ ' : '✕ ')+SATIS_AD[o.satis]+'</span>' : '';
   }

   /* ---------- IZGARA ---------- */
   function sutunlar(liste){
      var s = [];
      var secPid = $('#ogt_personel').val(), gorulen = {};
      function ekle(id, ad, bg, yazi){
         if (gorulen[id]) return; gorulen[id] = 1;
         var l = liste.filter(function(o){ return o.personel_id === id; });
         s.push({ gun:secili, pid:id || '', renk:bg ? '--bz:'+bg+';--by:'+yazi : '', ust:esc(ad)+'<small>'+l.filter(function(o){ return o.kat !== 'ertelendi'; }).length+' görüşme</small>', l:l });
      }
      PERSONELLER.forEach(function(p){ if (!secPid || String(p.id) === String(secPid)) ekle(p.id, p.ad, p.bg, p.yazi); });
      if (!s.length) s.push({ gun:secili, pid:'', ust:'Ön Görüşmeler', l:liste });
      return s;
   }
   function hhmm(m){ return iki(Math.floor(m/60))+':'+iki(m%60); }
   function py(dkk){ return dkk*SAAT_H/60; }
   // Salonun o gunku calisma saati (randevu takvimi kurali: 00:00 baslangic=06:00, 00:00 bitis=20:00)
   function calisma(g){
      var c = CALISMA[haftaGunu(g)+1];
      if (!c || !c.b || !c.e) return { acik:true, b:480, e:GUN_SONU };
      return { acik:c.acik, b:c.b === '00:00' ? 360 : dk(c.b), e:c.e === '00:00' ? 1200 : dk(c.e) };
   }
   // Izgara: calisma saati baslangici (daha erken kayit varsa o) -> gun sonu. Dakika doner.
   function saatAraligi(liste){
      var b = calisma(secili).b;
      liste.forEach(function(o){ if (o.saat) b = Math.min(b, zaman(o).b); });
      return [Math.floor(b/ADIM)*ADIM, GUN_SONU];
   }
   function zaman(o){
      var b = dk(o.saat), e = o.bitis ? dk(o.bitis) : b+60;
      if (e <= b) e = Math.min(GUN_SONU, b+60); // gece yarisini asan / bozuk bitis
      return { b:b, e:e };
   }
   // Cakisan gorusmeleri yan yana serit olarak yerlestir
   function yerlestir(l){
      var ol = l.filter(function(o){ return o.saat; }).map(function(o){ var z = zaman(o); return { o:o, b:z.b, e:Math.max(z.e, z.b+ADIM), s:0, n:1 }; });
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
      var s = sutunlar(liste), sa = saatAraligi(liste);
      var saatsizVar = liste.some(function(o){ return !o.saat; });
      var h = '<div class="ogt-kaydir" data-bas="'+sa[0]+'"><div class="ogt-izgara" style="grid-template-columns:54px repeat('+s.length+',minmax(170px,1fr))">';
      h += '<div class="ogt-kb kose"></div>';
      s.forEach(function(k){ h += '<div class="ogt-kb'+(k.renk ? ' renkli" style="'+esc(k.renk) : '')+'">'+k.ust+'</div>'; });
      if (saatsizVar) {
         h += '<div class="ogt-saatsiz kose">Saatsiz</div>';
         s.forEach(function(k){
            h += '<div class="ogt-saatsiz">';
            k.l.forEach(function(o){ if (!o.saat) h += '<div class="ogt-e '+o.kat+(o.aranir ? ' arali' : '')+'" tabindex="0" role="button" data-id="'+o.id+'" style="'+stil(o)+'" title="'+esc(ipucu(o))+'">'+araIkon(o)+'<span class="ad">'+esc(o.musteri)+'</span> '+rozet(o)+'</div>'; });
            h += '</div>';
         });
      }
      h += '<div class="ogt-saatler">';
      for (var i = sa[0]; i < sa[1]; i += ADIM) h += '<div'+(i%60 ? '' : ' class="tam"')+'>'+hhmm(i)+'</div>';
      h += '</div>';
      var simdi = new Date(), sdk = simdi.getHours()*60+simdi.getMinutes();
      s.forEach(function(k){
         var c = calisma(k.gun);
         function kapali(b, e){ b = Math.max(b, sa[0]); e = Math.min(e, sa[1]); return e > b ? '<div class="ogt-kapali" style="top:'+py(b-sa[0])+'px;height:'+py(e-b)+'px"></div>' : ''; }
         h += '<div class="ogt-sutun'+(YETKI ? ' ekle' : '')+'" data-gun="'+k.gun+'" data-pid="'+k.pid+'" style="height:'+py(sa[1]-sa[0])+'px;background-position-y:'+(-py(sa[0]%60))+'px">';
         h += c.acik ? kapali(sa[0], c.b)+kapali(c.e, sa[1]) : kapali(sa[0], sa[1]);
         if (k.gun === BUGUN && sdk >= sa[0] && sdk < sa[1]) h += '<div class="ogt-simdi" style="top:'+py(sdk-sa[0])+'px"></div>';
         yerlestir(k.l).forEach(function(y){
            var o = y.o, boy = Math.min(y.e, sa[1])-y.b, w = 100/y.n, z = zaman(o), t = tasinir(o);
            h += '<div class="ogt-e '+o.kat+(t ? ' tasinir' : '')+(o.aranir ? ' arali' : '')+'" tabindex="0" role="button" data-id="'+o.id+'" title="'+esc(ipucu(o))+'" style="'+stil(o)+'top:'+py(y.b-sa[0])+'px;height:'+(py(boy)-1)+'px;left:'+(y.s*w)+'%;width:calc('+w+'% - 2px)">'+
               araIkon(o)+(boy < 30 ? '<div class="ust"><span class="s">'+o.saat+'</span> <span class="ad">'+esc(o.musteri)+'</span> '+rozet(o)+'</div>'
                  : '<div class="s ust">'+o.saat+' - '+hhmm(z.e%GUN_SONU)+'</div><div class="ad">'+esc(o.musteri)+'</div>')+
               (boy >= 30 && rozet(o) ? '<div>'+rozet(o)+'</div>' : '')+
               (boy >= 40 ? '<div>'+esc(o.neden)+'</div>' : '')+
               (boy >= 50 && o.oda && !rozet(o) ? '<div>'+esc(o.oda)+'</div>' : '')+
               (t ? '<div class="ogt-tut"></div>' : '')+'</div>';
         });
         h += '</div>';
      });
      return h + '</div></div>';
   }
   // Ilk acilista en erken gorusme gorunsun; hic yoksa ve bugun ekrandaysa simdiki saat
   function kaydir($k, liste){
      var bas = parseInt($k.attr('data-bas'),10), hedef = null;
      liste.forEach(function(o){ if (o.saat && (hedef === null || dk(o.saat)-30 < hedef)) hedef = dk(o.saat)-30; });
      if (hedef === null && secili === BUGUN) { var s = new Date(); hedef = s.getHours()*60+s.getMinutes()-60; }
      if (hedef !== null) $k.scrollTop(Math.max(0,py(hedef-bas)));
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
      var o = olaylar.filter(function(x){ return String(x.id) === id; })[0];
      if (!o) return;
      detayOlay = o;
      $('#ogt_d_ad').text(o.musteri);
      $('#ogt_d_durum').attr('class','ogt-e ogt-rozet '+o.kat).attr('style', stil(o)).text(DURUM_AD[o.kat]+(o.satis ? ' · '+SATIS_AD[o.satis] : ''));
      var satir = o.kat === 'ertelendi' ? [
         ['Eski tarih', uzun(o.tarih)+(o.saat ? ', '+o.saat : '')],
         ['Yeni tarih', uzun(o.yeni_tarih)+(o.yeni_saat ? ', '+o.yeni_saat : '')],
         ['Görüşmeyi yapan', o.personel],
         ['Sebep', o.neden],
         ['Telefon', o.telefon],
         ['Erteleme işlemi', o.olusturma]
      ] : [
         ['Tarih', uzun(o.tarih)],
         ['Saat', o.saat ? o.saat+(o.bitis ? ' – '+o.bitis : '') : 'Belirtilmemiş'],
         ['Görüşmeyi yapan', o.personel],
         ['Oda', o.oda],
         ['Sebep', o.neden],
         ['Telefon', o.telefon],
         ['Referans', o.referans],
         ['Açıklama', o.aciklama],
         ['Satış olmama nedeni', o.satis === 'satilmadi' ? (o.neden_yok || 'Belirtilmemiş') : ''],
         ['Oluşturma', o.olusturma]
      ];
      $('#ogt_d_bilgi').html(satir.filter(function(s){ return s[1]; }).map(function(s){ return '<dt>'+s[0]+'</dt><dd>'+esc(s[1])+'</dd>'; }).join(''));
      var b = o.aranir ? '<button type="button" class="ogt-btn ogt-btn-yesil cm-ara-islem" '+araOzellik(o)+'><i class="fa fa-phone"></i> Ara</button>' : '';
      if (o.user_id) b += '<a class="ogt-btn" target="_blank" rel="noopener" href="/isletmeyonetim/musteridetay/'+o.user_id+'?sube='+SUBE+'"><i class="fa fa-user"></i> Müşteri</a>';
      if (o.kat === 'ertelendi') b += '<button type="button" class="ogt-btn" data-is="yeni_tarih"><i class="fa fa-calendar"></i> Yeni Tarihe Git</button>';
      else if (ARAMA && o.user_id) {
         b += '<button type="button" class="ogt-btn" data-is="arama_ekle"><i class="fa fa-calendar"></i> Arama Randevusu</button>';
      }
      if (o.sonucsuz) {
         if (YETKI && o.randevulu) b += '<button type="button" class="ogt-btn" data-is="duzenle"><i class="fa fa-edit"></i> Düzenle</button>';
         if (o.tur) b += '<button type="button" class="ogt-btn ogt-btn-yesil" data-is="satildi"><i class="fa fa-check"></i> Satış Yapıldı</button>';
         b += '<button type="button" class="ogt-btn ogt-btn-kirmizi" data-is="satilmadi"><i class="fa fa-times"></i> Satış Yapılmadı</button>';
      }
      $('#ogt_d_katilim').html(katilimHtml(o)).toggle(!!katilimHtml(o));
      $('#ogt_d_alt').html(b).toggle(!!b);
      $('#ogt_detay').modal('show');
   }
   /* Katılım: Gelecek / Geldi / Gelmedi. İşaretliyse "… İşaretini Kaldır" (seçili olana tekrar basmak da kaldırır). */
   function katilimHtml(o){
      if (!KATILIM || !o.randevu_id || o.kat === 'iptal' || o.kat === 'ertelendi') return '';
      function dugme(k, renk){ return '<button type="button" data-kat="'+k+'" class="'+(o.kat === k ? 'secili' : '')+'" aria-pressed="'+(o.kat === k)+'" style="--b:'+renk+'">'+(o.kat === k ? '✓ ' : '')+DURUM_AD[k]+'</button>'; }
      var isaretli = o.kat === 'geldi' || o.kat === 'gelmedi';
      return (!isaretli && o.onayli ? dugme('gelecek','#EF7400') : '')+dugme('geldi','#008000')+dugme('gelmedi','#e00000')+
         (o.kat !== 'bekliyor' ? '<button type="button" class="kaldir" data-kat="'+o.kat+'"><i class="fa fa-undo"></i> '+DURUM_AD[o.kat]+' İşaretini Kaldır</button>' : '');
   }
   $('#ogt_d_katilim').on('click', 'button', function(){
      var k = $(this).attr('data-kat'), o = detayOlay, kaldir = o && o.kat === k;
      if (!o) return;
      detayKapat(function(){
         if (k === 'gelecek') return gelecekIsaretle(o, !kaldir);
         // Randevu takvimiyle AYNI akis (onay, KVKK, dogrulama kodu): custom.js'teki delege baglantiyi tetikle
         $('<a href="#" style="display:none" data-index-number=""></a>').attr({ name:k+(kaldir ? '_isareti_kaldir' : '_isaretle'), 'data-value':o.randevu_id }).appendTo('body').trigger('click').remove();
      });
   });
   function gelecekIsaretle(o, isaretle){
      $.ajax({ type:'POST', url:'/isletmeyonetim/randevuOnDurumIsaretle', dataType:'json', headers:{ 'X-CSRF-TOKEN':TOKEN },
         data:{ sube:SUBE, randevuid:o.randevu_id, alan:'gelecek', deger:isaretle ? 1 : 0 } })
         .done(function(r){ if (typeof swal === 'function') swal({ type:'success', title:'Başarılı', text:r.mesaj, timer:1500, showConfirmButton:false }); })
         .fail(function(x){ if (typeof swal === 'function') swal('Uyarı', (x.responseJSON && x.responseJSON.mesaj) || 'İşlem yapılamadı, lütfen tekrar deneyin.', 'warning'); })
         .always(function(){ yukle(); });
   }
   // Detay penceresi TAM kapandiktan sonra devam et (ust uste modal/backdrop karismasin)
   function detayKapat(sonra){
      $('#ogt_detay').one('hidden.bs.modal', function(){ setTimeout(sonra, 30); }).modal('hide');
   }
   $(document).on('click', '#ogt_d_alt [data-is]', function(){
      var is = $(this).attr('data-is'), o = detayOlay;
      if (!o) return;
      detayKapat(function(){
         if (is === 'yeni_tarih') {
            git(o.yeni_tarih);
         } else if (is === 'arama_ekle') {
            window.aramaRandevuEkleAc({ musteriId:o.user_id, musteriAd:o.musteri, personelId:o.personel_id });
         } else if (is === 'duzenle') {
            if (typeof modalbaslikata === 'function') modalbaslikata('Ön Görüşme Düzenleme','');
            $('#ongorusmeformu')[0].reset();
            secenekEkle($('#gorusmeyi_yapan'), o.personel_id, o.personel);
            secenekEkle($('#musteri_select_list'), o.user_id, o.musteri);
            $('#ogt_duzenle_tetik').attr('data-value', o.gorusme_id).trigger('click');
            $('#ongorusme-modal').modal('show');
         } else if (typeof ongorusmeseansgirdikontrol === 'function') {
            ongorusmeseansgirdikontrol(o.gorusme_id, is === 'satildi', is === 'satildi' ? o.tur : 1);
         }
      });
   });

   /* ---------- sürükle / uzat (randevu takvimiyle aynı uç: randevuguncelledragdropresize) ---------- */
   function tasinir(o){ return TASI && o.saat && o.sonucsuz && o.kat !== 'iptal' && o.randevu_id && o.kalem_id; }
   var sur = null;
   $('#ogt_alan').on('mousedown', '.ogt-sutun .ogt-e.tasinir', function(e){
      if (e.which !== 1 || araMi(e)) return;
      var id = $(this).attr('data-id'), o = olaylar.filter(function(x){ return String(x.id) === id; })[0], z = zaman(o);
      var $s = $(this).closest('.ogt-sutun');
      sur = { o:o, $e:$(this), boy:$(e.target).hasClass('ogt-tut'), b:z.b, e:z.e, sure:z.e-z.b, $s:$s, $ilk:$s,
         x:e.clientX, y:e.clientY, dy:e.clientY - this.getBoundingClientRect().top, bas:parseInt($s.closest('.ogt-kaydir').attr('data-bas'),10), basladi:false };
      e.preventDefault(); // metin secimi baslamasin
   });
   function surBasla(){
      sur.basladi = true;
      $('body').addClass(sur.boy ? 'ogt-boy' : 'ogt-surukle');
      sur.$g = sur.$e.clone().addClass('golge').removeAttr('data-id tabindex').css({ left:0, width:'calc(100% - 2px)' });
      sur.$e.addClass('kaynak');
   }
   function surKonum(y){
      var r0 = sur.$s[0].getBoundingClientRect().top;
      function dkk(px){ return sur.bas + Math.round(px*60/SAAT_H/ADIM)*ADIM; }
      if (sur.boy) sur.e = Math.max(sur.b+ADIM, Math.min(SON_BITIS, dkk(y - r0)));
      else {
         sur.b = Math.max(sur.bas, Math.min(SON_BITIS - sur.sure, dkk(y - sur.dy - r0)));
         sur.e = sur.b + sur.sure;
      }
      sur.$g.css({ top:py(sur.b-sur.bas)+'px', height:(py(sur.e-sur.b)-1)+'px' }).appendTo(sur.$s);
      var ust = sur.$g.children('.s'), t = hhmm(sur.b)+' - '+hhmm(sur.e);
      if (ust.length) ust.text(t); else sur.$g.html('<div class="s">'+t+'</div>');
   }
   $(document).on('mousemove.ogt', function(e){
      if (!sur) return;
      if (!sur.basladi) {
         if (Math.abs(e.clientX-sur.x) + Math.abs(e.clientY-sur.y) < 5) return;
         surBasla();
      }
      if (!sur.boy) {
         $('#ogt_alan .ogt-sutun').each(function(){
            var r = this.getBoundingClientRect(), pid = $(this).attr('data-pid');
            if (e.clientX < r.left || e.clientX >= r.right) return;
            // Yalniz kendi sutununa ya da aktif bir personele birakilabilir
            if (this !== sur.$ilk[0] && !PERSONELLER.some(function(p){ return String(p.id) === pid; })) { sur.$s = sur.$ilk; return; }
            sur.$s = $(this);
         });
      }
      var $k = sur.$s.closest('.ogt-kaydir'), kr = $k[0].getBoundingClientRect();
      if (e.clientY > kr.bottom-28) $k.scrollTop($k.scrollTop()+14); else if (e.clientY < kr.top+70) $k.scrollTop($k.scrollTop()-14);
      if (e.clientX > kr.right-20) $k.scrollLeft($k.scrollLeft()+14); else if (e.clientX < kr.left+60) $k.scrollLeft($k.scrollLeft()-14);
      surKonum(e.clientY);
   });
   function surBitir(){ $('body').removeClass('ogt-surukle ogt-boy'); var v = sur; sur = null; return v; }
   $(document).on('mouseup.ogt', function(){
      if (!sur) return;
      var v = surBitir();
      if (!v.basladi) return; // normal tiklama: detay acilir
      // Birakma sonrasi gelen click detay / yeni kayit penceresi acmasin
      function yut(ev){ ev.stopPropagation(); ev.preventDefault(); }
      document.addEventListener('click', yut, true);
      setTimeout(function(){ document.removeEventListener('click', yut, true); }, 0);
      var z = zaman(v.o), gun = v.$s.attr('data-gun'), pid = '';
      if (v.$s[0] !== v.$ilk[0]) pid = v.$s.attr('data-pid');
      if (v.b === z.b && v.e === z.e && gun === v.o.tarih && !pid) return ciz(false);
      tasiOnay(v.o, gun, hhmm(v.b), hhmm(v.e), pid);
   });
   $(document).on('keydown', function(e){ if (sur && e.which === 27) { var v = surBitir(); if (v.basladi) ciz(false); } });
   function tasiOnay(o, gun, b, e, pid){
      var yeniP = pid ? PERSONELLER.filter(function(p){ return String(p.id) === pid; })[0] : null;
      var metin = o.musteri+' · '+uzun(gun)+' · '+b+' - '+e+(yeniP ? ' · '+yeniP.ad : '');
      function gonder(){
         $('#ogt_alan').addClass('yukleniyor');
         $.ajax({ type:'POST', url:'/isletmeyonetim/randevuguncelledragdropresize', dataType:'text', headers:{ 'X-CSRF-TOKEN':TOKEN },
            data:{ sube:SUBE, randevu_id:o.randevu_id, id:o.kalem_id, personel_id:pid, start:gun+'T'+b+':00', end:gun+'T'+e+':00', oda_id:'', cihaz_id:'', yeni_hizmet_id:'' } })
            .done(function(r){
               try { var j = JSON.parse(r); if (j && j.eklenemez) swal({ type:'error', title:'Taşınamadı', html:j.eklenemez }); } catch(x){}
            })
            .fail(function(x){ if (typeof swal === 'function') swal({ type:'error', title:'Taşınamadı', text:x.status === 403 ? 'Randevu düzenleme yetkiniz yok.' : 'Kaydedilemedi, takvim yenilendi.' }); })
            .always(function(){ yukle(); });
      }
      if (typeof swal !== 'function') { if (confirm(metin)) gonder(); else ciz(false); return; }
      swal({ title:'Onayla', text:metin, type:'warning', showCancelButton:true, confirmButtonColor:'#00bc8c', confirmButtonText:'Evet', cancelButtonText:'Hayır',
         confirmButtonClass:'btn btn-success', cancelButtonClass:'btn btn-danger' })
         .then(function(x){ if (x === true || (x && x.value)) gonder(); else ciz(false); }, function(){ ciz(false); });
   }

   /* ---------- olaylar ---------- */
   function git(g){ secili = g; yukle(); }
   function adim(n){ git(gunEkle(secili, n)); }
   $('#ogt_geri').on('click', function(){ adim(-1); });
   $('#ogt_ileri').on('click', function(){ adim(1); });
   $('#ogt_bugun').on('click', function(){ git(BUGUN); });
   $('#ogt_yeni').on('click', function(){ yeniAc(secili, '', $('#ogt_personel').val()); });
   $('#ogt_personel').on('change', function(){ ciz(false); });
   $('#ogt_filtre').on('click', 'button', function(){
      durumF = $(this).attr('data-d');
      $('#ogt_filtre button').removeClass('aktif'); $(this).addClass('aktif');
      ciz(false);
   });
   function araMi(e){ return $(e.target).closest('.cm-ara-islem').length > 0; } // arama butonu: kayıt/boş saat tıklaması sayılmaz
   $('#ogt_alan').on('click', '.ogt-e', function(e){ if (araMi(e)) return; e.stopPropagation(); detayAc($(this).attr('data-id')); });
   $('#ogt_alan').on('keydown', '.ogt-e', function(e){ if (araMi(e)) return; if (e.which === 13 || e.which === 32) { e.preventDefault(); detayAc($(this).attr('data-id')); } });
   $('#ogt_alan').on('click', '.ogt-sutun.ekle', function(e){
      if (araMi(e)) return;
      var bas = parseInt($(this).closest('.ogt-kaydir').attr('data-bas'),10);
      var m = bas + Math.floor((e.clientY - this.getBoundingClientRect().top)*60/SAAT_H/ADIM)*ADIM;
      m = Math.max(0, Math.min(1425, m));
      yeniAc($(this).attr('data-gun'), hhmm(m), $(this).attr('data-pid'));
   });

   // Kayit / duzenleme / satis sonucu basarili olunca takvimi tazele. Prefilter: custom.js'in kendi
   // success'inden ONCE baglanir; oradaki bir hata tazelemeyi engelleyemez.
   $.ajaxPrefilter(function(s, o, xhr){
      if (/\/isletmeyonetim\/(ongorusmeekleduzenle|ongorusmesatisyapildi|ongorusmesatisyapilmadi|randevugeldiisaretle|randevuyagelmedi|randevuGeldiGelmediIsaretiKaldir)\b/.test(s.url || '')) xhr.done(function(){ yukle(); });
   });

   if ($.fn.select2 && $('#ogt_personel').length) {
      $('#ogt_personel').select2({ width:'100%', dropdownParent:$('.ogt-psec'),
         language:{ noResults:function(){ return 'Personel bulunamadı'; }, searching:function(){ return 'Aranıyor...'; } } });
   }

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
