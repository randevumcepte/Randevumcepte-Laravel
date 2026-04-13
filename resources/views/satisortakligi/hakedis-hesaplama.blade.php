<?php 
   $toplam = 0; 
   $talep_edilmis_toplam = 0;

   use Carbon\Carbon; 

   $datetime = Carbon::now('Europe/Istanbul'); 
?>

@foreach($musteri_bilgileri as $guncel_musteri)
    @foreach($guncel_musteri->hizmetler as $basarili_satis_hizmetler)
        <?php 
            $kdv_orani = 20;
            $kdv_dahil_tutar = $basarili_satis_hizmetler->ucret;
            $kdv_haric_tutar = $kdv_dahil_tutar / (1 + $kdv_orani / 100); 
        ?>
        @if(!$guncel_musteri->devam_eden_odeme)
            @if($guncel_musteri->satis_ortagi_hakedis_odeme_durumu_id == 3)
                @if($guncel_musteri->musteri_temsilcisi_id != null)
                    <?php $toplam += ($kdv_haric_tutar / 5); ?>
                @else
                    <?php $toplam += ($kdv_haric_tutar / 2.5); ?>
                @endif
            @else
                @if(!is_numeric($guncel_musteri->musteri_temsilcisi_id))
                    <?php $talep_edilmis_toplam += ($kdv_haric_tutar / 5); ?>
                @else
                    <?php $talep_edilmis_toplam += ($kdv_haric_tutar / 2.5); ?>
                @endif
            @endif
        @else
            @if($guncel_musteri->satis_ortagi_hakedis_odeme_durumu_id == 3)
                @if(!is_numeric($guncel_musteri->musteri_temsilcisi_id))
                    <?php $toplam += ($kdv_haric_tutar / 6.66666667); ?>
                @endif
            @else
                @if(!is_numeric($guncel_musteri->musteri_temsilcisi_id))
                    <?php $talep_edilmis_toplam += ($kdv_haric_tutar / 6.66666667); ?>
                @endif
            @endif
        @endif
    @endforeach
@endforeach

<!-- Değerleri ekrana yazdır -->
@php
    echo json_encode(['toplam' => $toplam, 'talep_edilmis_toplam' => $talep_edilmis_toplam]);
@endphp