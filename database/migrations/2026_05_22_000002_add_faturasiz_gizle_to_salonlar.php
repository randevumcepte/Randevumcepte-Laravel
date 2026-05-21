<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFaturasizGizleToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'faturasiz_gizle')) {
                $table->boolean('faturasiz_gizle')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'faturasiz_gizle')) {
                $table->dropColumn('faturasiz_gizle');
            }
        });
    }
}
