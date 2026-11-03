<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Sadakat puani kazanim kurali (salon basina): her X TL harcamaya Y puan +
 * gelinen randevu basina Z puan. Islem: sadakat:puan-isle (SadakatPuanServisi).
 */
class SalonSadakatAyarlari extends Model
{
    protected $table = 'salon_sadakat_ayarlari';

    protected $fillable = ['salon_id', 'aktif', 'harcama_tutari', 'harcama_puani', 'ziyaret_puani', 'baslangic'];
}
