<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSistemyonetimHazirCevaplarTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sistemyonetim_hazir_cevaplar')) {
            Schema::create('sistemyonetim_hazir_cevaplar', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('baslik', 200);
                $table->text('icerik');
                $table->string('kategori', 30)->default('genel')->comment('genel|teknik|odeme|egitim|iade|kapanis');
                $table->string('kisayol', 30)->nullable()->comment('hizli erisim icin: /merhaba');
                $table->unsignedInteger('kullanim_sayisi')->default(0);
                $table->unsignedInteger('olusturan_user_id')->nullable();
                $table->string('olusturan_user_name', 120)->nullable();
                $table->tinyInteger('aktif')->default(1);
                $table->timestamps();
                $table->index('kategori', 'sy_hc_kategori_idx');
                $table->index('kisayol', 'sy_hc_kisayol_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sistemyonetim_hazir_cevaplar');
    }
}
