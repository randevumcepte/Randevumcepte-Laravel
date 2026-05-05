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
