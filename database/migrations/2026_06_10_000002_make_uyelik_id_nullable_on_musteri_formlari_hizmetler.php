<?php

use Illuminate\Database\Migrations\Migration;

class MakeUyelikIdNullableOnMusteriFormlariHizmetler extends Migration
{
    /**
     * Manuel/serbest hizmet satirlari (uyelik paketine bagli olmayan, ad+adet+tutar ile
     * girilen kalemler) icin uyelik_id bos olabilmeli. FK kolonu NULL degerlere zaten
     * izin verir (FK yalnizca non-null degerleri denetler), bu yuzden FK kaldirilmaz;
     * sadece NOT NULL kisiti gevsetilir.
     */
    public function up()
    {
        try {
            \DB::statement('ALTER TABLE musteri_formlari_hizmetler MODIFY uyelik_id INT(10) UNSIGNED NULL');
        } catch (\Exception $e) {
            // surucu desteklemiyorsa sessizce gec
        }
    }

    public function down()
    {
        try {
            \DB::statement('ALTER TABLE musteri_formlari_hizmetler MODIFY uyelik_id INT(10) UNSIGNED NOT NULL');
        } catch (\Exception $e) {
            // geri alinamiyorsa (NULL kayit varsa) sessizce gec
        }
    }
}
