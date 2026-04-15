<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormTaslaklari extends Model
{
     
    protected $table = 'formtaslaklari';
    protected $with =  ['salonlar'];
    
   public function salonlar(){
		return $this->belongsTo(Salonlar::class,'salon_id');
	}
     public function arsiv(){
        return $this->hasMany(Arsiv::class,'form_id');
    }

     
    
    
}
