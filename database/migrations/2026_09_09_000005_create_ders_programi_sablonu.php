<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Haftalik sabit ders programi sablonu (kagit haftalik program karsiligi).
 *
 * Egitmenin sabit haftalik dersleri BIR KEZ tanimlanir; "Programi Yayinla"
 * / cron bu sablondan ileriye donuk ders_oturumlari uretir. Boylece her hafta
 * tek tek oturum girmeye gerek kalmaz (kagittaki tablo mantigi).
 * Idempotent.
 */
class CreateDersProgramiSablonu extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ders_programi_sablonu')) {
            return;
        }
        Schema::create('ders_programi_sablonu', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salon_id')->index();
            $table->integer('sube_id')->nullable();
            $table->integer('personel_id')->nullable();
            $table->tinyInteger('hafta_gunu');            // 1=Pazartesi ... 7=Pazar
            $table->time('saat');
            $table->time('saat_bitis');
            $table->string('ders_tipi')->nullable();
            $table->integer('kapasite')->default(1);
            $table->string('renk')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ders_programi_sablonu');
    }
}
