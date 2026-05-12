<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Urunler extends Model
{
    protected $table = 'urunler';

    protected $with = ['salonlar'];

    protected $fillable = [
        'urun_adi', 'fiyat', 'barkod', 'stok_adedi', 'dusuk_stok_siniri',
        'kategori_id', 'tedarikci_id', 'alis_fiyati', 'birim', 'tip',
        'kritik_stok_siniri', 'resim_url', 'aciklama',
        'varsayilan_depo_id', 'sku', 'kdv_orani', 'salon_id', 'aktif',
    ];

    protected $casts = [
        'fiyat'              => 'decimal:2',
        'alis_fiyati'        => 'decimal:2',
        'stok_adedi'         => 'decimal:3',
        'dusuk_stok_siniri'  => 'decimal:3',
        'kritik_stok_siniri' => 'decimal:3',
        'kdv_orani'          => 'decimal:2',
        'aktif'              => 'boolean',
    ];

    public function salonlar()
    {
        return $this->belongsTo(Salonlar::class, 'salon_id');
    }

    public function kategori()
    {
        return $this->belongsTo(UrunKategorisi::class, 'kategori_id');
    }

    public function tedarikci()
    {
        return $this->belongsTo(Tedarikci::class, 'tedarikci_id');
    }

    public function varsayilanDepo()
    {
        return $this->belongsTo(Depo::class, 'varsayilan_depo_id');
    }

    public function depoStoklari()
    {
        return $this->hasMany(UrunDepoStoku::class, 'urun_id');
    }

    public function hareketler()
    {
        return $this->hasMany(StokHareketi::class, 'urun_id');
    }

    public function toplamStok()
    {
        return $this->depoStoklari()->sum('stok');
    }
}
