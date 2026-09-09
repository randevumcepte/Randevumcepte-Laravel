<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Ders oturumuna yazili musteri (grup dersi katilimcisi).
 */
class DersKatilimci extends Model
{
    protected $table = 'ders_katilimcilar';

    protected $fillable = [
        'oturum_id', 'user_id', 'salon_id', 'durum', 'adisyon_paket_id',
        'ekleyen_personel_id', 'not',
    ];

    public function oturum()
    {
        return $this->belongsTo(DersOturumu::class, 'oturum_id');
    }

    public function musteri()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
