<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekDilimleri extends Model
{
    protected $table = 'carkifelek_dilimleri';

    protected $fillable = [
        'cark_id',
        'dilim_ismi',
        'dilim_olasilik',
        'renk_kodu',
        'tip',
        'deger',
        'kupon_mu',
        'sira',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'deger'         => 'float',
        'dilim_olasilik'=> 'integer',
        'kupon_mu'      => 'integer',
        'sira'          => 'integer',
    ];

    public function cark()
    {
        return $this->belongsTo(CarkifelekSistemi::class, 'cark_id');
    }
}