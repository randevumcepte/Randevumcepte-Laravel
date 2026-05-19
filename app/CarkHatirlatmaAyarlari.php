<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkHatirlatmaAyarlari extends Model
{
    protected $table = 'cark_hatirlatma_ayarlari';

    protected $fillable = [
        'salon_id', 'aktif',
        'saat_1', 'saat_2', 'saat_3', 'saat_son',
        'mesaj_1', 'mesaj_2', 'mesaj_3', 'mesaj_son',
        'aktif_1', 'aktif_2', 'aktif_3', 'aktif_son',
        'gonderim_gunleri',
    ];

    protected $casts = [
        'aktif'             => 'integer',
        'aktif_1'           => 'integer',
        'aktif_2'           => 'integer',
        'aktif_3'           => 'integer',
        'aktif_son'         => 'integer',
        'gonderim_gunleri'  => 'array',
    ];
}
