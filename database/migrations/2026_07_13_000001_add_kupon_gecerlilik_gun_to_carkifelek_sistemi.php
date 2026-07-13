<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKuponGecerlilikGunToCarkifelekSistemi extends Migration
{
    public function up()
    {
        Schema::table('carkifelek_sistemi', function (Blueprint $table) {
            // 0 veya null = süresiz kupon; pozitif değer = kupon oluşturulduktan itibaren X gün geçerli.
            if (!Schema::hasColumn('carkifelek_sistemi', 'kupon_cark_gecerlilik_gun')) {
                $table->unsignedSmallInteger('kupon_cark_gecerlilik_gun')->nullable()->after('kullanim_kurallari');
            }
            if (!Schema::hasColumn('carkifelek_sistemi', 'kupon_puan_gecerlilik_gun')) {
                $table->unsignedSmallInteger('kupon_puan_gecerlilik_gun')->nullable()->after('kupon_cark_gecerlilik_gun');
            }
        });
    }

    public function down()
    {
        Schema::table('carkifelek_sistemi', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_sistemi', 'kupon_puan_gecerlilik_gun')) {
                $table->dropColumn('kupon_puan_gecerlilik_gun');
            }
            if (Schema::hasColumn('carkifelek_sistemi', 'kupon_cark_gecerlilik_gun')) {
                $table->dropColumn('kupon_cark_gecerlilik_gun');
            }
        });
    }
}
