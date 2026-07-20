<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * seans_cihaz_verileri:
 *   Lazer epilasyon (ve benzeri cihazli) seanslarda uygulamayi yapan
 *   personelin girdigi cihaz parametreleri. Bir seansta birden fazla
 *   bolge islenebildigi icin seansa hasMany: her bolge ayri satir.
 *
 *   seans_id -> adisyon_paket_seanslar.id
 *   Parametreler (enerji/hiz/ms/atis_sayisi) serbest metin: cihazlar
 *   ondalik/birimli deger gosterebiliyor, dogrulama zorlamiyoruz.
 */
class CreateSeansCihazVerileri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('seans_cihaz_verileri')) {
            Schema::create('seans_cihaz_verileri', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('seans_id')->index();      // adisyon_paket_seanslar.id
                $table->unsignedInteger('salon_id')->nullable()->index();
                $table->unsignedInteger('personel_id')->nullable(); // uygulamayi yapan
                $table->string('uygulama_bolgesi', 150)->nullable();
                $table->string('enerji', 50)->nullable();           // Jul
                $table->string('hiz', 50)->nullable();
                $table->string('ms', 50)->nullable();               // milisaniye
                $table->string('atis_sayisi', 50)->nullable();
                $table->date('tarih')->nullable();
                $table->text('notlar')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('seans_cihaz_verileri');
    }
}
