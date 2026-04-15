<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaksitliTahsilatlar extends Model
{
   
    

    protected $table = 'taksitli_tahsilatlar';
    
    protected $with =  [ 'musteri','olusturan', 'salon','adisyon','vadeler','urunler','hizmetler','paketler'];

    
    public function musteri()
    {
    	return $this->belongsTo(User::class,'user_id');
    }
    public function olusturan()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'olusturan_id');
    }
    
     
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
     public function adisyon()
    {
        return $this->belongsTo(Adisyonlar::class,'adisyon_id');
    }
     public function vadeler()
    {
        return $this->hasMany(TaksitVadeleri::class,'taksitli_tahsilat_id');
    }
    public function urunler()
    {
        return $this->hasMany(AdisyonUrunler::class,'taksitli_tahsilat_id');
    }
    public function hizmetler()
    {
        return $this->hasMany(AdisyonHizmetler::class,'taksitli_tahsilat_id');
    }
    public function paketler()
    {
        return $this->hasMany(AdisyonPaketler::class,'taksitli_tahsilat_id');
    }


    
}