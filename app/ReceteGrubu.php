<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceteGrubu extends Model
{
   
    

    protected $table = 'recete_grubu';

    protected $with = ['musteriler'];

   
    public function musteriler(){
        return $this->hasMany(ReceteGrubuHastalari::class,'grup_id');
    }



    
   
    
}
