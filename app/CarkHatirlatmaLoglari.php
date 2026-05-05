<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkHatirlatmaLoglari extends Model
{
    protected $table = 'cark_hatirlatma_loglari';

    protected $fillable = [
        'salon_id', 'user_id', 'asama', 'tarih',
        'gonderim_tarihi', 'durum',
    ];

    protected $casts = [
        'asama'           => 'integer',
        'tarih'           => 'date',
        'gonderim_tarihi' => 'datetime',
    ];
}
