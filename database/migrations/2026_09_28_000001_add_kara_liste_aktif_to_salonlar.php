<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kara liste modulu salon-bazli ac/kapat ayari.
 *  - 1 (default): kara liste calisir, musteri detayda buton gorunur, suzgec aktif
 *  - 0: buton gizlenir + suzgec devre disi + ayar kapatilirken mevcut
 *    musteri_portfoy.kara_liste=1 kayitlar 0'a cekilir (ApiController@smsYonetimAyarKaydet)
 */
class AddKaraListeAktifToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'kara_liste_aktif')) {
                $table->tinyInteger('kara_liste_aktif')->default(1)->after('cakisma_uyarisi_aktif');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'kara_liste_aktif')) {
                $table->dropColumn('kara_liste_aktif');
            }
        });
    }
}
