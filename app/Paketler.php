<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paketler extends Model
{
   
    

    protected $table = 'paketler';
    
    protected $with =  ['salon','hizmetler'];

    public function hizmetler()
    {
        return $this->hasMany(PaketHizmetler::class,'paket_id');
    }
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }

    
    
}