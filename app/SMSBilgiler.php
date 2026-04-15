<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSBilgiler extends Model
{
   
    protected $fillable = ['salon_id','paket_id','baslik','kullanici_adi','sifre'];

    protected $table = 'sms_bilgiler';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
    public function sms_paketleri()
     {
        return $this->belongsTo(SMSPaketleri::class,'paket_id');
    }
    
}
