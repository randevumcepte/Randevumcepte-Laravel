<!DOCTYPE html>
<html lang="tr">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
   <title>Hizmet Sözleşmesi — {{ $isletme->salon_adi }}</title>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/vendors/styles/core.css') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/vendors/styles/icon-font.min.css') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/vendors/styles/style.css?v=1.13') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.css') }}">
   <link rel="stylesheet" href="{{ secure_asset('public/css/sign/jquery.signaturepad.css') }}">
   <style>
      body { background: #f5f6fa; font-family: 'Inter', sans-serif; }
      .form-kapsayici { max-width: 720px; margin: 30px auto; padding: 0 15px 40px; }
      .isletme-baslik { text-align: center; padding: 20px; background: #5C008E; color: #fff !important; border-radius: 10px 10px 0 0; }
      .isletme-baslik h4, .isletme-baslik p { color: #fff !important; margin: 0; }
      .form-kart { background: white; border-radius: 0 0 10px 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); padding: 30px; }
      .bilgi-tablo { width:100%; border-collapse: collapse; margin-bottom:20px; }
      .bilgi-tablo td { padding:8px 10px; border-bottom:1px solid #eee; font-size:14px; }
      .bilgi-tablo td.etiket { font-weight:600; color:#555; width:40%; background:#fafafa; }
      .sozlesme-metni { background:#f8f9fa; border-left:4px solid #5C008E; padding:15px; border-radius:4px; font-size:13px; color:#333; line-height:1.7; margin-bottom:20px; }
      .imza-alani { border: 2px dashed #dee2e6; border-radius: 8px; background: white; cursor: crosshair; }
      .otp-kutu { background: #fffbf0; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 24px 0; }
      .otp-input { font-size: 28px; letter-spacing: 12px; text-align: center; font-weight: 700; border: 2px solid #ffc107; border-radius: 8px; width: 100%; padding: 10px; }
      .gonder-btn { width: 100%; padding: 14px; font-size: 16px; font-weight: 700; background: #5C008E; border: none; color: white; border-radius: 8px; cursor: pointer; }
      .basarili-mesaj { text-align:center; padding:50px 20px; }
      .basarili-mesaj i { font-size:64px; color:#28a745; }
      .hata-mesaji { display:none; color:red; font-size:12px; margin-top:5px; }
   </style>
</head>
<body>

@if($zaten_imzalandi)
<div class="form-kapsayici">
   <div class="isletme-baslik"><h4>{{ $isletme->salon_adi }}</h4><p style="margin-top:5px;">Hizmet Sözleşmesi</p></div>
   <div class="form-kart">
      <div class="basarili-mesaj">
         <i class="fa fa-check-circle"></i>
         <h4 style="margin-top:20px; color:#155724;">Sözleşme Zaten İmzalandı</h4>
         <p class="text-muted">Bu sözleşmeyi daha önce imzaladınız.</p>
      </div>
   </div>
</div>
@else
<div class="form-kapsayici" id="form_bolumu">
   <div class="isletme-baslik"><h4>{{ $isletme->salon_adi }}</h4><p style="margin-top:5px;">HİZMET SÖZLEŞMESİ</p></div>
   <div class="form-kart">

      <table class="bilgi-tablo">
         <tr><td class="etiket">Müşteri Ad Soyad</td><td>{{ $musteri->name ?? '-' }}</td></tr>
         <tr><td class="etiket">Telefon</td><td>{{ $musteri->cep_telefon ?? '-' }}</td></tr>
         <tr><td class="etiket">Tarih</td><td>{{ date('d.m.Y') }}</td></tr>
         @if($hizmet_adi)<tr><td class="etiket">Hizmet</td><td>{{ $hizmet_adi }}</td></tr>@endif
         @if($paket_adi)<tr><td class="etiket">Paket</td><td>{{ $paket_adi }}</td></tr>@endif
         @if($arsiv->seans_sayisi)<tr><td class="etiket">Seans Sayısı</td><td>{{ $arsiv->seans_sayisi }}</td></tr>@endif
         <tr><td class="etiket">Toplam Ücret</td><td><b>{{ number_format($arsiv->toplam_ucret ?? 0, 2, ',', '.') }} ₺</b></td></tr>
         @if($arsiv->kapora > 0)
         <tr><td class="etiket">Kapora / Ön Ödeme</td><td>{{ number_format($arsiv->kapora, 2, ',', '.') }} ₺</td></tr>
         <tr><td class="etiket">Kalan Bakiye</td><td><b>{{ number_format(($arsiv->toplam_ucret - $arsiv->kapora), 2, ',', '.') }} ₺</b></td></tr>
         @endif
      </table>

      <div class="sozlesme-metni">
         <b>SÖZLEŞME ŞARTLARI:</b><br>
         1. Bu sözleşme <b>{{ $isletme->salon_adi }}</b> ile <b>{{ $musteri->name ?? '-' }}</b> arasında akdedilmiştir.<br>
         2. Hizmet bedeli toplam <b>{{ number_format($arsiv->toplam_ucret ?? 0, 2, ',', '.') }} ₺</b>'dir.
         @if($arsiv->kapora > 0) Müşteri tarafından <b>{{ number_format($arsiv->kapora, 2, ',', '.') }} ₺</b> kapora alınmış olup, kalan tutar hizmet süresi içinde tahsil edilecektir.@endif<br>
         3. Müşteri belirlenen randevu saatlerinde işletmede hazır bulunmakla yükümlüdür. Mazeretsiz iptaller ücret iadesi gerektirmez.<br>
         4. İşletme, hizmeti taahhüt edilen kalitede sunmakla yükümlüdür.<br>
         5. Taraflar bu sözleşmeyi okuyup, kabul etmiş sayılır.
         @if($arsiv->sozlesme_notu)<br><br><b>Ek Not:</b> {{ $arsiv->sozlesme_notu }}@endif
      </div>

      <hr>

      <div class="mb-4">
         <p style="font-weight:600;"><i class="fa fa-pencil"></i> Dijital İmza <span style="color:red;">*</span></p>
         <p style="font-size:12px; color:#666;">Aşağıdaki alana parmağınızla veya farenizle imzanızı atın.</p>
         <canvas id="imza_canvas" class="imza-alani" width="100%" height="200" style="width:100%; height:200px; display:block;"></canvas>
         <div style="text-align:right; margin-top:6px;">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="imzaTemizle()"><i class="fa fa-eraser"></i> Temizle</button>
         </div>
         <div class="hata-mesaji" id="hata_imza">İmza zorunludur.</div>
      </div>

      <div class="otp-kutu">
         <h6 style="color:#856404; font-weight:700;"><i class="fa fa-mobile"></i> SMS Doğrulama Kodu</h6>
         <p style="font-size:12px; margin:5px 0;">Cep telefonunuza gelen 4 haneli onay kodunu girin.</p>
         <input type="text" id="otp_input" class="otp-input" maxlength="4" inputmode="numeric" placeholder="••••">
         <div class="hata-mesaji" id="hata_otp">Geçerli bir onay kodu girin.</div>
      </div>

      <button type="button" class="gonder-btn" id="gonder_btn" onclick="sozlesmeyiGonder()"><i class="fa fa-paper-plane"></i> Sözleşmeyi İmzala ve Gönder</button>
   </div>
</div>

<div class="form-kapsayici" id="basarili_bolumu" style="display:none;">
   <div class="isletme-baslik"><h4>{{ $isletme->salon_adi }}</h4><p style="margin-top:5px;">Hizmet Sözleşmesi</p></div>
   <div class="form-kart">
      <div class="basarili-mesaj">
         <i class="fa fa-check-circle"></i>
         <h4 style="margin-top:20px; color:#155724;">Sözleşme Başarıyla İmzalandı</h4>
         <p class="text-muted">Sözleşmeniz işletmeye iletildi. Teşekkürler!</p>
      </div>
   </div>
</div>
@endif

<script src="{{ secure_asset('public/yeni_panel/vendors/scripts/core.js') }}"></script>
<script src="{{ secure_asset('public/yeni_panel/src/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
<script>
var arsivId = {{ $arsiv->id }};
var userId = {{ $musteri->id ?? 0 }};
var canvas = document.getElementById('imza_canvas'); var ctx = canvas ? canvas.getContext('2d') : null;
var imzaCizildi = false; var ciziyor = false;

function canvasBoyutAyarla(){
   if(!canvas) return;
   canvas.width = canvas.offsetWidth; canvas.height = 200;
   ctx.lineWidth = 2; ctx.lineCap='round'; ctx.strokeStyle='#333';
}
window.addEventListener('load', canvasBoyutAyarla);
window.addEventListener('resize', canvasBoyutAyarla);

function pos(e){
   var rect = canvas.getBoundingClientRect();
   var t = e.touches ? e.touches[0] : e;
   return { x: t.clientX - rect.left, y: t.clientY - rect.top };
}
function start(e){ e.preventDefault(); ciziyor=true; imzaCizildi=true; var p=pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); }
function draw(e){ if(!ciziyor) return; e.preventDefault(); var p=pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); }
function end(){ ciziyor=false; }
if(canvas){
   canvas.addEventListener('mousedown',start); canvas.addEventListener('mousemove',draw);
   canvas.addEventListener('mouseup',end); canvas.addEventListener('mouseleave',end);
   canvas.addEventListener('touchstart',start); canvas.addEventListener('touchmove',draw);
   canvas.addEventListener('touchend',end);
}
function imzaTemizle(){ if(!ctx) return; ctx.clearRect(0,0,canvas.width,canvas.height); imzaCizildi=false; }

function sozlesmeyiGonder(){
   var hata=false;
   if(!imzaCizildi){ $('#hata_imza').show(); hata=true; } else $('#hata_imza').hide();
   var otp = $('#otp_input').val().trim();
   if(!otp || otp.length<4){ $('#hata_otp').show(); hata=true; } else $('#hata_otp').hide();
   if(hata) return;
   var imzaData = canvas ? canvas.toDataURL('image/png') : '';
   $('#gonder_btn').prop('disabled',true).text('Gönderiliyor...');
   $.post('/sozlesme-kaydet', {
      _token: '{{ csrf_token() }}',
      arsiv_id: arsivId, user_id: userId,
      musteri_imza: imzaData, dogrulama_kodu: otp
   }, function(resp){
      if(resp && resp.basarili){
         $('#form_bolumu').hide(); $('#basarili_bolumu').show();
         $('html,body').animate({scrollTop:0},300);
      } else {
         Swal.fire('Hata', resp.mesaj || 'Bir hata oluştu', 'error');
         $('#gonder_btn').prop('disabled',false).html('<i class="fa fa-paper-plane"></i> Sözleşmeyi İmzala ve Gönder');
      }
   }).fail(function(){ Swal.fire('Hata','Sunucu hatası','error'); $('#gonder_btn').prop('disabled',false).html('<i class="fa fa-paper-plane"></i> Sözleşmeyi İmzala ve Gönder'); });
}

$('#otp_input').on('input', function(){ this.value = this.value.replace(/[^0-9]/g,'').substring(0,4); });
</script>
</body>
</html>
