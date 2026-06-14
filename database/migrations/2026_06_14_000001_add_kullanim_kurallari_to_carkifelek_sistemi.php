<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKullanimKurallariToCarkifelekSistemi extends Migration
{
    public function up()
    {
        Schema::table('carkifelek_sistemi', function (Blueprint $table) {
            if (!Schema::hasColumn('carkifelek_sistemi', 'kullanim_kurallari')) {
                // Salon sahibinin müşteriye gösterdiği çark kullanım kuralları / uyarı metni
                $table->text('kullanim_kurallari')->nullable()->after('aktifmi');
            }
        });
    }

    public function down()
    {
        Schema::table('carkifelek_sistemi', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_sistemi', 'kullanim_kurallari')) {
                $table->dropColumn('kullanim_kurallari');
            }
        });
    }
}
