<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * kampanya_yonetimi'ye HEDEF KITLE secimini saklayan kolonlar. Boylece kampanya
 * DUZENLENIRKEN sihirbaza kayitli hedef kitle (preset/grup/cinsiyet) SECILI gelir.
 * (Eskiden kampanya sadece cozulmus musteri_turu etiketini tutuyordu; filtreyi degil.)
 */
class AddHedefColsToKampanyaYonetimi extends Migration
{
    public function up()
    {
        Schema::table('kampanya_yonetimi', function (Blueprint $table) {
            if (!Schema::hasColumn('kampanya_yonetimi', 'hedef_filtre'))
                $table->string('hedef_filtre')->nullable()->default(null);   // gelenGelmeyenMusteri (6/7/8/1...)
            if (!Schema::hasColumn('kampanya_yonetimi', 'hedef_grup'))
                $table->string('hedef_grup')->nullable()->default(null);     // musteriGruplari (haricigrup-X)
            if (!Schema::hasColumn('kampanya_yonetimi', 'hedef_cinsiyet'))
                $table->string('hedef_cinsiyet')->nullable()->default(null); // katilimciTuru (erkekler/kadinlar/'')
        });
    }

    public function down()
    {
        Schema::table('kampanya_yonetimi', function (Blueprint $table) {
            foreach (['hedef_filtre', 'hedef_grup', 'hedef_cinsiyet'] as $c) {
                if (Schema::hasColumn('kampanya_yonetimi', $c)) $table->dropColumn($c);
            }
        });
    }
}
