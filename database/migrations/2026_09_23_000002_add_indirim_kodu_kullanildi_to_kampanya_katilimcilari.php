<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * kampanya_katilimcilari'ya indirim kodu KULLANIM takibi ekler.
 *
 * Kampanya aramasiyla musteriye SMS ile gonderilen indirim_kodu, tahsilat ekranindan
 * girilip dogrulaninca burada isaretlenir (tekrar kullanim engellenir). eczane24'teki
 * indirimKoduKullan akisinin RandevuMcepte karsiligi.
 */
class AddIndirimKoduKullanildiToKampanyaKatilimcilari extends Migration
{
    public function up()
    {
        Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
            if (!Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_kullanildi')) {
                $table->tinyInteger('indirim_kodu_kullanildi')->nullable()->default(null);
            }
            if (!Schema::hasColumn('kampanya_katilimcilari', 'indirim_kodu_kullanim_tarihi')) {
                $table->timestamp('indirim_kodu_kullanim_tarihi')->nullable()->default(null);
            }
        });
    }

    public function down()
    {
        Schema::table('kampanya_katilimcilari', function (Blueprint $table) {
            foreach (['indirim_kodu_kullanildi', 'indirim_kodu_kullanim_tarihi'] as $c) {
                if (Schema::hasColumn('kampanya_katilimcilari', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
}
