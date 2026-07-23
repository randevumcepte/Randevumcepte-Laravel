<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bildirim reklamindan (bildirim_reklamlari) tek-dokunusla kapilan kuponu
 * kaynak reklama baglar. Kisi-basi limit kontrolu ve rapor icin gerekir.
 * null = reklamdan gelmeyen (cark/puan) normal kupon.
 *
 * Idempotent: kolon varsa no-op.
 */
class AddKaynakReklamIdToCarkifelekOdulleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('carkifelek_odulleri')) return;
        if (Schema::hasColumn('carkifelek_odulleri', 'kaynak_reklam_id')) return;

        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            $table->unsignedInteger('kaynak_reklam_id')->nullable()->after('log_id');
            $table->index('kaynak_reklam_id', 'co_kaynak_reklam_idx');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('carkifelek_odulleri', 'kaynak_reklam_id')) return;
        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            $table->dropIndex('co_kaynak_reklam_idx');
            $table->dropColumn('kaynak_reklam_id');
        });
    }
}
