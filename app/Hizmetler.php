<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hizmetler extends Model
{
   
    protected $fillable = ['hizmet_kategori_id','hizmet_adi','fiyat'];

    protected $table = 'hizmetler';
    
    protected $with =  [ 'personeller'];

    public function hizmet_kategorisi()
    {
        return $this->belongsTo(Hizmet_Kategorisi::class, 'hizmet_kategori_id');
    }
    public function personelhizmetler(){
    	 return $this->belongsTo(PersonelHizmetler::class);
    }
    public function personeller(){
    	return $this->belongsTo(Personeller::class);
    }
    public function salon_sunulan_hizmetler(){
        return $this->hasMany(SalonHizmetler::class,'salon_id');
    }
}
