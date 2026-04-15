<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Tahsilatlar extends Model
{
    
    protected $table = 'tahsilatlar';
    
    protected $with = ['banka','urun_satisi','olusturan','satici','randevu','musteri','salon','adisyon','hizmet_odemeleri','urun_odemeleri','paket_odemeleri'];
    
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function musteri()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function randevu()
    {
        return $this->belongsTo(Randevular::class,'randevu_id');
    } 
      public function satici()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    } 
    public function olusturan()
    {
        return $this->belongsTo(Personeller::class,'olusturan_id');
    } 
    public function urun_satisi()
    {
        return $this->belongsTo(UrunSatislari::class,'urun_satis_id');
    } 
    public function odeme_yontemi()
    {
        return $this->belongsTo(OdemeYontemleri::class,'odeme_yontemi_id');
    }
    public function adisyon()
    {
        return $this->belongsTo(Adisyonlar::class,'adisyon_id');
    }
    public function hizmet_odemeleri()
    {
        return $this->hasMany(TahsilatHizmetler::class,'tahsilat_id');
    }
    public function urun_odemeleri()
    {
        return $this->hasMany(TahsilatUrunler::class,'tahsilat_id');
    }
    public function paket_odemeleri()
    {
        return $this->hasMany(TahsilatPaketler::class,'tahsilat_id');
    }
    public function banka()
    {
        return $this->belongsTo(SatisOrtakligiModel\Bankalar::class,'banka_id');
    }
   
   
    
}
