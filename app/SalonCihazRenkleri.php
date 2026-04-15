<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonCihazRenkleri extends Model
{
     
    protected $table = 'salon_cihaz_renkleri';
    protected $with =  ['salon','cihaz','renkduzeni'];
    
   
 
    public function salon(){
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function cihaz(){
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
        
    }
    public function renkduzeni(){
        return $this->belongsTo(RenkDuzenleri::class,'renk_id');
    }
    
    
}
