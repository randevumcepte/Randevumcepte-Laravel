<style>
   .rdb-row { display:flex; gap:8px; flex-wrap:wrap; width:100%; }
   .rdb-row .btn {
      flex: 1 1 130px; min-width: 0;
      border-radius: 8px; font-weight: 600; font-size: 13px;
      padding: 9px 12px; line-height: 1.2;
      white-space: normal;
   }
   .rdb-row .btn i { margin-right: 4px; }
</style>

@php
   // Yeni serbest yazi gorusme_konusu mu, eski paket/urun/hizmet mi belli olsun
   $_hasGK = false;
   if ($randevu->randevu->ongorusme && \Schema::hasColumn('on_gorusmeler','gorusme_konusu')) {
      $_hasGK = !empty($randevu->randevu->ongorusme->gorusme_konusu);
   }
@endphp

@if($randevu->randevu->on_gorusme_id !== null)
   <div class="rdb-row">
      <a name="gelmedi_isaretle" href="#" class="btn btn-danger" data-value="{{$randevu->randevu_id}}"><i class="fa fa-times"></i> Gelmedi</a>
      <a name="geldi_isaretle"   href="#" class="btn btn-success" data-value="{{$randevu->randevu_id}}"><i class="fa fa-check"></i> Geldi</a>

      @if($randevu->randevu->ongorusme && $randevu->randevu->ongorusme->paket_id !== null)
         <a name="satis_yapildi" href="#" class="btn btn-success" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
      @elseif($randevu->randevu->ongorusme && $randevu->randevu->ongorusme->hizmet_id !== null)
         <a name="hizmet_satis_yapildi" href="#" class="btn btn-success" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
      @elseif($randevu->randevu->ongorusme && $randevu->randevu->ongorusme->urun_id !== null)
         <a name="urun_satis_yapildi" href="#" class="btn btn-success" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
      @elseif($_hasGK)
         {{-- Free-text gorusme_konusu icin generic satis butonu --}}
         <a name="satis_yapildi" href="#" class="btn btn-success" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
      @endif

      <a class="btn btn-danger" href="#" name="satis_yapilmadi" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-times"></i> Satış Yapılmadı</a>
   </div>
@elseif($randevu->randevu->durum == 0)
   <div class="rdb-row">
      <button data-value="{{$randevu->randevu_id}}" class="btn btn-success randevuonayla"><i class="fa fa-check"></i> Onayla</button>
      <button class="btn btn-danger randevuiptalet" data-value="{{$randevu->randevu_id}}"><i class="fa fa-times"></i> İptal Et</button>
   </div>
@else
   <div class="rdb-row">
      <a name="gelmedi_isaretle" href="#" class="btn btn-danger" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-times"></i> Gelmedi</a>
      <a name="geldi_isaretle"   href="#" class="btn btn-success" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-check"></i> Geldi</a>

      @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
         @if(\App\AdisyonPaketSeanslar::where('randevu_id',$randevu->randevu_id)->count()>0 || \App\AdisyonHizmetler::where('randevu_id',$randevu->randevu_id)->count()>0)
            <a name="paket_tahsilatlari" href="#" class="btn btn-primary" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-money"></i> Tahsilat</a>
         @else
            <a name="tahsil_et" href="#" class="btn btn-primary" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-money"></i> Tahsilat</a>
         @endif
      @endif

      <button class="btn btn-danger randevuiptalet" data-value="{{$randevu->randevu_id}}" data-index-number="{{$randevu->hizmet_id}}"><i class="fa fa-times"></i> İptal Et</button>
   </div>
@endif
