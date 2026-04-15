<?php

namespace App\SatisOrtakligiModel;


use Illuminate\Database\Eloquent\Model;


class Satis_Ortagi_Banka_Hesaplari extends Model
{
    
    protected $table = 'satis_ortagi_banka_hesaplari';
    protected $with = ['satis_ortagi','banka'];
     public function satis_ortagi(){
        return $this->belongsTo(SatisOrtaklari::class,'satis_ortagi_id');
    }
    public function banka(){
    	return $this->belongsTo(Bankalar::class,'banka_id');
    }
   
     
   
}
