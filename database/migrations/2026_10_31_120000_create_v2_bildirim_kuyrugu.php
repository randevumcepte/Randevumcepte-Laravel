<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v2 bildirim kuyrugu (outbox) — yeni L13 app (Randevumcepte-New-Laravel) randevu
 * durum islemlerinin YAN ETKILERINI (SMS/WA, FCM push, uygulama-ici bildirim,
 * dogrulama/KVKK kodu, seans kullanim push'u) burada kuyruga yazar; eski monolitin
 * `v2:bildirim-isle` komutu bunlari MEVCUT bildirim koduyla (ApiController
 * randevuIptalBildirimleri / randevuOnayBildirimleri / kvkkOnayKoduGonder /
 * dogrulama_kodu_gonder, SeansBildirimService) gonderir — kod tekrari YOK.
 *
 * Yazma, durum degisikligiyle AYNI transaction'da yapilir → durum yazildiysa
 * bildirim kaybolmaz; yazilmadiysa hayalet bildirim gitmez.
 *
 * YENI TABLO: mevcut tablolara SIFIR etki. Anonim class degil (L5.6/PHP 7.4).
 */
class CreateV2BildirimKuyrugu extends Migration
{
    public function up()
    {
        if (Schema::hasTable('v2_bildirim_kuyrugu')) {
            return;
        }
        Schema::create('v2_bildirim_kuyrugu', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedInteger('salon_id')->nullable();
            $t->string('tur', 40);                          // randevu_iptal | randevu_onay | dogrulama_kodu | kvkk_kodu | seans_kullanim
            $t->unsignedBigInteger('randevu_id')->nullable();
            $t->unsignedBigInteger('yetkili_id')->nullable(); // islemi yapan (v2 token kimligi)
            $t->text('payload')->nullable();                // JSON (orn. {"red":true})
            $t->tinyInteger('durum')->default(0);           // 0 bekliyor | 3 isleniyor | 1 gonderildi | 2 hata (kalici)
            $t->unsignedTinyInteger('deneme')->default(0);
            $t->text('hata')->nullable();
            $t->timestamp('islenme_at')->nullable();
            $t->timestamps();

            $t->index(['durum', 'id'], 'v2bk_durum_id');
            $t->index('randevu_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('v2_bildirim_kuyrugu');
    }
}
