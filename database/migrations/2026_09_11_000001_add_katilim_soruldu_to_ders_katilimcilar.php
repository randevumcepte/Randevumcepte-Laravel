<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ders bitiminde musteriye "katildiniz mi?" push'u GONDERILDI mi bayragi.
 * Salon "Geldi" isaretlemediyse, ders bitiminde musteriye tek sefer sorulur.
 * Idempotent.
 */
class AddKatilimSorulduToDersKatilimcilar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ders_katilimcilar') && !Schema::hasColumn('ders_katilimcilar', 'katilim_soruldu')) {
            Schema::table('ders_katilimcilar', function (Blueprint $table) {
                $table->boolean('katilim_soruldu')->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ders_katilimcilar') && Schema::hasColumn('ders_katilimcilar', 'katilim_soruldu')) {
            Schema::table('ders_katilimcilar', function (Blueprint $table) {
                $table->dropColumn('katilim_soruldu');
            });
        }
    }
}
