<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SenetVadeleri extends Model
{
   
    

    protected $table = 'senet_vadeleri';
    protected $with = ['odeme_turu'];
    public function odeme_turu()
    {
        return $this->belongsTo(OdemeYontemleri::class,'odeme_yontemi_id');
    }
    
   


    
}