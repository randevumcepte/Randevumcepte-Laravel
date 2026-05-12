<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Depo extends Model
{
    protected $table = 'depolar';

    protected $fillable = [
        'salon_id', 'depo_adi', 'aciklama', 'varsayilan', 'aktif',
    ];

    protected $casts = [
        'varsayilan' => 'boolean',
        'aktif'      => 'boolean',
    ];

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }

    public function stoklar()
    {
        return $this->hasMany(UrunDepoStoku::class, 'depo_id');
    }

    public function hareketler()
    {
        return $this->hasMany(StokHareketi::class, 'depo_id');
    }
}
