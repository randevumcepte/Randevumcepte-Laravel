<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ders hatirlatma idempotency damgasi. Cron (ders:hatirlat) her dakika calisir;
 * bir oturumun hatirlatmasi tam olarak bir kez gitsin diye atomik claim:
 * UPDATE ... SET hatirlatma_gonderildi=NOW() WHERE id=? AND hatirlatma_gonderildi IS NULL
 * Idempotent.
 */
class AddHatirlatmaGonderildiToDersOturumlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ders_oturumlari')) return;
        Schema::table('ders_oturumlari', function (Blueprint $table) {
            if (!Schema::hasColumn('ders_oturumlari', 'hatirlatma_gonderildi')) {
                $table->timestamp('hatirlatma_gonderildi')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('ders_oturumlari')) return;
        Schema::table('ders_oturumlari', function (Blueprint $table) {
            if (Schema::hasColumn('ders_oturumlari', 'hatirlatma_gonderildi')) {
                $table->dropColumn('hatirlatma_gonderildi');
            }
        });
    }
}
