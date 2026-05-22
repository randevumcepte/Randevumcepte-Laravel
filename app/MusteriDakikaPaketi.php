<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MusteriDakikaPaketi extends Model
{
    protected $table = 'musteri_dakika_paketleri';

    protected $fillable = [
        'salon_id',
        'musteri_portfoy_id',
        'hizmet_id',
        'toplam_dakika',
        'kalan_dakika',
        'satis_fiyati',
        'satis_tarihi',
        'bitis_tarihi',
        'durum',
        'notlar',
        'olusturan_user_id',
        'olusturan_personel_id',
    ];

    protected $casts = [
        'satis_tarihi' => 'date',
        'bitis_tarihi' => 'date',
        'satis_fiyati' => 'decimal:2',
    ];

    public function hizmet()
    {
        return $this->belongsTo(Hizmetler::class, 'hizmet_id');
    }

    public function musteri()
    {
        return $this->belongsTo(MusteriPortfoy::class, 'musteri_portfoy_id');
    }

    public function hareketler()
    {
        return $this->hasMany(MusteriDakikaPaketHareketi::class, 'musteri_dakika_paketi_id')
            ->orderBy('tarih', 'desc');
    }

    public function olusturanPersonel()
    {
        return $this->belongsTo(Personeller::class, 'olusturan_personel_id');
    }
}
