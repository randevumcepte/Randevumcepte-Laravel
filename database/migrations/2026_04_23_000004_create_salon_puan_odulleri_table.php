<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalonPuanOdulleriTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salon_puan_odulleri')) return;

        Schema::create('salon_puan_odulleri', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id');
            $table->integer('puan_esigi');                   // gerekli puan
            $table->string('baslik', 150);                   // "Ücretsiz Saç Bakımı"
            $table->string('aciklama', 300)->nullable();
            $table->string('tip', 50);                       // hizmet_indirimi | urun_indirimi | hediye
            $table->decimal('deger', 10, 2)->nullable();     // indirim yüzdesi (hediye ise null)
            $table->tinyInteger('aktif')->default(1);
            $table->integer('sira')->default(0);
            $table->timestamps();

            $table->index(['salon_id', 'aktif']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salon_puan_odulleri');
    }
}
