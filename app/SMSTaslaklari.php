<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSTaslaklari extends Model
{
   
    protected $fillable = ['salon_id','taslak_icerik','baslik'];

    protected $table = 'sms_taslaklari';
    
    protected $with =  ['salonlar'];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class,'salon_id');
    }
 

    
}