<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * randevular ek alanlari — TEK DOGRULUK KAYNAGI (idempotent).
 * Canli deploy `migrate` calistirmaz; migration + `randevu:sema-ensure` (scheduler) bunu cagirir.
 *
 * randevuya_islemde: musteri su an islemde (Gelecek -> Islemde -> Geldi). NULL/0 = degil.
 */
class RandevuSemasi
{
    const CACHE_ANAHTAR = 'randevular_islemde_kolonu_var';

    /** @var bool|null */
    private static $islemdeVar = null;

    /** Kolon var mi? Yoksa liste sorgulari/menusu "Islemde"yi hic kullanmaz (500 vermez). */
    public static function islemdeVar()
    {
        if (self::$islemdeVar !== null) return self::$islemdeVar;
        if (Cache::get(self::CACHE_ANAHTAR)) return self::$islemdeVar = true;
        self::$islemdeVar = Schema::hasColumn('randevular', 'randevuya_islemde');
        if (self::$islemdeVar) Cache::forever(self::CACHE_ANAHTAR, 1);
        return self::$islemdeVar;
    }

    /** @return bool kolon bu cagrida eklendi mi */
    public static function ensure()
    {
        if (Schema::hasColumn('randevular', 'randevuya_islemde')) return false;
        DB::statement('ALTER TABLE `randevular` ADD COLUMN `randevuya_islemde` TINYINT(1) NULL');
        self::$islemdeVar = null;
        return true;
    }
}
