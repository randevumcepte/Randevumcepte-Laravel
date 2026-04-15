<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonSMSAyarlari extends Model
{
    
    protected $table = 'salon_sms_ayarlari';
    protected $fillable = ['musteri','personel','salon_id','ayar_id'];
    protected $with = ['salon','ayar'];

    
    public function salon()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function ayar()
    {
        return $this->belongsTo(SMSAyarlari::class,'ayar_id');
    }
    

   
   
    
}
