<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E-santral musterileri icin dakika paketi tanimi.
 *
 * "Dakika bazli havuz" mantigi: tanimli_dakika = musteriye taninan toplam
 * giden konusma dakikasi. Kullanilan dakika, salonun olusturma tarihinden
 * (veya sayim_baslangic doluysa ondan) bugune, FreePBX CDR'daki giden
 * (outbound_cnum = trunk) ANSWERED cagrilarinin SUM(billsec)/60 degeri.
 * Kalan = tanimli_dakika - kullanilan. Donem sifirlamasi yoktur; havuz
 * tanimli_dakika artirilarak buyutulur.
 *
 * sayim_baslangic NULL => salon olusturma tarihi kullanilir (varsayilan).
 */
class CreateSalonDakikaPaketi extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salon_dakika_paketi')) return;

        Schema::create('salon_dakika_paketi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id')->unique();
            $table->integer('tanimli_dakika')->default(0);       // taninan toplam dakika
            $table->date('sayim_baslangic')->nullable();          // null => salon.created_at
            $table->string('guncelleyen', 120)->nullable();       // son duzenleyen yonetici
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salon_dakika_paketi');
    }
}
