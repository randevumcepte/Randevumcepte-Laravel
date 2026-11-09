<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Hata izleme. sistem_hatalari: ayni hata tek satir (parmak izi), sistem_hata_kayitlari: her olusum
 * (hangi isletme, hangi kullanici, ne zaman). Kaynaklar: istisnalar, 5xx/419/429 cevaplar, Log::error,
 * tarayici JS hatalari ve zaman asimlari. Kayitlar 14 gun saklanir (performans:nobetci temizler).
 */
class CreateSistemHatalariTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sistem_hatalari')) {
            Schema::create('sistem_hatalari', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->char('parmak_izi', 32)->unique();
                $t->string('tur', 20)->index();          // istisna | http | log | js | zaman_asimi | komut
                $t->string('baslik', 255)->nullable();   // sinif / HTTP durumu
                $t->string('mesaj', 1000)->nullable();
                $t->string('konum', 500)->nullable();    // dosya:satir
                $t->string('uc', 255)->nullable();       // Controller@action / yol / komut
                $t->timestamp('ilk_gorulme')->nullable();
                $t->timestamp('son_gorulme')->nullable()->index();
                $t->unsignedInteger('adet')->default(0);
                $t->text('ornek')->nullable();           // trace / yigin
            });
        }
        if (!Schema::hasTable('sistem_hata_kayitlari')) {
            Schema::create('sistem_hata_kayitlari', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('hata_id')->index();
                $t->timestamp('created_at')->nullable()->index();
                $t->unsignedBigInteger('salon_id')->nullable();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('uri', 500)->nullable();
                $t->unsignedSmallInteger('durum_kodu')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sistem_hata_kayitlari');
        Schema::dropIfExists('sistem_hatalari');
    }
}
