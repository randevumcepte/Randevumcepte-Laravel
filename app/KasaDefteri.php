<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KasaDefteri extends Model
{
   
    protected $fillable = ['salon_id','gelir_gider','aciklama','tarih', 'miktar'];

    protected $table = 'kasa_defteri';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class);
    }
    
}