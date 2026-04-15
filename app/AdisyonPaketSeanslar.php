<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdisyonPaketSeanslar extends Model
{
   
    

    protected $table = 'adisyon_paket_seanslar';

    protected $fillable = ['geldi'];
    
    protected $with =  ['personel','cihaz','oda','hizmet','randevu'];

  
    public function personel()
    {
        return $this->belongsTo(Personeller::class,'personel_id');
    }
    public function cihaz()
    {
        return $this->belongsTo(Cihazlar::class,'cihaz_id');
    }
     public function oda()
    {
        return $this->belongsTo(Odalar::class,'oda_id');
    }
    public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class,'hizmet_id');
    }
    public function randevu()
    {
        return $this->belongsTo(Randevular::class,'randevu_id');
    }
}
