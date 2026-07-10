<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satis Takibi (adisyon_yukle) acik/kapali sayimi ve listesi her adisyon icin
 * satis-tahsilat bakiyesini ANLIK hesapliyor (kalici kolon yok — istenmedi).
 * Bottleneck: adisyon_hizmetler/urunler/paketler tablolarinda adisyon_id'ye gore
 * SUM(fiyat) alt-sorgulari (satir basina 3x, ~45k satir) ve adisyonlar(salon_id,
 * tarih) taramasi. 2026-06-10 perf migration'i tahsilat_* tablolarini indexledi
 * ama bu tablolari atlamis.
 *
 * Bu migration eksik index'leri ekler — DAVRANIS DEGISMEZ, sadece hizlanir.
 * Idempotent + kolon-farkinda: ilgili kolonu zaten BASTAN kapsayan bir index
 * varsa (orn. FK index) atlanir; mukerrer index olusturulmaz.
 */
class AddAdisyonBalanceIndexes extends Migration
{
    public function up()
    {
        // Acik/kapali bakiye alt-sorgularinin cektigi tablolar — adisyon_id'ye gore.
        $this->addIndexIfColumnNotLeading('adisyon_hizmetler', ['adisyon_id'], 'ah_adisyon_idx');
        $this->addIndexIfColumnNotLeading('adisyon_urunler',   ['adisyon_id'], 'au_adisyon_idx');
        $this->addIndexIfColumnNotLeading('adisyon_paketler',  ['adisyon_id'], 'ap_adisyon_idx');

        // Liste + sayim disinda kalan adisyonlari salon + tarih araligiyla suzuyor.
        $this->addIndexIfColumnNotLeading('adisyonlar', ['salon_id', 'tarih'], 'adisyon_salon_tarih_idx');
    }

    public function down()
    {
        $this->dropIndexIfExists('adisyon_hizmetler', 'ah_adisyon_idx');
        $this->dropIndexIfExists('adisyon_urunler',   'au_adisyon_idx');
        $this->dropIndexIfExists('adisyon_paketler',  'ap_adisyon_idx');
        $this->dropIndexIfExists('adisyonlar',        'adisyon_salon_tarih_idx');
    }

    /**
     * $cols'un ILK kolonunu bastan kapsayan (Seq_in_index = 1) bir index zaten
     * varsa hicbir sey yapmaz (mukerrer onler). Yoksa $cols uzerinde index ekler.
     */
    private function addIndexIfColumnNotLeading($table, array $cols, $indexName)
    {
        if (!Schema::hasTable($table)) return;
        foreach ($cols as $c) {
            if (!Schema::hasColumn($table, $c)) return;
        }

        // Ayni isimde index zaten var mi?
        $named = collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
        if ($named) return;

        // Ilk kolonu zaten lider olarak iceren bir index var mi? (FK index dahil)
        $leadingExists = collect(DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Column_name = ? AND Seq_in_index = 1",
            [$cols[0]]
        ))->isNotEmpty();

        // Tek kolonluk hedefte lider index varsa gerek yok.
        // Composite hedefte (orn. salon_id,tarih) lider salon_id index'i tarih
        // araligini optimize etmeyebilir; yine de FK/var olan index'i bozmamak
        // icin sadece HIC lider index yoksa ekliyoruz (guvenli taraf).
        if ($leadingExists) return;

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
