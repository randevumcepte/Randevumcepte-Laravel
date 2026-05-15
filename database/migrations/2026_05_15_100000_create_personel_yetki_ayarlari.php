<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personel granular yetki sistemi.
 *
 * - personel_id basina ayar saklanir
 * - sablon: 'sekreter' | 'personel_tam' | 'personel_sade' | 'demo' | 'ozel'
 * - ayarlar: JSON, key=yetki_anahtari, value=bool
 *
 * Sadece "Personel" rolundekilere uygulanir. Hesap Sahibi/Yonetici/Supervisor/
 * Sekreter/Sanat Yonetmeni/Sosyal Medya Uzmani rolleri default olarak tam yetkili
 * kalir (defansif yaklasim).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('personel_yetki_ayarlari', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('personel_id');
            $t->unsignedBigInteger('salon_id');
            $t->string('sablon', 32)->default('personel_sade');
            // JSON: { "musteri.telefon_gor": false, "rapor.satis": true, ... }
            $t->json('ayarlar')->nullable();
            $t->timestamps();

            $t->unique(['personel_id', 'salon_id'], 'pya_unique_per_sub');
            $t->index('salon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personel_yetki_ayarlari');
    }
};
