<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Etkinlikler extends Model
{
   
    protected $table = 'etkinlikler';
    protected $with =  ['katilimcilar','salon'];
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function katilimcilar(){
         return $this->hasMany(EtkinlikKatilimcilari::class,'etkinlik_id');
   
      
    }

   
   
    
}
