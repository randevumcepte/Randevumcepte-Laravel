<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KampanyaYapilanOdemeler extends Model
{
   
    protected $fillable = ['kampanya_id','tutar'];

    protected $table = 'kampanya_yapilan_odemeler';
    
    protected $with =  ['kampanyalar'];

    public function kampanyalar()
    {
        return $this->belongsTo(SalonKampanyalar::class,'kampanya_id');
    }
    
}
