<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ajanda extends Model
{
    
        
         
    
    protected $table = 'ajanda';
    protected $with =  ['salonlar','sube','personel'];
  
      public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
  
   
    public function sube(){
       return $this->belongsTo(Subeler::class,'sube_id');
    }
   
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'ajanda_olusturan');
    }
   
 

   
   
    
}
