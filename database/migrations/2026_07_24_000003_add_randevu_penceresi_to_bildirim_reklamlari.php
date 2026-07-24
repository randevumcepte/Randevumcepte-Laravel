<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Bos slot doldurma" reklami: randevu aksiyonu icin tarih + saat araligi.
 * Musteri reklama dokununca randevu ekrani BU tarih ve saat araligina
 * kisitli acilir (sadece kampanya saatleri gosterilir). Idempotent.
 */
class AddRandevuPenceresiToBildirimReklamlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bildirim_reklamlari')) return;

        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            if (!Schema::hasColumn('bildirim_reklamlari', 'randevu_tarih')) {
                $table->date('randevu_tarih')->nullable()->after('aksiyon_hedef');
            }
            if (!Schema::hasColumn('bildirim_reklamlari', 'randevu_saat_bas')) {
                $table->string('randevu_saat_bas', 5)->nullable()->after('randevu_tarih'); // "10:00"
            }
            if (!Schema::hasColumn('bildirim_reklamlari', 'randevu_saat_bit')) {
                $table->string('randevu_saat_bit', 5)->nullable()->after('randevu_saat_bas'); // "12:00"
            }
        });
    }

    public function down()
    {
        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            foreach (['randevu_saat_bit', 'randevu_saat_bas', 'randevu_tarih'] as $col) {
                if (Schema::hasColumn('bildirim_reklamlari', $col)) $table->dropColumn($col);
            }
        });
    }
}
