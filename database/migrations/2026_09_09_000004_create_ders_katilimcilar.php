<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ders oturumuna yazili musteriler (grup dersi katilimci listesi).
 *
 * Bir oturuma birden cok musteri baglanir; kapasite kontrolu bu tablodaki
 * (durum IN rezerve/geldi/gelmedi) satir sayisi ile oturum.kapasite karsilastirilarak
 * yapilir. iptal edilen katilim kontenjani serbest birakir.
 *
 * adisyon_paket_id: ilerde paket/ders hakki (AdisyonPaketSeanslar) baglamak icin;
 * simdilik nullable birakildi.
 * Idempotent.
 */
class CreateDersKatilimcilar extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ders_katilimcilar')) {
            return;
        }
        Schema::create('ders_katilimcilar', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oturum_id')->index();
            $table->integer('user_id')->index();               // musteri (users.id)
            $table->integer('salon_id')->nullable();
            $table->string('durum')->default('rezerve');       // rezerve | geldi | gelmedi | iptal | bekleme
            $table->integer('adisyon_paket_id')->nullable();
            $table->integer('ekleyen_personel_id')->nullable();
            $table->text('not')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ders_katilimcilar');
    }
}
