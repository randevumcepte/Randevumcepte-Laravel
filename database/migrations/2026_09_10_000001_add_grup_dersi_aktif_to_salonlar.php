<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grup dersi (Pilates/kurs) modulu flag'i. Varsayilan 0 (kapali) — tum isletmelerde
 * grup dersi kisimlari (takvim enjeksiyonu, menu, butonlar, online liste) gizli.
 * 1 yapilinca ilgili yerler gorunur. Idempotent.
 */
class AddGrupDersiAktifToSalonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salonlar')) return;
        if (!Schema::hasColumn('salonlar', 'grup_dersi_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->boolean('grup_dersi_aktif')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salonlar') && Schema::hasColumn('salonlar', 'grup_dersi_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn('grup_dersi_aktif');
            });
        }
    }
}
