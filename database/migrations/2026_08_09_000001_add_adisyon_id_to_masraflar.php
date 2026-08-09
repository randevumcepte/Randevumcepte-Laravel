<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdisyonIdToMasraflar extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('masraflar')) return;
        Schema::table('masraflar', function (Blueprint $table) {
            if (!Schema::hasColumn('masraflar', 'adisyon_id')) {
                $table->unsignedInteger('adisyon_id')->nullable()->after('salon_id');
                $table->index('adisyon_id');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('masraflar')) return;
        Schema::table('masraflar', function (Blueprint $table) {
            if (Schema::hasColumn('masraflar', 'adisyon_id')) {
                $table->dropIndex(['adisyon_id']);
                $table->dropColumn('adisyon_id');
            }
        });
    }
}
