<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OnGorusmeler extends Model
{
    
    protected $table = 'on_gorusmeler';

    protected $with = ['musteri','personel','salon','hizmet','urun','paket'];

    // Takvim odaya gore ise (randevu_takvim_turu == 3) duzenleme ekranlarinda
    // secili oda onceden gelsin diye randevu_hizmetler uzerinden oda_id eklenir.
    protected $appends = ['oda_id'];

    public function getOdaIdAttribute()
    {
        $randevuId = Randevular::where('on_gorusme_id', $this->id)->value('id');
        if (!$randevuId) {
            return null;
        }
        return RandevuHizmetler::where('randevu_id', $randevuId)->value('oda_id');
    }

    public function musteri(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function personel(){
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
    public function urun()
    {
        return $this->belongsTo(Urunler::class,'urun_id');
    }
    public function paket()
    {
        return $this->belongsTo(Paketler::class,'paket_id');
    }

   
   
    
}
