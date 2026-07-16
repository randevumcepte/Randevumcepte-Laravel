<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstagramWebLinkiToSalonlar extends Migration
{
    public function up()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            // WhatsApp mesajina tek tikla eklenen hazir baglantilar (konum_linki ile ayni mantik)
            if (!Schema::hasColumn('salonlar', 'instagram_linki')) {
                $table->string('instagram_linki', 500)->nullable()->after('konum_linki');
            }
            if (!Schema::hasColumn('salonlar', 'web_linki')) {
                $table->string('web_linki', 500)->nullable()->after('instagram_linki');
            }
        });
    }

    public function down()
    {
        Schema::table('salonlar', function (Blueprint $table) {
            foreach (['instagram_linki', 'web_linki'] as $col) {
                if (Schema::hasColumn('salonlar', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
