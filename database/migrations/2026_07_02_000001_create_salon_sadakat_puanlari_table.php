<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSalonSadakatPuanlariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salon_sadakat_puanlari')) return;

        Schema::create('salon_sadakat_puanlari', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id');
            $table->unsignedInteger('user_id');
            $table->decimal('puan', 10, 2)->default(0);      // carkifelek sadakat bakiyesi
            $table->timestamps();

            $table->index(['salon_id', 'user_id']);
        });

        // Backfill: salon_puanlar tablosu tarihsel olarak hem yildiz rating'i (1-5)
        // hem de carkifelek sadakat bakiyesini tutuyordu. 5'ten buyuk degerler
        // yalnizca sadakat olabilir (rating 1-5 araligindadir); bunlari yeni tabloya
        // tasi ve salon_puanlar'i sadece rating kalacak sekilde temizle. Boylece
        // rating ortalamasi da bozulmaz.
        if (Schema::hasTable('salon_puanlar')) {
            DB::statement(
                "INSERT INTO salon_sadakat_puanlari (salon_id, user_id, puan, created_at, updated_at)
                 SELECT salon_id, user_id, puan, NOW(), NOW()
                 FROM salon_puanlar WHERE puan > 5"
            );
            DB::table('salon_puanlar')->where('puan', '>', 5)->delete();
        }
    }

    public function down()
    {
        Schema::dropIfExists('salon_sadakat_puanlari');
    }
}
