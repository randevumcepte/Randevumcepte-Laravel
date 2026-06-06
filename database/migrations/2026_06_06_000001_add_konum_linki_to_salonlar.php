<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKonumLinkiToSalonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('salonlar', 'konum_linki')) {
            Schema::table('salonlar', function (Blueprint $table) {
                // Google Maps paylasim linki — WhatsApp mesajinda "Konum Ekle" butonu icin
                $table->string('konum_linki', 500)->nullable()->after('adres');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('salonlar', 'konum_linki')) {
            Schema::table('salonlar', function (Blueprint $table) {
                $table->dropColumn('konum_linki');
            });
        }
    }
}
