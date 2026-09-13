<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Tekrarli otomatik ders katilimi kaydi (studyo modu).
 * Bir musterinin bir satisindan (paket/hizmet) gelen seanslari, secilen
 * gun/saat/hoca tercihine gore ileri tarihli oturumlara dagitir.
 */
class DersTekrarliKatilim extends Model
{
    protected $table = 'ders_tekrarli_katilim';

    protected $fillable = [
        'salon_id', 'user_id', 'hizmet_id', 'adisyon_paket_id', 'adisyon_hizmet_id',
        'toplam_seans', 'gunler', 'saatler', 'personel_id', 'baslangic_tarihi', 'aktif',
    ];

    // gunler / saatler JSON string tutulur; dizi olarak okumak icin yardimcilar.
    public function gunlerDizi()
    {
        $d = json_decode($this->gunler ?? '[]', true);
        return is_array($d) ? array_map('intval', $d) : [];
    }

    public function saatlerDizi()
    {
        $d = json_decode($this->saatler ?? '[]', true);
        return is_array($d) ? array_values(array_map('strval', $d)) : [];
    }

    public function musteri()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
