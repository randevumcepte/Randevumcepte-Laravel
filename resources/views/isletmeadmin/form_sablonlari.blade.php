@if(Auth::guard('satisortakligi')->check()) @php $_layout = 'layout.layout_isletmesatisortagi'; @endphp @else @php $_layout = 'layout.layout_isletmeadmin'; @endphp @endif @extends($_layout)
@section('content')
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
            <th>Soru Sayısı</th>
            <th>Oluşturulma</th>
            <th>İşlemler</th>
         </thead>
         <tbody>
            @foreach($formlar as $form)
            <tr>
               <td>{{$form->form_adi}}</td>
               <td>
                  @if($form->sorular_json)
                     {{ count(json_decode($form->sorular_json, true) ?? []) }} soru
                  @else
                     -
                  @endif
               </td>
               <td>{{ $form->created_at ? date('d.m.Y', strtotime($form->created_at)) : '-' }}</td>
               <td>
                  <button class="btn btn-sm btn-primary" onclick="formDuzenle({{$form->id}})">
                     <i class="fa fa-edit"></i> Düzenle
                  </button>
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
   <div class="modal-dialog" style="max-width: 900px">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="h4" id="modalBaslik">Yeni Form Şablonu</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            {{ csrf_field() }}
            <input type="hidden" id="form_id_gizli" value="">
            <input type="hidden" name="sube" value="{{$isletme->id}}">

            <div class="row">
               <div class="col-md-8 form-group">
                  <label><b>Form Adı *</b></label>
                  <input type="text" id="form_adi_input" class="form-control" placeholder="Örn: Cilt Bakımı Onam Formu" maxlength="200">
               </div>
               <div class="col-md-12 form-group">
                  <label><b>Form Açıklaması / Üst Metin</b></label>
                  <textarea id="form_aciklama_input" class="form-control" rows="2" placeholder="Formun üst kısmında görünecek açıklama metni..."></textarea>
               </div>
            </div>

            <hr>
            <h5><b>Sorular</b></h5>
            <p class="text-muted" style="font-size:13px;">Müşterilerin cevaplayacağı soruları aşağıya ekleyin.</p>

            <div id="sorular_konteyneri">
               {{-- Sorular buraya eklenir --}}
            </div>

            <div class="row" style="margin-top:10px;">
               <div class="col-md-12">
                  <div class="btn-group">
                     <button type="button" class="btn btn-outline-primary btn-sm" onclick="soruEkle('evet_hayir')">
                        <i class="fa fa-plus"></i> Evet/Hayır Sorusu
                     </button>
                     <button type="button" class="btn btn-outline-secondary btn-sm" onclick="soruEkle('metin')">
                        <i class="fa fa-plus"></i> Metin Girişi
                     </button>
                     <button type="button" class="btn btn-outline-info btn-sm" onclick="soruEkle('uzun_metin')">
                        <i class="fa fa-plus"></i> Uzun Metin
                     </button>
                     <button type="button" class="btn btn-outline-warning btn-sm" onclick="soruEkle('bilgi_metni')">
                        <i class="fa fa-plus"></i> Bilgi/Açıklama Metni
                     </button>
                  </div>
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

function soruEkle(tip, mevcutSoru) {
   soruSayaci++;
   var idx = soruSayaci;
   var soru = mevcutSoru || { soru: '', tip: tip, zorunlu: false };

   var html = '';

   if (tip === 'bilgi_metni') {
      html = `
      <div class="soru-satiri card mb-2" id="soru_${idx}" style="border-left: 4px solid #17a2b8;">
         <div class="card-body p-2">
            <div class="row align-items-center">
               <div class="col-md-1 text-center">
                  <span class="badge badge-info" style="font-size:11px;">#${idx}</span><br>
                  <small class="text-muted" style="font-size:10px;">Metin</small>
               </div>
               <div class="col-md-9">
                  <textarea class="form-control soru-metni" rows="2" placeholder="Bilgi/açıklama metni (müşteri cevaplamaz, sadece okur)..." style="font-size:13px;">${soru.soru || ''}</textarea>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="soruYukariTasi(${idx})" title="Yukarı">↑</button><br>
                  <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="soruAsagiTasi(${idx})" title="Aşağı">↓</button>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-danger" onclick="soruSil(${idx})" title="Sil"><i class="fa fa-trash"></i></button>
               </div>
            </div>
            <input type="hidden" class="soru-tip" value="bilgi_metni">
            <input type="hidden" class="soru-zorunlu" value="0">
         </div>
      </div>`;
   } else if (tip === 'evet_hayir') {
      html = `
      <div class="soru-satiri card mb-2" id="soru_${idx}" style="border-left: 4px solid #5C008E;">
         <div class="card-body p-2">
            <div class="row align-items-center">
               <div class="col-md-1 text-center">
                  <span class="badge badge-primary" style="font-size:11px;">#${idx}</span><br>
                  <small class="text-muted" style="font-size:10px;">E/H</small>
               </div>
               <div class="col-md-7">
                  <input type="text" class="form-control soru-metni" placeholder="Soru metni..." value="${escapeHtml(soru.soru || '')}" style="font-size:13px;">
               </div>
               <div class="col-md-2 text-center">
                  <label style="font-size:12px;"><input type="checkbox" class="soru-zorunlu" ${soru.zorunlu ? 'checked' : ''}> Zorunlu</label>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="soruYukariTasi(${idx})" title="Yukarı">↑</button><br>
                  <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="soruAsagiTasi(${idx})" title="Aşağı">↓</button>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-danger" onclick="soruSil(${idx})" title="Sil"><i class="fa fa-trash"></i></button>
               </div>
            </div>
            <input type="hidden" class="soru-tip" value="evet_hayir">
         </div>
      </div>`;
   } else if (tip === 'metin') {
      html = `
      <div class="soru-satiri card mb-2" id="soru_${idx}" style="border-left: 4px solid #28a745;">
         <div class="card-body p-2">
            <div class="row align-items-center">
               <div class="col-md-1 text-center">
                  <span class="badge badge-success" style="font-size:11px;">#${idx}</span><br>
                  <small class="text-muted" style="font-size:10px;">Metin</small>
               </div>
               <div class="col-md-7">
                  <input type="text" class="form-control soru-metni" placeholder="Soru metni..." value="${escapeHtml(soru.soru || '')}" style="font-size:13px;">
               </div>
               <div class="col-md-2 text-center">
                  <label style="font-size:12px;"><input type="checkbox" class="soru-zorunlu" ${soru.zorunlu ? 'checked' : ''}> Zorunlu</label>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="soruYukariTasi(${idx})" title="Yukarı">↑</button><br>
                  <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="soruAsagiTasi(${idx})" title="Aşağı">↓</button>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-danger" onclick="soruSil(${idx})" title="Sil"><i class="fa fa-trash"></i></button>
               </div>
            </div>
            <input type="hidden" class="soru-tip" value="metin">
         </div>
      </div>`;
   } else if (tip === 'uzun_metin') {
      html = `
      <div class="soru-satiri card mb-2" id="soru_${idx}" style="border-left: 4px solid #ffc107;">
         <div class="card-body p-2">
            <div class="row align-items-center">
               <div class="col-md-1 text-center">
                  <span class="badge badge-warning" style="font-size:11px;">#${idx}</span><br>
                  <small class="text-muted" style="font-size:10px;">Uzun</small>
               </div>
               <div class="col-md-7">
                  <input type="text" class="form-control soru-metni" placeholder="Soru metni..." value="${escapeHtml(soru.soru || '')}" style="font-size:13px;">
               </div>
               <div class="col-md-2 text-center">
                  <label style="font-size:12px;"><input type="checkbox" class="soru-zorunlu" ${soru.zorunlu ? 'checked' : ''}> Zorunlu</label>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="soruYukariTasi(${idx})" title="Yukarı">↑</button><br>
                  <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="soruAsagiTasi(${idx})" title="Aşağı">↓</button>
               </div>
               <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-sm btn-danger" onclick="soruSil(${idx})" title="Sil"><i class="fa fa-trash"></i></button>
               </div>
            </div>
            <input type="hidden" class="soru-tip" value="uzun_metin">
         </div>
      </div>`;
   }

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
         Swal.fire('Hata', 'Form bilgileri alınamadı.', 'error');
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
      Swal.fire('Uyarı', 'Form adı zorunludur.', 'warning');
      return;
   }

   var sorular = [];
   $('#sorular_konteyneri .soru-satiri').each(function() {
      var tip = $(this).find('.soru-tip').val();
      var metin = $(this).find('.soru-metni').val().trim();
      var zorunluEl = $(this).find('.soru-zorunlu');
      var zorunlu = zorunluEl.is('[type=checkbox]') ? zorunluEl.is(':checked') : false;

      if (metin || tip === 'bilgi_metni') {
         sorular.push({ tip: tip, soru: metin, zorunlu: zorunlu });
      }
   });

   if (sorular.length === 0) {
      Swal.fire('Uyarı', 'En az bir soru ekleyin.', 'warning');
      return;
   }

   var formId = $('#form_id_gizli').val();
   var url = formId ? '/isletmeyonetim/form-sablonlari-guncelle' : '/isletmeyonetim/form-sablonlari-kaydet';

   $.post(url, {
      _token: $('input[name=_token]').first().val(),
      sube: '{{$isletme->id}}',
      form_id: formId,
      form_adi: formAdi,
      aciklama: $('#form_aciklama_input').val(),
      sorular_json: JSON.stringify(sorular)
   }, function(resp) {
      if (resp && resp.basarili) {
         Swal.fire('Başarılı', formId ? 'Form güncellendi.' : 'Form oluşturuldu.', 'success').then(function() {
            location.reload();
         });
      } else {
         Swal.fire('Hata', resp.mesaj || 'Bir hata oluştu.', 'error');
      }
   });
}

function formSil(formId, formAdi) {
   silinecekFormId = formId;
   $('#silMesaji').text('"' + formAdi + '" form şablonunu silmek istediğinize emin misiniz?');
   $('#silOnayModal').modal('show');
}

$('#silOnayBtn').click(function() {
   if (!silinecekFormId) return;
   $.post('/isletmeyonetim/form-sablonlari-sil', {
      _token: $('input[name=_token]').first().val(),
      sube: '{{$isletme->id}}',
      form_id: silinecekFormId
   }, function(resp) {
      $('#silOnayModal').modal('hide');
      if (resp && resp.basarili) {
         Swal.fire('Başarılı', 'Form silindi.', 'success').then(function() {
            location.reload();
         });
      } else {
         Swal.fire('Hata', resp.mesaj || 'Bir hata oluştu.', 'error');
      }
   });
});
</script>
@endsection

