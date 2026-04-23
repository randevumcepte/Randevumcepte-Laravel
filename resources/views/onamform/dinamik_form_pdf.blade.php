<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
   body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; padding: 0; }
   .header-isletme { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 2px; }
   .header-form-adi { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; }
   .ust-aciklama { background: #f0f0f0; border: 1px solid #ccc; padding: 6px 8px; font-size: 10px; color: #333; margin-bottom: 10px; font-style: italic; }
   hr { border: 0; border-top: 1px solid #aaa; margin: 6px 0; }
   .info-tablo { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
   .info-tablo td { padding: 3px 4px; font-size: 11px; }
   .info-tablo .etiket { font-weight: bold; width: 22%; }
   .bolum-basligi { background: #d0d0d0; padding: 5px 8px; font-weight: bold; font-size: 12px; text-transform: uppercase; margin: 10px 0 4px 0; }
   .alt-baslik { font-weight: bold; font-size: 11px; text-align: center; margin: 6px 0 4px 0; text-decoration: underline; }
   .metin-blogu { font-size: 10.5px; margin: 4px 0 6px 0; line-height: 1.5; text-align: justify; }
   .madde-listesi { margin: 4px 0 6px 16px; padding: 0; }
   .madde-listesi li { font-size: 10.5px; margin-bottom: 2px; }
   .not-kutusu { background: #f5f5f5; border: 1px solid #bbb; padding: 6px 8px; font-size: 10px; color: #333; margin: 6px 0; }
   .bilgi-metni { background: #f5f5f5; padding: 5px 8px; font-size: 10px; color: #444; margin: 4px 0; }
   .eh-tablo { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
   .eh-tablo tr td { padding: 3px 4px; font-size: 10.5px; vertical-align: middle; border-bottom: 1px dotted #ddd; }
   .eh-tablo tr td:first-child { width: 78%; }
   .eh-tablo tr td:last-child { width: 22%; text-align: right; white-space: nowrap; }
   .metin-soru-blok { margin-bottom: 6px; border-bottom: 1px dotted #ddd; padding-bottom: 4px; }
   .metin-soru-metin { font-weight: bold; font-size: 10.5px; }
   .metin-soru-cevap { font-size: 10.5px; padding-left: 8px; color: #222; margin-top: 2px; }
   .imza-alani { margin-top: 20px; }
   .imza-kutu { border: 1px dashed #888; height: 70px; text-align: center; vertical-align: middle; }
</style>
</head>
<body>

<div class="header-isletme">{{ $isletme->salon_adi }}</div>
<div class="header-form-adi">{{ $form_adi }}</div>
<hr>

@if($aciklama)
<div class="ust-aciklama">{{ $aciklama }}</div>
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

@php
   $elemanlar = [];
   foreach ($sorular as $idx => $soru) {
      $elemanlar[] = ['idx' => $idx, 'soru' => $soru];
   }
   $toplam = count($elemanlar);
   $i = 0;
@endphp

@while($i < $toplam)
@php $item = $elemanlar[$i]; $soru = $item['soru']; $idx = $item['idx']; $tip = $soru['tip']; @endphp

@if($tip === 'bolum_basligi')
   <div class="bolum-basligi">{{ $soru['soru'] }}</div>
   @php $i++; @endphp

@elseif($tip === 'alt_baslik')
   <div class="alt-baslik">{{ $soru['soru'] }}</div>
   @php $i++; @endphp

@elseif($tip === 'metin_blogu')
   <div class="metin-blogu">{{ $soru['soru'] }}</div>
   @php $i++; @endphp

@elseif($tip === 'madde_listesi')
   <ul class="madde-listesi">
      @foreach(array_filter(array_map('trim', explode("\n", $soru['soru']))) as $madde)
         <li>{{ $madde }}</li>
      @endforeach
   </ul>
   @php $i++; @endphp

@elseif($tip === 'not_kutusu')
   <div class="not-kutusu">{{ $soru['soru'] }}</div>
   @php $i++; @endphp

@elseif($tip === 'bilgi_metni')
   <div class="bilgi-metni">{{ $soru['soru'] }}</div>
   @php $i++; @endphp

@elseif($tip === 'evet_hayir')
   <table class="eh-tablo">
   @while($i < $toplam && $elemanlar[$i]['soru']['tip'] === 'evet_hayir')
      @php $ehItem = $elemanlar[$i]; $ehIdx = $ehItem['idx']; $ehSoru = $ehItem['soru']; @endphp
      <tr>
         <td>&#9702; {{ $ehSoru['soru'] }}</td>
         <td>
            @if(isset($cevaplar[$ehIdx]))
               @if($cevaplar[$ehIdx] === 'evet')
                  <b style="color:#c0392b;">&#9745; Evet</b>&nbsp;&#9744; Hayir
               @elseif($cevaplar[$ehIdx] === 'hayir')
                  &#9744; Evet&nbsp;<b style="color:#27ae60;">&#9745; Hayir</b>
               @else
                  &#9744; Evet&nbsp;&#9744; Hayir
               @endif
            @else
               &#9744; Evet&nbsp;&#9744; Hayir
            @endif
         </td>
      </tr>
      @php $i++; @endphp
   @endwhile
   </table>

@elseif($tip === 'metin' || $tip === 'uzun_metin')
   <div class="metin-soru-blok">
      <div class="metin-soru-metin">{{ $soru['soru'] }}</div>
      <div class="metin-soru-cevap">{{ isset($cevaplar[$idx]) ? $cevaplar[$idx] : '-' }}</div>
   </div>
   @php $i++; @endphp

@else
   @php $i++; @endphp
@endif

@endwhile

<div class="imza-alani">
   <table width="100%">
      <tr>
         <td style="width:55%; vertical-align:bottom;">
            <table width="100%"><tr><td class="imza-kutu">
               @if($arsiv->musteri_imza && str_starts_with($arsiv->musteri_imza, 'data:'))
                  <img src="{{ $arsiv->musteri_imza }}" style="max-height:65px; max-width:180px;">
               @else
                  <span style="color:#aaa; font-size:10px;">Imza</span>
               @endif
            </td></tr></table>
            <div style="text-align:center; font-size:10px; margin-top:3px;">Musteri Imzasi</div>
         </td>
         <td style="width:45%; font-size:10px; color:#555; padding-left:15px; vertical-align:bottom;">
            <p>Tarih: {{ $arsiv->created_at ? date('d.m.Y', strtotime($arsiv->created_at)) : '' }}</p>
            <p>Durum:
               @if($arsiv->durum == 1) Onaylandi
               @elseif($arsiv->durum == 0) Iptal Edildi
               @else Beklemede
               @endif
            </p>
         </td>
      </tr>
   </table>
</div>

</body>
</html>
