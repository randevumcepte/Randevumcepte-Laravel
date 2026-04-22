<!DOCTYPE html>
<html lang="tr">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
   <title>{{ $form_baslik }} — {{ $isletme->salon_adi }}</title>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/vendors/styles/core.css') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/vendors/styles/style.css?v=1.13') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/css/sign/jquery.signaturepad.css') }}">
   <style>
      body { background: #f5f6fa; font-family: 'Inter', sans-serif; }
      .form-kapsayici { max-width: 720px; margin: 30px auto; padding: 0 15px 40px; }
      .isletme-baslik { text-align: center; padding: 20px; background: #5C008E; color: white; border-radius: 10px 10px 0 0; margin-bottom: 0; }
      .isletme-baslik h4 { margin: 0; font-weight: 700; }
      .isletme-baslik p { margin: 5px 0 0; opacity: 0.85; font-size: 14px; }
      .form-kart { background: white; border-radius: 0 0 10px 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); padding: 30px; }
      .aciklama-kutusu { background: #f8f9fa; border-left: 4px solid #5C008E; padding: 12px 16px; border-radius: 4px; margin-bottom: 24px; font-size: 13px; color: #555; }
      .soru-satiri { margin-bottom: 18px; padding: 14px 16px; background: #fafafa; border: 1px solid #e9ecef; border-radius: 8px; }
      .soru-satiri.bilgi-metni { background: #e8f4fd; border-color: #bee3f8; }
      .soru-etiketi { font-weight: 600; font-size: 14px; color: #333; margin-bottom: 10px; display: block; }
      .soru-etiketi .zorunlu { color: red; margin-left: 3px; }
      .evet-hayir-grup { display: flex; gap: 20px; }
      .evet-hayir-btn { flex: 1; text-align: center; padding: 10px; border: 2px solid #dee2e6; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; }
      .evet-hayir-btn.evet-secili { border-color: #dc3545; background: #fff0f0; color: #dc3545; }
      .evet-hayir-btn.hayir-secili { border-color: #28a745; background: #f0fff4; color: #28a745; }
      .evet-hayir-btn:hover { border-color: #5C008E; }
      .imza-alani { border: 2px dashed #dee2e6; border-radius: 8px; background: white; cursor: crosshair; }
      .imza-baslik { font-weight: 600; margin-bottom: 10px; }
      .otp-kutu { background: #fffbf0; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 24px 0; }
      .otp-kutu h6 { color: #856404; font-weight: 700; margin-bottom: 8px; }
      .otp-input { font-size: 28px; letter-spacing: 12px; text-align: center; font-weight: 700; border: 2px solid #ffc107; border-radius: 8px; width: 100%; padding: 10px; }
      .gonder-btn { width: 100%; padding: 14px; font-size: 16px; font-weight: 700; background: #5C008E; border: none; color: white; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
      .gonder-btn:hover { background: #7b00be; }
      .gonder-btn:disabled { background: #aaa; cursor: not-allowed; }
      .basarili-mesaj { text-align: center; padding: 50px 20px; }
      .basarili-mesaj i { font-size: 64px; color: #28a745; }
      .basarili-mesaj h4 { margin-top: 20px; color: #155724; }
      .hata-mesaji { display: none; color: red; font-size: 12px; margin-top: 5px; }
   </style>
</head>
<body>

@if($zaten_dolduruldu)
<div class="form-kapsayici">
   <div class="isletme-baslik">
      <h4>{{ $isletme->salon_adi }}</h4>
      <p>{{ $form_baslik }}</p>
   </div>
   <div class="form-kart">
      <div class="basarili-mesaj">
         <i class="fa fa-check-circle"></i>
         <h4>Form Zaten Dolduruldu</h4>
         <p class="text-muted">Bu formu daha önce doldurdunuz. Herhangi bir sorunuz olursa işletmeyle iletişime geçin.</p>
      </div>
   </div>
</div>
@else
<div class="form-kapsayici" id="form_bolumu">
   <div class="isletme-baslik">
      <h4>{{ $isletme->salon_adi }}</h4>
      <p>{{ $form_baslik }}</p>
   </div>
   <div class="form-kart">
      @if($aciklama)
      <div class="aciklama-kutusu">
         {!! nl2br(e($aciklama)) !!}
      </div>
      @endif

      <div class="row mb-3">
         <div class="col-md-6 mb-2">
            <label style="font-size:13px; color:#666;">Ad Soyad</label>
            <input type="text" class="form-control" value="{{ $musteri->name ?? '' }}" readonly style="background:#f8f9fa;">
         </div>
         <div class="col-md-6 mb-2">
            <label style="font-size:13px; color:#666;">Telefon</label>
            <input type="text" class="form-control" value="{{ $musteri->cep_telefon ?? '' }}" readonly style="background:#f8f9fa;">
         </div>
      </div>

      <hr style="margin: 20px 0;">

      <div id="sorular_bolumu">
         @foreach($sorular as $idx => $soru)
            @if($soru['tip'] === 'bilgi_metni')
               <div class="soru-satiri bilgi-metni">
                  <p style="margin:0; font-size:13px; color:#2c5282;">{!! nl2br(e($soru['soru'])) !!}</p>
               </div>
            @elseif($soru['tip'] === 'evet_hayir')
               <div class="soru-satiri">
                  <span class="soru-etiketi">
                     {{ $idx + 1 }}. {{ $soru['soru'] }}
                     @if(!empty($soru['zorunlu'])) <span class="zorunlu">*</span> @endif
                  </span>
                  <div class="evet-hayir-grup" id="grup_{{ $idx }}">
                     <div class="evet-hayir-btn" id="evet_{{ $idx }}" onclick="evHayirSec({{ $idx }}, 'evet')">Evet</div>
                     <div class="evet-hayir-btn" id="hayir_{{ $idx }}" onclick="evHayirSec({{ $idx }}, 'hayir')">Hayır</div>
                  </div>
                  <input type="hidden" id="cevap_{{ $idx }}" value="" data-tip="evet_hayir" data-zorunlu="{{ !empty($soru['zorunlu']) ? '1' : '0' }}">
                  <div class="hata-mesaji" id="hata_{{ $idx }}">Bu soruyu cevaplamak zorunludur.</div>
               </div>
            @elseif($soru['tip'] === 'metin')
               <div class="soru-satiri">
                  <label class="soru-etiketi">
                     {{ $idx + 1 }}. {{ $soru['soru'] }}
                     @if(!empty($soru['zorunlu'])) <span class="zorunlu">*</span> @endif
                  </label>
                  <input type="text" class="form-control" id="cevap_{{ $idx }}" placeholder="Cevabınızı yazın..." data-tip="metin" data-zorunlu="{{ !empty($soru['zorunlu']) ? '1' : '0' }}">
                  <div class="hata-mesaji" id="hata_{{ $idx }}">Bu alan zorunludur.</div>
               </div>
            @elseif($soru['tip'] === 'uzun_metin')
               <div class="soru-satiri">
                  <label class="soru-etiketi">
                     {{ $idx + 1 }}. {{ $soru['soru'] }}
                     @if(!empty($soru['zorunlu'])) <span class="zorunlu">*</span> @endif
                  </label>
                  <textarea class="form-control" id="cevap_{{ $idx }}" rows="3" placeholder="Cevabınızı yazın..." data-tip="uzun_metin" data-zorunlu="{{ !empty($soru['zorunlu']) ? '1' : '0' }}"></textarea>
                  <div class="hata-mesaji" id="hata_{{ $idx }}">Bu alan zorunludur.</div>
               </div>
            @endif
         @endforeach
      </div>

      <hr style="margin: 24px 0;">

      {{-- Dijital İmza --}}
      <div class="mb-4">
         <p class="imza-baslik"><i class="fa fa-pencil"></i> Dijital İmza <span style="color:red;">*</span></p>
         <p style="font-size:12px; color:#666; margin-bottom:8px;">Aşağıdaki alana parmağınızla veya farenizle imzanızı atın.</p>
         <canvas id="imza_canvas" class="imza-alani" width="100%" height="200" style="width:100%; height:200px; display:block;"></canvas>
         <div style="text-align:right; margin-top:6px;">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imzaTemizle()">
               <i class="fa fa-eraser"></i> Temizle
            </button>
         </div>
         <div class="hata-mesaji" id="hata_imza">İmza zorunludur.</div>
      </div>

      {{-- OTP Doğrulama --}}
      <div class="otp-kutu">
         <h6><i class="fa fa-mobile"></i> SMS Doğrulama Kodu</h6>
         <p style="font-size:13px; color:#856404; margin-bottom:12px;">
            Telefonunuza gönderilen 4 haneli onay kodunu girin.
         </p>
         <input type="text" id="otp_input" class="otp-input" maxlength="4" placeholder="_ _ _ _" inputmode="numeric" pattern="[0-9]*">
         <div class="hata-mesaji" id="hata_otp">Onay kodu zorunludur.</div>
      </div>

      <button type="button" class="gonder-btn" id="gonder_btn" onclick="formuGonder()">
         <i class="fa fa-paper-plane"></i> Formu Gönder
      </button>
   </div>
</div>

<div class="form-kapsayici" id="basarili_bolumu" style="display:none;">
   <div class="isletme-baslik">
      <h4>{{ $isletme->salon_adi }}</h4>
   </div>
   <div class="form-kart">
      <div class="basarili-mesaj">
         <i class="fa fa-check-circle"></i>
         <h4>Form Başarıyla Gönderildi!</h4>
         <p class="text-muted">Onam formunuz {{ $isletme->salon_adi }} tarafından incelenecek ve onaylanacaktır.</p>
         <p class="text-muted" style="font-size:13px;">Bu pencereyi kapatabilirsiniz.</p>
      </div>
   </div>
</div>
@endif

<script src="{{ secure_asset('public/yeni_panel/vendors/scripts/core.js') }}"></script>
<script src="{{ secure_asset('public/yeni_panel/vendors/scripts/script.min.js') }}"></script>
<script src="{{ secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ secure_asset('public/js/sign/jquery.signaturepad.min.js') }}"></script>

<script>
var arsivId = {{ $arsiv->id }};
var userId = {{ $musteri->id ?? 0 }};
var soruSayisi = {{ count($sorular) }};
var imzaCizildi = false;

// İmza canvas kurulumu
var canvas = document.getElementById('imza_canvas');
if (canvas) {
   var ctx = canvas.getContext('2d');
   canvas.width = canvas.offsetWidth;

   var ciziyorMu = false;
   var sonX, sonY;

   function koordinatAl(e) {
      var rect = canvas.getBoundingClientRect();
      if (e.touches) {
         return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
      }
      return { x: e.clientX - rect.left, y: e.clientY - rect.top };
   }

   canvas.addEventListener('mousedown', function(e) { ciziyorMu = true; var p = koordinatAl(e); sonX = p.x; sonY = p.y; });
   canvas.addEventListener('mousemove', function(e) {
      if (!ciziyorMu) return;
      var p = koordinatAl(e);
      ctx.beginPath(); ctx.moveTo(sonX, sonY); ctx.lineTo(p.x, p.y);
      ctx.strokeStyle = '#1a1a1a'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
      sonX = p.x; sonY = p.y; imzaCizildi = true;
   });
   canvas.addEventListener('mouseup', function() { ciziyorMu = false; });
   canvas.addEventListener('touchstart', function(e) { e.preventDefault(); ciziyorMu = true; var p = koordinatAl(e); sonX = p.x; sonY = p.y; }, { passive: false });
   canvas.addEventListener('touchmove', function(e) {
      e.preventDefault();
      if (!ciziyorMu) return;
      var p = koordinatAl(e);
      ctx.beginPath(); ctx.moveTo(sonX, sonY); ctx.lineTo(p.x, p.y);
      ctx.strokeStyle = '#1a1a1a'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
      sonX = p.x; sonY = p.y; imzaCizildi = true;
   }, { passive: false });
   canvas.addEventListener('touchend', function() { ciziyorMu = false; });
}

function imzaTemizle() {
   if (canvas) { ctx.clearRect(0, 0, canvas.width, canvas.height); imzaCizildi = false; }
}

function evHayirSec(idx, deger) {
   $('#evet_' + idx).removeClass('evet-secili hayir-secili');
   $('#hayir_' + idx).removeClass('evet-secili hayir-secili');
   if (deger === 'evet') {
      $('#evet_' + idx).addClass('evet-secili');
   } else {
      $('#hayir_' + idx).addClass('hayir-secili');
   }
   $('#cevap_' + idx).val(deger);
   $('#hata_' + idx).hide();
}

function formuGonder() {
   var hatalar = false;

   // Soruları doğrula
   for (var i = 0; i < soruSayisi; i++) {
      var el = $('#cevap_' + i);
      if (!el.length) continue;
      var zorunlu = el.data('zorunlu') == '1';
      var deger = el.val();
      if (zorunlu && !deger) {
         $('#hata_' + i).show();
         hatalar = true;
      } else {
         $('#hata_' + i).hide();
      }
   }

   // İmza doğrula
   if (!imzaCizildi) {
      $('#hata_imza').show();
      hatalar = true;
   } else {
      $('#hata_imza').hide();
   }

   // OTP doğrula
   var otp = $('#otp_input').val().trim();
   if (!otp || otp.length < 4) {
      $('#hata_otp').show();
      hatalar = true;
   } else {
      $('#hata_otp').hide();
   }

   if (hatalar) {
      $('html, body').animate({ scrollTop: 0 }, 400);
      return;
   }

   // Cevapları topla
   var cevaplar = [];
   for (var j = 0; j < soruSayisi; j++) {
      var el = $('#cevap_' + j);
      if (!el.length) continue;
      cevaplar.push({ indeks: j, cevap: el.val() });
   }

   var imzaData = canvas ? canvas.toDataURL('image/png') : '';

   $('#gonder_btn').prop('disabled', true).text('Gönderiliyor...');

   $.post('/onam-form-kaydet', {
      _token: '{{ csrf_token() }}',
      arsiv_id: arsivId,
      user_id: userId,
      cevaplar_json: JSON.stringify(cevaplar),
      musteri_imza: imzaData,
      dogrulama_kodu: otp
   }, function(resp) {
      if (resp && resp.basarili) {
         $('#form_bolumu').hide();
         $('#basarili_bolumu').show();
         $('html, body').animate({ scrollTop: 0 }, 300);
      } else {
         var mesaj = resp.mesaj || 'Bir hata oluştu.';
         Swal.fire('Hata', mesaj, 'error');
         $('#gonder_btn').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Formu Gönder');
      }
   }).fail(function() {
      Swal.fire('Hata', 'Sunucu hatası oluştu. Lütfen tekrar deneyin.', 'error');
      $('#gonder_btn').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Formu Gönder');
   });
}

// OTP: sadece rakam
$('#otp_input').on('input', function() {
   this.value = this.value.replace(/[^0-9]/g, '').substring(0, 4);
});
</script>
</body>
</html>
