<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHatirlatmaPersonelToRandevular extends Migration
{
    public function up()
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (!Schema::hasColumn('randevular', 'hatirlatma_personel_gonderildi')) {
                $table->timestamp('hatirlatma_personel_gonderildi')->nullable();
                $table->index(['tarih', 'hatirlatma_personel_gonderildi']);
            }
        });
    }

    public function down()
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'hatirlatma_personel_gonderildi')) {
                $table->dropIndex(['tarih', 'hatirlatma_personel_gonderildi']);
                $table->dropColumn('hatirlatma_personel_gonderildi');
            }
        });
    }
}
