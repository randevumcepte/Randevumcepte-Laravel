<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrunDepoStoku extends Model
{
    protected $table = 'urun_depo_stoklari';

    protected $fillable = [
        'salon_id', 'urun_id', 'depo_id', 'stok',
    ];

    protected $casts = [
        'stok' => 'decimal:3',
    ];

    public function urun()
    {
        return $this->belongsTo(Urunler::class, 'urun_id');
    }

    public function depo()
    {
        return $this->belongsTo(Depo::class, 'depo_id');
    }
}
