<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KampanyaKatilimcilari extends Model
{
   
    

    protected $table = 'kampanya_katilimcilari';
    protected $with = ['musteri'];
    
    public function musteri()
    {
         return $this->belongsTo(User::class,'user_id');
    } 

    
}