<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarkifelekSistemi extends Model
{

    protected $table = 'carkifelek_sistemi';

    protected $fillable = [
        'aktifmi',
        'created_at',
        'updated_at',
        'salon_id',
    ];

    public function dilimler()
    {
        return $this->hasMany(CarkifelekDilimleri::class, 'cark_id');
    }
}
