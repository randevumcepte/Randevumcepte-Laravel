<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dahililer extends Model
{
   
    protected $fillable = ['numara','salon_id'];

    protected $table = 'dahililer';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
}
