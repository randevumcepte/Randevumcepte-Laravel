<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramaListesi extends Model
{
   
    

    protected $table = 'arama_listesi';
    protected $with = ['personel','salon','aranacaklar'];
    
    public function personel()
    {
         return $this->belongsTo(Personeller::class,'personel_id');
    } 
     public function salon()
    {
         return $this->belongsTo(Salonlar::class,'salon_id');
    } 
     public function aranacaklar()
    {
         return $this->hasMany(AranacakMusteriler::class,'arama_id');
    } 

    
}