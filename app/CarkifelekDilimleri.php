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
        'kupon_mu', // KUPON MU ALANI EKLENDİ
        'sira',
        'created_at',
        'updated_at',
    ];

    public function cark()
    {
        return $this->belongsTo(CarkifelekSistemi::class, 'cark_id');
    }
}