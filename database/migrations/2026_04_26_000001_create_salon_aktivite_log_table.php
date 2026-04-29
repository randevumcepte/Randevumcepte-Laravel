<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalonAktiviteLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salon_aktivite_log')) return;

        Schema::create('salon_aktivite_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('salon_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('user_type', 30)->nullable();   // yetkili | personel | satis_ortagi | sistem
            $table->string('user_name', 150)->nullable();
            $table->string('user_rol', 80)->nullable();    // Hesap Sahibi, Yönetici, Personel, ...
            $table->string('action', 80);                   // login, randevu_sil, musteri_ekle ...
            $table->string('target_type', 80)->nullable();  // randevu, musteri, hizmet, adisyon ...
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_label', 220)->nullable();
            $table->text('aciklama')->nullable();
            $table->text('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['salon_id', 'created_at'], 'idx_salon_created');
            $table->index('action', 'idx_action');
            $table->index(['salon_id', 'user_id'], 'idx_salon_user');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salon_aktivite_log');
    }
}
