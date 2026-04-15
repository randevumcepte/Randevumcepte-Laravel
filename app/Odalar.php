<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Odalar extends Model
{
    
    protected $table = 'odalar';
    
    protected $with = ['salonlar','personel'];
    protected $fillable = [
        'oda_adi',
        'durum',
        'aciklama'
    ];
    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    

   
   
    
}
