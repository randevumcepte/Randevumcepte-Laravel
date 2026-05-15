<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelYetkiAyari extends Model
{
    protected $table = 'personel_yetki_ayarlari';

    protected $fillable = [
        'personel_id',
        'salon_id',
        'sablon',
        'ayarlar',
    ];

    protected $casts = [
        'ayarlar' => 'array',
    ];
}
