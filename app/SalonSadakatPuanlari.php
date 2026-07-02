<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Carkifelek sadakat puan bakiyesi — salon yildiz rating'inden (salon_puanlar)
 * ayri tutulur. Bir (salon_id, user_id) icin birikimli puan.
 */
class SalonSadakatPuanlari extends Model
{
    protected $fillable = ['salon_id', 'user_id', 'puan'];

    protected $table = 'salon_sadakat_puanlari';

    // Iliskiler lazy — eski SalonPuanlar'daki $with otomatik yuklemesi kaldirildi
    // (gereksiz eager load performansi dusuruyordu); erisen kod lazy cozer.
    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
