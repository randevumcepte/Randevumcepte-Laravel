<style>
/* ===== Cagri Merkezi — Arama Listesi Olustur (modern) ===== */
#santral_musteri_listesi .cm-content{
   border:none;border-radius:18px;overflow:hidden;
   box-shadow:0 24px 60px rgba(40,0,70,.28);max-height:94vh;
}
#santral_musteri_listesi .cm-header{
   background:linear-gradient(120deg,#5C008E 0%,#7B2FB8 55%,#9D5DC8 100%);
   color:#fff;padding:22px 28px;position:relative;
}
#santral_musteri_listesi .cm-header h2{margin:0;font-size:22px;font-weight:800;letter-spacing:.3px;}
#santral_musteri_listesi .cm-header p{margin:4px 0 0;opacity:.85;font-size:13px;}
#santral_musteri_listesi .cm-body{padding:22px 26px;background:#fafafe;overflow-y:auto;max-height:64vh;}
#santral_musteri_listesi label{font-size:12.5px;font-weight:700;color:#4a4a5a;margin-bottom:5px;display:block;letter-spacing:.2px;}
#santral_musteri_listesi .form-control,
#santral_musteri_listesi select.form-control{
   height:44px;border-radius:11px;border:1.5px solid #e6e3ef;background:#fff;
   font-size:14px;transition:border-color .15s,box-shadow .15s;box-shadow:none;
}
#santral_musteri_listesi textarea.form-control{height:auto;}
#santral_musteri_listesi .form-control:focus{
   border-color:#7B2FB8;box-shadow:0 0 0 3px rgba(123,47,184,.14);outline:none;
}
#santral_musteri_listesi .cm-ust{
   display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:14px;margin-bottom:18px;
}
#santral_musteri_listesi .cm-filtre-kart{
   background:#fff;border:1.5px solid #efeaf6;border-radius:16px;padding:18px 18px 6px;
   box-shadow:0 4px 18px rgba(92,0,142,.05);
}
#santral_musteri_listesi .cm-filtre-baslik{
   display:flex;align-items:center;gap:9px;font-size:15px;font-weight:800;color:#5C008E;margin-bottom:14px;
}
#santral_musteri_listesi .cm-filtre-baslik .ic{
   width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;
   background:linear-gradient(135deg,#5C008E,#9D5DC8);color:#fff;font-size:14px;
}
#santral_musteri_listesi .cm-grid{
   display:grid;grid-template-columns:repeat(4,1fr);gap:13px;
}
#santral_musteri_listesi .cm-grid .cm-fld{margin-bottom:12px;}
#santral_musteri_listesi .cm-chips{display:flex;flex-wrap:wrap;gap:10px;margin:6px 2px 14px;}
#santral_musteri_listesi .cm-chip{cursor:pointer;margin:0;user-select:none;}
#santral_musteri_listesi .cm-chip input{position:absolute;opacity:0;pointer-events:none;}
#santral_musteri_listesi .cm-chip span{
   display:inline-flex;align-items:center;gap:7px;padding:9px 15px;border-radius:999px;
   border:1.5px solid #e2dcee;background:#fff;color:#6a6a78;font-size:13px;font-weight:600;transition:all .15s;
}
#santral_musteri_listesi .cm-chip span:before{
   content:"";width:15px;height:15px;border-radius:5px;border:2px solid #cfc6e0;flex:0 0 auto;transition:all .15s;
}
#santral_musteri_listesi .cm-chip input:checked + span{
   background:linear-gradient(135deg,#5C008E,#9D5DC8);border-color:transparent;color:#fff;
   box-shadow:0 4px 12px rgba(92,0,142,.28);
}
#santral_musteri_listesi .cm-chip input:checked + span:before{
   background:#fff;border-color:#fff;content:"\2713";color:#5C008E;font-size:10px;
   display:flex;align-items:center;justify-content:center;font-weight:900;
}
#santral_musteri_listesi .cm-sayac{
   display:flex;align-items:center;gap:12px;background:linear-gradient(120deg,#f3ecfb,#eee6fa);
   border:1.5px solid #e0d4f3;border-radius:14px;padding:14px 18px;margin-top:4px;
}
#santral_musteri_listesi .cm-sayac .num{
   font-size:30px;font-weight:900;color:#5C008E;line-height:1;min-width:54px;text-align:center;
}
#santral_musteri_listesi .cm-sayac .lbl{font-size:13.5px;color:#5a4a6e;font-weight:600;}
#santral_musteri_listesi .cm-sayac .loading-filtre{font-size:12px;color:#9b8fb0;}
#santral_musteri_listesi .cm-sec-kart{
   background:#fff;border:1.5px solid #efeaf6;border-radius:16px;padding:16px 18px;margin-top:18px;
}
#santral_musteri_listesi #customerList{
   width:100%;border:1.5px solid #ece7f4;border-radius:12px;height:210px;overflow-y:auto;background:#fcfbff;
}
#santral_musteri_listesi .customer-item{
   padding:9px 13px;border-bottom:1px solid #f1edf8;font-size:14px;color:#3a3a47;
   display:flex;align-items:center;gap:10px;transition:background .12s;
}
#santral_musteri_listesi .customer-item:hover{background:#f6f1fd;}
#santral_musteri_listesi .customer-checkbox{width:17px;height:17px;accent-color:#5C008E;}
#santral_musteri_listesi #selectedCount{
   margin-top:12px;font-weight:700;color:#5C008E;font-size:13.5px;
}
#santral_musteri_listesi .cm-btn-mini{
   height:44px;border-radius:11px;font-weight:700;font-size:13px;border:1.5px solid #d9cef0;
   background:#fff;color:#5C008E;transition:all .15s;
}
#santral_musteri_listesi .cm-btn-mini:hover{background:#5C008E;color:#fff;border-color:#5C008E;}
#santral_musteri_listesi .cm-footer{padding:16px 26px;background:#fff;border-top:1px solid #eee;}
#santral_musteri_listesi .cm-kaydet{
   height:50px;border:none;border-radius:13px;font-weight:800;font-size:15px;color:#fff;
   background:linear-gradient(120deg,#5C008E,#9D5DC8);box-shadow:0 8px 22px rgba(92,0,142,.32);transition:transform .12s,box-shadow .12s;
}
#santral_musteri_listesi .cm-kaydet:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(92,0,142,.4);color:#fff;}
#santral_musteri_listesi .cm-kapat{
   height:50px;border-radius:13px;font-weight:700;font-size:15px;background:#fff;color:#8a8a96;border:1.5px solid #e6e6ee;
}
#santral_musteri_listesi .cm-kapat:hover{background:#f4f4f8;color:#555;}
/* Bagimli (kilitli) filtre gorunumu */
#santral_musteri_listesi .cm-disabled{opacity:.38;pointer-events:none;filter:grayscale(.4);}
#santral_musteri_listesi .cm-disabled label:after{content:" 🔒";font-size:11px;}
@media (max-width:768px){
   #santral_musteri_listesi .cm-ust{grid-template-columns:1fr;}
   #santral_musteri_listesi .cm-grid{grid-template-columns:1fr 1fr;}
}
</style>

<div id="santral_musteri_listesi" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog modal-dialog-centered" style="max-width:920px;width:96%;">
      <div class="modal-content cm-content">
         <form id="arama_listesi_formu" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="sube" value="{{$isletme->id}}">
            <input type="hidden" name="grup_id">

            <div class="cm-header">
               <h2><i class="fa fa-headset"></i> Arama Listesi Oluştur</h2>
               <p>Müşterilerini filtrele, bir personele ata; çağrı merkezi listesi hazır.</p>
            </div>

            <div class="cm-body">
               {{-- Ust alanlar --}}
               <div class="cm-ust">
                  <div>
                     <label>Liste Başlığı</label>
                     <input class="form-control" id="arama_basligi" name="arama_basligi" type="text" placeholder="Örn: 1. Gün Aramaları">
                  </div>
                  <div>
                     <label>Atanacak Personel</label>
                     <select name="aramapersoneli" id="aramapersoneli" class="form-control opsiyonelSelect personel_secimi" style="width:100%;">
                        <option></option>
                     </select>
                  </div>
                  <div>
                     <label>Aranacak Tarih</label>
                     <input class="form-control" id="aranacak_tarih" name="aranacak_tarih" type="date" value="{{ date('Y-m-d') }}">
                  </div>
               </div>

               {{-- Filtre karti --}}
               <div class="cm-filtre-kart">
                  <div class="cm-filtre-baslik"><span class="ic"><i class="fa fa-filter"></i></span> Müşteri Filtreleri</div>

                  <div class="cm-grid">
                     <div class="cm-fld">
                        <label>Kayıt Durumu</label>
                        <select id="f_kayit" class="form-control arama-filtre">
                           <option value="">Tümü</option>
                           <option value="son1yil">Son 1 Yılda Eklenen</option>
                           <option value="ozel">Özel Tarih Aralığı</option>
                        </select>
                     </div>
                     <div class="cm-fld ozel-tarih" style="display:none;">
                        <label>Kayıt Başlangıç</label>
                        <input id="f_kayit_t1" type="date" class="form-control arama-filtre">
                     </div>
                     <div class="cm-fld ozel-tarih" style="display:none;">
                        <label>Kayıt Bitiş</label>
                        <input id="f_kayit_t2" type="date" class="form-control arama-filtre">
                     </div>
                     <div class="cm-fld">
                        <label>Müşteri Durumu</label>
                        <select id="f_durum" class="form-control arama-filtre">
                           <option value="">Tümü</option>
                           <option value="sadik">Sadık (3+ işlem)</option>
                           <option value="aktif">Aktif (1-2 işlem)</option>
                           <option value="pasif">Pasif (hiç işlem yok)</option>
                        </select>
                     </div>
                     <div class="cm-fld">
                        <label>Gelmeyen Müşteriler</label>
                        <select id="f_gelmeyen" class="form-control arama-filtre">
                           <option value="">Tümü</option>
                           <option value="15">Son 15 gündür gelmeyen</option>
                           <option value="30">Son 30 gündür gelmeyen</option>
                           <option value="60">Son 60 gündür gelmeyen</option>
                           <option value="90">Son 90 gündür gelmeyen</option>
                        </select>
                     </div>
                     <div class="cm-fld">
                        <label>Satış Durumu</label>
                        <select id="f_satis" class="form-control arama-filtre">
                           <option value="">Tümü</option>
                           <option value="var">Satış Yapılmış</option>
                           <option value="yok">Satış Yapılmamış</option>
                        </select>
                     </div>
                     <div class="cm-fld">
                        <label>Cinsiyet</label>
                        <select id="f_cinsiyet" class="form-control arama-filtre">
                           <option value="">Tümü</option>
                           <option value="0">Kadın</option>
                           <option value="1">Erkek</option>
                        </select>
                     </div>
                     <div class="cm-fld">
                        <label>Doğum Günü Yaklaşan (gün)</label>
                        <input id="f_dogumgunu" type="number" min="0" placeholder="örn: 7" class="form-control arama-filtre">
                     </div>
                  </div>

                  <div class="cm-chips">
                     <label class="cm-chip"><input type="checkbox" class="arama-filtre" id="f_kara_liste_haric" checked><span>Kara liste hariç</span></label>
                     <label class="cm-chip"><input type="checkbox" class="arama-filtre" id="f_whatsapp_onay"><span>WhatsApp onaylı</span></label>
                     <label class="cm-chip"><input type="checkbox" class="arama-filtre" id="f_hic_randevu_yok"><span>Hiç randevu almamış</span></label>
                     <label class="cm-chip"><input type="checkbox" class="arama-filtre" id="f_iptal_eden"><span>Randevu iptal eden</span></label>
                  </div>

                  <div class="cm-sayac">
                     <div class="num" id="eslesenSayisi">0</div>
                     <div class="lbl">müşteri seçtiğin filtrelere uyuyor <span class="loading-filtre" style="display:none;">· hesaplanıyor…</span></div>
                  </div>
               </div>

               {{-- Musteri secim karti --}}
               <div class="cm-sec-kart">
                  <label>Müşterileri Seçiniz <span style="color:#9b8fb0;font-weight:600;">(varsayılan: filtreye uyan tümü)</span></label>
                  <div class="row" style="margin-bottom:12px;">
                     <div class="col-md-6"><input type="text" id="musteriarama" name="musteriarama" class="form-control" placeholder="🔍 Müşteri arayın..."></div>
                     <div class="col-md-3"><button id="selectAllBtn" type="button" class="cm-btn-mini btn-block">Tümünü Seç</button></div>
                     <div class="col-md-3"><button id="deselectAllBtn" type="button" class="cm-btn-mini btn-block">Tümünü Kaldır</button></div>
                  </div>
                  <div id="customerList"></div>
                  <div class="loading" style="display:none;color:#9b8fb0;font-size:13px;margin-top:8px;">Yükleniyor...</div>
                  <div id="selectedCount">0 müşteri seçildi</div>
               </div>
            </div>

            <div class="cm-footer">
               <div class="row">
                  <div class="col-md-7 col-7"><button type="submit" class="cm-kaydet btn-block"><i class="fa fa-check-circle"></i> Listeyi Oluştur</button></div>
                  <div class="col-md-5 col-5"><button type="button" class="cm-kapat btn-block" data-dismiss="modal">Kapat</button></div>
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
  $('#selectedCount').text(selectedIds.size + ' müşteri seçildi');
}

function renderCustomers(customers, append) {
  if (!append) { $('#customerList').empty(); }
  customers.forEach(function (c) {
    const isSelected = selectedIds.has(parseInt(c.id));
    const checkbox = $('<input type="checkbox" class="customer-checkbox">')
      .val(c.id).prop('checked', isSelected)
      .on('change', function () {
        const userId = parseInt($(this).val());
        if (this.checked) { selectedIds.add(userId); } else { selectedIds.delete(userId); }
        updateSelectedCount();
      });
    const item = $('<div class="customer-item">').text(c.name || '(İsimsiz)').prepend(checkbox);
    $('#customerList').append(item);
  });
}

function filtreUygula() {
  if (isLoading) return;
  isLoading = true;
  $('.loading').show();
  $('.loading-filtre').html('· hesaplanıyor…').show();
  currentPage = 1;
  $.ajax({
    url: '/isletmeyonetim/arama_filtre_onizleme',
    method: 'POST',
    data: $.extend({}, getFiltre(), { page: 1, perPage: perPage, _token: $('input[name="_token"]').val() }),
    success: function (res) {
      $('.loading-filtre').hide();
      totalCustomers = res.total;
      $('#eslesenSayisi').text(res.total);
      selectedIds = new Set((res.musteriIdler || []).map(Number));
      renderCustomers(res.customers, false);
      updateSelectedCount();
    },
    error: function (xhr) {
      $('#eslesenSayisi').text('!');
      var msg = 'Hata ' + xhr.status;
      try {
        var r = xhr.responseJSON || JSON.parse(xhr.responseText);
        if (r && r.message) msg += ': ' + r.message;
      } catch (e) {
        if (xhr.responseText) msg += ': ' + xhr.responseText.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').substring(0, 220);
      }
      console.error('arama_filtre_onizleme HATA:', xhr.status, xhr.responseText);
      $('.loading-filtre').html('<span style="color:#c62828;font-weight:600;">' + msg + '</span>').show();
    },
    complete: function () { isLoading = false; $('.loading').hide(); }
  });
}

function loadMore(page) {
  if (isLoading) return;
  isLoading = true;
  $('.loading').show();
  $.ajax({
    url: '/isletmeyonetim/arama_filtre_onizleme',
    method: 'POST',
    data: $.extend({}, getFiltre(), { page: page, perPage: perPage, _token: $('input[name="_token"]').val() }),
    success: function (res) { renderCustomers(res.customers, true); },
    complete: function () { isLoading = false; $('.loading').hide(); }
  });
}

function debounce(func, wait) {
  return function () { clearTimeout(filtreDebounce); filtreDebounce = setTimeout(() => func.apply(this, arguments), wait); };
}

// Bir alani kilitle/ac (deger korunur, gorsel olarak grayscale + kilit)
function setDisabled($el, disabled) {
  $el.prop('disabled', disabled);
  $el.closest('.cm-fld, .cm-chip').toggleClass('cm-disabled', disabled);
}

// Bir dropdown icindeki TEK secenegi devre disi birak (dropdown acik kalir).
// Secili olan secenek kapatiliyorsa "Tümü"ye (bos) doner.
function setOptionDisabled($select, value, disabled) {
  $select.find('option[value="' + value + '"]').prop('disabled', disabled);
  if (disabled && $select.val() === value) { $select.val(''); }
}

// Celiskili filtre kombinasyonlarini engelle — ama dropdown'lari KILITLEME,
// sadece mantiken imkansiz olan secenekleri devre disi birak.
function filtreBagimliliklari() {
  // 1) Tek kati celiski: PASIF = satis yok. Yani Pasif + "Satis Yapilmis" imkansiz.
  //    Aktif/Sadik'in satis secenekleri SERBEST birakilir (onlarda satis olabilir).
  var durum = $('#f_durum').val();
  setOptionDisabled($('#f_satis'), 'var', durum === 'pasif');
  // Ters yon: "Satis Yapilmis" secilince Pasif secilemez.
  var satis = $('#f_satis').val();
  setOptionDisabled($('#f_durum'), 'pasif', satis === 'var');

  // 2) "Hic randevu almamis" <-> "Gelmeyen" / "Iptal eden": gercekten karsilikli
  //    dislama (ortak gecerli secenek yok), bu yuzden alan bazli devre disi.
  var hic = $('#f_hic_randevu_yok').is(':checked');
  if (hic) {
    $('#f_gelmeyen').val(''); setDisabled($('#f_gelmeyen'), true);
    $('#f_iptal_eden').prop('checked', false); setDisabled($('#f_iptal_eden'), true);
    setDisabled($('#f_hic_randevu_yok'), false);
  } else {
    setDisabled($('#f_gelmeyen'), false);
    setDisabled($('#f_iptal_eden'), false);
    var gelmeyenVeyaIptal = ($('#f_gelmeyen').val() !== '' || $('#f_iptal_eden').is(':checked'));
    setDisabled($('#f_hic_randevu_yok'), gelmeyenVeyaIptal);
  }
}

var debouncedFiltre = debounce(filtreUygula, 250);

$(document).ready(function () {
  filtreBagimliliklari();
  filtreUygula();

  $('#f_kayit').on('change', function () {
    if ($(this).val() === 'ozel') { $('.ozel-tarih').show(); } else { $('.ozel-tarih').hide(); }
  });

  // Her filtre degisiminde once bagimliliklari guncelle, sonra sorguyu calistir
  $(document).on('change', '.arama-filtre', function () {
    filtreBagimliliklari();
    debouncedFiltre();
  });

  $('#musteriarama').on('input', debounce(function () { searchTerm = $(this).val().trim(); filtreUygula(); }, 400));

  $('#selectAllBtn').click(function () {
    $.ajax({
      url: '/isletmeyonetim/arama_filtre_onizleme', method: 'POST',
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
      if ((currentPage * perPage) < totalCustomers) { currentPage++; loadMore(currentPage); }
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
      type: "POST", url: '/isletmeyonetim/arama_listesi_ekle', dataType: "json",
      data: formData, processData: false, contentType: false,
      headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
      beforeSend: function () { $('#preloader').show(); },
      success: function (result) {
        $('#preloader').hide();
        $('button[data-dismiss="modal"]').trigger('click');
        swal({ type: "success", title: "Başarılı", text: result.mesaj, timer: 3000, showConfirmButton: false });
        aramaListesiniGetir('/isletmeyonetim/arama_listesi_getir');
      },
      error: function (request) { $('#preloader').hide(); $('#Hata').html(request.responseText); }
    });
  });
});
</script>
