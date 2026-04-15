<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RandevuHareketleri extends Model
{
    
    protected $table = 'randevu_hareketleri';
    protected $with =  ['randevu'];
    
   
    
  
    public function randevu(){
        return $this->belongsTo(Randevular::class,'randevu_id');
    }
   
   
    
}
