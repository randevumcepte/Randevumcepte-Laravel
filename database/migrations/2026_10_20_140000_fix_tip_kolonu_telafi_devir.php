<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * personel_prim_hareketleri.tip sutunu ENUM('bonus','kesinti',...) oldugundan 'telafi_devir'
 * degerini KABUL ETMIYOR; MariaDB non-strict modda bu degeri '' (bos string) olarak yaziyordu.
 * Bos tip -> primHakedisVerisi'de 'else => kesinti' kovasina dusup NEGATIF kesinti oluyor,
 * net hak edise TERS isaretle ekleniyordu (ör. -60 clawback -> net'e +60). Telafi Devri sutunu
 * da hep 0.00 kaliyordu (hicbir satirda tip gercekten 'telafi_devir' degil).
 *
 * Cozum: tip'i VARCHAR'a cevir (gelecekteki tipler de sorunsuz yazilsin) ve bos tiple yazilmis
 * telafi devir kayitlarini 'telafi_devir' olarak geri yaz.
 */
class FixTipKolonuTelafiDevir extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('personel_prim_hareketleri', 'tip')) return;

        // 1) Olasi NULL'lari doldur (NOT NULL MODIFY patlamasin)
        DB::statement("UPDATE personel_prim_hareketleri SET tip='bonus' WHERE tip IS NULL");

        // 2) ENUM -> VARCHAR(30): artik 'telafi_devir' dahil her tip yazilabilir
        DB::statement("ALTER TABLE personel_prim_hareketleri
            MODIFY COLUMN tip VARCHAR(30) NOT NULL DEFAULT 'bonus'");

        // 3) ENUM reddi yuzunden bos ('') yazilmis telafi devir kayitlarini duzelt.
        //    Telafi devir isareti: kaynak_* referansi dolu YA DA aciklamada 'telafi' geciyor.
        DB::statement("UPDATE personel_prim_hareketleri
            SET tip='telafi_devir'
            WHERE (tip IS NULL OR tip='' OR tip='0')
              AND (kaynak_adisyon_hizmet_id IS NOT NULL
                   OR kaynak_adisyon_paket_id IS NOT NULL
                   OR aciklama LIKE '%elafi%')");
    }

    public function down()
    {
        // tip VARCHAR olarak kalir (geri ENUM'a dondurmek riskli; veri kaybina gerek yok).
    }
}
