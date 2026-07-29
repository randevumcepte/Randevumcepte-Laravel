<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * app_bundle (beyaz etiket marka) bazlı ayarlar.
 * Şimdilik yalnızca marka/işletme BAŞLIĞI: çok şubeli bir markada müşteri
 * panelinde tek bir şube adı yerine bu başlık gösterilir.
 *
 * Idempotent.
 */
class CreateAppBundleAyarlariTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('app_bundle_ayarlari')) {
            return;
        }

        Schema::create('app_bundle_ayarlari', function (Blueprint $table) {
            $table->increments('id');
            $table->string('app_bundle', 191)->unique();
            $table->string('bundle_baslik', 200)->nullable(); // marka/işletme başlığı
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_bundle_ayarlari');
    }
}
