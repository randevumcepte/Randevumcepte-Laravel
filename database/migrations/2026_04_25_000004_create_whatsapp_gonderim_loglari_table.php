<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappGonderimLoglariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('whatsapp_gonderim_loglari')) {
            return;
        }
        Schema::create('whatsapp_gonderim_loglari', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('salon_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('randevu_id')->nullable();
            $table->string('telefon', 20);
            $table->text('mesaj');
            $table->tinyInteger('durum')->default(0)->comment('0=bekliyor, 1=gonderildi, 2=basarisiz, 3=sms_fallback');
            $table->string('hata', 150)->nullable();
            $table->string('mesaj_id', 120)->nullable();
            $table->timestamp('gonderim_tarihi')->nullable();
            $table->timestamps();

            $table->index(['salon_id', 'created_at']);
            $table->index('randevu_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_gonderim_loglari');
    }
}
