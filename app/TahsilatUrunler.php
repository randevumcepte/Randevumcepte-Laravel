<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TahsilatUrunler extends Model
{
   
    protected $fillable = ['adisyon_urun_id','tutar','id','tahsilat_id'];

    protected $table = 'tahsilat_urunler';

    /* PERF: Global $with kaldirildi. ->with('adisyon_urun') gerektiginde acikca.
       Eski deger: ['adisyon_urun'] */

    public function adisyon_urun()
    {
        return $this->belongsTo(AdisyonUrunler::class,'adisyon_urun_id');    
    }

}
