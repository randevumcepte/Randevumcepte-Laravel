<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHatirlatmaGunonceToRandevular extends Migration
{
    public function up()
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (!Schema::hasColumn('randevular', 'hatirlatma_gunonce_gonderildi')) {
                $table->timestamp('hatirlatma_gunonce_gonderildi')->nullable();
                $table->index(['tarih', 'hatirlatma_gunonce_gonderildi']);
            }
        });
    }

    public function down()
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (Schema::hasColumn('randevular', 'hatirlatma_gunonce_gonderildi')) {
                $table->dropIndex(['tarih', 'hatirlatma_gunonce_gonderildi']);
                $table->dropColumn('hatirlatma_gunonce_gonderildi');
            }
        });
    }
}
