<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaketSatislari extends Model
{
   
    

    protected $table = 'paket_satislari';
    
    protected $with =  ['paket','musteri','olusturan','satici'];

    public function paket()
    {
        return $this->belongsTo(Paketler::class,'paket_id');
    }
    public function musteri()
    {
    	return $this->belongsTo(User::class,'user_id');
    }
    public function olusturan()
    {
        return $this->belongsTo(IsletmeYetkilileri::class,'olusturan_id');
    }
    public function satici()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }


    
}