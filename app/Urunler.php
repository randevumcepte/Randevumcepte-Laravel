<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Urunler extends Model
{
    
    protected $table = 'urunler';
    
    protected $with = ['salonlar'];
    protected $fillable = [
        'urun_adi', 'fiyat','barkod' ,'stok_adedi'
    ];
    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    

   
   
    
}
