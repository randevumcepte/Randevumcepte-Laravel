<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IlacTakibi extends Model
{
   
    

    protected $table = 'ilac_takibi';
    protected $with = ['salon'];
    
    public function musteri()
    {
         return $this->belongsTo(User::class,'user_id');
    } 
    public function salon()
    {
         return $this->belongsTo(Salonlar::class,'salon_id');
    } 


    
}