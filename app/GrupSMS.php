<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GrupSMS extends Model
{
   
    protected $fillable = ['salon_id','grup_id'];

    protected $table = 'grup_sms';

    protected $with = ['salonlar','musteriler'];

       public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function musteriler(){
    	return $this->hasMany(GrupSMSKatilimcilari::class,'grup_id');
    }
    

    
}