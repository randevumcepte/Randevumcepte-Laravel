<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkBasliklariToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            // Salonun kendi yazabildigi buton basliklari (link farkli platform olabilir)
            if (!Schema::hasColumn('salonlar', 'instagram_baslik')) {
                $table->string('instagram_baslik', 60)->nullable()->after('instagram_linki');
            }
            if (!Schema::hasColumn('salonlar', 'web_baslik')) {
                $table->string('web_baslik', 60)->nullable()->after('web_linki');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach (['instagram_baslik', 'web_baslik'] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
