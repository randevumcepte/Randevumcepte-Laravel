<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OdaRenkleri extends Model
{
     
    protected $table = 'salon_oda_renkleri';
    protected $with =  ['salon','oda','renkduzeni'];
    
   
 
    public function salon(){
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function oda(){
        return $this->belongsTo(Odalar::class,'oda_id');
        
    }
    public function renkduzeni(){
        return $this->belongsTo(RenkDuzenleri::class,'renk_id');
    }
    
    
}
