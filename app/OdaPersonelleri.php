<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OdaPersonelleri extends Model
{
    
    protected $table = 'oda_personelleri';
    
    protected $with = ['salon','personel','oda'];
    
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function oda()
    {
        return $this->belongsTo(Odalar::class,'oda_id');
    }
    

   
   
    
}
