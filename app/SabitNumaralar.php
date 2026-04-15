<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SabitNumaralar extends Model
{
   
    protected $fillable = ['numara','salon_id'];

    protected $table = 'sabit_numaralar';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
}
