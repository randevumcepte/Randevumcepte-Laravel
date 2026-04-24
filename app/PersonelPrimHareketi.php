<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelPrimHareketi extends Model
{
    protected $table = 'personel_prim_hareketleri';

    protected $fillable = [
        'personel_id', 'salon_id', 'tarih', 'tip', 'tutar', 'aciklama', 'ekleyen_yetkili_id',
    ];

    protected $dates = ['tarih'];

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }
}
