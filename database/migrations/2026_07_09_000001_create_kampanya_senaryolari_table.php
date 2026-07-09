<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kampanya senaryolari — "Senaryo Sihirbazi" ile olusturulan, adim adim
 * konusma/mesaj sablonlari.
 *
 * salon_id NULL  -> sistem (hazir) senaryosu, tum isletmelerde gorunur.
 * salon_id dolu  -> isletmenin kendi olusturdugu senaryo.
 *
 * adimlar    : JSON. Kanal Santral Arama ise konusma adimlari (acilis, soru_kod,
 *              soru_randevu, kapanis, red...). SMS/Bildirim ise sadece "acilis".
 * aksiyonlar : JSON. {indirim_kodu_sms, yol_tarifi_sms, randevu_olustur}
 *
 * Idempotent: tablo varsa no-op.
 */
class CreateKampanyaSenaryolariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('kampanya_senaryolari')) {
            return;
        }

        Schema::create('kampanya_senaryolari', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('salon_id')->nullable();
            $table->string('ad', 191);
            // geri_kazanim | dogum_gunu | paket_bitti | yeni_hizmet | ozel
            $table->string('senaryo_tipi', 40)->default('ozel');
            // 1=Arama 2=SMS 3=Bildirim 4=Bilgilendirme (kampanya_yonetimi.gorev_turu ile ayni)
            $table->unsignedTinyInteger('gorev_turu')->default(1);
            $table->text('adimlar')->nullable();
            $table->text('aksiyonlar')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['salon_id', 'gorev_turu', 'aktif'], 'ks_salon_kanal_aktif_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kampanya_senaryolari');
    }
}
