<?php

namespace App\Services;

use App\PersonelYetkiAyari;
use App\PersonelYetkiSabitleri;
use App\Personeller;
use App\IsletmeYetkilileri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Personel yetki kontrol merkezi.
 *
 * Kullanim:
 *   if (!PersonelYetkiServisi::yetkiVar($personelId, $salonId, 'musteri.telefon_gor')) {
 *       // numara maskele
 *   }
 *
 * Kural:
 *   1. Personel rolu olmayan (Hesap Sahibi / Yonetici / Supervisor / Sekreter
 *      / Sanat Yonetmeni / Sosyal Medya Uzmani) → her zaman TRUE doner.
 *   2. Personel rolundeyse → personel_yetki_ayarlari tablosuna bakar.
 *   3. Tablo bos / kayit yoksa → 'personel_sade' sablonu uygulanir.
 */
class PersonelYetkiServisi
{
    /**
     * Tablo varligi kontrolu. Migration sunucuda calismadiysa runtime'da yarat.
     * Bu defansif onlem; normal akista migration yeterli olur. Ilk yetki
     * kayit/oku denemesinde tetiklenir.
     */
    private static $_tabloHazir = false;
    public static function tabloyuHazirla(): void
    {
        if (self::$_tabloHazir) return;
        // Raw SQL kullaniyoruz cunku JSON column tipi eski MySQL'de problem yapabilir;
        // TEXT + Eloquent cast 'array' ile ayni isi gorur.
        try {
            $exists = false;
            try {
                $exists = Schema::hasTable('personel_yetki_ayarlari');
            } catch (\Exception $e) {
                // Schema::hasTable bazi sunuculurda hata atabilir; raw kontrol yapalim
                $rows = DB::select("SHOW TABLES LIKE 'personel_yetki_ayarlari'");
                $exists = !empty($rows);
            }
            if (!$exists) {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS personel_yetki_ayarlari (
                        id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        personel_id BIGINT UNSIGNED NOT NULL,
                        salon_id BIGINT UNSIGNED NOT NULL,
                        sablon VARCHAR(32) NOT NULL DEFAULT 'personel_sade',
                        ayarlar LONGTEXT NULL,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        UNIQUE KEY pya_unique_per_sub (personel_id, salon_id),
                        KEY pya_salon_idx (salon_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                \Log::info('PersonelYetkiServisi: personel_yetki_ayarlari tablosu runtime olusturuldu.');
            }
            self::$_tabloHazir = true;
        } catch (\Exception $e) {
            \Log::warning('PersonelYetkiServisi tabloyuHazirla HATA: ' . $e->getMessage());
            // Exception'i bastirma — yukari fırlat ki controller catch'inde mesaj gozuksun
            throw $e;
        }
    }

    /**
     * Yetki kontrolu.
     *
     * @param int|string $personelId  salon_personelleri.id
     * @param int|string $salonId
     * @param string     $key         Ornek: 'musteri.telefon_gor'
     */
    public static function yetkiVar($personelId, $salonId, string $key): bool
    {
        self::tabloyuHazirla();
        if (!$personelId || !$salonId) return true; // bilinmiyorsa engelleme

        // 1. Personel rolu degilse → tam yetki
        if (!self::personelRolundeMi($personelId, $salonId)) {
            return true;
        }

        // 2. Yetki ayar kaydi
        $ayar = PersonelYetkiAyari::where('personel_id', $personelId)
            ->where('salon_id', $salonId)
            ->first();

        $ayarlar = [];
        if ($ayar && is_array($ayar->ayarlar)) {
            $ayarlar = $ayar->ayarlar;
        } else {
            // Default: personel_sade
            $ayarlar = PersonelYetkiSabitleri::sablonAyarlari('personel_sade');
        }

        // Anahtar tabloda yoksa default sade'den al
        if (!array_key_exists($key, $ayarlar)) {
            $sadeAyarlar = PersonelYetkiSabitleri::sablonAyarlari('personel_sade');
            return (bool)($sadeAyarlar[$key] ?? false);
        }

        return (bool)$ayarlar[$key];
    }

    /**
     * IsletmeYetkilileri.id (giris yapan kullanici) icin yetki kontrolu.
     * Yetkili'nin personel_id'sini bulup ana yetkiVar()'a yonlendirir.
     */
    public static function yetkiliYetkiVar($yetkiliId, $salonId, string $key): bool
    {
        if (!$yetkiliId || !$salonId) return true;

        // Yetkili'nin baglandigi personel_id (varsa)
        $personelId = IsletmeYetkilileri::where('id', $yetkiliId)->value('personel_id');
        if (!$personelId) {
            // Personel kaydi yoksa salon sahibidir veya direk yetkilidir → tam yetki
            return true;
        }
        return self::yetkiVar($personelId, $salonId, $key);
    }

    /**
     * Personel rolunde mi? (model_has_roles.role_id == 5)
     */
    public static function personelRolundeMi($personelId, $salonId): bool
    {
        $yetkiliId = Personeller::where('id', $personelId)
            ->where('salon_id', $salonId)
            ->value('yetkili_id');
        if (!$yetkiliId) return false;

        return DB::table('model_has_roles')
            ->where('role_id', 5) // Personel rolu
            ->where('model_id', $yetkiliId)
            ->where('salon_id', $salonId)
            ->exists();
    }

    /**
     * Personelin tum yetki ayarlarini dondur (yoksa default).
     * Frontend bunu cekip UI'da gosterir.
     */
    public static function ayarlariGetir($personelId, $salonId): array
    {
        self::tabloyuHazirla();
        $ayar = PersonelYetkiAyari::where('personel_id', $personelId)
            ->where('salon_id', $salonId)
            ->first();

        if ($ayar) {
            $kayitliAyarlar = is_array($ayar->ayarlar) ? $ayar->ayarlar : [];
            // Eksik anahtarlari sade sablondan tamamla
            $sade = PersonelYetkiSabitleri::sablonAyarlari('personel_sade');
            return [
                'sablon' => $ayar->sablon ?: 'ozel',
                'ayarlar' => array_merge($sade, $kayitliAyarlar),
            ];
        }
        // Hic kayit yoksa → personel_sade default
        return [
            'sablon' => 'personel_sade',
            'ayarlar' => PersonelYetkiSabitleri::sablonAyarlari('personel_sade'),
        ];
    }

    /**
     * Ayarlari kaydet. $sablon 'ozel' ise UI'dan gelen ayarlar kullanilir.
     * Onceden tanimli sablon ('sekreter' vb) gelirse o sablonun ayarlari + UI override.
     */
    public static function ayarlariKaydet($personelId, $salonId, string $sablon, array $ayarlar): PersonelYetkiAyari
    {
        self::tabloyuHazirla();
        // Bilinmeyen sablon → ozel olarak isaretle
        $bilinen = array_keys(PersonelYetkiSabitleri::sablonlar());
        if (!in_array($sablon, $bilinen) && $sablon !== 'ozel') {
            $sablon = 'ozel';
        }

        // Sadece tanimli anahtarlari kabul et (XSS / kotu key engelleme)
        $izinli = PersonelYetkiSabitleri::tumAnahtarlar();
        $temizAyarlar = [];
        foreach ($izinli as $key) {
            if (array_key_exists($key, $ayarlar)) {
                $temizAyarlar[$key] = (bool)$ayarlar[$key];
            }
        }

        return PersonelYetkiAyari::updateOrCreate(
            ['personel_id' => $personelId, 'salon_id' => $salonId],
            ['sablon' => $sablon, 'ayarlar' => $temizAyarlar]
        );
    }
}
