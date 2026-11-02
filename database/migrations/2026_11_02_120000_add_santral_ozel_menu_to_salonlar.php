<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Santral ozel karsilama menusu (isletme bazli DTMF IVR).
 * Aktifse gelen cagri from-trunk-custom'da standart ana menu yerine
 * "X icin biri, Y icin ikiyi tuslayiniz" menusune gider; her tus bir
 * kuyruga/dahiliye baglar, tuslanmazsa standart operator akisi devam eder.
 * santral_ozel_menu = JSON [{"tus":"1","etiket":"Randevu","hedef":"10013"}, ...]
 * Idempotent.
 */
class AddSantralOzelMenuToSalonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salonlar')) {
            return;
        }
        if (!Schema::hasColumn('salonlar', 'santral_ozel_menu_aktif')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->boolean('santral_ozel_menu_aktif')->default(0);
            });
        }
        if (!Schema::hasColumn('salonlar', 'santral_ozel_menu')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->text('santral_ozel_menu')->nullable();
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('salonlar')) {
            return;
        }
        foreach (['santral_ozel_menu_aktif', 'santral_ozel_menu'] as $kolon) {
            if (Schema::hasColumn('salonlar', $kolon)) {
                Schema::table('salonlar', function (Blueprint $table) use ($kolon) {
                    $table->dropColumn($kolon);
                });
            }
        }
    }
}
