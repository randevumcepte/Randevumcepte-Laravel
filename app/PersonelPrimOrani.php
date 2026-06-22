<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelPrimOrani extends Model
{
    protected $table = 'personel_prim_oranlari';

    protected $fillable = [
        'personel_id', 'salon_id', 'tur', 'kalem_id', 'yuzde',
    ];

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }
}
