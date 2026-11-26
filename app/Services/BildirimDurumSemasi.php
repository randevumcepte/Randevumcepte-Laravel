<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mobil cihazin "bildirim calisiyor mu" durumu — musteri_portfoy ve salon_personelleri ek alanlari.
 * TEK DOGRULUK KAYNAGI (idempotent). Canli deploy `migrate` calistirmaz; migration + `randevu:sema-ensure`
 * (scheduler) + ilk durum bildiriminde bu cagrilir.
 *
 * Eskiden uygulama ekranda "Bildirimler calismiyor / izin kapali" seridi gosteriyordu; artik kullaniciya
 * gosterilmez, uygulama durumu POST /api/v1/bildirim/durum ile yazar ve Sistem Yonetimi > Bildirim Sorunlari
 * sayfasindan takip edilir.
 *
 *  bildirim_durum             : ok | izin_kapali | token_yok  (NULL = uygulama hic bildirmedi / eski surum)
 *  bildirim_durum_tarihi      : son bildirim (uygulama en son ne zaman durum yazdi)
 *  bildirim_sorun_baslangic   : sorun ilk ne zaman goruldu (ok gelince NULL'lanir)
 *  bildirim_hata              : teknik hata metni (FCM istisnasi vb.)
 *  bildirim_cihaz             : platform / model / uygulama surumu
 */
class BildirimDurumSemasi
{
    const CACHE_ANAHTAR = 'bildirim_durum_kolonlari_v1';
    const TABLOLAR = ['musteri_portfoy', 'salon_personelleri'];
    const DURUMLAR = ['ok', 'izin_kapali', 'token_yok'];

    const KOLONLAR = [
        'bildirim_durum'           => 'VARCHAR(20) NULL',
        'bildirim_durum_tarihi'    => 'DATETIME NULL',
        'bildirim_sorun_baslangic' => 'DATETIME NULL',
        'bildirim_hata'            => 'VARCHAR(255) NULL',
        'bildirim_cihaz'           => 'VARCHAR(120) NULL',
    ];

    /** @var bool|null */
    private static $hazir = null;

    /** Kolonlar var mi? (istek basina + kalici cache) */
    public static function hazir()
    {
        if (self::$hazir !== null) return self::$hazir;
        if (Cache::get(self::CACHE_ANAHTAR)) return self::$hazir = true;
        foreach (self::TABLOLAR as $t) {
            if (!Schema::hasColumn($t, 'bildirim_durum')) return self::$hazir = false;
        }
        Cache::forever(self::CACHE_ANAHTAR, 1);
        return self::$hazir = true;
    }

    /** Eksik kolonlari ekler. @return bool bu cagrida bir sey eklendi mi */
    public static function ensure()
    {
        if (self::hazir()) return false;
        $eklendi = false;
        foreach (self::TABLOLAR as $t) {
            $sonrakiler = [];
            foreach (self::KOLONLAR as $kolon => $tanim) {
                if (!Schema::hasColumn($t, $kolon)) $sonrakiler[] = "ADD COLUMN `$kolon` $tanim";
            }
            if ($sonrakiler) {
                DB::statement("ALTER TABLE `$t` " . implode(', ', $sonrakiler));
                $eklendi = true;
            }
            // Panel sorgusu sorunlulari arar; index ile tablo taramasi olmaz.
            $idx = DB::select("SHOW INDEX FROM `$t` WHERE Key_name = 'idx_bildirim_durum'");
            if (!$idx) DB::statement("ALTER TABLE `$t` ADD INDEX `idx_bildirim_durum` (`bildirim_durum`)");
        }
        self::$hazir = null;
        Cache::forget(self::CACHE_ANAHTAR);
        return $eklendi;
    }
}
