<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grup dersi / kapasiteli ders oturumu (Pilates, kurs vb.).
 *
 * Mevcut "1 randevu = 1 musteri" modelini bozmadan, takvime AYRI event kaynagi
 * olarak binen kapasiteli ders oturumu. Bir oturum = bir egitmen (personel) +
 * bir zaman penceresi + kapasite (yatak/kontenjan). Katilimcilar ayri tabloda
 * (ders_katilimcilar) tutulur; boylece 1 oturum -> N musteri mumkun olur.
 *
 * Kapasite oturum bazinda tutulur (egitmene/derse gore degisken).
 * sablon_id NULL degilse oturum haftalik sablondan uretilmistir (ders_programi_sablonu).
 * Idempotent.
 */
class CreateDersOturumlari extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ders_oturumlari')) {
            return;
        }
        Schema::create('ders_oturumlari', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salon_id')->index();
            $table->integer('sube_id')->nullable();
            $table->integer('personel_id')->nullable()->index();
            $table->string('ders_tipi')->nullable();      // Reformer, Mat, Crossfit, Birebir...
            $table->date('tarih')->index();
            $table->time('saat');
            $table->time('saat_bitis');
            $table->integer('kapasite')->default(1);       // egitmene/derse gore degisken
            $table->integer('sablon_id')->nullable();      // ders_programi_sablonu.id (uretildiyse)
            $table->boolean('iptal')->default(false);
            $table->boolean('aktif')->default(true);
            $table->string('renk')->nullable();
            $table->text('not')->nullable();
            $table->integer('olusturan_personel_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ders_oturumlari');
    }
}
