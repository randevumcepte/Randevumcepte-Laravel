<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGecmisRandevulariGizleToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'gecmis_randevulari_gizle')) {
                $table->boolean('gecmis_randevulari_gizle')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'gecmis_randevulari_gizle')) {
                $table->dropColumn('gecmis_randevulari_gizle');
            }
        });
    }
}
