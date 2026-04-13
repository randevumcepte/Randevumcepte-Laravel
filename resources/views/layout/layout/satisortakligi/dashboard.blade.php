@extends('layout.layout_satisortakligi')

@section('content')

<?php $toplam = 0; 

   $talep_edilen_toplam = 0;

   use Carbon\Carbon;

   $datetime = Carbon::now('Europe/Istanbul');

   

   ?>

@foreach($musteriler as $guncel_musteri)

@foreach(\App\Musteri_Formlari_Hizmetler::where('form_id',$guncel_musteri->id)->get() as $basarili_satis_hizmetler)

@if($guncel_musteri->devam_eden_odeme)

@if($guncel_musteri->bayi_hakedis_odeme_durumu_id == 3)

<?php $toplam+=($basarili_satis_hizmetler->ucret/20); ?>

@else

<?php $talep_edilen_toplam+=($basarili_satis_hizmetler->ucret/20); ?>

@endif

@else

@if($guncel_musteri->bayi_hakedis_odeme_durumu_id == 3)

<?php $toplam+=($basarili_satis_hizmetler->ucret/10); ?>

@else

<?php $talep_edilen_toplam+=($basarili_satis_hizmetler->ucret/10); ?>

@endif

@endif

@endforeach

@endforeach

<div class="header pb-6 d-flex align-items-center" style="min-height: 274px;">

   <span class="mask bg-gradient-default opacity-8"></span>

   <!-- Header container -->

   <div class="container-fluid d-flex align-items-center">

      <div class="row">

         <div class="col-lg-7 col-md-10">

            <h1 class="display-2 text-white">Satış Ortaklığı Paneli</h1>

            <p class="text-white mt-0 mb-5">Bu bölümde aktif ve pasif müşterilerinizi ve yapılan satışlardan hakedişlerinizi görebilirsiniz</p>

         </div>

      </div>

   </div>

</div>

<!-- Page content -->


</div>

<div id="hata"></div>

@endsection