<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alacaklar extends Model
{
   
    

    protected $table = 'alacaklar';
    
    protected $with =  ['paketsatis','musteri','olusturan','randevu','senet','urunsatisi','salon'];

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
    public function senet(){
        return $this->belongsTo(Senetler::class,'senet_id');
    }
    public function urunsatisi()
    {
        return $this->belongsTo(UrunSatislari::class,'urun_satis_id');
    }
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function adisyon()
    {
        return $this->belongsTo(Adisyonlar::class,'adisyon_id');
    }
    public function taksit()
    {
        return $this->belongsTo(TaksitliTahsilatlar::class);
    }


    
}