<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaketHizmetler extends Model
{
     
    protected $table = 'paket_hizmetler';
    protected $with =  ['hizmet' ];
     protected $fillable = [
         'hizmet_id' ,'seans','fiyat'
    ];
    
    public function hizmet(){
    	return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
    
    
}
