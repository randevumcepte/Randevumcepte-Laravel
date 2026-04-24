<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Personeller extends Model
{
    
    protected $table = 'salon_personelleri';
    //protected $with = ['salonlar','randevuhizmetler'];
    protected $fillable = [
        'personel_adi', 'salon_id' ,'yetkili_id','cep_telefon',
        'uzmanlik','aciklama','yillik_tecrube','instagram'
    ];
   
    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function randevuhizmetler(){
    	return $this->belongsTo(RandevuHizmetler::class,'personel_id');
    }
    public function sube()
    {
        return $this->belongsTo(Subeler::class,'sube_id');
    }
    public function hesap_turu()
    {
        return $this->belongsTo(YetkiliHesapTurleri::class,'hesap_turu_id');
    }
    public function trenk()
    {
        return $this->belongsTo(RenkDuzenleri::class,'renk');
    }
    
   
   
    
}
