<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * E-Asistan ve diger admin sayfalarinin temel sorgu performansi icin
 * eksik index'leri ekler. Bu tablolarda hicbir composite index olmadigi
 * tespit edildi; 100k+ satirli production veritabanlarinda asistan
 * sayfasi full table scan yapiyordu.
 *
 * Index'ler:
 *  - randevular(salon_id, tarih)              → easistandata bugun/yarin
 *  - randevular(salon_id, onceki_tarih)       → easistandata OR sarti
 *  - randevular(salon_id, durum)              → randevu listeleme
 *  - alacaklar(salon_id, planlanan_odeme_tarihi)
 *  - alacaklar(salon_id, onceki_planlanan_odeme_tarihi)
 *  - randevu_hizmetler(randevu_id)            → has('hizmetler') EXISTS
 *  - bildirimler(salon_id, personel_id, id)   → notification dropdown
 *  - kampanya_yonetimi(salon_id, asistan_tarih_saat) → asistan kampanya
 *
 * MariaDB 10.6+ / MySQL 8.0+ "ALTER TABLE ... ADD INDEX" ONLINE DDL ile
 * calisir; bloklama minimumdur. Yine de tablo gerinde calistirilmasi
 * onerilir (gece). Index zaten varsa skip edilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('randevular',         ['salon_id','tarih'],                          'rand_salon_tarih_idx');
        $this->addIndexIfMissing('randevular',         ['salon_id','onceki_tarih'],                   'rand_salon_onceki_idx');
        $this->addIndexIfMissing('randevular',         ['salon_id','durum'],                          'rand_salon_durum_idx');
        $this->addIndexIfMissing('alacaklar',          ['salon_id','planlanan_odeme_tarihi'],         'alacak_salon_tarih_idx');
        $this->addIndexIfMissing('alacaklar',          ['salon_id','onceki_planlanan_odeme_tarihi'],  'alacak_salon_onceki_idx');
        $this->addIndexIfMissing('randevu_hizmetler',  ['randevu_id'],                                'rh_randevu_idx');
        $this->addIndexIfMissing('bildirimler',        ['salon_id','personel_id','id'],               'bild_salon_personel_idx');
        $this->addIndexIfMissing('kampanya_yonetimi',  ['salon_id','asistan_tarih_saat'],             'kampanya_salon_tarih_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('randevular',         'rand_salon_tarih_idx');
        $this->dropIndexIfExists('randevular',         'rand_salon_onceki_idx');
        $this->dropIndexIfExists('randevular',         'rand_salon_durum_idx');
        $this->dropIndexIfExists('alacaklar',          'alacak_salon_tarih_idx');
        $this->dropIndexIfExists('alacaklar',          'alacak_salon_onceki_idx');
        $this->dropIndexIfExists('randevu_hizmetler',  'rh_randevu_idx');
        $this->dropIndexIfExists('bildirimler',        'bild_salon_personel_idx');
        $this->dropIndexIfExists('kampanya_yonetimi',  'kampanya_salon_tarih_idx');
    }

    private function addIndexIfMissing(string $table, array $cols, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;
        // Kolonlarin hepsi var mi?
        foreach ($cols as $c) {
            if (!Schema::hasColumn($table, $c)) return;
        }
        // Index zaten var mi? information_schema'dan kontrol
        $exists = collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
        if ($exists) return;

        try {
            $colList = implode('`,`', $cols);
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$colList}`)");
        } catch (\Throwable $e) {
            // index olusturma hatasini sessizce gec, log
            \Log::warning('Index olusturulamadi: '.$indexName.' on '.$table.' — '.$e->getMessage());
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;
        $exists = collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
        if (!$exists) return;
        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        } catch (\Throwable $e) {
            \Log::warning('Index silinemedi: '.$indexName.' — '.$e->getMessage());
        }
    }
};
