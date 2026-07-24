<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bos slot kampanyasi tek gun degil, bir TARIH ARALIGI olabilir:
 * randevu_tarih = baslangic tarihi, randevu_tarih_bit = bitis tarihi.
 * (Her gun ayni saat araliginda randevu.) Idempotent.
 */
class AddRandevuTarihBitToBildirimReklamlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bildirim_reklamlari')) return;
        if (Schema::hasColumn('bildirim_reklamlari', 'randevu_tarih_bit')) return;

        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            $table->date('randevu_tarih_bit')->nullable()->after('randevu_tarih');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('bildirim_reklamlari', 'randevu_tarih_bit')) return;
        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            $table->dropColumn('randevu_tarih_bit');
        });
    }
}
