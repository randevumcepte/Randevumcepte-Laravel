<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaslikAltyaziToCarkHatirlatma extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cark_hatirlatma_ayarlari')) return;

        Schema::table('cark_hatirlatma_ayarlari', function (Blueprint $table) {
            foreach (['baslik_1', 'baslik_2', 'baslik_3'] as $col) {
                if (!Schema::hasColumn('cark_hatirlatma_ayarlari', $col)) {
                    $table->string($col, 80)->nullable();
                }
            }
            foreach (['altyazi_1', 'altyazi_2', 'altyazi_3'] as $col) {
                if (!Schema::hasColumn('cark_hatirlatma_ayarlari', $col)) {
                    $table->string($col, 120)->nullable();
                }
            }
        });

        // Eski 4. (son) slot artik kullanilmiyor — kapatip karisikligi onleyelim.
        if (Schema::hasColumn('cark_hatirlatma_ayarlari', 'aktif_son')) {
            \DB::table('cark_hatirlatma_ayarlari')->update(['aktif_son' => 0]);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('cark_hatirlatma_ayarlari')) return;

        Schema::table('cark_hatirlatma_ayarlari', function (Blueprint $table) {
            foreach (['baslik_1', 'baslik_2', 'baslik_3', 'altyazi_1', 'altyazi_2', 'altyazi_3'] as $col) {
                if (Schema::hasColumn('cark_hatirlatma_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
