<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnketSablon extends Model
{
    protected $table = 'anket_sablonlari';

    protected $fillable = [
        'salon_id', 'ad', 'aciklama', 'sorular_json',
        'otomatik_gonder', 'gonder_saat_sonra',
        'aktif', 'varsayilan', 'sira',
        // Anket tamamlama odulu
        'odul_tipi', 'odul_kupon_indirim_tipi', 'odul_kupon_deger',
        'odul_kupon_gecerlilik_gun', 'odul_kupon_hizmet_id', 'odul_puan', 'odul_baslik',
    ];

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }

    public function gonderimler()
    {
        return $this->hasMany(AnketGonderim::class, 'sablon_id');
    }
}
