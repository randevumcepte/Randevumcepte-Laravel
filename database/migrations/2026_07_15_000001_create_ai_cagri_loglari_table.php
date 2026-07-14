<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * AI sesli asistan (santral) cagri kayitlari.
 *
 * ai_cagri_loglari : her telefon cagrisi icin 1 satir (ozet + sonuc + sureler)
 * ai_cagri_turlari : cagri icindeki her konusma turu (STT metni, asistan cevabi,
 *                    tool cagrilari, gecikmeler) — "santral neyi neden yapti"
 *                    teshisi icin.
 *
 * Sidecar cagri bitiminde /api/ai/cagri-log ucuna tum dokumu tek POST ile yollar.
 * Idempotent.
 */
class CreateAiCagriLoglariTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ai_cagri_loglari')) {
            Schema::create('ai_cagri_loglari', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('salon_id')->nullable()->index();
                $table->string('caller_telefon', 32)->nullable()->index();
                $table->string('did', 64)->nullable();
                $table->string('channel_id', 80)->nullable();
                // basladi | tamamlandi | randevu | transfer | sessizlik | max_tur | hata
                $table->string('durum', 32)->nullable()->index();
                $table->string('sonuc', 500)->nullable();       // insan-okunur ozet
                $table->unsignedInteger('randevu_id')->nullable()->index();
                $table->unsignedInteger('tur_sayisi')->default(0);
                $table->unsignedInteger('toplam_sure_sn')->nullable();
                $table->unsignedInteger('stt_ms_toplam')->nullable();
                $table->unsignedInteger('llm_ms_toplam')->nullable();
                $table->unsignedInteger('tts_ms_toplam')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_cagri_turlari')) {
            Schema::create('ai_cagri_turlari', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('cagri_log_id')->index();
                $table->unsignedInteger('tur_no')->default(0);
                $table->text('kullanici_metni')->nullable();     // STT ciktisi
                $table->text('asistan_metni')->nullable();       // TTS ile calinan cevap
                $table->text('tool_cagrilari')->nullable();      // JSON: [{name, args, result_ozet}]
                $table->unsignedInteger('stt_ms')->nullable();
                $table->unsignedInteger('llm_ms')->nullable();
                $table->unsignedInteger('tts_ms')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down()
    {
        // Teshis verisi korunur — tablolar dusurulmuyor.
    }
}
