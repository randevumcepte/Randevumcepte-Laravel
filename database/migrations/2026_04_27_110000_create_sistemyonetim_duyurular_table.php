<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSistemyonetimDuyurularTable extends Migration
{
    public function up()
    {
        // Onceki basarisiz migration denemelerinden kalan kismi tablolari temizle
        Schema::dropIfExists('sistemyonetim_duyuru_okundu');
        Schema::dropIfExists('sistemyonetim_duyurular');

        if (!Schema::hasTable('sistemyonetim_duyurular')) {
            Schema::create('sistemyonetim_duyurular', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('baslik', 200);
                $table->text('icerik');
                $table->string('tip', 20)->default('bilgi')->comment('bilgi|uyari|onemli|bakim|kampanya');
                $table->string('hedef_tipi', 20)->default('hepsi')->comment('hepsi|secili|il');
                $table->text('hedef_ids')->nullable()->comment('JSON: salon_id[] veya il_id[]');
                $table->timestamp('baslangic_tarihi')->nullable();
                $table->timestamp('bitis_tarihi')->nullable();
                $table->tinyInteger('aktif')->default(1);
                $table->tinyInteger('sticky')->default(0)->comment('1: panele giriste her zaman gozuk');
                $table->string('cta_metin', 80)->nullable();
                $table->string('cta_link', 250)->nullable();
                $table->unsignedInteger('olusturan_user_id')->nullable();
                $table->string('olusturan_user_name', 120)->nullable();
                $table->timestamps();
                $table->index(['aktif', 'baslangic_tarihi'], 'sy_duy_aktif_at_idx');
            });
        }

        if (!Schema::hasTable('sistemyonetim_duyuru_okundu')) {
            Schema::create('sistemyonetim_duyuru_okundu', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('duyuru_id');
                $table->unsignedBigInteger('salon_id');
                $table->unsignedInteger('user_id')->nullable()->comment('isletme_yetkili_id');
                $table->timestamp('okundu_tarihi')->useCurrent();
                $table->index(['duyuru_id', 'salon_id'], 'sy_duy_okundu_idx');
                $table->unique(['duyuru_id', 'user_id'], 'sy_duy_user_uniq');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sistemyonetim_duyuru_okundu');
        Schema::dropIfExists('sistemyonetim_duyurular');
    }
}
