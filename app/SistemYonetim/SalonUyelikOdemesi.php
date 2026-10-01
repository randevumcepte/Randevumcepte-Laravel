<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

/**
 * Salon uyelik (lisans) uzatma / tahsilat kaydi — bkz. migration
 * create_salon_uyelik_odemeleri. Her paketli uzatmada bir satir.
 */
class SalonUyelikOdemesi extends Model
{
    protected $table = 'salon_uyelik_odemeleri';

    protected $fillable = [
        'salon_id', 'paket', 'periyot', 'adet', 'hediye_ay', 'toplam_ay',
        'ucret', 'eski_tarih', 'yeni_tarih', 'yapan_id', 'yapan_adi', 'aciklama',
    ];
}
