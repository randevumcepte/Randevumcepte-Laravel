<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitlesToSmsPaketleri extends Migration
{
    public function up()
    {
        Schema::table('sms_paketleri', function (Blueprint $table) {
            if (!Schema::hasColumn('sms_paketleri', 'paket_adi')) {
                $table->string('paket_adi', 255)->nullable()->after('sms_adet');
            }
            if (!Schema::hasColumn('sms_paketleri', 'alt_baslik')) {
                $table->string('alt_baslik', 255)->nullable()->after('paket_adi');
            }
        });
    }

    public function down()
    {
        Schema::table('sms_paketleri', function (Blueprint $table) {
            if (Schema::hasColumn('sms_paketleri', 'paket_adi'))  $table->dropColumn('paket_adi');
            if (Schema::hasColumn('sms_paketleri', 'alt_baslik')) $table->dropColumn('alt_baslik');
        });
    }
}
