<?php

namespace App\SistemYonetim;

use Illuminate\Database\Eloquent\Model;

class HazirCevap extends Model
{
    protected $table = 'sistemyonetim_hazir_cevaplar';
    protected $fillable = [
        'baslik', 'icerik', 'kategori', 'kisayol',
        'kullanim_sayisi', 'olusturan_user_id', 'olusturan_user_name', 'aktif',
    ];
}
