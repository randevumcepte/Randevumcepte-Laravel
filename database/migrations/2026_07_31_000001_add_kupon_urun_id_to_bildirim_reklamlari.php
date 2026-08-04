<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bildirim reklami kuponunda hizmet yaninda urun de secilebilsin diye
 * kupon_urun_id kolonu ekler. null = tum urunler / urun aksiyonu yok.
 *
 * Idempotent.
 */
class AddKuponUrunIdToBildirimReklamlari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bildirim_reklamlari')) return;
        if (Schema::hasColumn('bildirim_reklamlari', 'kupon_urun_id')) return;

        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            $table->unsignedInteger('kupon_urun_id')->nullable()->after('kupon_hizmet_id');
            $table->index('kupon_urun_id', 'br_kupon_urun_idx');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('bildirim_reklamlari', 'kupon_urun_id')) return;
        Schema::table('bildirim_reklamlari', function (Blueprint $table) {
            $table->dropIndex('br_kupon_urun_idx');
            $table->dropColumn('kupon_urun_id');
        });
    }
}
