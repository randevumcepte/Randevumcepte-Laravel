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

<div class="container-fluid mt--6">

   <div class="row">

   



            <div class="col-lg-2">

               <div class="card bg-gradient-info border-0">

                  <!-- Card body -->

                  <a href="/satisortakligi/aktif-musteriler">

                     <div class="card-body">

                        <div class="row">

                           <div class="col">

                              <h6 class="card-title text-uppercase text-muted mb-0 text-white">

                                 <!--<img src="{{asset ('public/satisortakligipanel/assets/img/brand/google-ads-logo-dark.png')}}" style="width: 100%; height: auto" >-->

                                 AKTİF MÜŞTERİLER

                              </h6>

                              <span class="h2 font-weight-bold mb-0 text-white" style="font-size: 20px">{{$aktif_musteriler}} </span>

                           </div>

                        

                        </div>

                     </div>

                  </a>

               </div>

            </div>
                       <div class="col-lg-2">

               <div class="card bg-gradient-danger border-0">

                  <!-- Card body -->

                  <a href="/satisortakligi/pasif-musteriler">

                     <div class="card-body">

                        <div class="row">

                           <div class="col">

                              <h6 class="card-title text-uppercase text-muted mb-0 text-white">

                                 PASİF MÜŞTERİLER

                              </h6>

                              <span class="h2 font-weight-bold mb-0 text-white" style="font-size: 20px">{{$pasif_musteriler}}</span>

                           </div>

                    

                        </div>

                     </div>

                  </a>

               </div>

            </div>

            <div class="col-lg-2">

               <div class="card bg-gradient-danger border-0">

                  <!-- Card body -->

                  <a href="/satisortakligi/pasif-musteriler">

                     <div class="card-body">

                        <div class="row">

                           <div class="col">

                              <h6 class="card-title text-uppercase text-muted mb-0 text-white">

                                 DEMOSU OLAN MÜŞTERİLER

                              </h6>

                              <span class="h2 font-weight-bold mb-0 text-white" style="font-size: 20px">{{$pasif_musteriler}}</span>

                           </div>

                       

                        </div>

                     </div>

                  </a>

               </div>

            </div>
              <div class="col-lg-2">

               <div class="card bg-gradient-danger border-0">

                  <!-- Card body -->

                  <a href="/satisortakligi/pasif-musteriler">

                     <div class="card-body">

                        <div class="row">

                           <div class="col">

                              <h6 class="card-title text-uppercase text-muted mb-0 text-white">

                                 SATIŞ YAPILMAMIŞ MÜŞTERİLER

                              </h6>

                              <span class="h2 font-weight-bold mb-0 text-white" style="font-size: 20px">{{$pasif_musteriler}}</span>

                           </div>


                        </div>

                     </div>

                  </a>

               </div>

            </div>


           <div class="col-lg-2">

               <div class="card bg-gradient-default border-0">

                  <!-- Card body -->

                  <a href="/satisortakligi/gecmis-odemeler">

                     <div class="card-body">

                        <div class="row">

                           <div class="col">

                              <h6 class="card-title text-uppercase text-muted mb-0 text-white">

                                 <!--<img src="{{asset ('public/satisortakligipanel/assets/img/brand/google-ads-logo-dark.png')}}" style="width: 100%; height: auto" >-->

                                 GEÇMİŞ ÖDEMELER

                              </h6>

                              <span class="h2 font-weight-bold mb-0 text-white" style="font-size: 20px">

                              {{$gecmis_odemeler}} ₺

                              </span>

                           </div>

                        </div>

                     </div>

                  </a>

               </div>

            </div>

            <div class="col-lg-2">

               <div class="card bg-gradient-warning border-0">

                  <!-- Card body -->

                  <a href="/satisortakligi/odeme-talepleri">

                     <div class="card-body">

                        <div class="row">

                           <div class="col">

                              <h6 class="card-title text-uppercase text-muted mb-0 text-white">

                                 <!--<img src="{{asset ('public/satisortakligipanel/assets/img/brand/google-ads-logo-dark.png')}}" style="width: 100%; height: auto" >-->

                                 TALEP EDİLEN HAKEDİŞ

                              </h6>

                              <span class="h2 font-weight-bold mb-0 text-white" style="font-size: 20px">

                              {{$talep_edilen_hakedis}} ₺

                              </span>

                           </div>


                        </div>

                     </div>

                  </a>

               </div>

            </div>


   

   </div>

   <div class="col-xl-12 order-xl-3">

      <div class="card">

         <!-- Card header -->

         <div class="card-header border-0">

            <h3 class="mb-0">Güncel Aktif Müşteriler

               @if($toplam == 0)

               <button type="button" disabled id="odeme_talep_et_button" style="float: right;" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="ni ni-money-coins"></i>Ödeme Talep Et</button>

               @else

               <button type="button"  id="odeme_talep_et_button" style="float: right;" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="ni ni-money-coins"></i>Ödeme Talep Et</button>

               @endif

            </h3>

         </div>

         <div class="table-responsive">

            <table class="table align-items-center table-flush">

               <thead class="thead-light">

                  <tr>

                     <th>#</th>

                     <th>Firma Unvanı</th>

                     <th>Firma Yetkilisi</th>

                     <th>Üyelik Türü / Satılan Paketler</th> 

                     <th>Hakediş</th>

                  </tr>

               </thead>

               <tbody class="list">

                  @foreach($musteriler as $musteri_form)

                  @if($musteri_form->salon->satis_ortagi_id == Auth::user()->id)

                  <tr>

                     <td>{{$musteri_form->id}}</td>

                     <td><a href="/satisortakligi/firma-detay-dokum/{{$musteri_form->musteri->id}}">{{$musteri_form->musteri->firma_unvani}}</a></td>

                     <td>{{\App\Personeller::where('salon_id',$musteri_form->salon_id)->where('role_id',1)->value('personel_adi')}}</td>

                     <td>

                     

                     </td>

                     <td>

                       

                     </td>

                     @if($musteri_form->bayi_hakedis_odeme_durumu_id == 1)

                     <td class="bg-warning text-white">

                        @endif

                        @if($musteri_form->bayi_hakedis_odeme_durumu_id == 3)

                     <td class="bg-success text-white">

                        @endif

                        <?php $hizmet_hakedis = 0;?>

                        @foreach(\App\Musteri_Formlari_Hizmetler::where('form_id',$musteri_form->id)->get() as $hizmetler)

                        <?php $hizmet_hakedis += $hizmetler->ucret;?>

                        @endforeach

                        @if($musteri_form->devam_eden_odeme)

                        <?php  echo $hizmet_hakedis/20; ?> ₺

                        @else

                        <?php  echo $hizmet_hakedis/10; ?> ₺

                        @endif

                     </td>

                  </tr>

                  @endif

                  @endforeach()

                  <tr>

                     <td colspan=4f class="text-right" style="font-size:20px;font-weight: bold">TOPLAM : </td>

                     <td>

                        {{$toplam+$talep_edilen_toplam}} ₺

                     </td>

                  </tr>

                  @if(!$musteriler)

                  <tr>

                     <td colspan="5" style="color:#ff0000; font-weight: bold; text-align: center;">

                        Henüz satışı gerçekleşmiş müşteriniz bulunmamaktadır

                     </td>

                  </tr>

                  @endif

                  </tr>

               </tbody>

            </table>

         </div>

      </div>

   </div>

   <div class="modal fade" id="modal-default" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true" style="display: none;">

      <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">

         <div class="modal-content">

            <div class="modal-header">

               <h6 class="modal-title" id="modal-title-default">ÖDEME TALEBİ</h6>

               <button type="button" class="close" data-dismiss="modal" aria-label="Close">

               <span aria-hidden="true">×</span>

               </button>

            </div>

            <form id="odeme_talep_et_formu" enctype='multipart/form-data'  method="POST">

               @csrf

               <div class="modal-body">

                  <div class="card card-pricing bg-success border-0 text-center mb-4" style="padding:0">

                     <div class="card-header bg-transparent" style="padding: 0">

                        <h4 class="text-uppercase ls-1 text-white py-3 mb-0" style="padding: 0">GÜNCEL HAKEDİŞ TUTARINIZ</h4>

                     </div>

                     <div class="card-body px-lg-7" style="padding: 0">

                        <input type="hidden" name="hakedis_miktari" id="hakedis_miktari">

                        <div class="display-2 text-white" id="hakedis_miktari_text"></div>

                     </div>

                  </div>

                  <div class="alert alert-info fade show" role="alert">

                     <span class="alert-icon"><i class="fa fa-info"></i></span>

                     <span class="alert-text"><strong>Bilgi Notu!</strong> Hakedişinizi hesabınıza transfer edebilmemiz için lütfen komisyon faturanızı veya gider pusulanızı ekleyiniz.</span>

                  </div>

                  <div class="form-group">

                     <label>Belge ekleyin</label>

                     <input type="file" id="komisyon_fatura_gider_pusulasi" required name="komisyon_fatura_gider_pusulasi" class="form-control">

                  </div>

               </div>

               <div class="modal-footer">

                  <button type="submit" class="btn btn-primary"><i class="ni ni-send"></i>Ödeme Talebi Gönder</button>

                  <button type="button" class="btn btn-danger  ml-auto" data-dismiss="modal"><i class="fa fa-times-circle"></i>Kapat</button>

               </div>

            </form>

         </div>

      </div>

   </div>

</div>

</div>

<div id="hata"></div>

@endsection