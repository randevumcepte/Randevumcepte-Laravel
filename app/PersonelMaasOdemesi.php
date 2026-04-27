<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonelMaasOdemesi extends Model
{
    protected $table = 'personel_maas_odemeleri';

    protected $fillable = [
        'personel_id', 'salon_id', 'donem', 'tutar',
        'odeme_tarihi', 'odeme_yontemi', 'aciklama', 'ekleyen_yetkili_id',
    ];

    protected $dates = ['odeme_tarihi'];

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }
}
