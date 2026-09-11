<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Uye vucut olcumu (Pilates/studyo). Her olcum = tarihli tek satir.
 */
class MusteriOlcum extends Model
{
    protected $table = 'musteri_olcumleri';

    protected $fillable = [
        'salon_id', 'user_id', 'olcum_tarihi', 'boy', 'kilo', 'yas', 'vki',
        'yag_orani', 'odem', 'kas_puani', 'kas_kg', 'ic_yaglanma',
        'olcen_personel_id', 'not',
    ];

    /** VKI (BMI) = kilo / (boy_m)^2. boy cm, kilo kg. */
    public static function vkiHesapla($boy, $kilo)
    {
        $boy = (float) $boy;
        $kilo = (float) $kilo;
        if ($boy <= 0 || $kilo <= 0) return null;
        $m = $boy / 100.0;
        return round($kilo / ($m * $m), 2);
    }

    /** VKI sinifi (bilgi amacli etiket). */
    public static function vkiSinif($vki)
    {
        $v = (float) $vki;
        if ($v <= 0) return '';
        if ($v < 18.5) return 'Zayıf';
        if ($v < 25) return 'Normal';
        if ($v < 30) return 'Fazla Kilolu';
        return 'Obez';
    }
}
