 <div
         id="santral_musteri_listesi"
         class="modal modal-top fade calendar-modal"

         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 92%;">
               <form id="arama_listesi_formu"  method="POST">
                 {{ csrf_field() }}
                <input type="hidden" name="sube" value="{{$isletme->id}}">
                <input type="hidden" name="grup_id">
                  <div class="modal-header">
                     <h2 class="modal_baslik">Arama Listesi Oluştur</h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">

                        <div class="col-sm-4 col-md-4">
                            <label>Başlık</label>
                             <input
                              class="form-control" id="arama_basligi" name='arama_basligi'
                              placeholder="Örn: 1. Gün Aramaları"
                              type="text"
                              />
                        </div>
                        <div class="col-sm-4 col-md-4">
                             <label>Personel </label>
                                             <select name="aramapersoneli" id="aramapersoneli" class="form-control opsiyonelSelect personel_secimi" style="width: 100%;">
                                                <option></option>
                                             </select>
                        </div>
                        <div class="col-sm-4 col-md-4">
                            <label>Aranacak Tarih</label>
                            <input class="form-control" id="aranacak_tarih" name="aranacak_tarih" type="date" value="{{ date('Y-m-d') }}">
                        </div>
                     </div>

                     {{-- ============ FİLTRE PANELİ ============ --}}
                     <div class="pd-15 card-box mb-10" style="margin-top:15px;background:#f8f9fb;border:1px solid #e6e9f0;border-radius:8px;">
                        <h5 style="margin-bottom:12px;"><i class="fa fa-filter"></i> Müşteri Filtreleri</h5>
                        <div class="row">
                           <div class="col-md-3 col-sm-6 mb-10">
                              <label>Kayıt Durumu</label>
                              <select id="f_kayit" class="form-control arama-filtre">
                                 <option value="">Tümü</option>
                                 <option value="son1yil">Son 1 Yılda Eklenen</option>
                                 <option value="ozel">Özel Tarih Aralığı</option>
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10 ozel-tarih" style="display:none;">
                              <label>Kayıt Başlangıç</label>
                              <input id="f_kayit_t1" type="date" class="form-control arama-filtre">
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10 ozel-tarih" style="display:none;">
                              <label>Kayıt Bitiş</label>
                              <input id="f_kayit_t2" type="date" class="form-control arama-filtre">
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10">
                              <label>Müşteri Durumu</label>
                              <select id="f_durum" class="form-control arama-filtre">
                                 <option value="">Tümü</option>
                                 <option value="sadik">Sadık (3+ işlem)</option>
                                 <option value="aktif">Aktif (1-2 işlem)</option>
                                 <option value="pasif">Pasif (hiç işlem yok)</option>
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10">
                              <label>Gelmeyen Müşteriler</label>
                              <select id="f_gelmeyen" class="form-control arama-filtre">
                                 <option value="">Tümü</option>
                                 <option value="15">Son 15 gündür gelmeyen</option>
                                 <option value="30">Son 30 gündür gelmeyen</option>
                                 <option value="60">Son 60 gündür gelmeyen</option>
                                 <option value="90">Son 90 gündür gelmeyen</option>
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10">
                              <label>Satış Durumu</label>
                              <select id="f_satis" class="form-control arama-filtre">
                                 <option value="">Tümü</option>
                                 <option value="var">Satış Yapılmış</option>
                                 <option value="yok">Satış Yapılmamış</option>
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10">
                              <label>Cinsiyet</label>
                              <select id="f_cinsiyet" class="form-control arama-filtre">
                                 <option value="">Tümü</option>
                                 <option value="0">Kadın</option>
                                 <option value="1">Erkek</option>
                              </select>
                           </div>
                           <div class="col-md-3 col-sm-6 mb-10">
                              <label>Doğum Günü Yaklaşan (gün)</label>
                              <input id="f_dogumgunu" type="number" min="0" placeholder="örn: 7" class="form-control arama-filtre">
                           </div>
                        </div>
                        <div class="row" style="margin-top:6px;">
                           <div class="col-md-3 col-sm-6 custom-control custom-checkbox mb-5">
                              <input type="checkbox" class="custom-control-input arama-filtre" id="f_kara_liste_haric" checked>
                              <label class="custom-control-label" for="f_kara_liste_haric">Kara liste hariç</label>
                           </div>
                           <div class="col-md-3 col-sm-6 custom-control custom-checkbox mb-5">
                              <input type="checkbox" class="custom-control-input arama-filtre" id="f_whatsapp_onay">
                              <label class="custom-control-label" for="f_whatsapp_onay">WhatsApp onaylı</label>
                           </div>
                           <div class="col-md-3 col-sm-6 custom-control custom-checkbox mb-5">
                              <input type="checkbox" class="custom-control-input arama-filtre" id="f_hic_randevu_yok">
                              <label class="custom-control-label" for="f_hic_randevu_yok">Hiç randevu almamış</label>
                           </div>
                           <div class="col-md-3 col-sm-6 custom-control custom-checkbox mb-5">
                              <input type="checkbox" class="custom-control-input arama-filtre" id="f_iptal_eden">
                              <label class="custom-control-label" for="f_iptal_eden">Randevu iptal eden</label>
                           </div>
                        </div>
                        <div style="margin-top:8px;font-weight:bold;color:#5C008E;">
                           <i class="fa fa-users"></i> <span id="eslesenSayisi">0</span> müşteri eşleşti
                           <span class="loading-filtre" style="display:none;color:#999;">(hesaplanıyor...)</span>
                        </div>
                     </div>

                     <div class="row">
                       <div class="col-md-12">
         <div class="container">
  <label>Müşterileri Seçiniz <small class="text-muted">(varsayılan: filtreye uyan tümü)</small></label>
  <div class="row" id="arama_musteri_liste" style="margin-bottom: 40px;">
      <div class="col-md-6">
         <div class="form-group">
            <input type="text" id="musteriarama" name="musteriarama" class="form-control" placeholder="Müşteri arayın...">
         </div>
      </div>
      <div class="col-md-3"><button id="selectAllBtn" type="button" class="btn btn-info btn-block">Tümünü Seç</button></div>
      <div class="col-md-3"> <button id="deselectAllBtn" type="button" class="btn btn-info btn-block">Tümünü Kaldır</button></div>
      <div class="col-md-12">
         <div id="customerList" style="width:100%;border:1px solid #e2e2e2;border-radius: 5px;height: 200px;overflow-y: scroll;">
         </div>
         <div class="loading" style="display: none;">Yükleniyor...</div>
         <div id="selectedCount" style="margin-top: 20px; font-weight: bold;">
             0 müşteri seçildi
         </div>
      </div>
  </div>
</div>
                       </div>
                     </div>
                  </div>
                  <div class="modal-footer" style="display:block">
                     <div class="row">
                        <div class="col-md-6">
                           <button type="submit"
                              class="btn btn-success btn-lg btn-block"> <i class="icon-copy dw dw-add"></i>
                           Kaydet</button>
                        </div>
                        <div class="col-md-6">
                           <button
                              type="button"
                              class="btn btn-danger btn-lg btn-block "
                              data-dismiss="modal"
                              > <i class="fa fa-times"></i>
                           Kapat
                           </button>
                        </div>
                     </div>
                  </div>

            </form>
         </div>
      </div>
      </div>
      <script>
let selectedIds = new Set();
let totalCustomers = 0;
let currentPage = 1;
const perPage = 100;
let isLoading = false;
let searchTerm = '';
let filtreDebounce = null;

// Tum filtre kriterlerini tek bir objede toplar (backend AramaFiltreService ile birebir).
function getFiltre() {
  return {
    kayit:              $('#f_kayit').val(),
    kayit_t1:           $('#f_kayit_t1').val(),
    kayit_t2:           $('#f_kayit_t2').val(),
    durum:              $('#f_durum').val(),
    gelmeyen:           $('#f_gelmeyen').val(),
    satis:              $('#f_satis').val(),
    cinsiyet:           $('#f_cinsiyet').val(),
    dogumgunu_yaklasan: $('#f_dogumgunu').val(),
    kara_liste_haric:   $('#f_kara_liste_haric').is(':checked') ? 1 : 0,
    whatsapp_onay:      $('#f_whatsapp_onay').is(':checked') ? 1 : 0,
    hic_randevu_yok:    $('#f_hic_randevu_yok').is(':checked') ? 1 : 0,
    iptal_eden:         $('#f_iptal_eden').is(':checked') ? 1 : 0,
    search:             searchTerm
  };
}

function updateSelectedCount() {
  $('#selectedCount').text(`${selectedIds.size} müşteri seçildi`);
}

function renderCustomers(customers, append = false) {
  if (!append) {
    $('#customerList').empty();
  }
  customers.forEach(c => {
    const isSelected = selectedIds.has(parseInt(c.id));
    const checkbox = $('<input type="checkbox" class="customer-checkbox">')
      .val(c.id)
      .prop('checked', isSelected)
      .on('change', function () {
        const userId = parseInt($(this).val());
        if (this.checked) { selectedIds.add(userId); } else { selectedIds.delete(userId); }
        updateSelectedCount();
      });
    const item = $('<div class="customer-item" style="padding:4px 8px;border-bottom:1px solid #f0f0f0;">')
      .text(' ' + (c.name || '(İsimsiz)'))
      .prepend(checkbox);
    $('#customerList').append(item);
  });
}

// Filtre degisince: sayac + liste (1. sayfa) + tum eslesenleri otomatik sec
function filtreUygula() {
  if (isLoading) return;
  isLoading = true;
  $('.loading').show();
  $('.loading-filtre').show();
  currentPage = 1;

  $.ajax({
    url: '/isletmeyonetim/arama_filtre_onizleme',
    method: 'POST',
    data: $.extend({}, getFiltre(), {
      page: 1,
      perPage: perPage,
      _token: $('input[name="_token"]').val()
    }),
    success: function (res) {
      totalCustomers = res.total;
      $('#eslesenSayisi').text(res.total);
      // Varsayilan: filtreye uyan TUM musteriler secili
      selectedIds = new Set((res.musteriIdler || []).map(Number));
      renderCustomers(res.customers, false);
      updateSelectedCount();
    },
    complete: function () {
      isLoading = false;
      $('.loading').hide();
      $('.loading-filtre').hide();
    }
  });
}

// Sayfalama (scroll) — secimi bozmadan sadece gorunum ekler
function loadMore(page) {
  if (isLoading) return;
  isLoading = true;
  $('.loading').show();
  $.ajax({
    url: '/isletmeyonetim/arama_filtre_onizleme',
    method: 'POST',
    data: $.extend({}, getFiltre(), {
      page: page,
      perPage: perPage,
      _token: $('input[name="_token"]').val()
    }),
    success: function (res) {
      renderCustomers(res.customers, true);
    },
    complete: function () {
      isLoading = false;
      $('.loading').hide();
    }
  });
}

function debounce(func, wait) {
  return function () {
    clearTimeout(filtreDebounce);
    filtreDebounce = setTimeout(() => func.apply(this, arguments), wait);
  };
}

$(document).ready(function () {
  filtreUygula();

  // Ozel tarih alanlarini goster/gizle
  $('#f_kayit').on('change', function () {
    if ($(this).val() === 'ozel') { $('.ozel-tarih').show(); } else { $('.ozel-tarih').hide(); }
  });

  // Herhangi bir filtre degisince yeniden hesapla
  $(document).on('change', '.arama-filtre', debounce(filtreUygula, 250));

  $('#musteriarama').on('input', debounce(function () {
    searchTerm = $(this).val().trim();
    filtreUygula();
  }, 400));

  $('#selectAllBtn').click(function () {
    // Tum eslesen id'leri sec (zaten filtreUygula bunu yapiyor ama manuel tetik)
    $.ajax({
      url: '/isletmeyonetim/arama_filtre_onizleme',
      method: 'POST',
      data: $.extend({}, getFiltre(), { page: 1, perPage: 1, _token: $('input[name="_token"]').val() }),
      success: function (res) {
        selectedIds = new Set((res.musteriIdler || []).map(Number));
        $('#customerList input.customer-checkbox').prop('checked', true);
        updateSelectedCount();
      }
    });
  });

  $('#deselectAllBtn').click(function () {
    selectedIds.clear();
    $('#customerList input.customer-checkbox').prop('checked', false);
    updateSelectedCount();
  });

  $('#customerList').scroll(function () {
    const $this = $(this);
    if ($this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 50) {
      if ((currentPage * perPage) < totalCustomers) {
        currentPage++;
        loadMore(currentPage);
      }
    }
  });

  $('#arama_listesi_formu').on('submit', function (e) {
    e.preventDefault();

    if (selectedIds.size === 0) {
      swal({ type: "warning", title: "Uyarı", text: "Lütfen en az bir müşteri seçin." });
      return;
    }

    const formData = new FormData(this);
    formData.append('secilenMusteriler', JSON.stringify(Array.from(selectedIds)));
    formData.append('filtre', JSON.stringify(getFiltre()));

    $.ajax({
      type: "POST",
      url: '/isletmeyonetim/arama_listesi_ekle',
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
      beforeSend: function () { $('#preloader').show(); },
      success: function (result) {
        $('#preloader').hide();
        $('button[data-dismiss="modal"]').trigger('click');
        swal({ type: "success", title: "Başarılı", text: result.mesaj, timer: 3000, showConfirmButton: false });
        aramaListesiniGetir('/isletmeyonetim/arama_listesi_getir');
      },
      error: function (request) {
        $('#preloader').hide();
        $('#Hata').html(request.responseText);
      }
    });
  });
});
</script>
