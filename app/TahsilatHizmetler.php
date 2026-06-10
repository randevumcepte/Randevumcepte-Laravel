<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TahsilatHizmetler extends Model
{
   
    protected $fillable = ['adisyon_hizmet_id','tutar','id','tahsilat_id'];

    protected $table = 'tahsilat_hizmetler';

    /* PERF: Global $with kaldirildi (adisyon_hizmet -> AdisyonHizmetler agacini cekiyordu).
       ->with('adisyon_hizmet') gerektiginde acikca. Eski deger: ['adisyon_hizmet'] */

    public function adisyon_hizmet()
    {
        return $this->belongsTo(AdisyonHizmetler::class,'adisyon_hizmet_id');    
    }

}
