<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** Kullanilan ucretsiz damga karti seansi: kalem indirimi + 0 TL tahsilat baglantisi. Bkz DamgaKartiServisi. */
class DamgaKartiKullanimlari extends Model
{
    protected $table = 'damga_karti_kullanimlari';

    protected $fillable = [
        'salon_id', 'kural_id', 'user_id', 'adisyon_hizmet_id', 'randevu_id',
        'tahsilat_id', 'indirim_tutari', 'olusturan_id',
    ];
}
