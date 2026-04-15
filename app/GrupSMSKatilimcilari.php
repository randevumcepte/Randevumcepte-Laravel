<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GrupSMSKatilimcilari extends Model
{ 
    protected $table = 'grup_sms_katilimcilari';
    protected $with =  ['musteri']; 
    public function musteri(){
    	return $this->belongsTo(User::class,'user_id');
    } 
}
