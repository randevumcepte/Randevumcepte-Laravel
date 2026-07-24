<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Belirli bir tarihe özel online randevu aç/kapa istisnası.
 * Haftalık pencere kuralını (SalonOnlineRandevuSaatleri) ezer.
 *   tip='kapali' -> o gün online tamamen kapalı
 *   tip='ozel'   -> o gün yalnız baslangic_saati..bitis_saati açık
 */
class SalonOnlineRandevuIstisnasi extends Model
{
    protected $table = 'salon_online_randevu_istisnalari';

    protected $fillable = ['salon_id', 'tarih', 'tip', 'baslangic_saati', 'bitis_saati'];
}
