<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * AI (Haiku) kullanim logu: her cagriyi salon + tur + token + maliyet ile kaydeder.
 * Sistem Yonetimi > AI Kredi paneli bunu okur (salon bazinda harcama, cache tasarrufu vb.).
 * cache=1 -> cevap onbellekten geldi (BEDAVA), maliyet 0.
 */
class CreateAiKullanimTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ai_kullanim')) {
            Schema::create('ai_kullanim', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('salon_id')->nullable();
                $table->string('tur', 20)->default('sohbet'); // niyet|sohbet|karne|yorum|kampanya
                $table->string('model', 40)->nullable();
                $table->unsignedInteger('girdi_token')->default(0);
                $table->unsignedInteger('cikti_token')->default(0);
                $table->decimal('maliyet_usd', 12, 6)->default(0);
                $table->boolean('cache')->default(0);
                $table->boolean('basarili')->default(1);
                $table->timestamp('created_at')->nullable();
                $table->index(['salon_id', 'created_at'], 'ai_kul_salon_tarih_idx');
                $table->index('created_at', 'ai_kul_tarih_idx');
                $table->index('tur', 'ai_kul_tur_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ai_kullanim');
    }
}
