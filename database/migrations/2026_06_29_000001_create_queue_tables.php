<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Asenkron kuyruk (database driver) tablolari.
 *
 * Reklam/randevu/alacak hatirlatma ARAMALARI HatirlatmaAramaJob ile
 * `database` baglantisi uzerinden kuyruga alinir (global QUEUE_DRIVER=sync
 * kalir; sadece arama joblari icin connection=database). Bu joblari isleyen
 * surec:  php artisan queue:work database --queue=hatirlatmalar,notifications
 *
 * Laravel 5.6 semasi (uuid yok, Bus::batch yok). Tamamen idempotent:
 * deploy.sh `migrate --force` calistirir; tablo varsa no-op.
 */
class CreateQueueTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->increments('id');
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down()
    {
        // Kuyruk tablolari veriyle birlikte kalir; geri alma uygulanmaz.
    }
}
