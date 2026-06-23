<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdisyonIdToCarkifelekOdulleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('carkifelek_odulleri')) return;
        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            if (!Schema::hasColumn('carkifelek_odulleri', 'adisyon_id')) {
                $table->unsignedInteger('adisyon_id')->nullable()->after('user_id');
                $table->index('adisyon_id');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('carkifelek_odulleri')) return;
        Schema::table('carkifelek_odulleri', function (Blueprint $table) {
            if (Schema::hasColumn('carkifelek_odulleri', 'adisyon_id')) {
                $table->dropIndex(['adisyon_id']);
                $table->dropColumn('adisyon_id');
            }
        });
    }
}
