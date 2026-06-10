<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sicak tablolarda eksik index'leri ekler (performans denetimi 2026-06-10).
 *
 * Tespit: randevu_hizmetler sadece randevu_id'de indexli; takvim her randevu
 * icin personel/hizmet/oda/cihaz JOIN'i yapiyor. tahsilat_* ve adisyon_paket_seanslar
 * tablolarinda hic index yok; yuzlerce sum()/count() full table scan yapiyor.
 *
 * TAMAMEN GUVENLI: her index oncesinde tablo + kolon varligi ve index'in zaten
 * olup olmadigi kontrol edilir (idempotent). Davranis degismez, sadece hizlanir.
 * Elle eklenmis index'ler varsa atlanir.
 */
class AddPerformanceIndexesHotTables extends Migration
{
    public function up()
    {
        // randevu_hizmetler — takvim eager-load JOIN'leri (randevu_id zaten indexli)
        $this->addIndexIfMissing('randevu_hizmetler', ['personel_id'], 'rh_personel_idx');
        $this->addIndexIfMissing('randevu_hizmetler', ['hizmet_id'],   'rh_hizmet_idx');
        $this->addIndexIfMissing('randevu_hizmetler', ['oda_id'],      'rh_oda_idx');
        $this->addIndexIfMissing('randevu_hizmetler', ['cihaz_id'],    'rh_cihaz_idx');

        // tahsilat_* — adisyon detay/tahsilat ekranlarinda sum('tutar')/count()
        $this->addIndexIfMissing('tahsilat_hizmetler', ['adisyon_hizmet_id'], 'th_adisyon_hizmet_idx');
        $this->addIndexIfMissing('tahsilat_hizmetler', ['tahsilat_id'],       'th_tahsilat_idx');
        $this->addIndexIfMissing('tahsilat_urunler',   ['adisyon_urun_id'],   'tu_adisyon_urun_idx');
        $this->addIndexIfMissing('tahsilat_urunler',   ['tahsilat_id'],       'tu_tahsilat_idx');
        $this->addIndexIfMissing('tahsilat_paketler',  ['adisyon_paket_id'],  'tp_adisyon_paket_idx');
        $this->addIndexIfMissing('tahsilat_paketler',  ['tahsilat_id'],       'tp_tahsilat_idx');

        // adisyon_paket_seanslar — paket seans listeleme
        $this->addIndexIfMissing('adisyon_paket_seanslar', ['adisyon_paket_id'], 'aps_adisyon_paket_idx');
        $this->addIndexIfMissing('adisyon_paket_seanslar', ['randevu_id'],       'aps_randevu_idx');

        // salon bazli kaynak/listeleme sorgulari
        $this->addIndexIfMissing('paket_satislari',    ['salon_id'],   'ps_salon_idx');
        $this->addIndexIfMissing('hizmetler',          ['salon_id'],   'hizmet_salon_idx');
        $this->addIndexIfMissing('odalar',             ['salon_id'],   'oda_salon_idx');
        $this->addIndexIfMissing('cihazlar',           ['salon_id'],   'cihaz_salon_idx');

        // randevular(user_id) — musteri randevu gecmisi (mevcut composite'ler salon_id on ekli)
        $this->addIndexIfMissing('randevular',         ['user_id'],    'rand_user_idx');

        // salon_personelleri(yetkili_id) — kanonik yetkili-salon sorgusu
        $this->addIndexIfMissing('salon_personelleri', ['yetkili_id'], 'sp_yetkili_idx');
    }

    public function down()
    {
        $this->dropIndexIfExists('randevu_hizmetler', 'rh_personel_idx');
        $this->dropIndexIfExists('randevu_hizmetler', 'rh_hizmet_idx');
        $this->dropIndexIfExists('randevu_hizmetler', 'rh_oda_idx');
        $this->dropIndexIfExists('randevu_hizmetler', 'rh_cihaz_idx');
        $this->dropIndexIfExists('tahsilat_hizmetler', 'th_adisyon_hizmet_idx');
        $this->dropIndexIfExists('tahsilat_hizmetler', 'th_tahsilat_idx');
        $this->dropIndexIfExists('tahsilat_urunler',   'tu_adisyon_urun_idx');
        $this->dropIndexIfExists('tahsilat_urunler',   'tu_tahsilat_idx');
        $this->dropIndexIfExists('tahsilat_paketler',  'tp_adisyon_paket_idx');
        $this->dropIndexIfExists('tahsilat_paketler',  'tp_tahsilat_idx');
        $this->dropIndexIfExists('adisyon_paket_seanslar', 'aps_adisyon_paket_idx');
        $this->dropIndexIfExists('adisyon_paket_seanslar', 'aps_randevu_idx');
        $this->dropIndexIfExists('paket_satislari',    'ps_salon_idx');
        $this->dropIndexIfExists('hizmetler',          'hizmet_salon_idx');
        $this->dropIndexIfExists('odalar',             'oda_salon_idx');
        $this->dropIndexIfExists('cihazlar',           'cihaz_salon_idx');
        $this->dropIndexIfExists('randevular',         'rand_user_idx');
        $this->dropIndexIfExists('salon_personelleri', 'sp_yetkili_idx');
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
