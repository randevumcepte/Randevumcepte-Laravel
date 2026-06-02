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
 */
class AddPerformanceIndexesForEasistandata extends Migration
{
    public function up()
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

    public function down()
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

    private function addIndexIfMissing($table, array $cols, $indexName)
    {
        if (!Schema::hasTable($table)) return;
        foreach ($cols as $c) {
            if (!Schema::hasColumn($table, $c)) return;
        }
        $exists = collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
        if ($exists) return;

        try {
            $colList = implode('`,`', $cols);
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`{$colList}`)");
        } catch (\Exception $e) {
            \Log::warning('Index olusturulamadi: '.$indexName.' on '.$table.' — '.$e->getMessage());
        }
    }

    private function dropIndexIfExists($table, $indexName)
    {
        if (!Schema::hasTable($table)) return;
        $exists = collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
        if (!$exists) return;
        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        } catch (\Exception $e) {
            \Log::warning('Index silinemedi: '.$indexName.' — '.$e->getMessage());
        }
    }
}
