@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
@if(request()->embed == 1)
<style>
   .header, .left-side-bar, .mobile-menu-overlay, nav[aria-label="breadcrumb"], .breadcrumb { display: none !important; }
   .main-container { margin-left: 0 !important; padding-top: 10px !important; }
   body { background: transparent !important; }
   .page-header { padding-top: 0 !important; }
</style>
@endif
<div class="page-header">
   <div class="row">
      <div class="col-md-6 col-sm-12">
         <div class="title"><h1>{{$sayfa_baslik}}</h1></div>
         <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="/isletmeyonetim{{(isset($_GET['sube'])) ? '?sube='.$isletme->id : '' }}">Ana Sayfa</a></li>
               <li class="breadcrumb-item active" aria-current="page">{{$sayfa_baslik}}</li>
            </ol>
         </nav>
      </div>
      <div class="col-md-6 col-sm-12 text-right">
         <button type="button" onclick="yeniFormAc()" class="btn btn-success btn-lg">
            <i class="fa fa-plus"></i> Yeni Form Şablonu
         </button>
      </div>
   </div>
</div>

<div class="card-box mb-30">
   <div style="padding: 20px">
      <table class="data-table table stripe hover nowrap" id="form_sablonlari_liste">
         <thead>
            <th>Form Adı</th>
            <th>Eleman Sayısı</th>
            <th>Oluşturulma</th>
            <th>İşlemler</th>
         </thead>
         <tbody>
            @foreach($formlar as $form)
            <tr>
               <td>{{$form->form_adi}}</td>
               <td>
                  @if($form->sorular_json)
                     {{ count(json_decode($form->sorular_json, true) ?? []) }} eleman
                  @else
                     -
                  @endif
               </td>
               <td>{{ $form->created_at ? date('d.m.Y', strtotime($form->created_at)) : '-' }}</td>
               <td>
                  <button class="btn btn-sm btn-primary" onclick="formDuzenle({{$form->id}})">
                     <i class="fa fa-edit"></i> Düzenle
                  </button>
                  <a href="/isletmeyonetim/bosFormIndirDinamik?formId={{$form->id}}&sube={{$isletme->id}}" class="btn btn-sm btn-info" target="_blank">
                     <i class="fa fa-download"></i> PDF
                  </a>
                  <button class="btn btn-sm btn-danger" onclick="formSil({{$form->id}}, '{{addslashes($form->form_adi)}}')">
                     <i class="fa fa-trash"></i> Sil
                  </button>
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
</div>

{{-- Form Oluştur/Düzenle Modal --}}
<div id="formSablonModal" class="modal modal-top fade calendar-modal">
   <div class="modal-dialog" style="max-width: 950px">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="h4" id="modalBaslik">Yeni Form Şablonu</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body" style="max-height:80vh; overflow-y:auto;">
            {{ csrf_field() }}
            <input type="hidden" id="form_id_gizli" value="">
            <input type="hidden" name="sube" value="{{$isletme->id}}">

            <div class="row">
               <div class="col-md-8 form-group">
                  <label><b>Form Adı *</b></label>
                  <input type="text" id="form_adi_input" class="form-control" placeholder="Örn: Lazer Epilasyon Onam Formu" maxlength="200">
               </div>
               <div class="col-md-12 form-group">
                  <label><b>Form Başlık Açıklaması</b> <small class="text-muted">(formun hemen üstünde gri kutuda gösterilir)</small></label>
                  <textarea id="form_aciklama_input" class="form-control" rows="2" placeholder="Bu formdaki açıklamaların amacı..."></textarea>
               </div>
            </div>

            <hr>
            <h5><b>Form İçeriği</b></h5>
            <p class="text-muted" style="font-size:12px;">
               Aşağıdaki butonlarla form elemanları ekleyin. Sürükle bırak yerine ↑↓ butonlarıyla sıralayabilirsiniz.
            </p>

            <div id="sorular_konteyneri"></div>

            <div class="row mt-2">
               <div class="col-md-12">
                  <div class="mb-1"><small class="text-muted font-weight-bold">YAPI ELEMANLARI</small></div>
                  <button type="button" class="btn btn-outline-dark btn-sm mb-1" onclick="soruEkle('bolum_basligi')">
                     <i class="fa fa-header"></i> Bölüm Başlığı
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="soruEkle('alt_baslik')">
                     <i class="fa fa-bold"></i> Alt Başlık
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="soruEkle('metin_blogu')">
                     <i class="fa fa-paragraph"></i> Metin Bloğu
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="soruEkle('madde_listesi')">
                     <i class="fa fa-list-ul"></i> Madde Listesi
                  </button>
                  <button type="button" class="btn btn-outline-info btn-sm mb-1" onclick="soruEkle('not_kutusu')">
                     <i class="fa fa-exclamation-circle"></i> Not Kutusu
                  </button>
               </div>
               <div class="col-md-12 mt-2">
                  <div class="mb-1"><small class="text-muted font-weight-bold">MÜŞTERİ CEVAPLI ALANLAR</small></div>
                  <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="soruEkle('evet_hayir')">
                     <i class="fa fa-check-square-o"></i> Evet/Hayır Sorusu
                  </button>
                  <button type="button" class="btn btn-outline-success btn-sm mb-1" onclick="soruEkle('metin')">
                     <i class="fa fa-minus"></i> Kısa Metin Girişi
                  </button>
                  <button type="button" class="btn btn-outline-warning btn-sm mb-1" onclick="soruEkle('uzun_metin')">
                     <i class="fa fa-align-left"></i> Uzun Metin Girişi
                  </button>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
            <button type="button" class="btn btn-success" onclick="formKaydet()">
               <i class="fa fa-save"></i> Kaydet
            </button>
         </div>
      </div>
   </div>
</div>

{{-- Silme Onay Modal --}}
<div id="silOnayModal" class="modal fade" tabindex="-1">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <h5>Formu Sil</h5>
            <button class="close" data-dismiss="modal">×</button>
         </div>
         <div class="modal-body">
            <p id="silMesaji">Bu form şablonunu silmek istediğinize emin misiniz?</p>
            <p class="text-danger" style="font-size:12px;"><i class="fa fa-warning"></i> Bu işlem geri alınamaz.</p>
         </div>
         <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" data-dismiss="modal">Vazgeç</button>
            <button class="btn btn-danger btn-sm" id="silOnayBtn">Evet, Sil</button>
         </div>
      </div>
   </div>
</div>

<script>
var soruSayaci = 0;
var silinecekFormId = null;

var TIP_RENK = {
   bolum_basligi: { renk: '#343a40', etiket: 'Bölüm Başlığı', badge: 'badge-dark' },
   alt_baslik:    { renk: '#6c757d', etiket: 'Alt Başlık',    badge: 'badge-secondary' },
   metin_blogu:   { renk: '#6c757d', etiket: 'Metin Bloğu',   badge: 'badge-secondary' },
   madde_listesi: { renk: '#6c757d', etiket: 'Madde Listesi', badge: 'badge-secondary' },
   not_kutusu:    { renk: '#17a2b8', etiket: 'Not Kutusu',    badge: 'badge-info' },
   evet_hayir:    { renk: '#5C008E', etiket: 'Evet/Hayır',    badge: 'badge-primary' },
   metin:         { renk: '#28a745', etiket: 'Kısa Metin',    badge: 'badge-success' },
   uzun_metin:    { renk: '#ffc107', etiket: 'Uzun Metin',    badge: 'badge-warning' },
   bilgi_metni:   { renk: '#17a2b8', etiket: 'Bilgi Metni',   badge: 'badge-info' },
};

function soruEkle(tip, mevcutSoru) {
   soruSayaci++;
   var idx = soruSayaci;
   var soru = mevcutSoru || { soru: '', tip: tip, zorunlu: false };
   var meta = TIP_RENK[tip] || { renk: '#999', etiket: tip, badge: 'badge-secondary' };

   var aksiyon = '';
   var zorunluKutucuk = tip === 'evet_hayir' || tip === 'metin' || tip === 'uzun_metin'
      ? `<label style="font-size:12px;"><input type="checkbox" class="soru-zorunlu" ${soru.zorunlu ? 'checked' : ''}> Zorunlu</label>`
      : `<input type="hidden" class="soru-zorunlu" value="0">`;

   var girdi = '';
   if (tip === 'bolum_basligi') {
      girdi = `<input type="text" class="form-control soru-metni font-weight-bold" placeholder="BÖLÜM BAŞLIĞI (büyük harf önerilir)..." value="${escapeHtml(soru.soru || '')}" style="font-size:13px;font-weight:bold;">`;
   } else if (tip === 'alt_baslik') {
      girdi = `<input type="text" class="form-control soru-metni" placeholder="Alt başlık metni (kalın gösterilir)..." value="${escapeHtml(soru.soru || '')}" style="font-size:13px;">`;
   } else if (tip === 'metin_blogu') {
      girdi = `<textarea class="form-control soru-metni" rows="3" placeholder="Paragraf metni..." style="font-size:13px;">${escapeHtml(soru.soru || '')}</textarea>`;
   } else if (tip === 'madde_listesi') {
      girdi = `<textarea class="form-control soru-metni" rows="4" placeholder="Her satıra bir madde yazın:\nKızarıklık (eritem).\nYan etki sadece geçici..." style="font-size:13px;">${escapeHtml(soru.soru || '')}</textarea>
               <small class="text-muted">Her satır ayrı bir madde olarak gösterilir (• işaretiyle)</small>`;
   } else if (tip === 'not_kutusu') {
      girdi = `<textarea class="form-control soru-metni" rows="2" placeholder="Not kutusu metni (kenarlıklı gri kutuda gösterilir)..." style="font-size:13px;">${escapeHtml(soru.soru || '')}</textarea>`;
   } else if (tip === 'bilgi_metni') {
      girdi = `<textarea class="form-control soru-metni" rows="2" placeholder="Bilgi/açıklama metni..." style="font-size:13px;">${escapeHtml(soru.soru || '')}</textarea>`;
   } else {
      girdi = `<input type="text" class="form-control soru-metni" placeholder="Soru metni..." value="${escapeHtml(soru.soru || '')}" style="font-size:13px;">`;
   }

   var html = `
   <div class="soru-satiri card mb-2" id="soru_${idx}" style="border-left: 4px solid ${meta.renk};">
      <div class="card-body p-2">
         <div class="row align-items-start">
            <div class="col-md-1 text-center pt-1">
               <span class="badge ${meta.badge}" style="font-size:10px;">${meta.etiket}</span>
            </div>
            <div class="col-md-8">
               ${girdi}
            </div>
            <div class="col-md-1 text-center pt-1">
               ${zorunluKutucuk}
            </div>
            <div class="col-md-1 text-center">
               <button type="button" class="btn btn-sm btn-outline-secondary btn-block mb-1" onclick="soruYukariTasi(${idx})" title="Yukarı">↑</button>
               <button type="button" class="btn btn-sm btn-outline-secondary btn-block" onclick="soruAsagiTasi(${idx})" title="Aşağı">↓</button>
            </div>
            <div class="col-md-1 text-center pt-1">
               <button type="button" class="btn btn-sm btn-danger" onclick="soruSil(${idx})" title="Sil"><i class="fa fa-trash"></i></button>
            </div>
         </div>
         <input type="hidden" class="soru-tip" value="${tip}">
      </div>
   </div>`;

   $('#sorular_konteyneri').append(html);
}

function soruSil(idx) {
   $('#soru_' + idx).remove();
}

function soruYukariTasi(idx) {
   var $el = $('#soru_' + idx);
   var $prev = $el.prev('.soru-satiri');
   if ($prev.length) $el.insertBefore($prev);
}

function soruAsagiTasi(idx) {
   var $el = $('#soru_' + idx);
   var $next = $el.next('.soru-satiri');
   if ($next.length) $el.insertAfter($next);
}

function escapeHtml(str) {
   return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function yeniFormAc() {
   $('#form_id_gizli').val('');
   $('#form_adi_input').val('');
   $('#form_aciklama_input').val('');
   $('#sorular_konteyneri').empty();
   soruSayaci = 0;
   $('#modalBaslik').text('Yeni Form Şablonu');
   $('#formSablonModal').modal('show');
}

function formDuzenle(formId) {
   $.get('/isletmeyonetim/form-sablonlari-getir?id=' + formId + '&sube={{$isletme->id}}', function(data) {
      if (!data || data.hata) {
         alert('Form bilgileri alınamadı.');
         return;
      }
      $('#form_id_gizli').val(data.id);
      $('#form_adi_input').val(data.form_adi);
      $('#form_aciklama_input').val(data.aciklama || '');
      $('#sorular_konteyneri').empty();
      soruSayaci = 0;

      var sorular = data.sorular_json ? JSON.parse(data.sorular_json) : [];
      sorular.forEach(function(soru) {
         soruEkle(soru.tip, soru);
      });

      $('#modalBaslik').text('Formu Düzenle: ' + data.form_adi);
      $('#formSablonModal').modal('show');
   });
}

function formKaydet() {
   var formAdi = $('#form_adi_input').val().trim();
   if (!formAdi) {
      alert('Form adı zorunludur.');
      return;
   }

   var sorular = [];
   $('#sorular_konteyneri .soru-satiri').each(function() {
      var tip = $(this).find('.soru-tip').val();
      var metin = $(this).find('.soru-metni').val().trim();
      var zorunluEl = $(this).find('.soru-zorunlu');
      var zorunlu = zorunluEl.is('[type=checkbox]') ? zorunluEl.is(':checked') : false;
      sorular.push({ tip: tip, soru: metin, zorunlu: zorunlu });
   });

   if (sorular.length === 0) {
      alert('En az bir eleman ekleyin.');
      return;
   }

   var formId = $('#form_id_gizli').val();
   var url = formId ? '/isletmeyonetim/form-sablonlari-guncelle' : '/isletmeyonetim/form-sablonlari-kaydet';

   $.ajax({
      url: url,
      type: 'POST',
      dataType: 'json',
      data: {
         _token: $('meta[name=csrf-token]').attr('content') || $('input[name=_token]').first().val(),
         sube: '{{$isletme->id}}',
         form_id: formId,
         form_adi: formAdi,
         aciklama: $('#form_aciklama_input').val(),
         sorular_json: JSON.stringify(sorular)
      },
      success: function(resp) {
         if (resp && resp.basarili) {
            $('#formSablonModal').modal('hide');
            setTimeout(function(){ location.reload(); }, 300);
         } else {
            alert((resp && resp.mesaj) ? resp.mesaj : 'Bir hata oluştu.');
         }
      },
      error: function(xhr) {
         alert('Sunucu hatası: ' + xhr.status + '. Lütfen tekrar deneyin.');
      }
   });
}

function formSil(formId, formAdi) {
   silinecekFormId = formId;
   $('#silMesaji').text('"' + formAdi + '" form şablonunu silmek istediğinize emin misiniz?');
   $('#silOnayModal').modal('show');
}

$(document).on('click', '#silOnayBtn', function() {
   if (!silinecekFormId) return;
   $.ajax({
      url: '/isletmeyonetim/form-sablonlari-sil',
      type: 'POST',
      dataType: 'json',
      data: {
         _token: $('meta[name=csrf-token]').attr('content') || $('input[name=_token]').first().val(),
         sube: '{{$isletme->id}}',
         form_id: silinecekFormId
      },
      success: function(resp) {
         $('#silOnayModal').modal('hide');
         if (resp && resp.basarili) {
            setTimeout(function(){ location.reload(); }, 200);
         } else {
            alert(resp.mesaj || 'Bir hata oluştu.');
         }
      },
      error: function(xhr) {
         alert('Sunucu hatası: ' + xhr.status);
      }
   });
});
</script>
@endsection
