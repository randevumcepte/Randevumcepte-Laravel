<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Arama istatistikleri — reklam/randevu/alacak hatirlatma aramalarinin
 * basari/hata kaydi (HatirlatmaAramaJob + SendCompletionNotification yazar).
 * Idempotent.
 */
class CreateAramaIstatistikleriTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('arama_istatistikleri')) {
            return;
        }

        Schema::create('arama_istatistikleri', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('salon_id')->nullable()->index();
            $table->unsignedInteger('kampanya_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('cep_telefon', 32)->nullable();
            $table->unsignedInteger('toplam_arama')->default(0);
            $table->string('durum', 32)->nullable();        // basarili | hata
            $table->string('hata_mesaji', 255)->nullable();
            $table->timestamp('tamamlanma_tarihi')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        // Istatistik verisi korunur.
    }
}
