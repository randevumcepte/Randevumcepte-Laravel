<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirebaseProfileToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (!Schema::hasColumn('salonlar', 'firebase_profile')) {
                // config/firebase_projects.php anahtarlarindan biri.
                // Bos veya tanimsizsa 'default' kullanilir.
                $table->string('firebase_profile', 64)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            if (Schema::hasColumn('salonlar', 'firebase_profile')) {
                $table->dropColumn('firebase_profile');
            }
        });
    }
}
