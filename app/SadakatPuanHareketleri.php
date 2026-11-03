<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Kazanilan sadakat puani defteri — kaynak (tahsilat/randevu) basina tek satir.
 */
class SadakatPuanHareketleri extends Model
{
    protected $table = 'sadakat_puan_hareketleri';

    protected $fillable = ['salon_id', 'user_id', 'kaynak', 'kaynak_id', 'puan'];
}
