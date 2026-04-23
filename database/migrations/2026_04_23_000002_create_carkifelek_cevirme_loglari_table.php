<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarkifelekCevirmeLoglariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('carkifelek_cevirme_loglari')) return;

        Schema::create('carkifelek_cevirme_loglari', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cark_id');
            $table->unsignedInteger('salon_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('randevu_id')->nullable();
            $table->unsignedInteger('dilim_id')->nullable();
            $table->string('tip', 50)->default('bos');
            $table->decimal('deger', 10, 2)->nullable();
            $table->string('dilim_ismi', 150)->nullable();
            $table->timestamps();

            $table->index(['salon_id', 'user_id']);
            $table->index('randevu_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carkifelek_cevirme_loglari');
    }
}
