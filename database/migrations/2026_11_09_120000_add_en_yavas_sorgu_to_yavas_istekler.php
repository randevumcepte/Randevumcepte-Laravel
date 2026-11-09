<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * yavas_istekler'e sorgu suresi ve en yavas sorgu: istegin yavasligi DB'den mi, hangi sorgudan mi
 * dogrudan gorunsun. SQL parametresiz (?) yazilir; kisisel veri icermez.
 */
class AddEnYavasSorguToYavasIstekler extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('yavas_istekler')) return;
        Schema::table('yavas_istekler', function (Blueprint $t) {
            if (!Schema::hasColumn('yavas_istekler', 'sorgu_ms')) {
                $t->unsignedInteger('sorgu_ms')->nullable()->after('sorgu_sayisi');
            }
            if (!Schema::hasColumn('yavas_istekler', 'en_yavas_sorgu_ms')) {
                $t->unsignedInteger('en_yavas_sorgu_ms')->nullable()->after('sorgu_ms');
            }
            if (!Schema::hasColumn('yavas_istekler', 'en_yavas_sorgu')) {
                $t->string('en_yavas_sorgu', 1000)->nullable()->after('en_yavas_sorgu_ms');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('yavas_istekler')) return;
        foreach (['en_yavas_sorgu', 'en_yavas_sorgu_ms', 'sorgu_ms'] as $kolon) {
            if (Schema::hasColumn('yavas_istekler', $kolon)) {
                Schema::table('yavas_istekler', function (Blueprint $t) use ($kolon) {
                    $t->dropColumn($kolon);
                });
            }
        }
    }
}
