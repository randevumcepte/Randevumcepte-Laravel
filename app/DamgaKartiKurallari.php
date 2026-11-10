<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** Damga karti kurali (salon+hizmet): gereken_adet ucretli ziyarete ucretsiz_adet seans. Bkz DamgaKartiServisi. */
class DamgaKartiKurallari extends Model
{
    protected $table = 'damga_karti_kurallari';

    protected $fillable = ['salon_id', 'hizmet_id', 'gereken_adet', 'ucretsiz_adet', 'aktif', 'baslangic'];
}
