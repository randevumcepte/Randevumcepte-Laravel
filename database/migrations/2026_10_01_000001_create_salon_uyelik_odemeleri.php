<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Salon uyelik (lisans) uzatma / tahsilat kayitlari.
 *
 * Sistem yonetimi v2 salon detayinda "Paketli Uyelik Uzatma" her calistirildiginda
 * bir satir yazilir: hangi paket (Baslangic/Standart/Premium), periyot (aylik/yillik),
 * kac ay (hediye dahil), alinan ucret ve eski/yeni bitis tarihi. Ileride ciro/tahsilat
 * raporu bu tablodan cikarilabilir.
 */
class CreateSalonUyelikOdemeleri extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('salon_uyelik_odemeleri')) {
            Schema::create('salon_uyelik_odemeleri', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('salon_id')->index();
                $table->string('paket', 60)->nullable();          // Baslangic | Standart | Premium
                $table->string('periyot', 10)->nullable();        // aylik | yillik
                $table->unsignedInteger('adet')->default(1);      // kac ay (aylik) veya kac yil (yillik)
                $table->unsignedInteger('hediye_ay')->default(0); // ek/hediye ay (orn. 12 + 4)
                $table->unsignedInteger('toplam_ay')->default(0); // uzatilan toplam ay (hediye dahil)
                $table->decimal('ucret', 12, 2)->default(0);      // alinan ucret (TL)
                $table->date('eski_tarih')->nullable();
                $table->date('yeni_tarih')->nullable();
                $table->unsignedBigInteger('yapan_id')->nullable();
                $table->string('yapan_adi', 120)->nullable();
                $table->string('aciklama', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('salon_uyelik_odemeleri');
    }
}
