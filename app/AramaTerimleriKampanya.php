<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AramaTerimleriKampanya extends Model
{
   
    protected $fillable = ['salon_id','arama_terimi'];

    protected $table = 'arama_terimleri_kampanya';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
}
