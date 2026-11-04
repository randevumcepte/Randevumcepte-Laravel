<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * v2 SMS Yonetimi outbox'i ('sms_toplu') alici telefonlarini payload'da tasir: TEXT (64 KB) ~4.500
 * numarada dolar ve strict olmayan baglantida JSON sessizce kesilir → MEDIUMTEXT (16 MB).
 * Kolon tipini ->change() yerine SQL ile: doctrine/dbal gerektirmesin.
 */
class V2BildirimKuyruguPayloadMediumtext extends Migration
{
    public function up()
    {
        if (Schema::hasTable('v2_bildirim_kuyrugu')) {
            DB::statement('ALTER TABLE v2_bildirim_kuyrugu MODIFY payload MEDIUMTEXT NULL');
        }
    }

    public function down()
    {
        if (Schema::hasTable('v2_bildirim_kuyrugu')) {
            DB::statement('ALTER TABLE v2_bildirim_kuyrugu MODIFY payload TEXT NULL');
        }
    }
}
