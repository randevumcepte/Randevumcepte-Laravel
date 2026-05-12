<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StokHareketi extends Model
{
    protected $table = 'stok_hareketleri';

    protected $fillable = [
        'salon_id', 'urun_id', 'depo_id', 'miktar',
        'hareket_tipi', 'referans_tip', 'referans_id', 'batch_uuid',
        'birim_alis_fiyati', 'birim_satis_fiyati',
        'aciklama', 'kullanici_id', 'kullanici_tipi', 'tarih',
    ];

    protected $casts = [
        'miktar'             => 'decimal:3',
        'birim_alis_fiyati'  => 'decimal:2',
        'birim_satis_fiyati' => 'decimal:2',
        'tarih'              => 'datetime',
    ];

    public const TIPLER = [
        'alis', 'satis', 'sarf', 'fire', 'sayim',
        'transfer_giris', 'transfer_cikis', 'iade', 'acilis', 'manuel',
    ];

    public function urun()
    {
        return $this->belongsTo(Urunler::class, 'urun_id');
    }

    public function depo()
    {
        return $this->belongsTo(Depo::class, 'depo_id');
    }

    public function salon()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }
}
