<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OdaHizmetler extends Model
{
    protected $table = 'oda_sunulan_hizmetler';

    protected $fillable = ['salon_id', 'oda_id', 'hizmet_id'];

    public function oda()
    {
        return $this->belongsTo(Odalar::class, 'oda_id');
    }

    public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class, 'hizmet_id');
    }
}
