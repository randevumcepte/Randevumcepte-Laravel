<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reklama "uygulama acilisinda tam ekran popup goster" secenegi.
 * true ise musteri uygulamayi actiginda reklam ekrani kaplayan bir popup
 * olarak gosterilir (interstitial). Idempotent.
 */
class AddTamEkranToBildirimReklamlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bildirim_reklamlari')) return;
        if (Schema::hasColumn('bildirim_reklamlari', 'tam_ekran')) return;

        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            $table->boolean('tam_ekran')->default(false)->after('kanal_inapp');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('bildirim_reklamlari', 'tam_ekran')) return;
        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            $table->dropColumn('tam_ekran');
        });
    }
}
