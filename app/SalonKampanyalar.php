<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonKampanyalar extends Model
{
   
    protected $fillable = ['salon_id','kampanya_baslik','kampanya_aciklama','kampanya_fiyat'];

    protected $table = 'kampanyalar';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
    
}
