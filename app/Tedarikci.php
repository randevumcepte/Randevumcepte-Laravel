<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tedarikci extends Model
{
    protected $table = 'tedarikciler';

    protected $fillable = [
        'salon_id', 'ad', 'telefon', 'vergi_no', 'email', 'adres', 'aciklama', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function urunler()
    {
        return $this->hasMany(Urunler::class, 'tedarikci_id');
    }
}
