<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHariciToAdisyonlar extends Migration
{
    /**
     * Harici (dis kaynakli/gecmise donuk) satislari isaretler.
     * harici=1 olan adisyon: finansal raporlarda gorunur AMA
     * stok dusmez, sarf recetesi uygulanmaz, paket seansi olusmaz.
     */
    public function up()
    {
        Schema::table('adisyonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('adisyonlar', 'harici')) {
                $table->boolean('harici')->default(0)->after('user_id')
                    ->comment('1: Harici tahsilat (gecmise donuk salt-finansal satis, yan etki yok)');
            }
        });
    }

    public function down()
    {
        Schema::table('adisyonlar', function (Blueprint $table) {
            if (Schema::hasColumn('adisyonlar', 'harici')) {
                $table->dropColumn('harici');
            }
        });
    }
}
