<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Memnuniyet anketi davetinin once hangi kanaldan gidecegi (isletmeye ozel).
 * whatsapp = WA-first, SMS yedek (eski davranis) | sms = SMS-first, WA yedek.
 */
class AddAnketGonderimKanaliToSalonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('salonlar', 'anket_gonderim_kanali')) {
            Schema::table('salonlar', function (Blueprint $t) {
                $t->string('anket_gonderim_kanali', 10)->default('whatsapp');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('salonlar', 'anket_gonderim_kanali')) {
            Schema::table('salonlar', function (Blueprint $t) {
                $t->dropColumn('anket_gonderim_kanali');
            });
        }
    }
}
