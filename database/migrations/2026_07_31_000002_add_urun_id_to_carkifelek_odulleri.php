<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * carkifelek_odulleri: kupon icin urun kisitlamasi. hizmet_id null olsa da
 * urun_id doluysa kupon sadece bu urunde gecerli. Reklam kuponundan geliyorsa
 * BildirimReklamlari.kupon_urun_id degeriyle senkronize edilir.
 *
 * Idempotent.
 */
class AddUrunIdToCarkifelekOdulleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('carkifelek_odulleri')) return;
        if (Schema::hasColumn('carkifelek_odulleri', 'urun_id')) return;

        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            $table->unsignedInteger('urun_id')->nullable()->after('hizmet_id');
            $table->index('urun_id', 'co_urun_idx');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('carkifelek_odulleri', 'urun_id')) return;
        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            $table->dropIndex('co_urun_idx');
            $table->dropColumn('urun_id');
        });
    }
}
