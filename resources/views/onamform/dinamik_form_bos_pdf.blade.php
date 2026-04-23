<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
   body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
   h3 { text-align: center; margin-bottom: 4px; }
   .subtitle { text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 4px; text-decoration: underline; }
   .isletme { text-align: center; font-size: 13px; margin-bottom: 16px; }
   hr { border: 1px solid #999; margin: 10px 0; }
   .info-tablo { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
   .info-tablo td { padding: 4px 6px; font-size: 12px; }
   .info-tablo .etiket { font-weight: bold; width: 28%; }
   .doldur-kutu { border-bottom: 1px solid #333; min-width: 120px; display: inline-block; width: 100%; height: 14px; }
   .soru-blok { margin-bottom: 12px; border-bottom: 1px dotted #ccc; padding-bottom: 8px; }
   .soru-metin { font-weight: bold; font-size: 12px; margin-bottom: 4px; }
   .soru-cevap { padding-left: 10px; }
   .evethayir-secenekler { font-size: 12px; }
   .bilgi-metni { background: #f5f5f5; padding: 6px; font-size: 11px; color: #444; margin-bottom: 10px; border-left: 3px solid #ccc; }
   .imza-alani { margin-top: 30px; }
   .imza-kutu { border: 1px dashed #888; height: 80px; text-align: center; vertical-align: middle; }
   .metin-kutu { border: 1px solid #ccc; height: 20px; width: 100%; margin-top: 2px; }
   .uzun-metin-kutu { border: 1px solid #ccc; height: 50px; width: 100%; margin-top: 2px; }
</style>
</head>
<body>

<div class="isletme">{{ $isletme->salon_adi }}</div>
<div class="subtitle">{{ $form_adi }}</div>
<hr>

@if($aciklama)
<div class="bilgi-metni">{{ $aciklama }}</div>
@endif

<table class="info-tablo">
   <tr>
      <td class="etiket">Ad Soyad:</td>
      <td><span class="doldur-kutu"></span></td>
      <td class="etiket">Telefon:</td>
      <td><span class="doldur-kutu"></span></td>
   </tr>
   <tr>
      <td class="etiket">Tarih:</td>
      <td><span class="doldur-kutu"></span></td>
      <td class="etiket">Personel:</td>
      <td><span class="doldur-kutu"></span></td>
   </tr>
</table>

<hr>

@foreach($sorular as $idx => $soru)
   @if($soru['tip'] === 'bilgi_metni')
      <div class="bilgi-metni">{{ $soru['soru'] }}</div>
   @elseif($soru['tip'] === 'evet_hayir')
      <div class="soru-blok">
         <div class="soru-metin">{{ $idx + 1 }}. {{ $soru['soru'] }}</div>
         <div class="soru-cevap evethayir-secenekler">
            ☐ Evet &nbsp;&nbsp;&nbsp; ☐ Hayır
         </div>
      </div>
   @elseif($soru['tip'] === 'uzun_metin')
      <div class="soru-blok">
         <div class="soru-metin">{{ $idx + 1 }}. {{ $soru['soru'] }}</div>
         <div class="soru-cevap">
            <div class="uzun-metin-kutu"></div>
         </div>
      </div>
   @else
      <div class="soru-blok">
         <div class="soru-metin">{{ $idx + 1 }}. {{ $soru['soru'] }}</div>
         <div class="soru-cevap">
            <div class="metin-kutu"></div>
         </div>
      </div>
   @endif
@endforeach

<div class="imza-alani">
   <table width="100%">
      <tr>
         <td style="width:55%; vertical-align:bottom;">
            <table width="100%"><tr><td class="imza-kutu" style="height:80px;">&nbsp;</td></tr></table>
            <div style="text-align:center; font-size:11px; margin-top:4px;">Müşteri İmzası</div>
         </td>
         <td style="width:45%; font-size:11px; padding-left:20px; vertical-align:bottom;">
            <p>Tarih: _____ / _____ / _________</p>
         </td>
      </tr>
   </table>
</div>

</body>
</html>
