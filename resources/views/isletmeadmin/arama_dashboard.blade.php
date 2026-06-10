@extends("layout.layout_isletmeadmin")
@section("content")

<div class="page-header">
   <div class="row">
      <div class="col-md-12">
         <div class="title"><h4>{{ $sayfa_baslik }}</h4></div>
         <p class="text-muted" style="margin-top:-6px;">Çağrı yapan personellerinizi tek ekrandan takip edin. Detay için bir karta tıklayın.</p>
      </div>
   </div>
</div>

{{-- Ozet bant --}}
<div class="row" id="ozet_bant">
   <div class="col-md-3 col-sm-6 mb-20">
      <div class="card-box pd-20" style="border-left:4px solid #5C008E;">
         <h3 id="oz_atanan" style="margin:0;">0</h3><span class="text-muted">Toplam Atanan Data</span>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-20">
      <div class="card-box pd-20" style="border-left:4px solid #2e7d32;">
         <h3 id="oz_konusulan" style="margin:0;">0</h3><span class="text-muted">Konuşulan</span>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-20">
      <div class="card-box pd-20" style="border-left:4px solid #c62828;">
         <h3 id="oz_cevapsiz" style="margin:0;">0</h3><span class="text-muted">Cevapsız</span>
      </div>
   </div>
   <div class="col-md-3 col-sm-6 mb-20">
      <div class="card-box pd-20" style="border-left:4px solid #1565c0;">
         <h3 id="oz_randevu" style="margin:0;">0</h3><span class="text-muted">Arama Randevusu</span>
      </div>
   </div>
</div>

{{-- Personel kartlari --}}
<div id="personel_kartlari" class="row">
   <div class="col-md-12"><p id="dash_yukleniyor">Yükleniyor...</p></div>
</div>

{{-- Personel detay modali --}}
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
            cont.html('<div class="col-md-12"><div class="alert alert-warning">Henüz hiçbir personele arama listesi atanmamış.</div></div>');
            return;
         }

         res.kartlar.forEach(function (k) {
            tAtanan += k.atanan; tKonusulan += k.konusulan; tCevapsiz += k.cevapsiz; tRandevu += k.randevu;
            const ilerleme = k.atanan > 0 ? Math.round((k.aranan / k.atanan) * 100) : 0;
            const card = `
               <div class="col-md-4 col-sm-6 mb-20">
                  <div class="card-box pd-20 personel-kart" data-id="${k.personel_id}" data-ad="${k.ad}" style="cursor:pointer;border-top:3px solid #5C008E;">
                     <div style="display:flex;align-items:center;justify-content:space-between;">
                        <h5 style="margin:0;"><i class="fa fa-user-circle"></i> ${k.ad}</h5>
                        <span class="badge badge-primary" style="background:#5C008E;">${k.toplam_dk} dk</span>
                     </div>
                     <div class="progress" style="height:7px;margin:10px 0;">
                        <div class="progress-bar" style="width:${ilerleme}%;background:#5C008E;"></div>
                     </div>
                     <small class="text-muted">${k.aranan}/${k.atanan} arandı (%${ilerleme})</small>
                     <hr style="margin:10px 0;">
                     <div class="row text-center">
                        <div class="col-4"><strong style="color:#2e7d32;">${k.konusulan}</strong><br><small>Konuşulan</small></div>
                        <div class="col-4"><strong style="color:#c62828;">${k.cevapsiz}</strong><br><small>Cevapsız</small></div>
                        <div class="col-4"><strong style="color:#1565c0;">${k.randevu}</strong><br><small>Randevu</small></div>
                     </div>
                     <div class="row text-center" style="margin-top:6px;">
                        <div class="col-6"><strong>${k.kalan}</strong><br><small>Kalan</small></div>
                        <div class="col-6"><strong style="color:#999;">${k.ulasilamadi}</strong><br><small>Ulaşılamadı</small></div>
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
         $('#personel_kartlari').html('<div class="col-md-12"><div class="alert alert-danger">Veriler yüklenirken hata oluştu.</div></div>');
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
            tbody.append(`<tr><td>${n.tarih}</td><td>${n.musteri}</td><td>${n.sonuc}</td><td>${n.not || '-'}</td><td>${ses}</td></tr>`);
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
