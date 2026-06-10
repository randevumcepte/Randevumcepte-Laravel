<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TahsilatPaketler extends Model
{
   
    protected $fillable = ['adisyon_paket_id','tutar','id','tahsilat_id'];

    protected $table = 'tahsilat_paketler';

    /* PERF: Global $with kaldirildi. ->with('adisyon_paket') gerektiginde acikca.
       Eski deger: ['adisyon_paket'] */

    public function adisyon_paket()
    {
        return $this->belongsTo(AdisyonPaketler::class,'adisyon_paket_id');    
    }

}
