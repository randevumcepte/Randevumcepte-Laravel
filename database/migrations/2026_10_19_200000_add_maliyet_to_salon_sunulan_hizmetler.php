<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaliyetToSalonSunulanHizmetler extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('salon_sunulan_hizmetler', 'maliyet')) {
            Schema::table('salon_sunulan_hizmetler', function (Blueprint $table) {
                $table->decimal('maliyet', 12, 2)->default(0)->after('son_fiyat');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('salon_sunulan_hizmetler', 'maliyet')) {
            Schema::table('salon_sunulan_hizmetler', function (Blueprint $table) {
                $table->dropColumn('maliyet');
            });
        }
    }
}
