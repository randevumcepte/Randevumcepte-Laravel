<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EtkinlikKatilimcilari extends Model
{
     
    protected $table = 'etkinlik_katilimcilari';
    protected $with =  ['musteri'];
    
   
 
    public function musteri(){
    	return $this->belongsTo(User::class,'user_id');
    }
     
    
    
}
