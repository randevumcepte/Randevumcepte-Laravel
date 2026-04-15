<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaksitVadeleri extends Model
{
   
    

    protected $table = 'taksit_vadeleri';
    protected $with = ['odeme_turu'];
    public function odeme_turu()
    {
        return $this->belongsTo(OdemeYontemleri::class,'odeme_yontemi_id');
    }
    
   


    
}