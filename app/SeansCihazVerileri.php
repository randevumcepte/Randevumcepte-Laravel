<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeansCihazVerileri extends Model
{
    protected $table = 'seans_cihaz_verileri';

    protected $fillable = [
        'seans_id', 'salon_id', 'personel_id',
        'uygulama_bolgesi', 'enerji', 'hiz', 'ms', 'atis_sayisi',
        'tarih', 'notlar',
    ];

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }

    public function seans()
    {
        return $this->belongsTo(AdisyonPaketSeanslar::class, 'seans_id');
    }
}
