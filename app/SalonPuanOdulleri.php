<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalonPuanOdulleri extends Model
{
    protected $table = 'salon_puan_odulleri';

    protected $fillable = [
        'salon_id', 'puan_esigi', 'baslik', 'aciklama',
        'tip', 'deger', 'aktif', 'sira',
    ];

    protected $casts = [
        'puan_esigi' => 'integer',
        'deger'      => 'float',
        'aktif'      => 'integer',
        'sira'       => 'integer',
    ];
}
