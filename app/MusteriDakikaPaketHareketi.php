<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MusteriDakikaPaketHareketi extends Model
{
    protected $table = 'musteri_dakika_paket_hareketleri';

    protected $fillable = [
        'musteri_dakika_paketi_id',
        'randevu_id',
        'randevu_hizmet_id',
        'dakika',
        'tur',
        'tarih',
        'aciklama',
        'olusturan_user_id',
        'olusturan_personel_id',
    ];

    protected $casts = [
        'tarih' => 'datetime',
    ];

    public function paket()
    {
        return $this->belongsTo(MusteriDakikaPaketi::class, 'musteri_dakika_paketi_id');
    }

    public function randevu()
    {
        return $this->belongsTo(Randevular::class, 'randevu_id');
    }

    public function randevuHizmet()
    {
        return $this->belongsTo(RandevuHizmetler::class, 'randevu_hizmet_id');
    }
}
