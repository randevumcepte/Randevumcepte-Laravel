<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDogumGunuMesajLoglari extends Migration
{
    public function up()
    {
        Schema::create('dogum_gunu_mesaj_loglari', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('salon_id');
            $table->unsignedInteger('user_id');
            $table->string('kanal', 20)->default('sms');
            $table->text('mesaj')->nullable();
            $table->string('detay', 200)->nullable();
            $table->timestamp('gonderim_tarihi')->nullable();
            $table->timestamps();

            $table->index(['salon_id', 'gonderim_tarihi']);
            $table->index(['salon_id', 'user_id', 'gonderim_tarihi']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dogum_gunu_mesaj_loglari');
    }
}
