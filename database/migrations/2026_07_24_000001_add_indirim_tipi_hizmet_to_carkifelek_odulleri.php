<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kupona indirim tipi (yuzde|tutar) ve gecerli hizmet (hizmet_id) ekler.
 * Boylece bildirim reklamindan gelen ₺ (tutar) kuponlar cekoutta dogru
 * uygulanir ve "gecerli hizmet" kisiti isler.
 *
 * default 'yuzde' -> mevcut cark/puan kuponlari eskisi gibi % calisir.
 * Idempotent: kolon varsa no-op.
 */
class AddIndirimTipiHizmetToCarkifelekOdulleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('carkifelek_odulleri')) return;

        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            if (!Schema::hasColumn('carkifelek_odulleri', 'indirim_tipi')) {
                $table->string('indirim_tipi', 10)->default('yuzde')->after('deger'); // yuzde | tutar
            }
            if (!Schema::hasColumn('carkifelek_odulleri', 'hizmet_id')) {
                $table->unsignedInteger('hizmet_id')->nullable()->after('indirim_tipi'); // null = tum hizmetler
            }
        });
    }

    public function down()
    {
        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_odulleri', 'hizmet_id')) $table->dropColumn('hizmet_id');
            if (Schema::hasColumn('carkifelek_odulleri', 'indirim_tipi')) $table->dropColumn('indirim_tipi');
        });
    }
}
