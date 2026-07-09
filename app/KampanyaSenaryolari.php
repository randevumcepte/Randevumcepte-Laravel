<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Senaryo Sihirbazi ile olusturulan kampanya senaryolari.
 * salon_id NULL => sistem (hazir) senaryosu.
 */
class KampanyaSenaryolari extends Model
{
    protected $table = 'kampanya_senaryolari';

    protected $fillable = [
        'salon_id', 'ad', 'senaryo_tipi', 'gorev_turu', 'adimlar', 'aksiyonlar', 'aktif',
    ];

    protected $casts = [
        'adimlar'    => 'array',
        'aksiyonlar' => 'array',
        'aktif'      => 'boolean',
        'gorev_turu' => 'integer',
    ];

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }
}
