<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImzaMetadataToArsiv extends Migration
{
    public function up()
    {
        Schema::table('arsiv', function (Blueprint $table) {
            if (!Schema::hasColumn('arsiv', 'imza_ip')) {
                $table->string('imza_ip', 45)->nullable()->after('cevaplar_json');
            }
            if (!Schema::hasColumn('arsiv', 'imza_cihaz')) {
                $table->string('imza_cihaz', 250)->nullable()->after('imza_ip');
            }
            if (!Schema::hasColumn('arsiv', 'imza_zaman')) {
                $table->timestamp('imza_zaman')->nullable()->after('imza_cihaz');
            }
        });
    }

    public function down()
    {
        Schema::table('arsiv', function (Blueprint $table) {
            $cols = [];
            foreach (['imza_ip', 'imza_cihaz', 'imza_zaman'] as $c) {
                if (Schema::hasColumn('arsiv', $c)) $cols[] = $c;
            }
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
}
