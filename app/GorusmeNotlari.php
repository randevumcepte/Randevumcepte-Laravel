<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Cagri Merkezi: bir aranacak musteriye ait cogul gorusme notu/ses kaydi gecmisi.
 * Tek bir musteri birden fazla kez aranabilecegi icin her gorusme ayri satirdir
 * (musteriye kalici olarak user_id ile baglanir).
 */
class GorusmeNotlari extends Model
{
    protected $table = 'gorusme_notlari';

    protected $fillable = [
        'aranacak_musteri_id',
        'arama_id',
        'salon_id',
        'user_id',
        'personel_id',
        'not',
        'sonuc',
        'kategori_id',
        'alt_kategori_id',
        'sure_dk',
        'cdr_id',
        'ses_kaydi',
    ];

    public function musteri()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function personel()
    {
        return $this->belongsTo(Personeller::class, 'personel_id');
    }

    public function aranacak()
    {
        return $this->belongsTo(AranacakMusteriler::class, 'aranacak_musteri_id');
    }
}
