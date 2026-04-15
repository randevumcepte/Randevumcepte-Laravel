<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramaTerimleri extends Model
{
   
    protected $fillable = ['salon_id','arama_terimi'];

    protected $table = 'arama_terimleri';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
}
