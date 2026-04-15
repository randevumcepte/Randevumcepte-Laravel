<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OnGorusmeler extends Model
{
    
    protected $table = 'on_gorusmeler';
    
    protected $with = ['musteri','personel','salon','hizmet','urun','paket'];
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
