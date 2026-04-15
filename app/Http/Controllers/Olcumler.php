<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Olcumler extends Model
{
    
    protected $table = 'olcumler';
    protected $with = ['olcumTuru','musteri','olcumGecmisi'];
      public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    }
   
    public function olcumTuru(){
        return $this->belongsTo(OlcumTuru::class,'olcum_id');
    }
    public function olcumGecmisi(){
        return $this->hasMany(OlcumGecmisi::class,'olcum_kaydi_id');
    }
    public function isletme()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
     
    
   
   
    
}
