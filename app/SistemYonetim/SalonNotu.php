<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class SalonNotu extends Model
{
    protected $table = 'sistemyonetim_salon_notlari';
    protected $fillable = [
        'salon_id', 'user_id', 'user_name',
        'baslik', 'icerik', 'tip', 'pinned',
    ];
}
