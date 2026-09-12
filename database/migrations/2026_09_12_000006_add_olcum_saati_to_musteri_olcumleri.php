<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vucut olcumune saat bilgisi. Ayni gun birden fazla olcum olabilecegi icin
 * kayit ani saati saklanir (tarih + saat birlikte gosterilir). Idempotent.
 */
class AddOlcumSaatiToMusteriOlcumleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('musteri_olcumleri')) return;
        if (Schema::hasColumn('musteri_olcumleri', 'olcum_saati')) return;
        Schema::table('musteri_olcumleri', function (Blueprint $table) {
            $table->time('olcum_saati')->nullable()->after('olcum_tarihi');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('musteri_olcumleri')) return;
        if (!Schema::hasColumn('musteri_olcumleri', 'olcum_saati')) return;
        Schema::table('musteri_olcumleri', function (Blueprint $table) {
            $table->dropColumn('olcum_saati');
        });
    }
}
