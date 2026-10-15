<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Salon temasi: Flutter uygulamasindaki "Gorunum" (tema rengi) secimi burada saklanir.
 *  - tema_config: Flutter'in gonderdigi ham JSON ({preset, customSeedArgb, brightnessMode})
 *  - tema_seed  : sunucuda cozumlenmis tek seed rengi (#RRGGBB). Mini web sitesi
 *                 (HomeController@salonDetay_anasayfa) bu degerden paletini turetir.
 * Her ikisi de NULL ise salon varsayilan mor temayi (#5C008E) kullanir.
 */
class AddTemaToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'tema_config')) {
                $table->text('tema_config')->nullable()->after('kara_liste_aktif');
            }
            if (!Schema::hasColumn('salonlar', 'tema_seed')) {
                $table->string('tema_seed', 9)->nullable()->after('tema_config');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'tema_seed')) {
                $table->dropColumn('tema_seed');
            }
            if (Schema::hasColumn('salonlar', 'tema_config')) {
                $table->dropColumn('tema_config');
            }
        });
    }
}
