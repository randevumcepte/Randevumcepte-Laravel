<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ikram Hizmet kampanya gorseli: isletmenin yukledigi foto (/public/kampanya_gorselleri/...).
 * NULL = uygulama hizmet adina gore hazir gorsel secer.
 */
class AddGorselToDamgaKartiKurallari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('damga_karti_kurallari')) return;
        Schema::table('damga_karti_kurallari', function (Blueprint $t) {
            if (!Schema::hasColumn('damga_karti_kurallari', 'gorsel')) {
                $t->string('gorsel', 255)->nullable()->after('aktif');
            }
        });
    }

    public function down()
    {
        Schema::table('damga_karti_kurallari', function (Blueprint $t) {
            $t->dropColumn('gorsel');
        });
    }
}
