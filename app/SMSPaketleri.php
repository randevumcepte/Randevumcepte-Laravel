<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSPaketleri extends Model
{
   
    protected $fillable = ['sms_adet','ucret','class'];

    protected $table = 'sms_paketleri';

    // Tabloda created_at/updated_at kolonu yok (id, sms_adet, ucret, class)
    public $timestamps = false;
    
}
