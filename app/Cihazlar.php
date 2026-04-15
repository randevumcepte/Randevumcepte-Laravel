<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cihazlar extends Model
{
    
    protected $table = 'cihazlar';
    
    protected $with = ['salonlar'];
    protected $fillable = [
        'cihaz_adi',
        'aciklama',
        'durum'
    ];
    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    

   
   
    
}
