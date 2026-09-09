<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Faz 3 — grup dersi paket/seans dusumu icin sema.
 *  - ders_oturumlari.hizmet_id + ders_programi_sablonu.hizmet_id: dersi bir hizmete
 *    baglar. Paketler hizmet bazli oldugu icin dogru paketi FIFO secmek + "hakki
 *    olan" kontrolu icin sart. NULL ise dusum yapilmaz (sadece katilim takibi).
 *  - ders_katilimcilar.aps_id: "geldi" isaretlenince olusturulan adisyon_paket_seanslar
 *    satirinin id'si. Gelmedi/geri-alinca bu satir silinip hak iade edilir.
 *  - ders_katilimcilar.hak_dusuldu: cift dusumu engelleyen bayrak.
 * Idempotent.
 */
class AddHizmetToDersPaketDusum extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ders_oturumlari')) {
            Schema::table('ders_oturumlari', function (Blueprint $table) {
                if (!Schema::hasColumn('ders_oturumlari', 'hizmet_id')) {
                    $table->integer('hizmet_id')->nullable()->after('ders_tipi');
                }
            });
        }
        if (Schema::hasTable('ders_programi_sablonu')) {
            Schema::table('ders_programi_sablonu', function (Blueprint $table) {
                if (!Schema::hasColumn('ders_programi_sablonu', 'hizmet_id')) {
                    $table->integer('hizmet_id')->nullable()->after('ders_tipi');
                }
            });
        }
        if (Schema::hasTable('ders_katilimcilar')) {
            Schema::table('ders_katilimcilar', function (Blueprint $table) {
                if (!Schema::hasColumn('ders_katilimcilar', 'aps_id')) {
                    $table->integer('aps_id')->nullable();
                }
                if (!Schema::hasColumn('ders_katilimcilar', 'hak_dusuldu')) {
                    $table->boolean('hak_dusuldu')->default(false);
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ders_oturumlari') && Schema::hasColumn('ders_oturumlari', 'hizmet_id')) {
            Schema::table('ders_oturumlari', function (Blueprint $table) { $table->dropColumn('hizmet_id'); });
        }
        if (Schema::hasTable('ders_programi_sablonu') && Schema::hasColumn('ders_programi_sablonu', 'hizmet_id')) {
            Schema::table('ders_programi_sablonu', function (Blueprint $table) { $table->dropColumn('hizmet_id'); });
        }
        if (Schema::hasTable('ders_katilimcilar')) {
            Schema::table('ders_katilimcilar', function (Blueprint $table) {
                if (Schema::hasColumn('ders_katilimcilar', 'aps_id')) $table->dropColumn('aps_id');
                if (Schema::hasColumn('ders_katilimcilar', 'hak_dusuldu')) $table->dropColumn('hak_dusuldu');
            });
        }
    }
}
