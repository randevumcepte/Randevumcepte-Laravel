<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonHizmetler extends Model
{
   
    protected $fillable = ['salon_id','hizmet_id','hizmet_kategori_id','baslangic_fiyat','son_fiyat','bolum'];

    protected $table = 'salon_sunulan_hizmetler';
    
    protected $with =  ['salonlar','hizmetler','hizmet_kategorisi'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
     public function hizmetler()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
    public function hizmet_kategorisi(){
        return $this->belongsTo(Hizmet_Kategorisi::class, 'hizmet_kategori_id');
    }
    
    
}
