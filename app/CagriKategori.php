<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Cagri Merkezi: gorusme sonucu icin ozellestirilebilir kategori/alt-kategori agaci.
 * ust_id NULL => ana kategori; ust_id dolu => o kategorinin alt kategorisi.
 */
class CagriKategori extends Model
{
    protected $table = 'cagri_kategorileri';

    protected $fillable = [
        'salon_id',
        'ust_id',
        'ad',
        'sira',
        'aktif',
    ];

    public function altlar()
    {
        return $this->hasMany(CagriKategori::class, 'ust_id')->where('aktif', 1)->orderBy('sira');
    }
}
