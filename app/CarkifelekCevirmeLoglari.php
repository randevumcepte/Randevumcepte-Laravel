<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekCevirmeLoglari extends Model
{
    protected $table = 'carkifelek_cevirme_loglari';

    protected $fillable = [
        'cark_id', 'salon_id', 'user_id', 'randevu_id',
        'dilim_id', 'tip', 'deger', 'dilim_ismi',
    ];

    protected $casts = [
        'deger' => 'float',
    ];
}
