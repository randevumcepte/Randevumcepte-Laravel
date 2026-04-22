<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipDegerToCarkifelekDilimleri extends Migration
{
    public function up()
    {
        Schema::table('carkifelek_dilimleri', function (Blueprint $table) {
            if (!Schema::hasColumn('carkifelek_dilimleri', 'tip')) {
                // puan | hizmet_indirimi | urun_indirimi | tekrar_dene | bos
                $table->string('tip', 50)->default('bos')->after('renk_kodu');
            }
            if (!Schema::hasColumn('carkifelek_dilimleri', 'deger')) {
                // puan → kaç puan, indirim → yüzde kaç (null = yok)
                $table->decimal('deger', 10, 2)->nullable()->after('tip');
            }
        });
    }

    public function down()
    {
        Schema::table('carkifelek_dilimleri', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('carkifelek_dilimleri', 'tip'))   $cols[] = 'tip';
            if (Schema::hasColumn('carkifelek_dilimleri', 'deger')) $cols[] = 'deger';
            if ($cols) $table->dropColumn($cols);
        });
    }
}
