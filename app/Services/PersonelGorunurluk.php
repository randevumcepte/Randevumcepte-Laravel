<?php

namespace App\Services;

use App\Personeller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Personelin ON GORUSME ve ARAMA RANDEVUSU gorunurlugu (randevu takvimindeki takvimde_gorunsun'dan AYRI).
 * Kayit yoksa personel GORUNUR sayilir. Pilot: yalniz SALONLAR listesindeki isletmeler;
 * digerlerinde ekranda hic cikmaz ve hicbir filtre uygulanmaz.
 */
class PersonelGorunurluk
{
    const SALONLAR = [195];
    const SEMA = 'personel_gorunurluk_sema_v1';

    /** tur => ekranda gorunen ad. Turler ayni zamanda kolon adidir. */
    const TURLER = ['ongorusme' => 'Ön Görüşme', 'arama' => 'Arama Randevusu'];

    public static function aktif($salonId): bool
    {
        return in_array((int) $salonId, self::SALONLAR, true);
    }

    /** Tablo yoksa olusturur (idempotent; migration da bunu cagirir). */
    public static function semaGaranti(): bool
    {
        if (Cache::get(self::SEMA)) return true;
        try {
            if (!Schema::hasTable('personel_gorunurluk')) {
                DB::statement('CREATE TABLE IF NOT EXISTS personel_gorunurluk (
                    personel_id INT UNSIGNED NOT NULL PRIMARY KEY,
                    salon_id INT UNSIGNED NOT NULL,
                    ongorusme TINYINT(1) NOT NULL DEFAULT 1,
                    arama TINYINT(1) NOT NULL DEFAULT 1,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    KEY personel_gorunurluk_salon (salon_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            }
            Cache::put(self::SEMA, 1, 1440); // dakika
            return true;
        } catch (\Throwable $e) {
            \Log::warning('PersonelGorunurluk sema: ' . $e->getMessage());
            return false;
        }
    }

    /** [personel_id => satir]; pilot disi salonda bos. */
    public static function salonHaritasi($salonId): array
    {
        if (!self::aktif($salonId) || !self::semaGaranti()) return [];
        try {
            return DB::table('personel_gorunurluk')->where('salon_id', (int) $salonId)->get()->keyBy('personel_id')->all();
        } catch (\Throwable $e) {
            Cache::forget(self::SEMA); // tablo yoksa sonraki istekte yeniden olusur; ekran dusmesin
            \Log::warning('PersonelGorunurluk oku: ' . $e->getMessage());
            return [];
        }
    }

    /** Verilen turde GIZLI personel id'leri; pilot disi salonda bos (hicbir sey gizlenmez). */
    public static function gizliIdler($salonId, string $tur): array
    {
        if (!isset(self::TURLER[$tur])) return [];
        $gizli = [];
        foreach (self::salonHaritasi($salonId) as $pid => $s) {
            if (!(int) $s->{$tur}) $gizli[] = (int) $pid;
        }
        return $gizli;
    }

    /** @param array $degerler ['ongorusme'=>bool,'arama'=>bool] (eksik tur degismez) */
    public static function kaydet(Personeller $personel, array $degerler): bool
    {
        $degerler = array_intersect_key($degerler, self::TURLER);
        if (!$degerler || !self::aktif($personel->salon_id) || !self::semaGaranti()) return false;
        $yaz = ['salon_id' => (int) $personel->salon_id, 'updated_at' => date('Y-m-d H:i:s')];
        foreach ($degerler as $tur => $d) $yaz[$tur] = $d ? 1 : 0;
        try {
            DB::table('personel_gorunurluk')->updateOrInsert(['personel_id' => (int) $personel->id], $yaz);
            return true;
        } catch (\Throwable $e) {
            Cache::forget(self::SEMA);
            \Log::warning('PersonelGorunurluk kaydet: ' . $e->getMessage());
            return false;
        }
    }
}
