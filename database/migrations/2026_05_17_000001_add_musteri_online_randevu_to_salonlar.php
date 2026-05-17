<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMusteriOnlineRandevuToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'musteri_online_randevu_aktif')) {
                $table->boolean('musteri_online_randevu_aktif')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'musteri_online_randevu_aktif')) {
                $table->dropColumn('musteri_online_randevu_aktif');
            }
        });
    }
}
