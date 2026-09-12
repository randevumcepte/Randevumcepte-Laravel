<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Studyo modu: odeme alindiginda tam tarih+saat. tamOde() doldurur, odemeGeriAl() bosaltir.
 * Satis takibinde tutar yerine bu gosterilir. Idempotent.
 */
class AddOdendiTarihiToAdisyonlar extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('adisyonlar', 'odendi_tarihi')) {
            Schema::table('adisyonlar', function (Blueprint $table) {
                $table->dateTime('odendi_tarihi')->nullable()->default(null)->after('odendi');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('adisyonlar', 'odendi_tarihi')) {
            Schema::table('adisyonlar', function (Blueprint $table) {
                $table->dropColumn('odendi_tarihi');
            });
        }
    }
}
