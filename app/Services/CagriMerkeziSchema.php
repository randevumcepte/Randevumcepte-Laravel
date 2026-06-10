<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cagri Merkezi (Call Center) sema garantisi — TEK DOGRULUK KAYNAGI.
 *
 * NEDEN: Prod deploy'u sadece `git pull` yapiyor, `php artisan migrate` CALISTIRMIYOR.
 * Bu yuzden yeni kolon/tablolar migration ile canliya YANSIMAZ. Cozum deseni
 * (bkz. easistan:index-ensure): semayi idempotent bir Artisan komutuna koy ve
 * scheduler'a bagla; schedule:run cron'da calistigi icin git-pull sonrasi kendiliginden uygulanir.
 *
 * Hem migration (2026_06_11_100001_*) hem de `cagrimerkezi:schema-ensure` komutu
 * bu metodu cagirir. Tamamen idempotent: her sey varsa hizlica doner.
 */
class CagriMerkeziSchema
{
    /** @var string[] Bu calistirmada uygulanan degisiklikler (rapor icin) */
    private static $uygulanan = [];

    /**
     * Tum cagri merkezi semasini garanti eder.
     * @return string[] uygulanan degisiklik aciklamalari (bos = her sey zaten vardi)
     */
    public static function ensure()
    {
        self::$uygulanan = [];
        self::ensureAramaListesi();
        self::ensureAranacakMusteriler();
        self::ensureGorusmeNotlari();
        return self::$uygulanan;
    }

    /** arama_listesi: cagri merkezi calisma alani alanlari */
    private static function ensureAramaListesi()
    {
        if (!Schema::hasTable('arama_listesi')) return;

        if (!Schema::hasColumn('arama_listesi', 'aranacak_tarih')) {
            self::run("ALTER TABLE `arama_listesi` ADD COLUMN `aranacak_tarih` DATE NULL");
        }
        if (!Schema::hasColumn('arama_listesi', 'filtre_snapshot')) {
            self::run("ALTER TABLE `arama_listesi` ADD COLUMN `filtre_snapshot` TEXT NULL");
        }
        if (!Schema::hasColumn('arama_listesi', 'olusturan_yetkili_id')) {
            self::run("ALTER TABLE `arama_listesi` ADD COLUMN `olusturan_yetkili_id` BIGINT UNSIGNED NULL");
        }
        if (!Schema::hasColumn('arama_listesi', 'durum')) {
            self::run("ALTER TABLE `arama_listesi` ADD COLUMN `durum` TINYINT NOT NULL DEFAULT 1");
        }
        if (!Schema::hasColumn('arama_listesi', 'created_at')) {
            self::run("ALTER TABLE `arama_listesi` ADD COLUMN `created_at` TIMESTAMP NULL");
        }
        if (!Schema::hasColumn('arama_listesi', 'updated_at')) {
            self::run("ALTER TABLE `arama_listesi` ADD COLUMN `updated_at` TIMESTAMP NULL");
        }

        self::addIndexIfMissing('arama_listesi', ['aranacak_tarih'], 'al_aranacak_tarih_idx');
        self::addIndexIfMissing('arama_listesi', ['salon_id', 'personel_id', 'durum'], 'al_salon_personel_durum_idx');
    }

    /** aranacak_musteriler: durum genisletme icin ek alan (son arama zamani) */
    private static function ensureAranacakMusteriler()
    {
        if (!Schema::hasTable('aranacak_musteriler')) return;

        if (!Schema::hasColumn('aranacak_musteriler', 'son_arama_zamani')) {
            self::run("ALTER TABLE `aranacak_musteriler` ADD COLUMN `son_arama_zamani` DATETIME NULL");
        }
        // created_at/updated_at toplu insert icin gerekli (model save() bunlari yaziyordu)
        if (!Schema::hasColumn('aranacak_musteriler', 'created_at')) {
            self::run("ALTER TABLE `aranacak_musteriler` ADD COLUMN `created_at` TIMESTAMP NULL");
        }
        if (!Schema::hasColumn('aranacak_musteriler', 'updated_at')) {
            self::run("ALTER TABLE `aranacak_musteriler` ADD COLUMN `updated_at` TIMESTAMP NULL");
        }

        self::addIndexIfMissing('aranacak_musteriler', ['arama_id', 'durum'], 'am_arama_durum_idx');
        self::addIndexIfMissing('aranacak_musteriler', ['user_id'], 'am_user_idx');
        self::addIndexIfMissing('aranacak_musteriler', ['tarih', 'saat'], 'am_tarih_saat_idx');
    }

    /** gorusme_notlari: cogul gorusme notu + ses + sure + sonuc (musteriye kalici bag) */
    private static function ensureGorusmeNotlari()
    {
        if (!Schema::hasTable('gorusme_notlari')) {
            self::run(
                "CREATE TABLE `gorusme_notlari` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `aranacak_musteri_id` BIGINT UNSIGNED NULL,
                    `arama_id` BIGINT UNSIGNED NULL,
                    `salon_id` BIGINT UNSIGNED NULL,
                    `user_id` BIGINT UNSIGNED NULL,
                    `personel_id` BIGINT UNSIGNED NULL,
                    `not` TEXT NULL,
                    `sonuc` TINYINT NULL,
                    `sure_dk` INT NOT NULL DEFAULT 0,
                    `cdr_id` BIGINT UNSIGNED NULL,
                    `ses_kaydi` VARCHAR(255) NULL,
                    `created_at` TIMESTAMP NULL,
                    `updated_at` TIMESTAMP NULL,
                    PRIMARY KEY (`id`),
                    INDEX `gn_personel_created_idx` (`personel_id`, `created_at`),
                    INDEX `gn_arama_idx` (`arama_id`),
                    INDEX `gn_user_idx` (`user_id`),
                    INDEX `gn_aranacak_idx` (`aranacak_musteri_id`),
                    INDEX `gn_cdr_idx` (`cdr_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                'gorusme_notlari tablosu olusturuldu'
            );
        }
    }

    private static function run($sql, $aciklama = null)
    {
        try {
            DB::statement($sql);
            self::$uygulanan[] = $aciklama ?: $sql;
        } catch (\Exception $e) {
            \Log::warning('[CagriMerkeziSchema] DDL hatasi: ' . $e->getMessage() . ' | SQL: ' . $sql);
        }
    }

    private static function addIndexIfMissing($table, array $cols, $indexName)
    {
        if (!Schema::hasTable($table)) return;
        foreach ($cols as $c) {
            if (!Schema::hasColumn($table, $c)) return;
        }
        try {
            $exists = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
            if ($exists) return;
            $colList = implode('`,`', $cols);
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$colList}`)");
            self::$uygulanan[] = "index {$indexName} -> {$table}";
        } catch (\Exception $e) {
            \Log::warning('[CagriMerkeziSchema] Index hatasi: ' . $indexName . ' on ' . $table . ' — ' . $e->getMessage());
        }
    }
}
