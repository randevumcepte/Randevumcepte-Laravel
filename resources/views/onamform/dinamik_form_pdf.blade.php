<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
   body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
   h3 { text-align: center; margin-bottom: 4px; }
   .subtitle { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 2px; text-decoration: underline; }
   .isletme { text-align: center; font-size: 13px; margin-bottom: 16px; }
   hr { border: 1px solid #999; margin: 10px 0; }
   .info-tablo { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
   .info-tablo td { padding: 3px 6px; font-size: 12px; }
   .info-tablo .etiket { font-weight: bold; width: 35%; }
   .soru-blok { margin-bottom: 8px; border-bottom: 1px dotted #ccc; padding-bottom: 6px; }
   .soru-metin { font-weight: bold; font-size: 12px; }
   .soru-cevap { margin-top: 3px; font-size: 12px; padding-left: 10px; color: #333; }
   .bilgi-metni { background: #f5f5f5; padding: 6px; font-size: 11px; color: #444; margin-bottom: 8px; }
   .imza-alani { margin-top: 20px; }
   .imza-alani table { width: 100%; }
   .imza-alani td { vertical-align: bottom; padding: 5px; }
   .imza-kutu { border: 1px dashed #888; height: 70px; text-align: center; }
   .tarih-alani { margin-top: 16px; font-size: 11px; color: #555; }
   .evethayir-evet { color: #c0392b; font-weight: bold; }
   .evethayir-hayir { color: #27ae60; font-weight: bold; }
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
      <td>{{ $arsiv->musteri ? $arsiv->musteri->name : '-' }}</td>
      <td class="etiket">Telefon:</td>
      <td>{{ $arsiv->musteri ? $arsiv->musteri->cep_telefon : '-' }}</td>
   </tr>
   <tr>
      <td class="etiket">Tarih:</td>
      <td>{{ $arsiv->created_at ? date('d.m.Y H:i', strtotime($arsiv->created_at)) : '-' }}</td>
      <td class="etiket">Personel:</td>
      <td>{{ $arsiv->personel ? $arsiv->personel->adi_soyadi : '-' }}</td>
   </tr>
</table>

<hr>

@foreach($sorular as $idx => $soru)
   @if($soru['tip'] === 'bilgi_metni')
      <div class="bilgi-metni">{{ $soru['soru'] }}</div>
   @else
      <div class="soru-blok">
         <div class="soru-metin">{{ $idx + 1 }}. {{ $soru['soru'] }}</div>
         <div class="soru-cevap">
            @if(isset($cevaplar[$idx]))
               @if($soru['tip'] === 'evet_hayir')
                  @if($cevaplar[$idx] === 'evet')
                     <span class="evethayir-evet">✔ Evet</span>
                  @elseif($cevaplar[$idx] === 'hayir')
                     <span class="evethayir-hayir">✔ Hayır</span>
                  @else
                     -
                  @endif
               @else
                  {{ $cevaplar[$idx] ?: '-' }}
               @endif
            @else
               -
            @endif
         </div>
      </div>
   @endif
@endforeach

<div class="imza-alani">
   <table>
      <tr>
         <td style="width:60%;">
            <div class="imza-kutu">
               @if($arsiv->musteri_imza && str_starts_with($arsiv->musteri_imza, 'data:'))
                  <img src="{{ $arsiv->musteri_imza }}" style="max-height:65px; max-width:180px;">
               @else
                  <span style="color:#aaa; font-size:11px;">İmza</span>
               @endif
            </div>
            <div style="text-align:center; font-size:11px; margin-top:4px;">Müşteri İmzası</div>
         </td>
         <td style="width:40%; font-size:11px; color:#555;">
            <p>Tarih: {{ $arsiv->created_at ? date('d.m.Y', strtotime($arsiv->created_at)) : '' }}</p>
            <p>Durum: @if($arsiv->durum == 1) Onaylandı @elseif($arsiv->durum == 0) İptal Edildi @else Beklemede @endif</p>
         </td>
      </tr>
   </table>
</div>

</body>
</html>
