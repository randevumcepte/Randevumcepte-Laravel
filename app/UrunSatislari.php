<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrunSatislari extends Model
{
    
    protected $table = 'urun_satislari';
    
    protected $with = ['randevular','personeller','users','urunler'];
    public function randevular(){
        return $this->belongsTo(Randevular::class,'randevu_id');
    }
    public function personeller(){
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function urunler()
    {
        return $this->belongsTo(Urunler::class,'urun_id');
    }

   
   
    
}
