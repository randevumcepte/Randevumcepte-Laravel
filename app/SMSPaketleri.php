<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSPaketleri extends Model
{
   
    protected $fillable = ['sms_adet','ucret'];

    protected $table = 'sms_paketleri'; 
    
}
