<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsDogrulamaKodlari extends Model
{
    protected $table = 'sms_dogrulama_kodlari';

    protected $fillable = [
        'telefon', 'kod', 'ip', 'amac', 'son_gecerlilik', 'dogrulandi',
    ];

    protected $casts = [
        'son_gecerlilik' => 'datetime',
        'dogrulandi'     => 'integer',
    ];
}
