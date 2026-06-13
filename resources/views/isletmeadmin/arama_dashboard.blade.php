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
#personel_detay_modal .modal-dialog{ max-width:680px; }
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

/* Boş / yükleniyor durumu */
.cm-empty{ text-align:center; padding:40px 20px; color:#8a85a0; }
.cm-empty i{ font-size:40px; color:#cfc7df; display:block; margin-bottom:10px; }
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
                  <div class="cm-card personel-kart" data-id="${cmEsc(k.personel_id)}" data-ad="${ad}">
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

// Sonuç metnine göre renkli rozet sınıfı seç
function pdBadge(sonuc){
   const s = (sonuc || '').toLocaleLowerCase('tr');
   if (s.indexOf('randevu') > -1) return 'pd-b-mavi';
   if (s.indexOf('konuş') > -1 || s.indexOf('olumlu') > -1 || s.indexOf('görüş') > -1) return 'pd-b-yesil';
   if (s.indexOf('cevapsız') > -1 || s.indexOf('ulaşıl') > -1 || s.indexOf('olumsuz') > -1 || s.indexOf('meşgul') > -1) return 'pd-b-kirmizi';
   if (s.indexOf('beklemede') > -1 || s.indexOf('sonra') > -1 || s.indexOf('tekrar') > -1) return 'pd-b-turuncu';
   return 'pd-b-gri';
}

$(document).on('click', '.personel-kart', function () {
   const pid = $(this).data('id');
   const adRaw = String($(this).data('ad') || '').trim();
   $('#pd_baslik').text(adRaw || 'Personel');
   $('#pd_avatar').text((adRaw.charAt(0) || '?').toUpperCase());
   $('#pd_altbaslik').text('Görüşme dökümü');
   $('#pd_liste').empty();
   $('#pd_loading').show();
   $('#personel_detay_modal').modal('show');
   $.ajax({
      url: '/isletmeyonetim/arama-dashboard-personel-detay',
      method: 'POST',
      data: { personel_id: pid, sube: $('input[name="sube"]').val(), _token: $('input[name="_token"]').val() },
      success: function (res) {
         const liste = $('#pd_liste');
         liste.empty();
         const notlar = res.notlar || [];

         if (notlar.length === 0) {
            liste.html('<div class="pd-empty"><div class="ic"><i class="fa fa-comments-o"></i></div><div class="t1">Henüz görüşme kaydı yok</div><div class="t2">Bu personel arama yaptıkça görüşmeler burada listelenecek.</div></div>');
            $('#pd_altbaslik').text('0 görüşme');
            return;
         }

         $('#pd_altbaslik').text(notlar.length + ' görüşme');
         notlar.forEach(function (n) {
            const ses = n.ses ? `<a href="${cmEsc(n.ses)}" target="_blank" class="pd-play" title="Kaydı dinle"><i class="fa fa-play"></i></a>` : '';
            const notHtml = n.not ? `<div class="pd-not"><i class="fa fa-sticky-note-o"></i>${cmEsc(n.not)}</div>` : '';
            liste.append(`
               <div class="pd-row">
                  <div class="pd-main">
                     <div class="pd-musteri">${cmEsc(n.musteri) || 'Müşteri'}</div>
                     <div class="pd-tarih"><i class="fa fa-clock-o"></i> ${cmEsc(n.tarih)}</div>
                     ${notHtml}
                  </div>
                  <span class="pd-badge ${pdBadge(n.sonuc)}">${cmEsc(n.sonuc) || '—'}</span>
                  ${ses}
               </div>`);
         });
      },
      error: function () {
         $('#pd_liste').html('<div class="pd-empty"><div class="ic"><i class="fa fa-exclamation-triangle"></i></div><div class="t1">Görüşmeler yüklenemedi</div><div class="t2">Lütfen tekrar deneyin.</div></div>');
      },
      complete: function () { $('#pd_loading').hide(); }
   });
});

$(document).ready(function () { dashYukle(); });
</script>
@endsection
