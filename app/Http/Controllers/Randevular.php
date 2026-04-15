<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Randevular extends Model
{
     const
        BEKLENIYOR = 0,
        ONAYLANDI = 1,
        IPTAL_EDILDI = 2,
        KABUL_EDILMEDI = 3
        
    ;
    protected $table = 'randevular';
    protected $with =  ['users','salonlar','randevuhizmetler','sube'];
    protected $fillable = [
        'user_id', 'salon_id' ,'tarih' ,'saat'
    ];
   
      public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function users(){
    	return $this->belongsTo(User::class,'user_id');
    }
    public function randevuhizmetler(){
    	return $this->hasMany(RandevuHizmetler::class,'randevu_id','hizmet_id','personel_id');
    }
    public function sube(){
       return $this->belongsTo(Subeler::class,'sube_id');
    }
    public function islem_yapan_personel(){
        return $this->belongsTo(Personeller::class 'islem_yapan_personel_id');
    }
    

   
   
    
}
