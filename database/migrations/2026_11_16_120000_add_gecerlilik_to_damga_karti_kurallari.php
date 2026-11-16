<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ikram Hizmet (damga karti) gecerlilik suresi: ikram kazanildigi gunden itibaren
 * gecerlilik_deger gun|ay gecerli. NULL = suresiz (eski davranis).
 */
class AddGecerlilikToDamgaKartiKurallari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('damga_karti_kurallari')) return;
        Schema::table('damga_karti_kurallari', function (Blueprint $t) {
            if (!Schema::hasColumn('damga_karti_kurallari', 'gecerlilik_deger')) {
                $t->unsignedSmallInteger('gecerlilik_deger')->nullable()->after('ucretsiz_adet');
            }
            if (!Schema::hasColumn('damga_karti_kurallari', 'gecerlilik_birim')) {
                $t->string('gecerlilik_birim', 3)->default('ay')->after('gecerlilik_deger');
            }
        });
    }

    public function down()
    {
        Schema::table('damga_karti_kurallari', function (Blueprint $t) {
            $t->dropColumn(['gecerlilik_deger', 'gecerlilik_birim']);
        });
    }
}
