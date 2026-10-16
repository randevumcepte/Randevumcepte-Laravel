<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelafiKolonlariRandevuHizmetlere extends Migration
{
    public function up()
    {
        Schema::table('randevu_hizmetler', function (Blueprint $t) {
            if (!Schema::hasColumn('randevu_hizmetler', 'telafi_kaynak_rh_id')) {
                // Bu RH bir telafi randevusuysa, orijinal (ucretli) randevu_hizmetler.id
                $t->integer('telafi_kaynak_rh_id')->unsigned()->nullable()->index('rh_telafi_kaynak_idx');
            }
            if (!Schema::hasColumn('randevu_hizmetler', 'telafi_devir_yapildi')) {
                // Prim devri yapildiysa 1 (yineleme olmasin)
                $t->tinyInteger('telafi_devir_yapildi')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('randevu_hizmetler', function (Blueprint $t) {
            if (Schema::hasColumn('randevu_hizmetler', 'telafi_kaynak_rh_id')) {
                $t->dropIndex('rh_telafi_kaynak_idx');
                $t->dropColumn('telafi_kaynak_rh_id');
            }
            if (Schema::hasColumn('randevu_hizmetler', 'telafi_devir_yapildi')) {
                $t->dropColumn('telafi_devir_yapildi');
            }
        });
    }
}
