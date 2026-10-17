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
      // Gelmedi olarak işaretli mi? — randevuya_geldi===0 (strict: null != 0)
      $__zatenGelmedi = (
         isset($randevu->randevu->randevuya_geldi)
         && $randevu->randevu->randevuya_geldi === 0
      );
   @endphp
   <div class="rdb-row">
      @if($__zatenGelmedi)
         {{-- Zaten gelmedi -> Beklemede butonu (gelmedi işaretini kaldırır) --}}
         <a name="gelmedi_isareti_kaldir" href="#" class="btn btn-warning" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-undo"></i> Beklemede</a>
      @else
         <a name="gelmedi_isaretle" href="#" class="btn btn-danger" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-times"></i> Gelmedi</a>
      @endif

      @if($__zatenGeldi)
         {{-- Zaten geldi -> Beklemede butonu (geldi işaretini kaldırır; hem randevu hem seanstan) --}}
         <a name="geldi_isareti_kaldir" href="#" class="btn btn-warning" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-undo"></i> Beklemede</a>
      @else
         <a name="geldi_isaretle"   href="#" class="btn btn-success" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-check"></i> Geldi</a>
      @endif

      {{-- TELAFI (paket/seans): seansı telafi slotuna (APS.geldi=2) alır; bir sonraki
           randevuda o telafiye bağlanır (yeni seans düşülmez).
           SADECE gerçek paket/seans randevularinda (APS kaydı varsa) — tek seferlik
           hizmet satışı (adisyon_hizmet) olanlarda bu buton anlamsız. --}}
      @if(($hasAPS ?? false))
         <a name="telafi_isaretle" href="#" class="btn" style="background:#FDA172;border-color:#FDA172;color:#fff" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-exclamation-triangle"></i> Seansı Telafi İşaretle</a>
      @endif

      {{-- TELAFI RANDEVUSU OLUSTUR: ucretsiz duzeltme randevusu olustur, baska personel
           yapacaksa prim de devredilir. Sadece zaten telafi randevusu DEGIL ise + devir
           yapilmamissa gorunur. --}}
      @php
         // $randevu bu partial'da RandevuHizmetler instance ($rh)
         $__telafiButtonGoster = empty($randevu->telafi_kaynak_rh_id)
                               && empty($randevu->telafi_devir_yapildi);
      @endphp
      @if($__telafiButtonGoster)
         <a href="#" class="btn btn-warning telafi-randevu-ac"
            data-rh-id="{{ $randevu->id }}"
            data-sure="{{ $randevu->sure_dk ?: 15 }}"
            data-personel-id="{{ $randevu->personel_id }}"
            data-oda-id="{{ $randevu->oda_id }}"
            data-cihaz-id="{{ $randevu->cihaz_id }}"
            data-musteri="{{ $randevu->randevu->users->name ?? '' }}"
            data-hizmet="{{ $randevu->hizmetler->hizmet_adi ?? '' }}">
            <i class="fa fa-undo"></i> Telafi Randevusu
         </a>
      @elseif($randevu->telafi_kaynak_rh_id)
         <span class="badge" style="background:#FDA172;color:#fff;padding:6px 10px;align-self:center;"><i class="fa fa-undo"></i> Telafi Randevusu</span>
      @endif

      @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
         @if(!empty($adisyonId))
            {{-- Randevuya bagli (cozumlenebilen) adisyon var -> popup yerine ilgili adisyon (tahsilat) ekranina git --}}
            <a href="/isletmeyonetim/tahsilat/{{$randevu->randevu->user_id}}/{{$adisyonId}}?sube={{$randevu->randevu->salon_id}}" class="btn btn-primary"><i class="fa fa-money"></i> Tahsilat</a>
         @else
            {{-- Cozumlenebilen adisyon yok. hasPaketTahsilat sadece yetim/hayalet seans yuzunden true olabilir;
                 bu durumda paket gecmis-odeme popup'i alakasiz/bos olur. Normal tahsilat akisina dusur. --}}
            <a name="tahsil_et" href="#" class="btn btn-primary" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"><i class="fa fa-money"></i> Tahsilat</a>
         @endif
      @endif

      <button class="btn btn-danger randevuiptalet" data-value="{{$randevu->randevu_id}}" data-index-number="{{$randevu->hizmet_id}}"><i class="fa fa-times"></i> İptal Et</button>
   </div>
@endif
