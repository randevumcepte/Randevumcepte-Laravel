<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * randevular.hatirlatma_yaklasan_gonderildi:
 * Musteriye "X saat once" yaklasan randevu hatirlatmasinin gonderildigini
 * isaretler. Eskiden sadece strict-equality (simdi == tetik_dakika) check'i
 * vardi; cron bir tick kacirinca o randevunun hatirlatmasi sonsuza kadar
 * kayboluyordu. Yeni mantik: tetik dakikasindan randevu saatine kadar
 * RANGE check + bu kolon ile atomik claim.
 */
class AddHatirlatmaYaklasanToRandevular extends Migration
{
    public function up()
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (!Schema::hasColumn('randevular', 'hatirlatma_yaklasan_gonderildi')) {
                $table->timestamp('hatirlatma_yaklasan_gonderildi')->nullable();
                $table->index(['tarih', 'hatirlatma_yaklasan_gonderildi'], 'rnd_tarih_yaklasan_idx');
            }
        });
    }

    public function down()
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'hatirlatma_yaklasan_gonderildi')) {
                $table->dropIndex('rnd_tarih_yaklasan_idx');
                $table->dropColumn('hatirlatma_yaklasan_gonderildi');
            }
        });
    }
}
