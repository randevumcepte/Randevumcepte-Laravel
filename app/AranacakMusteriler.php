<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AranacakMusteriler extends Model
{
   
    

    protected $table = 'aranacak_musteriler';
    protected $with = ['musteri'];
    
    public function musteri()
    {
         return $this->belongsTo(User::class,'user_id');
    } 
  
    
}