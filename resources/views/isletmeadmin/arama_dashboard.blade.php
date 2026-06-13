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

/* Modal tablo */
#personel_detay_modal .modal-content{ border:none; border-radius:16px; overflow:hidden; }
#personel_detay_modal .modal-header{ background:linear-gradient(120deg,#5C008E,#7B2FB8); color:#fff; border:none; }
#personel_detay_modal .modal-header .close{ color:#fff; opacity:.9; text-shadow:none; }

/* Boş / yükleniyor durumu */
.cm-empty{ text-align:center; padding:40px 20px; color:#8a85a0; }
.cm-empty i{ font-size:40px; color:#cfc7df; display:block; margin-bottom:10px; }
</style>

<div class="cm-wrap">

   {{-- Başlık şeridi --}}
   <div class="cm-hero">
      <h4><i class="fa fa-headset"></i> {{ $sayfa_baslik }}</h4>
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
            <div class="ic ic-kirmizi"><i class="fa fa-phone-slash"></i></div>
            <div>
               <div class="val" id="oz_cevapsiz">0</div>
               <span class="lbl">Cevapsız</span>
               <span class="hint">Arandı, açılmadı</span>
            </div>
         </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-20">
         <div class="cm-stat">
            <div class="ic ic-mavi"><i class="fa fa-calendar-check"></i></div>
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
            <h5 class="modal-title" id="pd_baslik">Personel Görüşmeleri</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <div class="modal-body">
            <div class="table-responsive">
               <table class="table table-sm table-hover">
                  <thead>
                     <tr><th>Tarih</th><th>Müşteri</th><th>Sonuç</th><th>Not</th><th>Ses</th></tr>
                  </thead>
                  <tbody id="pd_tbody"></tbody>
               </table>
               <div id="pd_loading" style="display:none;">Yükleniyor...</div>
            </div>
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
                        <span class="dk"><i class="fa fa-clock"></i> ${cmEsc(k.toplam_dk)} dk</span>
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

$(document).on('click', '.personel-kart', function () {
   const pid = $(this).data('id');
   $('#pd_baslik').text($(this).data('ad') + ' — Görüşmeler');
   $('#pd_tbody').empty();
   $('#pd_loading').show();
   $('#personel_detay_modal').modal('show');
   $.ajax({
      url: '/isletmeyonetim/arama-dashboard-personel-detay',
      method: 'POST',
      data: { personel_id: pid, sube: $('input[name="sube"]').val(), _token: $('input[name="_token"]').val() },
      success: function (res) {
         const tbody = $('#pd_tbody');
         tbody.empty();
         (res.notlar || []).forEach(function (n) {
            const ses = n.ses ? `<a href="${n.ses}" target="_blank" class="btn btn-sm btn-danger"><i class="fa fa-play"></i></a>` : '-';
            tbody.append(`<tr><td>${cmEsc(n.tarih)}</td><td>${cmEsc(n.musteri)}</td><td>${cmEsc(n.sonuc)}</td><td>${cmEsc(n.not) || '-'}</td><td>${ses}</td></tr>`);
         });
         if ((res.notlar || []).length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center text-muted">Bu personelin görüşme kaydı yok.</td></tr>');
         }
      },
      complete: function () { $('#pd_loading').hide(); }
   });
});

$(document).ready(function () { dashYukle(); });
</script>
@endsection
