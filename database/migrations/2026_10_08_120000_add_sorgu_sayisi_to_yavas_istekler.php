<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * yavas_istekler'e istek basina SORGU SAYISI ekle — N+1 teshisi.
 * Hangi endpoint tek istekte kac DB sorgusu atiyor gorunur (mariadb yukunun kok nedeni).
 */
class AddSorguSayisiToYavasIstekler extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('yavas_istekler')) return;
        if (Schema::hasColumn('yavas_istekler', 'sorgu_sayisi')) return;
        Schema::table('yavas_istekler', function (Blueprint $t) {
            $t->unsignedInteger('sorgu_sayisi')->nullable()->after('bellek_mb');
        });
    }

    public function down()
    {
        if (Schema::hasTable('yavas_istekler') && Schema::hasColumn('yavas_istekler', 'sorgu_sayisi')) {
            Schema::table('yavas_istekler', function (Blueprint $t) {
                $t->dropColumn('sorgu_sayisi');
            });
        }
    }
}
