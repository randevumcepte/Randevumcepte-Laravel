<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsDogrulamaKodlariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sms_dogrulama_kodlari')) return;

        Schema::create('sms_dogrulama_kodlari', function (Blueprint $table) {
            $table->increments('id');
            $table->string('telefon', 20);
            $table->string('kod', 6);
            $table->string('ip', 45)->nullable();
            $table->string('amac', 50)->default('cark_kayit'); // cark_kayit | sifremiunuttum vb.
            $table->timestamp('son_gecerlilik');
            $table->tinyInteger('dogrulandi')->default(0);
            $table->timestamps();

            $table->index(['telefon', 'amac']);
            $table->index('son_gecerlilik');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_dogrulama_kodlari');
    }
}
