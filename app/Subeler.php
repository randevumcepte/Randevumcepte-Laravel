<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subeler extends Model
{
   
    

    protected $table = 'subeler';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
     
    
    
}
