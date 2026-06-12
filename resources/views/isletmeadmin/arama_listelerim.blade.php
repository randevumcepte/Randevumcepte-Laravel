@extends("layout.layout_isletmeadmin")
@section("content")

<div class="page-header">
   <div class="row">
      <div class="col-md-12">
         <div class="title">
            <h4>{{ $sayfa_baslik }}</h4>
         </div>
         <p class="text-muted" style="margin-top:-6px;">Size atanan arama listeleri. Bir karta tıklayarak müşterileri görüp arama yapabilirsiniz.</p>
      </div>
   </div>
</div>

{{-- KVKK uyarisi --}}
<div class="alert alert-info" style="border-left:4px solid #5C008E;">
   <i class="fa fa-shield"></i> Müşteri telefon numaraları KVKK gereği gizlidir. Aramak için telefon ikonuna tıklamanız yeterlidir; numara sizinle paylaşılmaz.
</div>

{{-- Kartlar --}}
<div id="arama_kartlari" class="row">
   <div class="col-md-12"><p id="kart_yukleniyor">Yükleniyor...</p></div>
</div>

{{-- Liste detayi (musteri tablosu) --}}
<div id="liste_detay_alani" class="pd-20 card-box mb-30" style="display:none;margin-top:20px;">
   <div class="d-flex justify-content-between align-items-center" style="display:flex;justify-content:space-between;align-items:center;">
      <h4 id="detay_baslik" style="margin:0;"></h4>
      <button id="detay_kapat" class="btn btn-sm btn-outline-secondary"><i class="fa fa-times"></i> Kapat</button>
   </div>
   <hr>
   <div class="table-responsive">
      <table class="table table-hover">
         <thead>
            <tr>
               <th style="width:50px;">#</th>
               <th>Müşteri</th>
               <th>Telefon</th>
               <th>Durum</th>
               <th>Görüşme Notu</th>
               <th style="width:160px;">İşlemler</th>
            </tr>
         </thead>
         <tbody id="musteri_detay_tbody"></tbody>
      </table>
      <div id="detay_loading" style="display:none;">Yükleniyor...</div>
   </div>
</div>

{{-- KVKK: ses-kaydi eslestirmesi icin gizli arama_liste_detaylari linki (webphone callHangUp bunu okur) --}}
<a id="aktif_arama_liste_link" name="arama_liste_detaylari" data-value="" href="#" style="display:none;"></a>

{{-- Gorusme Notu / Arama Randevusu modali --}}
<div id="gorusme_not_modal" class="modal fade" role="dialog">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Görüşme Sonucu</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <form id="gorusme_not_form">
            <div class="modal-body">
               <input type="hidden" id="gn_aranacak_musteri_id">
               <input type="hidden" id="gn_arama_detay_id">
               <div class="form-group">
                  <label>Görüşme Notu</label>
                  <textarea id="gn_not" class="form-control" rows="3" placeholder="Müşteri ile görüşme notunuz..."></textarea>
               </div>
               <div class="custom-control custom-checkbox mb-10">
                  <input type="checkbox" class="custom-control-input" id="gn_tekrar">
                  <label class="custom-control-label" for="gn_tekrar">Müşteri sonra aranmak istedi (Arama Randevusu oluştur)</label>
               </div>
               <div class="row gn-randevu-alan" style="display:none;">
                  <div class="col-md-6 form-group">
                     <label>Aranacak Tarih</label>
                     <input type="date" id="gn_tarih" class="form-control">
                  </div>
                  <div class="col-md-6 form-group">
                     <label>Aranacak Saat</label>
                     <input type="time" id="gn_saat" class="form-control">
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Kaydet</button>
               <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
            </div>
         </form>
      </div>
   </div>
</div>

<input type="hidden" name="sube" value="{{ $isletme->id }}">

<script>
let aktifListeId = null;
let detayLoading = false;

function kartlariYukle() {
   $.ajax({
      url: '/isletmeyonetim/arama-kartlarim',
      method: 'GET',
      data: { sube: $('input[name="sube"]').val() },
      success: function (res) {
         const cont = $('#arama_kartlari');
         cont.empty();
         if (!res.kartlar || res.kartlar.length === 0) {
            cont.html('<div class="col-md-12"><div class="alert alert-warning">Size atanmış aktif bir arama listesi bulunmuyor.</div></div>');
            return;
         }
         res.kartlar.forEach(function (k) {
            const tarih = k.aranacak_tarih ? ('<i class="fa fa-calendar"></i> ' + k.aranacak_tarih) : '<span class="text-muted">Tarih belirtilmemiş</span>';
            const card = `
               <div class="col-md-4 col-sm-6 mb-20">
                  <div class="card-box pd-20 arama-kart" data-id="${k.id}" data-baslik="${k.baslik}" style="cursor:pointer;border-top:3px solid #5C008E;">
                     <h5 style="margin-bottom:8px;">${k.baslik}</h5>
                     <p style="margin:4px 0;">${tarih}</p>
                     <div style="margin:8px 0;">
                        <span class="badge badge-primary" style="background:#5C008E;">${k.toplam} müşteri</span>
                        <span class="badge badge-success">${k.arandi} arandı</span>
                        <span class="badge badge-warning">${k.kalan} kaldı</span>
                     </div>
                     <div class="progress" style="height:8px;">
                        <div class="progress-bar" role="progressbar" style="width:${k.ilerleme}%;background:#5C008E;"></div>
                     </div>
                     <small class="text-muted">%${k.ilerleme} tamamlandı</small>
                  </div>
               </div>`;
            cont.append(card);
         });
      },
      error: function () {
         $('#arama_kartlari').html('<div class="col-md-12"><div class="alert alert-danger">Listeler yüklenirken hata oluştu.</div></div>');
      }
   });
}

// Karta tikla -> detay yukle
$(document).on('click', '.arama-kart', function () {
   aktifListeId = $(this).data('id');
   $('#detay_baslik').text($(this).data('baslik'));
   $('#aktif_arama_liste_link').attr('data-value', aktifListeId);
   $('#musteri_detay_tbody').empty();
   $('#liste_detay_alani').show();
   $('html, body').animate({ scrollTop: $('#liste_detay_alani').offset().top - 80 }, 300);
   detayYukle();
});

$('#detay_kapat').on('click', function () {
   $('#liste_detay_alani').hide();
   aktifListeId = null;
});

function detayYukle() {
   if (detayLoading || !aktifListeId) return;
   detayLoading = true;
   $('#detay_loading').show();
   $.ajax({
      url: '/isletmeyonetim/arama_liste_detay_getir',
      method: 'POST',
      data: {
         arama_detay_id: aktifListeId,
         page: 1,
         perPage: 500,
         _token: $('input[name="_token"]').val()
      },
      success: function (res) {
         const tbody = $('#musteri_detay_tbody');
         tbody.empty();
         (res.data || []).forEach(function (item, index) {
            const sesBtn = item.ses
               ? `<a href="${item.ses}" target="_blank" class="btn btn-sm btn-danger" title="Ses Kaydını Dinle"><i class="fa fa-play"></i></a>`
               : '';
            const notHTML = item.not
               ? `${item.not}${(item.not_tarih && item.not_saat) ? '<br><small class="text-muted">Randevu: ' + item.not_tarih + ' ' + item.not_saat + '</small>' : ''}`
               : '<span class="text-muted">-</span>';
            const row = `
               <tr data-id="${item.aranacak_musteri_id}">
                  <td style="text-align:center;font-weight:600;color:#5C008E;">${index + 1}</td>
                  <td>${item.ad}</td>
                  <td><span class="text-muted">${item.telefonGizlenmis}</span></td>
                  <td><span class="durum-hucre">${item.durum}</span></td>
                  <td class="not-hucre">${notHTML}</td>
                  <td>
                     <button class="btn btn-sm btn-success btn-ara" data-id="${item.aranacak_musteri_id}" title="Ara"><i class="fa fa-phone"></i></button>
                     <button class="btn btn-sm btn-warning btn-not" data-id="${item.aranacak_musteri_id}" data-not="${(item.not||'').replace(/"/g,'&quot;')}" title="Görüşme Notu"><i class="fa fa-pencil"></i></button>
                     ${sesBtn}
                  </td>
               </tr>`;
            tbody.append(row);
         });
         if ((res.data || []).length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Bu listede müşteri yok.</td></tr>');
         }
      },
      complete: function () {
         detayLoading = false;
         $('#detay_loading').hide();
      }
   });
}

// KVKK uyumlu arama: santral originate — numara tarayiciya GITMEZ; personelin Bria'si calar.
$(document).on('click', '.btn-ara', function () {
   const aranacakId = $(this).data('id');
   const $btn = $(this);
   $btn.prop('disabled', true);
   $.ajax({
      url: '/isletmeyonetim/arama-baslat',
      method: 'POST',
      data: { aranacak_musteri_id: aranacakId, _token: $('input[name="_token"]').val() },
      success: function (res) {
         if (res.success) {
            $btn.closest('tr').find('.durum-hucre').text('Arandı');
            swal({ type: 'success', title: 'Arama başlatıldı', text: res.message || 'Telefonunuz (Bria) çalacak, açın.', timer: 3500, showConfirmButton: false });
         } else {
            swal({ type: 'warning', title: 'Aranamadı', text: res.message || 'Arama başlatılamadı.' });
         }
      },
      error: function (xhr) {
         const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Arama başlatılamadı.';
         swal({ type: 'error', title: 'Hata', text: msg });
      },
      complete: function () {
         setTimeout(function () { $btn.prop('disabled', false); }, 2000);
      }
   });
});

// Gorusme notu modali
$(document).on('click', '.btn-not', function () {
   $('#gn_aranacak_musteri_id').val($(this).data('id'));
   $('#gn_arama_detay_id').val(aktifListeId);
   $('#gn_not').val($(this).data('not') || '');
   $('#gn_tekrar').prop('checked', false);
   $('.gn-randevu-alan').hide();
   $('#gn_tarih').val('');
   $('#gn_saat').val('');
   $('#gorusme_not_modal').modal('show');
});

$('#gn_tekrar').on('change', function () {
   if ($(this).is(':checked')) { $('.gn-randevu-alan').show(); } else { $('.gn-randevu-alan').hide(); }
});

$('#gorusme_not_form').on('submit', function (e) {
   e.preventDefault();
   const randevuMu = $('#gn_tekrar').is(':checked');
   if (randevuMu && (!$('#gn_tarih').val() || !$('#gn_saat').val())) {
      swal({ type: 'warning', title: 'Eksik', text: 'Arama randevusu için tarih ve saat seçin.' });
      return;
   }
   $.ajax({
      url: '/isletmeyonetim/santral_not_ekle',
      method: 'POST',
      data: {
         arama_detay_id: $('#gn_arama_detay_id').val(),
         aranacak_musteri_id: $('#gn_aranacak_musteri_id').val(),
         noticerik: $('#gn_not').val(),
         santralnottarih: randevuMu ? $('#gn_tarih').val() : '',
         santralnotsaat: randevuMu ? $('#gn_saat').val() : '',
         _token: $('input[name="_token"]').val()
      },
      success: function (res) {
         if (res.success) {
            $('#gorusme_not_modal').modal('hide');
            swal({ type: 'success', title: 'Kaydedildi', timer: 1500, showConfirmButton: false });
            detayYukle();
         } else {
            swal({ type: 'error', title: 'Hata', text: res.message || 'Not kaydedilemedi.' });
         }
      },
      error: function () {
         swal({ type: 'error', title: 'Hata', text: 'Not kaydedilirken bir sorun oluştu.' });
      }
   });
});

$(document).ready(function () {
   kartlariYukle();
});
</script>
@endsection
