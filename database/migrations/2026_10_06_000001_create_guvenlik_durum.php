<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Güvenlik Duvarı — watchdog canlılık (heartbeat) durumu.
 *
 * Root watchdog (scripts/guvenlik-watchdog.sh) HER turunun sonunda buraya
 * 'heartbeat' anahtarını NOW() ile yazar (+ 'aktif_ban' = o anki ipset üye sayısı).
 * Panel bu satıra bakıp "ÇALIŞIYOR / son çalışma X önce / DURMUŞ" rozetini gösterir.
 *
 * Bu olmadan panel, sistem sessizken (olay yokken) watchdog'un canlı mı ölü mü
 * olduğunu ayırt edemiyordu — nitekim cron PATH tuzağı yüzünden koruma 2 ay
 * sessizce ölü kaldı (6 Eki 2026). Heartbeat bunu görünür kılar.
 *
 * Idempotent.
 */
class CreateGuvenlikDurum extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('guvenlik_durum')) {
            Schema::create('guvenlik_durum', function (Blueprint $table) {
                $table->string('anahtar', 50)->primary();   // heartbeat | aktif_ban | ...
                $table->string('deger', 255)->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('guvenlik_durum');
    }
}
