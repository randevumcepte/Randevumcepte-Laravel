@extends("layout.layout_isletmeadmin")
@section("content")

<style>
/* ===== Çağrı Merkezi Dashboard — modern tema ===== */
.cm-wrap{ --mor1:#5C008E; --mor2:#7B2FB8; --mor3:#9D5DC8; }

/* Başlık şeridi */
.cm-hero{
   background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%);
   border-radius:18px; padding:26px 28px; color:#fff; margin-bottom:24px;
   box-shadow:0 12px 30px -10px rgba(92,0,142,.45);
   position:relative; overflow:hidden;
}
.cm-hero:after{
   content:""; position:absolute; right:-40px; top:-40px;
   width:180px; height:180px; border-radius:50%;
   background:rgba(255,255,255,.10);
}
.cm-hero h4{ margin:0 0 6px; font-weight:700; font-size:24px; color:#fff; }
.cm-hero p{ margin:0; opacity:.92; font-size:14px; max-width:620px; }

/* Özet istatistik kartları */
.cm-stat{
   background:#fff; border-radius:16px; padding:20px;
   box-shadow:0 6px 18px -8px rgba(30,30,60,.18);
   display:flex; align-items:center; gap:16px; height:100%;
   border:1px solid #f0eef5; transition:transform .15s ease, box-shadow .15s ease;
}
.cm-stat:hover{ transform:translateY(-3px); box-shadow:0 14px 28px -10px rgba(30,30,60,.28); }
.cm-stat .ic{
   width:54px; height:54px; border-radius:14px; flex:0 0 54px;
   display:flex; align-items:center; justify-content:center;
   font-size:22px; color:#fff;
}
.cm-stat .val{ font-size:28px; font-weight:700; line-height:1; color:#241b3a; }
.cm-stat .lbl{ font-size:13px; font-weight:600; color:#5a5570; margin-top:4px; }
.cm-stat .hint{ font-size:11px; color:#9a95ab; margin-top:2px; display:block; }

.ic-mor{ background:linear-gradient(135deg,#5C008E,#9D5DC8); }
.ic-yesil{ background:linear-gradient(135deg,#1b9e4b,#37c871); }
.ic-kirmizi{ background:linear-gradient(135deg,#d33,#ff6b6b); }
.ic-mavi{ background:linear-gradient(135deg,#1565c0,#42a5f5); }

/* Personel kartı */
.cm-card{
   background:#fff; border-radius:18px; padding:0; cursor:pointer;
   box-shadow:0 6px 20px -10px rgba(30,30,60,.2);
   border:1px solid #f0eef5; overflow:hidden; height:100%;
   transition:transform .15s ease, box-shadow .15s ease;
}
.cm-card:hover{ transform:translateY(-4px); box-shadow:0 18px 36px -12px rgba(92,0,142,.35); }
.cm-card-head{
   background:linear-gradient(120deg,#5C008E,#7B2FB8);
   padding:16px 18px; color:#fff; display:flex; align-items:center; gap:12px;
}
.cm-avatar{
   width:42px; height:42px; border-radius:50%; flex:0 0 42px;
   background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
   font-size:20px; font-weight:700;
}
.cm-card-head .nm{ font-weight:700; font-size:16px; margin:0; line-height:1.1; }
.cm-card-head .dk{
   margin-left:auto; background:rgba(255,255,255,.20); border-radius:20px;
   padding:4px 12px; font-size:12px; font-weight:600; white-space:nowrap;
}
.cm-card-body{ padding:16px 18px 18px; }

/* İlerleme */
.cm-prog-top{ display:flex; justify-content:space-between; align-items:baseline; margin-bottom:6px; }
.cm-prog-top .pct{ font-weight:700; color:#5C008E; font-size:15px; }
.cm-prog-top .txt{ font-size:12px; color:#8a85a0; }
.cm-prog{ height:9px; border-radius:20px; background:#efeaf6; overflow:hidden; }
.cm-prog>span{ display:block; height:100%; border-radius:20px;
   background:linear-gradient(90deg,#7B2FB8,#9D5DC8); transition:width .6s ease; }

/* İstatistik kutucukları (kart içi) */
.cm-mini{ display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:16px; }
.cm-mini .box{ background:#faf9fc; border-radius:12px; padding:10px 6px; text-align:center; }
.cm-mini .box .n{ font-size:20px; font-weight:700; line-height:1; }
.cm-mini .box .t{ font-size:11px; color:#8a85a0; margin-top:4px; display:block; font-weight:600; }
.cm-mini2{ display:grid; grid-template-columns:repeat(2,1fr); gap:8px; margin-top:8px; }
.n-yesil{ color:#1b9e4b; } .n-kirmizi{ color:#d33; } .n-mavi{ color:#1565c0; }
.n-koyu{ color:#241b3a; } .n-gri{ color:#a7a2b5; }

/* ===== Modal — görüşme dökümü ===== */
#personel_detay_modal .modal-dialog{ max-width:840px; }
#personel_detay_modal .modal-content{ border:none; border-radius:20px; overflow:hidden; box-shadow:0 30px 70px -20px rgba(40,10,70,.55); }
#personel_detay_modal .modal-header{
   background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 60%,#9D5DC8 100%);
   color:#fff; border:none; padding:20px 22px; align-items:center; position:relative; overflow:hidden;
}
#personel_detay_modal .modal-header:after{
   content:""; position:absolute; right:-30px; top:-30px; width:130px; height:130px;
   border-radius:50%; background:rgba(255,255,255,.10);
}
#personel_detay_modal .modal-header .close{ color:#fff; opacity:.9; text-shadow:none; font-size:26px; position:relative; z-index:2; }
.pd-head-wrap{ display:flex; align-items:center; gap:14px; position:relative; z-index:2; }
.pd-head-av{
   width:46px; height:46px; border-radius:50%; flex:0 0 46px;
   background:rgba(255,255,255,.20); display:flex; align-items:center; justify-content:center;
   font-size:20px; font-weight:700; color:#fff;
}
.pd-head-tit{ font-weight:700; font-size:18px; line-height:1.1; margin:0; }
.pd-head-sub{ font-size:12px; opacity:.85; margin-top:2px; }
#personel_detay_modal .modal-body{ padding:18px 22px 22px; background:#faf9fc; max-height:65vh; overflow-y:auto; }

/* Görüşme satırları (kart liste) */
.pd-row{
   background:#fff; border:1px solid #efeaf6; border-radius:14px;
   padding:12px 14px; margin-bottom:10px; display:flex; align-items:center; gap:12px;
   transition:box-shadow .15s ease, transform .15s ease;
}
.pd-row:hover{ box-shadow:0 8px 20px -10px rgba(92,0,142,.25); transform:translateY(-1px); }
.pd-row .pd-main{ flex:1; min-width:0; }
.pd-row .pd-musteri{ font-weight:700; color:#241b3a; font-size:14px; }
.pd-row .pd-tarih{ font-size:12px; color:#9a95ab; margin-top:2px; }
.pd-row .pd-not{ font-size:12.5px; color:#6a6580; margin-top:5px; line-height:1.4; }
.pd-row .pd-not i{ color:#bcb4cf; margin-right:5px; }

/* Sonuç rozeti */
.pd-badge{ font-size:11.5px; font-weight:700; padding:5px 11px; border-radius:20px; white-space:nowrap; }
.pd-b-yesil{ background:#e4f7ec; color:#1b9e4b; }
.pd-b-kirmizi{ background:#fdecec; color:#d33; }
.pd-b-mavi{ background:#e7f1fd; color:#1565c0; }
.pd-b-gri{ background:#f0eef5; color:#8a85a0; }
.pd-b-turuncu{ background:#fff2e3; color:#e07a1a; }

/* Ses oynat butonu */
.pd-play{
   width:38px; height:38px; flex:0 0 38px; border-radius:50%;
   background:linear-gradient(135deg,#5C008E,#9D5DC8); color:#fff;
   display:flex; align-items:center; justify-content:center; font-size:14px;
   text-decoration:none; box-shadow:0 4px 12px -4px rgba(92,0,142,.5);
}
.pd-play:hover{ color:#fff; opacity:.9; }

/* Modal boş / yükleniyor durumu */
.pd-empty{ text-align:center; padding:46px 20px; color:#9a95ab; }
.pd-empty .ic{
   width:78px; height:78px; border-radius:50%; margin:0 auto 16px;
   background:linear-gradient(135deg,#f3eefa,#e9def5);
   display:flex; align-items:center; justify-content:center; font-size:34px; color:#b69ad6;
}
.pd-empty .t1{ font-weight:700; color:#5a5570; font-size:15px; }
.pd-empty .t2{ font-size:13px; margin-top:4px; }

/* ===== Modal — özet şerit ===== */
.pd-ozet{ display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:16px; }
.pd-oz{ background:#fff; border:1px solid #efeaf6; border-radius:14px; padding:12px 10px; text-align:center; position:relative; }
.pd-oz .oz-ic{ font-size:15px; margin-bottom:4px; }
.pd-oz .oz-n{ font-size:20px; font-weight:800; line-height:1; color:#241b3a; }
.pd-oz .oz-t{ font-size:11px; color:#8a85a0; margin-top:4px; font-weight:600; display:block; }
.pd-oz.mor{ background:linear-gradient(135deg,#5C008E,#7B2FB8); border:none; }
.pd-oz.mor .oz-n, .pd-oz.mor .oz-t, .pd-oz.mor .oz-ic{ color:#fff; }
.pd-oz.mor .oz-t{ opacity:.9; }
.c-yesil{ color:#1b9e4b; } .c-kirmizi{ color:#d33; } .c-mavi{ color:#1565c0; }
.c-turuncu{ color:#e07a1a; } .c-gri{ color:#a7a2b5; }
@media (max-width:680px){ .pd-ozet{ grid-template-columns:repeat(3,1fr); } }

/* ===== Modal — filtre çipleri ===== */
.pd-filtre{ display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
.pd-chip{
   border:1px solid #e3dcf0; background:#fff; color:#6a6580; border-radius:20px;
   padding:6px 14px; font-size:12.5px; font-weight:600; cursor:pointer; transition:all .12s ease;
}
.pd-chip:hover{ border-color:#9D5DC8; color:#5C008E; }
.pd-chip.aktif{ background:linear-gradient(120deg,#5C008E,#7B2FB8); border-color:transparent; color:#fff; }
.pd-chip .sayi{ opacity:.7; font-weight:700; margin-left:3px; }

/* ===== Modal — görüşme zaman çizelgesi (kart) ===== */
.pd-call{
   background:#fff; border:1px solid #efeaf6; border-left-width:4px; border-radius:14px;
   padding:13px 15px; margin-bottom:11px; transition:box-shadow .15s ease, transform .15s ease;
}
.pd-call:hover{ box-shadow:0 10px 24px -12px rgba(92,0,142,.28); transform:translateY(-1px); }
.pd-call.bl-yesil{ border-left-color:#1b9e4b; }
.pd-call.bl-kirmizi{ border-left-color:#d33; }
.pd-call.bl-mavi{ border-left-color:#1565c0; }
.pd-call.bl-turuncu{ border-left-color:#e07a1a; }
.pd-call.bl-gri{ border-left-color:#cfc7df; }
.pd-call-top{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.pd-call-mus{ font-weight:700; color:#241b3a; font-size:14.5px; }
.pd-call-meta{ display:flex; align-items:center; gap:12px; margin-left:auto; flex-wrap:wrap; }
.pd-call-meta .m{ font-size:12px; color:#9a95ab; white-space:nowrap; }
.pd-call-meta .m i{ margin-right:4px; color:#bcb4cf; }
.pd-call-sure{ background:#f3eefa; color:#5C008E; font-weight:700; border-radius:8px; padding:3px 9px; font-size:12px; white-space:nowrap; }
.pd-call-kat{ background:#e7f1fd; color:#1565c0; font-weight:700; border-radius:8px; padding:3px 9px; font-size:12px; white-space:nowrap; }
.pd-call-kat i{ margin-right:3px; }
.pd-call-not{ font-size:13px; color:#574f6b; margin-top:9px; line-height:1.45; background:#faf9fc; border-radius:10px; padding:9px 11px; }
.pd-call-not i{ color:#b69ad6; margin-right:6px; }
.pd-call-audio{ margin-top:10px; }
.pd-call-audio audio{ width:100%; height:38px; border-radius:10px; }

/* Boş / yükleniyor durumu */
.cm-empty{ text-align:center; padding:40px 20px; color:#8a85a0; }
.cm-empty i{ font-size:40px; color:#cfc7df; display:block; margin-bottom:10px; }

/* ===== Liste Segmentasyonu ===== */
.seg-kart{ background:#fff; border:1px solid #eef0f5; border-radius:18px; box-shadow:0 6px 20px -12px rgba(30,30,60,.16); padding:18px 20px; margin-top:8px; }
.seg-bas{ display:flex; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:14px; }
.seg-bas h5{ margin:0 0 3px; font-weight:700; font-size:16px; color:#241b3a; }
.seg-bas h5 .fa{ color:#7B2FB8; margin-right:6px; }
.seg-aciklama{ font-size:12.5px; color:#8a85a0; max-width:640px; display:block; }
.seg-select{ margin-left:auto; min-width:240px; border:1px solid #e3dcf0; border-radius:10px; padding:9px 12px; font-size:13px; outline:none; background:#fff; }
.seg-select:focus{ border-color:#9D5DC8; }

.seg-tablo{ display:grid; gap:10px; }
.seg-satir{ display:flex; align-items:center; gap:12px; border:1px solid #eef0f5; border-left:4px solid #cfc7df; border-radius:12px; padding:12px 14px; flex-wrap:wrap; }
.seg-satir.s-yesil{ border-left-color:#16a34a; } .seg-satir.s-kirmizi{ border-left-color:#dc2626; }
.seg-satir.s-mavi{ border-left-color:#1565c0; } .seg-satir.s-turuncu{ border-left-color:#ea580c; }
.seg-satir.s-pembe{ border-left-color:#db2777; } .seg-satir.s-gri{ border-left-color:#9aa0b0; }
.seg-ad{ font-weight:700; color:#241b3a; font-size:14px; min-width:130px; }
.seg-adet{ background:#f3eefa; color:#5C008E; font-weight:700; border-radius:20px; padding:3px 12px; font-size:12.5px; }
.seg-aksiyon{ margin-left:auto; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.seg-indir{ border:1px solid #d7d2e6; background:#fff; color:#5C008E; border-radius:9px; padding:7px 12px; font-size:12.5px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.seg-indir:hover{ border-color:#9D5DC8; background:#faf7ff; color:#5C008E; }
.seg-personel{ border:1px solid #e3dcf0; border-radius:9px; padding:7px 10px; font-size:12.5px; outline:none; background:#fff; min-width:150px; }
.seg-ata{ border:none; background:linear-gradient(120deg,#5C008E,#7B2FB8); color:#fff; border-radius:9px; padding:7px 14px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
.seg-ata:hover{ opacity:.93; } .seg-ata:disabled{ opacity:.5; cursor:not-allowed; }

/* Tek satır: segment seçim çipleri + aksiyonlar */
.seg-bar{ display:flex; align-items:center; gap:14px; flex-wrap:wrap; border:1px solid #eef0f5; border-radius:14px; padding:12px 14px; }
.seg-chips{ display:flex; gap:8px; flex-wrap:wrap; }
.seg-chip{ border:1.5px solid #e9ebf2; background:#fff; color:#5b6172; border-radius:11px; padding:8px 13px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all .12s ease; }
.seg-chip:hover{ border-color:#c4cad8; }
.seg-chip-n{ background:#f0f1f5; color:#6b7280; border-radius:20px; padding:1px 8px; font-size:11px; font-weight:800; }
.seg-chip.aktif{ color:#fff; border-color:transparent; }
.seg-chip.aktif .seg-chip-n{ background:rgba(255,255,255,.28); color:#fff; }
.seg-chip.s-yesil.aktif{ background:#16a34a; } .seg-chip.s-kirmizi.aktif{ background:#dc2626; }
.seg-chip.s-mavi.aktif{ background:#1565c0; } .seg-chip.s-turuncu.aktif{ background:#ea580c; }
.seg-chip.s-pembe.aktif{ background:#db2777; } .seg-chip.s-gri.aktif{ background:#6b7280; }
.seg-chip.bos{ opacity:.5; } /* 0 müşterili segment soluk */
.seg-bar .seg-aksiyon{ margin-left:auto; }
.seg-indir.pasif, .seg-ata.pasif{ opacity:.45; cursor:not-allowed; pointer-events:none; }
</style>

<div class="cm-wrap">

   {{-- Başlık şeridi --}}
   <div class="cm-hero">
      <h4><i class="fa fa-headphones"></i> {{ $sayfa_baslik }}</h4>
      <p>Çağrı yapan personellerinizin ilerlemesini tek ekrandan takip edin. Her personele <b>atanan data</b> (aranacak müşteri listesi) üzerinden ne kadarını aradığını, kaç görüşme ve randevu çıkardığını görürsünüz. Detaylı görüşme dökümü için bir personel kartına tıklayın.</p>
   </div>

   {{-- Özet bant --}}
   <div class="row" id="ozet_bant">
      <div class="col-md-3 col-sm-6 mb-20">
         <div class="cm-stat">
            <div class="ic ic-mor"><i class="fa fa-database"></i></div>
            <div>
               <div class="val" id="oz_atanan">0</div>
               <span class="lbl">Toplam Atanan Data</span>
               <span class="hint">Aranacak toplam müşteri</span>
            </div>
         </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-20">
         <div class="cm-stat">
            <div class="ic ic-yesil"><i class="fa fa-phone"></i></div>
            <div>
               <div class="val" id="oz_konusulan">0</div>
               <span class="lbl">Konuşulan</span>
               <span class="hint">Görüşme sağlanan kişi</span>
            </div>
         </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-20">
         <div class="cm-stat">
            <div class="ic ic-kirmizi"><i class="fa fa-ban"></i></div>
            <div>
               <div class="val" id="oz_cevapsiz">0</div>
               <span class="lbl">Cevapsız</span>
               <span class="hint">Arandı, açılmadı</span>
            </div>
         </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-20">
         <div class="cm-stat">
            <div class="ic ic-mavi"><i class="fa fa-calendar-check-o"></i></div>
            <div>
               <div class="val" id="oz_randevu">0</div>
               <span class="lbl">Arama Randevusu</span>
               <span class="hint">Görüşmeden çıkan randevu</span>
            </div>
         </div>
      </div>
   </div>

   {{-- Personel kartları --}}
   <div id="personel_kartlari" class="row">
      <div class="col-md-12"><div class="cm-empty" id="dash_yukleniyor"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div></div>
   </div>

   {{-- Liste Segmentasyonu --}}
   <div class="seg-kart">
      <div class="seg-bas">
         <div>
            <h5><i class="fa fa-filter"></i> Liste Segmentasyonu</h5>
            <span class="seg-aciklama">Bir arama listesini sonuca göre ayır (Görüşüldü / Cevapsız / Meşgul / Ulaşılamadı / Randevu / Bekleyen); segmenti <b>indir</b> ya da <b>başka personele taşı</b>.</span>
         </div>
         <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
            <select id="seg_liste_sec" class="seg-select"><option value="">Liste seçin...</option></select>
            <button id="seg_yenile" class="seg-indir" title="Segmentleri yenile"><i class="fa fa-refresh"></i> Yenile</button>
         </div>
      </div>
      <div id="seg_govde">
         <div class="cm-empty" style="padding:26px;"><i class="fa fa-hand-o-up"></i>Yukarıdan bir arama listesi seçin.</div>
      </div>
   </div>

</div>

{{-- Personel detay modalı --}}
<div id="personel_detay_modal" class="modal fade" role="dialog">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <div class="pd-head-wrap">
               <div class="pd-head-av" id="pd_avatar">?</div>
               <div>
                  <h5 class="pd-head-tit" id="pd_baslik">Personel Görüşmeleri</h5>
                  <div class="pd-head-sub" id="pd_altbaslik">Görüşme dökümü</div>
               </div>
            </div>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <div class="modal-body">
            {{-- Özet şerit (karttaki verilerden anında) --}}
            <div class="pd-ozet" id="pd_ozet"></div>

            {{-- Görüşme filtreleri --}}
            <div class="pd-filtre" id="pd_filtre" style="display:none;"></div>

            {{-- Görüşme zaman çizelgesi --}}
            <div id="pd_loading" class="pd-empty"><div class="ic"><i class="fa fa-spinner fa-spin"></i></div><div class="t1">Yükleniyor...</div></div>
            <div id="pd_liste"></div>
         </div>
      </div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

<script>
function cmEsc(s){ return (s==null?'':String(s)).replace(/[&<>"']/g,function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

function dashYukle() {
   $.ajax({
      url: '/isletmeyonetim/arama-dashboard-verileri',
      method: 'GET',
      data: { sube: $('input[name="sube"]').val() },
      success: function (res) {
         const cont = $('#personel_kartlari');
         cont.empty();
         let tAtanan = 0, tKonusulan = 0, tCevapsiz = 0, tRandevu = 0;

         if (!res.kartlar || res.kartlar.length === 0) {
            cont.html('<div class="col-md-12"><div class="cm-empty"><i class="fa fa-inbox"></i>Henüz hiçbir personele arama listesi atanmamış.</div></div>');
            $('#oz_atanan,#oz_konusulan,#oz_cevapsiz,#oz_randevu').text(0);
            return;
         }

         res.kartlar.forEach(function (k) {
            tAtanan += k.atanan; tKonusulan += k.konusulan; tCevapsiz += k.cevapsiz; tRandevu += k.randevu;
            const ilerleme = k.atanan > 0 ? Math.round((k.aranan / k.atanan) * 100) : 0;
            const adRaw = k.ad || '';
            const ad = cmEsc(adRaw);
            const bashHarf = cmEsc((adRaw.trim().charAt(0) || '?').toUpperCase());
            const card = `
               <div class="col-md-4 col-sm-6 mb-20">
                  <div class="cm-card personel-kart" data-id="${cmEsc(k.personel_id)}" data-ad="${ad}"
                       data-atanan="${k.atanan}" data-aranan="${k.aranan}" data-kalan="${k.kalan}"
                       data-konusulan="${k.konusulan}" data-cevapsiz="${k.cevapsiz}" data-randevu="${k.randevu}"
                       data-ulasilamadi="${k.ulasilamadi}" data-dk="${k.toplam_dk}">
                     <div class="cm-card-head">
                        <div class="cm-avatar">${bashHarf}</div>
                        <p class="nm">${ad}</p>
                        <span class="dk"><i class="fa fa-clock-o"></i> ${cmEsc(k.toplam_dk)} dk</span>
                     </div>
                     <div class="cm-card-body">
                        <div class="cm-prog-top">
                           <span class="txt">${cmEsc(k.aranan)} / ${cmEsc(k.atanan)} arandı</span>
                           <span class="pct">%${ilerleme}</span>
                        </div>
                        <div class="cm-prog"><span style="width:${ilerleme}%;"></span></div>

                        <div class="cm-mini">
                           <div class="box"><div class="n n-yesil">${cmEsc(k.konusulan)}</div><span class="t">Konuşulan</span></div>
                           <div class="box"><div class="n n-kirmizi">${cmEsc(k.cevapsiz)}</div><span class="t">Cevapsız</span></div>
                           <div class="box"><div class="n n-mavi">${cmEsc(k.randevu)}</div><span class="t">Randevu</span></div>
                        </div>
                        <div class="cm-mini2">
                           <div class="box"><div class="n n-koyu">${cmEsc(k.kalan)}</div><span class="t">Kalan</span></div>
                           <div class="box"><div class="n n-gri">${cmEsc(k.ulasilamadi)}</div><span class="t">Ulaşılamadı</span></div>
                        </div>
                     </div>
                  </div>
               </div>`;
            cont.append(card);
         });

         $('#oz_atanan').text(tAtanan);
         $('#oz_konusulan').text(tKonusulan);
         $('#oz_cevapsiz').text(tCevapsiz);
         $('#oz_randevu').text(tRandevu);
      },
      error: function () {
         $('#personel_kartlari').html('<div class="col-md-12"><div class="cm-empty"><i class="fa fa-exclamation-triangle"></i>Veriler yüklenirken hata oluştu.</div></div>');
      }
   });
}

// Sonuç koduna göre renk/etiket. Kodlar: 0 Ulaşılamadı, 1 Arandı, 2 Cevapsız, 3 Randevu, 4 Görüşüldü
function pdSonucStil(kod){
   switch (kod){
      case 4: return { c:'yesil',   et:'Görüşüldü' };
      case 1: return { c:'yesil',   et:'Arandı' };
      case 2: return { c:'kirmizi', et:'Cevapsız' };
      case 5: return { c:'kirmizi', et:'Meşgul' };
      case 0: return { c:'turuncu', et:'Ulaşılamadı' };
      case 3: return { c:'mavi',    et:'Randevu' };
      default:return { c:'gri',     et:'Bekliyor' };
   }
}

// Filtre grupları (sayısı 0 olan çip gösterilmez; Tümü daima görünür)
var PD_GRUPLAR = [
   { key:'hepsi',       et:'Tümü',        test:function(){ return true; } },
   { key:'gorusuldu',   et:'Cevaplanan',  test:function(k){ return k===1 || k===4; } },
   { key:'cevapsiz',    et:'Cevapsız / Meşgul', test:function(k){ return k===2 || k===5; } },
   { key:'ulasilamadi', et:'Ulaşılamadı', test:function(k){ return k===0; } },
   { key:'randevu',     et:'Randevu',     test:function(k){ return k===3; } }
];

var pdNotlar = [];
var pdAktif = 'hepsi';

// Üst özet şeridini karttaki verilerden çizer (ekstra sorgu yok)
function pdOzetCiz(d){
   $('#pd_ozet').html(`
      <div class="pd-oz mor"><div class="oz-ic"><i class="fa fa-clock-o"></i></div><div class="oz-n">${d.dk}</div><span class="oz-t">Konuşma (dk)</span></div>
      <div class="pd-oz"><div class="oz-ic c-yesil"><i class="fa fa-phone"></i></div><div class="oz-n c-yesil">${d.konusulan}</div><span class="oz-t">Cevaplanan</span></div>
      <div class="pd-oz"><div class="oz-ic c-kirmizi"><i class="fa fa-ban"></i></div><div class="oz-n c-kirmizi">${d.cevapsiz}</div><span class="oz-t">Cevapsız</span></div>
      <div class="pd-oz"><div class="oz-ic c-turuncu"><i class="fa fa-volume-off"></i></div><div class="oz-n c-turuncu">${d.ulasilamadi}</div><span class="oz-t">Ulaşılamadı</span></div>
      <div class="pd-oz"><div class="oz-ic c-mavi"><i class="fa fa-calendar-check-o"></i></div><div class="oz-n c-mavi">${d.randevu}</div><span class="oz-t">Randevu</span></div>
      <div class="pd-oz"><div class="oz-ic c-gri"><i class="fa fa-hourglass-half"></i></div><div class="oz-n c-gri">${d.kalan}</div><span class="oz-t">Sırada</span></div>
   `);
}

// Filtre çiplerini çizer
function pdFiltreCiz(){
   const $f = $('#pd_filtre');
   if (!pdNotlar.length){ $f.hide().empty(); return; }
   let html = '';
   PD_GRUPLAR.forEach(function(g){
      const sayi = g.key === 'hepsi' ? pdNotlar.length
                 : pdNotlar.filter(function(n){ return g.test(n.sonuc_kod); }).length;
      if (g.key !== 'hepsi' && sayi === 0) return;
      html += `<span class="pd-chip ${pdAktif===g.key?'aktif':''}" data-filtre="${g.key}">${g.et}<span class="sayi">${sayi}</span></span>`;
   });
   $f.html(html).show();
}

// Görüşme zaman çizelgesini (aktif filtreye göre) çizer
function pdListeCiz(){
   const liste = $('#pd_liste');
   const grup = PD_GRUPLAR.find(function(g){ return g.key === pdAktif; }) || PD_GRUPLAR[0];
   const veri = pdNotlar.filter(function(n){ return grup.test(n.sonuc_kod); });

   if (!veri.length){
      liste.html('<div class="pd-empty"><div class="t2">Bu filtreye uygun görüşme yok.</div></div>');
      return;
   }
   let html = '';
   veri.forEach(function(n){
      const st = pdSonucStil(n.sonuc_kod);
      const etiket = cmEsc(n.sonuc) || st.et;
      const sure = (n.sure_dk && n.sure_dk > 0) ? `<span class="pd-call-sure"><i class="fa fa-clock-o"></i> ${n.sure_dk} dk</span>` : '';
      const katMetin = [n.kategori, n.alt_kategori].filter(Boolean).join(' › ');
      const kat = katMetin ? `<span class="pd-call-kat"><i class="fa fa-tag"></i> ${cmEsc(katMetin)}</span>` : '';
      const not = n.not ? `<div class="pd-call-not"><i class="fa fa-sticky-note-o"></i>${cmEsc(n.not)}</div>` : '';
      const audio = n.ses ? `<div class="pd-call-audio"><audio controls preload="none" src="${cmEsc(n.ses)}"></audio></div>` : '';
      html += `
         <div class="pd-call bl-${st.c}">
            <div class="pd-call-top">
               <span class="pd-call-mus">${cmEsc(n.musteri) || 'Müşteri'}</span>
               <span class="pd-badge pd-b-${st.c}">${etiket}</span>
               <div class="pd-call-meta">
                  ${kat}
                  ${sure}
                  <span class="m"><i class="fa fa-calendar-o"></i>${cmEsc(n.tarih)}</span>
               </div>
            </div>
            ${not}
            ${audio}
         </div>`;
   });
   liste.html(html);
}

// Çip tıklama → filtre değiştir
$(document).on('click', '.pd-chip', function(){
   pdAktif = $(this).data('filtre');
   pdFiltreCiz();
   pdListeCiz();
});

$(document).on('click', '.personel-kart', function () {
   const $k = $(this);
   const pid = $k.data('id');
   const adRaw = String($k.data('ad') || '').trim();

   $('#pd_baslik').text(adRaw || 'Personel');
   $('#pd_avatar').text((adRaw.charAt(0) || '?').toUpperCase());

   // Özet şerit: karttaki verilerden ANINDA (AJAX beklemeden)
   const d = {
      dk: +$k.data('dk') || 0, konusulan: +$k.data('konusulan') || 0,
      cevapsiz: +$k.data('cevapsiz') || 0, ulasilamadi: +$k.data('ulasilamadi') || 0,
      randevu: +$k.data('randevu') || 0, kalan: +$k.data('kalan') || 0,
      aranan: +$k.data('aranan') || 0, atanan: +$k.data('atanan') || 0
   };
   pdOzetCiz(d);
   $('#pd_altbaslik').text(d.aranan + ' / ' + d.atanan + ' arandı');

   // Zaman çizelgesi: AJAX ile
   pdNotlar = []; pdAktif = 'hepsi';
   $('#pd_filtre').hide().empty();
   $('#pd_liste').empty();
   $('#pd_loading').show();
   $('#personel_detay_modal').modal('show');

   $.ajax({
      url: '/isletmeyonetim/arama-dashboard-personel-detay',
      method: 'POST',
      data: { personel_id: pid, sube: $('input[name="sube"]').val(), _token: $('input[name="_token"]').val() },
      success: function (res) {
         pdNotlar = res.notlar || [];
         if (pdNotlar.length === 0) {
            $('#pd_liste').html('<div class="pd-empty"><div class="ic"><i class="fa fa-comments-o"></i></div><div class="t1">Henüz görüşme kaydı yok</div><div class="t2">Bu personel arama yaptıkça her görüşme — süresi, sonucu, notu ve ses kaydıyla — burada listelenecek.</div></div>');
            return;
         }
         pdFiltreCiz();
         pdListeCiz();
      },
      error: function () {
         $('#pd_liste').html('<div class="pd-empty"><div class="ic"><i class="fa fa-exclamation-triangle"></i></div><div class="t1">Görüşmeler yüklenemedi</div><div class="t2">Lütfen tekrar deneyin.</div></div>');
      },
      complete: function () { $('#pd_loading').hide(); }
   });
});

/* ===== Liste Segmentasyonu ===== */
var segPersoneller = [];
function segMsg(o){ if (typeof swal === 'function') swal(o); else alert((o.title||'')+'\n'+(o.text||'')); }

function segPersonelYukle(){
   $.get('/isletmeyonetim/arama-personelleri', { sube: $('input[name="sube"]').val() }, function(res){
      segPersoneller = res || [];
   });
}
function segListeleriYukle(){
   $.get('/isletmeyonetim/arama-kartlarim', { sube: $('input[name="sube"]').val() }, function(res){
      var kartlar = (res && res.kartlar) ? res.kartlar : [];
      var opts = '<option value="">Liste seçin...</option>';
      kartlar.forEach(function(k){ opts += '<option value="'+k.id+'">'+cmEsc(k.baslik)+' ('+k.toplam+' müşteri)</option>'; });
      $('#seg_liste_sec').html(opts);
   });
}
function segRenk(kod){
   switch(String(kod)){
      case '4': case '1': return 'yesil';
      case '2': return 'kirmizi'; case '5': return 'pembe';
      case '0': return 'turuncu'; case '3': return 'mavi';
      default: return 'gri';
   }
}
function segPersonelOpts(){
   var o = '<option value="">Personel seç...</option>';
   segPersoneller.forEach(function(p){ o += '<option value="'+p.id+'">'+cmEsc(p.personel_adi)+'</option>'; });
   return o;
}

$(document).on('change', '#seg_liste_sec', function(){ segSegmentleriYukle(); });
$(document).on('click', '#seg_yenile', function(){ segSegmentleriYukle(); });

var segSecili = null; // {kod, ad, adet}

function segSegmentleriYukle(){
   var aramaId = $('#seg_liste_sec').val();
   var sube = $('input[name="sube"]').val();
   segSecili = null;
   if (!aramaId){ $('#seg_govde').html('<div class="cm-empty" style="padding:26px;"><i class="fa fa-hand-o-up"></i>Yukarıdan bir arama listesi seçin.</div>'); return; }
   $('#seg_govde').html('<div class="cm-empty" style="padding:26px;"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');
   $.get('/isletmeyonetim/cagri-liste-segmentler', { arama_id: aramaId, sube: sube }, function(res){
      var segs = (res && res.segmentler) ? res.segmentler : [];
      if (!segs.length){ $('#seg_govde').html('<div class="cm-empty" style="padding:26px;"><i class="fa fa-inbox"></i>Bu listede müşteri yok.</div>'); return; }
      var chips = '';
      segs.forEach(function(s){
         chips += '<button class="seg-chip s-'+segRenk(s.kod)+(s.adet===0?' bos':'')+'" data-kod="'+cmEsc(s.kod)+'" data-ad="'+cmEsc(s.ad)+'" data-adet="'+s.adet+'">'+
                     cmEsc(s.ad)+' <span class="seg-chip-n">'+s.adet+'</span></button>';
      });
      var html =
         '<div class="seg-bar">'+
            '<div class="seg-chips">'+chips+'</div>'+
            '<div class="seg-aksiyon">'+
               '<button class="seg-indir pasif" id="seg_indir"><i class="fa fa-download"></i> İndir</button>'+
               '<select class="seg-personel" id="seg_personel">'+segPersonelOpts()+'</select>'+
               '<button class="seg-ata pasif" id="seg_ata"><i class="fa fa-user-plus"></i> Personele Taşı</button>'+
            '</div>'+
         '</div>';
      $('#seg_govde').html(html);

      // İlk DOLU segmenti otomatik seç
      var ilkDolu = segs.filter(function(s){ return s.adet>0; })[0] || segs[0];
      if (ilkDolu){ $('.seg-chip[data-kod="'+ilkDolu.kod+'"]').trigger('click'); }
   });
}

// Çip seç -> aktif segment
$(document).on('click', '.seg-chip', function(){
   $('.seg-chip').removeClass('aktif');
   $(this).addClass('aktif');
   segSecili = { kod: String($(this).data('kod')), ad: $(this).data('ad'), adet: parseInt($(this).data('adet'),10)||0 };
   var aktif = segSecili.adet>0;
   $('#seg_indir').toggleClass('pasif', !aktif);
   $('#seg_ata').toggleClass('pasif', !aktif);
});

// Seçili segmenti indir
$(document).on('click', '#seg_indir', function(){
   if (!segSecili || segSecili.adet<=0) return;
   var aramaId = $('#seg_liste_sec').val();
   var sube = $('input[name="sube"]').val();
   window.location = '/isletmeyonetim/cagri-segment-indir?arama_id='+encodeURIComponent(aramaId)+'&kod='+encodeURIComponent(segSecili.kod)+'&sube='+encodeURIComponent(sube);
});

// Seçili segmenti personele taşı
$(document).on('click', '#seg_ata', function(){
   if (!segSecili || segSecili.adet<=0){ segMsg({ type:'warning', title:'Segment seçin', text:'Önce dolu bir segment (Görüşüldü, Cevapsız...) seçin.' }); return; }
   var aramaId = $('#seg_liste_sec').val();
   var personelId = $('#seg_personel').val();
   var personelAd = $('#seg_personel option:selected').text();
   if (!personelId){ segMsg({ type:'warning', title:'Personel seçin', text:'Taşımak için bir personel seçin.' }); return; }
   if (confirm(segSecili.ad+' segmentindeki '+segSecili.adet+' müşteri "'+personelAd+'" personeline TAŞINACAK (bu listeden çıkacak). Devam edilsin mi?')){
      segAtaYap(aramaId, segSecili.kod, personelId);
   }
});

function segAtaYap(aramaId, kod, personelId){
   var $btn = $('#seg_ata'); $btn.addClass('pasif');
   $.post('/isletmeyonetim/cagri-segment-ata',
      { arama_id: aramaId, kod: kod, personel_id: personelId, sube: $('input[name="sube"]').val(), _token: $('input[name="_token"]').val() },
      function(res){
         if (res.success){
            segMsg({ type:'success', title:'Taşındı', text:res.message, timer:2800, showConfirmButton:false });
            segListeleriYukle();                 // liste sayilari degisti
            segSegmentleriYukle();               // segmentleri tazele
            dashYukle();                          // performans kartlarini tazele
         } else { segMsg({ type:'error', title:'Hata', text:res.message||'Taşınamadı.' }); $btn.removeClass('pasif'); }
      }
   ).fail(function(){ segMsg({ type:'error', title:'Hata', text:'İşlem başarısız.' }); $btn.removeClass('pasif'); });
}

$(document).ready(function () { dashYukle(); segListeleriYukle(); segPersonelYukle(); });
</script>
@endsection
