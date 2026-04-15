<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdisyonHizmetler extends Model
{
   
    protected $fillable = ['adisyon_id','hizmet_id','sure','fiyat','islem_tarihi','islem_saati','indirim_tutari','hediye'];

    protected $table = 'adisyon_hizmetler';
    
    protected $with =  ['hizmet','personel','cihaz'];
    // protected $connection = 'mysql_source'; // Varsayılan olarak kaynak database
  
     public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
     public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
     public function cihaz()
    {
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
    public function seanslar()
    {
        return $this->hasMany(AdisyonPaketSeanslar::class,'adisyon_hizmet_id');
    }
    public function tahsilatlar()
    {
        return $this->hasMany(TahsilatHizmetler::class,'adisyon_hizmet_id');
        
    }
   

    /*public function setTargetConnection()
    {
        $this->setConnection('mysql_target');
    }*/
    
}
