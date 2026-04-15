<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Odemeler extends Model
{
   
  

    protected $table = 'odemeler';

    protected $with = ['users','isletmeyetkilileri'];

    public function yetkili()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'yetkili_id');
    }
    public function musteri(){
    	return $this->belongsTo(User::class,'user_id');
    }
    

    
}