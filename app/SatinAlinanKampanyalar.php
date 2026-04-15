<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SatinAlinanKampanyalar extends Model
{
    
    protected $table = 'kampanya_satin_alinan';
    protected $with = ['salonlar','users','kampanyalar'];
    protected $fillable = [
        'user_id', 'salon_id' 
    ];
   
      public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function kampanyalar(){
        return $this->belongsTo(SalonKampanyalar::class,'kampanya_id');
    }
    public function users(){
    	return $this->belongsTo(User::class,'user_id');
    }

   
   
    
}
