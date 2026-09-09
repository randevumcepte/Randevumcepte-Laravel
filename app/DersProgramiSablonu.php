<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Haftalik sabit ders programi sablonu (kagit haftalik program karsiligi).
 * "Programi Yayinla" bu sablondan ileriye donuk ders_oturumlari uretir.
 */
class DersProgramiSablonu extends Model
{
    protected $table = 'ders_programi_sablonu';

    protected $fillable = [
        'salon_id', 'sube_id', 'personel_id', 'hafta_gunu', 'saat', 'saat_bitis',
        'ders_tipi', 'kapasite', 'renk', 'aktif',
    ];

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }
}
