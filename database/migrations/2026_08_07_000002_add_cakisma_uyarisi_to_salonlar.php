<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Randevu olustururken, salonun takvim turune gore (personel/cihaz/oda)
 * ayni kaynak o saatte doluysa uyari popup'i gostermek icin salon-bazli
 * ayar. Varsayilan KAPALI => mevcut davranis birebir korunur.
 */
class AddCakismaUyarisiToSalonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salonlar')) return;
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'cakisma_uyarisi_aktif')) {
                $table->boolean('cakisma_uyarisi_aktif')->default(0);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('salonlar')) return;
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'cakisma_uyarisi_aktif')) {
                $table->dropColumn('cakisma_uyarisi_aktif');
            }
        });
    }
}
