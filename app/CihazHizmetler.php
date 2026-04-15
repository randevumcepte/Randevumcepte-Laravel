<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CihazHizmetler extends Model
{
   
    protected $fillable = ['cihaz','hizmet_id'];

    protected $table = 'cihaz_sunulan_hizmetler';
    
    protected $with =  ['cihaz','hizmet'];

    public function cihaz()
    {
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
     public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
 
    
}
