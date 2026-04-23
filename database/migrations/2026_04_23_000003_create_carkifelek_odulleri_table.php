<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarkifelekOdulleriTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('carkifelek_odulleri')) return;

        Schema::create('carkifelek_odulleri', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('log_id')->nullable();
            $table->unsignedInteger('salon_id');
            $table->unsignedInteger('user_id');
            $table->string('kod', 12)->unique();            // kullanıcıya gösterilen kupon kodu
            $table->string('tip', 50);                       // hizmet_indirimi | urun_indirimi | puan
            $table->decimal('deger', 10, 2);                 // indirim % veya puan miktarı
            $table->string('baslik', 150)->nullable();       // "%20 Hizmet İnd."
            $table->tinyInteger('kullanildi')->default(0);   // 0=hayır, 1=evet
            $table->timestamp('kullanim_tarihi')->nullable();
            $table->date('gecerlilik_tarihi')->nullable();   // boş = süresiz
            $table->timestamps();

            $table->index(['salon_id', 'user_id']);
            $table->index('kullanildi');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carkifelek_odulleri');
    }
}
