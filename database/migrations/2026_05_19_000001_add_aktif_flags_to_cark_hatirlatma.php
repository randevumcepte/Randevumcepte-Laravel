<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAktifFlagsToCarkHatirlatma extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cark_hatirlatma_ayarlari')) return;

        Schema::table('cark_hatirlatma_ayarlari', function (Blueprint $table) {
            if (!Schema::hasColumn('cark_hatirlatma_ayarlari', 'aktif_1')) {
                $table->tinyInteger('aktif_1')->default(1)->after('mesaj_son');
            }
            if (!Schema::hasColumn('cark_hatirlatma_ayarlari', 'aktif_2')) {
                $table->tinyInteger('aktif_2')->default(1)->after('aktif_1');
            }
            if (!Schema::hasColumn('cark_hatirlatma_ayarlari', 'aktif_3')) {
                $table->tinyInteger('aktif_3')->default(1)->after('aktif_2');
            }
            if (!Schema::hasColumn('cark_hatirlatma_ayarlari', 'aktif_son')) {
                $table->tinyInteger('aktif_son')->default(1)->after('aktif_3');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('cark_hatirlatma_ayarlari')) return;

        Schema::table('cark_hatirlatma_ayarlari', function (Blueprint $table) {
            foreach (['aktif_1', 'aktif_2', 'aktif_3', 'aktif_son'] as $col) {
                if (Schema::hasColumn('cark_hatirlatma_ayarlari', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
