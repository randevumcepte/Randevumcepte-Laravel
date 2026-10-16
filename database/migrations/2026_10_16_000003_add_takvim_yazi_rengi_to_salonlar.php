<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Takvim YAZI (font) rengi. Acikken randevu takviminde (web + mobil) kolon
 * basliklari ve randevu/etkinlik yazilari bu renge boyanir. Kapaliyken eski
 * davranis: web kolon basligi #fff, mobil ise zemin parlakligina gore otomatik
 * siyah/beyaz kontrast. Idempotent.
 *   - takvim_yazi_rengi_aktif : ac/kapa (varsayilan 0)
 *   - takvim_yazi_rengi       : hex (#RRGGBB)
 */
class AddTakvimYaziRengiToSalonlar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salonlar') && !Schema::hasColumn('salonlar', 'takvim_yazi_rengi_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->boolean('takvim_yazi_rengi_aktif')->default(0);
                $table->string('takvim_yazi_rengi', 10)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('salonlar') && Schema::hasColumn('salonlar', 'takvim_yazi_rengi_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn(['takvim_yazi_rengi_aktif', 'takvim_yazi_rengi']);
            });
        }
    }
}
