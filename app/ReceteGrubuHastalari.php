<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceteGrubuHastalari extends Model
{
   
    

    protected $table = 'recete_grubu_hastalar';
    protected $with = ['musteri'];
    public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    
   
    
}
