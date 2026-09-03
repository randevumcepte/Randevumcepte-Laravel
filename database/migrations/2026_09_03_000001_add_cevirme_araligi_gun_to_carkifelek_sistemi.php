<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCevirmeAraligiGunToCarkifelekSistemi extends Migration
{
    public function up()
    {
        Schema::table('carkifelek_sistemi', function (Blueprint $table) {
            // Çevirme aralığı (takvim günü): 1 = her gün çevrilebilir (varsayılan),
            // 7 = son çevirmeden 7 takvim günü sonra tekrar hak açılır.
            if (!Schema::hasColumn('carkifelek_sistemi', 'cevirme_araligi_gun')) {
                $table->unsignedSmallInteger('cevirme_araligi_gun')->default(1)->after('kullanim_kurallari');
            }
        });
    }

    public function down()
    {
        Schema::table('carkifelek_sistemi', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_sistemi', 'cevirme_araligi_gun')) {
                $table->dropColumn('cevirme_araligi_gun');
            }
        });
    }
}
