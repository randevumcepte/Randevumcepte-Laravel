<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uye/musteri vucut olcumleri (Pilates/studyo modu). Tarihli kayit — her olcumde
 * yeni satir; uye gelisim surecini (gecmis + trend) gorur.
 * VKI (BMI) = kilo / (boy_m)^2; kayitta saklanir (gecmis tutarli olsun).
 * Idempotent.
 */
class CreateMusteriOlcumleri extends Migration
{
    public function up()
    {
        if (Schema::hasTable('musteri_olcumleri')) return;
        Schema::create('musteri_olcumleri', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salon_id')->index();
            $table->integer('user_id')->index();            // musteri (users.id)
            $table->date('olcum_tarihi')->index();
            $table->decimal('boy', 5, 1)->nullable();       // cm
            $table->decimal('kilo', 5, 1)->nullable();      // kg
            $table->integer('yas')->nullable();
            $table->decimal('vki', 5, 2)->nullable();       // BMI (hesaplanip saklanir)
            $table->decimal('yag_orani', 5, 2)->nullable(); // %
            $table->decimal('odem', 6, 2)->nullable();
            $table->decimal('kas_puani', 6, 2)->nullable();
            $table->decimal('kas_kg', 5, 1)->nullable();
            $table->decimal('ic_yaglanma', 5, 2)->nullable();
            $table->integer('olcen_personel_id')->nullable();
            $table->text('not')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('musteri_olcumleri');
    }
}
