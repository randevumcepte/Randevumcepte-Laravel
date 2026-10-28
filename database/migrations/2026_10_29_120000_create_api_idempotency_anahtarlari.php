<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API v2 idempotency anahtarlari — finansal yazma uclarinda (POST /api/v2/tahsilat)
 * CIFT-KAYIT onlemek icin.
 *
 * Istemci her yazma istegine benzersiz bir 'anahtar' uretir; ayni anahtar ikinci
 * kez gelirse yeni app ikinci kez YAZMAZ, ilk istegin yanitini doner.
 *
 * YENI TABLO: eski monolite ve sicak 'tahsilatlar' tablosuna SIFIR etki (ALTER yok).
 * Yalnizca yeni L13 app (Randevumcepte-New-Laravel) okur/yazar. Sema eski klasorde
 * yonetilir (kural geregi); yeni app migrate ETMEZ.
 *
 * NOT: Anonim class degil — eski Laravel (<9 / PHP 7.4) anonim migration tanimaz.
 */
class CreateApiIdempotencyAnahtarlari extends Migration
{
    public function up()
    {
        if (Schema::hasTable('api_idempotency_anahtarlari')) {
            return;
        }
        Schema::create('api_idempotency_anahtarlari', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('anahtar', 100);                 // istemci idempotency-key
            $t->string('uc', 64);                       // uc adi: 'tahsilat' vb.
            $t->unsignedBigInteger('yetkili_id')->nullable();
            $t->unsignedBigInteger('salon_id')->nullable();
            $t->integer('yanit_kodu')->nullable();      // ilk yanit HTTP kodu
            $t->longText('yanit_json')->nullable();     // ilk yanit govdesi (replay)
            $t->timestamps();

            // Ayni anahtar+uc ikinci kez yazilamaz → cift-kayit kalkani.
            $t->unique(['anahtar', 'uc'], 'api_idem_uq');
            $t->index('salon_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_idempotency_anahtarlari');
    }
}
