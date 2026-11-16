<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Mobil softphone gelen cagri push token'lari (SoftphoneApiController).
 * iOS: PushKit VoIP token (bundle = APNs topic kokü), Android: FCM token.
 * Santral dahiliyi cagirmadan once bu tablodan hedefleri alir.
 */
class CreateSoftphoneVoipTokenlarTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('softphone_voip_tokenlar')) return;
        Schema::create('softphone_voip_tokenlar', function (Blueprint $t) {
            $t->increments('id');
            $t->string('dahili', 20)->index();
            $t->integer('salon_id')->nullable();
            $t->integer('personel_id')->nullable();
            $t->string('token', 200)->unique();
            $t->string('bundle', 150)->default('');
            $t->string('platform', 10)->default('ios');
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('softphone_voip_tokenlar');
    }
}
