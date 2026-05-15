<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personel granular yetki sistemi.
 *
 * - personel_id basina ayar saklanir
 * - sablon: 'yonetici' | 'sekreter' | 'personel' | 'ozel'
 * - ayarlar: TEXT (JSON encode), key=yetki_anahtari, value=bool
 *
 * Sadece "Personel" rolundekilere uygulanir. Hesap Sahibi/Yonetici/Supervisor/
 * Sekreter/Sanat Yonetmeni/Sosyal Medya Uzmani rolleri default olarak tam yetkili
 * kalir (defansif yaklasim).
 *
 * NOT: Anonim class yerine normal class kullaniyoruz cunku eski Laravel
 * surumleri (PHP 7.4 + Laravel <9) anonim class migration'i tanimiyor.
 */
class CreatePersonelYetkiAyarlari extends Migration
{
    public function up()
    {
        if (Schema::hasTable('personel_yetki_ayarlari')) {
            return; // Runtime auto-create ile zaten olusmus olabilir
        }
        Schema::create('personel_yetki_ayarlari', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('personel_id');
            $t->unsignedBigInteger('salon_id');
            $t->string('sablon', 32)->default('personel');
            // JSON yerine longText — eski MySQL surumlerinde de calisir
            $t->longText('ayarlar')->nullable();
            $t->timestamps();

            $t->unique(['personel_id', 'salon_id'], 'pya_unique_per_sub');
            $t->index('salon_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('personel_yetki_ayarlari');
    }
}
