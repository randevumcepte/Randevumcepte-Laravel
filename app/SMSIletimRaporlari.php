<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSIletimRaporlari extends Model
{
   
    protected $fillable = ['salon_id','rapor_id','tur','aciklama','adet','kredi','durum'];

    protected $table = 'sms_iletim_raporlari';

    protected $with = ['salonlar'];

       public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    
    

   
   

    
}