<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RandevuHizmetler extends Model
{
    
    protected $table = 'randevu_hizmetler';
    //protected $with =  ['hizmetler' ,'personeller','cihaz'];
    
    protected $fillable = [
        'randevu_id', 'hizmet_id' ,'personel_id' ,'saat','saat_bitis','oda_id','cihaz_id','sure_dk','fiyat'
    ];
    public function randevu()
    {
        return $this->belongsTo(Randevular::class, 'randevu_id');
    }

    public function hizmetler()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
    
    public function personeller(){
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function cihaz(){
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
    public function oda()
    {
        return $this->belongsTo(Odalar::class,'oda_id');
    }
    
   
   
    
}
