<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Randevu "X saat once" MUSTERI hatirlatmasi icin idempotency bayragi.
 *
 * Bu hatirlatma bugune kadar tam-dakika esitligi (simdi == tetik) ile gidiyordu;
 * cron o dakikayi kacirirsa (sunucu yuku / withoutOverlapping gecikmesi) hatirlatma
 * sonsuza dek kaciyordu. Bayrak + grace penceresi ile kacan dakika yakalanir, yine
 * de tek gonderim garanti edilir. (hatirlatma_gunonce_gonderildi ve
 * hatirlatma_personel_gonderildi ile ayni desen.)
 * Idempotent.
 */
class AddHatirlatmaMusteriGonderildiToRandevular extends Migration
{
    public function up()
    {
        if (Schema::hasTable('randevular') && !Schema::hasColumn('randevular', 'hatirlatma_musteri_gonderildi')) {
            Schema::table('randevular', function (Blueprint $table) {
                $table->timestamp('hatirlatma_musteri_gonderildi')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('randevular') && Schema::hasColumn('randevular', 'hatirlatma_musteri_gonderildi')) {
            Schema::table('randevular', function (Blueprint $table) {
                $table->dropColumn('hatirlatma_musteri_gonderildi');
            });
        }
    }
}
