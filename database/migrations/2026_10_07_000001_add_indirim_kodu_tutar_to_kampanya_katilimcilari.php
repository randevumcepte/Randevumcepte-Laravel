<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Kupon indirim kodu KULLANILINCA indirimin adisyon kalemine yazilan tutarini ve
 * bu tutarin tahsilat harici-indirim akisinda bir kez dusulup dusulmedigini tutar.
 *
 * - indirim_kodu_tutar: kupon kullaniminda adisyon kalemlerine yazilan gercek
 *   (bakiyeye gore kirpilmis) indirim tutari. 0/null => yazilmadi (xalyode vb.).
 * - indirim_kodu_tahsilata_dusuldu: frontend ayni tutari harici indirim olarak tekrar
 *   gonderince cift uygulanmasini engellemek icin guard; tahsilat akisinda 1 kez
 *   dusunce 1 yapilir (sonraki gercek manuel indirimler etkilenmez).
 */
class AddIndirimKoduTutarToKampanyaKatilimcilari extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_tutar')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->decimal('indirim_kodu_tutar', 12, 2)->nullable()->default(null);
            });
        }
        if (!Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_tahsilata_dusuldu')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->tinyInteger('indirim_kodu_tahsilata_dusuldu')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_tutar')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->dropColumn('indirim_kodu_tutar');
            });
        }
        if (Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_tahsilata_dusuldu')) {
            Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
                $table->dropColumn('indirim_kodu_tahsilata_dusuldu');
            });
        }
    }
}
