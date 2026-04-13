 <div
         id="santral_musteri_listesi"
         class="modal modal-top fade calendar-modal"
        
         >
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="max-height: 90%;">
               <form id="arama_listesi_formu"  method="POST">
                 {{ csrf_field() }}
                <input type="hidden" name="sube" value="{{$isletme->id}}">
                <input type="hidden" name="grup_id">
                  <div class="modal-header">
                     <h2 class="modal_baslik"></h2>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        
                        <div class="col-sm-4 col-md-4">
                         
                            <label>Başlık</label>
                             <input
                              class="form-control" id="arama_basligi" name='arama_basligi'
                              placeholder="Başlık"
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
                                
                    <label>Müşteriler</label>
                              <select id="musterifiltre" name="musterifiltre" class="form-control">
                                 <option value="0">Tüm Müşteriler</option>
                                 <option value="1">Sadık Müşteriler</option>
                                 <option value="2">Aktif Müşteriler</option>
                                 <option value="3">Pasif Müşteriler</option>
                                 <option value="4">15 Gün Gelmeyen Müşteriler</option>
                                 <option value="5">30 Gün Gelmeyen Müşteriler</option>
                                 <option value="6">45 Gün Gelmeyen Müşteriler</option>
                                 <option value="7">60 Gün Gelmeyen Müşteriler</option>
                              </select>
                        </div>
                   
                       <div class="col-md-12">
                       
                           
         <div class="container">
  <label>Müşterileri Seçiniz</label>
  <div class="row" id="arama_musteri_liste" style="margin-bottom: 40px;">
      <div class="col-md-6">
         <div class="form-group">
            <input type="text" name="musteriarama" class="form-control" placeholder="Müşteri arayın...">

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
let currentFilter = '0';

function updateSelectedCount() {
  $('#selectedCount').text(`${selectedIds.size} müşteri seçildi`);
}

function renderCustomers(customers, append = false) {
  if (!append) {
    $('#customerList').empty();
  }

  customers.forEach(c => {
    const isSelected = selectedIds.has(c.id);

    const checkbox = $('<input type="checkbox" class="customer-checkbox">')
      .val(c.id)
      .prop('checked', isSelected)
      .on('change', function () {
        const userId = parseInt($(this).val());
        if (this.checked) {
          selectedIds.add(userId);
        } else {
          selectedIds.delete(userId);
        }
        updateSelectedCount();
      });

    const item = $('<div class="customer-item">')
      .text(c.name || '(İsimsiz)')
      .prepend(checkbox);

    $('#customerList').append(item);
  });
}

function loadCustomers(page = 1, append = false) {
  if (isLoading) return;
  isLoading = true;
  $('.loading').show();

  $.ajax({
    url: '/isletmeyonetim/musteriportfoydropliste',
    method: 'POST',
    data: {
      page: page,
      perPage: perPage,
      filtre: currentFilter,
      search: searchTerm,
      _token: $('input[name="_token"]').val()
    },
    success: function (res) {
      totalCustomers = res.total;
      renderCustomers(res.customers, append);
      updateSelectedCount();
    },
    complete: function () {
      isLoading = false;
      $('.loading').hide();
    },
    error: function (xhr) {
      console.error("Error:", xhr.statusText);
    }
  });
}

// Debounce
function debounce(func, wait) {
  let timeout;
  return function () {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, arguments), wait);
  };
}

$(document).ready(function () {
  loadCustomers();

  $('input[name="musteriarama"]').on('input', debounce(function () {
    searchTerm = $(this).val().trim();
    currentPage = 1;
    loadCustomers(1, false);
  }, 400));

  $('#musterifiltre').change(function () {
    currentFilter = $(this).val();
    currentPage = 1;
    loadCustomers(1, false);
  });

  $('#selectAllBtn').click(function () {
    $.ajax({
      url: '/isletmeyonetim/musteriportfoydropliste',
      method: 'POST',
      data: {
        page: 1,
        perPage: totalCustomers,
        filtre: currentFilter,
        search: searchTerm,
        _token: $('input[name="_token"]').val()
      },
      success: function (res) {
        selectedIds = new Set(res.musteriIdler);
        loadCustomers(1, false);
      }
    });
  });

  $('#deselectAllBtn').click(function () {
    selectedIds.clear();
    loadCustomers(1, false);
  });

  $('#customerList').scroll(function () {
    const $this = $(this);
    if ($this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 50) {
      if ((currentPage * perPage) < totalCustomers) {
        currentPage++;
        loadCustomers(currentPage, true);
      }
    }
  });

  $('#arama_listesi_formu').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('secilenMusteriler', JSON.stringify(Array.from(selectedIds)));

    $.ajax({
      type: "POST",
      url: '/isletmeyonetim/arama_listesi_ekle',
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val()
      },
      beforeSend: function () {
        $('#preloader').show();
      },
      success: function (result) {
        $('#preloader').hide();
        $('button[data-dismiss="modal"]').trigger('click');
        swal({
          type: "success",
          title: "Başarılı",
          text: result.mesaj,
          timer: 3000,
          showConfirmButton: false
        });

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