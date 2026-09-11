<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Studyo Modu" (Pilates/kurs studyolari) bayragi. Varsayilan 0.
 * Acikken: gereksiz paneller gizlenir (on gorusme, urun satisi, satis raporlari/
 * takibi, zirvedekiler, cark, personel maas, dijital onam, saat-yogunluk onerisi),
 * finans sadelesir (fiyat gizli, sadece odendi/odenmedi), vucut olcum modulu acilir.
 * Kapaliyken sistem eskisi gibi (diger sektor musterileri etkilenmez). Idempotent.
 */
class AddStudyoModuToSalonlar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salonlar') && !Schema::hasColumn('salonlar', 'studyo_modu')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->boolean('studyo_modu')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salonlar') && Schema::hasColumn('salonlar', 'studyo_modu')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn('studyo_modu');
            });
        }
    }
}
