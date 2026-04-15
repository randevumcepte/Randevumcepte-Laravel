<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Masraflar extends Model
{
   
    

    protected $table = 'masraflar';
    
    protected $with =  ['masraf_kategorisi','harcayan','salon','odeme_yontemi'];

    public function masraf_kategorisi()
    {
        return $this->belongsTo(MasrafKategorisi::class,'masraf_kategori_id');
    }
    public function harcayan()
    {
    	return $this->belongsTo(Personeller::class,'harcayan_id');
    }
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function odeme_yontemi()
    {
        return $this->belongsTo(OdemeYontemleri::class,'odeme_yontemi_id');
    }
    


    
}