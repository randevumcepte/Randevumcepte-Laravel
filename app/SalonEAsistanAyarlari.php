<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonEAsistanAyarlari extends Model
{
    
    protected $table = 'salon_esistan_ayarlari';
    protected $fillable = ['acik_kapali','salon_id','ayar_id'];
    protected $with = ['salon','ayar'];

    
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function ayar()
    {
        return $this->belongsTo(EAsistanAyarlari::class,'ayar_id');
    }
    

   
   
    
}
