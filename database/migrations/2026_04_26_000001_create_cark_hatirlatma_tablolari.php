<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarkHatirlatmaTablolari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cark_hatirlatma_ayarlari')) {
            Schema::create('cark_hatirlatma_ayarlari', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('salon_id')->unique();
                $table->tinyInteger('aktif')->default(0);
                $table->time('saat_1')->default('10:00:00');
                $table->time('saat_2')->default('15:00:00');
                $table->time('saat_3')->default('20:00:00');
                $table->time('saat_son')->default('22:30:00');
                $table->string('mesaj_1', 300)->default('🎡 Bugün çark hakkınız var, hediyeler sizi bekliyor!');
                $table->string('mesaj_2', 300)->default('⏰ Çark hakkınız hâlâ duruyor — son birkaç saat!');
                $table->string('mesaj_3', 300)->default('🚨 Son 4 saat! Çarkı çevirmeyi unutmayın');
                $table->string('mesaj_son', 300)->default('🎯 Son 90 dakika! Çevirmek için tek tık');
                $table->json('gonderim_gunleri')->nullable(); // null = her gün, [0,6] = pazar/cumartesi yok
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cark_hatirlatma_loglari')) {
            Schema::create('cark_hatirlatma_loglari', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('salon_id');
                $table->unsignedInteger('user_id');
                $table->tinyInteger('asama'); // 1, 2, 3, son
                $table->date('tarih');
                $table->timestamp('gonderim_tarihi')->nullable();
                $table->string('durum', 20)->default('gonderildi'); // gonderildi | tiklandi | hata
                $table->timestamps();

                $table->index(['salon_id', 'user_id', 'tarih', 'asama'], 'cark_hat_log_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('cark_hatirlatma_loglari');
        Schema::dropIfExists('cark_hatirlatma_ayarlari');
    }
}
