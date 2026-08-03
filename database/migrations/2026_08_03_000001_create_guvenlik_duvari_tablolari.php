<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Güvenlik Duvarı (Sistem Yönetim v2) tabloları.
 *
 * Root watchdog (scripts/guvenlik-watchdog.sh) her dakika çalışır; flood / SSH
 * brute-force / yüksek load tespit eder, saldırgan IP'yi ipset+iptables ile
 * ENGELLER ve olayı 'guvenlik_olaylari' tablosuna yazar. Panel bu tabloyu
 * gösterir; whitelist/blacklist kurallarını 'guvenlik_ip_kurallari' tablosunda
 * yönetir ve watchdog bir sonraki turda bu kuralları uygular (whitelist = engeli
 * kaldır, blacklist = kalıcı engelle).
 *
 * Idempotent.
 */
class CreateGuvenlikDuvariTablolari extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('guvenlik_olaylari')) {
            Schema::create('guvenlik_olaylari', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tur', 30);                 // flood | ssh_brute | load_yuksek
                $table->string('ip', 45)->nullable();      // load olaylarında NULL
                $table->integer('deger')->nullable();      // bağlantı sayısı / fail sayısı / load*100
                $table->integer('esik')->nullable();       // tetiklenen eşik
                $table->string('aksiyon', 20)->default('izlendi'); // engellendi | uyari | izlendi
                $table->string('detay', 255)->nullable();
                $table->tinyInteger('bildirildi')->default(0);
                $table->timestamp('created_at')->nullable();
                $table->index('ip');
                $table->index('created_at');
                $table->index('tur');
            });
        }

        if (!Schema::hasTable('guvenlik_ip_kurallari')) {
            Schema::create('guvenlik_ip_kurallari', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ip', 45)->unique();
                $table->string('tip', 20);                 // whitelist | blacklist
                $table->string('aciklama', 255)->nullable();
                $table->string('ekleyen', 100)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index('tip');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('guvenlik_olaylari');
        Schema::dropIfExists('guvenlik_ip_kurallari');
    }
}
