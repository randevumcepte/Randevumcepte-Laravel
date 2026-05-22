<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * randevu_hizmetler tablosuna 'dusum_miktari' (INT, nullable):
 *   Randevu olustururken/duzenlerken her hizmet icin salon ne kadar
 *   dusulecegini belirtebilir (orn 2 seans veya 15 dk). NULL ise "Geldi"
 *   popup'unda default 1 gelir; doluysa o deger default olur.
 *   Lazer (seans) ve solaryum (dakika) icin ayni alan kullanilir, anlam
 *   paketin tipine gore belli olur.
 */
class AddDusumMiktariToRandevuHizmetler extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('randevu_hizmetler', 'dusum_miktari')) {
            Schema::table('randevu_hizmetler', function (Blueprint $table) {
                $table->integer('dusum_miktari')->nullable()->after('paket_dakika');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('randevu_hizmetler', 'dusum_miktari')) {
            Schema::table('randevu_hizmetler', function (Blueprint $table) {
                $table->dropColumn('dusum_miktari');
            });
        }
    }
}
