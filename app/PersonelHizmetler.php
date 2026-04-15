<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelHizmetler extends Model
{
   
    protected $fillable = ['personel_id','hizmet_id'];

    protected $table = 'personel_sunulan_hizmetler';
    
    protected $with =  ['personeller','hizmetler'];

    public function personeller()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
     public function hizmetler()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
 
    
}
