<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cark dilimlerine indirim tipi (yuzde|tutar) ekler.
 * Salon sahibi hizmet/urun/paket indirim diliminin degerinin
 * % oran mi yoksa sabit ₺ tutar mi olacagini kendisi secebilsin diye.
 *
 * default 'yuzde' -> mevcut dilimler eskisi gibi % calismaya devam eder.
 * Idempotent: kolon varsa no-op.
 */
class AddIndirimTipiToCarkifelekDilimleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('carkifelek_dilimleri')) return;

        Schema::table('carkifelek_dilimleri', function (Blueprint $table) {
            if (!Schema::hasColumn('carkifelek_dilimleri', 'indirim_tipi')) {
                $table->string('indirim_tipi', 10)->default('yuzde')->after('deger'); // yuzde | tutar
            }
        });
    }

    public function down()
    {
        Schema::table('carkifelek_dilimleri', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_dilimleri', 'indirim_tipi')) $table->dropColumn('indirim_tipi');
        });
    }
}
