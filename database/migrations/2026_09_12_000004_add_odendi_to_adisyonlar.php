<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Studyo modu (fiyat gizleme): adisyon icin tutar yerine ikili odeme durumu.
 * odendi: NULL/0 = odeme alinmadi, 1 = odeme alindi. YALNIZCA studyo modu
 * ekranlarinda kullanilir; fiyat kullanan isletmelerin tutar-bazli akisi
 * degismez (bu kolon onlar icin hep NULL kalir). Idempotent.
 */
class AddOdendiToAdisyonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('adisyonlar', 'odendi')) {
            Schema::table('adisyonlar', function (Blueprint $table) {
                $table->tinyInteger('odendi')->nullable()->default(null)->after('kapali');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('adisyonlar', 'odendi')) {
            Schema::table('adisyonlar', function (Blueprint $table) {
                $table->dropColumn('odendi');
            });
        }
    }
}
