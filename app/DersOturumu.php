<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Grup dersi / kapasiteli ders oturumu. Takvime ayri event kaynagi olarak biner.
 * Katilimcilar 1:N (ders_katilimcilar). Kapasite oturum bazinda (degisken).
 */
class DersOturumu extends Model
{
    protected $table = 'ders_oturumlari';

    protected $fillable = [
        'salon_id', 'sube_id', 'personel_id', 'ders_tipi', 'tarih', 'saat',
        'saat_bitis', 'kapasite', 'sablon_id', 'iptal', 'aktif', 'renk', 'not',
        'olusturan_personel_id', 'hatirlatma_gonderildi',
    ];

    public function katilimcilar()
    {
        return $this->hasMany(DersKatilimci::class, 'oturum_id');
    }

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }

    // Kontenjani dolduran (iptal olmayan, bekleme olmayan) katilimcilar
    public function aktifKatilimcilar()
    {
        return $this->hasMany(DersKatilimci::class, 'oturum_id')
            ->whereNotIn('durum', ['iptal', 'bekleme']);
    }

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }
}
