
@if($randevu->randevu->on_gorusme_id !==null)
<div class="row">
    <div class="col-3 col-xs-3 col-sm-3"><a name="gelmedi_isaretle" href="#" class="btn btn-danger btn-block btn-lg" data-value="{{$randevu->randevu_id}}"> Gelmedi</a></div>
    <div class="col-3 col-xs-3 col-sm-3"><a name="geldi_isaretle" href="#" class="btn btn-success btn-block btn-lg" data-value="{{$randevu->randevu_id}}"> Geldi</a></div>
    
    <div class="col-3 col-xs-3 col-sm-3">

        @if(  $randevu->randevu->ongorusme && $randevu->randevu->ongorusme->paket_id !== null)
        <a class="btn btn-success btn-block btn-lg" href="#" name="satis_yapildi" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
        @endif
         @if($randevu->randevu->ongorusme &&$randevu->randevu->ongorusme->hizmet_id !== null )
        <a class="btn btn-success btn-block btn-lg" href="#" name="hizmet_satis_yapildi" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
        @endif
        @if($randevu->randevu->ongorusme &&$randevu->randevu->ongorusme->urun_id !== null)
        <a class="btn btn-success btn-block btn-lg" href="#" name="urun_satis_yapildi" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-plus"></i> Satış Yapıldı</a>
        @endif
    </div>
    <div class="col-3 col-xs-3 col-sm-3">
        <a class="btn btn-danger btn-block btn-lg" href="#" name="satis_yapilmadi" data-value="{{$randevu->on_gorusme_id}}"><i class="fa fa-times"></i> Satış Yapılmadı</a>,
    </div>
</div>
@elseif($randevu->randevu->durum==0)
<div class="row">
    <div class="col-6 col-xs-6 col-sm-6"><button data-value="{{$randevu->randevu_id}}" class="btn btn-success btn-block btn-lg randevuonayla" data-value="{{$randevu->randevu_id}}"> Onayla</a></button></div>
    <div class="col-6 col-xs-6 col-sm-6"><button class="btn btn-danger btn-block btn-lg randevuiptalet" data-value="{{$randevu->randevu_id}}"> İptal Et</button></div>
</div>
@else
<div class="row">
    <div class="col-3 col-xs-3 col-sm-3"><a name="gelmedi_isaretle" href="#" class="btn btn-danger btn-block btn-lg" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"> Gelmedi</a></div>
    <div class="col-3 col-xs-3 col-sm-3"><a name="geldi_isaretle" href="#" class="btn btn-success btn-block btn-lg" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"> Geldi</a></div>
     @if($_SERVER['HTTP_HOST'] != 'randevu.randevumcepte.com.tr')
    <div class="col-3 col-xs-3 col-sm-3">

        @if(\App\AdisyonPaketSeanslar::where('randevu_id',$randevu->randevu_id)->count()>0 || \App\AdisyonHizmetler::where('randevu_id',$randevu->randevu_id)->count()>0)
            <a name="paket_tahsilatlari" href="#" class="btn btn-primary btn-block btn-lg" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"> Tahsilat</a>
        @else
            <a name="tahsil_et" href="#" class="btn btn-primary btn-block btn-lg" data-index-number="{{$randevu->hizmet_id}}" data-value="{{$randevu->randevu_id}}"> Tahsilat</a>
        @endif
    </div>
    @endif
     <div class="col-3 col-xs-3 col-sm-3"><button class="btn btn-danger btn-block btn-lg randevuiptalet" data-value="{{$randevu->randevu_id}}" data-index-number="{{$randevu->hizmet_id}}"> İptal Et</button></div> 
</div>
@endif
                                    


