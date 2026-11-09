<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Performans nobetcisinin (performans:nobetci) esik asildiginda aldigi anlik goruntuler:
 * o an calisan sorgular, bellek, yuk, son yavas istekler. "Sisme anini yakala" elle gerekmesin.
 */
class CreatePerformansAnliklariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('performans_anliklari')) return;
        Schema::create('performans_anliklari', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->timestamp('created_at')->nullable()->index();
            $t->string('nedenler', 500)->nullable();
            $t->decimal('load1', 6, 2)->nullable();
            $t->unsignedInteger('bos_bellek_mb')->nullable();
            $t->unsignedInteger('swap_kullanilan_mb')->nullable();
            $t->unsignedInteger('uzun_sorgu_sayisi')->nullable();
            $t->unsignedInteger('en_uzun_sorgu_sn')->nullable();
            $t->unsignedInteger('yavas_istek_sayisi')->nullable();
            $t->unsignedInteger('zaman_asimi_sayisi')->nullable();
            $t->boolean('alarm_gonderildi')->default(0);
            $t->longText('detay')->nullable(); // JSON: sorgular, surecler, yavas istekler
        });
    }

    public function down()
    {
        Schema::dropIfExists('performans_anliklari');
    }
}
