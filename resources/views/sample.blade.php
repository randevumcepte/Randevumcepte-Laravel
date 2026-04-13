<!DOCTYPE html>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta http-equiv="Content-language" content="tr">
      <title>{{ $title }}</title>
      <style type="text/css">
         h4{float:left; font-family:"DeJaVu Sans Mono",monospace; margin-left: 5px;}
         p{ font-family:"DeJaVu Sans Mono",monospace;font-size: 12px}
         .metin{width:740px; height:200px;word-wrap:break-word;}
         span{font-family:"DeJaVu Sans Mono",monospace;margin-left: 70px;font-size: 12px }
         }
      </style>
   </head>
   <body>


   	 
   		@foreach($senet->vadeler as $key=>$vade)
   		 <div style="width: 740px; margin: 17px; "><br>
   			
      		<h4>Ödeme Günü<br>{{date('d.m.Y',strtotime($vade->vade_tarih))}}</h4>
      		<?php  $tutar_conv = explode(',',number_format($vade->tutar,2,',','.')); ?>
      		<h4 style=" margin-left: 90px;">Türk Lirası<br>#{{$tutar_conv[0]}}#</h4>
          <h4 style=" margin-left: 90px;">Kuruş<br>#{{$tutar_conv[1]}}#</h4>
          <h4 style=" margin-left: 90px;">No.<br>{{++$key}}</h4>
      <br>
      <br>
      <br>
      <br>
      <br>
  		 </div>
     
         <p>
            İş bu emre yazılı senedimin mukabilinde {{date('d F Y',strtotime($vade->vade_tarih))}} tarihinde Sayın {{$senet->salon->firma_unvani}} veya emruhavalesine. Yukarıda yazılı yalnız #{{convert_number_to_words($tutar_conv[0])}}# Türk Lirası #{{convert_number_to_words($tutar_conv[1])}}# Kuruş kayıtsız şartsız ödeyeceğim Bedeli @if($senet->senet_turu == 1) Nakden @endif @if($senet->senet_turu == 2) Malen @endif @if($senet->senet_turu == 3) Hizmet bedeli olarak @endif ahzolunmuştur. İş bu emre yazılı senet vadesinde ödenmediği takdirde müteakip bonolarında muacceliyet kesbedeceğini, İhtilaf vukuunda {{$senet->salon->il->il_adi}} Mahkemelerinin selahiyetini şimdiden kabul eylerim.
         </p>
          <div class="metin" style="width: 740px;margin-bottom: 20px;">
         <div style="float:left;width:500px;">
             
            <p>Adı-Soyadı/Ünvan: {{$senet->musteri->name}}</p>
            <p>Adres: {{$senet->musteri->adres}}</p>
            <p>V.No/T.C No: {{$senet->musteri->tc_kimlik_no}}</p>
            <p>Kefil(Ad-Soyad): {{$senet->kefil_adi}}</p>
            <p>Kefil Adres: {{$senet->kefil_adres}}</p>
            <p>Kefil T.C No: {{$senet->kefil_tc_vergi_no}}</p>
         </div>
         <div style="	float: left; width:240px;">
            
            <p>Düzenleme Tarihi: {{date('d/m/Y',strtotime($senet->created_at))}}</p>
            <br>
            <span>İmza</span>
            <span>İmza</span>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br> 
         </div>
      </div>
   	  @endforeach
      
       
         
       
   </body>
   <?php
      function convert_number_to_words($number) {
         	if($number=='00')
         		$number='0';
          $hyphen      = '-';
          $conjunction = '  ';
          $separator   = ' ';
          $negative    = 'eksi ';
          $decimal     = ' nokta ';
          $dictionary  = array(
              0                   => 'Sıfır',
              1                   => 'Bir',
              2                   => 'İki',
              3                   => 'Üç',
              4                   => 'Dört',
              5                   => 'Beş',
              6                   => 'Altı',
              7                   => 'Yedi',
              8                   => 'Sekiz',
              9                   => 'Dokuz',
              10                  => 'On',
              11                  => 'OnBir',
              12                  => 'Onİki',
              13                  => 'OnÜç',
              14                  => 'OnDört',
              15                  => 'OnBeş',
              16                  => 'OnAltı',
              17                  => 'OnYedi',
              18                  => 'OnSekiz',
              19                  => 'OnDokuz',
              20                  => 'Yirmi',
              30                  => 'Otuz',
              40                  => 'Kırk',
              50                  => 'Elli',
              60                  => 'Atmış',
              70                  => 'Yetmiş',
              80                  => 'Seksen',
              90                  => 'Doksan',
              100                 => 'Yüz',
              1000                => 'Bin',
              1000000             => 'Milyon',
              1000000000          => 'Milyar',
              1000000000000       => 'Trilyon',
              1000000000000000    => 'Katrilyon',
              1000000000000000000 => 'Kentrilyon'
          );
         
          if (!is_numeric($number)) {
              return false;
          }
         
          if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
              // overflow
              trigger_error(
                  'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                  E_USER_WARNING
              );
              return false;
          }
      
          if ($number < 0) {
              return $negative . convert_number_to_words(abs($number));
          }
         
          $string = $fraction = null;
         
          if (strpos($number, '.') !== false) {
              list($number, $fraction) = explode('.', $number);
          }
         
          switch (true) {
              case $number < 21:
                  $string = $dictionary[$number];
                  break;
              case $number < 100:
                  $tens   = ((int) ($number / 10)) * 10;
                  $units  = $number % 10;
                  $string = $dictionary[$tens];
                  if ($units) {
                      $string .= $hyphen . $dictionary[$units];
                  }
                  break;
              case $number < 1000:
                  $hundreds  = $number / 100;
                  $remainder = $number % 100;
                  $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                  if ($remainder) {
                      $string .= $conjunction . convert_number_to_words($remainder);
                  }
                  break;
              default:
                  $baseUnit = pow(1000, floor(log($number, 1000)));
                  $numBaseUnits = (int) ($number / $baseUnit);
                  $remainder = $number % $baseUnit;
                  $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                  if ($remainder) {
                      $string .= $remainder < 100 ? $conjunction : $separator;
                      $string .= convert_number_to_words($remainder);
                  }
                  break;
          }
         
          if (null !== $fraction && is_numeric($fraction)) {
              $string .= $decimal;
              $words = array();
              foreach (str_split((string) $fraction) as $number) {
                  $words[] = $dictionary[$number];
              }
              $string .= implode(' ', $words);
          }
         
          return $string;
      }
      
       
      ?>
</html>