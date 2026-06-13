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
      @endif

      <a class="btn btn-danger rdb-pull-right" href="#" name="satis_yapilmadi" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-times"></i> Satış Yapılmadı</a>
   </div>
@elseif($randevu->randevu->durum == 0)
   <div class="rdb-row">
      <button data-value="{{$randevu->randevu_id}}" class="btn btn-success randevuonayla"><i class="fa fa-check"></i> Onayla</button>
      <button class="btn btn-danger randevuiptalet" data-value="{{$randevu->randevu_id}}"><i class="fa fa-times"></i> İptal Et</button>
   </div>
@else
   @php
      // Geldi olarak işaretli mi? — randevuya_geldi==1 VEYA bu hizmetin seansa_geldi==1
      $__zatenGeldi = (
         (isset($randevu->randevu->randevuya_geldi) && $randevu->randevu->randevuya_geldi === 1)
         || (isset($randevu->seansa_geldi) && $randevu->seansa_geldi === 1)
      );
   @endphp
   <div class="rdb-row">
      <a name="gelmedi_isaretle" href="#" class="btn btn-danger" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-times"></i> Gelmedi</a>

      @if($__zatenGeldi)
         {{-- Zaten geldi -> Beklemede butonu (geldi işaretini kaldırır; hem randevu hem seanstan) --}}
         <a name="geldi_isareti_kaldir" href="#" class="btn btn-warning" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-undo"></i> Beklemede</a>
      @else
         <a name="geldi_isaretle"   href="#" class="btn btn-success" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-check"></i> Geldi</a>
      @endif

      @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
         @if(!empty($hasPaketTahsilat) && !empty($adisyonId))
            {{-- Randevuya bagli adisyon var -> popup yerine ilgili adisyon (tahsilat) ekranina git --}}
            <a href="/isletmeyonetim/tahsilat/{{$randevu->randevu->user_id}}/{{$adisyonId}}?sube={{$randevu->randevu->salon_id}}" class="btn btn-primary"><i class="fa fa-money"></i> Tahsilat</a>
         @elseif(!empty($hasPaketTahsilat))
            <a name="paket_tahsilatlari" href="#" class="btn btn-primary" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-money"></i> Tahsilat</a>
         @else
            <a name="tahsil_et" href="#" class="btn btn-primary" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-money"></i> Tahsilat</a>
         @endif
      @endif

      <button class="btn btn-danger randevuiptalet" data-value="{{$randevu->randevu_id}}" data-index-number="{{$randevu->hizmet_id}}"><i class="fa fa-times"></i> İptal Et</button>
   </div>
@endif
