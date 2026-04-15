<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Senetler extends Model
{
   
    

    protected $table = 'senetler';
    
    protected $with =  ['paketsatis','musteri','olusturan','randevu','salon','adisyon','vadeler','urunler','hizmetler','paketler'];

    public function paketsatis()
    {
        return $this->belongsTo(Paketler::class,'paket_satis_id');
    }
    public function musteri()
    {
    	return $this->belongsTo(User::class,'user_id');
    }
    public function olusturan()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'olusturan_id');
    }
    public function randevu(){
        return $this->belongsTo(Randevular::class,'randevu_id');
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
        return $this->hasMany(SenetVadeleri::class,'senet_id');
    }
     public function urunler()
    {
        return $this->hasMany(AdisyonUrunler::class,'senet_id');
    }
    public function hizmetler()
    {
        return $this->hasMany(AdisyonHizmetler::class,'senet_id');
    }
    public function paketler()
    {
        return $this->hasMany(AdisyonPaketler::class,'senet_id');
    }



    
}