<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrunKategorisi extends Model
{
    protected $table = 'urun_kategoriler';

    protected $fillable = [
        'salon_id', 'ad', 'ikon', 'renk', 'sira', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function urunler()
    {
        return $this->hasMany(Urunler::class, 'kategori_id');
    }
}
