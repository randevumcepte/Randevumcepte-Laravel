<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Yavas istek kaydi — CPU/performans teshisi icin.
 * LogRequestPerformance middleware'i yalnizca ESIK USTU (yavas veya cok bellek)
 * istekleri buraya yazar; boylece "hangi istek/isletme/controller yuku yapiyor"
 * sorgulanabilir ve CPU uyarisi (watchdog) son yavas istekleri mesaja ekleyebilir.
 */
class CreateYavasIsteklerTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('yavas_istekler')) return;
        Schema::create('yavas_istekler', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->timestamp('created_at')->nullable()->index();
            $t->string('uri', 500)->nullable();
            $t->string('method', 10)->nullable();
            $t->string('aksiyon', 191)->nullable();      // Controller@action
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('salon_id')->nullable()->index();
            $t->unsignedInteger('sure_ms')->nullable();
            $t->unsignedInteger('bellek_mb')->nullable();
            $t->integer('pid')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('yavas_istekler');
    }
}
